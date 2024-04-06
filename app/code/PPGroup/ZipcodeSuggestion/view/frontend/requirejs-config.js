var config = {
    config: {
        map: {
            "*": {
                validationZipcodeSuggestion: 'PPGroup_ZipcodeSuggestion/js/validation'
            }
        },
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'PPGroup_ZipcodeSuggestion/js/validation-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'PPGroup_ZipcodeSuggestion/js/view/default-mixin': true
            },
        }
    }
};
