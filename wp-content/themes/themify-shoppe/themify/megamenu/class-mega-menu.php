<?php
/**
 * Class that generates custom markup for mega menu that shows posts by categories
 * @package themify
 * @subpackage megamenu
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if( ! class_exists('Themify_Mega_Menu_Walker') ) {
	/**
	 * Class Themify_Mega_Menu_Walker generates menu with posts by category
	 * @since 1.0.0
	 */
	class Themify_Mega_Menu_Walker extends Walker_Nav_Menu {
		static $mega_open = false;
		private static $hasLayout=false;
		private static $open=false;
		static $disableAssetsLoading=false;
		/**
		 * render a widget menu item
		 *
		 * $depth level
		 * $class_names additional CSS classes to add to the menu wrapper
		 * @return string
		 */

		function render_widget_menu( $item, $depth, $class_names = '' ) {
			$output = '';
			if( $depth === 0 ) {
				$title = apply_filters( 'the_title', $item->title, $item->ID );
				$output = "<li $class_names><a role='button' tabindex='0'>" . $title . '<span class="child-arrow"></span></a><ul class="sub-menu tf_box">';
			}

			$menu_widget = Themify_Widgets_Menu::get_instance()->get_widget_menu_class( $item );
			$output .= '<li class="themify-widget-menu">' . Themify_Widgets_Menu::get_instance()->render_widget(
				$menu_widget,
				Themify_Widgets_Menu::get_instance()->get_widget_options( $item->ID ),
				array(
					'widget_id' => $item->ID // widget_id is required, some widgets use it
				)
			) . '';

			if( $depth === 0 ) {
				$output .= '</ul>';
			}

			return $output;
		}

		/**
		 * Render a Layout Part inside menu
		 *
		 * $depth level
		 * $class_names additional CSS classes to add to the menu wrapper
		 * @return string
		 */
		function render_layout_part( $item, $depth, $class_names = '' ) {
			$output = '';
			if( $depth === 0 ) {
				$title = apply_filters( 'the_title', $item->title, $item->ID );
				$output = "<li $class_names><a role='button' tabindex='0'>" . $title . '<span class="child-arrow"></span></a><ul class="sub-menu tf_box">';
			}

			$output .= '<li class="themify-widget-menu">' . do_shortcode( sprintf( '[themify_layout_part id="%s"]', $item->object_id ) ) . '';

			if(  $depth === 0 ) {
				$output .= '</ul>';
			}

			return $output;
		}

		function start_el( &$output, $item, $depth = 0, $args = array(), $current_object_id = 0 ) {

			$classes = empty ( $item->classes ) ? array () : (array) $item->classes;
			if( $item->type === 'taxonomy' ){
				$classes[]='mega-link';
			}
            $classes[]='menu-item-'.$item->ID;
			$class_names = implode(' ', apply_filters('nav_menu_css_class', array_filter(array_unique($classes) ), $item, $args, $depth ));
			$classes=null;
			$class_names = !empty ( $class_names )? 'class="'.esc_attr( $class_names ).'"' : '';

			/* handle the display of widget menu items */
			if( Themify_Widgets_Menu::get_instance()->is_menu_widget( $item ) ) {
			    $item_output= $this->render_widget_menu( $item, $depth, $class_names );
			} else if ( $item->type === 'post_type' && $item->object === 'tbuilder_layout_part' ) {
				$item_output = $this->render_layout_part( $item, $depth, $class_names );
			} else {
				$li_attributes = '';
				/* required for "mega" menu type which displays posts from a taxonomy term */
				if( $item->type === 'taxonomy' ) {
					$li_attributes = 'data-termid="' . $item->object_id . '" data-tax="' . $item->object . '"';
				}
				if($args->walker->has_children>0){
					$li_attributes.=' aria-haspopup="true"';
				}
				$output .= "<li $class_names $li_attributes>";

				$attributes  = !empty( $item->attr_title )? ' title="'  . esc_attr( $item->attr_title ) . '"': '';
				$attributes .= !empty( $item->target )    ? ' target="' . esc_attr( $item->target     ) . '"': '';
				$attributes .= !empty( $item->xfn )       ? ' rel="'    . esc_attr( $item->xfn        ) . '"': '';
				$attributes .= !empty( $item->url ) && $item->url!=='#'? ' href="'   . esc_attr( $item->url        ) . '"': ' role="button" tabindex="0"';

				$title = apply_filters( 'the_title', $item->title, $item->ID );

				$item_output = $args->before. "<a $attributes>" . $args->link_before . $title;

				if(isset($args->post_count) && $args->post_count===true && $item->type==='taxonomy'){
					$term = get_term( $item->object_id, $item->object );
					$item_output.='<span class="tf_post_count">'.$term->count.'</span>';
				}
				if($args->walker->has_children>0){
					$item_output.='<span class="child-arrow"></span>';
				}
				if ( themify_check( 'menu_description', true ) && ! empty( $item->post_content ) ) {
					$item_output .= '<small>' . esc_html( $item->post_content ) . '</small>';
				}
				$item_output.='</a> ' . $args->link_after . $args->after;
			}
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		/**
		 * Start sub level markup
		 * @param string $output
		 * @param int $depth
		 * @param array $args
		 */
		function start_lvl( &$output, $depth = 0, $args = array() ) {
			$cl='sub-menu';
			if(self::$hasLayout==='layout'){
				$cl.=' tf_scrollbar';
			}
			elseif(self::$hasLayout==='ajax'){
				$cl='tf_mega_taxes tf_left tf_box';
				$output.='<div class="mega-sub-menu sub-menu">';
				self::$open=true;
			}
			$output .= '<ul class="'.$cl.'">';
		}

		/**
		 * End sub level markup
		 * @param string $output
		 * @param int $depth
		 * @param array $args
		 */
		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$output .= '</ul>';
			if(self::$open===true){
				self::$open=false;
				$output.='</div>';
			}
		}

		/**
		 * Modify item rendering
		 * @param object $item
		 * @param array $children_elements
		 * @param int $max_depth
		 * @param int $depth
		 * @param array $args
		 * @param string $output
		 * @return null|void
		 * @since 1.0.0
		 */
		function display_element( $item, &$children_elements, $max_depth, $depth, $args, &$output ) {
			$id_field = $this->db_fields['id'];
			static $is=null;
			self::$hasLayout=false;
			if ( themify_is_menu_highlighted_link($item->ID) ) {
				$item->classes[] = 'highlight-link';
			}
			$cl=false;
			if ( in_array( 'mega', $item->classes,true ) || themify_is_mega_menu_type( $item->ID, 'mega' )){ // backward compatibility
				if($is===null){
					$is=true;
					self::preloadCssJs();
				}
				$item->classes[] = 'mega';
				$item->classes[] = 'has-mega-sub-menu';
				$item->classes[] = 'has-mega';
				self::$hasLayout='ajax';
				$cl='mega-sub-item';
			}
			elseif(in_array( 'columns', $item->classes,true ) || themify_is_mega_menu_type( $item->$id_field, 'column' )){// backwards compatibility
				if($is===null){
					$is=true;
					self::preloadCssJs();
				}
				$item->classes[] = 'has-mega-column';
				$item->classes[] = 'has-mega';
				if( $column_layout = get_post_meta( $item->ID, '_themify_mega_menu_columns_layout', true ) ) {
					$item->classes[] = 'layout-' . $column_layout;
				} else {
					$item->classes[] = 'layout-auto';
				}
				self::$hasLayout='layout';
				$cl='columns-sub-item';
			}
			elseif(($dropdown_columns = get_post_meta( $item->$id_field, '_themify_dropdown_columns', true ))){
			    if($dropdown_columns>1){
				$item->classes[] = 'has-mega-dropdown dropdown-columns-' . $dropdown_columns;
				if($is===null){
				    $is=true;
				    self::preloadCssJs();
				}
			    }
			}
			elseif(in_array( 'columns-sub-item', $item->classes,true ) ) {
					$cl='columns-sub-item';
			}
			if ( ! empty( $children_elements[ $item->$id_field ] ) ) {
				$item->classes[] = 'has-sub-menu';

				if ($cl!==false ) {
					foreach( $children_elements[ $item->$id_field ] as $child ) {
						$child->classes[] =$cl;
					}
				}
			}

			Walker_Nav_Menu::display_element( $item, $children_elements, $max_depth, $depth, $args, $output );
		}

		public static function preloadCssJs(){
		    if(self::$disableAssetsLoading===false){
			themify_enque_style('tf_megamenu',THEMIFY_URI . '/megamenu/css/megamenu.css',null,THEMIFY_VERSION,'screen and (min-width:' . (Themify_Enqueue_Assets::$mobileMenuActive + 1) . 'px)');
			Themify_Enqueue_Assets::addLocalization('done','tf_megamenu',true);
		    }
		}
	}
}

