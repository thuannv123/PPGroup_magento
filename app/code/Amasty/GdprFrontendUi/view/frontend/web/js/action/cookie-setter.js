define([
    'jquery',
    'Amasty_GdprFrontendUi/js/model/cookie'
], function ($, cookies) {
    'use strict';

    return function (methodSet, parent) {
        return function (cookieName, data) {
            var isCookieAllowed = cookies.isCookieAllowed(cookieName);

            if (isCookieAllowed || !window.isGdprCookieEnabled || cookieName === 'mage-messages') {
                methodSet.call(parent, cookieName, data);
            }
        };
    };
});
