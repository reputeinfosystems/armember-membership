<?php
global $wpdb, $ARMemberLite, $arm_global_settings, $arm_access_rules, $arm_drip_rules, $arm_subscription_plans, $arm_member_forms, $arm_social_feature;


$redirection_settings = get_option( 'arm_redirection_settings' );
$redirection_settings = maybe_unserialize( $redirection_settings );

$arm_forms = $arm_member_forms->arm_get_member_forms_and_fields_by_type( 'registration', 'arm_form_id, arm_form_type, arm_form_label', false );

$arm_edit_profile_forms = $arm_member_forms->arm_get_member_forms_and_fields_by_type('edit_profile', 'arm_form_id, arm_form_type, arm_form_label', false);

$arm_redirection_login_type_main         = ( isset( $redirection_settings['login']['main_type'] ) && ! empty( $redirection_settings['login']['type'] ) ) ? $redirection_settings['login']['main_type'] : 'fixed';
if(!$ARMemberLite->is_arm_pro_active){
	$arm_redirection_login_type_main = 'fixed';
}
$arm_redirection_login_type              = ( isset( $redirection_settings['login']['type'] ) && ! empty( $redirection_settings['login']['type'] ) ) ? $redirection_settings['login']['type'] : 'page';
$arm_redirection_signup_redirection_type = ( isset( $redirection_settings['signup']['redirect_type'] ) && ! empty( $redirection_settings['signup']['redirect_type'] ) ) ? $redirection_settings['signup']['redirect_type'] : 'common';
if(!$ARMemberLite->is_arm_pro_active){
	$arm_redirection_signup_redirection_type = 'common';
}
$arm_redirection_signup_type             = ( isset( $redirection_settings['signup']['type'] ) && ! empty( $redirection_settings['signup']['type'] ) ) ? $redirection_settings['signup']['type'] : 'page';
$arm_redirection_social_type             = ( isset( $redirection_settings['social']['type'] ) && ! empty( $redirection_settings['social']['type'] ) ) ? $redirection_settings['social']['type'] : 'page';
$arm_default_signup_url                  = ( isset( $redirection_settings['signup']['default'] ) && ! empty( $redirection_settings['signup']['default'] ) ) ? $redirection_settings['signup']['default'] : ARMLITE_HOME_URL;
$arm_all_global_settings                 = $arm_global_settings->arm_get_all_global_settings();
$page_settings                           = $arm_all_global_settings['page_settings'];

$edit_profile_page_id               = isset( $page_settings['edit_profile_page_id'] ) ? $page_settings['edit_profile_page_id'] : 0;
$arm_redirection_login_page_id      = ( isset( $redirection_settings['login']['page_id'] ) && ! empty( $redirection_settings['login']['page_id'] ) ) ? $redirection_settings['login']['page_id'] : 0;
$arm_redirection_login_url          = ( isset( $redirection_settings['login']['url'] ) && ! empty( $redirection_settings['login']['url'] ) ) ? $redirection_settings['login']['url'] : ARMLITE_HOME_URL;
$arm_redirection_login_refferel     = ( isset( $redirection_settings['login']['refferel'] ) && ! empty( $redirection_settings['login']['refferel'] ) ) ? $redirection_settings['login']['refferel'] : '';
$arm_redirection_login_conditional  = ( isset( $redirection_settings['login']['conditional_redirect'] ) && ! empty( $redirection_settings['login']['conditional_redirect'] ) ) ? $redirection_settings['login']['conditional_redirect'] : array();
$arm_redirection_signup_conditional = ( isset( $redirection_settings['signup']['conditional_redirect'] ) && ! empty( $redirection_settings['signup']['conditional_redirect'] ) ) ? $redirection_settings['signup']['conditional_redirect'] : array();
$arm_redirection_signup_refferel    = ( isset( $redirection_settings['signup']['refferel'] ) && ! empty( $redirection_settings['signup']['refferel'] ) ) ? $redirection_settings['signup']['refferel'] : ARMLITE_HOME_URL;


$arm_redirection_setup_signup_type                 = ( isset( $redirection_settings['setup_signup']['type'] ) && ! empty( $redirection_settings['setup_signup']['type'] ) ) ? $redirection_settings['setup_signup']['type'] : 'page';
$arm_redirection_setup_signup_page_id              = ( isset( $redirection_settings['setup_signup']['page_id'] ) && ! empty( $redirection_settings['setup_signup']['page_id'] ) ) ? $redirection_settings['setup_signup']['page_id'] : 0;
$arm_redirection_setup_signup_url                  = ( isset( $redirection_settings['setup_signup']['url'] ) && ! empty( $redirection_settings['setup_signup']['url'] ) ) ? $redirection_settings['setup_signup']['url'] : ARMLITE_HOME_URL;
$arm_redirection_setup_signup_conditional_redirect = ( isset( $redirection_settings['setup_signup']['conditional_redirect'] ) && ! empty( $redirection_settings['setup_signup']['conditional_redirect'] ) ) ? $redirection_settings['setup_signup']['conditional_redirect'] : array();

$arm_redirection_setup_change_type    = ( isset( $redirection_settings['setup_change']['type'] ) && ! empty( $redirection_settings['setup_change']['type'] ) ) ? $redirection_settings['setup_change']['type'] : 'page';
$arm_redirection_setup_change_page_id = ( isset( $redirection_settings['setup_change']['type'] ) && ! empty( $redirection_settings['setup_change']['page_id'] ) ) ? $redirection_settings['setup_change']['page_id'] : 0;
$arm_redirection_setup_change_url     = ( isset( $redirection_settings['setup_change']['url'] ) && ! empty( $redirection_settings['setup_change']['url'] ) ) ? $redirection_settings['setup_change']['url'] : ARMLITE_HOME_URL;
$arm_redirection_setup_change_conditional_redirect = ( isset( $redirection_settings['setup_change']['conditional_redirect'] ) && ! empty( $redirection_settings['setup_change']['conditional_redirect'] ) ) ? $redirection_settings['setup_change']['conditional_redirect'] : array();


$arm_redirection_setup_renew_type    = ( isset( $redirection_settings['setup_renew']['type'] ) && ! empty( $redirection_settings['setup_renew']['type'] ) ) ? $redirection_settings['setup_renew']['type'] : 'page';
$arm_redirection_setup_renew_page_id = ( isset( $redirection_settings['setup_renew']['type'] ) && ! empty( $redirection_settings['setup_renew']['page_id'] ) ) ? $redirection_settings['setup_renew']['page_id'] : 0;
$arm_redirection_setup_renew_url     = ( isset( $redirection_settings['setup_renew']['url'] ) && ! empty( $redirection_settings['setup_renew']['url'] ) ) ? $redirection_settings['setup_renew']['url'] : ARMLITE_HOME_URL;
$arm_redirection_setup_renew_conditional_redirect = ( isset( $redirection_settings['setup_renew']['conditional_redirect'] ) && ! empty( $redirection_settings['setup_renew']['conditional_redirect'] ) ) ? $redirection_settings['setup_renew']['conditional_redirect'] : array();

$arm_default_setup_url               = ( isset( $redirection_settings['setup']['default'] ) && ! empty( $redirection_settings['setup']['default'] ) ) ? $redirection_settings['setup']['default'] : ARMLITE_HOME_URL;

$arm_redirection_signup_page_id = ( isset( $redirection_settings['signup']['page_id'] ) && ! empty( $redirection_settings['signup']['page_id'] ) ) ? $redirection_settings['signup']['page_id'] : 0;
$arm_redirection_signup_url     = ( isset( $redirection_settings['signup']['url'] ) && ! empty( $redirection_settings['signup']['url'] ) ) ? $redirection_settings['signup']['url'] : ARMLITE_HOME_URL;

$arm_redirection_social_page_id = ( isset( $redirection_settings['social']['page_id'] ) && ! empty( $redirection_settings['social']['page_id'] ) ) ? $redirection_settings['social']['page_id'] : 0;
$arm_redirection_social_url     = ( isset( $redirection_settings['social']['url'] ) && ! empty( $redirection_settings['social']['url'] ) ) ? $redirection_settings['social']['url'] : '';

$arm_redirection_oneclick = ( isset( $redirection_settings['oneclick']['redirect_to'] ) && ! empty( $redirection_settings['oneclick']['redirect_to'] ) ) ? $redirection_settings['oneclick']['redirect_to'] : 0;

$arm_default_redirection_rules = ( isset( $redirection_settings['default_access_rules'] ) && ! empty( $redirection_settings['default_access_rules'] ) ) ? $redirection_settings['default_access_rules'] : array();

$arm_non_logged_in_type        = $arm_logged_in_type = $arm_drip_type = $arm_blocked_type = $arm_pending_type = 'home';
$arm_non_logged_in_redirect_to = $arm_logged_in_redirect_to = $arm_drip_redirect_to = $arm_blocked_redirect_to = $arm_pending_redirect_to = 0;



