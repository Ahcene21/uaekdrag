<?php
/**
 * Import architecture for future bibliographic connectors.
 *
 * Design rules:
 *  - Connectors only ever return REAL data fetched from the remote service. There are no mock
 *    responses anywhere in this plugin.
 *  - Imported records are saved as "Pending Review" – nothing is published without manual approval.
 *  - Duplicates are detected by DOI.
 *
 * Ready today: Crossref (single DOI lookup).
 * Planned (registered, not yet enabled): ORCID API, OpenAlex, OpenAIRE, OAI-PMH, DSpace REST API.
 * Add your own connector with the `abc_import_providers` filter (extend ABC_Import_Provider).
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

abstract class ABC_Import_Provider {
	abstract public function id();
	abstract public function label();
	abstract public function description();

	/** @return bool Whether fetch() is implemented. */
	public function is_ready() {
		return false;
	}

	/** Input field label (what the user must type). */
	public function input_label() {
		return 'Identifier';
	}

	/**
	 * Fetch normalised records.
	 *
	 * Each record: title, authors[] ("Family, Given"), year, journal, publisher, volume,
	 * issue, pages, doi, url, abstract, type_key.
	 *
	 * @param string $input Identifier typed by the administrator.
	 * @return array[]|WP_Error
	 */
	public function fetch( $input ) {
		return new WP_Error( 'abc_not_implemented', 'This connector is planned but not enabled yet.' );
	}
}

class ABC_Import_Planned extends ABC_Import_Provider {
	private $id;
	private $label;
	private $desc;
	public function __construct( $id, $label, $desc ) {
		$this->id    = $id;
		$this->label = $label;
		$this->desc  = $desc;
	}
	public function id() {
		return $this->id;
	}
	public function label() {
		return $this->label;
	}
	public function description() {
		return $this->desc;
	}
}

class ABC_Import_Crossref extends ABC_Import_Provider {
	public function id() {
		return 'crossref';
	}
	public function label() {
		return 'Crossref (by DOI)';
	}
	public function description() {
		return 'Fetches the bibliographic record of one DOI from the public Crossref REST API (api.crossref.org).';
	}
	public function is_ready() {
		return true;
	}
	public function input_label() {
		return 'DOI';
	}

	public function fetch( $input ) {
		$doi = abc_doi_clean( $input );
		if ( ! $doi ) {
			return new WP_Error( 'abc_bad_doi', 'This does not look like a valid DOI (expected 10.xxxx/…).' );
		}
		$url = 'https://api.crossref.org/works/' . str_replace( '%2F', '/', rawurlencode( $doi ) );
		$res = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array( 'User-Agent' => 'AhceneBaboriCore/' . ABC_VERSION . ' (' . home_url( '/' ) . '; mailto:' . abc_setting( 'email' ) . ')' ),
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$code = wp_remote_retrieve_response_code( $res );
		if ( 404 === $code ) {
			return new WP_Error( 'abc_not_found', 'Crossref has no record for this DOI.' );
		}
		if ( 200 !== $code ) {
			return new WP_Error( 'abc_http', 'Crossref answered with HTTP ' . (int) $code . '.' );
		}
		$json = json_decode( wp_remote_retrieve_body( $res ), true );
		$m    = $json['message'] ?? null;
		if ( ! is_array( $m ) ) {
			return new WP_Error( 'abc_bad_response', 'Unexpected response from Crossref.' );
		}
		$authors = array();
		foreach ( (array) ( $m['author'] ?? array() ) as $a ) {
			$fam = $a['family'] ?? ( $a['name'] ?? '' );
			if ( $fam ) {
				$authors[] = trim( $fam . ( ! empty( $a['given'] ) ? ', ' . $a['given'] : '' ) );
			}
		}
		$year = $m['issued']['date-parts'][0][0] ?? ( $m['published']['date-parts'][0][0] ?? '' );
		$map  = array(
			'journal-article'     => 'journal-article',
			'proceedings-article' => 'conference-paper',
			'book-chapter'        => 'book-chapter',
			'book'                => 'book',
			'monograph'           => 'book',
			'dissertation'        => 'thesis',
			'report'              => 'report',
			'posted-content'      => 'preprint',
		);
		return array(
			array(
				'title'     => wp_strip_all_tags( $m['title'][0] ?? '' ),
				'authors'   => $authors,
				'year'      => $year ? (string) $year : '',
				'journal'   => wp_strip_all_tags( $m['container-title'][0] ?? '' ),
				'publisher' => wp_strip_all_tags( $m['publisher'] ?? '' ),
				'volume'    => $m['volume'] ?? '',
				'issue'     => $m['issue'] ?? '',
				'pages'     => $m['page'] ?? '',
				'doi'       => $m['DOI'] ?? $doi,
				'url'       => $m['URL'] ?? '',
				'abstract'  => isset( $m['abstract'] ) ? trim( wp_strip_all_tags( $m['abstract'] ) ) : '',
				'type_key'  => $map[ $m['type'] ?? '' ] ?? 'other',
			),
		);
	}
}

