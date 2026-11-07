<?php
/**
 * Archives for programmes, projects, events and articles (and their taxonomies).
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();

$type = get_query_var( 'post_type' );
if ( is_array( $type ) ) {
	$type = reset( $type );
}
if ( ! $type && is_tax() ) {
	$tax  = get_taxonomy( get_queried_object()->taxonomy );
	$type = $tax && $tax->object_type ? $tax->object_type[0] : '';
}

switch ( $type ) {
	case 'mnm_programme':
		mnm_t_page_header( __( 'What we do', 'mnm-foundation-theme' ), __( 'Our programmes', 'mnm-foundation-theme' ), __( 'Education support, digital literacy and community empowerment: three programmes with one aim, to give people a foundation to build on.', 'mnm-foundation-theme' ) );
		echo '<section class="section"><div class="wrap"><div class="grid grid--3">';
		while ( have_posts() ) {
			the_post();
			mnm_t_card_programme( get_the_ID() );
		}
		echo '</div></div></section>';
		break;

	case 'mnm_project':
		mnm_t_page_header( __( 'Projects & impact', 'mnm-foundation-theme' ), is_tax() ? single_term_title( '', false ) : __( 'Our projects', 'mnm-foundation-theme' ), __( 'Browse the work we are doing, by theme and by place.', 'mnm-foundation-theme' ) );
		echo '<section class="section"><div class="wrap">';
		echo '<div class="filter-rows"><div><span class="filter-label">' . esc_html__( 'Category', 'mnm-foundation-theme' ) . '</span>';
		mnm_t_term_filters( 'mnm_project_cat', get_post_type_archive_link( 'mnm_project' ) );
		echo '</div><div><span class="filter-label">' . esc_html__( 'Location', 'mnm-foundation-theme' ) . '</span>';
		mnm_t_term_filters( 'mnm_project_location', get_post_type_archive_link( 'mnm_project' ) );
		echo '</div></div>';
		if ( have_posts() ) {
			echo '<div class="grid grid--3">';
			while ( have_posts() ) {
				the_post();
				mnm_t_card_project( get_the_ID() );
			}
			echo '</div>';
			mnm_t_pagination();
		} else {
			echo '<p>' . esc_html__( 'No projects found.', 'mnm-foundation-theme' ) . '</p>';
		}
		echo '</div></section>';
		break;

	case 'mnm_event':
		mnm_t_page_header( __( 'Events', 'mnm-foundation-theme' ), __( 'Events', 'mnm-foundation-theme' ), __( 'Gatherings, trainings and community days. Everyone is welcome.', 'mnm-foundation-theme' ) );
		$up   = mnm_events_query( 'upcoming', 50 );
		$past = mnm_events_query( 'past', 50 );
		echo '<section class="section"><div class="wrap">';
		mnm_t_term_filters( 'mnm_event_type', get_post_type_archive_link( 'mnm_event' ) );
		echo '<h2 class="h3">' . esc_html__( 'Upcoming events', 'mnm-foundation-theme' ) . '</h2><div class="event-list">';
		if ( $up->have_posts() ) {
			while ( $up->have_posts() ) {
				$up->the_post();
				mnm_t_card_event( get_the_ID() );
			}
		} else {
			echo '<p>' . esc_html__( 'No upcoming events right now. Please check back soon.', 'mnm-foundation-theme' ) . '</p>';
		}
		wp_reset_postdata();
		echo '</div>';
		if ( $past->have_posts() ) {
			echo '<h2 class="h3 mt-xl">' . esc_html__( 'Past events', 'mnm-foundation-theme' ) . '</h2><div class="event-list">';
			while ( $past->have_posts() ) {
				$past->the_post();
				mnm_t_card_event( get_the_ID() );
			}
			wp_reset_postdata();
			echo '</div>';
		}
		echo '</div></section>';
		break;

	case 'mnm_article':
		mnm_t_page_header( __( 'News & articles', 'mnm-foundation-theme' ), is_tax() ? single_term_title( '', false ) : __( 'Latest news', 'mnm-foundation-theme' ), __( 'Updates, stories and reflections from the foundation.', 'mnm-foundation-theme' ) );
		echo '<section class="section"><div class="wrap">';
		mnm_t_term_filters( 'mnm_article_cat', get_post_type_archive_link( 'mnm_article' ) );
		$feat = ( ! is_tax() && ! is_paged() ) ? get_posts( array( 'post_type' => 'mnm_article', 'posts_per_page' => 1, 'meta_key' => '_mnm_featured', 'meta_value' => '1' ) ) : array(); // phpcs:ignore WordPress.DB.SlowDBQuery
		$feat_id = $feat ? $feat[0]->ID : 0;
		if ( $feat_id ) {
			echo '<div class="featured-article"><p class="eyebrow">' . esc_html__( 'Featured', 'mnm-foundation-theme' ) . '</p>';
			mnm_t_card_article( $feat_id, true );
			echo '</div>';
		}
		echo '<div class="grid grid--3">';
		while ( have_posts() ) {
			the_post();
			if ( get_the_ID() === $feat_id ) {
				continue;
			}
			mnm_t_card_article( get_the_ID() );
		}
		echo '</div>';
		mnm_t_pagination();
		echo '</div></section>';
		break;

	default:
		mnm_t_page_header( '', get_the_archive_title() );
		echo '<section class="section"><div class="wrap wrap--narrow">';
		while ( have_posts() ) {
			the_post();
			echo '<h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
		}
		echo '</div></section>';
}

get_template_part( 'parts/cta-band' );
get_footer();
