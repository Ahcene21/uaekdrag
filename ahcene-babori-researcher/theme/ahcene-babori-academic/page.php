<?php
/**
 * Standard page.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	abt_page_header( get_the_title() );
	?>
<div class="abt-container abt-section">
	<article <?php post_class( 'abt-prose' ); ?>>
		<?php the_content(); ?>
	</article>
</div>
	<?php
endwhile;
get_footer();
