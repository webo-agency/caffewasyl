<?php
/**
 * *
 *  * @link https://www.uniconsent.com/
 *  * @copyright Copyright (c) 2018 Transfon Ltd.
 *  * @license https://www.uniconsent.com/wordpress/
 *
 */

class UNIC_CMP {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		if ( defined( 'UNIC_VERSION' ) ) {
			$this->version = UNIC_VERSION;
		} else {
			$this->version = '1.2.4';
		}
		$this->plugin_name = 'uniconsent-cmp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-unic-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-unic-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-unic-values.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-unic-admin-pages.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-unic-public.php';

		$this->loader = new UNIC_Loader();

	}

	private function set_locale() {

		$plugin_i18n = new UNIC_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {

		$plugin_admin_pages = new UNIC_Admin_Pages();

	}

	private function define_public_hooks() {

		$plugin_public = new UNIC_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
