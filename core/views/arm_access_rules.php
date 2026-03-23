<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_buddypress_feature,$arm_common_lite;
$arm_global_settings->arm_set_ini_for_access_rules();
$data_cols      = array();
$rule_types     = $arm_access_rules->arm_get_access_rule_types();
$all_plans      = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name' );
$cur_type       = 'post_type';
$cur_slug       = 'page';
$cur_plan       = '';
$cur_protection = 'all';
$filter_search  = ( ! empty( $_REQUEST['search'] ) ) ? sanitize_text_field($_REQUEST['search']) : ''; //phpcs:ignore
if ( isset( $_REQUEST['type'] ) && ! empty( $_REQUEST['type'] ) ) { //phpcs:ignore
	$cur_type = sanitize_text_field($_REQUEST['type']); //phpcs:ignore
}
$filter_slug = '';
if ( isset( $_REQUEST['slug'] ) && ! empty( $_REQUEST['slug'] ) ) { //phpcs:ignore
	$cur_slug = sanitize_text_field($_REQUEST['slug']); //phpcs:ignore
	$filter_slug = $cur_slug;
}
if ( ($cur_slug == 'buddypress' || $cur_slug == 'buddyboss') && (!$arm_buddypress_feature->isBuddypressFeature || !is_plugin_active('buddypress/bp-loader.php')) && $ARMemberLite->is_arm_pro_active) {
	wp_redirect('admin.php?page=arm_access_rules'); 
}

if (!in_array($cur_slug,array('post','page','category')) && !$ARMemberLite->is_arm_pro_active) {
	wp_redirect('admin.php?page=arm_access_rules'); 
}
if ( isset( $_REQUEST['plan'] ) && ! empty( $_REQUEST['plan'] ) ) { //phpcs:ignore
	$cur_plan = intval($_REQUEST['plan']); //phpcs:ignore
}
if ( isset( $_REQUEST['protection'] ) ) { //phpcs:ignore
	if ( $_REQUEST['protection'] == '0' ) { //phpcs:ignore
		$cur_protection = '0';
	} else {
		if ( ! empty( $_REQUEST['protection'] ) ) { //phpcs:ignore
			$cur_protection = sanitize_text_field($_REQUEST['protection']); //phpcs:ignore
		}
	}
}
$arm_reset_url = admin_url('admin.php?page=arm_access_rules');
$not_sortable     = '1,2,';
$rule_item_fields = '';
wp_enqueue_script( 'jquery-ui-tooltip' );
?>
<!--|End Add Edit Rule Pop-up|-->
<style type="text/css" title="currentStyle">
	
	.paginate_page a{display:none;}
	.wrap table.dataTable thead tr th, .wrap table.dataTable thead tr td,
	.wrap #armember_datatable_wrapper tr td{width: auto;}
	.wrap .DTFC_LeftBodyWrapper table tbody tr td:first-child{
		width:275px;
	}
	.wrap .DTFC_LeftBodyWrapper table tbody tr td:last-child{
		width:130px;
	}
	@media all and (min-width:1400px){
		.wrap .DTFC_LeftBodyWrapper table tbody tr td:first-child{
			width:255px !important;
		}
	}
	@media all and (min-width:1900px){
		.wrap .DTFC_LeftBodyWrapper table tbody tr td:first-child{
			width:221px !important;
		}
	}
	@media all and (max-width:769px){
		.DTFC_LeftBodyWrapper table.dataTable tbody tr td:first-child{
			width: 120px !important;
		}
	}
	
	.arm_ref_info_links:not(.arm_pg_important_note,.arm_submit_btn), a.arm_ref_info_links:not(.arm_pg_important_note,.arm_submit_btn){
		width: 210px !important;
		background-position-y: center;
	}

	#arm_update_rules{
		margin-right: 0 !important;
	}
	#arm_reset_rules{
		margin-right: 9px !important;
	}
	#arm_update_rules,
	#arm_reset_rules{
		text-align: center;
	}
