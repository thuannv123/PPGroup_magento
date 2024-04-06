<?php

namespace PPGroup\Ccpp\Helper;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use stdClass;

class P2c2pApiHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Order $order
     * @param bool $isQROrder
     * @return bool|mixed|string
     */
    public function callPaymentTokenApi($order, $isQROrder = false) {
        $configData = $this->receiveMerchantIdAndSecretKeyConfig($isQROrder);

        $payload = [
            "merchantID" => $configData['merchant_id'],
            "invoiceNo" => $order->getIncrementId(),
            "description" => "item " . $order->getId(),
            "amount" => $order->getBaseGrandTotal(),
            "currencyCode" => $order->getBaseCurrencyCode()
        ];

        if ($isQROrder) {
            $payload['paymentChannel'] = ["QR"];
        }

        $jwt = JWT::encode($payload, $configData['secret_key'], 'HS256');

        $sentPayload = '{"payload":"' . $jwt . '"}';

        $sandboxMode = '';

        if ($this->isInSandBoxMode() == 1) {
            $sandboxMode = 'sandbox-';
        }

        $url = curl_init("https://" . $sandboxMode . "pgw.2c2p.com/payment/4.1/paymentToken");

        $response = $this->receiveApiResponse($url, $sentPayload);

        $response = json_decode($response, true);

        return $response;
    }

	public function callPaymentOptionApi($paymentToken) {
        $payload = [
            "paymentToken" => $paymentToken
        ];

        $payload = json_encode($payload);

        $sandboxMode = '';

        if ($this->isInSandBoxMode() == 1) {
            $sandboxMode = 'sandbox-';
        }

        $url = curl_init("https://" . $sandboxMode . "pgw.2c2p.com/payment/4.1/paymentOption");

        $response = $this->receiveApiResponse($url, $payload);

        $response = json_decode($response, true);

        return $response;
	}

    public function callPaymentOptionDetailApi($paymentToken, $paymentOptionData) {
        $payload = [
            "paymentToken" => $paymentToken,
            "categoryCode" => $paymentOptionData['channelCategories'][0]['code'],
            "groupCode" =>  $paymentOptionData['channelCategories'][0]['groups'][0]['code'],
        ];

        $payload = json_encode($payload);;

        $sandboxMode = '';

        if ($this->isInSandBoxMode() == 1) {
            $sandboxMode = 'sandbox-';
        }

        $url = curl_init("https://". $sandboxMode . "pgw.2c2p.com/payment/4.1/paymentOptionDetails");

        $response = $this->receiveApiResponse($url, $payload);

        $response = json_decode($response, true);

        return $response;
    }

    public function callDoPaymentApi($paymentToken, $paymentOptionDetailsData) {
        $sandboxMode = '';

        if ($this->isInSandBoxMode() == 1) {
            $sandboxMode = 'sandbox-';
        }

        $payload = [
            "paymentToken" => $paymentToken,
            "responseReturnUrl" => "https://" . $sandboxMode . "pgw-ui.2c2p.com/payment/4.1/#/info/",
            "payment" => [
                "code" => [
                    "channelCode" => $paymentOptionDetailsData["channels"][0]['payment']['code']['channelCode']
                ],
                "data" => [
                    "name" => isset($paymentOptionDetailsData['merchantDetails']['name']) ? $paymentOptionDetailsData['merchantDetails']['name'] : "0",
                    "email" => isset($paymentOptionDetailsData['merchantDetails']['email']) ? $paymentOptionDetailsData['merchantDetails']['email'] : "0",
                    "customerNote" => "I"
                ]
            ]
        ];

        $payload = json_encode($payload);;

        $url = curl_init("https://". $sandboxMode . "pgw.2c2p.com/payment/4.1/payment");

        $response = $this->receiveApiResponse($url, $payload);

        $response = json_decode($response, true);

        return $response;
    }

    public function callTransactionStatusApi($paymentToken) {
        $payload = [
            "paymentToken" => $paymentToken,
            "additionalInfo" => true
        ];

        $payload = json_encode($payload);;

        $sandboxMode = '';

        if ($this->isInSandBoxMode() == 1) {
            $sandboxMode = 'sandbox-';
        }

        $url = curl_init("https://" . $sandboxMode . "pgw.2c2p.com/payment/4.1/transactionStatus");
        $response = $this->receiveApiResponse($url, $payload);

        $response = json_decode($response, true);

        return $response;
    }

    /**
     * @param $paymentToken
     * @param $order
     * @param bool $isQROrder
     * @return bool|mixed|string
     */
    public function callPaymentInquiryApi($order, $isQROrder = false) {

        $configData = $this->receiveMerchantIdAndSecretKeyConfig($isQROrder);

        $payload = [
            "paymentToken" => $order->getPaymentToken() ? $order->getPaymentToken() : '',
            "merchantID" => $configData['merchant_id'],
            "invoiceNo" => $order->getIncrementId(),
            "description" => "item " . $order->getId(),
            "amount" => $order->getBaseGrandTotal(),
            "currencyCode" => $order->getBaseCurrencyCode()
        ];

        $jwt = JWT::encode($payload, $configData['secret_key'], 'HS256');

        $payload = '{"payload":"' . $jwt . '"}';

        $sandboxMode = '';

        if ($this->isInSandBoxMode() == 1) {
            $sandboxMode = 'sandbox-';
        }

        $url = curl_init("https://" . $sandboxMode . "pgw.2c2p.com/payment/4.1/paymentInquiry");
        $response = $this->receiveApiResponse($url, $payload);

        $response = json_decode($response, true);

        return $response;
    }

    /**
     * @param $order
     * @param bool $isQROrder
     * @return stdClass
     */
    public function receivePaymentInquiryResponse($order, $isQROrder)
    {
        $orderPayloadResponse = new stdClass();

        $dataConfig = $this->receiveMerchantIdAndSecretKeyConfig($isQROrder);

        $transactionStatusPaymentResult = "";

        if (!$order->getPaymentToken()) {

            $paymentTokenResponse = $this->callPaymentTokenApi($order, $isQROrder);

            if (!(isset($paymentTokenResponse['respCode']) && ($paymentTokenResponse['respCode'] == 9015))) {
                if (isset($paymentTokenResponse['payload'])) {
                    $tokenPayload = $paymentTokenResponse['payload'];

                    $tokenPayload = JWT::decode($tokenPayload, new Key($dataConfig['secret_key'], 'HS256'));

                    $order->setPaymentToken($tokenPayload->paymentToken);
                    $order->save();
                }
            } else {
                $orderPayloadResponse->orderSuccess = true;
            }
        }

        if (!isset($orderPayloadResponse->orderSuccess) && $order->getPaymentToken()) {
            $transactionStatusResponse = $this->callTransactionStatusApi($order->getPaymentToken());

            if (isset($transactionStatusResponse['additionalInfo'])) {
                $transactionStatusPaymentResult = $transactionStatusResponse['respCode'];
            }
        }

        $paymentInquiryResponse = $this->callPaymentInquiryApi($order, $isQROrder);

        if (isset($paymentInquiryResponse['payload'])) {
            $paymentInquiryPayload = $paymentInquiryResponse['payload'];

            $orderPayloadResponse = JWT::decode($paymentInquiryPayload, new Key($dataConfig['secret_key'], 'HS256'));
        }

        $orderPayloadResponse->transactionStatusPaymentResult = $transactionStatusPaymentResult;

        return $orderPayloadResponse;
    }

    /**
     * @param bool $isQROrder
     * @return array
     */
    public function receiveMerchantIdAndSecretKeyConfig($isQROrder = false) {
        $secretKey = $this->scopeConfig->getValue('payment/ccpp/secret_key');
        $merchantId = $this->scopeConfig->getValue('payment/ccpp/merchant_id');


        if ($isQROrder) {
            $secretKey = $this->scopeConfig->getValue('payment/qrcode/secret_key');
            $merchantId = $this->scopeConfig->getValue('payment/qrcode/merchant_id');
        }

        return [
            'secret_key' => $secretKey,
            'merchant_id' => $merchantId
        ];
    }

    /**
     * @return mixed
     */
    public function isInSandBoxMode(){
        return $this->scopeConfig->getValue('payment/ccpp/sandbox_flag');
    }

    /**
     * @return mixed
     */
    public function getPaymentCancelAllowConfigValue() {
        return $this->scopeConfig->getValue('payment/ccpp/auto_cancel');
    }

    /**
     * @param $api
     * @param $data
     * @return bool|string
     */
    private function receiveApiResponse($api, $data)
    {
        $headers[] = 'Content-Type: application/*+json';

        curl_setopt($api, CURLOPT_POST, true);
        curl_setopt($api, CURLOPT_POSTFIELDS, $data);
        curl_setopt($api, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($api, CURLOPT_HTTPHEADER, $headers);
        $sentResponse = curl_exec($api);
        curl_close($api);

        return $sentResponse;
    }
}
