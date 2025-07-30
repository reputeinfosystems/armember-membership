<?php
global $wpdb, $ARMemberLite, $arm_subscription_plans, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_payment_gateways;
$user_roles  = get_editable_roles();
$user_roles1 = $arm_global_settings->arm_get_all_roles();

$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';//phpcs:ignore
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.ColVis_Button{display:none;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(document).ready( function () {
	arm_load_plan_list_grid();

});

function arm_load_plan_list_filtered_grid(data)
{
	var tbl = jQuery('#armember_datatable').dataTable(); 
	tbl.fnDeleteRow(data);
	jQuery('#armember_datatable').dataTable().fnDestroy();
	arm_load_plan_list_grid();
}

function arm_load_plan_list_grid(){
		var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Showing_empty = '<?php echo addslashes( esc_html__( 'Showing 0 to 0 of 0 enteries', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_to = '<?php echo addslashes( esc_html__( 'to', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_PLANS = ' <?php echo addslashes( esc_html__( 'entries', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
		var __ARM_NO_FOUND = '<?php echo addslashes( esc_html__( 'No any subscription plan found.', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching records found.', 'armember-membership' ) ); //phpcs:ignore ?>';
	
	var table = jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"fr>t<"footer"ipl>',
		"sPaginationType": "four_button",
				"oLanguage": {
					"sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_PLANS,
					"sInfoEmpty": __ARM_Showing_empty,
				
					"sLengthMenu": __ARM_Show + "_MENU_" + __ARM_PLANS,
					"sEmptyTable": __ARM_NO_FOUND,
					"sZeroRecords": __ARM_NO_MATCHING,
				  },
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth" : false,
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [] }
		],
		"language":{
			"searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
			"search":"",
		},
		"fnPreDrawCallback": function () {
			jQuery('.arm_loading_grid').show();
		},
		"fnDrawCallback":function(){
			setTimeout(function(){
				jQuery('.arm_loading_grid').hide();
				arm_show_data();
			}, 1000);
			if (jQuery.isFunction(jQuery().tipso)) {
				jQuery('.armhelptip').each(function () {
					jQuery(this).tipso({
						position: 'top',
						size: 'small',
						background: '#939393',
						color: '#ffffff',
						width: false,
						maxWidth: 400,
						useTitle: true
					});
				});
			}
		}
	});
	var filter_box = jQuery('#arm_filter_wrapper').html();
	jQuery('div#armember_datatable_filter').parent().append(filter_box);
	jQuery('#arm_filter_wrapper').remove();
	}
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>
<div class="wrap arm_page arm_subscription_plans_main_wrapper">
	<?php
	if($ARMemberLite->is_arm_pro_active)
	{
		$arm_license_notice = '';
		echo apply_filters('arm_admin_license_notice_html',$arm_license_notice); //phpcs:ignore
	}
	?>
	<div class="content_wrapper arm_subscription_plans_content" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Manage Membership plans', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->manage_plans . '&action=new' ) ); //phpcs:ignore ?>"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/add_new_icon.png"><span><?php esc_html_e( 'Add New Plan', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_subscription_plans_list">
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list">
				<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->manage_plans); //phpcs:ignore ?>" />
				<input type="hidden" name="armaction" value="list" />
				<div id="armmainformnewlist">
					<div class="arm_loading_grid" style="display: none;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/loader.gif" alt="Loading.."></div>
					<table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display" id="armember_datatable" style="visibility: hidden;">
						<thead>
							<tr>
								<th class="arm_min_width_50"><?php esc_html_e( 'Plan ID', 'armember-membership' ); ?></th>
								<th class="arm_min_width_200"><?php esc_html_e( 'Plan Name', 'armember-membership' ); ?></th>
								<th style=""><?php esc_html_e( 'Plan Type', 'armember-membership' ); ?></th>
								<th class="arm_width_100"><?php esc_html_e( 'Members', 'armember-membership' ); ?></th>
								<th class="arm_width_120"><?php esc_html_e( 'Wp Role', 'armember-membership' ); ?></th>							
								<th class="armGridActionTD"></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$form_result = $arm_subscription_plans->arm_get_all_subscription_plans();
						if ( ! empty( $form_result ) ) {
							$arm_is_multisite    = is_multisite();
							$arm_current_blog_id = ! empty( $arm_is_multisite ) ? get_current_blog_id() : 0;

							$arm_user_query = $wpdb->get_results( $wpdb->prepare( "SELECT um.user_id, um.meta_value FROM $wpdb->users  u LEFT JOIN $wpdb->usermeta um ON um.user_id = u.ID WHERE um.meta_key = %s", 'arm_user_plan_ids' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$arm_user_array = array();
							if ( ! empty( $arm_user_query ) ) {
								foreach ( $arm_user_query as $arm_user ) {
									$user_meta  = get_userdata( $arm_user->user_id );
									$user_roles = ! empty( $user_meta->roles ) ? $user_meta->roles : array();
									if ( ! in_array( 'administrator', $user_roles ) ) {

										if ( $arm_is_multisite ) {
											if ( is_user_member_of_blog( $arm_user->user_id, $arm_current_blog_id ) ) {
												$arm_user_array[ $arm_user->user_id ] = maybe_unserialize( $arm_user->meta_value );
											} else {
												continue;
											}
										} else {
											$arm_user_array[ $arm_user->user_id ] = maybe_unserialize( $arm_user->meta_value );
										}
									}
								}
							}

							foreach ( $form_result as $planData ) {
								if($ARMemberLite->is_arm_pro_active){
									$planObj = new ARM_Plan();
								}
								else
								{
									$planObj = new ARM_Plan_Lite();
								}
								$planObj->init( (object) $planData );
								$planID      = $planData['arm_subscription_plan_id'];
								$total_users = 0;
								if ( ! empty( $arm_user_array ) ) {
									foreach ( $arm_user_array as $arm_user_id => $arm_user_plans ) {
										if ( ! empty( $arm_user_plans ) && in_array( $planID, $arm_user_plans ) ) {
											$total_users++;
										}
									}
								}



								$edit_link = admin_url( 'admin.php?page=' . $arm_slugs->manage_plans . '&action=edit_plan&id=' . $planID );
								?>
								<tr class="row_<?php echo intval($planID); ?>">
									<td class=""><?php echo '<a href="' . esc_url($edit_link) . '">' . $planID . '</a> '; //phpcs:ignore ?></td>
									<td class=""><?php echo '<a href="' . esc_url($edit_link) . '">' . esc_html( stripslashes( $planObj->name ) ) . '</a> '; //phpcs:ignore ?></td>
									<td><?php // echo $planObj->plan_text(true); ?>
										<?php
										if( $planObj->is_recurring() && isset($planObj->options['payment_cycles']) && count($planObj->options['payment_cycles']) > 1 && $ARMemberLite->is_arm_pro_active) {
											echo '<span class="arm_item_status_text active">' . esc_html__('Paid', 'armember-membership') . '</span><br/>
											<a href="javascript:void(0);" onclick="arm_plan_cycle('. esc_attr($planID) .')">' . esc_html__('Multiple Cycle', 'armember-membership') . '</a>';
										} else {
											echo $planObj->plan_text(true); //phpcs:ignore
										}
										?>
									</td>
									<td class="center">
									<?php
									$planMembers = $total_users;
									if ( $planMembers > 0 ) {
										$membersLink = admin_url( 'admin.php?page=' . $arm_slugs->manage_members . '&plan_id=' . $planID );
										echo "<a href='". esc_url( $membersLink )."'>" . $planMembers . '</a>'; //phpcs:ignore
									} else {
										echo $planMembers; //phpcs:ignore
									}
									?>
																			
									</td>
									<td>
									<?php
									$planRole = $planObj->plan_role;
									if ( ! empty( $user_roles1[ $planRole ] ) ) {
										echo esc_html($user_roles1[ $planRole ]);
									} else {
										echo '-';
									}
									?>
									</td>						
									<td class="armGridActionTD">
									<?php
										$gridAction = "<div class='arm_grid_action_btn_container'>";
									if ( current_user_can( 'arm_manage_plans' ) ) {
											$gridAction .= "<a href='" . $edit_link . "'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit.png' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit_hover.png';\" class='armhelptip' title='" . esc_attr__( 'Edit Plan', 'armember-membership' ) . "' onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_edit.png';\" /></a>"; //phpcs:ignore
											$gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$planID});'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_delete.png' class='armhelptip' title='" . esc_attr__( 'Delete', 'armember-membership' ) . "' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_delete.png';\" /></a>"; //phpcs:ignore
										if ( empty( $planMembers ) || $planMembers == 0 ) {
											$gridAction .= $arm_global_settings->arm_get_confirm_box( $planID, esc_html__( 'Are you sure you want to delete this plan?', 'armember-membership' ), 'arm_plan_delete_btn' );
										} else {
											$gridAction .= $arm_global_settings->arm_get_confirm_box( $planID, esc_html__( 'This plan has one or more subscribers. So this plan can not be deleted.', 'armember-membership' ), 'arm_plan_delete_btn_not arm_hide','','',esc_html__('Close','armember-membership') );
										}
									}
										$gridAction .= '</div>';
										echo '<div class="arm_grid_action_wrapper">' . $gridAction . '</div>'; //phpcs:ignore
									?>
									</td>
								</tr>
								<?php
							}//End Foreach
						}
						?>
						</tbody>
					</table>
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e( 'Show / Hide columns', 'armember-membership' ); ?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e( 'plans', 'armember-membership' ); ?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e( 'Show', 'armember-membership' ); ?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e( 'Showing', 'armember-membership' ); ?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e( 'to', 'armember-membership' ); ?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e( 'of', 'armember-membership' ); ?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e( 'No matching plans found', 'armember-membership' ); ?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e( 'No any subscription plan found.', 'armember-membership' ); ?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e( 'filtered from', 'armember-membership' ); ?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e( 'total', 'armember-membership' ); ?>"/>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
				<div class="footer_grid"></div>
			</form>
		</div>
		<?php
		/* **********./Begin Bulk Delete Plan Popup/.********** */
		$bulk_delete_plan_popup_content  = '<span class="arm_confirm_text">' . esc_html__( 'Are you sure you want to delete this plan(s)?', 'armember-membership' ) . '</span>';
		$bulk_delete_plan_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_plan_popup_arg      = array(
			'id'             => 'delete_bulk_plan_message',
			'class'          => 'delete_bulk_plan_message',
			'title'          => esc_html__( 'Delete Plan(s)', 'armember-membership' ),
			'content'        => $bulk_delete_plan_popup_content,
			'button_id'      => 'arm_bulk_delete_plan_ok_btn',
			'button_onclick' => "arm_delete_bulk_plan('true');",
		);
		echo $arm_global_settings->arm_get_bpopup_html( $bulk_delete_plan_popup_arg ); //phpcs:ignore
		/* **********./End Bulk Delete Plan Popup/.********** */
		?>
		<div class="armclear"></div>
	</div>
</div>


<script type="text/javascript" charset="utf-8">
// <![CDATA[
var ARM_IMAGE_URL = "<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>";
// ]]>
</script>

<div class="arm_plan_cycle_detail_popup popup_wrapper arm_import_user_list_detail_popup_wrapper <?php echo ( is_rtl() ) ? 'arm_page_rtl' : ''; ?>" >    
	<div>
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn arm_plan_cycle_detail_close_btn"></span>
			<input type="hidden" id="arm_edit_plan_user_id" />
			<span class="add_rule_content"><?php esc_html_e( 'Plans Cycles', 'armember-membership' ); ?> <span class="arm_plan_name"></span></span>
		</div>
		<div class="popup_content_text arm_plan_cycle_text arm_text_align_center" >
			
			<div class="arm_width_100_pct" style="margin: 45px auto;">	<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>"></div>
		</div>
		<div class="armclear"></div>
	</div>

</div>
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('membership-plans-list'); //phpcs:ignore
?>