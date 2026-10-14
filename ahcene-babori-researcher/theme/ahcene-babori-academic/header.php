<?php
/**
 * Header.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
$cv   = abc_cv_link();
$home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<span id="top"></span>
<a class="skip-link screen-reader-text" href="#main"><?php echo esc_html( abc_t( 'skip_to_content' ) ); ?></a>

<header class="abt-header">
	<div class="abt-container abt-header__bar">
		<a class="abt-brand" href="<?php echo esc_url( $home ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="abt-brand__mark" aria-hidden="true">AB</span>
			<?php endif; ?>
			<span class="abt-brand__text">
				<span class="abt-brand__name"><?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?></span>
				<span class="abt-brand__role"><?php echo esc_html( abc_ml( 'role' ) ); ?></span>
			</span>
		</a>

		<div class="abt-header__tools">
			<?php abt_lang_switcher(); ?>
			<button type="button" class="abt-iconbtn" id="abt-search-toggle" aria-expanded="false" aria-controls="abt-searchbar">
				<?php echo abc_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php echo esc_html( abc_t( 'search' ) ); ?></span>
			</button>
			<a class="abc-btn abc-btn--primary abc-btn--sm abt-header__cv" href="<?php echo esc_url( $cv['url'] ); ?>"<?php echo $cv['is_file'] ? ' download' : ''; ?>>
				<?php echo abc_icon( 'download', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php echo esc_html( abc_t( 'download_cv' ) ); ?></span>
			</a>
			<button type="button" class="abt-iconbtn abt-menu-toggle" id="abt-menu-toggle" aria-expanded="false" aria-controls="abt-nav">
				<?php echo abc_icon( 'menu', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="screen-reader-text"><?php echo esc_html( abc_t( 'menu' ) ); ?></span>
			</button>
		</div>
	</div>

	<div class="abt-searchbar" id="abt-searchbar" hidden>
		<div class="abt-container">
			<?php get_search_form(); ?>
		</div>
	</div>

	<nav class="abt-nav" id="abt-nav" aria-label="<?php echo esc_attr( abc_t( 'primary_menu' ) ); ?>">
		<div class="abt-container">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'abt-menu',
					'fallback_cb'    => 'abt_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
			<?php $cvm = abc_cv_link(); ?>
			<a class="abc-btn abc-btn--primary abt-nav__cv" href="<?php echo esc_url( $cvm['url'] ); ?>"><?php echo esc_html( abc_t( 'download_cv' ) ); ?></a>
		</div>
	</nav>
</header>

<main id="main" class="abt-main" tabindex="-1">
