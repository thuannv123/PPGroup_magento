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

var config = {
    map: {
        '*': {
            'Magento_Ui/js/lib/knockout/bindings/datepicker': 'Mageplaza_OrderAttributes/js/lib/knockout/bindings/datepicker'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Mageplaza_OrderAttributes/js/action/place-order-mixin': true
            },
            "Magento_Checkout/js/view/shipping": {
                "Mageplaza_OrderAttributes/js/view/shipping": true
            },
            "Magento_Checkout/js/action/set-payment-information": {
                "Mageplaza_OrderAttributes/js/action/set-payment-information-mixin": true
            },
            "Mageplaza_Osc/js/action/set-payment-method": {
                "Mageplaza_OrderAttributes/js/action/set-payment-method-mixin": true
            },
            "Magento_Checkout/js/view/billing-address": {
                "Mageplaza_OrderAttributes/js/view/billing-address-mixin": true
            },
            "Magento_Checkout/js/model/quote": {
                "Mageplaza_OrderAttributes/js/model/quote-mixin": true
            },
            "Magento_Checkout/js/model/step-navigator": {
                "Mageplaza_OrderAttributes/js/model/step-mixin": true
            }
        }
    }
};

if (window.isMagento244AndAbove) {
    config.map["*"]['jquery-ui-modules/slider'] = 'jquery/ui-modules/widgets/slider';
}
