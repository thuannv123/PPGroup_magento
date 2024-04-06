<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Integration\Model;

use Amasty\ShopbyBase\Model\UrlBuilder\UrlModifier;
use Amasty\ShopbySeo\Model\ResourceModel\Eav\Model\Entity\Attribute\Option\Collection as OptionsCollection;
use Amasty\ShopbySeo\Model\SeoOptions;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers UrlModifier
 * @magentoAppArea frontend
 * @magentoAppIsolation disabled
 * @magentoDbIsolation disabled
 */
class UrlModifierTest extends TestCase
{
    private const BASE_URL = 'http://localhost/index.php/';

    private const CATEGORY_ID = 22;

    /**
     * @var UrlModifier
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SeoOptions
     */
    private $optionsProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(UrlModifier::class);
        $this->optionsProvider = $this->objectManager->get(SeoOptions::class);
    }

    /**
     * @dataProvider urlData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 0
     */
    public function testExecute(string $currentUrl, ?string $expectedResult): void
    {
        // recollect storage
        $this->optionsProvider->loadData();

        $currentUrl = $this->processOptionIdTemplate($currentUrl);
        $result = $this->model->execute($currentUrl, self::CATEGORY_ID, true);
        $this->assertSame($expectedResult, $result, 'Wrong SEO url for input: ' . $currentUrl);
    }

    public function urlData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/option_2.html',// input
                self::BASE_URL . 'category-anchor/option_2.html',// expected result
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/option_1.html',
                self::BASE_URL . 'name/option_1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}',
                self::BASE_URL . 'name/option_1.html'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/option_2.html'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_1_ID},{OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/option_1-option_2.html'
            ],
            'multi options mixed' => [
                self::BASE_URL . 'category-anchor/option_1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/option_2-option_1.html'
            ],
            'multi options not category' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID},{OPTION_2_ID}',
                self::BASE_URL . 'name/option_1-option_2.html'
            ],
        ];
    }

    private function processOptionIdTemplate(string $currentUrl)
    {
        /** @var OptionsCollection $collection */
        $collection = Bootstrap::getObjectManager()->create(OptionsCollection::class);

        $collection->addAttributeFilter(['dropdown_attribute']);
        $optionIds = $collection->getColumnValues('option_id');

        return str_replace(['{OPTION_1_ID}', '{OPTION_2_ID}', '{OPTION_3_ID}'], $optionIds, $currentUrl);
    }

    /**
     * @dataProvider urlWithAttributeData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/filter_word filter
     */
    public function testExecuteWithAttribute(string $currentUrl, ?string $expectedResult): void
    {
        $this->testExecute($currentUrl, $expectedResult);
    }

    public function urlWithAttributeData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_1.html',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_1.html',
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/filter/dropdown_attribute-option_1.html',
                self::BASE_URL . 'name/filter/dropdown_attribute-option_1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}&test=1&shopbyAjax=1',
                self::BASE_URL . 'name/filter/dropdown_attribute-option_1.html?test=1&shopbyAjax=1'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2.html'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID},{OPTION_1_ID}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2-option_1.html'
            ],
            'multi options mixed' => [
                self::BASE_URL
                . 'category-anchor/filter/dropdown_attribute-option_1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2-option_1.html'
            ],
        ];
    }

    /**
     * @dataProvider urlWithAttributeAndCharData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/special_char -
     */
    public function testExecuteWithAttributeAndChar(string $currentUrl, ?string $expectedResult): void
    {
        $this->testExecute($currentUrl, $expectedResult);
    }

    public function urlWithAttributeAndCharData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-1.html',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-1.html',
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/dropdown_attribute-option-1.html',
                self::BASE_URL . 'name/dropdown_attribute-option-1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}&test=1&shopbyAjax=1',
                self::BASE_URL . 'name/dropdown_attribute-option-1.html?test=1&shopbyAjax=1'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2.html'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID},{OPTION_1_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2-option-1.html'
            ],
            'multi options mixed' => [
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2-option-1.html'
            ],
        ];
    }
}
