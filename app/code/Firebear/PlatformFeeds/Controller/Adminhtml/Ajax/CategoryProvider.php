<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Ajax;

use Magento\Framework\Exception\NotFoundException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\ImportExport\Model\ResourceModel\Helper as ResourceHelper;
use Firebear\PlatformFeeds\Helper\Data;

class CategoryProvider extends Action
{
    /**
     * @var ResourceHelper
     */
    protected $resourceHelper;

    /**
     * @var Data
     */
    public $helper;

    /**
     * CategoryProvider constructor.
     * @param Context $context
     * @param ResourceHelper $resourceHelper
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ResourceHelper $resourceHelper,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resourceHelper = $resourceHelper;
        $this->helper = $helper;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        if ($this->getRequest()->isAjax()) {
            $formData  = $this->getRequest()->getPost();
            return $resultJson->setData(
                [
                    'category_name' => $this->getCategoryName($formData)
                ]
            );
        }

        throw new NotFoundException(__('Ajax request only'));
    }

    /**
     * @param $formData
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryName($formData)
    {
        $id = $formData['id'];
        $typeId = $formData['type_id'];
        $categoryId = $formData['category_id'];
        if (!$id && $typeId) {
            $id = $this->helper->getNextEntityId();
        }
        $categories = $this->helper->getCategoriesCache("feed_categories_{$typeId}_{$id}");

        return $categories[$categoryId] ?? false;
    }
}
