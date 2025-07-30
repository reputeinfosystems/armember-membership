<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_slugs, $arm_subscription_plans, $arm_manage_communication;

$arm_all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$template_list          = $arm_email_settings->arm_get_all_email_template();


$form_id   = 'arm_add_message_wrapper_frm';
$mid       = 0;
$edit_mode = false;
$msg_type  = 'on_new_subscription';

$get_page = isset($_GET['page']) ? sanitize_text_field(esc_attr( $_GET['page'] )) : ''; //phpcs:ignore
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.delete_box{float:left;}
	.ColVis_Button{ display: none !important;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(document).ready(function () {
	var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','armember-membership')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 entries','armember-membership')); //phpcs:ignore?>';
    var __ARM_to = '<?php echo addslashes(esc_html__('to','armember-membership')); //phpcs:ignore?>';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','armember-membership')); //phpcs:ignore?>';
    var __ARM_RECORDS = '<?php echo addslashes(esc_html__('entries','armember-membership')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','armember-membership')); //phpcs:ignore?>';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No email template found.','armember-membership')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','armember-membership')); //phpcs:ignore?>';
	jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
				"oLanguage": {
                    "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_RECORDS,
                    "sInfoEmpty": __ARM_Showing_empty,
                    "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_RECORDS,
                    "sEmptyTable": __ARM_NO_FOUND,
                    "sZeroRecords": __ARM_NO_MATCHING
				},
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth": false,
		"aaSorting": [],
		"aoColumnDefs": [
			{"bVisible": false, "aTargets": []},
			{"bSortable": false, "aTargets": [1]}
		],
		"language":{
			"searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
			"search":"",
		},
		"oColVis": {
			"aiExclude": [0]
		},
		"iDisplayLength": 50,
	});
		
		arm_load_communication_messages_list_grid();
	   
});

function arm_load_communication_list_filtered_grid(data)
{
	var tbl = jQuery('#armember_datatable_1').dataTable(); 
		
		tbl.fnDeleteRow(data);
	   
		jQuery('#armember_datatable_1').dataTable().fnDestroy();
		arm_load_communication_messages_list_grid();
}

function arm_load_communication_messages_list_grid() {
	jQuery('#armember_datatable_1').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"oLanguage": {
			"sEmptyTable": "No any automated email message found.",
			"sZeroRecords": "No matching records found."
		},
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth": false,
		"aaSorting": [],
		"aoColumnDefs": [
			{"bVisible": false, "aTargets": []},
			{"bSortable": false, "aTargets": [0, 2, 5]}
		],
		"language":{
			"searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
			"search":"",
		},
		"oColVis": {
			"aiExclude": [0, 5]
		},
				"fnDrawCallback": function () {
					jQuery("#cb-select-all-1").prop("checked", false);
				},
	});
		
		 var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
		  
	jQuery('div#armember_datatable_1_filter').parent().append(filter_box);
	jQuery('#arm_filter_wrapper').remove(); 
	
	}
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
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
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="armember_datatable">
					<thead>
						<tr>
							<!--<th class="center"><?php esc_html_e( 'ID', 'armember-membership' ); ?></th>-->
							<th><?php esc_html_e( 'Template Name', 'armember-membership' ); ?></th>
							<th class="arm_text_align_center arm_width_100" ><?php esc_html_e( 'Active', 'armember-membership' ); ?></th>
							<th class="arm_padding_left_10" style="text-align: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;"><?php esc_html_e( 'Subject', 'armember-membership' ); ?></th>
							<th class="armGridActionTD"></th>
						</tr>
					</thead>
					<tbody>
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
							<tr class="member_row_<?php echo intval($tempID); ?>">
								<!--<td class="center"><?php echo intval($tempID); ?></td>-->
								<td><a class="arm_edit_template_btn" href="javascript:void(0);" data-temp_id="<?php echo intval($tempID); ?>" data-href="<?php echo esc_url($edit_link); //phpcs:ignore ?>"><?php echo esc_html($email_template->arm_template_name); ?></a></td>
								<td class="center">
								<?php
									$switchChecked = ( $email_template->arm_template_status == 1 ) ? 'checked="checked"' : '';
									echo '<div class="armswitch">
										<input type="checkbox" class="armswitch_input arm_email_status_action" id="arm_email_status_input_' . intval($tempID) . '" value="1" data-item_id="' . intval($tempID) . '" ' . $switchChecked . '><label class="armswitch_label" for="arm_email_status_input_' . intval($tempID) . '"></label> <span class="arm_status_loader_img"></span></div>'; //phpcs:ignore
								?>
								</td>
								<td id="arm_email_template_subject_<?php echo intval($tempID); ?>"><?php echo esc_html( stripslashes( $email_template->arm_template_subject ) ); ?></td>
								<td class="armGridActionTD">
								<?php
									$gridAction  = "<div class='arm_grid_action_btn_container'>";
									$gridAction .= "<a class='arm_edit_template_btn' href='javascript:void(0);' data-temp_id='" . esc_attr($tempID) . "'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit.png' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit_hover.png';\" class='armhelptip' title='" . esc_html__( 'Edit Message', 'armember-membership' ) . "' onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit.png';\" /></a>"; //phpcs:ignore
									$gridAction .= '</div>';
									echo '<div class="arm_grid_action_wrapper">' . $gridAction . '</div>'; //phpcs:ignore
								?>
								</td>
							</tr>
						<?php } ?>
						<?php endif; ?>
					</tbody>
				</table>
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
<?php

