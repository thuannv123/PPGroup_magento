<?php
namespace PPGroup\Ccpp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use PPGroup\Ccpp\Helper\P2c2pApiHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AfterPlaceOrderObserver implements ObserverInterface
{
    /**
     * @var P2c2pApiHelper
     */
    protected $p2c2pApiHelper;

    /**
     * AfterPlaceOrderObserver constructor.
     * @param P2c2pApiHelper $p2c2pApiHelper
     */
    public function __construct(
        P2c2pApiHelper $p2c2pApiHelper
    ){
        $this->p2c2pApiHelper = $p2c2pApiHelper;
    }


    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $orderPayment = $order->getPayment();
        
        $orderPaymentMethod = $orderPayment->getAdditionalInformation();

        $isQROrder = false;

        if ($orderPaymentMethod['method_title'] == "QR Code") {
            $isQROrder = true;
        }

        $dataConfig = $this->p2c2pApiHelper->receiveMerchantIdAndSecretKeyConfig($isQROrder);

        $paymentTokenResponse = $this->p2c2pApiHelper->callPaymentTokenApi($order);

        $tokenPayload = $paymentTokenResponse['payload'];

        $payloadData = JWT::decode($tokenPayload, new Key($dataConfig['secret_key'], 'HS256'));

        $order->setPaymentToken($payloadData->paymentToken);

        $order->save();

        return $order;
    }
}
