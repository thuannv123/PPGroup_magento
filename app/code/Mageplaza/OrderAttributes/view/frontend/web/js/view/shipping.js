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
    'ko',
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Mageplaza_OrderAttributes/js/model/order-attributes-data',
    'mage/translate'
], function (ko, $, _, registry, addressList, quote, oaData, $t) {
    'use strict';

    return function (Component) {
        var attributeDepend      = window.checkoutConfig.mpOaConfig ? window.checkoutConfig.mpOaConfig.attributeDepend : [],
            shippingDepend       = window.checkoutConfig.mpOaConfig ? window.checkoutConfig.mpOaConfig.shippingDepend : [],
            countryDepend        = window.checkoutConfig.mpOaConfig ? window.checkoutConfig.mpOaConfig.countryDepend : [],
            isOscPage            = window.checkoutConfig.mpOaConfig ? window.checkoutConfig.mpOaConfig.isOscPage : [],
            availableCustomSteps = window.checkoutConfig.totalsData.mp_orderattributes_steps ? window.checkoutConfig.totalsData.mp_orderattributes_steps : [],
            textareaComponent    = 'Mageplaza_OrderAttributes/js/form/element/textarea',
            checkboxComponent    = 'Mageplaza_OrderAttributes/js/form/element/checkboxes',
            multiselectComponent = 'Magento_Ui/js/form/element/multiselect',
            fieldset             = [
                '',
                'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.mpOrderAttributes',
                'checkout.steps.shipping-step.shippingAddress.before-shipping-method-form.mpOrderAttributes',
                'checkout.steps.shipping-step.shippingAddress.mpOrderAttributes',
                'checkout.steps.billing-step.payment.beforeMethods.mpOrderAttributes',
                'checkout.steps.billing-step.payment.afterMethods.mpOrderAttributes',
                'checkout.sidebar.summary.itemsAfter.mpOrderAttributes'
            ],
            scopes               = [
                '',
                'mpShippingAddressAttributes',
                'mpShippingMethodTopAttributes',
                'mpShippingMethodBottomAttributes'
            ];
        availableCustomSteps.forEach(function (stepCode) {
            fieldset.push('checkout.steps.' + stepCode + '.mpOrderAttributes');
        });
        if (addressList().length) {
            fieldset[1] = 'checkout.steps.shipping-step.shippingAddress.before-form.mpOrderAttributes';
            scopes[1]   = 'mpShippingAddressNewAttributes';
        }

        if (isOscPage) {
            fieldset[6] = 'checkout.sidebar.place-order-information-left.addition-information.mpOrderAttributes';
        }

        function checkShippingDepend (method) {
            $.each(shippingDepend, function (index, attribute) {
                $.each(fieldset, function (key, value) {
                    registry.async(value)(function (container) {
                        if (!container) {
                            return true;
                        }

                        $.each(container._elems, function (key, value) {
                            registry.async(value)(function (elem) {
                                if (!elem) {
                                    return true;
                                }

                                if (isDependOnShipping(elem) && attribute.attribute_code === elem.index) {
                                    var content      = typeof tinymce !== 'undefined' ? tinymce.get(elem.uid) : null;
                                    var dependMethod = attribute.shipping_depend.split(',');

                                    if ($.inArray(method, dependMethod) === -1 || !isAttributeDepend(elem, attribute) || !isCountryDepend(elem, attribute)) {
                                        hideElement(elem, content);
                                    } else {
                                        showElement(elem, content);
                                    }
                                }
                            })
                        })
                    });
                });
            });
        }

        function checkCountryDepend (country) {
            $.each(countryDepend, function (index, attribute) {
                $.each(fieldset, function (key, value) {
                    registry.async(value)(function (container) {
                        if (!container) {
                            return true;
                        }

                        $.each(container._elems, function (key, value) {
                            registry.async(value)(function (elem) {
                                if (!elem) {
                                    return true;
                                }

                                if (isDependOnCountry(elem) && attribute.attribute_code === elem.index) {
                                    var content       = typeof tinymce !== 'undefined' ? tinymce.get(elem.uid) : null;
                                    var dependCountry = attribute.country_depend.split(',');

                                    if ($.inArray(country, dependCountry) === -1 || !isAttributeDepend(elem, attribute) || !isShippingDepend(elem, attribute)) {
                                        hideElement(elem, content)
                                    } else {
                                        showElement(elem, content);
                                    }
                                }
                            })
                        })
                    });
                });
            });
        }

        function hideElement (elem, content) {
            elem.visible(false);

            if (elem.component === multiselectComponent || elem.component === checkboxComponent) {
                elem.value([null])
            } else {
                elem.value(undefined);
            }

            elem.disabled(true);
            if (elem.component === textareaComponent && content !== null) {
                content.setContent('');
            }
            elem.reset();
        }

        function showElement (elem, content) {
            elem.visible(true);
            if (!elem.value() || !elem.value().length) {
                elem.value(
                    elem.component === checkboxComponent
                    || elem.component === multiselectComponent
                        ? [elem.default] : elem.default
                );
            }
            if (elem.component === textareaComponent && content !== null) {
                var attributes = getOaAttributes();

                if (_.has(attributes, elem.index)) {
                    content.setContent(attributes[elem.index]);
                } else {
                    content.setContent('');
                }
            }
            elem.disabled(false);
        }

        function getOaAttributes () {
            var dataArray  = _.values(oaData.getData()),
                attributes = {};

            _.each(dataArray, function (data) {
                _.extend(attributes, data);
            });

            return attributes;
        }

        function isDependOnShipping (elem) {
            var result = false;

            $.each(shippingDepend, function (index, attribute) {
                if (attribute.attribute_code === elem.index) {
                    result = true;
                    return false;
                }
            });

            return result;
        }

        function isDependOnCountry (elem) {
            var result = false;

            $.each(countryDepend, function (index, attribute) {
                if (attribute.attribute_code === elem.index) {
                    result = true;
                    return false;
                }
            });

            return result;
        }

        function isShippingDepend (elem, attribute) {
            if(!attribute.shipping_depend) {
                return true;
            }
            var method = quote.shippingMethod();
            if (method) {
                method = method.carrier_code + '_' + method.method_code;
            }
            var dependMethod = attribute.shipping_depend.split(',');


            return ($.inArray(method, dependMethod) !== -1);
        }

        function isCountryDepend (elem, attribute) {
            if (!attribute.country_depend) {
                return true;
            }
            var countryId;
            if (quote.shippingAddress()) {
                countryId = quote.shippingAddress().countryId;
            }
            var dependCountry = attribute.country_depend.split(',');

            return ($.inArray(countryId, dependCountry) !== -1);
        }

        function isAttributeDepend (elem, attribute) {
            if (attribute.field_depend === "0" || !attribute.value_depend) {
                return true;
            }
            var result = false;
            var parentElem = getAttributeById(attribute.field_depend);
            if (parentElem) {
                var dependValue = attribute.value_depend.split(',');
                result          = ($.inArray(attribute.field_depend + '_' + parentElem.value(), dependValue) !== -1);
            }


            return result;
        }

        function getAttributeById (id) {
            var result = false;

            $.each(attributeDepend, function (index, attribute) {
                if (attribute.attribute_id === id) {
                    result = registry.get(fieldset[attribute.position] + '.' + attribute.attribute_code);
                    return false;
                }
            });

            return result;
        }

        function getSelectedShippingMethod () {
            var method = window.checkoutConfig.selectedShippingMethod;

            if (method) {
                method = method.carrier_code + '_' + method.method_code;
            } else if (window.checkoutConfig.selectedShippingRate) {
                method = window.checkoutConfig.selectedShippingRate;
            }

            return method;
        }

        function getSelectedCountryId () {
            var countryId;

            if (quote.shippingAddress()) {
                countryId = quote.shippingAddress().countryId;
            }

            return countryId;

        }

        checkShippingDepend(getSelectedShippingMethod());

        checkCountryDepend(getSelectedCountryId());

        return Component.extend({
            initObservable: function () {
                this._super();

                quote.shippingMethod.subscribe(function (method) {
                    var shippingMethod;
                    if (method && method.carrier_code && method.method_code) {
                        shippingMethod = method.carrier_code + '_' + method.method_code;
                    }
                    checkShippingDepend(shippingMethod);
                });

                quote.shippingAddress.subscribe(function (address) {
                    var countryId;
                    if (address) {
                        countryId = address.countryId;
                    }
                    clearTimeout(self.checkCountryTimeout);
                    self.checkCountryTimeout = setTimeout(function () {
                        checkCountryDepend(countryId);
                    }, 100);
                });

                return this;
            },

            validateShippingInformation: function () {
                var source = registry.get('mpOrderAttributesCheckoutProvider'),
                    result = true;

                if (!quote.isVirtual() && !quote.shippingMethod()) {
                    this.errorValidationMessage(
                        $t('The shipping method is missing. Select the shipping method and try again.')
                    );

                    return false;
                }

                _.each(scopes, function (scope) {
                    if (scope && source.get(scope)) {
                        source.set('params.invalid', false);
                        source.trigger(scope + '.data.validate');
                        if (source.get('params.invalid')) {
                            result = false;
                        }
                    }
                });

                return this._super() && result;
            }
        });
    }
});
