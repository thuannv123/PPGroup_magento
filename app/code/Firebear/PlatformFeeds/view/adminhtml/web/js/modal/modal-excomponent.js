/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define([
    'jquery',
    'Firebear_ImportExport/js/modal/modal-excomponent',
    'mage/storage',
    'uiRegistry',
    'mage/translate'
], function ($, Parent, storage, reg, $t) {
    'use strict';

    return Parent.extend({

        /**
         * Preview mode. If it's set to true then export will run for the limited qty of items
         */
        preview: false,

        /**
         * init preview mode
         *
         * @returns {*}
         */
        actionPreview: function () {
            this.preview = true;
            return this.actionRun(this.preview);
        },

        /**
         * Force reset "preview" flag to false
         *
         * @returns {*}
         */
        actionRun: function(preview) {
            this.preview = preview || false;
            return this._super();
        },

        /**
         * Send ajax request job/run. Added check of the flag "preview" for running in preview mode
         *
         * @param file
         */
        ajaxSend: function (file) {
            this.end = 0;

            var lastEntityValue = '';
            var job = reg.get(this.job).data.entity_id;
            var lastEntity = reg.get(this.ns + '.' + this.ns + '.settings.last_entity_id');

            if (localStorage.getItem('jobId')) {
                job = localStorage.getItem('jobId');
            }

            var object = reg.get(this.name + '.debugger.debug');
            var url = this.url + '?form_key=' + window.FORM_KEY;
            url += '&id=' + job + '&file=' + file + '&last_entity_value=' + lastEntityValue;
            if (this.preview) {
                url += '&preview=1';
            }

            var page = this.page + 1;
            this.page = page;
            url = url + '&page=' + page;

            this.currentAjax = this.urlAjax + '?file=' + file;
            if (lastEntity.value()) {
                lastEntityValue = lastEntity.value();
                url = url + '&last_entity_id=' + lastEntityValue;
                this.currentAjax = this.currentAjax + '&last_entity_id=' + lastEntityValue;
            }

            $('.run').attr('disabled', true);
            var urlAjax = this.currentAjax;
            var self = this;

            this.loading(true);
            storage.get(
                url
            ).done(
                function (response) {
                    var entity = reg.get(self.ns + '.' + self.ns + '.settings.entity');
                    if (entity.value() == 'catalog_product' && response.export_by_page) {
                        self.ajaxSend(file);
                    } else {
                        object.value(response.result);
                        $('.run').attr('disabled', false);
                        self.loading(false);
                        self.isNotice(response.result);
                        self.notice($t('The process is over'));
                        self.isError(!response.result);

                        if (response.file) {
                            self.isHref(response.result);
                            self.href(response.file);
                            if (lastEntity.value() < response.last_entity_id) {
                                lastEntity.value(response.last_entity_id);
                            }
                        }

                        self.end = 1;
                        self.page = 0;
                    }
                }
            ).fail(
                function (response) {
                    $('.run').attr('disabled', false);

                    self.loading(false);
                    self.isNotice(false);
                    self.isError(true);
                    self.end = 1;
                    self.page = 0;
                }
            );

            if ((self.page == 1) && (self.end != 1)) {
                setTimeout(self.getDebug.bind(self, urlAjax), 1500);
            }
        }
    });
});
