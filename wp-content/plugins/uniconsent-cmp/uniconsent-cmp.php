<?php

/**
* Plugin Name: UniConsent CMP for GDPR (Cookie Consent)
* Plugin URI: https://www.uniconsent.com/wordpress
* Description: UniConsent plugin implements the IAB Consent Framework for GDPR and Google DFP Consent guidance.
* Version: 1.2.4
* Author: UniConsent
* Author URI: https://www.uniconsent.com/
* License: GPL2
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'UNIC_CMP_VERSION', '1.2.4' );

function activate_unic_cmp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-unic-activator.php';
	UNIC_Activator::activate();
}

function deactivate_unic_cmp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-unic-deactivator.php';
	UNIC_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_unic_cmp' );
register_deactivation_hook( __FILE__, 'deactivate_unic_cmp' );

require plugin_dir_path( __FILE__ ) . 'includes/class-unic-cmp.php';

function run_unic_cmp() {

	$plugin = new UNIC_CMP();
	$plugin->run();

}
run_unic_cmp();
