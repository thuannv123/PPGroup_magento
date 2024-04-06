define([
    'jquery',
    'ko',
    'mage/translate'
], function ($, ko, $t) {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                template: 'PPGroup_Ccpp/payment/ccpp-form',
                redirectAfterPlaceOrder: false
            },

            initialize: function () {
                this._super();

                this.paymentLogo(window.checkoutConfig.payment[this.item.method].logo);
                this.additionalData(window.checkoutConfig.payment[this.item.method].additional_info);
            },

            /**
             * @return {exports}
             */
            initObservable: function () {
                this._super().observe([
                    'paymentLogo',
                    'additionalData'
                ]);

                return this;
            },

            /**
             *
             * @returns {*}
             */
            getLogoUrl: function () {
                return this.paymentLogo();
            },

            /**
             *
             * @returns {*}
             */
            getAdditionalInformation: function () {
                return this.additionalData();
            }
        });
    }
});
