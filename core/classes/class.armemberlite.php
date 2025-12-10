<?php 
if ( ! class_exists( 'ARM_common_lite' ) ) {
    class ARM_common_lite {       
		function __construct() {
            global $wpdb, $ARMemberLite, $arm_slugs;

            add_action( 'admin_footer', array( $this, 'arm_deactivate_feedback_popup' ), 1 );

        }
        function arm_deactivate_feedback_popup() {
			global $ARMemberLite;
			$question_options                      = array();
			$question_options['list_data_options'] = array(
				'setup-difficult'  => esc_html__( 'Set up is too difficult', 'armember-membership' ),
				'docs-improvement' => esc_html__( 'Lack of documentation', 'armember-membership' ),
				'features'         => esc_html__( 'Not the features I wanted', 'armember-membership' ),
				'better-plugin'    => esc_html__( 'Found a better plugin', 'armember-membership' ),
				'incompatibility'  => esc_html__( 'Incompatible with theme or plugin', 'armember-membership' ),
				'maintenance'      => esc_html__( 'Other', 'armember-membership' ),
			);

			$html2 = '<div class="armlite-deactivate-confirm-head"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="#fff"><path d="M4.47 21h15.06c1.54 0 2.5-1.67 1.73-3L13.73 4.99c-.77-1.33-2.69-1.33-3.46 0L2.74 18c-.77 1.33.19 3 1.73 3zM12 14c-.55 0-1-.45-1-1v-2c0-.55.45-1 1-1s1 .45 1 1v2c0 .55-.45 1-1 1zm1 4h-2v-2h2v2z"/></svg><p><strong>' . esc_html__('ARMember Lite plugin Deactivation', 'armember-membership').'.</strong></p></div>';
            $html2 .= '<div class="armlite-deactivate-form-body">';
            $html2 .= '<div class="armlite-deactivate-options">';

            $html2 .= '<p><strong>' . esc_html('You are using ARMember Pro plugin on your website and it is an extension to ARMember Lite, so, If you deactivate ARMember Lite then it will automatically deactivate ARMember Pro', 'armember-membership') . '.</strong></p></br>';

            $html2 .= '<p><label><input type="checkbox" name="armlite-risk-confirm" id="armlite-risk-confirm" value="risk-confirm">'.esc_html__('I understand the risk', 'armember-membership').'</label></p>';
            $html2 .= '</div>';
            $html2 .= '<hr/>';
            $html2 .= '</div>';
            $html2 .= '<div class="armlite-deactivate-form-footer"><p>';                            
            $html2 .= '<button id="armlite-deactivate-cancel-btn" class="arm-deactivate-btn arm-deactivate-btn-cancel" >'.__('Cancel', 'armember-membership')
            . '</button>';
            $html2 .= '<button id="armlite-deactivate-submit-btn" disabled=disabled class="arm-deactivate-btn button button-primary" href="#">'.esc_html__('Proceed', 'armember-membership')
            . '</button></p>';
            $html2 .= '</div>';

			$html  = '<div class="armlite-deactivate-form-head"><strong>' . esc_html__( 'ARMember Lite - Sorry to see you go', 'armember-membership' ) . '</strong></div>';
			$html .= '<div class="armlite-deactivate-form-body">';

			if ( is_array( $question_options['list_data_options'] ) ) {
				$html .= '<div class="armlite-deactivate-options">';
				$html .= '<p><strong>' . esc_html( esc_html__( 'Before you deactivate the ARMember Lite plugin, would you quickly give us your reason for doing so?', 'armember-membership' ) ) . '</strong></p><p>';

				foreach ( $question_options['list_data_options'] as $key => $option ) {
					$html .= '<input type="radio" name="armlite-deactivate-reason" id="' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '"> <label for="' . esc_attr( $key ) . '">' . esc_attr( $option ) . '</label><br>';
				}

				$html .= '</p><label id="armlite-deactivate-details-label" for="armlite-deactivate-reasons"><strong>' . esc_html( esc_html__( 'How could we improve ?', 'armember-membership' ) ) . '</strong></label><textarea name="armlite-deactivate-details" id="armlite-deactivate-details" rows="2" style="width:100%"></textarea>';

				$html .= '</div>';
			}
			$html .= '<hr/>';

			$html .= '</div>';
			$html .= '<p class="deactivating-spinner"><span class="spinner"></span> ' . esc_html__( 'Submitting form', 'armember-membership' ) . '</p>';
			$html .= '<div class="armlite-deactivate-form-footer"><p>';
			$html .= '<label for="armlite_anonymous" title="'
				. esc_html__( 'If you UNCHECK this then your email address will be sent along with your feedback. This can be used by armlite to get back to you for more info or a solution.', 'armember-membership' )
				. '"><input type="checkbox" name="armlite-deactivate-tracking" id="armlite_anonymous"> ' . esc_html__( 'Send anonymous', 'armember-membership' ) . '</label><br>';
			$html .= '<a id="armlite-deactivate-submit-form" class="button button-primary" href="#"><span>'
				. esc_html__( 'Submit', 'armember-membership' )
				. '&nbsp;and&nbsp;'. esc_html__( 'Deactivate', 'armember-membership' ).'</span></a>';
			$html .= '</p></div>';
			?>
			<div class="armlite-deactivate-form-bg"></div>
			<style type="text/css">
				.arm-deactivate-btn{display: inline-block;font-weight: 400;text-align: center;white-space;vertical-align: nowrap;user-select: none;border: 1px solid transparent;padding: .375rem .75rem;font-size:1rem;line-height:1.5;border-radius:0.25rem;transition:color .15s }
				.arm-deactivate-btn:hover
				{
					color: white;
				}                    
				.arm-deactivate-btn-cancel:hover ,.arm-deactivate-btn-cancel {
					color: #2c3338;
					background-color: #fff;
					border-color:#2c3338 !important;
					/* margin-left:350px; */
					margin-right: 10px;
				}
				.armlite-deactivate-form-active .armlite-deactivate-form-bg {background: rgba( 0, 0, 0, .5 );position: fixed;top: 0;left: 0;width: 100%;height: 100%; z-index: 9;}
				.armlite-deactivate-form-wrapper {position: relative;z-index: 999;display: none; }
				.armlite-deactivate-form-active .armlite-deactivate-form-wrapper {display: inline-block;}
				.armlite-deactivate-form {display: none;}
				.armlite-deactivate-form-active .armlite-deactivate-form {position: absolute;bottom: 30px;left: 0;max-width: 500px;min-width: 360px;background: #fff;white-space: normal;}
				.armlite-deactivate-form-head {background: #005aee;color: #fff;padding: 8px 18px;}
				.armlite-deactivate-form-body {padding: 8px 18px 0;color: #444;}
				.armlite-deactivate-form-body label[for="armlite-remove-settings"] {font-weight: bold;}
				.deactivating-spinner {display: none;}
				.deactivating-spinner .spinner {float: none;margin: 4px 4px 0 18px;vertical-align: bottom;visibility: visible;}
				.armlite-deactivate-form-footer {padding: 0 18px 8px;}
				.armlite-deactivate-form-footer label[for="armlite_anonymous"] {visibility: hidden;}
				.armlite-deactivate-form-footer p {display: flex;align-items: center;justify-content: space-between;margin: 0;}
				<?php /* #armlite-deactivate-submit-form span {display: none;} */ ?>
				.armlite-deactivate-form.process-response .armlite-deactivate-form-body,.armlite-deactivate-form.process-response .armlite-deactivate-form-footer {position: relative;}
				.armlite-deactivate-form.process-response .armlite-deactivate-form-body:after,.armlite-deactivate-form.process-response .armlite-deactivate-form-footer:after {content: "";display: block;position: absolute;top: 0;left: 0;width: 100%;height: 100%;background-color: rgba( 255, 255, 255, .5 );}
				.armlite-deactivate-confirm-head p{color: #fff; padding-left:10px}
                .armlite-deactivate-confirm-head{padding: 4px 18px; background:red; }
				.armlite-confirm-deactivate-wrapper{
                        width:550px;
                        max-width:600px !important;
                    }
                    .armlite-confirm-deactivate-wrapper .armlite-deactivate-confirm-head strong {
                        margin-bottom:unset;
                    }
                    .armlite-confirm-deactivate-wrapper .armlite-deactivate-confirm-head {
                        display: flex;
                        align-items: center;
                    }
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					var armlite_deactivateURL = $("#armlite-deactivate-link-<?php echo esc_attr( 'armember-membership' ); ?>")
						armlite_formContainer = $('#armlite-deactivate-form-<?php echo esc_attr( 'armember-membership' ); ?>'),
						armlite_deactivated = true,
						armlite_detailsStrings = {
							'setup-difficult' : '<?php echo esc_html__( 'What was the dificult part?', 'armember-membership' ); ?>',
							'docs-improvement' : '<?php echo esc_html__( 'What can we describe more?', 'armember-membership' ); ?>',
							'features' : '<?php echo esc_html__( 'How could we improve?', 'armember-membership' ); ?>',
							'better-plugin' : '<?php echo esc_html__( 'Can you mention it?', 'armember-membership' ); ?>',
							'incompatibility' : '<?php echo esc_html__( 'With what plugin or theme is incompatible?', 'armember-membership' ); ?>',
							'maintenance' : '<?php echo esc_html__( 'Please specify', 'armember-membership' ); ?>',
						};

					jQuery( armlite_deactivateURL).attr('onclick', "javascript:event.preventDefault();");
					jQuery( armlite_deactivateURL ).on("click", function(){

						function ARMLiteSubmitData(armlite_data, armlite_formContainer)
						{
							armlite_data['action']          = 'armlite_deactivate_plugin';
							armlite_data['security']        = '<?php echo esc_attr(wp_create_nonce( 'armlite_deactivate_plugin' )); ?>'; 
							armlite_data['_wpnonce']        = '<?php echo esc_attr(wp_create_nonce( 'arm_wp_nonce' )); ?>';
							armlite_data['dataType']        = 'json';
							armlite_formContainer.addClass( 'process-response' );
							armlite_formContainer.find(".deactivating-spinner").show();
							jQuery.post(ajaxurl,armlite_data,function(response)
							{
									window.location.href = armlite_url;
							});
						}

						var armlite_url = armlite_deactivateURL.attr( 'href' );
						jQuery('body').toggleClass('armlite-deactivate-form-active');
						armlite_formContainer.show({complete: function(){
							var offset = armlite_formContainer.offset();
							if( offset.top < 50) {
								$(this).parent().css('top', (50 - offset.top) + 'px')
							}
							jQuery('html,body').animate({ scrollTop: Math.max(0, offset.top - 50) });
						}});
						<?php if($ARMemberLite->is_arm_pro_active) {
                                $html = $html2;
                            } ?>
						armlite_formContainer.html( '<?php echo $html; //phpcs:ignore ?>');
						armlite_formContainer.on( 'change', 'input[type=radio]', function()
						{
							var armlite_detailsLabel = armlite_formContainer.find( '#armlite-deactivate-details-label strong' );
							var armlite_anonymousLabel = armlite_formContainer.find( 'label[for="armlite_anonymous"]' )[0];
							var armlite_submitSpan = armlite_formContainer.find( '#armlite-deactivate-submit-form span' )[0];
							var armlite_value = armlite_formContainer.find( 'input[name="armlite-deactivate-reason"]:checked' ).val();

							armlite_detailsLabel.text( armlite_detailsStrings[ armlite_value ] );
							armlite_anonymousLabel.style.visibility = "visible";
							armlite_submitSpan.style.display = "inline-block";
							if(armlite_deactivated)
							{
								armlite_deactivated = false;
								jQuery('#armlite-deactivate-submit-form').removeAttr("disabled");
								armlite_formContainer.off('click', '#armlite-deactivate-submit-form');
								armlite_formContainer.on('click', '#armlite-deactivate-submit-form', function(e){
									e.preventDefault();
									var data = {
										armlite_reason: armlite_formContainer.find('input[name="armlite-deactivate-reason"]:checked').val(),
										armlite_details: armlite_formContainer.find('#armlite-deactivate-details').val(),
										armlite_anonymous: armlite_formContainer.find('#armlite_anonymous:checked').length,
									};
									ARMLiteSubmitData(data, armlite_formContainer);
								});
							}
						});
						armlite_formContainer.on('click', '#armlite-deactivate-submit-form', function(e){
							e.preventDefault();
							ARMLiteSubmitData({}, armlite_formContainer);
						});
						$('.armlite-deactivate-form-bg').on('click',function(){
							armlite_formContainer.fadeOut();
							$('body').removeClass('armlite-deactivate-form-active');
						});
						armlite_formContainer.on( 'change', '#armlite-risk-confirm', function() {
							if(jQuery(this).is(":checked")) {
								$('#armlite-deactivate-submit-btn').removeAttr("disabled");
							} else {
								$('#armlite-deactivate-submit-btn').attr('disabled','disabled');
							}
						}); 
						armlite_formContainer.on( 'click', '#armlite-deactivate-cancel-btn', function(e) {
							e.preventDefault();
							armlite_formContainer.fadeOut(); 
							$('body').removeClass('armlite-deactivate-form-active');
							return false;
						});
						armlite_formContainer.on( 'click', '#armlite-deactivate-submit-btn', function() {
							window.location.href = armlite_url;
							return false;
						});
					});
				});
			</script>
			<?php
		}
		function arm_loader_img_func(){
			$arm_loader = '
			<div id="arm-loader-container">
			<svg width="64" height="64" viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg">
				  <path d="M162.219 170.516C165.763 171.145 169.222 171.908 172.609 172.802C173.438 188.605 172.04 200.174 168.119 214.483C168.017 210.383 167.971 206.131 167.698 201.908V201.897L167.697 201.887L167.523 199.968C167.095 195.488 166.501 191.009 165.739 186.53L165.738 186.521L165.736 186.513L165.384 184.538C164.89 181.825 163.908 176.79 162.219 170.516ZM154.917 168.9C156.449 174.383 157.554 179.692 158.197 184.555C158.162 184.461 158.127 184.365 158.092 184.268C156.543 180.107 154.338 174.577 151.512 168.479L154.917 168.9ZM84.4382 89.8562C93.0676 84.6969 102.471 81.9684 112.663 82.2898C118.607 82.5921 124.287 84.1586 129.844 86.7429L130.955 87.2742C137.815 90.7371 143.586 95.7647 148.795 101.469L149.83 102.62C154.012 107.403 157.145 113.137 160.126 118.84L161.397 121.279C166.246 130.585 169.022 142.739 170.59 152.704C164.12 150.228 158.477 148.676 157.712 148.469L157.688 148.461L157.635 148.446L157.135 148.328C156.588 148.207 155.744 148.044 154.648 147.858C148.972 134.708 140.579 120.982 128.339 112.063L127.745 111.637C119.483 105.783 110.211 103.087 100.135 103.381L99.157 103.419C87.7303 103.906 78.0361 108.175 69.199 114.941L68.3464 115.604L68.3435 115.607C59.7785 122.421 53.0411 130.717 47.3074 139.971L47.3005 139.981L47.2947 139.992C46.3497 141.605 44.955 144.125 43.6765 146.428C47.378 135.614 52.0304 125.464 58.2537 116.125C65.3575 105.541 73.7127 96.3172 84.4392 89.8572L84.4382 89.8562ZM58.4919 136.075C76.9203 113.482 93.8263 107.761 108.155 111.183C123.895 114.943 137.046 129.824 146.105 147.081C143.679 146.844 140.948 146.639 137.981 146.509C128.634 133.758 116.494 122.36 101.808 119.531C88.9671 117.057 74.4542 121.177 58.4919 136.075Z" fill="#0059ED" stroke="#0059ED" stroke-width="2" class="arm-loader-svg-elem-1"></path>
				  <path d="M140.79 161.742C137.458 161.738 133.924 161.692 130.524 161.692C127.609 161.692 124.672 161.727 121.732 161.854L120.472 161.914C114.485 162.19 108.464 162.829 102.408 163.829L102.395 163.832L102.383 163.834C96.2559 165.008 90.0196 166.502 83.7859 168.313L82.5613 168.674C78.1031 169.887 73.8096 171.828 69.4802 173.727L69.4646 173.734L69.45 173.742C67.1678 174.838 64.8903 176.007 62.6189 177.199C63.1395 176.773 63.6671 176.364 64.2087 175.978L64.2195 175.97L64.2312 175.962C68.4877 172.753 72.7037 169.721 76.8806 166.872C78.6266 165.765 80.3785 164.757 82.1423 163.766L83.9148 162.776C98.9328 154.42 116.839 152.265 131.123 152.265C144.442 152.265 154.489 154.139 155.898 154.51H155.899L155.902 154.511C155.904 154.512 155.907 154.512 155.911 154.513C155.919 154.516 155.933 154.519 155.95 154.524C155.985 154.534 156.038 154.548 156.107 154.567C156.246 154.605 156.451 154.663 156.716 154.739C157.245 154.89 158.014 155.113 158.963 155.403C160.861 155.983 163.482 156.826 166.365 157.878C172.168 159.994 178.909 162.912 183.043 166.179L183.073 166.203L183.104 166.223C185.227 167.656 188.794 170.257 192.042 172.688C180.332 167.572 168.575 164.552 156.687 162.959L156.668 162.957L147.708 161.933L147.679 161.93L147.651 161.928H147.639C147.632 161.928 147.62 161.927 147.605 161.927C147.575 161.925 147.531 161.922 147.475 161.919C147.363 161.912 147.201 161.903 147.005 161.892C146.612 161.871 146.079 161.842 145.52 161.813C144.42 161.756 143.169 161.696 142.724 161.696H142.63L140.79 161.742ZM115.912 20.2654C129.278 20.2688 139.842 31.0315 140.771 44.6443L140.808 45.2947V45.3015C141.198 52.666 138.003 59.3493 133.145 64.1052C128.277 68.8712 121.823 71.6198 115.807 71.2283L115.775 71.2263H115.742C102.282 71.2262 90.8408 60.0886 90.8406 46.0769C90.8406 31.553 101.812 20.4274 115.912 20.2654Z" fill="#F54EAC" stroke="#F54EAC" stroke-width="2" class="arm-loader-svg-elem-2"></path>
				</svg></div>';
			return $arm_loader;
		}
    }
    global $arm_common_lite;
    $arm_common_lite = new ARM_common_lite();
}
