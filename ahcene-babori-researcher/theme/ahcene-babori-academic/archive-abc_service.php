<?php
/**
 * Academic service timeline.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
ABC_Render::$level = 'h2';
abt_page_header( abc_t( 'academic_service' ), abc_t( 'desc_abc_service' ) );
?>
<div class="abt-container abt-section">
	<?php abt_term_chips( 'abc_service_type' ); ?>
	<?php echo ABC_Render::timeline( $GLOBALS['wp_query']->posts ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php abt_pagination(); ?>
</div>
<?php
get_footer();