</style>
<div class="wrap arm_page arm_access_rules_main_wrapper">
	<div class="content_wrapper arm_access_rules_container" id="content_wrapper">
		
		<div class="armclear"></div>
		<span class="arm_info_text arm-note-message --info arm_max_width_100_pct arm_margin_top_40"><?php esc_html_e( 'By default all content items will be accessible for all users. Once you turn ON the Default Restriction rule and select any plan(s) then it will be accessible for that selected plan(s) only.', 'armember-membership' ); ?></span>
		<span class="arm_info_text arm-note-message --alert arm_max_width_100_pct"><?php esc_html_e( 'Note: If you are using any caching plugin/mechanism on your site, then please clear your site cache after updating access rules table.', 'armember-membership' ); ?></span>
		<div class="page_title">
			<?php esc_html_e( 'Content Access Rules', 'armember-membership' ); ?>
			<?php if ( ! empty( $all_plans ) ) { ?>
			
			<div class="arm_add_new_item_box">
				<a href="javascript:void(0)" id="arm_reset_rules" class="arm_cancel_btn" onclick="reset_rule_protection_switch()"><?php esc_html_e( 'Reset Rules', 'armember-membership' ); ?></a>

				<a href="javascript:void(0)" id="arm_update_rules" class="arm_save_btn"><?php esc_html_e( 'Update Rules', 'armember-membership' ); ?></a>
				
			</div>
			<div class="arm_add_new_item_box arm_margin_right_20">
				<a class="arm_add_new_item_box arm_page_title_link arm_ref_info_links" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=access_restriction' ) ); //phpcs:ignore ?>" target="_blank"><?php esc_html_e( 'Check Default Access Rule', 'armember-membership' ); ?></a>
			</div>
			<div class="armclear"></div>
		</div>
			<?php
		}
		?>
		<div class="arm_solid_divider"></div>
		<div id="arm_access_rules_grid_wrapper" class="arm_access_rules_grid_wrapper" >
		<?php
				$is_filtered_css = 'display:none;';
				if(!empty($_REQUEST['type']) || !empty($_REQUEST['slug']) || !empty($_REQUEST['protection']) || (!empty($_REQUEST['plan']) && $_REQUEST['plan'] != 0))
				{
					$is_filtered_css = 'display:flex; width:100% !important';
				}
			?>
			<div class="arm_filter_wrapper" id="arm_filter_wrapper" style= '<?php echo $is_filtered_css;?>' >			
				<div class="arm_datatable_filters_options arm_filters_fields">
					<div class="sltstandard">
						<div class="arm_confirm_box_btn_container arm_margin_0">
							<div class="arm_dt_filter_block arm_datatable_searchbox">
								<div class="arm_datatable_filter_item">
									<label class="arm_padding_left_0">
										<input name="search" type="text" placeholder="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>" id="armmanagesearch_new" class="armmanagesearch_new armGridSearchBox" value="<?php echo sanitize_text_field($filter_search); ?>" tabindex="0">
									</label>
								</div>
							</div>
							<div class="arm_filter_child_row">
								<div>
									<input type="hidden" id="arm_rule_type_filter" name="type" value="<?php echo esc_attr($cur_type); ?>"/>
									<div class="arm_filter_restriction_box arm_datatable_filter_item">                        
										<input type="text" id="arm_rule_slug_filter" name="slug" class="arm_rule_slug_filter arm-selectpicker-input-control" data-type="<?php echo esc_attr($cur_type); ?>" value="<?php echo esc_attr($cur_slug); ?>"/>
										<dl class="arm_selectbox column_level_dd arm_width_250">
											<dt>
												<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.8333 6.10587L12.4167 2.66066C12 2.25053 11.4167 2.00444 10.75 2.00444H5.83334C4.58333 1.92241 3.5 2.98878 3.5 4.21921V15.7032C3.5 16.9336 4.5 18 5.83334 18H14.1667C15.4167 18 16.5 17.0157 16.5 15.7032V7.66441C16.5 7.09021 16.25 6.51601 15.8333 6.10587ZM7.5 8.48472H10C10.3334 8.48472 10.6667 8.7308 10.6667 9.14096C10.6667 9.55104 10.4166 9.79712 10 9.79712H7.5C7.16667 9.79712 6.83333 9.55104 6.83333 9.14096C6.83333 8.7308 7.16667 8.48472 7.5 8.48472ZM12.5 13.0783H7.5C7.16667 13.0783 6.83333 12.8322 6.83333 12.4221C6.83333 12.0119 7.08334 11.7658 7.5 11.7658H12.5C12.8333 11.7658 13.1666 12.0119 13.1666 12.4221C13.1666 12.8322 12.8333 13.0783 12.5 13.0783Z" fill="#9CA7BD"/></svg>
												<span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i>
											</dt>
											<dd>
												<ul data-id="arm_rule_slug_filter">
													<li data-label="<?php esc_attr_e( 'Select Type', 'armember-membership' ); ?>" data-value="page" data-type="post_type"><?php esc_html_e( 'Select Type', 'armember-membership' ); ?></li>
													<?php
													if ( ! empty( $rule_types ) ) {
														foreach ( $rule_types as $type => $opts ) {
															?>
															<ol><?php echo ucfirst( str_replace( '_', ' ', $type ) ); //phpcs:ignore ?></ol>
																		<?php
																			if ( is_array( $opts ) ) {
																				foreach ( $opts as $slug => $label ) {
																					?>
																	<li data-label="<?php echo esc_attr($label); ?>" data-value="<?php echo esc_attr($slug); ?>" data-type="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></li>
																					<?php
																				}
																			}
														}
													}
													?>
												</ul>
											</dd>
										</dl>
									</div>
								</div>
							</div>
							<div class="arm_filter_child_row">
								<div>
									<div class="arm_filter_restriction_box arm_datatable_filter_item">                        
										<input type="text" id="arm_rule_protection_filter" class="arm_rules_filter_input arm-selectpicker-input-control" name="protection" value="<?php echo esc_attr($cur_protection); ?>"/>
										<dl class="arm_selectbox column_level_dd arm_width_250">
											<dt>
												<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 2C5.60313 2 2 5.60313 2 10C2 14.3969 5.60313 18 10 18C14.3969 18 18 14.3969 18 10C18 5.60313 14.3969 2 10 2ZM3.90625 10C3.90625 6.64372 6.64372 3.90625 10 3.90625C11.2656 3.90625 12.4844 4.29997 13.525 5.04059L10 8.56559L5.04066 13.525C4.29997 12.4843 3.90625 11.2656 3.90625 10ZM10 16.0938C8.73441 16.0938 7.51559 15.7 6.475 14.9593L14.9594 6.47497C15.7 7.51559 16.0938 8.73434 16.0938 10C16.0938 13.3562 13.3563 16.0938 10 16.0938Z" fill="#9CA7BD"/></svg>
												<span class="arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i>
											</dt>
											<dd>
												<ul data-id="arm_rule_protection_filter">
													<li data-label="<?php esc_attr_e( 'Select Default Restriction', 'armember-membership' ); ?>" data-value="all"><?php esc_html_e( 'Select Default Restriction', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'On', 'armember-membership' ); ?>" data-value="1"><?php esc_html_e( 'On', 'armember-membership' ); ?></li>
													<li data-label="<?php esc_attr_e( 'Off', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Off', 'armember-membership' ); ?></li>
												</ul>
											</dd>
										</dl>
									</div>
								</div>
							</div>
							<div class="arm_filter_child_row">
								<div>
									<?php if ( ! empty( $all_plans ) ) :
										$plan = !empty($_REQUEST['plan']) ? $_REQUEST['plan']: ''; ?>
										<div class="arm_filter_plans_box arm_datatable_filter_item">                        
											<input type="text" id="arm_subs_filter" class="arm_subs_filter arm-selectpicker-input-control" value="<?php echo !empty($plan) ? esc_attr($plan): 0; ?>" />
											<dl class="arm_selectbox arm_width_250">
												<dt>
													<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7522_5770)"><rect x="6" y="2" width="8" height="16" rx="2" fill="#9CA7BD"/><path opacity="0.4" d="M11 4H16C17.1046 4 18 4.89543 18 6V14C18 15.1046 17.1046 16 16 16H11V4Z" fill="#9CA7BD"/><path opacity="0.4" d="M2 6C2 4.89543 2.89543 4 4 4H9V16H4C2.89543 16 2 15.1046 2 14V6Z" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7522_5770"><rect width="16" height="16" fill="white" transform="translate(2 2)"/></clipPath></defs></svg>
													<span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i>
												</dt>
												<dd>
													<ul data-id="arm_subs_filter" data-placeholder="<?php esc_attr_e( 'Select Plans', 'armember-membership' ); ?>">
														<li data-label="<?php  esc_attr_e( 'Select Plans', 'armember-membership' ); //phpcs:ignore ?>" data-value="0"><?php  esc_attr_e( 'Select Plans', 'armember-membership' ); //phpcs:ignore ?></li>
														<?php foreach ( $all_plans as $plan ) : ?>
															<li data-label="<?php echo stripslashes( esc_attr( $plan['arm_subscription_plan_name'] ) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></li>
														<?php endforeach; ?>
													</ul>
												</dd>
											</dl>
										</div>
									<?php endif;?>
								</div>
							</div>
							<div class="arm_filter_child_row">
								<div>
									<input type="button" name="search" class="armemailaddbtn arm_access_rule_grid_filter_btn" id="arm_access_rule_grid_filter_btn" value="<?php esc_html_e('Apply','armember-membership');?>">
									<a href="<?php echo admin_url('admin.php?page=arm_access_rules');?>" class="arm_cancel_btn arm_access_rule_reset hidden_section"><?php esc_html_e('Clear','armember-membership');?></a>
								</div>
							</div>
						</div>	
					</div>
					<div class="arm_filter_hide_show_btn_section arm_hide">
						<button type="button" class="arm_filter_hide_show_btn" id="arm_filter_hide_show_btn" data-status="0">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7619_15796)"><g clip-path="url(#clip1_7619_15796)"><path d="M17 1H3C1.89543 1 1 1.89557 1 3.00031V4.17207C1 4.70259 1.21071 5.21137 1.58579 5.58651L7.41421 11.4158C7.78929 11.791 8 12.2998 8 12.8302V18.0027V18.2884C8 18.9211 8.7649 19.2379 9.2122 18.7906L10 18.0027L11.4142 16.5882C11.7893 16.2131 12 15.7043 12 15.1738V12.8302C12 12.2998 12.2107 11.791 12.5858 11.4158L18.4142 5.58651C18.7893 5.21137 19 4.70259 19 4.17207V3.00031C19 1.89557 18.1046 1 17 1Z" stroke="#617191" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></g></g><defs><clipPath id="clip0_7619_15796"><rect width="20" height="20" fill="white"/></clipPath><clipPath id="clip1_7619_15796"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>
						</button>
					</div>
				</div>
			</div>
			<?php if ( ! empty( $all_plans ) ) : ?>
			<div class="arm_datatable_filters arm_rules_filters">
				<form method="get" action="<?php echo admin_url( 'admin.php' ); //phpcs:ignore ?>" class="arm_rules_filter_form">
					<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->access_rules); //phpcs:ignore ?>"/>
				</form>
				<div class="armclear"></div>
			</div>
			<div class="armclear"></div>
			<form method="POST" id="arm_access_rules_list_form" class="data_grid_list">
			<div id="arm_rule_grid_list" class="arm_rule_grid_list">
				<?php
				$rule_args    = array(
					'type'       => $cur_type,
					'slug'       => $cur_slug,
					'plan'       => $cur_plan,
					'protection' => $cur_protection,
				);
				$rule_records = $arm_access_rules->arm_prepare_rule_data( $rule_args );
				// Table Records
				$data_cols = array();
				if ( ! empty( $rule_records ) ) {
					foreach ( $rule_records as $item ) {
							$item_id    = $item['id'];
							$item_plans = ( ! empty( $item['plans'] ) ) ? $item['plans'] : array();
							$title_text = $item['title'];
							$pdata_cols = array();
						if ( isset( $item['description'] ) && ! empty( $item['description'] ) ) {
							$title_text .= '<span class="arm_rule_item_description">' . $item['description'] . '</span>';
						}
							$pdata_cols[] = $title_text;

							// For Protection
							$switchChecked   = ( $item['protection'] == 1 ) ? 'checked="checked"' : '';
							$protection_html = '<div class="armswitch">
								<input type="checkbox" class="armswitch_input arm_rule_protection_action" id="arm_rule_protection_input_' . esc_attr($item_id) . '" name="arm_rules[' . esc_attr($item_id) . '][protection]" value="1" data-item_id="' . esc_attr($item_id) . '" ' . $switchChecked . '>
								<label class="armswitch_label" for="arm_rule_protection_input_' . esc_attr($item_id) . '"></label>
							</div>';
							$pdata_cols[]    = $protection_html;
							// For Plan Data
							if (!empty($all_plans)){
														$plan_id       = '-2';
														$item_checked  = ( in_array( $plan_id, $item_plans ) ) ? 'checked="checked"' : '';
														$pdata_cols[] .= '<input type="checkbox" name="arm_rules[' . esc_attr($item_id) . '][plans][]" value="' . esc_attr($plan_id) . '" class="arm_rule_item_checkbox_' . esc_attr($item_id) . '_' . esc_attr($plan_id) . ' arm_no_plan_rule arm_rule_plan_chks" data-item_id="' . esc_attr($item_id) . '" data-plan_id="' . esc_attr($plan_id) . '" ' . $item_checked . '/>';
							foreach ( $all_plans as $sp ) {
								$plan_id      = $sp['arm_subscription_plan_id'];
								$item_checked = ( in_array( $plan_id, $item_plans ) ) ? 'checked="checked"' : '';
								$plan_html    = '';
								$plan_html   .= '<input type="checkbox" name="arm_rules[' . esc_attr($item_id) . '][plans][]" value="' . esc_attr($plan_id) . '" class="arm_rule_item_checkbox_' . esc_attr($item_id) . '_' . esc_attr($plan_id) . ' arm_rule_plan_chks" data-item_id="' . esc_attr($item_id) . '" data-plan_id="' . esc_attr($plan_id) . '" ' . $item_checked . '/>';
								$pdata_cols[] = $plan_html;
							}
						}
						$data_cols[] = array_values( $pdata_cols );
					}
				}
				?>
				<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>
				<table cellpadding="0" cellspacing="0" border="0" class="display arm_datatable arm_no_margin
				<?php
				if ( ! empty( $rule_records ) ) {
					echo ' arm_hide_datatable'; }
				?>
				" id="armember_datatable" width="100%">
				<thead>
				<?php
				$title_cols = $filter_cols = '';
				if (!empty($all_plans)) {
									$title_cols  .= '<th class="arm-no-sort center arm_text_align_center">' . esc_html__( 'Users Having No Plan', 'armember-membership' ) . '</th>';
									$filter_cols .= '<th class="arm-no-sort center arm_text_align_center"><input type="checkbox" class="arm_all_rules_checkbox_-2 arm_all_rule_plan_chks" data-plan_id="-2" /><br/><label class="arm_font_size_14">' . esc_html__( 'Allow Access', 'armember-membership' ) . '</label></th>';
					$i                            = 3;
					foreach ( $all_plans as $sp ) {
						$plan_id    = $sp['arm_subscription_plan_id'];
						$plan_title = stripslashes( $sp['arm_subscription_plan_name'] );

									$title_cols      .= '<th class="arm-no-sort center arm_text_align_center">' . $plan_title . '</th>';
										$filter_cols .= '<th class="arm-no-sort center arm_text_align_center"><input type="checkbox" class="arm_all_rules_checkbox_' . esc_attr($plan_id) . ' arm_all_rule_plan_chks" data-plan_id="' . esc_attr($plan_id) . '" /><br/><label class="arm_font_size_14">' . esc_html__( 'Allow Access', 'armember-membership' ) . '</label></th>';
									$not_sortable    .= "$i,";
									$i++;
					}
				}
				?>
							<?php if ( ! empty( $rule_records ) ) { ?>
							<tr class="arm_grid_main_header">
								<th class="arm_text_align_left arm_width_100_pct"><?php esc_html_e( 'Title', 'armember-membership' ); ?></th>
									<th class="arm-no-sort center arm_text_align_center"><label><?php esc_html_e( 'Default Restriction', 'armember-membership' ); ?><i class="arm_helptip_icon_ui arm_fixed_column_icon armfa armfa-question-circle tipso_style" title="<?php esc_html_e( 'If you enable Default Restriction, that item will be restricted for visitors and all the loggedin users except for those users whose plan are allowed here.', 'armember-membership' ); ?>"></i></label></th>
								<?php echo $title_cols; //phpcs:ignore ?>
							</tr>
							<tr class="arm_grid_filter_header">
								<th class="arm-no-sort center" id="arm_title_search_box_th">
									<div class="armGridSearchBox_filter arm_datatable_searchbox arm_float_left hidden_section" id="armGridSearchBox_filter" >									
										<input type="hidden" name="armGridSearchBox_hidden" id="armGridSearchBox_hidden">
									</div>
								</th>
								<th class="arm-no-sort center arm_text_align_center">
									<input class="arm_all_restriction" type="checkbox"></th>
								<?php echo $filter_cols; //phpcs:ignore ?>
							</tr>
							<?php } else { ?>
							<tr><td class="arm_access_rules_empty"><?php esc_html_e( 'No Record(s) Found', 'armember-membership' ); ?></td></tr>
							<?php } ?>
						</thead>
					</table>
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e( 'Show / Hide columns', 'armember-membership' ); ?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e( 'entries', 'armember-membership' ); ?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e( 'Show', 'armember-membership' ); ?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e( 'Showing', 'armember-membership' ); ?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e( 'to', 'armember-membership' ); ?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e( 'of', 'armember-membership' ); ?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e( 'No matching records found.', 'armember-membership' ); ?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e( 'No any record found.', 'armember-membership' ); ?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e( 'filtered from', 'armember-membership' ); ?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e( 'total', 'armember-membership' ); ?>"/>
					<input type="hidden" name="original_access_rules" id="original_access_rules" value=""/>					
				</div>
				<div class="footer_grid"></div>
				<input type="hidden" name="type" value="<?php echo esc_attr($cur_type); ?>" class="arm_rule_type_field_input" />
				<input type="hidden" name="slug" value="<?php echo esc_attr($cur_slug); ?>" class="arm_rule_slug_field_input"/>
				<?php
				
					$arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
				?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($arm_wp_nonce);?>" />
			</form>
			<div class="arm_rule_item_fields" style="display:none;">
			</div>
			<?php else : ?>
			<h4 class="arm_no_access_rules_message ">
				<?php esc_html_e( 'There is no any plan configured yet', 'armember-membership' ); ?>, <a href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->manage_plans . '&action=new' ) ); //phpcs:ignore ?>" class="arm_ref_info_links" target="_blank"><?php esc_html_e( 'Please add new plan.', 'armember-membership' ); ?></a>
			</h4>
			<?php endif; ?>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<?php
