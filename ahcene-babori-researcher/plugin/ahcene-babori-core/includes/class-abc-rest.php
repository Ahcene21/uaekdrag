<?php
/**
 * Read-only public REST endpoints (namespace abc/v1). Standard CPT endpoints are
 * also available under /wp/v2/ (publications, books, projects, ...).
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_REST {

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	/**
	 * Routes.
	 */
	public static function routes() {
		register_rest_route(
			'abc/v1',
			'/profile',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'profile' ),
			)
		);
		register_rest_route(
			'abc/v1',
			'/publications',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'publications' ),
				'args'                => array(
					'year'     => array( 'sanitize_callback' => 'sanitize_title' ),
					'type'     => array( 'sanitize_callback' => 'sanitize_title' ),
					'area'     => array( 'sanitize_callback' => 'sanitize_title' ),
					'q'        => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'per_page' => array( 'sanitize_callback' => 'absint', 'default' => 20 ),
					'page'     => array( 'sanitize_callback' => 'absint', 'default' => 1 ),
				),
			)
		);
	}

	/** Public profile (nothing private). */
	public static function profile() {
		return rest_ensure_response(
			array(
				'name'     => abc_setting( 'name' ),
				'title'    => abc_ml( 'role' ),
				'tagline'  => abc_ml( 'tagline' ),
				'email'    => abc_setting( 'email' ),
				'profiles' => abc_profiles(),
				'stats'    => abc_stats(),
				'language' => abc_lang(),
			)
		);
	}

	/** Publications list. */
	public static function publications( WP_REST_Request $r ) {
		$tax = array();
		foreach ( array( 'year' => 'abc_pub_year', 'type' => 'abc_pub_type', 'area' => 'abc_research_area' ) as $p => $t ) {
			if ( $r[ $p ] ) {
				$tax[] = array( 'taxonomy' => $t, 'field' => 'slug', 'terms' => $r[ $p ] );
			}
		}
		$args = array(
			'post_type'      => 'abc_publication',
			'post_status'    => 'publish',
			'posts_per_page' => min( 100, max( 1, (int) $r['per_page'] ) ),
			'paged'          => max( 1, (int) $r['page'] ),
			'tax_query'      => $tax, // phpcs:ignore WordPress.DB.SlowDBQuery
			's'              => $r['q'],
			'abc_meta_search' => $r['q'] ? 1 : 0,
			'lang'           => '' === (string) $r->get_param( 'lang' ) ? abc_lang() : sanitize_key( $r->get_param( 'lang' ) ),
		);
		$q   = new WP_Query( $args );
		$out = array();
		foreach ( $q->posts as $p ) {
			$d     = abc_pub_data( $p->ID );
			$out[] = array(
				'id'       => $p->ID,
				'title'    => $d['title'],
				'url'      => get_permalink( $p ),
				'authors'  => wp_list_pluck( $d['authors'], 'full' ),
				'year'     => $d['year'],
				'journal'  => $d['journal'],
				'doi'      => $d['doi'],
				'doi_url'  => abc_doi_url( $d['doi'] ),
				'pdf'      => abc_file_or_url( 'pub_pdf_id', 'pub_pdf_url', $p->ID ),
				'open_access' => abc_get( 'pub_oa', $p->ID ),
				'citation' => wp_strip_all_tags( abc_citation( $p->ID ) ),
			);
		}
		$res = rest_ensure_response( $out );
		$res->header( 'X-WP-Total', (string) $q->found_posts );
		$res->header( 'X-WP-TotalPages', (string) $q->max_num_pages );
		return $res;
	}
}
