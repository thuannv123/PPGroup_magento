<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mage24Fix for Magento 2 (System)
 */

namespace Amasty\Mage24Fix\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FixStoreSwitcher implements ObserverInterface
{
    private $handles = [
        'amshopby_option_option_settings'
    ];

    public function __construct(
        array $handles = []
    ) {
        $this->handles = array_merge($this->handles, $handles);
    }

    public function execute(Observer $observer): void
    {
        $handle = $observer->getEvent()->getFullActionName();
        $layout = $observer->getEvent()->getLayout();

        if ($layout && in_array($handle, $this->handles)) {
            $layout->getUpdate()->addHandle('mage24fix_fix_store_switcher');
        }
    }
}
