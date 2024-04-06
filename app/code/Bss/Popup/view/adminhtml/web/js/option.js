require([
    'jquery',
    'mage/url'
],function($,url) {
    "use strict";

    $(document).ready(function($){
        var content = $('[data-ui-id="bss-popup-popup-edit-tab-content-fieldset-element-form-field-floating-input-content"]');
        var contentInput = $('#popup_floating_input_content');
        var currentPopupFrequently = $('#popup_frequently').val();
        var currentPopupEventDisplay = $('#popup_event_display').val();
        if ( currentPopupEventDisplay === '5') {
            $("#popup_frequently option[value=" + 1 + "]").remove();
        }
        $('#popup_event_display').change(function () {
            var currentOptionValue = $(this).val();
            if(currentOptionValue === '5'){
                $('#popup_frequently').children('option').each(function(){
                    if ($(this).val() === '1'){
                        $(this).remove();
                        $("#popup_frequently option[value=" + currentPopupFrequently + "]").attr('selected','selected');
                    }
                });
            } else {
                var issetPopupFrequently = $("#popup_frequently option[value=1]").val();
                if (typeof(issetPopupFrequently) === 'undefined' ){
                    $("#popup_frequently").append($('<option>', {
                        value: 1,
                        text: 'When all conditions are satisfied'
                    }));
                }
            }
        });
        $('#popup_event_display, #popup_floating_input_type, #popup_floating_popup, #popup_frequently').change(function () {
            if ($('#popup_event_display').val() === "5"
                || $('#popup_floating_input_type').val() === "0"
                || $('#popup_floating_popup').val() === "0"
                || $('#popup_frequently').val() != "1"
            ) {
                content.hide();
                contentInput.removeClass("textarea required-entry _required");
            } else if ($('#popup_floating_popup').val() ==="1" && $('#popup_floating_input_type').val() === "1") {
                content.show();
                contentInput.addClass("textarea required-entry _required");
            }
        });

        if ($('#popup_floating_popup').val() ==="0"
            || $('#popup_floating_input_type').val() === "0"
            || $('#popup_event_display').val() === "5"
            || $('#popup_frequently').val() != "1"
        ) {
            content.hide();
            contentInput.removeClass("textarea required-entry _required");
        } else if ($('#popup_floating_popup').val() ==="1" && $('#popup_floating_input_type').val() === "1") {
            content.show();
            contentInput.addClass("textarea required-entry _required");
        }
    });
    return;
});
