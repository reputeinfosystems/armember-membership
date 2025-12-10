<?php 
global $wpdb, $ARMemberLite, $arm_global_settings, $arm_email_settings, $arm_payment_gateways, $arm_access_rules, $arm_subscription_plans, $arm_member_forms, $arm_social_feature;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();

$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
if(empty($all_email_settings))
{
	$all_email_settings = array();
}

$is_permalink       = $arm_global_settings->is_permalink();
$general_settings   = $all_global_settings['general_settings'];

$page_settings = !empty($all_global_settings['page_settings']) ? $all_global_settings['page_settings'] : array();

$all_plans_data             = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true );
$defaultRulesTypes          = $arm_access_rules->arm_get_access_rule_types();
$default_rules              = $arm_access_rules->arm_get_default_access_rules();
$default_schedular_settings = $arm_global_settings->arm_default_global_settings();
$all_roles                  = $arm_global_settings->arm_get_all_roles();

$currencies = array_merge( $arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['bank_transfer'] );

?>
<style>
	.purchased_info{
		color:#7cba6c;
		font-weight:bold;
		font-size: 15px;
	}
	.arperrmessage{color:red;}
	.arfnewmodalclose
	{
		font-size: 15px;
		font-weight: bold;
		height: 19px;
		position: absolute;
		right: 3px;
		top:5px;
		width: 19px;
		cursor:pointer;
		color:#D1D6E5;
	}
	.newform_modal_title { font-size:25px; line-height:25px; margin-bottom: 10px; }
	.newmodal_field_title { font-size: 16px;
	line-height: 16px;
	margin-bottom: 10px; }
