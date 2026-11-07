<?php
/**
 * Single article.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$cats = get_the_terms( get_the_ID(), 'mnm_article_cat' );
?>
<article <?php post_class( 'article' ); ?>>
	<header class="page-hero page-hero--article"><div class="wrap wrap--narrow">
		<p class="eyebrow">
			<?php if ( $cats && ! is_wp_error( $cats ) ) : ?><a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a> &middot; <?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</p>
		<h1 class="h1"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?><p class="lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
	</div></header>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="wrap article__media"><?php the_post_thumbnail( 'mnm-wide', array( 'fetchpriority' => 'high' ) ); ?></div>
	<?php endif; ?>
	<section class="section section--tight"><div class="wrap wrap--narrow prose"><?php the_content(); ?>
		<p class="back"><a class="link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'mnm_article' ) ); ?>">&larr; <?php esc_html_e( 'All articles', 'mnm-foundation-theme' ); ?></a></p>
	</div></section>
</article>
<?php
$related = get_posts( array( 'post_type' => 'mnm_article', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ) ) );
if ( $related ) :
	?>
<section class="section section--grey"><div class="wrap">
	<?php mnm_t_section_head( '', __( 'Keep reading', 'mnm-foundation-theme' ) ); ?>
	<div class="grid grid--3"><?php foreach ( $related as $r ) { mnm_t_card_article( $r->ID ); } ?></div>
</div></section>
<?php endif; ?>
