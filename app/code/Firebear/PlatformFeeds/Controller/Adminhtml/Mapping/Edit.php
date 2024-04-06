<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Mapping;

class Edit extends Mapping
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Firebear_PlatformFeeds::mapping_index');

        return $resultPage;
    }
}
