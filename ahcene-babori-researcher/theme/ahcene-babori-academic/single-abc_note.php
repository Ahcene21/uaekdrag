<?php
/**
 * Single research note.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$pubs  = abc_related( 'note_pubs', $id, 'abc_publication' );
	$projs = abc_related( 'note_projects', $id, 'abc_project' );
	$tags  = get_the_terms( $id, 'abc_note_tag' );
	?>
<article <?php post_class( 'abt-note' ); ?>>
	<header class="abt-page-header">
		<div class="abt-container abt-container--narrow">
			<?php abt_breadcrumbs(); ?>
			<p class="abt-eyebrow"><?php echo wp_kses_post( ABC_Render::terms( $id, 'abc_note_category' ) ); ?> <?php echo ABC_Render::demo_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<h1 class="abt-page-title"><?php the_title(); ?></h1>
			<p class="abt-note__meta">
				<span><?php echo esc_html( abc_t( 'by' ) ); ?> <?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?></span> ·
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( abc_format_date( get_the_date( 'Y-m-d' ) ) ); ?></time> ·
				<span><?php echo esc_html( abc_t( 'min_read', abc_reading_time( $id ) ) ); ?></span>
			</p>
			<?php if ( has_excerpt() ) : ?><p class="abt-page-desc"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</div>
	</header>
	<div class="abt-container abt-container--narrow abt-section">
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="abt-note__figure"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>
		<div class="abt-prose"><?php the_content(); ?></div>
		<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
			<ul class="abt-taglist" aria-label="<?php echo esc_attr( abc_t( 'tags' ) ); ?>">
				<?php foreach ( $tags as $t ) : ?><li><a href="<?php echo esc_url( get_term_link( $t ) ); ?>">#<?php echo esc_html( $t->name ); ?></a></li><?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( $pubs ) : ?>
			<section class="abt-subsection"><h2><?php echo esc_html( abc_t( 'related_publications' ) ); ?></h2>
				<div class="abc-publist"><?php foreach ( $pubs as $p ) { echo ABC_Render::publication_item( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</section>
		<?php endif; ?>
		<?php if ( $projs ) : ?>
			<section class="abt-subsection"><h2><?php echo esc_html( abc_t( 'related_projects' ) ); ?></h2>
				<div class="abc-grid abc-grid--cards"><?php foreach ( $projs as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</section>
		<?php endif; ?>
		<nav class="abt-postnav" aria-label="<?php echo esc_attr( abc_t( 'more_notes' ) ); ?>">
			<?php
			$prev = get_previous_post();
			$next = get_next_post();
			echo $prev ? '<a class="abt-postnav__prev" href="' . esc_url( get_permalink( $prev ) ) . '" rel="prev"><span>' . esc_html( abc_t( 'previous' ) ) . '</span>' . esc_html( get_the_title( $prev ) ) . '</a>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput
			echo $next ? '<a class="abt-postnav__next" href="' . esc_url( get_permalink( $next ) ) . '" rel="next"><span>' . esc_html( abc_t( 'next' ) ) . '</span>' . esc_html( get_the_title( $next ) ) . '</a>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</nav>
	</div>
</article>
	<?php
endwhile;
get_footer();
