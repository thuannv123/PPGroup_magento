<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class IsSkipFilter
{
    /**
     * @var FilterToSkipInterface[]
     */
    private $pool;

    public function __construct(
        array $pool = []
    ) {
        $this->pool = $pool;
    }

    public function execute(AbstractFilter $filter): bool
    {
        foreach ($this->pool as $validator) {
            if ($validator->execute($filter)) {
                return true;
            }
        }

        return false;
    }
}
