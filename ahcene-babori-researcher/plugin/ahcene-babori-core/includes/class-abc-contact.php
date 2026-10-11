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
		if ( is_admin() ) {
			add_action( 'add_meta_boxes_abc_message', array( __CLASS__, 'add_box' ) );
			add_action( 'save_post_abc_message', array( __CLASS__, 'save_status' ) );
			add_filter( 'manage_abc_message_posts_columns', array( __CLASS__, 'columns' ) );
			add_action( 'manage_abc_message_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
			add_filter( 'add_menu_classes', array( __CLASS__, 'menu_bubble' ) );
			add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		}
	}

	/** Statuses of a message. */
	public static function statuses() {
		return array( 'new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived' );
	}

	/** Sender display name (supports messages saved by older versions). */
	public static function sender( $id ) {
		$n = trim( get_post_meta( $id, 'abc_msg_first', true ) . ' ' . get_post_meta( $id, 'abc_msg_last', true ) );
		return $n ? $n : (string) get_post_meta( $id, 'abc_msg_name', true );
	}

	public static function add_box() {
		add_meta_box( 'abc_msg_box', 'Message', array( __CLASS__, 'render_box' ), 'abc_message', 'normal', 'high' );
		remove_meta_box( 'submitdiv', 'abc_message', 'side' );
		add_meta_box( 'abc_msg_status', 'Status', array( __CLASS__, 'render_status' ), 'abc_message', 'side', 'high' );
	}

	public static function render_box( $post ) {
		$id    = $post->ID;
		$email = (string) get_post_meta( $id, 'abc_msg_email', true );
		$reason = (string) get_post_meta( $id, 'abc_msg_reason', true );
		if ( 'new' === ( get_post_meta( $id, 'abc_msg_status', true ) ? get_post_meta( $id, 'abc_msg_status', true ) : 'new' ) ) {
			update_post_meta( $id, 'abc_msg_status', 'read' );
		}
		echo '<table class="form-table" role="presentation"><tbody>';
		$rows = array(
			'First name'         => get_post_meta( $id, 'abc_msg_first', true ),
			'Last name'          => get_post_meta( $id, 'abc_msg_last', true ),
			'Reason for contact' => $reason ? $reason : $post->post_title,
			'Received'           => get_the_date( 'j F Y, H:i', $post ),
			'Language'           => strtoupper( (string) get_post_meta( $id, 'abc_msg_lang', true ) ),
		);
		if ( ! $rows['First name'] && get_post_meta( $id, 'abc_msg_name', true ) ) {
			$rows = array( 'Name' => get_post_meta( $id, 'abc_msg_name', true ) ) + array_slice( $rows, 2, null, true );
		}
		foreach ( $rows as $k => $v ) {
			if ( '' !== (string) $v ) {
				echo '<tr><th scope="row">' . esc_html( $k ) . '</th><td>' . esc_html( $v ) . '</td></tr>';
			}
		}
		echo '<tr><th scope="row">E-mail</th><td>' . ( $email ? '<a href="mailto:' . esc_attr( $email ) . '?subject=' . rawurlencode( 'Re: ' . ( $reason ? $reason : $post->post_title ) ) . '">' . esc_html( $email ) . '</a> — <a class="button button-small" href="mailto:' . esc_attr( $email ) . '?subject=' . rawurlencode( 'Re: ' . ( $reason ? $reason : $post->post_title ) ) . '">Reply by e-mail</a>' : '—' ) . '</td></tr>';
		echo '<tr><th scope="row">Message</th><td><div style="white-space:pre-wrap;background:#f6f7f7;border:1px solid #dcdcde;padding:12px;max-width:760px">' . esc_html( $post->post_content ) . '</div></td></tr></tbody></table>';
	}

	public static function render_status( $post ) {
		wp_nonce_field( 'abc_msg_status', 'abc_msg_status_nonce' );
		$cur = get_post_meta( $post->ID, 'abc_msg_status', true );
		$cur = $cur ? $cur : 'new';
		echo '<select name="abc_msg_status" style="width:100%">';
		foreach ( self::statuses() as $k => $l ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $l ) . '</option>';
		}
		echo '</select><p class="description">Set to “Replied” once you have answered.</p><p>';
		submit_button( 'Update', 'primary', 'save', false );
		echo ' <a class="submitdelete" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Move to trash</a></p>';
	}

	public static function save_status( $post_id ) {
		if ( ! isset( $_POST['abc_msg_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_msg_status_nonce'] ) ), 'abc_msg_status' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$v = isset( $_POST['abc_msg_status'] ) ? sanitize_key( wp_unslash( $_POST['abc_msg_status'] ) ) : 'read';
		if ( isset( self::statuses()[ $v ] ) ) {
			update_post_meta( $post_id, 'abc_msg_status', $v );
		}
	}

	public static function columns( $c ) {
		return array( 'cb' => $c['cb'], 'abc_sender' => 'Sender', 'abc_email' => 'E-mail', 'title' => 'Reason for contact', 'abc_status' => 'Status', 'date' => 'Received' );
	}

	public static function column( $col, $id ) {
		if ( 'abc_sender' === $col ) {
			echo '<strong><a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( self::sender( $id ) ) . '</a></strong>';
		} elseif ( 'abc_email' === $col ) {
			$e = get_post_meta( $id, 'abc_msg_email', true );
			echo $e ? '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>' : '—';
		} elseif ( 'abc_status' === $col ) {
			$s = get_post_meta( $id, 'abc_msg_status', true );
			$s = $s ? $s : 'new';
			echo '<span style="' . ( 'new' === $s ? 'background:#d63638;color:#fff;' : 'background:#f0f0f1;' ) . 'padding:2px 8px;border-radius:10px;font-size:12px">' . esc_html( self::statuses()[ $s ] ?? $s ) . '</span>';
		}
	}

	public static function row_actions( $actions, $post ) {
		if ( 'abc_message' === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'], $actions['view'] );
		}
		return $actions;
	}

	/** Red bubble with the number of new messages. */
	public static function menu_bubble( $menu ) {
		$n = (int) ( new WP_Query( array( 'post_type' => 'abc_message', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( 'relation' => 'OR', array( 'key' => 'abc_msg_status', 'value' => 'new' ), array( 'key' => 'abc_msg_status', 'compare' => 'NOT EXISTS' ) ) ) ) )->found_posts; // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $n ) {
			foreach ( $menu as $k => $item ) {
				if ( isset( $item[2] ) && 'edit.php?post_type=abc_message' === $item[2] ) {
					$menu[ $k ][0] .= ' <span class="awaiting-mod">' . (int) $n . '</span>';
				}
			}
		}
		return $menu;
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
		$old    = array( 'first' => '', 'last' => '', 'email' => '', 'subject' => '', 'message' => '' );
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

			<div class="abc-field-row">
				<div class="abc-field<?php echo isset( $errors['first'] ) ? ' has-error' : ''; ?>">
					<label for="cf-first"><?php echo esc_html( abc_t( 'cf_first' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
					<input type="text" id="cf-first" name="abc_first" value="<?php echo esc_attr( $old['first'] ); ?>" required aria-required="true" autocomplete="given-name" maxlength="60" <?php echo isset( $errors['first'] ) ? 'aria-invalid="true"' : ''; ?>>
				</div>
				<div class="abc-field<?php echo isset( $errors['last'] ) ? ' has-error' : ''; ?>">
					<label for="cf-last"><?php echo esc_html( abc_t( 'cf_last' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
					<input type="text" id="cf-last" name="abc_last" value="<?php echo esc_attr( $old['last'] ); ?>" required aria-required="true" autocomplete="family-name" maxlength="60" <?php echo isset( $errors['last'] ) ? 'aria-invalid="true"' : ''; ?>>
				</div>
			</div>
			<div class="abc-field<?php echo isset( $errors['email'] ) ? ' has-error' : ''; ?>">
				<label for="cf-email"><?php echo esc_html( abc_t( 'cf_email' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
				<input type="email" id="cf-email" name="abc_email" value="<?php echo esc_attr( $old['email'] ); ?>" required aria-required="true" autocomplete="email" maxlength="150" dir="ltr" <?php echo isset( $errors['email'] ) ? 'aria-invalid="true"' : ''; ?>>
			</div>
			<div class="abc-field<?php echo isset( $errors['subject'] ) ? ' has-error' : ''; ?>">
				<label for="cf-subject"><?php echo esc_html( abc_t( 'cf_reason' ) ); ?> <span class="req" aria-hidden="true">*</span></label>
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
			'first'   => trim( preg_replace( '/[\r\n\t]+/', ' ', sanitize_text_field( wp_unslash( $_POST['abc_first'] ?? '' ) ) ) ),
			'last'    => trim( preg_replace( '/[\r\n\t]+/', ' ', sanitize_text_field( wp_unslash( $_POST['abc_last'] ?? '' ) ) ) ),
			'email'   => sanitize_email( wp_unslash( $_POST['abc_email'] ?? '' ) ),
			'subject' => trim( preg_replace( '/[\r\n\t]+/', ' ', sanitize_text_field( wp_unslash( $_POST['abc_subject'] ?? '' ) ) ) ),
			'message' => sanitize_textarea_field( wp_unslash( $_POST['abc_message'] ?? '' ) ),
		);
		$err = array();
		if ( mb_strlen( $v['first'] ) < 2 || mb_strlen( $v['first'] ) > 60 ) {
			$err['first'] = abc_t( 'cf_err_first' );
		}
		if ( mb_strlen( $v['last'] ) < 2 || mb_strlen( $v['last'] ) > 60 ) {
			$err['last'] = abc_t( 'cf_err_last' );
		}
		if ( ! is_email( $v['email'] ) ) {
			$err['email'] = abc_t( 'cf_err_email' );
		}
		if ( mb_strlen( $v['subject'] ) < 3 || mb_strlen( $v['subject'] ) > 150 ) {
			$err['subject'] = abc_t( 'cf_err_reason' );
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
		$full    = $v['first'] . ' ' . $v['last'];
		$body    = $v['message'] . "\n\n--\n" . $full . ' <' . $v['email'] . ">\nReason: " . $v['subject'] . "\nLanguage: " . abc_lang() . "\nPage: " . $back . "\n";
		$name_h  = trim( str_replace( array( '<', '>', '"', ',', ';', ':' ), '', $full ) );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name_h . ' <' . $v['email'] . '>' );
		$sent    = wp_mail( $to, $subject, $body, $headers );

		// Always keep the message in the dashboard (Contact Messages), so nothing is lost if mail delivery fails.
		$mid = wp_insert_post(
			array(
				'post_type'    => 'abc_message',
				'post_status'  => 'publish',
				'post_title'   => $full . ' — ' . $v['subject'],
				'post_content' => $v['message'],
			)
		);
		if ( $mid && ! is_wp_error( $mid ) ) {
			update_post_meta( $mid, 'abc_msg_first', $v['first'] );
			update_post_meta( $mid, 'abc_msg_last', $v['last'] );
			update_post_meta( $mid, 'abc_msg_email', $v['email'] );
			update_post_meta( $mid, 'abc_msg_reason', $v['subject'] );
			update_post_meta( $mid, 'abc_msg_lang', abc_lang() );
			update_post_meta( $mid, 'abc_msg_status', 'new' );
			update_post_meta( $mid, 'abc_msg_sent', $sent ? '1' : '0' );
		}
		set_transient( $rl, $cnt + 1, HOUR_IN_SECONDS );
		if ( ! $mid || is_wp_error( $mid ) ) {
			if ( ! $sent ) {
				self::fail( $back, array( 'message' => abc_t( 'cf_err_mail' ) ), $v );
			}
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
