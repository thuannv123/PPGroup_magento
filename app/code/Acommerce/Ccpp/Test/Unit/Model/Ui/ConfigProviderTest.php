<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Model\Ui;

use Magento\Framework\Url;
use Acommerce\Ccpp\Model\Ui\ConfigProvider;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Acommerce\Ccpp\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\Url')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
    }

    /**
     * Run test getConfig method
     */
    public function testGetConfig()
    {
        $configProvider = new ConfigProvider($this->urlBuilderMock);
        $this->urlBuilderMock->expects(static::exactly(2))
            ->method('getUrl')
            ->willReturn(ConfigProvider::TRANSACTION_DATA_URL);

        $this->assertEquals(
            [
                'payment' => [
                    ConfigProvider::ACOMMERCE_CWORLD => [
                        'transactionDataUrl' => $this->urlBuilderMock->getUrl(ConfigProvider::TRANSACTION_DATA_URL)
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
