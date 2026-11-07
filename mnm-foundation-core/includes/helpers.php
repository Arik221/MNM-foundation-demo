<?php
/**
 * Shared helpers: settings access, formatting, demo labels.
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for the main settings (option: mnm_settings).
 *
 * @return array
 */
function mnm_settings_defaults() {
	return array(
		'org_name'             => 'MNM Foundation',
		'tagline'              => 'Fund and Fight',
		'address'              => "H/No. 25, Kahum Tang,\nUkhrul, Manipur - 795142\nIndia",
		'phone'                => '+91 98190 32215',
		'email'                => '',
		'facebook'             => 'https://www.facebook.com/MNMFOUNDATIONINDIA',
		'instagram'            => '',
		'youtube'              => '',
		'x_twitter'            => '',
		'map_embed'            => '',
		'notification_email'   => '',
		'hero_heading'         => 'Empowering Communities. Transforming Futures.',
		'hero_text'            => 'MNM Foundation funds and fights the denial of education to economically disadvantaged children, and brings digital literacy to the people of remote villages.',
		'hero_image'           => 0,
		'hero_video'           => '',
		'announcement_on'      => 0,
		'announcement_text'    => '',
		'announcement_link'    => '',
		'statement'            => 'Three young lives. One foundation. A chance for every child to stay in school, and for every village to learn a new skill.',
		'intro'                => 'MNM Foundation was set up by the families of Michael Hunphun Luiyi, Nimmichon Raman and Masotmi Awungshi, three young people who died too soon. In their memory, the foundation works to empower communities, beginning with children who cannot afford to stay in school and with people who have never had access to a computer.',
		'mission'              => "To fund and fight the denial of education to children who are economically disadvantaged.\nTo bring digital literacy to students, youth leaders, village administrators and women leaders through our training centres.",
		'vision'               => 'Empowering children and communities through education, digital literacy and access to opportunities, so that no child leaves school only because the family cannot pay.',
		'dream'                => 'That this mission will spread, and that every child who receives our support will one day be able to pay it forward and help fund another child, while the computer skills they gain help them build a better life.',
		'values'               => "Remembrance|Everything we do carries the memory of Michael, Nimmichon and Masotmi.\nEducation for every child|Money should never be the reason a child leaves the classroom.\nAccess to opportunity|People in remote villages deserve the same chance to learn digital skills.\nPaying it forward|Every child we help is invited, one day, to help another.\nHumility|We make a humble attempt, stay honest about outcomes and keep learning.",
		'stat_1_value'         => '',
		'stat_1_label'         => '',
		'stat_2_value'         => '',
		'stat_2_label'         => '',
		'stat_3_value'         => '',
		'stat_3_label'         => '',
		'stat_4_value'         => '',
		'stat_4_label'         => '',
		'stats_sample'         => 0,
		'footer_text'          => 'MNM Foundation is a family-led, community-rooted non-profit based in Ukhrul, Manipur, India.',
		'footer_note'          => '',
		'donate_url'           => '',
		'demo_mode'            => 1,
		'memorials'            => array(),
		'marathon_title'       => 'MNM Foundation Marathon',
		'marathon_tagline'     => 'Run for a child\'s education.',
		'marathon_status'      => 'open',
		'marathon_date_text'   => '',
		'marathon_venue_text'  => '',
		'marathon_text'        => 'Join the MNM Foundation Marathon and run alongside your community to help fund education for children who might otherwise be forced to leave school. Every runner, walker and volunteer helps. Event details are being finalised and will be announced here.',
		'marathon_banner'      => 0,
		'marathon_categories'  => "5K Fun Run|Open to all ages. Walk, jog or run.\n10K Run|For regular runners.\nHalf Marathon (21K)|For experienced distance runners.",
		'marathon_tshirts'     => 'XS, S, M, L, XL, XXL',
		'marathon_campaign'    => 0,
	);
}

