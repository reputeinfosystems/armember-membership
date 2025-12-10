<?php global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans,$arm_pay_per_post_feature; ?>
<div class="wrap arm_page arm_manage_members_main_wrapper">
	<div class="content_wrapper" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Manage Members', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_member_btn" href="javascript:void(0)"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/add_new_icon.svg"><span><?php esc_html_e( 'Add Member', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="arm_solid_divider"></div>
		<div class="arm_members_grid_container" id="arm_members_grid_container">
			<?php
			if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list_records.php' ) ) {
				include MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list_records.php';
			}
			?>
		</div>
		<?php
		global $arm_global_settings;
		/* **********./Begin Bulk Delete Member Popup/.********** */
		$bulk_delete_member_popup_content  = '<span class="arm_confirm_text">' . esc_html__( 'Are you sure you want to delete this member(s)?', 'armember-membership' );
		$bulk_delete_member_popup_content .= '<br/>' . esc_html__( 'If you will delete these member(s), their subscription will be removed.', 'armember-membership' ) . '</span>';
		$bulk_delete_member_popup_content .= '<span class="arm_change_plan_confirm_text">'.esc_html__("This action cannot be reverted, Are you sure you want to change membership plan of selected member(s)?",'armember-membership' ).'</span>';
		$arm_bulk_delete_member_ok_btn_cls = 'arm_bulk_delete_member_ok_btn';
		$arm_blk_ok_btn_lbl = esc_html__('Delete Member','armember-membership');
		if($ARMemberLite->is_arm_pro_active)
		{
		
			$bulk_delete_member_popup_content .= '<span class="arm_change_status_confirm_text">'.esc_html__( "Are you sure you want to change status of selected member(s)?", 'armember-membership' ).'</span>';
			$arm_bulk_delete_member_ok_btn_cls = 'arm_bulk_change_member_ok_btn';
		}
		$bulk_delete_member_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_member_popup_title = '<span class="arm_confirm_text">'.esc_html__('Delete Member(s)', 'armember-membership').'</span>';
		$bulk_delete_member_popup_title .= '<span class="arm_change_plan_confirm_text">'.esc_html__('Change Plan', 'armember-membership').'</span>';
		if($ARMemberLite->is_arm_pro_active)
		{
			$bulk_delete_member_popup_title .= '<span class="arm_change_status_confirm_text">'.esc_html__('Change Status', 'armember-membership').'</span>';
		}
		
		$bulk_delete_member_popup_arg      = array(
			'id'             => 'delete_bulk_form_message',
			'class'          => 'arm_delete_bulk_action_message delete_bulk_form_message',
			'title'          => $bulk_delete_member_popup_title,
			'content'        => $bulk_delete_member_popup_content,
			'button_id'      => 'arm_bulk_delete_member_ok_btn',
			'button_onclick' => "apply_member_bulk_action('bulk_delete_flag');",
			'ok_btn_text' => $arm_blk_ok_btn_lbl
			
		);
		echo $arm_global_settings->arm_get_bpopup_html( $bulk_delete_member_popup_arg ); //phpcs:ignore
		/*
		 **********./End Bulk Delete Member Popup/.********** */
		/* **********./Begin Bulk Member Change To Plan Popup/.********** */
		$bulk_member_change_plan_popup_content  = '<span class="arm_confirm_text">' . esc_html__( 'This action cannot be reverted, Are you sure you want to change membership plan of selected member(s)?', 'armember-membership' ) . '</span>';
		$bulk_member_change_plan_popup_content .= '<input type="hidden" value="false" id="bulk_change_plan_flag"/>';
		$arm_blk_ok_btn_lbl = esc_html__('Change Plan','armember-membership');
		$bulk_member_change_plan_popup_arg      = array(
			'id'             => 'change_plan_bulk_message',
			'class'          => 'change_plan_bulk_message',
			'title'          => esc_html__( 'Change Plan', 'armember-membership' ),
			'content'        => $bulk_member_change_plan_popup_content,
			'button_id'      => 'arm_bulk_member_change_plan_ok_btn',
			'button_onclick' => "apply_member_bulk_action('bulk_change_plan_flag');",
			'ok_btn_text' => $arm_blk_ok_btn_lbl
		);
		echo $arm_global_settings->arm_get_bpopup_html( $bulk_member_change_plan_popup_arg ); //phpcs:ignore
		/* **********./End Bulk Member Change To Plan Popup/.********** */
		?>
	</div>
</div>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
var arm_other_file_image = '<?php echo MEMBERSHIPLITE_IMAGES_URL . '/file_icon.svg';?>';
var ARM_IMAGE_URL = "<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>";
var ARM_DELETE_MEMBER_LBL = "<?php echo esc_html__('Delete Member', 'armember-membership'); //phpcs:ignore ?>";
var ARM_CHANGE_PLAN_LBL = "<?php echo esc_html__('Change Plan', 'armember-membership'); //phpcs:ignore ?>";
var ARM_CHANGE_STATUS_LBL = "<?php echo esc_html__('Change Status', 'armember-membership'); //phpcs:ignore ?>";
<?php if(isset($_GET['action']) && $_GET['action'] == 'edit_member' && !empty($_GET['id'])){
	$user_id = $_GET['id'];
	?>
	jQuery(window).on("load", function(){	
		var arm_form_uri = window.location.toString();
		if( arm_form_uri.indexOf("&action=edit_member") > 0 && typeof arm_open_edit_member_form == 'function') {
			arm_open_edit_member_form(<?php echo $user_id?>);
			var arm_frm_clean_uri = arm_form_uri.substring(0, arm_form_uri.indexOf("&"));
			window.history.replaceState({}, document.title, arm_frm_clean_uri);
		}
	});
<?php }?>
jQuery(window).on("load", function () {
	document.onkeypress = stopEnterKey;
});

