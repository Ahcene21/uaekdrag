<?php
/**
 * Custom fields: schema, meta boxes, sanitisation, REST registration.
 * No third-party field plugin is required.
 *
 * @package AhceneBaboriCore
 */

defined( 'ABSPATH' ) || exit;

class ABC_Fields {

	/**
	 * Hook.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ), 20 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'demo_notice' ) );
	}

	/**
	 * Field schema. Meta key = "abc_" + key.
	 *
	 * @return array post_type => groups[ title, fields[ key => def ] ]
	 */
	public static function schema() {
		$schema = array(
			'abc_publication'  => array(
				array(
					'title'  => 'Publication details',
					'fields' => array(
						'pub_authors'   => array( 'type' => 'textarea', 'label' => 'Authors', 'help' => 'One author per line, e.g. “Babori, Ahcene” or “Ahcene Babori”. The order is kept.', 'rows' => 4 ),
						'pub_year'      => array( 'type' => 'year', 'label' => 'Year', 'help' => 'Four digits, e.g. 2024. The Year filter is generated from this field automatically.', 'width' => 'third' ),
						'pub_journal'   => array( 'type' => 'text', 'label' => 'Journal / book / conference', 'width' => 'two-thirds' ),
						'pub_publisher' => array( 'type' => 'text', 'label' => 'Publisher', 'width' => 'half' ),
						'pub_volume'    => array( 'type' => 'text', 'label' => 'Volume', 'width' => 'quarter' ),
						'pub_issue'     => array( 'type' => 'text', 'label' => 'Issue', 'width' => 'quarter' ),
						'pub_pages'     => array( 'type' => 'text', 'label' => 'Pages', 'help' => 'e.g. 123–145', 'width' => 'quarter' ),
						'pub_abstract'  => array( 'type' => 'textarea', 'label' => 'Abstract', 'rows' => 7 ),
					),
				),
				array(
					'title'  => 'Identifiers, links and files',
					'fields' => array(
						'pub_doi'      => array( 'type' => 'doi', 'label' => 'DOI', 'help' => 'Only the DOI, e.g. 10.xxxx/xxxxx (a doi.org link is also accepted). Leave empty if unknown – never invent a DOI.', 'width' => 'half' ),
						'pub_url'      => array( 'type' => 'url', 'label' => 'Publisher / landing page URL', 'width' => 'half' ),
						'pub_pdf_id'   => array( 'type' => 'media', 'label' => 'PDF file', 'mime' => 'application/pdf', 'help' => 'Upload only files you are legally allowed to share (e.g. accepted manuscript or open-access version).' ),
						'pub_pdf_url'  => array( 'type' => 'url', 'label' => 'or external PDF URL', 'width' => 'half' ),
						'pub_oa'       => array( 'type' => 'select', 'label' => 'Open Access status', 'options' => array( '' => '— Not specified —', 'open' => 'Open Access (gold)', 'green' => 'Green (repository copy)', 'hybrid' => 'Hybrid', 'closed' => 'Closed / subscription' ), 'width' => 'half' ),
						'pub_repo_url' => array( 'type' => 'url', 'label' => 'Repository URL', 'help' => 'Institutional or subject repository record.', 'width' => 'half' ),
						'pub_featured' => array( 'type' => 'checkbox', 'label' => 'Show on the homepage (featured)' ),
						'pub_related'  => array( 'type' => 'posts', 'label' => 'Related publications (optional)', 'post_types' => array( 'abc_publication' ), 'help' => 'If empty, publications from the same research area are suggested automatically.' ),
					),
				),
			),
			'abc_book'         => array(
				array(
					'title'  => 'Book details',
					'fields' => array(
						'book_author'        => array( 'type' => 'text', 'label' => 'Author(s) / editor(s)', 'width' => 'half' ),
						'book_publisher'     => array( 'type' => 'text', 'label' => 'Publisher', 'width' => 'half' ),
						'book_year'          => array( 'type' => 'year', 'label' => 'Year', 'width' => 'third' ),
						'book_isbn'          => array( 'type' => 'isbn', 'label' => 'ISBN', 'help' => 'Digits, X and hyphens only. Leave empty if not verified.', 'width' => 'third' ),
						'book_publisher_url' => array( 'type' => 'url', 'label' => 'Publisher URL', 'width' => 'half' ),
						'book_purchase_url'  => array( 'type' => 'url', 'label' => 'Purchase URL', 'width' => 'half' ),
						'book_pdf_id'        => array( 'type' => 'media', 'label' => 'PDF (only where legally available)', 'mime' => 'application/pdf' ),
						'book_pdf_url'       => array( 'type' => 'url', 'label' => 'or external PDF URL', 'width' => 'half' ),
					),
				),
			),
			'abc_project'      => array(
				array(
					'title'  => 'Project overview',
					'fields' => array(
						'project_status'  => array( 'type' => 'select', 'label' => 'Status', 'options' => array( '' => '— Not specified —', 'planned' => 'Planned', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'paused' => 'On hold' ), 'width' => 'third' ),
						'project_start'   => array( 'type' => 'date', 'label' => 'Start date', 'width' => 'third' ),
						'project_end'     => array( 'type' => 'date', 'label' => 'End date', 'width' => 'third' ),
						'project_question' => array( 'type' => 'textarea', 'label' => 'Research question', 'rows' => 3 ),
						'project_problem' => array( 'type' => 'textarea', 'label' => 'Problem', 'rows' => 4 ),
						'project_objectives' => array( 'type' => 'textarea', 'label' => 'Objectives', 'help' => 'One objective per line.', 'rows' => 4 ),
						'project_method'  => array( 'type' => 'textarea', 'label' => 'Methodology', 'rows' => 4 ),
						'project_tech'    => array( 'type' => 'text', 'label' => 'Technologies', 'help' => 'Separate with commas, e.g. OAI-PMH, DSpace, Python.' ),
						'project_partners' => array( 'type' => 'textarea', 'label' => 'Partners', 'help' => 'One partner per line.', 'rows' => 3 ),
						'project_outputs' => array( 'type' => 'textarea', 'label' => 'Outputs', 'help' => 'One output per line (software, dataset, report…).', 'rows' => 3 ),
						'project_url'     => array( 'type' => 'url', 'label' => 'External URL' ),
						'project_featured' => array( 'type' => 'checkbox', 'label' => 'Show on the homepage (featured)' ),
					),
				),
				array(
					'title'  => 'Connections',
					'fields' => array(
						'project_pubs' => array( 'type' => 'posts', 'label' => 'Related publications', 'post_types' => array( 'abc_publication' ) ),
						'project_pres' => array( 'type' => 'posts', 'label' => 'Related presentations', 'post_types' => array( 'abc_presentation' ) ),
					),
				),
			),
			'abc_presentation' => array(
				array(
					'title'  => 'Presentation details',
					'fields' => array(
						'pres_conference' => array( 'type' => 'text', 'label' => 'Conference / event', 'help' => 'Choose or add the Presentation Type in the “Presentation Types” box on the right.', 'width' => 'half' ),
						'pres_date'       => array( 'type' => 'date', 'label' => 'Date', 'width' => 'third' ),
						'pres_location'   => array( 'type' => 'text', 'label' => 'Location' ),
						'pres_abstract'   => array( 'type' => 'textarea', 'label' => 'Abstract', 'rows' => 5 ),
						'pres_slides_id'  => array( 'type' => 'media', 'label' => 'Slides (PDF or PPTX)', 'mime' => 'application/pdf,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-powerpoint' ),
						'pres_slides_url' => array( 'type' => 'url', 'label' => 'or external slides URL', 'width' => 'half' ),
						'pres_video'      => array( 'type' => 'url', 'label' => 'Video link', 'width' => 'half' ),
						'pres_event_url'  => array( 'type' => 'url', 'label' => 'Event URL' ),
						'pres_projects'   => array( 'type' => 'posts', 'label' => 'Related projects', 'post_types' => array( 'abc_project' ) ),
					),
				),
			),
			'abc_service'      => array(
				array(
					'title'  => 'Service details',
					'fields' => array(
						'svc_org'         => array( 'type' => 'text', 'label' => 'Organization / journal / committee' ),
						'svc_position'    => array( 'type' => 'text', 'label' => 'Position', 'width' => 'half' ),
						'svc_from'        => array( 'type' => 'period', 'label' => 'From', 'help' => 'Year (2021) or year-month (2021-09).', 'width' => 'quarter' ),
						'svc_to'          => array( 'type' => 'period', 'label' => 'To', 'width' => 'quarter' ),
						'svc_ongoing'     => array( 'type' => 'checkbox', 'label' => 'Ongoing (shows “Present”)' ),
						'svc_description' => array( 'type' => 'textarea', 'label' => 'Description', 'rows' => 4 ),
						'svc_url'         => array( 'type' => 'url', 'label' => 'URL' ),
					),
				),
			),
			'abc_teaching'     => array(
				array(
					'title'  => 'Teaching details',
					'fields' => array(
						'teach_institution' => array( 'type' => 'text', 'label' => 'Institution', 'width' => 'half' ),
						'teach_level'       => array( 'type' => 'text', 'label' => 'Level', 'help' => 'e.g. Bachelor, Master, Doctorate.', 'width' => 'quarter' ),
						'teach_period'      => array( 'type' => 'text', 'label' => 'Period', 'width' => 'quarter' ),
						'teach_description' => array( 'type' => 'textarea', 'label' => 'Short description', 'rows' => 4 ),
						'teach_materials_id'  => array( 'type' => 'media', 'label' => 'Teaching material (file)', 'mime' => '' ),
						'teach_materials_url' => array( 'type' => 'url', 'label' => 'or material URL' ),
					),
				),
			),
			'abc_career'       => array(
				array(
					'title'  => 'Timeline entry',
					'fields' => array(
						'career_start'  => array( 'type' => 'year', 'label' => 'Start year', 'help' => 'Used to sort entries, newest first (e.g. 2021).', 'width' => 'third' ),
						'career_period' => array( 'type' => 'text', 'label' => 'Period shown', 'help' => 'Free text: 2021 – Present, 2002–2004, Spring 2020…', 'width' => 'third' ),
						'career_badge'  => array( 'type' => 'text', 'label' => 'Badge (optional)', 'help' => 'Small label next to the title: Current, New, Paris 8…', 'width' => 'third' ),
						'career_org'    => array( 'type' => 'text', 'label' => 'Institution / place / date detail' ),
						'career_detail' => array( 'type' => 'textarea', 'label' => 'Details', 'help' => 'Supervisor, modules, thesis title… Text between “curly quotes” is shown in italics.', 'rows' => 4 ),
					),
				),
			),
			'abc_note'         => array(
				array(
					'title'  => 'Connections',
					'fields' => array(
						'note_pubs'     => array( 'type' => 'posts', 'label' => 'Related publications', 'post_types' => array( 'abc_publication' ) ),
						'note_projects' => array( 'type' => 'posts', 'label' => 'Related projects', 'post_types' => array( 'abc_project' ) ),
					),
				),
			),
		);
		return apply_filters( 'abc_field_schema', $schema );
	}

