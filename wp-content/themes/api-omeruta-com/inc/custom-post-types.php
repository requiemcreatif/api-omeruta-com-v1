<?php

function create_labels($name) {
	
    return array(
	    'name'                  => $name.'s',
	    'singular_name'         => $name,
	    'menu_name'             => $name.'s',
	    'name_admin_bar'        => $name,
	    'archives'              => $name.' Archives',
	    'attributes'            => $name.' Attributes',
	    'parent_item_colon'     => 'Parent '.$name.':',
	    'all_items'             => 'All '.$name.'s',
	    'add_new_item'          => 'Add New '.$name,
	    'add_new'               => 'Add New',
	    'new_item'              => 'New '.$name,
	    'edit_item'             => 'Edit '.$name,
	    'update_item'           => 'Update '.$name,
	    'view_item'             => 'View '.$name,
	    'view_items'            => 'View '.$name.'s',
	    'search_items'          => 'Search '.$name.'s',
	    'not_found'             => 'Not found',
	    'not_found_in_trash'    => 'Not found in Trash',
	    'featured_image'        => 'Featured Image',
	    'set_featured_image'    => 'Set featured image',
	    'remove_featured_image' => 'Remove featured image',
	    'use_featured_image'    => 'Use as featured image',
	    'insert_into_item'      => 'Insert into item',
	    'prev_text'             => 'Prev',
	    'next_text'             => 'Next',
	    'uploaded_to_this_item' => 'Uploaded to this item',
	    'items_list'            => $name.'s list',
	    'items_list_navigation' => $name.'s list navigation',
	    'filter_items_list'     => 'Filter '.$name.'s list',
	);
}

function register_custom_post_types() {
    // register_post_type('artists', array(
	// 	'label'                 => 'Artists',
	// 	'description'           => 'Artists Post Type',
	// 	'labels'                => create_labels('Artist'),
	// 	'supports'              => array( 'editor', 'title', 'revisions', 'author' ),
	// 	'taxonomies'            => array(),
	// 	'hierarchical'          => true,
	// 	'public'                => true,
	// 	'show_ui'               => true,
	// 	'show_in_menu'          => true,
	// 	'menu_position'         => 5,
    //     'menu_icon'            => 'dashicons-admin-users',
	// 	'show_in_admin_bar'     => true,
	// 	'show_in_nav_menus'     => true,
	// 	'can_export'            => true,
	// 	'has_archive'           => false,
	// 	'exclude_from_search'   => false,
	// 	'publicly_queryable'    => true,
	// 	'capability_type'       => 'post',
	// 	'show_in_rest'          => true,
	// 	'rest_base'             => 'artist',
	// 	'rest_controller_class' => 'WP_REST_Posts_Controller',
	// ));


}
add_action('init', 'register_custom_post_types', 0);



/* Add this since  WordPress only expects posts and pages to not have a slug in front */
function parse_request($query) {
	/*** EDITED POST TYPES GO HERE 👇 ***/
	$post_types =  array( 'post', 'page');

	if ( !$query->is_main_query() || count( $query->query ) != 2 || !isset($query->query['page'])) {
  return;
}

if ( ! empty( $query->query['name'] ) ) {
  $query->set( 'post_type', $post_types );
}
}
/* Fires after the query variable object is created, but before the actual query is run */
add_action( 'pre_get_posts', 'parse_request' );