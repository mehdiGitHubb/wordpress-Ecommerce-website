<?php
/**
 * Themify admin page
 *
 * @package Themify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue JS and CSS for Themify settings page and meta boxes
 * @param String $page
 * @since 1.1.1
 *******************************************************/
function themify_enqueue_scripts($page){
	$pagenow = isset( $_GET['page'] ) ? $_GET['page'] : '';

	// Don't do anything while updating the theme
	if ( 'themify' === $pagenow && isset( $_GET['action'] ) && 'upgrade' === $_GET['action'] ) {
		return;
	}
	
	global $typenow;
	$types = themify_post_types();
	$pages = apply_filters( 'themify_top_pages', array( 'post.php', 'post-new.php', 'toplevel_page_themify', 'nav-menus.php' ) );
	$pagenows = apply_filters( 'themify_pagenow', array( 'themify' ) );
	
	wp_register_style( 'tf_base', THEMIFY_URI . '/css/base.min.css', null, THEMIFY_VERSION);
	wp_register_style( 'themify-ui',  themify_enque(THEMIFY_URI . '/css/themify-ui.css'), array('tf_base'), THEMIFY_VERSION );
	wp_register_style( 'themify-ui-rtl',  themify_enque(THEMIFY_URI . '/css/themify-ui-rtl.css'), array('themify-ui'), THEMIFY_VERSION );
	wp_register_style( 'themify-colorpicker', themify_enque(THEMIFY_METABOX_URI . 'css/jquery.minicolors.css'), null, THEMIFY_VERSION );
	wp_register_script( 'markjs', THEMIFY_URI . '/js/admin/jquery.mark.min.js', array('jquery'), THEMIFY_VERSION,true );
	wp_register_script( 'themify-colorpicker', themify_enque(THEMIFY_METABOX_URI . 'js/jquery.minicolors.js'), array('jquery'), THEMIFY_VERSION,true );
	wp_localize_script( 'themify-colorpicker', 'themifyCM', Themify_Metabox::themify_localize_cm_data() );
	wp_register_script( 'themify-scripts', themify_enque(THEMIFY_URI . '/js/admin/scripts.js'), array('jquery', 'jquery-ui-tabs'), THEMIFY_VERSION,true );
	wp_register_script( 'themify-plupload', themify_enque(THEMIFY_METABOX_URI . 'js/plupload.js'), array('jquery', 'themify-scripts'), THEMIFY_VERSION,true);
	wp_register_style ( 'magnific', themify_enque(THEMIFY_URI . '/css/modules/lightbox.css'), array(), THEMIFY_VERSION );
	wp_register_script( 'magnific', THEMIFY_URI . '/js/modules/lightbox.min.js', array('jquery'), THEMIFY_VERSION, true );

	// Custom Write Panel
	if( ($page === 'post.php' || $page === 'post-new.php') && in_array($typenow, $types,true) ){
		wp_enqueue_script( 'meta-box-tabs' );
		wp_enqueue_script( 'media-library-browse' );
	}

	// Register icon assets for later enqueueing.
	wp_register_style( 'themify-icons', themify_enque(THEMIFY_URI . '/themify-icons/themify-icons.css'), array(), THEMIFY_VERSION );
	
	$admin_vars=array(
		'nonce' 	=> wp_create_nonce('tf_nonce'),
		'admin_url' => admin_url( 'admin.php?page=themify' ),
		'page_not_found' => esc_html__( 'Page not found', 'themify' )
	);
	// Settings Panel
	if( $page === 'toplevel_page_themify' ){
	    
	
		wp_enqueue_script( 'jquery-ui-sortable' );
		// Load main.js for using fontawsome function
		themify_enque_script( 'themify-main-script', THEMIFY_URI.'/js/main.js', THEMIFY_VERSION, array('jquery'));
		Themify_Enqueue_Assets::addLocalization('url', THEMIFY_URI, false);
		
		$admin_vars['empty_li']=__('The fileds could not be empty!','themify');
		$admin_vars['theme']=get_template();
		$admin_vars['erase']=array(
		    'processing'=>__('Erasing... ','themify'),
		    'done'=>__('Erasing complete','themify'),
		    'error'=>__('An error occurred: %error%','themify')
		);
		$admin_vars['license']=array(
			'title'=>__('Themify License','themify'),
			'labels'=>array(
				'name'=>__('Themify Username','themify'),
				'key'=>__('License Key','themify'),
				'update'=>__('Update','themify'),
			),
			'link'=> sprintf('%s<a href="https://themify.me/member/softsale/license" target="_blank" rel="noopener">%s</a>%s',__('Please enter your Themify username and ','themify'),__('license key','themify'),__('. Make sure your membership/license key is active (not expired).','themify'))
		);
		$admin_vars['import']=array(
		    'posts' => __('Importing Posts (%from%/%to%): %post%', 'themify'),
		    'terms' => __('Importing Taxonomies (%from%/%to%): %post%', 'themify'),
		    'menu_items' => __('Importing menu items (%from%/%to%): %post%', 'themify'),
		    'menu' => __('Importing menus', 'themify'),
		    'theme' => __('Importing theme Settings', 'themify'),
		    'done'=>__('Import successfully finished', 'themify'),
		    'import_gs_data' => __('Importing Global Styling', 'themify'),
		    'download_images'=> __( 'Downloading images (%from%/%to%):', 'themify' ),
		    'upload_images'=> __( 'Uploading images (%from%/%to%):', 'themify' ),
		    'import_skip' => __('Failed import. Skip importing %post%', 'tbp'),
		    'import_failed' => __('Failed import: %post%', 'themify'),
		    'download_fail' => __('Download failed: %post%.', 'themify'),
		    'upload_fail' => __('Upload failed (%msg%): %post%', 'themify'),
		    'memory'=>(int)(wp_convert_hr_to_bytes(WP_MEMORY_LIMIT)*MB_IN_BYTES)
		);
			
		if(class_exists('Themify_Updater')){
		    $themify_updater = Themify_Updater::get_instance();
		    $username=$themify_updater->get_setting('username');
		    $key=$themify_updater->get_setting('key');
		    $admin_vars['license']['username']=!empty($username)?$username:'';
		    $admin_vars['license']['key']=!empty($key)?$key:'';
		    unset($themify_updater,$key,$username);
		}
		
		//used icons
		themify_get_icon('info','ti');
		themify_get_icon('alert','ti');
		themify_get_icon('check','ti');
	}
	if( in_array( $page, $pages,true ) ) {
		//Enqueue styles
		wp_enqueue_style( 'themify-ui' );
		wp_enqueue_style( 'themify-metabox' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'themify-ui-rtl' );
		}
		wp_enqueue_style( 'themify-colorpicker' );

		//Enqueue scripts
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-slider' );
                if( 'themify' === $pagenow){
                        wp_enqueue_script( 'jquery-ui-autocomplete' );
                }
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'plupload-all' );
		wp_enqueue_script( 'markjs' );
		wp_enqueue_script( 'themify-colorpicker' );
		if( in_array($typenow, $types,true) || in_array( $pagenow, $pagenows,true ) ){
			//Don't include Themify JavaScript if we're not in one of the Themify-managed pages
			wp_enqueue_script( 'themify-scripts' );
			wp_enqueue_script( 'themify-plupload' );
			Themify_Metabox::get_instance()->enqueue();
		}
		// Enqueue font icon assets.
		
		wp_enqueue_style( 'themify-icons' );
		Themify_Icon_Font::enqueue();
		wp_enqueue_style ( 'magnific' );
		wp_enqueue_script( 'magnific' );
	}

	
	wp_localize_script('themify-scripts', 'themify_js_vars', $admin_vars);
	
	wp_localize_script('themify-scripts', 'themify_lang', array(
			'confirm_reset_settings' => __('Are you sure you want to reset your theme settings?', 'themify'),
			'check_backup' => __('Make sure to backup before upgrading. Files and settings may get lost or changed.', 'themify'),
			'confirm_delete_image' => __('Do you want to delete this image permanently?', 'themify'),
			'confirm_demo' => __( 'This will import demo and override current Themify panel settings.', 'themify' ),
		)
	);

    wp_localize_script('themify-plupload', 'themify_plupload_lang', array(
            'enable_zip_upload' => sprintf(
                __('Install the "File Upload Types" plugin and add the file extension upload support.', 'themify'),
                esc_url( network_admin_url('settings.php').'#upload_filetypes' )
            ),
            'filesize_error' => __('The file you are trying to upload exceeds the maximum file size allowed.', 'themify'),
            'filesize_error_fix' => sprintf(
                __('Go to your <a href="%s">Network Settings</a> and increase the value of the <strong>Max upload file size</strong>.', 'themify'),
                esc_url( network_admin_url('settings.php').'#fileupload_maxk' )
            )
        )
    );

	// Enqueu admin widgets stuff
	if( $page === 'index.php' && themify_is_themify_theme()) {
		wp_enqueue_style( 'themify-admin-widgets-css' );
		wp_enqueue_script( 'themify-admin-widgets-js' );
	}
}

/**
 * Checks if current user is allowed to view the update interface.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function themify_allow_update() {
	return current_user_can( 'update_themes' );
}

///////////////////////////////////////////
// Create Nav Options
///////////////////////////////////////////
function themify_admin_nav() {
	$theme = wp_get_theme();
	$name = $theme->display('Name');
    $can_manage_option = current_user_can( 'manage_options' );
	/**
	 * Add Themify menu entry
	 * @since 2.0.2
	 */
	 
	add_menu_page( 'themify',$name , 'edit_posts', 'themify', $can_manage_option?'themify_page':'',"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='%23ffcc08' viewBox='0 0 32 32'%3E%3Cpath d='M11.5 3.5c15.8-5 21.8 9 20.3 17.3-.9 4.5-10 5.7-18 6.5-6.9.7-12.1 2.3-12.1 2.3s6.2-4.7 2.1-6.8C1.4 21.6-6.6 9.1 11.5 3.5zm7.3 6c-1 0-1.9.8-1.9 1.8a2 2 0 0 0 2 1.9c1 0 1.8-.9 1.8-1.9 0-1-.8-1.9-1.9-1.9zm-9.4.9a1.9 1.9 0 0 0-1.9 1.9 2 2 0 0 0 2 1.8 2 2 0 0 0 1.8-1.8c0-1.1-.9-2-1.9-2zM7 17.8s7.7 9.8 18.2-1.8c0 0-12.1 7.2-18.2 1.8z'%3E%3C/path%3E%3C/svg%3E", '49.3' );

    /**
	 * Add Themify settings page
	 * @since 2.0.2
	 */
	if($can_manage_option){
        add_submenu_page( 'themify', $name, __('Themify Settings', 'themify'), 'manage_options', 'themify', 'themify_page' );
    }
	if ( class_exists('Themify_Builder_Model') && Themify_Builder_Model::builder_check() ) {
		/**
		 * Add Themify Builder Layouts page
		 * @since 2.0.2
		 */
		add_submenu_page ( 'themify', __( 'Saved Layouts', 'themify' ), __( 'Saved Layouts', 'themify' ), 'edit_posts', 'edit.php?post_type=tbuilder_layout' );
		/**
		 * Add Themify Builder Layout Parts page
		 * @since 2.0.2
		 */
		add_submenu_page( 'themify', __( 'Layout Parts', 'themify' ), __( 'Layout Parts', 'themify' ), 'edit_posts', 'edit.php?post_type=tbuilder_layout_part' );
		/**
		 * Add Themify Global Styles page
		 * @since 4.5.0
		 */
		add_submenu_page ( 'themify', __( 'Global Styles', 'themify' ), __( 'Global Styles', 'themify' ), 'edit_posts', 'themify-global-styles', 'themify_global_styles_page' );
		/**
		 * Add Themify Custom Fonts page
		 * @since 4.6.3
		 */
		add_submenu_page( 'themify', __( 'Custom Fonts', 'themify' ), __( 'Custom Fonts', 'themify' ), 'edit_posts', 'edit.php?post_type=tb_cf' );
	}
	/**
	 * Add Themify Customize submenu entry
	 * @since 2.0.2
	 */
	add_submenu_page( 'themify', 'themify_customize', __( 'Customize', 'themify' ), 'manage_options', 'customize.php?themify=1' );
	if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
		/**
		 * Add Themify Updater License page link
		 * @since 4.2.2
		 */
		add_submenu_page ( 'themify', __( 'Themify License', 'themify' ), __( 'Themify License', 'themify' ), 'manage_options', 'index.php?page=themify-license' );
	}
	/**
	 * Add submenu entry that redirects to Themify documentation site
	 * @since 2.0.2
	 */
	add_submenu_page( 'themify', $name, __('Documentation', 'themify'), 'manage_options', 'themify_docs', 'themify_docs' );
    if(!$can_manage_option){
        remove_submenu_page('themify','themify');
    }

}

function themify_get_theme_required_plugins() {
	$info = get_file_data( trailingslashit( get_template_directory() ) . 'style.css', array( 'Required Plugins' ) );
	return isset( $info[0] )?$info[0]:'';
}

/*  Pages
/***************************************************************************/

///////////////////////////////////////////
// Themify Documentation
///////////////////////////////////////////
function themify_docs() {
	$theme = wp_get_theme();
	$doc_path = str_replace( 'themify-', '', $theme->get_template() );
	?>
	<script>window.location = "https://themify.me/docs/<?php echo $doc_path; ?>-documentation";</script>
	<?php
}

function themify_sort_config($themify_config) {
    $themify_config['panel']['settings']['tab']['theme_settings']['custom-module'][] = array(
	'title' => __('Accessibility', 'themify'),
	'function' => 'accessibility_options'
    );
    $defaultModules=array(
	'general'=>array(
		array(
		    'title' => __('Favicon', 'themify'),
		    'function' => 'favicon',
		    'target' => 'uploads/favicon/'
		),
		array(
		    'title' => __('Header Code', 'themify'),
		    'function' => 'header_html'
		),
		array(
		    'title' => __('Footer Code', 'themify'),
		    'function' => 'footer_html'
		),
		array(
		    'title' => __('Google Analytics', 'themify'),
		    'function' => 'themify_google_analytics_settings'
		),
		array(
		    'title' => __('Search Settings', 'themify'),
		    'function' => 'search_settings'
		),
		array(
		    'title' => __('Error 404 Page', 'themify'),
		    'function' => 'page_404_settings'
		),
		array(
		    'title' => __('Google Fonts', 'themify'),
		    'function' => 'themify_webfonts_subsets'
		),
		array(
		    'title' => __('Schema Microdata', 'themify'),
		    'function' => 'themify_framework_theme_microdata_config_callback'
		),
		array(
		    'title' => __('Maintenance Mode', 'themify'),
		    'function' => 'themify_maintenance_mode_settings'
		),
		array(
		    'title' => __('Feed Settings', 'themify'),
		    'function' => 'feed_settings'
		),
		array(
		    'title' => __('Custom Feed URL', 'themify'),
		    'function' => 'custom_feed_url'
		),
	    ),
	'performance'=>array(
	    array(
		'title' => '',
		'function' => 'performance_settings'
	    )
	),
	'social_links'=>array(
	    array(
		    'title' => __('Manage Social Links', 'themify'),
		    'function' => 'themify_manage_links'
	    )
	),
	'image_script'=> array(
	    array(
		    'title' => __('Image Script Settings', 'themify'),
		    'function' => 'img_settings'
	    )
	),
	'integration-api'=>array(
	    array(
		'title' => __('Twitter API Settings', 'themify'),
		'function' => 'themify_manage_twitter_settings'
	    ),
	    array(
		'title' => __('Google Map API Settings', 'themify'),
		'function' => 'themify_google_map_key'
	    ),
	    array(
		'title' => __('Bing Map API Settings', 'themify'),
		'function' => 'themify_bing_map_key'
	    ),
	    array(
		'title' => __('Cloudflare API Settings', 'themify'),
		'function' => 'themify_cloudflare_setting'
	    ),
	    array(
		'title' => __('reCaptcha API Settings', 'themify'),
		'function' => 'themify_recaptcha_setting'
	    ),
	 //   array(
		//'title' => __('Youtube/Vimeo GDPR', 'themify'),
		//'function' => 'themify_video_gdpr'
	  //  ),
	),
	'custom-icon-font'=>array(
	    array(
		'title' => __('Custom Icon Font', 'themify'),
		'function' => 'themify_fontello_input_callback',
	    ),
	)
    );
    foreach($defaultModules as $k=>$def){
	if(isset($themify_config['panel']['settings']['tab'][$k]['custom-module'])){
	    $defaultModules[$k]=array_merge($def,$themify_config['panel']['settings']['tab'][$k]['custom-module']);
	}
    }
    $themify_config['panel']['settings']['tab']['general'] = array(
	'title' => __('General', 'themify'),
	'id' => 'general',
	'custom-module' =>$defaultModules['general'] 
    );
   

    $themify_config['panel']['settings']['tab']['performance'] = array(
	'title' => __('Performance', 'themify'),
	'id' => 'performance',
	'custom-module' => $defaultModules['performance'] 
    );
    $themify_config['panel']['settings']['tab']['social_links'] = array(
	    'title' => __('Social Links', 'themify'),
	    'id' => 'social_links',
	    'custom-module' => $defaultModules['social_links'] 
    );
    $themify_config['panel']['settings']['tab']['image_script'] = array(
	    'title' => __('Image Script', 'themify'),
	    'id' => 'image_script',
	    'custom-module' =>$defaultModules['image_script'] 
    );
    $themify_config['panel']['settings']['tab']['integration-api'] = array(
	'title' => __('Integration API', 'themify'),
	'id' => 'integration-api',
	'custom-module' => $defaultModules['integration-api'] 
    );
    

    $themify_config['panel']['settings']['tab']['custom-icon-font'] = array(
	'title' => __('Custom Icon Font', 'themify'),
	'id' => 'custom-icon-font',
	'custom-module' => $defaultModules['custom-icon-font'] 
    );
    unset($defaultModules);
    $ordered=array();
    $sort=array('general','default_layouts','performance','theme_settings','shop_settings','portfolio_layouts','page_builder','social_links','hook-content','image_script','integration-api','role_access','custom-icon-font');
    foreach($sort as $v){
	if(isset($themify_config['panel']['settings']['tab'][$v])){
	    $ordered['panel']['settings']['tab'][$v]=$themify_config['panel']['settings']['tab'][$v];
	    unset($themify_config['panel']['settings']['tab'][$v]);
	}
    }
    if(!empty($themify_config['panel']['settings']['tab'])){
	foreach($themify_config['panel']['settings']['tab'] as $k=>$v){
		$ordered['panel']['settings']['tab'][$k]=$v;
	}
    }
    return apply_filters('themify_theme_config_sort',$ordered);
}

