<?php
global $wpdb, $armPrimaryStatus, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_subscription_plans,$arm_member_forms;
$all_plans       = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_status, arm_subscription_plan_type' );
$dbProfileFields = $arm_member_forms->arm_get_db_form_fields();
?>
<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<?php
		// Handle Import/Export Process
		do_action( 'arm_handle_import_export', $_REQUEST ); //phpcs:ignore 
		?>
		<div class="arm_import_export_container">
			<div class="arm_import_export_left_box">
				<form method="post" action="#" id="arm_import_export" class="arm_admin_form" enctype="multipart/form-data">
					<div class="page_title"><?php esc_html_e( 'User Export', 'armember-membership' ); ?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><?php esc_html_e( 'Membership Plans', 'armember-membership' ); ?></th>
							<td class="arm-form-table-content">
								<select name="subscription_plan[]" id="subscription_plan_select" class="arm_chosen_selectbox" data-placeholder="<?php esc_attr_e( 'Select Plan(s)..', 'armember-membership' ); ?>" multiple >
									<?php
									if ( ! empty( $all_plans ) ) {
										foreach ( $all_plans as $plan ) {
											echo '<option value="' . intval($plan['arm_subscription_plan_id']) . '">' . stripslashes( $plan['arm_subscription_plan_name'] ) . '</option>'; //phpcs:ignore
										}
									}
									?>
								</select>
								<div class="armclear" style="max-height: 1px;"></div>
								<span class="arm_info_text">(<?php esc_html_e( 'Leave blank for all plans.', 'armember-membership' ); ?>)</span>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><?php esc_html_e( 'Member Status', 'armember-membership' ); ?></th>
							<td class="arm-form-table-content">
								<input type="hidden" id="arm_primary_status" name="primary_status" value="" />
								<dl class="arm_selectbox column_level_dd arm_width_300">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_primary_status">
											<li data-label="<?php esc_attr_e( 'All Status', 'armember-membership' ); ?>" data-value=""><?php esc_attr_e( 'All Status', 'armember-membership' ); ?></li>
											<?php
											if ( ! empty( $armPrimaryStatus ) ) {
												foreach ( $armPrimaryStatus as $key => $label ) {
													echo '<li data-label="' . esc_attr($label) . '" data-value="' . esc_attr($key) . '">' . esc_html($label) . '</li>';
												}
											}
											?>
										</ul>
									</dd>
								</dl>
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"><?php esc_html_e( 'Joining Date', 'armember-membership' ); ?></th>
							<td class="arm-form-table-content arm_import_export_date_fields">
								<input type="text" name="start_date" placeholder="<?php esc_attr_e( 'Start Date', 'armember-membership' ); ?>" class="arm_datepicker">
								<input type="text" name="end_date" placeholder="<?php esc_attr_e( 'End Date', 'armember-membership' ); ?>" class="arm_datepicker">
							</td>
						</tr>
						<tr class="form-field">
							<th class="arm-form-table-label"></th>
							<td class="arm-form-table-content arm_import_export_date_fields">
								<button id="arm_user_meta_to_export" class="armemailaddbtn arm_min_width_120" name="arm_action" value="select_meta" onClick="arm_open_user_meta_popup();" type="button" ><?php esc_html_e( 'Select Meta (Custom Fields)', 'armember-membership' ); ?></button>
								<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
								<input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpnonce);?>"/>
								<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>

							</td>
							<?php
							$defaultMetas = array();
							if ( ! empty( $dbProfileFields['default'] ) ) {
								foreach ( $dbProfileFields['default'] as $fieldMetaKey => $fieldOpt ) {
									if ( ! in_array( $fieldMetaKey, array( 'user_login', 'user_email' ) ) ) {
										continue;
									}
									array_push( $defaultMetas, $fieldMetaKey );
								}
							}
							$defaultMetas = implode( ',', $defaultMetas );
							?>
                            <input type="hidden" name="arm_user_metas_to_export" value="<?php echo esc_attr($defaultMetas); ?>" />
						</tr>
						<tr class="form-field">
							<th></th>
							<td class="arm-form-table-content">
								<button id="arm_user_export_btn_csv" class="armemailaddbtn arm_min_width_120" name="arm_action" value="user_export_csv" type="submit" ><?php esc_html_e( 'Export as csv', 'armember-membership' ); ?></button>
								<button id="arm_user_export_btn_xml" class="armemailaddbtn arm_min_width_120 arm_margin_0" name="arm_action" value="user_export_xml" type="submit" ><?php esc_html_e( 'Export as xml', 'armember-membership' ); ?></button>
								<span class="arm_info_text">(<?php esc_html_e( "User having role 'administrator' will not be exported.", 'armember-membership' ); ?>)
								</span>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="arm_import_export_right_box">
				<form method="post" action="#" id="arm_import_user_form"  class="arm_admin_form arm_import_user_form" enctype="multipart/form-data">
					<div class="page_title"><?php esc_html_e( 'User Import', 'armember-membership' ); ?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><?php esc_html_e( 'Upload File', 'armember-membership' ); ?></th>
							<td class="arm-form-table-content">
								<input type="file" name="import_user" id="arm_import_user" data-msg-required="<?php esc_attr_e( 'Please select a file.', 'armember-membership' ); ?>" class="armImportUpload" accept=".csv,.xml">
								<input class="arm_file_url" type="hidden" name="import_user" value="">
								<div class="arm_info_text"><?php esc_html_e( 'Only .csv and .xml files are allowed.', 'armember-membership' ); ?></div>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th class="arm-form-table-label">
								<label for="arm_plan_id"><?php esc_html_e( 'Assign Plan To User', 'armember-membership' ); ?></label>
							</th>
							<td class="arm-form-table-content">
								<input type="hidden" id="arm_plan_id" name="plan_id" value="" data-msg-required="<?php esc_attr_e( 'Please select at least one plan.', 'armember-membership' ); ?>" required/>
								<dl class="arm_selectbox column_level_dd arm_width_300">
									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
									<dd>
										<ul data-id="arm_plan_id">
											<li data-label="<?php esc_attr_e( 'Select Plan', 'armember-membership' ); ?>" data-value=""><?php esc_html_e( 'Select Plan', 'armember-membership' ); ?></li>
											<?php
											if ( ! empty( $all_plans ) ) {
												foreach ( $all_plans as $p ) {
													$p_id = $p['arm_subscription_plan_id'];
													if ( $p['arm_subscription_plan_status'] == '1' ) {
														?>
														<li data-label="<?php echo esc_attr( stripslashes( $p['arm_subscription_plan_name']) ); ?>" data-value="<?php echo esc_attr($p_id); ?>"><?php echo esc_html( stripslashes( $p['arm_subscription_plan_name'] ) ); //phpcs:ignore ?></li>
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
						<tr class="form-field">
							<th></th>
							<td class="arm-form-table-content">
								<input type="hidden" name="arm_user_metas_to_import" id="arm_user_metas_to_import" value="" />
								<button id="arm_user_import_btn" class="armemailaddbtn" name="arm_action" value="user_import" type="submit"><?php esc_html_e( 'Import', 'armember-membership' ); ?></button>&nbsp;<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" class="arm_loader_img_import_user arm_submit_btn_loader" style="display:none;" width="24" height="24" />
								<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
								<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
							</td>
						</tr>
						<tr class="form-field">
							<td colspan="2">
								<span class=""><?php esc_html_e( 'Please download sample csv', 'armember-membership' ); ?>&nbsp;<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=import_export&arm_action=download_sample&_wpnonce='.wp_create_nonce('arm_wp_nonce') ) ); ?>" class="arm_download_sample_csv_link" target="_blank"><?php esc_html_e( 'here', 'armember-membership' ); //phpcs:ignore ?></a>.
								</span>
								<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
								<input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpnonce);?>"/>
								<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
							</td>
						</tr>

					</table>
				</form>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="arm_divider"></div>
		<div class="arm_import_export_container">
			<div class="arm_import_export_left_box">
				<form method="post" action="#" id="arm_import_export" class="arm_admin_form" enctype="multipart/form-data">
					<div class="page_title"><?php esc_html_e( 'Export Settings', 'armember-membership' ); ?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<td colspan="2" class="arm_export_settings_container">
								<label>
									<input type="checkbox" name="global_options" value="1" class="arm_icheckbox"/>
									<span><?php esc_html_e( 'General Options', 'armember-membership' ); ?></span>
									<?php
									$gen_opt_note = esc_html__( 'All general options will be exported.', 'armember-membership' );
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($gen_opt_note); ?>"></i>
								</label>
								<div class="armclear"></div>
								<label>
									<input type="checkbox" name="block_options" value="1" class="arm_icheckbox"/>
									<span><?php esc_html_e( 'Security Options', 'armember-membership' ); ?></span>
									<?php
									$blk_opt_note = esc_html__( 'Export all security options.', 'armember-membership' );
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($blk_opt_note); ?>"></i>
								</label>
								
								<div class="armclear"></div>
								<label>
									<input type="checkbox" name="common_messages" value="1" class="arm_icheckbox"/>
									<span><?php esc_html_e( 'Common Messages', 'armember-membership' ); ?></span>
									<?php
									$com_msg_note = esc_html__( 'Export all common messages.', 'armember-membership' );
									?>
									<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($com_msg_note); ?>"></i>
								</label>
								<div class="armclear"></div>
							</td>
						</tr>
						<tr class="form-field">
							<td class="arm-form-table-content" colspan="2">
								<button id="arm_settings_export_btn" class="armemailaddbtn" name="arm_action" value="settings_export" type="submit"><?php esc_html_e( 'Export', 'armember-membership' ); ?></button>
								<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
								<input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpnonce);?>"/>
								<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="arm_import_export_right_box">
				<form method="post" action="#" id="arm_import_export" class="arm_admin_form" enctype="multipart/form-data">
					<div class="page_title"><?php esc_html_e( 'Import Settings', 'armember-membership' ); ?></div>
					<div class="armclear"></div>
					<table class="form-table">
						<tr class="form-field">
							<th class="arm-form-table-label"><?php esc_html_e( 'Import Settings', 'armember-membership' ); ?></th>
							<td class="arm-form-table-content">
								<textarea name="settings_import_text" id="settings_import_text" rows="8" cols="30" class="arm_min_width_100_pct" required></textarea>
							</td>
						</tr>
						<tr class="form-field">
							<th></th>
							<td class="arm-form-table-content">
								<button id="arm_settings_import_btn" class="armemailaddbtn" name="arm_action" value="settings_import" type="submit"><?php esc_html_e( 'Import', 'armember-membership' ); ?></button>
								<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
								<input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpnonce);?>"/>
								<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
	</div>
	<div class="arm_import_user_list_detail_container"></div>
