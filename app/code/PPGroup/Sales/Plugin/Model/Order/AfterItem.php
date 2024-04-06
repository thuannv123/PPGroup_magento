<?php

namespace PPGroup\Sales\Plugin\Model\Order;


class AfterItem {
    
    public function afterRegisterCancellation(\Magento\Sales\Model\Order $subject, $result, $comment='', $graceful=true){
        $this->debug_qty('------------- After return quantity ----------------');
        $data = $result;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $this->debug_qty('Order Id: '.$result->getOrderId());
        $this->debug_qty('Increment Id: '.$result->getIncrementId());
        $this->debug_qty('Status qty to cancel: '. $result->getQtyToCancel());
        
        foreach ($result->getItems() as $key => $item) {
            if($item->getProductType() == 'simple'){
                $this->debug_qty('Product Id: '.$item->getProductId());
                $qty = $StockState->getStockQty($item->getProduct()->getId(), $item->getStore()->getWebsiteId());
                $this->debug_qty('After Quantity: '. $qty);
                $this->debug_qty('Status cancel qty: '. $item->getQtyCanceled());
                $this->debug_qty('Status refund qty: '. $item->getQtyReturned());
            }
        }

        $this->debug_qty('Status Order: '. $data->getStatus());
        $this->debug_qty('State Order: '. $data->getState());
        return [$result,$comment, $graceful];
    }

    public function debug_qty($data) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/quantity_cancel.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($data);
    }
}