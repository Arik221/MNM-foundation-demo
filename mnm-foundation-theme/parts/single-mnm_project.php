<?php
/**
 * Single project.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
$id      = get_the_ID();
$cats    = get_the_terms( $id, 'mnm_project_cat' );
$locs    = get_the_terms( $id, 'mnm_project_location' );
$status  = get_post_meta( $id, '_mnm_status', true );
$period  = get_post_meta( $id, '_mnm_period', true );
$benef   = get_post_meta( $id, '_mnm_beneficiaries', true );
$prog    = (int) get_post_meta( $id, '_mnm_programme', true );
$outs    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $id, '_mnm_outcomes', true ) ) ) );
$gallery = function_exists( 'mnm_get_gallery_ids' ) ? mnm_get_gallery_ids( $id ) : array();
$labels  = array( 'ongoing' => __( 'Ongoing', 'mnm-foundation-theme' ), 'completed' => __( 'Completed', 'mnm-foundation-theme' ), 'planned' => __( 'Planned', 'mnm-foundation-theme' ) );
mnm_t_page_header( __( 'Project', 'mnm-foundation-theme' ), get_the_title(), get_the_excerpt() );
?>
<section class="section section--tight">
	<div class="wrap detail-grid">
		<div class="detail-main">
			<?php if ( has_post_thumbnail() ) : ?><div class="detail-media"><?php the_post_thumbnail( 'mnm-wide', array( 'fetchpriority' => 'high' ) ); ?></div><?php endif; ?>
			<div class="prose"><?php the_content(); ?></div>
			<?php if ( $outs ) : ?>
				<h2 class="h3"><?php esc_html_e( 'Outcomes and impact', 'mnm-foundation-theme' ); ?></h2>
				<ul class="ticks"><?php foreach ( $outs as $o ) : ?><li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php echo esc_html( $o ); ?></span></li><?php endforeach; ?></ul>
			<?php endif; ?>
			<?php if ( $gallery ) : ?>
				<h2 class="h3"><?php esc_html_e( 'Photographs', 'mnm-foundation-theme' ); ?></h2>
				<?php echo mnm_render_gallery( array( 'ids' => $gallery, 'filters' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		</div>
		<aside class="detail-aside">
			<h2 class="h4"><?php esc_html_e( 'At a glance', 'mnm-foundation-theme' ); ?></h2>
			<dl class="facts">
				<?php if ( $status && isset( $labels[ $status ] ) ) : ?><div><dt><?php esc_html_e( 'Status', 'mnm-foundation-theme' ); ?></dt><dd><span class="chip status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $labels[ $status ] ); ?></span></dd></div><?php endif; ?>
				<?php if ( $locs && ! is_wp_error( $locs ) ) : ?><div><dt><?php esc_html_e( 'Location', 'mnm-foundation-theme' ); ?></dt><dd><a href="<?php echo esc_url( get_term_link( $locs[0] ) ); ?>"><?php echo esc_html( $locs[0]->name ); ?></a></dd></div><?php endif; ?>
				<?php if ( $cats && ! is_wp_error( $cats ) ) : ?><div><dt><?php esc_html_e( 'Category', 'mnm-foundation-theme' ); ?></dt><dd><a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a></dd></div><?php endif; ?>
				<?php if ( $period ) : ?><div><dt><?php esc_html_e( 'Period', 'mnm-foundation-theme' ); ?></dt><dd><?php echo esc_html( $period ); ?></dd></div><?php endif; ?>
				<?php if ( $benef ) : ?><div><dt><?php esc_html_e( 'People supported', 'mnm-foundation-theme' ); ?></dt><dd><?php echo esc_html( number_format_i18n( (float) $benef ) ); ?></dd></div><?php endif; ?>
				<?php if ( $prog && get_post( $prog ) ) : ?><div><dt><?php esc_html_e( 'Programme', 'mnm-foundation-theme' ); ?></dt><dd><a href="<?php echo esc_url( get_permalink( $prog ) ); ?>"><?php echo esc_html( get_the_title( $prog ) ); ?></a></dd></div><?php endif; ?>
			</dl>
			<a class="btn btn--dark btn--block" href="<?php echo esc_url( function_exists( 'mnm_donate_url' ) ? mnm_donate_url() : '#' ); ?>"><?php esc_html_e( 'Support this work', 'mnm-foundation-theme' ); ?></a>
		</aside>
	</div>
</section>
<?php
$related = get_posts( array( 'post_type' => 'mnm_project', 'posts_per_page' => 3, 'post__not_in' => array( $id ) ) );
if ( $related ) :
	?>
<section class="section section--grey"><div class="wrap">
	<?php mnm_t_section_head( '', __( 'More projects', 'mnm-foundation-theme' ), '', array( __( 'All projects', 'mnm-foundation-theme' ), get_post_type_archive_link( 'mnm_project' ) ) ); ?>
	<div class="grid grid--3"><?php foreach ( $related as $r ) { mnm_t_card_project( $r->ID ); } ?></div>
</div></section>
<?php endif; ?>
