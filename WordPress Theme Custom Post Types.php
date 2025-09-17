<?php

// Add Show Post Type & Taxonomy
add_action( 'init', 'dt_add_show_type');
function dt_add_show_type() {

	register_post_type( 'dt_shows', array(
		'labels' => array(
			'name' => 'Shows',
			'singular_name' => 'Show',
			'all_items' => 'All Shows',
			'add_new' => 'Add New Show',
			'add_new_item' => 'Add New Show',
			'edit' =>  'Edit',
			'edit_item' => 'Edit Show',
			'new_item' => 'New Show',
			'view_item' => 'View Show',
			'search_items' => 'Search Shows',
			'not_found' =>  'Nothing found',
			'not_found_in_trash' => 'Nothing found',
		),
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'query_var' => true,
		'menu_position' => 3,
		'menu_icon' => false,
		'rewrite' => array( 'slug' => 'shows', 'with_front' => true ),
		'has_archive' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => array( 'title', 'excerpt', 'editor', 'author', 'thumbnail' ),
 	));

	register_taxonomy (
		'series',
		'dt_shows',
		array(
			'labels' => array(
				'name' => 'Show Titles',
				'singular_name' => 'Title',
				'add_new_item' => 'Add Show Title',
				'new_item_name' => "New Show Title"
			),
			'show_ui' => true,
			'show_admin_column' => true,
			'show_tagcloud' => false,
			'hierarchical' => true,
			'rewrite' => array( 'slug' => 'show' )
		)
	);

}

// Add Show Custom Fields
add_action( 'cmb2_admin_init', 'dt_add_show_meta' );
function dt_add_show_meta() {

	$shows_meta = new_cmb2_box( array(
		'id'            => 'dt_shows_options',
		'title'         => 'Show Details',
		'object_types'  => array( 'dt_shows' ),
	));

	$shows_meta->add_field( array(
		'name' => 'Live Broadcast',
		'id'   => 'dt-live-stream',
		'type' => 'checkbox',
	));

	$shows_meta->add_field( array(
		'name' => 'Free Show',
		'id'   => 'dt-free-show',
		'type' => 'checkbox',
	));

	$shows_meta->add_field( array(
		'name' => 'Broadcast Time',
		'id'   => 'dt-show-time',
		'type' => 'text_datetime_timestamp',
	));

	$shows_meta->add_field( array(
		'name' => 'Gigcasters ID',
		'id'   => 'dt-blastro-id',
		'type' => 'text',
	));

	$shows_meta->add_field( array(
		'name' => 'Product ID',
		'id'   => 'dt-show-sku',
		'type'        => 'post_search_text',
		'post_type'   => 'product',
		'select_type' => 'checkbox',
		'select_behavior' => 'replace',
	));

	$shows_meta->add_field( array(
		'name' => 'Soundcloud URL',
		'id'   => 'dt-audio-url',
		'type' => 'text',
	));

	$reviews_meta = new_cmb2_box( array(
		'id'            => 'dt-review',
		'title'         => 'Review Info',
		'object_types'  => array( 'dt_shows' ),
	));

	$reviews_meta->add_field( array(
		'name'             => 'Rating',
		'id'               => 'dt-rating',
		'type'             => 'select',
		'show_option_none' => true,
		'default'          => 'custom',
		'options'          => array(
			'excellent' => 'Excellent',
			'very-good' => 'Very Good',
			'good' => 'Good',
			'fair' => 'Fair',
			'poor' => 'Poor',
		),
	));

	$reviews_meta->add_field( array(
		'name' => 'MPAA Rating',
		'desc' => 'PG-13, R, MA, etc',
		'id'   => 'dt-mpaa-rating',
		'type' => 'text',
	));

	$reviews_meta->add_field( array(
		'name' => 'Genre',
		'id'   => 'dt-genre',
		'type' => 'text',
	));

	$reviews_meta->add_field( array(
		'name' => 'Release Year',
		'id'   => 'dt-release-year',
		'type' => 'text',
	));

}

