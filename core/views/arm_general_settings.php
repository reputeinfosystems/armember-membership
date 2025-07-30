<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_slugs, $arm_social_feature;
$active = 'arm_general_settings_tab_active';

$g_action = isset( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : 'general_settings'; //phpcs:ignore


?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
	<div class="content_wrapper arm_global_settings_content" id="content_wrapper">
		<div class="page_title"><?php esc_html_e( 'General Settings', 'armember-membership' ); ?></div>
		<div class="armclear"></div>
		<div class="armember_general_settings_wrapper">
			<div class="arm_general_settings_tab_wrapper">
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'general_settings' ) ? esc_attr($active) : ''; ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings ) ); //phpcs:ignore ?>"><?php esc_html_e( 'General Options', 'armember-membership' ); ?></a>
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'payment_options' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options' ) ); //phpcs:ignore ?>"><?php esc_html_e( 'Payment Gateways', 'armember-membership' ); ?></a>
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'page_setup' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=page_setup' ) ); //phpcs:ignore ?>"><?php esc_html_e( 'Page Setup', 'armember-membership' ); ?></a>
			   
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'access_restriction' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=access_restriction' )); //phpcs:ignore ?>"><?php esc_html_e( 'Default Restriction Rules', 'armember-membership' ); ?></a>
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'block_options' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=block_options' )); //phpcs:ignore ?>"><?php esc_html_e( 'Security Options', 'armember-membership' ); ?></a>
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'import_export' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=import_export' )); //phpcs:ignore ?>"><?php esc_html_e( 'Import / Export', 'armember-membership' ); ?></a>
		   
			   
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'redirection_options' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=redirection_options' )); //phpcs:ignore ?>"><?php esc_html_e( 'Redirection Rules', 'armember-membership' ); ?></a>
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'common_messages' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=common_messages' )); //phpcs:ignore ?>"><?php esc_html_e( 'Common Messages', 'armember-membership' ); ?></a>
				<a class="arm_general_settings_tab <?php echo ( $g_action == 'debug_logs' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=debug_logs' )); //phpcs:ignore ?>"><?php esc_html_e( 'Debug Log Settings', 'armember-membership' ); ?></a>
			   
				<div class="armclear"></div>
			</div>
			<div class="arm_settings_container">
				<?php
					/* if you add any new tab than reset the min height of the box other wise last menu not display in page setup page. */
					$arm_setting_title   = esc_html__( 'General Options', 'armember-membership' );
					$arm_setting_tooltip = '';
					$file_path           = MEMBERSHIPLITE_VIEWS_DIR . '/arm_global_settings.php';
				switch ( $g_action ) {
					case 'payment_options':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_payment_gateways.php';
							$arm_setting_title = esc_html__( 'Payment Gateways', 'armember-membership' );
						break;
					case 'page_setup':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_page_setup.php';
							$arm_setting_title = esc_html__( 'Page Setup', 'armember-membership' );
						break;

					case 'block_options':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_block_settings.php';
							$arm_setting_title = esc_html__( 'Security Options', 'armember-membership' );
						break;
					case 'import_export':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_import_export.php';
							$arm_setting_title = esc_html__( 'Import / Export', 'armember-membership' );
						break;
						
					case 'redirection_options':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_redirection_settings.php';
							$arm_setting_title = esc_html__( 'Page/Post Redirection Rules', 'armember-membership' );
						break;
					case 'common_messages':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_common_messages_settings.php';
							$arm_setting_title = esc_html__( 'Common Messages', 'armember-membership' );
						break;

					case 'access_restriction':
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_restriction_settings.php';
							$arm_setting_title = esc_html__( 'Default Restriction Rules', 'armember-membership' );
						break;
					case 'debug_logs':
						$file_path = MEMBERSHIPLITE_VIEWS_DIR . '/arm_debug_logs.php';
						$arm_setting_title = esc_html__('Debug Log Settings', 'armember-membership');
						break;
					default:
							$file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_global_settings.php';
							$arm_setting_title = esc_html__( 'General Options', 'armember-membership' );
						break;
				}
				if ( file_exists( $file_path ) ) {
					?>
							<div class="arm_settings_title_wrapper">
								<div class="arm_setting_title"><?php echo esc_html($arm_setting_title) . ' ' . esc_html($arm_setting_tooltip); ?></div>
							</div>
							<?php
							include $file_path;
				}
				?>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('arm_'.$g_action); //phpcs:ignore
?>