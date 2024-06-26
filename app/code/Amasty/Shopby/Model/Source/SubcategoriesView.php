<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

class SubcategoriesView implements \Magento\Framework\Option\ArrayInterface
{
    public const FOLDING = 1;
    public const FLY_OUT = 2;
    public const FLY_OUT_FOR_DESKTOP_ONLY = 3;

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::FOLDING,
                'label' => __('Folding')
            ],
            [
                'value' => self::FLY_OUT,
                'label' => __('Fly-out')
            ],
            [
                'value' => self::FLY_OUT_FOR_DESKTOP_ONLY,
                'label' => __('Fly-out for Desktop Only')
            ]
        ];
    }
}
