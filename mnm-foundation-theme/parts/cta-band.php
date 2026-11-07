<?php
/**
 * Volunteer and donation call to action.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$gi = get_page_by_path( 'get-involved' );
?>
<section class="cta-band">
	<div class="orb orb--a" aria-hidden="true"></div><div class="orb orb--c" aria-hidden="true"></div>
	<div class="wrap cta-band__inner">
		<h2 class="cta-band__title" data-split><?php esc_html_e( 'Help a child stay in school.', 'mnm-foundation-theme' ); ?></h2>
		<p class="cta-band__text reveal"><?php esc_html_e( 'Give, volunteer or run with us. Every contribution keeps a child learning or teaches a villager a new skill.', 'mnm-foundation-theme' ); ?></p>
		<div class="cta-band__btns reveal">
			<a class="btn btn--coral btn--lg" data-magnetic href="<?php echo esc_url( function_exists( 'mnm_donate_url' ) ? mnm_donate_url() : '#' ); ?>"><?php echo mnm_t_icon( 'heart', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Donate', 'mnm-foundation-theme' ); ?></a>
			<a class="btn btn--glass btn--lg" data-magnetic href="<?php echo esc_url( $gi ? get_permalink( $gi ) . '#volunteer' : home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Volunteer with us', 'mnm-foundation-theme' ); ?> <?php echo mnm_t_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
	</div>
</section>
