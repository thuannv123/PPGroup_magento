<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Catalog\Model\Product;

Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_dropdown_attribute.php'
);
Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_add_dropdown_filter_settings.php'
);
Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_add_dropdown_options_settings.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);

/** @var Collection $options */
$options = $objectManager->create(Collection::class);

/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var CategoryInterfaceFactory $categoryFactory */
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setName('Category Shopby Special Test')
    ->setIsActive(true)
    ->setPosition(1);
$category = $categoryRepository->save($category);

// get created attribute
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'amshop_dropdown_attribute');
$options->setAttributeFilter($attribute->getId());
$optionIds = $options->getAllIds();

// create product with special price, attribute and as new
$product = $productFactory->create();
$productData = [
    ProductInterface::TYPE_ID => Type::TYPE_SIMPLE,
    ProductInterface::ATTRIBUTE_SET_ID => 4,
    ProductInterface::SKU => 'shop_by_simple_product',
    ProductInterface::NAME => 'Shop by Simple Product',
    ProductInterface::PRICE => 10,
    ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH,
    ProductInterface::STATUS => Status::STATUS_ENABLED,
];
$dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
/** Out of interface */
$product
    ->setWebsiteIds([1])
    ->setStockData([
        'qty' => 85.5,
        'is_in_stock' => true,
        'manage_stock' => true,
        'is_qty_decimal' => true
    ])
    ->setNewsFromDate(date("Y-m-d H:i:s"))
    ->setNewsToDate(date("Y-m-d H:i:s", strtotime('+ 2 days')))
    ->setCustomAttribute('amshop_dropdown_attribute', $optionIds[0])
    ->setSpecialPrice(5.99)
    ->setCategoryIds([$category->getId()]);

$productRepository->save($product);
