<?php
/**
 * Template Name: About Us
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
$memorials = function_exists( 'mnm_get_memorials' ) ? mnm_get_memorials() : array();
$values    = function_exists( 'mnm_parse_pairs' ) ? mnm_parse_pairs( mnm_t_setting( 'values' ) ) : array();
$mission   = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) mnm_t_setting( 'mission' ) ) ) );
while ( have_posts() ) :
	the_post();
	mnm_t_page_header( __( 'About us', 'mnm-foundation-theme' ), __( 'A foundation built in memory, for the future', 'mnm-foundation-theme' ), mnm_t_setting( 'intro' ) );
	?>
<section class="section">
	<div class="wrap two-col">
		<div class="reveal">
			<p class="eyebrow"><?php esc_html_e( 'Our origin', 'mnm-foundation-theme' ); ?></p>
			<h2 class="h2"><?php esc_html_e( 'Where it began', 'mnm-foundation-theme' ); ?></h2>
		</div>
		<div class="prose reveal"><?php the_content(); ?></div>
	</div>
</section>

<?php if ( $memorials ) : ?>
<section class="section section--ink memorial" id="memorial">
	<div class="wrap">
		<header class="section-head reveal"><div>
			<p class="eyebrow eyebrow--light"><?php esc_html_e( 'In loving memory', 'mnm-foundation-theme' ); ?></p>
			<h2 class="h2"><?php esc_html_e( 'The three lives behind our name', 'mnm-foundation-theme' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'We remember them, and we work so that their memory becomes a chance for others.', 'mnm-foundation-theme' ); ?></p>
		</div></header>
		<div class="memorial__grid">
			<?php foreach ( $memorials as $m ) : ?>
				<figure class="memorial__card reveal">
					<?php if ( ! empty( $m['photo'] ) ) { echo wp_get_attachment_image( $m['photo'], 'medium', false, array( 'alt' => sprintf( /* translators: %s: name. */ __( 'Portrait of %s', 'mnm-foundation-theme' ), $m['name'] ), 'loading' => 'lazy' ) ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<figcaption>
						<strong><?php echo esc_html( $m['name'] ); ?></strong>
						<span><?php echo esc_html( $m['village'] ); ?></span>
						<span class="years"><?php echo esc_html( $m['years'] ); ?></span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section">
	<div class="wrap mvd__grid">
		<article class="mvd__card mvd__card--blue reveal">
			<span class="mvd__no">01</span><h3><?php esc_html_e( 'Mission', 'mnm-foundation-theme' ); ?></h3>
			<ul class="ticks"><?php foreach ( $mission as $line ) : ?><li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php echo esc_html( $line ); ?></span></li><?php endforeach; ?></ul>
		</article>
		<article class="mvd__card mvd__card--green reveal"><span class="mvd__no">02</span><h3><?php esc_html_e( 'Vision', 'mnm-foundation-theme' ); ?></h3><p><?php echo esc_html( mnm_t_setting( 'vision' ) ); ?></p></article>
		<article class="mvd__card mvd__card--yellow reveal"><span class="mvd__no">03</span><h3><?php esc_html_e( 'Our dream', 'mnm-foundation-theme' ); ?></h3><p><?php echo esc_html( mnm_t_setting( 'dream' ) ); ?></p></article>
	</div>
</section>

<?php if ( $values ) : ?>
<section class="section section--grey">
	<div class="wrap">
		<?php mnm_t_section_head( __( 'What guides us', 'mnm-foundation-theme' ), __( 'Our core values', 'mnm-foundation-theme' ) ); ?>
		<div class="values">
			<?php foreach ( $values as $i => $v ) : ?>
				<div class="value reveal"><span class="value__no"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( $v['label'] ); ?></h3><p><?php echo esc_html( $v['desc'] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
	<?php
endwhile;
get_template_part( 'parts/cta-band' );
get_footer();
