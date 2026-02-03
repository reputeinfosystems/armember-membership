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
			<a class="arm_all_standard_tab arm_selected_email_tab"  href="<?php echo admin_url( 'admin.php?page=' . $arm_slugs->email_notifications);?>"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 7.00002C15 5.58553 14.4732 4.22898 13.5355 3.22878C12.5979 2.22859 11.3261 1.66669 10 1.66669C8.67392 1.66669 7.40215 2.22859 6.46447 3.22878C5.52678 4.22898 5 5.58553 5 7.00002C5 13.2222 2.5 15 2.5 15H17.5C17.5 15 15 13.2222 15 7.00002Z" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.4413 17.5C11.2948 17.7526 11.0845 17.9622 10.8315 18.1079C10.5784 18.2537 10.2916 18.3304 9.9996 18.3304C9.70762 18.3304 9.42076 18.2537 9.16775 18.1079C8.91474 17.9622 8.70445 17.7526 8.55794 17.5" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php esc_html_e('Standard','armember-membership');?></a>
			<a class="arm_all_advanced_tab" href="<?php echo admin_url( 'admin.php?page=' . $arm_slugs->email_notifications . '&action=advanced_email' );?>"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.1113 9.16669C15.5963 13.6463 17.5 15 17.5 15H2.5C2.5 15 5 13.2222 5 7.00002C5 5.58553 5.52678 4.22898 6.46447 3.22878C7.40215 2.22859 8.67392 1.66669 10 1.66669C10.2811 1.66669 10.5598 1.69194 10.8333 1.74126" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.8333 6.66669C17.2141 6.66669 18.3333 5.5474 18.3333 4.16669C18.3333 2.78598 17.2141 1.66669 15.8333 1.66669C14.4526 1.66669 13.3333 2.78598 13.3333 4.16669C13.3333 5.5474 14.4526 6.66669 15.8333 6.66669Z" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.4413 17.5C11.2948 17.7526 11.0845 17.9622 10.8315 18.1079C10.5785 18.2537 10.2916 18.3304 9.99962 18.3304C9.70764 18.3304 9.42078 18.2537 9.16776 18.1079C8.91475 17.9622 8.70446 17.7526 8.55795 17.5" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php esc_html_e('Advanced','armember-membership');?></a>
            
        </div>
		<?php }?>
		
		<div class="arm_general_settings_wrapper">
			<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>
			<div class="arm_settings_container arm_padding_0" style="border-top: 0px;">
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