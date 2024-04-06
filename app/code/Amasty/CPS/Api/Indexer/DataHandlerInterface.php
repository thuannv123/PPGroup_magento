<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Api\Indexer;

interface DataHandlerInterface
{
    /**
     * @return DataHandlerInterface
     */
    public function reindexAll();
}
