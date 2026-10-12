<?php
/**
 * Generic single template (service, teaching entries and any other post).
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$pt = get_post_type();
	abt_page_header( get_the_title(), '', abc_pt_label( $pt ) );
	?>
<div class="abt-container abt-section abt-single">
	<article <?php post_class( 'abt-prose' ); ?>>
		<?php echo ABC_Render::demo_badge( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( 0 === strpos( $pt, 'abc_' ) ) : ?>
			<?php
			abt_post_details( get_the_ID() );
			foreach ( array( 'svc_description', 'teach_description' ) as $k ) {
				if ( abc_get( $k ) ) {
					echo '<p>' . esc_html( abc_get( $k ) ) . '</p>';
				}
			}
			?>
		<?php endif; ?>
		<?php the_content(); ?>
		<?php
		if ( 'abc_teaching' === $pt ) {
			$mat = abc_file_or_url( 'teach_materials_id', 'teach_materials_url' );
			echo $mat ? '<p>' . ABC_Render::button( $mat, abc_t( 'download' ), 'download', 'abc-btn abc-btn--primary' ) . '</p>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput
			if ( function_exists( 'abc_attendance_button' ) && abc_attendance_button( get_the_ID() ) ) {
				echo '<p>' . abc_attendance_button( get_the_ID() ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
		?>
	</article>
</div>
	<?php
endwhile;
get_footer();
