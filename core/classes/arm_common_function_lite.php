<?php 
if(! defined('ABSPATH')) exit;

if ( ! function_exists( 'arm_maybe_unserialize' ) ) {
    function arm_maybe_unserialize( $value, $allowed_classes = array() ) {

        if ( ! is_string( $value ) || ! is_serialized( $value ) ) {
            return $value;
        }

        $arm_allowed_classes = false;

        if (!empty( $allowed_classes ) ) {
            $arm_allowed_classes = $allowed_classes;
        }

        $trimmed = trim( $value );
        $result = @unserialize(
            $trimmed,
            array(
                'allowed_classes' => $arm_allowed_classes,
            )
        );

        if ($result instanceof __PHP_Incomplete_Class ) {
            return '';
        }

        if ( false === $result && $trimmed !== 'b:0;' && $trimmed !== 'b:1;' ) {
            return $value;
        }

        return $result;
    }
}

if(!function_exists('arm_get_user_meta')){
    function arm_get_user_meta($user_id, $meta_key='', $single = true){

        /*
         * Phase 2: read the member plan keys from the custom tables (arm_members_plan_detail / arm_subscriptions / arm_subscriptions_meta).
         * Same value as get_user_meta() for arm_user_plan_ids, arm_user_suspended_plan_ids, arm_user_future_plan_ids,
         * arm_user_plan_{plan_id}, arm_user_post_ids.
         * To enable: remove the "PHASE 2 START" line and the "PHASE 2 END" line below.
         */
        /* PHASE 2 START
        global $arm_member_subscriptions;
        if ( ! empty( $meta_key ) && is_object( $arm_member_subscriptions ) && method_exists( $arm_member_subscriptions, 'arm_get_user_meta_from_tables' ) ) {
            $arm_table_value = $arm_member_subscriptions->arm_get_user_meta_from_tables( $user_id, $meta_key, $single );
            if ( null !== $arm_table_value ) {
                return $arm_table_value;
            }
        }
        PHASE 2 END */

        return get_user_meta($user_id, $meta_key, $single);
    }
}

if(!function_exists('arm_update_user_meta')){
    function arm_update_user_meta($user_id, $meta_key, $meta_value){
        return update_user_meta($user_id, $meta_key, $meta_value);
    }
}

if(!function_exists('arm_delete_user_meta')){
    function arm_delete_user_meta($user_id, $meta_key, $meta_value = ''){
        return delete_user_meta($user_id, $meta_key, $meta_value);
    }
}

if(!function_exists('arm_sync_member_plan_tables')){
    function arm_sync_member_plan_tables($user_id, $meta_key){
        global $arm_member_subscriptions;
        if ( is_object( $arm_member_subscriptions ) && method_exists( $arm_member_subscriptions, 'arm_sync_from_user_meta' ) ) {
            $arm_member_subscriptions->arm_sync_from_user_meta( $user_id, $meta_key );
        }
    }
}