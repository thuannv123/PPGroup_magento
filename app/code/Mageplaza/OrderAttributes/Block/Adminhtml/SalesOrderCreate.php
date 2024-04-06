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

namespace Mageplaza\OrderAttributes\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Swatches\Helper\Media;
use Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer\Checkboxes;
use Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer\Radios;
use Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer\Time;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\Config\Source\PositionStep;
use Mageplaza\OrderAttributes\Model\Config\Source\Status;
use Mageplaza\OrderAttributes\Model\ResourceModel\Attribute\CollectionFactory;
use Mageplaza\OrderAttributes\Model\StepFactory;

/**
 * Class SalesOrderCreate
 * @package Mageplaza\OrderAttributes\Block\Adminhtml
 */
class SalesOrderCreate extends AbstractForm
{
    /**
     * @var array
     */
    protected $_attributes = [];

    /**
     * @var bool
     */
    protected $isSummaryForm = false;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Media
     */
    protected $swatchHelper;

    /**
     * @var StepFactory
     */
    public $stepFactory;

    /**
     * Additional constructor.
     *
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param FormFactory $formFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param Media $swatchHelper
     * @param StepFactory $stepFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        FormFactory $formFactory,
        DataObjectProcessor $dataObjectProcessor,
        Data $dataHelper,
        CollectionFactory $collectionFactory,
        Media $swatchHelper,
        StepFactory $stepFactory,
        array $data = []
    ) {
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
        $this->swatchHelper      = $swatchHelper;
        $this->stepFactory       = $stepFactory;

        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this|AbstractForm
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareForm()
    {
        $stepFieldsets = [];
        if ($this->isSummaryForm) {
            foreach ($this->getBeforeShippingStepsCollection() as $step) {
                $stepFieldsets = $this->addCustomStepToForm($step, $this->_form, $stepFieldsets);
            }
            if ($this->isStepHasAttributes(1)) {
                $fieldset = $this->_form->addFieldset('shipping_address', ['legend' => 'Shipping Address', 'collapsable' => false]);
            }
            foreach ($this->getAfterShippingStepsCollection() as $step) {
                $stepFieldsets = $this->addCustomStepToForm($step, $this->_form, $stepFieldsets);
            }
            if ($this->isStepHasAttributes(6)) {
                $fieldset = $this->_form->addFieldset('order_summary', ['legend' => 'Order Summary', 'collapsable' => false]);
            }
        } else {
            $fieldset = $this->_form->addFieldset('additional_fieldset', ['legend' => '', 'collapsable' => false]);
        }

        $storeId  = $this->getStoreId() ?: 0;

        /** @var Attribute $attribute */
        foreach ($this->getAttributes() as $attribute) {
            $tooltips      = Data::jsonDecode($attribute->getTooltips());
            $tooltip       = !empty($tooltips[$storeId]) ? $tooltips[$storeId] : null;
            $default       = $attribute->getDefaultValue();
            $options       = [];
            $frontendInput = $attribute->getFrontendInput();
            $class         = $attribute->getFrontendClass();
            $config        = [
                'name'        => 'mpOrderAttributes[' . $attribute->getAttributeCode() . ']',
                'label'       => $attribute->getFrontendLabel(),
                'title'       => $attribute->getFrontendLabel(),
                'required'    => $attribute->getIsRequired() === '1',
                'date_format' => $this->_localeDate->getDateFormatWithLongYear()
            ];
            switch ($frontendInput) {
                case 'text':
                case 'textarea':
                    if ($attribute->getMinTextLength() || $attribute->getMaxTextLength()) {
                        $class .= 'validate-length';
                    }

                    if ($attribute->getMinTextLength()) {
                        $class .= ' minimum-length-' . $attribute->getMinTextLength();
                    }

                    if ($attribute->getMaxTextLength()) {
                        $class .= ' maximum-length-' . $attribute->getMaxTextLength();
                    }
                    break;
                case 'boolean':
                    $options = [
                        ['value' => '', 'label' => __('Please select an option')],
                        ['value' => '0', 'label' => __('No')],
                        ['value' => '1', 'label' => __('Yes')]
                    ];
                    break;
                case 'select':
                    array_unshift($options, ['value' => '', 'label' => __('Please select an option')]);
                    // no break
                case 'multiselect':
                    $attrOptions = Data::jsonDecode($attribute->getOptions());
                    foreach ($attrOptions['option']['value'] as $index => $item) {
                        $optionLabel = empty($item[$storeId]) ? $item[0] : $item[$storeId];
                        $options[]   = [
                            'value' => $index,
                            'label' => __($optionLabel)
                        ];
                    }
                    if (isset($attrOptions['default'])) {
                        $default = implode(',', $attrOptions['default']);
                    }
                    break;
                case 'select_visual':
                case 'multiselect_visual':
                    $attrOptions = Data::jsonDecode($attribute->getOptions());
                    foreach ($attrOptions['optionvisual']['value'] as $index => $item) {
                        $swatchData  = $this->dataHelper->jsonDecodeData($attribute->getAdditionalData());
                        $optionLabel = empty($item[$storeId]) ? $item[0] : $item[$storeId];
                        $options[]   = [
                            'value'  => $index,
                            'label'  => __($optionLabel),
                            'visual' => $this->reformatSwatchLabels($swatchData[$index]['swatch_value'])
                        ];
                    }
                    if (isset($attrOptions['defaultvisual'])) {
                        $default = implode(',', $attrOptions['defaultvisual']);
                    }
                    break;
                case 'date':
                    $default = $default ? date('m/d/Y', strtotime($default)) : null;
                    break;
                case 'datetime':
                    $default               = $default ? date('m/d/Y H:i:s', strtotime($default)) : null;
                    $config['time_format'] = 'H:m:s';
                    $frontendInput         = 'date';
                    break;
                case 'time':
                    unset($config['date_format']);
                    $config['time_format'] = 'HH:mm:ss';
                    break;
                default:
                    $options = [];
                    break;
            }

            $config['class']  = $class;
            $config['values'] = $options;
            $config['value']  = $this->getQuote()->getData($attribute->getAttributeCode()) ?: $default;

            $type = $this->dataHelper->getFieldTypeByInputType($frontendInput);
            $id   = 'mpOrderAttributes-' . $attribute->getAttributeCode();

            if (!in_array($attribute->getPosition(), [1,2,3,4,5,6])) {
                $fieldset = $stepFieldsets[$attribute->getPosition()];
            }

            if ($frontendInput === 'date') {
                $field = $fieldset->addField(
                    $id,
                    $type,
                    $config
                )->setAfterElementHtml($this->getHtmlDate($attribute))->setSize(5);
            } elseif ($frontendInput === 'time') {
                $field = $fieldset->addField(
                    $id,
                    Time::class,
                    $config
                )->setAfterElementHtml($this->getHtmlTime($attribute))->setSize(5);
            } elseif ($frontendInput === 'select_visual') {
                $field = $fieldset->addField(
                    $id,
                    Radios::class,
                    $config
                )->setSize(5);
            } elseif ($frontendInput === 'multiselect_visual') {
                $field = $fieldset->addField(
                    $id,
                    Checkboxes::class,
                    $config
                )->setSize(5);
            } else {
                $field = $fieldset->addField(
                    $id,
                    $type === 'content' ? 'textarea' : $type,
                    $config
                )->setSize(5);
            }

            if ($attribute->getUseTooltip() && $tooltip) {
                $tooltip = '<div class="admin__field-tooltip tooltip">
                                <span class="admin__field-tooltip-action action-help"></span>
                                <span class="admin__field-tooltip-content">' . $tooltip . '</span >
                            </div>';
                $field->setAfterElementHtml($tooltip);
            }
        }

