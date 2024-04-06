<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Csv extends Generic implements TabInterface
{
    public function getTabLabel()
    {
        return __('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Content');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('options_fieldset', ['legend' => __('Options')]);

        $fieldset->addField(
            'csv_column_name',
            'select',
            [
                'label' => __('Column Names'),
                'title' => __('Column Names'),
                'name' => 'csv_column_name',
                'required' => true,
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            'csv_header',
            'textarea',
            [
                'name' => 'csv_header',
                'label' => __('Header'),
                'title' => __('Header')
            ]
        );

        $fieldset->addField(
            'csv_enclosure',
            'select',
            [
                'label' => __('Fields enclosed by'),
                'title' => __('Fields enclosed by'),
                'name' => 'csv_enclosure',
                'options' => [
                    'double_quote' => __('Double Quote (")'),
                    'quote' => __("Quote (')"),
                    'space' => __('Space'),
                    'none' => __('None')
                ]
            ]
        );

        $fieldset->addField(
            'csv_delimiter',
            'select',
            [
                'label' => __('Fields separated by'),
                'title' => __('Fields separated by'),
                'name' => 'csv_delimiter',
                'options' => [
                    'comma' => __('Comma (,)'),
                    'semicolon' => __('Semicolon (;)'),
                    'pipe' => __('Pipe (|)'),
                    'tab' => __('Tab')
                ]
            ]
        );

        $fieldset->addField(
            'csv_field',
            'text',
            [
                'name' => 'csv_field',
                'label' => __('Fields'),
                'title' => __('Fields'),
                'csv_field' => $model->getCsvField()

            ]
        );

        $form->getElement(
            'csv_field'
        )->setRenderer(
            $this->getLayout()->createBlock(\Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Csv\Field::class)
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
