<?php

namespace Oro\Bundle\ProductBundle\Action\Condition;

use Oro\Bundle\ProductBundle\Entity\Manager\ProductManager;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\ProductBundle\Helper\ProductHolderTrait;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Condition checks that at least one product is available in product holder
 */
class AtLeastOneAvailableProduct extends AbstractCondition implements ContextAccessorAwareInterface
{
    const NAME = 'at_least_one_available_product';

    use ContextAccessorAwareTrait, ProductHolderTrait;

    /** @var PropertyPathInterface */
    private $productIteratorPath;

    /** @var ProductManager */
    private $productManager;

    /** @var ProductRepository */
    private $productRepository;

    /** @var AclHelper */
    private $aclHelper;

    /**
     * @param ProductRepository $productRepository
     * @param ProductManager $productManager
     * @param AclHelper $aclHelper
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductManager $productManager,
        AclHelper $aclHelper
    ) {
        $this->productManager = $productManager;
        $this->productRepository = $productRepository;
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        $propertyPath = reset($options);
        if ($propertyPath instanceof PropertyPathInterface) {
            $this->productIteratorPath = $propertyPath;
        }
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function isConditionAllowed($context)
    {
        $productHolderIterator = $this->resolveValue($context, $this->productIteratorPath);
        $products = $this->getProductIdsFromProductHolders($productHolderIterator);

        if (count($products) > 0) {
            $queryBuilder = $this->productRepository->getProductsQueryBuilder($products);
            $this->productManager->restrictQueryBuilder($queryBuilder, []);
            $products = $this->aclHelper->apply($queryBuilder)->getResult();
        }

        return count($products) > 0;
    }
}
