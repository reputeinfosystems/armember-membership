<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways;
$date_format    = $arm_global_settings->arm_get_wp_date_format();
$user_roles     = get_editable_roles();
$nowDate        = current_time( 'mysql' );
$all_plans      = $arm_subscription_plans->arm_get_all_subscription_plans();
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
$filter_plan_id = ( ! empty( $_REQUEST['plan_id'] ) && $_REQUEST['plan_id'] != '0' ) ? intval($_REQUEST['plan_id']) : ''; //phpcs:ignore
$filter_form_id = ( ! empty( $posted_data['form_id'] ) && $posted_data['form_id'] != '0' ) ? intval($posted_data['form_id']) : '0';  //phpcs:ignore
$filter_search  = ( ! empty( $posted_data['search'] ) ) ? sanitize_text_field($posted_data['search']) : ''; //phpcs:ignore
$filter_member_status = (!empty($_REQUEST['member_status_id'])) ? intval($_REQUEST['member_status_id']) : '0'; //phpcs:ignore
/* * *************./Begin Set Member Grid Fields/.************** */
$grid_columns = array(
	'avatar'             => esc_html__( 'Avatar', 'armember-membership' ),
	'ID'                 => esc_html__( 'User ID', 'armember-membership' ),
	'user_login'         => esc_html__( 'Username', 'armember-membership' ),
	'user_email'         => esc_html__( 'Email Address', 'armember-membership' ),
	'arm_member_type'    => esc_html__( 'Membership Type', 'armember-membership' ),
	'arm_user_plan'      => esc_html__( 'Member Plan', 'armember-membership' ),
	'arm_primary_status' => esc_html__( 'Status', 'armember-membership' ),
	'roles'              => esc_html__( 'User Role', 'armember-membership' ),
	'first_name'         => esc_html__( 'First Name', 'armember-membership' ),
	'last_name'          => esc_html__( 'Last Name', 'armember-membership' ),
	'display_name'       => esc_html__( 'Display Name', 'armember-membership' ),
	'user_registered'    => esc_html__( 'Joined Date', 'armember-membership' ),
);

$grid_columns = apply_filters('arm_members_grid_columns',$grid_columns);

$default_columns = $grid_columns;
$user_meta_keys  = $arm_member_forms->arm_get_db_form_fields( true );
if ( ! empty( $user_meta_keys ) ) {
	$exclude_keys = array( 'user_pass', 'repeat_pass', 'rememberme', 'remember_me', 'section', 'html','arm_captcha');
	foreach ( $user_meta_keys as $umkey => $val ) {
		if ( ! in_array( $umkey, $exclude_keys ) ) {
            if(!empty($val['label'])){
	    	$grid_columns[ $umkey ] = stripslashes_deep($val['label']);
            }else if(empty($grid_columns[$umkey])){
                $grid_columns[$umkey] = stripslashes_deep($val['label']);
			}
		}
    }
}
/** *************./End Set Member Grid Fields/.************** */
$user_id                  = get_current_user_id();
$members_show_hide_column = maybe_unserialize( get_user_meta( $user_id, 'arm_members_hide_show_columns_' . $filter_form_id, true ) );
$column_hide              = '';
$totalCount               = count( $grid_columns ) + 2;
if($ARMemberLite->is_arm_pro_active)
{
	$totalCount = count( $grid_columns );
}
else{
	$totalCount               = count( $grid_columns ) + 2;
}
$totalDefaultCount        = count( $default_columns );
if ( ! empty( $members_show_hide_column ) ) {
	$i = 1;
	foreach ( $members_show_hide_column as $value ) {
		if ( $totalCount > $i ) {
			if ( $value != 1 ) {
				$column_hide = $column_hide . $i . ',';
			}
		}
		$i++;
	}
} else {
	$column_hide = '2,8,11,';
	$i           = 1;
	foreach ( $grid_columns as $value ) {
		if ( $totalDefaultCount < $i ) {
			$column_hide = $column_hide . $i . ',';
		}
		$i++;
	}
}
$plansLists = '<li data-label="' . esc_html__( 'Select Plan', 'armember-membership' ) . '" data-value="">' . esc_html__( 'Select Plan', 'armember-membership' ) . '</li>';
if ( ! empty( $all_plans ) ) {
	foreach ( $all_plans as $p ) {
		$p_id = $p['arm_subscription_plan_id'];
		if ( $p['arm_subscription_plan_status'] == '1' ) {
			$plansLists .= '<li data-label="' . stripslashes( esc_attr( $p['arm_subscription_plan_name'] ) ) . '" data-value="' . esc_attr($p_id) . '">' . stripslashes( esc_attr( $p['arm_subscription_plan_name'] ) ) . '</li>';
		}
	}
}

