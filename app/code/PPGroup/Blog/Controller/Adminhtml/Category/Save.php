<?php

namespace PPGroup\Blog\Controller\Adminhtml\Category;

use Exception;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Message\MessageInterface;
use PPGroup\Blog\Helper\Data as HelperData;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\CategoryFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Backend\Helper\Js;
class Save extends \Mageplaza\Blog\Controller\Adminhtml\Category\Save
{
     /**
     *
     * @var HelperData
     */
    protected $helper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param CategoryFactory $categoryFactory
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param Js $jsHelper
     * @param HelperData $helper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Js $jsHelper,
        HelperData $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context, $coreRegistry, $categoryFactory, $resultRawFactory, $resultJsonFactory, $layoutFactory, $jsHelper);
    }

    public function execute()
    {
        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category = $this->initCategory();
            $categoryPostData = $this->getRequest()->getPostValue();
            $categoryPostData['store_ids'] = 0;
            $categoryPostData['enabled'] = 1;

            $category->addData($categoryPostData);

            $parentId = $this->getRequest()->getParam('parent');
            if (!$parentId) {
                $parentId = CategoryModel::TREE_ROOT_ID;
            }
            $parentCategory = $this->categoryFactory->create()->load($parentId);
            $category->setPath($parentCategory->getPath());
            $category->setParentId($parentId);

            try {
                $category->save();
                $this->messageManager->addSuccessMessage(__('You saved the category.'));
            } catch (AlreadyExistsException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the category.'));
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }

            $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
                MessageInterface::TYPE_ERROR
            );

            $category->load($category->getId());
            $category->addData([
                'entity_id' => $category->getId(),
                'is_active' => $category->getEnabled(),
                'parent' => $category->getParentId()
            ]);

            // to obtain truncated category name
            /** @var $block Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'category' => $category->toArray(),
                ]
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('category')) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $storeIds = $storeManager->getStores();
            $label_arr = [];
            foreach ($storeIds as $storeId) {
                $label_arr[$storeId->getId()] =  $data[$storeId->getName()];
                unset($data[$storeId->getName()]);
            }
            $data['labels'] = $this->helper->jsonEncodeData($label_arr);

            $category = $this->initCategory(false, true);
            if ($this->getRequest()->getParam('duplicate')) {
                unset($data['id']);
            }
            if (!$category) {
                $resultRedirect->setPath('mageplaza_blog/*/', ['_current' => true]);

                return $resultRedirect;
            }
           
            $category->addData($data);
            if ($posts = $this->getRequest()->getPost('selected_products')) {
                $posts = json_decode($posts, true);
                $category->setPostsData($posts);
            }

            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    $parentId = CategoryModel::TREE_ROOT_ID;
                }
                $parentCategory = $this->categoryFactory->create()->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentId);
            }

            $this->_eventManager->dispatch(
                'mageplaza_blog_category_prepare_save',
                ['category' => $category, 'request' => $this->getRequest()]
            );

            try {
                $category->save();
                $this->messageManager->addSuccessMessage(__('You saved the Blog Category.'));
                $this->_getSession()->setData('mageplaza_blog_category_data', false);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_getSession()->setData('mageplaza_blog_category_data', $data);
            }

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['_current' => true, 'id' => $category->getId()]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageplaza_blog/*/edit', ['_current' => true]);

        return $resultRedirect;
    }
}