	/**
	 * Flat map key => def for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return array
	 */
	public static function fields_for( $post_type ) {
		$out    = array();
		$schema = self::schema();
		foreach ( $schema[ $post_type ] ?? array() as $group ) {
			foreach ( $group['fields'] as $k => $def ) {
				$out[ $k ] = $def;
			}
		}
		return $out;
	}

	/**
	 * Register meta for REST.
	 */
	public static function register_meta() {
		foreach ( array_keys( self::schema() ) as $pt ) {
			foreach ( self::fields_for( $pt ) as $key => $def ) {
				$args = array(
					'object_subtype'    => $pt,
					'single'            => true,
					'show_in_rest'      => true,
					'type'              => 'string',
					'sanitize_callback' => static function ( $v ) use ( $def ) {
						return ABC_Fields::sanitize( $v, $def );
					},
					'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				);
				if ( 'posts' === $def['type'] ) {
					$args['type']         = 'array';
					$args['show_in_rest'] = array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ) );
				} elseif ( 'media' === $def['type'] ) {
					$args['type'] = 'integer';
				}
				register_post_meta( $pt, 'abc_' . $key, $args );
			}
		}
	}

	/**
	 * Sanitise a value by field definition.
	 *
	 * @param mixed $v   Raw value.
	 * @param array $def Field definition.
	 * @return mixed
	 */
	public static function sanitize( $v, $def ) {
		switch ( $def['type'] ) {
			case 'textarea':
				return sanitize_textarea_field( (string) $v );
			case 'url':
				$v = trim( (string) $v );
				return ( '' !== $v && preg_match( '#^https?://#i', $v ) ) ? esc_url_raw( $v ) : '';
			case 'year':
				$v = trim( (string) $v );
				return preg_match( '/^\d{4}$/', $v ) ? $v : '';
			case 'period':
				$v = trim( (string) $v );
				return preg_match( '/^\d{4}(-\d{2})?$/', $v ) ? $v : '';
			case 'date':
				$v = trim( (string) $v );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
			case 'doi':
				return abc_doi_clean( sanitize_text_field( (string) $v ) );
			case 'isbn':
				return preg_replace( '/[^0-9Xx\-]/', '', (string) $v );
			case 'checkbox':
				return $v ? '1' : '';
			case 'media':
				return absint( $v );
			case 'select':
				$v = sanitize_key( (string) $v );
				return array_key_exists( $v, $def['options'] ) ? $v : '';
			case 'posts':
				return array_values( array_unique( array_map( 'strval', array_filter( array_map( 'absint', (array) $v ) ) ) ) );
			default:
				return sanitize_text_field( (string) $v );
		}
	}

	/**
	 * Add meta boxes.
	 */
	public static function add_boxes() {
		foreach ( self::schema() as $pt => $groups ) {
			foreach ( $groups as $i => $g ) {
				add_meta_box( "abc_box_{$pt}_{$i}", $g['title'], array( __CLASS__, 'render_box' ), $pt, 'normal', 'high', array( 'group' => $i ) );
			}
		}
		foreach ( array_merge( array_keys( ABC_Post_Types::definitions() ), array( 'abc_career' ) ) as $pt ) {
			add_meta_box( 'abc_box_demo', 'Sample content label', array( __CLASS__, 'render_demo_box' ), $pt, 'side', 'low' );
		}
	}

	/**
	 * Side box with demo flag.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_demo_box( $post ) {
		echo '<label><input type="checkbox" name="abc_is_demo" value="1" ' . checked( (bool) get_post_meta( $post->ID, '_abc_demo', true ), true, false ) . '> This entry is sample content</label>';
		echo '<p class="description">Sample entries show a small “Demo” label on the website. Untick this box once you have replaced the text with verified information.</p>';
	}

	/**
	 * Render a group of fields.
	 *
	 * @param WP_Post $post Post.
	 * @param array   $box  Box args.
	 */
	public static function render_box( $post, $box ) {
		$schema = self::schema();
		$group  = $schema[ $post->post_type ][ $box['args']['group'] ];
		wp_nonce_field( 'abc_save_' . $post->post_type, 'abc_nonce' );
		echo '<div class="abc-fields">';
		foreach ( $group['fields'] as $key => $def ) {
			$val   = get_post_meta( $post->ID, 'abc_' . $key, true );
			$id    = 'abc_f_' . $key;
			$name  = 'abc_f[' . $key . ']';
			$width = $def['width'] ?? 'full';
			echo '<div class="abc-field abc-field--' . esc_attr( $width ) . ' abc-type-' . esc_attr( $def['type'] ) . '">';
			if ( 'checkbox' === $def['type'] ) {
				echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( (bool) $val, true, false ) . '> ' . esc_html( $def['label'] ) . '</label>';
			} else {
				echo '<label class="abc-label" for="' . esc_attr( $id ) . '">' . esc_html( $def['label'] ) . '</label>';
				self::render_input( $key, $def, $val, $id, $name );
			}
			if ( ! empty( $def['help'] ) ) {
				echo '<p class="description">' . esc_html( $def['help'] ) . '</p>';
			}
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Render one input.
	 *
	 * @param string $key  Key.
	 * @param array  $def  Definition.
	 * @param mixed  $val  Value.
	 * @param string $id   DOM id.
	 * @param string $name Input name.
	 */
	private static function render_input( $key, $def, $val, $id, $name ) {
		switch ( $def['type'] ) {
			case 'textarea':
				printf( '<textarea class="widefat" id="%s" name="%s" rows="%d">%s</textarea>', esc_attr( $id ), esc_attr( $name ), (int) ( $def['rows'] ?? 4 ), esc_textarea( (string) $val ) );
				break;
			case 'select':
				echo '<select class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $def['options'] as $ov => $ol ) {
					echo '<option value="' . esc_attr( $ov ) . '"' . selected( (string) $val, (string) $ov, false ) . '>' . esc_html( $ol ) . '</option>';
				}
				echo '</select>';
				break;
			case 'date':
				printf( '<input type="date" class="widefat" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
				break;
			case 'url':
				printf( '<input type="url" class="widefat" id="%s" name="%s" value="%s" placeholder="https://">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
				break;
			case 'year':
				printf( '<input type="text" inputmode="numeric" maxlength="4" pattern="\d{4}" class="widefat" id="%s" name="%s" value="%s" placeholder="YYYY">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
				break;
			case 'period':
				printf( '<input type="text" maxlength="7" class="widefat" id="%s" name="%s" value="%s" placeholder="YYYY or YYYY-MM">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
				break;
			case 'media':
				$file = $val ? wp_basename( (string) get_attached_file( (int) $val ) ) : '';
				echo '<div class="abc-media" data-mime="' . esc_attr( $def['mime'] ?? '' ) . '">';
				printf( '<input type="hidden" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
				echo '<span class="abc-media__name">' . esc_html( $file ? $file : 'No file selected' ) . '</span> ';
				echo '<button type="button" class="button abc-media__pick">Choose / upload file</button> ';
				echo '<button type="button" class="button-link abc-media__clear"' . ( $val ? '' : ' hidden' ) . '>Remove</button>';
				echo '</div>';
				break;
			case 'posts':
				self::render_posts_checklist( $def, (array) $val, $name, $id );
				break;
			default:
				printf( '<input type="text" class="widefat" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
		}
	}

	/**
	 * Checklist of posts for relation fields.
	 *
	 * @param array  $def      Definition.
	 * @param array  $selected Selected IDs.
	 * @param string $name     Field name.
	 * @param string $id       DOM id.
	 */
	private static function render_posts_checklist( $def, $selected, $name, $id ) {
		$posts = get_posts(
			array(
				'post_type'      => $def['post_types'],
				'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'lang'           => '',
				'suppress_filters' => false,
			)
		);
		$selected = array_map( 'strval', $selected );
		echo '<div class="abc-posts" id="' . esc_attr( $id ) . '">';
		echo '<input type="search" class="abc-posts__filter widefat" placeholder="Type to filter…" aria-label="Filter list">';
		echo '<div class="abc-posts__list">';
		if ( ! $posts ) {
			echo '<p class="description">Nothing to link yet. Add entries first, then come back.</p>';
		}
		foreach ( $posts as $p ) {
			$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $p->ID ) : '';
			printf(
				'<label><input type="checkbox" name="%s[]" value="%d" %s> %s%s</label>',
				esc_attr( $name ),
				(int) $p->ID,
				checked( in_array( (string) $p->ID, $selected, true ), true, false ),
				esc_html( get_the_title( $p ) ),
				$lang ? ' <span class="abc-posts__lang">' . esc_html( strtoupper( $lang ) ) . '</span>' : ''
			);
		}
		echo '</div></div>';
	}

	/**
	 * Save meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function save( $post_id, $post ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['abc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abc_nonce'] ) ), 'abc_save_' . $post->post_type ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$raw = isset( $_POST['abc_f'] ) && is_array( $_POST['abc_f'] ) ? wp_unslash( $_POST['abc_f'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field below.
		foreach ( self::fields_for( $post->post_type ) as $key => $def ) {
			$value = $raw[ $key ] ?? ( 'posts' === $def['type'] ? array() : '' );
			$value = self::sanitize( $value, $def );
			if ( '' === $value || array() === $value || 0 === $value ) {
				delete_post_meta( $post_id, 'abc_' . $key );
			} else {
				update_post_meta( $post_id, 'abc_' . $key, $value );
			}
		}
		// Sample content flag.
		if ( ! empty( $_POST['abc_is_demo'] ) ) {
			update_post_meta( $post_id, '_abc_demo', '1' );
		} else {
			delete_post_meta( $post_id, '_abc_demo' );
		}
	}

	/**
	 * Admin assets for post edit screens and settings.
	 *
	 * @param string $hook Hook suffix.
	 */
	public static function assets( $hook ) {
		$screen = get_current_screen();
		$ours   = $screen && ( strpos( (string) $screen->post_type, 'abc_' ) === 0 || strpos( (string) $screen->id, 'abc-researcher' ) !== false || strpos( (string) $screen->id, 'abc_' ) !== false || strpos( (string) $screen->id, 'researcher_page_' ) === 0 );
		if ( ! $ours ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'abc-admin', ABC_URL . 'assets/admin.css', array(), ABC_VERSION );
		wp_enqueue_script( 'abc-admin', ABC_URL . 'assets/admin.js', array( 'jquery' ), ABC_VERSION, true );
	}

	/**
	 * Reminder on sample entries.
	 */
	public static function demo_notice() {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || strpos( (string) $screen->post_type, 'abc_' ) !== 0 ) {
			return;
		}
		global $post;
		if ( $post && get_post_meta( $post->ID, '_abc_demo', true ) ) {
			echo '<div class="notice notice-warning"><p><strong>Sample content.</strong> This entry was installed with the demo data. Replace the text and fields marked <code>[ADD VERIFIED INFORMATION]</code> with verified information, then untick “This entry is sample content” in the sidebar.</p></div>';
		}
	}
}
