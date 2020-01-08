<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to provide several hardening options.
 */
class W_Hardening extends W_Core
{
    /**
     * Add the actions required for the hardening of the site.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);

        // The hardening features can only be used on an activated license.
        if (!$this->license_is_active()) {
            return;
		}
		
		// Disallowed modification of the theme files?
		if(!defined('DISALLOW_FILE_EDIT') && $this->get_option('webarx_pluginedit', true)){
			define('DISALLOW_FILE_EDIT', 1);
		}

		// Set security headers
        add_filter('wp_headers', array($this, 'set_security_headers'));

		// When country blocking is set.
		if($this->get_option('webarx_geo_block_enabled', false) && !empty($this->get_option('webarx_geo_block_countries', array()))){
			add_action('init', array($this, 'geo_block_check'), 10);
		}

		// Apply comment captcha?
        if ($this->get_option('webarx_captcha_on_comments', 0) && !is_user_logged_in()) {
			add_action('comment_form_after_fields', array($this, 'captcha_display'));
			add_filter('preprocess_comment', array($this, 'verify_recaptcha'));
        }

		// Block unauthorized XML-RPC requests?
        if ($this->get_option('webarx_xmlrpc_is_disabled', false) == true) {
            add_filter('xmlrpc_enabled', '__return_false');
		}

		// Block unauthorized wp-json requests?
		if ($this->get_option('webarx_json_is_disabled', false)) {
			add_filter('rest_authentication_errors', array($this, 'disable_wpjson'));
		}

		// Delete the readme file?
		if (get_site_option('webarx_rm_readme', false) == true) {
			$this->delete_readme();
		}

		// Block WPScan user agent?
		if ($this->get_option('webarx_basicscanblock')) {
			$this->detect_user_agent();
		}

		// Prevent user enumeration?
		if ($this->get_option('webarx_userenum')) {
		    $this->stop_user_enum();
		}

		// Attempt to hide the WordPress version?
		if ($this->get_option('webarx_hidewpversion')) {
			remove_action('wp_head', 'wp_generator');
			add_filter('the_generator', array($this, 'remove_generator'));
		}

		// Block email registration patterns?
		if ($this->get_option('webarx_register_email_blacklist', '') != '') {
			add_filter('registration_errors', array($this, 'check_email_pattern'), 1, 3);
			add_filter('wpmu_validate_user_signup', array($this, 'check_email_pattern_wpmu'), 1, 1);
		}
	}

	/**
	 * Determine the country of the user and if we should block the user.
	 * 
	 * @return void
	 */
	public function geo_block_check()
	{
		$countries = $this->get_option('webarx_geo_block_countries', array());
		$ip = $this->get_ip();

		// Don't block WebARX.
		if(in_array($ip, $this->plugin->ips)){
			return;
		}

		// Load the required libraries.
		try {
			require_once __DIR__ . '/../lib/geoip2-php/autoload.php';
			$reader = new GeoIp2\Database\Reader(__DIR__ . '/../lib/GeoLite2-Country.mmdb');
			$record = $reader->country($ip);

			// Determine if we want to do an inverse check or not.
			$isMatch = in_array($record->country->isoCode, $countries);
			$isMatch = $this->get_option('webarx_geo_block_inverse', false) ? !$isMatch : $isMatch;

			// Check if there's a match.
			if ($isMatch) {
				$this->plugin->firewall_base->display_error_page(22);
			}			
		} catch (\Exception $e) { }
	}

	/**
	 * Prevent unauthorized users from accessing wp-json.
	 * 
	 * @return void|WP_Error
	 */
	public function disable_wpjson()
	{
		if (!is_user_logged_in()) {
			$msg = apply_filters('disable_wp_rest_api_error', __('The WP REST API cannot be accessed by unauthorized users.', 'disable-wp-rest-api'));
			return new WP_Error('rest_authorization_required', $msg, array('status' => rest_authorization_required_code()));
		}
	}

    /**
     * Set security headers if the option is enabled.
	 * 
	 * @return void|array
     */
    public function set_security_headers()
    {
        if (get_site_option('webarx_add_security_headers')) {
            $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
            $headers['X-Frame-Options'] = 'SAMEORIGIN';
            $headers['X-XSS-Protection'] = '1; mode=block';
            $headers['X-Content-Type-Options'] = 'nosniff';
            $headers['X-Powered-By'] = null;
            $headers['Server'] = null;
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
            return $headers;
        }
    }

