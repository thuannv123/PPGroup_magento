<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Command\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Acommerce\Ccpp\Helper\Data as Helper;

/**
 * Class AcceptCommand
 */
class AcceptCommand implements CommandInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var OrderSender
     */
    protected $orderSender;
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param ValidatorInterface $validator
     * @param HandlerInterface $handler
     * @param OrderSender $orderSender
     * @param Helper $helper
     */
    public function __construct(
        ValidatorInterface $validator,
        HandlerInterface $handler,
        OrderSender $orderSender,
        Helper $helper
    ) {
        $this->validator = $validator;
        $this->handler = $handler;
        $this->orderSender = $orderSender;
        $this->helper = $helper;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return string
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $response = SubjectReader::readResponse($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $result = $this->validator->validate($commandSubject);
        /* if (!$result->isValid()) {
            throw new CommandException(
                $result->getFailsDescription()
                ? __(implode(', ', $result->getFailsDescription()))
                : __('Gateway response is not valid.')
            );
        } */

        $this->handler->handle(
            $commandSubject,
            SubjectReader::readResponse($commandSubject)
        );

        /*switch ($response['eci']) {
            case '05':
                //Visa authentication successful
                $payment->capture();
                break;
             case '06':
                //Visa authentication successful
                $payment->capture();
                break;
            case '01':
                //Mastercard authentication successful
                $payment->capture();
                break;
            case '02':
                //Mastercard authentication successful
                $payment->capture();
                break;
            default:
                $payment->authorize(
                    false,
                    $paymentDO->getOrder()->getGrandTotalAmount()
                );
                break;
        }*/

        $payment->capture();
        if (!$payment->getOrder()->getEmailSent() && !$this->helper->getInvoiceEmailConfig()) {
            $this->orderSender->send($payment->getOrder());
        }

        $payment->getOrder()->save();
    }
}
