<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\Shopby\Model\Layer\GetSelectedFiltersSettings;
use Amasty\Shopby\Model\Layer\IsBrandPage;
use Amasty\Shopby\Model\Source\AbstractFilterDataPosition;
use Amasty\ShopbyBase\Api\CategoryDataSetterInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Amasty\ShopbyBase\Model\Meta\GetReplacedMetaData;
use Amasty\ShopbyPage\Model\Page as PageEntity;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Content extends AbstractHelper implements CategoryDataSetterInterface
{
    public const APPLY_TO_HEADING = 'am_apply_to_heading';
    public const APPLY_TO_META = 'am_apply_to_meta';

    /**
     * @var  Category
     */
    private $category;

    /**
     * @var  \Amasty\ShopbyBase\Api\Data\OptionSettingInterface[]
     */
    private $optionSettings;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var \Amasty\Shopby\Model\Layer\FilterList
     */
    private $filterList;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $_optionHelper;

    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var  array
     */
    private $_settings = [];

    /**
     * @var  string
     */
    private $_storeId;

    /**
     * @var bool
     */
    private $_headingApplyAll;

    /**
     * @var bool
     */
    private $_metaApplyAll;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $amshopbyRequest;

    /**
     * @var \Amasty\ShopbyBase\Helper\Meta
     */
    private $metaHelper;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    /**
     * @var GetReplacedMetaData
     */
    private $getReplacedMetaData;

    /**
     * @var GetSelectedFiltersSettings
     */
    private $getSelectedFilters;

    /**
     * @var IsBrandPage
     */
    private $isBrandPage;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Model\Layer\FilterList $filterList,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionHelper,
        \Amasty\Shopby\Model\Request $amshopbyRequest,
        Data $dataHelper,
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        \Amasty\ShopbyBase\Helper\Meta $metaHelper,
        GetReplacedMetaData $getReplacedMetaData,
        GetSelectedFiltersSettings $getSelectedFilters,
        IsBrandPage $isBrandPage
    ) {
        parent::__construct($context);
        $this->pageConfig = $pageConfig;
        $this->layer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->_storeId = $storeManager->getStore()->getId();
        $this->registry = $registry;
        $this->_helper = $dataHelper;
        $this->_optionHelper = $optionHelper;
        $this->amshopbyRequest = $amshopbyRequest;
        $this->metaHelper = $metaHelper;
        $this->baseHelper = $baseHelper;
        $this->getReplacedMetaData = $getReplacedMetaData;
        $this->initCategoryDataSettings();
        $this->getSelectedFilters = $getSelectedFilters;
        $this->isBrandPage = $isBrandPage;
    }

    private function initCategoryDataSettings()
    {
        $this->_settings['meta'] = $this->scopeConfig->getValue('amshopby/meta', ScopeInterface::SCOPE_STORE);
        $this->_settings['heading'] = $this->scopeConfig->getValue('amshopby/heading', ScopeInterface::SCOPE_STORE);
        if (!isset($this->_settings['meta']['apply_to'])) {
            $this->_settings['meta']['apply_to'] = '';
        }

        if (!isset($this->_settings['heading']['apply_to'])) {
            $this->_settings['heading']['apply_to'] = '';
        }

        $allAttributes = \Amasty\Shopby\Model\Source\Attribute\Extended::ALL;
        $this->_headingApplyAll = $this->_settings['heading'] &&
            in_array($allAttributes, explode(',', $this->_settings['heading']['apply_to']));
        $this->_metaApplyAll = $this->_settings['meta'] &&
            in_array($allAttributes, explode(',', $this->_settings['meta']['apply_to']));
    }

    /**
     * Apply filters first in order to load currently applied settings.
     * @return $this
     */
    private function applyFilters()
    {
        //at this point filters are not applied yet.
        foreach ($this->filterList->getAllFilters($this->layer) as $filter) {
            $filter->apply($this->_getRequest());
        }

        return $this;
    }

    /**
     * Return applicable data types for an attribute.
     * @param string $attributeId
     * @return array
     */
    private function getFilterDataApplicable($attributeId)
    {
        $result = [];
        if ($this->_headingApplyAll || in_array($attributeId, explode(',', $this->_settings['heading']['apply_to']))
        ) {
            $result[] = Content::APPLY_TO_HEADING;
        }

        if ($this->_metaApplyAll || in_array($attributeId, explode(',', $this->_settings['meta']['apply_to']))
        ) {
            $result[] = Content::APPLY_TO_META;
        }

        return $result;
    }

    /**
     * Get currently applied option settings which are applicable to change category data.
     * @return \Amasty\ShopbyBase\Api\Data\OptionSettingInterface[]
     */
    private function getAppliedOptionSettings()
    {
        if ($this->optionSettings === null) {
            $this->applyFilters();
            $this->optionSettings = [];
            $isBrandOrAjax = $this->isBrandPage->execute() || $this->isShopbyPageWithAjax();
            foreach ($this->getSelectedFilters->execute() as $row) {
                /** @var FilterInterface $filter */
                $filter = $row['filter'];
                /** @var Attribute $attrModel */
                $attrModel = $filter->getData('attribute_model');
                if ($attrModel === null
                    || ($isBrandOrAjax && $this->baseHelper->getBrandAttributeCode() == $attrModel->getAttributeCode())
                ) {
                    continue;
                }
                $filterApplicableTo = $this->getFilterDataApplicable($attrModel->getAttributeId());
                if (!$filterApplicableTo) {
                    continue;
                }
                /** @var FilterSettingInterface $filterSetting */
                $filterSetting = $row['setting'];
                $values = explode(',', $this->amshopbyRequest->getParam($filter->getRequestVar()));
                foreach ($values as $v) {
                    $option = $this->getOption((string)$v, $filterSetting->getAttributeCode(), $attrModel);
                    foreach ($filterApplicableTo as $applyTo) {
                        $option->setData($applyTo, true);
                    }

                    $this->optionSettings[] = $option;
                }
            }
        }

        return $this->optionSettings;
    }

    /**
     * @return bool
     */
    private function isShopbyPageWithAjax()
    {
        return $this->_request->getParam(Data::SHOPBY_AJAX)
            && $this->_request->getFullActionName() == Data::AMSHOPBY_INDEX_INDEX;
    }

    /**
     * @see \Amasty\GroupedOptions\Plugin\Shopby\Helper\Content\AddGroupOptionData
     * @param string $value
     * @param string $attributeCode
     * @param Attribute $attrModel
     *
     * @return \Amasty\ShopbyBase\Api\Data\OptionSettingInterface|\Amasty\ShopbyBase\Model\OptionSetting
     */
    public function getOption(string $value, string $attributeCode, Attribute $attrModel)
    {
        return $this->_optionHelper
            ->getSettingByValue($value, $attributeCode, $this->_storeId);
    }

    /**
     * Set category data from currently applied filters.
     * @param CategoryModel $category
     * @return $this;
     */
    public function setCategoryData(CategoryModel $category)
    {
        if (!$this->getAppliedOptionSettings()) {
            return $this;
        }

        if (is_object($this->registry->registry(PageEntity::MATCHED_PAGE))) {
            return $this;
        }

        $this->category = $category;

        $appliedBrandVal = (int) $category->getData(CategoryDataSetterInterface::APPLIED_BRAND_VALUE);
        $data = $this->getOptionsData($appliedBrandVal);

        $this->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setImg($data['img_url'])
            ->setCmsBlock($data['cms_block'])
            ->setBottomCmsBlock($data['bottom_cms_block'])
            ->setMetaTitle($data['meta_title'])
            ->setMetaDescription($data['meta_description'])
            ->setMetaKeywords($data['meta_keywords']);
        return $this;
    }

    /**
     * Get data from all applicable options.
     * @param int $appliedBrandVal
     * @return array
     */
    private function getOptionsData($appliedBrandVal)
    {
        $result = [
            'title' => [],
            'description' => [],
            'cms_block' => null,
            'bottom_cms_block' => null,
            'img_url' => null,
            'meta_title' => [],
            'meta_description' => [],
            'meta_keywords' => [],

        ];

        foreach ($this->getAppliedOptionSettings() as $opt) {
            if ($opt->getValue() === $appliedBrandVal) {
                continue;
            }

            if ($opt->getData(Content::APPLY_TO_HEADING)) {
                if ($opt->getTitle()) {
                    $result['title'][] = $opt->getTitle();
                }

                if ($opt->getDescription()) {
                    $result['description'][] = $opt->getDescription(true);
                }

                if ($opt->getTopCmsBlockId() && $result['cms_block'] === null) {
                    $result['cms_block'] = $opt->getTopCmsBlockId();
                }

                if ($opt->getBottomCmsBlockId() && $result['bottom_cms_block'] === null) {
                    $result['bottom_cms_block'] = $opt->getBottomCmsBlockId();
                }

                if ($opt->getImageUrl() && $result['img_url'] === null) {
                    $result['img_url'] = $opt->getImageUrl();
                }
            }

            if ($opt->getData(Content::APPLY_TO_META)) {
                if ($opt->getMetaTitle()) {
                    $result['meta_title'][] = $opt->getMetaTitle();
                }

                if ($opt->getMetaDescription()) {
                    $result['meta_description'][] = $opt->getMetaDescription();
                }

                if ($opt->getMetaKeywords()) {
                    $result['meta_keywords'][] = $opt->getMetaKeywords();
                }
            }
        }

        return $result;
    }

    /**
     * Set category title.
     * @param array $titles
     * @return $this
     */
    private function setTitle($titles)
    {
        $position = $this->_settings['heading']['add_title'];
        $title = $this->insertContent(
            $this->category->getName(),
            $titles,
            $position,
            $this->_settings['heading']['title_separator']
        );
        $this->category->setName($title);
        return $this;
    }

    /**
     * Set category meta title.
     * @param array $metaTitles
     * @return $this
     */
    private function setMetaTitle($metaTitles)
    {
        $position = $this->_settings['meta']['add_title'];
        $replacedData = $this->getReplacedMetaData->execute(GetReplacedMetaData::META_TITLE_IDENTIFIER);
        $originData = $replacedData ?: $this->metaHelper->getOriginPageMetaTitle($this->category);
        $metaTitle = $this->insertContent(
            $originData,
            $metaTitles,
            $position,
            $this->_settings['meta']['title_separator']
        );
        $this->category->setData(GetReplacedMetaData::META_TITLE_IDENTIFIER, $metaTitle);
        return $this;
    }

    /**
     * Set category description.
     * @param array $descriptions
     * @return $this
     */
    private function setDescription($descriptions)
    {
        $position = $this->_settings['heading']['add_description'];
        if ($descriptions && $position != AbstractFilterDataPosition::DO_NOT_ADD) {
            $oldDescription = $this->category->getData('description');
            // @codingStandardsIgnoreStart
            $description = '<span class="amshopby-descr">' . join('<br>', $descriptions) . '</span>';
            switch ($position) {
                case AbstractFilterDataPosition::AFTER:
                    $description = $oldDescription ? $oldDescription . '<br>' . $description : $description;
                    break;
                case AbstractFilterDataPosition::BEFORE:
                    $description = $oldDescription ? $description . '<br>' . $oldDescription : $description;
                    break;
            }
            // @codingStandardsIgnoreEnd
            $this->category->setData('description', $description);
        }
        return $this;
    }

    /**
     * Set category meta description.
     * @param array $metaDescriptions
     * @return $this
     */
    private function setMetaDescription(array $metaDescriptions)
    {
        $position = $this->_settings['meta']['add_description'];
        $originData = $this->getReplacedMetaData->execute(GetReplacedMetaData::META_DESCRIPTION_IDENTIFIER)
            ?: $this->metaHelper->getOriginPageMetaDescription($this->category);
        $metaDescription = $this->insertContent(
            $originData,
            $metaDescriptions,
            $position,
            $this->_settings['meta']['description_separator']
        );
        $this->category->setData('meta_description', $metaDescription);
        return $this;
    }

    /**
     * Set category meta keywords.
     * @param array $metaKeywords
     * @return $this
     */
    private function setMetaKeywords($metaKeywords)
    {
        $position = $this->_settings['meta']['add_keywords'];
        $originData = $this->getReplacedMetaData->execute(GetReplacedMetaData::META_KEYWORDS_IDENTIFIER)
            ?: $this->getOriginPageMetaKeywords();
        $metaKeyword = $this->insertContent(
            $originData,
            $metaKeywords,
            $position,
            ', '
        );
        $this->category->setData('meta_keywords', $metaKeyword);
        return $this;
    }

    /**
     * @return string
     */
    private function getOriginPageMetaKeywords()
    {
        return $this->category->getData('meta_keywords')
            ? $this->category->getData('meta_keywords')
            : $this->pageConfig->getKeywords();
    }

    /**
     * Set category image.
     * @param string|null $imgUrl
     * @return $this
     */
    private function setImg($imgUrl)
    {
        if ($imgUrl !== null && $this->_settings['heading']['replace_image']) {
            $this->category->setData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL, $imgUrl);
        }

        return $this;
    }

    /**
     * Set category CMS block.
     * @param string|null $blockId
     * @return $this
     */
    private function setCmsBlock($blockId)
    {
        if ($blockId !== null && $this->_settings['heading']['replace_cms_block']) {
            $this->category->setData('landing_page', $blockId);
            $this->category->setData(CategoryManager::CATEGORY_FORCE_MIXED_MODE, 1);
        }

        return $this;
    }

    /**
     * Set category bottom CMS block.
     * @param string|null $blockId
     * @return $this
     */
    private function setBottomCmsBlock($blockId)
    {
        if ($blockId !== null) {
            $this->category->setData('bottom_cms_block', $blockId);
            $this->category->setData(CategoryManager::CATEGORY_FORCE_MIXED_MODE, 1);
        }

        return $this;
    }

    /**
     * replace an original data considering a position and a separator.
     * @param string $original
     * @param array $newParts
     * @param string $position
     * @param string $separator
     * @return string
     */
    private function insertContent($original, $newParts, $position, $separator)
    {
        if ($newParts && $position != AbstractFilterDataPosition::DO_NOT_ADD) {
            if ($original) {
                switch ($position) {
                    case AbstractFilterDataPosition::AFTER:
                        array_unshift($newParts, $original);
                        break;
                    case AbstractFilterDataPosition::BEFORE:
                        array_push($newParts, $original);
                        break;
                }
            }
            $result = join($separator, $newParts);
        } else {
            $result = $original;
        }
        $result = $result ? $this->trim($result, $separator) : '';

        return $result;
    }

    /**
     * Trim a string considering a certain separator.
     * @param string $str
     * @param string $separator
     * @return string
     */
    private function trim($str, $separator = ',')
    {
        $str = strip_tags($str);
        $str = str_replace('"', '', $str);
        return trim($str, " " . $separator);
    }
}
