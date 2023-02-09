<?php
/**
 * Class SB_Instagram_GDPR_Integrations
 *
 * Adds GDPR related workarounds for third-party plugins:
 * https://wordpress.org/plugins/cookie-law-info/
 *
 * @since 2.6/5.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_GDPR_Integrations {

	/**
	 * Undoing of Cookie Notice's Instagram Feed related code
	 * needs to be done late.
	 */
	public static function init() {
		add_filter( 'wt_cli_third_party_scripts', array( 'SB_Instagram_GDPR_Integrations', 'undo_script_blocking' ), 11, 1 );
	}

	/**
	 * Prevents changes made to how JavaScript file is added to
	 * pages.
	 *
	 * @param array $blocking
	 *
	 * @return array
	 */
	public static function undo_script_blocking( $blocking ) {
		$settings = sbi_get_database_settings();
		if ( ! self::doing_gdpr( $settings ) ) {
			return $blocking;
		}
		unset( $blocking['instagram-feed'] );
		return $blocking;
	}

	/**
	 * Whether or not consent plugins that Instagram Feed
	 * is compatible with are active.
	 *
	 * @return bool|string
	 */
	public static function gdpr_plugins_active() {
		if ( class_exists( 'Cookie_Notice' ) ) {
			return 'Cookie Notice by dFactory';
		}
		if ( class_exists( 'Cookie_Law_Info' ) ) {
			return 'GDPR Cookie Consent by WebToffee';
		}
		if ( class_exists( 'Cookiebot_WP' ) ) {
			return 'Cookiebot by Cybot A/S';
		}
		if ( class_exists( 'COMPLIANZ' ) ) {
			return 'Complianz by Really Simple Plugins';
		}
		if ( function_exists( 'BorlabsCookieHelper' ) ) {
			return 'Borlabs Cookie by Borlabs';
		}

		return false;
	}

	/**
	 * GDPR features can be added automatically, forced enabled,
	 * or forced disabled.
	 *
	 * @param $settings
	 *
	 * @return bool
	 */
	public static function doing_gdpr( $settings ) {
		$gdpr = isset( $settings['gdpr'] ) ? $settings['gdpr'] : 'auto';
		if ( $gdpr === 'no' ) {
			return false;
		}
		if ( $gdpr === 'yes' ) {
			return true;
		}
		return ( self::gdpr_plugins_active() !== false );
	}

	public static function blocking_cdn( $settings ) {
		$gdpr = isset( $settings['gdpr'] ) ? $settings['gdpr'] : 'auto';
		if ( $gdpr === 'no' ) {
			return false;
		}
		if ( $gdpr === 'yes' ) {
			return true;
		}
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		if ( $sbi_statuses_option['gdpr']['from_update_success'] ) {
			return ( self::gdpr_plugins_active() !== false );
		}
		return false;
	}

	/**
	 * GDPR features are reliant on the image resizing features
	 *
	 * @param bool $retest
	 *
	 * @return bool
	 */
	public static function gdpr_tests_successful( $retest = false ) {
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		if ( ! isset( $sbi_statuses_option['gdpr']['image_editor'] ) || $retest ) {
			$test_image = trailingslashit( SBI_PLUGIN_URL ) . 'img/placeholder.png';

			$image_editor = wp_get_image_editor( $test_image );

			$sbi_statuses_option['gdpr']['image_editor'] = false;
			// not uncommon for the image editor to not work using it this way
			if ( ! is_wp_error( $image_editor ) ) {
				$sbi_statuses_option['gdpr']['image_editor'] = true;
			} else {
				$test_image = 'https://plugin.smashballoon.com/editor-test.png';

				$image_editor = wp_get_image_editor( $test_image );
				if ( ! is_wp_error( $image_editor ) ) {
					$sbi_statuses_option['gdpr']['image_editor'] = true;
				} else {

					if ( ! function_exists( 'download_url' ) ) {
						include_once ABSPATH . 'wp-admin/includes/file.php';
					}
					// Set a timeout for downloading the image
					$timeout_seconds = 5;

					// Download file to temp dir.
					$temp_file = download_url( $test_image, $timeout_seconds );

					$image_editor = wp_get_image_editor( $temp_file );
					if ( ! is_wp_error( $image_editor ) ) {
						$sbi_statuses_option['gdpr']['image_editor'] = true;
					}

					@unlink( $temp_file );
				}
			}

			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = trailingslashit( $upload_dir ) . SBI_UPLOADS_NAME;
			if ( file_exists( $upload_dir ) ) {
				$sbi_statuses_option['gdpr']['upload_dir'] = true;
			} else {
				$sbi_statuses_option['gdpr']['upload_dir'] = false;
			}

			global $wpdb;
			$table_name                            = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
			$sbi_statuses_option['gdpr']['tables'] = true;
			if ( $wpdb->get_var( "show tables like '$table_name'" ) !== $table_name ) {
				$sbi_statuses_option['gdpr']['tables'] = false;
			}

			$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;
			if ( $wpdb->get_var( "show tables like '$feeds_posts_table_name'" ) !== $feeds_posts_table_name ) {
				$sbi_statuses_option['gdpr']['tables'] = false;
			}

			update_option( 'sbi_statuses', $sbi_statuses_option );
		}

		if ( $retest ) {
			global $sb_instagram_posts_manager;
			$sb_instagram_posts_manager->add_action_log( 'Retesting GDPR features.' );
		}

		if ( ! $sbi_statuses_option['gdpr']['upload_dir']
		     || ! $sbi_statuses_option['gdpr']['tables']
		     || ! $sbi_statuses_option['gdpr']['image_editor'] ) {
			return false;
		}

		return true;
	}

	public static function gdpr_tests_error_message() {
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		$errors = array();
		if ( ! $sbi_statuses_option['gdpr']['upload_dir'] ) {
			$errors[] = __( 'A folder for storing resized images was not successfully created.', 'instagram-feed' );
		}
		if ( ! $sbi_statuses_option['gdpr']['tables'] ) {
			$errors[] = __( 'Tables used for storing information about resized images were not successfully created.', 'instagram-feed' );
		}
		if ( ! $sbi_statuses_option['gdpr']['image_editor'] ) {
			$errors[] = sprintf( __( 'An image editor is not available on your server. Instagram Feed is unable to create local resized images. See %1$sthis FAQ%2$s for more information', 'instagram-feed' ), '<a href="https://smashballoon.com/doc/the-images-in-my-feed-are-missing-or-showing-errors/" target="_blank" rel="noopener noreferrer">', '</a>' );
		}

		if ( isset( $_GET['tab'] ) && $_GET['tab'] !== 'support' ) {
			$tab      = sbi_is_pro_version() ? 'customize-advanced' : 'customize';
			$errors[] = '<a href="?page=sbi-settings&amp;tab=' . $tab . '&amp;retest=1" class="button button-secondary">' . __( 'Retest', 'instagram-feed' ) . '</a>';
		}

		return implode( '<br>', $errors );
	}

	public static function statuses() {
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		$return = isset( $sbi_statuses_option['gdpr'] ) ? $sbi_statuses_option['gdpr'] : array();
		return $return;
	}

}
