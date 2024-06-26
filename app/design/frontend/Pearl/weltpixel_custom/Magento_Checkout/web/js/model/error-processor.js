/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function (url, globalMessageList, $t) {
    'use strict';

    return {
        /**
         * @param {Object} response
         * @param {Object} messageContainer
         */
        process: function (response, messageContainer) {
            var error;

            messageContainer = messageContainer || globalMessageList;
            if (response.status == 401) { //eslint-disable-line eqeqeq
                window.location.replace(url.build('customer/account/login/'));
            } else {
                try {
                    if (response.responseText !== undefined){
                        error = JSON.parse(response.responseText);
                        messageContainer.addErrorMessage(error);
                    }
                } catch (exception) {
                    error = {
                        message: $t('Something went wrong with your request. Please try again later.')
                    };
                    messageContainer.addErrorMessage(error);
                }
            }
        }
    };
});
