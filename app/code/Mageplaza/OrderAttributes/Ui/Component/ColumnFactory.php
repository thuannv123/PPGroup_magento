<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Ui\Component;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;

/**
 * Class ColumnFactory
 * @package Mageplaza\OrderAttributes\Ui\Component
 */
class ColumnFactory
{
    /**
     * @var UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'select_visual' => 'select',
        'multiselect' => 'select',
        'multiselect_visual' => 'select',
        'date' => 'date',
    ];

    /**
     * ColumnFactory constructor.
     *
     * @param UiComponentFactory $componentFactory
     * @param Data $helperData
     */
    public function __construct(
        UiComponentFactory $componentFactory,
        Data $helperData
    ) {
        $this->componentFactory = $componentFactory;
        $this->helperData = $helperData;
    }

    /**
     * @param Attribute $attribute
     * @param ContextInterface $context
     * @param array $config
     *
     * @return UiComponentInterface
     * @throws LocalizedException
     */
    public function create($attribute, $context, array $config = [])
    {
        $frontendInput = $attribute->getFrontendInput();
        $columnName = $attribute->getAttributeCode();
        $config = array_merge([
            'label' => __($attribute->getFrontendLabel()),
            'dataType' => $this->getDataType($frontendInput),
            'component' => $this->getJsComponent($this->getDataType($frontendInput)),
            'filter' => $this->getFilterType($frontendInput)
        ], $config);

        switch ($frontendInput) {
            case 'boolean':
                $config['options'] = [
                    ['value' => '0', 'label' => __('No')],
                    ['value' => '1', 'label' => __('Yes')]
                ];
                break;
            case 'select':
            case 'multiselect':
                $attrOptions = $this->helperData->jsonDecodeData($attribute->getOptions());
                if (!empty($attrOptions['option']['value'])) {
                    foreach ($attrOptions['option']['value'] as $index => $item) {
                        $config['options'][] = [
                            'value' => $index,
                            'label' => __($item[0])
                        ];
                    }
                }
                break;
            case 'select_visual':
            case 'multiselect_visual':
                $attrOptions = $this->helperData->jsonDecodeData($attribute->getOptions());
                if (!empty($attrOptions['optionvisual']['value'])) {
                    foreach ($attrOptions['optionvisual']['value'] as $index => $item) {
                        $config['options'][] = [
                            'value' => $index,
                            'label' => __($item[0]),
                        ];
                    }
                }
                break;
            case 'date':
                $config['dateFormat'] = 'MMM d, y';
                break;
        }

        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    /**
     * @param string $dataType
     *
     * @return string
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /**
     * @param string $frontendType
     *
     * @return string
     */
    protected function getDataType($frontendType)
    {
        return isset($this->dataTypeMap[$frontendType])
            ? $this->dataTypeMap[$frontendType]
            : $this->dataTypeMap['default'];
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     *
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        $filtersMap = ['date' => 'dateRange'];
        $result = array_replace_recursive($this->dataTypeMap, $filtersMap);

        return isset($result[$frontendInput]) ? $result[$frontendInput] : $result['default'];
    }
}
