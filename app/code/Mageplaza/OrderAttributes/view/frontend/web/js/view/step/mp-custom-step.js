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

define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'uiRegistry',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (
        ko,
        Component,
        _,
        registry,
        stepNavigator
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_OrderAttributes/step/mp-custom'
            },
            isVisible: ko.observable(),
            stepCode: "step_code",
            stepTitle: "step_title",
            iconType: 'icon_type',
            iconClass: "icon_class",
            iconImg: "icon_img",
            sortOrder: "sort_order",
            isNeedShow: window.checkoutConfig.totalsData.mp_orderattributes_steps ? window.checkoutConfig.totalsData.mp_orderattributes_steps.includes('step_code') : false,
            /**
             * mpOrderAttributes
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                if (this.isNeedShow) {
                    stepNavigator.registerStep(
                        this.stepCode,
                        null,
                        this.stepTitle,
                        this.isVisible,
                        _.bind(this.navigate, this),
                        parseInt(this.sortOrder)
                    );
                }
                stepNavigator.registermpOaSteps(
                    this.stepCode,
                    this.stepTitle,
                    this.isVisible,
                    _.bind(this.navigate, this),
                    parseInt(this.sortOrder)
                );
                return this;
            },
            /**
             * Navigator change hash handler.
             *
             * @param {Object} step - navigation step
             */
            navigate: function (step) {
                step && step.isVisible(true);
            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                var source = registry.get('mpOrderAttributesCheckoutProvider'),
                    result = true;

                if (source.get(this.stepCode)) {
                    source.set('params.invalid', false);
                    source.trigger(this.stepCode + '.data.validate');
                    if (source.get('params.invalid')) {
                        result = false;
                    }
                }
                if (result) {
                    stepNavigator.next();
                }
            }
        });
    }
);
