<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_directory, $arm_subscription_plans;
$member_templates  = $arm_members_directory->arm_get_all_member_templates();
$defaultTemplates  = $arm_members_directory->arm_default_member_templates();
$tempColorSchemes  = $arm_members_directory->getTemplateColorSchemes();
$tempColorSchemes1 = $arm_members_directory->getTemplateColorSchemes1();
$subs_data         = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name' );

$fonts_option = array('title_font'=>array('font_family'=>'Poppins','font_size'=>'16','font_bold'=>'1','font_italic'=>'0','font_decoration'=>'',),'subtitle_font'=>array('font_family'=>'Poppins','font_size'=>'13','font_bold'=>'0','font_italic'=>'0','font_decoration'=>'',),'button_font'=>array('font_family'=>'Poppins','font_size'=>'14','font_bold'=>'0','font_italic'=>'0','font_decoration'=>'',),'content_font'=>array('font_family'=>'Poppins','font_size'=>'15','font_bold'=>'1','font_italic'=>'0','font_decoration'=>'',));

?>
<div class="wrap arm_page arm_profiles_directories_main_wrapper armPageContainer arm_padding_0">
	<?php
	$arm_activated = 'arm_visible';
	if(isset($_GET['action']) && $_GET['action'] == 'duplicate_temp') //phpcs:ignore
	{
		$arm_activated = '';
	}

	?>
	<div class="content_wrapper arm_profiles_directories_container arm_min_height_500" id="content_wrapper">
		<div class="page_title"><?php esc_html_e( 'Profiles & Directories', 'armember-membership' ); ?></div>
		<div class="arm_page_spacing_div"></div>
		<div class="arm_belt_box arm_membership_template_belt">
			<div class="arm_belt_block">
				<div class="page_sub_title"><?php esc_html_e( 'Member Profile Templates', 'armember-membership' ); ?></div>
			</div>		
			<div class="arm_belt_block" align="<?php echo is_rtl() ? 'left' : 'right'; ?>">
				<div class="arm_membership_setup_shortcode_box" >
					<span class="arm_membership_setup_shortcode_title"><?php esc_html_e( 'Shortcode', 'armember-membership' ); ?></span>
					<?php $shortCode = '[arm_template type="profile" id="1"]'; ?>
					<div class="arm_shortcode_text arm_form_shortcode_box" style="width:auto;">
						<span class="armCopyText"><?php echo esc_attr( $shortCode ); ?></span>
						<span class="arm_click_to_copy_text" data-code="<?php echo esc_attr( $shortCode ); ?>"><?php esc_html_e( 'Click to copy', 'armember-membership' ); ?></span>
						<span class="arm_copied_text"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/copied_ok.png" alt="ok"/><?php esc_html_e( 'Code Copied', 'armember-membership' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<div class="arm_belt_box arm_member_profile_action_belt hidden_section">
			<div class="arm_belt_block arm_membership_directory">
				<div class="page_sub_title"><?php esc_html_e( 'Member Directory Templates', 'armember-membership' ); ?></div>
			</div>
			<div class="arm_belt_block arm_membership_card hidden_section">
				<div class="page_sub_title"><?php esc_html_e( 'Member Card Templates', 'armember-membership' ); ?></div>
			</div>
		</div>
		<div class="arm_profiles_directories_templates_container">
			<div class="arm_profiles_directories_content arm_margin_top_48 <?php echo esc_attr($arm_activated);?>">
				<div id="arm_profile_templates_container" class="page_sub_content arm_profile_templates_container">
					
					<div id="arm_profile_templates" class="arm_profile_templates arm_pdt_content">
						<?php



						if ( ! empty( $member_templates['profile'] ) ) {
							foreach ( $member_templates['profile'] as $ptemp ) {


								$t_id              = $ptemp['arm_id'];
								$t_title           = !empty($ptemp['arm_title']) ? stripslashes_deep($ptemp['arm_title']) : '';
								$t_type            = $ptemp['arm_type'];
								$t_options         = maybe_unserialize( $ptemp['arm_options'] );
								$t_link_attr       = ' data-id="' . esc_attr($t_id) . '" data-type="' . esc_attr($t_type) . '" ';
								$t_container_class = '';
								$t_img_url         = MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $ptemp['arm_slug'] . '.svg';

								$default    = $ptemp['arm_default'];
								$plan_names = '';

								if ( $default == 1 ) {
									$plan_names = esc_html__( 'Default Profile Template', 'armember-membership' );
								} else {
									$subscription_plans = $ptemp['arm_subscription_plan'];
									if ( $subscription_plans == '' ) {
										$plan_names = '<strong>' . esc_html__( 'Associated Plans:', 'armember-membership' ) . '</strong><br/>' . esc_html__( 'No plan selected', 'armember-membership' );
									} else {
										$plan_name_array = explode( ',', $subscription_plans );
										$super_admin_placeholders = 'WHERE arm_subscription_plan_id IN (';
										$super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $plan_name_array ) ), ',' );
										$super_admin_placeholders .= ')';
										array_unshift( $plan_name_array, $super_admin_placeholders );
										$sub_where = call_user_func_array(array( $wpdb, 'prepare' ), $plan_name_array );

										$plan_names_db   = $wpdb->get_results( 'SELECT `arm_subscription_plan_name` FROM ' . $ARMemberLite->tbl_arm_subscription_plans . ' '.$sub_where );//phpcs:ignore --Reason: $tbl_arm_subscription_plans is a table name. False Positive Alarm
										$plan_names      = '<strong>' . esc_html__( 'Associated Plans:', 'armember-membership' ) . ' </strong><br/>';
										if ( $plan_names_db != '' ) {
											foreach ( $plan_names_db as $db_plan_name ) {
												$arm_subscription_plan_name = !empty($db_plan_name->arm_subscription_plan_name) ? stripslashes_deep($db_plan_name->arm_subscription_plan_name): '';
												if(!empty($arm_subscription_plan_name)){
													$plan_names .= $arm_subscription_plan_name . ', ';
												}	
											}
										} else {
											$plan_names .= ' ' . esc_html__( 'No Plan selected', 'armember-membership' );
										}
										$plan_names = rtrim( $plan_names, ', ' );
									}
								}

								?>
								<div class="arm_template_content_wrapper arm_row_temp_<?php echo $t_id; //phpcs:ignore ?> <?php echo esc_attr($t_container_class); ?> armGridActionTD">
									<div class="arm_template_content_main_box">
										<a href="javascript:void(0)" class="arm_template_preview" <?php echo $t_link_attr; //phpcs:ignore ?>><img alt="<?php echo esc_attr($t_title); ?>" src="<?php echo esc_url($t_img_url); ?>"></a>
										<div class="arm_template_content_option_links">
											<a href="javascript:void(0)" class="arm_template_preview armhelptip" title="<?php esc_attr_e( 'Click to preview', 'armember-membership' ); ?>" <?php echo $t_link_attr; //phpcs:ignore ?>><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/dir_preview_icon.svg" alt="" onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_preview_icon_hover.svg';"  onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_preview_icon.svg';"/></a>
											<a class="arm_template_edit_link armhelptip arm_member_profile_edit_link" title="<?php esc_attr_e( 'Edit Template Options', 'armember-membership' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->profiles_directories . '&action=edit_profile&id=' . $t_id ) ); //phpcs:ignore ?>" <?php echo $t_link_attr; //phpcs:ignore ?>><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/dir_edit_icon.svg" alt="" onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_edit_icon_hover.svg';"  onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_edit_icon.svg';"/></a>
											<?php $arm_edi_del_section_profile = '';
											echo apply_filters('arm_profile_template_btn_content',$arm_edi_del_section_profile,$t_id,$ptemp,$t_link_attr,'profile'); //phpcs:ignore?>
										</div>
									</div>
									<?php 
									$arm_confirm_box_profile_delete='';
									echo apply_filters('arm_confirm_box_profile_delete',$arm_confirm_box_profile_delete,$t_id); //phpcs:ignore?>
									<div class="armclear"></div>
									<div class="arm_profile_template_associalated_plan"><?php echo $plan_names; //phpcs:ignore ?></div>
								</div>
								<?php
							}
						}
						$arm_add_new_profile_view_content_box = '';
						echo apply_filters('arm_add_new_profile_view_content_box',$arm_add_new_profile_view_content_box,'profile'); //phpcs:ignore
						?>
						
					</div>
					<div class="armclear"></div>
					<?php
						/* $arm_profile_content_notice_message = '';
						echo apply_filters('arm_profile_content_notice_message',$arm_profile_content_notice_message); //phpcs:ignore */
					?>
					<div class="arm_member_profile_url arm_margin_bottom_48">
						<div class="page_sub_title"><?php esc_html_e( 'Member Profile URL', 'armember-membership' ); ?></div>
						<?php
						$permalink_base = ( isset( $arm_global_settings->global_settings['profile_permalink_base'] ) ) ? $arm_global_settings->global_settings['profile_permalink_base'] : 'user_login';
						$profileUrl = '';
						if ( get_option( 'permalink_structure' ) ) {
							if(!empty($arm_global_settings->profile_url))
							{
								$profileUrl = trailingslashit( untrailingslashit( $arm_global_settings->profile_url ) );
							}
							if ( $permalink_base == 'user_login' ) {
								$profileUrl = $profileUrl . '<b>username</b>/';
							} else {
								$profileUrl = $profileUrl . '<b>user_id</b>/';
							}
						} else {
							if(!empty($arm_global_settings->profile_url))
							{
								$profileUrl = $arm_global_settings->add_query_arg( 'arm_user', 'arm_base_slug', $arm_global_settings->profile_url );
							}
							if ( $permalink_base == 'user_login' ) {
								$profileUrl = str_replace( 'arm_base_slug', '<b>username</b>', $profileUrl );
							} else {
								$profileUrl = str_replace( 'arm_base_slug', '<b>user_id</b>', $profileUrl );
							}
						}
						?>
						<span class="arm_info_text">
							<span class="arm-black-350 arm_font_size_16 arm_font_weight_400">
						<?php
							
							echo esc_html__( 'Current user profile URL pattern', 'armember-membership' ) . ': </span><span class="arm-black-350 arm_font_size_16 arm_font_weight_600">' . $profileUrl; //phpcs:ignore
							echo '</span>&nbsp;&nbsp;<a class="arm_pattern_change_link" href="' . esc_url( admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '#profilePermalinkBase' ) ) . '">' . esc_html__( 'Change Pattern', 'armember-membership' ) . '</a>'; //phpcs:ignore
						?>
						</span>
					</div>
				</div>
				<div class="armclear"></div>
				<div class="arm_page_spacing_div"></div>
				<div class="arm_belt_box arm_margin_bottom_48">
					<div class="arm_belt_block">
						<div class="page_sub_title"><?php esc_html_e( 'Members Directory Templates', 'armember-membership' ); ?></div>
					</div>
				</div>
				<div id="arm_directory_templates_container" class="page_sub_content arm_directory_templates_container">
					<div id="arm_directory_templates" class="arm_directory_templates arm_pdt_content">
						<?php
						if ( ! empty( $member_templates['directory'] ) ) {
							foreach ( $member_templates['directory'] as $dtemp ) {
								$t_id              = $dtemp['arm_id'];
								$t_title           = !empty($dtemp['arm_title']) ? stripslashes_deep($dtemp['arm_title']) : '';
								$t_type            = $dtemp['arm_type'];
								$t_options         = maybe_unserialize( $dtemp['arm_options'] );
								$t_link_attr       = 'data-id="' . esc_attr($t_id) . '" data-type="' . esc_attr($t_type) . '"';
								$t_container_class = '';
								$t_img_url         = MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $dtemp['arm_slug'] . '.png';
								?>
								<div class="arm_template_content_wrapper arm_row_temp_<?php echo esc_attr($t_id); ?> <?php echo esc_attr($t_container_class); ?> armGridActionTD">
									<div class="arm_template_content_main_box">
										<a href="javascript:void(0)" class="arm_template_preview" <?php echo $t_link_attr; //phpcs:ignore ?>><img alt="<?php echo esc_attr($t_title); ?>" src="<?php echo esc_url($t_img_url); ?>"></a>
										<div class="arm_template_content_option_links">
											<a href="javascript:void(0)" class="arm_template_preview armhelptip" title="<?php esc_attr_e( 'Click to preview', 'armember-membership' ); ?>" <?php echo $t_link_attr; //phpcs:ignore ?>><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/dir_preview_icon.svg" alt="" onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_preview_icon_hover.svg';"  onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_preview_icon.svg';"/></a>
											<a href="javascript:void(0)" class="arm_template_edit_link armhelptip" title="<?php esc_html_e( 'Edit Template Options', 'armember-membership' ); ?>" <?php echo $t_link_attr; //phpcs:ignore ?>><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/dir_edit_icon.svg" alt="" onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_edit_icon_hover.svg';"  onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/dir_edit_icon.svg';"/></a>
											<?php $arm_profile_template_btn_content = '';
											echo apply_filters('arm_profile_template_btn_content',$arm_profile_template_btn_content,$t_id,$dtemp,$t_link_attr,'templates'); //phpcs:ignore?>
										</div>
									</div>
									<?php 
									$arm_confirm_box_profile_delete='';
									echo apply_filters('arm_confirm_box_profile_delete',$arm_confirm_box_profile_delete,$t_id); //phpcs:ignore
									?>
									<!--<span class="arm_template_title"><?php echo esc_attr($t_title); ?></span>-->
									<div class="arm_short_code_detail">
										<span class="arm_shortcode_title"><?php esc_html_e( 'Short Code', 'armember-membership' ); ?>&nbsp;&nbsp;</span>
										<?php $shortCode = '[arm_template type="' . esc_attr($t_type) . '" id="' . esc_attr($t_id) . '"]'; ?>
										<div class="arm_shortcode_text arm_form_shortcode_box">
											<span class="armCopyText"><?php echo esc_attr( $shortCode ); ?></span>
											<span class="arm_click_to_copy_text" data-code="<?php echo esc_attr( $shortCode ); ?>"><?php esc_html_e( 'Click to copy', 'armember-membership' ); ?></span>
											<span class="arm_copied_text"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/copied_ok.png" alt="ok"/><?php esc_html_e( 'Code Copied', 'armember-membership' ); ?></span>
										</div>
									</div>
									<div class="armclear"></div>
								</div>
								<?php
							}
						}
						$arm_add_new_profile_view_content_box = '';
						echo apply_filters('arm_add_new_profile_view_content_box',$arm_add_new_profile_view_content_box,'directory'); //phpcs:ignore
						?>
					   
					</div>
				</div>
				<div class="armclear"></div>
				<?php 
				$arm_profile_directories_list_section = '';
				echo apply_filters('arm_profile_directories_list_section',$arm_profile_directories_list_section,'cards',$member_templates, $defaultTemplates,$tempColorSchemes,$tempColorSchemes1,$subs_data); //phpcs:ignore?>
			</div>
						
						<?php

						$temp_id  = 1;
						$tempType = 'profile';
						if ( ! empty( $temp_id ) && $temp_id != 0 ) {
							$tempDetails = $arm_members_directory->arm_get_template_by_id( $temp_id );

							if ( ! empty( $tempDetails ) ) {



								$tempType                       = isset( $tempDetails['arm_type'] ) ? $tempDetails['arm_type'] : 'directory';
								$tempOptions                    = $tempDetails['arm_options'];
								$popup                          = '<div class="wrap arm_page arm_ptemp_add_popup_wrapper popup_wrapper" >';
								$is_rtl_form                    = is_rtl() ? 'arm_add_form_rtl' : '';
								$popup                         .= '<form action="#" method="post" class="arm_profile_template_add_form arm_admin_form ' . esc_attr($is_rtl_form) . '" onsubmit="return false;" id="arm_profile_template_add_form" data-temp_id="' . esc_attr($temp_id) . '">';
								$popup                         .= '<table cellspacing="0" id="arm_profile_template_add_form_table">';
								$popup                         .= '<tr class="popup_wrapper_inner">';
								$popup                         .= '<td class="popup_header">';
								$popup                         .= '<span class="popup_close_btn arm_popup_close_btn arm_add_profile_template_popup_close_btn"></span>';
								$popup                         .= '<span>' . esc_html__( 'Select Profile Template', 'armember-membership' ) . '</span>';
								$popup                         .= '</td>';
								$popup                         .= '<td class="popup_content_text">';
								$popup                         .= $arm_members_directory->arm_profile_template_options( $tempType );
								$popup                         .= '</td>';
								$popup                         .= '<td class="arm_submit_btn_container">';
								$popup                         .= '<input type="hidden" name="id" id="arm_pdtemp_edit_id" value="' . esc_attr($temp_id) . '">';
									
								$popup                         .= '<input type="hidden" id="arm_admin_url" value="' . esc_url( admin_url( 'admin.php?page=' . $arm_slugs->profiles_directories . '&action=add_profile' ) ) . '" />';
								$popup                         .= '<button class="arm_cancel_btn arm_profile_add_close_btn" type="button">' . esc_html__( 'Cancel', 'armember-membership' ) . '</button>';
								$popup                         .= '<button class="arm_save_btn arm_profile_next_submit" data-id="' . esc_attr($temp_id) . '" type="submit" name="arm_add_profile" id="arm_profile_next_submit">' . esc_html__( 'Next', 'armember-membership' ) . '</button>';
								$popup                         .= '<div class="popup_content_btn_wrapper arm_temp_custom_class_btn hidden_section">';
								$backToListingIcon              = MEMBERSHIPLITE_IMAGES_URL . '/back_to_listing_arrow.png';
								$popup                         .= '<a href="javascript:void(0)" class="arm_section_custom_css_detail_hide_template armemailaddbtn"><img align="absmiddle" src="' . esc_attr($backToListingIcon) . '"/>' . esc_html__( 'Cancel', 'armember-membership' ) . '</a>'; //phpcs:ignore
								$popup                         .= '</div>';
								$popup                         .= '</td>';
								$popup                         .= '</tr>';
								$popup                         .= '</table>';
								$popup                         .= '</form>';
								echo $popup                    .= '</div>';  //phpcs:ignore




							}
						}
						?>
		</div>
		<div class="armclear"></div>
		<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
		<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		<div id="arm_profile_directory_template_preview" class="arm_profile_directory_template_preview"></div>
		<div class="arm_pdtemp_edit_popup_wrapper popup_wrapper">
			<div class="popup_header arm_member_popup_header_wrapper">
				<div class="page_title">
					<span><?php esc_html_e( 'Edit Template Options', 'armember-membership' ) ?></span>
					<span class="popup_close_btn arm_popup_close_btn arm_pdtemp_directory_edit_close_btn"></span>
				</div>
			</div>
			<div class="popup_wrapper_inner"></div>
		</div>	
		<div class="arm_edit_membership_card_templates popup_wrapper">
			<div class="popup_header arm_member_popup_header_wrapper">
				<div class="page_title">
					<span><?php esc_html_e('Edit Card Options', 'armember-membership')?></span>
					<span class="popup_close_btn arm_popup_close_btn arm_pdtemp_edit_close_btn"></span>
				</div>
			</div>
			<div class="popup_wrapper_inner"></div>
		</div>
		<div id="arm_member_card_template_preview" class="arm_member_card_template_preview"></div>
	</div>
	<div class="arm_section_custom_css_detail_container"></div>
		
		<?php

		/* **********./Begin Bulk Delete Member Popup/.********** */
		$arm_template_change_message_popup_content  = '<span class="arm_confirm_text">' . esc_html__( 'Plese confirm that while changing Template, all colors will be reset to default.', 'armember-membership' );
		$arm_template_change_message_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$arm_template_change_message_popup_arg      = array(
			'id'             => 'arm_template_change_message',
			'class'          => 'arm_template_change_message arm_delete_bulk_action_message',
			'title'          => esc_html__( 'Change Directory Template', 'armember-membership' ),
			'content'        => $arm_template_change_message_popup_content,
			'button_id'      => 'arm_template_change_message_ok_btn',
			'button_onclick' => "arm_template_change_message_action();",
			'ok_btn_class' => 'arm_bulk_change_member_ok_btn'
		);
		echo $arm_global_settings->arm_get_bpopup_html( $arm_template_change_message_popup_arg ); //phpcs:ignore
		?>