</style>
<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
		
		<form method="post" action="#" id="arm_global_settings" class="arm_global_settings arm_admin_form" onsubmit="return false;">
		<div id="general_setting_sec" class="arm_settings_section">
			<div class="page_sub_title arm_margin_bottom_32 arm_setting_title"><?php esc_html_e( 'General Settings', 'armember-membership' ); ?></div>
				<div class="form-table"> 
				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e('Hide admin bar', 'armember-membership'); ?></div>
							<div class="arm_global_setting_label"><?php esc_html_e('Hide admin bar for non-admin users?', 'armember-membership'); ?></div>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<input type="checkbox" id="hide_admin_bar" <?php checked($general_settings['hide_admin_bar'], '1'); ?> value="1" class="armswitch_input" name="arm_general_settings[hide_admin_bar]" />
								<label for="hide_admin_bar" class="armswitch_label"></label>
							</div>
						</div>
					</div>
					<div class="arm_form_field_block arm_exclude_hide_admin_bar <?php echo ( $general_settings['hide_admin_bar'] == '1' ) ? '' : ' hidden_section'; ?>">
						<div class="arm_content_border arm_margin_top_24"></div>
						<label class="arm-form-table-label arm_margin_bottom_12 arm_padding_top_24"><?php esc_html_e('Exclude role for hide admin bar', 'armember-membership' ); ?></label>
						<div>
							<?php
							$arm_exclude_role_for_hide_admin = array();
							if ( isset( $general_settings['arm_exclude_role_for_hide_admin'] ) && is_array( $general_settings['arm_exclude_role_for_hide_admin'] ) ) {
								$arm_exclude_role_for_hide_admin = $general_settings['arm_exclude_role_for_hide_admin'];
							} else {
								$arm_exclude_role_for_hide_admin = isset( $general_settings['arm_exclude_role_for_hide_admin'] ) ? explode( ',', $general_settings['arm_exclude_role_for_hide_admin'] ) : array();
							}
							?>
							<select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox arm_width_500" name="arm_general_settings[arm_exclude_role_for_hide_admin][]" data-placeholder="<?php esc_html_e( 'Select Role(s)..', 'armember-membership' ); ?>" multiple="multiple" >
									<?php
									if ( ! empty( $all_roles ) ) :
										foreach ( $all_roles as $role_key => $role_value ) {
											?>
												<option class="arm_message_selectbox_op" value="<?php echo esc_attr( $role_key ); ?>" <?php echo ( in_array( $role_key, $arm_exclude_role_for_hide_admin ) ) ? ' selected="selected"' : ''; ?>><?php echo stripslashes( $role_value ); //phpcs:ignore ?></option>
																										   <?php
										}
										else :
											?>
											<option value=""><?php esc_html_e( 'No Roles Available', 'armember-membership' ); ?></option>
									<?php endif; ?>
							</select>
							<span class="arm_info_text_style arm_padding_0 arm_margin_0 arm_margin_top_12" >
								(<?php esc_html_e( 'Admin bar will be displayed to selected roles.', 'armember-membership' ); ?>)
							</span>
						</div>
					</div>
				</div>
				
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','wp-admin') : ''; //phpcs:ignore ?>
				
				<div class="arm_setting_content_spacing"></div>
				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Hide', 'armember-membership' ); ?> wp-login.php <?php esc_html_e( 'page', 'armember-membership' ); ?>
							</div>
							<div class="arm_global_setting_label">
								<?php esc_html_e( 'Hide', 'armember-membership' ); ?> <strong>wp-login.php</strong> <?php esc_html_e( 'page for all users?', 'armember-membership' ); ?>
							</div>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<input type="checkbox" id="hide_wp_login" <?php checked( $general_settings['hide_wp_login'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[hide_wp_login]" />
								<label for="hide_wp_login" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>

				<div class="arm_setting_content_spacing"></div>

				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Hide register link', 'armember-membership' ); ?>
							</div>
							<div class="arm_global_setting_label">
								<?php esc_html_e( 'Hide register link on', 'armember-membership' ); ?> <strong>wp-login.php</strong> <?php esc_html_e( 'page?', 'armember-membership' ); ?>
							</div>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<input type="checkbox" id="hide_register_link" <?php checked( $general_settings['hide_register_link'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[hide_register_link]" />
								<label for="hide_register_link" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>

				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','armember_styling') : ''; //phpcs:ignore ?>
						
				<div class="arm_setting_content_spacing"></div>
				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Auto Lock Shared Account', 'armember-membership' ); ?>
							</div>
							<span class="arm_global_setting_label arm_display_block">
								<?php esc_html_e( 'By enabling this feature, you can prevent simultaneous multiple logins using same login details', 'armember-membership' ); ?>
							</span>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<?php $general_settings['autolock_shared_account'] = ( isset( $general_settings['autolock_shared_account'] ) ) ? $general_settings['autolock_shared_account'] : 0; ?>
								<input type="checkbox" id="autolock_shared_account" <?php checked( $general_settings['autolock_shared_account'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[autolock_shared_account]" />
								<label for="autolock_shared_account" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>
				<div class="arm_setting_content_spacing"></div>

				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Enable Gravatars?', 'armember-membership' ); ?>
							</div>
							<span class="arm_global_setting_label arm_display_block">
								<?php esc_html_e( 'If BuddyPress plugin is active then use BuddyPress avatar', 'armember-membership' ); ?>
							</span>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<input type="checkbox" id="enable_gravatar" <?php checked( $general_settings['enable_gravatar'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[enable_gravatar]" />
								<label for="enable_gravatar" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>
				<div class="arm_setting_content_spacing"></div>

				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Allow image cropping', 'armember-membership' ); ?>
							</div>
							<span for="enable_crop" class="arm_global_setting_label arm_display_block">
								<?php esc_html_e( 'Allow avatar and cover photo cropping', 'armember-membership' ); ?>
							</span>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<?php $enable_crop = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0; ?>
								<input type="checkbox" id="enable_crop" <?php checked( $enable_crop, '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[enable_crop]" />
								<label for="enable_crop" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>

				<div class="arm_setting_content_spacing"></div>

				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php echo esc_html( 'Enable Spam Protection', 'armember-membership' ); ?>
							</div>
							<span for="enable_crop" class="arm_global_setting_label arm_display_block">
								<?php esc_html_e( 'Enable hidden spam protection mechanism in signup/login forms', 'armember-membership' ); ?>
							</span>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<?php $spam_protection = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : 0; ?>
								<input type="checkbox" id="spam_protection" <?php checked( $spam_protection, '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[spam_protection]" />
								<label for="spam_protection" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>


				<div class="arm_setting_content_spacing"></div>

				<div class="arm_setting_main_content arm_padding_0" id="changeCurrency">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label arm_font_">
								<?php esc_html_e( 'New user approval', 'armember-membership' ); ?>
							</div>
						</div>
					</div>
					<div class="arm_content_border"></div>

					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_width_95_pct arm_display_block">
						<div class="right_content arm_radio_btn_wrapper">
						<?php 
						$user_register_verification = isset( $general_settings['user_register_verification'] ) ? $general_settings['user_register_verification'] : ''; 
						$options = [
							'auto' => __( 'Automatic approve', 'armember-membership' ),
							'email' => __( 'Email verified approve', 'armember-membership' ),
							'manual' => __( 'Manual approve by admin', 'armember-membership' ),
						];
						?>
						<input type='hidden' id='arm_new_user_approval' name="arm_general_settings[user_register_verification]" value="<?php echo esc_attr( sanitize_text_field( $user_register_verification ) ); ?>" />

						<?php foreach ( $options as $value => $label ) : ?>
							<div class="arm_sub_plan_selection">
							<input 
								type="radio" 
								class="arm_iradio" 
								id="user_register_verification_<?php echo esc_attr($value); ?>" 
								name="arm_general_settings[user_register_verification]" 
								value="<?php echo esc_attr($value); ?>" 
								<?php checked( $user_register_verification, $value ); ?> 
							/>
							<label for="user_register_verification_<?php echo esc_attr($value); ?>" class="arm_margin_left_46"><?php echo esc_html( $label ); ?></label>
							</div>
						<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="arm_setting_content_spacing"></div>
							
				<div class="arm_setting_main_content arm_padding_0" id="profilePermalinkBase">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Default currency', 'armember-membership' ); ?>
							</div>
						</div>

					</div>
					<div class="arm_content_border"></div>
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_padding_bottom_12 arm_padding_top_24">
					<div class="arm_form_field_block ">

						<?php
							$currencies                = apply_filters( 'arm_available_currencies', $currencies );
							$paymentcurrency           = $general_settings['paymentcurrency'];
							$custom_currency_status    = isset( $general_settings['custom_currency']['status'] ) ? $general_settings['custom_currency']['status'] : '';
							$custom_currency_symbol    = isset( $general_settings['custom_currency']['symbol'] ) ? $general_settings['custom_currency']['symbol'] : '';
							$custom_currency_shortname = isset( $general_settings['custom_currency']['shortname'] ) ? $general_settings['custom_currency']['shortname'] : '';
							$custom_currency_place     = isset( $general_settings['custom_currency']['place'] ) ? $general_settings['custom_currency']['place'] : '';
							$arm_specific_currency_position = isset( $general_settings['arm_specific_currency_position'] ) ? $general_settings['arm_specific_currency_position'] : 'suffix';
						?>
						<input type='hidden' id='arm_payment_currency' name="arm_general_settings[paymentcurrency]" value="<?php echo esc_attr($paymentcurrency); ?>" />

						<div class="arm_email_setting_flex_group arm_payment_getway_page ">
							
							<div class="arm_form_field_block">
								<label class="arm-form-table-label"><?php esc_html_e( 'Select currency', 'armember-membership' ); ?></label>
								<div class="arm_margin_top_12 ">
									<dl class="arm_selectbox column_level_dd arm_default_currency_box  <?php echo ( $custom_currency_status == 1 ) ? 'disabled' : ''; ?> arm_width_100_pct">
										<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_padding_top_0 arm_padding_bottom_0"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
										<dd>
											<ul data-id="arm_payment_currency">
												<?php foreach ( $currencies as $key => $value ) : ?>
													<li data-label="<?php echo esc_attr($key) . " ( ".esc_attr($value)." ) "; ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html($key) . " (". esc_html($value) .") "; ?></li>
												<?php endforeach; ?>
											</ul>
										</dd>
									</dl>
								</div>	
							</div>	

							<div class="arm_form_field_block arm_specific_currency_position arm_currency_prefix_suffix_display <?php echo ( $paymentcurrency != 'EUR' ) ? "hidden_section" : ''; ?>">
								<label class="arm_display_flex"><?php esc_html_e( 'Currency symbol', 'armember-membership' ); ?></label>
								<div class="arm_currency_prefix_suffix_display arm_margin_top_20">
										<input type="radio" id="default_currency_prefix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked( $arm_specific_currency_position, 'prefix' ); ?> value="prefix" <?php echo ( $custom_currency_status == 1 ) ? 'disabled' : ''; ?> /><label class="default_currency_prefix_suffix_lbl" for="default_currency_prefix_val" <?php echo ( $custom_currency_status == 1 ) ? 'style="cursor: no-drop;"' : ''; ?>><?php esc_html_e( 'Prefix', 'armember-membership' ); ?></label>
										<input type="radio" id="default_currency_suffix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked( $arm_specific_currency_position, 'suffix' ); ?> value="suffix" <?php echo ( $custom_currency_status == 1 ) ? 'disabled' : ''; ?> /><label class="default_currency_prefix_suffix_lbl" for="default_currency_suffix_val" <?php echo ( $custom_currency_status == 1 ) ? 'style="cursor: no-drop;"' : ''; ?>><?php esc_html_e( 'Suffix', 'armember-membership' ); ?></label>
								</div>
							</div>
						</div>
				
						<div class="armclear"></div>
						<span class="arm_currency_seperator_text_style arm_text_align_left"><?php esc_html_e( 'OR', 'armember-membership' ); ?></span>
						<div class="armclear"></div>

						<div class="armGridActionTD arm_custom_currency_options_container arm_padding_0">
							<input type="hidden" class="custom_currency_symbol" name="arm_general_settings[custom_currency][symbol]" value="<?php echo esc_attr( sanitize_text_field($custom_currency_symbol) ); ?>">
							<input type="hidden" class="custom_currency_shortname" name="arm_general_settings[custom_currency][shortname]" value="<?php echo esc_attr( sanitize_text_field($custom_currency_shortname) ); ?>">
							

							<label class="arm_custom_currency_checkbox_label arm_display_flex">
								<input type="checkbox" class="arm_custom_currency_checkbox arm_icheckbox" value="1" name="arm_general_settings[custom_currency][status]" <?php checked( $custom_currency_status, 1 ); ?>>
								<span><?php esc_html_e( 'Set Custom Currency', 'armember-membership' ); ?></span>
							</label>

							<div class="arm_confirm_box_custom_currency arm_no_hide" id="arm_confirm_box_custom_currency">
								<div class="arm_confirm_box_body arm_max_width_90_pct">
									<div class="arm_confirm_box_arrow"></div>
									<div class="arm_confirm_box_text arm_custom_currency_fields arm_text_align_left">
										
										<div class="arm_form_field_block arm_min_width_0">
											<label class="arm_padding_left_0"><?php esc_html_e( 'Currency Symbol', 'armember-membership' ); ?></label>
											<input type="text" id="custom_currency_symbol" class="arm_width_100_pct arm_margin_top_12 arm_max_width_100_pct" value="<?php echo esc_attr($custom_currency_symbol); ?>">
											<span class="arm_error_msg symbol_error" style="display:none;"><?php esc_html_e( 'Please enter symbol.', 'armember-membership' ); ?></span>
											<span class="arm_error_msg invalid_symbol_error" style="display:none;"><?php esc_html_e( 'Please enter valid symbol.', 'armember-membership' ); ?></span>
										</div>

										<div class="arm_form_field_block arm_margin_top_20 arm_min_width_0">
											<label class="arm_padding_left_0"><?php esc_html_e( 'Currency Shortname', 'armember-membership' ); ?></label>
											<input type="text" id="custom_currency_shortname" class="arm_width_100_pct arm_margin_top_12 arm_max_width_100_pct" value="<?php echo esc_attr($custom_currency_shortname); ?>">
											<span class="arm_error_msg shortname_error" style="display:none;"><?php esc_html_e( 'Please enter shortname.', 'armember-membership' ); ?></span>
											<span class="arm_error_msg invalid_shortname_error" style="display:none;"><?php esc_html_e( 'Please enter valid shortname.', 'armember-membership' ); ?></span>
										</div>

										<div class="arm_form_field_block arm_margin_top_20 arm_min_width_0">
											<label class="arm_padding_left_0"><?php esc_html_e( 'Symbol will be display as', 'armember-membership' ); ?></label>
											<input type="hidden" class="custom_currency_place" id="custom_currency_place" name="arm_general_settings[custom_currency][place]" value="<?php echo esc_attr( sanitize_text_field($custom_currency_place) ); ?>">									
											<dl class="arm_selectbox column_level_dd arm_width_100_pct  arm_margin_top_12 arm_max_width_100_pct">
												<dt><span><?php esc_html_e('Prefix','armember-membership');?></span><input type="text" style="display:none;" value="" class="arm_autocomplete" wfd-id="id24"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="custom_currency_place" style="display: none;">
														<li data-label="<?php esc_html_e('Prefix','armember-membership');?>" data-value="prefix"><?php esc_html_e('Prefix','armember-membership');?></li>
														<li data-label="<?php esc_html_e('Suffix','armember-membership');?>" data-value="suffix"><?php esc_html_e('Suffix','armember-membership');?></li>
													</ul>
												</dd>
											</dl>
										</div>

									</div>
									<div class='arm_confirm_box_btn_container arm_margin_top_20 arm_min_width_0 arm_padding_top_24 arm_padding_0 arm_text_align_right'>
										<button type="button" class="arm_confirm_box_btn armcancel arm_margin_right_12" onclick="hideCustomCurrencyBox();"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
										<button type="button" class="arm_confirm_box_btn armemailaddbtn" id="arm_custom_currency_ok_btn"><?php esc_html_e( 'Add', 'armember-membership' ); ?></button>
									</div>
								</div>
							</div>

							<div class="armclear"></div>

							<span class="arm_custom_currency_text">
								<?php
									if ( ! empty( $custom_currency_symbol ) && ! empty( $custom_currency_shortname ) ) {
										$currency_name = $custom_currency_shortname . " ( $custom_currency_symbol )";
										echo '<span>' . esc_html__( 'Custom Currency', 'armember-membership' ) . ": <strong>". esc_html($currency_name) ."</strong><a href='javascript:void(0)' class='arm_custom_currency_edit'>" . esc_html__( 'Edit', 'armember-membership' ) . '</a></span>';
									}
								?>
							</span>

						</div>
						<div class="armclear"></div>
						<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','currency_decimal') : ''; //phpcs:ignore ?>	
						<?php
						if ( $custom_currency_status == 1 ) {
							$paymentcurrency = $custom_currency_shortname;
						}
						$currency_warring = $arm_payment_gateways->arm_check_currency_status( $paymentcurrency );
						?>
							<span class="arm_global_setting_currency_warring arm-note-message --warning" style="color: #676767;<?php echo ( empty( $currency_warring ) ) ? 'display:none;' : ''; ?>"><?php echo esc_html($currency_warring); ?></span>
						</div>
					</div>
				</div>
				
				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" style="<?php echo ( ! $arm_social_feature->isSocialFeature ) ? 'display:none;' : ''; ?>">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Profile Permalink Base', 'armember-membership' ); ?>
							</div>
						</div>
					</div>
					<div class="arm_content_border"></div>

					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_padding_top_24 arm_display_block">
						<div class="right_content arm_email_setting_flex_group arm_payment_getway_page">
							<?php
							$permalink_base = isset( $general_settings['profile_permalink_base'] ) ? $general_settings['profile_permalink_base'] : 'user_login';
							$profileUrl_user_login = '<b>username</b>/';
							$profileUrl_user_id    = '<b>user_id</b>/';

							if ( $is_permalink ) {
								if ( ! empty( $arm_global_settings->profile_url ) ) {
									$profileUrl            = trailingslashit( untrailingslashit( $arm_global_settings->profile_url ) );
									$profileUrl_user_login = $profileUrl . '<b>username</b>/';
									$profileUrl_user_id    = $profileUrl . '<b>user_id</b>/';
								}
							} else {
								$profileUrl            = $arm_global_settings->add_query_arg( 'arm_user', 'arm_base_slug', $arm_global_settings->profile_url );
								$profileUrl_user_login = str_replace( 'arm_base_slug', '<b>username</b>', $profileUrl );
								$profileUrl_user_id    = str_replace( 'arm_base_slug', '<b>user_id</b>', $profileUrl );
							}
							?>
							<input type="hidden" id="arm_profile_permalink_base" name="arm_general_settings[profile_permalink_base]" value="<?php echo esc_attr( $permalink_base ); ?>" />

							<dl class="arm_selectbox column_level_dd arm_width_100_pct">
								<dt>
									<span>
										<?php
										echo ( $permalink_base === 'user_id' ) ? esc_html__( 'User ID', 'armember-membership' ) : esc_html__( 'Username', 'armember-membership' );
										?>
									</span>
									<input type="text" style="display:none;" value="" class="arm_autocomplete" />
									<i class="armfa armfa-caret-down armfa-lg"></i>
								</dt>
								<dd class="">
									<ul data-id="arm_profile_permalink_base">
										<li data-label="<?php esc_html_e( 'Username', 'armember-membership' ); ?>" data-value="user_login"><?php esc_html_e( 'Username', 'armember-membership' ); ?></li>
										<li data-label="<?php esc_html_e( 'User ID', 'armember-membership' ); ?>" data-value="user_id"><?php esc_html_e( 'User ID', 'armember-membership' ); ?></li>
									</ul>
								</dd>
							</dl>
						</br>
							<div class="armclear"></div>
							
						</div>
						<div class="arm_info_text_style arm_profile_user_login arm_padding_0 arm_margin_0 arm_margin_top_12" style="<?php echo ( $permalink_base === 'user_login' ) ? '' : 'display:none;'; ?>">
							e.g. <?php echo $profileUrl_user_login; ?>
						</div>
						<div class="arm_info_text_style arm_profile_user_id arm_padding_0 arm_margin_0 arm_margin_top_12" style="<?php echo ( $permalink_base === 'user_id' ) ? '' : 'display:none;'; ?>">
							e.g. <?php echo $profileUrl_user_id; ?>
						</div>
					</div>
				</div>
		
				<div class="arm_setting_content_spacing"></div>
						
				<div class="arm_setting_main_content">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e('Load JS & CSS in all pages', 'armember-membership'); ?>
							</div>
							<span class="arm_global_setting_label arm_display_block"><strong><?php esc_html_e( 'Not recommended', 'armember-membership' ); ?></strong> - <?php esc_html_e( 'If you have any js/css loading issue in your theme, only in that case you should enable this settings', 'armember-membership' ); ?></span>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch  arm_margin_0">
								<input type="checkbox" id="arm_enqueue_all_js_css" <?php checked( $general_settings['enqueue_all_js_css'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[enqueue_all_js_css]"/>
								<label for="arm_enqueue_all_js_css" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>

				<!-- <div class="arm_setting_content_spacing"></div> -->

				<div class="arm_setting_main_content arm_margin_top_24 arm_margin_bottom_24">
					<div class="arm_row_wrapper">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e('Help us improve ARMember by sending anonymous usage stats','armember-membership'); ?>
							</div>
					 	</div>
						<div class="right_content">
							<?php $general_settings['arm_anonymous_data'] = !empty($general_settings['arm_anonymous_data']) ? 1 : 0; ?>
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<input type="checkbox" id="arm_anonymous_data" <?php checked($general_settings['arm_anonymous_data'], '1'); ?> value="1" class="armswitch_input" name="arm_general_settings[arm_anonymous_data]" />
								<label for="arm_anonymous_data" class="armswitch_label"></label>
							</div>
						</div>
					</div>
				</div>
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','badge_icons') : ''; //phpcs:ignore ?>
				</div>			
		</div>

		<div id="email_setting_sec" class="arm_settings_section" style="display:none;">
			<div class="page_sub_title arm_margin_bottom_0"><?php esc_html_e( 'Email Settings', 'armember-membership' ); ?></div>
				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_32 arm_email_setting_wapper">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Email Settings', 'armember-membership' ); ?>
							</div>
						</div>
					</div>
					<div class="arm_content_border"></div>
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_padding_top_24 arm_display_block">
						<div class="right_content" style="display:unset;">
							<div class="arm_email_setting_flex_group arm_payment_getway_page">
								<div class="arm_form_field_block">
									<label class="arm-form-table-label"><?php esc_html_e( 'From/Reply to Name', 'armember-membership' ); ?></label>
									<input id="arm_email_from_name" type="text" name="arm_email_from_name" value="<?php echo esc_attr( ! empty( $all_email_settings['arm_email_from_name'] ) ? stripslashes( sanitize_text_field($all_email_settings['arm_email_from_name']) ) : get_option( 'blogname' ) ); ?>" >
									<span id="email_from_name_error" class="arm_error_msg email_from_name_error" style="display:none;"><?php esc_html_e( 'Please enter From Name.', 'armember-membership' ); ?></span>
									<span id="invalid_email_from_name_error" class="arm_error_msg invalid_email_from_name_error" style="display:none;"><?php esc_html_e( 'Please enter valid From Name.', 'armember-membership' ); ?></span>   
								</div>
								<div class="arm_form_field_block ">
									<label class="arm-form-table-label"><?php esc_html_e( 'From/Reply to Email', 'armember-membership' ); ?></label>
									<input id="arm_email_from_email" type="email" name="arm_email_from_email" value="<?php echo ( ! empty( $all_email_settings['arm_email_from_email'] ) ? esc_attr($all_email_settings['arm_email_from_email']) : get_option( 'admin_email' ) ); ?>" >
									<span id="email_from_email_error" class="arm_error_msg email_from_email_error" style="display:none;"><?php esc_html_e( 'Please enter From Email ID.', 'armember-membership' ); ?></span>
									<span id="invalid_email_from_email_error" class="arm_error_msg invalid_email_from_email_error" style="display:none;"><?php esc_html_e( 'Please enter valid From Email ID.', 'armember-membership' ); ?></span>
								</div>
								<div class="arm_form_field_block ">
									<div class="arm_display_flex">
									<label class="arm-form-table-label"><?php esc_html_e( 'Admin Email', 'armember-membership' ); ?></label>
									<i class="arm_helptip_icon armfa armfa-question-circle arm_" title="<?php echo esc_html__('You can add multiple Admin email address separated by comma in case of you want to send email to more than one email address.', 'armember-membership'); ?>"></i>
									</div>
									<input id="arm_email_admin_email" type="email" name="arm_email_admin_email" value="<?php echo esc_attr( ! empty( $all_email_settings['arm_email_admin_email'] ) ? sanitize_text_field($all_email_settings['arm_email_admin_email']) : get_option( 'admin_email' ) ); ?>" >
									<span id="email_admin_email_error" class="arm_error_msg email_admin_email_error" style="display:none;"><?php esc_html_e( 'Please enter Admin Email ID.', 'armember-membership' ); ?></span>
									<span id="invalid_email_admin_email_error" class="arm_error_msg invalid_email_admin_email_error" style="display:none;"><?php esc_html_e( 'Please enter valid Admin Email ID.', 'armember-membership' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>	
				<div class="arm_setting_content_spacing"></div>
			
				<div class="arm_setting_main_content arm_padding_0 arm_margin_bottom_32">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label">
								<?php esc_html_e( 'Email notification', 'armember-membership' ); ?>
							</div>
						</div>
					</div>
					<div class="arm_content_border"></div>

					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<div class="right_content">
						<div class="arm_email_setting_flex_group arm_setting_content_display_unset arm_width_100_pct">

							<div class="arm_form_field_block ">
								<div class="arm_email_notification_wrapper arm_email_settings_select_text">

									<?php $all_email_settings['arm_email_server'] = ( isset( $all_email_settings['arm_email_server'] ) ) ? $all_email_settings['arm_email_server'] : 'wordpress_server'; ?>

									<div class="arm_email_settings_select_text_inner">
									<input type="radio" id="arm_email_server_ws" class="arm_general_input arm_email_notification_radio arm_iradio" 
										<?php checked( $all_email_settings['arm_email_server'], 'wordpress_server' ); ?> name="arm_email_server" value="wordpress_server" />
									<label for="arm_email_server_ws" class="arm_email_settings_help_text arm_padding_right_46"><?php esc_html_e( 'WordPress Server', 'armember-membership' ); ?></label>
									</div>

									<div class="arm_email_settings_select_text_inner">
									<input type="radio" id="arm_email_server_smtps" class="arm_general_input arm_email_notification_radio arm_iradio" 
										<?php checked( $all_email_settings['arm_email_server'], 'smtp_server' ); ?> name="arm_email_server" value="smtp_server" />
									<label for="arm_email_server_smtps" class="arm_email_settings_help_text arm_padding_right_46"><?php esc_html_e( 'SMTP Server', 'armember-membership' ); ?></label>
									</div>

									<div class="arm_email_settings_select_text_inner">
									<input type="radio" id="arm_email_server_phpm" class="arm_general_input arm_email_notification_radio arm_iradio" 
										<?php checked( $all_email_settings['arm_email_server'], 'phpmailer' ); ?> name="arm_email_server" value="phpmailer" />
									<label for="arm_email_server_phpm" class="arm_email_settings_help_text arm_padding_right_46"><?php esc_html_e( 'PHP Mailer', 'armember-membership' ); ?></label>
									</div>

									<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','gmail_options') : ''; ?>

								</div>
							</div>

							<div class=" arm_form_field_block arm_smtp_slide_form arm_email_server_smtp arm_email_setting_wapper">
								<div class="arm_form_field_block arm_margin_top_48">
									<label class="arm-form-table-label" style="color: var(--arm-dt-black-600); font-weight: 500"><?php esc_html_e( 'Authentication', 'armember-membership' ); ?></label>
									<?php $arm_mail_authentication = ( isset( $all_email_settings['arm_mail_authentication'] ) ) ? $all_email_settings['arm_mail_authentication'] : '1'; ?>
									<label class="arm_custom_currency_checkbox_label arm_margin_top_20 arm_width_100_pct">
									<input type="checkbox" class="arm_icheckbox" value="1" id="arm_mail_authentication" name="arm_mail_authentication" onchange="arm_mail_authentication_func(this.value);" <?php checked( $arm_mail_authentication, 1 ); ?>>
									<span><?php esc_html_e( 'SMTP authentication', 'armember-membership' ); ?></span>
									</label>
									
								</div>

								<div class="arm_email_setting_flex_group arm_margin_top_28">
									<div class="arm_form_field_block">
										<label class="arm-form-table-label"><?php esc_html_e( 'Mail Server', 'armember-membership' ); ?> *</label>
										<?php $arm_mail_server = ( isset( $all_email_settings['arm_mail_server'] ) ) ? $all_email_settings['arm_mail_server'] : ''; ?>
										<input type="text" id="arm_mail_server" name="arm_mail_server" value="<?php echo esc_attr( sanitize_text_field($arm_mail_server) ); ?>" class="arm_mail_server_input arm_max_width_360" >
										<span class="error arm_invalid" id="arm_mail_server_error" style="display: none;"><?php esc_html_e( 'Mail Server can not be left blank.', 'armember-membership' ); ?></span>
									</div>
									<div class="arm_form_field_block">
										<label class="arm-form-table-label"><?php esc_html_e( 'Port', 'armember-membership' ); ?> *</label>
										<?php $arm_mail_port = ( isset( $all_email_settings['arm_mail_port'] ) ) ? $all_email_settings['arm_mail_port'] : ''; ?>
										<input type="text" id="arm_port" name="arm_mail_port" value="<?php echo esc_attr( sanitize_text_field($arm_mail_port) ); ?>" class="arm_max_width_360" />
										<span class="error arm_invalid" id="arm_mail_port_error" style="display: none;"><?php esc_html_e( 'Port can not be left blank.', 'armember-membership' ); ?></span>
									</div>
								</div>
							
								<div class="arm_email_settings_login_name_main" style=" <?php if ( empty( $arm_mail_authentication ) ) { echo 'display:none;'; } ?>" >
								<div class="arm_margin_top_28 arm_email_setting_flex_group">
									<div class="arm_form_field_block arm_mail_authentication_fields " >
										<label class="arm-form-table-label"><?php esc_html_e( 'Login Name', 'armember-membership' ); ?> *</label>
										<?php $arm_mail_login_name = ( isset( $all_email_settings['arm_mail_login_name'] ) ) ? $all_email_settings['arm_mail_login_name'] : ''; ?>
										<input type="text" id="arm_login_name" name="arm_mail_login_name" value="<?php echo esc_attr( sanitize_text_field($arm_mail_login_name) ); ?>" class="arm_max_width_360" />
										<span class="error arm_invalid" id="arm_mail_login_name_error" style="display: none;"><?php esc_html_e( 'Login Name can not be left blank.', 'armember-membership' ); ?></span>
									</div>

									<div class="arm_form_field_block arm_mail_authentication_fields">
										<label class="arm-form-table-label"><?php esc_html_e( 'Password', 'armember-membership' ); ?> *</label>
										<?php $arm_mail_password = ( isset( $all_email_settings['arm_mail_password'] ) ) ? $all_email_settings['arm_mail_password'] : ''; ?>
										<input type="password" id="arm_password" name="arm_mail_password" value="<?php echo esc_attr( $arm_mail_password ); ?>" autocomplete="off" class="arm_max_width_360" />
										<span class="error arm_invalid" id="arm_mail_password_error" style="display: none;"><?php esc_html_e( 'Password can not be left blank.', 'armember-membership' ); ?></span>
									</div>
								</div>
								</div>

								<div class="arm_form_field_block arm_margin_top_32">
									<label class="arm-form-table-label"><?php esc_html_e( 'Encryption', 'armember-membership' ); ?></label>
									<div class="arm_email_settings_select_text">

									<?php
										$selected_enc = ( isset( $all_email_settings['arm_smtp_enc'] ) && in_array($all_email_settings['arm_smtp_enc'], ['ssl','tls']) ) ? '1' : '0';
										$all_email_settings['arm_smtp_enc'] = isset($all_email_settings['arm_smtp_enc']) ? $all_email_settings['arm_smtp_enc'] : '0';
									?>

									<div class="arm_email_settings_select_text_inner arm_margin_top_20 ">
										<input type="radio" id="arm_smtp_enc_none" class="arm_general_input arm_iradio" <?php checked( $selected_enc, '0' ); ?>  name="arm_smtp_enc" value="none" /><label for="arm_smtp_enc_none" class="arm_email_settings_help_text arm_margin_right_0 arm_padding_right_46"><?php esc_html_e( 'None', 'armember-membership' ); ?></label>
									</div>

									<div class="arm_email_settings_select_text_inner">
										<input type="radio" id="arm_smtp_enc_ssl" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'ssl' ); ?> name="arm_smtp_enc" value="ssl" /><label for="arm_smtp_enc_ssl" class="arm_email_settings_help_text arm_margin_right_0"><?php esc_html_e( 'SSL', 'armember-membership' ); ?></label>
									</div>

									<div class="arm_email_settings_select_text_inner">
										<input type="radio" id="arm_smtp_enc_tls" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'tls' ); ?> name="arm_smtp_enc" value="tls" /><label for="arm_smtp_enc_tls" class="arm_email_settings_help_text arm_margin_right_0"><?php esc_html_e( 'TLS', 'armember-membership' ); ?></label>
									</div>

									</div>
								</div>
							</div>
							<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','gmail_section') : ''; ?>	
						</div>			
					</div>
				</div>	
			</div>
			
			<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24 arm_margin_bottom_32">
				<div class="arm_smtp_slide_form arm_test_email_container arm_width_100_pct">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label arm_display_flex arm_margin_bottom_0 arm_test_email_smtp">
								<?php esc_html_e( 'Test Email', 'armember-membership' ); ?>
							<p class="arm_font_weight_400 arm_font_size_15 arm_margin_0" style="color: var(--arm-gray-500);">(<?php esc_html_e(' Test e-mail works only after configure SMTP server settings ', 'armember-membership'); ?>)</p>
							</div>
						</div>
					</div>
					<div class="arm_content_border"></div>
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<div class="arm_email_setting_flex_group">
							<div class="arm_form_field_block arm_width_100_pct arm_paddin_top_24" style="">
							<label id="arm_success_test_mail" class="arm_success_test_mail_label" style="display:none;"><?php esc_html_e('Your test mail is successfully sent.', 'armember-membership'); ?></label>
							<label id="arm_error_test_mail" class="arm_error_test_mail_label" style="display:none;"><?php esc_html_e('Your test mail is not sent for some reason, Please check your SMTP setting.', 'armember-membership'); ?></label>
						</div>
						</div>
						<div class="arm_email_setting_flex_group">
							<div class="arm_form_field_block arm_width_100_pct arm_paddin_top_24" style="">
								<label class="arm-form-table-label arm_width_100_pct"><?php esc_html_e('To', 'armember-membership'); ?> *</label>
								<input type="text" id="arm_test_email_to" name="arm_test_email_to" class="arm_width_100_pct" value="" />
								<span class="error arm_invalid" id="arm_test_email_to_error" style="display: none;"><?php esc_html_e('To can not be left blank.', 'armember-membership'); ?></span>
							</div>
						</div>
						<div class="arm_email_setting_flex_group">
							<div class="arm_form_field_block arm_margin_top_28">
								<label class="arm-form-table-label"><?php esc_html_e('Message', 'armember-membership'); ?> *</label>
								<textarea id="arm_test_email_msg" name="arm_test_email_msg" class="arm_max_width_360 arm_height_80"></textarea>
								<span class="error arm_invalid" id="arm_test_email_msg_error" style="display: none;"><?php esc_html_e('Message can not be left blank.', 'armember-membership'); ?></span>
							</div>
						</div>
						<div class="arm_form_field_block arm_margin_top_24">
							<button type="button" class="arm_save_btn arm_email_notification_btn" id="arm_send_test_mail" ><?php esc_html_e('Send test mail', 'armember-membership'); ?></button>
							<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL ?>/arm_loader.gif" id="arm_send_test_mail_loader" class="arm_submit_btn_loader" width="24" height="24" style="display: none;" />
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','invoices_tax') : ''; //phpcs:ignore ?>
		<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','google_recaptcha') : ''; //phpcs:ignore ?>

		<div id="preset_fields_sec" class="arm_settings_section" style="display:none;">	
			<div class="page_sub_title arm_margin_bottom_0"><?php esc_html_e( 'Manage Preset Form Fields', 'armember-membership' ); ?></div>
					
			<div class="arm_setting_main_content arm_margin_top_32">
				<label class="arm-form-table-label"><?php esc_html_e( 'To edit specific form preset fields, click on this button, popup opens, edit fields which you want to update and click on update button.', 'armember-membership' ); ?></label>
				<div class="arm_manage_preset_fields_btn arm_margin_top_12">
					<input type="button" value="<?php esc_html_e('Edit Preset Form Fields', 'armember-membership' ); ?>" onclick="arm_open_edit_field_popup();" id="arm_edit_form_fields" class="armemailaddbtn arm_width_220" title="" >
				</div>
			</div>
			<div class="arm_margin_top_30 arm_setting_main_content arm_margin_bottom_32">
				<label class="arm-form-table-label"><?php esc_html_e( 'To remove specific form fields with its value, click on this button, popup opens, select fields which you want to remove from everywhere.', 'armember-membership' ); ?></label>
				<div class="arm_manage_preset_fields_btn arm_margin_top_12">
					<input type="button" value="<?php esc_html_e( 'Clear Preset Form Fields', 'armember-membership' ); ?>" onclick="arm_open_clear_field_popup();" id="arm_clear_form_fields" class="armemailaddbtn arm_width_220"  >
				</div>
			</div>
		</div>

		<div id="email_scheduler_sec" class="arm_settings_section arm_margin_bottom_32" style="display:none;">
			<div class="page_sub_title arm_margin_bottom_0"><?php esc_html_e( 'Email notification scheduler setting', 'armember-membership' ); ?>
				<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'when you change value from below dropdown and save it then it will set new schedular and remove previous one.', 'armember-membership' ); ?>"></i>
			</div>
			<div class="arm_setting_main_content arm_padding_0 arm_margin_top_32">
				<div class="arm_row_wrapper arm_row_wrapper_padding_before">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label">
							<?php esc_html_e( 'Email Scheduler Settings', 'armember-membership' ); ?>
						</div>
					</div>
				</div>
				<div class="arm_content_border"></div>
				<div class="arm_email_setting_flex_group">
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_padding_top_24 arm_width_100_pct">
						<div class="right_content arm_width_100_pct">
							<div class="arm_email_setting_flex_group arm_setting_content_display_unset arm_width_100_pct">
								<label class="arm-form-table-label arm_padding_left_0"><?php esc_html_e( 'Schedule Every', 'armember-membership' ); ?></label>
								<div class="arm_form_field_block arm_email_scheduler_block arm_max_width_475">
									<?php $arm_email_schedular_time = isset( $general_settings['arm_email_schedular_time'] ) ? $general_settings['arm_email_schedular_time'] : 12; ?>
									<input type="hidden" name="arm_general_settings[arm_email_schedular_time]" id="arm_email_schedular_time" value="<?php echo esc_html( sanitize_text_field($arm_email_schedular_time) ); ?>" />

									<div class="arm_selectbox column_level_dd arm_width_362 arm_margin_top_12">
										<dt>
											<span></span>
											<input type="text" style="display:none;" value="" class="arm_autocomplete" />
											<i class="armfa armfa-caret-down armfa-lg"></i>
										</dt>
										<dd>
											<ul data-id="arm_email_schedular_time" style="display:none;">
												<?php
												for ( $ct = 1; $ct <= 24; $ct++ ) {
													echo "<li data-value='{$ct}' data-label='{$ct}'>{$ct}</li>"; // phpcs:ignore
												}
												?>
											</ul>
										</dd>
									</div>
									<label class="arm_margin_left_10 arm_margin_top_10"><?php esc_html_e( 'Hours', 'armember-membership' ); ?></label>
								</div>
								<?php do_action( 'arm_cron_schedular_from_outside' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>

		<?php do_action( 'arm_after_global_settings_html', $general_settings ); ?>

		<div id="front_end_font_sec" class="arm_settings_section" style="display:none;">
			<?php
			$frontfontOptions = array(
				'level_1_font' => esc_html__( 'Level 1', 'armember-membership' ),
				'level_2_font' => esc_html__( 'Level 2', 'armember-membership' ),
				'level_3_font' => esc_html__( 'Level 3', 'armember-membership' ),
				'level_4_font' => esc_html__( 'Level 4', 'armember-membership' ),
				'link_font'    => esc_html__( 'Links', 'armember-membership' ),
				'button_font'  => esc_html__( 'Buttons', 'armember-membership' ),
			);
			$frontfontOptions = apply_filters( 'arm_front_font_settings_type', $frontfontOptions );
			?>
			<?php if ( ! empty( $frontfontOptions ) ) : ?>
				<div class="page_sub_title"><?php esc_html_e( 'Front-end Font Settings', 'armember-membership' ); ?></div>
				<div class="form-table arm_width_auto arm_margin_top_32 arm_margin_bottom_32">
					<?php
					$frontOptHtml = '';
					$frontOptions = isset( $general_settings['front_settings'] ) ? $general_settings['front_settings'] : array();
					foreach ( $frontfontOptions as $key => $title ) {
						$fontVal         = ( ( ! empty( $frontOptions[ $key ] ) ) ? $frontOptions[ $key ] : array() );
						$font_bold       = ( isset( $fontVal['font_bold'] ) && $fontVal['font_bold'] == '1' ) ? 1 : 0;
						$font_italic     = ( isset( $fontVal['font_italic'] ) && $fontVal['font_italic'] == '1' ) ? 1 : 0;
						$font_decoration = ( isset( $fontVal['font_decoration'] ) ) ? $fontVal['font_decoration'] : '';
						$frontOptHtml   .= '<div class="arm_form_field_block arm_display_flex">';
						$frontOptHtml   .= '<div class="arm-form-table-label arm_padding_left_0  arm_font_setting_label arm_font_size_18">' . esc_attr( $title );
						if ( $key == 'level_1_font' ) {
							$tooltip_title = esc_html__( 'Font settings of Level 1 will be applied to main heading of frontend shortcodes. Like Transaction listing heading and like wise.', 'armember-membership' );
						} elseif ( $key == 'level_2_font' ) {
							$tooltip_title = esc_html__( 'Font settings of Level 2 will be applied to sub heading ( Main Labels ) of frontend shortcodes. For example table heading of trasanction listing.', 'armember-membership' );
						} elseif ( $key == 'level_3_font' ) {
							$tooltip_title = esc_html__( 'Font settings of Level 3 will be applied to sub labels of frontend shortcodes. For example table content of trasanction listing.', 'armember-membership' );
						} elseif ( $key == 'level_4_font' ) {
							$tooltip_title = esc_html__( 'Font settings of Level 4 will be applied to very small labels of frontend shortcodes. For member listing etc.', 'armember-membership' );
						} elseif ( $key == 'link_font' ) {
							$tooltip_title = esc_html__( 'Font settings of Links will be applied to links of frontend shortcodes. For example edit profile, logout link and profile links etc.', 'armember-membership' );
						} elseif ( $key == 'button_font' ) {
							$tooltip_title = esc_html__( 'Font settings of Buttons will be applied to buttons of frontend shortcodes output. For example Renew button, Cancel Button, Make Payment Button etc.', 'armember-membership' );
						}
						$frontOptHtml .= ' <i class="arm_helptip_icon armfa armfa-question-circle" title="' . esc_attr( $tooltip_title ) . '"></i></div>';
						$frontOptHtml .= '<div class="arm_setting_main_content arm_margin_left_40">';
						$frontOptHtml .= '<input type="hidden" id="arm_front_font_family_' . esc_attr( $key ) . '" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_family]" value="' . ( ( ! empty( $fontVal['font_family'] ) ) ? esc_attr( sanitize_text_field($fontVal['font_family']) ) : esc_attr('Helvetica') ) . '"/>';
						$frontOptHtml .= '<dl class="arm_selectbox column_level_dd arm_width_140 arm_margin_right_10">';
						$frontOptHtml .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
						$frontOptHtml .= '<dd><ul data-id="arm_front_font_family_' . esc_attr( $key ) . '">';
						$frontOptHtml .= $arm_member_forms->arm_fonts_list();
						$frontOptHtml .= '</ul></dd>';
						$frontOptHtml .= '</dl>';
						$frontOptHtml .= '<input type="hidden" id="arm_front_font_size_' . esc_attr( $key ) . '" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_size]" value="' . ( ! empty( $fontVal['font_size'] ) ? esc_attr( sanitize_text_field($fontVal['font_size'])) : esc_attr('14') ) . '"/>';
						$frontOptHtml .= '<dl class="arm_selectbox column_level_dd arm_width_100">';
						$frontOptHtml .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
						$frontOptHtml .= '<dd><ul data-id="arm_front_font_size_' . esc_attr( $key ) . '">';
						for ( $i = 8; $i < 41; $i++ ) {
							$frontOptHtml .= '<li data-label="' . esc_attr( $i ) . ' px" data-value="' . esc_attr( $i ) . '">' . esc_attr( $i ) . ' px</li>';
						}
						$frontOptHtml .= '</ul></dd>';
						$frontOptHtml .= '</dl>';
					
						$frontOptHtml .= '<div class="arm_font_style_options arm_front_font_style_options arm_margin_left_24">';
						$frontOptHtml .= '<label class="arm_font_style_label ' . ( ( $font_bold == '1' ) ? 'arm_style_active' : '' ) . '" data-value="bold" data-field="arm_front_font_bold_' . esc_attr( $key ) . ' "><svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 7.66667H1M5 7.66667C5 7.66667 8.33333 7.66666 8.33333 4.33333C8.33333 1.00001 5 1 5 1H1.6C1.26863 1 1 1.26863 1 1.6V7.66667M5 7.66667C5 7.66667 9 7.66667 9 11.3333C9 15 5 15 5 15H1.6C1.26863 15 1 14.7314 1 14.4V7.66667" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round"/></svg></label>';
						$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_bold]" id="arm_front_font_bold_' . esc_attr( $key ) . '" class="arm_front_font_bold_' . esc_attr( $key ) . '" value="' . esc_attr(sanitize_text_field($font_bold)) . '" />';
						$frontOptHtml .= '<label class="arm_font_style_label ' . ( ( $font_italic == '1' ) ? 'arm_style_active' : '' ) . '" data-value="italic" data-field="arm_front_font_italic_' . esc_attr( $key ) . '"><svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 1L8 0.999999M11 0.999999L8 0.999999M8 0.999999L4 15M4 15L1 15M4 15L7 15" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>';
						$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_italic]" id="arm_front_font_italic_' . esc_attr( $key ) . '" class="arm_front_font_italic_' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field($font_italic)) . '" />';

						$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label ' . ( ( $font_decoration == 'underline' ) ? 'arm_style_active' : '' ) . '" data-value="underline" data-field="arm_front_font_decoration_' . esc_attr( $key ) . '"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 4V10C17 12.7614 14.7614 15 12 15V15C9.23858 15 7 12.7614 7 10V4" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 19H19" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>';
						$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label ' . ( ( $font_decoration == 'line-through' ) ? 'arm_style_active' : '' ) . '" data-value="line-through" data-field="arm_front_font_decoration_' . esc_attr( $key ) . '"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12H20" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.5714 4H10.39C8.51775 4 7 5.61893 7 7.61597C7 9.1724 7.9337 10.5542 9.31797 11.0464L12 12M7 20H13.61C15.4823 20 17 18.3811 17 16.384C17 15.7697 16.8545 15.1826 16.5933 14.6667" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>';
						$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_decoration]" id="arm_front_font_decoration_' . esc_attr( $key ) . '" class="arm_front_font_decoration_' . esc_attr( $key ) . '" value="' . esc_attr(sanitize_text_field($font_decoration)) . '" />';
								$frontOptHtml     .= '</div>';
								$frontOptHtml .= '<div class="arm_front_font_color arm_margin_right_0">';
					
								$frontOptHtml .= '<input type="text" id="arm_front_font_color_' . esc_attr( $key ) . '" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_color]" class="arm_colorpicker" value="' . ( ! empty( $fontVal['font_color'] ) ? esc_attr( sanitize_text_field($fontVal['font_color']) ) : esc_attr('#000000') ) . '">';
								$frontOptHtml .= '</label>';
								$frontOptHtml .= '</div>';
							$frontOptHtml         .= '</div>';
							$frontOptHtml             .= '</div>';
							$frontOptHtml         .= '<div class="arm_margin_top_32"></div>';

					}
					echo $frontOptHtml; //phpcs:ignore
					?>
				</div>
				
				<?php endif; ?>
		</div>
		<div id="global_css_sec"  class="arm_settings_section" style="display:none;">
			<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','custom_css') : ''; //phpcs:ignore ?>			
		</div>
			<div class="arm_submit_btn_container arm_apply_changes_btn_container">
				<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img" style="display:none;" class="arm_submit_btn_loader" width="24" height="24" />&nbsp;<button id="arm_global_settings_btn" class="arm_save_btn" name="arm_global_settings_btn" type="submit"><?php esc_html_e( 'Apply Changes', 'armember-membership' ); ?></button>
				
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</div>
		</form>
	</div>
	<div class="armclear"></div>
	<div class="arm_custom_css_detail_container"></div>
	<div class="arm_edit_form_fields_popup_div popup_wrapper <?php echo ( is_rtl() ) ? 'arm_page_rtl' : ''; ?>">
			<form method="GET" id="arm_edit_preset_fields_form" class="content_wrapper">
				<div>
				<div class="popup_header arm_member_popup_header_wrapper">
					<div class="page_title">
						<span class="add_rule_content"><?php esc_html_e( 'Edit Preset Fields', 'armember-membership' ); ?></span>
						<span class="popup_close_btn arm_popup_close_btn arm_edit_preset_fields_close_btn"></span>
						
					</div>
				</div>
					<div class="popup_content_text arm_edit_form_fields_popup_text arm_text_align_center">
							<div class="arm_width_100_pct" style="margin: 45px auto;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>">
							</div>
					</div>
					<div class="arm_submit_btn_container">
						<div class="arm_preset_field_updated_msg">
								<span class="arm_success_msg"><?php esc_html_e( 'Preset Fields are updated successfully.', 'armember-membership' ); ?></span>
								<span class="arm_error_msg"><?php esc_html_e( 'Sorry, something went wrong while updating prest fields.', 'armember-membership' ); ?></span>
						</div>
						<div class="arm_submit_btn_container arm_preset_fields">
							<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_preset_update_field" class="arm_loader_img arm_submit_btn_loader" style="display: none; top: 5px;" width="20" height="20" />
							<button class="arm_cancel_btn arm_edit_preset_fields_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
							<button class="arm_save_btn arm_setup_save_btn arm_edit_preset_fields_button" type="button"><?php esc_html_e( 'Update', 'armember-membership' ); ?></button>
						</div>
					</div>
					<div class="armclear"></div>
				</div>
			</form>
	</div>
	<div id='arm_clear_form_fields_popup_div' class="popup_wrapper">
		<form method="post" action="#" id="arm_clear_form_fields_frm" class="arm_admin_form arm_clear_form_fields">
			<table  cellspacing="0">
				<tr>
					<td class="arm_clear_field_close_btn arm_popup_close_btn"></td>
					<td class="popup_header"><?php esc_html_e( 'Clear Form Fields', 'armember-membership' ); ?></td>
					<td class="popup_content_text arm_clear_field_wrapper arm_padding_32">
						<?php
						global $arm_member_forms;
						$dbProfileFields = $arm_member_forms->arm_get_db_form_fields();



						if ( ! empty( $dbProfileFields['default'] ) ) {

							foreach ( $dbProfileFields['default'] as $fieldMetaKey => $fieldOpt ) {
								if ( empty( $fieldMetaKey ) || $fieldMetaKey == 'user_pass' || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
									continue;
								}
								?>
								<label class = "account_detail_radio arm_account_detail_options arm_margin_bottom_12">
									<input type = "checkbox" value = "<?php echo esc_attr( sanitize_text_field($fieldMetaKey)); ?>" class = "arm_icheckbox arm_account_detail_fields" name = "clear_fields[<?php echo esc_attr($fieldMetaKey); ?>]" id = "arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>"  checked="checked" disabled="disabled" />
									<label for="arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>"><?php echo stripslashes_deep( $fieldOpt['label'] ); //phpcs:ignore ?></label>
									<div class="arm_list_sortable_icon"></div>
								</label>
								<?php
							}
						}


						if ( ! empty( $dbProfileFields['other'] ) ) {

							foreach ( $dbProfileFields['other'] as $fieldMetaKey => $fieldOpt ) {
								if ( empty( $fieldMetaKey ) || $fieldMetaKey == 'user_pass' || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
									continue;
								}
								$fchecked = '';
								$meta_count = $wpdb->get_var( $wpdb->prepare('SELECT count(`arm_form_field_slug`) FROM `' . $ARMemberLite->tbl_arm_form_field . "` WHERE `arm_form_field_slug`=%s",$fieldMetaKey) );//phpcs:ignore --Reason: $tbl_arm_form_field is a table name.False Positive Alarm
								if ( $meta_count > 0 ) {
									$fchecked = ' checked="checked" disabled="disabled" ';
								}
								?>
								<label class = "account_detail_radio arm_account_detail_options">
									<input type = "checkbox" value = "<?php echo esc_attr($fieldMetaKey); ?>" class = "arm_icheckbox arm_account_detail_fields" name = "clear_fields[<?php echo esc_attr($fieldMetaKey); ?>]" id = "arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>" 
																				 <?php
																					echo $fchecked; //phpcs:ignore
																					?> 
								/>
									<label for="arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>"><?php echo stripslashes_deep( $fieldOpt['label'] ); //phpcs:ignore ?></label>
									<?php
									$meta_count = $wpdb->get_var( $wpdb->prepare('SELECT count(`meta_key`) FROM `' . $wpdb->prefix . "usermeta` WHERE `meta_key`=%s",$fieldMetaKey) );//phpcs:ignore --Reason: $wpdb->prefix . "usermeta is a table name. False Positive Alarm 
									if ( $fchecked == '' && $meta_count > 0 ) { 
										?>
										<span style="color:red;"><?php esc_html_e( '(Entry Exists)', 'armember-membership' ); ?></span>
										<?php
									}
									?>
									<div class="arm_list_sortable_icon"></div>
								</label>
								<?php
							}
						}
						?>
					</td>
				</tr>
			</table>
			
				<div class="popup_content_btn popup_footer arm_padding_top_0 arm_padding_bottom_0 arm_margin_bottom_32">
					<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_clear_field" class="arm_loader_img arm_submit_btn_loader" style="float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>; display: none;" width="20" height="20" />
					<button class="arm_cancel_btn arm_members_close_btn arm_clear_field_close_btn arm_margin_0" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
					<button class="arm_save_btn arm_clear_form_fields_button arm_margin_right_0" type="submit" data-type="add" ><?php esc_html_e( 'Ok', 'armember-membership' ); ?></button>
				</div>
			
		</form>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
// <![CDATA[
var ARM_IMAGE_URL = "<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>";
var ARM_UPDATE_LABEL = "<?php esc_html_e( 'Update', 'armember-membership' ); ?>";
</script>
