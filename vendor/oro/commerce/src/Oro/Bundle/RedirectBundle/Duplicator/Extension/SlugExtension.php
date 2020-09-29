<?php

namespace Oro\Bundle\RedirectBundle\Duplicator\Extension;

use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Filter\Filter;
use DeepCopy\Matcher\Matcher;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\DraftBundle\Duplicator\Extension\AbstractDuplicatorExtension;
use Oro\Bundle\DraftBundle\Duplicator\Matcher\PropertiesNameMatcher;
use Oro\Bundle\DraftBundle\Entity\DraftableInterface;
use Oro\Bundle\RedirectBundle\Entity\Slug;
use Symfony\Component\Security\Acl\Util\ClassUtils;

/**
 * Responsible for modifying the Slug type parameter.
 */
class SlugExtension extends AbstractDuplicatorExtension
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return Filter
     */
    public function getFilter(): Filter
    {
        return new DoctrineCollectionFilter();
    }

    /**
     * @return Matcher
     */
    public function getMatcher(): Matcher
    {
        $source = $this->getContext()->offsetGet('source');
        $properties = $this->getSlugProperties($source);

        return new PropertiesNameMatcher($properties);
    }

    /**
     * @param DraftableInterface $source
     *
     * @return bool
     */
    public function isSupport(DraftableInterface $source): bool
    {
        $properties = $this->getSlugProperties($source);

        return !empty($properties);
    }

    /**
     * @param DraftableInterface $source
     *
     * @return array
     */
    private function getSlugProperties(DraftableInterface $source): array
    {
        $properties = [];
        $em = $this->managerRegistry->getManager();
        /** @var ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(ClassUtils::getRealClass($source));
        foreach ($metadata->getAssociationMappings() as $name => $fieldMapping) {
            if ($fieldMapping['targetEntity'] === Slug::class) {
                $properties[] = $name;
            }
        }

        return $properties;
    }
}
