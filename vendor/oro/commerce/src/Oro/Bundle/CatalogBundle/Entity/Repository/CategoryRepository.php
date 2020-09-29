<?php

namespace Oro\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Provides methods to retrieve information about Category entity form the DB
 *
 * @method QueryBuilder childrenQueryBuilder()
 */
class CategoryRepository extends NestedTreeRepository
{
    /**
     * @param Organization $organization
     * @return Category
     */
    public function getMasterCatalogRoot(Organization $organization)
    {
        $qb = $this->createQueryBuilder('category')
            ->andWhere('category.parentCategory IS NULL')
            ->orderBy('category.id', 'ASC');
        $qb->andWhere('category.organization = :organization');
        $qb->setParameter('organization', $organization);

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param object|null $node
     * @param bool $direct
     * @param string|null $sortByField
     * @param string $direction
     * @param bool $includeNode
     * @return QueryBuilder
     */
    public function getChildrenQueryBuilderPartial(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'ASC',
        $includeNode = false
    ) {
        return $this->getChildrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode)
            ->select('partial node.{id, parentCategory, materializedPath, left, level, right, root}');
    }

    /**
     * @param object|null $node
     * @param bool $direct
     * @param string|null $sortByField
     * @param string $direction
     * @param bool $includeNode
     * @return Category[]
     */
    public function getChildren(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'ASC',
        $includeNode = false
    ) {
        return $this->getChildrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode)
            ->addSelect('children')
            ->leftJoin('node.childCategories', 'children')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Category $category
     * @return array
     */
    public function getChildrenIds(Category $category)
    {
        $result = $this->childrenQueryBuilder($category)
            ->select('node.id')
            ->getQuery()
            ->getScalarResult();

        return array_map('current', $result);
    }

    /**
     * @param string $title
     * @param Organization $organization
     * @return Category|null
     */
    public function findOneByDefaultTitle(string $title, Organization $organization)
    {
        $qb = $this->createQueryBuilder('category');

        return $qb
            ->select('partial category.{id}')
            ->andWhere('category.denormalizedDefaultTitle = :title')
            ->andWhere('category.organization = :organization')
            ->setParameter('title', $title)
            ->setParameter('organization', $organization)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $title
     * @param Organization $organization
     * @param Category|null $parentCategory
     *
     * @return Category|null
     */
    public function findOneOrNullByDefaultTitleAndParent(
        string $title,
        Organization $organization,
        ?Category $parentCategory = null
    ): ?Category {
        $qb = $this->createQueryBuilder('category');

        try {
            $qb
                ->select('partial category.{id}')
                ->andWhere('category.denormalizedDefaultTitle = :title')
                ->andWhere('category.organization = :organization')
                ->setParameter('title', $title)
                ->setParameter('organization', $organization);

            if ($parentCategory !== null) {
                $qb
                    ->andWhere('category.parentCategory = :category')
                    ->setParameter('category', $parentCategory);
            }

            $category = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $exception) {
            $category = null;
        }

        return $category;
    }

    /**
     * @param Category $category
     *
     * @return string[]
     */
    public function getCategoryPath(Category $category): array
    {
        $qb = $this->getPathQueryBuilder($category);
        $qb->select('node.denormalizedDefaultTitle as title');

        return array_column($qb->getQuery()->getScalarResult(), 'title');
    }

    /**
     * @param Product $product
     * @return Category|null
     */
    public function findOneByProduct(Product $product)
    {
        if (!$product->getId()) {
            return null;
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select('category as cat')
            ->from(Product::class, 'product')
            ->innerJoin($this->_entityName, 'category', Join::WITH, 'category = product.category')
            ->where('product = :product')
            ->setParameter('product', $product)
            ->setMaxResults(1);
        $result = $qb->getQuery()
            ->getResult();
        if (count($result) > 0) {
            return $result[0]['cat'];
        }
        return null;
    }

    /**
     * @param string $productSku
     * @param bool $includeTitles
     * @return QueryBuilder
     */
    public function findOneByProductSkuQueryBuilder($productSku, $includeTitles = false)
    {
        $qb = $this->createQueryBuilder('category');

        if ($includeTitles) {
            $qb->addSelect('title.string');
            $qb->leftJoin('category.titles', 'title', Join::WITH, $qb->expr()->isNull('title.localization'));
        }

        $qb
            ->select('partial category.{id}')
            ->innerJoin('category.products', 'p', Join::WITH, $qb->expr()->eq('p.sku', ':sku'))
            ->setParameter('sku', $productSku)
            ->setMaxResults(1);

        return $qb;
    }

    /**
     * @param string $productSku
     * @param bool $includeTitles
     * @return null|Category
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByProductSku($productSku, $includeTitles = false)
    {
        $qb = $this->findOneByProductSkuQueryBuilder($productSku, $includeTitles);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Category $category
     * @return Category[]
     */
    public function getAllChildCategories(Category $category)
    {
        return $this->getChildrenQueryBuilderPartial($category)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Category[] $categories
     * @return array
     */
    public function getProductIdsByCategories(array $categories)
    {
        $qb = $this->_em->createQueryBuilder();
        $productIds = $qb->select('product.id')
            ->from(Product::class, 'product')
            ->where($qb->expr()->in('product.category', ':categories'))
            ->setParameter('categories', $categories)
            ->orderBy($qb->expr()->asc('product.id'))
            ->getQuery()
            ->getScalarResult();

        return array_column($productIds, 'id');
    }

    /**
     * Creates product to category map, [product_id => Category, ...]
     * @param Product[] $products
     * @param Localization[] $localizations
     * @return Category[]
     */
    public function getCategoryMapByProducts(array $products, array $localizations = [])
    {
        $builder = $this->_em->createQueryBuilder();
        $builder
            ->from(Product::class, 'product')
            ->innerJoin($this->_entityName, 'category', 'WITH', 'product.category = category')
            ->andWhere($builder->expr()->in('product', ':products'))
            ->setParameter('products', $products);

        $builder->select('category as cat');
        $builder->addSelect('product.id as productId');
        $builder->addSelect('category.id as categoryId');

        $results = $builder->getQuery()->getArrayResult();

        $categoryMap = [];
        $productCategoryMap = [];
        foreach ($results as $result) {
            $categoryMap[$result['cat']['id']] = $this->find($result['cat']['id']);
            $productCategoryMap[$result['productId']] = $categoryMap[$result['categoryId']];
        }

        return $productCategoryMap;
    }

    /**
     * @param Category $category
     */
    public function updateMaterializedPath(Category $category)
    {
        $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'category')
            ->set('category.materializedPath', ':newPath')
            ->where('category.id = :category')
            ->setParameter('category', $category)
            ->setParameter('newPath', $category->getMaterializedPath())
            ->getQuery()
            ->execute();
    }

    /**
     * Gets max value of Gedmo tree "left" field.
     *
     * @return int
     */
    public function getMaxLeft(): int
    {
        $qb = $this->createQueryBuilder('category');

        return $qb
            ->select($qb->expr()->max('category.left'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
