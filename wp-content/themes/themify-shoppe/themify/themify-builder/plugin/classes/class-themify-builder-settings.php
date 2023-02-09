<?php

if ( ! class_exists( 'Themify_Builder_Options' ) ) :
/**
 * Class Builder Options
 */
class Themify_Builder_Options {

	const KEY = 'themify_builder_setting';
	const SLUG = 'themify-builder';

	/**
	 * Constructor
	 */
	public static function init() {
		if ( is_admin() ){
			add_action( 'admin_menu', array( __CLASS__, 'add_plugin_page' ) );
			add_action('wp_ajax_themify_builder_settings_save',array(__CLASS__,'save'));
		}
		else{
			add_action( 'wp_head', array( __CLASS__, 'show_custom_css' ) );
		}
	}

	public static function add_plugin_page(){
	    if( Themify_Access_Role::check_access_backend() ){  
		$can_manage_option = current_user_can( 'manage_options' );
		    // This page will be under "Settings"
		$name = __( 'Themify Builder', 'themify' );
		add_menu_page( $name, $name, 'edit_posts', self::SLUG,$can_manage_option?array( __CLASS__, 'create_admin_page'):'' ,"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='%23ffcc08' viewBox='0 0 32 32'%3E%3Cpath d='M11.5 3.5c15.8-5 21.8 9 20.3 17.3-.9 4.5-10 5.7-18 6.5-6.9.7-12.1 2.3-12.1 2.3s6.2-4.7 2.1-6.8C1.4 21.6-6.6 9.1 11.5 3.5zm7.3 6c-1 0-1.9.8-1.9 1.8a2 2 0 0 0 2 1.9c1 0 1.8-.9 1.8-1.9 0-1-.8-1.9-1.9-1.9zm-9.4.9a1.9 1.9 0 0 0-1.9 1.9 2 2 0 0 0 2 1.8 2 2 0 0 0 1.8-1.8c0-1.1-.9-2-1.9-2zM7 17.8s7.7 9.8 18.2-1.8c0 0-12.1 7.2-18.2 1.8z'%3E%3C/path%3E%3C/svg%3E", 50 );
		if($can_manage_option){
		    add_submenu_page( self::SLUG, __( 'Settings', 'themify' ), __( 'Settings', 'themify' ), 'manage_options', self::SLUG );
		}

		add_submenu_page ( 'themify-builder', __( 'Saved Layouts', 'themify' ), __( 'Saved Layouts', 'themify' ), 'edit_posts', 'edit.php?post_type='.Themify_Builder_Layouts::LAYOUT_SLUG );
		add_submenu_page( 'themify-builder', __( 'Layout Parts', 'themify' ), __( 'Layout Parts', 'themify' ), 'edit_posts', 'edit.php?post_type='.Themify_Builder_Layouts::LAYOUT_PART_SLUG );
		add_submenu_page ( 'themify-builder', __( 'Global Styles', 'themify' ), __( 'Global Styles', 'themify' ), 'edit_posts', 'themify-global-styles', array( __CLASS__, 'global_styles_page' ) );
		add_submenu_page( 'themify-builder', __( 'Custom Fonts', 'themify' ), __( 'Custom Fonts', 'themify' ), 'edit_posts', 'edit.php?post_type='.Themify_Custom_Fonts::SLUG );

		if(!$can_manage_option){
		    remove_submenu_page(self::SLUG,self::SLUG);
		}
	    }
	}
	
