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
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as RuleConditions;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;

/**
 * Class Front
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Step\Edit\Tab
 */
class Condition extends Generic
{

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;
    /**
     * @var RuleConditions
     */
    protected $_conditions;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Data $dataHelper
     * @param Fieldset $rendererFieldset
     * @param RuleConditions $conditions
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $dataHelper,
        Fieldset $rendererFieldset,
        RuleConditions $conditions,
        array $data = []
    ) {
        $this->dataHelper        = $dataHelper;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions       = $conditions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();

        $model                = $this->getStepObject();
        $conditionsFieldSetId = 'form_conditions_fieldset';
        $formName             = 'sales_rule_form';
        $renderer             = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl(
                    'sales_rule/promo_quote/newConditionHtml/form/' . $conditionsFieldSetId,
                    ['form_namespace' => $formName]
                )
            )
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Apply the rule only if the following conditions are met (leave blank for all products).')]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'     => 'conditions',
                'label'    => __('Conditions'),
                'title'    => __('Conditions')
            ]
        )->setRule($model)->setRenderer($this->_conditions);
        $form->setValues($model->getData());
        $model->getConditions()->setJsFormObject($conditionsFieldSetId);
        $this->setConditionFormName($model->getConditions(), $formName);
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