if( ! function_exists('themify_theme_mega_get_posts') ) {
	/**
	 * Returns posts from a taxonomy by a given term
	 * @param $term_id
	 * @param $taxonomy
	 * @return string
	 */
	function themify_theme_mega_get_posts( $term_id, $taxonomy ) {
		$taxObject  = get_taxonomy( $taxonomy );
		if ( is_wp_error( $taxObject ) || empty( $taxObject ) ) {
			return '';
		}

		$postPerPage = themify_get( 'setting-mega_menu_posts', 5, true );
		$term_query_args = apply_filters( 'themify_mega_menu_query',
			array(
				'post_type' => $taxObject->object_type,
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field' => 'id',
						'terms' => $term_id
					)
				),
				'suppress_filters' => false,
				'posts_per_page' => $postPerPage
			)
		);

		$posts = get_posts( $term_query_args );

		if ( $posts ) {
			global $post;
			ob_start();
			foreach( $posts as $post ) {
				setup_postdata( $post );

				$post_type = get_post_type();
				locate_template( array(
					"includes/loop-megamenu-{$post_type}.php",
					"themify/megamenu/templates/loop-megamenu-{$post_type}.php",
					'includes/loop-megamenu.php',
					'themify/megamenu/templates/loop-megamenu.php'
				), true, false );
			}
			$mega_posts = ob_get_clean();
			wp_reset_postdata();
		} else {
			$mega_posts = '<article itemscope itemtype="https://schema.org/Article" class="post"><p class="post-title">'.__('Error loading posts.', 'themify').'</p></article>';
		}

		return apply_filters( 'themify_mega_posts_output', $mega_posts, $term_id, $taxonomy );
	}
}

