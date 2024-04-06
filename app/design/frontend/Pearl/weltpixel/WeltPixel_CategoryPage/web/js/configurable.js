define([
    'jquery',
    'priceUtils'
], function ($, priceUtils) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {
            _reloadPrice: function () {
                this._super();
                this._UpdateSalePrice();
            },

            _UpdateSalePrice: function () {
                var wpSalesMsgWrapperSelector = '.wp-sales-off-msg',
                    wpSalesDiscountPercentSelector = '#wp-discount-percent',
                    wpSalesDiscountValueSelector = '#wp-discount-value',
                    result = this.options.spConfig.optionPrices[this.simpleProduct];

                if ($(wpSalesMsgWrapperSelector).length) {
                    if (typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                        var discountPercent =  100 - Math.round((result.finalPrice.amount * 100) / result.oldPrice.amount);
                        var discountValue =  priceUtils.formatPrice(result.oldPrice.amount -  result.finalPrice.amount);
                        $(wpSalesDiscountPercentSelector).html(discountPercent);
                        $(wpSalesDiscountValueSelector).html(discountValue);
                        $(wpSalesMsgWrapperSelector).show();
                    } else {
                        $(wpSalesMsgWrapperSelector).hide();
                    }
                }
            }
        });

        return $.mage.configurable;
    }
});
