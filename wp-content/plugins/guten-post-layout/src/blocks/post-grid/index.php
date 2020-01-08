<?php
function guten_post_layout_render_post_grid( $attributes ){

	if( $attributes['postLayout'] === 'slides' ) {
		wp_enqueue_script( 'slick' );
		wp_enqueue_script( 'guten-post-layout-custom' );
	}

	$taxonomyName = isset($attributes['taxonomyName']) ? $attributes['taxonomyName'] : '';
	$tax_query = '';
	if( isset($attributes['categories']) && !empty($attributes['categories']) ){
		$tax_query = array(
			'taxonomy' => $taxonomyName,
			'field'    => 'term_id',
			'terms'    => isset($attributes['categories']) ? $attributes['categories'] : ''
		);
	}

	$recent_posts = wp_get_recent_posts( array(
		'post_type' => $attributes['post_type'],
		'numberposts' => $attributes['postscount'],
		'post_status' => $attributes['post_type'] === 'attachment' ? 'inherit' : 'publish',
		'order'       => $attributes['order'],
		'orderby'     => $attributes['orderBy'],
		'ignore_sticky_posts' => 1,
		'tax_query' => array($tax_query),
	));


	if ( count( $recent_posts ) === 0 ) {
		return;
	}

	$markup = '';
	$target = isset($attributes['linkTarget']) && !empty($attributes['linkTarget']) ? '_blank' : '_self';
	$widthClass = (isset($attributes['postBlockWidth']) && $attributes['postBlockWidth'] ) ? 'align'.$attributes['postBlockWidth'] : '';
	$postItemHeight = (isset($attributes['equalHeight']) && $attributes['equalHeight'] ) ? 'equal-height' : '';

	if( $attributes['postLayout'] === 'slides'){

		$random_number = rand(10, 1000);
		$markup .= sprintf('<div id="gpl-slick-slider-'.esc_attr($random_number).'" class="gpl-slick-slider '.esc_attr($widthClass).'" data-count="%1$d" data-slides-to-show="%2$s" data-autoplay="%3$s" data-navigation="%4$s">', count( $recent_posts ), $attributes['slidesToShow'], $attributes['autoPlay'], $attributes['navigation']);

		$markup .= sprintf('<div  class="gpl-post-slider-one wp-block-guten-post-layout-post-grid post-grid-view gpl-d-flex gpl-flex-wrap %1$s" data-layout="%1$s" style="--item-padding-left-right : '.$attributes['columnGap'].'px; --item-margin-bottom : '.($attributes['columnGap']*2).'px; --item-height : '.(300-$attributes['columnGap']).'px; --image-height:'.($attributes['imageHeight']).'px;" >', $attributes['carouselLayoutStyle']);

		foreach ( $recent_posts as $post ){
			$post_id = $post['ID'];
			$post_thumbnail_id = get_post_thumbnail_id($post_id);

			$gridView = $attributes['postLayout'] === 'grid' ? 'post-item gpl-mb-30 gpl-column-'.$attributes['columns'].'' : 'post-item gpl-mb-30';

			$image = $attributes['post_type'] === 'attachment' ? $post['guid'] : wp_get_attachment_image_src( $post_thumbnail_id, '' . $attributes['postImageSizes'] . '', false)[0];

			$hasImage = $image ? 'has-image' : '';
			$contentHasImage = $image ? 'content-has-image' : '';

			// start the post-item wrap
			$markup .= sprintf( '<article class="%1$s %2$s">', esc_attr($gridView), $attributes['carouselLayoutStyle'] );
			// start the post content wrap
			$markup .= '<div class="post-content-area ' . $attributes['align'] . ' '.$hasImage.'">';

			if ( $attributes['carouselLayoutStyle'] === 'skin1') {
				$markup .= '<a class="active-post-link" target="'.$target.'" href=' . esc_url( get_permalink( $post_id ) ) . '></a>';
			}

			if( $attributes['displayPostImage'] && $image ) {
				$markup .= sprintf( '<div class="post-image"><a href="%1$s" target="%3$s" rel="bookmark"><img src="%2$s"/></a></div>',
					esc_url( get_permalink( $post_id ) ),
					$image,
					$target
				);
			}

			// start the inner post content wrap
			$markup .= '<div class="gpl-inner-post-content '.$contentHasImage.'">';

			// start the post meta wrap
			$markup .= '<div class="post-meta">';

			if( isset($attributes['displayPostAuthor']) && $attributes['displayPostAuthor'] && $attributes['carouselLayoutStyle'] !== 'g_skin2' ) {
				$markup .= sprintf(
					'<a target="_blank" href="%2$s">%1$s</a>',
					esc_html( get_the_author_meta( 'display_name', $post['post_author'] ) ),
					esc_url( get_author_posts_url($post['post_author']) )
				);
			}

			if( isset($attributes['displayPostDate']) && $attributes['displayPostDate'] ) {
				$markup .= sprintf(
					'<time datetime="%1$s">%2$s</time>',
					esc_attr( get_the_date( 'c', $post_id ) ),
					esc_html( get_the_date( '', $post_id ) )
				);
			}

			$markup .= '</div>';
			// close the post meta wrap

			// start the post title wrap
			$markup .= sprintf( '<h2 class="post-title"><a href="%1$s" target="%3$s" rel="bookmark">%2$s</a></h2>',
				esc_url( get_permalink($post_id) ),
				esc_html( get_the_title($post_id)),
				$target
			);
			// close the post title wrap

			// start the post excerpt wrap
			$content = get_the_excerpt( $post_id );
			if( $content && $attributes['displayPostExcerpt'] && $attributes['carouselLayoutStyle'] !== 'g_skin1' && $attributes['carouselLayoutStyle'] !== 'g_skin2' ) {
				$markup .= sprintf( ' <div class="post-excerpt"><div><p>%1$s</p></div></div>',
					wp_kses_post( $content )
				);
			}
			// close the post excerpt wrap

			// start the post read more wrap
			if( isset($attributes['displayPostReadMoreButton']) && $attributes['displayPostReadMoreButton'] && $attributes['carouselLayoutStyle'] !== 'g_skin1' && $attributes['gridLayoutStyle'] !== 'g_skin2') {
				$markup .= sprintf( '<div><a class="post-read-moore" href="%1$s" target="%3$s" rel="bookmark">%2$s</a></div>', esc_url( get_permalink( $post_id ) ),esc_html( $attributes['postReadMoreButtonText'] ), $target );
			}
			// close the post read more wrap

			$markup .= '</div>';
			$markup .= '<div class="gpl-overlay-effect"></div>';
			$markup .= '</div>';
			// close the post content wrap

			$markup .= '</article>';
			// close the post-item wrap
		}

		$markup .= '</div>';
		$markup .= '</div>';
	}


	if( $attributes['postLayout'] !== 'slides') {

		$columnGap = isset($attributes['columnGap']) ? $attributes['columnGap'] : '';
		$imageHeight = isset($attributes['imageHeight']) && is_numeric($attributes['imageHeight']) ? $attributes['imageHeight'].'px' : null;
		$itemHeight = (int)$imageHeight && (int)$columnGap ? ((int)$imageHeight/2) - $columnGap .'px' : '285px';

		$firstPostItem = count($recent_posts) > 0 && $attributes['gridLayoutStyle'] === 'g_skin2' ? array_splice($recent_posts, 0, 1) : '';

		$gridViewWrapper = $attributes['postLayout'] === 'list' ? 'wp-block-guten-post-layout-post-grid post-grid-view gpl-d-flex gpl-flex-wrap list-layout' : 'wp-block-guten-post-layout-post-grid post-grid-view gpl-d-flex gpl-flex-wrap';


		$markup .= sprintf( '<div class="%1$s %2$s %3$s" style="--item-padding-left-right : '.$columnGap.'px; --item-margin-bottom : '.($columnGap*2).'px; --item-height: '.$itemHeight.'; --image-height:'.($imageHeight).';">', esc_attr( $gridViewWrapper ), $attributes['gridLayoutStyle'], $widthClass );
		if ( $firstPostItem ) {
			$post_id           = $firstPostItem[0]['ID'];
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );
			$post              = $firstPostItem[0];

			// start the post-item wrap
			$markup .= '<div class="gpl-column-4">';

			$gridView = $attributes['postLayout'] === 'grid' ? 'post-item gpl-mb-30 gpl-column-'
			                                                   . $attributes['columns'] . ''
				: 'post-item gpl-mb-30';

			$markup .= sprintf( '<article class="%1$s %2$s">', esc_attr( $gridView ), $attributes['gridLayoutStyle'] );

			// start the post content wrap
			$markup .= '<div class="post-content-area ' . $attributes['align'] . '">';
			if ( $attributes['gridLayoutStyle'] === 'g_skin2' || $attributes['gridLayoutStyle'] === 'g_skin1') {
				$markup .= '<a class="active-post-link" target="'.$target.'" href=' . esc_url( get_permalink( $post_id ) ) . '></a>';
			}

			$image = $attributes['post_type'] === 'attachment' ? $post['guid'] : wp_get_attachment_image_src( $post_thumbnail_id, '' . $attributes['postImageSizes'] . '', false)[0];
			if ( $attributes['displayPostImage'] && $image ) {
				$markup .= sprintf( '<div class="post-image"><a href="%1$s" target="%3$s" rel="bookmark"><img src="%2$s"/></a></div>',
					esc_url( get_permalink( $post_id ) ),
					$image,
					$target
				);
			}

			// start the inner post content wrap
			$markup .= '<div class="gpl-inner-post-content">';

			// start the post meta wrap
			$markup .= '<div class="post-meta">';

			if ( isset( $attributes['displayPostAuthor'] ) && $attributes['displayPostAuthor']
			     && $attributes['gridLayoutStyle'] !== 'g_skin2'
			) {
				$markup .= sprintf(
					'<a target="_blank" href="%2$s">%1$s</a>',
					esc_html( get_the_author_meta( 'display_name', $post['post_author'] ) ),
					esc_url( get_author_posts_url( $post['post_author'] ) )
				);
			}

			if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
				$markup .= sprintf(
					'<time datetime="%1$s">%2$s</time>',
					esc_attr( get_the_date( 'c', $post_id ) ),
					esc_html( get_the_date( '', $post_id ) )
				);
			}

			$markup .= '</div>';
			// close the post meta wrap

			// start the post title wrap
			$markup .= sprintf( '<h2 class="post-title"><a href="%1$s" target="%3$s" rel="bookmark">%2$s</a></h2>',
				esc_url( get_permalink( $post_id ) ),
				esc_html( get_the_title( $post_id ) ),
				$target
			);
			// close the post title wrap

			// start the post excerpt wrap
			$content = get_the_excerpt( $post_id );
			if ( $content && $attributes['displayPostExcerpt'] && $attributes['gridLayoutStyle'] !== 'g_skin2') {
				$markup .= sprintf( ' <div class="post-excerpt"><div><p>%1$s</p></div></div>',
					wp_kses_post( $content )
				);
			}
			// close the post excerpt wrap

			// start the post read more wrap
			if ( isset( $attributes['displayPostReadMoreButton'] ) && $attributes['displayPostReadMoreButton'] && $attributes['gridLayoutStyle'] !== 'g_skin2') {
				$markup .= sprintf( '<div><a class="post-read-moore" href="%1$s" target="%3$s" rel="bookmark">%2$s</a></div>',
					esc_url( get_permalink( $post_id ) ), esc_html( $attributes['postReadMoreButtonText'] ), $target );
			}
			// close the post read more wrap

			$markup .= '</div>';
			$markup .= '<div class="gpl-overlay-effect"></div>';
			$markup .= '</div>';
			// close the post content wrap

			$markup .= '</article>';
			// close the post-item wrap
			$markup .= '</div>';
		}
		$parentClasses = $attributes['gridLayoutStyle'] === 'g_skin2' ? 'gpl-column-8 gpl-d-flex gpl-flex-wrap' : 'gpl-column-12 gpl-d-flex gpl-flex-wrap';

		$markup .= '<div class="'.esc_attr($parentClasses).'">';
		foreach ( $recent_posts as $post ) {
			$post_id = $post['ID'];
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );

			$gridView = $attributes['postLayout'] === 'grid' ? 'post-item gpl-mb-30 gpl-column-'
			                                                   . $attributes['columns'] . ''
				: 'post-item gpl-mb-30';

			// start the post-item wrap
			$markup .= sprintf( '<article class="%1$s %2$s">', esc_attr( $gridView ), $attributes['gridLayoutStyle'] );

			// start the post item wrapper
			$markup .= '<div class="post-item-wrapper '.$postItemHeight.'">';

			// start the post content wrap
			$markup .= '<div class="post-content-area ' . $attributes['align'] . '">';

			if ( $attributes['gridLayoutStyle'] === 'g_skin2' || $attributes['gridLayoutStyle'] === 'g_skin1') {
				$markup .= '<a class="active-post-link" target="'.$target.'" href=' . esc_url( get_permalink( $post_id ) ) . '></a>';
			}

			$image = $attributes['post_type'] === 'attachment' ? $post['guid'] : wp_get_attachment_image_src( $post_thumbnail_id, '' . $attributes['postImageSizes'] . '', false)[0];
			if ( $attributes['displayPostImage'] && $image ) {
				$markup .= sprintf( '<div class="post-image"><a href="%1$s" target="%3$s" rel="bookmark"><img src="%2$s"/></a></div>',
					esc_url( get_permalink( $post_id ) ),
					$image,
					$target
				);
			}

			// start the inner post content wrap
			$markup .= '<div class="gpl-inner-post-content">';

			// start the post meta wrap
			$markup .= '<div class="post-meta">';

			if ( isset( $attributes['displayPostAuthor'] ) && $attributes['displayPostAuthor']
			     && $attributes['gridLayoutStyle'] !== 'g_skin2'
			) {
				$markup .= sprintf(
					'<a target="_blank" href="%2$s">%1$s</a>',
					esc_html( get_the_author_meta( 'display_name', $post['post_author'] ) ),
					esc_url( get_author_posts_url( $post['post_author'] ) )
				);
			}

			if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
				$markup .= sprintf(
					'<time datetime="%1$s">%2$s</time>',
					esc_attr( get_the_date( 'c', $post_id ) ),
					esc_html( get_the_date( '', $post_id ) )
				);
			}

			$markup .= '</div>';
			// close the post meta wrap

			// start the post title wrap
			$markup .= sprintf( '<h2 class="post-title"><a href="%1$s" target="%3$s" rel="bookmark">%2$s</a></h2>',
				esc_url( get_permalink( $post_id ) ),
				esc_html( get_the_title( $post_id ) ) ,
				$target
			);
			// close the post title wrap

			// start the post excerpt wrap
			$content = get_the_excerpt( $post_id );
			if ( $content && $attributes['displayPostExcerpt'] && $attributes['gridLayoutStyle'] !== 'g_skin1'
			     && $attributes['gridLayoutStyle'] !== 'g_skin2'
			) {
				$markup .= sprintf( ' <div class="post-excerpt"><div><p>%1$s</p></div></div>',
					wp_kses_post( $content )
				);
			}
			// close the post excerpt wrap

			// start the post read more wrap
			if ( isset( $attributes['displayPostReadMoreButton'] ) && $attributes['displayPostReadMoreButton']
			     && $attributes['gridLayoutStyle'] !== 'g_skin1'
			     && $attributes['gridLayoutStyle'] !== 'g_skin2'
			) {
				$markup .= sprintf( '<div><a class="post-read-moore" href="%1$s" target="%3$s" rel="bookmark">%2$s</a></div>',
					esc_url( get_permalink( $post_id ) ), esc_html( $attributes['postReadMoreButtonText'] ), $target);
			}
			// close the post read more wrap

			$markup .= '</div>';
			$markup .= '<div class="gpl-overlay-effect"></div>';
			$markup .= '</div>';
			// close the post content wrap
			$markup .= '</div>';
			// close the post item wrapper

			$markup .= '</article>';
			// close the post-item wrap
		}
		$markup .= '</div>';

		if( isset($attributes['displayPostCtaButton']) && $attributes['displayPostCtaButton'] && isset($attributes['postCtaButtonLink']) && $attributes['postCtaButtonLink'] ) {
			$icon = isset($attributes['displayCtaButtonIcon']) && $attributes['displayCtaButtonIcon'] ? '<i class="gpl-blocks-icon-long-arrow-right"></i>' : '';
			$markup .= sprintf( '<div class="gpl-cta-wrapper %6$s %7$s">
				<a class="gpl-cta-btn %1$s" href="%2$s" target="%3$s" rel="bookmark">%4$s %5$s</a>
				</div>',
				$attributes['postCtaButtonStyle'] ? 'gpl-cta-fill-btn' : '',
				esc_url( $attributes['postCtaButtonLink'] ),
				$attributes['CtaLinkTarget'],
				esc_html( $attributes['postCtaButtonText'] ),
				$icon,
				$attributes['postCtaButtonAlign'],
				$widthClass
			);
		}
		$markup .= '</div>';
	}

	return $markup;
}