// Add Series Meta
add_filter('cmb2-taxonomy_meta_boxes', 'dt_series_meta');
function dt_series_meta( array $meta_boxes ) {

	$meta_boxes['test_metabox'] = array(
		'id'            => 'dt_series_meta',
		'title'         => __( 'Series Info', 'cmb2' ),
		'object_types'  => array( 'series', ),
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true,
		'fields'        => array(
			array(
				'name' => __( 'Broadcast Time', 'cmb2' ),
				'id'   => 'dt_series_time',
				'type' => 'text_time',
			),
			array(
				'name'             => 'Broadcast Day',
				'id'               => 'dt_series_day',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => array(
					'Sunday' => __( 'Sunday', 'cmb2' ),
					'Monday'   => __( 'Monday', 'cmb2' ),
					'Tuesday'     => __( 'Tuesday', 'cmb2' ),
					'Wednesday'     => __( 'Wednesday', 'cmb2' ),
					'Thursday'     => __( 'Thursday', 'cmb2' ),
					'Friday'     => __( 'Friday', 'cmb2' ),
					'Saturday'     => __( 'Saturday', 'cmb2' ),
				),
			),
			array(
				'name' => __( 'Series Image', 'cmb2' ),
				'id'   => 'dt_series_image',
				'type' => 'file',
				'text'    => array(
					'add_upload_file_text' => 'Add Image'
				),
			),
		),
	);

	return $meta_boxes;
}

// Search All Shows, Pages, and Posts
add_filter( 'pre_get_posts', function ( $query ) {
    if ( $query->is_search )
    	$query->set( 'post_type', array( 'dt_shows', 'post', 'page' ) );
    return $query;
});

// Determine If User Can Watch A Show
function dt_authorize( $show = false ) {

    // Initialize
    $subscribed = $purchased = false;
	$free_show = dt_is_free_show( $show );

    if ( is_user_logged_in() ) {

		$user = wp_get_current_user();



		// Has Active Subscription?
        $subscribed = get_posts( array(
            'numberposts' => 1,
            'post_type'   => 'shop_subscription',
			'meta_key'    => '_customer_user',
	        'meta_value'  => $user->ID,
            'post_status' => 'wc-active',
        ));

		// With Or Without Ads
        if ( isset($subscribed[0]->ID) ) {
			$subscribed = true;

		// Purchased Single Episode?
		} else {
            $product = get_post_meta( $show, 'dt-show-sku', true );
            $purchased = wc_customer_bought_product( $user->user_email, $user->ID, $product );
        }

    }

    if ( $subscribed || $purchased )
		return true;
	else if ( $free_show )
		return 'ads';
	else
		return false;

}

// Get Video URL
function dt_get_video( $show = false ) {

	$authorized = dt_authorize( $show );

	if( dt_is_movie_review( $show ) ) {
		echo '<div class="dt-no-video" style="background-image:url(' . get_the_post_thumbnail_url($show) . ')"></div>';

	} else if ( $authorized ) {
		$ads = $authorized === 'ads' ? true : false;
	    $stream = dt_get_token( $show, $ads );
	    if ( $stream ) {
	        echo '<div class="dt-show-video"><iframe width="1066" height="650" scrolling="no" src="' .
	            $stream . '" frameborder="0" allowfullscreen >[Your browser does not support frames or
	            is currently configured not to display frames. Please use an up-to-date browser that is
	            capable of displaying frames.]</iframe></div>';
	    } else {
	        echo '<div class="dt-error">Video temporarily unavailable</div>';
	    }

	} else if ( dt_is_free_show( $show ) ) {
		$stream = dt_get_token( $show, true );
		if ( $stream ) {
			echo '<div class="dt-show-video"><iframe width="1066" height="650" scrolling="no" src="' .
				$stream . '" frameborder="0" allowfullscreen >[Your browser does not support frames or
				is currently configured not to display frames. Please use an up-to-date browser that is
				capable of displaying frames.]</iframe></div>';
		}

	} else {
		echo '<div class="dt-no-video" style="background-image:url(' . get_the_post_thumbnail_url($show) . ')">
				<div class="dt-no-video-overlay"></div><div class="dt-wrap">
				<h4>Videos are available as pay-per-view or by subscription only.</h4>
				<a class="dt-button dt-yellow dt-buy" href="' . dt_get_buy_video_url( $show ) . '">Buy Video</a>
				<a class="dt-button dt-outline dt-pop-open" href="#dt-log-in">Login / Join</a>
			</div></div>';
	}

}