///////////////////////////////////////////
// Themify Page
///////////////////////////////////////////
function themify_page() {

	if ( ! current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to update this site.', 'themify' ) );

	if (isset($_GET['action'])) {
	    $action = 'upgrade';
	    themify_updater();
	}


	/**
	 * Load themify_config
	 */
	$themify_theme_config=array();
	include locate_template(array('custom-config.php', 'theme-config.php'));
	$themify_config = apply_filters('themify_theme_config_setup', $themify_theme_config);
	unset($themify_theme_config);
	$themify_config=themify_sort_config($themify_config);

	// check theme information
	$theme = wp_get_theme();
        $is_child = is_child_theme();
	$check_theme_name = $is_child? $theme->parent()->Name : $theme->display('Name');
	$check_theme_version = $is_child ? $theme->parent()->Version : $theme->display('Version');

	/**
	 * Markup for Themify skins. It's empty if there are no skins
	 * @since 2.1.8
	 * @var string
	 */
	$themify_skins = themify_get_skins();

	/* special admin tab that shows available skins with option to import demo separately for each */
	$skins_and_demos = current_theme_supports( 'themify-skins-and-demos' );
	?>
	<!-- alerts -->
	<div class="tb_alert"></div>
	<!-- /alerts -->

	<div id="tf_message" data-dismiss="<?php _e( 'Dismiss', 'themify' ); ?>"></div>

	<!-- gzip issue detection -->
	<script>document.body.classList.add( 'tf_litespeed' );</script>
	<style>
		.tf_litespeed #wpbody-content > * { display: none; }
		.tf_litespeed #tf_gz_message { margin: 2em; display: block !important; }
	</style>
	<div id="tf_gz_message" style="display: none"><?php printf( __( 'It appears your server doesn\'t load .gz file correctly. Please contact your host provide to fix the issue. As a temporary solution, you may <a href="%s">install this plugin</a> or delete all gz files packed in the theme (which it would load the original scripts).', 'themify' ), 'https://themify.me/files/themify-gzip-cleaner.zip' ); ?> <a class="button button-primary" href="<?php echo add_query_arg( 'tf_clean_gz', 1 ); ?>"><?php _e( 'Remove all GZIP files', 'themify' ); ?></a></div>
	<script async src="<?php echo themify_enque( THEMIFY_URI . '/js/admin/litespeed-gzip.js' ); ?>"></script>

	<!-- html -->
	<form id="themify" method="post" action="" enctype="multipart/form-data">
	<p id="theme-title"><?php echo esc_html( $check_theme_name ); ?> <em><?php echo esc_html( $check_theme_version ); ?> (<a href="<?php echo themify_https_esc( 'https://themify.me/changelogs/' ); ?><?php echo get_template(); ?>.txt" class="themify_changelogs" target="_blank" rel="noopener" data-changelog="<?php echo themify_https_esc( 'https://themify.me/changelogs/' ); ?><?php echo get_template(); ?>.txt"><?php _e('changelogs', 'themify'); ?></a>)</em></p>
	<p class="top-save-btn">
		<a href="#" class="save-button"><?php _e('Save', 'themify'); ?></a>
	</p>
	<div id="content">

		<!-- nav -->
		<ul id="maintabnav">
			<li class="setting"><a href="#setting"><?php _e( 'Settings', 'themify' ); ?></a></li>
			<?php if( $skins_and_demos ) : ?>
				<li class="skins"><a href="#skins"><?php _e( 'Skins & Demos', 'themify' ); ?></a></li>
                        <?php elseif ( ! empty( $themify_skins ) ) : ?>
				<li class="skins"><a href="#skins"><?php _e( 'Skins', 'themify' ); ?></a></li>
			<?php endif; ?>

			<li class="transfer"><a href="#transfer"><?php _e( 'Transfer', 'themify' ); ?></a></li>
			<?php if( ! $skins_and_demos ) : ?>
				<li class="demo-import"><a href="#demo-import"><?php _e( 'Demo Import', 'themify' ); ?></a></li>
			<?php endif;?>
			<?php if ( themify_allow_update() ) : ?>
				<li class="update-check"><a href="#update-check"><?php _e( 'Update', 'themify' ); ?></a></li>
			<?php endif; ?>
		</ul>
		<!-- /nav -->

		<!------------------------------------------------------------------------------------>

		<!--setting tab -->
		<div id="setting" class="maintab">

			<ul class="subtabnav">
                <div class="search-setting-holder">
                    <label for="search-setting" class="search-icon"><?php echo themify_get_icon('search','ti')?></label>
                    <input id="search-setting" type="text" class="search-setting" name="search-setting">
                    <span class="clear-search"><?php echo themify_get_icon('close','ti')?></span>
                </div>
				<?php
				$x = true;
				foreach($themify_config['panel']['settings']['tab'] as $tab):?>
                                        <?php if ( isset( $tab['id'] )):?>
                                            <li<?php if( $x===true):?> class="selected"<?php $x = false;?><?php endif;?>><a href="<?php esc_attr_e( '#setting-' . themify_scrub_func( $tab['id'] ) )?>"><?php echo $tab['title']?></a></li>
                                        <?php endif;?>
				<?php endforeach;?>
			</ul>

			<?php $themify_settings_notice = false; ?>
			 <?php foreach($themify_config['panel']['settings']['tab'] as $tab){ ?>
				<!-- subtab: setting-<?php echo themify_scrub_func($tab['id']); ?> -->
				<div id="<?php echo esc_attr( 'setting-' . themify_scrub_func( $tab['id'] ) ); ?>" class="subtab">
					<?php
					if(is_array($tab['custom-module'])){
						if(isset($tab['custom-module']['title'],$tab['custom-module']['function']) ){
							echo themify_fieldset( $tab['custom-module']['title'], $tab['custom-module']['function'], $tab['custom-module'] );
						} else {
							foreach($tab['custom-module'] as $module){
								$wrap = ( ! isset( $module['wrap'] ) || $module['wrap'] ) ? true : false;
								echo themify_fieldset( $module['title'], $module['function'], $module, $wrap );
							}
						}
					}
					?>
					<?php
					if ( ! $themify_settings_notice ) :
						?>
						<div class="themify-info-link"><?php printf( __( 'For more info about the options below, refer to the <a href="%s">General Settings</a> documentation.', 'themify' ), 'https://themify.me/docs/general-settings' ); ?></div>
						<?php
						$themify_settings_notice = true;
					endif; // themify settings notice
					
					if (themify_scrub_func($tab['id'])== 'default_layouts') {
						echo '<div class="themify-info-link">' . __( 'Here you can set the <a href="https://themify.me/docs/default-layouts">Default Layouts</a> for WordPress archive post layout (category, search, archive, tag pages, etc.), single post layout (single post page), and the static Page layout. The default single post and page layout can be override individually on the post/page &gt; edit &gt; Themify Custom Panel.', 'themify' ) . '</div>';
					}
					
					?>
				</div>
				
				<!-- /subtab: setting-<?php echo themify_scrub_func($tab['id']); ?> -->
			<?php } ?>

		</div><!--/setting tab -->

		<!------------------------------------------------------------------------------------>

		<!--skins tab -->
		<?php
		if ( ! empty( $themify_skins ) ) : ?>
			<div id="skins" class="maintab">
				<ul class="subtabnav">
					<li class="selected"><a href="#setting-general"><?php _e('Skins', 'themify'); ?></a></li>
				</ul>

				<div id="load-load" class="subtab">
					<?php if( $skins_and_demos ) : ?>
						<div class="themify-info-link"><?php _e( 'Select a skin and import the demo content (demo import is optional). Importing the demo content will override your Themify settings, menu and widget settings. It will also add the content (posts, pages, widgets, menus, etc.) to your site as per our demo setup. It is recommend to do on a fresh/development site. Erase demo will delete all the imported posts/pages (either modified or unmodified). Themify panel settings, user&rsquo;s created content, and widgets will not be removed.', 'themify' ); ?></div>
					<?php endif; ?>
					<div class="themify-skins">
						<input type="hidden" name="skin" value="<?php echo themify_get( 'skin','',true ); ?>">
						<?php echo themify_get_skins_admin(); ?>
					</div>
				</div>
			</div>
			<!--/skins tab -->
		<?php endif; ?>

		<!------------------------------------------------------------------------------------>

		<!--transfer tab -->
		<div id="transfer" class="maintab">
			<ul class="subtabnav">
				<li><a href="#transfer-import"><?php _e( 'Theme Settings', 'themify' ); ?></a></li>
			</ul>

			<div id="transfer-import" class="subtab">
				<div class="themify-info-link"><?php _e( 'Click "Export" to export the Themify panel data which you can use to import in the future by clicking the "Import" button. Note: this will only export/import the data within the Themify panel (the WordPress settings, widgets, content, comments, page/post settings, etc. are not included).', 'themify' ) ?></div>

				<div class="biggest-transfer-btn">
					<input type="hidden" id="import" />
					<?php
					 themify_uploader( 'import', array(
						'label' => __('Import', 'themify'),
						'confirm' => __('Import will overwrite all settings and configurations. Press OK to continue, Cancel to stop.', 'themify'),
						'button_class' => 'themify_button big-button',
					) );
					?>

					<em><?php _e('or', 'themify'); ?></em>
					<?php
					/**
					 * URL of Themify Settings Page properly nonced.
					 * @var String
					 */
					$baseurl = wp_nonce_url(admin_url('admin.php?page=themify'), 'themify_export_nonce');
					$baseurl = add_query_arg( 'export', 'themify', $baseurl );
					?>
					<a href="<?php echo esc_url( $baseurl ) ?>" class="export themify_button big-button" id="download-export"><?php _e('Export', 'themify'); ?></a>
				</div>
			</div>

		</div>
		<!--/transfer tab -->

		<?php if( ! $skins_and_demos ) : ?>
		<!--demo import tab -->
		<div id="demo-import" class="maintab">
			<ul class="subtabnav">
				<li><a href="#demo-import"><?php _e( 'Demo Import', 'themify' ); ?></a></li>
			</ul>

			<div id="demo-import" class="subtab demo-import-main">
				<div>
					<a href="#" class="themify_button big-button import-sample-content" data-plugins="<?php echo esc_attr(themify_get_theme_required_plugins()); ?>" data-default="<?php _e( 'Import Demo', 'themify' ); ?>" data-success="<?php _e( 'Done', 'themify' ); ?>" data-importing="<?php _e( 'Importing', 'themify' ) ?>"> <i class="ti-arrow-down"></i> <span><?php _e( 'Import Demo', 'themify' ); ?></span> </a>
				</div>
				<p><?php _e( 'Import demo will replicate your site like our demo setup.', 'themify' ); ?> <br><br>
				
				<small><?php _e( 'WARNING: Importing the demo content will override your Themify settings, menu and widget settings. It will also add the content (posts, pages, widgets, menus, etc.) to your site as per our demo setup. It is recommend to do on a fresh/development site.', 'themify' ); ?></small></p>
				<div>
				<a href="#" class="themify_button big-button erase-sample-content" data-default="<?php _e( 'Erase Demo', 'themify' ); ?>" data-erasing="<?php _e( 'Erasing', 'themify' ); ?>" data-success="<?php _e( 'Done', 'themify' ); ?>"> <i class="tf_close"></i> <span><?php _e( 'Erase Demo', 'themify' ); ?></span> </a>
				</div>
				<p><small><?php _e( 'Erase demo will delete all the imported posts/pages (either modified or unmodified). Themify panel settings, user&rsquo;s created content, and widgets will not be removed. You may import the content again later.', 'themify' ); ?></small></p>
			</div>

		</div>
		<!--/demo import tab -->
		<?php endif; ?>

		<?php if ( themify_allow_update() ) : ?>
		<!--update theme/framework tab -->
		<div id="update-check" class="maintab">
			<ul class="subtabnav">
				<li><a href="#update-main"><?php _e( 'Update', 'themify' ); ?></a></li>
				<li><a href="#child-theme"><?php _e( 'Child Theme', 'themify' ); ?></a></li>
			</ul>
			<div id="update-main" class="subtab update-main">
				<?php if ( defined('THEMIFY_UPDATER') ) :

						$updater = Themify_Updater::get_instance();
						$theme = wp_get_theme();
						$theme = is_child_theme() ? $theme->parent() : $theme;
						if ( ! method_exists($updater, 'themify_reinstall_theme') ) : ?>
							
							<div class="note">
								<?php _e( 'For theme re-installation feature, please update the Themify Updater plugin to latest version.', 'themify' ); ?>
							</div>
							
						<?php
						elseif ( $updater->has_error() && !$updater->has_attribute( $theme->stylesheet, 'free') ) :
							printf( __('Error: please check <a class="license-link" data-src="update" href="#">Themify License</a> settings.', 'themify'));
                            $license_modal=true;
						else:
							$updater->themify_reinstall_theme( $theme->stylesheet );
						endif;
					else : ?>
					<div class="note">
                        <?php
                        if(!empty($GLOBALS['tgmpa'])){
                            $tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
                            $action = ! $tgmpa_instance->is_plugin_installed( 'themify-updater' )?'install':'activate';
                            $url='#';
                            $ajax_url = wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'plugin'           => urlencode( 'themify-updater' ),
                                        'tgmpa-' . $action => $action . '-plugin',
                                        'auto_activate' => 1,
                                    ),
                                    $tgmpa_instance->get_tgmpa_url()
                                ),
                                'tgmpa-' . $action,
                                'tgmpa-nonce'
                            );
                        }else{
                            $url=admin_url( 'admin.php?page=themify-install-plugins' );
                        }
                        ?>
                        <?php printf( __( 'To update theme automatically, please activate <a class="themify-ajax-installer" href="%s"%s>Themify Updater</a> plugin and insert your Themify username/license key.', 'themify' ), $url,('#'!==$url ? ' target="_blank" rel="noopener"' : ' data-ajax="'.esc_url($ajax_url).'"') ); ?>
					</div>
				<?php endif; ?>
			</div>
			<div id="child-theme" class="subtab">
				<?php if ( is_child_theme() ) : ?>
					<p><?php echo themify_get_icon( 'far thumbs-up' ); ?> <?php _e( 'You\'re already using a child theme. Well done!', 'themify' ); ?></p>
				<?php else : ?>
					<?php Themify_Child_Theme_Generator::form(); ?>
				<?php endif; ?>
			</div>
		</div>
		<!--/update theme/framework tab -->
		<?php endif; // user can update_themes ?>

		<!------------------------------------------------------------------------------------>

	</div>
	<!--/content -->

	<?php if( get_option( get_template() . '_themify_import_notice', 1 ) ) : ?>
		<div id="demo-import-notice" class="themify-modal tf_scrollbar">

			<?php if ( ! is_child_theme() ) : ?>
				<?php Themify_Child_Theme_Generator::form(); ?>
				<hr>
			<?php endif; ?>

		<?php if( $skins_and_demos ) : ?>
			<h3><?php _e( 'Skins & Demos', 'themify' ); ?></h3>
			<p><?php _e( 'Select a skin and import the demo content as per our demo (optional). You can do this later at the Skins & Demos tab.', 'themify' ); ?></p>
			<div class="skins-demo-import-notice">
				<?php echo themify_get_skins_admin(); ?>
			</div>
		<?php else : ?>
			<h3><?php _e( 'Import Demo', 'themify' ); ?></h3>
			<p><?php _e( 'Would you like to import the demo content to have the exact look as our demo?', 'themify' ); ?></p>
			<p><?php _e( 'You may import or erase demo content later at the Import tab of the Themify panel.', 'themify' ); ?></p>
			<a href="#" class="themify_button import-sample-content" data-plugins="<?php echo esc_attr(themify_get_theme_required_plugins()); ?>" data-default="<?php _e( 'Import Demo', 'themify' ); ?>" data-success="<?php _e( 'Done', 'themify' ); ?>" data-importing="<?php _e( 'Importing', 'themify' ) ?>"> <i class="ti-arrow-down"></i> <span><?php _e( 'Yes, import', 'themify' ); ?></span> </a>
			<a href="#" class="thanks-button dismiss-import-notice"> <?php _e( 'No, thanks', 'themify' ); ?> </a>
			<div class="note"><?php _e( 'WARNING: Importing the demo content will override your Themify settings, menu and widget settings. It will also add the content (posts, pages, widgets, menus, etc.) to your site as per our demo setup. It is recommend to do on a fresh/development site.', 'themify' ); ?></div>
		<?php endif; ?>
			<a href="#" class="close dismiss-import-notice"><i class="tf_close"></i></a>
		</div>
		<?php
		    // disable the demo import modal after first visit
		    $_key=get_template() . '_themify_import_notice';
		    delete_option($_key);
		    add_option($_key,0, '', false );
		?>
	<?php endif; ?>

	<!-- footer -->
	<div id="bottomtab">
	   <p id="logo"><a href="<?php echo themify_https_esc( 'https://themify.me/logs/framework-changelogs/' ); ?>" data-changelog="<?php echo themify_https_esc( 'https://themify.me/changelogs/themify.txt' ); ?>" target="_blank" rel="noopener" class="themify_changelogs">v<?php echo THEMIFY_VERSION; ?></a></p>
		<div class="reset">
			<strong><a href="#" id="reset-setting" class="reset-button"><?php _e( 'Reset Settings', 'themify' ); ?></a></strong>
		</div>
		<p class="btm-save-btn">
			<a href="#" class="save-button"><?php _e('Save', 'themify'); ?></a>
		</p>
	</div>
	<!--/footer -->
	</form>
        <script>
	/**
	 * Ensure checkboxes are included in the data sent to server
	 * Fixes checkboxes not being saved
	 */
	const items=document.querySelectorAll('#themify input[type="checkbox"]');
	for(var i=items.length-1;i>-1;--i){
	    let name=items[i].getAttribute('name');
	    if(name && (!items[i].previousElementSibling || items[i].previousElementSibling.type!=='hidden')){
		let hidden=document.createElement('input');
		hidden.type='hidden';
		hidden.name=name;
		items[i].before(hidden);
	    }
	}
	</script>
	<div class="clearBoth"></div>
	<!-- /html -->

	<?php
	do_action('themify_settings_panel_end');
}

/**
 * Return an array of available theme skins
 *
 * @since 2.7.8
 * @return array
 */
function themify_get_skins(){
	// Open Styles Folder
	$dir = trailingslashit( get_template_directory() ) . '/skins';

	$skins = array(
		1 => array(
			array(
				'name' => __( 'No Skin', 'themify' ),
				'version' => null,
				'description' => null,
				'screenshot' => get_template_directory_uri() . '/themify/img/non-skin.gif',
			),
		),
	);
	if ( is_dir( $dir )  && ($handle = opendir( $dir ) )) {
	    // Grab Folders
	    while ( false !== ( $dirTwo = readdir($handle) ) ) {
		    if( $dirTwo !== '.' && $dirTwo !== '..' ) {
			    $path = trailingslashit( $dir ) . $dirTwo;
			    if( is_file( $path . '/style.css' ) ) {
				    $info = get_file_data( $path . '/style.css', array( 'Skin Name', 'Version', 'Description', 'Demo URI', 'Required Plugins', 'Display Order' ) );
				    $order = empty( $info[5] ) ? 10 : $info[5];
					$skins[ $order ][ $dirTwo ] = array(
					    'name' => $info[0],
					    'version' => $info[1],
					    'description' => $info[2],
					    'demo_uri' => $info[3],
					    'required_plugins' => $info[4],
				    );
			    }
		    }
	    }
	    closedir($handle);
	}
	ksort( $skins ); // sort skin groups
	ksort( $skins[10] ); // sort by skin name
	$skins = call_user_func_array( 'array_merge', $skins );

	return apply_filters( 'themify_theme_skins', $skins );
}

/**
 * Display the admin field for the theme skins
 *
 * @return string
 */
function themify_get_skins_admin(){
	$skins = themify_get_skins();
	$output = '';
	$template = get_template();
	$skins_with_demos = current_theme_supports( 'themify-skins-and-demos' );
	
	if( ! empty( $skins ) ) {
		$current=themify_get_skin();
		$canImport = current_user_can('manage_options');
		$hasDemo=current_user_can('delete_pages') && Themify_Import_Helper::has_demo_content();
		foreach( $skins as $id => $skin ) {
			$selected = $current === $id ? 'selected' : '';
			$screenshot = 0 === $id ? get_template_directory_uri() . '/themify/img/screenshot-na.png' : 'https://themify.me/public-api/screenshots/' . $template . '/' . $id . '/screenshot.jpg';

			if( $id === 'default' &&(!$current || $current==='default')) {
				$selected = 'selected';
			}

			$output .= '
				<div class="skin-preview '. $selected .'" data-skin="'. $id .'">
				<a href="#"><img src="' . $screenshot . '" alt="' . esc_attr__( 'Skin', 'themify' ) . '" loading="lazy" decoding="async" width="300" height="225" /></a>
				<br />' . $skin['name'];
			if(! empty( $skin['demo_uri'] ) ) {
				$output .= sprintf( ' <span class="view-demo"><a href="%s" target="_blank" rel="noopener">%s</a></span>', $skin['demo_uri'], __( 'demo', 'themify' ) );
			}
			if( $skins_with_demos && $canImport) {
				$output .= '<div class="skin-demo-content" data-skin="' . esc_attr( $id ) . '">';
					$output .= __( 'Demo:', 'themify' );
					$output .= ' <a href="#" class="skin-demo-import" data-id="'.esc_attr($id).'" data-plugins="'.esc_attr($skin['required_plugins']).'">' . __( 'Import', 'themify' ).'</a>';
					if($hasDemo===true){
					    $output.='<a href="#" class="skin-erase-link">' . __( 'Erase', 'themify' ) . '</a>';
					    $output .='<div class="tf_erase_box tf_abs_t tf_hide" tabindex="-1"><label><input type="checkbox" class="tf_modify_demo" id="_tf_modify_demo" checked="checked">'.__('Keep modified posts/pages','themify').'</label><a href="#" class="skin-erase-demo">'.__( 'Erase Demo', 'themify' ).'</a></div>';
					}
				$output .= '</div>';
			}

			$output .= '</div>';
		}
	}

	return $output;
}