/**
 * Get all settings merged with defaults.
 *
 * @param bool $refresh Rebuild the request cache.
 * @return array
 */
function mnm_settings( $refresh = false ) {
	static $cache = null;
	if ( $refresh || null === $cache ) {
		$saved = get_option( 'mnm_settings', array() );
		$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), mnm_settings_defaults() );
	}
	return $cache;
}

// Keep the request cache fresh when the option changes (e.g. during demo seeding).
add_action( 'update_option_mnm_settings', function () { mnm_settings( true ); } );
add_action( 'add_option_mnm_settings', function () { mnm_settings( true ); } );

/**
 * Get one setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function mnm_setting( $key, $default = '' ) {
	$s = mnm_settings();
	return isset( $s[ $key ] ) && '' !== $s[ $key ] ? $s[ $key ] : $default;
}

/**
 * Is "show demonstration labels" switched on?
 *
 * @return bool
 */
function mnm_is_demo_mode() {
	return (bool) mnm_setting( 'demo_mode', 0 );
}

/**
 * Small "demonstration data" pill. Empty when demo mode is off.
 *
 * @param string $text Label.
 * @return string
 */
function mnm_demo_badge( $text = '' ) {
	if ( ! mnm_is_demo_mode() || ! is_admin() ) {
		return '';
	}
	$text = $text ? $text : __( 'Demonstration data', 'mnm-foundation-core' );
	return '<span class="mnm-demo-badge">' . esc_html( $text ) . '</span>';
}

/**
 * Format money with Indian digit grouping (e.g. 1,25,000).
 *
 * @param float  $amount Amount.
 * @param string $symbol Currency symbol.
 * @return string
 */
function mnm_format_money( $amount, $symbol = '₹' ) {
	$amount = (float) $amount;
	$neg    = $amount < 0 ? '-' : '';
	$int    = (string) (int) floor( abs( $amount ) );
	if ( strlen( $int ) > 3 ) {
		$last = substr( $int, -3 );
		$rest = substr( $int, 0, -3 );
		$rest = preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest );
		$int  = $rest . ',' . $last;
	}
	return $neg . $symbol . "\u{00A0}" . $int;
}

/**
 * Parse a comma-separated list setting into trimmed array.
 *
 * @param string $csv Comma-separated string.
 * @return array
 */
function mnm_csv_to_array( $csv ) {
	return array_values( array_filter( array_map( 'trim', explode( ',', (string) $csv ) ), 'strlen' ) );
}

/**
 * Parse "Label|Description" per line.
 *
 * @param string $text Multi-line string.
 * @return array[] Each with 'label' and 'desc'.
 */
function mnm_parse_pairs( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$out[] = array(
			'label' => $parts[0],
			'desc'  => isset( $parts[1] ) ? $parts[1] : '',
		);
	}
	return $out;
}

/**
 * Social links that have a URL.
 *
 * @return array[] Each: key, label, url.
 */
function mnm_social_links() {
	$map   = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'x_twitter' => 'X (Twitter)',
	);
	$links = array();
	foreach ( $map as $key => $label ) {
		$url = mnm_setting( $key );
		if ( $url ) {
			$links[] = array(
				'key'   => $key,
				'label' => $label,
				'url'   => $url,
			);
		}
	}
	return $links;
}

/**
 * Where notification emails go.
 *
 * @return string
 */
function mnm_notification_email() {
	$email = mnm_setting( 'notification_email' );
	if ( ! $email ) {
		$email = mnm_setting( 'email' );
	}
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

/**
 * Ordered gallery attachment IDs stored on a post.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return int[]
 */
function mnm_get_gallery_ids( $post_id, $key = '_mnm_gallery' ) {
	$raw = get_post_meta( $post_id, $key, true );
	if ( empty( $raw ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) ) );
}

/**
 * Phone number to a tel: href.
 *
 * @param string $phone Phone.
 * @return string
 */
function mnm_tel_href( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
}