if( ! function_exists( 'themify_theme_mega_posts' ) ) {
	/**
	 * Called with AJAX to return posts
	 * @since 1.0.0
	 */
	function themify_theme_mega_posts() {
		$termid = isset( $_POST['termid'] )? $_POST['termid']: '';
		$taxonomy  = isset( $_POST['tax'] )? $_POST['tax']: 'category';
		echo themify_theme_mega_get_posts( $termid, $taxonomy );
		die();
	}
}
add_action('wp_ajax_themify_theme_mega_posts', 'themify_theme_mega_posts');
add_action('wp_ajax_nopriv_themify_theme_mega_posts', 'themify_theme_mega_posts');


/***************************************************
 * Themify Theme Access Point
 ***************************************************/

if ( ! function_exists( 'themify_theme_main_menu' ) ) {
	/**
	 * Sets custom menu selected in page custom panel as navigation, otherwise sets the default.
	 *
	 * @since 1.0.0
	 */
	function themify_theme_main_menu( $args = array() ) {

		$args['echo'] = false;
		$menu_type = 'main';

		if( 'no' !== themify_get( 'setting-mega_menu',false,true ) ) {
			$args['walker'] = new Themify_Mega_Menu_Walker;
			$menu_type = 'mega';
		}

		echo apply_filters( 'themify_' . $menu_type . '_menu_html', themify_menu_nav( $args ), $args );
	}
}

