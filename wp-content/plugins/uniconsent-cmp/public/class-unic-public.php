<?php
/**
 * *
 *  * @link https://www.uniconsent.com/
 *  * @copyright Copyright (c) 2018 - 2019 Transfon Ltd.
 *  * @license https://www.uniconsent.com/wordpress/
 *
 */

use UNIC\UNIC_Values;

class UNIC_Public {

	private $plugin_name;

	private $version;

	private $unic_default_language;

	private $unic_display_language;
	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->unic_values = new UNIC_Values();

	}

	public function enqueue_styles() {

	}

	public function enqueue_scripts() {

		$unic_init = $this->get_unic_init_values();

$unic_template = <<<UNIC
window._unic_start = true;
window.__cmp = window.__cmp || function () {
	window.__cmp.commandQueue = window.__cmp.commandQueue || [];
	window.__cmp.commandQueue.push(arguments);
};
window.__cmp.commandQueue = window.__cmp.commandQueue || [];
window.__cmp.receiveMessage = function (event) {
	var data = event && event.data && event.data.__cmpCall;

	if (data) {
		var callId = data.callId,
		    command = data.command,
		    parameter = data.parameter;

		window.__cmp.commandQueue.push({
			callId: callId,
			command: command,
			parameter: parameter,
			event: event
		});
	}
};
var listen = window.attachEvent || window.addEventListener;
var eventMethod = window.attachEvent ? "onmessage" : "message";
listen(eventMethod, function (event) {
	window.__cmp.receiveMessage(event);
}, false);
function addLocatorFrame() {
	if (!window.frames['__cmpLocator']) {
		if (document.body) {
			var frame = document.createElement('iframe');
			frame.style.display = 'none';
			frame.name = '__cmpLocator';
			document.body.appendChild(frame);
		} else {
			setTimeout(addLocatorFrame, 5);
		}
	}
}
addLocatorFrame();
UNIC;
		echo "<script>\n";
		$unic_license = get_option( 'unic_license' );
		if(strpos($unic_license, 'key-') > -1) {
			$unic_license = str_replace('key-', '', $unic_license);
		} else {
			$unic_license = '69a3449348';
			echo "window.__unic_config = window.__unic_config || {}; window.__unic_config = ".$unic_init.";\n";
		}
		echo $unic_template."\n";
		echo "</script>\n";
		echo "<script async src='https://cmp.uniconsent.com/t/".$unic_license.".cmp.js'></script>\n";
	}

	public function get_unic_init_values() {

		$unic_init_vals = array();

		$unic_license = get_option( 'unic_license' );
		if(strpos($unic_license, 'key-') > -1) {
			$unic_init_vals['unic_license'] = str_replace('key-', '', $unic_license);
		} else {
			$unic_region = get_option( 'unic_region' );
			if(!$unic_region) {
				$unic_region = 'worldwide';
			}
			$unic_init_vals['unic_region'] = $unic_region;

			$unic_language = get_option( 'unic_language' );
			if(!$unic_language) {
				$unic_language = 'en';
			}
			$unic_init_vals['unic_language'] = $unic_language;

			$unic_company = get_option( 'unic_company' );
			if(!$unic_company) {
				$unic_company = 'Current website';
			}
			$unic_init_vals['unic_company'] = $unic_company;

			$unic_logo = get_option( 'unic_logo' );
			if(!$unic_logo) {
				$unic_logo = '';
			}
			$unic_init_vals['unic_logo'] = $unic_logo;

			$unic_policy_url = get_option( 'unic_policy_url' );
			if(!$unic_policy_url) {
				$unic_policy_url = '';
			}
			$unic_init_vals['unic_policy_url'] = $unic_policy_url;

			$unic_enable_iab = get_option( 'unic_enable_iab' );
			if(!$unic_enable_iab) {
				$unic_enable_iab = 'no';
			}
			$unic_init_vals['unic_enable_iab'] = $unic_enable_iab;

			$unic_type = get_option( 'unic_type' );
			if(!$unic_type) {
				$unic_type = 'popup';
			}
			$unic_init_vals['unic_type'] = $unic_type;
		}
		
		return json_encode( $unic_init_vals );

	}
}
