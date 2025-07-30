jQuery(document).ready(function () {
    jQuery('.ARM_arm_form_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');

        if (fild_name == 'id') {
            jQuery('#arm_form_select').val(fild_value);
            arm_show_hide_logged_in_message(fild_value);
        }
        if( fild_name == 'logged_in_message' ){
            jQuery('input#logged_in_message').val(fild_value);
        }

        if( fild_name == 'assign_default_plan') {
            jQuery('#assign_default_plan').val(fild_value);
            var field_label = jQuery("#assign_default_plan_dd dd").find('.arm_shortcode_form_id_li[data-value="'+fild_value+'"]').html();
            jQuery("#assign_default_plan_dd dt").find('span').html(field_label);
        }

        if (fild_name == 'form_position') {
            jQuery('input#arm_position_hidden').val(fild_value);
            if (fild_value == 'left') {
                jQuery('input#arm_position_left').prop('checked', true);
            }
            if (fild_value == 'center') {
                jQuery('input#arm_position_center').prop('checked', true);
            }
            if (fild_value == 'right') {
                jQuery('input#arm_position_right').prop('checked', true);
            }
        }
        if (fild_name == 'popup') {
            jQuery('input#arm_popup_hidden').val(fild_value);
            if (fild_value == 'true') {
                jQuery('input#arm_popup_true').prop('checked', true);
                jQuery('div.form_popup_options').show();
                jQuery('#arm_form_position_wrapper').hide();
            }
            if (fild_value == 'false') {
                jQuery('input#arm_popup_false').prop('checked', true);
                jQuery('div.form_popup_options').hide();
                jQuery('#arm_form_position_wrapper').show();
            }
        }
        if (fild_name == 'link_type') {
            jQuery('#arm_shortcode_form_link_type').val(fild_value);
            if (fild_value == 'link') {
                jQuery('.arm_shortcode_form_link_opts').removeClass('arm_hidden');
                jQuery('.arm_shortcode_form_button_opts').addClass('arm_hidden');
            } else {
                jQuery('.arm_shortcode_form_link_opts').addClass('arm_hidden');
                jQuery('.arm_shortcode_form_button_opts').removeClass('arm_hidden');
            }
        }
        if (fild_name == 'link_title') {
            jQuery("input#arm_link_title").val(fild_value);
        }
        if (fild_name == 'overlay') {
            jQuery('select#arm_overlay_select option[value="' + fild_value + '"]').prop('selected', true);
        }
        if (fild_name == 'modal_bgcolor') {
            jQuery('.arm_colorpicker_label').css('background', fild_value);
            jQuery("input#arm_vc_form_modal_bgcolor").val(fild_value);
        }
        if (fild_name == 'popup_height') {
            jQuery("input#arm_popup_height").val(fild_value);
        }
        if (fild_name == 'popup_width') {
            jQuery("input#arm_popup_width").val(fild_value);
        }
        if (fild_name == 'link_css') {
            jQuery("textarea#arm_link_css").val(fild_value);
        }
        if (fild_name == 'link_hover_css') {
            jQuery("textarea#arm_link_hover_css").val(fild_value);
        }
    });
    jQuery('.ARM_arm_edit_profile_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        
        if (fild_name == 'title') {
            jQuery("input#arm_title").val(fild_value);
        }
        if (fild_name == 'message') {
            jQuery("input#arm_message").val(fild_value);
        }
        if (fild_name == 'form_position'){
            jQuery('input#arm_edit_profile_position').val(fild_value);
            if( fild_value == 'left' ){
                jQuery('#arm_edit_profile_form_left').prop('checked',true);
            }
            if( fild_value == 'center' ){
                jQuery('#arm_edit_profile_form_center').prop('checked',true);
            }
            if( fild_value == 'right' ){
                jQuery('#arm_edit_profile_form_right').prop('checked',true);
            }
        }
        if( fild_name == 'view_profile_link' ){
            jQuery("input#view_profile_link_label").val(fild_value);
        }
        if( fild_name == 'view_profile') {
            if( fild_value == 'true' ){
                jQuery('input#arm_view_profile_checkbox').prop('checked',true);
            } else {
                jQuery('input#arm_view_profile_checkbox').prop('checked',false);
            }
            jQuery('input#arm_view_profile_hidden').val(fild_value);
        }
        if( fild_name == 'form_id'){
            jQuery("#arm_edit_profile_form").val(fild_value);
        }
        if( fild_name == 'social_fields' ){
            var form_id = jQuery('#arm_edit_profile_form').val();
            arm_get_social_fields(form_id,true,fild_value);
        }

    });
    jQuery('.ARM_arm_logout_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');

        if (fild_name == 'label') {
            jQuery("input#arm_logout_label").val(fild_value);
        }
        if (fild_name == 'type') {
            jQuery('#arm_shortcode_logout_link_type').val(fild_value);
            if (fild_value == 'link') {
                jQuery('.arm_shortcode_logout_link_opts').removeClass('arm_hidden');
                jQuery('.arm_shortcode_logout_button_opts').addClass('arm_hidden');
            } else {
                jQuery('.arm_shortcode_logout_link_opts').addClass('arm_hidden');
                jQuery('.arm_shortcode_logout_button_opts').removeClass('arm_hidden');
            }
        }
        if (fild_name == 'user_info') {
            jQuery('input#arm_user_info_hidden').val(fild_value);
            if (fild_value == 'true') {
                jQuery('input#arm_user_info_true').prop('checked', true);
            }
            if (fild_value == 'false') {
                jQuery('input#arm_user_info_false').prop('checked', true);
            }
        }
        if (fild_name == 'redirect_to') {
            jQuery("input#arm_redirect_to").val(fild_value);
        }
        if (fild_name == 'link_css') {
            jQuery('#arm_logout_link_css').text(fild_value);
        }
        if (fild_name == 'link_hover_css') {
            jQuery('#arm_logout_link_hover_css').text(fild_value);
        }
    });
    jQuery('.ARM_arm_setup_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        
        if (fild_name == 'id') {
            jQuery('#arm_subscription_id_select').val(fild_value);
        }
        if(fild_name == 'subscription_plan') {
            jQuery("#subscription_plan_input").val(fild_value);
        }
        if (fild_name == 'popup') {
            jQuery("input#arm_subscription_display_form_type_hidden").val(fild_value);
            
            if (fild_value == 'false') {
                jQuery('input#arm_subscription_display_type_internal').prop('checked', true);
            }
            if (fild_value == 'true') {
                jQuery('input#arm_subscription_display_type_external').prop('checked', true);
            }
            arm_subscription_setup_display_type();
        }
        if (fild_name == 'hide_title') {
            jQuery('input#arm_subscription_show_hide_title_hidden').val(fild_value);
            if (fild_value == 'true') {
                jQuery('input#arm_subscription_hide_title_true').prop('checked', true);
            }
            if (fild_value == 'false') {
                jQuery('input#arm_subscription_hide_title_false').prop('checked', true);
            }
        }
        if(fild_name == 'hide_plans') {
            if(fild_value==1) {
                jQuery('.hide_plans_checkbox').prop('checked', true);
            } else {
                jQuery('.hide_plans_checkbox').prop('checked', false);
            }
            
        }
        if (fild_name == 'link_type') {
            jQuery('input#arm_subscription_link_type').val(fild_value);
        }
        if( fild_name == 'link_title' ){
            jQuery('input#arm_setup_link_text_id').val(fild_value);
        }
        if (fild_name == 'modal_bgcolor') {
            jQuery('.arm_colorpicker_label').css('background', fild_value);
            jQuery('input#arm_vc_setup_modal_bgcolor').val(fild_value);
        }
        if (fild_name == 'popup_height') {
            jQuery('input#arm_setup_popup_height').val(fild_value);
        }
        if (fild_name == 'popup_width') {
            jQuery('input#arm_setup_popup_width').val(fild_value);
        }
        if (fild_name == 'link_css') {
            jQuery("textarea#arm_link_css").val(fild_value);
        }
        if (fild_name == 'link_hover_css' ){
            jQuery('textarea#arm_link_hover_css').val(fild_value);
        }
    });
    jQuery('.ARM_arm_restrict_content_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if (fild_name == 'type') {
            jQuery('#arm_restrict_content_type_select').val(fild_value);
        }
        if (fild_name == 'plan') {
            var select_values = fild_value.split(",");
            jQuery.each(select_values, function (i, e) {
                jQuery('select#arm_restrict_content_plan_select option[value="' + e + '"]').prop('selected', true);
            });
        }
        if (fild_name == 'armshortcodecontent') {
            jQuery('textarea#armshortcodecontent').val(fild_value);
        }
        if (fild_name == 'armelse_message') {
            jQuery('textarea#armelse_message').val(fild_value);
        }
    });
    
    jQuery('.ARM_arm_member_transaction_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        
        if( fild_name == 'display_invoice_button' ) {
            jQuery("#display_invoice_button.wpb_vc_param_value").val(fild_value);
            if (fild_value == 'true') {
                jQuery('input#display_invoice_button_radio_true').prop('checked', true);
                jQuery('.view_invoice_btn_options').show();
            }
            if (fild_value == 'false') {
                jQuery('input#display_invoice_button_radio_false').prop('checked', true);
                jQuery('.view_invoice_btn_options').hide();
            }
        }
        if(fild_name=="view_invoice_text") {
            jQuery("#view_invoice_text_input").val(fild_value);
        }
        if(fild_name=="view_invoice_css") {
            jQuery("#view_invoice_css_input").val(fild_value);
        }
        if(fild_name=="view_invoice_hover_css") {
            jQuery("#view_invoice_hover_css_input").val(fild_value);
        }
        if (fild_name == 'title') {
            jQuery('input#arm_transaction_title').val(fild_value);
        }
        if( fild_name == 'per_page' ){
            jQuery('input#arm_transaction_per_page_record').val(fild_value);
        }
        if (fild_name == 'message_no_record') {
            jQuery('input#arm_transaction_message_no_record').val(fild_value);
        }
        if( fild_name == 'label' ){
            jQuery("#arm_transaction_label_hidden").val(fild_value);
            last_char = fild_value[fild_value.length - 1];
            if( last_char == ',' ){
                fild_value = fild_value.substr(0,fild_value.length - 1);
            }
            field_value = fild_value.split(',');
            jQuery('.arm_member_transaction_field_input').each(function(){
                if( jQuery(this).is(':checked') ){
                    var fvalue = jQuery(this).val();
                    if( jQuery.inArray(fvalue,field_value) > -1){
                        jQuery(this).prop('checked',true);
                    } else {
                        jQuery(this).prop('checked',false);
                    }
                }
            });
            __FIELD_VALUE = field_value;
        }
        if( fild_name == 'value' ){
            jQuery("#arm_transaction_value_hidden").val(fild_value);
            if( fild_value != ''){
                last_char = fild_value[fild_value.length - 1];
                if( last_char == ',' ){
                    fild_value = fild_value.substr(0,fild_value.length - 1);
                }
                field_value = fild_value.split(',');
                if( typeof __FIELD_VALUE !== 'undefined' && __FIELD_VALUE !== '' ){
                    jQuery('.arm_member_transaction_field_input').each(function(){
                        if( jQuery(this).is(':checked') ){
                            var fvalue = jQuery(this).val();
                            var index = jQuery.inArray(fvalue,__FIELD_VALUE);
                            var fvalue_n = field_value[index];
                            jQuery(this).parents('.arm_member_transaction_fields').find('input[type="text"].arm_member_transaction_fields').eq(index).val(fvalue_n);
                        }
                    });
                }
            }
        }
    });
    jQuery('.ARM_arm_account_detail_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        var sectionOpts = '';       
        if( fild_name == 'label' ){
            jQuery("#arm_profile_label_hidden").val(fild_value);
            last_char = fild_value[fild_value.length - 1];
            if( last_char == ',' ){
                fild_value = fild_value.substr(0,fild_value.length - 1);
            }
            field_value = fild_value.split(',');
            jQuery('.arm_account_chk_fields').each(function(){
                
                var fvalue = jQuery(this).val();
                if( jQuery.inArray(fvalue,field_value) > -1){
                    jQuery(this).prop('checked',true);
                } else {
                    jQuery(this).prop('checked',false);
                }
            });
            __ACC_FIELD_VALUE = field_value;
        }
        
        if( fild_name == 'value' ){
            jQuery("#arm_profile_value_hidden").val(fild_value);
            if( fild_value != ''){
                last_char = fild_value[fild_value.length - 1];
                if( last_char == ',' ){
                    fild_value = fild_value.substr(0,fild_value.length - 1);
                }
                field_value = fild_value.split(',');
                if( typeof __ACC_FIELD_VALUE !== 'undefined' && __ACC_FIELD_VALUE !== '' ){
                    jQuery('.arm_account_chk_fields').each(function(){
                        var parent_obj = jQuery(this).parent();
                        if( jQuery(this).is(':checked') ){
                            var fvalue = jQuery(this).val();
                            var index = jQuery.inArray(fvalue,__ACC_FIELD_VALUE);
                            var fvalue_n = field_value[index];
                            jQuery(this).parents('.arm_acount_field_details_option').find('.arm_account_detail_input').val(fvalue_n);
                        }
                    });
                }
            }
        }
        if( fild_name == 'social_fields' ){
            jQuery("#profile_social_fields_hidden").val(fild_value);
            last_char = fild_value[fild_value.length - 1];
            if( last_char == ',' ){
                fild_value = fild_value.substr(0,fild_value.length - 1);
            }
            field_value = fild_value.split(',');
            jQuery('.arm_spf_profile_fields').each(function(){
                var fvalue = jQuery(this).val();
                if( jQuery.inArray(fvalue,field_value) > -1){
                    jQuery(this).prop('checked',true);
                } else {
                    jQuery(this).prop('checked',false);
                }
            });
        }
    });
    jQuery('.ARM_arm_close_account_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if( fild_name == 'set_id' ){
            jQuery('#arm_set_id').val(fild_value);
            var field_label = jQuery(".arm_set_id_dd dd").find("li[data-value='"+fild_value+"']").html();
            jQuery(".arm_set_id_dd dt").find("span").html(field_label);
            if(fild_value!='' || fild_value!=0) {
                jQuery("#arm_close_acc_css").show();
            } else {
                jQuery("#arm_close_acc_css").hide();
            }
            
        }
        if(fild_name=='css') {
            jQuery("#arm_cancel_link_css").val(fild_value);
        }
    });
    jQuery('.ARM_arm_cancel_membership_shortcode_armfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if (fild_name == 'label') {
            jQuery('input#arm_cancel_label').val(fild_value);
        }
        if (fild_name == 'type') {
            jQuery('#arm_shortcode_cancel_membership_link_type').val(fild_value);
            if (fild_value == 'link') {
                jQuery('.arm_shortcode_cancel_membership_link_opts').removeClass('arm_hidden');
                jQuery('.arm_shortcode_cancel_membership_button_opts').addClass('arm_hidden');
            } else {
                jQuery('.arm_shortcode_cancel_membership_link_opts').addClass('arm_hidden');
                jQuery('.arm_shortcode_cancel_membership_button_opts').removeClass('arm_hidden');
            }
        }
        if (fild_name == 'link_css') {
            jQuery('#arm_cancel_link_css').text(fild_value);
        }
        if (fild_name == 'link_hover_css') {
            jQuery('#arm_cancel_link_hover_css').text(fild_value);
        }
    });
    jQuery('.ARM_arm_social_login_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if( fild_name == 'network' ){
            jQuery("#arm_shortcode_social_networks").val(fild_value);
        }
        if( fild_name == 'icon' ){
            var network = jQuery("#arm_shortcode_social_networks").val();
            jQuery('.arm_social_network_icons').removeClass('selected');
            jQuery("#social_network_"+network+"_icon").addClass('selected');
            jQuery("input.arm_social_network_icons").prop('checked',false);
            jQuery("#arm_social_network_icon_hidden").val(fild_value);
            jQuery("input.arm_social_network_icons[value='"+fild_value+"']").prop('checked',true);
        }
    })
    jQuery('.ARM_arm_membership_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if( fild_name == 'show_change_subscription' ){
            jQuery('#arm_show_change_subscription_hidden').val(fild_value);
            if( fild_value == 'true' ){
                jQuery('#arm_show_change_subscription_true').prop('checked',true);
                jQuery('tr.form_popup_options').show();
            } else {
                jQuery('#arm_show_change_subscription_false').prop('checked',true);
                jQuery('tr.form_popup_options').hide();
            }
        }
        if( fild_name == 'change_subscription_url' ){
            jQuery('#arm_change_subscription_url').val(fild_value);
        }

        
        if( fild_name == 'title' ) {
            jQuery("#current_membership_label").val(fild_value);
        }
        
        if( fild_name == 'setup_id' ) {
            jQuery("#arm_form_select").val(fild_value);
            var selected_label = jQuery("#arm_form_select_dropdown dd").find(".arm_shortcode_form_id_li[data-value='"+fild_value+"']").html();
            jQuery("#arm_form_select_dropdown dt").find("span").html(selected_label);
        }

        if( fild_name == 'membership_label' ){

            jQuery('#arm_current_membership_fields_label').val(fild_value);
            if( fild_value != '' ){
                last_char = fild_value[fild_value.length - 1];
                if( last_char == ',' ){
                    fild_value = fild_value.substr(0,fild_value.length - 1);
                }
                field_value = fild_value.split(',');

                jQuery('.arm_current_membership_field_input').each(function(){
                    if( jQuery(this).is(':checked') ){
                        var fvalue = jQuery(this).val();
                        if( jQuery.inArray(fvalue,field_value) > -1){
                            jQuery(this).prop('checked',true);
                        } else {
                            jQuery(this).prop('checked',false);
                        }
                    }
                });
                __FIELD_VALUE = field_value;
            }
        }

        if( fild_name == 'membership_value' ){
            jQuery("#arm_current_membership_fields_value").val(fild_value);
            if( fild_value != ''){
                last_char = fild_value[fild_value.length - 1];
                if( last_char == ',' ){
                    fild_value = fild_value.substr(0,fild_value.length - 1);
                }
                field_value = fild_value.split(',');
                if( typeof __FIELD_VALUE !== 'undefined' && __FIELD_VALUE !== '' ){
                    jQuery('.arm_current_membership_field_input').each(function(){
                        if( jQuery(this).is(':checked') ){
                            var fvalue = jQuery(this).val();
                            var index = jQuery.inArray(fvalue,__FIELD_VALUE);
                            var fvalue_n = field_value[index];
                            jQuery(this).parents('.arm_member_current_membership_field_list').find('.arm_member_current_membership_fields.arm_text_input').val(fvalue_n);
                        }
                    });
                }
            }
        }

        if( fild_name == 'display_renew_button' ){
            jQuery('#arm_show_renew_subscription_hidden').val(fild_value);
            if( fild_value == 'true' ){
                jQuery('#arm_show_renew_subscription_false').prop('checked', false);
                jQuery('#arm_show_renew_subscription_true').prop('checked',true);
                jQuery('tr.form_popup_options#show_renew_subscription_section').show();
            } else {
                jQuery('#arm_show_renew_subscription_true').prop('checked', false);
                jQuery('#arm_show_renew_subscription_false').prop('checked',true);
                jQuery('tr.form_popup_options#show_renew_subscription_section').hide();
            }
        }

        if( fild_name == 'renew_text' ) {
            jQuery("#arm_renew_membership_text").val(fild_value);
        }

        if( fild_name == 'make_payment_text' ) {
            jQuery("#arm_make_payment_membership_text").val(fild_value);
        }

        if( fild_name == 'renew_css' ) {
            jQuery('#arm_button_css').val(fild_value);
        }

        if( fild_name == 'renew_hover_css' ) {
            jQuery("#arm_button_hover_css").val(fild_value);
        }


        if( fild_name == 'display_cancel_button' ){
            jQuery('#arm_show_cancel_subscription_hidden').val(fild_value);
            if( fild_value == 'true' ){
                jQuery('#arm_show_cancel_subscription_hidden_false').prop('checked', false);
                jQuery('#arm_show_cancel_subscription_true').prop('checked',true);
                jQuery('tr.form_popup_options#show_cancel_subscription_section').show();
            } else {
                jQuery('#arm_show_cancel_subscription_true').prop('checked', false);
                jQuery('#arm_show_cancel_subscription_hidden_false').prop('checked',true);
                jQuery('tr.form_popup_options#show_cancel_subscription_section').hide();
            }
        }

        if( fild_name == 'cancel_text' ) {
            jQuery("#arm_cancel_membership_text").val(fild_value);
        }

        if(fild_name == 'cancel_css') {
            jQuery("#arm_cancel_button_css").val(fild_value);
        }

        if(fild_name == 'cancel_hover_css') {
            jQuery("#arm_cancel_button_hover_css").val(fild_value);
        }

        if(fild_name == 'cancel_message') {
            jQuery("#arm_cancel_message").val(fild_value);
        }

        if( fild_name == 'display_update_card_button' ){
            jQuery('#arm_show_update_card_subscription_hidden').val(fild_value);
            if( fild_value == 'true' ){
                jQuery('#arm_show_update_card_subscription_hidden_false').prop('checked', false);
                jQuery('#arm_show_update_card_subscription_true').prop('checked',true);
                jQuery('tr.form_popup_options#show_update_card_subscription_section').show();
            } else {
                jQuery('#arm_show_update_card_subscription_true').prop('checked', false);
                jQuery('#arm_show_update_card_subscription_hidden_false').prop('checked',true);
                jQuery('tr.form_popup_options#show_update_card_subscription_section').hide();
            }
        }

        if(fild_name == 'update_card_text') {
            jQuery("#arm_update_card_membership_text").val(fild_value);
        }

        if(fild_name == 'update_card_css') {
            jQuery("#arm_update_card_button_css").val(fild_value);
        }

        if(fild_name == 'update_card_hover_css') {
            jQuery("#arm_update_card_button_hover_css").val(fild_value);
        }

        if( fild_name == 'trial_active' ) {
            jQuery("#arm_trial_active").val(fild_value);    
        }

        if( fild_name == 'message_no_record' ) {
            jQuery("#arm_message_no_record").val(fild_value);    
        }

    });
  
    jQuery('.ARM_arm_conditional_redirection_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if( fild_name == 'condition' ){
            jQuery("#arm_conditional_redirection_condition").val(fild_value);
        }
        if( fild_name == 'plans'){
            jQuery("#arm_conditional_redirection_plans").val(fild_value);
        }
        if( fild_name == 'redirect_to'){
            jQuery("#arm_conditional_redirection_url").val(fild_value);
        }
    });

    jQuery('.ARM_arm_conditional_redirection_role_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');
        if( fild_name == 'condition' ){
            jQuery("#arm_conditional_redirection_condition_role").val(fild_value);
        }
        if( fild_name == 'roles'){
            jQuery("#arm_conditional_redirection_roles").val(fild_value);
        }
        if( fild_name == 'redirect_to'){
            jQuery("#arm_conditional_redirection_url").val(fild_value);
        }
    });

    jQuery('.ARM_arm_usermeta_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');

        if(fild_name == 'meta') {
            jQuery("#arm_user_custom_meta").val(fild_value);
        }
    });
    
    jQuery('.ARM_arm_user_badge_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');

        if(fild_name == 'user_id') {
            jQuery("#arm_user_id").val(fild_value);
        }
    });
    
    jQuery('.ARM_arm_user_planinfo_shortcode_armfield').each(function(){
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');

        if(fild_name == 'plan_id') {
            jQuery("#arm_plan_id").val(fild_value);
        }

        if(fild_name == 'plan_info') {
            jQuery("#plan_info").val(fild_value);
        }
    });
    

    if (jQuery.isFunction(jQuery().chosen)) {
        jQuery(".arm_chosen_selectbox").chosen({
            no_results_text: "Oops, nothing found."
        });
    }
    if (jQuery.isFunction(jQuery().colpick))
    {
        jQuery('.arm_colorpicker').each(function (e) {
            var $arm_colorpicker = jQuery(this);
            var default_color = $arm_colorpicker.val();
            if (default_color == '') {
                default_color = '#000000';
            }
            else {
                default_color.replace(' ', '').replace('(', '').replace(')', '').replace('"', '').replace("'", '').replace("/", '').replace("\\", '');
                default_color_length = default_color.length;
                if( default_color_length > 7 )
                {
                    default_color = default_color.substr(0, 7);
                }
            }
            $arm_colorpicker.wrap('<label class="arm_colorpicker_label" style="background-color:' + default_color + '"></label>');
            $arm_colorpicker.colpick({
                layout: 'hex',
                submit: 0,
                colorScheme: 'dark',
                color: default_color,
                onChange: function (hsb, hex, rgb, el, bySetColor) {
                    jQuery(el).parent('.arm_colorpicker_label').css('background-color', '#' + hex);
                    if (!bySetColor) {
                        jQuery(el).val('#' + hex);
                    }
                }
            });
        });
    }

    arm_selectbox_init();
});

