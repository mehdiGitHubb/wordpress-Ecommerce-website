<?php

/**
 * Auto_Update class for automatically update functionality.
 *
 * @since      1.2.2
 * @package    Themify_Updater_Cache
 * @author     Themify
 */
if ( !class_exists('Themify_Auto_Update') ) :

class Themify_Auto_Update {

	private $cache = false;
	private $license = false;
	private $version = false;
	private $settings = false;
	private $updates = array();

	function __construct ($settings, $verions, $license) {
		$this->version = $verions;
		$this->license = $license;
		$this->settings = $settings;
		$this->cache = new Themify_Updater_Cache();
		add_filter( 'auto_update_plugin', array( $this, 'update_plugin'), 99, 2 );
		add_filter( 'auto_update_theme', array( $this, 'update_theme'), 99, 2 );
		add_action( 'automatic_updates_complete', array( $this, 'notify'), 10, 1 );
		
		global $wp_version;
		// WordPress Version.
		if ( empty($wp_version) ) {
			include( ABSPATH . 'wp-includes/version.php' );
		}
		
		if ( ! empty( $this->settings['autoUpdate']) && version_compare($wp_version, '5.5', '<')) {
			$this->next_check();
		}
	}

	private function next_check() {
		if ( ! $this->cache->get('autoUpdateNextCheck') ) {
			$this->cache->set('autoUpdateNextCheck', 1, 14400);
			$this->scheduled_auto_update();
		}
	}

	private function scheduled_auto_update() {
		include_once( ABSPATH . 'wp-includes/pluggable.php' );
		include_once( ABSPATH . 'wp-admin/includes/admin.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$upgrader = new WP_Automatic_Updater;
		if ( $upgrader->is_disabled() || ! is_main_network() || ! is_main_site() ||  ! WP_Upgrader::create_lock( 'auto_updater' )) {
			return;
		}


		// Don't automatically run these thins, as we'll handle it ourselves
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
		remove_action( 'upgrader_process_complete', 'wp_version_check' );
		remove_action( 'upgrader_process_complete', 'wp_update_plugins' );
		remove_action( 'upgrader_process_complete', 'wp_update_themes' );

		// Next, Plugins
		$plugin_updates = get_site_transient( 'update_plugins' );
		if ( $plugin_updates && ! empty( $plugin_updates->response ) ) {
			foreach ( $plugin_updates->response as $plugin ) {
				if ( ! property_exists( $plugin,'slug') || empty($plugin->package) ) continue;
				if ( $this->version->has_attribute( $plugin->slug, 'type' , true) === 'plugin' ) {
					$upgrader->update( 'plugin', $plugin );
				}
			}
			// Force refresh of plugin update information
			wp_clean_plugins_cache();
		}

		// Next, those themes we all love
		$theme_updates = get_site_transient( 'update_themes' );
		if ( $theme_updates && ! empty( $theme_updates->response ) ) {
			foreach ( $theme_updates->response as $theme ) {
				if ( ! $theme['theme'] === 'themify' || empty($theme['package']) ) continue;
				if ( $this->version->has_attribute( $theme['theme'], 'type' , true) === 'theme' ) {
					$upgrader->update( 'theme', (object) $theme );
				}
			}
			// Force refresh of theme update information
			wp_clean_themes_cache();
		}
		WP_Upgrader::release_lock( 'auto_updater' );
	}

	function update_plugin( $should_update, $plugin ) {

		if ( ! property_exists( $plugin,'slug') || empty($plugin->package)) return $should_update;
		
		$major_update = $this->version->has_attribute( $plugin->slug, 'stop_auto_update' , true);

		if ( ! file_exists( dirname(THEMIFY_UPDATER_DIR_PATH ) . '/' . $plugin->plugin ) ) {
			return $should_update;
		}

		$tmp_plugin = get_plugin_data( dirname(THEMIFY_UPDATER_DIR_PATH ) . '/' . $plugin->plugin );
		
		if ( !empty($major_update) && !empty($tmp_plugin['Version']) && version_compare($major_update, $tmp_plugin['Version'], '>') ) {
			return false;
		}

		$should_update = $this->update( $plugin->slug, 'plugin', $should_update);

		return $should_update;
	}

	function update_theme( $should_update, $theme ) {

		if ( $theme->theme === 'themify') return $should_update;

		if ( empty($theme->package) ) return $should_update;
		
		$major_update = $this->version->has_attribute( $theme->theme, 'stop_auto_update' , true);
		$tmp_theme = wp_get_theme( $theme->theme );

		if ( !empty($major_update) && $tmp_theme->exists() && version_compare($major_update, $tmp_theme->get('Version'), '>') ) {
			return false;
		}

		$should_update = $this->update( $theme->theme, 'theme', $should_update);

		return $should_update;
	}

	protected function update ( $name, $type, $should_update) {

		if ( empty( $this->settings['autoUpdate']) ) return $should_update;

		if ( $this->version->has_attribute( $name, 'type' , true) === $type ) {
			$should_update = apply_filters( 'auto_update_themify_' . $type, true);
			if ( $should_update ) {
				array_push($this->updates, $name);
			}
		}

		return $should_update;
	}

	public function notify($update_results) {

		if ( empty($this->updates) ) return;

		$types = array('plugin', 'theme');
		$notice = array();
		$changelog_str = __( '<li>%s %s (view <a href="%s" target="_blank" title="changelogs">changelogs</a>)</li>' , 'themify-updater' );
		foreach ( $types as $type ) {
			if ( isset($update_results[ $type ]) ) {
				foreach ( $update_results[ $type ] as $update_result ) {
					if ( ( $type === 'plugin' && property_exists( $update_result->item,'slug') && in_array( $update_result->item->slug, $this->updates ) ) ||
					     ( $type === 'theme' && in_array( $update_result->item->theme, $this->updates ) ) ) {
						if ($update_result->resul) {
							$uri = Themify_Updater_utils::$uri . '/changelogs/' . ($type === 'plugin' ? $update_result->item->slug : $update_result->item->theme) . '.txt';
							array_push($notice, sprintf($changelog_str, $update_result->name, $update_result->item->new_version, $uri));
						}
					}
				}
			}
		}

		if ( !empty($notice) ) {
			$domain = network_home_url();
			$body = __('Hi, <br /><br />
            The following have been automatically updated to the latest version on your site (%s):<br /><br />
            %s<br /><br />
            If you experience any issues or need support, please post it on our forum: https://themify.me/forum or email us: support@themify.me<br /><br />
            To disable auto update, login to your site (%s) admin dashboard , hover "Dashboard" and go to "Themify License", un-check auto update option.<br /><br />
            Thanks for using Themify!', 'themify-updater' );
			$body = sprintf( $body, $domain, implode( '', $notice), $domain);
			if (count($notice) > 1) {
				$subject = sprintf( __('Themify Updater: %d products are updated.', 'themify-updater'), count($notice) );
			} else {
				$subject = sprintf( __('Themify Updater: %d product is updated.', 'themify-updater'), count($notice) );
			}
			$headers = array('Content-Type: text/html; charset=UTF-8');
			$noticeEmail = !empty( $this->settings['noticeEmail'] ) ? $this->settings['noticeEmail'] : get_option('admin_email');
			wp_mail( $noticeEmail, $subject, $body, $headers );
		}

		return;
	}

}
endif;