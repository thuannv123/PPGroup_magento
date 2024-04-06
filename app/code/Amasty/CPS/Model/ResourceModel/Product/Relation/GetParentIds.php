<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Model\ResourceModel\Product\Relation;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class GetParentIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(ResourceConnection $resourceConnection, MetadataPool $metadataPool)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param int[] $entityIds
     * @return array [['child_id' => 'parent_ids'], ...]
     */
    public function execute(array $entityIds): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            ['cpr' => $this->resourceConnection->getTableName('catalog_product_relation')],
            ['child_id' => 'cpr.child_id']
        )->join(
            ['e' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf(
                'e.%s = cpr.parent_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            ),
            ['parent_ids' => 'GROUP_CONCAT(e.entity_id SEPARATOR ",")']
        )->where(
            'child_id IN (?)',
            $entityIds
        )->group('child_id');

        return $connection->fetchPairs($select);
    }
}
