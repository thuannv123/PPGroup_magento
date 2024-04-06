<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Grouped Options for Magento 2
 */

namespace Amasty\GroupedOptions\Controller\Adminhtml\Group;

class Index extends \Amasty\GroupedOptions\Controller\Adminhtml\Group
{
    public const ADMIN_RESOURCE = 'Amasty_GroupedOptions::group_options';

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_GroupedOptions::group_options')
            ->addBreadcrumb(__('Manage Grouped Options'), __('Manage Grouped Options'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Grouped Options'));

        return $resultPage;
    }
}
