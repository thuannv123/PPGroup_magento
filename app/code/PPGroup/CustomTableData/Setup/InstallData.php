<?php

namespace PPGroup\CustomTableData\Setup;

use Exception;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\App\ResourceConnection;

class InstallData implements InstallDataInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    public function __construct(
         ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

     /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableSalesOrderGrid = $connection->getTableName('sales_order_grid');
        $tableSalesOrder = $connection->getTableName('sales_order');
        $query = "UPDATE `" . $tableSalesOrderGrid . "` LEFT JOIN `" . $tableSalesOrder . "` ON `" . $tableSalesOrderGrid . "`.entity_id = `" . $tableSalesOrder . "`.entity_id SET `" . $tableSalesOrderGrid . "`.status = `" . $tableSalesOrder . "`.status WHERE `" . $tableSalesOrderGrid . "`.status != `" . $tableSalesOrder . "`.status";
        $connection->query($query);
    }
}