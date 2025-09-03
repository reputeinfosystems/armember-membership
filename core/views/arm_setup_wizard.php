<?php 
global $arm_lite_members_activity,$check_sorting,$ARMemberLite;
$arm_get_started_data = wp_remote_get('https://www.armemberplugin.com/?arm_get_started_wizard=1');
$arm_show_default_content = 1;
if(!is_wp_error($arm_get_started_data))
{
	$arm_get_started_data = json_decode($arm_get_started_data['body']);
	if(!empty($arm_get_started_data->content))
	{
		echo urldecode($arm_get_started_data->content); //phpcs:ignore
		$arm_show_default_content = 0;
	}
}

if(!empty($arm_show_default_content))
{ //phpcs:ignore
?>
	<div class="arm-wizard-setup-container arm-ws-is-landingpage">
		<a href="https://www.armemberplugin.com" target="_blank" class="arm-lp__logo"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-logo-primary.png' //phpcs:ignore?>" alt="ARMember Logo"></a>
		<div class="arm-lp-hero-section">
			<div class="arm-hs__left">
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-hero-img.png' //phpcs:ignore ?>" alt="ARMember Logo">
			</div>
			<div class="arm-hs__right">
			<h2><?php echo esc_html__('Welcome','armember-membership')?> & <strong><?php echo esc_html__('Thank You','armember-membership');?> </strong><?php echo esc_html__('for Choosing Us!','armember-membership'); ?></h2>
			<p><?php esc_html_e('ARMember setup is fast and easy. Click below, and we’ll walk you through the quick initial process. And don’t worry. You can go back and change anything you do - at anytime.','armember-membership'); ?></p>
				<button class="arm-wsc-btn arm-wsc-btn--primary arm_next_wizard_step">
				<?php esc_html_e('Getting Started','armember-membership'); ?>
					<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-long-right-arrow-icon.png' //phpcs:ignore ?>" alt="ARMember">
				</button>
			</div>
		</div>
		<?php /*
		<div class="arm-hs-cta-options-sec">
			<div class="arm-cos__row">
				<div class="arm-cos__col">
					<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-lifetime-icon.png' ?>" alt="ARMember">
				<p><?php esc_html_e('Lifetime Free Update','armember-membership'); ?></p>
				</div>
				<div class="arm-cos__col">
					<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-happy-simley-icon.png' ?>" alt="ARMember">
				<p><?php esc_html_e('12K+ Happy Customers','armember-membership'); ?></p>				
				</div>
				<div class="arm-cos__col">
					<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-money-icon.png' ?>" alt="ARMember">
				<p><?php esc_html_e('Pay Once, No Monthly Fees','armember-membership'); ?></p>				
				</div>
			</div>
		</div>
		*/ ?>
		<div class="arm-hs-cta-options-sec arm-ws-get-start-but-sec">
			<button class="arm-wsc-btn arm-wsc-btn--primary arm_next_wizard_step">
			<?php esc_html_e('Getting Started With Setup','armember-membership'); ?>
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-long-right-arrow-icon.png' //phpcs:ignore ?>" alt="ARMember">
			</button>
		</div>
	</div>
<?php 
	}
?>
<input type="hidden" id="total_completed_page" value="0">
<form class="arm_admin_form arm_setup_configuration_form" method="POST" novalidate="novalidate">
<?php $arm_sub_counter = 1;
if($ARMemberLite->is_arm_pro_active)
{
	$arm_sub_counter = $arm_sub_counter + 1;
}
$arm_setup_wizard_license_section = '';
echo apply_filters('arm_setup_wizard_license_section',$arm_setup_wizard_license_section); //phpcs:ignore

