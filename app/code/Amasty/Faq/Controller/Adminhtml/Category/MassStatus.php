<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package FAQ and Product Questions for Magento 2
 */

namespace Amasty\Faq\Controller\Adminhtml\Category;

use Amasty\Faq\Api\Data\CategoryInterface;

class MassStatus extends \Amasty\Faq\Controller\Adminhtml\AbstractCategoryMassAction
{
    /**
     * Set status enabled/disabled and save FAQ category
     *
     * @param CategoryInterface $category
     */
    protected function itemAction(CategoryInterface $category)
    {
        $category->setStatus($this->getRequest()->getParam('status'));
        $this->repository->save($category);
    }
}
