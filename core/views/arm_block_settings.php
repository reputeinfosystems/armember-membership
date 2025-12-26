<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans;
$arm_all_block_settings                          = $arm_global_settings->arm_get_all_block_settings();
$all_plans                                       = $arm_subscription_plans->arm_get_all_subscription_plans( 'all', ARRAY_A, true );
$failed_login_users                              = $arm_members_class->arm_get_failed_login_users();
$arm_all_block_settings['failed_login_lockdown'] = isset( $arm_all_block_settings['failed_login_lockdown'] ) ? $arm_all_block_settings['failed_login_lockdown'] : 0;
$arm_all_block_settings['max_login_retries']     = isset( $arm_all_block_settings['max_login_retries'] ) ? $arm_all_block_settings['max_login_retries'] : 5;
$arm_all_block_settings['temporary_lockdown_duration']  = isset( $arm_all_block_settings['temporary_lockdown_duration'] ) ? $arm_all_block_settings['temporary_lockdown_duration'] : 10;
$arm_all_block_settings['permanent_login_retries']      = isset( $arm_all_block_settings['permanent_login_retries'] ) ? $arm_all_block_settings['permanent_login_retries'] : 15;
$arm_all_block_settings['permanent_lockdown_duration']  = isset( $arm_all_block_settings['permanent_lockdown_duration'] ) ? $arm_all_block_settings['permanent_lockdown_duration'] : 24;
$arm_all_block_settings['remained_login_attempts']      = isset( $arm_all_block_settings['remained_login_attempts'] ) ? $arm_all_block_settings['remained_login_attempts'] : 0;
$arm_all_block_settings['track_login_history']          = isset( $arm_all_block_settings['track_login_history'] ) ? $arm_all_block_settings['track_login_history'] : 1;
$arm_all_block_settings['arm_block_ips']                = isset( $arm_all_block_settings['arm_block_ips'] ) ? $arm_all_block_settings['arm_block_ips'] : '';
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<form  method="post" action="#" id="arm_block_settings" class="arm_block_settings arm_admin_form">
			
			<div class="page_sub_title arm_margin_bottom_32"><?php esc_html_e('Security Options','armember-membership');
			$after_title_content = "";
			$after_title_content = apply_filters('arm_after_general_settings_title', $after_title_content); //phpcs:ignore
			echo $after_title_content; //phpcs:ignore ?>
			</div>
			<div class="arm_setting_main_content">
				<div class="arm_row_wrapper">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Login attempts Security', 'armember-membership' ); ?></div>
						<div class="arm_global_setting_label"><?php esc_html_e( 'Enable login security option for failed login attempts.', 'armember-membership' ); ?></div>
						
					</div>
					<div class="right_content">
						<div class="armswitch arm_global_setting_switch arm_margin_0">
							<input type="checkbox" id="failed_login_lockdown" value="1" class="armswitch_input" name="arm_block_settings[failed_login_lockdown]" <?php checked( $arm_all_block_settings['failed_login_lockdown'], 1 ); ?>/>
							<label for="failed_login_lockdown" class="armswitch_label"></label>
						</div>
					</div>
				</div>
				<div class="arm_global_settings_sub_content failed_login_lockdown <?php echo ( $arm_all_block_settings['failed_login_lockdown'] == 1 ) ? '' : 'hidden_section'; ?>">
				<div class="arm_content_border arm_margin_top_24"></div>

				<div class="arm_email_setting_flex_group arm_margin_top_24 arm_security_options">
					<div class="arm_form_field_block arm_max_width_360">
						<label class="arm-form-table-label payment_label"><span class="arm_failed_login_before_label"><?php esc_html_e( 'Maximum Number of login attempts', 'armember-membership' ); ?></span></label>	
						<input  type="text" id="max_login_retries" value="<?php echo intval($arm_all_block_settings['max_login_retries']); ?>" class="arm_general_input arm_margin_top_12 arm_width_100_pct"  name="arm_block_settings[max_login_retries]" onkeypress="return isNumber(event)" />
						<span class="arm_max_login_retries_error arm_error_msg"style="display: none;" ><?php esc_html_e( 'Please enter maximum number of login attempts.', 'armember-membership' ); ?> </span>
					</div>

					<div class="arm_form_field_block arm_max_width_360">
						<label class="arm-form-table-label payment_label"><span class="arm_failed_login_before_label"><?php esc_html_e( 'Lock user temporarily for', 'armember-membership' ); ?></span></label>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'After maximum failed login attempts user will be inactive for given minutes. During this time, user will not be able to login into the system.', 'armember-membership' ); ?>" style="margin-top: 0px !important;"></i>
						<br/>
						<span class="arm_temporary_lockdown_duration_error arm_error_msg" style="display:none;"> <?php esc_html_e( 'Please enter temporarily lock user duration.', 'armember-membership' ); ?></span>
						<div class="input-with-suffix">
						<input  type="text" id="temporary_lockdown_duration" value="<?php echo intval($arm_all_block_settings['temporary_lockdown_duration']); ?>" class="arm_general_input arm_margin_top_12 arm_width_100_pct"  name="arm_block_settings[temporary_lockdown_duration]" onkeypress="return isNumber(event)" />
						<span class="arm_failed_login_after_label suffix"><?php esc_html_e( 'Minutes', 'armember-membership' ); ?></span>
						</div>
					</div>
				</div>

				<div class="arm_form_header_label arm-setting-hadding-label arm_margin_top_32"><?php esc_html_e( 'Advanced Security', 'armember-membership' ); ?></div>
				
				<div class="arm_email_setting_flex_group arm_margin_top_24 arm_security_options">
					<div class="arm_form_field_block arm_max_width_360">
						<label class="arm-form-table-label payment_label"><span class="arm_failed_login_before_label"><?php esc_html_e( 'Permanent lock user after login attempts', 'armember-membership' ); ?></span></label>	
						<input  type="text" id="permanent_login_retries" value="<?php echo intval($arm_all_block_settings['permanent_login_retries']); ?>" class="arm_general_input arm_margin_top_12 arm_width_100_pct"  name="arm_block_settings[permanent_login_retries]" onkeypress="return isNumber(event)" />
						<span class="arm_permanent_login_retries_error arm_error_msg" style="display:none;"><?php esc_html_e( 'Please enter number of login attempts after user permanent lock.', 'armember-membership' ); ?></span>
					</div>

					<div class="arm_form_field_block arm_max_width_360">
						<label class="arm-form-table-label payment_label"><span class="arm_failed_login_before_label"><?php esc_html_e( 'Permanent lockdown Duration', 'armember-membership' ); ?></span></label>
						<div class="input-with-suffix">
						<input  type="text" id="permanent_lockdown_duration" value="<?php echo intval($arm_all_block_settings['permanent_lockdown_duration']); ?>" class="arm_general_input arm_margin_top_12 arm_width_100_pct"  name="arm_block_settings[permanent_lockdown_duration]" onkeypress="return isNumber(event)" />
						<span class="arm_failed_login_after_label suffix ">&nbsp;<?php esc_html_e( 'Hours', 'armember-membership' ); ?></span>
						<span class="arm_permanent_lockdown_duration_error arm_error_msg" style="display:none;"> <?php esc_html_e( 'Please enter permanent lockdown duration.', 'armember-membership' ); ?></span>
						</div>
					</div>
				</div>

				
					<div class="arm_form_header_label arm-setting-hadding-label arm_margin_top_32"><?php esc_html_e( 'Failed Login Attempt Login History', 'armember-membership' ); ?></div>
					<div class="arm_form_field_block">
						<label class="arm-form-table-label payment_label arm_margin_top_24"><span class="arm_failed_login_before_label "><?php esc_html_e( 'Reset Failed Login Attempts History', 'armember-membership' ); ?></span></label>
						<div class="arm_display_flex arm_margin_top_12 "> 
							
							<dl class="arm_max_width_95_pct arm_width_100_pct arm_margin_right_16 arm_margin_0">

								<select id="arm_reset_login_attempts_users" class="arm_chosen_selectbox" name="arm_general_settings[arm_exclude_role_for_restrict_admin][]" data-placeholder="<?php esc_attr_e( 'Select User(s)..', 'armember-membership' ); ?>" multiple="multiple"  >
									<?php
									if ( ! empty( $failed_login_users ) ) :
									?>
											<option class="arm_message_selectbox_op" value="all" >All Users</option>
									<?php
											foreach ( $failed_login_users as $user ) {
									?>
												<option class="arm_message_selectbox_op" value="<?php echo esc_attr( $user['ID'] ); ?>"><?php echo !empty($user['user_login']) ? stripslashes( $user['user_login'] ) : ''; //phpcs:ignore ?></option>
									<?php
											}
										else :
									?>
									<option value="" disabled="true"><?php esc_html_e( 'No Users Available', 'armember-membership' ); ?></option><?php endif; ?>
								</select>
							</dl>
							<input id="doaction1" class="armbulkbtn arm_reset_btn arm_height_40 arm_clear_login_user_btn" value="Reset" type="button"  onclick="showConfirmBoxCallback('arm_clear_login_user');">

							<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_reset_loader_img" style="position:relative;top:0px;display:none; margin-left:10px;" width="24" height="24" />
						</div>		
						<?php echo $arm_global_settings->arm_get_confirm_box( 'arm_clear_login_user', esc_html__( 'Are you sure want to reset login attempts for the selected member?', 'armember-membership' ), 'arm_reset_user_login_attempts','',esc_html__("Reset", 'armember-membership'),esc_html__("Cancel", 'armember-membership'),esc_html__("Reset Login Attempts", 'armember-membership') ); //phpcs:ignore ?>	
						<div class="arm_position_relative arm_margin_top_12 arm_failed_history">
							<a href="javascript:void(0)" id="arm_failed_login_attempts_history" class="arm_failed_login_attempts_history arm_ref_info_links"><?php esc_html_e( 'View Failed Login Attempts History', 'armember-membership' ); ?></a>
						</div>	
						<span class="arm_info_text arm_info_text_style arm-note-message --attempt arm_margin_top_15"><?php esc_html_e( 'Note', 'armember-membership' ); ?>: <?php esc_html_e( ' Failed login attempt history will automatically be cleared which is older than 30 days.', 'armember-membership' ); ?></span>

						<div class="arm_content_border arm_margin_top_24"></div>
						
						<div class="arm_row_wrapper arm_margin_top_24 arm_global_settings_sub_content failed_login_lockdown <?php echo ( $arm_all_block_settings['failed_login_lockdown'] == 1 ) ? '' : 'hidden_section'; ?>">
							<div class="left_content">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Remaining login attempt warning', 'armember-membership' ); ?></div>
								<div class="arm_global_setting_label"><?php esc_html_e( 'Display remaining login attempts warning message.', 'armember-membership' ); ?></div>
								
							</div>
							<div class="right_content">
							<?php
									$remiand_login_attempts = is_array( $arm_all_block_settings['remained_login_attempts'] ) ? $arm_all_block_settings['remained_login_attempts'][0] : $arm_all_block_settings['remained_login_attempts'];
								?>
									<div class="armswitch arm_global_setting_switch arm_margin_0">
										<input type="checkbox" id="remained_login_attempts" value="1" class="armswitch_input" name="arm_block_settings[remained_login_attempts]" <?php checked( $remiand_login_attempts, 1 ); ?>/>
										<label for="remained_login_attempts" class="armswitch_label"></label>
									</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
				<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Block Username', 'armember-membership' ); ?></div>
					</div>
				</div>
				<div class="arm_content_border"></div>

				<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block arm_security_options_block">
				<div class="page_sub_title arm_font_size_16 arm_font_weight_500">
					<label for="arm_block_usernames"><?php esc_html_e( 'Block Username On Signup', 'armember-membership' ); ?>
					<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo sprintf( esc_attr__("Those username(s) which are entered here, will be blocked on new user registration, that means those keywords will not be allowed to use as username upon signup. For example, if you will enter 'test' here, then all usernames which contain 'test' will be banned, like %s", 'armember-membership'),"'<u>test</u>abc', 'abc<u>test</u>', 'abc<u>test</u>def'");?>"></i></label></th> <?php //phpcs:ignore ?>
				</div>
				<div class="form-field">	
						
					<div class="arm-form-table-content">
						<textarea name="arm_block_settings[arm_block_usernames]" id="arm_block_usernames" rows="8" cols="40"><?php
						$arm_block_usernames = ( isset( $arm_all_block_settings['arm_block_usernames'] ) ) ? $arm_all_block_settings['arm_block_usernames'] : '';
						echo ( ! empty( $arm_block_usernames ) ) ? esc_textarea( stripslashes_deep( $arm_block_usernames ) ) : '';
						?></textarea>
						<div class="arm_info_text_style arm_padding_0 arm_margin_0 arm_margin_top_5"><?php esc_html_e( 'You should place each keyword on a new line.', 'armember-membership' ); ?></div>
					</div>
				</div>
				<div class="form-field arm_margin_top_32">
					<label for="arm_block_usernames_msg arm-form-table-label "><?php esc_html_e( 'Blocked Username Message', 'armember-membership' ); ?></label>
					<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'This message will be display when member tries to register with blocked username.', 'armember-membership' ); ?>"></i>
					<div class="arm-form-table-content arm_margin_top_12 arm_security_options_block_input  arm_width_100_pct">
						<input type="text" name="arm_block_settings[arm_block_usernames_msg]" class="arm_max_width_100_pct arm_width_100_pct" id="arm_block_usernames_msg" value="<?php echo ( ! empty( $arm_all_block_settings['arm_block_usernames_msg'] ) ) ? esc_html( stripslashes( $arm_all_block_settings['arm_block_usernames_msg'] ) ) : ''; ?>"/>
					</div>
				</div>
				</div>
			</div>
			<div class="form-table arm_setting_main_content arm_padding_0 arm_margin_top_24">
				<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Block Email address', 'armember-membership' ); ?></div>
					</div>
				</div>
				<div class="arm_content_border"></div>
				
				<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block arm_security_options_block">
					<div class="form-field page_sub_title arm_font_size_16 arm_font_weight_500">
						<label for="arm_block_emails arm-form-table-label"><?php esc_html_e( 'Block Email Addresses On Signup', 'armember-membership' ); ?>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo sprintf(esc_html__("Those Email Address(es) which are entered here, will be blocked on new user registration, that means those keywords will not be allowed to use as Email Address upon signup. For example, if you will enter 'abc@def.ghi' here, then exact this email address will be banned, but if you will enter 'test' only, then all email addresses which contain 'test' will be banned, like %s.", 'armember-membership'),"'<u>test</u>abc@abc.def', 'ab<u>test</u>@cde.efg', 'ab<u>test</u>cd@efg.ghi', 'abc@def.<u>test</u>', 'abc@<u>test</u>.def'");?>"></i></label> <?php //phpcs:ignore ?>
					</div>
					<div class="form-field">
						<div class="arm-form-table-content">
							<textarea name="arm_block_settings[arm_block_emails]" id="arm_block_emails" rows="8" cols="40" ><?php
								$arm_block_emails = ( isset( $arm_all_block_settings['arm_block_emails'] ) ) ? $arm_all_block_settings['arm_block_emails'] : '';
								echo ( ! empty( $arm_block_emails ) ) ? esc_textarea( stripslashes_deep( $arm_block_emails ) ) : '';
								?></textarea>
							<div class="arm_info_text_style arm_padding_0 arm_margin_0 arm_margin_top_5"><?php esc_html_e( 'You should place each keyword on a new line.', 'armember-membership' ); ?></div>
						</div>
					</div>
					<div class="form-field arm_margin_top_32">
						<label for="arm_block_emails_msg arm-form-table-label"><?php esc_html_e( 'Blocked Email Addresses Message', 'armember-membership' ); ?></label>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'This message will be display when member tries to register with blocked email address.', 'armember-membership' ); ?>"></i>
						<div class="">
							<input type="text" name="arm_block_settings[arm_block_emails_msg]" class="arm_max_width_100_pct arm_width_100_pct arm_margin_top_12" id="arm_block_emails_msg" value="<?php echo ( ! empty( $arm_all_block_settings['arm_block_emails_msg'] ) ) ? esc_attr( stripslashes( $arm_all_block_settings['arm_block_emails_msg'] ) ) : ''; ?>"/>
						</div>
					</div>
				</div>
			</div>

			<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters  ('arm_load_security_settings_section','') : ''; //phpcs:ignore?>

			<div class="arm_submit_btn_container arm_apply_changes_btn_container">
				<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" class="arm_submit_btn_loader" id="arm_loader_img" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_block_settings_btn" type="submit" id="arm_block_settings_btn" name="arm_block_settings_btn"><?php esc_html_e( 'Apply Changes', 'armember-membership' ); ?></button>
				<?php
				
					$arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
				?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($arm_wp_nonce);?>" />
			</div>
		</form>
		<div class="armclear"></div>
	</div>
</div>
<script>
	var ARM_CONDI_BLOCK_REQ_MSG = '<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>';
</script>

<div class="arm_failed_login_attempts_history_popup popup_wrapper" >
	
	<div class="popup_wrapper_inner">	
		<div class="arm_failed_login_attempts_history_popup_close_btn arm_popup_close_btn"></div>
		<div class="popup_header"><?php esc_html_e( 'Failed Login Attempts History', 'armember-membership' ); ?></div>
		<div class="popup_content_text arm_padding_0" >
			<?php
				$arm_failed_login_attempts_history = $arm_members_class->arm_get_failed_login_attempts_history( 1, 10 );
			if ( isset( $arm_failed_login_attempts_history ) && ! empty( $arm_failed_login_attempts_history ) ) {
				?>
					<div class="arm_membership_history_list">
				<?php echo $arm_failed_login_attempts_history; //phpcs:ignore ?>
					</div>
					<?php
			}
			?>
			<div class="armclear"></div>
		</div>
	</div>
	
	<div class="armclear"></div>
</div>    
<script>
	var NO_USERS_AVAILABE = '<?php esc_html_e( 'No Users Available', 'armember-membership' ); ?>';
</script>