function arm_show_hide_css_textarea(current_form){
    if( typeof current_form != 'undefined' && current_form != ''){
        jQuery('#arm_close_acc_css').show();
    } else {
        jQuery('#arm_close_acc_css').hide();
    }
}

function arm_show_hide_logged_in_message(current_form){
    var obj = jQuery('ul.arm_form_select li[data-value="'+current_form+'"]');
    var form_type = obj.attr('data-form-type');
    
    if( typeof form_type != 'undefined' && form_type != 'change_password'){
        jQuery('#arm_member_form_logged_in_message').show();
    } else {
        jQuery('#arm_member_form_logged_in_message').hide();
    }
    
    if( form_type == 'registration'){
        jQuery('#arm_member_form_default_free_plan').show();
    } else {
        jQuery('#arm_member_form_default_free_plan').hide();
    }
}
function arm_show_hide_title() {
    var fild_value = jQuery('input[name="arm_hide_title"]:checked').val();
    jQuery('input#arm_show_hide_title_hidden').val(fild_value);
}
function arm_show_hide_popup() {
    var fild_value = jQuery('input[name="arm_popup"]:checked').val();
    jQuery('input#arm_popup_hidden').val(fild_value);
    if (fild_value == 'true') {
        jQuery('div.form_popup_options').show();
        jQuery('#arm_form_position_wrapper').hide();
    }
    if (fild_value == 'false') {
        jQuery('div.form_popup_options').hide();
        jQuery('#arm_form_position_wrapper').show();
    }
    return false;
}
function arm_position_input(){
    var field_value = jQuery('input[name="arm_form_position"]:checked').val();
    jQuery("input#arm_position_hidden").val(field_value);
}
function arm_edit_form_position_input(){
    var field_value = jQuery('input[name="arm_edit_profile_position"]:checked').val();
    jQuery("input#arm_edit_profile_position").val(field_value);
}
function arm_user_info_action() {
    var fild_value = jQuery('input[name="arm_user_info"]:checked').val();
    jQuery('input#arm_user_info_hidden').val(fild_value);
}
function arm_subscription_show_hide_title() {
    var fild_value = jQuery('input[name="arm_subscription_hide_title"]:checked').val();
    jQuery('input#arm_subscription_show_hide_title_hidden').val(fild_value);
}
function arm_subscription_setup_display_type() {
    var field_value = jQuery('input[name="arm_subscription_display_type"]:checked').val();
    jQuery('input#arm_subscription_display_form_type_hidden').val(field_value);
    if (field_value == 'true') {
        jQuery('div.form_popup_options').show();
    } else {
        jQuery('div.form_popup_options').hide();
    }
    return false;
}
function arm_activities_paging_type_check(){
    var field_value = jQuery("input[name='arm_activitie_paging_type']:checked").val();
    jQuery("input#arm_activitie_paging_type_hidden").val(field_value);
    if( field_value == 'numeric' ){
        jQuery('.form_popup_options').hide();
    }
    if( field_value == 'infinite' ){
        jQuery('.form_popup_options').show();
    }
    return false;
}
function arm_activitie_show_hide_paging() {
    var fild_value = jQuery('input[name="arm_activitie_paging"]:checked').val();
    jQuery('input#arm_activitie_show_hide_paging_hidden').val(fild_value);
    if( fild_value == 'true' ){
        jQuery("#arm_paging_type_wrapper").show();
    } else {
        jQuery("#arm_paging_type_wrapper").hide();
    }
}
function arm_account_detail_tab_func() {
    var field_labels = '';
    var field_values = '';
    jQuery('.arm_account_chk_fields').each(function(){
        if( jQuery(this).is(':checked') ){
            field_labels += jQuery(this).val() + ',';
            var obj = jQuery(this);
            field_values += obj.parents('.arm_acount_field_details_option').find('input[type="text"]').val() + ',';
        }
    });

    jQuery('input#arm_profile_label_hidden').val(field_labels);
    jQuery('input#arm_profile_value_hidden').val(field_values);
}
function arm_social_networks_icon_list(network){
    jQuery('.arm_social_network_icons').removeClass('selected');
    jQuery("#social_network_"+network+"_icon").addClass('selected');
    var icon = jQuery('li.arm_social_login_network[data-value="'+network+'"]').attr('data-icon');
    jQuery('.arm_social_network_icons').prop('checked',false);
    jQuery('.arm_social_network_icons[data-key="'+icon+'"]').prop('checked',true);
}
function arm_set_social_network_icon(){
    var field_value = jQuery("input[name='arm_social_icon']:checked").val();
    jQuery("input#arm_social_network_icon_hidden").val(field_value);
}
jQuery(document).on('change', '#arm_subscription_link_type', function () {
    var value = jQuery(this).val();
    if (value == 'link') {
        jQuery('.arm_shortcode_setup_link_opts').removeClass('arm_hidden');
        jQuery('.arm_shortcode_setup_button_opts').addClass('arm_hidden');
    } else {
        jQuery('.arm_shortcode_setup_link_opts').addClass('arm_hidden');
        jQuery('.arm_shortcode_setup_button_opts').removeClass('arm_hidden');
    }
});

