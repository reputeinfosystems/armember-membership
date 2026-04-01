<?php
	global $wpdb, $ARMemberLite, $arm_global_settings ,$arm_common_lite;

    $arm_all_member_panel_settings =get_option('arm_member_panel_settings');

    $arm_all_tab_settings = isset($arm_all_member_panel_settings['tab_settings']) ? $arm_all_member_panel_settings['tab_settings'] : array();
    $arm_all_appearance_settings = isset($arm_all_member_panel_settings['appearance_settings']) ? $arm_all_member_panel_settings['appearance_settings'] : array();

    $arm_lite_allowed_tab_id = ['arm_member_subscription','arm_transaction','arm_edit_profile','arm_close_account','arm_change_password'];

?>

<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>

<div class="arm_global_settings_main_wrapper">
    <div class="page_sub_content">
        <div class="page_sub_title arm_margin_bottom_32">
            <?php esc_html_e('Member Panel','armember-membership');?>
        </div>
        <form method="post" action="#" id="arm_member_panel_settings_form" class="arm_member_panel_settings arm_admin_form">
            <div class="arm_padding_0 arm_margin_top_32">
                <div class="arm_row_wrapper arm_row_wrapper_padding_before arm_margin_bottom_28 ">
                    <div class="left_content">
                        <div class="arm_form_header_label arm-setting-hadding-label" id="arm_member_panel_settings_tab_title">
                            <?php esc_html_e('Tab Settings','armember-membership');?>
                        </div>
                    </div>
                    <?php if($ARMemberLite->is_arm_pro_active): ?>
                        <div class="right_content">
                            <div class="arm_add_member_panel_tab_btn_container">
                                <button id="arm_add_new_member_panel_tab_button" class="arm_add_new_member_panel_tab_button armemailaddbtn" type="button"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7V17M7 12H17" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span><?php esc_html_e('Add Custom Tab', 'armember-membership'); ?></span></button>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
                <div class="arm_member_panel_tab_main_wrapper">
                        <div class="arm_member_panel_tab_container ui-sortable arm_margin_bottom_32">
                            <?php
                                if(!empty($arm_all_tab_settings)){
                                    $count_total_tab = 0;

                                    foreach ($arm_all_tab_settings as $tab_index => $tab_data) {
                                        $tab_id = $tab_data['id'];
                                        $is_allowed_tab = false;

                                        if (in_array($tab_id, $arm_lite_allowed_tab_id)) {
                                            $is_allowed_tab = true;
                                        } else if($ARMemberLite->is_arm_pro_active){
                                            $is_allowed_tab = apply_filters('arm_is_member_panel_tab_allowed',false,$tab_id,);
                                        }

                                        if ($is_allowed_tab) {
                                            $arm_global_settings->arm_get_data_for_display_tab($tab_data, $tab_index);
                                        }

                                        $count_total_tab++;
                                    }
                                }
                            ?>
                        </div>
                        <div class="arm_row_wrapper arm_row_wrapper_padding_before arm_margin_bottom_28 ">
                            <div id="front_end_appearance_sec" class="arm_settings_section" >
                                <?php
                                    global $arm_global_settings, $arm_member_forms, $ARMemberLite;

                                    $all_default_member_panel_setting = $arm_global_settings->arm_default_member_panel_settings();
                                    $arm_default_front_settings  = $all_default_member_panel_setting['appearance_settings'];

                                    $frontfontOptions = array(
                                        'primary_color'         => esc_html__('Primary Color', 'armember-membership'),
                                        'panel_sidebar_color'   => esc_html__('Panel Sidebar Color', 'armember-membership'),
                                        'panel_background_color'=> esc_html__('Panel Background Color', 'armember-membership'),
                                        'border_color'          => esc_html__('Border Color', 'armember-membership'),
                                        'title_text_color'      => esc_html__('Title Text Color', 'armember-membership'),
                                        'content_color'         => esc_html__('Content Color', 'armember-membership'),
                                    );

                                    $frontfontOptions = apply_filters('arm_front_font_settings_type', $frontfontOptions);

                                    if (!empty($frontfontOptions)) : 

                                        $frontOptions = isset($arm_all_appearance_settings['color']) ? $arm_all_appearance_settings['color'] : $arm_default_front_settings['color'];
                                        $frontFontVal = isset($arm_all_appearance_settings['font']['font_family']) ? sanitize_text_field($arm_all_appearance_settings['font']['font_family']) : 'Poppins';
                                        ?>
                                        
                                        <div class="arm_form_header_label arm-setting-hadding-label"><?php echo esc_html__('Appearance', 'armember-membership'); ?></div>
                                        
                                        <div class="arm_setting_main_content arm_padding_0 arm_margin_top_32 arm_margin_bottom_32">
                                            <div class="form-field arm_width_100_pct">
                                        
                                                <div class="arm_row_wrapper arm_row_wrapper_padding_before arm_padding_bottom_24">
                                                    <div class="left_content">
                                                        <div class="arm-form-table-label arm_form_header_label arm-setting-hadding-label arm_margin_bottom_0 arm_width_100_pct"><?php echo esc_html__('Member Panel Appearance', 'armember-membership'); ?></div>
                                                    </div>
                                        
                                                    <div class="right_content">
                                                        <div class="arm_front_end_appearance_reset_buton_container">
                                                            <button id="arm_global_settings_reset_btn" class="arm_front_end_appearance_reset_btn" onclick="showConfirmBoxCallback('front_end_appearance')" name="arm_global_settings_reset_btn" type="button"><?php echo esc_html__('Reset to Default', 'armember-membership'); ?></button>
                                                        </div>
                                        
                                                        <div class="arm_front_end_appearance_reset_buton_confirm_box_container">
                                                            <?php echo $arm_global_settings->arm_get_confirm_box('front_end_appearance', esc_html__('Are you sure you want to reset member panel appearance?', 'armember-membership'), 'arm_front_end_appearance_reset_confirm_btn', '', esc_html__('Reset', 'armember-membership'), esc_html__('Cancel', 'armember-membership'), esc_html__('Reset to Default', 'armember-membership')); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                        
                                                <div class="arm_content_border arm_width_100_pct"></div>
                                        
                                                <div class="arm-form-table-content arm_row_wrapper arm_row_wrapper_padding_after arm_display_block arm_front_end_appearance_panel_color_container">
                                                    <div class="left_content arm_margin_bottom_24">
                                                        <div class="arm-form-table-label arm_form_header_label arm-setting-hadding-label arm_font_size_16 arm_margin_bottom_0 arm_width_100_pct"><?php echo esc_html__('Color Setting', 'armember-membership'); ?></div>
                                                    </div>
                                        
                                                    <div class="arm_form_field_block arm_display_flex arm_font_color_wrapper">
                                                        <?php foreach ($frontfontOptions as $key => $title) : 
                                                            $fontVal = !empty($frontOptions[$key]) ? sanitize_text_field($frontOptions[$key]) : $arm_default_front_settings['color'][$key];
                                                        ?>
                                                        <div class="arm_font_color_item">
                                                            <div class="arm_front_end_appearance_color_block">
                                                                <div class="arm_front_font_color arm_margin_right_0">
                                                                    <input type="text" autocomplete="off" id="arm_front_font_color_<?php echo esc_attr($key); ?>" name="member_panel_settings[appearance_settings][color][<?php echo esc_attr($key); ?>]" class="arm_colorpicker" value="<?php echo esc_attr($fontVal); ?>">
                                                                </div>
                                                                <div class="arm-form-table-label arm_padding_left_0 arm_font_size_15 arm_panel_color_label"><?php echo esc_html($title); ?></div>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                        
                                                <div class="arm-form-table-content arm_row_wrapper arm_row_wrapper_padding_after arm_display_block arm_padding_top_0">
                                                    <div class="left_content arm_margin_bottom_24">
                                                        <div class="arm-form-table-label arm_form_header_label arm-setting-hadding-label arm_font_size_16 arm_margin_bottom_0 arm_width_100_pct"><?php echo esc_html__('Font Setting', 'armember-membership'); ?></div>
                                                    </div>
                                        
                                                    <label class="arm-form-table-label"><?php echo esc_html__('Select Font', 'armember-membership'); ?></label>
                                        
                                                    <div>
                                                        <input type="hidden" id="arm_front_font_family" name="member_panel_settings[appearance_settings][font][font_family]" value="<?php echo esc_attr($frontFontVal); ?>">
                                                    </div>
                                        
                                                    <dl class="arm_selectbox column_level_dd arm_width_362 arm_margin_right_10 arm_margin_top_12">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_front_font_family">
                                                                <?php echo $arm_member_forms->arm_fonts_list(); ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                        
                                            </div>
                                        </div>
                                        
                                        <?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section', 'custom_css') : ''; ?>
                                        
                                        <?php endif; ?>
                            </div>
                        </div>
                        <div class="arm_submit_btn_container arm_apply_changes_btn_container">
                            <input type="hidden" name="arm_total_member_panel_tab" id="arm_total_member_panel_tab" value="<?php echo esc_html($count_total_tab) ?>">
                            <input type="hidden" name="arm_order_member_panel_tab" id="arm_order_member_panel_tab" value="<?php echo esc_html($count_total_tab) ?>">
                            <img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_member_panel_settings_btn" id="arm_member_panel_tab_settings_btn" type="submit" name="arm_member_panel_settings_btn"><?php esc_html_e('Apply Changes', 'armember-membership'); ?></button>
                            <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
                            <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
                        </div>
            </div>
        </form>
        <div class="arm_custom_css_detail_container"></div>
        <div id="arm_sample_member_panel_delete_confirm_box" style="display: none;">
            <?php
                $arm_sample_remove_tab_callback = $arm_global_settings->arm_get_confirm_box('SAMPLE_ID',esc_html__('Are you sure you want to delete member panel tab?', 'armember-membership'),'arm_remove_member_panle_tab_confirm_btn','',esc_html__('Delete', 'armember-membership'),esc_html__('Cancel', 'armember-membership'),esc_html__('Delete Member Panel Tab', 'armember-membership'));
                echo $arm_sample_remove_tab_callback;
            ?>
        </div>
        <div id="arm_sample_editor_wrapper" style="display:none;">
            <?php
                wp_editor(
                    '',
                    'arm_tab_editor_SAMPLE_ID',
                    array(
                        'textarea_name' => '',
                        'media_buttons' => false,
                        'textarea_rows' => 10,
                        'tinymce'       => false,
                        'quicktags'     => true,
                    )
                );
            ?>
        </div>
    </div>
