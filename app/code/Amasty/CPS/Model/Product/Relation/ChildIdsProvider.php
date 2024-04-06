<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Model\Product\Relation;

use Amasty\CPS\Model\ResourceModel\Product\Relation\GetChildIds;

class ChildIdsProvider
{
    /**
     * @var int[]
     */
    private $childIdsRelation = [];

    /**
     * @var GetChildIds
     */
    private $getChildIds;

    public function __construct(GetChildIds $getChildIds)
    {
        $this->getChildIds = $getChildIds;
    }

    /**
     * @param int[] $entityIds
     * @return int[]
     */
    public function getChildIdsForList(array $entityIds): array
    {
        $childIds = [];
        $childIdsRelation = $this->getChildIds->execute($entityIds);
        foreach ($entityIds as $entityId) {
            $this->childIdsRelation[$entityId] = isset($childIdsRelation[$entityId])
                ? explode(',', (string) $childIdsRelation[$entityId])
                : [];
            $childIds[] = $this->childIdsRelation[$entityId];
        }

        return $childIds ? array_unique(array_merge(...$childIds)) : [];
    }

    public function getChildIds(int $entityId): array
    {
        if (!isset($this->childIdsRelation[$entityId])) {
            $childIdsRelation = $this->getChildIds->execute([$entityId]);
            $this->childIdsRelation[$entityId] = isset($childIdsRelation[$entityId])
                ? explode(',', $childIdsRelation[$entityId])
                : [];
        }

        return $this->childIdsRelation[$entityId];
    }
}
