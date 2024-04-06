<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

interface SortInterface
{
    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection);

    /**
     * @return string
     */
    public function getLabel();
}
