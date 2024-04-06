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

define([
    'mage/utils/wrapper',
    'jquery',
    'Magento_Checkout/js/model/step-navigator'
], function (wrapper, $, stepNavigator) {
    'use strict';

    return function (quote) {

        var updateSteps = function (currentSteps) {
            if (currentSteps.length > 0) {
                if (typeof window.checkoutConfig.oscConfig !== 'undefined') {
                    $('.mp-osc-custom-step-content').each(function () {
                        if (!currentSteps.includes($(this).attr('id'))) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    });
                } else {
                    var allStep = stepNavigator.steps(), mpOaSteps = stepNavigator.mpOaSteps();
                    // Add Steps and stepCodes following CartCondition.
                    currentSteps.forEach(function (stepCode) {
                        var stepOjb = -1;
                        mpOaSteps.forEach(function (stepOa) {
                            if (stepOa.code === stepCode) {
                                stepOjb = stepOa;
                            }
                        });
                        if (stepOjb !== -1 && !stepNavigator.stepCodes.includes(stepOjb.code)) {
                            allStep.push(stepOjb);
                            stepNavigator.stepCodes.push(stepOjb.code);
                            stepNavigator.validCodes.push(stepOjb.code);
                        }
                    });
                    // remove Steps and stepCodes following CartCondition.
                    allStep.forEach(function (step, index) {
                        if (!currentSteps.includes(step.code) && step.code !== 'shipping' && step.code !== 'payment') {
                            allStep.splice(index, 1);
                            var indexStepCode = stepNavigator.stepCodes.indexOf(step.code);
                            if (indexStepCode !== -1) {
                                stepNavigator.stepCodes.splice(indexStepCode, 1);

                            }
                            indexStepCode = stepNavigator.validCodes.indexOf(step.code);
                            if (indexStepCode !== -1) {
                                stepNavigator.validCodes.splice(indexStepCode, 1);
                            }
                        }
                    });

                }
            }

        };

        quote.setTotals = wrapper.wrapSuper(quote.setTotals, function (data) {
            this._super(data);
            var steps = data.extension_attributes.mp_orderattributes_steps;
            updateSteps(steps);
            stepNavigator.handleHash();
        });

        return quote;
    };
});