<?php
/**
 * Fallback template.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
abt_page_header( is_home() ? abc_t( 'research_notes' ) : wp_strip_all_tags( get_the_archive_title() ) );
?>
<div class="abt-container abt-section">
	<?php if ( have_posts() ) : ?>
		<div class="abc-grid abc-grid--cards">
			<?php
			while ( have_posts() ) {
				the_post();
				echo ABC_Render::card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
		<?php abt_pagination(); ?>
	<?php else : ?>
		<p class="abc-empty"><?php echo esc_html( abc_t( 'nothing_yet' ) ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
