<?php
/**
 * CreateFeedCategoryMappingTable
 *
 * @copyright Copyright © 2021 Firebear Studio. All rights reserved.
 * @author    Firebear Studio <fbeardev@gmail.com>
 */
declare(strict_types=1);

namespace Firebear\PlatformFeeds\Setup\Operations;

use Firebear\PlatformFeeds\Api\Data\MappingInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class CreateFeedCategoryMappingTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $setup->getTable(MappingInterface::TABLE_NAME);
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $installer->getConnection()->createTable(
                $this->makeTable($installer, $tableName)
            );
        }
        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     * @return Table
     * @throws Zend_Db_Exception
     */
    private function makeTable(SchemaSetupInterface $setup, string $tableName)
    {
        return $setup->getConnection()->newTable($tableName)
            ->addColumn(
                MappingInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'ID'
            )
            ->addColumn(
                MappingInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name Category Mapping'
            )
            ->addColumn(
                MappingInterface::TYPE_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Feed Entity Id'
            )
            ->addColumn(
                MappingInterface::CREDENTIALS_DATA,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Credentials Data (json)'
            )
            ->addColumn(
                MappingInterface::MAPPING_DATA,
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Mapping Data (json)'
            )
            ->setComment('Feed Category Mapping');
    }
}
