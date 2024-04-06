<?php

namespace PPGroup\Integration\Logger;

use Monolog\Logger;

class SaleorderStatusLogHandler extends \Magento\Framework\Logger\Handler\Base
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
    protected $fileName = '/var/log/so_status.log';
}