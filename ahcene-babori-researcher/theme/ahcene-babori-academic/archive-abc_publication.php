<?php
/**
 * Publications archive with filters (year, type, research area, keyword).
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
ABC_Render::$level = 'h2';
abt_page_header( abc_t( 'publications' ), abc_t( 'desc_abc_publication' ) );
global $wp_query;
?>
<div class="abt-container abt-section">
	<?php echo ABC_Query::publication_filters(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<p class="abt-count" role="status"><?php echo esc_html( abc_t( 'results_count', (int) $wp_query->found_posts ) ); ?></p>
	<?php if ( have_posts() ) : ?>
		<div class="abc-pubgrid">
			<?php
			while ( have_posts() ) {
				the_post();
				echo ABC_Render::publication_card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
		<?php abt_pagination(); ?>
	<?php else : ?>
		<p class="abc-empty"><?php echo esc_html( abc_t( 'no_results' ) ); ?></p>
	<?php endif; ?>

	<?php
	$books = ABC_Render::query( 'book', array( 'limit' => 6 ) );
	if ( $books && ! ABC_Query::req( 'pub_q' ) && ! ABC_Query::req( 'pub_year' ) && ! ABC_Query::req( 'pub_type' ) && ! ABC_Query::req( 'pub_area' ) && ! is_paged() ) :
		?>
		<section class="abt-subsection" aria-labelledby="books-title">
			<?php abt_section_head( abc_t( 'books' ), '', abc_archive_url( 'abc_book' ), abc_t( 'view_all' ) ); ?>
			<div class="abc-grid abc-grid--cards">
				<?php foreach ( $books as $b ) { echo ABC_Render::card( $b ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
