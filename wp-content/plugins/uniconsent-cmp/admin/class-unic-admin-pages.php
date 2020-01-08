<?php
/**
 * *
 *  * @link https://www.uniconsent.com/
 *  * @copyright Copyright (c) 2018 - 2020 Transfon Ltd.
 *  * @license https://www.uniconsent.com/wordpress/
 *
 */

use UNIC\UNIC_Values;

class UNIC_Admin_Pages {

	private $unic_license;

	private $unic_options;

	private $unic_values;

	private $unic_language;

	private $unic_company;

	private $unic_policy_url;

	public function __construct() {

		$this->unic_values = new UNIC_Values();

		$this->unic_language_default = "en";
		$this->unic_language = esc_attr( get_option( 'unic_language' ) );

		$this->unic_language = isset( $this->unic_language ) && ! empty( $this->unic_language )
			? $this->unic_language
			: $this->unic_language_default;

		add_action( 'admin_init', array( $this, 'unic_options_page_init' ) );
		add_action( 'admin_menu', array( $this, 'add_unic_admin_pages' ) );

	}

	public function unic_options() { ?>

		<style>
		    #unic-left {
		    	width:670px;
		    	float: left;
		    }
		    #unic-right {
		    	position: absolute;
		    	top: 30px;
		        left: 670px;
    			width: 292px;
		    }
		    #unic-right .top {
		    	background: white;
		    	padding: 10px;
		    	text-align: center;
		    }
		    #unic-right .logo {
			    background: #555f80;
			    padding: 20px 10px;
			    text-align: center;
			    color: white;
		    }

		    #unic-right h3 {
		    	color: white;
		    }
		</style>

		<div class="wrap wrap-unic-options">
			<div class="admin-header">
				<div class="left"><h1><?php _e( 'UniConsent Options', 'unic-options' ); ?></h1></div>
                    
				<div class="clear"></div>
			</div>

			<?php settings_errors(); ?>

        <div id="unic-admin">

            <div id="unic-left">

        <form method="post" action="options.php">

				<?php settings_fields( 'unic-general-config' ); ?>
				<?php do_settings_sections( 'unic-general-config' ); ?>

				<table class="form-table options-form-table">


				<?php if(!($unic_license && strpos($unic_license, 'key-') > -1)): ?>

				<?php $unic_language = get_option( 'unic_language' ); ?>
					<tr class="table-top-row" valign="top">
						<th scope="row">
							<?php _e( 'Language', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<select name="unic_language">
								<option value="en" <?php selected( $unic_language, 'en' ); ?>><?php _e( 'English', 'uniconsent' ); ?></option>
								<option value="fr" <?php selected( $unic_language, 'fr' ); ?>><?php _e( 'French', 'uniconsent' ); ?></option>
								<option value="de" <?php selected( $unic_language, 'de' ); ?>><?php _e( 'German', 'uniconsent' ); ?></option>
								<option value="it" <?php selected( $unic_language, 'it' ); ?>><?php _e( 'Italian', 'uniconsent' ); ?></option>
								<option value="es" <?php selected( $unic_language, 'es' ); ?>><?php _e( 'Spanish', 'uniconsent' ); ?></option>
								<option value="pt" <?php selected( $unic_language, 'pt' ); ?>><?php _e( 'Portuguese', 'uniconsent' ); ?></option>
								<option value="pl" <?php selected( $unic_language, 'pl' ); ?>><?php _e( 'Polish', 'uniconsent' ); ?></option>
								<option value="nl" <?php selected( $unic_language, 'nl' ); ?>><?php _e( 'Dutch', 'uniconsent' ); ?></option>
							</select>
						</td>
					</tr>

					<?php $unic_company = get_option( 'unic_company'); ?>

					<tr valign="top">
						<th scope="row">
							<?php _e( 'Company Name (Optional)', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<input name="unic_company" type="text" value="<?php echo get_option( 'unic_company', $this->unic_company ); ?>">
						</td>
					</tr>

					<?php $unic_logo = get_option( 'unic_logo'); ?>

					<tr valign="top">
						<th scope="row">
							<?php _e( 'Website LOGO (Optional)', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<input name="unic_logo" type="text" value="<?php echo get_option( 'unic_logo', $this->unic_logo ); ?>">
						</td>
					</tr>

					<?php $unic_policy_url = get_option( 'unic_policy_url'); ?>
					
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Policy URL (Optional)', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<input name="unic_policy_url" type="text" value="<?php echo get_option( 'unic_policy_url', $this->unic_policy_url ); ?>">
							<div class="desc">
								<strong><?php _e( 'Example:', 'uniconsent' ); ?></strong> <?php _e( 'https://www.example.com/policy', 'uniconsent' ); ?>
							</div>
						</td>
					</tr>

					<?php $unic_region = get_option( 'unic_region' ); ?>
					<tr class="table-top-row" valign="top">
						<th scope="row">
							<?php _e( 'Display Consent', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<select name="unic_region">
								<option value="none" <?php selected( $unic_region, 'none' ); ?>><?php _e( 'None', 'uniconsent' ); ?></option>
								<option value="worldwide" <?php selected( $unic_region, 'worldwide' ); ?>><?php _e( 'Worldwide', 'uniconsent' ); ?></option>
								<option value="eu" <?php selected( $unic_region, 'eu' ); ?>><?php _e( 'EU (EEA) Countries', 'uniconsent' ); ?></option>
							</select>
							<div class="desc">
								<p>When choose EU, only display consent to the users in EU countries.</p>
							</div>
						</td>
					</tr>

					<?php $unic_type = get_option( 'unic_type' ); ?>
					<tr class="table-top-row" valign="top">
						<th scope="row">
							<?php _e( 'Display Type', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<select name="unic_type">
								<option value="bar" <?php selected( $unic_type, 'bar' ); ?>><?php _e( 'Bottom Bar', 'uniconsent' ); ?></option>
								<option value="popup" <?php selected( $unic_type, 'popup' ); ?>><?php _e( 'Popup Box', 'uniconsent' ); ?></option>
							</select>
						</td>
					</tr>

					<?php $unic_enable_iab = get_option( 'unic_enable_iab'); ?>
					<tr class="table-top-row" valign="top">
						<th scope="row">
							<?php _e( 'Enable IAB Compliance', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<select name="unic_enable_iab">
								<option value="no" <?php selected( $unic_enable_iab, 'no' ); ?>><?php _e( 'No', 'uniconsent' ); ?></option>
								<option value="yes" <?php selected( $unic_enable_iab, 'yes' ); ?>><?php _e( 'Yes', 'uniconsent' ); ?></option>
							</select>
						</td>
					</tr>

<!-- 					<?php $unic_enable_google = get_option( 'unic_enable_google'); ?>
					<tr class="table-top-row" valign="top">
						<th scope="row">
							<?php _e( 'Enable Google Support', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<select name="unic_enable_google">
								<option value="no" <?php selected( $unic_enable_google, 'no' ); ?>><?php _e( 'No', 'uniconsent' ); ?></option>
								<option value="yes" <?php selected( $unic_enable_google, 'yes' ); ?>><?php _e( 'Yes', 'uniconsent' ); ?></option>
							</select>
						</td>
					</tr> -->

<!-- 					<?php $unic_enable_cookie = get_option( 'unic_enable_cookie'); ?>
					<tr class="table-top-row" valign="top">
						<th scope="row">
							<?php _e( 'Delay cookies', 'uniconsent' ); ?>
						</th>
						<td class="col-2">
							<select name="unic_enable_cookie">
								<option value="no" <?php selected( $unic_enable_cookie, 'no' ); ?>><?php _e( 'No', 'uniconsent' ); ?></option>
								<option value="yes" <?php selected( $unic_enable_cookie, 'yes' ); ?>><?php _e( 'Yes', 'uniconsent' ); ?></option>
							</select>
							<div class="desc">
								<p>Don't store cookies before collecting consent.</p>
							</div>
						</td>
					</tr> -->

				<?php endif;?>

				<?php $unic_license = get_option( 'unic_license'); ?>

				<tr valign="top">
					<th scope="row">
						<?php _e( 'License key (Optional)', 'uniconsent' ); ?>
					</th>
					<td class="col-2">
						<input name="unic_license" type="text" value="<?php echo get_option( 'unic_license', $this->unic_license ); ?>">
						<div class="desc">
								<?php _e( 'Get your free license key at:', 'uniconsent' ); ?> <a target="_blank" href="https://www.uniconsent.com/?utm_source=wp_license">https://www.uniconsent.com/</a>
								<p>* The configurations are managed at <a target="_blank" href="https://www.uniconsent.com/?utm_source=wp_license">https://www.uniconsent.com/</a> once you have entered the license key: <b>key-xxxxxxxx</b>.</p>
							</div>
					</td>
				</tr>



				</table>

				<table class="form-table options-form-table">
					<tr>
						<td><?php submit_button(); ?></td>
					</tr>
				</table>

			</form>
            </div>

            <div id="unic-right">
            	<div class="top">
                    <a target="_blank" href="<?php _e( 'https://www.uniconsent.com', 'uniconsent' ); ?>" target="_blank">
                        <img src="<?php echo plugins_url( 'uniconsent-cmp/admin/images/unic-logo.png' ); ?>" style="width: 200px;" />
                    </a>
                </div>
                    
                <div class='logo'>
                	<div>
                        <h3>UniConsent for GDPR</h3>
                        <ul>
                        	<li><strong>Certified IAB CMP</strong>
                            <li><strong>Fully customisable multiple stages consent collection pop-ups, bars</strong>
                            <li><strong>Multiple languages support</strong>
                            <li><strong>Data analytics and inisght dashboard</strong>
                            <li><strong>One-tag Implementation</strong>
                            <li><strong>Google DFP support</strong>
                            <li><strong>Cookie ePrivacy consent support</strong>
                            <li><strong>Website cookie discovery and disclose</strong>
                            <li><strong>Javascript and cookie blocking</strong>
                            <li><strong>Consent rate analytics and insight</strong>
                            <li><strong>Support: support@uniconsent.com</strong>
                        </ul>
                        <a class="button button-primary" href="https://app.uniconsent.com/app/register?utm_source=wp" target="_blank">Sign up for free</a>
                        
                    </div>
                </div>
            </div>

        </div>

		</div>
		<?php

	}
 
	public function add_unic_admin_pages() {

		$unic_admin_page = add_menu_page(
			'UniConsent CMP',
			'UniConsent CMP',
			'administrator',
			'unic-options',
			array( $this, 'unic_options' )
		);

	}

	public function unic_options_page_init() {

		register_setting(
			'unic-general-config', // Option group
			'unic_license', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_language', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_type', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_region', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_company', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_logo', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_policy_url', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'unic-general-config', // Option group
			'unic_enable_iab', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		// register_setting(
		// 	'unic-general-config', // Option group
		// 	'unic_enable_google', // Option name
		// 	array(
		// 		'type' => 'string',
		// 		'sanitize_callback' => array( $this, 'sanitize_text' )
		// 	)
		// );

		// register_setting(
		// 	'unic-general-config', // Option group
		// 	'unic_enable_cookie', // Option name
		// 	array(
		// 		'type' => 'string',
		// 		'sanitize_callback' => array( $this, 'sanitize_text' )
		// 	)
		// );
	}

	public function sanitize_text( $input ) {

		$input = sanitize_text_field( $input );
		return $input;

	}

	public function sanitize_url( $input ) {

		$input = esc_url( $input );
		return $input;

	}
}
