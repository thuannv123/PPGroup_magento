<?php

namespace WeltPixel\Quickview\Plugin;

use Magento\Framework\App\ObjectManager;

class CheckoutCustomerData
{
    /**
     * @var \WeltPixel\Quickview\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Data $taxHelper
     */
    protected $taxHelper;

    /**
     * @param \WeltPixel\Quickview\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Data $taxHelper
     */
    public function __construct(
        \WeltPixel\Quickview\Helper\Data $helper,
        \Magento\Catalog\Helper\Data $taxHelper
    ) {
        $this->helper = $helper;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @param \Magento\Checkout\CustomerData\ItemInterface $subject
     * @param $result
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    public function afterGetItemData(
        \Magento\Checkout\CustomerData\ItemInterface $subject,
        $result,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        if (!$this->helper->isAjaxCartEnabled()) {
            return $result;
        }

        $priceDisplayValue = $this->helper->getPriceDisplayValue();

        if ($priceDisplayValue == 1 || $priceDisplayValue == 3) {
            $result = \array_merge(
                ['product_initial_price' => $item->getProduct()->getPrice()],
                ['product_final_price' => $item->getProduct()->getFinalPrice()],
                $result
            );
        } else {
            $result = \array_merge(
                ['product_initial_price' => $this->taxHelper->getTaxPrice($item->getProduct(), $item->getProduct()->getPrice(), true)],
                ['product_final_price' => $this->taxHelper->getTaxPrice($item->getProduct(), $item->getProduct()->getFinalPrice(), true)],
                $result
            );
        }


        $productExcludedTaxPrice = false;
        if ($priceDisplayValue == 3) {
            $productExcludedTaxPrice = $item->getProduct()->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue('tax');
        }

        $result = \array_merge(
            ['product_vat_excluded_price' => $productExcludedTaxPrice],
            $result
        );

        return $result;
    }
}
