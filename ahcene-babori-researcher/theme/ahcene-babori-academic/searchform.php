<?php
/**
 * Header search form.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
$home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
?>
<form role="search" method="get" class="abt-searchform" action="<?php echo esc_url( $home ); ?>">
	<label class="screen-reader-text" for="abt-s"><?php echo esc_html( abc_t( 'search_label' ) ); ?></label>
	<input type="search" id="abt-s" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php echo esc_attr( abc_t( 'search_placeholder' ) ); ?>" autocomplete="off">
	<button type="submit" class="abc-btn abc-btn--primary"><?php echo esc_html( abc_t( 'search' ) ); ?></button>
	<a class="abt-searchform__adv" href="<?php echo esc_url( abc_page_url( 'discover' ) ); ?>"><?php echo esc_html( abc_t( 'advanced_search' ) ); ?></a>
</form>
