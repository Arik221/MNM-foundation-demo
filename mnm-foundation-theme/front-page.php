<?php
/**
 * Homepage.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();

$hero_id   = (int) mnm_t_setting( 'hero_image', 0 );
$hero_video = (string) mnm_t_setting( 'hero_video', '' );
$memorials = function_exists( 'mnm_get_memorials' ) ? mnm_get_memorials() : array();
$mission   = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) mnm_t_setting( 'mission' ) ) ) ) );
$marathon  = get_page_by_path( 'marathon' );
$about     = get_page_by_path( 'about' );
$camp_id   = (int) mnm_t_setting( 'marathon_campaign', 0 );
$reg_count = function_exists( 'mnm_reg_count' ) ? mnm_reg_count() : 0;
$heading   = (string) mnm_t_setting( 'hero_heading' );
$parts     = preg_split( '/(?<=\.)\s+/', $heading, 2 );
?>

<?php
$camp   = ( $camp_id && function_exists( 'mnm_campaign_data' ) ) ? mnm_campaign_data( $camp_id ) : null;
$next   = function_exists( 'mnm_events_query' ) ? mnm_events_query( 'upcoming', 1 ) : null;
$circ   = 326.7;
?>
<section class="hero<?php echo ( $hero_id || $hero_video ) ? ' hero--photo' : ''; ?>" data-hero>
	<?php if ( $hero_video ) : ?>
		<video class="hero__photo" autoplay muted loop playsinline preload="metadata"<?php echo $hero_id ? ' poster="' . esc_url( wp_get_attachment_image_url( $hero_id, 'mnm-wide' ) ) . '"' : ''; ?> src="<?php echo esc_url( $hero_video ); ?>"></video>
	<?php elseif ( $hero_id ) : ?>
		<?php echo wp_get_attachment_image( $hero_id, 'mnm-wide', false, array( 'class' => 'hero__photo', 'fetchpriority' => 'high', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php endif; ?>
	<div class="mesh" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
	<div class="hero__glow" data-follow aria-hidden="true"></div>
	<div class="grain" aria-hidden="true"></div>

	<div class="wrap hero__grid">
		<div class="hero__inner">
			<p class="hero__kicker"><?php echo esc_html( mnm_t_setting( 'tagline', 'Fund and Fight' ) ); ?>, Ukhrul, Manipur</p>
			<h1 class="hero__title" aria-label="<?php echo esc_attr( $heading ); ?>">
				<span class="hero__line" data-split aria-hidden="true"><?php echo esc_html( $parts[0] ); ?></span>
				<?php if ( ! empty( $parts[1] ) ) : ?><span class="hero__line hero__line--accent" data-split aria-hidden="true"><?php echo esc_html( $parts[1] ); ?></span><?php endif; ?>
			</h1>
			<p class="hero__text"><?php echo esc_html( mnm_t_setting( 'hero_text' ) ); ?></p>
			<div class="hero__cta">
				<a class="btn btn--coral btn--lg" data-magnetic href="<?php echo esc_url( function_exists( 'mnm_donate_url' ) ? mnm_donate_url() : '#' ); ?>"><?php echo mnm_t_icon( 'heart', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Donate', 'mnm-foundation-theme' ); ?></a>
				<?php if ( $marathon ) : ?><a class="btn btn--glass btn--lg" data-magnetic href="<?php echo esc_url( get_permalink( $marathon ) ); ?>"><?php esc_html_e( 'Join the marathon', 'mnm-foundation-theme' ); ?> <?php echo mnm_t_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?>
			</div>
		</div>

		<div class="hero__stage" data-stage>
			<div class="fcard fcard--mark" data-depth="0.5"><div class="fcard__in">
				<svg class="fcard__ring" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="ring-path" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0"/></defs><text><textPath href="#ring-path" textLength="494" lengthAdjust="spacing">Fund and Fight • Fund and Fight • Fund and Fight • </textPath></text></svg>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/mark.svg' ); ?>" width="96" height="110" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
			</div></div>

			<?php if ( $camp ) : ?>
			<div class="fcard fcard--fund" data-depth="0.9"><div class="fcard__in">
				<p class="fcard__label"><?php esc_html_e( 'Marathon fund', 'mnm-foundation-theme' ); ?> <?php echo $camp['demo'] && function_exists( 'mnm_demo_badge' ) ? mnm_demo_badge( __( 'Sample', 'mnm-foundation-theme' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<div class="fcard__row">
					<svg class="ring" viewBox="0 0 120 120" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: percent. */ __( '%d percent of target', 'mnm-foundation-theme' ), $camp['percent'] ) ); ?>"><circle class="ring__bg" cx="60" cy="60" r="52"/><circle class="ring__fg" cx="60" cy="60" r="52" style="--o:<?php echo esc_attr( round( $circ * ( 1 - $camp['percent'] / 100 ), 1 ) ); ?>"/><text x="60" y="68" text-anchor="middle"><?php echo esc_html( $camp['percent'] ); ?>%</text></svg>
					<div><strong class="fcard__big"><?php echo esc_html( mnm_format_money( $camp['raised'], $camp['symbol'] ) ); ?></strong><span class="fcard__sub"><?php echo esc_html( sprintf( /* translators: %s: amount. */ __( 'of %s target, manually verified', 'mnm-foundation-theme' ), mnm_format_money( $camp['target'], $camp['symbol'] ) ) ); ?></span></div>
				</div>
			</div></div>
			<?php endif; ?>

			<?php if ( $next && $next->have_posts() ) : $next->the_post(); $nd = mnm_t_event_dates( get_the_ID() ); ?>
			<a class="fcard fcard--event" data-depth="0.7" href="<?php the_permalink(); ?>"><div class="fcard__in">
				<p class="fcard__label"><?php esc_html_e( 'Next up', 'mnm-foundation-theme' ); ?></p>
				<div class="fcard__row">
					<?php if ( $nd ) : ?><span class="fcard__date"><b><?php echo esc_html( $nd['day'] ); ?></b><?php echo esc_html( $nd['month'] ); ?></span><?php endif; ?>
					<strong class="fcard__ev"><?php the_title(); ?></strong>
				</div>
			</div></a>
			<?php wp_reset_postdata(); endif; ?>
		</div>
	</div>
	<a class="hero__cue" href="#statement" aria-label="<?php esc_attr_e( 'Scroll to content', 'mnm-foundation-theme' ); ?>"><span class="hero__cue-arrow" aria-hidden="true"><?php echo mnm_t_icon( 'arrow', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></a>
</section>

<div class="marquee" aria-hidden="true">
	<div class="marquee__track">
		<?php for ( $r = 0; $r < 2; $r++ ) : ?>
			<ul>
				<?php foreach ( array( 'Fund and Fight', 'Education for every child', 'Digital literacy', 'Stronger communities', 'Pay it forward' ) as $w ) : ?>
					<li><?php echo esc_html( $w ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endfor; ?>
	</div>
</div>

<section class="section statement" id="statement">
	<div class="wrap">
		<p class="statement__text" data-words><?php echo esc_html( mnm_t_setting( 'statement' ) ); ?></p>
		<div class="statement__row">
			<div class="reveal">
				<p class="lede"><?php echo esc_html( mnm_t_setting( 'intro' ) ); ?></p>
				<?php if ( $about ) : ?><a class="pill-link" href="<?php echo esc_url( get_permalink( $about ) ); ?>"><span><?php esc_html_e( 'Read our story', 'mnm-foundation-theme' ); ?></span><i><?php echo mnm_t_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></i></a><?php endif; ?>
			</div>
			<?php if ( $memorials ) : ?>
				<ul class="faces reveal" aria-label="<?php esc_attr_e( 'In loving memory', 'mnm-foundation-theme' ); ?>">
					<?php foreach ( $memorials as $m ) : ?>
						<li data-tilt>
							<?php if ( ! empty( $m['photo'] ) ) { echo wp_get_attachment_image( $m['photo'], 'medium', false, array( 'alt' => '', 'loading' => 'lazy' ) ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span><strong><?php echo esc_html( $m['name'] ); ?></strong><em><?php echo esc_html( $m['years'] ); ?></em></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section stack-section">
	<div class="wrap">
		<div class="stack">
			<article class="stack__card stack__card--teal">
				<span class="stack__no" aria-hidden="true">Mission</span>
				<h2 class="h2"><?php esc_html_e( 'Education first. Skills for everyone.', 'mnm-foundation-theme' ); ?></h2>
				<ul class="ticks ticks--light">
					<?php foreach ( $mission as $line ) : ?><li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php echo esc_html( $line ); ?></span></li><?php endforeach; ?>
				</ul>
			</article>
			<article class="stack__card stack__card--mint">
				<span class="stack__no" aria-hidden="true">Vision</span>
				<h2 class="h2"><?php esc_html_e( 'A better future, within reach.', 'mnm-foundation-theme' ); ?></h2>
				<p><?php echo esc_html( mnm_t_setting( 'vision' ) ); ?></p>
			</article>
			<article class="stack__card stack__card--sun">
				<span class="stack__no" aria-hidden="true">Dream</span>
				<h2 class="h2"><?php esc_html_e( 'Every child helps the next.', 'mnm-foundation-theme' ); ?></h2>
				<p><?php echo esc_html( mnm_t_setting( 'dream' ) ); ?></p>
			</article>
		</div>
	</div>
</section>

<?php if ( function_exists( 'mnm_render_stats' ) && mnm_render_stats() ) : ?>
<section class="section section--night stats">
	<div class="orb orb--a" aria-hidden="true"></div><div class="orb orb--b" aria-hidden="true"></div>
	<div class="wrap">
		<h2 class="h2 reveal" data-split><?php esc_html_e( 'Our reach, in numbers', 'mnm-foundation-theme' ); ?></h2>
		<div class="reveal"><?php echo mnm_render_stats(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
</section>
<?php endif; ?>

<?php
$programmes = get_posts( array( 'post_type' => 'mnm_programme', 'posts_per_page' => 3, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
if ( $programmes ) :
	?>
<section class="section tabs-section">
	<div class="wrap">
		<?php mnm_t_section_head( '', __( 'Three ways we empower communities', 'mnm-foundation-theme' ), __( 'Education, digital skills and a cycle of support that grows with every child we help.', 'mnm-foundation-theme' ), array( __( 'All programmes', 'mnm-foundation-theme' ), get_post_type_archive_link( 'mnm_programme' ) ) ); ?>
		<div class="tabs reveal" data-tabs data-accent="<?php echo esc_attr( get_post_meta( $programmes[0]->ID, '_mnm_accent', true ) ? get_post_meta( $programmes[0]->ID, '_mnm_accent', true ) : 'blue' ); ?>">
			<div class="tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Programmes', 'mnm-foundation-theme' ); ?>">
				<?php foreach ( $programmes as $i => $p ) : $ic = get_post_meta( $p->ID, '_mnm_icon', true ); ?>
					<button type="button" role="tab" id="tab-<?php echo (int) $p->ID; ?>" aria-controls="panel-<?php echo (int) $p->ID; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>"><?php echo mnm_t_icon( $ic ? $ic : 'heart', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( get_the_title( $p ) ); ?></span></button>
				<?php endforeach; ?>
			</div>
			<div class="tabs__panels">
				<?php
				foreach ( $programmes as $i => $p ) :
					$ac    = get_post_meta( $p->ID, '_mnm_accent', true );
					$focus = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $p->ID, '_mnm_focus', true ) ) ) );
					?>
					<div class="tabs__panel" role="tabpanel" id="panel-<?php echo (int) $p->ID; ?>" aria-labelledby="tab-<?php echo (int) $p->ID; ?>" data-accent="<?php echo esc_attr( $ac ? $ac : 'blue' ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
						<div class="tabs__text">
							<h3 class="h3"><?php echo esc_html( get_post_meta( $p->ID, '_mnm_tagline', true ) ? get_post_meta( $p->ID, '_mnm_tagline', true ) : get_the_title( $p ) ); ?></h3>
							<p class="lede"><?php echo esc_html( get_the_excerpt( $p ) ); ?></p>
							<?php if ( $focus ) : ?><ul class="ticks"><?php foreach ( array_slice( $focus, 0, 3 ) as $fo ) : ?><li><?php echo mnm_t_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php echo esc_html( $fo ); ?></span></li><?php endforeach; ?></ul><?php endif; ?>
							<a class="pill-link" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><span><?php esc_html_e( 'Explore programme', 'mnm-foundation-theme' ); ?></span><i><?php echo mnm_t_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></i></a>
						</div>
						<div class="tabs__media notch"><?php echo mnm_t_thumb( $p->ID, 'mnm-card' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
$projects = get_posts( array( 'post_type' => 'mnm_project', 'posts_per_page' => 3 ) );
if ( $projects ) :
	?>
<section class="section section--mist">
	<div class="wrap">
		<?php mnm_t_section_head( '', __( 'Projects on the ground', 'mnm-foundation-theme' ), '', array( __( 'All projects', 'mnm-foundation-theme' ), get_post_type_archive_link( 'mnm_project' ) ) ); ?>
		<div class="bento persp">
			<?php foreach ( $projects as $p ) { mnm_t_card_project( $p->ID ); } ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section spotlight">
	<div class="wrap spotlight__grid">
		<div class="spotlight__body reveal">
			<h2 class="h2" data-split><?php echo esc_html( mnm_t_setting( 'marathon_tagline', 'Run for a reason.' ) ); ?></h2>
			<p class="lede"><?php echo esc_html( wp_trim_words( (string) mnm_t_setting( 'marathon_text' ), 34 ) ); ?></p>
			<div class="btn-row">
				<?php if ( $marathon ) : ?><a class="btn btn--dark btn--lg" data-magnetic href="<?php echo esc_url( get_permalink( $marathon ) . '#register' ); ?>"><?php esc_html_e( 'Register to run', 'mnm-foundation-theme' ); ?> <?php echo mnm_t_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?>
				<?php if ( $reg_count ) : ?><span class="regcount"><strong data-mnm-regcount><?php echo (int) $reg_count; ?></strong> <?php esc_html_e( 'runners registered', 'mnm-foundation-theme' ); ?> <?php echo function_exists( 'mnm_demo_badge' ) ? mnm_demo_badge( __( 'Demo', 'mnm-foundation-theme' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?>
			</div>
		</div>
		<div class="spotlight__stage reveal">
			<?php
			$banner = (int) mnm_t_setting( 'marathon_banner', 0 );
			if ( $banner ) :
				?>
				<div class="spotlight__photo" data-parallax="0.06"><?php echo wp_get_attachment_image( $banner, 'large', false, array( 'loading' => 'lazy', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
			<div class="spotlight__card" data-tilt>
				<?php if ( $camp_id && function_exists( 'mnm_render_progress' ) ) { echo mnm_render_progress( $camp_id, array( 'title' => true ) ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<svg class="badge" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="circ" d="M100,100 m-78,0 a78,78 0 1,1 156,0 a78,78 0 1,1 -156,0"/></defs><text><textPath href="#circ" textLength="486" lengthAdjust="spacing">Run for a child's education • Run for a child's education • </textPath></text><circle cx="100" cy="100" r="30"/></svg>
		</div>
	</div>
</section>

<?php
$events = function_exists( 'mnm_events_query' ) ? mnm_events_query( 'upcoming', 3 ) : null;
if ( $events && $events->have_posts() ) :
	?>
<section class="section">
	<div class="wrap">
		<?php mnm_t_section_head( '', __( 'Coming up', 'mnm-foundation-theme' ), '', array( __( 'All events', 'mnm-foundation-theme' ), get_post_type_archive_link( 'mnm_event' ) ) ); ?>
		<div class="event-list">
			<?php
			while ( $events->have_posts() ) {
				$events->the_post();
				mnm_t_card_event( get_the_ID() );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
$feat    = get_posts( array( 'post_type' => 'mnm_article', 'posts_per_page' => 1, 'meta_key' => '_mnm_featured', 'meta_value' => '1' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
$feat_id = $feat ? $feat[0]->ID : 0;
$more    = get_posts( array( 'post_type' => 'mnm_article', 'posts_per_page' => 2, 'post__not_in' => $feat_id ? array( $feat_id ) : array() ) );
if ( $feat_id || $more ) :
	?>
<section class="section section--mist">
	<div class="wrap">
		<?php mnm_t_section_head( '', __( 'Latest from the foundation', 'mnm-foundation-theme' ), '', array( __( 'All articles', 'mnm-foundation-theme' ), get_post_type_archive_link( 'mnm_article' ) ) ); ?>
		<div class="news-grid persp">
			<?php
			if ( $feat_id ) {
				mnm_t_card_article( $feat_id, true );
			}
			?>
			<div class="news-grid__side">
				<?php foreach ( $more as $a ) { mnm_t_card_article( $a->ID ); } ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( function_exists( 'mnm_gallery_items' ) && mnm_gallery_items( array( 'limit' => 1 ) ) ) : ?>
<section class="section">
	<div class="wrap">
		<?php mnm_t_section_head( '', __( 'Moments from our work', 'mnm-foundation-theme' ), '', array( __( 'Open the gallery', 'mnm-foundation-theme' ), home_url( '/gallery/' ) ) ); ?>
	</div>
	<?php $reel = mnm_gallery_items( array( 'limit' => 12 ) ); ?>
	<div class="wrap"><div class="reels"><?php mnm_t_reel( array_slice( $reel, 0, 7 ) ); mnm_t_reel( array_reverse( array_slice( $reel, 3 ) ), true ); ?></div></div>
</section>
<?php endif; ?>

<?php get_template_part( 'parts/cta-band' ); ?>

<?php
get_footer();
