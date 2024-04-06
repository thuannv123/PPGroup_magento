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

namespace Mageplaza\OrderAttributes\Controller\Adminhtml\Step;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Mageplaza\OrderAttributes\Controller\Adminhtml\Step;

/**
 * Class Edit
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Step
 */
class Edit extends Step
{
    /**
     * @return Page|Redirect
     */
    public function execute()
    {
        $stepObject = $this->_initStep();

        $stepId = $this->getRequest()->getParam('id');
        if ($stepId) {
            $stepObject->load($stepId);

            if (!$stepObject->getId()) {
                $this->messageManager->addErrorMessage(__('This form no longer exists.'));

                return $this->returnResult('mporderattributes/*/', []);
            }
        }

        // restore attribute data
        $data = $stepObject->getData();
        $stepObject->addData($data);
        $stepObject->getActions()->setFormName('sales_rule_form');
        $stepObject->getActions()->setJsFormObject(
            $stepObject->getActionsFieldSetId($stepObject->getActions()->getFormName())
        );
        $this->_coreRegistry->register('entity_step', $stepObject);

        $pageTitle = $stepId ? $stepObject->getName() : __('New Order Form');

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);

        return $resultPage;
    }
}
