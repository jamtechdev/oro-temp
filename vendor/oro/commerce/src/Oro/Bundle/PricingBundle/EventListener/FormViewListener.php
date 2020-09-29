<?php

namespace Oro\Bundle\PricingBundle\EventListener;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\PricingBundle\Entity\PriceAttributePriceList;
use Oro\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\PricingBundle\Entity\Repository\PriceAttributePriceListRepository;
use Oro\Bundle\PricingBundle\Provider\PriceAttributePricesProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Component\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds scroll blocks with product price and product price attributes data on view and edit pages
 */
class FormViewListener implements FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    const PRICE_ATTRIBUTES_BLOCK_NAME = 'price_attributes';
    const PRICING_BLOCK_NAME = 'prices';

    const PRICING_BLOCK_PRIORITY = 1650;
    const PRICE_ATTRIBUTES_BLOCK_PRIORITY = 1600;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var PriceAttributePricesProvider
     */
    protected $priceAttributePricesProvider;

    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $doctrineHelper
     * @param PriceAttributePricesProvider $provider
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AclHelper $aclHelper
     */
    public function __construct(
        TranslatorInterface $translator,
        DoctrineHelper $doctrineHelper,
        PriceAttributePricesProvider $provider,
        AuthorizationCheckerInterface $authorizationChecker,
        AclHelper $aclHelper
    ) {
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
        $this->priceAttributePricesProvider = $provider;
        $this->authorizationChecker = $authorizationChecker;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onProductView(BeforeListRenderEvent $event)
    {
        $product = $event->getEntity();
        if (!$product instanceof Product) {
            throw new UnexpectedTypeException($product, Product::class);
        }

        $this->addPriceAttributesViewBlock($event, $product);
        $this->addProductPricesViewBlock($event, $product);
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onProductEdit(BeforeListRenderEvent $event)
    {
        if (!$this->isFeaturesEnabled()) {
            return;
        }

        $template = $event->getEnvironment()->render(
            'OroPricingBundle:Product:prices_update.html.twig',
            ['form' => $event->getFormView()]
        );
        $scrollData = $event->getScrollData();
        $blockLabel = $this->translator->trans('oro.pricing.productprice.entity_plural_label');
        $scrollData->addNamedBlock(self::PRICING_BLOCK_NAME, $blockLabel, 1600);
        $subBlockId = $scrollData->addSubBlock(self::PRICING_BLOCK_NAME);
        $scrollData->addSubBlockData(self::PRICING_BLOCK_NAME, $subBlockId, $template, 'productPriceAttributesPrices');
    }

    /**
     * @return PriceAttributePriceList[]
     */
    protected function getProductAttributesPriceLists()
    {
        $qb = $this->getPriceAttributePriceListRepository()->getPriceAttributesQueryBuilder();

        return $this->aclHelper->apply($qb)->getResult();
    }

    /**
     * @return PriceAttributePriceListRepository|EntityRepository
     */
    protected function getPriceAttributePriceListRepository()
    {
        return $this->doctrineHelper->getEntityRepository(PriceAttributePriceList::class);
    }

    /**
     * @param BeforeListRenderEvent $event
     * @param Product $product
     */
    protected function addPriceAttributesViewBlock(BeforeListRenderEvent $event, Product $product)
    {
        $scrollData = $event->getScrollData();
        $blockLabel = $this->translator->trans('oro.pricing.priceattributepricelist.entity_plural_label');
        $scrollData->addNamedBlock(
            self::PRICE_ATTRIBUTES_BLOCK_NAME,
            $blockLabel,
            self::PRICE_ATTRIBUTES_BLOCK_PRIORITY
        );

        $priceLists = $this->getProductAttributesPriceLists();

        foreach ($priceLists as $priceList) {
            $subBlockId = $scrollData->addSubBlock(self::PRICE_ATTRIBUTES_BLOCK_NAME);

            $priceAttributePrices = $this->priceAttributePricesProvider
                ->getPricesWithUnitAndCurrencies($priceList, $product);

            $template = $event->getEnvironment()->render(
                'OroPricingBundle:Product:price_attribute_prices_view.html.twig',
                [
                    'product' => $product,
                    'priceList' => $priceList,
                    'priceAttributePrices' => $priceAttributePrices,
                ]
            );

            $scrollData->addSubBlockData(
                self::PRICE_ATTRIBUTES_BLOCK_NAME,
                $subBlockId,
                $template,
                'productPriceAttributesPrices'
            );
        }

        if (empty($priceLists)) {
            $subBlockId = $scrollData->addSubBlock(self::PRICE_ATTRIBUTES_BLOCK_NAME);
            $template = $event->getEnvironment()
                ->render('OroPricingBundle:Product:price_attribute_no_data.html.twig', []);
            $scrollData->addSubBlockData(
                self::PRICE_ATTRIBUTES_BLOCK_NAME,
                $subBlockId,
                $template,
                'productPriceAttributesPrices'
            );
        }
    }

    /**
     * @param BeforeListRenderEvent $event
     * @param Product $product
     */
    protected function addProductPricesViewBlock(BeforeListRenderEvent $event, Product $product)
    {
        if (!$this->isFeaturesEnabled()) {
            return;
        }

        if (!$this->authorizationChecker->isGranted(
            'VIEW',
            sprintf('entity:%s', ProductPrice::class)
        )) {
            return;
        }

        $scrollData = $event->getScrollData();
        $blockLabel = $this->translator->trans('oro.pricing.pricelist.entity_plural_label');
        $scrollData->addNamedBlock(self::PRICING_BLOCK_NAME, $blockLabel, self::PRICING_BLOCK_PRIORITY);
        $priceListSubBlockId = $scrollData->addSubBlock(self::PRICING_BLOCK_NAME);

        $template = $event->getEnvironment()->render(
            'OroPricingBundle:Product:prices_view.html.twig',
            [
                'entity' => $product,
            ]
        );

        $scrollData->addSubBlockData(
            self::PRICING_BLOCK_NAME,
            $priceListSubBlockId,
            $template,
            'prices'
        );
    }
}
