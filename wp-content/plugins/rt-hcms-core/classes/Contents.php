<?php
if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * A Contents class containing functions that can be used for generating content particles response
 **/

if (!class_exists('RT_HCMS_Contents')) {
	class RT_HCMS_Contents
	{
		public static function get_post_meta($post_id, $custom_fields = null)
		{
			$date_format = get_option('date_format');
			$post_data = array(
				'ID' => $post_id,
				'post_date' => get_the_date($date_format, $post_id) . ' ' . (get_post_time('H:i:s', $post_id) ? get_post_time('H:i:s', $post_id) : '00:00:00'),
				'post_modified' => get_the_modified_date($date_format, $post_id) . ' ' . get_the_modified_time('H:i:s', $post_id),
				'post_title' => html_entity_decode(get_the_title($post_id)),
				'post_type' => RT_HCMS_Service::filter_post_type_value($post_id),
				'permalink' => RT_HCMS_Service::filter_link(get_the_permalink($post_id)),
			);

			// Get all custom fields data
			$acf_data = self::get_acf_fields($post_id, $custom_fields) ?: [];

			return array_merge($post_data, $acf_data);
		}

		public static function get_acf_fields($post_id, $custom_fields)
		{
			$response = [];
			if ($custom_fields) {
				foreach ($custom_fields as $cf) {
					$response[$cf] = get_field($cf, $post_id);
				}
			} else if (gettype($custom_fields) === 'array' && count($custom_fields) === 0) {
				return;
			} else {
				$response = get_fields($post_id);

				// A backu for the case ACF fields are not caught up
				if (empty($response)) {
					$meta_fields = get_post_meta($post_id);
					foreach ($meta_fields as $key => $val) {
						if (!str_starts_with($key, '_')) {
							$response[$key] = get_post_meta($post_id, $key, true);
						}
					}
				}
			}
			$response_upd = apply_filters('rt_hcms_custom_fields_response_filter', $response, $post_id, $custom_fields);

			$ret_obj = [];

			foreach ($response_upd as $field_name => $field_value) {
				$val = apply_filters('rt_hcms_custom_field_filter', $field_value, $field_name, $post_id);

				// Unset any null values
				if ($val === null) {
					unset($ret_obj[$field_name]);
				} else {
					$ret_obj[$field_name] = $val;
				}
			}

			return $ret_obj;
		}

		public static function get_seo_data($post_id)
		{
			$yoast_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
			$yoast_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
			$author = get_post_field('post_author', $post_id);
			$og_image_id = get_post_meta($post_id, '_yoast_wpseo_opengraph-image-id', true);
			$twitter_image_id = get_post_meta($post_id, '_yoast_wpseo_twitter-image-id', true);

			$seo_data = array(
				'core' => array(
					'title' => $yoast_title ?: html_entity_decode(get_the_title($post_id)),
					'description' => $yoast_description ?: html_entity_decode(get_the_excerpt($post_id)),
					'keyword' => get_post_meta($post_id, '_yoast_wpseo_focuskw', true),
					'published_time' => get_the_date('Y-m-d', $post_id) . ' ' . (get_post_time('H:i:s', $post_id) ? get_post_time('H:i:s', $post_id) : '00:00:00'),
					'modified_time' => get_the_modified_date('Y-m-d', $post_id) . ' ' . get_the_modified_time('H:i:s', $post_id),
				),
				'og' => array(
					'url' => RT_HCMS_Service::filter_link(get_the_permalink($post_id)),
					'type' => "article",
					'sitename' => get_option('seo_sitename'),
					'images' => $og_image_id ? [RT_HCMS_Service::filter_image_data($og_image_id)] : [],
					'authors' => $author ? [get_the_author_meta('display_name', get_post_field('post_author', $post_id))] : [],
				),
				'twitter' => array(
					'card' => 'summary',
					'images' => $twitter_image_id ? [RT_HCMS_Service::filter_image_data($twitter_image_id)] : null,
				)
			);

			$seo_upd = apply_filters('rt_hcms_seo_filter', $seo_data, $post_id);
			return $seo_upd;
		}

		public static function get_breadcrumbs_data($post_id)
		{
			$breadcrumbs_data = [];

			// add homepage data for the first crumb
			$home_crumb['id'] = get_option('page_on_front');
			$home_crumb['title'] = get_option('breadcrumbs_homepage_name');
			$home_crumb['link'] = '/';
			$breadcrumbs_data[] = $home_crumb;

			// add the post type top pages
			$post_type = get_post_type($post_id);
			$archives = get_field('breadcrumbs_archives', 'option');
			if ($archives && count($archives)) {
				$archived_post_types = array_map(function ($x) {
					return $x['post_type']['post_type_slug'];
				}, $archives);
				if (in_array($post_type, $archived_post_types)) {
					foreach ($archives as $archive_page) {
						if ($archive_page['post_type']['post_type_slug'] === $post_type) {
							$breadcrumbs_data[] = RT_HCMS_Service::get_single_item($archive_page['post_type']['page'], $archive_page['post_type']['name']);
						}
					}
				}
			}

			// get ancestors array
			$anc = array_reverse((array) get_post_ancestors($post_id));
			foreach ($anc as $apost) {
				$breadcrumbs_data[] = RT_HCMS_Service::get_single_item($apost);
			}

			//add current page
			if ($post_id) {
				$breadcrumbs_data[] = RT_HCMS_Service::get_single_item($post_id);
			}

			$breadcrumbs_upd = apply_filters('rt_hcms_breadcrumbs_filter', $breadcrumbs_data, $post_id);
			return $breadcrumbs_upd;
		}
	}
}
