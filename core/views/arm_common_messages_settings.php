<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
$common_messages         = $arm_global_settings->arm_get_all_common_message_settings();
$default_common_messages = $arm_global_settings->arm_default_common_messages();
$section_wise_common_messages = $arm_global_settings->get_section_wise_common_messages();
$common_messages_key_wise_notice = $arm_global_settings->get_common_messages_key_wise_notice();
$common_settings_section_titles = $arm_global_settings->get_common_settings_section_titles();
if ( ! empty( $common_messages ) ) {
	foreach ( $common_messages as $key => $value ) {
		if( !empty( $value ) )
		{
		    $value = esc_html( stripslashes( $value ) );
		}
		$common_messages[ $key ] = $value;
	}
}
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
	<div class="page_sub_title arm_margin_bottom_32"><?php esc_html_e( 'Common Messages', 'armember-membership' );
	$after_title_content = "";
	$after_title_content = apply_filters('arm_after_general_settings_title', $after_title_content); //phpcs:ignore
	echo $after_title_content; //phpcs:ignore
	?>
	</div>
		<form  method="post" action="#" id="arm_common_message_settings" class="arm_common_message_settings arm_admin_form">
			<?php 
				$section_counter = 1;
				if(!empty($section_wise_common_messages)){
					foreach($section_wise_common_messages as $section_title => $section_fields){ 
						
						if($section_counter>1){ ?>
							<div class="arm_margin_top_24"></div>
						<?php }
						
						$section_counter++;
						?>
						<div class="arm_setting_main_content arm_padding_0" id="changeCurrency">
						<div class="arm_row_wrapper arm_row_wrapper_padding_before ">
							<div class="left_content">
								<div class="arm_form_header_label arm-setting-hadding-label"><?php echo isset($common_settings_section_titles[$section_title])?esc_html($common_settings_section_titles[$section_title]):esc_html($section_title); ?></div>
							</div>
						</div>
						<div class="arm_content_border"></div>
						
						<div class="armclear"></div>	
						<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_display_block  arm_padding_top_24">	
						<div class="form-table arm_form_field_block arm_padding_0">
						<?php foreach($section_fields as $field_key => $field_title){ 
							if(is_array($field_title)){  ?>

								<label class="arm-form-table-label "><strong><?php echo esc_html($field_key); ?></strong></label>
									<div class="arm-form-table-content arm_margin_top_24"></div>
								<?php foreach($field_title as $f_key => $f_title){ ?>
									<div class="form-field arm_common_messages">
										<label class="arm-form-table-label">
											<label for="<?php echo esc_attr($f_key); ?>"><?php echo esc_html($f_title); ?></label>
										</label>
										<div class="arm-form-table-content arm_vertical_align_top arm_margin_top_12">
											<input type="text" name="arm_common_message_settings[<?php echo esc_attr($f_key); ?>]" id="<?php echo esc_attr($f_key); ?>" value="<?php echo ( ! empty( $common_messages[$f_key] ) ) ? esc_attr($common_messages[$f_key]) : (!empty($default_common_messages[$f_key])? esc_attr($default_common_messages[$f_key]) :""); ?>"/>
											<?php if(isset($common_messages_key_wise_notice[$f_key]) && !empty($common_messages_key_wise_notice[$f_key])){ ?>
												<div class="remained_login_attempts_notice arm_margin_top_10">
												<?php echo esc_html($common_messages_key_wise_notice[$f_key]); ?>
												</div>
											<?php } ?>
										</div>
									</div>
							<?php } }else{ ?>
							<div class="form-field arm_common_messages">
								
									<label  class="arm-form-table-label" for="<?php echo $field_key; ?>"><?php echo $field_title; //phpcs:ignore ?></label>
								<div class="arm-form-table-content arm_vertical_align_top arm_margin_top_12">
									<input type="text" class="arm_width_100_pct  arm_max_width_100_pct" name="arm_common_message_settings[<?php echo esc_attr($field_key); ?>]" id="<?php echo esc_attr($field_key); ?>" value="<?php echo ( ! empty( $common_messages[$field_key] ) ) ? esc_attr($common_messages[$field_key]) : (!empty($default_common_messages[$field_key])? esc_attr($default_common_messages[$field_key]) :""); ?>"/>
									<?php if(isset($common_messages_key_wise_notice[$field_key]) && !empty($common_messages_key_wise_notice[$field_key])){ ?><br>
										<span class="remained_login_attempts_notice arm_margin_top_12 arm_display_flex ">
										<?php echo esc_html($common_messages_key_wise_notice[$field_key]); ?>
										</span>
									<?php } ?>
								</div>
							</div>
						<?php } } ?>
					<?php if($section_title=="Payment Related Messages"){
						do_action( 'arm_payment_related_common_message', $common_messages );
					} ?>
						
						</div>
				</div>
						</div>
				<?php }
				} ?>
				
			<?php do_action( 'arm_after_common_messages_settings_html', $common_messages ); ?>
			<div class="arm_submit_btn_container arm_apply_changes_btn_container">
				<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore
				 ?>" class="arm_submit_btn_loader" id="arm_loader_img" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_common_message_settings_btn" type="submit" id="arm_common_message_settings_btn" name="arm_common_message_settings_btn"><?php esc_html_e( 'Apply Changes', 'armember-membership' ); ?></button>
			</div>
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</form>
		<div class="armclear"></div>
	</div>
</div>
