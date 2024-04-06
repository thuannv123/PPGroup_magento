<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Firebear\ImportExport\Model\Export\Product as ProductExport;
use Firebear\PlatformFeeds\Model\Export\Adapter\Product as ProductAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Firebear\PlatformFeeds\Model\Export\DataProvider\Registry;
use Firebear\PlatformFeeds\Plugin\Model\Export\Product;

class Variable extends AbstractHelper
{
    /**
     * Sometimes the fake job export isn't working and to provide product variables we need to use some hardcoded value
     */
    const DEFAULT_ROW_DATA = [
        'sku' => '24-MB01',
        'store_view_code' => 'default',
        'attribute_set_code' => 'Bag',
        'product_type' => 'simple',
        'categories' => 'Default Category/Gear,Default Category/Gear/Bags',
        'categories_position' => 'Default Category/Gear=0,Default Category/Gear/Bags=0',
        'category_ids' => '3,4',
        'product_websites' => 'base',
        'name' => 'Joust Duffle Bag',
        'description' => 'The sporty Joust Duffle Bag can\'t be beat',
        'short_description' => '',
        'weight' => '',
        'product_online' => '1',
        'tax_class_name' => '',
        'visibility' => 'Catalog, Search',
        'price' => '34.000000',
        'special_price' => '',
        'special_price_from_date' => '',
        'special_price_to_date' => '',
        'url_key' => 'joust-duffle-bag',
        'meta_title' => '',
        'meta_keywords' => '',
        'meta_description' => '',
        'base_image' => '/m/b/mb01-blue-0.jpg',
        'base_image_label' => '',
        'small_image' => '/m/b/mb01-blue-0.jpg',
        'small_image_label' => '',
        'thumbnail_image' => '/m/b/mb01-blue-0.jpg',
        'thumbnail_image_label' => '',
        'swatch_image' => '',
        'swatch_image_label' => '',
        'created_at' => '8/9/20, 6:39 PM',
        'updated_at' => '8/9/20, 6:39 PM',
        'new_from_date' => '',
        'new_to_date' => '',
        'display_product_options_in' => '',
        'map_price' => '',
        'msrp_price' => '',
        'map_enabled' => '',
        'gift_message_available' => '',
        'custom_design' => '',
        'custom_design_from' => '',
        'custom_design_to' => '',
        'custom_layout_update' => '',
        'page_layout' => '',
        'product_options_container' => '',
        'msrp_display_actual_price_type' => '',
        'country_of_manufacture' => '',
        'qty' => '100.0000',
        'out_of_stock_qty' => '0.0000',
        'use_config_min_qty' => '1',
        'is_qty_decimal' => '0',
        'allow_backorders' => '0',
        'use_config_backorders' => '1',
        'min_cart_qty' => '1.0000',
        'use_config_min_sale_qty' => '1',
        'max_cart_qty' => '0.0000',
        'use_config_max_sale_qty' => '1',
        'is_in_stock' => '1',
        'notify_on_stock_below' => '',
        'use_config_notify_stock_qty' => '1',
        'manage_stock' => '0',
        'use_config_manage_stock' => '1',
        'use_config_qty_increments' => '1',
        'qty_increments' => '0.0000',
        'use_config_enable_qty_inc' => '1',
        'enable_qty_increments' => '0',
        'is_decimal_divided' => '0',
        'website_id' => '0',
        'related_skus' => '',
        'related_position' => '',
        'crosssell_skus' => '24-WG086,24-WG083-blue,24-UG01,24-WG085_Group',
        'crosssell_position' => '1,2,3,4',
        'upsell_skus' => '24-MB02,24-MB03,24-MB05,24-MB06,24-UB02,24-WB03,24-WB04,24-WB07',
        'upsell_position' => '1,2,3,4,5,6,7,8',
        'additional_images' => '/m/b/mb01-blue-0.jpg',
        'additional_image_labels' => 'Image',
        'hide_from_product_page' => '',
        'custom_options' => '',
        'additional_attributes' => '',
        'activity' => '',
        'category_gear' => '',
        'climate' => '',
        'collar' => '',
        'color' => '',
        'cost' => '',
        'custom_layout' => '',
        'custom_layout_update_file' => '',
        'eco_collection' => '',
        'erin_recommends' => '',
        'features_bags' => '',
        'format' => '',
        'gallery' => '',
        'gender' => '',
        'manufacturer' => '',
        'material' => '',
        'merchant_center_category' => '',
        'new' => '',
        'pattern' => '',
        'performance_fabric' => '',
        'price_type' => '',
        'price_view' => '',
        'sale' => '',
        'shipment_type' => '',
        'size' => '',
        'sku_type' => '',
        'sleeve' => '',
        'strap_bags' => '',
        'style_bags' => '',
        'style_bottom' => '',
        'style_general' => '',
        'ts_country_of_origin' => '',
        'ts_dimensions_height' => '',
        'ts_dimensions_length' => '',
        'ts_dimensions_width' => '',
        'ts_hs_code' => '',
        'ts_packaging_id' => '',
        'ts_packaging_type' => '',
        'url_path' => '',
        'weight_type' => '',
        'bundle_price_type' => '',
        'bundle_sku_type' => '',
        'bundle_price_view' => '',
        'bundle_weight_type' => '',
        'bundle_values' => '',
        'bundle_shipment_type' => '',
        'associated_skus' => '',
        'configurable_variations' => '',
        'configurable_variation_labels' => '',
        'wee_tax_variations' => '',
        'video_url' => '',
        'tier_prices' => '',
        'product_id' => '1',
    ];