function guten_post_layout_register_post_grid(){

	if( !function_exists('register_block_type') ){
		return;
	}

	register_block_type( 'guten-post-layout/post-grid', array(
		'attributes' => array(
			'postBlockWidth' => array(
				'type' => 'string',
			),
			'align' => array(
				'type' => 'string',
				'default' => 'left',
			),
			'post_type' => array(
				'type' => 'string',
				'default' => 'post'
			),
			'categories' => array(
				'type' => 'string',
			),
			'team_cats' => array(
				'type' => 'string',
			),
			'postscount' => array(
				'type' => 'number',
				'default' => 5,
			),
			'taxonomyName' => array(
				'type' => 'string',
			),
			'order' => array(
				'type' => 'string',
				'default' => 'desc',
			),
			'orderBy'  => array(
				'type' => 'string',
				'default' => 'date',
			),
			'equalHeight' => array(
				'type' => 'boolen',
				'default' => true
			),
			'columns' => array(
				'type' => 'number',
				'default' => 2
			),
			'columnGap' => array(
				'type' => 'number',
				'default' => 15
			),
			'imageHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'postLayout' => array(
				'type' => 'string',
				'default' => 'grid',
			),
			'carouselLayoutStyle' => array(
				'type' => 'string',
				'default' => 'skin1',
			),
			'slidesToShow' => array(
				'type' => 'number',
				'default' => 2,
			),
			'autoPlay' => array(
				'type' => 'boolen',
				'default' => true
			),
			'navigation' => array(
				'type' => 'string',
				'default' => 'dots'
			),
			'gridLayoutStyle' => array(
				'type' => 'string',
				'default' => 'g_skin1',
			),
			'postImageSizes' => array(
				'type' => 'string',
				'default' => 'full',
			),
			'displayPostImage' => array(
				'type' => 'boolen',
				'default' => true
			),
			'displayPostDate' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'displayPostAuthor' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'displayPostExcerpt' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'displayPostReadMoreButton' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'postReadMoreButtonText' => array(
				'type' => 'string',
				'default' => 'Read More',
			),
			'linkTarget' => array(
				'type' => 'boolean',
				'default' => false,
			),

			'displayPostCtaButton' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'postCtaButtonStyle' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'postCtaButtonText' => array(
				'type' => 'string',
				'default' => 'View All',
			),
			'postCtaButtonLink' => array(
				'type' => 'string',
				'default' => '#',
			),
			'CtaLinkTarget' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'displayCtaButtonIcon' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'postCtaButtonAlign' => array(
				'type' => 'string',
				'default' => 'center',
			),
		),
		'render_callback' => 'guten_post_layout_render_post_grid',
	));


}
add_action( 'init', 'guten_post_layout_register_post_grid' );