	/**
	 * Determine if the reCAPTCHA is valid upon comment submission.
	 * 
	 * @param array $comment_data
	 * @return void|array
	 */
	public function verify_recaptcha($comment_data)
	{
        $result = $this->captcha_check();
        if (!$result['response'] && ($result['reason'] === 'VERIFICATION_FAILED' || $result['reason'] === 'RECAPTCHA_EMPTY_RESPONSE')) {
            wp_clear_auth_cookie();
			wp_die('reCaptcha was not solved or response was empty', 'Error');
        }

        return $comment_data;
    }

	/**
	 * Add the captcha to the comments form.
	 * 
	 * @return void
	 */
	public function captcha_display()
	{
		if ($this->get_option('webarx_captcha_type') == 'v2') {
			$site_key = trim($this->get_option('webarx_captcha_public_key'));
			require_once dirname(__FILE__) . '/views/captcha_v2.php';
        } else {
			$site_key = trim($this->get_option('webarx_captcha_public_key_v3'));
			require_once dirname(__FILE__) . '/views/captcha_invisible.php';
		}
    }

	/**
	 * Check if the submitted reCAPTCHA is valid.
	 *
	 * @return array
	 */
	public function captcha_check()
	{
        if ($this->get_option('webarx_captcha_type') == 'v2') {
            $secret_key = trim($this->get_option('webarx_captcha_private_key'));
		    $site_key = trim($this->get_option('webarx_captcha_public_key'));
        } else {
            $secret_key = trim($this->get_option('webarx_captcha_private_key_v3'));
            $site_key = trim($this->get_option('webarx_captcha_public_key_v3'));
        }

		if (!$secret_key || !$site_key) {
			return array('response' => false, 'reason' => 'ERROR_NO_KEYS');
		}

		if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
			return array('response' => false, 'reason' => 'RECAPTCHA_EMPTY_RESPONSE');
		}

		$response = $this->plugin->hardening->get_captcha_response($secret_key);
		if (isset($response['success']) && !empty($response['success'])) {
			return array('response' => true, 'reason' => '');
		}

		return array('response' => false, 'reason' => 'VERIFICATION_FAILED');
	}

	/**
	 * Query Google for reAPTCHA validation and response.
	 *
	 * @param string $privatekey
	 * @return array
	 */
	public function get_captcha_response($privatekey)
	{
		$args = array(
			'body' => array(
				'secret' => $privatekey,
				'response' => $_POST['g-recaptcha-response']
			),
			'sslverify' => false
		);
		$resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $args);
		return json_decode(wp_remote_retrieve_body($resp), true);
	}

	/**
	 * Delete the readme.html file.
	 * 
	 * @return void
	 */
	public function delete_readme()
	{
		if (!file_exists(ABSPATH . 'readme.html')) {
			return;
		}

		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
		$fs = new WP_Filesystem_Direct('');
		$fs->delete(ABSPATH . 'readme.html');
	}

    /**
     * Disable user enumeration with ?author=
     *
	 * @return void
     */
	public function stop_user_enum()
    {
		$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
		
        if (isset($_GET['author']) && $uri_parts[0] == '/') {
            $user = get_userdata($_GET['author']);
            if ($user) {
                die(wp_redirect(get_site_url()));
            }
        }
    }

	/**
	 * Block WPScan user agent.
	 * 
	 * @return void
	 */
	public function detect_user_agent()
	{
		if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/WPScan/i', $_SERVER['HTTP_USER_AGENT'])) {
			exit;
		}
	}

	/**
	 * Hide the WordPress generator version in response.
	 * 
	 * @return string
	 */
	public function remove_generator()
	{
		return '';
	}

	/**
	 * Determine if the email address of a new registration matches the defined patterns.
	 * This filter is called on regular sites.
	 * 
	 * @param object $errors
	 * @param string $sanitized_user_login
	 * @param string $user_email
	 * @return object
	 */
	public function check_email_pattern($errors, $sanitized_user_login, $user_email)
	{
		$patterns = explode(',', $this->get_option('webarx_register_email_blacklist'));
		foreach ($patterns as $pattern) {
			if (stripos($user_email, $pattern) !== false) {
				$errors->add('user_email', __('An invalid email address has been supplied.', 'webarx'));
			}
		}

		return $errors;
	}

	/**
	 * Determine if the email address of a new registration matches the defined patterns.
	 * This filter is called on network sites.
	 * 
	 * @param array $result
	 * @return array
	 */
	public function check_email_pattern_wpmu($result)
	{
		if (isset($result['user_email'])) {
			$patterns = explode(',', $this->get_option('webarx_register_email_blacklist'));
			foreach ($patterns as $pattern) {
				if (stripos($result['user_email'], $pattern) !== false) {
					$result['errors']->add('user_email', __('An invalid email address has been supplied.', 'webarx'));
				}
			}
		}

		return $result;
	}
}
