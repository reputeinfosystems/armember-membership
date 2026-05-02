/* global armCsvIE, jQuery */
(function ($) {
    'use strict';

    var allRows     = [];   // full parsed rows from preview
    var csvHeaders  = [];   // column headers from CSV

    /* ==================================================================
       Tab switching
       ================================================================== */
    $(document).on('click', '.arm-csv-ie-tabs .nav-tab', function (e) {
        e.preventDefault();
        var tab = $(this).data('tab');

        $('.arm-csv-ie-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.arm-csv-ie-panel').hide();
        $('#arm-csv-' + tab).show();
    });

    /* ==================================================================
       EXPORT
       ================================================================== */
    $('#armCsvExportForm').on('submit', function (e) {
        e.preventDefault();

        var $btn = $('#armCsvExportBtn');
        $btn.prop('disabled', true);
        $('#armExportSpinner').show();

        var formData = $(this).serialize();
        formData += '&action=arm_csv_export&nonce=' + armCsvIE.nonce;

        /* Trigger file download via hidden iframe */
        var iframe = $('<iframe>').attr({
            name   : 'arm_csv_export_frame',
            style  : 'display:none',
        }).appendTo('body');

        var form = $('<form>').attr({
            method : 'POST',
            action : armCsvIE.ajaxUrl,
            target : 'arm_csv_export_frame',
        });

        /* Append serialised fields as hidden inputs */
        $.each($(this).serializeArray(), function (_, pair) {
            $('<input>').attr({ type: 'hidden', name: pair.name, value: pair.value }).appendTo(form);
        });
        $('<input>').attr({ type: 'hidden', name: 'action', value: 'arm_csv_export' }).appendTo(form);
        $('<input>').attr({ type: 'hidden', name: 'nonce',  value: armCsvIE.nonce  }).appendTo(form);

        form.appendTo('body').submit();

        setTimeout(function () {
            $btn.prop('disabled', false);
            $('#armExportSpinner').hide();
            form.remove();
            iframe.remove();
        }, 3000);
    });

    /* ==================================================================
       IMPORT – Download sample
       ================================================================== */
    $('#armCsvDownloadSample').on('click', function (e) {
        e.preventDefault();

        var form = $('<form>').attr({
            method : 'POST',
            action : armCsvIE.ajaxUrl,
            target : '_blank',
        });
        $('<input>').attr({ type: 'hidden', name: 'action', value: 'arm_csv_download_sample' }).appendTo(form);
        $('<input>').attr({ type: 'hidden', name: 'nonce',  value: armCsvIE.nonce             }).appendTo(form);
        form.appendTo('body').submit().remove();
    });

    /* ==================================================================
       IMPORT – Step 1: Upload & preview
       ================================================================== */
    $('#armCsvUploadForm').on('submit', function (e) {
        e.preventDefault();

        var fileInput = document.getElementById('arm_csv_file');
        if (!fileInput.files.length) {
            alert(armCsvIE.selectFile || 'Please select a CSV file.');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'arm_csv_import_preview');
        formData.append('nonce',  armCsvIE.nonce);
        formData.append('csv_file', fileInput.files[0]);

        $('#armCsvUploadBtn').prop('disabled', true);
        $('#armUploadSpinner').show();

        $.ajax({
            url         : armCsvIE.ajaxUrl,
            type        : 'POST',
            data        : formData,
            processData : false,
            contentType : false,
            success     : function (response) {
                if (response.success) {
                    renderStep2(response.data);
                } else {
                    alert(response.data.message || 'Upload failed.');
                }
            },
            error       : function () {
                alert('An error occurred. Please try again.');
            },
            complete    : function () {
                $('#armCsvUploadBtn').prop('disabled', false);
                $('#armUploadSpinner').hide();
            },
        });
    });

    /* ==================================================================
       IMPORT – Step 2: Render column mapper + preview
       ================================================================== */
    function renderStep2(data) {
        csvHeaders = data.headers;
        allRows    = data.all_rows;

        /* Row count info */
        $('#armImportRowCount').text(
            'Total rows: ' + data.total
        );

        /* Column map table */
        var fieldOptions = buildFieldOptions(data.core_fields);
        var mapHtml  = '<table class="arm-csv-column-map"><thead><tr>'
                     + '<th>CSV Column</th><th>Sample Value</th><th>Map to Field</th>'
                     + '</tr></thead><tbody>';

        $.each(csvHeaders, function (idx, header) {
            var sampleVal = (data.preview_rows[0] && data.preview_rows[0][idx] !== undefined)
                          ? escHtml(data.preview_rows[0][idx])
                          : '';
            var autoMatch = autoMatchField(header.toLowerCase().trim(), data.core_fields);
            mapHtml += '<tr>'
                     + '<td><strong>' + escHtml(header) + '</strong></td>'
                     + '<td>' + sampleVal + '</td>'
                     + '<td><select name="column_map[' + idx + ']">' + fieldOptions(autoMatch) + '</select></td>'
                     + '</tr>';
        });

        mapHtml += '</tbody></table>';
        $('#armColumnMapContainer').html(mapHtml);

        /* Preview data table */
        var previewHtml = '<table class="arm-csv-preview-table"><thead><tr>';
        $.each(csvHeaders, function (_, h) {
            previewHtml += '<th>' + escHtml(h) + '</th>';
        });
        previewHtml += '</tr></thead><tbody>';
        $.each(data.preview_rows, function (_, row) {
            previewHtml += '<tr>';
            $.each(row, function (_, cell) {
                previewHtml += '<td>' + escHtml(cell) + '</td>';
            });
            previewHtml += '</tr>';
        });
        previewHtml += '</tbody></table>';
        $('#armPreviewTableWrap').html(previewHtml);

        $('#armImportStep1').hide();
        $('#armImportStep2').show();
    }

    /* ==================================================================
       IMPORT – Step 3: Run import
       ================================================================== */
    $('#armCsvImportBtn').on('click', function () {
        if (!confirm(armCsvIE.confirm)) {
            return;
        }

        var columnMap = {};
        $('select[name^="column_map"]').each(function () {
            var match = this.name.match(/\[(\d+)\]/);
            if (match) {
                columnMap[match[1]] = $(this).val();
            }
        });

        var data = {
            action          : 'arm_csv_import_process',
            nonce           : armCsvIE.nonce,
            rows            : JSON.stringify(allRows),
            column_map      : columnMap,
            plan_id         : $('#arm_import_plan').val(),
            send_notify     : $('#arm_send_notify').is(':checked') ? 1 : 0,
            update_existing : $('#arm_update_existing').is(':checked') ? 1 : 0,
        };

        $('#armCsvImportBtn, #armCsvImportBack').prop('disabled', true);
        $('#armImportSpinner').show();

        $.post(armCsvIE.ajaxUrl, data, function (response) {
            if (response.success) {
                renderResults(response.data);
            } else {
                alert(response.data.message || 'Import failed.');
            }
        }).fail(function () {
            alert('An error occurred. Please try again.');
        }).always(function () {
            $('#armCsvImportBtn, #armCsvImportBack').prop('disabled', false);
            $('#armImportSpinner').hide();
        });
    });

    $('#armCsvImportBack').on('click', function () {
        $('#armImportStep2').hide();
        $('#armImportStep1').show();
    });

    $('#armCsvImportAnother').on('click', function () {
        $('#armImportStep3').hide();
        $('#armImportStep1').show();
        $('#arm_csv_file').val('');
        allRows    = [];
        csvHeaders = [];
    });

    /* ==================================================================
       Render results
       ================================================================== */
    function renderResults(data) {
        var html = '<div class="arm-csv-ie-result-box">'
                 + '<span class="arm-csv-ie-stat"><strong>' + data.created + '</strong> Created</span>'
                 + '<span class="arm-csv-ie-stat"><strong>' + data.updated + '</strong> Updated</span>'
                 + '<span class="arm-csv-ie-stat"><strong>' + data.skipped + '</strong> Skipped</span>'
                 + '</div>';

        if (data.errors && data.errors.length) {
            html += '<div class="arm-csv-ie-errors"><ul>';
            $.each(data.errors, function (_, msg) {
                html += '<li>' + escHtml(msg) + '</li>';
            });
            html += '</ul></div>';
        }

        $('#armImportResults').html(html);
        $('#armImportStep2').hide();
        $('#armImportStep3').show();
    }

    /* ==================================================================
       Helpers
       ================================================================== */

    function buildFieldOptions(coreFields) {
        var fields = ['skip', 'username', 'email', 'first_name', 'last_name',
                      'display_name', 'password', 'role', 'website',
                      'description', 'nickname'];

        /* Add any extra core fields not already listed */
        $.each(coreFields, function (_, f) {
            if (fields.indexOf(f) === -1) {
                fields.push(f);
            }
        });

        return function (selected) {
            var opts = '';
            $.each(fields, function (_, f) {
                var sel = (f === selected) ? ' selected' : '';
                opts += '<option value="' + f + '"' + sel + '>' + f + '</option>';
            });
            return opts;
        };
    }

    function autoMatchField(header, coreFields) {
        var map = {
            'id'            : 'skip',
            'user_login'    : 'username',
            'username'      : 'username',
            'login'         : 'username',
            'user_email'    : 'email',
            'email'         : 'email',
            'first_name'    : 'first_name',
            'firstname'     : 'first_name',
            'last_name'     : 'last_name',
            'lastname'      : 'last_name',
            'display_name'  : 'display_name',
            'displayname'   : 'display_name',
            'name'          : 'display_name',
            'user_pass'     : 'password',
            'password'      : 'password',
            'pass'          : 'password',
            'role'          : 'role',
            'user_url'      : 'website',
            'website'       : 'website',
            'url'           : 'website',
            'description'   : 'description',
            'bio'           : 'description',
            'nickname'      : 'nickname',
        };
        return map[header] || 'skip';
    }

    function escHtml(str) {
        if (str === null || str === undefined) { return ''; }
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

}(jQuery));