function arm_view_profile_checked(){
    if( jQuery('#arm_view_profile_checkbox').is(':checked') ){
        jQuery('#arm_view_profile_hidden').val('true');
    } else {
        jQuery('#arm_view_profile_hidden').val('false');
    }
}

function arm_get_social_fields(form_id,from_vc,fild_value){
    var ajax_url = jQuery('#ajax_url_hidden').val();
    if( typeof from_vc == 'undefined' ){
        from_vc = false;
    }
    if( typeof fild_value == 'undefined'){
        fild_value = '';
    }
    if( form_id !== '' ){
        jQuery.ajax({
            url:ajax_url,
            method:'POST',
            dataType:'json',
            data:'action=arm_get_spf_in_tinymce&form_name='+form_id+'&is_vc='+true,
            success:function(response){
                if( response.error == false ){
                    jQuery("#arm_social_fields_wrapper").html(response.content);
                    if( from_vc == true ){
                        if( fild_value !== '' ){
                            jQuery('#social_fields_hidden').val(fild_value);
                            fild_value = fild_value.split(',');
                            jQuery('#social_fields_hidden').parent().find('.arm_spf_active_checkbox').each(function(){
                                var value = jQuery(this).val();
                                if( fild_value.indexOf( value ) > -1){
                                    jQuery(this).prop('checked',true);
                                } else {
                                    jQuery(this).prop('checked',false);
                                }
                            });
                        }
                    }
                }
            }
        })
    }
}

