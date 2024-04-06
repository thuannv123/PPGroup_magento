/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Acommerce_Ccpp/js/form-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/place-order',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/modal/confirm',
        'mage/translate',
        'Mageplaza_OrderAttributes/js/model/order-attributes-data'
    ],
    function (
        $,
        Component,
        quote,
        customer,
        urlBuilder,
        storage,
        formBuilder,
        errorProcessor,
        fullScreenLoader,
        placeOrderService,
        customerData,
        additionalValidators,
        confirm,
        $t,
        checkoutData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Acommerce_Ccpp/payment/ccpp-form',
                redirectAfterPlaceOrder: false
            },

            initialize: function () {
                this._super();

                $(document).on('click','.ccpp-installment dt input[type="radio"]', function(){
                    if($(this).is(':checked')){

                        $('.payment-method #ccpp').trigger('click');
                    }
                });

                $(document).on('click','.payment-method > .choice > input[type="radio"]:not(#ccpp)', function(){
                    if($(this).is(':checked')){
                        $('.ccpp-installment dt input[type="radio"]').prop('checked', false);
                    }
                });
            },

            /** Open window with  */
            showAcceptanceWindow: function (data, event) {
                window.open(
                    $(event.target).attr('href'),
                    'olcwhatispaypal',
                    'toolbar=no, location=no,' +
                    ' directories=no, status=no,' +
                    ' menubar=no, scrollbars=yes,' +
                    ' resizable=yes, ,left=0,' +
                    ' top=0, width=400, height=350'
                );

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
                        console.log(response);
                        fullScreenLoader.stopLoader();
                    });
            },

            /**
             * Rewrites place order deferred object with worldplay guest cart service handler
             * as a workaround for issue with customer sections flush on regular place order action
             * @returns {*}
             */
            getPlaceOrderDeferredObject: function () {
                var self = this;

                if (customer.isLoggedIn()) {
                    return this._super();
                }

                return $.when(
                    placeOrderService(
                        urlBuilder.createUrl('/ccpp-guest-carts/:quoteId/payment-information', {
                            quoteId: quote.getQuoteId()
                        }),
                        {
                            cartId: quote.getQuoteId(),
                            billingAddress: quote.billingAddress(),
                            paymentMethod: this.getData(),
                            email: quote.guestEmail
                        },
                        self.messageContainer
                    )
                );
            },

            /**
             * Payment method code getter
             * @returns {String}
             */
            getCode: function () {
                return 'ccpp';
            },

            getInstallmentDetails: function() {
                var details = [];
                var $this = this;

                $.each(window.checkoutConfig.payment.InstallmentDetails[this.item.method],
                    function (index, el)
                    {
                        var minAmount = parseFloat(el.min_amount);
                        var maxAmount = parseFloat(el.max_amount);

                        if(isNaN(minAmount)) {
                            minAmouunt = 0.00;
                        }

                        if(isNaN(maxAmount)) {
                            maxAmouunt = 0.00;
                        }

                        if(minAmount <= quote.totals._latestValue.grand_total &&
                            maxAmount >= quote.totals._latestValue.grand_total) {
                            el.id = $this.item.method + '-' + el.code;
                            details.push(el);
                        }
                    }
                );
                return details;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'installment_month': this.installmentType(),
                        'installment': this.installment(),
                        'amgdpr_agreement': this.amgdprAgreement()
                    },
                    'extension_attributes':{
                        mpOrderAttributes :this.attributes()
                    }
                        
                };
            },
            attributes: function () {
                var dataArray = _.values(checkoutData.getData()),
                attributes = {},
                extension_attributes = {};
                _.each(dataArray, function (data) {
                    _.extend(attributes, data);
                });

                _.each(attributes, function (attribute, key) {
                    var isFile = false;

                    if (_.isArray(attribute)) {
                        _.each(attribute, function (value) {
                            if (_.isObject(value) && value['file']) {
                                isFile = true;
                                extension_attributes[key] = JSON.stringify(value);
                            }
                            return false;
                        });

                        if (!isFile) {
                            key = key.replace('[]', '');
                            attribute = attribute.length === 1 && attribute[0] === null ? null : attribute.join(',');
                        }
                    }

                    if (!isFile && attribute !== undefined) {
                        extension_attributes[key] = attribute;
                    }
                });
                return (extension_attributes);   
            },
            /**
             * Add Gdpr consent into payment method additional data
             * @returns {String}
             */
            amgdprAgreement: function () {
                var consents = checkoutConfig.amastyGdprConsent.consents || [];
                var consentData = {};
                _.each(consents, function (consent) {

                    var consentElement = $('input[data-gdpr-checkbox-code="' + consent.checkbox_code + '"]:visible');
                    if (consentElement) {
                        consentData[consent.checkbox_code] = Boolean(consentElement.prop('checked'));
                    }
                });
                return JSON.stringify(consentData);
            },

            installmentType: function () {
                var month = 0;
                $('.ccpp-installment input').each(function() {

                    if($(this).is(":checked")) {
                        month = $(this).val();
                    }
                });
                return month;
            },

            installment: function () {
                var installment = false;
                $('.ccpp-installment input').each(function() {
                    if($(this).is(":checked")) {
                        installment = true;
                    }
                });
                return installment;
            },

            /**
             * @return {Boolean}
             */
            validate: function () {
                var items= $('.ccpp-installment input');
                var selected = $('.ccpp-installment input:checked');
                if(items.length > 0 && selected.length == 0) {
                    return false;
                }
                return true;
            },

            /**
             * Place order.
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if(!this.validate()) {
                    confirm({
                        title: $t('Payment Error'),
                        content: $t('Please specify payment option'),
                        actions: {
                            confirm: function(){},
                            cancel: function(){},
                            always: function(){}
                        },buttons: [{
                            text: $t('OK'),
                            class: 'action-primary action-accept',

                            /**
                             * Click handler.
                             */
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }]
                    });
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
            }
        });
    }
);
