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
		<form  method="post" action="#" id="arm_common_message_settings" class="arm_common_message_settings arm_admin_form">
			<?php 
				$section_counter = 1;
				if(!empty($section_wise_common_messages)){
					foreach($section_wise_common_messages as $section_title => $section_fields){ 
						
						if($section_counter>1){ ?>
							<div class="arm_solid_divider"></div>
						<?php }
						
						$section_counter++;
						?>

						<div class="page_sub_title"><?php echo isset($common_settings_section_titles[$section_title])?esc_html($common_settings_section_titles[$section_title]):esc_html($section_title); ?></div>
						<div class="armclear"></div>		
						<table class="form-table">
						<?php foreach($section_fields as $field_key => $field_title){ 
							if(is_array($field_title)){  ?>
								<tr>
									<th class="arm-form-table-label"><strong><?php echo esc_html($field_key); ?></strong></th>
									<td class="arm-form-table-content"></td>
								</tr>
								<?php foreach($field_title as $f_key => $f_title){ ?>
									<tr class="form-field">
										<th class="arm-form-table-label">
											<label for="<?php echo esc_attr($f_key); ?>"><?php echo esc_html($f_title); ?></label>
										</th>
										<td class="arm-form-table-content arm_vertical_align_top" >
											<input type="text" name="arm_common_message_settings[<?php echo esc_attr($f_key); ?>]" id="<?php echo esc_attr($f_key); ?>" value="<?php echo ( ! empty( $common_messages[$f_key] ) ) ? esc_attr($common_messages[$f_key]) : (!empty($default_common_messages[$f_key])? esc_attr($default_common_messages[$f_key]) :""); ?>"/>
											<?php if(isset($common_messages_key_wise_notice[$f_key]) && !empty($common_messages_key_wise_notice[$f_key])){ ?>
												<br>
												<span class="remained_login_attempts_notice">
												<?php echo esc_html($common_messages_key_wise_notice[$f_key]); ?>
												</span>
											<?php } ?>
										</td>
									</tr>
							<?php } }else{ ?>
							<tr class="form-field">
								<th class="arm-form-table-label">
									<label for="<?php echo $field_key; ?>"><?php echo $field_title; //phpcs:ignore ?></label>
								</th>
								<td class="arm-form-table-content arm_vertical_align_top" >
									<input type="text" name="arm_common_message_settings[<?php echo esc_attr($field_key); ?>]" id="<?php echo esc_attr($field_key); ?>" value="<?php echo ( ! empty( $common_messages[$field_key] ) ) ? esc_attr($common_messages[$field_key]) : (!empty($default_common_messages[$field_key])? esc_attr($default_common_messages[$field_key]) :""); ?>"/>
									<?php if(isset($common_messages_key_wise_notice[$field_key]) && !empty($common_messages_key_wise_notice[$field_key])){ ?>
										<br>
										<span class="remained_login_attempts_notice">
										<?php echo esc_html($common_messages_key_wise_notice[$field_key]); ?>
										</span>
									<?php } ?>
								</td>
							</tr>
						<?php } } ?>
					<?php if($section_title=="Payment Related Messages"){
						do_action( 'arm_payment_related_common_message', $common_messages );
					} ?>
						</table>
				<?php }
				} ?>
			<?php do_action( 'arm_after_common_messages_settings_html', $common_messages ); ?>
			<div class="arm_submit_btn_container">
				<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore
				 ?>" class="arm_submit_btn_loader" id="arm_loader_img" style="display:none;" width="24" height="24" />&nbsp;<button class="arm_save_btn arm_common_message_settings_btn" type="submit" id="arm_common_message_settings_btn" name="arm_common_message_settings_btn"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
			</div>
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</form>
		<div class="armclear"></div>
	</div>
</div>
