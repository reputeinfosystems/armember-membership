<?php
	global $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
	$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
	$arm_common_date_format = 'm/d/Y';
	$arm_default_date = date_i18n($arm_common_date_format);
?>

<div class="arm_global_settings_main_wrapper">
	<div class="page_sub_content">
		<div class="arm_debug_container">
			<form id="arm_debug_form" method="POST" action="#" id="" enctype="multipart/form-data" class="arm_admin_form">
				<div class="page_sub_title"><?php esc_html_e('Payment Gateway Debug Log Settings', 'armember-membership'); ?></div>
				<div class="armclear"></div>
				<table class="form-table">
					<?php
						foreach($payment_gateways as $payment_gateway_key => $payment_gateway_val)
						{
							$arm_gateway_name = $payment_gateway_val['gateway_name'];
							$arm_debug_logs = (!empty($payment_gateway_val['payment_debug_logs']) && $payment_gateway_val['payment_debug_logs'] == '1') ? 'checked="checked"' : '';
					?>
							<tr class="form-field">
								<th class="arm-form-table-label"><?php echo esc_attr($arm_gateway_name); ?></th>
								<td class="arm-form-table-content">
									<div class="armswitch arm_payment_setting_switch">
										<input type="checkbox" id="arm_<?php echo strtolower( esc_attr($arm_gateway_name) );//phpcs:ignore?>_debug_log" <?php echo esc_attr($arm_debug_logs);?> value="1" class="armswitch_input arm_debug_mode_switch" name="payment_gateway_settings[<?php echo strtolower( esc_attr($payment_gateway_key) ); //phpcs:ignore?>][debug_log]" data-switch_key="<?php echo strtolower( esc_attr($payment_gateway_key) ); //phpcs:ignore?>"/>
										<label for="arm_<?php echo strtolower( esc_attr($arm_gateway_name) ); //phpcs:ignore?>_debug_log" class="armswitch_label"></label>
									</div>
									<?php 
										if(!empty($arm_debug_logs)){
									?>
											<div class="arm_debug_switch_<?php echo esc_attr($payment_gateway_key); ?>  arm_debug_log_action_container" >
									<?php
										} else {
									?>
											<div class="arm_debug_switch_<?php echo esc_attr($payment_gateway_key); ?> arm_debug_log_action_container" style="display: none;">
									<?php
										}
									?>
										<a href="javascript:void(0)" onclick="arm_view_payment_debug_logs('<?php echo esc_attr($payment_gateway_key); ?>', '<?php echo esc_attr($arm_gateway_name); ?>')"><?php esc_html_e('View Log', 'armember-membership'); ?></a>
										<a href="javascript:void(0)" class="arm_margin_left_10" onclick="arm_download_payment_debug_logs('<?php echo esc_attr($payment_gateway_key); ?>')">
											<?php esc_html_e('Download Log', 'armember-membership'); ?>
										</a>
										<div class='arm_confirm_box arm_download_confirm_box' id='arm_download_confirm_box_<?php echo esc_attr($payment_gateway_key); ?>'>
											<div class='arm_confirm_box_body'>
												<div class='arm_confirm_box_arrow'></div>
												<div class='arm_confirm_box_text'>
													<div class="arm_download_duration_selection">
														<label class="arm_select_duration_label"><?php esc_html_e('Select log duration to download', 'armember-membership'); ?></label>
														<input type="hidden" id="arm_download_duration" name="action1" value="7" />
														<dl class="arm_selectbox column_level_dd arm_width_280">
															<dt>
																<span><?php esc_html_e('Last 1 Week','armember-membership');?></span>
																<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
															</dt>
															<dd>
																<ul data-id="arm_download_duration">
																	<li data-label="<?php esc_attr_e('Last 1 Day', 'armember-membership');?>" data-value="1"><?php esc_html_e('Last 1 Day', 'armember-membership');?></li>

																	<li data-label="<?php esc_attr_e('Last 3 Days', 'armember-membership');?>" data-value="3"><?php esc_html_e('Last 3 Days', 'armember-membership');?></li>

																	<li data-label="<?php esc_attr_e('Last 1 Week','armember-membership');?>" data-value="7"><?php esc_html_e('Last 1 Week','armember-membership');?></li>

																	<li data-label="<?php esc_attr_e('Last 2 Weeks', 'armember-membership');?>" data-value="15"><?php esc_html_e('Last 2 Weeks', 'armember-membership');?></li>

																	<li data-label="<?php esc_attr_e('Last Month', 'armember-membership');?>" data-value="30"><?php esc_html_e('Last Month', 'armember-membership');?></li>

																	<li data-label="<?php esc_attr_e('All', 'armember-membership');?>" data-value="all"><?php esc_html_e('All', 'armember-membership');?></li>

																	<li data-label="<?php esc_attr_e('Custom', 'armember-membership');?>" data-value="custom"><?php esc_html_e('Custom', 'armember-membership');?></li>
																</ul>
															</dd>
														</dl>
													</div>
													<form id="arm_download_custom_duration_<?php echo esc_attr($payment_gateway_key); ?>_form">
														<div class="arm_download_custom_duration_div">
											                <div class="arm_datatable_filter_item arm_margin_left_0" >
											                    <input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('Start Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
											                </div>
											                <div class="arm_datatable_filter_item">
											                    <input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('End Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
											                </div>
										            	</div>
										            </form>
													<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_debug_log_btn' data-selected_key='<?php echo esc_attr($payment_gateway_key); ?>'><?php esc_html_e('Download', 'armember-membership'); ?></button>
												</div>
											</div>
										</div>
										<a href="javascript:void(0)" class="arm_clear_debug_log arm_margin_left_10" onclick="arm_clear_payment_debug_logs('<?php echo esc_attr($payment_gateway_key); ?>')" ><?php esc_html_e('Clear Log', 'armember-membership'); ?></a>
										<?php
											$arm_debug_clear_log = $arm_global_settings->arm_get_confirm_box($payment_gateway_key, esc_html__("Are you sure you want to clear debug logs?", 'armember-membership'), 'arm_clear_debug_log');
											echo $arm_debug_clear_log; //phpcs:ignore
										?>
									</div>
								</td>
							</tr>
					<?php
						}

						$arm_add_new_debug_log_gateway = "";
						$arm_add_new_debug_log_gateway = apply_filters('arm_add_payment_debug_log_field', $arm_add_new_debug_log_gateway, $payment_gateways);
						echo $arm_add_new_debug_log_gateway; //phpcs:ignore
					?>
				</table>
				<?php 
					if($ARMemberLite->is_arm_pro_active){
						$arm_add_optins_debug_log_gateway = "";
						$arm_add_optins_debug_log_gateway = apply_filters('arm_load_debug_settings_section','opt-ins');
						echo $arm_add_optins_debug_log_gateway; //phpcs:ignore
					}
				?>
				<?php 
					if($ARMemberLite->is_arm_pro_active){
						$arm_get_integrations = apply_filters('arm_add_integration_debug_log_section',array());
						
						if (!empty($arm_get_integrations)) {
							?>
							<div class="page_sub_title"><?php esc_html_e('Integration Debug Log Settings', 'armember-membership'); ?></div>
							<div class="armclear"></div>
							<table class="form-table">
								<?php
								$integration_get_settings_unser = get_option('arm_integration_settings', array());
								$integration_get_settings = !empty($integration_get_settings_unser) ? maybe_unserialize($integration_get_settings_unser) : array();
								foreach($arm_get_integrations as $integration_key => $arm_integration_name)
								{
									$arm_integration_debug_logs = (!empty($integration_get_settings[$integration_key]['debug_logs']) && $integration_get_settings[$integration_key]['debug_logs'] == '1') ? 'checked="checked"' : '';
								?>
									<tr class="form-field">
										<th class="arm-form-table-label"><?php echo esc_html($arm_integration_name); ?></th>
										<td class="arm-form-table-content">
											<div class="armswitch arm_payment_setting_switch">
												<input type="checkbox" id="arm_<?php echo strtolower( esc_attr($arm_integration_name) );//phpcs:ignore?>_debug_log" <?php echo esc_attr($arm_integration_debug_logs);?> value="1" class="armswitch_input arm_debug_mode_switch" name="arm_integration_settings[<?php echo strtolower( esc_attr($integration_key) ); //phpcs:ignore?>][debug_logs]" data-switch_key="<?php echo strtolower( esc_attr($integration_key) ); //phpcs:ignore?>"/>
												<label for="arm_<?php echo strtolower( esc_attr($arm_integration_name) ); //phpcs:ignore?>_debug_log" class="armswitch_label"></label>
											</div>
											<?php 
												if(!empty($arm_integration_debug_logs)){
											?>
												<div class="arm_debug_switch_<?php echo esc_attr($integration_key); ?>  arm_debug_log_action_container" >
											<?php
												} else {
											?>
												<div class="arm_debug_switch_<?php echo esc_attr($integration_key); ?> arm_debug_log_action_container" style="display: none;">
											<?php
												}
											?>
												<a href="javascript:void(0)" onclick="arm_view_general_debug_logs('<?php echo esc_attr($integration_key); ?>', '<?php echo esc_attr($arm_integration_name); ?>')"><?php esc_html_e('View Log', 'armember-membership'); ?></a>
												<a href="javascript:void(0)" class="arm_margin_left_10" onclick="arm_download_general_debug_logs('<?php echo esc_attr($integration_key); ?>')">
													<?php esc_html_e('Download Log', 'armember-membership'); ?>
												</a>
												<div class='arm_confirm_box arm_general_debug_download_confirm_box' id='arm_general_debug_download_confirm_box_<?php echo esc_attr($integration_key); ?>'>
													<div class='arm_confirm_box_body'>
														<div class='arm_confirm_box_arrow'></div>
														<div class='arm_confirm_box_text'>
															<div class="arm_download_duration_selection">
																<label class="arm_select_duration_label"><?php esc_html_e('Select log duration to download', 'armember-membership'); ?></label>
																<input type="hidden" id="arm_general_download_duration" name="action1" value="7" />
																<dl class="arm_selectbox column_level_dd arm_width_280">
																	<dt>
																		<span><?php esc_html_e('Last 1 Week','armember-membership');?></span>
																		<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
																	</dt>
																	<dd>
																		<ul data-id="arm_general_download_duration">
																			<li data-label="<?php esc_attr_e('Last 1 Day', 'armember-membership');?>" data-value="1"><?php esc_html_e('Last 1 Day', 'armember-membership');?></li>

																			<li data-label="<?php esc_attr_e('Last 3 Days', 'armember-membership');?>" data-value="3"><?php esc_html_e('Last 3 Days', 'armember-membership');?></li>

																			<li data-label="<?php esc_attr_e('Last 1 Week','armember-membership');?>" data-value="7"><?php esc_html_e('Last 1 Week','armember-membership');?></li>

																			<li data-label="<?php esc_attr_e('Last 2 Weeks', 'armember-membership');?>" data-value="15"><?php esc_html_e('Last 2 Weeks', 'armember-membership');?></li>

																			<li data-label="<?php esc_attr_e('Last Month', 'armember-membership');?>" data-value="30"><?php esc_html_e('Last Month', 'armember-membership');?></li>

																			<li data-label="<?php esc_attr_e('All', 'armember-membership');?>" data-value="all"><?php esc_html_e('All', 'armember-membership');?></li>

																			<li data-label="<?php esc_attr_e('Custom', 'armember-membership');?>" data-value="custom"><?php esc_html_e('Custom', 'armember-membership');?></li>
																		</ul>
																	</dd>
																</dl>
															</div>
															<form id="arm_general_debug_download_custom_duration_<?php echo esc_attr($integration_key); ?>_form">
																<div class="arm_download_custom_duration_div">
																	<div class="arm_datatable_filter_item arm_margin_left_0">
																		<input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('Start Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
																	</div>
																	<div class="arm_datatable_filter_item">
																		<input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('End Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
																	</div>
																</div>
															</form>
															<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_general_debug_log_btn' data-selected_key='<?php echo esc_attr($integration_key); ?>'><?php esc_html_e('Download', 'armember-membership'); ?></button>
														</div>
													</div>
												</div>
												<a href="javascript:void(0)" class="arm_clear_debug_log arm_margin_left_10" onclick="arm_clear_general_debug_logs('<?php echo esc_attr($integration_key); ?>')" ><?php esc_html_e('Clear Log', 'armember-membership'); ?></a>
												<?php
													$arm_debug_clear_log = $arm_global_settings->arm_get_confirm_box($integration_key, esc_html__("Are you sure you want to clear debug logs?", 'armember-membership'), 'arm_clear_debug_log');
													echo $arm_debug_clear_log; //phpcs:ignore
												?>
											</div>
										</td>
									</tr>
								<?php	
								}
								?>
								
							</table>
							<?php
						}
					}
				?>
				<?php $nonce=wp_create_nonce('arm_wp_nonce');?>
				<input type="hidden" name='arm_wp_nonce' value="<?php echo esc_attr($nonce)?>"/>
				<br>
				<div class="page_sub_title"><?php esc_html_e('Cron Debug Log Settings', 'armember-membership'); ?></div>
				<div class="armclear"></div>
				<table class="form-table">
					<?php
						$arm_is_cron_log_enabled = get_option('arm_cron_debug_log');
						$arm_cron_debug_log = ($arm_is_cron_log_enabled) ? 'checked=checked' : '';
					?>
					<tr class="form-field">
						<th class="arm-form-table-label"><?php esc_html_e('Enable Cron Debug Logs', 'armember-membership'); ?></th>
						<td class="arm-form-table-content">
							<div class="armswitch arm_payment_setting_switch">
								<input type="checkbox" id="arm_cron_debug_log" <?php echo esc_attr($arm_cron_debug_log); ?> value="1" class="armswitch_input arm_debug_mode_switch" name="arm_cron_debug_log" data-switch_key="cron"/>
								<label for="arm_cron_debug_log" class="armswitch_label"></label>
							</div>
							<?php
								if(!empty($arm_cron_debug_log)){
							?>
									<div class="arm_debug_switch_cron arm_debug_log_action_container">
							<?php
								} else {
							?>
									<div class="arm_debug_switch_cron arm_debug_log_action_container" style="display: none;">
							<?php
								}
							?>
								<a href="javascript:void(0)" onclick="arm_view_general_debug_logs('cron', 'Cron')"><?php esc_html_e('View Log', 'armember-membership'); ?></a>
								<a href="javascript:void(0)" onclick="arm_download_general_debug_logs('cron')" class="arm_margin_left_10"><?php esc_html_e('Download Log', 'armember-membership'); ?></a>
								<div class='arm_confirm_box arm_general_debug_download_confirm_box' id='arm_general_debug_download_confirm_box_cron'>
									<div class='arm_confirm_box_body'>
										<div class='arm_confirm_box_arrow'></div>
										<div class='arm_confirm_box_text'>
											<div class="arm_download_duration_selection">
												<label class="arm_select_duration_label"><?php esc_html_e('Select log duration to download', 'armember-membership'); ?></label>
												<input type="hidden" id="arm_general_download_duration" name="action1" value="7" />
												<dl class="arm_selectbox column_level_dd arm_width_280">
													<dt>
														<span><?php esc_html_e('Last 1 Week','armember-membership');?></span>
														<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
													</dt>
													<dd>
														<ul data-id="arm_general_download_duration">
															<li data-label="<?php esc_attr_e('Last 1 Day', 'armember-membership');?>" data-value="1"><?php esc_html_e('Last 1 Day', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last 3 Days', 'armember-membership');?>" data-value="3"><?php esc_html_e('Last 3 Days', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last 1 Week','armember-membership');?>" data-value="7"><?php esc_html_e('Last 1 Week','armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last 2 Weeks', 'armember-membership');?>" data-value="15"><?php esc_html_e('Last 2 Weeks', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last Month', 'armember-membership');?>" data-value="30"><?php esc_html_e('Last Month', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('All', 'armember-membership');?>" data-value="all"><?php esc_html_e('All', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Custom', 'armember-membership');?>" data-value="custom"><?php esc_html_e('Custom', 'armember-membership');?></li>
														</ul>
													</dd>
												</dl>
											</div>
											<form id="arm_general_debug_download_custom_duration_cron_form">
												<div class="arm_download_custom_duration_div">
									                <div class="arm_datatable_filter_item arm_margin_left_0">
									                    <input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('Start Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
									                </div>
									                <div class="arm_datatable_filter_item">
									                    <input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('End Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
									                </div>
								            	</div>
								            </form>
											<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_general_debug_log_btn' data-selected_key='cron'><?php esc_html_e('Download', 'armember-membership'); ?></button>
										</div>
									</div>
								</div>
								<a href="javascript:void(0)" class="arm_clear_debug_log arm_margin_left_10" onclick="arm_clear_general_debug_logs('cron')" ><?php esc_html_e('Clear Log', 'armember-membership'); ?></a>
								<?php
									echo $arm_global_settings->arm_get_confirm_box("cron", esc_html__("Are you sure you want to clear debug logs?", 'armember-membership'), 'arm_clear_debug_log');//phpcs:ignore
									// echo $arm_debug_clear_log; //phpcs:ignore
								?>
							</div>
						</td>
					</tr>		
				</table>
				<br>
				<div class="page_sub_title"><?php esc_html_e('Email Debug Log Settings', 'armember-membership'); ?></div>
				<div class="armclear"></div>
				<table class="form-table">
					<?php
						$arm_is_email_log_enabled = get_option('arm_email_debug_log');
						$arm_email_debug_log = ($arm_is_email_log_enabled) ? 'checked=checked' : '';
					?>
					<tr class="form-field">
						<th class="arm-form-table-label"><?php esc_html_e('Enable Email Debug Logs', 'armember-membership'); ?></th>
						<td class="arm-form-table-content">
							<div class="armswitch arm_payment_setting_switch">
								<input type="checkbox" id="arm_email_debug_log" <?php echo esc_attr($arm_email_debug_log); ?> value="1" class="armswitch_input arm_debug_mode_switch" name="arm_email_debug_log" data-switch_key="email"/>
								<label for="arm_email_debug_log" class="armswitch_label"></label>
							</div>
							<?php
								if(!empty($arm_email_debug_log)){
							?>
									<div class="arm_debug_switch_email arm_debug_log_action_container">
							<?php
								} else {
							?>
									<div class="arm_debug_switch_email arm_debug_log_action_container" style="display: none;">
							<?php
								}
							?>
								<a href="javascript:void(0)" onclick="arm_view_general_debug_logs('email', 'Email')"><?php esc_html_e('View Log', 'armember-membership'); ?></a>
								<a href="javascript:void(0)" onclick="arm_download_general_debug_logs('email')" class="arm_margin_left_10"><?php esc_html_e('Download Log', 'armember-membership'); ?></a>
								<div class='arm_confirm_box arm_general_debug_download_confirm_box' id='arm_general_debug_download_confirm_box_email'>
									<div class='arm_confirm_box_body'>
										<div class='arm_confirm_box_arrow'></div>
										<div class='arm_confirm_box_text'>
											<div class="arm_download_duration_selection">
												<label class="arm_select_duration_label"><?php esc_html_e('Select log duration to download', 'armember-membership'); ?></label>
												<input type="hidden" id="arm_general_download_duration" name="action1" value="7" />
												<dl class="arm_selectbox column_level_dd arm_width_280">
													<dt>
														<span><?php esc_html_e('Last 1 Week','armember-membership');?></span>
														<input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i>
													</dt>
													<dd>
														<ul data-id="arm_general_download_duration">
															<li data-label="<?php esc_attr_e('Last 1 Day', 'armember-membership');?>" data-value="1"><?php esc_html_e('Last 1 Day', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last 3 Days', 'armember-membership');?>" data-value="3"><?php esc_html_e('Last 3 Days', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last 1 Week','armember-membership');?>" data-value="7"><?php esc_html_e('Last 1 Week','armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last 2 Weeks', 'armember-membership');?>" data-value="15"><?php esc_html_e('Last 2 Weeks', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Last Month', 'armember-membership');?>" data-value="30"><?php esc_html_e('Last Month', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('All', 'armember-membership');?>" data-value="all"><?php esc_html_e('All', 'armember-membership');?></li>

															<li data-label="<?php esc_attr_e('Custom', 'armember-membership');?>" data-value="custom"><?php esc_html_e('Custom', 'armember-membership');?></li>
														</ul>
													</dd>
												</dl>
											</div>
											<form id="arm_general_debug_download_custom_duration_email_form">
												<div class="arm_download_custom_duration_div">
									                <div class="arm_datatable_filter_item arm_margin_left_0">
									                    <input type="text" name="arm_filter_pstart_date" id="arm_filter_pstart_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('Start Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
									                </div>
									                <div class="arm_datatable_filter_item">
									                    <input type="text" name="arm_filter_pend_date" id="arm_filter_pend_date" class="arm_download_custom_duration_date" placeholder="<?php esc_attr_e('End Date', 'armember-membership'); ?>" data-date_format="<?php echo esc_attr($arm_common_date_format); ?>" value="<?php echo esc_attr($arm_default_date); ?>" />
									                </div>
								            	</div>
								            </form>
											<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_download_general_debug_log_btn' data-selected_key='email'><?php esc_html_e('Download', 'armember-membership'); ?></button>
										</div>
									</div>
								</div>
								<a href="javascript:void(0)" class="arm_clear_debug_log arm_margin_left_10" onclick="arm_clear_general_debug_logs('email')" ><?php esc_html_e('Clear Log', 'armember-membership'); ?></a>
								<?php
									$arm_debug_clear_log = $arm_global_settings->arm_get_confirm_box('email', esc_html__("Are you sure you want to clear debug logs?", 'armember-membership'), 'arm_clear_debug_log');
									echo $arm_debug_clear_log; //phpcs:ignore
								?>
							</div>
						</td>
					</tr>		
				</table>
				<table class="form-table">
					<tr class="form-field">
						<th class="arm-form-table-label"></th>
						<td class="arm-form-table-content"></td>
					</tr>
				</table>
				<div class="arm_submit_btn_container">
					<button id="arm_save_debug_logs_btn" class="arm_save_btn arm_min_width_120" name="arm_save_debug_logs" value="arm_save_debug_logs" type="submit" ><?php esc_html_e('Save', 'armember-membership');?></button>
				</div>
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</form>
		</div>
	</div>
</div>


<div class="arm_view_debug_payment_logs popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>">
	<div>
		<div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_view_debug_payment_logs_close_btn"></span>
            <span class="add_rule_content"><?php esc_html_e('Debug Log Settings', 'armember-membership'); ?> (<span class="view_payment_log_key"></span>)</span>
        </div>
        <div class="popup_content_text">
        	<div class="arm_debug_payment_log_loader arm_text_align_center arm_width_100_pct" style="margin: 45px auto; ">
        		<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL."/arm_loader.gif"; //phpcs:ignore?>">
        	</div>
        	<div class="arm_view_payment_debug_log armPageContainer" data-arm_selected_gateway=""></div>
        	<div class="armclear"></div>
        </div>
        <div class="armclear"></div>
	</div>
</div>



<div class="arm_view_debug_general_logs popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>">
	<div>
		<div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_view_debug_general_logs_close_btn"></span>
            <span class="add_rule_content"><?php esc_html_e('Debug Log Settings', 'armember-membership'); ?> (<span class="view_general_log_key"></span>)</span>
        </div>
        <div class="popup_content_text">
        	
        	<div class="arm_debug_general_log_loader arm_text_align_center arm_width_100_pct" style="margin: 45px auto; ">
        		<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL."/arm_loader.gif"; //phpcs:ignore?>">
        	</div>
        	<div class="arm_view_general_debug_log armPageContainer" data-arm_selected_gateway=""></div>
        </div>
        <div class="armclear"></div>
	</div>
</div>