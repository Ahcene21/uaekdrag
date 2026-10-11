<?php
/**
 * Single presentation.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$id     = get_the_ID();
	$slides = abc_file_or_url( 'pres_slides_id', 'pres_slides_url', $id );
	$video  = abc_get( 'pres_video', $id );
	$event  = abc_get( 'pres_event_url', $id );
	$projs  = abc_related( 'pres_projects', $id, 'abc_project' );
	if ( ! $projs ) {
		$projs = abc_reverse_related( $id, 'abc_project', 'abc_project_pres' );
	}
	abt_page_header( get_the_title(), abc_get( 'pres_conference', $id ), abc_t( 'presentations' ) );
	?>
<div class="abt-container abt-section abt-single-grid">
	<article class="abt-single-main abt-prose">
		<?php echo ABC_Render::demo_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( abc_get( 'pres_abstract', $id ) ) : ?>
			<h2><?php echo esc_html( abc_t( 'abstract' ) ); ?></h2>
			<p><?php echo nl2br( esc_html( abc_get( 'pres_abstract', $id ) ) ); ?></p>
		<?php endif; ?>
		<?php the_content(); ?>
		<?php if ( $projs ) : ?>
			<h2><?php echo esc_html( abc_t( 'related_projects' ) ); ?></h2>
			<div class="abc-grid abc-grid--cards"><?php foreach ( $projs as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</article>
	<aside class="abt-single-side" aria-label="<?php echo esc_attr( abc_t( 'details' ) ); ?>">
		<div class="abt-panel">
			<p class="abt-actions">
				<?php
				echo ABC_Render::button( $slides, abc_t( 'slides' ), 'slides', 'abc-btn abc-btn--primary abc-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo ABC_Render::button( $video, abc_t( 'video' ), 'video', 'abc-btn abc-btn--ghost abc-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo ABC_Render::button( $event, abc_t( 'event_page' ), 'external', 'abc-btn abc-btn--ghost abc-btn--block' ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</p>
			<dl class="abt-details">
				<?php
				$rows = array(
					abc_t( 'conference' ) => abc_get( 'pres_conference', $id ),
					abc_t( 'date' )       => abc_get( 'pres_date', $id ) ? abc_format_date( abc_get( 'pres_date', $id ) ) : '',
					abc_t( 'location' )   => abc_get( 'pres_location', $id ),
					abc_t( 'type' )       => wp_strip_all_tags( ABC_Render::terms( $id, 'abc_pres_type', false ) ) ? wp_strip_all_tags( ABC_Render::terms( $id, 'abc_pres_type', false ) ) : abc_option_label( 'pres_type', abc_get( 'pres_type', $id ) ),
				);
				foreach ( $rows as $k => $v ) {
					if ( $v ) {
						echo '<div><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
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
	<?php
endwhile;
get_footer();
