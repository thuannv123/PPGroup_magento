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

namespace Mageplaza\OrderAttributes\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote;
use Mageplaza\OrderAttributes\Helper\Data;
use Magento\Checkout\Model\Cart;
use Mageplaza\OrderAttributes\Model\Config\Source\IconType;
use Mageplaza\OrderAttributes\Model\Step;

/**
 * Class Head
 * @package Mageplaza\OrderAttributes\Block\Checkout
 */
class Head extends Template
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Head constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param Cart $cart
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Cart $cart,
        array $data = []
    ) {
        $this->_helperData = $helperData;
        $this->cart        = $cart;

        return parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isHaveIcon()
    {
        $stepsCodes = $this->_helperData->registry->registry('mp_order_attributes_steps');
        if (!$stepsCodes) {
            $steps = $this->_helperData->getStepCollectionFiltered($this->getQuote()->getShippingAddress())
                ->addFieldToFilter(Step::ICON_TYPE, IconType::CLASS_NAME);
        } else {
            $steps = $this->_helperData->getStepCollection()->addFieldToFilter('code', ['in' => $stepsCodes])
                ->addFieldToFilter(Step::ICON_TYPE, IconType::CLASS_NAME);
        }

        if ($steps->getSize() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->cart->getQuote();
    }
}
