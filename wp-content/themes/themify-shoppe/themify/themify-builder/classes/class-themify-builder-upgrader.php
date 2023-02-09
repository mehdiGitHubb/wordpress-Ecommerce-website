<?php

defined( 'ABSPATH' ) || exit;

class Themify_Builder_Upgrader extends WP_Upgrader {

	public $cookies;
	public $show_before = '';

	function upgrade_strings() {
		$this->strings['up_to_date'] = __('The plugin is at the latest version.', 'themify');
		$this->strings['no_package'] = __('Update package not available.', 'themify');
		$this->strings['downloading_package'] = __('Downloading update from <span class="code">%s</span>&#8230;', 'themify');
		$this->strings['unpack_package'] = __('Unpacking the update&#8230;', 'themify');
		$this->strings['remove_old'] = __('Removing the old version of the plugin&#8230;', 'themify');
		$this->strings['remove_old_failed'] = __('Could not remove the old plugin.', 'themify');
		$this->strings['process_failed'] = __('Plugin update failed.', 'themify');
		$this->strings['process_success'] = __('Plugin updated successfully.', 'themify');
	}

	function upgrade( $plugin, $url, $cookies ) {
		$this->cookies = $cookies;

		$this->init();
		$this->upgrade_strings();

		add_filter('upgrader_pre_install', array($this, 'deactivate_plugin_before_upgrade'), 10, 2);
		add_filter('upgrader_clear_destination', array($this, 'delete_old_plugin'), 10, 4);

		$this->run(array(
					'package' => $url,
					'destination' => WP_PLUGIN_DIR,
					'clear_destination' => true,
					'clear_working' => true,
					'hook_extra' => array(
                                            'plugin' => $plugin
					)
				));

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter('upgrader_pre_install', array($this, 'deactivate_plugin_before_upgrade'));
		remove_filter('upgrader_clear_destination', array($this, 'delete_old_plugin'));

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;
	}

	function download_package($package) {

		if ( ! preg_match('!^(http|https|ftp)://!i', $package) && is_file($package) ) //Local file or remote?
			return $package; //must be a local file..

		if ( empty($package) )
			return new WP_Error('no_package', $this->strings['no_package']);

		$this->skin->feedback('downloading_package', $package);

		$download_file = $this->download_url($package);

		if ( is_wp_error($download_file) )
			return new WP_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

		return $download_file;
	}

	function download_url( $url, $timeout = 300 ) {
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new WP_Error( 'http_no_url', __( 'Invalid URL Provided.', 'themify' ) );

		$tmpfname = wp_tempnam($url);
		if ( ! $tmpfname )
			return new WP_Error( 'http_no_file', __( 'Could not create Temporary file.', 'themify' ) );

		$response = wp_safe_remote_get( $url, array( 'cookies' => $this->cookies, 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname ) );

		if ( is_wp_error( $response ) ) {
			unlink( $tmpfname );
			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		return $tmpfname;
	}

	//Hooked to pre_install
	function deactivate_plugin_before_upgrade($return, $plugin) {

		if ( is_wp_error($return) ){ //Bypass.
			return $return;
                }

		if ( empty($plugin['plugin']) ){
                    return new WP_Error('bad_request', $this->strings['bad_request']);
                }
	}

	//Hooked to upgrade_clear_destination
	function delete_old_plugin($removed, $local_destination, $remote_destination, $plugin) {
		global $wp_filesystem;

		if ( is_wp_error($removed) )
			return $removed; //Pass errors through.

		$plugin = isset($plugin['plugin']) ? $plugin['plugin'] : '';
		if ( empty($plugin) )
			return new WP_Error('bad_request', $this->strings['bad_request']);

		$plugins_dir = $wp_filesystem->wp_plugins_dir();
		$this_plugin_dir = trailingslashit( dirname($plugins_dir . $plugin) );

		if ( ! $wp_filesystem->exists($this_plugin_dir) ) //If it's already vanished.
			return $removed;

		// If plugin is in its own directory, recursively delete the directory.
		if ( strpos($plugin, '/') && $this_plugin_dir !== $plugins_dir ) //base check on if plugin includes directory separator AND that it's not the root plugin folder
			$deleted = $wp_filesystem->delete($this_plugin_dir, true);
		else
			$deleted = $wp_filesystem->delete($plugins_dir . $plugin);

		if ( ! $deleted )
			return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);

		return true;
	}

	//return plugin info.
	function plugin_info() {
		if ( ! is_array($this->result) || empty($this->result['destination_name']) )
			return false;

		$plugin = get_plugins('/' . $this->result['destination_name']); //Ensure to pass with leading slash
		if ( empty($plugin) )
			return false;

		$pluginfiles = array_keys($plugin); //Assume the requested plugin is the first in the list

		return $this->result['destination_name'] . '/' . $pluginfiles[0];
	}
}