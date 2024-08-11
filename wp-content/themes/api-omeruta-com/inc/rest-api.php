<?php
ini_set('error_reporting', E_STRICT);
ini_set('memory_limit', -1);

// Import the HCMS_Contents class
require_once __DIR__ . '/../helpers/contents.php';

function register_routes()
{
    //print_r('Registering routes in apiomeruta API');

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

    // Register REST API endpoints
    register_rest_route('apiomeruta/v1', '/register', array(
        'methods' => 'POST',
        'callback' => 'handle_user_registration',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('apiomeruta/v1', '/login', array(
        'methods' => 'POST',
        'callback' => 'handle_user_login',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('apiomeruta/v1', '/profile', array(
        array(
            'methods' => 'GET',
            'callback' => 'get_user_profile',
            'permission_callback' => '__return_true'
        ),
        array(
            'methods' => 'PUT',
            'callback' => 'update_user_profile',
            'permission_callback' => '__return_true'
        )
    ));

    register_rest_route('apiomeruta/v1', '/lost-password', array(
        'methods' => 'POST',
        'callback' => 'handle_lost_password',
        'permission_callback' => '__return_true'
    ));


    error_log('Routes registered successfully');
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
    $menu_name = $request->get_param('menu') ?: 'omeruta-menu';  // Adjust 'primary-menu' to your menu name
    $menu_items = wp_get_nav_menu_items($menu_name);

    if (!$menu_items) {
        return new WP_Error('no_menu', 'No menu found', array('status' => 404));
    }

    $data = array_map(function ($item) {
        return array(
            'ID' => $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'slug' => basename(parse_url($item->url, PHP_URL_PATH)),
        );
    }, $menu_items);

    return new WP_REST_Response($data, 200);
}

// function for user registration
function handle_user_registration($request)
{
    $username = $request->get_param('username');
    $email = $request->get_param('email');
    $password = $request->get_param('password');

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', $user_id->get_error_message(), array('status' => 400));
    }

    $user = new WP_User($user_id);
    $user->set_role('subscriber');

    return new WP_REST_Response(array('message' => 'User registered successfully'), 200);
}

// User Login
function handle_user_login($request)
{
    $creds = array(
        'user_login'    => $request->get_param('username'),
        'user_password' => $request->get_param('password'),
        'remember'      => true
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        return new WP_Error('login_failed', $user->get_error_message(), array('status' => 401));
    }

    // Generate a token (you might want to use a more secure method in production)
    $token = wp_generate_password(32, false);

    // Store the token (you might want to use a more secure storage in production)
    update_user_meta($user->ID, 'auth_token', $token);

    return new WP_REST_Response(array(
        'message' => 'Login successful',
        'user' => array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
        ),
        'token' => $token
    ), 200);
}

// User Profile
function get_user_profile($request)
{
    $headers = $request->get_headers();
    $auth_header = isset($headers['authorization'][0]) ? $headers['authorization'][0] : '';

    if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        return new WP_Error('invalid_token', 'Token not found in request', array('status' => 401));
    }

    $token = $matches[1];
    $user = get_users(array(
        'meta_key' => 'auth_token',
        'meta_value' => $token,
        'number' => 1
    ));

    if (empty($user)) {
        return new WP_Error('invalid_token', 'Invalid token', array('status' => 401));
    }

    $user = $user[0];

    return new WP_REST_Response(array(
        'id' => $user->ID,
        'username' => $user->user_login,
        'email' => $user->user_email,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
    ), 200);
}

// Update User Profile
function update_user_profile($request)
{
    $headers = $request->get_headers();
    $auth_header = isset($headers['authorization'][0]) ? $headers['authorization'][0] : '';

    if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        return new WP_Error('invalid_token', 'Token not found in request', array('status' => 401));
    }

    $token = $matches[1];
    $user = get_users(array(
        'meta_key' => 'auth_token',
        'meta_value' => $token,
        'number' => 1
    ));

    if (empty($user)) {
        return new WP_Error('invalid_token', 'Invalid token', array('status' => 401));
    }

    $user = $user[0];
    $user_id = $user->ID;

    $first_name = $request->get_param('first_name');
    $last_name = $request->get_param('last_name');
    $email = $request->get_param('email');

    $userdata = array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_email' => $email,
    );

    $user_id = wp_update_user($userdata);

    if (is_wp_error($user_id)) {
        return new WP_Error('update_failed', $user_id->get_error_message(), array('status' => 400));
    }

    return new WP_REST_Response(array(
        'message' => 'Profile updated successfully',
        'user' => array(
            'id' => $user_id,
            'username' => $user->user_login,
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
        )
    ), 200);
}


// Lost Password
function handle_lost_password($request)
{
    error_log('Lost password request received');
    $email = $request->get_param('email');

    if (empty($email)) {
        error_log('Empty email provided');
        return new WP_Error('empty_email', 'Email is required.', array('status' => 400));
    }

    $user = get_user_by('email', $email);

    if (!$user) {
        error_log('No user found with email: ' . $email);
        return new WP_Error('invalid_email', 'There is no user registered with that email address.', array('status' => 400));
    }

    $key = get_password_reset_key($user);

    if (is_wp_error($key)) {
        error_log('Error generating reset key: ' . $key->get_error_message());
        return $key;
    }

    $message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
    $message .= network_home_url('/') . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
    $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');

    $sent = wp_mail($user->user_email, __('Password Reset'), $message);

    if ($sent) {
        error_log('Password reset email sent successfully to: ' . $email);
        return new WP_REST_Response(array('message' => 'Password reset email sent.'), 200);
    } else {
        error_log('Failed to send password reset email to: ' . $email);
        return new WP_Error('email_failed', 'The email could not be sent.', array('status' => 500));
    }
}