?>
<div class="arm-wizard-setup-container arm-ws-is-gen-option-page arm_setup_wizard_page_<?php echo esc_attr($arm_sub_counter);?> " id="arm_setup_wizard_page_<?php echo esc_attr($arm_sub_counter);?>">
	<div class="arm-ws-account-setup">
		<div class="arm-ws-acco-logo">
			<a href="https://www.armemberplugin.com/" target="_blank">
				<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-logo-icon.webp' //phpcs:ignore ?>" alt="ARMember">
			</a>
		</div>
		<div class="arm-ws-acc-content">
			<h2 class="arm-ws-acc-heding"><?php esc_html_e('Account Setup','armember-membership');?></h2>
			<p class="arm-ws-acc-disc"><?php esc_html_e('Complete simple steps to get started.','armember-membership');?></p>
		</div>
	</div>
	
	<div class="arm-ws-steps-belt">
		<?php $arm_counter = 1;
		if($ARMemberLite->is_arm_pro_active)
		{
			$arm_counter = $arm_counter + 1;
		}
		
		$arm_pro_lincense_menu_item = '';
		echo apply_filters('arm_pro_lincense_menu_item',$arm_pro_lincense_menu_item); //phpcs:ignore
		?>
		<div class="arm-ws-step-box arm-ws-step-activate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1; ?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 9.9627V7.9627L15.075 7.1627L14.5125 5.8002L15.6125 3.5002L14.2 2.0877L11.9375 3.2252L10.575 2.6627L9.7125 0.262695H7.7125L6.925 2.6877L5.5375 3.2502L3.2375 2.1502L1.825 3.5627L2.9625 5.8252L2.4 7.1877L0 8.0377V10.0252L2.425 10.8252L2.9875 12.1877L1.8875 14.4877L3.3 15.9002L5.5625 14.7627L6.925 15.3252L7.7875 17.7252H9.775L10.5625 15.3002L11.95 14.7377L14.25 15.8377L15.6625 14.4252L14.5125 12.1627L15.1 10.8002L17.5 9.93769V9.9627ZM8.75 12.7502C6.675 12.7502 5 11.0752 5 9.00019C5 6.9252 6.675 5.2502 8.75 5.2502C10.825 5.2502 12.5 6.9252 12.5 9.00019C12.5 11.0752 10.825 12.7502 8.75 12.7502Z" fi	ll="#637799"/>
					</svg>
				</span>
				
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('General Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1; ?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" style="display:block; margin: 0 auto;" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6154 0H4.38462C3.22174 0 2.1065 0.461949 1.28422 1.28422C0.461949 2.1065 0 3.22174 0 4.38462V13.6154C0 14.7783 0.461949 15.8935 1.28422 16.7158C2.1065 17.5381 3.22174 18 4.38462 18H13.6154C14.7783 18 15.8935 17.5381 16.7158 16.7158C17.5381 15.8935 18 14.7783 18 13.6154V4.38462C18 3.22174 17.5381 2.1065 16.7158 1.28422C15.8935 0.461949 14.7783 0 13.6154 0ZM7.64308 12.2585L5.79692 14.1046C5.66711 14.2343 5.49115 14.3071 5.30769 14.3071C5.12423 14.3071 4.94827 14.2343 4.81846 14.1046L3.89538 13.1815C3.82737 13.1182 3.77281 13.0417 3.73497 12.9568C3.69713 12.8719 3.67679 12.7802 3.67515 12.6873C3.67351 12.5943 3.69061 12.502 3.72543 12.4158C3.76024 12.3296 3.81207 12.2512 3.87781 12.1855C3.94355 12.1198 4.02186 12.0679 4.10806 12.0331C4.19427 11.9983 4.2866 11.9812 4.37956 11.9828C4.47252 11.9845 4.56419 12.0048 4.64911 12.0427C4.73403 12.0805 4.81047 12.1351 4.87385 12.2031L5.30769 12.6369L6.66462 11.28C6.79585 11.1577 6.96943 11.0911 7.14879 11.0943C7.32814 11.0975 7.49927 11.1701 7.62611 11.297C7.75295 11.4238 7.82561 11.5949 7.82878 11.7743C7.83194 11.9536 7.76537 12.1272 7.64308 12.2585ZM7.64308 4.87385L5.79692 6.72C5.66711 6.84965 5.49115 6.92247 5.30769 6.92247C5.12423 6.92247 4.94827 6.84965 4.81846 6.72L3.89538 5.79692C3.7731 5.66568 3.70652 5.4921 3.70968 5.31275C3.71285 5.13339 3.78551 4.96227 3.91235 4.83543C4.03919 4.70858 4.21032 4.63593 4.38967 4.63276C4.56903 4.6296 4.74261 4.69617 4.87385 4.81846L5.30769 5.25231L6.66462 3.89538C6.79585 3.7731 6.96943 3.70652 7.14879 3.70968C7.32814 3.71285 7.49927 3.78551 7.62611 3.91235C7.75295 4.03919 7.82561 4.21032 7.82878 4.38967C7.83194 4.56903 7.76537 4.74261 7.64308 4.87385ZM13.6154 13.3846H10.8462C10.6625 13.3846 10.4865 13.3117 10.3566 13.1818C10.2268 13.052 10.1538 12.8759 10.1538 12.6923C10.1538 12.5087 10.2268 12.3326 10.3566 12.2028C10.4865 12.0729 10.6625 12 10.8462 12H13.6154C13.799 12 13.9751 12.0729 14.1049 12.2028C14.2348 12.3326 14.3077 12.5087 14.3077 12.6923C14.3077 12.8759 14.2348 13.052 14.1049 13.1818C13.9751 13.3117 13.799 13.3846 13.6154 13.3846ZM13.6154 6H10.8462C10.6625 6 10.4865 5.92706 10.3566 5.79723C10.2268 5.66739 10.1538 5.4913 10.1538 5.30769C10.1538 5.12408 10.2268 4.94799 10.3566 4.81816C10.4865 4.68832 10.6625 4.61538 10.8462 4.61538H13.6154C13.799 4.61538 13.9751 4.68832 14.1049 4.81816C14.2348 4.94799 14.3077 5.12408 14.3077 5.30769C14.3077 5.4913 14.2348 5.66739 14.1049 5.79723C13.9751 5.92706 13.799 6 13.6154 6Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Membership Plan','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1; ?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.2009 13.2254C15.2009 14.3638 14.8307 15.3441 14.0904 16.1663C13.3501 16.9885 12.3884 17.4963 11.2054 17.6897V19.6429C11.2054 19.747 11.1719 19.8326 11.1049 19.8996C11.0379 19.9665 10.9524 20 10.8482 20H9.34152C9.24479 20 9.16109 19.9647 9.0904 19.894C9.01972 19.8233 8.98437 19.7396 8.98437 19.6429V17.6897C8.4933 17.6228 8.01897 17.5074 7.56138 17.3438C7.10379 17.1801 6.72619 17.0145 6.42857 16.8471C6.13095 16.6797 5.85565 16.5011 5.60268 16.3114C5.3497 16.1217 5.17671 15.9821 5.08371 15.8929C4.9907 15.8036 4.9256 15.7366 4.88839 15.692C4.7619 15.5357 4.75446 15.3832 4.86607 15.2344L6.01562 13.7277C6.06771 13.6533 6.15327 13.6086 6.27232 13.5938C6.38393 13.5789 6.47321 13.6124 6.54018 13.6942L6.5625 13.7165C7.40327 14.4531 8.30729 14.9182 9.27455 15.1116C9.54985 15.1711 9.82515 15.2009 10.1004 15.2009C10.7031 15.2009 11.2333 15.0409 11.6908 14.721C12.1484 14.401 12.3772 13.9472 12.3772 13.3594C12.3772 13.151 12.3214 12.9539 12.2098 12.7679C12.0982 12.5818 11.9736 12.4256 11.8359 12.2991C11.6983 12.1726 11.4807 12.0331 11.183 11.8806C10.8854 11.7281 10.6399 11.609 10.4464 11.5234C10.253 11.4379 9.95536 11.317 9.55357 11.1607C9.26339 11.0417 9.0346 10.9487 8.86719 10.8817C8.69978 10.8147 8.47098 10.7161 8.1808 10.5859C7.89062 10.4557 7.65811 10.3404 7.48326 10.24C7.30841 10.1395 7.09821 10.0074 6.85268 9.84375C6.60714 9.68006 6.40811 9.52195 6.25558 9.36942C6.10305 9.21689 5.94122 9.0346 5.77009 8.82255C5.59896 8.61049 5.46689 8.39472 5.37388 8.17522C5.28088 7.95573 5.20275 7.70833 5.13951 7.43304C5.07626 7.15774 5.04464 6.86756 5.04464 6.5625C5.04464 5.53571 5.40923 4.63542 6.13839 3.86161C6.86756 3.0878 7.81622 2.58929 8.98437 2.36607V0.357143C8.98437 0.260417 9.01972 0.176711 9.0904 0.106027C9.16109 0.0353423 9.24479 0 9.34152 0H10.8482C10.9524 0 11.0379 0.0334821 11.1049 0.100446C11.1719 0.167411 11.2054 0.252976 11.2054 0.357143V2.32143C11.6295 2.36607 12.0406 2.45164 12.4386 2.57812C12.8367 2.70461 13.1603 2.82924 13.4096 2.95201C13.6589 3.07478 13.8951 3.21429 14.1183 3.37054C14.3415 3.52679 14.4866 3.63467 14.5536 3.6942C14.6205 3.75372 14.6763 3.8058 14.721 3.85045C14.8475 3.98438 14.8661 4.12574 14.7768 4.27455L13.8728 5.90402C13.8132 6.01562 13.7277 6.07515 13.6161 6.08259C13.5119 6.10491 13.4115 6.07887 13.3147 6.00446C13.2924 5.98214 13.2385 5.9375 13.1529 5.87054C13.0673 5.80357 12.9222 5.70499 12.7176 5.57478C12.513 5.44457 12.2954 5.32552 12.0647 5.21763C11.8341 5.10975 11.5569 5.01302 11.2333 4.92746C10.9096 4.84189 10.5915 4.79911 10.279 4.79911C9.57217 4.79911 8.99554 4.95908 8.54911 5.27902C8.10268 5.59896 7.87946 6.0119 7.87946 6.51786C7.87946 6.71131 7.91109 6.88988 7.97433 7.05357C8.03757 7.21726 8.14732 7.37165 8.30357 7.51674C8.45982 7.66183 8.60677 7.7846 8.74442 7.88505C8.88207 7.98549 9.0904 8.10082 9.36942 8.23103C9.64844 8.36124 9.87351 8.46168 10.0446 8.53237C10.2158 8.60305 10.4762 8.70536 10.8259 8.83929C11.2202 8.9881 11.5216 9.10528 11.7299 9.19085C11.9382 9.27641 12.221 9.40662 12.5781 9.58147C12.9353 9.75632 13.2161 9.91443 13.4208 10.0558C13.6254 10.1972 13.856 10.3832 14.1127 10.6138C14.3694 10.8445 14.5666 11.0807 14.7042 11.3225C14.8419 11.5644 14.9591 11.849 15.0558 12.1763C15.1525 12.5037 15.2009 12.8534 15.2009 13.2254Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Payment Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1; ?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clip-path="url(#clip0_2972_7739)">
							<path d="M10.5416 8.33333C9.85408 6.39167 8.00825 5 5.83325 5C3.07075 5 0.833252 7.2375 0.833252 10C0.833252 12.7625 3.07075 15 5.83325 15C8.00825 15 9.85408 13.6083 10.5416 11.6667H14.1666V15H17.4999V11.6667H19.1666V8.33333H10.5416ZM5.83325 11.6667C4.91242 11.6667 4.16659 10.9208 4.16659 10C4.16659 9.07917 4.91242 8.33333 5.83325 8.33333C6.75409 8.33333 7.49992 9.07917 7.49992 10C7.49992 10.9208 6.75409 11.6667 5.83325 11.6667Z" fill="#637799"/>
						</g>
						<defs>
							<clipPath id="clip0_2972_7739">
								<rect width="20" height="20" fill="white"/>
							</clipPath>
						</defs>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Content Access','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1; ?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.00007 9.89051C2.04546 9.87493 2.03478 9.82954 2.04342 9.7985C2.35381 8.68316 3.66188 8.32595 4.48772 9.13906C5.26014 9.89957 6.022 10.6708 6.7885 11.4373C6.88833 11.5371 6.9896 11.6356 7.08678 11.7378C7.12965 11.783 7.16348 11.7963 7.20492 11.7392C7.22078 11.7174 7.24296 11.7001 7.26229 11.6808C9.32991 9.61319 11.3983 7.54638 13.4643 5.47712C13.7718 5.16918 14.1313 4.98564 14.5699 5.00088C15.1689 5.02171 15.614 5.30529 15.8609 5.85265C16.1076 6.39967 16.0201 6.91668 15.6468 7.38489C15.6044 7.43811 15.5553 7.48629 15.5071 7.53461C13.0821 9.95993 10.6572 12.3852 8.23172 14.81C7.73003 15.3115 7.08705 15.4322 6.49784 15.1393C6.34975 15.0656 6.21977 14.9677 6.10305 14.8509C4.88904 13.6366 3.67509 12.4222 2.46039 11.2086C2.2565 11.0048 2.1093 10.7699 2.0407 10.4876C2.03416 10.4609 2.0424 10.4202 2 10.4098C2.00007 10.2366 2.00007 10.0636 2.00007 9.89051Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Complete','armember-membership'); ?></div>
		</div>
	</div>
	
	<div class="arm-ws-license-part-content arm-ws-account-setup">
		<div class="arm-ws-lic-con-heding-area">
			<div class="arm-lic-con-page-count"><?php echo str_pad($arm_sub_counter,2,'0',STR_PAD_LEFT); $arm_sub_counter = $arm_sub_counter + 1; //phpcs:ignore?></div>
			<div class="arm-lic-con-page-info">
				<h2 class="arm-lic-page-heding"><?php esc_html_e('Enable General Options','armember-membership');?></h2>
				<p class="arm-lic-page-disc"><?php esc_html_e('Go to the General Settings menu from the admin interface. Here you will get number of tabs such as General, Payment Gateways, Page Setup etc..','armember-membership');?></p>
			</div>
		</div>
		<div class="arm-lic-page-content-wrapper">
			
			<div class="arm-lic-page-content">
				<label for="country" class="arm-lic-new-user-approv-dd"><?php esc_html_e('New User Approval','armember-membership');?> </label>
					<div class="arm_form_fields_wrapper">
						<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_countryoAbEb4GP0H"><input class="arm-selectpicker-input-control" type="text" id="arm_member_approval" name="user_register_verification" value="auto" data-msg-invalid="Please enter valid data.">
							<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
								<dt><span style=""></span><input type="text" style="display: none;" value="" class="arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
								<dd>
									<ul data-id="arm_member_approval" style="display: none;">
										<li data-label="Automatic approve" data-value="auto"><?php esc_html_e('Automatic approve','armember-membership');?></li>
										<li data-label="Email verified approve" data-value="email"><?php esc_html_e('Email verified approve','armember-membership');?></li>
										<li data-label="Manual approve by admin" data-value="manual"><?php esc_html_e('Manual approve by admin','armember-membership');?></li>
									</ul>
								</dd>
							</dl>
						</div>
					</div>
			</div>
			<div class="arm-lic-page-content">
				<label for="country" class="arm-lic-new-user-approv-dd"><?php esc_html_e('Default Currency','armember-membership');?> </label>
				<?php
				global $arm_global_settings,$arm_payment_gateways;
				$general_settings = $arm_global_settings->global_settings;
				$currencies = array_merge($arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['bank_transfer']);
				$currencies = apply_filters('arm_available_currencies', $currencies);
				$paymentcurrency = $general_settings['paymentcurrency'];
				$custom_currency_status = isset($general_settings['custom_currency']['status']) ? $general_settings['custom_currency']['status'] : '';
				$custom_currency_symbol = isset($general_settings['custom_currency']['symbol']) ? $general_settings['custom_currency']['symbol'] : '';
				$custom_currency_shortname = isset($general_settings['custom_currency']['shortname']) ? $general_settings['custom_currency']['shortname'] : '';
				$custom_currency_place = isset($general_settings['custom_currency']['place']) ? $general_settings['custom_currency']['place'] : '';
				?>
				<div class="arm_form_fields_wrapper">
					<div class="arm-df__form-field-wrap_select arm-df__form-field-wrap arm-controls " id="arm-df__form-field-wrap_countryoAbEb4GP0H">
					<input type='hidden' id='arm_payment_currency' name="paymentcurrency" value="<?php echo esc_attr($paymentcurrency);?>" />
						<dl class="arm_selectbox column_level_dd arm_default_currency_box <?php echo ($custom_currency_status == 1) ? 'disabled' : '';?>">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_payment_currency">
									<?php foreach ($currencies as $key => $value): ?>
									<li data-label="<?php echo esc_attr($key) . " ( ".esc_attr($value ).") ";?>" data-value="<?php echo esc_attr($key);?>"><?php echo esc_html($key) . " ( ".esc_html($value ).") ";?></li>
									<?php endforeach;?>
								</ul>
							</dd>
						</dl>
					</div>
				</div>
			</div>
			<?php $arm_setup_wizard_settings_section = '';
			echo apply_filters('arm_setup_wizard_settings_section',$arm_setup_wizard_settings_section); //phpcs:ignore
			?>
			<div class="arm-lic-page-content">
				<label class="arm_primary_status" for="arm_anonymous_data"><?php esc_html_e('Help us improve ARMember by sending anonymous usage stats','armember-membership');?></label>
				<div class="arm_position_relative">
					<div class="armswitch arm_member_status_div">
						<input type="checkbox" id="arm_anonymous_data" checked="checked" value="1" class="armswitch_input" name="arm_anonymous_data">
						<label for="arm_anonymous_data" class="armswitch_label arm_anonymous_data_label"></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="arm-ws-footer-wrapper">
	    <div class="arm-ws-footer-left">
		<a href="https://youtu.be/Yh_X00tHzMw" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-youtube-btn">
		        <img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-youtube-icon.webp' //phpcs:ignore ?>" alt="ARMember">
				<?php esc_html_e('Watch Tutorial','armember-membership'); ?>
		</a>
		</div>
		<div class="arm-ws-footer-right">
			<?php if($ARMemberLite->is_arm_pro_active)
			{?>
				<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-back-btn">
					<?php esc_html_e('Back','armember-membership'); ?>
				</button>
			<?php }?>
			<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-next-btn">
				<?php esc_html_e('Continue','armember-membership'); ?>
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-long-right-arrow-icon.png' //phpcs:ignore ?>" alt="ARMember">
			</button>
		</div>
	</div>
</div>

<div class="arm-wizard-setup-container arm-ws-is-lic-page arm_setup_wizard_page_<?php echo esc_attr($arm_sub_counter);?>" id="arm_setup_wizard_page_<?php echo esc_html($arm_sub_counter);?>">
	<div class="arm-ws-account-setup">
		<div class="arm-ws-acco-logo">
			<a href="https://www.armemberplugin.com/" target="_blank">
				<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-logo-icon.webp' //phpcs:ignore ?>" alt="ARMember">
			</a>
		</div>
		<div class="arm-ws-acc-content">
			<h2 class="arm-ws-acc-heding"><?php esc_html_e('Account Setup','armember-membership');?></h2>
			<p class="arm-ws-acc-disc"><?php esc_html_e('Complete simple steps to get started.','armember-membership');?></p>
		</div>
	</div>
	
	<div class="arm-ws-steps-belt">
		<?php $arm_counter = 1;
		if($ARMemberLite->is_arm_pro_active)
		{
			$arm_counter = $arm_counter + 1;
		}
		$arm_pro_lincense_menu_item = '';
		echo apply_filters('arm_pro_lincense_menu_item',$arm_pro_lincense_menu_item); //phpcs:ignore
		?>	
		<div class="arm-ws-step-box arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 9.9627V7.9627L15.075 7.1627L14.5125 5.8002L15.6125 3.5002L14.2 2.0877L11.9375 3.2252L10.575 2.6627L9.7125 0.262695H7.7125L6.925 2.6877L5.5375 3.2502L3.2375 2.1502L1.825 3.5627L2.9625 5.8252L2.4 7.1877L0 8.0377V10.0252L2.425 10.8252L2.9875 12.1877L1.8875 14.4877L3.3 15.9002L5.5625 14.7627L6.925 15.3252L7.7875 17.7252H9.775L10.5625 15.3002L11.95 14.7377L14.25 15.8377L15.6625 14.4252L14.5125 12.1627L15.1 10.8002L17.5 9.93769V9.9627ZM8.75 12.7502C6.675 12.7502 5 11.0752 5 9.00019C5 6.9252 6.675 5.2502 8.75 5.2502C10.825 5.2502 12.5 6.9252 12.5 9.00019C12.5 11.0752 10.825 12.7502 8.75 12.7502Z" fill="#637799"/>
					</svg>
				</span>
				
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('General Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-activate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" style="display:block; margin: 0 auto;" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6154 0H4.38462C3.22174 0 2.1065 0.461949 1.28422 1.28422C0.461949 2.1065 0 3.22174 0 4.38462V13.6154C0 14.7783 0.461949 15.8935 1.28422 16.7158C2.1065 17.5381 3.22174 18 4.38462 18H13.6154C14.7783 18 15.8935 17.5381 16.7158 16.7158C17.5381 15.8935 18 14.7783 18 13.6154V4.38462C18 3.22174 17.5381 2.1065 16.7158 1.28422C15.8935 0.461949 14.7783 0 13.6154 0ZM7.64308 12.2585L5.79692 14.1046C5.66711 14.2343 5.49115 14.3071 5.30769 14.3071C5.12423 14.3071 4.94827 14.2343 4.81846 14.1046L3.89538 13.1815C3.82737 13.1182 3.77281 13.0417 3.73497 12.9568C3.69713 12.8719 3.67679 12.7802 3.67515 12.6873C3.67351 12.5943 3.69061 12.502 3.72543 12.4158C3.76024 12.3296 3.81207 12.2512 3.87781 12.1855C3.94355 12.1198 4.02186 12.0679 4.10806 12.0331C4.19427 11.9983 4.2866 11.9812 4.37956 11.9828C4.47252 11.9845 4.56419 12.0048 4.64911 12.0427C4.73403 12.0805 4.81047 12.1351 4.87385 12.2031L5.30769 12.6369L6.66462 11.28C6.79585 11.1577 6.96943 11.0911 7.14879 11.0943C7.32814 11.0975 7.49927 11.1701 7.62611 11.297C7.75295 11.4238 7.82561 11.5949 7.82878 11.7743C7.83194 11.9536 7.76537 12.1272 7.64308 12.2585ZM7.64308 4.87385L5.79692 6.72C5.66711 6.84965 5.49115 6.92247 5.30769 6.92247C5.12423 6.92247 4.94827 6.84965 4.81846 6.72L3.89538 5.79692C3.7731 5.66568 3.70652 5.4921 3.70968 5.31275C3.71285 5.13339 3.78551 4.96227 3.91235 4.83543C4.03919 4.70858 4.21032 4.63593 4.38967 4.63276C4.56903 4.6296 4.74261 4.69617 4.87385 4.81846L5.30769 5.25231L6.66462 3.89538C6.79585 3.7731 6.96943 3.70652 7.14879 3.70968C7.32814 3.71285 7.49927 3.78551 7.62611 3.91235C7.75295 4.03919 7.82561 4.21032 7.82878 4.38967C7.83194 4.56903 7.76537 4.74261 7.64308 4.87385ZM13.6154 13.3846H10.8462C10.6625 13.3846 10.4865 13.3117 10.3566 13.1818C10.2268 13.052 10.1538 12.8759 10.1538 12.6923C10.1538 12.5087 10.2268 12.3326 10.3566 12.2028C10.4865 12.0729 10.6625 12 10.8462 12H13.6154C13.799 12 13.9751 12.0729 14.1049 12.2028C14.2348 12.3326 14.3077 12.5087 14.3077 12.6923C14.3077 12.8759 14.2348 13.052 14.1049 13.1818C13.9751 13.3117 13.799 13.3846 13.6154 13.3846ZM13.6154 6H10.8462C10.6625 6 10.4865 5.92706 10.3566 5.79723C10.2268 5.66739 10.1538 5.4913 10.1538 5.30769C10.1538 5.12408 10.2268 4.94799 10.3566 4.81816C10.4865 4.68832 10.6625 4.61538 10.8462 4.61538H13.6154C13.799 4.61538 13.9751 4.68832 14.1049 4.81816C14.2348 4.94799 14.3077 5.12408 14.3077 5.30769C14.3077 5.4913 14.2348 5.66739 14.1049 5.79723C13.9751 5.92706 13.799 6 13.6154 6Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Membership Plan','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.2009 13.2254C15.2009 14.3638 14.8307 15.3441 14.0904 16.1663C13.3501 16.9885 12.3884 17.4963 11.2054 17.6897V19.6429C11.2054 19.747 11.1719 19.8326 11.1049 19.8996C11.0379 19.9665 10.9524 20 10.8482 20H9.34152C9.24479 20 9.16109 19.9647 9.0904 19.894C9.01972 19.8233 8.98437 19.7396 8.98437 19.6429V17.6897C8.4933 17.6228 8.01897 17.5074 7.56138 17.3438C7.10379 17.1801 6.72619 17.0145 6.42857 16.8471C6.13095 16.6797 5.85565 16.5011 5.60268 16.3114C5.3497 16.1217 5.17671 15.9821 5.08371 15.8929C4.9907 15.8036 4.9256 15.7366 4.88839 15.692C4.7619 15.5357 4.75446 15.3832 4.86607 15.2344L6.01562 13.7277C6.06771 13.6533 6.15327 13.6086 6.27232 13.5938C6.38393 13.5789 6.47321 13.6124 6.54018 13.6942L6.5625 13.7165C7.40327 14.4531 8.30729 14.9182 9.27455 15.1116C9.54985 15.1711 9.82515 15.2009 10.1004 15.2009C10.7031 15.2009 11.2333 15.0409 11.6908 14.721C12.1484 14.401 12.3772 13.9472 12.3772 13.3594C12.3772 13.151 12.3214 12.9539 12.2098 12.7679C12.0982 12.5818 11.9736 12.4256 11.8359 12.2991C11.6983 12.1726 11.4807 12.0331 11.183 11.8806C10.8854 11.7281 10.6399 11.609 10.4464 11.5234C10.253 11.4379 9.95536 11.317 9.55357 11.1607C9.26339 11.0417 9.0346 10.9487 8.86719 10.8817C8.69978 10.8147 8.47098 10.7161 8.1808 10.5859C7.89062 10.4557 7.65811 10.3404 7.48326 10.24C7.30841 10.1395 7.09821 10.0074 6.85268 9.84375C6.60714 9.68006 6.40811 9.52195 6.25558 9.36942C6.10305 9.21689 5.94122 9.0346 5.77009 8.82255C5.59896 8.61049 5.46689 8.39472 5.37388 8.17522C5.28088 7.95573 5.20275 7.70833 5.13951 7.43304C5.07626 7.15774 5.04464 6.86756 5.04464 6.5625C5.04464 5.53571 5.40923 4.63542 6.13839 3.86161C6.86756 3.0878 7.81622 2.58929 8.98437 2.36607V0.357143C8.98437 0.260417 9.01972 0.176711 9.0904 0.106027C9.16109 0.0353423 9.24479 0 9.34152 0H10.8482C10.9524 0 11.0379 0.0334821 11.1049 0.100446C11.1719 0.167411 11.2054 0.252976 11.2054 0.357143V2.32143C11.6295 2.36607 12.0406 2.45164 12.4386 2.57812C12.8367 2.70461 13.1603 2.82924 13.4096 2.95201C13.6589 3.07478 13.8951 3.21429 14.1183 3.37054C14.3415 3.52679 14.4866 3.63467 14.5536 3.6942C14.6205 3.75372 14.6763 3.8058 14.721 3.85045C14.8475 3.98438 14.8661 4.12574 14.7768 4.27455L13.8728 5.90402C13.8132 6.01562 13.7277 6.07515 13.6161 6.08259C13.5119 6.10491 13.4115 6.07887 13.3147 6.00446C13.2924 5.98214 13.2385 5.9375 13.1529 5.87054C13.0673 5.80357 12.9222 5.70499 12.7176 5.57478C12.513 5.44457 12.2954 5.32552 12.0647 5.21763C11.8341 5.10975 11.5569 5.01302 11.2333 4.92746C10.9096 4.84189 10.5915 4.79911 10.279 4.79911C9.57217 4.79911 8.99554 4.95908 8.54911 5.27902C8.10268 5.59896 7.87946 6.0119 7.87946 6.51786C7.87946 6.71131 7.91109 6.88988 7.97433 7.05357C8.03757 7.21726 8.14732 7.37165 8.30357 7.51674C8.45982 7.66183 8.60677 7.7846 8.74442 7.88505C8.88207 7.98549 9.0904 8.10082 9.36942 8.23103C9.64844 8.36124 9.87351 8.46168 10.0446 8.53237C10.2158 8.60305 10.4762 8.70536 10.8259 8.83929C11.2202 8.9881 11.5216 9.10528 11.7299 9.19085C11.9382 9.27641 12.221 9.40662 12.5781 9.58147C12.9353 9.75632 13.2161 9.91443 13.4208 10.0558C13.6254 10.1972 13.856 10.3832 14.1127 10.6138C14.3694 10.8445 14.5666 11.0807 14.7042 11.3225C14.8419 11.5644 14.9591 11.849 15.0558 12.1763C15.1525 12.5037 15.2009 12.8534 15.2009 13.2254Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Payment Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clip-path="url(#clip0_2972_7739)">
							<path d="M10.5416 8.33333C9.85408 6.39167 8.00825 5 5.83325 5C3.07075 5 0.833252 7.2375 0.833252 10C0.833252 12.7625 3.07075 15 5.83325 15C8.00825 15 9.85408 13.6083 10.5416 11.6667H14.1666V15H17.4999V11.6667H19.1666V8.33333H10.5416ZM5.83325 11.6667C4.91242 11.6667 4.16659 10.9208 4.16659 10C4.16659 9.07917 4.91242 8.33333 5.83325 8.33333C6.75409 8.33333 7.49992 9.07917 7.49992 10C7.49992 10.9208 6.75409 11.6667 5.83325 11.6667Z" fill="#637799"/>
						</g>
						<defs>
							<clipPath id="clip0_2972_7739">
								<rect width="20" height="20" fill="white"/>
							</clipPath>
						</defs>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Content Access','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.00007 9.89051C2.04546 9.87493 2.03478 9.82954 2.04342 9.7985C2.35381 8.68316 3.66188 8.32595 4.48772 9.13906C5.26014 9.89957 6.022 10.6708 6.7885 11.4373C6.88833 11.5371 6.9896 11.6356 7.08678 11.7378C7.12965 11.783 7.16348 11.7963 7.20492 11.7392C7.22078 11.7174 7.24296 11.7001 7.26229 11.6808C9.32991 9.61319 11.3983 7.54638 13.4643 5.47712C13.7718 5.16918 14.1313 4.98564 14.5699 5.00088C15.1689 5.02171 15.614 5.30529 15.8609 5.85265C16.1076 6.39967 16.0201 6.91668 15.6468 7.38489C15.6044 7.43811 15.5553 7.48629 15.5071 7.53461C13.0821 9.95993 10.6572 12.3852 8.23172 14.81C7.73003 15.3115 7.08705 15.4322 6.49784 15.1393C6.34975 15.0656 6.21977 14.9677 6.10305 14.8509C4.88904 13.6366 3.67509 12.4222 2.46039 11.2086C2.2565 11.0048 2.1093 10.7699 2.0407 10.4876C2.03416 10.4609 2.0424 10.4202 2 10.4098C2.00007 10.2366 2.00007 10.0636 2.00007 9.89051Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Complete','armember-membership'); ?></div>
		</div>
	</div>
	<div class="arm-ws-license-part-content arm-ws-account-setup">
		<div class="arm-ws-lic-con-heding-area">
			<div class="arm-lic-con-page-count"><?php echo str_pad($arm_sub_counter,2,'0',STR_PAD_LEFT); $arm_sub_counter += 1; //phpcs:ignore?></div>
			<div class="arm-lic-con-page-info">
				<h2 class="arm-lic-page-heding"><?php esc_html_e('Membership Plan','armember-membership');?></h2>
				<p class="arm-lic-page-disc"><?php esc_html_e('Here you will get all different types of Free/Paid membership plans with various general options and Subscription Types & Amount related options.','armember-membership');?></p>
			</div>
		</div>
		<div class="arm-lic-page-content-wrapper arm-gen-otp-content-wapper arm-member-ship-setup-wapper">
			<input type="hidden" name="arm_subscription_plan_type" value="free"/> 
			<div>
				<label class="arm-form-table-label arm-lic-new-user-approv-dd"><?php esc_html_e('Plan Type','armember-membership');?></label>
				<div class="arm-membership-plan-list">
					<span class="arm-membership-plan arm_free_plan click">
						<span><?php esc_html_e('Free','armember-membership');?></span>
						<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/selected-plan-checkmark.webp' //phpcs:ignore ?>" alt="ARMember">
					</span>
					<span class="arm-membership-plan arm_paid_infinite_plan">
						<span><?php esc_html_e('Paid Plan (infinite)','armember-membership');?></span>
						<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/selected-plan-checkmark.webp' //phpcs:ignore ?>" alt="ARMember">
					</span>
					<span class="arm-membership-plan arm_paid_finite_plan">
						<span><?php esc_html_e('Paid Plan (finite)','armember-membership');?></span>
						<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/selected-plan-checkmark.webp' //phpcs:ignore ?>" alt="ARMember">
					</span>
					<span class="arm-membership-plan arm_paid_subscription_plan">
						<span><?php esc_html_e('Subscription / Recurring Payment','armember-membership');?></span>
						<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/selected-plan-checkmark.webp' //phpcs:ignore ?>" alt="ARMember">
					</span>
				</div>
			</div>
        	<div class="arm-lic-page-content">
				<label class="arm-form-table-label"><?php esc_html_e('Plan Name','armember-membership');?></label>
					<div class="arm-form-table-content">
						<input id="arm_membership_plan_name" class="arm-lic-sectext-field" type="text" name="arm_membership_plan_name" value="Default Plan" placeholder="<?php esc_html_e('Enter plan name', 'armember-membership');?>" required data-msg-required="<?php esc_html_e('Please Enter Plan name', 'armember-membership');?>" >
						<span id="arm_membership_plan_name-error" class="error arm_invalid"></span>
					</div>
			</div>
			<div class="arm-lic-page-content arm_plan_amount_section hidden_section">
				<label class="arm-form-table-label"><?php esc_html_e('Plan Amount','armember-membership');?></label>
				<div class="arm-form-table-content">
					<input class="arm-lic-sectext-field" type="number" id="arm_membership_plan_amount" name="arm_membership_plan_amount" invalid_number_msg="<?php esc_html_e('Invalid Plan amount','armember-membership');?>" placeholder="<?php esc_html_e('Enter plan amount', 'armember-membership');?>" data-msg-required="<?php esc_html_e('Please Enter Plan Amount', 'armember-membership');?>">
					<span id="arm_membership_plan_amount-error" class="error arm_invalid"></span>
				</div>
			</div>
		</div>
	</div>
	<div class="arm-ws-footer-wrapper">
	    <div class="arm-ws-footer-left">
		<a href="https://youtu.be/Yh_X00tHzMw" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-youtube-btn">
		        <img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-youtube-icon.webp' //phpcs:ignore ?>" alt="ARMember">
				<?php esc_html_e('Watch Tutorial','armember-membership'); ?>
		</a>
		</div>
		<div class="arm-ws-footer-right">
			<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-back-btn">
				<?php esc_html_e('Back','armember-membership'); ?>
			</button>
			<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-next-btn">
				<?php esc_html_e('Continue','armember-membership'); ?>
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-long-right-arrow-icon.png' //phpcs:ignore ?>" alt="ARMember">
			</button>
		</div>
	</div>
</div>
<div class="arm-wizard-setup-container arm-ws-is-lic-page arm-payment-opt-wapper arm_setup_wizard_page_<?php echo esc_attr($arm_sub_counter);?> " id="arm_setup_wizard_page_<?php echo esc_html($arm_sub_counter);?>">
	<div class="arm-ws-account-setup">
		<div class="arm-ws-acco-logo">
			<a href="https://www.armemberplugin.com/" target="_blank">
				<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-logo-icon.webp' //phpcs:ignore ?>" alt="ARMember">
			</a>
		</div>
		<div class="arm-ws-acc-content">
			<h2 class="arm-ws-acc-heding"><?php esc_html_e('Account Setup','armember-membership');?></h2>
			<p class="arm-ws-acc-disc"><?php esc_html_e('Complete simple steps to get started.','armember-membership');?></p>
		</div>
	</div>
	
	<div class="arm-ws-steps-belt">
	<?php $arm_counter = 1;
		if($ARMemberLite->is_arm_pro_active)
		{
			$arm_counter = $arm_counter + 1;
		}
		$arm_pro_lincense_menu_item = '';
		echo apply_filters('arm_pro_lincense_menu_item',$arm_pro_lincense_menu_item); //phpcs:ignore
		?>	
		<div class="arm-ws-step-box arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter);$arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 9.9627V7.9627L15.075 7.1627L14.5125 5.8002L15.6125 3.5002L14.2 2.0877L11.9375 3.2252L10.575 2.6627L9.7125 0.262695H7.7125L6.925 2.6877L5.5375 3.2502L3.2375 2.1502L1.825 3.5627L2.9625 5.8252L2.4 7.1877L0 8.0377V10.0252L2.425 10.8252L2.9875 12.1877L1.8875 14.4877L3.3 15.9002L5.5625 14.7627L6.925 15.3252L7.7875 17.7252H9.775L10.5625 15.3002L11.95 14.7377L14.25 15.8377L15.6625 14.4252L14.5125 12.1627L15.1 10.8002L17.5 9.93769V9.9627ZM8.75 12.7502C6.675 12.7502 5 11.0752 5 9.00019C5 6.9252 6.675 5.2502 8.75 5.2502C10.825 5.2502 12.5 6.9252 12.5 9.00019C12.5 11.0752 10.825 12.7502 8.75 12.7502Z" fill="#637799"/>
					</svg>
				</span>
				
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('General Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter);$arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" style="display:block; margin: 0 auto;" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6154 0H4.38462C3.22174 0 2.1065 0.461949 1.28422 1.28422C0.461949 2.1065 0 3.22174 0 4.38462V13.6154C0 14.7783 0.461949 15.8935 1.28422 16.7158C2.1065 17.5381 3.22174 18 4.38462 18H13.6154C14.7783 18 15.8935 17.5381 16.7158 16.7158C17.5381 15.8935 18 14.7783 18 13.6154V4.38462C18 3.22174 17.5381 2.1065 16.7158 1.28422C15.8935 0.461949 14.7783 0 13.6154 0ZM7.64308 12.2585L5.79692 14.1046C5.66711 14.2343 5.49115 14.3071 5.30769 14.3071C5.12423 14.3071 4.94827 14.2343 4.81846 14.1046L3.89538 13.1815C3.82737 13.1182 3.77281 13.0417 3.73497 12.9568C3.69713 12.8719 3.67679 12.7802 3.67515 12.6873C3.67351 12.5943 3.69061 12.502 3.72543 12.4158C3.76024 12.3296 3.81207 12.2512 3.87781 12.1855C3.94355 12.1198 4.02186 12.0679 4.10806 12.0331C4.19427 11.9983 4.2866 11.9812 4.37956 11.9828C4.47252 11.9845 4.56419 12.0048 4.64911 12.0427C4.73403 12.0805 4.81047 12.1351 4.87385 12.2031L5.30769 12.6369L6.66462 11.28C6.79585 11.1577 6.96943 11.0911 7.14879 11.0943C7.32814 11.0975 7.49927 11.1701 7.62611 11.297C7.75295 11.4238 7.82561 11.5949 7.82878 11.7743C7.83194 11.9536 7.76537 12.1272 7.64308 12.2585ZM7.64308 4.87385L5.79692 6.72C5.66711 6.84965 5.49115 6.92247 5.30769 6.92247C5.12423 6.92247 4.94827 6.84965 4.81846 6.72L3.89538 5.79692C3.7731 5.66568 3.70652 5.4921 3.70968 5.31275C3.71285 5.13339 3.78551 4.96227 3.91235 4.83543C4.03919 4.70858 4.21032 4.63593 4.38967 4.63276C4.56903 4.6296 4.74261 4.69617 4.87385 4.81846L5.30769 5.25231L6.66462 3.89538C6.79585 3.7731 6.96943 3.70652 7.14879 3.70968C7.32814 3.71285 7.49927 3.78551 7.62611 3.91235C7.75295 4.03919 7.82561 4.21032 7.82878 4.38967C7.83194 4.56903 7.76537 4.74261 7.64308 4.87385ZM13.6154 13.3846H10.8462C10.6625 13.3846 10.4865 13.3117 10.3566 13.1818C10.2268 13.052 10.1538 12.8759 10.1538 12.6923C10.1538 12.5087 10.2268 12.3326 10.3566 12.2028C10.4865 12.0729 10.6625 12 10.8462 12H13.6154C13.799 12 13.9751 12.0729 14.1049 12.2028C14.2348 12.3326 14.3077 12.5087 14.3077 12.6923C14.3077 12.8759 14.2348 13.052 14.1049 13.1818C13.9751 13.3117 13.799 13.3846 13.6154 13.3846ZM13.6154 6H10.8462C10.6625 6 10.4865 5.92706 10.3566 5.79723C10.2268 5.66739 10.1538 5.4913 10.1538 5.30769C10.1538 5.12408 10.2268 4.94799 10.3566 4.81816C10.4865 4.68832 10.6625 4.61538 10.8462 4.61538H13.6154C13.799 4.61538 13.9751 4.68832 14.1049 4.81816C14.2348 4.94799 14.3077 5.12408 14.3077 5.30769C14.3077 5.4913 14.2348 5.66739 14.1049 5.79723C13.9751 5.92706 13.799 6 13.6154 6Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Membership Plan','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-activate" data-page_id='<?php echo esc_attr($arm_counter);$arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.2009 13.2254C15.2009 14.3638 14.8307 15.3441 14.0904 16.1663C13.3501 16.9885 12.3884 17.4963 11.2054 17.6897V19.6429C11.2054 19.747 11.1719 19.8326 11.1049 19.8996C11.0379 19.9665 10.9524 20 10.8482 20H9.34152C9.24479 20 9.16109 19.9647 9.0904 19.894C9.01972 19.8233 8.98437 19.7396 8.98437 19.6429V17.6897C8.4933 17.6228 8.01897 17.5074 7.56138 17.3438C7.10379 17.1801 6.72619 17.0145 6.42857 16.8471C6.13095 16.6797 5.85565 16.5011 5.60268 16.3114C5.3497 16.1217 5.17671 15.9821 5.08371 15.8929C4.9907 15.8036 4.9256 15.7366 4.88839 15.692C4.7619 15.5357 4.75446 15.3832 4.86607 15.2344L6.01562 13.7277C6.06771 13.6533 6.15327 13.6086 6.27232 13.5938C6.38393 13.5789 6.47321 13.6124 6.54018 13.6942L6.5625 13.7165C7.40327 14.4531 8.30729 14.9182 9.27455 15.1116C9.54985 15.1711 9.82515 15.2009 10.1004 15.2009C10.7031 15.2009 11.2333 15.0409 11.6908 14.721C12.1484 14.401 12.3772 13.9472 12.3772 13.3594C12.3772 13.151 12.3214 12.9539 12.2098 12.7679C12.0982 12.5818 11.9736 12.4256 11.8359 12.2991C11.6983 12.1726 11.4807 12.0331 11.183 11.8806C10.8854 11.7281 10.6399 11.609 10.4464 11.5234C10.253 11.4379 9.95536 11.317 9.55357 11.1607C9.26339 11.0417 9.0346 10.9487 8.86719 10.8817C8.69978 10.8147 8.47098 10.7161 8.1808 10.5859C7.89062 10.4557 7.65811 10.3404 7.48326 10.24C7.30841 10.1395 7.09821 10.0074 6.85268 9.84375C6.60714 9.68006 6.40811 9.52195 6.25558 9.36942C6.10305 9.21689 5.94122 9.0346 5.77009 8.82255C5.59896 8.61049 5.46689 8.39472 5.37388 8.17522C5.28088 7.95573 5.20275 7.70833 5.13951 7.43304C5.07626 7.15774 5.04464 6.86756 5.04464 6.5625C5.04464 5.53571 5.40923 4.63542 6.13839 3.86161C6.86756 3.0878 7.81622 2.58929 8.98437 2.36607V0.357143C8.98437 0.260417 9.01972 0.176711 9.0904 0.106027C9.16109 0.0353423 9.24479 0 9.34152 0H10.8482C10.9524 0 11.0379 0.0334821 11.1049 0.100446C11.1719 0.167411 11.2054 0.252976 11.2054 0.357143V2.32143C11.6295 2.36607 12.0406 2.45164 12.4386 2.57812C12.8367 2.70461 13.1603 2.82924 13.4096 2.95201C13.6589 3.07478 13.8951 3.21429 14.1183 3.37054C14.3415 3.52679 14.4866 3.63467 14.5536 3.6942C14.6205 3.75372 14.6763 3.8058 14.721 3.85045C14.8475 3.98438 14.8661 4.12574 14.7768 4.27455L13.8728 5.90402C13.8132 6.01562 13.7277 6.07515 13.6161 6.08259C13.5119 6.10491 13.4115 6.07887 13.3147 6.00446C13.2924 5.98214 13.2385 5.9375 13.1529 5.87054C13.0673 5.80357 12.9222 5.70499 12.7176 5.57478C12.513 5.44457 12.2954 5.32552 12.0647 5.21763C11.8341 5.10975 11.5569 5.01302 11.2333 4.92746C10.9096 4.84189 10.5915 4.79911 10.279 4.79911C9.57217 4.79911 8.99554 4.95908 8.54911 5.27902C8.10268 5.59896 7.87946 6.0119 7.87946 6.51786C7.87946 6.71131 7.91109 6.88988 7.97433 7.05357C8.03757 7.21726 8.14732 7.37165 8.30357 7.51674C8.45982 7.66183 8.60677 7.7846 8.74442 7.88505C8.88207 7.98549 9.0904 8.10082 9.36942 8.23103C9.64844 8.36124 9.87351 8.46168 10.0446 8.53237C10.2158 8.60305 10.4762 8.70536 10.8259 8.83929C11.2202 8.9881 11.5216 9.10528 11.7299 9.19085C11.9382 9.27641 12.221 9.40662 12.5781 9.58147C12.9353 9.75632 13.2161 9.91443 13.4208 10.0558C13.6254 10.1972 13.856 10.3832 14.1127 10.6138C14.3694 10.8445 14.5666 11.0807 14.7042 11.3225C14.8419 11.5644 14.9591 11.849 15.0558 12.1763C15.1525 12.5037 15.2009 12.8534 15.2009 13.2254Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Payment Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter);$arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clip-path="url(#clip0_2972_7739)">
							<path d="M10.5416 8.33333C9.85408 6.39167 8.00825 5 5.83325 5C3.07075 5 0.833252 7.2375 0.833252 10C0.833252 12.7625 3.07075 15 5.83325 15C8.00825 15 9.85408 13.6083 10.5416 11.6667H14.1666V15H17.4999V11.6667H19.1666V8.33333H10.5416ZM5.83325 11.6667C4.91242 11.6667 4.16659 10.9208 4.16659 10C4.16659 9.07917 4.91242 8.33333 5.83325 8.33333C6.75409 8.33333 7.49992 9.07917 7.49992 10C7.49992 10.9208 6.75409 11.6667 5.83325 11.6667Z" fill="#637799"/>
						</g>
						<defs>
							<clipPath id="clip0_2972_7739">
								<rect width="20" height="20" fill="white"/>
							</clipPath>
						</defs>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Content Access','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box" data-page_id='<?php echo esc_attr($arm_counter);$arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.00007 9.89051C2.04546 9.87493 2.03478 9.82954 2.04342 9.7985C2.35381 8.68316 3.66188 8.32595 4.48772 9.13906C5.26014 9.89957 6.022 10.6708 6.7885 11.4373C6.88833 11.5371 6.9896 11.6356 7.08678 11.7378C7.12965 11.783 7.16348 11.7963 7.20492 11.7392C7.22078 11.7174 7.24296 11.7001 7.26229 11.6808C9.32991 9.61319 11.3983 7.54638 13.4643 5.47712C13.7718 5.16918 14.1313 4.98564 14.5699 5.00088C15.1689 5.02171 15.614 5.30529 15.8609 5.85265C16.1076 6.39967 16.0201 6.91668 15.6468 7.38489C15.6044 7.43811 15.5553 7.48629 15.5071 7.53461C13.0821 9.95993 10.6572 12.3852 8.23172 14.81C7.73003 15.3115 7.08705 15.4322 6.49784 15.1393C6.34975 15.0656 6.21977 14.9677 6.10305 14.8509C4.88904 13.6366 3.67509 12.4222 2.46039 11.2086C2.2565 11.0048 2.1093 10.7699 2.0407 10.4876C2.03416 10.4609 2.0424 10.4202 2 10.4098C2.00007 10.2366 2.00007 10.0636 2.00007 9.89051Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Complete','armember-membership'); ?></div>
		</div>
	</div>
	<div class="arm-ws-license-part-content arm-ws-account-setup">
		<div class="arm-ws-lic-con-heding-area">
			<div class="arm-lic-con-page-count"><?php echo str_pad($arm_sub_counter,2,'0',STR_PAD_LEFT); $arm_sub_counter = $arm_sub_counter + 1; //phpcs:ignore?></div>
			<div class="arm-lic-con-page-info">
				<h2 class="arm-lic-page-heding"><?php esc_html_e('Payment Options','armember-membership');?></h2>
				<p class="arm-lic-page-disc"><?php esc_html_e('Here you will get all different types of Payment gateways to purchase a membership plan.','armember-membership');?></p>
			</div>
		</div>
		<div class="arm-lic-page-content-wrapper arm-gen-otp-content-wapper arm-member-ship-setup-wapper arm-payment-setup-sec-wapper">
			<?php 
			$arm_setup_wizard_payment_gateway_section = '';
			echo apply_filters('arm_setup_wizard_payment_gateway_section',$arm_setup_wizard_payment_gateway_section); //phpcs:ignore
			$arm_paypal_active = 'checked="checked"';
			$arm_paypal_section_css = '';
			if($ARMemberLite->is_arm_pro_active)
			{
				$arm_paypal_active = '';
				$arm_paypal_section_css = 'display:none;';
			}
			?>
			<div class=arm_payment_gateway_section>
				<div class="armswitch arm_payment_setting_switch">
					<input type="checkbox" id="arm_setup_paypal_status" value="1" <?php echo esc_attr($arm_paypal_active);?> class="armswitch_input armswitch_payment_input" name="arm_selected_payment_gateway[paypal][status]">
					<label for="arm_setup_paypal_status" class="armswitch_label"></label>
				</div>
				<label class="arm-form-table-label arm-paym-meth-lable" for="arm_setup_paypal_status" style="display:contents">&nbsp;<?php esc_html_e('Enable PayPal Payment','armember-membership');?></label>
				<div class="arm_paypal_payment_setup" style="<?php echo esc_attr($arm_paypal_section_css)?>">
					<div class="arm-lic-page-content arm-payment-mrthod-checkbox">
							<label class="arm-form-table-label arm-paym-meth-lable"><?php esc_html_e('Payment Mode','armember-membership');?></label>
							<span class="arm_subscription_types_container" id="arm_subscription_types_container"><input type="radio" class="arm_iradio" checked="checked" value="sandbox" name="arm_selected_payment_gateway[paypal][payment_method]" id="payment_method_type_test_paypal"><label for="payment_method_type_test_paypal"><?php esc_html_e('Test','armember-membership');?></label>
							</span>
							<span class="arm_subscription_types_container" id="arm_subscription_types_container"><input type="radio" class="arm_iradio" value="live" name="arm_selected_payment_gateway[paypal][payment_method]" id="payment_method_type_live_paypal"><label for="payment_method_type_live_paypal"><?php esc_html_e('Live','armember-membership');?></label>
							</span>
					</div>
					<div class="arm-lic-page-content arm_paypal_setup arm_payment_setup">
						<label class="arm-form-table-label"><?php esc_html_e('Merchant Email','armember-membership');?>*</label>
							<div class="arm-form-table-content">
								<input id="arm_paypal_merchant_email" class="arm-lic-sectext-field" type="text" name="arm_selected_payment_gateway[paypal][merchant_email]" value="" reqiured placeholder="<?php esc_attr_e('Enter Merchant Email Address','armember-membership');?>" required_msg="<?php esc_attr_e('Please Enter Merchant API Email','armember-membership');?>">
								<span id="arm_paypal_merchant_email-error" class="error arm_invalid"></span>  
							</div>
					</div>
					<div class="arm-lic-page-content arm_paypal_setup arm_payment_setup">
						<label class="arm-form-table-label arm_paypal_sandbox_api"><?php esc_html_e('Paypal API Username ( Sandbox)','armember-membership');?>*</label>
						<label class="arm-form-table-label arm_paypal_live_api" style="display:none"><?php esc_html_e('Paypal API Username','armember-membership');?>*</label>
							<div class="arm-form-table-content">
								<input id="arm_paypal_merchant_api_username" class="arm-lic-sectext-field" type="text" name="arm_selected_payment_gateway[paypal][api_username]" value="" reqired placeholder="<?php esc_attr_e('Enter API Username','armember-membership');?>" required_msg="<?php esc_attr_e('Please Enter Merchant API Username','armember-membership');?>">
								<span id="arm_paypal_merchant_api_username-error" class="error arm_invalid"></span>  
							</div>
					</div>
					<div class="arm-lic-page-content arm_paypal_setup arm_payment_setup">
						<label class="arm-form-table-label arm_paypal_sandbox_api"><?php esc_html_e('Paypal API Password (Sandbox)','armember-membership');?>*</label>
						<label class="arm-form-table-label arm_paypal_live_api" style="display:none"><?php esc_html_e('Paypal API Password','armember-membership');?>*</label>
							<div class="arm-form-table-content">
								<input id="arm_paypal_merchant_api_password" class="arm-lic-sectext-field" type="text" name="arm_selected_payment_gateway[paypal][api_password]" value="" reqired placeholder="<?php esc_attr_e('Enter API Password','armember-membership');?>" required_msg="<?php esc_attr_e('Please Enter Merchant API Password','armember-membership');?>">    
								<span id="arm_paypal_merchant_api_password-error" class="error arm_invalid"></span>  
							</div>
					</div>
					<div class="arm-lic-page-content arm_paypal_setup arm_payment_setup">
						<label class="arm-form-table-label arm_paypal_sandbox_api"><?php esc_html_e('Paypal API Signature (Sandbox)','armember-membership');?>*</label>
						<label class="arm-form-table-label arm_paypal_live_api" style="display:none"><?php esc_html_e('Paypal API Signature','armember-membership');?>*</label>
							<div class="arm-form-table-content">
								<input id="arm_paypal_merchant_api_signature" class="arm-lic-sectext-field" type="text" name="arm_selected_payment_gateway[paypal][api_signature]" value="" reqired placeholder="<?php esc_attr_e('Enter API Signature','armember-membership');?>" required_msg="<?php esc_attr_e('Please Enter Merchant API Signature','armember-membership');?>">    
								<span id="arm_paypal_merchant_api_signature-error" class="error arm_invalid"></span>  
							</div>
					</div>
				</div>
			</div>
			<div class=arm_payment_gateway_section>
				<div class="armswitch arm_payment_setting_switch">
					<input type="checkbox" id="arm_setup_bank_status" value="1" class="armswitch_input armswitch_payment_input" name="arm_selected_payment_gateway[bank_transfer][status]">
					<label for="arm_setup_bank_status" class="armswitch_label"></label>
				</div>
				<label class="arm-form-table-label arm-paym-meth-lable" for="arm_setup_bank_status" style="display:contents">&nbsp;<?php esc_html_e('Enable Bank Transfer Payment','armember-membership');?></label>
				<div class="arm-lic-page-content arm_bank_transfer_payment_setup arm_payment_setup" style="display:none">
					<label class="arm-form-table-label"><?php esc_html_e('Bank Transfer Fields','armember-membership');?>*</label>
						<div class="arm-form-table-content">
							<label>
								<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_transaction_id" name="arm_selected_payment_gateway[bank_transfer][transaction_id]" value="1" checked="checked">
								<span><?php esc_html_e('Transaction ID','armember-membership'); ?></span>
							</label>
							<label>
								<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_bank_name" name="arm_selected_payment_gateway[bank_transfer][bank_name]" value="1" checked="checked">
								<span><?php esc_html_e('Bank Name','armember-membership');?></span>
							</label>
							<label>
								<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_account_name" name="arm_selected_payment_gateway[bank_transfer][account_name]" value="1" checked="checked">
								<span><?php esc_html_e('Account Holder Name','armember-membership');?></span>
							</label>
							<label>
								<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_additional_info" name="arm_selected_payment_gateway[bank_transfer][additional_info]" value="1" checked="checked">
								<span><?php esc_html_e('Additional Info/Note','armember-membership');?></span>
							</label>
							<label>
								<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_mode" name="arm_selected_payment_gateway[bank_transfer][transfer_mode]" value="1" checked="checked">
								<span><?php esc_html_e('Payment Mode','armember-membership');?></span>
							</label>
							<div class="arm_transfer_mode_main_container">
								<div class="arm_transfer_mode_list_container">
									<label>
										<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_mode_option" name="arm_selected_payment_gateway[bank_transfer][transfer_mode_option][]" value="bank_transfer" data-msg-required="Please select Payment Mode option." checked="checked">
									</label>
									<input class="arm_bank_transfer_mode_option_label" type="text" name="arm_selected_payment_gateway[bank_transfer][digital_transfer_label]" value="Digital Transfer">
								</div>
								<div class="arm_transfer_mode_list_container">
									<label>
										<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_mode_option" name="arm_selected_payment_gateway[bank_transfer][transfer_mode_option][]" value="cheque" data-msg-required="Please select Payment Mode option." checked="checked">
									</label>
										<input class="arm_bank_transfer_mode_option_label" type="text" name="arm_selected_payment_gateway[bank_transfer][cheque_label]" value="Cheque">
								</div>
								<div class="arm_transfer_mode_list_container">
									<label>
										<input class="arm_general_input arm_icheckbox arm_active_payment_bank_transfer" type="checkbox" id="bank_transfer_mode_option" name="arm_selected_payment_gateway[bank_transfer][transfer_mode_option][]" value="cash" data-msg-required="Please select Payment Mode option." checked="checked">
									</label>
									<input class="arm_bank_transfer_mode_option_label" type="text" name="arm_selected_payment_gateway[bank_transfer][cash_label]" value="Cash">
								</div>
							</div>
					</div>
				</div>
			</div>
			<span id="arm_no_payment_gateway-error" class="error arm_invalid" style="display:none;"><?php esc_html_e('Please enable at least one payment.','armember-membership');?></span>  
		</div>
	</div>
	<div class="arm-ws-footer-wrapper">
	    <div class="arm-ws-footer-left">
		<a href="https://youtu.be/Yh_X00tHzMw" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-youtube-btn">
		        <img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-youtube-icon.webp' //phpcs:ignore ?>" alt="ARMember">
				<?php esc_html_e('Watch Tutorial','armember-membership'); ?>
		</a>
		</div>
		<div class="arm-ws-footer-right">
			<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-back-btn">
				<?php esc_html_e('Back','armember-membership'); ?>
			</button>
			<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-next-btn">
				<?php esc_html_e('Continue','armember-membership'); ?>
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-long-right-arrow-icon.png' //phpcs:ignore ?>" alt="ARMember">
			</button>
		</div>
	</div>
</div>

<input id="arm_setup_name" class="arm-lic-sectext-field" type="hidden" name="arm_membership_setup_name" value="Default Setup">

<div class="arm-wizard-setup-container arm-ws-is-lic-page arm_setup_wizard_page_<?php echo esc_attr($arm_sub_counter);?> " id="arm_setup_wizard_page_<?php echo esc_html($arm_sub_counter);?>">
	<div class="arm-ws-account-setup">
		<div class="arm-ws-acco-logo">
			<a href="https://www.armemberplugin.com/" target="_blank">
				<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-logo-icon.webp' //phpcs:ignore ?>" alt="ARMember">
			</a>
		</div>
		<div class="arm-ws-acc-content">
			<h2 class="arm-ws-acc-heding"><?php esc_html_e('Account Setup','armember-membership');?></h2>
			<p class="arm-ws-acc-disc"><?php esc_html_e('Complete simple steps to get started.','armember-membership'); ?></p>
		</div>
	</div>
	
	<div class="arm-ws-steps-belt">
	<?php $arm_counter = 1;
		if($ARMemberLite->is_arm_pro_active)
		{
			$arm_counter = $arm_counter + 1;
		}
		$arm_pro_lincense_menu_item = '';
		echo apply_filters('arm_pro_lincense_menu_item',$arm_pro_lincense_menu_item); //phpcs:ignore
		?>	
		<div class="arm-ws-step-box arm-ws-step-complate" data-page_id='<?php echo esc_html($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 9.9627V7.9627L15.075 7.1627L14.5125 5.8002L15.6125 3.5002L14.2 2.0877L11.9375 3.2252L10.575 2.6627L9.7125 0.262695H7.7125L6.925 2.6877L5.5375 3.2502L3.2375 2.1502L1.825 3.5627L2.9625 5.8252L2.4 7.1877L0 8.0377V10.0252L2.425 10.8252L2.9875 12.1877L1.8875 14.4877L3.3 15.9002L5.5625 14.7627L6.925 15.3252L7.7875 17.7252H9.775L10.5625 15.3002L11.95 14.7377L14.25 15.8377L15.6625 14.4252L14.5125 12.1627L15.1 10.8002L17.5 9.93769V9.9627ZM8.75 12.7502C6.675 12.7502 5 11.0752 5 9.00019C5 6.9252 6.675 5.2502 8.75 5.2502C10.825 5.2502 12.5 6.9252 12.5 9.00019C12.5 11.0752 10.825 12.7502 8.75 12.7502Z" fill="#637799"/>
					</svg>
				</span>
				
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('General Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" style="display:block; margin: 0 auto;" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6154 0H4.38462C3.22174 0 2.1065 0.461949 1.28422 1.28422C0.461949 2.1065 0 3.22174 0 4.38462V13.6154C0 14.7783 0.461949 15.8935 1.28422 16.7158C2.1065 17.5381 3.22174 18 4.38462 18H13.6154C14.7783 18 15.8935 17.5381 16.7158 16.7158C17.5381 15.8935 18 14.7783 18 13.6154V4.38462C18 3.22174 17.5381 2.1065 16.7158 1.28422C15.8935 0.461949 14.7783 0 13.6154 0ZM7.64308 12.2585L5.79692 14.1046C5.66711 14.2343 5.49115 14.3071 5.30769 14.3071C5.12423 14.3071 4.94827 14.2343 4.81846 14.1046L3.89538 13.1815C3.82737 13.1182 3.77281 13.0417 3.73497 12.9568C3.69713 12.8719 3.67679 12.7802 3.67515 12.6873C3.67351 12.5943 3.69061 12.502 3.72543 12.4158C3.76024 12.3296 3.81207 12.2512 3.87781 12.1855C3.94355 12.1198 4.02186 12.0679 4.10806 12.0331C4.19427 11.9983 4.2866 11.9812 4.37956 11.9828C4.47252 11.9845 4.56419 12.0048 4.64911 12.0427C4.73403 12.0805 4.81047 12.1351 4.87385 12.2031L5.30769 12.6369L6.66462 11.28C6.79585 11.1577 6.96943 11.0911 7.14879 11.0943C7.32814 11.0975 7.49927 11.1701 7.62611 11.297C7.75295 11.4238 7.82561 11.5949 7.82878 11.7743C7.83194 11.9536 7.76537 12.1272 7.64308 12.2585ZM7.64308 4.87385L5.79692 6.72C5.66711 6.84965 5.49115 6.92247 5.30769 6.92247C5.12423 6.92247 4.94827 6.84965 4.81846 6.72L3.89538 5.79692C3.7731 5.66568 3.70652 5.4921 3.70968 5.31275C3.71285 5.13339 3.78551 4.96227 3.91235 4.83543C4.03919 4.70858 4.21032 4.63593 4.38967 4.63276C4.56903 4.6296 4.74261 4.69617 4.87385 4.81846L5.30769 5.25231L6.66462 3.89538C6.79585 3.7731 6.96943 3.70652 7.14879 3.70968C7.32814 3.71285 7.49927 3.78551 7.62611 3.91235C7.75295 4.03919 7.82561 4.21032 7.82878 4.38967C7.83194 4.56903 7.76537 4.74261 7.64308 4.87385ZM13.6154 13.3846H10.8462C10.6625 13.3846 10.4865 13.3117 10.3566 13.1818C10.2268 13.052 10.1538 12.8759 10.1538 12.6923C10.1538 12.5087 10.2268 12.3326 10.3566 12.2028C10.4865 12.0729 10.6625 12 10.8462 12H13.6154C13.799 12 13.9751 12.0729 14.1049 12.2028C14.2348 12.3326 14.3077 12.5087 14.3077 12.6923C14.3077 12.8759 14.2348 13.052 14.1049 13.1818C13.9751 13.3117 13.799 13.3846 13.6154 13.3846ZM13.6154 6H10.8462C10.6625 6 10.4865 5.92706 10.3566 5.79723C10.2268 5.66739 10.1538 5.4913 10.1538 5.30769C10.1538 5.12408 10.2268 4.94799 10.3566 4.81816C10.4865 4.68832 10.6625 4.61538 10.8462 4.61538H13.6154C13.799 4.61538 13.9751 4.68832 14.1049 4.81816C14.2348 4.94799 14.3077 5.12408 14.3077 5.30769C14.3077 5.4913 14.2348 5.66739 14.1049 5.79723C13.9751 5.92706 13.799 6 13.6154 6Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Membership Plan','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.2009 13.2254C15.2009 14.3638 14.8307 15.3441 14.0904 16.1663C13.3501 16.9885 12.3884 17.4963 11.2054 17.6897V19.6429C11.2054 19.747 11.1719 19.8326 11.1049 19.8996C11.0379 19.9665 10.9524 20 10.8482 20H9.34152C9.24479 20 9.16109 19.9647 9.0904 19.894C9.01972 19.8233 8.98437 19.7396 8.98437 19.6429V17.6897C8.4933 17.6228 8.01897 17.5074 7.56138 17.3438C7.10379 17.1801 6.72619 17.0145 6.42857 16.8471C6.13095 16.6797 5.85565 16.5011 5.60268 16.3114C5.3497 16.1217 5.17671 15.9821 5.08371 15.8929C4.9907 15.8036 4.9256 15.7366 4.88839 15.692C4.7619 15.5357 4.75446 15.3832 4.86607 15.2344L6.01562 13.7277C6.06771 13.6533 6.15327 13.6086 6.27232 13.5938C6.38393 13.5789 6.47321 13.6124 6.54018 13.6942L6.5625 13.7165C7.40327 14.4531 8.30729 14.9182 9.27455 15.1116C9.54985 15.1711 9.82515 15.2009 10.1004 15.2009C10.7031 15.2009 11.2333 15.0409 11.6908 14.721C12.1484 14.401 12.3772 13.9472 12.3772 13.3594C12.3772 13.151 12.3214 12.9539 12.2098 12.7679C12.0982 12.5818 11.9736 12.4256 11.8359 12.2991C11.6983 12.1726 11.4807 12.0331 11.183 11.8806C10.8854 11.7281 10.6399 11.609 10.4464 11.5234C10.253 11.4379 9.95536 11.317 9.55357 11.1607C9.26339 11.0417 9.0346 10.9487 8.86719 10.8817C8.69978 10.8147 8.47098 10.7161 8.1808 10.5859C7.89062 10.4557 7.65811 10.3404 7.48326 10.24C7.30841 10.1395 7.09821 10.0074 6.85268 9.84375C6.60714 9.68006 6.40811 9.52195 6.25558 9.36942C6.10305 9.21689 5.94122 9.0346 5.77009 8.82255C5.59896 8.61049 5.46689 8.39472 5.37388 8.17522C5.28088 7.95573 5.20275 7.70833 5.13951 7.43304C5.07626 7.15774 5.04464 6.86756 5.04464 6.5625C5.04464 5.53571 5.40923 4.63542 6.13839 3.86161C6.86756 3.0878 7.81622 2.58929 8.98437 2.36607V0.357143C8.98437 0.260417 9.01972 0.176711 9.0904 0.106027C9.16109 0.0353423 9.24479 0 9.34152 0H10.8482C10.9524 0 11.0379 0.0334821 11.1049 0.100446C11.1719 0.167411 11.2054 0.252976 11.2054 0.357143V2.32143C11.6295 2.36607 12.0406 2.45164 12.4386 2.57812C12.8367 2.70461 13.1603 2.82924 13.4096 2.95201C13.6589 3.07478 13.8951 3.21429 14.1183 3.37054C14.3415 3.52679 14.4866 3.63467 14.5536 3.6942C14.6205 3.75372 14.6763 3.8058 14.721 3.85045C14.8475 3.98438 14.8661 4.12574 14.7768 4.27455L13.8728 5.90402C13.8132 6.01562 13.7277 6.07515 13.6161 6.08259C13.5119 6.10491 13.4115 6.07887 13.3147 6.00446C13.2924 5.98214 13.2385 5.9375 13.1529 5.87054C13.0673 5.80357 12.9222 5.70499 12.7176 5.57478C12.513 5.44457 12.2954 5.32552 12.0647 5.21763C11.8341 5.10975 11.5569 5.01302 11.2333 4.92746C10.9096 4.84189 10.5915 4.79911 10.279 4.79911C9.57217 4.79911 8.99554 4.95908 8.54911 5.27902C8.10268 5.59896 7.87946 6.0119 7.87946 6.51786C7.87946 6.71131 7.91109 6.88988 7.97433 7.05357C8.03757 7.21726 8.14732 7.37165 8.30357 7.51674C8.45982 7.66183 8.60677 7.7846 8.74442 7.88505C8.88207 7.98549 9.0904 8.10082 9.36942 8.23103C9.64844 8.36124 9.87351 8.46168 10.0446 8.53237C10.2158 8.60305 10.4762 8.70536 10.8259 8.83929C11.2202 8.9881 11.5216 9.10528 11.7299 9.19085C11.9382 9.27641 12.221 9.40662 12.5781 9.58147C12.9353 9.75632 13.2161 9.91443 13.4208 10.0558C13.6254 10.1972 13.856 10.3832 14.1127 10.6138C14.3694 10.8445 14.5666 11.0807 14.7042 11.3225C14.8419 11.5644 14.9591 11.849 15.0558 12.1763C15.1525 12.5037 15.2009 12.8534 15.2009 13.2254Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Payment Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-activate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clip-path="url(#clip0_2972_7739)">
							<path d="M10.5416 8.33333C9.85408 6.39167 8.00825 5 5.83325 5C3.07075 5 0.833252 7.2375 0.833252 10C0.833252 12.7625 3.07075 15 5.83325 15C8.00825 15 9.85408 13.6083 10.5416 11.6667H14.1666V15H17.4999V11.6667H19.1666V8.33333H10.5416ZM5.83325 11.6667C4.91242 11.6667 4.16659 10.9208 4.16659 10C4.16659 9.07917 4.91242 8.33333 5.83325 8.33333C6.75409 8.33333 7.49992 9.07917 7.49992 10C7.49992 10.9208 6.75409 11.6667 5.83325 11.6667Z" fill="#637799"/>
						</g>
						<defs>
							<clipPath id="clip0_2972_7739">
								<rect width="20" height="20" fill="white"/>
							</clipPath>
						</defs>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Content Access','armember-membership'); ?></div>
		</div>

		<div class="arm-ws-step-box " data-page_id='<?php echo esc_attr($arm_counter);?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.00007 9.89051C2.04546 9.87493 2.03478 9.82954 2.04342 9.7985C2.35381 8.68316 3.66188 8.32595 4.48772 9.13906C5.26014 9.89957 6.022 10.6708 6.7885 11.4373C6.88833 11.5371 6.9896 11.6356 7.08678 11.7378C7.12965 11.783 7.16348 11.7963 7.20492 11.7392C7.22078 11.7174 7.24296 11.7001 7.26229 11.6808C9.32991 9.61319 11.3983 7.54638 13.4643 5.47712C13.7718 5.16918 14.1313 4.98564 14.5699 5.00088C15.1689 5.02171 15.614 5.30529 15.8609 5.85265C16.1076 6.39967 16.0201 6.91668 15.6468 7.38489C15.6044 7.43811 15.5553 7.48629 15.5071 7.53461C13.0821 9.95993 10.6572 12.3852 8.23172 14.81C7.73003 15.3115 7.08705 15.4322 6.49784 15.1393C6.34975 15.0656 6.21977 14.9677 6.10305 14.8509C4.88904 13.6366 3.67509 12.4222 2.46039 11.2086C2.2565 11.0048 2.1093 10.7699 2.0407 10.4876C2.03416 10.4609 2.0424 10.4202 2 10.4098C2.00007 10.2366 2.00007 10.0636 2.00007 9.89051Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Complete','armember-membership'); ?></div>
		</div>
	</div>
	<div class="arm-ws-license-part-content arm-ws-account-setup">
		<div class="arm-ws-lic-con-heding-area">
			<div class="arm-lic-con-page-count"><?php echo str_pad($arm_sub_counter,2,'0',STR_PAD_LEFT); $arm_sub_counter = $arm_sub_counter + 1; //phpcs:ignore?></div>
			<div class="arm-lic-con-page-info">
				<h2 class="arm-lic-page-heding"><?php esc_html_e('Content Access','armember-membership'); ?></h2>
				<p class="arm-lic-page-disc"><?php esc_html_e('"Access Rule" is the main core feature of ARMember plugin. Very easy interface to manage content protection on site in accordance with the related plans. Content Protection Access Rule','armember-membership'); ?>.</p>
			</div>
		</div>
		<div class="arm-lic-page-content-wrapper arm-lic-opt-com-wapper arm-content-access-wrapper">
			<div class="arm-lic-detail-part">
				<div class="arm-cont-access-plan-details">
					<div class="arm-cont-access-plan-heding"><?php esc_html_e('Plan Name','armember-membership'); ?></div>
					<div class="arm-cont-access-plan-name">Gold Membership Yearly</div>
				</div>
			</div>
			<div class="arm-lic-page-content-wrapper arm-gen-otp-content-wapper">
				<div class="arm-lic-page-content arm_content_access_rules_wrapper">
					<label class="arm-form-table-label"><?php esc_html_e('Select pages to set access/restriction for created membership plan','armember-membership');?>*</label>
					<?php
						global $arm_slugs;
						$post_type_obj = get_post_type_object('page');
						$arm_pages = $arm_global_settings->arm_get_single_global_settings('page_settings');
						$rule_records = array();
						$slug ='page';
						$protection = '';
						unset($arm_pages['member_profile_page_id']);
						unset($arm_pages['thank_you_page_id']);
						unset($arm_pages['cancel_payment_page_id']);
						if (!empty($post_type_obj))
						{
							$orderby = "ORDER BY P.`post_date` DESC";
							$user_where ='';
							if(!empty($arm_pages))
							{
								$super_admin_placeholders = ' AND P.`ID` NOT IN (';
								$super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $arm_pages ) ), ',' );
								$super_admin_placeholders .= ')';
								array_unshift( $arm_pages, $super_admin_placeholders );
								$user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $arm_pages );
							}
							if(empty($arm_pages))
							{
								$arm_pages = 0;
							}
							$arm_all_slugs = array_filter((array) $arm_slugs);
							// $arm_page_slugs = trim(implode("','", array_filter((array) $arm_slugs)), ",");
							$super_admin_placeholders = ' AND P.`post_name` NOT IN (';
							$super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $arm_all_slugs ) ), ',' );
							$super_admin_placeholders .= ')';
							array_unshift( $arm_all_slugs, $super_admin_placeholders );
							$user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $arm_all_slugs );

							$where = $wpdb->prepare("WHERE P.`post_type`=%s AND P.`post_status`=%s ".$user_where,$slug,'publish');//phpcs:ignore --Reason $user_where is a query
							$join = "";
							if (!empty($planArr)) {
								$findInSet = array();
								foreach ($planArr as $pid) {
									if ($protection == '0') {
										$findInSet[] = " NOT FIND_IN_SET($pid, PM2.`meta_value`) ";
									} else {
										$findInSet[] = " FIND_IN_SET($pid, PM2.`meta_value`) ";
									}
								}
								$findInSet = implode(' OR ', $findInSet);
								$join .= " INNER JOIN `" . $wpdb->postmeta . "` AS PM2 ON PM2.`post_id` = P.`ID`";
								$where .= $wpdb->prepare(" AND (PM2.`meta_key`=%s AND ($findInSet))",'arm_access_plan');//phpcs:ignore --Reason $findInSet is a find in set query
							} else {
								if ($protection != 'all') {
									
									if($protection == 1)
									{
										$join .= " INNER JOIN `" . $wpdb->postmeta . "` AS PM1 ON PM1.`post_id` = P.`ID`";
										$where .= $wpdb->prepare(" AND (PM1.`meta_key`=%s AND PM1.`meta_value`=%s)",'arm_access_plan','0');//phpcs:ignore
									}
									
								}
							}
							$posts_sql = "SELECT P.`ID`, P.`post_parent`, P.`post_title` FROM `" . $wpdb->posts . "` AS P $join $where $orderby";
							$results = $wpdb->get_results($posts_sql); //phpcs:ignore --Reason $posts_sql is a predefined queries
							if (!empty($results)) {
								foreach ($results as $p) {						
									if (is_plugin_active('bbpress/bbpress.php') && class_exists('bbPress')){
										if($slug == 'reply'){
											$posts_sql1 = $wpdb->prepare("SELECT `ID`,`post_title`  FROM `" . $wpdb->posts . "` WHERE `ID` = %d",$p->post_parent); //phpcs:ignore --Reason $wpdb->posts is a table name
											$post_result = $wpdb->get_row($posts_sql1); //phpcs:ignore --Reason $posts_sql1 is a predefined query
											$post_reply_title = $post_result->post_title;
											
											$post_title = esc_html__('Reply To:','armember-membership').$post_reply_title." (<i>#".$p->ID."</i>)";

										}
										else {
											$post_id = $p->ID;
											$post_title = $p->post_title;
										}
									}
									else
									{
										$post_title = $p->post_title;
									}
									
									array_push($rule_records,array(
										'id' => $p->ID,
										'title' => $post_title,
									));
								}
							}
						}
					?>
					<select id="arm_access_for_membership_plans" class="arm_chosen_selectbox arm_width_500" name="arm_access_rules_pages_ids[]" data-placeholder="<?php esc_attr_e('Select Pages(s)..', 'armember-membership');?>" multiple="multiple" >
							<?php
								if (!empty($rule_records)){
									foreach ($rule_records as $rules_page) {
										?><option class="arm_message_selectbox_op" value="<?php echo esc_attr($rules_page['id']); ?>"><?php echo esc_html(stripslashes($rules_page['title']));?></option><?php
									}
								}
								else{
							?>
									<option value=""><?php esc_html_e('No Pages Available', 'armember-membership');?></option>
							<?php };?>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="arm-ws-footer-wrapper">
	    <div class="arm-ws-footer-left">
		<a href="https://youtu.be/Yh_X00tHzMw" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-youtube-btn">
		        <img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-youtube-icon.webp' //phpcs:ignore ?>" alt="ARMember">
				<?php esc_html_e('Watch Tutorial','armember-membership'); ?>
		</a>
		</div>
		<div class="arm-ws-footer-right">
			<button type="button" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-back-btn">
				<?php esc_html_e('Back','armember-membership'); ?>
			</button>
			<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" id="arm_setup_completion_loader" class="arm_submit_btn_loader" width="24" height="24" style="display: none;" />
			<button type="submit" class="arm-wsc-btn arm-wsc-btn--primary arm-ws-next-btn arm_complete_setup_step">
				<?php esc_html_e('Continue','armember-membership'); ?>
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-lp-long-right-arrow-icon.png' //phpcs:ignore ?>" alt="ARMember">
			</button>
		</div>
	</div>
