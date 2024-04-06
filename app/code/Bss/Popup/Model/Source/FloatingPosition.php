<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Model\Source;

/**
 * Option Floating Position
 *
 */
class FloatingPosition implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Position values
     */
    const BOTTOM_LEFT = 1;
    const BOTTOM_CENTER = 2;
    const BOTTOM_RIGHT = 3;
    const MIDDLE_LEFT = 4;
    const MIDDLE_RIGHT = 5;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BOTTOM_LEFT,  'label' => __('Bottom Left')],
            ['value' => self::BOTTOM_CENTER,  'label' => __('Bottom Center')],
            ['value' => self::BOTTOM_RIGHT,  'label' => __('Bottom Right')],
            ['value' => self::MIDDLE_LEFT,  'label' => __('Middle Left')],
            ['value' => self::MIDDLE_RIGHT,  'label' => __('Middle Right')],
        ];
    }
}
