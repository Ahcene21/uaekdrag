<?php
/**
 * Shared helper functions (prefix abc_). Used by the plugin and by the theme.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Language
 * ---------------------------------------------------------------------- */

/**
 * Supported interface languages.
 *
 * @return string[]
 */
function abc_languages() {
	return array( 'en', 'fr', 'ar' );
}

/**
 * Normalise any locale string (fr_FR, ar, en-US) to en|fr|ar.
 *
 * @param string $locale Locale or language code.
 * @return string
 */
function abc_norm_lang( $locale ) {
	$code = strtolower( substr( (string) $locale, 0, 2 ) );
	return in_array( $code, abc_languages(), true ) ? $code : 'en';
}

/**
 * Current content language (Polylang aware, locale fallback).
 *
 * @return string en|fr|ar
 */
function abc_lang() {
	if ( function_exists( 'pll_current_language' ) ) {
		$l = pll_current_language();
		if ( $l ) {
			return abc_norm_lang( $l );
		}
	}
	return abc_norm_lang( determine_locale() );
}

/**
 * Is the current language right-to-left?
 *
 * @return bool
 */
function abc_is_rtl() {
	return 'ar' === abc_lang() || is_rtl();
}

/**
 * Translate a built-in interface string. No machine translation, no .mo files:
 * every string is professionally written in EN / FR / AR in includes/strings.php.
 *
 * @param string $key  String key.
 * @param mixed  ...$args Optional sprintf arguments.
 * @return string
 */
function abc_t( $key, ...$args ) {
	static $strings = null;
	if ( null === $strings ) {
		$strings = require ABC_DIR . 'includes/strings.php';
	}
	$lang = abc_lang();
	if ( isset( $strings[ $key ][ $lang ] ) ) {
		$out = $strings[ $key ][ $lang ];
	} elseif ( isset( $strings[ $key ]['en'] ) ) {
		$out = $strings[ $key ]['en'];
	} else {
		$out = $key;
	}
	return $args ? vsprintf( $out, $args ) : $out;
}

/**
 * Get a built-in string in an explicit language.
 *
 * @param string $key  Key.
 * @param string $lang Language.
 * @return string
 */
function abc_t_lang( $key, $lang ) {
	static $strings = null;
	if ( null === $strings ) {
		$strings = require ABC_DIR . 'includes/strings.php';
	}
	return $strings[ $key ][ $lang ] ?? ( $strings[ $key ]['en'] ?? $key );
}

/* -------------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------------- */

/**
 * All saved settings merged with defaults.
 *
 * @return array
 */
function abc_settings() {
	static $cache = null;
	if ( null === $cache ) {
		$saved = get_option( 'abc_settings', array() );
		$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), ABC_Settings::defaults() );
	}
	return $cache;
}

/**
 * One setting value.
 *
 * @param string $key     Key.
 * @param mixed  $default Default.
 * @return mixed
 */
function abc_setting( $key, $default = '' ) {
	$s = abc_settings();
	return isset( $s[ $key ] ) && '' !== $s[ $key ] ? $s[ $key ] : $default;
}

/**
 * Translatable setting: value for current language, else English, else built-in default string.
 *
 * @param string $key Key (role, tagline, affiliation, bio, ...).
 * @return string
 */
function abc_ml( $key ) {
	$s    = abc_settings();
	$lang = abc_lang();
	if ( ! empty( $s[ "ml_{$key}_{$lang}" ] ) ) {
		return (string) $s[ "ml_{$key}_{$lang}" ];
	}
	if ( ! empty( $s[ "ml_{$key}_en" ] ) && 'en' === $lang ) {
		return (string) $s[ "ml_{$key}_en" ];
	}
	return abc_t( 'default_' . $key );
}

/**
 * Researcher identifier / profile links that actually have a value.
 *
 * @return array[] Each: key,label,url,icon
 */
