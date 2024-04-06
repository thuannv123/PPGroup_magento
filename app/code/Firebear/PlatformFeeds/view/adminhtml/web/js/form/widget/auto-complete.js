/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define([
    'underscore',
    'jquery',
    'mage/template',
    'text!Firebear_PlatformFeeds/template/form/element/result-feed-auto-complete.html'
], function (_, $, mageTpl, autoCompTpl) {
    'use strict';

    $.widget('mage.feedsAutoComplete', {

        options: {
            url: '',
            delay: 1000,
            amountCharacters: 3,
            input: '[data-action="input-text"]',
            btn: '[data-action="done-select"]',
            searchBlock: '[data-role="search-block"]',
            labelBlock: '[data-action="open-search"]',
            selectValue: '[data-action="select"]',
            hiddenInput: '[data-role="hidden-input"]',
            data: {},
            value: '',
            currentVal: '',
            translation: {
                noResults: $.mage.__('No results'),
                options: $.mage.__('items')
            }
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            this.setElements();
            this.autoCompBlock = mageTpl(autoCompTpl);
            this.bind();
        },

        bind: function () {
            let handlers = {};
            handlers['click ' + this.options.labelBlock] = 'showSearch';
            handlers['keyup ' + this.options.input] = 'proceedRequest';
            handlers['click ' + this.options.selectValue] = 'selectValue';
            handlers.click = 'catchEvent';

            $(document).on('click', this.hideSearch.bind(this));
            this._on(handlers);
        },

        showSearch: function () {
            this.blocks.searchBlock.slideToggle(0);
        },

        hideSearch: function () {
            this.removeResult();
            this.blocks.searchBlock.hide();
            this.blocks.inputBlock.val('');
        },

        catchEvent: function (e) {
            e.stopPropagation();
        },

        selectValue: function (e) {
            let $listEl = $(e.target);

            this.options.value = $listEl.data('name');
            this.options.id = $listEl.data('id');
            this.setValue();
        },

        setValue: function () {
            this.blocks.labelBlock.text(this.options.value);
            this.blocks.hiddenBlock.val(this.options.id);
            this.blocks.hiddenBlock.trigger('change');

            this.hideSearch();
        },

        renderResult: function () {
            this.removeResult();
            this.resultBlock = $(this.autoCompBlock({
                data: $.extend(this.options.data, this.options.translation)
            }));

            this.blocks.searchBlock.append(this.resultBlock[this.resultBlock.length - 1]);
            this.blocks.list = this.element.find(this.options.selectValue);
        },

        removeResult: function () {
            if (this.resultBlock) {
                this.resultBlock.remove();
            }
        },

        setElements: function () {
            this.blocks = {};
            this.blocks.inputBlock = this.element.find(this.options.input);
            this.blocks.labelBlock = this.element.find(this.options.labelBlock);
            this.blocks.searchBlock = this.element.find(this.options.searchBlock);
            this.blocks.hiddenBlock = this.element.find(this.options.hiddenInput);
        },

        proceedRequest: function (e) {
            this.clearDelay();

            if (e.target.value.length >= this.options.amountCharacters) {
                this.delayRequest();
            }
        },

        clearDelay: function () {
            clearTimeout(this.delay);
        },

        delayRequest: function () {
            this.delay = setTimeout(
                this.sendAjax.bind(this),
                this.options.delay
            );
        },

        sendAjax: function () {
            let sendData = {
                name_category: this.blocks.inputBlock.val(),
                entity_id: $('input[name~="id"]').val(),
                title: $('input[name~="title"]').val(),
                type_id: $('select[name~="type_id"]').val()
            };

            $.ajax({
                url: this.options.url,
                data: sendData,
                type: 'post',
                dataType: 'json',
                showLoader: true,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                processData: true,

                success: $.proxy(function (items) {
                    this.options.data.list = items;
                    this.renderResult();
                }, this)
            });
        }

    });

    return $.mage.feedsAutoComplete;
});
