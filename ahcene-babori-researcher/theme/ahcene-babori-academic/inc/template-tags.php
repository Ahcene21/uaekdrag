<?php
/**
 * Template helpers (markup only; data comes from the core plugin).
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;

/**
 * EN | FR | AR switcher.
 */
function abt_lang_switcher() {
	$langs = ABC_Polylang::switcher();
	if ( ! $langs ) {
		return;
	}
	echo '<nav class="abt-lang" aria-label="' . esc_attr( abc_t( 'language' ) ) . '"><ul>';
	foreach ( $langs as $l ) {
		printf(
			'<li><a href="%1$s" lang="%2$s" hreflang="%2$s"%3$s><span aria-hidden="true">%4$s</span><span class="screen-reader-text">%5$s</span></a></li>',
			esc_url( $l['url'] ),
			esc_attr( $l['code'] ),
			$l['current'] ? ' aria-current="true"' : '',
			esc_html( strtoupper( $l['code'] ) ),
			esc_html( $l['name'] )
		);
	}
	echo '</ul></nav>';
}

/**
 * Fallback primary menu (before a menu is assigned): the same twelve entries, language aware.
 */
function abt_menu_fallback() {
	$items = array(
		array( abc_t( 'nav_home' ), abc_page_url( 'home' ) ),
		array( abc_t( 'nav_about' ), abc_page_url( 'about' ) ),
		array( abc_t( 'publications' ), abc_archive_url( 'abc_publication' ) ),
		array( abc_t( 'projects' ), abc_archive_url( 'abc_project' ) ),
		array( abc_t( 'teaching' ), abc_archive_url( 'abc_teaching' ) ),
		array( abc_t( 'academic_service' ), abc_archive_url( 'abc_service' ) ),
		array( abc_t( 'presentations' ), abc_archive_url( 'abc_presentation' ) ),
		array( abc_t( 'resources' ), abc_archive_url( 'abc_resource' ) ),
		array( abc_t( 'research_notes' ), abc_archive_url( 'abc_note' ) ),
		array( abc_t( 'nav_cv' ), abc_page_url( 'cv' ) ),
		array( abc_t( 'nav_contact' ), abc_page_url( 'contact' ) ),
	);
	echo '<ul class="abt-menu">';
	foreach ( $items as $i ) {
		echo '<li class="menu-item"><a href="' . esc_url( $i[1] ) . '">' . esc_html( $i[0] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Breadcrumbs (shares data with the BreadcrumbList schema).
 */
function abt_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$crumbs = ABC_SEO::breadcrumbs();
	if ( count( $crumbs ) < 2 ) {
		return;
	}
	echo '<nav class="abt-breadcrumbs" aria-label="' . esc_attr( abc_t( 'breadcrumb' ) ) . '"><ol>';
	$last = count( $crumbs ) - 1;
	foreach ( $crumbs as $i => $c ) {
		if ( $i === $last || ! $c[1] ) {
			echo '<li aria-current="page">' . esc_html( $c[0] ) . '</li>';
		} else {
			echo '<li><a href="' . esc_url( $c[1] ) . '">' . esc_html( $c[0] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * Pagination.
 */
function abt_pagination() {
	global $wp_query;
	if ( $wp_query->max_num_pages < 2 ) {
		return;
	}
	$links = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => abc_icon( 'arrow', 16 ) . '<span class="screen-reader-text">' . esc_html( abc_t( 'previous' ) ) . '</span>',
			'next_text' => '<span class="screen-reader-text">' . esc_html( abc_t( 'next' ) ) . '</span>' . abc_icon( 'arrow', 16 ),
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="abt-pagination" aria-label="' . esc_attr( abc_t( 'pagination' ) ) . '"><ul>';
	foreach ( $links as $l ) {
		echo '<li>' . wp_kses( $l, array_merge( abc_kses_svg(), array( 'a' => array( 'href' => true, 'class' => true, 'aria-label' => true ), 'span' => array( 'class' => true, 'aria-current' => true ) ) ) ) . '</li>';
	}
	echo '</ul></nav>';
}

/**
 * Page header block.
 *
 * @param string $title  Title (h1).
 * @param string $desc   Description.
 * @param string $kicker Small label above.
 */
function abt_page_header( $title, $desc = '', $kicker = '' ) {
	echo '<header class="abt-page-header"><div class="abt-container">';
	abt_breadcrumbs();
	if ( $kicker ) {
		echo '<p class="abt-eyebrow">' . esc_html( $kicker ) . '</p>';
	}
	echo '<h1 class="abt-page-title">' . esc_html( $title ) . '</h1>';
	if ( $desc ) {
		echo '<p class="abt-page-desc">' . esc_html( $desc ) . '</p>';
	}
	echo '</div></header>';
}

/**
 * Section heading on the homepage.
 */
function abt_section_head( $title, $intro = '', $link_url = '', $link_label = '' ) {
	echo '<div class="abt-section__head"><div><h2 class="abt-section__title">' . esc_html( $title ) . '</h2>';
	if ( $intro ) {
		echo '<p class="abt-section__intro">' . esc_html( $intro ) . '</p>';
	}
	echo '</div>';
	if ( $link_url ) {
		echo '<a class="abt-more" href="' . esc_url( $link_url ) . '"><span>' . esc_html( $link_label ? $link_label : abc_t( 'view_all' ) ) . '</span>' . abc_icon( 'arrow', 18 ) . '</a>';
	}
	echo '</div>';
}

/**
 * Render a post as list item (publications) or card.
 */
function abt_render_item( $post ) {
	if ( 'abc_publication' === $post->post_type ) {
		return ABC_Render::publication_item( $post );
	}
	return ABC_Render::card( $post );
}

/**
 * Definition list of a post's non-empty simple custom fields.
 *
 * @param int      $id      Post ID.
 * @param string[] $exclude Field keys to skip.
 */
function abt_post_details( $id, $exclude = array() ) {
	$post = get_post( $id );
	$rows = array();
	foreach ( ABC_Fields::fields_for( $post->post_type ) as $key => $def ) {
		if ( in_array( $key, $exclude, true ) || in_array( $def['type'], array( 'posts', 'textarea', 'checkbox', 'media' ), true ) ) {
			continue;
		}
		$val = abc_get( $key, $id );
		if ( '' === (string) $val ) {
			continue;
		}
		if ( 'url' === $def['type'] ) {
			$disp = '<a href="' . esc_url( $val ) . '" rel="noopener noreferrer">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $val ) ) ) . '</a>';
		} elseif ( 'select' === $def['type'] ) {
			$disp = esc_html( $def['options'][ $val ] ?? $val );
		} elseif ( 'date' === $def['type'] || 'period' === $def['type'] ) {
			$disp = esc_html( abc_format_date( $val ) );
		} else {
			$disp = esc_html( $val );
		}
		$rows[] = array( $def['label'], $disp );
	}
	if ( ! $rows ) {
		return;
	}
	echo '<dl class="abt-details">';
	foreach ( $rows as $r ) {
		echo '<div><dt>' . esc_html( $r[0] ) . '</dt><dd>' . wp_kses_post( $r[1] ) . '</dd></div>';
	}
	echo '</dl>';
}

/**
 * Table of contents from <h2 id=""> in CV content.
 *
 * @param string $html Content HTML.
 * @return string
 */
function abt_toc( $html ) {
	if ( ! preg_match_all( '/<h2[^>]*id="([^"]+)"[^>]*>(.*?)<\/h2>/s', $html, $m, PREG_SET_ORDER ) ) {
		return '';
	}
	$out = '<nav class="abt-toc" aria-label="' . esc_attr( abc_t( 'on_this_page' ) ) . '"><p class="abt-toc__title">' . esc_html( abc_t( 'on_this_page' ) ) . '</p><ul>';
	foreach ( $m as $h ) {
		$out .= '<li><a href="#' . esc_attr( $h[1] ) . '">' . esc_html( wp_strip_all_tags( $h[2] ) ) . '</a></li>';
	}
	return $out . '</ul></nav>';
}

/**
 * Chips linking to taxonomy terms (filter row on archives).
 *
 * @param string $taxonomy Taxonomy.
 */
function abt_term_chips( $taxonomy ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	$cur = is_tax( $taxonomy ) ? get_queried_object_id() : 0;
	$tax = get_taxonomy( $taxonomy );
	$pt  = $tax->object_type[0];
	echo '<nav class="abt-chips" aria-label="' . esc_attr( $tax->labels->name ) . '"><ul>';
	echo '<li><a href="' . esc_url( abc_archive_url( $pt ) ) . '"' . ( ! $cur ? ' aria-current="true"' : '' ) . '>' . esc_html( abc_t( 'all' ) ) . '</a></li>';
	foreach ( $terms as $t ) {
		echo '<li><a href="' . esc_url( get_term_link( $t ) ) . '"' . ( $cur === $t->term_id ? ' aria-current="true"' : '' ) . '>' . esc_html( $t->name ) . '</a></li>';
	}
	echo '</ul></nav>';
}

/**
 * Resource download button + info.
 */
function abt_resource_download( $id ) {
	$info = abc_file_info( abc_get( 'res_file_id', $id ) );
	if ( $info ) {
		$label = abc_t( 'download' ) . ' ' . $info['ext'] . ( $info['size'] ? ' (' . $info['size'] . ')' : '' );
		return ABC_Render::button( $info['url'], $label, 'download', 'abc-btn abc-btn--primary', array( 'download' => true ) );
	}
	$u = abc_get( 'res_url', $id );
	return $u ? ABC_Render::button( $u, abc_t( 'download' ), 'download', 'abc-btn abc-btn--primary' ) : '';
}

/**
 * Print a list from a textarea (one item per line).
 */
function abt_lines( $text ) {
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) );
	if ( ! $lines ) {
		return;
	}
	echo '<ul>';
	foreach ( $lines as $l ) {
		echo '<li>' . esc_html( $l ) . '</li>';
	}
	echo '</ul>';
}
