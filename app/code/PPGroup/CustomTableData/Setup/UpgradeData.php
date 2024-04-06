<?php

namespace PPGroup\CustomTableData\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package PPGroup\CustomTableData\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context     *
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $tableMageplazaBlogCategory = $setup->getTable('mageplaza_blog_category');
            $where = 'url_key';
            $eq = 'root';
            $setup->getConnection()->update($tableMageplazaBlogCategory, ['category_id' => '1'], $where . '=' . "'" . $eq . "'");
        }
        $setup->endSetup();
    }
}
