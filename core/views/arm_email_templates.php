<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_slugs, $arm_subscription_plans, $arm_manage_communication;

$arm_all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$template_list          = $arm_email_settings->arm_get_all_email_template();


$form_id   = 'arm_add_message_wrapper_frm';
$mid       = 0;
$edit_mode = false;
$msg_type  = 'on_new_subscription';
$local = get_locale();
$local = apply_filters('arm_get_current_locale',$local);
$get_page = isset($_GET['page']) ? sanitize_text_field(esc_attr( $_GET['page'] )) : ''; //phpcs:ignore
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.delete_box{float:left;}
	.ColVis_Button{ display: none !important;}
	.wrap #armember_datatable_wrapper tr:not(.arm_child_user_row) td.armGridActionTD{
		padding: 5px !important;
	}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
// ]]>
</script>
<div class="arm_email_notifications_main_wrapper">
	<div class="page_sub_content">
		<div class="page_sub_title" style="float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;" ><?php esc_html_e( 'Standard Email Responses', 'armember-membership' ); ?></div>
		<?php $arm_pro_add_new_auto_message_btn = '';
			echo apply_filters('arm_pro_add_new_auto_messages_btn',$arm_pro_add_new_auto_message_btn); //phpcs:ignore
		?>
		<div class="armclear"></div>
		<div class="arm_email_templates_list">
			<form method="GET" id="email_templates_list_form" class="data_grid_list arm_email_settings_wrapper">
				<input type="hidden" name="page" value="<?php echo esc_attr($get_page); ?>" />
				<input type="hidden" name="armaction" value="list" />
				<div id="armmainformnewlist">
					<div class="response_messages"></div>
					<div class="armclear"></div>
					<div class="divTable arm_email_template_table">
						<div class="divTableHeading">
							<div class="divTableRow divTableRowheader arm_std_email_notification">
								<div class="divTableHead arm_padding_left_32"><?php esc_html_e('Active/Inactive','armember-membership'); ?></div>
								<div class="divTableHead"><?php esc_html_e('Template Name','armember-membership'); ?></div>
								<div class="divTableHead"><?php esc_html_e('Subject','armember-membership'); ?></div>
							</div>
						</div>
						<div class="divTableBody">
							<?php if ( ! empty( $template_list ) ) : ?>
								<?php foreach ( $template_list as $key => $email_template ) { ?>
									<?php
									if ( $email_template->arm_template_slug == 'follow-notification' || $email_template->arm_template_slug == 'unfollow-notification' ) {
										if ( ! $arm_social_feature->isSocialFeature ) {
											continue;
										}
									}
									if ( $email_template->arm_template_slug == 'email-verify-user' || $email_template->arm_template_slug == 'account-verified-user' ) {
										$user_register_verification = $arm_global_settings->arm_get_single_global_settings( 'user_register_verification' );
										if ( $user_register_verification != 'email' ) {
											continue;
										}
									}
									$tempID    = $email_template->arm_template_id;
									$edit_link = admin_url( 'admin.php?page=' . $arm_slugs->email_notifications . '&action=edit_template&template_id=' . $tempID );
									?>
									<div class="divTableRow member_row_<?php echo intval($tempID); ?> arm_std_email_notification" onmouseover="arm_datatable_row_hover('member_row_<?php echo intval($tempID); ?>','hovered')" onmouseleave="arm_datatable_row_hover('member_row_<?php echo intval($tempID); ?>');">
										<?php $is_status_active = ($email_template->arm_template_status == 1) ? 'checked="checked"' : '';?>
										<div class="divTableCell">
											<div class="armswitch">
												<input id="arm_email_status_input_<?php echo intval($tempID)?>" class="armswitch_input  arm_email_status_action" <?php echo $is_status_active;?> type="checkbox" value="1" data-item_id="<?php echo intval($tempID)?>" /><label class="armswitch_label" for="arm_email_status_input_<?php echo intval($tempID)?>"></label>
												<span class="arm_status_loader_img"></span>
											</div>
										</div>
										<div class="divTableCell">
											<?php echo esc_html($email_template->arm_template_name); ?>
										</div>
										<div class="divTableCell"><?php echo esc_html( stripslashes( $email_template->arm_template_subject ) ); ?></div>
										<div class="divTableCell arm_grid_action_wrapper hidden_section">
											<div class="arm_grid_action_wrapper arm_margin_top_12">
												<div class="arm_grid_action_btn_container arm_margin_right_24">
												<?php
													$gridAction  = "<div class='arm_grid_action_btn_container'>";
													$gridAction .= "<a class='arm_edit_template_btn arm_margin_right_5' href='javascript:void(0);' data-temp_id='" . esc_attr($tempID) . "'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit.svg' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit_hover.svg';\" class='armhelptip' title='" . esc_html__( 'Edit Message', 'armember-membership' ) . "' onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit.svg';\" /></a>"; //phpcs:ignore

									$gridAction .= "<a class='arm_test_mail_btn' href='javascript:void(0);' data-temp_id='" . esc_attr($tempID) . "'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/resend_mail_icon.svg' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/resend_mail_icon_hover.svg';\" class='armhelptip' title='" . esc_html__( 'Send Test Mail', 'armember-membership' ) . "' onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/resend_mail_icon.svg';\" /></a>"; //phpcs:ignore
													$gridAction .= '</div>';
													echo '<div class="arm_grid_action_wrapper">' . $gridAction . '</div>'; //phpcs:ignore
												?>
												</div>
											</div>
										</div>
									</div>
							<?php } ?>
						<?php endif; ?>
						</div>
					</div>
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e( 'Show / Hide columns', 'armember-membership' ); ?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e( 'messages', 'armember-membership' ); ?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e( 'Show', 'armember-membership' ); ?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e( 'Showing', 'armember-membership' ); ?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e( 'to', 'armember-membership' ); ?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e( 'of', 'armember-membership' ); ?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e( 'No matching templates found.', 'armember-membership' ); ?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e( 'No any email template found.', 'armember-membership' ); ?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e( 'filtered from', 'armember-membership' ); ?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e( 'total', 'armember-membership' ); ?>"/>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
				<div class="footer_grid"></div>
			</form>
			<div class="armclear"></div>
		</div>
	</div>
