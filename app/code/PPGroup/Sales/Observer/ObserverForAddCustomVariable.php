<?php

namespace PPGroup\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

class ObserverForAddCustomVariable implements ObserverInterface
{
    const DATE_TIME_FORMAT_MEDIUM = 2;

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /** @var DataObject $transport */
            $transport = $observer->getData('transportObject');
            $transport->setData('created_at_formatted', $transport->getOrder()->getCreatedAtFormatted(self::DATE_TIME_FORMAT_MEDIUM));
            return;
        } catch (\Exception $exception) {
            return;
        }
    }
}
