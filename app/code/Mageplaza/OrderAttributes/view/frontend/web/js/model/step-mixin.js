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
    'mage/utils/wrapper',
    'jquery',
    'ko'
], function (wrapper, $, ko) {
    'use strict';

    return function (step) {
        step.mpOaSteps         = ko.observableArray();
        step.registermpOaSteps = wrapper.wrapSuper(step.registermpOaSteps, function (code, title, isVisible, navigate, sortOrder) {
            step.mpOaSteps.push({
                code: code,
                alias: code,
                title: title,
                isVisible: isVisible,
                navigate: navigate,
                sortOrder: sortOrder
            });
        });

        return step;
    };
});