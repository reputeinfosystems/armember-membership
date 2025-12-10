<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_slugs,$arm_common_lite;
$active = 'arm_general_settings_tab_active';

$_r_action = isset( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : 'email_notification'; //phpcs:ignore
?>
<div class="wrap arm_page arm_general_settings_main_wrapper arm_email_notification_main_wrapper">
	<div class="content_wrapper arm_global_settings_content" id="content_wrapper">
		<div class="page_title arm_margin_0"><?php esc_html_e( 'Email Notifications', 'armember-membership' ); ?></div>
		<?php if($ARMemberLite->is_arm_pro_active){?>
		<div class="arm_email_notification_tabs">
            <input type="hidden" id="arm_selected_email_tab" value="standard"/>
            <div class="arm_all_standard_tab arm_selected_email_tab">
                <?php esc_html_e('Standard','armember-membership');?>
            </div>
            <a class="arm_all_advanced_tab" href="<?php echo admin_url( 'admin.php?page=' . $arm_slugs->email_notifications . '&action=advanced_email' );?>">
                <?php esc_html_e('Advanced','armember-membership');?>
			</a>
            
        </div>
		<?php }?>
		<div class="arm_page_spacing_div"></div>
		<div class="armclear"></div>
		<div class="arm_general_settings_wrapper">
			<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>
			<div class="arm_settings_container" style="border-top: 0px;">
				<?php
				if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_templates.php' ) ) {
					include MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_templates.php';
				}
							
				?>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('email-notification-list'); //phpcs:ignore
?>