        $this->setForm($this->_form);

        return $this;
    }

    /**
     * @param null $position
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAttributes($position = null)
    {
        if (!$this->dataHelper->isEnabled($this->getStoreId())) {
            return [];
        }
        if ($position && in_array(1, $position)) {
            $this->isSummaryForm = true;
        }

        if (count($this->_attributes)) {
            return $this->_attributes;
        }

        $attributes = $this->collectionFactory->create()->addFieldToFilter('position', ['in' => $position]);
        foreach ($attributes as $attribute) {
            if ($this->dataHelper->isVisible(
                $attribute,
                $this->getStoreId(),
                $this->getQuote()->getCustomerGroupId()
            )
            ) {
                $this->_attributes[] = $attribute;
            }
        }

        return $this->_attributes;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $data = [
            'loadBaseUrl'            => $this->_urlBuilder->getUrl('mporderattributes/salesordercreate/index'),
            'selectedShippingMethod' => $this->_orderCreate->getShippingAddress()->getShippingMethod(),
            'selectedCountryId'      => $this->_orderCreate->getShippingAddress()->getCountryId(),
            'attributes'             => [],
            'attributeDepend'        => [],
            'shippingDepend'         => [],
            'countryDepend'          => [],
            'contentType'            => [],
            'tinymceConfig'          => $this->dataHelper->getTinymceConfig()
        ];

        foreach ($this->dataHelper->getFilteredAttributes(
            $this->getStoreId(),
            $this->getQuote()->getCustomerGroupId()
        ) as $attribute) {
            $data['attributes'][] = $attribute->getData();
            $frontendInput        = $attribute->getFrontendInput();

            if ($attribute->getFieldDepend() || in_array($frontendInput, ['select', 'select_visual', 'boolean'])) {
                $data['attributeDepend'][] = $attribute->getData();
            }

            if ($attribute->getShippingDepend()) {
                $data['shippingDepend'][] = $attribute->getData();
            }

            if ($attribute->getCountryDepend()) {
                $data['countryDepend'][] = $attribute->getData();
            }

            if ($frontendInput === 'textarea_visual') {
                $data['contentType'][] = $attribute->getData();
            }
        }

        return $data;
    }

    /**
     * @param Attribute $attribute
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function getHtmlDate($attribute)
    {
        $id        = 'mpOrderAttributes-' . $attribute->getAttributeCode();
        $minDate   = $attribute->getMinValueDate();
        $maxDate   = $attribute->getMaxValueDate();
        $showsTime = $attribute->getFrontendInput() === 'datetime' ? 'true' : 'false';

        $html = $this->getLayout()
            ->createBlock(Template::class)
            ->setMpElementId($id)
            ->setMpMinDate($minDate)
            ->setMpMaxDate($maxDate)
            ->setMpShowsTime($showsTime)
            ->setTemplate('Mageplaza_OrderAttributes::attribute/date.phtml')
            ->toHtml();

        return $html;
    }

    /**
     * @param Attribute $attribute
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function getHtmlTime($attribute)
    {
        $id      = 'mpOrderAttributes-' . $attribute->getAttributeCode();
        $maxDate = $attribute->getMaxValueTime()
            ? date('m/d/Y') . ' ' . date('H:i:s', strtotime($attribute->getMaxValueTime()))
            : date('m/d/Y') . ' ' . '23:59:59';
        $minDate = $attribute->getMinValueTime()
            ? date('m/d/Y') . ' ' . date('H:i:s', strtotime($attribute->getMinValueTime()))
            : date('m/d/Y') . ' ' . '00:00:00';

        $html = $this->getLayout()
            ->createBlock(Template::class)
            ->setMpElementId($id)
            ->setMpMinDate($minDate)
            ->setMpMaxDate($maxDate)
            ->setTemplate('Mageplaza_OrderAttributes::attribute/time.phtml')
            ->toHtml();

        return $html;
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
     * @return AbstractDb|AbstractCollection|null
     */
    public function getStepsCollection()
    {
        return $this->stepFactory->create()->getCollection()
            ->addFieldToFilter('status', Status::ENABLE)
            ->setOrder('sort_order', 'ASC');
    }

    /**
     * @return AbstractDb|AbstractCollection
     */
    public function getBeforeShippingStepsCollection()
    {
        return $this->getStepsCollection()->addFieldToFilter('position', PositionStep::BEFORE_SHIPPING);
    }

    /**
     * @return AbstractDb|AbstractCollection
     */
    public function getAfterShippingStepsCollection()
    {
        return $this->getStepsCollection()->addFieldToFilter('position', PositionStep::AFTER_SHIPPING);
    }

    /**
     * @return array
     */
    public function getAttributePositions()
    {
        $positions = [1, 6];
        foreach ($this->getStepsCollection() as $step) {
            $positions[] = $step->getCode();
        }
        return $positions;
    }

    /**
     * @param $step
     * @param $form
     * @param $stepFieldsets
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addCustomStepToForm($step, $form, $stepFieldsets)
    {
        $stepCode = $step->getCode();
        if ($this->isStepHasAttributes($stepCode)) {
            $stepFieldsets[$stepCode] = $form->addFieldset(
                $stepCode,
                ['legend' => $step->getName(), 'collapsable' => false]
            );
        }
        return $stepFieldsets;
    }

    /**
     * @param $stepCode
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isStepHasAttributes($stepCode)
    {
        $attributes = $this->collectionFactory->create()->addFieldToFilter('position', ['in' => $stepCode]);
        foreach ($attributes as $attribute) {
            if ($this->dataHelper->isVisible(
                $attribute,
                $this->getStoreId(),
                $this->getQuote()->getCustomerGroupId()
            )
            ) {
                return true;
            }
        }
        return false;
    }
}
