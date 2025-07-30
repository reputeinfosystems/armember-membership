<?php
if (!class_exists('ARMLITE_Gugenberg_restriction')) {

    class ARMLITE_Gugenberg_restriction {

        var $isGutenbergBlockRestrictionFeature;
        function __construct() {
            $is_gutenberg_block_restriction_feature = get_option('arm_is_gutenberg_block_restriction_feature');
            $this->isGutenbergBlockRestrictionFeature = ($is_gutenberg_block_restriction_feature == '1') ? true : false;
            if ($this->isGutenbergBlockRestrictionFeature) {
                add_action( 'parse_request', array($this, 'arm_register_dynamic_block'));
            }
        }

        function arm_register_dynamic_block() {
            // Hook server side rendering into render callback.
            register_block_type( 'armember/armember-block-restriction', [
                'render_callback' => array($this, 'arm_render_dynamic_block'),
            ] );
        }

        function arm_render_dynamic_block( $attributes, $content ) {
            global $ARMemberLite,$arm_restriction;
            if(!$this->isGutenbergBlockRestrictionFeature){
                return;
            }
            $arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            $main_content = $else_content = NULL;
            $main_content = $content;
            /* Always Display Content For Admins */
            if (current_user_can('administrator')) {
                return do_blocks($content);
            }
            
            $plan = (isset($attributes['plans']) && !empty($attributes['plans'])) ? $attributes['plans'] : array() ;
	        $type = isset($attributes['allowed_access']) && !empty($attributes['allowed_access']) ? $attributes['allowed_access'] : 'show' ;
            $hasaccess = FALSE;
            $hasaccess = $arm_restriction->arm_check_content_hasaccess($plan, $type);
            
            if ($hasaccess) {
                return do_blocks($main_content);
            } else {
                return do_blocks($else_content);
            }
        }

    }

}
global $arm_gutenberg_block_restriction;
$arm_gutenberg_block_restriction = new ARMLITE_Gugenberg_restriction();