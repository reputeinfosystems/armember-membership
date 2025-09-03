<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_class,$arm_manage_coupons,  $arm_payment_gateways, $arm_subscription_plans;
$currencies      = $arm_payment_gateways->arm_get_all_currencies();
$global_currency = $arm_payment_gateways->arm_get_global_currency();
$all_members     = $arm_members_class->arm_get_all_members_without_administrator( 0, 0 );
$all_plans       = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type' );
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
if ( isset( $posted_data['action'] ) && $posted_data['action'] == 'add_payment_history' ) {
	do_action( 'arm_save_manual_payment', $posted_data ); //phpcs:ignore
}
?>
<div class="wrap arm_page arm_add_edit_payment_history_main_wrapper">
	<div class="content_wrapper arm_add_edit_payment_history_content" id="content_wrapper">
		<div class="page_title"><?php esc_html_e( 'Add Manual Payment', 'armember-membership' ); ?></div>
		<div class="armclear"></div>
		<form  method="post" id="arm_add_edit_payment_history_form" class="arm_add_edit_payment_history_form arm_admin_form">
			<input type="hidden" name="action" value="add_payment_history">
			<div class="arm_admin_form_content">
				<table class="form-table">
					<tr class="form-field form-required">
						<td colspan="2"><div class="arm-note-message --warning arm_width_95_pct"><p><?php esc_html_e( 'Important Note', 'armember-membership' ); ?>:</p><span><?php esc_html_e( 'The only purpose of this form is to add missed payment records of users for keeping track of their all payments. So, it doesn\'t mean that, when you add paymnet from here for any plan, it will renew next payment cycle or any plan will be assigned to user.', 'armember-membership' ); ?></span></div>
						</td>
					</tr>
					<tr class="form-field form-required arm_auto_user_field">
						<th>
							<label for="arm_user_id"><?php esc_html_e( 'Member', 'armember-membership' ); ?></label>
						</th>
						<td>
							<input id="arm_user_auto_selection" type="text" name="arm_user_ids" value="" placeholder="<?php esc_attr_e('Search by username or email...', 'armember-membership');?>" data-msg-required="<?php esc_attr_e('Please select user.', 'armember-membership');?>" required>
							<input type="hidden" name="arm_display_admin_user" id="arm_display_admin_user" value="0">
							<div class="arm_users_items arm_required_wrapper" id="arm_users_items" style="display: none;"></div>
						</td>
					</tr>
					<?php if($ARMemberLite->is_arm_pro_active){
						$arm_selection_options = '';
						echo apply_filters('arm_payment_gateway_selection_options',$arm_selection_options); //phpcs:ignore
					}?>
					<tr class="form-field form-required arm_transaction_membership_plan_wrapper">
						<th>
							<label for="arm_plan_id"><?php esc_html_e( 'Select Membership Plan', 'armember-membership' ); ?></label>
						</th>
						<td>
							<input type="hidden" id="arm_plan_id" name="manual_payment[plan_id]" value="" data-msg-required="<?php esc_attr_e( 'Please select at least one membership', 'armember-membership' ); ?>" required/>
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="arm_plan_id">
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
						</td>
					</tr>
					<?php if($ARMemberLite->is_arm_pro_active){
						$arm_plans_selection_options = '';
						echo apply_filters('arm_payment_gateway_plans_selection_options',$arm_plans_selection_options); //phpcs:ignore
					}?>
					<tr class="form-field form-required">
						<th>
							<label for=""><?php esc_html_e( 'Status', 'armember-membership' ); ?></label>
						</th>
						<td>
							<input type="hidden" id="transaction_status" name="manual_payment[transaction_status]" value="success" />
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="transaction_status">
										<li data-label="<?php esc_attr_e( 'Success', 'armember-membership' ); ?>" data-value="success"><?php esc_html_e( 'Success', 'armember-membership' ); ?></li>
										<li data-label="<?php esc_attr_e( 'Pending', 'armember-membership' ); ?>" data-value="pending"><?php esc_html_e( 'Pending', 'armember-membership' ); ?></li>
										<li data-label="<?php esc_attr_e( 'Cancelled', 'armember-membership' ); ?>" data-value="canceled"><?php esc_html_e( 'Cancelled', 'armember-membership' ); ?></li>
										<li data-label="<?php esc_attr_e( 'Failed', 'armember-membership' ); ?>" data-value="failed"><?php esc_html_e( 'Failed', 'armember-membership' ); ?></li>
										<li data-label="<?php esc_attr_e( 'Expired', 'armember-membership' ); ?>" data-value="expired"><?php esc_html_e( 'Expired', 'armember-membership' ); ?></li>
									</ul>
								</dd>
							</dl>
						</td>
					</tr>
					<tr class="form-field form-required">
						<th>
							<label for=""><?php esc_html_e( 'Amount', 'armember-membership' ); ?></label>
						</th>
						<td>
							<input type="text" name="manual_payment[amount]" value="0" onkeypress="javascript:return ArmNumberValidation(event,this)" class="arm_no_paste">
						</td>
					</tr>
					<tr class="form-field form-required">
						<th>
							<label for=""><?php esc_html_e( 'Currency', 'armember-membership' ); ?></label>
						</th>
						<td>
							<?php $currencies = apply_filters( 'arm_available_currencies', $currencies ); ?>
							<input type='hidden' id="transaction_currency" name="manual_payment[currency]" value="<?php echo esc_attr($global_currency); ?>"/>
							<dl class="arm_selectbox column_level_dd">
								<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="transaction_currency">
										<?php foreach ( $currencies as $key => $value ) : ?>
										<li data-label="<?php echo esc_attr($key) . " (". esc_attr($value) .") "; ?>" data-value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key) . " (".esc_html($value).") "; ?></li>
										<?php endforeach; ?>
									</ul>
								</dd>
							</dl>
						</td>
					</tr>
					
					<tr class="form-field form-required">
						<th>
							<label for=""><?php esc_html_e( 'Note', 'armember-membership' ); ?></label>
						</th>
						<td>
							<textarea name="manual_payment[note]" rows="5" cols="40"></textarea>
						</td>
					</tr>
				</table>
				<div class="arm_submit_btn_container">
					<button class="arm_save_btn" type="submit" name="manualPaymentSubmit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
					<a class="arm_cancel_btn" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->transactions ) ); //phpcs:ignore ?>"><?php esc_html_e( 'Close', 'armember-membership' ); ?></a>
				</div>
				<div class="armclear"></div>
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