/**
 * Create API fields for additional info
 */

function guten_post_layout_register_rest_fields() {
	$post_types = get_post_types();

	register_rest_field(
		$post_types,
		'guten_post_layout_featured_media_urls',
		array(
			'get_callback' => 'get_guten_post_layout_featured_media',
			'update_callback' => null,
			'schema' => array(
				'description' => __( 'Different Sized Featured Images', 'guten-post-layout'),
				'type' => 'array'
			)
		)
	);

}

add_action('rest_api_init', 'guten_post_layout_register_rest_fields');


function get_guten_post_layout_featured_media($object){
	$featured_media = wp_get_attachment_image_src( $object['featured_media'], 'full', false );

	return array(
		'thumbnail' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'thumbnail',
			false
		) : '',
		'guten_post_layout_landscape_large' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'guten_post_layout_landscape_large',
			false
		) : '',
		'guten_post_layout_portrait_large' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'guten_post_layout_portrait_large',
			false
		) : '',
		'guten_post_layout_square_large' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'guten_post_layout_square_large',
			false
		) : '',
		'guten_post_layout_landscape' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'guten_post_layout_landscape',
			false
		) : '',
		'guten_post_layout_portrait' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'guten_post_layout_portrait',
			false
		) : '',
		'guten_post_layout_square' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'guten_post_layout_square',
			false
		) : '',
		'full' => is_array($featured_media) ? $featured_media : '',
	);

}

