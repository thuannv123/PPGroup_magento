<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\ResourceModel\Helper as ResourceHelper;

class Data extends AbstractHelper
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'feed_categories_config';

    /**
     * @var array
     */
    public $needyNameCategory = [
        5 => 'facebook',
        6 => 'yandex'
    ];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CacheInterface
     */
    public $cache;

    /**
     * @var SerializerInterface
     */
    public $serializer;

    /**
     * @var ResourceConnection
     */
    public $resource;

    /**
     * @var ResourceHelper
     */
    public $resourceHelper;

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param ResourceConnection $resource
     * @param ResourceHelper $resourceHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        CacheInterface $cache,
        SerializerInterface $serializer,
        ResourceConnection $resource,
        ResourceHelper $resourceHelper
    ) {
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->resourceHelper = $resourceHelper;

        parent::__construct($context);
    }

    /**
     * Get template options
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTemplateData()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        return [
            'web_url' => $store->getBaseUrl(UrlInterface::URL_TYPE_WEB),
            'media_url' => $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            'store_currency' => $store->getCurrentCurrency()->getCode(),
            'time' => date('H:i:s'),
            'date' => date('Y-m-d'),
        ];
    }

    /**
     * @param $categories
     * @param $identifier
     * @return bool
     */
    public function categoriesCacheSave($categories, $identifier)
    {
        $serializeCategories = $this->serializer->serialize($categories);
        return $this->cache->save($serializeCategories, $identifier, [self::CACHE_TAG]);
    }

    /**
     * @param $identifier
     * @return array|bool|float|int|string|null
     */
    public function getCategoriesCache($identifier)
    {
        $categories = [];
        $data = $this->cache->load($identifier);
        if (!empty($data)) {
            $categories = $this->serializer->unserialize($data);
        }

        return $categories;
    }

    /**
     * Get type_id
     *
     * @param $id
     * @return string
     */
    public function getTypeId($id)
    {
        $connection = $this->resource->getConnection();
        $table = $connection->getTableName('firebear_feed_category_mapping');
        $select = $connection->select()->from($table, 'type_id')->where('id = ?', $id);
        $result = $connection->fetchOne($select);

        return $result ? $result : '';
    }

    /**
     * Get mapping_data
     *
     * @param $id
     * @return array
     */
    public function getMappingData($id)
    {
        if (!$id) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $table = $connection->getTableName('firebear_feed_category_mapping');
        $select = $connection->select()->from($table)->where('id = ?', $id);
        $data = $connection->fetchRow($select);
        if (empty($data)) {
            return [];
        }

        $typeId = $data['type_id'];
        $result = $this->serializer->unserialize($data['mapping_data']);

        if ($result) {
            if (isset($this->needyNameCategory[$typeId])) {
                $replaceIdOnNameCategory = [];
                foreach ($result as $key => $mappingData) {
                    $mappingData['source_category_feed'] = $this->getFeedCategoryName(
                        $id,
                        $typeId,
                        $mappingData['source_category_feed']
                    );

                    $replaceIdOnNameCategory[$key] = $mappingData;
                }

                $result = $replaceIdOnNameCategory;
            }
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * @param $id
     * @param $typeId
     * @param $feedCategoryId
     * @return mixed|string
     */
    public function getFeedCategoryName($id, $typeId, $feedCategoryId)
    {
        $categories = $this->getCategoriesCache("feed_categories_{$typeId}_{$id}");
        return $categories[$feedCategoryId] ?? 'no data category';
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getNextEntityId()
    {
        $table = 'firebear_feed_category_mapping';
        $id = $this->resourceHelper->getNextAutoincrement($table);

        return $id;
    }
}
