<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_LayeredNavigation
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Weltpixel TEAM
 */

namespace WeltPixel\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use WeltPixel\LayeredNavigation\Helper\Data as LayerHelper;
use WeltPixel\LayeredNavigation\Model\Layer\Filter as LayerFilter;
use WeltPixel\LayeredNavigation\Model\AttributeOptions;

/**
 * Class Attribute
 * @package WeltPixel\LayeredNavigation\Model\Layer\Filter
 */
class Attribute extends AbstractFilter
{
    /**
     * @var LayerHelper
     */
    protected $_moduleHelper;

    /**
     * @var bool
     */
    protected $_isFilter = true;

    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    protected $tagFilter;

    /**
     * @var LayerFilter
     */
    protected $_layerFilter;

    /**
     * @var AttributeOptions
     */
    protected $_wpAttributeOptions;

    /**
     * @var
     */
    protected $_requestParamVal;

    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
     */
    protected $_resource;

    /**
     * @var
     */
    protected $_originalCollection;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;


    /**
     * @var int
     */
    protected $selectedOptionsCounter = 0;


    /**
     * Attribute constructor.
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $filterAttributeFactory
     * @param LayerHelper $moduleHelper
     * @param LayerFilter $layerFilter
     * @param AttributeOptions $attributeOptions
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $filterAttributeFactory,
        LayerHelper $moduleHelper,
        LayerFilter $layerFilter,
        AttributeOptions $attributeOptions,
        \Magento\Framework\Stdlib\StringUtils $string,
        array $data = []
    )
    {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_resource = $filterAttributeFactory->create();
        $this->tagFilter = $tagFilter;
        $this->_moduleHelper = $moduleHelper;
        $this->_layerFilter = $layerFilter;
        $this->_wpAttributeOptions = $attributeOptions;
        $this->string = $string;

    }

    /**
     * @inheritdoc
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return $this->_getBaseApply($request);
        }

        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue)) {
            $this->_isFilter = false;

            return $this;
        }
        $this->_requestParamVal = $this->_requestVar;
        $attributeValue = explode(',', $attributeValue);
        $this->selectedOptionsCounter = count($attributeValue);
        $state = $this->getLayer()->getState();
        foreach ($attributeValue as $value) {
            $label = $this->getOptionText($value);
            $state->addFilter($this->_createItem($label, $value));
        }
        $attribute = $this->getAttributeModel();

        if (!$this->isMultiselect($attribute->getId()) && count($attributeValue) > 1) {
            $attributeValue = array_slice($attributeValue, 0, 1);
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        if(!$this->_originalCollection) {
            $this->_originalCollection = $productCollection->getCollectionClone();
        }
        if (count($attributeValue) > 1) {
            if($this->_moduleHelper->isElasticOrOpenSearchEngine()) {
                $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
            }
            else {
                $productCollection->addFieldToFilter($attribute->getAttributeCode(), ['in' => $attributeValue]);
            }
        } else {
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue[0]);
        }

        return $this;
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
     */
    protected function _getResource()
    {
        return $this->_resource;
    }

    /**
     * @inheritdoc
     */
    protected function _getItemsData()
    {
        if (!$this->_moduleHelper->isEnabled()) {

           return $this->_getBaseItemsData();
        }

        $attribute = $this->getAttributeModel();
        $wpLnAttributeOptions = ($attribute->getId()) ? $this->_wpAttributeOptions->getDisplayOptionsByAttribute($attribute->getId()) : false;

        if(!$wpLnAttributeOptions->getIsMultiselect() && $this->_isFilter) {
            return [];
        }

        /** @var \WeltPixel\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter && ( $this->_layerFilter->isMainFilter($this) || $wpLnAttributeOptions->getIsMultiselect())) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch($attribute->getAttributeCode());
        }

        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());


        if (count($optionsFacetedData) === 0
            && $this->getAttributeIsFilterable($attribute) !== static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS
        ) {
            return $this->itemDataBuilder->build();
        }

        $productSize = $productCollection->getSize();

        $itemData = [];
        $checkCount = false;
        $options = $attribute->getFrontend()->getSelectOptions();
        $counter = false;

        foreach ($options as $option) {
            $sorted = false;
            if (empty($option['value'])) {
                continue;
            }

            $value = $option['value'];

            if($counter) {
                $count = isset($counter[$value]) ? (int)$counter[$value] : 0;
            } else {
                $count = isset($optionsFacetedData[$value]['count']) ? (int)$optionsFacetedData[$value]['count'] : 0;
            }

            // Check filter type
            if ($this->getAttributeIsFilterable($attribute) == static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS && (!$this->_layerFilter->isOptionReducesResults($this, $count, $productSize)) && $count == 0) {
                continue;
            }

            if ($count > 0) {
                $checkCount = true;
            }

            $itemData[] = [
                'label' => $this->tagFilter->filter($option['label']),
                'value' => $value,
                'count' => $count
            ];
        }


        if ($checkCount) {
            if ($wpLnAttributeOptions->getSortBy() == 2) {
                usort($itemData, [$this, '_compareAz']);
                $sorted = true;
            }
            foreach ($itemData as $item) {
                $this->itemDataBuilder->addItemData($item['label'], $item['value'], $item['count']);
            }
        }

        if ($wpLnAttributeOptions->getSortBy() == 2 && !$sorted) {
            usort($options, [$this, '_compareAz']);
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * return attribute custom 'is_multiselect' option
     *
     * @param $attrId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isMultiselect($attrId)
    {
        $wpLnAttributeOptions = ($attrId) ? $this->_wpAttributeOptions->getDisplayOptionsByAttribute($attrId) : false;
        $isMultiselect = ($wpLnAttributeOptions) ? $wpLnAttributeOptions->getIsMultiselect() : false;

        return $isMultiselect;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    protected function _compareAz($a, $b)
    {
        return strcmp($a["label"], $b["label"]);
    }

    /**
     * @param $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getBaseApply($request){
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
            $label = $this->getOptionText($attributeValue);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $attributeValue));

            $this->setItems([]); // set items to disable show filtering
            return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getBaseItemsData(){
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $options = $attribute->getFrontend()->getSelectOptions();
        $optionsCount = $this->_getResource()->getCount($this);
        foreach ($options as $option) {
            if (is_array($option['value'])) {
                continue;
            }
            if ($this->string->strlen($option['value'])) {
                // Check filter type
                if ($this->getAttributeIsFilterable($attribute) == self::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS) {
                    if (!empty($optionsCount[$option['value']])) {
                        $this->itemDataBuilder->addItemData(
                            $this->tagFilter->filter($option['label']),
                            $option['value'],
                            $optionsCount[$option['value']]
                        );
                    }
                } else {
                    $this->itemDataBuilder->addItemData(
                        $this->tagFilter->filter($option['label']),
                        $option['value'],
                        isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * @return int
     */
    public function getSelectedOptionsCounter()
    {
        return $this->selectedOptionsCounter;
    }
}