</div>
<div class="arm_import_user_list_detail_popup popup_wrapper arm_import_user_list_detail_popup_wrapper">
	<form method="GET" id="arm_add_import_user_form" class="arm_admin_form" onsubmit="return arm_add_import_user_form_action();">
		<div class="popup_wrapper_inner" style="overflow: hidden;">
			<div class="popup_header">
				<span class="popup_close_btn arm_popup_close_btn arm_import_user_list_detail_close_btn"></span>
				<span class="add_rule_content"><?php esc_html_e( 'Import User Details', 'armember-membership' ); ?></span>
			</div>
			<div class="popup_content_text arm_import_user_list_detail_popup_text">
				<div class="arm_import_processing_loader">
					<div class="arm_import_processing_text"><?php esc_html_e( 'Processing', 'armember-membership' ); ?></div>
				</div>
			</div>
			<div class="popup_content_btn popup_footer">
				<div class="arm_user_import_password_section">
					<input type="radio" id="arm_user_password_fixed" name="arm_user_password_create" class="arm_form_field_settings_field_input arm_iradio"  value="set_fix" checked="checked"  onchange="arm_set_user_password('set_fix')" ><label for="arm_user_password_fixed" class="arm_user_import_type"><?php esc_html_e( 'Set fix password', 'armember-membership' ); ?></label>

					<input type="radio" id="arm_user_password_dynamically" name="arm_user_password_create" class="arm_form_field_settings_field_input arm_iradio"  value="create_dynamic"  onchange="arm_set_user_password('create_dynamic')"><label for="arm_user_password_dynamically"  class="arm_user_import_type"><?php esc_html_e( 'Generate dynamically', 'armember-membership' ); ?></label>
				
					<div class="arm_import_user_send_mail_wrapper" id="arm_user_password_fixed_div" style="display:block;">
						 <input type="text" id="arm_import_user_fix_password" name="arm_import_user_fix_password" class="arm_fixed_password"/>
					</div>
				
					<div class="arm_import_user_send_mail_wrapper" id="arm_user_password_dynamically_div">
						<input type="checkbox" checked="checked" id="arm_send_mail_to_imported_users" class="arm_send_mail_to_imported_users chkstanard"/><label for="arm_send_mail_to_imported_users"><?php esc_html_e( 'Send Reset Password Link by email.', 'armember-membership' ); ?></label>
					</div>
					
					<input type="radio" id="arm_user_password_from_csv" name="arm_user_password_create" class="arm_form_field_settings_field_input arm_iradio"  value="from_csv"  onchange="arm_set_user_password('from_csv')" style="display: none;" ><label for="arm_user_password_from_csv" class="arm_user_import_type arm_user_password_from_csv_label" style="display:none;"><?php esc_html_e( 'Import Password from csv / xml', 'armember-membership' ); ?></label>
					<div class="arm_import_user_send_mail_wrapper" id="arm_user_password_from_csv_div">
						<input type="radio" class="arm_form_field_settings_field_input arm_iradio" checked="checked" name="arm_password_type" id="arm_is_hashed_password" value="hashed"/><label for="arm_is_hashed_password"><?php esc_html_e( 'Your password is hashed.', 'armember-membership' ); ?></label>
						
						 <input type="radio" class="arm_form_field_settings_field_input arm_iradio" name="arm_password_type" id="arm_is_plain_password" value="plain"/><label for="arm_is_plain_password"><?php esc_html_e( 'Your password is plain text.', 'armember-membership' ); ?></label>
					</div>
				 </div>
						<div class="armclear"></div>
				<div class="arm_import_progressbar">
					<div class="arm_import_progressbar_inner"></div>
				</div>
				<div class="popup_content_btn_wrapper">
					<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;" width="20" height="20"/>
					<button class="arm_cancel_btn arm_import_user_list_detail_previous_btn" type="button"><?php esc_html_e( 'Previous', 'armember-membership' ); ?></button>
					<button class="arm_submit_btn arm_add_import_user_submit_btn" type="submit"><?php esc_html_e( 'Add', 'armember-membership' ); ?></button>
					<button class="arm_cancel_btn arm_import_user_list_detail_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpnonce);?>"/>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
			</div>
			<div class="armclear"></div>
		</div>
	</form>