$not_sortable = trim( $not_sortable, ',' );
wp_print_scripts( array( 'sack' ) );
?>
<script type="text/javascript">
// <![CDATA[
var armRulesOriginal = {};

jQuery(document).ready(function ($){
	var armRules = {};
	var armfnInitCompleteFlag = 0;
	var armDefaultRules = {};
	var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','armember-membership')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 entries','armember-membership')); //phpcs:ignore?>';
    var __ARM_to = '-';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','armember-membership')); //phpcs:ignore?>';
    var __ARM_RECORDS = '<?php echo addslashes(esc_html__('entries','armember-membership')); //phpcs:ignore?>';   
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No any record found.','armember-membership')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','armember-membership')); //phpcs:ignore?>';
	var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
	var DTable = jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"CBfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"oLanguage": {
			"sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_RECORDS,
			"sInfoEmpty": __ARM_Showing_empty,
			"sLengthMenu": __ARM_Show + "_MENU_",
			"sEmptyTable": __ARM_NO_FOUND,
			"sZeroRecords": __ARM_NO_MATCHING
		},
		"buttons":[],
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth" : false,
		"sScrollX": "100%",
		"bScrollCollapse": true,
		"aaData": <?php echo wp_json_encode( $data_cols ); ?>,
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [0, <?php echo $not_sortable; //phpcs:ignore ?>] },
		],
		"aaSorting": [],
		"aLengthMenu": [10, 25, 50, 100, 150, 200, 300, 400, 500],
		"fnCreatedRow": function( nRow, aData, iDataIndex ) {
			var item_id = jQuery(nRow).find('input[type=radio]:checked').attr('data-item_id');
			jQuery(nRow).attr('data-item_id', item_id);
		},
		"fnInitComplete": function( settings, json ) {
			var tableData = this.fnGetNodes();
			jQuery(tableData).each(function(i, elements){
				var item_id = jQuery(elements).find('.arm_rule_protection_action').attr('data-item_id');
				var pVal = '0';
				if(jQuery(elements).find('.arm_rule_protection_action').is(':checked')){
					pVal = '1';
				}
								var no_plan= '0'
								if(jQuery(elements).find('.arm_no_plan_rule').is(":checked")){
									no_plan = '1';
								}
				armDefaultRules[item_id] = {};
				armDefaultRules[item_id]["protection"] = pVal;
								armDefaultRules[item_id]["no_plan"] = no_plan;
				armDefaultRules[item_id]["plans"] = {};
				armRules[item_id] = {};
				armRules[item_id]["protection"] = pVal;
				armRules[item_id]["no_plan"] = no_plan;			
				armRules[item_id]["plans"] = {};
				jQuery(elements).each(function(i, ele){
					armDefaultRules[item_id]["item_id"] = item_id;
					armRules[item_id]["item_id"] = item_id;
					jQuery(ele).find('input.arm_rule_plan_chks').each(function(i, ele){
						var plan_id = $(this).val();
						if(jQuery(this).is(':checked')){
							armDefaultRules[item_id]["plans"][plan_id] = '1';
							armRules[item_id]["plans"][plan_id] = '1';
						}
					});
				});
			});
		},
		"fnPreDrawCallback": function () {
			jQuery('.DTFC_ScrollWrapper').hide();
			jQuery('.footer').hide();
			jQuery('.arm_loading_grid').show();
		},
		"fnDrawCallback": function () {
			var tableData = this.fnGetNodes();
			setTimeout(function(){
				jQuery('.arm_loading_grid').hide();
				arm_show_data();
				if(armfnInitCompleteFlag==1)
				{
					let armrules_len = 0;
					for(let armRules_cnt in armRules) { armrules_len++; }

					if(armrules_len > 0)
					{
						jQuery(tableData).each(function(i, elements){
							
							var item_id = jQuery(elements).find('.arm_rule_plan_chks').attr('data-item_id');
							if(typeof item_id != 'undefined'){
								jQuery(elements).each(function(i, ele){
									jQuery(ele).find('input.arm_rule_plan_chks').each(function(i, ele) {
										var plan_id = $(this).val();
										if(armRules[item_id]["protection"]=="1"){
											if(typeof armRules[item_id]["plans"][plan_id] != 'undefined' && armRules[item_id]["plans"][plan_id]=='1') {
												jQuery(".arm_rule_item_checkbox_"+item_id+"_"+plan_id).prop('checked', true);
											}
										}
									});
									if(armRules[item_id]["protection"]=="1"){
										jQuery("input[name='arm_rules["+item_id+"][protection]']").prop('checked', true);
									}
									else {
										jQuery("input[name='arm_rules["+item_id+"][protection]']").prop('checked', false);
									}
									if(armRules[item_id]["no_plan"]=="1"){
										jQuery(".arm_rule_item_checkbox_"+item_id+"_-2").prop('checked', true);
									}
									else {
										jQuery(".arm_rule_item_checkbox_"+item_id+"_-2").prop('checked', false);
									}
								});
							}
						});
					}
				}
				DTable.dataTable().fnAdjustColumnSizing(false);
			}, 1000);
			jQuery('.DTFC_ScrollWrapper').show();
			jQuery('.footer').show();
		},
	});

	var filter_box = jQuery('#arm_filter_wrapper').html();
	jQuery('.arm_filter_grid_list_container').find('.arm_datatable_filters_options').remove();
	jQuery('div#armember_datatable_filter').parent().append(filter_box);
	jQuery('div#armember_datatable_filter').hide();
	jQuery('div#arm_filter_wrapper').hide();
	jQuery('.arm_access_rule_grid_filter_btn').removeAttr('disabled');

	var oFC = new FixedColumns(DTable, {
		"iLeftColumns": 3,
		"iLeftWidth": '510',
		"iRightColumns": 0,
		"iRightWidth": 0,
	});
	oFC.fnRedrawLayout();
		if (jQuery('.arm_rule_protection_action').length == jQuery('.arm_rule_protection_action:checked').length) {
			jQuery(".arm_all_restriction").data('checked', 1);
			jQuery(".arm_all_restriction").prop("checked", true);
		} else {
			jQuery(".arm_all_restriction").data('checked', 0);
			jQuery(".arm_all_restriction").prop("checked", false);
		}
		// set here because user no having plan field indeterminate not working.
		jQuery('.arm_all_rule_plan_chks').each(function() {
			var $this = jQuery(this);
			var plan_id = $this.attr('data-plan_id');
			var allInputs = jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').length;
			var checked = jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]:checked').length;
			if (checked == 0) {
				$this.data('checked', 0);
				$this.prop('indeterminate', false);
				$this.prop('checked', false);
			} else if(checked == allInputs) {
				$this.data('checked', 1);
				$this.prop('indeterminate', false);
				$this.prop('checked', true);
			} else {
				$this.attr('data-checked', 0);
				$this.prop('indeterminate', false);
				$this.prop('checked', false);
			}
		});
	jQuery(document).on( 'keyup','.armmanagesearch_new', function (e) {
		if(e.keyCode == 13)
		{
			var armGridSearchBox = jQuery(this).val();
			var armGridSearchBox_hidden = jQuery("#armGridSearchBox_hidden").val();
			if(armGridSearchBox!=armGridSearchBox_hidden)
			{
				jQuery("#armGridSearchBox_hidden").val(jQuery(this).val());
				armfnInitCompleteFlag = 1;
				DTable._fnReDraw();
			}
		}
	});
	jQuery.fn.dataTableExt.afnFiltering.push(function(oSettings, aData, iDataIndex) {
		var iSearch = jQuery('.armmanagesearch_new').last().val();
		var iVersion = aData[0];
		if (iVersion == "") {
			return true;
		} else if(iVersion.indexOf(iSearch) != -1){
			return true;
		} else if(iVersion.toLowerCase().indexOf(iSearch.toLowerCase()) != -1){
			return true;
		}
		return false;
	});

	armRulesOriginal = arm_rule_init(armRules);
	
	document.getElementById('original_access_rules').value = JSON.stringify(armRulesOriginal);
	
	jQuery(document).on('click', '#arm_update_rules', function () {
		var $this = jQuery(this);
		if (!$this.hasClass('arm_already_clicked')) {
			$this.addClass('arm_already_clicked').attr('disabled', 'disabled');
			jQuery('.arm_loading').fadeIn('slow');
			var type = jQuery('.arm_rule_type_field_input').val();
			var slug = jQuery('.arm_rule_slug_field_input').val();
			var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
			var form_data = JSON.stringify(armRules);
			var form_data_original = document.getElementById('original_access_rules').value;

			jQuery.ajax({
				type: "POST",
				url: __ARMAJAXURL,
				dataType: 'json',
				data: {action:"arm_update_access_rules", type: type, slug: slug, form_data: form_data, form_data_original: form_data_original, _wpnonce:_wpnonce},
				success: function (res) {
					if (res.type == 'success') {
						armToast(res.msg, 'success');
						armRulesOriginal = armRules;
						document.getElementById('original_access_rules').value = JSON.stringify(armRulesOriginal);
					} else {
						armToast(res.msg, 'error');
						armRulesOriginal = armRules;
						document.getElementById('original_access_rules').value = JSON.stringify(armRulesOriginal);
					}
					jQuery('.arm_loading').fadeOut();
				}
			});
		}
	});
	jQuery(document).on('click', '.arm_rule_protection_action', function () {
		var pVal = '0';
		if (jQuery(this).is(':checked')) {
			pVal = '1';
		}
		var item_id = jQuery(this).attr('data-item_id');
		armRules[item_id]["protection"] = pVal;
		if(pVal == '0')
		{
			jQuery('input.arm_rule_plan_chks[data-item_id="' + item_id + '"]:checked').each(function() {
				jQuery(this).prop('checked', false);
				var plan_id = jQuery(this).attr('data-plan_id');
				armRules[item_id]["plans"][plan_id] = '0';
				if(plan_id == '-2')
				{
					armRules[item_id]["no_plan"] = '0';
				}
				if (jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').length == jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]:checked').length) {
					jQuery(".arm_all_rules_checkbox_" + plan_id).data('checked', 1);
					jQuery(".arm_all_rules_checkbox_" + plan_id).prop("checked", true);
				} else {
					jQuery(".arm_all_rules_checkbox_" + plan_id).data('checked', 0);
					jQuery(".arm_all_rules_checkbox_" + plan_id).prop("checked", false);
				}
			});
		}
		if (jQuery('.arm_rule_protection_action').length == jQuery('.arm_rule_protection_action:checked').length) {
			jQuery(".arm_all_restriction").data('checked', 1);
			jQuery(".arm_all_restriction").prop("checked", true);
		} else {
			jQuery(".arm_all_restriction").data('checked', 0);
			jQuery(".arm_all_restriction").prop("checked", false);
		}
	});
	jQuery(document).on('click', '.arm_no_plan_rule', function () {
		var pVal = '0';
		if (jQuery(this).is(':checked')) {
			pVal = '1';
		}
		var item_id = jQuery(this).attr('data-item_id');
		armRules[item_id]["no_plan"] = pVal;
	});
	jQuery(document).on('click', '.arm_rule_plan_chks', function () {
		var item_id = jQuery(this).attr('data-item_id');
		var plan_id = jQuery(this).attr('data-plan_id');
		if(jQuery(this).is(':checked')){
			if(!jQuery('#arm_rule_protection_input_' + item_id).is(':checked')){
				jQuery('#arm_rule_protection_input_' + item_id).prop('checked', true);
				armRules[item_id]["protection"] = '1';
			}
			armRules[item_id]["plans"][plan_id] = '1';
		} else {
			if (jQuery('input.arm_rule_plan_chks[data-item_id=' + item_id + ']:checked').length == 0) {
				if (jQuery('#arm_rule_protection_input_' + item_id).is(':checked')) {
					jQuery('#arm_rule_protection_input_' + item_id).prop('checked', false);
					armRules[item_id]["protection"] = '0';
				}
			}
			delete armRules[item_id]["plans"][plan_id];
		}
		if (jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').length == jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]:checked').length) {
			jQuery(".arm_all_rules_checkbox_" + plan_id).data('checked', 1);
			jQuery(".arm_all_rules_checkbox_" + plan_id).prop("checked", true);
		} else {
			jQuery(".arm_all_rules_checkbox_" + plan_id).data('checked', 0);
			jQuery(".arm_all_rules_checkbox_" + plan_id).prop("checked", false);
		}
	});
	
	jQuery(document).on('click', '.arm_all_rule_plan_chks', function () {
		var $this = jQuery(this);
		var plan_id = $this.attr('data-plan_id');
		switch ($this.data('checked')) {
		  case 1:
			$this.data('checked', 0);
			$this.prop('indeterminate', true);
			$this.prop('checked', false);
			jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').each(function() {
				var item_id = jQuery(this).attr('data-item_id');
				if (armDefaultRules[item_id]["plans"][plan_id] != undefined && armDefaultRules[item_id]["plans"][plan_id] == '1') {
					jQuery(this).prop('checked', true);
					armRules[item_id]["plans"][plan_id] = '1';
				} else {
					jQuery(this).prop('checked', false);
					delete armRules[item_id]["plans"][plan_id];
				}
				if (jQuery('input.arm_rule_plan_chks[data-item_id="' + item_id + '"]:checked').length > 0) {
					jQuery('#arm_rule_protection_input_' + item_id).prop('checked', true);
					armRules[item_id]["protection"] = '1';
				} else {
					jQuery('#arm_rule_protection_input_' + item_id).prop('checked', false);
					armRules[item_id]["protection"] = '0';
				}
				
				if (plan_id=='-2')
				{
					if( jQuery('input.arm_rule_plan_chks[data-item_id="' + item_id + '"]:checked').length > 0 )
					{
						armRules[item_id]["no_plan"] = '1';
					} else {
						armRules[item_id]["no_plan"] = '0';
					}
				}
				jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').each(function(){
					var item_id = jQuery(this).attr('data-item_id');
					if (jQuery(this).is(':checked')) {
						jQuery(this).prop('checked', false);
						delete armRules[item_id]["plans"][plan_id];
						if (jQuery('input.arm_rule_plan_chks[data-item_id="' + item_id + '"]:checked').length == 0) {
							armRules[item_id]["protection"] = '0';
							jQuery('#arm_rule_protection_input_' + item_id).prop('checked', false);
							if(plan_id=='-2')
							{
								armRules[item_id]["no_plan"] = '0';	
							}
						}
					}
				});
			});
			break;
		  case 0:
			$this.data('checked', 1);
			$this.prop('indeterminate', false);
			$this.prop('checked', true);
			jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').each(function() {
				var item_id = jQuery(this).attr('data-item_id');
				if (!jQuery(this).is(':checked')) {
					jQuery(this).prop('checked', true);
					jQuery('#arm_rule_protection_input_' + item_id).prop('checked', true);
					jQuery('.arm_rule_item_checkbox_'+item_id+'_'+plan_id).prop('checked', true);
					armRules[item_id]["protection"] = '1';
					armRules[item_id]["plans"][plan_id] = '1';
					if (plan_id=='-2')
					{
						armRules[item_id]["no_plan"] = '1';
					}
				}
			});
			break;
		  default:
			$this.data('checked', 0);
			$this.prop('indeterminate', false);
			$this.prop('checked', false);
			jQuery('input.arm_rule_plan_chks[data-plan_id="' + plan_id + '"]').each(function(){
				var item_id = jQuery(this).attr('data-item_id');
				if (jQuery(this).is(':checked')) {
					jQuery(this).prop('checked', false);
					delete armRules[item_id]["plans"][plan_id];
					if (jQuery('input.arm_rule_plan_chks[data-item_id="' + item_id + '"]:checked').length == 0) {
						armRules[item_id]["protection"] = '0';
						jQuery('#arm_rule_protection_input_' + item_id).prop('checked', false);
						if(plan_id=='-2')
						{
							armRules[item_id]["no_plan"] = '0';	
						}
					}
				}
			});
		}
	});
		
	jQuery(document).on('click', '.arm_all_restriction', function() {
		var item_id = 0;
		if (jQuery(this).is(':checked')) {
			jQuery(jQuery('.arm_rule_protection_action').not(':checked')).each(function(){
				jQuery(this).prop('checked', true);
				item_id = jQuery(this).attr('data-item_id');
				armRules[item_id]["protection"] = '1';
			});
		} else {
			jQuery('.arm_rule_protection_action:checked').each(function(){
				jQuery(this).prop('checked', false);
				item_id = jQuery(this).attr('data-item_id');
				armRules[item_id]["protection"] = '0';
			});
		}
	});
	
	var armmanagesearch_new_val = jQuery('#arm_access_rules_list_form input[name="search"]').val();
	if(armmanagesearch_new_val!='')
	{
		var armGridSearchBox_hidden_val = jQuery("#armGridSearchBox_hidden").val();
		if(armmanagesearch_new_val!=armGridSearchBox_hidden_val)
		{
			jQuery("#armGridSearchBox_hidden").val(armmanagesearch_new_val);
			DTable._fnReDraw();
		}
	}

	<?php 
	if( ( !empty($_REQUEST['type']) && $_REQUEST['type']!='post_type') || ( !empty($_REQUEST['slug']) && $_REQUEST['slug']!='page') || ( !empty($_REQUEST['protection']) && $_REQUEST['protection']!='all') || ( !empty($_REQUEST['plan']) && $_REQUEST['plan']!='0' ) || !empty($_REQUEST['search']) ) //phpcs:ignore
	{
	?>
		jQuery('.arm_cancel_btn.arm_access_rule_reset').removeClass('hidden_section');
	<?php
	}
	?>
});