</div>
<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
</form>
<div class="arm-wizard-setup-container arm-ws-is-lic-page arm_setup_wizard_page_<?php echo esc_attr($arm_sub_counter);?> ">
	<div class="arm-ws-account-setup">
		<div class="arm-ws-acco-logo">
			<a href="https://www.armemberplugin.com/" target="_blank">
				<img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm-logo-icon.webp' //phpcs:ignore ?>" alt="ARMember">
			</a>
		</div>
		<div class="arm-ws-acc-content">
			<h2 class="arm-ws-acc-heding"><?php esc_html_e('Account Setup','armember-membership');?></h2>
			<p class="arm-ws-acc-disc"><?php esc_html_e('Complete simple steps to get started.','armember-membership'); ?></p>
		</div>
	</div>
	<div class="arm-ws-steps-belt">
	<?php $arm_counter = 1;
		if($ARMemberLite->is_arm_pro_active)
		{
			$arm_counter = $arm_counter + 1;
		}
		$arm_pro_lincense_menu_item = '';
		echo apply_filters('arm_pro_lincense_menu_item',$arm_pro_lincense_menu_item); //phpcs:ignore
		?>	
		<div class="arm-ws-step-box  arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 9.9627V7.9627L15.075 7.1627L14.5125 5.8002L15.6125 3.5002L14.2 2.0877L11.9375 3.2252L10.575 2.6627L9.7125 0.262695H7.7125L6.925 2.6877L5.5375 3.2502L3.2375 2.1502L1.825 3.5627L2.9625 5.8252L2.4 7.1877L0 8.0377V10.0252L2.425 10.8252L2.9875 12.1877L1.8875 14.4877L3.3 15.9002L5.5625 14.7627L6.925 15.3252L7.7875 17.7252H9.775L10.5625 15.3002L11.95 14.7377L14.25 15.8377L15.6625 14.4252L14.5125 12.1627L15.1 10.8002L17.5 9.93769V9.9627ZM8.75 12.7502C6.675 12.7502 5 11.0752 5 9.00019C5 6.9252 6.675 5.2502 8.75 5.2502C10.825 5.2502 12.5 6.9252 12.5 9.00019C12.5 11.0752 10.825 12.7502 8.75 12.7502Z" fill="#637799"/>
					</svg>
				</span>
				
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('General Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box  arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" style="display:block; margin: 0 auto;" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6154 0H4.38462C3.22174 0 2.1065 0.461949 1.28422 1.28422C0.461949 2.1065 0 3.22174 0 4.38462V13.6154C0 14.7783 0.461949 15.8935 1.28422 16.7158C2.1065 17.5381 3.22174 18 4.38462 18H13.6154C14.7783 18 15.8935 17.5381 16.7158 16.7158C17.5381 15.8935 18 14.7783 18 13.6154V4.38462C18 3.22174 17.5381 2.1065 16.7158 1.28422C15.8935 0.461949 14.7783 0 13.6154 0ZM7.64308 12.2585L5.79692 14.1046C5.66711 14.2343 5.49115 14.3071 5.30769 14.3071C5.12423 14.3071 4.94827 14.2343 4.81846 14.1046L3.89538 13.1815C3.82737 13.1182 3.77281 13.0417 3.73497 12.9568C3.69713 12.8719 3.67679 12.7802 3.67515 12.6873C3.67351 12.5943 3.69061 12.502 3.72543 12.4158C3.76024 12.3296 3.81207 12.2512 3.87781 12.1855C3.94355 12.1198 4.02186 12.0679 4.10806 12.0331C4.19427 11.9983 4.2866 11.9812 4.37956 11.9828C4.47252 11.9845 4.56419 12.0048 4.64911 12.0427C4.73403 12.0805 4.81047 12.1351 4.87385 12.2031L5.30769 12.6369L6.66462 11.28C6.79585 11.1577 6.96943 11.0911 7.14879 11.0943C7.32814 11.0975 7.49927 11.1701 7.62611 11.297C7.75295 11.4238 7.82561 11.5949 7.82878 11.7743C7.83194 11.9536 7.76537 12.1272 7.64308 12.2585ZM7.64308 4.87385L5.79692 6.72C5.66711 6.84965 5.49115 6.92247 5.30769 6.92247C5.12423 6.92247 4.94827 6.84965 4.81846 6.72L3.89538 5.79692C3.7731 5.66568 3.70652 5.4921 3.70968 5.31275C3.71285 5.13339 3.78551 4.96227 3.91235 4.83543C4.03919 4.70858 4.21032 4.63593 4.38967 4.63276C4.56903 4.6296 4.74261 4.69617 4.87385 4.81846L5.30769 5.25231L6.66462 3.89538C6.79585 3.7731 6.96943 3.70652 7.14879 3.70968C7.32814 3.71285 7.49927 3.78551 7.62611 3.91235C7.75295 4.03919 7.82561 4.21032 7.82878 4.38967C7.83194 4.56903 7.76537 4.74261 7.64308 4.87385ZM13.6154 13.3846H10.8462C10.6625 13.3846 10.4865 13.3117 10.3566 13.1818C10.2268 13.052 10.1538 12.8759 10.1538 12.6923C10.1538 12.5087 10.2268 12.3326 10.3566 12.2028C10.4865 12.0729 10.6625 12 10.8462 12H13.6154C13.799 12 13.9751 12.0729 14.1049 12.2028C14.2348 12.3326 14.3077 12.5087 14.3077 12.6923C14.3077 12.8759 14.2348 13.052 14.1049 13.1818C13.9751 13.3117 13.799 13.3846 13.6154 13.3846ZM13.6154 6H10.8462C10.6625 6 10.4865 5.92706 10.3566 5.79723C10.2268 5.66739 10.1538 5.4913 10.1538 5.30769C10.1538 5.12408 10.2268 4.94799 10.3566 4.81816C10.4865 4.68832 10.6625 4.61538 10.8462 4.61538H13.6154C13.799 4.61538 13.9751 4.68832 14.1049 4.81816C14.2348 4.94799 14.3077 5.12408 14.3077 5.30769C14.3077 5.4913 14.2348 5.66739 14.1049 5.79723C13.9751 5.92706 13.799 6 13.6154 6Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Membership Plan','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box  arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.2009 13.2254C15.2009 14.3638 14.8307 15.3441 14.0904 16.1663C13.3501 16.9885 12.3884 17.4963 11.2054 17.6897V19.6429C11.2054 19.747 11.1719 19.8326 11.1049 19.8996C11.0379 19.9665 10.9524 20 10.8482 20H9.34152C9.24479 20 9.16109 19.9647 9.0904 19.894C9.01972 19.8233 8.98437 19.7396 8.98437 19.6429V17.6897C8.4933 17.6228 8.01897 17.5074 7.56138 17.3438C7.10379 17.1801 6.72619 17.0145 6.42857 16.8471C6.13095 16.6797 5.85565 16.5011 5.60268 16.3114C5.3497 16.1217 5.17671 15.9821 5.08371 15.8929C4.9907 15.8036 4.9256 15.7366 4.88839 15.692C4.7619 15.5357 4.75446 15.3832 4.86607 15.2344L6.01562 13.7277C6.06771 13.6533 6.15327 13.6086 6.27232 13.5938C6.38393 13.5789 6.47321 13.6124 6.54018 13.6942L6.5625 13.7165C7.40327 14.4531 8.30729 14.9182 9.27455 15.1116C9.54985 15.1711 9.82515 15.2009 10.1004 15.2009C10.7031 15.2009 11.2333 15.0409 11.6908 14.721C12.1484 14.401 12.3772 13.9472 12.3772 13.3594C12.3772 13.151 12.3214 12.9539 12.2098 12.7679C12.0982 12.5818 11.9736 12.4256 11.8359 12.2991C11.6983 12.1726 11.4807 12.0331 11.183 11.8806C10.8854 11.7281 10.6399 11.609 10.4464 11.5234C10.253 11.4379 9.95536 11.317 9.55357 11.1607C9.26339 11.0417 9.0346 10.9487 8.86719 10.8817C8.69978 10.8147 8.47098 10.7161 8.1808 10.5859C7.89062 10.4557 7.65811 10.3404 7.48326 10.24C7.30841 10.1395 7.09821 10.0074 6.85268 9.84375C6.60714 9.68006 6.40811 9.52195 6.25558 9.36942C6.10305 9.21689 5.94122 9.0346 5.77009 8.82255C5.59896 8.61049 5.46689 8.39472 5.37388 8.17522C5.28088 7.95573 5.20275 7.70833 5.13951 7.43304C5.07626 7.15774 5.04464 6.86756 5.04464 6.5625C5.04464 5.53571 5.40923 4.63542 6.13839 3.86161C6.86756 3.0878 7.81622 2.58929 8.98437 2.36607V0.357143C8.98437 0.260417 9.01972 0.176711 9.0904 0.106027C9.16109 0.0353423 9.24479 0 9.34152 0H10.8482C10.9524 0 11.0379 0.0334821 11.1049 0.100446C11.1719 0.167411 11.2054 0.252976 11.2054 0.357143V2.32143C11.6295 2.36607 12.0406 2.45164 12.4386 2.57812C12.8367 2.70461 13.1603 2.82924 13.4096 2.95201C13.6589 3.07478 13.8951 3.21429 14.1183 3.37054C14.3415 3.52679 14.4866 3.63467 14.5536 3.6942C14.6205 3.75372 14.6763 3.8058 14.721 3.85045C14.8475 3.98438 14.8661 4.12574 14.7768 4.27455L13.8728 5.90402C13.8132 6.01562 13.7277 6.07515 13.6161 6.08259C13.5119 6.10491 13.4115 6.07887 13.3147 6.00446C13.2924 5.98214 13.2385 5.9375 13.1529 5.87054C13.0673 5.80357 12.9222 5.70499 12.7176 5.57478C12.513 5.44457 12.2954 5.32552 12.0647 5.21763C11.8341 5.10975 11.5569 5.01302 11.2333 4.92746C10.9096 4.84189 10.5915 4.79911 10.279 4.79911C9.57217 4.79911 8.99554 4.95908 8.54911 5.27902C8.10268 5.59896 7.87946 6.0119 7.87946 6.51786C7.87946 6.71131 7.91109 6.88988 7.97433 7.05357C8.03757 7.21726 8.14732 7.37165 8.30357 7.51674C8.45982 7.66183 8.60677 7.7846 8.74442 7.88505C8.88207 7.98549 9.0904 8.10082 9.36942 8.23103C9.64844 8.36124 9.87351 8.46168 10.0446 8.53237C10.2158 8.60305 10.4762 8.70536 10.8259 8.83929C11.2202 8.9881 11.5216 9.10528 11.7299 9.19085C11.9382 9.27641 12.221 9.40662 12.5781 9.58147C12.9353 9.75632 13.2161 9.91443 13.4208 10.0558C13.6254 10.1972 13.856 10.3832 14.1127 10.6138C14.3694 10.8445 14.5666 11.0807 14.7042 11.3225C14.8419 11.5644 14.9591 11.849 15.0558 12.1763C15.1525 12.5037 15.2009 12.8534 15.2009 13.2254Z" fill="#637799"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Payment Options','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box  arm-ws-step-complate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" style="display:block; margin: 0 auto;" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clip-path="url(#clip0_2972_7739)">
							<path d="M10.5416 8.33333C9.85408 6.39167 8.00825 5 5.83325 5C3.07075 5 0.833252 7.2375 0.833252 10C0.833252 12.7625 3.07075 15 5.83325 15C8.00825 15 9.85408 13.6083 10.5416 11.6667H14.1666V15H17.4999V11.6667H19.1666V8.33333H10.5416ZM5.83325 11.6667C4.91242 11.6667 4.16659 10.9208 4.16659 10C4.16659 9.07917 4.91242 8.33333 5.83325 8.33333C6.75409 8.33333 7.49992 9.07917 7.49992 10C7.49992 10.9208 6.75409 11.6667 5.83325 11.6667Z" fill="#637799"/>
						</g>
						<defs>
							<clipPath id="clip0_2972_7739">
								<rect width="20" height="20" fill="white"/>
							</clipPath>
						</defs>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Content Access','armember-membership'); ?></div>
		</div>
	
		<div class="arm-ws-step-box arm-ws-step-activate" data-page_id='<?php echo esc_attr($arm_counter); $arm_counter += 1;?>'>
			<div class="arm-ws-steps-icon-wrapper">
				<span>
					<svg class="arm-ws-step-activate-svg" width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.00007 9.89051C2.04546 9.87493 2.03478 9.82954 2.04342 9.7985C2.35381 8.68316 3.66188 8.32595 4.48772 9.13906C5.26014 9.89957 6.022 10.6708 6.7885 11.4373C6.88833 11.5371 6.9896 11.6356 7.08678 11.7378C7.12965 11.783 7.16348 11.7963 7.20492 11.7392C7.22078 11.7174 7.24296 11.7001 7.26229 11.6808C9.32991 9.61319 11.3983 7.54638 13.4643 5.47712C13.7718 5.16918 14.1313 4.98564 14.5699 5.00088C15.1689 5.02171 15.614 5.30529 15.8609 5.85265C16.1076 6.39967 16.0201 6.91668 15.6468 7.38489C15.6044 7.43811 15.5553 7.48629 15.5071 7.53461C13.0821 9.95993 10.6572 12.3852 8.23172 14.81C7.73003 15.3115 7.08705 15.4322 6.49784 15.1393C6.34975 15.0656 6.21977 14.9677 6.10305 14.8509C4.88904 13.6366 3.67509 12.4222 2.46039 11.2086C2.2565 11.0048 2.1093 10.7699 2.0407 10.4876C2.03416 10.4609 2.0424 10.4202 2 10.4098C2.00007 10.2366 2.00007 10.0636 2.00007 9.89051Z" fill="#637799"/>
					</svg>

					<svg class="arm-ws-step-complate-svg" style="display:none;" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M8.21674 14.9999C8.10247 14.9996 7.98951 14.9757 7.88486 14.9298C7.7802 14.8839 7.6861 14.817 7.6084 14.7333L3.5584 10.4249C3.40701 10.2636 3.32591 10.0487 3.33294 9.82758C3.33997 9.60644 3.43456 9.39715 3.5959 9.24576C3.75724 9.09436 3.97212 9.01326 4.19325 9.0203C4.41439 9.02733 4.62368 9.12192 4.77507 9.28326L8.20841 12.9416L15.2167 5.27493C15.2879 5.18636 15.3763 5.11327 15.4767 5.06014C15.5771 5.00702 15.6873 4.97498 15.8006 4.96599C15.9138 4.957 16.0277 4.97126 16.1352 5.00788C16.2427 5.04451 16.3416 5.10273 16.4258 5.17896C16.51 5.2552 16.5777 5.34785 16.6248 5.45121C16.6719 5.55458 16.6974 5.66648 16.6997 5.78004C16.7019 5.89361 16.681 6.00644 16.638 6.1116C16.5951 6.21677 16.5312 6.31205 16.4501 6.39159L8.83341 14.7249C8.75644 14.8102 8.66267 14.8787 8.55798 14.926C8.45329 14.9733 8.33995 14.9985 8.22507 14.9999H8.21674Z" fill="white"/>
					</svg>
				</span>
			</div>
			<div class="arm-ws-steps-text"><?php esc_html_e('Complete','armember-membership'); ?></div>
		</div>
	</div>
	<?php
	//PAGES PAGE URLS
	$page_settings = $arm_global_settings->global_settings;
	$register_page_id = $page_settings['register_page_id'];
	$edit_profile_page_id = $page_settings['edit_profile_page_id'];
	$login_page_id = $page_settings['login_page_id'];
	$forgot_password_page_id = $page_settings['forgot_password_page_id'];
	$change_password_page_id = $page_settings['change_password_page_id'];
	?>
	<div class="arm-ws-license-part-content arm-ws-account-setup arm-thank-you-wapper">
		<div class="arm-setup-comp-video arm-setup-comp-congrats arm-setup-wizard-celebration-show">
			
			<div class="arm-thank-you-text"><?php esc_html_e('Congratulations!','armember-membership');?></div>
			<div class="arm-thank-you-disc"><?php esc_html_e('Hurray!! Everything is ready to create your first membership site','armember-membership');?></div>
			<div class="arm-thank-you-content"><?php esc_html_e('Your site is ready & below are the details','armember-membership');?></div>
		</div>
		<div class="arm-thank-you-short-code-list-row"> 
			<div class="arm-short-code-wapper" id="arm_member_setup_link">
				<h4 class="arm-short-code-heding"><?php esc_html_e('Membership registration','armember-membership');?></h4>
				<div class="arm-short-code-text">
					<div class="arm_setup_shortcode_text">
						<span class="armCopyText arm_setupform"><?php echo esc_html(get_permalink($register_page_id));?></span>
						<img class="arm-ws-acc-img arm_setup_form_copy_text arm_setup_click_to_copy_text" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/content_copy_icon.webp' //phpcs:ignore?>" alt="armember" data-code="<?php echo esc_attr(get_permalink($register_page_id));?>">
					</div>
					<span class="arm_copied_text arm_setup_copied_text"><img src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL)."/copied_ok.png" //phpcs:ignore ?>' alt="ok"><?php esc_html_e('Link Copied!','armember-membership');?></span>
				</div>
			</div>
			<div class="arm-short-code-wapper" id="arm_login_link">
				<h4 class="arm-short-code-heding"><?php esc_html_e('Member Login','armember-membership');?></h4>
				<div class="arm-short-code-text">
					<span class="armCopyText"><?php echo esc_html(get_permalink($login_page_id));?> </span>
					<img class="arm-ws-acc-img arm_setup_click_to_copy_text" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/content_copy_icon.webp' //phpcs:ignore ?>" alt="armember" data-code="<?php echo esc_attr(get_permalink($login_page_id));?>">
					<span class="arm_copied_text arm_setup_copied_text"><img src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL)."/copied_ok.png" //phpcs:ignore ?>' alt="ok"><?php esc_html_e('Link Copied!','armember-membership');?></span>
				</div>
			</div>
			<div class="arm-short-code-wapper" id="arm_edit_profile_link">
				<h4 class="arm-short-code-heding"><?php esc_html_e('Edit Profile','armember-membership');?></h4>
				<div class="arm-short-code-text">
					<span class="armCopyText"><?php echo esc_html(get_permalink($edit_profile_page_id));?></span> 
					<img class="arm-ws-acc-img arm_setup_click_to_copy_text" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/content_copy_icon.webp' //phpcs:ignore ?>" alt="armember" data-code="<?php echo esc_html(get_permalink($edit_profile_page_id));?>">
					<span class="arm_copied_text arm_setup_copied_text"><img src='<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL)."/copied_ok.png" //phpcs:ignore ?>' alt="ok"><?php esc_html_e('Link Copied!','armember-membership');?></span>
				</div>
			</div>
			
		</div>
		<div class="arm-setup-comp-video arm-setup-wizard-celebration-show">
			<a href="javascript:void(0)" onclick="armopensetupvideos();"><img class="arm-ws-acc-img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/suceesfully-setup-video.webp' //phpcs:ignore ?>" alt="ARMember"></a>
		</div>
		<div class="arm-ws-get-start-but-sec">
			<a href="<?php echo esc_attr(admin_url('admin.php?page=' . $arm_slugs->manage_members));?>" class="arm-wsc-btn arm-wsc-btn--primary arm_setup_redirection_btn"><?php esc_html_e('Explore ARMember','armember-membership');?></a>
		</div>
		<div class="arm-thank-you-content arm-use-link"><b><?php esc_html_e('Useful Links','armember-membership');?></b></div>
		<a href="https://armemberplugin.com/documentation" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-usefull-links">
			<?php esc_html_e('Documentation','armember-membership'); ?>
		</a>
		<a href="https://wordpress.org/support/plugin/armember-membership/" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-usefull-links">
			<?php esc_html_e('Get Forum Support','armember-membership'); ?>
		</a>
		<a href="https://ideas.armemberplugin.com" target="_blank" class="arm-wsc-btn arm-wsc-btn--primary arm-usefull-links">
			<?php esc_html_e('Explore Features','armember-membership'); ?>
		</a>
	</div>	
