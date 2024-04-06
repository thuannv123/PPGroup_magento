<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Model\Config\Source;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Option\ArrayInterface;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class TimeFormat
 * @package Mageplaza\OrderAttributes\Model\Config\Source
 */
class TimeFormat implements ArrayInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * DateFormat constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toOptionArray()
    {
        $date = new DateTime(date('Y-m-d H:i:s'));
        $date->setTimezone(new DateTimeZone($this->helperData->getTimezone()));
        $dateArray = [];
        foreach ($this->helperData->getTimeFormatConfig() as $key => $item) {
            $dateArray[] = [
                'value' => $item,
                'label' => $item . ' (' . $date->format($key) . ')'
            ];
        }

        return $dateArray;
    }
}
