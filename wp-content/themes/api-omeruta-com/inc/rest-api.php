<?php
ini_set('error_reporting', E_STRICT);
ini_set('memory_limit', -1);

// Import the HCMS_Contents class
require_once __DIR__ . '/../helpers/contents.php';

function register_routes() {
    //print_r('Registering routes in apiomeruta API');

        // Route for fetching all posts
    /*register_rest_route('apiomeruta/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'get_all_posts',
        'permission_callback' => '__return_true'
    ));*/

    register_rest_route('apiomeruta/v1', '/menu', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_menu_items',
    ));

    register_rest_route('apiomeruta/v1', '/posts', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_all_posts_and_pages',
    ));
   
    // contact form route
    register_rest_route('apiomeruta/v1', '/contact', array(
        'methods' => 'POST',
        'callback' => 'handle_contact_form_submission',
        'permission_callback' => '__return_true'
    ));


    error_log('Routes registered successfully');
}
add_action('rest_api_init', 'register_routes');




// Function to fetch all posts and pages
function get_all_posts_and_pages($request) {
    $args = array(
        'post_type' => array('post', 'page'),  // Include both posts and pages
        'posts_per_page' => $request->get_param('per_page') ?: -1,
        'paged' => $request->get_param('page') ?: 1,
        'orderby' => $request->get_param('orderby') ?: 'date',
        'order' => $request->get_param('order') ?: 'DESC',
    );

    if ($request->get_param('search')) {
        $args['s'] = sanitize_text_field($request->get_param('search'));
    }

    if ($request->get_param('category')) {
        $args['category_name'] = sanitize_text_field($request->get_param('category'));
    }

    $posts_and_pages = get_posts($args);
    $data = array_map([HCMS_Contents::class, 'format_post_data'], $posts_and_pages);
    return new WP_REST_Response($data, 200);
}

// Function to fetch menu items
function get_menu_items($request) {
    $menu_name = $request->get_param('menu') ?: 'omeruta-menu';  // Adjust 'primary-menu' to your menu name
    $menu_items = wp_get_nav_menu_items($menu_name);

    if (!$menu_items) {
        return new WP_Error('no_menu', 'No menu found', array('status' => 404));
    }

    $data = array_map(function($item) {
        return array(
            'ID' => $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'slug' => basename(parse_url($item->url, PHP_URL_PATH)),
        );
    }, $menu_items);

    return new WP_REST_Response($data, 200);
}
