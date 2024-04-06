<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyGraphQl\Model;

interface FilterBuilderInterface
{
    public function build(array &$filters, int $storeId): void;
}
