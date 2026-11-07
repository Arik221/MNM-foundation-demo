<?php
/**
 * Template Name: Contact Us
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
$phone = mnm_t_setting( 'phone' );
$email = mnm_t_setting( 'email' );
$addr  = mnm_t_setting( 'address' );
$map   = mnm_t_setting( 'map_embed' );
$soc   = function_exists( 'mnm_social_links' ) ? mnm_social_links() : array();
mnm_t_page_header( __( 'Contact us', 'mnm-foundation-theme' ), __( 'We would love to hear from you', 'mnm-foundation-theme' ), __( 'Questions, ideas, partnerships, or an offer of help: write to us or call.', 'mnm-foundation-theme' ) );
?>
<section class="section"><div class="wrap contact-grid">
	<div class="contact-info reveal">
		<h2 class="h3"><?php esc_html_e( 'Find us', 'mnm-foundation-theme' ); ?></h2>
		<ul class="contact-list">
			<?php if ( $addr ) : ?><li><?php echo mnm_t_icon( 'pin', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><address><?php echo nl2br( esc_html( $addr ) ); ?></address></li><?php endif; ?>
			<?php if ( $phone ) : ?><li><?php echo mnm_t_icon( 'phone', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( mnm_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li><?php endif; ?>
			<?php if ( $email ) : ?><li><?php echo mnm_t_icon( 'mail', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li><?php endif; ?>
		</ul>
		<?php if ( $soc ) : ?>
			<h3 class="h4"><?php esc_html_e( 'Follow us', 'mnm-foundation-theme' ); ?></h3>
			<ul class="social social--dark"><?php foreach ( $soc as $s ) : ?><li><a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo mnm_t_icon( $s['key'], 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php echo esc_html( $s['label'] ); ?></span></a></li><?php endforeach; ?></ul>
		<?php endif; ?>
		<div class="map">
			<?php if ( $map && 0 === strpos( $map, 'https://' ) ) : ?>
				<iframe src="<?php echo esc_url( $map ); ?>" title="<?php esc_attr_e( 'Map showing the foundation office', 'mnm-foundation-theme' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
			<?php else : ?>
				<div class="map__ph"><?php echo mnm_t_icon( 'pin', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><strong><?php esc_html_e( 'Ukhrul, Manipur', 'mnm-foundation-theme' ); ?></strong><span><?php esc_html_e( 'H/No. 25, Kahum Tang, Ukhrul, Manipur 795142', 'mnm-foundation-theme' ); ?></span></div>
			<?php endif; ?>
		</div>
	</div>
	<div class="reveal">
		<h2 class="h3"><?php esc_html_e( 'Send a message', 'mnm-foundation-theme' ); ?></h2>
		<?php echo function_exists( 'mnm_render_contact_form' ) ? mnm_render_contact_form() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</div></section>
<?php
get_footer();
