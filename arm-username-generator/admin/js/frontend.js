/* global armUgen, jQuery */
/**
 * ARM Username Generator – Frontend Script
 *
 * For each ARMember registration form on the page:
 *  1. Detect the plan being registered for (hidden field: subscription_plan).
 *  2. If that plan has a configured prefix, call the AJAX preview endpoint.
 *  3. Pre-fill the username field with the preview and make it read-only.
 *
 * The actual sequential username is always assigned server-side on submission,
 * so the preview is informational — users see approximately what they'll get.
 */
(function ($) {
    'use strict';

    if (typeof armUgen === 'undefined') {
        return;
    }

    /**
     * Apply auto-username to a single ARMember form element.
     * @param {jQuery} $form
     */
    function processForm($form) {
        /* ----- Locate plan ID ------------------------------------------ */
        var planId = 0;

        /* ARMember hides the plan in a field called subscription_plan */
        var $planField = $form.find('[name="subscription_plan"]');
        if ($planField.length) {
            planId = parseInt($planField.val(), 10) || 0;
        }

        /* Fallback: check data attribute on the form itself */
        if (!planId && $form.data('arm-plan-id')) {
            planId = parseInt($form.data('arm-plan-id'), 10) || 0;
        }

        /* No plan found or plan not configured → leave form alone */
        if (!planId || !armUgen.planPrefixes[planId]) {
            return;
        }

        /* ----- Locate the username field -------------------------------- */
        var $usernameField = $form.find('#arm_username, [name="user_login"]').first();
        if (!$usernameField.length) {
            return;
        }

        /* ----- Apply readonly immediately with a cached preview ---------- */
        var cached = armUgen.planPreviews[planId];
        if (cached) {
            applyReadonly($usernameField, cached);
        }

        /* ----- Refresh preview via AJAX (handles page-cached responses) - */
        $.ajax({
            url      : armUgen.ajaxUrl,
            method   : 'POST',
            data     : {
                action  : 'arm_ugen_preview',
                nonce   : armUgen.nonce,
                plan_id : planId,
            },
            success  : function (response) {
                if (response.success && response.data.username) {
                    applyReadonly($usernameField, response.data.username);
                }
            },
        });
    }

    /**
     * Fill the username field and lock it.
     * @param {jQuery} $field
     * @param {string} username
     */
    function applyReadonly($field, username) {
        $field
            .val(username)
            .prop('readonly', true)
            .addClass('arm-ugen-username-readonly')
            /* Remove any validation that fires when the field is left empty */
            .removeAttr('required')
            .attr('title', armUgen.readonlyMsg);

        /* Insert helper badge below the field if not already there */
        if (!$field.next('.arm-ugen-badge').length) {
            $('<span class="arm-ugen-badge"></span>')
                .text(armUgen.readonlyMsg)
                .insertAfter($field);
        }
    }

    /* ================================================================
       Init: run on DOM ready and also after ARMember rebuilds forms
       (ARMember re-renders forms after AJAX tab switches)
       ================================================================ */

    function init() {
        /* Standard ARMember registration forms */
        $('form.arm_signup_form, form[id^="arm_signup_form"], form[class*="arm_form"]').each(function () {
            processForm($(this));
        });

        /* Broader fallback: any form containing the ARM username field */
        $('form').has('#arm_username, [name="user_login"]').each(function () {
            var $f = $(this);
            /* Skip if already processed */
            if ($f.data('arm-ugen-done')) { return; }
            $f.data('arm-ugen-done', true);
            processForm($f);
        });
    }

    $(document).ready(init);

    /* Re-run when ARMember dynamically loads/rebuilds a form */
    $(document).on('arm_form_loaded arm_after_form_render', init);

    /*
     * If the subscription_plan hidden field changes value (multi-plan pages
     * where the user can pick a plan), refresh the username preview.
     */
    $(document).on('change', '[name="subscription_plan"]', function () {
        var $form = $(this).closest('form');
        if ($form.length) {
            /* Remove done flag so processForm runs again */
            $form.removeData('arm-ugen-done');
            processForm($form);
        }
    });

}(jQuery));
