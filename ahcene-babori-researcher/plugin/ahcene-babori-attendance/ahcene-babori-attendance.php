<?php
/**
 * Plugin Name:       Ahcene Babori – Student Attendance
 * Plugin URI:        https://ahcenebabori.com
 * Description:       Take student attendance by scanning the student card (QR code / barcode) with the phone camera, from each course on the Teaching page. Teacher-only, private records, CSV export. No external services.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Dr. Ahcene Babori
 * License:           GPL-2.0-or-later
 * Text Domain:       ahcene-babori-attendance
 *
 * @package AhceneBaboriAttendance
 */

defined( 'ABSPATH' ) || exit;

define( 'ABA_VERSION', '1.1.0' );
define( 'ABA_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Translated UI string (EN/FR/AR; follows Polylang or the site locale).
 *
 * @param string $key Key.
 * @return string
 */
function aba_t( $key ) {
	static $strings = null;
	if ( null === $strings ) {
		$strings = require ABA_DIR . 'includes/strings.php';
	}
	$lang = function_exists( 'pll_current_language' ) && pll_current_language() ? pll_current_language() : substr( determine_locale(), 0, 2 );
	if ( isset( $strings[ $key ][ $lang ] ) ) {
		return $strings[ $key ][ $lang ];
	}
	return isset( $strings[ $key ]['en'] ) ? $strings[ $key ]['en'] : $key;
}

require_once ABA_DIR . 'includes/class-aba-attendance.php';
add_action( 'plugins_loaded', array( 'ABA_Attendance', 'init' ) );
register_activation_hook( __FILE__, array( 'ABA_Attendance', 'activate' ) );

/** The courses live in the Ahcene Babori core plugin. */
add_action(
	'admin_notices',
	static function () {
		if ( current_user_can( 'activate_plugins' ) && ! post_type_exists( 'abc_teaching' ) && did_action( 'init' ) ) {
			echo '<div class="notice notice-warning"><p><strong>Student Attendance</strong> needs the <em>Ahcene Babori Core</em> plugin (Teaching courses). Activate it, or use the <code>aba_post_types</code> filter to point to another post type.</p></div>';
		}
	}
);
