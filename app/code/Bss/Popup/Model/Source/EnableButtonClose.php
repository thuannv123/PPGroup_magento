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
 * Option YesNo
 *
 */
class EnableButtonClose implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "1",  'label' => __('Yes')],
            ['value' => "0",  'label' => __('No')]
        ];
    }
}
