<?php
global $wpdb, $wp_roles, $ARMemberLite, $arm_subscription_plans, $arm_global_settings, $arm_payment_gateways, $arm_member_forms, $ARMemberLiteAllowedHTMLTagsArray;
/**
 * Process Submited Form.
 */
$arm_common_date_format = 'm/d/Y';
if($ARMemberLite->is_arm_pro_active)
{
	$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
}
$all_plans         = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type, arm_subscription_plan_options' );
$plan_id           = 0;
$plan_name         = $plan_description = '';
$plan_status       = 1;
$user_roles        = $arm_global_settings->arm_get_all_roles();
$plan_role         = ( $wp_roles->is_role( 'armember' ) ) ? 'armember' : get_option( 'default_role' );
$plan_data         = $plan_options = array();
$subscription_type = 'free';
$expiry_type       = 'joined_date_expiry';

$plan_options = array(
	'access_type'           => 'lifetime',
	'payment_type'          => 'one_time',
	'recurring'             => array( 'type' => 'D' ),
	'trial'                 => array( 'type' => 'D' ),
	'eopa'                  => array( 'type' => 'D' ),
	'pricetext'             => '',
	'expity_type'           => 'joined_date_expiry',
	'expiry_date'           => date( 'Y-m-d 23:59:59' ), //phpcs:ignore
	'upgrade_action'        => 'immediate',
	'downgrade_action'      => 'on_expire',
	'cancel_action'         => 'block',
	'cancel_plan_action'    => 'immediate',
	'eot'                   => 'block',
	'payment_failed_action' => 'block',
);
$form_mode    = esc_html__( 'Add Membership Plan', 'armember-membership' );
$action       = 'add';
$edit_mode    = 0;


?>
<div class="wrap arm_page arm_subscription_plans_add_edit_main_wrapper armPageContainer popup_wrapper">
	
