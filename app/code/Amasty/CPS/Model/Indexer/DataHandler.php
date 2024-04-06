<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Model\Indexer;

use Amasty\CPS\Model\Product\Relation\ChildIdsProvider;
use Amasty\CPS\Model\Product\Relation\ParentIdsProvider;
use Amasty\CPS\Model\Product\Sorting;
use Amasty\CPS\Model\ResourceModel\BrandProduct;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Model\Product\Attribute\Repository;

class DataHandler implements \Amasty\CPS\Api\Indexer\DataHandlerInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @var BrandProduct
     */
    private $brandProduct;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    private $iterator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var Sorting
     */
    private $sorting;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $optionSettingHelper;

    /**
     * @var ChildIdsProvider
     */
    private $childIdsProvider;

    /**
     * @var ParentIdsProvider
     */
    private $parentIdsProvider;

    public function __construct(
        \Amasty\CPS\Model\ResourceModel\BrandProduct $brandProduct,
        ConfigProvider $configProvider,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Amasty\CPS\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        Repository $attributeRepository,
        Sorting $sorting,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionSettingHelper,
        ChildIdsProvider $childIdsProvider,
        ParentIdsProvider $parentIdsProvider
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->brandProduct = $brandProduct;
        $this->iterator = $iterator;
        $this->productRepository = $productRepository;
        $this->appState = $appState;
        $this->attributeRepository = $attributeRepository;
        $this->sorting = $sorting;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->childIdsProvider = $childIdsProvider;
        $this->parentIdsProvider = $parentIdsProvider;
    }

    /**
     * @return $this|\Amasty\CPS\Api\Indexer\DataHandlerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reindexAll()
    {
        $storeIds = $this->getStoreIds();

        foreach ($storeIds as $storeId) {
            $brandAttribute = $this->configProvider->getBrandAttributeCode($storeId);

            if (!$brandAttribute) {
                continue;
            }

            $options = $this->attributeRepository->get($brandAttribute)->getOptions();

            foreach ($options as $brand) {
                $brandId = $brand->getValue();
                if ($brandId) {
                    $this->appState->emulateAreaCode(
                        \Magento\Framework\App\Area::AREA_FRONTEND,
                        [$this, 'updateBrand'],
                        [$brandId, $storeId, $brandAttribute]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getStoreIds()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        return $storeIds;
    }

    public function reindexByProduct(array $products = []): void
    {
        $storeIds = $this->getStoreIds();

        foreach ($storeIds as $storeId) {
            $brandAttribute = $this->configProvider->getBrandAttributeCode($storeId);

            if (!$brandAttribute) {
                continue;
            }

            $productCollection = $this->productCollectionFactory->create()
                ->addStoreFilter($storeId)
                ->addAttributeToSelect($brandAttribute, 'inner');

            if ($products) {
                $childIds = $this->childIdsProvider->getChildIdsForList($products);
                $parentIds = $this->parentIdsProvider->getParentIdsForList($products);
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $products = array_merge($products, $childIds, $parentIds);
                $productCollection->addIdFilter($products);
            }

            $this->updateProducts($brandAttribute, $storeId, $productCollection->getItems());
        }
    }

    /**
     * @param string $brandAttribute
     * @param string|int $storeId
     * @param array $products
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateProducts(string $brandAttribute, $storeId, $products = [])
    {
        foreach ($products as $product) {
            $id = (int) $product['entity_id'];
            $brands = $this->resolveBrands($id, $products, $brandAttribute);
            $currentBrands = $this->getProductBrands($id, $storeId);

            if ($diff = array_diff($currentBrands, $brands)) {
                $this->brandProduct->clearBrandData([
                    'products' => $id,
                    'stores' => [$storeId],
                    'brands' => $diff
                ]);
            }

            if ($diff = array_diff($brands, $currentBrands)) {
                $this->callbackUpdateProduct(
                    [
                        'row' => [
                            'entity_id' => $id,
                            $brandAttribute => $diff,
                        ],
                        'brandAttribute' => $brandAttribute,
                        'store_id' => $storeId
                    ]
                );
            }
        }
    }

    /**
     * @return int[]
     */
    private function resolveBrands(int $parentProductId, array $products, string $brandAttribute): array
    {
        $childBrands = [];
        foreach ($this->childIdsProvider->getChildIds($parentProductId) as $childId) {
            if (!isset($products[$childId])) {
                continue;
            }
            $childBrands[] = explode(',', (string) $products[$childId][$brandAttribute]);
        }
        $brands = explode(',', (string) $products[$parentProductId][$brandAttribute]);

        return $childBrands ? array_unique(array_merge($brands, ...$childBrands)) : $brands;
    }

    /**
     * @param int $id
     * @param string|int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductBrands($id, $storeId)
    {
        $brandData = $this->brandProduct->getBrandIdsByProductIds($id, $storeId);

        return isset($brandData[$id]) ? array_keys($brandData[$id]) : [];
    }

    /**
     * @param array $args
     */
    public function callbackUpdateProduct($args)
    {
        $row = $args['row'];
        foreach ($row[$args['brandAttribute']] as $brand) {
            $positions = $this->brandProduct->getProductPositionData($brand, $args['store_id']);
            $position = !empty($positions) ? max($positions) + 1 : 0;

            $this->brandProduct->updateProductPositionsByBrand(
                $brand,
                $args['store_id'],
                [$row['entity_id'] => $position]
            );
        }
    }

    /**
     * @param int $brandId
     * @param int $storeId
     * @param string $brandAttribute
     */
    public function updateBrand(int $brandId, int $storeId, string $brandAttribute)
    {
        $productCollection = $this->getCollectionForIndex($brandId, $storeId, $brandAttribute);

        $productIds = $productCollection->getAllIds();
        $parentIds = $this->getParentIdsForIndex($brandId, $storeId, $brandAttribute);
        $productIds = array_merge($productIds, $parentIds);
        $pinnedProductPositionData = $this->brandProduct->getProductPositionData($brandId, $storeId, true);
        $productIds = $this->sortIds($productIds, $pinnedProductPositionData);

        $this->brandProduct->clearBrandData([
            'brands' => [$brandId],
            'stores' => [$storeId]
        ]);
        if ($productIds) {
            $this->brandProduct->updateProductPositionsByBrand(
                $brandId,
                $storeId,
                array_flip($productIds),
                array_keys($pinnedProductPositionData)
            );
        }
    }

    /**
     * @param int $brandId
     * @param int $storeId
     * @param string $brandAttribute
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getCollectionForIndex(int $brandId, int $storeId, string $brandAttribute)
    {
        $productCollection = $this->productCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->setStore($storeId);
        if ($storeId) {
            $productCollection->addPriceData();
        } else {
            $productCollection->addAttributeToSelect('price', true);
        }
        $productCollection
            ->addAttributeToFilter('visibility', ['IN' => $this->productVisibility->getVisibleInSiteIds()])
            ->addAttributeToFilter([
                ['attribute' => $brandAttribute, 'finset' => $brandId],
                ['attribute' => 'entity_id', 'in' => $this->getParentIdsForIndex($brandId, $storeId, $brandAttribute)]
            ]);
        $settingModel = $this->optionSettingHelper->getSettingByOption(
            $brandId,
            $brandAttribute,
            $storeId
        );
        if ($settingModel->getSorting()) {
            $this->sorting->applySorting($productCollection, $settingModel->getSorting());
        }

        return $productCollection;
    }

    /**
     * @return int[]
     */
    private function getParentIdsForIndex(int $brandId, int $storeId, string $brandAttribute): array
    {
        $productCollection = $this->productCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->setStore($storeId);
        $productCollection->addAttributeToFilter($brandAttribute, ['finset' => $brandId]);

        return $this->parentIdsProvider->getParentIdsForList($productCollection->getAllIds());
    }

    /**
     * @param array $productIds
     * @param array $pinnedProductIds
     * @return array
     */
    private function sortIds($productIds, $pinnedProductIds)
    {
        $sorted = $this->preparePositionDataForSort($productIds, $pinnedProductIds);
        $productIds = array_diff($productIds, $sorted);
        $itemsCount = count($productIds) + count($sorted);
        $idx = 0;
        while ($idx < $itemsCount) {
            if (!isset($sorted[$idx]) && current($productIds)) {
                $sorted[$idx] = current($productIds);
                next($productIds);
            }
            $idx++;
        }

        ksort($sorted, SORT_NUMERIC);
        return $sorted;
    }

    /**
     * @param array $productIds
     * @param array $pinnedProductIds
     * @return array
     */
    private function preparePositionDataForSort($productIds, $pinnedProductIds)
    {
        $positionData = array_intersect(array_flip($pinnedProductIds), $productIds);
        krsort($positionData);
        $maxPosition = count($productIds) - 1;
        foreach ($positionData as $position => $productId) {
            if ($position > $maxPosition) {
                $positionData[$maxPosition] = $productId;
                $maxPosition--;
            }
        }

        return $positionData;
    }
}
