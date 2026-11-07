<?php
/**
 * Admin experience: Foundation menu, overview dashboard, settings page, gallery collections.
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Menu
 * ------------------------------------------------------------------------- */

/**
 * Register the top-level menu and custom screens.
 */
function mnm_admin_menu() {
	add_menu_page( __( 'Foundation', 'mnm-foundation-core' ), __( 'Foundation', 'mnm-foundation-core' ), 'edit_posts', 'mnm-foundation', 'mnm_render_overview', 'dashicons-heart', 3 );
	add_submenu_page( 'mnm-foundation', __( 'Overview', 'mnm-foundation-core' ), __( 'Overview', 'mnm-foundation-core' ), 'edit_posts', 'mnm-foundation', 'mnm_render_overview' );
	add_submenu_page( 'mnm-foundation', __( 'Gallery Photos', 'mnm-foundation-core' ), __( 'Gallery Photos', 'mnm-foundation-core' ), 'upload_files', 'upload.php?mode=list' );
	add_submenu_page( 'mnm-foundation', __( 'Gallery Collections', 'mnm-foundation-core' ), __( 'Gallery Collections', 'mnm-foundation-core' ), 'upload_files', 'edit-tags.php?taxonomy=mnm_gallery_cat&post_type=attachment' );
	add_submenu_page( 'mnm-foundation', __( 'Website Settings', 'mnm-foundation-core' ), __( 'Website Settings', 'mnm-foundation-core' ), 'manage_options', 'mnm-settings', 'mnm_render_settings' );
	add_submenu_page( 'mnm-foundation', __( 'Demo Content', 'mnm-foundation-core' ), __( 'Demo Content', 'mnm-foundation-core' ), 'manage_options', 'mnm-demo', 'mnm_render_demo_page' );
}
add_action( 'admin_menu', 'mnm_admin_menu', 5 );

/**
 * Put the Foundation submenu in a sensible order.
 */
