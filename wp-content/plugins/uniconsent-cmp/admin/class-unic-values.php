<?php
/**
 * *
 *  * @link https://www.uniconsent.com/
 *  * @copyright Copyright (c) 2018 - 2020 Transfon Ltd.
 *  * @license https://www.uniconsent.com/wordpress/
 *
 */

namespace UNIC;

class UNIC_Values {

	private $default_values;

	public function __construct() {

		$this->default_values = array(
			'unic_language'  => 'en',
			'unic_region'	=> 'worldwide',
			'unic_enable_iab' => 'no',
			'unic_enable_google' => 'no',
			'unic_enable_cookie' => 'no',
			'unic_type' => 'popup',
		);

	}
}
