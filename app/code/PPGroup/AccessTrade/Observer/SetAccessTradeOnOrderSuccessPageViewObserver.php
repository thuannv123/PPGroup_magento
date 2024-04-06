<?php

namespace PPGroup\AccessTrade\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use PPGroup\AccessTrade\Config\Config;
use PPGroup\AccessTrade\Api\TrackingManagementInterface;
use PPGroup\AccessTrade\Model\Session;
use PPGroup\AccessTrade\Model\SessionFactory as AccessTradeSessionFactory;
use PPGroup\AccessTrade\Model\System\Config\Source\Method;

class SetAccessTradeOnOrderSuccessPageViewObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TrackingManagementInterface
     */
    private $trackingManagement;

    /**
     * @var AccessTradeSessionFactory
     */
    protected $accessTradeSessionFactory;

    /**
     * @param Config $config
     * @param TrackingManagementInterface $trackingManagement
     * @param AccessTradeSessionFactory $accessTradeSessionFactory
     */
    public function __construct(
        Config $config,
        TrackingManagementInterface $trackingManagement,
        AccessTradeSessionFactory $accessTradeSessionFactory
    ) {
        $this->config = $config;
        $this->trackingManagement = $trackingManagement;
        $this->accessTradeSessionFactory = $accessTradeSessionFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');

        if (!$order instanceof Order || !$this->config->isEnabled()) {
            return;
        }
        /** @var Session $accessTradeSession */
        $accessTradeSession = $this->accessTradeSessionFactory->create();

        if (!$accessTradeSession->getData(Session::ACCESS_TRADE_SESSION)) {
            return;
        }

        if ($this->config->getIntegrationMethod() === Method::TRACKING_API) {
            $this->trackingManagement->trackFlatRateCommissionByOrder($order);
        }

        if ($this->config->isClearParametersAfterOrderSuccess()) {
            $accessTradeSession->unsetData(Session::ACCESS_TRADE_SESSION);
        }
    }
}