if ( ! empty( $arm_default_redirection_rules ) ) {
	$arm_non_logged_in_type        = ( isset( $arm_default_redirection_rules['non_logged_in']['type'] ) && ! empty( $arm_default_redirection_rules['non_logged_in']['type'] ) ) ? $arm_default_redirection_rules['non_logged_in']['type'] : 'home';
	$arm_non_logged_in_redirect_to = ( isset( $arm_default_redirection_rules['non_logged_in']['redirect_to'] ) && ! empty( $arm_default_redirection_rules['non_logged_in']['redirect_to'] ) ) ? $arm_default_redirection_rules['non_logged_in']['redirect_to'] : 0;

	$arm_logged_in_type        = ( isset( $arm_default_redirection_rules['logged_in']['type'] ) && ! empty( $arm_default_redirection_rules['logged_in']['type'] ) ) ? $arm_default_redirection_rules['logged_in']['type'] : 'home';
	$arm_logged_in_redirect_to = ( isset( $arm_default_redirection_rules['logged_in']['redirect_to'] ) && ! empty( $arm_default_redirection_rules['logged_in']['redirect_to'] ) ) ? $arm_default_redirection_rules['logged_in']['redirect_to'] : 0;



	$arm_blocked_type        = ( isset( $arm_default_redirection_rules['blocked']['type'] ) && ! empty( $arm_default_redirection_rules['blocked']['type'] ) ) ? $arm_default_redirection_rules['blocked']['type'] : 'home';
	$arm_blocked_redirect_to = ( isset( $arm_default_redirection_rules['blocked']['redirect_to'] ) && ! empty( $arm_default_redirection_rules['blocked']['redirect_to'] ) ) ? $arm_default_redirection_rules['blocked']['redirect_to'] : 0;

	$arm_pending_type        = ( isset( $arm_default_redirection_rules['pending']['type'] ) && ! empty( $arm_default_redirection_rules['pending']['type'] ) ) ? $arm_default_redirection_rules['pending']['type'] : 'home';
	$arm_pending_redirect_to = ( isset( $arm_default_redirection_rules['pending']['redirect_to'] ) && ! empty( $arm_default_redirection_rules['pending']['redirect_to'] ) ) ? $arm_default_redirection_rules['pending']['redirect_to'] : 0;

}
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name' );
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<div class="page_sub_title arm_margin_bottom_32"><?php esc_html_e( 'Page/Post Redirection Rules', 'armember-membership' ); ?></div>	
		<form  method="post" action="#" id="arm_redirection_settings" class="arm_admin_form">
			<div class="page_sub_title arm_font_size_18 arm_font_weight_500 arm_margin_top_32 arm_margin_bottom_24" id="arm_global_default_access_rules">
				<?php esc_html_e('After Login Redirection Rules', 'armember-membership'); ?>
			</div>
				<?php if(!$ARMemberLite->is_arm_pro_active)
				{?>
					<input type="hidden" name="arm_redirection_settings[login][main_type]" value="fixed" class="arm_redirection_settings_login_radio_type arm_iradio" selected="selected">
				<?php } else
				{
					echo apply_filters( 'arm_redirection_rules_sections','login_selection_section'); //phpcs:ignore
				}?>
					
				<div id="arm_redirection_login_setting_fixed" class="arm_redirection_setting_login 
					<?php
					if ( ( $arm_redirection_login_type != 'page' && $arm_redirection_login_type != 'url' && $arm_redirection_login_type != 'referral' ) || $arm_redirection_login_type_main != 'fixed' ) {
						echo 'hidden_section'; }
					?>">
					

					<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
						<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
							<div class="left_content">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e('Redirect To', 'armember-membership'); ?></div>
							</div>
						</div>
						<div class="arm_content_border"></div>

						<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
							<div class="arm_radio_btn_wrapper">
								<label class="arm_min_width_100" >
										<input type="radio" name="arm_redirection_settings[login][type]" value="page" class="arm_redirection_settings_login_radio arm_iradio" <?php checked( $arm_redirection_login_type, 'page' ); ?>><span><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></span>
								</label>
								<label  class="arm_min_width_100">
										<input type="radio" name="arm_redirection_settings[login][type]" value="url" class="arm_redirection_settings_login_radio arm_iradio" <?php checked( $arm_redirection_login_type, 'url' ); ?>><span><?php esc_html_e( 'Specific URL', 'armember-membership' ); ?></span>
								</label>
								<label  class="arm_display_flex">
										<input type="radio" name="arm_redirection_settings[login][type]" value="referral" class="arm_redirection_settings_login_radio arm_iradio" <?php checked( $arm_redirection_login_type, 'referral' ); ?>><span class="arm_padding_right_0"><?php esc_html_e( 'Referrer Page', 'armember-membership' ); ?></span>
										<span class="arm_info_text_style arm_font_size_14 arm_padding_left_5 arm_margin_0"><?php esc_html_e( '(Original page before login.)', 'armember-membership' ); ?></span>
								</label>
							</div>

							
							<div id="arm_redirection_login_settings_page"  class="arm_redirection_settings_login arm_form_field_block arm_margin_top_32 arm_redirection_settings_selectbox
								<?php
									if ( $arm_redirection_login_type != 'page' || $arm_redirection_login_type_main != 'fixed' ) {
										echo 'hidden_section'; }
									?>">
					
								<label class="arm-form-table-label"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
							
								<div class="arm_default_redirection_txt">
									<dl class="arm_width_100_pct  arm_margin_0">
									<?php
									$arm_global_settings->arm_wp_dropdown_pages(
										array(
											'selected'     => $arm_redirection_login_page_id,
											'name'         => 'arm_redirection_settings[login][page_id]',
											'id'           => 'arm_login_redirection_page',
											'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
											'option_none_value' => '',
											'class'        => 'arm_login_redirection_page',
											'required'     => true,
											'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
										)
									);
									?></dl>
								<span class="arm_redirection_login_page_selection">
									<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
								</span>							
								</div>	
							</div>	
								<div id="arm_redirection_login_settings_url" class="arm_redirection_settings_login  arm_form_field_block
									<?php
									if ( $arm_redirection_login_type != 'url' || $arm_redirection_login_type_main != 'fixed' ) {
										echo 'hidden_section'; }
									?>
									">	
										<label class="arm-form-table-label arm_margin_top_32 arm_width_100_pct"><?php esc_html_e( 'Add URL', 'armember-membership' ); ?></label>
											
										<input type="text" name="arm_redirection_settings[login][url]" value="<?php echo esc_attr($arm_redirection_login_url); ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_login_redirection_url arm_redirection_settings_selectbox arm_margin_top_12 arm_width_100_pct"><br/>
										<span class="arm_redirection_login_url_selection">
												<?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?>
											</span>
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf( esc_html__('Use %s to add current user\'s usrename in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERNAME}</strong>"); ?></span><?php //phpcs:ignore ?>
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf(esc_html__('Use %s to add current user\'s id in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERID}</strong>"); ?></span><?php //phpcs:ignore ?>
								</div>

								<div id="arm_redirection_login_settings_referral" class="arm_redirection_settings_login 
									<?php
									if ( $arm_redirection_login_type != 'referral' || $arm_redirection_login_type_main != 'fixed' ) {
										echo 'hidden_section'; }
									?>
									">	
									<div class="arm_display_flex">
										<label class="arm_margin_top_32"><?php esc_html_e( 'Default Redirect URL', 'armember-membership' ); ?></label>
										<label class="arm_margin_top_32 arm_margin_left_10"><?php esc_html_e( '(If no referrer page.)', 'armember-membership' ); ?></label>
									</div>
										<input type="text" name="arm_redirection_settings[login][refferel]" value="<?php echo esc_attr($arm_redirection_login_refferel); ?>" data-msg-required="<?php esc_attr_e( 'Please Enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_login_redirection_referel arm_redirection_settings_selectbox arm_margin_top_12 arm_width_100_pct"><br/>
										<span class="arm_redirection_login_referel_selection ">
													<?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?>
												</span>   
										<span class="arm_info_text arm_padding_0 arm_margin_top_12"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
								</div>
						</div>
					</div>
				</div>

				
				<div id="arm_redirection_login_settings_conditional_redirect" class="arm_redirection_settings_login
					<?php
					if (trim(strtolower($arm_redirection_login_type_main)) != 'conditional_redirect') {
						echo 'hidden_section';
					}?> arm_setting_main_content arm_margin_top_24 arm_padding_0"> 
				
					<div class="arm_login_conditional_redirection_main_div">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before">
						<div class="left_content">
							<span class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Add Conditional Rules', 'armember-membership' ); ?></span>
						</div>
					</div>	
					<div class="arm_content_border"></div>
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<?php
							$default_redirect_url = ( isset( $arm_redirection_login_conditional['default'] ) && ! empty( $arm_redirection_login_conditional['default'] ) ) ? $arm_redirection_login_conditional['default'] : ARMLITE_HOME_URL;
						?>
						<ul class="arm_login_conditional_redirection_ul ui-sortable arm_margin_bottom_20" >
						<?php
						if ( empty( $arm_redirection_login_conditional ) ) {
							$ckey      = 1;
							$plan_id   = 0;
							$condition = '';
							$url       = ARMLITE_HOME_URL;
							?>
						<li id="arm_login_conditional_redirection_box0" class="arm_form_main_content arm_margin_bottom_20">
							<div class="arm_login_redirection_condition_sortable_icon ui-sortable-handle armhelptip" title="<?php esc_html_e( 'Set Redirection Priority', 'armember-membership' ); ?>"></div>
							
							<a class="arm_remove_login_redirection_condition" href="javascript:void(0)" data-index="0">
								<img src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/close_btn.svg' onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/close_btn_hover.png';" onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/close_btn.svg';" /> <?php //phpcs:ignore ?>
							</a>


							<div class="arm_login_conditional_redirection_row">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'If User Has', 'armember-membership' ); ?></div>
								<div class="arm_email_setting_flex_group">
									<div id="arm_condition_redirect_login_plan_td_0">
											<label class="arm_rr_login_condition_lbl"><?php esc_html_e( 'Membership Plan', 'armember-membership' ); ?></label><br/>
											<input type='hidden' id='arm_conditional_redirect_plan_id_0 ' name="arm_redirection_settings[login][conditional_redirect][0][plan_id]" value="<?php echo intval($plan_id); ?>" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;"  value="" class="arm_autocomplete arm_padding_top_0 arm_padding_bottom_0"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_conditional_redirect_plan_id_0">
														<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'No Plan', 'armember-membership' ); ?>" data-value="-2"><?php esc_html_e( 'No Plan', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
														
														<ol class="arm_selectbox_heading"><?php esc_html_e( 'Select Plans', 'armember-membership' ); ?></ol>
														<?php
														if ( ! empty( $all_plans ) ) {
															foreach ( $all_plans as $p ) {
																$p_id = $p['arm_subscription_plan_id'];
																?>
																<li data-label="<?php echo esc_attr( stripslashes( $p['arm_subscription_plan_name'] ) ); //phpcs:ignore ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); //phpcs:ignore ?></li>
																							<?php
															}
														}
														?>
													</ul>
												</dd>
											</dl>
											<span class="arm_rsc_error arm_redirection_settings_condition_plan_id_0">
												<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
											</span>  
									</div>
									<div width="11px" class="arm_login_redirection_and_lbl arm_padding_top_36"><?php esc_html_e( '&', 'armember-membership' ); ?></div>
									<div width="290px" class="arm_login_redirection_action">
										<label class="arm_rr_login_condition_lbl"><?php esc_html_e( 'Action', 'armember-membership' ); ?></label><br/>
										<input type='hidden' id='arm_conditional_redirect_condition_0' class="arm_redirection_condition_input" name="arm_redirection_settings[login][conditional_redirect][0][condition]" value='<?php echo esc_attr($condition); ?>' data-key='0' />
										<dl class="arm_selectbox column_level_dd arm_width_170">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_padding_top_0"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_conditional_redirect_condition_0">
													<li data-label="<?php esc_attr_e( 'Any Condition', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Any Condition', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'First Time Logged In', 'armember-membership' ); ?>" data-value="first_time"><?php esc_html_e( 'First Time Logged In', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'In Trial', 'armember-membership' ); ?>" data-value="in_trial"><?php esc_html_e( 'In Trial', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'In Grace Period', 'armember-membership' ); ?>" data-value="in_grace"><?php esc_html_e( 'In Grace Period', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Failed Payment(Suspended)', 'armember-membership' ); ?>" data-value="faled_payment"><?php esc_html_e( 'Failed Payment(Suspended)', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Pending', 'armember-membership' ); ?>" data-value="pending"><?php esc_html_e( 'Pending', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Before Expiration Of', 'armember-membership' ); ?>" data-value="before_expire"><?php esc_html_e( 'Before Expiration Of', 'armember-membership' ); ?></li>
													</ul>
												</dd>
											</dl>
											
										<div id="arm_redirection_expiration_days_0" class="arm_redirection_expiration_days 
										<?php
										if ( $condition != 'before_expire' ) {
											echo 'hidden_section'; }
										?>
										">
										
											<input type='hidden' id='arm_conditional_redirect_expire_0' name="arm_redirection_settings[login][conditional_redirect][0][expire]" value='0' />
											<dl class="arm_selectbox column_level_dd arm_width_60 arm_min_width_60">
													<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
													<dd>
														<ul data-id="arm_conditional_redirect_expire_0">
															<?php
															for ( $i = 0; $i <= 30; $i++ ) {
																?>
															<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																<?php
															}
															?>
														</ul>
													</dd>
											</dl>
										<?php esc_html_e( ' Days', 'armember-membership' ); ?>
										</div>
										<span class="arm_rsc_error arm_redirection_settings_condition_redirect_0">
											<?php esc_html_e( 'Please select condition.', 'armember-membership' ); ?>
										</span> 
									</div>
								</div>
							</div>
							<div class="arm_login_conditional_redirection_row">
								<div><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></div>
								<div colspan="3">
									<label class="arm_rr_login_condition_lbl"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label><br/>
									<?php
									$arm_global_settings->arm_wp_dropdown_pages(
										array(
											'selected' => 0,
											'name' => 'arm_redirection_settings[login][conditional_redirect][0][url]',
											'id' => 'arm_login_conditional_redirection_url_0',
											'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
											'option_none_value' => 0,
											'class' => 'arm_login_conditional_redirection_page',
											'required' => true,
											'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
										),
										'arm_login_conditional_redirection_page_dd'
									);
									?>
									<span class="arm_rsc_error arm_redirection_settings_condition_url_0">
										<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
									</span>  
								</div>
							</div>
						</li>
							<?php
						} else {
							$ckey = 0;

							foreach ( $arm_redirection_login_conditional as $arm_login_conditional ) {
								if ( is_array( $arm_login_conditional ) ) {
									$plan_id         = ( isset( $arm_login_conditional['plan_id'] ) && ! empty( $arm_login_conditional['plan_id'] ) ) ? $arm_login_conditional['plan_id'] : 0;
									$condition       = ( isset( $arm_login_conditional['condition'] ) && ! empty( $arm_login_conditional['condition'] ) ) ? $arm_login_conditional['condition'] : '';
									$expiration_days = ( isset( $arm_login_conditional['expire'] ) && ! empty( $arm_login_conditional['expire'] ) ) ? $arm_login_conditional['expire'] : 0;
									$url             = ( isset( $arm_login_conditional['url'] ) && ! empty( $arm_login_conditional['url'] ) ) ? $arm_login_conditional['url'] : ARMLITE_HOME_URL;
									?>
						<li id="arm_login_conditional_redirection_box<?php echo intval($ckey); ?>" class="arm_form_main_content arm_margin_bottom_20">
							<div class="arm_login_redirection_condition_sortable_icon ui-sortable-handle armhelptip" title="<?php esc_attr_e( 'Set Redirection Priority', 'armember-membership' ); ?>"></div>
							<a class="arm_remove_login_redirection_condition" href="javascript:void(0)" data-index="<?php echo intval($ckey); ?>">
								<img src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/close_btn.svg' onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/close_btn_hover.png';" onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/close_btn.svg';" /> <?php //phpcs:ignore ?>
							</a>
						<div class="">
							<div class="arm_login_conditional_redirection_row">
								<div class="arm_form_header_label arm_font_size_16 arm_padding_top_24 arm_padding_bottom_24"><?php esc_html_e( 'If User Has', 'armember-membership' ); ?></div>
								<div class="arm_email_setting_flex_group arm_padding_left_30 arm_padding_right_30">
									<div id="arm_condition_redirect_login_plan_td_<?php echo intval($ckey); ?>" class="arm_width_50_pct arm_form_field_block">
										<label class="arm_rr_login_condition_lbl"><?php esc_html_e( 'Membership Plan', 'armember-membership' ); ?></label>
										<input type='hidden'  id='arm_conditional_redirect_plan_id_<?php echo intval($ckey); ?>' name="arm_redirection_settings[login][conditional_redirect][<?php echo intval($ckey); ?>][plan_id]" value='<?php echo intval($plan_id); ?>' />
										<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
										<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_padding_top_0 arm_padding_bottom_0"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_conditional_redirect_plan_id_<?php echo intval($ckey); ?>">
													<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'No Plan', 'armember-membership' ); ?>" data-value="-2"><?php esc_html_e( 'No Plan', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
													<ol class="arm_selectbox_heading"><?php esc_html_e( 'Choose Plan', 'armember-membership' ); ?></ol>
													<?php
													if ( ! empty( $all_plans ) ) {
														foreach ( $all_plans as $p ) {
															$p_id = $p['arm_subscription_plan_id'];
															?>
														<li data-label="<?php echo esc_html(stripslashes( $p['arm_subscription_plan_name'] )); //phpcs:ignore ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?></li>
																					<?php
														}
													}
													?>
												</ul>
											</dd>
										</dl>
										<span class="arm_rsc_error arm_redirection_settings_condition_plan_id_<?php echo intval($ckey); ?>">
											<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
										</span>  
									</div>
									<div  class="arm_login_redirection_and_lbl"><?php esc_html_e( '&', 'armember-membership' ); ?></div>
									<div class="arm_login_redirection_action arm_width_50_pct arm_form_field_block">
										<label class="arm_rr_login_condition_lbl"><?php esc_html_e( 'Action', 'armember-membership' ); ?></label>
										<?php $arm_rr_login_condidtional_grid_sec_cls = ($condition == 'before_expire') ? 'arm_grid_col_70_30' : '' ?>
										<div class="arm_rr_login_condidtional_grid_sec arm_display_grid <?php echo $arm_rr_login_condidtional_grid_sec_cls?>">
											<input type='hidden' id='arm_conditional_redirect_condition_<?php echo intval($ckey); ?>' class="arm_redirection_condition_input" name="arm_redirection_settings[login][conditional_redirect][<?php echo intval($ckey); ?>][condition]" value='<?php echo esc_html($condition); ?>' data-key="<?php echo intval($ckey); ?> " />
											<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_padding_top_0 arm_padding_bottom_0"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_conditional_redirect_condition_<?php echo intval($ckey); ?>">
														<li data-label="<?php esc_attr_e( 'Any Condition', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Any Condition', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'First Time Logged In', 'armember-membership' ); ?>" data-value="first_time"><?php esc_html_e( 'First Time Logged In', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'In Trial', 'armember-membership' ); ?>" data-value="in_trial"><?php esc_html_e( 'In Trial', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'In Grace Period', 'armember-membership' ); ?>" data-value="in_grace"><?php esc_html_e( 'In Grace Period', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'Failed Payment(Suspended)', 'armember-membership' ); ?>" data-value="failed_payment"><?php esc_html_e( 'Failed Payment(Suspended)', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'Pending', 'armember-membership' ); ?>" data-value="pending"><?php esc_html_e( 'Pending', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'Before Expiration Of', 'armember-membership' ); ?>" data-value="before_expire"><?php esc_html_e( 'Before Expiration Of', 'armember-membership' ); ?></li>
													</ul>
												</dd>
											</dl>
											<?php $arm_login_con_exp_cls = ($condition != 'before_expire') ? 'hidden_section' : '' ?>
											<div id="arm_redirection_expiration_days_<?php echo intval($ckey); ?>" class="arm_redirection_expiration_days arm_margin_top_8 <?php echo $arm_login_con_exp_cls;?>" >
												<input type='hidden' id='arm_conditional_redirect_expire_<?php echo intval($ckey); ?>' name="arm_redirection_settings[login][conditional_redirect][<?php echo intval($ckey); ?>][expire]" value='<?php echo esc_attr($expiration_days); ?>' />
												<dl class="arm_selectbox column_level_dd arm_width_90 arm_min_width_60 arm_margin_right_10">
														<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="arm_conditional_redirect_expire_<?php echo intval($ckey); ?>">
																<?php
																for ( $i = 0; $i <= 30; $i++ ) {
																	?>
																<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																	<?php
																}
																?>
															</ul>
														</dd>
												</dl>
												
											<?php esc_html_e( ' Days', 'armember-membership' ); ?>
											</div>
											<span class="arm_rsc_error arm_redirection_settings_condition_redirect_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select condition.', 'armember-membership' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="arm_login_conditional_redirection_row">
								<div class="arm_form_header_label arm_padding_top_28 arm_padding_bottom_20 arm_font_size_16"><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></div>
								<div colspan="3" class="arm_padding_left_30 arm_padding_right_30 arm_padding_bottom_24 arm_flex arm_flex_direction_col">
									<label class="arm_rr_login_condition_lbl"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
									<?php
									$arm_global_settings->arm_wp_dropdown_pages(
										array(
											'selected' => $url,
											'name' => 'arm_redirection_settings[login][conditional_redirect][' . $ckey . '][url]',
											'id' => 'arm_login_conditional_redirection_url_' . $ckey,
											'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
											'option_none_value' => 0,
											'class' => 'arm_login_redirection_page',
											'required' => true,
											'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
										),
										'arm_login_conditional_redirection_page_dd'
									);
									?>
									<span class="arm_rsc_error arm_redirection_settings_condition_url_<?php echo intval($ckey); ?>">
										<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
									</span>  
								</div>
							</div>
						</div>
						</li>
									<?php
									$ckey++;
								}
							}
						}
						?>
						
						</ul>
						<?php if($ARMemberLite->is_arm_pro_active){?>
							<div class="arm_login_conditional_redirection_link">
								<input id="arm_total_login_conditional_redirection_condition" name="arm_total_login_conditional_redirection_condition" value="<?php echo intval($ckey); ?>" type="hidden">
								<input id="arm_order_login_conditional_redirection_condition" name="arm_order_login_conditional_redirection_condition" value="<?php echo intval($ckey); ?>" type="hidden">
								<a id="arm_login_conditional_redirection_add_new_condition" class="arm_login_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php esc_html_e('Add New Condition', 'armember-membership'); ?></a>
							</div>
							
						<?php }?>
						<div class="arm_default_redirection_lbl arm_margin_top_24 arm_width_100_pct">
						<label>
							<?php esc_html_e( 'Default Redirect URL', 'armember-membership' ); ?>
						</label>  </div>
						<div class="arm_default_redirection_txt arm_default_redirection_full">
						<input type="text" name="arm_redirection_settings[login][conditional_redirect][default]" value="<?php echo esc_url($default_redirect_url); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_login_redirection_conditional_redirection arm_margin_top_12 arm_max_width_50_pct">
						<span class="arm_redirection_login_conditional_redirection_selection">
								<?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?>
							</span>
						<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Default Redirect to above url if any of above conditions do not match.', 'armember-membership' ); ?></span>
						</div>
					</div>
						</div>
				</div>
				

				<!-- After Basic SignUp Redirection Rules -->
				<div class="page_sub_title arm_font_size_18 arm_font_weight_500 arm_margin_top_48" id="arm_global_default_access_rules">
								<?php esc_html_e('After Basic SignUp Redirection Rules', 'armember-membership'); ?>
				</div>
				<?php if(!$ARMemberLite->is_arm_pro_active)
				{?>
					<input type="hidden" name="arm_redirection_settings[signup][redirect_type]" value="common" class="arm_redirection_settings_signup_redirection_type arm_iradio" selected="selected">
				<?php } else
				{
					echo apply_filters( 'arm_redirection_rules_sections','signup_selection_section'); //phpcs:ignore
				}?>
							
				<div class="arm_redirection_signup_common_settings arm_redirection_settings_signup 
					<?php
					if ( $arm_redirection_signup_redirection_type != 'common' ) {
						echo 'hidden_section'; }
					?>
					">
				
					
					<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
						<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
							<div class="left_content">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Default Redirect To', 'armember-membership' ); ?></div>
							</div>
						</div>
						<div class="arm_content_border"></div>

						<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
							<div class="arm_radio_btn_wrapper" >
								<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[signup][type]" value="page" class="arm_redirection_settings_signup_radio arm_iradio" <?php checked( $arm_redirection_signup_type, 'page' ); ?>><span><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></span>
								</label>
								<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[signup][type]" value="url" class="arm_redirection_settings_signup_radio arm_iradio" <?php checked( $arm_redirection_signup_type, 'url' ); ?> ><span><?php esc_html_e( 'Specific URL', 'armember-membership' ); ?></span>
								</label>
								<label style="min-width: 100px;" class="arm_display_flex">
									<input type="radio" name="arm_redirection_settings[signup][type]" value="referral" class="arm_redirection_settings_signup_radio arm_iradio" <?php checked( $arm_redirection_signup_type, 'referral' ); ?>>
									<span class="arm_padding_right_0"><?php esc_html_e( 'Referrer Page', 'armember-membership' ); ?></span><br><span class="arm_info_text_style arm_font_size_14 arm_padding_left_5 arm_margin_0" style="font-size: 16px;"><?php esc_html_e( '(Original page before signup.)', 'armember-membership' ); ?></span>
								</label>
							</div>

							<div id="arm_redirection_signup_settings_page" class="arm_redirection_signup_common_settings arm_redirection_settings_signup arm_signup_settings_common  arm_margin_top_32 arm_redirection_settings_selectbox 
									<?php
									if ( $arm_redirection_signup_type != 'page' || $arm_redirection_signup_redirection_type != 'common' ) {
										echo 'hidden_section'; }
									?>
									">
										<label class="arm-form-table-label arm_info_text_select_page arm_margin_0"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
										
										<?php
										$arm_global_settings->arm_wp_dropdown_pages(
											array(
												'selected'     => $arm_redirection_signup_page_id,
												'name'         => 'arm_redirection_settings[signup][page_id]',
												'id'           => 'form_action_signup_page',
												'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
												'option_none_value' => '',
												'class'        => 'form_action_signup_page',
												'required'     => true,
												'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
											)
										);
										?>
										<span class="arm_redirection_signup_page_selection"><?php esc_html_e( 'Please select Page.', 'armember-membership' ); ?></span> 
							</div>
							

							<div id="arm_redirection_signup_settings_url" class="arm_redirection_signup_common_settings arm_redirection_settings_signup arm_signup_settings_common arm_margin_top_32  arm_form_field_block
								<?php 
								if ( $arm_redirection_signup_type != 'url' || $arm_redirection_signup_redirection_type != 'common' ) {
									echo 'hidden_section'; }
								?>
								">
								<label class="arm-form-table-label arm_margin_bottom_12 arm_width_100_pct"><?php esc_html_e( 'Add URL', 'armember-membership' ); ?></label>
									<div class="arm_default_redirection_txt">
										<input type="text" name="arm_redirection_settings[signup][url]" value="<?php echo esc_attr($arm_redirection_signup_url); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_signup_redirection_url arm_redirection_settings_selectbox  arm_width_100_pct"><br/>
										<span class="arm_redirection_signup_url_selection"><?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?></span>           
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf( esc_html__('Use %s to add current user\'s usrename in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERNAME}</strong>"); ?></span><?php //phpcs:ignore ?>
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf(esc_html__('Use %s to add current user\'s id in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERID}</strong>"); ?></span><?php //phpcs:ignore ?>
									</div>
								
								</div>
								<div id="arm_redirection_signup_settings_referral" class="arm_redirection_signup_common_settings arm_redirection_settings_signup arm_signup_settings_common 
								<?php
								if ( $arm_redirection_signup_type != 'referral' || $arm_redirection_signup_redirection_type != 'common' ) {
									echo 'hidden_section'; }
								?>
								">

									<div class="arm_display_flex">
										
										<label class="arm_margin_top_32"><?php esc_html_e( 'Default Redirect URL', 'armember-membership' ); ?></label>
										<label class="arm_margin_top_32" style="margin: 0 5px;"><?php esc_html_e( '(If no referrer page.)', 'armember-membership' ); ?></label>
									</div>
									<div class="arm_default_redirection_txt ">
										<input type="text" name="arm_redirection_settings[signup][refferel]" value="<?php echo esc_url($arm_redirection_signup_refferel); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'Please Enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_signup_redirection_referel arm_redirection_settings_selectbox  arm_margin_top_12 arm_width_100_pct">
										<span class="arm_redirection_signup_referel_selection">
													<?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?>
												</span>   
										<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
									</div>	
							</div>
						</div>
					</div>
				</div>
										
				<div  class="arm_redirection_signup_formwise_settings arm_redirection_settings_signup 
					<?php
					if ( $arm_redirection_signup_redirection_type != 'formwise' ) {
						echo 'hidden_section'; }
					?>
					">
					<div class="arm_setting_main_content">  
						<div class="arm_signup_conditional_redirection_main_div">
							<ul class="arm_signup_conditional_redirection_ul arm_margin_bottom_28" >
							<?php
							if ( empty( $arm_redirection_signup_conditional ) ) {
								$ckey      = 1;
								$plan_id   = 0;
								$condition = '';
								$url       = ARMLITE_HOME_URL;
								?>
							<li id="arm_signup_conditional_redirection_box0" class="arm_setting_main_content">
						
								<div>
									<label><?php esc_html_e( 'If SignUp form is', 'armember-membership' ); ?></label>
									<div>
											<input type='hidden' id='arm_conditional_signup_redirect_form_id_0' name="arm_redirection_settings[signup][conditional_redirect][0][form_id]" class="arm_form_conditional_redirect" value="<?php echo intval($form_id); ?>" />
											<dl class="arm_selectbox column_level_dd">
												<dt class="arm_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_conditional_signup_redirect_form_id_0">
														
														

															<li data-label="<?php esc_html_e( 'Select Form', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></li>
															<li data-label="<?php esc_html_e( 'All Forms', 'armember-membership' ); ?>" data-value="-2"><?php esc_html_e( 'All Forms', 'armember-membership' ); ?></li>
												<?php if ( ! empty( $arm_forms ) ) : ?>
													<?php foreach ( $arm_forms as $_form ) : ?>
														<?php
														$formTitle = wp_strip_all_tags( stripslashes( $_form['arm_form_label'] ) ) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
														?>
														<li class="arm_shortcode_form_id_li <?php echo esc_attr($_form['arm_form_type']); ?>" data-label="<?php echo esc_attr($_form['arm_form_label']); ?>" data-value="<?php echo esc_attr($_form['arm_form_id']); ?>"><?php echo esc_html($formTitle); ?></li>
													<?php endforeach; ?>
												<?php endif; ?>
													</ul>
												</dd>
											</dl>
											<span class="arm_rsc_error arm_redirection_settings_signup_condition_form_0">
												<?php esc_html_e( 'Please select signup form.', 'armember-membership' ); ?>
											</span>  
									</div>
								</div>
								<div>
									<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
									<div colspan="3" class="arm_wdith_100_pct">
										<?php
										$arm_global_settings->arm_wp_dropdown_pages(
											array(
												'selected' => 0,
												'name' => 'arm_redirection_settings[signup][conditional_redirect][0][url]',
												'id' => 'arm_signup_conditional_redirection_url_0',
												'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
												'option_none_value' => 0,
												'class' => 'arm_member_form_input arm_signup_conditional_redirection_url',
												'required' => true,
												'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
											)
										);
										?>
										<span class="arm_rsc_error arm_redirection_settings_signup_condition_url_0">
											<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
										</span>  
									</div>
								</div>
							</li>
								<?php
							} else {
								$ckey = 0;

								foreach ( $arm_redirection_signup_conditional as $arm_signup_conditional ) {
								if ( is_array( $arm_signup_conditional ) ) {
									$form_id = ( isset( $arm_signup_conditional['form_id'] ) && ! empty( $arm_signup_conditional['form_id'] ) ) ? $arm_signup_conditional['form_id'] : 0;

									$url = ( isset( $arm_signup_conditional['url'] ) && ! empty( $arm_signup_conditional['url'] ) ) ? $arm_signup_conditional['url'] : ARMLITE_HOME_URL;
									?>
							<li id="arm_signup_conditional_redirection_box<?php echo intval($ckey); ?>" class="arm_email_setting_flex_group arm_margin_top_28">
								
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'If SignUp form is', 'armember-membership' ); ?></label>
									
										<input type='hidden' id='arm_conditional_signup_redirect_form_id_<?php echo intval($ckey); ?>' class="arm_form_conditional_redirect" name="arm_redirection_settings[signup][conditional_redirect][<?php echo intval($ckey); ?>][form_id]" value="<?php echo intval($form_id); ?>" />
										<dl class="arm_selectbox column_level_dd arm_margin_top_12 arm_width_100_pct">
											<dt class="arm_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_conditional_signup_redirect_form_id_<?php echo intval($ckey); ?>">
													
													

														<li data-label="<?php esc_attr_e( 'Select Form', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'All Forms', 'armember-membership' ); ?>" data-value="-2"><?php esc_html_e( 'All Forms', 'armember-membership' ); ?></li>
											<?php if ( ! empty( $arm_forms ) ) : ?>
												<?php foreach ( $arm_forms as $_form ) : ?>
													<?php
													$formTitle = wp_strip_all_tags( stripslashes( $_form['arm_form_label'] ) ) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
													?>
													<li class="arm_shortcode_form_id_li <?php echo esc_attr($_form['arm_form_type']); ?>" data-label="<?php echo esc_attr($formTitle); ?>" data-value="<?php echo esc_attr($_form['arm_form_id']); ?>"><?php echo esc_html($formTitle); ?></li>
												<?php endforeach; ?>
											<?php endif; ?>
												</ul>
											</dd>
										</dl>
										<span class="arm_rsc_error arm_redirection_settings_signup_condition_form_<?php echo intval($ckey); ?>">
											<?php esc_html_e( 'Please select signup form.', 'armember-membership' ); ?>
										</span>  
									</div>
									
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
										<div colspan="3" width="540px">
											<?php
											$arm_global_settings->arm_wp_dropdown_pages(
												array(
													'selected' => $url,
													'name' => 'arm_redirection_settings[signup][conditional_redirect][' . $ckey . '][url]',
													'id' => 'arm_signup_conditional_redirection_url_' . $ckey,
													'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
													'option_none_value' => 0,
													'class' => 'arm_member_form_input arm_signup_conditional_redirection_url',
													'required' => true,
													'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
												)
											);
											?>
											<span class="arm_rsc_error arm_redirection_settings_signup_condition_url_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
											</span>  
										</div>
									</div>
									<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
										<a id="arm_signup_conditional_redirection_add_new_condition" class="arm_signup_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon" href="javascript:void(0)" data-field_index="2"></a>
										<a class="arm_remove_signup_redirection_condition arm_plan_cycle_minus_icon arm_margin_0" href="javascript:void(0)" data-index="0"></a> <?php //phpcs:ignore ?>
									</div>
							</li>
								<?php
								$ckey++;
									}
								}
							}
							?>
								
							</ul>
							<?php if($ARMemberLite->is_arm_pro_active){?>
								<div class="arm_signup_conditional_redirection_link">
									<input id="arm_total_signup_conditional_redirection_condition" name="arm_total_signup_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
									<input id="arm_order_signup_conditional_redirection_condition" name="arm_order_signup_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
								</div>
								
							<?php }?>
							<div class="arm_default_redirection_lbl arm_padding_0 arm_width_100_pct">
								<label><?php esc_html_e( 'Default Redirect URL', 'armember-membership' ); ?></label>   
							</div>
							<div class="arm_default_redirection_txt arm_default_redirection_full">
								<input type="text" name="arm_redirection_settings[signup][default]" value="<?php echo esc_url($arm_default_signup_url); ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_signup_redirection_conditional_redirection arm_margin_top_12 arm_max_width_50_pct">
								<span class="arm_redirection_signup_conditional_redirection_selection">
									<?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?>
								</span>   
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Default Redirect to above url if any of above conditions do not match.', 'armember-membership' ); ?></span>
							</div>
						</div>
					</div>
				</div>
							
					<?php if($ARMemberLite->is_arm_pro_active)
					{
						echo apply_filters( 'arm_redirection_rules_sections','after_edit_profile_selection_section'); //phpcs:ignore
					}?>

				<!--  -->

				<div class="page_sub_title arm_font_size_18 arm_font_weight_500 arm_margin_top_48" id="arm_global_default_access_rules">
					<?php esc_html_e( 'After Membership/Plan obtaining Redirection Rules', 'armember-membership' ); ?>
				</div>
				<div class="page_sub_title"></div>
				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Redirection after Membership SignUp', 'armember-membership' ); ?></div>
						</div>
					</div>
					<div class="arm_content_border"></div>
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<div class="arm_radio_btn_wrapper">
							<label class="arm_min_width_100">
								<input type="radio" name="arm_redirection_settings[setup_signup][type]" value="page" class="arm_redirection_settings_setup_signup_radio arm_iradio" <?php checked( $arm_redirection_setup_signup_type, 'page' ); ?>><span><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></span>
							</label>
							<label class="arm_min_width_100">
								<input type="radio" name="arm_redirection_settings[setup_signup][type]" value="url" class="arm_redirection_settings_setup_signup_radio arm_iradio" <?php checked( $arm_redirection_setup_signup_type, 'url' ); ?> ><span><?php esc_html_e( 'Specific URL', 'armember-membership' ); ?></span>
							</label>
								<?php if($ARMemberLite->is_arm_pro_active){?>
							<label class="arm_min_width_100">
								<input type="radio" name="arm_redirection_settings[setup_signup][type]" value="conditional_redirect" class="arm_redirection_settings_setup_signup_radio arm_iradio" <?php checked($arm_redirection_setup_signup_type, 'conditional_redirect');?> ><span><?php esc_html_e('Conditional Redirect','armember-membership');?></span>
							</label>
								<?php }?>
						</div>
	
						<div class="arm_form_field_block arm_margin_top_32">
				
							<div id="arm_redirection_settings_setup_signup_page" class="arm_redirection_settings_setup_signup arm_redirection_settings_selectbox 
								<?php
								if ( $arm_redirection_setup_signup_type != 'page' ) {
									echo 'hidden_section'; }
									?>
								">
								<label class="arm-form-table-label"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
									<div class="arm_default_redirection_txt">
										<?php
										$arm_global_settings->arm_wp_dropdown_pages(
											array(
												'selected'     => $arm_redirection_setup_signup_page_id,
												'name'         => 'arm_redirection_settings[setup_signup][page_id]',
												'id'           => 'arm_form_action_setup_signup_page',
												'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
												'option_none_value' => '',
												'class'        => 'form_action_setup_signup_page',
												'required'     => true,
												'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
											)
										);
										?>
										<span class="arm_form_action_setup_signup_page_require">
											<?php esc_html_e( 'Please Select Page.', 'armember-membership' ); ?>        
										</span>
									</div>
							</div>					
							<div id="arm_redirection_settings_setup_signup_url" class="arm_redirection_settings_setup_signup   arm_form_field_block
								<?php
								if ( $arm_redirection_setup_signup_type != 'url' ) {
									echo 'hidden_section'; }
								?>
								">
								<label class="arm-form-table-label arm_margin_bottom_12 arm_width_100_pct"><?php esc_html_e( 'Add URL', 'armember-membership' ); ?></label>
								
								<input type="text" name="arm_redirection_settings[setup_signup][url]" value="<?php echo esc_attr($arm_redirection_setup_signup_url); ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_setup_signup_redirection_url arm_redirection_settings_selectbox arm_min_width_0 arm_width_100_pct" id="arm_setup_signup_redirection_url"><br/>
								<span class="arm_setup_signup_redirection_url_require "><?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?></span>     
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf( esc_html__('Use %s to add current user\'s usrename in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERNAME}</strong>"); ?></span><?php //phpcs:ignore ?>
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf(esc_html__('Use %s to add current user\'s id in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERID}</strong>"); ?></span><?php //phpcs:ignore ?>					
							</div>
						</div>
	
						<div id="arm_redirection_settings_setup_signup_conditional_redirect" class="arm_redirection_settings_setup_signup 
						<?php
							if ($arm_redirection_setup_signup_type != 'conditional_redirect' ) {
								echo 'hidden_section'; }
						?>">
							<div class="arm_setup_signup_conditional_redirection_main_div">
								<ul class="arm_setup_signup_conditional_redirection_ul " >
								<?php
								if ( empty( $arm_redirection_setup_signup_conditional_redirect ) ) {
									$ckey    = 1;
									$setup_signup_plan_id = 0;
		
									$url = ARMLITE_HOME_URL;
									?>
								<li id="arm_setup_signup_conditional_redirection_box0" class="arm_email_setting_flex_group  arm_margin_bottom_0">
								
									<div class="arm_form_field_block"> 
										<label><?php esc_html_e( 'If User selected plan is', 'armember-membership' ); ?></label>
										<div>
												<input type='hidden' id='arm_conditional_redirect_setup_signup_plan_0' name="arm_redirection_settings[setup_signup][conditional_redirect][0][plan_id]" value="<?php echo intval($setup_signup_plan_id); ?>" />
												<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
													<dt class="arm_setup_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
													<dd>
														<ul data-id="arm_conditional_redirect_setup_signup_plan_0">
															<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
															<li data-label="<?php esc_attr_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
															<?php
															if ( ! empty( $all_plans ) ) {
																foreach ( $all_plans as $p ) {
																	$p_id = $p['arm_subscription_plan_id'];
																	?>
																	<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name'] )); ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?></li>
																								<?php
																}
															}
															?>
														</ul>
													</dd>
												</dl>
												<span class="arm_rsc_error arm_conditional_redirect_setup_signup_plan_require_0">
													<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
												</span> 
										</div>
										
									</div>
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
										<div colspan="3">
											<?php
											$arm_global_settings->arm_wp_dropdown_pages(
												array(
													'selected' => 0,
													'name' => 'arm_redirection_settings[setup_signup][conditional_redirect][0][url]',
													'id' => 'arm_setup_signup_conditional_redirection_url_0',
													'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
													'option_none_value' => 0,
													'class' => 'arm_member_form_input arm_setup_signup_conditional_redirection_url arm_width_100_pct',
													'required' => true,
													'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
												)
											);
											?>
											<span class="arm_rsc_error arm_setup_signup_conditional_redirection_url_require_0">
												<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
											</span>  
										</div>
									</div>
									<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
										<div id="arm_setup_signup_conditional_redirection_add_new_condition" class="arm_setup_signup_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon " href="javascript:void(0)" data-field_index="2"></div>
										<div class="arm_remove_setup_signup_redirection_condition arm_plan_cycle_minus_icon arm_margin_0" href="javascript:void(0)" data-index="0"></div> <?php //phpcs:ignore ?>
									</div>
	
								</li>
									<?php
								} else {
									$ckey = 0;
		
									foreach ( $arm_redirection_setup_signup_conditional_redirect as $arm_setup_signup_conditional ) {
										if ( is_array( $arm_setup_signup_conditional ) ) {
											$setup_signup_plan_id = ( isset( $arm_setup_signup_conditional['plan_id'] ) && ! empty( $arm_setup_signup_conditional['plan_id'] ) ) ? $arm_setup_signup_conditional['plan_id'] : 0;
		
											$url = ( isset( $arm_setup_signup_conditional['url'] ) && ! empty( $arm_setup_signup_conditional['url'] ) ) ? $arm_setup_signup_conditional['url'] : ARMLITE_HOME_URL;
											?>
								<li id="arm_setup_signup_conditional_redirection_box<?php echo intval($ckey); ?>" class="arm_email_setting_flex_group">
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'If user selected plan is', 'armember-membership' ); ?></label>
										<div>
											<input type='hidden' id='arm_conditional_redirect_setup_signup_plan_<?php echo intval($ckey); ?>' name="arm_redirection_settings[setup_signup][conditional_redirect][<?php echo intval($ckey); ?>][plan_id]" value="<?php echo intval($setup_signup_plan_id); ?>" />
											<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_max_width_100_pct arm_margin_top_12">
												<dt class="arm_setup_signup_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_conditional_redirect_setup_signup_plan_<?php echo intval($ckey); ?>">
														<li data-label="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_html_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
														<?php
														if ( ! empty( $all_plans ) ) {
															foreach ( $all_plans as $p ) {
																$p_id = $p['arm_subscription_plan_id'];
																?>
																<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name'] )); ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?></li>
																<?php
															}
														}
														?>
													</ul>
												</dd>
											</dl>
											<span class="arm_rsc_error arm_conditional_redirect_setup_signup_plan_require_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
											</span>
										</div>
									</div>
	
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
										<div colspan="3">
											<?php
											$arm_global_settings->arm_wp_dropdown_pages(
												array(
													'selected' => $url,
													'name' => 'arm_redirection_settings[setup_signup][conditional_redirect][' . $ckey . '][url]',
													'id' => 'arm_setup_signup_conditional_redirection_url_' . $ckey,
													'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
													'option_none_value' => 0,
													'class' => 'arm_member_form_input arm_setup_signup_conditional_redirection_url arm_width_100_pct',
													'required' => true,
													'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
												)
											);
											?>
											<span class="arm_rsc_error arm_setup_signup_conditional_redirection_url_require_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
											</span>
										</div>
									</div>
	
									<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
										<div id="arm_setup_signup_conditional_redirection_add_new_condition" class="arm_setup_signup_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon" href="javascript:void(0)" data-field_index="2"></div>
										<div class="arm_plan_cycle_minus_icon arm_margin_right_0" href="javascript:void(0)" data-index="<?php echo intval($ckey); ?>"></div>
									</div>
								</li>
	
								<?php
								$ckey++;
										}
									}
								}
								?>
								</ul>
								<div class="arm_signup_conditional_redirection_link">
									<input id="arm_total_setup_signup_conditional_redirection_condition" name="arm_total_setup_signup_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
									<input id="arm_order_setup_signup_conditional_redirection_condition" name="arm_order_setup_signup_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
									<!-- <a id="arm_setup_signup_conditional_redirection_add_new_condition" class="arm_setup_signup_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php esc_html_e('Add New Condition', 'armember-membership'); ?></a> -->
								</div>
								
							</div>
						</div>
					</div>
				</div>			
				

				<!-- Redirection upon Add/Change Membership -->	

				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Redirection upon Add/Change Membership', 'armember-membership' ); ?></div>
						</div>
					</div>
					<div class="arm_content_border"></div>
			
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<div class="arm_radio_btn_wrapper">
							<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[setup_change][type]" value="page" class="arm_redirection_settings_setup_change_radio arm_iradio" <?php checked( $arm_redirection_setup_change_type, 'page' ); ?>><span><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></span>
							</label>
							<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[setup_change][type]" value="url" class="arm_redirection_settings_setup_change_radio arm_iradio" <?php checked( $arm_redirection_setup_change_type, 'url' ); ?> ><span><?php esc_html_e( 'Specific URL', 'armember-membership' ); ?></span>
							</label>
							<?php if($ARMemberLite->is_arm_pro_active){?>
								<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[setup_change][type]" value="conditional_redirect" class="arm_redirection_settings_setup_change_radio arm_iradio" <?php checked($arm_redirection_setup_change_type, 'conditional_redirect');?> ><span><?php esc_html_e('Conditional Redirect','armember-membership');?></span>
							</label>
							<?php }?>		
						</div>

						<div id="arm_redirection_settings_setup_change_page" class="arm_redirection_settings_setup_change  arm_margin_top_32  arm_redirection_settings_selectbox
								<?php
									if ( $arm_redirection_setup_change_type != 'page' ) {
										echo 'hidden_section'; }
									?>
								">
								<label class="arm-form-table-label"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
									<div class="arm_default_redirection_txt">
										<?php
										$arm_global_settings->arm_wp_dropdown_pages(
											array(
												'selected'     => $arm_redirection_setup_change_page_id,
												'name'         => 'arm_redirection_settings[setup_change][page_id]',
												'id'           => 'arm_form_action_setup_change_page',
												'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
												'option_none_value' => '',
												'class'        => 'form_action_setup_change_page',
												'required'     => true,
												'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
											)
										);
										?>
										<span class="arm_form_action_setup_change_page_require">
											<?php esc_html_e( 'Please Select Page.', 'armember-membership' ); ?>        
										</span>
									</div>
						</div>

						<div id="arm_redirection_settings_setup_change_url" class="arm_redirection_settings_setup_change arm_margin_top_32 arm_form_field_block
							<?php
							if ( $arm_redirection_setup_change_type != 'url' ) {
								echo 'hidden_section'; }
							?>
							">
							<label class="arm-form-table-label arm_margin_bottom_12 arm_width_100_pct"><?php esc_html_e( 'Add URL', 'armember-membership' ); ?></label>
							<input type="text" name="arm_redirection_settings[setup_change][url]" value="<?php echo esc_attr($arm_redirection_setup_change_url); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_setup_change_redirection_url arm_redirection_settings_selectbox  arm_width_100_pct" id="arm_setup_change_redirection_url"><br/>
							<span class="arm_form_action_setup_change_url_require"><?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?></span>
							<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
							<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf( esc_html__('Use %s to add current user\'s usrename in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERNAME}</strong>"); ?></span><?php //phpcs:ignore ?>
							<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf(esc_html__('Use %s to add current user\'s id in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERID}</strong>"); ?></span><?php //phpcs:ignore ?>	  
						</div>


						<div  id="arm_redirection_settings_setup_change_conditional_redirect" class="arm_redirection_settings_setup_change  arm_margin_top_32
							<?php
							if ( $arm_redirection_setup_change_type != 'conditional_redirect' ) {
								echo 'hidden_section'; }
							?>
							">
							<div class="arm_setup_change_conditional_redirection_main_div">
								<ul class="arm_setup_change_conditional_redirection_ul">
								<?php
								if ( empty( $arm_redirection_setup_change_conditional_redirect ) ) {
									$ckey    = 1;
									$setup_change_plan_id = 0;
			
									$url = ARMLITE_HOME_URL;
									?>
								<li id="arm_setup_change_conditional_redirection_box<?php echo intval($ckey); ?>" class="arm_email_setting_flex_group arm_margin_bottom_0">
			
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'If User selected plan is', 'armember-membership' ); ?></label>
										<div>
											<input type='hidden' id='arm_conditional_redirect_setup_change_plan_<?php echo intval($ckey); ?>' name="arm_redirection_settings[setup_change][conditional_redirect][<?php echo intval($ckey); ?>][plan_id]" value="<?php echo intval($setup_change_plan_id); ?>" />
											<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
												<dt class="arm_setup_change_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_conditional_redirect_setup_change_plan_<?php echo intval($ckey); ?>">
														<li data-label="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_html_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
														<?php
														if ( ! empty( $all_plans ) ) {
															foreach ( $all_plans as $p ) {
																$p_id = $p['arm_subscription_plan_id'];
																?>
																<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name'] )); ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?></li>
																<?php
															}
														}
														?>
													</ul>
												</dd>
											</dl>
											<span class="arm_rsc_error arm_conditional_redirect_setup_change_plan_require_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
											</span> 
										</div>
									</div>
				
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
										<div>
											<?php
											$arm_global_settings->arm_wp_dropdown_pages(
												array(
													'selected' => 0,
													'name' => 'arm_redirection_settings[setup_change][conditional_redirect][' . $ckey . '][url]',
													'id' => 'arm_setup_change_conditional_redirection_url_' . $ckey,
													'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
													'option_none_value' => 0,
													'class' => 'arm_member_form_input arm_setup_change_conditional_redirection_url arm_width_100_pct',
													'required' => true,
													'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
												)
											);
											?>
											<span class="arm_rsc_error arm_setup_change_conditional_redirection_url_require_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
											</span>  
										</div>
									</div>
				
									<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
										<div id="arm_setup_change_conditional_redirection_add_new_condition" class="arm_setup_change_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon" href="javascript:void(0)" data-field_index="<?php echo intval($ckey); ?>"></div>
										<div class="arm_remove_setup_change_redirection_condition arm_plan_cycle_minus_icon arm_margin_0" href="javascript:void(0)" data-index="<?php echo intval($ckey); ?>"></div>
									</div>
			
								</li>
			
									<?php
								} else {
									$ckey = 0;
			
									foreach ( $arm_redirection_setup_change_conditional_redirect as $arm_setup_change_conditional ) {
										if ( is_array( $arm_setup_change_conditional ) ) {
											$setup_change_plan_id = ( isset( $arm_setup_change_conditional['plan_id'] ) && ! empty( $arm_setup_change_conditional['plan_id'] ) ) ? $arm_setup_change_conditional['plan_id'] : 0;
			
											$url = ( isset( $arm_setup_change_conditional['url'] ) && ! empty( $arm_setup_change_conditional['url'] ) ) ? $arm_setup_change_conditional['url'] : ARMLITE_HOME_URL;
											?>
								<li id="arm_setup_change_conditional_redirection_box<?php echo intval($ckey); ?>" class="arm_email_setting_flex_group">
									
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'If user selected plan is', 'armember-membership' ); ?></label>
										<div>
											<input type='hidden' id='arm_conditional_redirect_setup_change_plan_<?php echo intval($ckey); ?>' 
												name="arm_redirection_settings[setup_change][conditional_redirect][<?php echo intval($ckey); ?>][plan_id]" 
												value="<?php echo intval($setup_change_plan_id); ?>" />
											<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
												<dt class="arm_setup_change_redirection_dt">
													<span></span>
													<input type="text" style="display:none;" value="" class="arm_autocomplete" />
													<i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="arm_conditional_redirect_setup_change_plan_<?php echo intval($ckey); ?>">
														<li data-label="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_html_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
														<?php
														if ( ! empty( $all_plans ) ) {
															foreach ( $all_plans as $p ) {
																$p_id = $p['arm_subscription_plan_id'];
																?>
																<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name'] )); ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?></li>
																<?php
															}
														}
														?>
													</ul>
												</dd>
											</dl>
											<span class="arm_rsc_error arm_conditional_redirect_setup_change_plan_require_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
											</span> 
										</div>
									</div>
			
									<div class="arm_form_field_block">
										<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
										<div colspan="3">
											<?php
											$arm_global_settings->arm_wp_dropdown_pages(
												array(
													'selected' => $url,
													'name' => 'arm_redirection_settings[setup_change][conditional_redirect][' . $ckey . '][url]',
													'id' => 'arm_setup_change_conditional_redirection_url_' . $ckey,
													'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
													'option_none_value' => 0,
													'class' => 'arm_member_form_input arm_setup_change_conditional_redirection_url arm_width_100_pct',
													'required' => true,
													'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
												)
											);
											?>
											<span class="arm_rsc_error arm_setup_change_conditional_redirection_url_require_<?php echo intval($ckey); ?>">
												<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
											</span>  
										</div>
									</div>
			
									<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
										<div id="arm_setup_change_conditional_redirection_add_new_condition" class="arm_setup_change_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon" href="javascript:void(0)" data-field_index="<?php echo intval($ckey); ?>"></div>
										<div class="arm_plan_cycle_minus_icon arm_margin_right_0 arm_remove_setup_change_redirection_condition arm_margin_0" href="javascript:void(0)" data-index="<?php echo intval($ckey); ?>"></div> 
									</div>
		
								</li>
			
								<?php
								$ckey++;
										}
									}
								}
								?>
								</ul>
								<div class="arm_setup_change_conditional_redirection_link">
									<input id="arm_total_setup_change_conditional_redirection_condition" name="arm_total_setup_change_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
									<input id="arm_order_setup_change_conditional_redirection_condition" name="arm_order_setup_change_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
									<!-- <a id="arm_setup_change_conditional_redirection_add_new_condition" class="arm_setup_change_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php esc_html_e('Add New Condition', 'armember-membership'); ?></a> -->
								</div>
								
							</div>
						</div>
					</div>
				</div>

				<!-- Redirection upon Membership Renewal -->	

				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_32" id="changeCurrency">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Redirection upon Membership Renewal', 'armember-membership' ); ?></div>
						</div>
					</div>
					<div class="arm_content_border"></div>
			
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<div class="arm_radio_btn_wrapper">
							<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[setup_renew][type]" value="page" class="arm_redirection_settings_setup_renew_radio arm_iradio" <?php checked( $arm_redirection_setup_renew_type, 'page' ); ?>><span><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></span>
							</label>
							<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[setup_renew][type]" value="url" class="arm_redirection_settings_setup_renew_radio arm_iradio" <?php checked( $arm_redirection_setup_renew_type, 'url' ); ?> ><span><?php esc_html_e( 'Specific URL', 'armember-membership' ); ?></span>
							</label>
							<?php if($ARMemberLite->is_arm_pro_active){?>
								<label class="arm_min_width_100">
									<input type="radio" name="arm_redirection_settings[setup_renew][type]" value="conditional_redirect" class="arm_redirection_settings_setup_renew_radio arm_iradio" <?php checked($arm_redirection_setup_renew_type, 'conditional_redirect');?> ><span><?php esc_html_e('Conditional Redirect','armember-membership');?></span>
								</label>
							<?php }?>
											
						</div>

						<div id="arm_redirection_settings_setup_renew_page" class="arm_redirection_settings_setup_renew arm_margin_top_32 arm_redirection_settings_selectbox
							<?php
							if ( $arm_redirection_setup_renew_type != 'page' ) {
								echo 'hidden_section'; }
							?>
							">

							<label class="arm-form-table-label "><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
			
							<div class="arm_default_redirection_txt">
								<?php
								$arm_global_settings->arm_wp_dropdown_pages(
									array(
										'selected'     => $arm_redirection_setup_renew_page_id,
										'name'         => 'arm_redirection_settings[setup_renew][page_id]',
										'id'           => 'arm_form_action_setup_renew_page',
										'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
										'option_none_value' => '',
										'class'        => 'form_action_setup_renew_page',
										'required'     => true,
										'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
									)
								);


								?>
								<span class="arm_form_action_setup_renew_page_require">
									<?php esc_html_e( 'Please Select Page.', 'armember-membership' ); ?>        
								</span>
							</div>
						</div>

						<div id="arm_redirection_settings_setup_renew_url" class="arm_redirection_settings_setup_renew 
						arm_margin_top_32 arm_form_field_block
							<?php
							if ( $arm_redirection_setup_renew_type != 'url' ) {
								echo 'hidden_section'; }
							?>
							">
							<label class="arm-form-table-label arm_margin_bottom_12 arm_width_100_pct"><?php esc_html_e( 'Add URL', 'armember-membership' ); ?></label>
							<div class="arm_default_redirection_txt">
								<input type="text" name="arm_redirection_settings[setup_renew][url]" value="<?php echo esc_attr($arm_redirection_setup_renew_url); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_setup_renew_redirection_url arm_redirection_settings_selectbox arm_width_100_pct" id="arm_setup_renew_redirection_url">
								<span class="arm_setup_renew_redirection_url_require"><?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?></span>
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php esc_html_e( 'Enter URL with http:// or https://.', 'armember-membership' ); ?></span>
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf( esc_html__('Use %s to add current user\'s usrename in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERNAME}</strong>"); ?></span><?php //phpcs:ignore ?>
								<span class="arm_info_text_style arm_padding_0 arm_margin_bottom_0 arm_font_size_14"><?php echo sprintf(esc_html__('Use %s to add current user\'s id in url.', 'armember-membership'),"<strong>{ARMCURRENTUSERID}</strong>"); ?></span><?php //phpcs:ignore ?>
							</div>	  
						</div>
						
						<div  id="arm_redirection_settings_setup_renew_conditional_redirect" class="arm_redirection_settings_setup_renew arm_margin_top_32 
							<?php
							if ( $arm_redirection_setup_renew_type != 'conditional_redirect' ) {
								echo 'hidden_section'; }
							?>
							">
							<div>  
								<div class="arm_setup_renew_conditional_redirection_main_div">
									<ul class="arm_setup_renew_conditional_redirection_ul" >
									<?php
									if ( empty( $arm_redirection_setup_renew_conditional_redirect ) ) {
										$ckey    = 1;
										$setup_renew_plan_id = 0;
			
										$url = ARMLITE_HOME_URL;
										?>
									<li id="arm_setup_renew_conditional_redirection_box0" class="arm_email_setting_flex_group arm_margin_bottom_0">
			
										<div class="arm_form_field_block">
											<label><?php esc_html_e( 'If User selected plan is', 'armember-membership' ); ?></label>
											<div class="arm_form_field_block">
												<input type='hidden' id='arm_conditional_redirect_setup_renew_plan_0' 
													name="arm_redirection_settings[setup_renew][conditional_redirect][0][plan_id]" 
													value="<?php echo intval($setup_renew_plan_id); ?>" />
												<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
													<dt class="arm_setup_renew_redirection_dt">
														<span></span>
														<input type="text" style="display:none;" value="" class="arm_autocomplete" />
														<i class="armfa armfa-caret-down armfa-lg"></i>
													</dt>
													<dd>
														<ul data-id="arm_conditional_redirect_setup_renew_plan_0">
															<li data-label="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
															<li data-label="<?php esc_html_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
															<?php
															if ( ! empty( $all_plans ) ) {
																foreach ( $all_plans as $p ) {
																	$p_id = $p['arm_subscription_plan_id'];
																	?>
																	<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name'] )); ?>" 
																		data-value="<?php echo intval($p_id); ?>">
																		<?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?>
																	</li>
																	<?php
																}
															}
															?>
														</ul>
													</dd>
												</dl>
												<span class="arm_rsc_error arm_conditional_redirect_setup_renew_plan_require_0">
													<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
												</span> 
											</div>
										</div>
		
										<div class="arm_form_field_block">
											<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
											<div colspan="3">
												<?php
												$arm_global_settings->arm_wp_dropdown_pages(
													array(
														'selected' => 0,
														'name' => 'arm_redirection_settings[setup_renew][conditional_redirect][0][url]',
														'id' => 'arm_setup_renew_conditional_redirection_url_0',
														'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
														'option_none_value' => 0,
														'class' => 'arm_member_form_input arm_setup_renew_conditional_redirection_url arm_width_100_pct',
														'required' => true,
														'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
													)
												);
												?>
												<span class="arm_rsc_error arm_setup_renew_conditional_redirection_url_require_0">
													<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
												</span>  
											</div>
										</div>
		
										<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
											<div id="arm_setup_renew_conditional_redirection_add_new_condition" 
												class="arm_setup_renew_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon" 
												href="javascript:void(0)" data-field_index="1"></div>
											<div class="arm_remove_setup_renew_redirection_condition arm_plan_cycle_minus_icon arm_margin_0" href="javascript:void(0)" data-index="0"></div> 
										</div>
									</li>
		
										<?php
									} else {
										$ckey = 0;
			
										foreach ( $arm_redirection_setup_renew_conditional_redirect as $arm_setup_renew_conditional ) {
											if ( is_array( $arm_setup_renew_conditional ) ) {
												$setup_renew_plan_id = ( isset( $arm_setup_renew_conditional['plan_id'] ) && ! empty( $arm_setup_renew_conditional['plan_id'] ) ) ? $arm_setup_renew_conditional['plan_id'] : 0;
			
												$url = ( isset( $arm_setup_renew_conditional['url'] ) && ! empty( $arm_setup_renew_conditional['url'] ) ) ? $arm_setup_renew_conditional['url'] : ARMLITE_HOME_URL;
												?>
									<li id="arm_setup_renew_conditional_redirection_box<?php echo intval($ckey); ?>" class="arm_email_setting_flex_group arm_margin_top_24">
										
										<div class="arm_form_field_block">
											<label><?php esc_html_e( 'If user selected plan is', 'armember-membership' ); ?></label>
											<div>
													<input type='hidden' id='arm_conditional_redirect_setup_renew_plan_<?php echo intval($ckey); ?>' name="arm_redirection_settings[setup_renew][conditional_redirect][<?php echo intval($ckey); ?>][plan_id]" value="<?php echo intval($setup_renew_plan_id); ?>" />
													<dl class="arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12">
														<dt class="arm_setup_renew_redirection_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="arm_conditional_redirect_setup_renew_plan_<?php echo intval($ckey); ?>">
																<li data-label="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
																<li data-label="<?php esc_html_e( 'Any Plan', 'armember-membership' ); ?>" data-value="-3"><?php esc_html_e( 'Any Plan', 'armember-membership' ); ?></li>
			
																	<?php
			
			
																	if ( ! empty( $all_plans ) ) {
																		foreach ( $all_plans as $p ) {
																			$p_id = $p['arm_subscription_plan_id'];
																			?>
																		<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name'] )); ?>" data-value="<?php echo intval($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); ?></li>
																									<?php
																		}
																	}
																	?>
															</ul>
														</dd>
													</dl>
													<span class="arm_rsc_error arm_conditional_redirect_setup_renew_plan_require_<?php echo intval($ckey); ?>">
														<?php esc_html_e( 'Please select plan.', 'armember-membership' ); ?>
													</span> 
											</div>
											
										</div>
										<div class="arm_form_field_block">
											<label><?php esc_html_e( 'Then Redirect To', 'armember-membership' ); ?></label>
											<div colspan="3" >
												<?php
												$arm_global_settings->arm_wp_dropdown_pages(
													array(
														'selected' => $url,
														'name' => 'arm_redirection_settings[setup_renew][conditional_redirect][' . $ckey . '][url]',
														'id' => 'arm_setup_renew_conditional_redirection_url_' . $ckey,
														'show_option_none' => esc_html__( 'Select Page', 'armember-membership' ),
														'option_none_value' => 0,
														'class' => 'arm_member_form_input arm_setup_renew_conditional_redirection_url',
														'required' => true,
														'required_msg' => esc_html__( 'Please select redirection page.', 'armember-membership' ),
													)
												);
												?>
												<span class="arm_rsc_error arm_setup_renew_conditional_redirection_url_require_<?php echo intval($ckey); ?>">
													<?php esc_html_e( 'Please select a page.', 'armember-membership' ); ?>
												</span>  
											</div>
										</div>
										<div class="arm_plan_payment_cycle_action_buttons arm_margin_top_35">
										<a id="arm_setup_renew_conditional_redirection_add_new_condition" class="arm_setup_renew_conditional_redirection_add_new_condition arm_plan_cycle_plus_icon arm_margin_0 arm_margin_right_10" href="javascript:void(0)" data-field_index="2"></a>
										<a class="arm_remove_setup_renew_redirection_condition arm_plan_cycle_minus_icon arm_margin_0" href="javascript:void(0)" data-index="<?php echo intval($ckey); ?>"></a> <?php //phpcs:ignore ?>
										</div>
									</li>
										<?php
										$ckey++;
											}
										}
									}
									?>
									</ul>
									<div class="arm_setup_renew_conditional_redirection_link">
										<input id="arm_total_setup_renew_conditional_redirection_condition" name="arm_total_setup_renew_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
										<input id="arm_order_setup_renew_conditional_redirection_condition" name="arm_order_setup_renew_conditional_redirection_condition" value="<?php echo esc_attr($ckey); ?>" type="hidden">
										<!-- <a id="arm_setup_renew_conditional_redirection_add_new_condition" class="arm_setup_renew_conditional_redirection_add_new_condition" href="javascript:void(0)" data-field_index="2">+ <?php esc_html_e('Add New Condition', 'armember-membership'); ?></a> -->
									</div>
									
								</div>
							</div>
						</div>
					</div>
				</div>	

				<!-- Default Redirect URL -->	

				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Default Redirect URL', 'armember-membership' ); ?></div>
						</div>
					</div>
					<div class="arm_content_border"></div>
			
					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
						<label class="arm-form-table-label"><?php esc_html_e( 'Default Redirect to above url if any of above conditions do not match.', 'armember-membership' ); ?></label>
						<input type="text" name="arm_redirection_settings[setup][default]" value="<?php echo esc_url($arm_default_setup_url); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'Please enter URL.', 'armember-membership' ); ?>" class="arm_member_form_input arm_setup_signup_default_redirection arm_redirection_settings_selectbox  arm_width_100_pct arm_margin_top_12" id="arm_setup_signup_default_redirection">
							<span class="arm_redirection_plan_signup_url_selection_require">
								<?php esc_html_e( 'Please enter URL.', 'armember-membership' ); ?>                                      
							</span>
					</div>
				</div>	

					<?php
					if($ARMemberLite->is_arm_pro_active){
						echo apply_filters( 'arm_redirection_rules_sections','after_paid_post_selection_section'); //phpcs:ignore
					}
					
					if($ARMemberLite->is_arm_pro_active){
						echo apply_filters( 'arm_redirection_rules_sections','after_social_login_selection_section'); //phpcs:ignore
					}
					
					?>

					<!-- For non logged in users -->	

				<div class="page_sub_title arm_font_size_18 arm_font_weight_500 arm_margin_top_48" id="arm_global_default_access_rules">
							<?php esc_html_e( 'Redirection Rules upon Accessing Restricted Post/Page', 'armember-membership' ); ?>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'Please set default redirection rules for users when they try to access restricetd content.', 'armember-membership' ); ?>"></i>
				</div>
					<div class="page_sub_title"></div>

					<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
						<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
							<div class="left_content">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'For non logged in users', 'armember-membership' ); ?> <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'Set page for redirection in case when user is not loggedin & trying to access restricted page.', 'armember-membership' ); ?>"></i></div>
							</div>
						</div>
						<div class="arm_content_border"></div>
				
						<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
				
							<div class="arm_recstricted_page_post_redirection_input arm_padding_top_0" style="gap: 20px; flex-wrap: wrap; align-items: center;">
								<input type="radio" name="arm_redirection_settings[default_access_rules][non_logged_in][type]" id="arm_redirect_restricted_home" value="home" <?php checked( $arm_non_logged_in_type, 'home' ); ?> class="arm_iradio arm_redirect_restricted_page_input"><label for="arm_redirect_restricted_home" class="arm_min_width_140"><?php esc_html_e( 'Home Page', 'armember-membership' ); ?></label>
								<input type="radio" name="arm_redirection_settings[default_access_rules][non_logged_in][type]" id="arm_redirect_restricted_specific" value="specific" <?php checked( $arm_non_logged_in_type, 'specific' ); ?> class="arm_iradio arm_redirect_restricted_page_input"><label for="arm_redirect_restricted_specific"><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></label>
								
								<div class="arm_redirection_access_rules_specific arm_padding_0 arm_margin_top_32 arm_redirection_settings_selectbox" style="<?php echo ( $arm_non_logged_in_type == 'specific' ) ? '' : 'display:none'; ?>">
								<label class="arm-form-table-label "><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>

										<?php
										$arm_global_settings->arm_wp_dropdown_pages(
											array(
												'selected'              => ( isset( $arm_non_logged_in_redirect_to ) ? $arm_non_logged_in_redirect_to : 0 ),
												'name'                  => 'arm_redirection_settings[default_access_rules][non_logged_in][redirect_to]',
												'id'                    => 'redirect_url',
												'show_option_none'      => 'Select Page',
												'option_none_value'     => '0',
											)
										);
										?>
								<span class="arm_redirection_access_rules_non_loggedin_specific_error">
									<?php esc_html_e( 'The selected page is restricted item from access rule. Please select another page.', 'armember-membership' ); ?>
								</span>
								<span class="arm_redirection_access_rules_non_loggedin_specific_blank_error">
									<?php esc_html_e( 'Please Select Page.', 'armember-membership' ); ?>
								</span>
							</div>
						</div>
			
						</div>
					</div>	

					<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="changeCurrency">
						<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
							<div class="left_content">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'For logged in users', 'armember-membership' ); ?> <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'Set page for redirection in case when user is loggedin & trying to access restricted page.', 'armember-membership' ); ?>"></i></div>
							</div>
						</div>
						<div class="arm_content_border"></div>
				
						<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">
				
							<div class="arm_recstricted_page_post_redirection_input arm_padding_top_0" style="gap: 20px; flex-wrap: wrap; align-items: center;">
								<input type="radio" name="arm_redirection_settings[default_access_rules][logged_in][type]" id="arm_redirect_logged_in_restricted_home" value="home" <?php checked( $arm_logged_in_type, 'home' ); ?> class="arm_iradio arm_redirect_logged_in_restricted_page_input"><label for="arm_redirect_logged_in_restricted_home" class="arm_min_width_140"><?php esc_html_e( 'Home Page', 'armember-membership' ); ?></label>

								<input type="radio" name="arm_redirection_settings[default_access_rules][logged_in][type]" id="arm_redirect_logged_in_restricted_specific" value="specific" <?php checked( $arm_logged_in_type, 'specific' ); ?> class="arm_iradio arm_redirect_logged_in_restricted_page_input"><label for="arm_redirect_logged_in_restricted_specific"><?php esc_html_e( 'Specific Page', 'armember-membership' ); ?></label>
								<div class="arm_redirection_access_rules_logged_in_specific arm_padding_0 arm_margin_top_32 arm_redirection_settings_selectbox" style="<?php echo ( @$arm_logged_in_type == 'specific' ) ? '' : 'display:none'; ?>">
								<label class="arm-form-table-label"><?php esc_html_e( 'Select Page', 'armember-membership' ); ?></label>
									<?php
									$arm_global_settings->arm_wp_dropdown_pages(
										array(
											'selected' => ( isset( $arm_logged_in_redirect_to ) ? $arm_logged_in_redirect_to : 0 ),
											'name' => 'arm_redirection_settings[default_access_rules][logged_in][redirect_to]',
											'id'   => 'redirect_url_logged_in',
											'show_option_none' => 'Select Page',
											'option_none_value' => '0',
										)
									);
									?>
									<span class="arm_redirection_access_rules_loggedin_specific_error">
										<?php esc_html_e( 'The selected page is restricted item from access rule. Please select another page.', 'armember-membership' ); ?>
									</span>
									<span class="arm_redirection_access_rules_loggedin_specific_blank_error">
										<?php esc_html_e( 'Please Select Page.', 'armember-membership' ); ?>
									</span>
								</div>
							</div>
						</div>
					</div>
			<?php
			if($ARMemberLite->is_arm_pro_active){
				$arm_add_redirection_setting_option_content = '';
				echo apply_filters( 'arm_add_redirection_setting_option',$arm_add_redirection_setting_option_content,$redirection_settings); //phpcs:ignore
			}
			?>
			<div class="arm_submit_btn_container arm_redirection_submit_btn arm_apply_changes_btn_container arm_padding_bottom_32 ">
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_redirection_settings_btn <?php echo  ($ARMemberLite->is_arm_pro_active) ? 'arm_pro' : '';?>" type="submit" id="arm_redirection_settings_btn" name="arm_redirection_settings_btn"><?php esc_html_e( 'Apply Changes', 'armember-membership' ); ?></button>

				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</div>
		</form>		
	</div>
</div>

<div id="arm_all_pages" style="display:none;visibility: hidden;opacity: 0;">
<?php
$arm_page_args = array(
	'depth'                 => 0,
	'child_of'              => 0,
	'selected'              => 0,
	'echo'                  => 1,
	'name'                  => 'arm_redirection_settings[login][page_id]',
	'id'                    => 'arm_login_redirection_page',
	'show_option_none'      => esc_html__( 'Select Page', 'armember-membership' ),
	'show_option_no_change' => '',
	'option_none_value'     => '',
	'class'                 => 'arm_login_redirection_page',
);
$new_pages     = $arm_global_settings->arm_get_wp_pages( $arm_page_args, array( 'ID', 'post_title' ) );
echo wp_json_encode( $new_pages );
?>
</div>
	
<div id="arm_all_plans" style="display:none;visibility: hidden;opacity: 0;"><?php echo wp_json_encode( $all_plans ); ?></div>
<div id="arm_all_signup_forms" style="display:none;visibility: hidden;opacity: 0;"><?php echo wp_json_encode( $arm_forms ); ?></div>
<div id="arm_all_edit_profile_forms" style="display:none;visibility: hidden;opacity: 0;"><?php echo wp_json_encode($arm_edit_profile_forms); ?></div>
<script>
	var IF_USER_HAVE = '<?php echo addslashes( esc_html__( 'If User Has', 'armember-membership' ) ); //phpcs:ignore ?>';
	var PLAN_AND = '<?php echo addslashes( esc_html__( '&', 'armember-membership' ) ); //phpcs:ignore ?>';
	var SELECT_CONDITION = '<?php echo addslashes( esc_html__( 'Any Condition', 'armember-membership' ) ); //phpcs:ignore ?>';
	var SELECT_FORM = '<?php echo addslashes( esc_html__( 'Select Form', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ALL_FORM = '<?php echo addslashes( esc_html__( 'All Forms', 'armember-membership' ) ); //phpcs:ignore ?>';
	var IN_TRIAL = '<?php echo addslashes( esc_html__( 'In Trial', 'armember-membership' ) ); //phpcs:ignore ?>';
	var FAILED_PAYMENT = '<?php echo addslashes( esc_html__( 'Failed Payment(Suspended)', 'armember-membership' ) ); //phpcs:ignore ?>';
	var GRACE = '<?php echo addslashes( esc_html__( 'In Grace Period', 'armember-membership' ) ); //phpcs:ignore ?>';
	var BEFORE_EXPIRE = '<?php echo addslashes( esc_html__( 'Before Expiration Of', 'armember-membership' ) ); //phpcs:ignore ?>';
	var PENDING = '<?php echo addslashes( esc_html__( 'Pending', 'armember-membership' ) ); //phpcs:ignore ?>';
	var THEN_REDIRECT_TO = '<?php echo addslashes( esc_html__( 'Then Redirect To', 'armember-membership' ) ); //phpcs:ignore ?>';
	var  FIRST_TIME = '<?php echo addslashes( esc_html__( 'First Time Logged In', 'armember-membership' ) ); //phpcs:ignore ?>';
	var   HOME_URL = '<?php echo ARMLITE_HOME_URL; //phpcs:ignore ?>';
	var __SELECT_PLAN = '<?php echo addslashes( esc_html__( 'Select Plan', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __No_PLAN = '<?php echo addslashes( esc_html__( 'No Plan', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ANY_PLAN = '<?php echo addslashes( esc_html__( 'Any Plan', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __SELECT_PAGE = '<?php echo addslashes( esc_html__( 'Select Page', 'armember-membership' ) ); //phpcs:ignore ?>';
	var REMESSAGE = '<?php echo addslashes( esc_html__( 'You can not remove all Conditions', 'armember-membership' ) ); //phpcs:ignore ?>';
	var DAYS = '<?php echo addslashes( esc_html__( ' Days', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_RSC_PLAN_ID = '<?php echo addslashes( esc_html__( 'Please select plan.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_RSC_REDIRECT = '<?php echo addslashes( esc_html__( 'Please select condition.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_RSC_URL = '<?php echo addslashes( esc_html__( 'Please enter URL.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_RSC_PAGE = '<?php echo addslashes( esc_html__( 'Please select a page.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var IF_FORM_IS = '<?php echo addslashes( esc_html__( 'If SignUp Form is', 'armember-membership' ) ); //phpcs:ignore ?>';
	var IF_PLAN_IS = '<?php echo addslashes( esc_html__( 'If user selected plan is', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_RSC_FORM_ID = '<?php echo addslashes( esc_html__( 'Please select signup form.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_EDIT_PROFILE_FORM_ID = '<?php echo addslashes( esc_html__('Please select Edit Profile form.', 'armember-membership')); //phpcs:ignore?>';
	var ARM_MEMBERSHIP_PLAN = '<?php echo addslashes( esc_html__( 'Membership Plan', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_ACTION = '<?php echo addslashes( esc_html__( 'Action', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ARM_SET_REDIRECTION_PRIORITY = '<?php echo addslashes( esc_html__( 'Set Redirection Priority', 'armember-membership' ) ); //phpcs:ignore ?>';
	
	var ARM_RR_CLOSE_IMG = '<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>/close_btn.svg';
	var ARM_RR_CLOSE_IMG_HOVER = '<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>/close_btn_hover.png';
	var CHOOSEPLAN =  '<?php echo addslashes( esc_html__( 'Choose Plan', 'armember-membership' ) ); //phpcs:ignore ?>';
</script>
