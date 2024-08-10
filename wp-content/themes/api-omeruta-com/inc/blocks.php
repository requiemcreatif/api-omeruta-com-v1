<?php
function register_acf_block_types()
{

    // example block
	/*acf_register_block_type(array(
		'name'              => 'FAQ',
		'title'             => 'FAQ',
		'description'       => 'FAQ block',
		'category'          => 'headless-blocks',
		'icon'              => 'admin-comments',
		'mode'              => 'edit',
		'keywords'          => array('FAQ', 'question'),
	));*/

}


function cb_block_category($categories, $post)
{
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'headless-blocks',
				'title' => __('Omeruta Custom Blocks', 'headless-blocks'),
				'icon' => 'layout'
			),
		)
	);
}

if (function_exists('cb_block_category')) {
	add_filter('block_categories', 'cb_block_category', 10, 2);
}


// Check if function exists and hook into setup.
if (function_exists('acf_register_block_type')) {
	add_action('acf/init', 'register_acf_block_types');
}

function my_custom_block_register_block()
{
	// Register JavasScript File build/index.js
	wp_register_script(
		'my-custom-block',
		get_stylesheet_directory_uri() . '/build/index.js',
		array('wp-blocks', 'wp-element', 'wp-editor'),
		filemtime(get_template_directory() . '/build/index.js')
	);

	// Register editor style src/editor.css
	wp_register_style(
		'my-custom-block-editor-style',
		get_stylesheet_directory_uri() . '/src/editor.css',
		array('wp-edit-blocks'),
		filemtime(get_template_directory() . '/src/editor.css')
	);

	// Register your block
	register_block_type('myguten-block/test-block', array(
		'editor_script' => 'my-custom-block',
		'editor_style' => 'my-custom-block-editor-style',
		'style' => 'my-custom-block-frontend-style',
	));
}
//add_action( 'init', 'my_custom_block_register_block' );
