<?php

namespace PPGroup\AccessTrade\Api\Data;

/**
 * @api
 */
interface TrackResultInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const STATUS = 'status';

    /**
     * Get Status
     *
     * @return bool
     */
    public function getStatus();

    /**
     * @param bool $status
     * @return \PPGroup\AccessTrade\Api\Data\TrackResultInterface
     */
    public function setStatus($status);
}
