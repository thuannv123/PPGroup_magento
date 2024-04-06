<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Mapping;

class Add extends Mapping
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->resultFactory->create($this->resultFactory::TYPE_FORWARD)
            ->forward('edit');
    }
}
