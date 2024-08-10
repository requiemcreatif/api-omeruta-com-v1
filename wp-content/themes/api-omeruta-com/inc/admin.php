<?php

// Custom menu order
function custom_menu_order($menu_ord) {
    if (!$menu_ord) return true;
    return array(
        'index.php', // Dashboard
        'edit.php?post_type=page', // Pages
        'edit.php', // Posts
        //'edit.php?post_type=subscribers', // Subscribers CPT
        'upload.php', // Media
        //'site-options', // ACF Site Options
        'plugins.php', // Plugins
        'users.php', // Users
        'tools.php', // Tools
        'options-general.php', // Settings
        //'separator-last', // Last separator
    );
}
add_filter('custom_menu_order', 'custom_menu_order');
add_filter('menu_order', 'custom_menu_order');

// Make ACF fields readonly
function readonly_field($field) {
    $field['readonly'] = '1';
    return $field;
}
add_filter('acf/load_field/name=user_ratings_counter', 'readonly_field');
add_filter('acf/load_field/name=user_ratings_sum', 'readonly_field');
add_filter('acf/load_field/name=user_ratings_last_updated', 'readonly_field');
add_filter('acf/load_field/name=user_ratings_last_rating', 'readonly_field');

// ACF JSON sync
function acf_json_save_point($path) {
    $path = get_template_directory() . '/acf-json';
    return $path;
}
add_filter('acf/settings/save_json', 'acf_json_save_point');

function acf_json_load_point($paths) {
    unset($paths[0]);
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
}
add_filter('acf/settings/load_json', 'acf_json_load_point');

// Disable live preview
add_action('customize_preview_init', function () {
    die("<h2>⚠️ The customizer is disabled. Please save and preview your site on the frontend ⚠️</h2>");
}, 1);

// Remove default dashboard widgets
function remove_dashboard_widgets() {
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['yoast_db_widget']);
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

// Add custom dashboard widget
function my_custom_dashboard_widgets() {
    wp_add_dashboard_widget('cb_notifications', 'Notifications', 'cb_notifications_dashboard_widget');
}
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');

// function subscriber_count_dashboard_widget() {
//     $subscriber_count = wp_count_posts('subscribers')->publish;
//     echo "<p>Total Subscribers: " . $subscriber_count . "</p>";
// }

// function cb_notifications_dashboard_widget() {
//     $out = RT_HCMS_Service::outlink_sql('bethard', ['casinos'], ['out_link_slug']);
//     echo "<p>" . $out[0]->post_id . "</p>";
// }

// Add featured image support
function add_featured_image_support() {
    add_theme_support('post-thumbnails', apply_filters('pagelines_post-thumbnails', array('post')));
}
add_action('after_setup_theme', 'add_featured_image_support');

// Customize columns for subscribers list
// function customize_subscribers_columns($columns) {
//     $columns = array(
//         'cb' => '<input type="checkbox" />',
//         'title' => __('Email'),
//         'date' => __('Date Subscribed')
//     );
//     return $columns;
// }
// add_filter('manage_subscribers_posts_columns', 'customize_subscribers_columns');

// // Display subscriber email in the admin list
// function display_subscribers_columns($column, $post_id) {
//     switch ($column) {
//         case 'title':
//             echo get_the_title($post_id);
//             break;
//     }
// }
// add_action('manage_subscribers_posts_custom_column', 'display_subscribers_columns', 10, 2);

// Optionally, you can add more admin customizations here