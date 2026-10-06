<?php 
if(! defined('ABSPATH')) exit;

if ( ! class_exists( 'ARM_Spam_Filter_Lite' ) ) {
	class ARM_Spam_Filter_Lite {

		const nonce_action             = 'form_spam_filter';
		const nonce_name               = 'arm_nonce_check';
		const arm_nonce_start_time     = 'form_filter_st';
		const arm_nonce_keyboard_press = 'form_filter_kp';
		const token_field    = 'arm_spam_token';
		const ux_field       = 'arm_ux';
		const salt_scheme    = 'arm_spam_filter';
		const token_ttl      = 7200; /* 2 hours. Long enough for a slow form fill. */
		const min_fill_time  = 2;    /* Seconds. Below this a human did not type it. */
		const pow_field      = 'arm_pow';
		const pow_bits       = 15;
		const score_start    = 10;
		var $nonce_fields;
		public $is_enabled = false;
		private $consumed_jtis = array();
		function __construct() {
			global $wp, $wpdb,$arm_global_settings;

			add_shortcode( 'armember_spam_filters', array( $this, 'armember_spam_filters_func' ) );

			$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
			$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
			
			$allowed = false;
			$allowed = apply_filters('arm_modify_file_validation_external', $allowed);
			$this->is_enabled = ( !empty($spam_protection) && !$allowed );
			if ( $this->is_enabled ) {
				add_filter('armember_validate_spam_filter_fields', array($this, 'armember_check_spam_filter_fields'), 9999, 2);
				add_action('wp_enqueue_scripts', array($this, 'arm_enqueue_spam_filter_script'), 20);
			}
		}
		private function arm_spam_secret()
		{
			return wp_salt( self::salt_scheme );
		}
		private function arm_client_ua()
		{
			$ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : ''; //phpcs:ignore
			return substr( hash( 'sha256', $ua ), 0, 16 );
		}
		public function arm_issue_spam_token($form_key_arr)
		{
			nocache_headers();
			header( 'Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0' );

			$raw_keys = isset($form_key_arr) ? sanitize_text_field( wp_unslash( $form_key_arr ) ) : ''; //phpcs:ignore
			if ( $raw_keys === '' ) {
				wp_send_json_error( array( 'reason' => 'missing_form_keys' ), 400 );
			}
			$out = array();
			$keys = array_slice( array_filter( array_map( 'sanitize_text_field', explode( ',', $raw_keys ) ) ), 0, 20 );
			foreach ( $keys as $form_key ) {
				$payload = array(
					'k'   => $form_key,
					'iat' => time(),
					'jti' => wp_generate_password( 16, false, false ),
					'fld' => 'arm_f_' . substr( hash_hmac( 'sha256', $form_key . microtime() . wp_rand(), $this->arm_spam_secret() ), 0, 12 ),
					'ua'  => $this->arm_client_ua(),
				);
				$payload['pow'] = (int) apply_filters( 'arm_spam_filter_pow_bits', self::pow_bits, $form_key );

				$blob = $this->arm_b64_encode( wp_json_encode( $payload ) );
				$sig  = hash_hmac( 'sha256', $blob, $this->arm_spam_secret() );

				$out[ $form_key ] = array(
					'tok'  => $blob . '.' . $sig,
					'fld'  => $payload['fld'],
					'chal' => $payload['jti'],
					'bits' => $payload['pow'],
				);
			}
			
			return $out;
		}

		private function arm_b64_encode( $data )
		{
			return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); //phpcs:ignore
		}

		private function arm_b64_decode( $data )
		{
			$data = strtr( (string) $data, '-_', '+/' );
			$pad  = strlen( $data ) % 4;
			if ( $pad ) { $data .= str_repeat( '=', 4 - $pad ); }
			return base64_decode( $data ); //phpcs:ignore
		}
		private function arm_verify_spam_token( $token, $form_key )
		{
			if ( ! is_string($token) || $token === '' ) { return 'token_absent'; }
			if ( substr_count( $token, '.' ) !== 1 )    { return 'token_malformed'; }

			list( $blob, $sig ) = explode( '.', $token, 2 );

			if ( ! hash_equals( hash_hmac( 'sha256', $blob, $this->arm_spam_secret() ), (string) $sig ) ) {
				return 'token_bad_signature';
			}

			$payload = json_decode( $this->arm_b64_decode( $blob ), true );
			if ( ! is_array($payload) || empty($payload['jti']) || empty($payload['fld']) ) {
				return 'token_malformed';
			}
			if ( ! isset($payload['k']) || (string) $payload['k'] !== (string) $form_key ) {
				return 'token_wrong_form';
			}
			if ( ! isset($payload['ua']) || $payload['ua'] !== $this->arm_client_ua() ) {
				return 'token_wrong_client';
			}

			$age = time() - (int) $payload['iat'];
			if ( $age < 0 || $age > apply_filters( 'arm_spam_filter_token_ttl', self::token_ttl ) ) {
				return 'token_expired';
			}

			$min_age = (int) apply_filters( 'arm_spam_filter_min_fill_time', self::min_fill_time );
			if ( $age < $min_age ) {
				return 'token_too_fast';
			}

			$bits = isset($payload['pow']) ? (int) $payload['pow'] : 0;
			if ( $bits > 0 ) {
				$solution = isset($_REQUEST[ self::pow_field ]) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::pow_field ] ) ) : ''; //phpcs:ignore
				if ( $solution === '' || ! preg_match( '/^[0-9]{1,20}$/', $solution ) ) {
					return 'pow_missing';
				}
				if ( ! $this->arm_pow_is_valid( $payload['jti'], $solution, $bits ) ) {
					return 'pow_invalid';
				}
			}

			if ( get_transient( 'arm_sf_jti_' . $payload['jti'] ) ) {
				if ( ! in_array( $payload['jti'], $this->consumed_jtis, true ) ) {
					return 'token_consumed';
				}
			}

			if ( ! in_array( $payload['jti'], $this->consumed_jtis, true ) ) {
				set_transient(
					'arm_sf_jti_' . $payload['jti'],
					1,
					apply_filters( 'arm_spam_filter_token_ttl', self::token_ttl )
				);
				$this->consumed_jtis[] = $payload['jti'];
			}

			$payload['age'] = $age;
			return $payload;
		}
		private function arm_pow_is_valid( $challenge, $nonce, $bits )
		{
			$digest = hash( 'sha256', $challenge . ':' . $nonce, true );
			$need   = (int) $bits;
			$i      = 0;
			$len    = strlen( $digest );

			while ( $need >= 8 && $i < $len ) {
				if ( $digest[ $i ] !== "\x00" ) { return false; }
				$need -= 8;
				$i++;
			}
			if ( $need > 0 ) {
				if ( $i >= $len ) { return false; }
				if ( ( ord( $digest[ $i ] ) >> ( 8 - $need ) ) !== 0 ) { return false; }
			}
			return true;
		}
		private function arm_score_threshold()
		{
			global $arm_global_settings;

			$level = '';
			if ( is_object($arm_global_settings) && method_exists($arm_global_settings, 'arm_get_all_global_settings') ) {
				$all = $arm_global_settings->arm_get_all_global_settings();
				$general = isset($all['general_settings']) ? $all['general_settings'] : array();
				$level = isset($general['spam_protection_level']) ? $general['spam_protection_level'] : '';
			}

			$map = array( 'low' => 2, 'balanced' => 4, 'strict' => 6 );
			$threshold = isset($map[$level]) ? $map[$level] : $map['balanced'];

			return (int) apply_filters( 'arm_spam_filter_threshold', $threshold, $level );
		}
		function armember_check_spam_filter_fields($validate = true,$form_key = '')
		{
			global $wpdb, $ARMemberLite, $arm_case_types;

			$score      = self::score_start;
			$signals    = array();
			$hard_fail  = '';

			$posted_token = isset($_REQUEST[self::token_field]) ? sanitize_text_field( wp_unslash( $_REQUEST[self::token_field] ) ) : ''; //phpcs:ignore
			$token        = $this->arm_verify_spam_token( $posted_token, $form_key );
			$has_token    = is_array( $token );

			if ( $has_token ) {
				$hp_name = $token['fld'];
				if ( ! isset($_REQUEST[ $hp_name ]) ) { //phpcs:ignore
					/* Field was stripped somewhere between load and submit. Odd, not fatal. */
					$score -= 2; $signals[] = 'honeypot_missing:-2';
				} elseif ( trim( (string) wp_unslash( $_REQUEST[ $hp_name ] ) ) !== '' ) { //phpcs:ignore
					$hard_fail = 'honeypot_filled';
				}

				$ux = isset($_REQUEST[self::ux_field]) ? (int) $_REQUEST[self::ux_field] : -1; //phpcs:ignore
				if ( $ux === 0 ) {
					$score -= 2; $signals[] = 'no_interaction:-2';
				}

			} else {

				if ( apply_filters( 'arm_spam_filter_require_token', true, $form_key ) ) {
					$hard_fail = is_string( $token ) ? $token : 'token_invalid';
				} else {
					$score -= 5; $signals[] = 'no_token(' . $token . '):-5';
				}

			}

			if ( isset($_REQUEST['arm_filter_input']) ) { //phpcs:ignore
				if ( trim( (string) wp_unslash( $_REQUEST['arm_filter_input'] ) ) !== '' ) { //phpcs:ignore
					$hard_fail = 'legacy_honeypot_filled';
				} else {
					$score -= 2; $signals[] = 'legacy_honeypot_present:-2';
				}
			}

			$referer = $this->validateReferer();
			if ( empty($referer['hasReferrer']) ) {
				if ( $has_token ) {
					$score -= 1; $signals[] = 'referer_absent:-1';
				} else {
					$signals[] = 'referer_absent:0';
				}
			} elseif ( empty($referer['pass']) ) {
				$score -= 2; $signals[] = 'referer_foreign:-2';
			}

			/* Content heuristics. */
			$content_penalty = $this->arm_content_penalty( $signals );
			$score -= $content_penalty;

			$threshold = $this->arm_score_threshold();

			$score = (int) apply_filters( 'arm_spam_filter_score', $score, $signals, $hard_fail, $form_key );

			if ( $hard_fail !== '' ) {
				$validate = false;
			} else {
				$validate = ( $score >= $threshold );
			}

			$validate = (bool) apply_filters( 'arm_spam_filter_validate', $validate, $score, $threshold, $signals, $hard_fail, $form_key );

			if ( ! $validate ) {
				do_action( 'arm_spam_filter_blocked', $form_key, $score, $threshold, $signals, $hard_fail );
			}

			if ( defined('MEMBERSHIPLITE_DEBUG_LOG') && MEMBERSHIPLITE_DEBUG_LOG == true && is_object($ARMemberLite) ) {
				$log  = " form_key => " . $form_key;
				$log .= "\r\n token => " . ( $has_token ? 'valid' : ( is_string($token) ? $token : 'invalid' ) );
				$log .= "\r\n hard fail => " . ( $hard_fail !== '' ? $hard_fail : 'none' );
				$log .= "\r\n signals => " . implode( ', ', $signals );
				$log .= "\r\n score => " . $score . " (threshold " . $threshold . ")";
				$log .= "\r\n FINAL validate => " . ( $validate ? 'pass' : 'BLOCK' );
				$arm_case_types['shortcode']['protected'] = true;
				$arm_case_types['shortcode']['message']   = $log;
				$arm_case_types['shortcode']['type']      = "spam_filter";
				$ARMemberLite->arm_debug_response_log('armember_check_spam_filter_fields', $arm_case_types, array(), $wpdb->last_query);
			}

			return $validate;
		}

		private function arm_content_penalty( &$signals )
		{
			$penalty = 0;

			$name_fields = array( 'arm_user_first_name', 'arm_user_last_name', 'first_name', 'last_name', 'arm_user_login', 'user_login', 'display_name' );
			foreach ( $name_fields as $field ) {
				if ( ! empty($_REQUEST[ $field ]) && is_string($_REQUEST[ $field ]) ) { //phpcs:ignore
					$value = (string) wp_unslash( $_REQUEST[ $field ] ); //phpcs:ignore
					if ( preg_match( '#(https?://|www\.|\[url|<a\s)#i', $value ) ) {
						$penalty += 1; $signals[] = 'url_in_name_field:-1';
						break;
					}
				}
			}

			$email = '';
			foreach ( array( 'arm_user_email', 'user_email', 'email' ) as $field ) {
				if ( ! empty($_REQUEST[ $field ]) && is_string($_REQUEST[ $field ]) ) { //phpcs:ignore
					$email = strtolower( (string) wp_unslash( $_REQUEST[ $field ] ) ); //phpcs:ignore
					break;
				}
			}
			if ( $email !== '' && strpos( $email, '@' ) !== false ) {
				$domain = substr( $email, strrpos( $email, '@' ) + 1 );
				$disposable = apply_filters( 'arm_spam_filter_disposable_domains', array(
					'mailinator.com', 'guerrillamail.com', 'guerrillamail.info', '10minutemail.com',
					'yopmail.com', 'trashmail.com', 'sharklasers.com', 'temp-mail.org',
					'getnada.com', 'dispostable.com', 'maildrop.cc', 'tempmail.net',
					'throwawaymail.com', 'fakeinbox.com', 'mailnesia.com', 'mytemp.email',
				) );
				if ( in_array( $domain, (array) $disposable, true ) ) {
					$penalty += 1; $signals[] = 'disposable_email:-1';
				}
			}

			return (int) apply_filters( 'arm_spam_filter_content_penalty', $penalty, $signals );
		}
		public function arm_enqueue_spam_filter_script()
		{
			if ( ! defined('MEMBERSHIPLITE_URL') ) { return; }

			wp_register_script(
				'arm_spam_filter_js',
				MEMBERSHIPLITE_URL . '/js/arm_spam_filter.js',
				array(),
				defined('MEMBERSHIPLITE_VERSION') ? MEMBERSHIPLITE_VERSION : false,
				true
			);
			wp_localize_script( 'arm_spam_filter_js', 'ARM_SPAM_FILTER', array(
				'tokenField' => self::token_field,
				'uxField'    => self::ux_field,
				'powField'   => self::pow_field,
				'minAge'     => (int) apply_filters( 'arm_spam_filter_min_fill_time', self::min_fill_time ),
			) );
			wp_enqueue_script( 'arm_spam_filter_js' );
		}
		function armember_spam_filters_func( $atts, $content = '' ) {
			global $arm_global_settings,$ARMemberLite;
			
			if ( $this->is_enabled ) {
				$defaults = array(
					'var' => '',
				);
				/* Extract Shortcode Attributes */
				$opts = shortcode_atts( $defaults, $atts, 'spam_filters' );
				$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
				extract( $opts );

				$content .= $this->add_form_fields();
			} else {
				$content = '';
			}

			return do_shortcode( $content );
		}
		function add_form_fields() {
			$this->nonce_fields  = '<input type="hidden" name="" class="kpress" value="" />';
			$this->nonce_fields .= '<input type="hidden" name="" class="stime" value="' . esc_attr( time() + 14921 ) . '" />';
			$this->nonce_fields .= '<input type="hidden" data-id="arm_nonce_start_time" class="arm_nonce_start_time" value="' . esc_attr(self::arm_nonce_start_time) . '" />';
			$this->nonce_fields .= '<input type="hidden" data-id="arm_nonce_keyboard_press" class="arm_nonce_keyboard_press" value="' . esc_attr(self::arm_nonce_keyboard_press) . '" />';
			$this->nonce_fields .= '<input type="hidden" name="' . esc_attr( self::token_field ) . '" value="" />';
			if ( function_exists( 'wp_nonce_field' ) ) {
				$this->nonce_fields .= '<input type="hidden" name="' . esc_attr(self::nonce_name) . '" value="' . esc_attr(wp_create_nonce( self::nonce_action )) . '" />';

			}
			return $this->nonce_fields;
		}
		function validateTimedFormSubmission( $formContents = array() ) {
			$in_time = false;
			if ( empty( $formContents[ self::arm_nonce_start_time ] ) ) {
				$formContents[ self::arm_nonce_start_time ] = isset( $_REQUEST[ self::arm_nonce_start_time ] ) ? intval($_REQUEST[ self::arm_nonce_start_time ]) : ''; //phpcs:ignore
			}
			if ( isset( $formContents[ self::arm_nonce_start_time ] ) ) {
				$displayTime = intval($formContents[ self::arm_nonce_start_time ]) - 14921;
				$submitTime  = time();
				$fillOutTime = $submitTime - $displayTime;
				/* Less than 3 seconds */
				if ( $fillOutTime < 3 ) {
					$in_time = false;
				} else {
					$in_time = true;
				}
			}
			return $in_time;
		}
		function validateUsedKeyboard( $formContents = array() ) {
			$is_user_keyboard = false;
			if ( empty( $formContents[ self::arm_nonce_keyboard_press ] ) ) {
				$formContents[ self::arm_nonce_keyboard_press ] = isset( $_REQUEST[ self::arm_nonce_keyboard_press ] ) ? sanitize_text_field($_REQUEST[ self::arm_nonce_keyboard_press ]) : ''; //phpcs:ignore
			}
			if ( is_numeric($formContents[self::arm_nonce_keyboard_press]) ) {
				$is_user_keyboard = true;
			}
			if ( ! $is_user_keyboard && isset($_REQUEST[self::ux_field]) && is_numeric($_REQUEST[self::ux_field]) ) { //phpcs:ignore
				$is_user_keyboard = true;
			}
			return $is_user_keyboard;
		}
		function verifyNonceField( $nonce_value = '' ) {
			$return = '';
			if ( empty( $nonce_value ) ) {
				$nonce_value = isset( $_REQUEST[ self::nonce_name ] ) ? sanitize_text_field( $_REQUEST[ self::nonce_name ] ) : ''; //phpcs:ignore
			}
			if ( function_exists( 'wp_verify_nonce' ) ) {
				$nonce = wp_verify_nonce( $nonce_value, self::nonce_action );
				switch ( $nonce ) {
					case 1:
						$return = esc_html__( 'Nonce is less than 12 hours old', 'armember-membership' );
						break;

					case 2:
						$return = esc_html__( 'Nonce is between 12 and 24 hours old', 'armember-membership' );
						break;

					default:
						$return = false;
				}
			}
			return $return;
		}

		function validateReferer()
		{
			$result = array( 'pass' => false, 'hasReferrer' => false );

			$host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : ''; //phpcs:ignore
			$httpReferer = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : ''; //phpcs:ignore

			if ( $httpReferer === '' || $host === '' ) {
				/* No referer is not evidence of a bot. Reported, never fatal. */
				return $result;
			}

			$refererParsed = wp_parse_url( $httpReferer );
			if ( empty($refererParsed['host']) ) {
				return $result;
			}

			$result['hasReferrer'] = true;
			/* Exact host comparison. The old preg_match() on an unescaped host also matched
			   any domain that merely contained the site's host as a substring. */
			if ( strtolower( $refererParsed['host'] ) === strtolower( preg_replace( '/:\d+$/', '', $host ) ) ) {
				$result['pass'] = true;
			}
			return $result;
		}
	}
}
global $ARM_Spam_Filter;
$arm_Spam_Filter = new ARM_Spam_Filter_Lite();
