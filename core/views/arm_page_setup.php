<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature;
$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$page_settings           = $arm_all_global_settings['page_settings'];
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<div class="page_sub_title arm_margin_bottom_32">
			<?php esc_html_e('Page Setup','armember-membership');?>
		</div>
		<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
			<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
				<div class="left_content">
					<div class="arm_form_header_label arm-setting-hadding-label">
					<?php esc_html_e('Map default pages for all common actions','armember-membership');?></div>
				</div>
			</div>
			<div class="arm_content_border"></div>

			<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block arm_padding_top_24 ">
				<form method="post" action="#" id="arm_page_settings" class="arm_page_settings ">
					<div class="arm_page_setup_flex_group">
						 
						<div class="arm_form_field_block">
							<label class="arm-form-table-label"><?php esc_html_e( 'Registration Page', 'armember-membership' ); ?></label>
							
							<span data-type="registration" class="arm_page_type"></span>
							
							<?php
							$page_settings['register_page_id'] = isset( $page_settings['register_page_id'] ) ? $page_settings['register_page_id'] : 0;
							$is_valid_reg_page                 = $arm_global_settings->arm_shortcode_exist_in_page( 'registration', $page_settings['register_page_id'] );
							
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'          => $page_settings['register_page_id'],
									'name'              => 'arm_page_settings[register_page_id]',
									'id'                => 'register_page_id',
									'show_option_none'  => esc_html__( 'Select Page', 'armember-membership' ),
									'option_none_value' => '0',
									'class'             => 'arm_page_setup_input',
								)
							);
							?>
							
							<div class="arm_icon_status_wrap">
								<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
								<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							</div>
							
							<span class="arm_error_msg <?php echo ( $is_valid_reg_page ) ? 'arm_no_error' : ''; ?>">
								<?php esc_html_e( 'Shortcode of Registration Form not found on selected page. Please add shortcode on that page Or please select appropriate page.', 'armember-membership' ); ?>
							</span>
						</div>
		
						<div class="arm_form_field_block">
							<label class="arm-form-table-label"><?php esc_html_e( 'Login Page', 'armember-membership' ); ?></label>
		
							<span data-type="login" class="arm_page_type"></span>
		
							<?php
							$page_settings['login_page_id'] = isset( $page_settings['login_page_id'] ) ? $page_settings['login_page_id'] : 0;
							$is_valid_login_page            = $arm_global_settings->arm_shortcode_exist_in_page( 'login', $page_settings['login_page_id'] );
		
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'          => $page_settings['login_page_id'],
									'name'              => 'arm_page_settings[login_page_id]',
									'id'                => 'login_page_id',
									'show_option_none'  => esc_html__( 'Select Page', 'armember-membership' ),
									'option_none_value' => '0',
									'class'             => 'arm_page_setup_input',
								)
							);
							?>
		
							<div class="arm_icon_status_wrap">
								<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
								<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							</div>
		
							<span class="arm_error_msg <?php echo ( $is_valid_login_page ) ? 'arm_no_error' : ''; ?>">
								<?php esc_html_e( 'Shortcode of Login Form not found on selected page. Please add shortcode on that page Or please select appropriate page.', 'armember-membership' ); ?>
							</span>
						</div>
						
						<div class="arm_form_field_block">
							<label class="arm-form-table-label"><?php esc_html_e( 'Forgot Password Page', 'armember-membership' ); ?></label>
		
							<span data-type="forgot_password" class="arm_page_type"></span>
		
							<?php
							$page_settings['forgot_password_page_id'] = isset( $page_settings['forgot_password_page_id'] ) ? $page_settings['forgot_password_page_id'] : 0;
							$is_valid_fp_page                         = $arm_global_settings->arm_shortcode_exist_in_page( 'forgot_password', $page_settings['forgot_password_page_id'] );
		
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'          => $page_settings['forgot_password_page_id'],
									'name'              => 'arm_page_settings[forgot_password_page_id]',
									'id'                => 'forgot_password_page_id',
									'show_option_none'  => esc_html__( 'Select Page', 'armember-membership' ),
									'option_none_value' => '0',
									'class'             => 'arm_page_setup_input',
								)
							);
							?>
		
							<div class="arm_icon_status_wrap">
								<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
								<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							</div>
		
							<span class="arm_error_msg <?php echo ( $is_valid_fp_page ) ? 'arm_no_error' : ''; ?>">
								<?php esc_html_e( 'Shortcode of Forgot Password Form not found on selected page. Please add shortcode on that page Or please select appropriate page.', 'armember-membership' ); ?>
							</span>
						</div>
		
						<div class="arm_form_field_block">
							<label class="arm-form-table-label"><?php esc_html_e( 'Change Password Page', 'armember-membership' ); ?></label>
		
							<span data-type="change_password" class="arm_page_type"></span>
		
							<?php
							$page_settings['change_password_page_id'] = isset( $page_settings['change_password_page_id'] ) ? $page_settings['change_password_page_id'] : 0;
							$is_valid_cp_page                         = $arm_global_settings->arm_shortcode_exist_in_page( 'change_password', $page_settings['change_password_page_id'] );
		
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'          => $page_settings['change_password_page_id'],
									'name'              => 'arm_page_settings[change_password_page_id]',
									'id'                => 'change_password_page_id',
									'show_option_none'  => esc_html__( 'Select Page', 'armember-membership' ),
									'option_none_value' => '0',
									'class'             => 'arm_page_setup_input',
								)
							);
							?>
		
							<div class="arm_icon_status_wrap">
								<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
								<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							</div>
		
							<span class="arm_error_msg <?php echo ( $is_valid_cp_page ) ? 'arm_no_error' : ''; ?>">
								<?php esc_html_e( 'Shortcode of Change Password Form not found on selected page. Please add shortcode on that page Or please select appropriate page.', 'armember-membership' ); ?>
							</span>
						</div>
						
						<div class="arm_form_field_block">
							<label class="arm-form-table-label"><?php esc_html_e( 'Edit Profile Page', 'armember-membership' ); ?></label>
		
							<span data-type="edit_profile" class="arm_page_type"></span>
		
							<?php
							$page_settings['edit_profile_page_id'] = isset( $page_settings['edit_profile_page_id'] ) ? $page_settings['edit_profile_page_id'] : 0;
							$is_valid_ep_page                      = $arm_global_settings->arm_shortcode_exist_in_page( 'edit_profile', $page_settings['edit_profile_page_id'] );
		
							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'          => $page_settings['edit_profile_page_id'],
									'name'              => 'arm_page_settings[edit_profile_page_id]',
									'id'                => 'edit_profile_page_id',
									'show_option_none'  => esc_html__( 'Select Page', 'armember-membership' ),
									'option_none_value' => '0',
									'class'             => 'arm_page_setup_input',
								)
							);
							?>
		
							<div class="arm_icon_status_wrap">
								<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
								<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							</div>
		
							<span class="arm_error_msg <?php echo ( $is_valid_ep_page ) ? 'arm_no_error' : ''; ?>">
								<?php esc_html_e( 'Shortcode of Edit Profile Form not found on selected page. Please add shortcode on that page Or please select appropriate page.', 'armember-membership' ); ?>
							</span>
						</div>
			
						<div class="arm_form_field_block" style="<?php echo ( ! $arm_social_feature->isSocialFeature ) ? 'display:none;' : ''; ?>">
							<label class="arm-form-table-label"><?php esc_html_e( 'Members Profile Page', 'armember-membership' ); ?></label>

							<span data-type="members_directory" class="arm_page_type"></span>

							<?php
							$page_settings['member_profile_page_id'] = isset( $page_settings['member_profile_page_id'] ) ? $page_settings['member_profile_page_id'] : 0;
							$is_valid_md_page                        = $arm_global_settings->arm_shortcode_exist_in_page( 'members_directory', $page_settings['member_profile_page_id'] );

							$arm_global_settings->arm_wp_dropdown_pages(
								array(
									'selected'          => $page_settings['member_profile_page_id'],
									'name'              => 'arm_page_settings[member_profile_page_id]',
									'id'                => 'member_profile_page_id',
									'show_option_none'  => esc_html__( 'Select Page', 'armember-membership' ),
									'option_none_value' => '0',
									'class'             => 'arm_page_setup_input',
								)
							);
							?>

							<div class="arm_icon_status_wrap">
								<i class="armfa armfa-1x armfa-refresh armfa-spin arm_refresh arm_no_error"></i>
								<i class="armfa armfa-1x armfa-check arm_check arm_no_error"></i>
							</div>

							<span class="arm_error_msg <?php echo ( $is_valid_md_page ) ? 'arm_no_error' : ''; ?>">
								<?php esc_html_e( 'Shortcode of Member Directory not found on selected page. Please add shortcode on that page Or please select appropriate page.', 'armember-membership' ); ?>
							</span>
						</div>	
						
					
							<?php if($ARMemberLite->is_arm_pro_active){
								echo apply_filters('arm_load_page_setup_section','paid_post');	//phpcs:ignore
								do_action('arm_page_setup_section');
							}  ?>
					</div>
					<?php do_action( 'arm_after_page_settings_html', $page_settings ); ?>
				</form>
			</div>
		</div>
		<div class="armclear"></div>
			
		<div class="arm_submit_btn_container arm_apply_changes_btn_container">
			<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn" type="submit" id="arm_page_setup_btn" name="arm_global_settings_btn"><?php esc_html_e( 'Apply Changes', 'armember-membership' ); ?></button>
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
			<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</div>
	</div>
</div>
