<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Gateway\Command;

use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Acommerce\Ccpp\Gateway\Command\InitializeCommand;

/**
 * Class InitializeCommandTest
 *
 * Test for class \Acommerce\Ccpp\Gateway\Command\InitializeCommand
 */
class InitializeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InitializeCommand
     */
    protected $initializeCommand;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->initializeCommand = new InitializeCommand();
    }

    public function testExecuteException()
    {
        $this->setExpectedException('LogicException', 'Order Payment should be provided');

        $stateObjectMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentDO = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectInterface'
        )
            ->getMockForAbstractClass();
        $paymentInfo = $this->getMockBuilder('Magento\Payment\Model\InfoInterface')
            ->getMockForAbstractClass();

        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentInfo);

        $this->initializeCommand->execute(
            [
                'payment' => $paymentDO,
                'stateObject' => $stateObjectMock
            ]
        );
    }

    public function testExecute()
    {
        $stateObjectMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentDO = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectInterface'
        )
            ->getMockForAbstractClass();
        $paymentInfo = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentDO->expects(static::any())
            ->method('getPayment')
            ->willReturn($paymentInfo);
        $stateObjectMock->expects($this->at(0))
            ->method('setData')
            ->with(OrderInterface::STATE, Order::STATE_PENDING_PAYMENT);
        $stateObjectMock->expects($this->at(1))
            ->method('setData')
            ->with(OrderInterface::STATUS, Order::STATE_PENDING_PAYMENT);
        $stateObjectMock->expects($this->at(2))
            ->method('setData')
            ->with('is_notified', false);

        $paymentInfo->expects(static::any())
            ->method('getOrder')
            ->willReturn($order);
        $order->expects(static::once())
            ->method('getTotalDue')
            ->willReturn(10);
        $order->expects(static::once())
            ->method('getBaseTotalDue')
            ->willReturn(10);
        $order->expects(static::once())
            ->method('setCanSendNewEmailFlag')
            ->with(false);

        $paymentInfo->expects(static::once())
            ->method('setAmountAuthorized')
            ->with(10);
        $paymentInfo->expects(static::once())
            ->method('setBaseAmountAuthorized')
            ->with(10);

        $this->initializeCommand->execute(
            [
                'payment' => $paymentDO,
                'stateObject' => $stateObjectMock
            ]
        );
    }
}
