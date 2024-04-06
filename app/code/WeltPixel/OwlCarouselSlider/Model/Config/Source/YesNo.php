<?php

namespace WeltPixel\OwlCarouselSlider\Model\Config\Source;

class YesNo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Yes'),
                'value' => '1',
            ],
            [
                'label' => __('No'),
                'value' => '0',
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            1 => __('Yes'),
            0 => __('No'),
        ];
    }
}
