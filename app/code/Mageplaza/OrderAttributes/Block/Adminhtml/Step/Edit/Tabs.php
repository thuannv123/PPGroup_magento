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

namespace Mageplaza\OrderAttributes\Block\Adminhtml\Step\Edit;

/**
 * Class Tabs
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Step\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Form Information'));
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->addTab('main', [
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getChildHtml('general'),
            'active' => true
        ]);
        $this->addTab('condition', [
            'label' => __('Condition'),
            'title' => __('Condition'),
            'content' => $this->getChildHtml('condition')
        ]);
        $this->addTab('front', [
            'label' => __('Frontend Properties'),
            'title' => __('Frontend Properties'),
            'content' => $this->getChildHtml('frontend_properties')
        ]);

        return parent::_beforeToHtml();
    }
}
