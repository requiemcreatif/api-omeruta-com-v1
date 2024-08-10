<?php
if (!defined('ABSPATH')) {
	die('-1');
}

function rt_hcms_regster_routes()
{
	register_rest_route('rest/v1', 'frontpage', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_frontpage',
	));

	register_rest_route('rest/v1', 'masterdata', array(
		'methods' => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_masterdata',
	));

	register_rest_route('rest/v1', 'out\/(?P<slug>[a-zA-Z0-9-]+)\/?(?P<pageID>[0-9]+)?', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_outlink'
	));

	register_rest_route('rest/v1', 'tc/(?P<slug>[a-zA-Z0-9-]+)\/?(?P<pageID>[0-9]+)?', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_tc_outlink'
	));

	register_rest_route('rest/v1', 'sitemap-index', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_sitemap_index',
	));

	register_rest_route('rest/v1', 'sitemap-single/(?P<slug>[_-a-zA-Z\s]+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_sitemap_single',
	));

	register_rest_route('rest/v1', 'redirects', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_redirections'
	));

	register_rest_route('rest/v1', 'author/(?P<slug>[_-a-zA-Z\s]+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RT_HCMS_REST::expose_author_info',
	));
};
add_action('rest_api_init', 'rt_hcms_regster_routes');
