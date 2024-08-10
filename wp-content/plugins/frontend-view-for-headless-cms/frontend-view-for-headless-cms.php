<?php
/**
 * Frontend View For Headless CMS
 *
 * Links up backend WordPress articles, pages, custom post types, taxonomies, and categories to the headless CMS site.
 *
 * Plugin Name: Frontend View For Headless CMS
 * Plugin URI: https://wordpress.org/plugins/frontend-view-for-headless-cms/
 * Description: This plugin links up backend WordPress articles, pages, custom post types, taxonomies, and categories to the headless CMS site.
 * Version: 1.1
 * Author: Dropndot Solutions
 * Author URI: https://www.dropndot.com/
 * License: GPLv2 or later
 * Text Domain: frontend-view-for-headless-cms
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package Frontend View For Headless CMS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the admin functions file.
require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';

// Include the  functions file.
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

/**
 * Enqueuing a JS Script
 *
 * @return void
 */
function fvhc_add_target_blank_enqueue_script() {
	wp_enqueue_script( 'add-target-blank-script', plugins_url( 'includes/js/add-target-blank.js', __FILE__ ), array( 'jquery' ), '1.1', true );
}

add_action( 'admin_enqueue_scripts', 'fvhc_add_target_blank_enqueue_script' );
