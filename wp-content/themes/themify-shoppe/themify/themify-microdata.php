<?php
/**
 * Adds Schema.org Microdata Support
 * Adds Organization fields in User Profile Page
 * @since 2.6.6
 * @return json
 */

if( ! class_exists( 'Themify_Microdata' ) ) :
class Themify_Microdata {

        private static $output = array();

        public static function init() {
		if ( is_admin() ) {
                    add_filter( 'themify_metabox/user/fields', array( __CLASS__, 'custom_user_meta_fields' ) );
		} else {
                    add_action( 'themify_body_start', array( __CLASS__, 'schema_markup_homepage' ) );
                    add_action( 'themify_content_start', array( __CLASS__, 'schema_markup_page' ) );
                    add_action( 'themify_post_start', array( __CLASS__, 'schema_markup_post' ) );
                    add_action( 'wp_footer', array( __CLASS__, 'display_schema_markup' ),9999 );
                    add_filter( 'get_avatar', array( __CLASS__, 'authorbox_microdata' ) );
                    if ( themify_is_woocommerce_active() ) {
                            add_action( 'woocommerce_after_shop_loop_item', array( __CLASS__, 'schema_markup_wc_product' ) );
                    }
		}
	}

	public static function schema_markup_homepage() {
		// Homepage
		if ( is_home() || is_front_page() && ! is_paged() ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_inactive( 'wordpress-seo/wp-seo.php' ) ) {
				$microdata = array(
					'@context' => 'https://schema.org',
					'@type' => 'WebSite',
					'url' => esc_url( themify_home_url() ),
					'potentialAction' => array(
						'@type' => 'SearchAction',
						'target' => add_query_arg( [ 's' => '{search_term_string}' ], esc_url( themify_home_url() ) ),
						'query-input' => 'required name=search_term_string'
					)
				);
				self::$output[] = $microdata;
			}
		}
	}

	// Pages
	public static function schema_markup_page() {
		global $post;

		if( ! isset( $post ) ) {
			return;
		}

		$current_page_url   = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

                if ( themify_is_shop() ) {
                    self::$output[] = array(
                            '@context' => 'https://schema.org',
                            '@type' => 'Store',
                            'mainEntityOfPage' => array(
                                    '@type' => 'WebPage',
                                    '@id' => $current_page_url
                            )
                    );
		}
                elseif ( is_author() ) {
                    $user_email=get_the_author_meta('user_email');
                    $author_avatar_data = get_avatar_data( $user_email );
                    self::$output[] = array(
                            '@context' => 'https://schema.org',
                            '@type' => 'ProfilePage',
                            'mainEntityOfPage' => array(
                                '@type' => 'WebPage',
                                '@id' => $current_page_url,
                            ),
                            'author' => array(
                                '@type' => 'Person',
                                'name' => get_the_author()
                            ),
                            'image' => array(
                                '@type' => 'ImageObject',
                                'url' => get_avatar_url( $user_email ),
                                'width' => $author_avatar_data['width'],
                                'height' => $author_avatar_data['height']
                            ),
                            'description' => get_the_author_meta('description'),
                            'url' => get_the_author_meta('user_url')
                    );
                    unset($author_avatar_data,$user_email);
                }
                elseif ( is_search() ) {
                    self::$output[] = array(
                            '@context' => 'https://schema.org',
                            '@type' => 'SearchResultsPage',
                            'mainEntityOfPage' => array(
                                    '@type' => 'WebPage',
                                    '@id' => $current_page_url,
                            )
                    );
		}
		elseif( is_page() && ! post_password_required() ) {
                    $microdata = array(
                            '@context' => 'https://schema.org',
                            '@type' => 'WebPage',
                            'mainEntityOfPage' => array(
                                    '@type' => 'WebPage',
                                    '@id' => get_permalink(),
                            ),
                            'headline' => get_the_title(),
                            'datePublished' => get_the_time('c'),
                            'dateModified' => get_the_modified_time('c'),
                            'description' => $post->post_excerpt
                    );
                    if( has_post_thumbnail() ) {
                        $post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
                        $microdata['image'] = array(
                                '@type' => 'ImageObject',
                                'url' => $post_image[0],
                                'width' => $post_image[1],
                                'height' => $post_image[2]
                        );
                        $post_image=null;
                    }
                    $comments    = get_comments(array('post_id' => $post->ID,'status' => 'approve'));
                    if(!empty($comments)){
                        $comment_count  = 0;
                        foreach ( $comments as $comment ) {
                                $microdata['comment'][] = array(
                                    '@type' => 'Comment',
                                    'author' => array(
                                            '@type' => 'Person',
                                            'name' => $comment->comment_author
                                    ),
                                    'text' => $comment->comment_content
                                );
                                ++$comment_count;
                        }
                        $microdata['commentCount']=$comment_count;
                    }
                    $comments=null;
                    self::$output[] = $microdata;
		}
                $microdata=null;

	}

	// Posts
	public static function schema_markup_post() {
		if ( post_password_required() ) {
			return;
		}
		global $post;
                $post_type=$post->post_type;
		$creative_types = apply_filters( 'tb_creative_works_items', array( 'audio', 'highlight', 'quote', 'portfolio', 'testimonial', 'video' ) );
		// Cases
		if ( is_singular('post') ) {
                    $post_schema_type = 'BlogPosting';
		} elseif ( in_array( $post_type, $creative_types,true ) ) {
                    $post_schema_type = 'CreativeWork';
		} elseif ( $post_type === 'team' ) {
                    $post_schema_type = 'Person';
		} elseif ( $post_type=== 'event' ) {
                    $post_schema_type = 'Event';
		} elseif ( $post_type === 'gallery' ) {
                    $post_schema_type = 'ImageGallery';
		} elseif ( $post_type === 'press' ) {
                    $post_schema_type = 'NewsArticle';
		} else {
                    if($post_type!=='post'){
                        return;
                    }
                    $post_schema_type = 'Article';
		}
                
		$post_title     = get_the_title();
		$date_added     = get_the_time('c');
		$date_modified  = get_the_modified_time('c');
		$permalink      = get_permalink();
		$excerpt        = get_the_excerpt();
		$post_image     = has_post_thumbnail()?wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' ):null;
               
		if ($post_type==='post' || $post_type==='press' || $post_type==='gallery') {
                        $publisher_logo = get_the_author_meta('user_meta_org_logo');
                        $logo_width = $logo_height = 0;
                        if ( $publisher_logo ) {
                            $publisher_logo_id = themify_get_attachment_id_from_url( $publisher_logo);
                            if( $publisher_logo_id ) {
                                $publisher_logo_meta = wp_get_attachment_metadata( $publisher_logo_id );
                                if ( ! empty( $publisher_logo_meta['width'] ) ) {
                                    $logo_width = $publisher_logo_meta['width'];
                                }
                                if ( ! empty( $publisher_logo_meta['height'] ) ) {
                                    $logo_height = $publisher_logo_meta['height'];
                                }
                            }
                        }
			$microdata = array(
				'@context' => 'https://schema.org',
				'@type' => $post_schema_type,
				'mainEntityOfPage' => array(
					'@type' => 'WebPage',
					'@id' => $permalink
				),
				'headline' => $post_title,
				'datePublished' => $date_added,
				'dateModified' => $date_modified,
				'author' => array(
					'@type' => 'Person',
					'name' => get_the_author()
				),
				'publisher' => array(
					'@type' => 'Organization',
					'name' => get_the_author_meta('user_meta_org_name'),
					'logo' => array(
						'@type' => 'ImageObject',
						'url' => $publisher_logo,
						'width' => $logo_width,
						'height' => $logo_height
					),
				),
				'description' => $excerpt
			);
                        
                        $comments = is_single() ?get_approved_comments($post->ID, array('type'=>'comment','no_found_rows'=>false) ):null;
                        if (!empty($comments)) {
                            foreach ( $comments as $comment ) {
                                $microdata['comment'][] = array(
                                    '@type' => 'Comment',
                                    'author' => array(
                                            '@type' => 'Person',
                                            'name' => $comment->comment_author
                                    ),
                                    'text' => $comment->comment_content
                                );
                            }
                            $microdata['commentCount']=count($microdata['comment']);
                        }
		}
                elseif ( $post_type === 'event') {
                    if(class_exists( 'Themify_Event_Post' ) ){
			$microdata = array(
				'@context' => 'https://schema.org',
				'@type' => $post_schema_type,
				'mainEntityOfPage' => array(
					'@type' => 'WebPage',
					'@id' => $permalink
				),
				'name' => $post_title,
				'description' => $excerpt,
				'startDate' => themify_get( 'start_date' ),
                                'endDate' => themify_get( 'end_date' ),
				'location' => array(
					'@type' => 'Place',
					'name' => themify_get( 'location' ),
					'address' => themify_get( 'map_address' )
				)
			);
			if ( themify_check( 'buy_tickets' ) ) {
                            $microdata['offers'] = array(
                                    '@type' => 'Offer',
                                    // "price" => "",
                                    'url' => themify_get( 'buy_tickets' )
                            );
			}
                    }
		}  
                elseif ( in_array( $post_type, $creative_types,true ) ) { // Audio, Highlight, Quote, Portfolio, Testimonial, Video
			$microdata = array(
				'@context' => 'https://schema.org',
				'@type' => $post_schema_type,
				'mainEntityOfPage' => array(
					'@type' => 'WebPage',
					'@id' => $permalink
				),
				'headline' => $post_title,
				'datePublished' => $date_added,
				'dateModified' => $date_modified,
				'description' => $excerpt
			);
                        $post_video=themify_get( 'video_url' );
			if ( $post_video!= '' ) {
                            $video_meta = self::fetch_video_meta( $post_video );
                            if( $video_meta!==false ) {
                                    $microdata['video'] = array(
                                            '@type' => 'VideoObject',
                                            'url' => $post_video
                                    );
                                    if( isset( $video_meta->thumbnail_url ) ) {
                                            $microdata['video']['thumbnailUrl'] = $video_meta->thumbnail_url;
                                    }
                                    $microdata['video']['uploadDate'] = isset( $video_meta->upload_date )?$video_meta->upload_date:$date_added;
                                    $microdata['video']['description'] = isset( $video_meta->description )?$video_meta->description:$excerpt;
                                    $microdata['video']['name'] = isset( $video_meta->title )?$video_meta->title:$post_title;
                            }
			}
		} 
                elseif ( $post_type === 'team' ) {
			$microdata = array(
				'@context' => 'https://schema.org',
				'@type' => $post_schema_type,
				'mainEntityOfPage' => array(
					'@type' => 'WebPage',
					'@id' => $permalink
				),
				'name' => $post_title,
				'description' => $excerpt
			);
		}
                if(isset($microdata)){
                    if ( ! empty( $post_image ) ) {
                            $microdata['image'] = array(
                                    '@type' => 'ImageObject',
                                    'url' => $post_image[0],
                                    'width' => $post_image[1],
                                    'height' => $post_image[2]
                            );
                    }
                    self::$output[] = $microdata;
                    $microdata=null;
                }
	}

	// WooCommerce Products
	public static function schema_markup_wc_product() {
		// Product
		if ( ! is_singular( 'product' ) && ! post_password_required() ) {
			global $post, $product;

                        $post = is_int($post)?get_post($post):$post;
                        if ( !is_object( $product ) || !is_object($post) ) {
                            return;
                        }

			$price_valid_until = get_post_meta( $post->ID, '_sale_price_dates_to', true );
			// Output only for product loops, not single product.
			// Single product metadata added by WooCommerce.
			$microdata = array(
				'@context' => 'https://schema.org',
				'@type' => 'Product',
				'name' => $product->get_title(),
				'description' => $post->post_excerpt,
				'sku' => $product->get_sku(),
				'brand' => '',
				'offers' => array(
                                    '@type' => 'Offer',
                                    'price' => $product->get_price(),
                                    'priceCurrency' => apply_filters( 'woocommerce_currency', get_option( 'woocommerce_currency' ) ),
                                    'priceValidUntil' => $price_valid_until?date_i18n( 'Y-m-d', $price_valid_until ):'',
                                    'availability' => 'https://schema.org/InStock',
                                    'url' => get_permalink( $product->get_id() )
				)
			);
			if ( $product->get_review_count() && ('yes' === get_option( 'woocommerce_enable_reviews', 'yes' )) ) {
				$microdata['aggregateRating'] = array(
                                    '@type'       => 'AggregateRating',
                                    'ratingValue' => $product->get_average_rating(),
                                    'reviewCount' => $product->get_review_count()
				);
			}
			if ( has_post_thumbnail() ) {
				$post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),  'large' );
				if ( ! empty( $post_image ) ) {
					$microdata['image'] = array(
                                            '@type' => 'ImageObject',
                                            'url' => $post_image[0],
                                            'width' => $post_image[1],
                                            'height' => $post_image[2]
					);
				}
			}
			self::$output[] = $microdata;
                        $microdata=null;
		}
	}

	// Output Schema.org JSON-LD
	public static function display_schema_markup() {
		self::$output = apply_filters( 'themify_microdata', self::$output );
		if ( ! empty( self::$output ) ) {
			echo '<!-- SCHEMA BEGIN --><script type="application/ld+json">',json_encode( self::$output ),'</script><!-- /SCHEMA END -->';
			self::$output = array();
		}
	}

	/**
	 * Adds itemprop='image' microdata to avatar called by author box
	 * @param string $avatar The original markup for avatar
	 * @return string Modified markup with microdata
	 */
	public static function authorbox_microdata( $avatar ) {
		return str_replace( "class='avatar", "itemprop='image' class='avatar", $avatar );
	}

	public static function custom_user_meta_fields( $fields ) {
		Themify_Metabox::get_instance()->admin_enqueue_scripts();
		Themify_Metabox::get_instance()->enqueue();
		$fields['themify-microdata'] = array(
			'title' => __( 'Organization', 'themify' ),
			'description' => sprintf( __( 'These fields are required to fully comply with <a href="%s">Rich Snippets</a> standards.', 'themify' ), 'https://developers.google.com/structured-data/rich-snippets/articles' ),
			'fields' => array(
				array(
					'name' => 'user_meta_org_name',
					'title' => __( 'Organization Name', 'themify' ),
					'type' => 'textbox',
				),
				array(
					'name' => 'user_meta_org_logo',
					'title' => __( 'Organization Logo', 'themify' ),
					'description' => __( 'Organizaition Logo should be no wider than 600px, and no taller than 60px.', 'themify' ),
					'type' => 'image',
					'meta' => array()
				),
			),
		);

		return $fields;
	}

	private static function fetch_video_meta( $video_url ) {
		$cache_key = 'themify_video_meta_' . $video_url;
		if ( false === ( $meta = Themify_Storage::get( $cache_key) ) ) {
			if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match ) ) {
				$request = wp_remote_get( "https://www.youtube.com/oembed?url=". urlencode( $video_url ) ."&format=json", array( 'sslverify' => false ) );
			} elseif ( false !== stripos( $video_url, 'vimeo' ) ) {
				$request = wp_remote_get( 'https://vimeo.com/api/oembed.json?url='.urlencode( $video_url ), array( 'sslverify' => false ) );
			} elseif( false !== stripos( $video_url, 'funnyordie' ) ) {
				$request = wp_remote_get( 'https://www.funnyordie.com/oembed.json?url='.urlencode( $video_url ), array( 'sslverify' => false ) );
			} elseif( false !== stripos( $video_url, 'dailymotion' ) ) {
				$video_id = parse_url( $video_url, PHP_URL_PATH );
				$request = wp_remote_get( 'https://api.dailymotion.com/' . str_replace( '/embed/', '', $video_id ) . '?fields=thumbnail_large_url', array( 'sslverify' => false ) );
			} elseif( false !== stripos( $video_url, 'blip' ) ) {
				$request = wp_remote_get( 'https://blip.tv/oembed?url=' . $video_url, array( 'sslverify' => false ) );
			}

			if ( isset( $request ) && ! is_wp_error( $request ) ) {
				$response_body = wp_remote_retrieve_body( $request );
				if ( '' != $response_body ) {
					$meta = json_decode( $response_body );
					Themify_Storage::set( $cache_key, $meta, YEAR_IN_SECONDS );
					return $meta;
				}
			}
		} else {
			return $meta;
		}

		return false;
	}
}
Themify_Microdata::init();
endif;