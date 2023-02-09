<?php
/**
* This file defines Custom Fonts
*
* Themify_Custom_Fonts class register post type for Custom Fonts and load them
*
*
* @package    Themify_Builder
* @subpackage Themify_Builder/classes
*/

if ( !class_exists( 'Themify_Custom_Fonts' ) ) :
/**
 * The Custom Fonts class.
 *
 * This class register post type for Custom Fonts and load them.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Custom_Fonts {

	/**
	 * slug Custom Fonts Object.
	 *
	 * @access private const
	 * @var string $slug .
	 */
	const SLUG = 'tb_cf';

	/**
	 * API Url
	 *
	 * @access static
	 * @var string $api_url .
	 */
	public static $api_url;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public static function init() {
		self::$api_url = site_url( '/?tb_load_cf=' );
		self::load_fonts_api();
		self::register_cpt();
		if ( is_admin() ) {
			add_filter( 'themify_metaboxes', array( __CLASS__, 'meta_box' ) );
			add_filter( 'themify_metabox/fields/tm-cf', array( __CLASS__, 'meta_box_fields' ), 10, 2 );
			add_filter( 'upload_mimes', array( __CLASS__, 'upload_mimes' ), 99, 1 );
			add_action( 'admin_head', array( __CLASS__, 'clean_admin_listing_page' ) );
			add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );
		}else{
			add_action( 'wp_enqueue_scripts', array(__CLASS__,'enqueue_custom_fonts'),30 );
		}
	}

	/**
	 * Register Custom Font Custom Post Type
	 *
	 * @access static
	 */
	private static function register_cpt() {
		if ( !class_exists( 'CPT' ) ) {
			include THEMIFY_DIR . '/CPT.php';
		}

		// create a template custom post type
		$cpt = new CPT( array(
			'post_type_name' => self::SLUG,
			'singular' => __( 'Custom Font', 'themify' ),
			'plural' => __( 'Custom Fonts', 'themify' )
		), array(
			'supports' => array( 'title' ),
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_ui' => true,
			'public' => false,
			'has_archive' => false
		) );

		// define the columns to appear on the admin edit screen
		$cpt->columns( array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Title', 'themify' ),
			'font_preview' => __( 'Preview', 'themify' )
		) );
	}

	public static function meta_box( $meta_boxes ) {
		$meta_boxes['tm-cf'] = array(
			'id' => 'tm-cf',
			'title' => __( 'Manage Font Files', 'themify' ),
			'context' => 'normal',
			'priority' => 'high',
			'screen' => array( self::SLUG )
		);

		return $meta_boxes;
	}

	/**
	 * Setup the custom fields
	 *
	 * @return array
	 */
	public static function meta_box_fields( $fields ) {

		$fields['tm-cf'] = array(
			'name' => __( 'Font Variations', 'themify' ),
			'id' => 'tm-cf',
			'options' => array(
				array(
					'name' => 'custom_fonts_notice',
					'title' => '',
					'description' => '',
					'type' => 'separator',
					'meta' => array(
					'html' => '<div class="themify-info-link">' . __('To add custom fonts: select the font weight/style and upload the font files accordingly. You don\'t need to upload all formats. The common support format is .ttf or .woff (just upload either .tff or .woff file is fine, depending on the font files you have). The custom fonts will appear on Builder and Customizer font select.', 'themify') . '</div>'
					),
				),
				array(
					'name' => 'variations',
					'type' => 'repeater',
					'show_first' => true,
					'add_new_label' => __( 'Add New Variation', 'themify' ),
					'fields' => array(
						array(
							'name' => 'weight',
							'title' => __( 'Weight', 'themify' ),
							'type' => 'dropdown',
							'meta' => array(
								array( 'value' => '100', 'name' => __( '100', 'themify' ) ),
								array( 'value' => '200', 'name' => __( '200', 'themify' ) ),
								array( 'value' => '300', 'name' => __( '300', 'themify' ) ),
								array( 'value' => '400', 'name' => __( '400', 'themify' ) ),
								array( 'value' => '500', 'name' => __( '500', 'themify' ) ),
								array( 'value' => '600', 'name' => __( '600', 'themify' ) ),
								array( 'value' => '700', 'name' => __( '700', 'themify' ) ),
								array( 'value' => '800', 'name' => __( '800', 'themify' ) ),
								array( 'value' => '900', 'name' => __( '900', 'themify' ) ),
							),
						),
						array(
							'name' => 'style',
							'title' => __( 'Style', 'themify' ),
							'type' => 'dropdown',
							'meta' => array(
								array( 'value' => 'normal', 'name' => __( 'Normal', 'themify' ) ),
								array( 'value' => 'italic', 'name' => __( 'Italic', 'themify' ) ),
								array( 'value' => 'oblique', 'name' => __( 'Oblique', 'themify' ) )
							)
						),
						array(
							'name' => 'woff',
							'ext' => 'woff',
							'title' => __( 'WOFF File', 'themify' ),
							'type' => 'font',
							'mime' => 'application/x-font-woff',
						),
						array(
							'name' => 'woff2',
							'ext' => 'woff2',
							'title' => __( 'WOFF2 File', 'themify' ),
							'type' => 'font',
							'mime' => 'application/x-font-woff2',
						),
						array(
							'name' => 'ttf',
							'ext' => 'ttf,otf',
							'title' => __( 'TTF / OTF File', 'themify' ),
							'type' => 'font',
							'mime' => 'application/x-font-ttf',
						),
						array(
							'name' => 'svg',
							'ext' => 'svg',
							'title' => __( 'SVG File', 'themify' ),
							'type' => 'font',
							'mime' => 'image/svg+xml',
						),
						array(
							'name' => 'eot',
							'ext' => 'eot',
							'title' => __( 'EOT File', 'themify' ),
							'type' => 'font',
							'mime' => 'application/x-font-eot',
						),
						array(
							'name' => 'separator',
							'type' => 'separator',
						),
					),
				)
			),
		);

		return $fields;
	}

	public static function upload_mimes( $mime_types ) {
		$ext = array(
			'woff' => 'application/x-font-woff',
			'woff2' => 'application/x-font-woff2',
			'ttf' => 'font/sfnt',
			'svg' => 'image/svg+xml',
			'eot' => 'application/x-font-eot',
			'otf' => 'font/otf',
		);
		foreach ( $ext as $type => $mime ) {
			if ( !isset( $mime_types[ $type ] ) ) {
				$mime_types[ $type ] = $mime;
			}
		}
		return $mime_types;
	}

	/**
	 * Clean up admin Font manager admin listing
	 */
	public static function clean_admin_listing_page() {
		global $typenow;

		if ( self::SLUG !== $typenow ) {
			return;
		}
		add_filter( 'months_dropdown_results', '__return_empty_array' );
		add_action( 'manage_' . self::SLUG . '_posts_custom_column', array( __CLASS__, 'render_columns' ), 10, 2 );
		add_filter( 'screen_options_show_screen', '__return_false' );
	}

	/**
	 * Render preview column in font manager admin listing
	 *
	 * @param $column
	 * @param $post_id
	 */
	public static function render_columns( $column, $post_id ) {
		if ( 'font_preview' === $column ) {
			$variations = get_post_meta( $post_id, 'variations', true );
			if ( empty( $variations ) ) {
				return;
			}
			$fonts = array(
				'woff' => 'woff',
				'woff2' => 'woff2',
				'ttf' => 'truetype',
				'svg' => 'svg',
				'eot' => 'embedded-opentype',
			);
			$font_family = get_the_title( $post_id );
			ob_start();
			foreach ( $variations as $var ):
				$src = '';
				foreach ( $fonts as $type => $format ) {
					$src .= !empty( $var[ $type ] ) ? 'url(\'' . $var[ $type ] . '\') format(\'' . $format . '\'),' : '';
				}
				?>
				@font-face{font-family:'<?php echo $font_family; ?>';font-weight:<?php echo $var['weight']; ?>;font-style:<?php echo $var['style']; ?>;src:<?php echo rtrim( $src, ',' ); ?>;    }
			<?php
			endforeach;
			$font_face = ob_get_clean();
			printf( '<style>%s</style><span style="font-family: \'%s\';">%s</span>', trim( $font_face ), $font_family, __( 'Themify makes your dreams come true.', 'themify' ) );
		}
	}

	/**
	 * Returns a list of Custom Fonts
	 * @return array
	 */
	public static function get_list( $env = 'builder' ) {
		$list = 'builder' !== $env ? array() : array(
			array( 'value' => '', 'name' => '' ),
			array(
				'value' => '',
				'name' => '--- ' . __( 'Custom Fonts', 'themify' ) . ' ---'
			)
		);
		$fonts = self::get_posts( array( 'limit' => -1 ) );
		foreach ( $fonts as $slug => $font ) {
			if ( !empty( $font['family'] ) && !empty( $font['variants'] ) ) {
				$list[] = array(
					'value' => $slug,
					'name' => $font['family'],
					'variant' => $font['variants']
				);
			}
		}
		return $list;
	}

	/**
	 * Returns a list of variants
	 * @return array
	 */
	private static function get_variants( $variants ) {
		$vars = array();
		if ( !empty( $variants ) && is_array( $variants ) ) {
			foreach ( $variants as $var ) {
				/* 400 is "normal" weight, always show first */
				if ( $var['weight'] === '400' ) {
					array_unshift( $vars, '400' );
				} else {
					$vars[] = $var['weight'];
				}
			}
			$vars = array_values( array_unique( $vars ) );
		}
		return $vars;
	}

	/**
	 * Get a list of Custom Fonts CPT
	 *
	 * @param array $args arguments of get posts
	 * @return array
	 */
	private static function get_posts( $args = array() ) {
		$limit = empty( $args['limit'] ) ? 10 : $args['limit'];
		$post_names = empty( $args['post_names'] ) ? array() : $args['post_names'];
		$cf_posts = array();
		$posts_args = array(
			'post_type' => self::SLUG,
			'posts_per_page' => $limit,
			'no_found_rows'=>true,
			'ignore_sticky_posts'=>true,
			'orderby'     => 'ID',
			'order'       => 'DESC',
			'post_name__in' => $post_names
		);
		$posts = get_posts( $posts_args );
		unset($posts_args);
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$post_id = $post->ID;
				$data = get_post_meta( $post_id, 'variations', true );
				$cf_posts[ $post->post_name ] = array(
					'family' => $post->post_title,
					'variants' => self::get_variants( $data ),
					'data' => $data
				);
			}
		}
		return $cf_posts;
	}

	/**
	 * Generate @font-face CSS for list of fonts in $_GET['tb_load_cf']
	 *
	 * This is used only in Builder live preview. On frontend self::load_fonts()
	 * adds the necessary code as inline CSS to the page.
	 *
	 * @return void
	 */
	private static function load_fonts_api() {
		if ( ! empty( $_GET['tb_load_cf'] ) ) {
			header( 'Content-Type: text/css' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: private', false );
			header( 'Content-Transfer-Encoding: binary' );
			$fonts = $_GET['tb_load_cf'];
			$fonts = preg_replace( '/\.css$/', '', $fonts );
			$fonts = explode( '|', $fonts );
			echo self::get_font_face_css( $fonts );
			exit;
		}
	}

	/**
	 * Returns @font-face CSS for all the custom fonts used in a given $post_id
	 *
	 * @param int $post_id
	 * @return string Generated CSS code to load the fonts
	 */
	public static function load_fonts( $post_id ) {
		$fonts = explode( '|', self::get_fonts( $post_id ) );
		return self::get_font_face_css( $fonts,true );
	}

	/**
	 * Generate @font-family CSS code from an array of fonts
	 *
	 * @param array $fonts
	 * @return string
	 */
	private static function get_font_face_css( $fonts, $builder=false ) {
		$font_css = '';
		$api_fonts = array();
        if(true===$builder){
            $customizer_fonts = apply_filters( 'themify_custom_fonts', array() );
            $cf=array();
            foreach($customizer_fonts as $font){
                $f = explode(':',$font);
                $v=isset($f[1])?explode(',',$f[1]):array('normal');
                $cf[$f[0]]=!isset($cf[$f[0]])?$v:array_unique(array_merge($cf[$f[0]],$v));
            }
            $customizer_fonts=$cf;
            $cf=null;
        }
		foreach ( $fonts as $font ) {
			if(!empty($font)){
			    $font = explode( ':', $font );
			    $font_family = $font[0];
			    $variations = empty( $font[1] ) ? array() : explode( ',', $font[1] );
			    // Skip loading duplicate fonts
			    if(true===$builder && isset($customizer_fonts[$font_family])){
				$variations=array_diff($variations,$customizer_fonts[$font_family]);
			    }
			$api_fonts[ $font_family ] = !isset($api_fonts[ $font_family ])?$variations:array_unique(array_merge($api_fonts[ $font_family ],$variations));
		    }
		}
		unset($fonts);
		if ( !empty( $api_fonts ) ) {
		    $cf_fonts = self::get_posts( array( 'post_names' => array_keys( $api_fonts ), 'limit' => -1 ) );
		    if ( !empty( $cf_fonts ) ) {
			foreach ( $api_fonts as $font_family => $variations ) {
			    if (isset( $cf_fonts[ $font_family ] ) && !empty( $cf_fonts[ $font_family ]['data']) ) {
				if(empty( $variations ) ){
					$variations = $cf_fonts[ $font_family ]['variants'];
				}	
				if(!empty($variations)){
				    foreach ( $variations as $var ) {
					$var = str_replace( [ 'normal', 'bold' ], [ '400', '700' ], $var );
					foreach ( $cf_fonts[ $font_family ]['data'] as $k => $v ) {
					    $v['weight'] = str_replace( [ 'normal', 'bold' ], [ '400', '700' ], $v['weight'] );
					    if ( $v['weight'] === $var ) {
						    $font_css .= self::get_font_face_from_data( $font_family, $cf_fonts[ $font_family ]['data'][ $k ] ) . PHP_EOL;
						    unset( $cf_fonts[ $font_family ]['data'][ $k ] );
					    }
					}
				    }
				}
			    }
			}
		    }
		}

		return $font_css;
	}

	/**
	 * Generate font-face CSS
	 *
	 * @param string $font_family
	 * @param array $data variations data
	 * @return string
	 */
	private static function get_font_face_from_data( $font_family, $data ) {
		$font_face = '';
		$src = array();
		$types = array('woff2', 'woff','eot', 'ttf', 'svg' );
		foreach ( $types as $type ) {
			if ( empty( $data[ $type ] ) ) {
				continue;
			}
			if ( 'svg' === $type ) {
				$data[ $type ] .= '#' . str_replace( ' ', '', $font_family );
			}

			$src[] = self::get_font_src_per_type( $type, $data[ $type ] );
		}
		if ( empty( $src ) ) {
			return $font_face;
		}
		$font_face = '@font-face{' . PHP_EOL;
		$font_face .= "\tfont-family:'" . $font_family . "';" . PHP_EOL;
		$font_face .= "\tfont-style:" . $data['style'] . ';' . PHP_EOL;
		$font_face .= "\tfont-weight:" . $data['weight'] . ';' . PHP_EOL;
		$font_face .= "\tfont-display:swap;". PHP_EOL;

		if ( !empty( $data['eot'] ) ) {
			$font_face .= "\tsrc:url('" . esc_attr( $data['eot'] ) . "');" . PHP_EOL;
		}

		$font_face .= "\tsrc:" . implode( ',' . PHP_EOL . "\t\t", $src ) . PHP_EOL . '}';

		return $font_face;
	}

	/**
	 * Generate font file src base on file type
	 *
	 * @param string $type font file type
	 * @param string $url font file url
	 * @return string
	 */
	private static function get_font_src_per_type( $type, $url ) {
		$src = 'url(\'' . esc_attr( $url ) . '\') ';
		switch ( $type ) {
			case 'woff':
			case 'woff2':
			case 'svg':
				$src .= 'format(\'' . $type . '\')';
				break;
			case 'ttf':
				$src .= 'format(\'truetype\')';
				break;
			case 'eot':
				$src = 'url(\'' . esc_attr( $url ) . '?#iefix\') format(\'embedded-opentype\')';
				break;
		}
		return $src;
	}

	/**
	 * Enqueues Custom Fonts (Builder)
	 */
	public static function get_fonts( $post_id = null ) {
		$entry_cf_fonts = get_option( 'themify_builder_cf_fonts' );
		if ( !empty( $entry_cf_fonts ) && is_array( $entry_cf_fonts ) ) {
			$entry_id = $post_id ? $post_id : Themify_Builder_Model::get_ID();
			if ( isset( $entry_cf_fonts[ $entry_id ] ) ) {
				return $entry_cf_fonts[ $entry_id ];
			}
		}
		return false;
	}

	/**
	 * Enqueue Custom fonts (if any) on the page
	 *
	 * @uses themify_custom_fonts filter
	 */
	public static function enqueue_custom_fonts() {
		$fonts = apply_filters( 'themify_custom_fonts', array() );
		if ( ! empty( $fonts ) ) {
			$css = self::get_font_face_css( $fonts );
			if ( ! empty( $css ) ) {
				echo '<style id="themify-custom-fonts">' . $css . '</style>';
			}
		}
	}

	public static function get_cf_fonts_url(array $fonts=array()){
		$res = array();
		static $isLoaded=array();
		foreach ( $fonts as $key => $font ) {
			if ( ! empty( $font ) && preg_match( '/^\w/', $font ) ) {
				/* fix the delimiter with multiple weight variants, it should use `,` and not `:`
					reset the delimiter between font name and first variant */
				$font=preg_replace( '/,/', ':', str_replace( ':', ',', $font ), 1 );
				if(!in_array($font,$isLoaded,true)){
					$isLoaded[]=$font;
					$res[ $key ] = $font;
				}
			}
		}
		if ( ! empty( $res ) ) {
			return self::$api_url . implode( '|', $res );
		}
		return false;
	}

	public static function admin_footer() {
		$screen = get_current_screen();
		if ( $screen->base !== 'edit' || $screen->post_type !== self::SLUG ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'themify-scripts' );
		wp_enqueue_style( 'themify-ui' );
		wp_enqueue_style( 'themify-icons' );
		?>

		<div class="notice notice-success" id="tf_font_import_notice">
			<p><?php themify_is_themify_theme() ? printf( __( 'FYI: You can download all in-use Google Fonts to your own server. Go to <a href="%s">Themify Settings > General</a> > Google Fonts to enable this option.', 'themify' ), admin_url( 'admin.php?page=themify#setting-general' ) ) : printf( __( 'FYI: You can download all in-use Google Fonts to your own server. Go to <a href="%s">Themify Builder > Settings</a> Download Google Fonts section to enable this option.', 'themify' ), admin_url( 'admin.php?page=themify-builder' ) ); ?></p>
		</div>

		<style id="tf_font_preview_style"></style>

		<div id="tf_font_size_slider_wrap">
			<div id="tf_font_size_slider">
			</div>
		</div>
		<?php
	}
}
Themify_Custom_Fonts::init();
endif;