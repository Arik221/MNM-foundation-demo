<?php
/**
 * Template Name: Get Involved
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
mnm_t_page_header( __( 'Get involved', 'mnm-foundation-theme' ), __( 'Be part of the story', 'mnm-foundation-theme' ), __( 'Donate, volunteer or run with us. Every contribution helps a child stay in school or a villager learn a new skill.', 'mnm-foundation-theme' ) );
$campaigns = get_posts( array( 'post_type' => 'mnm_campaign', 'posts_per_page' => 4 ) );
$phone     = mnm_t_setting( 'phone' );
?>
<section class="section" id="donate"><div class="wrap">
	<?php mnm_t_section_head( __( 'Donate', 'mnm-foundation-theme' ), __( 'Support a campaign', 'mnm-foundation-theme' ), __( 'Progress bars show amounts our team has verified by hand. They are not live payment counters.', 'mnm-foundation-theme' ) ); ?>
	<div class="grid grid--2">
		<?php foreach ( $campaigns as $c ) : ?>
			<div class="reveal"><?php echo mnm_render_progress( $c->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endforeach; ?>
	</div>
	<div class="notice-box reveal">
		<strong><?php esc_html_e( 'Online payments are coming soon.', 'mnm-foundation-theme' ); ?></strong>
		<span><?php esc_html_e( 'This prototype does not take payments. To donate for now, please call or write to the foundation.', 'mnm-foundation-theme' ); ?>
			<?php if ( $phone ) : ?><a href="<?php echo esc_url( mnm_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php endif; ?></span>
	</div>
</div></section>

<section class="section section--grey" id="volunteer"><div class="wrap two-col">
	<div class="reveal">
		<p class="eyebrow"><?php esc_html_e( 'Volunteer', 'mnm-foundation-theme' ); ?></p>
		<h2 class="h2"><?php esc_html_e( 'Offer your time and skills', 'mnm-foundation-theme' ); ?></h2>
		<p class="lede"><?php esc_html_e( 'Help at an event, support a training centre, share your expertise, or spread the word. Tell us how you would like to help.', 'mnm-foundation-theme' ); ?></p>
		<ul class="ticks">
			<li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Marathon race-day marshals and helpers', 'mnm-foundation-theme' ); ?></span></li>
			<li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Computer trainers and assistants', 'mnm-foundation-theme' ); ?></span></li>
			<li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Community ambassadors in your village', 'mnm-foundation-theme' ); ?></span></li>
		</ul>
	</div>
	<div class="reveal"><?php echo mnm_render_contact_form( 'Volunteering' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</div></section>
<?php
get_footer();
