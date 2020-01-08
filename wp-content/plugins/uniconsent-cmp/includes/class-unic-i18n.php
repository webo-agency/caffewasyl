<?php
/**
 * *
 *  * @link https://www.uniconsent.com/
 *  * @copyright Copyright (c) 2018 Transfon Ltd.
 *  * @license https://www.uniconsent.com/wordpress/
 *
 */

class UNIC_i18n {

	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'uniconsent-cmp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
