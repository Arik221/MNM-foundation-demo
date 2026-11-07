<?php
/**
 * Lightweight custom-field framework (no third-party plugin required).
 *
 * Fields are declared once in mnm_field_defs(), rendered in meta boxes, sanitised on save
 * and reused by the settings screens through mnm_render_field() / mnm_sanitize_field().
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions grouped by post type.
 *
 * @return array
 */
function mnm_field_defs() {
	$demo = array(
		'label' => __( 'Sample / demonstration content', 'mnm-foundation-core' ),
		'type'  => 'checkbox',
		'text'  => __( 'This entry contains sample content. Tick to show a "Demonstration data" label on the website.', 'mnm-foundation-core' ),
	);

	$defs = array(
		'mnm_project'   => array(
			'_mnm_status'        => array(
				'label'   => __( 'Status', 'mnm-foundation-core' ),
				'type'    => 'select',
				'options' => array(
					'ongoing'   => __( 'Ongoing', 'mnm-foundation-core' ),
					'completed' => __( 'Completed', 'mnm-foundation-core' ),
					'planned'   => __( 'Planned', 'mnm-foundation-core' ),
				),
			),
			'_mnm_period'        => array(
				'label'       => __( 'Period', 'mnm-foundation-core' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. 2026 or Jan - Jun 2026', 'mnm-foundation-core' ),
			),
			'_mnm_programme'     => array(
				'label'     => __( 'Programme', 'mnm-foundation-core' ),
				'type'      => 'post_select',
				'post_type' => 'mnm_programme',
			),
			'_mnm_beneficiaries' => array(
				'label' => __( 'People or children supported', 'mnm-foundation-core' ),
				'type'  => 'number',
				'desc'  => __( 'Only enter a number you can verify. Leave empty if unknown.', 'mnm-foundation-core' ),
			),
			'_mnm_outcomes'      => array(
				'label' => __( 'Outcomes and impact', 'mnm-foundation-core' ),
				'type'  => 'textarea',
				'desc'  => __( 'One outcome per line. Shown as a list on the project page.', 'mnm-foundation-core' ),
			),
			'_mnm_gallery'       => array(
				'label' => __( 'Project photographs', 'mnm-foundation-core' ),
				'type'  => 'gallery',
			),
			'_mnm_demo'          => $demo,
		),
		'mnm_programme' => array(
			'_mnm_tagline'  => array(
				'label' => __( 'Short tagline', 'mnm-foundation-core' ),
				'type'  => 'text',
			),
			'_mnm_icon'     => array(
				'label'   => __( 'Icon', 'mnm-foundation-core' ),
				'type'    => 'select',
				'options' => array(
					'book'   => __( 'Book (education)', 'mnm-foundation-core' ),
					'laptop' => __( 'Laptop (digital literacy)', 'mnm-foundation-core' ),
					'people' => __( 'People (community)', 'mnm-foundation-core' ),
					'heart'  => __( 'Heart', 'mnm-foundation-core' ),
				),
			),
			'_mnm_accent'   => array(
				'label'   => __( 'Accent colour', 'mnm-foundation-core' ),
				'type'    => 'select',
				'options' => array(
					'blue'   => __( 'Blue', 'mnm-foundation-core' ),
					'green'  => __( 'Green', 'mnm-foundation-core' ),
					'yellow' => __( 'Yellow', 'mnm-foundation-core' ),
				),
			),
			'_mnm_audience' => array(
				'label' => __( 'Who it serves', 'mnm-foundation-core' ),
				'type'  => 'text',
			),
			'_mnm_focus'    => array(
				'label' => __( 'Key activities', 'mnm-foundation-core' ),
				'type'  => 'textarea',
				'desc'  => __( 'One per line. Shown as a bullet list.', 'mnm-foundation-core' ),
			),
		),
		'mnm_event'     => array(
			'_mnm_start'              => array(
				'label' => __( 'Starts', 'mnm-foundation-core' ),
				'type'  => 'datetime',
			),
			'_mnm_end'                => array(
				'label' => __( 'Ends', 'mnm-foundation-core' ),
				'type'  => 'datetime',
			),
			'_mnm_venue'              => array(
				'label' => __( 'Venue', 'mnm-foundation-core' ),
				'type'  => 'text',
			),
			'_mnm_registration_url'   => array(
				'label' => __( 'Registration link', 'mnm-foundation-core' ),
				'type'  => 'url',
				'desc'  => __( 'Optional. Leave empty if registration is not required.', 'mnm-foundation-core' ),
			),
			'_mnm_registration_label' => array(
				'label'       => __( 'Registration button text', 'mnm-foundation-core' ),
				'type'        => 'text',
				'placeholder' => __( 'Register now', 'mnm-foundation-core' ),
			),
			'_mnm_gallery'            => array(
				'label' => __( 'Event gallery', 'mnm-foundation-core' ),
				'type'  => 'gallery',
			),
			'_mnm_demo'               => $demo,
		),
		'mnm_article'   => array(
			'_mnm_featured' => array(
				'label' => __( 'Featured article', 'mnm-foundation-core' ),
				'type'  => 'checkbox',
				'text'  => __( 'Show on the homepage and at the top of the news page.', 'mnm-foundation-core' ),
			),
			'_mnm_demo'     => $demo,
		),
		'mnm_update'    => array(
			'_mnm_demo' => $demo,
		),
		'mnm_campaign'  => array(
			'_mnm_target'        => array(
				'label' => __( 'Fundraising target', 'mnm-foundation-core' ),
				'type'  => 'number',
				'step'  => '1',
			),
			'_mnm_raised'        => array(
				'label' => __( 'Amount raised (manually verified)', 'mnm-foundation-core' ),
				'type'  => 'number',
				'step'  => '1',
				'desc'  => __( 'Enter only amounts your team has verified (for example against bank records). This is NOT an automatic donation counter.', 'mnm-foundation-core' ),
			),
			'_mnm_currency'      => array(
				'label'       => __( 'Currency symbol', 'mnm-foundation-core' ),
				'type'        => 'text',
				'placeholder' => '₹',
			),
			'_mnm_verified_on'   => array(
				'label' => __( 'Amount last verified on', 'mnm-foundation-core' ),
				'type'  => 'date',
			),
			'_mnm_verified_note' => array(
				'label'       => __( 'Verification note', 'mnm-foundation-core' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Checked against bank statement by treasurer', 'mnm-foundation-core' ),
				'desc'        => __( 'Shown publicly next to the amount.', 'mnm-foundation-core' ),
			),
			'_mnm_end_date'      => array(
				'label' => __( 'Campaign ends', 'mnm-foundation-core' ),
				'type'  => 'date',
			),
			'_mnm_donate_url'    => array(
				'label' => __( 'Donation link', 'mnm-foundation-core' ),
				'type'  => 'url',
				'desc'  => __( 'Optional. Where the "Donate" button leads. Online payments are not built in yet.', 'mnm-foundation-core' ),
			),
			'_mnm_demo'          => array(
				'label' => __( 'Sample / demonstration figures', 'mnm-foundation-core' ),
				'type'  => 'checkbox',
				'text'  => __( 'The figures above are sample data. A "Demonstration data" label is shown publicly.', 'mnm-foundation-core' ),
			),
		),
	);

	/**
	 * Filter field definitions.
	 *
	 * @param array $defs Definitions keyed by post type.
	 */
	return apply_filters( 'mnm_field_defs', $defs );
}

/**
 * Register meta so it is sanitised and available to the REST API / block editor.
 */
function mnm_register_meta() {
	foreach ( mnm_field_defs() as $post_type => $fields ) {
		foreach ( $fields as $key => $def ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'single'            => true,
					'type'              => in_array( $def['type'], array( 'number' ), true ) ? 'number' : 'string',
					'show_in_rest'      => false,
					'sanitize_callback' => function ( $value ) use ( $def ) {
						return mnm_sanitize_field( $value, $def );
					},
					'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}
}
add_action( 'init', 'mnm_register_meta', 20 );

/**
 * Sanitise one field value by type.
 *
 * @param mixed $value Raw value.
 * @param array $def   Field definition.
 * @return mixed
 */
function mnm_sanitize_field( $value, $def ) {
	$type = isset( $def['type'] ) ? $def['type'] : 'text';
	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'number':
			return ( '' === $value || null === $value || ! is_numeric( $value ) ) ? '' : (string) max( 0, (float) $value );
		case 'date':
			return ( is_string( $value ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) && strtotime( $value ) ) ? $value : '';
		case 'datetime':
			return ( is_string( $value ) && preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) && strtotime( $value ) ) ? $value : '';
		case 'url':
			return esc_url_raw( (string) $value );
		case 'select':
			$value = (string) $value;
			return isset( $def['options'][ $value ] ) ? $value : (string) key( $def['options'] );
		case 'checkbox':
			return $value ? '1' : '';
		case 'gallery':
			return implode( ',', array_filter( array_map( 'absint', explode( ',', (string) $value ) ) ) );
		case 'image':
		case 'post_select':
			return (string) absint( $value );
		case 'text':
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Render one field control.
 *
 * @param string $name  Input name.
 * @param mixed  $value Current value.
 * @param array  $def   Field definition.
 * @param string $id    Optional element ID.
 */
function mnm_render_field( $name, $value, $def, $id = '' ) {
	$id   = $id ? $id : sanitize_html_class( $name );
	$type = isset( $def['type'] ) ? $def['type'] : 'text';
	$ph   = isset( $def['placeholder'] ) ? $def['placeholder'] : '';

	switch ( $type ) {
		case 'textarea':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text" placeholder="%4$s">%5$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				isset( $def['rows'] ) ? (int) $def['rows'] : 4,
				esc_attr( $ph ),
				esc_textarea( $value )
			);
			break;

		case 'number':
			printf(
				'<input type="number" id="%1$s" name="%2$s" value="%3$s" min="0" step="%4$s" class="regular-text" placeholder="%5$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( isset( $def['step'] ) ? $def['step'] : '1' ),
				esc_attr( $ph )
			);
			break;

		case 'date':
		case 'datetime':
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" />',
				'date' === $type ? 'date' : 'datetime-local',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		case 'url':
			printf(
				'<input type="url" id="%1$s" name="%2$s" value="%3$s" class="large-text" placeholder="%4$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $ph ? $ph : 'https://' )
			);
			break;

		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $def['options'] as $k => $label ) {
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $k ), selected( (string) $value, (string) $k, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'post_select':
			$posts = get_posts(
				array(
					'post_type'      => $def['post_type'],
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => 100,
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			printf( '<select id="%1$s" name="%2$s"><option value="">%3$s</option>', esc_attr( $id ), esc_attr( $name ), esc_html__( 'None', 'mnm-foundation-core' ) );
			foreach ( $posts as $p ) {
				printf( '<option value="%1$d"%2$s>%3$s</option>', (int) $p->ID, selected( (int) $value, (int) $p->ID, false ), esc_html( get_the_title( $p ) ) );
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf(
				'<label><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> %4$s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( ! empty( $value ), true, false ),
				esc_html( isset( $def['text'] ) ? $def['text'] : '' )
			);
			break;

		case 'image':
		case 'gallery':
			$multiple = 'gallery' === $type;
			$ids      = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
			printf( '<div class="mnm-media-field" data-multiple="%1$s">', $multiple ? '1' : '0' );
			printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" class="mnm-media-ids" />', esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
			echo '<ul class="mnm-media-preview">';
			foreach ( $ids as $att_id ) {
				$thumb = wp_get_attachment_image( $att_id, 'thumbnail' );
				if ( $thumb ) {
					printf( '<li data-id="%1$d">%2$s<button type="button" class="mnm-media-remove" aria-label="%3$s">&times;</button></li>', (int) $att_id, $thumb, esc_attr__( 'Remove image', 'mnm-foundation-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
			echo '</ul>';
			printf(
				'<button type="button" class="button mnm-media-pick">%s</button>',
				esc_html( $multiple ? __( 'Add / change images', 'mnm-foundation-core' ) : __( 'Choose image', 'mnm-foundation-core' ) )
			);
			echo '</div>';
			break;

		case 'text':
		default:
			printf(
				'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text" placeholder="%4$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $ph )
			);
			break;
	}

	if ( ! empty( $def['desc'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $def['desc'] ) );
	}
}

/**
 * Register meta boxes.
 */
function mnm_add_meta_boxes() {
	$titles = array(
		'mnm_project'   => __( 'Project details', 'mnm-foundation-core' ),
		'mnm_programme' => __( 'Programme details', 'mnm-foundation-core' ),
		'mnm_event'     => __( 'Event details', 'mnm-foundation-core' ),
		'mnm_article'   => __( 'Article options', 'mnm-foundation-core' ),
		'mnm_update'    => __( 'Update options', 'mnm-foundation-core' ),
		'mnm_campaign'  => __( 'Campaign figures', 'mnm-foundation-core' ),
	);
	foreach ( $titles as $pt => $title ) {
		add_meta_box( 'mnm_details', $title, 'mnm_render_metabox', $pt, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'mnm_add_meta_boxes' );

/**
 * Render a details meta box.
 *
 * @param WP_Post $post Post.
 */
function mnm_render_metabox( $post ) {
	$defs = mnm_field_defs();
	if ( empty( $defs[ $post->post_type ] ) ) {
		return;
	}
	wp_nonce_field( 'mnm_save_meta_' . $post->post_type, 'mnm_meta_nonce' );
	echo '<table class="form-table mnm-meta-table" role="presentation"><tbody>';
	foreach ( $defs[ $post->post_type ] as $key => $def ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( '' === $value && isset( $def['default'] ) ) {
			$value = $def['default'];
		}
		echo '<tr><th scope="row"><label for="' . esc_attr( 'f' . $key ) . '">' . esc_html( $def['label'] ) . '</label></th><td>';
		mnm_render_field( 'mnm_meta[' . $key . ']', $value, $def, 'f' . $key );
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Save meta box values.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function mnm_save_metabox( $post_id, $post ) {
	if ( ! isset( $_POST['mnm_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mnm_meta_nonce'] ) ), 'mnm_save_meta_' . $post->post_type ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$defs = mnm_field_defs();
	if ( empty( $defs[ $post->post_type ] ) ) {
		return;
	}
	$raw = isset( $_POST['mnm_meta'] ) && is_array( $_POST['mnm_meta'] ) ? wp_unslash( $_POST['mnm_meta'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $defs[ $post->post_type ] as $key => $def ) {
		$value = isset( $raw[ $key ] ) ? $raw[ $key ] : '';
		$clean = mnm_sanitize_field( $value, $def );
		if ( '' === $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}

}
add_action( 'save_post', 'mnm_save_metabox', 10, 2 );

