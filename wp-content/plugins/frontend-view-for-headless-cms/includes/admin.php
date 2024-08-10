<?php
/**
 * Admin Page
 *
 * This is admin page
 *
 * @package Frontend View for Headless CMS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create admin menu
 *
 * @return void
 */
function fvhc_admin_menu() {
	add_menu_page(
		'Frontend View Settings',
		'Frontend View Settings',
		'manage_options',
		'fvhc_settings',
		'fvhc_settings_page'
	);
}
add_action( 'admin_menu', 'fvhc_admin_menu' );

/**
 * Admin page content
 *
 * @return void
 */
function fvhc_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}

	// Create a nonce.
	$nonce = wp_create_nonce( 'fvhc_action' );

	// Checking submitted form.
	if ( isset( $_POST['submit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( isset( $_POST['fvhc_nonce_field'] ) ? $_POST['fvhc_nonce_field'] : '' ) ), 'fvhc_action' ) ) {

		// Save the frontend site URL.
		$frontend_site_url = sanitize_url( wp_unslash( isset( $_POST['frontend_site_url'] ) ? $_POST['frontend_site_url'] : '' ) );
		update_option( 'fvhc_frontend_site_url', rtrim( $frontend_site_url, '/' ) );

		echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
	}

	// Get the current frontend site URL.
	$current_frontend_site_url = get_option( 'fvhc_frontend_site_url', '' );

	// Display the admin form.
	?>
	<div class="wrap">
		<h2>Frontend View Settings</h2>
		<form method="post" action="">
			<input type="hidden" name="fvhc_nonce_field" value="<?php echo esc_attr( $nonce ); ?>" />
			<label for="frontend_site_url">Frontend Site URL:</label>
			<input type="url" name="frontend_site_url" id="frontend_site_url" value="<?php echo esc_url( $current_frontend_site_url ); ?>" required>
			<p class="description">Enter the URL of the HeadLess CMS frontend site.</p>
			<?php submit_button( 'Save Settings', 'primary', 'submit' ); ?>
		</form>
		
	</div>
	<?php
}
