<?php
/**
 * Single book.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$id  = get_the_ID();
	$pdf = abc_file_or_url( 'book_pdf_id', 'book_pdf_url', $id );
	abt_page_header( get_the_title(), abc_get( 'book_author', $id ), abc_t( 'books' ) );
	?>
<div class="abt-container abt-section abt-book">
	<div class="abt-book__cover">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'large', array( 'alt' => sprintf( abc_t( 'cover_of' ), get_the_title() ) ) ); ?>
		<?php else : ?>
			<div class="abt-book__placeholder" aria-hidden="true"><?php echo abc_icon( 'library', 56 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</div>
	<div class="abt-book__body">
		<?php echo ABC_Render::demo_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<dl class="abt-details">
			<?php
			foreach ( array( abc_t( 'author' ) => abc_get( 'book_author', $id ), abc_t( 'publisher' ) => abc_get( 'book_publisher', $id ), abc_t( 'year' ) => abc_get( 'book_year', $id ), 'ISBN' => abc_get( 'book_isbn', $id ) ) as $k => $v ) {
				if ( $v ) {
					echo '<div><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
				}
			}
			?>
		</dl>
		<div class="abt-prose"><?php the_content(); ?></div>
		<p class="abt-actions abt-actions--row">
			<?php
			echo ABC_Render::button( abc_get( 'book_publisher_url', $id ), abc_t( 'publisher_page' ), 'external', 'abc-btn abc-btn--ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo ABC_Render::button( abc_get( 'book_purchase_url', $id ), abc_t( 'purchase' ), 'link', 'abc-btn abc-btn--primary' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo ABC_Render::button( $pdf, abc_t( 'download_pdf' ), 'download', 'abc-btn abc-btn--ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</p>
	</div>
</div>
	<?php
endwhile;
get_footer();
