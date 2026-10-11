<?php
/**
 * Starter / sample content installer.
 *
 * - Idempotent: every created item is tagged with a key; running it again never duplicates
 *   and never overwrites anything you edited.
 * - Creates content in EN/FR/AR when Polylang is active (linked as translations), otherwise English only.
 * - Sample entries are flagged and show a "Demo" label until you replace them.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Demo {

	/** @var string[] */
	private static $report = array();

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'admin_post_abc_demo', array( __CLASS__, 'handle' ) );
	}

	/** Demo data. */
	public static function data() {
		static $d = null;
		if ( null === $d ) {
			$d = require ABC_DIR . 'demo/demo-data.php';
		}
		return $d;
	}

	/**
	 * Languages to create.
	 *
	 * @return string[]
	 */
	public static function langs() {
		if ( function_exists( 'pll_languages_list' ) ) {
			$have = array_map( 'abc_norm_lang', (array) pll_languages_list( array( 'fields' => 'slug' ) ) );
			$out  = array_values( array_intersect( abc_languages(), $have ) );
			return $out ? $out : array( 'en' );
		}
		return array( 'en' );
	}

	/** Missing Polylang languages. */
	private static function missing_langs() {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return array();
		}
		return array_values( array_diff( abc_languages(), (array) pll_languages_list( array( 'fields' => 'slug' ) ) ) );
	}

	private static function log( $msg ) {
		self::$report[] = $msg;
	}

	/* ------------------------------------------------------------------ find */

	private static function find_term( $tax, $key, $lang ) {
		$ids = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'fields'     => 'ids',
				'number'     => 1,
				'meta_key'   => 'abc_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => "$tax:$key:$lang", // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'       => '',
			)
		);
		return ( $ids && ! is_wp_error( $ids ) ) ? (int) $ids[0] : 0;
	}

	private static function find_post( $dkey, $type = 'any' ) {
		$ids = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_abc_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $dkey, // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
				'no_found_rows'  => true,
			)
		);
		return $ids ? (int) $ids[0] : 0;
	}

	private static function pick( $v, $lang ) {
		return ( is_array( $v ) && isset( $v['en'] ) ) ? ( $v[ $lang ] ?? $v['en'] ) : $v;
	}

	/* ------------------------------------------------------------- structure */

	/**
	 * Terms, pages, menus, basic options.
	 */
	public static function install_structure( $only_tax = '' ) {
		$d     = self::data();
		$langs = self::langs();
		self::log( 'Languages: ' . implode( ', ', array_map( 'strtoupper', $langs ) ) . ( function_exists( 'pll_languages_list' ) ? '' : ' (Polylang not active – English only)' ) );

		// Terms.
		foreach ( $d['taxonomies'] as $tax => $items ) {
			if ( ! taxonomy_exists( $tax ) || ( $only_tax && $tax !== $only_tax ) ) {
				continue;
			}
			$created = 0;
			foreach ( $items as $key => $it ) {
				$ids = array();
				foreach ( $langs as $l ) {
					$existing = self::find_term( $tax, $key, $l );
					if ( $existing ) {
						$ids[ $l ] = $existing;
						continue;
					}
					$slug = $it['slug'][ $l ] ?? ( 'en' === $l ? $key : $key . '-' . $l );
					$r    = wp_insert_term( $it['name'][ $l ], $tax, array( 'slug' => $slug, 'description' => $it['desc'][ $l ] ?? '' ) );
					if ( is_wp_error( $r ) ) {
						self::log( "Could not create term “{$it['name'][ $l ]}” ($tax): " . $r->get_error_message() );
						continue;
					}
					$tid = (int) $r['term_id'];
					update_term_meta( $tid, 'abc_key', $key );
					update_term_meta( $tid, 'abc_demo_key', "$tax:$key:$l" );
					if ( isset( $it['icon'] ) ) {
						update_term_meta( $tid, 'abc_icon', $it['icon'] );
					}
					if ( isset( $it['order'] ) ) {
						update_term_meta( $tid, 'abc_order', (int) $it['order'] );
					}
					if ( function_exists( 'pll_set_term_language' ) ) {
						pll_set_term_language( $tid, $l );
					}
					$ids[ $l ] = $tid;
					$created++;
				}
				if ( count( $ids ) > 1 && function_exists( 'pll_save_term_translations' ) ) {
					pll_save_term_translations( $ids );
				}
			}
			self::log( "Terms $tax: $created created." );
		}

		if ( $only_tax ) {
			return;
		}

		// Pages.
		$page_ids = array();
		foreach ( $d['pages'] as $key => $pg ) {
			$ids = array();
			foreach ( $langs as $l ) {
				$dkey = "page:$key:$l";
				$id   = self::find_post( $dkey, 'page' );
				if ( ! $id ) {
					$id = wp_insert_post(
						array(
							'post_type'    => 'page',
							'post_status'  => 'publish',
							'post_title'   => $pg['title'][ $l ],
							'post_name'    => $pg['slug'][ $l ],
							'post_content' => $pg['content'][ $l ],
							'post_author'  => get_current_user_id(),
						),
						true
					);
					if ( is_wp_error( $id ) ) {
						self::log( 'Could not create page ' . $pg['title'][ $l ] . ': ' . $id->get_error_message() );
						continue;
					}
					update_post_meta( $id, '_abc_demo_key', $dkey );
					update_post_meta( $id, '_abc_page_key', $key );
					if ( ! empty( $pg['template'] ) ) {
						update_post_meta( $id, '_wp_page_template', $pg['template'] );
					}
					if ( function_exists( 'pll_set_post_language' ) ) {
						pll_set_post_language( $id, $l );
						wp_update_post( array( 'ID' => $id, 'post_name' => $pg['slug'][ $l ] ) );
					}
					self::log( 'Page created: ' . $pg['title'][ $l ] . ' (' . strtoupper( $l ) . ')' );
				}
				$ids[ $l ] = (int) $id;
			}
			if ( count( $ids ) > 1 && function_exists( 'pll_save_post_translations' ) ) {
				pll_save_post_translations( $ids );
			}
			$page_ids[ $key ] = $ids;
		}

		// Front page (only if the site has no static front page yet).
		if ( ! empty( $page_ids['home'] ) && 'page' !== get_option( 'show_on_front' ) ) {
			$first = reset( $page_ids['home'] );
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_ids['home']['en'] ?? $first );
			self::log( 'Static front page set to “Home”.' );
		}

		// Menus.
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		$poly      = function_exists( 'pll_languages_list' ) ? (array) get_option( 'polylang', array() ) : null;
		foreach ( $d['menus'] as $loc => $items ) {
			foreach ( $langs as $l ) {
				$name = ( 'primary' === $loc ? 'Primary menu' : 'Footer menu' ) . ' (' . strtoupper( $l ) . ')';
				$menu = wp_get_nav_menu_object( $name );
				if ( ! $menu ) {
					$mid = wp_create_nav_menu( $name );
					if ( is_wp_error( $mid ) ) {
						self::log( "Menu $name: " . $mid->get_error_message() );
						continue;
					}
					foreach ( $items as $pos => $it ) {
						$args = array(
							'menu-item-title'    => $it[2][ $l ],
							'menu-item-status'   => 'publish',
							'menu-item-position' => $pos + 1,
						);
						if ( 'page' === $it[0] ) {
							if ( empty( $page_ids[ $it[1] ][ $l ] ) ) {
								continue;
							}
							$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids[ $it[1] ][ $l ] );
						} else {
							$args += array( 'menu-item-type' => 'post_type_archive', 'menu-item-object' => $it[1], 'menu-item-url' => get_post_type_archive_link( $it[1] ) ? get_post_type_archive_link( $it[1] ) : home_url( '/' ) );
						}
						$item_id = wp_update_nav_menu_item( $mid, 0, $args );
						if ( 'page' === $it[0] && $item_id && ! is_wp_error( $item_id ) ) {
							update_post_meta( $item_id, '_abc_page_key', $it[1] );
						}
					}
					self::log( "Menu created: $name" );
					$menu = wp_get_nav_menu_object( $mid );
				}
				if ( $menu ) {
					if ( null !== $poly ) {
						$poly['nav_menus'][ get_stylesheet() ][ $loc ][ $l ] = (int) $menu->term_id;
					}
					if ( 'en' === $l || count( $langs ) === 1 ) {
						$locations[ $loc ] = (int) $menu->term_id;
					}
				}
			}
		}
		set_theme_mod( 'nav_menu_locations', $locations );
		if ( null !== $poly ) {
			update_option( 'polylang', $poly );
		}

		// Site identity & permalinks – only when still at WordPress defaults.
		$name = get_option( 'blogname' );
		if ( '' === $name || in_array( $name, array( 'My WordPress Website', 'WordPress', 'My Blog' ), true ) || false !== stripos( $name, 'wordpress' ) ) {
			update_option( 'blogname', abc_setting( 'name', 'Dr. Ahcene Babori' ) );
		}
		$tag = get_option( 'blogdescription' );
		if ( '' === $tag || false !== stripos( $tag, 'just another wordpress' ) ) {
			update_option( 'blogdescription', 'Assistant Professor | Library & Information Science Researcher' );
		}
		if ( '' === (string) get_option( 'permalink_structure' ) ) {
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			self::log( 'Permalinks set to “Post name”.' );
		}
		update_option( 'abc_needs_flush', 1, false ); // Flushed on the next request, once every post type is registered.
		self::log( 'Structure installed. Review Appearance → Menus and Settings → Reading if you already had your own setup.' );
	}

	/* --------------------------------------------------------------- samples */

	/**
	 * Sample entries.
	 */
	public static function install_samples( $only_type = '' ) {
		$d     = self::data();
		$langs = self::langs();
		if ( ! $only_type && ! self::find_term( 'abc_research_area', 'digital-libraries', $langs[0] ) ) {
			self::log( 'Install the structure first (research areas are missing).' );
			return;
		}
		$ids = array(); // key => lang => id.
		foreach ( $d['posts'] as $p ) {
			$type = $p['type'];
			if ( $only_type && $type !== $only_type ) {
				continue;
			}
			$defs = ABC_Fields::fields_for( $type );
			$byl  = array();
			foreach ( $langs as $l ) {
				$dkey = $type . ':' . $p['key'] . ':' . $l;
				$id   = self::find_post( $dkey, $type );
				if ( ! $id ) {
					$slug = $p['slug'] . ( 'en' === $l ? '' : '-' . $l );
					$id   = wp_insert_post(
						array(
							'post_type'    => $type,
							'post_status'  => 'publish',
							'post_title'   => $p['title'][ $l ],
							'post_name'    => $slug,
							'post_content' => $p['content'][ $l ] ?? '',
							'post_excerpt' => isset( $p['excerpt'] ) ? $p['excerpt'][ $l ] : '',
							'menu_order'   => (int) ( $p['order'] ?? 0 ),
							'post_author'  => get_current_user_id(),
						),
						true
					);
					if ( is_wp_error( $id ) ) {
						self::log( 'Could not create ' . $p['key'] . ': ' . $id->get_error_message() );
						continue;
					}
					update_post_meta( $id, '_abc_demo_key', $dkey );
					if ( empty( $p['real'] ) ) {
						update_post_meta( $id, '_abc_demo', '1' );
					}
					if ( function_exists( 'pll_set_post_language' ) ) {
						pll_set_post_language( $id, $l );
						wp_update_post( array( 'ID' => $id, 'post_name' => $slug ) );
					}
					foreach ( (array) $p['meta'] as $k => $v ) {
						if ( isset( $defs[ $k ] ) ) {
							$val = ABC_Fields::sanitize( self::pick( $v, $l ), $defs[ $k ] );
							if ( '' !== $val && 0 !== $val ) {
								update_post_meta( $id, 'abc_' . $k, $val );
							}
						}
					}
					foreach ( (array) ( $p['terms'] ?? array() ) as $tax => $keys ) {
						$tids = array();
						foreach ( $keys as $tk ) {
							$tid = self::find_term( $tax, $tk, $l );
							if ( $tid ) {
								$tids[] = $tid;
							}
						}
						if ( $tids ) {
							wp_set_object_terms( $id, $tids, $tax );
						}
					}
					self::log( 'Sample created: ' . $p['title'][ $l ] );
				}
				$byl[ $l ] = (int) $id;
			}
			$ids[ $p['key'] ] = $byl;
			if ( count( $byl ) > 1 && function_exists( 'pll_save_post_translations' ) ) {
				pll_save_post_translations( $byl );
			}
		}
		// Relations (second pass).
		foreach ( $d['posts'] as $p ) {
			if ( $only_type && $p['type'] !== $only_type ) {
				continue;
			}
			foreach ( (array) ( $p['rel'] ?? array() ) as $mk => $keys ) {
				foreach ( $langs as $l ) {
					if ( empty( $ids[ $p['key'] ][ $l ] ) ) {
						continue;
					}
					if ( get_post_meta( $ids[ $p['key'] ][ $l ], 'abc_' . $mk, true ) ) {
						continue; // Never overwrite edits.
					}
					$rel = array();
					foreach ( $keys as $rk ) {
						if ( ! empty( $ids[ $rk ][ $l ] ) ) {
							$rel[] = (string) $ids[ $rk ][ $l ];
						}
					}
					if ( $rel ) {
						update_post_meta( $ids[ $p['key'] ][ $l ], 'abc_' . $mk, $rel );
					}
				}
			}
		}
		self::log( 'Sample entries installed. They carry a “Demo” label and the marker [ADD VERIFIED INFORMATION].' );
	}

	/**
	 * Delete sample entries that are still flagged as sample.
	 */
	public static function remove_samples() {
		$ids = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_abc_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		self::log( count( $ids ) . ' sample entries deleted. Pages, menus, categories and research areas were kept.' );
	}

	/* -------------------------------------------------------------- admin UI */

	/**
	 * Form handler.
	 */
	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['abc_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_demo_nonce'] ) ), 'abc_demo' ) ) {
			wp_die( 'Not allowed.', 403 );
		}
		$op = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore
		}
		switch ( $op ) {
			case 'structure':
				self::install_structure();
				break;
			case 'samples':
				self::install_samples();
				break;
			case 'remove':
				self::remove_samples();
				break;
			default:
				wp_die( 'Unknown action.', 400 );
		}
		set_transient( 'abc_demo_report_' . get_current_user_id(), self::$report, 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'admin.php?page=abc-demo&done=1' ) );
		exit;
	}

	/**
	 * Admin page.
	 */
	public static function render_page() {
		echo '<div class="wrap"><h1>Sample content</h1>';
		$report = get_transient( 'abc_demo_report_' . get_current_user_id() );
		if ( $report && isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success"><p><strong>Done.</strong></p><ul style="list-style:disc;padding-inline-start:20px;max-height:240px;overflow:auto">';
			foreach ( $report as $line ) {
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul></div>';
			delete_transient( 'abc_demo_report_' . get_current_user_id() );
		}
		$missing = self::missing_langs();
		if ( ! function_exists( 'pll_languages_list' ) ) {
			echo '<div class="notice notice-warning inline"><p><strong>Polylang is not active.</strong> Content will be created in English only. For the full EN/FR/AR site, install Polylang, add the three languages, then come back here.</p></div>';
		} elseif ( $missing ) {
			echo '<div class="notice notice-warning inline"><p><strong>Missing Polylang languages:</strong> ' . esc_html( strtoupper( implode( ', ', $missing ) ) ) . '. Add them in Languages → Languages, then run the installer again – existing items are not duplicated.</p></div>';
		} else {
			echo '<div class="notice notice-success inline"><p>Polylang is active with EN, FR and AR. Content will be created in all three languages and linked as translations.</p></div>';
		}
		echo '<p class="abc-note">The installer never overwrites or deletes your own content. Run it as often as needed.</p>';
		$forms = array(
			array( 'structure', '1. Install structure', 'Research areas, categories, pages (Home, About, Research, CV, Contact, Discovery), primary and footer menus.', 'primary' ),
			array( 'samples', '2. Install sample entries', 'Placeholder publications, book, four projects, presentations, academic service, teaching entries and two blog posts. Each is marked “Demo”.', 'secondary' ),
			array( 'remove', 'Remove sample entries', 'Deletes only the entries still marked as sample content. Pages, menus and categories stay.', 'delete' ),
		);
		foreach ( $forms as $f ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="background:#fff;border:1px solid #c3c4c7;padding:14px 18px;margin:12px 0;max-width:760px">';
			echo '<input type="hidden" name="action" value="abc_demo"><input type="hidden" name="op" value="' . esc_attr( $f[0] ) . '">';
			wp_nonce_field( 'abc_demo', 'abc_demo_nonce' );
			echo '<h2 style="margin-top:0">' . esc_html( $f[1] ) . '</h2><p>' . esc_html( $f[2] ) . '</p>';
			$onclick = 'remove' === $f[0] ? 'onclick="return confirm(\'Delete all entries still marked as sample content?\')"' : '';
			echo '<button type="submit" class="button button-' . ( 'delete' === $f[3] ? 'secondary' : esc_attr( $f[3] ) ) . '" ' . $onclick . '>' . esc_html( $f[1] ) . '</button></form>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<p>Prefer WordPress’ own importer? Use <em>Tools → Import → WordPress</em> with <code>demo-content.xml</code> (see the installation guide). Both methods only add content.</p></div>';
	}
}
