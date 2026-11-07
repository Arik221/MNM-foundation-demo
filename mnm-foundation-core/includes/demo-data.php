<?php
/**
 * Demonstration content for the client prototype.
 *
 * Everything created here is flagged with the meta key _mnm_demo so it can be removed in one click.
 * Programme and About copy is based on the foundation's own poster. Projects, events, articles,
 * campaign figures, statistics and registrations are SAMPLE data and are labelled as such.
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrap paragraphs (and optional "## heading" lines) in block markup.
 *
 * @param string[] $parts Strings; prefix with "## " for a heading.
 * @return string
 */
function mnm_blocks( array $parts ) {
	$out = '';
	foreach ( $parts as $p ) {
		if ( 0 === strpos( $p, '## ' ) ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( substr( $p, 3 ) ) . "</h2>\n<!-- /wp:heading -->\n\n";
		} else {
			$out .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return trim( $out );
}

/**
 * Import a bundled demo image into the Media Library (once).
 *
 * @param string $file    File name in assets/demo.
 * @param string $title   Title.
 * @param string $caption Caption.
 * @param string $alt     Alt text.
 * @param string $term    Gallery collection name, optional.
 * @return int Attachment ID.
 */
function mnm_demo_image( $file, $title, $caption = '', $alt = '', $term = '' ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_mnm_demo_file', 'meta_value' => $file, 'fields' => 'ids', 'posts_per_page' => 1 ) );
	if ( $existing ) {
		$id = (int) $existing[0];
	} else {
		$src = file_exists( MNM_CORE_DIR . 'assets/demo/' . $file ) ? MNM_CORE_DIR . 'assets/demo/' . $file : MNM_CORE_DIR . 'assets/demo/photos/' . $file;
		if ( ! file_exists( $src ) ) {
			return 0;
		}
		$up = wp_upload_bits( $file, null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! empty( $up['error'] ) ) {
			return 0;
		}
		$id = wp_insert_attachment(
			array(
				'post_mime_type' => ( wp_check_filetype( $file )['type'] ? wp_check_filetype( $file )['type'] : 'image/jpeg' ),
				'post_title'     => $title,
				'post_excerpt'   => $caption,
				'post_status'    => 'inherit',
			),
			$up['file']
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
		update_post_meta( $id, '_mnm_demo_file', $file );
		update_post_meta( $id, '_mnm_demo', '1' );
	}
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	if ( $term ) {
		$t = term_exists( $term, 'mnm_gallery_cat' );
		if ( ! $t ) {
			$t = wp_insert_term( $term, 'mnm_gallery_cat' );
		}
		if ( ! is_wp_error( $t ) ) {
			wp_set_object_terms( $id, array( (int) ( is_array( $t ) ? $t['term_id'] : $t ) ), 'mnm_gallery_cat' );
		}
	}
	return (int) $id;
}

/**
 * Create (or fetch) a demo post by slug.
 *
 * @param string $type Post type.
 * @param string $slug Slug.
 * @param array  $args Post args.
 * @param array  $meta Meta.
 * @param array  $tax  Taxonomy => term names.
 * @return int
 */
function mnm_demo_post( $type, $slug, array $args, array $meta = array(), array $tax = array() ) {
	$existing = get_page_by_path( $slug, OBJECT, $type );
	if ( $existing ) {
		return (int) $existing->ID;
	}
	$id = wp_insert_post(
		array_merge(
			array(
				'post_type'   => $type,
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_author' => get_current_user_id() ? get_current_user_id() : 1,
			),
			$args
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( $meta as $k => $v ) {
		if ( '' !== $v && null !== $v ) {
			update_post_meta( $id, $k, $v );
		}
	}
	foreach ( $tax as $taxonomy => $names ) {
		wp_set_object_terms( $id, $names, $taxonomy );
	}
	return (int) $id;
}

/**
 * Seed everything. Safe to run repeatedly.
 */
function mnm_seed_demo() {
	mnm_register_post_types();
	$now = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
	$day = function ( $offset, $h = 10 ) use ( $now ) {
		return gmdate( 'Y-m-d', $now + $offset * DAY_IN_SECONDS ) . sprintf( 'T%02d:00', $h );
	};
	$demo = array( '_mnm_demo' => '1' );
	$note = 'Demonstration image: replace with a real photograph.';

	/* ---------- Media ---------- */
	$img = array(
		'hero'      => mnm_demo_image( 'hero-demo.jpg', 'Homepage hero (demo)', $note, 'Illustration of green and blue hills under a yellow sun' ),
		'banner'    => mnm_demo_image( 'marathon-banner-demo.jpg', 'Marathon banner (demo)', $note, 'Illustration of golden hills' ),
		'campaign'  => mnm_demo_image( 'campaign-demo.jpg', 'Campaign image (demo)', $note, 'Illustration of green hills' ),
		'p_edu'     => mnm_demo_image( 'programme-education-demo.jpg', 'Education programme (demo)', $note, 'Illustration of hills' ),
		'p_digital' => mnm_demo_image( 'programme-digital-demo.jpg', 'Digital literacy programme (demo)', $note, 'Illustration of hills at dusk' ),
		'p_comm'    => mnm_demo_image( 'programme-community-demo.jpg', 'Community programme (demo)', $note, 'Illustration of green hills' ),
	);
	$mem_files = array(
		array( 'memorial-michael-hunphun-luiyi.jpg', 'Lt. Michael Hunphun Luiyi', 'Lt. Michael Hunphun Luiyi' ),
		array( 'memorial-nimmichon-raman.jpg', 'Lt. Nimmichon Raman', 'Lt. Nimmichon Raman' ),
		array( 'memorial-masotmi-awungshi.jpg', 'Lt. Masotmi Awungshi', 'Lt. Masotmi Awungshi' ),
	);
	$mem_ids = array();
	foreach ( $mem_files as $m ) {
		$mem_ids[] = mnm_demo_image( $m[0], $m[1], '', 'Portrait of ' . $m[2] );
	}

	$collections = array( 'Education', 'Digital Literacy', 'Community', 'Marathon', 'Events' );
	$gal         = array(
		array( '01', 'Education', 'Children in a classroom' ),
		array( '02', 'Digital Literacy', 'A computer training session' ),
		array( '03', 'Community', 'Village gathering' ),
		array( '04', 'Marathon', 'Runners at the start line' ),
		array( '05', 'Education', 'School supplies' ),
		array( '06', 'Digital Literacy', 'Learning basic computer skills' ),
		array( '07', 'Events', 'Community event' ),
		array( '08', 'Community', 'Women leaders meeting' ),
		array( '09', 'Marathon', 'Fun run' ),
		array( '10', 'Education', 'Students at school' ),
		array( '11', 'Events', 'Open day' ),
		array( '12', 'Digital Literacy', 'Training centre' ),
	);
	$gal_ids = array();
	foreach ( $gal as $g ) {
		$gal_ids[ $g[0] ] = mnm_demo_image( "gallery-{$g[0]}-demo.jpg", $g[2] . ' (demo)', $g[2] . '. ' . $note, $g[2], $g[1] );
	}

	// Real photographs (assets/demo/photos/) take the first gallery slots; placeholders fill any that remain.
	$real = glob( MNM_CORE_DIR . 'assets/demo/photos/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE );
	$hero_file = '';
	if ( $real ) {
		sort( $real );
		$credits = array();
		$cfile   = MNM_CORE_DIR . 'assets/demo/photos/credits.txt';
		if ( file_exists( $cfile ) ) {
			foreach ( file( $cfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
				$bits = explode( '|', $line, 2 );
				if ( 2 === count( $bits ) ) {
					$credits[ trim( $bits[0] ) ] = trim( $bits[1] );
				}
			}
		}
		$names = array( 'education' => 'Education', 'digital' => 'Digital Literacy', 'community' => 'Community', 'marathon' => 'Marathon', 'events' => 'Events' );
		$cycle = array_values( $names );
		$real_ids  = array();
		$by_prefix = array();
		$n = 0;
		foreach ( $real as $path ) {
			$base = basename( $path );
			if ( 0 === stripos( $base, 'hero' ) ) {
				$hero_file = $base;
				continue;
			}
			$term    = $cycle[ $n % count( $cycle ) ];
			$matched = '';
			$n++;
			foreach ( $names as $prefix => $label ) {
				if ( 0 === stripos( $base, $prefix ) ) {
					$term    = $label;
					$matched = $prefix;
				}
			}
			$title = ucwords( str_replace( array( '-', '_' ), ' ', preg_replace( '/\.[a-z]+$/i', '', $base ) ) );
			$cap   = isset( $credits[ $base ] ) ? $credits[ $base ] : $title;
			$id    = mnm_demo_image( $base, $title, $cap, $cap, $term );
			if ( $id ) {
				$real_ids[] = $id;
				if ( $matched ) {
					$by_prefix[ $matched ][] = $id;
				}
			}
		}
		$keys = array_keys( $gal_ids );
		foreach ( $real_ids as $k => $rid ) {
			if ( ! isset( $keys[ $k ] ) ) {
				break;
			}
			$old = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_mnm_demo_file', 'meta_value' => "gallery-{$keys[ $k ]}-demo.jpg", 'fields' => 'ids', 'posts_per_page' => 1 ) );
			if ( $old ) {
				wp_delete_attachment( $old[0], true );
			}
			$gal_ids[ $keys[ $k ] ] = $rid;
		}
		if ( $real_ids ) {
			$img['p_comm'] = $real_ids[0];
			$img['p_edu']  = isset( $by_prefix['education'][0] ) ? $by_prefix['education'][0] : $real_ids[0];
			if ( ! empty( $by_prefix['digital'][0] ) ) {
				$img['p_digital'] = $by_prefix['digital'][0];
			}
			if ( ! empty( $by_prefix['community'][0] ) ) {
				$img['p_comm'] = $by_prefix['community'][0];
			}
			if ( ! empty( $by_prefix['marathon'][0] ) ) {
				$img['banner']   = $by_prefix['marathon'][0];
				$img['campaign'] = isset( $by_prefix['marathon'][1] ) ? $by_prefix['marathon'][1] : $by_prefix['marathon'][0];
			}
		}
	}
	$hero_att = $hero_file ? mnm_demo_image( $hero_file, 'Hill village in blossom season (hero)', 'Hill village, Manipur (photo credit to be added)', 'Terraced hill village with blossoming trees below blue mountains' ) : 0;

	/* ---------- Programmes ---------- */
	$prog = array();
	$prog['edu'] = mnm_demo_post(
		'mnm_programme',
		'education-support',
		array(
			'post_title'   => 'Education Support',
			'menu_order'   => 1,
			'post_excerpt' => 'Funding and fighting the denial of education to children who are economically disadvantaged.',
			'post_content' => mnm_blocks(
				array(
					'No child should leave school only because their family cannot pay. Education Support exists to fund schooling for children in remote communities who are unable to attend school, or who have had to discontinue, because of financial circumstances.',
					'We work to ensure basic education and schooling for these children, and we speak up against the denial of education that poverty creates. Our hope is that every child who receives our support will one day be able to help fund another child.',
				)
			),
		),
		array_merge(
			$demo,
			array(
				'_mnm_tagline'  => 'Fund and fight the denial of education',
				'_mnm_icon'     => 'book',
				'_mnm_accent'   => 'blue',
				'_mnm_audience' => 'Economically disadvantaged children',
				'_mnm_focus'    => "Fund schooling for children who cannot afford to attend or continue\nFight the denial of education caused by financial hardship\nEnsure basic education and schooling\nInvite supported children to pay it forward",
			)
		)
	);
	set_post_thumbnail( $prog['edu'], $img['p_edu'] );

	$prog['dig'] = mnm_demo_post(
		'mnm_programme',
		'digital-literacy',
		array(
			'post_title'   => 'Digital Literacy',
			'menu_order'   => 2,
			'post_excerpt' => 'Basic computer skills for students, youth leaders, village administrators and women leaders, taught through our training centres.',
			'post_content' => mnm_blocks(
				array(
					'Many people in remote villages have never had the opportunity or access to use a computer. Through our training centres, we impart digital literacy to students, youth leaders, village administrators and women leaders.',
					'We hope these computer skills will help people in their personal and professional lives, and in building a better life.',
				)
			),
		),
		array_merge(
			$demo,
			array(
				'_mnm_tagline'  => 'Computer skills for people without access',
				'_mnm_icon'     => 'laptop',
				'_mnm_accent'   => 'green',
				'_mnm_audience' => 'Students, youth leaders, village administrators and women leaders',
				'_mnm_focus'    => "Basic computer skills through our training centres\nReaching people in remote villages\nEquipping people who have had no opportunity to learn\nSkills for personal and professional life",
			)
		)
	);
	set_post_thumbnail( $prog['dig'], $img['p_digital'] );

	$prog['com'] = mnm_demo_post(
		'mnm_programme',
		'community-empowerment',
		array(
			'post_title'   => 'Community Empowerment',
			'menu_order'   => 3,
			'post_excerpt' => 'A cycle of empowerment in which supported children grow up to help another child, and whole communities gain access to opportunity.',
			'post_content' => mnm_blocks(
				array(
					'Our humble attempt is to empower children and communities and to aid them towards a better future. By supporting children to stay in school, we also help prevent, to an extent, the dropping out of school that comes from lack of family funds.',
					'Our dream is that this mission will spread, and that every child who has received our support will one day pay it forward and help fund another child.',
				)
			),
		),
		array_merge(
			$demo,
			array(
				'_mnm_tagline'  => 'Pay it forward, child by child',
				'_mnm_icon'     => 'people',
				'_mnm_accent'   => 'yellow',
				'_mnm_audience' => 'Children, families and village communities',
				'_mnm_focus'    => "Help prevent dropping out because of lack of family funds\nBuild a pay-it-forward cycle of support\nImprove access to opportunity in remote communities",
			)
		)
	);
	set_post_thumbnail( $prog['com'], $img['p_comm'] );

	/* ---------- Projects (SAMPLE) ---------- */
	$projects = array(
		array( 'school-fee-support-fund', 'School Fee Support Fund', 'Sample project: covering school fees for children whose families cannot afford them.', 'ongoing', '2026', 'edu', 'Education', 'Ukhrul', array( 'Children able to stay in school despite family hardship', 'A transparent record of how funds are used' ), '02' ),
		array( 'village-computer-corner', 'Village Computer Corner', 'Sample project: a small computer training corner for a remote village.', 'planned', '2027', 'dig', 'Digital Literacy', 'Theiva Village', array( 'Basic computer skills for village residents', 'A shared learning space for students and youth leaders' ), '06' ),
		array( 'women-leaders-digital-circle', 'Women Leaders Digital Skills Circle', 'Sample project: digital skills workshops for women leaders.', 'ongoing', '2026', 'dig', 'Digital Literacy', 'Phalee Village', array( 'Confidence using computers for community work', 'Women leaders able to support others to learn' ), '08' ),
		array( 'youth-leaders-computer-basics', 'Youth Leaders Computer Basics', 'Sample project: introductory computer sessions for youth leaders.', 'completed', '2025', 'dig', 'Digital Literacy', 'Ukhrul', array( 'Youth leaders with practical computer skills' ), '12' ),
		array( 'books-and-uniforms-drive', 'Books and Uniforms Drive', 'Sample project: school supplies for children starting the school year.', 'completed', '2025', 'edu', 'Education', 'Phalee Village', array( 'Children equipped for the school year' ), '05' ),
		array( 'pay-it-forward-scholars', 'Pay It Forward Scholars', 'Sample project: inviting supported children to help the next child.', 'planned', '2027', 'com', 'Community', 'Ukhrul', array( 'A growing circle of children helping children' ), '03' ),
	);
	$project_ids = array();
	foreach ( $projects as $p ) {
		$pid = mnm_demo_post(
			'mnm_project',
			$p[0],
			array(
				'post_title'   => $p[1],
				'post_excerpt' => $p[2],
				'post_content' => mnm_blocks( array( $p[2], 'This entry is sample content created for the website prototype. Replace it with real project details, photographs and verified outcomes.', '## Why it matters', 'Every project is designed around the foundation\'s two goals: keeping economically disadvantaged children in school and bringing digital literacy to people who have never had access to a computer.' ) ),
			),
			array_merge(
				$demo,
				array(
					'_mnm_status'    => $p[3],
					'_mnm_period'    => $p[4],
					'_mnm_programme' => $prog[ $p[5] ],
					'_mnm_outcomes'  => implode( "\n", $p[8] ),
					'_mnm_gallery'   => implode( ',', array_filter( array( $gal_ids[ $p[9] ], $gal_ids['11'], $gal_ids['03'] ) ) ),
				)
			),
			array( 'mnm_project_cat' => array( $p[6] ), 'mnm_project_location' => array( $p[7] ) )
		);
		if ( $pid && $gal_ids[ $p[9] ] ) {
			set_post_thumbnail( $pid, $gal_ids[ $p[9] ] );
		}
		$project_ids[] = $pid;
	}

	/* ---------- Events (SAMPLE, dates relative to today) ---------- */
	$events = array(
		array( 'digital-literacy-open-day', 'Digital Literacy Open Day', 18, 'Ukhrul (venue to be confirmed)', 'Community', '11', 'Sample event: an open day introducing the training centre and basic computer skills.' ),
		array( 'community-education-meeting', 'Community Education Meeting', 41, 'Phalee Village (venue to be confirmed)', 'Community', '07', 'Sample event: a village meeting about keeping every child in school.' ),
		array( 'training-centre-orientation', 'Training Centre Orientation', -28, 'Ukhrul', 'Training', '12', 'Sample past event: orientation for new learners.' ),
		array( 'memorial-gathering', 'Memorial Gathering', -95, 'Theiva Village', 'Memorial', '03', 'Sample past event: a gathering to remember Michael, Nimmichon and Masotmi.' ),
		array( 'school-supplies-handover', 'School Supplies Handover', -150, 'Phalee Village', 'Community', '05', 'Sample past event: handing over school supplies.' ),
	);
	foreach ( $events as $e ) {
		$eid = mnm_demo_post(
			'mnm_event',
			$e[0],
			array(
				'post_title'   => $e[1],
				'post_excerpt' => $e[6],
				'post_content' => mnm_blocks( array( $e[6], 'This is sample content for the website prototype. Replace the details with the real programme, timings and venue.' ) ),
			),
			array_merge(
				$demo,
				array(
					'_mnm_start'            => $day( $e[2], 10 ),
					'_mnm_end'              => $day( $e[2], 14 ),
					'_mnm_venue'            => $e[3],
					'_mnm_registration_url' => $e[2] > 0 ? home_url( '/contact/' ) : '',
					'_mnm_gallery'          => implode( ',', array_filter( array( $gal_ids[ $e[5] ], $gal_ids['07'], $gal_ids['11'] ) ) ),
				)
			),
			array( 'mnm_event_type' => array( $e[4] ) )
		);
		if ( $eid && $gal_ids[ $e[5] ] ) {
			set_post_thumbnail( $eid, $gal_ids[ $e[5] ] );
		}
	}

	/* ---------- Articles ---------- */
	$articles = array(
		array( 'remembering-michael-nimmichon-masotmi', 'Remembering Michael, Nimmichon and Masotmi', 'Why the foundation exists, and the three young people it remembers.', 'Foundation News', '1', '03',
			array( 'MNM Foundation was set up by the families of Michael Hunphun Luiyi of Theiva village, and Nimmichon Raman and Masotmi Awungshi of Phalee village. All three died young.', 'In their memory, their families are building a foundation to empower communities: by funding and fighting the denial of education to children who are economically disadvantaged, and by teaching digital literacy through training centres.', 'We are unsure of the outcome, but we are committed.' ) ),
		array( 'why-education-and-digital-skills', 'Why education and digital skills?', 'Two goals, one idea: give people a foundation to build on.', 'Education', '1', '10',
			array( 'The foundation works in two ways. First, it funds and fights the denial of education to children who are economically disadvantaged. Second, it imparts digital literacy to students, youth leaders, village administrators and women leaders through its training centres.', 'This article is sample copy for the website prototype and can be replaced with the foundation\'s own words.' ) ),
		array( 'marathon-what-to-expect', 'Marathon registration: what to expect', 'A sample update about registering for the MNM Foundation Marathon.', 'Events', '', '09',
			array( 'Registration for the marathon happens directly on this website. You will choose a race category, a T-shirt size and give an emergency contact, then receive a unique reference number.', 'This is a sample article. Race categories, dates and venue are placeholders until the organisers confirm them.' ) ),
		array( 'how-you-can-support-a-child', 'How you can support a child\'s education', 'Sample article on ways to get involved.', 'Foundation News', '', '05',
			array( 'There are several ways to help: donate to a campaign, volunteer your time, run in the marathon or simply share our work.', 'Online payments are not yet connected. Until they are, please contact the foundation directly using the details on the Contact page. This is sample copy.' ) ),
	);
	foreach ( $articles as $i => $a ) {
		$aid = mnm_demo_post(
			'mnm_article',
			$a[0],
			array(
				'post_title'   => $a[1],
				'post_excerpt' => $a[2],
				'post_content' => mnm_blocks( $a[6] ),
				'post_date'    => gmdate( 'Y-m-d H:i:s', $now - ( $i * 9 + 3 ) * DAY_IN_SECONDS ),
			),
			array(
				'_mnm_featured' => $a[4],
				'_mnm_demo'     => '1',
			),
			array( 'mnm_article_cat' => array( $a[3] ) )
		);
		if ( $aid && $gal_ids[ $a[5] ] ) {
			set_post_thumbnail( $aid, $gal_ids[ $a[5] ] );
		}
	}

	/* ---------- Campaigns (SAMPLE figures) ---------- */
	$c1 = mnm_demo_post(
		'mnm_campaign',
		'marathon-fund',
		array(
			'post_title'   => 'Marathon Fund: Run for a Child\'s Education',
			'post_excerpt' => 'Funds raised through the MNM Foundation Marathon support schooling for children who cannot afford it.',
			'post_content' => mnm_blocks( array( 'Every kilometre run helps fund a child\'s schooling. The figures shown here are sample data for the prototype.' ) ),
		),
		array(
			'_mnm_target'        => '500000',
			'_mnm_raised'        => '185000',
			'_mnm_currency'      => '₹',
			'_mnm_verified_on'   => gmdate( 'Y-m-d', $now - 5 * DAY_IN_SECONDS ),
			'_mnm_verified_note' => 'sample figure',
			'_mnm_demo'          => '1',
		)
	);
	set_post_thumbnail( $c1, $img['campaign'] );
	$c2 = mnm_demo_post(
		'mnm_campaign',
		'education-support-fund',
		array(
			'post_title'   => 'Education Support Fund',
			'post_excerpt' => 'Keeping economically disadvantaged children in school.',
			'post_content' => mnm_blocks( array( 'This general fund supports the Education Support programme. The figures shown here are sample data for the prototype.' ) ),
		),
		array(
			'_mnm_target'        => '1000000',
			'_mnm_raised'        => '320000',
			'_mnm_currency'      => '₹',
			'_mnm_verified_on'   => gmdate( 'Y-m-d', $now - 12 * DAY_IN_SECONDS ),
			'_mnm_verified_note' => 'sample figure',
			'_mnm_demo'          => '1',
		)
	);
	set_post_thumbnail( $c2, $img['p_edu'] );

	/* ---------- Marathon updates ---------- */
	$updates = array(
		array( 'registration-is-open', 'Registration is open', 'Registration for the MNM Foundation Marathon is now open on this website. This is a sample update.', 2 ),
		array( 'race-categories-being-finalised', 'Race categories are being finalised', 'The race categories shown are placeholders while organisers confirm distances. This is a sample update.', 9 ),
		array( 'volunteers-needed', 'Volunteers needed on race day', 'We will need marshals, water-station helpers and first-aid support. Contact us to volunteer. This is a sample update.', 16 ),
	);
	foreach ( $updates as $u ) {
		mnm_demo_post( 'mnm_update', $u[0], array( 'post_title' => $u[1], 'post_content' => mnm_blocks( array( $u[2] ) ), 'post_date' => gmdate( 'Y-m-d H:i:s', $now - $u[3] * DAY_IN_SECONDS ) ), $demo );
	}

	/* ---------- Registrations (46 demo entries) ---------- */
	if ( ! get_posts( array( 'post_type' => 'mnm_registration', 'meta_key' => '_mnm_demo', 'meta_value' => '1', 'fields' => 'ids', 'posts_per_page' => 1 ) ) ) {
		mt_srand( 20260711 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_seeding_mt_srand
		$first = array( 'Asha', 'Bimal', 'Chaya', 'Dev', 'Esha', 'Farid', 'Gita', 'Hari', 'Isha', 'Jon', 'Kavi', 'Lena', 'Manu', 'Neha', 'Omar', 'Priya', 'Qadir', 'Rina', 'Sam', 'Tara', 'Uma', 'Vik', 'Wen', 'Yuki' );
		$loc   = array( 'Ukhrul', 'Phalee Village', 'Theiva Village', 'Imphal', 'Kamjong', 'Litan', 'Shangkai' );
		$races = array_keys( mnm_reg_races() );
		$sizes = mnm_reg_sizes();
		for ( $i = 1; $i <= 46; $i++ ) {
			$name = $first[ ( $i - 1 ) % count( $first ) ] . ' Sample' . ( $i > 24 ? ' ' . chr( 64 + ( $i - 24 ) ) : '' );
			$age  = mt_rand( 9, 56 );
			$ref  = mnm_reg_new_reference();
			$when = gmdate( 'Y-m-d H:i:s', $now - mt_rand( 0, 21 * 24 * 3600 ) );
			$email = sprintf( 'demo%02d@example.org', $i );
			$phone = sprintf( '+91 90000 %05d', 10000 + $i );
			$id = wp_insert_post( array( 'post_type' => 'mnm_registration', 'post_status' => 'publish', 'post_title' => $ref, 'post_content' => "$name $email $phone", 'post_date' => $when, 'post_date_gmt' => get_gmt_from_date( $when ) ) );
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			$loc_i = $loc[ mt_rand( 0, count( $loc ) - 1 ) ];
			foreach ( array(
				'_mnm_ref'             => $ref,
				'_mnm_name'            => $name,
				'_mnm_email'           => $email,
				'_mnm_phone'           => $phone,
				'_mnm_dob'             => gmdate( 'Y-m-d', $now - $age * YEAR_IN_SECONDS - mt_rand( 0, 300 ) * DAY_IN_SECONDS ),
				'_mnm_age'             => $age,
				'_mnm_location'        => $loc_i,
				'_mnm_race'            => $races[ mt_rand( 0, count( $races ) - 1 ) ],
				'_mnm_tshirt'          => $sizes[ mt_rand( 0, count( $sizes ) - 1 ) ],
				'_mnm_emergency_name'  => 'Demo Contact',
				'_mnm_emergency_phone' => sprintf( '+91 90001 %05d', 10000 + $i ),
				'_mnm_consent_at'      => $when,
				'_mnm_reg_status'      => mt_rand( 1, 10 ) > 6 ? 'confirmed' : 'pending',
				'_mnm_demo'            => '1',
			) as $k => $v ) {
				update_post_meta( $id, $k, $v );
			}
		}
	}

	/* ---------- Pages ---------- */
	$pages = array(
		'home'         => array( 'Home', 'front-page', mnm_blocks( array( 'The homepage is assembled from the sections you manage under Foundation. Edit the headline and text in Foundation, Website Settings.' ) ) ),
		'about'        => array( 'About Us', 'page-templates/template-about.php', mnm_blocks( array(
			'MNM Foundation began with loss. Michael Hunphun Luiyi of Theiva village, and Nimmichon Raman and Masotmi Awungshi of Phalee village, each died young.',
			'Their families came together to set up the foundation in their memory, so that something lasting and useful could grow from that loss: a way to empower communities, starting with the children who most need a chance and the people who have never had access to a computer.',
			'We are unsure of the outcome, but we are committed. We approach this work humbly, and we will always be honest about what we have and have not achieved.',
		) ) ),
		'marathon'     => array( 'Marathon', 'page-templates/template-marathon.php', '' ),
		'gallery'      => array( 'Gallery', 'page-templates/template-gallery.php', mnm_blocks( array( 'Photographs from our programmes, projects and events.' ) ) ),
		'contact'      => array( 'Contact Us', 'page-templates/template-contact.php', mnm_blocks( array( 'Questions, ideas, or want to volunteer? Write to us or call. We would love to hear from you.' ) ) ),
		'get-involved' => array( 'Get Involved', 'page-templates/template-get-involved.php', mnm_blocks( array( 'There are many ways to be part of this work. Choose the one that suits you.' ) ) ),
	);
	$page_ids = array();
	foreach ( $pages as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$page_ids[ $slug ] = $existing->ID;
			continue;
		}
		$page_ids[ $slug ] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p[0], 'post_name' => $slug, 'post_content' => $p[2], 'meta_input' => array( '_wp_page_template' => $p[1] ) ) );
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_ids['home'] );

	// Remove WordPress's stock sample content.
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
		$old = get_page_by_path( $slug, OBJECT, $type );
		if ( $old ) {
			wp_delete_post( $old->ID, true );
		}
	}

	/* ---------- Settings ---------- */
	$memorials = array(
		array( 'name' => 'Lt. Michael Hunphun Luiyi', 'village' => 'Theiva Village', 'years' => '1984 – 2009', 'photo' => $mem_ids[0] ),
		array( 'name' => 'Lt. Nimmichon Raman', 'village' => 'Phalee Village', 'years' => '1993 – 2016', 'photo' => $mem_ids[1] ),
		array( 'name' => 'Lt. Masotmi Awungshi', 'village' => 'Phalee Village', 'years' => '1989 – 2017', 'photo' => $mem_ids[2] ),
	);
	$settings = array_merge(
		mnm_settings_defaults(),
		array(
			'hero_image'         => $hero_att,
			'announcement_on'    => '1',
			'announcement_text'  => 'Sample announcement: Marathon registration is open.',
			'announcement_link'  => get_permalink( $page_ids['marathon'] ),
			'stat_1_value'       => '250+',
			'stat_1_label'       => 'Children supported (sample)',
			'stat_2_value'       => '6',
			'stat_2_label'       => 'Villages reached (sample)',
			'stat_3_value'       => '120+',
			'stat_3_label'       => 'People trained in computer skills (sample)',
			'stat_4_value'       => '3',
			'stat_4_label'       => 'Programmes (sample)',
			'stats_sample'       => '1',
			'marathon_banner'    => $img['banner'],
			'marathon_campaign'  => $c1,
			'demo_mode'          => '1',
			'memorials'          => $memorials,
		)
	);
	update_option( 'mnm_settings', $settings );
	mnm_settings( true );

	update_option( 'blogname', 'MNM Foundation' );
	update_option( 'blogdescription', 'Fund and Fight' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'timezone_string', 'Asia/Kolkata' );
	update_option( 'date_format', 'j F Y' );

	/* ---------- Menus ---------- */
	$menu = wp_get_nav_menu_object( 'Primary Menu' );
	if ( ! $menu ) {
		$mid   = wp_create_nav_menu( 'Primary Menu' );
		$items = array(
			array( 'About', get_permalink( $page_ids['about'] ), $page_ids['about'], 'page' ),
			array( 'Programmes', get_post_type_archive_link( 'mnm_programme' ), 0, '' ),
			array( 'Projects & Impact', get_post_type_archive_link( 'mnm_project' ), 0, '' ),
			array( 'Events', get_post_type_archive_link( 'mnm_event' ), 0, '' ),
			array( 'Marathon', get_permalink( $page_ids['marathon'] ), $page_ids['marathon'], 'page' ),
			array( 'Gallery', get_permalink( $page_ids['gallery'] ), $page_ids['gallery'], 'page' ),
			array( 'News', get_post_type_archive_link( 'mnm_article' ), 0, '' ),
			array( 'Contact', get_permalink( $page_ids['contact'] ), $page_ids['contact'], 'page' ),
		);
		foreach ( $items as $it ) {
			$args = array( 'menu-item-title' => $it[0], 'menu-item-status' => 'publish' );
			if ( $it[2] ) {
				$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $it[2] );
			} else {
				$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => $it[1] );
			}
			wp_update_nav_menu_item( $mid, 0, $args );
		}
	} else {
		$mid = $menu->term_id;
	}
	$fmenu = wp_get_nav_menu_object( 'Footer Menu' );
	if ( ! $fmenu ) {
		$fid = wp_create_nav_menu( 'Footer Menu' );
		foreach ( array( 'about' => 'About Us', 'get-involved' => 'Get Involved', 'gallery' => 'Gallery', 'contact' => 'Contact Us' ) as $slug => $label ) {
			wp_update_nav_menu_item( $fid, 0, array( 'menu-item-title' => $label, 'menu-item-status' => 'publish', 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids[ $slug ] ) );
		}
	} else {
		$fid = $fmenu->term_id;
	}
	set_theme_mod( 'nav_menu_locations', array( 'primary' => $mid, 'footer' => $fid ) );

	/* ---------- Optional demo staff account (limited Editor role) ----------
	 * Created only when a password is supplied by the site owner (never hardcoded):
	 * define( 'MNM_DEMO_STAFF_PASSWORD', '...' ) in wp-config.php, or use the filter. */
	$staff_pass = apply_filters( 'mnm_demo_staff_password', defined( 'MNM_DEMO_STAFF_PASSWORD' ) ? MNM_DEMO_STAFF_PASSWORD : '' );
	if ( $staff_pass && ! username_exists( 'staff' ) ) {
		wp_insert_user( array( 'user_login' => 'staff', 'user_pass' => $staff_pass, 'user_email' => 'staff@example.invalid', 'role' => 'editor', 'display_name' => 'Foundation Staff (demo)' ) );
	}

	flush_rewrite_rules();
	update_option( 'mnm_demo_seeded', time() );
}

/**
 * Remove all content flagged as demonstration data.
 *
 * @return int Number removed.
 */
function mnm_remove_demo() {
	$ids = get_posts(
		array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'meta_key'       => '_mnm_demo',
			'meta_value'     => '1',
			'fields'         => 'ids',
			'posts_per_page' => -1,
		)
	);
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	delete_option( 'mnm_demo_seeded' );
	$s = get_option( 'mnm_settings', array() );
	if ( is_array( $s ) ) {
		$s['memorials']         = array();
		$s['hero_image']        = 0;
		$s['marathon_banner']   = 0;
		$s['marathon_campaign'] = 0;
		for ( $i = 1; $i <= 4; $i++ ) {
			$s[ "stat_{$i}_value" ] = '';
			$s[ "stat_{$i}_label" ] = '';
		}
		update_option( 'mnm_settings', $s );
	}
	return count( $ids );
}

/**
 * Admin screen: Demo Content.
 */
function mnm_render_demo_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg = '';
	if ( isset( $_POST['mnm_demo_action'] ) && check_admin_referer( 'mnm_demo' ) ) {
		$action = sanitize_key( wp_unslash( $_POST['mnm_demo_action'] ) );
		if ( 'seed' === $action ) {
			mnm_seed_demo();
			$msg = __( 'Demonstration content installed.', 'mnm-foundation-core' );
		} elseif ( 'remove' === $action ) {
			/* translators: %d: count. */
			$msg = sprintf( __( 'Removed %d demonstration items.', 'mnm-foundation-core' ), mnm_remove_demo() );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Demo Content', 'mnm-foundation-core' ); ?></h1>
		<?php if ( $msg ) : ?><div class="notice notice-success"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>
		<div class="mnm-panel">
			<p><?php esc_html_e( 'Install sample programmes, projects, events, articles, campaigns, gallery photos, 46 demonstration registrations, pages and menus so the whole website can be shown to the client. Everything is clearly labelled as sample content and can be removed again with one click.', 'mnm-foundation-core' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'mnm_demo' ); ?>
				<button class="button button-primary" name="mnm_demo_action" value="seed"><?php esc_html_e( 'Install / refresh demo content', 'mnm-foundation-core' ); ?></button>
				<button class="button" name="mnm_demo_action" value="remove" onclick="return confirm('<?php echo esc_js( __( 'Remove all demonstration content, including demo registrations?', 'mnm-foundation-core' ) ); ?>');"><?php esc_html_e( 'Remove demo content', 'mnm-foundation-core' ); ?></button>
			</form>
			<?php if ( get_option( 'mnm_demo_seeded' ) ) : ?>
				<p class="description"><?php echo esc_html( sprintf( /* translators: %s: date. */ __( 'Installed on %s.', 'mnm-foundation-core' ), wp_date( 'j M Y H:i', (int) get_option( 'mnm_demo_seeded' ) ) ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
