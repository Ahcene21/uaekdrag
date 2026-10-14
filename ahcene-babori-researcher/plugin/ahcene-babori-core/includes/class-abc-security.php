<?php
/**
 * Low-risk hardening: safe response headers, no WordPress version leak,
 * no public user enumeration (REST users / ?author=N). Each can be switched off with a filter.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Security {

	public static function init() {
		add_action( 'send_headers', array( __CLASS__, 'headers' ) );
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
		if ( apply_filters( 'abc_harden_users', true ) ) {
			add_filter( 'rest_endpoints', array( __CLASS__, 'hide_users' ) );
			add_action( 'template_redirect', array( __CLASS__, 'author_scan' ), 1 );
		}
	}

	/** Safe defaults; camera stays allowed for the attendance scanner on this site only. */
	public static function headers() {
		if ( is_admin() || headers_sent() || ! apply_filters( 'abc_security_headers', true ) ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(self), microphone=(), geolocation=(), payment=()' );
	}

	/** Anonymous visitors cannot list or read users through the REST API. */
	public static function hide_users( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			foreach ( array_keys( $endpoints ) as $route ) {
				if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
					unset( $endpoints[ $route ] );
				}
			}
		}
		return $endpoints;
	}

	/** ?author=1 would reveal login names: send scanners home. */
	public static function author_scan() {
		if ( ! is_user_logged_in() && isset( $_GET['author'] ) && ! is_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
}
