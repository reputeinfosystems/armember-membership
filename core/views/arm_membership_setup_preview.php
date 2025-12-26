<?php

global $ARMemberLite, $arm_lite_ajaxurl, $arm_membership_setup, $ARMemberLiteAllowedHTMLTagsArray;
$setupData = isset( $_REQUEST['setup_data'] ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_REQUEST['setup_data'] )  : array(); //phpcs:ignore

$setup_plans = explode(',',$setupData['setup_modules']['modules']['plans'][0]);
$setupData['setup_modules']['modules']['plans'] = $setup_plans;

$setupData['setup_name'] = ( ! empty( $setupData['setup_name'] ) ) ? wp_kses($setupData['setup_name'], $ARMemberLiteAllowedHTMLTagsArray ) : esc_html__( 'Untitled Setup', 'armember-membership' );

$setupData['setup_labels']['button_labels']['submit'] = wp_kses($setupData['setup_labels']['button_labels']['submit'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['payment_section_title'] = wp_kses($setupData['setup_labels']['payment_section_title'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['payment_gateway_labels']['paypal'] = wp_kses($setupData['setup_labels']['payment_gateway_labels']['paypal'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['payment_gateway_labels']['bank_transfer'] = wp_kses($setupData['setup_labels']['payment_gateway_labels']['bank_transfer'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['payment_mode_selection'] = wp_kses($setupData['setup_labels']['payment_mode_selection'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['automatic_subscription'] = wp_kses($setupData['setup_labels']['automatic_subscription'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['semi_automatic_subscription'] = wp_kses($setupData['setup_labels']['semi_automatic_subscription'], $ARMemberLiteAllowedHTMLTagsArray);
$setupData['setup_labels']['summary_text'] = wp_kses($setupData['setup_labels']['summary_text'], $ARMemberLiteAllowedHTMLTagsArray);

$setupData = maybe_serialize($setupData);


$ARMemberLite->set_front_js(2);
$ARMemberLite->set_front_css( 2 );
$ARMemberLite->enqueue_angular_script();
$ARMemberLite->set_global_javascript_variables();

wp_print_styles( 'arm_lite_front_css' );
wp_print_styles( 'arm_form_style_css' );
wp_print_styles( 'arm_fontawesome_css' );
wp_print_styles( 'arm_bootstrap_all_css' );

wp_print_styles( 'arm_front_components_base-controls' );
wp_print_styles( 'arm_front_components_form-style_base' );
wp_print_styles( 'arm_front_components_form-style__arm-style-default' );

// wp_print_styles('arm-font-awesome');

wp_print_styles( 'arm_front_components_form-style__arm-style-material' );
wp_print_styles( 'arm_front_components_form-style__arm-style-outline-material' );
wp_print_styles( 'arm_front_components_form-style__arm-style-rounded' );

wp_print_styles( 'arm_front_component_css' );
wp_print_styles( 'arm_custom_component_css' );
?>
<script type='text/javascript'>
/* <![CDATA[ */
var ajaxurl = "<?php echo $arm_lite_ajaxurl; //phpcs:ignore ?>";
var armurl = "<?php echo MEMBERSHIPLITE_URL; //phpcs:ignore ?>";
var armviewurl = "<?php echo MEMBERSHIPLITE_VIEWS_URL; //phpcs:ignore ?>";
var imageurl = "<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>";
/* ]]> */
</script>
<?php
wp_print_scripts( 'wp-hooks' );
wp_print_scripts( 'jquery' );
wp_print_scripts( 'arm_common_js' );
wp_print_scripts( 'arm_admin_file_upload_js' );
wp_print_scripts( 'arm_bootstrap_js' );
wp_print_scripts( 'arm_bootstrap_datepicker_with_locale' );
?>

<!--* Angular CSS & JS *-->
<?php
wp_print_styles( 'arm_angular_material_css' );

wp_print_scripts( 'arm_angular_with_material' );
wp_print_scripts( 'arm_jquery_validation' );
wp_print_scripts( 'arm_form_validation' );
?>
<style type="text/css">
	body{
		padding:0;
		margin:0;
	}
	.arm_setup_form_container{
		height: 100% !important;
		overflow-x: hidden;
		overflow-y: auto;
		padding: 10px 8% 40px;
		box-sizing: border-box;
	}
	.arm_setup_form_container form{
		margin: 0 auto;
	}
</style>
<?php
echo $arm_membership_setup->arm_setup_shortcode_func(  array( 'preview'    => 'true', 'setup_data' => $setupData, ) ); //phpcs:ignore
