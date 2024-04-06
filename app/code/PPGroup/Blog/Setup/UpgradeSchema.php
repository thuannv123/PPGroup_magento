<?php
namespace PPGroup\Blog\Setup;

use \Magento\Framework\Setup\UpgradeSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface {
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('mageplaza_blog_category'),
                'labels',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Labels'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            if (!$setup->tableExists('mageplaza_blog_post_stores')) {
                $table = $setup->getConnection()
                    ->newTable($setup->getTable('mageplaza_blog_post_stores'))
                    ->addColumn(
                        'post_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Post ID'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        '1G',
                        ['nullable => false'],
                        'Post Name'
                    )
                    ->addColumn(
                        'short_description',
                        Table::TYPE_TEXT,
                        '1G',
                        ['nullable => false'],
                        'Post Short Description'
                    )
                    ->addColumn(
                        'post_content',
                        Table::TYPE_TEXT,
                        '1G',
                        ['nullable => false'],
                        'Post Content'
                    )
                    ->addColumn(
                        'store_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Store ID'
                    )
                    ->addForeignKey(
                        $setup->getFkName(
                            'mageplaza_blog_post_store',
                            'post_id',
                            'mageplaza_blog_post',
                            'post_id'
                        ),
                        'post_id',
                        $setup->getTable('mageplaza_blog_post'),
                        'post_id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('Post Table');

                $setup->getConnection()->createTable($table);
            }
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($setup->tableExists('mageplaza_blog_post_stores')) {
                $table = $setup->getConnection();

                $sql = "ALTER TABLE `mageplaza_blog_post_stores` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT COMMENT 'ID' FIRST, ADD PRIMARY KEY (`id`);";
                $table->query($sql);
            }
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            if ($setup->tableExists('mageplaza_blog_category')) {
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $setup->getTable('mageplaza_blog_category'),
                    'category_layout',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Layout'
                    ]
                );
                $connection->addColumn(
                    $setup->getTable('mageplaza_blog_category'),
                    'category_sidebar',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Sidebar'
                    ]
                );
            }
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            if ($setup->tableExists('mageplaza_blog_category')) {
                $connection = $setup->getConnection();
                $connection->dropColumn(
                    $setup->getTable('mageplaza_blog_category'),
                    'category_sidebar'
                );
            }
        }
    }
}
