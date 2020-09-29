<?php

namespace Oro\Bundle\CatalogBundle\ImportExport\TemplateFixture;

use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProvider;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * Fixture of Category entity used for generation of import-export template
 */
class CategoryFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /** @var MasterCatalogRootProvider */
    private $masterCatalogRootProvider;

    /** @var LocalizationManager */
    private $localizationManager;

    /**
     * @param LocalizationManager $localizationManager
     */
    public function __construct(LocalizationManager $localizationManager)
    {
        $this->localizationManager = $localizationManager;
    }

    /**
     * @param MasterCatalogRootProvider $masterCatalogRootProvider
     */
    public function setMasterCatalogRootProvider(MasterCatalogRootProvider $masterCatalogRootProvider): void
    {
        $this->masterCatalogRootProvider = $masterCatalogRootProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Category::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Sample Category');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Category();
    }

    /**
     * @param string $key
     * @param Category $entity
     */
    public function fillEntityData($key, $entity)
    {
        $organizationRepo = $this->templateManager->getEntityRepository(Organization::class);

        if ($key === 'Sample Category') {
            $localization = $this->localizationManager->getDefaultLocalization();
            $entity
                ->setParentCategory($this->masterCatalogRootProvider->getMasterCatalogRootForCurrentOrganization())
                ->addTitle((new LocalizedFallbackValue())->setString('Sample Category'))
                ->addTitle(
                    (new LocalizedFallbackValue())
                        ->setString('Sample Category English')
                        ->setLocalization($localization)
                )
                ->addShortDescription((new LocalizedFallbackValue())->setText('Sample short description'))
                ->addLongDescription((new LocalizedFallbackValue())->setWysiwyg('Sample long description'))
                ->setOrganization($organizationRepo->getEntity('default'));

            return;
        }

        parent::fillEntityData($key, $entity);
    }
}
