<?php

namespace Oro\Bundle\WebsiteSearchBundle\Attribute\Type;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\SearchBundle\Query\Query;

/**
 * Attribute type provides metadata for decimal attribute for search index.
 */
class DecimalSearchableAttributeType extends AbstractSearchableAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function getFilterStorageFieldTypeMain(): string
    {
        return Query::TYPE_DECIMAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getSorterStorageFieldType(): string
    {
        return Query::TYPE_DECIMAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterType(): string
    {
        return self::FILTER_TYPE_NUMBER_RANGE;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizable(FieldConfigModel $attribute): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterableFieldNameMain(FieldConfigModel $attribute): string
    {
        return $attribute->getFieldName();
    }

    /**
     * {@inheritdoc}
     */
    public function getSortableFieldName(FieldConfigModel $attribute): string
    {
        return $attribute->getFieldName();
    }
}
