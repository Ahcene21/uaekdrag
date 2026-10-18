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

	private static function items() {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'abc_publication', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => 300, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => true ) ) as $p ) {
			$pdf = absint( get_post_meta( $p->ID, 'abc_pub_pdf_id', true ) );
			$cov = absint( get_post_meta( $p->ID, 'abc_pub_cover_id', true ) );
			$out[] = array(
				'id'     => $p->ID,
				'title'  => get_the_title( $p ),
				'pdf'    => $pdf ? (string) wp_get_attachment_url( $pdf ) : '',
				'hasPdf' => (bool) $pdf,
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
				'items'  => $is_page ? self::items() : array(),
				'mode'   => $is_page ? 'bulk' : 'edit',
			)
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap"><h1>Publication covers</h1><p>Creates a cover image from the <strong>first page of each uploaded PDF</strong> (rendered in your browser – nothing is sent to another service). Covers appear on the publications page. Publications with only an external PDF link cannot be processed: upload the PDF to the Media Library or add a cover image manually.</p>';
		echo '<p><button type="button" class="button button-primary" id="abc-covers-all">Generate all missing covers</button> <label style="margin-inline-start:12px"><input type="checkbox" id="abc-covers-redo"> also redo existing covers</label></p><p id="abc-covers-status" role="status"></p>';
		echo '<table class="widefat striped" id="abc-covers-table" style="max-width:900px"><thead><tr><th>Cover</th><th>Publication</th><th>PDF</th><th>Status</th></tr></thead><tbody></tbody></table></div>';
	}
}
