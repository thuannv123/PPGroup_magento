<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;

class ProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ReindexRuleProductPrice
     */
    protected $reindexRuleProductPrice;

    /**
     * Object intialization
     *
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param ResourceConnection $resourceConnection
     * @param ReindexRuleProductPrice $reindexRuleProductPrice
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        ResourceConnection $resourceConnection,
        ReindexRuleProductPrice $reindexRuleProductPrice
    )
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->resourceConnection = $resourceConnection;
        $this->reindexRuleProductPrice = $reindexRuleProductPrice;
    }

    /**
     * Product save after observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $productId = $observer->getProduct()->getId();
            if (!empty($productId)) {
                $connection = $this->resourceConnection->getConnection();

                $connection->truncateTable('catalogrule_product_price');

                $categoryIndexer = $this->indexerRegistry->get('catalog_product_category');
                $categoryIndexer->reindexList([$productId]);

                $ruleIndexer = $this->indexerRegistry->get('catalogrule_rule');

                $ruleIndexer->reindexAll();

                $this->reindexRuleProductPrice->execute(1000, null, false);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
