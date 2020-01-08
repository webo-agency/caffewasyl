<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to communicate from the API to the plugin.
 */
class W_Listener extends W_Core
{
	/**
	 * Add the actions required to hide the login page.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
        parent::__construct($core);

        // Only hook into the action if the authentication is set and valid.
        if (isset($_POST['webarx_secret']) && ($this->authenticated($_POST['webarx_secret']) || $this->isAuthorizedOld($_POST['webarx_secret']))) {
            add_action('init', array($this, 'handleRequest'));
        }
    }

    /**
     * Extend update_plugins for updating the WebARX plugin.
     * 
     * @param object $update_plugins
     * @return object
     */
    public function extend_filter_update_plugins($update_plugins)
    {
        if (!is_object($update_plugins)) {
            return $update_plugins;
        }
            
        if (!isset($update_plugins->response) || !is_array($update_plugins->response)) {
            $update_plugins->response = array();
        }

        $update_plugins->response['webarx/webarx.php'] = (object) array(
            'slug' => 'webarx',
            'url' => $this->plugin->update_checker_url,
            'package' => $this->plugin->update_download_url,
        );
        return $update_plugins;
    }

    /**
     * Determine if the provided secret hash equals the sha1 of the private id and key.
     *
     * @param string $providedSecret Hash that is sent from our API.
     * @return boolean
     */
    public function isAuthorizedOpenSSL($providedSecret)
    {
        $id = get_option('webarx_clientid', '');
        $secret = get_option('webarx_secretkey', '');

        // Get the hash and token.
        $hash = sha1($id . $secret);
        $token = openssl_decrypt(base64_decode($providedSecret), 'AES-256-CBC', $hash);
        return $token === $hash;
    }

    /**
     * Get list of all users on WordPress
     * 
     * @return void
     */
    public function listUsers()
    {
        header('Content-Type: application/json');

        // Only fetch data we actually need.
        $users = get_users();
        $roles = wp_roles();
        $roles = $roles->get_names();
        $data = array();

        // Loop through all users.
        foreach ($users as $user) {

            // Get text friendly version of the role.
            $roleText = '';
            foreach ($user->roles as $role) {
                if (isset($roles[$role])) {
                    $roleText .= $roles[$role] . ', ';
                } else {
                    $roleText .= $role . ', ';
                }
            }

            // Push to array that we will eventually output.
            array_push($data, array(
                'id' => $user->data->ID,
                'username' => $user->data->user_login,
                'email' => $user->data->user_email,
                'roles' => substr($roleText, 0, -2)
            ));
        }
        die(json_encode(array('users' => $data)));
    }

    /**
     * Delete user of a given user ID.
     * 
     * @return void
     */
    public function deleteUser()
    {
        if (!ctype_digit($_POST['webarx_delete_user'])) {
            exit;
        }

        @include_once(ABSPATH . '/wp-admin/includes/user.php');
        wp_delete_user($_POST['webarx_delete_user']);
        die(json_encode(array('success' => 'The user has been deleted.')));
    }

    /**
     * Update password of a given user ID.
     * 
     * @return void
     */
    public function updatePassword()
    {
        if (!ctype_digit($_POST['webarx_edit_user'])) {
            exit;
        }

        wp_set_password($_POST['pass'], $_POST['webarx_edit_user']);
        die(json_encode(array('success' => 'The password of the user has been changed.')));
    }

    /**
     * Create an user inside WordPress.
     * 
     * @return void
     */
    public function createUser()
    {
        $user = wp_insert_user(array(
            'user_login' => $_POST['user_login'],
            'user_pass' => $_POST['pass1'],
            'user_email' => $_POST['user_email'],
            'role' => $_POST['role']
        ));

        $this->returnResults($user, 'The user has been created', 'Something went wrong while creating a user.');
    }


    /**
     * Switch the firewall status from on to off or off to on.
     *
     * @return string
     */
    public function switchFirewallStatus()
    {
        $state = $this->get_option('webarx_basic_firewall') == 1;
        update_option('webarx_basic_firewall', $state == 1 ? 0 : 1);
        die(json_encode(array('success' => 'Firewall ' . ($state == 1 ? 'disabled' : 'enabled') . '.')));
    }

