/* global jQuery */
(function ($) {
    'use strict';

    /* Live-preview the next username as the admin types a prefix */
    $(document).on('input', '.arm-ugen-prefix-input', function () {
        var prefix     = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '');
        var planId     = $(this).data('plan-id');
        var lastNumber = parseInt($(this).data('last-number'), 10) || 0;
        var $cell      = $('#preview_' + planId + ' code');

        if (prefix) {
            $cell.text(prefix + (lastNumber + 1));
        } else {
            $cell.text('—');
        }

        /* Force uppercase in the input itself */
        $(this).val(prefix);
    });

    /* Also update previews when the "Last Allotted Number" changes */
    $('#arm_ugen_last_number').on('input', function () {
        var last = parseInt($(this).val(), 10) || 0;
        $('.arm-ugen-prefix-input').each(function () {
            $(this).data('last-number', last);
            /* Trigger the prefix handler to refresh the preview */
            $(this).trigger('input');
        });
    });

}(jQuery));
