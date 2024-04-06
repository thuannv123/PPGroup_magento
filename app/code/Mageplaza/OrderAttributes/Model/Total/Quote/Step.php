<?php

/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\OrderAttributes\Model\Total\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Mageplaza\OrderAttributes\Helper\Data as HelperData;

/**
 * Class Step
 * @package Mageplaza\OrderAttributes\Model\Total\Quote
 */
class Step extends AbstractTotal
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Step constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this|Step
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $storeId        = $quote->getStoreId();
        $fullActionName = $this->helperData->getFullActionName();
        if (!$this->helperData->isEnabled($storeId) && $fullActionName === 'checkout_cart_add') {
            return $this;
        }
        $items = $shippingAssignment->getItems();
        if (!$items
            || in_array(
                $this->helperData->getFullActionName(),
                ['multishipping_checkout_overviewPost', 'multishipping_checkout_overview'],
                true
            )
        ) {
            return $this;
        }
        if (!$this->helperData->registry->registry('mp_order_attributes_steps')) {
            $address = $shippingAssignment->getShipping()->getAddress();
            $steps   = $this->helperData->getStepCodesFiltered(clone $address);
            $this->helperData->registry->register('mp_order_attributes_steps', $steps);
        }

        return $this;
    }
}
