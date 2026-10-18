<?php
/**
 * First-party statistics: no cookies, no IP stored, no external service.
 * A tiny beacon posts page views / share clicks to a REST route; counts are kept per day.
 * Visitors are counted with a one-day, salted hash held in a short-lived transient (never in the stats table).
 *
 * @package AhceneBaboriInsights
 */

defined( 'ABSPATH' ) || exit;

class ABI_Stats {

	const DBV = '1';

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'abi_hits';
	}

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'admin_post_abi_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_abi_reset', array( __CLASS__, 'reset' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_css' ) );
		foreach ( array( 'abc_note', 'abc_publication', 'post', 'page' ) as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( __CLASS__, 'columns' ) );
			add_action( "manage_{$pt}_posts_custom_column", array( __CLASS__, 'column' ), 10, 2 );
		}
	}

	/* ------------------------------------------------------------ install */

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$t       = self::table();
		$charset = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$t} (
			day date NOT NULL,
			type varchar(10) NOT NULL,
			item varchar(191) NOT NULL,
			views int(10) unsigned NOT NULL DEFAULT 0,
			visitors int(10) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (day,type,item)
		) {$charset};"
		);
		update_option( 'abi_db_version', self::DBV, false );
	}

	public static function maybe_install() {
		if ( get_option( 'abi_db_version' ) !== self::DBV ) {
			self::install();
		}
	}

	/* ---------------------------------------------------------- recording */

	public static function routes() {
		register_rest_route(
			'abi/v1',
			'/hit',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'hit' ),
			)
		);
	}

	private static function is_bot( $ua ) {
		return '' === $ua || preg_match( '/bot|crawl|spider|slurp|bing|yandex|baidu|facebookexternalhit|preview|monitor|uptime|lighthouse|headless|curl|wget|python|java|go-http|scrapy/i', $ua );
	}

	private static function bump( $type, $item, $views, $visitors ) {
		global $wpdb;
		$t   = self::table();
		$day = wp_date( 'Y-m-d' );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$t} (day,type,item,views,visitors) VALUES (%s,%s,%s,0,0)", $day, $type, $item ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$t} SET views = views + %d, visitors = visitors + %d WHERE day = %s AND type = %s AND item = %s", $views, $visitors, $day, $type, $item ) );
		// phpcs:enable
	}

	public static function hit( WP_REST_Request $r ) {
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( self::is_bot( $ua ) || ( isset( $_SERVER['HTTP_DNT'] ) && '1' === $_SERVER['HTTP_DNT'] ) || empty( abi_settings()['track'] ) ) {
			return rest_ensure_response( array( 'ok' => 1 ) );
		}
		$type = 'share' === $r->get_param( 'type' ) ? 'share' : 'view';
		$post = absint( $r->get_param( 'post' ) );
		if ( $post && ( 'publish' !== get_post_status( $post ) || ! is_post_type_viewable( get_post_type( $post ) ) ) ) {
			$post = 0;
		}
		$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$hash = substr( md5( wp_date( 'Y-m-d' ) . '|' . $ip . '|' . $ua . '|' . wp_salt( 'auth' ) ), 0, 20 );

		if ( 'share' === $type ) {
			$net = sanitize_key( (string) $r->get_param( 'net' ) );
			if ( ! $post || ! isset( ABI_Share::networks()[ $net ] ) || get_transient( 'abi_s_' . $hash . $post . $net ) ) {
				return rest_ensure_response( array( 'ok' => 1 ) );
			}
			set_transient( 'abi_s_' . $hash . $post . $net, 1, 5 * MINUTE_IN_SECONDS );
			self::bump( 'share', $net, 1, 0 );
			self::bump( 'pshare', (string) $post, 1, 0 );
			update_post_meta( $post, 'abi_shares', (int) get_post_meta( $post, 'abi_shares', true ) + 1 );
			return rest_ensure_response( array( 'ok' => 1 ) );
		}

		// Page view: the same visitor reloading within 30 minutes counts once.
		if ( get_transient( 'abi_p_' . $hash . $post ) ) {
			return rest_ensure_response( array( 'ok' => 1 ) );
		}
		set_transient( 'abi_p_' . $hash . $post, 1, 30 * MINUTE_IN_SECONDS );
		$new_visitor = 0;
		if ( ! get_transient( 'abi_u_' . $hash ) ) {
			set_transient( 'abi_u_' . $hash, 1, DAY_IN_SECONDS );
			$new_visitor = 1;
		}
		self::bump( 'site', '0', 1, $new_visitor );
		if ( $post ) {
			self::bump( 'page', (string) $post, 1, 0 );
			update_post_meta( $post, 'abi_views', (int) get_post_meta( $post, 'abi_views', true ) + 1 );
		}
		// External referrer host only (never the full URL).
		$ref  = esc_url_raw( (string) $r->get_param( 'ref' ), array( 'http', 'https' ) );
		$host = strtolower( (string) wp_parse_url( $ref, PHP_URL_HOST ) );
		$own  = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		if ( $host && $host !== $own && substr( $host, -strlen( $own ) - 1 ) !== '.' . $own ) {
			self::bump( 'ref', substr( preg_replace( '/^www\./', '', $host ), 0, 120 ), 1, 0 );
		}
		if ( 1 === wp_rand( 1, 200 ) ) { // Housekeeping: keep ~2 years.
			global $wpdb;
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table() . ' WHERE day < %s', wp_date( 'Y-m-d', time() - 800 * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
		}
		return rest_ensure_response( array( 'ok' => 1 ) );
	}

	/* -------------------------------------------------------------- admin */

	public static function menu() {
		add_menu_page( 'Statistics', 'Statistics', 'manage_options', 'abi-stats', array( __CLASS__, 'render' ), 'dashicons-chart-bar', 5 );
		add_submenu_page( 'abi-stats', 'Sharing & tracking', 'Sharing & tracking', 'manage_options', 'abi-settings', array( __CLASS__, 'render_settings' ) );
	}

	public static function admin_css( $hook ) {
		if ( false !== strpos( (string) $hook, 'abi-' ) ) {
			wp_enqueue_style( 'abi-admin', ABI_URL . 'assets/admin.css', array(), ABI_VERSION );
		}
	}

	private static function rows( $sql, $args ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$days = isset( $_GET['range'] ) ? absint( $_GET['range'] ) : 30; // phpcs:ignore WordPress.Security.NonceVerification
		$days = in_array( $days, array( 7, 30, 90, 365 ), true ) ? $days : 30;
		$t    = self::table();
		$from = wp_date( 'Y-m-d', time() - ( $days - 1 ) * DAY_IN_SECONDS );
		$today = wp_date( 'Y-m-d' );

		$tot = self::rows( "SELECT COALESCE(SUM(views),0) v, COALESCE(SUM(visitors),0) u FROM {$t} WHERE type='site' AND day >= %s", array( $from ) );
		$td  = self::rows( "SELECT COALESCE(SUM(views),0) v, COALESCE(SUM(visitors),0) u FROM {$t} WHERE type='site' AND day = %s", array( $today ) );
		$all = self::rows( "SELECT COALESCE(SUM(views),0) v FROM {$t} WHERE type='site' AND day >= %s", array( '2000-01-01' ) );
		$ser = self::rows( "SELECT day, views, visitors FROM {$t} WHERE type='site' AND day >= %s ORDER BY day", array( $from ) );
		$by  = array();
		foreach ( $ser as $r ) {
			$by[ $r['day'] ] = $r;
		}
		$pages = self::rows( "SELECT item, SUM(views) v FROM {$t} WHERE type='page' AND day >= %s GROUP BY item ORDER BY v DESC LIMIT 15", array( $from ) );
		$refs  = self::rows( "SELECT item, SUM(views) v FROM {$t} WHERE type='ref' AND day >= %s GROUP BY item ORDER BY v DESC LIMIT 10", array( $from ) );
		$shr   = self::rows( "SELECT item, SUM(views) v FROM {$t} WHERE type='share' AND day >= %s GROUP BY item ORDER BY v DESC", array( $from ) );
		$pshr  = self::rows( "SELECT item, SUM(views) v FROM {$t} WHERE type='pshare' AND day >= %s GROUP BY item ORDER BY v DESC LIMIT 10", array( $from ) );

		echo '<div class="wrap abi-wrap"><h1>Statistics</h1>';
		echo '<p class="abi-range">';
		foreach ( array( 7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => '1 year' ) as $d => $l ) {
			printf( '<a class="button%s" href="%s">%s</a> ', $d === $days ? ' button-primary' : '', esc_url( add_query_arg( array( 'page' => 'abi-stats', 'range' => $d ), admin_url( 'admin.php' ) ) ), esc_html( $l ) );
		}
		echo '</p><div class="abi-cards">';
		$card = static function ( $label, $val ) {
			echo '<div class="abi-card"><span>' . esc_html( $label ) . '</span><strong>' . esc_html( number_format_i18n( (int) $val ) ) . '</strong></div>';
		};
		$card( 'Today – views', $td[0]['v'] );
		$card( 'Today – visitors', $td[0]['u'] );
		$card( "Last {$days} days – views", $tot[0]['v'] );
		$card( "Last {$days} days – visitors*", $tot[0]['u'] );
		$card( 'All time – views', $all[0]['v'] );
		echo '</div><p class="description">* Visitors are counted per day (a person returning on another day counts again). No cookies and no IP addresses are stored.</p>';

		// SVG bar chart (views per day).
		$max = 1;
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$d   = wp_date( 'Y-m-d', time() - $i * DAY_IN_SECONDS );
			$max = max( $max, isset( $by[ $d ] ) ? (int) $by[ $d ]['views'] : 0 );
		}
		$w   = 760;
		$h   = 170;
		$bw  = $w / $days;
		echo '<h2>Views per day</h2><svg class="abi-chart" viewBox="0 0 ' . (int) $w . ' ' . ( (int) $h + 22 ) . '" role="img" aria-label="Views per day">';
		for ( $i = 0; $i < $days; $i++ ) {
			$d  = wp_date( 'Y-m-d', time() - ( $days - 1 - $i ) * DAY_IN_SECONDS );
			$v  = isset( $by[ $d ] ) ? (int) $by[ $d ]['views'] : 0;
			$bh = round( ( $v / $max ) * $h );
			printf( '<rect x="%s" y="%s" width="%s" height="%s" rx="2"><title>%s: %d</title></rect>', esc_attr( round( $i * $bw + 1, 2 ) ), esc_attr( $h - $bh ), esc_attr( max( 1, round( $bw - 2, 2 ) ) ), esc_attr( max( $bh, $v ? 1 : 0 ) ), esc_html( $d ), (int) $v );
		}
		echo '<text x="0" y="' . ( (int) $h + 16 ) . '">' . esc_html( $from ) . '</text><text x="' . (int) $w . '" y="' . ( (int) $h + 16 ) . '" text-anchor="end">' . esc_html( $today ) . '</text></svg>';

		echo '<div class="abi-cols"><div><h2>Top pages &amp; articles</h2><table class="widefat striped"><thead><tr><th>Page</th><th>Views</th></tr></thead><tbody>';
		foreach ( $pages as $p ) {
			$id = (int) $p['item'];
			echo '<tr><td><a href="' . esc_url( get_permalink( $id ) ) . '" target="_blank" rel="noopener">' . esc_html( get_the_title( $id ) ) . '</a></td><td>' . esc_html( number_format_i18n( (int) $p['v'] ) ) . '</td></tr>';
		}
		echo $pages ? '' : '<tr><td colspan="2">No data yet. Views appear after visitors open pages.</td></tr>';
		echo '</tbody></table></div><div><h2>Where visitors come from</h2><table class="widefat striped"><thead><tr><th>Website</th><th>Views</th></tr></thead><tbody>';
		foreach ( $refs as $p ) {
			echo '<tr><td>' . esc_html( $p['item'] ) . '</td><td>' . esc_html( number_format_i18n( (int) $p['v'] ) ) . '</td></tr>';
		}
		echo $refs ? '' : '<tr><td colspan="2">No external referrers yet.</td></tr>';
		echo '</tbody></table><h2>Shares by network</h2><table class="widefat striped"><tbody>';
		$nets = ABI_Share::networks();
		foreach ( $shr as $p ) {
			echo '<tr><td>' . esc_html( $nets[ $p['item'] ] ?? $p['item'] ) . '</td><td>' . esc_html( number_format_i18n( (int) $p['v'] ) ) . '</td></tr>';
		}
		echo $shr ? '' : '<tr><td>No share clicks yet.</td></tr>';
		echo '</tbody></table><h2>Most shared articles</h2><table class="widefat striped"><tbody>';
		foreach ( $pshr as $p ) {
			echo '<tr><td>' . esc_html( get_the_title( (int) $p['item'] ) ) . '</td><td>' . esc_html( number_format_i18n( (int) $p['v'] ) ) . '</td></tr>';
		}
		echo $pshr ? '' : '<tr><td>—</td></tr>';
		echo '</tbody></table></div></div></div>';
	}

	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = abi_settings();
		echo '<div class="wrap"><h1>Sharing &amp; tracking</h1>';
		if ( isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>Saved.</p></div>';
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="abi_settings">';
		wp_nonce_field( 'abi_settings' );
		echo '<h2>Share buttons (blog articles)</h2><p>';
		foreach ( ABI_Share::networks() as $id => $label ) {
			printf( '<label style="display:block;margin:4px 0"><input type="checkbox" name="networks[]" value="%s"%s> %s</label>', esc_attr( $id ), checked( in_array( $id, (array) $s['networks'], true ), true, false ), esc_html( $label ) );
		}
		echo '</p><p class="description">Buttons are plain links: nothing is loaded from Facebook, X, etc. until a visitor clicks. Use <code>[abi_share]</code> to place the buttons anywhere.</p>';
		echo '<h2>Statistics</h2>';
		printf( '<p><label><input type="checkbox" name="track" value="1"%s> Count page views and visitors (no cookies, no IP stored; skips bots, logged-in editors and visitors who send Do-Not-Track)</label></p>', checked( ! empty( $s['track'] ), true, false ) );
		printf( '<p><label><input type="checkbox" name="show_views" value="1"%s> Show the view counter under each blog article title</label></p>', checked( ! empty( $s['show_views'] ), true, false ) );
		echo '<p><button class="button button-primary">Save</button></p></form>';
		echo '<hr><h2>Reset statistics</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Delete all statistics? This cannot be undone.\')"><input type="hidden" name="action" value="abi_reset">';
		wp_nonce_field( 'abi_reset' );
		echo '<p><button class="button">Delete all statistics</button></p></form></div>';
	}

	public static function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'abi_settings' );
		$nets = isset( $_POST['networks'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['networks'] ) ) : array();
		$nets = array_values( array_intersect( $nets, array_keys( ABI_Share::networks() ) ) );
		$cur  = abi_settings();
		$cur['networks']   = $nets;
		$cur['track']      = empty( $_POST['track'] ) ? 0 : 1;
		$cur['show_views'] = empty( $_POST['show_views'] ) ? 0 : 1;
		update_option( 'abi_settings', $cur, false );
		wp_safe_redirect( admin_url( 'admin.php?page=abi-settings&done=1' ) );
		exit;
	}

	public static function reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'abi_reset' );
		global $wpdb;
		$wpdb->query( 'TRUNCATE TABLE ' . self::table() ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('abi_views','abi_shares')" ); // phpcs:ignore WordPress.DB
		wp_safe_redirect( admin_url( 'admin.php?page=abi-settings&done=1' ) );
		exit;
	}

	/* ---------------------------------------------------- list columns */

	public static function columns( $cols ) {
		$cols['abi_views']  = 'Views';
		$cols['abi_shares'] = 'Shares';
		return $cols;
	}

	public static function column( $col, $id ) {
		if ( 'abi_views' === $col ) {
			echo esc_html( number_format_i18n( (int) get_post_meta( $id, 'abi_views', true ) ) );
		} elseif ( 'abi_shares' === $col ) {
			echo esc_html( number_format_i18n( (int) get_post_meta( $id, 'abi_shares', true ) ) );
		}
	}
}
