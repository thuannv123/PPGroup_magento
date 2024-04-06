<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Categories;

/**
 * Class Delete
 */
class Delete extends \Amasty\Blog\Controller\Adminhtml\Categories
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->getCategoryRepository()->deleteById($id);
                $this->getMessageManager()->addSuccessMessage(__('You have deleted the category.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->getMessageManager()->addErrorMessage(__('We can\'t find a category to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
