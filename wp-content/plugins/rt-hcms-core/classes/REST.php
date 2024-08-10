<?php
if (!defined('ABSPATH')) {
	die('-1');
}

if (!class_exists('RT_HCMS_REST')) {
	class RT_HCMS_REST
	{
		public static function expose_frontpage()
		{
			$frontpage_id = get_option('page_on_front');
			wp_send_json(RT_HCMS_General::general_response($frontpage_id));
		}

		public static function expose_masterdata()
		{
			$response = array(
				'menus' => array(
					'main' => array_map(function ($item) {
						$item['menu_item']['page'] = $item['menu_item']['page']
							? RT_HCMS_Service::filter_link($item['menu_item']['page'])
							: '#';
						return $item['menu_item'];
					}, (array)get_field('menu_bar', 'option')),
					'sidebar' => get_nav_menu_locations()['side-menu']
						? RT_HCMS_Service::build_menu(wp_get_nav_menu_items(get_nav_menu_locations()['side-menu']))
						: [],
				),
				'footer' => RT_HCMS_Service::filter_footer_data(get_field('footer_settings', 'option')),
			);
			$masterdata = apply_filters('rt_hcms_masterdata_filter', $response);
			wp_send_json($masterdata);
		}

		public static function expose_outlink(WP_REST_Request $request)
		{
			$slug = $request->get_param('slug');
			$slug_upd = apply_filters('rt_hcms_outlink_slug_filter', $slug);
			$page_id = $request->get_param('pageID');

			$res = RT_HCMS_Service::get_outlink_data($slug_upd, false);

			$custom_affiliate_field_name = get_option('custom_affiliate_field_name') ?: '';
			$affiliate_field_name = get_option('affiliate_field_name') ?: '';
			if ($page_id) {
				foreach ($res['casino'][$custom_affiliate_field_name] as $value) {
					if ($page_id === $value['page_id']) {
						$res['casino'][$affiliate_field_name] = $value['custom_affiliate_link'];
					}
				}
			}
			if (!is_wp_error($res)) {
				if ($res['casino'][$custom_affiliate_field_name]) {
					unset($res['casino'][$custom_affiliate_field_name]);
				}
			}
			$res = apply_filters('rt_hcms_outdata_result_filter', $res, $slug);

			return $res;
		}

		public static function expose_tc_outlink(WP_REST_Request $request)
		{
			$slug = $request->get_param('slug');
			$slug_upd = apply_filters('rt_hcms_outlink_slug_filter', $slug);
			$page_id = $request->get_param('pageID');

			$res = RT_HCMS_Service::get_outlink_data($slug_upd, true);

			$custom_affiliate_field_name = get_option('custom_tc_affiliate_field_name');
			if ($page_id) {
				$affiliate_field_name = get_option('tc_affiliate_field_name');
				foreach ($res['casino'][$custom_affiliate_field_name] as $value) {
					if ($page_id === $value['page_id']) {
						$res['casino'][$affiliate_field_name] = $value['terms_and_conditions_external_custom'];
					}
				}
			}
			if (!is_wp_error($res)) {
				if ($res['casino'][$custom_affiliate_field_name]) {
					unset($res['casino'][$custom_affiliate_field_name]);
				}
			}
			$res = apply_filters('rt_hcms_tc_outdata_result_filter', $res, $slug);

			return $res;
		}

		public static function expose_sitemap_index()
		{
			$all_post_types = get_option('all_post_types');
			$post_types = [];
			foreach ($all_post_types as $pt) {
				$x['slug'] = $pt;
				$lastmod = get_posts([
					'posts_per_page' => 1,
					'post_type' => $pt,
					'post_status' => 'publish',
					'orderby' => 'modified',
					'order' => 'DESC'
				]);
				$x['lastmod'] = date("Y-m-d\TH:i:s+00:00", strtotime($lastmod[0]->post_modified_gmt));
				$post_types[] = $x;
			}

			$include_authors = get_option('include_authors_in_sitemap');
			if (boolval($include_authors)) {
				$authors = array_map(function ($x) {
					return $x['lastmod'];
				}, RT_HCMS_Service::get_authors_for_sitemap());
				$latest_author = date("Y-m-d\TH:i:s+00:00", max(array_map('strtotime', $authors)));
				$post_types[] = array(
					'slug' => 'author',
					'lastmod' => $latest_author,
				);
			}

			$include_categories = get_option('include_categories_in_sitemap');
			if (boolval($include_categories)) {
				$categories['slug'] = 'categories';
				$lastmod_cat = get_posts([
					'posts_per_page' => 1,
					'post_type' => ['post', 'casino_game', 'betting_article'],
					'post_status' => 'publish',
				]);
				$categories['lastmod'] = date("Y-m-d\TH:i:s+00:00", strtotime($lastmod_cat[0]->post_modified_gmt));
				$post_types[] = $categories;
			}

			$sitemap_index_data = apply_filters('rt_hcms_sitemap_index_filter', $post_types);
			return $sitemap_index_data;
		}

		public static function expose_sitemap_single(WP_REST_Request $request)
		{
			$post_type = $request->get_param('slug');
			return RT_HCMS_Service::get_sitemap_data($post_type);
		}

		public static function expose_redirections()
		{
			global $wpdb;
			$results = $wpdb->get_results("SELECT * FROM wp_redirection_items WHERE status = 'enabled' AND action_code = 301");
			$redirections = [];
			foreach ($results as $item) {
				$url = $item->url;
				$redirections[$url] = $item->action_data;
			}
			return $redirections;
		}

		public static function expose_author_info(WP_REST_Request $request)
		{
			$slug = $request->get_param('slug');
			$users = get_users([
				'meta_key'     => 'custom_slug',
				'meta_value'   => $slug,
			]);
			if (count($users) > 0) {
				$author_id = $users[0]->ID;
				$all_post_types = get_option('all_post_types');

				$args = array(
					'author'         => $author_id,
					'post_type'      => $all_post_types,
					'orderby'        => 'post_date',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'posts_per_page' => 9
				);
				$current_user_posts = array_map(function ($p) {
					return RT_HCMS_Service::get_post_basic_data($p->ID, ['post_modified', 'featured_image']);
				}, get_posts($args));

				if (count($current_user_posts)) {
					$author = RT_HCMS_Service::get_author_data($author_id);

					$author['seo'] = array(
						'core' => array(
							'title' => get_user_meta($author_id)['wpseo_title'][0]
								? get_user_meta($author_id)['wpseo_title'][0]
								: get_the_author_meta('display_name', $author_id),
							'description' => get_user_meta($author_id)['wpseo_metadesc'][0]
								? get_user_meta($author_id)['wpseo_metadesc'][0]
								: get_the_author_meta('description', $author_id),
							'canonical' => '/author/' . get_field('custom_slug', 'user_' . $author_id),
						),
						'og' => array(
							'url' => '/author/' . get_field('custom_slug', 'user_' . $author_id),
							'type' => 'article',
							'sitename' => get_option('seo_sitename'),
						),
					);
					$author['posts'] = $current_user_posts;

					$author_data = apply_filters('rt_hcms_author_endpoint_filter', $author, $author_id);
					return $author_data;
				} else {
					return new WP_Error('no_posts', 'No Posts Found for the User', array('status' => 404));
				}
			}

			return new WP_Error('no_user', 'No User Found', array('status' => 404));
		}
	}
}