// Get Rating For Reviews
function dt_get_rating( $show ) {

	$rating['slug'] = get_post_meta( $show, 'dt-rating', true );

	if ( !empty($rating['slug']) ) {
		switch ( $rating['slug'] ) {
			 case 'excellent' : $rating['name'] = 'Excellent'; break;
			 case 'very-good' : $rating['name'] = 'Very Good'; break;
			 case 'good' : $rating['name'] = 'Good'; break;
			 case 'fair' : $rating['name'] = 'Fair'; break;
			 case 'poor' : $rating['name'] = 'Poor'; break;
		}
	}

	return $rating;

}

function dt_get_review_meta( $review ) {
	$meta['rating'] = get_post_meta( $review, 'dt-mpaa-rating', true );
	$meta['genre'] = get_post_meta( $review, 'dt-genre', true );
	$meta['year'] = get_post_meta( $review, 'dt-release-year', true );
	return $meta;
}

function dt_get_review_title( $review ) {
	$title = get_the_title($review);
	return str_replace("–","",$title);
}

// Get Audio URL
function dt_get_audio( $show = false ) {
    	$audio = get_post_meta( $show, 'dt-audio-url', true );
    echo wp_oembed_get($audio);
}

// Get Series Description
function dt_get_series_description() {
	$series = get_term_by( 'slug', get_query_var('term'), 'series' );
	if ( $series->description ) {
		echo '<h2>About</h2><div class="dt-description">' . wpautop($series->description) . '</div>';
	}
}

// Get GigCasters Token
function dt_get_token( $show, $ads, $token = false ) {

    // Get Video ID
    $video = get_post_meta( $show, '_lac0509_dt-blastro-id', true );

    if ( $video ) {

        // Get User
        $user = wp_get_current_user();

        // Load Key Crypto
        require_once('gigcasters/gc-crypto-v2.php');

        // Initialize Key Crypto
        $key = DT_GIGCASTERS_CRYPTO_KEY;
        $crypto = new GcCrypto();
        $crypto->setKey( pack( 'H*', $key ) );

        // Set URL & Get Key
		// Needs a unique ID and if the user is free or paid
        $url = 'https://ppv.gigcasters.com/embed/' . urlencode($video) . '.html?token=dt:';
        $token = $crypto->encryptObject( array(
            'show_sku' => $video,
            'user_id' => $user ? $user->ID : 0,
			'ads' => $ads,
            'expires' => time() + 86400
        ));

    }

    return $token ? $url . urlencode($token) : false;

}

// Get The Post/Show Series Title
function dt_get_series( $show ) {
    $series = get_the_terms( $show, 'series' );
    if ( isset($series[0]->name) && $series[0]->slug == 'reviews' ) {
        echo '<a class="dt-series" href="/reviews">' . $series[0]->name . '</a>';
	} elseif ( isset($series[0]->name) ) {
		echo '<a class="dt-series" href="/show/' . $series[0]->slug . '">' . $series[0]->name . '</a>';
	}
}

function dt_is_movie_review( $show ) {
	$series = get_the_terms( $show, 'series' );
	return isset($series[0]->name) && $series[0]->slug == 'reviews';
}

