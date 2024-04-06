<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model;

use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Aggregation;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\TmpAnalytics;

class FlushAnalytics
{
    /**
     * @var TmpAnalytics
     */
    private $tmpAnalytics;

    /**
     * @var Aggregation
     */
    private $aggregation;

    public function __construct(TmpAnalytics $tmpAnalytics, Aggregation $aggregation)
    {
        $this->tmpAnalytics = $tmpAnalytics;
        $this->aggregation = $aggregation;
    }

    /**
     * Delete all collected statistics.
     */
    public function execute(): void
    {
        $this->tmpAnalytics->flushTable();
        $this->aggregation->flushTable();
    }
}
