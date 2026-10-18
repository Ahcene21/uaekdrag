<?php
/**
 * Plugin Name:       Ahcene Babori – Share & Statistics
 * Plugin URI:        https://ahcenebabori.com
 * Description:       Social-sharing buttons for blog articles and privacy-friendly first-party statistics (page views, visitors, top articles, referrers, shares) for each article and for the whole site. No cookies, no IP addresses stored, no external services.
 * Version:           1.1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Dr. Ahcene Babori
 * License:           GPL-2.0-or-later
 * Text Domain:       ahcene-babori-insights
 *
 * @package AhceneBaboriInsights
 */

defined( 'ABSPATH' ) || exit;

define( 'ABI_VERSION', '1.1.1' );
define( 'ABI_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABI_URL', plugin_dir_url( __FILE__ ) );

/**
 * UI string (EN/FR/AR; follows Polylang or the site locale).
 *
 * @param string $key Key.
 * @return string
 */
function abi_t( $key ) {
	static $s = null;
	if ( null === $s ) {
		$s = require ABI_DIR . 'includes/strings.php';
	}
	$lang = function_exists( 'pll_current_language' ) && pll_current_language() ? pll_current_language() : substr( determine_locale(), 0, 2 );
	if ( isset( $s[ $key ][ $lang ] ) ) {
		return $s[ $key ][ $lang ];
	}
	return isset( $s[ $key ]['en'] ) ? $s[ $key ]['en'] : $key;
}

/** Plugin settings with defaults. */
function abi_settings() {
	return wp_parse_args(
		(array) get_option( 'abi_settings', array() ),
		array(
			'networks'   => array( 'facebook', 'x', 'linkedin', 'whatsapp', 'telegram', 'email', 'copy', 'native' ),
			'show_views' => 1,
			'show_visits' => 1,
			'track'      => 1,
			'types'      => array( 'abc_note', 'abc_publication', 'abc_project', 'abc_presentation', 'abc_book', 'abc_teaching', 'abc_service', 'page', 'post' ),
		)
	);
}

require_once ABI_DIR . 'includes/class-abi-stats.php';
require_once ABI_DIR . 'includes/class-abi-share.php';

add_action(
	'plugins_loaded',
	static function () {
		ABI_Stats::init();
		ABI_Share::init();
	}
);
register_activation_hook( __FILE__, array( 'ABI_Stats', 'install' ) );
