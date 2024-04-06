<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Request\HtmlRedirect;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Acommerce\Ccpp\Model\Adminhtml\Source\PaymentOption;
use Magento\Sales\Model\OrderRepository;

/**
 * Class OrderDataBuilder
 */
class OrderDataBuilder implements BuilderInterface
{
    /**
     * Your own reference number for this purchase. It is returned to you along
     * with the authorisation results by whatever method you have chosen for
     * being informed (email and / or Payment Responses).
     */
    const CART_ID = 'cartId';

    /**
     * A decimal number giving the cost of the purchase in terms of the major
     * currency unit e.g. 12.56 would mean 12 pounds and 56 pence if the
     * currency were GBP (Pounds Sterling). Note that the decimal separator
     * must be a dot (.), regardless of the typical language convention for the
     * chosen currency. The decimal separator does not need to be included if
     * the amount is an integral multiple of the major currency unit. Do not
     * include other separators, for example between thousands.
     */
    const AMOUNT = 'amount';

    /**
     * ISO 639
     */
    const LANGUAGE = 'lang';

    /**
     * 3 letter ISO code for the currency of this payment.
     */
    const CURRENCY = 'currency';

    /**
     * Order increment id in the Magento system
     */
    const ORDER_ID = 'MC_order_id';

    /**
     * ID store the current order
     */
    const STORE_ID = 'MC_store_id';

    /**
     * Your Ccpp Installation ID. This is a unique 6-digit
     * reference number we assign to you. It tells us which payment
     * methods and currencies your installation supports.
     */
    const INSTALLATION_ID = 'instId';

    /**
     * This specifies the authorisation mode to use. If there is
     * no merchant code with a matching authMode then
     * the transaction is rejected. The values are "A" for a full
     * auth, or "E" for a pre-auth. In the payment result this
     * parameter can also take the value "O" when
     * performing a post-auth.
     */
    const AUTH_MODE = 'authMode';

    /**
     * The shopper's full name, including any title, personal
     * name and family name.
     * Note that if you do not pass through a name, and use
     * Payment Responses, the name that the cardholder
     * enters on the payment page is returned to you as the
     * value of name in the Payment Responses message.
     * Also note that if you are sending a test submission you
     * can specify the type of response you want from our
     * system by entering REFUSED, AUTHORISED, ERROR or
     * CAPTURED as the value in the name parameter. You
     * can also generate an AUTHORISED response by using a
     * real name, such as, J. Bloggs.
     */
    const NAME = 'name';

    /**
     * The first line of the shopper's address. Encode newlines
     * as "&#10;" (the HTML entity for ASCII 10, the new line
     * character).
     * If this is not supplied in the order details then it must
     * be entered in the payment pages by the shopper
     */
    const ADDRESS_1 = 'address1';

    /**
     * The first line of the shopper's address. Encode newlines
     * as "&#10;" (the HTML entity for ASCII 10, the new line
     * character).
     */
    const ADDRESS_2 = 'address2';

    /**
     * The first line of the shopper's address. Encode newlines
     * as "&#10;" (the HTML entity for ASCII 10, the new line
     * character).
     */
    const ADDRESS_3 = 'address3';

    /**
     * The town or city. Encode newlines as "&#10;" (the
     * HTML entity for ASCII 10, the new line character).
     * If this is not supplied in the order details then it must
     * be entered in the payment pages by the shopper.
     */
    const TOWN = 'town';

    /**
     * The shopper’s region/county/state. Encode newlines as
     * "&#10;" (the HTML entity for ASCII 10, the new line
     * character).
     */
    const REGION = 'region';

    /**
     * The shopper's postcode.
     * Note that at your request we can assign mandatory
     * status to this parameter. That is, if it is not supplied in
     * the order details then the shopper must enter it in the
     * payment pages.
     */
    const POSTCODE = 'postcode';

