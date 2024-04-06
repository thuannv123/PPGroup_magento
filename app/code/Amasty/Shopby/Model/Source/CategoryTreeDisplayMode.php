<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

class CategoryTreeDisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    public const SHOW_LABELS_ONLY = 0;
    public const SHOW_IMAGES_ONLY = 1;
    public const SHOW_LABELS_IMAGES = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SHOW_LABELS_ONLY,
                'label' => __('Show Labels Only')
            ],
            [
                'value' => self::SHOW_IMAGES_ONLY,
                'label' => __('Show Images Only')
            ],
            [
                'value' => self::SHOW_LABELS_IMAGES,
                'label' => __('Show Labels And Images')
            ],
        ];
    }
}