class ABC_Import {

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'admin_post_abc_import', array( __CLASS__, 'handle' ) );
		add_action( 'admin_notices', array( __CLASS__, 'review_notice' ) );
	}

	/**
	 * Registered providers.
	 *
	 * @return ABC_Import_Provider[] keyed by id
	 */
	public static function providers() {
		$list = array(
			new ABC_Import_Crossref(),
			new ABC_Import_Planned( 'orcid', 'ORCID API', 'Planned: import works listed in your public ORCID record (requires your ORCID iD).' ),
			new ABC_Import_Planned( 'openalex', 'OpenAlex', 'Planned: import works linked to your OpenAlex author profile.' ),
			new ABC_Import_Planned( 'openaire', 'OpenAIRE', 'Planned: import research outputs from the OpenAIRE Graph.' ),
			new ABC_Import_Planned( 'oai-pmh', 'OAI-PMH harvester', 'Planned: harvest Dublin Core records from any OAI-PMH repository endpoint.' ),
			new ABC_Import_Planned( 'dspace', 'DSpace REST API', 'Planned: import items from a DSpace institutional repository.' ),
		);
		$list = apply_filters( 'abc_import_providers', $list );
		$out  = array();
		foreach ( $list as $p ) {
			if ( $p instanceof ABC_Import_Provider ) {
				$out[ $p->id() ] = $p;
			}
		}
		return $out;
	}

	/**
	 * Create a pending publication from a normalised record.
	 *
	 * @param array  $r      Record.
	 * @param string $source Provider id.
	 * @return int|WP_Error Post ID.
	 */
	public static function stage( $r, $source ) {
		$doi = abc_doi_clean( $r['doi'] ?? '' );
		if ( $doi ) {
			$dup = get_posts(
				array(
					'post_type'      => 'abc_publication',
					'post_status'    => 'any',
					'meta_key'       => 'abc_pub_doi', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'     => $doi, // phpcs:ignore WordPress.DB.SlowDBQuery
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'lang'           => '',
				)
			);
			if ( $dup ) {
				return new WP_Error( 'abc_duplicate', 'A publication with this DOI already exists (ID ' . (int) $dup[0] . ').' );
			}
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'abc_publication',
				'post_status' => 'pending',
				'post_title'  => sanitize_text_field( $r['title'] ?? '(untitled)' ),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$fields = array(
			'pub_authors'   => implode( "\n", array_map( 'sanitize_text_field', (array) ( $r['authors'] ?? array() ) ) ),
			'pub_year'      => $r['year'] ?? '',
			'pub_journal'   => $r['journal'] ?? '',
			'pub_publisher' => $r['publisher'] ?? '',
			'pub_volume'    => $r['volume'] ?? '',
			'pub_issue'     => $r['issue'] ?? '',
			'pub_pages'     => $r['pages'] ?? '',
			'pub_doi'       => $doi,
			'pub_url'       => $r['url'] ?? '',
			'pub_abstract'  => $r['abstract'] ?? '',
		);
		$defs = ABC_Fields::fields_for( 'abc_publication' );
		foreach ( $fields as $k => $v ) {
			$v = ABC_Fields::sanitize( $v, $defs[ $k ] );
			if ( '' !== $v ) {
				update_post_meta( $id, 'abc_' . $k, $v );
			}
		}
		update_post_meta( $id, '_abc_import_source', sanitize_key( $source ) );
		update_post_meta( $id, '_abc_import_time', time() );
		if ( ! empty( $r['type_key'] ) ) {
			$terms = get_terms( array( 'taxonomy' => 'abc_pub_type', 'hide_empty' => false, 'meta_key' => 'abc_key', 'meta_value' => $r['type_key'], 'lang' => '' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( $terms && ! is_wp_error( $terms ) ) {
				wp_set_object_terms( $id, array( (int) $terms[0]->term_id ), 'abc_pub_type' );
			}
		}
		return $id;
	}

	/**
	 * Form handler.
	 */
	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['abc_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_import_nonce'] ) ), 'abc_import' ) ) {
			wp_die( 'Not allowed.', 403 );
		}
		$pid       = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
		$input     = isset( $_POST['identifier'] ) ? sanitize_text_field( wp_unslash( $_POST['identifier'] ) ) : '';
		$providers = self::providers();
		$redirect  = admin_url( 'admin.php?page=abc-import' );
		if ( empty( $providers[ $pid ] ) || ! $providers[ $pid ]->is_ready() ) {
			wp_safe_redirect( add_query_arg( 'abc_error', rawurlencode( 'Choose an available connector.' ), $redirect ) );
			exit;
		}
		$records = $providers[ $pid ]->fetch( $input );
		if ( is_wp_error( $records ) ) {
			wp_safe_redirect( add_query_arg( 'abc_error', rawurlencode( $records->get_error_message() ), $redirect ) );
			exit;
		}
		$last = 0;
		foreach ( $records as $r ) {
			$res = self::stage( $r, $pid );
			if ( is_wp_error( $res ) ) {
				wp_safe_redirect( add_query_arg( 'abc_error', rawurlencode( $res->get_error_message() ), $redirect ) );
				exit;
			}
			$last = $res;
		}
		wp_safe_redirect( $last ? get_edit_post_link( $last, 'raw' ) : $redirect );
		exit;
	}

	/**
	 * Reminder on imported, unreviewed records.
	 */
	public static function review_notice() {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || 'abc_publication' !== $screen->post_type ) {
			return;
		}
		global $post;
		if ( $post && get_post_meta( $post->ID, '_abc_import_source', true ) && 'pending' === $post->post_status ) {
			echo '<div class="notice notice-info"><p><strong>Imported record.</strong> This publication was imported from <code>' . esc_html( get_post_meta( $post->ID, '_abc_import_source', true ) ) . '</code> and is <em>Pending Review</em>. Check every field, add research area and PDF/repository links, then publish.</p></div>';
		}
	}

	/**
	 * Admin page.
	 */
	public static function render_page() {
		echo '<div class="wrap"><h1>Import publications (beta)</h1>';
		if ( isset( $_GET['abc_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-error"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['abc_error'] ) ) ) . '</p></div>'; // phpcs:ignore WordPress.Security.NonceVerification
		}
		echo '<p class="abc-note">Imported records are saved as <strong>Pending Review</strong>. Nothing is published until you approve it. Only real data from the remote service is used.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="abc_import">';
		wp_nonce_field( 'abc_import', 'abc_import_nonce' );
		echo '<table class="form-table" role="presentation"><tr><th scope="row"><label for="abc-prov">Connector</label></th><td><select id="abc-prov" name="provider">';
		foreach ( self::providers() as $p ) {
			echo '<option value="' . esc_attr( $p->id() ) . '"' . disabled( ! $p->is_ready(), true, false ) . '>' . esc_html( $p->label() . ( $p->is_ready() ? '' : ' — planned' ) ) . '</option>';
		}
		echo '</select></td></tr><tr><th scope="row"><label for="abc-ident">DOI</label></th><td><input type="text" id="abc-ident" name="identifier" class="regular-text" placeholder="10.xxxx/xxxxx" required></td></tr></table>';
		submit_button( 'Fetch and create pending record' );
		echo '</form><h2>Connectors</h2><table class="widefat striped" style="max-width:860px"><thead><tr><th>Connector</th><th>Status</th><th>Description</th></tr></thead><tbody>';
		foreach ( self::providers() as $p ) {
			echo '<tr><td>' . esc_html( $p->label() ) . '</td><td>' . ( $p->is_ready() ? '<strong style="color:#0a7d33">Ready</strong>' : 'Planned' ) . '</td><td>' . esc_html( $p->description() ) . '</td></tr>';
		}
		echo '</tbody></table>';
		$pending = get_posts( array( 'post_type' => 'abc_publication', 'post_status' => 'pending', 'posts_per_page' => 20, 'lang' => '' ) );
		echo '<h2>Awaiting review</h2>';
		if ( ! $pending ) {
			echo '<p>No pending records.</p>';
		} else {
			echo '<ul>';
			foreach ( $pending as $p ) {
				echo '<li><a href="' . esc_url( get_edit_post_link( $p->ID ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}
}
