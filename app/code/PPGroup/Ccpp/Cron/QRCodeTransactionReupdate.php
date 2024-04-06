<?php

namespace PPGroup\Ccpp\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as OrderInvoiceCollectionFactory;
use PPGroup\Ccpp\Helper\P2c2pApiHelper;
use \Magento\Sales\Api\OrderManagementInterface;

/**
 * Class QRCodeTransactionReupdate
 * @package PPGroup\Ccpp\Cron
 */
class QRCodeTransactionReupdate
{
    const QR_CODE_METHOD = 'qrcode';

    /**
     * Invoice Service
     *
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * Data Helper
     *
     * @var P2c2pApiHelper
     */
    protected $p2c2pApiHelper;

    /**
     * Sales Order Collection Factory
     *
     * @var CollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * Order Invoice Collection Factory
     *
     * @var OrderInvoiceCollectionFactory
     */
    protected $orderInvoiceCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var OrderManagementInterface
     */
    protected $orderInterface;

    /**
     * Constructor
     *
     * @param InvoiceService $invoiceService
     * @param P2c2pApiHelper $p2c2pApiHelper
     * @param CollectionFactory $collectionFactory
     * @param OrderInvoiceCollectionFactory $orderInvoiceCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resource
     * @return void
     */
    public function __construct(
        InvoiceService $invoiceService,
        P2c2pApiHelper $p2c2pApiHelper,
        CollectionFactory $collectionFactory,
        OrderInvoiceCollectionFactory $orderInvoiceCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource,
        OrderManagementInterface $orderInterface
    )
    {
        $this->invoiceService = $invoiceService;
        $this->p2c2pApiHelper = $p2c2pApiHelper;
        $this->salesOrderCollectionFactory = $collectionFactory;
        $this->orderInvoiceCollectionFactory = $orderInvoiceCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->connection = $resource->getConnection();
        $this->orderInterface = $orderInterface;
    }


    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Log_Exception
     */
    public function execute()
    {
        $this->log("---------STARTING THE QRCODE INQUIRY CRON------------");

        $orderCollection = $this->salesOrderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            'main_table.state',
            ['in' => [
                Order::STATE_NEW,
                Order::STATE_PENDING_PAYMENT
            ]
            ]
        );

