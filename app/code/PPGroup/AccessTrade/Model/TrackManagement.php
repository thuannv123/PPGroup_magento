<?php

namespace PPGroup\AccessTrade\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Api\Data\OrderInterface;
use PPGroup\AccessTrade\Api\Data;
use PPGroup\AccessTrade\Api\Data\TrackRequestInterface as TrackRequestData;
use PPGroup\AccessTrade\Api\Data\TrackRequestInterfaceFactory as TrackRequestDataFactory;
use PPGroup\AccessTrade\Api\Data\TrackResultInterface;
use PPGroup\AccessTrade\Api\Data\TrackResultInterfaceFactory as TrackResultDataFactory;
use PPGroup\AccessTrade\Api\TrackingManagementInterface;
use PPGroup\AccessTrade\Config\Config;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PPGroup\AccessTrade\Model\SessionFactory as AccessTradeSessionFactory;

class TrackManagement implements TrackingManagementInterface
{
    /**
     * @var TrackRequestDataFactory
     */
    protected $trackRequestDataFactory;

    /**
     * @var TrackResultDataFactory
     */
    protected $trackResultDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var AccessTradeSessionFactory
     */
    protected $accessTradeSessionFactory;

    /**
     * Constructor
     *
     * @param TrackRequestDataFactory $trackRequestDataFactory
     * @param TrackResultDataFactory $trackResultDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Client $client
     * @param Config $config
     * @param TimezoneInterface $localeDate
     * @param AccessTradeSessionFactory $accessTradeSessionFactory
     */
    public function __construct(
        TrackRequestDataFactory $trackRequestDataFactory,
        TrackResultDataFactory $trackResultDataFactory,
        DataObjectHelper $dataObjectHelper,
        Client $client,
        Config $config,
        TimezoneInterface $localeDate,
        AccessTradeSessionFactory $accessTradeSessionFactory
    ) {
        $this->trackRequestDataFactory = $trackRequestDataFactory;
        $this->trackResultDataFactory = $trackResultDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->client = $client;
        $this->config = $config;
        $this->localeDate = $localeDate;
        $this->accessTradeSessionFactory = $accessTradeSessionFactory;
    }

    /**
     * @inheritDoc
     */
    public function trackFlatCommission(
        Data\TrackRequestInterface $trackRequest
    ): TrackResultInterface {

        if ($this->config->isMergeConfigWithAPI()) {
            if ($campaignId = $this->config->getCampaignId()) {
                $trackRequest->setMcn($campaignId);
            }

            if ($resultId = $this->config->getResultId()) {
                $trackRequest->setResultId($resultId);
            }
        }

        if ($rk = $this->accessTradeSessionFactory->create()->getData(Session::ACCESS_TRADE_SESSION)) {
            $trackRequest->setRk($rk);
        }

        $requestParameters = array_merge($trackRequest->__toArray(), ['method_type' => 'GET']);

        $dataArray = $this->client->postRequest($requestParameters);

        $trackResultDataObj = $this->trackResultDataFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $trackResultDataObj,
            $dataArray,
            TrackResultInterface::class
        );
        return $trackResultDataObj;
    }

    /**
     * @inheritDoc
     */
    public function trackFlatRateCommission(
        Data\TrackRequestInterface $trackRequest
    ): TrackResultInterface {
        return $this->trackFlatCommission($trackRequest);
    }

    /**
     * @inheritDoc
     */
    public function trackFlatCommissionByOrder(
        OrderInterface $order
    ): TrackResultInterface {
        /** @var TrackRequestData $trackRequest */
        $trackRequest = $this->trackRequestDataFactory->create();
        $trackRequest->setIdentifier($order->getIncrementId())
            ->setSalesDate($this->formatDate($order->getCreatedAt()))
            ->setCurrency($order->getOrderCurrencyCode());

        return $this->trackFlatCommission($trackRequest);
    }

    /**
     * @inheritDoc
     */
    public function trackFlatRateCommissionByOrder(
        OrderInterface $order
    ): TrackResultInterface {
        /** @var TrackRequestData $trackRequest */
        $trackRequest = $this->trackRequestDataFactory->create();

        $salesAmount = $order->getSubtotal();
        $discountAmount = $order->getDiscountAmount() ? abs($order->getDiscountAmount()) : 0.00;

        if ($this->config->getTaxMode() == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX) {
            $salesAmount = round($salesAmount / (107 / 100), 2);
            $discountAmount = round($discountAmount / (107 / 100), 2);
        }

        $trackRequest->setIdentifier($order->getIncrementId())
            ->setSalesDate($this->formatDate($order->getCreatedAt()))
            ->setValue($salesAmount)
            ->setTransactionDiscount($discountAmount)
            ->setCurrency($order->getOrderCurrencyCode());

        return $this->trackFlatCommission($trackRequest);
    }

    /**
     * @param $date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    protected function formatDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        if ($this->config->isUsingUnixTime() && is_string($date)) {
            return $date;
        }
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date ?? 'now');
        return $this->localeDate->date($date)->format($format);
    }

    /**
     * @inheritDoc
     */
    public function recordRk(string $rk): string
    {
        $session = $this->accessTradeSessionFactory->create();
        $session->setData(Session::ACCESS_TRADE_SESSION, $rk);

        return $session->getData(Session::ACCESS_TRADE_SESSION);
    }
}
