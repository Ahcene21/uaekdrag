<?php
/**
 * Plugin Name:       Ahcene Babori Core
 * Plugin URI:        https://ahcenebabori.com
 * Description:       Academic data layer for the website of Dr. Ahcene Babori: publications, books, projects, presentations, teaching, academic service, resources, research notes, researcher profile, statistics, SEO/schema, contact form and future import connectors. Content lives here, so it survives a theme change.
 * Version:           1.0.7
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

define( 'ABC_VERSION', '1.0.7' );
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

/**
 * Menu links to starter pages are resolved at run time to the page of the CURRENT language,
 * so a menu item can never point to the wrong-language or a missing page.
 */
add_filter(
	'wp_setup_nav_menu_item',
	static function ( $item ) {
		if ( is_admin() || empty( $item->type ) || 'post_type' !== $item->type || 'page' !== $item->object ) {
			return $item;
		}
		$key = get_post_meta( (int) $item->object_id, '_abc_page_key', true );
		if ( $key && 'home' !== $key ) {
			$url = abc_page_url( $key );
			if ( $url ) {
				$item->url = $url;
			}
		}
		return $item;
	}
);

/**
 * One-time upgrade tasks.
 */
add_action(
	'init',
	static function () {
		$done = (string) get_option( 'abc_db_version', '0' );
		if ( version_compare( $done, '1.0.7', '>=' ) ) {
			return;
		}
		if ( version_compare( $done, '1.0.1', '<' ) ) {
			abc_core_upgrade_101();
		}
		// 1.0.2: About page becomes a chronological timeline (only pages still holding untouched sample text are replaced).
		$about = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'lang' => '', 'meta_key' => '_abc_page_key', 'meta_value' => 'about' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $about && file_exists( ABC_DIR . 'demo/demo-data.php' ) ) {
			$demo = require ABC_DIR . 'demo/demo-data.php';
			foreach ( $about as $pg ) {
				if ( false !== strpos( $pg->post_content, 'abt-chrono' ) || false === strpos( $pg->post_content, '[ADD VERIFIED INFORMATION]' ) ) {
					continue; // Already updated or edited by the owner.
				}
				$lang = function_exists( 'pll_get_post_language' ) ? abc_norm_lang( (string) pll_get_post_language( $pg->ID ) ) : 'en';
				if ( ! empty( $demo['pages']['about']['content'][ $lang ] ) ) {
					wp_update_post( array( 'ID' => $pg->ID, 'post_content' => $demo['pages']['about']['content'][ $lang ] ) );
				}
			}
		}
		// 1.0.6: About timeline entries become editable posts (About Timeline).
		if ( version_compare( $done, '1.0.6', '<' ) ) {
			$has = get_posts( array( 'post_type' => 'abc_career', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'lang' => '' ) );
			if ( ! $has ) {
				ABC_Demo::install_structure( 'abc_career_section' );
				ABC_Demo::install_samples( 'abc_career' );
			}
			foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'lang' => '', 'meta_key' => '_abc_page_key', 'meta_value' => 'about' ) ) as $pg ) { // phpcs:ignore WordPress.DB.SlowDBQuery
				$c = $pg->post_content;
				if ( false !== strpos( $c, 'abt-chrono' ) && false === strpos( $c, '[abc_career' ) ) {
					$s0 = strpos( $c, '<section class="abt-chrono">' );
					$s1 = strrpos( $c, '</section>' );
					if ( false !== $s0 && false !== $s1 ) {
						wp_update_post( array( 'ID' => $pg->ID, 'post_content' => substr( $c, 0, $s0 ) . '[abc_career]' . substr( $c, $s1 + 10 ) ) );
					}
				}
			}
		}
		// 1.0.7: Resources and CV removed from the primary menus; "Research Notes" renamed "Blog".
		$blog = array( 'en' => 'Blog', 'fr' => 'Blog', 'ar' => 'المدونة' );
		foreach ( array( 'EN', 'FR', 'AR' ) as $l ) {
			$menu = wp_get_nav_menu_object( 'Primary menu (' . $l . ')' );
			if ( ! $menu ) {
				continue;
			}
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $it ) {
				if ( 'abc_resource' === $it->object || ( 'page' === $it->object && 'cv' === get_post_meta( (int) $it->object_id, '_abc_page_key', true ) ) ) {
					wp_delete_post( $it->ID, true );
				} elseif ( 'abc_note' === $it->object && in_array( $it->title, array( 'Research Notes', 'Notes de recherche', 'ملاحظات بحثية' ), true ) ) {
					wp_update_post( array( 'ID' => $it->ID, 'post_title' => $blog[ strtolower( $l ) ] ) );
				}
			}
		}
		update_option( 'abc_db_version', '1.0.7', false );
		update_option( 'abc_needs_flush', 1, false );
	},
	100
);

/**
 * Upgrade tasks of 1.0.1.
 */
function abc_core_upgrade_101() {
	{
		// 1.0.1: "Research" removed from the starter primary menus.
		foreach ( array( 'EN', 'FR', 'AR' ) as $l ) {
			$menu = wp_get_nav_menu_object( 'Primary menu (' . $l . ')' );
			if ( ! $menu ) {
				continue;
			}
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $it ) {
				if ( 'page' === $it->object && 'research' === get_post_meta( (int) $it->object_id, '_abc_page_key', true ) ) {
					wp_delete_post( $it->ID, true );
				}
			}
		}
		// 1.0.1: Presentation Type became a taxonomy – migrate the old select value.
		$ids = get_posts( array( 'post_type' => 'abc_presentation', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => 'abc_pres_type' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		foreach ( $ids as $pid ) {
			$old = get_post_meta( $pid, 'abc_pres_type', true );
			$map = array( 'keynote' => 'Keynote', 'invited' => 'Invited talk', 'paper' => 'Conference paper', 'poster' => 'Poster', 'workshop' => 'Workshop', 'webinar' => 'Webinar', 'seminar' => 'Seminar' );
			if ( $old && isset( $map[ $old ] ) && ! has_term( '', 'abc_pres_type', $pid ) ) {
				$t = term_exists( $map[ $old ], 'abc_pres_type' );
				if ( $t ) {
					wp_set_object_terms( $pid, array( (int) ( is_array( $t ) ? $t['term_id'] : $t ) ), 'abc_pres_type' );
				}
			}
		}
	}
}

/**
 * Old Research Notes URLs (/research-notes/…) now live under /blog/….
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! is_404() || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		if ( false !== strpos( $uri, '/research-notes' ) ) {
			wp_safe_redirect( home_url( str_replace( '/research-notes', '/blog', $uri ) ), 301 );
			exit;
		}
	}
);
