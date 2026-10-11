<?php
/**
 * Colour schemes and menu styles. Chosen in the dashboard (Researcher → Appearance); every scheme
 * keeps text/background contrast at WCAG AA or better.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Available colour schemes.
 *
 * @return array id => [ label, vars ]
 */
function abt_palettes() {
	$p = array(
		'teal'     => array( 'Teal Navy (default)', '#0b1f3a', '#13294b', '#1d3a63', '#0b7a7c', '#075f61', '#5ad1d3', '#e3f3f3', '#eef3f6', '#0f3a5c' ),
		'indigo'   => array( 'Indigo & Amber', '#161a45', '#1f2560', '#2b3380', '#3a3fa3', '#2b2f85', '#f4c15d', '#e8e9fa', '#eff0fa', '#2a2f7a' ),
		'emerald'  => array( 'Emerald & Gold', '#0c2f26', '#114034', '#1a5646', '#0f6b4f', '#0a5640', '#e6c76b', '#e0f2ea', '#eef5f1', '#145a45' ),
		'burgundy' => array( 'Burgundy & Gold', '#4a1426', '#5e1a31', '#78243f', '#8a1f43', '#6e1835', '#e9c46a', '#f7e4ea', '#f8eef1', '#6b1d36' ),
		'ocean'    => array( 'Ocean Blue & Sky', '#0a2540', '#0f3358', '#16477a', '#1565c0', '#0f4f9a', '#7cc7f5', '#e1eefb', '#edf3fa', '#124a82' ),
		'slate'    => array( 'Slate & Coral', '#1c2433', '#252f43', '#34415c', '#0e7490', '#0b5a70', '#ff9b7a', '#e0f1f5', '#eef2f6', '#2c3a55' ),
	);
	$out = array();
	foreach ( $p as $id => $v ) {
		$out[ $id ] = array(
			'label'   => $v[0],
			'navy'    => $v[1],
			'navy2'   => $v[2],
			'navy3'   => $v[3],
			'primary' => $v[4],
			'pdark'   => $v[5],
			'accent'  => $v[6],
			'soft'    => $v[7],
			'tint'    => $v[8],
			'hero'    => $v[9],
		);
	}
	return $out;
}
add_filter( 'abc_palettes', 'abt_palettes' );

/**
 * Menu styles.
 */
function abt_menu_styles() {
	return array(
		'light'  => 'Light (white bar)',
		'dark'   => 'Dark (colour of the scheme)',
		'accent' => 'Accent (coloured menu row)',
	);
}
add_filter( 'abc_menu_styles', 'abt_menu_styles' );

/**
 * Selected palette id / menu style (administrators can preview with ?abc_palette=…&abc_menu=…).
 */
function abt_current_palette() {
	$id = (string) abc_setting( 'palette', 'teal' );
	if ( isset( $_GET['abc_palette'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$id = sanitize_key( wp_unslash( $_GET['abc_palette'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	return isset( abt_palettes()[ $id ] ) ? $id : 'teal';
}

function abt_current_menu_style() {
	$id = (string) abc_setting( 'menu_style', 'light' );
	if ( isset( $_GET['abc_menu'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$id = sanitize_key( wp_unslash( $_GET['abc_menu'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	return isset( abt_menu_styles()[ $id ] ) ? $id : 'light';
}

/** "r,g,b" of a hex colour. */
function abt_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );
	return hexdec( substr( $hex, 0, 2 ) ) . ',' . hexdec( substr( $hex, 2, 2 ) ) . ',' . hexdec( substr( $hex, 4, 2 ) );
}

/** Print the colour variables of the chosen scheme. */
add_action(
	'wp_head',
	static function () {
		$id = abt_current_palette();
		if ( 'teal' === $id ) {
			return; // Defaults live in main.css.
		}
		$p   = abt_palettes()[ $id ];
		$css = ':root{--navy:' . $p['navy'] . ';--navy-2:' . $p['navy2'] . ';--navy-3:' . $p['navy3'] . ';--teal:' . $p['primary'] . ';--teal-dark:' . $p['pdark'] . ';--teal-light:' . $p['accent'] . ';--teal-soft:' . $p['soft'] . ';--tint:' . $p['tint'] . ';--hero-end:' . $p['hero'] . ';--accent-rgb:' . abt_rgb( $p['accent'] ) . ';--navy-rgb:' . abt_rgb( $p['navy'] ) . '}';
		echo '<style id="abt-palette">' . wp_strip_all_tags( $css ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	20
);

add_filter(
	'body_class',
	static function ( $c ) {
		$c[] = 'abt-menu-' . abt_current_menu_style();
		return $c;
	}
);
