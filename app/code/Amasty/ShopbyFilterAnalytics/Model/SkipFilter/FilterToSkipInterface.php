<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

interface FilterToSkipInterface
{
    /**
     * Determines whether to collect statistics for the filter
     *
     * @param AbstractFilter $filter
     * @return bool
     */
    public function execute(AbstractFilter $filter): bool;
}
