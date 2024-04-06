<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Comments;

use Amasty\Blog\Api\Data\CommentInterface;
use Amasty\Blog\Model\Source\CommentStatus;

/**
 * Class MassInactivate
 */
class MassInactivate extends AbstractMassAction
{
    /**
     * @param CommentInterface $comment
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function itemAction($comment)
    {
        try {
            $comment->setStatus(CommentStatus::STATUS_REJECTED);
            $this->getRepository()->save($comment);
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
