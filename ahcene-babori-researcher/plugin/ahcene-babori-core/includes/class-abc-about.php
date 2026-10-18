<?php
/**
 * The "About me" menu entry always works: whatever the menu item is (page, custom link, "#", a deleted or
 * draft page) and whatever the language, it is pointed at the real About page, which is created on the
 * spot when it does not exist. Old / alias addresses (/about-me/, /a-propos/, /نبذة-عني/ …) redirect there.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_About {

	public static function init() {
		add_filter( 'wp_setup_nav_menu_item', array( __CLASS__, 'menu_item' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'alias_redirect' ), 5 );
	}

	/** Lower-case, tag-free, diacritic-light form of a label for comparison. */
	private static function norm( $s ) {
		$s = html_entity_decode( wp_strip_all_tags( (string) $s ), ENT_QUOTES, 'UTF-8' );
		$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s, 'UTF-8' ) : strtolower( $s );
		$s = str_replace( array( 'أ', 'إ', 'آ', 'ى', 'ة', 'ـ' ), array( 'ا', 'ا', 'ا', 'ي', 'ه', '' ), $s );
		$s = preg_replace( '/[\x{064B}-\x{065F}\x{0670}]/u', '', $s ); // Arabic marks.
		$s = strtr( $s, array( 'à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ô' => 'o', 'î' => 'i', 'ç' => 'c' ) );
		return trim( preg_replace( '/[\s\-_]+/u', ' ', $s ) );
	}

	/** Menu labels that mean "About me". */
	private static function is_about_label( $title ) {
		static $set = null;
		if ( null === $set ) {
			$set = array();
			foreach ( array( 'about', 'about me', 'about us', 'my profile', 'profile', 'a propos', 'a propos de moi', 'mon profil', 'qui suis je', 'نبذه عني', 'نبذه', 'من انا', 'عني', 'نبذه تعريفيه', 'الملف الشخصي' ) as $l ) {
				$set[ self::norm( $l ) ] = true;
			}
			foreach ( array( 'nav_about' ) as $k ) {
				foreach ( array( 'en', 'fr', 'ar' ) as $lg ) {
					$strings = require ABC_DIR . 'includes/strings.php';
					if ( isset( $strings[ $k ][ $lg ] ) ) {
						$set[ self::norm( $strings[ $k ][ $lg ] ) ] = true;
					}
				}
			}
		}
		return isset( $set[ self::norm( $title ) ] );
	}

	/** First path segment(s) that identify an About address. */
	private static function alias_slugs() {
		return array( 'about', 'about-me', 'aboutme', 'about-us', 'my-profile', 'profile', 'a-propos', 'apropos', 'mon-profil', 'nubdha-anni', 'nabdha-anni', 'nubdha', 'who-am-i', 'عني', 'نبذة', 'نبذة-عني', 'نبذه-عني', 'من-أنا', 'من-انا' );
	}

	/** URL of the About page, creating the page if the site has none. */
	public static function url() {
		$url = abc_find_page( 'about' );
		if ( $url ) {
			return $url;
		}
		if ( get_transient( 'abc_about_lock' ) ) {
			return '';
		}
		set_transient( 'abc_about_lock', 1, 5 * MINUTE_IN_SECONDS );
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => abc_t( 'nav_about' ),
				'post_name'    => 'about',
				'post_content' => '[abc_career]',
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			return '';
		}
		update_post_meta( $id, '_abc_page_key', 'about' );
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $id, abc_lang() );
		}
		return (string) get_permalink( $id );
	}

	/** Point About-like menu items (any type) to the About page. */
	public static function menu_item( $item ) {
		if ( is_admin() || empty( $item->title ) ) {
			return $item;
		}
		$type = (string) ( $item->type ?? '' );
		$own  = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		$host = strtolower( (string) wp_parse_url( (string) $item->url, PHP_URL_HOST ) );
		$path = trim( rawurldecode( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ) ), '/' );
		$path = preg_replace( '#^(ar|fr|en)(/|$)#', '', $path );
		$is_alias_path = $path && in_array( strtolower( $path ), array_map( 'strtolower', self::alias_slugs() ), true );

		$candidate = false;
		if ( 'custom' === $type ) {
			$internal  = ( '' === $host || $host === $own || '#' === trim( (string) $item->url ) );
			$candidate = $internal && ( self::is_about_label( $item->title ) || $is_alias_path );
		} elseif ( 'post_type' === $type && 'page' === ( $item->object ?? '' ) ) {
			$st        = get_post_status( (int) $item->object_id );
			$broken    = ! $st || 'publish' !== $st;
			$candidate = $broken && self::is_about_label( $item->title );
		}
		if ( ! $candidate ) {
			return $item;
		}
		$url = self::url();
		if ( $url ) {
			$item->url = $url;
		}
		return $item;
	}

	/** /about-me/, /a-propos/, /نبذة-عني/ … that are not real pages go to the About page. */
	public static function alias_redirect() {
		if ( ! is_404() || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$path = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
		$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( '/' !== $home && 0 === strpos( $path, $home ) ) {
			$path = substr( $path, strlen( $home ) );
		}
		$path = strtolower( trim( rawurldecode( $path ), '/' ) );
		$path = preg_replace( '#^(ar|fr|en)(/|$)#', '', $path );
		if ( $path && in_array( $path, array_map( 'strtolower', self::alias_slugs() ), true ) ) {
			$url = self::url();
			if ( $url && trailingslashit( $url ) !== trailingslashit( home_url( '/' . $path ) ) ) {
				wp_safe_redirect( $url, 302 );
				exit;
			}
		}
	}
}
