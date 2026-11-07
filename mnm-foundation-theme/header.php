<?php
/**
 * Site header.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$ann = mnm_t_announcement();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/img/mark.svg' ); ?>" type="image/svg+xml" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="scroll-progress" data-progress aria-hidden="true"></div>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'mnm-foundation-theme' ); ?></a>

<?php if ( $ann ) : ?>
	<div class="announce">
		<div class="wrap">
			<p><?php echo esc_html( $ann['text'] ); ?>
				<?php if ( $ann['link'] ) : ?><a href="<?php echo esc_url( $ann['link'] ); ?>"><?php esc_html_e( 'Find out more', 'mnm-foundation-theme' ); ?> &rarr;</a><?php endif; ?></p>
		</div>
	</div>
<?php endif; ?>

<header class="site-header" data-header>
	<div class="wrap site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: site name. */ __( '%s home', 'mnm-foundation-theme' ), get_bloginfo( 'name' ) ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.svg' ); ?>" width="150" height="52" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
			<?php endif; ?>
		</a>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
			<span class="nav-toggle__open"><?php echo mnm_t_icon( 'menu', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="nav-toggle__close"><?php echo mnm_t_icon( 'close', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'mnm-foundation-theme' ); ?></span>
		</button>

		<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'mnm-foundation-theme' ); ?>" data-nav>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
			<a class="btn btn--coral btn--sm nav-cta" href="<?php echo esc_url( function_exists( 'mnm_donate_url' ) ? mnm_donate_url() : home_url( '/' ) ); ?>"><?php echo mnm_t_icon( 'heart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Support us', 'mnm-foundation-theme' ); ?></a>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
