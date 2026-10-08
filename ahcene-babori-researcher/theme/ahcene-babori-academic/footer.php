<?php
/**
 * Footer.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="abt-footer">
	<div class="abt-container abt-footer__grid">
		<div class="abt-footer__brand">
			<p class="abt-footer__name"><?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?></p>
			<p class="abt-footer__role"><?php echo esc_html( abc_ml( 'role' ) ); ?></p>
			<p class="abt-footer__statement"><?php echo esc_html( abc_ml( 'tagline' ) ); ?></p>
		</div>
		<nav class="abt-footer__nav" aria-label="<?php echo esc_attr( abc_t( 'footer_menu' ) ); ?>">
			<p class="abt-footer__title"><?php echo esc_html( abc_t( 'explore' ) ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'abt-footer__menu', 'depth' => 1 ) );
			} else {
				echo '<ul class="abt-footer__menu">';
				foreach ( array( array( abc_t( 'nav_cv' ), abc_page_url( 'cv' ) ), array( abc_t( 'books' ), abc_archive_url( 'abc_book' ) ), array( abc_t( 'advanced_search' ), abc_page_url( 'discover' ) ), array( abc_t( 'nav_contact' ), abc_page_url( 'contact' ) ) ) as $i ) {
					echo '<li><a href="' . esc_url( $i[1] ) . '">' . esc_html( $i[0] ) . '</a></li>';
				}
				echo '</ul>';
			}
			?>
		</nav>
		<div class="abt-footer__contact">
			<p class="abt-footer__title"><?php echo esc_html( abc_t( 'nav_contact' ) ); ?></p>
			<?php $em = abc_setting( 'email', 'contact@ahcenebabori.com' ); ?>
			<p><a href="mailto:<?php echo esc_attr( antispambot( $em ) ); ?>"><?php echo esc_html( antispambot( $em ) ); ?></a></p>
			<?php echo ABC_Render::profiles( 'icons' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
	<div class="abt-container abt-footer__bottom">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?> · <?php echo esc_html( abc_t( 'rights' ) ); ?></p>
		<p><a href="#top"><?php echo esc_html( abc_t( 'back_to_top' ) ); ?></a></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
