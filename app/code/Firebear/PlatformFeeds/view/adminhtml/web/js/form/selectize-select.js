/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 * @link: https://github.com/selectize/selectize.js/tree/master/docs
 */

define(
    [
        'jquery',
        'Magento_Ui/js/form/element/select',
        'Firebear_PlatformFeeds/js/lib/selectize',
    ],
    function ($, Select, Selectize) {

        let selectizeConfig = {
            maxItems: 1,
            closeAfterSelect: true,
            placeholder: 'Select feed category mapping'
        };

        return Select.extend({
            el: null,

            isSelectizeReady: false,

            afterRender: function(element) {
                this.el = $(element);

                // Use delayed initialization of selectize to make it catch all the options which will be added later.
                this.el.on('mouseenter', $.proxy(this.initSelectize, this));
                setTimeout($.proxy(this.initSelectize, this), 3000);
            },

            initSelectize: function () {
                if (this.isSelectizeReady) {
                    return;
                }

                this.el.off('mouseenter');
                this.el.selectize(selectizeConfig);
            }
        });
    }
);
