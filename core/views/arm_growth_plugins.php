<?php 
	global $arm_social_feature,$ARMemberLite;
	if(!$ARMemberLite->is_arm_pro_active){
		$growth_plugins_content = $arm_social_feature->growth_plugins_content();

		$payment_gateways_addon = '21+';
		$total_addons = '51+';
		$total_inbuilt_addon = '56+';
		$discount = '82%';
		$downloads = '3,00,000+';
		$support_period = '6 Months';
		if( !empty($growth_plugins_content) ) {
			$growth_plugins_content_data = json_decode($growth_plugins_content, true);
			if( !empty($growth_plugins_content_data) && is_array($growth_plugins_content_data) ) {
				$payment_gateways_addon = isset($growth_plugins_content_data['payment_gateways_addon']) ? $growth_plugins_content_data['payment_gateways_addon'] : $payment_gateways_addon;
				$total_addons = isset($growth_plugins_content_data['total_addons']) ? $growth_plugins_content_data['total_addons'] : $total_addons;
				$total_inbuilt_addon = isset($growth_plugins_content_data['total_inbuilt_addon']) ? $growth_plugins_content_data['total_inbuilt_addon'] : $total_inbuilt_addon;
				$discount = isset($growth_plugins_content_data['discount']) ? $growth_plugins_content_data['discount'] : $discount;
				$downloads = isset($growth_plugins_content_data['downloads']) ? $growth_plugins_content_data['downloads'] : $downloads;
				$support_period = isset($growth_plugins_content_data['support_period']) ? $growth_plugins_content_data['support_period'] : $support_period;
			}
		}
	}
?>

