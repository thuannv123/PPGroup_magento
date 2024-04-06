<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\Exception\LocalizedException;
use Firebear\PlatformFeeds\Helper\Data;

class PortionCategory extends Action
{
    /**
     * @var DbHelper
     */
    public $dbHelper;

    /**
     * @var Data
     */
    public $helper;

    /**
     * PortionCategory constructor.
     * @param Context $context
     * @param DbHelper $dbHelper
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        DbHelper $dbHelper,
        Data $helper
    ) {
        parent::__construct($context);
        $this->dbHelper = $dbHelper;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $formData = $this->getRequest()->getPostValue();
        $id = $formData['entity_id'];
        //$title = $formData['title'];
        $typeId = $formData['type_id'];
        $findName = $formData['name_category'];

        if (!$id && $typeId) {
            $id = $this->helper->getNextEntityId();
        }

        $categories = $this->helper->getCategoriesCache("feed_categories_{$typeId}_{$id}");

        $returnCategories = [];
        foreach ($categories as $id => $CategoryName) {
            if (stripos($CategoryName, $findName) !== false) {
                $returnCategories[] = [
                    'id' => $id,
                    'name' =>$CategoryName
                ];
            }
        }

        try {
            $result->setData($returnCategories);
        } catch (LocalizedException $e) {
            $result->setData(['error' => $e->getMessage()]);
        }

        return $result;
    }
}
