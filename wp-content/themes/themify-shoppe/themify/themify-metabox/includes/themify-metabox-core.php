<?php

defined( 'ABSPATH' ) || exit;

if( ! class_exists( 'Themify_Metabox' ) ) :
class Themify_Metabox {

	private static $instance = null;
	private static $panel_options=array();

	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		$this->includes();
		add_action( 'init', array( $this, 'hooks' ), 100 );
	}

	/**
	 * Setup plugin actions.
	 *
	 * Hooked to init[100] to ensure post types are loaded
	 * @since 1.0.3
	 */
	function hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'pre_post_update', array( $this, 'save_postdata' ), 101 );
		add_action( 'save_post', array( $this, 'save_postdata' ), 101 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
		add_filter( 'is_protected_meta', array( $this, 'protected_meta' ), 10, 3 );
		// Color Manager Hooks
		add_action( 'wp_ajax_themify_import_colors', array( $this, 'themify_import_colors' ), 10 );
		add_action( 'wp_ajax_themify_save_colors', array( $this, 'themify_save_colors' ), 10 );
		add_action( 'admin_init', array( $this, 'themify_export_colors' ), 10 );

		if ( current_theme_supports( 'frontend-page-options' ) && is_user_logged_in() ) {
            if ( ! empty( $_GET['tf-meta-opts'] ) ) {
                if('update'===$_GET['tf-meta-opts']){
                    add_filter( 'save_post', array( $this, 'page_options_updated' ), 102 );
                }
				else{
                    self::load_meta_options();
                    add_action( 'add_meta_boxes', array( $this, 'remove_extra_meta_boxes' ), 99 );
                    add_action( 'admin_head', array( $this, 'remove_help' ), 99 );
                    add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_scripts' ), 100 );
				}
            }
			else{
                add_action( 'template_redirect', array( $this, 'page_options_init' ), 10 );
            }
        }
	}

	function includes() {
		require_once( THEMIFY_METABOX_DIR . 'includes/themify-field-types.php' );
		require_once( THEMIFY_METABOX_DIR . 'includes/themify-metabox-utils.php' );
		require_once( THEMIFY_METABOX_DIR . 'includes/themify-user-fields.php' );
		require_once( THEMIFY_METABOX_DIR . 'includes/themify-term-fields.php' );
	}

	/**
	 * Returns a list of all meta boxes registered through Themify Metabox plugin
	 *
	 * @return array
	 * @since 1.0.2
	 */
	function get_meta_boxes() {
		static $meta_boxes = null;

		if( ! isset( $meta_boxes ) ) {
			// Themify Custom Panel by default is added to all post types
			$types = get_post_types( '', 'names' );
			$meta_boxes = apply_filters( 'themify_metaboxes', array(
				'themify-meta-boxes' => array(
					'id' => 'themify-meta-boxes',
					'title' => __( 'Themify Custom Panel', 'themify' ),
					'context' => 'normal',
					'priority' => 'high',
					'screen' => $types,
				),
			) );
		}

		return $meta_boxes;
	}

	/**
	 * Returns the parameters for a meta box
	 *
	 * @param $id string the ID of the metabox registered using "themify_metaboxes" filter hook
	 * @return array
	 * @since 1.0.2
	 */
	public function get_meta_box( $id ) {
		$meta_boxes = $this->get_meta_boxes();
		return isset( $meta_boxes[$id] ) ?$meta_boxes[$id]:false;
	}

	/**
	 * Returns all the tabs and their fields for a meta box
	 *
	 * @param $meta_box string the ID of the metabox registered using "themify_metaboxes" filter hook
	 * @param $post_type string optional post_type to filter down the list of tabs displayed in the meta box
	 * @return array
	 * @since 1.0.2
	 */
	function get_meta_box_options( $meta_box, $post_type = null ) {
	    if( ! isset( self::$panel_options[$meta_box] ) ) {
	        $themify_write_panels = apply_filters( 'themify_do_metaboxes', array() );
	        self::$panel_options[$meta_box] = array_filter( apply_filters( "themify_metabox/fields/{$meta_box}", $themify_write_panels, $post_type ) );
		}

		$meta_box_result = self::$panel_options[$meta_box];
		$isShop = themify_metabox_shop_pageId() !== false && themify_metabox_shop_pageId() === get_the_ID();

		// filter the panels by post type
		if ( $post_type ) {
			$meta_box_result = array();
			foreach ( self::$panel_options[ $meta_box ] as $tab ) {
				if ( !empty( $tab['pages'] ) && ! is_array( $tab['pages'] )  ) {
				    $tab['pages'] = array_map( 'trim', explode( ',', $tab['pages'] ) );
				}

				if ( ! isset( $tab['pages'] ) || in_array( $post_type, $tab['pages'], true ) ) {
				    if(!($isShop===true  && isset($tab['id']) && strpos(trim($tab['id']),'query-')===0)){//disable query posts,portfolio and etc in shop page
						$meta_box_result[$tab['id']] = $tab;
				    }
				}
			}
		}

		return apply_filters( 'themify_metabox_panel_options', $meta_box_result );
	}

	function clear_cache() {
		self::$panel_options = array();
	}

	function admin_menu() {
		foreach( $this->get_meta_boxes() as $meta_box ) {
			add_meta_box( $meta_box['id'], $meta_box['title'], array( $this, 'render' ), $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
		}
	}

	/**
	 * Save Custom Write Panel Data
	 * @param number
	 * @return mixed
	 */
	function save_postdata( $post_id ) {
		global $post;

		if( function_exists( 'icl_object_id' ) && current_filter() === 'save_post' ) {
			wp_cache_delete( $post_id, 'post_meta' );
		}

		if ( ! isset( $_POST['post_type'] ) || ('page' === $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id )) || ('page' !== $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ))) {
			return $post_id;
		} 

		if( !empty( $_POST['themify_proper_save'] )) {
			foreach( $this->get_meta_boxes() as $meta_box ) {
				$tabs = $this->get_meta_box_options( $meta_box['id'], $_POST['post_type'] );
				if( empty( $tabs ) )
					continue;

				foreach( $tabs as $tab ) {
					foreach ( $tab['options'] as $field ) {

						if( 'multi' === $field['type'] ) {
							// Grouped fields
							foreach ( $field['meta']['fields'] as $field ) {
								$this->_save_meta( $field, $post_id );
							}
						} elseif ( 'toggle_group' === $field['type'] ) {
							foreach ( $field['meta'] as $field_toggle ) {
								if ( 'multi' === $field_toggle['type'] ) {
									foreach ( $field_toggle['meta']['fields'] as $field_multi ) {
										$this->_save_meta( $field_multi, $post_id );
									}
								} else {
									$this->_save_meta( $field_toggle, $post_id );
								}
							}
						} else {
							$this->_save_meta( $field, $post_id );
						}
					}
				}
			}
		} else {
			if ( isset( $post ) && isset( $post->ID ) ) {
				return $post->ID;
			}
		}
		return false;
	}

	/**
	 * Helper function that saves the custom field
	 *
	 * @since 1.0.2
	 */
	function _save_meta( $field, $post_id ) {
		if(!isset($field['name'])){
		    return;
        }
	    $new_meta = isset( $field['name'], $_POST[ $field['name'] ] ) ? $_POST[ $field['name'] ] : '';

		// when a default value is set for the field and it's the same as $new_meta, do not bother with saving the field
		if ($new_meta === 'default'|| ( isset( $field['default'] ) && $new_meta == $field['default'] )) {
			$new_meta = '';
		}

		// remove empty meta fields from database
		if ( '' === $new_meta ) {
			if ( metadata_exists( 'post', $post_id, $field['name'] ) ) {
				delete_post_meta( $post_id, $field['name'] );
			}
			return;
		}
		
		/* sanitization */
		if (isset( $field['type'] ) && ($field['type'] === 'textbox' || $field['type'] === 'textarea' ) && ! current_user_can( 'unfiltered_html' )) {
			$new_meta = wp_kses_data( $new_meta );
		}
		
		$old_meta = get_post_meta( $post_id, $field['name'], true );
		if ( $new_meta !== '' && $new_meta != $old_meta ) {
			update_post_meta( $post_id, $field['name'], $new_meta );
		}
	}

	function render( $post, $metabox ) {
		global $post, $typenow;
		$tabs = $this->get_meta_box_options( $metabox['id'], $typenow );
		if( empty( $tabs ) ) {

			if( $metabox['id'] === 'themify-meta-boxes' ) {
				// this is a hack to prevent Themify Custom Panel from showing up when it has no options to show
				echo '<style>#themify-meta-boxes, .metabox-prefs label[for="themify-meta-boxes-hide"] { display: none !important; }</style>';
			}
			return;
		}

		$this->render_tabs( $tabs, $post, $metabox['id'] );
	}

	/**
	 * Output the form and the fields
	 *
	 * @return null
	 */
	function render_tabs( $tabs, $post, $id ) {
		$post_id = $post->ID;
		$isShop=themify_metabox_shop_pageId()===$post_id;
		echo '<div class="themify-meta-box-tabs" id="' . $id . '-meta-box">';
			echo '<ul class="ilc-htabs themify-tabs-heading">';
			foreach( $tabs as $tab ) {
				if( isset( $tab['display_callback'] ) && is_callable( $tab['display_callback'] ) ) {
					$show = (bool) call_user_func( $tab['display_callback'], $tab );
					if( ! $show ) { // if display_callback returns "false",
						continue;  // do not output the tab
					}
				}
				$panel_id = isset( $tab['id'] ) ? $tab['id'] : sanitize_title( $tab['name'] );
				$class = isset( $tab['default_active'] ) ? 'class="default_active"' : '';
				echo '<li><span><a ' . $class . ' id="' . esc_attr( $panel_id . 't' ) . '" href="' . esc_attr( '#' . $panel_id ) . '">' . esc_html( $tab['name'] ) . '</a></span></li>';
			}
			echo '</ul>';
			echo '<div class="ilc-btabs themify-tabs-body">';
			foreach( $tabs as $tab ) {
				if( isset( $tab['display_callback'] ) && is_callable( $tab['display_callback'] ) ) {
					$show = (bool) call_user_func( $tab['display_callback'], $tab );
					if( ! $show ) { // if display_callback returns "false",
						continue;  // do not output the tab
					}
				}
				$panel_id = isset( $tab['id'] )? $tab['id']: sanitize_title( $tab['name'] );
				?>
				<div id="<?php echo esc_attr( $panel_id ); ?>" class="ilc-tab themify_write_panel">

				<div class="inside">

					<input type="hidden" name="themify_proper_save" value="true" />

					<?php $themify_custom_panel_nonce = wp_create_nonce('tf_nonce'); ?>

					<!-- alerts -->
					<div class="tb_alert"></div>
					<!-- /alerts -->
					
					<?php
					foreach( $tab['options'] as $field ){
						if($isShop===true && isset($field['shop']) && $field['shop']===false){
							continue;
						}
                        if ( $field['type'] !== 'toggle_group' ) {
						$toggle_class = '';
						if( isset( $field['display_callback'] ) && is_callable( $field['display_callback'] ) ) {
							$show = (bool) call_user_func( $field['display_callback'], $field );
							if( ! $show ) { // if display_callback returns "false",
								continue;  // do not output the field
							}
						}

						$meta_value = isset($field['name']) ? get_post_meta( $post_id, $field['name'], true ) : '';
						$ext_attr = '';
						if( isset($field['toggle']) ){
							$toggle_class .= 'themify-toggle ';
							$toggle_class .= (is_array($field['toggle'])) ? implode(' ', $field['toggle']) : $field['toggle'];
							if ( is_array( $field['toggle'] ) && in_array( '0-toggle', $field['toggle'] ) ) {
								$toggle_class .= ' default-toggle';
							}
						}
						if ( isset( $field['class'] ) ) {
							$toggle_class .= ' ';
							$toggle_class .= is_array( $field['class'] ) ? implode( ' ', $field['class'] ) : $field['class'];
						}
						$data_hide = '';
						if ( isset( $field['hide'] ) ) {
							$data_hide = is_array( $field['hide'] ) ? implode( ' ', $field['hide'] ) : $field['hide'];
						}
						if( isset($field['default_toggle']) && $field['default_toggle'] === 'hidden' ){
							$ext_attr = 'style="display:none;"';
						}
						if( isset($field['enable_toggle']) && $field['enable_toggle'] == true ) {
							$toggle_class .= ' enable_toggle';
						}

						// @todo
						$meta_box = $field;

						echo $this->before_meta_field( compact( 'meta_box', 'toggle_class', 'ext_attr', 'data_hide' ) );

						do_action( "themify_metabox/field/{$field['type']}", compact( 'meta_box', 'meta_value', 'toggle_class', 'data_hide', 'ext_attr', 'post_id', 'themify_custom_panel_nonce' ) );

						// backward compatibility: allow custom function calls in the fields array
						if( isset( $field['function'] ) && is_callable( $field['function'] ) ) {
							call_user_func( $field['function'], $field );
						}

						echo $this->after_meta_field();
                        } else {
	                        echo $this->before_meta_toggle_field( $field['title'] );
	                        foreach ( $field['meta'] as $toggle_field ) {
		                        $toggle_class = '';
		                        if ( isset( $toggle_field['display_callback'] ) && is_callable( $toggle_field['display_callback'] ) ) {
			                        $show = (bool) call_user_func( $toggle_field['display_callback'], $toggle_field );
			                        if ( ! $show ) { // if display_callback returns "false",
				                        continue;  // do not output the field
			                        }
		                        }
		                        $meta_value = isset( $toggle_field['name'] ) ? get_post_meta( $post_id, $toggle_field['name'], true ) : '';
		                        $ext_attr   = '';
		                        if ( isset( $toggle_field['toggle'] ) ) {
			                        $toggle_class .= 'themify-toggle ';
			                        $toggle_class .= ( is_array( $toggle_field['toggle'] ) ) ? implode( ' ', $toggle_field['toggle'] ) : $toggle_field['toggle'];
			                        if ( is_array( $toggle_field['toggle'] ) && in_array( '0-toggle', $toggle_field['toggle'] ) ) {
				                        $toggle_class .= ' default-toggle';
			                        }
		                        }
		                        if ( isset( $toggle_field['class'] ) ) {
			                        $toggle_class .= ' ';
			                        $toggle_class .= is_array( $toggle_field['class'] ) ? implode( ' ', $toggle_field['class'] ) : $toggle_field['class'];
		                        }
		                        $data_hide = '';
		                        if ( isset( $toggle_field['hide'] ) ) {
			                        $data_hide = is_array( $toggle_field['hide'] ) ? implode( ' ', $toggle_field['hide'] ) : $toggle_field['hide'];
		                        }
		                        if ( isset( $toggle_field['default_toggle'] ) && $toggle_field['default_toggle'] === 'hidden' ) {
			                        $ext_attr = 'style="display:none;"';
		                        }
		                        if ( isset( $toggle_field['enable_toggle'] ) && $toggle_field['enable_toggle'] == true ) {
			                        $toggle_class .= ' enable_toggle';
		                        }
		                        $meta_box = $toggle_field;
		                        echo $this->before_meta_field( compact( 'meta_box', 'toggle_class', 'ext_attr', 'data_hide' ) );
		                        do_action( "themify_metabox/field/{$toggle_field['type']}", compact( 'meta_box', 'meta_value', 'toggle_class', 'data_hide', 'ext_attr', 'post_id', 'themify_custom_panel_nonce' ) );
		                        // backward compatibility: allow custom function calls in the fields array
		                        if ( isset( $toggle_field['function'] ) && is_callable( $toggle_field['function'] ) ) {
			                        call_user_func( $toggle_field['function'], $toggle_field );
		                        }
		                        echo $this->after_meta_field();
	                        }
	                        echo $this->after_meta_toggle_field();
                        }

			} ?>
				</div>
				</div>
				<?php
			}
		echo '</div>';//end .ilc-btabs
		echo '</div>';//end #themify-meta-box-tabs
	}

	function before_meta_field( $args = array() ) {
		$meta_box = $args['meta_box'];
		$meta_box_name = isset( $meta_box['name'] ) ? $meta_box['name'] : '';
		$toggle_class = isset( $args['toggle_class'] ) ? $args['toggle_class'] : '';
		$ext_attr = isset( $args['ext_attr'] ) ? $args['ext_attr'] : '';
		$html = '
		<input type="hidden" name="' . esc_attr( $meta_box_name ) . '_noncename" id="' . esc_attr( $meta_box_name ) . '_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />
		<div class="themify_field_row ' . esc_attr( $toggle_class ) . '" ' . esc_attr( $ext_attr );
		if ( isset( $args['data_hide'] ) && ! empty( $args['data_hide'] ) ) {
			$html .= ' data-hide="' . esc_attr( $args['data_hide'] ) . '"';
		}
		$html .= '>';
		if ( isset( $meta_box['title'] ) ) {
			$html .= '<div class="themify_field_title">' . esc_html( $meta_box['title'] ) . '</div>';
		}
		$html .= '<div class="themify_field themify_field-' . esc_attr( $meta_box['type'] ) . '">';

		$html .= isset( $meta_box['meta']['before'] ) ? $meta_box['meta']['before'] : '';
		return $html;
	}

	function after_meta_field( $after = null ) {
		$html = isset( $after ) ? $after : '';
		$html .= '
			</div>
		</div><!--/themify_field_row -->';
		return $html;
	}

	function before_meta_toggle_field( $title ) {
		$html = '
		<div class="themify_field_row themify_field_row_toggle tf_clearfix">
		    <div class="themify_toggle_group_wrapper">
                <div class="themify_toggle_group_label">
                    <span>' . $title . '</span>
                    <i class="tf_plus_icon"></i>
                </div>
                <div class="themify_toggle_group_inner">';

		return $html;
	}

	function after_meta_toggle_field() {
		$html = '</div></div>
		</div><!--/themify_field_row_toggle -->';

		return $html;
	}
	

	function admin_enqueue_scripts( $page = '' ) {
		global $typenow, $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		$protocol = is_ssl() ? 'https': 'http';
		$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.css";
		$ver=defined('THEMIFY_VERSION')?THEMIFY_VERSION:'1.0';
		wp_register_style( 'jquery-ui-smoothness', $url, false, null );

		wp_register_style( 'themify-datetimepicker', THEMIFY_METABOX_URI . 'css/jquery-ui-timepicker.min.css', array( 'jquery-ui-smoothness' ) );
		wp_register_style( 'themify-colorpicker', themify_metabox_enque(THEMIFY_METABOX_URI . 'css/themify.minicolors.css'),null, $ver );
		wp_register_style( 'themify-metabox', themify_metabox_enque(THEMIFY_METABOX_URI . 'css/styles.css'), array( 'themify-colorpicker', 'themify-datetimepicker' ), $ver  );
        if(!empty($_GET['tf-meta-opts'])){
            wp_enqueue_style( 'themify-page-option', themify_metabox_enque(THEMIFY_METABOX_URI . 'css/page-options-iframe.css'),null, $ver );
        }

		wp_register_script( 'meta-box-tabs', themify_metabox_enque(THEMIFY_METABOX_URI . 'js/meta-box-tabs.js'), array( 'jquery' ), '2.0', true );
		wp_register_script( 'media-library-browse', themify_metabox_enque(THEMIFY_METABOX_URI . 'js/media-lib-browse.js'), array( 'jquery'), '1.0', true );
		wp_register_script( 'themify-colorpicker', themify_metabox_enque(THEMIFY_METABOX_URI . 'js/themify.minicolors.js'), array( 'jquery' ), null, true );

		wp_register_script( 'themify-metabox', themify_metabox_enque(THEMIFY_METABOX_URI . 'js/scripts.js'), array( 'jquery', 'meta-box-tabs', 'media-library-browse', 'jquery-ui-tabs', 'themify-colorpicker' ),$ver, true );
		wp_register_script( 'themify-plupload', themify_metabox_enque(THEMIFY_METABOX_URI . 'js/plupload.js'), array( 'jquery', 'themify-metabox' ), null, true );

		// Inject variable for Plupload
		$global_plupload_init = array(
			'runtimes'				=> 'html5',
			'browse_button'			=> 'plupload-browse-button', // adjusted by uploader
			'container' 			=> 'plupload-upload-ui', // adjusted by uploader
			'drop_element' 			=> 'drag-drop-area', // adjusted by uploader
			'file_data_name' 		=> 'async-upload', // adjusted by uploader
			'multiple_queues' 		=> true,
			'max_file_size' 		=> wp_max_upload_size() . 'b',
			'url' 					=> admin_url( 'admin-ajax.php' ),
			'filters' 				=> array(
				array(
					'title' => __( 'Allowed Files', 'themify' ),
					'extensions' => 'jpg,jpeg,gif,png,ico,zip,txt,svg,webp',
				),
			),
			'multipart' 			=> true,
			'urlstream_upload' 		=> true,
			'multi_selection' 		=> false, // added by uploader
			 // additional post data to send to our ajax hook
			'multipart_params' 		=> array(
				'_ajax_nonce' => '', // added by uploader
				'imgid' => 0 // added by uploader
			)
		);
		wp_localize_script( 'themify-metabox', 'global_plupload_init', $global_plupload_init );
		wp_localize_script( 'themify-metabox', 'TF_Metabox', array(
			'url' => THEMIFY_METABOX_URI,
			'includes_url' => includes_url(),
		) );

		do_action( 'themify_metabox_register_assets' );

		// attempt to enqueue Metabox API assets automatically when needed
		if( ( $page === 'post.php' || $page === 'post-new.php' ) ) {
			foreach( $this->get_meta_boxes() as $meta_box ) {
				if( isset( $meta_box['screen'] ) && in_array( $typenow, $meta_box['screen'],true ) ) {
					$this->enqueue();
					break;
				}
			}
		}
	}

	/**
	 * Enqueues Themify Metabox assets
	 *
	 * @since 1.0
	 */
	function enqueue() {
		wp_enqueue_media();
		wp_enqueue_style( 'themify-metabox' );
		wp_enqueue_script( 'themify-metabox' );
		wp_enqueue_script( 'themify-plupload' );

		do_action( 'themify_metabox_enqueue_assets' );
	}

	/*
	 * Protect $themify_write_panels fields
	 * This will hide these fields from Custom Fields panel
	 *
	 * @since 1.8.2
	 */
	function protected_meta( $protected, $meta_key, $meta_type ) {

		/* ensure this method is not called before self::$panel_options is filled */
		if ( ! did_action( 'all_admin_notices' ) ) return $protected;
		
		global $typenow;
		static $protected_metas = null;
		if( $protected_metas === null ) {
			$protected_metas=array();
			foreach( $this->get_meta_boxes() as $meta_box ) {
				$protected_metas = array_merge( themify_metabox_get_field_names( $this->get_meta_box_options( $meta_box['id'], $typenow ) ), $protected_metas );
			}
		}

		if( is_array( $protected_metas ) && in_array( $meta_key, $protected_metas ) ) {
			$protected = true;
		}
		
		return $protected;
	}

	/**
	 * Import color and gradient swatches
	 * @since 4.5
	 */
	function themify_import_colors() {
		check_ajax_referer('tf_nonce', 'nonce');
		$response['status'] = 'ERROR';
		$response['msg'] = __( 'Oopsss ... .Something went wrong.', 'themify' );
		if ( isset( $_FILES['file'] ) ) {
			$fileContent = themify_get_file_contents( $_FILES['file']['tmp_name'] );
			$new_data = unserialize( $fileContent );
			if ( $new_data !== null ) {
				if ( 'colors' === $_POST['type'] ) {
					$type = 'colors';
				} elseif ( 'gradients' === $_POST['type'] ) {
					$type = 'gradients';
				}
				$end = end( $new_data );
				$end=!empty($end['uid']);
				if ( ($end===true && 'colors' === $type) || ( $end===false && 'gradients' === $type ) ) {
					$currentSwatches = unserialize( get_option( 'themify_saved_' . $type, serialize( array() ) ) );
					$new_data = $currentSwatches + $new_data;
					$new_data = !empty($new_data) && is_array($new_data) ? $new_data : array();
					$_key='themify_saved_' . $type;
					delete_option($_key);
					add_option($_key,serialize( $new_data ), '', 'no' );
					$response['status'] = 'SUCCESS';
					$response['colors'] = $new_data;
					$response['msg'] = __( 'New swatches successfully imported.', 'themify' );
				} else {
					$response['status'] = 'FAILED';
					$response['msg'] = __( 'The uploaded file is not valid.', 'themify' );
				}

			}
		}
		wp_send_json( $response );
		wp_die();
	}

	/**
	 * Save color and gradient swatches
	 * @since 4.5
	 */
	function themify_save_colors() {
		check_ajax_referer('tf_nonce', 'nonce');
		$type = $_POST['type'];
		$colors = !empty( $_POST['colors'] ) && is_array($_POST['colors']) ? serialize( $_POST['colors'] ) : serialize( array() );
		$_key='themify_saved_' . $type;
		delete_option($_key);
		add_option($_key,$colors, '', 'no' );
		$response['status'] = 'success';
		wp_send_json( $response );
		wp_die();
	}

	/**
	 * Localize required data for manage color and gradient swatches
	 * @since 4.5
	 */
	public static function themify_localize_cm_data() {
	    $default['colors'] = array(
	            '99999'=>array('color'=>'rgb(96,91,168)','opacity'=>'1','uid'=>'99999'),
	            '88888'=>array('color'=>'rgb(240,110,170)','opacity'=>'1','uid'=>'88888'),
	            '77777'=>array('color'=>'rgb(255,242,4)','opacity'=>'1','uid'=>'77777'),
	            '66666'=>array('color'=>'rgb(254,101,0)','opacity'=>'1','uid'=>'66666'),
	            '55555'=>array('color'=>'rgb(238,35,18)','opacity'=>'1','uid'=>'55555'),
	            '44444'=>array('color'=>'rgb(140,197,63)','opacity'=>'1','uid'=>'44444'),
	            '33333'=>array('color'=>'rgb(27,187,180)','opacity'=>'1','uid'=>'33333'),
	            '22222'=>array('color'=>'rgb(96,204,247)','opacity'=>'1','uid'=>'22222'),
	            '11111'=>array('color'=>'rgb(255,255,255)','opacity'=>'1','uid'=>'11111'),
	            '11110'=>array('color'=>'rgb(0,0,0)','opacity'=>'1','uid'=>'11110'),
        );
	    // validate colors value
		$colors = unserialize( get_option( 'themify_saved_colors', serialize( $default['colors'] ) ) );
        if(is_array($colors) && !empty($colors)){
			$test_color = current($colors);
			$colors = is_array($test_color) && !empty($test_color['color']) ? $colors : $default['colors'];
        }else if(!is_array($colors)){
            $colors = $default['colors'];
        }
		unset($default);
		// validate gradients value
		$grads = unserialize( get_option( 'themify_saved_gradients', serialize( array() ) ) );
        if(is_array($grads) && !empty($grads)){
			$test_color = current($grads);
			$grads = is_array($test_color) && !empty($test_color['setting']) ? $grads : array();
		}else if (!is_array($grads)){
			$grads = array();
		}
	    // Localize required data for color manager
		return array(
			'nonce' => wp_create_nonce( 'tf_nonce' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'colors' => $colors,
			'gradients' => $grads,
			'exportColorsURL' => add_query_arg( 'themify_export_colors', 'true', wp_nonce_url( admin_url( 'admin.php?page=themify' ), 'themify_export_colors_nonce' ) ),
			'exportGradientsURL' => add_query_arg( 'themify_export_gradients', 'true', wp_nonce_url( admin_url( 'admin.php?page=themify' ), 'themify_export_colors_nonce' ) ),
			'labels' => array(
				'import' => __( 'Import', 'themify' ),
				'export' => __( 'Export', 'themify' ),
				'save' => __( 'Save', 'themify' ),
				'ie' => __( 'Import/Export', 'themify' )
			)
		);
	}

	/**
	 * Export color and gradient swatches
	 * @since 4.5
	 */
	function themify_export_colors() {
		if ( ( !empty( $_GET['themify_export_colors'] ) || !empty( $_GET['themify_export_gradients'] ) ) && is_user_logged_in() && check_admin_referer( 'themify_export_colors_nonce' ) ) {
			if ( ini_get( 'zlib.output_compression' ) ) {
				ini_set( 'zlib.output_compression', 'Off' );
			}
			$type = !empty( $_GET['themify_export_colors'] )?'colors':'gradients';
			ob_start();
			header( 'Content-Type: application/force-download' );
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: private', false );
			header( 'Content-Disposition: attachment; filename="themify_' . $type . '_export_' . date( 'Y_m_d' ) . '.txt"' );
			header( 'Content-Transfer-Encoding: binary' );
			ob_clean();
			flush();
			echo get_option( 'themify_saved_' . $type, serialize( array() ) );
			exit();
		}
	}

	/**
	 * Initialize Page Options feature on frontend
	 */
	function page_options_init() {
        $id = get_the_ID();

		/* the first parameter passed to 'frontend-page-options', can limit what post types this feature is enabled on */
		$support = get_theme_support( 'frontend-page-options' );
		if ( is_array( $support ) && ! empty( $support[0] ) ) {
			if ( ! in_array( get_post_type( $id ), $support[0] ) ) {
				return;
			}
		}

        if ( is_user_logged_in() && current_user_can( 'edit_'.get_post_type($id), $id ) ) {
			add_action( 'wp_before_admin_bar_render', array( $this, 'add_meta_options_menu' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'page_options_enqueue' ), 10 );
		}
	}

	function page_options_enqueue() {
	    if(!class_exists('Themify_Builder_Model') || !Themify_Builder_Model::is_front_builder_activate()){
		$version = defined( 'THEMIFY_VERSION' ) ? THEMIFY_VERSION : $GLOBALS['wp_version'];
		wp_enqueue_script( 'themify-page-options', themify_metabox_enque(THEMIFY_METABOX_URI . 'js/page-options.js'), null, $version, true );
	    }
	}

    /**
     * Add meta options menu
     */
    function add_meta_options_menu() {
		global $wp_admin_bar;
		$post_type = get_post_type_object(get_post_type());
		$t = $post_type?$post_type->labels->singular_name:__('Page','themify');
		$wp_admin_bar->add_menu( array(
			'id' => 'themify-page-options',
			'parent' => 'edit',
			'title' => sprintf('%s %s',$t,__('Options','themify')),
			'href' => add_query_arg(array('tf-meta-opts'=>get_the_ID()),get_edit_post_link())
		));
    }

    /**
     * Add meta options menu
     */
    function load_meta_options() {
        add_filter( 'use_block_editor_for_post', '__return_false' );
        remove_action( 'in_admin_header', 'wp_admin_bar_render', 0);
        add_filter('screen_options_show_screen', '__return_false');
        $post_type = get_post_type($_GET['tf-meta-opts']);
        remove_post_type_support($post_type, 'editor');
        remove_post_type_support($post_type, 'title');
        remove_post_type_support($post_type, 'revisions');
    }

    function remove_extra_meta_boxes() {
        $post_type = get_post_type($_GET['tf-meta-opts']);
        global $wp_meta_boxes;
        $comments = $wp_meta_boxes[$post_type]['normal']['core']['commentstatusdiv'];
        $wp_meta_boxes = array($post_type=> array(
            'normal'=>array('high'=>array('themify-meta-boxes'=>$wp_meta_boxes[$post_type]['normal']['high']['themify-meta-boxes'])),
            'side'=>array('core'=>array('submitdiv'=>$wp_meta_boxes[$post_type]['side']['core']['submitdiv'])))
        );
        if(!empty($comments)){
            $wp_meta_boxes[$post_type]['normal']['low'] = array('commentstatusdiv'=>$comments);
        }
    }

    function remove_help() {
        $screen = get_current_screen();
        $screen->remove_help_tabs();
    }

    function page_options_updated($post_id) {
        wp_redirect(add_query_arg('tf-meta-opts',$post_id,get_edit_post_link( $post_id ,'edit')));
        exit;
    }

    function dequeue_scripts( $page = '' ) {
        global $wp_scripts;
        $plugin_url = plugins_url();
        foreach( $wp_scripts->queue as $script ) {
            $url = $wp_scripts->registered[$script]->src;
            if(!empty($url) && strpos($url,$plugin_url)!==false && strpos($script,'themify')===false){
                wp_dequeue_script($script);
            }
        }
    }
}
endif;
add_action( 'init', array('Themify_Metabox','get_instance'), 10 );
