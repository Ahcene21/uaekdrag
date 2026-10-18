<?php
/**
 * Share buttons: plain links (no third-party scripts), copy link and the phone's native share sheet.
 *
 * @package AhceneBaboriInsights
 */

defined( 'ABSPATH' ) || exit;

class ABI_Share {

	public static function init() {
		add_action( 'abc_after_note_content', array( __CLASS__, 'echo_block' ) );
		add_action( 'abc_note_meta', array( __CLASS__, 'echo_views' ) );
		add_action( 'abc_footer_stats', array( __CLASS__, 'echo_visits' ) );
		add_filter( 'abc_card_extra', array( __CLASS__, 'card_views' ), 20, 2 );
		add_shortcode( 'abi_visits', array( __CLASS__, 'visits_shortcode' ) );
		add_shortcode( 'abi_share', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function networks() {
		return array(
			'facebook' => 'Facebook',
			'x'        => 'X',
			'linkedin' => 'LinkedIn',
			'whatsapp' => 'WhatsApp',
			'telegram' => 'Telegram',
			'email'    => 'Email',
			'copy'     => 'Copy link',
			'native'   => 'Phone share menu',
		);
	}

	public static function assets() {
		if ( is_admin() || is_feed() || is_embed() || is_404() ) {
			return;
		}
		wp_enqueue_style( 'abi', ABI_URL . 'assets/insights.css', array(), ABI_VERSION );
		wp_enqueue_script( 'abi', ABI_URL . 'assets/insights.js', array(), ABI_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		$st = abi_settings();
		wp_localize_script(
			'abi',
			'abiCfg',
			array(
				'endpoint' => esc_url_raw( rest_url( 'abi/v1/hit' ) ),
				'post'     => is_singular() ? get_queried_object_id() : 0,
				'track'    => ( ! empty( $st['track'] ) && ! current_user_can( 'edit_posts' ) && ! is_preview() ) ? 1 : 0,
				'copied'   => abi_t( 'copied' ),
			)
		);
	}

	/** Share block HTML. */
	public static function html( $id = 0 ) {
		$id    = $id ? $id : get_the_ID();
		$url   = get_permalink( $id );
		$title = wp_strip_all_tags( get_the_title( $id ) );
		$st    = abi_settings();
		$u     = rawurlencode( $url );
		$t     = rawurlencode( $title );
		$links = array(
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $u,
			'x'        => 'https://twitter.com/intent/tweet?url=' . $u . '&text=' . $t,
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u,
			'whatsapp' => 'https://wa.me/?text=' . $t . '%20' . $u,
			'telegram' => 'https://t.me/share/url?url=' . $u . '&text=' . $t,
			'email'    => 'mailto:?subject=' . $t . '&body=' . $u,
		);
		$labels = self::networks();
		$icons  = require ABI_DIR . 'includes/icons.php';
		$icon   = static function ( $n ) use ( $icons ) {
			list( $color, $path, $stroke ) = $icons[ $n ];
			$attr = $stroke ? 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"' : 'fill="currentColor"';
			return '<svg class="abi-ico" viewBox="0 0 24 24" width="22" height="22" ' . $attr . ' aria-hidden="true" focusable="false"><path d="' . esc_attr( $path ) . '"/></svg>';
		};
		$out    = '<div class="abi-share" data-url="' . esc_url( $url ) . '" data-title="' . esc_attr( $title ) . '"><p class="abi-share__t">' . esc_html( abi_t( 'share_title' ) ) . '</p><ul class="abi-share__list">';
		foreach ( (array) $st['networks'] as $n ) {
			if ( isset( $links[ $n ] ) ) {
				$label = 'email' === $n ? abi_t( 'email' ) : $labels[ $n ];
				$out  .= '<li><a class="abi-btn abi-btn--' . esc_attr( $n ) . '" data-net="' . esc_attr( $n ) . '" href="' . esc_url( $links[ $n ], array( 'https', 'mailto' ) ) . '" target="' . ( 'email' === $n ? '_self' : '_blank' ) . '" rel="noopener noreferrer" aria-label="' . esc_attr( sprintf( abi_t( 'share_on' ), $label ) ) . '" title="' . esc_attr( $label ) . '" style="--c:' . esc_attr( $icons[ $n ][0] ) . '">' . $icon( $n ) . '</a></li>';
			} elseif ( 'copy' === $n ) {
				$out .= '<li><button type="button" class="abi-btn abi-btn--copy" data-net="copy" aria-label="' . esc_attr( abi_t( 'copy' ) ) . '" title="' . esc_attr( abi_t( 'copy' ) ) . '" style="--c:' . esc_attr( $icons['copy'][0] ) . '">' . $icon( 'copy' ) . '</button></li>';
			} elseif ( 'native' === $n ) {
				$out .= '<li><button type="button" class="abi-btn abi-btn--native" data-net="native" aria-label="' . esc_attr( abi_t( 'native' ) ) . '" title="' . esc_attr( abi_t( 'native' ) ) . '" style="--c:' . esc_attr( $icons['native'][0] ) . '" hidden>' . $icon( 'native' ) . '</button></li>';
			}
		}
		return $out . '</ul><span class="abi-share__msg" role="status" aria-live="polite"></span></div>';
	}

	public static function echo_block( $id = 0 ) {
		echo self::html( $id ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public static function shortcode( $atts ) {
		$a = shortcode_atts( array( 'id' => 0 ), $atts );
		return self::html( absint( $a['id'] ) );
	}

	/** " · 123 views" in the article meta line. */
	public static function echo_views( $id = 0 ) {
		$st = abi_settings();
		if ( empty( $st['show_views'] ) ) {
			return;
		}
		$id = $id ? $id : get_the_ID();
		$n  = (int) get_post_meta( $id, 'abi_views', true );
		echo ' · <span class="abi-views">' . esc_html( number_format_i18n( $n ) . ' ' . abi_t( 'views' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/** Total page views of the whole site (cached 5 minutes). */
	public static function total_visits() {
		$n = get_transient( 'abi_total' );
		if ( false === $n ) {
			global $wpdb;
			$n = (int) $wpdb->get_var( 'SELECT COALESCE(SUM(views),0) FROM ' . ABI_Stats::table() . " WHERE type='site'" ); // phpcs:ignore WordPress.DB
			set_transient( 'abi_total', $n, 5 * MINUTE_IN_SECONDS );
		}
		return (int) $n;
	}

	/** Footer: "Site visits: 1,234". */
	public static function echo_visits() {
		$st = abi_settings();
		if ( empty( $st['show_visits'] ) ) {
			return;
		}
		echo '<p class="abi-visits"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg> ' . esc_html( abi_t( 'visits' ) ) . ': <strong>' . esc_html( number_format_i18n( self::total_visits() ) ) . '</strong></p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public static function visits_shortcode() {
		return esc_html( number_format_i18n( self::total_visits() ) );
	}

	/** Article cards: view counter for blog posts. */
	public static function card_views( $html, $post ) {
		$st = abi_settings();
		if ( $post && 'abc_note' === $post->post_type && ! empty( $st['show_views'] ) ) {
			$n     = (int) get_post_meta( $post->ID, 'abi_views', true );
			$html .= '<p class="abi-card-views"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg> ' . esc_html( number_format_i18n( $n ) . ' ' . abi_t( 'views' ) ) . '</p>';
		}
		return $html;
	}
}
