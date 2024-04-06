/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/sidebar'
], function (Component, ko, $, sidebarModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/note',  
            rendererTemplates: []
        },
        initialize: function () {
            this._super();
        },
    });
});
