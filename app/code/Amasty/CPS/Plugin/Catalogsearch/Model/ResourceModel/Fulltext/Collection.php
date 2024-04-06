<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Plugin\Catalogsearch\Model\ResourceModel\Fulltext;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Amasty\CPS\Model\OptionSetting\IsUdeDefaultStoreSettings;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Store\Model\Store;

class Collection
{
    public const POSITION_COLUMN_NAME = 'cat_index_position';

    public const COLUMN_NAME_INDEX = 2;

    /**
     * @var bool
     */
    private $isPositionIndexJoinApplied = false;

    /**
     * @var \Amasty\ShopbyBrand\Model\BrandResolver
     */
    private $brandResolver;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsUdeDefaultStoreSettings
     */
    private $isUdeDefaultStoreSettings;

    public function __construct(
        \Amasty\ShopbyBrand\Model\BrandResolver $brandResolver,
        \Amasty\Base\Model\MagentoVersion $magentoVersion,
        ConfigProvider $configProvider,
        IsUdeDefaultStoreSettings $isUdeDefaultStoreSettings
    ) {
        $this->brandResolver = $brandResolver;
        $this->magentoVersion = $magentoVersion;
        $this->configProvider = $configProvider;
        $this->isUdeDefaultStoreSettings = $isUdeDefaultStoreSettings;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function afterAddCategoryFilter($collection)
    {
        if ($this->brandResolver->getCurrentBrand()) {
            $this->addPositionIndexJoin($collection);
        }

        return $collection;
    }

    /**
     * @param $collection
     * @return $this
     */
    private function addPositionIndexJoin($collection)
    {
        if (!$this->isPositionIndexJoinApplied) {
            /**
             * @var \Magento\Framework\DB\Select $select
             */
            $select = $collection->getSelect();
            $brand = $this->brandResolver->getCurrentBrand();
            $columns = $select->getPart(\Magento\Framework\DB\Select::COLUMNS);
            foreach ($columns as $index => $column) {
                if (isset($column[self::COLUMN_NAME_INDEX])
                    && $column[self::COLUMN_NAME_INDEX] == self::POSITION_COLUMN_NAME
                ) {
                    unset($columns[$index]);
                    break;
                }
            }
            $select->setPart(\Magento\Framework\DB\Select::COLUMNS, $columns);
            $table = BrandProductInterface::MAIN_TABLE;

            if (version_compare($this->magentoVersion->get(), '2.3.2', '>=')) {
                $positionExpr = new \Zend_Db_Expr("IFNULL(brand_product_index.position, 0)");
            } else {
                $positionExpr = new \Zend_Db_Expr("IFNULL(brand_product_index.position, cat_index.position)");
            }

            $select->joinInner(
                ['brand_product_index' => $collection->getResource()->getTable($table)],
                'brand_product_index.product_id = e.entity_id'
                . ' AND brand_product_index.ambrand_id = ' . $brand->getValue()
                . ' AND brand_product_index.store_id = ' . $this->getStoreIdForSelect($collection, $brand),
                ['cat_index_position' => $positionExpr]
            );
            $this->isPositionIndexJoinApplied = true;
        }

        return $this;
    }

    private function getStoreIdForSelect(AbstractCollection $collection, OptionSettingInterface $brand): int
    {
        $currentStoreId = (int)$collection->getStoreId();
        $isUseDefaultStoreId = $this->isUdeDefaultStoreSettings->execute($currentStoreId, $brand);

        return $isUseDefaultStoreId ? Store::DEFAULT_STORE_ID : $currentStoreId;
    }

    /**
     * @param $collection
     * @param callable $proceed
     * @param $attribute
     * @param $dir
     * @return mixed
     */
    public function aroundAddAttributeToSort(
        $collection,
        callable $proceed,
        $attribute,
        $dir = AbstractCollection::SORT_ORDER_ASC
    ) {
        if ($this->brandResolver->getCurrentBrand() && $attribute == 'position') {
            $this->addPositionIndexJoin($collection);
            $collection->getSelect()->order("cat_index_position {$dir}");
        }

        return $proceed($attribute, $dir);
    }
}
