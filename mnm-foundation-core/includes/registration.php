<?php
/**
 * Marathon registration (prototype / demonstration mode) and contact form.
 *
 * Registrations are stored as a private custom post type, so WordPress itself provides the
 * admin list, search, bulk delete and capability checks. Security: nonce, honeypot, time-trap,
 * server-side validation, escaping. No payment is taken.
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Race categories from settings ("Label|Description" per line).
 *
 * @return array label => description
 */
function mnm_reg_races() {
	$out = array();
	foreach ( mnm_parse_pairs( mnm_setting( 'marathon_categories' ) ) as $row ) {
		$out[ $row['label'] ] = $row['desc'];
	}
	return $out;
}

/**
 * T-shirt sizes.
 *
 * @return string[]
 */
function mnm_reg_sizes() {
	return mnm_csv_to_array( mnm_setting( 'marathon_tshirts', 'XS, S, M, L, XL, XXL' ) );
}

/**
 * Number of registrations (all published registration records).
 *
 * @return int
 */
function mnm_reg_count() {
	$counts = wp_count_posts( 'mnm_registration' );
	return isset( $counts->publish ) ? (int) $counts->publish : 0;
}

/**
 * Generate a unique reference such as MNM-7K4Q2X.
 *
 * @return string
 */
function mnm_reg_new_reference() {
	$chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
	for ( $try = 0; $try < 10; $try++ ) {
		$ref = 'MNM-';
		for ( $i = 0; $i < 6; $i++ ) {
			$ref .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
		}
		$exists = get_posts(
			array(
				'post_type'      => 'mnm_registration',
				'post_status'    => 'any',
				'meta_key'       => '_mnm_ref',
				'meta_value'     => $ref,
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		if ( ! $exists ) {
			return $ref;
		}
	}
	return 'MNM-' . strtoupper( wp_generate_password( 8, false ) );
}

/**
 * Signed time token + honeypot + nonce markup for a public form.
 *
 * @param string $action Nonce action.
 */
function mnm_form_security_fields( $action ) {
	$ts  = time();
	$sig = hash_hmac( 'sha256', 'mnm|' . $ts, wp_salt( 'nonce' ) );
	wp_nonce_field( $action, 'mnm_nonce' );
	echo '<input type="hidden" name="mnm_ts" value="' . esc_attr( $ts . '.' . $sig ) . '" />';
	echo '<div class="mnm-hp" aria-hidden="true"><label>' . esc_html__( 'Leave this field empty', 'mnm-foundation-core' ) . '<input type="text" name="mnm_website" value="" tabindex="-1" autocomplete="off" /></label></div>';
}

/**
 * Verify nonce, honeypot and time-trap.
 *
 * @param string $action Nonce action.
 * @return true|WP_Error
 */
function mnm_verify_form_security( $action ) {
	$nonce = isset( $_POST['mnm_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mnm_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		return new WP_Error( 'nonce', __( 'Your session has expired. Please reload the page and try again.', 'mnm-foundation-core' ) );
	}
	if ( ! empty( $_POST['mnm_website'] ) ) {
		return new WP_Error( 'spam', __( 'We could not accept this submission.', 'mnm-foundation-core' ) );
	}
	$parts = explode( '.', isset( $_POST['mnm_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['mnm_ts'] ) ) : '' );
	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) || ! hash_equals( hash_hmac( 'sha256', 'mnm|' . $parts[0], wp_salt( 'nonce' ) ), $parts[1] ) ) {
		return new WP_Error( 'spam', __( 'We could not accept this submission.', 'mnm-foundation-core' ) );
	}
	if ( time() - (int) $parts[0] < 2 ) {
		return new WP_Error( 'fast', __( 'That was very quick. Please check your answers and submit again.', 'mnm-foundation-core' ) );
	}
	// Light per-visitor rate limit (hashed IP, never stored raw): 12 submissions per hour per form.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'mnm_rl_' . substr( hash_hmac( 'sha256', $ip . '|' . $action, wp_salt( 'auth' ) ), 0, 32 );
	$n   = (int) get_transient( $key );
	if ( $n >= 12 ) {
		return new WP_Error( 'rate', __( 'Too many submissions from your connection. Please try again later.', 'mnm-foundation-core' ) );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * Send a JSON reply (when called by JavaScript) or redirect back with a flash message.
 *
 * @param bool   $ok      Success flag.
 * @param array  $payload Data: message, errors, ref.
 * @param string $anchor  Anchor on return page.
 */
function mnm_form_respond( $ok, array $payload, $anchor ) {
	if ( ! empty( $_POST['mnm_ajax'] ) ) {
		wp_send_json( array_merge( array( 'ok' => $ok ), $payload ), $ok ? 200 : 422 );
	}
	$back = wp_get_referer() ? remove_query_arg( array( 'mnm_flash' ), wp_get_referer() ) : home_url( '/' );
	$flash = wp_generate_password( 14, false );
	set_transient( 'mnm_flash_' . $flash, array_merge( array( 'ok' => $ok ), $payload ), 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( 'mnm_flash', $flash, $back ) . '#' . $anchor, 303 );
	exit;
}

/**
 * Flash data for the current request (non-JS fallback).
 *
 * @return array
 */
function mnm_flash() {
	static $flash = null;
	if ( null === $flash ) {
		$token = isset( $_GET['mnm_flash'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( wp_unslash( $_GET['mnm_flash'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$flash = $token ? get_transient( 'mnm_flash_' . $token ) : array();
		$flash = is_array( $flash ) ? $flash : array();
	}
	return $flash;
}

/**
 * Handle marathon registration submissions.
 */
function mnm_handle_registration() {
	$sec = mnm_verify_form_security( 'mnm_register' );
	if ( is_wp_error( $sec ) ) {
		mnm_form_respond( false, array( 'message' => $sec->get_error_message(), 'errors' => array() ), 'register' );
	}

	$in = isset( $_POST['mnm_reg'] ) && is_array( $_POST['mnm_reg'] ) ? wp_unslash( $_POST['mnm_reg'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$g  = function ( $k ) use ( $in ) {
		return isset( $in[ $k ] ) && is_scalar( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
	};

	$d = array(
		'full_name'       => $g( 'full_name' ),
		'email'           => sanitize_email( $g( 'email' ) ),
		'phone'           => $g( 'phone' ),
		'dob'             => $g( 'dob' ),
		'location'        => $g( 'location' ),
		'race'            => $g( 'race' ),
		'tshirt'          => $g( 'tshirt' ),
		'emergency_name'  => $g( 'emergency_name' ),
		'emergency_phone' => $g( 'emergency_phone' ),
		'consent'         => ! empty( $in['consent'] ),
	);

	$err = array();
	if ( strlen( $d['full_name'] ) < 2 || strlen( $d['full_name'] ) > 120 ) {
		$err['full_name'] = __( 'Please enter your full name.', 'mnm-foundation-core' );
	}
	if ( ! is_email( $d['email'] ) ) {
		$err['email'] = __( 'Please enter a valid email address.', 'mnm-foundation-core' );
	}
	if ( ! preg_match( '/^[0-9+()\-\s]{7,20}$/', $d['phone'] ) ) {
		$err['phone'] = __( 'Please enter a valid phone number.', 'mnm-foundation-core' );
	}
	$age = null;
	$ts  = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d['dob'] ) ? strtotime( $d['dob'] ) : false;
	if ( $ts ) {
		$age = (int) floor( ( time() - $ts ) / YEAR_IN_SECONDS );
	}
	if ( null === $age || $age < 5 || $age > 100 ) {
		$err['dob'] = __( 'Please enter a valid date of birth.', 'mnm-foundation-core' );
	}
	if ( strlen( $d['location'] ) < 2 ) {
		$err['location'] = __( 'Please tell us where you live.', 'mnm-foundation-core' );
	}
	if ( ! array_key_exists( $d['race'], mnm_reg_races() ) ) {
		$err['race'] = __( 'Please choose a race category.', 'mnm-foundation-core' );
	}
	if ( ! in_array( $d['tshirt'], mnm_reg_sizes(), true ) ) {
		$err['tshirt'] = __( 'Please choose a T-shirt size.', 'mnm-foundation-core' );
	}
	if ( strlen( $d['emergency_name'] ) < 2 ) {
		$err['emergency_name'] = __( 'Please enter an emergency contact name.', 'mnm-foundation-core' );
	}
	if ( ! preg_match( '/^[0-9+()\-\s]{7,20}$/', $d['emergency_phone'] ) ) {
		$err['emergency_phone'] = __( 'Please enter a valid emergency contact number.', 'mnm-foundation-core' );
	}
	if ( ! $d['consent'] ) {
		$err['consent'] = __( 'Please tick the consent box to continue.', 'mnm-foundation-core' );
	}
	if ( 'closed' === mnm_setting( 'marathon_status', 'open' ) ) {
		$err['_form'] = __( 'Registration is currently closed.', 'mnm-foundation-core' );
	}

	if ( ! $err ) {
		$dupe = get_posts(
			array(
				'post_type'      => 'mnm_registration',
				'post_status'    => 'any',
				'meta_key'       => '_mnm_email',
				'meta_value'     => $d['email'],
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		if ( $dupe ) {
			$err['email'] = __( 'This email address is already registered.', 'mnm-foundation-core' );
		}
	}

	if ( $err ) {
		mnm_form_respond(
			false,
			array(
				'message' => __( 'Please correct the highlighted fields.', 'mnm-foundation-core' ),
				'errors'  => $err,
			),
			'register'
		);
	}

	$ref = mnm_reg_new_reference();
	$id  = wp_insert_post(
		array(
			'post_type'    => 'mnm_registration',
			'post_status'  => 'publish',
			'post_title'   => $ref,
			'post_content' => $d['full_name'] . ' ' . $d['email'] . ' ' . $d['phone'] . ' ' . $d['location'],
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		mnm_form_respond( false, array( 'message' => __( 'Sorry, something went wrong. Please try again.', 'mnm-foundation-core' ), 'errors' => array() ), 'register' );
	}

	$meta = array(
		'_mnm_ref'             => $ref,
		'_mnm_name'            => $d['full_name'],
		'_mnm_email'           => $d['email'],
		'_mnm_phone'           => $d['phone'],
		'_mnm_dob'             => $d['dob'],
		'_mnm_age'             => $age,
		'_mnm_location'        => $d['location'],
		'_mnm_race'            => $d['race'],
		'_mnm_tshirt'          => $d['tshirt'],
		'_mnm_emergency_name'  => $d['emergency_name'],
		'_mnm_emergency_phone' => $d['emergency_phone'],
		'_mnm_consent_at'      => current_time( 'mysql' ),
		'_mnm_reg_status'      => 'pending',
		'_mnm_demo'            => mnm_is_demo_mode() ? '1' : '',
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}

	// Demonstration mode sends no email. Otherwise confirm to the participant and notify staff.
	if ( ! mnm_is_demo_mode() ) {
		/* translators: %s: reference. */
		$subject = sprintf( __( 'Your MNM Foundation marathon registration (%s)', 'mnm-foundation-core' ), $ref );
		$body    = sprintf( "Hello %s,\n\nThank you for registering. Your reference is %s.\nRace category: %s\n\nWe will be in touch with further details.\n\nMNM Foundation", $d['full_name'], $ref, $d['race'] );
		wp_mail( $d['email'], $subject, $body );
		wp_mail( mnm_notification_email(), 'New marathon registration ' . $ref, $d['full_name'] . ' / ' . $d['race'] );
	}

	mnm_form_respond(
		true,
		array(
			'message' => __( 'Registration received.', 'mnm-foundation-core' ),
			'ref'     => $ref,
			'name'    => strtok( $d['full_name'], ' ' ),
			'count'   => mnm_reg_count(),
		),
		'register'
	);
}
add_action( 'admin_post_nopriv_mnm_register', 'mnm_handle_registration' );
add_action( 'admin_post_mnm_register', 'mnm_handle_registration' );

/**
 * Handle contact / volunteer form: emails the foundation (suppressed in demo mode).
 */
function mnm_handle_contact() {
	$sec = mnm_verify_form_security( 'mnm_contact' );
	if ( is_wp_error( $sec ) ) {
		mnm_form_respond( false, array( 'message' => $sec->get_error_message(), 'errors' => array() ), 'contact-form' );
	}
	$name  = isset( $_POST['c_name'] ) ? sanitize_text_field( wp_unslash( $_POST['c_name'] ) ) : '';
	$email = isset( $_POST['c_email'] ) ? sanitize_email( wp_unslash( $_POST['c_email'] ) ) : '';
	$topic = isset( $_POST['c_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['c_topic'] ) ) : '';
	$msg   = isset( $_POST['c_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['c_message'] ) ) : '';
	$err   = array();
	if ( strlen( $name ) < 2 ) {
		$err['c_name'] = __( 'Please enter your name.', 'mnm-foundation-core' );
	}
	if ( ! is_email( $email ) ) {
		$err['c_email'] = __( 'Please enter a valid email address.', 'mnm-foundation-core' );
	}
	if ( strlen( $msg ) < 5 ) {
		$err['c_message'] = __( 'Please write a short message.', 'mnm-foundation-core' );
	}
	if ( $err ) {
		mnm_form_respond( false, array( 'message' => __( 'Please correct the highlighted fields.', 'mnm-foundation-core' ), 'errors' => $err ), 'contact-form' );
	}
	if ( ! mnm_is_demo_mode() ) {
		wp_mail( mnm_notification_email(), '[MNM website] ' . ( $topic ? $topic : 'Message' ) . ' from ' . $name, $msg . "\n\n" . $name . ' <' . $email . '>', array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	}
	mnm_form_respond( true, array( 'message' => __( 'Thank you. Your message has been received.', 'mnm-foundation-core' ) ), 'contact-form' );
}
add_action( 'admin_post_nopriv_mnm_contact', 'mnm_handle_contact' );
add_action( 'admin_post_mnm_contact', 'mnm_handle_contact' );

/* ---------------------------------------------------------------------------
 * Admin: list columns, filters, CSV export.
 * ------------------------------------------------------------------------- */

/**
 * Columns for the registrations list.
 *
 * @param array $cols Columns.
 * @return array
 */
function mnm_reg_columns( $cols ) {
	return array(
		'cb'        => $cols['cb'],
		'title'     => __( 'Reference', 'mnm-foundation-core' ),
		'mnm_name'  => __( 'Participant', 'mnm-foundation-core' ),
		'mnm_race'  => __( 'Race', 'mnm-foundation-core' ),
		'mnm_shirt' => __( 'T-shirt', 'mnm-foundation-core' ),
		'mnm_loc'   => __( 'Location', 'mnm-foundation-core' ),
		'mnm_state' => __( 'Status', 'mnm-foundation-core' ),
		'date'      => __( 'Registered', 'mnm-foundation-core' ),
	);
}
add_filter( 'manage_mnm_registration_posts_columns', 'mnm_reg_columns' );

/**
 * Render registration columns.
 *
 * @param string $col  Column.
 * @param int    $id   Post ID.
 */
function mnm_reg_column_content( $col, $id ) {
	switch ( $col ) {
		case 'mnm_name':
			printf(
				'<strong>%1$s</strong>%2$s<br /><a href="mailto:%3$s">%3$s</a><br />%4$s',
				esc_html( get_post_meta( $id, '_mnm_name', true ) ),
				get_post_meta( $id, '_mnm_demo', true ) ? ' <span class="mnm-pill mnm-pill--demo">demo</span>' : '',
				esc_attr( get_post_meta( $id, '_mnm_email', true ) ),
				esc_html( get_post_meta( $id, '_mnm_phone', true ) )
			);
			break;
		case 'mnm_race':
			echo esc_html( get_post_meta( $id, '_mnm_race', true ) );
			$age = get_post_meta( $id, '_mnm_age', true );
			if ( $age ) {
				echo '<br /><span class="description">' . esc_html( sprintf( /* translators: %d: age. */ __( 'Age %d', 'mnm-foundation-core' ), $age ) ) . '</span>';
			}
			break;
		case 'mnm_shirt':
			echo esc_html( get_post_meta( $id, '_mnm_tshirt', true ) );
			break;
		case 'mnm_loc':
			echo esc_html( get_post_meta( $id, '_mnm_location', true ) );
			break;
		case 'mnm_state':
			$s = get_post_meta( $id, '_mnm_reg_status', true );
			$s = $s ? $s : 'pending';
			printf( '<span class="mnm-pill mnm-pill--%1$s">%2$s</span>', esc_attr( $s ), esc_html( ucfirst( $s ) ) );
			break;
	}
}
add_action( 'manage_mnm_registration_posts_custom_column', 'mnm_reg_column_content', 10, 2 );

/**
 * Remove "Edit"/"Quick edit" noise: registrations are viewed, not authored.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function mnm_reg_row_actions( $actions, $post ) {
	if ( 'mnm_registration' === $post->post_type ) {
		unset( $actions['inline hide-if-no-js'] );
		$s = get_post_meta( $post->ID, '_mnm_reg_status', true );
		if ( 'confirmed' !== $s && current_user_can( 'edit_post', $post->ID ) ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=mnm_reg_confirm&id=' . $post->ID ), 'mnm_reg_confirm_' . $post->ID );
			$actions['mnm_confirm'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Mark confirmed', 'mnm-foundation-core' ) . '</a>';
		}
	}
	return $actions;
}
add_filter( 'post_row_actions', 'mnm_reg_row_actions', 10, 2 );

/**
 * Mark a registration confirmed.
 */
function mnm_reg_confirm() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	check_admin_referer( 'mnm_reg_confirm_' . $id );
	if ( $id && current_user_can( 'edit_post', $id ) && 'mnm_registration' === get_post_type( $id ) ) {
		update_post_meta( $id, '_mnm_reg_status', 'confirmed' );
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=mnm_registration' ) );
	exit;
}
add_action( 'admin_post_mnm_reg_confirm', 'mnm_reg_confirm' );

/**
 * Race filter dropdown and CSV export button above the list.
 *
 * @param string $post_type Post type.
 */
function mnm_reg_filters( $post_type ) {
	if ( 'mnm_registration' !== $post_type ) {
		return;
	}
	$cur = isset( $_GET['mnm_race'] ) ? sanitize_text_field( wp_unslash( $_GET['mnm_race'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<select name="mnm_race"><option value="">' . esc_html__( 'All race categories', 'mnm-foundation-core' ) . '</option>';
	foreach ( array_keys( mnm_reg_races() ) as $r ) {
		printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $r ), selected( $cur, $r, false ) );
	}
	echo '</select>';
	$st = isset( $_GET['mnm_state'] ) ? sanitize_text_field( wp_unslash( $_GET['mnm_state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<select name="mnm_state"><option value="">' . esc_html__( 'All statuses', 'mnm-foundation-core' ) . '</option>';
	foreach ( array( 'pending', 'confirmed' ) as $s ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $s ), selected( $st, $s, false ), esc_html( ucfirst( $s ) ) );
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'mnm_reg_filters' );

/**
 * Apply filters to the registrations query.
 *
 * @param WP_Query $q Query.
 */
function mnm_reg_filter_query( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'mnm_registration' !== $q->get( 'post_type' ) ) {
		return;
	}
	$meta = array();
	if ( ! empty( $_GET['mnm_race'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta[] = array( 'key' => '_mnm_race', 'value' => sanitize_text_field( wp_unslash( $_GET['mnm_race'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	if ( ! empty( $_GET['mnm_state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$meta[] = array( 'key' => '_mnm_reg_status', 'value' => sanitize_text_field( wp_unslash( $_GET['mnm_state'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	if ( $meta ) {
		$q->set( 'meta_query', $meta );
	}
}
add_action( 'pre_get_posts', 'mnm_reg_filter_query' );

/**
 * CSV export (admins/editors only).
 */
function mnm_reg_export_csv() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to export registrations.', 'mnm-foundation-core' ), 403 );
	}
	check_admin_referer( 'mnm_export_registrations' );

	$ids = get_posts(
		array(
			'post_type'      => 'mnm_registration',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'ASC',
		)
	);
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=mnm-registrations-' . gmdate( 'Ymd-His' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM for Excel.
	$cols = array( 'Reference', 'Full name', 'Email', 'Phone', 'Date of birth', 'Age', 'Location', 'Race', 'T-shirt', 'Emergency contact', 'Emergency phone', 'Status', 'Demo', 'Registered' );
	fputcsv( $out, $cols );
	$safe = function ( $v ) {
		$v = (string) $v;
		return ( '' !== $v && in_array( $v[0], array( '=', '+', '-', '@' ), true ) ) ? "'" . $v : $v; // Spreadsheet formula injection guard.
	};
	foreach ( $ids as $id ) {
		$m = function ( $k ) use ( $id, $safe ) {
			return $safe( get_post_meta( $id, $k, true ) );
		};
		fputcsv(
			$out,
			array(
				$m( '_mnm_ref' ), $m( '_mnm_name' ), $m( '_mnm_email' ), $m( '_mnm_phone' ), $m( '_mnm_dob' ), $m( '_mnm_age' ), $m( '_mnm_location' ),
				$m( '_mnm_race' ), $m( '_mnm_tshirt' ), $m( '_mnm_emergency_name' ), $m( '_mnm_emergency_phone' ),
				$m( '_mnm_reg_status' ) ? $m( '_mnm_reg_status' ) : 'pending', get_post_meta( $id, '_mnm_demo', true ) ? 'yes' : 'no', get_the_date( 'Y-m-d H:i', $id ),
			)
		);
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'admin_post_mnm_export_registrations', 'mnm_reg_export_csv' );

/**
 * Statistics for the admin overview.
 *
 * @return array
 */
function mnm_reg_stats() {
	global $wpdb;
	$by = function ( $key ) use ( $wpdb ) {
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS label, COUNT(*) AS n FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type = 'mnm_registration' AND p.post_status = 'publish' GROUP BY pm.meta_value ORDER BY n DESC",
				$key
			),
			ARRAY_A
		);
	};
	$confirmed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_mnm_reg_status' AND pm.meta_value = 'confirmed' AND p.post_type = 'mnm_registration' AND p.post_status = 'publish'" ); // phpcs:ignore WordPress.DB.PreparedSQL
	return array(
		'total'     => mnm_reg_count(),
		'confirmed' => $confirmed,
		'by_race'   => $by( '_mnm_race' ),
		'by_shirt'  => $by( '_mnm_tshirt' ),
	);
}

/**
 * Add "Export CSV" next to the status links above the registrations table.
 *
 * @param array $views Views.
 * @return array
 */
function mnm_reg_views( $views ) {
	$url              = wp_nonce_url( admin_url( 'admin-post.php?action=mnm_export_registrations' ), 'mnm_export_registrations' );
	$views['mnm_csv'] = '<a href="' . esc_url( $url ) . '" class="button button-small" style="margin-left:12px">' . esc_html__( 'Export all to CSV', 'mnm-foundation-core' ) . '</a>';
	return $views;
}
add_filter( 'views_edit-mnm_registration', 'mnm_reg_views' );