/**
 * Check if mega menu is enabled for a menu item
 *
 * @param int    $item_id
 * @param string $type
 *
 * @return bool
 * @since 1.0.0
 */
function themify_is_mega_menu_type( $item_id, $type = 'mega' ) {
	if($type==='mega' || $type==='column' || $type==='dual'){
		$key=$type==='mega'?'item':$type;
		return get_post_meta( $item_id, '_themify_mega_menu_'.$key, true ) == '1';
	}
}

function themify_is_menu_highlighted_link( $item_id ) {
		return get_post_meta( $item_id, '_themify_highlight_link', true );
}

/**
 * Add the option to enable mega menu to taxonomy menu types
 *
 * @since 1.0.0
 */
function themify_menu_mega_option( $item_id, $item, $depth, $args ) {
		$dropdown_columns = (int) get_post_meta( $item_id, '_themify_dropdown_columns', true );
		$is_mega = themify_is_mega_menu_type( $item_id, 'mega' );
		$is_column = !$is_mega && themify_is_mega_menu_type( $item_id, 'column' );
		$is_dropdown_column = !$is_column && ! empty( $dropdown_columns );
		$column_layout = get_post_meta( $item_id, '_themify_mega_menu_columns_layout', true );
		?>
		<div class="field-tf-mega description description-thin">
			<label for="edit-menu-item-tf-mega-<?php echo $item_id; ?>">
			<?php _e( 'Mega Menu', 'themify' ) ?>
			<a href="https://themify.me/docs/ultra-documentation#mega-menu" target="_blank"><i class="ti-help"></i></a>
			<br />
				<select name="menu-item-tf-mega[<?php echo $item_id; ?>]" id="edit-menu-item-tf-mega-<?php echo $item_id?>" class="edit-menu-item-tf-mega themify_field_tf-mega widefat">
					<option value=""></option>
					<option value="mega" <?php if( $is_mega ) echo 'selected';?>><?php _e( 'Mega Posts', 'themify' ); ?></option>
					<option value="columns" <?php if( $is_column ) echo 'selected';?>><?php _e( 'Fullwidth Columns', 'themify' ); ?></option>
				<option value="drop" <?php if( $is_dropdown_column===true ) echo 'selected';?>><?php _e( 'Dropdown Columns', 'themify' ); ?></option>
				</select>
			</label>
			<div class="tf-mega-columns-layout">
				<?php
				echo '<input type="hidden" name="menu-item-tf-mega-columns-layout[' . $item_id . ']" value="'.$column_layout.'" class="val">';
				foreach ( array(
					'' => __( 'Auto', 'themify' ),
					'4-8' => __( '1/3 - 2/3', 'themify' ),
					'8-4' => __( '2/3 - 1/3', 'themify' ),
					'6-3-3' => __( '2/4 - 1/4 - 1/4', 'themify' ),
					'3-3-6' => __( '1/4 - 1/4 - 2/4', 'themify' ),
					'3-6-3' => __( '1/4 - 2/4 - 1/4', 'themify' ),
					'3-9' => __( '1/4 - 3/4', 'themify' ),
					'9-3' => __( '3/4 - 1/4', 'themify' ),
				) as $key => $label ) {
					$selected = $column_layout === $key ? 'class="selected"' : '';
					echo '<a href="#" ' . $selected . ' data-value="'. $key . '" title="' . $label . '"></a>';
				}
				?>
			</div>
		</div>

	<p class="tf-dropdown-columns-field description description-thin">
		<label for="edit-menu-item-tf-dropdown-columns-<?php echo $item_id; ?>">
			<?php _e( 'Dropdown Columns', 'themify' ) ?><br />
			<select name="menu-item-tf-dropdown_columns[<?php echo $item_id; ?>]" id="edit-menu-item-tf-dropdown_columns-<?php echo $item_id?>" class="edit-menu-item-tf-dropdown_columns">
				<option value=""></option>
				<option value="2" <?php selected( 2, $dropdown_columns ) ?>>2</option>
				<option value="3" <?php selected( 3, $dropdown_columns ) ?>>3</option>
				<option value="4" <?php selected( 4, $dropdown_columns ) ?>>4</option>
			</select>
		</label>
	</p>

	<?php
			$allow_hightlight = apply_filters( 'themify_menu_highlight_link', false);
			if ($allow_hightlight) :
			$highlight = get_post_meta( $item_id, '_themify_highlight_link', true );
		?>
			<div class="field-tf-highlight description description-thin"><br>
				<label for="edit-menu-item-tf-highlight-<?php echo $item_id; ?>">
					<input type="checkbox" name="menu-item-tf-highlight[<?php echo $item_id; ?>]" value="1" <?php echo ($highlight ? 'checked="checked"' : ''); ?> id="edit-menu-item-tf-highlight-<?php echo $item_id?>" class="edit-menu-item-tf-highlight themify_field_tf-highlight widefat">
				<?php _e( 'Highlight this link', 'themify' ) ?><br />
				</label>
			</div>
		<?php endif;
}
add_action( 'wp_nav_menu_item_custom_fields', 'themify_menu_mega_option', 12, 4 );

