<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyGraphQl\Test\GraphQl;

use Magento\Framework\Shell;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ShopbyProductsFiltersTest extends GraphQlAbstract
{
    private const MAIN_RESPONSE_KEY = 'products';
    private const AM_SHOP_BY_RESPONSE_KEY = 'amshopby_filter_data';

    /**
     * @var Shell
     */
    private $shell;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shell = Bootstrap::getObjectManager()->get(Shell::class);
    }

    /**
     * @group amasty_shopby
     *
     * @magentoConfigFixture base_website amshopby/stock_filter/enabled 1
     * @magentoConfigFixture base_website amshopby/am_is_new_filter/enabled 1
     * @magentoConfigFixture base_website amshopby/am_on_sale_filter/enabled 1
     * @magentoConfigFixture base_website cataloginventory/options/display_product_stock_status 1
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_simple_product.php
     */
    public function testAmShopbyProductsFilter(): void
    {
        $this->reindexCatalogProducts();

        $aggregationAttr = 'attr_amshop_dropdown_attribute';

        $filterAmIsNew = 1;
        $filterAmOnSale = 1;
        $filterStockStatus = 1;

        $assertArrayItems = [
            [
                'sku' => 'shop_by_simple_product'
            ]
        ];

        $assertArrayAggregation = [
            'amshopby_filter_data' => [
                'index_mode' => 1,
                'follow_mode' => 0,
                'rel_nofollow' => 0,
                'filter_code' => $aggregationAttr,
                'is_multiselect' => true,
                'display_mode' => 0,
                'display_mode_label' => "Labels",
                'is_seo_significant' => false,
                'slider_step' => 1,
                'units_label_use_currency_symbol' => 0,
                'units_label' => "test_unit_label",
                'is_expanded' => 1,
                'sort_options_by' => 0,
                'show_product_quantities' => 1,
                'is_show_search_box' => true,
                'number_unfolded_options' => 0,
                'is_use_and_logic' => false,
                'add_from_to_widget' => false,
                'visible_in_categories' => "visible_everywhere",
                'categories_filter' => "",
                'block_position' => 0,
                'top_position' => 0,
                'side_position' => 0,
                'position' => null,
                'slider_min' => 0,
                'slider_max' => 3,
                'show_icons_on_product' => false,
                'category_tree_display_mode' => 0,
                'position_label' => 0,
                'limit_options_show_search_box' => 0,
                'category_tree_depth' => null,
                'subcategories_view' => null,
                'subcategories_expand' => null,
                'render_categories_level' => null,
                'render_all_categories_tree' => null
            ],
            'options' => [
                [
                    'filter_code' => $aggregationAttr,
                    'url_alias' => 'test_url_alias',
                    'is_featured' => false,
                    'meta_title' => "A Amasty Option 1",
                    'meta_description' => 'test_meta_description',
                    'meta_keywords' => 'test_meta_keywords',
                    'title' => "A Amasty Option 1",
                    'description' => 'test_description',
                    'image' => "",
                    'top_cms_block_id' => null,
                    'bottom_cms_block_id' => null,
                    'slider_position' => 0,
                    'slider_image' => null,
                    'short_description' => "",
                    'small_image_alt' => "test_alt"
                ]
            ]
        ];

        $query = $this->getQuery($filterAmIsNew, $filterAmOnSale, $filterStockStatus);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(self::MAIN_RESPONSE_KEY, $response);
        $this->assertCount(1, $response[self::MAIN_RESPONSE_KEY]['items']);
        $this->assertResponseFields($response[self::MAIN_RESPONSE_KEY]['items'], $assertArrayItems);

        $isAssertAggregation = false;
        foreach ($response[self::MAIN_RESPONSE_KEY]['aggregations'] as $aggregation) {
            $this->assertArrayHasKey(self::AM_SHOP_BY_RESPONSE_KEY, $aggregation);
            $this->assertArrayHasKey('options', $aggregation);

            if ($aggregation[self::AM_SHOP_BY_RESPONSE_KEY]['filter_code'] == $aggregationAttr) {
                $this->assertResponseFields($aggregation, $assertArrayAggregation);
                $isAssertAggregation = true;
            }
        }

        $this->assertTrue($isAssertAggregation, "Attribute $aggregationAttr not found in response!");
    }

    private function getQuery($amOnSale, $amIsNew, $stockStatus): string
    {
        return <<<QUERY
query {
    products(filter: { am_on_sale:{eq:$amOnSale} am_is_new:{eq:$amIsNew} stock_status:{eq:$stockStatus} }) {
        items {
            sku
        }
        aggregations {
            amshopby_filter_data {
                index_mode
                follow_mode
                rel_nofollow
                filter_code
                is_multiselect
                display_mode
                display_mode_label
                is_seo_significant
                slider_step
                units_label_use_currency_symbol
                units_label
                is_expanded
                sort_options_by
                show_product_quantities
                is_show_search_box
                number_unfolded_options
                is_use_and_logic
                add_from_to_widget
                visible_in_categories
                categories_filter
                block_position
                top_position
                side_position
                position
                slider_min
                slider_max
                show_icons_on_product
                category_tree_display_mode
                position_label
                limit_options_show_search_box
                category_tree_depth
                subcategories_view
                subcategories_expand
                render_categories_level
                render_all_categories_tree
            }
            options {
                filter_code
                url_alias
                is_featured
                meta_title
                meta_description
                meta_keywords
                title
                description
                image
                top_cms_block_id
                bottom_cms_block_id
                slider_position
                slider_image
                short_description
                small_image_alt
            }
        }
    }
}
QUERY;
    }

    private function reindexCatalogProducts():void
    {
        $indexes = [
            'catalogrule_rule',
            'catalogsearch_fulltext',
            'catalog_category_product',
            'catalog_product_attribute',
            'inventory',
            'catalog_product_price',
            'cataloginventory_stock'
        ];
        $parameters = implode(' ', $indexes);

        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $this->shell->execute("php -f {$appDir}/bin/magento indexer:reindex {$parameters}");
    }
}
