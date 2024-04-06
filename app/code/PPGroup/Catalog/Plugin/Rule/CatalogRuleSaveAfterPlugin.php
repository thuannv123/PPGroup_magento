<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Catalog\Plugin\Rule;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\Framework\App\ResourceConnection;

class CatalogRuleSaveAfterPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ReindexRuleProductPrice
     */
    protected $reindexRuleProductPrice;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Object intialization
     *
     * @param IndexerRegistry $indexerRegistry
     * @param ReindexRuleProductPrice $reindexRuleProductPrice
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ReindexRuleProductPrice $reindexRuleProductPrice,
        ResourceConnection $resourceConnection
    )
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->reindexRuleProductPrice = $reindexRuleProductPrice;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Product save after plugin
     *
     * @param \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Save $subject
     * @param $result
     */
    public function afterExecute(\Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Save $subject, $result)
    {
        try {
            $salesRuleId = $subject->getRequest()->getParam('rule_id');

            if (!empty($salesRuleId)) {
                $connection = $this->resourceConnection->getConnection();

                $connection->truncateTable('catalogrule_product_price');

                $ruleIndexer = $this->indexerRegistry->get('catalogrule_rule');
                $ruleIndexer->reindexAll();

                $this->reindexRuleProductPrice->execute(1000, null, false);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
