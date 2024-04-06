<?php

namespace PPGroup\Sales\Plugin;

class OrderManagementPlugin
{
    protected $orderRepository;

    protected $stockState;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    ) {
        $this->orderRepository = $orderRepository;
        $this->stockState = $stockState;
    }

    public function beforeCancel(
        \Magento\Sales\Api\OrderManagementInterface $subject,
        $orderId
    ) {
        $this->debug_qty('------------- Before return quantity ----------------');
        $order = $this->orderRepository->get($orderId);
        $this->debug_qty('Order Id: '.$orderId);
        $this->debug_qty('Increment Id: '.$order->getIncrementId());

        $date = new \DateTime();
        $date = $date->format('d/m/y H:i:s A');      
        $this->debug_qty('Timestamp'. $date);
        
        foreach ($order->getAllItems() as $key => $item) {
            if($item->getProductType() == 'simple'){
                $this->debug_qty('Product Id: '.$item->getProductId());
                $qty = $this->stockState->getStockQty($item->getProduct()->getId(), $item->getStore()->getWebsiteId());
                $this->debug_qty('Before Quantity: '. $qty);
                $this->debug_qty('Status cancel qty: '. $item->getQtyCanceled());
                $this->debug_qty('Status refund qty: '. $item->getQtyReturned());
                $this->debug_qty('Status qty to cancel: '. $item->getQtyToCancel());
            }
        }

        $this->debug_qty('Status Order: '. $order->getStatus());
        $this->debug_qty('State Order: '. $order->getState());
        return [$orderId];
    }

    public function afterCancel(
        \Magento\Sales\Api\OrderManagementInterface $subject,
        $result,
        $orderId
    ) {
        $this->debug_qty('------------- After return quantity ----------------');
        $order = $this->orderRepository->get($orderId);
        $this->debug_qty('Order Id: ' . $orderId);
        $this->debug_qty('Increment Id: ' . $order->getIncrementId());

        $date = new \DateTime();
        $date = $date->format('d/m/y H:i:s A');      
        $this->debug_qty('Timestamp'. $date);

        foreach ($order->getAllItems() as $key => $item) {
            if ($item->getProductType() == 'simple') {
                $this->debug_qty('Product Id: ' . $item->getProductId());
                $qty = $this->stockState->getStockQty($item->getProduct()->getId(), $item->getStore()->getWebsiteId());
                $this->debug_qty('After Quantity: ' . $qty);
                $this->debug_qty('Status cancel qty: ' . $item->getQtyCanceled());
                $this->debug_qty('Status refund qty: ' . $item->getQtyReturned());
                $this->debug_qty('Status qty to cancel: ' . $item->getQtyToCancel());
            }
        }

        $this->debug_qty('Status Order: ' . $order->getStatus());
        $this->debug_qty('State Order: ' . $order->getState());
        return $result;
    }

    public function debug_qty($data)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/quantity_cancel_api.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($data);
    }
}
