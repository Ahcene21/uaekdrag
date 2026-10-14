<?php
/**
 * Single publication.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$d     = abc_pub_data( $id );
	$doi   = abc_doi_url( $d['doi'] );
	$pdf   = abc_file_or_url( 'pub_pdf_id', 'pub_pdf_url', $id );
	$repo  = abc_get( 'pub_repo_url', $id );
	$url   = $d['url'];
	$oa    = abc_get( 'pub_oa', $id );
	$abs   = abc_get( 'pub_abstract', $id );
	$kw    = get_the_terms( $id, 'abc_keyword' );
	$auth  = wp_list_pluck( $d['authors'], 'full' );
	$projs = abc_reverse_related( $id, 'abc_project', 'abc_project_pubs' );
	$rel   = abc_related( 'pub_related', $id, 'abc_publication' );
	if ( ! $rel ) {
		$areas = wp_get_post_terms( $id, 'abc_research_area', array( 'fields' => 'ids' ) );
		if ( $areas && ! is_wp_error( $areas ) ) {
			$rel = get_posts(
				array(
					'post_type'      => 'abc_publication',
					'posts_per_page' => 4,
					'post__not_in'   => array( $id ),
					'lang'           => abc_lang(),
					'tax_query'      => array( array( 'taxonomy' => 'abc_research_area', 'field' => 'term_id', 'terms' => $areas ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
		}
	}
	?>
<article <?php post_class( 'abt-single-pub' ); ?>>
	<header class="abt-page-header">
		<div class="abt-container">
			<?php abt_breadcrumbs(); ?>
			<p class="abt-badges">
				<?php
				echo ABC_Render::badge( wp_strip_all_tags( ABC_Render::terms( $id, 'abc_pub_type', false ) ), 'abc-badge--type' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo ( $oa && 'closed' !== $oa ) ? ABC_Render::badge( abc_t( 'open_access' ), 'abc-badge--oa' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput
				echo ABC_Render::demo_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</p>
			<h1 class="abt-page-title"><?php the_title(); ?></h1>
			<?php if ( $auth ) : ?>
				<p class="abt-page-desc"><?php echo esc_html( implode( '; ', $auth ) ); ?></p>
			<?php endif; ?>
			<p class="abt-pub-venue">
				<?php
				$bits = array_filter( array( $d['journal'], $d['volume'] ? abc_t( 'volume' ) . ' ' . $d['volume'] . ( $d['issue'] ? ' (' . $d['issue'] . ')' : '' ) : '', $d['pages'] ? abc_t( 'pages' ) . ' ' . $d['pages'] : '', $d['year'] ) );
				echo esc_html( implode( ' · ', $bits ) );
				?>
			</p>
		</div>
	</header>

	<div class="abt-container abt-section abt-single-grid">
		<div class="abt-single-main">
			<?php if ( $abs ) : ?>
				<section aria-labelledby="abs-h"><h2 id="abs-h"><?php echo esc_html( abc_t( 'abstract' ) ); ?></h2><p class="abt-abstract"><?php echo nl2br( esc_html( $abs ) ); ?></p></section>
			<?php endif; ?>
			<?php if ( get_the_content() ) : ?>
				<div class="abt-prose"><?php the_content(); ?></div>
			<?php endif; ?>
			<?php if ( $kw && ! is_wp_error( $kw ) ) : ?>
				<section aria-labelledby="kw-h"><h2 id="kw-h"><?php echo esc_html( abc_t( 'keywords' ) ); ?></h2>
					<ul class="abt-taglist">
						<?php foreach ( $kw as $t ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section aria-labelledby="cite-h" class="abt-cite">
				<h2 id="cite-h"><?php echo esc_html( abc_t( 'cite_this' ) ); ?></h2>
				<p class="abt-cite__text" id="abt-citation"><?php echo abc_citation( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<button type="button" class="abc-btn abc-btn--ghost abc-btn--sm" data-copy="#abt-citation"><?php echo abc_icon( 'quote', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( abc_t( 'copy' ) ); ?></span></button>
				<details class="abt-bibtex"><summary>BibTeX</summary><pre id="abt-bibtex" dir="ltr"><?php echo esc_html( abc_bibtex( $id ) ); ?></pre></details>
			</section>

			<?php if ( $projs ) : ?>
				<section aria-labelledby="rp-h"><h2 id="rp-h"><?php echo esc_html( abc_t( 'related_projects' ) ); ?></h2>
					<div class="abc-grid abc-grid--cards"><?php foreach ( $projs as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</section>
			<?php endif; ?>
			<?php if ( $rel ) : ?>
				<section aria-labelledby="rl-h"><h2 id="rl-h"><?php echo esc_html( abc_t( 'related_publications' ) ); ?></h2>
					<div class="abc-publist"><?php foreach ( $rel as $p ) { echo ABC_Render::publication_item( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</section>
			<?php endif; ?>
		</div>

		<aside class="abt-single-side" aria-label="<?php echo esc_attr( abc_t( 'details' ) ); ?>">
			<div class="abt-panel">
				<p class="abt-actions">
					<?php
					echo ABC_Render::button( $doi, abc_t( 'view_doi' ), 'link', 'abc-btn abc-btn--primary abc-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput
					echo ABC_Render::button( $pdf, abc_t( 'download_pdf' ), 'download', 'abc-btn abc-btn--ghost abc-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput
					echo ABC_Render::button( $repo, abc_t( 'repository' ), 'repository', 'abc-btn abc-btn--ghost abc-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput
					echo ( ! $doi && $url ) ? ABC_Render::button( $url, abc_t( 'publisher_page' ), 'external', 'abc-btn abc-btn--ghost abc-btn--block' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput
					?>
				</p>
				<dl class="abt-details">
					<?php
					$rows = array(
						abc_t( 'year' )      => $d['year'],
						abc_t( 'type' )      => wp_strip_all_tags( ABC_Render::terms( $id, 'abc_pub_type', false ) ),
						abc_t( 'journal' )   => $d['journal'],
						abc_t( 'publisher' ) => $d['publisher'],
						abc_t( 'volume' )    => $d['volume'],
						abc_t( 'issue' )     => $d['issue'],
						abc_t( 'pages' )     => $d['pages'],
						'DOI'                => $d['doi'],
						abc_t( 'open_access' ) => $oa ? abc_option_label( 'oa', $oa ) : '',
					);
					foreach ( $rows as $k => $v ) {
						if ( '' !== (string) $v ) {
							echo '<div><dt>' . esc_html( $k ) . '</dt><dd>' . ( 'DOI' === $k && $doi ? '<a href="' . esc_url( $doi ) . '" rel="noopener noreferrer">' . esc_html( $v ) . '</a>' : esc_html( $v ) ) . '</dd></div>';
						}
					}
					$areas_html = ABC_Render::terms( $id, 'abc_research_area' );
					if ( $areas_html ) {
						echo '<div><dt>' . esc_html( abc_t( 'research_area' ) ) . '</dt><dd>' . wp_kses_post( $areas_html ) . '</dd></div>';
					}
					?>
				</dl>
			</div>
		</aside>
	</div>
</article>
	<?php
endwhile;
get_footer();
