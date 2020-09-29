<?php

namespace Oro\Bundle\PricingBundle\Event;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Event;

/**
 * It published immediately after the flush.
 */
class ProductPricesUpdated extends Event
{
    const NAME = 'oro_pricing.product_prices.updated';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager(): ?EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
