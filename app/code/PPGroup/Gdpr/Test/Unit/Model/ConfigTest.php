<?php

namespace PPGroup\Gdpr\Test\Unit\Model;

use Amasty\Gdpr\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Amasty\Gdpr\Model\Config as ConfigModel;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Test for CommonTaxCollector plugin
 */
class ConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var ConfigModel
     */
    private ConfigModel $configModel;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var AppConfig|MockObject
     */
    protected AppConfig $scopeConfig;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigInterface;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mockContext();
        $this->mockScopeConfig();
        $this->configModel = new ConfigModel($this->scopeConfig);

    }


    protected function mockContext()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(AppConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
    }

    private function mockScopeConfig()
    {
        $this->scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test plugin test is admin delete notification Enabled
     */
    public function testIsAdminDeleteNotificationEnabled()
    {

        $expected = true;
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($expected);

        $this->assertTrue($this->configModel->isAdminDeleteNotificationEnabled());
    }

    /**
     * Test plugin test get admin notification template
     */
    public function testGetAdminNotificationTemplate()
    {
        $expected = 'test';
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->configModel->getAdminNotificationTemplate());
    }

    /**
     * Test plugin test get admin notification sender
     */
    public function testGetAdminNotificationSender()
    {
        $expected = 'test';
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->configModel->getAdminNotificationSender());
    }

    /**
     * Test plugin test get admin notification receiver
     */
    public function testGetAdminNotificationReceiver()
    {
        $expected = ['test'];
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn('test');

        $this->assertEquals($expected, $this->configModel->getAdminNotificationReciever());
    }
}
