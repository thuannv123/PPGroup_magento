<?php

namespace PPGroup\Sales\Plugin\Model\Order;


class Item {
    
    public function beforeCancel(\Magento\Sales\Model\Order\Item $subject) {
        $this->debug_qty('------------- Before return quantity ----------------');
        $data = $subject->getOrder();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $this->debug_qty('Order Id: '.$subject->getOrderId());
        $this->debug_qty('Increment Id: '.$data->getIncrementId());
        $this->debug_qty('Status qty to cancel: '. $subject->getQtyToCancel());
        
        foreach ($data->getItems() as $key => $item) {
            if($item->getProductType() == 'simple'){
                $this->debug_qty('Product Id: '.$item->getProductId());
                $qty = $StockState->getStockQty($item->getProduct()->getId(), $item->getStore()->getWebsiteId());
                $this->debug_qty('Before Quantity: '. $qty);
                $this->debug_qty('Status cancel qty: '. $item->getQtyCanceled());
                $this->debug_qty('Status refund qty: '. $item->getQtyReturned());
            }
        }

        $this->debug_qty('Status Order: '. $data->getStatus());
        $this->debug_qty('State Order: '. $data->getState());
        return [$subject];
    }

    public function debug_qty($data) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/quantity_cancel.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($data);
    }
}