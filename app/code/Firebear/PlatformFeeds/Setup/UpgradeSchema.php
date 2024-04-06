<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Setup;

use Firebear\PlatformFeeds\Setup\Operations\CreateFeedCategoryMappingTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operations\CreateFeedCategoryMappingTable
     */
    protected $createFeedCategoryMappingTable;

    /**
     * UpgradeSchema constructor.
     * @param Operations\CreateFeedCategoryMappingTable $createFeedCategoryMappingTable
     */
    public function __construct(
        CreateFeedCategoryMappingTable $createFeedCategoryMappingTable
    ) {
        $this->createFeedCategoryMappingTable = $createFeedCategoryMappingTable;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addMappingTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function addMappingTable(SchemaSetupInterface $setup)
    {
        $this->createFeedCategoryMappingTable->execute($setup);
    }
}
