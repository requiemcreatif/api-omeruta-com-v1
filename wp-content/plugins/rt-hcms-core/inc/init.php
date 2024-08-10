<?php
if (!defined('ABSPATH')) {
	die('-1');
}

add_action('admin_enqueue_scripts', 'rt_hcms_core_enqueue_script');
function rt_hcms_core_enqueue_script()
{
	$screen = get_current_screen();
	if ($screen->id === 'toplevel_page_rt_hcms_dashboard') {
		$js_ver  = date("ymd-Gis", filemtime(str_replace(['/var/www/html', '/opt/bitnami/wordpress'], '', RTHCMS_DIR) . 'assets/dashboard.css'));
		$css_ver = date("ymd-Gis", filemtime(str_replace(['/var/www/html', '/opt/bitnami/wordpress'], '', RTHCMS_DIR) . 'assets/dashboard.js'));
		wp_enqueue_style('rt_hcms_dashboard_style', str_replace(['/var/www/html', '/opt/bitnami/wordpress'], '', RTHCMS_DIR) . 'assets/dashboard.css', $css_ver);
		wp_enqueue_script('rt_hcms_dashboard_scripts', str_replace(['/var/www/html', '/opt/bitnami/wordpress'], '', RTHCMS_DIR) . 'assets/dashboard.js', array('jquery'), $js_ver, true);
	}
}

add_action('admin_menu', 'rt_hcms_core_create_menu_item');
function rt_hcms_core_create_menu_item()
{
	add_menu_page(
		'RT HCMS Core Dashboard',
		'RT HCMS Core',
		'manage_options',
		'rt_hcms_dashboard',
		'rt_hcms_create_dashboard',
		'dashicons-admin-tools'
	);
}

function rt_hcms_create_dashboard()
{
	require RTHCMS_DIR . 'templates/dashboard.php';
}

add_action('admin_init', 'rt_hcms_core_register_settings');
function rt_hcms_core_register_settings()
{
	register_setting('rt_hcms_settings_dashboard', 'domain_prefix_backend', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'domain_prefix_frontend', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'domain_name', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'frontend_url_base', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'seo_sitename', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'breadcrumbs_homepage_name', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'custom_date_format', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'include_authors_in_sitemap', array('type' => 'boolean', 'default' => false));
	register_setting('rt_hcms_settings_dashboard', 'include_categories_in_sitemap', array('type' => 'boolean', 'default' => false));

	register_setting('rt_hcms_settings_dashboard', 'all_post_types', array('type' => 'array', 'default' => []));

	register_setting('rt_hcms_settings_dashboard', 'outlink_prefix', array('type' => 'string', 'default' => 'go'));
	register_setting('rt_hcms_settings_dashboard', 'outlink_post_types', array('type' => 'array', 'default' => []));
	register_setting('rt_hcms_settings_dashboard', 'outlink_meta_keys', array('type' => 'array', 'default' => []));
	register_setting('rt_hcms_settings_dashboard', 'outpage_casino_fields', array('type' => 'array', 'default' => []));
	register_setting('rt_hcms_settings_dashboard', 'affiliate_field_name', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'custom_affiliate_field_name', array('type' => 'string', 'default' => ''));

	register_setting('rt_hcms_settings_dashboard', 'tc_outlink_prefix', array('type' => 'string', 'default' => 'tc'));
	register_setting('rt_hcms_settings_dashboard', 'tc_outlink_post_types', array('type' => 'array', 'default' => []));
	register_setting('rt_hcms_settings_dashboard', 'tc_outlink_meta_keys', array('type' => 'array', 'default' => []));
	register_setting('rt_hcms_settings_dashboard', 'tc_outpage_casino_fields', array('type' => 'array', 'default' => []));
	register_setting('rt_hcms_settings_dashboard', 'tc_affiliate_field_name', array('type' => 'string', 'default' => ''));
	register_setting('rt_hcms_settings_dashboard', 'custom_tc_affiliate_field_name', array('type' => 'string', 'default' => ''));
}

define('FRONTEND_URL_BASE', get_option('frontend_url_base'));
add_action('save_post', 'rt_hcms_core_revalidate_post', 20, 2);
add_action('update_option_permalink_structure', 'rt_hcms_core_revalidate_post', 20, 2);
function rt_hcms_core_revalidate_post($post_id)
{
	if (wp_is_post_revision($post_id)) {
		return;
	}
	$curl = curl_init(FRONTEND_URL_BASE . '/api/revalidate');

	curl_setopt_array($curl, [
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
	]);

	curl_exec($curl);
	curl_close($curl);
}
