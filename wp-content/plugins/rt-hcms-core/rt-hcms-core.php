<?php

/**
 * Plugin Name: RT HCMS Core
 * Plugin URI:
 * Description: A core setup for the HCMS backend
 * Version: 1.0.15
 * Author: RT
 **/

 // Basic plugin definitions
define('RTHCMS_PLG_NAME', 'rt_hcms_core');
define('RTHCMS_PLG_VERSION', '1.0.15');
define('RTHCMS_URL', plugins_url() . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__)));
define('RTHCMS_URI', plugin_dir_path(__FILE__));
define('RTHCMS_DIR', WP_PLUGIN_DIR . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__)));

// Plugin INIT
require_once RTHCMS_DIR . 'inc/init.php';
require_once RTHCMS_DIR . 'inc/admin.php';
require_once RTHCMS_DIR . 'inc/rest-api.php';

require_once RTHCMS_DIR . 'classes/General.php';
require_once RTHCMS_DIR . 'classes/REST.php';
require_once RTHCMS_DIR . 'classes/Contents.php';
require_once RTHCMS_DIR . 'classes/Service.php';
require_once RTHCMS_DIR . 'classes/Blocks.php';
