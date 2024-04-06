define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Acommerce_Ccpp/js/form-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        $,
        Component,
        additionalValidators,
        customerData,
        formBuilder,
        errorProcessor,
        fullScreenLoader,
        redirectOnSuccessAction
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Acommerce_Ccpp/payment/qrcode',
                redirectAfterPlaceOrder: false
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
            /**
             * Place order.
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    this.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                        function () {
                            self.afterPlaceOrder();

                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }
                    );

                    return true;
                }

                return false;
            },
            /**
             * After place order action
             */
            afterPlaceOrder: function () {
                var self = this;

                $.get(window.checkoutConfig.payment[this.getCode()].transactionDataUrl)
                    .done(function (response) {
                        customerData.invalidate(['cart', 'checkout-data']);
                        formBuilder.build(response).submit();
                    }).fail(function (response) {
                    errorProcessor.process(response, self.messageContainer);
                    fullScreenLoader.stopLoader();
                });
            },
        });
    }
);