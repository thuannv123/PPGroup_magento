<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Plugin\Controller\Adminhtml\Export\Job;

use Firebear\ImportExport\Controller\Adminhtml\Export\Job\Run as RunAction;
use Firebear\PlatformFeeds\Model\Export\DataProvider\Registry;

class Run
{
    /**
     * Controller before run
     *
     * @param RunAction $subject
     * @return null
     * @see BeforeRunAction::execute()
     */
    public function beforeExecute($subject)
    {
        $isPreview = $subject->getRequest()->getParam('preview');
        if ($isPreview == 1) {
            Registry::getInstance()->setPreviewMode(true);
        }

        return null;
    }
}
