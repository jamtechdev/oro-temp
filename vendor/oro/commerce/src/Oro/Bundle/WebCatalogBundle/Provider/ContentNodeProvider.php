<?php

namespace Oro\Bundle\WebCatalogBundle\Provider;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\ScopeBundle\Model\ScopeCriteria;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\ContentVariant;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This provider returns web catalog content nodes and content variants available for the storefront.
 */
class ContentNodeProvider
{
    public const ENTITY_ALIAS_PLACEHOLDER = '_entity_alias_';

    private const SCOPE_TYPE = 'web_content';

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var ScopeManager */
    private $scopeManager;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ScopeManager   $scopeManager
     */
    public function __construct(DoctrineHelper $doctrineHelper, ScopeManager $scopeManager)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->scopeManager = $scopeManager;
    }

    /**
     * Gets IDs of all nodes available for the storefront.
     *
     * @param QueryBuilder|null  $qb
     * @param ScopeCriteria|null $criteria
     *
     * @return int[]
     */
    public function getContentNodeIds(QueryBuilder $qb = null, ScopeCriteria $criteria = null): array
    {
        if (null === $criteria) {
            $criteria = $this->scopeManager->getCriteria(self::SCOPE_TYPE);
        }
        $webCatalog = $this->getWebCatalog($criteria);
        if (null === $webCatalog) {
            return [];
        }

        if (null === $qb) {
            $qb = $this->doctrineHelper->createQueryBuilder(ContentNode::class, 'node');
        }
        $rootAlias = QueryBuilderUtil::getSingleRootAlias($qb);
        $qb
            ->select(sprintf('%1$s.id, %1$s.right, %1$s.left', $rootAlias))
            ->andWhere($rootAlias . '.webCatalog = :webCatalog')
            ->setParameter('webCatalog', $webCatalog)
            ->orderBy($rootAlias . '.left');
        $nodeHierarchy = $qb->getQuery()->getArrayResult();
        if (!$nodeHierarchy) {
            return [];
        }

        $scopesToMatchQb = $this->doctrineHelper
            ->createQueryBuilder(ContentNode::class, 'node')
            ->select('node.id, node.parentScopeUsed, IDENTITY(node.parentNode) AS parentId, scope.id AS scopeId')
            ->innerJoin('node.scopes', 'scope')
            ->where('node.webCatalog = :webCatalog')
            ->setParameter('webCatalog', $webCatalog);
        $scopesToMatch = $this->loadScopesToMatch($scopesToMatchQb);

        return $this->getMatchedNodeIds($nodeHierarchy, $this->createMatcher($scopesToMatch, $criteria));
    }

    /**
     * Gets a node by its ID if it is available for the storefront.
     *
     * @param int                $id
     * @param ScopeCriteria|null $criteria
     *
     * @return ContentNode|null The requested node or NULL if the node does not exist
     *
     * @throws AccessDeniedException if the requested node is not available for the storefront
     */
    public function getContentNode(int $id, ScopeCriteria $criteria = null): ?ContentNode
    {
        if (null === $criteria) {
            $criteria = $this->scopeManager->getCriteria(self::SCOPE_TYPE);
        }
        if (null === $this->getWebCatalog($criteria)) {
            return null;
        }

        $node = $this->doctrineHelper->getEntityManagerForClass(ContentNode::class)
            ->find(ContentNode::class, $id);
        if (null === $node) {
            return null;
        }

        if (!$this->isContentNodeMatchCriteria($node, $criteria)) {
            throw new AccessDeniedException();
        }

        return $node;
    }

    /**
     * Gets IDs of content variants available for the storefront for given nodes.
     *
     * @param int[]              $nodeIds
     * @param ScopeCriteria|null $criteria
     *
     * @return array [node id => content variant id, ...]
     */
    public function getContentVariantIds(array $nodeIds, ScopeCriteria $criteria = null): array
    {
        if (null === $criteria) {
            $criteria = $this->scopeManager->getCriteria(self::SCOPE_TYPE);
        }
        if (null === $this->getWebCatalog($criteria)) {
            return [];
        }

        $qb = $this->doctrineHelper
            ->createQueryBuilder(ContentVariant::class, 'v')
            ->select('IDENTITY(v.node) AS nodeId, v.id')
            ->innerJoin('v.scopes', 'scope')
            ->where('v.node IN (:ids)')
            ->setParameter('ids', $nodeIds);
        $criteria->applyWhereWithPriority($qb, 'scope');

        $rows = $qb->getQuery()->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            if (!isset($result[$row['nodeId']])) {
                $result[$row['nodeId']] = $row['id'];
            }
        }

        return $result;
    }

    /**
     * Gets details of content variants available for the storefront for given nodes.
     *
     * @param int[]              $nodeIds
     * @param string[]           $contentVariantFields [DQL expression => result field name, ...]
     *                                                 use a value ENTITY_ALIAS_PLACEHOLDER constant as an alias
     *                                                 of ContentVariant entity in DQL expressions
     * @param ScopeCriteria|null $criteria
     *
     * @return array [node id => ['id' => content variant id, other requested fields], ...]
     */
    public function getContentVariantDetails(
        array $nodeIds,
        array $contentVariantFields,
        ScopeCriteria $criteria = null
    ): array {
        if (null === $criteria) {
            $criteria = $this->scopeManager->getCriteria(self::SCOPE_TYPE);
        }
        if (null === $this->getWebCatalog($criteria)) {
            return [];
        }

        $selectExpr = 'IDENTITY(v.node) AS nodeId, v.id';
        foreach ($contentVariantFields as $expr => $fieldAlias) {
            $selectExpr .= sprintf(
                ', %s AS %s',
                str_replace(self::ENTITY_ALIAS_PLACEHOLDER, 'v', $expr),
                $fieldAlias
            );
        }
        $qb = $this->doctrineHelper
            ->createQueryBuilder(ContentVariant::class, 'v')
            ->select($selectExpr)
            ->innerJoin('v.scopes', 'scope')
            ->where('v.node IN (:ids)')
            ->setParameter('ids', $nodeIds);
        $criteria->applyWhereWithPriority($qb, 'scope');

        $rows = $qb->getQuery()->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            if (!isset($result[$row['nodeId']])) {
                $details = ['id' => $row['id']];
                foreach ($contentVariantFields as $expr => $fieldAlias) {
                    $details[$fieldAlias] = $row[$fieldAlias];
                }
                $result[$row['nodeId']] = $details;
            }
        }

        return $result;
    }

    /**
     * @param array                         $nodeHierarchy [['id' => node id, 'right' => right, 'right' => left], ...]
     * @param MatcherForContentNodeProvider $matcher
     *
     * @return array
     */
    private function getMatchedNodeIds(array $nodeHierarchy, MatcherForContentNodeProvider $matcher): array
    {
        $nodeIds = [];
        $skipRight = null;
        $skipLeft = null;
        foreach ($nodeHierarchy as $item) {
            $nodeId = $item['id'];
            $right = $item['right'];
            $left = $item['left'];
            if (null !== $skipLeft) {
                if ($right < $skipRight && $left > $skipLeft) {
                    // do not add a child node of not matched parent node to the list of node IDs
                    continue;
                }
                $skipRight = null;
                $skipLeft = null;
            }
            if (null === $skipLeft) {
                if ($matcher->isContentNodeMatchCriteria($nodeId)) {
                    $nodeIds[] = $nodeId;
                } else {
                    // do not matched parent node to the list of node IDs
                    // and remember its left and right pointers that are used to process child nodes
                    $skipRight = $right;
                    $skipLeft = $left;
                }
            }
        }

        return $nodeIds;
    }

    /**
     * @param ContentNode   $node
     * @param ScopeCriteria $criteria
     *
     * @return bool
     */
    private function isContentNodeMatchCriteria(ContentNode $node, ScopeCriteria $criteria): bool
    {
        $scopesToMatchQb = $this->doctrineHelper
            ->createQueryBuilder(ContentNode::class, 'node')
            ->select('node.id, node.parentScopeUsed, IDENTITY(node.parentNode) AS parentId, scope.id AS scopeId')
            ->innerJoin('node.scopes', 'scope')
            ->setParameter('root', $node->getRoot())
            ->setParameter('right', $node->getRight())
            ->setParameter('left', $node->getLeft());
        $scopesToMatchQb->where($scopesToMatchQb->expr()->andX(
            $scopesToMatchQb->expr()->eq('node.root', ':root'),
            $scopesToMatchQb->expr()->gte('node.right', ':right'),
            $scopesToMatchQb->expr()->lte('node.left', ':left')
        ));
        $scopesToMatch = $this->loadScopesToMatch($scopesToMatchQb);
        if (!$scopesToMatch) {
            return false;
        }

        $matcher = $this->createMatcher($scopesToMatch, $criteria);
        foreach ($scopesToMatch as $nodeId => $scopeIds) {
            if (!$matcher->isContentNodeMatchCriteria($nodeId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param QueryBuilder $scopesToMatchQb
     *
     * @return array [node id => [scope id, ...], ...]
     */
    private function loadScopesToMatch(QueryBuilder $scopesToMatchQb): array
    {
        $result = [];
        $rows = $scopesToMatchQb->getQuery()->getArrayResult();
        foreach ($rows as $row) {
            if (!$row['parentScopeUsed'] || null === $row['parentId']) {
                $result[$row['id']][] = $row['scopeId'];
            }
        }

        return $result;
    }

    /**
     * @param array         $scopesToMatch [node id => [scope id, ...], ...]
     * @param ScopeCriteria $criteria
     *
     * @return MatcherForContentNodeProvider
     */
    private function createMatcher(array $scopesToMatch, ScopeCriteria $criteria): MatcherForContentNodeProvider
    {
        return new MatcherForContentNodeProvider(
            $scopesToMatch,
            $criteria,
            $this->doctrineHelper,
            $this->scopeManager,
            self::SCOPE_TYPE
        );
    }

    /**
     * @param ScopeCriteria $criteria
     *
     * @return WebCatalog|null
     */
    private function getWebCatalog(ScopeCriteria $criteria): ?WebCatalog
    {
        $parameters = $criteria->toArray();

        return $parameters[ScopeWebCatalogProvider::WEB_CATALOG] ?? null;
    }
}
