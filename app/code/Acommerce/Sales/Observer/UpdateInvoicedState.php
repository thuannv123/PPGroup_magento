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
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

/**
 * Export Sales Order To Cpms
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class UpdateInvoicedState implements ObserverInterface
{

    const STATE_INVOICED = 'invoiced';

    /**
     *  Invoice Sender
     *
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * UpdateInvoicedState constructor.
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        InvoiceSender $invoiceSender
    )
    {
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Update Order state to invoiced
     *
     * @param \Magento\Framework\Event\Observer $observer Observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $invoice = $observer->getInvoice();
        $order = $invoice->getOrder();
        if(!$invoice->getEmailSent()) {
            $this->invoiceSender->send($invoice);
        }
        $order->setState(self::STATE_INVOICED)
            ->setStatus(
                $order->getConfig()->getStateDefaultStatus(self::STATE_INVOICED)
            )
            ->save();

    }
}
