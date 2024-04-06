<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
namespace Acommerce\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Export Sales Order To Cpms
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class UpdateShippedState implements ObserverInterface
{

    const STATE_SHIPPED = 'shipped';
    /**
     * Update Order state to invoiced
     *
     * @param \Magento\Framework\Event\Observer $observer Observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();

        $order->setState(self::STATE_SHIPPED)
            ->setStatus(
                $order->getConfig()->getStateDefaultStatus(self::STATE_SHIPPED)
            );

    }
}
