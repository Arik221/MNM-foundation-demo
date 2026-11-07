<?php
/**
 * Default page.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	mnm_t_page_header( '', get_the_title() );
	?>
	<section class="section"><div class="wrap wrap--narrow prose"><?php the_content(); ?></div></section>
	<?php
endwhile;
get_footer();
