<?php

namespace PPGroup\Integration\Logger;

use Monolog\Logger;

class InventoryLogHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/inventory.log';
}