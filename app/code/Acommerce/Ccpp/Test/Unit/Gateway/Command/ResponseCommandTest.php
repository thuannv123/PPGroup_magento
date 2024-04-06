<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Gateway\Command;

use Magento\Sales\Model\Order;
use Acommerce\Ccpp\Gateway\Command\ResponseCommand;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Acommerce\Ccpp\Gateway\Request\HtmlRedirect\OrderDataBuilder;
use Acommerce\Ccpp\Gateway\Validator\DecisionValidator;

/**
 * Class ResponseCommandTest
 *
 * Test for class \Acommerce\Ccpp\Gateway\Command\ResponseCommand
 */
class ResponseCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseCommand
     */
    protected $responseCommand;

    /**
     * @var CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandPool;

    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->commandPool = $this->getMockBuilder('Magento\Payment\Gateway\Command\CommandPoolInterface')
            ->getMockForAbstractClass();
        $this->validator = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ValidatorInterface')
            ->getMockForAbstractClass();
        $this->orderRepository = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder('Magento\Payment\Model\Method\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDataObjectFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();


        $this->responseCommand = new ResponseCommand(
            $this->commandPool,
            $this->validator,
            $this->orderRepository,
            $this->paymentDataObjectFactory,
            $this->logger
        );
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $response = [
            OrderDataBuilder::ORDER_ID => '1',
            'transStatus' => 'Y'
        ];
        $commandSubject = [
            'response' => $response
        ];

        $paymentDO = $this->getMockBuilder('Magento\Payment\Gateway\Data\PaymentDataObjectInterface')
            ->getMockForAbstractClass();
        $commandMock = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();
        $orderPaymentMock = $this->getMockBuilder('Magento\Payment\Model\InfoInterface')
            ->getMockForAbstractClass();
        $resultMock = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterface'
        )->getMockForAbstractClass();
        $orderMock = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->getMockForAbstractClass();

        $this->logger->expects(static::once())
            ->method('debug')
            ->with($commandSubject);
        $commandMock->expects(static::once())
            ->method('execute')
            ->with(['response' => $response, 'payment' => $paymentDO]);
        $orderMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($orderPaymentMock);

        $this->paymentDataObjectFactory->expects(static::once())
            ->method('create')
            ->with($orderPaymentMock)
            ->willReturn($paymentDO);

        $this->validator->expects(static::once())
            ->method('validate')
            ->with($commandSubject)
            ->willReturn($resultMock);
        $resultMock->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->orderRepository->expects(static::once())
            ->method('get')
            ->with($response[OrderDataBuilder::ORDER_ID])
            ->willReturn($orderMock);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->willReturn($commandMock);

        $this->responseCommand->execute($commandSubject);
    }
}
