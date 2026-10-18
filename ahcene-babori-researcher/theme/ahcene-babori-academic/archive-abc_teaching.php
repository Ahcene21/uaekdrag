<?php
/**
 * Teaching portfolio, grouped by section.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
abt_page_header( abc_t( 'teaching' ), abc_t( 'desc_abc_teaching' ) );

$sections = get_terms( array( 'taxonomy' => 'abc_teaching_section', 'hide_empty' => true, 'orderby' => 'term_id', 'order' => 'ASC' ) );
?>
<div class="abt-container abt-section">
	<?php
	if ( $sections && ! is_wp_error( $sections ) ) :
		foreach ( $sections as $sec ) :
			$items = get_posts(
				array(
					'post_type'      => 'abc_teaching',
				'lang'           => abc_lang(),
					'posts_per_page' => 100,
					'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
					'tax_query'      => array( array( 'taxonomy' => 'abc_teaching_section', 'field' => 'term_id', 'terms' => $sec->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
			if ( ! $items ) {
				continue;
			}
			?>
			<section class="abt-subsection" id="<?php echo esc_attr( $sec->slug ); ?>">
				<h2 class="abt-subsection__title"><?php echo esc_html( $sec->name ); ?></h2>
				<div class="abc-grid abc-grid--cards">
					<?php foreach ( $items as $it ) { echo ABC_Render::card( $it ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</section>
			<?php
		endforeach;
	elseif ( have_posts() ) :
		?>
		<div class="abc-grid abc-grid--cards">
			<?php while ( have_posts() ) { the_post(); echo ABC_Render::card( get_post() ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	<?php else : ?>
		<p class="abc-empty"><?php echo esc_html( abc_t( 'nothing_yet' ) ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
