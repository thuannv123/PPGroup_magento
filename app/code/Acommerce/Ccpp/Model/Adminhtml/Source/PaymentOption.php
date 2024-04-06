<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_OneTwoThree
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */

namespace Acommerce\Ccpp\Model\Adminhtml\Source;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * OneTwoThree payment method model
 *
 * @category Acommerce_OneTwoThree
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class PaymentOption implements \Magento\Framework\Option\ArrayInterface
{

    const ALL = 'A';
    const CREDITCARD_AND_IPP = 'B';
    const CREDITCARD = 'C';
    const FULLPAYMENT = 'F';
    const IPP = 'I';
    const QRCODE = 'E';
    const APM = '1';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                    ['value' => self::ALL, 'label' => __('All available options')],
                    ['value' => self::CREDITCARD_AND_IPP, 'label' => __('Credit card and IPP')],
                    ['value' => self::CREDITCARD, 'label' => __('Credit card payment only')],
                    ['value' => self::FULLPAYMENT, 'label' => __('Full amount payment only')],
                    ['value' => self::IPP, 'label' => __('Installment Payment Plan')],
                    ['value' => self::APM, 'label' => __('1-2-3 (APM) payment only')],
                ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
                self::ALL => __('All available options'),
                self::CREDITCARD_AND_IPP => __('Credit card and IPP'),
                self::CREDITCARD => __('Credit card payment only'),
                self::FULLPAYMENT => __('Full amount payment only'),
                self::IPP => __('Installment Payment Plan'),
                self::APM => __('1-2-3 (APM) payment only'),
            ];
    }
}
