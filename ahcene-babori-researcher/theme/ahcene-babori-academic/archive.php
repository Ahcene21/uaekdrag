<?php
/**
 * Generic archive: cards (projects, books, presentations, resources, notes, taxonomy terms).
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
ABC_Render::$level = 'h2';

$pt    = get_query_var( 'post_type' );
$pt    = is_array( $pt ) ? reset( $pt ) : $pt;
$title = $pt && abc_pt_label( $pt ) ? abc_pt_label( $pt ) : wp_strip_all_tags( get_the_archive_title() );
$desc  = $pt ? abc_t( 'desc_' . $pt ) : wp_strip_all_tags( term_description() );
if ( 0 === strpos( $desc, 'desc_' ) ) {
	$desc = '';
}
if ( is_tax() ) {
	$title = single_term_title( '', false );
}
abt_page_header( $title, $desc, is_tax() ? abc_t( 'browse' ) : '' );

$chip_tax = array(
	'abc_project'  => '',
	'abc_presentation' => 'abc_pres_type',
	'abc_note'     => 'abc_note_category',
);
$tax_obj = is_tax() ? get_queried_object() : null;
?>
<div class="abt-container abt-section">
	<?php
	if ( $pt && ! empty( $chip_tax[ $pt ] ) ) {
		abt_term_chips( $chip_tax[ $pt ] );
	} elseif ( $tax_obj && 'abc_pres_type' === $tax_obj->taxonomy ) {
		abt_term_chips( 'abc_pres_type' );
	} elseif ( $tax_obj && 'abc_note_category' === $tax_obj->taxonomy ) {
		abt_term_chips( 'abc_note_category' );
	}
	if ( have_posts() ) :
		$is_pub = ( 'abc_publication' === $pt ) || ( $tax_obj && in_array( $tax_obj->taxonomy, array( 'abc_pub_type', 'abc_pub_year', 'abc_keyword' ), true ) );
		?>
		<?php if ( $is_pub ) : ?>
			<div class="abc-publist">
				<?php while ( have_posts() ) { the_post(); echo ABC_Render::publication_item( get_post() ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php else : ?>
			<div class="abc-grid abc-grid--cards">
				<?php
				while ( have_posts() ) {
					the_post();
					$p = get_post();
					echo ABC_Render::card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		<?php endif; ?>
		<?php abt_pagination(); ?>
	<?php else : ?>
		<p class="abc-empty"><?php echo esc_html( abc_t( 'nothing_yet' ) ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