function arm_select_profile_social_fields(){
    var field_value = '';
    jQuery('.arm_spf_profile_fields').each(function(){
        if( jQuery(this).is(':checked') ){
            field_value += jQuery(this).val() + ',';
        }
    });
    jQuery('#profile_social_fields_hidden').val(field_value);
}

function arm_select_social_fields(){
    var field_value = '';
    jQuery('.arm_spf_active_checkbox_input').each(function(){
        if( jQuery(this).is(':checked') ){
            field_value += jQuery(this).val() + ',';
        }
    });
    jQuery("#social_fields_hidden").val( field_value );
}

function arm_select_transaction_fields(){
    var field_labels = '';
    var field_values = '';
    jQuery('.arm_member_transaction_field_input').each(function(){
        if( jQuery(this).is(':checked') ){
            field_labels += jQuery(this).val() + ',';
            var obj = jQuery(this);
            field_values += obj.parents('.arm_member_transaction_field_list').find('input[type="text"]').val() + ',';
        }
    });
    jQuery('#arm_transaction_label_hidden').val(field_labels);
    jQuery('#arm_transaction_value_hidden').val(field_values);
}

function arm_select_membership_fields(){
    var field_values = '';
    field_values = jQuery('#current_membership_label').val()+','+jQuery('#current_membership_started').val()+','+jQuery('#membership_expired_on').val()+','+jQuery('#membership_recurring_profile').val()+','+jQuery("#membership_remaining_occurence").val()+','+jQuery('#membership_next_billing_date').val()+','+jQuery('#membership_trial_period').val();
    jQuery("#arm_current_membership_fields_value").val(field_values);
}