if($ARMemberLite->is_arm_pro_active)
{
	$arm_email_notification_grids = '';
	echo apply_filters('arm_pro_email_notification_automated_notification',$arm_email_notification_grids); //phpcs:ignore
}
?>
</div>
<!--./******************** Add New Member Form ********************/.-->
<?php 
if($ARMemberLite->is_arm_pro_active)
{
	$arm_add_new_response_email = '';
	echo apply_filters('arm_pro_email_notification_automated_notification_form',$arm_add_new_response_email); //phpcs:ignore
}
else
{?>

<div class="add_edit_message_wrapper_container"></div>
<div class="edit_email_template_wrapper popup_wrapper" >
	<form method="post" id="arm_edit_email_temp_frm" class="arm_admin_form arm_responses_message_wrapper_frm" action="#" onsubmit="return false;">
		<input type='hidden' name="arm_template_id" id="arm_template_id" value="0"/>
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="edit_template_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e( 'Edit Email Template', 'armember-membership' ); ?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr class="">
							<th><?php esc_html_e( 'Subject', 'armember-membership' ); ?></th>
							<td>
								<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject" id="arm_template_subject" value="" data-msg-required="<?php esc_attr_e( 'Email Subject Required.', 'armember-membership' ); ?>"/>
							</td>
						</tr>
						<tr class="form-field">
							<th><?php esc_html_e( 'Message', 'armember-membership' ); ?></th>
							<td>
								<div class="arm_email_content_area_left">
								<?php
								$email_setting_editor = array(
									'textarea_name'  => 'arm_template_content',
									'editor_class'   => 'arm_message_content',
									'media_buttons'  => false,
									'textarea_rows'  => 5,
									'default_editor' => 'html',
									'editor_css'     => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
								);
								wp_editor( '', 'arm_template_content', $email_setting_editor );
								?>
									<span id="arm_responses_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e( 'Content Cannot Be Empty.', 'armember-membership' ); ?></span>
								</div>
								<div class="arm_email_content_area_right">
									<span class="arm_sec_head"><?php esc_html_e( 'Template Tags', 'armember-membership' ); ?></span>
									<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_ADMIN_EMAIL}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Admin Email', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the admin email that users can contact you at. You can configure it under Mail settings.', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOGNAME}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Blog Name', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays blog name', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_URL}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Blog URL', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays blog URL', 'armember-membership' ); ?>"></i>
										</div>
										<!--									<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_ADMIN}" title="<?php esc_html_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Blog Admin', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon fa fa-question-circle" title="<?php esc_html_e( 'Displays blog WP-admin URL', 'armember-membership' ); ?>"></i>
										</div>-->
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LOGIN_URL}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Login URL', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the ARM login page', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERNAME}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Username', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the Username of user', 'armember-membership' ); ?>"></i>
										</div>
															<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USER_ID}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'User ID', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the User ID of user', 'armember-membership' ); ?>"></i>
										</div>
															<div class="arm_shortcode_row arm_email_code_reset_password">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_RESET_PASSWORD_LINK}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Reset Password Link', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the Reset Password Link for user', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_FIRST_NAME}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'First Name', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the user first name', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LAST_NAME}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Last Name', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the user last name', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_NAME}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Display Name', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the user display name or public name', 'armember-membership' ); ?>"></i>
										</div>                                        
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_EMAIL}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Email', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the E-mail address of user', 'armember-membership' ); ?>"></i>
										</div>                                        
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'User Profile Link', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the User Profile address', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_VALIDATE_URL}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Validation URL', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'The account validation URL that user receives after signing up (If you enable e-mail validation feature)', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERMETA_meta_key}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'User Meta Key', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr_e( "To Display User's meta field value.", 'armember-membership' ) . ' (' . esc_attr__( 'Where', 'armember-membership' ) . ' `meta_key` ' . esc_attr__( 'is meta field name.', 'armember-membership' ) . ')'; ?>"></i>
										</div>
										
										<div class="arm_shortcode_row arm_email_code_plan_name">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Plan Name', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the plan name of user', 'armember-membership' ); ?>"></i>
										</div>										
										<div class="arm_shortcode_row arm_email_code_plan_desc">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DESCRIPTION}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Plan Description', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the plan description of user', 'armember-membership' ); ?>"></i>
										</div>	
										<div class="arm_shortcode_row arm_email_code_plan_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_AMOUNT}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Plan Amount', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the plan amount of user', 'armember-membership' ); ?>"></i>
										</div>
										
										<div class="arm_shortcode_row arm_email_code_trial_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRIAL_AMOUNT}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Trial Amount', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the trial amount of plan', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payable_amount">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYABLE_AMOUNT}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Payable Amount', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the Final Payable Amount of user', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payment_type">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_TYPE}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Payment Type', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the payment type of user', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_payment_gateway">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_GATEWAY}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Payment Gateway', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the payment gateway of user', 'armember-membership' ); ?>"></i>
										</div>
										<div class="arm_shortcode_row arm_email_code_transaction_id">
											<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRANSACTION_ID}" title="<?php esc_attr_e( 'Click to add shortcode in textarea', 'armember-membership' ); ?>"><?php esc_html_e( 'Transaction Id', 'armember-membership' ); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Displays the payment transaction Id of user', 'armember-membership' ); ?>"></i>
										</div>
																				<?php do_action( 'arm_email_notification_template_shortcode' ); ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>	
								<span class="arm-note-message --warning"><?php printf( esc_html__( 'NOTE : Please add %1$sbr%2$s to use line break in plain text.', 'armember-membership' ), '&lt;', '&gt;' ); //phpcs:ignore ?></span>
							</td>
						</tr>
					</table>
					<input type=hidden name="arm_template_status" id="arm_template_status" value=""/>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_temp" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;" width="20" height="20" />
						<button class="arm_save_btn" id="arm_email_template_submit" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
						<button class="arm_cancel_btn edit_template_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<?php }?>
<script type="text/javascript">
	__ARM_ADDNEWRESPONSE = '<?php esc_html_e( 'Add New Response', 'armember-membership' ); ?>';
	__ARM_VALUE = '<?php esc_html_e( 'Value', 'armember-membership' ); ?>';
</script>
