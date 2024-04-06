<?php

namespace PPGroup\Checkout\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class DefaultConfigProviderPlugin
{
    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        CartItemRepositoryInterface $cartItemRepository,
        ProductRepositoryInterface $productRepository,
        CheckoutSession $checkoutSession
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetConfig(DefaultConfigProvider $subject, $result)
    {
        $items = &$result['totalsData']['items'];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        foreach ($items as $index => $item) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $quoteItem = $quote->getItemById($item['item_id']);
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($quoteItem->getProductId());
            $brandAttribute = $product->getResource()->getAttribute('brand');
            $brandValue = $brandAttribute ? $brandAttribute->getFrontend()->getValue($product) : '';
            $items[$index]['brand'] = $brandValue;
        }

        return $result;
    }
}
