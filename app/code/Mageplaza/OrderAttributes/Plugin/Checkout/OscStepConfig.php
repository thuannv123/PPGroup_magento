<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Plugin\Checkout;

use Mageplaza\Osc\Model\DefaultConfigProvider;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class OscStepConfig
 * @package Mageplaza\OrderAttributes\Plugin\Checkout
 */
class OscStepConfig
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * OscStepConfig constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $output
     *
     * @return array
     */
    public function afterGetConfig(
        DefaultConfigProvider $subject,
        array $output
    ) {
        if (isset($output['oscConfig']) && $this->helperData->isOscPage()) {
            $steps                             = $this->helperData->getStepCollection();
            $output['oscConfig']['stepConfig'] = [];
            foreach ($steps as $step) {
                $output['oscConfig']['stepConfig']['stepCodes'][]                 = $step->getCode();
                $output['oscConfig']['stepConfig']['positions'][$step->getCode()] = $step->getPosition();
            }
        }

        return $output;
    }
}