function dt_is_free_show( $show ) {
	$series = get_the_terms( $show, 'series' );
	return isset($series[0]->name) && $series[0]->slug == 'free-shows';
}

// Load Single Show Template
add_filter('single_template', function( $template ){
  global $post;
  return $post->post_type == 'dt_shows' ?
      locate_template('show.php') : $template;
});

// Get Reviews For Reviews Page Template
function dt_get_reviews() {

    // Set Page
    $page = get_query_var('paged') ? (int) get_query_var('paged') : 1;

    // Get Reviews
    return new WP_Query([
        'post_type' => 'dt_shows',
        'paged' => $page,
        'taxonomy' => 'series',
        'term' => 'reviews',
        'posts_per_page' => 30,
    ]);

}

// Create "review" movie poster thumbnail
if (class_exists('MultiPostThumbnails')) {
    new MultiPostThumbnails(array(
        'label'     => 'Movie Poster',
        'id'        => 'dt-review-poster',
        'post_type' => 'dt_shows',
    ));
}

// Get Review Movie Poster
function dt_get_review_thumb( $review ) {
    if (class_exists('MultiPostThumbnails')) {
        $check = MultiPostThumbnails::the_post_thumbnail( 'dt_shows',
            'dt-review-poster', NULL, 'sm-review');
        return $check;
    }
}

// Customize Soundcloud/Youtube Player Embeds
add_filter('oembed_result', 'dt_custom_embed', 10, 3);
function dt_custom_embed($html, $url) {

    // Soundcloud parameters
    if (strpos($url, 'soundcloud') !== false) {
        $html = str_replace('?visual=true', '?visual=false', $html);
        $html = str_replace('&show_artwork=true&maxwidth=500&maxheight=750', '&show_artwork=false&&color=ff7700&hide_related=true&maxwidth=200&maxheight=960', $html);
        $html = str_replace('width="500" height="400"', 'width="100%" height="180px"', $html);
    }

    // Youtube parameters
    if (strpos($url, 'youtube') !== false) {
        $html = str_replace('?feature=oembed', '?feature=oembed&modestbranding=1&showinfo=0&color=white&rel=0', $html);
        $html = str_replace('width="500" height="281"', 'width="100%" height="500px"', $html);
    }

    return $html;

}

// Determine If There Is A Live Show
add_action( 'wp_ajax_nopriv_dt_on_air', 'dt_on_air' );
add_action( 'wp_ajax_dt_on_air', 'dt_on_air' );
function dt_on_air() {

    // Basic security check
    if (!wp_verify_nonce($_REQUEST['nonce'] ?? '', 'dt_on_air_nonce')) {
        wp_die('Security check failed');
    }

    // Get Last On-Air Show
    $on_air = new WP_Query([
        'post_type'  => ['dt_shows'],
        'posts_per_page' => 1,
        		'meta_key' => 'dt-live-stream',
        'orderby' => 'post_date',
        'order' => 'DESC'
    ]);

    // Set Post
	$show_date = get_post_meta( $on_air->posts[0]->ID, 'dt-show-time', true );
    $post_id = $on_air->posts[0]->ID;

    // Format Dates
    $date = date('Y/m/d', $show_date );
    $today = date('Y/m/d', strtotime('now') );

    // Response
    $date == $today ? wp_send_json($post_id) : wp_send_json(0);

}

function dt_query_taxonomy( $id ) {
    $series = get_the_terms( $id, 'series' );
    return isset($series[0]->slug) ? [[ 'taxonomy' => 'series', 'field' => 'slug',
        'terms' => $series[0]->slug, 'operator' => 'IN' ]] : false;
}

