<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
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

namespace Acommerce\Ccpp\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Payment\Block\Info as BlockInfo;
use Magento\Framework\App\State as AppState;

/**
 * OneTwoThree payment Block Info
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */

class Info extends BlockInfo
{
    // @codingStandardsIgnoreStart
    /**
     * Path Of Template
     *
     * @var string
     */
    protected $_template = 'Acommerce_Ccpp::info/ccpp.phtml';
    // @codingStandardsIgnoreEnd

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    protected $statuses = array('000' => 'SUCCESS (PAID)',
                    '001' => 'SUCCESS (PENDING)',
                    '002' => 'PAYMENT REJECTED',
                    '003' => 'PAYMENT WAS CANCELED BY USER',
                    '004' => 'PAYMENT WAS EXPIRED',
                    '999' => 'PAYMENT FAILED',
                    );

    protected $channels = array('001' => 'CREDIT AND DEBIT CARDS',
                    '002' => 'CASH PAYMENT CHANNEL',
                    '003' => 'DIRECT DEBIT',
                    '004' => 'OTHERS',
                    '005' => 'IPP TRANSACTION',
                    '006' => 'QR CODE PAYMENT CHANNEL');

    protected $processBy = array('AL' => 'ALIPAY',
                    'AM' => 'AMEX',
                    'AP' => 'ALTERNATIVE PAYMENT',
                    'DI' => 'DISCOVER',
                    'DN' => 'DINNER',
                    'JC' => 'JCB',
                    'KP' => 'KCP',
                    'LP' => 'LINEPAY',
                    'MA' => 'MASTER CARD',
                    'MP' => 'MPU',
                    'PA' => 'PAYPAL',
                    'UP' => 'CHINA UNION PAY',
                    'VI' => 'VISA',
                    'WC' => 'WECHAT');


    /**
     * Constructor
     *
     * @param Context $context       Context
     * @param Config  $paymentConfig Payment Config
     * @param array   $data          data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentConfig = $paymentConfig;
        //$this->state = $state;

    }//end __construct()


    /**
     * Get Cc Type Name
     *
     * @return string
     */
    public function getCcTypeName()
    {
        $types  = $this->paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();

        if (isset($types[$ccType]) === true) {
            return $types[$ccType];
        }

        if (empty($ccType) === true) {
            return __('N/A');
        }

        return __($ccType);
    }//end getCcTypeName()


    /**
     * Prepare credit card related payment info
     *
     * @param DataObject $transport transport
     *
     * @return DataObject
     */

