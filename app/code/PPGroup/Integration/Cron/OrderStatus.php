<?php

namespace PPGroup\Integration\Cron;

use Magento\Setup\Exception;
use PPGroup\Integration\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;
use PPGroup\Integration\Helper\Order as OrderHelper;
use PPGroup\Integration\Logger\SaleorderStatusLog;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order as OrderModel;

class OrderStatus
{
    /**
     * @var OrderHelper
     */
    protected $orderHelper;
    protected $helperData;
    protected $objectManager;
    protected $logger;
    protected $orderFactory;
    protected $convertOrder;
    protected $trackFactory;
    protected $invoiceService;
    protected $invoiceSender;
    protected $shipmentSender;

    const FILE_PREFIX = 'SO-Status';
    const SKIP_STATUS = ['order confirmed', 'packing'];

    public function __construct(
        OrderHelper $orderHelper,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        SaleorderStatusLog $logger,
        OrderFactory $orderFactory,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender
    )
    {
        $this->orderHelper = $orderHelper;
        $this->helperData = $helperData;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->convertOrder = $convertOrder;
        $this->trackFactory = $trackFactory;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
    }

    public function execute()
    {
        $this->logger->info('====Start sales order status sync====');
        $sftpHost = $this->helperData->getGeneralConfig('sftp_host');
        $sftpUserName = $this->helperData->getGeneralConfig('sftp_username');
        $sftpPassword = $this->helperData->getGeneralConfig('sftp_pass');
        $soStatusFilePath = $this->helperData->getSaleOrderStatusConfig('sales_order_status_sync_file_path');
        $directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $soStatusFolder = $directory->getRoot() . '/var/import/so_status/';
        $soStatusArchiveFolder = $directory->getRoot() . '/var/import/so_status/Archive/';
        $this->helperData->createFolder($soStatusFolder);
        $this->helperData->createFolder($soStatusArchiveFolder);
        $listFiles = $this->helperData->saveSftpFileToLocal(
            [
                'host' => $sftpHost,
                'username' => $sftpUserName,
                'password' => $sftpPassword
            ],
            self::FILE_PREFIX,
            $soStatusFilePath,
            $soStatusFolder
        );

        if (!empty($listFiles)) {
            foreach ($listFiles as $file) {
                $csvData = [];
                $csvData = $this->helperData->readCSV($soStatusFolder . $file);
                if (!empty($csvData)) {
                    $this->logger->info(sprintf('Processing total of %s rows of %s', count($csvData), $file));

                    foreach ($csvData as $data) {
                        $updateData = [];

                        if (isset($data[3])) {
                            if (!isset($data[2]) || in_array(strtolower($data[3]), self::SKIP_STATUS)) {
                                continue;
                            }

                            $updateData = [
                                'csvDate' => $data[0],
                                'csvTime' => $data[1],
                                'csvOrderId' => $data[2],
                                'csvStatus' => $data[3],
                                'csvTrackingNumber' => $data[4],
                                'csvTrackingUrl' => $data[5]
                            ];

                            switch (strtolower($data[3])) {
                                case 'shipped':
                                    $this->createShipmentOrder($updateData);
                                    break;
                                case 'finished':
                                    $this->createCompletedOrder($updateData);
                                    break;
                                default:
                                    break;
                            }
                        } else {
                            continue;
                        }
                    }
                }
                $this->helperData->moveFile($soStatusFolder . $file, $soStatusArchiveFolder . $file);
            }
        }
        $this->logger->info('====Complete sales order status sync====');
    }

    private function createShipmentOrder($data) {
        try {
            $order = $this->orderFactory->create();
            $order = $order->loadByIncrementId($data['csvOrderId']);
            if ($order->canShip()) {
                $convertOrder = $this->convertOrder;
                $shipment = $convertOrder->toShipment($order);

                foreach ($order->getAllItems() as $orderItem) {
                    if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    $qtyShipped = $orderItem->getQtyToShip();
                    $shipmentItem = $convertOrder
                        ->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    $shipment->addItem($shipmentItem);
                }

                $trackingData = array(
                    'carrier_code' => $data['csvTrackingNumber'],
                    'number' => $data['csvTrackingNumber'],
                    'description' => $data['csvTrackingUrl'],
                    'title' => $data['csvTrackingUrl']
                );

                $track = $this->trackFactory->create();
                $track->addData($trackingData);
                $shipment->addTrack($track);


                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);

                $shipment->save();
                $shipment->getOrder()->save();

                $shipment->save();

                $this->shipmentSender->send($shipment);

                $order->setState('shipped', true);
                $order->setStatus('shipped');
                $order->save();
                $this->orderHelper->updateOrderStatus($order->getId(), 'shipped', 'shipped');
            } else {
                $this->logger->warning(sprintf('Order %s can not ship so can not update to shipped', $order->getIncrementId()));
            }
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Exception error: %s', $exception->getMessage()));
            throw $exception;
        }
    }

    protected function createCompletedOrder($data)
    {
        try {
            $order = $this->orderFactory->create();
            $order = $order->loadByIncrementId($data['csvOrderId']);

            if ($order->getStatus() != 'shipped') {
                $this->createShipmentOrder($data);
            }

            if ($order->getStatus() != OrderModel::STATE_COMPLETE) {
                $this->createInvoice($order);
            }
            $order = $order->loadByIncrementId($data['csvOrderId']);

            if ($order->getStatus() == 'shipped' && $order->hasInvoices()) {
                $userNotification
                    = $order->hasCustomerNoteNotify() ?
                    $order->getCustomerNoteNotify() : null;
                $stateComplete = OrderModel::STATE_COMPLETE;
                $statusComplete = $order->getConfig()->getStateDefaultStatus($stateComplete);
                $order->setState($stateComplete)
                    ->setStatus($statusComplete)
                    ->save();
                $this->orderHelper->updateOrderStatus($order->getId(), $stateComplete, $statusComplete);
            } else {
                $this->logger->warning(sprintf('Order %s does not have invoice so can not update to complete', $order->getIncrementId()));
            }
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Exception error: %s', $exception->getMessage()));
            throw $exception;
        }
    }

    /**
     * Create Invoice
     *
     * @param OrderModel $order Sales Order
     *
     * @return void
     */
    protected function createInvoice($order)
    {
        if ($order->canInvoice() === true) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();

            $transactionSave = $this->objectManager->create(
                'Magento\Framework\DB\Transaction'
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );

            $transactionSave->save();
            $this->invoiceSender->send($invoice);

            $order->addStatusHistoryComment(
                __(
                    'Notified customer about invoice #%1.',
                    $invoice->getIncrementId()
                )
            )->save();
        }
    }
}
