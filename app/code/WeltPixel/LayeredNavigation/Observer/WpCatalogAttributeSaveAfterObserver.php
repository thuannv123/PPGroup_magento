<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_LayeredNavigation
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Weltpixel TEAM
 */

namespace WeltPixel\LayeredNavigation\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class WpCatalogAttributeSaveAfterObserver
 * @package WeltPixel\LayeredNavigation\Model\Observer
 */
class WpCatalogAttributeSaveAfterObserver implements ObserverInterface
{
    /**
     * @var array
     */
    protected $_lnInputTypes = [
        'select',
        'multiselect',
        'price'
    ];

    /**
     * @var \WeltPixel\LayeredNavigation\Model\AttributeOptions
     */
    protected $_attributeOptions;

    /**
     * @param CheckSalesRulesAvailability $checkSalesRulesAvailability
     */
    public function __construct(
        \WeltPixel\LayeredNavigation\Model\AttributeOptions $attributeOptions
    )
    {
        $this->_attributeOptions = $attributeOptions;
    }

    /**
     * After save attribute
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        $wpAttributeOptions = $this->_attributeOptions->getDisplayOptionsByAttribute($attribute->getAttributeId());

        if (in_array($attribute->getFrontendInput(), $this->_lnInputTypes)) {
            // insert
            if (!$wpAttributeOptions->getId()) {
                $categoryIds = (null !== $attribute->getWpCategoryIds() && is_array($attribute->getWpCategoryIds())) ? implode(',',$attribute->getWpCategoryIds()) : '';
                $this->_attributeOptions->setAttributeId($attribute->getAttributeId())
                    ->setDisplayOption($attribute->getWpDisplayOptions())
                    ->setVisibleOptions($attribute->getWpVisibleOptions())
                    ->setVisibleOptionsStep($attribute->getWpVisibleOptionsStep())
                    ->setIsMultiselect($attribute->getWpIsMultiselect())
                    ->setKeepOpenAfterFilter($attribute->getWpKeepOpenAfterFilter())
                    ->setShowQuantity($attribute->getWpShowQuantity())
                    ->setSortBy($attribute->getWpSortBy())
                    ->setInstantSearch($attribute->getWpInstantSearch())
                    ->setInstantSearchMobile($attribute->getWpInstantSearchMobile())
                    ->setCategoryVisibility($attribute->getWpCategoryVisibility())
                    ->setCategoryIds($categoryIds)
                    ->save();
            } // update
            else {
                $visibleItems = ($attribute->getWpDisplayOptions() == \WeltPixel\LayeredNavigation\Model\AttributeOptions::DISPLAY_OPTION_OPEN_VAL || $attribute->getWpDisplayOptions() == \WeltPixel\LayeredNavigation\Model\AttributeOptions::DISPLAY_OPTION_DEF_VAL) ? \WeltPixel\LayeredNavigation\Model\AttributeOptions::VISIBLE_OPTIONS_DEF_VAL : $attribute->getWpVisibleOptions();
                $visibleItemsStep = (!$attribute->getWpDisplayOptions() == \WeltPixel\LayeredNavigation\Model\AttributeOptions::DISPLAY_OPTION_OPEN_VAL || $attribute->getWpDisplayOptions() == \WeltPixel\LayeredNavigation\Model\AttributeOptions::DISPLAY_OPTION_DEF_VAL) ? \WeltPixel\LayeredNavigation\Model\AttributeOptions::VISIBLE_OPTIONS_STEP_DEF_VAL : $attribute->getWpVisibleOptionsStep();
                $categoryIds = (null !== $attribute->getWpCategoryIds() && is_array($attribute->getWpCategoryIds())) ? implode(',',$attribute->getWpCategoryIds()) : '';
                $this->_attributeOptions->setId($wpAttributeOptions->getId())
                    ->setDisplayOption($attribute->getWpDisplayOptions())
                    ->setVisibleOptions($visibleItems)
                    ->setVisibleOptionsStep($visibleItemsStep)
                    ->setIsMultiselect($attribute->getWpIsMultiselect())
                    ->setKeepOpenAfterFilter($attribute->getWpKeepOpenAfterFilter())
                    ->setShowQuantity($attribute->getWpShowQuantity())
                    ->setSortBy($attribute->getWpSortBy())
                    ->setInstantSearch($attribute->getWpInstantSearch())
                    ->setInstantSearchMobile($attribute->getWpInstantSearchMobile())
                    ->setCategoryVisibility($attribute->getWpCategoryVisibility())
                    ->setCategoryIds($categoryIds)
                    ->save();
            }
        }

        return $this;
    }
}
