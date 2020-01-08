<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to upload the local logs to our API so it can
 * be shown on the portal.
 */
class W_Upload extends W_Core
{
	/**
	 * Add the actions required to upload logs to our API.
	 *
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);

		// In case the software has never been synchronized, force it.
		if (!get_option('webarx_software_data_hash', false)) {
			$this->upload_software();
		}

		// Register the actions.
		add_action('webarx_send_software_data', array($this, 'upload_software'));
		add_action('webarx_send_hacker_logs', array($this, 'upload_firewall_logs'));
		add_action('webarx_send_event_logs', array($this, 'upload_activity_logs'));

		// In case a plugin or upgrade has been performed, re-synchronize with the portal.
		add_action('activated_plugin', array($this, 'upload_software'));
        add_action('deactivated_plugin', array($this, 'upload_software'));
        add_action('upgrader_process_complete', array($this, 'upload_software'));
        add_action('_core_updated_successfully', array(&$this, 'upload_software'));
	}

	/**
	 * Synchronize the software data with our API.
	 * This includes plugins, themes, WordPress and PHP version.
	 *
	 * @return void
	 */
	public function upload_software()
	{
		// No need to contact API if no changes have been made to the software list.
		$data = $this->get_software_data();
        $currentSoftwareHash = sha1(json_encode($data));
        if (!isset($_POST['webarx_secret']) && get_option('webarx_software_data_hash', false) == $currentSoftwareHash) {
            return;
        }

		// Synchronize the software list with the API.
		$results = $this->plugin->api->upload_software(array('software' => json_encode($data)));
		if (isset($results['success'])) {
			update_option('webarx_software_data_hash', $currentSoftwareHash);
			return $results;
		}
		return;
	}

