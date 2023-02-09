<?php
/**
* This file defines Global Styles
*
* Themify_Global_Styles class register post type for Global Styles and load them
*
*
* @package    Themify_Builder
* @subpackage Themify_Builder/classes
*/

if ( ! class_exists( 'Themify_Global_Styles' ) ) :
/**
 * The Global Styles class.
 *
 * This class register post type for Global Styles and load them.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
final class Themify_Global_Styles{
	
	
	const SLUG= 'tglobal_style';

	/**
	 * Post Type Global Styles Object.
	 *
	 * @access private
	 * @var object $used_styles .
	 */
	public static $used_styles = array();


	/**
	 * Constructor
	 *
	 * @access public
	 */
	
	public static $isGlobalEditPage=false;
	
	public static function init() {
		self::register_global_style();
		if ( is_admin() ) {
			add_filter( 'themify_post_types', array( __CLASS__, 'extend_post_types' ) );
			add_filter( 'themify_exclude_CPT_for_sidebar', array( __CLASS__, 'extend_post_types' ) );
			add_action( 'wp_ajax_tb_save_custom_global_style', array( __CLASS__, 'save_custom_global_style_ajaxify' ), 10 );
			add_action( 'wp_ajax_tb_delete_global_style', array( __CLASS__, 'delete_global_style_ajaxify' ), 10 );
			add_action( 'wp_ajax_tb_restore_global_style', array( __CLASS__, 'restore_global_style_ajaxify' ), 10 );
			add_filter( 'themify_builder_post_types_support', array( __CLASS__, 'add_builder_support' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ),25 );
			add_action( 'wp_ajax_tb_save_as_new_global_style', array( __CLASS__, 'save_as_new_ajax' ), 10 );
			add_action( 'wp_ajax_tb_update_global_style', array( __CLASS__, 'update_live' ), 10 );
			add_action( 'wp_ajax_tb_get_gs_posts', array( __CLASS__, 'get_posts_ajax' ), 10 );
			add_action( 'wp_ajax_tb_import_gs_posts_ajax', array( __CLASS__, 'import_posts_ajax' ), 10 );
			add_action( 'wp_ajax_tb_get_gs_post', array( __CLASS__, 'wp_ajax_tb_get_gs_post' ) );
		}
		else{
			add_filter( 'template_include', array( __CLASS__, 'template_singular_global_style' ) );
		}
                add_filter( 'themify_builder_active_vars', array( __CLASS__, 'localize_data' ) );
	}

	/**
	 * Register Global Style Custom Post Type
	 *
	 * @access static
	 */
	private static function register_global_style() {
		if ( !class_exists( 'CPT' ) ) {
			include THEMIFY_DIR . '/CPT.php';
		}

		// create a template custom post type
		$cpt = new CPT( array(
			'post_type_name' => self::SLUG,
			'singular' => __( 'Global Style', 'themify' ),
			'plural' => __( 'Global Styles', 'themify' )
		), array(
			'supports' => array( 'title' ),
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'public' => true,
			'has_archive' => false
		) );

		// define the columns to appear on the admin edit screen
		$cpt->columns( array(
			'title' => __( 'Title', 'themify' )
		) );

		// use "pages" icon for post type
		$cpt->menu_icon( 'dashicons-admin-page' );
	}

	/**
	 * Includes this custom post to array of cpts managed by Themify
	 *
	 * @access static
	 * @param array $types
	 * @return array
	 */
	static function extend_post_types( $types ) {
		unset($types[self::SLUG]);
		$types[]=self::SLUG;
		return $types;
	}

	/**
	 * Global Style for Template (Editor).
	 *
	 * @access static
	 * @param stirng $original_template
	 * @return string
	 */
	static function template_singular_global_style( $original_template ) {
		if ( is_singular( self::SLUG) ) {
		    self::$isGlobalEditPage=true;
		    $original_template= THEMIFY_BUILDER_TEMPLATES_DIR . '/template-builder-editor.php';
		}
		return $original_template;
	}

	/**
	 * Handle Ajax request for add new Global Style
	 *
	 * @access static
	 */
	static function save_custom_global_style_ajaxify() {

		check_ajax_referer( 'tf_nonce', 'nonce' );
		$data = $response = array();
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $data );
			$insert_post = self::add_new( $data );
			if ( false === $insert_post ) {
				$response['status'] = 'failed';
				$response['msg'] = __( 'Something went wrong', 'themify' );
			} else {
				$response['status'] = 'success';
				$response['url'] = $insert_post['url'] . '#builder_active';
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Handle Ajax request for save as new global style from a module
	 *
	 * @access static
	 */
	static function save_as_new_ajax() {

		check_ajax_referer( 'tf_nonce', 'nonce' );
		if ( empty( $_POST['title'] ) || empty( $_POST['type'] ) || empty( $_POST['styles'] ) ) {
			return false;
		}
		$args = array(
			'style-name' => $_POST['title'],
			'style-type' => $_POST['type'],
			'styles' => json_decode(stripslashes_deep($_POST['styles']),true)
		);
		$insert_post = self::add_new( $args );
		if ( false === $insert_post ) {
			$response['status'] = 'failed';
			$response['msg'] = __( 'Something went wrong', 'themify' );
		} else {
			$response['status'] = 'success';
			$response['post_data'] = $insert_post;
			$response['msg'] = __( 'Global Style has been saved!', 'themify' );
		}
		wp_send_json( $response );
	}

	/**
	 * Handle Ajax request for updating a global style
	 *
	 * @access static
	 */
	static function update_live() {
		if ( empty( $_POST['bid'] ) || empty( $_POST['data'] ) ) {
			return false;
		}
		check_ajax_referer( 'tf_nonce', 'nonce' );
		ThemifyBuilder_Data_Manager::save_data( stripslashes_deep($_POST['data']), $_POST['bid'] );
		$response['status'] = 'success';
		wp_send_json( $response );
	}

	/**
	 * Handle Ajax request for get global style posts
	 *
	 * @access static
	 */
	static function get_posts_ajax() {

		check_ajax_referer( 'tf_nonce', 'nonce' );
		$loaded = !empty( $_POST['loaded'] ) ? $_POST['loaded'] : array();
		$args = array(
			'limit' => 10,
			'data' => true,
			'order'=>'ASC',
			'orderby'=>'title',
			'loaded' => $loaded
		);
		if(!empty($_POST['s'])){
			$args['limit']=50;
			$args['search']=sanitize_text_field($_POST['s']);
		}
		$globalStyles = self::get_global_styles( $args );
		wp_send_json( $globalStyles );
	}

	/**
	 * Insert new Global Style
	 *
	 * @access static
	 * @param array $args
	 * @return Mixed return false if fails or array of info about inserted post if success
	 */
	public static function add_new( $args,$action = 'new' ) {

		if ( empty( $args['style-name'] ) ) {
			return false;
		}
		$name = sanitize_text_field( $args['style-name'] );
		$type = sanitize_text_field( $args['style-type'] );
		if ( $type === 'subcolumn' ) {
			$type = 'column';
		} else if ( $type === 'subrow' ) {
			$type = 'row';
		}
		$module_type = 'row' === $type || 'column' === $type || 'subrow' === $type? 'text' : $type;
		$module = isset( Themify_Builder_Model::$modules[ $module_type ] ) ? Themify_Builder_Model::$modules[ $module_type ] : null;
		$rowStyling = $colStyling = $moduleStyling = '{}';
		if ( $module===null ) {
			$modules = Themify_Builder_Model::get_modules();
                        Themify_Builder_Component_Module::load_modules($module_type);
			$module = Themify_Builder_Model::$modules[ $module_type ];
		}
		$default = $module->get_live_default();
		$default = is_array( $default ) ? $default : array();
		if ( isset( $default['content_text'] ) && 'text' === $module_type ) {
			$default['content_text'] = '<h1>Heading 1</h1><h2>Heading 2</h2><h3>Heading 3</h3><h4>Heading 4</h4><p>This is a sample Text module.</p>';
		}
		if ( !empty( $args['styles'] ) ) {
         
                    if(is_array($args['styles']) && !empty($args['styles'][0])){
                        $args['styles'] = $args['styles'][0];
                        if ($type !== 'row' && $type !== 'subrow') {
                            $args['styles'] = !empty($args['styles']['cols'])?$args['styles']['cols'][0]:array();
                        }
                        if ($type === 'column' || $type === 'row' || $type === 'subrow') {
                            $args['styles'] = isset($args['styling'])?$args['styling']:array();
                        } 
                        else {
                            $args['styles'] = !empty($args['styles']['modules'])?$args['styles']['modules'][0]['mod_settings']:array();
                        }
                        if(empty($args['styles'])){
                            return false;
                        }
                    }
			switch ( $type ) {
				case 'subrow':
				case 'row':
				$rowStyling = json_encode( $args['styles'] );
					break;
				case 'column':
				$colStyling = json_encode( $args['styles'] );
					break;
				default:
				$args['styles'] = array_merge( $default, $args['styles'] );
				$moduleStyling = json_encode( $args['styles'] );
			}
		} else {
			$moduleStyling = json_encode( $default );
		}
		if ( 'row' === $type || 'column' === $type || 'subrow' === $type ) {
			$moduleStyling = json_encode( $default );
		}
		$id = uniqid();
		$builder_content = '[{"element_id":"row' . $id . '","styling":' . $rowStyling . ',"cols":[{"element_id":"col' . $id . '","grid_class":"col-full","styling":' . $colStyling . ',"modules":[{"element_id":"mod' . $id . '","mod_name":"' . $module_type . '","mod_settings":' . $moduleStyling . '}]}]}]';
		if('import' === $action){
			$new_id = $args['id'];
			update_post_meta( $new_id, 'themify_global_style_type', $type );
		}else{
		$new_id = wp_insert_post( array(
			'post_status' => 'publish',
			'post_type' => self::SLUG,
			'post_title' => $name,
			'post_name'=>!empty($args['slug'])?$args['slug']:'',
			'meta_input' => array(
				'themify_global_style_type' => $type,
				'hide_page_title' => 'yes'
			),
		) );
		}
		if ( !is_wp_error( $new_id ) ) {
			ThemifyBuilder_Data_Manager::save_data( $builder_content, $new_id );
			$post_slug = !empty( $args['slug'] ) ? $args['slug'] : 'tb_gs' . $new_id . substr( uniqid(), 0, 3 );
			if(empty($args['slug'])){
				$post_slug='tb_gs' . $new_id . substr( uniqid(), 0, 3 );
				wp_update_post( array(
					'ID' => $new_id,
					'post_name' => $post_slug
				) );
			}
			else{
				$post_slug = $args['slug'];
			}
			if('import' === $action){
				$result = array(
						'builder_data'=>ThemifyBuilder_Data_Manager::get_data( $new_id ),
						'gsType'=>$type
				);
			}else{
				$result = array(
				'id' => $new_id,
				'class' => $post_slug,
				'title' => $name,
				'type' => $type,
				'url' => get_post_permalink( $new_id ),
				'data' => ThemifyBuilder_Data_Manager::get_data( $new_id ),
			);
			}
		} else {
			//there was an error in the post insertion,
			$result = false;
		}
		return $result;
	}

	/**
	 * Add Builder support to Global Style post type.
	 * @param array $post_types
	 * @access static
	 * @return array
	 */
	static function add_builder_support( $post_types ) {
		$post_types[self::SLUG] = self::SLUG;

		return $post_types;
	}

	/**
	 * Return Global Styles page content
	 *
	 * @access static
	 * @return String
	 */
	public static function page_content() {
		$page_status = empty( $_GET['status'] ) ? 'publish' : sanitize_text_field( $_GET['status'] );
		$args = array(
			'preview' => true,
			'status' => $page_status,
			'limit' => 20,
			'paged' => isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1
		);
		$search = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}
		$globalStyles = self::get_global_styles( $args );
		if ( !empty( $globalStyles ) ) {
			themify_enque_style( 'themify-builder-style',  THEMIFY_BUILDER_URI . '/css/themify-builder-style.css', null, THEMIFY_VERSION,null,true);
			if ( is_rtl() ) {
				themify_enque_style( 'themify-builder-style-rtl', THEMIFY_BUILDER_URI . '/css/themify-builder-style-rtl.css' , null, THEMIFY_VERSION,null,true);
			}
			Themify_Enqueue_Assets::loadMainScript();
			global $ThemifyBuilder;
			$ThemifyBuilder->footer_js();
			themify_enque_script( 'themify-builder-js',  THEMIFY_BUILDER_URI . '/js/themify.builder.script.js');
		}
		// Enqueue media scripts
		wp_enqueue_media();
		?>
		<div id="tb_admin_gs_container" class="wrap">
			<!-- Just used for admin notice placement-->
			<h2 style="display: none;"></h2>
			<h3 class="page-title"><?php _e( 'Global Styles', 'themify' ); ?></h3>
			<a href="#tb_new_gs_form"
			   class="page-title-action tb_add_new_gs"><?php _e( 'Add new', 'themify' ) ?></a>
			<a href="#" class="page-title-action tb_import_gs"><?php _e( 'Import from file', 'themify' ) ?></a>
			<div class="tb_gs_admin_page_header">
				<div class="tb_gs_post_status">
					<a <?php echo 'publish' === $page_status ? 'class="tb_gs_active_page"' : ''; ?>
							href="<?php echo admin_url( 'admin.php?page=themify-global-styles&status=publish' ); ?>"><?php _e( 'Published', 'themify' ); ?></a>
					<a <?php echo 'trash' === $page_status ? 'class="tb_gs_active_page"' : ''; ?>
							href="<?php echo admin_url( 'admin.php?page=themify-global-styles&status=trash' ); ?>"><?php _e( 'Trash', 'themify' ); ?></a>
				</div>
				<div class="tb_gs_admin_search">
					<form>
						<input type="text" name="s" value="<?php echo ! empty( $search ) ? esc_attr( $search ) : ''; ?>">
						<input type="hidden" name="page" value="themify-global-styles">
						<input type="hidden" name="status" value="<?php echo esc_attr( $page_status ); ?>">
						<button type="submit"><?php _e( 'Search', 'themify' ); ?></button>
					</form>
				</div>
			</div>
			<?php if ( ! empty( $search ) ): ?>
				<h3><?php echo sprintf( __( 'Search Results for %s', 'themify' ), $search ); ?></h3>
			<?php endif; ?>
			<div class="tb_admin_gs_list" data-list="<?php echo esc_attr( $page_status ); ?>">
			<?php if ( !empty( $globalStyles ) ): ?>
				<?php foreach ( $globalStyles as $style ) :
					Themify_Builder_Stylesheet::enqueue_stylesheet( false, $style['id'] );
					?>
					<div class="tb_gs_element">
						<div class="tb_gs_thumbnail_container">
							<span class="tb_admin_gs_type"><?php echo $style['type']; ?></span>
							<a href="#" class="tb_remove_gs tf_close" data-id="<?php echo $style['id']; ?>"></a>
							<?php
							if ( 'publish' === $page_status ): ?>
								<a href="#" target="_blank" class="tb_gs_export tb_export" data-title="<?php echo esc_attr( $style['title'] ); ?>" data-id="<?php echo $style['id']; ?>"><?php echo themify_get_icon('export','ti'); ?></a>
							<?php else: ?>
								<a href="#" class="tb_gs_restore" data-id="<?php echo $style['id']; ?>"><?php echo themify_get_icon('back-left','ti'); ?></a>
							<?php endif; ?>
							<div data-builder="<?php echo esc_url( $style['url'] ); ?>#builder_active"
								 class="tb_gs_preview_container">
								<a href="<?php echo 'publish' === $page_status ? esc_url( $style['url'] ) . '#builder_active' : '#'; ?>"
								   class="tb_gs_preview_overlay"></a>
								<?php echo $style['preview']; ?>
							</div>
						</div>
						<span class="tb_admin_gs_title"><?php echo $style['title']; ?></span>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<p class="wp-heading-inline"><?php _e( 'No Global Styles found.', 'themify' ); ?></p>
			<?php endif; ?>
			</div>
			<?php self::add_new_form(); ?>
			<?php self::pagination(); ?>
		</div>
		<?php
		Themify_Builder_Stylesheet::load_styles();
	}


	/**
	 * Admin page pagination
	 *
	 * @return String
	 */
	private static function pagination() {
		$page_num = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$limit = 20;
		$count_posts = wp_count_posts( self::SLUG );
		if ( !isset( $count_posts->publish ) )
			return;
		$total = $count_posts->publish;
		$num_of_pages = ceil( $total / $limit );
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pagenum', '%#%' ),
			'format' => '',
			'prev_text' => __( '«', 'text-domain' ),
			'next_text' => __( '»', 'text-domain' ),
			'total' => $num_of_pages,
			'current' => $page_num
		) );

		if ( $page_links ) {
			echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
		}
	}

	/**
	 * Create add new Global Style form
	 *
	 * @return String
	 */
	private static function add_new_form() {
		$excludes = array( 'page-break', 'divider', 'widget', 'widgetized', 'layout-part', 'plain-text' );
		?>
		<div id="tb_new_gs_form">
			<div class="tb_gs_form_header">
				<span class="tb_gs_form_title"><?php _e( 'New Style', 'themify' ); ?></span>
			</div>
			<div class="tb_gs_form_body">
				<form id="tb_admin_new_gs">
					<div class="tb_gs_input_container">
						<label for="style-name"><?php _e( 'Style Name', 'themify' ); ?></label>
						<input type="text" id="style-name" name="style-name"/>
					</div>
					<div class="tb_gs_input_container">
						<label for="style-type"><?php _e( 'Type', 'themify' ); ?></label>
						<div class="tb_gs_type_container">
							<select id="style-type" name="style-type">
								<option value="row"><?php _e( 'Row', 'themify' ); ?></option>
								<option value="column"><?php _e( 'Column', 'themify' ); ?></option>
				<?php foreach ( Themify_Builder_Model::$modules as $module ): ?>
				<?php if ( !in_array( $module->slug, $excludes,true ) ): ?>
					<option value="<?php echo esc_attr( $module->slug ); ?>"><?php echo esc_html( $module->name ); ?></option>
				<?php endif;?>
				<?php endforeach; ?>
							</select>
						</div>
					</div>
				</form>
			</div>
			<div class="tb_gs_form_footer">
				<a href="#" class="tb_admin_save_gs"><?php _e( 'CREATE', 'themify' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Get a list of Global Styles
	 *
	 * @param array $args arguments of get posts
	 * @return array
	 */
	public static function get_global_styles( $args ) {
		$limit = empty( $args['limit'] ) ? 10 : $args['limit'];
		$paged = empty( $args['paged'] ) ? '' : $args['paged'];
		$data = empty( $args['data'] ) ? false : $args['data'];
		$exclude = empty( $args['loaded'] ) ? array() : $args['loaded'];
		$include = empty( $args['include'] ) ? array() : $args['include'];
		$preview = empty( $args['preview'] ) ? false : $args['preview'];
		$post_names = empty( $args['post_names'] ) ? array() : $args['post_names'];
		$status = empty( $args['status'] ) ? 'publish' : $args['status'];
		$globalStyles = array();
		$posts_args = array(
			'post_type' => self::SLUG,
			'posts_per_page' => $limit,
			'paged' => $paged,
			'post_status' => $status,
			'orderby' => !empty( $args['orderby'])?$args['orderby']:'date',
			'order' =>!empty( $args['order'])?$args['order']:'DESC',
			'exclude' => $exclude,
			'include' => $include,
			'ignore_sticky_posts'=>true,
			'post_name__in' => $post_names
		);
		if ( !empty( $args['search'] ) ) {
			$posts_args['s'] = $args['search'];
		}
		if(!isset($args['found_rows'])){
			$posts_args['no_found_rows']=true;
		}
		$posts = get_posts( $posts_args );
		$args=null;
		if ( $posts ) {
			global $ThemifyBuilder;
			if ( $preview===true) {
				$isLoop = $ThemifyBuilder->in_the_loop===true;
				$ThemifyBuilder->in_the_loop=true;
			}
			foreach ( $posts as $post ) {
				if ( $preview===true) {
					setup_postdata( $post );
				}
				$post_id = $post->ID;
				$slug=$post->post_name;
				$globalStyles[ $slug ] = array(
					'id' => $post_id,
					'title' =>get_the_title($post_id),
					'type' => get_post_meta( $post_id, 'themify_global_style_type', true ),
					'url' => get_permalink( $post_id )
				);
				if ( $preview===true) {
					$globalStyles[ $slug ]['preview'] = $ThemifyBuilder->get_builder_output( $post_id );
				}
				if ( $data===true) {
					$globalStyles[ $slug ]['data'] = ThemifyBuilder_Data_Manager::get_data( $post_id );
				}
			}
			if ( $preview===true) {
				wp_reset_postdata();
				$ThemifyBuilder->in_the_loop=$isLoop;
			}
		}
		return $globalStyles;
	}

	/**
	 * Enqueue required styles and scripts for Global Styles page
	 *
	 * @param String $page name of the admin page
	 * @return void
	 */
	public static function enqueue_admin_scripts(  ) {
		if ( isset($_GET['page']) && $_GET['page']==='themify-global-styles') {
			themify_enque_style( 'magnific', THEMIFY_URI . '/css/lightbox.css' , null, THEMIFY_VERSION,null,true);
			themify_enque_style( 'themify-global-styles-css', THEMIFY_BUILDER_URI . '/css/editor/themify-global-styles.css' , false, THEMIFY_VERSION,null,true);
			themify_enque_script( 'magnific', THEMIFY_URI . '/js/modules/lightbox.min.js');
			themify_enque_script( 'themify-global-styles-js', THEMIFY_BUILDER_URI . '/js/editor/themify-global-styles.js' );
			wp_localize_script( 'themify-global-styles-js', 'themifyGlobalStylesVars', array(
				'i18n' => array(
					'formValid' => esc_html__( 'Please enter a name.', 'themify' ),
					'create' => esc_html__( 'Create', 'themify' ),
					'creating' => esc_html__( 'Creating ...', 'themify' ),
					'deleteConfirm' => esc_html__( 'Once it is deleted, all styling associated with this Global Style will be gone. This can not be undone.', 'themify' ),
					'deleteConfirm2' => esc_html__( 'Remove permanently, Are you sure?', 'themify' ),
					'missing_file' => esc_html__( 'Global Styles file is missing.', 'themify' ),
					'invalid_file' => esc_html__( 'Global Styles file is invalid.', 'themify' ),
				),
				'nonce' => wp_create_nonce( 'tf_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' )
				)
			);
		}
	}

	/**
	 * Localize some required data about global styles into Builder scripts
	 * This function is a filter for a hook
	 *
	 * @param array $vars
	 * @return array
	 */
	public static function localize_data($vars) {
		// localize current post if post type is global style
		$post_id=is_admin() ? get_the_ID() : Themify_Builder::$builder_active_id;
		if(self::$isGlobalEditPage===true) {
			$vars['globalStyleData']=array(
			    'id'=>$post_id,
			    'class'=>get_post_field('post_name', $post_id),
			    'save_text'=>sprintf(__('Global style %s is saved','themify'),get_the_title($post_id)),
			    'type'=>get_post_meta($post_id, 'themify_global_style_type', true)
			);
		} else {
			// Localize used Global Styles in this post/page
			if(!isset(self::$used_styles[$post_id])) {
				self::$used_styles[$post_id]=self::used_global_styles($post_id);
			}
			$vars['globalStyles']=self::$used_styles[$post_id];
		}
		return $vars;
	}

	/**
	 * Get list of used Global Styles in a post
	 *
	 * @param int $post_id
	 * @return array list of global styles
	 */
	public static function used_global_styles( $post_id ) {
		$usedGlobalStyles = get_post_meta( $post_id, 'themify_used_global_styles', true );
		$globalStyles = array();
		if (!empty( $usedGlobalStyles ) && is_array( $usedGlobalStyles ) ) {
			$args = array(
				'limit' => -1,
				'post_names' => $usedGlobalStyles,
				'data' => true
			);
			$globalStyles = self::get_global_styles( $args );
		}
		return $globalStyles;
	}

	/**
	 * Get list of used Global Styles in a post and extract their styles
	 *
	 * @param int $post_id
	 * @return array list of global styles
	 */
	private static function extract_used_global_styles( $post_id ) {
		if(!isset(self::$used_styles[$post_id])){
			self::$used_styles[$post_id]=self::format(self::used_global_styles( $post_id ));
		}
		return self::$used_styles[$post_id];
	}
	
	private static function format($data){
		$styles = array();
		if ( !empty( $data ) ) {
			foreach ( $data as $k=>$post ) {
				if ( 'row' === $post['type'] || 'subrow' === $post['type'] ) {
					$styles[ $k ] = $post['data'][0]['styling'];
				} 
				elseif ( 'column' === $post['type'] ) {
					$styles[ $k ] = $post['data'][0]['cols'][0]['styling'];
				}
				// Temporary prevent to load unnecessary data
				/* else {
					$styles[$post['class']] = $post['data'][0]['cols'][0]['modules'][0]['mod_settings'];
				}*/
			}
		}
		return $styles;
	}

	/**
	 * Add Global Style Breadcrumb below of the toolbar
	 *
	 * @return String
	 */
	public static function breadcrumb() {
		if (is_singular(self::SLUG)) {
			$post_id = Themify_Builder_Model::get_ID();
			$title = get_the_title( $post_id );
			$type = get_post_meta( $post_id, 'themify_global_style_type', true );
			?>
			<div class="global-styles-breadcrumb">
			<span class="title"><a
					href="<?php echo esc_url( admin_url( 'admin.php?page=themify-global-styles' ) ); ?>"><?php _e( 'Global Styles', 'themify' ); ?></a></span>
			<span class="arrow">></span>
			<span class="title"><?php echo esc_html( strtoupper( $type ) ); ?></span>
			<span class="arrow">></span>
			<span class="title"><?php echo esc_html( $title ); ?></span>
			</div>
		<?php
		}
	}


	/**
	 * Save used global styles as post meta or trigger global style update function if post type is GS
	 *
	 * @param Int $post_id
	 * @param string $builder_data
	 */
	public static function save_used_global_styles( $builder_data, $post_id ) {
		// Update all linked posts if current post type is Global Style
		if ( self::SLUG === get_post_type( $post_id ) ) {
			self::global_style_updated( $post_id );
		} else {
			$global_styles = self::find_in_text( $builder_data );	
			$is_empty = count( $global_styles ) <= 1 && empty( $global_styles[0] );
			if ( ! $is_empty ) {
			update_post_meta( $post_id, 'themify_used_global_styles', $global_styles );
			} else {
				delete_post_meta( $post_id, 'themify_used_global_styles' );
		}
	}
	}

	/**
	 * find gs classes in builder data
	 *
	 * @param string $builder_data
	 * @return array used gs
	 */
	private static function find_in_text( $builder_data ) {
		$global_styles = array();
		if(is_array($builder_data)){
			$builder_data=json_encode($builder_data);
		}
		$builder_data = stripcslashes( $builder_data );
		preg_match_all( '/"global_styles":"(.*?)"/m', $builder_data, $matches );
		if ( !empty( $matches ) ) {
			$styles = explode( ' ', implode( ' ', $matches[1]  ));
			$global_styles = array_unique( $styles );
		}
		return $global_styles;
	}


	/**
	 * Updates all linked posts to a Global Style that has updated
	 * (Remove generated CSS files)
	 *
	 * @param Int $post_id
	 */
	private static function global_style_updated( $post_id ) {
		$posts = self::get_linked_posts( $post_id );
		if ( !empty( $posts ) ) {
			$themify_css_dir = Themify_Builder_Stylesheet::get_stylesheet_dir();
			foreach ( $posts as $linked_post_id ) {
				$file = $themify_css_dir . '/themify-builder-' . $linked_post_id . '-generated.css';
				if (Themify_Filesystem::is_file($file)) {
					Themify_Filesystem::delete($file,'f');
				}
			}
		}
	}

	/**
	 * Handle Ajax request for deleting a Global Style
	 */
	public static function delete_global_style_ajaxify() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		$response = array();
		if ( isset( $_POST['id'] ) ) {
			$force = 'publish' === $_POST['status'] ? false : true;
			$delete_post = self::delete_global_style( $_POST['id'], $force );
			if ( false === $delete_post ) {
				$response['status'] = 'failed';
				$response['msg'] = __( 'Something went wrong', 'themify' );
			} else {
				$response['status'] = 'success';
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Handle Ajax request for restoring a Global Style
	 */
	public static function restore_global_style_ajaxify() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		$response = array();
		if ( isset( $_POST['id'] ) ) {
			$restore_post = self::restore_global_style( $_POST['id'] );
			if ( false === $restore_post || null === $restore_post ) {
				$response['status'] = 'failed';
				$response['msg'] = __( 'Something went wrong', 'themify' );
			} else {
				$response['status'] = 'success';
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Delete a Global Style and update all linked posts to that Global Style
	 * (Remove Global Styles CSS from linked posts)
	 *
	 * @param Int $post_id Global Style post ID
	 * @param bool $force Global Style post ID
	 * @return Bool
	 */
	private static  function delete_global_style( $post_id, $force = false ) {
		if ( !$force ) {
			self::global_style_updated( $post_id );
		}
		return $force?wp_delete_post( $post_id, true ):wp_trash_post( $post_id );
	}

	/**
	 * Restore a Global Style
	 *
	 * @param Int $post_id Global Style post ID
	 * @return Bool
	 */
	private static  function restore_global_style( $post_id ) {
		return wp_untrash_post( $post_id )?true:false;
	}

	/**
	 * Get list of posts ID as an array, that use a specific Global Style
	 *
	 * @param integer $global_style_id
	 * @return array list of posts ID
	 */
	private static function get_linked_posts( $global_style_id ) {
		$posts = array();
		$class = get_post_field( 'post_name', $global_style_id );
		$args = array(
			'post_type' => themify_post_types(),
			'posts_per_page' => -1,
			'ignore_sticky_posts'=>true,
			'meta_query' => array(
				array(
					'key' => 'themify_used_global_styles',
					'value' => $class,
					'compare' => 'LIKE',
				)
			)
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$posts[] = get_the_ID();
			}
		}
		wp_reset_query();
		return $posts;
	}

	/**
	 * Handle Ajax request for import global style posts (Copy & Paste Module)
	 *
	 * @access public
	 */
	public static function import_posts_ajax() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		if ( isset( $_POST['data'] ) ) {
		    $data = stripslashes_deep( $_POST['data'] );
		} 
		elseif ( isset( $_FILES['data'] ) ) {
		    $data = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		$data = ! empty( $data ) ? json_decode( $data, true ) : array();
		$globalStyles = self::builder_import( $data, ! empty( $_POST['onlySave'] ) );
		wp_send_json( $globalStyles );
	}

	/**
	 * Import GS attached file after builder export
	 *
	 * @param array $data exported gs attached file data
	 * @return array
	 */
	public static function builder_import( $data,$onlySave=false) {
		$data = is_object( $data ) ? json_decode( json_encode( $data ), true ) : $data;
		$used_gs = $exist_posts = array();
		if ( is_array( $data ) && !empty( $data ) ) {
			foreach ( $data as $slug=>$post ) {
				$post_id = get_page_by_path( $slug, OBJECT, self::SLUG );
				if ( is_null( $post_id ) ) {
					$args = array(
						'style-name' => $post['title'], 
						'style-type' => $post['type'], 
						'styles' => $post['data'],
						'slug' => $slug
					);
					if ( $gsPost = self::add_new( $args ) ) {
						$used_gs[$slug] = $gsPost;
					}
				} elseif($onlySave===false){
					$exist_posts[] = $post_id->ID;
				}
			}
			if ( $onlySave===false && !empty( $exist_posts )) {
				$args = array(
					'limit' => -1, 
					'data' => true, 
					'include' => $exist_posts
				);
				$exist_posts = self::get_global_styles( $args );
				if ( !empty( $exist_posts ) ) {
					$used_gs = array_merge( $used_gs, $exist_posts );
				}
			}
		}
		return $used_gs;
	}

	/**
	 * Export GS file
	 *
	 * @return bool
	 */
	public static function wp_ajax_tb_get_gs_post() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		$id = (int) $_POST['id'];
		$post = self::get_global_styles( array( 'include' => array( $id ), 'data' => true ) );
		if ( empty( $post ) ) {
			return false;
			exit();
		}
		$post = current($post);
		unset( $post['id'],$post['url']  );
		$styling = Themify_Builder_Import_Export::prepare_builder_data( $post['data'] );
		$styling = $styling[0];
		if ( $post['type'] === 'row' || $post['type'] === 'subrow') {
			$styling = $styling['styling'];
		} elseif ( $post['type'] === 'column' ) {
			$styling = $styling['cols'][0]['styling'];
		} else {
			$styling = $styling['cols'][0]['modules'][0]['mod_settings'];
		}
		$post['data'] = $styling;
		echo json_encode( [ $id => $post ], true );
		exit();
	}

	/**
	 * Add used Global Styles and extra classes to (Row/Sub Row/Column/Sub Column) container
	 * It's a filter function
	 *
	 * @param array $class
	 * @param array $styling
	 * @param int $builder_id
	 * @return array
	 */
	public static function add_class_to_components( $class, $styling, $builder_id ) {
		$class[]= $styling['global_styles'];	
		if(empty($styling['background_repeat']) || empty($styling['background_image'])){
			// Attach Background Repeat Class (Parallax or Zoom scrolling class)
			$parallax =!in_array('builder-parallax-scrolling',$class,true )?'builder-parallax-scrolling':false;
	
			$zoom = !in_array( 'builder-zoom-scrolling',$class,true )?'builder-zoom-scrolling':false;
			if ( $parallax!==false || $zoom!==false ) {
				$gs_styles = self::get_used_gs($builder_id);	
				if(!empty($gs_styles)){
					$hasImage=!empty($styling['background_image']);
					$used_gs = explode( ' ', trim( $styling['global_styles'] ) );
					foreach ( $used_gs as $slug ) {
						if($hasImage===true || !empty( $gs_styles[ $slug ]['background_image'])){
							$value = !empty( $gs_styles[ $slug ]['background_repeat'] ) ? $gs_styles[ $slug ]['background_repeat'] : null;
							if ($parallax!==false && $value === $parallax ) {
								$class[]= $parallax;
								$parallax = false;
							}
							elseif ($zoom!==false && $value === $zoom) {
								$class[]= $zoom;
								$zoom = false;
							}
							if ( $parallax===false && $zoom===false ) {
								break;
							}
						}
					}
				}
			}
		}
		return $class;
	}

	// Set Used GS Styles
	public static function get_used_gs($post_id){
		if ( self::$isGlobalEditPage===true) {
			return false;
		}
		return self::extract_used_global_styles( $post_id );
	}

	public static function addGS($post_id,$data){
		$usedGs=self::used_global_styles($post_id);
		if(!empty($data)){
			foreach($data as $k=>$v){
				if(!isset($usedGs[$k])){
					$usedGs[$k]=$v;
				}
			}
		}
		return self::format($usedGs);
	}
}
endif;