<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways,$arm_common_lite;
$date_format    = $arm_global_settings->arm_get_wp_date_format();
$user_roles     = get_editable_roles();
$nowDate        = current_time( 'mysql' );
$all_plans      = $arm_subscription_plans->arm_get_all_subscription_plans();
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_REQUEST ); //phpcs:ignore
$filter_plan_id = ( ! empty( $_REQUEST['plan_id'] ) && $_REQUEST['plan_id'] != '0' ) ? intval($_REQUEST['plan_id']) : ''; //phpcs:ignore
$filter_form_id = ( ! empty( $posted_data['form_id'] ) && $posted_data['form_id'] != '0' ) ? intval($posted_data['form_id']) : '0';  //phpcs:ignore
$filter_search  = ( ! empty( $_REQUEST['sSearch'] ) ) ? $_REQUEST['sSearch'] : ''; //phpcs:ignore
$filter_member_status = (!empty($_REQUEST['member_status_id'])) ? intval($_REQUEST['member_status_id']) : '0'; //phpcs:ignore
$user_meta_keys  = $arm_member_forms->arm_get_db_form_fields( true );
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
);

$grid_columns = apply_filters('arm_members_grid_columns',$grid_columns);

/** *************./End Set Member Grid Fields/.************** */
$user_id                  = get_current_user_id();
$members_show_hide_column = maybe_unserialize( get_user_meta( $user_id, 'arm_members_hide_show_columns_' . $filter_form_id, true ) );
$column_hide              = '';
$totalCount               = count( $grid_columns ) + 3;
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
$total_grid_column     = count( $grid_columns ) + 3;
$grid_column_paid_with = true;
$arm_colvis            = $total_grid_column;
$grid_clmn          = '';
$sort_clmn          = '';
$arm_exclude_colvis = '1';
$arm_exclude_colvis_fields = '3,4,5,8,9';
$arm_exclude_colvis_arr = explode(',',$arm_exclude_colvis_fields);
$arm_less_id = 12;
if($ARMemberLite->is_arm_pro_active)
{
	$arm_less_id = 13;
}
for ( $i = 0; $i < $total_grid_column; $i++ ) {
	
	if(!in_array($i,$arm_exclude_colvis_arr))
	{	
		$grid_clmn .= $i . ',';
	}
	$sort_clmn  = 3;
}
$arm_colvis         = apply_filters('arm_pro_get_grid_arm_colvis',$arm_colvis,$total_grid_column);
$arm_exclude_colvis = apply_filters('arm_pro_get_grid_exlcuded_colvis',$arm_exclude_colvis,$total_grid_column);
$grid_clmn          = apply_filters('arm_pro_get_grid_sortable_columns',$grid_clmn,$total_grid_column);
$sort_clmn          = apply_filters('arm_pro_get_default_grid_sort_columns',$sort_clmn);

?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

jQuery(document).ready(function(){
	jQuery(document).on('click', '.wrap #armember_datatable_wrapper tr.shown td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		if( ( jQuery(e.target) ).is( 'input[type="checkbox"]' ) || ( jQuery(e.target) ).is( 'span' ) || ( jQuery(e.target) ).is( 'i' ) || ( jQuery(e.target) ).is( 'button' ) || ( jQuery(e.target) ).is( 'input.arm_autocomplete' ))
		{
			return;
		}
		var id = jQuery(this).attr('data-id');	
		var tr = jQuery(this).closest('tr');
		var class_name = jQuery(this).closest('tr').attr('class');
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var row = jQuery('#armember_datatable').DataTable().row(tr);
		row.child.hide();
		tr.removeClass('shown');
		tr.addClass('hide');
	});
	jQuery(document).on('click', '.wrap #armember_datatable_wrapper tr:not(.arm_detail_expand_container,.arm_detail_expand_container_child_row,.shown,.arm_filter_child_row,.parent) > td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		if( ( jQuery(e.target) ).is( 'input[type="checkbox"]' ) || ( jQuery(e.target) ).is( 'span' ) || ( jQuery(e.target) ).is( 'i' ) || ( jQuery(e.target) ).is( 'button' ) || ( jQuery(e.target) ).is( 'input.arm_autocomplete' ))
		{
			return;
		}
		jQuery('tr.arm_detail_expand_container').hide();
		jQuery('.wrap #armember_datatable_wrapper tr').removeClass('shown');
		jQuery('.wrap #armember_datatable_wrapper tr').addClass('hide');
		var id = jQuery(this).closest('tr').find('.arm_show_user_more_data').attr('data-id');	
		var tr = jQuery(this).closest('tr');
		var class_name = jQuery(this).closest('tr').attr('class');
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var row = jQuery('#armember_datatable').DataTable().row(tr);
		var datatable = jQuery('#armember_datatable').DataTable();
		var dataTableHeaderElements = datatable.columns().header();	
		var headers = [];
		var headers_label = [];
		for (var i = 0; i< dataTableHeaderElements.length; i++) {
			if(typeof dataTableHeaderElements[i].dataset.key != 'undefined' && !jQuery(dataTableHeaderElements[i]).is(':visible'))
			{
				key = dataTableHeaderElements[i].dataset.key;
				label = jQuery(dataTableHeaderElements[i]).text();
				headers.push(key);
				headers_label.push(label);
			}
		}
		// Open this row
		if (row.child()) {
			row.child().removeAttr('style');
			row.child().removeClass('hide');
			row.child.show();
			tr.removeClass('hide');
			tr.addClass('shown');
		}
		else{
			row.child.show();
			tr.removeClass('hide');
			row.child(user_format(id,headers,headers_label,_wpnonce), class_name +" "+"arm_detail_expand_container").show();
			tr.addClass('shown');
		}
	});
});
function user_grid_format(d,response_data) {
    var response1 = '<div class="arm_child_row_div_'+d+'">'+response_data+'</div>';
    return response1;
}

