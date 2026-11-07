<?php
/**
 * Template tags: safe accessors for plugin data, icons, cards.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a foundation setting (falls back gracefully when the plugin is inactive).
 *
 * @param string $key     Key.
 * @param mixed  $default Default.
 * @return mixed
 */
function mnm_t_setting( $key, $default = '' ) {
	if ( function_exists( 'mnm_setting' ) ) {
		return mnm_setting( $key, $default );
	}
	$fallback = array(
		'org_name'     => 'MNM Foundation',
		'phone'        => '+91 98190 32215',
		'address'      => "H/No. 25, Kahum Tang,\nUkhrul, Manipur - 795142",
		'hero_heading' => 'Empowering Communities. Transforming Futures.',
	);
	return isset( $fallback[ $key ] ) ? $fallback[ $key ] : $default;
}

/**
 * Announcement bar data or false.
 *
 * @return array|false
 */
function mnm_t_announcement() {
	if ( ! mnm_t_setting( 'announcement_on' ) || ! mnm_t_setting( 'announcement_text' ) ) {
		return false;
	}
	return array( 'text' => mnm_t_setting( 'announcement_text' ), 'link' => mnm_t_setting( 'announcement_link' ) );
}

/**
 * Inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @return string
 */
function mnm_t_icon( $name, $size = 24 ) {
	$paths = array(
		'book'      => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/><path d="M9 8h7"/>',
		'laptop'    => '<rect x="4" y="4" width="16" height="11" rx="2"/><path d="M2 19h20"/><path d="M9 15v4h6v-4"/>',
		'people'    => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17 14.2c2.5.3 4.5 2.3 4.5 5.1"/>',
		'heart'     => '<path d="M12 21s-7.5-4.6-9.5-9.2C1.2 8.600 3 5.500 6.200 5.500c1.900 0 3.200 1 3.800 2.300.6-1.300 1.900-2.300 3.800-2.300 3.200 0 5 3.100 3.700 6.300C19.500 16.400 12 21 12 21Z"/>',
		'calendar'  => '<rect x="3.500" y="5" width="17" height="15.500" rx="2"/><path d="M3.500 10h17M8 3v4M16 3v4"/>',
		'pin'       => '<path d="M12 21s7-6.100 7-11.500A7 7 0 0 0 5 9.500C5 14.900 12 21 12 21Z"/><circle cx="12" cy="9.500" r="2.500"/>',
		'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'phone'     => '<path d="M5 4h4l2 5-2.500 1.500a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/>',
		'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.500 7 8.500 6.500L20.500 7"/>',
		'home'      => '<path d="M3.5 11.2 12 4l8.5 7.2"/><path d="M5.5 9.8V20h13V9.8"/><path d="M10 20v-5.5h4V20"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
		'check'     => '<path d="m5 12.500 4.500 4.500L19 7.500"/>',
		'facebook'  => '<path d="M14 8.500V7c0-.8.300-1.500 1.500-1.500H17V2.500h-2.500C11.800 2.500 10.500 4.200 10.500 6.800v1.700H8v3h2.500v9h3.500v-9h2.600l.4-3H14Z" fill="currentColor" stroke="none"/>',
		'instagram' => '<rect x="3.500" y="3.500" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r=".8" fill="currentColor"/>',
		'youtube'   => '<rect x="2.500" y="5.500" width="19" height="13" rx="4"/><path d="m10 9.500 5 2.500-5 2.500v-5Z" fill="currentColor"/>',
		'x_twitter' => '<path d="M4 4l16 16M20 4 4 20"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf( '<svg class="icon icon-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>', esc_attr( $name ), (int) $size, $paths[ $name ] ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Thumbnail or a brand-coloured placeholder.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @return string
 */
function mnm_t_thumb( $post_id, $size = 'mnm-card' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	}
	return '<span class="ph" role="img" aria-label="' . esc_attr__( 'Image placeholder', 'mnm-foundation-theme' ) . '"></span>';
}

/**
 * Section heading block.
 *
 * @param string $eyebrow Unused (kept for compatibility).
 * @param string $title   Title.
 * @param string $text    Intro text.
 * @param array  $link    [label, url].
 */
function mnm_t_section_head( $eyebrow, $title, $text = '', $link = array() ) {
	echo '<header class="section-head reveal">';
	echo '<div>';
	echo '<h2 class="h2" data-split>' . esc_html( $title ) . '</h2>';
	if ( $text ) {
		echo '<p class="lede">' . esc_html( $text ) . '</p>';
	}
	echo '</div>';
	if ( $link ) {
		echo '<a class="pill-link" href="' . esc_url( $link[1] ) . '"><span>' . esc_html( $link[0] ) . '</span><i>' . mnm_t_icon( 'arrow', 18 ) . '</i></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</header>';
}

/**
 * SVG path for a hill ridge.
 *
 * @param float $seed Phase.
 * @param float $base Baseline (0-700).
 * @param float $amp  Amplitude.
 * @return string
 */
function mnm_t_ridge_path( $seed, $base, $amp ) {
	$w = 1600;
	$h = 700;
	$d = 'M0,' . $h . ' L';
	$pts = array();
	for ( $x = 0; $x <= $w; $x += 20 ) {
		$t     = $x / $w;
		$y     = $base - $amp * ( sin( $t * 5.1 + $seed ) * 0.62 + sin( $t * 11.3 + $seed * 1.7 ) * 0.27 + sin( $t * 23 + $seed * 0.5 ) * 0.07 );
		$pts[] = $x . ',' . round( $y, 1 );
	}
	return $d . implode( ' L', $pts ) . ' L' . $w . ',' . $h . ' Z';
}

/**
 * Layered hill artwork used behind hero headings. Layers carry data-depth for parallax.
 *
 * @param string $class Extra class.
 * @return string
 */
function mnm_t_ridges( $class = '' ) {
	$layers = array(
		array( 'c' => '#5FA3A9', 'o' => '.55', 'seed' => 1.1, 'base' => 330, 'amp' => 120, 'depth' => '.04' ),
		array( 'c' => '#3F8590', 'o' => '.75', 'seed' => 2.4, 'base' => 400, 'amp' => 110, 'depth' => '.08' ),
		array( 'c' => '#2A6674', 'o' => '.9', 'seed' => 3.7, 'base' => 470, 'amp' => 100, 'depth' => '.14' ),
		array( 'c' => '#164552', 'o' => '1', 'seed' => 5.0, 'base' => 540, 'amp' => 90, 'depth' => '.22' ),
		array( 'c' => '#0A2A32', 'o' => '1', 'seed' => 6.3, 'base' => 610, 'amp' => 70, 'depth' => '.34' ),
	);
	$out = '<div class="ridges ' . esc_attr( $class ) . '" aria-hidden="true">';
	foreach ( $layers as $l ) {
		$out .= '<svg class="ridge" data-depth="' . $l['depth'] . '" viewBox="0 0 1600 700" preserveAspectRatio="none" focusable="false"><path d="' . mnm_t_ridge_path( $l['seed'], $l['base'], $l['amp'] ) . '" fill="' . $l['c'] . '" fill-opacity="' . $l['o'] . '"/></svg>';
	}
	return $out . '</div>';
}

/**
 * Page header used by inner pages (dark dawn band with hills).
 *
 * @param string $eyebrow Unused.
 * @param string $title   Title.
 * @param string $lede    Lede.
 * @param string $class   Extra class.
 */
function mnm_t_page_header( $eyebrow, $title, $lede = '', $class = '' ) {
	echo '<header class="page-hero ' . esc_attr( $class ) . '" data-hero-lite><div class="mesh" aria-hidden="true"><i></i><i></i><i></i></div><div class="page-hero__ring" data-depth=".25" aria-hidden="true"><span></span><span></span><span></span></div><div class="grain" aria-hidden="true"></div><div class="wrap page-hero__inner">';
	if ( $eyebrow ) {
		echo '<p class="crumb">' . esc_html( $eyebrow ) . '</p>';
	}
	echo '<h1 class="h1" data-split>' . esc_html( $title ) . '</h1>';
	if ( $lede ) {
		echo '<p class="lede">' . esc_html( $lede ) . '</p>';
	}
	echo '</div></header>';
}

/**
 * Sample label for flagged posts.
 *
 * @param int    $post_id Post ID.
 * @param string $text    Label.
 * @return string
 */
function mnm_t_sample_badge( $post_id, $text = '' ) {
	if ( ! function_exists( 'mnm_demo_badge' ) || ! get_post_meta( $post_id, '_mnm_demo', true ) ) {
		return '';
	}
	return mnm_demo_badge( $text ? $text : __( 'Sample', 'mnm-foundation-theme' ) );
}

/**
 * Formatted event date parts.
 *
 * @param int $post_id Event ID.
 * @return array|null
 */
function mnm_t_event_dates( $post_id ) {
	$start = get_post_meta( $post_id, '_mnm_start', true );
	if ( ! $start ) {
		return null;
	}
	$ts  = strtotime( $start );
	$end = get_post_meta( $post_id, '_mnm_end', true );
	return array(
		'day'      => wp_date( 'j', $ts ),
		'month'    => wp_date( 'M', $ts ),
		'year'     => wp_date( 'Y', $ts ),
		'full'     => wp_date( 'l, j F Y', $ts ),
		'time'     => wp_date( 'g:i a', $ts ) . ( $end ? ' – ' . wp_date( 'g:i a', strtotime( $end ) ) : '' ),
		'upcoming' => $ts >= current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
	);
}

/**
 * Programme card.
 *
 * @param int $id Post ID.
 */
function mnm_t_card_programme( $id ) {
	$accent = get_post_meta( $id, '_mnm_accent', true );
	$accent = $accent ? $accent : 'blue';
	$icon   = get_post_meta( $id, '_mnm_icon', true );
	$icon   = $icon ? $icon : 'heart';
	?>
	<article class="card card--programme accent-<?php echo esc_attr( $accent ); ?> reveal" data-tilt>
		<a class="card__media" href="<?php echo esc_url( get_permalink( $id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo mnm_t_thumb( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<div class="card__body">
			<span class="card__icon"><?php echo mnm_t_icon( $icon, 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<h3 class="card__title"><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></h3>
			<p><?php echo esc_html( get_the_excerpt( $id ) ); ?></p>
			<a class="link-arrow" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php esc_html_e( 'Learn more', 'mnm-foundation-theme' ); ?> <?php echo mnm_t_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
	</article>
	<?php
}

/**
 * Project card (image tile with overlaid text).
 *
 * @param int $id Post ID.
 */
function mnm_t_card_project( $id ) {
	$cats   = get_the_terms( $id, 'mnm_project_cat' );
	$locs   = get_the_terms( $id, 'mnm_project_location' );
	$status = get_post_meta( $id, '_mnm_status', true );
	$labels = array( 'ongoing' => __( 'Ongoing', 'mnm-foundation-theme' ), 'completed' => __( 'Completed', 'mnm-foundation-theme' ), 'planned' => __( 'Planned', 'mnm-foundation-theme' ) );
	?>
	<article class="tile reveal" data-tilt>
		<a class="tile__link" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
			<span class="tile__media"><?php echo mnm_t_thumb( $id, 'mnm-card' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="tile__top">
				<?php if ( $status && isset( $labels[ $status ] ) ) : ?><span class="chip status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $labels[ $status ] ); ?></span><?php endif; ?>
				<?php echo mnm_t_sample_badge( $id, __( 'Sample', 'mnm-foundation-theme' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
			<span class="tile__body">
				<span class="tile__meta">
					<?php if ( $cats && ! is_wp_error( $cats ) ) { echo esc_html( $cats[0]->name ); } ?>
					<?php if ( $locs && ! is_wp_error( $locs ) ) { echo ' · ' . esc_html( $locs[0]->name ); } ?>
				</span>
				<span class="tile__title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
				<span class="tile__go"><?php echo mnm_t_icon( 'arrow', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</span>
		</a>
	</article>
	<?php
}

/**
 * Event row.
 *
 * @param int $id Post ID.
 */
function mnm_t_card_event( $id ) {
	$d     = mnm_t_event_dates( $id );
	$venue = get_post_meta( $id, '_mnm_venue', true );
	?>
	<article class="event-row reveal<?php echo ( $d && ! $d['upcoming'] ) ? ' is-past' : ''; ?>">
		<?php if ( $d ) : ?>
			<div class="event-row__date" aria-hidden="true"><strong><?php echo esc_html( $d['day'] ); ?></strong><span><?php echo esc_html( $d['month'] ); ?></span><small><?php echo esc_html( $d['year'] ); ?></small></div>
		<?php endif; ?>
		<div class="event-row__body">
			<h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a> <?php echo mnm_t_sample_badge( $id, __( 'Sample event', 'mnm-foundation-theme' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
			<p class="event-row__meta">
				<?php if ( $d ) : ?><span><?php echo mnm_t_icon( 'calendar', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $d['full'] ); ?></span><?php endif; ?>
				<?php if ( $venue ) : ?><span><?php echo mnm_t_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $venue ); ?></span><?php endif; ?>
			</p>
		</div>
		<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php esc_html_e( 'Details', 'mnm-foundation-theme' ); ?></a>
	</article>
	<?php
}

/**
 * Article card. The large variant is an image tile; the small one is a text-led card.
 *
 * @param int  $id    Post ID.
 * @param bool $large Large variant.
 */
function mnm_t_card_article( $id, $large = false ) {
	$cats = get_the_terms( $id, 'mnm_article_cat' );
	$cat  = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
	if ( $large ) :
		?>
	<article class="tile tile--large reveal" data-tilt>
		<a class="tile__link" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
			<span class="tile__media"><?php echo mnm_t_thumb( $id, 'mnm-wide' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<span class="tile__body">
				<span class="tile__meta"><?php echo esc_html( trim( $cat . ' · ' . get_the_date( '', $id ), ' ·' ) ); ?></span>
				<span class="tile__title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
				<span class="tile__text"><?php echo esc_html( get_the_excerpt( $id ) ); ?></span>
				<span class="tile__go"><?php echo mnm_t_icon( 'arrow', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</span>
		</a>
	</article>
		<?php
		return;
	endif;
	?>
	<article class="card card--article reveal">
		<a class="card__media" href="<?php echo esc_url( get_permalink( $id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo mnm_t_thumb( $id, 'mnm-card' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<div class="card__body">
			<p class="card__meta"><?php if ( $cat ) : ?><span><?php echo esc_html( $cat ); ?></span><?php endif; ?><time datetime="<?php echo esc_attr( get_the_date( 'c', $id ) ); ?>"><?php echo esc_html( get_the_date( '', $id ) ); ?></time></p>
			<h3 class="card__title"><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></h3>
			<p><?php echo esc_html( get_the_excerpt( $id ) ); ?></p>
		</div>
	</article>
	<?php
}

/**
 * Auto-scrolling photo reel (a second, hidden copy makes the loop seamless).
 *
 * @param array $items   From mnm_gallery_items().
 * @param bool  $reverse Scroll the other way.
 */
function mnm_t_reel( $items, $reverse = false ) {
	if ( ! $items ) {
		return;
	}
	echo '<div class="reel' . ( $reverse ? ' reel--rev' : '' ) . '"><div class="reel__track">';
	foreach ( array( 0, 1 ) as $copy ) {
		foreach ( $items as $it ) {
			$img = wp_get_attachment_image( $it['id'], 'medium_large', false, array( 'loading' => 'lazy', 'alt' => $copy ? '' : ( $it['alt'] ? $it['alt'] : $it['caption'] ) ) );
			echo '<figure class="reel__item"' . ( $copy ? ' aria-hidden="true"' : '' ) . '>';
			if ( $copy ) {
				echo '<span>' . $img . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				echo '<a href="' . esc_url( $it['full'] ) . '" data-caption="' . esc_attr( $it['caption'] ) . '" data-lightbox>' . $img . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( $it['caption'] ) {
				echo '<figcaption>' . esc_html( wp_trim_words( $it['caption'], 6, '' ) ) . '</figcaption>';
			}
			echo '</figure>';
		}
	}
	echo '</div></div>';
}

/**
 * Pagination for archives.
 */
function mnm_t_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( 'Previous', 'mnm-foundation-theme' ),
			'next_text' => __( 'Next', 'mnm-foundation-theme' ),
		)
	);
}

/**
 * Terms of a taxonomy as filter chips (links) for an archive.
 *
 * @param string $taxonomy Taxonomy.
 * @param string $archive  Archive URL.
 */
function mnm_t_term_filters( $taxonomy, $archive ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}
	echo '<nav class="chips" aria-label="' . esc_attr__( 'Filter', 'mnm-foundation-theme' ) . '">';
	printf( '<a href="%s"%s>%s</a>', esc_url( $archive ), ! is_tax( $taxonomy ) ? ' class="is-active" aria-current="true"' : '', esc_html__( 'All', 'mnm-foundation-theme' ) );
	foreach ( $terms as $t ) {
		printf( '<a href="%s"%s>%s</a>', esc_url( get_term_link( $t ) ), is_tax( $taxonomy, $t->term_id ) ? ' class="is-active" aria-current="true"' : '', esc_html( $t->name ) );
	}
	echo '</nav>';
}
