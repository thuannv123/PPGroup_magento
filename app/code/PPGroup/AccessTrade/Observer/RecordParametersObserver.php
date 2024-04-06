<?php

namespace PPGroup\AccessTrade\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PPGroup\AccessTrade\Model\Session;
use PPGroup\AccessTrade\Model\SessionFactory as AccessTradeSessionFactory;

class RecordParametersObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AccessTradeSessionFactory
     */
    protected $accessTradeSessionFactory;

    /**
     * @param RequestInterface $request
     * @param AccessTradeSessionFactory $accessTradeSessionFactory
     */
    public function __construct(
        RequestInterface $request,
        AccessTradeSessionFactory $accessTradeSessionFactory
    ) {
        $this->request = $request;
        $this->accessTradeSessionFactory = $accessTradeSessionFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getData('request');

        if(!$request && !$request instanceof \Magento\Framework\App\Request\Http) {
           $request = $this->request;
        }

        $rk = trim((string)$request->getParam('utm_campaign'), false);
        if (!empty($rk)) {
            $this->accessTradeSessionFactory->create()->setData(Session::ACCESS_TRADE_SESSION, $rk);
        }
    }
}
