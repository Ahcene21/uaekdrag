<?php
/**
 * Footer: name, title, contact details, social and academic profiles.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
$em       = abc_setting( 'email', 'contact@ahcenebabori.com' );
$profiles = ABC_Render::profiles( 'icons' );
?>
</main>

<footer class="abt-footer">
	<div class="abt-container abt-footer__grid">
		<div class="abt-footer__brand">
			<p class="abt-footer__name"><?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?></p>
			<p class="abt-footer__role"><?php echo esc_html( abc_ml( 'role' ) ); ?></p>
		</div>

		<div class="abt-footer__col abt-footer__contact">
			<p class="abt-footer__title"><?php echo esc_html( abc_t( 'nav_contact' ) ); ?></p>
			<p class="abt-footer__mail"><?php echo abc_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="mailto:<?php echo esc_attr( antispambot( $em ) ); ?>"><?php echo esc_html( antispambot( $em ) ); ?></a></p>
		</div>

		<?php if ( $profiles ) : ?>
			<div class="abt-footer__col">
				<p class="abt-footer__title"><?php echo esc_html( abc_t( 'research_profiles' ) ); ?></p>
				<?php echo $profiles; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="abt-footer__bar">
		<div class="abt-container abt-footer__bottom">
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( abc_setting( 'name', 'Dr. Ahcene Babori' ) ); ?> · <?php echo esc_html( abc_t( 'rights' ) ); ?></p>
			<a class="abt-totop" href="#top"><span><?php echo esc_html( abc_t( 'back_to_top' ) ); ?></span><i aria-hidden="true"><?php echo abc_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></i></a>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
