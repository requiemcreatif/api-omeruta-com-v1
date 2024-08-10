<?php
if (!defined('ABSPATH')) {
	die('-1');
}

/**
 * A Blocks class containing functions that can be used for custom blocks handlinng
 **/

if (!class_exists('RT_HCMS_Blocks')) {
	class RT_HCMS_Blocks
	{
		public static function map_block($block)
		{
			switch ($block['blockName']) {
				case 'core/columns':
				case 'core/column':
				case 'core/quote':
				case 'core/group':
				case 'core/list':
				case 'core/buttons':
					$block_data = self::headless_block(substr($block['blockName'], 5), $block['attrs']['className'], self::map_inner_block($block), $block['attrs']);
					return apply_filters('rt_hcms_containing_core_blocks_filter', $block_data);

				case 'core/button':
					return self::headless_block(substr($block['blockName'], 5), null, null, $block);

				case 'core/image':
					$attrs = $block['attrs'];

					$ret_value['align'] = $attrs['align'];
					$resolution = $attrs['sizeSlug'] ?: 'full';
					$image = wp_get_attachment_image_src($attrs['id'], $resolution);

					$ret_value['url'] = RT_HCMS_Service::filter_link($image[0]);
					$ret_value['alt'] = get_post_meta($attrs['id'], '_wp_attachment_image_alt', true);

					// image sizes
					if ($attrs['width'] || $attrs['height']) {
						$ret_value['width'] = $attrs['width'] ? intval($attrs['width']) : null;
						$ret_value['height'] = $attrs['height'] ? intval($attrs['height']) : null;
					} else {
						$ret_value['width'] = $image[1] ?: wp_get_attachment_metadata($attrs['id'])['width'];
						$ret_value['height'] = $image[2] ?: wp_get_attachment_metadata($attrs['id'])['height'];
					}

					$caption_matches = array();
					preg_match('/figcaption class=\"wp-element-caption\"([\s\S]+)\/figcaption/', $block['innerHTML'], $caption_matches);
					$ret_value['caption'] = substr($caption_matches[1], 1, -1);
					return self::headless_block('image', null, null, $ret_value, RT_HCMS_Service::filter_link($block['innerHTML']));

				case 'core/heading':
				case 'core/paragraph':
					if ($block['innerHTML'] === "\n\n") {
						return null; //skip if block content contains new line charcters only
					} else {
						// remove backend image links
						$block['innerHTML'] = RT_HCMS_Service::filter_link($block['innerHTML']);
						return self::headless_block('html', $block['attrs']['className'], null, null, $block['innerHTML']);
					}

				default:
					return apply_filters('rt_hcms_blocks_filter', $block);
			}
		}

		public static function map_inner_block($block)
		{
			$post_blocks = array();
			$count = 0;
			$total_inner_blocks = count($block['innerBlocks']);
			foreach ($block['innerBlocks'] as $inner_block) {
				//merge blocks to optimize DOM
				$map = self::map_block($inner_block);
				switch ($map->blockName) {
						// merge `html` block if previous block was an `html` block
					case "html": {
							if ($count > 0 && $post_blocks[$count - 1]->blockName === "html") {
								$post_blocks[$count - 1]->innerHTML .= $map->innerHTML;
								break;
							}
						}
					case "column": {
							$count++;
							$map->total_columns = $total_inner_blocks;
							$post_blocks[] = $map;
							break;
						}

					default: {
							$count++;
							$post_blocks[] = $map;
							break;
						}
				}
			}
			return $post_blocks;
		}

		public static function headless_block($block_name = 'default', $custom_css = '', $inner_blocks = null, $data = null, $inner_html = null)
		{
			$obj = new stdClass();
			$obj->blockName = $block_name;

			if ($custom_css) {
				$obj->blockCss = $custom_css;
			}

			if ($inner_html) {
				$obj->innerHTML .= RT_HCMS_Service::filter_link($inner_html);
			}

			if ($inner_blocks !== null) {
				$obj->innerBlocksCount = count($inner_blocks);
				$obj->innerBlocks = $inner_blocks;
			}

			if ($data) {
				$obj->data = $data;
			}

			$res = apply_filters('rt_hcms_blocks_data_filter', $obj);
			return $res;
		}

		public static function get_block_fields_acf($block)
		{
			acf_setup_meta($block['attrs']['data'], $block['attrs']['id'], true);
			$fields = get_fields();

			// Restore global context
			acf_reset_meta($block['attrs']['id']);

			return $fields;
		}
	}
}
