<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);

$currentArea = $registry->registry('isSecureArea');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $productRepository->delete($productRepository->get('shop_by_simple_product'));
} catch (NoSuchEntityException $e) {
    // product already deleted.
}

$category = $getCategoryByName->execute('Category Shopby Special Test');

try {
    $categoryRepository->delete($category);
} catch (NoSuchEntityException $e) {
    // category already deleted.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', $currentArea);

Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_add_dropdown_options_settings_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_add_dropdown_filter_settings_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Amasty_ShopbyGraphQl::Test/GraphQl/_files/amshop_dropdown_attribute_rollback.php'
);
