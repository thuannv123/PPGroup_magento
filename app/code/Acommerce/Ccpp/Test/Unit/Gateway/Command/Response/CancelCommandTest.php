<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acommerce\Ccpp\Test\Unit\Gateway\Command\Response;

use Acommerce\Ccpp\Gateway\Command\Response\CancelCommand;

/**
 * Class CancelCommandTest
 */
class CancelCommandTest extends \PHPUnit_Framework_TestCase
{
    const ORDER_INCREMENT_ID = 1;

    const ORDER_ID = 2;

    /**
     * @var CancelCommand
     */
    protected $cancelCommand;

    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderManagementInterface;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->orderManagementInterface = $this->getMockBuilder('Magento\Sales\Api\OrderManagementInterface')
            ->getMockForAbstractClass();

        $this->checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->setMethods(['setLastRealOrderId', 'restoreQuote'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cancelCommand = new CancelCommand(
            $this->orderManagementInterface,
            $this->checkoutSession
        );
    }

    public function testSuccessCancelOrder()
    {
        $paymentDO = $this->getMockBuilder('Magento\Payment\Gateway\Data\PaymentDataObjectInterface')
            ->getMockForAbstractClass();
        $orderAdapter = $this->getMockBuilder('Magento\Payment\Gateway\Data\OrderAdapterInterface')
            ->getMockForAbstractClass();
        $paymentInfo = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();


        $orderAdapter->expects(static::once())
            ->method('getId')
            ->willReturn(self::ORDER_ID);
        $orderAdapter->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn(self::ORDER_INCREMENT_ID);
        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentInfo);
        $paymentDO->expects(static::any())
            ->method('getOrder')
            ->willReturn($orderAdapter);
        $this->checkoutSession->expects(static::once())
            ->method('setLastRealOrderId')
            ->with(self::ORDER_INCREMENT_ID);
        $this->orderManagementInterface->expects(static::once())
            ->method('cancel')
            ->with(self::ORDER_ID);

        $this->checkoutSession->expects(static::once())
            ->method('restoreQuote');

        $this->cancelCommand->execute(['payment' => $paymentDO]);
    }
}
