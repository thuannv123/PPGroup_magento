<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Test\Unit\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;
use Acommerce\Ccpp\Model\Adminhtml\Source\PaymentAction;

/**
 * Class PaymentActionTest
 *
 * Test for class \Acommerce\Ccpp\Model\Adminhtml\Source\PaymentAction
 */
class PaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Run test toOptionArray method
     */
    public function testToOptionArray()
    {
        $paymentAction = new PaymentAction();
        $this->assertEquals(
            [
                [
                    'value' => AbstractMethod::ACTION_AUTHORIZE,
                    'label' => __('Authorize')
                ],
                [
                    'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                    'label' => __('Authorize and Capture')
                ]
            ],
            $paymentAction->toOptionArray()
        );
    }
}
