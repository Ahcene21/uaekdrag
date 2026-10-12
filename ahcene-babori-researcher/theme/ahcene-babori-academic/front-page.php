<?php
/**
 * Homepage.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
get_header();

$cv      = abc_cv_link();
$res_url = abc_page_url( 'research' );
if ( $res_url === ( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ) ) {
	$res_url = abc_archive_url( 'abc_publication' );
}
$projects = ABC_Render::query( 'project', array( 'limit' => 4, 'featured' => 1 ) );
$notes    = ABC_Render::query( 'note', array( 'limit' => 3 ) );
?>

<section class="abt-hero" aria-labelledby="hero-title">
	<div class="abt-hero__bg" aria-hidden="true"></div>
	<div class="abt-container abt-hero__grid">
		<div class="abt-hero__text">
			<p class="abt-eyebrow abt-eyebrow--light"><?php echo esc_html( abc_t( 'hero_eyebrow' ) ); ?></p>
			<h1 id="hero-title" class="abt-hero__name"><?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?></h1>
			<p class="abt-hero__role"><?php echo esc_html( abc_ml( 'role' ) ); ?></p>
			<p class="abt-hero__tagline"><?php echo esc_html( abc_ml( 'tagline' ) ); ?></p>
			<div class="abt-hero__actions">
				<a class="abc-btn abc-btn--accent" href="<?php echo esc_url( $res_url ); ?>"><span><?php echo esc_html( abc_t( 'explore_research' ) ); ?></span><?php echo abc_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="abc-btn abc-btn--outline-light" href="<?php echo esc_url( abc_archive_url( 'abc_publication' ) ); ?>"><?php echo esc_html( abc_t( 'view_publications' ) ); ?></a>
				<a class="abc-btn abc-btn--outline-light" href="<?php echo esc_url( $cv['url'] ); ?>"<?php echo $cv['is_file'] ? ' download' : ''; ?>><?php echo abc_icon( 'download', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( abc_t( 'download_cv' ) ); ?></span></a>
			</div>
			<div class="abt-hero__profiles"><?php echo ABC_Render::profiles( 'icons' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
		<figure class="abt-hero__portrait">
			<span class="abt-orbit" aria-hidden="true">
				<i class="abt-orbit__ring"></i><i class="abt-orbit__ring abt-orbit__ring--2"></i>
				<b class="abt-chip abt-chip--1"><?php echo abc_icon( 'library', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></b>
				<b class="abt-chip abt-chip--2"><?php echo abc_icon( 'openaccess', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></b>
				<b class="abt-chip abt-chip--3"><?php echo abc_icon( 'ai', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></b>
				<b class="abt-chip abt-chip--4"><?php echo abc_icon( 'repository', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></b>
			</span>
			<?php echo abc_portrait( 'large', array( 'class' => 'abt-hero__img', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( current_user_can( 'manage_options' ) && ! absint( abc_setting( 'photo_id', 0 ) ) ) : ?>
				<figcaption class="abt-hero__hint"><a href="<?php echo esc_url( admin_url( 'admin.php?page=abc-profile' ) ); ?>">Upload your portrait</a></figcaption>
			<?php endif; ?>
		</figure>
	</div>
	<div class="abt-container abt-hero__topics" aria-label="<?php echo esc_attr( abc_t( 'topics' ) ); ?>">
		<ul>
			<li><?php echo esc_html( abc_t( 'topic_dl' ) ); ?></li>
			<li><?php echo esc_html( abc_t( 'topic_os' ) ); ?></li>
			<li><?php echo esc_html( abc_t( 'topic_ir' ) ); ?></li>
			<li><?php echo esc_html( abc_t( 'topic_sc' ) ); ?></li>
			<li><?php echo esc_html( abc_t( 'topic_ai' ) ); ?></li>
		</ul>
	</div>
</section>

<section class="abt-quick" aria-label="<?php echo esc_attr( abc_t( 'quick_access' ) ); ?>">
	<div class="abt-container">
		<ul class="abt-quick__grid">
			<?php
			$tiles = array(
				array( 'doc', abc_t( 'publications' ), abc_archive_url( 'abc_publication' ) ),
				array( 'library', abc_t( 'books' ), abc_archive_url( 'abc_book' ) ),
				array( 'data', abc_t( 'projects' ), abc_archive_url( 'abc_project' ) ),
				array( 'slides', abc_t( 'presentations' ), abc_archive_url( 'abc_presentation' ) ),
				array( 'graduation', abc_t( 'teaching' ), abc_archive_url( 'abc_teaching' ) ),
				array( 'check', abc_t( 'academic_service' ), abc_archive_url( 'abc_service' ) ),
				array( 'communication', abc_t( 'research_notes' ), abc_archive_url( 'abc_note' ) ),
				array( 'mail', abc_t( 'nav_contact' ), abc_contact_url() ),
			);
			foreach ( $tiles as $t ) :
				?>
				<li><a class="abt-tile" href="<?php echo esc_url( $t[2], array( 'http', 'https', 'mailto' ) ); ?>"><span class="abt-tile__icon"><?php echo abc_icon( $t[0], 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="abt-tile__label"><?php echo esc_html( $t[1] ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="abt-section abt-bio" aria-label="<?php echo esc_attr( abc_t( 'nav_about' ) ); ?>">
	<div class="abt-container abt-bio__inner">
		<p class="abt-bio__text"><?php echo esc_html( abc_ml( 'bio' ) ); ?></p>
		<a class="abc-btn abc-btn--ghost" href="<?php echo esc_url( abc_page_url( 'about' ) ); ?>"><span><?php echo esc_html( abc_t( 'about_me' ) ); ?></span><?php echo abc_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
</section>

<section class="abt-section abt-section--tint" aria-labelledby="areas-title">
	<div class="abt-container">
		<?php abt_section_head( abc_t( 'home_areas_title' ), abc_t( 'home_areas_intro' ), $res_url, abc_t( 'nav_research' ) ); ?>
		<div class="abt-tiles">
			<?php echo ABC_Render::research_areas( 8 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>

<?php $stats = ABC_Render::stats(); if ( $stats ) : ?>
<section class="abt-section abt-section--dark" aria-labelledby="impact-title">
	<div class="abt-container">
		<div class="abt-section__head"><div><h2 class="abt-section__title" id="impact-title"><?php echo esc_html( abc_t( 'home_impact_title' ) ); ?></h2></div></div>
		<?php echo $stats; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $projects ) : ?>
<section class="abt-section" aria-labelledby="projects-title">
	<div class="abt-container">
		<?php abt_section_head( abc_t( 'home_projects_title' ), abc_t( 'home_projects_intro' ), abc_archive_url( 'abc_project' ), abc_t( 'projects' ) ); ?>
		<div class="abc-grid abc-grid--cards">
			<?php foreach ( $projects as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $notes ) : ?>
<section class="abt-section abt-section--tint" aria-labelledby="notes-title">
	<div class="abt-container">
		<?php abt_section_head( abc_t( 'research_notes' ), abc_t( 'home_notes_intro' ), abc_archive_url( 'abc_note' ), abc_t( 'view_all' ) ); ?>
		<div class="abc-grid abc-grid--cards">
			<?php foreach ( $notes as $p ) { echo ABC_Render::card( $p ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="abt-cta" aria-labelledby="collab-title">
	<div class="abt-container abt-cta__inner">
		<div>
			<h2 id="collab-title"><?php echo esc_html( abc_t( 'home_collab_title' ) ); ?></h2>
			<p><?php echo esc_html( abc_t( 'home_collab_text' ) ); ?></p>
		</div>
		<div class="abt-cta__actions">
			<a class="abc-btn abc-btn--accent" href="<?php echo esc_url( abc_contact_url(), array( 'http', 'https', 'mailto' ) ); ?>"><?php echo abc_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( abc_t( 'nav_contact' ) ); ?></span></a>
			<a class="abc-btn abc-btn--outline-light" href="<?php echo esc_url( $cv['url'] ); ?>"<?php echo $cv['is_file'] ? ' download' : ''; ?>><span><?php echo esc_html( abc_t( 'download_cv' ) ); ?></span></a>
		</div>
	</div>
</section>

<?php
get_footer();
