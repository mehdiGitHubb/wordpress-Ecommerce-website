<?php

/**
 * Themify_Updater class to handle various functionality of updater plugin.
 *
 * @since      1.1.4
 * @package    Themify_Updater
 * @author     Themify
 */

if ( !class_exists('Themify_Updater') ) :

    class Themify_Updater{
        private $settings = false;
        private $license = false;
        private $version = false;
        private $notifications = false;
        private $current_theme;
        private static $instance = null;
        private $skipGzip = false;

        /**
         * Creates or returns an instance of this class.
         *
         * @return Themify_Updater class single instance.
         */
        public static function get_instance() {
            return null == self::$instance ? self::$instance = new self : self::$instance;
        }

        public function __construct()
        {
            if( !defined('THEMIFY_UPGRADER') ) define('THEMIFY_UPGRADER', true);

            define('THEMIFY_UPDATER', 1);

            $this->init();

	        new Themify_Auto_Update($this->settings , $this->version, $this->license);

        }

        private function init() {

            if ( Themify_Updater_utils::is_admin() ) {

                add_action('admin_menu', array($this, 'menu'));
                add_action('admin_init', array($this, 'menu_p'));

                if ( THEMIFY_UPGRADER ) {
                    $this->notifications = Themify_Updater_Notifications::get_instance();
                    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script'));
                    add_action( 'admin_footer', array( $this, 'prompt'));
                    add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
                    add_action( 'admin_init', array( $this, 'update_notices' ) );
                }
            }

			$this->load_license();
			add_action( 'site_transient_update_plugins', array( $this, 'update_transient'), 10, 2);
			add_action( 'pre_site_transient_update_plugins', array( $this, 'pre_update_transient'), 100, 2);
			add_action( 'site_transient_update_themes', array( $this, 'update_transient'), 10, 2);
			add_action( 'pre_site_transient_update_themes', array( $this, 'pre_update_transient'), 100, 2);
			add_action( 'upgrader_process_complete', array( $this, 'update_cache'), 10, 2);
			add_action( 'upgrader_pre_download', array( $this, 'upgrader_error_message'), 10, 3 );
			add_filter( 'plugins_api', array( $this, 'automatic_install'), 10, 3 );
			add_filter( 'themes_api', array( $this, 'automatic_install'), 10, 3 );
			
			add_filter( 'http_request_args', array( $this, 'older_theme_updater'), 1, 2 );

            $this->check_curl_lib();

            $this->current_theme = wp_get_theme();
            if ( is_object($this->current_theme->parent()) )
                $this->current_theme = $this->current_theme->parent();

            $this->version = new Themify_Updater_Version();
			if (! wp_next_scheduled ( 'updater_hourly_schedule_notification' )) {
				wp_schedule_event(time(), 'hourly', 'updater_hourly_schedule_notification');
			}
			add_action( 'updater_hourly_schedule_notification', array($this, 'schedule_notification') );
        }

        /**
         * If curl lib doesn't exist then wordpress is unable to handle gzip compressed files return by themify server and throw PCLZIP_ERR_BAD_FORMAT (-10) error.
         *
         */
        private function check_curl_lib() {

            if ( !extension_loaded('curl') || ( defined('THEMIFY_UPDATER_SKIP_GZIP') && THEMIFY_UPDATER_SKIP_GZIP  ) ) {
                $this->skipGzip = true;
                return;
            }
        }

        private function load_settings() {
            $settings = get_option('themify_updater_licence','');

            if (!empty($settings) ) {
                $settings = json_decode( $settings, true);
                if (!$settings) {
                    $settings = array();
                }
            } else {
                $settings = array();
            }

            $this->settings = $settings;
        }

        private function update_settings( $settings ) {
            if ( !is_array($settings) ) return;
            $this->settings = $settings;

            $_key='themify_updater_licence';
            delete_option($_key);
            add_option($_key, json_encode($settings));
        }

        /**
         * @param string $key
         * @return bool|mixed
         */
        public function get_setting( $key ) {
            if( !$this->settings )
                $this->load_settings();

            return isset($this->settings[$key])?$this->settings[$key]:false;
        }

        private function load_license(){
            $username = $this->get_setting('username');
            $key = $this->get_setting('key');

            $this->license = new Themify_Updater_License( $username, $key);
        }

        public function menu() {
            add_submenu_page( 'index.php', __('Themify License', 'themify-updater'), __('Themify License', 'themify-updater'),
                'manage_options', 'themify-license', array($this, 'menu_page_callback'));
        }

        public function menu_page_callback() {

            $username = $this->get_setting('username');
            $key = $this->get_setting('key');
            $hideKey = $this->get_setting('hideKey');
            $hideName = $this->get_setting('hideName');
            $hideNotice = $this->get_setting('hideNotice');
            $notification = $this->get_setting('notification');
            $noticeEmail = $this->get_setting('noticeEmail');
            $autoUpdate = $this->get_setting('autoUpdate');

            if ($hideKey) {
                $key = Themify_Updater_utils::preg_replace($key, 'key', "*");
            }

            if ($hideName) {
                $username = Themify_Updater_utils::preg_replace($username, 'username', "*");
            }

            define('THEMIFY_UPDATER_MENU_PAGE', true);
            require (THEMIFY_UPDATER_DIR_PATH.'/templates/admin_menu.php');
        }

        public function menu_p() {

            $ajax = isset($_POST['action']) && $_POST['action']==='themify_update_license';
            if (true === $ajax || (isset($_GET['page'],$_POST['updater_licence']) && $_GET['page'] === 'themify-license')) {

                $hide = array('hideKey' => $this->get_setting('hideKey'), 'hideName' => $this->get_setting('hideName'));
                $regex = array('updater_licence' => 'key', 'themify_username' => 'username');
                $credential_key = array( 'hideKey' => 'updater_licence', 'hideName' => 'themify_username');
                $credential = array( 'updater_licence' => $this->get_setting('key'), 'themify_username' => $this->get_setting('username') );

                $temp = array(
                    'hideKey' => Themify_Updater_utils::preg_replace($credential['updater_licence'], 'key', '*'),
                    'hideName' => Themify_Updater_utils::preg_replace($credential['themify_username'], 'username', '*')
                );

                foreach ($credential_key as $key => $value) {
                    if ($hide[$key] && isset($_POST[$key])) {
                        if ($temp[$key] != $_POST[$value]) {
                            $credential[$value] = Themify_Updater_utils::preg_replace($_POST[$value], $regex[$value]);
                        }
                    } elseif ($hide[$key] && !isset($_POST[$key])) {
                        if ($temp[$key] != $_POST[$value]) {
                            $credential[$value] = Themify_Updater_utils::preg_replace($_POST[$value], $regex[$value]);
                        } else {
                            $credential[$value] = '';
                        }
                    } elseif (!$hide[$key] && isset($_POST[$key]) ) {
                        if ($credential[$value] != $_POST[$value]) {
                            $credential[$value] = Themify_Updater_utils::preg_replace($_POST[$value], $regex[$value]);
                        }
                    } else {
                        $credential[$value] = Themify_Updater_utils::preg_replace($_POST[$value], $regex[$value]);
                    }
                }
                if(false===$ajax){
                    $_POST['noticeEmail'] = trim($_POST['noticeEmail']);
                    $this->notifications = Themify_Updater_Notifications::get_instance();
                    if ( !empty($_POST['noticeEmail']) && !is_email($_POST['noticeEmail']) ) {
                        $this->notifications->add_notice( __( 'Invalid email address. Please correct it.', 'themify-updater') , 'error');
                        $_POST['noticeEmail'] = '';
                    }
                }
                $settings = array('key' => $credential['updater_licence'], 'username' => $credential['themify_username']);
                $args = array('hideKey','hideName','autoUpdate','hideNotice','notification');
                foreach($args as $k){
                    $settings[$k]=true===$ajax?$this->get_setting($k):(isset($_POST[$k]) ? true : false);
                }
                $settings['noticeEmail'] = true===$ajax?$this->get_setting('noticeEmail'):$_POST['noticeEmail'];
                $old_username = $this->get_setting('username');
                $old_key = $this->get_setting('key');
                $this->update_settings($settings);

                // We don't need to check license again if license key or username is not changed.
                if ($old_username === $credential['themify_username'] && $old_key === $credential['updater_licence']) return false===$ajax;

                if ( is_object( $this->license ) ) {
                    $this->license->update_credentials( $this->get_setting('username'), $this->get_setting('key'));
                    $this->license->license_check_action();
                    if ( ! $this->license->has_error() ) {
                        if(false===$ajax){
                            $this->notifications->add_notice( __( 'Themify Updater: Successfully validated your username and license key.', 'themify-updater') , 'success');
                        }else{
                            return true;
                        }
                    }
                }
                if(true===$ajax){
                    return false;
                }
            }
        }

        /**
         * Get the list of updates available for installed plugins.
         *
         * @return array
         */
        private function get_plugins() {

            $updates = array();
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $installed_plugins = get_plugins();
            if( !empty( $installed_plugins ) ) {
                foreach ( $installed_plugins as $key => $plugin ) {
                    $plugin_name = dirname( $key );
                    if ( ( !$this->version->has_attribute($plugin_name, 'wp_hosted') && $this->version->is_update_available($plugin_name, $plugin['Version']) )
                         || ( Themify_Updater_utils::is_admin() && isset($_GET['themify_theme_downgrade'], $_GET['plugin']) && urldecode( $_GET['plugin'] ) === $plugin_name )
                    ) {
                        $temp_class = new stdClass;
                        $temp_class->name = $plugin_name;
                        $temp_class->nicename = $plugin['Name'];
                        $temp_class->basename = $key;
                        $temp_class->themify_uri = $plugin['PluginURI'];
                        $temp_class->type = 'plugin';
                        $temp_class->version = isset($_GET['themify_theme_downgrade']) ? urldecode($_GET['version']) : $this->version->remote_version($plugin_name);
                        $temp_class->slug = $plugin_name;
                        $temp_class->is_premium = !$this->version->has_attribute($key, 'free');

                        $updates[] = $temp_class;
                    }
                }
            }
            return $updates;
        }

        /**
         * Get the list of updates available for installed themes.
         *
         * @return array
         */
        private function get_themes() {

            $updates = array();
            $installed_themes = Themify_Updater_utils::wp_get_themes();
			$framework_update = true;

            if( !empty( $installed_themes ) ) {
                foreach ($installed_themes as $key => $theme) {
                    if ( ( !$this->version->has_attribute($key, 'wp_hosted') && $this->version->is_update_available($key, $theme->get('Version')) )
                            || ( Themify_Updater_utils::is_admin() && isset($_GET['themify_theme_downgrade'], $_GET['theme']) && urldecode( $_GET['theme'] ) === $key )
						)
                    {
                        $temp_class = new stdClass;
                        $temp_class->name = $key;
                        $temp_class->nicename = $theme->get('Name');
                        $temp_class->basename = $key;
                        $temp_class->themify_uri = $theme->get('ThemeURI');
                        $temp_class->type = 'theme';
                        $temp_class->version = isset($_GET['themify_theme_downgrade']) ? urldecode($_GET['version']) : $this->version->remote_version($key);
                        $temp_class->slug = $key;
                        $temp_class->is_premium = !$this->version->has_attribute($key, 'free');

                        $updates[] = $temp_class;
						
						if ( $this->current_theme->stylesheet === $key ) {
							$framework_update = false;
						}
                    }
                }
            }

            return $updates;
        }

        /**
         * @param object $transient
         * @param string $for
         * @return object
         */
        public function update_transient($transient, $for) {

            if ($for !== 'update_themes' && $for !== 'update_plugins') return $transient;

            $type = 'theme';
            if ($for === 'update_plugins') {
                $type = 'plugin';
            }

            $tu_transient = $this->get_wp_updates();
            $tu_transient = $this->create_update_transient( $tu_transient );

            if ( isset($transient->last_checked) ) {
                if ( property_exists($transient, 'response') && is_array($transient->response) ) {
					//Clean old entries if wordpress stored them in the database.
					$transient = $this->clean_transient($transient, $type);
				} else {
					$transient->response = array();
					$transient->checked = array();
				}

                foreach ($tu_transient[$type]->response as $key => $response) {
                    $transient->response[$key] = $response;
					if ( isset( $tu_transient[$type]->checked[$key] ) ) {
						$transient->checked[$key] = $tu_transient[$type]->checked[$key];
					}
                }
            }
			if ($transient === false) {
				$transient = $tu_transient[$type];
			}

            return $transient;
        }

		/*
		* WordPress update transient maybe cache using ob cahce or some other cache.
		*
		*/
		function pre_update_transient($transient, $for) {
			
			if ($for !== 'update_themes' && $for !== 'update_plugins') return $transient;
			
			if ($transient === false) return $transient;
			
			$transient = $this->update_transient($transient, $for);
			
			return $transient;
			
		}
        
        /*
        *
        * Clean wordpress update transient. It ensures that the updated product is not shown again.
        *
        */
        private function clean_transient($transient, $type) {
            
            foreach($transient->response as $key => $response) {

                // check if it is themify product or not. if it is themify product then remove it from list.
                if ( $this->version->has_attribute($key, 'type', true) === $type ) {
                    unset($transient->response[$key], $transient->checked[$key]);
                }
            }
            return $transient;
        }
        
        
        public function update_cache ($object, $extra_hooks) {

            if ( !empty($extra_hooks['type']) && !in_array($extra_hooks['type'], array('theme', 'plugin'), true) ) return;

            if ( !class_exists('TFCache') ) {
                $pathto = $this->current_theme->get_stylesheet_directory() . '/themify/cache/class-themify-cache.php';
                if (!is_file($pathto)) return;

                include $pathto;
            }

            if ( !method_exists('TFCache','clearCacheOnUpdate') ) return;

            if ( !empty($extra_hooks['plugins']) || !empty($extra_hooks['plugin']) ) {
                $updated = ( !empty($extra_hooks['plugin']) && is_string($extra_hooks['plugin']) ) ? array($extra_hooks['plugin']) : $extra_hooks['plugins'];
                foreach ( $updated as $plugin_path ) {
                    if ($this->version->has_attribute( dirname($plugin_path), 'type')) { // verify if it is themify plugin through versions.xml
                        TFCache::clearCacheOnUpdate( 'plugin', $plugin_path);  // for plugin
                    }
                }
            } elseif ( !empty($extra_hooks['themes']) || !empty($extra_hooks['theme'])) {
                $updated = ( !empty($extra_hooks['theme']) && is_string($extra_hooks['theme']) ) ? array($extra_hooks['theme']) : $extra_hooks['themes'];
                foreach ($updated as $theme_slug) {
                    if ($this->version->has_attribute($theme_slug, 'type')) { // verify if it is themify theme through versions.xml
                        if ($theme_slug === 'themify') {
                            TFCache::clearCacheOnUpdate( 'fw', $this->current_theme->stylesheet); // for framework
                        } else {
                            TFCache::clearCacheOnUpdate( 'theme', $theme_slug);  // for theme
                        }
                    }
                }
            }
        }

        /**
         * @return array|bool
         */
        private function get_wp_updates() {
            static $updates = false;

            if ( $updates !== false ) return $updates;
            $plugins = $this->get_themes();
            $themes = $this->get_plugins();

            $updates = array_merge($themes, $plugins);

            return $updates;
        }

        /**
         * @param array $updates
         * @return array|bool
         */
        private function create_update_transient ( $updates ) {
            static $transients = false;

            if (!is_array($transients)) {

                $theme_transient = new stdClass();
                $plugin_transient = new stdClass();
                $theme_transient->response = $theme_transient->checked = array();
                $plugin_transient->response = $plugin_transient->checked = array();

                foreach ($updates as $update) {
                    $transient = new stdClass();

                    $package = '';
                    $products = $this->license->get_products();

                    // download link for premium products.
                    if ( !empty($products) && in_array($update->slug, $products ) ) {
                        if ( Themify_Updater_utils::is_admin() &&  isset($_GET['themify_theme_downgrade']) ) {
                            if ( isset( $_GET['theme'] ) && urldecode( $_GET['theme'] ) === $update->slug ) {
                                if ( isset($_GET['themify-theme']) ) {
                                    add_filter( 'update_theme_complete_actions', array($this, 'themify_reinstall_actions'), 1, 9 );
                                } else {
                                    add_filter( 'update_theme_complete_actions', array($this, 'automatic_install_actions'), 2, 9 );
                                }
                                $package = $this->license->get_product_link($update->slug, urldecode($_GET['version']));
                            } elseif ( isset( $_GET['plugin'] ) && urldecode( $_GET['plugin'] ) === $update->slug ) {
                                $package = $this->license->get_product_link($update->slug, urldecode($_GET['version']) );
                                $update->basename = $update->slug;
                                add_filter( 'update_plugin_complete_actions', array($this, 'automatic_install_actions'), 2, 9 );
                            }
                        } else {
                            $package = $this->license->get_product_link( $update->slug, '' );
                        }
                    }

                    // download link for free products.
                    if ( $this->version->has_attribute($update->slug, 'free') && $package == '' ) {
                        if ( Themify_Updater_utils::is_admin() && isset($_GET['themify_theme_downgrade']) ) {
							$tmp_version = '';
                            if ( !empty($_GET['version']) ) {
                                $tmp_version = '-'.$_GET['version'];
                            }
                            if ( isset( $_GET['theme'] ) && urldecode( $_GET['theme'] ) === $update->slug ) {
                                if ( isset($_GET['themify-theme']) ) {
                                    add_filter( 'update_theme_complete_actions', array($this, 'themify_reinstall_actions'), 1, 9 );
                                } else {
                                    add_filter( 'update_theme_complete_actions', array($this, 'automatic_install_actions'), 2, 9 );
                                }
                                $package = Themify_Updater_utils::$uri . '/files/'. $update->slug .'/'.$update->slug . urldecode($tmp_version) . '.zip';
                            } elseif ( isset( $_GET['plugin'] ) && urldecode( $_GET['plugin'] ) === $update->slug ) {
                                $package = Themify_Updater_utils::$uri . '/files/'. $update->slug .'/'.$update->slug . urldecode($tmp_version) . '.zip';
                                $update->basename = $update->slug;
                                add_filter( 'update_plugin_complete_actions', array($this, 'automatic_install_actions'), 2, 9 );
                            }
                        } else {
                            $package = Themify_Updater_utils::$uri . '/files/'. $update->slug .'/'.$update->slug.'.zip';
                        }

						if ( Themify_Updater_utils::use_proxy() ) {
							$package = Themify_Updater_utils::$proxy_uri . '/?action=get-themify&file=' . $package;
						}
                    }

                    if ($update->type === 'theme') {
                        $transient = array();
                        $transient['theme'] = $update->name;
                        $transient['new_version'] = $update->version;
                        $transient['url'] = $update->themify_uri;
                        $transient['package'] = $package;
                        $theme_transient->response[$update->basename] = $transient;
                        $theme_transient->checked[$update->basename] = $update->version;
                        if ($update->name == 'themify') {
                            add_filter( 'upgrader_package_options', array($this, 'update_framework_setup') );
                        } else {
                            add_action( "after_theme_row_". $update->basename, array($this, 'theme_update_row'), 8, 2 );
                        }
                    } else {
                        $transient->name = $update->nicename;
                        $transient->plugin = $update->basename;
                        $transient->slug = $update->slug;
                        $transient->new_version = $update->version;
                        $transient->url = $update->themify_uri;
                        $transient->package = $package;
                        $plugin_transient->response[$update->basename] = $transient;
                        $plugin_transient->checked[$update->basename] = $update->version;
                        add_action( "after_plugin_row_". $update->basename , array($this , 'plugin_update_row'), 8, 2 );
                    }
                }

                $transients = array();
                $transients['theme'] = $theme_transient;
                $transients['plugin'] = $plugin_transient;
            }
            return $transients;
        }

        public function update_notices() {

            if ( $this->get_setting('hideNotice') ) return;

            $updates = $this->get_wp_updates();

            foreach ( $updates as $update ) {

                $classes = array('themify-updater');
                $products = $this->license->get_products();

                if ( isset($_GET['themify_theme_downgrade']) && ( (isset($_GET['theme']) && $_GET['theme'] === $update->slug) || (isset($_GET['plugin']) && $_GET['plugin'] === $update->slug)) ) {
                    continue;
                }

                if ( ( !in_array($update->slug, $products) || $this->license->has_error() ) && !$this->version->has_attribute($update->slug, 'free') ) {
                    $classes[] = 'themify-updater-stop';
                }

                $classes = array_unique($classes);

                $notification = sprintf(
                    __('<div>%s version %s is now available.
                                    <a href="#" title="" class="%s" data-plugin="%s" data-nicename_short="%s" data-update_type="%s" data-base="%s" data-nonce="%s">Update now</a>
                                    or view the 
                                    <a href="%s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%s">changelog</a>
                                    for details.
                                    </div>', 'themify-updater'),
                    $update->nicename,
                    $update->version,
                    esc_attr(implode(' ', $classes)),
                    esc_attr($update->slug),
                    esc_attr($update->nicename),
                    esc_attr( $update->type === 'plugin' ? 'update-plugins' : 'update-themes' ),
                    esc_attr($update->basename),
                    wp_create_nonce('updates'),
                    esc_url('https://themify.me/changelogs/' . $update->name . '.txt'),
                    esc_url('https://themify.me/changelogs/' . $update->name . '.txt')
                );
                $this->notifications->add_notice($notification);
            }
        }

        public function schedule_notification() {

			$noticeEmail = $this->get_setting('noticeEmail');
            if ( $this->get_setting('notification') && !empty( $noticeEmail ) ) {
                $updates = $this->get_wp_updates();
                $notified = get_option('themify_updater_notified_updates', '');
                $notified = json_decode( $notified, true);
                $new_notifications = array();
                if ( !is_array($notified) ) {
                    $notified = array();
                }
                foreach ($updates as $update) {
                    if ( !isset($notified[$update->name]) || version_compare($notified[$update->name], $update->version, '<') ) {
                        $new_notifications[] = $update;
                    }
                }
                if ( !empty($new_notifications) ) {

                    if (count($new_notifications) > 1) {
                        $subject = sprintf( __('Themify Updater: %d new updates are available.', 'themify-updater'), count($new_notifications) );
                    } else {
                        $subject = sprintf( __('Themify Updater: %d new update is available.', 'themify-updater'), count($new_notifications) );
                    }
                    $body = __('The following new updates are available.','themify-updater').'<br><ol>';
                    foreach ($new_notifications as $new) {
                        $body .= sprintf(
                            __('<li>%s version %s is now available. View the 
                                            <a href="%s" title="" target="_blank">changelog</a>
                                            for details.
                                            </li>', 'themify-updater'),
                            $new->nicename,
                            $new->version,
                            esc_url( Themify_Updater_utils::$uri . '/changelogs/' . $new->name . '.txt')
                        );
                    }
                    $body .= '</ol><br><br>' . sprintf('%s <a href="%s" target="_blank">%s</a> %s', __('NOTE: You are receiving this email because you\'ve enabled update notifications for', 'themify-updater'), network_home_url() , __('your site', 'themify-updater'), __(' via the Themify License Settings page. To disable notifications, login to your WordPress site admin Dashboard, go to Themify License, and uncheck the notification updates option.', 'themify-updater') );
                    $headers = array('Content-Type: text/html; charset=UTF-8');

                    wp_mail( $noticeEmail, $subject, $body, $headers );
                }

                foreach ($new_notifications as $new) {
                    $notified[$new->name] = $new->version;
                }

                update_option('themify_updater_notified_updates', json_encode( $notified ));
            }
        }

        public function upgrader_error_message($reply, $package , $upgrader) {

            if ( ! is_object($upgrader) || is_wp_error($upgrader) ) return $reply;

            if ( $this->skipGzip ) {
                    add_filter('http_request_args', array( $this, 'pclzip_err'), 10, 1);
            }

            if ( isset($upgrader->skin->plugin_info) ) {

                $name = 'sadasdasdqewekjvbnv'; // A random name to insure the plugin works without fall for unexpected error.
                $nicename = '';
                $option = $upgrader->skin->options['url'];
                $option = substr($option,strpos($option, "plugins=")+8);
                $plugins = explode(',', urldecode($option));
                foreach ( $plugins as $plugin) {
                    $temp = get_plugin_data(plugin_dir_path( THEMIFY_UPDATER_DIR_PATH ) . $plugin);
                    if ( isset($temp['Name']) && $temp['Name'] == $upgrader->skin->plugin_info['Name'] ) {
                        $name = dirname($plugin);
                        $nicename = $temp['Name'];
                    }
                }
                $version = $upgrader->skin->plugin_info['Version'];
            } elseif ( isset($upgrader->skin->theme_info) ) {
                $name = $upgrader->skin->theme_info->template;
                $version = $upgrader->skin->theme_info->get('Version');
                $nicename = $upgrader->skin->theme_info->get('Name');
            } else return $reply;

            if (  $this->version->is_update_available($name, $version) ) {

                if ( $this->license->has_error() ) {
                    $upgrader->strings['no_package'] = '';
                    $upgrader->strings['skin_update_failed_error'] = $upgrader->strings['skin_update_failed'] = $this->license->get_error_message();
                } elseif ( $name !== 'themify' && ! $this->license->has_product_access($name) ) {
                    $upgrader->strings['no_package'] = $upgrader->strings['skin_update_failed'] = $upgrader->strings['skin_update_failed_error'] = sprintf( __('Sorry, you don\'t have access to %s updates. Your membership might be expired. Please check your membership expiry or <a href="https://themify.me/contact">contact us</a>.', 'themify-updater'), $nicename );
                }
            }
            return $reply;
        }

        public function pclzip_err($opt){
            $opt['headers']['Accept-Encoding'] = "identity;q=0";
            return $opt;
        }

        public function has_error(){
            if ( !THEMIFY_UPGRADER ) return true;
            elseif ( !is_object($this->license) ) return true;
            else return $this->license->has_error();
        }

        public function has_attribute( $name, $attr ){
            if ( !is_object($this->version) ) return false;
            else return $this->version->has_attribute($name, $attr);
        }

	    /**
	     * @param $product string
	     *
	     * @return bool
	     */
	    public function has_access ( $product ) {
	        if ( !is_object($this->license) ) return false;
	        else return $this->license->has_product_access( $product );
        }

	    /**
	     * @return bool|null|object
	     */
	    public function get_versions(){
		    if ( !is_object($this->version) ) return null;
		    else return $this->version;
        }

        /**
         * @param $res
         * @param $action
         * @param $args
         * @return bool|stdClass
         */
        public function automatic_install ($res, $action, $args) {

            if ($action !== 'plugin_information' && $action !== 'theme_information') return false;

            if ( !isset($args->slug) || !$this->version->is_update_available($args->slug) ) return false;

            add_filter( 'install_theme_complete_actions', array($this, 'automatic_install_actions'), 2, 9 );
            add_filter( 'install_plugin_complete_actions', array($this, 'automatic_install_actions'), 2, 9 );
            if ( $this->skipGzip ) {
                add_filter('http_request_args', array( $this, 'pclzip_err'), 10, 1);
            }

            if ( $this->version->has_attribute($args->slug, 'wp_hosted') ) return false;

            $temp = new stdClass();
            $temp->name = $args->slug;
            $temp->version = $this->version->remote_version( $args->slug );
            $temp->download_link = $this->license->get_product_link( $args->slug );

            return $temp;

        }

        /**
         * @param $actions
         * @return mixed
         */
        public function automatic_install_actions($actions,$plugin_file) {

            if ( isset($actions['themes_page']) ) {
                $actions['themes_page'] = '<a href="' . admin_url( 'index.php?page=themify-license&promotion=1' ) . '" target="_parent">' . __( 'Return to Themify License' , 'themify-updater' ) . '</a>';
            } elseif (isset($actions['plugins_page'])) {
                $actions['plugins_page'] = '<a href="' . admin_url( 'index.php?page=themify-license&promotion=2' ) . '" target="_parent">' . __( 'Return to Themify License' , 'themify-updater' ) . '</a>';
                if(is_string($plugin_file)){
                    $is_wp_network = is_network_admin();
                    if ( ($is_wp_network && is_plugin_active_for_network( $plugin_file )) || (!$is_wp_network && is_plugin_active( $plugin_file )) ) {
                        unset($actions['activate_plugin']);
                    }
                }
            }

            return $actions;
        }

        /**
         * @param $actions
         * @return mixed
         */
        public function themify_reinstall_actions($actions) {

            if ( isset($actions['themes_page']) ) {
                $actions['themes_page'] = '<script>function goBackThemifyPanel() { window.location.href = document.referrer }</script><a href="#" onclick="goBackThemifyPanel()" target="_parent">' . __( 'Return to Themify Panel' , 'themify-updater' ) . '</a>';
            }

            return $actions;
        }

        /**
		 * Re-install older version dropdown for themes. 
		 *
         * @param $theme
         */
        public function themify_reinstall_theme ($theme) {
            $install = array( 'url' => self_admin_url( 'update.php' ), 'themify_theme_downgrade' => 1, 'theme' => $theme, 'action' => 'upgrade-theme',  '_wpnonce' => wp_create_nonce( "upgrade-theme_". $theme ) );
			?>
            <p class="update">
                <select id="themeversiontoreinstall" name="version">
					<?php
					$version = $this->version->remote_version( $theme );
					echo '<option value="'. $version .'">'. $version . ' (' . __('Latest', 'themify-updater') . ')' .'</option>';
					$versions = Themify_Updater_utils::get_previous_versions_from_changelogs( $theme );
					if ( empty( $versions ) ) {
						$versions = Themify_Updater_utils::get_previous_versions( $version, 5, false );
					}
					$lsv = $this->version->has_attribute( $theme, 'stable_back_version', true );
					foreach ( $versions as $v ) {
						if ( $version === $v || $lsv === $v ) {
							continue;
						}
						echo '<option value="'. $v .'">'. $v .'</option>';
					}
					if ( ! empty( $lsv ) ) {
						echo '<option value="'.$lsv.'">'. $lsv . __( ' (Legacy)', 'themify-updater' ) .'</option>';
					}
					?>
				</select>
            </p>
            <p class="reinstalltheme">
                <a class="upgrade-theme upgrade-theme-button button big-button themify_button" href="#" data-install="<?php echo base64_encode(json_encode($install)); ?>"><?php _e( 'Re-install Theme', 'themify-updater' ) ?></a>
            </p>
            <p><?php _e( 'Re-install the theme to the selected version.', 'themify-updater' ); ?></p>
            <?php
        }

        public function update_framework_setup($options) {

            if ( !empty($options['package']) && basename( $options['package'], '.zip' ) === 'themify' ) {
                $options['clear_destination'] = false;
                $options['abort_if_destination_exists'] = false;
                add_filter( 'upgrader_source_selection', array($this, 'change_framework_install_source'), 10, 2 );
                remove_filter('upgrader_package_options', array($this , 'update_framework_setup'));
            }
            return $options;
        }

        /**
         * @param $source
         * @param $remote_source
         * @return string
         */
        public function change_framework_install_source($source, $remote_source) {

            remove_filter('upgrader_source_selection', array($this , 'change_framework_install_source'));

            $dest = $remote_source . '/' . $this->current_theme->stylesheet;
            $theme = $this->current_theme->theme_root . '/' . $this->current_theme->stylesheet;
            $framework_old = $theme . '/themify_old';

			if( !file_exists($dest) ) {
				mkdir($dest);
			}

			// check and remove old framework backup
	        if( file_exists($framework_old) ) {
		        Themify_Updater_utils::rrmdir( $framework_old );
	        }

	        Themify_Updater_utils::rcopy( $source, $dest . '/themify');
            Themify_Updater_utils::rrmdir($source);

	        // make framework backup
	        rename($theme . '/themify', $framework_old);

            add_filter('upgrader_post_install', array($this, 'remove_framework_backup'), 3, 10);

            return $dest;
        }

        public function remove_framework_backup($response, $extra, $result) {

            //we already knew that this function is called when update framework was successful. but still checks to insure.
            if ( !empty($extra['theme']) && $extra['theme'] === 'themify' ) {
	            $framework_old = $this->current_theme->theme_root . '/' . $this->current_theme->stylesheet . '/themify_old';
	            // Remove old framework backup
	            Themify_Updater_utils::rrmdir( $framework_old );
            }
            return $response;
        }

        public function enqueue_script() {
            $upgrader_var = array(
                'check_backup' => __('Make sure to backup before upgrading. Files and settings may get lost or changed.', 'themify-updater'),
                'installation_message' => __('Are you sure to install this?', 'themify-updater'),
                'v7_message'=>__('WARNING: Downgrading to an older version might have issues. The older versions don’t have some of the layout features (eg. the layouts and column arrangements might look different).', 'themify-updater'),
                'installing'=>__('Installing ', 'themify-updater'),
                'activate_lnk'=>__(' (Activate)', 'themify-updater'),
                'activated'=>__('(Activated)', 'themify-updater'),
                'installed'=>__(' Installed', 'themify-updater'),
                'done'=>__('Done', 'themify-updater'),
                'activate'=>__('Activate', 'themify-updater')
            );
            wp_enqueue_script('themify-upgrader', Themify_Updater_utils::enque_min(THEMIFY_UPDATER_DIR_URL . 'js/themify-upgrader.js'), array('jquery'), THEMIFY_UPDATER_VERSION, true);
            wp_localize_script('themify-upgrader', 'themify_upgrader', $upgrader_var);
            wp_enqueue_style('themify-updater-style', Themify_Updater_utils::enque_min(THEMIFY_UPDATER_DIR_URL . 'css/themify-upgrader.css'), array(), THEMIFY_UPDATER_VERSION, 'all');
        }

        public function prompt() {
            ?>
            <div class="themify_updater_alert" style="display:none;"></div>
            <!-- prompts -->
            <div class="themify-updater-promt-box" style="display:none;">
                <div class="show-error">
                    <p class="error-msg"><?php _e('There were some errors updating the theme', 'themify-updater'); ?></p>
                </div>
            </div>
            <div class="themify_updater_promt_overlay" style="display:none;"></div>
            <!-- /prompts -->
            <?php
        }

        public function plugin_update_row( $file, $plugin_data ) {

            remove_action( "after_plugin_row_$file", 'wp_plugin_update_row', 10, 2 );

            $pluginSlug = dirname($file);

            $plugins_allowedtags = array(
                'a'       => array( 'href' => array(), 'title' => array() ),
                'abbr'    => array( 'title' => array() ),
                'acronym' => array( 'title' => array() ),
                'code'    => array(),
                'em'      => array(),
                'strong'  => array(),
            );

            $plugin_name   = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

            /** @var WP_Plugins_List_Table $wp_list_table */
            $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

            if ( is_network_admin() || ! is_multisite() ) {
                if ( is_network_admin() ) {
                    $active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
                } else {
                    $active_class = is_plugin_active( $file ) ? ' active' : '';
                }

                $details_url = esc_url( 'https://themify.me/changelogs/'. dirname($file). '.txt' );

                echo '<tr class="plugin-update-tr' . $active_class . '" id="' . esc_attr( $pluginSlug . '-update' ) . '" data-slug="' . esc_attr( $pluginSlug ) . '" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';

                if ( ! current_user_can( 'update_plugins' ) ) {
                    /* translators: 1: plugin name, 2: details URL, 3: additional link attributes, 4: version number */
                    printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>.' ),
                        $plugin_name,
                        $details_url,
                        $details_url,
                        $this->version->remote_version($pluginSlug)
                    );
                } elseif ( $this->license->has_error() ) {
                    /* translators: 1: plugin name, 2: details URL, 3: additional link attributes, 4: version number */
                    printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>. <em>%5$s Automatic update is unavailable.</em>' ),
                        $plugin_name,
                        $details_url,
                        $details_url,
                        $this->version->remote_version($pluginSlug),
                        $this->license->get_error_message()
                    );
                } else {
                    /* translators: 1: plugin name, 2: details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
                    printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ),
                        $plugin_name,
                        $details_url,
                        $details_url,
                        $this->version->remote_version($pluginSlug),
                        wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file ),
                        sprintf( 'class="update-link" aria-label="%s"',
                            /* translators: %s: plugin name */
                            esc_attr( sprintf( __( 'Update %s now' ), $plugin_name ) )
                        )
                    );
                }

                echo '</p></div></td></tr>';
            }
        }

        public function theme_update_row( $theme_key, $theme ) {

            remove_action( "after_theme_row_$theme_key", 'wp_theme_update_row', 10, 2 );

            $details_url = esc_url ('https://themify.me/changelogs/'. dirname($theme_key). '.txt' );

            /** @var WP_MS_Themes_List_Table $wp_list_table */
            $wp_list_table = _get_list_table( 'WP_MS_Themes_List_Table' );

            $active = $theme->is_allowed( 'network' ) ? ' active' : '';

            echo '<tr class="plugin-update-tr' . $active . '" id="' . esc_attr( $theme->get_stylesheet() . '-update' ) . '" data-slug="' . esc_attr( $theme->get_stylesheet() ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';
            if ( ! current_user_can( 'update_themes' ) ) {
                /* translators: 1: theme name, 2: details URL, 3: additional link attributes, 4: version number */
                printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>.'),
                    $theme['Name'],
                    $details_url,
                    $details_url,
                    $this->version->remote_version($theme_key)
                );
            } elseif ( $this->license->has_error() ) {
                /* translators: 1: theme name, 2: details URL, 3: additional link attributes, 4: version number */
                printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>. <em>%5$s Automatic update is unavailable.</em>' ),
                    $theme['Name'],
                    $details_url,
                    $details_url,
                    $this->version->remote_version($theme_key),
                    $this->license->get_error_message()
                );
            } else {
                /* translators: 1: theme name, 2: details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
                printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ),
                    $theme['Name'],
                    $details_url,
                    $details_url,
                    $this->version->remote_version($theme_key),
                    wp_nonce_url( self_admin_url( 'update.php?action=upgrade-theme&theme=' ) . $theme_key, 'upgrade-theme_' . $theme_key ),
                    sprintf( 'class="update-link" aria-label="%s"',
                        /* translators: %s: theme name */
                        esc_attr( sprintf( __( 'Update %s now' ), $theme['Name'] ) )
                    )
                );
            }

            echo '</p></div></td></tr>';
        }

        /**
         * Setup admin dashboard widget to show update notification
         *
         * @since 1.1.3
         */
        function wp_dashboard_setup() {
			$wp_updates = $this->get_wp_updates();
            if ( empty( $wp_updates ) ) {
                return;
            }

            if ( current_user_can( 'install_themes' ) ) {
                wp_add_dashboard_widget( 'themify_updates', esc_html__( 'Themify Updates', 'themify-updater' ), array( $this, 'admin_widget' ) );
            }
        }

        /**
         * Renders the Themify Updates admin dashboard widget
         *
         * @since 1.1.3
         */
        function admin_widget() {
            $current_theme_name = $this->current_theme->get( 'Name' );

            echo '<ul>';

            // active theme
            $active_theme_key = null;
            $updates = $this->get_wp_updates();
            foreach ( $updates as $key => $update ) {
                if ( strpos( $update->nicename, $current_theme_name ) !== false ) {

                    ?>
                    <li class="themify-update-theme">
                        <div class="themify-theme-thumb"><img src="<?php echo esc_attr( $this->current_theme->get_screenshot() ); ?>"/></div>
                        <div class="themify-theme-meta">
                            <h2><?php echo sprintf( __( '%s <span>V. %s</span>', 'themify-updater' ), $current_theme_name, $update->version ) ; ?></h2>
                            <p><?php echo $this->current_theme->get( 'Description' ); ?></p>
                            <a href="<?php echo admin_url( 'index.php?page=themify-license' ); ?>" class="themify-update-button"><?php _e( 'Update Now', 'themify-updater' ) ?></a>
                            <a href="<?php echo 'https://themify.me/changelogs/' . get_template(); ?>.txt" target="_blank"><?php _e( 'View changelogs', 'themify-updater' ); ?></a>
                        </div>
                    </li>
                    <?php
                    $active_theme_key = $key;
                    break;
                }
            }

            foreach ( $updates as $key => $update ) {
                if ( $key === $active_theme_key ) {
                    continue;
                }

                ?>
                <li class="themify-update-plugins">
                    <h2><?php echo sprintf( __( '%s <span>V. %s</span>', 'themify-updater' ), $update->nicename, $update->version ) ; ?></h2>
                    <a href="<?php echo admin_url( 'index.php?page=themify-license' ); ?>" class="themify-update-button"><?php _e( 'Update Now', 'themify-updater' ) ?></a>
                    <a href="<?php echo 'https://themify.me/changelogs/' . $update->slug; ?>.txt" target="_blank"><?php _e( 'View changelogs', 'themify-updater' ); ?></a>
                </li>
                <?php
            }
            echo '</ul>';
        }

        /**
         * To fix fatal error produced by older theme in latest wordpress.
         *
         * @since 1.2.4
         */
        function older_theme_updater($response, $url) {

            if ( 0 === strpos( $url, 'https://api.wordpress.org/themes/update-check' ) && function_exists('themify_hide_themes') && !current_theme_supports( 'themify-exclude-theme-from-wp-update' ) ) {
                    remove_filter( 'http_request_args', 'themify_hide_themes', 5);
                    $themes = json_decode( $response['body']['themes'] );
                    unset( $themes->themes->{$this->current_theme->stylesheet} );
                    $response['body']['themes'] = json_encode( $themes );
            }
            return $response;
        }
    }
endif;
