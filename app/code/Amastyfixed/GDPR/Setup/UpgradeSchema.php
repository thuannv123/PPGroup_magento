<?php
namespace Amastyfixed\GDPR\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.0', '<')) {
           $installer->getConnection()->addColumn(
                $installer->getTable('newsletter_subscriber'),
                'consent_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Consent Code'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('newsletter_subscriber'),
                'action',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Consent Action'
                ]
            );
			
			
			$installer->getConnection()->dropForeignKey(
            $installer->getTable('amasty_gdpr_consent_log'),
            $installer->getFkName(
                'amasty_gdpr_consent_log',
                'customer_id',
                'customer_entity',
                'entity_id'
            )
			);


            if ($installer->getConnection()->tableColumnExists('amasty_gdpr_consent_log', 'customer_id')){
                $definition = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'Customer Id'
                ];
                $installer->getConnection()->modifyColumn(
                    $setup->getTable('amasty_gdpr_consent_log'),
                    'customer_id',
                    $definition
                );
            }

            $installer->getConnection()->addColumn(
                $installer->getTable('amasty_gdpr_consent_log'),
                'customer_email',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Customer Email'
                ]
            );
        }
        $installer->endSetup();
    }
}