<?php

namespace PPGroup\ConfigurableProduct\Plugin;

use \Magento\ConfigurableProduct\Helper\Data as ConfigurableProductHelper;
use \Magento\Catalog\Model\Product;

class ConfigurableProductHelperDataPlugin
{
    public function aroundGetOptions(
        ConfigurableProductHelper $subject,
        callable $proceed,
        Product $currentProduct,
        array $allowedProducts
    )
    {
        $options = [];
        $allowAttributes = $subject->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                // skip salable check
                $options[$productAttributeId][$attributeValue][] = $productId;

                $options['index'][$productId][$productAttributeId] = $attributeValue;
                $options['index'][$productId]['stock'] = $product->isSalable();
            }
        }
        return $options;
    }
}
