<?php

namespace PPGroup\Catalog\Rule\ProductPrice;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;

class Reindex
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
    private $reindexRuleProductPrice;

    /**
     * Reindex constructor.
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param ResourceConnection $resourceConnection
     * @param ReindexRuleProductPrice $reindexRuleProductPrice
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        ResourceConnection $resourceConnection,
        ReindexRuleProductPrice $reindexRuleProductPrice
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->resourceConnection = $resourceConnection;
        $this->reindexRuleProductPrice = $reindexRuleProductPrice;
    }

    /**
     * @throws \Exception
     */
    public function reindex()
    {
        $connection = $this->resourceConnection->getConnection();

        $tableRuleProduct = $connection->getTableName('catalogrule_product');
        $tableRuleProductPrice = $connection->getTableName('catalogrule_product_price');

        $firstQuery = "SELECT product_id FROM `" . $tableRuleProductPrice . "`";

        $tableProductPriceConnection = $connection->fetchAll($firstQuery);

        if (empty($tableProductPriceConnection)) {
            $this->reindexRuleProductPrice->execute(1000, null, false);
        } else {
            $secondQuery = "SELECT product_id FROM `" . $tableRuleProduct
                . "` WHERE `"
                . $tableRuleProduct . "`.product_id NOT IN (SELECT product_id FROM `"
                . $tableRuleProductPrice . "`)";

            $checkMissingReindexProductPriceDataConnection  = $connection->fetchAll($secondQuery);

            if (!empty($checkMissingReindexProductPriceDataConnection)) {
                $connection->truncateTable('catalogrule_product_price');

                $ruleIndexer = $this->indexerRegistry->get('catalogrule_rule');
                $ruleIndexer->reindexAll();

                if (empty($checkTableProductRulePriceQuery)) {
                    $this->reindexRuleProductPrice->execute(1000, null, false);
                }

            }
        }
    }
}
