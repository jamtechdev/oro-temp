<?php

namespace Oro\Bundle\PricingBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\FormContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Collects initial statuses for updated price lists to later handle.
 */
class CollectPriceListInitialStatuses implements ProcessorInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var FormContext $context */

        $priceListStatuses = $context->get(HandlePriceListStatusChange::PRICE_LIST_INITIAL_STATUSES) ?? [];
        $entities = $context->getAllEntities();
        foreach ($entities as $entity) {
            if ($entity instanceof PriceList && null !== $entity->getId()) {
                $priceListStatuses[$entity->getId()] = [$this->getPriceListInitialStatus($entity), $entity];
            }
        }
        if ($priceListStatuses) {
            $context->set(HandlePriceListStatusChange::PRICE_LIST_INITIAL_STATUSES, $priceListStatuses);
        }
    }

    /**
     * @param PriceList $priceList
     *
     * @return bool
     */
    private function getPriceListInitialStatus(PriceList $priceList): bool
    {
        $originalEntityData = $this->doctrineHelper
            ->getEntityManagerForClass(PriceList::class)
            ->getUnitOfWork()
            ->getOriginalEntityData($priceList);

        return $originalEntityData['active'] ?? false;
    }
}
