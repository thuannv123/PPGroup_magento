<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Block\Adminhtml\Popup\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Display extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * Yes no options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Page Display options
     *
     * @var \Bss\Popup\Model\Source\PageDisplay
     */
    protected $pageDisplay;

    /**
     * Event Display options
     *
     * @var \Bss\Popup\Model\Source\EventDisplay
     */
    protected $eventDisplay;

    /**
     * Position options
     *
     * @var \Bss\Popup\Model\Source\Position
     */
    protected $positionOption;

    /**
     * Position options
     *
     * @var \Bss\Popup\Model\Source\FloatingPosition
     */
    protected $floatingPosition;

    /**
     * Frequently options
     *
     * @var \Bss\Popup\Model\Source\Frequently
     */
    protected $frequentlyOption;

    /**
     * Enable Button Close
     *
     * @var \Bss\Popup\Model\Source\EnableButtonClose
     */
    protected $enableButtonClose;

    /**
     * Enable Floating Icon
     *
     * @var \Bss\Popup\Model\Source\FloatingIcon
     */
    protected $floatingIcon;

    /**
     * Floating Type options
     *
     * @var \Bss\Popup\Model\Source\FloatingType
     */
    protected $floatingType;

    /**
     * Effect options
     *
     * @var \Bss\Popup\Model\Source\Animation
     *
     */
    protected $effectOption;

    /**
     * Wysiwyg Config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param \Bss\Popup\Model\Source\FloatingIcon $floatingIcon
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Bss\Popup\Model\Source\PageDisplay $pageDisplay
     * @param \Bss\Popup\Model\Source\EventDisplay $eventDisplay
     * @param \Bss\Popup\Model\Source\Position $position
     * @param \Bss\Popup\Model\Source\FloatingPosition $floatingPosition
     * @param \Bss\Popup\Model\Source\Frequently $frequentlyOption
     * @param \Bss\Popup\Model\Source\EnableButtonClose $enableButtonClose
     * @param \Bss\Popup\Model\Source\FloatingType $floatingType
     * @param \Bss\Popup\Model\Source\Animation $effectOption
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        \Bss\Popup\Model\Source\FloatingIcon $floatingIcon,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Bss\Popup\Model\Source\PageDisplay $pageDisplay,
        \Bss\Popup\Model\Source\EventDisplay $eventDisplay,
        \Bss\Popup\Model\Source\Position $position,
        \Bss\Popup\Model\Source\FloatingPosition $floatingPosition,
        \Bss\Popup\Model\Source\Frequently $frequentlyOption,
        \Bss\Popup\Model\Source\EnableButtonClose $enableButtonClose,
        \Bss\Popup\Model\Source\FloatingType $floatingType,
        \Bss\Popup\Model\Source\Animation $effectOption,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        $data = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->floatingIcon = $floatingIcon;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->enableButtonClose = $enableButtonClose;
        $this->floatingType = $floatingType;
        $this->frequentlyOption = $frequentlyOption;
        $this->booleanOptions = $booleanOptions;
        $this->pageDisplay = $pageDisplay;
        $this->eventDisplay = $eventDisplay;
        $this->floatingPosition = $floatingPosition;
        $this->positionOption = $position;
        $this->effectOption = $effectOption;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Bss\Popup\Model\Popup $Popup */
        $popup = $this->_coreRegistry->registry('bss_popup_popup');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('popup_');
        $form->setFieldNameSuffix('popup');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Display Setting'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldset->addField(
            'event_display',
            'select',
            [
                'name' => 'event_display',
                'label' => __('Display Rule'),
                'title' => __('Display Rule'),
                'values' => $this->eventDisplay->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'after_load',
            'text',
            [
                'name' => 'after_load',
                'label' => __('X equals (seconds)'),
                'title' => __('X equals (seconds)'),
                'required' => true,
                'class' => 'validate-greater-than-zero',
                'note' => __("Enter a number here. Ex: 2")
            ]
        );

        $fieldset->addField(
            'after_scroll',
            'text',
            [
                'name' => 'after_scroll',
                'label' => __('X equals (%)'),
                'title' => __('X equals (%)'),
                'required' => true,
                'display' => 'none',
                'class' => 'validate-greater-than-zero',
                'note' => __("Enter a number here. Ex: 2")
            ]
        );

        $fieldset->addField(
            'page_view',
            'text',
            [
                'name' => 'page_view',
                'label' => __('X equals'),
                'title' => __('X equals'),
                'required' => true,
                'display' => 'none',
                'class' => 'validate-greater-than-zero',
                'note' => __("Enter a number here. Ex: 2")
            ]
        );

        $fieldset->addField(
            'effect_display',
            'select',
            [
                'name' => 'effect_display',
                'label' => __('Display Animation'),
                'title' => __('Display Animation'),
                'values' => $this->effectOption->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'position',
            'select',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'values' => $this->positionOption->toOptionArray(),
            ]
        )->setValue('5');

        $fieldset->addField(
            'hide_after',
            'text',
            [
                'name' => 'hide_after',
                'label' => __('Auto Close Pop-up After'),
                'title' => __('Auto Close Pop-up After'),
                'class' => 'validate-zero-or-greater',
                'note' => __(
                    "Enter the amount of time (seconds) for the pop-up to automatically close.
                    Ex: 10. Enter “0” to disable auto close of Pop-up"
                )
            ]
        );

        $fieldset->addField(
            'frequently',
            'select',
            [
                'name' => 'frequently',
                'label' => __('Display Frequency'),
                'title' => __('Display Frequency'),
                'values' => $this->frequentlyOption->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'cookie_expires',
            'text',
            [
                'name' => 'cookie_expires',
                'label' => __('Cookie Expires (minutes)'),
                'title' => __('Cookie Expires (minutes)'),
                'required' => true,
                'class' => 'validate-greater-than-zero validate-digits',
                'display' => 'none'
            ]
        );

        $refField = $this->fieldFactory->create(
            ['fieldData' => ['value' => '1,2,3,4', 'separator' => ','], 'fieldPrefix' => '']
        );

        $fieldset->addField(
            'floating_popup',
            'select',
            [
                'name' => 'floating_popup',
                'label' => __('Enable Floating Pop-up'),
                'title' => __('Enable Floating Popup'),
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'floating_input_type',
            'select',
            [
                'name' => 'floating_input_type',
                'label' => __('Floating Pop-up Type'),
                'title' => __('Floating Type'),
                'values' => $this->floatingType->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'floating_input_content',
            'editor',
            [
                'title' => __('Floating Content'),
                'label' => __('Floating Button Text'),
                'name'  => 'floating_input_content',
                'config'    =>$this->wysiwygConfig->getConfig([
                    'add_variables' => false,
                    'add_widgets' => false,
                    'hidden'=>true
                ]),
                'rows' => '5',
                'cols' => '30',
                'wysiwyg' => true,
                'required' => true,
                'hidden'=>true
            ]
        );

        $fieldset->addField(
            'floating_icon',
            'select',
            [
                'name' => 'floating_icon',
                'label' => __('Floating Icon'),
                'title' => __('Floating Icon'),
                'values' => $this->floatingIcon->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'floating_input_color',
            'text',
            [
                'name' => 'floating_input_color',
                'class'  => 'jscolor {hash:true,refine:false}',
                'label' => __('Floating Button Fill Color'),
                'title' => __('Floating Color')
            ]
        );

        $fieldset->addField(
            'floating_position',
            'select',
            [
                'name' => 'floating_position',
                'label' => __('Floating Pop-up Position'),
                'title' => __('Floating Position'),
                'class' => 'validate-greater-than-zero',
                'values' => $this->floatingPosition->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'floating_close_button',
            'select',
            [
                'name' => 'floating_close_button',
                'label' => __('Display "Close" button'),
                'title' => __('Floating Close Button'),
                'values' => $this->enableButtonClose->toOptionArray(),
            ]
        );

        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )->addFieldMap(
                "{$htmlIdPrefix}event_display",
                'event_display'
            )->addFieldMap(
                "{$htmlIdPrefix}after_load",
                'after_load'
            )->addFieldMap(
                "{$htmlIdPrefix}after_scroll",
                'after_scroll'
            )->addFieldMap(
                "{$htmlIdPrefix}page_view",
                'page_view'
            )->addFieldMap(
                "{$htmlIdPrefix}frequently",
                'frequently'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_input_content",
                'floating_input_content'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_input_color",
                'floating_input_color'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_popup",
                'floating_popup'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_input_type",
                'floating_input_type'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_icon",
                'floating_icon'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_position",
                'floating_position'
            )->addFieldMap(
                "{$htmlIdPrefix}floating_close_button",
                'floating_close_button'
            )->addFieldMap(
                "{$htmlIdPrefix}cookie_expires",
                'cookie_expires'
            )->addFieldDependence(
                'after_load',
                'event_display',
                '1'
            )->addFieldDependence(
                'after_scroll',
                'event_display',
                '2'
            )->addFieldDependence(
                'page_view',
                'event_display',
                '3'
            )->addFieldDependence(
                'cookie_expires',
                'frequently',
                '3'
            )->addFieldDependence(
                'floating_popup',
                'event_display',
                $refField
            )->addFieldDependence(
                'floating_input_color',
                'floating_popup',
                '1'
            )->addFieldDependence(
                'floating_input_type',
                'floating_popup',
                '1'
            )->addFieldDependence(
                'floating_position',
                'floating_popup',
                '1'
            )->addFieldDependence(
                'floating_close_button',
                'floating_popup',
                '1'
            )->addFieldDependence(
                'floating_input_type',
                'event_display',
                $refField
            )->addFieldDependence(
                'floating_position',
                'event_display',
                $refField
            )->addFieldDependence(
                'floating_close_button',
                'event_display',
                $refField
            )->addFieldDependence(
                'floating_input_color',
                'event_display',
                $refField
            )->addFieldDependence(
                'floating_icon',
                'floating_input_type',
                '0'
            )->addFieldDependence(
                'floating_icon',
                'event_display',
                $refField
            )->addFieldDependence(
                'floating_icon',
                'floating_popup',
                '1'
            )->addFieldDependence(
                'floating_input_color',
                'floating_input_type',
                '1'
            )->addFieldDependence(
                'floating_popup',
                'frequently',
                '1'
            )->addFieldDependence(
                'floating_input_color',
                'frequently',
                '1'
            )->addFieldDependence(
                'floating_input_type',
                'frequently',
                '1'
            )->addFieldDependence(
                'floating_position',
                'frequently',
                '1'
            )->addFieldDependence(
                'floating_close_button',
                'frequently',
                '1'
            )->addFieldDependence(
                'floating_icon',
                'frequently',
                '1'
            )->addFieldDependence(
                'floating_input_content',
                'frequently',
                '1'
            )
        );

        /* @var $layoutBlock \Bss\Popup\Block\Adminhtml\Popup\Edit\Tab\Layout */
        $layoutBlock = $this->getLayout()->createBlock(
            \Bss\Popup\Block\Adminhtml\Popup\Edit\Tab\Layout::class
        );
        $fieldset = $form->addFieldset('layout_updates_fieldset', ['legend' => __('Layout Updates')]);
        $fieldset->addField('layout_updates', 'note', []);
        $form->getElement('layout_updates_fieldset')->setRenderer($layoutBlock);

        $popupData = $this->_session->getData('bss_popup_popup_data', true);
        if ($popupData) {
            $popup->addData($popupData);
        } else {
            if (!$popup->getId()) {
                $popup->addData($popup->getDefaultValues());
            }
        }

        $form->addValues($popup->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Display Rule');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