    // @codingStandardsIgnoreStart
    protected function _prepareSpecificInformation($transport=null)
    {
        $parentBlock = $this->getParentBlock();
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $data      = array();

        if($parentBlock && $parentBlock instanceof \Magento\Sales\Block\Adminhtml\Order\Payment) {
            $additionalInfo = $this->getInfo()->getAdditionalInformation();
            if ($additionalInfo) {
                if (isset($additionalInfo['merchant_id']) === true
                    && !empty($additionalInfo['merchant_id'])) {
                    $merchantId = $additionalInfo['merchant_id'];
                    $data[(string) __('Merchant ID')] = $merchantId;
                }

                if (isset($additionalInfo['approval_code']) === true
                    && !empty($additionalInfo['approval_code'])) {
                    $approvalCode = $additionalInfo['approval_code'];
                    $data[(string) __('Approval Code')] = $approvalCode;
                }

                if (isset($additionalInfo['eci']) === true
                    && !empty($additionalInfo['eci'])) {
                    $eci = $additionalInfo['eci'];
                    $data[(string) __('ECI')] = $eci;
                }

                if (isset($additionalInfo['transaction_datetime']) === true
                    && !empty($additionalInfo['transaction_datetime'])) {
                    $transactionDatetime = $additionalInfo['transaction_datetime'];
                    $data[(string) __('Transaction Datetime')] = $transactionDatetime;
                }

                if (isset($additionalInfo['payment_channel']) === true
                    && !empty($additionalInfo['payment_channel'])) {
                    $paymentChannel = $additionalInfo['payment_channel'];
                    $data[(string) __('Payment Channel')] = $this->channels[$paymentChannel];
                }

                if (isset($additionalInfo['channel_response_code']) === true
                    && !empty($additionalInfo['channel_response_code'])) {
                    $channelResponseCode = $additionalInfo['channel_response_code'];
                    $data[(string) __('Channel Response Code')] = $channelResponseCode;
                }

                if (isset($additionalInfo['channel_response_desc']) === true
                    && !empty($additionalInfo['channel_response_desc'])) {
                    $channelResponseDesc = $additionalInfo['channel_response_desc'];
                    $data[(string) __('Channel Response Desc')] = strtoupper($channelResponseDesc);
                }

                if (isset($additionalInfo['masked_pan']) === true
                    && !empty($additionalInfo['masked_pan'])) {
                    $maskedPan = $additionalInfo['masked_pan'];
                    $data[(string) __('Masked Pan')] = $maskedPan;
                }

                if (isset($additionalInfo['paid_channel']) === true
                    && !empty($additionalInfo['paid_channel'])) {
                    $paidChannel = $additionalInfo['paid_channel'];
                    $data[(string) __('Paid Channel')] = $paidChannel;
                }

                if (isset($additionalInfo['paid_agent']) === true
                    && !empty($additionalInfo['paid_agent'])) {
                    $paidAgent = $additionalInfo['paid_agent'];
                    $data[(string) __('Paid Agent')] = $paidAgent;
                }

                if (isset($additionalInfo['ippPeriod']) === true
                    && !empty($additionalInfo['ippPeriod'])) {
                    $ippPeriod = $additionalInfo['ippPeriod'];
                    $data[(string) __('IPP Period')] = $ippPeriod;
                }

                if (isset($additionalInfo['ippInterestType']) === true
                    && !empty($additionalInfo['ippInterestType'])) {
                    $ippInterestType = $additionalInfo['ippInterestType'];
                    $data[(string) __('IPP Interest Type')] = $ippInterestType;
                }

                if (isset($additionalInfo['ippInterestRate']) === true
                    && !empty($additionalInfo['ippInterestRate'])) {
                    $ippInterestRate = $additionalInfo['ippInterestRate'];
                    $data[(string) __('IPP Interest Rate')] = $ippInterestRate;
                }

                if (isset($additionalInfo['ippMerchantAbsorbRate']) === true
                    && !empty($additionalInfo['ippMerchantAbsorbRate'])) {
                    $ippMerchantAbsorbRate = $additionalInfo['ippMerchantAbsorbRate'];
                    $data[(string) __('IPP Merchant Absorb Rate')] = $ippMerchantAbsorbRate;
                }

                if (isset($additionalInfo['payment_scheme']) === true
                    && !empty($additionalInfo['payment_scheme'])) {
                    $paymentScheme = $additionalInfo['payment_scheme'];
                    $data[(string) __('Payment Scheme')] = $this->processBy[$paymentScheme];
                }

                if (isset($additionalInfo['process_by']) === true
                    && !empty($additionalInfo['process_by'])) {
                    $processBy = $additionalInfo['process_by'];
                    $data[(string) __('process By')] = $this->processBy[$processBy];
                }

                if (isset($additionalInfo['payment_status']) === true
                    && !empty($additionalInfo['payment_status'])) {
                    $responseCode = $additionalInfo['payment_status'];
                    if(isset($this->statuses[$responseCode])) {
                        $data[(string) __('Payment Status')] = $this->statuses[$responseCode];
                    }
                }
            }//end if
        }

        return $transport->setData(array_merge($data, $transport->getData()));

    }//end prepareSpecificInformation()
    // @codingStandardsIgnoreEnd

    /**
     * Convert to pdf
     *
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Acommerce_Ccpp::info/pdf/ccpp.phtml');
        return $this->toHtml();
    }//end toPdf()
}