/**
 * Save the mega menu option for menu items
 *
 * @since 1.0.0
 */
function themify_update_mega_menu_option( $menu_id, $menu_item_db_id, $args ) {
	$meta_keys = array(
		'_themify_mega_menu_item_tax' => 'menu-item-tf-mega_tax',
		'_themify_dropdown_columns'   => 'menu-item-tf-dropdown_columns',
		'_themify_mega_menu_columns_layout'   => 'menu-item-tf-mega-columns-layout',
		'_themify_highlight_link'   => 'menu-item-tf-highlight',
	);

	/**
	 * save Mega Menu <select> option
	 * the Mega Menu option saves as two different custom fields, for backwards compatibility
	 */
	if( isset( $_POST['menu-item-tf-mega'][$menu_item_db_id] ) ) {
		/* delete both keys first to ensure they are not both activated on a menu item at the same time */
		delete_post_meta( $menu_item_db_id, '_themify_mega_menu_item' );
		delete_post_meta( $menu_item_db_id, '_themify_mega_menu_column' );
		if( $_POST['menu-item-tf-mega'][$menu_item_db_id] != '' ) {
			if( $_POST['menu-item-tf-mega'][$menu_item_db_id] === 'mega' ) {
				update_post_meta( $menu_item_db_id, '_themify_mega_menu_item', 1 );
			} elseif( $_POST['menu-item-tf-mega'][$menu_item_db_id] === 'columns' ) {
				update_post_meta( $menu_item_db_id, '_themify_mega_menu_column', 1 );
			}
		}
	}

	foreach ( $meta_keys as $meta_key => $param_key ) {
		$new_meta_value = isset( $_POST[$param_key] , $_POST[$param_key][$menu_item_db_id] ) ? $_POST[$param_key][$menu_item_db_id] : false;
		if ( $new_meta_value ) {
			update_post_meta( $menu_item_db_id, $meta_key, $new_meta_value );
		} else {
			delete_post_meta( $menu_item_db_id, $meta_key );
		}
	}
}
add_action( 'wp_update_nav_menu_item', 'themify_update_mega_menu_option', 10, 3 );

/**
 * Clear the mega menu option when a menu item is removed
 *
 * @since 1.0.0
 */
