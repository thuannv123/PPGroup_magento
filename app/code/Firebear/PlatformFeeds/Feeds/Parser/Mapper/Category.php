<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Mapper;

use Firebear\PlatformFeeds\Model\Export\DataProvider\Registry;
use Firebear\PlatformFeeds\Helper\Data;

class Category extends AbstractVariableMapper
{
    /**
     * @var string
     */
    const KEY_SOURCE_CATEGORY_MAGENTO = 'source_category_magento';

    /**
     * @var string
     */
    const KEY_SOURCE_CATEGORY_FEED = 'source_category_feed';

    /**
     * @var string
     */
    const CATEGORY_DELIMITER = ',';

    /**
     * @var Data
     */
    public $helper;

    /**
     * Category constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function map($value)
    {
        $category = $this->getCategoryValue($value);
        if (empty($category)) {
            return $value;
        }

        $mappedCategory = $this->getMappedCategory($category);
        return $mappedCategory;
    }

    /**
     * Get map data
     *
     * @return array
     */
    protected function getMapData()
    {
        $id = Registry::getInstance()->getMappingId();

        return $this->helper->getMappingData($id);
    }

    /**
     * Get category value. There can be only one category per product item.
     * We will pick the first category of the list.
     *
     * @param string $categories
     * @return string|null
     */
    protected function getCategoryValue($categories)
    {
        if (!empty($categories)) {
            $list = explode(self::CATEGORY_DELIMITER, $categories);
            $count = count($list);
            if ($count) {
                // Take the most specific one. Usually it's the last one
                return $list[$count - 1];
            }
        }

        return null;
    }

    /**
     * Get mapped category
     *
     * @param string $category
     * @return string
     */
    protected function getMappedCategory($category)
    {
        $map = $this->getMapData();
        if (empty($map)) {
            return $category;
        }

        $key = array_search($category, array_column($map, self::KEY_SOURCE_CATEGORY_MAGENTO));
        if ($key === false || !isset($map[$key][self::KEY_SOURCE_CATEGORY_FEED])) {
            return $category;
        }

        return $map[$key][self::KEY_SOURCE_CATEGORY_FEED];
    }
}