</div>
<script type="text/javascript">
	__PROCESSING = '<?php esc_html__( 'Processing', 'armember-membership' ); ?>';
	arm_import_export_continue_flag = 0;
</script>

<div id='arm_select_user_meta_for_export' class="popup_wrapper">
	<form method="post" action="#" id="arm_select_user_meta_for_export_form" class="arm_admin_form">
		<table  cellspacing="0">
			<tr>
				<td class="arm_select_user_meta_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e( 'Select User Meta Fields', 'armember-membership' ); ?></td>
				<td class="popup_content_text arm_select_user_meta_wrapper">
					<?php

					if ( ! empty( $dbProfileFields['default'] ) ) {

						foreach ( $dbProfileFields['default'] as $fieldMetaKey => $fieldOpt ) {
							if ( empty( $fieldMetaKey ) || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
								continue;
							}
							$checkedDefault = " checked='checked' disabled='disabled' ";
							if ( ! in_array( $fieldMetaKey, array( 'user_login', 'user_email' ) ) ) {
								$checkedDefault = '';
							}
							?>
							<label class = "account_detail_radio arm_account_detail_options">
								<input type = "checkbox" value = "<?php echo esc_attr($fieldMetaKey); ?>" class = "arm_icheckbox arm_account_detail_fields" name = "export_user_meta[<?php echo esc_attr($fieldMetaKey); ?>]" id = "arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>" <?php echo $checkedDefault; //phpcs:ignore ?> />
								<label for="arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>"><?php echo esc_attr($fieldOpt['label']); ?></label>
								<div class="arm_list_sortable_icon"></div>
							</label>
							<?php
						}
					}


					if ( ! empty( $dbProfileFields['other'] ) ) {

						foreach ( $dbProfileFields['other'] as $fieldMetaKey => $fieldOpt ) {
							if ( empty( $fieldMetaKey ) || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
								continue;
							}

							?>
							<label class = "account_detail_radio arm_account_detail_options">
								<input type = "checkbox" value = "<?php echo esc_attr($fieldMetaKey); ?>" class = "arm_icheckbox arm_account_detail_fields" name = "export_user_meta[<?php echo esc_attr($fieldMetaKey); ?>]" id = "arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>"/>
								<label for="arm_profile_field_input_<?php echo esc_attr($fieldMetaKey); ?>"><?php echo esc_html($fieldOpt['label']); ?></label>
								<div class="arm_list_sortable_icon"></div>
							</label>
							<?php
						}
					}
					?>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_clear_field" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
						   <button class="arm_save_btn arm_user_meta_to_export" id="arm_select_metas_to_export" type="button" data-type="add"><?php esc_html_e( 'Ok', 'armember-membership' ); ?></button>
						<button class="arm_cancel_btn arm_select_user_meta_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>

