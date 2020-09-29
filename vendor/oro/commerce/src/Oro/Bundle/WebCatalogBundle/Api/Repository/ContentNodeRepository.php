<?php

namespace Oro\Bundle\WebCatalogBundle\Api\Repository;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Provider\ContentNodeProvider;
use Oro\Component\EntitySerializer\EntitySerializer;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * The repository to get web catalog tree nodes available for the storefront.
 */
class ContentNodeRepository
{
    /** @var ContentNodeProvider */
    private $contentNodeProvider;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var EntitySerializer */
    private $entitySerializer;

    /**
     * @param ContentNodeProvider $contentNodeProvider
     * @param DoctrineHelper      $doctrineHelper
     * @param EntitySerializer    $entitySerializer
     */
    public function __construct(
        ContentNodeProvider $contentNodeProvider,
        DoctrineHelper $doctrineHelper,
        EntitySerializer $entitySerializer
    ) {
        $this->contentNodeProvider = $contentNodeProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->entitySerializer = $entitySerializer;
    }

    /**
     * Gets all nodes available for the storefront.
     *
     * @param QueryBuilder           $qb
     * @param EntityDefinitionConfig $config
     * @param array                  $normalizationContext
     *
     * @return array
     */
    public function getContentNodes(
        QueryBuilder $qb,
        EntityDefinitionConfig $config,
        array $normalizationContext
    ): array {
        $nodeIds = $this->contentNodeProvider->getContentNodeIds($qb);
        if (!$nodeIds) {
            return [];
        }

        $nodesQb = $this->doctrineHelper
            ->createQueryBuilder(ContentNode::class, 'node')
            ->where('node.id IN (:ids)')
            ->setParameter('ids', $nodeIds)
            ->orderBy('node.left');

        return $this->entitySerializer->serialize($nodesQb, $config, $normalizationContext);
    }

    /**
     * Gets a node by its ID.
     *
     * @param int                    $id
     * @param EntityDefinitionConfig $config
     * @param array                  $normalizationContext
     *
     * @return array|null The normalized data for the requested node or NULL if the node does not exist
     *
     * @throws AccessDeniedException if the requested node is not available for the storefront
     */
    public function getContentNode(
        int $id,
        EntityDefinitionConfig $config,
        array $normalizationContext
    ): ?array {
        $node = $this->contentNodeProvider->getContentNode($id);
        if (null === $node) {
            return null;
        }

        $serializedNodes = $this->entitySerializer->serializeEntities(
            [$node],
            ContentNode::class,
            $config,
            $normalizationContext
        );

        return reset($serializedNodes);
    }

    /**
     * Gets a node entity by its ID.
     *
     * @param int $id
     *
     * @return ContentNode|null The requested node or NULL if the node does not exist
     *
     * @throws AccessDeniedException if the requested node is not available for the storefront
     */
    public function getContentNodeEntity(int $id): ?ContentNode
    {
        return $this->contentNodeProvider->getContentNode($id);
    }
}
