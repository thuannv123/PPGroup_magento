<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acommerce\Ccpp\Test\Unit\Gateway\Command\Response;

use Acommerce\Ccpp\Gateway\Command\Response\AcceptCommand;

class AcceptCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AcceptCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validator = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ValidatorInterface'
        )->getMockForAbstractClass();
        $this->handler = $this->getMockBuilder(
            'Magento\Payment\Gateway\Response\HandlerInterface'
        )->getMockForAbstractClass();
        $this->paymentDO = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectInterface'
        )->getMockForAbstractClass();
        $this->payment = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterface'
        )
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->payment);
        $this->payment->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->command = new AcceptCommand(
            $this->validator,
            $this->handler
        );
    }

    public function testExecuteCapture()
    {
        $commandSubject = [
            'payment' => $this->paymentDO,
            'response' => [
                'authMode' => 'A'
            ]
        ];

        $this->validator->expects(static::once())
            ->method('validate')
            ->with($commandSubject)
            ->willReturn($this->resultMock);
        $this->resultMock->expects(static::once())
            ->method('isValid')
            ->willReturn(true);
        $this->handler->expects(static::once())
            ->method('handle')
            ->with(
                $commandSubject,
                $commandSubject['response']
            );
        $this->payment->expects(static::once())
            ->method('capture');
        $this->orderMock->expects(static::once())
            ->method('save');

        $this->command->execute($commandSubject);
    }

    public function testExecuteAuthorize()
    {
        $commandSubject = [
            'payment' => $this->paymentDO,
            'response' => [
                'authMode' => 'E'
            ]
        ];

        $orderAdapater = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\OrderAdapterInterface'
        )
            ->getMockForAbstractClass();

        $this->validator->expects(static::once())
            ->method('validate')
            ->with($commandSubject)
            ->willReturn($this->resultMock);
        $this->resultMock->expects(static::once())
            ->method('isValid')
            ->willReturn(true);
        $this->handler->expects(static::once())
            ->method('handle')
            ->with(
                $commandSubject,
                $commandSubject['response']
            );
        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($orderAdapater);
        $orderAdapater->expects(static::once())
            ->method('getGrandTotalAmount')
            ->willReturn(20.02);
        $this->payment->expects(static::once())
            ->method('authorize')
            ->with(
                false,
                20.02
            );
        $this->orderMock->expects(static::once())
            ->method('save');

        $this->command->execute($commandSubject);
    }
}
