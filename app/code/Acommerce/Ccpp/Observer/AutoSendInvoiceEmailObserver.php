<?php

namespace Acommerce\Ccpp\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Acommerce\Ccpp\Helper\Data as HelperData;

class AutoSendInvoiceEmailObserver implements ObserverInterface
{
    /**
     *  Invoice Sender
     *
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     *  Helper
     *
     * @var HelperData
     */
    protected $helper;

    /**
     * AutoSendInvoiceEmail constructor.
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        InvoiceSender $invoiceSender,
        HelperData $helper
    )
    {
        $this->invoiceSender = $invoiceSender;
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $payment = $observer->getEvent()->getPayment();
        $method = $payment->getMethodInstance();
        if($method->getCode() == 'ccpp') {
            if(!$invoice->getEmailSent() && $this->helper->getInvoiceEmailConfig()) {
                $this->invoiceSender->send($invoice);
            }
        }
    }
}