/**
 * Create Settings Fieldset
 *
 * @param string $title
 * @param string $module
 * @param string $attr
 * @param bool $wrap whether to output the module wrapper
 *
 * @return string
 */
function themify_fieldset( $title = '', $module = '', $attr = '', $wrap = true ) {
	$data = themify_get_data();
	$data_param =  isset( $data['setting'][$title] )? $data['setting'][$title] : '';
	if( is_array( $module ) && is_callable( $module ) ) {
		$function = $module;
	} else {
		$function = '';
		$module = trim( $module );
		$module = themify_scrub_func( $module );
		if ( function_exists( 'themify_' . $module ) ) {
			$function = 'themify_' . $module;
		} else if ( function_exists( $module ) ) {
			$function = $module;
		}
		if ( '' == $function ) {
			return '';
		}
	}
	$output = call_user_func( $function, array(
		'data' => $data_param,
		'attr' => $attr )
	);
	if ( $wrap ) {
		$tmp_id = is_string( $function ) ? 'id="'. esc_attr( $function ) .'"' : '' ;
		$output = '<fieldset '.$tmp_id.'><legend><span>' . esc_html( $title ) . '</span><i class="tf_plus_icon"></i></legend><div class="themify_panel_fieldset_wrap">'
			. $output
		. '</div></fieldset>';
	}
	return $output;
}


/**
 * Get details about a known plugin
 *
 * @param $name if omitted, returns the entire list
 * @since 2.8.6
 */
