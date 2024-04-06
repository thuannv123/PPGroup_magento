<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Catalog\Plugin\Rule\Update;

use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\Framework\App\ResourceConnection;

class CatalogRuleUpdateAfterPlugin
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $ruleFactory;

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
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\CatalogRule\Model\RuleFactory $ruleFactory
     * @param ReindexRuleProductPrice $reindexRuleProductPrice
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
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
    public function afterExecute(\Magento\CatalogRuleStaging\Controller\Adminhtml\Update\Save $subject, $result)
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

        return $result;
    }
}
