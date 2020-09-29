<?php

namespace Oro\Bundle\PricingBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\PricingBundle\Datagrid\Provider\ProductPriceProvider;
use Oro\Bundle\PricingBundle\EventListener\FrontendProductPriceDatagridListener;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaRequestHandler;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;
use Oro\Bundle\SearchBundle\Query\SearchQueryInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendProductPriceDatagridListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var FrontendProductPriceDatagridListener
     */
    private $listener;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ProductPriceScopeCriteriaRequestHandler
     */
    private $scopeCriteriaRequestHandler;

    /**
     * @var UserCurrencyManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $currencyManager;

    /**
     * @var ProductPriceProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $combinedProductPriceProvider;

    public function setUp()
    {
        $this->scopeCriteriaRequestHandler = $this->createMock(ProductPriceScopeCriteriaRequestHandler::class);

        $this->currencyManager = $this->getMockBuilder(UserCurrencyManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->combinedProductPriceProvider = $this->getMockBuilder(ProductPriceProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')
            ->willReturnMap([
                ['oro.pricing.productprice.price.label', [], null, null, 'Price'],
            ]);

        $this->listener = new FrontendProductPriceDatagridListener(
            $this->scopeCriteriaRequestHandler,
            $this->currencyManager,
            $this->combinedProductPriceProvider,
            $translator
        );
    }

    /**
     * @param array $priceCurrencies
     */
    protected function setUpPriceListRequestHandler(array $priceCurrencies = [])
    {
        $this->scopeCriteriaRequestHandler
            ->expects($this->any())
            ->method('getPriceScopeCriteria')
            ->willReturn(new ProductPriceScopeCriteria());

        $this->currencyManager
            ->expects($this->any())
            ->method('getUserCurrency')
            ->willReturn(reset($priceCurrencies));
    }

    /**
     * @param array $priceCurrencies
     * @param array $expectedConfig
     * @dataProvider onBuildBeforeDataProvider
     */
    public function testOnBuildBefore(array $priceCurrencies = [], array $expectedConfig = [])
    {
        $this->setUpPriceListRequestHandler($priceCurrencies);

        /** @var \PHPUnit\Framework\MockObject\MockObject|DatagridInterface $datagrid */
        $datagrid = $this->createMock(DatagridInterface::class);
        $config = DatagridConfiguration::create([]);

        $event = new BuildBefore($datagrid, $config);
        $this->listener->onBuildBefore($event);

        $this->assertEquals($expectedConfig, $config->toArray());
    }

    /**
     * @return array
     */
    public function onBuildBeforeDataProvider()
    {
        return [
            'no currencies' => [
                'priceCurrencies' => [],
            ],
            'valid currencies' => [
                'priceCurrencies' => ['EUR'],
                'expectedConfig' => [
                    'properties' => [
                        'prices' => ['type' => 'field', 'frontend_type' => 'row_array'],
                    ],
                    'columns' => [
                        'minimal_price' => ['label' => 'Price'],
                        'minimal_price_sort' => [
                            'label' => 'oro.pricing.price.label',
                        ]
                    ],
                    'filters' => [
                        'columns' => [
                            'minimal_price' => [
                                'type' => 'frontend-product-price',
                                'data_name' => 'minimal_price_CPL_ID_CURRENCY_UNIT'
                            ]
                        ]
                    ],
                    'sorters' => [
                        'columns' => [
                            'minimal_price_sort' => [
                                'data_name' => 'minimal_price_CPL_ID_CURRENCY',
                                'type' => 'decimal',
                            ]
                        ]
                    ],
                ],
            ],
        ];
    }

    public function testOnResultAfterNoRecords()
    {
        $this->currencyManager->expects($this->never())
            ->method($this->anything());

        /** @var SearchQueryInterface $query */
        $query = $this->getMockBuilder(SearchQueryInterface::class)->getMock();
        /** @var DatagridInterface $datagrid */
        $datagrid = $this->createMock(DatagridInterface::class);
        $event = new SearchResultAfter($datagrid, $query, []);
        $this->listener->onResultAfter($event);
    }

    /**
     * @dataProvider onResultWithCombinedPricesProvider
     * @param array $products
     * @param array $combinedProductPrices
     * @param array $expected
     */
    public function testOnResultWithCombinedPrices($products, $combinedProductPrices, $expected)
    {
        $this->setUpPriceListRequestHandler(['USD']);

        $records = [new ResultRecord($products)];
        $priceScopeCriteria = new ProductPriceScopeCriteria();

        $this->scopeCriteriaRequestHandler->expects($this->once())
            ->method('getPriceScopeCriteria')
            ->willReturn($priceScopeCriteria);

        $this->combinedProductPriceProvider->expects($this->once())
            ->method('getCombinedPricesForProductsByPriceList')
            ->with($records, $priceScopeCriteria, 'USD')
            ->will($this->returnValue($combinedProductPrices));

        /** @var SearchQueryInterface $query */
        $query = $this->getMockBuilder(SearchQueryInterface::class)->getMock();
        /** @var DatagridInterface $datagrid */
        $datagrid = $this->createMock(DatagridInterface::class);
        $event = new SearchResultAfter($datagrid, $query, $records);
        $this->listener->onResultAfter($event);

        $actualResults = $event->getRecords();

        $this->assertSameSize($expected, $actualResults);
        foreach ($expected as $key => $expectedResult) {
            $actualResult = $actualResults[$key];
            foreach ($expectedResult as $name => $value) {
                $this->assertEquals($value, $actualResult->getValue($name));
            }
        }
    }


    /**
     * @return array
     */
    public function onResultWithCombinedPricesProvider()
    {
        return [
            'valid data' => [
                'sourceResults' => [
                    'id' => 2
                ],
                [
                    2 => [
                        'item_1' => [
                            'price' => 20,
                            'currency' => 'EUR',
                            'formatted_price' => 'EUR20',
                            'unit' => 'item',
                            'formatted_unit' => 'item-formatted',
                            'quantity' => 1,
                            'quantity_with_unit' => '1-item-formatted',
                        ],
                        'item_2' => [
                            'price' => 21,
                            'currency' => 'EUR',
                            'formatted_price' => 'EUR21',
                            'unit' => 'item',
                            'formatted_unit' => 'item-formatted',
                            'quantity' => 2,
                            'quantity_with_unit' => '2-item-formatted',
                        ],
                    ],
                ],
                'expectedResults' => [
                    [
                        'id' => 2,
                        'prices' => [
                            'item_1' => [
                                'price' => 20,
                                'currency' => 'EUR',
                                'formatted_price' => 'EUR20',
                                'unit' => 'item',
                                'formatted_unit' => 'item-formatted',
                                'quantity' => 1,
                                'quantity_with_unit' => '1-item-formatted',
                            ],
                            'item_2' => [
                                'price' => 21,
                                'currency' => 'EUR',
                                'formatted_price' => 'EUR21',
                                'unit' => 'item',
                                'formatted_unit' => 'item-formatted',
                                'quantity' => 2,
                                'quantity_with_unit' => '2-item-formatted',
                            ],
                        ],
                        'price_units' => null,
                        'price_quantities' => null,
                    ]
                ],
            ],
        ];
    }
}
