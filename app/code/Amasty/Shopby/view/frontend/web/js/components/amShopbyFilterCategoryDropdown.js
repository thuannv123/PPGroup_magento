/**
 *  Amasty Filter Category Dropdown Component
 */

define([
    'jquery',
    'amShopbyFilterAbstract',
    'amShopbyFiltersSync'
], function ($, amShopbyFilterAbstract) {
    'use strict';

    $.widget('mage.amShopbyFilterCategoryDropdown', amShopbyFilterAbstract, {
        options: {},
        classes: {
            itemRemoved: 'amshopby-item-removed'
        },

        /**
         * @private
         * @return {void}
         */
        _create: function () {
            var self = this,
                element = self.element;

            element.click(function (e) {
                e.preventDefault();
                e.stopPropagation();

                element.parent().addClass(self.classes.itemRemoved);
                element.trigger('sync');
                self.renderShowButton(e, element);
                self.apply(element.data('remove-url'), true);
            });
        }
    });

    return $.mage.amShopbyFilterCategoryDropdown;
});
