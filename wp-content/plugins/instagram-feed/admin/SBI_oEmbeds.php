<?php

/**
 * The Settings Page
 *
 * @since 4.0
 */

namespace InstagramFeed\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use InstagramFeed\SBI_View;
use InstagramFeed\SBI_Response;

class SBI_oEmbeds {
    /**
	 * Admin menu page slug.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	const SLUG = 'sbi-oembeds-manager';

	/**
	 * Initializing the class
	 *
	 * @since 4.0
	 */
	function __construct(){
		$this->init();
	}

    /**
	 * Determining if the user is viewing the our page, if so, party on.
	 *
	 * @since 4.0
	 */
	public function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action('admin_menu', [$this, 'register_menu']);

		add_action( 'wp_ajax_disable_instagram_oembed_from_instagram', [$this, 'disable_instagram_oembed_from_instagram'] );
		add_action( 'wp_ajax_disable_facebook_oembed_from_instagram', [$this, 'disable_facebook_oembed_from_instagram'] );
	}

	/**
	 * Register Menu.
	 *
	 * @since 4.0
	 */
	function register_menu() {
        $cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';
        $cap = apply_filters( 'sbi_settings_pages_capability', $cap );

       $oembeds_manager = add_submenu_page(
           'sb-instagram-feed',
           __( 'oEmbeds', 'instagram-feed' ),
           __( 'oEmbeds', 'instagram-feed' ),
           $cap,
           self::SLUG,
           [$this, 'oembeds_manager'],
           2
       );
       add_action( 'load-' . $oembeds_manager, [$this,'oembeds_enqueue_admin_scripts']);
   }

	/**
	 * Disable Instagram oEmbed
	 *
	 * @since 4.0
	 *
	 * @return SBI_Response
	 */
	public function disable_instagram_oembed_from_instagram () {
		check_ajax_referer( 'sbi-admin', 'nonce'  );

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error(); // This auto-dies.
		}
		$oembed_settings = get_option( 'sbi_oembed_token', array() );
		$oembed_settings['access_token'] = '';
		$oembed_settings['disabled'] = true;
		update_option( 'sbi_oembed_token', $oembed_settings );

		new SBI_Response( true, array(
			'connectionUrl' => $this->get_connection_url()
		) );
	}

	/**
	 * Disable Facebook oEmbed
	 *
	 * @since 4.0
	 *
	 * @return SBI_Response
	 */
	public function disable_facebook_oembed_from_instagram () {
		check_ajax_referer( 'sbi-admin', 'nonce'  );

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error(); // This auto-dies.
		}
		$oembed_settings = get_option( 'cff_oembed_token', array() );
		$oembed_settings['access_token'] = '';
		$oembed_settings['disabled'] = true;
		update_option( 'cff_oembed_token', $oembed_settings );

		new SBI_Response( true, array(
			'connectionUrl' => $this->get_connection_url()
		) );
	}

   	/**
	 * Enqueue oEmbeds CSS & Script.
	 *
	 * Loads only for oEmbeds page
	 *
	 * @since 4.0
	 */
    public function oembeds_enqueue_admin_scripts(){
        if( ! get_current_screen() ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! 'instagram-feed_page_sbi-oembeds-manager' === $screen->id ) {
            return;
		}

		wp_enqueue_style(
			'oembeds-style',
			SBI_PLUGIN_URL . 'admin/assets/css/oembeds.css',
			false,
			SBIVER
		);

		wp_enqueue_script(
            'sb-vue',
            SBI_PLUGIN_URL . 'js/vue.min.js',
            null,
            '2.6.12',
            true
        );

		wp_enqueue_script(
			'oembeds-app',
			SBI_PLUGIN_URL.'admin/assets/js/oembeds.js',
			null,
			SBIVER,
			true
		);

		$sbi_oembends = $this->statuses_and_info();
		$sbi_oembends['nonce'] = wp_create_nonce( 'sbi-admin' );

        wp_localize_script(
			'oembeds-app',
			'sbi_oembeds',
			$sbi_oembends
		);
    }

	/**
	 * Statuses and info about the current state of oEmbed connection
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function statuses_and_info() {
		$return = array(
			'admin_url' 		=> admin_url(),
			'ajax_handler'		=> admin_url( 'admin-ajax.php' ),
            'supportPageUrl'    => admin_url( 'admin.php?page=sbi-support' ),
			'links'				=> \InstagramFeed\Builder\SBI_Feed_Builder::get_links_with_utm(),
			'socialWallLinks'   => \InstagramFeed\Builder\SBI_Feed_Builder::get_social_wall_links(),
			'socialWallActivated' => is_plugin_active( 'social-wall/social-wall.php' ),
			'genericText'       => array(
				'help' => __( 'Help', 'instagram-feed' ),
				'title' => __( 'oEmbeds', 'instagram-feed' ),
				'description' => __( 'Use Smash Balloon to power any Instagram or Instagram oEmbeds across your site. Just click the button below and we\'ll do the rest.                ', 'instagram-feed' ),
				'instagramOEmbeds' => __( 'Instagram oEmbeds are currently not being handled by Smash Balloon', 'instagram-feed' ),
				'instagramOEmbedsEnabled' => __( 'Instagram oEmbeds are turned on', 'instagram-feed' ),
				'facebookOEmbeds' => __( 'Facebook oEmbeds are currently not being handled by Smash Balloon', 'instagram-feed' ),
				'facebookOEmbedsEnabled' => __( 'Facebook oEmbeds are turned on', 'instagram-feed' ),
				'enable' => __( 'Enable', 'instagram-feed' ),
				'disable' => __( 'Disable', 'instagram-feed' ),
				'whatAreOembeds' => __( 'What are oEmbeds?', 'instagram-feed' ),
				'whatElseOembeds' => __( 'What else can the Instagram Feed plugin do?', 'instagram-feed' ),
				'whenYouPaste' => __( 'When you paste a link to a Instagram or Facebook post in WordPress, it automatically displays the post instead of the URL. That is called an oEmbed.', 'instagram-feed' ),
				'dueToRecent' => __( 'Due to recent API changes from Instagram, WordPress cannot automatically embed your posts.', 'instagram-feed' ),
				'however' => __( 'However, we have added this feature to Smash Balloon to make sure your oEmbeds keep working.', 'instagram-feed' ),
				'justEnable' => __( 'Just enable it above, and all your existing and new embeds should work automatically, no other input required.', 'instagram-feed' ),
				'displayACompletely' => __( 'Display a completely customizable Instagram Feed with tons of features', 'instagram-feed' ),
				'createACustom' => __( 'Create a custom styled feed of your Instagram posts which integrates seamlessly with your WordPress theme.', 'instagram-feed' ),
            ),
            'images' => array(
                'fbIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.04004C6.5 2.04004 2 6.53004 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85004C10.44 7.34004 11.93 5.96004 14.22 5.96004C15.31 5.96004 16.45 6.15004 16.45 6.15004V8.62004H15.19C13.95 8.62004 13.56 9.39004 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96C15.9164 21.5879 18.0622 20.3856 19.6099 18.5701C21.1576 16.7546 22.0054 14.4457 22 12.06C22 6.53004 17.5 2.04004 12 2.04004Z" fill="#006BFA"/></svg>',
                'instaIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 6.60938C9 6.60938 6.60938 9.04688 6.60938 12C6.60938 15 9 17.3906 12 17.3906C14.9531 17.3906 17.3906 15 17.3906 12C17.3906 9.04688 14.9531 6.60938 12 6.60938ZM12 15.5156C10.0781 15.5156 8.48438 13.9688 8.48438 12C8.48438 10.0781 10.0312 8.53125 12 8.53125C13.9219 8.53125 15.4688 10.0781 15.4688 12C15.4688 13.9688 13.9219 15.5156 12 15.5156ZM18.8438 6.42188C18.8438 5.71875 18.2812 5.15625 17.5781 5.15625C16.875 5.15625 16.3125 5.71875 16.3125 6.42188C16.3125 7.125 16.875 7.6875 17.5781 7.6875C18.2812 7.6875 18.8438 7.125 18.8438 6.42188ZM22.4062 7.6875C22.3125 6 21.9375 4.5 20.7188 3.28125C19.5 2.0625 18 1.6875 16.3125 1.59375C14.5781 1.5 9.375 1.5 7.64062 1.59375C5.95312 1.6875 4.5 2.0625 3.23438 3.28125C2.01562 4.5 1.64062 6 1.54688 7.6875C1.45312 9.42188 1.45312 14.625 1.54688 16.3594C1.64062 18.0469 2.01562 19.5 3.23438 20.7656C4.5 21.9844 5.95312 22.3594 7.64062 22.4531C9.375 22.5469 14.5781 22.5469 16.3125 22.4531C18 22.3594 19.5 21.9844 20.7188 20.7656C21.9375 19.5 22.3125 18.0469 22.4062 16.3594C22.5 14.625 22.5 9.42188 22.4062 7.6875ZM20.1562 18.1875C19.8281 19.125 19.0781 19.8281 18.1875 20.2031C16.7812 20.7656 13.5 20.625 12 20.625C10.4531 20.625 7.17188 20.7656 5.8125 20.2031C4.875 19.8281 4.17188 19.125 3.79688 18.1875C3.23438 16.8281 3.375 13.5469 3.375 12C3.375 10.5 3.23438 7.21875 3.79688 5.8125C4.17188 4.92188 4.875 4.21875 5.8125 3.84375C7.17188 3.28125 10.4531 3.42188 12 3.42188C13.5 3.42188 16.7812 3.28125 18.1875 3.84375C19.0781 4.17188 19.7812 4.92188 20.1562 5.8125C20.7188 7.21875 20.5781 10.5 20.5781 12C20.5781 13.5469 20.7188 16.8281 20.1562 18.1875Z" fill="url(#paint0_linear)"/><defs><linearGradient id="paint0_linear" x1="8.95781" y1="41.6859" x2="53.1891" y2="-3.46406" gradientUnits="userSpaceOnUse"><stop stop-color="white"/><stop offset="0.147864" stop-color="#F6640E"/><stop offset="0.443974" stop-color="#BA03A7"/><stop offset="0.733337" stop-color="#6A01B9"/><stop offset="1" stop-color="#6B01B9"/></linearGradient></defs></svg>',
                'image1_2x' => SBI_PLUGIN_URL . 'admin/assets/img/oembeds-image-1@2x.png',
                'image2_2x' => SBI_PLUGIN_URL . 'admin/assets/img/oembeds-image-2@2x.png',
                'image3_2x' => SBI_PLUGIN_URL . 'admin/assets/img/oembeds-image-3@2x.png',
                'image4_2x' => SBI_PLUGIN_URL . 'admin/assets/img/oembeds-image-4@2x.png',
			),
			'modal' => array(
				'title' => __( 'Enable Facebook oEmbeds', 'instagram-feed' ),
				'description' => __( 'To enable Instagram oEmbeds our Custom Facebok Feed plugin is required. Click the button below to Install it and enable Instagram oEmbeds.', 'instagram-feed' ),
				'install' => __( 'Install Plugin', 'instagram-feed' ),
				'activate' => __( 'Activate Plugin', 'instagram-feed' ),
				'cancel' => __( 'Cancel', 'instagram-feed' ),
				'instaIcon' => SBI_PLUGIN_URL . 'admin/assets/img/instagram-color-icon.svg',
				'timesIcon' => '<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.2084 2.14275L12.8572 0.791504L7.50008 6.14859L2.143 0.791504L0.791748 2.14275L6.14883 7.49984L0.791748 12.8569L2.143 14.2082L7.50008 8.85109L12.8572 14.2082L14.2084 12.8569L8.85133 7.49984L14.2084 2.14275Z" fill="#141B38"/></svg>',
				'plusIcon' => '<svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.0832 6.83317H7.08317V11.8332H5.4165V6.83317H0.416504V5.1665H5.4165V0.166504H7.08317V5.1665H12.0832V6.83317Z" fill="white"/></svg>'
			),
			'loaderSVG' => '<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve"><path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"/></path></svg>',
			'checkmarkSVG' => '<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>',
			'timesCircleSVG' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!-- Font Awesome Pro 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) --><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm101.8-262.2L295.6 256l62.2 62.2c4.7 4.7 4.7 12.3 0 17l-22.6 22.6c-4.7 4.7-12.3 4.7-17 0L256 295.6l-62.2 62.2c-4.7 4.7-12.3 4.7-17 0l-22.6-22.6c-4.7-4.7-4.7-12.3 0-17l62.2-62.2-62.2-62.2c-4.7-4.7-4.7-12.3 0-17l22.6-22.6c4.7-4.7 12.3-4.7 17 0l62.2 62.2 62.2-62.2c4.7-4.7 12.3-4.7 17 0l22.6 22.6c4.7 4.7 4.7 12.3 0 17z"/></svg>'
		);

		$oembed_token_settings = get_option( 'sbi_oembed_token', array() );
		$saved_access_token_data = isset( $oembed_token_settings['access_token'] ) ? $oembed_token_settings['access_token'] : false;
		$newly_retrieved_oembed_connection_data = $this->maybe_connection_data( $saved_access_token_data );
		if ( ! empty( $newly_retrieved_oembed_connection_data['access_token'] ) ) {
			$oembed_token_settings = $newly_retrieved_oembed_connection_data;
			$return['newOembedData'] = $newly_retrieved_oembed_connection_data;

			update_option( 'cff_oembed_token', $newly_retrieved_oembed_connection_data );
			update_option( 'sbi_oembed_token', $newly_retrieved_oembed_connection_data );

			// If the access token is new or has changed, then we need to clear the cache.
			$this->clear_oembed_cache();
		} elseif ( ! empty( $newly_retrieved_oembed_connection_data ) ) {
			$return['newOembedData'] = $newly_retrieved_oembed_connection_data;
		}
		$return['connectionURL'] = $this->get_connection_url();
		$return['tokenData'] = $oembed_token_settings;

		$return['instagram'] = array(
			'doingOembeds' => $this->instagram_oembed_enabled()
		);
		$return['facebook'] = [
			'active' => class_exists( '\CustomFacebookFeed\CFF_Oembed' ),
			'doingOembeds' => false
		];

		$return['facebook']['installer'] = $this->facebook_installer_info();

		if ( class_exists( '\CustomFacebookFeed\CFF_Oembed' ) ) {
			$return['facebook']['doingOembeds'] = \CustomFacebookFeed\CFF_Oembed::can_do_oembed();
		}

		return $return;
	}

	/**
	 * Clear instagram oembed transients and cache
	 *
	 * @since 6.1.2
	 */
	public static function clear_oembed_cache() {

		// get _transient_oembed_* options from wp_options.
		global $wpdb;
		$table_name        = $wpdb->prefix . 'options';
		$transient_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM $table_name WHERE option_name LIKE %s AND option_value LIKE %s",
				'_transient_oembed_%',
				'%fbtrace_id%'
			)
		);

		foreach ( $transient_options as $value ) {
			$option_name  = $value->option_name;
			delete_option( $option_name );

			// find the _transient_timeout_oembed_* options and delete them.
			$option_key    = substr( $option_name, 18 );
			$timeout_key   = '_transient_timeout_oembed_' . $option_key;
			$timeout_value = get_option( $timeout_key );
			if ( is_numeric( $timeout_value ) ) {
				delete_option( $timeout_key );
			}
		}

		// get _oembed_* options from wp_postmeta.
		$postmeta_table = $wpdb->prefix . 'postmeta';
		$oembed_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_key, meta_value FROM $postmeta_table WHERE meta_key LIKE %s AND meta_value LIKE %s",
				'_oembed_%',
				'{{unknown}}'
			)
		);

		foreach ( $oembed_options as $value ) {
			$post_id    = $value->post_id;
			$meta_key   = $value->meta_key;
			$meta_value = $value->meta_value;
			$meta_value = maybe_unserialize( $meta_value );

			delete_post_meta( $post_id, $meta_key );

			// get the cache key.
			$cache_key        = substr( $meta_key, 8 );
			$cache_meta_key   = '_oembed_time_' . $cache_key;
			$cache_meta_value = get_post_meta( $post_id, $cache_meta_key, true );
			if ( is_numeric( $cache_meta_value ) ) {
				delete_post_meta( $post_id, $cache_meta_key );
			}
		}
	}

	/**
	 * Connection URLs are based on the website connecting accounts so that is
	 * configured here and returned
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	public static function get_connection_url() {

		$admin_url_state = admin_url( 'admin.php?page=sbi-oembeds-manager' );
		//If the admin_url isn't returned correctly then use a fallback
		if( $admin_url_state == '/wp-admin/admin.php?page=sbi-oembeds-manager' ){
			$admin_url_state = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		}

		if ( class_exists( '\CustomFacebookFeed\CFF_Oembed' ) ) {
			$cff_oembed_token = \CustomFacebookFeed\CFF_Oembed::last_access_token();

			if ( ! empty( $cff_oembed_token ) ) {
				return add_query_arg( 'transfer', '1', $admin_url_state );
			}
		}

		return 'https://www.facebook.com/dialog/oauth?client_id=254638078422287&redirect_uri=https://api.smashballoon.com/v2/instagram-graph-api-redirect.php&scope=pages_show_list&state=' . $admin_url_state;
	}

	/**
	 * Listener for retrieving and storing an access token for oEmbeds
	 *
	 * @param string $saved_access_token_data
	 *
	 * @return array|bool
	 *
	 * @since 4.0
	 */
	public static function maybe_connection_data( $saved_access_token_data ) {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}
		if( ! isset( $_GET['page'] ) && 'sbi-oembeds-manager' !== $_GET['page'] ) {
			return false;
		}
		if ( ! empty( $_GET['transfer'] ) ) {
			if ( class_exists( '\CustomFacebookFeed\CFF_Oembed' ) ) {
				$cff_oembed_token = \CustomFacebookFeed\CFF_Oembed::last_access_token();
				$return = get_option( 'cff_oembed_token', array() );

				$return['access_token'] = $cff_oembed_token;
				$return['disabled'] = false;

				return $return;
			}
		} if ( isset( $_GET['sbi_access_token'] ) ) {
			$access_token = sbi_sanitize_alphanumeric_and_equals( $_GET['sbi_access_token'] );

			$return = [];

			$valid_new_access_token = ! empty( $access_token ) && strlen( $access_token ) > 20 && $saved_access_token_data !== $access_token ? $access_token : false;
			if ( $valid_new_access_token ) {
				$url = esc_url_raw( 'https://graph.instagram.com/me/accounts?limit=500&access_token=' . $valid_new_access_token );
				$pages_data_connection = wp_remote_get( $url );
				$return['access_token'] = $valid_new_access_token;
				$return['disabled'] = false;
				if ( ! is_wp_error( $pages_data_connection ) && isset( $pages_data_connection['body'] ) ) {
					$pages_data = json_decode( $pages_data_connection['body'], true );
					if ( isset( $pages_data['data'][0]['access_token'] ) ) {
						$return['expiration_date'] = 'never';
					} else {
						$return['expiration_date'] = time() + (60 * DAY_IN_SECONDS);
					}
				} else {
					$return['expiration_date'] = 'unknown';
				}
			} else {
				if ( $saved_access_token_data === $access_token ) {
					$return['error'] = 'Not New';
				} else {
					$return['error'] = 'Not Valid';
				}
			}

			return $return;

		}

		return false;
	}

	/**
	 * Check if Instagram oEmbed is enabled or not
	 *
	 * @return bool
	 *
	 * @since 4.0
	 */
	public function instagram_oembed_enabled() {
		$sbi_oembed_token = get_option( 'sbi_oembed_token' );
		if ( isset( $sbi_oembed_token['access_token'] ) && isset( $sbi_oembed_token['disabled'] ) && ! $sbi_oembed_token['disabled'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if Facebook oEmbed is enabled or not
	 *
	 * @return bool
	 *
	 * @since 4.0
	 */
	public function facebook_oembed_enabled() {
		$cff_oembed_token = get_option( 'cff_oembed_token' );
		if ( isset( $cff_oembed_token['access_token'] ) && isset( $cff_oembed_token['disabled'] ) && ! $cff_oembed_token['disabled'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Determines what action for Instagram should be done in the following order
	 * and returns data used in the common "addon" installer
	 *
	 * Free or Pro active, do nothing
	 * Pro installed but not active, activate Pro
	 * Free installed but not active, activate Free
	 * Nothing installed, install and activate Free
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public static function facebook_installer_info() {
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins' );

		if ( in_array( 'custom-facebook-feed/custom-facebook-feed.php', $active_plugins, true )
			|| in_array( 'custom-facebook-feed-pro/custom-facebook-feed.php', $active_plugins, true ) ) {
			return [
				'nextStep' => 'none',
				'plugin' => 'none',
				'action' => 'none'
			];
		}

		foreach ( $all_plugins as $plugin ) {
			if ( strpos( $plugin['Name'], 'Custom Facebook Feed Pro' ) !== false ) {
				return [
					'nextStep' => 'pro_activate',
					'plugin' => 'custom-facebook-feed-pro/custom-facebook-feed.php',
					'action' => 'sbi_activate_addon'
				];
			}
			if ( strpos( $plugin['Name'], 'Custom Facebook Feed' ) !== false ) {
				return [
					'nextStep' => 'free_activate',
					'plugin' => 'custom-facebook-feed/custom-facebook-feed.php',
					'action' => 'sbi_activate_addon'
				];
			}
		}

		return [
			'nextStep' => 'free_install',
			'plugin' => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
			'action' => 'sbi_install_addon'
		];
	}

   	/**
	 * oEmbeds Manager Page View Template
	 *
	 * @since 4.0
	 */
	public function oembeds_manager(){
		return \InstagramFeed\SBI_View::render( 'oembeds.index' );
	}
}
