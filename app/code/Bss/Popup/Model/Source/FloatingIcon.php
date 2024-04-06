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
 * Option Floating Icon
 *
 */
class FloatingIcon implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Floating Icon values
     */
    const CONTACT_FORM = 1;
    const HOT_DEAL = 2;
    const NEWSLETTER = 3;
    const NOTIFICATION_BELL = 4;
    const PROMOTIONAL = 5;
    const PROMOTIONS = 6;
    const SOCIAL_MEDIA = 7;
    const WARNING = 8;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CONTACT_FORM,  'label' => __('CONTACT FORM')],
            ['value' => self::HOT_DEAL,  'label' => __('HOT DEAL')],
            ['value' => self::NEWSLETTER,  'label' => __('NEWSLETTER')],
            ['value' => self::NOTIFICATION_BELL,  'label' => __('NOTIFICATION BELL')],
            ['value' => self::PROMOTIONAL,  'label' => __('PROMOTIONAL')],
            ['value' => self::PROMOTIONS,  'label' => __('PROMOTIONS')],
            ['value' => self::SOCIAL_MEDIA,  'label' => __('SOCIAL MEDIA')],
            ['value' => self::WARNING,  'label' => __('WARNING')],
        ];
    }
}
