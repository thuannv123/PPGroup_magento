<?php
namespace PPGroup\AccessTrade\Model\Api\Data;

use Magento\Framework\Api\AbstractSimpleObject;

class TrackResult extends AbstractSimpleObject implements \PPGroup\AccessTrade\Api\Data\TrackResultInterface {

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
