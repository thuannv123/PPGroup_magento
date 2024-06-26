define([
    'jquery',
    'Amasty_GdprFrontendUi/js/action/cookie-setter',
    'jquery/jquery-storageapi'
], function ($, cookieSetter) {
    'use strict';

    return function (_super) {
        $.cookieStorage.set = cookieSetter($.cookieStorage.set, $.cookieStorage);

        return _super.call(this);
    };
});