    /**
     * Upgrade the core of WordPress.
     *
     * @return string|void
     */
    public function wordpressCoreUpgrade()
    {
        @set_time_limit(120);

        // Get the core update info.
        wp_version_check();
        $core = get_site_transient('update_core');

        // Any updates available?
        if (!isset($core->updates)) {
            die(json_encode(array('success' => 'No update available at this time.')));
        }

        // Are we on the latest version already?
        if ($core->updates[0]->response == 'latest') {
            die(json_encode(array('success' => 'Site is already running the latest version available.')));
        }

        // Require some libraries and attempt the upgrade.
        @include_once(ABSPATH . '/wp-admin/includes/admin.php');
        @include_once(ABSPATH . '/wp-admin/includes/class-wp-upgrader.php');
        $upgrader = new Core_Upgrader();
        $results = $upgrader->upgrade($core->updates[0], array('attempt_rollback' => true, 'do_rollback' => true));

        // Synchronize again with the API.
        $this->plugin->upload->upload_software();
        $this->returnResults($results, 'WordPress code has been upgraded', $results->get_error_message());
        exit;
    }

    /**
     * Upgrade a WordPress theme.
     * 
     * @return string|void
     */
    public function themeUpgrade()
    {
        @set_time_limit(120);

        // Require some files we need to execute the upgrade.
        @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        if (file_exists(ABSPATH . 'wp-admin/includes/class-theme-upgrader.php')) {
            @include_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
        }
        @include_once ABSPATH . 'wp-admin/includes/misc.php';
        @include_once ABSPATH . 'wp-admin/includes/file.php';

        // Upgrade the theme.
        $upgrader = new Theme_Upgrader();
        $upgrade = $upgrader->upgrade($_POST['webarx_theme_upgrade']);

        // Synchronize again with the API.
        $this->plugin->upload->upload_software();
        $this->returnResults($upgrade, 'WordPress code has been upgraded', $upgrade->get_error_message());
        exit;
    }

