/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Customer/js/customer-data',
        'PPGroup_AccessTrade/js/model/url-builder',
        'mageUtils'
    ],
    function (customerData, urlBuilder, utils) {
        "use strict";
        return {
            /**
             *
             * @param rk
             * @returns {string|*}
             */
            getUrlRecordRk: function (rk) {
                var params = {rk: rk};
                var urls = {
                    'guest': '/access_trade/:rk/record',
                    'customer': '/access_trade/:rk/record'
                };

                return this.getUrl(urls, params);
            },

            /** Get url for service */
            getUrl: function (urls, urlParams) {
                var url;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    url = urls['default'];
                } else {
                    url = urls[this.getCheckoutMethod()];
                }
                return urlBuilder.createUrl(url, urlParams);
            },

            /**
             *
             * @returns {string}
             */
            getCheckoutMethod: function () {
                var customer = customerData.get('customer');
                return !customer().firstname ? 'guest' : 'customer';
            }
        };
    }
);
