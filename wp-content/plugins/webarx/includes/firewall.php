<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class provides the firewall functionality.
 */
class W_Firewall extends W_Core
{
    /**
     * Array that will contain all the firewall rules.
     * @var array $rules
     */
    private $rules = array();

    /**
     * Whitelisted rules.
     * @var string $whitelist
     */
    private $whitelist = '';

    /**
     * Whitelisted rules.
     * @var string $whitelistOld
     */
    private $whitelistOld = '';

    /**
     * Determine if the request was whitelisted.
     * @var boolean $isWhitelisted
     */
    private $isWhitelisted = false;

    /**
     * Parse the firewall and whitelist rules and determine if it's valid.
     * Then set the types with all server/client variables and launch the processor.
     *
     * @param bool $fromMain Whether or not the firewall is loaded from the main script or not.
     * @param Webarx $core
     * @return void
     */
    public function __construct($fromMain = false, $core = null)
    {
        if (!$fromMain || !$core) {
            if ($core) {
                parent::__construct($core);
            }
            return;
        }
        
        parent::__construct($core);
        
        // Load the firewall rules.
        $this->rules = json_decode(get_option('webarx_firewall_rules', ''), true);
        if ($this->rules == '' || is_null($this->rules)) {
            return;
        }

        // Load the whitelist and custom whitelist.
        if (get_option('webarx_whitelist_rules', '') != '') {
            $this->whitelist = json_decode(str_replace('<?php exit; ?>', '', get_option('webarx_whitelist_rules', '')), true);
        }

        if (get_option('webarx_custom_whitelist_rules', '') != '') {
            $this->whitelistOld = str_replace('<?php exit; ?>', '', get_option('webarx_custom_whitelist_rules', ''));
        }

        // Check for whitelist.
        $this->isWhitelisted = $this->is_whitelisted();
        if (!$this->isWhitelisted) {
            $this->processor();
        }
    }

