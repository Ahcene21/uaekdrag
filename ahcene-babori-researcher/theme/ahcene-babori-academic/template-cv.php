<?php
/**
 * Template Name: Online CV
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$cv      = abc_cv_link();
	$content = apply_filters( 'the_content', get_the_content() );
	abt_page_header( get_the_title(), abc_ml( 'role' ), abc_t( 'nav_cv' ) );
	?>
<div class="abt-container abt-section abt-cv">
	<aside class="abt-cv__side">
		<div class="abt-cv__card">
			<div class="abt-cv__photo"><?php echo abc_portrait( 'medium', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<p class="abt-cv__name"><?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?></p>
			<p class="abt-cv__role"><?php echo esc_html( abc_ml( 'role' ) ); ?></p>
			<?php echo ABC_Render::button( $cv['url'], abc_t( 'download_full_cv' ), 'download', 'abc-btn abc-btn--primary abc-btn--block', $cv['is_file'] ? array( 'download' => true ) : array() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! $cv['is_file'] && current_user_can( 'manage_options' ) ) : ?>
				<p class="abt-cv__hint"><a href="<?php echo esc_url( admin_url( 'admin.php?page=abc-cv' ) ); ?>">Upload the CV PDF</a> (visible to administrators only).</p>
			<?php endif; ?>
		</div>
		<?php echo abt_toc( $content ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</aside>
	<article class="abt-prose abt-cv__body">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content filters applied. ?>
	</article>
</div>
	<?php
endwhile;
get_footer();
