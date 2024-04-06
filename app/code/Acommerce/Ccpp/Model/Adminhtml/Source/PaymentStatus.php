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
class PaymentStatus implements \Magento\Framework\Option\ArrayInterface
{

    protected $paymentStatuses = array('000' => 'SUCCESS (PAID)',
                    '001' => 'SUCCESS (PENDING)',
                    '002' => 'PAYMENT REJECTED',
                    '003' => 'PAYMENT WAS CANCELED BY USER',
                    '999' => 'PAYMENT FAILED',
                    );

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $values = [];
        foreach ($this->paymentStatuses as $key => $value) {
            $values[] = ['value' => $key, 'label' => __($value)];
        }
        return $values;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $paymentStatuses;
    }
}
