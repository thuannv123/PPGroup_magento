<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model\Export\DataProvider;

use Magento\Framework\DataObject;
use Firebear\PlatformFeeds\Model\Export\Adapter\Product as ProductAdapter;

/**
 * @codingStandardsIgnoreFile
 * phpcs:ignoreFile
 */
class Registry extends DataObject
{
    /**
     * @var string
     */
    const DATA_KEY_SOURCE_CATEGORY =  'source_category_map';

    /**
     * @var string
     */
    const DATA_KEY_SOURCE_CATEGORY_FEED_MAPPING_ID =  'source_category_feed_mapping_list';

    /**
     * @var string
     */
    const DATA_KEY_IS_PREVIEW = 'is_preview_export';

    /**
     * @var string
     */
    const DATA_KEY_ROW_WRITER = 'attribute_row_writer';

    /**
     * @var Registry
     */
    protected static $instance = null;

    /**
     * Get Registry instance
     *
     * @return Registry
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Set source category map data
     *
     * @param array $categoryMap
     * @return Registry
     */
    public function setSourceCategoryMapData($categoryMap)
    {
        return $this->setData(self::DATA_KEY_SOURCE_CATEGORY, $categoryMap);
    }

    /**
     * Get source category map data
     *
     * @return array
     */
    public function getSourceCategoryMapData()
    {
        $categoryMap = $this->getData(self::DATA_KEY_SOURCE_CATEGORY);
        if (!is_array($categoryMap)) {
            $categoryMap = [];
        }

        return $categoryMap;
    }

    /**
     * Is export running in preview mode
     *
     * @return bool
     */
    public function isPreviewMode()
    {
        return (bool) $this->getData(self::DATA_KEY_IS_PREVIEW);
    }

    /**
     * Set preview mode
     *
     * @param bool $mode
     * @return Registry
     */
    public function setPreviewMode($mode)
    {
        return $this->setData(self::DATA_KEY_IS_PREVIEW, $mode);
    }

    /**
     * Set writer for collecting 1 row of attributes
     *
     * @param ProductAdapter $writer
     * @return Registry
     */
    public function setRowDataWriter($writer)
    {
        return $this->setData(self::DATA_KEY_ROW_WRITER, $writer);
    }

    /**
     * Get row data writer
     *
     * @return ProductAdapter|null
     */
    public function getRowDataWriter()
    {
        return $this->getData(self::DATA_KEY_ROW_WRITER);
    }

    /**
     * Set source mapping id
     *
     * @param array $mappingId
     * @return Registry
     */
    public function setMappingId($mappingId)
    {
        return $this->setData(self::DATA_KEY_SOURCE_CATEGORY_FEED_MAPPING_ID, $mappingId);
    }

    /**
     * Get source mapping id
     *
     * @return int
     */
    public function getMappingId()
    {
        return $this->getData(self::DATA_KEY_SOURCE_CATEGORY_FEED_MAPPING_ID);
    }
}
