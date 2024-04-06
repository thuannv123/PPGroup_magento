<?php
namespace PPGroup\Integration\Cron;

use PPGroup\Integration\Helper\Data as HelperData;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Magento\Framework\ObjectManagerInterface;
use PPGroup\Integration\Logger\InventoryLog;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class InventorySync
{
    protected $helperData;
    protected $serialize;
    protected $objectManager = null;
    protected $logger;
    protected $orderItemCollection;
    protected $productModel;
    protected $stockRegistry;
    protected $_catalogProductTypeConfigurable;
    protected $_productloader;
    const FILE_PREFIX = 'inventory';

    public function __construct(
        HelperData $helperData,
        Serialize $serialize,
        ObjectManagerInterface $objectManager,
        InventoryLog $logger,
        OrderItemCollection $orderItemCollection,
        ProductModel $productModel,
        StockRegistryInterface $stockRegistry,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Model\ProductFactory $productLoader
    )
    {
        $this->helperData = $helperData;
        $this->serialize = $serialize;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->orderItemCollection = $orderItemCollection;
        $this->productModel = $productModel;
        $this->stockRegistry = $stockRegistry;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_productloader = $productLoader;
    }

    public function execute()
    {
        $this->logger->info('====Start inventory sync====');
        $sftpHost = $this->helperData->getGeneralConfig('sftp_host');
        $sftpUserName = $this->helperData->getGeneralConfig('sftp_username');
        $sftpPassword = $this->helperData->getGeneralConfig('sftp_pass');
        $inventoryFilePath = $this->helperData->getInventoryConfig('inventory_sync_file_path');
        $directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $inventoryFolder = $directory->getRoot() . '/var/import/inventory/';
        $inventoryArchiveFolder = $directory->getRoot() . '/var/import/inventory/Archive/';
        $this->helperData->createFolder($inventoryFolder);
        $this->helperData->createFolder($inventoryArchiveFolder);
        $listFiles = $this->helperData->saveSftpFileToLocal(
            [
                'host' => $sftpHost,
                'username' => $sftpUserName,
                'password' => $sftpPassword
            ],
            self::FILE_PREFIX,
            $inventoryFilePath,
            $inventoryFolder
        );

        if (!empty($listFiles)) {
            $reservedStock = $this->getReservedStock();
            foreach ($listFiles as $file) {
                $csvData = [];
                $csvData = $this->helperData->readCSV($inventoryFolder . $file);
                if (!empty($csvData)) {
                    $this->logger->info(sprintf('Processing total of %s rows', count($csvData)));
                    $updatedItem = 0;
                    $skip = 0;
                    foreach ($csvData as $data) {
                        $csvSku = $data[0];
                        $csvQty = $data[1];
                        if (!isset($csvSku) || !isset($csvQty)) {
                            continue;
                        }
                        $this->updateInventoryItem($csvSku, $csvQty, $reservedStock) == true ? $updatedItem++ : $skip++;
                    }
                    $this->logger->info(sprintf('Total %s skus updated, skip %s skus', $updatedItem, $skip));
                }
                $this->helperData->moveFile($inventoryFolder . $file, $inventoryArchiveFolder . $file);
            }
        }
        $this->logger->info('====Complete inventory sync====');
    }

    /**
     * Update Inventory Item
     *
     * @param string $csvSku
     * @param string $csvQty
     * @param array $reservedStock
     *
     * @return bool
     */
    protected function updateInventoryItem($csvSku, $csvQty, $reservedStock = [])
    {
        $productId = $this->productModel->getIdBySku($csvSku);
        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            if ((int)$stockItem->getQty() == (int)$csvQty) {
                return false;
            }
            $reservedQty = 0;

            if (isset($reservedStock[$csvSku])) {
                $reservedQty = (int)$reservedStock[$csvSku];
            }

            $qty = 0;

            if ($csvQty > $reservedQty) {
                $qty = $csvQty - $reservedQty;
            }

            $stockItem->setManageStock(1)
                ->setQty($qty);

            $flag = false;
            if ($stockItem->getQty() > $stockItem->getMinQty()) {
                $flag = true;
            }
            $stockItem->setIsInStock($flag)
                ->setStockStatusChangedAutomaticallyFlag(true);

            $this->stockRegistry->updateStockItemBySku($csvSku, $stockItem);

            if ($flag == true) {
                $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($productId);
                foreach ($parentByChild as $parentId) {
                    $parent = $this->_productloader->create()->load($parentId);
                    $stockParentItem = $this->stockRegistry->getStockItem($parentId);
                    if ($stockParentItem) {
                        $stockParentItem->setManageStock(1)
                            ->setQty($qty);
                        $stockParentItem->setIsInStock($flag)
                            ->setStockStatusChangedAutomaticallyFlag(true);
                        $this->stockRegistry->updateStockItemBySku($parent->getSku(), $stockParentItem);
                        $stockParentItem->save();
                    }
                }
            }
            $stockItem->save();
            return true;
        }
        return false;
    }

    protected function getReservedStock()
    {
        $reservedStock = [];

        $reduceReservedStockConfig = [];
        if ($this->helperData->getInventoryConfig('stock_reserve_condition') !== null) {
            $reduceReservedStockConfig = $this->serialize->unserialize($this->helperData->getInventoryConfig('stock_reserve_condition'));
        }
        $reservedStockConditionStatement = $this->getReservedStockConditionStatement($reduceReservedStockConfig);
        if ($reservedStockConditionStatement == '') {
            $this->logger->info('==== Can not build reserve stock condition ====');
            return $reservedStock;
        }
        $collection = $this->orderItemCollection;
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->reset(\Zend_Db_Select::GROUP)
            ->columns(
                array('main_table.sku', 'SUM(main_table.qty_ordered) AS qty')
            )
            ->join(
                array('so' => 'sales_order'),
                'main_table.order_id = so.entity_id',
                null
            )
            ->join(
                array('sop' => 'sales_order_payment'),
                'so.entity_id = sop.parent_id',
                null
            )
            ->where(
                'main_table.sku is not null AND ' .
                'main_table.parent_item_id IS NULL AND ' .
                '(' . $reservedStockConditionStatement . ')'
            )
            ->group('main_table.sku');
        if ($collection->count() > 0) {
            foreach ($collection as $key => $item) {
                $reservedStock[$item->getSku()] = $item->getQty();
            }
        }
        return $reservedStock;
    }

    private function getReservedStockConditionStatement($reservedStockConfig)
    {
        $statement = '';
        if (count($reservedStockConfig) > 0) {
            $conditions = [];
            foreach ($reservedStockConfig as $exportStatus) {
                $conditions[] = "(sop.method = '" .
                    $exportStatus['payment_method'] . "' AND so.status ='" .
                    $exportStatus['order_status'] . "')";
            }
            $statement = implode(' OR ', $conditions);
        }
        return $statement;
    }
}