function abc_profiles() {
	$s      = abc_settings();
	$orcid  = trim( (string) $s['orcid'] );
	if ( $orcid && preg_match( '/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid ) ) {
		$orcid = 'https://orcid.org/' . $orcid;
	}
	$map = array(
		'orcid'         => array( 'ORCID', $orcid, 'orcid' ),
		'scholar'       => array( 'Google Scholar', $s['scholar'], 'scholar' ),
		'scopus'        => array( 'Scopus', $s['scopus'], 'scopus' ),
		'researchgate'  => array( 'ResearchGate', $s['researchgate'], 'researchgate' ),
		'linkedin'      => array( 'LinkedIn', $s['linkedin'], 'linkedin' ),
		'institutional' => array( $s['institutional_label'] ? $s['institutional_label'] : abc_t( 'institutional_profile' ), $s['institutional'], 'institution' ),
	);
	$out = array();
	foreach ( $map as $key => $row ) {
		if ( ! empty( $row[1] ) && preg_match( '#^https?://#i', $row[1] ) ) {
			$out[] = array(
				'key'   => $key,
				'label' => $row[0],
				'url'   => esc_url_raw( $row[1] ),
				'icon'  => $row[2],
			);
		}
	}
	return apply_filters( 'abc_profiles', $out );
}

/**
 * Statistics rows for the homepage "Research impact" block.
 *
 * @return array[] Each: value,label
 */
function abc_stats() {
	$s    = abc_settings();
	$lang = abc_lang();
	$out  = array();
	foreach ( (array) $s['stats'] as $row ) {
		$value = isset( $row['value'] ) ? trim( (string) $row['value'] ) : '';
		$label = trim( (string) ( $row[ 'label_' . $lang ] ?? '' ) );
		if ( '' === $label ) {
			$label = trim( (string) ( $row['label_en'] ?? '' ) );
		}
		if ( '' !== $value && '' !== $label ) {
			$out[] = array( 'value' => $value, 'label' => $label );
		}
	}
	return $out;
}

/**
 * URL of the CV PDF for the current language (falls back to another language).
 *
 * @return string Empty string when no file uploaded.
 */
function abc_cv_file_url() {
	$s     = abc_settings();
	$order = array_unique( array_merge( array( abc_lang() ), array( 'en', 'fr', 'ar' ) ) );
	foreach ( $order as $l ) {
		$id = absint( $s[ 'cv_id_' . $l ] ?? 0 );
		if ( $id && get_post( $id ) ) {
			$url = wp_get_attachment_url( $id );
			if ( $url ) {
				return $url;
			}
		}
	}
	return '';
}

/**
 * Online CV page URL (page using template-cv.php), translated.
 *
 * @return string
 */
function abc_cv_page_url() {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => 'template-cv.php', // phpcs:ignore WordPress.DB.SlowDBQuery
			'lang'           => abc_lang(),
			'fields'         => 'ids',
		)
	);
	if ( $pages ) {
		return get_permalink( $pages[0] );
	}
	return home_url( '/' );
}

/**
 * URL of a demo/starter page by its key (home, about, research, cv, contact, discover), translated.
 *
 * @param string $key Page key.
 * @return string
 */
function abc_page_url( $key ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_abc_page_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => sanitize_key( $key ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'lang'           => abc_lang(),
			'fields'         => 'ids',
		)
	);
	if ( $pages ) {
		return get_permalink( $pages[0] );
	}
	if ( 'cv' === $key ) {
		return abc_cv_page_url();
	}
	return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
}

/**
 * "Download CV" target: the PDF if uploaded, otherwise the online CV.
 *
 * @return array url,is_file
 */
function abc_cv_link() {
	$file = abc_cv_file_url();
	if ( $file ) {
		return array( 'url' => $file, 'is_file' => true );
	}
	return array( 'url' => abc_cv_page_url(), 'is_file' => false );
}

/**
 * Portrait <img>. Uses the Media Library photo, or the neutral placeholder.
 *
 * @param string $size  Image size.
 * @param array  $attrs Extra attributes.
 * @return string
 */
function abc_portrait( $size = 'large', $attrs = array() ) {
	$id  = absint( abc_setting( 'photo_id', 0 ) );
	$alt = abc_setting( 'photo_alt', '' );
	if ( ! $alt ) {
		$alt = abc_t( 'portrait_alt' );
	}
	if ( $id && wp_attachment_is_image( $id ) ) {
		$attrs = wp_parse_args( $attrs, array( 'alt' => $alt ) );
		return wp_get_attachment_image( $id, $size, false, $attrs );
	}
	$src = apply_filters( 'abc_portrait_placeholder_url', ABC_URL . 'assets/portrait-placeholder.svg' );
	$cls = isset( $attrs['class'] ) ? $attrs['class'] : '';
	return sprintf(
		'<img src="%s" alt="%s" class="%s" width="640" height="800" decoding="async" %s%s>',
		esc_url( $src ),
		esc_attr( $alt ),
		esc_attr( $cls ),
		isset( $attrs['loading'] ) ? 'loading="' . esc_attr( $attrs['loading'] ) . '" ' : '',
		isset( $attrs['fetchpriority'] ) ? 'fetchpriority="' . esc_attr( $attrs['fetchpriority'] ) . '"' : ''
	);
}

