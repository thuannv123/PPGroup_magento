<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Mapping;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Save extends Mapping
{
    /**
     * @inheritdoc
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data = $this->prepareData($data);
        if ($data) {
            if (empty($data['id'])) {
                $data['id'] = null;
                $model = $this->mappingFactory->create();
            } else {
                $model = $this->repository->getById($data['id']);
            }
            $model->setData($data);
            try {
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the mapping.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the mapping.'));
            }
        }

        $this->_redirect(self::INDEX_PAGE_URL);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function prepareData($data)
    {
        $data['credentials_data'] = $this->prepareCredentialsData($data);
        $data['mapping_data'] = $this->prepareMappingData($data);

        return $data;
    }

    /**
     * @param $data
     * @return bool|string|null
     */
    public function prepareCredentialsData($data)
    {
        $prepareDataArr['token'] = $data['token'] ?? '';
        $prepareDataArr['login'] = $data['login'] ?? '';
        $prepareDataArr['password'] = $data['password'] ?? '';
        $prepareDataArr = array_diff($prepareDataArr, ['']);

        return $this->getSerializeData($prepareDataArr);
    }

    /**
     * @param $data
     * @return bool|string|null
     */
    public function prepareMappingData($data)
    {
        $dataMappingArr = $data['source_category_map'] ?? '';

        return $this->getSerializeData($dataMappingArr);
    }

    /**
     * @param $data
     * @return bool|string|null
     */
    public function getSerializeData($data)
    {
        if (!empty($data)) {
            $prepareData = $this->serializer->serialize($data);
        } else {
            $prepareData = null;
        }

        return $prepareData;
    }
}
