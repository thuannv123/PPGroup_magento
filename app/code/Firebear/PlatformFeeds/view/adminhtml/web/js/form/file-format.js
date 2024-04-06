/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'Firebear_ImportExport/js/form/element/file-format',
        'jquery',
    ],
    function (Element, $) {
        'use strict';

        return Element.extend(
            {
                getEntityOptions: function() {
                    return $.extend(this._super(), {
                        'feeds_product': 'catalog_product'
                    });
                },

                onUpdate: function() {
                    this._super();
                    this.switchPreviewButton();
                },

                onAfterRender: function() {
                    this._super();
                    this.switchPreviewButton();
                },

                switchPreviewButton: function() {
                    let $select = $("select[name='behavior_field_file_format']");
                    let $button = $('.preview-btn');

                    if ($button.length && $select.length) {
                        setTimeout(function () {
                            let isVisible = $select.val() === 'feeds_product';
                            isVisible ? $button.show() : $button.hide();
                        }, 1000);
                    }
                }
            }
        );
    }
);
