<?php
ini_set('error_reporting', E_STRICT);
ini_set('memory_limit', -1);

// Import the HCMS_Contents class
require_once __DIR__ . '/../helpers/contents.php';

function register_routes()
{
    static $routes_registered = false;

    if ($routes_registered) {
        return;
    }
    print_r('Registering routes in apiomeruta API');

    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        print_r('Registering routes in apiomeruta API');
    }

    $routes_registered = true;


    // Route for fetching all posts
    register_rest_route('apiomeruta/v1', '/frontpage', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_frontpage_data',
    ));

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

    /* Evo Tech Routes*/
    register_rest_route('apiomeruta/v1', 'custom-registration', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'custom_user_registration')
    ));

    register_rest_route('apiomeruta/v1', 'custom-login', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'custom_user_login')
    ));

    register_rest_route('apiomeruta/v1', 'get-user-data', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'get_user_data'),
        'permission_callback' => array('HCMS_Evo_Tech_Auth', 'is_user_authenticated')
    ));

    register_rest_route('apiomeruta/v1', 'update-profile', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'update_user_profile'),
        'permission_callback' => array('HCMS_Evo_Tech_Auth', 'is_user_authenticated')
    ));

    register_rest_route('apiomeruta/v1', 'change-password', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'change_user_password'),
        'permission_callback' => array('HCMS_Evo_Tech_Auth', 'is_user_authenticated')
    ));

    register_rest_route('apiomeruta/v1', 'reset-password', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'initiate_password_reset'),
    ));

    register_rest_route('apiomeruta/v1', 'reset-password-complete', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array('HCMS_Evo_Tech_Auth', 'complete_password_reset'),
    ));

}
add_action('rest_api_init', 'register_routes');


// Function frontpage data
function get_frontpage_data()
{
    $front_page_id = get_option('page_on_front');
    if (!$front_page_id) {
        return new WP_Error('no_front_page', 'No front page set', array('status' => 404));
    }

    $front_page = get_post($front_page_id);
    if (!$front_page) {
        return new WP_Error('front_page_not_found', 'Front page not found', array('status' => 404));
    }

    $data = HCMS_Contents::format_post_data($front_page);
    return new WP_REST_Response($data, 200);
}

// Function to fetch all posts and pages
function get_all_posts_and_pages($request)
{
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
function get_menu_items($request)
{
    $menu_name = $request->get_param('menu') ?: 'omeruta-menu';
    $menu_items = wp_get_nav_menu_items($menu_name);

    if (!$menu_items) {
        return new WP_Error('no_menu', 'No menu found', array('status' => 404));
    }

    $current_user = wp_get_current_user();
    $is_admin = in_array('administrator', $current_user->roles);

    $data = array_map(function ($item) use ($is_admin) {
        $should_show = true;
        // Hide admin dashboard for non-admins
        if ($item->title === 'Admin Dashboard' && !$is_admin) {
            $should_show = false;
        }
        return array(
            'ID' => $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'slug' => basename(parse_url($item->url, PHP_URL_PATH)),
            'should_show' => $should_show,
        );
    }, $menu_items);

    return new WP_REST_Response($data, 200);
}

