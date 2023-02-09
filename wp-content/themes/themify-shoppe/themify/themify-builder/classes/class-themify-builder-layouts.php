<?php
/**
 * This file defines Builder Layouts and Layout Parts
 *
 * Themify_Builder_Layouts class register post type for Layouts and Layout Parts
 * Custom metabox, shortcode, and load layout / layout part.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

if( !class_exists( 'Themify_Builder_Layouts' ) ) {
	/**
	 * The Builder Layouts class.
	 *
	 * This class register post type for Layouts and Layout Parts
	 * Custom metabox, shortcode, and load layout / layout part.
	 *
	 *
	 * @package    Themify_Builder
	 * @subpackage Themify_Builder/classes
	 * @author     Themify
	 */
	class Themify_Builder_Layouts{

		/**
		 * Post Type Layout Object.
		 *
		 * @access public
		 * @var object $layout .
		 */
		const LAYOUT_SLUG='tbuilder_layout';

		/**
		 * Post Type Layout Part Object.
		 *
		 * @access public
		 * @var string $layout_part_slug .
		 */
		const LAYOUT_PART_SLUG='tbuilder_layout_part';

		/**
		 * Store registered layout / part post types.
		 *
		 * @access public
		 * @var array $post_types .
		 */
		public $post_types = array();

		/**
		 * Holds a list of layout provider instances
		 */
		public $provider_instances = array();

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			$this->register_layout();
			if ( is_admin() ) {
				// Builder write panel
				add_filter( 'themify_post_types', array( $this, 'extend_post_types' ) );
				add_filter( 'themify_builder_post_types_support', array( $this, 'add_builder_support' ) );
				add_action( 'add_meta_boxes_tbuilder_layout_part', array( $this, 'custom_meta_boxes' ) );
				add_action( 'add_meta_boxes_tbuilder_layout', array( $this, 'custom_meta_boxes' ) );

				add_action( 'wp_ajax_tb_set_layout', array( __CLASS__, 'set_layout_ajaxify' ), 10 );
				add_action( 'wp_ajax_set_layout_action', array( __CLASS__, 'set_layout_action' ), 10 );
				add_action( 'wp_ajax_tb_save_custom_layout', array( __CLASS__, 'save_custom_layout_ajaxify' ), 10 );
                                add_action( 'wp_ajax_tb_get_save_custom_layout', array( __CLASS__, 'get_custom_layout_ajaxify' ), 10 );

				// Quick Edit Links
				add_filter( 'post_row_actions', array( $this, 'row_actions' ) );
				add_filter( 'page_row_actions', array( $this, 'row_actions' ) );
				add_filter( 'bulk_actions-edit-tbuilder_layout_part', array( $this, 'row_bulk_actions' ) );
				add_filter( 'bulk_actions-edit-tbuilder_layout', array( $this, 'row_bulk_actions' ) );
				add_filter( 'handle_bulk_actions-edit-tbuilder_layout_part', array( $this, 'export_row_bulk' ), 10, 3 );
				add_filter( 'handle_bulk_actions-edit-tbuilder_layout', array( $this, 'export_row_bulk' ), 10, 3 );
				add_action( 'admin_init', array( $this, 'duplicate_action' ) );
				add_action( 'admin_init', array( $this, 'export_row' ) );


				// Ajax hook for Layout and Layout Parts import file.
				add_action( 'wp_ajax_tbuilder_plupload_layout', array( $this, 'row_bulk_import' ) );
				add_action( 'admin_head-edit.php', array( $this, 'row_bulk_import_button' ) );
			}
			add_shortcode( 'themify_layout_part', array( $this, 'layout_part_shortcode' ) );
			add_filter( 'template_include', array( $this, 'template_singular_layout' ) );
		}


		/**
		 * Registers providers for layouts in Builder
		 *
		 * @since 2.0.0
		 */
		private function register_providers() {
			$providers = apply_filters( 'themify_builder_layout_providers', array(
				'Themify_Builder_Layouts_Provider_Custom'
			) );
			foreach ( $providers as $provider ) {
				if ( class_exists( $provider ) ) {
					$instance = new $provider();
					$this->provider_instances[ $instance->get_id() ] = $instance;
				}
			}
		}

		/**
		 * Get a single layout provider instance
		 *
		 * @since 2.0.0
		 */
		public function get_provider( $id ) {
			return isset( $this->provider_instances[ $id ] ) ? $this->provider_instances[ $id ] : false;
		}

		private static function register_layout_post_type() {
			return new CPT( array(
				'post_type_name' => self::LAYOUT_SLUG,
				'singular' => __( 'Layout', 'themify' ),
				'plural' => __( 'Layouts', 'themify' )
			), array(
				'supports' => array( 'title', 'thumbnail' ),
				'exclude_from_search' => true,
				'show_in_nav_menus' => false,
				'show_in_menu' => false,
				'public' => true
			) );
		}

		private static function register_layout_part_post_type() {
			return new CPT( array(
				'post_type_name' =>self::LAYOUT_PART_SLUG,
				'singular' => __( 'Layout Part', 'themify' ),
				'plural' => __( 'Layout Parts', 'themify' ),
				'slug' => 'tbuilder-layout-part'
			), array(
				'supports' => array( 'title', 'thumbnail' ),
				'exclude_from_search' => true,
				'show_in_nav_menus' => false,
				'show_in_admin_bar' => true,
				'show_in_menu' => false,
				'public' => true
			) );
		}

		/**
		 * Register Layout and Layout Part Custom Post Type
		 *
		 * @access public
		 */
		private function register_layout() {
			if ( !class_exists( 'CPT' ) ) {
				include THEMIFY_DIR . '/CPT.php';
			}

			// create a template custom post type
			$layout = self::register_layout_post_type();

			// define the columns to appear on the admin edit screen
			$layout->columns( array(
				'cb' => '<input type="checkbox" />',
				'title' => __( 'Title', 'themify' ),
				'thumbnail' => __( 'Thumbnail', 'themify' ),
				'author' => __( 'Author', 'themify' ),
				'date' => __( 'Date', 'themify' )
			) );

			// populate the thumbnail column
			$layout->populate_column( 'thumbnail', array( $this, 'populate_column_layout_thumbnail' ) );

			// use "pages" icon for post type
			$layout->menu_icon( 'dashicons-admin-page' );

			// create a template custom post type
			$layout_part = self::register_layout_part_post_type();

			// define the columns to appear on the admin edit screen
			$layout_part->columns( array(
				'cb' => '<input type="checkbox" />',
				'title' => __( 'Title', 'themify' ),
				'shortcode' => __( 'Shortcode', 'themify' ),
				'author' => __( 'Author', 'themify' ),
				'date' => __( 'Date', 'themify' )
			) );

			// populate the thumbnail column
			$layout_part->populate_column( 'shortcode', array( $this, 'populate_column_layout_part_shortcode' ) );

			// use "pages" icon for post type
			$layout_part->menu_icon( 'dashicons-screenoptions' );

			$this->set_post_type_var( $layout->post_type_name );
			$this->set_post_type_var( $layout_part->post_type_name );

			add_post_type_support( $layout->post_type_name, 'revisions' );
			add_post_type_support( $layout_part->post_type_name, 'revisions' );
			if(is_admin()){
			    $this->register_providers();
			}
		}

		/**
		 * Set the post type variable.
		 *
		 * @access public
		 * @param string $name
		 */
		public function set_post_type_var( $name ) {
			$this->post_types[] = $name;
		}

		/**
		 * Custom column thumbnail.
		 *
		 * @access public
		 * @param array $column
		 * @param object $post
		 */
		public function populate_column_layout_thumbnail( $column, $post ) {
			echo get_the_post_thumbnail( $post->ID, 'thumbnail' );
		}

		/**
		 * Custom column for shortcode.
		 *
		 * @access public
		 * @param array $column
		 * @param object $post
		 */
		public function populate_column_layout_part_shortcode( $column, $post ) {
			echo
				'<input readonly size="30" type="text" onclick="this.select();" value="' . esc_attr( sprintf( '[themify_layout_part id="%d"]', $post->ID ) ) . '">',
				'<br/>',
				'<input readonly size="30" type="text" onclick="this.select();" value="' . esc_attr( sprintf( '[themify_layout_part slug="%s"]', $post->post_name ) ) . '">';
		}

		/**
		 * Includes this custom post to array of cpts managed by Themify
		 *
		 * @access public
		 * @param Array $types
		 * @return Array
		 */
		public function extend_post_types( $types ) {
			$cpts = array( self::LAYOUT_SLUG, self::LAYOUT_PART_SLUG );
			return array_merge( $types, $cpts );
		}

		/**
		 * Add meta boxes to layout and/or layout part screens.
		 *
		 * @access public
		 * @param object $post
		 */
		public function custom_meta_boxes( $post ) {
			add_meta_box( 'layout-part-info', __( 'Using this Layout Part', 'themify' ), array( $this, 'layout_part_info' ), self::LAYOUT_PART_SLUG, 'side', 'default' );
		}

		/**
		 * Displays information about this layout part.
		 *
		 * @access public
		 */
		public function layout_part_info() {
			$layout_part = get_post();
			echo '<div>', __( 'To display this Layout Part, insert this shortcode:', 'themify' ), '<br/>
		<input type="text" readonly="readonly" class="widefat" onclick="this.select()" value="' . esc_attr( '[themify_layout_part id="' . $layout_part->ID . '"]' ) . '" />';
			if ( !empty( $layout_part->post_name ) ) {
				echo '<input type="text" readonly="readonly" class="widefat" onclick="this.select()" value="' . esc_attr( '[themify_layout_part slug="' . $layout_part->post_name . '"]' ) . '" />';
			}
			echo '</div>';
		}


		/**
		 * Custom layout for Template / Template Part Builder Editor.
		 *
		 * @access public
		 */
		public function template_singular_layout( $original_template ) {
			if ( is_singular( array( self::LAYOUT_SLUG, self::LAYOUT_PART_SLUG ) ) ) {
				$templatefilename = 'template-builder-editor.php';

				$return_template = locate_template(
					array(
						trailingslashit( 'themify-builder/templates' ) . $templatefilename
					)
				);

				// Get default template
				if ( !$return_template )
					$return_template = THEMIFY_BUILDER_TEMPLATES_DIR . '/' . $templatefilename;

				return $return_template;
			} else {
				return $original_template;
			}
		}

		/**
		 * Set/Append template to current active builder.
		 *
		 * @access public
		 */
		public static function set_layout_ajaxify() {
			check_ajax_referer( 'tf_nonce', 'nonce' );
			$response = array();
			if ( isset( $this->provider_instances[ $_POST['layout_group'] ] ) ) {
				$builder_data = $this->provider_instances[ $_POST['layout_group'] ]->get_builder_data( $_POST['layout_slug'] );
			}
			if ( !is_wp_error( $builder_data ) && !empty( $builder_data ) ) {
				$response['data'] = $builder_data;
				// Return used gs if used
				if ( $layoutPost = get_page_by_path( $_POST['layout_slug'], OBJECT, self::LAYOUT_SLUG) ) {
					$usedGS = Themify_Global_Styles::used_global_styles( $layoutPost->ID );
					if ( !empty( $usedGS ) ) {
						$response['gs'] = $usedGS;
					}
				}
			} else {
				$response['msg'] = $builder_data->get_error_message();
			}
                        header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			echo json_encode( $response );
			self::set_layout_action();
		}

		public static function set_layout_action() {
			check_ajax_referer( 'tf_nonce', 'nonce' );
			if ( !empty( $_POST['bid'] ) ) {
				$mode = !empty( $_POST['mode'] ) ? 'themify_builder_layout_appended' : 'themify_builder_layout_loaded';
				do_action( $mode, array( 'template_slug' => '', 'current_builder_id' => (int)$_POST['bid'], 'layout_group' => '', 'builder_data' => '' ) );
			}
			die;
		}


		/**
		 * Layout Part Shortcode
		 *
		 * @access public
		 * @param array $atts
		 * @return string
		 */
		public function layout_part_shortcode( $atts ) {
			$args = array(
				'post_type' => self::LAYOUT_PART_SLUG,
				'post_status' => 'publish',
				'numberposts' => 1,
				'no_found_rows' => true,
				'cache_results' => false,
				'orderby' => 'ID',
				'order' => 'ASC'
			);
			if ( !empty( $atts['slug'] ) ) {
				$args['name'] = $atts['slug'];
			}
			if ( !empty( $atts['id'] ) ) {
				$args['p'] = $atts['id'];
			}
			$template = get_posts( $args );
			if ( ! $template ) {
				return '';
			}
			unset($args);
			global $ThemifyBuilder;
			$id = $template[0]->ID;
			$id = themify_maybe_translate_object_id( $id );
			if ($id == Themify_Builder::$builder_active_id && Themify_Builder_Model::is_front_builder_activate()) {
				static $isDone=false;//return only for first element
				if($isDone===false){
					$isDone=true;
					return $ThemifyBuilder->get_builder_output($id);
				}
			}
			// infinite-loop prevention
			static $stack = array();
			if ( isset($stack[$id])) {
				$message = sprintf( __( 'Layout Part %s is in an infinite loop.', 'themify' ), $id );
				return "<!-- {$message} -->";
			} 
			else {
				$stack[ $id ] = true;
			}
			
			$output = '';
			$builder_data = ThemifyBuilder_Data_Manager::get_data( $id );
			// Check For page break module
			if ( !Themify_Builder::$frontedit_active ) {
				$module_list = $ThemifyBuilder->get_flat_modules_list( $id );
				$page_breaks = 0;
				foreach ( $module_list as $module ) {
					if ( isset( $module['mod_name'] ) && 'page-break' === $module['mod_name'] ) {
						++$page_breaks;
					}
				}
				unset($module_list);
				$template_args = array();
				if ( $page_breaks > 0 ) {
					$pb_result = $ThemifyBuilder->load_current_inner_page_content( $builder_data, $page_breaks );
					$builder_data = $pb_result['builder_data'];
					$template_args['pb_pagination'] = $pb_result['pagination'];
					$pb_result = null;

				}
			}
			if ( !empty( $builder_data ) ) {
				$template_args['builder_output'] = $builder_data;
				$template_args['builder_id'] = $id;
				$template_args['l_p'] = true;
                if(Themify_Builder::$frontedit_active === false){
                    $isActive=isset($_POST['action']) && $_POST['action']==='tb_render_element_shortcode';
                    Themify_Builder::$frontedit_active=$isActive;
                }
				$output = Themify_Builder_Component_Base::retrieve_template( 'builder-layout-part-output.php', $template_args,THEMIFY_BUILDER_TEMPLATES_DIR, '', false );
                if(isset($isActive)){
                    Themify_Builder::$frontedit_active=false;
                }
				if ( !themify_is_ajax() ) {
					$ThemifyBuilder->get_builder_stylesheet( $output );
				}
				unset($template_args);
			}

			unset( $stack[ $id ] );

			return $output;
		}

		/**
		 * Save as Layout
		 *
		 * @access public
		 */
		public static function save_custom_layout_ajaxify() {
			check_ajax_referer( 'tf_nonce', 'nonce' );
			$response = array(
				'status' => 'failed',
				'msg' => __( 'Something went wrong', 'themify' )
			);
			if ( !empty( $_POST['postid'] )) {
				$template = get_post( (int)$_POST['postid'] );
				$title = !empty( $_POST['layout_title_field'] ) ? sanitize_text_field( $_POST['layout_title_field'] ) : $template->post_title . ' Layout';
				$builder_data = ThemifyBuilder_Data_Manager::get_data( $template->ID );
				if ( !empty( $builder_data ) ) {
					$new_id = wp_insert_post( array(
						'post_status' => 'publish',
						'post_type' => self::LAYOUT_SLUG,
						'post_author' => $template->post_author,
						'post_title' => $title
					) );

					ThemifyBuilder_Data_Manager::save_data( $builder_data, $new_id );

					// Set image as Featured Image
					if ( !empty( $_POST['layout_img_field_id'] ) ) {
						set_post_thumbnail( $new_id, (int)$_POST['layout_img_field_id'] );
					}
					$response['status'] = 'success';
					$response['msg'] = '';
				}
			}
			wp_send_json( $response );
                        wp_die();
		}
                
                
                public static function get_custom_layout_ajaxify(){
                    check_ajax_referer( 'tf_nonce', 'nonce' );
                    $slug=!empty($_POST['slug'])?sanitize_text_field($_POST['slug']):'';    
                    if($slug!==''){
                        $args = array(
				'name' => $slug,
				'post_type' => self::LAYOUT_SLUG,
				'post_status' => 'publish',
				'no_found_rows' => true,
				'cache_results' => false,
				'numberposts' => 1
			);
			$template = get_posts( $args );
			if ( $template ) {
                            $layouts= ThemifyBuilder_Data_Manager::get_data( $template[0]->ID );
			} 
                        else {
                            wp_send_json_error(__( 'Requested layout not found.', 'themify' ) );
			}
                    }
                    else{
                        $layouts=self::get_saved_layouts($slug);   
                    }
                    wp_send_json( $layouts );
                }
		/**
		 * Get a list of "custom" layouts, each post from the "tbuilder_layout" post type
		 * is a Custom layout, this returns a list of them all
		 *
		 * @return array
		 */
		public static function get_saved_layouts($limit=-1) {
			global $post;
			$layouts = array();
			$posts = new WP_Query( array(
				'post_type' => self::LAYOUT_SLUG,
                                'post_status' => 'publish',
				'posts_per_page' => $limit,
				'orderby' => 'title',
				'order' => 'ASC',
				'ignore_sticky_posts'=>true,
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'cache_results' => false
			) );
			while ( $posts->have_posts() ) {
				$posts->the_post();
				$url = get_the_post_thumbnail_url( $post, 'thumbnail' );
				$layouts[] = array(
					'title' => get_the_title(),
					'slug' => $post->post_name,
					'thumbnail' => !empty( $url )?$url:THEMIFY_BUILDER_URI . '/img/image-placeholder.png'
				);
			}
			wp_reset_postdata();
			return $layouts;
		}

		/**
		 * Add custom link actions in post / page rows
		 *
		 * @access public
		 * @param array $actions
		 * @return array
		 */
		public function row_actions( $actions ) {
			global $post;
			if(Themify_Access_Role::check_access_frontend($post->ID)){
				$post_type = get_post_type();
				$builder_link = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( get_permalink( $post->ID ) . '#builder_active' ), __( 'Themify Builder', 'themify' ) );
				if (self::LAYOUT_SLUG=== $post_type || self::LAYOUT_PART_SLUG === $post_type ) {
					$actions['themify-builder-duplicate'] = sprintf( '<a href="%s">%s</a>', wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=duplicate_tbuilder' ), 'duplicate_themify_builder' ), __( 'Duplicate', 'themify' ) );
					$actions['tbuilder-export'] = sprintf( '<a href="%s">%s</a>', wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=tbuilder_export' ), 'tbuilder_layout_export' ), __( 'Export', 'themify' ) );
					$actions['themify-builder'] = $builder_link;
				} else {
					// print builder links on another post types
					$registered_post_types = themify_post_types();
					if ( in_array( $post_type, $registered_post_types, true ) )
						$actions['themify-builder'] = $builder_link;
				}
			}
			return $actions;
					
		}

		/**
		 * Add custom link actions in Layout / Layout Part rows bulk action
		 *
		 * @access public
		 * @param array $actions
		 * @return array
		 */
		public function row_bulk_actions( $actions ) {

			$actions['tbuilder-bulk-export'] = __( 'Export', 'themify' );

			return $actions;
		}

		/**
		 * Export Layouts and Layout Parts.
		 *
		 * @access public
		 */
		public function export_row() {
			if ( isset( $_GET['action'] ) && 'tbuilder_export' === $_GET['action'] && wp_verify_nonce( $_GET['_wpnonce'], 'tbuilder_layout_export' ) ) {
				$postid = array( (int)$_GET['post'] );
				if ( !$this->export_row_bulk( '', 'tbuilder-bulk-export', $postid ) )
					wp_redirect( admin_url( 'edit.php?post_type=' . get_post_type( $postid[0] ) ) );
				exit;
			}
		}

		/**
		 * Export Layouts and Layout Parts.
		 *
		 * @access public
		 */
		public function export_row_bulk( $redirect_to, $action, $pIds ) {
			if ( $action !== 'tbuilder-bulk-export' || empty( $pIds ) ) {
				return $redirect_to;
			}

			$data = array( 'import' => '', 'content' => array() );
			$type = get_post_type( $pIds[0] );
			$data['import'] = $type ===self::LAYOUT_PART_SLUG ? 'Layout Parts' : 'Layouts';
			$usedGS = array();
			foreach ( $pIds as $pId ) {

				$data['content'][] = array(
					'title' => get_the_title( $pId ),
					'settings' => get_post_meta( $pId, '_themify_builder_settings_json', true )
				);
				// Check for attached GS
				$usedGS = $usedGS + Themify_Global_Styles::used_global_styles( $pId );
			}

			if ( class_exists( 'ZipArchive' ) ) {
				$datafile = 'export_file.txt';
                Themify_Filesystem::put_contents( $datafile, serialize( $data ) );
				$files_to_zip = array( $datafile );
				// Export used global styles
				if ( !empty( $usedGS ) ) {
					foreach ( $usedGS as $gsID => $gsPost ) {
						unset( $usedGS[ $gsID ]['id'] );
						unset( $usedGS[ $gsID ]['url'] );
						$styling = Themify_Builder_Import_Export::prepare_builder_data( $gsPost['data'] );
						$styling = $styling[0];
						if ( $gsPost['type'] === 'row' || $gsPost['type'] === 'subrow' ) {
							$styling = $styling['styling'];
						} elseif ( $gsPost['type'] === 'column' ) {
							$styling = $styling['cols'][0]['styling'];
						} else {
							$styling = $styling['cols'][0]['modules'][0]['mod_settings'];
						}
						$usedGS[ $gsID ]['data'] = $styling;
					}
					$gs_data = json_encode( $usedGS );
					$gs_datafile = 'builder_gs_data_export.txt';
                    Themify_Filesystem::put_contents( $gs_datafile, $gs_data );
					$files_to_zip[] = $gs_datafile;
				}
				$file = 'themify_' . $data['import'] . '_export_' . date( 'Y_m_d' ) . '.zip';
				$result = themify_create_zip( $files_to_zip, $file, true );
			}
			if ( isset( $result ) && $result ) {
				if ( ( isset( $file ) ) && ( Themify_Filesystem::exists( $file ) ) ) {
					ob_start();
					header( 'Pragma: public' );
					header( 'Expires: 0' );
					header( 'Content-type: application/force-download' );
					header( 'Content-Disposition: attachment; filename="' . $file . '"' );
					header( 'Content-Transfer-Encoding: Binary' );
					header( 'Content-length: ' . filesize( $file ) );
					header( 'Connection: close' );
					ob_clean();
					flush();
					echo Themify_Filesystem::get_contents( $file );
                    Themify_Filesystem::delete( $datafile,'f' );
                    Themify_Filesystem::delete( $file,'f' );
					exit();
				} else {
					return false;
				}
			} else {
				if ( ini_get( 'zlib.output_compression' ) ) {
					ini_set( 'zlib.output_compression', 'Off' );
				}
				ob_start();
				header( 'Content-Type: application/force-download' );
				header( 'Pragma: public' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Cache-Control: private', false );
				header( 'Content-Disposition: attachment; filename="themify_' . $data['import'] . '_export_' . date( "Y_m_d" ) . '.txt"' );
				header( 'Content-Transfer-Encoding: binary' );
				ob_clean();
				flush();
				echo serialize( $data );
				exit();
			}

			return false;
		}

		/**
		 * Import Layout and Layout Parts.
		 *
		 * @access public
		 */
		public function row_bulk_import() {
			$imgid = $_POST['imgid'];

			!empty( $_POST['_ajax_nonce'] ) && check_ajax_referer( $imgid . 'themify-plupload' );

			/** Handle file upload storing file|url|type. @var Array */
			$file = wp_handle_upload( $_FILES[ $imgid . 'async-upload' ], array( 'test_form' => true, 'action' => 'tbuilder_plupload_layout' ) );

			// if $file returns error, return it and exit the function
			if ( !empty( $file['error'] ) ) {
				echo json_encode( $file );
				exit;
			}

			//let's see if it's an image, a zip file or something else
			$ext = explode( '/', $file['type'] );
			// Import routines
			if ( 'zip' === $ext[1] || 'rar' === $ext[1] || 'plain' === $ext[1] ) {

				$url = wp_nonce_url( 'edit.php' );

				if ( false === ( $creds = request_filesystem_credentials( $url ) ) ) {
					return true;
				}
				if ( !WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( $url, '', true );
					return true;
				}

				global $wp_filesystem;
				$base_path = themify_upload_dir();
				$base_path = trailingslashit( $base_path['path'] );

				if ( 'zip' === $ext[1] || 'rar' === $ext[1] ) {
					unzip_file( $file['file'], $base_path );
					if ( $wp_filesystem->exists( $base_path . 'export_file.txt' ) ) {
						$data = $wp_filesystem->get_contents( $base_path . 'export_file.txt' );
						$msg = $this->set_data( unserialize( $data ) );
						// Check for importing attached GS data
						$gs_path = $base_path . 'builder_gs_data_export.txt';
						if ( $wp_filesystem->exists( $gs_path ) ) {
							$gs_data = $wp_filesystem->get_contents( $gs_path );
							$gs_data = is_serialized( $gs_data ) ? maybe_unserialize( $gs_data ) : json_decode( $gs_data );
							Themify_Global_Styles::builder_import( $gs_data );
							$wp_filesystem->delete( $gs_path );
						}
						if ( $msg )
							$file['error'] = $msg;
						$wp_filesystem->delete( $base_path . 'export_file.txt' );
						$wp_filesystem->delete( $file['file'] );
					} else {
						$file['error'] = __( 'Data could not be loaded', 'themify' );
					}
				} else {
					if ( $wp_filesystem->exists( $file['file'] ) ) {
						$data = $wp_filesystem->get_contents( $file['file'] );
						$msg = $this->set_data( unserialize( $data ) );
						if ( $msg )
							$file['error'] = $msg;
						$wp_filesystem->delete( $file['file'] );
					} else {
						$file['error'] = __( 'Data could not be loaded', 'themify' );
					}
				}

			}
			$file['type'] = $ext[1];
			// send the uploaded file url in response
			echo json_encode( $file );
			exit;
		}

		public function row_bulk_import_button() {
			$post_type = get_current_screen()->post_type;

			if ( self::LAYOUT_SLUG !== $post_type && self::LAYOUT_PART_SLUG!== $post_type )
				return;

			$message = self::LAYOUT_SLUG!== $post_type ? 'Layouts' : 'Layout Parts';
			// Enqueue media scripts
			wp_enqueue_media();

			// Plupload
			wp_enqueue_script( 'plupload-all' );
			wp_enqueue_script( 'themify-plupload' );

			$button = themify_get_uploader( 'tbuilder-layout-import', array(
					'label' => __( 'Import', 'themify' ),
					'preset' => false,
					'preview' => false,
					'tomedia' => false,
					'topost' => '',
					'fields' => '',
					'featured' => '',
					'message' => '',
					'fallback' => '',
					'dragfiles' => false,
					'confirm' => __( 'Import will add all the ' . $message . ' containing in the file. Press OK to continue, Cancel to stop.', 'themify' ),
					'medialib' => false,
					'formats' => 'zip,txt',
					'type' => '',
					'action' => 'tbuilder_plupload_layout',
				)
			);
			?>
            <style>
                .tbuilder-layout-import{
                    display:inline-block;
                    top:0;
                    margin:0;
                    vertical-align:bottom;
                    border:0;
                    margin-left:5px
                }
                .tbuilder-layout-import .plupload-button{
                    padding:4px 10px;
                    position:relative;
                    top:-4px;
                    text-decoration:none;
                    border:0;
                    border:1px solid #ccc;
                    border-radius:2px;
                    background:#f7f7f7;
                    text-shadow:none;
                    font-weight:600;
                    font-size:inherit;
                    line-height:normal;
                    color:#0073aa;
                    cursor:pointer;
                    outline:0;
                    box-shadow:none;
                    height:auto
                }
                .tbuilder-layout-import .plupload-button:hover{
                    border-color:#008EC2;
                    background:#00a0d2;
                    color:#fff
                }
            </style>
            <script>
				window.addEventListener('load', function(){
					jQuery( '.page-title-action' ).after( '<div class="tbuilder-layout-import" style="display:inline-block"><?php echo preg_replace( '~[\r\n\t]+~', '', addslashes( $button ) ); ?></div>' );
				}, {once:true, passive:true});
            </script>
			<?php
		}

		private function set_data( $data ) {
			$error = false;

			if ( !isset( $data['import'] ) || !isset( $data['content'] ) || !is_array( $data['content'] ) ) {
				$error = __( 'Incorrect Import File', 'themify' );
			} else {

				if ( $data['import'] === 'Layouts' )
					$type = self::LAYOUT_SLUG;
                elseif ( $data['import'] === 'Layout Parts' ) {
					$type = self::LAYOUT_PART_SLUG;
				} else {
					$error = __( 'Failed to import. Unknown data.', 'themify' );
				}

				if ( !$error ) {

					foreach ( $data['content'] as $psot ) {
						$new_id = wp_insert_post( array(
							'post_status' => 'publish',
							'post_type' => $type,
							'post_author' => get_current_user_id(),
							'post_title' => $psot['title'],
							'post_content' => ''
						) );
						if ( !empty( $psot['settings'] ) ) {
							ThemifyBuilder_Data_Manager::save_data( json_decode( $psot['settings'], true ), $new_id );
						}
					}
				}
			}

			return $error;
		}

		/**
		 * Duplicate Post in Admin Edit page.
		 *
		 * @access public
		 */
		public function duplicate_action() {
			if ( isset( $_GET['action'] ) && 'duplicate_tbuilder' === $_GET['action'] && wp_verify_nonce( $_GET['_wpnonce'], 'duplicate_themify_builder' ) ) {
				$postid = (int)$_GET['post'];
				$layout = get_post( $postid );
				if(null === $layout){
				    exit;
                }
				$new_id = Themify_Builder_Duplicate_Page::duplicate( $layout );
				delete_post_meta( $new_id, '_themify_builder_prebuilt_layout' );
				wp_redirect( admin_url( 'edit.php?post_type=' . get_post_type( $postid ) ) );
				exit;
			}
		}

		/**
		 * Add Builder support to Layout and Layout Part post types.
		 *
		 * @access public
		 * @since 2.4.8
		 */
		public function add_builder_support( $post_types ) {
			$post_types[self::LAYOUT_SLUG] = self::LAYOUT_SLUG;
			$post_types[self::LAYOUT_PART_SLUG] = self::LAYOUT_PART_SLUG;
			return $post_types;
		}
                
                

	}
}

if( !class_exists( 'Themify_Builder_Layouts_Provider' ) ) {//05/20/22 deprecated will be removed in the future
	/**
	 * Base class for Builder layout provider
	 *
	 * Different types of layouts that can be imported in Builder must each extend this base class
	 *
	 * @since 2.0.0
	 */
	class Themify_Builder_Layouts_Provider
	{

		/**
		 * Get the ID of provider
		 *
		 * @return string
		 */
		public function get_id() {
		}

		/**
		 * Get the label of provider
		 *
		 * @return string
		 */
		public function get_label() {
		}

		/**
		 * Get a list of available layouts provided by this class
		 *
		 * @return array
		 */
		public function get_layouts() {
			return array();
		}

		/**
		 * Check if the layout provider has any layouts available
		 *
		 * @return bool
		 */
		public function has_layouts() {
			return false;
		}

	}
}
