<?php

namespace Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityBundle\Entity\EntityFieldFallbackValue;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Tests\Functional\DataFixtures\LoadAttributeFamilyData;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductImage;
use Oro\Bundle\ProductBundle\Entity\ProductImageType;
use Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use Oro\Bundle\ProductBundle\Migrations\Data\ORM\LoadProductDefaultAttributeFamilyData;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract class for load Products fixtures
 */
abstract class AbstractLoadProductData extends AbstractFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadLocalizationData::class,
            LoadProductUnits::class,
            LoadAttributeFamilyData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $inventoryStatusClassName = ExtendHelper::buildEnumValueClassName('prod_inventory_status');
        /** @var AbstractEnumValue[] $enumInventoryStatuses */
        $enumInventoryStatuses = $manager->getRepository($inventoryStatusClassName)->findAll();

        $inventoryStatuses = [];
        foreach ($enumInventoryStatuses as $inventoryStatus) {
            $inventoryStatuses[$inventoryStatus->getId()] = $inventoryStatus;
        }

        $data = Yaml::parse(file_get_contents($this->getFilePath()));
        $defaultAttributeFamily = $this->getDefaultAttributeFamily($manager);
        $this->setReference(LoadProductDefaultAttributeFamilyData::DEFAULT_FAMILY_CODE, $defaultAttributeFamily);

        foreach ($data as $referenceName => $item) {
            if (isset($item['user'])) {
                /** @var User $user */
                $user = $this->getReference($item['user']);
            } else {
                /** @var EntityManager $manager */
                $user = $this->getFirstUser($manager);
            }

            $businessUnit = $user->getOwner();
            $organization = $user->getOrganization();

            $unit = $this->getReference('product_unit.milliliter');

            $unitPrecision = new ProductUnitPrecision();
            $unitPrecision->setUnit($unit)
                ->setPrecision((int)$item['primaryUnitPrecision']['precision'])
                ->setConversionRate(1)
                ->setSell(true);

            $product = new Product();
            $product
                ->setSku($item['productCode'])
                ->setOwner($businessUnit)
                ->setOrganization($organization)
                ->setAttributeFamily($defaultAttributeFamily)
                ->setInventoryStatus($inventoryStatuses[$item['inventoryStatus']])
                ->setStatus($item['status'])
                ->setPrimaryUnitPrecision($unitPrecision)
                ->setType($item['type'])
                ->setFeatured($item['featured']);

            if (isset($item['attributeFamily'])) {
                $product->setAttributeFamily($this->getReference($item['attributeFamily']));
            }

            $this->addAdvancedValue($item, $product);
            $this->addEntityFieldFallbackValue($item, $product);
            $this->addProductImages($item, $product);

            $manager->persist($product);
            $this->addReference($product->getSku(), $product);
            $this->addReference(
                sprintf('product_unit_precision.%s', implode('.', [$product->getSku(), $unit->getCode()])),
                $unitPrecision
            );
        }

        $manager->flush();
    }

    /**
     * @return string
     */
    abstract protected function getFilePath(): string;

    /**
     * @param array $name
     * @return LocalizedFallbackValue
     */
    protected function createValue(array $name)
    {
        $value = new LocalizedFallbackValue();
        if (array_key_exists('localization', $name)) {
            /** @var Localization $localization */
            $localization = $this->getReference($name['localization']);
            $value->setLocalization($localization);
        }
        if (array_key_exists('fallback', $name)) {
            $value->setFallback($name['fallback']);
        }
        if (array_key_exists('string', $name)) {
            $value->setString($name['string']);
        }
        if (array_key_exists('text', $name)) {
            $value->setText($name['text']);
        }
        $this->setReference($name['reference'], $value);

        return $value;
    }

    /**
     * @param array $name
     * @return EntityFieldFallbackValue
     */
    private function createFieldFallbackValue(array $name)
    {
        $value = new EntityFieldFallbackValue();
        if (array_key_exists('fallback', $name)) {
            $value->setFallback($name['fallback']);
        }
        if (array_key_exists('scalarValue', $name)) {
            $value->setScalarValue($name['scalarValue']);
        }
        if (array_key_exists('arrayValue', $name)) {
            $value->setArrayValue($name['arrayValue']);
        }
        $this->setReference($name['reference'], $value);

        return $value;
    }

    /**
     * @param ObjectManager $manager
     * @return AttributeFamily|null
     */
    private function getDefaultAttributeFamily(ObjectManager $manager)
    {
        $familyRepository = $manager->getRepository(AttributeFamily::class);

        return $familyRepository->findOneBy(['code' => LoadProductDefaultAttributeFamilyData::DEFAULT_FAMILY_CODE]);
    }

    /**
     * @param array $item
     * @param Product $product
     */
    private function addAdvancedValue(array $item, Product $product)
    {
        if (!empty($item['names'])) {
            foreach ($item['names'] as $name) {
                $product->addName($this->createValue($name));
            }
        }

        if (!empty($item['slugPrototypes'])) {
            foreach ($item['slugPrototypes'] as $slugPrototype) {
                $product->addSlugPrototype($this->createValue($slugPrototype));
            }
        }

        if (!empty($item['descriptions'])) {
            foreach ($item['descriptions'] as $description) {
                $product->addDescription($this->createValue($description));
            }
        }

        if (!empty($item['shortDescriptions'])) {
            foreach ($item['shortDescriptions'] as $shortDescription) {
                $product->addShortDescription($this->createValue($shortDescription));
            }
        }
    }

    /**
     * @param array $item
     * @param Product $product
     */
    private function addProductImages(array $item, Product $product)
    {
        if (empty($item['images'])) {
            return;
        }

        foreach ($item['images'] as $image) {
            $imageFile = new File();
            $imageFile->setFilename($item['productCode'] . '.jpg');
            $imageFile->setOriginalFilename($item['productCode'] . '-original.jpg');
            $imageFile->setExtension('jpg');
            $imageFile->setParentEntityClass(ProductImage::class);
            $imageFile->setMimeType('image/jpeg');
            $this->setReference($image['reference'] . '.' . $item['productCode'], $imageFile);

            $productImage = new ProductImage();
            $productImage->setImage($imageFile);

            $productType = $image['type'] ?? ProductImageType::TYPE_LISTING;
            $productImage->addType($productType);

            $product->addImage($productImage);
        }
    }

    /**
     * @param array $item
     * @param Product $product
     */
    private function addEntityFieldFallbackValue(array $item, Product $product)
    {
        if (!empty($item['manageInventory'])) {
            $product->setManageInventory($this->createFieldFallbackValue($item['manageInventory']));
        }

        if (!empty($item['inventoryThreshold'])) {
            $product->setInventoryThreshold($this->createFieldFallbackValue($item['inventoryThreshold']));
        }

        if (!empty($item['minimumQuantityToOrder'])) {
            $product->setMinimumQuantityToOrder($this->createFieldFallbackValue($item['minimumQuantityToOrder']));
        }

        if (!empty($item['maximumQuantityToOrder'])) {
            $product->setMaximumQuantityToOrder($this->createFieldFallbackValue($item['maximumQuantityToOrder']));
        }

        if (!empty($item['decrementQuantity'])) {
            $product->setDecrementQuantity($this->createFieldFallbackValue($item['decrementQuantity']));
        }

        if (!empty($item['backOrder'])) {
            $product->setBackOrder($this->createFieldFallbackValue($item['backOrder']));
        }
    }
}
