define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "mage/cookies",
    "domReady!"
], function ($, modal) {
    "use strict";

    $.widget('ppgroup.popupNewsletter', {

        /** @inheritdoc */
        _create: function () {
            this.searchForm = $(this.options.formSelector);
            this.cookieName = this.options.cookieName;

            this.searchForm.on('submit', $.proxy(function () {
                if (this.validateEmail()) {
                    this._setCookie(this.cookieName);
                }
            }, this));
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                delay = this.options.delay,
                isSubscribed = parseInt(this.options.isSubscribed) === 1,
                time = this._getDelay(delay),
                options = {
                    type: 'popup',
                    innerScroll: true,
                    modalClass: 'newsletter-modal popup-center',
                    buttons: '',
                    closed: function (e, modal) {
                        modal.modal.remove();
                        $widget._setCookie($widget.cookieName);
                    }
                };

            if (this._isCookieSet($widget.cookieName) !== true || isSubscribed) {
                this._logTime(time, function () {
                    $widget._openModal(options);
                });
            }

        },

        /**
         * Open Modal
         *
         * @param options
         * @private
         */
        _openModal: function (options) {
            var html = this.element,
                popup = modal(options, html);

            popup.openModal();
        },


        /**
         * Validate input email
         */
        validateEmail: function () {
            $(this.options.formSelector).validation();
            return $(this.options.formSelector + ' input[type=email]').valid();
        },

        /**
         * Return the remaining time
         * for the modal opening
         *
         * @param delay
         * @returns {*}
         * @private
         */
        _getDelay: function (delay) {
            let cookie = $.mage.cookies.get('popup-timing');
            if (cookie > 0) {
                return delay - cookie
            } else {
                return delay
            }
        },

        /**
         * Set remaining time cookie
         *
         * @param i
         * @param callback
         * @private
         */
        _logTime: function (i, callback) {

            callback = callback || function () {
            };
            let int = setInterval(function () {
                $.mage.cookies.set('popup-timing', i);
                i-- || (clearInterval(int), callback());
            }, 1000);
        },

        /**
         * Set cookie
         *
         * @param cookie
         * @private
         */
        _setCookie: function (cookie) {
            $.mage.cookies.set(cookie, 'yes',
                {lifetime: 342342342342});
        },

        /**
         * Check if cookie is set
         *
         * @param cookie
         * @returns {boolean}
         * @private
         */
        _isCookieSet: function (cookie) {
            if ($.mage.cookies.get(cookie) === 'yes') {
                return true;
            }
        }

    });

    return $.ppgroup.popupNewsletter;
});
