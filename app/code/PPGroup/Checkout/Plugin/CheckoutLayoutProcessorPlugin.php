<?php

namespace PPGroup\Checkout\Plugin;

class CheckoutLayoutProcessorPlugin
{
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
    {
        $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress'];
        $paymentConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment'];

        // Modify field sort order
        $shippingConfig['children']['shipping-address-fieldset']['children']['region_id']['sortOrder'] = 70;
        $paymentConfig['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['region_id']['sortOrder'] = 70;

        // Hide country field, but keeps default value = 'TH'
        $shippingConfig['children']['shipping-address-fieldset']['children']['country_id']['visible'] = false;
        $paymentConfig['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['country_id']['visible'] = false;

        // Add phone field validation
        $shippingConfig['children']['shipping-address-fieldset']['children']['telephone']['validation']['validate-number'] = true;
        $paymentConfig['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['telephone']['validation']['validate-number]'] = true;

        // Remove tooltip
        $shippingConfig['children']['shipping-address-fieldset']['children']['telephone']['config']['tooltip'] = false;
        $paymentConfig['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['telephone']['config']['tooltip'] = false;

        return $jsLayout;
    }
}
