define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Amasty_SocialLogin/js/am-popup',
    'mage/utils/wrapper'
], function ($, modal, amPopup, wrapper) {
    'use strict';

    return function (amPopupFunction) {

        return wrapper.wrap(amPopupFunction, {
            modalWindow: null,

            /**
             * Create popUp window for provided element
             *
             * @param {HTMLElement} element
             */
            createPopUp: function (element) {
                var options = {
                    'type': 'popup',
                    'modalClass': 'popup-authentication',
                    'focus': '[name=username]',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '.proceed-to-checkout',
                    'buttons': []
                };

                this.modalWindow = element;
                modal(options, $(this.modalWindow));
            },

            /** Show Amasty Social login popup window */
            showModal: function () {
                if ($(amPopup.prototype.options.selectors.popup).length) {
                    amPopup.prototype.openPopup(0);
                } else {
                    $(this.modalWindow).modal('openModal').trigger('contentUpdated');
                }
            }
        });
    };
});
