<?php
/**
 * SEO layer: meta description, Open Graph, schema.org JSON-LD, Highwire/Google Scholar
 * citation meta tags, breadcrumbs. Stays out of the way when Yoast SEO, Rank Math or
 * All in One SEO manage the basics.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Label (translated) for a CPT archive.
 *
 * @param string $pt Post type.
 * @return string
 */
function abc_pt_label( $pt ) {
	$map = array(
		'abc_publication'  => 'publications',
		'abc_book'         => 'books',
		'abc_project'      => 'projects',
		'abc_presentation' => 'presentations',
		'abc_service'      => 'academic_service',
		'abc_teaching'     => 'teaching',
		'abc_note'         => 'research_notes',
	);
	return isset( $map[ $pt ] ) ? abc_t( $map[ $pt ] ) : '';
}

class ABC_SEO {

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'head' ), 2 );
		add_filter( 'document_title_parts', array( __CLASS__, 'title_parts' ) );
		add_filter( 'post_type_archive_title', array( __CLASS__, 'archive_title' ), 10, 2 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_filter( 'wp_sitemaps_taxonomies', array( __CLASS__, 'sitemap_taxonomies' ) );
	}

	public static function archive_title( $name, $pt ) {
		$l = abc_pt_label( $pt );
		return $l ? $l : $name;
	}

	public static function title_parts( $parts ) {
		if ( is_post_type_archive() ) {
			$pt = get_query_var( 'post_type' );
			$pt = is_array( $pt ) ? reset( $pt ) : $pt;
			if ( abc_pt_label( $pt ) ) {
				$parts['title'] = abc_pt_label( $pt );
			}
		} elseif ( is_search() ) {
			$parts['title'] = abc_t( 'search_results_for', get_search_query( false ) );
		} elseif ( is_404() ) {
			$parts['title'] = abc_t( 'not_found_title' );
		}
		return $parts;
	}

	public static function robots( $robots ) {
		if ( is_search() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
		return $robots;
	}

	public static function sitemap_taxonomies( $taxes ) {
		unset( $taxes['abc_pub_year'] );
		return $taxes;
	}

	/**
	 * Breadcrumb trail for the current request.
	 *
	 * @return array[] Each [ label, url|'' ].
	 */
	public static function breadcrumbs() {
		$home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
		$out  = array( array( abc_t( 'home' ), $home ) );
		if ( is_front_page() ) {
			return $out;
		}
		if ( is_singular() ) {
			$post = get_queried_object();
			if ( 'page' === $post->post_type ) {
				$anc = array_reverse( get_post_ancestors( $post ) );
				foreach ( $anc as $a ) {
					$out[] = array( get_the_title( $a ), get_permalink( $a ) );
				}
			} elseif ( abc_pt_label( $post->post_type ) ) {
				$out[] = array( abc_pt_label( $post->post_type ), abc_archive_url( $post->post_type ) );
			}
			$out[] = array( get_the_title( $post ), '' );
		} elseif ( is_post_type_archive() ) {
			$pt    = get_query_var( 'post_type' );
			$pt    = is_array( $pt ) ? reset( $pt ) : $pt;
			$out[] = array( abc_pt_label( $pt ), '' );
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			$tax  = get_taxonomy( $term->taxonomy );
			$pt   = $tax && ! empty( $tax->object_type ) ? $tax->object_type[0] : '';
			if ( 'abc_research_area' === $term->taxonomy ) {
				$out[] = array( abc_t( 'research' ), '' );
			} elseif ( $pt && abc_pt_label( $pt ) ) {
				$out[] = array( abc_pt_label( $pt ), abc_archive_url( $pt ) );
			}
			$out[] = array( $term->name, '' );
		} elseif ( is_search() ) {
			$out[] = array( abc_t( 'search' ), '' );
		} elseif ( is_404() ) {
			$out[] = array( abc_t( 'not_found_title' ), '' );
		} elseif ( is_archive() ) {
			$out[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
		}
		return apply_filters( 'abc_breadcrumbs', $out );
	}

	/**
	 * Meta description for the current request.
	 *
	 * @return string
	 */
	public static function description() {
		$d = '';
		if ( is_front_page() ) {
			$d = abc_ml( 'tagline' );
		} elseif ( is_singular() ) {
			$p = get_queried_object();
			if ( 'abc_publication' === $p->post_type ) {
				$d = abc_get( 'pub_abstract', $p->ID );
			} elseif ( 'abc_presentation' === $p->post_type ) {
				$d = abc_get( 'pres_abstract', $p->ID );
			} elseif ( 'abc_project' === $p->post_type ) {
				$d = abc_get( 'project_question', $p->ID );
			}
			if ( ! $d ) {
				$d = has_excerpt( $p ) ? $p->post_excerpt : wp_strip_all_tags( strip_shortcodes( $p->post_content ) );
			}
		} elseif ( is_post_type_archive() ) {
			$pt = get_query_var( 'post_type' );
			$pt = is_array( $pt ) ? reset( $pt ) : $pt;
			$d  = abc_t( 'desc_' . $pt );
		} elseif ( is_tax() ) {
			$d = term_description();
		}
		if ( ! $d || 'desc_' === substr( $d, 0, 5 ) ) {
			$d = abc_ml( 'tagline' );
		}
		$d = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $d ) ) );
		return mb_strlen( $d ) > 160 ? rtrim( mb_substr( $d, 0, 157 ) ) . '…' : $d;
	}

	/**
	 * Current URL (canonical).
	 *
	 * @return string
	 */
	private static function current_url() {
		if ( is_singular() ) {
			return get_permalink( get_queried_object_id() );
		}
		if ( is_post_type_archive() ) {
			$pt = get_query_var( 'post_type' );
			return get_post_type_archive_link( is_array( $pt ) ? reset( $pt ) : $pt );
		}
		if ( is_tax() || is_category() || is_tag() ) {
			return get_term_link( get_queried_object() );
		}
		if ( is_front_page() ) {
			return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
		}
		return home_url( add_query_arg( null, null ) );
	}

	/**
	 * Image for social cards.
	 *
	 * @return string
	 */
	private static function image() {
		if ( is_singular() && has_post_thumbnail() ) {
			return (string) get_the_post_thumbnail_url( null, 'large' );
		}
		$pid = absint( abc_setting( 'photo_id', 0 ) );
		if ( $pid ) {
			return (string) wp_get_attachment_image_url( $pid, 'large' );
		}
		return (string) apply_filters( 'abc_default_og_image', '' );
	}

	/**
	 * Output everything.
	 */
	public static function head() {
		if ( is_admin() || is_feed() ) {
			return;
		}
		$seo_plugin = abc_seo_plugin_active();
		$lang       = abc_lang();

		if ( ! $seo_plugin ) {
			$desc  = self::description();
			$url   = self::current_url();
			$title = wp_get_document_title();
			$img   = self::image();
			$loc   = array( 'en' => 'en_US', 'fr' => 'fr_FR', 'ar' => 'ar_AR' );
			if ( $desc ) {
				echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
			}
			if ( $url && ! is_singular() && ! is_search() && ! is_404() ) {
				echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n"; // Singular canonicals come from WordPress core.
			}
			echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
			echo '<meta property="og:locale" content="' . esc_attr( $loc[ $lang ] ) . '">' . "\n";
			echo '<meta property="og:type" content="' . esc_attr( is_singular( 'abc_note' ) ? 'article' : 'website' ) . '">' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
			if ( $desc ) {
				echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
			}
			if ( $url ) {
				echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
			}
			if ( $img ) {
				echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
			}
			echo '<meta name="twitter:card" content="' . esc_attr( $img ? 'summary_large_image' : 'summary' ) . '">' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
			if ( $desc ) {
				echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
			}
		}

		if ( is_singular( 'abc_publication' ) ) {
			self::citation_meta( get_queried_object_id() );
		}

		$graph = array();
		if ( is_front_page() ) {
			$graph[] = self::person();
			if ( ! $seo_plugin ) {
				$graph[] = self::website();
			}
		}
		if ( is_singular( 'abc_publication' ) ) {
			$graph[] = self::scholarly_article( get_queried_object_id() );
		} elseif ( is_singular( 'abc_book' ) ) {
			$graph[] = self::book( get_queried_object_id() );
		} elseif ( is_singular( 'abc_project' ) ) {
			$graph[] = self::project( get_queried_object_id() );
		} elseif ( is_singular( 'abc_note' ) ) {
			$graph[] = self::note( get_queried_object_id() );
		}
		if ( ! $seo_plugin && ! is_front_page() && ! is_404() ) {
			$crumbs = self::breadcrumbs();
			$items  = array();
			foreach ( $crumbs as $i => $c ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $c[0],
					'item'     => $c[1] ? $c[1] : self::current_url(),
				);
			}
			$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
		}
		$graph = array_values( array_filter( $graph ) );
		if ( $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	/** Google Scholar / Highwire Press tags. */
	private static function citation_meta( $id ) {
		$d    = abc_pub_data( $id );
		$tags = array( array( 'citation_title', $d['title'] ) );
		foreach ( $d['authors'] as $a ) {
			$tags[] = array( 'citation_author', $a['family'] . ( $a['given'] ? ', ' . $a['given'] : '' ) );
		}
		if ( $d['year'] ) {
			$tags[] = array( 'citation_publication_date', $d['year'] );
		}
		$map = array( 'citation_journal_title' => $d['journal'], 'citation_publisher' => $d['publisher'], 'citation_volume' => $d['volume'], 'citation_issue' => $d['issue'], 'citation_doi' => $d['doi'] );
		if ( $d['pages'] ) {
			$pg = preg_split( '/\s*[-–—]\s*/u', $d['pages'] );
			$map['citation_firstpage'] = $pg[0];
			if ( ! empty( $pg[1] ) ) {
				$map['citation_lastpage'] = $pg[1];
			}
		}
		foreach ( $map as $k => $v ) {
			if ( $v ) {
				$tags[] = array( $k, $v );
			}
		}
		$kw = get_the_terms( $id, 'abc_keyword' );
		if ( $kw && ! is_wp_error( $kw ) ) {
			$tags[] = array( 'citation_keywords', implode( '; ', wp_list_pluck( $kw, 'name' ) ) );
		}
		$tags[] = array( 'citation_abstract_html_url', get_permalink( $id ) );
		$pdf    = abc_file_or_url( 'pub_pdf_id', 'pub_pdf_url', $id );
		if ( $pdf ) {
			$tags[] = array( 'citation_pdf_url', $pdf );
		}
		$tags[] = array( 'citation_language', abc_lang() );
		foreach ( $tags as $t ) {
			echo '<meta name="' . esc_attr( $t[0] ) . '" content="' . esc_attr( $t[1] ) . '">' . "\n";
		}
	}

	/** Person node. */
	private static function person() {
		$s     = abc_settings();
		$home  = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
		$p     = array(
			'@type'           => 'Person',
			'@id'             => home_url( '/#researcher' ),
			'name'            => $s['name'],
			'honorificPrefix' => 'Dr.',
			'jobTitle'        => 'Assistant Professor',
			'description'     => abc_ml( 'tagline' ),
			'url'             => $home,
			'email'           => 'mailto:' . $s['email'],
			'knowsAbout'      => array( 'Digital Libraries', 'Institutional Repositories', 'Open Access', 'Open Science', 'Scholarly Communication', 'Metadata', 'Knowledge Organization', 'Research Data Management', 'Artificial Intelligence in Libraries', 'Library Technologies' ),
		);
		$pid   = absint( $s['photo_id'] );
		if ( $pid && wp_get_attachment_image_url( $pid, 'large' ) ) {
			$p['image'] = wp_get_attachment_image_url( $pid, 'large' );
		}
		$same = wp_list_pluck( abc_profiles(), 'url' );
		if ( $same ) {
			$p['sameAs'] = array_values( $same );
		}
		$org = trim( (string) ( $s['ml_affiliation_en'] ?? '' ) );
		if ( $org ) {
			$p['affiliation'] = array( '@type' => 'Organization', 'name' => $org );
		}
		return $p;
	}

	/** WebSite node with site search. */
	private static function website() {
		return array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'inLanguage'      => abc_lang(),
			'publisher'       => array( '@id' => home_url( '/#researcher' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/?s={search_term_string}' ) ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/** ScholarlyArticle node. */
	private static function scholarly_article( $id ) {
		$d      = abc_pub_data( $id );
		$authors = array();
		foreach ( $d['authors'] as $a ) {
			$authors[] = array( '@type' => 'Person', 'name' => $a['full'], 'familyName' => $a['family'], 'givenName' => $a['given'] );
		}
		$n = array(
			'@type'         => 'ScholarlyArticle',
			'@id'           => get_permalink( $id ) . '#article',
			'headline'      => $d['title'],
			'name'          => $d['title'],
			'url'           => get_permalink( $id ),
			'inLanguage'    => abc_lang(),
			'mainEntityOfPage' => get_permalink( $id ),
		);
		if ( $authors ) {
			$n['author'] = $authors;
		}
		if ( $d['year'] ) {
			$n['datePublished'] = $d['year'];
		}
		if ( abc_get( 'pub_abstract', $id ) ) {
			$n['abstract'] = abc_get( 'pub_abstract', $id );
		}
		if ( $d['publisher'] ) {
			$n['publisher'] = array( '@type' => 'Organization', 'name' => $d['publisher'] );
		}
		if ( $d['journal'] ) {
			$part = array( '@type' => 'Periodical', 'name' => $d['journal'] );
			$n['isPartOf'] = $part;
		}
		if ( $d['volume'] ) {
			$n['volumeNumber'] = $d['volume'];
		}
		if ( $d['issue'] ) {
			$n['issueNumber'] = $d['issue'];
		}
		if ( $d['pages'] ) {
			$n['pagination'] = $d['pages'];
		}
		if ( $d['doi'] ) {
			$n['identifier'] = array( '@type' => 'PropertyValue', 'propertyID' => 'DOI', 'value' => $d['doi'] );
			$n['sameAs']     = abc_doi_url( $d['doi'] );
		}
		$oa = abc_get( 'pub_oa', $id );
		if ( $oa ) {
			$n['isAccessibleForFree'] = 'closed' !== $oa;
		}
		$kw = get_the_terms( $id, 'abc_keyword' );
		if ( $kw && ! is_wp_error( $kw ) ) {
			$n['keywords'] = implode( ', ', wp_list_pluck( $kw, 'name' ) );
		}
		$pdf = abc_file_or_url( 'pub_pdf_id', 'pub_pdf_url', $id );
		if ( $pdf ) {
			$n['encoding'] = array( '@type' => 'MediaObject', 'contentUrl' => $pdf, 'encodingFormat' => 'application/pdf' );
		}
		return $n;
	}

	/** Book node. */
	private static function book( $id ) {
		$n = array(
			'@type'      => 'Book',
			'@id'        => get_permalink( $id ) . '#book',
			'name'       => get_the_title( $id ),
			'url'        => get_permalink( $id ),
			'inLanguage' => abc_lang(),
		);
		if ( abc_get( 'book_author', $id ) ) {
			$n['author'] = array( '@type' => 'Person', 'name' => abc_get( 'book_author', $id ) );
		}
		if ( abc_get( 'book_publisher', $id ) ) {
			$n['publisher'] = array( '@type' => 'Organization', 'name' => abc_get( 'book_publisher', $id ) );
		}
		if ( abc_get( 'book_year', $id ) ) {
			$n['datePublished'] = abc_get( 'book_year', $id );
		}
		if ( abc_get( 'book_isbn', $id ) ) {
			$n['isbn'] = abc_get( 'book_isbn', $id );
		}
		if ( has_post_thumbnail( $id ) ) {
			$n['image'] = get_the_post_thumbnail_url( $id, 'large' );
		}
		return $n;
	}

	/** ResearchProject node. */
	private static function project( $id ) {
		$n = array(
			'@type' => 'ResearchProject',
			'name'  => get_the_title( $id ),
			'url'   => get_permalink( $id ),
		);
		if ( abc_get( 'project_question', $id ) ) {
			$n['description'] = abc_get( 'project_question', $id );
		}
		if ( abc_get( 'project_start', $id ) ) {
			$n['startDate'] = abc_get( 'project_start', $id );
		}
		if ( abc_get( 'project_end', $id ) ) {
			$n['endDate'] = abc_get( 'project_end', $id );
		}
		return $n;
	}

	/** Research note as Article. */
	private static function note( $id ) {
		$n = array(
			'@type'         => 'Article',
			'headline'      => get_the_title( $id ),
			'url'           => get_permalink( $id ),
			'datePublished' => get_the_date( 'c', $id ),
			'dateModified'  => get_the_modified_date( 'c', $id ),
			'inLanguage'    => abc_lang(),
			'author'        => array( '@id' => home_url( '/#researcher' ) ),
		);
		if ( has_post_thumbnail( $id ) ) {
			$n['image'] = get_the_post_thumbnail_url( $id, 'large' );
		}
		return $n;
	}
}
