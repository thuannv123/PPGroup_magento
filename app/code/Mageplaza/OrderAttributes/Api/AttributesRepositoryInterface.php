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

namespace Mageplaza\OrderAttributes\Api;

/**
 * Interface AttributesRepositoryInterface
 * @package Mageplaza\OrderAttributes\Api
 */
interface AttributesRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria The search criteria.
     *
     * @return \Mageplaza\OrderAttributes\Api\Data\AttributesSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @param string $cartId
     * @return \Mageplaza\OrderAttributes\Api\Data\FileResultInterface
     */
    public function upload($cartId);

    /**
     * @param string $cartId
     * @return \Mageplaza\OrderAttributes\Api\Data\FileResultInterface
     */
    public function guestUpload($cartId);
}
