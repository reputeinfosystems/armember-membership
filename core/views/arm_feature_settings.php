<?php 
global $wpdb, $ARMemberLite, $arm_slugs, $arm_social_feature,$myplugarr;
$ARMemberLite->arm_session_start();
$social_feature              = get_option( 'arm_is_social_feature' );
$user_private_content        = 0;
$social_login_feature        = 0;
$pro_ration_feature 	     = 0; 
$pausing_subscriptions_feature = 0; 
$drip_content_feature        = 0;
$opt_ins_feature             = 0;
$coupon_feature              = 0;
$buddypress_feature          = 0;
$invoice_tax_feature         = 0;
$multiple_membership_feature = 0;
$arm_is_mycred_active        = 0;
$woocommerce_feature         = 0;
$arm_pay_per_post            = 0;
$arm_admin_mycred_feature    = 0;
$plan_limit_feature 	     = 0;
$arm_api_service_feature     = 0;
$gutenberg_block_restriction_feature = get_option('arm_is_gutenberg_block_restriction_feature');
$beaver_builder_restriction_feature = get_option('arm_is_beaver_builder_restriction_feature');
$divi_builder_restriction_feature = get_option('arm_is_divi_builder_restriction_feature');
$wpbakery_page_builder_restriction_feature = get_option('arm_is_wpbakery_page_builder_restriction_feature');
$fusion_builder_restriction_feature = get_option('arm_is_fusion_builder_restriction_feature');
$oxygen_builder_restriction_feature = get_option('arm_is_oxygen_builder_restriction_feature');
$siteorigin_builder_restriction_feature = get_option('arm_is_siteorigin_builder_restriction_feature');
$bricks_builder_restriction_feature = get_option('arm_is_bricks_builder_restriction_feature');
if($ARMemberLite->is_arm_pro_active){
	$user_private_content = get_option('arm_is_user_private_content_feature');	
	$social_login_feature = get_option('arm_is_social_login_feature');
	$pro_ration_feature = get_option('arm_is_pro_ration_feature');
	$pausing_subscriptions_feature = get_option('arm_is_pausing_subscriptions_feature');
	$drip_content_feature = get_option('arm_is_drip_content_feature');
	$coupon_feature = get_option('arm_is_coupon_feature');
	$buddypress_feature = get_option('arm_is_buddypress_feature');
	$invoice_tax_feature = get_option('arm_is_invoice_tax_feature');
	$multiple_membership_feature = get_option('arm_is_multiple_membership_feature');
	$arm_admin_mycred_feature = get_option('arm_is_mycred_feature');
	$woocommerce_feature = get_option('arm_is_woocommerce_feature');
	$arm_pay_per_post = get_option('arm_is_pay_per_post_feature');
	$arm_api_service_feature = get_option('arm_is_api_service_feature');
	$plan_limit_feature = get_option('arm_is_plan_limit_feature');
}


$arm_is_pro_switch_class= '';
if($ARMemberLite->is_arm_pro_active)
{
	$arm_is_pro_switch_class= 'arm_pro_feature';
}


