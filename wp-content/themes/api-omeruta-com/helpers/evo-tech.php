<?php

class HCMS_Evo_Tech_Auth
{
    /**
     * Extract and validate the authentication token from the request
     *
     * @param WP_REST_Request $request The request object
     * @return string|WP_Error The token if valid, WP_Error otherwise
     */
    private static function get_token_from_request($request)
    {
        $headers = $request->get_headers();
        $auth_header = isset($headers['authorization'][0]) ? $headers['authorization'][0] : '';
        
        error_log("Received Authorization header: " . $auth_header);

        // Remove "Bearer" prefix if it exists
        $token = preg_replace('/^Bearer\s+/i', '', $auth_header);

        if (empty($token)) {
            error_log("Invalid or missing token in request");
            return new WP_Error('unauthorized', 'Invalid or missing token', ['status' => 401]);
        }

        error_log("Extracted token: " . $token);
        return $token;
    }


    /**
     * Get the authenticated user based on the token
     *
     * @param string $token The authentication token
     * @return WP_User|WP_Error The user object if found, WP_Error otherwise
     */
    private static function get_user_by_token($token)
    {
        $users = get_users([
            'meta_key' => 'auth_token',
            'meta_value' => $token,
            'number' => 1
        ]);

        if (empty($users)) {
            return new WP_Error('unauthorized', 'Invalid token', ['status' => 401]);
        }

        return $users[0];
    }

    /**
     * Authenticate user based on the provided token
     *
     * @param WP_REST_Request $request The request object
     * @return bool Whether the user is authenticated
     */
    public static function is_user_authenticated($request)
    {
        error_log("Attempting to authenticate user");
        $token = self::get_token_from_request($request);
        if (is_wp_error($token)) {
            error_log("Authentication failed: " . $token->get_error_message());
            return false;
        }

        $user = get_users([
            'meta_key' => 'auth_token',
            'meta_value' => $token,
            'number' => 1,
            'fields' => 'ID'
        ]);

        $is_authenticated = !empty($user);
        error_log("User authenticated: " . ($is_authenticated ? "Yes" : "No"));
        return $is_authenticated;
    }


    /**
     * Register a new user
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error The response or error object
     */
    public static function custom_user_registration($request)
{
    $username = sanitize_user($request->get_param('username'));
    $email = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');
    $is_adult = $request->get_param('isAdult');
    $not_registered_on_spelpaus = $request->get_param('notRegisteredOnSpelpaus');
    $agree_to_terms = $request->get_param('agreeToTerms');
    $agree_to_newsletter = $request->get_param('agreeToNewsletter');

    if (empty($username) || empty($email) || empty($password)) {
        return new WP_Error('missing_fields', 'Please provide username, email, and password', ['status' => 400]);
    }

    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Please provide a valid email address', ['status' => 400]);
    }

    if (username_exists($username)) {
        return new WP_Error('username_exists', 'This username is already taken', ['status' => 400]);
    }

    if (email_exists($email)) {
        return new WP_Error('email_exists', 'This email is already registered', ['status' => 400]);
    }

    if (!$is_adult || !$not_registered_on_spelpaus || !$agree_to_terms) {
        return new WP_Error('terms_not_accepted', 'You must accept all required terms', ['status' => 400]);
    }

