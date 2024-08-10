<?php
if (!defined('ABSPATH')) {
	die('-1');
}

if (!class_exists('RT_HCMS_General')) {
	class RT_HCMS_General
	{
		public static function general_response($post_id)
		{
			global $post;
			$post = get_post($post_id, OBJECT);

			// collect data about the post meta and fields
			$ret_obj = RT_HCMS_Contents::get_post_meta($post_id);

			$post_blocks = array();
			$count = 0;
			if (has_blocks($post->post_content)) {
				$blocks = parse_blocks($post->post_content);
				foreach ($blocks as $block) {
					// collect data about the blocks in the post
					$map = RT_HCMS_Blocks::map_block($block);
					if ($map) {
						//merge blocks to optimize DOM
						switch ($map->blockName) {
							case null:
								break;

								// merge `html` block if previous block was an `html` block
							case "html":
								if ($count > 0 && $post_blocks[$count - 1]->blockName === "html") {
									$post_blocks[$count - 1]->innerHTML .= RT_HCMS_Service::filter_link($map->innerHTML);
									break;
								}

							default:
								$count++;
								$post_blocks[] = $map;
								break;
						}
					}
				}
			}

			// if the post is too old and contains only the classic editor content,
			// pass this content as the 'html' block
			if ($count > 0) {
				$ret_obj['post_blocks'] = $post_blocks;
			} else {
				$ret_obj['post_blocks'] = [
					array(
						'blockName' => 'html',
						'innerHTML' => get_post_field('post_content', $post_id)
					)
				];
			}

			// collect the post SEO meta data
			$ret_obj['seo'] = RT_HCMS_Contents::get_seo_data($post_id);

			// collect the post breadcrumbs data
			$ret_obj['breadcrumbs'] = RT_HCMS_Contents::get_breadcrumbs_data($post_id);

			$ret_obj_upd = apply_filters('rt_hcms_general_response_filter', $ret_obj, $post_id);
			return $ret_obj_upd;
		}

		public static function category_response(WP_Term $category)
		{
			$resp = array(
				'id' => $category->term_id,
				'post_title' => html_entity_decode($category->name),
				'post_type' => 'category'
			);

			// Add description.
			$resp['description'] = wpautop(html_entity_decode($category->description));

			// Add breadcrumbs.
			$category_chain = RT_HCMS_Service::get_category_chain_by_child_category($category);
			$resp['breadcrumbs'] = RT_HCMS_Contents::get_breadcrumbs_data(null);
			$resp['seo'] = array(
				'core' => array(
					'title' => $resp['post_title'],
				),
				'noindex' => false
			);

			// Get current category URL.
			$cur_category_url = $category_chain[count($category_chain) - 1]['permalink'];
			$resp['permalink'] = $cur_category_url;

			// Add child categories.
			$child_categories = get_terms('category', array(
				'parent' => $category->term_id,
				'hide_empty' => false,
			));
			$cur_category_slug = $category->slug;
			$resp['child_categories'] = $child_categories ? array_map(function (WP_Term $category) use ($cur_category_slug) {
				return array(
					'name' => html_entity_decode($category->name),
					'link' => $cur_category_slug . '/' . $category->slug . '/',
				);
			}, $child_categories) : [];

			$category_data = apply_filters('rt_hcms_category_data_filter', $resp, $category);
			return $category_data;
		}
	}
}
