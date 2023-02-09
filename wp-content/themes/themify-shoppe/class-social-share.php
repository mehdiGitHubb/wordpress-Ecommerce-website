<?php
if ( ! class_exists( 'Themify_Social_Share' ) ) {
	/**
	 * Class Themify_Social_Share
	 *
	 * @since 1.0.0
	 */
	class Themify_Social_Share {

		/**
		 * List of all social networks.
		 *
		 * @var array
		 */
		public static $all_networks = null;

		/**
		 * List of social networks that will be actually rendered in front end.
		 *
		 * @var array
		 */
		public static $active_networks = null;
                
                private static $networks_url = array(
                                            'facebook'=>'https://www.facebook.com/sharer/sharer.php?u=',
                                            'twitter'=>'//twitter.com/intent/tweet?url=',
                                            'pinterest'=>'//pinterest.com/pin/create/button/?url=',
                                            'linkedin'=>'//www.linkedin.com/cws/share?url='
                );
                
		/**
		 * Populate if it's empty and return list of social networks.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return array
		 */
		public static function get_all_networks() {
			if ( is_null( self::$all_networks ) ) {
				self::$all_networks = array(
					'twitter'    => esc_html__( 'Twitter', 'themify' ),
					'facebook'   => esc_html__( 'Facebook', 'themify' ),
					'pinterest'  => esc_html__( 'Pinterest', 'themify' ),
					'linkedin'   => esc_html__( 'LinkedIn', 'themify' ),
				);
			}
			return apply_filters( 'themify_social_share_all_networks', self::$all_networks );
		}
                
                /**
		 * Get url of network
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return string
		 */
                
                public static function get_network_url($network){
                    if(!isset(self::$networks_url[$network])){
                        return FALSE;
                    }
                    $url = self::$networks_url[$network].urlencode(self::get_shared_url());
                    $text = the_title_attribute(array('echo'=>false));
                    $args = array();
                    switch($network){
                        case 'facebook':
                            if($text){
                                $args['t'] = $text;
                            }
                            $args['original_referer'] = get_the_permalink();
                          
                        break;
                        case 'twitter':
                            if($text){
                                $args['text'] = $text;
                            }
                        break;
                        case 'linkedin':
                            $url.='&token=&isFramed=true';
                        break;
                        case 'pinterest':
                            if($text){
                                $args['description'] = $text;
                            }
                            $img = self::get_shared_image();
                            if($img){
                                $args['media'] = $img;
                            }
                        break;
                    }
                        if(!empty($args)){
                        foreach($args as $k=>$v){
                            $url.='&'.$k.'='.urlencode(html_entity_decode($v, ENT_COMPAT, 'UTF-8'));
                        }
                    }
                    return esc_url($url);
                }
                
                /**
		 * Get params of network window
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return string
		 */
                public static function get_window_params($network){
                    if(!isset(self::$networks_url[$network])){
                        return FALSE;
                    }
                    switch($network){
                        case 'facebook':
                            return  'toolbar=0, status=0, width=900, height=500';
                        case 'twitter':
                            return  'toolbar=0, status=0, width=650, height=360';
                        case 'linkedin':
                           return  'toolbar=no,width=550,height=550';
                        case 'pinterest':
                           return  'toolbar=no,width=700,height=300';
                    }
                }

		/**
		 * Populate if it's empty and return list of social networks that will be used in front end.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return array
		 */
		public static function get_active_networks() {
			if ( self::$active_networks===null ) {
				$networks = self::get_all_networks();
				self::$active_networks = array();
				foreach ( $networks as $network_slug => $network_label ) {
					if ( themify_check( "setting-social_share_exclude_$network_slug",true ) ) {
						continue;
					}
					self::$active_networks[$network_slug] = $network_label;
				}
			}
			return apply_filters( 'themify_social_share_active_networks', self::$active_networks );
		}

		

		/**
		 * Check status of social share in the desired context.
		 *
		 * @since 1.0.0
		 *
		 * @param string $context
		 *
		 * @return bool
		 */
		public static function is_enabled( $context = 'all' ) {
			global $themify,$ThemifyBuilder;
			if ( !empty($ThemifyBuilder->in_the_loop) && isset( $themify->hide_meta ) ) {
				return 'yes' !== $themify->hide_meta;
			}
			if ( 'single' === $context ) {
				$post_type = 'post' === get_post_type() ? '' : get_post_type() . '_';
				$hide_social_share = themify_get( $post_type . 'hide_social_share' );
				if ( 'default' !== $hide_social_share ) {
					$social_share_status = 'yes' !== $hide_social_share;
				} else {
					$social_share_status = ! themify_check( 'setting-social_share_single_disabled',true );
				}
				return $social_share_status && is_singular();
			}
			elseif ( 'archive' === $context ) {
				$hide_social_share = '';
				if ( themify_is_query_page() ) {
					$hide_social_share = get_post_meta( $themify->page_id, 'hide_social_share', true );
				}
				if ( ! empty( $hide_social_share ) && 'default' !== $hide_social_share ) {
					$social_share_status = 'yes' !== $hide_social_share;
				} else {
					$social_share_status = ! themify_check( 'setting-social_share_archive_disabled',true );
				}
				return  $social_share_status && ( ! is_singular() || themify_is_query_page() );
			}
			return false;
		}
                
                

		/**
		 * Returns the URL to share with proper http or https scheme.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public static function get_shared_url() {
			if ( is_multisite() && ($meta_permalink = get_post_meta( get_the_ID(), 'permalink', true ) )) {
			    $share_link = $meta_permalink;
			}
			else{
			    $share_link = get_permalink();
			}

			return trim(themify_https_esc( $share_link ),'/');
		}

		/**
		 * Returns the image URL to share with proper http or https scheme.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public static function get_shared_image() {
			if ( has_post_thumbnail() ) {
				$get_social_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
				$share_image = isset( $get_social_image[0] )?$get_social_image[0]:'';
			} else{
				$share_image = themify_get( 'post_image','' );
			}

			return themify_https_esc( $share_image );
		}
                
                public static function shortcode($atts){
                    ob_start();
                    get_template_part('includes/social-share');
                    $content = ob_get_contents();
                    ob_end_clean();
                    return $content;
                }

	} // class end

} // endif class exists

if ( is_admin() ) {
    
	if ( ! function_exists( 'themify_social_share_module' ) ) {
		/**
		 * Markup for social share module in theme settings.
		 *
		 * @param array $data
		 *
		 * @return string
		 */
		function themify_social_share_module( $data = array() ) {
			/**
			 * Variable key in theme settings
			 * @var string
			 */
			$key = 'setting-social_share_archive_disabled';

			/**
			 * Module markup
			 * @var string
			 */
			$html = sprintf(
				'<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /> %3$s</label></p>',
				$key,
				checked( themify_get( $key,false,true ), 'on', false ),
				esc_html__( 'Disable social share in archive views (eg. category, tag pages, etc.)', 'themify' )
			);

			$key = 'setting-social_share_single_disabled';

			$html .= sprintf(
				'<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /> %3$s</label></p>',
				$key,
				checked( themify_get( $key,false,true ), 'on', false ),
				esc_html__( 'Disable social share in single entry view', 'themify' )
			);

			foreach ( Themify_Social_Share::get_all_networks() as $network_slug => $network_label ) {
				$key = "setting-social_share_exclude_$network_slug";
				$html .= sprintf(
					'<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" %2$s /> %3$s</label></p>',
					$key,
					checked( themify_get( $key,false,true ), 'on', false ),
					sprintf( esc_html__( 'Exclude %s', 'themify' ), $network_label )
				);
			}

			return $html;
		}
	}

}
add_shortcode( 'themify_share_buttons', array('Themify_Social_Share','shortcode'));