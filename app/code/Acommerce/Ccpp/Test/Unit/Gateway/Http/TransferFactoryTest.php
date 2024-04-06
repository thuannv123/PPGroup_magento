<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Gateway\Http;

use Acommerce\Ccpp\Gateway\Http\TransferFactory;

class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $clientConfig = ['timeout' => 60, 'verifypeer' => 1];
        $request = ['request'];
        $method = \Laminas\Http\Request::METHOD_POST;
        $uri = 'https://secure-test.ccpp.com/wcc/iadmin';
        $expectedBuildResult = 'buildResult';


        $config = $this->getMockBuilder('Magento\Payment\Gateway\ConfigInterface')
            ->getMockForAbstractClass();

        $config->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['sandbox_flag', null, 1],
                    ['iadmin_url_test', null, 'https://secure-test.ccpp.com/wcc/iadmin'],
                ]
            );

        $transferBuilder = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferBuilder')
            ->getMock();
        $transferBuilder->expects($this->once())
            ->method('setClientConfig')
            ->with($clientConfig)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('setMethod')
            ->with($method)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('setUri')
            ->with($uri)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('build')
            ->willReturn($expectedBuildResult);

        $factory = new TransferFactory($config, $transferBuilder);
        $this->assertEquals($expectedBuildResult, $factory->create($request));
    }
}
