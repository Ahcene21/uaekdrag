<?php
/**
 * Academic service category (timeline).
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
ABC_Render::$level = 'h2';
abt_page_header( single_term_title( '', false ), wp_strip_all_tags( term_description() ), abc_t( 'academic_service' ) );
?>
<div class="abt-container abt-section">
	<?php abt_term_chips( 'abc_service_type' ); ?>
	<?php echo ABC_Render::timeline( $GLOBALS['wp_query']->posts ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
<?php
get_footer();
