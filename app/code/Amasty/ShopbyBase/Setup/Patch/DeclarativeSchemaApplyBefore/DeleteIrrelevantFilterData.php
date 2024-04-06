<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Clean up irrelevant data before adding foreign keys
 */
class DeleteIrrelevantFilterData implements DataPatchInterface
{
    /**
     * @var FilterSetting
     */
    private $filtersResource;

    public function __construct(
        FilterSetting $filtersResource
    ) {
        $this->filtersResource = $filtersResource;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        if ($this->filtersResource->getConnection()->isTableExists($this->filtersResource->getMainTable())) {
            $this->clearFilters();
        }

        return $this;
    }

    private function clearFilters(): void
    {
        $connection = $this->filtersResource->getConnection();

        $select = $connection->select()->from(
            $this->filtersResource->getTable('eav_attribute'),
            ['attribute_code']
        );
        $connection->delete(
            $this->filtersResource->getMainTable(),
            $connection->quoteInto('attribute_code NOT IN(?)', $select)
        );
    }

    public static function getDependencies(): array
    {
        return [\Amasty\ShopbyBase\Setup\Patch\Data\FillAttributeCodeColumn::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
