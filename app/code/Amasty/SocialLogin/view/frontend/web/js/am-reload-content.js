define([
    "jquery",
    "Magento_Customer/js/customer-data",
    "Magento_PageCache/js/page-cache",
    'Amasty_SocialLogin/js/am-reload-reviews-form',
    'Magento_Customer/js/section-config',
    'mage/url'
], function ($, customerData, pageCache, reloadReviewsForm, sectionConfig, urlBuilder) {
    'use strict';

    var amReloadContent = {
        ajaxReloadHeader: function (data) {
            var header = $('.header.links'),
                qty = null;

            header.find('li').each(function (index, element) {
                if (!$(element).hasClass('welcome')) {
                    element.remove();
                }
            });

            $(data.content).children().each(function (index, element) {
                header.first().append(element);
            });
            header.trigger('contentUpdated');

            qty = header.find('.wishlist .qty');
            if (qty.length && !qty.html()) {
                qty.remove();
            }

            $.mage.formKey();
            $("[data-am-js='am-login-popup']").amLoginPopup('init');
        },

        updateCustomerData: function () {
            customerData.invalidate(sectionConfig.getSectionNames());
            customerData.reload(sectionConfig.getSectionNames(), true);
        },

        headerUpdate: function () {
            var self = this;
            urlBuilder.setBaseUrl(window.BASE_URL);

            $.ajax({
                url:  urlBuilder.build('amsociallogin/header/update'),
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        self.ajaxReloadHeader(response);
                    } else {
                        setTimeout(function () {
                            window.location.reload(true);
                        }, self.options.redirect_duration);
                    }
                }
            });
        },

        customRedirect: function (data) {
            var redirect = +data.redirect;

            if (redirect === 0 && this._isShouldReloadPage()) {
                redirect = 2;//reload page
            }

            switch (redirect) {
                case 0:
                    this.headerUpdate();
                    reloadReviewsForm.prototype.refreshReviewForm();
                    $(document.body).trigger('scroll-after-ajax-update');
                    break;
                case 1:
                    setTimeout(function() {
                        window.location.href = data.url;
                    }, 4000);
                    break;
                case 2:
                default:
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 4000);
            }

            this.updateCustomerData();
        },

        _isShouldReloadPage: function () {
            var url = window.location.pathname,
                body = $('body'),
                shouldReload = url === "/customer/account/create"
                || url === "/customer/account/create/"
                || url === "/customer/account/login"
                || url === "/customer/account/login/",
                isCheckoutPage = body.hasClass('checkout-index-index');

            shouldReload = shouldReload || isCheckoutPage;

            return shouldReload;
        }
    };

    return amReloadContent;
});
