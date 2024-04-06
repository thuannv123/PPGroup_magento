<?php

namespace PPGroup\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory;
use Mageplaza\Blog\Model\Category as MageplazaCategory;
use Mageplaza\Blog\Model\CategoryFactory;

class CategoryExtend extends MageplazaCategory
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_blog_category';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_blog_category';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_blog_category';

    /**
     * Post Collection
     *
     * @var Collection
     */
    public $postCollection;

    /**
     * Blog Category Factory
     *
     * @var CategoryFactory
     */
    public $categoryFactory;

    /**
     * Post Collection Factory
     *
     * @var CollectionFactory
     */
    public $postCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $postCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CategoryFactory $categoryFactory,
        CollectionFactory $postCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $registry, $categoryFactory, $postCollectionFactory, $categoryCollectionFactory, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Mageplaza\Blog\Model\ResourceModel\Category::class);
    }
    public function getNameStore($id,$storeId){
        $values = $this->categoryFactory->create()->load($id)->getData();
        $arr = json_decode($values['labels'],true);
        $name = $arr[$storeId];
        if($name ==''){
            $name = $values['name'];
        }
        return $name;
    }
}