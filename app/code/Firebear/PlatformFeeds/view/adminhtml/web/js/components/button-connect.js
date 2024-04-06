/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
    'jquery'
], function (Element, registry, jQuery) {
    'use strict';

    return Element.extend({
        action: function () {
            let data = {
                id: this.source.data.id,
                type_id: registry.get(this.parentName + '.type_id').value(),
                token: registry.get(this.parentName + '.token').value(),
                login: registry.get(this.parentName + '.login').value(),
                password: registry.get(this.parentName + '.password').value()
            };

            let self = this;
            jQuery.ajax(
                {
                    type: "POST",
                    data: data,
                    showLoader: true,
                    url: self.loadCategoryUrl,
                    dataType: "json",
                    success: function (result, status) {
                        // console.log(result);
                    },
                    error: function () {

                    }
                }
            );

            if (data.type_id && data.id) {
                location.reload();
            }
        }
    });
});
