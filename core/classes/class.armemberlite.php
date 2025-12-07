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
				$html .= '<p><strong>' . esc_html__( 'Before you deactivate the ARMember Lite plugin, would you quickly give us your reason for doing so?', 'armember-membership' ) . '</strong></p><p>';

				foreach ( $question_options['list_data_options'] as $key => $option ) {
					$html .= '<input type="radio" name="armlite-deactivate-reason" id="' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '"> <label for="' . esc_attr( $key ) . '">' . esc_attr( $option ) . '</label><br>';
				}

				$html .= '</p><label id="armlite-deactivate-details-label" for="armlite-deactivate-reasons"><strong>' . esc_html__( 'How could we improve ?', 'armember-membership' ) . '</strong></label><textarea name="armlite-deactivate-details" id="armlite-deactivate-details" rows="2" style="width:100%"></textarea>';

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
    }
    global $arm_common_lite;
    $arm_common_lite = new ARM_common_lite();
}
