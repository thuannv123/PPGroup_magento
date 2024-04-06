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
    'moment',
    'Magento_Ui/js/form/element/date',
    'jquery-ui-modules/timepicker',
    'jquery-ui-modules/datepicker',
    'jquery-ui-modules/slider'
], function ($, moment, Component) {
    'use strict';

    var timepickerPrototype = $.timepicker.constructor.prototype,
        datepickerPrototype = $.datepicker.constructor.prototype;

    $.extend(timepickerPrototype, {
        /*
         * update our input with the new date time..
         */
        _updateDateTime: function (dp_inst) {
            dp_inst           = this.inst || dp_inst;
            var dtTmp         = (dp_inst.currentYear > 0 ?
                new Date(dp_inst.currentYear, dp_inst.currentMonth, dp_inst.currentDay) :
                new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay)),
                dt            = $.datepicker._daylightSavingAdjust(dtTmp),
                dateFmt       = $.datepicker._get(dp_inst, 'dateFormat'),
                formatCfg     = $.datepicker._getFormatConfig(dp_inst),
                timeAvailable = dt !== null && this.timeDefined;

            this.formattedDate    = $.datepicker.formatDate(dateFmt, (dt === null ? new Date() : dt), formatCfg);
            var formattedDateTime = this.formattedDate;

            // if a slider was changed but datepicker doesn't have a value yet, set it
            if (dp_inst.lastVal === "") {
                dp_inst.currentYear  = dp_inst.selectedYear;
                dp_inst.currentMonth = dp_inst.selectedMonth;
                dp_inst.currentDay   = dp_inst.selectedDay;
            }

            if (this._defaults.timeOnly === true) {
                formattedDateTime = this.formattedTime;
            } else if (this._defaults.timeOnly !== true && (this._defaults.alwaysSetTime || timeAvailable)) {
                formattedDateTime += this._defaults.separator + this.formattedTime + this._defaults.timeSuffix;
            }

            this.formattedDateTime = formattedDateTime;

            if (!this._defaults.showTimepicker) {
                this.$input.val(this.formattedDate);
            } else if (this.$altInput && this._defaults.timeOnly === false && this._defaults.altFieldTimeOnly === true) {
                this.$altInput.val(this.formattedTime);
                this.$input.val(this.formattedDate);
            } else if (this.$altInput) {
                this.$input.val(formattedDateTime);
                var altFormattedDateTime = '',
                    altSeparator         = this._defaults.altSeparator ? this._defaults.altSeparator : this._defaults.separator,
                    altTimeSuffix        = this._defaults.altTimeSuffix ? this._defaults.altTimeSuffix : this._defaults.timeSuffix;

                if (!this._defaults.timeOnly) {
                    if (this._defaults.altFormat) {
                        altFormattedDateTime = $.datepicker.formatDate(this._defaults.altFormat, (dt === null ? new Date() : dt), formatCfg);
                    } else {
                        altFormattedDateTime = this.formattedDate;
                    }

                    if (altFormattedDateTime) {
                        altFormattedDateTime += altSeparator;
                    }
                }

                if (this._defaults.altTimeFormat) {
                    altFormattedDateTime += $.datepicker.formatTime(this._defaults.altTimeFormat, this, this._defaults) + altTimeSuffix;
                } else {
                    altFormattedDateTime += this.formattedTime + altTimeSuffix;
                }
                this.$altInput.val(altFormattedDateTime);
            } else {
                if ($.datepicker._get(dp_inst, 'isDateTime')
                    && ($.datepicker._get(dp_inst, 'mpDateFormat') || $.datepicker._get(dp_inst, 'mpTimeFormat'))) {
                    var date         = new Date(formattedDateTime),
                        mpDateFormat = $.datepicker._get(dp_inst, 'mpDateFormat'),
                        mpTimeFormat = $.datepicker._get(dp_inst, 'mpTimeFormat');

                    formattedDateTime = $.datepicker.formatDate(mpDateFormat, date, formatCfg)
                        + ' ' + $.datepicker.formatTime(mpTimeFormat, $.datepicker._get(dp_inst, 'timepicker'), {});
                }
                this.$input.val(formattedDateTime);
            }

            this.$input.trigger("change");
        },

        _limitMinMaxDateTime: function (dp_inst, adjustSliders) {
            var o       = this._defaults,
                dp_date = new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay);

            if (!this._defaults.showTimepicker) {
                return;
            }

            if (dp_inst.settings.timeOnly) {
                dp_inst.settings.minDateTime = new Date(dp_inst.settings.minDate);
                dp_inst.settings.maxDateTime = new Date(dp_inst.settings.maxDate);
            }

            if ($.datepicker._get(dp_inst, 'minDateTime') !== null && $.datepicker._get(dp_inst, 'minDateTime') !== undefined && dp_date) {
                var minDateTime     = $.datepicker._get(dp_inst, 'minDateTime'),
                    minDateTimeDate = new Date(minDateTime.getFullYear(), minDateTime.getMonth(), minDateTime.getDate(), 0, 0, 0, 0);

                if (this.hourMinOriginal === null || this.minuteMinOriginal === null || this.secondMinOriginal === null || this.millisecMinOriginal === null || this.microsecMinOriginal === null) {
                    this.hourMinOriginal     = o.hourMin;
                    this.minuteMinOriginal   = o.minuteMin;
                    this.secondMinOriginal   = o.secondMin;
                    this.millisecMinOriginal = o.millisecMin;
                    this.microsecMinOriginal = o.microsecMin;
                }

                if (dp_inst.settings.timeOnly || minDateTimeDate.getTime() === dp_date.getTime()) {
                    this._defaults.hourMin = minDateTime.getHours();
                    if (this.hour <= this._defaults.hourMin) {
                        this.hour                = this._defaults.hourMin;
                        this._defaults.minuteMin = minDateTime.getMinutes();
                        if (this.minute <= this._defaults.minuteMin) {
                            this.minute              = this._defaults.minuteMin;
                            this._defaults.secondMin = minDateTime.getSeconds();
                            if (this.second <= this._defaults.secondMin) {
                                this.second                = this._defaults.secondMin;
                                this._defaults.millisecMin = minDateTime.getMilliseconds();
                                if (this.millisec <= this._defaults.millisecMin) {
                                    this.millisec              = this._defaults.millisecMin;
                                    this._defaults.microsecMin = minDateTime.getMicroseconds();
                                } else {
                                    if (this.microsec < this._defaults.microsecMin) {
                                        this.microsec = this._defaults.microsecMin;
                                    }
                                    this._defaults.microsecMin = this.microsecMinOriginal;
                                }
                            } else {
                                this._defaults.millisecMin = this.millisecMinOriginal;
                                this._defaults.microsecMin = this.microsecMinOriginal;
                            }
                        } else {
                            this._defaults.secondMin   = this.secondMinOriginal;
                            this._defaults.millisecMin = this.millisecMinOriginal;
                            this._defaults.microsecMin = this.microsecMinOriginal;
                        }
                    } else {
                        this._defaults.minuteMin = this.minuteMinOriginal;
                        this._defaults.secondMin = this.secondMinOriginal;

                        if (dp_inst.settings.isTime) {
                            this._defaults.minuteMin = 0;
                            this._defaults.secondMin = 0;
                        }
                        this._defaults.millisecMin = this.millisecMinOriginal;
                        this._defaults.microsecMin = this.microsecMinOriginal;
                    }
                } else {
                    this._defaults.hourMin     = this.hourMinOriginal;
                    this._defaults.minuteMin   = this.minuteMinOriginal;
                    this._defaults.secondMin   = this.secondMinOriginal;
                    this._defaults.millisecMin = this.millisecMinOriginal;
                    this._defaults.microsecMin = this.microsecMinOriginal;
                }
            }

            if ($.datepicker._get(dp_inst, 'maxDateTime') !== null && $.datepicker._get(dp_inst, 'maxDateTime') !== undefined && dp_date) {
                var maxDateTime     = $.datepicker._get(dp_inst, 'maxDateTime'),
                    maxDateTimeDate = new Date(maxDateTime.getFullYear(), maxDateTime.getMonth(), maxDateTime.getDate(), 0, 0, 0, 0);

                if (this.hourMaxOriginal === null || this.minuteMaxOriginal === null || this.secondMaxOriginal === null || this.millisecMaxOriginal === null) {
                    this.hourMaxOriginal     = o.hourMax;
                    this.minuteMaxOriginal   = o.minuteMax;
                    this.secondMaxOriginal   = o.secondMax;
                    this.millisecMaxOriginal = o.millisecMax;
                    this.microsecMaxOriginal = o.microsecMax;
                }

                if (dp_inst.settings.timeOnly || maxDateTimeDate.getTime() === dp_date.getTime()) {
                    this._defaults.hourMax = maxDateTime.getHours();
                    if (this.hour >= this._defaults.hourMax) {
                        this.hour                = this._defaults.hourMax;
                        this._defaults.minuteMax = maxDateTime.getMinutes();
                        if (this.minute >= this._defaults.minuteMax) {
                            this.minute              = this._defaults.minuteMax;
                            this._defaults.secondMax = maxDateTime.getSeconds();
                            if (this.second >= this._defaults.secondMax) {
                                this.second                = this._defaults.secondMax;
                                this._defaults.millisecMax = maxDateTime.getMilliseconds();
                                if (this.millisec >= this._defaults.millisecMax) {
                                    this.millisec              = this._defaults.millisecMax;
                                    this._defaults.microsecMax = maxDateTime.getMicroseconds();
                                } else {
                                    if (this.microsec > this._defaults.microsecMax) {
                                        this.microsec = this._defaults.microsecMax;
                                    }
                                    this._defaults.microsecMax = this.microsecMaxOriginal;
                                }
                            } else {
                                this._defaults.millisecMax = this.millisecMaxOriginal;
                                this._defaults.microsecMax = this.microsecMaxOriginal;
                            }
                        } else {
                            this._defaults.secondMax   = this.secondMaxOriginal;
                            this._defaults.millisecMax = this.millisecMaxOriginal;
                            this._defaults.microsecMax = this.microsecMaxOriginal;
                        }
                    } else {
                        this._defaults.minuteMax = this.minuteMaxOriginal;
                        this._defaults.secondMax = this.secondMaxOriginal;
                        if (dp_inst.settings.isTime) {
                            this._defaults.minuteMax = 59;
                            this._defaults.secondMax = 59;
                        }
                        this._defaults.millisecMax = this.millisecMaxOriginal;
                        this._defaults.microsecMax = this.microsecMaxOriginal;
                    }
                } else {
                    this._defaults.hourMax     = this.hourMaxOriginal;
                    this._defaults.minuteMax   = this.minuteMaxOriginal;
                    this._defaults.secondMax   = this.secondMaxOriginal;
                    this._defaults.millisecMax = this.millisecMaxOriginal;
                    this._defaults.microsecMax = this.microsecMaxOriginal;
                }
            }

            if (adjustSliders !== undefined && adjustSliders === true) {
                var hourMax     = parseInt((this._defaults.hourMax - ((this._defaults.hourMax - this._defaults.hourMin) % this._defaults.stepHour)), 10),
                    minMax      = parseInt((this._defaults.minuteMax - ((this._defaults.minuteMax - this._defaults.minuteMin) % this._defaults.stepMinute)), 10),
                    secMax      = parseInt((this._defaults.secondMax - ((this._defaults.secondMax - this._defaults.secondMin) % this._defaults.stepSecond)), 10),
                    millisecMax = parseInt((this._defaults.millisecMax - ((this._defaults.millisecMax - this._defaults.millisecMin) % this._defaults.stepMillisec)), 10),
                    microsecMax = parseInt((this._defaults.microsecMax - ((this._defaults.microsecMax - this._defaults.microsecMin) % this._defaults.stepMicrosec)), 10);

                if (this.hour_slider) {
                    this.control.options(this, this.hour_slider, 'hour', {min: this._defaults.hourMin, max: hourMax});
                    this.control.value(this, this.hour_slider, 'hour', this.hour - (this.hour % this._defaults.stepHour));
                }
                if (this.minute_slider) {
                    this.control.options(this, this.minute_slider, 'minute', {
                        min: this._defaults.minuteMin,
                        max: minMax
                    });
                    this.control.value(this, this.minute_slider, 'minute', this.minute - (this.minute % this._defaults.stepMinute));
                }
                if (this.second_slider) {
                    this.control.options(this, this.second_slider, 'second', {
                        min: this._defaults.secondMin,
                        max: secMax
                    });
                    this.control.value(this, this.second_slider, 'second', this.second - (this.second % this._defaults.stepSecond));
                }
                if (this.millisec_slider) {
                    this.control.options(this, this.millisec_slider, 'millisec', {
                        min: this._defaults.millisecMin,
                        max: millisecMax
                    });
                    this.control.value(this, this.millisec_slider, 'millisec', this.millisec - (this.millisec % this._defaults.stepMillisec));
                }
                if (this.microsec_slider) {
                    this.control.options(this, this.microsec_slider, 'microsec', {
                        min: this._defaults.microsecMin,
                        max: microsecMax
                    });
                    this.control.value(this, this.microsec_slider, 'microsec', this.microsec - (this.microsec % this._defaults.stepMicrosec));
                }
            }
        }
    });

    $.extend(datepickerPrototype, {
        /* Attach the date picker to a jQuery selection.
         * @param  target	element - the target input field or division or span
         * @param  settings  object - the new settings to use for this date picker instance (anonymous)
         */
        _attachDatepicker: function (target, settings) {
            var nodeName, inline, inst;

            nodeName = target.nodeName.toLowerCase();
            inline   = nodeName === "div" || nodeName === "span";
            if (!target.id) {
                this.uuid += 1;
                target.id = "dp" + this.uuid;
            }
            inst          = this._newInst($(target), inline);
            inst.settings = $.extend({}, settings || {});

            if (inst.settings.minDate || inst.settings.maxDate) {
                $(target).prop('readonly', true);
            }

            if (nodeName === "input") {
                this._connectDatepicker(target, inst);
            } else if (inline) {
                this._inlineDatepicker(target, inst);
            }
        },

        /* Action for selecting a day. */
        _selectDay: function (id, month, year, td) {
            var inst, minTime, maxTime, nowDate, nowTime, minDate, maxDate,
                target = $(id);

            if ($(td).hasClass(this._unselectableClass) || this._isDisabledDatepicker(target[0])) {
                return;
            }

            inst               = this._getInst(target[0]);
            inst.selectedDay   = inst.currentDay = $("a", td).html();
            inst.selectedMonth = inst.currentMonth = month;
            inst.selectedYear  = inst.currentYear = year;

            if (inst.settings.showsTime) {
                nowDate = this._formatDate(inst).split(' ')[0];
                nowTime = this._formatDate(inst).split(' ')[1].split(':');
                minDate = inst.settings.minDate;
                maxDate = inst.settings.maxDate;
                if (inst.settings.isDate || inst.settings.isDateTime || inst.settings.isTime) {
                    minDate = new Date(inst.settings.minDate.split(' ')[0]);
                    minDate = $.datepicker.formatDate($.datepicker._get(inst, 'mpDateFormat'), minDate, $.datepicker._getFormatConfig(inst));
                    maxDate = new Date(inst.settings.maxDate.split(' ')[0]);
                    maxDate = $.datepicker.formatDate($.datepicker._get(inst, 'mpDateFormat'), maxDate, $.datepicker._getFormatConfig(inst));
                }
                if (inst.settings.minDate && nowDate === minDate.split(' ')[0]) {
                    minTime                   = inst.settings.minTime.split(':');
                    inst.settings.minDateTime = new Date(inst.settings.minDate);
                    inst.settings.hourMin     = Number(minTime[0]);
                    inst.settings.minuteMin   = Number(minTime[1]);
                    inst.settings.secondMin   = Number(minTime[2]);
                } else {
                    inst.settings.hourMin   = Number(nowTime[0]);
                    inst.settings.minuteMin = Number(nowTime[1]);
                    inst.settings.secondMin = Number(nowTime[2]);
                }

                if (inst.settings.maxDate && nowDate === maxDate.split(' ')[0]) {
                    maxTime                   = inst.settings.maxTime.split(':');
                    inst.settings.maxDateTime = new Date(inst.settings.maxDate);
                    inst.settings.hourMax     = Number(maxTime[0]);
                    inst.settings.minuteMax   = Number(maxTime[1]);
                    inst.settings.secondMax   = Number(maxTime[2]);
                } else {
                    inst.settings.hourMax   = Number(nowTime[0]);
                    inst.settings.minuteMax = Number(nowTime[1]);
                    inst.settings.secondMax = Number(nowTime[2]);
                }
            }

            this._selectDate(id, this._formatDate(inst,
                inst.currentDay, inst.currentMonth, inst.currentYear));
        },

        /* Parse existing date and initialise date picker. */
        _setDateFromField: function (inst, noDefault) {
            if (inst.input.val() === inst.lastVal) {
                return;
            }

            var dateFormat = this._get(inst, "mpDateFormat") ? this._get(inst, "mpDateFormat") : this._get(inst, "dateFormat"),
                dates = inst.lastVal = inst.input ? inst.input.val() : null,
                defaultDate = this._getDefaultDate(inst),
                date = defaultDate,
                settings = this._getFormatConfig(inst);

            try {
                date = this.parseDate(dateFormat, dates, settings) || defaultDate;
            } catch (event) {
                dates = (noDefault ? "" : dates);
            }
            inst.selectedDay = date.getDate();
            inst.drawMonth = inst.selectedMonth = date.getMonth();
            inst.drawYear = inst.selectedYear = date.getFullYear();
            inst.currentDay = (dates ? date.getDate() : 0);
            inst.currentMonth = (dates ? date.getMonth() : 0);
            inst.currentYear = (dates ? date.getFullYear() : 0);
            this._adjustInstDate(inst);
        }
    });

    $.widget('ui.slider', $.ui.slider, {
        _setOptionDisabled: function (value) {
            if (value) {
                this._removeClass(this.hoverable, null, 'ui-state-hover');
                this._removeClass(this.focusable, null, 'ui-state-focus');
            }
        }
    });

    return Component.extend({
        defaults: {
            inputDateFormat: 'MM/dd/y',
            timezoneFormat: 'YYYY-MM-DD HH:mm:ss'
        },

        /**
         * Prepares and sets date/time value that will be sent
         * to the server.
         *
         * @param {String} shiftedValue
         */
        onShiftedValueChange: function (shiftedValue) {
            var value, formattedValue, momentValue;

            if (shiftedValue) {
                momentValue = moment(shiftedValue, this.pickerDateTimeFormat);

                if (this.options.showsTime) {
                    formattedValue = moment(momentValue).format(this.timezoneFormat);
                    value          = moment.tz(formattedValue, this.storeTimeZone).tz('UTC').toISOString();
                } else {
                    value = momentValue.format(this.outputDateFormat);
                }
            } else {
                value = '';
            }

            if (value !== moment.tz(this.value(), this.storeTimeZone).tz('UTC').toISOString()) {
                this.value(value);
            }
        }
    });
});
