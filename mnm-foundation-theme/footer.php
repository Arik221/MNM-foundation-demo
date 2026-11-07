<?php
/**
 * Site footer.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$phone = mnm_t_setting( 'phone' );
$email = mnm_t_setting( 'email' );
$addr  = mnm_t_setting( 'address' );
$soc   = function_exists( 'mnm_social_links' ) ? mnm_social_links() : array();
?>
</main>

<footer class="site-footer">
	<div class="wrap site-footer__grid">
		<div class="site-footer__about">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.svg' ); ?>" width="190" height="66" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy" />
			<p><?php echo esc_html( mnm_t_setting( 'footer_text' ) ); ?></p>
			<?php if ( $soc ) : ?>
				<ul class="social" aria-label="<?php esc_attr_e( 'Social media', 'mnm-foundation-theme' ); ?>">
					<?php foreach ( $soc as $s ) : ?>
						<li><a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo mnm_t_icon( $s['key'], 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php echo esc_html( $s['label'] ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Footer', 'mnm-foundation-theme' ); ?>">
			<h2 class="footer-h"><?php esc_html_e( 'Explore', 'mnm-foundation-theme' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
			<ul class="footer-menu">
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'mnm_article' ) ); ?>"><?php esc_html_e( 'News & Articles', 'mnm-foundation-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'mnm_event' ) ); ?>"><?php esc_html_e( 'Events', 'mnm-foundation-theme' ); ?></a></li>
			</ul>
		</nav>

		<div>
			<h2 class="footer-h"><?php esc_html_e( 'Contact', 'mnm-foundation-theme' ); ?></h2>
			<address>
				<?php if ( $addr ) : ?><p><?php echo nl2br( esc_html( $addr ) ); ?></p><?php endif; ?>
				<?php if ( $phone ) : ?><p><a href="<?php echo esc_url( function_exists( 'mnm_tel_href' ) ? mnm_tel_href( $phone ) : 'tel:' . $phone ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
				<?php if ( $email ) : ?><p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
			</address>
		</div>
	</div>
	<div class="wrap site-footer__base">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( mnm_t_setting( 'org_name', 'MNM Foundation' ) ); ?>. <?php echo esc_html( mnm_t_setting( 'footer_note' ) ); ?></p>
	</div>
	<div class="site-footer__mark" aria-hidden="true">MNM Foundation</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
