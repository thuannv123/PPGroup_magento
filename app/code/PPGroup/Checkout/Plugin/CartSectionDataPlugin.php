<?php

namespace PPGroup\Checkout\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;

class CartSectionDataPlugin {

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $data = $result;
        $data['lastProductAddedId'] = $this->checkoutSession->getLastAddedProductId();
        return $data;
    }
}