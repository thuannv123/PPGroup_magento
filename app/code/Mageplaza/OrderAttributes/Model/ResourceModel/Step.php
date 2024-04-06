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

namespace Mageplaza\OrderAttributes\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Step
 * @package Mageplaza\OrderAttributes\Model\ResourceModel
 */
class Step extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_order_checkout_step_resource_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mageplaza_order_checkout_step', 'step_id');
        $this->_useIsObjectNew = true;
    }

    /**
     * Load attribute data by attribute code
     *
     * @param \Mageplaza\OrderAttributes\Model\Step $object
     * @param string $code
     *
     * @return bool
     */
    public function loadByCode(\Mageplaza\OrderAttributes\Model\Step $object, $code)
    {
        $select = $this->_getLoadSelect('code', $code, $object);
        $data   = $this->getConnection()->fetchRow($select);

        if ($data) {
            $object->setData($data);

            return true;
        }

        return false;
    }

    /**
     * @param \Mageplaza\OrderAttributes\Model\Step $object
     * @param $sortOrder
     *
     * @return bool
     */
    public function loadBySortOrder(\Mageplaza\OrderAttributes\Model\Step $object, $sortOrder)
    {
        $select = $this->_getLoadSelect('sort_order', $sortOrder, $object);
        $data   = $this->getConnection()->fetchRow($select);

        if ($data) {
            $object->setData($data);

            return true;
        }

        return false;
    }

    /**
     * @param \Mageplaza\OrderAttributes\Model\Step $object
     *
     * @return int|void
     * @throws LocalizedException
     */
    public function getCountAttribute(\Mageplaza\OrderAttributes\Model\Step $object)
    {
        $attributesTable = $this->getConnection()->getTableName('mageplaza_order_attribute');
        $select         = $this->getConnection()->select()->joinInner(
            $attributesTable,
            "{$attributesTable}.position = {$this->getMainTable()}.code",
            "position"
        )->from($this->getMainTable())->where('step_id =?', $object->getStepId());
        $data           = $this->getConnection()->fetchAll($select);

        return count($data);
    }
}
