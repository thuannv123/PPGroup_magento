<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Amasty\Shopby\Model\Layer\Filter\Category as CategoryFilter;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class Category implements FilterToSkipInterface
{
    public function execute(AbstractFilter $filter): bool
    {
        return $filter instanceof CategoryFilter;
    }
}
