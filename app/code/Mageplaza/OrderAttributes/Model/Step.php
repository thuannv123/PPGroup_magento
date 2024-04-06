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

namespace Mageplaza\OrderAttributes\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory as SaleRuleCombineFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as SaleRuleProductCombineFactory;
use Mageplaza\OrderAttributes\Api\Data\StepInterface;
use Mageplaza\OrderAttributes\Model\ResourceModel\Step as ResourceModel;
use Mageplaza\OrderAttributes\Model\ResourceModel\Step\Collection;

/**
 * Class Step
 * @package Mageplaza\OrderAttributes\Model
 */
class Step extends AbstractModel implements StepInterface
{
    const  ICON_IMG_MEDIA_PATH = 'mageplaza/order_attributes/icon/';
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_order_checkout_step_model';

    /**
     * @var SaleRuleCombineFactory
     */
    protected $saleRuleCombineFactory;

    /**
     * @var SaleRuleProductCombineFactory
     */
    protected $saleRuleProductCombineFactory;

    /**
     * Step constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param ResourceModel|null $resource
     * @param Collection|null $resourceCollection
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     * @param SaleRuleCombineFactory $saleRuleCombineFactory
     * @param SaleRuleProductCombineFactory $saleRuleProductCombineFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ResourceModel $resource = null,
        Collection $resourceCollection = null,
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null,
        SaleRuleCombineFactory $saleRuleCombineFactory,
        SaleRuleProductCombineFactory $saleRuleProductCombineFactory,
        array $data = []
    ) {
        $this->saleRuleCombineFactory        = $saleRuleCombineFactory;
        $this->saleRuleProductCombineFactory = $saleRuleProductCombineFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine|\Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->saleRuleCombineFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection|Combine
     */
    public function getActionsInstance()
    {
        return $this->saleRuleProductCombineFactory->create();
    }

    /**
     * @param $code
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function loadByCode($code)
    {
        return $this->_getResource()->loadByCode($this, $code);
    }

    /**
     * @param $sortOrder
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function loadBySortOrder($sortOrder)
    {
        return $this->_getResource()->loadBySortOrder($this, $sortOrder);
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getCountAttribute()
    {
        return $this->_getResource()->getCountAttribute($this);
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param Address $address
     *
     * @return bool
     */
    public function isMatchCondition($address)
    {
        $this->afterLoad();
        if (!$this->validate($address)) {
            $this->setIsValidForAddress($address, false);

            return false;
        }
        $this->setIsValidForAddress($address, true);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getStepId()
    {
        return $this->getData(self::STEP_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStepId($value)
    {
        return $this->setData(self::STEP_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($value)
    {
        return $this->setData(self::CODE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getIconType()
    {
        return $this->getData(self::ICON_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setIconType($value)
    {
        return $this->setData(self::ICON_TYPE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getIconCustom()
    {
        return $this->getData(self::ICON_TYPE_CUSTOM);
    }

    /**
     * @inheritDoc
     */
    public function setIconCustom($value)
    {
        return $this->setData(self::ICON_TYPE_CUSTOM, $value);
    }

    /**
     * @inheritDoc
     */
    public function getIconClass()
    {
        return 'fa_oa ' . $this->getData(self::ICON_TYPE_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setIconClass($value)
    {
        return $this->setData(self::ICON_TYPE_CLASS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritDoc
     */
    public function setPosition($value)
    {
        return $this->setData(self::POSITION, $value);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($value)
    {
        return $this->setData(self::STORE_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroup()
    {
        return $this->getData(self::CUSTOMER_GROUP);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerGroup($value)
    {
        return $this->setData(self::CUSTOMER_GROUP, $value);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($value)
    {
        return $this->setData(self::SORT_ORDER, $value);
    }
}
