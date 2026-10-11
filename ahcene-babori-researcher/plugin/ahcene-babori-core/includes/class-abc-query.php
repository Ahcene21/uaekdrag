<?php
/**
 * Archive ordering, publication filters and site-wide research search.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Query {

	/** Post types searched site-wide. */
	public static function search_types() {
		return array( 'page', 'abc_publication', 'abc_book', 'abc_project', 'abc_presentation', 'abc_note', 'abc_resource', 'abc_service', 'abc_teaching' );
	}

	/**
	 * Hook.
	 */
	public static function init() {
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		add_filter( 'posts_search', array( __CLASS__, 'meta_search' ), 20, 2 );
	}

	/**
	 * Public query vars used by the filter form.
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		return array_merge( $vars, array( 'pub_year', 'pub_type', 'pub_area', 'pub_q', 'abc_type' ) );
	}

	/**
	 * Adjust queries.
	 *
	 * @param WP_Query $q Query.
	 */
	public static function pre_get_posts( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}

		if ( $q->is_search() ) {
			$types = self::search_types();
			$only  = sanitize_key( (string) $q->get( 'abc_type' ) );
			if ( $only && in_array( $only, $types, true ) ) {
				$types = array( $only );
			}
			$q->set( 'post_type', $types );
			$q->set( 'abc_meta_search', 1 );
			$q->set( 'posts_per_page', 12 );
			if ( 'abc_publication' === $only ) {
				self::apply_publication_filters( $q );
			}
			return;
		}

		if ( $q->is_post_type_archive( 'abc_publication' ) ) {
			self::apply_publication_filters( $q );
			$q->set( 'posts_per_page', 20 );
			self::order_by_meta( $q, 'abc_pub_year', 'NUMERIC', 'DESC' );
		} elseif ( $q->is_tax( 'abc_pub_type' ) || $q->is_tax( 'abc_pub_year' ) || $q->is_tax( 'abc_keyword' ) ) {
			$q->set( 'posts_per_page', 20 );
			self::order_by_meta( $q, 'abc_pub_year', 'NUMERIC', 'DESC' );
		} elseif ( $q->is_post_type_archive( 'abc_book' ) ) {
			self::order_by_meta( $q, 'abc_book_year', 'NUMERIC', 'DESC' );
			$q->set( 'posts_per_page', 24 );
		} elseif ( $q->is_post_type_archive( 'abc_presentation' ) || $q->is_tax( 'abc_pres_type' ) ) {
			self::order_by_meta( $q, 'abc_pres_date', 'CHAR', 'DESC' );
			$q->set( 'posts_per_page', 20 );
		} elseif ( $q->is_post_type_archive( 'abc_service' ) || $q->is_tax( 'abc_service_type' ) ) {
			self::order_by_meta( $q, 'abc_svc_from', 'CHAR', 'DESC' );
			$q->set( 'posts_per_page', 100 );
		} elseif ( $q->is_post_type_archive( 'abc_teaching' ) || $q->is_tax( 'abc_teaching_section' ) ) {
			$q->set( 'posts_per_page', 100 );
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		} elseif ( $q->is_post_type_archive( 'abc_project' ) ) {
			$q->set( 'posts_per_page', 24 );
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		} elseif ( $q->is_post_type_archive( 'abc_resource' ) || $q->is_tax( 'abc_resource_type' ) ) {
			$q->set( 'posts_per_page', 18 );
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		} elseif ( $q->is_post_type_archive( 'abc_note' ) || $q->is_tax( 'abc_note_category' ) || $q->is_tax( 'abc_note_tag' ) ) {
			$q->set( 'posts_per_page', 9 );
		} elseif ( $q->is_tax( 'abc_research_area' ) ) {
			$q->set( 'posts_per_page', 50 );
		}
	}

	/**
	 * Order by a meta field while still including posts without it.
	 *
	 * @param WP_Query $q    Query.
	 * @param string   $key  Meta key.
	 * @param string   $type CHAR|NUMERIC.
	 * @param string   $dir  ASC|DESC.
	 */
	private static function order_by_meta( $q, $key, $type, $dir ) {
		$q->set(
			'meta_query',
			array(
				'relation'    => 'OR',
				'abc_order_c' => array( 'key' => $key, 'compare' => 'EXISTS', 'type' => $type ),
				array( 'key' => $key, 'compare' => 'NOT EXISTS' ),
			)
		);
		$q->set( 'orderby', array( 'abc_order_c' => $dir, 'date' => 'DESC' ) );
	}

	/**
	 * Apply year / type / area / keyword filters from the request.
	 *
	 * @param WP_Query $q Query.
	 */
	private static function apply_publication_filters( $q ) {
		$tax = array();
		$map = array( 'pub_year' => 'abc_pub_year', 'pub_type' => 'abc_pub_type', 'pub_area' => 'abc_research_area' );
		foreach ( $map as $var => $taxonomy ) {
			$val = sanitize_title( (string) $q->get( $var ) );
			if ( $val ) {
				$tax[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $val );
			}
		}
		if ( $tax ) {
			$q->set( 'tax_query', array_merge( array( 'relation' => 'AND' ), $tax ) );
		}
		$kw = trim( sanitize_text_field( (string) $q->get( 'pub_q' ) ) );
		if ( '' !== $kw ) {
			$q->set( 's', $kw );
			$q->set( 'abc_meta_search', 1 );
		}
	}

	/**
	 * Extend the WordPress search to authors, journal, abstract, DOI and keywords.
	 *
	 * @param string   $search Search SQL.
	 * @param WP_Query $q      Query.
	 * @return string
	 */
	public static function meta_search( $search, $q ) {
		if ( ! $search || ! $q->get( 'abc_meta_search' ) || ! $q->get( 's' ) ) {
			return $search;
		}
		global $wpdb;
		$term = trim( (string) $q->get( 's' ) );
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$keys = array( 'abc_pub_authors', 'abc_pub_journal', 'abc_pub_abstract', 'abc_pub_doi', 'abc_book_author', 'abc_pres_conference', 'abc_pres_abstract', 'abc_project_question', 'abc_project_tech', 'abc_svc_org', 'abc_teach_institution' );
		$ph   = implode( ',', array_fill( 0, count( $keys ), '%s' ) );
		$ids  = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ($ph) AND meta_value LIKE %s LIMIT 300", array_merge( $keys, array( $like ) ) ) // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
		);
		$terms = get_terms(
			array(
				'taxonomy'   => array( 'abc_keyword', 'abc_research_area' ),
				'name__like' => $term,
				'fields'     => 'ids',
				'hide_empty' => true,
			)
		);
		if ( $terms && ! is_wp_error( $terms ) ) {
			$more = get_objects_in_term( $terms, array( 'abc_keyword', 'abc_research_area' ) );
			if ( ! is_wp_error( $more ) ) {
				$ids = array_merge( $ids, $more );
			}
		}
		$ids = array_values( array_unique( array_map( 'absint', $ids ) ) );
		if ( ! $ids ) {
			return $search;
		}
		return " AND ( ( 1=1 {$search} ) OR {$wpdb->posts}.ID IN (" . implode( ',', $ids ) . ') ) ';
	}

	/* ------------------------------------------------------------ UI parts */

	/**
	 * Request value helper.
	 *
	 * @param string $key GET key.
	 * @return string
	 */
	public static function req( $key ) {
		return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Publication filter form (Year, Type, Research area, Keyword).
	 *
	 * @param array $args action (URL), search (bool: include s + type fields).
	 * @return string HTML
	 */
	public static function publication_filters( $args = array() ) {
		$args = wp_parse_args( $args, array( 'action' => abc_archive_url( 'abc_publication' ), 'hidden' => array() ) );
		$sel  = static function ( $name, $label, $taxonomy, $order = 'ASC' ) {
			$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true, 'orderby' => 'name', 'order' => $order ) );
			$cur   = ABC_Query::req( $name );
			$out   = '<div class="abc-filter"><label for="f-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label><select id="f-' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '"><option value="">' . esc_html( abc_t( 'all' ) ) . '</option>';
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$out .= '<option value="' . esc_attr( $t->slug ) . '"' . selected( $cur, $t->slug, false ) . '>' . esc_html( $t->name ) . '</option>';
				}
			}
			return $out . '</select></div>';
		};
		$html  = '<form class="abc-filters" method="get" action="' . esc_url( $args['action'] ) . '" role="search" aria-label="' . esc_attr( abc_t( 'filter_publications' ) ) . '">';
		foreach ( (array) $args['hidden'] as $k => $v ) {
			$html .= '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
		}
		$html .= '<div class="abc-filter abc-filter--grow"><label for="f-pub_q">' . esc_html( abc_t( 'keyword' ) ) . '</label><input type="search" id="f-pub_q" name="pub_q" value="' . esc_attr( self::req( 'pub_q' ) ) . '" placeholder="' . esc_attr( abc_t( 'keyword_placeholder' ) ) . '"></div>';
		$html .= $sel( 'pub_year', abc_t( 'year' ), 'abc_pub_year', 'DESC' );
		$html .= $sel( 'pub_type', abc_t( 'type' ), 'abc_pub_type' );
		$html .= $sel( 'pub_area', abc_t( 'research_area' ), 'abc_research_area' );
		$html .= '<div class="abc-filter abc-filter--actions"><button type="submit" class="abc-btn abc-btn--primary">' . esc_html( abc_t( 'filter' ) ) . '</button> <a class="abc-link-reset" href="' . esc_url( $args['action'] ) . '">' . esc_html( abc_t( 'reset' ) ) . '</a></div>';
		return $html . '</form>';
	}

	/**
	 * Full research-discovery search form: query + content type (+ publication filters).
	 *
	 * @return string
	 */
	public static function search_form() {
		$cur   = self::req( 'abc_type' );
		$types = array(
			''                 => abc_t( 'all_content' ),
			'abc_publication'  => abc_t( 'publications' ),
			'abc_book'         => abc_t( 'books' ),
			'abc_project'      => abc_t( 'projects' ),
			'abc_presentation' => abc_t( 'presentations' ),
			'abc_note'         => abc_t( 'research_notes' ),
			'abc_resource'     => abc_t( 'resources' ),
			'page'             => abc_t( 'pages' ),
		);
		$home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
		$out  = '<form class="abc-search-form" role="search" method="get" action="' . esc_url( $home ) . '">';
		$out .= '<div class="abc-filter abc-filter--grow"><label for="abc-s">' . esc_html( abc_t( 'search_label' ) ) . '</label><input type="search" id="abc-s" name="s" value="' . esc_attr( get_search_query( false ) ) . '" placeholder="' . esc_attr( abc_t( 'search_placeholder' ) ) . '"></div>';
		$out .= '<div class="abc-filter"><label for="abc-type">' . esc_html( abc_t( 'content_type' ) ) . '</label><select id="abc-type" name="abc_type">';
		foreach ( $types as $v => $l ) {
			$out .= '<option value="' . esc_attr( $v ) . '"' . selected( $cur, $v, false ) . '>' . esc_html( $l ) . '</option>';
		}
		$out .= '</select></div>';
		if ( 'abc_publication' === $cur ) {
			foreach ( array( 'pub_year' => array( 'abc_pub_year', abc_t( 'year' ), 'DESC' ), 'pub_type' => array( 'abc_pub_type', abc_t( 'type' ), 'ASC' ), 'pub_area' => array( 'abc_research_area', abc_t( 'research_area' ), 'ASC' ) ) as $name => $d ) {
				$terms = get_terms( array( 'taxonomy' => $d[0], 'hide_empty' => true, 'orderby' => 'name', 'order' => $d[2] ) );
				$out  .= '<div class="abc-filter"><label for="s-' . esc_attr( $name ) . '">' . esc_html( $d[1] ) . '</label><select id="s-' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '"><option value="">' . esc_html( abc_t( 'all' ) ) . '</option>';
				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $t ) {
						$out .= '<option value="' . esc_attr( $t->slug ) . '"' . selected( self::req( $name ), $t->slug, false ) . '>' . esc_html( $t->name ) . '</option>';
					}
				}
				$out .= '</select></div>';
			}
		}
		$out .= '<div class="abc-filter abc-filter--actions"><button type="submit" class="abc-btn abc-btn--primary">' . esc_html( abc_t( 'search' ) ) . '</button></div></form>';
		return $out;
	}
}
