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
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Model;

use Bss\Popup\Model\ResourceModel\Popup;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class HandleLayout
{
    /**
     * @var Popup
     */
    protected $popupResourceModel;

    /**
     * @param Popup $popupResourceModel
     */

    /**
     * Date Time
     *
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Construct.
     *
     * @param Popup $popupResourceModel
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Popup $popupResourceModel,
        TimezoneInterface $timezone
    ) {
        $this->popupResourceModel = $popupResourceModel;
        $this->timezone = $timezone;
    }

    /**
     * Get Id of popup
     *
     * @param array|string $handleList
     * @param Int $storeId
     * @param Int $customerGroupId
     * @return string
     */
    public function getPopupId($handleList, $storeId, $customerGroupId)
    {
        if (!is_array($handleList)) {
            $handleList = [$handleList];
        }
        $result = $this->popupResourceModel->getPopupByHandleList($handleList);
        $popupId = '';
        foreach ($result as $row) {
            $checkStore = $this->checkStore($row['storeview'], $storeId);
            if (!$checkStore) {
                continue;
            }
            $checkCustomerGroup = $this->checkCustomerGroup($row['customer_group'], $customerGroupId);
            if (!$checkCustomerGroup) {
                continue;
            }
            $isPopupExpired = $this->isPopupExpired($row);
            if ($isPopupExpired) {
                continue;
            }
            if ($row['page_group'] == 'all_pages' && $row['entities'] != '') {
                $checkExclude = $this->checkExclude($handleList, $row['entities']);
                if (!$checkExclude) {
                    continue;
                }
            }
            $popupId = $row['popup_id'];
            break;
        }
        return $popupId;
    }

    /**
     * Check store view
     *
     * @param string $stores
     * @param string $storeId
     * @return bool
     */
    public function checkStore($stores, $storeId)
    {
        $stores = explode(",", $stores);
        if (in_array(0, $stores) || in_array($storeId, $stores)) {
            return true;
        }
        return false;
    }

    /**
     * Check customer group
     *
     * @param string $customerGroup
     * @param int $customerGroupId
     * @return bool
     */
    public function checkCustomerGroup($customerGroup, $customerGroupId)
    {
        $customerGroup = explode(",", $customerGroup);
        if (in_array($customerGroupId, $customerGroup)) {
            return true;
        }
        return false;
    }

    /**
     * Check popup is expired
     *
     * @param array $row
     * @return bool
     */
    public function isPopupExpired($row)
    {
        $fromStr = isset($row['display_from']) ? $row['display_from'] : false;
        $toStr = isset($row['display_to']) ? $row['display_to'] : false;
        $currentTime = $this->timezone->scopeTimeStamp();
        // Popup was not expire in cases
        // Not set $from and $to
        // Set $from and not set $to and $current >= $from
        // Not set $from and set $to and $current <= $to
        // Set $from and set $to and $from <= $current <= $to
        if (!$fromStr && !$toStr) {
            return false;
        } elseif ($fromStr && !$toStr) {
            $fromTime = strtotime($fromStr);
            return $fromTime > $currentTime;
        } elseif (!$fromStr && $toStr) {
            $toTime = strtotime($toStr);
            return $toTime < $currentTime;
        } elseif ($fromStr && $toStr) {
            $fromTime = strtotime($fromStr);
            $toTime = strtotime($toStr);
            return $fromTime > $currentTime || $toTime < $currentTime;
        } else {
            return true;
        }
    }

    /**
     * Check exclude
     *
     * @param array $handleList
     * @param string $excludeHandle
     * @return bool
     */
    public function checkExclude($handleList, $excludeHandle)
    {
        $excludeHandle = explode(',', $excludeHandle);
        $currentPage = $handleList[0];
        if (count($handleList) > 1) {
            if (strpos($currentPage, 'product') !== false) {
                $currentPage = 'catalog_product_view';
            } else {
                $currentPage = 'catalog_category_view';
            }
        }
        if (in_array($currentPage, $excludeHandle)) {
            return false;
        }
        return true;
    }
}