/* -------------------------------------------------------------------------
 * Post meta helpers
 * ---------------------------------------------------------------------- */

/**
 * Get a plugin field value.
 *
 * @param string   $key  Field key without the abc_ prefix.
 * @param int|null $id   Post ID.
 * @return mixed
 */
function abc_get( $key, $id = null ) {
	$id = $id ? $id : get_the_ID();
	return get_post_meta( $id, 'abc_' . $key, true );
}

/**
 * Array of post IDs stored in a relation field.
 *
 * @param string   $key Field key.
 * @param int|null $id  Post ID.
 * @return int[]
 */
function abc_get_ids( $key, $id = null ) {
	$v = abc_get( $key, $id );
	if ( ! is_array( $v ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', $v ) ) );
}

/**
 * Translate a post ID to the current language (Polylang), falling back to the ID itself.
 *
 * @param int $id Post ID.
 * @return int
 */
function abc_translate_id( $id ) {
	if ( function_exists( 'pll_get_post' ) ) {
		$t = pll_get_post( $id, abc_lang() );
		if ( $t ) {
			return (int) $t;
		}
	}
	return (int) $id;
}

/**
 * Related posts from a relation field, published only, translated.
 *
 * @param string   $key       Field key (e.g. project_pubs).
 * @param int|null $post_id   Source post.
 * @param string   $post_type Expected post type.
 * @return WP_Post[]
 */
function abc_related( $key, $post_id = null, $post_type = '' ) {
	$out  = array();
	$seen = array();
	foreach ( abc_get_ids( $key, $post_id ) as $id ) {
		$id = abc_translate_id( $id );
		if ( isset( $seen[ $id ] ) ) {
			continue;
		}
		$p = get_post( $id );
		if ( $p && 'publish' === $p->post_status && ( ! $post_type || $p->post_type === $post_type ) ) {
			$out[]        = $p;
			$seen[ $id ] = true;
		}
	}
	return $out;
}

/**
 * Posts of $post_type whose relation field $meta_key (array of IDs) contains this post.
 *
 * @param int    $post_id   Target post.
 * @param string $post_type Post type holding the relation.
 * @param string $meta_key  Full meta key (abc_...).
 * @param int    $limit     Max.
 * @return WP_Post[]
 */
function abc_reverse_related( $post_id, $post_type, $meta_key, $limit = 12 ) {
	$ids = array( (int) $post_id );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_merge( $ids, array_map( 'intval', array_values( pll_get_post_translations( $post_id ) ) ) );
	}
	$meta_query = array( 'relation' => 'OR' );
	foreach ( array_unique( $ids ) as $id ) {
		$meta_query[] = array(
			'key'     => $meta_key,
			'value'   => '"' . $id . '"',
			'compare' => 'LIKE',
		);
	}
	$q = new WP_Query(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'meta_query'             => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
			'lang'                   => abc_lang(),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);
	return $q->posts;
}

/**
 * Reading time in minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function abc_reading_time( $post_id = null ) {
	$post  = get_post( $post_id );
	$words = $post ? count( preg_split( '/\s+/u', trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ), -1, PREG_SPLIT_NO_EMPTY ) ) : 0;
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Attachment info for download buttons.
 *
 * @param int $attachment_id Attachment.
 * @return array|null url,ext,size,name
 */
function abc_file_info( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id || ! get_post( $attachment_id ) ) {
		return null;
	}
	$url = wp_get_attachment_url( $attachment_id );
	if ( ! $url ) {
		return null;
	}
	$path = get_attached_file( $attachment_id );
	$size = ( $path && file_exists( $path ) ) ? size_format( filesize( $path ) ) : '';
	return array(
		'url'  => $url,
		'ext'  => strtoupper( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ),
		'size' => $size,
		'name' => get_the_title( $attachment_id ),
	);
}

/**
 * Resolve a "file or URL" field pair to a URL.
 *
 * @param string   $id_key  Attachment ID field key.
 * @param string   $url_key External URL field key.
 * @param int|null $post_id Post.
 * @return string
 */
