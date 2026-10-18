<?php
/**
 * Mega menu: the main-menu entries for Publications, Projects, Presentations, Teaching, Academic
 * Service and Blog open a wide panel with their categories and latest items (built from the
 * content, nothing to configure). Works with hover, keyboard focus and a toggle button.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Panel definition per post type: taxonomies to list.
 */
function abt_mega_map() {
	return array(
		'abc_publication'  => array( 'abc_pub_type', 'abc_research_area' ),
		'abc_project'      => array( 'abc_research_area' ),
		'abc_presentation' => array( 'abc_pres_type', 'abc_research_area' ),
		'abc_teaching'     => array( 'abc_teaching_section' ),
		'abc_service'      => array( 'abc_service_type' ),
		'abc_note'         => array( 'abc_note_category' ),
	);
}

function abt_mega_heading( $taxonomy ) {
	return in_array( $taxonomy, array( 'abc_pub_type', 'abc_pres_type' ), true ) ? abc_t( 'type' ) : ( 'abc_research_area' === $taxonomy ? abc_t( 'research_area' ) : abc_t( 'categories' ) );
}

/**
 * Panel HTML (cached for ten minutes per language).
 */
function abt_mega_panel( $pt, $id ) {
	$map = abt_mega_map();
	if ( ! isset( $map[ $pt ] ) ) {
		return '';
	}
	$key  = 'abt_mega_' . $pt . '_' . abc_lang();
	$html = get_transient( $key );
	if ( false === $html ) {
		$html = '<div class="abt-mega" id="' . esc_attr( $id ) . '" role="region" aria-label="' . esc_attr( abc_pt_label( $pt ) ) . '"><div class="abt-container abt-mega__grid">';
		foreach ( $map[ $pt ] as $tax ) {
			$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'number' => 8 ) );
			if ( ! $terms || is_wp_error( $terms ) ) {
				continue;
			}
			$html .= '<div class="abt-mega__col"><p class="abt-mega__title">' . esc_html( abt_mega_heading( $tax ) ) . '</p><ul>';
			foreach ( $terms as $t ) {
				$link  = 'abc_teaching_section' === $tax ? abc_archive_url( $pt ) . '#' . $t->slug : get_term_link( $t );
				$html .= '<li><a href="' . esc_url( $link ) . '">' . esc_html( $t->name ) . '</a></li>';
			}
			$html .= '</ul></div>';
		}
		$latest = get_posts( array( 'post_type' => $pt, 'posts_per_page' => 4, 'lang' => abc_lang(), 'no_found_rows' => true ) );
		if ( $latest ) {
			$html .= '<div class="abt-mega__col abt-mega__col--latest"><p class="abt-mega__title">' . esc_html( abc_t( 'latest' ) ) . '</p><ul>';
			foreach ( $latest as $p ) {
				$html .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( wp_trim_words( get_the_title( $p ), 9 ) ) . '</a></li>';
			}
			$html .= '</ul></div>';
		}
		$html .= '<a class="abt-mega__all" href="' . esc_url( abc_archive_url( $pt ) ) . '">' . esc_html( abc_t( 'view_all' ) ) . ' ' . abc_icon( 'arrow', 16 ) . '</a></div></div>';
		set_transient( $key, $html, 10 * MINUTE_IN_SECONDS );
	}
	return $html;
}

add_filter(
	'nav_menu_css_class',
	static function ( $classes, $item, $args, $depth ) {
		if ( 0 === $depth && isset( $args->theme_location ) && 'primary' === $args->theme_location && 'post_type_archive' === $item->type && isset( abt_mega_map()[ $item->object ] ) ) {
			$classes[] = 'abt-has-mega';
		}
		return $classes;
	},
	10,
	4
);

add_filter(
	'walker_nav_menu_start_el',
	static function ( $out, $item, $depth, $args ) {
		if ( 0 !== $depth || ! isset( $args->theme_location ) || 'primary' !== $args->theme_location || 'post_type_archive' !== $item->type || ! isset( abt_mega_map()[ $item->object ] ) ) {
			return $out;
		}
		$id    = 'mega-' . (int) $item->ID;
		$panel = abt_mega_panel( $item->object, $id );
		if ( ! $panel ) {
			return $out;
		}
		$btn = '<button type="button" class="abt-mega-toggle" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '"><span class="screen-reader-text">' . esc_html( $item->title ) . '</span>' . abc_icon( 'arrow', 14 ) . '</button>';
		return $out . $btn . $panel;
	},
	10,
	4
);

/** Fresh panels when content changes. */
add_action(
	'save_post',
	static function ( $post_id ) {
		$pt = get_post_type( $post_id );
		if ( isset( abt_mega_map()[ $pt ] ) ) {
			foreach ( abc_languages() as $l ) {
				delete_transient( 'abt_mega_' . $pt . '_' . $l );
			}
		}
	}
);
