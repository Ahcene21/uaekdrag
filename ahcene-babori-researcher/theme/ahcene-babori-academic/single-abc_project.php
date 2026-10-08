<?php
/**
 * Single research project.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id     = get_the_ID();
	$status = abc_option_label( 'project_status', abc_get( 'project_status', $id ) );
	$start  = abc_get( 'project_start', $id );
	$end    = abc_get( 'project_end', $id );
	$pubs   = abc_related( 'project_pubs', $id, 'abc_publication' );
	$pres   = abc_related( 'project_pres', $id, 'abc_presentation' );
	$notes  = abc_reverse_related( $id, 'abc_note', 'abc_note_projects' );
	$ext    = abc_get( 'project_url', $id );
	$tech   = array_filter( array_map( 'trim', explode( ',', (string) abc_get( 'project_tech', $id ) ) ) );
	abt_page_header( get_the_title(), has_excerpt() ? get_the_excerpt() : '', abc_t( 'projects' ) );
	?>
<div class="abt-container abt-section abt-single-grid">
	<article class="abt-single-main abt-prose">
		<p class="abt-badges"><?php echo ABC_Render::badge( $status, 'abc-badge--status' ) . ABC_Render::demo_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php
		$blocks = array(
			'project_question' => abc_t( 'research_question' ),
			'project_problem'  => abc_t( 'problem' ),
		);
		foreach ( $blocks as $k => $label ) {
			if ( abc_get( $k, $id ) ) {
				echo '<h2>' . esc_html( $label ) . '</h2><p>' . nl2br( esc_html( abc_get( $k, $id ) ) ) . '</p>';
			}
		}
		if ( abc_get( 'project_objectives', $id ) ) {
			echo '<h2>' . esc_html( abc_t( 'objectives' ) ) . '</h2>';
			abt_lines( abc_get( 'project_objectives', $id ) );
		}
		if ( abc_get( 'project_method', $id ) ) {
			echo '<h2>' . esc_html( abc_t( 'methodology' ) ) . '</h2><p>' . nl2br( esc_html( abc_get( 'project_method', $id ) ) ) . '</p>';
		}
		if ( $tech ) {
			echo '<h2>' . esc_html( abc_t( 'technologies' ) ) . '</h2><ul class="abt-taglist">';
			foreach ( $tech as $t ) {
				echo '<li><span>' . esc_html( $t ) . '</span></li>';
			}
			echo '</ul>';
		}
		the_content();
		if ( abc_get( 'project_outputs', $id ) ) {
			echo '<h2>' . esc_html( abc_t( 'outputs' ) ) . '</h2>';
			abt_lines( abc_get( 'project_outputs', $id ) );
		}
		?>
		<?php if ( $pubs ) : ?>
			<h2><?php echo esc_html( abc_t( 'related_publications' ) ); ?></h2>
			<div class="abc-publist"><?php foreach ( $pubs as $p ) { echo ABC_Render::publication_item( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
		<?php if ( $pres ) : ?>
			<h2><?php echo esc_html( abc_t( 'related_presentations' ) ); ?></h2>
			<div class="abc-grid abc-grid--cards"><?php foreach ( $pres as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
		<?php if ( $notes ) : ?>
			<h2><?php echo esc_html( abc_t( 'related_notes' ) ); ?></h2>
			<div class="abc-grid abc-grid--cards"><?php foreach ( $notes as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</article>
	<aside class="abt-single-side" aria-label="<?php echo esc_attr( abc_t( 'details' ) ); ?>">
		<div class="abt-panel">
			<?php echo $ext ? '<p class="abt-actions">' . ABC_Render::button( $ext, abc_t( 'project_website' ), 'external', 'abc-btn abc-btn--primary abc-btn--block' ) . '</p>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<dl class="abt-details">
				<?php
				$rows = array(
					abc_t( 'status' ) => $status,
					abc_t( 'start' )  => $start ? abc_format_date( $start ) : '',
					abc_t( 'end' )    => $end ? abc_format_date( $end ) : '',
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
				if ( abc_get( 'project_partners', $id ) ) {
					echo '<div><dt>' . esc_html( abc_t( 'partners' ) ) . '</dt><dd>' . nl2br( esc_html( abc_get( 'project_partners', $id ) ) ) . '</dd></div>';
				}
				?>
			</dl>
		</div>
	</aside>
</div>
	<?php
endwhile;
get_footer();
