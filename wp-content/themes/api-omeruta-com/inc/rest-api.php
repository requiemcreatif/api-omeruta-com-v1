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

    register_rest_route('apiomeruta/v1', '/posts', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_all_posts',
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




// Function to fetch all posts
function get_all_posts($request) {
    $args = array(
        'post_type' => 'post',
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

    $posts = get_posts($args);
    $data = array_map([HCMS_Contents::class, 'format_post_data'], $posts);
    return new WP_REST_Response($data, 200);
}
