define([
    'jquery',
    'mage/url',
    'Magento_PageCache/js/page-cache',
    'Magento_Ui/js/lib/view/utils/bindings',
], function ($, urlBuilder) {
    'use strict';

    $.widget('mage.amSocialLoginReloadReviewsForm', {
        loginProcessStarted: false,

        options: {
            baseUrl: window.BASE_URL,
            backendUrl: 'amsociallogin/ajax/renderReviewsForm/',
            detectProductConfig: [
                {selector: '.price-container [id^="product-price-"]', attribute: 'id'},
                {selector: '[data-price-box^="product-id-"]', attribute: 'data-price-box'},
                {selector: '[data-product-id]', attribute: 'data-product-id'},
                {selector: '[name="product"]', attribute: 'value'},
                {selector: '#review-form', attribute: 'action', regex: /\/(\d+)\/$/g},
            ],
            selectors: {
                reviewsButton: '[data-amreview-js="amreview-toform"]',
                reviewsFormSelector: '.review-add'
            }
        },

        /**
         * @return {void}
         */
        _create: function () {
            urlBuilder.setBaseUrl(this.options.baseUrl);
        },

        /**
         * @return {void}
         */
        refreshReviewForm: function () {
            var reviewsForm = $(this.options.selectors.reviewsFormSelector);
            if (reviewsForm.length) {
                this.ajaxLoadForm();
                $(this.options.selectors.reviewsButton).unbind( "click" ).attr('href', '#review-form');
            }
        },

        /**
         * @return {void}
         */
        ajaxLoadForm: function () {
            $.getJSON(
                urlBuilder.build(this.options.backendUrl),
                {id: this.getProductId()},
                this.reloadForm.bind(this)
            );
        },

        /**
         * @param {object} data
         */
        reloadForm: function (data) {
            var reviewsForm = $(this.options.selectors.reviewsFormSelector),
                updatedForm;

            if (data.form) {
                updatedForm = $('<div>').append(data.form);
                reviewsForm.replaceWith(updatedForm);
                this.reviewsFormAppendAfter(updatedForm);
            }
        },

        /**
         * @param {jQuery} formObject
         */
        reviewsFormAppendAfter: function (formObject) {
            formObject.formKey();
            formObject.trigger('contentUpdated');
            formObject.applyBindings();
        },

        /**
         * @return {number|NaN}
         */
        getProductId: function () {
            var result = NaN,
                config = this.options.detectProductConfig,
                selector,
                attribute,
                regex,
                element,
                attributeValue,
                productIdParsingResult;

            for (var i = 0; i < config.length; ++i) {
                selector = config[i].selector;
                attribute = config[i].attribute;
                regex = config[i].regex || /\d+$/;
                element = $(selector);

                if (element.length) {
                    attributeValue = element.attr(attribute);
                    productIdParsingResult = regex.exec(attributeValue);

                    if (productIdParsingResult !== null) {
                        result = Number(productIdParsingResult[1] || productIdParsingResult[0]);
                        break;
                    }
                }
            }

            return result;
        }
    });

    return $.mage.amSocialLoginReloadReviewsForm;
});
