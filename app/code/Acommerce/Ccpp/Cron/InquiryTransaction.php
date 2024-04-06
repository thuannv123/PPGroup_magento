<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */

namespace Acommerce\Ccpp\Cron;

use Magento\Sales\Model\Service\InvoiceService;
use Acommerce\Ccpp\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as OrderInvoiceCollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Ccpp Payment Cron
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class InquiryTransaction
{
    /**
     * Invoice Service
     *
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * Data Helper
     *
     * @var Data
     */
    protected $helperData;

    const PAYMENT_METHOD = 'ccpp';

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
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param InvoiceService    $invoiceService    Invoice Service
     * @param Data              $helperData        Helper Data
     * @param CollectionFactory $collectionFactory Order Collection Factory
     * @param OrderInvoiceCollectionFactory Order Invoice Collection Factory
     * @param ResourceConnection $resource
     *
     * @return void
     */
    public function __construct(
        InvoiceService $invoiceService,
        Data $helperData,
        CollectionFactory $collectionFactory,
        OrderInvoiceCollectionFactory $orderInvoiceCollectionFactory,
        ResourceConnection $resource
    ) {
        $this->invoiceService   = $invoiceService;
        $this->helperData        = $helperData;
        $this->salesOrderCollectionFactory = $collectionFactory;
        $this->orderInvoiceCollectionFactory = $orderInvoiceCollectionFactory;
        $this->connection = $resource->getConnection();
    }//end __construct()


    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $this->log("---------STARTING THE INQUIRY CRON------------");

        if (!$this->helperData->getCronActive()) {
            return;
        }

        $orderCollection = $this->salesOrderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            'main_table.state',
            array('in' => array(Order::STATE_NEW, Order::STATE_PENDING_PAYMENT))
        );

        $orderCollection->addFieldToFilter(
            'created_at',
            array('lteq' => new \Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 30 MINUTE)'))
        );

        $orderCollection->getSelect()->joinInner(
            array(
                'payment' => $orderCollection->getTable('sales_order_payment')
            ),
            'main_table.entity_id = payment.parent_id AND payment.method = \''.
            self::PAYMENT_METHOD.'\'',
            array('payment_method' => 'payment.method')
        );

        //echo $orderCollection->getSelect()->__toString();
        //echo $orderCollection->getSize();

        if ($orderCollection->getSize() > 0) {
            $this->log('Order Size: ' . $orderCollection->getSize());

            $this->log('Order Ids: ' . json_encode($orderCollection->getAllIds()));
            foreach ($orderCollection as $order) {
                $data = $this->helperData->processTransaction(
                    $order->getIncrementId()
                );

                $this->log("Order ID: " . $order->getEntityId() . ", Increment ID: " . $order->getIncrementID() . ", Grand Total: " . $order->getGrandTotal());

                $orderId = isset($data['invoiceNo'])?$data['invoiceNo']:'';
                $respCode = isset($data['respCode'])?$data['respCode']:'';
                $status = isset($data['status'])?$data['status']:'';

                if ($respCode == '00' && in_array($status, array('A', 'S'))) {
                    $paymentData = array(
                        'masked_cc_number' => $data['pan'],
                        'approval_code' => $data['approvalCode'],
                        'eci' => $data['eci'],
                        'status' => $data['status'],
                        'fail_reason' => $data['failReason']
                    );

                    $payment = $order->getPayment();
                    $payment->setAdditionalInformation($paymentData);

                    $payment->setCcNumberEnc($data['pan'])
                        ->setCcTransId($data['tranRef'])
                        ->setCcApproval($data['status'])
                        ->setTransactionId($data['tranRef']);

                    $payment->save();

                    if ($order->canInvoice() === true) {
                        $invoice = $this->invoiceService
                            ->prepareInvoice($order);

                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $invoice->save();

                        $orderInvoiceCollection = $this->orderInvoiceCollectionFactory->create()
                                                  ->addFieldToFilter('order_id', $order->getEntityId());

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

                        $order->addStatusHistoryComment(
                            __(
                                'Notified customer about invoice #%1.',
                                $invoice->getIncrementId()
                            )
                        )->save();
                    } else {
                        $invoices = $order->getInvoiceCollection();

                        if (count($invoices) > 0) {
                            foreach ($invoices as $invoice) {
                                $invoice->capture();
                            }
                        }
                    }
                }
            }
            $this->log("---------ENDING THE INQUIRY CRON------------");
        }
    }//end execute()

    public function callByOrderId($orderId) {
        $output = '';
        $data = $this->helperData->processTransaction(
            $orderId
        );
        $output.= 'Info - Payloads: ' . print_r($data, true);

        return $output;
    }

    /**
     * @param $logData
     * @return mixed
     * @throws \Zend_Log_Exception
     */
    public function log($logData) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cron_inquiry_trigger.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        return $logger->info($logData);
    }
}//end class
