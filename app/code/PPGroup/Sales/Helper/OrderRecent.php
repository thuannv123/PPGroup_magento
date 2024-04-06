<?php

namespace PPGroup\Sales\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class OrderRecent extends AbstractHelper
{
    /**
     * Limit of orders
     */
    const ORDER_LIMIT = 5;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * OrderRecent constructor.
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig
    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getRecentOrders()
    {
        return $this->_orderCollectionFactory->create()->addAttributeToSelect(
            '*'
        )->addAttributeToFilter(
            'customer_id',
            $this->_customerSession->getCustomerId()
        )->addAttributeToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->addAttributeToSort(
            'created_at',
            'desc'
        )->setPageSize(
            self::ORDER_LIMIT
        )->load();
    }
}