    /**
     * Check the custom whitelist rules defined in the backend of WordPress
     * and attempt to match it with the request.
     *
     * @return boolean
     */
    private function is_custom_whitelisted()
    {
        if (empty($this->whitelistOld)) {
            return false;
        }

        // Loop through all lines.
        $lines = explode("\n", $this->whitelistOld);
        foreach ($lines as $line) {
            $t = explode(':', $line);

            if (count($t) == 2) {
                $val = strtolower(trim($t[1]));
                switch (strtolower($t[0])) {
                    // IP address match.
                    case 'ip':
                        if ($this->get_ip() == $val) {
                            return true;
                        }
                        break;
                    // Payload match.
                    case 'payload':
                        if (count($_POST) > 0 && strpos(strtolower(print_r($_POST, true)), $val) !== false) {
                            return true;
                        }

                        if (count($_GET) > 0 && strpos(strtolower(print_r($_GET, true)), $val) !== false) {
                            return true;
                        }
                        break;
                    // URL match.
                    case 'url':
                        if (strpos(strtolower($_SERVER['REQUEST_URI']), $val) !== false) {
                            return true;
                        }
                        break;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the request should be whitelisted.
     *
     * @return boolean
     */
    public function is_whitelisted()
    {
        // First check if the user has custom whitelist rules configured.
        if ($this->is_custom_whitelisted()) {
            return true;
        }

        // Pull whitelists
        $whitelists = $this->whitelist;
        if ($whitelists == null || $whitelists == '') {
            return false;
        }

        // Grab visitor's IP address and request data.
        $clientIP = $this->get_ip();
        $requests = $this->capture_request();
        $isWhitelisted = false;

        foreach ($whitelists as $whitelist) {
            $whitelistRule = json_decode($whitelist['rule']);
            $blockedRules = 0;

            // If matches on all request methods, only 1 rule match is required to block
            if ($whitelistRule->method === 'ALL') {
                $countRules = 1;
            } else {
                if (is_null($whitelistRule) === false) {
                    $countRules = $whitelistRule->rules;
                    $countRules = $this->count_rules($countRules);
                }
            }
            $ip = isset($whitelistRule->rules, $whitelistRule->rules->ip_address) ? $whitelistRule->rules->ip_address : null;

            if (is_null($ip) === false) {
                if (strpos($ip, '*') !== false) {
                    $whitelistedIp = $this->plugin->ban->check_wildcard_rule($clientIP, $ip);
                } elseif (strpos($ip, '-') !== false) {
                    $whitelistedIp = $this->plugin->ban->check_range_rule($clientIP, $ip);
                } elseif (strpos($ip, '/') !== false) {
                    $whitelistedIp = $this->plugin->ban->check_subnet_mask_rule($clientIP, $ip);
                } elseif ($clientIP == $ip) {
                    $whitelistedIp = true;
                } else {
                    $whitelistedIp = false;
                }
            } else {
                $whitelistedIp = true;
            }

            foreach ($requests as $key => $request) {
                if ($whitelistRule->method == $requests['method'] || $whitelistRule->method == 'ALL') {
                    $test = strtolower(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', '->$0', $key));
                    $rule = array_reduce(explode('->', $test), function ($o, $p) {
                        return $o->$p;
                    }, $whitelistRule);

                    if ($rule !== null && 'rule' == substr($key, 0, 4)) {
                        $is_matched = $this->is_rule_match($rule, $request);
                        if ($is_matched) {
                            $blockedRules++;
                        }
                    }
                }
            }

            if ($blockedRules >= $countRules && $whitelistedIp == true) {
                $isWhitelisted = true;
            }
        }

        return $isWhitelisted;
    }

    /**
     * Retrieve all HTTP headers that start with HTTP_.
     *
     * @return array
     */
    protected function get_headers()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }


    /**
     * Returns all request methods and parameters
     *
     * @return string
     */
    private function capture_request()
    {
        $data = $this->capture_keys();

        // Get the method and URL.
        $method = $_SERVER['REQUEST_METHOD'];
        $rulesUri = $_SERVER['REQUEST_URI'];

        // Store the header values in different formats.
        $rulesHeadersKeys = array();
        $rulesHeadersValues = array();
        $rulesHeadersCombinations = array();

        // Retrieve the headers.
        $headers = $this->get_headers();
        $rulesHeadersAll = implode(' ', $headers);
        foreach ($headers as $name => $value) {
            $rulesHeadersKeys[] = $name;
            $rulesHeadersValues[] = $value;
            $rulesHeadersCombinations[] = $name . ': ' . $value;
        }

        // Store the $_POST values in different formats.
        $rulesBodyKeys = array();
        $rulesBodyValues = array();
        $rulesBodyCombinations = array();

        // Retrieve the $_POST values.
        $rulesBodyAll = urldecode(http_build_query($data['POST']));
        foreach ($data['POST'] as $key => $value) {
            if (is_array($value)) {
                $value = @$this->multi_implode($value, ' ');
            }
            $rulesBodyKeys[] = $key;
            $rulesBodyValues[] = $value;
            $rulesBodyCombinations[] = $key . '=' . $value;
        }

        // Store the $_GET values in different formats.
        $rulesParamsKeys = array();
        $rulesParamsValues = array();
        $rulesParamsCombinations = array();

        // Retrieve the $_GET values.
        $rulesParamsAll =  urldecode(http_build_query($data['GET']));
        foreach ($data['GET'] as $key => $value) {
            if (is_array($value)) {
                $value = @$this->multi_implode($value, ' ');
            }
            $rulesParamsKeys[] = $key;
            $rulesParamsValues[] = $value;
            $rulesParamsCombinations[] = $key . '=' . $value;
        }

        // Return each value as its own array.
        return compact(
            'method',
            'rulesUri',
            'rulesHeadersAll',
            'rulesHeadersKeys',
            'rulesHeadersValues',
            'rulesHeadersCombinations',
            'rulesBodyAll',
            'rulesBodyKeys',
            'rulesBodyValues',
            'rulesBodyCombinations',
            'rulesParamsAll',
            'rulesParamsKeys',
            'rulesParamsValues',
            'rulesParamsCombinations'
        );
    }

    /**
     * Capture the keys of the request.
     * 
     * @return array
     */
    private function capture_keys()
    {
        // Data we want to go through.
        $data = array('POST' => $_POST, 'GET' => $_GET);
        $default = array('POST' => array(), 'GET' => array());

        // Determine if there are any keys we should remove from the data set.
        if (get_option('webarx_whitelist_keys_rules', '') != '') {
            $keys = json_decode(get_option('webarx_whitelist_keys_rules'), true);

            // Must be valid JSON and decodes to at least 2 primary data arrays.
            if ($keys && count($keys) >= 2) {
                
                // Remove the keys where necessary.
                // Go through all data types (GET, POST).
                foreach ($keys as $type => $entries) {

                    // Go through all whitelisted actions.
                    foreach ($entries as $entry) {
                        $t = explode('.', $entry);

                        // For non-multidimensional array checks.
                        if (count($t) == 1) {
                            // If the value itself exists.
                            if (isset($data[$type][$t[0]])) {
                                unset($data[$type][$t[0]]);
                            }

                            // For pattern checking.
                            if (strpos($t[0], '*') !== false) {
                                $star = explode('*', $t[0]);

                                // Loop through all $_POST, $_GET values.
                                foreach ($data as $method => $values) {
                                    foreach ($values as $key => $value) {
                                        if (!is_array($value) && strpos($key, $star[0]) !== false) {
                                            unset($data[$method][$key]);
                                        }
                                    }
                                }   
                            }
                            continue;
                        }

                        // For multidimensional array checks.
                        $end =& $data[$type];
                        $skip = false;
                        foreach($t as $var){
                            if (!isset($end[$var])) {
                                $skip = true;
                                break;
                            }
                            $end =& $end[$var];
                        }

                        // Since we cannot unset it due to it being a reference variable,
                        // we just set it to an empty string instead.
                        if (!$skip) {
                            $end = '';
                        }
                    }
                }
            }
        }

        // Special condition for vaultpress due to how their API communicates with the plugin.
        if (isset($_GET['vaultpress']) && $_SERVER['SCRIPT_NAME'] == 'wp-load.php') {
            return $default;
        }

        // Special condition for other plugins.
        $url = explode('?', $_SERVER['REQUEST_URI']);
        if (isset($url[0]) && strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== false && (strpos($url[0], 'wc-api/v') !== false || strpos($url[0], 'wp-json/storychief') !== false)) {
            return $default; 
        }

        return $data;
    }

    /**
     * Implode array recursively
     *
     * @param $array
     * @param $glue
     * @return bool|string
     */
    private function multi_implode($array, $glue)
    {
        $ret = '';

        foreach ($array as $item) {
            if (is_array($item)) {
                $ret .= $this->multi_implode($item, $glue) . $glue;
            } else {
                $ret .= $item . $glue;
            }
        }

        return substr($ret, 0, 0 - strlen($glue));
    }

    /**
     * Determine if the request matches the given firewall or whitelist rule.
     *
     * @param string $rule
     * @param string|array $request
     * @return bool
     */
    private function is_rule_match($rule, $request)
    {
        $is_matched = false;
        if (is_array($request)) {
            foreach ($request as $key => $value) {
                $is_matched = $this->is_rule_match($rule, $value);
                if ($is_matched) {
                    return $is_matched;
                }
            }
        } else {
            return preg_match($rule, urldecode($request));
        }

        return $is_matched;
    }

	/**
	 * Count the number of rules.
	 * 
	 * @param array $array
	 * @return integer
	 */
	private function count_rules($array)
    {
        $counter = 0;
        if (is_object($array)) {
            $array = (array) $array;
        }

        if ($array['uri']) {
            $counter++;
        }

        foreach (array('body', 'params', 'headers') as $type) {
            foreach ($array[$type] as $key => $value) {
                if (!is_null($value)) {
                    $counter++;
                }
            }
        }

        return $counter;
    }

	/**
	 * Runs the firewall rules processor.
	 *
	 * @return void
	 */
    private function processor()
    {
	    if ($this->is_auto_ip_blocked() > $this->get_option('webarx_autoblock_attempts', 10) && !$this->is_authenticated()) {
            $this->display_error_page(22);
        }

        $clientIP = $this->get_ip();
        $requests = $this->capture_request();

        // Iterate through all root objects.
        foreach ($this->rules as $rulesss) {
            $blockedRules = 0;
            $oldRule = $rulesss;
            $rulesss = json_decode($rulesss['rule']);

            $ip = isset($rulesss->rules->ip_address) ? $rulesss->rules->ip_address : null;

            if (is_null($ip) === false) {
                if (strpos($ip, '*') !== false) {
                    $matchedIp = $this->plugin->ban->check_wildcard_rule($clientIP, $ip);
                } elseif (strpos($ip, '-') !== false) {
                    $matchedIp = $this->plugin->ban->check_range_rule($clientIP, $ip);
                } elseif (strpos($ip, '/') !== false) {
                    $matchedIp = $this->plugin->ban->check_subnet_mask_rule($clientIP, $ip);
                } elseif ($clientIP == $ip) {
                    $matchedIp = true;
                } else {
                    $matchedIp = false;
                }
            } else {
                $matchedIp = true;
            }

            if ($matchedIp) {

                // If matches on all request methods, only 1 rule match is required to block
                if ($rulesss->method === 'ALL') {
                    $countRules = 1;
                } else {
                    $countRules = json_decode(json_encode($rulesss->rules), true);
                    $countRules = $this->count_rules($countRules);
                }

                foreach ($requests as $key => $request) {
                    $test = strtolower(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', '->$0', $key));

                    if ($rulesss->method == $requests['method'] || $rulesss->method == 'ALL' || $rulesss->method == 'GET' || ($rulesss->method == 'FILES' && $this->is_file_upload())) {
                        $exp = explode('->', $test);
                        $rule = array_reduce($exp, function ($o, $p) {
                            return $o->$p;
                        }, $rulesss);

                        if ($rule !== null && 'rule' == substr($key, 0, 4)) {
                            $is_matched = $this->is_rule_match($rule, $request);
                            if ($is_matched) {
                                $blockedRules++;
                            }
                        }
                    }
                }

                if ($blockedRules >= $countRules) {
                    if ($rulesss->type == 'BLOCK') {
                        $this->block_user($oldRule['id']);
                    } elseif ($rulesss->type == 'LOG') {
                        $this->log_user($oldRule['id']);
                    } elseif ($rulesss->type == 'REDIRECT') {
                        $this->redirect_user($oldRule['id'], $rulesss->type_params);
                    }
                }
            }
        }
    }

    /**
     * Determine if the current request is a file upload.
     *
     * @return boolean
     */
    private function is_file_upload()
    {
        return isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== true;
    }

    /**
     * Automatically block the user if there are many blocked requests in a short period of time.
     *
     * @return integer
     */
    public function is_auto_ip_blocked()
    {
        global $wpdb;
        $ip = $this->get_ip();
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT COUNT(*) as numIps FROM " . $wpdb->prefix . "webarx_firewall_log WHERE apply_ban = 1 AND ip = '%s' AND log_date >= (STR_TO_DATE(NOW(), '%%Y-%%m-%%d %%H:%%i:%%s') - INTERVAL %d MINUTE)", array($ip, ($this->get_option('webarx_autoblock_minutes', 30) + $this->get_option('webarx_autoblock_blocktime', 60)))), OBJECT
        );

        if (!isset($results, $results[0], $results[0]->numIps)) {
            return 0;
        }
        return $results[0]->numIps;
    }

    /**
     * Block the user, and log, do whatever is necessary.
     *
     * @param string $rule
     * @return void
     */
    private function block_user($rule)
    {
        if (!$this->is_authenticated()) {
            $this->display_error_page('55' . intval($rule));
        }
    }

    /**
     * Log the user action.
     *
     * @param string $rule
     * @return void
     */
    private function log_user($rule)
    {
        $this->log_hacker($rule, array(), 'LOG');
    }

    /**
     * Log the user action and redirect.
     *
     * @param integer $ruleId
     * @param array $type_params
     * @return void
     */
    private function redirect_user($ruleId, $type_params)
    {
        $this->log_hacker($ruleId, array(), 'REDIRECT', $type_params);
        ob_start();
        header('Location: ' . $type_params);
        ob_end_flush();
        exit;
    }

    /**
     * Determine if the user is authenticated and in the list of whitelisted roles.
     *
     * @return bool
     */
    private function is_authenticated()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        // User is logged in, determine the role.
        $user = wp_get_current_user();
        if (!isset($user->roles) || count((array) $user->roles) == 0) {
            return false;
        }

        // Is the user in the whitelist roles list?
        $roleCount = array_intersect($user->roles, $this->get_option('webarx_basic_firewall_roles', array('administrator', 'editor', 'author')));
        return count($roleCount) != 0;
    }

    /**
     * Log the blocked request.
     *
     * @param integer $fid firewall
     * @param array $query_vars
     * @param string $block_type
     * @param array $block_params
     * @return void
     */
    private function log_hacker($fid = 1, $query_vars = array(), $block_type = 'BLOCK', $block_params = '')
    {
        global $wpdb;
        if (!$wpdb) {
            return;
        }

        // Insert into the logs.
        if ($fid != 22) {
            $wpdb->insert(
                $wpdb->prefix . 'webarx_firewall_log',
                array(
                    'ip' => $this->get_ip(),
                    'request_uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                    'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                    'protocol' => isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '',
                    'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
                    'query_string' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
                    'query_vars' => implode(', ', $query_vars),
                    'fid' => $fid,
                    'flag' => '-',
                    'post_data' => $this->get_post_data(),
                    'block_type' => $block_type,
                    'block_params' => $block_params
                )
            );
        }
    }

    /**
     * Get POST data.
     *
     * @return string|NULL
     */
    private function get_post_data()
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            return null;
        }

        return json_encode($_POST);
    }

    /**
     * Display error page.
     *
     * @param integer $fid
     * @return void
     */
    public function display_error_page($fid = 1)
    {
        if ($fid != 22) {
            $this->log_hacker($fid);
        }

        http_response_code(403);
        require_once dirname(__FILE__) . '/views/access-denied.php';
        exit;
    }
}
