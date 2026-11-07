<?php
/**
 * Single fundraising campaign.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
mnm_t_page_header( __( 'Fundraising campaign', 'mnm-foundation-theme' ), get_the_title(), get_the_excerpt() );
?>
<section class="section section--tight">
	<div class="wrap detail-grid">
		<div class="detail-main">
			<?php if ( has_post_thumbnail() ) : ?><div class="detail-media"><?php the_post_thumbnail( 'mnm-wide' ); ?></div><?php endif; ?>
			<div class="prose"><?php the_content(); ?></div>
		</div>
		<aside class="detail-aside"><?php echo mnm_render_progress( get_the_ID(), array( 'title' => false, 'cta' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></aside>
	</div>
</section>
