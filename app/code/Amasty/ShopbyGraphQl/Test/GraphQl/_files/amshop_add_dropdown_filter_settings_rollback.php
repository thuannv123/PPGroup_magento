<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionValueProvider;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;

$objectManager = Bootstrap::getObjectManager();

/** @var FilterSettingInterface $filterSetting */
$filterSetting = $objectManager->create(FilterSettingInterface::class);

/** @var FilterSettingRepositoryInterface $filterSettingRepository */
$filterSettingRepository = $objectManager->create(FilterSettingRepositoryInterface::class);

$filterSettingRepository->deleteByAttributeCode('amshop_dropdown_attribute');
