<?php
/**
 * Polylang integration (optional – the plugin works without it, in one language).
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Polylang {

	/**
	 * Hook.
	 */
	public static function init() {
		add_filter( 'pll_get_post_types', array( __CLASS__, 'post_types' ), 10, 2 );
		add_filter( 'pll_get_taxonomies', array( __CLASS__, 'taxonomies' ), 10, 2 );
		add_filter( 'pll_copy_post_metas', array( __CLASS__, 'copy_metas' ), 10, 2 );
		add_filter( 'language_attributes', array( __CLASS__, 'language_attributes' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
	}

	/**
	 * Make our post types translatable.
	 */
	public static function post_types( $types, $is_settings ) {
		foreach ( array_merge( array_keys( ABC_Post_Types::definitions() ), array( 'abc_career' ) ) as $pt ) {
			$types[ $pt ] = $pt;
		}
		return $types;
	}

	/**
	 * Make our taxonomies translatable (the numeric Year taxonomy is language-neutral).
	 */
	public static function taxonomies( $taxes, $is_settings ) {
		foreach ( array( 'abc_pub_type', 'abc_pres_type', 'abc_research_area', 'abc_keyword', 'abc_service_type', 'abc_teaching_section', 'abc_note_category', 'abc_note_tag', 'abc_career_section' ) as $t ) {
			$taxes[ $t ] = $t;
		}
		return $taxes;
	}

	/**
	 * When a translation is created, pre-fill the language-neutral fields from the source.
	 */
	public static function copy_metas( $keys, $sync ) {
		foreach ( array_merge( array_keys( ABC_Post_Types::definitions() ), array( 'abc_career' ) ) as $pt ) {
			foreach ( array_keys( ABC_Fields::fields_for( $pt ) ) as $k ) {
				$keys[] = 'abc_' . $k;
			}
		}
		return array_unique( $keys );
	}

	/**
	 * Guarantee lang + dir on <html>, whatever the WordPress locale files say.
	 */
	public static function language_attributes( $out ) {
		if ( is_admin() ) {
			return $out;
		}
		$lang = abc_lang();
		$out  = preg_replace( '/\s*(lang|dir)="[^"]*"/', '', $out );
		return trim( $out ) . ' lang="' . esc_attr( $lang ) . '" dir="' . ( 'ar' === $lang ? 'rtl' : 'ltr' ) . '"';
	}

	/**
	 * Body classes.
	 */
	public static function body_class( $classes ) {
		$classes[] = 'abc-lang-' . abc_lang();
		if ( 'ar' === abc_lang() ) {
			$classes[] = 'abc-rtl';
		}
		return $classes;
	}

	/**
	 * Data for the EN | FR | AR switcher.
	 *
	 * @return array[] code,name,url,current
	 */
	public static function switcher() {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return array();
		}
		$raw = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0 ) );
		$out = array();
		foreach ( abc_languages() as $code ) {
			foreach ( (array) $raw as $l ) {
				if ( abc_norm_lang( $l['slug'] ) === $code && $l['slug'] === $code ) {
					$out[] = array(
						'code'    => $code,
						'name'    => $l['name'],
						'url'     => $l['url'],
						'current' => ! empty( $l['current_lang'] ),
					);
				}
			}
		}
		return $out;
	}

	/**
	 * Gentle reminder to install Polylang.
	 */
	public static function notice() {
		if ( function_exists( 'pll_languages_list' ) || ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'toplevel_page_abc-researcher' ), true ) ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>Ahcene Babori Core:</strong> install and activate the free <em>Polylang</em> plugin to publish the website in English, French and Arabic (see <code>language-setup.md</code>).</p></div>';
	}
}
