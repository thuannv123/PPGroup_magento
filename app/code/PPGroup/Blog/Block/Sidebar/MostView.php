<?php
namespace PPGroup\Blog\Block\Sidebar;

use Mageplaza\Blog\Helper\Data;
use PPGroup\Blog\Block\Frontend;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class MostView
 * @package PPGroup\Blog\Block\Sidebar
 */
class MostView extends Frontend
{
    /**
     * @var string
     */
    protected $_object;

    /**
     * @return Collection
     */
    public function getMostViewPosts()
    {
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
            ->joinLeft(
                ['traffic' => $collection->getTable('mageplaza_blog_post_traffic')],
                'main_table.post_id=traffic.post_id',
                'numbers_view'
            )
            ->order('numbers_view DESC')
            ->limit((int)$this->helperData->getBlogConfig('sidebar/number_mostview_posts') ?: 4);

        return $collection;
    }

    /**
     * @return Collection
     */
    public function getRecentPost()
    {
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
            ->limit((int)$this->helperData->getBlogConfig('sidebar/number_recent_posts') ?: 4);

        return $collection;
    }

    /**
     * @param $dataType
     * @return mixed
     */
    public function getBlogObject($dataType)
    {
        if (!$this->_object) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $blogObject = $this->helperData->getObjectByParam($id, null, $dataType);
                if ($blogObject && $blogObject->getId()) {
                    $this->_object = $blogObject;
                }
            }
        }

        return $this->_object;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toHtml()
    {
        $layout = null;
        $categoryBlock = $this->getLayout()->getBlock('mpblog.category.post.list');

        if ($categoryBlock) {
            $layout = $this->getBlogObject(Data::TYPE_CATEGORY)->getCategoryLayout();
        }

        $postBlock = $this->getLayout()->getBlock('mpblog.post.list');
        if ($postBlock) {
            $layout = $this->getLayoutConfig();
        }

        $postDetailBlock = $this->getLayout()->getBlock('mpblog.post.view');
        if ($postDetailBlock) {
            $layout = $this->getBlogObject(Data::TYPE_POST)->getLayout();
        }

        $disableLayoutConditions = [
            'empty',
            '1column'
        ];

        if (!in_array($layout, $disableLayoutConditions)) {
            if ($this->getSideBarConfig() != 'none') {
                return parent::toHtml();
            }
        }
    }
}
