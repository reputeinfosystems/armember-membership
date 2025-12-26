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
jQuery(document).ready( function () {
	arm_load_plan_list_grid();
	jQuery('#subscription_plans_list_form .arm_datatable_searchbox input[type="search"]').val('').trigger('keyup');
});

jQuery(document).on('keyup','#armmanageplan_search',function(e){
	if (e.keyCode == 13 || 'Enter' == e.key) {
		var arm_search = jQuery(this).val();
		jQuery('#subscription_plans_list_form .arm_datatable_searchbox input[type="search"]').val(arm_search).trigger('keyup');
	}
});

function arm_load_plan_list_filtered_grid()
{
	jQuery('#armember_datatable').dataTable().fnDestroy();
	arm_load_plan_list_grid();
}

function show_grid_loader(){
	jQuery('#armember_datatable').hide();
	jQuery('.footer').hide();
    jQuery('.arm_loading_grid').show();
}

function arm_load_plan_list_grid(){
		var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Showing_empty = '<?php echo addslashes( esc_html__( 'Showing 0 to 0 of 0 enteries', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_to = '<?php echo addslashes( esc_html__( 'to', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_PLANS = ' <?php echo addslashes( esc_html__( 'Plans', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
		var __ARM_NO_FOUND = '<?php echo addslashes( esc_html__( 'No any subscription plan found.', 'armember-membership' ) ); //phpcs:ignore ?>';
		var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching records found.', 'armember-membership' ) ); //phpcs:ignore ?>';

		var __SHOW_PER_PAGE = '<?php echo addslashes( esc_html__( 'Plans per page', 'armember-membership' ) ); //phpcs:ignore ?>';

		var ajax_url = '<?php echo admin_url("admin-ajax.php"); //phpcs:ignore?>';
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
	
		var table = jQuery('#armember_datatable').dataTable({
		
		"oLanguage": {
			"sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_PLANS,
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
		"bProcessing": false,
		"responsive": true,
		"bServerSide": true,
		"sAjaxSource": ajax_url,
		"sServerMethod": "POST",
		"fnServerParams": function (aoData) {
			aoData.push({'name': 'action', 'value': 'arm_get_subscription_plan_details'});
			aoData.push({'name': '_wpnonce', 'value': _wpnonce});
		},
		"bRetrieve": false,
		"sDom": '<"H"fr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth" : false,
		"bScrollCollapse": true,
		"aaSorting": [],
		"fixedColumns": false,
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [] },
			{ "sClass": "arm_padding_left_24", "aTargets": [0,1,2,3,4] }
		],
		"bStateSave": true,
		"iCookieDuration": 60 * 60,
		"sCookiePrefix": "arm_datatable_",
		"aLengthMenu": [10, 25, 50, 100, 150, 200],
		"fnPreDrawCallback": function () {
			show_grid_loader();
		},
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
			setTimeout(function(){
				jQuery('#armember_datatable').show();
				jQuery('.footer').show();
				jQuery('.arm_loading_grid').hide();
				arm_show_data();
				jQuery('#arm_filter_wrapper').hide();
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
	jQuery('div#armember_datatable_filter').addClass('arm_datatable_searchbox');
	// jQuery('#arm_filter_wrapper').remove();
}
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}
// ]]>
</script>
<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
	<div class="arm_datatable_filters_options arm_filters_searchbox">
		<div class="sltstandard">
			<div class="arm_dt_filter_block arm_datatable_searchbox">
				<div class="arm_datatable_filter_item">
					<label class="arm_padding_0"><input type="text" placeholder="<?php esc_attr_e( 'Search Plans', 'armember-membership' ); ?>" id="armmanageplan_search" value="<?php echo esc_attr($filter_search); ?>" tabindex="-1"></label>
				</div>				
			</div>
		</div>
	</div>
</div>
<div class="wrap arm_page arm_subscription_plans_main_wrapper">
	<div class="content_wrapper arm_subscription_plans_content" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Manage Membership plans', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_new_plan_btn" href="javascript:void(0)"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/add_new_icon.svg"><span><?php esc_html_e( 'Add New Plan', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="arm_solid_divider"></div>	
		<div class="arm_subscription_plans_list">
			
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list">
				<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->manage_plans); //phpcs:ignore ?>" />
				<input type="hidden" name="armaction" value="list" />
				<div id="armmainformnewlist">
					<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
					echo $arm_loader; //phpcs:ignore ?></div>
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