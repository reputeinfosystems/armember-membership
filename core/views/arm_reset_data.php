<?php
if(! defined('ABSPATH')) exit;

global $ARMemberLite, $arm_reset_data, $arm_common_lite;

if ( empty( $arm_reset_data ) || ! is_object( $arm_reset_data ) || ! $arm_reset_data->arm_reset_data_can_access() ) {
	return;
}

$arm_rd_modules   = $arm_reset_data->arm_reset_data_get_modules();

$arm_rd_done_icon = MEMBERSHIPLITE_IMAGES_URL . '/tick-green.webp';
?>
<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<form id="arm_reset_data_form" method="POST" action="#" class="arm_admin_form" onsubmit="return false;"
			data-arm_rd_done_text="<?php esc_attr_e( 'Done', 'armember-membership' ); ?>"
			data-arm_rd_running_text="<?php esc_attr_e( 'Running...', 'armember-membership' ); ?>"
			data-arm_rd_waiting_text="<?php esc_attr_e( 'Waiting', 'armember-membership' ); ?>"
			data-arm_rd_failed_text="<?php esc_attr_e( 'Failed', 'armember-membership' ); ?>"
			data-arm_rd_error_text="<?php esc_attr_e( 'Something went wrong while resetting data. Please try again.', 'armember-membership' ); ?>"
			data-arm_rd_done_icon="<?php echo esc_url( $arm_rd_done_icon ); ?>">

			<div class="page_sub_title arm_margin_bottom_32"><?php esc_html_e( 'Reset Data', 'armember-membership' );
			$after_title_content = '';
			$after_title_content = apply_filters( 'arm_after_general_settings_title', $after_title_content ); //phpcs:ignore
			echo $after_title_content; //phpcs:ignore ?>
			</div>

			<div class="arm_setting_main_content arm_padding_0">
				<div class="arm_row_wrapper arm_row_wrapper_padding_before">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Choose Data to Reset', 'armember-membership' ); ?></div>
					</div>
				</div>
				<div class="arm_content_border"></div>

				<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block">

				<?php
				$arm_rd_row_index = 0;
				foreach ( $arm_rd_modules as $arm_rd_key => $arm_rd_module ) {
					$arm_rd_allowed = $arm_reset_data->arm_reset_data_can_use_module( $arm_rd_key );
					?>
					<?php if ( $arm_rd_row_index > 0 ) { ?>
						<div class="arm_content_border arm_margin_top_24"></div>
					<?php } ?>
					<div class="arm_row_wrapper <?php echo ( $arm_rd_row_index > 0 ) ? 'arm_margin_top_24' : ''; ?>">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php echo esc_html( $arm_rd_module['label'] ); ?></div>
							<div class="arm_global_setting_label">
								<?php echo esc_html( trim( $arm_rd_module['description'] . ' ' . implode( ' ', $arm_rd_module['notes'] ) ) ); ?>
								<?php if ( ! $arm_rd_allowed ) { ?>
									<br/><?php echo esc_html( $arm_reset_data->arm_reset_data_get_module_denied_message( $arm_rd_key ) ); ?>
								<?php } ?>
							</div>
							<?php if ( 'members' === $arm_rd_key ) { ?>
								<div class="arm_radio_btn_wrapper arm_margin_top_16" id="arm_rd_member_action_wrapper">
									<div class="arm_sub_plan_selection">
										<input type="radio" class="arm_iradio" id="arm_rd_member_action_keep" name="arm_rd_member_action" value="keep" checked="checked" /><label for="arm_rd_member_action_keep"><?php esc_html_e( 'Keep user accounts, remove only ARMember assigned plan', 'armember-membership' ); ?></label>
									</div>
									<div class="arm_sub_plan_selection">
										<input type="radio" class="arm_iradio" id="arm_rd_member_action_delete" name="arm_rd_member_action" value="delete" /><label for="arm_rd_member_action_delete"><?php esc_html_e( 'Delete WordPress user accounts', 'armember-membership' ); ?>
										<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e( 'Deletes only users registered through ARMember or with an ARMember plan, plan history or subscription. Administrators and other WordPress users (for example customers or editors) are not deleted. Their posts are assigned to you.', 'armember-membership' ); ?>"></i></label>
									</div>
								</div>
							<?php } ?>
						</div>
						<div class="right_content">
							<div class="armswitch arm_global_setting_switch arm_margin_0">
								<input type="checkbox" id="arm_rd_module_<?php echo esc_attr( $arm_rd_key ); ?>" value="<?php echo esc_attr( $arm_rd_key ); ?>" class="armswitch_input arm_rd_module_switch" name="arm_rd_modules[]" <?php checked( $arm_rd_allowed ); ?> <?php disabled( ! $arm_rd_allowed ); ?> />
								<label for="arm_rd_module_<?php echo esc_attr( $arm_rd_key ); ?>" class="armswitch_label"></label>
							</div>
						</div>
					</div>
					<?php
					$arm_rd_row_index++;
				}
				?>

				<div class="arm_content_border arm_margin_top_24"></div>
				<div class="arm_row_wrapper arm_margin_top_24">
					<div class="left_content">
						<div class="arm_form_header_label arm-setting-hadding-label"><?php esc_html_e( 'Reset Invoice ID and start from 1', 'armember-membership' ); ?></div>
						<div class="arm_global_setting_label"><?php esc_html_e( 'Only applies when transactions are removed.', 'armember-membership' ); ?></div>
					</div>
					<div class="right_content">
						<div class="armswitch arm_global_setting_switch arm_margin_0">
							<input type="checkbox" id="arm_rd_reset_invoice" value="1" class="armswitch_input" name="arm_rd_reset_invoice" checked="checked" />
							<label for="arm_rd_reset_invoice" class="armswitch_label"></label>
						</div>
					</div>
				</div>
				</div>
			</div>

			<div class="arm_submit_btn_container arm_apply_changes_btn_container">
				<button id="arm_reset_data_btn" class="arm_save_btn arm_min_width_120" type="button"><?php esc_html_e( 'Reset Selected Data', 'armember-membership' ); ?></button>
			</div>
			<?php $arm_rd_wpnonce = wp_create_nonce( 'arm_wp_nonce' ); ?>
			<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr( $arm_rd_wpnonce ); ?>"/>
			<input type="hidden" name="arm_rd_nonce" value="<?php echo esc_attr( wp_create_nonce( 'arm_reset_data' ) ); ?>"/>
		</form>
	</div>
