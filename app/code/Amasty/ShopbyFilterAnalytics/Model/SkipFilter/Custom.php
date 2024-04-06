<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Amasty\Shopby\Model\Layer\Filter\IsNew;
use Amasty\Shopby\Model\Layer\Filter\OnSale;
use Amasty\Shopby\Model\Layer\Filter\Rating;
use Amasty\Shopby\Model\Layer\Filter\Stock;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class Custom implements FilterToSkipInterface
{
    public function execute(AbstractFilter $filter): bool
    {
        return $filter instanceof Rating
            || $filter instanceof IsNew
            || $filter instanceof OnSale
            || $filter instanceof Stock;
    }
}
