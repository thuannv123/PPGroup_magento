<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Mapping;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class MassDelete extends Mapping
{
    /**
     * @inheritdoc
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('selected');
        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $model = $this->repository->getById($id);
                    $this->repository->delete($model);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) has(have) been deleted.', count($ids))
            );
        }

        $this->_redirect(self::INDEX_PAGE_URL);
    }
}
