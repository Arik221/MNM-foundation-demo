<?php
/**
 * Single content router: loads parts/single-{post_type}.php when present.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) {
	the_post();
	$type = get_post_type();
	if ( locate_template( 'parts/single-' . $type . '.php' ) ) {
		get_template_part( 'parts/single', $type );
	} else {
		mnm_t_page_header( '', get_the_title() );
		echo '<section class="section"><div class="wrap wrap--narrow prose">';
		the_content();
		echo '</div></section>';
	}
}
get_footer();
