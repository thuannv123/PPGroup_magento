/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'mage/template'
], function ($, CheckboxElement) {
    'use strict';

    return CheckboxElement.extend({
        defaults: {
            elementTmpl: 'Firebear_PlatformFeeds/form/element/input'
        },

        /**
         * @returns {Element}
         */
        initialize: function () {
            return this._super()
                .initStateConfig();
        },

        /**
         * @returns {Element}
         */
        initStateConfig: function () {
            let feedCategoryAttrs;
            if (this.source) {
                feedCategoryAttrs = this.source.get(this.parentScope);
                let feedCategoryId = feedCategoryAttrs['source_category_feed'];
                this.loadFeedCategoryName(feedCategoryId);
            }

            return this;
        },

        /**
         * Load Feed category name by Id
         * @param feedCategoryId
         */
        loadFeedCategoryName: function (feedCategoryId) {
            let feedCategoryName = this.getCategoryName(feedCategoryId)
            if (feedCategoryName === false) {
                feedCategoryName = feedCategoryId;
            }
            this.selectedFeedCategory = feedCategoryName;
        },

        /**
         * @param feedCategoryId
         * @returns {*}
         */
        getCategoryName: function (feedCategoryId) {
            let data = {
                id: this.source.data.id,
                type_id: this.source.data.type_id,
                category_id: feedCategoryId,
            };

            let self = this;
            return $.ajax(
                {
                    type: "POST",
                    data: data,
                    showLoader: true,
                    url: self.loadCategoryName,
                    dataType: "json",
                    async: false
                }
            ).done(function (data) {
                // console.log(data);
            }).responseJSON['category_name'];
        },

        /**
         * Defines if value has changed.
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            let notEqual = this.value() !== this.initialValue;
            if (notEqual) {
                this.loadFeedCategoryName(this.value());
            }

            return !this.visible() ? false : notEqual;
        },

        /**
         * Change feed category name after update
         */
        onUpdate: function() {
            this._super();
            $('#'  + this.uid + '_feed_category').html(this.selectedFeedCategory);
        },
    });
});
