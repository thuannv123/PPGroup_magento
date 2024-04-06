<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model\Mapping;

use Magento\Framework\Data\OptionSourceInterface;

class FeedList implements OptionSourceInterface
{
    /**
     * Feed list
     *
     * @var array
     */
    public $feeds = [
        1 => 'Google',
        2 => 'Ebay',
        3 => 'Amazon',
//        4 => 'Awin'
        5 => 'Facebook',
        6 => 'Yandex'
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => __('Select...'),
                'value' => false
            ]
        ];
        foreach ($this->getFeeds() as $code => $label) {
            $options[$code] = [
                'label' => $label,
                'value' => $code
            ];
        }

        return $options;
    }

    /**
     * @return array|string[]
     */
    public function getFeeds()
    {
        return $this->feeds;
    }
}
