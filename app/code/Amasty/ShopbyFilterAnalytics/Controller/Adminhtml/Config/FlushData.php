<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Controller\Adminhtml\Config;

use Amasty\ShopbyFilterAnalytics\Model\FlushAnalytics;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

class FlushData extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Amasty_ShopbyFilterAnalytics::config_flush';

    /**
     * @var FlushAnalytics
     */
    private $flushAnalytics;

    public function __construct(
        Context $context,
        FlushAnalytics $flushAnalytics
    ) {
        parent::__construct($context);
        $this->flushAnalytics = $flushAnalytics;
    }

    public function execute()
    {
        $this->flushAnalytics->execute();
    }
}