jQuery(document).on('click','#arm_member_grid_filter_clr_btn',function(){
	hideConfirmBoxCallback_filter('manage_access_rule_filter');
});

jQuery(document).on('click','.arm_access_rule_grid_filter_btn',function(){
	var url = window.location.href;
	var rule_type = jQuery('input[name="type"]').val();
	var rule_post_type = jQuery('input[name="slug"]').val();
	var rule_type = jQuery('input[name="slug"]').attr('data-type');
	url = url+'&type='+rule_type;
	url = url+'&slug='+rule_post_type;
	var rule_protection = jQuery('input#arm_rule_protection_filter').val();
	url = url+'&protection='+rule_protection+'&plan='+rule_plan;
	var rule_plan = jQuery('input#arm_subs_filter').val();
	if(typeof rule_plan != 'undefined' && rule_plan != '')
	{
		url = url+'&plan='+rule_plan;
	}
	else{
		url = url+'&plan=';
	}
	var armmanagesearch_new = jQuery('#arm_access_rules_list_form input[name="search"]').val();
	url = url+'&search='+encodeURIComponent(armmanagesearch_new);
	
	

	window.location.href= url;
});

function reset_rule_protection_switch() {
	jQuery('.arm_rule_protection_action').each(function(){
		var item_id = jQuery(this).attr('data-item_id');
		if (jQuery(this).is(':checked')) {
			if (jQuery('input.arm_rule_plan_chks[data-item_id=' + item_id + ']:checked').length === 0) {
				jQuery(this).trigger('click');
			}
		}
	});
}
jQuery(window).on('load', function() {
	arm_tooltip_init();
});
// ]]>
</script>
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('content-access-rules'); //phpcs:ignore
?>