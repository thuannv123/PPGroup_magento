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

namespace Mageplaza\OrderAttributes\Api\Data;

/**
 * Interface StepInterface
 * @package Mageplaza\OrderAttributes\Api\Data
 */
interface StepInterface
{
    const         STEP_ID               = 'step_id';
    const         CODE                  = 'code';
    const         NAME                  = 'name';
    const         STATUS                = 'status';
    const         ICON_TYPE             = 'icon_type';
    const         ICON_TYPE_CUSTOM      = 'icon_type_custom';
    const         ICON_TYPE_CLASS       = 'icon_type_class';
    const         CONDITIONS_SERIALIZED = 'conditions_serialized';
    const         POSITION              = 'position';
    const         STORE_ID              = 'store_id';
    const         CUSTOMER_GROUP        = 'customer_group';
    const         SORT_ORDER            = 'sort_order';

    /**
     * @return int
     */
    public function getStepId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStepId($value);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCode($value);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setName($value);

    /**
     * @return int
     */
    public function getIconType();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIconType($value);

    /**
     * @return string
     */
    public function getIconCustom();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIconCustom($value);

    /**
     * @return string
     */
    public function getIconClass();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIconClass($value);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPosition($value);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return string
     */
    public function getCustomerGroup();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCustomerGroup($value);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSortOrder($value);

}
