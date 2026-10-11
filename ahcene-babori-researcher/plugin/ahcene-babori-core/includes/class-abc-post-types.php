<?php
/**
 * Custom post types and taxonomies.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Post_Types {

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );
		add_action( 'init', array( __CLASS__, 'maybe_flush' ), 99 );
		add_action( 'added_post_meta', array( __CLASS__, 'sync_year_term' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'sync_year_term' ), 10, 4 );
		add_action( 'abc_research_area_add_form_fields', array( __CLASS__, 'area_add_fields' ) );
		add_action( 'abc_research_area_edit_form_fields', array( __CLASS__, 'area_edit_fields' ) );
		add_action( 'created_abc_research_area', array( __CLASS__, 'save_area_fields' ) );
		add_action( 'edited_abc_research_area', array( __CLASS__, 'save_area_fields' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
		add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );
		self::admin_columns();
	}

	/**
	 * Deferred rewrite flush (set by the sample-content installer).
	 */
	public static function maybe_flush() {
		if ( get_option( 'abc_needs_flush' ) ) {
			delete_option( 'abc_needs_flush' );
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Post type definitions.
	 *
	 * Admin labels are English by design (the dashboard language follows WordPress);
	 * public strings go through abc_t().
	 *
	 * @return array
	 */
	public static function definitions() {
		return array(
			'abc_publication'  => array( 'Publication', 'Publications', 'publications', 'dashicons-media-text', 26, 'Add publication' ),
			'abc_book'         => array( 'Book', 'Books', 'books', 'dashicons-book-alt', 27, 'Add book' ),
			'abc_project'      => array( 'Research Project', 'Research Projects', 'projects', 'dashicons-lightbulb', 28, 'Add project' ),
			'abc_presentation' => array( 'Presentation', 'Presentations', 'presentations', 'dashicons-megaphone', 29, 'Add presentation' ),
			'abc_service'      => array( 'Academic Service', 'Academic Service', 'academic-service', 'dashicons-groups', 30, 'Add academic service' ),
			'abc_teaching'     => array( 'Teaching Entry', 'Teaching', 'teaching', 'dashicons-welcome-learn-more', 31, 'Add course / teaching entry' ),
			'abc_resource'     => array( 'Research Resource', 'Research Resources', 'resources', 'dashicons-portfolio', 32, 'Add resource' ),
			'abc_note'         => array( 'Research Note', 'Research Notes', 'research-notes', 'dashicons-edit-page', 33, 'Add research note' ),
		);
	}

	/**
	 * Register CPTs and taxonomies.
	 */
	public static function register() {
		foreach ( self::definitions() as $slug => $d ) {
			list( $singular, $plural, $rewrite, $icon, $position, $add ) = $d;
			register_post_type(
				$slug,
				array(
					'labels'              => array(
						'name'               => $plural,
						'singular_name'      => $singular,
						'add_new'            => $add,
						'add_new_item'       => $add,
						'edit_item'          => 'Edit ' . strtolower( $singular ),
						'new_item'           => 'New ' . strtolower( $singular ),
						'view_item'          => 'View ' . strtolower( $singular ),
						'search_items'       => 'Search ' . strtolower( $plural ),
						'not_found'          => 'Nothing found yet. Use “' . $add . '”.',
						'not_found_in_trash' => 'Nothing in the trash.',
						'all_items'          => 'All ' . strtolower( $plural ),
						'menu_name'          => $plural,
					),
					'public'              => true,
					'publicly_queryable'  => true,
					'show_ui'             => true,
					'show_in_menu'        => true,
					'show_in_rest'        => true,
					'rest_base'           => str_replace( 'abc_', '', $slug ) . 's',
					'has_archive'         => $rewrite,
					'rewrite'             => array( 'slug' => $rewrite, 'with_front' => false ),
					'menu_icon'           => $icon,
					'menu_position'       => $position,
					'exclude_from_search' => false,
					'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'author' ),
					'taxonomies'          => array(),
				)
			);
		}

		// Contact messages (private, not public).
		register_post_type(
			'abc_message',
			array(
				'labels'              => array(
					'name'          => 'Contact messages',
					'singular_name' => 'Contact message',
					'menu_name'     => 'Contact messages',
					'all_items'     => 'Contact messages',
					'not_found'     => 'No messages yet.',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'abc-researcher',
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'supports'            => array( 'title', 'editor' ),
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);

		$tax = array(
			// slug => [ singular, plural, objects, hierarchical, rewrite, show_ui ].
			'abc_pub_type'        => array( 'Publication Type', 'Publication Types', array( 'abc_publication' ), true, 'publication-type', true ),
			'abc_research_area'   => array( 'Research Area', 'Research Areas', array( 'abc_publication', 'abc_book', 'abc_project', 'abc_presentation', 'abc_note', 'abc_resource' ), true, 'research-area', true ),
			'abc_pub_year'        => array( 'Year', 'Years', array( 'abc_publication' ), false, 'publication-year', false ),
			'abc_keyword'         => array( 'Keyword', 'Keywords', array( 'abc_publication', 'abc_project', 'abc_book', 'abc_presentation' ), false, 'keyword', true ),
			'abc_pres_type'       => array( 'Presentation Type', 'Presentation Types', array( 'abc_presentation' ), true, 'presentation-type', true ),
			'abc_service_type'    => array( 'Service Category', 'Service Categories', array( 'abc_service' ), true, 'service-category', true ),
			'abc_teaching_section' => array( 'Teaching Section', 'Teaching Sections', array( 'abc_teaching' ), true, 'teaching-section', true ),
			'abc_resource_type'   => array( 'Resource Type', 'Resource Types', array( 'abc_resource' ), true, 'resource-type', true ),
			'abc_note_category'   => array( 'Note Category', 'Note Categories', array( 'abc_note' ), true, 'note-category', true ),
			'abc_note_tag'        => array( 'Note Tag', 'Note Tags', array( 'abc_note' ), false, 'note-tag', true ),
		);
		foreach ( $tax as $slug => $t ) {
			list( $singular, $plural, $objects, $hier, $rewrite, $ui ) = $t;
			register_taxonomy(
				$slug,
				$objects,
				array(
					'labels'            => array(
						'name'          => $plural,
						'singular_name' => $singular,
						'add_new_item'  => 'Add ' . strtolower( $singular ),
						'edit_item'     => 'Edit ' . strtolower( $singular ),
						'search_items'  => 'Search ' . strtolower( $plural ),
						'all_items'     => 'All ' . strtolower( $plural ),
						'menu_name'     => $plural,
					),
					'hierarchical'      => $hier,
					'public'            => true,
					'show_ui'           => $ui,
					'show_in_menu'      => $ui,
					'show_in_rest'      => true,
					'show_admin_column' => $ui,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => $rewrite, 'with_front' => false ),
				)
			);
		}
	}

	/**
	 * Keep the "Year" taxonomy in sync with the year field (works for editor and REST).
	 *
	 * @param int    $meta_id    Meta ID.
	 * @param int    $object_id  Post ID.
	 * @param string $meta_key   Key.
	 * @param mixed  $meta_value Value.
	 */
	public static function sync_year_term( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( 'abc_pub_year' !== $meta_key || 'abc_publication' !== get_post_type( $object_id ) ) {
			return;
		}
		$year = preg_match( '/^\d{4}$/', (string) $meta_value ) ? (string) $meta_value : '';
		wp_set_object_terms( $object_id, $year ? array( $year ) : array(), 'abc_pub_year' );
	}

	/** Research-area icon choices. */
	public static function area_icons() {
		return array( 'library', 'repository', 'openaccess', 'communication', 'metadata', 'ai', 'data', 'transform' );
	}

	/**
	 * "Add term" form fields.
	 */
	public static function area_add_fields() {
		wp_nonce_field( 'abc_area_icon', 'abc_area_nonce' );
		echo '<div class="form-field"><label for="abc_icon">Icon</label><select name="abc_icon" id="abc_icon">';
		foreach ( self::area_icons() as $i ) {
			echo '<option value="' . esc_attr( $i ) . '">' . esc_html( ucfirst( $i ) ) . '</option>';
		}
		echo '</select><p>Icon shown on the research-area card.</p></div>';
		echo '<div class="form-field"><label for="abc_order">Display order</label><input type="number" name="abc_order" id="abc_order" value="50" min="0" step="1"><p>Lower numbers appear first on the homepage.</p></div>';
	}

	/**
	 * "Edit term" form fields.
	 *
	 * @param WP_Term $term Term.
	 */
	public static function area_edit_fields( $term ) {
		wp_nonce_field( 'abc_area_icon', 'abc_area_nonce' );
		$cur = get_term_meta( $term->term_id, 'abc_icon', true );
		echo '<tr class="form-field"><th scope="row"><label for="abc_icon">Icon</label></th><td><select name="abc_icon" id="abc_icon">';
		foreach ( self::area_icons() as $i ) {
			echo '<option value="' . esc_attr( $i ) . '"' . selected( $cur, $i, false ) . '>' . esc_html( ucfirst( $i ) ) . '</option>';
		}
		echo '</select><p class="description">Icon shown on the research-area card. The description above is the card text.</p></td></tr>';
		echo '<tr class="form-field"><th scope="row"><label for="abc_order">Display order</label></th><td><input type="number" name="abc_order" id="abc_order" value="' . esc_attr( (string) get_term_meta( $term->term_id, 'abc_order', true ) ) . '" min="0" step="1"><p class="description">Lower numbers appear first on the homepage.</p></td></tr>';
	}

	/**
	 * Save term icon.
	 *
	 * @param int $term_id Term.
	 */
	public static function save_area_fields( $term_id ) {
		if ( ! isset( $_POST['abc_area_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_area_nonce'] ) ), 'abc_area_icon' ) || ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		$icon = isset( $_POST['abc_icon'] ) ? sanitize_key( wp_unslash( $_POST['abc_icon'] ) ) : '';
		if ( in_array( $icon, self::area_icons(), true ) ) {
			update_term_meta( $term_id, 'abc_icon', $icon );
		}
		if ( isset( $_POST['abc_order'] ) && '' !== $_POST['abc_order'] ) {
			update_term_meta( $term_id, 'abc_order', absint( $_POST['abc_order'] ) );
		}
	}

	/**
	 * Friendlier "Add title" placeholder.
	 *
	 * @param string  $text Text.
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function title_placeholder( $text, $post ) {
		$map = array(
			'abc_publication'  => 'Title of the publication',
			'abc_book'         => 'Book title',
			'abc_project'      => 'Project title',
			'abc_presentation' => 'Presentation title',
			'abc_service'      => 'Short label, e.g. “Peer reviewer – Journal name”',
			'abc_teaching'     => 'Course or teaching entry title',
			'abc_resource'     => 'Resource title',
			'abc_note'         => 'Title of the research note',
		);
		return $map[ $post->post_type ] ?? $text;
	}

	/**
	 * Clearer notices.
	 *
	 * @param array $messages Messages.
	 * @return array
	 */
	public static function updated_messages( $messages ) {
		foreach ( array_keys( self::definitions() ) as $pt ) {
			$messages[ $pt ] = $messages['post'];
		}
		return $messages;
	}

	/**
	 * Admin list columns.
	 */
	public static function admin_columns() {
		$cols = array(
			'abc_publication'  => array( 'pub_year' => 'Year', 'pub_journal' => 'Journal / Venue', 'pub_doi' => 'DOI' ),
			'abc_book'         => array( 'book_year' => 'Year', 'book_publisher' => 'Publisher' ),
			'abc_project'      => array( 'project_status' => 'Status' ),
			'abc_presentation' => array( 'pres_date' => 'Date', 'pres_conference' => 'Conference' ),
			'abc_service'      => array( 'svc_org' => 'Organization', 'svc_from' => 'From' ),
			'abc_teaching'     => array( 'teach_institution' => 'Institution' ),
			'abc_resource'     => array( 'res_file_id' => 'File' ),
		);
		foreach ( $cols as $pt => $fields ) {
			add_filter(
				"manage_{$pt}_posts_columns",
				static function ( $c ) use ( $fields ) {
					$new = array();
					foreach ( $c as $k => $v ) {
						$new[ $k ] = $v;
						if ( 'title' === $k ) {
							foreach ( $fields as $fk => $label ) {
								$new[ 'abc_' . $fk ] = $label;
							}
						}
					}
					return $new;
				}
			);
			add_action(
				"manage_{$pt}_posts_custom_column",
				static function ( $col, $post_id ) use ( $fields ) {
					foreach ( $fields as $fk => $label ) {
						if ( 'abc_' . $fk === $col ) {
							$v = get_post_meta( $post_id, 'abc_' . $fk, true );
							if ( 'res_file_id' === $fk ) {
								$v = $v ? wp_basename( (string) get_attached_file( (int) $v ) ) : '—';
							}
							echo esc_html( $v ? $v : '—' );
						}
					}
				},
				10,
				2
			);
		}
	}
}