function mnm_reorder_submenu() {
	global $submenu;
	if ( empty( $submenu['mnm-foundation'] ) ) {
		return;
	}
	$order = array(
		'mnm-foundation',
		'edit.php?post_type=mnm_programme',
		'edit.php?post_type=mnm_project',
		'edit.php?post_type=mnm_event',
		'edit.php?post_type=mnm_article',
		'upload.php?mode=list',
		'edit-tags.php?taxonomy=mnm_gallery_cat&post_type=attachment',
		'edit.php?post_type=mnm_update',
		'edit.php?post_type=mnm_campaign',
		'edit.php?post_type=mnm_registration',
		'mnm-settings',
		'mnm-demo',
	);
	$sorted = array();
	foreach ( $order as $slug ) {
		foreach ( $submenu['mnm-foundation'] as $k => $item ) {
			if ( $item[2] === $slug ) {
				$sorted[] = $item;
				unset( $submenu['mnm-foundation'][ $k ] );
			}
		}
	}
	$submenu['mnm-foundation'] = array_merge( $sorted, array_values( $submenu['mnm-foundation'] ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
}
add_action( 'admin_menu', 'mnm_reorder_submenu', 999 );

/**
 * Keep the Foundation menu highlighted on gallery-collection screens.
 *
 * @param string $parent Parent file.
 * @return string
 */
function mnm_menu_highlight( $parent ) {
	global $current_screen;
	if ( $current_screen && 'mnm_gallery_cat' === $current_screen->taxonomy ) {
		return 'mnm-foundation';
	}
	return $parent;
}
add_filter( 'parent_file', 'mnm_menu_highlight' );

/* ---------------------------------------------------------------------------
 * Assets
 * ------------------------------------------------------------------------- */

/**
 * Admin CSS/JS.
 */
function mnm_admin_assets() {
	wp_enqueue_style( 'mnm-admin', MNM_CORE_URL . 'assets/css/admin.css', array(), MNM_CORE_VERSION );
	wp_enqueue_media();
	wp_enqueue_script( 'mnm-admin', MNM_CORE_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), MNM_CORE_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'mnm_admin_assets' );

/**
 * Branded login screen.
 */
function mnm_login_branding() {
	$logo = get_template_directory_uri() . '/assets/img/logo.svg';
	if ( 'mnm-foundation-theme' !== get_template() ) {
		return;
	}
	echo '<style>.login h1 a{background-image:url(' . esc_url( $logo ) . ');background-size:contain;background-position:center;width:280px;height:98px}body.login{background:#f5f7f8}.login #nav a,.login #backtoblog a{color:#20252b}.wp-core-ui .button-primary{background:#2196d2;border-color:#1778ab}</style>';
}
add_action( 'login_enqueue_scripts', 'mnm_login_branding' );
add_filter( 'login_headerurl', 'home_url' );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );

/**
 * Footer text in admin.
 *
 * @return string
 */
function mnm_admin_footer() {
	return esc_html__( 'MNM Foundation website prototype. Powered by WordPress.', 'mnm-foundation-core' );
}
add_filter( 'admin_footer_text', 'mnm_admin_footer' );

/* ---------------------------------------------------------------------------
 * Overview
 * ------------------------------------------------------------------------- */

/**
 * Count published posts of a type.
 *
 * @param string $type Post type.
 * @return int
 */
function mnm_count( $type ) {
	$c = wp_count_posts( $type );
	return isset( $c->publish ) ? (int) $c->publish : 0;
}

/**
 * Overview dashboard.
 */
function mnm_render_overview() {
	$stats    = mnm_reg_stats();
	$upcoming = mnm_events_query( 'upcoming', 3 );
	$camp     = (int) mnm_setting( 'marathon_campaign', 0 );
	$campaigns = get_posts( array( 'post_type' => 'mnm_campaign', 'posts_per_page' => 5, 'post_status' => 'publish' ) );
	$export   = wp_nonce_url( admin_url( 'admin-post.php?action=mnm_export_registrations' ), 'mnm_export_registrations' );
	$max      = 1;
	foreach ( $stats['by_race'] as $r ) {
		$max = max( $max, (int) $r['n'] );
	}
	?>
	<div class="wrap mnm-overview">
		<h1><?php esc_html_e( 'MNM Foundation', 'mnm-foundation-core' ); ?></h1>
		<p class="mnm-lead"><?php esc_html_e( 'Welcome. Everything you need to run the website is on this menu. Pick a card below to get started.', 'mnm-foundation-core' ); ?>
			<?php echo mnm_is_demo_mode() ? mnm_demo_badge( __( 'Prototype with demonstration data', 'mnm-foundation-core' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>

		<div class="mnm-cards">
			<a class="mnm-card mnm-card--blue" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mnm_registration' ) ); ?>">
				<span class="mnm-card__num"><?php echo (int) $stats['total']; ?></span>
				<span class="mnm-card__label"><?php esc_html_e( 'Marathon registrations', 'mnm-foundation-core' ); ?></span>
				<span class="mnm-card__sub"><?php echo esc_html( sprintf( /* translators: %d: count. */ __( '%d confirmed', 'mnm-foundation-core' ), $stats['confirmed'] ) ); ?></span>
			</a>
			<a class="mnm-card mnm-card--green" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mnm_project' ) ); ?>">
				<span class="mnm-card__num"><?php echo (int) mnm_count( 'mnm_project' ); ?></span>
				<span class="mnm-card__label"><?php esc_html_e( 'Projects published', 'mnm-foundation-core' ); ?></span>
				<span class="mnm-card__sub"><?php echo esc_html( sprintf( /* translators: %d: count. */ __( '%d programmes', 'mnm-foundation-core' ), mnm_count( 'mnm_programme' ) ) ); ?></span>
			</a>
			<a class="mnm-card mnm-card--yellow" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mnm_event' ) ); ?>">
				<span class="mnm-card__num"><?php echo (int) $upcoming->found_posts; ?></span>
				<span class="mnm-card__label"><?php esc_html_e( 'Upcoming events', 'mnm-foundation-core' ); ?></span>
				<span class="mnm-card__sub"><?php echo esc_html( sprintf( /* translators: %d: count. */ __( '%d articles published', 'mnm-foundation-core' ), mnm_count( 'mnm_article' ) ) ); ?></span>
			</a>
			<a class="mnm-card mnm-card--ink" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mnm_campaign' ) ); ?>">
				<span class="mnm-card__num"><?php echo (int) mnm_count( 'mnm_campaign' ); ?></span>
				<span class="mnm-card__label"><?php esc_html_e( 'Fundraising campaigns', 'mnm-foundation-core' ); ?></span>
				<span class="mnm-card__sub"><?php esc_html_e( 'Amounts are entered manually', 'mnm-foundation-core' ); ?></span>
			</a>
		</div>

		<div class="mnm-cols">
			<section class="mnm-panel">
				<h2><?php esc_html_e( 'Registrations by race', 'mnm-foundation-core' ); ?></h2>
				<?php if ( $stats['by_race'] ) : ?>
					<ul class="mnm-bars">
						<?php foreach ( $stats['by_race'] as $r ) : ?>
							<li><span><?php echo esc_html( $r['label'] ); ?></span><i style="width:<?php echo esc_attr( round( $r['n'] / $max * 100 ) ); ?>%"></i><b><?php echo (int) $r['n']; ?></b></li>
						<?php endforeach; ?>
					</ul>
					<h3><?php esc_html_e( 'T-shirt sizes', 'mnm-foundation-core' ); ?></h3>
					<p class="mnm-chips">
						<?php foreach ( $stats['by_shirt'] as $r ) : ?>
							<span><?php echo esc_html( $r['label'] ); ?> <b><?php echo (int) $r['n']; ?></b></span>
						<?php endforeach; ?>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'No registrations yet.', 'mnm-foundation-core' ); ?></p>
				<?php endif; ?>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mnm_registration' ) ); ?>"><?php esc_html_e( 'View registrations', 'mnm-foundation-core' ); ?></a>
					<a class="button" href="<?php echo esc_url( $export ); ?>"><?php esc_html_e( 'Export CSV', 'mnm-foundation-core' ); ?></a>
				</p>
			</section>

			<section class="mnm-panel">
				<h2><?php esc_html_e( 'Fundraising progress', 'mnm-foundation-core' ); ?></h2>
				<?php foreach ( $campaigns as $c ) : $d = mnm_campaign_data( $c->ID ); ?>
					<div class="mnm-mini">
						<div><strong><a href="<?php echo esc_url( get_edit_post_link( $c->ID ) ); ?>"><?php echo esc_html( $d['title'] ); ?></a></strong>
							<span><?php echo esc_html( mnm_format_money( $d['raised'], $d['symbol'] ) . ' / ' . mnm_format_money( $d['target'], $d['symbol'] ) ); ?></span></div>
						<div class="mnm-mini__bar"><i style="width:<?php echo esc_attr( $d['percent'] ); ?>%"></i></div>
					</div>
				<?php endforeach; ?>
				<?php if ( ! $campaigns ) : ?><p><?php esc_html_e( 'No campaigns yet.', 'mnm-foundation-core' ); ?></p><?php endif; ?>
				<p class="description"><?php esc_html_e( 'To update an amount, open the campaign and edit "Amount raised (manually verified)". Online payments are not connected yet.', 'mnm-foundation-core' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mnm_campaign' ) ); ?>"><?php esc_html_e( 'Manage campaigns', 'mnm-foundation-core' ); ?></a></p>
			</section>
		</div>

		<section class="mnm-panel mnm-panel--wide">
			<h2><?php esc_html_e( 'What would you like to do?', 'mnm-foundation-core' ); ?></h2>
			<ul class="mnm-tasks">
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mnm_article' ) ); ?>"><span class="dashicons dashicons-edit"></span><?php esc_html_e( 'Write a news article', 'mnm-foundation-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mnm_event' ) ); ?>"><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e( 'Add an event', 'mnm-foundation-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mnm_project' ) ); ?>"><span class="dashicons dashicons-portfolio"></span><?php esc_html_e( 'Add a project', 'mnm-foundation-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'media-new.php' ) ); ?>"><span class="dashicons dashicons-format-gallery"></span><?php esc_html_e( 'Upload gallery photos', 'mnm-foundation-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mnm_update' ) ); ?>"><span class="dashicons dashicons-megaphone"></span><?php esc_html_e( 'Post a marathon update', 'mnm-foundation-core' ); ?></a></li>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=mnm-settings' ) ); ?>"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e( 'Edit contact details &amp; homepage', 'mnm-foundation-core' ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><span class="dashicons dashicons-admin-page"></span><?php esc_html_e( 'Edit pages', 'mnm-foundation-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'View the website', 'mnm-foundation-core' ); ?></a></li>
			</ul>
		</section>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * Settings
 * ------------------------------------------------------------------------- */

/**
 * Settings sections and fields.
 *
 * @return array
 */
function mnm_settings_schema() {
	$t = function ( $label, $type = 'text', $extra = array() ) {
		return array_merge( array( 'label' => $label, 'type' => $type ), $extra );
	};
	return array(
		'contact'   => array(
			'title'  => __( 'Contact details', 'mnm-foundation-core' ),
			'fields' => array(
				'tagline'            => $t( __( 'Tagline', 'mnm-foundation-core' ) ),
				'address'            => $t( __( 'Address', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 3 ) ),
				'phone'              => $t( __( 'Phone', 'mnm-foundation-core' ) ),
				'email'              => $t( __( 'Public email', 'mnm-foundation-core' ), 'text', array( 'desc' => __( 'Leave empty to hide the email line on the website.', 'mnm-foundation-core' ) ) ),
				'notification_email' => $t( __( 'Send form notifications to', 'mnm-foundation-core' ), 'text', array( 'desc' => __( 'Used once demonstration mode is switched off.', 'mnm-foundation-core' ) ) ),
				'map_embed'          => $t( __( 'Map embed address', 'mnm-foundation-core' ), 'url', array( 'desc' => __( 'Paste the "src" address from a Google Maps embed. Leave empty to show a map placeholder.', 'mnm-foundation-core' ) ) ),
			),
		),
		'social'    => array(
			'title'  => __( 'Social media', 'mnm-foundation-core' ),
			'fields' => array(
				'facebook'  => $t( 'Facebook', 'url' ),
				'instagram' => $t( 'Instagram', 'url' ),
				'youtube'   => $t( 'YouTube', 'url' ),
				'x_twitter' => $t( 'X (Twitter)', 'url' ),
			),
		),
		'home'      => array(
			'title'  => __( 'Homepage', 'mnm-foundation-core' ),
			'fields' => array(
				'hero_heading'      => $t( __( 'Hero heading', 'mnm-foundation-core' ) ),
				'hero_text'         => $t( __( 'Hero text', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 3 ) ),
				'hero_image'        => $t( __( 'Hero photograph', 'mnm-foundation-core' ), 'image', array( 'desc' => __( 'Optional. Leave empty to show the animated sunrise-over-the-hills artwork. A wide landscape photo (1600px+) replaces it.', 'mnm-foundation-core' ) ) ),
				'hero_video'        => $t( __( 'Hero video (optional)', 'mnm-foundation-core' ), 'url', array( 'desc' => __( 'Address of an MP4 file from your Media Library. Plays muted on a loop behind the headline (the photograph is used as the poster).', 'mnm-foundation-core' ) ) ),
				'announcement_on'   => $t( __( 'Announcement bar', 'mnm-foundation-core' ), 'checkbox', array( 'text' => __( 'Show the announcement bar at the top of every page', 'mnm-foundation-core' ) ) ),
				'announcement_text' => $t( __( 'Announcement text', 'mnm-foundation-core' ) ),
				'announcement_link' => $t( __( 'Announcement link', 'mnm-foundation-core' ), 'url' ),
				'statement'         => $t( __( 'Big statement', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 2, 'desc' => __( 'One or two short sentences shown in large type on the homepage.', 'mnm-foundation-core' ) ) ),
				'intro'             => $t( __( 'Introduction', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 4 ) ),
				'mission'           => $t( __( 'Mission', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 3, 'desc' => __( 'One point per line.', 'mnm-foundation-core' ) ) ),
				'vision'            => $t( __( 'Vision', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 3 ) ),
				'dream'             => $t( __( 'Long-term dream', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 3 ) ),
				'values'            => $t( __( 'Core values', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 6, 'desc' => __( 'One per line as: Title|Short description', 'mnm-foundation-core' ) ) ),
			),
		),
		'stats'     => array(
			'title'  => __( 'Impact statistics', 'mnm-foundation-core' ),
			'intro'  => __( 'Shown on the homepage. Only enter figures you can stand behind.', 'mnm-foundation-core' ),
			'fields' => array(
				'stat_1_value' => $t( __( 'Figure 1', 'mnm-foundation-core' ), 'text', array( 'placeholder' => '250+' ) ),
				'stat_1_label' => $t( __( 'Label 1', 'mnm-foundation-core' ) ),
				'stat_2_value' => $t( __( 'Figure 2', 'mnm-foundation-core' ) ),
				'stat_2_label' => $t( __( 'Label 2', 'mnm-foundation-core' ) ),
				'stat_3_value' => $t( __( 'Figure 3', 'mnm-foundation-core' ) ),
				'stat_3_label' => $t( __( 'Label 3', 'mnm-foundation-core' ) ),
				'stat_4_value' => $t( __( 'Figure 4', 'mnm-foundation-core' ) ),
				'stat_4_label' => $t( __( 'Label 4', 'mnm-foundation-core' ) ),
				'stats_sample' => $t( __( 'Sample figures', 'mnm-foundation-core' ), 'checkbox', array( 'text' => __( 'These figures are samples. Show a "Sample figures" label.', 'mnm-foundation-core' ) ) ),
			),
		),
		'marathon'  => array(
			'title'  => __( 'Marathon', 'mnm-foundation-core' ),
			'fields' => array(
				'marathon_title'      => $t( __( 'Event name', 'mnm-foundation-core' ) ),
				'marathon_tagline'    => $t( __( 'Tagline', 'mnm-foundation-core' ) ),
				'marathon_status'     => $t( __( 'Registration', 'mnm-foundation-core' ), 'select', array( 'options' => array( 'open' => __( 'Open', 'mnm-foundation-core' ), 'closed' => __( 'Closed', 'mnm-foundation-core' ) ) ) ),
				'marathon_date_text'  => $t( __( 'Date', 'mnm-foundation-core' ), 'text', array( 'desc' => __( 'Leave empty to show "Date to be announced".', 'mnm-foundation-core' ) ) ),
				'marathon_venue_text' => $t( __( 'Venue', 'mnm-foundation-core' ), 'text', array( 'desc' => __( 'Leave empty to show "Venue to be announced".', 'mnm-foundation-core' ) ) ),
				'marathon_text'       => $t( __( 'Description', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 4 ) ),
				'marathon_banner'     => $t( __( 'Banner image', 'mnm-foundation-core' ), 'image' ),
				'marathon_categories' => $t( __( 'Race categories', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 4, 'desc' => __( 'One per line as: Name|Short description. These are placeholders until confirmed.', 'mnm-foundation-core' ) ) ),
				'marathon_tshirts'    => $t( __( 'T-shirt sizes', 'mnm-foundation-core' ), 'text', array( 'desc' => __( 'Comma separated.', 'mnm-foundation-core' ) ) ),
				'marathon_campaign'   => $t( __( 'Fundraising campaign', 'mnm-foundation-core' ), 'post_select', array( 'post_type' => 'mnm_campaign', 'desc' => __( 'Its progress bar is shown on the marathon page.', 'mnm-foundation-core' ) ) ),
			),
		),
		'footer'    => array(
			'title'  => __( 'Footer and donations', 'mnm-foundation-core' ),
			'fields' => array(
				'footer_text' => $t( __( 'Footer text', 'mnm-foundation-core' ), 'textarea', array( 'rows' => 3 ) ),
				'footer_note' => $t( __( 'Footer note', 'mnm-foundation-core' ), 'text', array( 'desc' => __( 'Small print line, e.g. registration details.', 'mnm-foundation-core' ) ) ),
				'donate_url'  => $t( __( 'Donate button address', 'mnm-foundation-core' ), 'url', array( 'desc' => __( 'Leave empty to use the "Get Involved" page. Online payments will be added later.', 'mnm-foundation-core' ) ) ),
			),
		),
		'prototype' => array(
			'title'  => __( 'Prototype mode', 'mnm-foundation-core' ),
			'fields' => array(
				'demo_mode' => $t( __( 'Demonstration mode', 'mnm-foundation-core' ), 'checkbox', array( 'text' => __( 'Show "Demonstration data" labels and do not send emails. Switch off before launch.', 'mnm-foundation-core' ) ) ),
			),
		),
	);
}

/**
 * Register the option.
 */
function mnm_register_settings() {
	register_setting( 'mnm_settings_group', 'mnm_settings', array( 'sanitize_callback' => 'mnm_sanitize_settings' ) );
}
add_action( 'admin_init', 'mnm_register_settings' );

/**
 * Sanitise all settings.
 *
 * @param array $in Raw input.
 * @return array
 */
function mnm_sanitize_settings( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$out = get_option( 'mnm_settings', array() );
	$out = is_array( $out ) ? $out : array();
	foreach ( mnm_settings_schema() as $section ) {
		foreach ( $section['fields'] as $key => $def ) {
			$raw = isset( $in[ $key ] ) ? $in[ $key ] : '';
			if ( 'checkbox' === $def['type'] ) {
				$out[ $key ] = $raw ? '1' : '0';
			} else {
				$out[ $key ] = mnm_sanitize_field( $raw, $def );
			}
		}
	}
	if ( isset( $in['memorials'] ) && is_array( $in['memorials'] ) ) {
		$m = array();
		foreach ( $in['memorials'] as $row ) {
			if ( empty( $row['name'] ) ) {
				continue;
			}
			$m[] = array(
				'name'    => sanitize_text_field( $row['name'] ),
				'village' => isset( $row['village'] ) ? sanitize_text_field( $row['village'] ) : '',
				'years'   => isset( $row['years'] ) ? sanitize_text_field( $row['years'] ) : '',
				'photo'   => isset( $row['photo'] ) ? absint( $row['photo'] ) : 0,
			);
		}
		$out['memorials'] = $m;
	}
	return $out;
}

/**
 * Let editors-in-charge (manage_options) save via options.php.
 */
function mnm_settings_page_cap() {
	return 'manage_options';
}
add_filter( 'option_page_capability_mnm_settings_group', 'mnm_settings_page_cap' );

/**
 * Settings screen.
 */
function mnm_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$schema = mnm_settings_schema();
	$s      = mnm_settings();
	?>
	<div class="wrap mnm-settings">
		<h1><?php esc_html_e( 'Website Settings', 'mnm-foundation-core' ); ?></h1>
		<p class="mnm-lead"><?php esc_html_e( 'Change contact details, homepage text, the marathon and more. Click "Save changes" at the bottom when you are done.', 'mnm-foundation-core' ); ?></p>
		<?php settings_errors(); ?>
		<nav class="mnm-jump" aria-label="<?php esc_attr_e( 'Sections', 'mnm-foundation-core' ); ?>">
			<?php foreach ( $schema as $id => $sec ) : ?>
				<a href="#sec-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $sec['title'] ); ?></a>
			<?php endforeach; ?>
			<a href="#sec-memorial"><?php esc_html_e( 'Memorial', 'mnm-foundation-core' ); ?></a>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'mnm_settings_group' ); ?>
			<?php foreach ( $schema as $id => $sec ) : ?>
				<section class="mnm-panel" id="sec-<?php echo esc_attr( $id ); ?>">
					<h2><?php echo esc_html( $sec['title'] ); ?></h2>
					<?php if ( ! empty( $sec['intro'] ) ) : ?><p class="description"><?php echo esc_html( $sec['intro'] ); ?></p><?php endif; ?>
					<table class="form-table" role="presentation"><tbody>
						<?php foreach ( $sec['fields'] as $key => $def ) : ?>
							<tr>
								<th scope="row"><label for="s-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $def['label'] ); ?></label></th>
								<td><?php mnm_render_field( 'mnm_settings[' . $key . ']', isset( $s[ $key ] ) ? $s[ $key ] : '', $def, 's-' . $key ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody></table>
				</section>
			<?php endforeach; ?>

			<section class="mnm-panel" id="sec-memorial">
				<h2><?php esc_html_e( 'Memorial', 'mnm-foundation-core' ); ?></h2>
				<p class="description"><?php esc_html_e( 'The people the foundation is named in memory of. Shown on the About page.', 'mnm-foundation-core' ); ?></p>
				<?php
				$mem = mnm_get_memorials();
				for ( $i = 0; $i < 3; $i++ ) :
					$row = isset( $mem[ $i ] ) ? $mem[ $i ] : array( 'name' => '', 'village' => '', 'years' => '', 'photo' => 0 );
					?>
					<fieldset class="mnm-memrow">
						<legend><?php echo esc_html( sprintf( /* translators: %d: number. */ __( 'Person %d', 'mnm-foundation-core' ), $i + 1 ) ); ?></legend>
						<p><label><?php esc_html_e( 'Name', 'mnm-foundation-core' ); ?> <input type="text" class="regular-text" name="mnm_settings[memorials][<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $row['name'] ); ?>" /></label></p>
						<p><label><?php esc_html_e( 'Village', 'mnm-foundation-core' ); ?> <input type="text" class="regular-text" name="mnm_settings[memorials][<?php echo (int) $i; ?>][village]" value="<?php echo esc_attr( $row['village'] ); ?>" /></label></p>
						<p><label><?php esc_html_e( 'Years', 'mnm-foundation-core' ); ?> <input type="text" class="regular-text" name="mnm_settings[memorials][<?php echo (int) $i; ?>][years]" value="<?php echo esc_attr( $row['years'] ); ?>" placeholder="1984 – 2009" /></label></p>
						<div><?php mnm_render_field( 'mnm_settings[memorials][' . $i . '][photo]', $row['photo'], array( 'type' => 'image' ), 'mem-photo-' . $i ); ?></div>
					</fieldset>
				<?php endfor; ?>
			</section>
			<?php submit_button( __( 'Save changes', 'mnm-foundation-core' ) ); ?>
		</form>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * Gallery collections on Media Library items
 * ------------------------------------------------------------------------- */

/**
 * Add a "Gallery collection" dropdown and caption hint to media details.
 *
 * @param array   $fields Fields.
 * @param WP_Post $post   Attachment.
 * @return array
 */
function mnm_attachment_fields( $fields, $post ) {
	if ( ! wp_attachment_is_image( $post ) ) {
		return $fields;
	}
	$terms   = get_terms( array( 'taxonomy' => 'mnm_gallery_cat', 'hide_empty' => false ) );
	$current = wp_get_object_terms( $post->ID, 'mnm_gallery_cat', array( 'fields' => 'ids' ) );
	$cur     = ! is_wp_error( $current ) && $current ? (int) $current[0] : 0;
	$html    = '<select name="attachments[' . (int) $post->ID . '][mnm_collection]"><option value="0">' . esc_html__( 'Not in gallery', 'mnm-foundation-core' ) . '</option>';
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$html .= '<option value="' . (int) $t->term_id . '"' . selected( $cur, $t->term_id, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
	}
	$html .= '</select>';
	$fields['mnm_collection'] = array(
		'label' => __( 'Gallery collection', 'mnm-foundation-core' ),
		'input' => 'html',
		'html'  => $html,
		'helps' => __( 'Choose a collection to show this photo in the public gallery. The Caption above is shown under the photo.', 'mnm-foundation-core' ),
	);
	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'mnm_attachment_fields', 10, 2 );

/**
 * Save the collection choice.
 *
 * @param array $post       Attachment post array.
 * @param array $attachment Submitted fields.
 * @return array
 */
function mnm_attachment_save( $post, $attachment ) {
	if ( isset( $attachment['mnm_collection'] ) && current_user_can( 'edit_post', $post['ID'] ) ) {
		$term = absint( $attachment['mnm_collection'] );
		wp_set_object_terms( $post['ID'], $term ? array( $term ) : array(), 'mnm_gallery_cat' );
	}
	return $post;
}
add_filter( 'attachment_fields_to_save', 'mnm_attachment_save', 10, 2 );

/**
 * Campaigns use the classic editor so the verified-amount fields are the first thing staff see.
 *
 * @param bool   $use       Whether the block editor is used.
 * @param string $post_type Post type.
 * @return bool
 */
function mnm_classic_editor_for_campaigns( $use, $post_type ) {
	return 'mnm_campaign' === $post_type ? false : $use;
}
add_filter( 'use_block_editor_for_post_type', 'mnm_classic_editor_for_campaigns', 10, 2 );