</div>
<div class="arm_setup_skip_div">
<a href="javascript:void(0)" class="arm_skip_setup_process"><?php esc_html_e('Skip The Wizard and Setup Manually','armember-membership');?></a>
<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
</div>
<div id="arm_document_setup_video_popup" class="popup_wrapper arm_document_setup_video_popup">
	<div class="popup_wrapper_inner">
		<div class="popup_header">
			<span class="popup_close_btn arm_popup_close_btn" onclick="armHideDocumentSetupVideo();"></span>
			<span class="popup_header_text"><?php esc_html_e('ARMember Basic Tutorial', 'armember-membership'); ?></span>
		</div>
		<div class="popup_content_text">
			<iframe src="<?php echo esc_attr(MEMBERSHIPLITE_VIDEO_URL) ?>" allowfullscreen="" frameborder="0"> </iframe>
		</div>
		<div class="armclear"></div>
		<div class="popup_content_btn popup_footer">
			<div class="popup_content_btn_wrapper">
				<button class="arm_cancel_btn popup_close_btn" onclick="armHideDocumentSetupVideo();" type="button"><?php esc_html_e('Close', 'armember-membership') ?></button>
			</div>
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<?php 
    echo $ARMemberLite->arm_get_need_help_html_content('get-started'); //phpcs:ignore
?>