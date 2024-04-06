<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Model\Adminhtml\Source;

use Acommerce\Ccpp\Model\Adminhtml\Source\TestAction;

/**
 * Class TestActionTest
 *
 * Test for class \Acommerce\Ccpp\Model\Adminhtml\Source\TestAction
 */
class TestActionTest extends \PHPUnit_Framework_TestCase
{
    const REFUSED = 'REFUSED';

    const AUTHORISED = 'AUTHORISED';

    const ERROR = 'ERROR';

    const CAPTURED = 'CAPTURED';

    /**
     * Run test toOptionArray method
     */
    public function testToOptionArray()
    {
        $testAction = new TestAction();
        $this->assertEquals(
            [
                [
                    'value' => self::REFUSED,
                    'label' => __('Refused')
                ],
                [
                    'value' => self::AUTHORISED,
                    'label' => __('Authorised')
                ],
                [
                    'value' => self::ERROR,
                    'label' => __('Error')
                ],
                [
                    'value' => self::CAPTURED,
                    'label' => __('Captured')
                ],
            ],
            $testAction->toOptionArray()
        );
    }
}