function user_format(d,headers,headers_label,_wpnonce) {
    var response1 = '<div class="arm_child_row_div_'+d+'"><div class="arm_child_row_div"><div class="arm_child_user_data_section"><div class="arm_view_member_left_box arm_no_border arm_margin_top_0" style="display: flex;align-items: center;"><img class="arm_load_subscription_plans" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_loader.gif" alt="<?php esc_attr_e('Load More', 'armember-membership'); ?>" style="margin:30px auto;padding: 10px;width:24px; height:24px;display: flex;align-items: center;"></div></div></div></div>';
    
	jQuery.ajax({
		type: "POST",
		url: __ARMAJAXURL,
		data: "action=get_user_all_details_for_grid&user_id=" + d + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
		dataType: 'html',
		success: function (response) {
			jQuery('.arm_child_row_div_'+d).html(response);
		}
	});
    return response1;
}

	<?php if(isset($_REQUEST['plan_id']) && !empty($_REQUEST['plan_id'])){?>
		jQuery(document).ready( function(){
			var arm_member_plan = <?php echo $_REQUEST['plan_id']; ?>;
			jQuery('#arm_subs_filter').val(arm_member_plan).trigger('change');
			jQuery('.arm_filter_child_row').find('#arm_member_grid_filter_btn').trigger('click');
			jQuery('.arm_filter_child_row').find('.arm_cancel_btn').removeClass('hidden_section');
			var arm_form_uri = window.location.toString();
			if( arm_form_uri.indexOf("&plan_id=") > 0 ) {
				var arm_frm_clean_uri = arm_form_uri.substring(0, arm_form_uri.indexOf("&"));
				window.history.replaceState({}, document.title, arm_frm_clean_uri);
			}
		});
	<?php }?>
	<?php if(isset($_REQUEST['member_status_id']) && !empty($_REQUEST['member_status_id'])){?>
		jQuery(document).ready( function(){
			var arm_member_status = <?php echo $_REQUEST['member_status_id']; ?>;
			jQuery('#arm_status_filter').val(arm_member_status).trigger('change');
			jQuery('.arm_filter_child_row').find('#arm_member_grid_filter_btn').trigger('click');
			jQuery('.arm_filter_child_row').find('.arm_cancel_btn').removeClass('hidden_section');
			var arm_form_uri = window.location.toString();
			if( arm_form_uri.indexOf("&member_status_id=") > 0 ) {
				var arm_frm_clean_uri = arm_form_uri.substring(0, arm_form_uri.indexOf("&"));
				window.history.replaceState({}, document.title, arm_frm_clean_uri);
			}
		});
	<?php }?>
	<?php if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] == 'view_member' && !empty($_REQUEST['id'])){?>
	jQuery(document).ready( function(){
		var user_id = <?php echo $_REQUEST['id'];?>;
		var arm_form_uri = window.location.toString();
		if( arm_form_uri.indexOf("&action=") > 0 ) {
			var arm_frm_clean_uri = arm_form_uri.substring(0, arm_form_uri.indexOf("&"));
			window.history.replaceState({}, document.title, arm_frm_clean_uri);
		}
		arm_open_preview_member_data(user_id);
	});
<?php }?>
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
			  row.child(format(row.data(),_wpnonce), class_name +" "+"arm_detail_expand_container").show();
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
		});},50);
	   return response1;
	} 

	function show_grid_loader() {
		jQuery('.arm_bulk_action_section').hide();
		jQuery(".dataTables_scroll").hide();	
		jQuery(".footer").hide();
		jQuery('.arm_loading_grid').show();
	}
	jQuery(document).ready(function () {
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_membership_grid(false);
		var count_checkbox = jQuery('.chkstanard:checked').length;
		if(count_checkbox > 0)
		{
			jQuery('.arm_bulk_action_section').removeClass('hidden_section');
		}
		else{
			jQuery('.arm_bulk_action_section').addClass('hidden_section');
		}	
	});
	jQuery(document).on('change','.chkstanard',function(e)
	{
		e.preventDefault();
		var count_checkbox = jQuery('.chkstanard:not(#cb-select-all-1):checked').length;
		var total_checkbox = jQuery('.chkstanard:not(#cb-select-all-1)').length;
		if(count_checkbox > 0)
		{
			jQuery('.arm_selected_chkcount').html(count_checkbox);
			jQuery('.arm_selected_chkcount_total').html(total_checkbox);		
			jQuery('.arm_bulk_action_section').removeClass('hidden_section').show();
		}
		else{
			jQuery('.arm_bulk_action_section').addClass('hidden_section').hide();
		}
	});

	jQuery(document).on('click','.arm_reset_bulk_action',function(){
		jQuery('.chkstanard:checked').each(function(){
			jQuery(this).prop('checked',false).trigger('change');
		})
	});


	jQuery(document).on('click','#arm_member_grid_filter_btn',function(){
		var is_filtered = 0;
		var is_before_filtered = 0;
		
		hideConfirmBoxCallback_close_filter('manage_member_filter');
		var chk_count = 0;
		var arm_selected_plan = jQuery('.arm_filter_plans_box').find('#arm_subs_filter').val();
		if(arm_selected_plan != '')
		{
			var arm_plans = arm_selected_plan.split(',');
			chk_count = arm_plans.length;
		}		
				
		if(!jQuery('.arm_filter_data_options').hasClass('hidden_section'))
		{
			is_before_filtered = 1;
		}
		else{
			is_before_filtered = 0;
		}
		jQuery('.arm_plan_filter_value').html('');
		if(chk_count > 0)
		{
			var arm_plan_label = '';
			var arm_selected_plan_labels = [];
			var arm_plan_label_temp = '';
			var first_selected_plan_lbl = '';
			var first_selected_plan_id = arm_plans[0];
			
			var first_selected_plan_lbl = jQuery('.arm_filter_plans_box').find('li[data-value="'+first_selected_plan_id+'"]').attr('data-label');
			arm_plans.forEach(function(plan_id){				
				var plan_label = jQuery('.arm_filter_plans_box').find('li[data-value="'+plan_id+'"]').attr('data-label');
				arm_selected_plan_labels.push(plan_label);
			});		
			if(chk_count > 1)
			{
				first_selected_plan_lbl += '...';
			}
			
			if(typeof arm_selected_plan_labels != 'undefined')
			{
				arm_selected_plan_labels.forEach(
					function(plan_label) {
						arm_plan_label_temp += plan_label+',</br>';
					}
				);
				arm_plan_label = arm_plan_label_temp;
				
			}		
			
			jQuery('.arm_plan_tp').removeClass('hidden_section');
			jQuery('.arm_plan_filter_value_tooltip').html(arm_plan_label);		
			first_selected_plan_lbl = first_selected_plan_lbl != '' ? first_selected_plan_lbl : jQuery('.arm_filter_plans_box').find('ul[data-id="arm_subs_filter"]').attr('data-placeholder');
			jQuery('.arm_plan_filter_value').html(first_selected_plan_lbl);
			jQuery('.arm_members_plan_filter').removeClass('hidden_section');
			jQuery('.arm_reset_bulk_action').trigger('click');
			jQuery('.arm_filter_data_options').removeClass('hidden_section');
		}
		else{
			var first_selected_plan_lbl = jQuery('.arm_filter_plans_box').find('ul[data-id="arm_subs_filter"]').attr('data-placeholder');
			jQuery('.arm_plan_filter_value_tooltip').html('');
			jQuery('.arm_plan_filter_value').html(first_selected_plan_lbl);
			jQuery('.arm_plan_tp').addClass('hidden_section');
			jQuery('.arm_members_plan_filter').addClass('hidden_section');
			jQuery('.arm_filter_data_options').addClass('hidden_section');
		}
		
		if(!jQuery('.arm_filter_data_options').hasClass('hidden_section'))
		{
			is_filtered = 1;
		}
		else{
			is_filtered = 0;
		}
		is_filtered = wp.hooks.applyFilters('arm_filter_list_action',is_filtered);
		if(is_filtered == 1)
		{
			jQuery('.arm_filter_data_options').removeClass('hidden_section')
		}
		if(is_filtered == 1 || is_before_filtered == 1)
		{
			arm_member_list_grid_load_filter_data();
			jQuery('.arm_reset_bulk_action').trigger('click');
			setTimeout(function () {
				arm_load_membership_grid_after_filtered();
				is_before_filtered = 0;
			},200);
		}
		else
		{
			jQuery('.arm_filter_data_options').addClass('hidden_section');
		}	
	});

	function arm_member_list_grid_load_filter_data() {
		if (jQuery('.arm_filter_fields_box').length > 0) {
			var arm_selected_fields = jQuery('.arm_filter_fields_box').find('#arm_meta_field_filter').val();
			if (arm_selected_fields != '' && arm_selected_fields != 0) {
				var fields_label = jQuery('.arm_filter_fields_box').find('li[data-value="'+arm_selected_fields+'"]').attr('data-label');
				jQuery('.arm_fields_filter_value').html(fields_label);
			} else {
				var fields_label = jQuery('.arm_filter_fields_box').find('li[data-value="0"]').attr('data-label');
				jQuery('.arm_fields_filter_value').html(fields_label);
			}
		}
		if (jQuery('.arm_filter_status_box').length > 0) {
			var arm_selected_status = jQuery('.arm_filter_status_box').find('#arm_status_filter').val();
			if (arm_selected_status != '' && arm_selected_status != 0) {
				var status_label = jQuery('.arm_filter_status_box').find('li[data-value="'+arm_selected_status+'"]').attr('data-label');
				jQuery('.arm_status_filter_value').html(status_label);
			} else {
				var status_label = jQuery('.arm_filter_status_box').find('li[data-value="0"]').attr('data-label');
				jQuery('.arm_status_filter_value').html(status_label);
			}
		}
		if (jQuery('.arm_filter_membership_type_label').length > 0) {
			var arm_selected_membership_type = jQuery('.arm_filter_membership_type_label').find('#arm_filter_membership_type').val();
			if (arm_selected_membership_type != '' && arm_selected_membership_type != 0) {
				var membership_type_label = jQuery('.arm_filter_membership_type_label').find('li[data-value="'+arm_selected_membership_type+'"]').attr('data-label');
				jQuery('.arm_membership_type_filter_value').html(membership_type_label);
			} else {
				var membership_type_label = jQuery('.arm_filter_membership_type_label').find('li[data-value="0"]').attr('data-label');
				jQuery('.arm_membership_type_filter_value').html(membership_type_label);
			}
		}
	}

	jQuery(document).on('change','.arm_filter_data_options:not(.arm_bulk_action_section) input:not([type="button"])',function(){
		if(jQuery(this).val() != '')
		{
			jQuery('#arm_member_grid_filter_clr_btn').removeClass('hidden_section');
		}
		else{
			jQuery('#arm_member_grid_filter_clr_btn').addClass('hidden_section');
		}
	})

	function arm_reset_membership_grid(){
		hideConfirmBoxCallback_filter('manage_member_filter');
		jQuery('.arm_plan_filter_value').html('');
		jQuery('.arm_plan_tp').addClass('hidden_section');
		jQuery('.arm_plan_filter_value_tooltip').html("");
		// jQuery('.arm_membership_plan_filters').removeClass('tipso_style');
		jQuery('.arm_filter_data_options').addClass('hidden_section');
		wp.hooks.doAction('arm_reset_datatable');
		jQuery('.arm_reset_bulk_action').trigger('click');
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_membership_grid_after_filtered(false);
		arm_selectbox_init();
	}

	function arm_reset_fields_membership_grid(){
		hideConfirmBoxCallback_filter('manage_member_filter');	
	}

	function arm_load_membership_grid_after_filtered() {
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_membership_grid();
	}
	jQuery(document).on('keyup','#armmanagesearch_new', function (e) {
		// e.stopPropagation();
		var arm_search_val = jQuery(this).val();
		jQuery('#armmanagesearch_new').val(arm_search_val);
		if (e.keyCode == 13 || 'Enter' == e.key) {
			jQuery('#armember_datatable').dataTable().fnDestroy();
			var chk_count = 0;
			var arm_selected_plan = jQuery('.arm_filter_plans_box').find('#arm_subs_filter').val();
			if(arm_selected_plan != '')
			{
				var arm_plans = arm_selected_plan.split(',');
				chk_count = arm_plans.length;
			}		

			jQuery('.arm_plan_filter_value').html('');
			if(chk_count > 0)
			{
				var first_selected_plan_lbl = '';
				var first_selected_plan_id = arm_plans[0];
				
				var first_selected_plan_lbl = jQuery('.arm_filter_plans_box').find('li[data-value="'+first_selected_plan_id+'"]').attr('data-label');	
				if(chk_count > 1)
				{
					first_selected_plan_lbl += '...';
				}		
			
				first_selected_plan_lbl = first_selected_plan_lbl != '' ? first_selected_plan_lbl : jQuery('.arm_filter_plans_box').find('ul[data-id="arm_subs_filter"]').attr('data-placeholder');
				jQuery('.arm_plan_filter_value').html(first_selected_plan_lbl);
			}
			else
			{
				var first_selected_plan_lbl = jQuery('.arm_filter_plans_box').find('ul[data-id="arm_subs_filter"]').attr('data-placeholder');
				jQuery('.arm_plan_filter_value').html(first_selected_plan_lbl);
			}
			arm_member_list_grid_load_filter_data();
			arm_load_membership_grid_after_filtered();
			return false;
		}
	});
	function arm_load_membership_grid(is_filtered=false) {
		var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Showing_empty = '<?php echo addslashes( esc_html__( 'Showing','armember-membership').'<span class="arm-black-350 arm_font_size_15">0</span> - <span class="arm-black-350 arm_font_size_15">0</span> of <span class="arm-black-350 arm_font_size_15">0</span> '.esc_html__('members', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_to = '-';
		var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_MEMBERS = ' <?php esc_html_e( 'members', 'armember-membership' ); //phpcs:ignore ?>';
		var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
		var __ARM_NO_FOUND = '<?php echo addslashes( esc_html__( 'No any member found.', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching records found.', 'armember-membership' ) ); //phpcs:ignore ?>';

		var search_term = jQuery('#armmanagesearch_new').val();
		
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

		$arm_colvis = "1,2,6";

		var oTables = jQuery('#armember_datatable').dataTable({
			"oLanguage": {
				"sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_MEMBERS,
				"sInfoEmpty": __ARM_Showing_empty,
				
				"sLengthMenu": __ARM_Show + "_MENU_",
				"sEmptyTable": __ARM_NO_FOUND,
				"sZeroRecords": __ARM_NO_MATCHING,
			},
            "bDestroy": true,
			"language":{
				"searchPlaceholder":"<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
				"search":"",
			},
			"buttons":[],
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
			"aoColumnDefs": [
				{"sType": "html", "bVisible": false, "aTargets": [<?php echo $column_hide; //phpcs:ignore ?>]},
				{"sClass": "arm_padding_left_0 arm_width_30 center noVis", "aTargets": [1]},
				{"bSortable": false, "aTargets": [<?php echo rtrim( $grid_clmn, ',' ); //phpcs:ignore ?>]},
				{"aTargets":[<?php echo $arm_exclude_colvis; //phpcs:ignore ?>],"sClass":"noVis"},
				{"sClass":"arm_padding_right_0 arm_min_width_40 center noVis","aTargets":[0]},
				{"sClass":"arm_min_width_60","aTargets":[2]},
				{"sClass":"arm_min_width_80","aTargets":[3]},
				{"sClass":"arm_min_width_200","aTargets":[5]},
				{"sClass":"arm_width_250 arm_max_width_250","aTargets":[5]},
				{"sClass":"arm_min_width_130","aTargets":[7]},
				{"sClass":"arm_min_width_150","aTargets":[4,6,8,9]},
				{ "aTargets": -1, "responsivePriority": 1 }
			],
			"responsive": {
				details: {
					type: 'column',
					target: '' // This removes the dtr-control click event
				}
			},
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
				jQuery('.dataTables_scroll').show();
				jQuery(".footer").show();
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
				var datatable = jQuery('#armember_datatable').DataTable();
				var dataTableHeaderElements = datatable.columns().header();	
				for (var i = 0; i< dataTableHeaderElements.length; i++) {
					if(typeof dataTableHeaderElements[i].dataset.key != 'undefined')
					{
						if(!jQuery(dataTableHeaderElements[i]).is(':visible')){
							var i = i - 1;
							jQuery(dataTableHeaderElements[i]).addClass('arm_last_dt_col');
							break;
						}
					}
				}
				//get user id
				var grid_data_length = jQuery('.arm_hide_datatable tbody .chkstanard').length;
				var grid_ids = [];
				jQuery('.arm_hide_datatable tbody .chkstanard').each(function(){
					var id = jQuery(this).closest('tr').find('.arm_show_user_more_data').attr('data-id');
					grid_ids.push(id);
				})
				
				var datatable = jQuery('#armember_datatable').DataTable();
				var dataTableHeaderElements = datatable.columns().header();	
				var headers = [];
				var headers_label = [];
				for (var i = 0; i< dataTableHeaderElements.length; i++) {
					if(typeof dataTableHeaderElements[i].dataset.key != 'undefined' && !jQuery(dataTableHeaderElements[i]).is(':visible'))
					{
						key = dataTableHeaderElements[i].dataset.key;
						label = jQuery(dataTableHeaderElements[i]).text();
						headers.push(key);
						headers_label.push(label);
					}
				}
				if(grid_ids != '') {
					jQuery.ajax({
						type: "POST",
						url: __ARMAJAXURL,
						data: "action=get_user_all_details_for_grid_loads&user_ids=" + grid_ids + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
						dataType: 'json',
						success: function (response) {
							
							jQuery.each(grid_ids, function(index, uid) {							
								var arm_user_d = 'arm_user_id_'+uid;
								var response_data = response[arm_user_d];
								var tr = jQuery('.arm_hide_datatable tbody .chkstanard[value="'+uid+'"]').closest('tr');
								var row = jQuery('#armember_datatable').DataTable().row(tr);
								var class_name = jQuery('.arm_hide_datatable tbody .chkstanard[value="'+uid+'"]').closest('tr').attr('class');
								if (!row.child()) {
									row.child(user_grid_format(uid,response_data), class_name +" "+"arm_detail_expand_container").hide();							
									tr.removeClass('shown');
									tr.addClass('hide');
								}
							})
						}
					});
				}
			}
		});

		var filter_box = jQuery('#arm_filter_wrapper').html();
		jQuery('.arm_filter_grid_list_container').find('.arm_datatable_filters_options').remove();
		jQuery('div#armember_datatable_filter').parent().append(filter_box);
		jQuery('div#armember_datatable_filter').hide();
		// jQuery('#arm_member_grid_filter_btn').removeAttr('disabled');
		if(db_search_term != ''){
			jQuery('.arm_datatable_searchbox').find('#armmanagesearch_new').val(db_search_term)
		}
		
	}
// ]]>

jQuery(document).on('change','#arm_manage_bulk_action1',function(){
	var action_val = jQuery(this).val();
	if(action_val == 'change_plan')
	{
		jQuery('.arm_bulk_action_other_section').removeClass('hidden_section');
		jQuery('.arm_bulk_action_plan_section').removeClass('hidden_section');
		jQuery('.arm_bulk_action_status_section').addClass('hidden_section');
	}
	else if(action_val == 'change_status')
	{
		jQuery('.arm_bulk_action_other_section').removeClass('hidden_section');
		jQuery('.arm_bulk_action_status_section').removeClass('hidden_section');
		jQuery('.arm_bulk_action_plan_section').addClass('hidden_section');
	}
	else{
		jQuery('.arm_bulk_action_other_section').addClass('hidden_section');
		jQuery('.arm_bulk_action_status_section').addClass('hidden_section');
		jQuery('.arm_bulk_action_plan_section').addClass('hidden_section');
	}
})
</script>
<div class="arm_loading_grid" style="display: none;">
	<?php $arm_loader = $arm_common_lite->arm_loader_img_func();
	echo $arm_loader; //phpcs:ignore ?>
</div>
<div class="arm_members_list">
	<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
		<div class="arm_datatable_filters_options arm_bulk_action_section hidden_section">
			<span class="arm_reset_bulk_action"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6.34313 17.6569L12 12M17.6568 6.34315L12 12M12 12L6.34313 6.34315M12 12L17.6568 17.6569" stroke="#617191" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="arm_selected_chkcount"></span>&nbsp;&nbsp;<span><?php esc_html_e('of','armember-membership');?></span>&nbsp;&nbsp;<span class="arm_selected_chkcount_total arm-black-600 arm_font_size_15'>"></span>&nbsp;&nbsp;<span><?php esc_html_e('Selected','armember-membership');?></span><div class="arm_margin_right_10"></div><div class="arm_margin_left_10"></div>
			<div class='sltstandard'>
				<input type='hidden' id='arm_manage_bulk_action1' name="action1" value="-1" />
				<dl class="arm_selectbox arm_width_250">
					<dt><span class="arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i></dt>
					<dd>
						<ul data-id="arm_manage_bulk_action1">
							<li data-label="<?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?>" data-value="-1"><?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?></li>
							<li data-label="<?php esc_html_e( 'Delete Members', 'armember-membership' ); ?>" data-value="delete_member"><?php esc_html_e( 'Delete Members', 'armember-membership' ); ?></li>
							<li data-label="<?php esc_html_e( 'Change Plan', 'armember-membership' ); ?>" data-value="change_plan"><?php esc_html_e( 'Change Plan', 'armember-membership' ); ?></li>
							<?php
							$filters_data = '';
							if($ARMemberLite->is_arm_pro_active)
							{
								$filters_data = apply_filters('arm_pro_bulk_actions_filter_data',$filters_data); //phpcs:ignore
							}
							echo $filters_data; //phpcs:ignore
							?>
						</ul>
					</dd>
				</dl>
				<div class="arm_bulk_action_other_section arm_display_flex_wrap hidden_section">
					<span class="arm_margin_left_5 arm_margin_right_5"><?php esc_html_e('To','armember-membership')?></span>
				</div>
				<div class="arm_bulk_action_plan_section hidden_section">
					<input type='hidden' id='arm_bulk_action_plan' name="action_plan" value="" />
					<dl class="arm_selectbox arm_width_250 arm_bulk_action_plan">
						<dt><span class="arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_bulk_action_plan">
								<li data-label="<?php echo esc_html__('Select Membership Plan','armember-membership'); ?>" data-value=""><?php echo esc_html__('Select Membership Plan','armember-membership'); ?></li>
								<?php
								if ( ! empty( $all_plans ) ) {
									foreach ( $all_plans as $plan ) { ?>
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
				<?php
				$filters_data = '';
				if($ARMemberLite->is_arm_pro_active)
				{
					$filters_data = apply_filters('arm_pro_bulk_action_to_filter_data',$filters_data); //phpcs:ignore
					echo $filters_data; //phpcs:ignore
				}
				?>
			</div>
			
			<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e( 'Go', 'armember-membership' ); ?>"/>
		</div>	
		<div class="arm_datatable_filters_options arm_filters_fields">
			<div class="sltstandard">			
				<div class="arm_confirm_box_btn_container arm_margin_0">
					<div class="arm_dt_filter_block arm_datatable_searchbox">
						<div class="arm_datatable_filter_item">
							<label class="arm_padding_0"><input type="text" placeholder="<?php esc_attr_e( 'Search Members', 'armember-membership' ); ?>" id="armmanagesearch_new" value="<?php echo esc_attr($filter_search); ?>" tabindex="0"></label>
						</div>				
					</div>
					<?php
						$arm_meta_field_filters = '';
						echo apply_filters('arm_member_grid_meta_fields_filter',$arm_meta_field_filters,$user_meta_keys); //phpcs:ignore
					?>
					<div class="arm_filter_child_row">
						<div>
							<?php if ( ! empty( $all_plans ) ) : ?>
								<div class="arm_filter_plans_box arm_datatable_filter_item">                        
									<input type="text" id="arm_subs_filter" class="arm_subs_filter arm-selectpicker-input-control" value="<?php echo esc_attr($filter_plan_id); ?>" />
									<dl class="arm_multiple_selectbox arm_width_250">
										<dt>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7522_5770)"><rect x="6" y="2" width="8" height="16" rx="2" fill="#9CA7BD"/><path opacity="0.4" d="M11 4H16C17.1046 4 18 4.89543 18 6V14C18 15.1046 17.1046 16 16 16H11V4Z" fill="#9CA7BD"/><path opacity="0.4" d="M2 6C2 4.89543 2.89543 4 4 4H9V16H4C2.89543 16 2 15.1046 2 14V6Z" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7522_5770"><rect width="16" height="16" fill="white" transform="translate(2 2)"/></clipPath></defs></svg>
											<span class="arm_plan_filter_value"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i>
										</dt>
										<dd>
											<ul data-id="arm_subs_filter" data-placeholder="<?php esc_attr_e( 'Select Plans', 'armember-membership' ); ?>">
												<?php foreach ( $all_plans as $plan ) : ?>
													<li data-label="<?php echo stripslashes( esc_attr( $plan['arm_subscription_plan_name'] ) ); //phpcs:ignore ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><input type="checkbox" class="arm_icheckbox" value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"/><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></li>
												<?php endforeach; ?>
											</ul>
										</dd>
									</dl>
								</div>
							<?php endif;?>
						</div>
					</div>
					<?php
						$arm_membership_plans_field_filters = '';
						echo apply_filters('arm_member_grid_membership_plans_fields_filter',$arm_membership_plans_field_filters,$all_plans,$filter_member_status); //phpcs:ignore
					?>
					<div class="arm_filter_child_row arm_margin_left_8">
						<div>
							<input type="button" class="armemailaddbtn arm_margin_left_12" id="arm_member_grid_filter_btn" value="<?php esc_html_e('Apply','armember-membership');?>">
							<input type="button" class="arm_cancel_btn arm_margin_left_12 hidden_section" value="<?php esc_html_e('Clear','armember-membership');?>">
						</div>
					</div>
					<input type="hidden" id="arm_form_filter" class="arm_form_filter" value="<?php echo esc_attr($filter_form_id); ?>" />
				</div>
			</div>
			<div class="arm_filter_hide_show_btn_section arm_hide">
				<button type="button" class="arm_filter_hide_show_btn" id="arm_filter_hide_show_btn" data-status="0">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7619_15796)"><g clip-path="url(#clip1_7619_15796)"><path d="M17 1H3C1.89543 1 1 1.89557 1 3.00031V4.17207C1 4.70259 1.21071 5.21137 1.58579 5.58651L7.41421 11.4158C7.78929 11.791 8 12.2998 8 12.8302V18.0027V18.2884C8 18.9211 8.7649 19.2379 9.2122 18.7906L10 18.0027L11.4142 16.5882C11.7893 16.2131 12 15.7043 12 15.1738V12.8302C12 12.2998 12.2107 11.791 12.5858 11.4158L18.4142 5.58651C18.7893 5.21137 19 4.70259 19 4.17207V3.00031C19 1.89557 18.1046 1 17 1Z" stroke="#617191" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></g></g><defs><clipPath id="clip0_7619_15796"><rect width="20" height="20" fill="white"/></clipPath><clipPath id="clip1_7619_15796"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>
				</button>
			</div>
		</div>
	</div>
	<form method="GET" id="arm_member_list_form" class="data_grid_list" onsubmit="return arm_member_list_form_bulk_action();">
		<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->manage_members); //phpcs:ignore ?>" />
		<input type="hidden" name="armaction" value="list" />
		<div id="armmainformnewlist" class="arm_filter_grid_list_container">
			<div class="response_messages"></div>
			<?php do_action( 'arm_before_listing_members' ); ?>
			<div class="armclear"></div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="display arm_hide_datatable" id="armember_datatable">
				<thead>
					<tr>
						<th class="arm_min_width_40 arm_padding_right_0"></th>
						<th class="center cb-select-all-th"><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
						<?php if ( ! empty( $grid_columns ) ) { ?>
							<?php foreach ( $grid_columns as $key => $title ) : ?>
								<th data-key="<?php echo esc_attr($key); ?>" class="arm_grid_th_<?php echo esc_attr($key); ?>" ><?php echo esc_html($title); ?></th>
							<?php endforeach; ?>
						<?php }
						?>
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

<?php require_once(MEMBERSHIPLITE_VIEWS_DIR.'/arm_view_member_details.php')?>