    /**
     * The shopper's country, as 2-character ISO code,
     * uppercase.
     * If this is not supplied in the order details then it must
     * be entered in the payment pages by the shopper.
     */
    const COUNTRY = 'country';

    /**
     * The shopper's telephone number.
     */
    const TELEPHONE = 'tel';

    /**
     * The shopper's email address.
     */
    const EMAIL = 'email';

    /**
     * Using the fixContact parameter locks the address information passed to us, so that your shoppers
     * cannot change this information when they reach the payment pages, as shown in the example below.
     */
    const FIX_CONTACT = 'fixContact';

    /**
     * Alternatively, you can use the hideContact parameter to hide the address information of shoppers on
     * the payment pages.
     */
    const HIDE_CONTACT = 'hideContact';

    /**
     * A value of 100 specifies that this is a test payment.
     * Specify the test result you want by entering REFUSED,
     * AUTHORISED, ERROR, or CAPTURED in the name
     * parameter.
     * When you submit order details using the testMode
     * parameter and the URL for the live Production
     * Environment, you are presented with a page asking you
     * if you want to redirect the order details to the Test
     * Environment – select the Redirect button if you do.
     * If you submit the order details to the live production
     * environment our systems attempt to debit merchant
     * codes (accounts).
     * Reversing transactions such as these, and adjusting
     * accounts, causes unnecessary work for us as well as
     * you.
     * Set this parameter to 0 (zero) or omit it for a live
     * transaction.
     */
    const TEST_MODE = 'testMode';

    /**
     * If present, this causes the currency drop down to be hidden, so fixing
     * the currency that the shopper must purchase in.
     */
    const HIDE_CURRENCY = 'hideCurrency';

    /**
     * The value set in test mode
     */
    const TEST_MODE_VALUE = '100';

    /**
     * The value set in live mode
     */
    const LIVE_MODE_VALUE = '0';

    /**
     * The URL to process the response from gateway
     */
    const PAYMENT_CALLBACK = 'MC_callback';

    /**
     * Response url
     */
    const RESPONSE_URL = 'ccpp/htmlRedirect/response';

    /**
     * The glue for the signature fields
     */
    const GLUE = ':';

    /**
     * The field name for the signature
     */
    const SIGNATURE = 'signature';

    /**
     * The field name for the merchant_id
     */
    const MERCHANT_ID = 'merchant_id';

