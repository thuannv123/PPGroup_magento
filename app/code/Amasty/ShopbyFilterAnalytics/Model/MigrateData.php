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

class MigrateData
{
    public const DATE_CONDITION = 'DATE(created_at) < CURRENT_DATE()';

    /**
     * @var TmpAnalytics
     */
    private $tmpStatistic;

    /**
     * @var Aggregation
     */
    private $aggregation;

    public function __construct(
        Aggregation $aggregation,
        TmpAnalytics $tmpStatistic
    ) {
        $this->tmpStatistic = $tmpStatistic;
        $this->aggregation = $aggregation;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void
    {
        $select = $this->tmpStatistic->getAggregationSelect();
        $select->where(self::DATE_CONDITION);

        $this->aggregation->insertFromSelect($select);
        $this->tmpStatistic->deleteOldData(self::DATE_CONDITION);
    }
}