<?php
$all_currencies              = $arm_payment_gateways->arm_get_all_currencies();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$global_currency_sym         = isset( $all_currencies ) ? $all_currencies[ strtoupper( $global_currency ) ] : '';
$global_currency_sym = apply_filters('arm_admin_membership_plan_currency_format', $global_currency_sym, strtoupper($global_currency)); //phpcs:ignore
$global_currency_sym_pos     = $arm_payment_gateways->arm_currency_symbol_position( $global_currency );
$global_currency_sym_pos_pre = ( ! empty( $global_currency_sym_pos ) && $global_currency_sym_pos == 'prefix' ? '' : 'hidden_section' );
$global_currency_sym_pos_suf = ( ! empty( $global_currency_sym_pos ) && $global_currency_sym_pos == 'suffix' ? '' : 'hidden_section' );
$arm_currency_pos_class = ($global_currency_sym_pos == 'suffix') ? 'arm_curr_sym_suff' : 'arm_curr_sym_pref';
	?>
	<div class="content_wrapper arm_subscription_plan_content" id="content_wrapper">
        <div class="popup_header page_title">
            <span class="arm_add_membership_plan_page_title"><?php 
	    	echo esc_html__("Add Membership Plan", 'armember-membership');?></span><span class="arm_edit_membership_plan_page_title"><?php 
	    	echo esc_html__("Edit Membership Plan", 'armember-membership');?></span>
            <span class="arm_popup_close_btn arm_membership_pln_close_btn"></span>
            <?php 	
            $after_title_content = "";
            $after_title_content = apply_filters('arm_after_subscription_plan_page_title', $after_title_content); //phpcs:ignore
            echo $after_title_content; //phpcs:ignore
	    ?>
        </div>

		<div class="armclear"></div>

		<form  method="post" id="arm_add_edit_plan_form" class="arm_add_edit_plan_form arm_admin_form">
			<input type="hidden" name="id" id="arm_add_edit_plan_id" value="<?php echo intval($plan_id); ?>" />
			<input type="hidden" name="arm_action" value="<?php echo esc_attr($action); ?>" />
			<div class="arm_admin_form_content">
				<div class="form-table arm_plan_basic_detail_section">
					<div class="arm_setup_section_title arm_margin_bottom_5"><?php esc_html_e('Basic Details', 'armember-membership'); ?></div>
					<div class="arm_plan_detail_section">
						<table class="form-table">
							<tr class="form-field arm_plan_detail_section_data">
								<th>
									<label class="arm-df__label-text " for="plan_name"><?php esc_html_e( 'Plan name', 'armember-membership' ); ?></label>
								</th>
								<td>
									<div class="arm_form_fields_wrapper">
										<div class="arm_setup_forms_container">
											<div class="arm-df__form-field-wrap_text arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_member_plan">
												<input name="plan_name" type="text" id="arm-df__form-control_member_plan" value="" class="arm-df__form-control arm-df__form-control_member_plan arm_material_input" required="required" data-validation-required-message="<?php esc_html_e( 'Plan name can not be left blank.', 'armember-membership' ); ?>"  placeholder="<?php esc_html_e('Enter Plan Name', 'armember-membership'); ?>"/>
											</div>
											<div class="armclear"></div>
										</div>
									</div>	
									<input type='hidden' name="plan_status" value='<?php echo esc_attr($plan_status); ?>' />
								</td>
							</tr>
							<tr class="form-field arm_plan_detail_section_data arm_plan_desc_section">
								<th>
									<label for="plan_description"><?php esc_html_e('Plan Description', 'armember-membership'); ?></label>
								</th>
								<td>
									<textarea rows="40" cols="40" name="plan_description" id="plan_description"  placeholder="<?php esc_html_e('Enter plan description', 'armember-membership'); ?>" style="height: 80px;"><?php echo stripslashes( $plan_description); //phpcs:ignore?></textarea>
								</td>								
							</tr>
							<tr class="form-field arm_member_role_section arm_plan_detail_section_data">
								<th>
									<?php $role_name = isset( $user_roles[ $plan_role ] ) ? $user_roles[ $plan_role ] : ''; ?>
									<label for="arm_plan_role"><?php esc_html_e('Member Role', 'armember-membership'); ?></label>
								</th>
								<td>
									<div class="arm_form_fields_wrapper">
										<div class="arm_setup_forms_container">
											<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_member_plan_role">
												<input class="arm-selectpicker-input-control" type="text" id="arm_member_plan_role" name="plan_role" value="armember" data-msg-invalid="Please enter valid data.">
												<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
													<dt>
														<span></span>
														<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
													</dt>
													<dd>
														<ul data-id="arm_member_plan_role" style="display:none;">
														<?php if ( ! empty( $user_roles ) ) : ?>
															<?php foreach ( $user_roles as $key => $val ) : ?>
																<li data-label="<?php echo esc_attr($val); ?>" data-value="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($val); ?>"><?php echo esc_html($val); ?></li>
															<?php endforeach; ?>
														<?php endif; ?>
														</ul>
													</dd>
												</dl>
											</div>
										</div>
										<div class="armclear"></div>
									</div>
									<span class="arm-note-message --notice arm_badge_size_field_label arm_margin_top_10"><?php esc_html_e('Change of role is not recommended.', 'armember-membership'); ?></span>
								</td>
							</tr>
							<?php $total_plans = $arm_subscription_plans->arm_get_total_plan_counts(); ?>
							<?php if ( empty( $action ) || $action == 'add' && $total_plans > 0 ) : ?>
							<tr class="form-field arm_inherit_access_rules_section arm_plan_detail_section_data">
								<th>
									<label for="arm_inherit_rules"><?php esc_html_e( 'Inherit Access Rules Of Membership Plan', 'armember-membership' ); ?></label>
								</th>
								<td>
									<div class="arm_form_fields_wrapper">
										<div class="arm_setup_forms_container">
											<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_inherit_rules">
												<input class="arm-selectpicker-input-control" type="text" id="arm_inherit_rules" name="arm_inherit_plan_rules" value="" data-msg-invalid="Please enter valid data.">
												<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
													<dt>
														<span></span>
														<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
													</dt>
													<dd>
														<ul data-id="arm_inherit_rules" style="display:none;">
															<li data-label="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
															<?php
															if ( ! empty( $all_plans ) ) {
																foreach ( $all_plans as $p ) {
																	$p_id = $p['arm_subscription_plan_id'];
																	if ( $p_id != $plan_id && $p['arm_subscription_plan_status'] == '1' ) {
																		?><li data-label="<?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); //phpcs:ignore?>" data-value="<?php echo $p_id ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name'])); //phpcs:ignore?></li><?php
																	}
																}
															}
															?>
														</ul>
													</dd>
												</dl>
											</div>
										</div>
										<div class="armclear"></div>
									</div>
								</td>
							</tr>
							<?php endif; ?>						
						</table>
					</div>
				</div>
                <div class="arm_solid_divider"></div>
                <div class="arm_plan_price_section">
                    <div class="arm_setup_section_title arm_margin_top_32 arm_margin_bottom_10"><?php esc_html_e('Plan Type', 'armember-membership'); ?></div>
					<div id="arm_plan_price_box_content" class="arm_plan_price_box">
						<div class="page_sub_content">
							<table class="form-table arm_plan_price_box">
								<tr class="form-field form-required arm_plan_price_type">
									<th class="arm_padding_bottom_0"><label><?php esc_html_e( 'Setup Type', 'armember-membership' ); ?></label></th>
									<td class="arm_padding_top_20">
										<div class="arm_form_fields_wrapper">
											<div class="arm-df__form-field-wrap_radio arm-df__form-field-wrap arm-controls arm-d-flex" id="arm-df__form-field-wrap_arm_plan_type">
												<div class="arm_sub_plan_selection">
													<input type="radio" class="arm_iradio" <?php checked( $subscription_type, 'free' ); ?> value="free" name="arm_subscription_plan_type" id="subscription_type_free" />
													<label for="subscription_type_free"><?php esc_html_e( 'Free Plan', 'armember-membership' ); ?></label>
												</div>
												<div class="arm_sub_plan_selection">
													<input type="radio" class="arm_iradio" <?php checked( $subscription_type, 'paid_infinite' ); ?> value="paid_infinite" name="arm_subscription_plan_type" id="subscription_type_paid" />
													<label for="subscription_type_paid"><?php esc_html_e( 'Paid Plan (infinite)', 'armember-membership' ); ?></label>
												</div>
												<div class="arm_sub_plan_selection">
													<input type="radio" class="arm_iradio" <?php checked( $subscription_type, 'paid_finite' ); ?> value="paid_finite" name="arm_subscription_plan_type" id="subscription_finite_type_paid" />
													<label for="subscription_finite_type_paid"><?php esc_html_e( 'Paid Plan (finite)', 'armember-membership' ); ?></label>
												</div>
												<div class="arm_sub_plan_selection">
													<input type="radio" class="arm_iradio" <?php checked( $subscription_type, 'recurring' ); ?> value="recurring" name='arm_subscription_plan_type' id="subscription_recurring_type" />
													<label for="subscription_recurring_type"><?php esc_html_e( 'Subscription / Recurring Payment', 'armember-membership' ); ?></label>
												</div>
												<input type="hidden" value="<?php echo esc_attr( sanitize_text_field($plan_options['access_type']) ); ?>" name="arm_subscription_plan_options[access_type]" id="arm_subscription_plan_access_type" />
												<input type="hidden" value="<?php echo esc_attr( sanitize_text_field($plan_options['payment_type']) ); ?>" name="arm_subscription_plan_options[payment_type]" id="arm_subscription_plan_payment_type" />
											</div>
											<div class="armclear"></div>
										</div>
									</td>
								</tr>							
								<?php
									
									$arm_subscription_plans_class = '';
									if($subscription_type == 'paid_finite')
									{
										$arm_subscription_plans_class = 'paid_subscription_options_finite';
									}
								?>
								
								<tr class="form-field paid_subscription_options arm_sub_plan_section <?php echo $arm_subscription_plans_class;?>  <?php echo ( ! in_array( $subscription_type, array( 'free', 'recurring' ) ) ) ? '' : 'hidden_section'; ?>">
									<th><label><?php esc_html_e( 'Plan Amount', 'armember-membership' ); ?></label></th>   
									<td class='arm_padding_top_0'>
										<div class="arm_setup_forms_container">
											<div class="arm-df__form-field-wrap_text arm-df__form-field-wrap arm-controls arm_display_flex_wrap" id="arm-df__form-field-wrap_member_plan_amount">
												<span class="arm_plan_currency_symbol arm_plan_currency_symbol_prefix arm_margin_top_0 <?php echo $global_currency_sym_pos_pre;?>"><?php echo $global_currency_sym; ?></span>
												<input name="arm_subscription_plan_amount" type="text" id="arm-df__form-control_member_plan_amount arm_subscription_plan_amount" value=""  class="arm-df__form-control arm-df__form-control_member_plan arm_material_input arm_no_paste <?php echo $arm_currency_pos_class?>" required="required" data-validation-required-message="<?php esc_html_e( 'Plan amount can not be left blank.', 'armember-membership' ); ?>" onkeypress="javascript:return ArmNumberValidation(event, this)"/>
												<span class="arm_plan_currency_symbol arm_plan_currency_symbol_suffix <?php echo $global_currency_sym_pos_suf;?>"><?php echo $global_currency_sym; ?></span>
											</div>
											<div class="armclear"></div>
										</div>
									</td>
								</tr>
								<?php 
									$arm_add_plan_amount_html_for_paid_plan = '';
									$plan_type = 'paid_plan';
									$arm_payment_cycle_num = '';
									echo apply_filters('arm_admin_membership_plan_html_after_amount', $arm_add_plan_amount_html_for_paid_plan, $plan_data, $plan_type, $arm_payment_cycle_num); //phpcs:ignore
								?>
								<tr class="form-field paid_subscription_options_finite arm_plan_finite_duration_section <?php echo ( $subscription_type == 'paid_finite' ) ? '' : 'hidden_section'; ?>">
									<th>
										<label><?php esc_html_e('Plan Duration','armember-membership')?></label>
									</th>
									<td>
										<div class="arm_paid_finite_expiry_based_on_joined_date" id="arm_paid_finite_expiry_based_on_joined_date">
											<div class="arm_expiry_joined_date_radio" id="arm_expiry_joined_date_radio">
												<input type="radio" class="arm_iradio" <?php checked( $expiry_type, 'joined_date_expiry' ); ?> value="joined_date_expiry" name="arm_subscription_plan_options[expiry_type]" id="arm_plan_finite_expiry_based_joined_date" />
												<label for="arm_plan_finite_expiry_based_joined_date" class="arm_padding_0 arm_padding_right_0"><?php esc_html_e( 'Based On Plan Assigned Date', 'armember-membership' ); ?><i class="arm_helptip_icon armfa armfa-question-circle" title='<?php esc_html_e( 'User will be expired after certain amount of time based on plan assigned date. For example, after one year of joined, after 5 months of joined and like wise.', 'armember-membership' ); ?>'></i></label>
												
											</div>
											<div class="arm_expiry_joined_date_box" id="arm_expiry_joined_date_box">
												<div id="arm_eopa_D" class="arm_eopa_select" style="<?php echo ( isset( $plan_options['eopa']['type'] ) && ( $plan_options['eopa']['type'] != 'D' || $plan_options['eopa']['type'] == '' ) ) ? 'display:none;' : ''; ?>">
													<div class="arm_form_fields_wrapper">
														<div class="arm_setup_forms_container">
															<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_eopa_days">
																<input class="arm-selectpicker-input-control" type="text" id="arm_eopa_days" name="arm_subscription_plan_options[eopa][days]" value="<?php echo ( ! empty( $plan_options['eopa']['days'] ) ) ? esc_attr( sanitize_text_field($plan_options['eopa']['days']) ) : 1; ?>">
																<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
																	<dt>
																		<span></span>
																		<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
																	</dt>
																	<dd>
																		<ul data-id="arm_eopa_days" style="display:none;">
																		<?php for ( $i = 1; $i <= 90; $i++ ) { ?>
																			<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																		<?php } ?>
																		</ul>
																	</dd>
																</dl>
															</div>
														</div>
														<div class="armclear"></div>
													</div>
													
												</div>
												<div id="arm_eopa_W" class="arm_eopa_select" style="<?php echo ( isset( $plan_options['eopa']['type'] ) && $plan_options['eopa']['type'] != 'W' ) ? 'display:none;' : ''; ?>">
													<div class="arm_form_fields_wrapper">
														<div class="arm_setup_forms_container">
															<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_eopa_weeks">
																<input class="arm-selectpicker-input-control" type="text" id="arm_eopa_weeks" name="arm_subscription_plan_options[eopa][weeks]" value="<?php echo ( ! empty( $plan_options['eopa']['weeks'] ) ) ? esc_attr( sanitize_text_field($plan_options['eopa']['weeks']) ) : 1; ?>">
																<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
																	<dt>
																		<span></span>
																		<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
																	</dt>
																	<dd>
																		<ul data-id="arm_eopa_weeks" style="display:none;">
																		<?php for ( $i = 1; $i <= 90; $i++ ) { ?>
																			<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																		<?php } ?>
																		</ul>
																	</dd>
																</dl>
															</div>
														</div>
														<div class="armclear"></div>
													</div>
												</div>
												<div id="arm_eopa_M" class="arm_eopa_select" style="<?php echo ( isset( $plan_options['eopa']['type'] ) && $plan_options['eopa']['type'] != 'M' ) ? 'display:none;' : ''; ?>">
													<div class="arm_form_fields_wrapper">
														<div class="arm_setup_forms_container">
															<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_eopa_months">
																<input class="arm-selectpicker-input-control" type="text" id="arm_eopa_months" name="arm_subscription_plan_options[eopa][months]" value="<?php echo ( ! empty( $plan_options['eopa']['months'] ) ) ? esc_attr( sanitize_text_field($plan_options['eopa']['months']) ) : 1; ?>">
																<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
																	<dt>
																		<span></span>
																		<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
																	</dt>
																	<dd>
																		<ul data-id="arm_eopa_months" style="display:none;">
																		<?php for ( $i = 1; $i <= 90; $i++ ) { ?>
																			<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																		<?php } ?>
																		</ul>
																	</dd>
																</dl>
															</div>
														</div>
														<div class="armclear"></div>
													</div>
												</div>
												<div id="arm_eopa_Y" class="arm_eopa_select" style="<?php echo ( isset( $plan_options['eopa']['type'] ) && $plan_options['eopa']['type'] != 'Y' ) ? 'display:none;' : ''; ?>">
													<div class="arm_form_fields_wrapper">
														<div class="arm_setup_forms_container">
															<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_eopa_years">
																<input class="arm-selectpicker-input-control" type="text" id="arm_eopa_years" name="arm_subscription_plan_options[eopa][years]" value="<?php echo ( ! empty( $plan_options['eopa']['months'] ) ) ? esc_attr( sanitize_text_field($plan_options['eopa']['years']) ) : 1; ?>">
																<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
																	<dt>
																		<span></span>
																		<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
																	</dt>
																	<dd>
																		<ul data-id="arm_eopa_years" style="display:none;">
																		<?php for ( $i = 1; $i <= 90; $i++ ) { ?>
																			<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																		<?php } ?>
																		</ul>
																	</dd>
																</dl>
															</div>
														</div>
														<div class="armclear"></div>
													</div>
												</div>
												<div id="arm_eopa_type_main" class="arm_eopa_type_main" >
													<div class="arm_form_fields_wrapper">
														<div class="arm_setup_forms_container">
															<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_eopa_type">
																<input class="arm-selectpicker-input-control" type="text" id="arm_eopa_type" name="arm_subscription_plan_options[eopa][type]" value="<?php echo ( ! empty( $plan_options['eopa']['type'] ) ) ? esc_attr( sanitize_text_field($plan_options['eopa']['type']) ) : 'D'; ?>" onChange="arm_subscription_plan_duration_select();" />
																<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
																	<dt>
																		<span></span>
																		<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
																	</dt>
																	<dd>
																		<ul data-id="arm_eopa_type" style="display:none;">
																			<li data-label="<?php esc_attr_e( 'Day(s)', 'armember-membership' ); ?>" data-value="D"><?php esc_html_e( 'Day(s)', 'armember-membership' ); ?></li>
																			<li data-label="<?php esc_attr_e( 'Week(s)', 'armember-membership' ); ?>" data-value="W"><?php esc_html_e( 'Week(s)', 'armember-membership' ); ?></li>
																			<li data-label="<?php esc_attr_e( 'Month(s)', 'armember-membership' ); ?>" data-value="M"><?php esc_html_e( 'Month(s)', 'armember-membership' ); ?></li>
																			<li data-label="<?php esc_attr_e( 'Year(s)', 'armember-membership' ); ?>" data-value="Y"><?php esc_html_e( 'Year(s)', 'armember-membership' ); ?></li>
																		</ul>
																	</dd>
																</dl>
															</div>
														</div>
														<div class="armclear"></div>
													</div>
												</div>
											</div>
										</div>
										
										<div class="arm_paid_finite_fixed_expiry_date" id="arm_paid_finite_fixed_expiry_date">
											<div class="arm_expiry_fix_date_radio" id="arm_expiry_fix_date_radio">
												<input type="radio" class="arm_iradio" <?php checked( $expiry_type, 'fixed_date_expiry' ); ?> value="fixed_date_expiry" name="arm_subscription_plan_options[expiry_type]" id="arm_plan_finite_expiry_fix_date" />
												<label for="arm_plan_finite_expiry_fix_date" class="arm_padding_0"><?php esc_html_e( 'Fix Expiration Date', 'armember-membership' ); ?><i class="arm_helptip_icon armfa armfa-question-circle" title='<?php esc_html_e( 'User will be expired after the certain date selected here. No matter when he joined. For example if date is set 31 Dec, 2017 then all users having this plan will be expired on that date no matter when he registered.', 'armember-membership' ); ?>'></i></label>
												
											</div>
											<div class="arm_expiry_fix_date_box arm_position_relative" id="arm_expiry_fix_date_box" >
												<input type="hidden" name="wordpress_date_format" id="arm_finite_plan_expiry_format" value="<?php echo get_option( 'date_format' ); //phpcs:ignore ?>">
												<input type="text" id="arm_finite_plan_expiry_date" value="<?php echo ( ( isset( $plan_options['expiry_date'] ) && ! empty( $plan_options['expiry_date'] ) ) ? esc_attr( date( $arm_common_date_format, strtotime( $plan_options['expiry_date']) ) ) : '' ); //phpcs:ignore ?>" data-date_format="<?php echo $arm_common_date_format?>" name="arm_subscription_plan_options[expiry_date]" class="arm_finite_plan_expiry_date" data-editmode="<?php echo ( $edit_mode ) ? '1' : '0'; ?>" data-msg-required="<?php esc_attr_e( 'Please select expiry date.', 'armember-membership' ); ?>"/>
												<span class="arm_plan_currency_symbol arm_plan_currency_symbol_suffix"><img src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/date_icon.svg"/></span>
											</div>
										</div>
										
									</td>
								</tr>
								
								<tr class="form-field paid_subscription_options_recurring_payment_cycles_main_box_tr <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<th><label class="arm_label_fields arm_font_size_16 arm_font_weight_500 arm_plan_payment_cyle_lbl"><?php esc_html_e( 'Payment Cycle', 'armember-membership' ); ?></label></th>
									<td>
										<div class="paid_subscription_options_recurring_payment_cycles_main_box_label">
											<div class="arm_display_grid arm_payment_cycle_grid">
												<label class="arm_plan_payment_cycle_label_text arm_padding_right_8"><?php esc_html_e( 'Label', 'armember-membership' ); ?></label>
												<label class="arm_plan_payment_cycle_amount_text arm_plan_payment_cycle_label_text"><?php esc_html_e( 'Amount', 'armember-membership' ); ?></label>
											</div>
											<label class="arm_plan_payment_cycle_billing_text arm_plan_payment_cycle_label_text"><?php esc_html_e( 'Billing Cycle', 'armember-membership' ); ?></label>
											<label class="arm_plan_payment_cycle_recurring_text arm_plan_payment_cycle_label_text"><?php esc_html_e( 'Recurring Time', 'armember-membership' ); ?></label>
											<label class="arm_plan_payment_cycle_recurring_text arm_plan_payment_cycle_label_text"></label>
										</div>
										<div class="paid_subscription_options_recurring_payment_cycles_main_box">
										<ul class="arm_plan_payment_cycle_ul" >
										<?php
										$plan_options['payment_cycles'] = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();

										if ( $edit_mode == '1' ) {
											if ( empty( $plan_options['payment_cycles'] ) ) {

												$plan_amount    = ! empty( $plan_data['arm_subscription_plan_amount'] ) ? sanitize_text_field($plan_data['arm_subscription_plan_amount']) : 0;
												$recurring_time = isset( $plan_options['recurring']['time'] ) ? sanitize_text_field($plan_options['recurring']['time']) : 'infinite';
												$recurring_type = isset( $plan_options['recurring']['type'] ) ? sanitize_text_field($plan_options['recurring']['type']) : 'D';
												switch ( $recurring_type ) {
													case 'D':
														$billing_cycle = isset( $plan_options['recurring']['days'] ) ? sanitize_text_field($plan_options['recurring']['days']) : '1';
														break;
													case 'M':
														$billing_cycle = isset( $plan_options['recurring']['months'] ) ? sanitize_text_field($plan_options['recurring']['months']) : '1';
														break;
													case 'Y':
														$billing_cycle = isset( $plan_options['recurring']['years'] ) ? sanitize_text_field($plan_options['recurring']['years']) : '1';
														break;
													default:
														$billing_cycle = '1';
														break;
												}
												$plan_options['payment_cycles'] = array(
													array(
														'cycle_key'    => 'arm0',
														'cycle_label'  => $plan->plan_text( false, false ),
														'cycle_amount' => $plan_amount,
														'billing_cycle' => $billing_cycle,
														'billing_type' => $recurring_type,
														'recurring_time' => $recurring_time,
														'payment_cycle_order' => 1,
													),
												);

											}
										}
										if ( ! empty( $plan_options['payment_cycles'] ) && $ARMemberLite->is_arm_pro_active) {
											$total_inirecurring_cycle = count( $plan_options['payment_cycles'] );
											$gi                       = 1;
											foreach ( $plan_options['payment_cycles'] as $arm_pc => $arm_value ) {
												?>
													<li class="arm_plan_payment_cycle_li paid_subscription_options_recurring_payment_cycles_child_box" id="paid_subscription_options_recurring_payment_cycles_child_box<?php echo esc_html($arm_pc); ?>">
													
														<div>
															<div class="arm_plan_payment_cycle_label">
																<div class="arm_plan_payment_cycle_label_input arm_padding_top_12 arm_padding_bottom_10">
																<input type="hidden" name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][cycle_key]" value="<?php echo ( ! empty( $arm_value['cycle_key'] ) ) ? esc_attr(stripslashes($arm_value['cycle_key'])) : 'arm' . rand(); //phpcs:ignore ?>"/>
																<input type="text" name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][cycle_label]" value="<?php echo ( ! empty( $arm_value['cycle_label'] ) ) ? esc_attr(stripslashes($arm_value['cycle_label'])) : ''; ?>" class="paid_subscription_options_recurring_payment_cycle_label" data-msg-required="<?php esc_html_e( 'Label should not be blank.', 'armember-membership' ); ?>"/>
																</div>
															</div>
	
	
															<div class="arm_plan_payment_cycle_amount">
																
																<div class="arm_plan_payment_cycle_amount_input arm_padding_top_12 arm_padding_bottom_10">
																	<span class="arm_plan_currency_symbol arm_plan_currency_symbol_prefix <?php echo $global_currency_sym_pos_pre?>"><?php echo $global_currency_sym; ?></span>
																	<input type="text" name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][cycle_amount]" value="<?php echo ( isset( $arm_value['cycle_amount'] ) ) ? esc_attr($arm_value['cycle_amount']) : ''; ?>" class="paid_subscription_options_recurring_payment_cycle_amount <?php echo $arm_currency_pos_class; ?>" data-msg-required="<?php esc_html_e( 'Amount should not be blank.', 'armember-membership' ); ?>" onkeypress="javascript:return ArmNumberValidation(event, this)" />
																	<span class="arm_plan_currency_symbol arm_plan_currency_symbol_suffix <?php echo $global_currency_sym_pos_suf?>"><?php echo $global_currency_sym; ?></span>
																</div>
															</div>
														</div>
														
														<?php 
															$arm_add_plan_amount_html_for_plan = '';
															$plan_type = 'recurring';
															echo apply_filters('arm_admin_membership_plan_html_after_amount', $arm_add_plan_amount_html_for_plan, $plan_data, $plan_type, $arm_pc); //phpcs:ignore
														?>
														<div class="arm_plan_payment_cycle_billing_cycle arm_width_23_pct">
															
														<div class="arm_plan_payment_cycle_billing_input arm_padding_top_12 arm_padding_bottom_10">
															<input type='hidden' id='arm_ipc_billing<?php echo esc_attr($arm_pc); ?>' name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][billing_cycle]" value='<?php echo ( ! empty( $arm_value['billing_cycle'] ) ) ? esc_attr($arm_value['billing_cycle']) : 1; ?>' />
																	<dl class="arm_selectbox column_level_dd arm_margin_0 arm_min_width_50">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_ipc_billing<?php echo esc_attr($arm_pc); ?>">
																			<?php for ( $i = 1; $i <= 90; $i++ ) { ?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																				<?php } ?>
																			</ul>
																		</dd>
																	</dl>

																	<input type='hidden' id='arm_ipc_billing_type<?php echo esc_attr($arm_pc); ?>' name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][billing_type]" value='<?php echo ( ! empty( $arm_value['billing_type'] ) ) ? esc_attr($arm_value['billing_type']) : 'D'; ?>' />
																	<dl class="arm_selectbox column_level_dd arm_margin_0 arm_width_120 arm_min_width_120" >
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_ipc_billing_type<?php echo esc_attr($arm_pc); ?>">

																					<li data-label="<?php esc_attr_e( 'Day(s)', 'armember-membership' ); ?>" data-value="D"><?php esc_attr_e( 'Day(s)', 'armember-membership' ); ?></li>
																					<li data-label="<?php esc_attr_e( 'Month(s)', 'armember-membership' ); ?>" data-value="M"><?php esc_attr_e( 'Month(s)', 'armember-membership' ); ?></li>
																					<li data-label="<?php esc_attr_e( 'Year(s)', 'armember-membership' ); ?>" data-value="Y"><?php esc_attr_e( 'Year(s)', 'armember-membership' ); ?></li>

																			</ul>
																		</dd>
																	</dl>
															</div>
														</div>
														<div class="arm_plan_payment_cycle_recurring_time">
															<input type='hidden' id='arm_ipc_recurring<?php echo esc_attr($arm_pc); ?>' name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][recurring_time]" value='<?php echo ( ! empty( $arm_value['recurring_time'] ) ) ? esc_attr($arm_value['recurring_time']) : 'infinite'; ?>' />
																	<dl class="arm_selectbox column_level_dd arm_width_100 arm_min_width_100 arm_padding_top_12">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_ipc_recurring<?php echo esc_attr($arm_pc); ?>">
																				<li data-label="<?php esc_attr_e( 'Infinite', 'armember-membership' ); ?>" data-value="infinite"><?php esc_attr_e( 'Infinite', 'armember-membership' ); ?></li>
																			<?php for ( $i = 2; $i <= 30; $i++ ) { ?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																				<?php } ?>
																			</ul>
																		</dd>
																	</dl>
															</div>

													<?php
													$arm_plan_cycle_action_btns = '';
													echo apply_filters('arm_plan_cycle_action_btns',$arm_plan_cycle_action_btns,$total_inirecurring_cycle); //phpcs:ignore
													?>
														<input type="hidden" name="arm_subscription_plan_options[payment_cycles][<?php echo esc_attr($arm_pc); ?>][payment_cycle_order]" value="<?php echo esc_attr($gi); ?>" class="arm_module_payment_cycle_order">
												</li>
												<?php												
												$gi++;
											}
										} else {
											$total_inirecurring_cycle = count( $plan_options['payment_cycles'] );
											?>
												<li class="arm_plan_payment_cycle_li paid_subscription_options_recurring_payment_cycles_child_box" id="paid_subscription_options_recurring_payment_cycles_child_box0">
													<div class="arm_display_grid arm_payment_cycle_grid">
														<div class="arm_plan_payment_cycle_label">
															
															<div class="arm_plan_payment_cycle_label_input arm_padding_top_12">
																<input type="hidden" name="arm_subscription_plan_options[payment_cycles][0][cycle_key]" value="<?php echo 'arm0'; ?>"/>
																<input type="text" name="arm_subscription_plan_options[payment_cycles][0][cycle_label]" value="" class="paid_subscription_options_recurring_payment_cycle_label" data-msg-required="<?php esc_attr_e( 'Label should not be blank.', 'armember-membership' ); ?>"/>
															</div>
														</div>
														<div class="arm_plan_payment_cycle_amount">
		
															<div class="arm_plan_payment_cycle_amount_input arm_padding_top_12">
																<span class="arm_plan_currency_symbol arm_plan_currency_symbol_prefix <?php echo $global_currency_sym_pos_pre?>"><?php echo $global_currency_sym; ?></span>
																<input type="text" name="arm_subscription_plan_options[payment_cycles][0][cycle_amount]" value="" class="paid_subscription_options_recurring_payment_cycle_amount <?php echo $arm_currency_pos_class;?>" data-msg-required="<?php esc_html_e( 'Amount should not be blank.', 'armember-membership' ); ?>" onkeypress="javascript:return ArmNumberValidation(event, this)" />
																<span class="arm_plan_currency_symbol arm_plan_currency_symbol_suffix <?php echo $global_currency_sym_pos_suf?>"><?php echo $global_currency_sym; ?></span>
															</div>
														</div>
													</div>
														<?php 
															$arm_add_plan_amount_html_for_plan = '';
															$plan_type = 'recurring';
															$arm_payment_cycle_num = 0;
															echo apply_filters('arm_admin_membership_plan_html_after_amount', $arm_add_plan_amount_html_for_plan, $plan_data, $plan_type, $arm_payment_cycle_num); //phpcs:ignore
														?>

													<div class="arm_plan_payment_cycle_billing_cycle">
														<div class="arm_plan_payment_cycle_billing_input arm_display_grid">
													
															<div class="arm_form_fields_wrapper  arm_plan_payment_cycle_billing_input arm_padding_top_12">
																<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls" id="arm-df__form-field-wrap_arm_ipc_billing0">
																	<input type='text' class="arm-selectpicker-input-control" id='arm_ipc_billing0' name="arm_subscription_plan_options[payment_cycles][0][billing_cycle]" value='<?php echo ( ! empty( $arm_value['billing_cycle'] ) ) ? esc_attr($arm_value['billing_cycle']) : 1; ?>' />
																	<dl class="arm_selectbox column_level_dd arm_margin_0 arm_min_width_50 arm_width_100_pct" >
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_ipc_billing0">
																			<?php for ( $i = 1; $i <= 90; $i++ ) { ?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																				<?php } ?>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</div>
															<div class="arm_form_fields_wrapper  arm_plan_payment_cycle_billing_input arm_padding_top_12">

																<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_ipc_billing_type0">
																	<input type='text' class="arm-selectpicker-input-control" id='arm_ipc_billing_type0' name="arm_subscription_plan_options[payment_cycles][0][billing_type]" value='<?php echo ( ! empty( $arm_value['billing_type'] ) ) ? esc_attr($arm_value['billing_type']) : 'D'; ?>' />
																	<dl class="arm_selectbox column_level_dd arm_margin_0 arm_width_100_pct" >
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_ipc_billing_type0">

																				<li data-label="<?php esc_attr_e( 'Day(s)', 'armember-membership' ); ?>" data-value="D"><?php esc_attr_e( 'Day(s)', 'armember-membership' ); ?></li>
																				<li data-label="<?php esc_attr_e( 'Month(s)', 'armember-membership' ); ?>" data-value="M"><?php esc_attr_e( 'Month(s)', 'armember-membership' ); ?></li>
																				<li data-label="<?php esc_attr_e( 'Year(s)', 'armember-membership' ); ?>" data-value="Y"><?php esc_attr_e( 'Year(s)', 'armember-membership' ); ?></li>

																			</ul>
																		</dd>
																	</dl>
																</div>
															</div>
														</div>
													</div>

													<div class="arm_plan_payment_cycle_recurring_time">
														<div class="arm_form_fields_wrapper arm_padding_top_12">
															<div class="arm_setup_forms_container">
																<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_ipc_recurring0">
																	<input class="arm-selectpicker-input-control" type="text" id="arm_ipc_recurring0" name="arm_subscription_plan_options[payment_cycles][0][recurring_time]" value="<?php echo ( ! empty( $arm_value['recurring_time'] ) ) ? esc_attr( sanitize_text_field($arm_value['recurring_time']) ) : 'infinite'; ?>"/>
																	
																	<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_0">
																		<dt>
																			<span></span>
																			<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
																		</dt>
																		<dd>
																			<ul data-id="arm_ipc_recurring0" style="display:none;">
																			<li data-label="<?php esc_html_e( 'Infinite', 'armember-membership' ); ?>" data-value="infinite"><?php esc_html_e( 'Infinite', 'armember-membership' ); ?></li>
																			<?php for ( $i = 2; $i <= 30; $i++ ) { ?>
																				<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																			<?php } ?>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</div>
															<div class="armclear"></div>
														</div>
														
													</div>

													<?php
													$arm_plan_cycle_action_btns = '';
													echo apply_filters('arm_plan_cycle_action_btns',$arm_plan_cycle_action_btns,$total_inirecurring_cycle); //phpcs:ignore
													?>

	
														<input type="hidden" name="arm_subscription_plan_options[payment_cycles][0][payment_cycle_order]" value="1" class="arm_module_payment_cycle_order">
												</li>
												<?php
										}
										?>
										</ul>
										<div class="paid_subscription_options_recurring_payment_cycles_link">
												<input type="hidden" name="arm_total_recurring_plan_cycles" id="arm_total_recurring_plan_cycles_order" value="2"/>
												<input type="hidden" name="arm_total_recurring_plan_cycles" id="arm_total_recurring_plan_cycles" value="1"/>
												<input type="hidden" name="arm_total_recurring_plan_cycles_counter" id="arm_total_recurring_plan_cycles_counter" value="1" />
											</div>
										</div>
									</td>
								</tr>							
								<tr class="form-field paid_subscription_options_recurring arm_paid_subscription_trial_period <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<th style='padding:0px'><label class="arm_label_fields arm_font_size_20 arm_font_weight_500 arm_black_600"><?php esc_html_e( 'Trial Period', 'armember-membership' ); ?></label></th>
									<td>
										<?php $is_trial_period = ( isset( $plan_options['trial']['is_trial_period'] ) ) ? $plan_options['trial']['is_trial_period'] : 0; ?>
										<div class="arm_trial_period_switch arm_padding_top_10">
											<div class="armswitch arm_global_setting_switch">
												<input type="checkbox" id="trial_period" name="arm_subscription_plan_options[trial][is_trial_period]" value="1" class="armswitch_input trial_period_chk" onclick="arm_hide_show_trial_options(this);" <?php checked( $is_trial_period, '1' ); ?> />
												<label for="trial_period" class="armswitch_label arm_min_width_40" ></label>
												
											</div>
											<label for="trial_period" class="arm-black-600 arm_line_height_normal arm_padding_left_10"><?php esc_html_e( 'Trial Period', 'armember-membership' ); ?></label>
										</div>
									</td>
								</tr>
								<tr class="form-field trial_period_options arm_trial_period_amount <?php echo ( $subscription_type == 'recurring' && $is_trial_period == '1' ) ? '' : 'hidden_section'; ?>">
									<th class="arm_padding_0"><label><?php esc_html_e( 'Amount', 'armember-membership' ); ?></label></th>
									<td>
										<div class="arm_form_fields_wrapper arm_margin_bottom_10">
											<div class="arm_setup_forms_container">
												<div class="arm-df__form-field-wrap_text arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_member_plan">
													<span class="arm_plan_currency_symbol arm_plan_currency_symbol_prefix <?php echo $global_currency_sym_pos_pre;?>"><?php echo $global_currency_sym; ?></span>
													<input name="arm_subscription_plan_options[trial][amount]" type="text" id="arm-df__form-control_member_trial_amt" value="<?php echo ( ! empty( $plan_options['trial']['amount'] ) ) ? floatval($plan_options['trial']['amount']) : 0; ?>" class="arm-df__form-control arm-df__form-control_member_trial_amt arm_material_input <?php echo $arm_currency_pos_class;?>">
													<span class="arm_plan_currency_symbol arm_plan_currency_symbol_suffix <?php echo $global_currency_sym_pos_suf;?>"><?php echo $global_currency_sym; ?></span>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
									</td>
								</tr>
								<tr class="form-field trial_period_options arm_trial_period_duration <?php echo ( $subscription_type == 'recurring' && $is_trial_period == '1' ) ? '' : 'hidden_section'; ?>">
									<th class="arm_padding_0"><label><?php esc_html_e( 'Duration', 'armember-membership' ); ?></label></th>
									<td>
										<div id="arm_plan_trial_recurring_days_main" class="arm_trial_select" style="<?php echo ( isset( $plan_options['trial']['type'] ) && ( $plan_options['trial']['type'] != 'D' || $plan_options['trial']['type'] == '' ) ) ? 'display:none;' : ''; ?>">
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_trial_days">
														<input class="arm-selectpicker-input-control" type="text" id="arm_trial_days" name="arm_subscription_plan_options[trial][days]" value="<?php echo ( ! empty( $plan_options['trial']['days'] ) ) ? esc_attr( sanitize_text_field($plan_options['trial']['days']) ) : 1; ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_0 arm_width_80">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_trial_days" style="display:none;">
																	<?php for ( $i = 1; $i <= 365; $i++ ) { ?>
																	<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																<?php } ?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
										<div id="arm_plan_trial_recurring_months_main" class="arm_trial_select" style="<?php echo ( isset( $plan_options['trial']['type'] ) && $plan_options['trial']['type'] != 'M' ) ? 'display:none;' : ''; ?>">
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_trial_months">
														<input class="arm-selectpicker-input-control" type="text" id="arm_trial_months" name="arm_subscription_plan_options[trial][months]" value="<?php echo ( ! empty( $plan_options['trial']['months'] ) ) ? esc_attr( sanitize_text_field($plan_options['trial']['months']) ) : 1; ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_0 arm_width_120">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_trial_months" style="display:none;">
																	<?php for ( $i = 1; $i <= 365; $i++ ) { ?>
																	<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																<?php } ?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
										<div id="arm_plan_trial_recurring_years_main" class="arm_trial_select" style="<?php echo ( isset( $plan_options['trial']['type'] ) && $plan_options['trial']['type'] != 'Y' ) ? 'display:none;' : ''; ?>">
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_trial_years">
														<input class="arm-selectpicker-input-control" type="text" id="arm_trial_years" name="arm_subscription_plan_options[trial][years]" value="<?php echo ( ! empty( $plan_options['trial']['years'] ) ) ? esc_attr( sanitize_text_field($plan_options['trial']['years']) ) : 1; ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_0 arm_width_120">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_trial_years" style="display:none;">
																	<?php for ( $i = 1; $i <= 365; $i++ ) { ?>
																	<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																<?php } ?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
										<div id="arm_plan_recurring_type_main" class="arm_plan_recurring_type_main" >
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_plan_trial_recurring_type">
														<input class="arm-selectpicker-input-control" type="text" id="arm_plan_trial_recurring_type" name="arm_subscription_plan_options[trial][type]" value="<?php echo  sanitize_text_field($plan_options['trial']['type']);?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_0 arm_width_120">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_plan_trial_recurring_type" style="display:none;">
																	<li data-label="<?php esc_attr_e( 'Day(s)', 'armember-membership' ); ?>" data-value="D"><?php esc_html_e( 'Day(s)', 'armember-membership' ); ?></li>
																	<li data-label="<?php esc_attr_e( 'Month(s)', 'armember-membership' ); ?>" data-value="M"><?php esc_html_e( 'Month(s)', 'armember-membership' ); ?></li>
																	<li data-label="<?php esc_attr_e( 'Year(s)', 'armember-membership' ); ?>" data-value="Y"><?php esc_html_e( 'Year(s)', 'armember-membership' ); ?></li>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
									</td>
								</tr>
								<tr class="form-field arm_subscription_payment_mode <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<th class="arm_padding_top_0"><label><?php esc_html_e( 'Billing Cycle Starts From', 'armember-membership' ); ?></label></th>
									<td class="arm_padding_top_0">
										<div>
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_manual_subscription_start_from">
														<input class="arm-selectpicker-input-control" type="text" id="arm_manual_subscription_start_from" name="arm_subscription_plan_options[recurring][manual_billing_start]" value="<?php echo ! empty( $plan_options['recurring']['manual_billing_start'] ) ? esc_attr( sanitize_text_field($plan_options['recurring']['manual_billing_start']) ) : 'transaction_day'; ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_manual_subscription_start_from" style="display:none;">
																	<li data-label="<?php echo esc_html__( 'From Transaction Day', 'armember-membership' ); ?>" data-value="transaction_day"><?php echo esc_html__( 'From Transaction Day', 'armember-membership' ); ?></li>
																	<?php for ( $i = 1; $i <= 31; $i++ ) { ?>
																		<?php
																		$dprefix = 'th';
																		if ( in_array( $i, array( 1, 21, 31 ) ) ) {
																			$dprefix = 'st';
																		}
																		if ( in_array( $i, array( 2, 22 ) ) ) {
																			$dprefix = 'nd';
																		}
																		if ( in_array( $i, array( 3, 23 ) ) ) {
																			$dprefix = 'rd';
																		}
																		?>
																		<li data-label="<?php echo intval($i) . esc_attr($dprefix) . ' ' . esc_attr__( 'day of month', 'armember-membership' ); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i) . esc_html($dprefix) . ' ' . esc_html__( 'day of month', 'armember-membership' ); ?></li>
																	<?php } ?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
										<span class="arm_end_of_term_action_note arm_font_size_13 arm_margin_top_12"><?php esc_html_e( 'Possible only in the case of semi-automatic / manual subscription', 'armember-membership' ); ?></span>
									</td>
								</tr>
								
								<?php
									$freePlans          = array();
									$cancel_eot_options = '';
									// $cancel_eot_options = '<li data-label="' . esc_html__('Remove this plan from user', 'armember-membership') . '" data-value="block">' . esc_html__('Remove this plan from user', 'armember-membership') . '</li>';
								if ( ! empty( $all_plans ) ) {
									foreach ( $all_plans as $p ) {
										$p_id = $p['arm_subscription_plan_id'];
										if ( $p_id != $plan_id && $p['arm_subscription_plan_status'] == '1' ) {
											$freePlans[]         = $p_id;
											$data_label          = esc_html__( 'Give access to', 'armember-membership' ) . ' ' . esc_html( stripslashes( $p['arm_subscription_plan_name'] ) );
											$cancel_eot_options .= '<li data-label="' . esc_attr( $data_label ) . '" data-value="' . esc_attr($p_id) . '">' . $data_label . '</li>';
										}
									}
								}
								?>
								<tr class="form-field paid_subscription_options_recurring arm_recurring_cancel_action <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<th class="arm_padding_top_0"><label><?php esc_html_e( 'Cancel Subscription Action', 'armember-membership' ); ?>&nbsp;<span>(<?php esc_html_e( 'By User', 'armember-membership' ); ?>)</span><br/>
									</label></th>
									<td class="arm_vertical_align_top arm_padding_top_0">
										<?php
										$cancel_action = ( ! empty( $plan_options['cancel_action'] ) ) ? $plan_options['cancel_action'] : 'block';
										if ( $cancel_action != 'block' ) {
											if ( ! in_array( $cancel_action, $freePlans ) ) {
												$cancel_action = 'block';
											}
										}
										?>
										<div>
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_plan_cancel_action">
														<input class="arm-selectpicker-input-control" type="text" id="arm_plan_cancel_action" name="arm_subscription_plan_options[cancel_action]" value="<?php echo esc_html($cancel_action); ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_plan_cancel_action" style="display:none;">
																	<?php echo '<li data-label="' . esc_html__( 'Remove this plan from user', 'armember-membership' ) . '" data-value="block">' . esc_html__( 'Remove this plan from user', 'armember-membership' ) . '</li>' . $cancel_eot_options; //phpcs:ignore ?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
										<span class="arm_end_of_term_action_note arm_font_size_13 arm_margin_top_12"><?php esc_html_e('Action to be performed when user cancels membership from Front-end.', 'armember-membership'); ?></span>
									</td>
								</tr>
								<tr class="form-field paid_subscription_options_recurring arm_recurring_cancel_action_mode <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
									<th><label><?php esc_html_e("When user's subscription plan should be cancelled", 'armember-membership'); ?></label></th>
									<td>
										<?php $cancel_plan_action = (isset($plan_options["cancel_plan_action"])) ? $plan_options["cancel_plan_action"] : 'immediate'; ?>
										<div class="arm_form_fields_wrapper">
											<div class="arm-df__form-field-wrap_radio arm-df__form-field-wrap arm-controls  arm-d-flex" id="arm-df__form-field-wrap_arm_recurring_cancel_action_mode">
												<input class="arm_iradio arm-df__form-control_arm_recurring_cancel_action_mode arm_material_input arm_cancel_action_radio" type="radio" name="arm_subscription_plan_options[cancel_plan_action]" id="arm_cancel_action_radio_on_expire" value="on_expire" <?php checked($cancel_plan_action, 'on_expire') ?>>
												<label class="arm_radio_label" for="arm_cancel_action_radio_on_expire"><?php esc_html_e('Do not cancel subscription until plan expired', 'armember-membership'); ?></label>

												<input class="arm_iradio arm-df__form-control_arm_recurring_cancel_action_mode arm_material_input arm_cancel_action_radio" type="radio" name="arm_subscription_plan_options[cancel_plan_action]" id="arm_cancel_action_radio_immediate" value="immediate" <?php checked($cancel_plan_action, 'immediate') ?>>
												<label class="arm_radio_label" for="arm_cancel_action_radio_immediate"><?php esc_html_e('Cancel Subscription Immediately', 'armember-membership'); ?></label>
											</div>
											<div class="armclear"></div>
										</div>
									</td>
								</tr>
								<tr class="form-field paid_subscription_options_recurring_payment_cycles_main_box_tr divider <?php echo ($subscription_type == 'recurring') ? '' : 'hidden_section'; ?>">
									<td>
										<span class="arm-note-message --notice arm_badge_size_field_label"><?php esc_html_e('In case of infinite subscription plan cancelled, then that plan will be cancelled after current cycle completes.', 'armember-membership'); ?></span>
									</td>
								</tr>
								
								<?php
									$arm_paid_subscription_action_rec = '';
									if($subscription_type == 'recurring')
									{
										$arm_paid_subscription_action_rec = 'paid_subscription_end_of_term_rec';
									}
									?>
								<tr class="form-field paid_subscription_end_of_term <?php echo $arm_paid_subscription_action_rec; ?> <?php echo ( $subscription_type == 'paid_finite' || $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>" >
									
									<th><label><?php esc_html_e( 'End Of Term Action', 'armember-membership' ); ?></label></th>
									<td class="arm_padding_top_0">
										<?php
										$eot_action = ( ! empty( $plan_options['eot'] ) ) ? $plan_options['eot'] : 'block';
										if ( $eot_action != 'block' ) {
											if ( ! in_array( $eot_action, $freePlans ) ) {
												$eot_action = 'block';
											}
										}
										?>
										<div class="arm_form_fields_wrapper">
											<div class="arm_setup_forms_container">
												<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_end_of_term_action">
													<input class="arm-selectpicker-input-control" type="text" id="arm_end_of_term_action" name="arm_subscription_plan_options[eot]" value="<?php echo esc_attr( sanitize_text_field($eot_action) ); ?>"/>
													
													<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_subscription_plan_options_eot">
														<dt>
															<span></span>
															<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
														</dt>
														<dd>
															<ul data-id="arm_end_of_term_action">
																<?php echo '<li data-label="' . esc_html__( 'Remove this plan from user', 'armember-membership' ) . '" data-value="block">' . esc_html__( 'Remove this plan from user', 'armember-membership' ) . '</li>' . $cancel_eot_options; //phpcs:ignore ?>
															</ul>
														</dd>
													</dl>
												</div>
											</div>
											<div class="armclear"></div>
										</div>
										
										<span class="arm_font_size_13 arm_margin_top_12 arm_font_color_label_note"><?php esc_html_e('Action to be performed after plan duration is finished.', 'armember-membership'); ?></span>
									</td>
								</tr>
								<?php
								$arm_paid_subscription_grace_action_rec = '';
								if($subscription_type == 'recurring')
								{
									$arm_paid_subscription_grace_action_rec = 'paid_subscription_grace_period_end_of_term_rec';
								}
								?>
								<tr class="form-field paid_subscription_grace_period_end_of_term <?php echo $arm_paid_subscription_grace_action_rec;?> <?php echo ( $subscription_type == 'recurring' || $subscription_type == 'paid_finite' ) ? '' : 'hidden_section'; ?>">
									<th><label><?php esc_html_e( 'Grace Period End of Term', 'armember-membership' ); ?>&nbsp;<span>(<?php esc_html_e('Days', 'armember-membership');?>)</span></label></th>
									<td class="arm_padding_top_0">
										<?php
										$grace_period_eot = ( ! empty( $plan_options['grace_period']['end_of_term'] ) ) ? $plan_options['grace_period']['end_of_term'] : '0';
										?>
										<div>
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_plan_grace_period_eot">
														<input class="arm-selectpicker-input-control" type="text" id="arm_plan_grace_period_eot" name="arm_subscription_plan_options[grace_period][end_of_term]" value="<?php echo intval($grace_period_eot); ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_plan_grace_period_eot">
																	<?php
																	for ( $p = 0; $p <= 90; $p++ ) {
																		?>
																		<li data-value="<?php echo intval($p); ?>" data-label="<?php echo intval($p); ?>"><?php echo intval($p); ?></li>
																		<?php
																	}
																	?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
									</td>
								</tr>						
								<tr class="form-field arm_width_100_pct paid_subscription_rec_notice <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<td class="arm_padding_left_0 arm_padding_bottom_0 arm_margin_0">
									<?php
										$arm_payment_gateway_notice = '';
										echo apply_filters('arm_warning_notices_for_payment_gateways',$arm_payment_gateway_notice); //phpcs:ignore
									?>
									</td>
								</tr>
							
								<tr class="form-field paid_subscription_options_recurring arm_failed_payment_action_option <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<th><label><?php esc_html_e( 'Payment Failed Action', 'armember-membership' ); ?></label></th>
									<td class="arm_padding_top_0">
										<?php
										$payment_failed_action = ( ! empty( $plan_options['payment_failed_action'] ) ) ? $plan_options['payment_failed_action'] : 'block';
										if ( $payment_failed_action != 'block' ) {
											if ( ! in_array( $payment_failed_action, $freePlans ) ) {
												$payment_failed_action = 'block';
											}
										}
										?>
										<div>
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_plan_payment_failed_action">
														<input class="arm-selectpicker-input-control" type="text" id="arm_plan_payment_failed_action" name="arm_subscription_plan_options[payment_failed_action]" value="<?php echo intval($grace_period_eot); ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_plan_payment_failed_action">
																	<?php echo '<li data-label="' . esc_html__( 'Block all access of this plan', 'armember-membership' ) . '" data-value="block">' . esc_html__( 'Block all access of this plan', 'armember-membership' ) . '</li>' . $cancel_eot_options; //phpcs:ignore ?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
										<span class="arm_end_of_term_action_note arm_font_size_13 arm_margin_top_12"><?php esc_html_e('Action to be performed when payment has been failed due to any reason.', 'armember-membership'); ?></span>
									</td>
								</tr>
								<tr class="form-field paid_subscription_options_recurring arm_failed_payment_action_option arm_failed_grace_action <?php echo ( $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">
									<th><label><?php esc_html_e( 'Grace Period Failed Payment', 'armember-membership' ); ?>&nbsp;<span>(<?php esc_html_e('Days', 'armember-membership'); ?>)<br/></span></label></th>
									<td class="arm_padding_top_0">
										<?php
										$grace_period_faild_payment = ( isset( $plan_options['grace_period']['failed_payment'] ) ) ? $plan_options['grace_period']['failed_payment'] : '2';
										?>
										<div>
											<div class="arm_form_fields_wrapper">
												<div class="arm_setup_forms_container">
													<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_plan_grace_period_failed_payment">
														<input class="arm-selectpicker-input-control" type="text" id="arm_plan_grace_period_failed_payment" name="arm_subscription_plan_options[grace_period][failed_payment]" value="<?php echo intval($grace_period_eot); ?>"/>
														
														<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
															<dt>
																<span></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_plan_grace_period_failed_payment">
																	<?php
																	for ( $p = 0; $p <= 31; $p++ ) {
																		?>
																		<li data-value="<?php echo intval($p); ?>" data-label="<?php echo intval($p); ?>"><?php echo intval($p); ?></li>
																		<?php
																	}
																	?>
																</ul>
															</dd>
														</dl>
													</div>
												</div>
												<div class="armclear"></div>
											</div>
										</div>
									</td>
								</tr>
								<tr class="form-field arm_current_plan_warning_section hidden_section arm_width_100_pct">
									<td class="arm_padding_bottom_0 arm_padding_left_0">
										<div class="arm_current_plan_warning_section hidden_section">
											<span class="arm_current_plan_warning error" ><?php esc_html_e('One or more members has already subscribed to this plan. Any changes made to plan type & price will be applied (affect) to new users but not existing ones.', 'armember-membership'); ?></span>
										</div>
									</td>
								</tr>
								<tr class="form-field paid_subscription_options arm_upd_paid_subscription_divider arm_width_100_pct">
									<td>
										<div class="arm_solid_divider arm_upd_paid_subscription_divider"></div>
									</td>
								</tr>
							</table>
                        </div>
                    </div>
                </div>
				
				<div class="form-field paid_subscription_upgrade_downgrade arm_margin_bottom_24 <?php echo (($subscription_type == 'paid_infinite' || $subscription_type == 'paid_finite') || $subscription_type == 'recurring' ) ? '' : 'hidden_section'; ?>">				
                    <table class="form-table paid_subscription_upgrade_downgrade_tbl">
                        <tr class = 'form-field arm_upgrade_downgrade_section'>
								<?php
								$enable_up_down_action = ( isset( $plan_options['enable_upgrade_downgrade_action'] ) ) ? $plan_options['enable_upgrade_downgrade_action'] : 0;
								$upgrade_plans         = ( isset( $plan_options['upgrade_plans'] ) ) ? $plan_options['upgrade_plans'] : array();
								$upgrade_action        = ( isset( $plan_options['upgrade_action'] ) && $subscription_type != 'paid_infinite') ? $plan_options['upgrade_action'] : 'immediate';
								$downgrade_plans       = ( isset( $plan_options['downgrade_plans'] ) ) ? $plan_options['downgrade_plans'] : array();
								$downgrade_action      = ( isset( $plan_options['downgrade_action'] ) && $subscription_type != 'paid_infinite') ? $plan_options['downgrade_action'] : 'immediate';
								?>
								<td class="arm_upgrade_downgrade_section arm_padding_top_8">
									<div class="arm_upgrade_downgrade_section_switch">
										<div class="armswitch arm_global_setting_switch arm_vertical_align_middle" >
											<input type="checkbox" id="enable_upgrade_downgrade_action" <?php checked( $enable_up_down_action, 1 ); ?> value="1" class="armswitch_input" name="arm_subscription_plan_options[enable_upgrade_downgrade_action]"/>
											<label for="enable_upgrade_downgrade_action" class="armswitch_label arm_min_width_40" ></label>
										</div>
										<label for="enable_upgrade_downgrade_action" class="arm_padding_left_10 arm_font_size_14 arm_field_hint"><?php esc_html_e('Enable Upgrade / Downgrade Action', 'armember-membership'); ?><i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e('Upgrade / Downgrade action will be applied when users will change their plan from frontend. Select appropriate plan level which is higher/lower than current plan and action will be performed accordingly.', 'armember-membership'); ?>"></i></label>
										
										<span class ="arm_font_size_14 arm_margin_top_10 " style="float:left;width:50%;position:relative;top:5px;font-weight:400px;color: #6E7E9E;"><?php esc_html_e( 'Action to be performed when user upgrade / downgrade membership from current plan.', 'armember-membership' ); ?></span>
									</div>
									<div class="arm_enable_up_down_action <?php echo ( $enable_up_down_action != '1' ) ? 'hidden_section' : ''; ?>">
										<div class="arm_membership_upgrade_downgrade_plan_section">
											<span  class="arm_font_size_16 arm_font_weight_500 arm_up_down_action_label"><?php esc_html_e( 'Upgrade Plan', 'armember-membership' ); ?></span>
											<span class="arm_margin_bottom_12 arm_font_size_16 arm_font_weight_400 arm-black-350"><?php esc_html_e('Select plan(s) which level is higher than current plan', 'armember-membership'); ?></span>
											<table width="100%">
											<tr>
												<td class="arm_upgrade_downgrade_section">
													<select name="arm_subscription_plan_options[upgrade_plans][]" class="arm_chosen_selectbox arm_upgrade_plans_selectbox" multiple tabindex="-1" data-placeholder="<?php esc_attr_e( 'Select higher plan(s)..', 'armember-membership' ); ?>">
														<?php
														$isURecSelected = false;
														if ( ! empty( $all_plans ) ) {
															foreach ( $all_plans as $plan ) {
																$isRecurring = '0';
																$planOpts    = $plan['arm_subscription_plan_options'];
																if ( $plan['arm_subscription_plan_type'] != 'free' ) {
																	if ( $planOpts['access_type'] == 'finite' && $planOpts['payment_type'] == 'subscription' ) {
																		$isRecurring = '1';
																		if ( in_array( $plan['arm_subscription_plan_id'], $upgrade_plans ) ) {
																			$upgrade_action = 'immediate';
																			$isURecSelected = true;
																		}
																	}
																}
																if ( $plan_id != $plan['arm_subscription_plan_id'] ) {
																	?>
																	<option value="<?php echo intval($plan['arm_subscription_plan_id']); ?>" <?php echo ( in_array( $plan['arm_subscription_plan_id'], $upgrade_plans ) ) ? 'selected="selected"' : ''; ?> data-recurring="<?php echo esc_attr($isRecurring); ?>"><?php echo esc_html( stripslashes( $plan['arm_subscription_plan_name'] ) ); //phpcs:ignore ?></option>
																							  <?php
																}
															}
														}
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="arm_upgrade_downgrade_section">
													<span class="arm_font_size_16 arm_margin_bottom_15 arm-black-350"><?php esc_html_e( 'What action should be performed while upgrading to other plan', 'armember-membership' ); ?></span><br/>
													<label style="<?php echo ( $isURecSelected || $subscription_type == 'paid_infinite') ? 'display:none;' : ''; ?>" class="arm_upgrade_action_on_expire">
														<input type="radio" class="arm_iradio arm_upgrade_action_radio" name="arm_subscription_plan_options[upgrade_action]" value="on_expire" <?php checked( $upgrade_action, 'on_expire' ); ?>/>
														<span class="arm_padding_left_10"><?php esc_html_e( 'Upgrade to other plan after current plan expiration ( After End Of Term)', 'armember-membership' ); ?></span>
													</label>
													<label class="arm_upgrade_action_immediate">
														<input type="radio" class="arm_iradio arm_upgrade_action_radio" name="arm_subscription_plan_options[upgrade_action]" value="immediate" <?php checked( $upgrade_action, 'immediate' ); ?>/>
														<span class="arm_padding_left_10"><?php esc_html_e( 'Immediately upgrade to other plan', 'armember-membership' ); ?></span>
													</label>
												</td>
											</tr>
										</table>
									</div>
									<div class="arm_solid_divider arm_upd_dwn_divider"></div>
									<div class="arm_membership_upgrade_downgrade_plan_section">
									<span  class="arm_font_size_16 arm_font_weight_500 arm_up_down_action_label"><?php esc_html_e( 'Downgrade Plan', 'armember-membership' ); ?></span><span class="arm_margin_bottom_12 arm_font_size_16 arm_font_weight_400 arm-black-350"><?php esc_html_e('Select plan(s) which level is lower than current plan', 'armember-membership'); ?></span>
										<table width="100%">
											<tr>
												<td class="arm_upgrade_downgrade_section">
													<select name="arm_subscription_plan_options[downgrade_plans][]" class="arm_chosen_selectbox arm_downgrade_plans_selectbox" multiple tabindex="-1" data-placeholder="<?php esc_html_e( 'Select lower plan(s)..', 'armember-membership' ); ?>">
														<?php
														$isDRecSelected = false;
														if ( ! empty( $all_plans ) ) {
															foreach ( $all_plans as $plan ) {
																$isRecurring = '0';
																$planOpts    = $plan['arm_subscription_plan_options'];
																if ( $plan['arm_subscription_plan_type'] != 'free' ) {
																	if ( $planOpts['access_type'] == 'finite' && $planOpts['payment_type'] == 'subscription' ) {
																		$isRecurring = '1';
																		if ( in_array( $plan['arm_subscription_plan_id'], $downgrade_plans ) ) {
																			$downgrade_action = 'immediate';
																			$isDRecSelected   = true;
																		}
																	}
																}
																if ( $plan_id != $plan['arm_subscription_plan_id'] ) {
																	?>
																	<option value="<?php echo intval($plan['arm_subscription_plan_id']); ?>" <?php echo ( in_array( $plan['arm_subscription_plan_id'], $downgrade_plans ) ) ? 'selected="selected"' : ''; ?> data-recurring="<?php echo esc_attr($isRecurring); ?>"><?php echo esc_html( stripslashes( $plan['arm_subscription_plan_name'] ) ); //phpcs:ignore ?></option>
																							  <?php
																}
															}
														}
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="arm_upgrade_downgrade_section">
													<span class="arm_font_size_16 arm_margin_bottom_15 arm-black-350"><?php esc_html_e( 'What action should be performed while downgrading to other plan', 'armember-membership' ); ?></span><br/>
													<label style="<?php echo ( $isDRecSelected || $subscription_type == 'paid_infinite') ? 'display:none;' : ''; //phpcs:ignore ?>" class="arm_downgrade_action_on_expire">
														<input type="radio" class="arm_iradio arm_downgrade_action_radio" name="arm_subscription_plan_options[downgrade_action]" value="on_expire" <?php checked( $downgrade_action, 'on_expire' ); ?>/>
														<span class="arm_padding_left_10"><?php esc_html_e( 'Downgrade to other plan after current plan expiration ( After End Of Term)', 'armember-membership' ); ?></span>
													</label>
													<label class="arm_downgrade_action_immediate">
														<input type="radio" class="arm_iradio arm_downgrade_action_radio" name="arm_subscription_plan_options[downgrade_action]" value="immediate" <?php checked( $downgrade_action, 'immediate' ); ?>/>
														<span class="arm_padding_left_10"><?php esc_html_e( 'Immediately downgrade to other plan', 'armember-membership' ); ?></span>
													</label>
												</td>
											</tr>
										</table>
									</div>
                                					</div>
								</td>
							</tr>
						</table>
					</div>
					<?php
					do_action( 'arm_display_field_add_membership_plan', $plan_options );
				?>
				<div class="arm_submit_btn_container">
					<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' //phpcs:ignore?>" id="arm_loader_img_add_member_plan_loader" class="arm_loader_img arm_submit_btn_loader"  style=" display: none;" width="20" height="20" />
					<a class="arm_cancel_btn arm_membership_pln_close_btn arm_margin_right_10" href="javascript:void(0)"><?php esc_html_e( 'Close', 'armember-membership' ); ?></a>
					<button class="arm_save_btn arm_user_plan_save_btn arm_margin_right_143" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
				</div>
				<div class="armclear"></div>
			</div>
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
			<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</form>
		<div class="armclear"></div>
	</div>
