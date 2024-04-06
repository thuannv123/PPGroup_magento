<?php

namespace PPGroup\Checkout\Plugin;

use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;

class DefaultItemPlugin
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepo = $productRepository;
    }

    public function afterGetItemData(
        AbstractItem $subject,
        $result,
        Item $item)
    {
        $_product = $this->productRepo->getById($item->getProduct()->getId());

        $brandAttribute = $_product->getResource()->getAttribute('brand');
        $brandValue = $brandAttribute ? $brandAttribute->getFrontend()->getValue($_product) : '';
        $data['brand'] = $brandValue;

        $data['has_error'] = $item->getHasError();

        return \array_merge(
            $result,
            $data
        );
    }

}
