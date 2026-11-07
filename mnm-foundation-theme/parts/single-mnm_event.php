<?php
/**
 * Single event.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$id      = get_the_ID();
$d       = mnm_t_event_dates( $id );
$venue   = get_post_meta( $id, '_mnm_venue', true );
$reg     = get_post_meta( $id, '_mnm_registration_url', true );
$reg_lbl = get_post_meta( $id, '_mnm_registration_label', true );
$gallery = function_exists( 'mnm_get_gallery_ids' ) ? mnm_get_gallery_ids( $id ) : array();
mnm_t_page_header( $d && $d['upcoming'] ? __( 'Upcoming event', 'mnm-foundation-theme' ) : __( 'Past event', 'mnm-foundation-theme' ), get_the_title(), get_the_excerpt() );
?>
<section class="section section--tight">
	<div class="wrap detail-grid">
		<div class="detail-main">
			<?php if ( has_post_thumbnail() ) : ?><div class="detail-media"><?php the_post_thumbnail( 'mnm-wide', array( 'fetchpriority' => 'high' ) ); ?></div><?php endif; ?>
			<div class="prose"><?php the_content(); ?></div>
			<?php if ( $gallery ) : ?>
				<h2 class="h3"><?php esc_html_e( 'Event gallery', 'mnm-foundation-theme' ); ?></h2>
				<?php echo mnm_render_gallery( array( 'ids' => $gallery, 'filters' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		</div>
		<aside class="detail-aside">
			<h2 class="h4"><?php esc_html_e( 'Event details', 'mnm-foundation-theme' ); ?></h2>
			<dl class="facts">
				<?php if ( $d ) : ?>
					<div><dt><?php esc_html_e( 'Date', 'mnm-foundation-theme' ); ?></dt><dd><?php echo esc_html( $d['full'] ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Time', 'mnm-foundation-theme' ); ?></dt><dd><?php echo esc_html( $d['time'] ); ?></dd></div>
				<?php endif; ?>
				<?php if ( $venue ) : ?><div><dt><?php esc_html_e( 'Venue', 'mnm-foundation-theme' ); ?></dt><dd><?php echo esc_html( $venue ); ?></dd></div><?php endif; ?>
			</dl>
			<?php if ( $reg && $d && $d['upcoming'] ) : ?>
				<a class="btn btn--dark btn--block" href="<?php echo esc_url( $reg ); ?>"><?php echo esc_html( $reg_lbl ? $reg_lbl : __( 'Register now', 'mnm-foundation-theme' ) ); ?></a>
			<?php endif; ?>
			<a class="link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'mnm_event' ) ); ?>">&larr; <?php esc_html_e( 'All events', 'mnm-foundation-theme' ); ?></a>
		</aside>
	</div>
</section>
