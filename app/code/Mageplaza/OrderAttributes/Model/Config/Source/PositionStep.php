<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PositionStep
 * @package Mageplaza\OrderAttributes\Model\Config\Source
 */
class PositionStep implements ArrayInterface
{
    const NONE            = 0;
    const AFTER_SHIPPING  = 1;
    const BEFORE_SHIPPING = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NONE,
                'label' => __("None")
            ],
            [
                'value' => self::BEFORE_SHIPPING,
                'label' => __('Before Shipping Step')
            ],
            [
                'value' => self::AFTER_SHIPPING,
                'label' => __('After Shipping Step')
            ],
        ];
    }
}
