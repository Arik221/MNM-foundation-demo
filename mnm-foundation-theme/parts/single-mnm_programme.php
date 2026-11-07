<?php
/**
 * Single programme.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$id       = get_the_ID();
$accent   = get_post_meta( $id, '_mnm_accent', true );
$accent   = $accent ? $accent : 'blue';
$icon     = get_post_meta( $id, '_mnm_icon', true );
$audience = get_post_meta( $id, '_mnm_audience', true );
$focus    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $id, '_mnm_focus', true ) ) ) );
mnm_t_page_header( __( 'Programme', 'mnm-foundation-theme' ), get_the_title(), get_post_meta( $id, '_mnm_tagline', true ), 'page-hero--' . $accent );
?>
<section class="section section--tight">
	<div class="wrap detail-grid">
		<div class="detail-main">
			<?php if ( has_post_thumbnail() ) : ?><div class="detail-media"><?php the_post_thumbnail( 'mnm-wide' ); ?></div><?php endif; ?>
			<div class="prose"><?php the_content(); ?></div>
			<?php if ( $focus ) : ?>
				<h2 class="h3"><?php esc_html_e( 'What this programme does', 'mnm-foundation-theme' ); ?></h2>
				<ul class="ticks"><?php foreach ( $focus as $f ) : ?><li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php echo esc_html( $f ); ?></span></li><?php endforeach; ?></ul>
			<?php endif; ?>
		</div>
		<aside class="detail-aside accent-<?php echo esc_attr( $accent ); ?>">
			<span class="card__icon"><?php echo mnm_t_icon( $icon ? $icon : 'heart', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php if ( $audience ) : ?><h2 class="h4"><?php esc_html_e( 'Who it serves', 'mnm-foundation-theme' ); ?></h2><p><?php echo esc_html( $audience ); ?></p><?php endif; ?>
			<a class="btn btn--dark btn--block" href="<?php echo esc_url( function_exists( 'mnm_donate_url' ) ? mnm_donate_url() : '#' ); ?>"><?php esc_html_e( 'Support this programme', 'mnm-foundation-theme' ); ?></a>
		</aside>
	</div>
</section>
<?php
$related = get_posts( array( 'post_type' => 'mnm_project', 'posts_per_page' => 3, 'meta_key' => '_mnm_programme', 'meta_value' => $id ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
if ( $related ) :
	?>
<section class="section section--grey"><div class="wrap">
	<?php mnm_t_section_head( '', __( 'Projects under this programme', 'mnm-foundation-theme' ) ); ?>
	<div class="grid grid--3"><?php foreach ( $related as $r ) { mnm_t_card_project( $r->ID ); } ?></div>
</div></section>
<?php endif; ?>
