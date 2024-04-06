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
    'jquery',
    'Magento_Ui/js/form/element/date',
    'jquery-ui-modules/datepicker'
], function ($, Component) {
    'use strict';

    var datepickerPrototype = $.datepicker.constructor.prototype,
        isFormatted           = false;

    $.extend(datepickerPrototype, {
        /* Set the date(s) directly. */
        _setDate: function (inst, date, noChange) {
            var clear     = !date,
                origMonth = inst.selectedMonth,
                origYear  = inst.selectedYear,
                newDate   = this._restrictMinMax(inst, this._determineDate(inst, date, new Date()));

            inst.selectedDay = inst.currentDay = newDate.getDate();
            inst.drawMonth   = inst.selectedMonth = inst.currentMonth = newDate.getMonth();
            inst.drawYear    = inst.selectedYear = inst.currentYear = newDate.getFullYear();
            if ((origMonth !== inst.selectedMonth || origYear !== inst.selectedYear) && !noChange) {
                this._notifyChange(inst);
            }
            this._adjustInstDate(inst);
            if (inst.input) {
                if (this._get(inst, 'isDate') && inst.settings.mpDateFormat) {
                    inst.settings.dateFormat = inst.settings.mpDateFormat;
                    if (!isFormatted) {
                        var minDate, maxDate;
                        minDate = inst.settings.minDate ? new Date(inst.settings.minDate.split(' ')[0]) : null;
                        minDate = minDate ? $.datepicker.formatDate($.datepicker._get(inst, 'mpDateFormat'), minDate, $.datepicker._getFormatConfig(inst)) : null;
                        maxDate = inst.settings.maxDate ? new Date(inst.settings.maxDate.split(' ')[0]) : null;
                        maxDate = maxDate ? $.datepicker.formatDate($.datepicker._get(inst, 'mpDateFormat'), maxDate, $.datepicker._getFormatConfig(inst)) : null;
                        inst.settings.minDate = minDate;
                        inst.settings.maxDate = maxDate;
                        isFormatted = true;
                    }
                }
                inst.input.val(clear ? "" : this._formatDate(inst));
            }
        }
    });

    return Component.extend({
        defaults: {
            inputDateFormat: 'MM/dd/y'
        }
    });
});