	/**
	 * Synchronize the firewall logs with our API.
	 *
	 * @return void
	 */
	public function upload_firewall_logs()
	{
		global $wpdb;
		$lastid = get_option('webarx_firewall_log_lastid', 0, true);
		$items = $wpdb->get_results($wpdb->prepare("SELECT id, ip, log_date, request_uri, referer, user_agent, fid, protocol, method, query_string, query_vars, post_data FROM " . $wpdb->prefix . "webarx_firewall_log WHERE id > %d ORDER BY id", $lastid));
		
		// No need to synchronize if there are no new logs present.
		if ($wpdb->num_rows == 0) {
			return;
		}

		// Construct the array to be uploaded to our API.
		$logs = array();
		foreach ($items as $item) {

			// Entries that we don't want to store on the API side.
			if (stripos($item->request_uri, 'wp-comments-post') !== false) {
				continue;
			}

			// Push to entries to be uploaded.
			$logs[] = array(
				'ip' => $item->ip,
				'fid' => $item->fid,
				'request_uri' => $item->request_uri,
				'user_agent' => $item->user_agent,
				'protocol' => $item->protocol,
				'method' => $item->method,
				'query_string' => $item->query_string,
				'query_vars' => $item->query_vars,
				'log_date' => $item->log_date,
				'post_data' => $item->post_data
			);
		}

		// JSON encode the logs and upload.
		$logs = json_encode($logs);
		$results = $this->plugin->api->upload_firewall_logs(array('logs' => $logs, 'type' => 'firewall'));
		if (isset($results['errors'])) {
			return;
		}

		// Get the most recent id of the logs.
		$lastid = $wpdb->get_var("SELECT id FROM " . $wpdb->prefix . "webarx_firewall_log ORDER BY id DESC LIMIT 0, 1");
		update_option('webarx_firewall_log_lastid', $lastid);

		// Delete logs that are older than 2 weeks.
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "webarx_firewall_log WHERE log_date < DATE_SUB(NOW(), INTERVAL 14 DAY)");
	}

    /**
     * Synchronize the activity logs with our API.
     * 
     * @return void
     */
	public function upload_activity_logs()
	{
        global $wpdb;

		// Determine if we should upload failed logins to the portal.
		$where = " AND action != 'failed login' ";
		if ($this->get_option('webarx_activity_log_failed_logins', false)) {
			$where = ' ';
		}

		// Do we have data to upload?
		$lastid = get_option('webarx_eventlog_lastid', 0);
        $items = $wpdb->get_results($wpdb->prepare("SELECT id, author, ip, object, object_id, object_name, action, date FROM " . $wpdb->prefix . "webarx_event_log WHERE id > %d" . $where . "ORDER BY id", array($lastid)));
        if ($wpdb->num_rows == 0) {
            return;
        }

		// Send to the API.
		$logs = json_encode($items);
        $results = $this->plugin->api->upload_activity_logs(array('logs' => $logs));
        if (isset($results['errors'])) {
            return;
        }

        // Get the most recent id of the logs.
        $lastid = $wpdb->get_var("SELECT id FROM " . $wpdb->prefix . "webarx_event_log ORDER BY id DESC LIMIT 0, 1");
        update_option('webarx_eventlog_lastid', $lastid);

        // Delete logs that are older than 2 weeks.
        $wpdb->query("DELETE FROM " . $wpdb->prefix . "webarx_event_log WHERE date < DATE_SUB(NOW(), INTERVAL 14 DAY)");
	}
	
	/**
	 * Obtain information about the software that the user has installed.
	 * This includes plugins, themes, WordPress and PHP version.
	 *
	 * @return array
	 */
	public function get_software_data()
	{
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (!function_exists('get_plugin_updates')) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		// Refetch updates data if we are performing a plugin listener related action.
		if (isset($_POST['webarx_secret'])) {
			@require_once ABSPATH . 'wp-includes/update.php';
			@wp_update_themes();
			@wp_update_plugins();
		}

		// Fetch list of plugins.
		$all_plugin = get_plugins();
		$installed_plugins = array_keys($all_plugin);
		$updatable_plugins = get_plugin_updates();
		$software_list = array();

		foreach ($installed_plugins as $plugin) {
			if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
				continue;
			}
			
			$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
			$new_version = empty($updatable_plugins[$plugin]->update->new_version) ? '' : $updatable_plugins[$plugin]->update->new_version;
			$plugin_name = empty($plugin_data['Name']) ? '' : $plugin_data['Name'];
			$plugin_version = empty($plugin_data['Version']) ? '' : $plugin_data['Version'];

			if (!empty($plugin_name) && !empty($plugin_version)) {
				$software_list[] = array(
					'sw_type' => 'plugin',
					'sw_name' => $plugin_name,
					'sw_cur_ver' => $plugin_version,
					'sw_new_ver' => $new_version,
					'sw_key' => $plugin,
					'sw_active' => is_plugin_active($plugin)
				);
			}
		}

		// Fetch list of themes.
		$themes = wp_get_themes();
		$themes_keys = array_keys($themes);
		$updatable_themes = get_theme_updates();

		foreach ($themes_keys as $theme_key) {
			$themes_data = $themes[$theme_key];
			$theme_temporary = empty($updatable_themes[$theme_key]) ? '' : $updatable_themes[$theme_key];
			$theme_new_version = empty($updatable_themes[$theme_key]) || !isset($theme_temporary->update, $theme_temporary->update['new_version']) ? '' : $theme_temporary->update['new_version'];
			$theme_name = $themes_data->get('Name');
			$theme_version = $themes_data->get('Version');

			if (!empty($theme_name) && !empty($theme_version)) {
				$software_list[] = array(
					'sw_type' => 'theme',
					'sw_name' => $theme_name,
					'sw_cur_ver' => $theme_version,
					'sw_new_ver' => $theme_new_version,
					'sw_key' => $theme_key
				);
			}
		}

		// Fetch WordPress version.
		global $wp_version;
		$core_updates = get_core_updates();
		$new_wp_version = (!empty($core_updates) && $core_updates[0]->response == 'upgrade') ? $core_updates[0]->version : '';
		$software_list[] = array(
			'sw_type' => 'wordpress',
			'sw_name' => 'WordPress',
			'sw_cur_ver' => $wp_version,
			'sw_new_ver' => $new_wp_version
		);

		// Fetch PHP version.
		$software_list[] = array(
			'sw_type' => 'php',
			'sw_name' => 'PHP',
			'sw_cur_ver' => substr(phpversion(), 0, 5),
			'sw_new_ver' => ''
		);

		return $software_list;
	}
}