</div>
<style type="text/css" title="currentStyle">
	#adminmenuback{z-index: 101;}
	#adminmenuwrap{z-index: 9990;}
</style>
<script type="text/javascript">
function armTempColorSchemes() {
	var tempColorSchemes = <?php echo wp_json_encode( $tempColorSchemes ); ?>;
	return tempColorSchemes;
}
function armTempColorSchemes1() {
	var tempColorSchemes = <?php echo wp_json_encode( $tempColorSchemes1 ); ?>;
	return tempColorSchemes;
}
jQuery(window).on("load", function(){
	var popupH = jQuery('.arm_template_preview_popup').height();
	jQuery('.arm_template_preview_popup .popup_content_text').css('height', (popupH - 60)+'px');
	var contentHeight = jQuery('.arm_visible').outerHeight();
	jQuery('.arm_profiles_directories_templates_container').css('height', contentHeight);
});
jQuery(window).resize(function(){
	var popupH = jQuery('.arm_template_preview_popup').height();
	jQuery('.arm_template_preview_popup .popup_content_text').css('height', (popupH - 60)+'px');
	var contentHeight = jQuery('.arm_visible').outerHeight();
	jQuery('.arm_profiles_directories_templates_container').css('height', contentHeight);
});
var DEFAULT_COVER = "<?php echo MEMBERSHIPLITE_IMAGES_URL . '/profile_default_cover.png';?>";
var ARM_REMOVE_IMAGE_ICON = '<?php echo MEMBERSHIPLITE_IMAGES_URL?>/delete.svg';
var ARM_REMOVE_IMAGE_ICON_HOVER = '<?php echo MEMBERSHIPLITE_IMAGES_URL?>/delete_hover.svg';
</script>
<?php
echo $ARMemberLite->arm_get_need_help_html_content('members-profile-directories'); //phpcs:ignore
?>