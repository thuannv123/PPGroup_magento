<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Mapping;

class Delete extends Mapping
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $mappingId = $this->getRequest()->getParam('id');
        if ($mappingId) {
            try {
                $model = $this->repository->getById($mappingId);
                $this->repository->delete($model);
                $this->messageManager->addSuccessMessage(__('You deleted the mapping.'));
                $result = $this->_redirect(self::INDEX_PAGE_URL);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $result = $this->_redirect(self::EDIT_PAGE_URL, ['id' => $mappingId]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a mapping to delete.'));
            $result = $this->_redirect(self::INDEX_PAGE_URL);
        }

        return $result;
    }
}
