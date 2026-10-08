<?php
/**
 * Plugin Name:       Ahcene Babori Core
 * Plugin URI:        https://ahcenebabori.com
 * Description:       Academic data layer for the website of Dr. Ahcene Babori: publications, books, projects, presentations, teaching, academic service, resources, research notes, researcher profile, statistics, SEO/schema, contact form and future import connectors. Content lives here, so it survives a theme change.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Dr. Ahcene Babori
 * Author URI:        https://ahcenebabori.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ahcene-babori-core
 * Domain Path:       /languages
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

define( 'ABC_VERSION', '1.0.0' );
define( 'ABC_FILE', __FILE__ );
define( 'ABC_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABC_URL', plugin_dir_url( __FILE__ ) );

require_once ABC_DIR . 'includes/helpers.php';
require_once ABC_DIR . 'includes/class-abc-post-types.php';
require_once ABC_DIR . 'includes/class-abc-fields.php';
require_once ABC_DIR . 'includes/class-abc-settings.php';
require_once ABC_DIR . 'includes/class-abc-query.php';
require_once ABC_DIR . 'includes/class-abc-shortcodes.php';
require_once ABC_DIR . 'includes/class-abc-contact.php';
require_once ABC_DIR . 'includes/class-abc-seo.php';
require_once ABC_DIR . 'includes/class-abc-polylang.php';
require_once ABC_DIR . 'includes/class-abc-rest.php';
require_once ABC_DIR . 'includes/class-abc-import.php';
require_once ABC_DIR . 'includes/class-abc-demo.php';

/**
 * Boot all modules.
 */
function abc_core_boot() {
	load_plugin_textdomain( 'ahcene-babori-core', false, dirname( plugin_basename( ABC_FILE ) ) . '/languages' );

	ABC_Post_Types::init();
	ABC_Fields::init();
	ABC_Settings::init();
	ABC_Query::init();
	ABC_Shortcodes::init();
	ABC_Contact::init();
	ABC_SEO::init();
	ABC_Polylang::init();
	ABC_REST::init();
	ABC_Import::init();
	ABC_Demo::init();
}
add_action( 'plugins_loaded', 'abc_core_boot' );

register_activation_hook( __FILE__, 'abc_core_activate' );
function abc_core_activate() {
	ABC_Post_Types::register();
	flush_rewrite_rules();
	if ( false === get_option( 'abc_settings', false ) ) {
		add_option( 'abc_settings', ABC_Settings::defaults(), '', false );
	}
}

register_deactivation_hook( __FILE__, 'abc_core_deactivate' );
function abc_core_deactivate() {
	flush_rewrite_rules();
}
