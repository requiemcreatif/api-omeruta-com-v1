<?php

// replace the Appearence dashboard menu with only Navigation
function rt_hcms_update_menus()
{
	remove_menu_page('edit-comments.php');
	remove_menu_page('themes.php'); //Appearance
	add_menu_page(__('Menus', 'nav-menus'), __('Navigation', 'nav-menus'), 'edit_themes', 'nav-menus.php', '', '
	dashicons-menu', 9.1);
}
add_filter('admin_init', 'rt_hcms_update_menus');

// register the menu, which will be multi-level and will be used as a side menu
register_nav_menu('side-menu', 'Side Menu');

function rt_hcms_add_acf_options()
{
	if (function_exists('acf_add_options_page')) {
		acf_add_options_page(array(
			'page_title' 	=> 'Site Options',
			'menu_title'	=> 'Site Options',
			'menu_slug' 	=> 'site-options',
			'redirect'		=> false
		));

		acf_add_options_sub_page(array(
			'page_title' 	=> 'Menu Settings',
			'menu_title'	=> 'Menu Settings',
			'parent_slug'	=> 'site-options',
		));

		acf_add_options_sub_page(array(
			'page_title' 	=> 'Footer Settings',
			'menu_title'	=> 'Footer Settings',
			'parent_slug'	=> 'site-options',
		));

		acf_add_options_sub_page(array(
			'page_title' 	=> 'Breadcrumbs / Archive Settings',
			'menu_title'	=> 'Breadcrumbs / Archive Settings',
			'parent_slug'	=> 'site-options',
		));
	}
}
add_action('init', 'rt_hcms_add_acf_options');