function themify_remove_mega_menu_meta( $post_id ) {
	if ( is_nav_menu_item( $post_id ) ) {
		delete_post_meta( $post_id, '_themify_mega_menu_item' );
		delete_post_meta( $post_id, '_themify_mega_menu_column' );
		delete_post_meta( $post_id, '_themify_mega_menu_column_sub_item' );
		delete_post_meta( $post_id, '_themify_mega_menu_dual' );
		delete_post_meta( $post_id, '_themify_highlight_link' );
	}
}
add_action( 'delete_post', 'themify_remove_mega_menu_meta', 1, 3 );

class Themify_Widgets_Menu {

	private static $instance = null;
	public $meta_key = '_themify_menu_widget';

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		if( is_admin() ) {
			require_once( ABSPATH . 'wp-admin/includes/widgets.php' );
			add_action( 'admin_init', array( $this, 'add_menu_meta_box' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'nav_menu_script' ), 12 );
			add_action( 'admin_footer-nav-menus.php', array( $this, 'admin_footer' ) );
			add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'wp_nav_menu_item_custom_fields' ), 99, 4 );
			add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 3 );
		}
	}

	function add_menu_meta_box() {
		add_meta_box(
			'themify-menu-widgets-meta',
			__( 'Widgets', 'themify' ),
			array( $this, 'widgets_menu_meta_box' ),
			'nav-menus',
			'side',
			'high'
		);
	}

	function widgets_menu_meta_box() {
		global $wp_widget_factory;

		?>
		<div id="themify-widget-section" class="posttypediv">
			<label for="themify-menu-widgets"><?php _e( 'Widgets', 'themify' ); ?>:</label>
			<select id="themify-menu-widgets" class="widefat">
				<?php foreach( $wp_widget_factory->widgets as $widget ) : ?>
					<option value="<?php echo get_class( $widget ); ?>"><?php echo $widget->name; ?> </option>
				<?php endforeach; ?>
			</select>

			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php _e( 'Add to Menu', 'themify' ); ?>" name="add-post-type-menu-item" id="themify-widget-menu-submit">
				</span>
			</p>
		</div>
		<?php
	}

	function wp_nav_menu_item_custom_fields( $item_id, $item, $depth, $args ) {
		if( $this->is_menu_widget( $item ) ) :
			global $wp_widget_factory;
			$class = $this->get_widget_menu_class( $item );

			/* Check widget availability */
			if ( ! isset( $wp_widget_factory->widgets[ $class ] ) )
				return;
			?>
			<input type="hidden" class="themify-widget-menu-type" value="widget" />
			<div class="themify-widget-options" style="clear: both;">
				<div class="widget-inside">
					<div class="form">
						<div class="widget-content">
							<?php echo $this->get_widget_form( $class, $item_id, $item ); ?>
						</div>
						<input type="hidden" class="id_base" name="id_base" value="<?php echo esc_attr( $wp_widget_factory->widgets[$class]->id_base ) ?>" />
						<input type="hidden" class="widget-id" name="widget-id" value="<?php echo time() ?>" />
					</div>
				</div>
			</div>
		<?php endif;
	}

	function wp_update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
		if ( isset( $_POST['menu-item-widget-options'],$_POST['menu-item-widget-options'][ $menu_item_db_id ] ) ) {
			global $wp_widget_factory;
			$new_instance = $_POST['menu-item-widget-options'][ $menu_item_db_id ];
			/* the widget class is stored in the menu item URL field */
			$widget_class = ltrim( $_POST['menu-item-url'][ $menu_item_db_id ], '#' );
			if ( isset( $wp_widget_factory->widgets[ $widget_class ] ) ) {

				$old_instance = get_post_meta( $menu_item_db_id, $this->meta_key, true );
				if ( ! is_array( $old_instance ) )
					$old_instance = array();
				$new_instance = $wp_widget_factory->widgets[ $widget_class ]->update( $new_instance, $old_instance );
				$this->save_meta( $this->meta_key, $new_instance, $menu_item_db_id );
			}
		}
	}

	function nav_menu_script() {
		$screen = get_current_screen();
		if( 'nav-menus' !== $screen->base )
			return;

		themify_enque_script( 'themify-widgets-menu-admin',THEMIFY_URI . '/megamenu/js/admin-nav-menu.js',THEMIFY_VERSION, array( 'jquery' ));
		themify_enque_style( 'themify-widgets-menu-admin', THEMIFY_URI . '/megamenu/css/megamenu-admin.css',null,THEMIFY_VERSION );

		do_action( 'themify_widgets_menu_enqueue_admin_scripts' );
		remove_action( 'admin_enqueue_scripts', array( $this, 'nav_menu_script' ), 12 );

		/* fire enqueue events for Widgets Manager in Menus screen */
		do_action( 'sidebar_admin_setup' );
		do_action( 'admin_enqueue_scripts', 'widgets.php' );
		do_action( 'admin_print_styles-widgets.php' );
		do_action( 'admin_print_scripts-widgets.php' );
	}

	function admin_footer() {
		do_action( 'admin_footer-widgets.php' );
	}

	/**
	 * Returns an array of all registered widget classes
	 *
	 * @return array
	 */
	function get_widget_types() {
		global $wp_widget_factory;

		return array_keys( (array) $wp_widget_factory->widgets );
	}

	/**
	 * Checks if the menu item is of "widget" type and returns the widget's base class name
	 *
	 * @return mixed string name of the base widget class, false otherwise
	 */
	function get_widget_menu_class( $item ) {
		$type = ltrim( $item->url, '#' );
		return in_array( $type, $this->get_widget_types(),true )?$type:false;
	}

	function is_menu_widget( $item ) {
		return $this->get_widget_menu_class( $item )?true:false;
	}

	/**
	 * Generates the widget form required for widget menu types
	 *
	 * @since 1.0
	 * @return string
	 */
	function get_widget_form( $widget, $item_id, $item ) {
		global $wp_widget_factory;

		$options = $this->get_widget_options( $item_id );

		ob_start();
		$wp_widget_factory->widgets[$widget]->form( $options );
		do_action_ref_array( 'in_widget_form', array( $wp_widget_factory->widgets[$widget], null, $options ) );
		$form = ob_get_clean();
		$base_name = 'widget-' . $wp_widget_factory->widgets[$widget]->id_base . '\[' . $wp_widget_factory->widgets[$widget]->number . '\]';
		$form = preg_replace( "/{$base_name}/", 'menu-item-widget-options['. $item_id .']', $form );

		return $form;
	}

	/**
	 * Returns saved options for a widget menu type
	 *
	 * @since 1.0
	 * @return array
	 */
	function get_widget_options( $item_id ) {
		$options = get_post_meta( $item_id, $this->meta_key, true );
		if( ! is_array( $options ) ) {
			$options = array();
		}

		return $options;
	}

	/**
	 * Helper method that performs proper action on a metadata based on user input
	 *
	 * @since 1.0
	 */
	public function save_meta( $meta_key, $new_meta_value, $post_id = null ) {
		global $post;

		if( ! $post_id )
			$post_id = $post->ID;
		$meta_value = get_post_meta( $post_id, $meta_key, true );
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );
	}

	/**
	 * Renders a widget using the provided options
	 *
	 * @since 1.0
	 * @return string
	 */
	function render_widget( $widget, $instance, $args = array() ) {
		ob_start();
		the_widget( $widget, $instance, apply_filters( 'themify_widget_menu_args', $args ) );
		return ob_get_clean();
	}
}
Themify_Widgets_Menu::get_instance();


/**
 * Allow Layout Part post type in navigation menus
 *
 * @return array
 * @since 3.3.5
 */
function themify_allow_layout_parts_in_nav_menus( $args, $name ) {
	if ( 'tbuilder_layout_part' === $name ) {
		$args['show_in_nav_menus'] = true;
	}

	return $args;
}
// add_filter( 'register_post_type_args', 'themify_allow_layout_parts_in_nav_menus', 10, 2 );
