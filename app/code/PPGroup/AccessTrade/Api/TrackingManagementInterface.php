<?php

namespace PPGroup\AccessTrade\Api;

/**
 * @api
 */
interface TrackingManagementInterface
{
    /**
     * Track the flat commission data
     *
     * @param Data\TrackRequestInterface $trackRequest
     * @return Data\TrackResultInterface
     */
    public function trackFlatCommission(
        \PPGroup\AccessTrade\Api\Data\TrackRequestInterface $trackRequest
    ): \PPGroup\AccessTrade\Api\Data\TrackResultInterface;

    /**
     * Track the flat rate commission data
     *
     * @param Data\TrackRequestInterface $trackRequest
     * @return Data\TrackResultInterface
     */
    public function trackFlatRateCommission(
        \PPGroup\AccessTrade\Api\Data\TrackRequestInterface $trackRequest
    ): \PPGroup\AccessTrade\Api\Data\TrackResultInterface;

    /**
     * Track flat commission by order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \PPGroup\AccessTrade\Api\Data\TrackResultInterface
     */
    public function trackFlatCommissionByOrder(\Magento\Sales\Api\Data\OrderInterface $order): Data\TrackResultInterface;

    /**
     * Track flat rate commission by order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \PPGroup\AccessTrade\Api\Data\TrackResultInterface
     */
    public function trackFlatRateCommissionByOrder(\Magento\Sales\Api\Data\OrderInterface $order): Data\TrackResultInterface;


    /**
     * Record rk to session storage
     *
     * @param string $rk
     * @return string
     */
    public function recordRk(string $rk): string;
}
