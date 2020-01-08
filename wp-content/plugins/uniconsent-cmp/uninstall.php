<?php
/**
 * *
 *  * @link https://www.uniconsent.com/
 *  * @copyright Copyright (c) 2018 - 2019 Transfon Ltd.
 *  * @license https://www.uniconsent.com/wordpress/
 *
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean out the plugin option values on uninstall
delete_option('unic_version');
delete_option('unic_region');
delete_option('unic_language');
delete_option('unic_company');
delete_option('unic_license');
delete_option('unic_logo');
delete_option('unic_policy_url');
delete_option('unic_enable_iab');
delete_option('unic_enable_google');
delete_option('unic_enable_cookie');


