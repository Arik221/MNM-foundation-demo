<?php
/**
 * Template Name: Marathon
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
$banner   = (int) mnm_t_setting( 'marathon_banner', 0 );
$camp_id  = (int) mnm_t_setting( 'marathon_campaign', 0 );
$races    = function_exists( 'mnm_reg_races' ) ? mnm_reg_races() : array();
$count    = function_exists( 'mnm_reg_count' ) ? mnm_reg_count() : 0;
$date     = mnm_t_setting( 'marathon_date_text' );
$venue    = mnm_t_setting( 'marathon_venue_text' );
$closed   = 'closed' === mnm_t_setting( 'marathon_status', 'open' );
$updates  = get_posts( array( 'post_type' => 'mnm_update', 'posts_per_page' => 5, 'post_status' => 'publish' ) );
$gallery  = function_exists( 'mnm_gallery_items' ) ? mnm_gallery_items( array( 'collection' => 'marathon', 'limit' => 9 ) ) : array();
?>
<section class="marathon-hero">
	<?php if ( $banner ) { echo wp_get_attachment_image( $banner, 'mnm-wide', false, array( 'class' => 'marathon-hero__img', 'fetchpriority' => 'high', 'alt' => '' ) ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="marathon-hero__shade" aria-hidden="true"></div>
	<div class="wrap marathon-hero__inner">
		<p class="eyebrow eyebrow--light"><?php esc_html_e( 'Run for a reason', 'mnm-foundation-theme' ); ?></p>
		<h1 class="hero__title"><?php echo esc_html( mnm_t_setting( 'marathon_title', 'MNM Foundation Marathon' ) ); ?></h1>
		<p class="hero__text"><?php echo esc_html( mnm_t_setting( 'marathon_tagline' ) ); ?></p>
		<div class="hero__cta">
			<?php if ( ! $closed ) : ?><a class="btn btn--coral btn--lg" href="#register"><?php esc_html_e( 'Register now', 'mnm-foundation-theme' ); ?></a><?php endif; ?>
			<a class="btn btn--glass btn--lg" href="<?php echo esc_url( function_exists( 'mnm_donate_url' ) ? mnm_donate_url() : '#' ); ?>"><?php esc_html_e( 'Donate', 'mnm-foundation-theme' ); ?></a>
		</div>
	</div>
	<?php if ( $banner && get_post_meta( $banner, '_mnm_demo', true ) ) : ?><span class="demo-tag"><?php esc_html_e( 'Demo image: replace with a photograph', 'mnm-foundation-theme' ); ?></span><?php endif; ?>
	<div class="hero__ribbon" aria-hidden="true"><span class="stripes"></span></div>
</section>

<section class="section section--tight">
	<div class="wrap info-strip">
		<div class="info-tile reveal"><?php echo mnm_t_icon( 'calendar', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><span><?php esc_html_e( 'Date', 'mnm-foundation-theme' ); ?></span><strong><?php echo esc_html( $date ? $date : __( 'To be announced', 'mnm-foundation-theme' ) ); ?></strong><?php if ( ! $date ) : ?><em class="placeholder"><?php esc_html_e( 'Placeholder', 'mnm-foundation-theme' ); ?></em><?php endif; ?></div></div>
		<div class="info-tile reveal"><?php echo mnm_t_icon( 'pin', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><span><?php esc_html_e( 'Venue', 'mnm-foundation-theme' ); ?></span><strong><?php echo esc_html( $venue ? $venue : __( 'To be announced', 'mnm-foundation-theme' ) ); ?></strong><?php if ( ! $venue ) : ?><em class="placeholder"><?php esc_html_e( 'Placeholder', 'mnm-foundation-theme' ); ?></em><?php endif; ?></div></div>
		<div class="info-tile reveal"><?php echo mnm_t_icon( 'people', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><span><?php esc_html_e( 'Registered', 'mnm-foundation-theme' ); ?></span><strong data-mnm-regcount><?php echo (int) $count; ?></strong><?php echo function_exists( 'mnm_demo_badge' ) ? mnm_demo_badge( __( 'Demo count', 'mnm-foundation-theme' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
	</div>
</section>

<section class="section">
	<div class="wrap two-col two-col--wide">
		<div class="reveal">
			<p class="eyebrow"><?php esc_html_e( 'About the event', 'mnm-foundation-theme' ); ?></p>
			<h2 class="h2"><?php esc_html_e( 'Every step funds a child\'s education', 'mnm-foundation-theme' ); ?></h2>
			<p class="lede"><?php echo esc_html( mnm_t_setting( 'marathon_text' ) ); ?></p>
			<?php if ( $races ) : ?>
				<h3 class="h4"><?php esc_html_e( 'Race categories', 'mnm-foundation-theme' ); ?> <em class="placeholder"><?php esc_html_e( 'Placeholders', 'mnm-foundation-theme' ); ?></em></h3>
				<ul class="races">
					<?php foreach ( $races as $label => $desc ) : ?><li><strong><?php echo esc_html( $label ); ?></strong><span><?php echo esc_html( $desc ); ?></span></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="reveal">
			<?php if ( $camp_id && function_exists( 'mnm_render_progress' ) ) { echo mnm_render_progress( $camp_id, array( 'cta' => true, 'class' => 'is-feature' ) ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>

<section class="section section--grey" id="register-section">
	<div class="wrap two-col two-col--form">
		<div class="reveal">
			<p class="eyebrow"><?php esc_html_e( 'Registration', 'mnm-foundation-theme' ); ?></p>
			<h2 class="h2"><?php esc_html_e( 'Take your place on the start line', 'mnm-foundation-theme' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Complete the form to register. You will receive a unique reference number straight away.', 'mnm-foundation-theme' ); ?></p>
			<ul class="ticks">
				<li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Choose your race category and T-shirt size', 'mnm-foundation-theme' ); ?></span></li>
				<li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Give an emergency contact for race day', 'mnm-foundation-theme' ); ?></span></li>
				<li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Your details are kept private and never shown publicly', 'mnm-foundation-theme' ); ?></span></li>
			</ul>
		</div>
		<div class="form-card reveal"><?php echo function_exists( 'mnm_render_registration_form' ) ? mnm_render_registration_form() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
</section>

<?php if ( $updates ) : ?>
<section class="section">
	<div class="wrap wrap--narrow">
		<?php mnm_t_section_head( __( 'Event updates', 'mnm-foundation-theme' ), __( 'Latest marathon news', 'mnm-foundation-theme' ) ); ?>
		<ol class="timeline">
			<?php foreach ( $updates as $u ) : ?>
				<li class="reveal">
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $u ) ); ?>"><?php echo esc_html( get_the_date( '', $u ) ); ?></time>
					<h3><?php echo esc_html( get_the_title( $u ) ); ?> <?php echo mnm_t_sample_badge( $u->ID, __( 'Sample', 'mnm-foundation-theme' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<div class="prose"><?php echo wp_kses_post( apply_filters( 'the_content', $u->post_content ) ); ?></div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<?php endif; ?>

<?php if ( $gallery ) : ?>
<section class="section section--grey">
	<div class="wrap">
		<?php mnm_t_section_head( __( 'Gallery', 'mnm-foundation-theme' ), __( 'Marathon photographs', 'mnm-foundation-theme' ) ); ?>
		<?php echo mnm_render_gallery( array( 'collection' => 'marathon', 'filters' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'parts/cta-band' ); ?>
<?php
get_footer();
