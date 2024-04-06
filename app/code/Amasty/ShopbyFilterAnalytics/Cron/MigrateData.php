<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Cron;

use Amasty\ShopbyFilterAnalytics\Model\MigrateData as MigrateDataModel;

class MigrateData
{
    /**
     * @var MigrateDataModel
     */
    private $migrateData;

    public function __construct(MigrateDataModel $migrateData)
    {
        $this->migrateData = $migrateData;
    }

    public function execute(): void
    {
        $this->migrateData->execute();
    }
}