// Get Schedule
function dt_get_schedule( $schedule = array() ) {

	$i = 0;
	while ($i < 7) { // 7 days in a week

		$hours = ( $i * 24 ) - 6; // 6 hour offset for timezone
		$time = strtotime('now +' . $hours . ' hours');
		$day = date('l',$time);
		$date = strtolower(date('M j',$time));
		$date_long = date('F jS',$time);
		$active = $i == 0 ? true : false;

		$schedule[$day] = array(
			'name' => $day,
			'date' => $date,
			'date_long' => $date_long,
			'shows' => array(),
			'active' => $active,
		);

		$i++;

	}

	$series = get_terms([ 'taxonomy' => 'series', 'hide_empty' => false ]);

	foreach ( $series as $title ) {

		$day = get_term_meta( $title->term_id , 'dt_series_day', true );

		if ( $day ) {

			// Reset Content
			$content = NULL;

			// Get Most Recent Show From Series
			$query = new WP_Query( array(
				'post_type' => 'dt_shows', 'posts_per_page' => 1,
				'tax_query' => array( array (
					'taxonomy' => 'series', 'field' => 'slug',
					'terms' => $title->slug,
				)),
			));

			// Display Latest Show
			if ( isset($query->posts[0]->ID) ) {

				$show_date = get_post_meta( $query->posts[0]->ID, 'dt-show-time', true );
				$show_time = $show_date ? date('g:ia',$show_date) . ' CST' : '';
				$show_date = $show_date ? strtolower(date('M j',$show_date)) : '';

				if ( $show_date == $schedule[$day]['date'] ) {

					$id = $query->posts[0]->ID;
					$post_title = $query->posts[0]->post_title;
					$image = get_the_post_thumbnail_url($id);

					// Get Post Excerpt Or Create It
					if ( $query->posts[0]->post_excerpt ) {
						$content = $query->posts[0]->post_excerpt;

					} else {
						$content = strip_tags($query->posts[0]->post_content);
						$content = substr($content, 0, 200) . '...';
					}

				}

			}

			// Display Placeholder
			 if ( !isset($content) ) {
				$id = $post_title = false;
				$image_url = get_term_meta( $title->term_id , 'dt_series_image', true );
				$image = $image_url ? $image_url : '/wp-content/themes/toasted-v3.0/library/images/dt-schedule-tbd.jpg';
				$content = $title->description ? $title->description : false;
				$time = get_term_meta( $title->term_id , 'dt_series_time', true );
				$show_time = $time ? date('g:ia',strtotime($time)) . ' CST' : '';
			}

			// Add Show
			$schedule[$day]['shows'][] = array(
				'ID' => $id,
				'post_title' => $post_title,
				'post_content' => $content,
				'image' => $image,
				'series' => $title->name,
				'series-slug' => $title->slug,
				'time' => $show_time,
			);

		}

	}

	return $schedule ? $schedule : false;

}

function dt_schedule_class( $day, $class ) {
	$class = $class . ' ' . strtolower($day['name']);
	$class = $day['active'] ? $class . ' dt-active' : $class;
	return $class;
}

function dt_get_series_header( $image = '' ) {

	if ( is_tax() ) {
		$term = get_queried_object();
		if ( $term->taxonomy == 'series' ) {
			$image_url = get_term_meta( $term->term_id , 'dt_series_image', true );
			$image = $image_url ? '<div class="dt-series-header"><img src="' .
				$image_url . '"><div class="dt-series-blur" style="background-image:url('.$image_url.')"></div></div>' : '';
		}
	}

	return $image;

}

function dt_get_latest() {
    $latest['this_week'] =  new WP_Query([ 'post_type' => 'dt_shows', 'posts_per_page' => 20,
        'date_query' => [[ 'after'  => date('F jS Y', strtotime('-1 weeks')),
        'before' => date('F jS Y', strtotime('+1 hour')), 'inclusive' => true ]]]);
    $latest['last_week'] =  new WP_Query([ 'post_type' => 'dt_shows', 'posts_per_page' => 20,
        'date_query' => [[ 'after'  => date('F jS Y',strtotime('-2 weeks')),
        'before'    => date('F jS Y',strtotime('-1 weeks')), 'inclusive' => true ]]]);
    return $latest;
}
