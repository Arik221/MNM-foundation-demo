<?php
/**
 * Front-end output: campaign progress, statistics, gallery, registration and contact forms,
 * plus matching shortcodes. These render with neutral classes (styled by the plugin's own small
 * stylesheet), so they keep working if the theme is ever changed.
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the plugin's front-end assets.
 */
function mnm_enqueue_frontend() {
	wp_enqueue_style( 'mnm-core', MNM_CORE_URL . 'assets/css/frontend.css', array(), MNM_CORE_VERSION );
	wp_enqueue_script( 'mnm-core', MNM_CORE_URL . 'assets/js/frontend.js', array(), MNM_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script(
		'mnm-core',
		'mnmCore',
		array(
			'postUrl' => admin_url( 'admin-post.php' ),
			'i18n'    => array(
				'required' => __( 'This field is required.', 'mnm-foundation-core' ),
				'email'    => __( 'Please enter a valid email address.', 'mnm-foundation-core' ),
				'phone'    => __( 'Please enter a valid phone number.', 'mnm-foundation-core' ),
				'sending'  => __( 'Sending…', 'mnm-foundation-core' ),
				'error'    => __( 'Sorry, something went wrong. Please try again.', 'mnm-foundation-core' ),
				'close'    => __( 'Close', 'mnm-foundation-core' ),
				'prev'     => __( 'Previous image', 'mnm-foundation-core' ),
				'next'     => __( 'Next image', 'mnm-foundation-core' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mnm_enqueue_frontend' );

/**
 * Prevent page caches storing pages whose forms carry time-limited nonces.
 */
function mnm_nocache_form_pages() {
	if ( is_singular() && get_page_template_slug() && preg_match( '/(marathon|contact|get-involved)/', (string) get_page_template_slug() ) ) {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
	}
}
add_action( 'template_redirect', 'mnm_nocache_form_pages' );

/* ---------------------------------------------------------------------------
 * Campaigns
 * ------------------------------------------------------------------------- */

/**
 * Normalised campaign figures.
 *
 * @param int $id Campaign post ID.
 * @return array|null
 */
function mnm_campaign_data( $id ) {
	$post = get_post( $id );
	if ( ! $post || 'mnm_campaign' !== $post->post_type ) {
		return null;
	}
	$target = (float) get_post_meta( $id, '_mnm_target', true );
	$raised = (float) get_post_meta( $id, '_mnm_raised', true );
	$symbol = get_post_meta( $id, '_mnm_currency', true );
	return array(
		'id'          => (int) $id,
		'title'       => get_the_title( $id ),
		'target'      => $target,
		'raised'      => $raised,
		'percent'     => $target > 0 ? min( 100, round( $raised / $target * 100 ) ) : 0,
		'symbol'      => $symbol ? $symbol : '₹',
		'verified_on' => get_post_meta( $id, '_mnm_verified_on', true ),
		'note'        => get_post_meta( $id, '_mnm_verified_note', true ),
		'demo'        => (bool) get_post_meta( $id, '_mnm_demo', true ),
		'donate_url'  => get_post_meta( $id, '_mnm_donate_url', true ),
		'ends'        => get_post_meta( $id, '_mnm_end_date', true ),
	);
}

/**
 * Progress bar markup. Always labels figures as manually verified and flags demo data.
 *
 * @param int   $id   Campaign ID.
 * @param array $args title (bool), cta (bool), class.
 * @return string
 */
function mnm_render_progress( $id, $args = array() ) {
	$c = mnm_campaign_data( $id );
	if ( ! $c ) {
		return '';
	}
	$args = wp_parse_args( $args, array( 'title' => true, 'cta' => false, 'class' => '' ) );
	$uid  = 'mnm-prog-' . (int) $id . '-' . wp_rand( 100, 999 );

	ob_start();
	?>
	<div class="mnm-progress <?php echo esc_attr( $args['class'] ); ?>">
		<?php if ( $args['title'] ) : ?>
			<p class="mnm-progress__title"><?php echo esc_html( $c['title'] ); ?> <?php echo $c['demo'] ? mnm_demo_badge() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php endif; ?>
		<div class="mnm-progress__figures">
			<div><span class="mnm-progress__label"><?php esc_html_e( 'Raised so far', 'mnm-foundation-core' ); ?></span><strong class="mnm-progress__raised"><?php echo esc_html( mnm_format_money( $c['raised'], $c['symbol'] ) ); ?></strong></div>
			<div><span class="mnm-progress__label"><?php esc_html_e( 'Target', 'mnm-foundation-core' ); ?></span><strong><?php echo esc_html( mnm_format_money( $c['target'], $c['symbol'] ) ); ?></strong></div>
		</div>
		<div class="mnm-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $c['percent'] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: campaign title. */ __( 'Progress for %s', 'mnm-foundation-core' ), $c['title'] ) ); ?>" id="<?php echo esc_attr( $uid ); ?>">
			<span class="mnm-progress__fill" style="--mnm-pct:<?php echo esc_attr( $c['percent'] ); ?>%"></span>
		</div>
		<p class="mnm-progress__meta"><strong><?php echo esc_html( $c['percent'] ); ?>%</strong> <?php esc_html_e( 'of target', 'mnm-foundation-core' ); ?>
			&middot; <?php
			if ( $c['verified_on'] ) {
				/* translators: %s: date. */
				echo esc_html( sprintf( __( 'Amount manually verified by staff on %s', 'mnm-foundation-core' ), mysql2date( get_option( 'date_format' ), $c['verified_on'] ) ) );
			} else {
				esc_html_e( 'Amount entered manually by staff', 'mnm-foundation-core' );
			}
			if ( $c['note'] ) {
				echo ' (' . esc_html( $c['note'] ) . ')';
			}
			?></p>
		<?php if ( $args['cta'] ) : ?>
			<?php $url = $c['donate_url'] ? $c['donate_url'] : mnm_donate_url(); ?>
			<a class="mnm-btn" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Support this campaign', 'mnm-foundation-core' ); ?></a>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Where "Donate" buttons point by default.
 *
 * @return string
 */
function mnm_donate_url() {
	$url = mnm_setting( 'donate_url' );
	if ( $url ) {
		return $url;
	}
	$page = get_page_by_path( 'get-involved' );
	return $page ? get_permalink( $page ) . '#donate' : home_url( '/' );
}

/* ---------------------------------------------------------------------------
 * Stats
 * ------------------------------------------------------------------------- */

/**
 * Statistic tiles that have both value and label.
 *
 * @return array[]
 */
function mnm_stats() {
	$out = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$v = mnm_setting( "stat_{$i}_value" );
		$l = mnm_setting( "stat_{$i}_label" );
		if ( '' !== $v && '' !== $l ) {
			$out[] = array( 'value' => $v, 'label' => $l );
		}
	}
	return $out;
}

/**
 * Render statistic tiles.
 *
 * @return string
 */
function mnm_render_stats() {
	$stats = mnm_stats();
	if ( ! $stats ) {
		return '';
	}
	ob_start();
	echo '<div class="mnm-stats">';
	foreach ( $stats as $s ) {
		preg_match( '/^([^0-9]*)([0-9][0-9,\.]*)(.*)$/u', $s['value'], $m );
		$has_num = ! empty( $m );
		printf(
			'<div class="mnm-stat"><strong class="mnm-stat__value"%1$s>%2$s</strong><span class="mnm-stat__label">%3$s</span></div>',
			$has_num ? ' data-count="' . esc_attr( str_replace( ',', '', $m[2] ) ) . '" data-prefix="' . esc_attr( $m[1] ) . '" data-suffix="' . esc_attr( $m[3] ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput
			esc_html( $s['value'] ),
			esc_html( $s['label'] )
		);
	}
	echo '</div>';
	return ob_get_clean();
}

/* ---------------------------------------------------------------------------
 * Gallery
 * ------------------------------------------------------------------------- */

/**
 * Gallery images (Media Library items that belong to a Gallery Collection).
 *
 * @param array $args limit, collection (slug), ids.
 * @return array[]
 */
function mnm_gallery_items( $args = array() ) {
	$args = wp_parse_args( $args, array( 'limit' => 60, 'collection' => '', 'ids' => array() ) );
	$q    = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image',
		'posts_per_page' => (int) $args['limit'],
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
	);
	if ( $args['ids'] ) {
		$q['post__in'] = $args['ids'];
		$q['orderby']  = 'post__in';
	} elseif ( $args['collection'] ) {
		$q['tax_query'] = array( array( 'taxonomy' => 'mnm_gallery_cat', 'field' => 'slug', 'terms' => $args['collection'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	} else {
		$q['tax_query'] = array( array( 'taxonomy' => 'mnm_gallery_cat', 'operator' => 'EXISTS' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$items = array();
	foreach ( get_posts( $q ) as $att ) {
		$terms   = wp_get_object_terms( $att->ID, 'mnm_gallery_cat' );
		$items[] = array(
			'id'      => $att->ID,
			'full'    => wp_get_attachment_image_url( $att->ID, 'large' ),
			'caption' => $att->post_excerpt,
			'alt'     => get_post_meta( $att->ID, '_wp_attachment_image_alt', true ),
			'terms'   => is_wp_error( $terms ) ? array() : $terms,
		);
	}
	return $items;
}

/**
 * Render a gallery: filter chips, masonry columns, lightbox hooks.
 *
 * @param array $args limit, collection, ids, filters (bool).
 * @return string
 */
function mnm_render_gallery( $args = array() ) {
	$args    = wp_parse_args( $args, array( 'filters' => true ) );
	$items   = mnm_gallery_items( $args );
	if ( ! $items ) {
		return '<p class="mnm-empty">' . esc_html__( 'No photographs have been added yet.', 'mnm-foundation-core' ) . '</p>';
	}
	$cats = array();
	foreach ( $items as $it ) {
		foreach ( $it['terms'] as $t ) {
			$cats[ $t->slug ] = $t->name;
		}
	}
	ob_start();
	echo '<div class="mnm-gallery" data-mnm-gallery>';
	if ( $args['filters'] && count( $cats ) > 1 ) {
		echo '<div class="mnm-gallery__filters" role="group" aria-label="' . esc_attr__( 'Filter photographs by collection', 'mnm-foundation-core' ) . '">';
		echo '<button type="button" class="is-active" data-filter="*" aria-pressed="true">' . esc_html__( 'All', 'mnm-foundation-core' ) . '</button>';
		foreach ( $cats as $slug => $name ) {
			printf( '<button type="button" data-filter="%1$s" aria-pressed="false">%2$s</button>', esc_attr( $slug ), esc_html( $name ) );
		}
		echo '</div>';
	}
	echo '<div class="mnm-gallery__grid">';
	foreach ( $items as $it ) {
		$slugs = implode( ' ', wp_list_pluck( $it['terms'], 'slug' ) );
		printf(
			'<figure class="mnm-gallery__item" data-cats="%1$s"><a href="%2$s" data-caption="%3$s" data-lightbox>%4$s</a>%5$s</figure>',
			esc_attr( $slugs ),
			esc_url( $it['full'] ),
			esc_attr( $it['caption'] ),
			wp_get_attachment_image( $it['id'], 'medium_large', false, array( 'loading' => 'lazy', 'alt' => $it['alt'] ? $it['alt'] : $it['caption'] ) ), // phpcs:ignore WordPress.Security.EscapeOutput
			$it['caption'] ? '<figcaption>' . esc_html( $it['caption'] ) . '</figcaption>' : '' // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}
	echo '</div></div>';
	return ob_get_clean();
}

/* ---------------------------------------------------------------------------
 * Forms
 * ------------------------------------------------------------------------- */

/**
 * Print one labelled field.
 *
 * @param array $f Field config: name, label, type, required, options, autocomplete, hint.
 * @param array $err Field errors.
 * @param array $old Old values.
 */
function mnm_form_field( $f, $err = array(), $old = array() ) {
	$name  = $f['name'];
	$id    = 'f-' . sanitize_html_class( $name );
	$type  = isset( $f['type'] ) ? $f['type'] : 'text';
	$req   = ! empty( $f['required'] );
	$val   = isset( $old[ $f['key'] ] ) ? $old[ $f['key'] ] : '';
	$msg   = isset( $err[ $f['key'] ] ) ? $err[ $f['key'] ] : '';
	$attrs = ( $req ? ' required aria-required="true"' : '' ) . ( $msg ? ' aria-invalid="true"' : '' ) . ' aria-describedby="' . esc_attr( $id ) . '-err' . ( ! empty( $f['hint'] ) ? ' ' . esc_attr( $id ) . '-hint' : '' ) . '"';
	if ( ! empty( $f['autocomplete'] ) ) {
		$attrs .= ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"';
	}
	echo '<div class="mnm-field' . ( ! empty( $f['wide'] ) ? ' mnm-field--wide' : '' ) . ( $msg ? ' has-error' : '' ) . '">';
	if ( 'checkbox' === $type ) {
		printf( '<label class="mnm-check"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> <span>%4$s</span></label>', esc_attr( $id ), esc_attr( $name ), $attrs, wp_kses_post( $f['label'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		printf( '<label for="%1$s">%2$s%3$s</label>', esc_attr( $id ), esc_html( $f['label'] ), $req ? ' <span class="mnm-req" aria-hidden="true">*</span>' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
		if ( 'select' === $type ) {
			printf( '<select id="%1$s" name="%2$s"%3$s><option value="">%4$s</option>', esc_attr( $id ), esc_attr( $name ), $attrs, esc_html__( 'Select…', 'mnm-foundation-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			foreach ( $f['options'] as $o ) {
				printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $o ), selected( $val, $o, false ) );
			}
			echo '</select>';
		} elseif ( 'textarea' === $type ) {
			printf( '<textarea id="%1$s" name="%2$s" rows="5"%3$s>%4$s</textarea>', esc_attr( $id ), esc_attr( $name ), $attrs, esc_textarea( $val ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s"%5$s />', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( $val ), $attrs . ( 'date' === $type ? ' max="' . esc_attr( gmdate( 'Y-m-d' ) ) . '"' : '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
	if ( ! empty( $f['hint'] ) ) {
		echo '<small class="mnm-hint" id="' . esc_attr( $id ) . '-hint">' . esc_html( $f['hint'] ) . '</small>';
	}
	echo '<span class="mnm-error" id="' . esc_attr( $id ) . '-err" role="alert">' . esc_html( $msg ) . '</span></div>';
}

/**
 * Registration form (with confirmation panel).
 *
 * @return string
 */
function mnm_render_registration_form() {
	$flash = mnm_flash();
	$races = array_keys( mnm_reg_races() );
	ob_start();

	echo '<div class="mnm-reg" id="register" data-mnm-reg>';

	// Success state (server-rendered for the non-JS fallback; JS fills the same panel).
	$ok = ! empty( $flash['ok'] );
	echo '<div class="mnm-reg__success" data-mnm-success' . ( $ok ? '' : ' hidden' ) . ' tabindex="-1">';
	echo '<h3>' . esc_html__( 'You are registered', 'mnm-foundation-core' ) . '</h3>';
	echo '<p>' . esc_html__( 'Thank you for registering for the marathon. Please keep your reference number:', 'mnm-foundation-core' ) . '</p>';
	echo '<p class="mnm-reg__ref" data-mnm-ref>' . esc_html( isset( $flash['ref'] ) ? $flash['ref'] : '' ) . '</p>';
	echo '</div>';

	if ( 'closed' === mnm_setting( 'marathon_status', 'open' ) ) {
		echo '<p class="mnm-notice">' . esc_html__( 'Registration is currently closed.', 'mnm-foundation-core' ) . '</p></div>';
		return ob_get_clean();
	}

	$err = ( ! $ok && isset( $flash['errors'] ) ) ? $flash['errors'] : array();
	$msg = ( ! $ok && ! empty( $flash['message'] ) ) ? $flash['message'] : '';

	echo '<form class="mnm-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate data-mnm-form="register"' . ( $ok ? ' hidden' : '' ) . '>';
	echo '<input type="hidden" name="action" value="mnm_register" />';
	mnm_form_security_fields( 'mnm_register' );
	echo '<div class="mnm-form__status" role="status" aria-live="polite" data-mnm-status>' . esc_html( $msg ) . '</div>';
	echo '<div class="mnm-form__grid">';
	$fields = array(
		array( 'key' => 'full_name', 'label' => __( 'Full name', 'mnm-foundation-core' ), 'required' => true, 'autocomplete' => 'name' ),
		array( 'key' => 'email', 'label' => __( 'Email', 'mnm-foundation-core' ), 'type' => 'email', 'required' => true, 'autocomplete' => 'email' ),
		array( 'key' => 'phone', 'label' => __( 'Phone', 'mnm-foundation-core' ), 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel' ),
		array( 'key' => 'dob', 'label' => __( 'Date of birth', 'mnm-foundation-core' ), 'type' => 'date', 'required' => true, 'autocomplete' => 'bday' ),
		array( 'key' => 'location', 'label' => __( 'Location', 'mnm-foundation-core' ), 'required' => true, 'hint' => __( 'Village or town where you live', 'mnm-foundation-core' ) ),
		array( 'key' => 'race', 'label' => __( 'Race category', 'mnm-foundation-core' ), 'type' => 'select', 'required' => true, 'options' => $races ),
		array( 'key' => 'tshirt', 'label' => __( 'T-shirt size', 'mnm-foundation-core' ), 'type' => 'select', 'required' => true, 'options' => mnm_reg_sizes() ),
		array( 'key' => 'emergency_name', 'label' => __( 'Emergency contact name', 'mnm-foundation-core' ), 'required' => true ),
		array( 'key' => 'emergency_phone', 'label' => __( 'Emergency contact phone', 'mnm-foundation-core' ), 'type' => 'tel', 'required' => true ),
	);
	foreach ( $fields as $f ) {
		$f['name'] = 'mnm_reg[' . $f['key'] . ']';
		mnm_form_field( $f, $err );
	}
	echo '</div>';
	mnm_form_field(
		array(
			'key'      => 'consent',
			'name'     => 'mnm_reg[consent]',
			'type'     => 'checkbox',
			'required' => true,
			'wide'     => true,
			'label'    => esc_html__( 'I agree that MNM Foundation may store my details to manage my registration and contact me about this event. If I am under 18, a parent or guardian has agreed to my taking part.', 'mnm-foundation-core' ),
		),
		$err
	);
	echo '<button type="submit" class="mnm-btn mnm-btn--lg">' . esc_html__( 'Register for the marathon', 'mnm-foundation-core' ) . '</button>';
	echo '</form></div>';
	return ob_get_clean();
}

/**
 * Contact form.
 *
 * @param string $topic Pre-selected topic.
 * @return string
 */
function mnm_render_contact_form( $topic = '' ) {
	$flash = mnm_flash();
	$ok    = ! empty( $flash['ok'] );
	$err   = ( ! $ok && isset( $flash['errors'] ) ) ? $flash['errors'] : array();
	ob_start();
	echo '<div class="mnm-reg" id="contact-form" data-mnm-reg>';
	echo '<div class="mnm-reg__success" data-mnm-success' . ( $ok ? '' : ' hidden' ) . ' tabindex="-1"><h3>' . esc_html__( 'Message sent', 'mnm-foundation-core' ) . '</h3><p>' . esc_html__( 'Thank you for getting in touch. We will reply as soon as we can.', 'mnm-foundation-core' ) . '</p>';
	echo '</div>';
	echo '<form class="mnm-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate data-mnm-form="contact"' . ( $ok ? ' hidden' : '' ) . '>';
	echo '<input type="hidden" name="action" value="mnm_contact" />';
	mnm_form_security_fields( 'mnm_contact' );
	echo '<div class="mnm-form__status" role="status" aria-live="polite" data-mnm-status>' . esc_html( ( ! $ok && ! empty( $flash['message'] ) ) ? $flash['message'] : '' ) . '</div><div class="mnm-form__grid">';
	mnm_form_field( array( 'key' => 'c_name', 'name' => 'c_name', 'label' => __( 'Your name', 'mnm-foundation-core' ), 'required' => true, 'autocomplete' => 'name' ), $err );
	mnm_form_field( array( 'key' => 'c_email', 'name' => 'c_email', 'label' => __( 'Email', 'mnm-foundation-core' ), 'type' => 'email', 'required' => true, 'autocomplete' => 'email' ), $err );
	mnm_form_field( array( 'key' => 'c_topic', 'name' => 'c_topic', 'label' => __( 'Topic', 'mnm-foundation-core' ), 'type' => 'select', 'wide' => true, 'options' => array( 'General enquiry', 'Volunteering', 'Donations and support', 'Marathon', 'Partnerships' ) ), $err, array( 'c_topic' => $topic ) );
	mnm_form_field( array( 'key' => 'c_message', 'name' => 'c_message', 'label' => __( 'Message', 'mnm-foundation-core' ), 'type' => 'textarea', 'required' => true, 'wide' => true ), $err );
	echo '</div><button type="submit" class="mnm-btn mnm-btn--lg">' . esc_html__( 'Send message', 'mnm-foundation-core' ) . '</button></form></div>';
	return ob_get_clean();
}

/* ---------------------------------------------------------------------------
 * Shortcodes
 * ------------------------------------------------------------------------- */

add_shortcode( 'mnm_registration_form', 'mnm_render_registration_form' );
add_shortcode( 'mnm_stats', 'mnm_render_stats' );
add_shortcode(
	'mnm_contact_form',
	function ( $atts ) {
		$a = shortcode_atts( array( 'topic' => '' ), $atts );
		return mnm_render_contact_form( $a['topic'] );
	}
);
add_shortcode(
	'mnm_campaign',
	function ( $atts ) {
		$a = shortcode_atts( array( 'id' => 0, 'cta' => '1' ), $atts );
		return mnm_render_progress( (int) $a['id'], array( 'cta' => (bool) $a['cta'] ) );
	}
);
add_shortcode(
	'mnm_gallery',
	function ( $atts ) {
		$a = shortcode_atts( array( 'collection' => '', 'limit' => 60 ), $atts );
		return mnm_render_gallery( array( 'collection' => sanitize_title( $a['collection'] ), 'limit' => (int) $a['limit'] ) );
	}
);

/**
 * Memorial entries from settings.
 *
 * @return array[]
 */
function mnm_get_memorials() {
	$m = mnm_setting( 'memorials', array() );
	return is_array( $m ) ? $m : array();
}
