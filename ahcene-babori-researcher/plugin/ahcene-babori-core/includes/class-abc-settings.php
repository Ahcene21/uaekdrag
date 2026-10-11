<?php
/**
 * "Researcher" dashboard menu: profile, identifiers, statistics, CV, contact.
 * Everything a researcher edits regularly is here – no code required.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Settings {

	const CAP = 'manage_options';

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_abc_save_settings', array( __CLASS__, 'handle_save' ) );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'dashboard_widget' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ABC_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Default option values. No identifiers are invented: only the contact e-mail
	 * and LinkedIn URL supplied by the researcher are pre-filled.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'name'                => 'Dr. Ahcene Babori',
			'photo_id'            => 0,
			'photo_alt'           => '',
			'orcid'               => '',
			'scholar'             => '',
			'scopus'              => '',
			'researchgate'        => '',
			'linkedin'            => 'https://linkedin.com/in/baboriahcene/',
			'institutional'       => '',
			'institutional_label' => '',
			'email'               => 'contact@ahcenebabori.com',
			'contact_to'          => '',
			'store_messages'      => '1',
			'cv_id_en'            => 0,
			'cv_id_fr'            => 0,
			'cv_id_ar'            => 0,
			'stats'               => array(
				array( 'value' => '10+', 'label_en' => 'Years of Professional Experience', 'label_fr' => 'Années d’expérience professionnelle', 'label_ar' => 'سنوات من الخبرة المهنية' ),
				array( 'value' => 'PhD', 'label_en' => 'Library & Information Science', 'label_fr' => 'Sciences de l’information et des bibliothèques', 'label_ar' => 'علم المكتبات والمعلومات' ),
				array( 'value' => 'International', 'label_en' => 'Research Activities', 'label_fr' => 'Activités de recherche', 'label_ar' => 'أنشطة بحثية' ),
				array( 'value' => 'Multiple', 'label_en' => 'Repository Projects', 'label_fr' => 'Projets de dépôts institutionnels', 'label_ar' => 'مشاريع المستودعات الرقمية' ),
				array( 'value' => 'Academic', 'label_en' => 'Peer Review & Service', 'label_fr' => 'Évaluation par les pairs et service académique', 'label_ar' => 'التحكيم العلمي والخدمة الأكاديمية' ),
			),
		);
	}

	/** @return array slug => [ title, callback ] */
	private static function pages() {
		return array(
			'abc-researcher'   => array( 'Overview', 'render_overview' ),
			'abc-profile'      => array( 'Profile & Photo', 'render_profile' ),
			'abc-identifiers'  => array( 'Identifiers & Links', 'render_identifiers' ),
			'abc-stats'        => array( 'Statistics', 'render_stats' ),
			'abc-cv'           => array( 'CV files', 'render_cv' ),
			'abc-contact'      => array( 'Contact form', 'render_contact' ),
		);
	}

	/**
	 * Register menu.
	 */
	public static function menu() {
		add_menu_page( 'Researcher', 'Researcher', self::CAP, 'abc-researcher', array( __CLASS__, 'render_overview' ), 'dashicons-welcome-learn-more', 3 );
		foreach ( self::pages() as $slug => $p ) {
			add_submenu_page( 'abc-researcher', $p[0], $p[0], self::CAP, $slug, array( __CLASS__, $p[1] ) );
		}
		add_submenu_page( 'abc-researcher', 'Sample content', 'Sample content', self::CAP, 'abc-demo', array( 'ABC_Demo', 'render_page' ) );
		add_submenu_page( 'abc-researcher', 'Import (beta)', 'Import (beta)', self::CAP, 'abc-import', array( 'ABC_Import', 'render_page' ) );
	}

	/**
	 * Plugin list shortcut.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=abc-researcher' ) ) . '">Researcher dashboard</a>' );
		return $links;
	}

	/**
	 * Notice after save.
	 */
	private static function notices() {
		if ( isset( $_GET['abc_saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>Saved.</p></div>';
		}
		if ( isset( $_GET['abc_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['abc_error'] ) ) ) . '</p></div>'; // phpcs:ignore WordPress.Security.NonceVerification
		}
	}

	/**
	 * Wrap a settings form.
	 *
	 * @param string   $title Title.
	 * @param string   $tab   Tab key (used by the save handler).
	 * @param callable $body  Fields renderer.
	 * @param string   $intro Intro text.
	 */
	private static function form( $title, $tab, $body, $intro = '' ) {
		echo '<div class="wrap abc-tabs"><h1>' . esc_html( $title ) . '</h1>';
		self::notices();
		if ( $intro ) {
			echo '<p class="abc-note">' . wp_kses_post( $intro ) . '</p>';
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'abc_save_settings_' . $tab, 'abc_settings_nonce' );
		echo '<input type="hidden" name="action" value="abc_save_settings"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
		call_user_func( $body );
		submit_button( 'Save changes' );
		echo '</form></div>';
	}

	/**
	 * Row helper: text input.
	 */
	private static function row_text( $label, $name, $value, $help = '', $type = 'text', $placeholder = '' ) {
		echo '<tr><th scope="row"><label for="abc_s_' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td>';
		printf( '<input type="%s" class="regular-text" style="width:100%%;max-width:560px" id="abc_s_%s" name="abc[%s]" value="%s" placeholder="%s">', esc_attr( $type ), esc_attr( $name ), esc_attr( $name ), esc_attr( (string) $value ), esc_attr( $placeholder ) );
		if ( $help ) {
			echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
		}
		echo '</td></tr>';
	}

	/**
	 * Row helper: one value per language.
	 */
	private static function row_ml( $label, $key, $textarea = false, $help = '' ) {
		$s = abc_settings();
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><div class="abc-ml">';
		foreach ( abc_languages() as $l ) {
			$name = "ml_{$key}_{$l}";
			$val  = $s[ $name ] ?? '';
			$dir  = 'ar' === $l ? ' dir="rtl"' : '';
			echo '<div><label for="abc_s_' . esc_attr( $name ) . '">' . esc_html( strtoupper( $l ) ) . '</label>';
			if ( $textarea ) {
				printf( '<textarea class="widefat" rows="4" id="abc_s_%1$s" name="abc[%1$s]"%3$s placeholder="%4$s">%2$s</textarea>', esc_attr( $name ), esc_textarea( (string) $val ), $dir, esc_attr( abc_t_lang( 'default_' . $key, $l ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				printf( '<input type="text" class="widefat" id="abc_s_%1$s" name="abc[%1$s]" value="%2$s"%3$s placeholder="%4$s">', esc_attr( $name ), esc_attr( (string) $val ), $dir, esc_attr( abc_t_lang( 'default_' . $key, $l ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</div>';
		}
		echo '</div><p class="description">Leave a field empty to use the built-in text (shown greyed as placeholder). ' . esc_html( $help ) . '</p></td></tr>';
	}

	/**
	 * Row helper: media picker.
	 */
	private static function row_media( $label, $name, $mime, $help = '', $image = false ) {
		$id   = absint( abc_settings()[ $name ] ?? 0 );
		$file = $id ? wp_basename( (string) get_attached_file( $id ) ) : '';
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><div class="abc-media" data-mime="' . esc_attr( $mime ) . '">';
		if ( $image ) {
			$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
			echo '<img class="abc-media__preview" src="' . esc_url( (string) $src ) . '" alt="" style="max-width:160px;height:auto;display:block;margin-bottom:8px;border-radius:4px"' . ( $src ? '' : ' hidden' ) . '>';
		}
		printf( '<input type="hidden" name="abc[%s]" value="%s">', esc_attr( $name ), esc_attr( (string) $id ) );
		echo '<span class="abc-media__name">' . esc_html( $file ? $file : 'No file selected' ) . '</span> ';
		echo '<button type="button" class="button abc-media__pick">Choose / upload</button> ';
		echo '<button type="button" class="button-link abc-media__clear"' . ( $id ? '' : ' hidden' ) . '>Remove</button>';
		echo '</div>';
		if ( $help ) {
			echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
		}
		echo '</td></tr>';
	}

	/* ---------------------------------------------------------------- pages */

	/**
	 * Overview with quick actions.
	 */
	public static function render_overview() {
		$s     = abc_settings();
		$new   = static function ( $pt ) {
			return admin_url( 'post-new.php?post_type=' . $pt );
		};
		$cards = array(
			array( 'Add a publication', 'Journal article, chapter, conference paper…', $new( 'abc_publication' ) ),
			array( 'Add a book', 'Monograph, edited volume', $new( 'abc_book' ) ),
			array( 'Add a project', 'Research or repository project', $new( 'abc_project' ) ),
			array( 'Add a presentation', 'Conference talk, keynote, poster', $new( 'abc_presentation' ) ),
			array( 'Add academic service', 'Reviewer, editorial board, committee', $new( 'abc_service' ) ),
			array( 'Add a course', 'Teaching, supervision, materials', $new( 'abc_teaching' ) ),
			array( 'Publish a blog post', 'Short academic article for the Blog', $new( 'abc_note' ) ),
			array( 'Edit About timeline', 'Education, positions, experience, awards', admin_url( 'edit.php?post_type=abc_career' ) ),
			array( 'Update photo', 'Portrait shown on the homepage', admin_url( 'admin.php?page=abc-profile' ) ),
			array( 'Upload CV (PDF)', 'One file per language', admin_url( 'admin.php?page=abc-cv' ) ),
			array( 'Update ORCID & profiles', 'ORCID, Scholar, Scopus, ResearchGate…', admin_url( 'admin.php?page=abc-identifiers' ) ),
			array( 'Update statistics', 'Numbers on the homepage', admin_url( 'admin.php?page=abc-stats' ) ),
			array( 'Research areas', 'Edit the eight research-area cards', admin_url( 'edit-tags.php?taxonomy=abc_research_area&post_type=abc_publication' ) ),
			array( 'Manage translations', function_exists( 'pll_languages_list' ) ? 'Polylang languages & strings' : 'Install Polylang first', function_exists( 'pll_languages_list' ) ? admin_url( 'admin.php?page=mlang' ) : admin_url( 'plugin-install.php?s=polylang&tab=search&type=term' ) ),
			array( 'Menus', 'Primary navigation per language', admin_url( 'nav-menus.php' ) ),
			array( 'Contact messages', 'Name, e-mail and reason of each sender', admin_url( 'edit.php?post_type=abc_message' ) ),
		);
		echo '<div class="wrap"><h1>Researcher dashboard</h1>';
		echo '<p>Everything you need to keep the website up to date, without any code.</p><div class="abc-admin-cards">';
		foreach ( $cards as $c ) {
			echo '<a class="abc-admin-card" href="' . esc_url( $c[2] ) . '"><strong>' . esc_html( $c[0] ) . '</strong><span>' . esc_html( $c[1] ) . '</span></a>';
		}
		echo '</div><h2>Setup checklist</h2><table class="widefat striped" style="max-width:760px"><tbody>';
		$checks = array(
			array( 'Polylang installed and active (EN / FR / AR)', function_exists( 'pll_languages_list' ) ),
			array( 'Theme “Ahcene Babori Academic” active', 'ahcene-babori-academic' === get_template() ),
			array( 'Profile photo uploaded', (bool) absint( $s['photo_id'] ) ),
			array( 'CV PDF uploaded (at least one language)', (bool) abc_cv_file_url() ),
			array( 'ORCID added', (bool) $s['orcid'] ),
			array( 'Sample content replaced with verified information', ! self::has_demo() ),
		);
		foreach ( $checks as $c ) {
			echo '<tr><td style="width:36px;font-weight:700;color:' . ( $c[1] ? '#0a7d33' : '#b32d2e' ) . '">' . ( $c[1] ? '✔' : '✖' ) . '</td><td>' . esc_html( $c[0] ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	/**
	 * Any sample entries left?
	 *
	 * @return bool
	 */
	public static function has_demo() {
		$q = new WP_Query(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_abc_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
				'no_found_rows'  => true,
			)
		);
		return ! empty( $q->posts );
	}

	/**
	 * Profile tab.
	 */
	public static function render_profile() {
		self::form(
			'Profile & Photo',
			'profile',
			static function () {
				$s = abc_settings();
				echo '<table class="form-table" role="presentation">';
				self::row_text( 'Display name', 'name', $s['name'] );
				self::row_media( 'Portrait photo', 'photo_id', 'image', 'Recommended: portrait orientation, at least 800 × 1000 px (JPG/PNG/WebP). Until you upload one, a neutral placeholder is shown.', true );
				self::row_text( 'Photo description (alt text)', 'photo_alt', $s['photo_alt'], 'Short description for screen readers, e.g. “Portrait of Dr. Ahcene Babori”.' );
				self::row_ml( 'Professional title', 'role' );
				self::row_ml( 'Positioning statement', 'tagline', true, 'Appears under the title in the homepage hero.' );
				self::row_ml( 'Affiliation (optional)', 'affiliation', false, 'Only add verified information, e.g. your university and faculty.' );
				self::row_ml( 'Short biography (homepage)', 'bio', true );
				echo '</table>';
			},
			'These texts exist in English, French and Arabic. Each language shows its own version.'
		);
	}

	/**
	 * Identifiers tab.
	 */
	public static function render_identifiers() {
		self::form(
			'Identifiers & Links',
			'identifiers',
			static function () {
				$s = abc_settings();
				echo '<table class="form-table" role="presentation">';
				self::row_text( 'ORCID iD', 'orcid', $s['orcid'], 'Your iD (0000-0000-0000-0000) or the full https://orcid.org/… link.', 'text', '0000-0000-0000-0000' );
				self::row_text( 'Google Scholar URL', 'scholar', $s['scholar'], '', 'url', 'https://scholar.google.com/citations?user=…' );
				self::row_text( 'Scopus Author URL', 'scopus', $s['scopus'], '', 'url', 'https://www.scopus.com/authid/detail.uri?authorId=…' );
				self::row_text( 'ResearchGate URL', 'researchgate', $s['researchgate'], '', 'url', 'https://www.researchgate.net/profile/…' );
				self::row_text( 'LinkedIn URL', 'linkedin', $s['linkedin'], '', 'url' );
				self::row_text( 'Institutional profile URL', 'institutional', $s['institutional'], '', 'url' );
				self::row_text( 'Institutional profile label', 'institutional_label', $s['institutional_label'], 'Optional, e.g. the name of your university.' );
				self::row_text( 'Public e-mail address', 'email', $s['email'], 'Shown on the Contact page and in the footer.', 'email' );
				echo '</table>';
			},
			'Only fields that contain a value are displayed on the website. Nothing is pre-filled except your public e-mail and LinkedIn link.'
		);
	}

	/**
	 * Statistics tab.
	 */
	public static function render_stats() {
		self::form(
			'Statistics',
			'stats',
			static function () {
				$stats = (array) abc_settings()['stats'];
				while ( count( $stats ) < 6 ) {
					$stats[] = array();
				}
				echo '<table class="widefat abc-stats-table" style="max-width:1000px"><thead><tr><th>#</th><th>Value</th><th>Label (EN)</th><th>Label (FR)</th><th>Label (AR)</th></tr></thead><tbody>';
				foreach ( array_slice( $stats, 0, 6 ) as $i => $row ) {
					echo '<tr><td>' . (int) ( $i + 1 ) . '</td>';
					printf( '<td><input type="text" name="abc[stats][%d][value]" value="%s" maxlength="24"></td>', (int) $i, esc_attr( $row['value'] ?? '' ) );
					foreach ( abc_languages() as $l ) {
						printf( '<td><input type="text" name="abc[stats][%d][label_%s]" value="%s"%s></td>', (int) $i, esc_attr( $l ), esc_attr( $row[ 'label_' . $l ] ?? '' ), 'ar' === $l ? ' dir="rtl"' : '' );
					}
					echo '</tr>';
				}
				echo '</tbody></table>';
			},
			'Edit the numbers or words shown in the “Research impact” block. Leave a row empty to hide it. <strong>Never publish citation counts or an h-index unless they are verified and dated.</strong>'
		);
	}

	/**
	 * CV tab.
	 */
	public static function render_cv() {
		self::form(
			'CV files',
			'cv',
			static function () {
				echo '<table class="form-table" role="presentation">';
				self::row_media( 'CV – English (PDF)', 'cv_id_en', 'application/pdf' );
				self::row_media( 'CV – Français (PDF)', 'cv_id_fr', 'application/pdf' );
				self::row_media( 'CV – العربية (PDF)', 'cv_id_ar', 'application/pdf' );
				echo '</table>';
			},
			'The “Download CV” buttons use the file of the visitor’s language, or another language if missing. Without any file, the buttons open the online CV page. To replace a CV, choose a new file here – the old link stops being used immediately.'
		);
	}

	/**
	 * Contact tab.
	 */
	public static function render_contact() {
		self::form(
			'Contact form',
			'contact',
			static function () {
				$s = abc_settings();
				echo '<table class="form-table" role="presentation">';
				self::row_text( 'Send messages to', 'contact_to', $s['contact_to'], 'Leave empty to use the public e-mail address.', 'email', $s['email'] );
				echo '</table>';
				echo '<p class="abc-note"><strong>Spam protection:</strong> hidden honeypot field, minimum fill-in time, signed form token, nonce and rate limit are always active. No CAPTCHA service and no tracking cookies are used.</p>';
			}
		);
	}

	/* ---------------------------------------------------------------- save */

	/**
	 * Save handler (merges only the submitted tab into the option).
	 */
	public static function handle_save() {
		$tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : '';
		if ( ! current_user_can( self::CAP ) || ! isset( $_POST['abc_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_settings_nonce'] ) ), 'abc_save_settings_' . $tab ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'ahcene-babori-core' ), 403 );
		}
		$in    = isset( $_POST['abc'] ) && is_array( $_POST['abc'] ) ? wp_unslash( $_POST['abc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- each key sanitised below.
		$cur   = get_option( 'abc_settings', array() );
		$cur   = is_array( $cur ) ? $cur : array();
		$err   = '';
		$url   = static function ( $v ) {
			$v = trim( (string) $v );
			return preg_match( '#^https?://#i', $v ) ? esc_url_raw( $v ) : '';
		};
		$pages = array(
			'profile'     => 'abc-profile',
			'identifiers' => 'abc-identifiers',
			'stats'       => 'abc-stats',
			'cv'          => 'abc-cv',
			'contact'     => 'abc-contact',
		);

		switch ( $tab ) {
			case 'profile':
				$cur['name']      = sanitize_text_field( $in['name'] ?? '' );
				$cur['photo_id']  = absint( $in['photo_id'] ?? 0 );
				$cur['photo_alt'] = sanitize_text_field( $in['photo_alt'] ?? '' );
				foreach ( array( 'role' => false, 'tagline' => true, 'affiliation' => false, 'bio' => true ) as $k => $area ) {
					foreach ( abc_languages() as $l ) {
						$v = $in[ "ml_{$k}_{$l}" ] ?? '';
						$cur[ "ml_{$k}_{$l}" ] = $area ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
					}
				}
				if ( $cur['photo_id'] && ! wp_attachment_is_image( $cur['photo_id'] ) ) {
					$cur['photo_id'] = 0;
					$err             = 'The selected photo is not an image and was ignored.';
				}
				break;
			case 'identifiers':
				$orcid = trim( (string) ( $in['orcid'] ?? '' ) );
				if ( '' !== $orcid && ! preg_match( '/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid ) ) {
					$orcid = preg_match( '#^https://orcid\.org/\d{4}-\d{4}-\d{4}-\d{3}[\dX]$#', $orcid ) ? esc_url_raw( $orcid ) : '';
					if ( '' === $orcid ) {
						$err = 'The ORCID iD was not valid (expected 0000-0000-0000-0000) and was left empty.';
					}
				}
				$cur['orcid'] = $orcid;
				foreach ( array( 'scholar', 'scopus', 'researchgate', 'linkedin', 'institutional' ) as $k ) {
					$cur[ $k ] = $url( $in[ $k ] ?? '' );
				}
				$cur['institutional_label'] = sanitize_text_field( $in['institutional_label'] ?? '' );
				$email                      = sanitize_email( $in['email'] ?? '' );
				$cur['email']               = is_email( $email ) ? $email : self::defaults()['email'];
				break;
			case 'stats':
				$rows = array();
				foreach ( array_slice( (array) ( $in['stats'] ?? array() ), 0, 6 ) as $r ) {
					$row = array( 'value' => sanitize_text_field( $r['value'] ?? '' ) );
					foreach ( abc_languages() as $l ) {
						$row[ 'label_' . $l ] = sanitize_text_field( $r[ 'label_' . $l ] ?? '' );
					}
					if ( '' !== $row['value'] || '' !== $row['label_en'] ) {
						$rows[] = $row;
					}
				}
				$cur['stats'] = $rows;
				break;
			case 'cv':
				foreach ( abc_languages() as $l ) {
					$id = absint( $in[ 'cv_id_' . $l ] ?? 0 );
					if ( $id && 'application/pdf' !== get_post_mime_type( $id ) ) {
						$id  = 0;
						$err = 'Only PDF files can be used as CV; a non-PDF selection was ignored.';
					}
					$cur[ 'cv_id_' . $l ] = $id;
				}
				break;
			case 'contact':
				$to                    = sanitize_email( $in['contact_to'] ?? '' );
				$cur['contact_to']     = is_email( $to ) ? $to : '';
				$cur['store_messages'] = '1';
				break;
			default:
				wp_die( 'Unknown settings tab.', 400 );
		}
		update_option( 'abc_settings', $cur, false );
		$args = array( 'page' => $pages[ $tab ], 'abc_saved' => 1 );
		if ( $err ) {
			$args['abc_error'] = rawurlencode( $err );
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Dashboard widget with quick links.
	 */
	public static function dashboard_widget() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'abc_quick',
			'Researcher – quick actions',
			static function () {
				$links = array(
					'Add publication' => 'post-new.php?post_type=abc_publication',
					'Add project'     => 'post-new.php?post_type=abc_project',
					'Add research note' => 'post-new.php?post_type=abc_note',
					'Researcher dashboard' => 'admin.php?page=abc-researcher',
				);
				echo '<ul>';
				foreach ( $links as $label => $path ) {
					echo '<li><a href="' . esc_url( admin_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>';
				}
				echo '</ul>';
			}
		);
	}
}