function arm_select_login_history_fields(){
    var field_labels = '';
    var field_values = '';
    jQuery('.arm_member_login_history_field_input').each(function(){
        if( jQuery(this).is(':checked') ){
            field_labels += jQuery(this).val() + ',';
            var obj = jQuery(this);
            field_values += obj.parents('.arm_member_login_history_field_list').find('input[type="text"]').val() + ',';
        }
    });
    jQuery('#arm_login_history_label_hidden').val(field_labels);
    jQuery('#arm_login_history_value_hidden').val(field_values);
}

function arm_show_change_subscription() {
    var fild_value = jQuery('input[name="arm_show_change_subscription_input"]:checked').val();
    jQuery('input#arm_show_change_subscription_hidden').val(fild_value);
    if (fild_value == 'true') {
        jQuery('tr.form_popup_options').show();
    }
    if (fild_value == 'false') {
        jQuery('tr.form_popup_options').hide();
    }
    return false;
}

function arm_show_renew_subscription(){
    var field_value = jQuery('input[name="arm_show_renew_subscription_input"]:checked').val();
    jQuery('input#arm_show_renew_subscription_hidden').val(field_value);
    if( field_value == 'true' ){
        jQuery('tr.form_popup_options#show_renew_subscription_section').show();
    }
    if( field_value == 'false'){
        jQuery('tr.form_popup_options#show_renew_subscription_section').hide();
    }
    return false;
}

