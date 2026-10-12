<?php
/**
 * Front-end markup (ABC_Render) and shortcodes. Shortcodes work in the block editor
 * through the "Shortcode" block and in classic content.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Render {

	/** Heading level of item titles (templates set 'h2' on archives so heading order stays valid). */
	public static $level = 'h3';

	/** Map of short type names to post types. */
	public static function type_map() {
		return array(
			'publication'  => 'abc_publication',
			'book'         => 'abc_book',
			'project'      => 'abc_project',
			'presentation' => 'abc_presentation',
			'service'      => 'abc_service',
			'teaching'     => 'abc_teaching',
			'note'         => 'abc_note',
		);
	}

	/**
	 * Button / link.
	 *
	 * @param string $url   URL.
	 * @param string $label Label.
	 * @param string $icon  Icon name.
	 * @param string $class Extra classes.
	 * @param array  $attrs Extra attributes.
	 * @return string
	 */
	public static function button( $url, $label, $icon = '', $class = 'abc-btn abc-btn--ghost abc-btn--sm', $attrs = array() ) {
		if ( ! $url ) {
			return '';
		}
		$external = wp_parse_url( $url, PHP_URL_HOST ) && wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST );
		$extra    = '';
		foreach ( $attrs as $k => $v ) {
			$extra .= ' ' . esc_attr( $k ) . ( true === $v ? '' : '="' . esc_attr( $v ) . '"' );
		}
		return sprintf(
			'<a class="%s" href="%s"%s%s>%s<span>%s</span></a>',
			esc_attr( $class ),
			esc_url( $url ),
			$external ? ' rel="noopener noreferrer"' : '',
			$extra,
			$icon ? abc_icon( $icon, 18 ) : '',
			esc_html( $label )
		);
	}

	/**
	 * Small coloured badge.
	 *
	 * @param string $text  Text.
	 * @param string $class Modifier.
	 * @return string
	 */
	public static function badge( $text, $class = '' ) {
		return $text ? '<span class="abc-badge ' . esc_attr( $class ) . '">' . esc_html( $text ) . '</span>' : '';
	}

	/**
	 * Terms of a taxonomy as a comma list of names.
	 *
	 * @param int    $id       Post.
	 * @param string $taxonomy Taxonomy.
	 * @param bool   $link     Link terms.
	 * @return string HTML
	 */
	public static function terms( $id, $taxonomy, $link = true ) {
		$terms = get_the_terms( $id, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		$out = array();
		foreach ( $terms as $t ) {
			$out[] = $link ? '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>' : esc_html( $t->name );
		}
		return implode( ', ', $out );
	}

	/**
	 * Demo badge if flagged.
	 *
	 * @param int $id Post.
	 * @return string
	 */
	public static function demo_badge( $id ) {
		return abc_is_demo( $id ) ? self::badge( abc_t( 'demo' ), 'abc-badge--demo' ) : '';
	}

	/**
	 * Publication list item.
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function publication_item( $post ) {
		$id   = $post->ID;
		$d    = abc_pub_data( $id );
		$auth = array();
		foreach ( $d['authors'] as $a ) {
			$auth[] = $a['given'] ? $a['family'] . ', ' . abc_initials( $a['given'] ) : $a['family'];
		}
		$venue = '';
		if ( $d['journal'] ) {
			$venue = '<i>' . esc_html( $d['journal'] ) . '</i>';
			if ( $d['volume'] ) {
				$venue .= ', ' . esc_html( $d['volume'] ) . ( $d['issue'] ? '(' . esc_html( $d['issue'] ) . ')' : '' );
			}
			if ( $d['pages'] ) {
				$venue .= ', ' . esc_html( $d['pages'] );
			}
		}
		$oa    = abc_get( 'pub_oa', $id );
		$pdf   = abc_file_or_url( 'pub_pdf_id', 'pub_pdf_url', $id );
		$repo  = abc_get( 'pub_repo_url', $id );
		$doi   = abc_doi_url( $d['doi'] );
		$type  = self::terms( $id, 'abc_pub_type', false );
		$html  = '<article class="abc-pub" id="pub-' . (int) $id . '">';
		$html .= '<div class="abc-pub__year" aria-label="' . esc_attr( abc_t( 'year' ) ) . '">' . ( $d['year'] ? esc_html( $d['year'] ) : '—' ) . '</div><div class="abc-pub__body">';
		$html .= '<' . self::$level . ' class="abc-pub__title"><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></' . self::$level . '>';
		if ( $auth ) {
			$html .= '<p class="abc-pub__authors">' . esc_html( implode( '; ', $auth ) ) . '</p>';
		}
		if ( $venue ) {
			$html .= '<p class="abc-pub__venue">' . $venue . '</p>';
		}
		$html .= '<p class="abc-pub__tags">' . self::badge( wp_strip_all_tags( $type ), 'abc-badge--type' ) . ( $oa && 'closed' !== $oa ? self::badge( abc_t( 'open_access' ), 'abc-badge--oa' ) : '' ) . self::demo_badge( $id ) . '</p>';
		$btn   = self::button( $doi, 'DOI', 'link' ) . self::button( $pdf, 'PDF', 'doc' ) . self::button( $repo, abc_t( 'repository' ), 'repository' );
		if ( $btn ) {
			$html .= '<p class="abc-pub__links">' . $btn . '</p>';
		}
		return $html . '</div></article>';
	}

	/**
	 * Generic card for any post.
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function card( $post ) {
		$id    = $post->ID;
		$pt    = $post->post_type;
		$kick  = '';
		$meta  = '';
		$extra = '';
		switch ( $pt ) {
			case 'abc_project':
				$kick  = self::terms( $id, 'abc_research_area', false );
				$st    = abc_option_label( 'project_status', abc_get( 'project_status', $id ) );
				$meta  = $st ? self::badge( $st, 'abc-badge--status' ) : '';
				$extra = abc_get( 'project_question', $id );
				break;
			case 'abc_book':
				$kick = abc_get( 'book_year', $id );
				$meta = abc_get( 'book_author', $id ) ? esc_html( abc_get( 'book_author', $id ) ) : '';
				break;
			case 'abc_presentation':
				$ptype = wp_strip_all_tags( self::terms( $id, 'abc_pres_type', false ) );
				$kick  = $ptype ? esc_html( $ptype ) : '';
				$kick .= ( $kick && abc_get( 'pres_date', $id ) ? ' · ' : '' ) . ( abc_get( 'pres_date', $id ) ? esc_html( abc_format_date( abc_get( 'pres_date', $id ) ) ) : '' );
				$meta = esc_html( trim( abc_get( 'pres_conference', $id ) . ( abc_get( 'pres_location', $id ) ? ' — ' . abc_get( 'pres_location', $id ) : '' ) ) );
				break;
			case 'abc_note':
				$kick = self::terms( $id, 'abc_note_category', false );
				$meta = esc_html( abc_format_date( get_the_date( 'Y-m-d', $id ) ) ) . ' · ' . esc_html( abc_t( 'min_read', abc_reading_time( $id ) ) );
				break;
			case 'abc_teaching':
				$kick = self::terms( $id, 'abc_teaching_section', false );
				$meta = esc_html( abc_get( 'teach_institution', $id ) );
				break;
			default:
				$kick = ( 'page' === $pt ) ? abc_t( 'page' ) : '';
		}
		if ( 'abc_book' === $pt || 'abc_note' === $pt || has_post_thumbnail( $id ) ) {
			$img = has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ) : '';
		} else {
			$img = '';
		}
		$excerpt = has_excerpt( $id ) ? get_the_excerpt( $id ) : ( $extra ? $extra : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 26 ) );
		$html    = '<article class="abc-card abc-card--' . esc_attr( str_replace( 'abc_', '', $pt ) ) . '">';
		if ( $img ) {
			$html .= '<a class="abc-card__media" href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>';
		}
		$html .= '<div class="abc-card__body">';
		if ( $kick ) {
			$html .= '<p class="abc-card__kicker">' . wp_kses_post( $kick ) . '</p>';
		}
		$html .= '<' . self::$level . ' class="abc-card__title"><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></' . self::$level . '>';
		if ( $excerpt ) {
			$html .= '<p class="abc-card__excerpt">' . esc_html( wp_trim_words( $excerpt, 28 ) ) . '</p>';
		}
		$html .= '<p class="abc-card__meta">' . wp_kses_post( $meta ) . self::demo_badge( $id ) . '</p>';
		$html .= '</div></article>';
		return $html;
	}

	/**
	 * Research-area card.
	 *
	 * @param WP_Term $term Term.
	 * @return string
	 */
	public static function area_card( $term ) {
		$icon = get_term_meta( $term->term_id, 'abc_icon', true );
		$icon = $icon ? $icon : 'library';
		$count = static function ( $pt ) use ( $term ) {
			$q = new WP_Query(
				array(
					'post_type'      => $pt,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'tax_query'      => array( array( 'taxonomy' => 'abc_research_area', 'field' => 'term_id', 'terms' => $term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
					'lang'           => abc_lang(),
				)
			);
			return (int) $q->found_posts;
		};
		$pubs  = $count( 'abc_publication' );
		$projs = $count( 'abc_project' );
		$link  = get_term_link( $term );
		$img_id = abc_area_image_id( $term->term_id );
		$bg     = $img_id ? wp_get_attachment_image( $img_id, 'medium_large', false, array( 'class' => 'abc-area__bg', 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) . '<span class="abc-area__shade" aria-hidden="true"></span>' : '';
		$html   = '<article class="abc-area' . ( $bg ? ' abc-area--img' : '' ) . '"><header class="abc-area__head"><div class="abc-area__icon">' . abc_icon( $icon, 24 ) . '</div>';
		$html  .= '<h3 class="abc-area__title"><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></h3></header>';
		$html  .= $bg ? '<div class="abc-area__media">' . $bg . '</div>' : '';
		$html  .= '<p class="abc-area__desc">' . esc_html( $term->description ) . '</p><ul class="abc-area__related">';
		$html  .= '<li><a href="' . esc_url( add_query_arg( 'pub_area', $term->slug, abc_archive_url( 'abc_publication' ) ) ) . '"><strong>' . (int) $pubs . '</strong> ' . esc_html( abc_t( 'related_publications' ) ) . '</a></li>';
		$html  .= '<li><a href="' . esc_url( $link ) . '#projects"><strong>' . (int) $projs . '</strong> ' . esc_html( abc_t( 'related_projects' ) ) . '</a></li></ul></article>';
		return $html;
	}

	/**
	 * Research areas grid.
	 *
	 * @param int $limit Max.
	 * @return string
	 */
	public static function research_areas( $limit = 8 ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'abc_research_area',
				'hide_empty' => false,
				'number'     => $limit,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'OR',
					'o'        => array( 'key' => 'abc_order', 'type' => 'NUMERIC', 'compare' => 'EXISTS' ),
					array( 'key' => 'abc_order', 'compare' => 'NOT EXISTS' ),
				),
				'orderby'    => 'o',
				'order'      => 'ASC',
			)
		);
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '<p class="abc-empty">' . esc_html( abc_t( 'no_areas' ) ) . '</p>';
		}
		$out = '<div class="abc-grid abc-grid--areas">';
		foreach ( $terms as $t ) {
			$out .= self::area_card( $t );
		}
		return $out . '</div>';
	}

	/**
	 * Statistics block.
	 *
	 * @return string
	 */
	public static function stats() {
		$stats = abc_stats();
		if ( ! $stats ) {
			return '';
		}
		$out = '<dl class="abc-stats">';
		foreach ( $stats as $s ) {
			$out .= '<div class="abc-stat"><dt class="abc-stat__label">' . esc_html( $s['label'] ) . '</dt><dd class="abc-stat__value">' . esc_html( $s['value'] ) . '</dd></div>';
		}
		return $out . '</dl>';
	}

	/**
	 * Profile icons.
	 *
	 * @param string $style icons|list.
	 * @return string
	 */
	public static function profiles( $style = 'icons' ) {
		$p = abc_profiles();
		if ( ! $p ) {
			return '';
		}
		$out = '<ul class="abc-profiles abc-profiles--' . esc_attr( $style ) . '" aria-label="' . esc_attr( abc_t( 'research_profiles' ) ) . '">';
		foreach ( $p as $row ) {
			$out .= '<li><a href="' . esc_url( $row['url'] ) . '" rel="me noopener noreferrer"><span class="abc-profiles__icon">' . abc_icon( $row['icon'], 22 ) . '</span><span class="' . ( 'icons' === $style ? 'screen-reader-text' : 'abc-profiles__label' ) . '">' . esc_html( $row['label'] ) . '</span></a></li>';
		}
		return $out . '</ul>';
	}

	/**
	 * Contact card.
	 *
	 * @return string
	 */
	public static function contact_card() {
		$email = abc_setting( 'email', 'contact@ahcenebabori.com' );
		$li    = abc_setting( 'linkedin', '' );
		$aff   = abc_ml( 'affiliation' );
		$out   = '<address class="abc-contact-card"><p class="abc-contact-card__name">' . esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ) . '</p>';
		$out  .= '<p>' . esc_html( abc_t( 'assistant_professor' ) ) . '</p><p>' . esc_html( abc_t( 'lis' ) ) . '</p>';
		if ( $aff && abc_t( 'default_affiliation' ) !== $aff ) {
			$out .= '<p>' . esc_html( $aff ) . '</p>';
		}
		$out .= '<ul class="abc-contact-card__list">';
		$out .= '<li>' . abc_icon( 'mail', 20 ) . '<span class="abc-contact-card__k">' . esc_html( abc_t( 'email' ) ) . '</span> <a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></li>';
		if ( $li ) {
			$out .= '<li>' . abc_icon( 'linkedin', 20 ) . '<span class="abc-contact-card__k">LinkedIn</span> <a href="' . esc_url( $li ) . '" rel="me noopener noreferrer">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $li ) ) ) . '</a></li>';
		}
		$out .= '</ul>' . self::profiles( 'icons' ) . '</address>';
		return $out;
	}

	/**
	 * Academic service timeline.
	 *
	 * @param WP_Post[] $posts Posts.
	 * @return string
	 */
	public static function timeline( $posts ) {
		if ( ! $posts ) {
			return '<p class="abc-empty">' . esc_html( abc_t( 'nothing_yet' ) ) . '</p>';
		}
		$out = '<ol class="abc-timeline">';
		foreach ( $posts as $p ) {
			$id   = $p->ID;
			$from = abc_get( 'svc_from', $id );
			$to   = abc_get( 'svc_to', $id );
			$date = $from ? abc_format_date( $from ) : '';
			if ( abc_get( 'svc_ongoing', $id ) ) {
				$date .= ( $date ? ' – ' : '' ) . abc_t( 'present' );
			} elseif ( $to ) {
				$date .= ( $date ? ' – ' : '' ) . abc_format_date( $to );
			}
			$org  = abc_get( 'svc_org', $id );
			$pos  = abc_get( 'svc_position', $id );
			$url  = abc_get( 'svc_url', $id );
			$desc = abc_get( 'svc_description', $id );
			$out .= '<li class="abc-timeline__item"><div class="abc-timeline__date">' . esc_html( $date ? $date : '—' ) . '</div><div class="abc-timeline__body">';
			$out .= '<p class="abc-timeline__cat">' . wp_kses_post( self::terms( $id, 'abc_service_type', false ) ) . self::demo_badge( $id ) . '</p>';
			$out .= '<' . self::$level . ' class="abc-timeline__title">' . esc_html( $pos ? $pos : get_the_title( $id ) ) . ( $org ? ' <span class="abc-timeline__org">— ' . esc_html( $org ) . '</span>' : '' ) . '</' . self::$level . '>';
			if ( $desc ) {
				$out .= '<p>' . esc_html( $desc ) . '</p>';
			}
			if ( $url ) {
				$out .= '<p>' . self::button( $url, abc_t( 'visit' ), 'external' ) . '</p>';
			}
			$out .= '</div></li>';
		}
		return $out . '</ol>';
	}

	/**
	 * About / CV timeline built from "About Timeline" entries.
	 *
	 * @param array $args section (term key or slug), heading (bool).
	 * @return string
	 */
	public static function career( $args = array() ) {
		$args  = wp_parse_args( $args, array( 'section' => '', 'heading' => true ) );
		$terms = get_terms(
			array(
				'taxonomy'   => 'abc_career_section',
				'hide_empty' => true,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'OR',
					'o'        => array( 'key' => 'abc_order', 'type' => 'NUMERIC', 'compare' => 'EXISTS' ),
					array( 'key' => 'abc_order', 'compare' => 'NOT EXISTS' ),
				),
				'orderby'    => 'o',
				'order'      => 'ASC',
			)
		);
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}
		$out = '';
		$n   = 0;
		foreach ( $terms as $t ) {
			if ( $args['section'] && $t->slug !== $args['section'] && get_term_meta( $t->term_id, 'abc_key', true ) !== $args['section'] ) {
				continue;
			}
			$posts = get_posts(
				array(
					'post_type'      => 'abc_career',
					'post_status'    => 'publish',
					'posts_per_page' => 100,
					'lang'           => abc_lang(),
					'tax_query'      => array( array( 'taxonomy' => 'abc_career_section', 'field' => 'term_id', 'terms' => $t->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						'relation' => 'OR',
						'c'        => array( 'key' => 'abc_career_start', 'type' => 'NUMERIC', 'compare' => 'EXISTS' ),
						array( 'key' => 'abc_career_start', 'compare' => 'NOT EXISTS' ),
					),
					'orderby'        => array( 'c' => 'DESC', 'menu_order' => 'ASC' ),
				)
			);
			if ( ! $posts ) {
				continue;
			}
			$n++;
			$out .= '<section class="abt-chrono">';
			if ( $args['heading'] ) {
				$out .= '<h2 class="abt-chrono__bar">' . (int) $n . '. ' . esc_html( $t->name ) . '</h2>';
			}
			$out .= '<ol class="abt-chrono__list">';
			foreach ( $posts as $p ) {
				$id     = $p->ID;
				$period = abc_get( 'career_period', $id );
				$period = $period ? $period : abc_get( 'career_start', $id );
				$badge  = abc_get( 'career_badge', $id );
				$org    = abc_get( 'career_org', $id );
				$det    = esc_html( abc_get( 'career_detail', $id ) );
				$det    = preg_replace( '/“([^”]+)”/u', '<em>“$1”</em>', $det );
				$out   .= '<li class="abt-chrono__item"><div class="abt-chrono__date">' . esc_html( $period ) . '</div><h3 class="abt-chrono__title">' . esc_html( get_the_title( $id ) ) . ( $badge ? ' <span class="abc-badge">' . esc_html( $badge ) . '</span>' : '' ) . '</h3>';
				$out   .= ( $org ? '<p class="abt-chrono__org">' . esc_html( $org ) . '</p>' : '' ) . ( $det ? '<p class="abt-chrono__detail">' . $det . '</p>' : '' ) . '</li>';
			}
			$out .= '</ol></section>';
		}
		return $out;
	}

	/**
	 * Compact CV list line for a post.
	 *
	 * @param WP_Post $p Post.
	 * @return string
	 */
	public static function cv_line( $p ) {
		$id = $p->ID;
		switch ( $p->post_type ) {
			case 'abc_publication':
				return '<li>' . abc_citation( $id ) . '</li>';
			case 'abc_book':
				$bits = array_filter( array( abc_get( 'book_author', $id ), '(' . abc_get( 'book_year', $id ) . ')', '<i>' . esc_html( get_the_title( $id ) ) . '</i>', abc_get( 'book_publisher', $id ) ) );
				return '<li>' . implode( '. ', array_map( static function ( $b ) { return false !== strpos( $b, '<i>' ) ? $b : esc_html( $b ); }, $bits ) ) . '</li>';
			case 'abc_presentation':
				return '<li>' . esc_html( get_the_title( $id ) ) . ', ' . esc_html( abc_get( 'pres_conference', $id ) ) . ( abc_get( 'pres_date', $id ) ? ', ' . esc_html( abc_format_date( abc_get( 'pres_date', $id ) ) ) : '' ) . '</li>';
			case 'abc_service':
				return '<li><strong>' . esc_html( abc_get( 'svc_position', $id ) ? abc_get( 'svc_position', $id ) : get_the_title( $id ) ) . '</strong>' . ( abc_get( 'svc_org', $id ) ? ', ' . esc_html( abc_get( 'svc_org', $id ) ) : '' ) . ( abc_get( 'svc_from', $id ) ? ' (' . esc_html( abc_get( 'svc_from', $id ) ) . ( abc_get( 'svc_ongoing', $id ) ? '–' . esc_html( abc_t( 'present' ) ) : ( abc_get( 'svc_to', $id ) ? '–' . esc_html( abc_get( 'svc_to', $id ) ) : '' ) ) . ')' : '' ) . '</li>';
			default:
				return '<li><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></li>';
		}
	}

	/**
	 * Query helper used by [abc_list].
	 *
	 * @param string $type  Short type.
	 * @param array  $args  limit, featured, area, order.
	 * @return WP_Post[]
	 */
	public static function query( $type, $args = array() ) {
		$map = self::type_map();
		$pt  = $map[ $type ] ?? '';
		if ( ! $pt ) {
			return array();
		}
		$q = array(
			'post_type'      => $pt,
			'post_status'    => 'publish',
			'posts_per_page' => isset( $args['limit'] ) ? (int) $args['limit'] : 6,
			'lang'           => abc_lang(),
			'no_found_rows'  => true,
		);
		$meta = array();
		switch ( $pt ) {
			case 'abc_publication':
				$q['meta_key'] = 'abc_pub_year'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$q['orderby']  = array( 'meta_value_num' => 'DESC', 'date' => 'DESC' );
				if ( ! empty( $args['featured'] ) ) {
					$meta[] = array( 'key' => 'abc_pub_featured', 'value' => '1' );
				}
				break;
			case 'abc_project':
				$q['orderby'] = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
				if ( ! empty( $args['featured'] ) ) {
					$meta[] = array( 'key' => 'abc_project_featured', 'value' => '1' );
				}
				break;
			case 'abc_service':
				$q['meta_key'] = 'abc_svc_from'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$q['orderby']  = array( 'meta_value' => 'DESC', 'date' => 'DESC' );
				break;
			case 'abc_teaching':
				$q['orderby'] = array( 'menu_order' => 'ASC', 'title' => 'ASC' );
				break;
			case 'abc_presentation':
				$q['meta_key'] = 'abc_pres_date'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$q['orderby']  = array( 'meta_value' => 'DESC', 'date' => 'DESC' );
				break;
		}
		if ( $meta ) {
			$q['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( ! empty( $args['area'] ) ) {
			$q['tax_query'] = array( array( 'taxonomy' => 'abc_research_area', 'field' => 'slug', 'terms' => array_map( 'sanitize_title', explode( ',', $args['area'] ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$posts = get_posts( $q );
		// Featured selection falling back to latest when nothing flagged.
		if ( ! $posts && ! empty( $args['featured'] ) ) {
			unset( $q['meta_query'] );
			$posts = get_posts( $q );
		}
		return $posts;
	}
}

class ABC_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public static function init() {
		$codes = array( 'abc_stats', 'abc_research_areas', 'abc_profiles', 'abc_cv_button', 'abc_publications', 'abc_list', 'abc_contact_card', 'abc_search_form', 'abc_link', 'abc_career' );
		foreach ( $codes as $c ) {
			add_shortcode( $c, array( __CLASS__, str_replace( 'abc_', 'sc_', $c ) ) );
		}
	}

	public static function sc_stats() {
		return ABC_Render::stats();
	}

	public static function sc_research_areas( $atts ) {
		$a   = shortcode_atts( array( 'limit' => 8 ), $atts, 'abc_research_areas' );
		$old = ABC_Render::$level;
		if ( ! is_front_page() ) {
			ABC_Render::$level = 'h2'; // Page content normally has no heading before the cards.
		}
		$out               = ABC_Render::research_areas( (int) $a['limit'] );
		ABC_Render::$level = $old;
		return $out;
	}

	public static function sc_profiles( $atts ) {
		$a = shortcode_atts( array( 'style' => 'icons' ), $atts, 'abc_profiles' );
		return ABC_Render::profiles( 'list' === $a['style'] ? 'list' : 'icons' );
	}

	public static function sc_cv_button( $atts ) {
		$a    = shortcode_atts( array( 'label' => '' ), $atts, 'abc_cv_button' );
		$link = abc_cv_link();
		return ABC_Render::button( $link['url'], $a['label'] ? $a['label'] : abc_t( 'download_full_cv' ), 'download', 'abc-btn abc-btn--primary', $link['is_file'] ? array( 'download' => true ) : array() );
	}

	public static function sc_search_form() {
		return ABC_Query::search_form();
	}

	public static function sc_contact_card() {
		return ABC_Render::contact_card();
	}

	/**
	 * [abc_career section="education" heading="1"] – timeline from the About Timeline entries.
	 */
	public static function sc_career( $atts ) {
		$a = shortcode_atts( array( 'section' => '', 'heading' => '1' ), $atts, 'abc_career' );
		return ABC_Render::career( array( 'section' => sanitize_title( $a['section'] ), 'heading' => '0' !== $a['heading'] ) );
	}

	/**
	 * [abc_link page="contact" label="contact page"] – language-aware link to a starter page.
	 */
	public static function sc_link( $atts ) {
		$a = shortcode_atts( array( 'page' => 'contact', 'label' => '' ), $atts, 'abc_link' );
		return '<a href="' . esc_url( abc_page_url( $a['page'] ) ) . '">' . esc_html( $a['label'] ? $a['label'] : $a['page'] ) . '</a>';
	}

	/**
	 * [abc_publications limit="10" featured="1" area="slug" filters="1"]
	 */
	public static function sc_publications( $atts ) {
		$a     = shortcode_atts( array( 'limit' => 10, 'featured' => '', 'area' => '', 'filters' => '' ), $atts, 'abc_publications' );
		$posts = ABC_Render::query( 'publication', $a );
		$out   = $a['filters'] ? ABC_Query::publication_filters() : '';
		if ( ! $posts ) {
			return $out . '<p class="abc-empty">' . esc_html( abc_t( 'nothing_yet' ) ) . '</p>';
		}
		$out .= '<div class="abc-publist">';
		foreach ( $posts as $p ) {
			$out .= ABC_Render::publication_item( $p );
		}
		return $out . '</div>';
	}

	/**
	 * [abc_list type="project" limit="3" featured="1" layout="cards|cv|timeline" area=""]
	 */
	public static function sc_list( $atts ) {
		$a     = shortcode_atts( array( 'type' => 'project', 'limit' => 6, 'featured' => '', 'layout' => 'cards', 'area' => '' ), $atts, 'abc_list' );
		$posts = ABC_Render::query( sanitize_key( $a['type'] ), $a );
		if ( ! $posts ) {
			return '<p class="abc-empty">' . esc_html( abc_t( 'nothing_yet' ) ) . '</p>';
		}
		if ( 'timeline' === $a['layout'] ) {
			return ABC_Render::timeline( $posts );
		}
		if ( 'cv' === $a['layout'] ) {
			$out = '<ul class="abc-cvlist">';
			foreach ( $posts as $p ) {
				$out .= ABC_Render::cv_line( $p );
			}
			return $out . '</ul>';
		}
		$out = '<div class="abc-grid abc-grid--cards">';
		foreach ( $posts as $p ) {
			$out .= ABC_Render::card( $p );
		}
		return $out . '</div>';
	}
}
