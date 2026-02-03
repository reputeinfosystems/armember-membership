<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings,  $arm_subscription_plans, $arm_payment_gateways,$arm_pay_per_post_feature,$arm_common_lite;
$date_format             = $arm_global_settings->arm_get_wp_date_format();
$actions['delete_setup'] = esc_html__( 'Delete', 'armember-membership' );
//$addNewSetupLink         = admin_url( 'admin.php?page=' . $arm_slugs->membership_setup . '&action=new_setup' );
$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';//phpcs:ignore
/*if ( $total_setups < 1 ) {
	wp_redirect( $addNewSetupLink );
	exit;
}*/
$arm_col = '0,4';
	if($ARMemberLite->is_arm_pro_active)
	{
		if( ( $arm_pay_per_post_feature->isPayPerPostFeature || is_plugin_active('armembergift/armembergift.php'))){
			$arm_col = "0,1,4";
		}
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
var add_setup_shortcode_text = '<span style="display: block;font-size: 12px;line-height: normal;text-align: left;"><?php esc_html_e('Shortcode will be display here once you save current setup.', 'armember-membership');?> </span>';

jQuery(document).ready( function () {
	jQuery('#armember_datatable').dataTable().fnDestroy();
	arm_load_setup_list_grid();
});

function arm_load_setup_list_filtered_grid(){
	jQuery('#armember_datatable').dataTable().fnDestroy();
	arm_load_setup_list_grid();
}

jQuery(document).on('keyup','#armmanagesearch_new',function(e){
	var arm_search_val = jQuery(this).val();
	jQuery('.arm_datatable_searchbox #armmanagesearch_new').val(arm_search_val);
	if (e.keyCode == 13 || 'Enter' == e.key) {
		arm_load_setup_list_filtered_grid();
		return false;
	}
})
jQuery(document).on('click','#arm_member_setup_grid_filter_btn',function(e){
	arm_load_setup_list_filtered_grid();
})

function show_grid_loader(){
	jQuery('.dataTables_scroll').hide();
	jQuery('.footer').hide();
	jQuery('.arm_loading_grid').show();
}

function arm_load_setup_list_grid(){
	var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing','armember-membership').' <span class="arm-black-350 arm_font_size_15">0</span> - <span class="arm-black-350 arm_font_size_15">0</span> of <span class="arm-black-350 arm_font_size_15">0</span> '.esc_html__('setups','armember-membership')); //phpcs:ignore?>';
	var __ARM_to = '-';
	var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_SETUPS = ' <?php echo addslashes( esc_html__( 'setups', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?> ';
	var __ARM_NO_FOUND = '<?php echo addslashes( esc_html__( 'No any membership setup found.', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching records found.', 'armember-membership' ) ); //phpcs:ignore ?>';

	var __ARM_PER_PAGE = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?>';
	var ajax_url = '<?php echo admin_url("admin-ajax.php"); //phpcs:ignore?>';
	var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
	var arm_width_pct = "25%";
	var arm_width_40_cols = 1;
	var arm_width_20_cols = [3];
	var search_term = jQuery('.arm_datatable_searchbox #armmanagesearch_new').val();	
	var db_search_term = (typeof search_term !== 'undefined' && search_term !== '') ? search_term : '';
	<?php if($ARMemberLite->is_arm_pro_active)
	{?>
		arm_width_pct = "20%";
	<?php if($arm_pay_per_post_feature->isPayPerPostFeature){
		?>
		var arm_width_40_cols = 2;
		var arm_width_20_cols = [1,3];
		<?php } else{?>
		var arm_width_40_cols = 1;
	    var arm_width_20_cols = [3];
	<?php }?>
	<?php }?>
	
	var table = jQuery('#armember_datatable').dataTable({
		"oLanguage": {
			"sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_SETUPS,
			"sInfoEmpty": __ARM_Showing_empty,
			"sLengthMenu": __ARM_PER_PAGE +"_MENU_",
			"sEmptyTable": __ARM_NO_FOUND,
			"sZeroRecords": __ARM_NO_MATCHING,
		},
		"bDestroy": true,
		"language":{
			"searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
			"search":"",
		},
		"bProcessing": false,
		"bServerSide": true,
		"sAjaxSource": ajax_url,
		"sServerMethod": "POST",
		"fnServerParams": function (aoData) {
			aoData.push({'name': 'action', 'value': 'arm_get_configure_setup_details'});
			aoData.push({'name': 'sSearch', 'value': db_search_term});
			aoData.push({'name': '_wpnonce', 'value': _wpnonce});
		},
		"bRetrieve": false,
		"sDom": '<"H"fr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth" : false,
		"aaSorting": [],
		"sScrollX":"100%",
		"bScrollCollapse": false,
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [0,1,2,3,4,5] },
			{ "sWidth": "15%", "aTargets": [0] },
			{ "sWidth": "30%", "aTargets": [arm_width_40_cols] },
			{ "sWidth": "15%", "aTargets": [2] },
			{ "sWidth": "15%", "aTargets": [arm_width_20_cols] },
			{ "sWidth": "15%", "aTargets": [4] }
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
			jQuery('.dataTables_scroll').show();
			jQuery('.footer').show();
			jQuery('.arm_loading_grid').hide();
			arm_show_data();	
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
			table.dataTable().fnAdjustColumnSizing(false);
		}
	});
	var filter_box = jQuery('#arm_filter_wrapper').html();
	jQuery('div#armember_datatable_filter').parent().append(filter_box);
	jQuery('div#armember_datatable_filter').hide();
	// jQuery('#arm_filter_wrapper').remove();
	if(typeof db_search_term != 'undefined'){
		jQuery('.arm_datatable_searchbox #armmanagesearch_new').val(db_search_term);
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
						<label><input type="text" placeholder="<?php esc_attr_e( 'Search Setup', 'armember-membership' ); ?>" id="armmanagesearch_new" value="<?php echo esc_attr($filter_search); ?>" tabindex="0"></label>
					</div>
				</div>
				<div class="arm_filter_child_row arm_margin_left_12">
					<div>
						<input type="button" class="armemailaddbtn arm_margin_left_12" id="arm_member_setup_grid_filter_btn" value="<?php esc_html_e('Apply','armember-membership');?>">
						<input type="button" class="arm_cancel_btn arm_margin_left_12 hidden_section" value="<?php esc_html_e('Clear','armember-membership');?>">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="wrap arm_page arm_membership_setup_main_wrapper">
	<div class="content_wrapper arm_membership_setup_container" id="content_wrapper">
		<div class="page_title">
			<?php esc_html_e( 'Configure Plan + Signup Page', 'armember-membership' ); ?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_new_setup_form_btn" href="javascript:void(0)"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/add_new_icon.svg"><span><?php esc_html_e( 'Add New Setup', 'armember-membership' ); ?></span></a>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="arm_solid_divider"></div>
		<form method="GET" id="subscription_setup_list_form" class="data_grid_list">
			<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->membership_setup); //phpcs:ignore ?>" />
			<input type="hidden" name="armaction" value="list" />
			<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
			echo $arm_loader; //phpcs:ignore ?></div>
			<div id="armmainformnewlist" class="arm_filter_grid_list_container">
				<table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable arm_on_display" id="armember_datatable" style="visibility: hidden;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Setup Name', 'armember-membership' ); ?></th>
							<?php if($ARMemberLite->is_arm_pro_active)
							{
								if( ( $arm_pay_per_post_feature->isPayPerPostFeature || is_plugin_active('armembergift/armembergift.php'))){?> 
								<th><?php esc_html_e('Setup Type','armember-membership');?></th>
								<?php }
							}?>
							<th><?php esc_html_e( 'Plans', 'armember-membership' ); ?></th>
							<th><?php esc_html_e( 'Shortcode', 'armember-membership' ); ?></th>
							<th><?php esc_html_e( 'Gateways', 'armember-membership' ); ?></th>
							<th><?php esc_html_e( 'Member Form', 'armember-membership' ); ?></th>
														
							<th data-key="armGridActionTD" class="armGridActionTD noVis"></th>
						</tr>
					</thead>
					
				</table>
				<div class="armclear"></div>
				<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e( 'Show / Hide columns', 'armember-membership' ); ?>"/>
				<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>"/>
				<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e( 'setups', 'armember-membership' ); ?>"/>
				<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e( 'Show', 'armember-membership' ); ?>"/>
				<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e( 'Showing', 'armember-membership' ); ?>"/>
				<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e( 'to', 'armember-membership' ); ?>"/>
				<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e( 'of', 'armember-membership' ); ?>"/>
				<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e( 'No matching setup found', 'armember-membership' ); ?>"/>
				<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e( 'No any membership setup found.', 'armember-membership' ); ?>"/>
				<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e( 'filtered from', 'armember-membership' ); ?>"/>
				<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e( 'total', 'armember-membership' ); ?>"/>
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</div>
			<div class="footer_grid"></div>
		</form>
		<div class="armclear"></div>
	</div>
</div>

<?php
    echo $ARMemberLite->arm_get_need_help_html_content('configure-membership-setup--list'); //phpcs:ignore
?>