/**
 * Add image sizes
 */
function guten_post_layout_image_sizes() {
	add_image_size( 'guten_post_layout_landscape_large', 1200, 800, true );
	add_image_size( 'guten_post_layout_portrait_large', 1200, 1800, true );
	add_image_size( 'guten_post_layout_square_large', 1200, 1200, true );
	add_image_size( 'guten_post_layout_landscape', 600, 400, true );
	add_image_size( 'guten_post_layout_portrait', 600, 900, true );
	add_image_size( 'guten_post_layout_square', 600, 600, true );
}
add_action( 'after_setup_theme', 'guten_post_layout_image_sizes' );



/**
 * Create API Order By Fields
 */
function guten_post_layout_register_rest_orderby_fields(){
	$post_types = get_post_types();

	foreach ( $post_types as $type ) {
		// This enables the orderby=menu_order for any Posts
		add_filter( "rest_{$type}_collection_params", 'guten_post_layout_add_orderby_params', 10, 1 );
	}
}
add_action( 'init', 'guten_post_layout_register_rest_orderby_fields' );


/**
 * Add menu_order to the list of permitted orderby values
 */
function guten_post_layout_add_orderby_params( $params ) {
	$params['orderby']['enum'][] = 'menu_order';
	$params['orderby']['enum'][] = 'rand';
	return $params;
}




