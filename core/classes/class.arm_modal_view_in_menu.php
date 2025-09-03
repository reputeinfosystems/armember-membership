<?php
if ( ! class_exists( 'ARM_modal_view_in_menu_Lite' ) ) {
	class ARM_modal_view_in_menu_Lite {

		function __construct() {
			global  $all_child_array, $all_items_array, $all_parent_array;
			$all_child_array  = array();
			$all_items_array  = array();
			$all_parent_array = array();

			add_action( 'admin_head-nav-menus.php', array( $this, 'arm_add_nav_menu_metabox' ), 10 );
			if ( ! isset( $_GET['uxb_iframe'] ) ) { //phpcs:ignore
				add_filter( 'wp_nav_menu', array( $this, 'arm_wp_loaded_walker_menu' ), 10, 2 );
			}

			add_filter( 'wp_nav_menu', array( $this, 'arm_main_hook_for_exclude' ), 11, 2 );
				add_action( 'wp_footer', array( $this, 'arm_nav_menu_add_javascript' ) );

			add_filter( 'wp_nav_menu_objects', array( $this, 'arm_exclude_menu_items' ), 11, 3 );

			add_filter( 'wp_nav_menu_objects', array( $this, 'arm_exclude_menu_items_2' ), 100, 3 );

			/* Custom Field for WordPress Menu Item */
			add_action( 'wp_update_nav_menu_item', array( $this, 'arm_add_nav_menu_meta_box' ), 10, 3 );
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'arm_setup_nav_menu_item' ) );

			//add_action( 'wp_ajax_arm_get_post_meta_for_menu', array( $this, 'arm_get_post_meta_for_menu' ) );
			add_action( 'init', array( $this, 'logout_from_menu_link' ) );
		}

		function get_nav_menu_item_children( $parent_id, $nav_menu_items, $depth = true ) {
			global $all_parent_array;
			$nav_menu_item_list = array();
			$all_parent_array[] = $parent_id;
			foreach ( (array) $nav_menu_items as $nav_menu_item ) {
				if ( $nav_menu_item->menu_item_parent == $parent_id ) {
					$nav_menu_item_list[] = $nav_menu_item;
					if ( $depth ) {
						if ( $children = $this->get_nav_menu_item_children( $nav_menu_item->ID, $nav_menu_items ) ) {
							$nav_menu_item_list = array_merge( $nav_menu_item_list, $children );
						}
					}
				}
			}
			return $nav_menu_item_list;
		}

		function arm_exclude_menu_items_2( $sorted_menu_objects, $args ) {
			global $arm_member_forms,$all_child_array,$all_items_array,$all_parent_array;
				$child_parent_array = array_merge( $all_parent_array, array_unique( $all_child_array ) );
			foreach ( $sorted_menu_objects as $key => $menu_object ) {
				$url               = $menu_object->url;
				$menu_id           = $menu_object->ID;
				$arm_hide_show_val = get_post_meta( $menu_id, 'arm_is_hide_show_after_login', true );
				if ( $arm_hide_show_val != '' ) {
					if ( in_array( $menu_id, $child_parent_array ) ) {
						unset( $sorted_menu_objects[ $key ] );
					}
				}
			}
			   return $sorted_menu_objects;
		}

		function arm_main_hook_for_exclude( $nav_menu, $args ) {
			global $all_items_array,$all_child_array;
			foreach ( $all_child_array as $k => $y ) {
				foreach ( $all_items_array as $all_itme => $val ) {
					if ( $y == $val ) {
						unset( $all_items_array[ $all_itme ] );
					}
				}
			}
			return $nav_menu;
		}
		function arm_exclude_menu_items( $sorted_menu_objects, $args ) {
			global $arm_member_forms,$all_child_array,$all_items_array,$all_parent_array;
			$show           = '';
			$arm_logout_url = ARMLITE_HOME_URL . '/?arm_action=logout';
			$arm_logout_url2 = ARMLITE_HOME_URL . '?arm_action=logout';
			foreach ( $sorted_menu_objects as $key => $menu_object ) {
				$url               = $menu_object->url;
				$menu_id           = $menu_object->ID;
				$all_items_array[] = $menu_id;
				$arm_hide_show     = get_post_meta( $menu_id, 'arm_is_hide_show_after_login', true );
				if ( $arm_hide_show == '' ) {
					$arm_hide_show = 'show_to_all';
				}
				if ( $arm_hide_show != 'show_to_all' ) {
					if ( ! is_user_logged_in() ) {

						if ( $arm_hide_show == 'show_before_login' ) {
							$show = 1;
						} elseif ( $arm_hide_show == 'show_after_login' ) {
							$show = 0;
						}
					} else {
						if ( $arm_hide_show == 'show_before_login' ) {
							$show = 0;
						} elseif ( $arm_hide_show == 'show_after_login' ) {
							$show = 1;
						}
					}
				}
				if ( !in_array($arm_hide_show ,array('show_to_all','always_show')) && ( $show == 0 ) ) {
					$all_child_array_temp = $this->get_nav_menu_item_children( $menu_id, $sorted_menu_objects );
				}
				if ( ! empty( $all_child_array_temp ) && is_array($all_child_array_temp)) {
					foreach ( $all_child_array_temp as $key_child => $child ) {
						$all_child_array[] = $child->ID;
						if ( $child->ID == $menu_id ) {
							// unset child from sorted object
							// unset($sorted_menu_objects[$key]);
						}
					}
				}
				
				//Check Login URL.
				$arm_logout_url_strpos = strpos( $url, $arm_logout_url );
				$arm_logout_url_strpos2 = strpos( $url, $arm_logout_url2 );
				if ( false !== $arm_logout_url_strpos ) {
					$menu_object->url = add_query_arg( array( 'arm_wpnonce' => wp_create_nonce( 'arm_wpnonce' ) ), $menu_object->url );
				}
				else if( false !== $arm_logout_url_strpos2 ) {
					$menu_object->url = add_query_arg( array( 'arm_wpnonce' => wp_create_nonce( 'arm_wpnonce' ) ), $menu_object->url );
				}
			}
			return $sorted_menu_objects;
		}

		function logout_from_menu_link() {
			$arm_action  = isset( $_REQUEST['arm_action'] ) ? sanitize_text_field($_REQUEST['arm_action']) : ''; //phpcs:ignore
			$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : ARMLITE_HOME_URL; //phpcs:ignore
			$arm_wpnonce  = isset( $_REQUEST['arm_wpnonce'] ) ? sanitize_text_field($_REQUEST['arm_wpnonce']) : ''; //phpcs:ignore
			if ( $arm_action == 'logout' && wp_verify_nonce( $arm_wpnonce, 'arm_wpnonce' ) ) {
				// $location = wp_logout_url($redirect_to);
				$redirect_to = str_replace( '&amp;', '&', $redirect_to );
				// wp_redirect($location);
				// wp_logout();

				wp_clear_auth_cookie();
				do_action( 'wp_logout' );
				nocache_headers();

				wp_safe_redirect( $redirect_to );
				exit;
			}
		}
		function arm_add_nav_menu_metabox() {

			add_meta_box( 'armlogout', esc_html__( 'ARMember Logout', 'armember-membership' ), array( $this, 'arm_logout_menu_metabox' ), 'nav-menus', 'side', 'default' );
			?>
			<style type="text/css">
				.armlogout .accordion-section-title.hndle, .armlogout.open .accordion-section-title.hndle { background: #005aee !important; background-color: #005aee !important; border-top: 1px solid #ffffff !important; color: #ffffff; margin: -6px 0 0; position: relative; padding-left: 28px; }
                .control-section.accordion-section.armlogout .accordion-section-title.hndle button, .control-section.accordion-section.armlogout.open .accordion-section-title.hndle button { color: #ffffff; outline: none; border: 0px; box-shadow: none; padding-left: 8px; }
                .armlogout .accordion-section-title.hndle:focus,
                .armlogout .accordion-section-title.hndle:hover { background-color: #005aee; color: white; margin: -6px 0 0; position: relative; }
                .armlogout .accordion-section-title.hndle::before { background-image: url(<?php echo MEMBERSHIPLITE_IMAGES_URL.'/logo_navmenu_white.png'; //phpcs:ignore ?>); height: 20px; width: 20px; content: " "; position: absolute; left: 8px; top: 10px; }
                .armlogout .accordion-section-title::after, .armlogout .accordion-section-title.hndle button span::before { color: #fff !important; }
                #menu-settings-column .armlogout .inside { margin: 0; }
                .arm_color_red { color: #ff0000 !important ; }
			</style>
			<?php
		}

		function arm_logout_menu_metabox( $object ) {
				   global $nav_menu_selected_id,$wpdb,$ARMemberLite,$arm_member_forms;
					// Create an array of objects that imitate Post objects
					$form_items = array();

									$_Lolabel = 'Logout';
									// $lo_navigation_link = wp_login_url().'?action=logout';
									$lo_navigation_link = add_query_arg( array( 'arm_action' => 'logout' ), ARMLITE_HOME_URL );
									$form_items[]       = (object) array(
										'ID'               => 1,
										'db_id'            => 0,
										'menu_item_parent' => 0,
										'object_id'        => $lo_navigation_link,
										'post_parent'      => 0,
										'type'             => 'custom',
										'object'           => 'arm-form-slug',
										'type_label'       => 'ARMember Plugin',
										'title'            => $_Lolabel,
										'url'              => $lo_navigation_link,
										'target'           => '',
										'attr_title'       => '',
										'description'      => '',
										'classes'          => array(),
										'xfn'              => '',
									);
									$db_fields          = false;
									// If your links will be hieararchical, adjust the $db_fields array bellow
									if ( false ) {
										$db_fields = array(
											'parent' => 'parent',
											'id'     => 'post_parent',
										);
									}
									$walker       = new Walker_Nav_Menu_Checklist( $db_fields );
									$removed_args = array(
										'action',
										'customlink-tab',
										'edit-menu-item',
										'menu-item',
										'page-tab',
										'_wpnonce',
									);
									?>
					<div id="arm-logout-links" class="loginlinksdiv posttypediv">
						<div><?php echo "<p class='arm_color_red'>".esc_html( "NOTE: This feature will only work with those themes which has support of WordPress' navigation menu core hooks.", 'armember-membership' )."</p>"; ?></div>
						<p><?php esc_html_e( 'This navigation menu link is to set Logout Link.', 'armember-membership' ); ?></p>
						<div id="tabs-panel-arm-logout-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
							<ul id="arm-logout-linkschecklist" class="list:arm-logout-links categorychecklist form-no-clear">
								<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $form_items ), 0, (object) array( 'walker' => $walker ) ); ?>
							</ul>
						</div>
						<p class="button-controls">
							<span class="add-to-menu">
								<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'armember-membership' ); ?>" name="add-arm-logout-links-menu-item" id="submit-arm-logout-links" />
								<span class="spinner"></span>
							</span>
						</p>
					</div>    
				<?php
		}

		function arm_nav_menu_add_javascript() {
			?>
			<script data-cfasync="false" type="text/javascript">
			function arm_open_modal_box_in_nav_menu(menu_id, form_id) {
							
				jQuery(".arm_nav_menu_link_" + form_id).find("." + form_id).trigger("click");
				return false;
			}
			</script>
			<?php
		}

		function arm_add_modal_popups_after_theme_loaded() {
			global $arm_lite_popup_modal_elements, $arm_lite_inner_form_modal;
			if ( ! is_admin() ) {
				if ( is_array( $arm_lite_popup_modal_elements ) && ! empty( $arm_lite_popup_modal_elements ) ) {
					foreach ( $arm_lite_popup_modal_elements as $key => $arm_modal_popup ) {
						echo do_shortcode( $arm_modal_popup );
					}
				}
				if ( is_array( $arm_lite_inner_form_modal ) && count( $arm_lite_inner_form_modal ) > 0 ) {

					foreach ( $arm_lite_inner_form_modal as $modal_popup ) {
						echo do_shortcode( $modal_popup );
					}
				}
			}
		}
		function arm_wp_loaded_walker_menu( $nav_menu, $args ) {
			global $ARMemberLite,$arm_lite_bpopup_loaded,$arm_lite_popup_modal_elements;
			preg_match( '/armaction=(arm_modal_view_menu|arm_modalmembership_setup)/', $nav_menu, $matches );
			if ( count( $matches ) > 0 ) {
				$dom = new DOMDocument();
				if ( extension_loaded( 'mbstring' ) ) {
					@$dom->loadHTML( mb_convert_encoding( $nav_menu, 'HTML-ENTITIES', 'UTF-8' ) );
				} else {
					@$dom->loadHTML( htmlspecialchars_decode( utf8_decode( htmlentities( $nav_menu, ENT_COMPAT, 'utf-8', false ) ) ) );
				}
				$n          = new DOMXPath( $dom );
				$new_menu   = '';
				$anchor_tag = $dom->getElementsByTagName( 'a' );
				foreach ( $anchor_tag as $tag ) {
					$href = $tag->getAttribute( 'href' );
					$echo = '';
					if ( preg_match( '/armaction=(arm_modal_view_menu|arm_modalmembership_setup)/', $href ) ) {
						$menu_id = '';
						/* changes for notice warning need to confirm */
						if ( isset( $args->menu->term_id ) ) {
							$menu_id = $args->menu->term_id;
						}
						if ( ! is_admin() ) {
							$ARMemberLite->set_front_css( true );
							$ARMemberLite->set_front_js( true );
							do_action( 'arm_enqueue_js_css_from_outside' );
						}
						$arm_menu_array = array();
						$arm_menu_elems = explode( '&', str_replace( '&amp;', '&', $href ) );
						if ( ! empty( $arm_menu_elems ) ) {
							foreach ( $arm_menu_elems as $arm_menu_elem ) {
								if ( ! empty( $arm_menu_elem ) ) {
									$arm_link_pera                       = explode( '=', $arm_menu_elem );
									$arm_menu_array[ $arm_link_pera[0] ] = $arm_link_pera[1];
								}
							}
						}
						if ( ! empty( $arm_menu_array ) ) {
							if ( array_key_exists( 'id', $arm_menu_array ) && ! empty( $arm_menu_array['id'] ) ) {
								$formAttr     = " id='" . $arm_menu_array['id'] . "' ";
								$formRandomID = $arm_menu_array['id'] . arm_generate_random_code( 8 );
								$formAttr    .= " link_class=\"arm_form_link_$formRandomID\"";
								$formAttr    .= ' link_type="link" link_title="&nbsp;" link_css="" link_hover_css=""';
								$formAttr    .= ' popup="true"';
								if ( isset( $arm_menu_array['popup_height'] ) && ! empty( $arm_menu_array['popup_height'] ) ) {
									$formAttr .= ' popup_height="' . $arm_menu_array['popup_height'] . '"';
								}
								if ( isset( $arm_menu_array['popup_width'] ) && ! empty( $arm_menu_array['popup_width'] ) ) {
									$formAttr .= ' popup_width="' . $arm_menu_array['popup_width'] . '"';
								}
								if ( isset( $arm_menu_array['overlay'] ) && ! empty( $arm_menu_array['overlay'] ) ) {
									$formAttr .= ' overlay="' . $arm_menu_array['overlay'] . '"';
								}
								if ( isset( $arm_menu_array['modal_bgcolor'] ) && ! empty( $arm_menu_array['modal_bgcolor'] ) ) {
									$formAttr .= ' modal_bgcolor="' . $arm_menu_array['modal_bgcolor'] . '"';
								}
								if ( isset( $arm_menu_array['nav_menu'] ) && $arm_menu_array['nav_menu'] == 1 ) {
									$formAttr .= ' nav_menu="1"';
								}
								$onClick     = "arm_open_modal_box_in_nav_menu('$menu_id','arm_form_link_" . $formRandomID . "');return false;";
								$arm_data_id = 'arm_form_link_' . $formRandomID;
								$shortcode   = '[arm_form ' . $formAttr . ']';
								if ( preg_match( '/armaction=arm_modalmembership_setup/', $href ) ) {
									$shortcode = "[arm_setup $formAttr]";
								}
								$echo  = '<div id="arm_nav_menu_link_' . esc_attr($menu_id) . '" class="arm_nav_menu_form_container arm_nav_menu_link_' . esc_attr($menu_id) . ' arm_nav_menu_link_arm_form_link_' . esc_attr($formRandomID) . '" style="display:none;">';
								$echo .= $shortcode;
								$echo .= '</div>';
								$arm_lite_popup_modal_elements[ $formRandomID ] = $echo;
							}
							$tag->setAttribute( 'href', '#' );
							$tag->setAttribute( 'onClick', $onClick );
							$tag->setAttribute( 'arm-data-id', $arm_data_id );

						}
						$arm_lite_bpopup_loaded = 1;
					}
					$new_menu = preg_replace( '/^<!DOCTYPE.+?>/', '', str_replace( array( '<html>', '</html>', '<body>', '</body>' ), array( '', '', '', '' ), $dom->saveHTML() ) );
				}
				$nav_menu = $new_menu;
			}
			return $nav_menu;
		}

		function arm_add_nav_menu_meta_box( $menu_id, $menu_item_db_id, $args ) {
			global $ARMemberLite;

			if ( isset( $_REQUEST['arm_is_hide_show_after_login'] ) ) { //phpcs:ignore
				if ( is_array( $_REQUEST['arm_is_hide_show_after_login'] ) ) { //phpcs:ignore
					$custom_value = isset( $_REQUEST['arm_is_hide_show_after_login'][ $menu_item_db_id ] ) ? sanitize_text_field($_REQUEST['arm_is_hide_show_after_login'][ $menu_item_db_id ]) : 'always_show'; //phpcs:ignore
					update_post_meta( $menu_item_db_id, 'arm_is_hide_show_after_login', $custom_value );
					if ( ! empty( $_REQUEST['arm_access_rule_menu'] ) && $custom_value == 'show_after_login' ) { //phpcs:ignore
						$menu_rules = isset( $_REQUEST['arm_access_rule_menu'][ $menu_item_db_id ] ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_REQUEST['arm_access_rule_menu'][ $menu_item_db_id ] ) : array(); //phpcs:ignore
						if ( ! empty( $menu_rules ) && count( $menu_rules ) > 0 ) {
							delete_post_meta( $menu_item_db_id, 'arm_protection' );
							delete_post_meta( $menu_item_db_id, 'arm_access_plan' );
							// add_post_meta($menu_item_db_id, 'arm_access_plan', '0');
							$wpdb->query( $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'postmeta (`post_id`, `meta_key`, `meta_value`) VALUES (%d,%s,%s)',$menu_item_db_id, 'arm_access_plan', '0') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

							foreach ( $menu_rules as $plan_id ) {
								delete_post_meta( $menu_item_db_id, 'arm_access_plan', $plan_id );
								// add_post_meta($menu_item_db_id, 'arm_access_plan', $plan_id);
								$wpdb->query( $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . "postmeta (`post_id`, `meta_key`, `meta_value`) VALUES (%d,%s,%s)",$menu_item_db_id, 'arm_access_plan', $plan_id) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							}
						} else {
							delete_post_meta( $menu_item_db_id, 'arm_protection' );
							delete_post_meta( $menu_item_db_id, 'arm_access_plan' );
						}
					} else {
						delete_post_meta( $menu_item_db_id, 'arm_protection' );
						delete_post_meta( $menu_item_db_id, 'arm_access_plan' );
					}
					if ( ! isset( $_REQUEST['arm_access_rule_menu'] ) ) { //phpcs:ignore
						delete_post_meta( $menu_item_db_id, 'arm_protection' );
						delete_post_meta( $menu_item_db_id, 'arm_access_plan' );
					}
				}
			}
		}

		function arm_setup_nav_menu_item( $menu_item ) {
			$menu_item->custom = get_post_meta( $menu_item->ID, 'arm_is_hide_show_after_login', true );
			return $menu_item;
		}

		/*
		function arm_get_post_meta_for_menu() {
			$response = array();
			if ( isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {  //phpcs:ignore
				$response['error'] = false;
				$response['res']   = array();
				$ids               = json_decode( stripslashes_deep( $_REQUEST['ids'] ) );  //phpcs:ignore
				foreach ( $ids as $key => $menu_id ) {
					$response['res'][ $menu_id ]         = get_post_meta( $menu_id, 'arm_is_hide_show_after_login', true );
					$response['access_rule'][ $menu_id ] = get_post_meta( $menu_id, 'arm_access_plan', false );
				}
			} else {
				$response['error'] = true;
			}
			echo wp_json_encode( $response );
			die();
		}
		*/
	}
}
global $arm_modal_view_in_menu;
$arm_modal_view_in_menu = new ARM_modal_view_in_menu_Lite();
