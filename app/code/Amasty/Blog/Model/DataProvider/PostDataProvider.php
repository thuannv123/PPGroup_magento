<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\DataProvider;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Controller\Adminhtml\Posts\Edit;
use Amasty\Blog\Model\BlogRegistry;
use Amasty\Blog\Model\DataProvider\Traits\DataProviderTrait;
use Amasty\Blog\Model\DataProvider\Traits\Fields;
use Amasty\Blog\Model\ImageProcessor;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class PostDataProvider extends AbstractDataProvider
{
    use DataProviderTrait;
    use Fields;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PostRepositoryInterface
     */
    private $repository;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var BlogRegistry
     */
    private $blogRegistry;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        AuthorRepositoryInterface $authorRepository,
        PostRepositoryInterface $postRepository,
        ImageProcessor $imageProcessor,
        PoolInterface $pool,
        BlogRegistry $blogRegistry,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->repository = $postRepository;
        $this->imageProcessor = $imageProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->authorRepository = $authorRepository;
        $this->pool = $pool;
        $this->blogRegistry = $blogRegistry;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $data = parent::getData();
        $storeId = $this->blogRegistry->registry(AbstractModifier::CURRENT_STORE_ID) ?: 0;
        $current = $this->blogRegistry->registry(Edit::CURRENT_AMASTY_BLOG_POST);
        if ($data['totalRecords'] > 0) {
            $postId = (int)$data['items'][0]['post_id'];
            $model = $this->getPost($postId);
            if ($model) {
                $postData = $model->getData();
                $author = $this->getAuthor($postData);
                $postData[PostInterface::AUTHOR_ID] = $author ? $author->getId() : null;
                $postData = $this->modifyData($model, $postData);
                $data[$postId] = $postData;
            }
        }

        if ($current && $current->getId()) {
            $this->addDataByStore($data, $storeId, $current->getId());
        }

        if ($savedData = $this->dataPersistor->get(Posts::PERSISTENT_NAME)) {
            $savedPostId = $savedData['post_id'] ?? null;
            $data[$savedPostId] = isset($data[$savedPostId])
                ? array_merge($data[$savedPostId], $savedData)
                : $savedData;
            $this->dataPersistor->clear(Posts::PERSISTENT_NAME);
        }

        return $data;
    }

    /**
     * @param $postId
     *
     * @return PostInterface|null
     */
    private function getPost($postId)
    {
        try {
            $model = $this->repository->getById($postId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $model = null;
        }

        return $model;
    }

    /**
     * @param $postData
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface|null
     */
    private function getAuthor($postData)
    {
        try {
            $authorId = (int)$postData[PostInterface::AUTHOR_ID];
            $model = $authorId ? $this->authorRepository->getById($authorId) : null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $model = null;
        }

        return $model;
    }

    /**
     * @param \Amasty\Blog\Api\Data\PostInterface $model
     * @param array $postData
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function modifyData(\Amasty\Blog\Api\Data\PostInterface $model, $postData)
    {
        $this->imageFormat($model->getPostThumbnail(), $postData, Posts::POST_THUMBNAIL);
        $this->imageFormat($model->getListThumbnail(), $postData, Posts::LIST_THUMBNAIL);

        if (isset($postData['related_post_ids']) && $postData['related_post_ids']) {
            $postData['related_post_ids'] = [
                'related_posts_container' => array_values($this->getPostsData($postData['related_post_ids']))
            ];
        }

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $postData = $modifier->modifyData($postData);
        }

        return $postData;
    }

    /**
     * @param $thumbnail
     * @param $categoryData
     * @param $fieldName
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function imageFormat($thumbnail, &$categoryData, $fieldName)
    {
        if ($thumbnail) {
            $categoryData[$fieldName . '_file'] = [
                [
                    'name' => $thumbnail,
                    'url'  => $this->imageProcessor->getThumbnailUrl($thumbnail, $fieldName)
                ]
            ];
        }
    }

    /**
     * @param array|string $postIds
     *
     * @return array
     */
    protected function getPostsData($postIds)
    {
        if (!is_array($postIds)) {
            $postIds = explode(',', $postIds);
        }
        $postCollection = $this->collectionFactory->create();
        $postCollection->addStoreWithDefault(
            (int)$this->blogRegistry->registry(AbstractModifier::CURRENT_STORE_ID)
        );
        $postCollection->addFieldToFilter('post_id', ['in' => $postIds]);

        $result = [];
        /** @var PostInterface $post */
        foreach ($postCollection->getItems() as $post) {
            $result[$post->getPostId()] = $this->fillData($post);
        }

        return $result;
    }

    /**
     * @param PostInterface $post
     *
     * @return array
     */
    protected function fillData(PostInterface $post)
    {
        return [
            'post_id'        => $post->getPostId(),
            'post_thumbnail' => $post->getListThumbnailSrc(),
            'title'          => $post->getTitle(),
            'url_key'        => $post->getUrlKey(),
            'status'         => $post->getStatus()
        ];
    }
}
