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

namespace Acommerce\Ccpp\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Acommerce\Ccpp\Model\Adminhtml\Source\PaymentOption;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Ccpp payment method model
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class ConfigProvider implements ConfigProviderInterface
{
    const ACOMMERCE_CWORLD = 'ccpp';
    const ACOMMERCE_QRCODE = 'qrcode';
    const TRANSACTION_DATA_URL = 'ccpp/htmlredirect/gettransactiondata';

    // @codingStandardsIgnoreStart
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Method Codes
     *
     * @var array
     */
    protected $_methodCodes = array(self::ACOMMERCE_CWORLD, self::ACOMMERCE_QRCODE);
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * Methods
     *
     * @var array
     */
    protected $methods = array();

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CartHelper
     */
    protected $checkoutSession;

    /**
     *  Product Meta Data
     *
     * @var Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    //@codingStandardsIgnoreEnd


    /**
     * Constructor
     *
     * @param UrlInterface             $urlBuilder      URL Builder
     * @param PaymentHelper            $paymentHelper   Payment Helper
     * @param Escaper                  $escaper         Escaper
     * @param Session                  $checkoutSession Checkout Session
     * @param ProductMetadataInterface $productMetadata Checkout Session
     */
    public function __construct(
        UrlInterface $urlBuilder,
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Session $checkoutSession,
        ProductMetadataInterface $productMetadata
    ) {

        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->checkoutSession = $checkoutSession;
        $this->productMetadata = $productMetadata;
        foreach ($this->_methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                self::ACOMMERCE_CWORLD => [
                    'transactionDataUrl' =>
                    $this->urlBuilder->getUrl(
                        self::TRANSACTION_DATA_URL,
                        ['_secure' => true]
                    )
                ],
                self::ACOMMERCE_QRCODE => [
                    'transactionDataUrl' =>
                        $this->urlBuilder->getUrl(
                            self::TRANSACTION_DATA_URL,
                            ['_secure' => true]
                        )
                ]
            ]
        ];

        foreach ($this->_methodCodes as $code) {
            if ($this->methods[$code]->isAvailable() === true) {
                $config['payment']['instructions'][$code]
                    = $this->getInstructions($code);
                $config['payment']['InstallmentAllow'][$code]
                    = $this->getAllowInstallment($code);
                $config['payment']['InstallmentDetails'][$code]
                    = $this->getInstallmentDetails($code);
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code Payment Code
     *
     * @return string
     */
    public function getInstructions($code)
    {
        $instructions = $this->escaper->escapeHtml(
            $this->methods[$code]->getConfigData('instructions')
        );
        return nl2br((string)$instructions);
    }//end getInstructions()


    /**
     * Get Allow Installment
     *
     * @param string $code Code
     *
     * @return string
     */
    public function getAllowInstallment($code)
    {
        $result = false;
        $paymentOption = $this->methods[$code]->getConfigData('payment_option');
        $installments = $this->methods[$code]->getConfigData('installments');

        $ipps = array(
                //PaymentOption::ALL,
                PaymentOption::CREDITCARD_AND_IPP,
                PaymentOption::IPP,
            );

        if (in_array($paymentOption, $ipps)) {

            if(version_compare($this->productMetadata->getVersion(), '2.2.0',  '>=')) {
                $installments = json_decode(trim($installments), true);
            } else {
                $installments = unserialize(trim($installments));
            }

            if ($installments) {
                if ($quote = $this->getQuote()) {
                    $grandTotal = $quote->getGrandTotal();

                    foreach ($installments as $key => $installment) {
                        $minAmount = (float) $installment['min_amount'];
                        $maxAmount = (float) $installment['max_amount'];

                        if (!($minAmount <= $grandTotal
                            && $maxAmount >= $grandTotal)
                        ) {
                            unset($installments[$key]);
                        }
                    }

                    if (count($installments) > 0) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }//end getAllowInstallment()

    /**
     * Get Installment Detail
     *
     * @param string $code Code
     *
     * @return string
     */
    public function getInstallmentDetails($code)
    {
        $paymentOption = $this->methods[$code]->getConfigData('payment_option');
        $installments = $this->methods[$code]->getConfigData('installments');

        $ipps = array(
                //PaymentOption::ALL,
                PaymentOption::CREDITCARD_AND_IPP,
                PaymentOption::IPP,
            );

        if (in_array($paymentOption, $ipps)) {
            if ($quote = $this->getQuote()) {
                $grandTotal = $quote->getGrandTotal();
                $installments = unserialize(trim($installments));

                if ($installments) {
                    foreach ($installments as $key => $installment) {
                        $minAmount = (float) $installment['min_amount'];
                        $maxAmount = (float) $installment['max_amount'];

                        if (!($minAmount <= $grandTotal
                            && $maxAmount >= $grandTotal)
                        ) {
                            unset($installments[$key]);
                        }
                    }

                    if (count($installments) > 0
                        && $paymentOption != PaymentOption::IPP
                    ) {
                        $fullPayment = array('code' => '0',
                                        'description' => __('Pay Full Payment'),
                                        'min_amount' => '0',
                                        'max_amount' => '9999999999');
                        array_unshift($installments, $fullPayment);
                    }
                }
            }
        } else {
            return [];
        }

        return $installments;
    }//end getAllowInstallment()

    /**
     * Retrieve current quote instance
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
