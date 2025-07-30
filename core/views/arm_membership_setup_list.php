<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings,  $arm_subscription_plans, $arm_payment_gateways,$arm_pay_per_post_feature;
$date_format             = $arm_global_settings->arm_get_wp_date_format();
$actions['delete_setup'] = esc_html__( 'Delete', 'armember-membership' );
$addNewSetupLink         = admin_url( 'admin.php?page=' . $arm_slugs->membership_setup . '&action=new_setup' );

if ( $total_setups < 1 ) {
	wp_redirect( $addNewSetupLink );
	exit;
}
?>
<style type="text/css" title="currentStyle">
.paginate_page a{display:none;}
#poststuff #post-body {margin-top: 32px;}
.delete_box{float: <?php echo ( is_rtl() ) ? 'right' : 'left'; ?>;}
.ColVis_Button{display:none;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
function ChangeID(id){
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>
<div class="wrap arm_page arm_membership_setup_main_wrapper">
	<?php 
	if($ARMemberLite->is_arm_pro_active)
	{
		$arm_license_notice = '';
		echo apply_filters('arm_admin_license_notice_html',$arm_license_notice); //phpcs:ignore
	}
	?>
	<div class="content_wrapper arm_membership_setup_container" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Configure Plan + Signup Page', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_new_form_btn" href="<?php echo esc_url($addNewSetupLink); ?>"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/add_new_icon.png"><span><?php esc_html_e( 'Add New Setup', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_manage_forms_content arm_membership_setups_list armPageContainer">
			<div class="arm_form_content_box">
				<div class="arm_form_list_container">
					<table class="form-table">
						<tbody>
							<tr class="arm_form_list_header">
								<td></td>
								<td class="arm_form_title_col setup_name"><?php esc_html_e( 'Setup Name', 'armember-membership' ); ?></td>
								<?php if($ARMemberLite->is_arm_pro_active)
								{
									if( ($arm_pay_per_post_feature->isPayPerPostFeature || is_plugin_active('armembergift/armembergift.php'))){?> 
									<td class="arm_form_title_col setup_type"><?php esc_html_e('Setup Type','armember-membership');?></td>
									<?php }
								}?>
								<td><?php esc_html_e( 'Plans', 'armember-membership' ); ?></td>
								<td><?php esc_html_e( 'Gateways', 'armember-membership' ); ?></td>
								<td><?php esc_html_e( 'Member Form', 'armember-membership' ); ?></td>
								<td><?php esc_html_e( 'Shortcode', 'armember-membership' ); ?></td>
								<td class="arm_form_action_col"><?php esc_html_e( 'Action', 'armember-membership' ); ?></td>
								<td></td>
							</tr>
						<?php
						$setup_result = $wpdb->get_results('SELECT `arm_setup_id`, `arm_setup_name`, `arm_setup_modules`, `arm_created_date`,`arm_setup_type` FROM `' . $ARMemberLite->tbl_arm_membership_setup . '` ORDER BY `arm_setup_id` DESC');//phpcs:ignore --Reason: $tbl_arm_membership_setup is a table name. False Positive Alarm.No need to prepare query without Where clause.
						?>
						<?php if ( ! empty( $setup_result ) ) : ?>
							<?php foreach ( $setup_result as $val ) : ?>
								<?php $setupID = $val->arm_setup_id; ?>
								<tr class="row_<?php echo intval($setupID); ?>">
									<td></td>
									<td class="arm_form_title_col setup_name">
									<?php
									$edit_link = admin_url( 'admin.php?page=' . $arm_slugs->membership_setup . '&action=edit_setup&id=' . $setupID );
									echo '<a href="' . esc_url( $edit_link ). '">' . stripslashes( $val->arm_setup_name ) . '</a> '; //phpcs:ignore
									?></td>
									<?php if($ARMemberLite->is_arm_pro_active)
									{
										
										if( ($arm_pay_per_post_feature->isPayPerPostFeature || is_plugin_active('armembergift/armembergift.php'))){?> 
										<td class="arm_form_shortcode_col">
										<?php 
										
											$arm_setup_type = !empty($val->arm_setup_type) ? $val->arm_setup_type : 0;

											if($arm_setup_type==0){
												echo esc_html_e('Membership Plan','armember-membership');
													
											}elseif($arm_setup_type==1){
												echo esc_html_e('Paid Post','armember-membership');
											} elseif($arm_setup_type==2) {
												echo esc_html_e('Gift','armember-membership');
											}
										?>
										</td>
										<?php } 
									} ?>
									<td class="arm_form_shortcode_col">
									<?php
									$val->setup_modules = maybe_unserialize( $val->arm_setup_modules );
									$module_plans       = ( isset( $val->setup_modules['modules']['plans'] ) ) ? $val->setup_modules['modules']['plans'] : array();
									$plan_title         = $arm_subscription_plans->arm_get_comma_plan_names_by_ids( $module_plans );
									echo ( ! empty( $plan_title ) ) ? stripslashes_deep( $plan_title ) : '--'; //phpcs:ignore
									?>
									</td>
									<td class="arm_form_shortcode_col">
									<?php
									$module_gateways = ( isset( $val->setup_modules['modules']['gateways'] ) ) ? $val->setup_modules['modules']['gateways'] : array();
									$gateway_title   = '--';

									if ( ! empty( $module_gateways ) ) {
										$gateway_title = '';
										foreach ( $module_gateways as $key => $gateway ) {
											$gateway_title .= $arm_payment_gateways->arm_gateway_name_by_key( $gateway ) . ', ';
										}
									}
									echo rtrim( $gateway_title, ', ' ); //phpcs:ignore
									?>
									</td>
									<td class="arm_form_shortcode_col">
									<?php
									$module_plans = ( isset( $val->setup_modules['modules']['forms'] ) ) ? $val->setup_modules['modules']['forms'] : 101;
									if($ARMemberLite->is_arm_pro_active)
									{
										$module_form = new ARM_Form( 'id', $module_plans );
									}
									else
									{
										$module_form = new ARM_Form_Lite( 'id', $module_plans );
									}
									if ( $module_form->exists() ) {
										echo $module_form->form_detail['arm_form_label']; //phpcs:ignore
									} else {
										echo '--';
									}
									?>
									</td>
									<td class="arm_form_shortcode_col">
										<!--<span><?php esc_html_e( 'Short Code', 'armember-membership' ); ?>&nbsp;&nbsp;</span>-->
										<?php $shortCode = '[arm_setup id="' . $setupID . '"]'; ?>
										<div class="arm_shortcode_text arm_form_shortcode_box">
											<span class="armCopyText"><?php echo esc_html($shortCode); ?></span>
											<span class="arm_click_to_copy_text" data-code="<?php echo esc_attr( $shortCode ); ?>"><?php esc_html_e( 'Click to copy', 'armember-membership' ); ?></span>
											<span class="arm_copied_text"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/copied_ok.png" alt="ok"/><?php esc_html_e( 'Code Copied', 'armember-membership' ); ?></span>
										</div>
									</td>
									<td class="arm_form_action_col">
										<div class="arm_form_action_btns">
											<a href="<?php echo $edit_link; //phpcs:ignore ?>" class="arm_get_form_link" data-form_id="<?php echo intval($setupID); ?>">
												<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL);?>/edit_icon.png" onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/edit_icon_hover.png';" class="armhelptip" title="<?php esc_attr_e( 'Edit Form', 'armember-membership' ); ?>" onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/edit_icon.png';" /> <?php //phpcs:ignore ?>
											</a>
											<a href="javascript:void(0)" onclick="showConfirmBoxCallback(<?php echo intval($setupID); ?>);" data-form_id="<?php echo intval($setupID); ?>">
												<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/delete.png" class="armhelptip" title="<?php esc_attr_e( 'Delete Setup', 'armember-membership' ); ?>" onmouseover="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/delete_hover.png';" onmouseout="this.src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); ?>/delete.png';" style='cursor:pointer'/> <?php //phpcs:ignore ?>
											</a>
											<?php
											echo $arm_global_settings->arm_get_confirm_box( $setupID, esc_html__( 'Are you sure you want to delete this setup?', 'armember-membership' ), 'arm_setup_delete_btn' ); //phpcs:ignore
											?>
										</div>
										<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
										<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
									</td>
									<td></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>

<?php
    echo $ARMemberLite->arm_get_need_help_html_content('configure-membership-setup--list'); //phpcs:ignore
?>