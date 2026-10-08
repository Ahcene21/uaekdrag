<?php
/**
 * Contact form: nonce, honeypot, time trap, rate limit, validation, optional storage.
 * Works without JavaScript and without external services.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Contact {

	const MIN_SECONDS = 4;
	const MAX_SECONDS = 172800; // 2 days.
	const RATE_LIMIT  = 5;      // Messages per hour per visitor.

	/**
	 * Hook.
	 */
	public static function init() {
		add_shortcode( 'abc_contact_form', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_post_nopriv_abc_contact', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_abc_contact', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Signed timestamp token.
	 *
	 * @param int $ts Timestamp.
	 * @return string
	 */
	private static function token( $ts ) {
		return $ts . '.' . substr( hash_hmac( 'sha256', 'abc_contact|' . $ts, wp_salt( 'nonce' ) ), 0, 20 );
	}

	/**
	 * Render the form.
	 *
	 * @return string
	 */
	public static function shortcode() {
		$status = isset( $_GET['abc_contact'] ) ? sanitize_key( wp_unslash( $_GET['abc_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$old    = array( 'name' => '', 'email' => '', 'subject' => '', 'message' => '' );
		$errors = array();
		if ( 'error' === $status && isset( $_GET['abc_cf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$key  = sanitize_key( wp_unslash( $_GET['abc_cf'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$data = get_transient( 'abc_cf_' . $key );
			if ( is_array( $data ) ) {
				$old    = wp_parse_args( $data['values'], $old );
				$errors = (array) $data['errors'];
			}
		}
		ob_start();
		echo '<div class="abc-form-wrap" id="contact-form">';
		if ( 'ok' === $status ) {
			echo '<div class="abc-alert abc-alert--success" role="status"><strong>' . esc_html( abc_t( 'cf_thanks' ) ) . '</strong> ' . esc_html( abc_t( 'cf_thanks_text' ) ) . '</div>';
		} elseif ( 'error' === $status ) {
			echo '<div class="abc-alert abc-alert--error" role="alert" tabindex="-1" id="abc-form-errors"><strong>' . esc_html( abc_t( 'cf_error_title' ) ) . '</strong>';
			if ( $errors ) {
				echo '<ul>';
				foreach ( $errors as $field => $msg ) {
					echo '<li><a href="#cf-' . esc_attr( $field ) . '">' . esc_html( $msg ) . '</a></li>';
				}
				echo '</ul>';
			}
			echo '</div>';
		}
		$ts = time();
		?>
		<form class="abc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
			<input type="hidden" name="action" value="abc_contact">
			<?php wp_nonce_field( 'abc_contact_send', 'abc_cf_nonce' ); ?>
			<input type="hidden" name="abc_cf_token" value="<?php echo esc_attr( self::token( $ts ) ); ?>">
			<input type="hidden" name="abc_cf_back" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>">
			<div class="abc-hp" aria-hidden="true"><label for="cf-website"><?php echo esc_html( abc_t( 'cf_hp' ) ); ?></label><input type="text" id="cf-website" name="abc_website" value="" tabindex="-1" autocomplete="off"></div>

			<div class="abc-field<?php echo isset( $errors['name'] ) ? ' has-error' : ''; ?>">
				<label for="cf-name"><?php echo esc_html( abc_t( 'cf_name' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
				<input type="text" id="cf-name" name="abc_name" value="<?php echo esc_attr( $old['name'] ); ?>" required aria-required="true" autocomplete="name" maxlength="100" <?php echo isset( $errors['name'] ) ? 'aria-invalid="true"' : ''; ?>>
			</div>
			<div class="abc-field<?php echo isset( $errors['email'] ) ? ' has-error' : ''; ?>">
				<label for="cf-email"><?php echo esc_html( abc_t( 'cf_email' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
				<input type="email" id="cf-email" name="abc_email" value="<?php echo esc_attr( $old['email'] ); ?>" required aria-required="true" autocomplete="email" maxlength="150" dir="ltr" <?php echo isset( $errors['email'] ) ? 'aria-invalid="true"' : ''; ?>>
			</div>
			<div class="abc-field<?php echo isset( $errors['subject'] ) ? ' has-error' : ''; ?>">
				<label for="cf-subject"><?php echo esc_html( abc_t( 'cf_subject' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
				<input type="text" id="cf-subject" name="abc_subject" value="<?php echo esc_attr( $old['subject'] ); ?>" required aria-required="true" maxlength="150" <?php echo isset( $errors['subject'] ) ? 'aria-invalid="true"' : ''; ?>>
			</div>
			<div class="abc-field<?php echo isset( $errors['message'] ) ? ' has-error' : ''; ?>">
				<label for="cf-message"><?php echo esc_html( abc_t( 'cf_message' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
				<textarea id="cf-message" name="abc_message" rows="7" required aria-required="true" maxlength="5000" <?php echo isset( $errors['message'] ) ? 'aria-invalid="true"' : ''; ?>><?php echo esc_textarea( $old['message'] ); ?></textarea>
			</div>
			<p class="abc-form__note"><?php echo esc_html( abc_t( 'cf_privacy' ) ); ?></p>
			<button type="submit" class="abc-btn abc-btn--primary"><?php echo esc_html( abc_t( 'cf_send' ) ); ?></button>
		</form>
		<?php
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Redirect helper.
	 *
	 * @param string $back   Return URL.
	 * @param string $status ok|error.
	 * @param array  $extra  Extra query args.
	 */
	private static function back( $back, $status, $extra = array() ) {
		$url = add_query_arg( array_merge( array( 'abc_contact' => $status ), $extra ), $back );
		wp_safe_redirect( $url . '#contact-form' );
		exit;
	}

	/**
	 * Process submission.
	 */
	public static function handle() {
		$back = isset( $_POST['abc_cf_back'] ) ? esc_url_raw( wp_unslash( $_POST['abc_cf_back'] ) ) : home_url( '/' );
		$back = wp_validate_redirect( $back, home_url( '/' ) );
		$back = remove_query_arg( array( 'abc_contact', 'abc_cf' ), $back );

		// 1. Nonce.
		if ( ! isset( $_POST['abc_cf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_cf_nonce'] ) ), 'abc_contact_send' ) ) {
			self::fail( $back, array( 'message' => abc_t( 'cf_err_session' ) ), array() );
		}
		// 2. Honeypot – pretend success to bots.
		if ( ! empty( $_POST['abc_website'] ) ) {
			self::back( $back, 'ok' );
		}
		// 3. Signed time token.
		$tok   = isset( $_POST['abc_cf_token'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_cf_token'] ) ) : '';
		$parts = explode( '.', $tok );
		$ts    = isset( $parts[0] ) ? absint( $parts[0] ) : 0;
		if ( ! $ts || ! hash_equals( self::token( $ts ), $tok ) || ( time() - $ts ) > self::MAX_SECONDS ) {
			self::fail( $back, array( 'message' => abc_t( 'cf_err_session' ) ), array() );
		}
		if ( ( time() - $ts ) < self::MIN_SECONDS ) {
			self::back( $back, 'ok' ); // Too fast: bot. Silent.
		}
		// 4. Rate limit.
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$rl  = 'abc_rl_' . md5( $ip . wp_salt( 'auth' ) );
		$cnt = (int) get_transient( $rl );
		if ( $cnt >= self::RATE_LIMIT ) {
			self::fail( $back, array( 'message' => abc_t( 'cf_err_rate' ) ), array() );
		}

		// 5. Validate.
		$v = array(
			'name'    => trim( preg_replace( '/[\r\n\t]+/', ' ', sanitize_text_field( wp_unslash( $_POST['abc_name'] ?? '' ) ) ) ),
			'email'   => sanitize_email( wp_unslash( $_POST['abc_email'] ?? '' ) ),
			'subject' => trim( preg_replace( '/[\r\n\t]+/', ' ', sanitize_text_field( wp_unslash( $_POST['abc_subject'] ?? '' ) ) ) ),
			'message' => sanitize_textarea_field( wp_unslash( $_POST['abc_message'] ?? '' ) ),
		);
		$err = array();
		if ( mb_strlen( $v['name'] ) < 2 || mb_strlen( $v['name'] ) > 100 ) {
			$err['name'] = abc_t( 'cf_err_name' );
		}
		if ( ! is_email( $v['email'] ) ) {
			$err['email'] = abc_t( 'cf_err_email' );
		}
		if ( mb_strlen( $v['subject'] ) < 3 || mb_strlen( $v['subject'] ) > 150 ) {
			$err['subject'] = abc_t( 'cf_err_subject' );
		}
		if ( mb_strlen( $v['message'] ) < 10 || mb_strlen( $v['message'] ) > 5000 ) {
			$err['message'] = abc_t( 'cf_err_message' );
		}
		if ( preg_match_all( '#https?://#i', $v['message'] ) > 3 ) {
			$err['message'] = abc_t( 'cf_err_links' );
		}
		if ( $err ) {
			self::fail( $back, $err, $v );
		}
		$err = apply_filters( 'abc_contact_spam_check', array(), $v ); // Hook for CAPTCHA / Akismet integrations.
		if ( $err ) {
			self::fail( $back, (array) $err, $v );
		}

		// 6. Deliver.
		$to      = abc_setting( 'contact_to', '' );
		$to      = is_email( $to ) ? $to : abc_setting( 'email', 'contact@ahcenebabori.com' );
		$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$subject = '[' . $site . '] ' . $v['subject'];
		$body    = $v['message'] . "\n\n--\n" . $v['name'] . ' <' . $v['email'] . ">\n" . 'Language: ' . abc_lang() . "\nPage: " . $back . "\n";
		$name_h  = trim( str_replace( array( '<', '>', '"', ',', ';', ':' ), '', $v['name'] ) );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name_h . ' <' . $v['email'] . '>' );
		$sent    = wp_mail( $to, $subject, $body, $headers );

		if ( abc_setting( 'store_messages', '' ) ) {
			$mid = wp_insert_post(
				array(
					'post_type'    => 'abc_message',
					'post_status'  => 'publish',
					'post_title'   => $v['subject'],
					'post_content' => $v['message'],
				)
			);
			if ( $mid && ! is_wp_error( $mid ) ) {
				update_post_meta( $mid, 'abc_msg_name', $v['name'] );
				update_post_meta( $mid, 'abc_msg_email', $v['email'] );
				update_post_meta( $mid, 'abc_msg_lang', abc_lang() );
				update_post_meta( $mid, 'abc_msg_sent', $sent ? '1' : '0' );
			}
		}
		set_transient( $rl, $cnt + 1, HOUR_IN_SECONDS );
		if ( ! $sent && ! abc_setting( 'store_messages', '' ) ) {
			self::fail( $back, array( 'message' => abc_t( 'cf_err_mail' ) ), $v );
		}
		self::back( $back, 'ok' );
	}

	/**
	 * Redirect with errors and sticky values.
	 *
	 * @param string $back   URL.
	 * @param array  $errors Errors by field.
	 * @param array  $values Posted values.
	 */
	private static function fail( $back, $errors, $values ) {
		$key = wp_generate_password( 12, false, false );
		set_transient( 'abc_cf_' . strtolower( $key ), array( 'errors' => $errors, 'values' => $values ), 10 * MINUTE_IN_SECONDS );
		self::back( $back, 'error', array( 'abc_cf' => strtolower( $key ) ) );
	}
}
