define([
    'jquery',
    'Amasty_GdprFrontendUi/js/action/cookie-decliner'
], function ($, cookieDecliner) {
    'use strict';

    var mixin = {
        initialize: function () {
            return cookieDecliner.call(this, this._super);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
