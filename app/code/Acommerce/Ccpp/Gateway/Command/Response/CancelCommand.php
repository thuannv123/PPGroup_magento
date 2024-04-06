<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Command\Response;

use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class CancelCommand
 */
class CancelCommand implements CommandInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param OrderManagementInterface $orderManagement
     * @param Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderManagement = $orderManagement;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return array
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            throw new \LogicException;
        }

        $additionalPayment = $payment->getAdditionalInformation();

        if(isset($additionalPayment['payment_status']) && !empty($additionalPayment['payment_status'])) {

            $paymentStatus = $additionalPayment['payment_status'];
            $autoCancel = $this->scopeConfig->getValue(
                'payment/ccpp/auto_cancel',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $paymentStatuses = $this->scopeConfig->getValue(
                'payment/ccpp/payment_status',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if((int)$autoCancel == 1 && !empty($paymentStatuses)) {
                $paymentStatuses = explode(',', $paymentStatuses);

                if(in_array($paymentStatus, $paymentStatuses)) {
                    $this->orderManagement->cancel($paymentDO->getOrder()->getId());
                }
            }
        }

        $this->checkoutSession->setLastRealOrderId($paymentDO->getOrder()->getOrderIncrementId());
        //$this->checkoutSession->restoreQuote();
    }
}
