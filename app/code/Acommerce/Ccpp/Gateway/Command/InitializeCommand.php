<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Command;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CaptureCommand
 */
class InitializeCommand implements CommandInterface
{
    /**
     * @var \Acommerce\Ccpp\Helper\Data
     */
    private $helper;

    /**
     * InitializeCommand constructor.
     * @param \Acommerce\Ccpp\Helper\Data $helper
     */
    public function __construct(\Acommerce\Ccpp\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Framework\DataObject $stateObject */
        $stateObject = $commandSubject['stateObject'];

        $paymentDO = SubjectReader::readPayment($commandSubject);

        $payment = $paymentDO->getPayment();
        if (!$payment instanceof Payment) {
            throw new \LogicException('Order Payment should be provided');
        }

        $payment->setAmountAuthorized($payment->getOrder()->getTotalDue());
        $payment->setBaseAmountAuthorized($payment->getOrder()->getBaseTotalDue());
        $canSendEmail = false;
        if($this->helper->getInvoiceEmailConfig())
            $canSendEmail = true;
        $payment->getOrder()->setCanSendNewEmailFlag($canSendEmail);

        $stateObject->setData(OrderInterface::STATE, Order::STATE_PENDING_PAYMENT);
        $stateObject->setData(OrderInterface::STATUS, Order::STATE_PENDING_PAYMENT);
        $stateObject->setData('is_notified', false);
    }
}
