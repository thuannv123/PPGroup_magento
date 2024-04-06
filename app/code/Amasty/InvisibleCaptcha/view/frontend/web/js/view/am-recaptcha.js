define([
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'Amasty_InvisibleCaptcha/js/view/am-recaptcha-abstract',
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha',
    'mage/loader',
    'domReady!'
], function (
    $,
    ko,
    _,
    utils,
    Component,
    amReCaptchaModel
) {
    'use strict';

    return Component.extend({
        defaults: {
            formsToProtect: '',
            captchaElementClass: 'am-recaptcha-block'
        },

        /**
         * @inheritDoc
         * @returns {Object}
         */
        initObservable: function () {
            this._super();

            this.formsToProtect = $(amReCaptchaModel.getFormsList());
            this.renderCaptcha();

            return this;
        },

        /**
         * @returns {void}
         */
        renderCaptcha: function () {
            $(window).on('amcaptchaReady', this.initFormHandler.bind(this));

            // eslint-disable-next-line consistent-return
            _.debounce(function () {
                this._addListeners();

                this.formsToProtect.on('submit', function (event) {
                    var form = $(event.currentTarget);

                    window.dispatchEvent(new CustomEvent('am-recaptcha-submit-event', {
                        detail: {
                            form: form
                        }
                    }));

                    if (amReCaptchaModel.isScriptLoaded) {
                        form.off('submit:beforeSubmit');

                        return;
                    }

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    form.trigger('submit:beforeSubmit');
                });

                this._eventOrderChange();
                this._enableSubmitButtons();
            }.bind(this), 200)();
        },

        _enableSubmitButtons: function (forms) {
            if (typeof forms === 'undefined') {
                forms = this.formsToProtect;
            }

            forms.find('[am-captcha-protect=true]').removeAttr('disabled');
        },

        _addListeners: function () {
            this.formsToProtect.on('submit:beforeSubmit', function (event) {
                this.cachedForm = $(event.target);
                if (amReCaptchaModel.isScriptLoaded) {
                    $(window).trigger('amcaptchaReady');
                    return;
                }

                this.loadApi();
            }.bind(this));
        },

        /**
         * @private
         * @returns {void}
         */
        _eventOrderChange: function () {
            _.each(this.formsToProtect, function (form) {
                var $form = $(form);

                $form.data('recaptchaFormId', utils.uniqueid());

                if (+amReCaptchaModel.invisibleCaptchaCustomForm) {
                    this._swapSubmit($form);
                }
            }.bind(this));
        },

        /**
         * @param {Element} form
         * @private
         * @returns {void}
         */
        _swapSubmit: function (form) {
            var $form = $(form),
                listeners;

            listeners = $._data($form[0], 'events').submit;
            if (listeners) {
                listeners.unshift(listeners.pop());
            }
        },

        /**
         * @param {Element} form
         * @returns {Object}
         */
        getParameters: function (form) {
            var $form = $(form);

            return _.extend(amReCaptchaModel.getRecaptchaConfig(), {
                'callback': function () {
                    if (this.showLoaderOnCaptchaLoading) {
                        $('body').trigger('processStop');
                    }

                    if ($form.valid()) {
                        $form.submit();
                    }
                }.bind(this),
                'expired-callback': this.resetCaptcha
            });
        },

        /**
         * @returns {void}
         */
        initFormHandler: function () {
            var self = this;

            amReCaptchaModel.isScriptLoaded = true;
            this.appendCaptcha();
            _.each(self.formsToProtect, function (form) {
                if (self.cachedForm && self.cachedForm[0] === form) {
                    var $form = $(form),
                        widgetId = self._initCaptchaOnForm(form);

                    $form.on('ajaxFormLoaded', function () {
                        self._formButtonClickEvent(form, widgetId);
                    });

                    $(document).on('am_form:ajax_complete', function (event, form) {
                        self.renderCaptchaOnForm($(form));
                    });
                }
            });
        },

        /**
         * Render captcha element on form
         *
         * @param {jQuery} $form
         * @param {jQuery|null} $captchaElement
         * @returns {Number}
         */
        renderCaptchaOnForm: function ($form, $captchaElement = null) {
            var $formCaptchaElement = $form.find('.' + this.captchaElementClass);

            if ($formCaptchaElement.length) {
                $formCaptchaElement.remove();
            }

            if (!$captchaElement) {
                $captchaElement = this.getCaptchaElement();
            }

            $form.append($captchaElement);

            var widgetId = window.grecaptcha.render($captchaElement[0], this.getParameters($form[0]));

            $captchaElement.data('id', widgetId);
            this._formButtonClickEvent($form[0], widgetId);

            this._enableSubmitButtons($form);

            return widgetId;
        },

        /**
         * Get captcha element
         *
         * @returns {jQuery}
         */
        getCaptchaElement: function () {
            return $('<div class="' + this.captchaElementClass + '"></div>');
        },

        /**
         * Init captcha on form
         * @param {Element} form
         * @returns {String|Number}
         */
        _initCaptchaOnForm: function (form) {
            var $form = $(form),
                widgetId,
                $button = $form.find("[type='submit']"),
                $captchaElement = this.getCaptchaElement();

            widgetId = this.renderCaptchaOnForm($form, $captchaElement);

            this._submitCachedForm($form, $button);

            amReCaptchaModel.tokenFields.push($captchaElement);

            return widgetId;
        },

        /**
         *
         * @param {Element} $form
         * @param {Element} $button
         * @private
         * @returns {void}
         */
        _submitCachedForm: function ($form, $button) {
            if ($button.length && this.cachedForm
                && this.cachedForm.data('recaptchaFormId') === $form.data('recaptchaFormId')) {
                if (this.showLoaderOnCaptchaLoading) {
                    $('body').trigger('processStart');
                }

                $button.trigger('click');
                this.cachedForm = null;
            }
        },

        /**
         * Add Event to execute recaptcha widget on button click
         * @private
         * @param {Element} form
         * @param {Number} widgetId
         * @returns {void}
         */
        _formButtonClickEvent: function (form, widgetId) {
            var $form = $(form),
                $button = $form.find("[type='submit']"),
                buttonClickListeners = null,
                buttonListeners = {};

            if ($button.length) {
                buttonListeners = $._data($button[0], 'events');
            }

            if (_.has(buttonListeners, 'click')) {
                buttonClickListeners = _.clone(buttonListeners.click);
            }

            $button.off('click').on('click', function (e) {
                e.preventDefault();

                if ($form.valid()) {
                    $button.prop('disabled', true);
                    window.grecaptcha.reset(widgetId);
                    if (amReCaptchaModel.getRecaptchaConfig().isInvisible) {
                        window.grecaptcha.execute(widgetId);
                    }
                }

                if (buttonClickListeners) {
                    $button.off('click');
                    $.each(buttonClickListeners, function (index, event) {
                        $button.bind(event.type, event.handler);
                    });

                    buttonClickListeners = null;
                }
            });

            this._enableSubmitButtons($form);
        }
    });
});