    /**
     * @var string
     */
    const VARIABLE_TEMPLATE = '{{ %s.%s }}';

    /**
     * @var string
     */
    const FOR_PRODUCT_TEMPLATE_BEGIN = '{% for product in set %}';

    /**
     * @var string
     */
    const FOR_PRODUCT_TEMPLATE_END = '{% endforProduct %}';

    /**
     * @var string
     */
    const FOR_IMAGE_TEMPLATE_BEGIN = '{% for image in set %}';

    /**
     * @var string
     */
    const FOR_IMAGE_TEMPLATE_END = '{% endforImage %}';

    /**
     * @var string
     */
    const IF_TEMPLATE_BEGIN = '{% if product.ATTRIBUTE_NAME == ATTRIBUTE_VALUE %}';

    /**
     * @var string
     */
    const IF_TEMPLATE_END = '{% endif %}';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductExport
     */
    protected $productExport;

    /**
     * @var ProductAdapter
     */
    protected $writer;

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param ProductExport $productExport
     * @param ProductAdapter $writer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        ProductExport $productExport,
        ProductAdapter $writer
    ) {
        $this->storeManager = $storeManager;
        $this->productExport = $productExport;
        $this->writer = $writer;

        parent::__construct($context);
    }

    /**
     * Get list of supported template variables
     *
     * @return array
     * @throws LocalizedException
     */
    public function getVariables()
    {
        return array_merge(
            $this->getRowVariables(),
            $this->getTemplateVariables(),
            $this->getSpecialVariables()
        );
    }

    /**
     * Get export rowData fields
     *
     * @see Product::aroundExport()
     * @return array
     * @throws LocalizedException
     */
    protected function getRowVariables()
    {
        Registry::getInstance()->setRowDataWriter($this->writer);

        $rowData = $this->productExport->export();
        if (empty($rowData)) {
            // Something went wrong
            $rowData = self::DEFAULT_ROW_DATA;
        }

        $options = [];
        foreach ($rowData as $key => $value) {
            $options[] = [
                'label' => $key,
                'value' => $this->getTemplateVariableCode($key, 'product'),
                'tooltip' => empty($value)
                    ? 'Got empty value. Can\'t provide an example of value'
                    : 'Example: ' . $value
            ];
        }

        return $options;
    }

    /**
     * Get special variables
     *
     * @return array
     */
    protected function getSpecialVariables()
    {
        return [
            [
                'label' => 'For each product',
                'value' => self::FOR_PRODUCT_TEMPLATE_BEGIN
                    . ' -- Put here your content -- '
                    . self::FOR_PRODUCT_TEMPLATE_END,
                'tooltip' => 'Use this operator to print feed content for each product'
            ],
            [
                'label' => 'For each gallery image',
                'value' => self::FOR_IMAGE_TEMPLATE_BEGIN
                    . ' -- Put here your content -- '
                    . self::FOR_IMAGE_TEMPLATE_END,
                'tooltip' => 'Use this operator to print feed content for each image of product'
            ],
            [
                'label' => 'If conditional operator',
                'value' => self::IF_TEMPLATE_BEGIN . ' -- Put here your content -- ' . self::IF_TEMPLATE_END,
                'tooltip' => 'Use this operator to print some value depending on attribute value'
            ]
        ];
    }

    /**
     * Get template options
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getTemplateVariables()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        return [
            [
                'label' => 'Web url',
                'value' => $this->getTemplateVariableCode('web_url', 'template'),
                'tooltip' => 'Store Web url. Example: ' . $store->getBaseUrl(UrlInterface::URL_TYPE_WEB)
            ],
            [
                'label' => 'Media url',
                'value' => $this->getTemplateVariableCode('media_url', 'template'),
                'tooltip' => 'Store Media url. Example: ' . $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            ],
            [
                'label' => 'Store currency',
                'value' => $this->getTemplateVariableCode('store_currency', 'template'),
                'tooltip' => 'Store currency. Example: ' . $store->getCurrentCurrency()->getCode()
            ],
            [
                'label' => 'Time (H:i:s)',
                'value' => $this->getTemplateVariableCode('time', 'template'),
                'tooltip' => 'Feed generation time. Example: ' . date('H:i:s')
            ],
            [
                'label' => 'Date',
                'value' => $this->getTemplateVariableCode('date', 'template'),
                'tooltip' => 'Feed generation date. Example: ' . date('Y-m-d')
            ],
        ];
    }

    /**
     * Get attribute template code
     *
     * @param string $attribute
     * @param string $namespace
     * @return string
     */
    protected function getTemplateVariableCode($attribute, $namespace)
    {
        return sprintf(self::VARIABLE_TEMPLATE, $namespace, $attribute);
    }
}
