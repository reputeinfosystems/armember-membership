<?php global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_payment_gateways,$arm_subscription_plans; ?>
<div class="wrap arm_page arm_transactions_main_wrapper">
	<div class="content_wrapper arm_transactions_container" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Payment History', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<?php if($ARMemberLite->is_arm_pro_active)
				{ 
					if(!empty($_REQUEST['action'])){?>
					
						<a class="arm_export_paid_post_txn_btn arm_margin_right_10" href="javascript:void(0);" onmouseover="arm_change_export_csv(this,'<?php echo MEMBERSHIPLITE_IMAGES_URL;?>')" onmouseout="arm_change_export_csv_out(this,'<?php echo MEMBERSHIPLITE_IMAGES_URL;?>')"><img align="absmiddle" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>/arm_export_icon_pg.svg"><span><?php esc_html_e( 'Export as CSV', 'armember-membership' ); ?></span></a>
					<?php }else{?>
						<a class="arm_export_plan_txn_btn arm_margin_right_10" href="javascript:void(0);" onmouseover="arm_change_export_csv(this,'<?php echo MEMBERSHIPLITE_IMAGES_URL;?>')" onmouseout="arm_change_export_csv_out(this,'<?php echo MEMBERSHIPLITE_IMAGES_URL;?>')"><img align="absmiddle" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>/arm_export_icon_pg.svg"><span><?php esc_html_e( 'Export as CSV', 'armember-membership' ); ?></span></a>	
					<?php }
				}?>
				<a class="greensavebtn arm_add_manual_payment_btn" href="javascript:void(0);"><img align="absmiddle" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>/add_new_icon.svg"><span><?php esc_html_e( 'Add Manual Payment', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<?php
			$arm_spacing_div_cls = "arm_margin_top_20 arm_margin_bottom_10";
			if($ARMemberLite->is_arm_pro_active){			
				$arm_spacing_div_cls = "";
				$arm_tab_action = '';
				$arm_tab_action = apply_filters('arm_get_transaction_tab_actions',$arm_tab_action);
				echo $arm_tab_action;
			}
			?>
		<div class="arm_solid_divider arm_margin_top_0"></div>
		<div class="armclear"></div>
		<?php 
		$arm_transactions_grid_container_option = '';
		$arm_transactions_grid_container_option = apply_filters('arm_transaction_grid_list_options',$arm_transactions_grid_container_option);
		echo $arm_transactions_grid_container_option;
		?>
		<?php if(!$ARMemberLite->is_arm_pro_active || ($ARMemberLite->is_arm_pro_active && empty($_REQUEST['action'])) ) 
		{?>
			<div class="arm_transactions_grid_container" id="arm_transactions_grid_container">
				<?php
				if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_list_records.php' ) ) {
					include MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_list_records.php';
				}
				?>
			</div>
		<?php }
		if($ARMemberLite->is_arm_pro_active)
		{
			$arm_license_notice = '';
			echo apply_filters('arm_admin_paid_post_transaction_html',$arm_license_notice); //phpcs:ignore
		}
		?>
		<?php

		/* **********./Begin Change Transaction Status Popup/.********** */
		$change_transaction_status_popup_content  = '<span class="arm_confirm_text">' . esc_html__( 'Are you sure you want to change transaction status?', 'armember-membership' ) . '</span>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="log_id"/>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="log_status"/>';
		$change_transaction_status_popup_arg      = array(
			'id'             => 'change_transaction_status_message',
			'class'          => 'change_transaction_status_message',
			'title'          => esc_html__( 'Change Transaction Status', 'armember-membership' ),
			'content'        => $change_transaction_status_popup_content,
			'button_id'      => 'arm_change_transaction_status_ok_btn',
			'button_onclick' => 'arm_change_bank_transfer_status_func();',
		);
		echo $arm_global_settings->arm_get_bpopup_html( $change_transaction_status_popup_arg ); //phpcs:ignore
		/*
		 **********./End Change Transaction Status Popup/.********** */
		/* **********./Begin Bulk Delete Transaction Popup/.********** */
		$bulk_delete_transaction_popup_content  = '<span class="arm_confirm_text">' . esc_html__( 'Are you sure you want to delete this transaction(s)?', 'armember-membership' ) . '</span>';
		$bulk_delete_transaction_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_transaction_popup_arg      = array(
			'id'             => 'delete_bulk_transactions_message',
			'class'          => 'delete_bulk_transactions_message',
			'title'          => esc_html__( 'Delete Transaction(s)', 'armember-membership' ),
			'content'        => $bulk_delete_transaction_popup_content,
			'button_id'      => 'arm_bulk_delete_transactions_ok_btn',
			'button_onclick' => "apply_transactions_bulk_action('bulk_delete_flag');",
		);
		echo $arm_global_settings->arm_get_bpopup_html( $bulk_delete_transaction_popup_arg ); //phpcs:ignore
		/* **********./End Bulk Delete Transaction Popup/.********** */

		?>
		<div class="arm_invoice_detail_container">
			<?php $log_id = !empty($_REQUEST['log_id']) ? $_REQUEST['log_id'] : 0;?>
			<div class="arm_invoice_detail_popup popup_wrapper arm_invoice_detail_popup_wrapper">
                <div class="popup_wrapper_inner" style="overflow: hidden;">
                    <div class="popup_header arm_text_align_center" >
                        <span class="popup_close_btn arm_popup_close_btn arm_invoice_detail_close_btn"></span>
                        <span class="add_rule_content"><?php esc_html_e('Invoice Detail','armember-membership' );?></span>
                    </div>
                    <div class="popup_content_text arm_invoice_detail_popup_text arm_padding_24" id="arm_invoice_detail_popup_text" >
					</div>
                </div>
            </div>
		</div>
		<div class="arm_preview_log_detail_container">
			<div class="arm_preview_log_detail_popup popup_wrapper arm_preview_log_detail_popup_wrapper" style="width:600px;">
                <div class="popup_wrapper_inner" style="overflow: hidden;">
                    <div class="popup_header">
                        <span class="popup_close_btn arm_popup_close_btn arm_preview_log_detail_close_btn"></span>
                        <span class="add_rule_content"><?php esc_html_e( 'Transaction Details', 'armember-membership' ); ?></span>
                    </div>
                    <div class="popup_content_text arm_transactions_detail_popup_text"></div>
                    <div class="armclear"></div>
                </div>
            </div>
		</div>
		<div class="arm_preview_failed_log_detail_container"></div>
	</div>
	
