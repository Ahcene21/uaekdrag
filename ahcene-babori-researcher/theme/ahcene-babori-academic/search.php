<?php
/**
 * Research discovery: site-wide search with publication filters.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
ABC_Render::$level = 'h2';
global $wp_query;
abt_page_header( abc_t( 'search_results_for', get_search_query( false ) ), '', abc_t( 'search' ) );
?>
<div class="abt-container abt-section">
	<?php echo ABC_Query::search_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<p class="abt-count" role="status"><?php echo esc_html( abc_t( 'results_count', (int) $wp_query->found_posts ) ); ?></p>
	<?php if ( have_posts() ) : ?>
		<div class="abt-results">
			<?php
			while ( have_posts() ) {
				the_post();
				$p = get_post();
				if ( 'abc_publication' === $p->post_type ) {
					echo '<div class="abt-result">' . ABC_Render::publication_item( $p ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				} else {
					echo '<div class="abt-result">' . ABC_Render::card( $p ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
			?>
		</div>
		<?php abt_pagination(); ?>
	<?php else : ?>
		<p class="abc-empty"><?php echo esc_html( abc_t( 'no_results' ) ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