</div>

<?php /* Confirm reset: summary of the selected data with counts. */ ?>
<div id="arm_reset_data_summary_popup" class="popup_wrapper arm_reset_data_summary_popup" style="width:820px;">
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_header_text"><?php esc_html_e( 'Confirm reset', 'armember-membership' ); ?><span class="popup_close_btn arm_popup_close_btn"></span></span>
		</div>
		<div class="popup_content_text">
			<div id="arm_rd_summary_loader" class="arm_margin_top_24 arm_margin_bottom_24"><?php echo $arm_common_lite->arm_loader_img_func(); //phpcs:ignore ?></div>
			<div id="arm_rd_summary_content" style="display:none;">
			<div class="arm_info_text"><?php esc_html_e( 'The following data will be permanently deleted.', 'armember-membership' ); ?></div>
			<div class="arm_content_border arm_margin_top_16"></div>
			<div id="arm_rd_summary_rows"></div>
			<div class="arm_current_plan_warning error arm_margin_top_35 arm_margin_left_0 arm_margin_right_0"><?php esc_html_e( 'This can\'t be undone. Take a database backup first. Recurring subscriptions are cancelled at the payment gateway; the gateway itself may still send its own email (for example PayPal). Data of inactive add-ons is not removed.', 'armember-membership' ); ?></div>
			</div>
		</div>
		<div class="armclear"></div>
		<div class="popup_footer arm_padding_top_0" id="arm_rd_summary_footer" style="display:none;">
			<div class="popup_content_btn_wrapper arm_margin_bottom_24">
				<button type="button" class="arm_submit_btn popup_ok_btn" id="arm_rd_confirm_btn"><?php esc_html_e( 'Reset Data', 'armember-membership' ); ?></button>
			</div>
			<div class="popup_content_btn_wrapper arm_margin_bottom_24">
				<button class="arm_cancel_btn popup_close_btn " type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>

<?php /* Progress: no close button while the reset runs. */ ?>
<div id="arm_reset_data_progress_popup" class="popup_wrapper arm_reset_data_progress_popup" style="width:640px;">
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_header_text"><?php esc_html_e( 'Resetting data...', 'armember-membership' ); ?></span>
		</div>
		<div class="popup_content_text">
			<div class="arm_info_text"><?php esc_html_e( 'Please keep this page open. Data is removed in small batches.', 'armember-membership' ); ?></div>
			<div class="arm_import_progressbar" id="arm_rd_progressbar">
				<div class="arm_import_progressbar_inner" id="arm_rd_progressbar_inner"></div>
			</div>
			<div class="armclear"></div>
			<div class="arm_info_text arm_text_align_right arm_margin_top_8" id="arm_rd_progress_percent">0%</div>
			<div id="arm_rd_progress_rows"></div>
		</div>
		<div class="armclear"></div>
		<div class="popup_footer" id="arm_rd_progress_footer" style="display:none;">
			<div class="popup_content_btn_wrapper">
				<button type="button" class="arm_submit_btn" id="arm_rd_retry_btn"><?php esc_html_e( 'Retry', 'armember-membership' ); ?></button>
			</div>
			<div class="popup_content_btn_wrapper">
				<button class="arm_cancel_btn" type="button" id="arm_rd_progress_close_btn"><?php esc_html_e( 'Close', 'armember-membership' ); ?></button>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>

<?php /* Completed. */ ?>
<div id="arm_reset_data_completed_popup" class="popup_wrapper arm_reset_data_completed_popup" style="width:640px;">
	<div class="popup_wrapper_inner">
		<div class="popup_header arm_text_align_center arm_padding_bottom_0">
			<div class=arm_rd_completed_icon>
				<img src="<?php echo esc_url( MEMBERSHIPLITE_IMAGES_URL . '/arm_rd_completed.svg' ); ?>" alt="" />
			</div>
			<span class="popup_header_text"><?php esc_html_e( 'Reset completed', 'armember-membership' ); ?></span>
		</div>
		<div class="popup_content_text">
			<div class="arm_info_text arm_text_align_center arm_padding_bottom_20"><?php esc_html_e( 'Your site is ready for production.', 'armember-membership' ); ?></div>
			<div id="arm_rd_completed_rows"></div>
		</div>
		<div class="armclear"></div>
		<div class="popup_footer">
			<div class="popup_content_btn_wrapper">
				<button class="arm_submit_btn popup_close_btn arm_margin_0" type="button"><?php esc_html_e( 'Close', 'armember-membership' ); ?></button>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
