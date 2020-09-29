<?php

namespace Oro\Bundle\ShoppingListBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionFieldConfig;
use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes values for "currency", "total" and "subTotal" fields for a shopping list.
 */
class ComputeShoppingListTotal implements ProcessorInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var TotalProcessorProvider */
    private $totalProvider;

    /**
     * @param DoctrineHelper         $doctrineHelper
     * @param TotalProcessorProvider $totalProvider
     */
    public function __construct(DoctrineHelper $doctrineHelper, TotalProcessorProvider $totalProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->totalProvider = $totalProvider;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeLoadedDataContext $context */

        $data = $context->getData();

        $totalFieldName = 'total';
        $subTotalFieldName = 'subTotal';
        $currencyFieldName = 'currency';
        if (\array_key_exists($totalFieldName, $data)
            || \array_key_exists($subTotalFieldName, $data)
            || \array_key_exists($currencyFieldName, $data)
        ) {
            // the computing values are already set
            return;
        }

        $config = $context->getConfig();
        $totalField = $config->getField($totalFieldName);
        $subTotalField = $config->getField($subTotalFieldName);
        $currencyField = $config->getField($currencyFieldName);
        if (null === $totalField && null === $subTotalField && null === $currencyField) {
            // only identifier field was requested
            return;
        }
        if ($totalField->isExcluded() && $subTotalField->isExcluded() && $currencyField->isExcluded()) {
            // none of computing fields was requested
            return;
        }

        $data = $this->computeFields(
            $data,
            $config,
            $currencyFieldName,
            $currencyField,
            $totalFieldName,
            $totalField,
            $subTotalFieldName,
            $subTotalField
        );
        $context->setData($data);
    }

    /**
     * @param array                       $data
     * @param EntityDefinitionConfig      $config
     * @param string                      $currencyFieldName
     * @param EntityDefinitionFieldConfig $currencyField
     * @param string                      $totalFieldName
     * @param EntityDefinitionFieldConfig $totalField
     * @param string                      $subTotalFieldName
     * @param EntityDefinitionFieldConfig $subTotalField
     *
     * @return array
     */
    private function computeFields(
        array $data,
        EntityDefinitionConfig $config,
        string $currencyFieldName,
        EntityDefinitionFieldConfig $currencyField,
        string $totalFieldName,
        EntityDefinitionFieldConfig $totalField,
        string $subTotalFieldName,
        EntityDefinitionFieldConfig $subTotalField
    ): array {
        $idFieldName = $config->findFieldNameByPropertyPath('id');
        $shoppingList = $this->doctrineHelper->getEntityReference(ShoppingList::class, $data[$idFieldName]);
        $computedTotal = $this->totalProvider->getTotal($shoppingList);

        if (!$totalField->isExcluded()) {
            $total = $computedTotal->getAmount();
            if (null !== $total) {
                $total = (string)$total;
            }
            $data[$totalFieldName] = $total;
        }
        if (!$currencyField->isExcluded()) {
            $data[$currencyFieldName] = $computedTotal->getCurrency();
        }
        if (!$subTotalField->isExcluded()) {
            $computedSubtotals = $this->totalProvider->getSubtotals($shoppingList);
            $subTotal = null;
            foreach ($computedSubtotals as $computedValue) {
                if ('subtotal' === $computedValue->getType()) {
                    $subTotal = $computedValue->getAmount();
                    break;
                }
            }
            if (null !== $subTotal) {
                $subTotal = (string)$subTotal;
            }
            $data[$subTotalFieldName] = $subTotal;
        }

        return $data;
    }
}
