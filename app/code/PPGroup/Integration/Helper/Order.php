<?php
namespace PPGroup\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use PPGroup\Integration\Logger\SaleorderExportLog;

class Order extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    protected $resource;
    protected $connection;
    /**
     * @var SaleorderExportLog
     */
    protected $logger;

    /**
     * Order constructor.
     * @param Context $context
     * @param SaleorderExportLog $logger
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        SaleorderExportLog $logger,
        ResourceConnection $resource
    ) {
        $this->logger = $logger;
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
        parent::__construct($context);
    }

    /**
     * @param $orderId
     * @param $state
     * @param $status
     */
    public function updateOrderStatus(
        $orderId,
        $state,
        $status
    ) {
        $orderTable = $this->connection->getTableName('sales_order');
        $orderGridTable = $this->connection->getTableName('sales_order_grid');
        try {
            $this->connection->update(
                $orderTable,
                [
                    'state' => $state,
                    'status' => $status
                ],
                'entity_id=' . $orderId
            );
            $this->connection->update(
                $orderGridTable,
                [
                    'status' => $status
                ],
                'entity_id=' . $orderId
            );
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
    }
}
