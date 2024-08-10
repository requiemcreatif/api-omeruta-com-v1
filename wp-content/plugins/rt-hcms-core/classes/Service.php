<?php
if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * A Service class containing general helper functions that can be used inside the headless theme:
 *  - Filtering functions, which are about to mutate some incoming data
 *  - Helper functions which are about to represent contents response in a correct manner
 *  - Single item data collectors
 **/

if (!class_exists('RT_HCMS_Service')) {
	class RT_HCMS_Service
	{
		/* FILTERING FUNCTIONS start ---------------------------------------------------------------------------*/
		public static function filter_link($link)
		{
			$domain_prefix_backend = get_option('domain_prefix_backend') ?: '';
			$domain_prefix_frontend = get_option('domain_prefix_frontend') ?: '';
			$domain_name = get_option('domain_name') ?: '';

			$link = str_replace('http://' . $domain_prefix_backend . $domain_name, '', $link);
			$link = str_replace('https://' . $domain_prefix_backend . $domain_name, '', $link);
			$link = str_replace('https://.' . $domain_prefix_frontend . $domain_name, '', $link);

			return str_replace(get_site_url(), '', $link);
		}

		public static function filter_image_data($image, $concise = false)
		{
			if (is_numeric($image)) {
				$image_src = wp_get_attachment_image_src($image, 'full');
				if ($image_src) {
					$new_image = array(
						'url' => self::filter_link($image_src[0]),
						'alt' => get_post_meta($image, '_wp_attachment_image_alt', true),
					);
					if (!$concise) {
						$new_image['width'] = $image_src[1];
						$new_image['height'] = $image_src[2];
					}
				}
			} else {
				$keys_to_copy = $concise
					? [
						'url',
						'alt',
					] : [
						'url',
						'alt',
						'width',
						'height',
					];

				foreach ($keys_to_copy as $var) {
					$new_image[$var] = $image[$var];
				}
				$new_image['url'] = self::filter_link($new_image['url']);
			}

			return $new_image;
		}

		public static function filter_post_type_value($post_id)
		{
			$archives = get_field('breadcrumbs_archives', 'option');
			if ($archives && count($archives)) {
				foreach ($archives as $archive) {
					if ($archive['post_type']['is_archive'] && $archive['post_type']['page'] == $post_id) {
						return 'archive_' . $archive['post_type']['post_type_slug'];
					}
				}
			}

			if (get_field('html_sitemap_page', 'option') == $post_id) {
				return 'html_sitemap';
			}

			return get_post_type($post_id);
		}

		public static function modify_outlink_url($slug, $is_terms = false)
		{
			$out_prefix = get_option('outlink_prefix');
			$tc_prefix = get_option('tc_outlink_prefix');
			$prefix = $is_terms ? $tc_prefix : $out_prefix;
			return $slug ? '/' . $prefix . '/' . $slug : null;
		}

		public static function filter_footer_data($data)
		{
			$data['imprint'] = self::filter_link($data['imprint']);
			$data['link_columns'] = array_map(function ($x) {
				$x['links'] = array_map(function ($k) {
					return $k['url'];
				}, $x['links']);
				return $x;
			}, $data['link_columns']);
			$footer_data_upd = apply_filters('rt_hcms_footer_data_filter', $data);
			return $footer_data_upd;
		}
		/* FILTERING FUNCTIONS end -----------------------------------------------------------------------------*/

		/* POST DATA COLLECTORS start --------------------------------------------------------------------------*/
		public static function get_post_basic_data($post_id, $extended_data = [])
		{
			$ret_value = array(
				'ID' => $post_id,
				'post_title' => html_entity_decode(get_the_title($post_id)),
				'permalink' => self::filter_link(get_the_permalink($post_id)),
			);

			// $extended_data is an array of entitites to collect in addition to the most basic data
			if ($extended_data && count($extended_data)) {
				foreach ($extended_data as $data) {
					switch ($data) {
						case 'banner':
							if (get_field('logo', $post_id) || get_field('banner', $post_id)) {
								$ret_value['banner'] = get_field('logo', $post_id)
									? self::filter_image_data(get_field('logo', $post_id))
									: self::filter_image_data(get_field('banner', $post_id));
							}
							break;

						case 'featured_image':
							$ret_value['featured_image'] = self::get_featured_image($post_id);
							break;

						case 'post_date':
							$date_format = get_option('date_format');
							$ret_value['post_date'] = get_the_date($date_format, $post_id) . ' ' . (get_post_time('H:i:s', $post_id) ? get_post_time('H:i:s', $post_id) : '00:00:00');
							break;

						case 'post_modified':
							$date_format = get_option('date_format');
							$ret_value['post_modified'] = get_the_modified_date($date_format, $post_id) . ' ' . get_the_modified_time('H:i:s', $post_id);
							break;

						case 'post_excerpt':
							$ret_value['post_excerpt'] = html_entity_decode(get_the_excerpt($post_id));
							break;

						default:
							break;
					}
				}
			}

			return $ret_value;
		}

		public static function get_post_filled_data($post_id, $extended_data = [], $fields = [])
		{
			// first, collect the basic data
			$post_data = self::get_post_basic_data($post_id, $extended_data);
			// second, collect the data about the fields
			$post_acf = RT_HCMS_Contents::get_acf_fields($post_id, $fields) ?: [];

			return array_merge($post_data, $post_acf);
		}
		/* POST DATA COLLECTORS end ----------------------------------------------------------------------------*/

		/* SINGLE ITEM COLLECTORS start ------------------------------------------------------------------------*/
		public static function get_single_item($post_id, $custom_name = null)
		{
			return array(
				'id' => $post_id,
				'title' => $custom_name ?: html_entity_decode(get_the_title($post_id)),
				'link' => self::filter_link(get_the_permalink($post_id))
			);
		}
		/* SINGLE ITEM COLLECTORS end --------------------------------------------------------------------------*/

		/* DATA COLLECTORS start -------------------------------------------------------------------------------*/
		public static function get_featured_image($post_id, $size = 'full')
		{
			$thumbnail_id = get_post_thumbnail_id($post_id) ?: get_field('banner', $post_id)['ID'];

			return $thumbnail_id
				? self::filter_image_data(array(
					'url' => wp_get_attachment_image_url($thumbnail_id, $size),
					'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
				), true)
				: null;
		}

		public static function outlink_sql($out_slug, $post_types, $meta_keys)
		{
			global $wpdb;
			$query = $wpdb->prepare("SELECT post_id FROM wp_postmeta
				RIGHT JOIN wp_posts
				ON wp_postmeta.post_id = wp_posts.ID
				WHERE (wp_posts.post_type IN ('" . implode("','", $post_types) . "'))
				AND (meta_key IN ('" . implode("','", $meta_keys) . "'))
				AND wp_posts.post_status = 'publish'
				AND meta_value = %s;
			", $out_slug);
			$results = $wpdb->get_results($query);

			return $results;
		}

		public static function get_outlink_data($slug, $is_terms = false)
		{
			$post_types = $is_terms ? get_option('tc_outlink_post_types') : get_option('outlink_post_types');
			if (!$post_types || count($post_types) == 0) {
				return $is_terms
					? new WP_Error('no_tc_outlink_post_types', 'No TC outlink post types found. Please input at least one into the plugin field', array('status' => 404))
					: new WP_Error('no_outlink_post_types', 'No outlink post types found. Please input at least one into the plugin field', array('status' => 404));
			}

			$meta_keys = $is_terms ? get_option('tc_outlink_meta_keys') : get_option('outlink_meta_keys');
			if (!$meta_keys || count($meta_keys) == 0) {
				return $is_terms
					? new WP_Error('no_tc_meta_keys', 'No TC meta keys found. Please input at least one into the plugin field', array('status' => 404))
					: new WP_Error('no_outlink', 'No meta keys found. Please input at least one into the plugin field', array('status' => 404));
			}

			$results = self::outlink_sql($slug, $post_types, $meta_keys);
			$results_count = count($results);
			if ($results_count <= 0) {
				return $is_terms
					? new WP_Error('no_tc_link', 'No TC link Data Found', array('status' => 404))
					: new WP_Error('no_outlink', 'No Outlink Data Found', array('status' => 404));
			}

			$res['status'] = $results_count == 1 ? "OK" : "WARNING: Slug Conflict";
			if ($results_count > 1) {
				$res['id_list'] = array_map(function ($x) {
					return (int)$x->post_id;
				}, $results);

				$res['casino'] = null;

				return $res;
			}

			$casino_fields = $is_terms ? get_option('tc_outpage_casino_fields') : get_option('outpage_casino_fields');
			$res['casino'] = self::get_post_filled_data((int)$results[0]->post_id, ['banner'], $casino_fields);

			return $res;
		}

		public static function get_author_data($author_id)
		{
			$author_meta = array(
				'name' => get_the_author_meta('display_name', $author_id),
				'description' => get_the_author_meta('description', $author_id),
				'permalink' => '/author/' . get_field('custom_slug', 'user_' . $author_id) . '/',
				'avatar' => str_contains(get_avatar_url($author_id), 'gravatar.com')
					? ''
					: self::filter_link(get_avatar_url($author_id, ['size' => '200'])),
				'social_links' => array(
					'facebook' => get_user_meta($author_id)['facebook'][0],
					'instagram' => get_user_meta($author_id)['instagram'][0],
					'linkedin' => get_user_meta($author_id)['linkedin'][0],
					'twitter' => get_user_meta($author_id)['twitter'][0],
				),
			);

			$acf = get_fields('user_' . $author_id, $author_id);
			foreach ($acf as $field_name => $field_value) {
				$author_meta[$field_name] = $field_value;
			}

			$author_meta_upd = apply_filters('rt_hcms_author_data_filter', $author_meta, $author_id);
			return $author_meta_upd;
		}

		public static function get_sitemap_data($post_type, $detailed = false)
		{
			if ($post_type === 'author') {
				$sitemap_data = self::get_authors_for_sitemap($detailed);
			} else if ($post_type === 'categories') {
				$sitemap_data = self::get_categories_sitemap_data();
			} else {
				$all = get_posts([
					'posts_per_page' => -1,
					'post_type' => $post_type,
					'post_status' => 'publish',
					'orderby' => $detailed ? 'title' : '',
					'order' => $detailed ? 'ACS' : 'DESC',
					'fields' => 'ids',
				]);
				global $wpdb;
				$redirects = $wpdb->get_results("SELECT * FROM wp_redirection_items");
				$redirection_links = $redirects ? array_map(function ($x) {
					return '/' . urldecode(trim($x->url, '/'));
				}, $redirects) : [];

				$sitemap_data = array();
				foreach ($all as $post_id) {
					$url_to_search = '/' . urldecode(trim(str_replace(get_site_url(), '', get_permalink($post_id)), '/'));
					if (!array_search($url_to_search, $redirection_links)) {
						$xml_data_object['url'] = self::filter_link(get_permalink($post_id));
						if ($detailed) {
							$xml_data_object['id'] = $post_id;
							$xml_data_object['title'] = html_entity_decode(get_the_title($post_id));
						} else {
							$xml_data_object['lastmod'] = get_the_modified_date('Y-m-d\TH:i:s+00:00', $post_id);
						}
						$sitemap_data[] = $xml_data_object;
					}
				}
			}

			$sitemap_data_upd = apply_filters('rt_hcms_sitemap_data_filter', $sitemap_data);
			return $sitemap_data_upd;
		}

		public static function get_authors_for_sitemap($detailed = false)
		{
			$sitemap_data = [];
			$users = get_users();
			$all_post_types = get_option('all_post_types');
			foreach ($users as $user) {
				$custom_slug = get_field('custom_slug', 'user_' . $user->ID);
				if ($custom_slug) {
					$args = array(
						'author'         => $user->ID,
						'post_type'      => $all_post_types,
						'post_status'    => 'publish',
						'posts_per_page' => -1
					);

					if (count(get_posts($args))) {
						$sitemap_data[] = $detailed
							? array(
								'id' => $user->ID,
								'title' => get_the_author_meta('display_name', $user->ID),
								'url' => '/author/' . $custom_slug,
							)
							: array(
								'url' => '/author/' . $custom_slug,
								'lastmod' => date("Y-m-d\TH:i:s+00:00", strtotime($user->data->user_registered)),
							);
					}
				}
			}

			$sitemap_authors = apply_filters('rt_hcms_sitemap_author_filter', $sitemap_data);
			return $sitemap_authors;
		}

		public static function get_categories_sitemap_data()
		{
			$sitemap_data = array();

			$categories = get_categories([
				'parent' => 0
			]);

			foreach ($categories as $category) {
				$category_data = RT_HCMS_General::category_response($category);

				$priority = $category->slug === 'uutiset' ? 0.7 : 0.5;
				$url = "/$category->slug";
				$xml_data_object["url"] = $url;
				$lastmod = get_posts([
					'posts_per_page' => 1,
					'category' => $category->term_id,
					'post_status' => 'publish',
				]);

				$xml_data_object["lastmod"] = date("Y-m-d\TH:i:s+00:00", strtotime($lastmod[0]->post_modified_gmt));

				if (!$category_data['seo']['noindex']) {
					array_push($sitemap_data, $xml_data_object);
				}

				//child categories
				$child_sitemap = self::get_child_categories_sitemap_data($category->term_id, $url, $priority);
				if (is_array($child_sitemap) && count($child_sitemap)) {
					foreach ($child_sitemap as $child_sitemap_record) {
						array_push($sitemap_data, $child_sitemap_record);
					}
				}
			}

			$sitemap_categories = apply_filters('rt_hcms_sitemap_category_filter', $sitemap_data);
			return $sitemap_categories;
		}

		public static function get_child_categories_sitemap_data($parent_category_term_id, $url, $priority, $sitemap_data = array())
		{
			$child_categories = get_categories(array(
				'parent' => $parent_category_term_id,
				'hide_empty' => false,
			));

			if (!empty($child_categories)) {
				foreach ($child_categories as $child_category) {
					$category_data = RT_HCMS_General::category_response($child_category);

					$child_url = $url . "/$child_category->slug";
					$xml_data_object["url"] = $child_url;
					$lastmod = get_posts([
						'posts_per_page' => 1,
						'category' => $child_category->term_id,
						'post_status' => 'publish',
					]);
					$xml_data_object["lastmod"] = date("Y-m-d\TH:i:s+00:00", strtotime($lastmod[0]->post_modified_gmt));

					if (!$category_data['seo']['noindex']) {
						array_push($sitemap_data, $xml_data_object);
					}

					$child_sitemap = self::get_child_categories_sitemap_data($child_category->term_id, $child_url, $priority, $sitemap_data);
					foreach ($child_sitemap as $child_sitemap_record) {
						array_push($sitemap_data, $child_sitemap_record);
					}
					reset($child_sitemap);
				}

				return $sitemap_data;
			} else {
				return array();
			}
		}

		public static function get_category_chain_by_child_category(WP_Term $category, array &$category_id_2_category = null)
		{
			$category_chain = [];

			$categories = array($category);
			while ($category_parent_id = $category->parent) {
				if (isset($category_id_2_category) && isset($category_id_2_category[$category_parent_id])) {
					$category = $category_id_2_category[$category_parent_id];
				} else {
					$category = get_term($category_parent_id, 'category');
					if (isset($category_id_2_category)) {
						$category_id_2_category[$category_parent_id] = $category;
					}
				}
				$categories[] = $category;
			}
			$categories = array_reverse($categories);

			$slugs = [];
			foreach ($categories as $category) {
				$slugs[] = $category->slug;
				$category_chain[] = array(
					'id' => $category->term_id,
					'title' => html_entity_decode($category->name),
					'permalink' => implode('/', $slugs),
				);
			}

			$category_chain_upd = apply_filters('rt_hcms_category_chain_filter', $category_chain);
			return $category_chain_upd;
		}
		/* DATA COLLECTORS end ---------------------------------------------------------------------------------*/

		/* BUILDERS start --------------------------------------------------------------------------------------*/
		public static function build_menu(array &$menu_items, $parent_id = 0)
		{
			$branch = array();
			foreach ($menu_items as &$item) {
				if ($item->menu_item_parent == $parent_id) {
					$item_obj = [
						'id' => $item->ID,
						'title' => html_entity_decode($item->title),
						'post_type' => $item->object,
						'slug' => self::filter_link($item->url)
					];

					$children = self::build_menu($menu_items, $item->ID);
					if ($children) {
						$item_obj['children'] = $children;
					}

					$branch[] = $item_obj;
					unset($item);
				}
			}
			return $branch;
		}
		/* BUILDERS end ----------------------------------------------------------------------------------------*/
	}
}
