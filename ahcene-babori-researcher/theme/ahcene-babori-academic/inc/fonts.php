<?php
/**
 * Arabic font choice (bundled, no external requests). Administrators can preview with ?abc_font=cairo.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;

/** Registry: id => label, family, files (weight => file). */
function abt_ar_fonts() {
	return apply_filters(
		'abt_ar_fonts',
		array(
			'tajawal' => array( 'label' => 'Tajawal', 'family' => 'ABT Arabic', 'files' => array( 400 => 'tajawal-arabic-400-normal.woff2', 500 => 'tajawal-arabic-500-normal.woff2', 700 => 'tajawal-arabic-700-normal.woff2', 800 => 'tajawal-arabic-800-normal.woff2' ) ),
			'cairo'   => array( 'label' => 'Cairo', 'family' => 'ABT Arabic', 'files' => array( 400 => 'cairo-arabic-400-normal.woff2', 700 => 'cairo-arabic-700-normal.woff2' ) ),
			'plex'    => array( 'label' => 'IBM Plex Sans Arabic', 'family' => 'ABT Arabic', 'files' => array( 400 => 'ibm-plex-sans-arabic-arabic-400-normal.woff2', 700 => 'ibm-plex-sans-arabic-arabic-700-normal.woff2' ) ),
			'almarai' => array( 'label' => 'Almarai', 'family' => 'Almarai', 'files' => array() ), // Declared in main.css.
		)
	);
}

function abt_current_ar_font() {
	$id = (string) abc_setting( 'ar_font', 'tajawal' );
	if ( isset( $_GET['abc_font'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$id = sanitize_key( wp_unslash( $_GET['abc_font'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	return isset( abt_ar_fonts()[ $id ] ) ? $id : 'tajawal';
}

add_action(
	'wp_head',
	static function () {
		if ( 'ar' !== abc_lang() ) {
			return;
		}
		$f   = abt_ar_fonts()[ abt_current_ar_font() ];
		$css = '';
		foreach ( $f['files'] as $w => $file ) {
			$css .= '@font-face{font-family:"ABT Arabic";font-style:normal;font-weight:' . (int) $w . ';font-display:swap;src:url("' . esc_url( ABT_URI . '/assets/fonts/' . $file ) . '") format("woff2");unicode-range:U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE70-FE74,U+FE76-FEFC}';
		}
		$css .= 'html[lang="ar"]{--font:"' . $f['family'] . '","Almarai","Noto Sans Arabic","Inter",system-ui,"Segoe UI",Tahoma,Arial,sans-serif}';
		if ( ! empty( $f['files'][400] ) ) {
			echo '<link rel="preload" href="' . esc_url( ABT_URI . '/assets/fonts/' . $f['files'][400] ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		}
		echo '<style id="abt-ar-font">' . wp_strip_all_tags( $css ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	20
);
