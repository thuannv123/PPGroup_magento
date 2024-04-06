<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);

/** @var FilterSettingInterface $filterSetting */
$filterSetting = $objectManager->create(FilterSettingInterface::class);

/** @var FilterSettingRepositoryInterface $filterSettingRepository */
$filterSettingRepository = $objectManager->create(FilterSettingRepositoryInterface::class);

$filterSetting->setAttributeCode('amshop_dropdown_attribute');
$filterSetting->setIndexMode(1);
$filterSetting->setFollowMode(0);
$filterSetting->setRelNofollow(0);
$filterSetting->setIsMultiselect(true);
$filterSetting->setDisplayMode(0);
$filterSetting->setSeoSignificant(0);
$filterSetting->setSliderStep(1);
$filterSetting->setUnitsLabelUseCurrencySymbol(0);
$filterSetting->setUnitsLabel("test_unit_label");
$filterSetting->setIsExpanded(true);
$filterSetting->setSortOptionsBy(0);
$filterSetting->setShowProductQuantities(1);
$filterSetting->setIsShowSearchBox(true);
$filterSetting->setNumberUnfoldedOptions(0);
$filterSetting->setTooltip('test_tool');
$filterSetting->setIsUseAndLogic(false);
$filterSetting->setAddFromToWidget(false);
$filterSetting->setVisibleInCategories('visible_everywhere');
$filterSetting->setBlockPosition(0);
$filterSetting->setTopPosition(0);
$filterSetting->setSidePosition(0);
$filterSetting->setPosition(1);
$filterSetting->setSliderMin(0);
$filterSetting->setSliderMax(3);
$filterSetting->setShowIconsOnProduct(false);
$filterSetting->setCategoryTreeDisplayMode(0);
$filterSetting->setPositionLabel(0);
$filterSetting->setLimitOptionsShowSearchBox(0);

$filterSettingRepository->save($filterSetting);
