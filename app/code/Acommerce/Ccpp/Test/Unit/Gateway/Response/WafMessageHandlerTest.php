<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Gateway\Response;

use Acommerce\Ccpp\Gateway\Response\WafMessageHandler;

class WafMessageHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $response = [
            'wafMerchMessage' => 'waf.caution'
        ];
        $additionalInfoExpectation = [
            ['waf_merch_message', 'waf.caution']
        ];

        $paymentDO = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectInterface'
        )
            ->getMockForAbstractClass();
        $paymentInfo = $this->getMockBuilder(
            'Magento\Payment\Model\InfoInterface'
        )
            ->getMockForAbstractClass();

        $paymentDO->expects(static::any())
            ->method('getPayment')
            ->willReturn($paymentInfo);
        $paymentInfo->expects(static::once())
            ->method('setAdditionalInformation')
            ->willReturnMap($additionalInfoExpectation);

        $handler = new WafMessageHandler();
        $handler->handle(
            ['payment' => $paymentDO],
            $response
        );
    }

    public function testHandleFraud()
    {
        $response = [
            'wafMerchMessage' => 'waf.warning'
        ];
        $additionalInfoExpectation = [
            ['waf_merch_message', 'waf.warning']
        ];

        $paymentDO = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectInterface'
        )
            ->getMockForAbstractClass();
        $paymentInfo = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $paymentDO->expects(static::any())
            ->method('getPayment')
            ->willReturn($paymentInfo);
        $paymentInfo->expects(static::once())
            ->method('setAdditionalInformation')
            ->willReturnMap($additionalInfoExpectation);
        $paymentInfo->expects(static::once())
            ->method('setIsFraudDetected')
            ->with(true);

        $handler = new WafMessageHandler();
        $handler->handle(
            ['payment' => $paymentDO],
            $response
        );
    }
}
