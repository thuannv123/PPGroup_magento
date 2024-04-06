<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acommerce\Ccpp\Controller\HtmlRedirect;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Acommerce\Ccpp\Gateway\Command\ResponseCommand;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;

use Magento\Checkout\Model\Cart as CustomerCart;

use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\RuleFactory;
use PPGroup\Checkout\Observer\SalesOrderCancelAfterCoupon;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use PPGroup\Ccpp\Helper\P2c2pApiHelper;

/**
 * Class Response
 */
class Response extends \Magento\Framework\App\Action\Action
{
    protected $observerOrderCancel;

    CONST QR_CODE_METHOD_TITLE_PATH = 'payment/qrcode/title';

    CONST CREDIT_CODE_METHOD_TITLE_PATH = 'payment/ccpp/title';

    const CCPP_CREDIT_CARD_QR_PAYMENT = "EQ";

    /**
     * Invoice Service
     *
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var ResponseCommand
     */
    private $command;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected $checkoutSession;

    protected $cart;

    protected $order;

    protected $_customerSession;

    protected $_response;

    /**
     * @var Coupon
     *
     */
    protected $couponModel;

    /**
     * @var RuleFactory
     */
    protected $rule;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var P2c2pApiHelper
     */
    protected $p2c2pApiHelper;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param Context $context
     * @param ResponseCommand $command
     * @param LoggerInterface $logger
     * @param LayoutFactory $layoutFactory
     * @param OrderCollectionFactory $salesOrderCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param P2c2pApiHelper $p2c2pApiHelper
     * @param InvoiceService $invoiceService
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        ResponseCommand $command,
        LoggerInterface $logger,
        LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerCart $cart,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Customer\Model\Session $customerSession,

        \Magento\Framework\App\Response\Http $response,