function abc_file_or_url( $id_key, $url_key, $post_id = null ) {
	$info = abc_file_info( abc_get( $id_key, $post_id ) );
	if ( $info ) {
		return $info['url'];
	}
	$u = abc_get( $url_key, $post_id );
	return $u ? esc_url_raw( $u ) : '';
}

/* -------------------------------------------------------------------------
 * Dates
 * ---------------------------------------------------------------------- */

/**
 * Localised date from Y-m-d (or Y-m / Y) without depending on the WP locale files.
 *
 * @param string $ymd Date.
 * @return string
 */
function abc_format_date( $ymd ) {
	$ymd = trim( (string) $ymd );
	if ( ! preg_match( '/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $ymd, $m ) ) {
		return esc_html( $ymd );
	}
	$months = array(
		'en' => array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
		'fr' => array( 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' ),
		'ar' => array( 'جانفي', 'فيفري', 'مارس', 'أفريل', 'ماي', 'جوان', 'جويلية', 'أوت', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ),
	);
	$lang = abc_lang();
	if ( empty( $m[2] ) ) {
		return $m[1];
	}
	$mon = $months[ $lang ][ (int) $m[2] - 1 ] ?? $m[2];
	if ( empty( $m[3] ) ) {
		return $mon . ' ' . $m[1];
	}
	return (int) $m[3] . ' ' . $mon . ' ' . $m[1];
}

/* -------------------------------------------------------------------------
 * Bibliographic formatting
 * ---------------------------------------------------------------------- */

/**
 * Parse an authors textarea (one per line, "Family, Given" or "Given Family").
 *
 * @param string $text Raw text.
 * @return array[] Each: full,family,given
 */
function abc_parse_authors( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n|;/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( '[' === $line[0] ) { // Placeholder such as [ADD VERIFIED INFORMATION]: keep as is.
			$family = $line;
			$given  = '';
		} elseif ( false !== strpos( $line, ',' ) ) {
			list( $family, $given ) = array_map( 'trim', explode( ',', $line, 2 ) );
		} else {
			$parts  = preg_split( '/\s+/u', $line );
			$family = array_pop( $parts );
			$given  = implode( ' ', $parts );
		}
		$out[] = array(
			'full'   => trim( $given . ' ' . $family ),
			'family' => $family,
			'given'  => $given,
		);
	}
	return $out;
}

/**
 * "G. H." style initials.
 *
 * @param string $given Given names.
 * @return string
 */
function abc_initials( $given ) {
	$out = '';
	foreach ( preg_split( '/[\s\-]+/u', trim( $given ), -1, PREG_SPLIT_NO_EMPTY ) as $p ) {
		$out .= mb_strtoupper( mb_substr( $p, 0, 1 ) ) . '. ';
	}
	return trim( $out );
}

/**
 * DOI → URL. Accepts "10.xxxx/yyy" or a doi.org URL.
 *
 * @param string $doi DOI.
 * @return string
 */
function abc_doi_clean( $doi ) {
	$doi = trim( (string) $doi );
	$doi = preg_replace( '#^https?://(dx\.)?doi\.org/#i', '', $doi );
	$doi = preg_replace( '#^doi:\s*#i', '', $doi );
	return preg_match( '#^10\.\d{4,9}/\S+$#', $doi ) ? $doi : '';
}

/**
 * DOI URL.
 *
 * @param string $doi DOI.
 * @return string
 */
function abc_doi_url( $doi ) {
	$doi = abc_doi_clean( $doi );
	return $doi ? 'https://doi.org/' . $doi : '';
}

/**
 * Plain-text pieces of a publication used by citation, BibTeX and schema.
 *
 * @param int $id Post ID.
 * @return array
 */
function abc_pub_data( $id ) {
	$types = get_the_terms( $id, 'abc_pub_type' );
	return array(
		'title'     => get_the_title( $id ),
		'authors'   => abc_parse_authors( abc_get( 'pub_authors', $id ) ),
		'year'      => abc_get( 'pub_year', $id ),
		'journal'   => abc_get( 'pub_journal', $id ),
		'publisher' => abc_get( 'pub_publisher', $id ),
		'volume'    => abc_get( 'pub_volume', $id ),
		'issue'     => abc_get( 'pub_issue', $id ),
		'pages'     => abc_get( 'pub_pages', $id ),
		'doi'       => abc_doi_clean( abc_get( 'pub_doi', $id ) ),
		'url'       => abc_get( 'pub_url', $id ),
		'type_slug' => ( $types && ! is_wp_error( $types ) ) ? $types[0]->slug : '',
		'type_key'  => ( $types && ! is_wp_error( $types ) ) ? (string) get_term_meta( $types[0]->term_id, 'abc_key', true ) : '',
	);
}

/**
 * Human-readable citation (HTML, safe to echo).
 *
 * @param int $id Post ID.
 * @return string
 */
function abc_citation( $id ) {
	$d     = abc_pub_data( $id );
	$names = array();
	foreach ( $d['authors'] as $a ) {
		$names[] = $a['given'] ? $a['family'] . ', ' . abc_initials( $a['given'] ) : $a['family'];
	}
	$authors = $names ? implode( '; ', array_map( 'esc_html', $names ) ) : '';
	$html    = $authors;
	$html   .= ( $html ? ' ' : '' ) . '(' . ( $d['year'] ? esc_html( $d['year'] ) : esc_html( abc_t( 'n_d' ) ) ) . '). ';
	$html   .= esc_html( $d['title'] ) . '. ';
	if ( $d['journal'] ) {
		$html .= '<i>' . esc_html( $d['journal'] ) . '</i>';
		if ( $d['volume'] ) {
			$html .= ', ' . esc_html( $d['volume'] );
			if ( $d['issue'] ) {
				$html .= '(' . esc_html( $d['issue'] ) . ')';
			}
		}
		if ( $d['pages'] ) {
			$html .= ', ' . esc_html( $d['pages'] );
		}
		$html .= '. ';
	}
	if ( $d['publisher'] ) {
		$html .= esc_html( $d['publisher'] ) . '. ';
	}
	if ( $d['doi'] ) {
		$html .= esc_html( abc_doi_url( $d['doi'] ) );
	}
	return trim( $html );
}

/**
 * BibTeX entry (plain text).
 *
 * @param int $id Post ID.
 * @return string
 */
function abc_bibtex( $id ) {
	$d     = abc_pub_data( $id );
	$map   = array(
		'journal-article'   => 'article',
		'conference-paper'  => 'inproceedings',
		'book-chapter'      => 'incollection',
		'book'              => 'book',
		'thesis'            => 'phdthesis',
		'report'            => 'techreport',
	);
	$type  = $map[ $d['type_key'] ] ?? 'misc';
	$first = $d['authors'] ? $d['authors'][0]['family'] : 'abc';
	$key   = strtolower( preg_replace( '/[^A-Za-z0-9]/', '', remove_accents( $first ) ) ) . $d['year'] . $id;
	$esc   = static function ( $s ) {
		return str_replace( array( '{', '}' ), '', (string) $s );
	};
	$f = array();
	if ( $d['authors'] ) {
		$f['author'] = implode( ' and ', array_map( static function ( $a ) {
			return $a['given'] ? $a['family'] . ', ' . $a['given'] : $a['family'];
		}, $d['authors'] ) );
	}
	$f['title'] = $d['title'];
	if ( $d['journal'] ) {
		$f[ 'article' === $type ? 'journal' : 'booktitle' ] = $d['journal'];
	}
	foreach ( array( 'year', 'volume', 'publisher', 'pages' ) as $k ) {
		if ( $d[ $k ] ) {
			$f[ $k ] = $d[ $k ];
		}
	}
	if ( $d['issue'] ) {
		$f['number'] = $d['issue'];
	}
	if ( $d['doi'] ) {
		$f['doi'] = $d['doi'];
	}
	$out = '@' . $type . '{' . $key . ",\n";
	foreach ( $f as $k => $v ) {
		$out .= '  ' . $k . ' = {' . $esc( $v ) . "},\n";
	}
	return rtrim( $out, ",\n" ) . "\n}";
}

/* -------------------------------------------------------------------------
 * Misc
 * ---------------------------------------------------------------------- */

/**
 * Is Yoast SEO or Rank Math active?
 *
 * @return bool
 */
function abc_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Archive URL for a post type (language aware).
 *
 * @param string $post_type Post type.
 * @return string
 */
function abc_archive_url( $post_type ) {
	$u = get_post_type_archive_link( $post_type );
	return $u ? $u : home_url( '/' );
}

/**
 * Labels for select-type fields.
 *
 * @param string $group  Group: oa, project_status, pres_type.
 * @param string $value  Stored value.
 * @return string
 */
function abc_option_label( $group, $value ) {
	if ( '' === (string) $value ) {
		return '';
	}
	$key = $group . '_' . $value;
	$t   = abc_t( $key );
	return $t === $key ? ucfirst( str_replace( '-', ' ', $value ) ) : $t;
}

/**
 * Whether this post is demo content (visible badge so it gets replaced).
 *
 * @param int|null $id Post.
 * @return bool
 */
function abc_is_demo( $id = null ) {
	$id = $id ? $id : get_the_ID();
	return (bool) get_post_meta( $id, '_abc_demo', true );
}

/**
 * Inline SVG icons (stroke style, no external requests).
 *
 * @param string $name Icon name.
 * @param int    $size Pixel size.
 * @return string
 */
function abc_icon( $name, $size = 24 ) {
	static $paths = null;
	if ( null === $paths ) {
		$badge = static function ( $t, $fs = 8.5 ) {
			return '<circle cx="12" cy="12" r="9.5"/><text x="12" y="15.2" text-anchor="middle" font-size="' . $fs . '" font-weight="700" fill="currentColor" stroke="none" font-family="Inter,Arial,sans-serif">' . $t . '</text>';
		};
		$paths = array(
			'library'       => '<path d="M4 4h4v16H4zM10 4h4v16h-4z"/><path d="M17 5.5l3.4 1-3.9 13.5-3.4-1z"/>',
			'repository'    => '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
			'openaccess'    => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 7.6-1.7"/><circle cx="12" cy="15.5" r="1"/>',
			'communication' => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/>',
			'metadata'      => '<path d="M3 12V4h8l9 9-8 8z"/><circle cx="7.5" cy="8.5" r="1.2"/>',
			'ai'            => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4M10 12h4"/>',
			'data'          => '<path d="M4 20V4M4 20h16M8 16v-4M12 16V8M16 16v-6"/>',
			'transform'     => '<path d="M4 12a8 8 0 0 1 14-5l2 2M20 12a8 8 0 0 1-14 5l-2-2M20 4v5h-5M4 20v-5h5"/>',
			'download'      => '<path d="M12 3v12m0 0l-4-4m4 4l4-4M4 20h16"/>',
			'external'      => '<path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/>',
			'doc'           => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 13h6M9 17h6"/>',
			'mail'          => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
			'search'        => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l5 5"/>',
			'menu'          => '<path d="M4 7h16M4 12h16M4 17h16"/>',
			'close'         => '<path d="M6 6l12 12M18 6L6 18"/>',
			'arrow'         => '<path d="M5 12h14m-5-5l5 5-5 5"/>',
			'calendar'      => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M8 3v4M16 3v4"/>',
			'location'      => '<path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
			'video'         => '<rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3z"/>',
			'slides'        => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M12 16v4M8 20h8"/>',
			'quote'         => '<path d="M5 17c0-5 1.5-8 5-10M13 17c0-5 1.5-8 5-10M5 17h5v-4H5zM13 17h5v-4h-5z"/>',
			'link'          => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3A4 4 0 0 0 11 18.7l1-1"/>',
			'graduation'    => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-4.5"/>',
			'user'          => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
			'check'         => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
			'tag'           => '<path d="M3 12V4h8l9 9-8 8z"/>',
			'institution'   => '<path d="M3 10l9-6 9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 20h18"/>',
			'orcid'         => $badge( 'iD', 8.5 ),
			'scholar'       => $badge( 'GS', 8 ),
			'scopus'        => $badge( 'S', 10 ),
			'researchgate'  => $badge( 'RG', 7.5 ),
			'linkedin'      => $badge( 'in', 9 ),
		);
	}
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="abc-icon abc-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput -- static, trusted markup.
	);
}

/**
 * Whitelisted attributes for wp_kses of our own markup (SVG icons).
 *
 * @return array
 */
function abc_kses_svg() {
	return array(
		'svg'     => array( 'class' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true, 'focusable' => true ),
		'path'    => array( 'd' => true ),
		'circle'  => array( 'cx' => true, 'cy' => true, 'r' => true ),
		'ellipse' => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ),
		'rect'    => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
		'text'    => array( 'x' => true, 'y' => true, 'text-anchor' => true, 'font-size' => true, 'font-weight' => true, 'fill' => true, 'stroke' => true, 'font-family' => true ),
	);
}
