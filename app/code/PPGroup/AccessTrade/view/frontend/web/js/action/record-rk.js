define(
    [
        'jquery',
        'PPGroup_AccessTrade/js/model/resource-url-manager',
        'mage/storage',
        'mage/url'
    ],
    function (
        $,
        resourceUrlManager,
        storage
    ) {
        "use strict";
        return function (rk, deferred) {
            var serviceUrl = resourceUrlManager.getUrlRecordRk(rk),
                params;

            deferred = deferred || $.Deferred();

            params = {rk: rk};

            storage.post(
                serviceUrl,
                JSON.stringify(params)
            ).done(
                function (response) {
                    // Response rk should be caught here
                }
            ).fail(
                function (response) {
                    deferred.reject();
                }
            ).always(
            );
        }
    }
);
