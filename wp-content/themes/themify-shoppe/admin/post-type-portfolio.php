<?php

/**************************************************************************************************
 * Portfolio Class - Shortcode
 **************************************************************************************************/

if ( ! class_exists( 'Themify_Portfolio' ) ) {

	class Themify_Portfolio {

		const POST_TYPE = 'portfolio';
		const TAX = 'portfolio-category';

		function __construct() {
			add_action( 'save_post', array($this, 'set_default_term'), 100, 2 );
			add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

			add_shortcode( self::POST_TYPE, array( $this, 'init_shortcode' ) );
			add_shortcode( 'themify_' . self::POST_TYPE . '_posts', array( $this, 'init_shortcode' ) );

		}

		/**
		 * Customize post type updated messages.
		 *
		 * @param $messages
		 *
		 * @return mixed
		 */
		function updated_messages( $messages ) {
			global $post;
						if(!$post){
							$post         = get_post();
						}
						$post_type        = $post->post_type;
			$post_type_object = get_post_type_object( $post_type );
			
			$view = get_permalink( $post->ID );

			$messages[ self::POST_TYPE ] = array(
				0 => '',
				1 => sprintf( __('%s updated. <a href="%s">View %s</a>.', 'themify'), $post_type_object->labels->name, esc_url( $view ), $post_type_object->labels->name ),
				2 => __( 'Custom field updated.', 'themify' ),
				3 => __( 'Custom field deleted.', 'themify' ),
				4 => sprintf( __('%s updated.', 'themify'), $post_type_object->labels->name ),
				5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'themify' ), $post_type_object->labels->name, wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('%s published.', 'themify'), $post_type_object->labels->name ),
				7 => sprintf( __('%s saved.', 'themify'), $post_type_object->labels->name ),
				8 => sprintf( __('%s submitted.', 'themify'), $post_type_object->labels->name ),
				9 => sprintf( __( '%s scheduled for: <strong>%s</strong>.', 'themify' ),
					$post_type_object->labels->name, date_i18n( __( 'M j, Y @ G:i', 'themify' ), strtotime( $post->post_date ) ) ),
				10 => sprintf( __( '%s draft updated.', 'themify' ), $post_type_object->labels->name )
			);
			return $messages;
		}

		/**
		 * Set default term for custom taxonomy and assign to post
		 * @param number
		 * @param object
		 */
		function set_default_term( $post_id, $post ) {
			if ( 'publish' === $post->post_status ) {
				$terms = wp_get_post_terms( $post_id, self::TAX );
				if ( empty( $terms ) ) {
					wp_set_object_terms( $post_id, __( 'Uncategorized', 'themify' ), self::TAX );
				}
			}
		}
		

		/**
		 * Returns link wrapped in paragraph either to the post type archive page or a custom location
		 * @param bool|string $more_link False does nothing, true goes to archive page, custom string sets custom location
		 * @param string $more_text
		 * @param string $post_type
		 * @return string
		 */
		function section_link( $more_link, $more_text, $post_type ) {
			if ( $more_link ) {
				if ( 'true' == $more_link ) {
					$more_link = get_post_type_archive_link( $post_type );
				}
				return '<p class="more-link-wrap"><a href="' . esc_url( $more_link ) . '" class="more-link">' . $more_text . '</a></p>';
			}
			return '';
		}

		/**
		 * Add shortcode to WP
		 * @param $atts Array shortcode attributes
		 * @return String
		 * @since 1.0.0
		 */
		function init_shortcode( $atts ) {
			$atts = is_array( $atts ) ? $atts : [];
			$args = array(
				'id' => '',
				'title' => '',
				'unlink_title' => '',
				'image' => 'yes', // no
				'image_w' => 290,
				'image_h' => 290,
				'display' => 'none', // excerpt, content
				'post_meta' => '', // yes
				'post_date' => '', // yes
				'more_link' => false, // true goes to post type archive, and admits custom link
				'more_text' => __('More &rarr;', 'themify'),
				'limit' => 4,
				'category' => 'all', // integer category ID
				'order' => 'DESC', // ASC
				'orderby' => 'date', // title, rand
				'style' => 'grid4', // grid4, grid3, grid2
				'sorting' => 'no', // yes
				'paged' => '0', // internal use for pagination, dev: previously was 1
				'use_original_dimensions' => 'no', // yes
				'filter' => 'yes', // entry filter
				'visible' => '3',
				'scroll' => '1',
				'speed' => '1000', // integer, slow, normal, fast
				'autoplay' => 'off',
			);
			if ( ! isset( $atts['image_w'] ) || '' == $atts['image_w'] ) {
				if ( ! isset( $atts['style'] ) ) {
					$atts['style'] = 'grid3';
				}
				
				switch ( $atts['style'] ) {
					case 'list-post':
						$args['image_w'] = 1160;
						$args['image_h'] = 665;
						break;
					case 'grid4':
					case '':
						$args['image_w'] = 260;
						$args['image_h'] = 150;
						break;
					case 'grid3':
						$args['image_w'] = 360;
						$args['image_h'] = 205;
						break;
					case 'grid2':
						$args['image_w'] = 561;
						$args['image_h'] = 321;
						break;
					case 'list-large-image':
						$args['image_w'] = 800;
						$args['image_h'] = 460;
						break;
					case 'list-thumb-image':
						$args['image_w'] = 260;
						$args['image_h'] = 150;
						break;
					case 'grid2-thumb':
						$args['image_w'] = 160;
						$args['image_h'] = 95;
						break;
					case 'slider':
						$args['image_w'] = 1280;
						$args['image_h'] = 500;
						break;
				}
			}
			return do_shortcode( $this->shortcode( shortcode_atts( $args, $atts ), self::POST_TYPE ) );
		}

		/**
		 * Main shortcode rendering
		 * @param array $atts
		 * @param $post_type
		 * @return string|void
		 */
		function shortcode($atts, $post_type){
			extract($atts);
			// Pagination
			global $paged;
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			// Parameters to get posts
			$args = array(
				'post_type' => $post_type,
				'posts_per_page' => $limit,
				'order' => $order,
				'orderby' => $orderby,
				'suppress_filters' => false,
				'paged' => $paged
			);
			// Category parameters
			$args['tax_query'] = themify_parse_category_args($category, self::TAX );

			// Defines layout type
			$multiple = true;

			// Single post type or many single post types
			if( '' != $id ){
				if(strpos($id, ',')){
					$ids = explode(',', str_replace(' ', '', $id));
					foreach ($ids as $string_id) {
						$int_ids[] = intval($string_id);
					}
					$args['post__in'] = $int_ids;
					$args['orderby'] = 'post__in';
				} else {
					$args['p'] = intval($id);
					$multiple = false;
				}
			}

			// Get posts according to parameters
			$portfolio_query = new WP_Query();
			$posts = $portfolio_query->query(apply_filters('themify_'.$post_type.'_shortcode_args', $args));

			// Grid Style
			if( '' == $style ){
				$style =  themify_get('setting-default_portfolio_index_post_layout','grid4',true);
			}

			if( is_singular('portfolio') ){
				if( '' == $image_w ){
					$image_w = themify_get('setting-default_portfolio_single_image_post_width',670,true);
				}
				if( '' == $image_h ){
					$image_h = themify_get('setting-default_portfolio_single_image_post_height',0,true);
				}
				if( '' == $post_date ){
					$post_date = themify_get('setting-default_portfolio_index_post_date','yes',true);
				}
				if( '' == $title ){
					$title = themify_get('setting-default_portfolio_single_title','yes',true);
				}
				if( '' == $unlink_title ){
					$unlink_title = themify_get('setting-default_portfolio_single_unlink_post_title','no',true);
				}
				if( '' == $post_meta ){
					$post_meta = themify_get('setting-default_portfolio_single_meta','yes',true);
				}
			} else {
				if( '' == $image_w ){
					$image_w = themify_get('setting-default_portfolio_index_image_post_width',221,true);
				}
				if( '' == $image_h ){
					$image_h = themify_get('setting-default_portfolio_index_image_post_height',221,true);
				}
				if( '' == $title ){
					$title = themify_get('setting-default_portfolio_index_title','yes',true);
				}
				if( '' == $unlink_title ){
					$unlink_title = themify_get('setting-default_portfolio_index_unlink_post_title','no',true);
				}
				// Reverse logic
				if( '' == $post_date ){
				    $post_date = 'no' === themify_get('setting-default_portfolio_index_post_date','yes',true)?'yes' : 'no';
				}
				if( '' == $post_meta ){
				    $post_meta = 'no' === themify_get('setting-default_portfolio_index_post_meta_category','yes',true)? 'yes' : 'no';
				}
			}

			// Collect markup to be returned
			$out = '';

			if( $posts ) {
				global $themify;
				$themify_save = clone $themify; // save a copy

				// override $themify object
				$themify->hide_title = 'yes' === $title? 'no': 'yes';
				$themify->unlink_title =  ( '' == $unlink_title || 'no' === $unlink_title )? 'no' : 'yes';
				$themify->hide_image = 'yes' === $image? 'no': 'yes';
				$themify->hide_meta = 'yes' === $post_meta? 'no': 'yes';
				$themify->hide_date = 'yes' === $post_date? 'no': 'yes';
				if(!$multiple) {
					if( '' == $image_w || get_post_meta($args['p'], 'image_width', true ) ){
						$themify->width = get_post_meta($args['p'], 'image_width', true );
					}
					if( '' == $image_h || get_post_meta($args['p'], 'image_height', true ) ){
						$themify->height = get_post_meta($args['p'], 'image_height', true );
					}
				} else {
					$themify->width = $image_w;
					$themify->height = $image_h;
				}
				$themify->use_original_dimensions = 'yes' === $use_original_dimensions? 'yes': 'no';
				$themify->display_content = $display;
				$themify->more_link = $more_link;
				$themify->more_text = $more_text;
				$themify->post_layout = explode(' ',$style);
				$themify->post_layout=trim($themify->post_layout[0]);

				// Output entry filter
				if ( 'yes' === $filter && $themify->post_layout!=='slider') {
					$themify->query_category = $category;
                    $themify->query_taxonomy = self::TAX;
					ob_start();
					get_template_part( 'includes/filter', self::POST_TYPE );
					$out = ob_get_contents();
					ob_end_clean();
				}
				$class=apply_filters( 'themify_loops_wrapper_class', array($post_type,$style),$post_type,$themify->post_layout,'shortcode' );
				$out = '<div data-lazy="1" class="loops-wrapper shortcode ' . esc_attr( implode(' ',$class) ) . ' tf_clear">';

					// Slider wrapper
					if ( 'slider' === $themify->post_layout ) {
						switch ( $speed ) {
							case 'fast':
								$speed = '500';
							break;
							case 'normal':
								$speed = '1000';
							break;
							case 'slow':
								$speed = '4000';
							break;
						}
						$out .= '<div class="slideshow tf_carousel tf_swiper-container tf_overflow tf_rel" data-lazy="1" data-autoplay="' . esc_attr( $autoplay ) . '" data-speed="' . esc_attr( $speed ) . '" data-scroll="' . esc_attr( $scroll ) . '" data-visible="' . esc_attr( $visible ) . '"><div class="tf_swiper-wrapper tf_lazy tf_rel tf_w tf_h">';
							$out .= themify_get_shortcode_template($posts, 'includes/loop-portfolio', 'index');
						$out .= '</div></div>';
					} else {
						$out .= themify_get_shortcode_template($posts, 'includes/loop-portfolio', 'index');
					}

					$out .= $this->section_link($more_link, $more_text, $post_type);

				$out .= '</div>';

				$themify = clone $themify_save; // revert to original $themify state
				
				wp_reset_postdata();
			}
			return $out;
		}

	}
}

/**************************************************************************************************
 * Initialize Type Class
 **************************************************************************************************/
new Themify_Portfolio();
