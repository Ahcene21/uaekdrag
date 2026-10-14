<?php
/**
 * Research area hub: projects → publications → presentations → notes.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
$term = get_queried_object();
$icon = get_term_meta( $term->term_id, 'abc_icon', true );
$img_id = abc_area_image_id( $term->term_id );
abt_page_header( $term->name, wp_strip_all_tags( $term->description ), abc_t( 'research_area' ), $img_id ? (string) wp_get_attachment_image_url( $img_id, 'large' ) : '' );

$sections = array(
	array( 'projects', 'abc_project', abc_t( 'projects' ), 'cards', 12 ),
	array( 'publications', 'abc_publication', abc_t( 'publications' ), 'pubs', 50 ),
	array( 'presentations', 'abc_presentation', abc_t( 'presentations' ), 'cards', 12 ),
	array( 'notes', 'abc_note', abc_t( 'research_notes' ), 'cards', 12 ),
);
?>
<div class="abt-container abt-section">
	<?php
	$any = false;
	foreach ( $sections as $s ) :
		$items = get_posts(
			array(
				'post_type'      => $s[1],
				'lang'           => abc_lang(),
				'posts_per_page' => $s[4],
				'tax_query'      => array( array( 'taxonomy' => 'abc_research_area', 'field' => 'term_id', 'terms' => $term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( ! $items ) {
			continue;
		}
		$any = true;
		?>
		<section class="abt-subsection" id="<?php echo esc_attr( $s[0] ); ?>">
			<h2 class="abt-subsection__title"><?php echo esc_html( $s[2] ); ?></h2>
			<?php if ( 'pubs' === $s[3] ) : ?>
				<div class="abc-publist"><?php foreach ( $items as $it ) { echo ABC_Render::publication_item( $it ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php else : ?>
				<div class="abc-grid abc-grid--cards"><?php foreach ( $items as $it ) { echo ABC_Render::card( $it ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
	<?php if ( ! $any ) : ?>
		<p class="abc-empty"><?php echo esc_html( abc_t( 'nothing_yet' ) ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