</div>
<!--./******************** Add New Member Form ********************/.-->

<div class="add_edit_message_wrapper_container"></div>
<div class="edit_email_template_wrapper arm_common_setting_multi_language_wrapper popup_wrapper" >
	<div class="arm_member_popup_header_wrapper">
		<div class="popup_header page_title">
			<span class="arm_edit_standard_mail_template_span"><?php esc_html_e('Edit Email Template','armember-membership');?></span>
			<span class="arm_popup_close_btn edit_template_close_btn"></span>
		</div>
	</div>
	<form method="post" id="arm_edit_email_temp_frm" class="arm_admin_form arm_responses_message_wrapper_frm arm_padding_top_0" action="#" onsubmit="return false;">
		<div class="arm_add_new_message_wrapper">
		<input type='hidden' name="arm_template_id" id="arm_template_id" value="0"/>
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="popup_content_text">
				<?php 
					$arm_table_top_class= 'arm_padding_top_24';
					$email_notifications_page_language_setting = apply_filters('arm_email_notifications_page_language_setting',array());
					$all_supported_language = apply_filters('arm_get_armember_multi_languages_list',array());
					$default_supported_language = apply_filters('arm_get_default_multi_languages',array());
				
					if(empty($all_supported_language)){
						$email_notifications_page_language_setting = $all_supported_language;
					}else{
						$arm_table_top_class= 'arm_padding_top_0';
						if(!empty($default_supported_language)){
							$all_supported_language = array_merge($all_supported_language,$default_supported_language);
						}
					}?>
					<table class="arm_table_label_on_top <?php echo $arm_table_top_class;?>">	
						<?php 						
							if(empty($email_notifications_page_language_setting)){ ?>
								<tr class="arm_membership_plan_selection_row arm_margin_top_5">
									<td>								
										<div class="arm_font_size_20 arm-black-600 arm_font_weight_500"><?php esc_html_e('Subject', 'armember-membership'); ?></div>
									</td>
								</tr>
								<tr class="arm_width_33_pct">
									<th class="arm_margin_top_24 arm_margin_bottom_12"><?php esc_html_e( 'Email Notification Subject', 'armember-membership' ); ?></th>
									<td>
										<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject" id="arm_template_subject" value="" data-msg-required="<?php esc_attr_e('Email Subject Required.', 'armember-membership');?>"/>
									</td>
								</tr>
								<tr class="arm_membership_plan_selection_row">						
									<td>								
										<div class="arm_solid_divider arm_margin_top_32"></div>
										<div class="arm_font_size_20 arm-black-600 arm_font_weight_500 arm_margin_top_32"><?php esc_html_e('Message', 'armember-membership'); ?></div>
									</td>
								</tr>
								<tr class="form-field arm_display_grid arm_grid_col_70_30">
									<th class="arm_email_content_area_left arm_margin_top_24 arm_margin_bottom_12">
										<?php esc_html_e( 'Email Notification Subject', 'armember-membership' ); ?>
									</th>
									<th class="arm_email_content_area_left arm_margin_top_24 arm_margin_bottom_12">
										<?php esc_html_e( 'Email Template', 'armember-membership' ); ?>
									</th>
								</tr>
								<tr class="form-field">
									<td class="arm_email_template_grid arm_grid_col_70_30 arm_padding_bottom_0">
										<div class="arm_email_content_area_left">
										<?php 
										$email_setting_editor = array(
											'textarea_name' => 'arm_template_content',
											'editor_class' => 'arm_message_content',
											'media_buttons' => false,
											'textarea_rows' => 5,
											'default_editor' => 'html',
											'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
										);
										wp_editor('', 'arm_template_content', $email_setting_editor);
										?>
											<span id="arm_responses_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'armember-membership');?></span>
										</div>
										<div class="arm_email_content_area_right">
											<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_ADMIN_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Admin Email', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the admin email that users can contact you at. You can configure it under Mail settings.", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOGNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Blog Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog name', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Blog URL', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog URL', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LOGIN_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Login URL', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the ARM login page', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Username', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Username of user', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USER_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('User ID', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the User ID of user', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_reset_password">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_RESET_PASSWORD_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Reset Password Link', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Reset Password Link for user', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_FIRST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('First Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user first name', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LAST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Last Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user last name', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Display Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the user display name or public name", 'armember-membership'); ?>"></i>
												</div>                                        
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Email', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the E-mail address of user", 'armember-membership'); ?>"></i>
												</div>                                        
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('User Profile Link', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the User Profile address", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_VALIDATE_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Validation URL', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("The account validation URL that user receives after signing up (If you enable e-mail validation feature)", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERMETA_meta_key}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('User Meta Key', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr__("To Display User's meta field value.", 'armember-membership').' ('.esc_attr__("Where", 'armember-membership').' `meta_key` '.esc_attr__("is meta field name.", 'armember-membership').')';?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_name">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Plan Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan name of user', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_desc">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DESCRIPTION}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Plan Description', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan description of user', 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Plan Amount', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the plan amount of user", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_trial_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRIAL_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Trial Amount', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays tax amount", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payable_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYABLE_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Payable Amount', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the Final Payable Amount of user", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payment_type">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_TYPE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Payment Type', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment type of user", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payment_gateway">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_GATEWAY}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Payment Gateway', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment gateway of user", 'armember-membership'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_transaction_id">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRANSACTION_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Transaction Id', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment transaction Id of user", 'armember-membership'); ?>"></i>
												</div>

												<?php do_action("arm_email_notification_template_shortcode"); ?>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td>	
										<span class="arm-note-message --alert arm_margin_top_20"><?php printf( esc_html__( 'NOTE : Please add %1$sbr%2$s to use line break in plain text.', 'armember-membership' ), '&lt;', '&gt;' ); //phpcs:ignore ?></span>
									</td>
								</tr>
						
           					<?php }else{ ?>
								<div class="form-table arm_table_label_on_top arm_padding_top_32 arm_margin_bottom_10">	
								<div class="arm_general_settings_tab_wrapper arm_width_auto">
									<?php 
									
									if(in_array($local,$email_notifications_page_language_setting)){
										unset($email_notifications_page_language_setting[array_search($local,$email_notifications_page_language_setting)]);
									}
									array_unshift($email_notifications_page_language_setting,$local);
									foreach ($email_notifications_page_language_setting as $language){ ?>
										<a class="arm_general_settings_tab arm_general_settings_languages_tab arm_general_settings_languages_tab_standard" data-selected-value="<?php echo $language; ?>">&nbsp;<?php echo $all_supported_language[$language] ?>&nbsp;&nbsp;</a>
									<?php } ?>
								<div class="armclear"></div>
								</div>
								<?php 
								foreach ($email_notifications_page_language_setting as $lan) { ?>
								<tr class="arm_general_settings_multi_languages_standard_tab arm_width_auto arm_width_100_pct" data-multi-lang-value="<?php echo $lan; ?>">
									<td>
										<table class="arm_padding_0 arm_padding_top_0">
										<tr class="arm_membership_plan_selection_row arm_margin_top_0">
	<td>								
		<div class="arm_font_size_20 arm-black-600 arm_font_weight_500"><?php esc_html_e('Subject', 'armember-membership'); ?></div>
	</td>
</tr>
								<tr class="arm_width_33_pct">
									<th class="arm_margin_top_24 arm_margin_bottom_12"><?php esc_html_e( 'Email Notification Subject', 'armember-membership' ); ?></th>
												<td>
													<?php if($local==$lan){ ?>
														<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject" id="arm_template_subject" value="" data-msg-required="<?php esc_attr_e('Email Subject Required.', 'armember-membership');?>"/>
													<?php }else{ ?>
														<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject_translated[<?php echo $lan; ?>]" id="arm_template_subject_translated_<?php echo $lan; ?>" value="" data-msg-required="<?php esc_attr_e('Email Subject Required.', 'armember-membership');?>"/>
													<?php } ?>
												</td>
											</tr>
											<tr class="arm_membership_plan_selection_row">
												<td>								
													<div class="arm_solid_divider arm_margin_top_32"></div>
													<div class="arm_font_size_20 arm-black-600 arm_font_weight_500 arm_margin_top_32"><?php esc_html_e('Message', 'armember-membership'); ?></div>
												</td>
											</tr>
											<tr class="form-field arm_display_grid arm_grid_col_70_30">
												<th class="arm_email_content_area_left arm_margin_top_24 arm_margin_bottom_12">
													<?php esc_html_e( 'Email Notification Subject', 'armember-membership' ); ?>
												</th>
												<th class="arm_email_content_area_left arm_margin_top_24 arm_margin_bottom_12">
													<?php esc_html_e( 'Email Template', 'armember-membership' ); ?>
												</th>
											</tr>
											<tr class="form-field arm_auto_message_content">
												<td class="arm_display_grid arm_grid_col_70_30">
													<div class="arm_email_content_area_left">
													<?php 
													if($local==$lan){
														
														$email_setting_editor = array(
															'textarea_name' => 'arm_template_content',
															'editor_class' => 'arm_message_content',
															'media_buttons' => false,
															'textarea_rows' => 5,
															'default_editor' => 'html',
															'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
														);
														wp_editor('', 'arm_template_content', $email_setting_editor);
													}else{
														$email_setting_editor = array(
															'textarea_name' => 'arm_template_content_translated['.$lan.']',
															'editor_class' => 'arm_message_content_translated_'.$lan,
															'media_buttons' => false,
															'textarea_rows' => 5,
															'default_editor' => 'html',
															'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
														);
														wp_editor('', 'arm_template_content_translated_'.$lan, $email_setting_editor);
		
													} ?>
														<span id="arm_responses_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'armember-membership');?></span>
													</div>
													<div class="arm_email_content_area_right">
														<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_ADMIN_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Admin Email', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the admin email that users can contact you at. You can configure it under Mail settings.", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOGNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Blog Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog name', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Blog URL', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog URL', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LOGIN_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Login URL', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the ARM login page', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Username', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Username of user', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USER_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('User ID', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the User ID of user', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_reset_password">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_RESET_PASSWORD_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Reset Password Link', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Reset Password Link for user', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_FIRST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('First Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user first name', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LAST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Last Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user last name', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Display Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the user display name or public name", 'armember-membership'); ?>"></i>
															</div>                                        
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Email', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the E-mail address of user", 'armember-membership'); ?>"></i>
															</div>                                        
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('User Profile Link', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the User Profile address", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_VALIDATE_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Validation URL', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("The account validation URL that user receives after signing up (If you enable e-mail validation feature)", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERMETA_meta_key}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('User Meta Key', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr__("To Display User's meta field value.", 'armember-membership').' ('.esc_attr__("Where", 'armember-membership').' `meta_key` '.esc_attr__("is meta field name.", 'armember-membership').')';?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_plan_name">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Plan Name', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan name of user', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_plan_desc">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DESCRIPTION}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Plan Description', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan description of user', 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_plan_amount">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Plan Amount', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the plan amount of user", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_trial_amount">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRIAL_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Trial Amount', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays tax amount", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_payable_amount">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYABLE_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Payable Amount', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the Final Payable Amount of user", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_payment_type">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_TYPE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Payment Type', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment type of user", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_payment_gateway">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_GATEWAY}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Payment Gateway', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment gateway of user", 'armember-membership'); ?>"></i>
															</div>
															<div class="arm_shortcode_row arm_email_code_transaction_id">
																<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRANSACTION_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'armember-membership'); ?>"><?php esc_html_e('Transaction Id', 'armember-membership'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment transaction Id of user", 'armember-membership'); ?>"></i>
															</div>

															<?php do_action("arm_email_notification_template_shortcode"); ?>
														</div>
													</div>
												</td>
											</tr>
											<tr>
												<td>	
													<span class="arm-note-message --alert arm_margin_top_20"><?php printf( esc_html__( 'NOTE : Please add %1$sbr%2$s to use line break in plain text.', 'armember-membership' ), '&lt;', '&gt;' ); //phpcs:ignore ?></span>
												</td>
											</tr>

										</table>
									</td>
								</tr>
								<?php } ?>
								<script>
									jQuery(document).ready(function () {
										jQuery('.arm_common_setting_multi_language_wrapper .arm_general_settings_languages_tab_standard:first').click();
									});
								</script>
						<?php } 
						$arm_field_html='';
						$arm_field_html=apply_filters('arm_add_standard_email_template_field_html',$arm_field_html);
						echo $arm_field_html; //phpcs:ignore
						?>
					</table>
					<input type=hidden name="arm_template_status" id="arm_template_status" value=""/>
					<div class="armclear"></div>
				</td>
			</tr>
		</table>
		</div>
		<div class="arm_submit_btn_container">
			<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_temp" class="arm_loader_img arm_submit_btn_loader" style="float: <?php echo ( is_rtl() ) ? 'right' : ''; ?> ;display: none;" width="20" height="20" />
			<button class="arm_cancel_btn edit_template_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>	
			<button class="arm_cancel_btn reset_befault_btn" id="arm_email_template_reset_default" type="button" onclick="document.getElementById('arm_confirm_box_reset_template_confirm_box')"><?php esc_html_e('Reset Default', 'armember-membership'); ?></button>
			<div id="arm_confirm_box_reset_template_confirm_box" class=" arm_confirm_box_reset_template arm_confirm_box">
				<div class="arm_confirm_box_body">
					<div class="arm_confirm_box_arrow"></div>
					<div class="arm_confirm_box_text_title"><?php esc_html_e('Reset email template', 'armember-membership' );?></div>
					<div class="arm_confirmbox_message">
						<?php echo esc_html__('Are you sure you want to reset this template to default?', 'armember-membership'); ?>
					</div>
					<div class="arm_confirmbox_actions arm_confirm_box_btn_container arm_display_flex" style="margin-top: 10px;">
						<button type="button" class="arm_confirm_box_btn armcancel" onclick='hideConfirmBoxCallback();'><?php esc_html_e('Cancel', 'armember-membership'); ?></button>
						<button type="button" class="arm_confirm_box_btn armok arm_margin_left_0" id="arm_confirmbox_confirm_reset"><?php esc_html_e('Reset', 'armember-membership'); ?></button>
					</div>
				</div>
			</div>
			<button class="arm_save_btn" id="arm_email_template_submit" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
		</div>
		<div class="armclear"></div>
	</form>
</div>
<script type="text/javascript">
	__ARM_ADDNEWRESPONSE = '<?php esc_html_e( 'Add New Response', 'armember-membership' ); ?>';
	__ARM_VALUE = '<?php esc_html_e( 'Value', 'armember-membership' ); ?>';
</script>
