<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace PPGroup\Blog\Controller\Adminhtml\Post;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\Blog\Controller\Adminhtml\Post;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\Post as PostModel;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostHistoryFactory;
use RuntimeException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Save extends Post
{
    /**
     * JS helper
     *
     * @var Js
     */
    public $jsHelper;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var PostHistoryFactory
     */
    protected $_postHistory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    protected $store;
    protected $resourceConnection;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param Js $jsHelper
     * @param Image $imageHelper
     * @param Data $helperData
     * @param PostHistoryFactory $postHistory
     * @param DateTime $date
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        Js $jsHelper,
        Image $imageHelper,
        Data $helperData,
        PostHistoryFactory $postHistory,
        DateTime $date,
        TimezoneInterface $timezone,
        StoreManagerInterface $store,
        ResourceConnection $resourceConnection
    ) {
        $this->jsHelper     = $jsHelper;
        $this->_helperData  = $helperData;
        $this->_postHistory = $postHistory;
        $this->imageHelper  = $imageHelper;
        $this->date         = $date;
        $this->timezone     = $timezone;
        $this->store        = $store;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($postFactory, $registry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var PostModel $post */
        $post = $this->initPost(false, true);
        $resultRedirect = $this->resultRedirectFactory->create();
        $action         = $this->getRequest()->getParam('action');

        if ($data = $this->getRequest()->getPost('post')) {

            $this->prepareData($post, $data);

            $this->_eventManager->dispatch(
                'mageplaza_blog_post_prepare_save',
                ['post' => $post, 'request' => $this->getRequest()]
            );
            try {
                if (empty($action) || $action === 'add') {
                    $post->save();
                    $this->messageManager->addSuccessMessage(__('The post has been saved.'));
                }
                $this->addHistory($post, $action);

                $this->_getSession()->setData('mageplaza_blog_post_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $post->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                // add Post Custom
                $dataCustom = $this->getRequest()->getPost('posts');
                $stores = $this->store->getStores();

                $dbConnection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('mageplaza_blog_post_stores');
                //Select Data from table
                $sql = "Select * FROM " . $tableName ." where post_id = ". $post->getId();
                $result = $dbConnection->fetchAll($sql);

                if($result == []){
                    $this->insertData($dataCustom,$stores,$dbConnection,$post);
                }else{
                    $this->updateData($dataCustom,$stores,$dbConnection,$post);
                }

                return $resultRedirect;
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Post.'));
            }

            $this->_getSession()->setData('mageplaza_blog_post_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $post->getId(), '_current' => true]);



            return $resultRedirect;
        }



        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }

    /**
     * @param PostModel $post
     * @param null $action
     */
    protected function addHistory($post, $action = null)
    {
        if (!empty($action)) {
            $history      = $this->_postHistory->create();
            $historyCount = $history->getSumPostHistory($post->getPostId());
            $limitHistory = (int)$this->_helperData->getConfigGeneral('history_limit');
            try {
                $data = $post->getData();
                unset(
                    $data['is_changed_tag_list'],
                    $data['is_changed_topic_list'],
                    $data['is_changed_category_list'],
                    $data['is_changed_product_list']
                );
                if ($isSave = $this->checkHistory($data)) {
                    $this->messageManager->addErrorMessage(__(
                        'Record Id %1 like the one you want to save.',
                        $isSave->getId()
                    ));
                } else {
                    if ($historyCount >= $limitHistory) {
                        $history->removeFirstHistory($post->getPostId());
                    }
                    $history->addData($data);
                    $history->save();
                }
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Post History.')
                );
            }
        }
    }

    /**
     * @param array $data
     *
     * @return DataObject|null
     */
    protected function checkHistory($data)
    {
        unset($data['updated_at']);
        $historyItems = $this->_postHistory->create()->getCollection()
            ->addFieldToFilter('post_id', $data['post_id'])->getItems();

        if (count($historyItems) < 1) {
            return null;
        }
        $data['category_ids'] = implode(',', $data['categories_ids']);
        $data['topic_ids']    = implode(',', $data['topics_ids']);
        $data['tag_ids']      = implode(',', $data['tags_ids']);
        $data['product_ids']  = Data::jsonEncode($data['products_data']);

        $result = null;
        foreach ($historyItems as $historyItem) {
            $subReturn = false;
            foreach ($historyItem->getData() as $key => $value) {
                if (array_key_exists($key, $data)) {
                    if (is_array($data[$key])) {
                        $data[$key] = trim(implode(',', $data[$key]), ',');
                    }
                    if ($data[$key] === null) {
                        $data[$key] = '';
                    }
                    if ($value === null) {
                        $value = '';
                    }
                    if ($data[$key] !== $value) {
                        $subReturn = true;
                        break;
                    }
                }
            }

            if (!$subReturn) {
                $result = $historyItem;
                break;
            }
        }

        return $result;
    }

    /**
     * @param PostModel $post
     * @param array $data
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareData($post, $data = [])
    {
        if (!$this->getRequest()->getParam('image')) {
            try {
                $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_POST, $post->getImage());
            } catch (Exception $exception) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
            }
        } else {
            $data['image'] = '';
        }

        /** Set specify field data */
        try {
            $data['publish_date'] = $this->timezone->convertConfigTimeToUtc($data['publish_date']);
        } catch (Exception $e) {
            $data['publish_date'] = $this->timezone->convertConfigTimeToUtc($this->date->date());
        }

        $data['modifier_id'] = $this->_auth->getUser()->getId();
        $data['categories_ids'] = (isset($data['categories_ids']) && $data['categories_ids']) ? explode(
            ',',
            $data['categories_ids'] ?? ''
        ) : [];
        $data['tags_ids'] = (isset($data['tags_ids']) && $data['tags_ids'])
            ? explode(',', $data['tags_ids'] ?? '') : [];
        $data['topics_ids'] = (isset($data['topics_ids']) && $data['topics_ids']) ? explode(
            ',',
            $data['topics_ids'] ?? ''
        ) : [];

        if ($post->getCreatedAt() == null) {
            $data['created_at'] = $this->date->date();
        }
        $data['updated_at'] = $this->date->date();

        $post->addData($data);

        if ($tags = $this->getRequest()->getPost('tags', false)) {
            $post->setTagsData(
                $this->jsHelper->decodeGridSerializedInput($tags)
            );
        }

        if ($topics = $this->getRequest()->getPost('topics', false)) {
            $post->setTopicsData(
                $this->jsHelper->decodeGridSerializedInput($topics)
            );
        }

        $products = $this->getRequest()->getPost('products', false);

        if ($products || $products === '') {
            $post->setProductsData(
                $this->jsHelper->decodeGridSerializedInput($products)
            );
        } else {
            $productData = [];
            foreach ($post->getProductsPosition() as $key => $value) {
                $productData[$key] = ['position' => $value];
            }
            $post->setProductsData($productData);
        }

        return $this;
    }

    protected function insertData($dataCustom,$stores,$connection,$post){
        foreach($stores as $store){
            $insertData = [];
            $insertData = [
                'post_id' => $post->getId(),
                'name' => $dataCustom['name'.$store->getName()],
                'post_content'=> $dataCustom['post_content'.$store->getName()],
                'store_id'=> $store->getId(),
                'short_description'=> $dataCustom['short_description'.$store->getName()],
            ];
            $connection->insertOnDuplicate('mageplaza_blog_post_stores', $insertData);
        }
    }
    protected function updateData($dataCustom,$stores,$connection,$post){
        $id = $post->getId();
        $tableName = $connection->getTableName("mageplaza_blog_post_stores");

        foreach($stores as $store){
            $updatedata = [];
            $where = [];
            $updatedata = [
                'post_id' => $id,
                'name' => $dataCustom['name'.$store->getName()],
                'short_description'=> $dataCustom['short_description'.$store->getName()],
                'post_content'=> $dataCustom['post_content'.$store->getName()],
                'store_id'=> $store->getId()
            ];
            $where =    ['post_id = ?' => $id,
                        'store_id = ?'=> $store->getId()
                        ];
            $sql = "Select * FROM " . $tableName ." where store_id = ". $store->getId() ." and post_id=". $id;
            $result = $connection->fetchAll($sql)[0];
            if( $result['name']                 != $dataCustom['name'.$store->getName()] ||
                $result['short_description']    != $dataCustom['short_description'.$store->getName()] ||
                $result['post_content']         != $dataCustom['post_content'.$store->getName()]
            ) {
                $connection->update($tableName,$updatedata,$where);
            }
        }
    }
}