        $orderCollection->getSelect()->joinInner(
            ['payment' => $orderCollection->getTable('sales_order_payment')],
            'main_table.entity_id = payment.parent_id
            AND (payment.method = \'' . self::QR_CODE_METHOD . '\')',
            [
                'payment_method' => 'payment.method',
                'additional_information' => 'payment.additional_information'
            ]
        )->order('main_table.entity_id DESC');

        $dataConfig = $this->p2c2pApiHelper->receiveMerchantIdAndSecretKeyConfig(true);

        if ($orderCollection->getSize() > 0) {
            foreach ($orderCollection as $order) {

                //get Time server
                $date = str_replace('+00:00', 'Z', date('c'));
                $time_one_day = 86400;

                if( (strtotime($date) - strtotime($order->getCreatedAt())) <  $time_one_day ){
                    //bypass order 
                    $this->log("Bypass order has not yet arrived : " .$order->getIncrementId());
                    continue;
                }

                $orderAdditionalData = $order['additional_information'];

                $orderAdditionalData = json_decode($orderAdditionalData, true);

                $this->log('Order ID: ' . $order->getId());

                $this->log('Order No: ' . $order->getIncrementId());

                $orderPayload = $this->p2c2pApiHelper->receivePaymentInquiryResponse($order, true);

                $this->log("Payment Inquiry Response: " . json_encode($orderPayload));

                if (isset($orderPayload->orderSuccess)) {
                    $this->log("Order Success: " . $orderPayload->orderSuccess);
                }

                $this->log("Order Method Title: " . $orderAdditionalData['method_title']);

                $responseDesc = "";

                if (isset($orderPayload->respDesc)) {
                    $responseDesc = (string)$orderPayload->respDesc;
                } else {
                    if (isset($orderPayload->orderSuccess)) {
                        $responseDesc = "Success";
                    }
                }

                $responseCode = $orderPayload->respCode;
                $this->log("Response Decs: " . $responseDesc);
                $this->log("Response Code: " . $responseCode);

                $orderAdditionalData['merchant_id'] = $dataConfig['merchant_id'];
                $orderAdditionalData["transaction_ref"] = isset($orderPayload->tranRef) ? $orderPayload->tranRef : '';
                $orderAdditionalData['approval_code'] = isset($orderPayload->approvalCode) ? $orderPayload->approvalCode : '';
                $orderAdditionalData['eci'] = isset($orderPayload->eci) ? $orderPayload->eci : '';

                if (isset($orderPayload->transactionDateTime) && $orderPayload->transactionDateTime) {
                    $datetime = str_split($orderPayload->transactionDateTime, 4);

                    $year = $datetime[0];
                    $month = str_split($datetime[1], 2)[0];
                    $day = str_split($datetime[1], 2)[1];

                    $hour = str_split($datetime[2], 2)[0];
                    $minute = str_split($datetime[2], 2)[1];
                    $second = $datetime[3];

                    $transactionDate = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;

                    $orderAdditionalData["transaction_datetime"] = $transactionDate;
                    $this->log("Date time: " . $orderAdditionalData["transaction_datetime"]);
                }

                $orderStatus = "pending_payment";

                if ($responseCode == '0000' || $responseCode == '000' || $responseCode == '00') {
                    $this->log('---------- Order success ---------');
                    $orderAdditionalData['payment_status'] = "000";
                    $orderAdditionalData['channel_response_code'] = "00";
                    $orderAdditionalData['channel_response_desc'] = "approved";
                    $orderAdditionalData['payment_channel'] = "006";

                    $orderStatus = 'invoiced';
                    $this->log('---------- Create invoice ---------');

                    $orderInvoiceCollection = $this->orderInvoiceCollectionFactory->create()->addFieldToFilter('order_id', $order->getEntityId());

                    if ($orderInvoiceCollection->getSize() == 0) {
                        $invoice = $this->invoiceService
                            ->prepareInvoice($order);

                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $invoice->save();
                    }
                    $this->log('---------- Create transaction ---------');

                    $sql = "SELECT * FROM `sales_payment_transaction` WHERE `order_id` = " . $order->getId();

                    if (empty($this->connection->fetchRow($sql))) {
                        $datetime = (string)$orderPayload->transactionDateTime;

                        $transactionCaptureData = [
                            "order_id" => $order->getId(),
                            "payment_id" => $order->getId(),
                            "txn_id" => $datetime,
                            "txn_type" => \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
                            "is_closed" => 0,
                            "additional_information" => null
                        ];
                        $this->connection->insertOnDuplicate('sales_payment_transaction', $transactionCaptureData);
                    }

                    foreach ($orderInvoiceCollection->getItems() as $orderInvoice) {
                        if ($orderInvoice->getGrandTotal() == 0) {
                            $invoiceQuery = "DELETE FROM `sales_invoice` WHERE `order_id` = " . $invoice->getOrderId()
                                . " AND  `base_grand_total` = 0";

                            $this->connection->query($invoiceQuery);

                            $invoiceQuery = "DELETE FROM `sales_invoice_grid` WHERE `order_id` = " . $invoice->getOrderId()
                                . " AND  `grand_total` = 0";

                            $this->connection->query($invoiceQuery);
                        }
                    }

                    $transactionSave = \Magento\Framework\App\ObjectManager::getInstance()->create(
                        'Magento\Framework\DB\Transaction'
                    )->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );

                    $transactionSave->save();

                    $order->setStatus($orderStatus);
                    $order->setState($orderStatus);

                    $order->addStatusHistoryComment(
                        __(
                            'Notified customer about invoice #%1.',
                            $invoice->getIncrementId()
                        )
                    );

                    $order->addStatusHistoryComment(
                        __('Captured amount of ' . $order->getGrandTotal()
                        .' online. Transaction ID: "' . $orderPayload->transactionDateTime . '"'
                        )
                    );

                    $order->save();
                } elseif ($responseCode == '0003' || $responseCode == '003' || $responseCode == '03') {
                    $orderAdditionalData['payment_status'] = ($responseDesc == 'Payment Expired.') ? "004" : "999";
                    $orderAdditionalData['channel_response_code'] = "03";
                    $orderAdditionalData['channel_response_desc'] = ($responseDesc == 'Payment Expired.') ? "failed (expired)" : "Transaction failed";

                    $this->orderInterface->cancel($order->getEntityId());

                } elseif ($responseCode == "4017") {
                    $this->log('---------- Customer Cancel ---------');

                    if ($this->p2c2pApiHelper->getPaymentCancelAllowConfigValue() == 1) {
                        $orderAdditionalData['payment_status'] = "003";
                        $orderAdditionalData['channel_response_code'] = "03";
                        $orderAdditionalData['channel_response_desc'] = "failed (canceled)";

                        $this->orderInterface->cancel($order->getEntityId());
                        
                    }
                } elseif ($responseCode == '2001') {
                    $this->log('-------- transaction success pending -----------------');
                    $orderAdditionalData['payment_status'] = "001";
                    $orderAdditionalData['channel_response_code'] = "01";
                    $orderAdditionalData['channel_response_desc'] = "success (pending)";
                    $orderAdditionalData['payment_channel'] = "006";
                }else{
                    $this->log('---------- Pending transaction ---------');
                }

                $payment = $order->getPayment();

                $payment->setAdditionalInformation($orderAdditionalData);
                $payment->save();


            }
        }
        $this->log("---------ENDING THE QRCODE INQUIRY CRON------------");
    }

    /**
     * @param $logData
     * @return mixed
     * @throws \Zend_Log_Exception
     */
    private function log($logData)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cron_update_qr_order.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        return $logger->info($logData);
    }
 
}
