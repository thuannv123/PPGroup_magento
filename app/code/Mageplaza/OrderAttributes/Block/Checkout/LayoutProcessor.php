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

namespace Mageplaza\OrderAttributes\Block\Checkout;

use Exception;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Cms\Block\Block;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Media;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\Config\Source\Position;
use Mageplaza\OrderAttributes\Model\Step;

/**
 * Class LayoutProcessor
 * @package Mageplaza\OrderAttributes\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Media
     */
    protected $swatchHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var Step[]
     */
    protected $steps;

    /**
     * LayoutProcessor constructor.
     *
     * @param Data $helperData
     * @param Media $swatchHelper
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     */
    public function __construct(
        Data $helperData,
        Media $swatchHelper,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        LayoutInterface $layout
    ) {
        $this->helperData      = $helperData;
        $this->swatchHelper    = $swatchHelper;
        $this->customerSession = $customerSession;
        $this->storeManager    = $storeManager;
        $this->layout          = $layout;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function process($jsLayout)
    {
        if (!$this->helperData->isEnabled()) {
            return $jsLayout;
        }

        $attributes = $this->helperData->getFilteredAttributes();

        $customerHasAddress = false;
        if ($customer = $this->customerSession->getCustomer()) {
            $customerHasAddress = (count($customer->getAddresses()) > 0);
        }

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $additionalClass = '';
            $attributeCode   = $attribute->getAttributeCode();
            $oriPositionAtt  = $attribute->getOrigData('position');

            switch ($attribute->getPosition()) {
                case Position::ADDRESS:
                    if (!$customerHasAddress) {
                        $customScope = 'mpShippingAddressAttributes';
                        $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']
                        ['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']
                        ['children']['mpOrderAttributes']['children'][$attributeCode];
                    } else {
                        $customScope = 'mpShippingAddressNewAttributes';
                        $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']
                        ['shipping-step']['children']['shippingAddress']['children']['before-form']['children']
                        ['mpOrderAttributes']['children'][$attributeCode];
                    }
                    if ($this->helperData->isOscPage()) {
                        $additionalClass = 'col-mp';
                    }
                    break;
                case Position::SHIPPING_TOP:
                    $customScope = 'mpShippingMethodTopAttributes';
                    $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']
                    ['shipping-step']['children']['shippingAddress']['children']['before-shipping-method-form']
                    ['children']['mpOrderAttributes']['children'][$attributeCode];
                    break;
                case Position::SHIPPING_BOTTOM:
                    $customScope = 'mpShippingMethodBottomAttributes';
                    $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']
                    ['shipping-step']['children']['shippingAddress']['children']['mpOrderAttributes']
                    ['children'][$attributeCode];
                    break;
                case Position::PAYMENT_TOP:
                    $customScope = 'mpPaymentMethodTopAttributes';
                    $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['beforeMethods']['children']['mpOrderAttributes']
                    ['children'][$attributeCode];
                    break;
                case Position::PAYMENT_BOTTOM:
                    $customScope = 'mpPaymentMethodBottomAttributes';
                    $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['mpOrderAttributes']
                    ['children'][$attributeCode];
                    break;
                // case Position::ORDER_SUMMARY:
                default:
                    if ($this->helperData->isOscPage() && !$this->isInCheckoutStep($oriPositionAtt)) {
                        $customScope = 'mpOrderSummaryOscAttributes';
                        $fieldset    = &$jsLayout['components']['checkout']['children']['sidebar']['children']
                        ['place-order-information-left']['children']['addition-information']['children']
                        ['mpOrderAttributes']['children'][$attributeCode];
                    } elseif ($this->isInCheckoutStep($oriPositionAtt)) {
                        $customScope = $oriPositionAtt;
                        $fieldset    = &$jsLayout['components']['checkout']['children']['steps']['children']
                        [$oriPositionAtt]['children']['mpOrderAttributes']
                        ['children'][$attributeCode];
                    } else {
                        if ((int) $attribute->getPosition() === 6) {
                            $customScope = 'mpOrderSummaryAttributes';
                            $fieldset    = &$jsLayout['components']['checkout']['children']['sidebar']['children']
                            ['note']['children']['mpOrderAttributes']
                            ['children'][$attributeCode];
                        }
                    }
                    break;
            }
            if (isset($customScope)) {
                $fieldset = $this->getAttributeField($attribute, $customScope, $additionalClass);
            }
        }

        return $jsLayout;
    }

    /**
     * Convert attribute to field
     *
     * @param Attribute $attribute
     * @param string $customScope
     * @param string $additionalClass
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getAttributeField($attribute, $customScope, $additionalClass)
    {
        $frontendInput = $attribute->getFrontendInput();
        $attributeCode = $attribute->getAttributeCode();
        $component     = $this->helperData->getComponentByInputType($frontendInput);
        $elementTmpl   = $this->helperData->getElementTmplByInputType($frontendInput);
        $fieldType     = $this->helperData->getFieldTypeByInputType($frontendInput);

        $storeId = $this->storeManager->getStore()->getId();
        $label   = $this->helperData->prepareLabel($attribute, $storeId);

        $tooltips = $this->helperData->jsonDecodeData($attribute->getTooltips());
        $tooltip  = !empty($tooltips[$storeId]) ? $tooltips[$storeId] : null;

        $validation = [];
        if ($attribute->getIsRequired()) {
            $additionalClass              .= ' required';
            $validation['required-entry'] = true;
        }
        if ($attribute->getFrontendClass()) {
            $validation[$attribute->getFrontendClass()] = true;
        }

        $options = [];
        $default = $attribute->getDefaultValue();
        switch ($frontendInput) {
            case 'text':
            case 'textarea':
                if ($attribute->getMinTextLength()) {
                    $validation['min_text_length'] = (int) $attribute->getMinTextLength();
                }
                if ($attribute->getMaxTextLength()) {
                    $validation['max_text_length'] = (int) $attribute->getMaxTextLength();
                }
                break;
            case 'boolean':
                $options = [
                    ['value' => '0', 'label' => __('No')],
                    ['value' => '1', 'label' => __('Yes')]
                ];
                break;
            case 'select':
            case 'multiselect':
                $attrOptions = $this->helperData->jsonDecodeData($attribute->getOptions());
                if (!empty($attrOptions['option']['value'])) {
                    foreach ($attrOptions['option']['value'] as $index => $item) {
                        $optionLabel = empty($item[$storeId]) ? $item[0] : $item[$storeId];
                        $options[]   = [
                            'value' => $index,
                            'label' => __($optionLabel)
                        ];
                    }
                }
                if (isset($attrOptions['default'])) {
                    $default = implode(',', $attrOptions['default']);
                }
                break;
            case 'select_visual':
            case 'multiselect_visual':
                $attrOptions = $this->helperData->jsonDecodeData($attribute->getOptions());
                if (!empty($attrOptions['optionvisual']['value'])) {
                    foreach ($attrOptions['optionvisual']['value'] as $index => $item) {
                        $swatchData  = $this->helperData->jsonDecodeData($attribute->getAdditionalData());
                        $optionLabel = empty($item[$storeId]) ? $item[0] : $item[$storeId];
                        $options[]   = [
                            'value'  => $index,
                            'label'  => __($optionLabel),
                            'visual' => $this->reformatSwatchLabels($swatchData[$index]['swatch_value'])
                        ];
                    }
                }
                if (isset($attrOptions['defaultvisual'])) {
                    $default = implode(',', $attrOptions['defaultvisual']);
                }
                break;
            case 'date':
                $default         = $this->helperData->prepareDateValue($default);
                $additionalClass .= ' date';
                $options         = [
                    'isDate'          => true,
                    'mpDateFormat'    => $this->helperData->getDateFormat(),
                    'changeMonth'     => true,
                    'changeYear'      => true,
                    'minDate'         => $attribute->getMinValueDate(),
                    'maxDate'         => $attribute->getMaxValueDate(),
                    'showButtonPanel' => true,
                    'showOn'          => 'both'
                ];

                break;
            case 'datetime':
                $default         = $this->helperData->prepareDateTimeValue($default);
                $additionalClass .= ' date';
                $options         = [
                    'isDateTime'   => true,
                    'mpDateFormat' => $this->helperData->getDateFormat(),
                    'mpTimeFormat' => $this->helperData->getTimeFormat(),
                    'changeMonth'  => true,
                    'changeYear'   => true,
                    'showsTime'    => true,
                    'timeFormat'   => 'HH:mm:ss',
                    'minDate'      => $attribute->getMinValueDate(),
                    'minTime'      => date('H:i:s', strtotime($attribute->getMinValueDate() ?? '')),
                    'maxDate'      => $attribute->getMaxValueDate(),
                    'maxTime'      => date('H:i:s', strtotime($attribute->getMaxValueDate() ?? '')),
                    'showOn'       => 'both'
                ];

                break;
            case 'time':
                $timeFormat = array_search(
                    $this->helperData->getTimeFormat(),
                    $this->helperData->getTimeFormatConfig()
                );

                $default         = $default ? date($timeFormat, strtotime($default)) : null;
                $additionalClass .= ' date';
                $maxDate         = $attribute->getMaxValueTime()
                    ? date('m/d/Y') . ' ' . date('H:i:s', strtotime($attribute->getMaxValueTime()))
                    : date('m/d/Y') . ' ' . '23:59:59';
                $minDate         = $attribute->getMinValueTime()
                    ? date('m/d/Y') . ' ' . date('H:i:s', strtotime($attribute->getMinValueTime()))
                    : date('m/d/Y') . ' ' . '00:00:00';
                $minTime         = $attribute->getMinValueTime()
                    ? date('H:i:s', strtotime($attribute->getMinValueTime())) : '00:00:00';
                $maxTime         = $attribute->getMaxValueTime()
                    ? date('H:i:s', strtotime($attribute->getMaxValueTime())) : '23:59:59';
                $options         = [
                    'isTime'       => true,
                    'mpDateFormat' => $this->helperData->getDateFormat(),
                    'mpTimeFormat' => $this->helperData->getTimeFormat(),
                    'timeOnly'     => true,
                    'showSecond'   => true,
                    'showsTime'    => true,
                    'timeFormat'   => $this->helperData->getTimeFormat(),
                    'maxDate'      => $maxDate,
                    'minDate'      => $minDate,
                    'minTime'      => $minTime,
                    'maxTime'      => $maxTime,
                    'showOn'       => 'both'
                ];
                break;
            case 'cms_block':
                $default = $this->getContentCmsBlock($default);
                break;
        }

        $name = $attributeCode;
        if (strpos($frontendInput, 'multiselect') !== false) {
            $name .= '[]';
        }

        $field = [
            'component'  => $component,
            'fieldType'  => $fieldType,
            'dataScope'  => $customScope . '.' . $name,
            'label'      => $label,
            'options'    => $options,
            'caption'    => __('Please select an option'),
            'provider'   => 'mpOrderAttributesCheckoutProvider',
            'visible'    => true,
            'validation' => $validation,
            'sortOrder'  => $attribute->getSortOrder(),
            'default'    => $default,
            'content'    => $default,
            'config'     => [
                'rows'              => 5,
                'additionalClasses' => $additionalClass,
                'customScope'       => $customScope,
                'elementTmpl'       => $elementTmpl,
                'template'          => 'ui/form/field',
            ],
        ];

        if ($tooltip && $attribute->getUseTooltip()) {
            $field['config']['tooltip'] = [
                'description' => $tooltip
            ];
        }

        return $field;
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    public function getContentCmsBlock($identifier)
    {
        try {
            $content = $this->layout->createBlock(Block::class)->setBlockId($identifier)->toHtml();
        } catch (Exception $e) {
            $content = '';
        }

        return $content;
    }

    /**
     * Parse swatch labels for template
     *
     * @param string $swatchValue
     *
     * @return string
     */
    protected function reformatSwatchLabels($swatchValue)
    {
        if (strncmp($swatchValue, '#', 1) === 0) {
            return '<div class="color" style="background-color: ' . $swatchValue . '"></div>';
        }

        if (strncmp($swatchValue, '/', 1) === 0) {
            return '<img class="image" src="'
                . $this->swatchHelper->getSwatchAttributeImage('swatch_thumb', $swatchValue) . '">';
        }

        return '';
    }

    /**
     * @param $position
     *
     * @return bool
     */
    public function isInCheckoutStep($position)
    {
        $steps     = $this->getSteps();
        $stepCodes = [];
        foreach ($steps as $form) {
            $stepCodes[] = $form->getData('code');
        }
        if (count($stepCodes) === 0) {
            return false;
        }
        if (in_array($position, $stepCodes, true)) {
            return true;
        }

        return false;
    }

    /**
     * @return Step[]
     */
    public function getSteps()
    {
        if (!$this->steps) {
            $this->steps = $this->helperData->stepFactory->create()->getCollection()->getItems();
        }

        return $this->steps;
    }
}
