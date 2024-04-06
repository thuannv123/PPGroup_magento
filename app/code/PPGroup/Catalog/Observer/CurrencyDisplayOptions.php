<?php
namespace PPGroup\Catalog\Observer;

class CurrencyDisplayOptions implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $currencyOptions = $observer->getEvent()->getCurrencyOptions();
        $currencyOptions->setData('format', '#,##0.00 ¤');
        return $this;
    }
}