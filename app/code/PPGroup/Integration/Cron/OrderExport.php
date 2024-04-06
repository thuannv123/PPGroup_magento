<?php

namespace PPGroup\Integration\Cron;

use Magento\Sales\Model\Order;
use PPGroup\Integration\Helper\Data as HelperData;
use PPGroup\Integration\Helper\Order as OrderHelper;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Magento\Framework\ObjectManagerInterface;
use PPGroup\Integration\Logger\SaleorderExportLog;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;

class OrderExport
{
    /**
     * @var OrderHelper
     */
    protected $orderHelper;
    protected $helperData;
    protected $serialize;
    protected $objectManager = null;
    protected $logger;
    protected $websiteCollectionFactory;
    protected $orderCollectionFactory;
    protected $soExportConfig;
    const FILE_PREFIX = 'SO-';

    /**
     * @var WeeeHelper
     */
    protected WeeeHelper $weeeHelper;

    /**
     * @var float
     */
    private float $itemDiscount;

    /**
     * @var float
     */
    private float $itemRowTotal;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        OrderHelper $orderHelper,
        HelperData $helperData,
        Serialize $serialize,
        ObjectManagerInterface $objectManager,
        SaleorderExportLog $logger,
        WebsiteCollectionFactory $websiteCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        WeeeHelper $weeeHelper,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->orderHelper = $orderHelper;
        $this->helperData = $helperData;
        $this->serialize = $serialize;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->productRepository = $productRepository;
        $this->weeeHelper = $weeeHelper;
        $this->itemDiscount = 0;
        $this->itemRowTotal = 0;
    }

    public function execute()
    {
        try {
            $this->logger->info('====Start export  sale order====');
            $websites = $this->websiteCollectionFactory->create();
            $directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
            $ordersFolder = $directory->getRoot() . '/var/export/orders/';
            $this->logger->info(sprintf(' Directory root folder: %s', $directory->getRoot()));
            $this->logger->info(sprintf('Orders Folder %s', $ordersFolder));
            $this->helperData->createFolder($ordersFolder);
            $soExportConfig = [];
            if ($this->helperData->getSoExportConfig('sale_order_export_condition') !== null) {
                $soExportConfig = $this->serialize->unserialize($this->helperData->getSoExportConfig('sale_order_export_condition'));
            }
            $sftpHost = $this->helperData->getGeneralConfig('sftp_host');
            $sftpUserName = $this->helperData->getGeneralConfig('sftp_username');
            $sftpPassword = $this->helperData->getGeneralConfig('sftp_pass');
            $sftpFilePath = $this->helperData->getSoExportConfig('sale_order_export_file_path');


            if (empty($soExportConfig)) {
                throw new \Exception("Error Sales Order Export condition do not setup");
            }
            $this->soExportConfig = $soExportConfig;
             if ($websites->getSize() > 0) {
                $csvData = [];
                foreach ($websites as $website) {
                    if ((int)$website->getId() === 0) {
                        continue;
                    }
                    $orders = $this->getOrderCollection($website);
                    $this->logger->info(sprintf('Found %s orders need export', $orders->getSize()));
                    if ($orders->getSize() == 0) {
                        continue;
                    }
                    $csvData[] = $this->buildCsvHeader();
                    foreach ($orders as $order) {
                        $orderData = $this->getSoData($order);

                        $orderShippingAddress = $order->getShippingAddress();
                        $orderBillingAddress = $order->getBillingAddress();


                        $shippingAddress = $this->getAddress($orderShippingAddress);
                        $shippingData = $this->getAddressData($orderShippingAddress);

                        $billingAddress = $this->getAddress($orderBillingAddress);
                        $billingData = $this->getAddressData($orderBillingAddress);
                        $orderItems = $order->getAllItems();
                        $couponCode = $order->getCouponCode();
                        $branch = $order['branch'];
                        $companyName = $order['company_name'];
                        $taxId = $order['tax_id'];
                        foreach ($orderItems as $item) {
                            // set discount and row total
                            $this->itemDiscount = ($this->itemDiscount == 0) ? $item->getDiscountAmount() : $this->itemDiscount;
                            $this->itemRowTotal = ($this->itemRowTotal == 0) ? $item->getRowTotal() : $this->itemRowTotal;

                            if ($item->getData('product_type') == 'configurable') {
                                continue;
                            }
                            $csvData[] = array_merge(
                                $orderData,
                                $shippingAddress,
                                $shippingData,
                                $billingAddress,
                                $billingData,
                                $this->getItemData($item, $couponCode, $branch, $companyName, $taxId)
                            );
                            $this->clearData();
                        }

                    }

                    $fileName = self::FILE_PREFIX . $this->helperData->getDateTimeFromServer() . '.csv';
                    $filePath = $ordersFolder . $fileName;
                    $this->logger->info(sprintf('Start write data to local path %s', $filePath));
                    $this->helperData->writeCsv($filePath, $csvData);
                    $this->logger->info(sprintf('Done write data to local path %s', $filePath));
                    $this->logger->info('Start upload file to SFTP');

                    $this->helperData->uploadFileToSftp(
                        [
                            'host' => $sftpHost,
                            'username' => $sftpUserName,
                            'password' => $sftpPassword,
                            'timeout'  => 300
                        ],
                        $fileName,
                        $filePath,
                        $sftpFilePath
                    );
                    $this->logger->info('Done upload file to SFTP');
                    $this->logger->info('Start update sale order status');
                    $this->updateSoStatus($orders);
                    $this->logger->info('End update sale order status');
                }
            }
            $this->logger->info('====Complete export  sale order====');
        } catch (\Exception $e) {
            $this->logger->error('====ERROR export  sale order====');
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Order Collection
     *
     * @param \Magento\Store\Model\Website $website Website
     *
     * @return collection
     */
    protected function getOrderCollection($website)
    {
        $storeIds = $website->getStoreIds();

        $orders = $this->orderCollectionFactory->create();


        $orders->getSelect()
            ->joinInner(
                ['payment' => 'sales_order_payment'],
                'main_table.entity_id = payment.parent_id',
                ['payment.method']
            )->joinLeft(
                ['mageplaza_order_attribute' => 'mageplaza_order_attribute_sales_order'],
                'main_table.entity_id = mageplaza_order_attribute.order_id',
                ['mageplaza_order_attribute.company_name',
                 'mageplaza_order_attribute.branch',
                 'mageplaza_order_attribute.tax_id']
            );

        $paymentCondition = $this->getPaymentCondition();
        if ($paymentCondition != '') {
            $orders->getSelect()->where($paymentCondition);
        }

        if (count($storeIds) > 0) {
            $orders->addFieldToFilter('main_table.store_id', array('in' => $storeIds));
        }
        return $orders;
    }

    /**
     * Get Payment Condition
     *
     * @return string
     */
    protected function getPaymentCondition()
    {
        $statement = '';
        $exportStatuses = $this->soExportConfig;
        $conditions = [];
        if ($exportStatuses) {
            if (count($exportStatuses) > 0) {
                foreach ($exportStatuses as $exportStatus) {
                    $conditions[] = "(payment.method = '" .
                        $exportStatus['payment_method'] . "' AND main_table.status ='" .
                        $exportStatus['order_status'] . "')";
                }
                $statement = implode(' OR ', $conditions);
            }
        }
        return $statement;
    }

    private function buildCsvHeader()
    {
        return [
            'Sale Order ID', 'Purchase Point', 'Status', 'Purchase Date', 'Grand Total (Base)',
            'Shipping and Handling', 'Discount',
            'Paid Price', 'Shipping Information',
            'Customer Email', 'Customer Name',
            'Shipping Address', 'Ship-to Name', 'Shipping Phone Number',
            'Billing Address', 'Bill-to Name', 'Billing Phone Number',
            'SKU', 'Qty', 'Brand', 'Item Subtotal', 'Item Discount', 'Item Total',
            'Product Name', 'Coupon Code', 'Company Name', 'Branch', 'Identification No.'
        ];
    }


    private function getSoData($order)
    {
        $data = [];
        $data[] = $order->getIncrementId();
        $data[] = $order->getStore()->getWebsite()->getName() . ' > ' . $order->getStore()->getName();
        $data[] = $order->getStatus();
        $data[] = $this->helperData->convertDateToStoreTimeZone($order->getCreatedAt());
        $data[] = $order->getSubtotal();
        $data[] = $order->getShippingAmount();
        $data[] = $order->getBaseDiscountAmount();
        $data[] = $order->getGrandTotal();
        $data[] = $order->getShippingDescription();
        $data[] = $order->getCustomerEmail();
        $data[] = $order->getCustomerFirstName() . ' ' . $order->getCustomerLastName();
        return $data;
    }

    /**
     * @param $item
     * @param $couponCode
     * @return array
     */
    private function getItemData($item, $couponCode, $branch, $companyName, $taxId): array
    {
        $data = [];
        $data[] = $item->getSku();
        $data[] = $item->getQtyOrdered();
        $data[] = $item->getProduct()->getAttributeText('brand');
        $data[] = $this->getPriceBySku($item->getSKU()) * $item->getQtyOrdered();
        $data[] = $this->itemDiscount;
        $data[] = $this->getTotalAmount($item);
        $data[] = $item->getProduct()->getName();
        $data[] = $couponCode;
        $data[] = $companyName;
        $data[] = $branch;
        $data[] = $taxId;

        return $data;
    }

    /**
     * @param $sku
     * @return float
     */
    public function getPriceBySku($sku)
    {
        $productFinalPrice = 0.0;

        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return $productFinalPrice;
        }

        if ($product) {
            $productFinalPrice = $product->getFinalPrice();
        }

        return $productFinalPrice;
    }

    private function getAddress($address)
    {
        $data = [];
        if (!empty($address)) {
            $streets = '';
            foreach ($address->getStreet() as $street) {
                $streets .= ' ' . $street;
            }
            $data[] = $streets . ' ' . $address->getSubdistrict(). ' ' . $address->getCity() . ' '
                . $address->getPostcode() . ' ' . $address->getRegion();
        } else {
            $data[] ='';
        }
        return $data;
    }

    private function getAddressData($address)
    {
        $data = [];
        if (!empty($address)) {
            $data[] = $address->getFirstname() . ' ' . $address->getLastname();
            $data[] = $address->getTelephone();
        } else {
            $data[] ='';
            $data[] ='';
        }
        return $data;
    }

    private function updateSoStatus($orders) {
        foreach ($orders as $order) {
            $order->setState(Order::STATE_PROCESSING, true);
            $order->setStatus(Order::STATE_PROCESSING);
            $order->save();
            $this->orderHelper->updateOrderStatus($order->getId(), Order::STATE_PROCESSING, Order::STATE_PROCESSING);
        }
    }

    /**
     * Calculate total amount of item
     * @param $item
     * @return float
     */
    public function getTotalAmount($item)
    {
        return $this->itemRowTotal
            - $this->itemDiscount
            + $item->getTaxAmount()
            + $item->getDiscountTaxCompensationAmount()
            + $this->weeeHelper->getRowWeeeTaxInclTax($item);
    }

    /**
     * Clear price when endforeach
     * @return void
     */
    private function clearData()
    {
        $this->itemDiscount = 0;
        $this->itemRowTotal = 0;
    }
}
