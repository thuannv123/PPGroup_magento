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
class InterestType implements \Magento\Framework\Option\ArrayInterface
{

    const ALL = 'A';
    const CUSTOMER_PAY = 'C';
    const MERCHANT_PAY = 'M';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                    ['value' => self::ALL, 'label' => __('All available')],
                    ['value' => self::CUSTOMER_PAY, 'label' => __('Customer Pay Interest')],
                    ['value' => self::MERCHANT_PAY, 'label' => __('Merchant Pay Interest')],
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
                self::ALL => __('All available'),
                self::CUSTOMER_PAY => __('Customer Pay Interest'),
                self::MERCHANT_PAY => __('Merchant Pay Interest'),
            ];
    }
}