    error_log('Registration attempt: ' . print_r($request->get_params(), true));

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        error_log('Registration failed: ' . $user_id->get_error_message());
        return new WP_Error('registration_failed', $user_id->get_error_message(), ['status' => 400]);
    }

    wp_update_user([
        'ID' => $user_id,
        'role' => 'subscriber'
    ]);

    // Save additional user meta
    update_user_meta($user_id, 'is_adult', $is_adult);
    update_user_meta($user_id, 'not_registered_on_spelpaus', $not_registered_on_spelpaus);
    update_user_meta($user_id, 'agree_to_terms', $agree_to_terms);
    update_user_meta($user_id, 'agree_to_newsletter', $agree_to_newsletter);

    return new WP_REST_Response(['message' => 'User registered successfully'], 200);
}


    /**
     * Authenticate and log in a user
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error The response or error object
     */
    public static function custom_user_login($request)
{
    $username = $request->get_param('username');
    $password = $request->get_param('password');
    $remember_me = $request->get_param('rememberMe');

    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        return new WP_Error('login_failed', $user->get_error_message(), ['status' => 401]);
    }

    $token = wp_generate_password(32, false);
    update_user_meta($user->ID, 'auth_token', $token);

    if ($remember_me) {
        $expiration = time() + (14 * DAY_IN_SECONDS);
        update_user_meta($user->ID, 'auth_token_expiration', $expiration);
    } else {
        delete_user_meta($user->ID, 'auth_token_expiration');
    }

    $user_data = [
        'ID' => $user->ID,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
    ];

    return new WP_REST_Response([
        'token' => $token,
        'user' => $user_data
    ], 200);
}

    /**
     * Get user data for the authenticated user
     *
     * @param WP_REST_Request $request The request object
     * @return array|WP_Error The user data or error object
     */
    public static function get_user_data($request)
    {
        $token = self::get_token_from_request($request);
        if (is_wp_error($token)) {
            return $token;
        }

        $user = self::get_user_by_token($token);
        if (is_wp_error($user)) {
            return $user;
        }

        $avatar_id = get_user_meta($user->ID, 'avatar', true);
        $avatar_url = $avatar_id ? wp_get_attachment_url($avatar_id) : get_avatar_url($user->ID);

        if ($avatar_url && !filter_var($avatar_url, FILTER_VALIDATE_URL)) {
            $avatar_url = get_site_url(null, $avatar_url);
        }

        return [
            'username' => $user->user_login,
            'email' => $user->user_email,
            'firstname' => $user->first_name,
            'lastname' => $user->last_name,
            'phone' => get_user_meta($user->ID, 'phone', true),
            'birthdate' => get_user_meta($user->ID, 'birthdate', true),
            'avatar' => $avatar_url,
            'registrationDate' => $user->user_registered,
        ];
    }

    /**
     * Update user profile
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error The response or error object
     */
    public static function update_user_profile($request)
{
    $token = self::get_token_from_request($request);
    if (is_wp_error($token)) {
        return $token;
    }

    $user = self::get_user_by_token($token);
    if (is_wp_error($user)) {
        return $user;
    }

    $params = $request->get_params();
    $user_data = [
        'ID' => $user->ID,
        'first_name' => sanitize_text_field($params['firstname']),
        'last_name' => sanitize_text_field($params['lastname']),
    ];

    $user_id = wp_update_user($user_data);

    if (is_wp_error($user_id)) {
        return new WP_Error('update_failed', $user_id->get_error_message(), ['status' => 400]);
    }

    if (isset($params['phone'])) {
        update_user_meta($user->ID, 'phone', sanitize_text_field($params['phone']));
    }

    // Handle birthday update
    if (isset($params['birthdate'])) {
        $birthdate = sanitize_text_field($params['birthdate']);
        if (empty($birthdate)) {
            delete_user_meta($user->ID, 'birthdate');
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
            update_user_meta($user->ID, 'birthdate', $birthdate);
        } else {
            return new WP_Error('invalid_birthdate', 'Invalid birthdate format. Please use YYYY-MM-DD', ['status' => 400]);
        }
    }

    // Handle avatar upload
    $files = $request->get_file_params();
    if (!empty($files['avatar'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('avatar', 0);
        if (is_wp_error($attachment_id)) {
            return new WP_Error('avatar_upload_failed', $attachment_id->get_error_message(), ['status' => 400]);
        }
        update_user_meta($user->ID, 'avatar', $attachment_id);
    }

    $updated_user_data = self::get_user_data($request);

    return new WP_REST_Response([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'user' => $updated_user_data
    ], 200);
}
    /**
     * Initiate password reset
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error The response or error object
     */
    public static function initiate_password_reset($request)
    {
        $email = sanitize_email($request->get_param('email'));
        $frontend_url = esc_url_raw($request->get_param('frontendUrl'));

        if (empty($email)) {
            return new WP_Error('missing_email', 'Please provide an email address', ['status' => 400]);
        }

        $user = get_user_by('email', $email);

        if (!$user) {
            return new WP_Error('user_not_found', 'No user found with this email address', ['status' => 404]);
        }

        $reset_key = get_password_reset_key($user);

        if (is_wp_error($reset_key)) {
            return new WP_Error('reset_key_failed', 'Failed to generate reset key', ['status' => 500]);
        }

        $reset_link = add_query_arg(
            [
                'key' => $reset_key,
                'login' => rawurlencode($user->user_login),
            ],
            $frontend_url
        );

        $to = $email;
        $subject = 'Återställning av lösenord för ditt konto';
        $message = "Hej {$user->display_name},\n\n";
        $message .= "Du har begärt att återställa ditt lösenord. Klicka på länken nedan för att återställa det:\n\n";
        $message .= $reset_link . "\n\n";
        $message .= "Om du inte har begärt detta, vänligen ignorera detta e-postmeddelande.\n\n";
        $message .= "Med vänliga hälsningar,\nDitt webbplatsteam";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        $email_sent = wp_mail($to, $subject, $message, $headers);

        if ($email_sent) {
            return new WP_REST_Response([
                'message' => 'E-post för lösenordsåterställning har skickats. Kontrollera din e-post för instruktioner.',
            ], 200);
        } else {
            return new WP_Error('email_failed', 'Kunde inte skicka e-post för lösenordsåterställning', ['status' => 500]);
        }
    }

    /**
     * Complete password reset
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error The response or error object
     */
    public static function complete_password_reset($request)
    {
        $key = $request->get_param('key');
        $login = $request->get_param('login');
        $new_password = $request->get_param('new_password');

        if (empty($key) || empty($login) || empty($new_password)) {
            return new WP_Error('missing_fields', 'Please provide all required fields', ['status' => 400]);
        }

        $user = check_password_reset_key($key, $login);

        if (is_wp_error($user)) {
            return new WP_Error('invalid_key', 'Invalid or expired reset key', ['status' => 400]);
        }

        reset_password($user, $new_password);

        return new WP_REST_Response(['message' => 'Password reset successfully'], 200);
    }

    /**
     * Change user password
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error The response or error object
     */
    public static function change_user_password($request)
    {
        $token = self::get_token_from_request($request);
        if (is_wp_error($token)) {
            return $token;
        }

        $user = self::get_user_by_token($token);
        if (is_wp_error($user)) {
            return $user;
        }

        $current_password = $request->get_param('currentPassword');
        $new_password = $request->get_param('newPassword');
        $confirm_password = $request->get_param('confirmPassword');

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            return new WP_Error('missing_fields', 'Please provide all required fields', ['status' => 400]);
        }

        if ($new_password !== $confirm_password) {
            return new WP_Error('password_mismatch', 'New passwords do not match', ['status' => 400]);
        }

        if (!wp_check_password($current_password, $user->user_pass, $user->ID)) {
            return new WP_Error('invalid_password', 'Nuvarande lösenord är felaktigt', ['status' => 400]);
        }

        wp_set_password($new_password, $user->ID);

        $new_token = wp_generate_password(32, false);
        update_user_meta($user->ID, 'auth_token', $new_token);

        return new WP_REST_Response([
            'message' => 'Password changed successfully',
            'token' => $new_token
        ], 200);
    }
}