//$total_grid_column     = count( $grid_columns ) + 2;
if($ARMemberLite->is_arm_pro_active)
{
	$total_grid_column = count( $grid_columns );
}
else
{
	$total_grid_column     = count( $grid_columns ) + 2;
}
$grid_column_paid_with = true;
$arm_colvis            = $total_grid_column;
$grid_clmn          = '';
$sort_clmn          = '';
$arm_exclude_colvis = '0';
$arm_less_id = 12;
if($ARMemberLite->is_arm_pro_active)
{
	$arm_less_id = 13;
}
for ( $i = 0; $i < $total_grid_column; $i++ ) {
	if ( $i >= 2 && $i <= $arm_less_id ) {
		continue;
	}
	$grid_clmn .= $i . ',';
	$sort_clmn  = 2;
}
$arm_colvis         = apply_filters('arm_pro_get_grid_arm_colvis',$arm_colvis,$total_grid_column);
$arm_exclude_colvis = apply_filters('arm_pro_get_grid_exlcuded_colvis',$arm_exclude_colvis,$total_grid_column);
$grid_clmn          = apply_filters('arm_pro_get_grid_sortable_columns',$grid_clmn,$total_grid_column);
$sort_clmn          = apply_filters('arm_pro_get_default_grid_sort_columns',$sort_clmn);
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
	<?php if(!$ARMemberLite->is_arm_pro_active){?>
	jQuery(document).on('click', '.arm_show_user_more_plans_types, .arm_show_user_more_plans', function () {

		var id = jQuery(this).attr('data-id');
		var tr = jQuery(this).closest('tr');

		var class_name = jQuery(this).closest('tr').attr('class');
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var row = jQuery('#armember_datatable').DataTable().row(tr);
		
		  if (row.child.isShown()) {
			  // This row is already open - close it
			  row.child.hide();
			  tr.removeClass('shown');
			  tr.addClass('hide');
		  }
		  else {
			  // Open this row
			  row.child.show();
			  tr.removeClass('hide');
			  row.child(format(row.data(),_wpnonce), class_name +" "+"arm_child_user_row").show();
			  tr.addClass('shown');
		  }
	});
	<?php }?>
	function format(d,_wpnonce) {
		var response1 = '</div><div class="arm_child_row_div_'+d[3]+'"><img class="arm_load_user_plans" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/arm_loader.gif" alt="<?php esc_attr_e( 'Load More', 'armember-membership' ); ?>" style="  margin-left: 530px; padding: 10px;"></div>';
		setTimeout(function () { jQuery.ajax({
			type: "POST",
			url: __ARMAJAXURL,
			data: "action=arm_get_user_all_pan_details_for_grid&user_id=" + d[3] + "&_wpnonce=" + _wpnonce,
			dataType: 'html',
			success: function (response) {

			  jQuery('.arm_child_row_div_'+d[3]).html('<div class="arm_member_grid_arrow"></div>'+response);
			}
		});},200);
	   return response1;
	} 

	function show_grid_loader() {
			jQuery(".arm_hide_datatable").css('visibility', 'hidden');
			jQuery('.arm_loading_grid').show();
		
	}
	jQuery(document).ready(function () {
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_membership_grid(false);
		jQuery('#armmanagesearch_new').on('keyup', function (e) {
		
			e.stopPropagation();
			if (e.keyCode == 13) {
				arm_load_membership_grid_after_filtered();
				return false;
			}
		});
	});
	function arm_load_membership_grid_after_filtered() {
		jQuery('#arm_member_grid_filter_btn').attr('disabled', 'disabled');
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_membership_grid();
	}
	function arm_load_membership_grid(is_filtered) {
		var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Showing_empty = '<?php echo addslashes( esc_html__( 'Showing 0 to 0 of 0 members', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_to = '<?php echo addslashes( esc_html__( 'to', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_MEMBERS = ' <?php esc_html_e( 'members', 'armember-membership' ); //phpcs:ignore ?>';
		var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
		var __ARM_NO_FOUND = '<?php echo addslashes( esc_html__( 'No any member found.', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching records found.', 'armember-membership' ) ); //phpcs:ignore ?>';

		var search_term = jQuery("#armmanagesearch_new").val();
		var filtered_id = jQuery("#arm_subs_filter").val();
        var payment_mode_id = jQuery("#arm_mode_filter").val();
        var status_id = jQuery("#arm_status_filter").val();
        var meta_field_key= jQuery("#arm_meta_field_filter").val();
        var arm_filter_membership_type = jQuery("#arm_filter_membership_type");
        var db_search_term = (typeof search_term !== 'undefined' && search_term !== '') ? search_term : '';
		var db_filter_id = (typeof filtered_id !== 'undefined' && filtered_id !== '') ? filtered_id : '';
        var db_payment_mode = (typeof payment_mode_id !== 'undefined' && payment_mode_id !== '') ? payment_mode_id : '';
        var db_status_id = (typeof status_id !== 'undefined' && status_id !== '') ? status_id : '';
        var db_meta_field_key = (typeof meta_field_key !== 'undefined' && meta_field_key !== '' && meta_field_key != 0) ? meta_field_key : '';
		var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
        var arm_multiple_membership_list_show = (typeof arm_filter_membership_type !== 'undefined') ? arm_filter_membership_type.val() : 0;
        var ajax_url = '<?php echo esc_url(admin_url("admin-ajax.php"));?>';
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();


		<?php if(!$ARMemberLite->is_arm_pro_active){?>
				var nColVisCols = [];
				var arm_cols_vis = '<?php echo $arm_colvis; //phpcs:ignore ?>';
				for( var cv = 1; cv < arm_cols_vis ; cv++ ){
					nColVisCols.push( cv );
				}
		<?php }
		else
		{?>
			var nColVisCols = ":not(.noVis)";
		<?php }?>


		var oTables = jQuery('#armember_datatable').dataTable({
			"oLanguage": {
				"sProcessing": show_grid_loader(),
				"sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_MEMBERS,
				"sInfoEmpty": __ARM_Showing_empty,
				
				"sLengthMenu": __ARM_Show + "_MENU_" + __ARM_MEMBERS,
				"sEmptyTable": __ARM_NO_FOUND,
				"sZeroRecords": __ARM_NO_MATCHING,
			},
            "bDestroy": true,
			"language":{
				"searchPlaceholder":"<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
				"search":"",
			},
			"buttons":[{
				"extend":"colvis",
				"columns":nColVisCols,
				"className":"ColVis_Button TableTools_Button ui-button ui-state-default ColVis_MasterButton",
				"text":"<span class=\"armshowhideicon\" style=\"background-image: url(<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>/show_hide_icon.png);background-repeat: no-repeat;background-position: 0 center;padding: 0 0 0 30px;\"><?php esc_html_e('Show / Hide columns','armember-membership');?></span>",
			}],
			"bProcessing": false,
			"bServerSide": true,
			"sAjaxSource": ajax_url,
			"sServerMethod": "POST",
			"fnServerParams": function (aoData) {
				aoData.push({'name': 'action', 'value': 'arm_get_member_details'});
				aoData.push({'name': 'filter_plan_id', 'value': db_filter_id});
                aoData.push({'name': 'filter_mode_id', 'value': db_payment_mode});
                aoData.push({'name': 'filter_status_id', 'value': db_status_id});
                aoData.push({'name': 'filter_meta_field_key','value': db_meta_field_key});
				aoData.push({'name': 'sSearch', 'value': db_search_term});
                aoData.push({'name': 'arm_multiple_membership_list_show', 'value': arm_multiple_membership_list_show });
				aoData.push({'name': 'sColumns', 'value':null});
				aoData.push({'name': '_wpnonce', 'value': _wpnonce});
			},
			"bRetrieve": false,
			"sDom": '<"H"CBfr>t<"footer"ipl>',
			"sPaginationType": "four_button",
			"bJQueryUI": true,
			"bPaginate": true,
			"bAutoWidth": false,
			"sScrollX": "100%",
			"bScrollCollapse": true,
			"oColVis": {
				"aiExclude": [0, <?php echo $arm_colvis; //phpcs:ignore ?>]
			},
			"aoColumnDefs": [
				{"sType": "html", "bVisible": false, "aTargets": [<?php echo $column_hide; //phpcs:ignore ?>]},
				{"sClass": "center", "aTargets": [0]},
				{"bSortable": false, "aTargets": [<?php echo rtrim( $grid_clmn, ',' ); //phpcs:ignore ?>]},
				{"aTargets":[<?php echo $arm_exclude_colvis; //phpcs:ignore ?>],"sClass":"noVis"}
			],
			"fixedColumns": false,
			"bStateSave": true,
			"iCookieDuration": 60 * 60,
			"sCookiePrefix": "arm_datatable_",
			"aLengthMenu": [10, 25, 50, 100, 150, 200],
			"fnStateSave": function (oSettings, oData) {
				oData.aaSorting = [];
				oData.abVisCols = [];
				oData.aoSearchCols = [];
				this.oApi._fnCreateCookie(
						oSettings.sCookiePrefix + oSettings.sInstance,
						this.oApi._fnJsonString(oData),
						oSettings.iCookieDuration,
						oSettings.sCookiePrefix,
						oSettings.fnCookieCallback
						);
			},
			"stateSaveParams":function(oSettings,oData){
				oData.start=0;
			},
			"aaSorting": [[<?php echo $sort_clmn; //phpcs:ignore ?>, 'desc']],
			"fnStateLoadParams": function (oSettings, oData) {
				oData.iLength = 10;
				oData.iStart = 1;
				//oData.oSearch.sSearch = db_search_term;
			},
			"fnPreDrawCallback": function () {
				show_grid_loader();
			},
			"fnCreatedRow": function (nRow, aData, iDataIndex) {
				jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
					jQuery(this).parent().addClass('armGridActionTD');
					jQuery(this).parent().attr('data-key', 'armGridActionTD');
				});
			},
			
			"fnDrawCallback": function (oSettings) {
				jQuery('.arm_loading_grid').hide();
				arm_show_data();
				jQuery("#cb-select-all-1").prop("checked", false);
				arm_selectbox_init();
				jQuery('#arm_filter_wrapper').hide();
				filtered_data = false;
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
				oTables.dataTable().fnAdjustColumnSizing(false);
				jQuery('#arm_member_grid_filter_btn').removeAttr('disabled');
			}
		});

		var filter_box = jQuery('#arm_filter_wrapper').html();
		jQuery('.arm_filter_grid_list_container').find('.arm_datatable_filters_options').remove();
		jQuery('div#armember_datatable_filter').parent().append(filter_box);
		jQuery('div#armember_datatable_filter').hide();
	}
// ]]>
</script>
<div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">
	<div class="arm_datatable_filters_options">
		<div class='sltstandard'>
			<input type='hidden' id='arm_manage_bulk_action1' name="action1" value="-1" />
			<dl class="arm_selectbox arm_width_250">
				<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
				<dd>
					<ul data-id="arm_manage_bulk_action1">
						<li data-label="<?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?>" data-value="-1"><?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?></li>
						<li data-label="<?php esc_html_e( 'Delete', 'armember-membership' ); ?>" data-value="delete_member"><?php esc_html_e( 'Delete', 'armember-membership' ); ?></li>
						<?php
							$filters_data = '';
							if($ARMemberLite->is_arm_pro_active)
							{
								$filters_data = apply_filters('arm_pro_bulk_actions_filter_data',$filters_data);
								echo $filters_data; //phpcs:ignore
							}
							
						?>
						<?php


						if ( ! empty( $all_plans ) ) {
							?>
							
							<?php foreach ( $all_plans as $plan ) : ?>
								<?php if ( $plan['arm_subscription_plan_status'] == 1 ) { ?>
						  <li data-label="<?php echo stripslashes( esc_attr( $plan['arm_subscription_plan_name'] ) ); ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></li>
						  <?php } ?>
						  <?php endforeach; ?>
						  <?php }?>
					</ul>
				</dd>
			</dl>
		</div>
		<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_html_e( 'Go', 'armember-membership' ); ?>"/>
	</div>
</div>
<div class="arm_members_list">
	<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
		<div class="arm_datatable_filters_options">
			<div class='sltstandard'>
				<input type='hidden' id='arm_manage_bulk_action1' name="action1" value="-1" />
				<dl class="arm_selectbox arm_width_250">
					<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
					<dd>
						<ul data-id="arm_manage_bulk_action1">
							<li data-label="<?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?>" data-value="-1"><?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?></li>
							<li data-label="<?php esc_html_e( 'Delete', 'armember-membership' ); ?>" data-value="delete_member"><?php esc_html_e( 'Delete', 'armember-membership' ); ?></li>
							<?php
							if($ARMemberLite->is_arm_pro_active)
							{
								$filters_data = apply_filters('arm_pro_bulk_actions_filter_data',$filters_data); //phpcs:ignore
							}
							echo $filters_data; //phpcs:ignore
							if ( ! empty( $all_plans ) ) {
								if(!$ARMemberLite->is_arm_pro_active)
								{
								?>
							  <ol><?php esc_html_e( 'Change Plan To', 'armember-membership' ); ?></ol>
								<?php 
								} foreach ( $all_plans as $plan ) { ?>
									<?php if ( $plan['arm_subscription_plan_status'] == 1 ) { ?>
							  <li data-label="<?php echo stripslashes( esc_attr( $plan['arm_subscription_plan_name'] ) ); ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></li>
							   
										<?php
									}
								}
							}
							?>
						</ul>
					</dd>
				</dl>
			</div>
			<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e( 'Go', 'armember-membership' ); ?>"/>
		</div>
	</div>
	<form method="GET" id="arm_member_list_form" class="data_grid_list" onsubmit="return arm_member_list_form_bulk_action();">
		<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->manage_members); //phpcs:ignore ?>" />
		<input type="hidden" name="armaction" value="list" />
		<div class="arm_datatable_filters">
			<div class="arm_dt_filter_block arm_datatable_searchbox">
                <div class="arm_datatable_filter_item">
					<label><input type="text" placeholder="<?php esc_attr_e( 'Search Member', 'armember-membership' ); ?>" id="armmanagesearch_new" value="<?php echo esc_attr($filter_search); ?>" tabindex="-1"></label>
                </div>
				<?php
					$arm_meta_field_filters = '';
					echo apply_filters('arm_member_grid_meta_fields_filter',$arm_meta_field_filters,$user_meta_keys); //phpcs:ignore
				?>
				<!--./====================Begin Filter By Plan Box====================/.-->
				<?php if ( ! empty( $all_plans ) ) : ?>
					<div class="arm_filter_plans_box arm_datatable_filter_item">                        
						<input type="hidden" id="arm_subs_filter" class="arm_subs_filter" value="<?php echo esc_attr($filter_plan_id); ?>" />
						<dl class="arm_multiple_selectbox arm_width_250">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_subs_filter" data-placeholder="<?php esc_attr_e( 'Select Plans', 'armember-membership' ); ?>">
									<?php foreach ( $all_plans as $plan ) : ?>
										<li data-label="<?php echo stripslashes( esc_attr( $plan['arm_subscription_plan_name'] ) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><input type="checkbox" class="arm_icheckbox" value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"/><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></li>
									<?php endforeach; ?>
								</ul>
							</dd>
						</dl>
					</div>
					<?php
						$arm_membership_plans_field_filters = '';
						echo apply_filters('arm_member_grid_membership_plans_fields_filter',$arm_membership_plans_field_filters,$all_plans,$filter_member_status); //phpcs:ignore
					?>
				<?php endif; ?>
				<!--./====================End Filter By Plan Box====================/.-->
				<!--./====================Begin Filter By Member Form Box====================/.-->
				<input type="hidden" id="arm_form_filter" class="arm_form_filter" value="<?php echo esc_attr($filter_form_id); ?>" />
				<!--./====================End Filter By Member Form Box====================/.-->
			</div>
			<div class="arm_dt_filter_block arm_dt_filter_submit">
				<input type="button" class="armemailaddbtn" id="arm_member_grid_filter_btn" onClick="arm_load_membership_grid_after_filtered();" value="<?php esc_attr_e( 'Apply', 'armember-membership' ); ?>"/>
			</div>
			<div class="armclear"></div>
		</div>
		<div id="armmainformnewlist" class="arm_filter_grid_list_container">
			<div class="arm_loading_grid" style="display: none;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/loader.gif" alt="Loading.."></div>
			<div class="response_messages"></div>
			<?php do_action( 'arm_before_listing_members' ); ?>
			<div class="armclear"></div>
			<table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable">
				<thead>
					<tr>
					
						<th class="center cb-select-all-th arm_max_width_60"><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
						<?php if ( ! empty( $grid_columns ) ) { ?>
							<?php foreach ( $grid_columns as $key => $title ) : ?>
								<th data-key="<?php echo esc_attr($key); ?>" class="arm_grid_th_<?php echo esc_attr($key); ?>" ><?php echo esc_html($title); ?></th>
							<?php endforeach; ?>
						<?php } 

						$grid_column_paid_with = apply_filters('grid_column_paid_with_arm_pro',$grid_column_paid_with);
						?>
						<?php if ( $grid_column_paid_with ) : ?>
							<th class="center"><?php esc_html_e( 'Paid With', 'armember-membership' ); ?></th>
						<?php endif; ?>
						<th data-key="armGridActionTD" class="armGridActionTD noVis"></th>
					</tr>
				</thead>
			</table>
			<div class="armclear"></div>
			<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e( 'Show / Hide columns', 'armember-membership' ); ?>"/>
			<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>"/>
			<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e( 'members', 'armember-membership' ); ?>"/>
			<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e( 'Show', 'armember-membership' ); ?>"/>
			<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e( 'Showing', 'armember-membership' ); ?>"/>
			<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e( 'to', 'armember-membership' ); ?>"/>
			<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e( 'of', 'armember-membership' ); ?>"/>
			<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e( 'No matching members found.', 'armember-membership' ); ?>"/>
			<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e( 'No any member found.', 'armember-membership' ); ?>"/>
			<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e( 'filtered from', 'armember-membership' ); ?>"/>
			<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e( 'total', 'armember-membership' ); ?>"/>
			<input type="hidden" name="total_members_grid_columns" id="total_members_grid_columns" value="<?php echo esc_attr( count( $grid_columns ) ); ?>"/>
			<?php $nonce = wp_create_nonce( 'arm_wp_nonce' );?>
			<input type="hidden" name="arm_wp_nonce" value='<?php echo esc_attr( $nonce );?>'/>
			<?php do_action( 'arm_after_listing_members' ); ?>
		</div>
		<div class="footer_grid"></div>
	</form>
</div>

<div class="arm_member_view_detail_container"></div>
