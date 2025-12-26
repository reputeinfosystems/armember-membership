<?php


global $wpdb, $ARMemberLite, $arm_slugs, $arm_shortcodes, $arm_global_settings, $arm_member_forms, $arm_subscription_plans, $arm_membership_setup, $arm_social_feature, $arm_members_directory;
$arm_forms          = $arm_member_forms->arm_get_all_member_forms( 'arm_form_id, arm_form_label, arm_form_type' );
$all_plans          = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name' );
$arm_all_free_plans = $arm_subscription_plans->arm_get_all_free_plans();
$all_roles          = $arm_global_settings->arm_get_all_roles();
$total_setups       = $arm_membership_setup->arm_total_setups();
$wrapperClass       = 'arm_shortcode_options_popup_wrapper popup_wrapper arm_normal_wrapper ';
if ( is_rtl() ) {
	$wrapperClass .= ' arm_rtl_wrapper ';
}
?>
<!--********************/. Form Shortcodes ./********************-->
<div id="arm_form_shortcode_options_popup_wrapper" class="<?php echo esc_html($wrapperClass); ?>" style="width:960px;">
	<input type="hidden" id="arm_ajaxurl" value="<?php echo admin_url( 'admin-ajax.php' ); //phpcs:ignore ?>" />
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn"></span>
			<span class="popup_header_text"><?php esc_html_e( 'Membership Shortcodes', 'armember-membership' ); ?></span>
		</div>
		<div class="popup_content_text arm_shortcode_options_container">
			<div class="arm_tabgroups">
				<div class="arm_tabgroup_belt">
					<ul class="arm_tabgroup_link_container">
						<li class="arm_tabgroup_link arm_active">
							<a href="#arm-forms" data-id="arm-forms"><?php esc_html_e( 'Forms', 'armember-membership' ); ?></a>
						</li>
						<?php if ( $total_setups > 0 ) : ?>
						<li class="arm_tabgroup_link arm_tabgroup_link_setup">
							<a href="#arm-membership-setup" data-id="arm-membership-setup"><?php esc_html_e( 'Membership Setup Wizard', 'armember-membership' ); ?></a>
						</li>
						<?php endif; ?>
						<li class="arm_tabgroup_link">
							<a href="#arm-action-buttons" data-id="arm-action-buttons"><?php esc_html_e( 'Action Buttons', 'armember-membership' ); ?></a>
						</li>
						<li class="arm_tabgroup_link">
							<a href="#arm-other" data-id="arm-other"><?php esc_html_e( 'Others', 'armember-membership' ); ?></a>
						</li>
						
						
												<?php do_action( 'arm_shortcode_add_tab' ); ?>
					</ul>
				</div>
				<div class="arm_tabgroup_content_wrapper">
					<div id="arm-forms" class="arm_tabgroup_content arm_show">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php esc_html_e( 'Select Form Type', 'armember-membership' ); ?></th>
									<td>
										<input type="hidden" id="arm_shortcode_form_type" name="" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_form_type">
													<li data-label="<?php esc_attr_e( 'Select Form Type', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Form Type', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Registration', 'armember-membership' ); ?>" data-value="registration"><?php esc_html_e( 'Registration', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Login', 'armember-membership' ); ?>" data-value="login"><?php esc_html_e( 'Login', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Forgot Password', 'armember-membership' ); ?>" data-value="forgot_password"><?php esc_html_e( 'Forgot Password', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Change Password', 'armember-membership' ); ?>" data-value="change_password"><?php esc_html_e( 'Change Password', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Edit Profile', 'armember-membership' ); ?>" data-value="edit_profile"><?php esc_html_e( 'Edit Profile', 'armember-membership' ); ?></li>
													</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>

						<form class="arm_shortcode_form_opts arm_shortcode_form_select arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr class="arm_shortcode_form_select arm_shortcode_form_main_opt">
										<th><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_form_id" name="id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_shortcode_form_id_wrapper arm_reg_sc_form_lists" data-id="arm_shortcode_form_id">
														<li data-label="<?php esc_attr_e( 'Select Form', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></li>
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
										</td>
									</tr>									
									
									<tr class="arm_shortcode_form_main_opt arm_shortcode_form_position">
										<th><?php esc_html_e( 'Form Position', 'armember-membership' ); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="left" class="arm_iradio" />
												<span><?php esc_html_e( 'Left', 'armember-membership' ); ?></span>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="center" class="arm_iradio" checked="checked" />
												<span><?php esc_html_e( 'Center', 'armember-membership' ); ?></span>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="right" class="arm_iradio" />
												<span><?php esc_html_e( 'Right', 'armember-membership' ); ?></span>
											</label>
											<div class="arm_margin_left_10">(<?php esc_html_e( 'With Respect to its container', 'armember-membership' ); ?>)</div>
										</td>
									</tr>
																		
																		<tr id="arm_assign_default_plan_opt_wrapper" class="arm_shortcode_form_main_opt arm_shortcode_form_popup_options arm_hidden">
										<th><?php esc_html_e( 'Assign Default Plan', 'armember-membership' ); ?></th>
										<td>
																					<input type="hidden" id="arm_assign_default_plan" name="assign_default_plan" value="0" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_assign_default_plan_wrapper" data-id="arm_assign_default_plan">
														<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
														<?php if ( ! empty( $arm_all_free_plans ) ) : ?>
															<?php foreach ( $arm_all_free_plans as $plan ) : ?>
																<li class="arm_assign_default_plan_li <?php echo esc_attr(stripslashes( $plan['arm_subscription_plan_name']) ); //phpcs:ignore ?>" data-label="<?php echo esc_attr(stripslashes( $plan['arm_subscription_plan_name']) ); //phpcs:ignore ?>" data-value="<?php echo intval($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></li>
															<?php endforeach; ?>
														<?php endif; ?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
																		
																		<tr id="arm_logged_in_message_opt_wrapper" class="arm_shortcode_form_options arm_shortcode_form_main_opt arm_shortcode_form_popup_options arm_hidden">
										<th><?php esc_html_e( 'Logged In Message', 'armember-membership' ); ?></th>
										<td>
											<input type="text" name="logged_in_message" value="<?php esc_html_e( 'You are already logged in.', 'armember-membership' ); ?>" id="logged_in_message_input"><br/>
										</td>
									</tr>
								</table>
								<div class="armclear"></div>
							</div>

						</form>
						<form class="arm_shortcode_form_opts arm_shortcode_edit_profile_opts arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																		<tr class="arm_shortcode_form_select">
										<th><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_form_name" class="arm_shortcode_edit_profile_form" name="form_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul class="arm_shortcode_form_id_wrapper" data-id="arm_shortcode_form_name">
														<li data-label="<?php esc_attr_e( 'Select Form', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></li>
														<?php if ( ! empty( $arm_forms ) ) : ?>
															<?php
															foreach ( $arm_forms as $_form ) :
																?>
																		  <?php
																			if ( $_form['arm_form_type'] == 'registration' ) {
																				$formTitle = wp_strip_all_tags( stripslashes( $_form['arm_form_label'] ) ) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
																				?>
																 <li class="arm_shortcode_form_id_li_edit_profile <?php echo esc_attr($_form['arm_form_type']); ?>" data-label="<?php echo esc_attr($formTitle); ?>" data-value="<?php echo esc_attr($_form['arm_form_id']); ?>"><?php echo esc_html($formTitle); ?></li>
																				<?php
																			}  endforeach;
															?>
														<?php endif; ?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Form Position', 'armember-membership' ); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="left" class="arm_iradio arm_shortcode_form_popup_opt" />
												<span><?php esc_html_e( 'Left', 'armember-membership' ); ?></span>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="center" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<span><?php esc_html_e( 'Center', 'armember-membership' ); ?></span>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="form_position" value="right" class="arm_iradio arm_shortcode_form_popup_opt" />
												<span><?php esc_html_e( 'Right', 'armember-membership' ); ?></span>
											</label>
											<div class="arm_margin_left_10">(<?php esc_html_e( 'With Respect to its container', 'armember-membership' ); ?>)</div>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Title', 'armember-membership' ); ?></th>
										<td><input type="text" name="title" value="<?php esc_attr_e( 'Edit Profile', 'armember-membership' ); ?>"></td>
									</tr>
									<?php if ( $arm_social_feature->isSocialFeature ) : ?>
									<tr>
										<th><?php esc_html_e( 'Display Avatar', 'armember-membership' ); ?></th>
										<td>
											<label class="form_popup_type_radio">
												<input type="radio" name="avatar_field" value="yes" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<?php esc_html_e( 'Yes', 'armember-membership' ); ?>
											</label>
											<label class="form_popup_type_radio">
												<input type="radio" name="avatar_field" value="no" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php esc_html_e( 'No', 'armember-membership' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Display Profile Cover', 'armember-membership' ); ?></th>
										<td>
											<label class="edit_form_popup_type_radio">
												<input type="radio" name="profile_cover_field" value="yes" class="arm_iradio arm_shortcode_form_popup_opt" checked="checked" />
												<?php esc_html_e( 'Yes', 'armember-membership' ); ?>
											</label>
											<label class="edit_form_popup_type_radio">
												<input type="radio" name="profile_cover_field" value="no" class="arm_iradio arm_shortcode_form_popup_opt" />
												<?php esc_html_e( 'No', 'armember-membership' ); ?>
											</label>
										</td>
									</tr>
									<tr class="arm_edit_profile_cover_options">
										<th><?php esc_html_e( 'Profile Cover Title', 'armember-membership' ); ?></th>
										<td><input type="text" name="profile_cover_title" value="<?php esc_attr_e( 'Profile Cover', 'armember-membership' ); ?>"></td>
									</tr>
									<tr class="arm_edit_profile_cover_options">
										<th><?php esc_html_e( 'Profile Cover Placeholder', 'armember-membership' ); ?></th>
										<td><input type="text" name="profile_cover_placeholder" value="<?php esc_attr_e( 'Drop file here or click to select', 'armember-membership' ); ?>"></td>
									</tr>
									<?php endif; ?>
									<tr>
										<th><?php esc_html_e( 'Message', 'armember-membership' ); ?></th>
										<td><input type="text" name="message" value="<?php esc_attr_e( 'Your profile has been updated successfully.', 'armember-membership' ); ?>"></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'View Profile', 'armember-membership' ); ?></th>
										<td>
											<input type="checkbox" id='arm_profile_field_view_profile' value="true" class="arm_icheckbox" name="view_profile" id="" checked="checked" />
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'View Profile Link Label', 'armember-membership' ); ?></th>
										<td>
											<input type="text" name="view_profile_link" value="<?php esc_attr_e( 'View Profile', 'armember-membership' ); ?>" />
										</td>
									</tr>
								</table>
							</div>

						</form>
					</div>
					<div id="arm-membership-setup" class="arm_tabgroup_content">
												<form class="arm_shortcode_membership_setup_opts" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																	<tr class="arm_shortcode_setup_main_opt">
										<th><?php esc_html_e( 'Select Setup', 'armember-membership' ); ?></th>
										<td class="arm_sc_mem_setup_td">
											<?php

												$setups = $wpdb->get_results( 'SELECT `arm_setup_id`, `arm_setup_name` FROM `' . $ARMemberLite->tbl_arm_membership_setup . '` ' );//phpcs:ignore --Reason: $tbl_arm_membership_setup is a table name. NO need to add Prepare as Query without Where clause. False Positive Alarm
											?>
											<input type="hidden" id="arm_shortcode_membership_setup_id" name="id" value="<?php echo ( ! empty( $setups[0] ) ? $setups[0]->arm_setup_id : '' ); //phpcs:ignore ?>" />
											<dl class="arm_selectbox column_level_dd">
												<dt class="arm_sc_mem_setup_dt">
													<span></span>
													<input type="text" style="display:none;" value="" class="arm_autocomplete"/>
													<i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="arm_shortcode_membership_setup_id" class="arm_sc_mem_setup_lists">
													<li data-label="<?php esc_attr_e( 'Select Setup', 'armember-membership' ); ?>" data-value=""><?php esc_attr_e( 'Select Setup', 'armember-membership' ); ?></li>
														<?php
														if ( ! empty( $setups ) ) {

															foreach ( $setups as $ms ) {
																?>
														<li data-label="<?php echo esc_attr(stripslashes( $ms->arm_setup_name) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($ms->arm_setup_id); //phpcs:ignore ?>"><?php echo esc_html( stripslashes( $ms->arm_setup_name) ); //phpcs:ignore ?></li>
																<?php
															}
														}
														?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr class="arm_shortcode_setup_main_opt">
										<th><?php esc_html_e( 'Hide Setup Title?', 'armember-membership' ); ?></th>
										<td>
											<label>
												<input type="radio" name="hide_title" value="true" class="arm_iradio">
												<span><?php esc_html_e( 'Yes', 'armember-membership' ); ?></span>
											</label>
											<label>
												<input type="radio" name="hide_title" value="false" class="arm_iradio" checked="checked">
												<span><?php esc_html_e( 'No', 'armember-membership' ); ?></span>
											</label>
										</td>
									</tr>
																		
																		<tr class="arm_shortcode_setup_main_opt">
																			<th class="arm_color_red"><?php esc_html_e( 'Important Notes', 'armember-membership' ); ?></th>
																			<td>
																				<div class="arm_padding_top_5"><?php esc_html_e( 'Add hide_plans="1" parameter to hide plan selection area.', 'armember-membership' ); ?></div>
																				<div class="arm_padding_top_5"><?php esc_html_e( 'Add subscription_plan="PLAN_ID" parameter to keep plan having PLAN_ID selected.', 'armember-membership' ); ?></div>
																			</td>
									</tr>
																		
								</table>
							</div>
						</form>
					</div>
					<div id="arm-action-buttons" class="arm_tabgroup_content">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php esc_html_e( 'Select Action Type', 'armember-membership' ); ?></th>
									<td>
										<input type="hidden" id="arm_shortcode_action_button_type" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_action_button_type">
													<li data-label="<?php esc_attr_e( 'Select Action Type', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Action Type', 'armember-membership' ); ?></li>
													<?php $social_options = $arm_social_feature->arm_get_active_social_options(); ?>                                                                                                      
													<li data-label="<?php esc_attr_e( 'Logout', 'armember-membership' ); ?>" data-value="arm_logout"><?php esc_html_e( 'Logout', 'armember-membership' ); ?></li>

												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>
					  
						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_logout arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php esc_html_e( 'Link Type', 'armember-membership' ); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_logout_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_logout_link_type">
														<li data-label="<?php esc_attr_e( 'Link', 'armember-membership' ); ?>" data-value="link"><?php esc_html_e( 'Link', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'Button', 'armember-membership' ); ?>" data-value="button"><?php esc_html_e( 'Button', 'armember-membership' ); ?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php esc_html_e( 'Link Text', 'armember-membership' ); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php esc_html_e( 'Button Text', 'armember-membership' ); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php esc_attr_e( 'Logout', 'armember-membership' ); ?>"></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Display User Info?', 'armember-membership' ); ?></th>
										<td>
											<label>
												<input type="radio" name="user_info" value="true" class="arm_iradio" checked="checked">
												<span><?php esc_html_e( 'Yes', 'armember-membership' ); ?></span>
											</label>
											<label>
												<input type="radio" name="user_info" value="false" class="arm_iradio">
												<span><?php esc_html_e( 'No', 'armember-membership' ); ?></span>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Redirect After Logout', 'armember-membership' ); ?></th>
										<td>
											<input type="text" name="redirect_to" value="<?php echo esc_url(ARMLITE_HOME_URL); //phpcs:ignore ?>">
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php esc_html_e( 'Link CSS', 'armember-membership' ); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php esc_html_e( 'Button CSS', 'armember-membership' ); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_logout_link_opts"><?php esc_html_e( 'Link Hover CSS', 'armember-membership' ); ?></span>
											<span class="arm_shortcode_logout_button_opts arm_hidden"><?php esc_html_e( 'Button Hover CSS', 'armember-membership' ); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
								</table>
							</div>
						</form>

						<form class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_cancel_membership arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php esc_html_e( 'Link Type', 'armember-membership' ); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_cancel_membership_link_type" name="type" value="link" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_cancel_membership_link_type">
														<li data-label="<?php esc_attr_e( 'Link', 'armember-membership' ); ?>" data-value="link"><?php esc_html_e( 'Link', 'armember-membership' ); ?></li>
														<li data-label="<?php esc_attr_e( 'Button', 'armember-membership' ); ?>" data-value="button"><?php esc_html_e( 'Button', 'armember-membership' ); ?></li>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php esc_html_e( 'Link Text', 'armember-membership' ); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php esc_html_e( 'Button Text', 'armember-membership' ); ?></span>
										</th>
										<td><input type="text" name="label" value="<?php esc_attr_e( 'Cancel Subscription', 'armember-membership' ); ?>"></td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php esc_html_e( 'Link CSS', 'armember-membership' ); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php esc_html_e( 'Button CSS', 'armember-membership' ); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #000000;</em>
										</td>
									</tr>
									<tr>
										<th>
											<span class="arm_shortcode_cancel_membership_link_opts"><?php esc_html_e( 'Link Hover CSS', 'armember-membership' ); ?></span>
											<span class="arm_shortcode_cancel_membership_button_opts arm_hidden"><?php esc_html_e( 'Button Hover CSS', 'armember-membership' ); ?></span>
										</th>
										<td>
											<textarea class="arm_popup_textarea" name="link_hover_css" rows="3"></textarea>
												<br/>
												<em>e.g. color: #ffffff;</em>
										</td>
																				
									</tr>
								</table>
							</div>

						</form>
					</div>
					<div id="arm-other" class="arm_tabgroup_content">
						<div class="arm_group_body">
							<table class="arm_shortcode_option_table">
								<tr>
									<th><?php esc_html_e( 'Select Option', 'armember-membership' ); ?></th>
									<td>
										<input type="hidden" id="arm_shortcode_other_type" value="" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_other_type">
													<li data-label="<?php esc_attr_e( 'Select Option', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Option', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'My Profile', 'armember-membership' ); ?>" data-value="arm_account_detail"><?php esc_html_e( 'My Profile', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Payment Transactions', 'armember-membership' ); ?>" data-value="arm_member_transaction"><?php esc_html_e( 'Payment Transactions', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Current Membership', 'armember-membership' ); ?>" data-value="arm_current_membership"><?php esc_html_e( 'Current Membership', 'armember-membership' ); ?></li>
													
													 <li data-label="<?php esc_attr_e( 'Close Account', 'armember-membership' ); ?>"
																	  data-value="arm_close_account"><?php esc_html_e( 'Close Account', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Current User Information', 'armember-membership' ); ?>" data-value="arm_greeting_message"><?php esc_html_e( 'Current User Information', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Check If User In Trial Period', 'armember-membership' ); ?>" data-value="arm_check_if_user_in_trial"><?php esc_html_e( 'Check If User In Trial Period', 'armember-membership' ); ?></li>
													
												<li data-label="<?php esc_attr_e( 'User Plan Information', 'armember-membership' ); ?>" data-value="arm_user_planinfo"><?php esc_html_e( 'User Plan Information', 'armember-membership' ); ?></li>
													<?php do_action( 'add_others_section_option_tinymce' ); ?>
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
							</table>
						</div>

						
						
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_member_transaction arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php esc_html_e( 'Transaction History', 'armember-membership' ); ?></th>
										<td>
											<ul class="arm_member_transaction_fields">
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_id" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_transaction_id" value="<?php esc_attr_e( 'Transaction ID', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="plan" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_plan" value="<?php esc_attr_e( 'Plan', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_gateway" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_gateway" value="<?php esc_attr_e( 'Payment Gateway', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_type" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_type" value="<?php esc_attr_e( 'Payment Type', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="transaction_status" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_transaction_status" value="<?php esc_attr_e( 'Transaction Status', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="amount" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_amount" value="<?php esc_attr_e( 'Amount', 'armember-membership' ); ?>" />
												</li>
											
												
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_transaction_fields" name="arm_transaction_fields[]" value="payment_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_transaction_field_label_payment_date" value="<?php esc_attr_e( 'Payment Date', 'armember-membership' ); ?>" />
												</li>
											</ul>
										</td>
									</tr>


									
								   
									


									<tr>
										<th><?php esc_html_e( 'Title', 'armember-membership' ); ?></th>
										<td>
											<input type="text" class='arm_member_transaction_opts' name="title" value="<?php esc_attr_e( 'Transactions', 'armember-membership' ); ?>">
										</td>
									</tr>
																		<tr>
										<th><?php esc_html_e( 'Records Per Page', 'armember-membership' ); ?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="per_page" value="5">
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'No Records Message', 'armember-membership' ); ?></th>
										<td>
											<input type="text" class="arm_member_transaction_opts" name="message_no_record" value="<?php esc_attr_e( 'There is no any Transactions found', 'armember-membership' ); ?>">
										</td>
									</tr>
								</table>
							</div>

						</form>
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_account_detail arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									
									<tr>
										<th><?php esc_html_e( 'Profile Fields', 'armember-membership' ); ?></th>
										<td class="arm_view_profile_wrapper">
										<?php
											$dbProfileFields = $arm_members_directory->arm_template_profile_fields();
										if ( ! empty( $dbProfileFields ) ) :
											?>
						   <ul class="arm_member_transaction_fields">
							
											<?php
											$i = 1;
											foreach ( $dbProfileFields as $fieldMetaKey => $fieldOpt ) :
												?>
												<?php
												if ( empty( $fieldMetaKey ) || $fieldMetaKey == 'user_pass' || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
													continue;
												}
												$fchecked = '';
												if ( in_array( $fieldMetaKey, array( 'user_email', 'user_login', 'first_name', 'last_name' ) ) ) {
													$fchecked = 'checked="checked"';
												}
												?>



												
												<li class="arm_member_transaction_field_list">
													<label class="arm_member_transaction_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_account_detail_fields" name="arm_account_detail_fields[]" value="<?php echo esc_attr($fieldMetaKey); ?>" <?php echo $fchecked; //phpcs:ignore ?> />
													</label>
													<input type="text" class="arm_member_transaction_fields" name="arm_account_detail_field_label_<?php echo esc_attr($fieldMetaKey); ?>" value="<?php echo esc_attr(stripslashes_deep( $fieldOpt['label']) ); //phpcs:ignore ?>" />
												</li>
												  
												
												<?php
												$i++;
										endforeach;
											?>
											</ul>
											<?php endif; ?>
										</td>
									</tr>
								   
								</table>
							</div>

						</form>
											<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_current_membership arm_hidden" onsubmit="return false;">
							
													
													<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																	<tr>
										<th><?php esc_html_e( 'Title', 'armember-membership' ); ?></th>
										<td>
											<input type="text" class='arm_member_current_membership_opts' name="title" value="<?php esc_attr_e( 'Current Membership', 'armember-membership' ); ?>">
										</td>
									</tr>
																		
																		
																		
																		<tr>
										<th><?php esc_html_e( 'Select Setup', 'armember-membership' ); ?></th>
										<td>
											<?php // $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMemberLite->tbl_arm_membership_setup."` "); ?>
											<input type="hidden" id="arm_shortcode_current_membership_setup_id" name="setup_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_shortcode_current_membership_setup_id">
													   <li data-label="<?php esc_attr_e( 'Select Setup', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Setup', 'armember-membership' ); ?></li>
													<?php if ( ! empty( $setups ) ) : ?>
														<?php foreach ( $setups as $ms ) : ?>
														<li data-label="<?php echo esc_attr(stripslashes( $ms->arm_setup_name) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($ms->arm_setup_id); //phpcs:ignore ?>"><?php echo esc_html(stripslashes( $ms->arm_setup_name )); //phpcs:ignore ?></li>
														<?php endforeach; ?>
													<?php endif; ?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
																		
																		
																		
									<tr>
										<th><?php esc_html_e( 'Current Membership', 'armember-membership' ); ?></th>
										<td>
											<ul class="arm_member_current_membership_fields">

											<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_no" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_no" value="<?php esc_attr_e( 'No.', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_is" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_is" value="<?php esc_attr_e( 'Membership Plan', 'armember-membership' ); ?>" />
												</li>
												 <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_recurring_profile" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_recurring_profile" value="<?php esc_attr_e( 'Plan Type', 'armember-membership' ); ?>" />
												</li>
												  
													  <li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_started_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_started_on" value="<?php esc_attr_e( 'Starts On', 'armember-membership' ); ?>" />
												</li>
													<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_expired_on" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_expired_on" value="<?php esc_attr_e( 'Expires On', 'armember-membership' ); ?>" />
												</li>
													<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="current_membership_next_billing_date" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_current_membership_next_billing_date" value="<?php esc_attr_e( 'Cycle Date', 'armember-membership' ); ?>" />
												</li>
												<li class="arm_member_current_membership_field_list">
													<label class="arm_member_current_membership_field_item">
														<input type="checkbox" class="arm_icheckbox arm_member_current_membership_fields" name="arm_current_membership_fields[]" value="action_button" checked="checked" />
													</label>
													<input type="text" class="arm_member_current_membership_fields" name="arm_current_membership_field_label_action_button" value="<?php esc_attr_e( 'Action', 'armember-membership' ); ?>" />
												</li>
																								
												
											</ul>
										</td>
									</tr>
									
																		
																		<tr>
										<th><?php esc_html_e( 'Display Renew Subscription Button?', 'armember-membership' ); ?></th>
										<td>
											<label class="renew_subscription_radio">
												<input type="radio" name="display_renew_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
												<span><?php esc_html_e( 'No', 'armember-membership' ); ?></span>
											</label>
											<label class="renew_subscription_radio">
												<input type="radio" name="display_renew_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
												<span><?php esc_html_e( 'Yes', 'armember-membership' ); ?></span>
											</label>
										</td>
									</tr>
									<tr class="renew_subscription_btn_options">
										<th><?php esc_html_e( 'Renew Text', 'armember-membership' ); ?></th>
										<td><input type="text" name="renew_text" value="<?php esc_attr_e( 'Renew', 'armember-membership' ); ?>" /></td>
									</tr>
									<tr class="renew_subscription_btn_options">
										<th><?php esc_html_e( 'Make Payment Text', 'armember-membership' ); ?></th>
										<td><input type="text" name="make_payment_text" value="<?php esc_attr_e( 'Make Payment', 'armember-membership' ); ?>" /></td>
									</tr>
								   
									<tr class="renew_subscription_btn_options">
										<th><?php esc_html_e( 'Button CSS', 'armember-membership' ); ?></th>
										<td>
											<textarea class="arm_popup_textarea" name="renew_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
									<tr class="renew_subscription_btn_options">
										<th><?php esc_html_e( 'Button Hover CSS', 'armember-membership' ); ?></th>
										<td>
											<textarea class="arm_popup_textarea" name="renew_hover_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
									
									
									<tr>
										<th><?php esc_html_e( 'Display Cancel Subscription Button?', 'armember-membership' ); ?></th>
										<td>
											<label class="cancel_subscription_radio">
												<input type="radio" name="display_cancel_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked"/>
												<span><?php esc_html_e( 'No', 'armember-membership' ); ?></span>
											</label>
											<label class="cancel_subscription_radio">
												<input type="radio" name="display_cancel_button" value="true" class="arm_iradio arm_shortcode_subscription_opt" />
												<span><?php esc_html_e( 'Yes', 'armember-membership' ); ?></span>
											</label>
										</td>
									</tr>
									<tr class="cancel_subscription_btn_options">
										<th><?php esc_html_e( 'Button Text', 'armember-membership' ); ?></th>
										<td><input type="text" name="cancel_text" value="<?php esc_attr_e( 'Cancel', 'armember-membership' ); ?>" /></td>
									</tr>
									
									<tr class="cancel_subscription_btn_options">
										<th><?php esc_html_e( 'Button CSS', 'armember-membership' ); ?></th>
										<td>
											<textarea class="arm_popup_textarea" name="cancel_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
									<tr class="cancel_subscription_btn_options">
										<th><?php esc_html_e( 'Button Hover CSS', 'armember-membership' ); ?></th>
										<td>
											<textarea class="arm_popup_textarea" name="cancel_hover_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
									<tr class="cancel_subscription_btn_options">
										<th><?php esc_html_e( 'Subscription Cancelled Message', 'armember-membership' ); ?></th>
										<td><input type="text" name="cancel_message" value="<?php esc_attr_e( 'Your subscription has been cancelled.', 'armember-membership' ); ?>" /></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Display Update Card Subscription Button?', 'armember-membership' ); ?></th>
										<td>
											<label class="update_card_subscription_radio">
												<input type="radio" name="display_update_card_button" value="false" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" />
												<span><?php esc_html_e( 'No', 'armember-membership' ); ?></span>
											</label>
											<label class="update_card_subscription_radio">
												<input type="radio" name="display_update_card_button" value="true" class="arm_iradio arm_shortcode_subscription_opt"  />
												<span><?php esc_html_e( 'Yes', 'armember-membership' ); ?></span>
											</label>
										</td>
									</tr>
									<tr class="update_card_subscription_btn_options">
										<th><?php esc_html_e( 'Update Card Text', 'armember-membership' ); ?></th>
										<td><input type="text" name="update_card_text" value="<?php esc_attr_e( 'Update Card', 'armember-membership' ); ?>" /></td>
									</tr>
									
									<tr class="update_card_subscription_btn_options">
										<th><?php esc_html_e( 'Button CSS', 'armember-membership' ); ?></th>
										<td>
											<textarea class="arm_popup_textarea" name="update_card_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
									<tr class="update_card_subscription_btn_options">
										<th><?php esc_html_e( 'Button Hover CSS', 'armember-membership' ); ?></th>
										<td>
											<textarea class="arm_popup_textarea" name="update_card_hover_css" rows="3"></textarea>
											<br/>
											<em>e.g. color: #ffffff;</em>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Trial Active Label', 'armember-membership' ); ?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="trial_active" value="<?php esc_attr_e( 'trial active', 'armember-membership' ); ?>">
										</td>
									</tr>
	  
									<tr>
										<th><?php esc_html_e( 'No Records Message', 'armember-membership' ); ?></th>
										<td>
											<input type="text" class="arm_member_current_membership_opts" name="message_no_record" value="<?php esc_html_e( 'There is no membership found.', 'armember-membership' ); ?>">
										</td>
									</tr>
								</table>
							</div>
													
										

						</form>
									   
											
											
						
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_close_account arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
									<tr>
										<th><?php esc_html_e( 'Select Set of Login Form', 'armember-membership' ); ?></th>
										<td>
											<input type="hidden" id="arm_shortcode_close_account" name="set_id" value="" />
											<dl class="arm_selectbox column_level_dd">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<?php $setnames = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type` = %s GROUP BY arm_set_id ORDER BY arm_form_id ASC",'login') );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name ?>
													<ul data-id="arm_shortcode_close_account" class="arm_shortcode_form_id_wrapper">
														<li data-label="<?php esc_attr_e( 'Select Set', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Set', 'armember-membership' ); ?></li>
														<?php if ( ! empty( $setnames ) ) : ?>
															<?php foreach ( $setnames as $sn ) : ?>
																<li data-label="<?php echo stripslashes( esc_attr($sn->arm_set_name) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($sn->arm_form_id); //phpcs:ignore ?>"><?php echo stripslashes( $sn->arm_set_name ); //phpcs:ignore ?></li>
															<?php endforeach; ?>
														<?php endif; ?>
													</ul>
												</dd>
											</dl>
										</td>
									</tr>
																		
								</table>
							</div>

						</form>
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_greeting_message arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																		<tr>
									<th><?php esc_html_e( 'Display Information Based On', 'armember-membership' ); ?></th>
									<td>
										<input type="hidden" id="arm_shortcode_username_type" name="type" value="" class="type" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_username_type">
													<li data-label="<?php esc_attr_e( 'Select Type', 'armember-membership' ); ?>" data-value=""><?php esc_attr_e( 'Select Username Type', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'User ID', 'armember-membership' ); ?>" data-value="arm_userid"><?php esc_html_e( 'User ID', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Username', 'armember-membership' ); ?>" data-value="arm_username"><?php esc_html_e( 'Username', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Display Name', 'armember-membership' ); ?>" data-value="arm_displayname"><?php esc_html_e( 'Display Name', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Firstname Lastname', 'armember-membership' ); ?>" data-value="arm_firstname_lastname"><?php esc_html_e( 'Firstname Lastname', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'User Plan', 'armember-membership' ); ?>" data-value="arm_user_plan"><?php esc_html_e( 'User Plan', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Avatar', 'armember-membership' ); ?>" data-value="arm_avatar"><?php esc_html_e( 'Avatar', 'armember-membership' ); ?></li>											
													<li data-label="<?php esc_attr_e( 'Custom Meta', 'armember-membership' ); ?>" data-value="arm_usermeta"><?php esc_html_e( 'Custom Meta', 'armember-membership' ); ?></li>
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
																<tr class="arm_shortcode_other_opts_arm_greeting_message_arm_usermeta arm_hidden">
																	<th><?php esc_html_e( 'Enter Usermeta Name', 'armember-membership' ); ?></th>
																	<td>
																		<input type="text" name="arm_custom_user_meta" id="arm_custom_user_meta" value="" />
																	</td>
																</tr>
								</table>
							</div>

						</form>
												<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_check_if_user_in_trial arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																		<tr>
									<th><?php esc_html_e( 'Display Content Based On', 'armember-membership' ); ?></th>
									<td>
										<input type="hidden" id="arm_shortcode_if_user_in_trial_or_not" name="type" value="" class="type" />
										<dl class="arm_selectbox column_level_dd">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_shortcode_if_user_in_trial_or_not">
													<li data-label="<?php esc_attr_e( 'Select Type', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Type', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'If User In Trial', 'armember-membership' ); ?>" data-value="arm_if_user_in_trial"><?php echo sprintf(esc_html__('If User %sIn%s Trial Period', 'armember-membership'),'<b>','</b>');?></li><?php //phpcs:ignore ?>
																										<li data-label="<?php esc_attr_e( 'If User Not In Trial', 'armember-membership' ); ?>" data-value="arm_not_if_user_in_trial"><?php echo sprintf(esc_html__('If User %sNot In%s Trial Period', 'armember-membership'),'<b>','</b>');?></li><?php //phpcs:ignore ?>
													
												</ul>
											</dd>
										</dl>
									</td>
								</tr>
								</table>
							</div>

						</form>
												<form class="arm_shortcode_other_opts arm_shortcode_other_opts_user_badge arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																		<tr>
									<th><?php esc_html_e( 'User Id', 'armember-membership' ); ?></th>
									<td>
										<input type="text" id="user_id" name="user_id" value="" class="type" />
									</td>
								</tr>
								</table>
							</div>

						</form>
											<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_user_planinfo arm_hidden" onsubmit="return false;">
							<div class="arm_group_body">
								<table class="arm_shortcode_option_table">
																		<tr>
																			<th><?php esc_html_e( 'Select Membership Plan', 'armember-membership' ); ?></th>
										<td class="arm_sc_upi_mp_td">
																				<input type='hidden' class="arm_user_plan_change_input" name="plan_id" id="arm_user_plan_0" value=""/>
																				<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
												<dt class="arm_sc_upi_mp_dt">
													<span></span>
													<input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="arm_user_plan_0" class="arm_upi_plan_list">
																					

		<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
														<?php
														foreach ( $all_plans as $p ) {

														echo '<li data-label="' . stripslashes( esc_attr( $p['arm_subscription_plan_name'] ) ) . '" data-value="' . esc_attr($p['arm_subscription_plan_id']) . '">' . stripslashes( esc_attr( $p['arm_subscription_plan_name'] ) ) . '</li>'; //phpcs:ignore

														}
														?>
													</ul>
												</dd>
																				</dl>
																			</td>
																		</tr>
																		<tr>
																			<th><?php esc_html_e( 'Select Plan Information', 'armember-membership' ); ?></th>
																			<td>
																				<input type='hidden' class="arm_user_plan_change_input" name="plan_info" id="arm_user_plan_info" value="start_date"/>
																				<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
																					<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																					<dd><ul data-id="arm_user_plan_info">
																							<li data-label="<?php esc_attr_e( 'Start Date', 'armember-membership' ); ?>" data-value="arm_start_plan"><?php esc_html_e( 'Start Date', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'End Date', 'armember-membership' ); ?>" data-value="arm_expire_plan"><?php esc_html_e( 'End Date', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Trial Start Date', 'armember-membership' ); ?>" data-value="arm_trial_start"><?php esc_html_e( 'Trial Start Date', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Trial End Date', 'armember-membership' ); ?>" data-value="arm_trial_end"><?php esc_html_e( 'Trial End Date', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Grace End Date', 'armember-membership' ); ?>" data-value="arm_grace_period_end"><?php esc_html_e( 'Grace End Date', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Paid By', 'armember-membership' ); ?>" data-value="arm_user_gateway"><?php esc_html_e( 'Paid By', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Completed Recurrence', 'armember-membership' ); ?>" data-value="arm_completed_recurring"><?php esc_html_e( 'Completed Recurrence', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Next Due Date', 'armember-membership' ); ?>" data-value="arm_next_due_payment"><?php esc_html_e( 'Next Due Date', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Payment Mode', 'armember-membership' ); ?>" data-value="arm_payment_mode"><?php esc_html_e( 'Payment Mode', 'armember-membership' ); ?></li>
																							<li data-label="<?php esc_attr_e( 'Payment Cycle', 'armember-membership' ); ?>" data-value="arm_payment_cycle"><?php esc_html_e( 'Payment Cycle', 'armember-membership' ); ?></li>
																						</ul></dd>
																				</dl>
																			</td>
																		</tr>
								</table>
							</div>

						</form>
											   
											
						<form class="arm_shortcode_other_opts arm_shortcode_other_opts_arm_last_login_history arm_hidden" onsubmit="return false;">
							<div class='arm_group_body'>
							</div>

						</form>
												<?php do_action( 'add_others_section_select_option_tinymce' ); ?>
					</div>
				
					
										<?php do_action( 'arm_shortcode_add_tab_content' ); ?>
				</div>
							
							<!-- add form shortcode buttons -->
							<div id="arm-forms_buttons" class="arm_tabgroup_content_buttons arm_show">
									<div class="arm_shortcode_form_opts arm_shortcode_form_opts_no_type" style="">
											<div class="popup_content_btn popup_footer arm_padding_top_0 arm_padding_bottom_0">
													<div class="popup_content_btn_wrapper arm_margin_bottom_12">
															<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
															<button type="button" class="arm_insrt_btn arm_margin_0" disabled="disabled"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
													</div>
											</div>
									</div>
									

									<div class="arm_group_footer arm_shortcode_form_opts arm_shortcode_form_select arm_hidden arm_padding_0" style="position:relative;">
											<div class="popup_content_btn_wrapper arm_margin_bottom_12">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_form_insert_btn arm_insrt_btn arm_shortcode_form_add_btn arm_margin_0" id="arm_shortcode_form_select" data-code="arm_form"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>
									<div class="arm_group_footer arm_shortcode_form_opts arm_shortcode_edit_profile_opts arm_hidden arm_padding_0">
											<div class="popup_content_btn_wrapper arm_margin_bottom_12">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_edit_profile_opts" data-code="arm_edit_profile"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>                                
							</div>
							<!-- add setup shortcode buttons -->
							<div id="arm-membership-setup_buttons" class="arm_tabgroup_content_buttons">      
									<div class="popup_content_btn popup_footer arm_padding_top_0 arm_padding_bottom_0 " style="">
											<div class="popup_content_btn_wrapper  arm_margin_bottom_12">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_setup_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_membership_setup_opts" data-code="arm_setup"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>                                
							</div>
							<!-- add action shortcode buttons -->
							<div id="arm-action-buttons_buttons" class="arm_tabgroup_content_buttons">
									<div class="arm_shortcode_action_button_opts arm_shortcode_action_button_opts_no_type" style="">
											<div class="popup_content_btn popup_footer arm_padding_top_0 arm_padding_bottom_0">
													<div class="popup_content_btn_wrapper  arm_margin_bottom_12">
														<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
														<button type="button" class="arm_insrt_btn arm_margin_right_0" disabled="disabled"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
													</div>
											</div>
									</div>                                

												   
									<div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_logout arm_hidden arm_padding_0">
											<div class="popup_content_btn_wrapper arm_margin_bottom_12 arm_margin_top_32">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_action_button_opts_arm_logout" data-code="arm_logout"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>
									<div class="arm_group_footer arm_shortcode_action_button_opts arm_shortcode_action_button_opts_arm_cancel_membership arm_hidden arm_padding_0">
											<div class="popup_content_btn_wrapper arm_margin_bottom_12 arm_margin_top_32">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_action_button_opts_arm_cancel_membership" data-code="arm_cancel_membership"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>                                
							</div>
							<!-- add other shortcode buttons -->
							<div id="arm-other_buttons" class="arm_tabgroup_content_buttons">
									<div class="arm_shortcode_other_opts arm_shortcode_other_opts_no_type" style="">
											<div class="popup_content_btn popup_footer arm_padding_top_0 arm_padding_bottom_0">
													<div class="popup_content_btn_wrapper  arm_margin_bottom_12">
														<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
														<button type="button" class="arm_insrt_btn arm_margin_right_0" disabled="disabled"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
													</div>
											</div>
									</div>     
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_member_transaction arm_hidden">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_member_transaction" data-code="arm_member_transaction"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>  
								<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_user_planinfo arm_hidden">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_user_planinfo_shortcode arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_user_planinfo" data-code="arm_user_planinfo" disabled="disabled"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div> 
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_account_detail arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_account_detail" data-code="arm_account_detail"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>      
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_current_membership arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_current_membership_shortcode arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_current_membership" data-code="arm_membership" disabled="disabled"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>                                
															   
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_close_account arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
													<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
													<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_close_account_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_close_account" data-code="arm_close_account"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>          
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_greeting_message arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_greeting_message" data-code="arm_greeting_message"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>   
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_check_if_user_in_trial arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_check_if_user_in_trial" data-code="arm_if_user_in_trial_or_not"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>     
									  
									
									
									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_login_history arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_login_history" data-code="arm_login_history"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>                                

									<div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_last_login_history arm_hidden" style="">
											<div class="popup_content_btn_wrapper">
												<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
												<button type="button" class="arm_shortcode_insert_btn arm_insrt_btn arm_margin_right_0" id="arm_shortcode_other_opts_arm_last_login_history" data-code="arm_last_login_history"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
											</div>
									</div>                                
							</div>
						   
							
							<?php do_action( 'arm_shortcode_add_tab_buttons' ); ?>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<!--********************/. Restrict Content Shortcodes ./********************-->
<div id="arm_restriction_shortcode_options_popup_wrapper" class="<?php echo esc_attr($wrapperClass); ?>">
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn"></span>
			<span class="popup_header_text"><?php esc_html_e( 'Content Restriction Shortcode', 'armember-membership' ); ?></span>
		</div>
		<div class="popup_content_text arm_shortcode_options_container">
							<form onsubmit="return false;" class="arm_shortcode_rc_form">
				<div class="arm_group_body">
					<table class="arm_shortcode_option_table">
						<tr>
							<th><?php esc_html_e( 'Restriction Type', 'armember-membership' ); ?></th>
							<td>
								<input type="hidden" id="arm_restriction_type" name="type" value="hide" />
								<dl class="arm_selectbox column_level_dd arm_width_330">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_restriction_type">
											<li data-label="<?php esc_attr_e( 'Hide content only for', 'armember-membership' ); ?>" data-value="hide"><?php esc_html_e( 'Hide content only for', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Show content only for', 'armember-membership' ); ?>" data-value="show"><?php esc_html_e( 'Show content only for', 'armember-membership' ); ?></li>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Target Users', 'armember-membership' ); ?></th>
							<td>
								<select name="plan" class="arm_chosen_selectbox arm_width_350" multiple data-placeholder="<?php esc_attr_e( 'Everyone', 'armember-membership' ); ?>" tabindex="-1" >
									<option value="registered"><?php esc_html_e( 'Loggedin Users', 'armember-membership' ); ?></option>
									<option value="unregistered"><?php esc_html_e( 'Non Loggedin Users', 'armember-membership' ); ?></option>
									<?php
									if ( ! empty( $all_plans ) ) {
										foreach ( $all_plans as $plan ) {
											?>
											<option value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></option>
																	  <?php
										}
									}
									?>
									<option value="any_plan"><?php esc_html_e( 'Any Plans', 'armember-membership' ); ?></option>
								</select>
							</td>
						</tr>
<!--						<tr>
							<th><?php esc_html_e( 'Enter content here which will be restricted', 'armember-membership' ); ?></th>
							<td>
								<?php
								$armshortcodecontent_editor = array(
									'textarea_name'  => 'armshortcodecontent',
									'media_buttons'  => false,
									'textarea_rows'  => 5,
									'default_editor' => 'html',
									'editor_css'     => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
									'tinymce'        => true,
								);
								wp_editor( '', 'armshortcodecontent', $armshortcodecontent_editor );
								?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'What to display when content is restricted', 'armember-membership' ); ?></th>
							<td>
								<?php
								$armelse_message_editor = array(
									'textarea_name'  => 'armelse_message',
									'media_buttons'  => false,
									'textarea_rows'  => 5,
									'default_editor' => 'html',
									'editor_css'     => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
									'tinymce'        => true,
								);
								wp_editor( '', 'armelse_message', $armelse_message_editor );
								?>
							</td>
						</tr>-->
					</table>
				</div>
				<div class="arm_group_footer arm_padding_0 arm_margin_bottom_0">
					<div class="popup_content_btn_wrapper">
						<a class="arm_cancel_btn popup_close_btn arm_margin_right_12" href="javascript:void(0)"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></a>
						<button type="button" class="arm_shortcode_insert_rc_btn arm_insrt_btn arm_margin_right_0" data-code="arm_restrict_content"><?php esc_html_e( 'Add Shortcode', 'armember-membership' ); ?></button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery(function($){
	if (typeof armICheckInit == "function") {
		armICheckInit();
	}
	/*For Chosen Select Boxes*/
	if (jQuery.isFunction(jQuery().chosen)) {
		jQuery(".arm_chosen_selectbox").chosen({
			no_results_text: "<?php esc_html_e( 'Oops, nothing found', 'armember-membership' ); ?>"
		});
	}
	if (jQuery.isFunction(jQuery().colpick))
	{
		jQuery('.arm_colorpicker').each(function (e) {
			var $arm_colorpicker = jQuery(this);
			var default_color = $arm_colorpicker.val();
			if (default_color == '') {
				default_color = '#000';
			}
			else {
				default_color.replace(' ', '').replace('(', '').replace(')', '').replace('"', '').replace("'", '').replace("/", '').replace("\\", '');
                default_color_length = default_color.length;
                if( default_color_length > 7 )
                {
                    default_color = default_color.substr(0, 7);
                }
            }
			$arm_colorpicker.wrap('<label class="arm_colorpicker_label" style="background-color:' + default_color + '"></label>');
			$arm_colorpicker.colpick({
				layout: 'hex',
				submit: 0,
				colorScheme: 'dark',
				color: default_color,
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					jQuery(el).parent('.arm_colorpicker_label').css('background-color', '#' + hex);
					/*Fill the text box just if the color was set using the picker, and not the colpickSetColor function.*/
					if (!bySetColor) {
						jQuery(el).val('#' + hex);
					}
				}
			});
		});
	}
});
</script>
