<?php
/**
 * Single research resource.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$id = get_the_ID();
	abt_page_header( get_the_title(), has_excerpt() ? get_the_excerpt() : '', abc_t( 'resources' ) );
	$dl = abt_resource_download( $id );
	?>
<div class="abt-container abt-section abt-single-grid">
	<article class="abt-single-main abt-prose">
		<?php echo ABC_Render::demo_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php the_content(); ?>
	</article>
	<aside class="abt-single-side" aria-label="<?php echo esc_attr( abc_t( 'details' ) ); ?>">
		<div class="abt-panel">
			<p class="abt-actions"><?php echo $dl ? $dl : '<span class="abc-empty">' . esc_html( abc_t( 'file_not_available' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<dl class="abt-details">
				<?php
				$rows = array(
					abc_t( 'type' )    => wp_strip_all_tags( ABC_Render::terms( $id, 'abc_resource_type', false ) ),
					abc_t( 'version' ) => abc_get( 'res_version', $id ),
					abc_t( 'license' ) => abc_get( 'res_license', $id ),
				);
				foreach ( $rows as $k => $v ) {
					if ( $v ) {
						echo '<div><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
					}
				}
				?>
			</dl>
		</div>
	</aside>
</div>
	<?php
endwhile;
get_footer();