	public static function save(){
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(current_user_can( 'manage_options' ) && Themify_Access_Role::check_access_backend() ){
		if ( isset( $_POST['data'] ) ) {
		    $data = stripslashes_deep( $_POST['data'] );
		} 
		elseif ( isset( $_FILES['data'] ) ) {
		    $data = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(isset($data)){//don't use empty, when builder is empty need to remove data
		    $results = array();
		    $exist_data = get_option( self::KEY );
		    $isExist=$exist_data!==false;
		    if(empty($exist_data)){
			$exist_data=array();
		    }
		    if(!empty($data)){		  
			$data=json_decode( $data, true );				
			foreach($data as $k=>$v){
			    if($v==='' || $v==='default'){
				unset($data[$k]);
			    } 
			}
			$success=maybe_serialize( $exist_data ) === maybe_serialize( $data )?true:update_option(self::KEY, $data);
		    }
		    else{
			$success=$isExist===true?delete_option(self::KEY):true;
			$data=array();
		    }
		    if($success===true){
			Themify_Enqueue_Assets::rewrite_htaccess( empty( $data['performance-cache_gzip'] ), empty( $data['performance-webp'] ), empty( $data['performance-cache_browser'] ) );
			foreach ( array( 'tablet_landscape', 'tablet', 'mobile' ) as $breakpoint ) {
			    if ( isset( $data["builder_responsive_design_{$breakpoint}"], $exist_data["builder_responsive_design_{$breakpoint}"] ) && $data["builder_responsive_design_{$breakpoint}"] !== $exist_data["builder_responsive_design_{$breakpoint}"] ) {
				Themify_Builder_Stylesheet::regenerate_css_files();
				break;
			    }
			}
		    }
		    wp_send_json_success();
		}
	    }
	    wp_send_json_error();
	}

	/**
	 * Display Builder Styles page content
	 * @return String
	 * @since 4.5.0
	 */
	public static function global_styles_page(){
	    if ( ! current_user_can( 'edit_posts' ) ){
		    wp_die( __( 'You do not have sufficient permissions to update this site.', 'themify' ) );
	    }

	    return Themify_Global_Styles::page_content();
	}
	
	private static function get_tab_settings(){
	    Themify_Builder_Component_Module::load_modules('all');	
	    $responsive=$disableModules=$feature_sizes=$disablePosts=array();
	    $defBreakPoints=array(
		'tablet_landscape'=>1024,
		'tablet'=>768,
		'mobile'=>600
	    );
	    foreach(themify_get_breakpoints('',true) as $bp=>$val){
		$label=$bp === 'tablet_landscape' ? 'Tablet Landscape' : ($bp === 'tablet' ? 'Tablet Portrait' : ucfirst($bp));
		$responsive[]=array(
		    'type'=>'slider',
		    'id'=>'builder_responsive_design_'.$bp,
		    'label'=> sprintf(__('%s','themify'),$label),
		    'def'=>$defBreakPoints[$bp],
		    'min'=> is_array($val)?$val[0]:320,
		    'max'=> is_array($val)?$val[1]:$val
		);
	    }
	    foreach(Themify_Builder_Model::$modules as $k=>$m){
		$disableModules[]=array(
		    'label'=>sprintf(__('"%s" module', 'themify'), $m->get_name()),
		    'id'=>'builder_exclude_module_'.$k,
		    'opp'=>1,
		    'type'=>'toggle'
		);
	    }
	    foreach(themify_get_image_sizes_list() as $opt){
		$feature_sizes[$opt['value']]=$opt['name'];
	    }
	    $excludes =array(Themify_Builder_Layouts::LAYOUT_PART_SLUG, Themify_Builder_Layouts::LAYOUT_SLUG,Themify_Global_Styles::SLUG);
	    $globalGutters= Themify_Builder_Model::get_gutters();
	    foreach( $GLOBALS['ThemifyBuilder']->builder_post_types_support() as $v ) {
		if(!in_array($v,$excludes,true)){
		    $disablePosts[]=array(
			'label'=>sprintf(__('"%s" type', 'themify'), $v),
			'id'=>'builder_disable_tb_'.$v,
			'opp'=>1,
			'type'=>'toggle'
		    );
		}
	    }
	    unset($excludes,$defBreakPoints);
	    $imageLibrary=_wp_image_editor_choose()!==false;
	    return array(
		    array(
			'type'=>'group',
			'label'=>__('Gutter Size','themify'),
			'options'=>array(
			    array(
				'type'=>'number',
				'step'=>.1,
				'id'=>'setting-gutter',
				'def'=>$globalGutters['gutter'],
				'after'=>__('Normal gutter (%)','themify')
			    ),
			    array(
				'type'=>'number',
				'step'=>.1,
				'id'=>'setting-narrow',
				'def'=>$globalGutters['narrow'],
				'after'=>__('Narrow gutter (%)','themify')
			    ),
			    array(
				'type'=>'number',
				'step'=>.1,
				'id'=>'setting-none',
				'def'=>$globalGutters['none'],
				'after'=>__('None gutter (%)','themify')
			    ),
			)
		    ),
		    array(
			'label'=>__( 'Gallery Module Lightbox', 'themify' ),
			'type'=>'toggle',
			'id'=>'builder_lightbox',
			'value'=>'disable',
			'opp'=>1
		    ),
		    array(
			'label'=>__( 'Keyboard Shortcuts', 'themify' ),
			'type'=>'toggle',
			'id'=>'builder_disable_shortcuts',
			'opp'=>1,
			'help'=>__('Builder shortcuts (eg. disable shortcut like Cmd+S = save)','themify'),
		    ),
		    array(
			'label'=>__( 'WordPress Classic Editor', 'themify' ),
			'type'=>'toggle',
			'id'=>'builder_disable_wp_editor',
			'opp'=>1,
			'help'=>__('Enable/disable WordPress Classic Editor when Builder is in use','themify')
		    ),
		    array(
			'label'=>__( 'Google Fonts List', 'themify' ),
			'type'=>'radio',
			'id'=>'builder_google_fonts',
			'def'=>'less',
			'options'=>array(
			    'less'=>__( 'Show recommended Google Fonts only', 'themify' ),
			    'full'=>__( 'Show all Google Fonts (showing all fonts will take longer to load)', 'themify' ),
			)
		    ),
		    array(
			'type'=>'group',
			'label'=>__( 'Download Google Fonts', 'themify' ),
			'options'=>array(
			    array(
				'type'=>'toggle',
				'id'=>'setting-gf',
				'desc'=>__('Downloads all Google Fonts used in the Builder to your local server. Note: Google Maps, YouTube and any embeds loaded in iframe are excluded as they are loaded in the iframe.','themify'),
			    ),
			    array(
				'type'=>'clear_cache',
				'action'=>'themify_clear_gfonts',
				'text'=>__('Clear Google Fonts Cache','themify'),
				'clearing'=>__('Clearing...','themify'),
				'done'=>__('Done','themify')
			    ),
			)
		    ),
		    array(
			'type'=>'group',
			'label'=>__('Animation Effects','themify'),
			'options'=>array(
			    array(
				'label'=>__( 'Appearance Animation', 'themify' ),
				'type'=>'select',
				'id'=>'builder_animation_appearance',
				'options'=>array(
				    ''=>'',
				    'mobile'=>__('Disable on mobile & tablet','themify'),
				    'all'=>__('Disable on all devices','themify')
				)
			    ),
			    array(
				'label'=>__( 'Parallax Background', 'themify' ),
				'type'=>'select',
				'id'=>'builder_animation_parallax_bg',
				'options'=>array(
				    ''=>'',
				    'mobile'=>__('Disable on mobile & tablet','themify'),
				    'all'=>__('Disable on all devices','themify')
				)
			    ),
			    array(
				'label'=>__( 'Scroll Effects', 'themify' ),
				'type'=>'select',
				'id'=>'builder_animation_scroll_effect',
				'options'=>array(
				    ''=>'',
				    'mobile'=>__('Disable on mobile & tablet','themify'),
				    'all'=>__('Disable on all devices','themify')
				)
			    ),
			)
		    ),
		    array(
			'type'=>'group',
			'label'=>__('Responsive Breakpoints','themify'),
			'options'=>$responsive
		    ),
		    array(
			'type'=>'number',
			'id'=>'builder_scrollTo',
			'label'=>__('ScrollTo Offset','themify'),
			'after'=>'px',
			'help'=>__('Enter the top position where it should scrollTo','themify')
		    ),
		    array(
			'type'=>'number',
			'id'=>'builder_scrollTo_speed',
			'label'=>__('ScrollTo Speed','themify'),
			'after'=>'Seconds',
			'step'=>.1,
			'help'=>__('Speed of scrolling animation. Default: 0.9 second','themify')
		    ),
		    array(
			'label'=>__('Image Script','themify'),
			'type'=>'toggle',
			'id'=>'image_setting-img_settings_use',
			'opp'=>1,
			'help'=>__('Image script crops the images to the entered size. If disabled, WordPress Featured Image or original images will be used.','themify'),
			'disabled'=>$imageLibrary?'': sprintf( __( 'This feature requires an <a href="%s" target="_blank">image processing library</a> to be installed on the server. Please contact your hosting provider to enable this.', 'themify' ), 'https://www.php.net/manual/en/refs.utilspec.image.php' ),
			'bind'=>array(
			    'checked' => array(
				'show' => 'featured_size'
			    ),
			    'not_checked' => array(
				'hide' =>'featured_size'
			    )
			)
		    ),
		    array(
			'label'=>__('Default Featured Image Size','themify'),
			'type'=>'select',
			'wrap_class'=>$imageLibrary?'featured_size':'',
			'id'=>'image_global_size_field',
			'options'=>$feature_sizes
		    ),
		    array(
			'label'=>__('Builder For Post Types','themify'),
			'type'=>'group',
			'options'=>$disablePosts
		    ),
		    array(
			'label'=>__('Builder Modules','themify'),
			'type'=>'group',
			'options'=>$disableModules
		    )
		);
	}
	
	private static function get_tab_performance(){
	    $htaccess_file = Themify_Enqueue_Assets::getHtaccessFile();
	    $htaccess_msg=Themify_Filesystem::is_file( $htaccess_file ) && Themify_Filesystem::is_writable( $htaccess_file )?'':sprintf(__( 'The htaccess file %s isn`t writable. Please allow to write to enable this feauture','themify' ),$htaccess_file);
	    $imageLibrary=_wp_image_editor_choose()!==false;
	    return array(
		    array(
			'type'=>'group',
			'label'=>__( 'Lazy Load', 'themify' ),
			'options'=>array(
			    array(
				'label'=>__( 'Themify Lazy Load', 'themify' ),
				'type'=>'toggle',
				'id'=>'performance-disable-lazy',
				'opp'=>1
			    ),
			    array(
				'label'=>__( 'Use native lazy load', 'themify' ),
				'type'=>'toggle',
				'id'=>'performance-disable-lazy-native',
			    )
			)
		    ),
		    array(
			'label'=>__( 'Minified Scripts', 'themify' ),
			'type'=>'toggle',
			'id'=>'performance-script_minification-min',
			'help'=>__('Disable minified scripts (css/js files)','themify'),
			'opp'=>1,
		    ),
		    array(
			'label'=>__( 'Browser Caching', 'themify' ),
			'type'=>'toggle',
			'help'=>__("Cache static assets (CSS, JS, images, etc.) on user's browser. HTML is not cached",'themify'),
			'disabled'=>$htaccess_msg,
			'id'=>'performance-cache_browser',
		    ),
		    array(
			'label'=>__( 'Gzip Scripts', 'themify' ),
			'type'=>'toggle',
			'id'=>'performance-cache_gzip',
			'disabled'=>$htaccess_msg,
			'desc'=>$htaccess_msg===''?sprintf(__('Enabling Gzip will add code to your .htaccess file %s','themify'),$htaccess_file):'',
		    ),
		    array(
			'label'=>__( 'Enable jQuery Migrate', 'themify' ),
			'type'=>'toggle',
			'id'=>'performance-jquery_migrate',
			'help'=>__( 'Enable this option if you have plugins that use deprecated jQuery versions.','themify' )
		    ),
		    array(
			'label'=>__( 'WebP Images', 'themify' ),
			'type'=>'toggle',
			'id'=>'performance-webp',
			'help'=>__('Enable WebP image (recommended)','themify'),
			'disabled'=>$imageLibrary?'': __( 'The GD library or Imagick extensions are not installed. Ask your host provider to enable them to use this feature.', 'themify' ),
			'bind'=>array(
			    'checked' => array(
				    'show' => 'webp_group'
			    ),
			    'not_checked' => array(
				    'hide' =>'webp_group'
			    )
			)
		    ),
		    array(
			'type'=>'group',
			'label'=>__( 'WebP Image Quality', 'themify' ),
			'help'=>__('Lower quality has smaller file size, but image might appear pixelated/blurry.','themify'),
			'wrap_class'=>'webp_group',
			'options'=>array(
			    array(
				'type'=>'select',
				'disabled'=>$imageLibrary?'':'disable',
				'id'=>'performance-webp_quality',
				'def'=>'5',
				'options'=>array(
				    '1'=>__('Lowest','themify'),
				    '2'=>__('Low','themify'),
				    '3'=>__('Medium','themify'),
				    '4'=>__('Good','themify'),
				    '5'=>__('High','themify'),
				    '6'=>__('Highest','themify'),
				)
			    ),
			    array(
				'type'=>'clear_cache',
				'disabled'=>$imageLibrary?'':'disable',
				'action'=>'themify_clear_all_webp',
				'text'=>__('Clear WebP Images','themify'),
				'clearing'=>__('Clearing...','themify'),
				'done'=>__('Done','themify')
			    ),
			)
		    ),
		    array(
			'label'=>__( 'Concate CSS', 'themify' ),
			'type'=>'clear_cache',
			'action'=>'themify_clear_all_concate',
			'text'=>__('Clear Concate CSS Cache','themify'),
			'clearing'=>__('Clearing...','themify'),
			'done'=>__('Done','themify'),
			'disabled'=>Themify_Enqueue_Assets::createDir()?'':__('It looks like the WordPress upload folder path is set wrong or have file permission issue. Please check the upload path on WP Settings > Media. Make sure the folder is set correctly and it has correct file permission.','themify'),
			'network'=> is_multisite()? array('tmp_cache_concte_network'=>__('Clear Concate cache in the whole network site','themify')):'',
		    ),
		);
	}
	
	private static function get_tab_role_access(){
		global $wp_roles;
		$defaultRoles=array(
		    'backend'=>__( 'Builder Backend Access', 'themify' ),
		    'frontend'=>__( 'Builder Frontend Access', 'themify' )
		);
		$role_options = array(
			'default' => __( 'Default', 'themify' ),
			'enable' => __( 'Enable', 'themify' ),
			'disable' => __( 'Disable', 'themify' )
		);
		$result=array();
		$roles = $wp_roles->get_names();
		$defaultRoles= apply_filters('tb_roles',$defaultRoles);
		// Remove the adminitrator and subscriber user role from the array
		unset( $roles['administrator']);

		// Remove all the user roles with no "edit_posts" capability
		foreach( $roles as $role => $slug ) {
		    if( empty( $wp_roles->roles[$role]['capabilities']['edit_posts'] ) ){
			unset( $roles[$role] );
		    }
		}
		
		foreach($defaultRoles as $k=>$v){
		    $opt=array();
		    foreach($roles as $type=>$name){
			$opt[]=array(
			    'type'=>'select',
			    'label'=>$name,
			    'id'=>'setting-'.$k.'-'.$type,
			    'options'=>$role_options
			);
		    }
		    $result[]=array(
			'type'=>'group',
			'label'=>$v,
			'options'=>$opt
		    );
		}
		return $result;
	}
	
	private static function get_tab_custom_css() {
	    return array(
		array(
		    'label'=>__('Custom CSS','themify'),
		    'id'=>'custom_css-custom_css',
		    'type'=>'textarea',
		    'codeditor'=>'css'
		)
	    );
	}
	
	
	private static function get_tab_integration(){
	    
	    include_once  THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php';
	    $providers = Builder_Optin_Service::get_providers();
	    $optins=array();
	    foreach ( $providers as $id => $instance ) {
		$options = $instance->get_global_options();
		if(!empty($options)){
		    foreach($options as &$opt){
			if(isset($opt['description'])){
			    $opt['desc']=$opt['description'];
			    unset($opt['description']);
			}
		    $optins[]=$opt;
		    }
		}
	    }
	    $optins[]=array(
		'type'=>'clear_cache',
		'action'=>'tb_optin_clear_cache',
		'text'=>__('Clear Cache','themify'),
		'clearing'=>__('Clearing...','themify'),
		'done'=>__('Done','themify'),
	    );
	    return array(
		array(
		    'label'=>__('Twitter API Keys','themify'),
		    'type'=>'group',
		    'options'=>array(
			array(
			    'type'=>'text',
			    'id'=>'builder_settings_twitter_consumer_key',
			    'label'=>__('Consumer Key','themify'),
			),
			array(
			    'type'=>'text',
			    'id'=>'builder_settings_twitter_consumer_secret',
			    'label'=>__('Consumer Secret','themify'),
			),
			array(
			    'type'=>'number',
			    'id'=>'builder_settings_twitter_cache_duration',
			    'label'=>__('Cache Duration','themify'),
			    'def'=>10,
			    'min'=>3,
			    'after'=>__('Minutes','themify'),
			    'desc'=> sprintf( __( '<a href="https://apps.twitter.com/app/new" target="_blank">Twitter access</a> is required for Themify Twitter module, read this <a href="%s" target="_blank">documentation</a> for more details.', 'themify' ), 'https://themify.me/docs/setting-up-twitter' )
			),
			array(
			    'type'=>'clear_cache',
			    'action'=>'themify_twitter_flush',
			    'text'=>__('Clear Cache','themify'),
			    'clearing'=>__('Clearing...','themify'),
			    'done'=>__('Done','themify'),
			),
		    )
		),
		array(
		    'label'=>__('Google Map API Key','themify'),
		    'type'=>'text',
		    'id'=>'builder_settings_google_map_key',
		    'desc'=>sprintf( __( 'Google API key is required to use Builder Map module and Map shortcode. <a href="%s" target="_blank">Generate an API key</a> and insert it here. Also, please ensure you\'ve setup a <a href="%s" target="_blank">billing plan</a>.' ), '//developers.google.com/maps/documentation/javascript/get-api-key#key', 'https://support.google.com/googleapi/answer/6158867' )
		),
		array(
		    'label'=>__('Bing Map API Key','themify'),
		    'type'=>'text',
		    'id'=>'builder_settings_bing_map_key',
		    'desc'=>sprintf( __( 'To use Bing Maps, <a href="%s" target="_blank">generate an API key</a> and insert it here.', 'themify' ), 'https://msdn.microsoft.com/en-us/library/ff428642.aspx' )
		),
		array(
		    'label'=>__('Cloudflare API','themify'),
		    'type'=>'group',
		    'options'=>array(
			array(
			    'type'=>'email',
			    'id'=>'builder_settings_clf_email',
			    'label'=>__('Account Email','themify'),
			),
			array(
			    'type'=>'text',
			    'id'=>'builder_settings_clf_key',
			    'label'=>__('API Key','themify'),
			)
		    )
		),
		array(
		    'label'=>__('reCaptcha API Settings','themify'),
		    'type'=>'group',
		    'options'=>array(
			array(
			    'type'=>'select',
			    'id'=>'builder_settings_recaptcha_version',
			    'label'=>__('Version','themify'),
			    'options'=>array('v2'=>__('Version 2','themify'),'v3'=>__('Version 3','themify'))
			),
			array(
			    'type'=>'text',
			    'id'=>'builder_settings_recaptcha_site_key',
			    'label'=>__('Site Key','themify'),
			),
			array(
			    'type'=>'text',
			    'id'=>'builder_settings_recaptcha_secret_key',
			    'label'=>__('Secret Key','themify'),
			)
		    )
		),
		array(
		    'label'=>__('Optin','themify'),
		    'type'=>'group',
		    'options'=>$optins
		),
	    );
	}
	
	private static function get_tab_tools(){
	    return array(
		    array(
			'label'=>__('Builder CSS Files','themify'),
			'type'=>'clear_cache',
			'action'=>'themify_regenerate_css_files_ajax',
			'text'=>__('Regenerate Builder CSS Files','themify'),
			'clearing'=>__('Clearing...','themify'),
			'done'=>__('Done','themify'),
			'desc'=>__('Builder styling are output to the generated CSS files stored in \'wp-content/uploads\' folder. Regenerate files will update all data in the generated files (eg. correct background image paths, etc.).','themify'),
			'network'=> is_multisite()? array('tmp_regenerate_all_css'=>__('Regenerate CSS in the whole network site','themify')):'',
		    ),
		    array(
			'label'=>__('Search & Replace URLs','themify'),
			'type'=>'group',
			'options'=>array(
			    array(
				'type'=>'replace_url',
				'text'=>__('Replace','themify'),
				'clearing'=>__('Searching...','themify'),
				'confirm'=>__('WARNING: This will replace all data in your database. It can not be undone. Please backup your database before proceeding.','themify'),
				'find'=>array(
				    'type'=>'text',
				    'label'=>__('Search for','themify')
				),
				'replace'=>array(
				    'type'=>'text',
				    'label'=>__('Replace to','themify')
				),
				'desc'=>__('Use this tool to find and replace the URLs in the Builder data. Warning: Please backup your database before replacing URLs, this can not be undone.','themify'),
			    )
			)
		    ),
		    array(
			'label'=>__('Maintenance Mode','themify'),
			'type'=>'group',
			'options'=>array(
			    array(
				'type'=>'select',
				'id'=>'tools_maintenance_mode',
				'desc'=>__('Once it is enabled, only logged-in users can see your site.','themify'),
				'options'=>array(
				    ''=>__('Disabled','themify'),
				    'on'=>__('Enable and display a page','themify'),
				    'message'=>__('Enable and display a message','themify'),
				),
				'bind'=>array(
				    ''=>array(
					'hide' => ['maintenance_message','maintenance_page']
				    ),
				    'on' => array(
					'hide' => 'maintenance_message',
					'show' => 'maintenance_page'
				    ),
				    'message' => array(
					'hide' => 'maintenance_page',
					'show' => 'maintenance_message'
				    )
				)
			    ),
			    array(
				'type'=>'select',
				'id'=>'tools_maintenance_page',
				'ajax'=>'themify_load_maintenance_pages',
				'desc'=>__('Select a page to show for public users','themify'),
				'wrap_class'=>'maintenance_page'
			    ),
			    array(
				'type'=>'textarea',
				'wrap_class'=>'maintenance_message',
				'id'=>'tools_maintenance_message'
			    ),
			)
		    ),
	    );
	}

	public static function create_admin_page() {
		$tabs = array(
		    'builder' => array(
			'label'=>__( 'Settings', 'themify' ),
			'icon'=> 'ti-settings',
			'options'=>self::get_tab_settings()
		    ),
		    'performance' =>array(
			'label'=> __( 'Performance', 'themify' ),
			'icon'=> 'ti-bolt-alt',
			'options'=>self::get_tab_performance()
		    ),
		    'role-access' => array(
			'label'=>__( 'Role Access', 'themify' ),
			'icon'=>'ti-lock',
			'options'=>self::get_tab_role_access()
		    ),
		    'custom-css' => array(
			'label'=>__('Custom CSS', 'themify'),
			'icon'=> 'ti-css3',
			'options'=>self::get_tab_custom_css()
		    ),
		    'integration'=>array(
			'label'=>__('Integration API','themify'),
			'icon'=> 'ti-key',
			'options'=>self::get_tab_integration()
		    ),
		    'tools'=>array(
			'label'=>__('Tools','themify'),
			'icon'=>'ti-panel',
			'options'=>self::get_tab_tools()
		    )
		    );
		foreach($tabs as $k=>$v){
		    themify_get_icon($v['icon'],'ti');
		}
		//used icons
		themify_get_icon('info','ti');
		themify_get_icon('alert','ti');
		themify_get_icon('check','ti');
		themify_get_icon('ti-eraser','ti');
		themify_get_icon('ti-help','ti');
		themify_enque_script('themify-builder-admin-settings', THEMIFY_BUILDER_URI . '/plugin/js/themify-builder-admin-settings.js',THEMIFY_VERSION,array('themify-main-script'));
		$options=get_option( self::KEY );
		if(empty($options)){
		    $options=array();
		}
		$options['recaptcha_version']=Themify_Builder_Model::getReCaptchaOption( 'version','v2');
		$options['recaptcha_site_key']=Themify_Builder_Model::getReCaptchaOption( 'public_key');
		$options['recaptcha_secret_key']=Themify_Builder_Model::getReCaptchaOption( 'private_key');
		wp_localize_script('themify-builder-admin-settings', 'tb_settings', array(
		    'data'=>$options,
		    'nonce' 	=> wp_create_nonce('tf_nonce'),
		    'labels'=>array(
			'en'=>__('Enable','themify'),
			'dis'=>__('Disable','themify')
		    ),
		    'options'=>$tabs
		));
		include THEMIFY_BUILDER_DIR . '/plugin/tpl/tmpl-builder-plugin-settings-page.php';
	}


	public static function show_custom_css(){
		$settings = get_option( self::KEY );
		$custom_css = !empty( $settings['custom_css-custom_css'] ) ? $settings['custom_css-custom_css'] : false;
		if ( $custom_css ){
			echo PHP_EOL . '<!-- Builder Custom Style -->' . PHP_EOL,
				'<style>' . PHP_EOL,
				$custom_css . PHP_EOL,
				'</style>' . PHP_EOL . '<!-- / end builder custom style -->' . PHP_EOL;
		}
	}

}
Themify_Builder_Options::init();
endif;