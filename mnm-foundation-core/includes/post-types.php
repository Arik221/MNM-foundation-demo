<?php
/**
 * Custom post types and taxonomies.
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register content types and taxonomies using WordPress's standard capabilities.
 */
function mnm_register_post_types() {
	$common = array(
		'public'       => true,
		'show_ui'      => true,
		'show_in_menu' => 'mnm-foundation',
		'show_in_rest' => true,
		'has_archive'  => true,
	);

	$types = array(
		'mnm_project'   => array( __( 'Project', 'mnm-foundation-core' ), __( 'Projects', 'mnm-foundation-core' ), 'projects', array( 'title', 'editor', 'excerpt', 'thumbnail' ) ),
		'mnm_programme' => array( __( 'Programme', 'mnm-foundation-core' ), __( 'Programmes', 'mnm-foundation-core' ), 'programmes', array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ) ),
		'mnm_event'     => array( __( 'Event', 'mnm-foundation-core' ), __( 'Events', 'mnm-foundation-core' ), 'events', array( 'title', 'editor', 'excerpt', 'thumbnail' ) ),
		'mnm_article'   => array( __( 'Article', 'mnm-foundation-core' ), __( 'Articles', 'mnm-foundation-core' ), 'news', array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ) ),
	);
	foreach ( $types as $type => $t ) {
		register_post_type(
			$type,
			array_merge(
				$common,
				array(
					'labels'   => mnm_cpt_labels( $t[0], $t[1] ),
					'supports' => $t[3],
					'rewrite'  => array( 'slug' => $t[2], 'with_front' => false ),
				)
			)
		);
	}

	// Marathon updates (announcements shown on the marathon page).
	register_post_type(
		'mnm_update',
		array(
			'labels'       => mnm_cpt_labels( __( 'Marathon Update', 'mnm-foundation-core' ), __( 'Marathon Updates', 'mnm-foundation-core' ) ),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'mnm-foundation',
			'supports'     => array( 'title', 'editor' ),
		)
	);

	// Fundraising campaigns (progress shown publicly; amounts entered manually).
	register_post_type(
		'mnm_campaign',
		array(
			'labels'       => mnm_cpt_labels( __( 'Campaign', 'mnm-foundation-core' ), __( 'Fundraising Campaigns', 'mnm-foundation-core' ) ),
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => 'mnm-foundation',
			'show_in_rest' => true,
			'has_archive'  => false,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'      => array( 'slug' => 'campaigns', 'with_front' => false ),
		)
	);

	// Marathon registrations: private, admin-only, no "Add New".
	register_post_type(
		'mnm_registration',
		array(
			'labels'          => mnm_cpt_labels( __( 'Registration', 'mnm-foundation-core' ), __( 'Registrations', 'mnm-foundation-core' ) ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'mnm-foundation',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);

	$tax = array(
		'mnm_project_cat'      => array( 'mnm_project', __( 'Project Category', 'mnm-foundation-core' ), __( 'Project Categories', 'mnm-foundation-core' ), 'project-category' ),
		'mnm_project_location' => array( 'mnm_project', __( 'Location', 'mnm-foundation-core' ), __( 'Locations', 'mnm-foundation-core' ), 'project-location' ),
		'mnm_event_type'       => array( 'mnm_event', __( 'Event Type', 'mnm-foundation-core' ), __( 'Event Types', 'mnm-foundation-core' ), 'event-type' ),
		'mnm_article_cat'      => array( 'mnm_article', __( 'Article Category', 'mnm-foundation-core' ), __( 'Article Categories', 'mnm-foundation-core' ), 'news-category' ),
	);
	foreach ( $tax as $name => $t ) {
		register_taxonomy(
			$name,
			$t[0],
			array(
				'labels'            => mnm_tax_labels( $t[1], $t[2] ),
				'hierarchical'      => true,
				'public'            => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => $t[3], 'with_front' => false ),
			)
		);
	}

	// Gallery collections: categorise Media Library images.
	register_taxonomy(
		'mnm_gallery_cat',
		'attachment',
		array(
			'labels'            => mnm_tax_labels( __( 'Gallery Collection', 'mnm-foundation-core' ), __( 'Gallery Collections', 'mnm-foundation-core' ) ),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'mnm_register_post_types', 5 );

/**
 * Post type labels.
 *
 * @param string $singular Singular.
 * @param string $plural   Plural.
 * @return array
 */
function mnm_cpt_labels( $singular, $plural ) {
	/* translators: %s: post type name. */
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'add_new'            => __( 'Add New', 'mnm-foundation-core' ),
		'add_new_item'       => sprintf( __( 'Add New %s', 'mnm-foundation-core' ), $singular ),
		'edit_item'          => sprintf( __( 'Edit %s', 'mnm-foundation-core' ), $singular ),
		'new_item'           => sprintf( __( 'New %s', 'mnm-foundation-core' ), $singular ),
		'view_item'          => sprintf( __( 'View %s', 'mnm-foundation-core' ), $singular ),
		'search_items'       => sprintf( __( 'Search %s', 'mnm-foundation-core' ), $plural ),
		'not_found'          => sprintf( __( 'No %s found.', 'mnm-foundation-core' ), strtolower( $plural ) ),
		'not_found_in_trash' => sprintf( __( 'No %s found in the Bin.', 'mnm-foundation-core' ), strtolower( $plural ) ),
		'all_items'          => $plural,
	);
}

/**
 * Taxonomy labels.
 *
 * @param string $singular Singular.
 * @param string $plural   Plural.
 * @return array
 */
function mnm_tax_labels( $singular, $plural ) {
	return array(
		'name'          => $plural,
		'singular_name' => $singular,
		'menu_name'     => $plural,
		'all_items'     => sprintf( __( 'All %s', 'mnm-foundation-core' ), $plural ),
		'edit_item'     => sprintf( __( 'Edit %s', 'mnm-foundation-core' ), $singular ),
		'add_new_item'  => sprintf( __( 'Add New %s', 'mnm-foundation-core' ), $singular ),
		'search_items'  => sprintf( __( 'Search %s', 'mnm-foundation-core' ), $plural ),
	);
}

/**
 * Events: order archive by event start date, upcoming first.
 *
 * @param WP_Query $q Query.
 */
function mnm_order_event_archive( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'mnm_event' ) || $q->is_tax( 'mnm_event_type' ) ) {
		$q->set( 'posts_per_page', 100 );
		$q->set( 'meta_key', '_mnm_start' );
		$q->set( 'orderby', array( 'meta_value' => 'ASC' ) );
	}
	if ( $q->is_post_type_archive( 'mnm_project' ) || $q->is_tax( array( 'mnm_project_cat', 'mnm_project_location' ) ) ) {
		$q->set( 'posts_per_page', 12 );
	}
	if ( $q->is_post_type_archive( 'mnm_programme' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$q->set( 'posts_per_page', 20 );
	}
	if ( $q->is_post_type_archive( 'mnm_article' ) || $q->is_tax( 'mnm_article_cat' ) ) {
		$q->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'mnm_order_event_archive' );

/**
 * Query upcoming or past events.
 *
 * @param string $when  'upcoming' or 'past'.
 * @param int    $limit Number of events.
 * @return WP_Query
 */
function mnm_events_query( $when = 'upcoming', $limit = 6 ) {
	$now = current_time( 'Y-m-d\TH:i' );
	return new WP_Query(
		array(
			'post_type'      => 'mnm_event',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_mnm_start',
			'orderby'        => 'meta_value',
			'order'          => 'upcoming' === $when ? 'ASC' : 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_mnm_start',
					'value'   => $now,
					'compare' => 'upcoming' === $when ? '>=' : '<',
					'type'    => 'CHAR',
				),
			),
		)
	);
}
