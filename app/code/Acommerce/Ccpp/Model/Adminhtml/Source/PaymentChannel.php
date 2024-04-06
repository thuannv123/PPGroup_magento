<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentChannel
 */
class PaymentChannel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'ALL',
                'label' => __('All Available')
            ],
            [
                'value' => 'CC',
                'label' => __('Credit Card Payment')
            ],
            [
                'value' => 'BANK',
                'label' => __('Bank Transfer')
            ],
            [
                'value' => 'OTC',
                'label' => __('Counter Payment')
            ],
            [
                'value' => '123',
                'label' => __('Counter Payment & Bank Transfer')
            ],
            [
                'value' => 'EMVQR',
                'label' => __('EMV QR (Merchant QR)')
            ],
            [
                'value' => 'ALIPAY',
                'label' => __('Alipay')
            ],
            [
                'value' => 'LINE',
                'label' => __('LinePay')
            ],
            [
                'value' => 'PAYPAL',
                'label' => __('Paypal')
            ],
            [
                'value' => 'TRUEMONEY',
                'label' => __('TRUEMONEY')
            ],
            [
                'value' => 'MPASS',
                'label' => __('Masterpass')
            ],
            [
                'value' => 'WCQR',
                'label' => __('Wechat Pay (Merchant QR)')
            ],
            [
                'value' => 'IPP',
                'label' => __('IPP (Installment Payment Plan) payment only')
            ],
            [
                'value' => 'PAYMAYA',
                'label' => __('PayMaya')
            ],
            [
                'value' => 'MPASS',
                'label' => __('Masterpass')
            ]
        ];
    }
}
