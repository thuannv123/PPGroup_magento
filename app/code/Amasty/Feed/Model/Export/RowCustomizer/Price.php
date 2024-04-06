<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice as CatalogRegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice as CatalogSpecialPrice;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Price implements RowCustomizerInterface
{
    /**
     * @var array
     */
    protected $prices = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Product
     */
    protected $export;

    /**
     * @var array
     */
    protected $data;

    public function __construct(
        StoreManagerInterface $storeManager,
        Product $export,
        ?ProductRepositoryInterface $productRepository // @deprecated
    ) {
        $this->storeManager = $storeManager;
        $this->export = $export;
    }

    /**
     * @param Collection $collection
     * @param array $productIds
     *
     * @return void
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->export->hasAttributes(Product::PREFIX_PRICE_ATTRIBUTE)) {
            $productCollection = clone $collection;
            $productCollection->clear();
            $productCollection->applyFrontendPriceLimitations();
            $productCollection->addAttributeToSelect([
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'price_type', // necessary for correct calculation of prices
                'tax_class_id'
            ]);
            $productCollection->getSelect()->columns(['maximal_price' => 'price_index.max_price']);

            $storeId = $collection->getStoreId() === Store::DEFAULT_STORE_ID
                ? $this->storeManager->getDefaultStoreView()->getId() // For getting valid configurable product price
                : $collection->getStoreId();

            $this->storeManager->setCurrentStore($storeId);
            $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
            // For parent product FormatPriceCurrency could be null.
            if ($feedProfileCurrency = $this->export->getFormatPriceCurrency()) {
                $currentCurrency->setCurrencyCode($feedProfileCurrency);
            }
            $this->storeManager->getStore()->setCurrentCurrencyCode($feedProfileCurrency);

            foreach ($productCollection->getItems() as $item) {
                $this->processItemPrices($item);
            }

            $this->storeManager->getStore()->setCurrentCurrency($currentCurrency);
        }
    }

    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        $customData[Product::PREFIX_PRICE_ATTRIBUTE] = $this->prices[$productId] ?? [];

        return $dataRow;
    }

    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    private function processItemPrices(ProductInterface $item): void
    {
        $specialPriceInfo = $item->getPriceInfo()->getPrice(CatalogSpecialPrice::PRICE_CODE);
        $finalPriceInfo = $item->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE);
        $priceInfo = $item->getPriceInfo();

        $regularPriceAmount = $priceInfo->getPrice(CatalogRegularPrice::PRICE_CODE)->getAmount();
        $specialPrice = $specialPriceInfo->getValue();
        $finalPrice = $finalPriceInfo->getAmount()->getValue(['tax', 'weee']);
        $finalTaxPrice = $finalPriceInfo->getAmount()->getValue();
        $regularPrice = $priceInfo->getPrice(CatalogRegularPrice::PRICE_CODE)->getValue();

        // in case of bundle product we must recalculate some prices manually
        if ($item->getTypeId() === BundleProductType::TYPE_CODE) {
            if ($item->getPrice()) {//fixed price
                $percentage = $priceInfo->getPrice(CatalogSpecialPrice::PRICE_CODE)->getDiscountPercent();
                if ($percentage) { //special price
                    $finalPrice = $specialPrice = $regularPriceAmount->getBaseAmount() * $percentage / 100;
                    $finalTaxPrice = $regularPriceAmount->getValue() * $percentage / 100;
                }
            } else {//dynamic price
                $finalPrice = $finalPriceInfo->getMinimalPrice()->getBaseAmount();
                if ($specialPrice < 0.0001 && $specialPrice !== false) {
                    $specialPrice = $finalPrice;
                }
            }
        }

        $this->prices[$item['entity_id']] = [
            'price' => $regularPriceAmount->getValue(['tax','weee']),
            'tax_price' => $regularPriceAmount->getValue(),
            'regular_price' => $regularPrice,
            'final_price' => $finalPrice,
            'tax_final_price' => $finalTaxPrice,
            'min_price' => $finalPriceInfo->getMinimalPrice()->getValue(['tax', 'weee']),
            'max_price' => $finalPriceInfo->getMaximalPrice()->getValue(['tax', 'weee']),
            'tax_min_price' => $finalPriceInfo->getMinimalPrice()->getValue(),
            'special_price' => $specialPrice
        ];
    }
}
