<?php
global $wpdb, $ARMemberLite, $arm_subscription_plans, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_payment_gateways,$arm_common_lite;
$user_roles  = get_editable_roles();
$user_roles1 = $arm_global_settings->arm_get_all_roles();

$filter_search = (!empty($_POST['sSearch'])) ? sanitize_text_field($_POST['sSearch']) : '';//phpcs:ignore
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.ColVis_Button{display:none;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

jQuery(document).keydown(function(e) {
    if (e.key === "Escape") {
		jQuery('.arm_subscription_plans_add_edit_main_wrapper .error.arm_invalid').each(function()
		{
			jQuery('span.error.arm_invalid').html('');
			jQuery(this).removeClass('error');
			jQuery(this).removeClass('arm_invalid');
		});
		jQuery('body').css("overflow", "auto");
		arm_membership_reset_func();
		arm_selectbox_init();
    }
  });
jQuery(document).ready( function () {
	arm_load_plan_list_filtered_grid();
	jQuery('#subscription_plans_list_form .arm_datatable_searchbox input[type="search"]').val('').trigger('keyup');
});

jQuery(document).on('keyup','#armmanageplan_search',function(e){
	var arm_search = jQuery(this).val();
	jQuery('.arm_datatable_searchbox #armmanageplan_search').val(arm_search);
	if (e.keyCode == 13 || 'Enter' == e.key) {
		arm_load_plan_list_filtered_grid();
		return false;
	}
});
jQuery(document).on('click','#arm_member_plan_grid_filter_btn',function(e){
	arm_load_plan_list_filtered_grid();
});

function arm_load_plan_list_filtered_grid()
{
	jQuery('#armember_datatable').dataTable().fnDestroy();
	arm_load_plan_list_grid();
}

function show_grid_loader(){
	jQuery('#armember_datatable').css('visibility','hidden');
	jQuery(".dataTables_scroll").hide();
	jQuery('.footer').hide();
    jQuery('.arm_loading_grid').show();
}

function arm_load_plan_list_grid(){
		var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing','armember-membership').' <span class="arm-black-350 arm_font_size_15">0</span> - <span class="arm-black-350 arm_font_size_15">0</span> of <span class="arm-black-350 arm_font_size_15">0</span> '.esc_html__('enteries','armember-membership')); //phpcs:ignore?>';
		var __ARM_to = '-';
		var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_PLANS = ' <?php echo addslashes( esc_html__( 'Plans', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
		var __ARM_NO_FOUND = '<?php echo addslashes( esc_html__( 'No any subscription plan found.', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching records found.', 'armember-membership' ) ); //phpcs:ignore ?>';

		var __SHOW_PER_PAGE = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?>';

		var ajax_url = '<?php echo admin_url("admin-ajax.php"); //phpcs:ignore?>';
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var search_term = jQuery('.arm_datatable_searchbox #armmanageplan_search').val();
		
		var db_search_term = (typeof search_term !== 'undefined' && search_term !== '') ? search_term : '';
		

		var table = jQuery('#armember_datatable').dataTable({
		"oLanguage": {
			"sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_PLANS,
			"sInfoEmpty": __ARM_Showing_empty,
		
			"sLengthMenu": __SHOW_PER_PAGE + "_MENU_" ,
			"sEmptyTable": __ARM_NO_FOUND,
			"sZeroRecords": __ARM_NO_MATCHING,
		},
		"bDestroy": true,
		"language":{
			"searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
			"search":"",
		},
		"buttons":[],
		"bProcessing": false,
		"responsive": true,
		"bServerSide": true,
		"sAjaxSource": ajax_url,
		"sServerMethod": "POST",
		"fnServerParams": function (aoData) {
			aoData.push({'name': 'action', 'value': 'arm_get_subscription_plan_details'});
			aoData.push({'name': 'sSearch', 'value': db_search_term});
			aoData.push({'name': '_wpnonce', 'value': _wpnonce});
		},
		"bRetrieve": false,
		"sDom": '<"H"CBfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth" : false,
		"aaSorting": [],
		"fixedColumns": false,
		"sScrollX":"100%",
		"bScrollCollapse": false,
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [3] },
			{ "sWidth": "10%", "aTargets": [0] },
			{ "sWidth": "40%", "aTargets": [1] },
			{ "sWidth": "25%", "aTargets": [2] },
			{ "sWidth": "10%", "aTargets": [3] },
			{ "sWidth": "15%", "aTargets": [4] }		
		],	
		"bStateSave": true,
		"iCookieDuration": 60 * 60,
		"sCookiePrefix": "arm_datatable_",
		"aLengthMenu": [10, 25, 50, 100, 150, 200],
		"fnPreDrawCallback": function () {
			show_grid_loader();
		},
		"aaSorting": [0, 'desc'],
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
		"fnStateLoadParams": function (oSettings, oData) {
			oData.iLength = 10;
			oData.iStart = 1;
		},
		"fnCreatedRow": function (nRow, aData, iDataIndex) {
			jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
				jQuery(this).parent().addClass('armGridActionTD');
				jQuery(this).parent().attr('data-key', 'armGridActionTD');
			});
		},
		"fnDrawCallback":function(){
			jQuery('.arm_loading_grid').hide();
			jQuery('#armember_datatable').css('visibility','visible');
			jQuery(".dataTables_scroll").show();
			jQuery('.footer').show();
			arm_show_data();
			jQuery('#arm_filter_wrapper').hide();
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
			table.dataTable().fnAdjustColumnSizing(false);
		}
	});
	var filter_box = jQuery('#arm_filter_wrapper').html();
	jQuery('div#armember_datatable_filter').parent().append(filter_box);
	jQuery('div#armember_datatable_filter').addClass('arm_datatable_searchbox');
	// jQuery('#arm_filter_wrapper').remove();
	if(typeof db_search_term != 'undefined'){
		jQuery('.arm_datatable_searchbox #armmanageplan_search').val(db_search_term);
	}
}
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>
<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
	<div class="arm_datatable_filters_options arm_filters_searchbox">
		<div class="sltstandard">
			<div class="arm_confirm_box_btn_container">
				<div class="arm_dt_filter_block arm_datatable_searchbox">
					<div class="arm_datatable_filter_item">
						<label class="arm_padding_0"><input type="text" placeholder="<?php esc_attr_e( 'Search Plans', 'armember-membership' ); ?>" id="armmanageplan_search" value="<?php echo esc_attr($filter_search); ?>" tabindex="0"></label>
					</div>
				</div>
				<div class="arm_filter_child_row arm_margin_left_12">
					<div>
						<input type="button" class="armemailaddbtn arm_margin_left_12" id="arm_member_plan_grid_filter_btn" value="<?php esc_html_e('Apply','armember-membership');?>">
						<input type="button" class="arm_cancel_btn arm_margin_left_12 hidden_section" value="<?php esc_html_e('Clear','armember-membership');?>">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="wrap arm_page arm_subscription_plans_main_wrapper">
	<div class="content_wrapper arm_subscription_plans_content" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Manage Membership Plans', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_new_plan_btn" href="javascript:void(0)"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/add_new_icon.svg"><span><?php esc_html_e( 'Add New Plan', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="arm_solid_divider"></div>
		
		<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func(); 
		echo $arm_loader; //phpcs:ignore ?></div>
		<div class="arm_subscription_plans_list">
			
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list">
				<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->manage_plans); //phpcs:ignore ?>" />
				<input type="hidden" name="armaction" value="list" />
				<div id="armmainformnewlist" class="arm_memebership_plan_lists">
					<table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable" style="visibility: hidden;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plan ID', 'armember-membership' ); ?></th>
								<th><?php esc_html_e( 'Plan Name', 'armember-membership' ); ?></th>
								<th><?php esc_html_e( 'Plan Type', 'armember-membership' ); ?></th>
								<th><?php esc_html_e( 'Members', 'armember-membership' ); ?></th>
								<th><?php esc_html_e( 'Wp Role', 'armember-membership' ); ?></th>							
								<th class="armGridActionTD"></th>
							</tr>
						</thead>
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
<?php if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'new'){?>
	jQuery(window).on("load", function(){
		jQuery('.arm_add_new_plan_btn').trigger('click');
		var arm_form_uri = window.location.toString();
		if( arm_form_uri.indexOf("&action=new") > 0 ) {
			var arm_frm_clean_uri = arm_form_uri.substring(0, arm_form_uri.indexOf("&"));
			window.history.replaceState({}, document.title, arm_frm_clean_uri);
		}
	});
<?php }?>
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