$featureActiveIcon = MEMBERSHIPLITE_IMAGES_URL . '/feature_active_icon.png';
if ( is_rtl() ) {
	$featureActiveIcon = MEMBERSHIPLITE_IMAGES_URL . '/feature_active_icon_rtl.png';
}
?>
<?php
$setact = 1;
if($ARMemberLite->is_arm_pro_active){
	$hostname = $_SERVER["SERVER_NAME"]; //phpcs:ignore
	global $arm_members_activity;
	$setact = 0;
	global $check_sorting;
	$setact = $arm_members_activity->$check_sorting();
}
?>
<style>
	.purchased_info{ color:#7cba6c; font-weight:bold; font-size: 15px; }
	#license_success{ color:#8ccf7a !important; }
	.arperrmessage{color:red;}
    #arfactlicenseform { border-radius:0px; text-align:center; width:570px; min-height:350px; height:auto; left:35%; border:none; background:#ffffff !important; padding:30px 20px; }
	#wpcontent{ background: #EEF2F8; }
	#arfactlicenseform .form-table th{ text-align:right; }
	#arfactlicenseform .form-table td{ text-align:left; }
	#license_error{ color:red;}
	.arfnewmodalclose { font-size: 15px; font-weight: bold; height: 19px; position: absolute; right: 3px; top:5px; width: 19px; cursor:pointer; color:#D1D6E5; }
	#licenseactivatedmessage { height:22px; color:#FFFFFF; font-size:17px; font-weight:bold; letter-spacing:0.5; margin-left:0px; display:block; border-radius:3px; -moz-border-radius:3px; -webkit-border-radius:3px; -o-border-radius:3px; padding:7px 5px 5px 0px; font-family:'open_sansregular', Arial, Helvetica, Verdana, sans-serif; background-color:#8ccf7a; margin-top:15px !important; margin-bottom:10px !important; text-align:center; }
	.red_remove_license_btn { -moz-box-sizing: content-box; background: #e95a5a;  border:none; box-shadow: 0 4px 0 0 #d23939; color: #FFFFFF !important; cursor: pointer; font-size: 16px !important; font-style: normal; font-weight: bold; height: 30px; min-width: 90px; width: auto; outline: none; padding: 0px 10px; text-shadow: none; text-transform: none; vertical-align:middle; text-align:center; margin-bottom:15px; }
    .red_remove_license_btn:hover { background: #d23939; box-shadow: 0 4px 0 0 #b83131; }
	.newform_modal_title { font-size:25px; line-height:25px; margin-bottom: 10px; }
	.newmodal_field_title { font-size: 16px; line-height: 16px; margin-bottom: 10px; }
	.page_title.arm_new_addon_page_design{ font-size: 28px; line-height: 48px; text-align: center; padding: 24px 0; }
    .page_title.arm_new_addon_page_design.arm_new_addon_page_design_other_sec{ padding-top: 35px !important; }
	body:has(.arm-lite-upgrade-pro-wrapper) .popup_wrapper.arm_addon_not_supoported_notice{ background-color: transparent !important; }
	.arm-lite-upgrade-pro-wrapper{ width: 816px; border-radius: 12px; background-color: #FFF; position: relative; text-align: center; top: 45px; }
	.arm-lite-upgrade-pro{ padding: 0 58px; }
	.arm-lite-upgrade-pro-header{ background-image: url(<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm-addon-popup-vactor.webp); width: 624px; height: 95px; background-repeat: no-repeat; margin: 0 auto; }
	.arm-lite-upgrade-pro-header-heding{ font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS"; font-size: 26px; font-weight: 600; line-height: 90px; text-align: center; color: #000000; padding: 22px 0; margin: 0 auto; }
	.arm-lite-upgrade-pro-hero-section{ margin-top: 35px; }
	.arm-lite-upgrade-pro-hero-heding{ font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS"; font-size: 22px; font-weight: 600; line-height: 64px; text-align: center; color: #1A2538; display: block; }
	.arm-lite-upgrade-pro-hero-content{ font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS"; font-size: 18px; font-weight: 400; line-height: 30px; text-align: center; color: #2F3F5C; margin-top: 15px; }
	.arm-lite-upgrade-pro-body-section{ margin: 40px auto 60px auto; }
	.arm-lite-upgrade-pro-body-heding{ font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS"; font-size: 26px; font-weight: 600; line-height: 24px; text-align: center; color: #005AEE; }
	.arm-lite-upgrade-pro-body-heding::after, .arm-lite-upgrade-pro-body-heding::before { content: ''; background-image: url(<?php echo MEMBERSHIPLITE_IMAGES_URL;//phpcs:ignore?>/cs-lifetime-family-plugin-star.webp); width: 22px; height: 22px; display: inline-block;
		position: relative; right: -10px; vertical-align: bottom; top: -4px; background-size: 100%; }
	.arm-lite-upgrade-pro-body-heding::before{ right: unset; left: -10px; }
	.arm-lite-upgrade-pro-body-fetur-list{ display: flex; margin-bottom: 32px; }
	.arm-lite-upgrade-pro-body-fetur-list:nth-child(2){ margin-top: 40px; }
	.arm-lite-upgrade-pro-body-fetur-item{ display: flex; flex-basis: 48%; }
	.arm-lite-upgrade-pro-body-fetur-item-title { max-width: 90%; word-break: break-word; margin-left: 12px; font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS"; font-size: 18px; font-weight: 400; line-height: 24px; color: #2F3F5C; text-align: left; }
	.arm-lite-upgrade-pro-body-fetur-icon{ background: url(<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/tick-green.webp); width: 26px; height: 24px; background-size: 100%; background-repeat: no-repeat; background-size: 100%; }
	.arm-addon-popup-upg-btn{ font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS"; font-size: 18px; font-weight: 600; line-height: 20px; color: #FFF; background-color: #005AEE; border: 1px solid #005AEE; border-radius: 8px; padding: 16px 44px; text-decoration: none; }
	.arm-addon-popup-upg-btn:hover{ color: #005AEE; background-color: #FFF; transition: background-color 0.2s ease-in-out; }
	.arm-lite-upgrade-pro-footer-wrapper{ background-color: #005AEE0A; padding: 40px 0; margin-top: 80px; }
	.arm-addon-popup-footer-btn{ border: 1.5px solid #005AEE; font-family: var(--arm-primary-font), sans-serif, "Trebuchet MS";; font-size: 16px; font-weight: 600; line-height: 20px; color: #005AEE; padding: 12px 29px; border-radius: 8px; margin-right: 21px; text-decoration: none; }
	.arm-addon-popup-footer-btn:hover{ background-color: #005AEE; color: #FFF; transition: background-color 0.2s ease-in-out; }
	.arm-addon-popup-footer-btn.other{ color: #F547AF; border-color: #F547AF; margin-right: 0; margin-left: 21px; }
	.arm-addon-popup-footer-btn.other:hover{ color: #FFF; background-color: #F547AF;  transition: background-color 0.2s ease-in-out; }
	.arm-lite-popup.popup_content_text{ padding: 0; }
	.arm-lite-popup.popup_content_text .arm_confirm_text { margin: 0 12px }
	.popup_content_text.arm_text_align_center.arm-lite-popup .notice.arm_admin_notice_shown{ display: none !important; }
	.arm-lite-upgrade-pro-spacer{ display: none; }
	.popup_wrapper.arm_addon_not_supoported_notice{ width: 800px; }
    @media only screen and (max-width: 1366px) {
		.arm-lite-upgrade-pro-header{ background-size: 100%; width: 575px; }
		.arm-lite-upgrade-pro-header-heding{ line-height: 80px; }
		.arm-lite-upgrade-pro-hero-section{ margin-top: 15px }
		.arm-lite-upgrade-pro-body-fetur-list{ margin-bottom: 24px }
		.arm-lite-upgrade-pro-footer-wrapper{ margin-top: 40px; }
	}
    @media only screen and (max-width: 1024px) {
        .arm_feature_settings_wrapper{ margin: 0 15px; }
        .wrap.arm_page:not(.arm_manage_form_main_wrapper){ padding: 25px 15px; }
        .page_title.arm_new_addon_page_design.arm_new_addon_page_design_other_sec{ font-size: 25px; padding-top: 26px !important; padding-bottom: 0; }
        .page_title.arm_new_addon_page_design{ font-size: 25px; padding: 15px 0 10px 0; }
    }
	@media only screen and (max-width: 820px) {
		.popup_wrapper.arm_addon_not_supoported_notice { width: 726px; }
		.arm-lite-upgrade-pro { padding: 0 38px; }
		.arm-lite-upgrade-pro-wrapper{ width: 740px; }
		.arm-lite-upgrade-pro-hero-content{ font-size: 17px; }
	}
    @media only screen and (max-width: 576px) {
		.popup_wrapper.arm_addon_not_supoported_notice { width: 100%; max-width: 100%; left: 0 !important; }
        .arm_feature_settings_wrapper{ margin: 0 10px; }
        .page_title.arm_new_addon_page_design{ font-size: 22px; padding: 20px 0; }
        .page_title.arm_new_addon_page_design.arm_new_addon_page_design_other_sec{ padding-top: 20px !important; padding-bottom: 0; }
        .arm_feature_list .arm_feature_text { padding: 0 10px }
		.arm-lite-upgrade-pro-body-fetur-list{ display: block; margin-bottom: 0; text-align: center; }
		.arm-lite-upgrade-pro-wrapper{ width: 100%; border-radius: 0; top: unset; }
		.arm-lite-upgrade-pro-header{ background-size: 100%; width: auto; }
		.arm-lite-upgrade-pro-header-heding{ font-size: 20px; padding: 0; }
		.arm-lite-upgrade-pro-body-fetur-item{  margin: 12px; }
		.arm-lite-upgrade-pro-hero-heding{ line-height: 34px; }
		.arm-lite-upgrade-pro { padding: 0 5px; }
		.arm-addon-popup-upg-btn{ font-size: 16px; padding: 16px 18px; }
		.arm-addon-popup-footer-btn, .arm-addon-popup-footer-btn.other{ display: block; margin-right: unset; margin-left: unset; }
		.arm-addon-popup-footer-btn{ padding: 12px 34px; }
		.arm-addon-popup-footer-btn.other{ padding: 12px 29px; margin-top: 25px; }
		.arm-lite-upgrade-pro-hero-section{ margin-top: 0; }
		.arm-lite-upgrade-pro-spacer{ display: block; height: 20px; }
		.arm-lite-upgrade-pro-body-fetur-list:nth-child(2) { margin-top: 20px; }
		.arm-lite-upgrade-pro-footer-wrapper{ padding: 20px; margin-top: 50px; }
	}
	@media only screen and (max-width: 480px) {
		.arm-lite-upgrade-pro-header-heding{ line-height: 54px; font-size: 16px; }
		.arm-lite-upgrade-pro-hero-heding{ font-size: 18px; line-height: 26px; }
		.arm-lite-upgrade-pro-hero-content{ font-size: 16px; line-height: 24px; }
		.arm-lite-upgrade-pro-body-fetur-item-title{ font-size: 16px; }
		.arm-lite-upgrade-pro-body-fetur-icon{ width: 18px; height: 18px; }
		.arm-lite-upgrade-pro-body-fetur-item{ align-items: center; }
	}
</style>
<div class="wrap arm_page arm_feature_settings_main_wrapper">
    <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license'); //phpcs:ignore
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo esc_url($admin_css_url); ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_feature_settings_content" id="content_wrapper">
		<div class="page_title arm_new_addon_page_design"><?php esc_html_e( 'In-built Modules', 'armember-membership' ); ?></div>
		<div class="armclear"></div>
		<div class="arm_feature_settings_wrapper">            
			<div class="arm_feature_settings_container">
				<div class="arm_feature_list social_enable <?php echo ( $social_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Social Feature', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'With this feature, enable social activities like Member Directory/Public Profile, Social Profile Fields etc.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_congig_url = admin_url('admin.php?page=arm_profiles_directories');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'social',array(),$arm_addon_congig_url); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ( $social_feature == 1 ) ? 'hidden_section' : ''; ?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="social"><?php esc_html_e( 'Activate', 'armember-membership' ); ?></a>
								<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $social_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> " data-feature_val="0" data-feature="social"><?php esc_html_e('Deactivate','armember-membership');?></a><a href="<?php echo esc_url(admin_url('admin.php?page=arm_profiles_directories'));?>" class="arm_feature_configure_btn"><?php esc_html_e('Configure','armember-membership');?></a><span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>	
					</div>
					<a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/brief-of-social-features/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list pro_ration_enable <?php echo ( $pro_ration_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Pro-Rata', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Allows member to purchase membership plan through Pro-Rata..', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'pro_ration',array()); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $pro_ration_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="pro_ration"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
                            <svg class="arm_circular" viewBox="0 0 60 60">
                                <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                            </svg>
                        </span>
						</div>
						<?php } ?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/pro-rata/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>
				<div class="arm_feature_list drip_content_enable <?php echo ( $drip_content_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Drip Content', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Publish your site content based on different time intervals by enabling this feature.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_congig_url = admin_url('admin.php?page=arm_drip_rules');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'drip_content',array(),$arm_addon_congig_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $drip_content_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="drip_content"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
							</span>
						</div>
						<?php } ?>
					</div>
					<a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/enable-drip-content-for-your-site/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list social_login_enable <?php echo ( $social_login_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Social Connect', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Allow users to sign up / login with their social accounts by enabling this feature.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_congig_url = admin_url('admin.php?page=arm_general_settings&action=social_options');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'social_login',array(),$arm_addon_congig_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $social_login_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="social_login"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
							</span>
						</div>
						<?php }?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/basic-information-for-social-login/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list pay_per_post_enable <?php echo ( $arm_pay_per_post == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Pay Per Post', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'With this feature, you can sell post separately without creating plan(s).', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'pay_per_post',array(),''); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $arm_pay_per_post == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="pay_per_post"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
							</span>
						</div>
						<?php } ?>
					</div>
					<a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/pay-per-post/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list coupon_enable <?php echo ( $coupon_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Coupon', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Let users get benefit of discounts coupons while making payment with your site.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_congig_url = admin_url('admin.php?page=arm_coupon_management');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'coupon',array(),$arm_addon_congig_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $coupon_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="coupon"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
							</span>
						</div>
						<?php }?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/how-to-do-coupon-management/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list invoice_tax_enable <?php echo ( $invoice_tax_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Invoice and Tax', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Enable facility to send Invoice and apply Sales Tax on membership plans.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_congig_url = admin_url('admin.php?page=arm_general_settings');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'invoice_tax',array(),$arm_addon_congig_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $invoice_tax_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="invoice_tax"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
							</span>
						</div>
						<?php }?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/invoice-and-tax"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list user_private_content_enable <?php echo ( $user_private_content == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'User Private Content', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'With this feature, you can set different content for different user.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_congig_url = admin_url('admin.php?page=arm_manage_private_content');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'user_private_content',array(),$arm_addon_congig_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $user_private_content == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="user_private_content"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
                        </span>
						</div>
						<?php } ?>
					</div>
					<a class="arm_ref_info_links arm_feature_link" target="_blank" href="https://www.armemberplugin.com/documents/user-private-content/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list multiple_membership_enable <?php echo ( $multiple_membership_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Multiple Membership/Plans', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Allow members to subscribe multiple plans simultaneously.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'multiple_membership',array()); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $multiple_membership_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="multiple_membership"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
				
							<span class="arm_addon_loader">
								<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
								</svg>
                        </span>
						</div>
						<?php }?>
					</div>
						<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/single-vs-multiple-membership/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>
				<!-- START -->
				<div class="arm_feature_list plan_limit_enable <?php echo ( $plan_limit_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Membership Limit', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'With this feature, you can limit plan, Pay Per Post purchases for members.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'plan_limit',array()); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $plan_limit_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="plan_limit"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
				
							<span class="arm_addon_loader">
                            <svg class="arm_circular" viewBox="0 0 60 60">
                                <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                            </svg>
                        </span>
						</div>
						<?php }?>
					</div>
						<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/paid-membership-plan-payment-process/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>
				<div class="arm_feature_list api_service_enable <?php echo ( $arm_api_service_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'API Services', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'With this feature, you will able to use Membership API Services for your Application.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_config_url = admin_url('admin.php?page=arm_general_settings&action=api_service_feature');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'api_service',array(),$arm_addon_config_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $arm_api_service_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="api_service"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
				
							<span class="arm_addon_loader">
                            <svg class="arm_circular" viewBox="0 0 60 60">
                                <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                            </svg>
                        </span>
						</div>
						<?php }?>
					</div>
						<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/paid-membership-plan-payment-process/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>
				<!-- END -->

				<div class="arm_feature_list buddypress_enable <?php echo ( $buddypress_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Buddypress/Buddyboss Integration', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Integrate BuddyPress/Buddyboss with ARMember.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_config_url = admin_url('admin.php?page=arm_general_settings&action=buddypress_options');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'buddypress',array(),$arm_addon_config_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $buddypress_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="buddypress"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
                            <svg class="arm_circular" viewBox="0 0 60 60">
                                <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                            </svg>
                        </span>
						</div>
						<?php }?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/buddypress-support/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list woocommerce_enable <?php echo ( $woocommerce_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'Woocommerce Integration', 'armember-membership' ); ?></div>
						<div class="arm_feature_text" style=" min-height: 0;"><?php esc_html_e( 'Integrate Woocommerce with ARMember.', 'armember-membership' ); ?></div>
						<div class="arm_feature_text arm_woocommerce_feature_version_required_notice"><?php esc_html_e( 'Minimum Required Woocommerce Version: 3.0.2', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'woocommerce',array()); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $woocommerce_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="woocommerce"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<!--<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>-->
							<span class="arm_addon_loader">
                            <svg class="arm_circular" viewBox="0 0 60 60">
                                <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                            </svg>
                        </span>
						</div>
						<?php }?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/woocommerce-support/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list mycred_enable <?php echo ( $arm_admin_mycred_feature == 1 ) ? 'active' : ''; ?>">
					<div class="arm_feature_icon"></div>
					<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
					<div class="arm_feature_content">
						<div class="arm_feature_title"><?php esc_html_e( 'myCRED Integration', 'armember-membership' ); ?></div>
						<div class="arm_feature_text"><?php esc_html_e( 'Integrate myCRED adaptive points management system with ARMember.', 'armember-membership' ); ?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							$arm_addon_config_url = admin_url('admin.php?page=arm_general_settings&action=payment_options#mycred');
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'mycred',array(),$arm_addon_config_url); //phpcs:ignore
						}
						else
						{ ?>
						<div class="arm_feature_button_activate_wrapper <?php echo ( $arm_admin_mycred_feature == 1 ) ? 'hidden_section' : ''; ?>">
							<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="mycred"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
							<a href="javascript:void(0)" class="arm_feature_configure_btn"><?php esc_html_e( 'Configure', 'armember-membership' ); ?></a>
							<span class="arm_addon_loader">
                            <svg class="arm_circular" viewBox="0 0 60 60">
                                <circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
                            </svg>
                        </span>
						</div>
						<?php }?>
					</div>
					<a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/mycred-integration/"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
				</div>

				<div class="arm_feature_list gutenberg_block_restriction_enable <?php echo ($gutenberg_block_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('Gutenberg Block Restriction','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows facility to set the Access for Gutenberg Blocks per Membership Plan or Logged in member.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'gutenberg_block_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($gutenberg_block_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="gutenberg_block_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
									<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $gutenberg_block_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="gutenberg_block_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/gutenberg-block-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>

				<div class="arm_feature_list beaver_builder_restriction_enable <?php echo ($beaver_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('Beaver Builder Restriction','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows Beaver Builder widgets to restrict based on Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'beaver_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($beaver_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="beaver_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $beaver_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="beaver_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/beaver-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>

				<div class="arm_feature_list divi_builder_restriction_enable <?php echo ($divi_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('Divi Builder Restriction','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows facility to set the access for Divi Builder content Like Section and Row per Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'divi_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($divi_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="divi_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $divi_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="divi_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/divi-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>

				<div class="arm_feature_list wpbakery_page_builder_restriction_enable <?php echo ($wpbakery_page_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('WPBakery Page Builder Restriction','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows to set restrict content on WPBakery Elements per Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'wpbakery_page_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($wpbakery_page_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="wpbakery_page_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $wpbakery_page_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="wpbakery_page_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/wpbakery-page-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>

				<div class="arm_feature_list fusion_builder_restriction_enable <?php echo ($fusion_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('Fusion Builder Integration','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows to set restrict content on Fusion Builder Containers & Columns per Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'fusion_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($fusion_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="fusion_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $fusion_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="fusion_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/fusion-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>

				<div class="arm_feature_list oxygen_builder_restriction_enable <?php echo ($oxygen_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('Oxygen Builder Integration','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows to set restrict content on Oxygen Builder Container, Section, Column and Components per Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'oxygen_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($oxygen_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="oxygen_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $oxygen_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="oxygen_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/oxygen-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>

				<!-- Start Armember Sietorigin Builder Integration -->
                <div class="arm_feature_list siteorigin_builder_restriction_enable <?php echo ($siteorigin_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('SiteOrigin Builder Integration','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows to set restrict content on SiteOrigin Builder Row and Column per Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'siteorigin_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($siteorigin_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="siteorigin_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $siteorigin_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="siteorigin_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/siteorigin-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>
                <!-- End Armember Sietorigin Builder Integration -->

				<!-- Start Armember Bricks Builder Integration -->
				<div class="arm_feature_list bricks_builder_restriction_enable <?php echo ($bricks_builder_restriction_feature == 1) ? 'active':'';?>">
                    <div class="arm_feature_icon"></div>
                    <div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
                    <div class="arm_feature_content">
                        <div class="arm_feature_title"><?php esc_html_e('Bricks Builder Integration','armember-membership'); ?></div>
                        <div class="arm_feature_text"><?php esc_html_e("Allows to set restrict content on Bricks Builder Elements per Membership Plan.", 'armember-membership');?></div>
						<?php if($ARMemberLite->is_arm_pro_active) {
							$arm_addon_btn = '';
							echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,'bricks_builder_restriction',array()); //phpcs:ignore
						}
						else
						{ ?>
							<div class="arm_feature_button_activate_wrapper <?php echo ($bricks_builder_restriction_feature == 1) ? 'hidden_section':'';?>">
								<a href="javascript:void(0)" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?>" data-feature_val="1" data-feature="bricks_builder_restriction"><?php esc_html_e('Activate','armember-membership'); ?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
							<div class="arm_feature_button_deactivate_wrapper <?php echo ( $bricks_builder_restriction_feature == 1 ) ? '' : 'hidden_section'; ?>">
								<a href="javascript:void(0)" class="arm_feature_deactivate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_no_config_feature_btn" data-feature_val="0" data-feature="bricks_builder_restriction"><?php esc_html_e('Deactivate','armember-membership');?></a>
								<span class="arm_addon_loader">
									<svg class="arm_circular" viewBox="0 0 60 60">
										<circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle>
									</svg>
								</span>
							</div>
						<?php }?>
                    </div>
                    <a class="arm_ref_info_links arm_feature_link arm_advanced_link" target="_blank" href="https://www.armemberplugin.com/documents/bricks-builder-support/"><?php esc_html_e('More Info', 'armember-membership'); ?></a>
                </div>
                <!-- End Armember Bricks Builder Integration -->
				
				<?php echo do_action( 'arm_add_new_custom_add_on' ); //phpcs:ignore ?>
			</div>
			
			<div class="arm_feature_settings_container arm_margin_top_30">
				<?php
				global $arm_social_feature;
				global $arm_lite_version;
				$addon_resp = '';
				$addon_resp = $arm_social_feature->addons_page();

				$plugins           = get_plugins();
				$installed_plugins = array();
				foreach ( $plugins as $key => $plugin ) {
					$is_active                            = is_plugin_active( $key );
					$installed_plugin                     = array(
						'plugin'    => $key,
						'name'      => $plugin['Name'],
						'is_active' => $is_active,
					);
					$installed_plugin['activation_url']   = $is_active ? '' : wp_nonce_url( "plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}" );
					$installed_plugin['deactivation_url'] = ! $is_active ? '' : wp_nonce_url( "plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}" );

					$installed_plugins[] = $installed_plugin;
				}

				if ($addon_resp != "") {
					$resp = explode("|^^|", $addon_resp);
		
					if ($resp[0] == 1) {
					$myplugarr = array();
					$myplugarr = unserialize(base64_decode($resp[1]));
		
		
					$is_active = 0;
					if (is_array($myplugarr) && count($myplugarr) > 0) {
						?><?php
						foreach ($myplugarr as $key => $plug_1) {
							if($key == 'feature' ) {
							?>
								<div class="page_title arm_new_addon_page_design arm_new_addon_page_design_other_sec"><?php esc_html_e('Additional Functionality Addon', 'armember-membership' ); ?></div>
							<?php } 
							if($key == 'payment_gateways' ) { ?>
								<div class="page_title arm_new_addon_page_design arm_new_addon_page_design_other_sec"><?php esc_html_e('Payment Gateways Addons', 'armember-membership' ); ?></div>
							<?php }  
							if($key == 'integrations' ) { ?>
								<div class="page_title arm_new_addon_page_design arm_new_addon_page_design_other_sec"><?php esc_html_e('Third-Party Integrations', 'armember-membership' ); ?></div>
							<?php }  ?>
							<div class="arm_feature_settings_container arm_margin_top_30 arm_margin_bottom_25">
							<?php 
							foreach ($plug_1 as $key_1 => $plug) {
							
								$is_active_plugin = is_plugin_active($plug['plugin_installer']);
								
								$is_config = ( isset( $plug['display_config'] ) && 'yes' == $plug['display_config'] ) ? true : false;
								$config_url = isset( $plug['config_args'] ) ? admin_url( $plug['config_args'] ) : '';
								?>
								

								<div class="arm_feature_list <?php echo esc_attr($plug['short_name']); ?>_enable <?php echo ($is_active_plugin == 1) ? 'active' : ''; ?>">
									<div class="arm_feature_icon" style="background-image:url(<?php echo esc_attr($plug['icon']); ?>);"></div>
									<div class="arm_feature_active_icon"><div class="arm_check_mark"></div></div>
									<div class="arm_feature_content">
										<div class="arm_feature_title"><?php echo esc_html($plug['full_name']); ?></div>
										<div class="arm_feature_text"><?php echo esc_html($plug['description']); ?></div>
										<?php if($ARMemberLite->is_arm_pro_active) {
											$arm_addon_btn = '';
											echo apply_filters('arm_addon_activate_button_section',$arm_addon_btn,$plug['short_name'],$plug); //phpcs:ignore

										}
										else
										{ 
											?>
										<div class="arm_feature_button_activate_wrapper <?php echo ( $is_active_plugin == 1 ) ? 'hidden_section' : ''; ?>">
											<a href="javascript:void(0)" style="padding:0 15px !important;" class="arm_feature_activate_btn arm_feature_settings_switch <?php echo esc_attr($arm_is_pro_switch_class);?> arm_feature_activation_license" data-feature_val="1" data-feature="<?php echo esc_attr($plug['short_name']); ?>"><?php esc_html_e( 'Upgrade Pro', 'armember-membership' ); ?></a>
											
										</div>
										<?php }?>
										
										
									</div>
									<a class="arm_ref_info_links arm_feature_link" target="_blank" href="<?php echo esc_url($plug['detail_url']); ?>"><?php esc_html_e( 'More Info', 'armember-membership' ); ?></a>
								</div>



								
									
									
									
								<?php
							}
		
						}
					?>
						</div>
					<?php
					}
					}
					else if(!empty($resp[1])) {
						echo $resp[1]; //phpcs:ignore
					}
				}


				?>
		<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</div>
		</div>
		<div class="armclear"></div>
	</div>
</div>

<?php
$addon_content                   = '<span class="arm_confirm_text">' . esc_html__( 'You need to have ARMember version 1.6 OR higher to install this addon.', 'armember-membership' ) . '</span>';
		$addon_content          .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$addon_content_popup_arg = array(
			'id'             => 'addon_message',
			'class'          => 'adddon_message',
			'title'          => esc_html__( 'Confirmation', 'armember-membership' ),
			'content'        => $addon_content,
			'button_id'      => 'addon_ok_btn',
			'button_onclick' => 'addon_message();',
		);
		echo $arm_global_settings->arm_get_bpopup_html( $addon_content_popup_arg ); //phpcs:ignore



		// $addon_not_supported_content = '<span class="arm_confirm_text ">' . esc_html__( 'This feature is available only in Pro version.', 'armember-membership' ) . '</span>';
		$popup 	= '';
		if(!$ARMemberLite->is_arm_pro_active)
		{
			$upgrade_to_pro = $arm_social_feature->upgrade_to_pro_content();
			if( !empty($upgrade_to_pro) )
			{
				$addon_not_supported_content = $upgrade_to_pro;
			}
			else
			{
				$addon_not_supported_content = '<span class="arm_confirm_text "> <div class="arm-lite-upgrade-pro">
				<div class="arm-lite-upgrade-pro-header">
					<span class="arm-lite-upgrade-pro-header-heding">Unlock the Powerful Pro Features</span>
				</div>
				<div class="arm-lite-upgrade-pro-hero-section">
					<span class="arm-lite-upgrade-pro-hero-heding">Unlock the Full Potential of Your Membership Business!</span>
					<span class="arm-lite-upgrade-pro-spacer"></span>
					<span  class="arm-lite-upgrade-pro-hero-content">Effortlessly manage your memberships, simplify every process, and unlock the potential to grow your membership business with powerful features.</span>
				</div>
				<div class="arm-lite-upgrade-pro-body-section">
					<span class="arm-lite-upgrade-pro-body-heding">Amazing Features</span>
					<div class="arm-lite-upgrade-pro-body-fetur-list">
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">Pay Per Post (Paid Post)</div>
						</div>
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">Prorating Memberships</div>
						</div>
					</div>
					<div class="arm-lite-upgrade-pro-body-fetur-list">
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">17+ Payment Gateways</div>
						</div>
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">Advanced Content Restriction</div>
						</div>
					</div>
					<div class="arm-lite-upgrade-pro-body-fetur-list">
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">Advanced Form Builder</div>
						</div>
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">53+ Inbuilt Addons Included</div>
						</div>
					</div>
					<div class="arm-lite-upgrade-pro-body-fetur-list">
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">Drip Content Feature</div>
						</div>
						<div class="arm-lite-upgrade-pro-body-fetur-item">
							<div class="arm-lite-upgrade-pro-body-fetur-icon"></div>
							<div class="arm-lite-upgrade-pro-body-fetur-item-title">Sell Online Courses Like LMS</div>
						</div>
					</div>
				</div>
	
				<a href="https://www.armemberplugin.com/pricing/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Pro" class="arm-addon-popup-upg-btn" target="_blank">Upgrade to ARMember Pro Now</a>
				</div>
	
				<div class="arm-lite-upgrade-pro-footer-wrapper">
					<a href="https://www.armemberplugin.com/comparison-of-armember-lite-vs-armember-premium/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Pro" class="arm-addon-popup-footer-btn" target="_blank">Compare Lite vs Pro</a>
					<a href="https://www.armemberplugin.com/memberpress-vs-paid-membership-pro-vs-s2-member/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Pro" class="arm-addon-popup-footer-btn other" target="_blank">ARMember vs Others</a>
				</div> </span>';
			}
			$popup 	= '<div id="arm_addon_not_supoported_notice" class="popup_wrapper arm_addon_not_supoported_notice"><div class="popup_wrapper_inner arm-lite-upgrade-pro-wrapper">' ;
	
				$popup .= '<div class="popup_content_text arm_text_align_center arm-lite-popup">' . $addon_not_supported_content . '</div>';
				$popup .= '<div class="armclear"></div>';
				$popup .= '<div class="armclear"></div>';
				$popup .= '</div></div>';
	
	
		}
		echo $popup //phpcs:ignore
		?>

<div id="arfactnotcompatible" style="display:none; background:white; padding:15px; border-radius:3px; width:400px; height:100px;">
		
		<div class="arfactnotcompatiblemodalclose" style="float:right;text-align:right;cursor:pointer; position:absolute;right:10px; " onclick="javascript:return false;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/close-button.png'; //phpcs:ignore ?>" align="absmiddle" /></div>
		
	   <table class="form-table">
			<tr class="form-field">
				<th class="arm-form-table-label arm_font_size_16">You need to have ARMember version 1.6 OR higher to install this addon.</th>
			</tr>				
		</table>
</div>
<?php if($ARMemberLite->is_arm_pro_active){
	$arm_lincense_activate_form = '';
	echo apply_filters('arm_addon_activate_license_form',$arm_lincense_activate_form); //phpcs:ignore
}?>
<script type="text/javascript">
	var ADDON_NOT_COMPATIBLE_MESSAGE = "<?php esc_html_e( 'This Addon is not compatible with current ARMember version. Please update ARMember to latest version.', 'armember-membership' ); ?>";
	<?php if ( ! empty( $_REQUEST['arm_activate_social_feature'] ) ) { //phpcs:ignore?>
		armToast("<?php esc_html_e( 'Please activate the \"Social Feature\" module to make this feature work.', 'armember-membership' ); ?>", 'error', 5000, false);
	<?php } ?>
	<?php if($ARMemberLite->is_arm_pro_active){
		?>
	<?php 
			if(!empty($_REQUEST['arm_activate_social_feature'])) //phpcs:ignore
			{
		?>
				armToast("<?php esc_html_e('Please activate the \"Social Feature\" module to make this feature work.','armember-membership'); ?>", 'error', 5000, false);
		<?php 
			}
			else if(!empty($_REQUEST['arm_activate_drip_feature'])) //phpcs:ignore
			{
		?>
				armToast("<?php esc_html_e('Please activate the \"Drip Content\" module to make this feature work.','armember-membership'); ?>", 'error', 5000, false);
		<?php 
			}
			else if(!empty($_REQUEST['arm_activate_private_content_feature'])) //phpcs:ignore
			{
		?>
				armToast("<?php esc_html_e('Please activate the \"User Private Content\" module to make this feature work.','armember-membership'); ?>", 'error', 5000, false);
		<?php
			}
			else if(!empty($_REQUEST['arm_activate_coupon_feature'])) //phpcs:ignore
			{
		?>
				armToast("<?php esc_html_e('Please activate the \"Coupon\" module to make this feature work.','armember-membership'); ?>", 'error', 5000, false);
		<?php
			}
			else if(!empty($_REQUEST['arm_activate_pay_per_pst_feature'])) //phpcs:ignore
			{
		?>
				armToast("<?php esc_html_e('Please activate the \"Pay Per Post\" module to make this feature work.','armember-membership'); ?>", 'error', 5000, false);
		<?php
			}
		?>
	<?php		
	}?>
	</script>
	
<?php
$_SESSION['arm_member_addon'] = $myplugarr;
