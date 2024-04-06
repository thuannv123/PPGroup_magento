<?php
/**
 * InstallSchema
 *
 * @copyright Copyright © 2021 Firebear Studio. All rights reserved.
 * @author    Firebear Studio <fbeardev@gmail.com>
 */
declare(strict_types=1);

namespace Firebear\PlatformFeeds\Setup;

use Firebear\PlatformFeeds\Setup\Operations\CreateFeedCategoryMappingTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateFeedCategoryMappingTable
     */
    protected $createFeedCategoryMappingTable;

    /**
     * InstallSchema constructor.
     * @param CreateFeedCategoryMappingTable $createFeedCategoryMappingTable
     */
    public function __construct(
        CreateFeedCategoryMappingTable $createFeedCategoryMappingTable
    ) {
        $this->createFeedCategoryMappingTable = $createFeedCategoryMappingTable;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createFeedCategoryMappingTable->execute($setup);
        $setup->endSetup();
    }
}
