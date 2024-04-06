<?php
namespace PPGroup\Ccpp\Setup;

use \Magento\Framework\Setup\UpgradeSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface {
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            if ($setup->tableExists('sales_order')) {
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $setup->getTable('sales_order'),
                    'payment_token',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Layout'
                    ]
                );
            }
        }
    }
}
