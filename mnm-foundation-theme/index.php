<?php
/**
 * Fallback template.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
mnm_t_page_header( '', is_search() ? sprintf( /* translators: %s: query. */ __( 'Search: %s', 'mnm-foundation-theme' ), get_search_query() ) : get_the_archive_title() );
?>
<section class="section"><div class="wrap wrap--narrow">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'plain-entry' ); ?>>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php the_excerpt(); ?>
		</article>
	<?php endwhile; mnm_t_pagination(); else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'mnm-foundation-theme' ); ?></p>
	<?php endif; ?>
</div></section>
<?php
get_footer();