    /**
     * The field name for the merchant_id
     */
    const VERSION = 'version';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlHelper;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    private $currencyMap = [
      'USD' => '840',
      'THB' => '764',
      'AUD' => '036',
      'GBP' => '826',
      'CNY' => '156',
      'EUR' => '978',
      'HKD' => '344',
      'IDR' => '360',
      'JPY' => '392',
      'SGD' => '702',
      'PHP' => '608'
     ];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * @var ScopeConfigInterface
     */
    protected $orderRepository;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param UrlInterface $urlHelper
     * @param ResolverInterface $localeResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlHelper,
        ResolverInterface $localeResolver,
        ScopeConfigInterface $scopeConfig,
        OrderRepository $orderRepository
    ) {
        $this->config = $config;
        $this->urlHelper = $urlHelper;
        $this->localeResolver = $localeResolver;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $this->log("---------Start Log---------");
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $method = $paymentDO->getPayment()->getMethodInstance()->getCode();
        $storeId = $order->getStoreId();
        $address = $order->getBillingAddress();

        $orderModel = $this->orderRepository->get($order->getId());
        $data = $orderModel->getPayment()->getAdditionalInformation();

        if (isset($data['installment'])) {
            $paymentOption = PaymentOption::IPP;
        } else {
            $paymentOption = $this->getConfigValue($method, 'payment_channel_code');

            if ($paymentOption == PaymentOption::CREDITCARD_AND_IPP) {
                $paymentOption = PaymentOption::CREDITCARD;
            }
        }

        $includePromo = $this->getConfigValue($method, 'include_promo');

        $couponCode = '';
        if($includePromo) {
            $couponCode = $orderModel->getCouponCode();
            if(is_null($couponCode)) {
                $couponCode = '';
            } else {
                $couponCodes = $this->getConfigValue($method, 'coupon_codes');

                if(!empty(trim($couponCodes))) {
                    $couponCodes = str_replace(' ', '', $couponCodes);
                    $couponCodes = explode(',', trim($couponCodes));
                    if(count($couponCodes) > 0) {
                        foreach ($couponCodes as $key => $code) {
                            $couponCodes[$key] = trim($code);
                        }

                        if(!in_array($couponCode, $couponCodes)) {
                            $couponCode = '';
                        }
                    }
                }
            }
        }

        $result = [
          'version' => $this->getConfigValue($method, 'version'),
          'merchant_id' => $this->getConfigValue($method, 'merchant_id'),
          'payment_description' => $order->getOrderIncrementId(),
          'default_lang' => $this->getConfigValue($method, 'default_language'),
          'order_id' => $order->getOrderIncrementId(),
          'invoice_no' => $order->getOrderIncrementId(),
          'currency' => $this->currencyMap[$order->getCurrencyCode()],
          'amount' => str_pad($order->getGrandTotalAmount()*100, 12, "0", STR_PAD_LEFT),
          'customer_email' => $address->getEmail(),
          'pay_category_id' => '',
          'promotion' => $couponCode,
          'user_defined_1' => $order->getId(),
          'user_defined_2' => '',
          'user_defined_3' => '',
          'user_defined_4' => '',
          'user_defined_5' => '',
          'result_url_1' => $this->urlHelper->getUrl(self::RESPONSE_URL),
          'result_url_2' => '',
          'payment_option' => $paymentOption
        ];

        $this->log("Data Sent To 2c2p: " . json_encode($result));

        if ($paymentOption == PaymentOption::IPP) {
            $result['ipp_interest_type'] = $this->getConfigValue($method, 'ipp_interest_type');

            $result['ipp_period_filter'] = $data['installment_month'];
        }
        if ($paymentOption == PaymentOption::QRCODE) {
            $result['qr_type'] = $this->getConfigValue($method, 'qr_type');
        }

        $result['hash_value'] = $this->getSignature($method, $result, $storeId);

        $history = $orderModel->addStatusHistoryComment('Build data for posting to 2C2P #'.$order->getOrderIncrementId());
        $history->save();

        $this->log("---------End Log---------");

        return [
            'fields' => array_filter(
                $result,
                function ($value) {
                    return !is_bool($value) || $value === true;
                }
            ),
            'action' => (bool)(int)$this->getConfigValue($method, 'sandbox_flag')
                ? $this->getConfigValue($method, 'gateway_url_test')
                : $this->getConfigValue($method, 'gateway_url')
        ];
    }

    /**
     * Returns signature
     * @param string $group
     * @param array $request
     * @param int $storeId
     * @return null|string
     */
    private function getSignature($group, array $request, $storeId)
    {
        $secret = $this->getConfigValue($group, 'secret_key');

        $fieldsToSign =  explode(
            self::GLUE,
            (string)$this->getConfigValue($group, 'signature_fields')
        );

        if (!$secret || !$fieldsToSign) {
            return null;
        }

        $sign = [];
        foreach ($fieldsToSign as $field) {
            if (array_key_exists($field, $request)) {
                $sign[] = $request[$field];
            }
        }
        $strSignature = implode("", $sign);

        $HashValue = hash_hmac('sha1', $strSignature, $secret, false);

        return $HashValue;
    }

    /**
     * Returns field config value
     *
     * @param string $group
     * @param string $field
     * @return null|string
     */
    protected function getConfigValue($group, $field)
    {
        return $this->scopeConfig->getValue(
            'payment/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $logData
     * @return mixed
     * @throws \Zend_Log_Exception
     */
    public function log($logData) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/2c2p_order_forward_data.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        return $logger->info($logData);
    }
}
