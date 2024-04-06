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

namespace Mageplaza\OrderAttributes\Model\ResourceModel\Step;

use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;
use Mageplaza\OrderAttributes\Model\Step as Model;
use Mageplaza\OrderAttributes\Model\ResourceModel\Step as ResourceModel;

/**
 * Class Collection
 * @package Mageplaza\OrderAttributes\Model\ResourceModel\Step
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_order_checkout_step_collection';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
