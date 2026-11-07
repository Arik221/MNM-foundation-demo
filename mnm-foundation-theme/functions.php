<?php
/**
 * MNM Foundation Theme: setup, assets, SEO basics.
 *
 * The theme is presentation only. Content types, forms, settings and fundraising logic live in
 * the "MNM Foundation Core" plugin, so switching themes never loses data.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'MNM_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/template-tags.php';

/**
 * Theme setup.
 */
function mnm_theme_setup() {
	load_theme_textdomain( 'mnm-foundation-theme', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 300, 'flex-width' => true, 'flex-height' => true ) );
	add_image_size( 'mnm-card', 800, 560, true );
	add_image_size( 'mnm-wide', 1600, 900, true );
	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'mnm-foundation-theme' ),
			'footer'  => __( 'Footer menu', 'mnm-foundation-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'mnm_theme_setup' );

/**
 * Assets: one stylesheet, one deferred script, preloaded fonts. No jQuery on the front end.
 */
function mnm_theme_assets() {
	wp_enqueue_style( 'mnm-theme', get_template_directory_uri() . '/assets/css/main.css', array(), MNM_THEME_VERSION );
	wp_enqueue_script( 'mnm-theme', get_template_directory_uri() . '/assets/js/main.js', array(), MNM_THEME_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'mnm_theme_assets' );

/**
 * Mark the document as JS-enabled before first paint (avoids a flash of un-animated content).
 */
function mnm_theme_js_flag() {
	echo "<script>document.documentElement.className+=' js';</script>\n";
}
add_action( 'wp_head', 'mnm_theme_js_flag', 0 );

/**
 * Preload the two variable fonts.
 */
function mnm_theme_preload_fonts() {
	foreach ( array( 'manrope-latin', 'inter-latin' ) as $f ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n", esc_url( get_template_directory_uri() . '/assets/fonts/' . $f . '.woff2' ) );
	}
}
add_action( 'wp_head', 'mnm_theme_preload_fonts', 1 );

/**
 * Trim default head clutter (emoji scripts).
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function mnm_theme_body_class( $classes ) {
	if ( mnm_t_announcement() ) {
		$classes[] = 'has-announcement';
	}
	return $classes;
}
add_filter( 'body_class', 'mnm_theme_body_class' );

/**
 * Menu link classes and current state.
 *
 * @param array $atts Link attributes.
 * @param WP_Post $item Menu item.
 * @return array
 */
function mnm_theme_menu_link_atts( $atts, $item ) {
	if ( in_array( 'current-menu-item', (array) $item->classes, true ) || in_array( 'current-menu-ancestor', (array) $item->classes, true ) ) {
		$atts['aria-current'] = 'page';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'mnm_theme_menu_link_atts', 10, 2 );

/**
 * Highlight CPT archives in the menu when viewing single items.
 *
 * @param array $classes Classes.
 * @param WP_Post $item Menu item.
 * @return array
 */
function mnm_theme_menu_classes( $classes, $item ) {
	if ( 'custom' === $item->type && is_singular() ) {
		$archive = get_post_type_archive_link( get_post_type() );
		if ( $archive && trailingslashit( $item->url ) === trailingslashit( $archive ) ) {
			$classes[] = 'current-menu-item';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'mnm_theme_menu_classes', 10, 2 );

/**
 * Meta description, Open Graph and structured data.
 */
function mnm_theme_seo() {
	$title = wp_get_document_title();
	$desc  = '';
	$img   = '';
	if ( is_singular() ) {
		$desc = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 30, '…' );
		$img  = get_the_post_thumbnail_url( null, 'large' );
	}
	if ( ! $desc ) {
		$desc = mnm_t_setting( 'intro', get_bloginfo( 'description' ) );
		$desc = wp_trim_words( $desc, 30, '…' );
	}
	if ( ! $img ) {
		$hero = (int) mnm_t_setting( 'hero_image', 0 );
		$img  = $hero ? wp_get_attachment_image_url( $hero, 'large' ) : '';
	}
	echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'mnm_article' ) ? 'article' : 'website' ) . '" />' . "\n";
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '" />' . "\n";
	}
	echo '<meta name="theme-color" content="#20252B" />' . "\n";

	$ld = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'NGO',
		'name'      => mnm_t_setting( 'org_name', 'MNM Foundation' ),
		'url'       => home_url( '/' ),
		'telephone' => mnm_t_setting( 'phone' ),
		'address'   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'H/No. 25, Kahum Tang',
			'addressLocality' => 'Ukhrul',
			'addressRegion'   => 'Manipur',
			'postalCode'      => '795142',
			'addressCountry'  => 'IN',
		),
		'sameAs'    => function_exists( 'mnm_social_links' ) ? wp_list_pluck( mnm_social_links(), 'url' ) : array(),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

	if ( is_singular( 'mnm_event' ) ) {
		$start = get_post_meta( get_the_ID(), '_mnm_start', true );
		$ev    = array(
			'@context'  => 'https://schema.org',
			'@type'     => 'Event',
			'name'      => get_the_title(),
			'startDate' => $start,
			'endDate'   => get_post_meta( get_the_ID(), '_mnm_end', true ),
			'location'  => array( '@type' => 'Place', 'name' => get_post_meta( get_the_ID(), '_mnm_venue', true ) ),
			'organizer' => array( '@type' => 'Organization', 'name' => 'MNM Foundation' ),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $ev, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'mnm_theme_seo', 5 );

/**
 * Notice for administrators when the companion plugin is missing.
 */
function mnm_theme_plugin_notice() {
	if ( ! function_exists( 'mnm_setting' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'MNM Foundation Theme needs the "MNM Foundation Core" plugin for projects, events, forms and settings. Please install and activate it.', 'mnm-foundation-theme' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'mnm_theme_plugin_notice' );

/**
 * Prepend a standard Home tab (house icon) to the primary menu.
 *
 * @param string   $items Menu items HTML.
 * @param stdClass $args  Menu arguments.
 * @return string
 */
function mnm_theme_home_tab( $items, $args ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
		$class = 'menu-item menu-home' . ( is_front_page() ? ' current-menu-item' : '' );
		$home  = '<li class="' . esc_attr( $class ) . '"><a href="' . esc_url( home_url( '/' ) ) . '"' . ( is_front_page() ? ' aria-current="page"' : '' ) . '>' . mnm_t_icon( 'home', 20 ) . '<span class="menu-home__label">' . esc_html__( 'Home', 'mnm-foundation-theme' ) . '</span></a></li>';
		$items = $home . $items;
	}
	return $items;
}
add_filter( 'wp_nav_menu_items', 'mnm_theme_home_tab', 10, 2 );
