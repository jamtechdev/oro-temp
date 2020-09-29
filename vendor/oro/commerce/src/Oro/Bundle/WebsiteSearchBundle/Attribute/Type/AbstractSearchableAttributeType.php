<?php

namespace Oro\Bundle\WebsiteSearchBundle\Attribute\Type;

use Oro\Bundle\EntityConfigBundle\Attribute\Type\AttributeTypeInterface;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\LocaleBundle\Entity\Localization;

/**
 * Abstract implementation of searchable attribute type.
 * Contains base implementation of methods of AttributeConfigurationInterface and AttributeValueInterface.
 */
abstract class AbstractSearchableAttributeType implements SearchAttributeTypeInterface
{
    /** @var AttributeTypeInterface */
    protected $attributeType;

    /**
     * @param AttributeTypeInterface $attributeType
     */
    public function __construct(AttributeTypeInterface $attributeType)
    {
        $this->attributeType = $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchable(FieldConfigModel $attribute = null)
    {
        return $this->attributeType->isSearchable($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable(FieldConfigModel $attribute = null)
    {
        return $this->attributeType->isFilterable($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable(FieldConfigModel $attribute = null)
    {
        return $this->attributeType->isSortable($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableValue(FieldConfigModel $attribute, $originalValue, Localization $localization = null)
    {
        return $this->attributeType->getSearchableValue($attribute, $originalValue, $localization);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterableValue(FieldConfigModel $attribute, $originalValue, Localization $localization = null)
    {
        return $this->attributeType->getFilterableValue($attribute, $originalValue, $localization);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortableValue(FieldConfigModel $attribute, $originalValue, Localization $localization = null)
    {
        return $this->attributeType->getSortableValue($attribute, $originalValue, $localization);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterableFieldNames(FieldConfigModel $attribute): array
    {
        $names = array_filter([
            static::VALUE_MAIN => $this->getFilterableFieldNameMain($attribute),
            static::VALUE_AGGREGATE => $this->getFilterableFieldNameAggregate($attribute),
        ]);

        if (!$names) {
            throw new \LogicException('Main filterable field name `SearchAttributeTypeInterface::VALUE_MAIN` is '
                .' required for filtering! Either provide it by overriding '
                .' `AbstractSearchableAttributeType::getFilterableFieldNameMain()` or make your own implementation of'
                .' `SearchAttributeTypeInterface::getFilterableFieldNames()` to avoid this exception if filtering '
                .' is not supported by your attribute type.');
        }

        return $names;
    }

    /**
     * Returns main filterable field name which is used for filtering.
     *
     * @param FieldConfigModel $attribute
     *
     * @return string
     */
    protected function getFilterableFieldNameMain(FieldConfigModel $attribute): string
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * Returns aggregate filterable field name which is used for aggregation.
     * Empty by default, which means aggregation is not supported by default.
     *
     * @param FieldConfigModel $attribute
     *
     * @return string
     */
    protected function getFilterableFieldNameAggregate(FieldConfigModel $attribute): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterStorageFieldTypes(): array
    {
        $types = array_filter([
            static::VALUE_MAIN => $this->getFilterStorageFieldTypeMain(),
            static::VALUE_AGGREGATE => $this->getFilterStorageFieldTypeAggregate(),
        ]);

        if (!$types) {
            throw new \LogicException('Main filter storage field type `SearchAttributeTypeInterface::VALUE_MAIN` is '
                .' required for filtering! Either provide it by overriding '
                .' `AbstractSearchableAttributeType::getFilterStorageFieldTypeMain()` or make your own implementation'
                .' of `SearchAttributeTypeInterface::getFilterStorageFieldTypes()` to avoid this exception if '
                .' filtering is not supported by your attribute type.');
        }

        return $types;
    }

    /**
     * Returns main filter storage field type which is used for filtering.
     *
     * @return string
     */
    protected function getFilterStorageFieldTypeMain(): string
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * Returns aggregate filter storage field type which is used for aggregation.
     * Empty by default, which means aggregation is not supported by default.
     *
     * @return string
     */
    protected function getFilterStorageFieldTypeAggregate(): string
    {
        return '';
    }
}
