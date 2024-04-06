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
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\Config\Source\PositionStep;

/**
 * Class Front
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Step\Edit\Tab
 */
class Front extends Generic
{

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var Store
     */
    protected $systemStore;
    /**
     * @var PositionStep
     */
    protected $positionStep;


    /**
     * Front constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Data $dataHelper
     * @param CollectionFactory $groupCollectionFactory
     * @param Store $systemStore
     * @param PositionStep $positionStep
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $dataHelper,
        CollectionFactory $groupCollectionFactory,
        Store $systemStore,
        PositionStep $positionStep,
        array $data = []
    ) {
        $this->dataHelper             = $dataHelper;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->systemStore            = $systemStore;
        $this->positionStep           = $positionStep;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();


        $fieldset = $form->addFieldset('front_fieldset', ['legend' => __('Frontend Properties')]);

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
        $fieldset->addField('store_id', 'multiselect', [
            'name'     => 'store_id',
            'label'    => __('Store View'),
            'title'    => __('Store View'),
            'values'   => $this->systemStore->getStoreValuesForForm(false, true),
            'value'    => 0,
            'required' => true
        ])->setRenderer($rendererBlock)->setSize(5);

        $fieldset->addField('customer_group', 'multiselect', [
            'name'     => 'customer_group',
            'label'    => __('Customer Group'),
            'title'    => __('Customer Group'),
            'values'   => $this->groupCollectionFactory->create()->toOptionArray(),
            'value'    => 0,
            'required' => true
        ])->setSize(5);

        $fieldset->addField('position', 'select', [
            'name'     => 'position',
            'label'    => __('Position'),
            'title'    => __('Position'),
            'values'   => $this->positionStep->toOptionArray(),
            'value'    => PositionStep::AFTER_SHIPPING,
            'required' => true
        ])->setSize(5);

        $fieldset->addField('sort_order', 'text', [
            'name'     => 'sort_order',
            'label'    => __('Sort Order'),
            'title'    => __('Sort Order'),
            'class'    => 'validate-digits',
            'required' => true,
            'note'     => __('If you want "Before Shipping Step" enter [0-9], if you want "After Shipping Step" enter [11-19]')
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @inheritdoc
     */
    protected function _initFormValues()
    {
        /** @var Attribute $stepObj */
        $stepObj = $this->getStepObject();

        if ($stepObj->getData('store_id') === null) {
            $stepObj->setData('store_id', 0);
        }

        if ($stepObj->getData('customer_group') === null) {
            $group = array_keys($this->groupCollectionFactory->create()->toOptionArray());
            $stepObj->setData('customer_group', implode(',', $group));
        }

        $this->getForm()->addValues($stepObj->getData());

        return parent::_initFormValues();
    }

    /**
     * @return mixed
     */
    protected function getStepObject()
    {
        return $this->_coreRegistry->registry('entity_step');
    }
}