    /**
     * Upgrade a batch of plugins at once.
     *
     * @return string|void
     */
    public function pluginsUpgrade()
    {
        @set_time_limit(120);

        // Must have a valid number of plugins received to upgrade.
        $plugins = explode('|', $_POST['webarx_plugins_upgrade']);
        if (count($plugins) == 0) {
            die(json_encode(array('error' => 'No valid plugin names have been given.')));
        }

        // Require some files we need to execute the upgrade.
        @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        if (file_exists(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php')) {
            @include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        }

        @include_once ABSPATH . 'wp-admin/includes/plugin.php';
        @include_once ABSPATH . 'wp-admin/includes/misc.php';
        @include_once ABSPATH . 'wp-admin/includes/file.php';
        @wp_update_plugins();
        $all_plugins = get_plugins();

        // New array with all available plugins and the ones we want to upgrade.
        $upgrade = array();
        foreach ($all_plugins as $path => $data) {
            $t = explode('/', $path);
            if (in_array($t[0], $plugins)) {
                array_push($upgrade, $path);
            }
        }

        // Don't continue if we have no valid plugins to upgrade.
        if (count($upgrade) == 0) {
            die(json_encode(array('error' => 'No valid plugin names have been given.')));
        }

        // Upgrade the plugins.
        $upgrader = new Plugin_Upgrader();
        print_r($upgrader->bulk_upgrade($upgrade));

        // Synchronize again with the API.
        $this->plugin->upload->upload_software();
        exit;
    }

    /**
     * Toggle the state of a batch of plugin to activated or de-activated.
     *
     * @return string|void
     */
    public function pluginsToggle()
    {
        @set_time_limit(120);

        // Must have a valid number of plugins received to toggle.
        $plugins = explode('|', $_POST['webarx_plugins']);
        if (count($plugins) == 0) {
            die(json_encode(array('error' => 'No valid plugin names have been given.')));
        }

        @include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins = get_plugins();

        // New array with all available plugins and the ones we want to toggle.
        $toggle = array();
        foreach ($all_plugins as $path => $data) {
            $t = explode('/', $path);

            // Don't continue if the plugin does not exist locally.
            if (!in_array($t[0], $plugins)) {
                continue;
            }

            // If plugin should be turned on, check if it's already turned on first.
            if ($_POST['webarx_plugins_toggle'] == 'on' && !is_plugin_active($path)) {
                array_push($toggle, $path);
            }

            // If plugin should be turned off, check if it's already turned off first.
            if ($_POST['webarx_plugins_toggle'] == 'off' && is_plugin_active($path)) {
                array_push($toggle, $path);
            }
        }

        // Don't continue if we have no valid plugins to toggle..
        if (count($toggle) == 0) {
            die(json_encode(array('error' => 'The plugins are already turned ' . $_POST['webarx_plugins_toggle'] . '.')));
        }

        // Turn the plugins on or off?
        if ($_POST['webarx_plugins_toggle'] == 'on') {
            activate_plugins($toggle);
        }

        if ($_POST['webarx_plugins_toggle'] == 'off') {
            deactivate_plugins($toggle);
        }

        // Synchronize again with the API.
        $this->plugin->upload->upload_software();
        die(json_encode(array('success' => 'The ' . (count($toggle) == 1 ? 'plugin has' : 'plugins have') . ' been successfully turned ' . $_POST['webarx_plugins_toggle'] . '.')));
    }

    /**
     * Delete a batch of plugins.
     *
     * @return string|void
     */
    public function pluginsDelete()
    {
        @set_time_limit(120);

        // Must have a valid number of plugins received to toggle.
        $plugins = explode('|', $_POST['webarx_plugins']);
        if (count($plugins) == 0) {
            die(json_encode(array('error' => 'No valid plugin names have been given.')));
        }

        @include_once ABSPATH . 'wp-admin/includes/file.php';
        @include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins = get_plugins();

        // New array with all available plugins and the ones we want to toggle.
        $delete = array();
        foreach ($all_plugins as $path => $data) {
            $t = explode('/', $path);

            // Don't continue if the plugin does not exist locally.
            if (!in_array($t[0], $plugins)) {
                continue;
            }

            array_push($delete, $path);
        }

        // Don't continue if we have no valid plugins to toggle..
        if (count($delete) == 0) {
            die(json_encode(array('error' => 'No valid plugins to delete.')));
        }

        @deactivate_plugins($delete);
        @delete_plugins($delete);

        // Synchronize again with the API.
        @$this->plugin->upload->upload_software();
        die(json_encode(array('success' => 'The plugins have been successfully deleted.')));
    }

    /**
     * Function for WebARX plugin upgrade
     * In case of plugin installation failure
     * revert back to old plugin version
     *
     * @return boolean|void
     * @throws
     */

    public function upgradeWebARX()
    {
        // Include some necessary files to execute the upgrade.
        @include_once ABSPATH . 'wp-admin/includes/file.php';
        @include_once ABSPATH . 'wp-admin/includes/plugin.php';
        @include_once ABSPATH . 'wp-admin/includes/misc.php';
        @include_once ABSPATH . 'wp-includes/pluggable.php';
        @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        @include_once ABSPATH . 'wp-admin/includes/update.php';
        @require_once ABSPATH . 'wp-admin/includes/update-core.php';
        if (file_exists(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php')) {
            @include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        }

        add_filter('site_transient_update_plugins', array($this, 'extend_filter_update_plugins'));
        add_filter('transient_update_plugins', array($this, 'extend_filter_update_plugins'));

        $title = __('Update Plugin');
        $plugin = 'webarx/webarx.php';
        wp_enqueue_script('updates');

        $nonce = 'upgrade-plugin_' . $plugin;
        $url = 'update.php?action=upgrade-plugin&plugin=' . urlencode($plugin);
        get_site_transient('update_plugins');

        $upgrader = new Plugin_Upgrader(new Plugin_Upgrader_Skin(compact('title', 'nonce', 'url', 'plugin')));
        $upgrader->upgrade($plugin);

        // Activate the plugin and return the result.
        activate_plugin('webarx/webarx.php');

        // Synchronize again with the API.
        $this->plugin->upload->upload_software();
        die(json_encode(array('success' => 'WebARX plugin has been upgraded.')));
    }

    /**
     * Save received options.
     *
     * @return void
     */
    public function saveOptions()
    {
        if (!isset($_POST['webarx_set_options'], $_POST['webarx_secret']) || !$this->authenticated($_POST['webarx_secret'])) {
            exit;
        }

        // Get the received options.
        $options = json_decode(base64_decode($_POST['webarx_set_options']));
        header('Content-Type: application/json');
        foreach ($options as $key => $value) {
            if (substr($key, 0, 7) === 'webarx_') {
                update_option($key, $value);
            }
        }
        die(json_encode(array('success' => 'Plugin options has been updated!')));
    
    }

    /**
     * Return list of keys and values of webarx options.
     *
     * @return array
     */
    public function getAvailableOptions()
    {
        global $wpdb;
        $settings = $wpdb->get_results("SELECT * FROM " . $wpdb->options . " WHERE option_name LIKE 'webarx_%'");
        header('Content-Type: application/json');
        die(json_encode($settings));
    }

    /**
     * Pull firewall rules from the API.
     * 
     * @return void
     */
    public function refreshRules()
    {
        do_action('webarx_post_dynamic_firewall_rules');
        $this->returnResults(null, 'Firewall rules have been refreshed', null);
        exit;
    }

    /**
     * Get a list of IP addresses that are currently banned by the firewall.
     * 
     * @return void
     */
    private function getFirewallBans()
    {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT ip FROM " . $wpdb->prefix . "webarx_firewall_log WHERE apply_ban = 1 AND log_date >= (STR_TO_DATE(NOW(), '%%Y-%%m-%%d %%H:%%i:%%s') - INTERVAL %d MINUTE) GROUP BY ip", array(($this->get_option('webarx_autoblock_minutes', 30) + $this->get_option('webarx_autoblock_blocktime', 60)))), OBJECT
        );

        $out = array();
        foreach($results as $result) {
            if (isset($result->ip)) {
                array_push($out, $result->ip);
            }
        }
        die(json_encode($out));
    }

    /**
     * Unban a specific IP address from the firewall.
     * 
     * @return void
     */
    private function unbanFirewallIp()
    {
        if (!isset($_POST['webarx_ip'])) {
            return;
        }

        global $wpdb;
        $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . "webarx_firewall_log SET apply_ban = 0 WHERE ip = %s", array($_POST['webarx_ip'])));
        die(json_encode(array('success' => 'The IP has been unbanned.')));
    }

    /**
     * Handle the incoming request.
     * 
     * @return void
     */
    public function handleRequest()
    {
        header('Content-Type: application/json');

        // Loop through all possible actions.
        foreach (array(
            'webarx_remote_users' => 'listUsers',
            'webarx_delete_user' => 'deleteUser',
            'webarx_edit_user' => 'updatePassword',
            'webarx_add_user' => 'createUser',
            'webarx_firewall_switch' => 'switchFirewallStatus',
            'webarx_wordpress_upgrade' => 'wordpressCoreUpgrade',
            'webarx_theme_upgrade' => 'themeUpgrade',
            'webarx_plugins_upgrade' => 'upgradeWebARX',
            'webarx_plugins_upgrade' => 'pluginsUpgrade',
            'webarx_plugins_toggle' => 'pluginsToggle',
            'webarx_plugins_delete' => 'pluginsDelete',
            'webarx_get_options' => 'getAvailableOptions',
            'webarx_set_options' => 'saveOptions',
            'webarx_refresh_rules' => 'refreshRules',
            'webarx_get_firewall_bans' => 'getFirewallBans',
            'webarx_firewall_unban_ip' => 'unbanFirewallIp'
        ) as $key => $action) {
            // Special case for WebARX plugin upgrade.
            if ($action == 'upgradeWebARX' && isset($_POST['webarx_plugins_upgrade']) && $_POST['webarx_plugins_upgrade'] == 'webarx') {
                $this->$action();
            } elseif ($action != 'upgradeWebARX' && isset($_POST[$key])) {
                $this->$action();
            }
        }
    }

    /**
     * Check if incoming token is valid.
     *
     * @param $token
     * @return bool
     */

    private function authenticated($token)
    {
        $date = new \DateTime();
        $date->modify('-120 seconds');
        $id = get_option('webarx_clientid');
        $key = get_option('webarx_secretkey');

        // Timeout of 2 minutes.
        for ($clientTimestamp = $date->getTimestamp(), $x = 0; $x <=120; $clientTimestamp = $date->modify('+1 seconds')->getTimestamp()) {
            $verifyToken = password_verify($id . $key . $clientTimestamp, $token);
            if ($verifyToken) {
                return true;
            }
            $x++;
        }

        return false;
    }

    /**
     * Determine if given action succeded or not, then return the appropriate message.
     * 
     * @return void
     */
    public function returnResults($thing, $successMessage, $failMessage)
    {
        if (!is_wp_error($thing)) {
            echo json_encode(array('success' => $successMessage));
        } else {
            echo json_encode(array('error' => $failMessage));
        }
    }

    /**
     * Determine if the provided secret hash equals the sha1 of the private id and key.
     *
     * @param string $providedSecret Hash that is sent from our API.
     * @return boolean
     */
    public function isAuthorizedOld($providedSecret)
    {
        $id = get_option('webarx_clientid');
        $key = get_option('webarx_secretkey');
        return $providedSecret === sha1($id . $key);
    }
}