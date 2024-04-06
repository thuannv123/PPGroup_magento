define([
    'jquery',
    "Magento_Customer/js/customer-data",
    'mage/cookies'
], function ($,customerData) {
    'use strict';

    var mixin = {
        showPopupWithConsentPolicy: function () {
            var self = this;
            $.when( this.getCustomer() ).done(function(customer) {
                if(typeof customer.fullname !== 'undefined') {
                    self._super();
                } else {
                    var acceptPolicy = $.cookie('accept_policy');
                    if(!acceptPolicy) {
                        self._super();
                        $.cookie('accept_policy', 1);
                    }
                }
            });
        },
        getCustomer: function () {
            return customerData.get('customer')();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});