function themify_get_known_plugin_info( $name = '' ) {
	$plugins = array(
		'builder-ab-image'          => array(
			'name' => __( 'Builder A/B Image', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/ab-image.jpg',
			'desc' => 'Compare 2 images side by side',
			'page' => 'https://themify.me/addons/ab-image',
			'path' => 'builder-ab-image/init.php',
		),
		'builder-audio'             => array(
			'name' => __( 'Builder Audio', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/audio.jpg',
			'desc' => 'Elegant audio playlist',
			'page' => 'https://themify.me/addons/audio',
			'path' => 'builder-audio/init.php'
		),
		'builder-bar-chart'         => array(
			'name' => __( 'Builder Bar Chart', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/bar-chart.jpg',
			'desc' => '',
			'page' => 'https://themify.me/addons/bar-chart',
			'path' => 'builder-bar-chart/init.php'
		),
		'builder-button'            => array(
			'name' => __( 'Builder Button Pro', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/button.jpg',
			'desc' => 'Custom designed action buttons',
			'page' => 'https://themify.me/addons/button',
			'path' => 'builder-button/init.php'
		),
		'builder-contact'           => array(
			'name' => __( 'Builder Contact', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/contact.jpg',
			'desc' => 'Simple contact form',
			'page' => 'https://themify.me/addons/contact',
			'path' => 'builder-contact/init.php'
		),
		'builder-countdown'         => array(
			'name' => __( 'Builder Countdown', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/countdown.jpg',
			'desc' => 'Count down events and promotions',
			'page' => 'https://themify.me/addons/countdown',
			'path' => 'builder-countdown/init.php'
		),
		'builder-counter'           => array(
			'name' => __( 'Builder Counter', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/counter.jpg',
			'desc' => 'Animated circles and number counters',
			'page' => 'https://themify.me/addons/counter',
			'path' => 'builder-counter/init.php'
		),
		'builder-fittext'           => array(
			'name' => __( 'Builder FitText', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/fittext.jpg',
			'desc' => 'Auto fit text in the container',
			'page' => 'https://themify.me/addons/fittext',
			'path' => 'builder-fittext/init.php'
		),
		'builder-image-pro'         => array(
			'name' => __( 'Builder Image Pro', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/image-pro.jpg',
			'desc' => 'Beautify images with image filters, color/image overlay, and animation effects',
			'page' => 'https://themify.me/addons/image-pro',
			'path' => 'builder-image-pro/init.php'
		),
		'builder-infinite-posts'    => array(
			'name' => __( 'Builder Infinite Posts', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/infinite-posts.jpg',
			'desc' => 'Display posts in infinite scrolling on parallax, grid, overlay, or list view',
			'page' => 'https://themify.me/addons/infinite-posts',
			'path' => 'builder-infinite-posts/init.php'
		),
		'builder-bar-chart'        => array(
			'name' => __( 'Builder Bat Chart', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/bar-chart.jpg',
			'desc' => 'Display bar graphs',
			'page' => 'https://themify.me/addons/bar-chart',
			'path' => 'builder-bar-chart/init.php'
		),
		'builder-maps-pro'          => array(
			'name' => __( 'Builder Maps Pro', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/maps-pro.jpg',
			'desc' => 'Multiple markers, custom icons, tooltips, and 40+ map styles',
			'page' => 'https://themify.me/addons/maps-pro',
			'path' => 'builder-maps-pro/init.php'
		),
		'builder-pie-chart'         => array(
			'name' => __( 'Builder Pie Chart', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/pie-chart.jpg',
			'desc' => '',
			'page' => 'https://themify.me/addons/pie-chart',
			'path' => 'builder-pie-chart/init.php'
		),
		'builder-pointers'          => array(
			'name' => __( 'Builder Pointers', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/pointers.jpg',
			'desc' => 'Highlight certain areas of your image',
			'page' => 'https://themify.me/addons/pointers',
			'path' => 'builder-pointers/init.php'
		),
		'builder-pricing-table'     => array(
			'name' => __( 'Builder Pricing Table', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/pricing-table.jpg',
			'desc' => 'Beautiful and responsive pricing table addon',
			'page' => 'https://themify.me/addons/pricing-table',
			'path' => 'builder-pricing-table/init.php'
		),
		'builder-progress-bar'      => array(
			'name' => __( 'Builder Progress Bar', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/progress-bar.jpg',
			'desc' => 'Animated bars based on input percentage',
			'page' => 'https://themify.me/addons/progress-bar',
			'path' => 'builder-progress-bar/init.php'
		),
		'builder-slider-pro'        => array(
			'name' => __( 'Builder Slider Pro', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/slider-pro.jpg',
			'desc' => 'Make stunning sliders with transition and animation effects',
			'page' => 'https://themify.me/addons/slider-pro',
			'path' => 'builder-slider-pro/init.php'
		),
		'builder-tiles'             => array(
			'name' => __( 'Builder Tiles', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/tiles.jpg',
			'desc' => 'Drag & drop tiles to create Windows 8 Metro layouts',
			'page' => 'https://themify.me/addons/tiles',
			'path' => 'builder-tiles/init.php'
		),
		'builder-timeline'          => array(
			'name' => __( 'Builder Timeline', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/timeline.jpg',
			'desc' => 'Display content in a timeline-styled layouts',
			'page' => 'https://themify.me/addons/timeline',
			'path' => 'builder-timeline/init.php'
		),
		'builder-typewriter'        => array(
			'name' => __( 'Builder Typewriter', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/typewriter.jpg',
			'desc' => 'Display your text with eye-catching typing animation',
			'page' => 'https://themify.me/addons/typewriter',
			'path' => 'builder-typewriter/init.php'
		),
		'builder-woocommerce'       => array(
			'name' => __( 'Builder WooCommerce', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/woocommerce.jpg',
			'desc' => 'Show WooCommerce products anywhere in the Builder',
			'page' => 'https://themify.me/addons/woocommerce',
			'path' => 'builder-woocommerce/init.php'
		),
		'contact-form-7'            => array(
			'name' => __( 'Contact Form 7', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/ab-image.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/contact-form-7/',
			'path' => 'contact-form-7/wp-contact-form-7.php',
			'wp_hosted'=>true
		),
		'themify-portfolio-post'    => array(
			'name' => __( 'Portfolio Posts', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/ab-image.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/themify-portfolio-post/',
			'path' => 'themify-portfolio-post/themify-portfolio-post.php',
			'wp_hosted'=>true
		),
		'mailchimp-for-wp'          => array(
			'name' => __( 'MailChimp for WordPress', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/ab-image.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/mailchimp-for-wp/',
			'path' => 'mailchimp-for-wp/mailchimp-for-wp.php',
			'wp_hosted'=>true
		),
		'woocommerce'               => array(
			'name' => __( 'WooCommerce', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/ab-image.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/woocommerce/',
			'path' => 'woocommerce/woocommerce.php',
			'wp_hosted'=>true
		),
		'themify-wc-product-filter' => array(
			'name' => __( 'Themify Product Filter', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/ab-image.jpg',
			'desc' => '',
			'page' => 'https://themify.me/themify-product-filter',
			'path' => 'themify-wc-product-filter/themify-wc-product-filter.php'
		),
		'themify-shortcodes' => array(
			'name' => __( 'Themify Shortcodes', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/themify-shortcodes.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/themify-shortcodes/',
			'path' => 'themify-shortcodes/init.php',
			'wp_hosted'=>true
		),
		'themify-event-post' => array(
			'name' => __( 'Themify Event Post', 'themify' ),
			'image' => 'https://themify.me/wp-content/product-img/addons/themify-shortcodes.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/themify-event-post/',
			'path' => 'themify-event-post/themify-event-post.php',
			'wp_hosted'=>true
		),
		'learnpress' => array(
			'name' => __( 'LearnPress', 'themify' ),
			'image' => 'https://ps.w.org/learnpress/assets/icon-256x256.png',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/learnpress/',
			'path' => 'learnpress/learnpress.php',
			'wp_hosted'=>true
		),
		'themify-store-locator'=>array(
			'name' => __( 'Themify Store Locator', 'themify' ),
			'image' => 'https://themify.me/wp-content/uploads/2016/12/store-locator-blog-image.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/themify-store-locator/',
			'path' => 'themify-store-locator/store_locator.php',
			'wp_hosted'=>true
		),
		'themify-updater' => array(
			'name' => __( 'Themify Updater', 'themify' ),
			'image' => '',
			'desc' => '',
			'page' => 'https://themify.me/docs/themify-updater-documentation',
			'path' => 'themify-updater/themify-updater.php'
		),
		'give' => array(
			'name' => __( 'Give', 'themify' ),
			'image' => 'https://ps.w.org/give/assets/icon-256x256.jpg',
			'desc' => '',
			'page' => 'https://wordpress.org/plugins/give/',
			'path' => 'give/give.php',
			'wp_hosted'=>true
		),
	);
	return $name===''?$plugins:(isset( $plugins[$name] )?$plugins[$name]:false);
}

/**
 * Themify Admin Widgets
 */

if( !function_exists( 'themify_add_admin_widgets' ) ):
function themify_add_admin_widgets() {
	wp_add_dashboard_widget( 'themify_news', esc_html__( 'Themify News', 'themify' ), 'themify_news_admin_widget' );
}
endif;
add_action( 'wp_dashboard_setup', 'themify_add_admin_widgets' );

// Themify News Admin Widget
function themify_news_admin_widget() {
	$cache_key = 'themify_news_dashboard_widget';

	echo '<div class="rss-widget">';
        if ( false !== ( $output = Themify_Storage::get( $cache_key ) ) ) {
		echo $output;
	} else {
            echo '<script async src="' . THEMIFY_URI . '/js/admin/admin-dashboard.min.js"></script>';
	}
	echo '</div>';
}


function themify_check_update_link( $plugin, $type ) {
	global $admin_page_hooks;
	if( !empty($admin_page_hooks[$plugin]) && $type === 'plugin' && strpos( 'builder-' , $plugin) === false ) {
		return esc_url( admin_url( 'admin.php?page=' . $plugin ) );
	}

	return esc_url( admin_url( 'admin.php?page=themify#update-check' ) );
}

///////////////////////////////////////////
// Favicon Module
///////////////////////////////////////////
function themify_favicon( $data = array() ) {
	if($data['attr']['target'] != ''){
		$target = "<span class='hide target'>".$data['attr']['target']."</span>";	
	} else {
		$target = '';
	}
	$setting_favicon = themify_get( 'setting-favicon','',true );
	return '<div class="themify_field_row">
				<span class="label">'. __('Custom Favicon', 'themify') . '</span>
				<input id="setting-favicon" type="text" class="width10" name="setting-favicon" value="' . esc_attr( $setting_favicon ) . '" /> <br />
				'.$target.'
				<span class="pushlabel" style="display:block;">
					' . themify_get_uploader('setting-favicon', array('tomedia' => true)) . '
				</span>
			</div>';
}

///////////////////////////////////////////
// Default Layouts
///////////////////////////////////////////
if (!function_exists('themify_custom_post_type_layouts')) :
/**
 * Default Custom Post sidebar Module
 * @param array $data Theme settings data
 * @return string Markup for module.
 * @since 4.0.0
 */
function themify_custom_post_type_layouts($data = array()){
	$data = themify_get_data();

	/**
	 * Theme Settings Option Key Prefix
	 * @var string
	 */
	$prefix = 'setting-custom_post_';

	/**
	 * Module markup
	 * @var string
	*/

	$output = '';

	$custom_posts = null;

	$post_types = get_post_types(array('public' => true, 'publicly_queryable' => 'true'), 'objects');
	$excluded_types = apply_filters( 'themify_exclude_CPT_for_sidebar', array('post', 'page', 'attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section','tglobal_style'));


	foreach ($post_types as $key => $value) {
		if (!in_array($key, $excluded_types)) {
			$custom_posts[$key] =  array( 'name' => $value->labels->singular_name, 'archive' => $value->has_archive );
		}
	}

	$custom_posts = apply_filters('themify_get_public_post_types', $custom_posts);

	/**
	 * Sidebar placement options
	 * @var array
	 */
	$sidebar_location_options = apply_filters('themify_post_type_theme_sidebars' , array(
									array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
									array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
									array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
								), false );
	/**
	 * Page sidebar placement
	 */
	
	if(is_array($custom_posts)){
		foreach($custom_posts as $key => $cPost){
			$output .= sprintf('<h4>%s %s</h4>', strtoupper($cPost['name']), __('POST TYPE', 'themify'));
			
			if ($cPost['archive']) {

				$output .= '<p>'. sprintf('<span class="label">%s %s</span>', ucfirst($cPost['name']), __('Archive Sidebar', 'themify'));
				$val = isset( $data[$prefix.$key.'_archive'] ) ? $data[$prefix.$key.'_archive'] : '';

				foreach ( $sidebar_location_options as $option ) {
					if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
						$val = $option['value'];
					}
					$class = $val == $option['value']?'selected':'';
					$w= isset($option['w'])?$option['w']:'46';
					$h= isset($option['w'])?$option['w']:'35';
					$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img width="'.$w.'" height="'.$h.'" loading="lazy" decoding="async" src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
				}

				$output .= '<input type="hidden" name="'.$prefix.$key.'_archive" class="val" value="'.$val.'" /></p>';

				$content_width = isset( $data[ $prefix . $key . '_archive_content_width'] ) ? $data[ $prefix . $key . '_archive_content_width'] : 'default_width';
				$output .=
					'<p data-show-if-element="[name=' . ( $prefix . $key) . '_archive]" data-show-if-value=\'["sidebar-none"]\'>
						<span class="label">' . sprintf( __( '%s Archive Content Width', 'themify' ), $cPost['name'] ) . '</span>
						<a href="#" class="preview-icon' . ( $content_width === 'default_width' ? ' selected' : '' ) . '" title="' . __( 'Default Width', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/default.svg" alt="default_width" loading="lazy" decoding="async" width="46" height="35"></a>
						<a href="#" class="preview-icon' . ( $content_width === 'full_width' ? ' selected' : '' ) . '" title="' . __( 'Fullwidth', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/fullwidth.svg" alt="full_width" loading="lazy" decoding="async" width="46" height="35"></a>
						<input type="hidden" name="' . $prefix . $key . '_archive_content_width" value="' . esc_attr( $content_width ) . '" class="val">
					</p>';
			}
			
			$output .= '<p>'. sprintf('<span class="label">%s %s</span>', ucfirst($cPost['name']), __('Single Sidebar', 'themify'));
			$val = isset( $data[$prefix.$key.'_single'] ) ? $data[$prefix.$key.'_single'] : '';

			foreach ( $sidebar_location_options as $option ) {
				if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
					$val = $option['value'];
				}
				$class = $val == $option['value']?'selected':'';
                                $w= isset($option['w'])?$option['w']:'46';
                                $h= isset($option['w'])?$option['w']:'35';
				$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img width="'.$w.'" height="'.$h.'" loading="lazy" decoding="async" src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
			}
			$output .= '<input type="hidden" name="'.$prefix.$key.'_single" class="val" value="'.$val.'" /></p>';

			$content_width = isset( $data[ $prefix . $key . '_single_content_width'] ) ? $data[ $prefix . $key . '_single_content_width'] : 'default_width';
			$output .=
				'<p data-show-if-element="[name=' . ( $prefix . $key) . '_single]" data-show-if-value=\'["sidebar-none"]\'>
					<span class="label">' . __( 'Default Single Content Width', 'themify' ) . '</span>
					<a href="#" class="preview-icon' . ( $content_width === 'default_width' ? ' selected' : '' ) . '" title="' . __( 'Default Width', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/default.svg" alt="default_width" loading="lazy" decoding="async" width="46" height="35"></a>
					<a href="#" class="preview-icon' . ( $content_width === 'full_width' ? ' selected' : '' ) . '" title="' . __( 'Fullwidth', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/fullwidth.svg" alt="full_width" loading="lazy" decoding="async" width="46" height="35"></a>
					<input type="hidden" name="' . $prefix . $key . '_single_content_width" value="' . esc_attr( $content_width ) . '" class="val">
				</p>';
		}
	}

	return $output;
}

endif;

///////////////////////////////////////////
// Custom Feed URL Module
///////////////////////////////////////////
function themify_custom_feed_url( $data = array() ) {
	$custom_feed_url = themify_get( 'setting-custom_feed_url','',true );
	return '<p><span class="label">' . __( 'Custom Feed URL', 'themify' ) . '</span> <input type="text" class="width10" name="setting-custom_feed_url" value="' . esc_attr( $custom_feed_url ) . '" /> <br />
			<span class="pushlabel"><small>' . __( 'e.g. http://feedburner.com/userid', 'themify' ) . '</small></span></p>';
}

///////////////////////////////////////////
// Meta Description Module
///////////////////////////////////////////
function themify_meta_description( $data = array() ) {
	$data = themify_get_data();
	return '<p><textarea name="setting-meta_description" class="widthfull" rows="4">'.$data['setting-meta_description'].'</textarea></p>';
}

/**
 * Header HTML Module
 * @param array $data
 * @return string
 */
function themify_header_html( $data = array() ) {
	$header_html = themify_get( 'setting-header_html','',true );
	return '<p>' . __('The following code will add to the &lt;head&gt; tag.', 'themify') . '</p>
				<p><textarea class="widthfull tf_code_editor" rows="10" name="setting-header_html" id="setting-header_html">'. esc_html( $header_html ) .'</textarea><br />
				<small>' . __('Useful if you need to add additional scripts such as CSS or JS.', 'themify') . '</small></p>';
}


/**
 * Footer HTML Module
 * @param array $data
 * @return string
 */
function themify_footer_html( $data = array() ) {
	$footer_html = themify_get( 'setting-footer_html','',true );
	return '<p>' . __('The following code will be added to the footer before the closing &lt;/body&gt; tag.', 'themify') . '</p>
				<p><textarea type="text" class="widthfull tf_code_editor" rows="10" name="setting-footer_html" id="setting-footer_html">' . esc_html( $footer_html ) . '</textarea><br />
				<small>' . __('Useful if you need to Javascript or tracking code.', 'themify') . '</small></p>';
}

///////////////////////////////////////////
// Search Settings Module
///////////////////////////////////////////
function themify_search_settings( $data = array() ) {
	$data            = themify_get_data();
	$post_checked         = '';
	$checked         = '';
	$search_settings = themify_get( 'setting-search_settings','',true );
	if ( themify_check( 'setting-search_settings_exclude',true ) ) {
		$checked = 'checked="checked"';
	}
	if ( themify_check( 'setting-search_exclude_post',true ) ) {
		$post_checked = 'checked="checked"';
	}
	$out = '<p>
				<span class="label">' . __( 'Search in Category IDs', 'themify' ) .themify_help(__( 'Use minus sign (-) to exclude categories. Example: (1,4,-7) = search only in Category 1 &amp; 4, and exclude Category 7.', 'themify' )) . ' </span>
				<input type="text" class="width6" name="setting-search_settings" value="' . esc_attr( $search_settings ) . '" />
			</p>
			<p>
				<span class="pushlabel"><label for="setting-search_exclude_post"><input type="checkbox" id="setting-search_exclude_post" name="setting-search_exclude_post" ' . $post_checked . '/> ' . __( 'Exclude Posts in search results', 'themify' ) . '</label></span>
			</p>
			<p>
				<span class="pushlabel"><label for="setting-search_settings_exclude"><input type="checkbox" id="setting-search_settings_exclude" name="setting-search_settings_exclude" ' . $checked . '/> ' . __( 'Exclude Pages in search results', 'themify' ) . '</label></span>
			</p>';


	$pre        = 'setting-search_exclude_';
	$checkboxes = '';

	$exclude_types = apply_filters( 'themify_types_excluded_in_search', get_post_types( array(
		'_builtin'            => false,
		'public'              => true,
		'exclude_from_search' => false
	) ) );

	foreach ( array_keys( $exclude_types ) as $post_type ) {

		$type = get_post_type_object( $post_type );

		if ( is_object( $type ) ) {
			$checkboxes .= '
		<p>
			<span class="pushlabel">
				<label for="' . $pre . $type->name . '">
					<input type="checkbox" id="' . $pre . $type->name . '" name="' . esc_attr( $pre . $type->name ) . '" ' . checked( isset( $data[ $pre . $type->name ] ) ? $data[ $pre . $type->name ] : '', 'on', false ) . '/> ' . sprintf( __( 'Exclude %s in search results', 'themify' ), $type->labels->name ) . '
				</label>
			</span>
		</p>';
		}
	}

	if ( '' != $checkboxes ) {
		$out .= $checkboxes;
	}

	return apply_filters('themify_search_settings_output', $out);
}

///////////////////////////////////////////
// 404 Page Settings Module
///////////////////////////////////////////
if( !function_exists( 'page_404_settings' ) ){
	function page_404_settings(){
		$data            = themify_get_data();
		$page_404 = themify_get( 'setting-page_404','',true );
		$max = 100;
		$args = array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'post_type' => 'page',
			'ignore_sticky_posts'=>true,
			'post_status' => 'publish',
			'cache_results'=>false,
			'update_post_term_cache'=>false,
			'update_post_meta_cache'=>false,
			'posts_per_page' => $max
		);
		$pages = new WP_Query( $args );
					$front = get_option('page_on_front');
		$out = '<p><span class="label">' . __( 'Custom 404 Page', 'themify' ) .themify_help(__('First create a new Page (eg. 404) and then select it here. The selected page will be used for error 404 (when a URL is not found on your site).', 'themify')). ' </span>';
					if($pages->max_num_pages>1){
						$post_name = '';
						if($page_404){
							$post_name = get_post($page_404);
							if(!empty($post_name)){
								$post_name = esc_attr($post_name->post_title);
							}
						}
						$out .= '<span class="themify_404_autocomplete_wrap">';
						$out .= '<input type="text" value="'.$post_name.'" id="themify_404_page_autocomplete" /><input type="hidden" name="setting-page_404" value="'.$page_404.'" />';
						$out .= '</span>';
					}
					else{
						$out.='<select name="setting-page_404"> 
						<option value="0">'.esc_attr( __( 'Select page', 'themify'  ) ).'</option>';
						while ( $pages->have_posts() ) {
														$pages->the_post();
														$id = get_the_ID();
														if($id!=$front){
							$selected         = '';
							if ( $page_404 == $id ) {
								$selected = 'selected="selected"';
							}
							$out .= '<option '.$selected.' value="' . $id . '">';
							$out .= get_the_title();
							$out .= '</option>';
														}
						}
						$out .= '</select>';
					}
					wp_reset_postdata();
		return $out;
	}
}

///////////////////////////////////////////
// RSS Feed Settings Module
///////////////////////////////////////////
function themify_feed_settings( $data = array() ) {
	$checked_use = '';
	$feed_settings = themify_get( 'setting-feed_settings','',true );
	$feed_custom_post = themify_get( 'setting-feed_custom_post','',true );
	$custom_posts = array_diff( get_post_types( array('public' => true, 'publicly_queryable' => 'true' ) )
		, array('attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section','tglobal_style') );
	$custom_posts_options = '<option></option>';

	if ( 'on' === themify_get( 'setting-exclude_img_rss','',true ) ) {
		$checked_use = 'checked="checked"';
	}

	if( ! empty( $custom_posts ) ) {
		array_unshift( $custom_posts, 'all' );
		$feed_custom_post_arr = explode( ',', trim( $feed_custom_post ) );

		foreach( $custom_posts as $c_post ) {
			$custom_posts_options .= sprintf( '<option %s value="%s">%s</option>'
			, in_array( $c_post, $feed_custom_post_arr ) ? 'selected="selected"' : ''
			, $c_post
			, ucfirst( preg_replace( "/[-_]/", ' ', $c_post ) ) );
		}
	}

	return '<p><span class="label">' . __('Feed Category', 'themify') .themify_help(__('Use minus sign (-) to exclude categories. <br/> Example: (2,-9) = include only Category 2 in feeds and exclude Category 9.', 'themify')) . '</span> <input type="text" class="width6" name="setting-feed_settings" value="' . esc_attr( $feed_settings ) . '" /></p>
			<p><span class="label">' . __('Post Image in RSS', 'themify') .themify_help(__('Check this to exclude post image in RSS feeds', 'themify')) . '</span> <label for="setting-exclude_img_rss"><input type="checkbox" id="setting-exclude_img_rss" name="setting-exclude_img_rss" '.$checked_use.'/> ' . __('Exclude featured image in RSS feeds', 'themify') . '</label></p>
			<p><span class="label">' . __('Custom Posts in RSS', 'themify') .themify_help(__( 'Select "All" to add all available posts in your feed or select the specific ones.', 'themify')) . '</span>
				<select size="6" multiple="multiple" class="width10 themify_multiselect">' . $custom_posts_options . '</select>
					<input type="hidden" name="setting-feed_custom_post" value="' . esc_attr( $feed_custom_post ) . '" />
			</p>';
}

/**
 * Outputs Image Script module in theme settings.
 */
function themify_img_settings( $data = array() ) {
	$feature_sizes = themify_get_image_sizes_list();
	$checked_use = '';
	$disable = '';
	$imaging_library = _wp_image_editor_choose();
	$imaging_library_error = '';
	if ( ! $imaging_library ) {
		$disable = ' style="pointer-events: none;"';
		$imaging_library_error = '<div class="pushlabel themify_warning note">' . sprintf( __( 'This feature requires an <a href="%s">image processing library</a> to be installed on the server. Please contact your hosting provider to enable this.', 'themify' ), 'https://www.php.net/manual/en/refs.utilspec.image.php' ) . '</div>';
	}
	if ( ! $imaging_library || themify_is_image_script_disabled() ) {
		$checked_use = "checked='checked'";
	}
	$size = themify_get( 'setting-img_php_base_size', 'large',true );
	$global = themify_get( 'setting-global_feature_size','',true );
	$output = '
	<div class="module">
		<div class="themify-info-link">' . sprintf( __( 'The image script is used to generate featured images dynamically in any dimension. If your images are cropped manually, disable it for faster performance. For more info about the image script, refer to the <a href="%s">Image Script</a> documentation.', 'themify' ), 'https://themify.me/docs/image-script' ) . '
		</div>
		<fieldset>
		<div class="label">' . __( 'Disable', 'themify' ) .themify_help(__( 'Default WordPress image sizes or original images will be used.', 'themify' )) . '</div> 
		<div class="row">
			<label for="setting-img_settings_use"' . $disable . '><input type="checkbox" id="setting-img_settings_use" name="setting-img_settings_use" class="disable_img_php" ' . $checked_use . '/> ' . __( 'Disable image script globally', 'themify' ) . '</label>
		' . $imaging_library_error . '
		</div>
		<div class="show_if_enabled_img_php">
			<div class="label">' . __('Base Image Size', 'themify') .themify_help(__( 'Select the image size that image script will resize thumbnails from. If you\'re not sure, leave it as "Large".', 'themify' )) . '</div>
			<div class="row">
				<select name="setting-img_php_base_size">';
					foreach ( $feature_sizes as $option ) {
					    $selected = $option['value'] === $size?' selected="selected"':'';
					    $output .= '<option'.$selected.' value="' . esc_attr( $option['value'] ) . '">' . $option['name'] . '</option>';
					}
					$output .= '
				</select>
			</div>
		</div>
		<div class="show_if_disabled_img_php">
			<div class="label">' . __('Default Featured Image Size', 'themify') . '</div>
			<div class="show_if_disabled_img_php row">
				<select name="setting-global_feature_size">';
					foreach ( $feature_sizes as $option ) {
						$selected = $option['value'] === $global?' selected="selected"':'';
						$output .= '<option'. $selected.' value="' . esc_attr( $option['value'] ) . '">' . $option['name'] . '</option>';
					}
					$output .= '
				</select>
			</div>
		</div>
		</fieldset>
		
	</div>';
	return $output;
}

/**
 * Outputs module for user to select whether to use a lightbox or not. The lightbox choices can be filtered using the 'themify_lightbox_module_options' filter in a custom-functions.php file.
 * @param array $data
 * @return string
 * @since 1.2.5
 */
function themify_gallery_plugins( $data = array() ) {

	$display_options = apply_filters('themify_lightbox_module_options', array(
		__( 'Enable', 'themify' ) => 'lightbox',
		__( 'Disable', 'themify' ) => 'none'
	));

	$gallery_lightbox = themify_get( 'setting-gallery_lightbox',null,true );

	$out = '<p>
				<span class="label">' . __( 'WordPress Gallery', 'themify' ) . ' </span>
				<select class="gallery_lightbox_type" name="setting-gallery_lightbox">';
				foreach ( $display_options as $option => $value ) {
					$out .= '<option value="' . $value . '" '.selected( $value, $gallery_lightbox, false ).'>' . esc_html( $option ) . '</option>';
				}
	$out .= '	</select>
			</p>';
    $out .= '<p>
				<span class="pushlabel"><label for="setting-lightbox_disable_share">
					<input type="checkbox" id="setting-lightbox_disable_share" name="setting-lightbox_disable_share" '. checked( themify_get( 'setting-lightbox_disable_share',false,true ), 'on', false ) .'/> ' . __('Hide social share buttons on lightbox', 'themify') . '</label>
				</span>
			</p>';
	$out .= '<p>
				<span class="pushlabel"><label for="setting-lightbox_content_images">
					<input type="checkbox" id="setting-lightbox_content_images" name="setting-lightbox_content_images" '. checked( themify_get( 'setting-lightbox_content_images',false,true ), 'on', false ) .'/> ' . __('Apply lightbox to image links automatically', 'themify') . '</label>
				</span>
				<small class="pushlabel">'. __( 'All links to jpg, png, and gif will open in lightbox.', 'themify' ) .'</small>
			</p>';
	return $out;
}

/**
 * Template to display a link in Links module, also used when creating a link.
 * @param array $data
 * @return string
 * @since 1.2.7
 */
function themify_add_link_template( $fid, $data = array(), $ajax = false, $type = 'image-icon' ) {
	$pre = 'setting-link_';
	
	$type_name = $pre.'type_'.$fid;
	if ( $ajax ) {
		$type_val = $type;
	} else {
		$type_val = isset($data[$type_name])? $data[$type_name] : 'image-icon';
	}

	$title_name = $pre.'title_'.$fid;
	$title_val = isset($data[$title_name])? esc_attr(trim($data[$title_name])): '';
	
	$link_name = $pre.'link_'.$fid;
	$link_val = isset($data[$link_name])? esc_attr(trim($data[$link_name])) : '';
	
	$img_name = $pre.'img_'.$fid;
	$img_val = ! isset( $data[$img_name] ) || '' == $data[$img_name]? '' : esc_attr($data[$img_name]);

	$ficon_name = $pre.'ficon_'.$fid;
	$ficon_val = trim( isset($data[$ficon_name])? esc_attr($data[$ficon_name]) : '' );

	$ficolor_name = $pre.'ficolor_'.$fid;
	$ficolor_val = isset($data[$ficolor_name])? esc_attr($data[$ficolor_name]) : '';

	$fibgcolor_name = $pre.'fibgcolor_'.$fid;
	$fibgcolor_val = isset($data[$fibgcolor_name])? esc_attr($data[$fibgcolor_name]) : '';

	/**
	 * TODO: Add appearance checkboxes
	 */

	$out = '<li id="' . $fid . '" class="social-link-item ' . $type_val . '">';

	$out .= '<div class="social-drag">' . esc_html__('Drag to Sort', 'themify') . '<i class="ti-arrows-vertical"></i></div>';

	$out .= '<input type="hidden" name="' . $type_name . '" value="' . trim( $type_val ) . '">';

	$out .= '<div class="row">
				<span class="label">' . __( 'Title', 'themify' ) . '</span> <input type="text" name="' . $title_name. '" class="width6" value="' . $title_val . '">
			</div>
			<!-- /row -->';

	$out .= '<div class="row">
				<span class="label">' . __( 'Link', 'themify' ) . '</span> <input type="text" name="' . $link_name . '" class="width10" value="' . $link_val . '">
			</div>
			<!-- /row -->';

	if ( 'font-icon' === $type_val ) {

		$out .= '<div class="row">
					<span class="label">' . __( 'Icon', 'themify' ) . '</span>';

		$out .= sprintf('<input type="text" id="%s" name="%s" value="%s" size="55" class="themify_input_field themify_fa %s" /> <a class="button button-secondary hide-if-no-js themify_fa_toggle" href="#" data-target="#%s">%s</a>',
			$ficon_name, $ficon_name, $ficon_val, 'small', $ficon_name, __( 'Insert Icon', 'themify' ) );

		$out .= '</div>
				<!-- /row -->';

		$out .= '<div class="icon-preview font-icon-preview">
						<i>'.themify_get_icon( $ficon_val ).'</i>
					</div>
					<!-- /icon-preview -->';

		$out .= '<div class="themify_field_row">
					<span class="label">' . __('Icon Color', 'themify') . '</span>
					<div class="themify_field-color">
						<input type="text" data-format="rgba" class="colorSelectInput width4" value="' . $ficolor_val. '" name="' . $ficolor_name . '" />
					</div>
				</div>';

		$out .= '<div class="themify_field_row">
					<span class="label">' . __('Background', 'themify') . '</span>
					<div class="themify_field-color">
						<input type="text" data-format="rgba" class="colorSelectInput width4" value="' . $fibgcolor_val . '" name="' . $fibgcolor_name . '" />
					</div>
				</div>';

	} else {

		$out .= '<div class="row">
					<span class="label">' . __( 'Image', 'themify' ) . '</span>
					<div class="uploader-fields image">
						<input type="text" id="' . $img_name . '" name="' . $img_name. '" class="width10" value="' . $img_val. '">
						<div class="clear image">' . themify_get_uploader( $img_name, array( 'tomedia' => true, 'preview' => true ) ) . '</div>
					</div>
				</div>
				<!-- /row -->';
		$out .= '<div class="icon-preview">
					<img id="' . $img_name . '-preview" src="' . $img_val . '" loading="lazy" decoding="async" />
				</div>
				<!-- /icon-preview -->';
	}

	$out .= '<a href="#" class="remove-item" data-removelink="' . $fid . '"><i class="tf_close"></i></a>
		</li>
		<!-- /social-links-item -->';

	return $out;
}

/**
 * Outputs module to manage links to be shown using the corresponding widget
 * @param array $data
 * @return string
 * @since 1.2.7
 */
function themify_manage_links( $data = array() ) {
	$data = themify_get_data();
	$pre = 'setting-link_';
	$field_hash = !empty( $data[$pre.'field_hash'] )? (int)$data[$pre.'field_hash'] : 8;
	$start = array();
	for ( $i=0; $i < $field_hash; ++$i ) {
		$start['themify-link-'.$i] = 'themify-link-'.$i;
	}
	//$data[$pre.'field_ids'] = json_encode($start);
	$field_ids=themify_get( $pre.'field_ids',false,true );
	if ( $field_ids) {
		$field_ids=json_decode( $field_ids, true );
		if ( ! is_array( $field_ids ) ) {
			$field_ids = array();
		}
	} else {
		$field_ids = $start;

		// Image Icons

		$data[$pre.'type_themify-link-0'] = 'image-icon';
		$data[$pre.'type_themify-link-1'] = 'image-icon';
		$data[$pre.'type_themify-link-2'] = 'image-icon';
		$data[$pre.'type_themify-link-3'] = 'image-icon';

		$data[$pre.'title_themify-link-0'] = 'Twitter';
		$data[$pre.'title_themify-link-1'] = 'Facebook';
		$data[$pre.'title_themify-link-2'] = 'YouTube';
		$data[$pre.'title_themify-link-3'] = 'Pinterest';
		
		$data[$pre.'link_themify-link-0'] = '';
		$data[$pre.'link_themify-link-1'] = '';
		$data[$pre.'link_themify-link-2'] = '';
		$data[$pre.'link_themify-link-3'] = '';
		
		$data[$pre.'img_themify-link-0'] = THEMIFY_URI . '/img/social/twitter.png';
		$data[$pre.'img_themify-link-1'] = THEMIFY_URI . '/img/social/facebook.png';
		$data[$pre.'img_themify-link-2'] = THEMIFY_URI . '/img/social/youtube.png';
		$data[$pre.'img_themify-link-3'] = THEMIFY_URI . '/img/social/pinterest.png';

		// Font Icons

		$data[$pre.'type_themify-link-4'] = 'font-icon';
		$data[$pre.'type_themify-link-5'] = 'font-icon';
		$data[$pre.'type_themify-link-6'] = 'font-icon';
		$data[$pre.'type_themify-link-7'] = 'font-icon';

		$data[$pre.'title_themify-link-4'] = 'Twitter';
		$data[$pre.'title_themify-link-5'] = 'Facebook';
		$data[$pre.'title_themify-link-6'] = 'YouTube';
		$data[$pre.'title_themify-link-7'] = 'Pinterest';

		$data[$pre.'link_themify-link-4'] = '';
		$data[$pre.'link_themify-link-5'] = '';
		$data[$pre.'link_themify-link-6'] = '';
		$data[$pre.'link_themify-link-7'] = '';

		$data[$pre.'ficon_themify-link-4'] = 'fa-twitter';
		$data[$pre.'ficon_themify-link-5'] = 'fa-facebook';
		$data[$pre.'ficon_themify-link-6'] = 'fa-youtube';
		$data[$pre.'ficon_themify-link-7'] = 'fa-pinterest';

		$data[$pre.'ficolor_themify-link-4'] = '';
		$data[$pre.'ficolor_themify-link-5'] = '';
		$data[$pre.'ficolor_themify-link-6'] = '';
		$data[$pre.'ficolor_themify-link-7'] = '';

		$data[$pre.'fibgcolor_themify-link-4'] = '';
		$data[$pre.'fibgcolor_themify-link-5'] = '';
		$data[$pre.'fibgcolor_themify-link-6'] = '';
		$data[$pre.'fibgcolor_themify-link-7'] = '';
		
		$data = apply_filters('themify_default_social_links', $data);
	}

	$out = '<div class="themify-info-link">' . sprintf( __( 'To display the links: go to Appearance > <a href="%s">Widgets</a> and drop a Themify - Social Links widget in a widget area (<a href="%s" target="_blank" rel="noopener">learn more</a>)', 'themify' ), admin_url('widgets.php'), 'https://themify.me/docs/social-media-links') . '</div>';

	$out .= '<div id="social-link-type">';
		// Icon Font
		$out .= '<label for="' . $pre . 'font_icon">';
		$out .= '<input ' . checked( isset( $data[$pre.'icon_type'] )? $data[$pre.'icon_type'] : 'font-icon', 'font-icon', false) . ' type="radio" id="' . $pre . 'font_icon" name="' .  $pre . 'icon_type" value="font-icon" data-hide="image-icon" /> ';
		$out .= __( 'Icon Font', 'themify' ) . '</label>';

		// Image
		$out .= '<label for="' . $pre . 'image_icon">';
		$out .= '<input ' . checked( isset( $data[$pre.'icon_type'] )? $data[$pre.'icon_type'] : '', 'image-icon', false ) . ' type="radio" id="' . $pre . 'image_icon" name="' . $pre . 'icon_type" value="image-icon" data-hide="font-icon" /> ';
		$out .= __( 'Image', 'themify' ) . '</label>';
	$out .= '</p>';

	$out .=  '<ul id="social-links-wrapper">';
		foreach ( $field_ids as $fid ) {
			$out .= themify_add_link_template( $fid, $data );
		}
	$out .= '</ul>';
	
	$out .= '<p class="add-link add-social-link"><a href="#">' . __('Add Link', 'themify') . '</a></p>';

	$out .= '<input type="hidden" id="' .  $pre . 'field_ids" name="' . $pre . 'field_ids" value=\'' . json_encode( $field_ids ) . '\'/>';
	$out .= '<input type="hidden" id="' .  $pre . 'field_hash" name="' . $pre . 'field_hash" value="' . esc_attr( $field_hash ) . '"/>';
	//$out .= '<p>Fields: '.json_encode($field_ids).'</p><p>Hash: '.$field_hash.'</p>';
	
	return $out;
}

/**
 * Outputs post meta options
 * @param string $pmkey Key used to get data from theme settings array
 * @param array $data Theme settings data
 * @param array $metas Optional array stating the metas available.
 * @return string $out Markup for options
 */
function themify_post_meta_options( $pmkey, $data, $metas = array(), $states = array(), $group_label = false ) {
	
	if ( empty($metas ) ) {
		$metas = array (
			''			=> __( 'Hide All', 'themify' ),
	 		'author' 	=> __( 'Author', 'themify' ),
	 		'category' 	=> __( 'Category', 'themify' ),
	 		'comment' 	=> __( 'Comment', 'themify' ),
	 		'tag' 		=> __( 'Tag', 'themify' )
		);
	}
	if ( empty( $states ) ) {
		$states = array(
			array(
				'name' => __( 'Hide', 'themify' ),
				'value' => 'yes',
				'icon' => THEMIFY_URI . '/img/ddbtn-check.svg',
				'title' => __( 'Hide this meta', 'themify' )
			),
			array(
				'name' => __( 'Do not hide', 'themify' ),
				'value' => 'no',
				'icon' => THEMIFY_URI . '/img/ddbtn-cross.svg',
				'title' => __( 'Show this meta', 'themify' )
			)
		);
	}
	if ( ! $group_label ) {
		$group_label = __( 'Hide Post Meta', 'themify' );
	}
	
	$default = array(
		'name' => __( 'Theme', 'themify' ),
		'value' => '',
		'icon' => THEMIFY_URI . '/img/ddbtn-blank.png',
		'title' => esc_attr(__( 'Use theme settings', 'themify' ))
	);
	
	$out = '<div class="themify_field_row dropdownbutton-group"><span class="label">' . esc_html( $group_label ) . '</span>';
					
			foreach ( $metas as $meta => $name ) {
				if ( '' == $meta ) {
					$metakey = $pmkey;
					$meta_class = 'ddbtn-all';
				} else {
					$metakey = $pmkey.'_'.$meta;
					$meta_class = 'ddbtn-sub ddbtn-'.$meta;
				}
				$name=esc_html( $name );
				$others = '';
				$out .=	'<div id="' . $metakey. '" class="dropdownbutton-list" data-name="' . $name . '" data-def-icon="' . $default['icon']. '">';
				
				// default state
				$first = '
					<div class="first-ddbtn">
						<a href="#" data-val="' . $default['value'] . '" data-name="' . $default['name']  . '" title="' . $default['title'] . '">
							<img src="' . $default['icon'] . '" title="' . $default['title'] . '" loading="lazy" decoding="async" />
							<span class="ddbtn-name">' . $name . '</span>
						</a>
					</div>';

				foreach ( $states as $state ) {
					$title=esc_attr($state['title']);
					if ( isset( $state['value'] ) && isset( $data[$metakey] ) && $state['value'] === $data[$metakey] ) {
					    $first = '<div class="first-ddbtn">
						    <a href="#" data-val="' . $state['value'] . '" data-name="' . $state['name'] . '" title="' . $title. '">
							    <img src="' . $state['icon']. '" title="' . $title . '" loading="lazy" decoding="async" />
							    <span class="ddbtn-name">' . $name . '</span>
						    </a>
					    </div>';
					    $selected = 'selected';
					} else {
						$selected = '';
					}
					
					$others .= '
						<div class="ddbtn">
							<a href="#" data-sel="' . $selected. '" data-val="' . $state['value'] . '" data-name="' . $state['name'] . '" title="' . $title . '">
								<img src="' . $state['icon'] . '" title="' . $title. '" loading="lazy" decoding="async" />
								<span class="ddbtn-label">' . esc_html( $state['name'] ) . '</span>
							</a>
						</div>';
				}
				$out .= $first . '<div class="dropdownbutton">' . $others . '</div>';
				$out .= '
				</div>';
				$out .= '<input type="hidden" value="' . esc_attr( themify_get( $metakey ) ) . '" class="' . $meta_class. '" id="' . $metakey . '" name="' . $metakey . '" />';
			}

	$out .= '</div>';
	return $out;
}

/**
 * Outputs post sorting options
 * @param string $key Key used to get data from theme settings array
 * @param array $data Theme settings data
 * @return string $out Markup for options
 */
if ( ! function_exists( 'themify_post_sorting_options' ) ) {
	function themify_post_sorting_options( $key = 'setting-index_order', $data = array() ) {

		$orderby = themify_get( $key . 'by','');
		$orderby_options = apply_filters( 'themify_index_orderby_options', array(
			__( 'Date (default)', 'themify' ) => 'date',
			__( 'Random', 'themify' ) => 'rand',
			__( 'Author', 'themify' ) => 'author',
			__( 'Post Title', 'themify' ) => 'title',
			__( 'Comments Number', 'themify' ) => 'comment_count',
			__( 'Modified Date', 'themify' ) => 'modified',
			__( 'Post Slug', 'themify' ) => 'name',
			__( 'Post ID', 'themify' ) => 'ID',
			__( 'Custom Field String', 'themify' ) => 'meta_value',
			__( 'Custom Field Numeric', 'themify' ) => 'meta_value_num' ) );

		$order = themify_get( $key,'');
		$order_options = array(
			__( 'Descending (default)', 'themify' ) => 'DESC',
			__( 'Ascending', 'themify' ) => 'ASC' );

		$order_meta_key = 'setting-index_meta_key';

		$out = '<p><span class="label">' . __( 'Order By', 'themify' ) . ' </span>
					<select name="' .$key . 'by">';
						foreach ( $orderby_options as $option => $value ) {
						    $out .= '<option value="' . esc_attr( $value ) . '" '.selected( $value, $orderby, false ).'>' . esc_html( $option ) . '</option>';
					    }
		$out .= '	</select>
				</p>
				<p data-show-if-element="[name=' . $key . 'by]" data-show-if-value=\'["meta_value", "meta_value_num"]\'>
					<span class="label">' . __( 'Custom Field Key', 'themify' ) . ' </span>
					<input type="text" id="' . $order_meta_key . '" name="' . $order_meta_key . '" value="' . esc_attr( themify_get( $order_meta_key,'',true ) ) . '" />
				</p>
				<p>
					<span class="label">' . __( 'Order', 'themify' ) . ' </span>
					<select name="' . $key. '">';
						foreach ( $order_options as $option => $value ) {
							$out .= '<option value="' . esc_attr( $value ) . '" '.selected( $value, $order, false ).'>' . esc_html( $option ) . '</option>';
					}
		$out .= '	</select>
				</p>';

		return $out;
	}
}

if ( ! function_exists( 'themify_homepage_welcome' ) ) {
	/**
	 * Homepage Welcome Function
	 * @return string Markup for welcome text control
	 */
	function themify_homepage_welcome() {
		return '<p><textarea class="widthfull" name="setting-homepage_welcome" rows="4">' . esc_textarea( themify_get( 'setting-homepage_welcome','',true ) ) . '</textarea></p>';
	}
}

if ( ! function_exists( 'themify_exclude_rss' ) ) {
	/**
	 * Exclude RSS
	 * @return string
	 */
	function themify_exclude_rss() {
		return '<p><label for="setting-exclude_rss"><input type="checkbox" id="setting-exclude_rss" name="setting-exclude_rss" ' . checked( themify_get( 'setting-exclude_rss','',true ), 'on', false ) . '/> ' . __( 'Check here to exclude RSS icon/button in the header', 'themify' ) . '</label></p>';	
	}
}

if ( ! function_exists( 'themify_exclude_search_form' ) ) {
	/**
	 * Exclude Search Form
	 * @return string
	 */
	function themify_exclude_search_form() {
		return '<p><label for="setting-exclude_search_form"><input type="checkbox" id="setting-exclude_search_form" name="setting-exclude_search_form" ' . checked( themify_get( 'setting-exclude_search_form','',true ), 'on', false ) . '/> ' . __( 'Check here to exclude search form in the header', 'themify' ) . '</label></p>';	
	}
}

if( ! function_exists( 'footer_text_settings' ) ) {
	/**
	 * Footer Text Settings
	 * @return string
	 */
	function footer_text_settings() {
		return '<div class="themify-info-link">' . __( 'Enter your text to replace the copyright and credit links in the footer. HTML tags allowed. Type %year% to display the current year.', 'themify' ) . '</div>' . themify_footer_text_left() . themify_footer_text_right();
	}
}

if ( ! function_exists( 'themify_footer_text_left' ) ) {
	/**
	 * Footer Text Left Function
	 * @return string
	 */
	function themify_footer_text_left() {
		return '<h4>' . __('Footer Text One', 'themify') . '</h4><div data-show-if-element="[name=setting-footer_text_left_hide]" data-show-if-value="false"><textarea class="widthfull" rows="4" name="setting-footer_text_left">' . esc_textarea( themify_get( 'setting-footer_text_left','',true ) ) . '</textarea></div><div><label><input type="checkbox" name="setting-footer_text_left_hide" value="hide" ' . checked( themify_get( 'setting-footer_text_left_hide','',true ), 'hide', false ) . ' />' . __( 'Hide Footer Text One', 'themify' ) . '</label></div>';
	}
}

if ( ! function_exists( 'themify_footer_text_right' ) ) {
	/**
	 * Footer Text Right Function
	 * @return string
	 */
	function themify_footer_text_right(){
		return '<h4>' . __('Footer Text Two', 'themify') . '</h4><div data-show-if-element="[name=setting-footer_text_right_hide]" data-show-if-value="false"><textarea class="widthfull" rows="4" name="setting-footer_text_right">' . esc_textarea( themify_get( 'setting-footer_text_right','',true ) ) . '</textarea></div><div><label><input type="checkbox" name="setting-footer_text_right_hide" value="hide" ' . checked( themify_get( 'setting-footer_text_right_hide','',true ), 'hide', false ) . ' />' . __( 'Hide Footer Text Two', 'themify' ) . '</label></div>';
	}
}

if(!function_exists('themify_homepage_widgets')){
	/**
	 * Widgets module function
	 * @return string Module markup
	 */
	function themify_homepage_widgets(){
		$val = themify_get( 'setting-homepage_widgets',false,true );
		$options = array(
			array(
				'value' => 'homewidget-4col',
				'img' => 'themify/img/sidebars/4col.png',
				'title' => __('Widgets 4 Columns', 'themify')),
			array(
				'value' => 'homewidget-3col',
				'img' => 'themify/img/sidebars/3col.png',
				'title' => __('Widgets 3 Columns', 'themify'),
				'selected' => true),
			array(
				'value' => 'homewidget-2col',
				'img' => 'themify/img/sidebars/2col.png',
				'title' => __('Widgets 3 Columns', 'themify')),
			array(
				'value' => 'homewidget-1col',
				'img' => 'themify/img/sidebars/1col.png',
				'title' => __('Widgets 1 Column', 'themify')),
			array(
				'value' => 'none',
				'img' => 'themify/img/sidebars/none.png',
				'title' => __('No Widgets', 'themify'))
		);
		$output = '';
		foreach($options as $option){
			if(!$val && !empty($option['selected'])){ 
			    $val = $option['value'];
			}
			$class = $val === $option['value']?' selected':'';
			$output .= '<a href="#" class="preview-icon' . $class . '" title="' . esc_attr( $option['title'] ) . '"><img src="' . THEME_URI.'/'.$option['img']. '" alt="' . esc_attr( $option['value'] ) . '" loading="lazy" decoding="async" width="46" height="35"/></a>';
		}
		$output .= '<input type="hidden" name="setting-homepage_widgets" class="val" value="' . esc_attr( $val ) . '" />';
		return $output;
	}
}

if(!function_exists('themify_footer_widgets')){
	/**
	 * Widgets module function
	 * @return string Module markup
	 */
	function themify_footer_widgets(){
		$val = themify_get( 'setting-footer_widgets',false,true );
		$options = array(
			array(
				'value' => 'footerwidget-4col',
				'img' => 'themify/img/sidebars/4col.png',
				'title' => __('Widgets 4 Columns', 'themify')),
			array(
				'value' => 'footerwidget-3col',
				'img' => 'themify/img/sidebars/3col.png',
				'title' => __('Widgets 3 Columns', 'themify'),
				'selected' => true),
			array(
				'value' => 'footerwidget-2col',
				'img' => 'themify/img/sidebars/2col.png',
				'title' => __('Widgets 2 Columns', 'themify')),
			array(
				'value' => 'footerwidget-1col',
				'img' => 'themify/img/sidebars/1col.png',
				'title' => __('Widgets 1 Column', 'themify')),
			array(
				'value' => 'none',
				'img' => 'themify/img/sidebars/none.png',
				'title' => __('No Widgets', 'themify'))
		);
		$output = '';
		foreach($options as $option){
			if(!$val &&!empty($option['selected'])){ 
			    $val = $option['value'];
			}
			$class = $val === $option['value']?' selected':'';
			$output .= '<a href="#" class="preview-icon' . $class. '" title="' . esc_attr( $option['title'] ) . '"><img src="' . THEME_URI.'/'.$option['img']. '" alt="' . esc_attr( $option['value'] ) . '" loading="lazy" decoding="async" width="46" height="35" /></a>';
		}
		$output .= '<input type="hidden" name="setting-footer_widgets" class="val" value="' . esc_attr( $val ) . '" />';
		return $output;
	}
}

if(!function_exists('themify_manage_twitter_settings')){
	/**
	 * Twitter API Settings
	 * @return string
	 */
	function themify_manage_twitter_settings() {
		$prefix = 'setting-twitter_settings_';

		$out = '<p><label class="label" for="' .  $prefix . 'consumer_key">'.__('Consumer Key', 'themify').'</label>';
		$out .= '<input type="text" id="' . $prefix . 'consumer_key" name="' . $prefix . 'consumer_key" class="width10" value="' . esc_attr( themify_get( $prefix.'consumer_key','',true ) ) . '" /></p>';

		$out .= '<p><label class="label" for="' . $prefix . 'consumer_secret">'.__('Consumer Secret', 'themify').themify_help(__('<a href="https://apps.twitter.com/app/new">Twitter access</a> is required for Themify Twitter widget, read this <a href="https://themify.me/docs/setting-up-twitter">documentation</a> for more details.', 'themify')).'</label>';
		$out .= '<input type="text" id="' . $prefix . 'consumer_secret" name="' . $prefix . 'consumer_secret" class="width10" value="' . esc_attr( themify_get( $prefix.'consumer_secret','',true ) ) . '" />
		</p>';

		$out .= '<p><label class="label" for="' . $prefix . 'cache">'.__('Cache Duration', 'themify') . '</label>';
		$out .= '<input type="number" id="' . $prefix . 'cache" name="' . $prefix . 'cache" class="width2" value="' . esc_attr( themify_get( $prefix . 'cache', 10, true ) ) . '" /> ' . __( 'Minutes', 'themify' ) . '</p>';
		$out .=
			'<p class="pushlabel">
				<a href="#" class="themify_button" id="tb_option_flush_twitter"><span>' . __( 'Clear Cache', 'themify' ) . '</span></a>
			</p>';

		return $out;
	}
}

if ( ! function_exists( 'themify_entries_navigation' ) ) {
	/**
	 * Display module to select numbered pagination or links to previous and next posts.
	 * @param array $data
	 * @return string $html Module markup.
	 * @since 1.6.0
	 */
	function themify_entries_navigation( $data = array() ) {
		$key = 'setting-entries_nav';
		$v= themify_get( $key,'numbered',true );
		$html = '<p>';
			// Numbered pagination
			$html .= '<label for="' . $key . '_numbered">';
			$html .= '<input ' . checked( $v, 'numbered', false) . ' type="radio" id="' .  $key . '_numbered" name="' . $key . '" value="numbered" /> ';
			$html .= __( 'Numbered Pagination (page 1, 2, 3, etc.)', 'themify' ) . '</label>';
			$html .= '<br/>';
			
			// Previous / Next links
			$html .= '<label for="' . $key . '_prevnext">';
			$html .= '<input ' . checked( $v, 'prevnext', false ) . ' type="radio" id="' . $key . '_prevnext" name="' . $key . '" value="prevnext" /> ';
			$html .= __( 'Previous Posts and Next Posts Links', 'themify' ) . '</label>';
		$html .= '</p>';
		return $html;
	}
}


/**
 * Renders Accessibility options
 *
 * @return string
 */
function themify_accessibility_options( $data = array() ) {
	$key = 'setting-acc_';
	$out = '<p>
				<span class="label">' . __( 'Link Focus Outline', 'themify' ) . '</span>
				<select name="' . $key.'lfo' . '">' . themify_options_module( array(
			array( 'name' => __( 'Light', 'themify' ), 'value' => '' ),
			array( 'name' => __( 'Heavy', 'themify' ), 'value' => 'h' ),
			array( 'name' => __( 'None', 'themify' ), 'value' => 'n' )
		), $key.'lfo' ) . '
				</select>
			</p>';
	$out .= '<p>
				<span class="label">' . __( 'General Font Size', 'themify' ) . '</span>
				<select name="' . $key.'fs' . '">' . themify_options_module( array(
			array( 'name' => __( 'Normal', 'themify' ), 'value' => '' ),
			array( 'name' => __( 'Large', 'themify' ), 'value' => 'l' ),
		), $key . 'fs' ) . '
				</select>
			</p>';
	return $out;
}

/**
 * Render the input field to allow uploading font packages
 *
 * @return string
 */
function themify_fontello_input_callback( $data = array() ) {
	return '
	<div class="themify_field_row">
		<span class="label">'. __('Fontello Icon Package', 'themify') . '</span>
		<input id="setting-fontello" type="text" class="width10" name="setting-fontello" value="' . esc_attr( themify_get( 'setting-fontello','',true ) ) . '" /> <br />
		<div class="pushlabel" style="display:block;">
			<div class="themify_medialib_wrapper">
				<a href="#" class="themify-media-lib-browse" data-submit=\'' . json_encode( array( 'action' => 'themify_handle_fontello_upload', 'field_name' => 'setting-fontello' ) ) . '\' data-uploader-title="' . __( 'Upload package', 'themify' ) .'" data-uploader-button-text="'. __( 'Upload package', 'themify' ) .'" data-fields="setting-fontello" data-type="application/zip">'. __( 'Browse Library', 'themify' ) . '</a>
			</div>
			<small class="description">' . __( 'Go to <a target="_blank" rel="noopener" href="http://fontello.com">fontello.com</a>, pick your icons, download the webfont zip, upload and insert the zip URL here. The icon package will be auto detected on Themify\'s icon library where you click "Insert Icon".', 'themify' ) . '</small>
			<small class="description">' . sprintf( __( '<a href="%s">Full Tutorial</a>' ), 'https://themify.me/blog/how-to-add-custom-icon-fonts' ) . '</small>
		</div>
	</div>';
}

if(!function_exists('themify_performance_settings')) {
	/**
	 * Script Minification Settings
	 * @param array Themify data
	 * @return string Module markup
	 * @since 1.3.9
	 */
	function themify_performance_settings($data = array()){
		$server=themify_get_server();
		$htaccess_file=$server==='nginx'?null:Themify_Enqueue_Assets::getHtaccessFile();
                $isMultiSite=is_multisite();
		$cache_dir=TFCache::get_wp_content_dir();
		if ($htaccess_file!==null && Themify_Filesystem::is_file($htaccess_file) && Themify_Filesystem::is_writable($htaccess_file)) {
		   $message= sprintf(__('Enabling Gzip will add code to your .htaccess file (%s)','themify'),$htaccess_file);
		   $gzip=themify_check( 'setting-cache_gzip',true );
                   $browser_cache=themify_check( 'setting-cache_browser',true );
		}
		else{
                    $message= $server!=='apache'?sprintf(__('It looks like you are using Nginx server. Please <a href="%s" target="_blank" rel="noopener">follow</a> this tutorial to enable this feature.','themify'),'https://themify.me/docs/enable-gzip-nginx-servers'):sprintf(__('The htaccess file %s isn`t writable. Please allow to write to enable this feauture','themify'),$htaccess_file);
		    $gzip=$browser_cache=null;
		}
		$cache_plugins=false!==TFCache::get_cache_plugins();
		$menuCache='data-show-if-element="[name$=setting-cache-html]" data-show-if-value="false"';
		$warning=$tmp='';
		if($cache_plugins===true){
			$menuCache='style="display:none"';
		    $warning=__('Themify Cache can not be enabled due to another cache plugin is activated.','themify');
		}
		elseif(!Themify_Filesystem::is_writable($cache_dir)){
		    $warning=sprintf(__('The directory %s isn`t writable. Please allow to write to enable this feauture','themify'),$cache_dir);
		}
		elseif(!WP_CACHE){
		    $tmp='<div class="pushlabel themify_warning note">'.__('WP_CACHE is not enabled. Please enable it on wp-config.php file. ','themify');
		    $tmp.=' <a href="https://wordpress.org/support/article/editing-wp-config-php/#cache" target="_blank" rel="noopener">'.__('Read details','themify').'</a>.';
		    if(Themify_Filesystem::is_writable(ABSPATH . 'wp-config.php' )){
			$tmp.='<br/><br/><a href="#" data-action="themify_write_config" data-send="all" data-clearing-text="'.__('Writing...','themify').'" data-done-text="'.__('Done','themify').'" data-default-text="'.__('Try to fix it','themify').'" data-default-icon="ti-eraser" class="button button-outline js-clear-cache"> <span>'.__('Try to fix it','themify').'</span></a>';
		    }
		    $tmp.='</div>';
		}
		
		
		$ignore_cache=array(
		    'is_single'=>__('Exclude Single Posts','themify'),
		    'is_page'=>__('Exclude Pages','themify'),
		    'is_front_page'=>__('Exclude Front Page','themify'),
		    'is_home'=>__('Exclude Home','themify'),
		    'is_archives'=>__('Exclude Archives','themify'),
		    'is_tags'=>__('Exclude Tags','themify'),
		    'is_category'=>__('Exclude Category','themify'),
		    'is_feed'=>__('Exclude Feeds','themify'),
		    'is_author'=>__('Exclude Author Pages','themify')
		);
		if(themify_is_woocommerce_active()){
		    $ignore_cache['is_shop']=__('Exclude Shop Page','themify');
		    $ignore_cache['is_product']=__('Exclude Single Products','themify');
		    $ignore_cache['is_product_category']=__('Exclude Product Categories','themify');
		    $ignore_cache['is_product_tag']=__('Exclude Product Tags','themify');
		}
		$key='setting-cache-live';
		$tmp.='<p><span class="label">' . __( 'Cache Expires', 'themify' ) . '</span><label class="pushlabel"><input type="text" name="'.$key.'" value="'.themify_get($key,(WEEK_IN_SECONDS/60),true).'" class="width4"> '.__('Minutes (default 1 week)','themify').'</label></p>';
		
		$key='setting-cache-ignore';
		$tmp.='<span class="label">' . __( 'Exclude Caching On', 'themify' ) . '</span><div class="pushlabel">';
		foreach($ignore_cache as $k=>$v){
		    $tmp.='<label>';
		    $tmp.='<input type="checkbox" value="'.$k.'" id="'.$key.'" name="'.$key.'_'.$k.'" '. checked( themify_check($key.'_'.$k,true ),true,false ) .'/> ' . $v . '</label>';
		    $tmp.='</label><br/>';
		}
		$tmp.='</div>';
		$disableJsLazy=$cache_plugins===true;
		$ignore_cache=null;
		$pageCache=$warning==='' && Themify_Filesystem::is_file(TFCache::get_cache_config_file());
		$key = 'setting-disable-lazy';
		$output='<div>
			<div class="themify-info-link">To maximize your pagespeeed performance, please follow <a href="https://themify.me/blog/get-90-score-google-pagespeed-insights" target="_blank">this tutorial</a> to setup your theme and content.</div>
			<span class="label">' . __( 'Themify Lazy Load', 'themify' ) .themify_help(__('Lazy load can speed up pagespeed by loading media (image, audio, video, iframe, etc.) when they are visible in the viewport.','themify')) . '</span>';
		
			$output.='<label for="'.$key.'"><input type="checkbox" id="'.$key.'" name="'.$key.'" '. checked( themify_check($key,true ),true, false ) .'/> ' . __('Disable lazy load', 'themify') . '</label><br/>';
			
			$output.='<div data-show-if-element="[name$='.$key.']" data-show-if-value="false" class="pushlabel'.($disableJsLazy===false?' themify_field_disable':'').'"><label for="'.$key.'-native"><input type="checkbox"'.($disableJsLazy===false?' disabled="disabled"':'').' id="'.$key.'-native" name="'.$key.'-native" '. checked(($disableJsLazy===true?themify_check($key.'-native',true ):false),true, false ).'/> ' . __('Use native lazy load', 'themify').themify_help(__('Themify can detect and use native lazy load if the browser is compatible(e.g safari doesn`t support native lazy load). If you are using a third party cache plugin, this option will be available which allow you to enable native lazy load instead of using Javascript.','themify')) . '</label></div>';
			
			$output.='<div data-show-if-element="[name$='.$key.']" data-show-if-value="false" class="tf_clearfix">';
			$output .= sprintf( '<div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="0" data-max="100" data-default-min="0" data-default-max="100" class="width4" readonly> px</div>',
				esc_html__( 'Blur Image', 'themify' ),
				'setting-lazy-blur',
				themify_get('setting-lazy-blur',25,true )
			);
				
		$output.='</div>';
		
		$key = 'setting-dev-mode';
		$output.='<hr><p><span class="label">' . __( 'Development Mode', 'themify' ) .themify_help(__('Warning: the following will be disabled: Themify cache, menu cache, concate CSS caching, Gzip scripts, and minified scripts. Only enable this for development purposes (eg. preview child theme CSS/script changes).','themify')) . '</span>';
		
		$output.='<label for="'.$key.'"><input type="checkbox" id="'.$key.'" name="'.$key.'" '. checked(themify_check($key,true ),true, false ) .'/> ' . __('Enable development mode', 'themify') . '</label><br/>';
	
                $output.='<span data-show-if-element="[name$='.$key.']" data-show-if-value="true">';
                
                $output.='<label for="'.$key.'-concate" class="pushlabel"><input type="checkbox" id="'.$key.'-concate" name="'.$key.'-concate" '. checked(themify_check($key.'-concate',true ),true, false ) .'/> ' . __('Disable Concate CSS', 'themify') . '</label><br/>';
                
                $output.='<span  class="themify_warning">'.__('Warning: the following will be disabled: Themify cache, menu cache, concate CSS caching, Gzip scripts, and minified scripts. Only enable this for development purposes (eg. preview child theme CSS/script changes).','themify').'</span></span>';
		
                $output.='</p>';
		$output.='<div data-show-if-element="[name$='.$key.']" data-show-if-value="false">';//dev mode
		
		$key = 'setting-cache-html';
		$output.='<hr><p>
			<span class="label">' . __( 'Themify Cache', 'themify' ) .themify_help(__('Caching can reduce page size and server responses (only frontend public viewing will be cached). Themify Cache can not be used with third party cache plugins to avoid conflicts.','themify')) . '</span>
			<label for="'.$key.'"'.($warning!==''?' class="themify_field_disable"':'').'><input type="checkbox"'.($warning!==''?' disabled="disabled"':'').' id="'.$key.'" name="'.$key.'" '. checked( ($pageCache===true ?themify_check($key,true ):false),true,false ) .'/> ' . __('Enable Themify Cache (recommended)', 'themify') . '</label>
			<small class="pushlabel'.($warning!==''?' themify_warning':'').'" style="margin:20px 0;">'.$warning.'</small></p>';
		
		$output.='<div data-show-if-element="[name$='.$key.']" data-show-if-value="true">
			'.$tmp.'
			<span class="label">'. __( 'Excluding Caching URLs', 'themify' ) .'</span>
			<div class="pushlabel">
			<textarea class="widthfull" rows="5" name="setting-cache-rule" id="setting-cache-rule">'. esc_html( themify_get( 'setting-cache-rule','',true ) ).'</textarea>'
			.'<small>'.__('Enter the URLs to exclude caching (supports regular expressions). Separate each rule with a line break.','themify').'</small>';
		$output.='<br/><br/><a href="#" data-action="themify_clear_all_html" data-send="all" data-clearing-text="'.__('Clearing...','themify').'" data-done-text="'.__('Done','themify').'" data-default-text="'.__('Clear Themify Cache','themify').'" data-default-icon="ti-eraser" class="button button-outline js-clear-cache"><i class="ti-eraser"></i> <span>'.__('Clear Themify Cache','themify').'</span></a>';
		if($isMultiSite){
			$output.='<br/><label><input type="checkbox" value="1" id="tmp_cache_network" name="tmp_cache_network"/>'.__('Clear all network sites','themify').'</label>';
		}
		$output.='</div></div>';
		$tmp=null;
		$key = 'setting-script_minification-min';
		$output.='<hr><p>
			<span class="label">' . __( 'Minified Scripts', 'themify' ) .themify_help(__('Using minified version of scripts can reduce script file size by 10-20%.','themify')) . '</span>
			<label for="'.$key.'"><input type="checkbox" id="'.$key.'" name="'.$key.'" '. checked( themify_check($key,true ),true, false ) .'/> ' . __('Disable minified scripts (css/js files)', 'themify') . '</label>
		    </p>';
                $output.='<hr><p>
			<span class="label">' . __( 'Browser Caching', 'themify' ) .themify_help(__("Cache static assets (CSS, JS, images, etc.) on user's browser. HTML is not cached",'themify')) . '</span>
			<label for="setting-cache_browser"'.($browser_cache===null?' class="themify_field_disable"':'').'><input type="checkbox"'.($browser_cache===null?' disabled="disabled"':'').'id="setting-cache_browser" name="setting-cache_browser" '.checked( $browser_cache, true, false ).'/> ' . __('Enable Browser Caching (recommended)', 'themify').'</label>
		</p>';
		$output.='<hr><p>
			<span class="label">' . __( 'Gzip Scripts', 'themify' ) .themify_help(__('Using Gzip version of scripts can reduce script file size by 60-80%.','themify')) . '</span>
			<label for="setting-cache_gzip"'.($gzip===null?' class="themify_field_disable"':'').'><input type="checkbox"'.($gzip===null?' disabled="disabled"':'').'id="setting-cache_gzip" name="setting-cache_gzip" '.checked( $gzip, true, false ).'/> ' . __('Enable Gzip scripts (recommended)', 'themify').'</label>
			<small class="pushlabel'.($gzip===null?' themify_warning':'').'" >'.$message.'</small>
		</p>';
		if ( ! _wp_image_editor_choose() ) {
			$message=__('The GD library or Imagick extensions are not installed. Ask your host provider to enable them to use this feature.','themify');
			$webp=null;
		}
		else{
			$message='';
			$webp=themify_check( 'setting-webp',true );
			if ( $server === 'litespeed' ) {
				$message = sprintf( __( 'It looks like you are using LiteSpeed server. Please follow <a href="%s" target="_blank" rel="noopener">this tutorial</a> to enable WebP images on your server.', 'themify' ), 'https://docs.litespeedtech.com/lscache/lscwp/imageopt/' );
				$webp = null;
			} else if ( $server !== 'apache' ) {
				$message=sprintf(__('It looks like you are using %s server. Please <a href="%s" target="_blank" rel="noopener">follow</a> this tutorial to enable this feature.','themify'), ucfirst($server),'https://themify.me/docs/enable-webp-nginx-servers');
			}
		}
		$webp_quality = (int) themify_get( 'setting-webp-quality', '5', true );
		$output.='</p>';
		$output.='<hr><p>
			<span class="label">' . __( 'Concate CSS', 'themify' ) . '</span>
			<a href="#" data-action="themify_clear_all_concate" data-send="all" data-clearing-text="'.__('Clearing...','themify').'" data-done-text="'.__('Done','themify').'" data-default-text="'.__('Clear Concate CSS Cache','themify').'" data-default-icon="ti-eraser" class="button button-outline js-clear-cache"><i class="ti-eraser"></i> <span>'.__('Clear Concate CSS Cache','themify').'</span></a>
		';
		if(!Themify_Enqueue_Assets::createDir()){
			$output.='<span class="pushlabel themify_warning">'.__('It looks like the WordPress upload folder path is set wrong or have file permission issue. Please check the upload path on WP Settings > Media. Make sure the folder is set correctly and it has correct file permission.','themify').'</span>';
		}
                elseif($isMultiSite){
			$output.='<br/><label class="pushlabel"><input type="checkbox" value="1" id="tmp_cache_concte_network" name="tmp_cache_concte_network"/>'.__('Clear Concate cache in the whole network site','themify').'</label>';
		}
		$output.='</p>';
		$key='setting-cache-menu';
		$output.='<div '.$menuCache.'><hr><p>
			<span class="label">' . __( 'WordPress Menus Cache', 'themify' ) .themify_help(__('Caching WordPress menus can reduce queries. Will only work when there is no active cache plugin. If you have server cache, you can disable this feature.', 'themify')) . '</span>';
		if ( defined( 'POLYLANG_VERSION' ) ) {
			$output .= '<small class="pushlabel themify_warning">' . __( 'Themify menu cache has been disabled because Polylang is detected and it can not work with menu caching.', 'themify' ) . '</small>';
		} else {
			$output .= '<label for="'. $key.'"><input type="checkbox" id="'. $key.'" name="'. $key.'" '.checked(themify_check( $key,true ), true, false ).'/> ' . __('Disable menu cache', 'themify').'</label>';
		}
		$output .= '</p></div>';

		$output.='</div>';//end of dev mode
		
		$output.='<hr><p>
			<span class="label">' . __( 'WebP Image', 'themify' ) .themify_help(__('Using WebP image format can reduce file size by 50-90%. Only local images will be converted. The CDN and external images can not be converted. For background images of Themify Builder, you have to regenerate CSS and .htaccess file must be writeable.','themify')) . '</span>
			<label for="setting-webp"'.($webp===null?' class="themify_field_disable"':'').'><input type="checkbox"'.($webp===null?' disabled="disabled"':'').'id="setting-webp" name="setting-webp" '.checked( $webp, true, false ).'/> ' . __('Enable WebP image (recommended)', 'themify').'</label>
			</p>
			<p>
			<span class="label">' . __( 'WebP Image Quality', 'themify' ) . themify_help( __( 'Lower quality has smaller file size, but image might appear pixelated/blurry.', 'themify' ) ) . '</span> <select' . ($webp===null ? ' disabled="disabled"' : '' ) . ' id="setting-webp-quality" name="setting-webp-quality">'
				. '<option value="1" ' . selected( $webp_quality, 1, false ) . '>' . __( 'Lowest', 'themify' ) . '</option>'
				. '<option value="2" ' . selected( $webp_quality, 2, false ) . '>' . __( 'Low', 'themify' ) . '</option>'
				. '<option value="3" ' . selected( $webp_quality, 3, false ) . '>' . __( 'Medium', 'themify' ) . '</option>'
				. '<option value="4" ' . selected( $webp_quality, 4, false ) . '>' . __( 'Good', 'themify' ) . '</option>'
				. '<option value="5" ' . selected( $webp_quality, 5, false ) . '>' . __( 'High', 'themify' ) . '</option>'
				. '<option value="6" ' . selected( $webp_quality, 6, false ) . '>' . __( 'Highest', 'themify' ) . '</option>'
			. '</select>
			</p>
			<span class="pushlabel"><a href="#" data-action="themify_clear_all_webp" data-clearing-text="'.__('Clearing...','themify').'" data-done-text="'.__('Done','themify').'" data-default-text="'.__('Clear WebP Images','themify').'" data-default-icon="ti-eraser" class="button button-outline js-clear-cache"><i class="ti-eraser"></i> <span>'.__('Clear WebP Images','themify').'</span></a></span>';
		if($message!==''){
			$output.='<small class="pushlabel themify_warning" >'.$message.'</small>';
		}
		$key='setting-jquery';
		$output.='<hr><p>
			<span class="label">' . __( 'jQuery Script', 'themify' ) .themify_help(__('Defer jQuery and all scripts can prevent render blocking. If your site/plugin(s) have inline jQuery code conflicting it, disable this option.','themify')) . '</span>
			<label for="'. $key.'"><input type="checkbox" id="'. $key.'" name="'. $key.'" '.checked(themify_check( $key,true ), true, false ).'/> ' . __('Defer jQuery script loading', 'themify').'</label>
		</p>';
		$key = 'setting-jquery-migrate';
		$output.='<p>
			<span class="label">' . __( 'jQuery Migrate', 'themify' ) .themify_help(__('Only enable this if your plugin(s) use the deprecated jQuery versions.','themify')) . '</span>
			<label for="'. $key.'"><input type="checkbox" id="'. $key.'" name="'. $key.'" '.checked(themify_check( $key,true ), true, false ).'/> ' . __('Enable jQuery Migrate', 'themify').'</label>
		</p>';

		if(themify_is_woocommerce_active()){
		    $key='setting-optimize-wc';
		    $output.='<hr><p>
				<span class="label">' . __( 'WooCommerce Script Optimization', 'themify' ) .themify_help(__('Themify loads WooCommerce scripts on demand for faster page load. If you are encountering issues with third party WooCommerce extensions, try to disable WooCommerce script optimization.','themify')) . '</span>
				<label for="'. $key.'"><input type="checkbox" id="'. $key.'" name="'. $key.'" '.checked(themify_check( $key,true ), true, false ).'/> ' . __('Disable WooCommerce script loading optimization', 'themify').'</label>
			</p>';
		    $key='setting-defer-wc';
		    $output.='<div data-show-if-element="[name$=setting-jquery]" data-show-if-value="false"><p data-show-if-element="[name$=setting-optimize-wc]" data-show-if-value="false">
				<span class="label">' . __( 'WooCommerce Script Defer', 'themify' ) .themify_help(__('WooCommerce scripts are deferred for faster page load. If you are encountering issues with third party WooCommerce extensions, try to disable script defer.','themify')) . '</span>
				<label for="'. $key.'"><input type="checkbox" id="'. $key.'" name="'. $key.'" '.checked(themify_check( $key,true ), true, false ).'/> ' . __('Disable WooCommerce script defer', 'themify').'</label>
			</p></div>';
		}
		$key='setting-emoji';
		$output.='<hr><p>
			<span class="label">' . __( 'WordPress Emoji', 'themify' ) .themify_help(__('If you are not using WordPress Emoji icons, keep it disabled to reduce script load.','themify')) . '</span>
			<label for="'. $key.'"><input type="checkbox" id="'. $key.'" name="'. $key.'" '.checked(themify_check( $key,true ), true, false ).'/> ' . __('Enable Emoji script loading', 'themify').'</label>
		</p>';
		return $output;
	}
}

if(!function_exists('themify_webfonts_subsets')) {
	/**
	 * Module to specify additional characters subsets
	 * @param array Themify data
	 * @return string Module markup
	 * @since 1.3.9
	 */
	function themify_webfonts_subsets($data = array()){
		
		$html='';
		// List of fonts, recommended or full
		if(!defined('THEMIFY_GOOGLE_FONTS') || THEMIFY_GOOGLE_FONTS == true){
		    $key = 'setting-gf';
		    $html='<p>
			<span class="label">' . __( 'Download Google Fonts', 'themify' ) .'</span>
			<label for="'.$key.'"><input type="checkbox" id="'.$key.'" name="'.$key.'" '. checked( themify_check($key,true ),true, false ) .'/> ' . __('Download Google Fonts to Local', 'themify') . '</label>
			<small class="pushlabel">'.__('Downloads all Google Fonts used in the theme and Builder to your local server. Note: Google Maps, YouTube and any embeds loaded in iframe are excluded as they are loaded in the iframe.','themify').'</small>
		    ';
		    $html.='<br/><span class="pushlabel"><a href="#" data-action="themify_clear_gfonts" data-send="all" data-clearing-text="'.__('Clearing...','themify').'" data-done-text="'.__('Done','themify').'" data-default-text="'.__('Clear Google Fonts Cache','themify').'" data-default-icon="ti-eraser" class="button button-outline js-clear-cache"><i class="ti-eraser"></i> <span>'.__('Clear Google Fonts Cache','themify').'</span></a></span></p><hr>';
		}
		$key = 'setting-webfonts_list';
		$html.= '<p>
					<span class="label">' . __('Google Fonts List', 'themify') . '</span>';

			// Disable Google fonts
			$html .= '<label for="' . esc_attr( $key . '_disabled' ) . '">
					<input ' . checked( themify_check( $key ) ? themify_get( $key ) : '', 'disabled', false ) . ' type="radio" id="' . esc_attr( $key . '_disabled' ) . '" name="' . esc_attr( $key ) . '" value="disabled" /> ' .  __( 'Disable Google fonts', 'themify' ) . '</label><br/>';

			// Recommended list
			$html .= '<span class="pushlabel">
					<label for="' . esc_attr( $key . '_recommended' ) . '">
					<input ' . checked( themify_check( $key )? themify_get( $key ) : 'recommended', 'recommended', false) . ' type="radio" id="' . esc_attr( $key . '_recommended' ) . '" name="' . esc_attr( $key ) . '" value="recommended" /> ' .  __( 'Show recommended Google Fonts only', 'themify' ) . '</label><br/>';

			// Full list
			$html .= '
					<label for="' . esc_attr( $key . '_full' ) . '">
					<input ' . checked( themify_check( $key )? themify_get( $key ) : '', 'full', false ) . ' type="radio" id="' . esc_attr( $key . '_full' ) . '" name="' . esc_attr( $key ) . '" value="full" /> ' . __( 'Show all Google Fonts (showing all fonts will take longer to load)', 'themify' ) . '</label>
					</span>
				</p>';
		return $html;
	}
}


/**
 * Renders the option to responsive design
 *
 * @since 2.1.5
 * @return string
 */
function themify_disable_responsive_design_option( $data = array() ) {
        $out = sprintf( '<p class="tf_clearfix"><span class="label width10">%s</span></p>', esc_html__( 'Responsive Breakpoints:', 'themify' ) );

	$opt_data = themify_get_data();
        $break_points = themify_get_breakpoints('',true);
	$pre = 'setting-customizer_responsive_design_';
	$bp_tablet_landscape = !empty( $opt_data[ $pre . 'tablet_landscape'] ) ? $opt_data[ $pre . 'tablet_landscape'] : 1024;
	$bp_tablet = !empty( $opt_data[ $pre . 'tablet'] ) ? $opt_data[ $pre . 'tablet'] : 768;
	$bp_mobile =!empty( $opt_data[ $pre . 'mobile'] ) ? $opt_data[ $pre . 'mobile'] : 600;
        
	$out .= sprintf( '<div class="tf_clearfix"><div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="%d" data-max="%d" data-default-min="%d" data-default-max="%d" class="width4"> px</div></div>',
		esc_html__( 'Tablet Landscape', 'themify' ),
		$pre . 'tablet_landscape',
		$bp_tablet_landscape,
		$bp_tablet_landscape < $break_points['tablet_landscape'][0] ? $bp_tablet_landscape : $break_points['tablet_landscape'][0],//min
		$bp_tablet_landscape > $break_points['tablet_landscape'][1] ? $bp_tablet_landscape : $break_points['tablet_landscape'][1],//max
		$break_points['tablet_landscape'][0],// default min
		$break_points['tablet_landscape'][1]// default max
	);
	$out .= sprintf( '<div class="tf_clearfix"><div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="%d" data-max="%d" data-default-min="%d" data-default-max="%d" class="width4"> px</div></div>',
		esc_html__( 'Tablet Portrait', 'themify' ),
		$pre . 'tablet',
		$bp_tablet,
		$bp_tablet < $break_points['tablet'][0] ? $bp_tablet : $break_points['tablet'][0],//min
		$bp_tablet > $break_points['tablet'][1] ? $bp_tablet : $break_points['tablet'][1],//max
		$break_points['tablet'][0],
		$break_points['tablet'][1]
	);
	$out .= sprintf( '<div class="tf_clearfix"><div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="%d" data-max="%d" data-default-min="%d" data-default-max="%d" class="width4"> px</div></div>',
		esc_html__( 'Mobile', 'themify' ),
		$pre . 'mobile',
		$bp_mobile,
		$bp_mobile < 320 ? $bp_mobile : 320,//min
		$bp_mobile > $break_points['mobile'] ? $bp_mobile : $break_points['mobile'],//max
        320,
		$break_points['mobile']
	);
	$out .= '
	<p>
		<span class="label">' . __( 'Mobile Menu', 'themify' ) .themify_help(__( 'Main menu will toggle to mobile menu style when viewport width meets the entered value.', 'themify' )) . '</span>
		<input type="text" name="setting-mobile_menu_trigger_point" value="' . esc_attr( Themify_Enqueue_Assets::$mobileMenuActive ) . '" class="width2">' . __( 'Mobile menu viewport (px)', 'themify' ) .'
	</p>';

	return $out;
}


if ( ! function_exists( 'themify_generic_slider_controls' ) ) {
	/**
	 * Creates a general module to setup slider parameters
	 * @param $prefix
	 * @return string
	 */
	function themify_generic_slider_controls( $prefix ) {
		/**
		 * Associative array containing theme settings
		 * @var array
		 */

		$auto_options = apply_filters( 'themify_generic_slider_auto',
			array(
				__('4 Secs (default)', 'themify') => 4000,
				__('Off', 'themify') => 'off',
				__('1 Sec', 'themify') => 1000,
				__('2 Secs', 'themify') => 2000,
				__('3 Secs', 'themify') => 3000,
				__('4 Secs', 'themify') => 4000,
				__('5 Secs', 'themify') => 5000,
				__('6 Secs', 'themify') => 6000,
				__('7 Secs', 'themify') => 7000,
				__('8 Secs', 'themify') => 8000,
				__('9 Secs', 'themify') => 9000,
				__('10 Secs', 'themify')=> 10000
			)
		);
		$speed_options = apply_filters( 'themify_generic_slider_speed',
			array(
				__('Fast', 'themify') => 500,
				__('Normal', 'themify') => 1000,
				__('Slow', 'themify') => 1500
			)
		);
		$effect_options = array(
			array('name' => __('Slide', 'themify'), 'value' => 'slide'),
			array('name' => __('Fade', 'themify'), 'value' =>'fade')
		);

		/**
		 * Auto Play
		 */
		$output = '<p>
						<span class="label">' . __('Auto Play', 'themify') . '</span>
						<select name="' . $prefix . 'autoplay">';
						foreach ( $auto_options as $name => $val ) {
							$v=themify_get( $prefix . 'autoplay' );
							$output .= '<option value="' . $val . '" ' . selected( $v, $v ? $val : 4000, false ) . '>' . esc_html( $name ) . '</option>';
						}
		$output .= '	</select>
					</p>';

		/**
		 * Effect
		 */
		$output .= '<p>
						<span class="label">' . __( 'Effect', 'themify' ) . '</span>
						<select name="' . $prefix. 'effect">' .
						themify_options_module( $effect_options, $prefix . 'effect' ) . '
						</select>
					</p>';

		/**
		 * Transition Speed
		 */
		$output .= '<p>
						<span class="label">' . __( 'Transition Speed', 'themify' ) . '</span>
						<select name="' . $prefix . 'transition_speed">';
						$transition=themify_get( $prefix . 'transition_speed' );
						foreach ( $speed_options as $name => $val ) {
							$output .= '<option value="' . $val . '" ' . selected( $transition, $transition? $val : 500, false ) . '>' . esc_html( $name ) . '</option>';
						}
		$output .= '	</select>
					</p>';

		return apply_filters( 'themify_generic_slider_controls', $output );
	}
}

/**
 * Display select element with featured image sizes + blank slot
 * @param String $key setting name
 * @return String
 * @since 1.1.5
 */
function themify_feature_image_sizes_select($key = ''){
	/** Define WP Featured Image sizes + blank + Themify's image script
	 * @var array */
	$themify_layout_feature_sizes = themify_get_image_sizes_list();

	$output = '<p class="show_if_disabled_img_php">
		    <span class="label">' . __('Featured Image Size', 'themify') . '</span>
		    <select name="setting-' . $key . '">';
	$val=themify_get('setting-'.$key,false,true);
	foreach($themify_layout_feature_sizes as $option){
		$sel=$option['value']===$val?' selected="selected"':'';
		$output .= '<option'.$sel.' value="' . $option['value'] . '">' . esc_html( $option['name'] ) . '</option>';
	}
	$output .= '</select></p>';

	return $output;
}

if ( ! function_exists( 'themify_theme_mega_menu_controls' ) ) {
/**
 * Mega Menu Controls
 * @param array $data Theme settings data
 * @return string Markup for module.
 * @since 3.5.8
 */
function themify_theme_mega_menu_controls( $data = array() ) {
	/**
	 * Theme Settings Option Key Prefix
	 *
	 * @var string
	 */
	$key = 'setting-mega_menu';

	/**
	 * Module markup
	 * @var string
	 */
	$out = '
	<p>
		<span class="label">' . __( 'Mega Menu Posts', 'themify' ) .themify_help(__( 'Number of posts to show on mega menu.', 'themify' )) . '</span>
		<input type="text" name="'.$key.'_posts" value="' . esc_attr( themify_get( $key.'_posts', 5,true ) ) . '" class="width2">' . __( 'Posts', 'themify' ) .'
	</p>';
	$out .= '
	<p>
		<span class="label">' . __( 'Mega Post Image', 'themify' ) .themify_help(__( 'Enter featured image size on mega menu posts.', 'themify' )) . '</span>
		<input type="text" name="'.$key.'_image_width" value="' . esc_attr( themify_get( $key.'_image_width', 180,true ) ) . '" class="width2"> X <input type="text" name="'.$key.'_image_height" value="' . esc_attr( themify_get( $key.'_image_height', 120,true ) ) . '" class="width2"> ' . __( 'px', 'themify' ) .'
	</p>';

	return apply_filters('themify_mega_menu_settings',$out);
}

}

/**
 * Display google map api key input
 * @return String
 * @since 2.7.7
 */
function themify_google_map_key($data=array()){
    return '
	<p>
		<span class="label">' . __( 'Google Map Key', 'themify' ) . '</span> <input type="text" class="width10" name="setting-google_map_key" value="' . esc_attr( themify_get( 'setting-google_map_key','',true) ) . '" /> <br />' .
		'<span class="pushlabel"><small>' . sprintf( __( 'Google API key is required to use Builder Map module and Map shortcode. <a href="%s" target="_blank" rel="noopener">Generate an API key</a> and insert it here. Also, please ensure you\'ve setup a <a href="%s" target="_blank" rel="noopener">billing plan</a>.' ), '//developers.google.com/maps/documentation/javascript/get-api-key#key', 'https://support.google.com/googleapi/answer/6158867' ) . '</small></span>' .
	'</p>';
}

/**
 * Display bing map api key input
 * @return String
 * @since 2.8.0
 */
function themify_bing_map_key($data=array()){
    return '<p><span class="label">' . __( 'Bing Maps Key', 'themify' ) . '</span> <input type="text" class="width10" name="setting-bing_map_key" value="' . esc_attr( themify_get( 'setting-bing_map_key','',true ) ) . '" /> <br />
				<span class="pushlabel"><small>' . sprintf( __( 'To use Bing Maps, <a href="%s" target="_blank" rel="noopener">generate an API key</a> and insert it here.' ), 'https://msdn.microsoft.com/en-us/library/ff428642.aspx' ) . '</small></span></p>';
}

/**
 * Display Cloudflare api key input
 * @return String
 * @since 2.8.0
 */
function themify_cloudflare_setting($data=array()){
    $key='setting-clf_email';
    $email=themify_get( $key,'',true );
    $output = '<p><span class="label">' . __( 'Account Email', 'themify' ) . '</span> <input type="email" class="width8" name="'.$key.'" value="' . esc_attr( $email ) . '" /></p>';
    $key='setting-clf_key';
    $api=themify_get( $key,'',true );
    $output .= '<p><span class="label">' . __( 'API Key', 'themify' ) . '</span> <input type="text" class="width10" name="'.$key.'" value="' . esc_attr( $api ) . '" /></p>';
    $key='setting-clf_z_'.crc32($email.$api);
    $zone=themify_get( $key,'',true );
    if(!empty($zone)){
        $output .= '<input type="hidden" name="'.$key.'" value="' . esc_attr( $zone ) . '" />';
    }
    $output .= '<p><span class="pushlabel"><small>' . sprintf( __( 'To clear Cloudflare cache upon theme update or purge Themify cache, please login to your Cloudflare account and insert your information here.' ) ) . '</small></span></p>';
    return $output;
}

/**
 * Display recaptcha api key input
 * @return String
 */
function themify_recaptcha_setting($data=array()){
    $version=Themify_Builder_Model::getReCaptchaOption( 'version');
    $options='';
    for($i=2;$i<4;$i++){
        $selected = $version=='v'.$i?' selected="selected"':'';
        $options.='<option value="v'.$i.'"'.$selected.'>Version '.$i.'</option>';
    }
    $output='<p><span class="label">' . __( 'ReCaptcha Version', 'themify' ) . '</span><select name="setting-recaptcha_version">'.$options.'</select></p>';
    $output.='<p><span class="label">' . __( 'ReCaptcha Site Key', 'themify' ) . '</span> <input type="text" class="width10" name="setting-recaptcha_site_key" value="' . esc_attr( Themify_Builder_Model::getReCaptchaOption( 'public_key') ) . '" /></p>';
    $output.='<p><span class="label">' . __( 'ReCaptcha Secret Key', 'themify' ) . '</span> <input type="text" class="width10" name="setting-recaptcha_secret_key" value="' . esc_attr( Themify_Builder_Model::getReCaptchaOption( 'private_key') ) . '" /><br/><span class="pushlabel"><small>'.sprintf( __( 'Go to <a target="_blank" href="%s" rel="noopener">reCAPTCHA Admin Console</a> to create API keys for your domain. A Google account is required.', 'themify' ), 'https://www.google.com/recaptcha/admin' ).'</small></span></p>';;
    return $output;
}

/**
 * Display video gdpr
 * @return String
 */
function themify_video_gdpr($data=array()){
    $key='setting-gdpr';
    $imgKey=$key.'-img';
    $media=array(
	'fields' => $imgKey,
	'medialib'=>1,
	'preview' => true
    );
    $imgVal=themify_get( $imgKey,'',true );
    $output = '
	<p>
		<label class="label" for="'.$key.'">' . __( 'GDPR', 'themify' ) . '</label>
		<select name="'.$key.'" .id="'.$key.'">
			<option value="">' . __( 'Disabled', 'themify' ) . '</option>
			<option value="1" ' . selected( 'on', themify_get($key,'',true ), false ) . '>' . __( 'Enable', 'themify' ) . '</option>
		</select>
		<span class="pushlabel">
			<small class="description">' . __( 'Once it is enabled, only logged-in users can see your site.', 'themify' ) . '</small>
		</span>
	</p>';
	$output .= '
	<div data-show-if-element="[name='.$key.']" data-show-if-value="1">
	    <label class="label" for="'.$imgKey.'">' . __( 'Show Image', 'themify' ) . '</label>
		<div class="icon-preview">
					<img id="' . $imgKey . '-preview" src="' . $imgVal . '" loading="lazy" decoding="async" />
				</div>
	    <input id="'.$imgKey.'" type="text" class="width10" name="'.$imgKey.'" value="' . $imgVal . '" />
	    '.themify_get_uploader($imgKey,$media).'
	</div>
	';
	return $output;
}

/**
 * Add Maintenance mode option
 * @return string
 * @since 4.5.8
 */
function themify_maintenance_mode_settings() {
	$pre = 'setting-page_builder_';
	$value = themify_builder_get( $pre . 'maintenance_mode', 'tools_maintenance_mode' );
	$output = '
	<p>
		<label class="label" for="tb_maintenance_mode">' . __( 'Maintenance', 'themify' ) . '</label>
		<select name="' . $pre . 'maintenance_mode">
			<option value="">' . __( 'Disabled', 'themify' ) . '</option>
			<option value="on" ' . selected( 'on', $value, false ) . '>' . __( 'Enable and display a page', 'themify' ) . '</option>
			<option value="message" ' . selected( 'message', $value, false ) . '>' . __( 'Enable and display a message', 'themify' ) . '</option>
		</select>
		<span class="pushlabel">
			<small class="description">' . __( 'Once it is enabled, only logged-in users can see your site.', 'themify' ) . '</small>
		</span>
	</p>';

	$message = themify_builder_get( 'setting-maintenance_message', 'tools_maintenance_message' );
	$output .= '
	<div data-show-if-element="[name=setting-page_builder_maintenance_mode]" data-show-if-value="message">
		<div class="pushlabel">
			<textarea name="setting-maintenance_message" class="width10">' . esc_html( $message ) . '</textarea>
		</div>
	</div>
	';

	$selected_value = themify_builder_get( 'setting-page_builder_maintenance_page', 'tools_maintenance_page' );
	$selected_page = empty($selected_value) ? '' : get_page_by_path( $selected_value, OBJECT, 'page' );
	$output .= sprintf( '<div data-show-if-element="[name=setting-page_builder_maintenance_mode]" data-show-if-value="on"><label class="label" for="%s">%s</label><select id="%s" name="%s">%s<option>%s</option></select><div data-show-if-element="[name=page_builder_maintenance_mode]" data-show-if-value="true" class="pushlabel"><small>%s</small></div></p>',
		$pre . 'maintenance_page',
		__( 'Maintenance Page', 'themify' ),
		$pre . 'maintenance_page',
		$pre . 'maintenance_page',
		!is_object($selected_page) ? '<option></option>' : sprintf('<option value="%s" selected="selected">%s</option>',$selected_value,$selected_page->post_title),
		__( 'Loading...', 'themify' ),
		__( 'Select a page to show for public users.', 'themify' )
	);

	return $output;
}

/**
 * Add Google Analytics option
 * @return string
 * @since 5.5.5
 */
function themify_google_analytics_settings() {
    $m_id = themify_get( 'setting-ga_m_id','',true );
    return '<p><span class="label">' . __( 'Measurement ID', 'themify' ) . '</span> <input type="text" name="setting-ga_m_id" value="' . esc_attr( $m_id ) . '" /> <br />
		<span class="pushlabel"><small>' .
			sprintf( __( '<a href="%s">Set up Analytics for your website</a>. Afterwards, go to your <a href="%s">Analytics admin dashboard</a> and select Data Streams, click on the item you want, then copy the Measurement ID (starts with "G-") and paste it here.', 'themify' ), 'https://support.google.com/analytics/answer/9304153', 'https://support.google.com/analytics/answer/6132368' )
		. '</small></span></p>';
}


/**
 * Callback for themify_framework_theme_microdata_config(), to display the options
 *
 * @return string
 */
function themify_framework_theme_microdata_config_callback() {
	return '<p><span class="label">' . __('Schema Microdata', 'themify') . '</span> <label for="setting-disable_microdata"><input type="checkbox" id="setting-disable_microdata" name="setting-disable_microdata" '. checked( 'on', themify_get( 'setting-disable_microdata','',true ), false ) .'/> ' . __('Disable schema.org microdata output.', 'themify') . '</label></p>';
}


/**
 * Display Builder Styles page content
 * @return String
 * @since 4.5.0
 */
function themify_global_styles_page(){
	
	if ( ! current_user_can( 'edit_posts' ) )
		wp_die( __( 'You do not have sufficient permissions to update this site.', 'themify' ) );

	return Themify_Global_Styles::page_content();
}


///////////////////////////////////////////
// Scrub Function
///////////////////////////////////////////
function themify_scrub_func($string=''){
	return str_replace(array(' ', '/', ','), array('_', '_', '-'), strtolower($string));
}

///////////////////////////////////////////
// Scrub
///////////////////////////////////////////
function themify_scrub($string=''){
	return str_replace(array('#','-',' ','.',':',',','[',']','=','<','>'), array('_id_','_dash_','_space_','_class_','_colon_','_comma_','_opensquare_','_closesquare_','_equal_','_openbracket_','_closebracket_'), $string);
}
/**
 * Check if multiple plugins are active, returns true only if all of them are
 *
 * @return bool
 * @since 2.8.6
 */
function themify_are_plugins_active( $plugins ) {
	foreach( $plugins as $plugin ) {
	    if( ! is_plugin_active( $plugin ) ) {
		return false;
	    }
	}

	return true;
}

if ( ! function_exists( 'themify_lightbox_link_field' ) ) {
	/**
	 * Returns Lightbox Link field definition for themify custom panel
	 * @return array
	 */
	function themify_lightbox_link_field( $args = array() ) {

		$defaults = array(
			'name' 	=> 'multi_lightbox_link',
			'title' => __('Lightbox Link', 'themify'),
			'description' => '',
			'type' 	=> 'multi',
			'meta'	=> array(
				'fields' => array(
			  		// Lightbox link field
			  		array(
						'name' 	=> 'lightbox_link',
						'label' => '',
						'description' => __('Link Featured Image and Post Title to lightbox image, video or iframe URL <br/>(<a href="https://themify.me/docs/lightbox" target="_blank" rel="noopener">learn more</a>)', 'themify'),
						'type' 	=> 'textbox',
						'meta'	=> array(),
						'before' => '',
						'after' => '',
					),
					array(
						'name' 		=> 'iframe_url',
						'label' 		=> __('iFrame URL', 'themify'),
						'description' => '',
						'type' 		=> 'checkbox',
						'before' => '',
						'after' => '',
					),
					array(
						'name' 		=> 'lightbox_icon',
						'label' 		=> __('Add zoom icon on lightbox link', 'themify'),
						'description' => '',
						'type' 		=> 'checkbox',
						'before' => '',
						'after' => '',
					)
				),
				'description' => '',
				'before' => '',
				'after' => '',
				'separator' => ''
			)
		);

		$field = wp_parse_args( $args, $defaults );

		return apply_filters( 'themify_lightbox_link_field', $field );
	}
}

if( ! function_exists( 'themify_image_dimensions_field' ) ) {
	/**
	 * Multi field: Image dimensions fields to enter width and height.
	 * @param array $args
	 * @param string $prefix
	 * @return mixed|void
	 * @since 1.5.2
	 */
	function themify_image_dimensions_field( $args = array(), $prefix = 'image' ) {
		if(!themify_is_image_script_disabled()){
			$defaults = array(
				'type' => 'multi',
				'name' => $prefix . '_dimensions',
				'title' => __('Featured Image Size', 'themify'),
				'meta' => array(
					'fields' => array(
						// Image Width
						array(
							'name' => $prefix . '_width',
							'label' => __('width', 'themify'),
							'description' => '',
							'type' => 'textbox',
							'meta' => array('size' => 'small')
						),
						// Image Height
						array(
							'name' => $prefix . '_height',
							'label' => __('height', 'themify'),
							'type' => 'textbox',
							'meta' => array( 'size' => 'small')
						),
					),
					'description' => __('Enter height = 0 to disable vertical cropping with image script enabled', 'themify'),
					'before' => '',
					'after' => '',
					'separator' => ''
				)
			);
		} else {
			$defaults = array( 'name'=>'','type'=>'' );
		}
		$field = wp_parse_args( $args, $defaults );

		return apply_filters( 'themify_image_dimensions_field', $field );
	}
}


/**
 * Returns a collection of options: yes, no and default which means the theme settings will be used.
 *
 * @since 2.1.3
 *
 * @param string $yes
 * @param string $no
 * @param string $default
 * @param array $args
 *
 * @return array
 */
function themify_ternary_options( $yes = '', $no = '', $default = '', $args = array() ) {
	return wp_parse_args( $args, array(
		array(
			'value' => 'default',
			'name'  => !empty( $default ) ? $default : __( 'Default', 'themify' ),
			'selected' => true
		),
		array(
			'value'    => 'yes',
			'name'     => !empty( $yes ) ? $yes : __( 'Yes', 'themify' ),
		),
		array(
			'value' => 'no',
			'name'  => !empty( $no ) ? $no : __( 'No', 'themify' ),
		),
	));
}

/**
 * Returns a collection of states: yes, no and default which means the theme settings will be used.
 *
 * @since 2.1.3
 *
 * @param string $yes
 * @param string $no
 * @param string $default
 * @param array $args
 *
 * @return array
 */
function themify_ternary_states( $args = array(), $all = array() ) {
	$args = wp_parse_args( $args, array(
		'icon_yes' => THEMIFY_URI . '/img/ddbtn-check.svg',
		'icon_no' => THEMIFY_URI . '/img/ddbtn-cross.svg',
		'value_default' => '',
	) );
	return wp_parse_args( $all, array(
		array(
			'name' => empty( $args['label_yes'] ) ? __('Hide', 'themify') : $args['label_yes'],
			'value' => 'yes',
			'icon' => $args['icon_yes'],
			'title' => __('Hide this', 'themify')
		),
		array(
			'name' => empty( $args['label_no'] ) ? __('Show', 'themify') : $args['label_no'],
			'value' => 'no',
			'icon' => $args['icon_no'],
			'title' => __('Show this', 'themify')
		),
		array(
			'name' => empty( $args['default'] ) ? __('Theme default', 'themify') : $args['default'],
			'value' => $args['value_default'],
			'icon' => THEMIFY_URI . '/img/ddbtn-blank.png',
			'title' => __('Use theme settings', 'themify'),
			'default' => true
		)
	));
}
if( ! function_exists( 'themify_multi_meta_field' ) ) {
	/**
	 * Definition for tri-state hide meta buttons
	 *
	 * @param array  $args
	 * @param string $prefix
	 *
	 * @return mixed|void
	 * @since 1.5.2
	 */
	function themify_multi_meta_field( $args = array(), $prefix = 'hide_meta' ) {

		$states = themify_ternary_states( array( 'label_no' => __('Do not hide', 'themify') ) );

		$defaults = array(
			'name' 		=> $prefix . '_multi',
			'title' 	=> __('Hide Post Meta', 'themify'),
			'description' => '',
			'type' 		=> 'multi',
			'meta'		=>  array (
				'fields' => array(
					array(
						'name' => $prefix . '_all',
						'title' => __('Hide All', 'themify'),
						'description' => '',
						'type' => 'dropdownbutton',
						'states' => $states,
						'main' => true,
						'disable_value' => 'yes'
					),
					array(
						'name' => $prefix . '_author',
						'title' => __('Author', 'themify'),
						'description' => '',
						'type' => 'dropdownbutton',
						'states' => $states,
						'sub' => true
					),
					array(
						'name' => $prefix . '_category',
						'title' => __('Category', 'themify'),
						'description' => '',
						'type' => 'dropdownbutton',
						'states' => $states,
						'sub' => true
					),
					array(
						'name' => $prefix . '_comment',
						'title' => __('Comment', 'themify'),
						'description' => '',
						'type' => 'dropdownbutton',
						'states' => $states,
						'sub' => true
					),
					array(
						'name' => $prefix . '_tag',
						'title' => __('Tag', 'themify'),
						'description' => '',
						'type' => 'dropdownbutton',
						'states' => $states,
						'sub' => true
					),
				),
				'description' => '',
				'before' => '',
				'after' => '',
				'separator' => ''
			)
		);

		$field = wp_parse_args( $args, $defaults );

		return apply_filters( 'themify_multi_meta_field', $field );
	}
}

function themify_meta_field_fontawesome( $args, $call_before_after = true, $echo = true ) {
	
	$meta_box =  $args['meta_box'];
	$meta_value = $args['meta_value'];
	$class = isset( $meta_box['meta']['size'] ) && 'small' === $meta_box['meta']['size']?'small': '';

	$html = '<div class="icon-preview font-icon-preview">
				<i class="fa ' . esc_attr( $meta_value ) . '"></i>
			</div>
			<!-- /icon-preview -->';
	$html .= sprintf( '<input type="text" id="%s" name="%s" value="%s" size="55" class="themify_input_field themify_fa %s" /> <a class="button button-secondary hide-if-no-js themify_fa_toggle" href="#" data-target="#%s">%s</a>',
		esc_attr( $meta_box['name'] ),
		esc_attr( $meta_box['name'] ),
		esc_attr( $meta_value ),
		$class,
		esc_attr( $meta_box['name'] ),
		__( 'Insert Icon', 'themify' ) );

	if (  !empty( $meta_box['label'] )) {
		$html = sprintf( '<label for="%s">%s %s</label>',
			esc_attr( $meta_box['name'] ),
			$html,
			esc_html( $meta_box['label'] )
		);
	}

	if ( isset( $meta_box['description'] ) ) {
		$html .= themify_meta_field_get_description( $meta_box['description'] );
	}

	if ( !empty( $meta_box['before'] )) {
		$html = $meta_box['before'] . $html;
	}
	if ( !empty( $meta_box['after'] )) {
		$html .= $meta_box['after'];
	}

	if ( $echo ) echo $html;
	return $html;
}
/**
 * Build custom write panels
 * This function is required to provide backward compatibility
 */
function themify_build_write_panels( $args = null ) {
}
/**
 * featimgdropdown field type, creates an option to select image sizes
 *
 * @since 2.8.8
 */
function themify_meta_field_featimgdropdown( $args ) {
	/** Define WP Featured Image sizes + blank + Themify's image script*/
	$themify_fi_sizes = themify_get_image_sizes_list();
	$meta_box = $args['meta_box'];
	?>
	<select name="<?php  esc_attr_e( $meta_box['name'] ); ?>">
		<?php foreach($themify_fi_sizes as $option): ?>
			<option value="<?php  esc_attr_e( $option['value'] ); ?>" <?php selected( $option['value'], $args['meta_value'] ); ?>><?php echo esc_html( $option['name'] ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php if ( isset( $meta_box['description'] ) ) : ?>
		<span class="themify_field_description"><?php echo wp_kses_post( $meta_box['description'] ); ?></span>
	<?php endif; // meta_box description
}
/**
 * Outputs html to display alert messages in post edit/new screens. Excludes pages.
 * @since 1.2.2
 */
function themify_prompt_message() {
	global $pagenow, $typenow;
	if('page' !== $typenow && ('post.php' === $pagenow || 'post-new.php' === $pagenow || 'admin.php' === $pagenow) ){
		echo '<div class="prompt-box"><div class="show-error"></div></div><div class="overlay">&nbsp;</div>';
	}
	if(class_exists('Themify_Builder_Model')){
		Themify_Builder_Model::check_plugins_compatible();
	}
	if(!Themify_Enqueue_Assets::createDir()){
		echo '<div class="notice notice-error"><p><strong>'.__('Themify:','themify').'</strong></p><p>'.__('It looks like the WordPress upload folder path is set wrong or have file permission issue. Please check the upload path on WP Settings > Media. Make sure the folder is set correctly and it has correct file permission.','themify').'</p></div>';
	}
}
add_action( 'admin_enqueue_scripts', 'themify_enqueue_scripts', 12 );

// register custom field types only available in the framework
function themify_meta_field_page_layout( $args ) {
	$page_layout = get_post_meta( get_the_ID(), 'page_layout', true );
	$content_width = get_post_meta( get_the_ID(), 'content_width', true );
	$section_scroll = get_post_meta( get_the_ID(), 'section_full_scrolling', true );
	if ( $section_scroll === 'yes' ) {
		$args['meta_value'] = 'section_scroll';
	} else if ( $content_width === 'full_width' ) {
		$args['meta_value'] = 'full_width';
	}

	echo '<div class="themify_field-layout">';
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract( $args, EXTR_OVERWRITE );
	$ops_html = '';

	foreach ( $meta_box['meta'] as $options ) {
		if ( ( '' == $meta_value || !$meta_value || !isset($meta_value)) && ( isset( $options['selected'] ) && $options['selected'] ) ) {
			$meta_value = $options['value'];
		}
		$class = $meta_value == $options['value']?'selected':'';

		if(isset($meta_box['show_title'])){
			$title = isset($options['title'])? $options['title']: ucwords(str_replace('-', ' ', $options['value']));
		} else {
			$title = '';
		}
                $w= isset($option['w'])?$option['w']:'46';
                $h= isset($option['w'])?$option['w']:'35';
		// Check image src whether absolute url or relative url
		$img_src = ( '' != parse_url( $options['img'], PHP_URL_SCHEME) ) ? $options['img'] : get_template_directory_uri() . '/' . $options['img'];

		$ops_html .= sprintf('<a href="#" class="preview-icon %s"><img src="%s" alt="%s" width="'.$w.'" height="'.$h.'" loading="lazy" decoding="async"/><span class="tm-option-title">%s</span></a>',
			$class, 
			esc_url( $img_src ), 
			esc_attr( $options['value'] ),
			$title
		);
	}

	$page_layout = empty( $page_layout ) ? 'default' : $page_layout;

	/* data-selected is the selected option, whereas 'value' is the thing stored in db */
	$html = sprintf('%s<input type="hidden" name="%s" value="%s" class="val" data-selected="%s" />',
		$ops_html, esc_attr( $meta_box['name'] ), esc_attr( $page_layout ), $meta_value );

	if(isset($meta_box['label']) && '' != $meta_box['label'])
		$html = sprintf('<label for="%s">%s %s</label>', esc_attr( $meta_box['name'] ), $html, esc_attr( $meta_box['label'] ));

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) )  $html .= $meta_box['after'];

	echo $html;
	echo '</div>';
}
add_action( 'themify_metabox/field/page_layout', 'themify_meta_field_page_layout', 10, 3 );
add_action( 'themify_metabox/field/fontawesome', 'themify_meta_field_fontawesome', 10, 1 );
add_action( 'themify_metabox/field/sidebar_visibility', 'themify_meta_field_sidebar_visibility', 10, 1 );
add_action( 'themify_metabox/field/featimgdropdown', 'themify_meta_field_featimgdropdown', 10, 1 );
add_action( 'themify_metabox/field/page_builder', 'themify_meta_field_page_builder', 10, 1 );
add_action( 'admin_notices', 'themify_prompt_message' );

require_once THEMIFY_DIR . '/themify-import-functions.php';
require_once THEMIFY_DIR . '/themify-wpajax.php';

if ( themify_is_themify_theme() ) {

	/**
	 * In this hook current user is authenticated so we can check for capabilities.
	 *
	 * @since 2.1.8
	 */
	function themify_after_user_is_authenticated() {
		if ( current_theme_supports( 'themify-exclude-theme-from-wp-update' ) ) {
			add_filter( 'http_request_args', 'themify_hide_themes', 10, 2 );
		}
        /**
         * Themify - Admin Menu
         *******************************************************/
        add_action( 'admin_menu', 'themify_admin_nav', 1 );
        if ( current_user_can( 'manage_options' ) ) {
			add_action( 'load-toplevel_page_themify', function() {
				if ( isset( $_GET['tf_clean_gz'] ) ) {
					// clean gz files from the theme
					themify_remove_gzip( THEME_DIR );
					// clean gz files from all Themify plugins
					$plugins = get_plugins();
					foreach ( $plugins as $path => $data ) {
						if ( isset( $data['Author'] ) && $data['Author'] === 'Themify' ) {
							themify_remove_gzip( trailingslashit( WP_PLUGIN_DIR ) . dirname( $path ) );
						}
					}
				}
			} );
		}
	}
	add_action( 'init', 'themify_after_user_is_authenticated' );

	/**
	 * Hijacks themes passed for upgrade checking and remove those from Themify
	 *
	 * This feature is only required for legacy themes without "themify-" prefix,
	 * to prevent updates from wp.org overwriting theme files.
	 *
	 * @param Bool
	 * @param Array $r List of themes
	 * @param String $url URL of upgrade check
	 * @return Array
	 * @since 1.1.8
	 */
	function themify_hide_themes( $response, $url ){
		if ( 0 === strpos( $url, 'https://api.wordpress.org/themes/update-check' ) ) {
			$themes = json_decode( $response['body']['themes'] );
			unset( $themes->themes->{get_option( 'template' )},$themes->themes->{get_option( 'stylesheet' )} );
			$response['body']['themes'] = json_encode( $themes );
		}

		return $response;
	}

    require_once THEMIFY_DIR . '/class-tgm-plugin-activation.php';
}

/**
 * List all files in a directory, recursively
 *
 * @return array
 */
function themify_glob_recursive( $pattern, $flags = 0 ) {
	$files = glob( $pattern, $flags );
	foreach ( glob( dirname( $pattern ) . '/*', GLOB_ONLYDIR|GLOB_NOSORT ) as $dir ) {
		$files = array_merge( $files, themify_glob_recursive( $dir . '/' . basename( $pattern ), $flags ) );
	}

	return $files;
}

/**
 * Removes all .gz files found in the $path
 */
function themify_remove_gzip( $path ) {
	$search = themify_glob_recursive( $path . '/*.gz' );
	foreach ( $search as $file ) {
		@unlink( $file );
	}
}

/**
 * Loads Term Cover feature, an option to add an image to taxonomy terms
 *
 * @since 7.0
 * @return void
 */
function themify_load_term_cover_feature() {
	if ( current_theme_supports( 'themify-term-cover' ) ) {
		require_once THEMIFY_DIR . '/class-tf-term-image.php';
	}
}
add_action( 'admin_init', 'themify_load_term_cover_feature' );