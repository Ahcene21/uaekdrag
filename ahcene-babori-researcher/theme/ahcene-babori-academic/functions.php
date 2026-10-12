<?php
/**
 * Ahcene Babori Academic – theme functions.
 * Design and layout only. Academic data (CPTs, fields, settings) lives in the
 * "Ahcene Babori Core" plugin.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;

define( 'ABT_VERSION', '1.0.34' );
define( 'ABT_DIR', get_template_directory() );
define( 'ABT_URI', get_template_directory_uri() );

/**
 * Is the companion plugin active?
 */
function abt_core_active() {
	return defined( 'ABC_VERSION' ) && function_exists( 'abc_t' );
}

/* ---------------------------------------------------------------- setup */
add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'ahcene-babori-academic', ABT_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 80, 'flex-width' => true, 'flex-height' => true ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );
		register_nav_menus(
			array(
				'primary' => __( 'Primary menu', 'ahcene-babori-academic' ),
				'footer'  => __( 'Footer menu', 'ahcene-babori-academic' ),
			)
		);
		add_image_size( 'abt-card', 720, 450, true );
	}
);

if ( ! isset( $content_width ) ) {
	$content_width = 760;
}

/* --------------------------------------------------- plugin dependency */
if ( ! abt_core_active() ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p><strong>Ahcene Babori Academic</strong> needs the plugin <strong>Ahcene Babori Core</strong>. Please install and activate it (Plugins → Add New → Upload).</p></div>';
		}
	);
	add_filter(
		'template_include',
		static function ( $tpl ) {
			return ABT_DIR . '/inc/plugin-missing.php';
		},
		99
	);
	return; // Nothing else can work without the plugin.
}

require_once ABT_DIR . '/inc/template-tags.php';
require_once ABT_DIR . '/inc/palettes.php';
require_once ABT_DIR . '/inc/mega-menu.php';

/* --------------------------------------------------------------- assets */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$ver = ABT_VERSION;
		wp_enqueue_style( 'abt-main', ABT_URI . '/assets/css/main.css', array(), $ver );
		wp_enqueue_script( 'abt-main', ABT_URI . '/assets/js/main.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script(
			'abt-main',
			'abtI18n',
			array(
				'copied' => abc_t( 'copied' ),
				'copy'   => abc_t( 'copy' ),
			)
		);
	}
);

// Preload the main Latin font (and the Arabic one on Arabic pages) – the largest render-blocking asset.
add_action(
	'wp_head',
	static function () {
		$f = 'ar' === abc_lang() ? 'almarai-arabic-400-normal.woff2' : 'inter-latin-wght-normal.woff2';
		echo '<link rel="preload" href="' . esc_url( ABT_URI . '/assets/fonts/' . $f ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		echo '<meta name="theme-color" content="' . esc_attr( abt_palettes()[ abt_current_palette() ]['navy'] ) . '">' . "\n";
	},
	1
);

/* ------------------------------------------------- performance cleanups */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

// WebP: generate WebP sub-sizes for new JPEG/PNG uploads when the server supports it.
add_filter(
	'image_editor_output_format',
	static function ( $formats ) {
		if ( function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}
);

// Our content types do not use comments.
add_filter(
	'comments_open',
	static function ( $open, $post_id ) {
		return ( 0 === strpos( (string) get_post_type( $post_id ), 'abc_' ) ) ? false : $open;
	},
	10,
	2
);

/* ----------------------------------------------------------- body class */
add_filter(
	'body_class',
	static function ( $c ) {
		$c[] = 'abt';
		return $c;
	}
);

// Excerpt tweaks.
add_filter( 'excerpt_length', static function () { return 28; } );
add_filter( 'excerpt_more', static function () { return '…'; } );
