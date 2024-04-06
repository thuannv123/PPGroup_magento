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
namespace Acommerce\Sales\Plugin\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State as HandlerState;

/**
 * Export Sales Order To Cpms
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class State
{

    /**
     * Check order status before save
     *
     * @param HandlerState $subject Subject
     * @param callable     $proceed proceed
     * @param Order        $order
     *
     * @return $this
     */
    public function aroundCheck(
        HandlerState $subject,
        callable $proceed,
        Order $order
    ) {
        if (!$order->isCanceled()
            && !$order->canUnhold()
            && !$order->canInvoice()
            && !$order->canShip()
        ) {
            if (0 == $order->getBaseGrandTotal() || $order->canCreditmemo()) {
                if ($order->getState() !== Order::STATE_COMPLETE) {
                    //$order->setState(Order::STATE_COMPLETE)
                    //    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                }
            } elseif (floatval($order->getTotalRefunded())
                || !$order->getTotalRefunded() && $order->hasForcedCanCreditmemo()
            ) {
                if ($order->getState() !== Order::STATE_CLOSED) {
                    $order->setState(Order::STATE_CLOSED)
                        ->setStatus(
                            $order->getConfig()
                                ->getStateDefaultStatus(Order::STATE_CLOSED)
                        );
                }
            }
        }
        if ($order->getState() == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus(
                    $order->getConfig()
                        ->getStateDefaultStatus(Order::STATE_PROCESSING)
                );
        }

        return $subject;
    }
}
