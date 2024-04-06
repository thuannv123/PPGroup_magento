<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Plugin\ElasticSearch\Model;

use Amasty\CPS\Model\OptionSetting\IsUdeDefaultStoreSettings;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionSettingCollectionFactory;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Store\Model\Store;

class BrandProductProvider
{
    /**
     * @var  Repository
     */
    protected $repository;

    /**
     * @var OptionSettingCollectionFactory
     */
    private $optionSettingCollectionFactory;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSettingFactory
     */
    private $optionSettingFactory;

    /**
     * @var \Amasty\CPS\Model\ResourceModel\BrandProduct
     */
    private $brandProductResource;

    /**
     * @var array
     */
    private $settingByValue = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsUdeDefaultStoreSettings
     */
    private $isUdeDefaultStoreSettings;

    public function __construct(
        Repository $repository,
        \Amasty\ShopbyBase\Model\OptionSettingFactory $optionSettingFactory,
        OptionSettingCollectionFactory $optionSettingCollectionFactory,
        ConfigProvider $configProvider,
        \Amasty\CPS\Model\ResourceModel\BrandProduct $brandProductResource,
        IsUdeDefaultStoreSettings $isUdeDefaultStoreSettings
    ) {
        $this->repository = $repository;
        $this->configProvider = $configProvider;
        $this->optionSettingCollectionFactory = $optionSettingCollectionFactory;
        $this->optionSettingFactory = $optionSettingFactory;
        $this->brandProductResource = $brandProductResource;
        $this->isUdeDefaultStoreSettings = $isUdeDefaultStoreSettings;
    }

    public function getBrandProductsData(array $productIds, int $storeId): array
    {
        $attributeCode = $this->configProvider->getBrandAttributeCode($storeId);

        if (!$attributeCode) {
            return [];
        }

        $options = $this->repository->get($attributeCode)->getOptions();
        array_shift($options);
        $positionData = [];
        foreach ($options as $option) {
            $setting = $this->getBrandOptionSettingByValue((int)$option->getValue(), $storeId);
            $storeId = $this->isUdeDefaultStoreSettings->execute($storeId, $setting)
                ? Store::DEFAULT_STORE_ID
                : $storeId;
            $brandPositionData = $this->brandProductResource->getBrandIdsByProductIds(
                $productIds,
                $storeId,
                $option->getValue()
            );
            // phpcs:ignore
            $positionData = array_replace_recursive($positionData, $brandPositionData);
        }

        return $positionData;
    }

    private function getBrandOptionSettingByValue(int $value, int $storeId): OptionSettingInterface
    {
        if (empty($this->settingByValue[$storeId])) {
            $stores = [Store::DEFAULT_STORE_ID, $storeId];
            $collection = $this->optionSettingCollectionFactory->create()
                ->addFieldToFilter('store_id', $stores)
                ->addFieldToFilter('attribute_code', $this->configProvider->getBrandAttributeCode($storeId))
                ->addOrder('store_id', 'ASC'); //current store values will rewrite defaults
            foreach ($collection as $item) {
                $this->settingByValue[$storeId][$item->getValue()] = $item;
            }
        }

        return $this->settingByValue[$storeId][$value] ?? $this->optionSettingFactory->create();
    }
}