        Coupon $couponModel,
        RuleFactory $rule,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        SalesOrderCancelAfterCoupon $observerOrderCancel,
        OrderCollectionFactory $salesOrderCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        P2c2pApiHelper $p2c2pApiHelper,
        InvoiceService $invoiceService,
        ResourceConnection $resource
    )
    {
        parent::__construct($context);

        $this->command = $command;
        $this->layoutFactory = $layoutFactory;
        $this->logger = $logger;

        $this->invoiceService = $invoiceService;

        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->order = $order;
        $this->_customerSession = $customerSession;
        $this->_response = $response;


        $this->couponModel = $couponModel;
        $this->rule = $rule;
        $this->quoteFactory = $quoteFactory;
        $this->observerOrderCancel = $observerOrderCancel;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->p2c2pApiHelper = $p2c2pApiHelper;
        $this->connection = $resource->getConnection();
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->getRequest()->setParams(['ajax' => 1]);
        $resultLayout = $this->layoutFactory->create();
        $resultLayout->addDefaultHandle();
        $processor = $resultLayout->getLayout()->getUpdate();
        $return_path = "checkout/";

        $orderCollection = $this->salesOrderCollectionFactory->create();

        $this->log("-----------------Start Log----------------");

        $this->log("2c2p Webhook params: " . json_encode($params));

        if ($params) {
            $order = $orderCollection->addFieldToFilter("increment_id", $params['order_id'])->getFirstItem();

            $this->log("Order ID: " . $order->getData('entity_id') . ", Increment ID: " . $order->getData('increment_id') . ", Grand Total: " . $order->getData('base_grand_total'));

            $this->log("-----------------Start Logging Order Products List----------------");

            foreach($order->getAllItems() as $orderItem) {
                $productSku = $orderItem->getSku();

                if($orderItem->getData('has_children')) {
                    continue;
                }
                else if($orderItem->getProduct()->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                    $_rootProduct = $orderItem->getProduct();
                }
                else {
                    $_parentItem = $orderItem->getParentItem();
                    if($_parentItem){
                        $_rootProduct = $_parentItem->getProduct();
                    } else {
                        $_rootProduct = $orderItem->getProduct();
                    }
                    $productSku = $orderItem->getSku();
                }

                $this->log("Product ID: " . $_rootProduct->getId()
                           . ", Name : " . $_rootProduct->getName()
                           . ", SKU : " . $productSku
                           . ", Product Final Price : " . $_rootProduct->getFinalPrice());
            }

            $this->log("-----------------End Logging Order Products List----------------");

            $this->log("-----------------End Log----------------");
        }

        try {
            $this->command->execute(['response' => $params]);
            $this->log('-------------- The order has an invoice and transaction --------------');

            $orderId = '';
            if (isset($params['order_id'])) {
                $orderId = $params['order_id'];

                $order = $this->order->loadByIncrementId($orderId);

                $payment = $order->getPayment();

                $orderAdditionalData = $payment->getAdditionalInformation();

                $QRTitleConfig = $this->scopeConfig->getValue(self::QR_CODE_METHOD_TITLE_PATH);

                $creditTitleConfig = $this->scopeConfig->getValue(self::CREDIT_CODE_METHOD_TITLE_PATH);

                if ($orderAdditionalData['method_title'] == $QRTitleConfig) {
                    $this->createSuccessQROrderTransaction($order);
                } elseif($orderAdditionalData['method_title'] == $creditTitleConfig) {
                    $orderPayload = $this->receivePaymentInquiryResponse($order);

                    if ($orderPayload->paymentScheme == self::CCPP_CREDIT_CARD_QR_PAYMENT) {
                        $orderAdditionalData['payment_channel'] = "006";
                    } else {
                        $orderAdditionalData['payment_channel'] = "001";
                    }
                }

                $payment->setAdditionalInformation($orderAdditionalData);
                $payment->save();
            }

            if ($params['payment_status'] == '003') {
                $orderUpdate = $this->order->loadByIncrementId($orderId);
                $orderUpdate->setStatus("pending_payment");
                $orderUpdate->save();

                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $storeId = $storeManager->getStore()->getStoreId();
                $this->deleteQuoteItems();
                $this->_customerSession->setCurrentStore($storeId);



                /* remove coupon code */
                /* if ($code = $orderUpdate->getCouponCode()) {
                    $coupon = $this->couponModel->loadByCode($code);
                    $coupon->setTimesUsed($coupon->getTimesUsed() - 1);
                    $coupon->save();
                    if ($customerId = $orderUpdate->getCustomerId()) {
                        if ($customerCoupon = $this->rule->create()->load($coupon->getId())) {
                            $customerCoupon->setTimesUsed($customerCoupon->getTimesUsed() - 1);
                            $customerCoupon->save();
                        }
                    }
                }*/

                $checkoutSession = $this->getCheckoutSession();
                $quoteId = $checkoutSession->getQuote()->getId();
                $couponCode = $orderUpdate->getCouponCode();
                if ($quoteId) {
                    $quote = $this->quoteFactory->create()->load($quoteId);

                    $quote->setCouponCode($couponCode);
                    $quote->collectTotals()->save();
                }

                $this->observerOrderCancel->checkUpdateRule($couponCode, $orderUpdate->getCustomerId());
            }

            switch ($params['payment_status']) {
                case '000':
                    $return_path = "checkout/onepage/success";
                    break;
                default:
                    $return_path = "checkout/onepage/failure?order=" . $orderId;
                    break;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->resultRedirectFactory->create()->setPath($return_path);
        }

        return $this->resultRedirectFactory->create()->setPath($return_path);
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Log_Exception
     */
    public function createSuccessQROrderTransaction($order) {
        $orderPayload = $this->receivePaymentInquiryResponse($order, true);

        $responseDesc = '';

        if (isset($orderPayload->respDesc)) {
            $responseDesc = (string)$orderPayload->respDesc;
        } else {
            if (isset($orderPayload->orderSuccess)) {
                $responseDesc = "Success";
            }
        }

        if ($responseDesc == 'Success') {
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
        }
    }

    /**
     * @param $order
     * @param bool $isQROrder
     * @return \stdClass
     * @throws \Zend_Log_Exception
     */
    private function receivePaymentInquiryResponse($order, $isQROrder = false)
    {
        $orderPayloadResponse = new \stdClass();

        $dataConfig = $this->p2c2pApiHelper->receiveMerchantIdAndSecretKeyConfig($isQROrder);

        if (!$order->getPaymentToken()) {
            $paymentTokenResponse = $this->p2c2pApiHelper->callPaymentTokenApi($order, $isQROrder);

            if (!(isset($paymentTokenResponse['respCode']) && ($paymentTokenResponse['respCode'] == 9015))) {
                $tokenPayload = $paymentTokenResponse['payload'];

                $tokenPayload = JWT::decode($tokenPayload, new Key($dataConfig['secret_key'], 'HS256'));

                $order->setPaymentToken($tokenPayload->paymentToken);
                $order->save();
            } else {
                $orderPayloadResponse->orderSuccess = true;
            }
        }

        $paymentInquiryResponse = $this->p2c2pApiHelper->callPaymentInquiryApi($order, $isQROrder);

        if (isset($paymentInquiryResponse['payload'])) {
            $paymentInquiryPayload = $paymentInquiryResponse['payload'];

            $orderPayloadResponse = JWT::decode($paymentInquiryPayload, new Key($dataConfig['secret_key'], 'HS256'));
        }

        $this->log("Payment Inquiry Response: " . json_encode($orderPayloadResponse));

        return $orderPayloadResponse;
    }

    public function deleteQuoteItems()
    {
        $checkoutSession = $this->getCheckoutSession();
        $quote_Id = $this->cart->getQuote()->getId();

        $allItems = $checkoutSession->getQuote()->getAllVisibleItems(); //returns all teh items in session
        foreach ($allItems as $item) {
            $itemId = $item->getItemId(); //item id of particular item
            $quoteItem = $this->getItemModel()->load($itemId); //load particular item which you want to delete by his item id
            $quoteItem->delete();
        }
        if (!empty($quote_Id)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('quote');
            $sql = "DELETE  FROM " . $tableName . " WHERE entity_id = " . $quote_Id;
            $connection->query($sql);
        }
    }

    public function getCheckoutSession()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of object manager
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session'); //checkout session
        return $checkoutSession;
    }

    public function getItemModel()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of object manager
        $itemModel = $objectManager->create('Magento\Quote\Model\Quote\Item'); //Quote item model to load quote item
        return $itemModel;
    }

    /**
     * @param $logData
     * @return mixed
     * @throws \Zend_Log_Exception
     */    public function log($logData) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/2c2p_order_return_data.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        return $logger->info($logData);
    }
}
