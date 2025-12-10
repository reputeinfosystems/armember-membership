<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_payment_gateways,$arm_members_activity;
$arm_all_global_settings    = $arm_global_settings->arm_get_all_global_settings();
$arm_general_settings       = $arm_all_global_settings['general_settings'];
$global_currency            = $arm_payment_gateways->arm_get_global_currency();
$all_currency               = $arm_payment_gateways->arm_get_all_currencies();
$global_currency_symbol     = $all_currency[ strtoupper( $global_currency ) ];
$payment_gateways           = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
$arm_paypal_currency        = $arm_payment_gateways->currency['paypal'];
$arm_bank_transfer_currency = $arm_payment_gateways->currency['bank_transfer'];
if($ARMemberLite->is_arm_pro_active)
{
	if(isset($_SESSION['arm_file_upload_arr'])){
		unset($_SESSION['arm_file_upload_arr']);
	}
	$file_meta_key = "stripe_popup";
	$ARMember->arm_session_start(true);
	$arm_members_activity->session_for_file_handle($file_meta_key,"");
}
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content" id="content_wrapper">
		<form method="post" action="#" id="arm_payment_geteway_form" class="arm_payment_geteway_form arm_admin_form">
			<div class="page_sub_title arm_margin_bottom_32 arm_setting_title"><?php echo esc_html("Payment Gateways");
			$after_title_content = "";
			$after_title_content = apply_filters('arm_after_general_settings_title', $after_title_content); //phpcs:ignore
			echo $after_title_content; //phpcs:ignore
			 ?></div>
		<?php $i = 0;foreach ( $payment_gateways as $gateway_name => $gateway_options ) : ?>
			<?php
			$gateway_options['status'] = isset( $gateway_options['status'] ) ? $gateway_options['status'] : 0;
			$arm_status_switchChecked  = ( $gateway_options['status'] == '1' ) ? 'checked="checked"' : '';
			$disabled_field_attr       = ( $gateway_options['status'] == '1' ) ? '' : 'disabled="disabled"';
			$readonly_field_attr       = ( $gateway_options['status'] == '1' ) ? '' : 'readonly="readonly"';
			?>
			<?php if ( $i != 0 ) : ?>
				<div class="arm_margin_top_24"></div><?php endif; ?>
			<?php $i++; ?>
			<?php
				$apiCallbackUrlInfo = '';
				$apiCallbackUrlInfo = apply_filters( 'arm_gateway_callback_info', $apiCallbackUrlInfo, $gateway_name, $gateway_options );
				?>
			<div class="armclear"></div>
			<table class="arm_active_payment_gateways arm_payment_gateways" id="<?php echo esc_attr($gateway_name);?>">
			<div class="arm_setting_main_content">
				<div class="arm_row_wrapper">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label arm_margin_bottom_0">
							<?php echo esc_html($gateway_options['gateway_name']); ?>
							<?php
								$titleTooltip       = '';
								switch ( $gateway_name ) {
									case 'paypal':
										$titleTooltip = esc_html__( 'Click below links for more details about how to get API Credentials:', 'armember-membership' ) . '<br><a href="https://developer.paypal.com/api/nvp-soap/apiCredentials/" target="_blank">' . esc_html__( 'Sandbox/Live API Detail', 'armember-membership' ) . '</a>';

										break;

									default:
										break;
								}
								$titleTooltip       = apply_filters( 'arm_change_payment_gateway_tooltip', $titleTooltip, $gateway_name, $gateway_options );
								if ( ! empty( $titleTooltip ) ) {
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo htmlentities( $titleTooltip ); //phpcs:ignore ?>"></i>
									<?php
								}
								?>
						</div>					
					</div>

					<div class="right_content">
						<div class="armswitch arm_payment_setting_switch arm_margin_right_0">
							<input type="checkbox"
								id="arm_<?php echo strtolower( esc_attr($gateway_name) ); ?>_status"
								<?php echo $arm_status_switchChecked; ?>
								value="1"
								class="armswitch_input armswitch_payment_input"
								name="payment_gateway_settings[<?php echo strtolower( esc_attr($gateway_name) ); ?>][status]"
								data-payment="<?php echo strtolower( esc_attr($gateway_name) ); ?>"
							/>
							<label for="arm_<?php echo strtolower( esc_attr($gateway_name) ); ?>_status" class="armswitch_label"></label>
						</div>
					</div>
				</div>
				<?php
				switch ( strtolower( $gateway_name ) ) {
					case 'paypal':
						$gateway_options['paypal_payment_mode'] = ( ! empty( $gateway_options['paypal_payment_mode'] ) ) ? $gateway_options['paypal_payment_mode'] : 'sandbox';
						$globalSettings                         = $arm_global_settings->global_settings;
						$ty_pageid                              = isset( $globalSettings['thank_you_page_id'] ) ? $globalSettings['thank_you_page_id'] : 0;
						$cp_page_id                             = isset( $globalSettings['cancel_payment_page_id'] ) ? $globalSettings['cancel_payment_page_id'] : 0;
						$default_return_url                     = $arm_global_settings->arm_get_permalink( '', $ty_pageid );
						$default_cancel_url                     = $arm_global_settings->arm_get_permalink( '', $cp_page_id );
						$return_url                             = ( ! empty( $gateway_options['return_url'] ) ) ? $gateway_options['return_url'] : $default_return_url;
						$cancel_url                             = ( ! empty( $gateway_options['cancel_url'] ) ) ? $gateway_options['cancel_url'] : $default_cancel_url;
						?>
						<div class="arm_payment_gateway_section arm_paypal_main_container <?php echo ( $gateway_options['status'] == '1' ) ? '' : ' hidden_section'; ?>">
						<div class="arm_content_border arm_margin_top_24"></div>
						<div class="arm_email_setting_flex_group arm_margin_top_24">
							<div class="arm_form_field_block arm_mail_authentication_fields arm_max_width_550" style="">
								<label class="arm-form-table-label arm_font_size_16 arm_font_weight_400 arm_width_100_pct"><?php esc_html_e( 'Merchant Email', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_width_100_pct" id="arm_payment_gateway_merch_email" type="text" name="payment_gateway_settings[paypal][paypal_merchant_email]" value="<?php echo ( ! empty( $gateway_options['paypal_merchant_email'] ) ? esc_attr( sanitize_email($gateway_options['paypal_merchant_email']) ) : '' ); ?>" data-msg-required="<?php esc_attr_e( 'Merchant Email can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
						<div class="arm_email_setting_flex_group arm_margin_top_24">
							<div class="arm_form_field_block arm_mail_authentication_fields arm_max_width_550" style="">
							<label class="arm-form-table-label arm_font_size_16 arm_font_weight_400 payment_label"><?php esc_html_e( 'Payment Mode', 'armember-membership' ); ?>*</label>
							<div class="arm_paypal_mode_container arm_margin_top_20 arm_margin_bottom_32" id="arm_paypal_mode_container">
									<input id="arm_payment_gateway_mode_sand" class="arm_general_input arm_paypal_mode_radio arm_iradio arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignoer ?> " type="radio" value="sandbox" name="payment_gateway_settings[paypal][paypal_payment_mode]" <?php checked( $gateway_options['paypal_payment_mode'], 'sandbox' ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?>><label for="arm_payment_gateway_mode_sand" class="arm_padding_right_46"><?php esc_html_e( 'Sandbox', 'armember-membership' ); ?></label>
									<input id="arm_payment_gateway_mode_pro" class="arm_general_input arm_paypal_mode_radio arm_iradio arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="radio" value="live" name="payment_gateway_settings[paypal][paypal_payment_mode]" <?php checked( $gateway_options['paypal_payment_mode'], 'live' ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?>><label for="arm_payment_gateway_mode_pro" class="arm_padding_right_46"><?php esc_html_e( 'Live', 'armember-membership' ); ?></label>
								</div>
							</div>
						</div>
						<!--**********./Begin Paypal Sandbox Details/.**********-->

						<div class="arm_email_setting_flex_group arm_payment_getway_page">
							<div class="arm_form_field_block arm_paypal_sandbox_fields <?php echo ( $gateway_options['paypal_payment_mode'] == 'sandbox' ) ? '' : 'hidden_section'; ?>">
								<label class="arm-form-table-label payment_label"><?php esc_html_e( 'API Username', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_username]" value="<?php echo ( ! empty( $gateway_options['sandbox_api_username'] ) ? esc_attr( sanitize_text_field($gateway_options['sandbox_api_username']) ) : '' ); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'API Username can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

							<div class="arm_form_field_block arm_paypal_sandbox_fields <?php echo ( $gateway_options['paypal_payment_mode'] == 'sandbox' ) ? '' : 'hidden_section'; ?>">
								<label class="arm-form-table-label payment_label"><?php esc_html_e( 'API Password', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_password]" value="<?php echo ( ! empty( $gateway_options['sandbox_api_password'] ) ? esc_attr($gateway_options['sandbox_api_password']) : '' ); //phpcs:ignore ?>" data-msg-required="<?php esc_attr_e( 'API Password can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

							<div class="arm_form_field_block arm_paypal_sandbox_fields <?php echo ( $gateway_options['paypal_payment_mode'] == 'sandbox' ) ? '' : 'hidden_section'; ?>">
								<label class="arm-form-table-label "><?php esc_html_e( 'API Signature', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); ?>" type="text" name="payment_gateway_settings[paypal][sandbox_api_signature]" value="<?php echo ( ! empty( $gateway_options['sandbox_api_signature'] ) ? esc_attr($gateway_options['sandbox_api_signature']) : '' ); ?>" data-msg-required="<?php esc_attr_e( 'API Signature can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

					
								<!--**********./End Paypal Sandbox Details/.**********-->
								<!--**********./Begin Paypal Live Details/.**********-->

						
							<div class="arm_form_field_block arm_paypal_live_fields  <?php echo ( $gateway_options['paypal_payment_mode'] == 'live' ) ? '' : 'hidden_section'; ?>">
								<label class="arm-form-table-label payment_label"><?php esc_html_e( 'API Username', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); ?>" type="text" name="payment_gateway_settings[paypal][live_api_username]" value="<?php echo ( ! empty( $gateway_options['live_api_username'] ) ? esc_attr($gateway_options['live_api_username']) : '' ); ?>" data-msg-required="<?php esc_attr_e( 'API Username can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

							<div class="arm_form_field_block arm_paypal_live_fields  <?php echo ( $gateway_options['paypal_payment_mode'] == 'live' ) ? '' : 'hidden_section'; ?>">
								<label class="arm-form-table-label payment_label"><?php esc_html_e( 'API Password', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); ?>" type="text" name="payment_gateway_settings[paypal][live_api_password]" value="<?php echo ( ! empty( $gateway_options['live_api_password'] ) ? esc_attr($gateway_options['live_api_password']) : '' ); ?>" data-msg-required="<?php esc_attr_e( 'API Password can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

							<div class="arm_form_field_block arm_paypal_live_fields  <?php echo ( $gateway_options['paypal_payment_mode'] == 'live' ) ? '' : 'hidden_section'; ?>">
								<label class="arm-form-table-label "><?php esc_html_e( 'API Signature', 'armember-membership' ); ?> *</label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="text" name="payment_gateway_settings[paypal][live_api_signature]" value="<?php echo ( ! empty( $gateway_options['live_api_signature'] ) ? esc_attr($gateway_options['live_api_signature']) : '' ); ?>" data-msg-required="<?php esc_attr_e( 'API Signature can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

					

							<!--**********./End Paypal Live Details/.**********-->

							<div class="arm_form_field_block">
								<label class="arm-form-table-label payment_label"><?php esc_html_e( 'Unsuccessful / Cancel Url', 'armember-membership' ); ?></label>
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="text" name="payment_gateway_settings[paypal][cancel_url]" value="<?php echo esc_url($cancel_url); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>

							<div class="arm_form_field_block">
								<label class="arm-form-table-label payment_label"><?php esc_html_e( 'Language', 'armember-membership' ); ?></label>
								<?php $arm_paypal_language = $arm_payment_gateways->arm_paypal_language(); ?>
								<input type='hidden' id='arm_paypal_language' name="payment_gateway_settings[paypal][language]" value="<?php echo ( ! empty( $gateway_options['language'] ) ) ? esc_attr($gateway_options['language']) : 'en_US'; ?>" />
								<div class="arm_selectbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_width_100_pct arm_margin_top_12" <?php echo $disabled_field_attr; //phpcs:ignore ?>>
									<dt <?php echo ( $gateway_options['status'] == '1' ) ? '' : 'style="border:1px solid #DBE1E8"'; ?>>
										<span></span>
										<input type="text" style="display:none;" value="<?php esc_attr_e( 'English/United States ( en_US )', 'armember-membership' ); ?>" class="arm_autocomplete"/>
										<i class="armfa armfa-caret-down armfa-lg"></i>
									</dt>
									<dd>
										<ul data-id="arm_paypal_language">
											<?php foreach ( $arm_paypal_language as $key => $value ) : ?>
												<li data-label="<?php echo esc_attr($value) . " ( ".esc_attr($key)." ) "; ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr($value) . " ( ".esc_attr($key)." ) "; ?></li>
											<?php endforeach; ?>
										</ul>
									</dd>
								</div>
							</div>

							<div class="arm_form_field_block">
								<label class="arm-form-table-label arm_width_100_pct"><?php esc_html_e( 'Currency', 'armember-membership' ); ?></label>
								<div class="arm_margin_top_12">
									
									<span class="arm_payment_gateway_currency_label "><?php echo esc_html($global_currency); ?><?php echo ' ( ' . esc_html($global_currency_symbol) . ' ) '; ?></span>
									<a class="arm_payment_gateway_currency_link arm_ref_info_links" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '#changeCurrency' ) ); //phpcs:ignore ?>"><?php esc_html_e( 'Change currency', 'armember-membership' ); ?></a>			
								</div>
							</div>

						</div>

						</div>
						</div>

						<?php
						break;

					case $gateway_name:
						$arm_gateway_fields = '';
						if($gateway_name != 'bank_transfer'){
							echo apply_filters('armember_pro_gateways_fields_section',$arm_gateway_fields,$gateway_name,$gateway_options); //phpcs:ignore
						}
						else
						{
						?>
						<div class="arm_payment_gateway_section arm_bank_transfer_main_container <?php echo ( $gateway_options['status'] == '1' ) ? '' : ' hidden_section'; ?>">
						<div class="arm_content_border arm_margin_top_24"></div>
						<div class="arm_email_setting_flex_group arm_margin_top_24">
							<div class="arm_form_field_block arm_mail_authentication_fields arm_max_width_72_pct" style="">
								<label class="arm-form-table-label arm_font_size_16 arm_font_weight_400 arm_padding_bottom_12"for="arm_bank_transfer_note"><?php esc_html_e( 'Note/Description', 'armember-membership' ); ?></label>
								<?php
									wp_editor(
										stripslashes( ( isset( $gateway_options['note'] ) ) ? $gateway_options['note'] : '' ),
										'arm_bank_transfer_note',
										array(
											'textarea_name' => 'payment_gateway_settings[bank_transfer][note]',
											'textarea_rows' => 6,
										)
									);
									?>
							</div>
						</div>
							<div class=" arm_margin_top_24">
								<div class="arm-form-table-label " >
									<div class="arm-setting-hadding-label">
											<?php esc_html_e( 'Fields to be included in payment form', 'armember-membership' ); ?>	
										</div>
								</div>
								<div class="arm-form-table-content armBankTransferFields arm_margin_top_20">
									<label>
											<?php $gateway_options['fields']['transaction_id'] = isset( $gateway_options['fields']['transaction_id'] ) ? $gateway_options['fields']['transaction_id'] : ''; ?>
											<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="checkbox" id="bank_transfer_transaction_id" name="payment_gateway_settings[bank_transfer][fields][transaction_id]" value="1" <?php checked( $gateway_options['fields']['transaction_id'], 1 ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?> >
										<span><?php esc_html_e( 'Transaction ID', 'armember-membership' ); ?></span>
									</label>
									<label>
										<?php $gateway_options['fields']['bank_name'] = ( isset( $gateway_options['fields']['bank_name'] ) ) ? $gateway_options['fields']['bank_name'] : ''; ?>
										<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="checkbox" id="bank_transfer_bank_name" name="payment_gateway_settings[bank_transfer][fields][bank_name]" value="1" <?php checked( $gateway_options['fields']['bank_name'], 1 ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?>>
										<span><?php esc_html_e( 'Bank Name', 'armember-membership' ); ?></span>
									</label>
									<label>
										<?php $gateway_options['fields']['account_name'] = ( isset( $gateway_options['fields']['account_name'] ) ) ? $gateway_options['fields']['account_name'] : ''; ?>
										<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="checkbox" id="bank_transfer_account_name" name="payment_gateway_settings[bank_transfer][fields][account_name]" value="1" <?php checked( $gateway_options['fields']['account_name'], 1 ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?>>
										<span><?php esc_html_e( 'Account Holder Name', 'armember-membership' ); ?></span>
									</label>
									<label>
										<?php $gateway_options['fields']['additional_info'] = ( isset( $gateway_options['fields']['additional_info'] ) ) ? $gateway_options['fields']['additional_info'] : ''; ?>
										<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="checkbox" id="bank_transfer_additional_info" name="payment_gateway_settings[bank_transfer][fields][additional_info]" value="1" <?php checked( $gateway_options['fields']['additional_info'], 1 ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?>>
										<span><?php esc_html_e( 'Additional Info/Note', 'armember-membership' ); ?></span>
									</label>
									<label>
										<?php $gateway_options['fields']['transfer_mode'] = ( isset( $gateway_options['fields']['transfer_mode'] ) ) ? $gateway_options['fields']['transfer_mode'] : ''; ?>
										<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="checkbox" id="bank_transfer_mode" name="payment_gateway_settings[bank_transfer][fields][transfer_mode]" value="1" <?php checked( $gateway_options['fields']['transfer_mode'], 1 ); ?> <?php echo $disabled_field_attr; //phpcs:ignore ?>>
										<span><?php esc_html_e( 'Payment Mode', 'armember-membership' ); ?></span>
									</label>
									<?php
									global $arm_payment_gateways;
									$arm_transfer_mode   = $arm_payment_gateways->arm_get_bank_transfer_mode_options();
									$transfer_mode_style = ( ! empty( $gateway_options['fields']['transfer_mode'] ) && $gateway_options['fields']['transfer_mode'] == 1 ) ? 'style="display:block;"' : '';
									?>
									<div class="arm_transfer_mode_main_container" <?php echo esc_attr($transfer_mode_style); //phpcs:ignore ?>>
										<div class="arm_margin_top_24">
											<?php
												$bank_transfer_mode_option = ( isset( $gateway_options['fields']['transfer_mode_option'] ) ) ? $gateway_options['fields']['transfer_mode_option'] : array();

											foreach ( $arm_transfer_mode as $key => $transfer_mode ) {
												$is_checked_option = '';
												if ( in_array( $key, $bank_transfer_mode_option ) ) {
													$is_checked_option = 'checked="checked"';
												}

												$transfer_mode_val = isset( $gateway_options['fields']['transfer_mode_option_label'][ $key ] ) ? stripslashes($gateway_options['fields']['transfer_mode_option_label'][ $key ]) : $transfer_mode;
												?>
												
													<div class="arm_transfer_mode_list_container">
														<input class="arm_general_input arm_icheckbox arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" type="checkbox" id="bank_transfer_mode_option" name="payment_gateway_settings[bank_transfer][fields][transfer_mode_option][]" value="<?php echo esc_attr($key); ?>" <?php echo $is_checked_option; //phpcs:ignore ?> <?php echo $disabled_field_attr; //phpcs:ignore ?> data-msg-required="<?php esc_attr_e( 'Please select Payment Mode option.', 'armember-membership' ); ?>">
													<input class="arm_bank_transfer_mode_option_label arm_max_width_360" type="text" name="payment_gateway_settings[bank_transfer][fields][transfer_mode_option_label][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($transfer_mode_val); ?>" >
													</div>
												<?php
											}
											?>
										</div>
									</div>
								</div>
							</div>

							<div class="arm_email_setting_flex_group arm_margin_top_32 arm_payment_getway_page">
								<div class="arm_form_field_block">
									<label class="arm-form-table-label payment_label"><?php esc_html_e( 'Transaction ID Label', 'armember-membership' ); ?></label>
									<input class="arm_active_payment_<?php echo strtolower( $gateway_name ); //phpcs:ignore ?>" id="arm_bank_transfer_transaction_id_label" type="text" name="payment_gateway_settings[bank_transfer][transaction_id_label]" value="<?php echo ( ! empty( $gateway_options['transaction_id_label'] ) ? esc_attr( stripslashes( $gateway_options['transaction_id_label'] ) ) : esc_html__( 'Transaction ID', 'armember-membership' ) ); ?>" data-msg-required="<?php esc_attr_e( 'Transaction ID Label can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
								</div>

								<div class="arm_form_field_block">
									<label class="arm-form-table-label payment_label"><?php esc_html_e( 'Bank Name Label', 'armember-membership' ); ?></label>
									<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" id="arm_bank_transfer_bank_name_label" type="text" name="payment_gateway_settings[bank_transfer][bank_name_label]" value="<?php echo ( ! empty( $gateway_options['bank_name_label'] ) ? esc_attr( stripslashes( $gateway_options['bank_name_label'] ) ) : esc_html__( 'Bank Name', 'armember-membership' ) ); ?>" data-msg-required="<?php esc_html_e( 'Bank Name Label can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
								</div>

								<div class="arm_form_field_block">
									<label class="arm-form-table-label "><?php esc_html_e( 'Account Holder Name Label', 'armember-membership' ); ?></label>
									<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" id="arm_bank_transfer_account_name_label" type="text" name="payment_gateway_settings[bank_transfer][account_name_label]" value="<?php echo ( ! empty( $gateway_options['account_name_label'] ) ? esc_html( stripslashes( $gateway_options['account_name_label'] ) ) : esc_html__( 'Account Holder Name', 'armember-membership' ) ); ?>" data-msg-required="<?php esc_html_e( 'Account Holder Name Label can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
								</div>

								<div class="arm_form_field_block">
									<label class="arm-form-table-label payment_label"><?php esc_html_e( 'Additional Info/Note Label', 'armember-membership' ); ?></label>
									<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" id="arm_bank_transfer_additional_info_label" type="text" name="payment_gateway_settings[bank_transfer][additional_info_label]" value="<?php echo ( ! empty( $gateway_options['additional_info_label'] ) ? esc_attr( stripslashes( $gateway_options['additional_info_label'] ) ) : esc_html__( 'Additional Info/Note', 'armember-membership' ) ); ?>" data-msg-required="<?php esc_attr_e( 'Additional Info/Note Label can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
								</div>

								<div class="arm_form_field_block">
									<label class="arm-form-table-label payment_label"><?php esc_html_e( 'Payment Method Label', 'armember-membership' ); ?></label>
									<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?>" id="arm_bank_transfer_payment_mode_label" type="text" name="payment_gateway_settings[bank_transfer][transfer_mode_label]" value="<?php echo ( ! empty( $gateway_options['transfer_mode_label'] ) ? esc_attr( stripslashes( $gateway_options['transfer_mode_label'] ) ) : esc_html__( 'Payment Mode', 'armember-membership' ) ); ?>" data-msg-required="<?php esc_attr_e( 'Payment Mode Label can not be left blank.', 'armember-membership' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
								</div>

								<div class="arm_form_field_block">
									<label class="arm-form-table-label "><?php esc_html_e( 'Currency', 'armember-membership' ); ?></label>
									<div class="arm_margin_top_12">
									
									<span class="arm_payment_gateway_currency_label "><?php echo esc_html($global_currency); ?><?php echo ' ( ' . esc_html($global_currency_symbol) . ' ) '; ?></span>
									<a class="arm_payment_gateway_currency_link arm_ref_info_links" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '#changeCurrency' ) ); //phpcs:ignore ?>"><?php esc_html_e( 'Change currency', 'armember-membership' ); ?></a>			
								</div>
								</div>
							</div>
							
							<?php if($ARMemberLite->is_arm_pro_active)
							{
								$gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] = isset($gateway_options['arm_bank_transfer_do_not_allow_pending_transaction']) ? $gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] : 0;
								$arm_bank_transfer_allow_switchChecked = ($gateway_options['arm_bank_transfer_do_not_allow_pending_transaction'] == '1') ? 'checked="checked"' : "" ;
							?>
							<div class="form-field arm_sidebar_content_header arm_margin_top_32 arm_margin_bottom_0">
								<label class="arm-form-table-label"><?php esc_html_e('Do not allow user to submit transaction data more than one time', 'armember-membership');?></label>
								<div class="armswitch arm_payment_setting_switch arm_payment_<?php echo esc_attr($gateway_name); ?>_display_switch arm_text_align_right arm_margin_right_0">
									<input type="checkbox" id="arm_<?php echo esc_attr($gateway_name); ?>_do_not_allow_pending_transaction_switch_status" <?php echo $arm_bank_transfer_allow_switchChecked; //phpcs:ignore?> value="1" class="armswitch_input arm_active_payment_<?php echo esc_attr($gateway_name); ?>" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][arm_bank_transfer_do_not_allow_pending_transaction]" <?php echo $disabled_field_attr; //phpcs:ignore?>/>
									<label for="arm_<?php echo esc_attr($gateway_name); ?>_do_not_allow_pending_transaction_switch_status" class="armswitch_label arm_active_payment_<?php echo esc_attr($gateway_name); ?>" <?php echo $readonly_field_attr; //phpcs:ignore?>></label>
								</div>
							</div>
							</div>
							<?php
							}
						}
						break;
					default:
						break;
				}
				// do_action( 'arm_after_payment_gateway_listing_section', $gateway_name, $gateway_options );
				$payment_gateway_content = '';
				echo apply_filters('arm_after_payment_gateway_listing_content', $payment_gateway_content, $gateway_name, $gateway_options); //phpcs:ignore
				$pgHasCCFields = apply_filters( 'arm_payment_gateway_has_ccfields', false, $gateway_name, $gateway_options );
				$arm_allowed_cc_fields_gateways = apply_filters('arm_allow_ccfields_settings', false, $gateway_name, $gateway_options); 
				if ( $pgHasCCFields || $arm_allowed_cc_fields_gateways) {
					$arm_gateway_class_name = '';
					$arm_gateway_class_name = apply_filters('arm_allow_ccfields_class_names', $arm_gateway_class_name, $gateway_name, $gateway_options);
					?>
					<?php
						$arm_card_holder_label_filter = apply_filters('arm_payment_card_holder_filter', $allowed_arr = array(), $gateway_name);

						if( $ARMemberLite->is_arm_pro_active && ($gateway_name == 'stripe' || $gateway_name == 'paypal_pro' || $gateway_name == 'online_worldpay' || in_array($gateway_name, $arm_card_holder_label_filter))) {
					?>
					
						<div class="arm_form_field_block  <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<div class="arm-form-table-label">
								<label><?php esc_html_e('Card Holder Name Label', 'armember-membership');?></label>
							</div>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name);?>_cc_label_name" data-id="arm_payment_gateway_<?php echo esc_attr($gateway_name);?>_cc_label" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name);?>][card_holder_name]" value="<?php echo (!empty($gateway_options['card_holder_name']) ? esc_html(stripslashes($gateway_options['card_holder_name'])) : esc_html__('Card Holder Name', 'armember-membership')); //phpcs:ignore?>" <?php echo $readonly_field_attr; //phpcs:ignore?>>
							</div>
						</div>
						<div class="arm_form_field_block  <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<div class="arm-form-table-label">
								<label><?php esc_html_e('Card Holder Name Description', 'armember-membership');?></label>
							</div>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name);?>_cc_label_desc" data-id="arm_payment_gateway_<?php echo esc_attr($gateway_name);?>_cc_label" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name);?>][card_holder_name_description]" value="<?php echo (!empty($gateway_options['card_holder_name_description']) ? esc_html(stripslashes($gateway_options['card_holder_name_description'])) : ""); //phpcs:ignore?>" <?php echo $readonly_field_attr; //phpcs:ignore?>>
							</div>
						</div>
					<?php							
						}
					?>
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'Credit Card Label', 'armember-membership' ); ?></label>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'This label will be displayed at fronted membership setup wizard page while payment.', 'armember-membership' ); ?>"></i>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_cc_label" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][cc_label]" value="<?php echo ( ! empty( $gateway_options['cc_label'] ) ? esc_attr( stripslashes( $gateway_options['cc_label'] ) ) : esc_attr__( 'Credit Card Number', 'armember-membership' ) ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'Credit Card Description', 'armember-membership' ); ?></label>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_cc_desc" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][cc_desc]" value="<?php echo ( ! empty( $gateway_options['cc_desc'] ) ? esc_attr( stripslashes( $gateway_options['cc_desc'] ) ) : '' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'Expire Month Label', 'armember-membership' ); ?></label>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'This label will be displayed at fronted membership setup wizard page while payment.', 'armember-membership' ); ?>"></i>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_em_label" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][em_label]" value="<?php echo ( ! empty( $gateway_options['em_label'] ) ? esc_attr( stripslashes( $gateway_options['em_label'] ) ) : esc_attr__( 'Expiration Month', 'armember-membership' ) ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'Expire Month Description', 'armember-membership' ); ?></label>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_em_desc" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][em_desc]" value="<?php echo ( ! empty( $gateway_options['em_desc'] ) ? esc_attr( stripslashes( $gateway_options['em_desc'] ) ) : '' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
				
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'Expire Year Label', 'armember-membership' ); ?></label>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'This label will be displayed at fronted membership setup wizard page while payment.', 'armember-membership' ); ?>"></i>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( $gateway_name ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_ey_label" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][ey_label]" value="<?php echo ( ! empty( $gateway_options['ey_label'] ) ? esc_attr( stripslashes( $gateway_options['ey_label'] ) ) : esc_attr__( 'Expiration Year', 'armember-membership' ) ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'Expire Year Description', 'armember-membership' ); ?></label>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( $gateway_name ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_ey_desc" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][ey_desc]" value="<?php echo ( ! empty( $gateway_options['ey_desc'] ) ? esc_attr( stripslashes( $gateway_options['ey_desc'] ) ) : '' ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> arm_width_100_pct">
							<label class="arm-form-table-label"><?php esc_html_e( 'CVV Label', 'armember-membership' ); ?></label>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'This label will be displayed at fronted membership setup wizard page while payment.', 'armember-membership' ); ?>"></i>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( $gateway_name ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_cvv_label" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][cvv_label]" value="<?php echo ( ! empty( $gateway_options['cvv_label'] ) ? esc_attr( stripslashes( $gateway_options['cvv_label'] ) ) : esc_attr__( 'CVV Code', 'armember-membership' ) ); ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>

						<div class="arm_form_field_block <?php echo esc_attr($arm_gateway_class_name);?> ">
							<label class="arm-form-table-label"><?php esc_html_e( 'CVV Description', 'armember-membership' ); ?></label>
							<div class="arm-form-table-content">
								<input class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore ?> arm_margin_top_12" id="arm_payment_gateway_<?php echo esc_attr($gateway_name); ?>_cvv_desc" type="text" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][cvv_desc]" value="<?php echo ( ! empty( $gateway_options['cvv_desc'] ) ? esc_attr( stripslashes( $gateway_options['cvv_desc'] ) ) : '' ); //phpcs:ignore ?>" <?php echo $readonly_field_attr; //phpcs:ignore ?>>
							</div>
						</div>
					
					<div class="arm_form_field_block">
								<label class="arm-form-table-label arm_width_100_pct"><?php esc_html_e( 'Currency', 'armember-membership' )?></label>
								<div class="arm_margin_top_12">
									
									<span class="arm_payment_gateway_currency_label "><?php echo esc_html($global_currency).' ( ' . esc_html($global_currency_symbol) . ' )';?> </span>
									<a class="arm_payment_gateway_currency_link arm_ref_info_links" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '#changeCurrency' ) ) ?>"><?php esc_html_e( 'Change currency', 'armember-membership' );?></a>			
								</div>
							</div>
						</div>
					<?php
					if($ARMemberLite->is_arm_pro_active && ($gateway_name == "stripe" || $gateway_name == "authorize_net" || $gateway_name == "paypal_pro" || $gateway_name == "online_worldpay") ) {
						$gateway_options['enable_debug_mode'] = isset($gateway_options['enable_debug_mode']) ? $gateway_options['enable_debug_mode'] : 0;
						$arm_debug_mode_switchChecked = ($gateway_options['enable_debug_mode'] == '1') ? 'checked="checked"' : "" ;
					?>
						<div class="form-field arm_sidebar_content_header arm_margin_top_32">
							<label class="arm-form-table-label">
								<?php esc_html_e('Display actual error returned from payment gateway', 'armember-membership');?>
							</label>
							<div class="arm-form-table-content">
								<div class="armswitch arm_payment_setting_switch arm_payment_<?php echo esc_attr($gateway_name); ?>_display_switch arm_margin_right_0">
									<input type="checkbox" id="arm_<?php echo esc_attr($gateway_name); ?>_debug_mode_switch_status" <?php echo $arm_debug_mode_switchChecked; //phpcs:ignore?> value="1" class="armswitch_input arm_active_payment_<?php echo esc_attr($gateway_name); ?>" name="payment_gateway_settings[<?php echo esc_attr($gateway_name); ?>][enable_debug_mode]" <?php echo $disabled_field_attr; //phpcs:ignore?>/>
									<label for="arm_<?php echo esc_attr($gateway_name); ?>_debug_mode_switch_status" class="armswitch_label arm_active_payment_<?php echo esc_attr($gateway_name); ?>" <?php echo $readonly_field_attr; //phpcs:ignore?>></label>
								</div>
							</div>
						</div>

						
				<?php 
					}
				}
				do_action( 'arm_payment_gateway_add_ccfields', $gateway_name, $gateway_options, $readonly_field_attr );

				$arm_is_mycred_feature = get_option('arm_is_mycred_feature');
				$arm_ismyCREDFeature = ($arm_is_mycred_feature == '1') ? true : false;
				if($arm_ismyCREDFeature && $gateway_name == "mycred" && $ARMemberLite->is_arm_pro_active) {
					$point_exchange = 1;
					if(!empty($gateway_options['point_exchange'])) {
						$point_exchange = $gateway_options['point_exchange'];
					}
					$point_exchange = number_format((float)$point_exchange, 3, '.', '');
				?>	
				
				<div class="arm_payment_gateway_section arm_mycred_main_container <?php echo ( $gateway_options['status'] == '1' ) ? '' : ' hidden_section'; ?>">
				<div class="arm_content_border arm_margin_top_24"></div>
					<div class="form-field arm_margin_top_24 arm_display_flex">
		                <label class="arm-form-table-label arm_font_weight_600" style="color:var(--arm-dt-black-600)"><?php echo sprintf(esc_html__('%d Point', 'armember-membership'), 1); //phpcs:ignore?> = </label>
						
		                <div class="arm-form-table-content">
		                    <input type="text" class="arm_active_payment_<?php echo strtolower( esc_attr($gateway_name) ); //phpcs:ignore?> arm_margin_left_10" id="arm_mycred_point_exchange" name="payment_gateway_settings[mycred][point_exchange]" value="<?php echo esc_attr($point_exchange); ?>">
		                </div>
		            </div>
				</div>
				<?php							
				} ?>
				
				<?php if ( ! empty( $apiCallbackUrlInfo ) ) : ?>
				<div>
					<span class="arm_info_text arm_margin_top_24"><?php echo $apiCallbackUrlInfo; //phpcs:ignore ?></span>
				</div>
				<?php endif; ?>
				</div>
			</table>
		<?php endforeach; ?>
			<div class="arm_submit_btn_container arm_apply_changes_btn_container">
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_pay_gate_settings_btn" type="submit" name="arm_pay_gate_settings_btn"><?php esc_html_e( 'Apply Changes', 'armember-membership' ); ?></button>
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</div>
		</form>
	</div>
</div>
