<?php
namespace PPGroup\Checkout\Block\OnePage;

/**
 * One page checkout success page
 *
 * @api
 * @since 100.0.2
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * Get Order
     *
     * @return Magento\Sales\Model\Order;
     */
    public function GetOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order;
    }

    /**
     * @param $logData
     * @return mixed
     * @throws \Zend_Log_Exception
     */
    public function log($logData) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/2c2p_data_to_GA_report.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        return $logger->info($logData);
    }
}
