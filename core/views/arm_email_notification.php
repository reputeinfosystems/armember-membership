<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_slugs;
$active = 'arm_general_settings_tab_active';

$_r_action = isset( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : 'email_notification'; //phpcs:ignore
?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
<?php
	if($ARMemberLite->is_arm_pro_active)
	{
		$arm_license_notice = '';
		echo apply_filters('arm_admin_license_notice_html',$arm_license_notice);  //phpcs:ignore
	}
	?>
	<div class="content_wrapper arm_global_settings_content" id="content_wrapper">
		<div class="page_title arm_margin_0"><?php esc_html_e( 'Email Notification', 'armember-membership' ); ?></div>
		<div class="armclear"></div>
		<div class="arm_general_settings_wrapper">
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