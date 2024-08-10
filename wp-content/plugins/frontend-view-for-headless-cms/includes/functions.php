<?php
/**
 * Functions File
 *
 * This file contains functions
 *
 * @package Frontend View for Headless CMS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle redirection.
 *
 * @return void
 */
function fvhc_redirection() {
	$frontend_site_url = get_option( 'fvhc_frontend_site_url', '' );

	if ( $frontend_site_url ) {
		$https = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'];

		if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$http_host   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			$request_uri = sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );

			$full_url = sanitize_url( ( $https ? 'https' : 'http' ) . '://' . $http_host . $request_uri );

			// Check if it's a preview.
			if ( 'true' === get_query_var( 'preview' ) && get_query_var( 'p' ) ) {
				$post_id = intval( get_query_var( 'p' ) );
				$post    = get_post( $post_id );

				if ( $post ) {
					$post_slug    = $post->post_name;
					$redirect_url = esc_url_raw( trailingslashit( $frontend_site_url ) . 'preview/' . $post_slug . '/' );

					wp_safe_redirect( $redirect_url, 301 );
					exit;
				}
			}

			if ( is_singular() || is_tax() || is_category() || is_home() || is_author() ) {
				$redirect_url = esc_url_raw( str_replace( site_url(), esc_url_raw( $frontend_site_url ), $full_url ) );
				wp_safe_redirect( $redirect_url, 301 );
				exit;
			}
		}
	}
}
add_action( 'template_redirect', 'fvhc_redirection' );

/**
 * Extend the List of Allowed Domains for wp_safe_redirect().
 *
 * @param array $hosts Array of allowed domains.
 * @return array
 */
function fvhc_extend_allowed_domains_list( $hosts ) {

	$frontend_site_url = sanitize_url( get_option( 'fvhc_frontend_site_url', '' ) );

	if ( wp_http_validate_url( $frontend_site_url ) ) {
		$url_without_protocol = str_replace( array( 'http://', 'https://' ), '', $frontend_site_url );
		$hosts[]              = $url_without_protocol;
	}

	return $hosts;
}
add_filter( 'allowed_redirect_hosts', 'fvhc_extend_allowed_domains_list' );