<div class="wrap arm_page arm_feature_settings_main_wrapper arm_groth_plugin_page">
	<div class="content_wrapper arm_feature_settings_content" id="content_wrapper">
		<div class="page_title"></div>
		<div class="armclear"></div>
		<div class="arm_feature_settings_wrapper">            
			<div class="arm_feature_settings_container arm-gt-mainwrapper">
				<?php if(!$ARMemberLite->is_arm_pro_active){?>
			
				<div class="arm_set_bg_image">
					<div class="arm-mlc-head-wrap">
						<div class="arm-mlc-left-heading">
							<div class="arm-gt-heading-cls">
								<div class="arm-gt-page-heading"> Unlock Endless Capabilities with <span style="color: #FF46AC">ARMember Pro</span> the
									<span class="arm-gt-page-heading arm-page-heading-highlight">#1 WordPress Membership Plugin</span>
								</div>
							</div>
						</div>
					</div>
					<div class="arm-mlc-head-wrap">
						<div class="arm-mlc-left-heading">
							<div class="arm-gt-heading-inner-cls">
								<div class="arm-gt-page-inner-contain">
									Elevate your Membership website to new heights with the ARMember Pro upgrade. Create a fully customized membership website that boasts standard payment gateways support, robust email marketing tools, and a wide range of additional features. There is only one step left before you unlock the true potential of ARMember Pro. Don't miss out on this opportunity to enhance the capabilities of your website and grow business.
								</div>
							</div>
							<div class="arm-gt-one-time-sec-wrapper">
								<div class="arm-gt-one-time-sec">
									<div class="arm-gt-one-time-sec-first">
										<span class="arm-gt-one-time-sec-first-img"></span>
										<div class="arm-gt-one-time-sec-content">
											<h4 class="arm-gt-one-time-sec-heding"><?php echo esc_html(sanitize_text_field( $payment_gateways_addon )); ?> Worldwide</h4>
											<p class="arm-gt-one-time-sec-disc">Payment Gateways</p>
										</div>
									</div>
									<div class="arm-gt-one-time-sec-first arm-gt-addon-bun-sec">
										<span class="arm-gt-one-time-sec-first-img"></span>
										<div class="arm-gt-one-time-sec-content">
											<h4 class="arm-gt-one-time-sec-heding"> <?php echo esc_html(sanitize_text_field( $total_inbuilt_addon )); ?> Inbuilt Addons</h4>
											<p class="arm-gt-one-time-sec-disc">included with Pro</p>
										</div>
									</div>
								</div>
							</div>
							<div class="arm-gt-heading-inner-heading"> <span></span><?php echo esc_html(sanitize_text_field( $downloads )); ?> Users ❤️ ARMember <span class="arm-gt-orange-line"></span></div>					
						</div>
					</div>
				</div>
				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-pre-features-box">
							<div class="arm-feature-list-head">
								<span> Premium </span> <span class="arm-page-heading-highlight">Features Highlight</span>
							</div>
							<ul class="arm-feature-list-cls">
								<li class="arm-feature-list-li"> Pay Per Post (Paid Post) </li>
								<li class="arm-feature-list-li"> Built-in Live Form Builder </li>
								<li class="arm-feature-list-li"> Drip Content Feature Support </li>
								<li class="arm-feature-list-li"> Unlimited Membership Plans </li>
								<li class="arm-feature-list-li"> Membership Card Support </li>
								<li class="arm-feature-list-li"> Powerful <?php echo esc_html(sanitize_text_field( $total_addons )); ?> External Addons </li>
								<li class="arm-feature-list-li"> Pro-rating Membership Plans </li>
								<li class="arm-feature-list-li"> Advanced Content Restriction </li>
								<li class="arm-feature-list-li"> Sell Online Courses Like LMS </li>
							</ul>
							<ul>
								<li class="arm-feature-list-li arm-feature-list-last"> <?php echo esc_html(sanitize_text_field( $support_period )); ?> of Premium Support </li>
							</ul>
							
						</div>
					</div>
				</div>
				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-upgrade-premium-cls">
							<a href="https://www.armemberplugin.com/pricing/?utm_source=lite_version&utm_medium=wordpress_org&utm_campaign=upgrade_to_pro" target="_blank" class="arm-upgrade-premium-btn"> <?php esc_html_e('Upgrade to ARMember Pro','armember-membership'); ?></a>
						</div>
					</div>
				</div>
				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<span class="arm-growth-tools-spacer"></span>
					</div>
				</div>
				<?php }?>
				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-gt-page-inner-block-heading"> Our <span class="arm-page-heading-highlight"> Family WordPress Plugins </span>
						</div>
					</div>
				</div>

				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-gt-heading-main-inner-cls">
							<div class="arm-gt-page-inner-contain">
								You will get the same user-friendly experience throughout all of our plugins. Enjoy single-window 24/7
								support for all our plugins. All of our plugins are compatible with each other.
							</div>
						</div>
					</div>
				</div>
				<div class="arm-mlc-sec-space"></div>
				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-plugin-details-cls">
							<div class="arm-gt-plugin-icon">
								<span class="arm-plugin-icon arm-afp-icon"></span>
							</div>
							<div class="arm-gt-plugin-dec">
								<div class="arm-plugin-heading afp-heading"> AffiliatePress <span class="arm-plugin-heading-cls">- WordPress Affiliate Plugin </span></div>
								<div class="arm-plugin-heading-desc">Seamlessly compatible with WooCommerce, ARMember, BookingPress, and 20+ other platforms, AffiliatePress makes running affiliate programs effortless. With multiple commission types, powerful tracking, and a modern interface, it's the smarter choice over costly and complex affiliate tools.</div>
								<div class="arm-plugin-key-feature"> Key Features: </div>
								<ul class="arm-feature-list-cls-plugin-dec">
									<li class="arm-feature-list-li-plugin"> Easy Affiliates Management </li>
									<li class="arm-feature-list-li-plugin"> Multiple Commission Types </li>
									<li class="arm-feature-list-li-plugin"> Customized Affiliate Signup </li>
									<li class="arm-feature-list-li-plugin"> Advanced Reports </li>
									<li class="arm-feature-list-li-plugin"> Realtime Commission Tracking </li>
									<li class="arm-feature-list-li-plugin"> AI-Powered Fraud Detection </li>
								</ul>
								<div style="margin-top: 40px;" class="arm-gt-product-btn-seprator">
									<a href="https://www.affiliatepressplugin.com/" target="_blank" class="arm-learnmore-btn afp-plugin"> Learn More </a>
									<?php if (file_exists( WP_PLUGIN_DIR . '/affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php')  ) { 
										if((is_plugin_active('affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php'))){?>
											<button disabled="disabled" type="button" class="el-button arm-btn arm-install-btn afp-plugin el-button--default is-disabled">
												<span>
													<span class="arm-btn__label"> Activated </span>
													<div class="arm-btn--loader__circles">
														<div></div>
														<div></div>
														<div></div>
													</div>
												</span>
											</button>
										<?php	}
										else{?>
										<button type="button" onclick="arm_activate_plugins('affiliatepress',this)" class="el-button arm-btn arm-install-btn afp-plugin el-button--default">
											<span>
												<span class="arm-btn__label"> Activate </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
										<?php }
									 } else { ?>	
										<button type="button" onclick="arm_download_plugins('affiliatepress',this)" class="el-button arm-btn arm-install-btn afp-plugin el-button--default ">
											<span>
												<span class="arm-btn__label"> Install </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
									<?php }  ?>	
								</div>
							</div>
							<hr class="arm-section-line">
						</div>
					</div>
				</div>

				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-plugin-details-cls">
							<div class="arm-gt-plugin-icon">
								<span class="arm-plugin-icon arm-arm-icon"></span>
							</div>
							<div class="arm-gt-plugin-dec">
								<div class="arm-plugin-heading"> BookingPress <span class="arm-plugin-heading-cls"> - WordPress Booking Plugin</span></div>
								<div class="arm-plugin-heading-desc"> Imagine a WordPress BookingPress Plugin that's remarkably user-friendly, equipped with an extensive feature set, excelling in performance, and featuring a sleek modern interface. It distinguishes itself as a superior option, surpassing even the most popular Appointment Booking plugins available.</div>
								<div class="arm-plugin-key-feature"> Key Features: </div>
								<ul class="arm-feature-list-cls-plugin-dec">
									<li class="arm-feature-list-li-plugin"> Great UI And UX </li>
									<li class="arm-feature-list-li-plugin"> Interactive booking wizard </li>
									<li class="arm-feature-list-li-plugin"> Online Payment Gateways </li>
									<li class="arm-feature-list-li-plugin"> Offline Payment </li>
									<li class="arm-feature-list-li-plugin"> Built-in Spam Facility </li>
									<li class="arm-feature-list-li-plugin"> Custom Email Notifications </li>
								</ul>
								<div style="margin-top: 40px;" class="arm-gt-product-btn-seprator">
								<a href="https://www.bookingpressplugin.com/" target="_blank" class="arm-learnmore-btn"> Learn More </a>
								<?php if ( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php')  ) {
									
									if(is_plugin_active('bookingpress-appointment-booking/bookingpress-appointment-booking.php')){ ?>
										<button disabled="disabled" type="button" class="arm-btn arm-install-btn arm-plugin is-disabled">
											<span>
												<span class="arm-btn__label"> Activated </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
									<?php }
									else{ ?>
										<button type="button" onclick="arm_activate_plugins('bookingpress',this)" class="el-button arm-btn arm-install-btn arm-plugin el-button--default">
											<span>
												<span class="arm-btn__label"> Activate </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
										<?php }
									} else { ?>	
										<button type="button" onclick="arm_download_plugins('bookingpress',this)" class="el-button arm-btn arm-install-btn arm-plugin el-button--default">
											<span>
												<span class="arm-btn__label"> Install </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
									<?php }  ?>
								</div>
							</div>
							<hr class="arm-section-line">
						</div>
					</div>
				</div>

				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-plugin-details-cls">
							<div class="arm-gt-plugin-icon">
								<span class="arm-plugin-icon arm-arf-icon"></span>
							</div>
							<div class="arm-gt-plugin-dec">
								<div class="arm-plugin-heading arf-heading"> ARForms <span class="arm-plugin-heading-cls">- WordPress Form Builder Plugin </span></div>
								<div class="arm-plugin-heading-desc"> ARForms is an all-in-one WordPress form builder plugin. It not only allows you to create contact forms for your site but also empowers you to build WordPress forms such as feedback forms, survey forms, and various other types of forms with responsive designs. </div>
								<div class="arm-plugin-key-feature"> Key Features: </div>
								<ul class="arm-feature-list-cls-plugin-dec">
									<li class="arm-feature-list-li-plugin"> Real-Time Form Editor </li>
									<li class="arm-feature-list-li-plugin"> Multi-Column Option </li>
									<li class="arm-feature-list-li-plugin"> Styling &amp; Unlimited Color Option </li>
									<li class="arm-feature-list-li-plugin"> Built-In Anti Spam Protection </li>
									<li class="arm-feature-list-li-plugin"> Material &amp; Rounded Style Forms </li>
									<li class="arm-feature-list-li-plugin"> Popular Page Builders Support </li>
								</ul>
								<div style="margin-top: 40px;" class="arm-gt-product-btn-seprator">
									<a href="https://www.arformsplugin.com/" target="_blank" class="arm-learnmore-btn arf-plugin"> Learn More </a>
									<?php if ( file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php') ) {
										if((is_plugin_active('arforms-form-builder/arforms-form-builder.php'))){?>
										<button disabled="disabled" type="button" class="el-button el-button arm-btn arm-install-btn arf-plugin el-button--default is-disabled"><!----><!---->
											<span>
												<span class="arm-btn__label"> Activated </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
										<?php }
										else{?>
										<button type="button" onclick="arm_activate_plugins('arforms',this)" class="el-button arm-btn arm-install-btn arf-plugin el-button--default">
											<span>
												<span class="arm-btn__label"> Activate </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
										<?php }
									 } else { ?>
										<button type="button" onclick="arm_download_plugins('arforms',this)" class="el-button arm-btn arm-install-btn arf-plugin el-button--default"><!----><!---->
											<span>
												<span class="arm-btn__label"> Install </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
									<?php }  ?>	
								</div>
							</div>
							<hr class="arm-section-line">
						</div>
					</div>
				</div>

				<div class="arm-mlc-head-wrap">
					<div class="arm-mlc-left-heading">
						<div class="arm-plugin-details-cls">
							<div class="arm-gt-plugin-icon">
								<span class="arm-plugin-icon arm-arp-icon"></span>
							</div>
							<div class="arm-gt-plugin-dec">
								<div class="arm-plugin-heading arp-heading"> ARPrice <span class="arm-plugin-heading-cls">- WordPress Pricing Table Plugin </span></div>
								<div class="arm-plugin-heading-desc">ARPrice is a WordPress pricing table plugin that enables you to effortlessly craft responsive pricing tables and plan comparison tables. With its powerful and flexible real-time editor, you can swiftly design pricing tables, using multiple templates, to suit various WordPress themes. </div>
								<div class="arm-plugin-key-feature"> Key Features: </div>
								<ul class="arm-feature-list-cls-plugin-dec">
									<li class="arm-feature-list-li-plugin"> Real-time Pricing Table Editor </li>
									<li class="arm-feature-list-li-plugin"> Unlimited Color Options </li>
									<li class="arm-feature-list-li-plugin"> Create Team Showcases </li>
									<li class="arm-feature-list-li-plugin"> Translation Ready </li>
									<li class="arm-feature-list-li-plugin"> Responsive Pricing Tables </li>
									<li class="arm-feature-list-li-plugin"> Multi-Site Compatible </li>
								</ul>
								<div style="margin-top: 40px;" class="arm-gt-product-btn-seprator">
									<a href="https://www.arpriceplugin.com/" target="_blank" class="arm-learnmore-btn arp-plugin"> Learn More </a>
									<?php if (file_exists( WP_PLUGIN_DIR . '/arprice-responsive-pricing-table/arprice-responsive-pricing-table.php')  ) { 
										if((is_plugin_active('arprice-responsive-pricing-table/arprice-responsive-pricing-table.php'))){?>
											<button disabled="disabled" type="button" class="el-button arm-btn arm-install-btn arp-plugin el-button--default is-disabled">
												<span>
													<span class="arm-btn__label"> Installed </span>
													<div class="arm-btn--loader__circles">
														<div></div>
														<div></div>
														<div></div>
													</div>
												</span>
											</button>
										<?php	}
										else{?>
										<button type="button" onclick="arm_activate_plugins('arprice',this)" class="el-button arm-btn arm-install-btn arp-plugin el-button--default">
											<span>
												<span class="arm-btn__label"> Activate </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
										<?php }
									 } else { ?>	
										<button type="button" onclick="arm_download_plugins('arprice',this)" class="el-button arm-btn arm-install-btn arp-plugin el-button--default ">
											<span>
												<span class="arm-btn__label"> Install </span>
												<div class="arm-btn--loader__circles">
													<div></div>
													<div></div>
													<div></div>
												</div>
											</span>
										</button>
									<?php }  ?>	
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="arm-mlc-sec-space"></div>
				<input type="hidden" name="arm_download_plugins_flag" id="arm_download_plugins_flag" value="0">
				<input type="hidden" name="arm_activate_plugins_flag" id="arm_activate_plugins_flag" value="0">

			</div>
            <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</div>
		<div class="armclear"></div>
	</div>
</div>