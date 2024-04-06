<?php
/**
 * Author: Son Nguyen
 * Copyright © Wiki Solution All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Antispam\Test\Unit\Helper;

use PPGroup\Antispam\Helper\Data;
use Magento\Framework\App\Helper\Context;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config;

/**
 * Class DataTest
 *
 * @see Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var Data
     */
    private Data $data;

    /**
     * @var Config|MockObject
     */
    protected Config|MockObject $scopeConfig;
    /**
     * @var Context|MockObject
     */

    private Context|MockObject $context;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mockContext();

        $this->data = new Data($this->context);
    }

    protected function mockContext()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
    }

    /**
     * Test check is chinese
     */
    public function testIsChinese()
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(Data::XML_PATH_ANTISPAM_DISALLOW_CHINESE_WORDS)
            ->willReturn(1);
        $string = '韩国成';
        $result = $this->data->isChinese($string);
        $this->assertTrue($result);
    }

    /**
     * Test check is english
     */
    public function testIsEnglish()
    {
        $string = 'Sơn Nguyễn';
        $result = $this->data->isEnglish($string);
        $this->assertFalse($result);
    }

    /**
     * Test check isForeignWords
     */
    public function testIsForeignWords()
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(Data::XML_PATH_ANTISPAM_DISALLOW_FOREIGN_WORDS)
            ->willReturn(1);
        $string = 'Sơn Nguyễn';
        $result = $this->data->isForeignWords($string);
        $this->assertTrue($result);
    }

    /**
     * Test check is spam content
     */
    public function testIsSpamContent()
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(Data::XML_PATH_ANTISPAM_LIST_WORDS_DISALLOW)
            ->willReturn('http://,【');

        $string = '【 sonnguyen';
        $result = $this->data->isSpamContent($string);
        $this->assertTrue($result);
    }

    /**
     * Test check has Special Characters
     */
    public function testHasSpecialCharacters()
    {
        $string = '@';
        $result = $this->data->hasSpecialCharacters($string);
        $this->assertTrue($result);
    }

    /**
     * Test check Spam
     */
    public function testCheckSpam()
    {

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->withAnyParameters()
            ->willReturn(1);

        $string = 'เกี่ยวกับเรา';

        $result = $this->data->checkSpam($string);
        $this->assertTrue($result);
    }
}
