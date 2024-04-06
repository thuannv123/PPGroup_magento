<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Ui\Component\Listing\Column\Feeds\Category;

use Magento\Framework\Data\OptionSourceInterface;
use Firebear\PlatformFeeds\Helper\Data;

class Feed implements OptionSourceInterface
{
    /**
     * @var Data
     */
    public $helper;

    /**
     * Feed constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $categories = $this->getCategories();
        if (!empty($categories)) {
            foreach ($categories as $categoryId => $categoryData) {
                $options[] = [
                    'value' => $categoryId,
                    'label' => $categoryData
                ];
            }
        }

        return $options;
    }

    /**
     * Get categories
     *
     * @return array|bool|float|int|string|null
     */
    protected function getCategories()
    {
        $categories = '';
        $idData = $GLOBALS['_SERVER']['REQUEST_URI'];
        $idData = explode('/',  $idData);
        foreach ($idData as $key => $value) {
            if ($value == 'id' && is_numeric($idData[$key + 1] ?? '')) {
                $id = $idData[$key + 1];
                $typeId = $this->helper->getTypeId($id);
                $identifier = "feed_categories_{$typeId}_{$id}";
                $categories = $this->helper->getCategoriesCache($identifier);
                break;
            }
        }

        return $categories;
    }
}
