<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Model\Product\Relation;

use Amasty\CPS\Model\ResourceModel\Product\Relation\GetParentIds;

class ParentIdsProvider
{
    /**
     * @var int[]
     */
    private $parentIdsRelation = [];

    /**
     * @var GetParentIds
     */
    private $getParentIds;

    public function __construct(GetParentIds $getParentIds)
    {
        $this->getParentIds = $getParentIds;
    }

    /**
     * @param int[] $entityIds
     * @return int[]
     */
    public function getParentIdsForList(array $entityIds): array
    {
        $parentIds = [];
        $parentIdsRelation = $this->getParentIds->execute($entityIds);
        foreach ($entityIds as $entityId) {
            $this->parentIdsRelation[$entityId] = isset($parentIdsRelation[$entityId])
                ? explode(',', (string) $parentIdsRelation[$entityId])
                : [];
            $parentIds[] = $this->parentIdsRelation[$entityId];
        }

        return $parentIds ? array_unique(array_merge(...$parentIds)) : [];
    }
}
