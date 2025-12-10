<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_class,$arm_manage_coupons,  $arm_payment_gateways, $arm_subscription_plans;
$currencies      = $arm_payment_gateways->arm_get_all_currencies();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$all_members     = $arm_members_class->arm_get_all_members_without_administrator( 0, 0 );
$all_plans       = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type' );
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
/*if ( isset( $posted_data['action'] ) && $posted_data['action'] == 'add_payment_history' ) {
	do_action( 'arm_save_manual_payment', $posted_data ); //phpcs:ignore
}*/
?>
<div class="wrap arm_page arm_add_edit_payment_history_main_wrapper popup_wrapper">
	<div class="content_wrapper arm_add_edit_payment_history_content" id="content_wrapper">
		<div class="popup_header page_title">
			<span><?php esc_html_e( 'Add Manual Payment', 'armember-membership' ); ?></span>
			<span class="arm_popup_close_btn arm_admin_payment_cancel_btn"></span>
		</div>
		<div class="armclear"></div>
		<form  method="post" id="arm_add_edit_payment_history_form" class="arm_add_edit_payment_history_form arm_admin_form">
			<input type="hidden" name="arm_action" value="add_payment_history">
			<div class="arm_admin_form_content">
				<div class="arm_admin_form_content_inner">
				<div class="arm_setup_section_title arm_margin_bottom_24"> <?php esc_html_e('Members','armember-membership'); ?></div>
				
				<div class="armclear"></div>
				<table class="form-table arm_transaction_table">
					<tbody class="arm_margin_left_0">
					<tr class="form-field form-required arm_auto_user_field">
						<th>
							<label for="arm_user_id"><?php esc_html_e( 'Select Member', 'armember-membership' ); ?></label>
						</th>
						<td>
							<div class="arm_setup_forms_container">
								<input id="arm_user_auto_selection" type="text" name="arm_user_ids" value="" placeholder="<?php esc_attr_e('Search by username or email...', 'armember-membership');?>" data-msg-required="<?php esc_attr_e('Please select user.', 'armember-membership');?>" required>
								<input type="hidden" name="arm_display_admin_user" id="arm_display_admin_user" value="0">
								<div class="arm_users_items arm_required_wrapper" id="arm_users_items" style="display: none;"></div>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
				<div class="arm_solid_divider"></div>
				<div class="arm_setup_section_title arm_margin_top_30 arm_margin_bottom_24"> <?php esc_html_e('Plan Details','armember-membership'); ?></div>
				<table class="form-table arm_display_block">
					<tbody class="arm_margin_left_0 ">
						<?php if($ARMemberLite->is_arm_pro_active){
							$arm_selection_options = '';
							echo apply_filters('arm_payment_gateway_selection_options',$arm_selection_options); //phpcs:ignore
						}?>
					</tbody>
				</table>
				<table class="form-table arm_transaction_table">
				<tbody class="arm_margin_left_0">
					<tr class="form-field form-required arm_transaction_membership_plan_wrapper">
						<th>
							<label for="arm_plan_id"><?php esc_html_e( 'Select Membership Plan', 'armember-membership' ); ?></label>
						</th>
						<td>
						<div class="arm_form_fields_wrapper">
								<div class="arm_setup_forms_container">
									<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_arm_plan_id">
										<input class="arm-selectpicker-input-control" type="text" id="arm_plan_id" name="manual_payment[plan_id]" value="success">
										<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
											<dt>
												<span></span>
												<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
											</dt>
											<dd>
												<ul data-id="arm_plan_id" style="display:none;">
													<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
													<?php
													if ( ! empty( $all_plans ) ) {
														foreach ( $all_plans as $p ) {
															$p_id = $p['arm_subscription_plan_id'];
															if ( $p['arm_subscription_plan_status'] == '1' && $p['arm_subscription_plan_type'] != 'free' ) {
																?>
																<li data-label="<?php echo esc_attr(stripslashes( $p['arm_subscription_plan_name']) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); //phpcs:ignore ?></li>
																						<?php
															}
														}
													}
													?>
												</ul>
											</dd>
										</dl>
									</div>
									
								</div>
							</div>
						</td>
					</tr>
					<?php if($ARMemberLite->is_arm_pro_active){
						$arm_plans_selection_options = '';
						echo apply_filters('arm_payment_gateway_plans_selection_options',$arm_plans_selection_options); //phpcs:ignore
					}?>
					<tr class="form-field form-required arm_transaction_status_section">
						<th>
							<label for=""><?php esc_html_e( 'Status', 'armember-membership' ); ?></label>
						</th>
						<td>
							<div class="arm_form_fields_wrapper">
								<div class="arm_setup_forms_container">
									<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_transaction_status">
										<input class="arm-selectpicker-input-control" type="text" id="transaction_status" name="manual_payment[transaction_status]" value="success">
										<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
											<dt>
												<span></span>
												<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
											</dt>
											<dd>
												<ul data-id="transaction_status" style="display:none;">
													<li data-label="<?php esc_attr_e( 'Success', 'armember-membership' ); ?>" data-value="success"><?php esc_html_e( 'Success', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Pending', 'armember-membership' ); ?>" data-value="pending"><?php esc_html_e( 'Pending', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Cancelled', 'armember-membership' ); ?>" data-value="canceled"><?php esc_html_e( 'Cancelled', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Failed', 'armember-membership' ); ?>" data-value="failed"><?php esc_html_e( 'Failed', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Expired', 'armember-membership' ); ?>" data-value="expired"><?php esc_html_e( 'Expired', 'armember-membership' ); ?></li>
												</ul>
											</dd>
										</dl>
									</div>
									
								</div>
							</div>
						</td>
					</tr>
					<tr class="form-field form-required arm_transaction_amount_section">
						<th>
							<label for=""><?php esc_html_e( 'Amount', 'armember-membership' ); ?></label>
						</th>
						<td>
							<div class="arm_setup_forms_container arm_transaction_flex">
								<div class="arm-df__form-field-wrap_text arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_member_plan_amount">
									<input name="manual_payment[amount]" type="text" id="arm-df__form-control_member_plan_amount arm_subscription_plan_amount" value="0" class="arm-df__form-control arm-df__form-control_member_plan_amount arm_material_input arm_no_paste"  onkeypress="javascript:return ArmNumberValidation(event, this)"/>
								</div>							
								<?php $currencies = apply_filters( 'arm_available_currencies', $currencies ); ?>
								<div class="arm_form_fields_wrapper">
									<div class="arm_setup_forms_container">
										<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_transaction_currency">
											<input class="arm-selectpicker-input-control" type="text" id="transaction_currency" name="manual_payment[currency]" value="<?php echo esc_attr($global_currency); ?>">
											<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_currency_selectbox">
												<dt>
													<span></span>
													<input type="text" style="display:none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="transaction_currency" style="display:none;">
														<?php foreach ( $currencies as $key => $value ) : ?>
															<li data-label="<?php echo esc_attr($key) . " (". esc_attr($value) .") "; ?>" data-value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key) . " (".esc_html($value).") "; ?></li>
														<?php endforeach; ?>
													</ul>
												</dd>
											</dl>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
			
					</tbody>
				</table>
				<div class="arm_setup_forms_container">
					<label for=""><?php esc_html_e( 'Note', 'armember-membership' ); ?></label>
					<textarea name="manual_payment[note]" rows="5" cols="20" class="arm_margin_top_12" style="height:auto"></textarea>
				</div>
				<div class="arm-note-message --alert arm_width_95_pct arm_margin_top_24">
					<p><?php esc_html_e( 'Important Note', 'armember-membership' ); ?>:</p><span><?php esc_html_e( 'The only purpose of this form is to add missed payment records of users for keeping track of their all payments. So, it doesn\'t mean that, when you add paymnet from here for any plan, it will renew next payment cycle or any plan will be assigned to user.', 'armember-membership' ); ?></span>
				</div>
				<div class="armclear"></div>
				</div>
			</div>
				<div class="arm_submit_btn_container">
					<a class="arm_cancel_btn arm_admin_payment_cancel_btn" href="javascript:void(0)"><?php esc_html_e( 'Close', 'armember-membership' ); ?></a>
					<button class="arm_save_btn arm_admin_payment_submit_btn" type="submit" name="manualPaymentSubmit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
			</div>
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
			<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce); ?>"/>
		</form>
		<div class="armclear"></div>
	</div>
</div>
<div id="arm_all_users" style="display:none;visibility: hidden;opacity: 0;">
	<?php echo wp_json_encode( $all_members ); ?>
</div>
<script type="text/javascript">
	__SELECT_USER = '<?php echo esc_html__( 'Type username to select user', 'armember-membership' ); ?>';
</script>
<?php
	echo $ARMemberLite->arm_get_need_help_html_content('member-payment-history-add'); //phpcs:ignore
?>