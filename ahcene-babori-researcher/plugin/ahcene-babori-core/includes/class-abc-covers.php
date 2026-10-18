<?php
/**
 * Publication covers: render the first page of an uploaded PDF in the browser (bundled PDF.js,
 * no server software needed), save it as an image and use it on the publications page.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Covers {

	public static function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( __CLASS__, 'menu' ), 30 );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		}
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'auto_assets' ) );
	}

	/** Owner visiting the publications page: missing covers are created in the background, then the page refreshes. */
	public static function auto_assets() {
		if ( ! is_post_type_archive( 'abc_publication' ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$todo = array_values( array_filter( self::items(), static function ( $i ) {
			return $i['hasPdf'] && ! $i['cover'];
		} ) );
		if ( ! $todo ) {
			return;
		}
		wp_enqueue_script( 'abc-pdfjs', ABC_URL . 'assets/vendor/pdfjs/pdf.min.js', array(), '3.11.174', true );
		wp_enqueue_script( 'abc-covers', ABC_URL . 'assets/covers.js', array( 'abc-pdfjs' ), ABC_VERSION, true );
		wp_localize_script(
			'abc-covers',
			'abcCovers',
			array(
				'worker' => ABC_URL . 'assets/vendor/pdfjs/pdf.worker.min.js',
				'media'  => esc_url_raw( rest_url( 'wp/v2/media' ) ),
				'route'  => esc_url_raw( rest_url( 'abc/v1/pub-cover' ) ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
				'items'  => $todo,
				'mode'   => 'auto',
			)
		);
	}

	public static function menu() {
		add_submenu_page( 'abc-researcher', 'Publication covers', 'Publication covers', 'manage_options', 'abc-covers', array( __CLASS__, 'render' ) );
	}

	public static function routes() {
		register_rest_route(
			'abc/v1',
			'/pub-cover',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
				'callback'            => array( __CLASS__, 'save' ),
			)
		);
		register_rest_route(
			'abc/v1',
			'/pdf-proxy',
			array(
				'methods'             => 'GET',
				'permission_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
				'callback'            => array( __CLASS__, 'proxy' ),
			)
		);
	}

	/** Same-origin copy of the publication's own external PDF (so the browser can render page 1). Editors only; the URL comes from the saved post, never from the request. */
	public static function proxy( WP_REST_Request $r ) {
		$post = absint( $r->get_param( 'post' ) );
		if ( ! $post || 'abc_publication' !== get_post_type( $post ) || ! current_user_can( 'edit_post', $post ) ) {
			return new WP_Error( 'abc_cover_bad', 'Invalid request.', array( 'status' => 400 ) );
		}
		$url = esc_url_raw( (string) abc_get( 'pub_pdf_url', $post ), array( 'http', 'https' ) );
		if ( ! $url ) {
			return new WP_Error( 'abc_cover_nourl', 'No PDF link.', array( 'status' => 404 ) );
		}
		$res = wp_safe_remote_get( $url, array( 'timeout' => 40, 'redirection' => 4, 'limit_response_size' => 30 * MB_IN_BYTES, 'user-agent' => 'Mozilla/5.0 (compatible; AhceneBaboriCovers/1.0)' ) );
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			return new WP_Error( 'abc_cover_fetch', 'The PDF could not be downloaded.', array( 'status' => 502 ) );
		}
		$body = wp_remote_retrieve_body( $res );
		if ( 0 !== strpos( ltrim( substr( $body, 0, 1024 ) ), '%PDF' ) ) {
			return new WP_Error( 'abc_cover_notpdf', 'The link is not a PDF file.', array( 'status' => 415 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/pdf' );
		header( 'Content-Length: ' . strlen( $body ) );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput -- binary PDF.
		exit;
	}

	/** Store the generated cover on a publication. */
	public static function save( WP_REST_Request $r ) {
		$post = absint( $r->get_param( 'post' ) );
		$att  = absint( $r->get_param( 'attachment' ) );
		if ( ! $post || 'abc_publication' !== get_post_type( $post ) || ! current_user_can( 'edit_post', $post ) || ! $att || ! wp_attachment_is_image( $att ) || ! current_user_can( 'edit_post', $att ) ) {
			return new WP_Error( 'abc_cover_bad', 'Invalid request.', array( 'status' => 400 ) );
		}
		update_post_meta( $post, 'abc_pub_cover_id', $att );
		return rest_ensure_response( array( 'ok' => 1 ) );
	}

	public static function items() {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'abc_publication', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => 300, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => true ) ) as $p ) {
			$pdf = absint( get_post_meta( $p->ID, 'abc_pub_pdf_id', true ) );
			$ext = (string) get_post_meta( $p->ID, 'abc_pub_pdf_url', true );
			$cov = absint( get_post_meta( $p->ID, 'abc_pub_cover_id', true ) );
			$src = $pdf ? (string) wp_get_attachment_url( $pdf ) : ( $ext ? esc_url_raw( add_query_arg( 'post', $p->ID, rest_url( 'abc/v1/pdf-proxy' ) ) ) : '' );
			$out[] = array(
				'id'     => $p->ID,
				'title'  => get_the_title( $p ),
				'pdf'    => $src,
				'hasPdf' => '' !== $src,
				'cover'  => ( $cov && wp_attachment_is_image( $cov ) ) ? (string) wp_get_attachment_image_url( $cov, 'thumbnail' ) : '',
			);
		}
		return $out;
	}

	public static function assets( $hook ) {
		$screen  = get_current_screen();
		$is_edit = $screen && 'abc_publication' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
		$is_page = false !== strpos( (string) $hook, 'abc-covers' );
		if ( ! $is_edit && ! $is_page ) {
			return;
		}
		wp_enqueue_script( 'abc-pdfjs', ABC_URL . 'assets/vendor/pdfjs/pdf.min.js', array(), '3.11.174', true );
		wp_enqueue_script( 'abc-covers', ABC_URL . 'assets/covers.js', array( 'abc-pdfjs' ), ABC_VERSION, true );
		wp_localize_script(
			'abc-covers',
			'abcCovers',
			array(
				'worker' => ABC_URL . 'assets/vendor/pdfjs/pdf.worker.min.js',
				'media'  => esc_url_raw( rest_url( 'wp/v2/media' ) ),
				'route'  => esc_url_raw( rest_url( 'abc/v1/pub-cover' ) ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
				'proxy'  => esc_url_raw( rest_url( 'abc/v1/pdf-proxy' ) ),
				'items'  => $is_page ? self::items() : array(),
				'mode'   => $is_page ? 'bulk' : 'edit',
			)
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap"><h1>Publication covers</h1><p>Creates a cover image from the <strong>first page of each uploaded PDF</strong> (rendered in your browser – nothing is sent to another service). Covers appear on the publications page. Publications with an external PDF link are processed too (the site downloads the PDF once to render page 1).</p>';
		echo '<p><button type="button" class="button button-primary" id="abc-covers-all">Generate all missing covers</button> <label style="margin-inline-start:12px"><input type="checkbox" id="abc-covers-redo"> also redo existing covers</label></p><p id="abc-covers-status" role="status"></p>';
		echo '<table class="widefat striped" id="abc-covers-table" style="max-width:900px"><thead><tr><th>Cover</th><th>Publication</th><th>PDF</th><th>Status</th></tr></thead><tbody></tbody></table></div>';
	}
}