function arm_display_invoice() {
    var field_value = jQuery('input[name="display_invoice_button_radio"]:checked').val();
    jQuery('input#display_invoice_button').val(field_value);
}

function arm_show_cancel_subscription(){
    var field_value = jQuery('input[name="arm_show_cancel_subscription_input"]:checked').val();
    jQuery('input#arm_show_cancel_subscription_hidden').val(field_value);
    if( field_value == 'true' ){
        jQuery('tr.form_popup_options#show_cancel_subscription_section').show();
    }
    if( field_value == 'false'){
        jQuery('tr.form_popup_options#show_cancel_subscription_section').hide();
    }
    return false;
}

function arm_select_current_membership_fields(){
    var field_labels = '';
    var field_values = '';
    jQuery('.arm_current_membership_field_input').each(function(){
        if( jQuery(this).is(':checked') ){
            field_labels += jQuery(this).val() + ',';
            var obj = jQuery(this);
            field_values += obj.parents('.arm_member_current_membership_field_list').find('input[type="text"]').val() + ',';
        }
    });
    jQuery('#arm_current_membership_fields_label').val(field_labels);
    jQuery('#arm_current_membership_fields_value').val(field_values);
}

function arm_change_hide_plan_settigs(){
    
    if(jQuery('.hide_plans_checkbox').is(':checked')){
        jQuery('.hide_plans').val('1');
    }else{
        jQuery('.hide_plans').val('0');
    }
}

function arm_show_update_card_subscription(){
    var field_value = jQuery('input[name="arm_show_update_card_subscription_input"]:checked').val();
    jQuery('input#arm_show_update_card_subscription_hidden').val(field_value);
    if( field_value == 'true' ){
        jQuery('tr.form_popup_options#show_update_card_subscription_section').show();
    }
    if( field_value == 'false'){
        jQuery('tr.form_popup_options#show_update_card_subscription_section').hide();
    }
    return false;
}