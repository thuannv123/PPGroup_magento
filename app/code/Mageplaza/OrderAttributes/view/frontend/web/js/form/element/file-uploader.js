/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'Magento_Ui/js/form/element/file-uploader',
    'Mageplaza_OrderAttributes/js/model/resource-url-manager'
], function (Component, resourceUrl) {
    'use strict';

    return Component.extend({
        defaults: {
            previewTmpl: 'Mageplaza_OrderAttributes/form/element/uploader/preview',
            uploaderConfig: {
                url: resourceUrl.getUploadUrl()
            },
            maxFileSize: 2000000
        },

        /**
         * May perform modifications on the provided
         * file object before adding it to the files list.
         *
         * @param {Object} file
         * @returns {Object} Modified file object.
         */
        processFile: function (file) {
            file.previewType = this.getFilePreviewType(file);

            this.observe.call(file, true, [
                'previewWidth',
                'previewHeight'
            ]);

            return file;
        },

        /**
         * Get simplified file type.
         *
         * @param {Object} file - File to be checked.
         * @returns {String}
         */
        getFilePreviewType: function (file) {
            var type;

            if (!file.type) {
                return 'document';
            }

            type = file.type.split('/')[0];

            return type !== 'image' && type !== 'video' ? 'document' : type;
        }
    });
});
