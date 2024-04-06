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
use Mageplaza\OrderAttributes\Model\StepFactory;

/**
 * Class Position
 * @package Mageplaza\OrderAttributes\Model\Config\Source
 */
class Position implements ArrayInterface
{
    const NONE            = 0;
    const ADDRESS         = 1;
    const SHIPPING_TOP    = 2;
    const SHIPPING_BOTTOM = 3;
    const PAYMENT_TOP     = 4;
    const PAYMENT_BOTTOM  = 5;
    const ORDER_SUMMARY   = 6;

    /**
     * @var StepFactory
     */
    protected $_stepFactory;

    /**
     * Position constructor.
     *
     * @param StepFactory $stepFactory
     */
    public function __construct(StepFactory $stepFactory)
    {
        $this->_stepFactory = $stepFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::ADDRESS, 'label' => __('Shipping Address')],
            ['value' => self::SHIPPING_TOP, 'label' => __('Shipping Method Top')],
            ['value' => self::SHIPPING_BOTTOM, 'label' => __('Shipping Method Bottom')],
            ['value' => self::PAYMENT_TOP, 'label' => __('Payment Method Top')],
            ['value' => self::PAYMENT_BOTTOM, 'label' => __('Payment Method Bottom')],
            ['value' => self::ORDER_SUMMARY, 'label' => __('Order Summary')],
        ];
        $steps   = $this->_stepFactory->create()->getCollection()->getItems();
        foreach ($steps as $step) {
            $code = $step->getData('code');
            $name = $step->getData('name');
            if ($code && $name) {
                $options[] = ['value' => $code, 'label' => __($name)];
            }
        }

        return $options;
    }
}
