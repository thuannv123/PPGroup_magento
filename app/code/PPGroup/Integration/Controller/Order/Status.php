<?php

namespace PPGroup\Integration\Controller\Order;

class Status extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context

    )
    {
        return parent::__construct($context);
    }

    public function execute()
    {
        $cronjob = $this->_objectManager->create('\PPGroup\Integration\Cron\OrderStatus');
        $cronjob->execute();
        exit();
    }
}