<div id='arm_select_user_meta_for_import' class="popup_wrapper">
	<form method="post" action="#" id="arm_select_user_meta_for_import_form" class="arm_admin_form">
		<table  cellspacing="0">
			<tr>
				<td class="arm_select_user_meta_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e( 'Select User Meta Fields', 'armember-membership' ); ?></td>
				<td class="popup_content_text popup_header">
					<span class="arm_info_text arm_margin_0" >
						<?php esc_html_e( ' Note that if you will select new meta then new meta will be set as', 'armember-membership' ); ?>
						<strong> <?php esc_html_e( 'Preset Fields', 'armember-membership' ); ?> </strong>
						<?php esc_html_e( 'and the field type will be', 'armember-membership' ); ?>   
						<strong> <?php esc_html_e( 'Textbox.', 'armember-membership' ); ?> </strong>
						<br/>
					</span>
				</td>
				<td class="popup_content_text arm_select_user_meta_wrapper" id="arm_select_user_meta_wrapper"> </td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_loader_img_clear_field" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;display: none;" width="20" height="20" />
						<button class="arm_save_btn arm_user_meta_to_import_next" id="arm_user_meta_to_import_next" type="button" data-type="add"><?php esc_html_e( 'Next', 'armember-membership' ); ?></button>
						<button class="arm_cancel_btn arm_select_user_meta_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
						
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>
