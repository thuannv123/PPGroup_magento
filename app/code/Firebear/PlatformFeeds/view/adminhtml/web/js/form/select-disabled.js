/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'Magento_Ui/js/form/element/select'
    ],
    function (Element) {
        'use strict';

        return Element.extend(
            {
                defaults: {
                    imports          : {
                        toggleDisabled: ''
                    },
                    disabled: false,
                },

                toggleDisabled: function (mappingId) {
                    if (parseInt(mappingId) >= 0) {
                        this.disabled = true;
                    }
                },

                setInitialValue: function () {
                    this.initialValue = this.getInitialValue();

                    if (this.value.peek() !== this.initialValue) {
                        this.value(this.initialValue);
                    }

                    this.on('value', this.onUpdate.bind(this));

                    return this;
                },

                getMappingId: function () {
                    return this.source.data.id ? this.source.data.id : '';
                },

                validate: function () {
                    if (!(parseInt(this.getMappingId()) >= 0)) {
                        this._super();
                    }
                },
            }
        );
    }
);