</div>

<script>
    var ARM_MPT_TITLE = '<?php echo addslashes( esc_html__( 'Title', 'armember-membership' ) ); //phpcs:ignore ?>';
    var ARM_MPT_TYPE = '<?php echo addslashes( esc_html__( 'Type', 'armember-membership' ) ); //phpcs:ignore ?>';
    var ARM_MPT_CONTENT = '<?php echo addslashes( esc_html__( 'Content', 'armember-membership' ) ); //phpcs:ignore ?>';
    var ARM_MPT_URL = '<?php echo addslashes( esc_html__( 'URL', 'armember-membership' ) ); //phpcs:ignore ?>';
    var ARM_MPT_ENTER_URL = '<?php echo addslashes( esc_html__( 'Enter URL', 'armember-membership' ) ); //phpcs:ignore ?>';
    var ARM_MPT_ENABLE_TAB = '<?php echo addslashes( esc_html__( 'Enable Tab', 'armember-membership' ) ); //phpcs:ignore ?>';
    var ARM_MPT_URL_IN_NEW_TAB = '<?php echo addslashes( esc_html__( 'Open URL in the new tab', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_MPT_COMMON_ERR_MSG = '<?php echo addslashes( esc_html__( 'This field is required.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_MPT_INVALID_URL_ERR = '<?php echo addslashes( esc_html__( 'Please enter a valid URL.', 'armember-membership' ) ); //phpcs:ignore ?>';
</script>