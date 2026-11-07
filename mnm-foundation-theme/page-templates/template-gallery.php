<?php
/**
 * Template Name: Gallery
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
mnm_t_page_header( __( 'Gallery', 'mnm-foundation-theme' ), get_the_title(), __( 'Photographs from our programmes, projects and events. Select a photo to view it larger.', 'mnm-foundation-theme' ) );
?>
<section class="section"><div class="wrap">
	<?php if ( function_exists( 'mnm_gallery_items' ) ) { $reel = mnm_gallery_items( array( 'limit' => 12 ) ); echo '<div class="reels" style="margin-bottom:3rem">'; mnm_t_reel( array_slice( $reel, 0, 8 ) ); mnm_t_reel( array_reverse( array_slice( $reel, 2 ) ), true ); echo '</div>'; } ?>
	<?php echo function_exists( 'mnm_render_gallery' ) ? mnm_render_gallery( array( 'limit' => 100 ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div></section>
<?php
get_template_part( 'parts/cta-band' );
get_footer();
