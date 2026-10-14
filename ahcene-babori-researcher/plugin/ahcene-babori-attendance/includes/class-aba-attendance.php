<?php
/**
 * Student attendance by scanning the student card (QR code / barcode) with the phone camera.
 * Teacher-only: nothing is public. Records live in a private post type and are managed
 * under Researcher → Attendance (filter, CSV export, delete).
 *
 * @package AhceneBaboriAttendance
 */

defined( 'ABSPATH' ) || exit;

class ABA_Attendance {

	const CPT = 'abc_attendance';

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		// Attach the button to the course cards and course pages of the Researcher theme/plugin.
		add_filter( 'abc_card_extra', array( __CLASS__, 'card_extra' ), 10, 2 );
		add_action( 'abc_teaching_single_actions', array( __CLASS__, 'single_extra' ) );
		add_shortcode( 'attendance_button', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_init', array( __CLASS__, 'activate' ) ); // Creates the phone page after an update too (idempotent).
		add_shortcode( 'attendance_scanner', array( __CLASS__, 'scanner_shortcode' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'dialog' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_abc_att_export', array( __CLASS__, 'export' ) );
		add_action( 'admin_post_abc_att_delete', array( __CLASS__, 'delete' ) );
		add_action( 'admin_post_abc_att_roster', array( __CLASS__, 'save_roster' ) );
	}

	/** Filter: append the button to a teaching card. */
	public static function card_extra( $html, $post ) {
		if ( $post && 'abc_teaching' === $post->post_type ) {
			$html .= self::button( $post->ID );
		}
		return $html;
	}

	/** Action: button on a single course page. */
	public static function single_extra( $post_id ) {
		$b = self::button( $post_id );
		if ( $b ) {
			echo '<p>' . $b . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	/** [attendance_button id="123"] for any other page. Needs the scanner assets, so the page must be a course page. */
	public static function shortcode( $atts ) {
		$a = shortcode_atts( array( 'id' => get_the_ID() ), $atts );
		return self::button( absint( $a['id'] ) );
	}

	/** Only the site owner/teacher may take or read attendance. */
	public static function can() {
		return current_user_can( 'manage_options' );
	}

	/** Is the scanner needed on this request? */
	private static function needed() {
		$types = apply_filters( 'aba_post_types', array( 'abc_teaching' ) );
		return self::can() && ( is_post_type_archive( $types ) || is_singular( $types ) || self::is_scanner_page() );
	}

	/** Is this request the phone scanner page (a page holding [attendance_scanner])? */
	private static function is_scanner_page() {
		$p = is_page() ? get_queried_object() : null;
		return $p instanceof WP_Post && has_shortcode( $p->post_content, 'attendance_scanner' );
	}

	/** [attendance_scanner]: login for the teacher on a phone, then the list of courses with their scan buttons. */
	public static function scanner_shortcode() {
		if ( ! self::can() ) {
			$out  = '<div class="aba-login"><p>' . esc_html( aba_t( 'att_login' ) ) . '</p>';
			$out .= wp_login_form( array( 'echo' => false, 'redirect' => get_permalink() ) );
			return $out . '</div>';
		}
		$posts = get_posts( array( 'post_type' => apply_filters( 'aba_post_types', array( 'abc_teaching' ) ), 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => true ) );
		if ( ! $posts ) {
			return '<p>' . esc_html( aba_t( 'att_nocourse' ) ) . '</p>';
		}
		$out = '<ul class="aba-list">';
		foreach ( $posts as $p ) {
			$out .= '<li><span class="aba-list__t">' . esc_html( get_the_title( $p ) ) . '</span>' . self::button( $p->ID ) . '</li>';
		}
		return $out . '</ul>';
	}

	/** Create the private-use scanner page once (not indexed, not in menus). */
	public static function activate() {
		if ( get_option( 'aba_page_id' ) && 'page' === get_post_type( (int) get_option( 'aba_page_id' ) ) ) {
			return;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Attendance',
				'post_name'    => 'attendance',
				'post_content' => '[attendance_scanner]',
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_option( 'aba_page_id', (int) $id, false );
		}
	}

	public static function noindex( $robots ) {
		if ( self::is_scanner_page() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}

	/** URL of the phone scanner page ('' if missing). */
	public static function scanner_url() {
		$id = (int) get_option( 'aba_page_id' );
		return ( $id && 'publish' === get_post_status( $id ) ) ? get_permalink( $id ) : '';
	}

	public static function register() {
		register_post_type(
			self::CPT,
			array(
				'label'               => 'Attendance',
				'public'              => false,
				'show_ui'             => false,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'rewrite'             => false,
				'supports'            => array( 'title' ),
			)
		);
	}

	/* ------------------------------------------------------------ helpers */

	private static function valid_date( $d ) {
		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', (string) $d, $m ) || ! checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
			return '';
		}
		return $d;
	}

	private static function clean_code( $c ) {
		$c = sanitize_text_field( wp_unslash( (string) $c ) );
		return substr( trim( $c ), 0, 100 );
	}

	/** Optional roster: option text "code,Full name" per line. */
	public static function roster() {
		static $map = null;
		if ( null === $map ) {
			$map = array();
			foreach ( preg_split( '/\R/', (string) get_option( 'abc_roster', '' ) ) as $line ) {
				$p = array_map( 'trim', explode( ',', $line, 2 ) );
				if ( ! empty( $p[0] ) && ! empty( $p[1] ) ) {
					$map[ $p[0] ] = $p[1];
				}
			}
		}
		return $map;
	}

	private static function name_of( $code ) {
		$r = self::roster();
		return isset( $r[ $code ] ) ? $r[ $code ] : '';
	}

	private static function query( $module, $date, $limit = -1 ) {
		$meta = array( 'relation' => 'AND' );
		if ( $module ) {
			$meta[] = array( 'key' => 'abc_att_module', 'value' => (int) $module );
		}
		if ( $date ) {
			$meta[] = array( 'key' => 'abc_att_date', 'value' => $date );
		}
		return get_posts(
			array(
				'post_type'        => self::CPT,
				'post_status'      => 'any',
				'posts_per_page'   => $limit,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => true,
				'meta_query'       => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
	}

	/* --------------------------------------------------------------- REST */

	public static function routes() {
		$perm = array( __CLASS__, 'can' );
		register_rest_route(
			'aba/v1',
			'/attendance',
			array(
				array(
					'methods'             => 'POST',
					'permission_callback' => $perm,
					'callback'            => array( __CLASS__, 'rest_record' ),
				),
				array(
					'methods'             => 'GET',
					'permission_callback' => $perm,
					'callback'            => array( __CLASS__, 'rest_summary' ),
				),
			)
		);
	}

	private static function summary( $module, $date ) {
		$rows   = self::query( $module, $date, 8 );
		$recent = array();
		foreach ( $rows as $p ) {
			$code     = (string) get_post_meta( $p->ID, 'abc_att_code', true );
			$recent[] = array( 'code' => $code, 'name' => self::name_of( $code ), 'time' => get_the_date( 'H:i', $p ) );
		}
		return array( 'count' => count( self::query( $module, $date ) ), 'recent' => $recent );
	}

	public static function rest_summary( WP_REST_Request $r ) {
		$module = absint( $r->get_param( 'module' ) );
		$date   = self::valid_date( $r->get_param( 'date' ) );
		return rest_ensure_response( self::summary( $module, $date ? $date : wp_date( 'Y-m-d' ) ) );
	}

	public static function rest_record( WP_REST_Request $r ) {
		$module = absint( $r->get_param( 'module' ) );
		$code   = self::clean_code( $r->get_param( 'code' ) );
		$date   = self::valid_date( $r->get_param( 'date' ) );
		$date   = $date ? $date : wp_date( 'Y-m-d' );
		if ( ! $module || ! in_array( get_post_type( $module ), apply_filters( 'aba_post_types', array( 'abc_teaching' ) ), true ) || '' === $code ) {
			return new WP_Error( 'abc_att_bad', 'Invalid request.', array( 'status' => 400 ) );
		}
		$key = md5( $module . '|' . $date . '|' . $code );
		$dup = get_posts(
			array(
				'post_type'        => self::CPT,
				'post_status'      => 'any',
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'suppress_filters' => true,
				'meta_key'         => 'abc_att_key', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		$status = 'duplicate';
		if ( ! $dup ) {
			$id = wp_insert_post(
				array(
					'post_type'   => self::CPT,
					'post_status' => 'private',
					'post_title'  => $code,
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				return new WP_Error( 'abc_att_fail', 'Could not save.', array( 'status' => 500 ) );
			}
			update_post_meta( $id, 'abc_att_code', $code );
			update_post_meta( $id, 'abc_att_module', $module );
			update_post_meta( $id, 'abc_att_date', $date );
			update_post_meta( $id, 'abc_att_key', $key );
			$status = 'recorded';
		}
		return rest_ensure_response( array( 'status' => $status, 'code' => $code, 'name' => self::name_of( $code ) ) + self::summary( $module, $date ) );
	}

	/* ----------------------------------------------------------- front end */

	public static function assets() {
		if ( ! self::needed() ) {
			return;
		}
		wp_enqueue_style( 'aba-attendance', ABA_URL . 'assets/attendance.css', array(), ABA_VERSION );
		wp_enqueue_script( 'abc-jsqr', ABA_URL . 'assets/vendor/jsQR.js', array(), '1.4.0', true );
		wp_enqueue_script( 'aba-attendance', ABA_URL . 'assets/attendance.js', array( 'abc-jsqr' ), ABA_VERSION, true );
		$keys = array( 'att_title', 'att_date', 'att_start', 'att_stop', 'att_close', 'att_manual', 'att_add', 'att_ok', 'att_dup', 'att_err', 'att_nocam', 'att_count', 'att_hint', 'att_recent' );
		$i18n = array();
		foreach ( $keys as $k ) {
			$i18n[ $k ] = aba_t( $k );
		}
		wp_localize_script(
			'aba-attendance',
			'abaAtt',
			array(
				'endpoint' => esc_url_raw( rest_url( 'aba/v1/attendance' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'today'    => wp_date( 'Y-m-d' ),
				'i18n'     => $i18n,
			)
		);
	}

	/** Button shown to the teacher only, on a teaching entry. */
	public static function button( $post_id ) {
		if ( ! self::can() ) {
			return '';
		}
		return sprintf(
			'<button type="button" class="aba-btn aba-att-open" data-module="%d" data-title="%s"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3M4 12h16"/></svg> %s</button>',
			(int) $post_id,
			esc_attr( get_the_title( $post_id ) ),
			esc_html( aba_t( 'att_take' ) )
		);
	}

	public static function dialog() {
		if ( ! self::needed() ) {
			return;
		}
		?>
		<dialog id="aba-att" class="aba-att" aria-labelledby="aba-att-title">
			<div class="aba-att__head">
				<h2 id="aba-att-title"><?php echo esc_html( aba_t( 'att_title' ) ); ?></h2>
				<button type="button" class="aba-att__x" data-att-close aria-label="<?php echo esc_attr( aba_t( 'att_close' ) ); ?>">&times;</button>
			</div>
			<p class="aba-att__module" id="aba-att-module"></p>
			<label class="aba-att__date"><?php echo esc_html( aba_t( 'att_date' ) ); ?>
				<input type="date" id="aba-att-date">
			</label>
			<div class="aba-att__cam">
				<video id="aba-att-video" playsinline muted></video>
				<span class="aba-att__frame" aria-hidden="true"></span>
			</div>
			<p class="aba-att__hint"><?php echo esc_html( aba_t( 'att_hint' ) ); ?></p>
			<div class="aba-att__actions">
				<button type="button" class="aba-btn" id="aba-att-toggle"><?php echo esc_html( aba_t( 'att_start' ) ); ?></button>
			</div>
			<p class="aba-att__status" id="aba-att-status" role="status" aria-live="polite"></p>
			<form class="aba-att__manual" id="aba-att-form" autocomplete="off">
				<label for="aba-att-code"><?php echo esc_html( aba_t( 'att_manual' ) ); ?></label>
				<span><input type="text" id="aba-att-code" inputmode="text" maxlength="100"><button type="submit" class="aba-btn"><?php echo esc_html( aba_t( 'att_add' ) ); ?></button></span>
			</form>
			<p class="aba-att__count"><?php echo esc_html( aba_t( 'att_count' ) ); ?>: <strong id="aba-att-count">0</strong></p>
			<h3 class="aba-att__sub"><?php echo esc_html( aba_t( 'att_recent' ) ); ?></h3>
			<ol class="aba-att__recent" id="aba-att-recent"></ol>
		</dialog>
		<?php
	}

	/* --------------------------------------------------------------- admin */

	public static function menu() {
		add_menu_page( 'Attendance', 'Attendance', 'manage_options', 'abc-attendance', array( __CLASS__, 'render_page' ), 'dashicons-id', 4 );
	}

	private static function teaching_posts() {
		return get_posts( array( 'post_type' => 'abc_teaching', 'post_status' => 'any', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => true ) );
	}

	public static function render_page() {
		if ( ! self::can() ) {
			return;
		}
		$module = isset( $_GET['module'] ) ? absint( $_GET['module'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$date   = isset( $_GET['date'] ) ? self::valid_date( sanitize_text_field( wp_unslash( $_GET['date'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$mods   = self::teaching_posts();
		$rows   = self::query( $module, $date, 500 );
		echo '<div class="wrap"><h1>Attendance</h1>';
		if ( isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>Done.</p></div>';
		}
		$url = self::scanner_url();
		if ( $url ) {
			echo '<p><strong>On your phone:</strong> open <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $url ) . '</a>, log in once, then use the browser menu → <em>Add to Home screen</em>. Each course has its own scan button there.</p>';
		}
		echo '<p>Attendance is recorded from the public <strong>Teaching</strong> page: while logged in, each course shows a <em>Take attendance</em> button that scans student cards with the phone camera. Records are private (never shown to visitors). Camera access needs HTTPS.</p>';
		echo '<form method="get" style="margin:12px 0"><input type="hidden" name="page" value="aba-attendance"><select name="module"><option value="0">All modules</option>';
		foreach ( $mods as $m ) {
			printf( '<option value="%d"%s>%s</option>', (int) $m->ID, selected( $module, $m->ID, false ), esc_html( get_the_title( $m ) ) );
		}
		echo '</select> <input type="date" name="date" value="' . esc_attr( $date ) . '"> <button class="button">Filter</button>';
		$q = array( 'action' => 'abc_att_export', 'module' => $module, 'date' => $date );
		echo ' <a class="button" href="' . esc_url( wp_nonce_url( add_query_arg( $q, admin_url( 'admin-post.php' ) ), 'abc_att_export' ) ) . '">Export CSV</a></form>';
		echo '<p><strong>' . count( $rows ) . '</strong> record(s).</p>';
		echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Card code</th><th>Name (roster)</th><th>Module</th><th>Date</th><th>Time</th><th></th></tr></thead><tbody>';
		foreach ( $rows as $p ) {
			$code = (string) get_post_meta( $p->ID, 'abc_att_code', true );
			$del  = wp_nonce_url( add_query_arg( array( 'action' => 'abc_att_delete', 'id' => $p->ID ), admin_url( 'admin-post.php' ) ), 'abc_att_delete_' . $p->ID );
			echo '<tr><td>' . esc_html( $code ) . '</td><td>' . esc_html( self::name_of( $code ) ) . '</td><td>' . esc_html( get_the_title( (int) get_post_meta( $p->ID, 'abc_att_module', true ) ) ) . '</td><td>' . esc_html( (string) get_post_meta( $p->ID, 'abc_att_date', true ) ) . '</td><td>' . esc_html( get_the_date( 'H:i', $p ) ) . '</td><td><a href="' . esc_url( $del ) . '" onclick="return confirm(\'Delete this record?\')">Delete</a></td></tr>';
		}
		if ( ! $rows ) {
			echo '<tr><td colspan="6">No records yet.</td></tr>';
		}
		echo '</tbody></table>';
		echo '<h2 style="margin-top:2em">Student roster (optional)</h2><p>One student per line: <code>card code,Full name</code>. Used only to show names next to scanned codes. Keep this list confidential and remove it when the course ends.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="abc_att_roster">';
		wp_nonce_field( 'abc_att_roster' );
		echo '<textarea name="roster" rows="8" style="width:100%;max-width:560px" spellcheck="false">' . esc_textarea( (string) get_option( 'abc_roster', '' ) ) . '</textarea><p><button class="button button-primary">Save roster</button></p></form></div>';
	}

	public static function save_roster() {
		if ( ! self::can() ) {
			wp_die( 'Not allowed.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'abc_att_roster' );
		$txt = isset( $_POST['roster'] ) ? sanitize_textarea_field( wp_unslash( $_POST['roster'] ) ) : '';
		update_option( 'abc_roster', $txt, false );
		wp_safe_redirect( admin_url( 'admin.php?page=aba-attendance&done=1' ) );
		exit;
	}

	public static function delete() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! self::can() ) {
			wp_die( 'Not allowed.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'abc_att_delete_' . $id );
		if ( $id && self::CPT === get_post_type( $id ) ) {
			wp_delete_post( $id, true );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=aba-attendance&done=1' ) );
		exit;
	}

	public static function export() {
		if ( ! self::can() ) {
			wp_die( 'Not allowed.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'abc_att_export' );
		$module = isset( $_GET['module'] ) ? absint( $_GET['module'] ) : 0;
		$date   = isset( $_GET['date'] ) ? self::valid_date( sanitize_text_field( wp_unslash( $_GET['date'] ) ) ) : '';
		$rows   = self::query( $module, $date );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="attendance-' . gmdate( 'Ymd-His' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'Card code', 'Name', 'Module', 'Date', 'Time' ) );
		$safe = static function ( $v ) {
			$v = (string) $v;
			return preg_match( '/^[=+\-@\t\r]/', $v ) ? "'" . $v : $v; // CSV-injection guard.
		};
		foreach ( $rows as $p ) {
			$code = (string) get_post_meta( $p->ID, 'abc_att_code', true );
			fputcsv( $out, array( $safe( $code ), $safe( self::name_of( $code ) ), $safe( get_the_title( (int) get_post_meta( $p->ID, 'abc_att_module', true ) ) ), get_post_meta( $p->ID, 'abc_att_date', true ), get_the_date( 'H:i', $p ) ) );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}

