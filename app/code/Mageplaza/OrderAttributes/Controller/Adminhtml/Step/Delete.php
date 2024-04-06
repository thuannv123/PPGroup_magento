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

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Mageplaza\OrderAttributes\Controller\Adminhtml\Step;

/**
 * Class Delete
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Step
 */
class Delete extends Step
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $stepId = $this->getRequest()->getParam('id');
        if ($stepId) {
            $formObj = $this->_initStep()->load($stepId);
            try {
                $formObj->delete();

                $this->_eventManager->dispatch(
                    'mporderattributes_step_delete',
                    ['attribute' => $formObj]
                );

                $this->messageManager->addSuccessMessage(__('The form has been deleted.'));

                return $this->returnResult('mporderattributes/*/', []);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t delete the form right now.')
                );

                return $this->returnResult('mporderattributes/*/edit', ['id' => $stepId, '_current' => true]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find an form to delete.'));

        return $this->returnResult('mporderattributes/*/', []);
    }
}
