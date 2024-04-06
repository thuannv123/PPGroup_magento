<?php

namespace PPGroup\Checkout\Block;

use Magento\Framework\View\Element\Block\ArgumentInterface;


 class Quote implements ArgumentInterface
 {
     public function __construct(
         \Magento\Checkout\Model\Session $checkoutSession         
     ) {
         $this->_checkoutSession = $checkoutSession;
     }

     /**
      * Get quote object associated with cart. By default it is current customer session quote
      *
      * @return \Magento\Quote\Model\Quote
      */
     public function getQuoteData()
     {
         return $this->_checkoutSession->getQuote();
     }
 }
