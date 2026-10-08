<?php
/**
 * 404.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
abt_page_header( abc_t( 'not_found_title' ), abc_t( 'not_found_text' ) );
?>
<div class="abt-container abt-section">
	<?php get_search_form(); ?>
	<p><a class="abc-btn abc-btn--primary" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>"><?php echo esc_html( abc_t( 'back_home' ) ); ?></a></p>
</div>
<?php
get_footer();
