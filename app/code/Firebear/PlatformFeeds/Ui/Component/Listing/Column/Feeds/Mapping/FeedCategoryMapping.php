<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Ui\Component\Listing\Column\Feeds\Mapping;

use Magento\Framework\Data\OptionSourceInterface;
use Firebear\PlatformFeeds\Model\ResourceModel\Mapping\CollectionFactory;

class FeedCategoryMapping implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $mappingList;

    /**
     * FeedCategoryMapping constructor.
     * @param CollectionFactory $mappingList
     */
    public function __construct(
        CollectionFactory $mappingList
    ) {
        $this->mappingList = $mappingList;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        try {
            $options = $this->getOptions();
        } catch (\Exception $exception) {
            $options = [];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'value' => false,
            'label' => 'Select Feed Category Mapping'
        ];
        $items = $this->mappingList->create()->getItems();
        foreach ($items as $item) {
            $data = $item->getData();
            $options[] = [
                'value' => $data['id'],
                'label' => $data['title']
            ];
        }

        return $options;
    }
}
