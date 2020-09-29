<?php

namespace Oro\Bundle\TaxBundle\Tests\Functional\EventListener;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\TaxBundle\Tests\Functional\Traits\OrderTaxHelperTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @covers \Oro\Bundle\TaxBundle\EventListener\EntityTaxListener
 * @covers \Oro\Bundle\TaxBundle\EventListener\BuiltinEntityTaxListener
 */
class EntityTaxListenerTest extends WebTestCase
{
    use OrderTaxHelperTrait;

    /** @var ManagerRegistry */
    protected $doctrine;

    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->doctrine = $this->getContainer()->get('doctrine');
    }

    public function testSaveOrderTaxValue()
    {
        $order = $this->createOrder();

        $taxValue = $this->getTaxValue($order);
        $this->assertNotNull($taxValue);
        $this->removeTaxValue($taxValue);
        $this->assertNull($this->getTaxValue($order));

        $this->updateOrder($order);
        $this->assertNotNull($this->getTaxValue($order));
    }

    public function testSaveTwoNewOrders()
    {
        $order1 = $this->createOrder(false);
        $order2 = $this->createOrder();

        $taxValue1 = $this->getTaxValue($order1);
        $this->assertNotNull($taxValue1);

        $taxValue2 = $this->getTaxValue($order2);
        $this->assertNotNull($taxValue2);
    }

    public function testRemoveOrderShouldRemoveTaxValue()
    {
        $order1 = $this->createOrder(false);
        $order2 = $this->createOrder(false);
        $order3 = $this->createOrder();

        $this->assertNotNull($this->getTaxValue($order1));
        $this->assertNotNull($this->getTaxValue($order2));

        $this->removeOrder($order1);

        $this->assertNull($this->getTaxValue($order1));
        $this->assertNotNull($this->getTaxValue($order2));
        $this->assertNotNull($this->getTaxValue($order3));
    }
}
