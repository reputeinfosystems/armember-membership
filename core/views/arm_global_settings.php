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
			<?php do_action( 'arm_before_global_settings_html', $general_settings ); ?>
			<div class="page_sub_title"><?php esc_html_e( 'General Settings', 'armember-membership' ); ?></div>
			
			
			
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Hide admin bar', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_admin_bar" <?php checked( $general_settings['hide_admin_bar'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[hide_admin_bar]"/>
							<label for="hide_admin_bar" class="armswitch_label"></label>
						</div>
						<label for="hide_admin_bar" class="arm_global_setting_switch_label"><?php esc_html_e( 'Hide admin bar for non-admin users?', 'armember-membership' ); ?></label>
					</td>
				</tr>
				<tr class="form-field arm_exclude_role_for_hide_admin<?php echo ( $general_settings['hide_admin_bar'] == '1' ) ? '' : ' hidden_section'; ?>">
					<th class="arm-form-table-label"><?php esc_html_e( 'Exclude role for hide admin bar', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
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
						<span class="arm_info_text arm_info_text_style" >
							(<?php esc_html_e( 'Admin bar will be displayed to selected roles.', 'armember-membership' ); ?>)
						</span>
					</td>
				</tr>
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','wp-admin') : ''; //phpcs:ignore ?>
				
				
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Hide', 'armember-membership' ); ?> wp-login.php <?php esc_html_e( 'page', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_wp_login" <?php checked( $general_settings['hide_wp_login'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[hide_wp_login]"/>
							<label for="hide_wp_login" class="armswitch_label"></label>
						</div>
						<label for="hide_wp_login" class="arm_global_setting_switch_label"><?php esc_html_e( 'Hide', 'armember-membership' ); ?> <strong>wp-login.php</strong> <?php esc_html_e( 'page for all users?', 'armember-membership' ); ?></label>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Hide register link', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="hide_register_link" <?php checked( $general_settings['hide_register_link'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[hide_register_link]"/>
							<label for="hide_register_link" class="armswitch_label"></label>
						</div>
						<label for="hide_register_link" class="arm_global_setting_switch_label"><?php esc_html_e( 'Hide register link on', 'armember-membership' ); ?> <strong>wp-login.php</strong> <?php esc_html_e( 'page?', 'armember-membership' ); ?></label>
					</td>
				</tr>
					
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','armember_styling') : ''; //phpcs:ignore ?>
				
								
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Auto Lock Shared Account', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
													<?php $general_settings['autolock_shared_account'] = ( isset( $general_settings['autolock_shared_account'] ) ) ? $general_settings['autolock_shared_account'] : 0; ?>
							<input type="checkbox" id="autolock_shared_account" <?php checked( $general_settings['autolock_shared_account'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[autolock_shared_account]"/>
							<label for="autolock_shared_account" class="armswitch_label"></label>
						</div>
												<span class="arm_info_text arm_info_text_style">(<?php esc_html_e( 'By enabling this feature, you can prevent simultaneous multiple logins using same login details', 'armember-membership' ); ?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Enable Gravatars?', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch">
							<input type="checkbox" id="enable_gravatar" <?php checked( $general_settings['enable_gravatar'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[enable_gravatar]"/>
							<label for="enable_gravatar" class="armswitch_label"></label>
						</div>
												<span class="arm_info_text arm_info_text_style">(<?php esc_html_e( 'If BuddyPress plugin is active then use BuddyPress avatar', 'armember-membership' ); ?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Allow image cropping', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
						 <?php $enable_crop = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0; ?>
							<input type="checkbox" id="enable_crop" <?php checked( $enable_crop, '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[enable_crop]"/>
							<label for="enable_crop" class="armswitch_label"></label>
						</div>
						<label for="enable_crop" class="arm_global_setting_switch_label"><?php esc_html_e( 'Allow avatar and cover photo cropping', 'armember-membership' ); ?> </label>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php echo esc_html( 'Enable Spam Protection', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<div class="armswitch arm_global_setting_switch">
						 <?php $spam_protection = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : 0; ?>
							<input type="checkbox" id="spam_protection" <?php checked( $spam_protection, '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[spam_protection]"/>
							<label for="spam_protection" class="armswitch_label"></label>
						</div>
			<label for="spam_protection" class="arm_global_setting_switch_label"><?php echo esc_html( 'Enable hidden spam protection mechanism in signup/login forms', 'armember-membership' ); ?></label>
					</td>
				</tr>
				<tr class="form-field" id="changeCurrency">
					<th class="arm-form-table-label"><?php esc_html_e( 'New user approval', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
											<?php $general_settings['user_register_verification'] = isset( $general_settings['user_register_verification'] ) ? $general_settings['user_register_verification'] : ''; ?>
						<input type='hidden' id='arm_new_user_approval' name="arm_general_settings[user_register_verification]" value="<?php echo esc_attr( sanitize_text_field($general_settings['user_register_verification']) ); ?>" />
						<dl class="arm_selectbox column_level_dd">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_new_user_approval">
									<li data-label="<?php esc_html_e( 'Automatic approve', 'armember-membership' ); ?>" data-value="auto"><?php esc_html_e( 'Automatic approve', 'armember-membership' ); ?></li>
									<li data-label="<?php esc_html_e( 'Email verified approve', 'armember-membership' ); ?>" data-value="email"><?php esc_html_e( 'Email verified approve', 'armember-membership' ); ?></li>
									<li data-label="<?php esc_html_e( 'Manual approve by admin', 'armember-membership' ); ?>" data-value="manual"><?php esc_html_e( 'Manual approve by admin', 'armember-membership' ); ?></li>
								</ul>
							</dd>
						</dl>
					</td>
				</tr>
				<tr class="form-field" id="profilePermalinkBase">
					<th class="arm-form-table-label"><?php esc_html_e( 'Default currency', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<?php
						$currencies                = apply_filters( 'arm_available_currencies', $currencies );
						$paymentcurrency           = $general_settings['paymentcurrency'];
						$custom_currency_status    = isset( $general_settings['custom_currency']['status'] ) ? $general_settings['custom_currency']['status'] : '';
						$custom_currency_symbol    = isset( $general_settings['custom_currency']['symbol'] ) ? $general_settings['custom_currency']['symbol'] : '';
						$custom_currency_shortname = isset( $general_settings['custom_currency']['shortname'] ) ? $general_settings['custom_currency']['shortname'] : '';
						$custom_currency_place     = isset( $general_settings['custom_currency']['place'] ) ? $general_settings['custom_currency']['place'] : '';
						?>
						<input type='hidden' id='arm_payment_currency' name="arm_general_settings[paymentcurrency]" value="<?php echo esc_attr($paymentcurrency); ?>" />
						<dl class="arm_selectbox column_level_dd arm_default_currency_box <?php echo ( $custom_currency_status == 1 ) ? 'disabled' : ''; ?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_payment_currency">
									<?php foreach ( $currencies as $key => $value ) : ?>
									<li data-label="<?php echo esc_attr($key) . " ( ".esc_attr($value)." ) "; ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html($key) . " (". esc_html($value) .") "; ?></li>
									<?php endforeach; ?>
								</ul>
							</dd>
						</dl>

						<?php
							$arm_specific_currency_position = isset( $general_settings['arm_specific_currency_position'] ) ? $general_settings['arm_specific_currency_position'] : 'suffix';
						?>

						<div class="arm_currency_prefix_suffix_display" <?php echo ( $paymentcurrency != 'EUR' ) ? "style='display: none;'" : ''; ?>>
							<div>
								<input type="radio" id="default_currency_prefix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked( $arm_specific_currency_position, 'prefix' ); ?> value="prefix" <?php echo ( $custom_currency_status == 1 ) ? 'disabled' : ''; ?> /><label class="default_currency_prefix_suffix_lbl" for="default_currency_prefix_val" <?php echo ( $custom_currency_status == 1 ) ? 'style="cursor: no-drop;"' : ''; ?>><?php esc_html_e( 'Prefix', 'armember-membership' ); ?></label>
							</div>
							<div>
								<input type="radio" id="default_currency_suffix_val" name="arm_general_settings[arm_specific_currency_position]" class="arm_general_input arm_iradio default_currency_prefix_suffix_val" <?php checked( $arm_specific_currency_position, 'suffix' ); ?> value="suffix" <?php echo ( $custom_currency_status == 1 ) ? 'disabled' : ''; ?> /><label class="default_currency_prefix_suffix_lbl" for="default_currency_suffix_val" <?php echo ( $custom_currency_status == 1 ) ? 'style="cursor: no-drop;"' : ''; ?>><?php esc_html_e( 'Suffix', 'armember-membership' ); ?></label>
							</div>
						</div>


						<div class="armclear"></div>
						<span class="arm_currency_seperator_text_style"><?php esc_html_e( 'OR', 'armember-membership' ); ?></span>
						<div class="armclear"></div>
						<div class="armGridActionTD arm_custom_currency_options_container">
							<input type="hidden" class="custom_currency_symbol" name="arm_general_settings[custom_currency][symbol]" value="<?php echo esc_attr( sanitize_text_field($custom_currency_symbol) ); ?>">
							<input type="hidden" class="custom_currency_shortname" name="arm_general_settings[custom_currency][shortname]" value="<?php echo esc_attr( sanitize_text_field($custom_currency_shortname) ); ?>">
							<input type="hidden" class="custom_currency_place" name="arm_general_settings[custom_currency][place]" value="<?php echo esc_attr( sanitize_text_field($custom_currency_place) ); ?>">

							<div class="armclear"></div>
							<label class="arm_custom_currency_checkbox_label"><input type="checkbox" class="arm_custom_currency_checkbox arm_icheckbox" value="1" name="arm_general_settings[custom_currency][status]" <?php checked( $custom_currency_status, 1 ); ?>><span><?php esc_html_e( 'Set Custom Currency', 'armember-membership' ); ?></span></label>
							<div class="arm_confirm_box_custom_currency arm_no_hide" id="arm_confirm_box_custom_currency">
								<div class="arm_confirm_box_body arm_max_width_100_pct" >
									<div class="arm_confirm_box_arrow"></div>
									<div class="arm_confirm_box_text arm_custom_currency_fields arm_text_align_left" >
										<table>
											<tr>
												<th><?php esc_html_e( 'Currency Symbol', 'armember-membership' ); ?></th>
												<td>
													<input type="text" id="custom_currency_symbol" value="<?php echo ( ! empty( $custom_currency_symbol ) ) ? "".esc_attr($custom_currency_symbol)."" : ''; ?>">
													<span class="arm_error_msg symbol_error" style="display:none;"><?php esc_html_e( 'Please enter symbol.', 'armember-membership' ); ?></span>
													<span class="arm_error_msg invalid_symbol_error" style="display:none;"><?php esc_html_e( 'Please enter valid symbol.', 'armember-membership' ); ?></span>
												</td>
											</tr>
											<tr>
												<th><?php esc_html_e( 'Currency Shortname', 'armember-membership' ); ?></th>
												<td>
													<input type="text" id="custom_currency_shortname" value="<?php echo ( ! empty( $custom_currency_shortname ) ) ? "".esc_attr($custom_currency_shortname)."" : ''; ?>">
													<span class="arm_error_msg shortname_error" style="display:none;"><?php esc_html_e( 'Please enter shortname.', 'armember-membership' ); ?></span>
													<span class="arm_error_msg invalid_shortname_error" style="display:none;"><?php esc_html_e( 'Please enter valid shortname.', 'armember-membership' ); ?></span>
												</td>
											</tr>
											<tr>
												<th><?php esc_html_e( 'Symbol will be display as', 'armember-membership' ); ?></th>
												<td>
													<input type="hidden" id="custom_currency_place" value="<?php echo ( ! empty( $custom_currency_place ) ) ? "".esc_attr($custom_currency_place)."" : 'prefix'; ?>"/>
													<dl class="arm_selectbox column_level_dd arm_width_130">
														<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="custom_currency_place">
																<li data-label="<?php esc_html_e( 'Prefix', 'armember-membership' ); ?>" data-value="prefix"><?php esc_html_e( 'Prefix', 'armember-membership' ); ?></li>
																<li data-label="<?php esc_html_e( 'Suffix', 'armember-membership' ); ?>" data-value="suffix"><?php esc_html_e( 'Suffix', 'armember-membership' ); ?></li>
															</ul>
														</dd>
													</dl>
												</td>
											</tr>
										</table>
									</div>
									<div class='arm_confirm_box_btn_container'>
										<button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" id="arm_custom_currency_ok_btn"><?php esc_html_e( 'Add', 'armember-membership' ); ?></button>
										<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideCustomCurrencyBox();"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
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
						<?php
						if ( $custom_currency_status == 1 ) {
							$paymentcurrency = $custom_currency_shortname;
						}
						$currency_warring = $arm_payment_gateways->arm_check_currency_status( $paymentcurrency );
						?>
						<span class="arm_global_setting_currency_warring arm-note-message --warning" style="color: #676767;<?php echo ( empty( $currency_warring ) ) ? 'display:none;' : ''; ?>"><?php echo esc_html($currency_warring); ?></span>
					</td>
				</tr>
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','currency_decimal') : ''; //phpcs:ignore ?>
				<tr class="form-field" style="<?php echo ( ! $arm_social_feature->isSocialFeature ) ? 'display:none;' : ''; ?>">
					<th class="arm-form-table-label"><?php esc_html_e( 'Profile Permalink Base', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<?php
						$permalink_base = ( isset( $general_settings['profile_permalink_base'] ) ) ? $general_settings['profile_permalink_base'] : 'user_login';
						$profileUrl_user_login = '<b>username</b>/';
						$profileUrl_user_id    = '<b>user_id</b>/';
						if ( $is_permalink ) {
							if(!empty($arm_global_settings->profile_url)){
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
						<input type='hidden' id="arm_profile_permalink_base" name="arm_general_settings[profile_permalink_base]" value="<?php echo esc_html($permalink_base); ?>" />
						<dl class="arm_selectbox column_level_dd">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_profile_permalink_base">
									<li data-label="<?php esc_html_e( 'Username', 'armember-membership' ); ?>" data-value="user_login"><?php esc_html_e( 'Username', 'armember-membership' ); ?></li>
									<li data-label="<?php esc_html_e( 'User ID', 'armember-membership' ); ?>" data-value="user_id"><?php esc_html_e( 'User ID', 'armember-membership' ); ?></li>
									
								</ul>
							</dd>
						</dl>
						<div class="armclear"></div>
						<span class="arm_info_text arm_profile_user_login" style="<?php echo ( $permalink_base == 'user_login' ) ? '' : 'display: none;'; ?>">e.g. <?php echo $profileUrl_user_login; //phpcs:ignore ?></span>
						<span class="arm_info_text arm_profile_user_id" style="<?php echo ( $permalink_base == 'user_id' ) ? '' : 'display: none;'; ?>">e.g. <?php echo $profileUrl_user_id; //phpcs:ignore ?></span>
					</td>
				</tr>
								
								
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Load JS & CSS in all pages', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">						
						<div class="armswitch arm_global_setting_switch arm_margin_top_5">
							<input type="checkbox" id="arm_enqueue_all_js_css" <?php checked( $general_settings['enqueue_all_js_css'], '1' ); ?> value="1" class="armswitch_input" name="arm_general_settings[enqueue_all_js_css]"/>
							<label for="arm_enqueue_all_js_css" class="armswitch_label"></label>
						</div>
						<span class="arm_info_text arm_info_text_style">(<strong><?php esc_html_e( 'Not recommended', 'armember-membership' ); ?></strong> - <?php esc_html_e( 'If you have any js/css loading issue in your theme, only in that case you should enable this settings', 'armember-membership' ); ?>)</span>
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e('Help us improve ARMember by sending anonymous usage stats','armember-membership');?></th>
					<td class="arm-form-table-content">
                        <?php $general_settings['arm_anonymous_data'] = !empty($general_settings['arm_anonymous_data']) ? 1 : 0;?>						
						<div class="armswitch arm_global_setting_switch arm_margin_top_5">
							<input type="checkbox" id="arm_anonymous_data" <?php checked($general_settings['arm_anonymous_data'], '1');?> value="1" class="armswitch_input" name="arm_general_settings[arm_anonymous_data]"/>
							<label for="arm_anonymous_data" class="armswitch_label"></label>
						</div>
					</td>
				</tr>
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','badge_icons') : ''; //phpcs:ignore ?>
			</table>
			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php esc_html_e( 'Email Settings', 'armember-membership' ); ?></div>
			<table class="form-table">
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'From/Reply to name', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_from_name" type="text" name="arm_email_from_name" value="<?php echo esc_attr( ! empty( $all_email_settings['arm_email_from_name'] ) ? stripslashes( sanitize_text_field($all_email_settings['arm_email_from_name']) ) : get_option( 'blogname' ) ); //phpcs:ignore ?>" >
						<span id="email_from_name_error" class="arm_error_msg email_from_name_error" style="display:none;"><?php esc_html_e( 'Please enter From Name.', 'armember-membership' ); ?></span>
						 <span id="invalid_email_from_name_error" class="arm_error_msg invalid_email_from_name_error" style="display:none;"><?php esc_html_e( 'Please enter valid From Name.', 'armember-membership' ); ?></span>         
					</td>
				</tr>
				<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'From/Reply to email', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_from_email" type="email" name="arm_email_from_email" value="<?php echo ( ! empty( $all_email_settings['arm_email_from_email'] ) ? esc_attr($all_email_settings['arm_email_from_email']) : get_option( 'admin_email' ) ); //phpcs:ignore ?>" >
												<span id="email_from_email_error" class="arm_error_msg email_from_email_error" style="display:none;"><?php esc_html_e( 'Please enter From Email ID.', 'armember-membership' ); ?></span>
										<span id="invalid_email_from_email_error" class="arm_error_msg invalid_email_from_email_error" style="display:none;"><?php esc_html_e( 'Please enter valid From Email ID.', 'armember-membership' ); ?></span>
					</td>
				</tr>
								<tr class="form-field">
					<th class="arm-form-table-label"><?php esc_html_e( 'Admin email', 'armember-membership' ); ?></th>
					<td class="arm-form-table-content">
						<input id="arm_email_admin_email" type="email" name="arm_email_admin_email" value="<?php echo esc_attr( ! empty( $all_email_settings['arm_email_admin_email'] ) ? sanitize_text_field($all_email_settings['arm_email_admin_email']) : get_option( 'admin_email' ) ); //phpcs:ignore ?>" >
												<span id="email_admin_email_error" class="arm_error_msg email_admin_email_error" style="display:none;"><?php esc_html_e( 'Please enter Admin Email ID.', 'armember-membership' ); ?></span>
										<span id="invalid_email_admin_email_error" class="arm_error_msg invalid_email_admin_email_error" style="display:none;"><?php esc_html_e( 'Please enter valid Admin Email ID.', 'armember-membership' ); ?></span>
					<?php $ae_tooltip = esc_html__( 'You can add multiple Admin email address separated by comma in case of you want to send email to more than one email address.', 'armember-membership' ); ?>
										<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_html($ae_tooltip); ?>"></i>
										</td>
				</tr>
				<tr class="form-field">
					<th class="arm_email_settings_content_label"><?php esc_html_e( 'Email notification', 'armember-membership' ); ?></th>
					<td class="arm_email_settings_content_text arm_vertical_align_top arm_padding_10">
						<div class="arm_email_settings_select_text">
							<div class="arm_email_settings_select_text_inner">
							<?php $all_email_settings['arm_email_server'] = ( isset( $all_email_settings['arm_email_server'] ) ) ? $all_email_settings['arm_email_server'] : 'wordpress_server'; ?>
								<input type="radio" id="arm_email_server_ws" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked( $all_email_settings['arm_email_server'], 'wordpress_server' ); ?> name="arm_email_server" value="wordpress_server" /><label for="arm_email_server_ws" class="arm_email_settings_help_text"><?php esc_html_e( 'WordPress Server', 'armember-membership' ); ?></label>
							</div>
							<div class="arm_email_settings_select_text_inner">
								<input type="radio" id="arm_email_server_smtps" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked( $all_email_settings['arm_email_server'], 'smtp_server' ); ?> name="arm_email_server" value="smtp_server" /><label for="arm_email_server_smtps" class="arm_email_settings_help_text"><?php esc_html_e( 'SMTP Server', 'armember-membership' ); ?></label>
							</div>
							<div class="arm_email_settings_select_text_inner">
								<input type="radio" id="arm_email_server_phpm" class="arm_general_input arm_email_notification_radio arm_iradio" <?php checked( $all_email_settings['arm_email_server'], 'phpmailer' ); ?> name="arm_email_server" value="phpmailer" /><label for="arm_email_server_phpm" class="arm_email_settings_help_text"><?php esc_html_e( 'PHP Mailer', 'armember-membership' ); ?></label>
							</div>
							<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','gmail_options') : ''; //phpcs:ignore ?>
						</div>
						<div class="arm_smtp_slide_form arm_email_server_smtp">
							<table class="form-sub-table" width="100%">

								<tr>
									<th class="arm_email_settings_content_label arm_min_width_100"><?php esc_html_e( 'Authentication', 'armember-membership' ); ?></th>
									<td class="arm_email_settings_content_text">
										<?php $arm_mail_authentication = ( isset( $all_email_settings['arm_mail_authentication'] ) ) ? $all_email_settings['arm_mail_authentication'] : '1'; ?>
										
										
										<label class="arm_custom_currency_checkbox_label"><input type="checkbox" class="arm_icheckbox" value="1" id="arm_mail_authentication" name="arm_mail_authentication" onchange="arm_mail_authentication_func(this.value);" <?php checked( $arm_mail_authentication, 1 ); ?>><span><?php esc_html_e( 'Enable SMTP authentication', 'armember-membership' ); ?></span></label>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label arm_min_width_100"><?php esc_html_e( 'Mail Server', 'armember-membership' ); ?> *</th>
									<td class="arm_email_settings_content_text">
																			<?php $arm_mail_server = ( isset( $all_email_settings['arm_mail_server'] ) ) ? $all_email_settings['arm_mail_server'] : ''; ?>
										<input type="text" id="arm_mail_server" name="arm_mail_server" value="<?php echo ( isset( $all_email_settings['arm_mail_server'] ) ) ? esc_attr( sanitize_text_field($all_email_settings['arm_mail_server']) ) : ''; ?>" class="arm_mail_server_input arm_width_390" >
										<span class="error arm_invalid" id="arm_mail_server_error" style="display: none;"><?php esc_html_e( 'Mail Server can not be left blank.', 'armember-membership' ); ?></span>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label"><?php esc_html_e( 'Port', 'armember-membership' ); ?> *</th>
									<td class="arm_email_settings_content_text">
										<?php $arm_mail_port = ( isset( $all_email_settings['arm_mail_port'] ) ) ? $all_email_settings['arm_mail_port'] : ''; ?>
										<input type="text" id="arm_port" class="arm_width_390" name="arm_mail_port" value="<?php echo ( isset( $all_email_settings['arm_mail_port'] ) ) ? esc_attr( sanitize_text_field($all_email_settings['arm_mail_port'])) : ''; ?>" />
										<span class="error arm_invalid" id="arm_mail_port_error" style="display: none;"><?php esc_html_e( 'Port can not be left blank.', 'armember-membership' ); ?></span>
									</td>
								</tr>
								<tr class="arm_email_settings_login_name_main" style="
								<?php
								if ( empty( $arm_mail_authentication ) ) {
									echo 'display:none;'; }
								?>
								">
									<th class="arm_email_settings_content_label"><?php esc_html_e( 'Login Name', 'armember-membership' ); ?> *</th>
									<td class="arm_email_settings_content_text">
																			<?php $arm_mail_login_name = ( isset( $all_email_settings['arm_mail_login_name'] ) ) ? $all_email_settings['arm_mail_login_name'] : ''; ?>
										<input type="text" id="arm_login_name" class="arm_width_390" value="<?php echo ( isset( $all_email_settings['arm_mail_login_name'] ) ) ? esc_attr( sanitize_text_field($all_email_settings['arm_mail_login_name'])) : ''; ?>" name="arm_mail_login_name" />
										<span class="error arm_invalid" id="arm_mail_login_name_error" style="display: none;"><?php esc_html_e( 'Login Name can not be left blank.', 'armember-membership' ); ?></span>
									</td>
								</tr>
								<tr class="arm_email_settings_password_main" style="
								<?php
								if ( empty( $arm_mail_authentication ) ) {
									echo 'display:none;'; }
								?>
								">
									<th class="arm_email_settings_content_label"><?php esc_html_e( 'Password', 'armember-membership' ); ?> *</th>
									<td class="arm_email_settings_content_text">
																			<?php $arm_mail_pssword = ( isset( $all_email_settings['arm_mail_password'] ) ) ? $all_email_settings['arm_mail_password'] : ''; ?>
										<input type="password" id="arm_password" autocomplete="off" value="<?php echo ( isset( $all_email_settings['arm_mail_password'] ) ) ? esc_attr($all_email_settings['arm_mail_password']) : ''; ?>" name="arm_mail_password" class="arm_width_390"/>
										<span class="error arm_invalid" id="arm_mail_password_error" style="display: none;"><?php esc_html_e( 'Password can not be left blank.', 'armember-membership' ); ?></span>
									</td>
								</tr>
								<tr>
									<th class="arm_email_settings_content_label"><?php esc_html_e( 'Encryption', 'armember-membership' ); ?></th>
									<td class="arm_email_settings_content_text">
										<div class="arm_email_settings_select_text">     	
											<div id="arm_first_enc" class="arm_email_settings_select_text_inner">
												<?php
												$selected_enc = ( isset( $all_email_settings['arm_smtp_enc'] ) ) ? ( ( $all_email_settings['arm_smtp_enc'] == 'ssl' || $all_email_settings['arm_smtp_enc'] == 'tls' ) ? '1' : '0' ) : '0';
												$all_email_settings['arm_smtp_enc'] = ( isset( $all_email_settings['arm_smtp_enc'] ) ) ? $all_email_settings['arm_smtp_enc'] : '0';
												?>
												<input type="radio" id="arm_smtp_enc_none" class="arm_general_input arm_iradio" <?php checked( $selected_enc, '0' ); ?>  name="arm_smtp_enc" value="none" /><label for="arm_smtp_enc_none" class="arm_email_settings_help_text arm_margin_right_0" ><?php esc_html_e( 'None', 'armember-membership' ); ?></label>
											</div>
											<div class="arm_email_settings_select_text_inner">
												<input type="radio" id="arm_smtp_enc_ssl" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'ssl' ); ?> name="arm_smtp_enc" value="ssl" /><label for="arm_smtp_enc_ssl" class="arm_email_settings_help_text arm_margin_right_0" ><?php esc_html_e( 'SSL', 'armember-membership' ); ?></label>
											</div>
											<div class="arm_email_settings_select_text_inner">
												<input type="radio" id="arm_smtp_enc_tls" class="arm_general_input arm_iradio" <?php checked( $all_email_settings['arm_smtp_enc'], 'tls' ); ?> name="arm_smtp_enc" value="tls" /><label for="arm_smtp_enc_tls" class="arm_email_settings_help_text arm_margin_right_0"><?php esc_html_e( 'TLS', 'armember-membership' ); ?></label>
											</div>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','gmail_section') : ''; //phpcs:ignore ?>
						<div class="arm_smtp_slide_form arm_test_email_container">
                <table class="form-sub-table" width="100%">
                <tr>
                    <th class="arm_email_settings_content_label"><b><?php esc_html_e('Send Test E-mail', 'armember-membership');?></b></th>
                    <td class="arm_email_settings_content_text">
                        <label id="arm_success_test_mail" class="arm_success_test_mail_label" style="display:none;"><?php esc_html_e('Your test mail is successfully sent.', 'armember-membership');?></label>
                        <label id="arm_error_test_mail" class="arm_error_test_mail_label" style="display:none;"><?php esc_html_e('Your test mail is not sent for some reason, Please check your SMTP setting.', 'armember-membership');?></label>
                    </td>
                </tr> 
                <tr>
                    <th class="arm_email_settings_content_label arm_gmail_settings_content_label"><?php esc_html_e('To', 'armember-membership');?> *</th>
                    <td class="arm_email_settings_content_text">
                        <input type="text" id="arm_test_email_to" class="arm_width_390" name="arm_test_email_to" value="" />
                        <span class="error arm_invalid" id="arm_test_email_to_error" style="display: none;"><?php esc_html_e('To can not be left blank.', 'armember-membership');?></span>
                    </td>
                </tr>
                <tr>
                    <th class="arm_email_settings_content_label arm_gmail_settings_content_label"><?php esc_html_e('Message', 'armember-membership');?> *</th>
                    <td class="arm_email_settings_content_text">
                        <textarea id="arm_test_email_msg" class="arm_width_390" value="" name="arm_test_email_msg" ></textarea>
                        <span class="error arm_invalid" id="arm_test_email_msg_error" style="display: none;"><?php esc_html_e('Message can not be left blank.', 'armember-membership');?></span>
                    </td>
                </tr>
                <tr>
                    <th class="arm_email_settings_content_label arm_gmail_settings_content_label"></th>
                    <td class="arm_email_settings_content_text">
                        
                        <button type="button" class="arm_save_btn" id="arm_send_test_mail"><?php esc_html_e('Send test mail', 'armember-membership');?></button><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL ?>/arm_loader.gif" id="arm_send_test_mail_loader" class="arm_submit_btn_loader" width="24" height="24" style="display: none;" /><br/><span style="font-style:italic;">(<?php esc_html_e('Test e-mail works only after configure SMTP server settings', 'armember-membership');?>)</span><?php //phpcs:ignore ?>
                    </td>
                </tr>
                </table>
            </div>
					</td>
				</tr>
			</table>
			<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','invoices_tax') : ''; //phpcs:ignore ?>

			<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','google_recaptcha') : ''; //phpcs:ignore ?>
			
			<div class="arm_solid_divider"></div>
			
			<div class="page_sub_title"><?php esc_html_e( 'Manage Preset Form Fields', 'armember-membership' ); ?></div>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
						<div class="arm_manage_preset_fields">
						<div class="arm_manage_preset_fields_btn">
						 <input type="button" value="<?php esc_html_e( 'Edit Preset Form Fields', 'armember-membership' ); ?>" onclick="arm_open_edit_field_popup();" id="arm_edit_form_fields" class="armemailaddbtn arm_width_220" title="" >
						 </div>
						 <div class="arm_manage_preset_fields_text">
							<span class="arm_info_text"><?php esc_html_e( 'To edit specific form preset fields, click on this button, popup opens, edit fields which you want to update and click on update button.', 'armember-membership' ); ?></span>
							</div>
						 </div>
						 <div class="arm_manage_preset_fields arm_margin_top_30" >
						 <div class="arm_manage_preset_fields_btn">
							<input type="button" value="<?php esc_html_e( 'Clear Preset Form Fields', 'armember-membership' ); ?>" onclick="arm_open_clear_field_popup();" id="arm_clear_form_fields" class="armemailaddbtn arm_width_220"  >
							</div>
							<div class="arm_manage_preset_fields_text">
								<span class="arm_info_text"><?php esc_html_e( 'To remove specific form fields with its value, click on this button, popup opens, select fields which you want to remove from everywhere.', 'armember-membership' ); ?></span>
								</div>
							</div>
						   
						</td>
					</tr>
				</tbody>
			</table>

			<div class="arm_solid_divider"></div>
			<div class="page_sub_title"><?php esc_html_e( 'Email notification scheduler setting', 'armember-membership' ); ?>
			<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'when you change value from below dropdown and save it then it will set new schedular and remove previous one.', 'armember-membership' ); ?>"></i>
			</div>
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th><?php esc_html_e( 'Schedule Every', 'armember-membership' ); ?> </th>
						<td>
							<?php $arm_email_schedular_time = isset( $general_settings['arm_email_schedular_time'] ) ? $general_settings['arm_email_schedular_time'] : 12; ?>
							<input type="hidden" name="arm_general_settings[arm_email_schedular_time]" id="arm_email_schedular_time" value="<?php echo esc_html( sanitize_text_field($arm_email_schedular_time) ); ?>" />
							<dl class="arm_selectbox column_level_dd arm_width_200 arm_max_width_200">
								<dt>
								<span></span>
								<input type="text" style="display:none;" value="" class="arm_autocomplete"  />
								<i class="armfa armfa-caret-down armfa-lg"></i>
								</dt>
								<dd>
									<ul data-id="arm_email_schedular_time" style="display:none;">
										<?php
										for ( $ct = 1; $ct <= 24; $ct++ ) {
											echo "<li data-value='{$ct}' data-label='{$ct}'>{$ct}</li>"; //phpcs:ignore
										}
										?>
									</ul>
								</dd>
							</dl>
							<span><?php esc_html_e( 'Hours', 'armember-membership' ); ?></span>
						</td>
					</tr>
					<?php do_action( 'arm_cron_schedular_from_outside' ); ?>
				</tbody>
			</table>
			<div class="arm_solid_divider"></div>
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
				<div class="page_sub_title"><?php esc_html_e( 'Front End Font Settings', 'armember-membership' ); ?></div>
				<table class="form-table">
					<?php
					$frontOptHtml = '';
					$frontOptions = isset( $general_settings['front_settings'] ) ? $general_settings['front_settings'] : array();
					foreach ( $frontfontOptions as $key => $title ) {
						$fontVal         = ( ( ! empty( $frontOptions[ $key ] ) ) ? $frontOptions[ $key ] : array() );
						$font_bold       = ( isset( $fontVal['font_bold'] ) && $fontVal['font_bold'] == '1' ) ? 1 : 0;
						$font_italic     = ( isset( $fontVal['font_italic'] ) && $fontVal['font_italic'] == '1' ) ? 1 : 0;
						$font_decoration = ( isset( $fontVal['font_decoration'] ) ) ? $fontVal['font_decoration'] : '';
						$frontOptHtml   .= '<tr class="form-field">';
						$frontOptHtml   .= '<th class="arm-form-table-label">' . esc_attr( $title );
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
						$frontOptHtml .= ' <i class="arm_helptip_icon armfa armfa-question-circle" title="' . esc_attr( $tooltip_title ) . '"></i></th>';
						$frontOptHtml .= '<td>';
						$frontOptHtml .= '<input type="hidden" id="arm_front_font_family_' . esc_attr( $key ) . '" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_family]" value="' . ( ( ! empty( $fontVal['font_family'] ) ) ? esc_attr( sanitize_text_field($fontVal['font_family']) ) : esc_attr('Helvetica') ) . '"/>';
						$frontOptHtml .= '<dl class="arm_selectbox column_level_dd arm_width_200 arm_margin_right_10">';
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
						$frontOptHtml .= '<div class="arm_front_font_color">';
						$frontOptHtml .= '<label class="arm_colorpicker_label" style="background-color:' . ( ! empty( $fontVal['font_color'] ) ? esc_attr( sanitize_text_field($fontVal['font_color']) ) : esc_attr('#000000') ) . '">';
						$frontOptHtml .= '<input type="text" id="arm_front_font_color_' . esc_attr( $key ) . '" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_color]" class="arm_colorpicker" value="' . ( ! empty( $fontVal['font_color'] ) ? esc_attr( sanitize_text_field($fontVal['font_color']) ) : esc_attr('#000000') ) . '">';
						$frontOptHtml .= '</label>';
						$frontOptHtml .= '</div>';
						$frontOptHtml .= '<div class="arm_font_style_options arm_front_font_style_options">';
						$frontOptHtml .= '<label class="arm_font_style_label ' . ( ( $font_bold == '1' ) ? 'arm_style_active' : '' ) . '" data-value="bold" data-field="arm_front_font_bold_' . esc_attr( $key ) . '"><i class="armfa armfa-bold"></i></label>';
						$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_bold]" id="arm_front_font_bold_' . esc_attr( $key ) . '" class="arm_front_font_bold_' . esc_attr( $key ) . '" value="' . esc_attr(sanitize_text_field($font_bold)) . '" />';
						$frontOptHtml .= '<label class="arm_font_style_label ' . ( ( $font_italic == '1' ) ? 'arm_style_active' : '' ) . '" data-value="italic" data-field="arm_front_font_italic_' . esc_attr( $key ) . '"><i class="armfa armfa-italic"></i></label>';
						$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_italic]" id="arm_front_font_italic_' . esc_attr( $key ) . '" class="arm_front_font_italic_' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field($font_italic)) . '" />';

									$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label ' . ( ( $font_decoration == 'underline' ) ? 'arm_style_active' : '' ) . '" data-value="underline" data-field="arm_front_font_decoration_' . esc_attr( $key ) . '"><i class="armfa armfa-underline"></i></label>';
									$frontOptHtml .= '<label class="arm_font_style_label arm_decoration_label ' . ( ( $font_decoration == 'line-through' ) ? 'arm_style_active' : '' ) . '" data-value="line-through" data-field="arm_front_font_decoration_' . esc_attr( $key ) . '"><i class="armfa armfa-strikethrough"></i></label>';
									$frontOptHtml .= '<input type="hidden" name="arm_general_settings[front_settings][' . esc_attr( $key ) . '][font_decoration]" id="arm_front_font_decoration_' . esc_attr( $key ) . '" class="arm_front_font_decoration_' . esc_attr( $key ) . '" value="' . esc_attr(sanitize_text_field($font_decoration)) . '" />';
								$frontOptHtml     .= '</div>';
							$frontOptHtml         .= '</td>';
						$frontOptHtml             .= '</tr>';
					}
					echo $frontOptHtml; //phpcs:ignore
					?>
				</table>
				
				<?php endif; ?>
				<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_global_settings_section','custom_css') : ''; //phpcs:ignore ?>			
			<?php do_action( 'arm_after_global_settings_html', $general_settings ); ?>
			<div class="arm_submit_btn_container">
				<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img" style="display:none;" class="arm_submit_btn_loader" width="24" height="24" />&nbsp;<button id="arm_global_settings_btn" class="arm_save_btn" name="arm_global_settings_btn" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
				
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</div>
		</form>
	</div>
	<div class="armclear"></div>
	<div class="arm_custom_css_detail_container"></div>
	<div class="arm_edit_form_fields_popup_div popup_wrapper <?php echo ( is_rtl() ) ? 'arm_page_rtl' : ''; ?>">
			<form method="GET" id="arm_edit_preset_fields_form" class="arm_admin_form">
				<div>
					<div class="popup_header">
						<span class="popup_close_btn arm_popup_close_btn arm_edit_preset_fields_close_btn"></span>
						
						<span class="add_rule_content"><?php esc_html_e( 'Edit Preset Fields', 'armember-membership' ); ?></span>
					</div>
					<div class="popup_content_text arm_edit_form_fields_popup_text arm_text_align_center" >
							<div class="arm_width_100_pct" style="margin: 45px auto;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>">
							</div>
					</div>
					<div class="popup_content_btn popup_footer">
						<div class="arm_preset_field_updated_msg">
								<span class="arm_success_msg"><?php esc_html_e( 'Preset Fields are updated successfully.', 'armember-membership' ); ?></span>
								<span class="arm_error_msg"><?php esc_html_e( 'Sorry, something went wrong while updating prest fields.', 'armember-membership' ); ?></span>
						</div>
						<div class="popup_content_btn_wrapper">
							<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_preset_update_field" class="arm_loader_img arm_submit_btn_loader" style="float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
							<button class="arm_save_btn arm_edit_preset_fields_button" type="button"><?php esc_html_e( 'Update', 'armember-membership' ); ?></button>
							<button class="arm_cancel_btn arm_edit_preset_fields_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
						</div>
					</div>
					<div class="armclear"></div>
				</div>
			</form>
	</div>
	<div id='arm_clear_form_fields_popup_div' class="popup_wrapper">
		<form method="post" action="#" id="arm_clear_form_fields_frm" class="arm_admin_form">
			<table  cellspacing="0">
				<tr>
					<td class="arm_clear_field_close_btn arm_popup_close_btn"></td>
					<td class="popup_header"><?php esc_html_e( 'Clear Form Fields', 'armember-membership' ); ?></td>
					<td class="popup_content_text arm_clear_field_wrapper">
						<?php
						global $arm_member_forms;
						$dbProfileFields = $arm_member_forms->arm_get_db_form_fields();



						if ( ! empty( $dbProfileFields['default'] ) ) {

							foreach ( $dbProfileFields['default'] as $fieldMetaKey => $fieldOpt ) {
								if ( empty( $fieldMetaKey ) || $fieldMetaKey == 'user_pass' || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
									continue;
								}
								?>
								<label class = "account_detail_radio arm_account_detail_options">
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
					<td class="popup_content_btn popup_footer">
						<div class="popup_content_btn_wrapper">
							<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_clear_field" class="arm_loader_img arm_submit_btn_loader" style="float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />

							<button class="arm_save_btn arm_clear_form_fields_button" type="submit" data-type="add"><?php esc_html_e( 'Ok', 'armember-membership' ); ?></button>
							<button class="arm_cancel_btn arm_clear_field_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
// <![CDATA[
var ARM_IMAGE_URL = "<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>";
var ARM_UPDATE_LABEL = "<?php esc_html_e( 'Update', 'armember-membership' ); ?>";
</script>