jQuery(document).on("click","#cb-select-all-1",function () {
	jQuery('input[name="item-action[]"]').prop('checked', this.checked);
});

jQuery(document).on('click','input[name="item-action[]"]',function() {
	if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
		jQuery("#cb-select-all-1").prop("checked", true);
	}
	else {
		jQuery("#cb-select-all-1").prop("checked", false);
	}
});

jQuery(document).on('click', "#armember_datatable_wrapper .ColVis_Button:not(.ColVis_MasterButton)", function () {
	var form_id = jQuery('#arm_form_filter').val();
	var column_list = "";
	var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();

	var column_list_str = '';
	jQuery('#armember_datatable_wrapper .ColVis_Button:not(.ColVis_MasterButton)').each(function(){
		if(jQuery(this).hasClass('active'))
		{
			column_list_str += '1,';
		}
		else {
			column_list_str += '0,';
		}
		
	});
	
	var column_list = [[ column_list_str ]];
	if (form_id == '') { return false; }
	jQuery.ajax({
		type:"POST",
		url:__ARMAJAXURL,
		data:"action=arm_members_hide_column&form_id="+form_id+"&column_list="+column_list+"&_wpnonce="+_wpnonce,
		success: function (msg) {
			return false;
		}
	});
});
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>

<div class="arm_member_manage_plan_detail_popup popup_wrapper arm_import_user_list_detail_popup_wrapper <?php echo ( is_rtl() ) ? 'arm_page_rtl' : ''; ?>" style="width:1000px; min-height: 200px;">
	<form method="GET" id="arm_member_manage_plan_user_form" class="arm_admin_form">
		<div>
			<div class="popup_header">
				<span class="popup_close_btn arm_popup_close_btn arm_member_manage_plan_detail_close_btn"></span>
				<input type="hidden" id="arm_edit_plan_user_id" />
				<span class="add_rule_content"><?php esc_html_e( 'Manage Plans', 'armember-membership' ); ?> <span class="arm_manage_plans_username"></span></span>
			</div>
			<div class="popup_content_text arm_member_manage_plan_detail_popup_text arm_padding_top_12"  style="text-align:center;">
				
			<div style="width: 100%; margin: 45px auto;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>"></div>

			</div>
			<div class="armclear"></div>
		</div>
	</form>
</div>
<!-- SHOW popups -->
<?php
if($ARMemberLite->is_arm_pro_active && $arm_pay_per_post_feature->isPayPerPostFeature)
{
	$arm_paid_post_popup_wrapper_filter = '';
	echo apply_filters('arm_paid_post_wrapper_popup_filter',$arm_paid_post_popup_wrapper_filter);
}

if(!$ARMemberLite->is_arm_pro_active)
{
	$arm_get_arm_lite_display_bf_offers = get_option('arm_lite_display_bf_offers');
	if((!empty($_REQUEST['arm_upgrade_action']) && 'arm_upgrade_to_premium' == $_REQUEST['arm_upgrade_action']) || !empty($arm_get_arm_lite_display_bf_offers) ){ //phpcs:ignore

	global $arm_lite_bf_sale_start_time, $arm_lite_bf_sale_end_time;
	$arm_current_date_for_bf_popup = current_time('timestamp',true); //GMT-0 Timezone 
	
	if( $arm_lite_bf_sale_start_time <= $arm_current_date_for_bf_popup && $arm_lite_bf_sale_end_time >= $arm_current_date_for_bf_popup ) {

		if(!empty($arm_get_arm_lite_display_bf_offers))
		{
			update_option('arm_lite_display_bf_offers', 0);
		}
		echo $arm_global_settings->arm_get_plugin_upgrade_popup(); //phpcs:ignore
	?>
		<script type="text/javascript">
			jQuery(window).on("load", function () {
				armBpopup('arm_black_friday_bpopup');
			});
			jQuery(document).on('click','.popup_wrapper.arm_black_friday_bpopup .popup_content_text',function(){
				window.location.replace("https://www.armemberplugin.com/pricing/?utm_source=blackfriday_liteversionpopup&utm_medium=liteversion&utm_campaign=balckfriday");
			});
		</script>
	<?php
	}
	else if(!empty($_REQUEST['arm_upgrade_action']) && 'arm_upgrade_to_premium' == $_REQUEST['arm_upgrade_action'])	//phpcs:ignore
	{
		wp_redirect('https://www.armemberplugin.com/pricing/?utm_source=newsletter&utm_medium=email&utm_campaign=Armember_blackfriday_2025&utm_id=armember_1');
		exit;
	}
	}
}
?>

<?php
	echo $ARMemberLite->arm_get_need_help_html_content('started-armember'); //phpcs:ignore
?>