<?php 

if ( ! function_exists( 'arm_maybe_unserialize' ) ) {
    function arm_maybe_unserialize( $value, $allowed_classes = array() ) {

        if ( ! is_string( $value ) || ! is_serialized( $value ) ) {
            return $value;
        }

        $trimmed = trim( $value );
        $result = @unserialize(
            $trimmed,
            array(
                'allowed_classes' => false
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
    function arm_get_user_meta($user_id, $meta_key='', $single = false){
        return get_user_meta($user_id, $meta_key, $single);
    }
}

if(!function_exists('arm_update_user_meta')){
    function arm_update_user_meta($user_id, $meta_key, $meta_value){
        return update_user_meta($user_id, $meta_key, $meta_value);
    }
}