</div>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.arm_status_filter_label, .arm_status_filter_label select{min-width:120px;}
</style>
<script type="text/javascript" charset="utf-8">
	var ARM_IMAGE_URL = "<?php echo MEMBERSHIPLITE_IMAGES_URL;?>";
// <![CDATA[
jQuery(window).on("load", function () {
	document.onkeypress = stopEnterKey;
});
jQuery(document).on('click', ".ColVis_Button:not(.ColVis_MasterButton)", function () {
	
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
	column_list_str += '1';
	column_list = [[ column_list_str ]];
	jQuery.ajax({
		type:"POST",
		url:__ARMAJAXURL,
		data:"action=arm_transaction_hide_show_columns&column_list="+column_list+"&_wpnonce="+_wpnonce,
		success: function (msg) {
			return false;
		}
	});
});

jQuery(document).on('mouseover','.arm_export_paid_post_txn_btn',function(){
    jQuery(this).find('img').attr('src','<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_export_icon_pg_hover.svg');
});

jQuery(document).on('mouseover','.arm_export_plan_txn_btn',function(){
    jQuery(this).find('img').attr('src','<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_export_icon_pg_hover.svg');
});

jQuery(document).on('mouseout','.arm_export_paid_post_txn_btn',function(){
    jQuery(this).find('img').attr('src','<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/arm_export_icon_pg.svg');
});

jQuery(document).on('mouseout','.arm_export_plan_txn_btn',function(){
    jQuery(this).find('img').attr('src','<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/arm_export_icon_pg.svg');
});
// ]]>
</script>
<?php
	echo $ARMemberLite->arm_get_need_help_html_content('member-payment-history'); //phpcs:ignore
?>