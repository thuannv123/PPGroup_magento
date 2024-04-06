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

namespace Mageplaza\OrderAttributes\Block\Adminhtml\Step\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Config\Source\IconType;
use Mageplaza\OrderAttributes\Model\Config\Source\Status;
use Mageplaza\OrderAttributes\Model\Config\Source\ValidateRequiredFactory;
use Mageplaza\OrderAttributes\Model\Step;

/**
 * Class Main
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Step\Edit\Tab
 */
class Main extends Generic
{
    /**
     * @var Step
     */
    protected $_step = null;
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ValidateRequiredFactory
     */
    protected $validateRequiredFactory;

    protected $iconType;
    /**
     * @var Status
     */
    protected $status;

    /**
     * Main constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param IconType $iconType
     * @param Status $status
     * @param Data $dataHelper
     * @param ValidateRequiredFactory $validateRequiredFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        IconType $iconType,
        Status $status,
        Data $dataHelper,
        ValidateRequiredFactory $validateRequiredFactory,
        array $data = []
    ) {
        $this->iconType                = $iconType;
        $this->status                  = $status;
        $this->dataHelper              = $dataHelper;
        $this->validateRequiredFactory = $validateRequiredFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $stepObj = $this->getStepObject();

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('main_fieldset', ['legend' => __('General Information')]);

        if ($stepObj->getStepId()) {
            $fieldset->addField('step_id', 'hidden', ['name' => 'step_id']);
        }

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Name'),
            'title'    => __('Name'),
            'required' => true
        ]);

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField('code', 'text', [
            'name'     => 'code',
            'label'    => __('Code'),
            'title'    => __('Code'),
            'note'     => __(
                'For internal use. Must be unique with no spaces. Maximum length of attribute code must be fewer than %1 symbols.',
                EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
            ),
            'class'    => $validateClass,
            'required' => true
        ]);
        $fieldset->addField('status', 'select', [
            'name'   => 'status',
            'label'  => __('Status'),
            'title'  => __('Status'),
            'values' => $this->status->toOptionArray(),
            'value'  => 1,
            'required' => true
        ]);
        $iconType   = $fieldset->addField('icon_type', 'select', [
            'name'   => 'icon_type',
            'label'  => __('Icon Type'),
            'title'  => __('Icon Type'),
            'value'  => 2,
            'values' => $this->iconType->toOptionArray()
        ]);
        $iconCustom = $fieldset->addField('icon_type_custom', 'image', [
            'name'  => 'icon_type_custom',
            'label' => __('Icon'),
            'title' => __('Icon')
        ]);
        $link       = <<<HTML
                <a target="_blank" href="https://fontawesome.com/v5/search?m=free">here</a>
        HTML;
        $iconClass  = $fieldset->addField('icon_type_class', 'text', [
            'name'  => 'icon_type_class',
            'label' => __('Icon'),
            'title' => __('Icon'),
            'note'  => __('You can see more icons ' . $link)
        ]);
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap($iconType->getHtmlId(), $iconType->getName())
                ->addFieldMap($iconCustom->getHtmlId(), $iconCustom->getName())
                ->addFieldMap($iconClass->getHtmlId(), $iconClass->getName())
                ->addFieldDependence($iconCustom->getName(), $iconType->getName(), IconType::CUSTOM)
                ->addFieldDependence($iconClass->getName(), $iconType->getName(), IconType::CLASS_NAME)
        );
        if ($stepObj->getStepId()) {
            $form->getElement('code')->setDisabled(1);
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @inheritdoc
     */
    protected function _initFormValues()
    {
        $data = $this->getStepObject()->getData();
        $this->getForm()->addValues($data);

        return parent::_initFormValues();
    }

    /**
     * @return Step|mixed|null
     */
    protected function getStepObject()
    {
        if (null === $this->_step) {
            $this->_step = $this->_coreRegistry->registry('entity_step');
        }

        return $this->_step;
    }
}