</div>
	<script>
        var CYCLEAMOUNT = "<?php esc_html_e('Amount', 'armember-membership'); ?>";
        var BILLINGCYCLE = "<?php esc_html_e('Billing Cycle', 'armember-membership'); ?>";
        var ARMCYCLELABEL = "<?php esc_html_e('Label', 'armember-membership'); ?>";
        var RECURRINGTIME = "<?php esc_html_e('Recurring Time', 'armember-membership'); ?>";
		var AMOUNTERROR = "<?php esc_html_e( 'Amount should not be blank.', 'armember-membership' ); ?>";
		var LABELERROR = "<?php esc_html_e( 'Label should not be blank.', 'armember-membership' ); ?>";
        var DAY = "<?php esc_html_e('Day(s)', 'armember-membership'); ?>";
        var MONTH = "<?php esc_html_e('Month(s)', 'armember-membership'); ?>";
        var YEAR = "<?php esc_html_e('Year(s)', 'armember-membership'); ?>";
        var INFINITE = "<?php esc_html_e('Infinite', 'armember-membership'); ?>";
        var EMESSAGE = "<?php esc_html_e('You cannot remove all payment cycles.', 'armember-membership'); ?>";
        var ARMREMOVECYCLE = "<?php esc_html_e('Remove Cycle', 'armember-membership'); ?>";
		var CURRENCYINPUTCLASS = "<?php echo $arm_currency_pos_class; //phpcs:ignore?>";
        var CURRENCYPREF = "<?php echo $global_currency_sym_pos_pre; //phpcs:ignore?>";
        var CURRENCYSUF = "<?php echo $global_currency_sym_pos_suf; //phpcs:ignore?>";
		var CURRENCYSYM = "<?php echo $global_currency_sym; //phpcs:ignore?>";
        var CURRENCYSYM = "<?php echo $global_currency_sym; //phpcs:ignore?>";
        var ARM_RR_CLOSE_IMG = '<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_close_icon.png'; 
        var ARM_RR_CLOSE_IMG_HOVER = '<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_close_icon_hover.png';
        var ADDCYCLE = "<?php esc_html_e('Add Payment Cycle', 'armember-membership'); ?>";
        var REMOVECYCLE = "<?php esc_html_e('Remove Payment Cycle', 'armember-membership'); ?>";
		</script>
		
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('membership-plan-add'); //phpcs:ignore
?>