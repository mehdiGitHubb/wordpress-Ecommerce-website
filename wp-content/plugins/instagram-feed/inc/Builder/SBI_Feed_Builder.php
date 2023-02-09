<?php
/**
 * Custom Facebook Feed Builder
 *
 * @since 6.0
 */
namespace InstagramFeed\Builder;

use InstagramFeed\Builder\Tabs\SBI_Styling_Tab;
use InstagramFeed\Builder\SBI_Feed_Saver;


class SBI_Feed_Builder {
	private static $instance;
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			return self::$instance;

		}
	}


	/**
	 * Constructor.
	 *
	 * @since 6.0
	 */
	function __construct() {
		$this->init();
	}

	/**
	 * Init the Builder.
	 *
	 * @since 6.0
	 */
	function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_menu' ) );
			// add ajax listeners
			SBI_Feed_Saver_Manager::hooks();
			SBI_Source::hooks();
			self::hooks();
		}
	}

	/**
	 * Mostly AJAX related hooks
	 *
	 * @since 6.0
	 */
	public static function hooks() {
		add_action( 'wp_ajax_sbi_dismiss_onboarding', array( 'InstagramFeed\Builder\SBI_Feed_Builder', 'after_dismiss_onboarding' ) );

		add_action( 'wp_ajax_sbi_other_plugins_modal', array( 'InstagramFeed\Builder\SBI_Feed_Builder', 'sb_other_plugins_modal' ) );

	}

	/**
	 * Register Menu.
	 *
	 * @since 6.0
	 */
	function register_menu() {
		$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';
		$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

		$feed_builder = add_submenu_page(
			'sb-instagram-feed',
			__( 'All Feeds', 'instagram-feed' ),
			__( 'All Feeds', 'instagram-feed' ),
			$cap,
			'sbi-feed-builder',
			array( $this, 'feed_builder' ),
			0
		);
		add_action( 'load-' . $feed_builder, array( $this, 'builder_enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue Builder CSS & Script.
	 *
	 * Loads only for builder pages
	 *
	 * @since 6.0
	 */
	public function builder_enqueue_admin_scripts() {
		if ( get_current_screen() ) :
			$screen = get_current_screen();
			if ( strpos( $screen->id, 'sbi-feed-builder' ) !== false ) :
				$installed_plugins = get_plugins();

				$newly_retrieved_source_connection_data = SBI_Source::maybe_source_connection_data();
				$license_key                            = get_option( 'sbi_license_key', '' );
				$upgrade_url                            = 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=lite-upgrade-bar';

				$active_extensions = array(
					// Fake
					'feedLayout'       => false,
					'headerLayout'     => false,
					'postStyling'      => false,
					'lightbox'         => false,
					'filtermoderation' => false,
					'shoppablefeed'    => false,

				);

				$sbi_builder = array(
					'ajax_handler'         => admin_url( 'admin-ajax.php' ),
					'pluginType'           => 'free',
					'licenseType'          => sbi_is_pro_version() ? 'pro' : 'free',

					'builderUrl'           => admin_url( 'admin.php?page=sbi-feed-builder' ),
					'upgradeUrl'           => $upgrade_url,
					'activeExtensions'     => $active_extensions,
					'pluginUrl'            => trailingslashit( SBI_PLUGIN_URL ),

					'nonce'                => wp_create_nonce( 'sbi-admin' ),
					'adminPostURL'         => admin_url( 'post.php' ),
					'widgetsPageURL'       => admin_url( 'widgets.php' ),
					'supportPageUrl'       => admin_url( 'admin.php?page=sbi-support' ),
					'genericText'          => self::get_generic_text(),
					'welcomeScreen'        => array(
						'mainHeading'              => __( 'All Feeds', 'instagram-feed' ),
						'createFeed'               => __( 'Create your Feed', 'instagram-feed' ),
						'createFeedDescription'    => __( 'Connect your Instagram account and choose a feed type', 'instagram-feed' ),
						'customizeFeed'            => __( 'Customize your feed type', 'instagram-feed' ),
						'customizeFeedDescription' => __( 'Choose layouts, color schemes, styles and more', 'instagram-feed' ),
						'embedFeed'                => __( 'Embed your feed', 'instagram-feed' ),
						'embedFeedDescription'     => __( 'Easily add the feed anywhere on your website', 'instagram-feed' ),
						'customizeImgPath'         => SBI_BUILDER_URL . 'assets/img/welcome-1.png',
						'embedImgPath'             => SBI_BUILDER_URL . 'assets/img/welcome-2.png',
					),
					'pluginsInfo'          => array(
						'social_wall' => array(
							'installed'    => isset( $installed_plugins['social-wall/social-wall.php'] ) ? true : false,
							'activated'    => is_plugin_active( 'social-wall/social-wall.php' ),
							'settingsPage' => admin_url( 'admin.php?page=sbsw' ),
						)
					),
					'allFeedsScreen'       => array(
						'mainHeading'     => __( 'All Feeds', 'instagram-feed' ),
						'columns'         => array(
							'nameText'      => __( 'Name', 'instagram-feed' ),
							'shortcodeText' => __( 'Shortcode', 'instagram-feed' ),
							'instancesText' => __( 'Instances', 'instagram-feed' ),
							'actionsText'   => __( 'Actions', 'instagram-feed' ),
						),
						'bulkActions'     => __( 'Bulk Actions', 'instagram-feed' ),
						'legacyFeeds'     => array(
							'heading'               => __( 'Legacy Feeds', 'instagram-feed' ),
							'toolTip'               => __( 'What are Legacy Feeds?', 'instagram-feed' ),
							'toolTipExpanded'       => array(
								__( 'Legacy feeds are older feeds from before the version 6 update. You can edit settings for these feeds by using the "Settings" button to the right. These settings will apply to all legacy feeds, just like the settings before version 6, and work in the same way that they used to.', 'instagram-feed' ),
								__( 'You can also create a new feed, which will now have it\'s own individual settings. Modifying settings for new feeds will not affect other feeds.', 'instagram-feed' ),
							),
							'toolTipExpandedAction' => array(
								__( 'Legacy feeds represent shortcodes of old feeds found on your website before <br/>the version 6 update.', 'instagram-feed' ),
								__( 'To edit Legacy feed settings, you will need to use the "Settings" button above <br/>or edit their shortcode settings directly. To delete them, simply remove the <br/>shortcode wherever it is being used on your site.', 'instagram-feed' ),
							),
							'show'                  => __( 'Show Legacy Feeds', 'instagram-feed' ),
							'hide'                  => __( 'Hide Legacy Feeds', 'instagram-feed' ),
						),
						'socialWallLinks' => self::get_social_wall_links(),
						'onboarding'      => $this->get_onboarding_text()
					),
					'dialogBoxPopupScreen' => array(
						'deleteSourceCustomizer' => array(
							'heading'     => __( 'Delete "#"?', 'instagram-feed' ),
							'description' => __( 'You are going to delete this source. To retrieve it, you will need to add it again. Are you sure you want to continue?', 'instagram-feed' ),
						),
						'deleteSingleFeed'       => array(
							'heading'     => __( 'Delete "#"?', 'instagram-feed' ),
							'description' => __( 'You are going to delete this feed. You will lose all the settings. Are you sure you want to continue?', 'instagram-feed' ),
						),
						'deleteMultipleFeeds'    => array(
							'heading'     => __( 'Delete Feeds?', 'instagram-feed' ),
							'description' => __( 'You are going to delete these feeds. You will lose all the settings. Are you sure you want to continue?', 'instagram-feed' ),
						),
						'backAllToFeed'          => array(
							'heading'       => __( 'Are you Sure?', 'instagram-feed' ),
							'description'   => __( 'Are you sure you want to leave this page, all unsaved settings will be lost, please make sure to save before leaving.', 'instagram-feed' ),
							'customButtons' => array(
								'confirm' => array(
									'text'  => __( 'Save and Exit', 'instagram-feed' ),
									'color' => 'blue',
								),
								'cancel'  => array(
									'text'  => __( 'Exit without Saving', 'instagram-feed' ),
									'color' => 'red',
								),
							),
						),
						'unsavedFeedSources'     => array(
							'heading'       => __( 'You have unsaved changes', 'instagram-feed' ),
							'description'   => __( 'If you exit without saving, all the changes you made will be reverted.', 'instagram-feed' ),
							'customButtons' => array(
								'confirm' => array(
									'text'  => __( 'Save and Exit', 'instagram-feed' ),
									'color' => 'blue'
								),
								'cancel'  => array(
									'text'  => __( 'Exit without Saving', 'instagram-feed' ),
									'color' => 'red'
								)
							)
						)
					),
					'selectFeedTypeScreen' => array(
						'mainHeading'            => __( 'Create an Instagram Feed', 'instagram-feed' ),
						'feedTypeHeading'        => __( 'Select Feed Type', 'instagram-feed' ),
						'mainDescription'        => __( 'Select one or more feed types. You can add or remove them later.', 'instagram-feed' ),
						'updateHeading'          => __( 'Update Feed Type', 'instagram-feed' ),
						'advancedHeading'        => __( 'Advanced Feeds', 'instagram-feed' ),
						'anotherFeedTypeHeading' => __( 'Add Another Source Type', 'instagram-feed' ),
					),
					'mainFooterScreen'     => array(
						'heading'     => sprintf( __( 'Upgrade to the %1$sAll Access Bundle%2$s to get all of our Pro Plugins', 'instagram-feed' ), '<strong>', '</strong>' ),
						'description' => __( 'Includes all Smash Balloon plugins for one low price: Instagram, Facebook, Twitter, YouTube, and Social Wall', 'instagram-feed' ),
						'promo'       => sprintf( __( '%1$sBonus%2$s Lite users get %3$s50&#37; Off%4$s automatically applied at checkout', 'instagram-feed' ), '<span class="sbi-bld-ft-bns">', '</span>', '<strong>', '</strong>' ),
					),
					'embedPopupScreen'     => array(
						'heading'       => __( 'Embed Feed', 'instagram-feed' ),
						'description'   => __( 'Add the unique shortcode to any page, post, or widget:', 'instagram-feed' ),
						'description_2' => __( 'Or use the built in WordPress block or widget', 'instagram-feed' ),
						'addPage'       => __( 'Add to a Page', 'instagram-feed' ),
						'addWidget'     => __( 'Add to a Widget', 'instagram-feed' ),
						'selectPage'    => __( 'Select Page', 'instagram-feed' ),
					),
					'links'                => self::get_links_with_utm(),
					'pluginsInfo'          => array(
						'social_wall' => array(
							'installed'    => isset( $installed_plugins['social-wall/social-wall.php'] ) ? true : false,
							'activated'    => is_plugin_active( 'social-wall/social-wall.php' ),
							'settingsPage' => admin_url( 'admin.php?page=sbsw' ),
						)
					),
					'selectSourceScreen'   => self::select_source_screen_text(),
					'feedTypes'            => $this->get_feed_types(),
					'advancedFeedTypes'    => $this->get_advanced_feed_types(),
					'socialInfo'           => $this->get_smashballoon_info(),
					'svgIcons'             => $this->builder_svg_icons(),
					'installPluginsPopup'  => $this->install_plugins_popup(),
					'feeds'                => self::get_feed_list(),
					'itemsPerPage'         => SBI_Db::RESULTS_PER_PAGE,
					'feedsCount'           => SBI_Db::feeds_count(),
					'sources'              => self::get_source_list(),
					'sourceConnectionURLs' => SBI_Source::get_connection_urls(),

					'legacyFeeds'          => $this->get_legacy_feed_list(),
					'extensionsPopup'      => array(
						'hashtag'          => array(
							'heading'         => __( 'Upgrade to Pro to get Hashtag Feeds', 'instagram-feed' ),
							'description'     => __( 'Display posts from any public hashtag with an Instagram hashtag feed. Great for pulling in user-generated content associated with your brand, running promotional hashtag campaigns, engaging audiences at events, and more.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#cliphashtagpro0_506_22510)"><g clip-path="url(#cliphashtagpro1_506_22510)"><g filter="url(#filterhashtagpro0_ddd_506_22510)"><rect x="169.506" y="31.2762" width="158.634" height="180.446" rx="3.96584" transform="rotate(4 169.506 31.2762)" fill="white"/></g><g clip-path="url(#cliphashtagpro2_506_22510)"><rect x="169.506" y="31.2762" width="158.634" height="158.634" transform="rotate(4 169.506 31.2762)" fill="#0096CC"/></g><path d="M192.49 182.337L188.63 182.067L189.561 178.756C189.585 178.659 189.587 178.557 189.568 178.458C189.549 178.359 189.509 178.266 189.45 178.184C189.391 178.102 189.315 178.034 189.228 177.984C189.14 177.934 189.043 177.904 188.943 177.896L187.552 177.798C187.391 177.783 187.23 177.824 187.097 177.914C186.963 178.004 186.864 178.138 186.818 178.292L185.855 181.873L182.099 181.611L183.029 178.3C183.053 178.204 183.056 178.105 183.038 178.008C183.021 177.911 182.983 177.819 182.927 177.738C182.871 177.657 182.799 177.588 182.715 177.537C182.631 177.486 182.537 177.453 182.439 177.441L181.048 177.343C180.888 177.328 180.727 177.369 180.593 177.459C180.459 177.549 180.361 177.683 180.315 177.837L179.365 181.42L175.192 181.128C175.029 181.113 174.867 181.156 174.733 181.249C174.598 181.341 174.501 181.478 174.457 181.636L174.075 183.007C174.047 183.107 174.042 183.213 174.06 183.316C174.079 183.419 174.12 183.517 174.181 183.602C174.242 183.686 174.322 183.756 174.414 183.806C174.506 183.856 174.608 183.885 174.712 183.89L178.572 184.16L177.043 189.645L172.869 189.353C172.707 189.338 172.544 189.381 172.41 189.474C172.276 189.567 172.179 189.703 172.135 189.861L171.752 191.232C171.724 191.333 171.719 191.438 171.738 191.541C171.756 191.644 171.797 191.742 171.859 191.827C171.92 191.911 171.999 191.981 172.091 192.031C172.183 192.081 172.285 192.11 172.39 192.115L176.278 192.387L175.347 195.698C175.323 195.797 175.321 195.901 175.341 196.002C175.361 196.102 175.403 196.197 175.464 196.279C175.525 196.361 175.603 196.429 175.694 196.478C175.784 196.527 175.884 196.555 175.986 196.56L177.377 196.658C177.532 196.664 177.685 196.619 177.812 196.529C177.938 196.44 178.031 196.31 178.076 196.161L179.053 192.581L182.809 192.844L181.879 196.155C181.855 196.25 181.852 196.35 181.869 196.447C181.887 196.543 181.925 196.635 181.981 196.717C182.036 196.798 182.109 196.866 182.193 196.917C182.277 196.969 182.371 197.001 182.469 197.014L183.86 197.111C184.02 197.126 184.181 197.085 184.315 196.995C184.448 196.905 184.547 196.771 184.593 196.617L185.577 193.037L189.751 193.329C189.913 193.344 190.076 193.301 190.21 193.208C190.344 193.115 190.441 192.979 190.485 192.821L190.861 191.45C190.889 191.349 190.894 191.243 190.875 191.14C190.857 191.037 190.816 190.94 190.754 190.855C190.693 190.77 190.614 190.7 190.522 190.65C190.43 190.6 190.328 190.572 190.223 190.566L186.342 190.295L187.865 184.81L192.038 185.102C192.201 185.116 192.363 185.073 192.498 184.981C192.632 184.888 192.729 184.751 192.773 184.594L193.155 183.223C193.184 183.119 193.189 183.011 193.169 182.906C193.149 182.801 193.106 182.701 193.041 182.616C192.977 182.53 192.894 182.461 192.798 182.412C192.703 182.364 192.597 182.338 192.49 182.337ZM183.56 190.1L179.804 189.838L181.334 184.353L185.09 184.616L183.56 190.1Z" fill="#0068A0"/><rect x="206.604" y="182.953" width="57.5047" height="13.8804" rx="1.98292" transform="rotate(4 206.604 182.953)" fill="#86D0F9"/><g filter="url(#filterhashtagpro1_ddd_506_22510)"><rect x="169.506" y="31.2762" width="158.634" height="180.446" rx="3.96584" transform="rotate(4 169.506 31.2762)" fill="white"/></g><g clip-path="url(#cliphashtagpro3_506_22510)"><rect x="169.506" y="31.2762" width="158.634" height="158.634" transform="rotate(4 169.506 31.2762)" fill="#0096CC"/><circle cx="183.84" cy="95.0859" r="92.2057" fill="#0068A0"/></g><path d="M192.49 182.337L188.63 182.067L189.561 178.756C189.585 178.659 189.587 178.557 189.568 178.458C189.549 178.359 189.509 178.266 189.45 178.184C189.391 178.102 189.315 178.034 189.228 177.984C189.14 177.934 189.043 177.904 188.943 177.896L187.552 177.798C187.391 177.783 187.23 177.824 187.097 177.914C186.963 178.004 186.864 178.138 186.818 178.292L185.855 181.873L182.099 181.611L183.029 178.3C183.053 178.204 183.056 178.105 183.038 178.008C183.021 177.911 182.983 177.819 182.927 177.738C182.871 177.657 182.799 177.588 182.715 177.537C182.631 177.486 182.537 177.453 182.439 177.441L181.048 177.343C180.888 177.328 180.727 177.369 180.593 177.459C180.459 177.549 180.361 177.683 180.315 177.837L179.365 181.42L175.192 181.128C175.029 181.113 174.867 181.156 174.733 181.249C174.598 181.341 174.501 181.478 174.457 181.636L174.075 183.007C174.047 183.107 174.042 183.213 174.06 183.316C174.079 183.419 174.12 183.517 174.181 183.602C174.242 183.686 174.322 183.756 174.414 183.806C174.506 183.856 174.608 183.885 174.712 183.89L178.572 184.16L177.043 189.645L172.869 189.353C172.707 189.338 172.544 189.381 172.41 189.474C172.276 189.567 172.179 189.703 172.135 189.861L171.752 191.232C171.724 191.333 171.719 191.438 171.738 191.541C171.756 191.644 171.797 191.742 171.859 191.827C171.92 191.911 171.999 191.981 172.091 192.031C172.183 192.081 172.285 192.11 172.39 192.115L176.278 192.387L175.347 195.698C175.323 195.797 175.321 195.901 175.341 196.002C175.361 196.102 175.403 196.197 175.464 196.279C175.525 196.361 175.603 196.429 175.694 196.478C175.784 196.527 175.884 196.555 175.986 196.56L177.377 196.658C177.532 196.664 177.685 196.619 177.812 196.529C177.938 196.44 178.031 196.31 178.076 196.161L179.053 192.581L182.809 192.844L181.879 196.155C181.855 196.25 181.852 196.35 181.869 196.447C181.887 196.543 181.925 196.635 181.981 196.717C182.036 196.798 182.109 196.866 182.193 196.917C182.277 196.969 182.371 197.001 182.469 197.014L183.86 197.111C184.02 197.126 184.181 197.085 184.315 196.995C184.448 196.905 184.547 196.771 184.593 196.617L185.577 193.037L189.751 193.329C189.913 193.344 190.076 193.301 190.21 193.208C190.344 193.115 190.441 192.979 190.485 192.821L190.861 191.45C190.889 191.349 190.894 191.243 190.875 191.14C190.857 191.037 190.816 190.94 190.754 190.855C190.693 190.77 190.614 190.7 190.522 190.65C190.43 190.6 190.328 190.572 190.223 190.566L186.342 190.295L187.865 184.81L192.038 185.102C192.201 185.116 192.363 185.073 192.498 184.981C192.632 184.888 192.729 184.751 192.773 184.594L193.155 183.223C193.184 183.119 193.189 183.011 193.169 182.906C193.149 182.801 193.106 182.701 193.041 182.616C192.977 182.53 192.894 182.461 192.798 182.412C192.703 182.364 192.597 182.338 192.49 182.337ZM183.56 190.1L179.804 189.838L181.334 184.353L185.09 184.616L183.56 190.1Z" fill="#0068A0"/><rect x="206.604" y="182.953" width="57.5047" height="13.8804" rx="1.98292" transform="rotate(4 206.604 182.953)" fill="#86D0F9"/><g filter="url(#filterhashtagpro2_ddd_506_22510)"><rect x="61.8896" y="48.4873" width="158.634" height="180.446" rx="3.96584" fill="white"/></g><g clip-path="url(#cliphashtagpro4_506_22510)"><rect x="61.8896" y="48.4873" width="158.634" height="158.634" fill="#F6966B"/><path d="M93.1149 104.67L218.7 230.255H-10.8174L93.1149 104.67Z" fill="#F9BBA0"/></g><path d="M95.3546 197.577H91.4848L92.1821 194.209C92.1992 194.11 92.1946 194.008 92.1687 193.911C92.1427 193.814 92.096 193.723 92.0317 193.646C91.9675 193.568 91.8872 193.506 91.7964 193.462C91.7056 193.419 91.6064 193.395 91.5057 193.394H90.1112C89.95 193.39 89.7925 193.442 89.6653 193.541C89.5382 193.64 89.4494 193.78 89.414 193.937L88.7028 197.577H84.9376L85.6348 194.209C85.6517 194.112 85.6479 194.013 85.6236 193.918C85.5992 193.822 85.555 193.733 85.4937 193.656C85.4325 193.579 85.3556 193.516 85.2682 193.47C85.1807 193.425 85.0847 193.399 84.9864 193.394H83.5919C83.4307 193.39 83.2731 193.442 83.146 193.541C83.0189 193.64 82.93 193.78 82.8946 193.937L82.1974 197.577H78.0138C77.8507 197.574 77.6916 197.628 77.5642 197.73C77.4368 197.832 77.3492 197.975 77.3166 198.135L77.0307 199.529C77.0098 199.632 77.0121 199.738 77.0377 199.839C77.0632 199.941 77.1112 200.035 77.1782 200.115C77.2451 200.196 77.3293 200.26 77.4245 200.303C77.5197 200.347 77.6234 200.368 77.7279 200.366H81.5977L80.4542 205.944H76.2707C76.1076 205.941 75.9485 205.995 75.8211 206.097C75.6937 206.199 75.606 206.342 75.5734 206.502L75.2876 207.897C75.2666 207.999 75.269 208.105 75.2945 208.206C75.3201 208.308 75.3681 208.402 75.435 208.483C75.502 208.563 75.5862 208.627 75.6814 208.671C75.7765 208.714 75.8802 208.735 75.9848 208.733H79.8825L79.1852 212.101C79.1678 212.202 79.1728 212.306 79.2 212.404C79.2271 212.503 79.2757 212.595 79.3423 212.673C79.4088 212.751 79.4918 212.813 79.5851 212.855C79.6785 212.897 79.78 212.918 79.8825 212.917H81.277C81.4322 212.913 81.5816 212.857 81.7016 212.759C81.8216 212.66 81.9053 212.524 81.9394 212.373L82.6645 208.733H86.4297L85.7324 212.101C85.7156 212.198 85.7194 212.297 85.7437 212.393C85.768 212.488 85.8123 212.577 85.8735 212.654C85.9348 212.732 86.0117 212.795 86.0991 212.84C86.1866 212.885 86.2826 212.911 86.3809 212.917H87.7754C87.9366 212.921 88.0941 212.869 88.2213 212.77C88.3484 212.67 88.4372 212.53 88.4727 212.373L89.2048 208.733H93.3883C93.5514 208.737 93.7105 208.683 93.8379 208.581C93.9653 208.479 94.053 208.335 94.0856 208.175L94.3645 206.781C94.3854 206.678 94.383 206.573 94.3575 206.471C94.332 206.37 94.2839 206.275 94.217 206.195C94.15 206.115 94.0658 206.05 93.9707 206.007C93.8755 205.964 93.7718 205.942 93.6672 205.944H89.7765L90.9131 200.366H95.0966C95.2597 200.369 95.4188 200.315 95.5462 200.214C95.6736 200.112 95.7612 199.968 95.7938 199.808L96.0797 198.414C96.1013 198.309 96.0985 198.201 96.0714 198.097C96.0444 197.993 95.9939 197.897 95.9237 197.816C95.8536 197.736 95.7658 197.672 95.6671 197.63C95.5684 197.589 95.4615 197.571 95.3546 197.577ZM86.9875 205.944H83.2223L84.3658 200.366H88.131L86.9875 205.944Z" fill="#FE544F"/><rect x="109.479" y="197.206" width="57.5047" height="13.8804" rx="1.98292" fill="#FCE1D5"/><g filter="url(#filterhashtagpro3_ddd_506_22510)"><rect x="61.8896" y="48.4873" width="158.634" height="180.446" rx="3.96584" fill="white"/></g><g clip-path="url(#cliphashtagpro5_506_22510)"><rect x="61.8896" y="48.4873" width="158.634" height="158.634" fill="#F6966B"/><path d="M93.1149 104.67L218.7 230.255H-10.8174L93.1149 104.67Z" fill="#F9BBA0"/></g><path d="M95.3546 197.577H91.4848L92.1821 194.209C92.1992 194.11 92.1946 194.008 92.1687 193.911C92.1427 193.814 92.096 193.723 92.0317 193.646C91.9675 193.568 91.8872 193.506 91.7964 193.462C91.7056 193.419 91.6064 193.395 91.5057 193.394H90.1112C89.95 193.39 89.7925 193.442 89.6653 193.541C89.5382 193.64 89.4494 193.78 89.414 193.937L88.7028 197.577H84.9376L85.6348 194.209C85.6517 194.112 85.6479 194.013 85.6236 193.918C85.5992 193.822 85.555 193.733 85.4937 193.656C85.4325 193.579 85.3556 193.516 85.2682 193.47C85.1807 193.425 85.0847 193.399 84.9864 193.394H83.5919C83.4307 193.39 83.2731 193.442 83.146 193.541C83.0189 193.64 82.93 193.78 82.8946 193.937L82.1974 197.577H78.0138C77.8507 197.574 77.6916 197.628 77.5642 197.73C77.4368 197.832 77.3492 197.975 77.3166 198.135L77.0307 199.529C77.0098 199.632 77.0121 199.738 77.0377 199.839C77.0632 199.941 77.1112 200.035 77.1782 200.115C77.2451 200.196 77.3293 200.26 77.4245 200.303C77.5197 200.347 77.6234 200.368 77.7279 200.366H81.5977L80.4542 205.944H76.2707C76.1076 205.941 75.9485 205.995 75.8211 206.097C75.6937 206.199 75.606 206.342 75.5734 206.502L75.2876 207.897C75.2666 207.999 75.269 208.105 75.2945 208.206C75.3201 208.308 75.3681 208.402 75.435 208.483C75.502 208.563 75.5862 208.627 75.6814 208.671C75.7765 208.714 75.8802 208.735 75.9848 208.733H79.8825L79.1852 212.101C79.1678 212.202 79.1728 212.306 79.2 212.404C79.2271 212.503 79.2757 212.595 79.3423 212.673C79.4088 212.751 79.4918 212.813 79.5851 212.855C79.6785 212.897 79.78 212.918 79.8825 212.917H81.277C81.4322 212.913 81.5816 212.857 81.7016 212.759C81.8216 212.66 81.9053 212.524 81.9394 212.373L82.6645 208.733H86.4297L85.7324 212.101C85.7156 212.198 85.7194 212.297 85.7437 212.393C85.768 212.488 85.8123 212.577 85.8735 212.654C85.9348 212.732 86.0117 212.795 86.0991 212.84C86.1866 212.885 86.2826 212.911 86.3809 212.917H87.7754C87.9366 212.921 88.0941 212.869 88.2213 212.77C88.3484 212.67 88.4372 212.53 88.4727 212.373L89.2048 208.733H93.3883C93.5514 208.737 93.7105 208.683 93.8379 208.581C93.9653 208.479 94.053 208.335 94.0856 208.175L94.3645 206.781C94.3854 206.678 94.383 206.573 94.3575 206.471C94.332 206.37 94.2839 206.275 94.217 206.195C94.15 206.115 94.0658 206.05 93.9707 206.007C93.8755 205.964 93.7718 205.942 93.6672 205.944H89.7765L90.9131 200.366H95.0966C95.2597 200.369 95.4188 200.315 95.5462 200.214C95.6736 200.112 95.7612 199.968 95.7938 199.808L96.0797 198.414C96.1013 198.309 96.0985 198.201 96.0714 198.097C96.0444 197.993 95.9939 197.897 95.9237 197.816C95.8536 197.736 95.7658 197.672 95.6671 197.63C95.5684 197.589 95.4615 197.571 95.3546 197.577ZM86.9875 205.944H83.2223L84.3658 200.366H88.131L86.9875 205.944Z" fill="#FE544F"/><rect x="109.479" y="197.206" width="57.5047" height="13.8804" rx="1.98292" fill="#FCE1D5"/></g></g><defs><filter id="filterhashtagpro0_ddd_506_22510" x="131.408" y="17.6626" width="221.857" height="242.094" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="11.8975"/><feGaussianBlur stdDeviation="12.889"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.98292"/><feGaussianBlur stdDeviation="1.98292"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_506_22510" result="effect2_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="5.94876"/><feGaussianBlur stdDeviation="5.94876"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_506_22510" result="effect3_dropShadow_506_22510"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_506_22510" result="shape"/></filter><filter id="filterhashtagpro1_ddd_506_22510" x="131.408" y="17.6626" width="221.857" height="242.094" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="11.8975"/><feGaussianBlur stdDeviation="12.889"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.98292"/><feGaussianBlur stdDeviation="1.98292"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_506_22510" result="effect2_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="5.94876"/><feGaussianBlur stdDeviation="5.94876"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_506_22510" result="effect3_dropShadow_506_22510"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_506_22510" result="shape"/></filter><filter id="filterhashtagpro2_ddd_506_22510" x="36.1117" y="34.6069" width="210.19" height="232.002" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="11.8975"/><feGaussianBlur stdDeviation="12.889"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.98292"/><feGaussianBlur stdDeviation="1.98292"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_506_22510" result="effect2_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="5.94876"/><feGaussianBlur stdDeviation="5.94876"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_506_22510" result="effect3_dropShadow_506_22510"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_506_22510" result="shape"/></filter><filter id="filterhashtagpro3_ddd_506_22510" x="36.1117" y="34.6069" width="210.19" height="232.002" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="11.8975"/><feGaussianBlur stdDeviation="12.889"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.98292"/><feGaussianBlur stdDeviation="1.98292"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_506_22510" result="effect2_dropShadow_506_22510"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="5.94876"/><feGaussianBlur stdDeviation="5.94876"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_506_22510" result="effect3_dropShadow_506_22510"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_506_22510" result="shape"/></filter><clipPath id="cliphashtagpro0_506_22510"><rect width="396" height="264" fill="white"/></clipPath><clipPath id="cliphashtagpro1_506_22510"><rect width="530" height="250.308" fill="white" transform="translate(-67 6.84601)"/></clipPath><clipPath id="cliphashtagpro2_506_22510"><path d="M169.275 34.573C169.403 32.7522 170.982 31.3794 172.803 31.5067L324.456 42.1114C326.277 42.2387 327.65 43.818 327.522 45.6387L318.624 172.896L160.377 161.83L169.275 34.573Z" fill="white"/></clipPath><clipPath id="cliphashtagpro3_506_22510"><path d="M169.275 34.573C169.403 32.7522 170.982 31.3794 172.803 31.5067L324.456 42.1114C326.277 42.2387 327.65 43.818 327.522 45.6387L318.624 172.896L160.377 161.83L169.275 34.573Z" fill="white"/></clipPath><clipPath id="cliphashtagpro4_506_22510"><path d="M61.8896 51.7922C61.8896 49.9669 63.3693 48.4873 65.1945 48.4873H217.218C219.044 48.4873 220.523 49.9669 220.523 51.7922V179.36H61.8896V51.7922Z" fill="white"/></clipPath><clipPath id="cliphashtagpro5_506_22510"><path d="M61.8896 51.7922C61.8896 49.9669 63.3693 48.4873 65.1945 48.4873H217.218C219.044 48.4873 220.523 49.9669 220.523 51.7922V179.36H61.8896V51.7922Z" fill="white"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/hashtag?utm_campaign=instagram-free&utm_source=feed-type&utm_medium=hashtag' )
						),
						'tagged'           => array(
							'heading'         => __( 'Upgrade to Pro to get Tagged Posts Feed', 'instagram-feed' ),
							'description'     => __( 'Display posts that you\'ve been tagged in by other users allowing you to increase your audience\'s engagement with your Instagram account.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#cliptagged0_506_22672)"><g clip-path="url(#cliptagged1_506_22672)"><g filter="url(#filtertagged0_ddd_506_22672)"><g clip-path="url(#cliptagged2_506_22672)"><rect x="139.63" y="64.4107" width="164.72" height="166.806" rx="4.17014" transform="rotate(2 139.63 64.4107)" fill="white"/><g clip-path="url(#cliptagged3_506_22672)"><path d="M139.131 52.643L305.835 58.4644L300.45 212.666L133.746 206.844L139.131 52.643Z" fill="#59AB46"/><path d="M170.246 102.41L297.611 238.993L56.4177 230.57L170.246 102.41Z" fill="#76C064"/></g><path fill-rule="evenodd" clip-rule="evenodd" d="M157.064 194.828C159.201 194.48 161.393 194.773 163.363 195.671C165.333 196.569 166.993 198.031 168.132 199.872C169.271 201.713 169.839 203.85 169.763 206.014L169.707 207.635C169.671 208.674 169.223 209.656 168.463 210.364C167.703 211.073 166.692 211.451 165.653 211.415C164.615 211.379 163.633 210.931 162.924 210.171C162.792 210.029 162.671 209.879 162.563 209.721C161.492 210.7 160.081 211.22 158.63 211.17C157.162 211.118 155.773 210.486 154.771 209.411C153.769 208.336 153.235 206.907 153.286 205.439C153.337 203.97 153.97 202.582 155.045 201.58C156.119 200.578 157.548 200.043 159.017 200.095C160.486 200.146 161.874 200.779 162.876 201.853C163.878 202.928 164.412 204.357 164.361 205.826L164.304 207.446C164.291 207.84 164.434 208.224 164.703 208.512C164.972 208.8 165.344 208.97 165.738 208.984C166.132 208.998 166.516 208.854 166.804 208.585C167.092 208.316 167.262 207.944 167.276 207.55L167.332 205.929C167.391 204.246 166.95 202.584 166.063 201.152C165.177 199.72 163.887 198.583 162.354 197.885C160.822 197.186 159.117 196.958 157.455 197.229C155.793 197.499 154.249 198.257 153.017 199.405C151.785 200.554 150.922 202.042 150.536 203.681C150.15 205.32 150.259 207.037 150.849 208.614C151.438 210.191 152.483 211.558 153.849 212.542C155.216 213.526 156.844 214.082 158.526 214.141L158.528 214.141C160.021 214.195 161.502 213.853 162.819 213.15C163.412 212.834 164.148 213.058 164.465 213.651C164.781 214.243 164.557 214.98 163.964 215.296C162.267 216.202 160.362 216.641 158.44 216.572L158.484 215.357L158.442 216.572C158.441 216.572 158.441 216.572 158.44 216.572C156.277 216.496 154.185 215.781 152.428 214.516C150.671 213.251 149.328 211.494 148.57 209.466C147.812 207.438 147.672 205.231 148.168 203.123C148.665 201.016 149.775 199.103 151.358 197.626C152.941 196.15 154.927 195.176 157.064 194.828ZM161.93 205.741C161.959 204.917 161.659 204.115 161.097 203.512C160.535 202.909 159.756 202.555 158.932 202.526C158.108 202.497 157.307 202.797 156.704 203.359C156.101 203.921 155.746 204.7 155.717 205.524C155.688 206.348 155.988 207.149 156.55 207.752C157.113 208.355 157.891 208.71 158.715 208.739C159.539 208.767 160.34 208.468 160.943 207.905C161.546 207.343 161.901 206.564 161.93 205.741Z" fill="#59AB46"/><rect x="184.048" y="200.256" width="60.467" height="14.5955" rx="2.08507" transform="rotate(2 184.048 200.256)" fill="#B6DDAD"/></g></g><g filter="url(#filtertagged1_ddd_506_22672)"><g clip-path="url(#cliptagged4_506_22672)"><rect x="73.3418" y="28.4567" width="164.72" height="166.806" rx="4.17014" transform="rotate(-2 73.3418 28.4567)" fill="white"/><g clip-path="url(#cliptagged5_506_22672)"><path d="M72.0225 16.7529L238.726 10.9315L244.111 165.133L77.4073 170.954L72.0225 16.7529Z" fill="#43A6DB"/><circle cx="268.485" cy="149.951" r="96.9557" transform="rotate(-2 268.485 149.951)" fill="#0068A0"/></g><path fill-rule="evenodd" clip-rule="evenodd" d="M99.8296 157.341C101.937 156.845 104.144 156.985 106.172 157.743C108.2 158.501 109.958 159.844 111.223 161.601C112.487 163.358 113.203 165.451 113.279 167.615L113.335 169.235C113.371 170.274 112.994 171.285 112.285 172.045C111.576 172.805 110.594 173.253 109.555 173.289C108.516 173.325 107.506 172.947 106.745 172.238C106.604 172.106 106.473 171.965 106.354 171.815C105.354 172.866 103.983 173.483 102.532 173.534C101.063 173.585 99.6345 173.051 98.5597 172.049C97.485 171.047 96.8524 169.659 96.8011 168.19C96.7498 166.721 97.284 165.292 98.2862 164.218C99.2885 163.143 100.677 162.51 102.145 162.459C103.614 162.408 105.043 162.942 106.117 163.944C107.192 164.946 107.825 166.335 107.876 167.803L107.933 169.424C107.946 169.818 108.116 170.19 108.405 170.459C108.693 170.728 109.076 170.871 109.47 170.858C109.864 170.844 110.237 170.674 110.506 170.386C110.774 170.098 110.918 169.714 110.904 169.32L110.847 167.699C110.789 166.017 110.232 164.389 109.248 163.022C108.265 161.656 106.898 160.611 105.32 160.022C103.743 159.432 102.026 159.323 100.387 159.709C98.7481 160.095 97.2602 160.958 96.1118 162.19C94.9634 163.422 94.2059 164.966 93.9353 166.628C93.6646 168.29 93.8928 169.995 94.5911 171.527C95.2895 173.06 96.4265 174.35 97.8584 175.236C99.2903 176.123 100.953 176.564 102.636 176.505L102.637 176.505C104.13 176.455 105.584 176.011 106.849 175.218C107.418 174.861 108.169 175.033 108.525 175.602C108.882 176.171 108.71 176.922 108.141 177.279C106.511 178.3 104.641 178.871 102.719 178.937L102.678 177.721L102.721 178.937C102.72 178.937 102.72 178.937 102.719 178.937C100.556 179.012 98.419 178.444 96.5783 177.305C94.7373 176.166 93.2754 174.506 92.3776 172.536C91.4798 170.566 91.1863 168.374 91.5343 166.237C91.8823 164.1 92.8562 162.114 94.3327 160.531C95.8093 158.948 97.7222 157.838 99.8296 157.341ZM105.445 167.888C105.416 167.064 105.061 166.286 104.458 165.723C103.856 165.161 103.054 164.861 102.23 164.89C101.406 164.919 100.628 165.274 100.065 165.877C99.5031 166.48 99.2034 167.281 99.2322 168.105C99.261 168.929 99.6158 169.708 100.219 170.27C100.822 170.832 101.623 171.132 102.447 171.103C103.271 171.074 104.05 170.719 104.612 170.116C105.174 169.514 105.474 168.712 105.445 167.888Z" fill="#0068A0"/><rect x="127.129" y="160.874" width="60.467" height="14.5955" rx="2.08507" transform="rotate(-2 127.129 160.874)" fill="#86D0F9"/></g></g></g></g><defs><filter id="filtertagged0_ddd_506_22672" x="106.703" y="49.8152" width="224.653" height="226.664" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="12.5104"/><feGaussianBlur stdDeviation="13.5529"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22672"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="2.08507"/><feGaussianBlur stdDeviation="2.08507"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_506_22672" result="effect2_dropShadow_506_22672"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="6.25521"/><feGaussianBlur stdDeviation="6.25521"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_506_22672" result="effect3_dropShadow_506_22672"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_506_22672" result="shape"/></filter><filter id="filtertagged1_ddd_506_22672" x="46.2359" y="8.11258" width="224.653" height="226.664" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="12.5104"/><feGaussianBlur stdDeviation="13.5529"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22672"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="2.08507"/><feGaussianBlur stdDeviation="2.08507"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_506_22672" result="effect2_dropShadow_506_22672"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="6.25521"/><feGaussianBlur stdDeviation="6.25521"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_506_22672" result="effect3_dropShadow_506_22672"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_506_22672" result="shape"/></filter><clipPath id="cliptagged0_506_22672"><rect width="396" height="264" fill="white"/></clipPath><clipPath id="cliptagged1_506_22672"><rect width="530" height="250.308" fill="white" transform="translate(-67 6.84601)"/></clipPath><clipPath id="cliptagged2_506_22672"><rect x="139.63" y="64.4107" width="164.72" height="166.806" rx="4.17014" transform="rotate(2 139.63 64.4107)" fill="white"/></clipPath><clipPath id="cliptagged3_506_22672"><path d="M139.373 45.6973C139.44 43.7792 141.049 42.2786 142.967 42.3456L302.725 47.9245C304.643 47.9914 306.144 49.6006 306.077 51.5187L301.395 185.576L134.691 179.755L139.373 45.6973Z" fill="white"/></clipPath><clipPath id="cliptagged4_506_22672"><rect x="73.3418" y="28.4567" width="164.72" height="166.806" rx="4.17014" transform="rotate(-2 73.3418 28.4567)" fill="white"/></clipPath><clipPath id="cliptagged5_506_22672"><path d="M71.7785 9.80722C71.7115 7.88913 73.2121 6.27993 75.1302 6.21295L234.888 0.634078C236.806 0.567097 238.415 2.06771 238.482 3.9858L243.164 138.044L76.4599 143.865L71.7785 9.80722Z" fill="white"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=feed-type&utm_medium=tagged' )
						),
						'socialwall'       => array(
							// Combine all your social media channels into one Social Wall
							'heading'         => __( '<span class="sb-social-wall">Combine all your social media channels into one', 'instagram-feed' ) . ' <span>' . __( 'Social Wall', 'instagram-feed' ) . '</span></span>',
							'description'     => __( '<span class="sb-social-wall">A dash of Instagram, a sprinkle of Facebook, a spoonful of Twitter, and a dollop of YouTube, all in the same feed.</span>', 'instagram-feed' ),
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'img'             => '<svg width="397" height="264" viewBox="0 0 397 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><g filter="url(#filter0_ddd)"><rect x="18.957" y="63" width="113.812" height="129.461" rx="2.8453" fill="white"/></g><g clip-path="url(#clip1)"><path d="M18.957 63H132.769V176.812H18.957V63Z" fill="#0068A0"/><rect x="56.957" y="106" width="105" height="105" rx="9" fill="#005B8C"/></g><path d="M36.0293 165.701C31.4649 165.701 27.7305 169.427 27.7305 174.017C27.7305 178.166 30.7678 181.61 34.7347 182.232V176.423H32.6268V174.017H34.7347V172.183C34.7347 170.1 35.9712 168.954 37.8716 168.954C38.7762 168.954 39.7222 169.112 39.7222 169.112V171.162H38.6766C37.6475 171.162 37.3239 171.801 37.3239 172.456V174.017H39.6309L39.2575 176.423H37.3239V182.232C39.2794 181.924 41.0602 180.926 42.3446 179.419C43.629 177.913 44.3325 175.996 44.3281 174.017C44.3281 169.427 40.5936 165.701 36.0293 165.701Z" fill="#006BFA"/><rect x="53.1016" y="169.699" width="41.2569" height="9.95855" rx="1.42265" fill="#D0D1D7"/><g filter="url(#filter1_ddd)"><rect x="18.957" y="201" width="113.812" height="129.461" rx="2.8453" fill="white"/></g><g clip-path="url(#clip2)"><path d="M18.957 201H132.769V314.812H18.957V201Z" fill="#EC352F"/><circle cx="23.957" cy="243" r="59" fill="#FE544F"/></g><g filter="url(#filter2_ddd)"><rect x="139.957" y="23" width="113.812" height="129.461" rx="2.8453" fill="white"/></g><g clip-path="url(#clip3)"><path d="M139.957 23H253.769V136.812H139.957V23Z" fill="#8C8F9A"/><circle cx="127.457" cy="142.5" r="78.5" fill="#D0D1D7"/></g><path d="M157.026 129.493C154.537 129.493 152.553 131.516 152.553 133.967C152.553 136.456 154.537 138.44 157.026 138.44C159.477 138.44 161.5 136.456 161.5 133.967C161.5 131.516 159.477 129.493 157.026 129.493ZM157.026 136.884C155.431 136.884 154.109 135.601 154.109 133.967C154.109 132.372 155.392 131.088 157.026 131.088C158.621 131.088 159.905 132.372 159.905 133.967C159.905 135.601 158.621 136.884 157.026 136.884ZM162.706 129.338C162.706 128.754 162.239 128.287 161.655 128.287C161.072 128.287 160.605 128.754 160.605 129.338C160.605 129.921 161.072 130.388 161.655 130.388C162.239 130.388 162.706 129.921 162.706 129.338ZM165.662 130.388C165.584 128.987 165.273 127.743 164.262 126.731C163.25 125.72 162.005 125.409 160.605 125.331C159.166 125.253 154.848 125.253 153.408 125.331C152.008 125.409 150.802 125.72 149.752 126.731C148.74 127.743 148.429 128.987 148.351 130.388C148.274 131.827 148.274 136.145 148.351 137.585C148.429 138.985 148.74 140.191 149.752 141.241C150.802 142.253 152.008 142.564 153.408 142.642C154.848 142.719 159.166 142.719 160.605 142.642C162.005 142.564 163.25 142.253 164.262 141.241C165.273 140.191 165.584 138.985 165.662 137.585C165.74 136.145 165.74 131.827 165.662 130.388ZM163.795 139.102C163.523 139.88 162.9 140.463 162.161 140.774C160.994 141.241 158.271 141.124 157.026 141.124C155.742 141.124 153.019 141.241 151.891 140.774C151.113 140.463 150.53 139.88 150.219 139.102C149.752 137.974 149.868 135.25 149.868 133.967C149.868 132.722 149.752 129.999 150.219 128.832C150.53 128.093 151.113 127.509 151.891 127.198C153.019 126.731 155.742 126.848 157.026 126.848C158.271 126.848 160.994 126.731 162.161 127.198C162.9 127.47 163.484 128.093 163.795 128.832C164.262 129.999 164.145 132.722 164.145 133.967C164.145 135.25 164.262 137.974 163.795 139.102Z" fill="url(#paint0_linear)"/><rect x="174.102" y="129.699" width="41.2569" height="9.95855" rx="1.42265" fill="#D0D1D7"/><g filter="url(#filter3_ddd)"><rect x="139.957" y="161" width="114" height="109" rx="2.8453" fill="white"/></g><rect x="148.957" y="194" width="91" height="8" rx="1.42265" fill="#D0D1D7"/><rect x="148.957" y="208" width="51" height="8" rx="1.42265" fill="#D0D1D7"/><path d="M164.366 172.062C163.788 172.324 163.166 172.497 162.521 172.579C163.181 172.182 163.691 171.552 163.931 170.794C163.308 171.169 162.618 171.432 161.891 171.582C161.298 170.937 160.466 170.562 159.521 170.562C157.758 170.562 156.318 172.002 156.318 173.779C156.318 174.034 156.348 174.282 156.401 174.514C153.731 174.379 151.353 173.097 149.771 171.154C149.493 171.627 149.336 172.182 149.336 172.767C149.336 173.884 149.898 174.874 150.768 175.437C150.236 175.437 149.741 175.287 149.306 175.062V175.084C149.306 176.644 150.416 177.949 151.886 178.242C151.414 178.371 150.918 178.389 150.438 178.294C150.642 178.934 151.041 179.493 151.579 179.894C152.117 180.295 152.767 180.517 153.438 180.529C152.301 181.43 150.891 181.916 149.441 181.909C149.186 181.909 148.931 181.894 148.676 181.864C150.101 182.779 151.796 183.312 153.611 183.312C159.521 183.312 162.768 178.407 162.768 174.154C162.768 174.012 162.768 173.877 162.761 173.734C163.391 173.284 163.931 172.714 164.366 172.062Z" fill="#1B90EF"/><g filter="url(#filter4_ddd)"><rect x="260.957" y="63" width="113.812" height="129.461" rx="2.8453" fill="white"/></g><g clip-path="url(#clip4)"><rect x="260.957" y="63" width="113.812" height="113.812" fill="#D72C2C"/><path d="M283.359 103.308L373.461 193.41H208.793L283.359 103.308Z" fill="#DF5757"/></g><path d="M276.37 176.456L280.677 173.967L276.37 171.477V176.456ZM285.963 169.958C286.071 170.348 286.145 170.871 286.195 171.535C286.253 172.199 286.278 172.772 286.278 173.27L286.328 173.967C286.328 175.784 286.195 177.12 285.963 177.975C285.755 178.722 285.274 179.203 284.527 179.411C284.137 179.519 283.423 179.593 282.328 179.643C281.249 179.701 280.262 179.726 279.349 179.726L278.029 179.776C274.552 179.776 272.386 179.643 271.531 179.411C270.784 179.203 270.303 178.722 270.096 177.975C269.988 177.585 269.913 177.062 269.863 176.398C269.805 175.734 269.78 175.162 269.78 174.664L269.73 173.967C269.73 172.149 269.863 170.813 270.096 169.958C270.303 169.212 270.784 168.73 271.531 168.523C271.921 168.415 272.635 168.34 273.73 168.29C274.809 168.232 275.797 168.207 276.71 168.207L278.029 168.158C281.506 168.158 283.672 168.29 284.527 168.523C285.274 168.73 285.755 169.212 285.963 169.958Z" fill="#EB2121"/><rect x="295.102" y="169.699" width="41.2569" height="9.95855" rx="1.42265" fill="#D0D1D7"/><g filter="url(#filter5_ddd)"><rect x="260.957" y="201" width="113.812" height="129.461" rx="2.8453" fill="white"/></g><g clip-path="url(#clip5)"><rect x="260.957" y="201" width="113.812" height="113.812" fill="#59AB46"/><circle cx="374.457" cy="235.5" r="44.5" fill="#468737"/></g><g clip-path="url(#clip6)"><path d="M139.957 228H253.957V296C253.957 296.552 253.509 297 252.957 297H140.957C140.405 297 139.957 296.552 139.957 296V228Z" fill="#0068A0"/><circle cx="227.957" cy="245" r="34" fill="#004D77"/></g></g><defs><filter id="filter0_ddd" x="0.462572" y="53.0414" width="150.801" height="166.45" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="8.5359"/><feGaussianBlur stdDeviation="9.24723"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.42265"/><feGaussianBlur stdDeviation="1.42265"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.26795"/><feGaussianBlur stdDeviation="4.26795"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/></filter><filter id="filter1_ddd" x="0.462572" y="191.041" width="150.801" height="166.45" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="8.5359"/><feGaussianBlur stdDeviation="9.24723"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.42265"/><feGaussianBlur stdDeviation="1.42265"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.26795"/><feGaussianBlur stdDeviation="4.26795"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/></filter><filter id="filter2_ddd" x="121.463" y="13.0414" width="150.801" height="166.45" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="8.5359"/><feGaussianBlur stdDeviation="9.24723"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.42265"/><feGaussianBlur stdDeviation="1.42265"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.26795"/><feGaussianBlur stdDeviation="4.26795"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/></filter><filter id="filter3_ddd" x="121.463" y="151.041" width="150.989" height="145.989" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="8.5359"/><feGaussianBlur stdDeviation="9.24723"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.42265"/><feGaussianBlur stdDeviation="1.42265"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.26795"/><feGaussianBlur stdDeviation="4.26795"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/></filter><filter id="filter4_ddd" x="242.463" y="53.0414" width="150.801" height="166.45" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="8.5359"/><feGaussianBlur stdDeviation="9.24723"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.42265"/><feGaussianBlur stdDeviation="1.42265"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.26795"/><feGaussianBlur stdDeviation="4.26795"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/></filter><filter id="filter5_ddd" x="242.463" y="191.041" width="150.801" height="166.45" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="8.5359"/><feGaussianBlur stdDeviation="9.24723"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.42265"/><feGaussianBlur stdDeviation="1.42265"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.26795"/><feGaussianBlur stdDeviation="4.26795"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/></filter><linearGradient id="paint0_linear" x1="154.502" y1="158.603" x2="191.208" y2="121.133" gradientUnits="userSpaceOnUse"><stop stop-color="white"/><stop offset="0.147864" stop-color="#F6640E"/><stop offset="0.443974" stop-color="#BA03A7"/><stop offset="0.733337" stop-color="#6A01B9"/><stop offset="1" stop-color="#6B01B9"/></linearGradient><clipPath id="clip0"><rect width="396" height="264" fill="white" transform="translate(0.957031)"/></clipPath><clipPath id="clip1"><path d="M18.957 65.3711C18.957 64.0616 20.0186 63 21.3281 63H130.398C131.708 63 132.769 64.0616 132.769 65.3711V156.895H18.957V65.3711Z" fill="white"/></clipPath><clipPath id="clip2"><path d="M18.957 203.371C18.957 202.062 20.0186 201 21.3281 201H130.398C131.708 201 132.769 202.062 132.769 203.371V294.895H18.957V203.371Z" fill="white"/></clipPath><clipPath id="clip3"><path d="M139.957 25.3711C139.957 24.0616 141.019 23 142.328 23H251.398C252.708 23 253.769 24.0616 253.769 25.3711V116.895H139.957V25.3711Z" fill="white"/></clipPath><clipPath id="clip4"><path d="M260.957 65.3711C260.957 64.0616 262.019 63 263.328 63H372.398C373.708 63 374.769 64.0616 374.769 65.3711V156.895H260.957V65.3711Z" fill="white"/></clipPath><clipPath id="clip5"><path d="M260.957 203.371C260.957 202.062 262.019 201 263.328 201H372.398C373.708 201 374.769 202.062 374.769 203.371V294.895H260.957V203.371Z" fill="white"/></clipPath><clipPath id="clip6"><path d="M139.957 228H253.957V296C253.957 296.552 253.509 297 252.957 297H140.957C140.405 297 139.957 296.552 139.957 296V228Z" fill="white"/></clipPath></defs></svg>',
							'demoUrl'         => 'https://smashballoon.com/social-wall/demo/?utm_campaign=instagram-free&utm_source=feed-type&utm_medium=social-wall&utm_content=learn-more',
							'buyUrl'          => sprintf( 'https://smashballoon.com/social-wall/demo/?license_key=%s&upgrade=true&utm_campaign=instagram-free&utm_source=feed-type&utm_medium=social-wall&utm_content=Try Demo', $license_key ),
							'bullets'         => array(
								'heading' => __( 'Upgrade to the All Access Bundle and get:', 'instagram-feed' ),
								'content' => array(
									__( 'Instagram Feed Pro', 'instagram-feed' ),
									__( 'Custom Twitter Feeds Pro', 'instagram-feed' ),
									__( 'YouTube Feeds Pro', 'instagram-feed' ),
									__( 'Custom Facebook Feed Pro', 'instagram-feed' ),
									__( 'All Pro Facebook Extensions', 'instagram-feed' ),
									__( 'Social Wall Pro', 'instagram-feed' ),
								)
							),
						),

						// Other Types
						'feedLayout'       => array(
							'heading'         => __( 'Upgrade to Pro to get Feed Layouts', 'instagram-feed' ),
							'description'     => __( 'Choose from one of our built-in layout options; grid, carousel, masonry, and highlight to allow you to showcase your content in any way you want.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#feedLayoutSettingsfilter0_d_541_17506)"><rect x="45" y="67.0205" width="166.67" height="154.67" rx="2.66672" transform="rotate(-3 45 67.0205)" fill="white"/><g clip-path="url(#clip0feedLayoutSettings_541_17506)"><rect width="97.3351" height="82.6682" transform="translate(54.8096 75.8527) rotate(-3)" fill="#FCE1D5"/><circle cx="50.9329" cy="167.516" r="66.0012" transform="rotate(-3 50.9329 167.516)" fill="#F9BBA0"/><circle cx="151.997" cy="121.496" r="56.001" transform="rotate(-3 151.997 121.496)" fill="#FE544F"/></g><rect width="42.6674" height="37.334" transform="translate(160 70.3398) rotate(-3)" fill="#E8E8EB"/><rect width="42.6674" height="37.334" transform="translate(162.372 115.612) rotate(-3)" fill="#DCDDE1"/><rect width="66.6679" height="44.0008" transform="translate(140.779 162.14) rotate(-3)" fill="#D0D1D7"/><rect width="73.3347" height="44.0008" transform="translate(59.5542 166.397) rotate(-3)" fill="#E8E8EB"/></g><g filter="url(#feedLayoutSettingsfilter1_d_541_17506)"><g clip-path="url(#clip1feedLayoutSettings_541_17506)"><rect x="175.256" y="27.1967" width="176.132" height="168.003" rx="2.27031" transform="rotate(5 175.256 27.1967)" fill="white"/><g clip-path="url(#clip2feedLayoutSettings_541_17506)"><rect width="112.002" height="124.647" transform="translate(204.782 52.4471) rotate(5)" fill="#B5E5FF"/><circle cx="199.572" cy="173.295" r="46.9609" transform="rotate(5 199.572 173.295)" fill="#43A6DB"/><circle cx="338.706" cy="133.728" r="64.1417" transform="rotate(5 338.706 133.728)" fill="#86D0F9"/></g><g clip-path="url(#clip3feedLayoutSettings_541_17506)"><rect width="112.002" height="124.647" transform="translate(322.654 62.7597) rotate(5)" fill="#B6DDAD"/><circle cx="317.444" cy="183.608" r="46.9609" transform="rotate(5 317.444 183.608)" fill="#96CE89"/></g><g clip-path="url(#clip4feedLayoutSettings_541_17506)"><rect width="112.002" height="124.647" transform="translate(86.0059 42.0556) rotate(5)" fill="#FCE1D5"/><circle cx="214.51" cy="169.366" r="64.1417" transform="rotate(5 214.51 169.366)" fill="#F9BBA0"/></g></g></g><defs><filter id="feedLayoutSettingsfilter0_d_541_17506" x="36.8814" y="54.8182" width="190.773" height="179.418" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4.63923"/><feGaussianBlur stdDeviation="4.05932"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.16 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_541_17506"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_541_17506" result="shape"/></filter><filter id="feedLayoutSettingsfilter1_d_541_17506" x="153.701" y="24.2344" width="203.928" height="196.538" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="3.94961"/><feGaussianBlur stdDeviation="3.45591"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.16 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_541_17506"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_541_17506" result="shape"/></filter><clipPath id="clip0feedLayoutSettings_541_17506"><rect width="97.3351" height="82.6682" fill="white" transform="translate(54.8096 75.8527) rotate(-3)"/></clipPath><clipPath id="clip1feedLayoutSettings_541_17506"><rect x="175.256" y="27.1967" width="176.132" height="168.003" rx="2.27031" transform="rotate(5 175.256 27.1967)" fill="white"/></clipPath><clipPath id="clip2feedLayoutSettings_541_17506"><rect width="112.002" height="124.647" fill="white" transform="translate(204.782 52.4471) rotate(5)"/></clipPath><clipPath id="clip3feedLayoutSettings_541_17506"><rect width="112.002" height="124.647" fill="white" transform="translate(322.654 62.7597) rotate(5)"/></clipPath><clipPath id="clip4feedLayoutSettings_541_17506"><rect width="112.002" height="124.647" fill="white" transform="translate(86.0059 42.0556) rotate(5)"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=feed-layouts' )
						),
						'headerLayout'     => array(
							'heading'         => __( 'Get Stories, Followers and Advanced Header Options', 'instagram-feed' ),
							'description'     => __( 'Got stories to tell? We want to help you share them. Display Instagram stories right on your website in a pop-up lightbox to keep your users engaged and on your website for longer.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#headerLayoutfilter0_d_543_17792)"><rect x="113.089" y="44.6909" width="219.908" height="169.263" rx="3" transform="rotate(3 113.089 44.6909)" fill="#0096CC"/><circle cx="219.925" cy="94.3318" r="21.9039" transform="rotate(3 219.925 94.3318)" fill="#DCDDE1"/><circle cx="219.925" cy="94.3318" r="23.99" transform="rotate(3 219.925 94.3318)" stroke="url(#paint0_linear_543_17792)" stroke-width="1.04304"/><rect x="148.973" y="123.979" width="134.61" height="11.995" rx="1.33278" transform="rotate(3 148.973 123.979)" fill="#E8E8EB"/><rect x="166.559" y="144.919" width="97.2927" height="11.995" rx="1.33278" transform="rotate(3 166.559 144.919)" fill="#E8E8EB"/><path d="M191.365 180.919C193.326 181.022 194.997 179.517 195.1 177.556C195.203 175.595 193.698 173.923 191.737 173.821C189.776 173.718 188.104 175.223 188.002 177.184C187.899 179.145 189.404 180.816 191.365 180.919ZM191.272 182.694C188.903 182.57 184.111 183.511 183.987 185.871L183.894 187.645L198.091 188.39L198.184 186.615C198.308 184.255 193.641 182.818 191.272 182.694Z" fill="#141B38"/><path d="M211.234 187.974L213.955 188.116L214.643 174.987L211.913 174.844L208.396 177.023L208.268 179.461L211.736 177.362L211.79 177.365L211.234 187.974ZM217.462 188.3L227.006 188.8L227.12 186.617L221.17 186.305L221.176 186.196L224.055 183.828C226.43 181.864 227.239 180.866 227.323 179.274L227.324 179.256C227.441 177.017 225.613 175.334 222.838 175.189C220.063 175.043 217.916 176.673 217.786 179.148L217.782 179.221L220.33 179.354L220.343 179.273C220.442 178.092 221.398 177.257 222.744 177.328C223.909 177.389 224.688 178.196 224.641 179.27L224.64 179.288C224.592 180.198 224.203 180.826 222.428 182.32L217.56 186.417L217.462 188.3ZM232.551 189.091L235.271 189.233L235.959 176.105L233.23 175.962L229.712 178.14L229.585 180.578L233.052 178.479L233.107 178.482L232.551 189.091ZM238.805 189.419L241.462 189.558L241.631 186.328L242.353 185.609L244.937 189.74L248.004 189.901L244.428 184.139L248.333 180.138L245.321 179.98L241.823 183.71L241.768 183.708L242.15 176.429L239.493 176.29L238.805 189.419Z" fill="#141B38"/></g><g filter="url(#headerLayoutfilter1_d_543_17792)"><rect x="88.5157" y="41" width="219.908" height="169.263" rx="3" fill="white"/><circle cx="197.804" cy="84.9817" r="24.6564" stroke="url(#paint1_linear_543_17792)" stroke-width="1.33278"/><rect x="128.499" y="118.301" width="134.61" height="11.995" rx="1.33278" fill="#E8E8EB"/><rect x="147.158" y="138.293" width="97.2927" height="11.995" rx="1.33278" fill="#E8E8EB"/><path d="M173.813 172.945C175.776 172.945 177.367 171.354 177.367 169.391C177.367 167.427 175.776 165.837 173.813 165.837C171.849 165.837 170.259 167.427 170.259 169.391C170.259 171.354 171.849 172.945 173.813 172.945ZM173.813 174.722C171.44 174.722 166.705 175.913 166.705 178.276V180.053H180.921V178.276C180.921 175.913 176.185 174.722 173.813 174.722Z" fill="#141B38"/><path d="M194.025 178.95H196.749V165.803H194.016L190.618 168.163V170.604L193.971 168.327H194.025V178.95ZM200.261 178.95H209.818V176.763H203.86V176.654L206.611 174.139C208.88 172.053 209.636 171.014 209.636 169.42V169.402C209.636 167.161 207.723 165.575 204.944 165.575C202.165 165.575 200.106 167.315 200.106 169.794V169.867H202.657L202.666 169.785C202.703 168.6 203.614 167.716 204.962 167.716C206.128 167.716 206.948 168.482 206.957 169.557V169.575C206.957 170.486 206.602 171.133 204.907 172.718L200.261 177.064V178.95ZM215.371 178.95H218.095V165.803H215.362L211.963 168.163V170.604L215.316 168.327H215.371V178.95ZM221.634 178.95H224.294V175.716L224.977 174.959L227.774 178.95H230.845L226.973 173.383L230.663 169.183H227.647L224.349 173.092H224.294V165.803H221.634V178.95Z" fill="#141B38"/><g clip-path="url(#headerLayoutclip0_543_17792)"><rect x="176" y="63" width="44" height="44" rx="22" fill="#0068A0"/><circle cx="198" cy="80" r="8" fill="#B5E5FF"/><circle cx="198" cy="106" r="15" fill="#B5E5FF"/></g></g><defs><filter id="headerLayoutfilter0_d_543_17792" x="96.2966" y="40.7241" width="244.333" height="196.407" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="2.9751" operator="erode" in="SourceAlpha" result="effect1_dropShadow_543_17792"/><feOffset dy="3.9668"/><feGaussianBlur stdDeviation="5.45435"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_543_17792"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_543_17792" result="shape"/></filter><filter id="headerLayoutfilter1_d_543_17792" x="80.5822" y="37.0332" width="235.775" height="185.13" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="2.9751" operator="erode" in="SourceAlpha" result="effect1_dropShadow_543_17792"/><feOffset dy="3.9668"/><feGaussianBlur stdDeviation="5.45435"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_543_17792"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_543_17792" result="shape"/></filter><linearGradient id="paint0_linear_543_17792" x1="213.028" y1="162.104" x2="314.086" y2="58.9466" gradientUnits="userSpaceOnUse"><stop stop-color="white"/><stop offset="0.147864" stop-color="#F6640E"/><stop offset="0.443974" stop-color="#BA03A7"/><stop offset="0.733337" stop-color="#6A01B9"/><stop offset="1" stop-color="#6B01B9"/></linearGradient><linearGradient id="paint1_linear_543_17792" x1="190.906" y1="152.753" x2="291.964" y2="49.5964" gradientUnits="userSpaceOnUse"><stop stop-color="white"/><stop offset="0.147864" stop-color="#F6640E"/><stop offset="0.443974" stop-color="#BA03A7"/><stop offset="0.733337" stop-color="#6A01B9"/><stop offset="1" stop-color="#6B01B9"/></linearGradient><clipPath id="headerLayoutclip0_543_17792"><rect x="176" y="63" width="44" height="44" rx="22" fill="white"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=header' )
						),

						'postStyling'      => array(
							'heading'         => __( 'Display Captions, Likes, and Comments', 'instagram-feed' ),
							'description'     => __( 'Upgrade to Pro to display post captions below each post and in the lightbox, which can be crawled by search engines to help boost SEO.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#hoverstatefilter0_d_557_20473)"><g clip-path="url(#hoverstateclip0_557_20473)"><rect x="196.486" y="41.9882" width="139.542" height="147.818" rx="1.74428" transform="rotate(3 196.486 41.9882)" fill="#59AB46"/><path opacity="0.4" d="M207.272 102.814C208.625 102.884 212.65 103.095 217.927 103.372C224.523 103.718 224.523 103.718 230.104 104.01C235.686 104.303 236.7 104.356 250.4 105.074C264.099 105.792 261.562 105.659 268.666 106.031C275.769 106.403 276.784 106.457 289.976 107.148C300.529 107.701 312.977 108.353 317.882 108.61" stroke="white" stroke-width="6.91919"/><path opacity="0.4" d="M208.185 85.3947C209.538 85.4656 213.563 85.6766 218.84 85.9531C225.436 86.2988 225.436 86.2988 231.017 86.5913C236.599 86.8838 237.613 86.937 251.313 87.6549C265.012 88.3729 262.475 88.2399 269.579 88.6122C276.682 88.9845 277.697 89.0376 290.889 89.729C301.442 90.2821 313.89 90.9345 318.795 91.1915" stroke="white" stroke-width="6.91919"/><path opacity="0.4" d="M206.359 120.232C207.711 120.303 211.732 120.514 217.003 120.79C223.592 121.136 223.592 121.136 229.168 121.428C234.743 121.72 234.236 121.693 247.922 122.411C261.607 123.128 260.593 123.075 268.196 123.473" stroke="white" stroke-width="6.91919"/><path d="M272.181 159.772C272.033 159.764 271.895 159.698 271.796 159.589C271.698 159.479 271.647 159.335 271.654 159.187L271.742 157.521L269.52 157.405C269.225 157.389 268.949 157.258 268.752 157.038C268.554 156.819 268.452 156.53 268.468 156.236L268.817 149.571C268.832 149.277 268.964 149 269.183 148.803C269.403 148.605 269.691 148.503 269.986 148.519L278.872 148.984C279.167 149 279.443 149.132 279.64 149.351C279.838 149.57 279.94 149.859 279.925 150.153L279.575 156.818C279.56 157.113 279.428 157.389 279.209 157.586C278.99 157.784 278.701 157.886 278.406 157.87L275.018 157.693L272.855 159.646C272.739 159.745 272.597 159.794 272.458 159.786L272.181 159.772Z" fill="white"/><path d="M287.454 158.704L289.234 158.798L289.684 150.206L287.898 150.113L285.596 151.538L285.513 153.134L287.782 151.76L287.818 151.762L287.454 158.704ZM291.529 158.918L297.774 159.245L297.849 157.816L293.955 157.612L293.959 157.541L295.843 155.992C297.397 154.706 297.927 154.053 297.981 153.011L297.982 152.999C298.059 151.535 296.863 150.433 295.047 150.338C293.231 150.243 291.826 151.31 291.741 152.929L291.739 152.977L293.406 153.064L293.414 153.011C293.479 152.238 294.104 151.692 294.986 151.738C295.748 151.778 296.257 152.306 296.226 153.009L296.226 153.021C296.195 153.616 295.94 154.027 294.779 155.005L291.593 157.685L291.529 158.918Z" fill="white"/><g clip-path="url(#hoverstateclip1_557_20473)"><path d="M218.664 146.871C219.194 148.86 218.561 150.822 217.366 152.263C216.576 153.241 215.649 154.071 214.709 154.79C213.84 155.518 211.907 156.946 211.413 156.963C210.983 156.857 210.522 156.34 210.19 156.069C208.33 154.495 206.339 152.595 205.384 150.582C204.596 148.656 204.716 146.331 206.172 144.962C208.045 143.443 210.726 143.899 212.006 145.656C212.398 145.198 212.87 144.847 213.42 144.602C215.64 143.848 217.855 144.851 218.664 146.871Z" fill="white"/></g><path d="M226.488 155.509L228.269 155.602L228.719 147.011L226.933 146.918L224.631 148.343L224.548 149.939L226.816 148.565L226.852 148.567L226.488 155.509ZM230.563 155.723L236.809 156.05L236.884 154.621L232.99 154.417L232.994 154.346L234.878 152.797C236.432 151.511 236.961 150.858 237.016 149.816L237.017 149.804C237.093 148.34 235.897 147.238 234.082 147.143C232.266 147.048 230.861 148.114 230.776 149.734L230.773 149.782L232.44 149.869L232.449 149.816C232.513 149.043 233.139 148.497 234.02 148.543C234.782 148.583 235.292 149.111 235.261 149.814L235.26 149.826C235.229 150.421 234.975 150.832 233.813 151.809L230.628 154.49L230.563 155.723ZM242.229 156.334L243.938 156.424L244.021 154.846L245.152 154.905L245.227 153.47L244.096 153.411L244.388 147.832L241.852 147.699L238.116 153.032L238.037 154.532L242.312 154.756L242.229 156.334ZM239.676 153.227L239.678 153.18L242.606 149.04L242.647 149.043L242.42 153.371L239.676 153.227Z" fill="white"/></g></g><g filter="url(#hoverstatefilter1_d_557_20473)"><g clip-path="url(#hoverstateclip2_557_20473)"><rect x="63" y="55.1204" width="139.542" height="164.834" rx="1.74428" fill="white"/><path d="M74.5776 156.85C75.7308 156.82 79.1616 156.769 83.6591 156.797C89.2809 156.832 89.2809 156.876 94.0379 156.85C98.7948 156.823 99.6597 156.771 111.336 156.823C123.012 156.876 120.85 156.876 126.904 156.85C132.958 156.823 133.823 156.81 145.067 156.85C154.062 156.881 164.671 156.877 168.852 156.872" stroke="#DCDDE1" stroke-width="6.91919"/><path d="M74.5776 171.754C75.7308 171.729 79.1616 171.685 83.6591 171.709C89.2809 171.739 89.2809 171.777 94.0379 171.754C98.7948 171.731 98.3623 171.709 110.038 171.754C121.715 171.799 120.85 171.789 127.336 171.783" stroke="#DCDDE1" stroke-width="6.91919"/><g clip-path="url(#hoverstateclip3_557_20473)"><rect x="72.5933" y="52.5039" width="132.565" height="88.9581" fill="#2C324C"/><circle cx="97.5117" cy="88.7693" r="71.0037" fill="#0068A0"/><circle cx="209.41" cy="160.88" r="73.7586" fill="#FE544F"/></g><path d="M143.009 210.272C142.862 210.272 142.72 210.214 142.616 210.109C142.512 210.005 142.453 209.864 142.453 209.716V208.048H140.229C139.934 208.048 139.651 207.93 139.442 207.722C139.233 207.513 139.116 207.23 139.116 206.935V200.262C139.116 199.967 139.233 199.684 139.442 199.475C139.651 199.266 139.934 199.149 140.229 199.149H149.127C149.422 199.149 149.705 199.266 149.913 199.475C150.122 199.684 150.239 199.967 150.239 200.262V206.935C150.239 207.23 150.122 207.513 149.913 207.722C149.705 207.93 149.422 208.048 149.127 208.048H145.734L143.677 210.111C143.565 210.217 143.426 210.272 143.287 210.272H143.009Z" fill="#434960"/><path d="M158.207 208.407H159.989V199.804H158.201L155.977 201.348V202.946L158.171 201.455H158.207V208.407ZM162.287 208.407H168.541V206.976H164.642V206.904L166.443 205.259C167.927 203.894 168.422 203.214 168.422 202.171V202.159C168.422 200.692 167.17 199.655 165.352 199.655C163.533 199.655 162.186 200.793 162.186 202.415V202.463H163.855L163.861 202.409C163.885 201.634 164.481 201.056 165.364 201.056C166.127 201.056 166.663 201.556 166.669 202.26V202.272C166.669 202.868 166.437 203.291 165.328 204.329L162.287 207.173V208.407Z" fill="#434960"/><g clip-path="url(#hoverstateclip4_557_20473)"><path d="M88.8934 200.19C89.5271 202.148 88.9967 204.141 87.879 205.642C87.1412 206.66 86.2595 207.538 85.358 208.305C84.5286 209.077 82.6726 210.605 82.1806 210.647C81.7458 210.564 81.2578 210.072 80.9126 209.819C78.9728 208.344 76.8846 206.551 75.8258 204.591C74.9381 202.708 74.9365 200.38 76.3181 198.937C78.1096 197.322 80.8104 197.637 82.1806 199.325C82.5486 198.847 83.001 198.472 83.5381 198.199C85.7151 197.33 87.9798 198.215 88.8934 200.19Z" fill="#434960"/></g><path d="M97.1573 208.407H98.9399V199.804H97.1514L94.9276 201.348V202.946L97.1216 201.455H97.1573V208.407ZM101.238 208.407H107.492V206.976H103.593V206.904L105.393 205.259C106.878 203.894 107.373 203.214 107.373 202.171V202.159C107.373 200.692 106.121 199.655 104.302 199.655C102.484 199.655 101.137 200.793 101.137 202.415V202.463H102.806L102.812 202.409C102.836 201.634 103.432 201.056 104.314 201.056C105.077 201.056 105.614 201.556 105.62 202.26V202.272C105.62 202.868 105.387 203.291 104.278 204.329L101.238 207.173V208.407ZM112.92 208.407H114.631V206.827H115.764V205.39H114.631V199.804H112.091L108.639 205.324V206.827H112.92V208.407ZM110.207 205.438V205.39L112.914 201.103H112.956V205.438H110.207Z" fill="#434960"/></g></g><path d="M293.823 189.298L293.89 188.027L292.48 187.953L292.546 186.682L289.727 186.534L289.793 185.263L285.564 185.041L285.63 183.77L282.811 183.623L283.077 178.539L281.667 178.465L281.734 177.194L278.914 177.046L278.848 178.317L277.438 178.243L276.838 189.682L275.429 189.608L275.495 188.337L271.266 188.115L270.999 193.199L272.409 193.273L272.276 195.815L273.686 195.889L273.553 198.431L274.962 198.505L274.829 201.047L276.239 201.121L276.039 204.934L291.547 205.746L291.814 200.663L293.224 200.736L293.423 196.923L292.014 196.85L291.814 200.663L290.404 200.589L290.204 204.402L277.516 203.737L277.649 201.195L276.239 201.121L276.372 198.579L274.962 198.505L275.096 195.963L273.686 195.889L273.819 193.347L272.409 193.273L272.609 189.46L275.429 189.608L275.362 190.879L276.772 190.953L276.572 194.766L277.982 194.84L278.848 178.317L281.667 178.465L281.068 189.904L282.478 189.977L282.744 184.894L285.564 185.041L285.297 190.125L286.707 190.199L286.907 186.386L289.727 186.534L289.46 191.618L290.87 191.692L291.07 187.879L292.48 187.953L292.413 189.224L293.823 189.298L293.423 196.923L294.833 196.997L295.233 189.371L293.823 189.298Z" fill="#141B38"/><path d="M292.014 196.85L293.424 196.923L293.823 189.298L292.413 189.224L292.48 187.953L291.07 187.879L290.87 191.692L289.461 191.618L289.727 186.534L286.907 186.386L286.708 190.199L285.298 190.125L285.564 185.041L282.745 184.894L282.478 189.977L281.068 189.904L281.668 178.465L278.848 178.317L277.982 194.84L276.572 194.766L276.772 190.953L275.362 190.879L275.429 189.608L272.609 189.46L272.409 193.273L273.819 193.347L273.686 195.889L275.096 195.963L274.963 198.505L276.372 198.579L276.239 201.121L277.649 201.195L277.516 203.737L290.204 204.402L290.404 200.589L291.814 200.663L292.014 196.85Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M280.869 193.717L282.278 193.791L281.964 199.782L280.555 199.708L280.869 193.717ZM285.098 193.938L283.688 193.864L283.374 199.856L284.784 199.93L285.098 193.938ZM287.917 194.086L286.507 194.012L286.193 200.004L287.603 200.078L287.917 194.086Z" fill="#141B38"/><defs><filter id="hoverstatefilter0_d_557_20473" x="181.831" y="38.5286" width="160.926" height="168.757" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="2.5947" operator="erode" in="SourceAlpha" result="effect1_dropShadow_557_20473"/><feOffset dy="3.45959"/><feGaussianBlur stdDeviation="4.75694"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_20473"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_557_20473" result="shape"/></filter><filter id="hoverstatefilter1_d_557_20473" x="56.0808" y="51.6608" width="153.38" height="178.672" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="2.5947" operator="erode" in="SourceAlpha" result="effect1_dropShadow_557_20473"/><feOffset dy="3.45959"/><feGaussianBlur stdDeviation="4.75694"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_20473"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_557_20473" result="shape"/></filter><clipPath id="hoverstateclip0_557_20473"><rect x="196.486" y="41.9882" width="139.542" height="147.818" rx="1.74428" transform="rotate(3 196.486 41.9882)" fill="white"/></clipPath><clipPath id="hoverstateclip1_557_20473"><rect width="13.9542" height="13.9542" fill="white" transform="translate(205.154 143.248) rotate(3)"/></clipPath><clipPath id="hoverstateclip2_557_20473"><rect x="63" y="55.1204" width="139.542" height="164.834" rx="1.74428" fill="white"/></clipPath><clipPath id="hoverstateclip3_557_20473"><rect width="139.542" height="86.3416" fill="white" transform="translate(62.9998 55.1204)"/></clipPath><clipPath id="hoverstateclip4_557_20473"><rect width="13.9542" height="13.9542" fill="white" transform="translate(75.21 197.279)"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=posts' )
						),

						'lightbox'         => array(
							'heading'         => __( 'Upgrade to Pro to enable the popup Lightbox', 'instagram-feed' ),
							'description'     => __( 'Allow visitors to view your photos and videos in a beautiful full size lightbox, keeping them on your site for longer to discover more of your content.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M50.5998 136.122L48.2638 134.019L39.3134 143.959L49.2538 152.91L51.3572 150.574L43.7685 143.726L50.5998 136.122Z" fill="#8C8F9A"/><path d="M347.201 119.809L345.097 122.145L352.686 128.993L345.855 136.597L348.191 138.7L357.141 128.76L347.201 119.809Z" fill="#8C8F9A"/><g clip-path="url(#lightboxxclip0_557_20899)" filter="url(#lightboxxfilter0_d_557_20899)"><rect width="261.925" height="173.162" transform="translate(62.6831 52.3919) rotate(-3)" fill="white"/><rect x="112.468" y="187.874" width="93.129" height="5.82056" rx="1.45514" transform="rotate(-3 112.468 187.874)" fill="#D0D1D7"/><rect x="113.151" y="200.952" width="53.8402" height="5.82056" rx="1.45514" transform="rotate(-3 113.151 200.952)" fill="#D0D1D7"/><circle cx="94.1753" cy="195.21" r="8.73084" transform="rotate(-3 94.1753 195.21)" fill="#DCDDE1"/><g clip-path="url(#lightboxxclip1_557_20899)"><rect x="62.6812" y="52.3918" width="262.169" height="173.162" transform="rotate(-3 62.6812 52.3918)" fill="#FE544F"/><rect x="191.14" y="20.5734" width="271.58" height="334.479" rx="55.73" transform="rotate(2.99107 191.14 20.5734)" fill="#DCDDE1"/><circle cx="141.741" cy="201.742" r="113.935" transform="rotate(-3 141.741 201.742)" fill="#0096CC"/></g></g><defs><filter id="lightboxxfilter0_d_557_20899" x="53.8243" y="34.2544" width="288.346" height="204.35" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="3.32203" operator="erode" in="SourceAlpha" result="effect1_dropShadow_557_20899"/><feOffset dy="4.42938"/><feGaussianBlur stdDeviation="6.0904"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.05 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_20899"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_557_20899" result="shape"/></filter><clipPath id="lightboxxclip0_557_20899"><rect width="261.925" height="173.162" fill="white" transform="translate(62.6831 52.3919) rotate(-3)"/></clipPath><clipPath id="lightboxxclip1_557_20899"><rect width="262.15" height="121.608" fill="white" transform="translate(62.6821 52.3919) rotate(-3)"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=lightbox' )
						),

						'filtermoderation' => array(
							'heading'         => __( 'Get Advanced Moderation and Filters with Pro', 'instagram-feed' ),
							'description'     => __( 'Use powerful moderation tools to easily create feeds of only specific photos, or choose specific ones to exclude. You can also easily choose to include or block specific words or phrases in your posts.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#moderationfltrclip0_542_16736)"><g filter="url(#moderationfltrfilter0_ddd_542_16736)"><rect x="139.981" y="52.5992" width="162.17" height="179.401" rx="3.96584" fill="white"/></g><path d="M156.198 174.881C157.55 174.847 161.57 174.786 166.841 174.819C173.429 174.86 173.429 174.912 179.003 174.881C184.578 174.85 185.592 174.788 199.275 174.85C212.958 174.912 210.424 174.912 217.519 174.881C224.614 174.85 225.627 174.835 238.804 174.881C249.345 174.918 261.778 174.913 266.677 174.907" stroke="#DCDDE1" stroke-width="8.10851"/><path d="M156.198 194.559C157.55 194.53 161.57 194.478 166.841 194.506C173.429 194.542 173.429 194.586 179.003 194.559C184.578 194.533 184.071 194.506 197.754 194.559C211.437 194.613 210.424 194.6 218.026 194.593" stroke="#DCDDE1" stroke-width="8.10851"/><g clip-path="url(#moderationfltrclip1_542_16736)"><rect x="151.131" y="65.7755" width="139.912" height="88.1801" fill="#8C8F9A"/><circle cx="157.01" cy="165.713" r="48.2051" fill="#EC352F"/><circle cx="295.745" cy="112.805" r="65.8411" fill="#DCDDE1"/></g><circle cx="291.51" cy="58.1738" r="18.7509" fill="#D72C2C"/><path d="M290.886 55.6399L293.028 57.7751V57.667C293.028 57.1294 292.815 56.6137 292.435 56.2336C292.054 55.8534 291.539 55.6399 291.001 55.6399H290.886ZM287.981 56.1804L289.028 57.2278C288.994 57.3697 288.974 57.5116 288.974 57.667C288.974 58.2046 289.188 58.7202 289.568 59.1004C289.948 59.4805 290.464 59.6941 291.001 59.6941C291.15 59.6941 291.298 59.6738 291.44 59.6401L292.488 60.6874C292.035 60.9104 291.535 61.0455 291.001 61.0455C290.105 61.0455 289.246 60.6896 288.612 60.056C287.979 59.4224 287.623 58.563 287.623 57.667C287.623 57.1332 287.758 56.6332 287.981 56.1804ZM284.244 52.4438L285.785 53.9844L286.089 54.2884C284.974 55.1669 284.095 56.3156 283.568 57.667C284.737 60.6333 287.623 62.7348 291.001 62.7348C292.049 62.7348 293.049 62.5321 293.961 62.1672L294.251 62.451L296.224 64.4241L297.083 63.5659L285.102 51.5856L284.244 52.4438ZM291.001 54.2884C291.897 54.2884 292.757 54.6444 293.39 55.278C294.024 55.9116 294.38 56.7709 294.38 57.667C294.38 58.0994 294.292 58.5184 294.136 58.8968L296.116 60.8766C297.13 60.032 297.941 58.9238 298.434 57.667C297.265 54.7006 294.38 52.5992 291.001 52.5992C290.055 52.5992 289.15 52.7681 288.298 53.0722L289.765 54.5249C290.15 54.3763 290.562 54.2884 291.001 54.2884Z" fill="white"/><g filter="url(#moderationfltrfilter1_ddd_542_16736)"><rect x="85.7402" y="31.9814" width="162.17" height="179.401" rx="3.96584" fill="white"/><path d="M101.957 154.263C103.308 154.229 107.329 154.169 112.599 154.201C119.188 154.242 119.188 154.294 124.762 154.263C130.337 154.232 131.35 154.171 145.034 154.232C158.717 154.294 156.183 154.294 163.278 154.263C170.373 154.232 171.386 154.217 184.562 154.263C195.104 154.3 207.537 154.296 212.436 154.289" stroke="#DCDDE1" stroke-width="8.10851"/><path d="M101.957 173.942C103.308 173.912 107.329 173.86 112.599 173.889C119.188 173.924 119.188 173.968 124.762 173.942C130.337 173.915 129.83 173.889 143.513 173.942C157.196 173.995 156.183 173.982 163.784 173.975" stroke="#DCDDE1" stroke-width="8.10851"/><g clip-path="url(#moderationfltrclip2_542_16736)"><rect x="96.8887" y="45.1577" width="139.912" height="88.1801" fill="#2C324C"/><circle cx="125.771" cy="71.4144" r="83.2083" fill="#0068A0"/><circle cx="256.9" cy="155.92" r="86.4368" fill="#FE544F"/></g></g><circle cx="234.383" cy="30.7509" r="18.7509" fill="#0096CC"/><g clip-path="url(#moderationfltrclip3_542_16736)"><path d="M233.874 28.217C233.337 28.217 232.821 28.4306 232.441 28.8108C232.061 29.1909 231.847 29.7065 231.847 30.2442C231.847 30.7818 232.061 31.2974 232.441 31.6775C232.821 32.0577 233.337 32.2713 233.874 32.2713C234.412 32.2713 234.927 32.0577 235.308 31.6775C235.688 31.2974 235.901 30.7818 235.901 30.2442C235.901 29.7065 235.688 29.1909 235.308 28.8108C234.927 28.4306 234.412 28.217 233.874 28.217ZM233.874 33.6227C232.978 33.6227 232.119 33.2667 231.485 32.6331C230.852 31.9995 230.496 31.1402 230.496 30.2442C230.496 29.3481 230.852 28.4888 231.485 27.8552C232.119 27.2216 232.978 26.8656 233.874 26.8656C234.77 26.8656 235.63 27.2216 236.263 27.8552C236.897 28.4888 237.253 29.3481 237.253 30.2442C237.253 31.1402 236.897 31.9995 236.263 32.6331C235.63 33.2667 234.77 33.6227 233.874 33.6227ZM233.874 25.1763C230.496 25.1763 227.61 27.2778 226.441 30.2442C227.61 33.2105 230.496 35.312 233.874 35.312C237.253 35.312 240.138 33.2105 241.307 30.2442C240.138 27.2778 237.253 25.1763 233.874 25.1763Z" fill="white"/></g></g><defs><filter id="moderationfltrfilter0_ddd_542_16736" x="114.203" y="38.7187" width="213.726" height="230.957" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="11.8975"/><feGaussianBlur stdDeviation="12.889"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_542_16736"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.98292"/><feGaussianBlur stdDeviation="1.98292"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_542_16736" result="effect2_dropShadow_542_16736"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="5.94876"/><feGaussianBlur stdDeviation="5.94876"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_542_16736" result="effect3_dropShadow_542_16736"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_542_16736" result="shape"/></filter><filter id="moderationfltrfilter1_ddd_542_16736" x="59.9623" y="18.101" width="213.726" height="230.957" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="11.8975"/><feGaussianBlur stdDeviation="12.889"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_542_16736"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="1.98292"/><feGaussianBlur stdDeviation="1.98292"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/><feBlend mode="normal" in2="effect1_dropShadow_542_16736" result="effect2_dropShadow_542_16736"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="5.94876"/><feGaussianBlur stdDeviation="5.94876"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="effect2_dropShadow_542_16736" result="effect3_dropShadow_542_16736"/><feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow_542_16736" result="shape"/></filter><clipPath id="moderationfltrclip0_542_16736"><path d="M0 0H396V264H0V0Z" fill="white"/></clipPath><clipPath id="moderationfltrclip1_542_16736"><rect x="151.131" y="65.7755" width="139.912" height="88.1801" rx="2" fill="white"/></clipPath><clipPath id="moderationfltrclip2_542_16736"><rect x="96.8887" y="45.1577" width="139.912" height="88.1801" rx="2" fill="white"/></clipPath><clipPath id="moderationfltrclip3_542_16736"><rect width="16.217" height="16.217" fill="white" transform="translate(225.767 22.1356)"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=filters' )
						),

						'shoppablefeed'    => array(
							'heading'         => __( 'Upgrade to Pro to Get Shoppable Feeds', 'instagram-feed' ),
							'description'     => __( 'Automatically link Instagram posts to custom URLs of your choosing by adding the URL in the caption, or manually add links to specific pages or products on your site (or other sites) in a quick and easy way.', 'instagram-feed' ),
							'img'             => '<svg width="396" height="264" viewBox="0 0 396 264" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#shoppablefeedfilter0_d_557_17550)"><rect x="234.717" y="38.2059" width="143" height="158.889" rx="2" transform="rotate(4 234.717 38.2059)" fill="white"/></g><rect width="143" height="82.0926" transform="translate(234.717 38.2059) rotate(4)" fill="#E8E8EB"/><g filter="url(#shoppablefeedfilter1_dd_557_17550)"><mask id="shoppablefeedmask0_557_17550" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="266" y="53" width="80" height="62"><path fill-rule="evenodd" clip-rule="evenodd" d="M315.226 54.937C315.099 58.5599 310.591 61.343 305.157 61.1533C299.723 60.9635 295.42 57.8727 295.546 54.2498C295.546 54.2498 295.546 54.2497 295.546 54.2497L286.163 53.922C286.057 53.9183 285.951 53.9404 285.856 53.9863L278.402 57.5651C278.37 57.5802 278.351 57.6124 278.352 57.6471C278.35 57.62 278.321 57.6035 278.297 57.6159L266.724 63.5265C266.477 63.6528 266.336 63.921 266.372 64.1964L268.295 78.8772C268.344 79.2535 268.701 79.5097 269.073 79.4357L278.317 77.5986C278.714 77.5198 279.086 77.8146 279.101 78.2185L280.339 112.219C280.352 112.563 280.628 112.839 280.972 112.851L326.982 114.458C327.326 114.47 327.621 114.214 327.657 113.872L331.266 80.0236C331.308 79.6249 331.695 79.3572 332.083 79.4578L341.845 81.9875C342.209 82.0819 342.578 81.8513 342.653 81.4825L345.594 66.9741C345.651 66.6966 345.523 66.4143 345.277 66.2738L333.503 59.5421C333.48 59.5288 333.45 59.5433 333.447 59.57C333.45 59.5361 333.433 59.5032 333.404 59.4863L326.216 55.3957C326.124 55.3432 326.021 55.3139 325.915 55.3102L315.226 54.9369C315.226 54.9369 315.226 54.937 315.226 54.937Z" fill="white"/></mask><g mask="url(#shoppablefeedmask0_557_17550)"><rect x="261.444" y="49.1168" width="94.5192" height="70.8894" transform="rotate(4 261.444 49.1168)" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M304.293 77.7265C302.782 76.9164 301.23 75.7559 299.747 75.1625C300.196 76.4854 300.555 77.8865 300.931 79.2729C299.943 79.9127 298.808 80.3837 297.719 80.9071C298.549 81.8507 299.776 82.4491 300.728 83.2863C299.853 84.2243 298.197 85.147 297.749 86.0211C299.431 85.9288 301.399 85.7258 302.956 85.7657C303.177 87.5038 303.222 89.3956 303.597 90.9999C304.464 88.9723 305.268 86.8705 306.263 84.99C307.552 85.6316 309.122 86.4139 310.395 86.828C309.575 85.4894 308.84 84.0769 308.131 82.6421C309.383 81.8618 310.648 81.0961 311.86 80.2694C310.247 80.0064 308.572 79.7978 306.872 79.6108C306.724 77.8331 306.942 75.7372 306.638 74.0953C305.915 75.3731 305.086 76.5293 304.293 77.7265ZM304.045 92.3479C303.766 93.2504 304.446 93.6761 304.301 94.2247C303.62 93.9356 303.104 93.7755 302.159 93.8425C302.238 93.1288 302.889 93.0725 302.817 92.1846C292.92 90.3527 294.16 72.3239 304.157 71.908C316.657 71.388 315.549 92.832 304.045 92.3479Z" fill="#FE544F"/><path fill-rule="evenodd" clip-rule="evenodd" d="M306.638 74.0951C306.942 75.7371 306.724 77.833 306.872 79.6107C308.572 79.7977 310.247 80.0062 311.86 80.2693C310.648 81.096 309.383 81.8617 308.131 82.642C308.84 84.0768 309.575 85.4893 310.395 86.8279C309.122 86.4138 307.552 85.6315 306.263 84.9899C305.268 86.8704 304.464 88.9721 303.597 90.9997C303.221 89.3955 303.177 87.5037 302.956 85.7655C301.399 85.7257 299.431 85.9287 297.749 86.021C298.197 85.1469 299.853 84.2242 300.728 83.2861C299.776 82.449 298.549 81.8506 297.719 80.907C298.808 80.3836 299.943 79.9126 300.931 79.2727C300.555 77.8864 300.196 76.4853 299.747 75.1624C301.23 75.7557 302.782 76.9163 304.293 77.7264C305.086 76.5292 305.915 75.3729 306.638 74.0951Z" fill="white"/></g></g><path d="M245.82 168.987C245.767 169.739 245.479 170.369 244.954 170.878C244.429 171.387 243.773 171.678 242.987 171.751L242.896 173.041C242.892 173.11 242.866 173.165 242.819 173.206C242.771 173.247 242.714 173.266 242.645 173.261L241.655 173.192C241.591 173.187 241.538 173.16 241.494 173.11C241.451 173.06 241.432 173.003 241.436 172.939L241.526 171.649C241.207 171.583 240.9 171.485 240.607 171.355C240.313 171.226 240.073 171.1 239.885 170.975C239.697 170.851 239.524 170.721 239.367 170.584C239.209 170.447 239.102 170.347 239.045 170.283C238.988 170.22 238.948 170.173 238.926 170.142C238.85 170.033 238.852 169.932 238.932 169.838L239.757 168.896C239.795 168.849 239.853 168.824 239.932 168.82C240.006 168.815 240.064 168.841 240.104 168.898L240.118 168.914C240.636 169.439 241.209 169.788 241.836 169.96C242.014 170.012 242.194 170.044 242.375 170.057C242.771 170.085 243.127 170.003 243.443 169.813C243.759 169.623 243.93 169.334 243.957 168.945C243.967 168.808 243.939 168.675 243.874 168.547C243.81 168.419 243.735 168.31 243.65 168.22C243.566 168.131 243.429 168.028 243.24 167.914C243.052 167.8 242.896 167.71 242.772 167.644C242.649 167.579 242.459 167.485 242.202 167.364C242.017 167.272 241.871 167.2 241.764 167.148C241.657 167.096 241.511 167.02 241.326 166.921C241.141 166.822 240.994 166.735 240.883 166.66C240.773 166.586 240.641 166.489 240.487 166.37C240.333 166.25 240.21 166.137 240.116 166.029C240.023 165.921 239.925 165.793 239.823 165.645C239.72 165.498 239.643 165.349 239.592 165.2C239.541 165.051 239.501 164.884 239.472 164.699C239.443 164.514 239.436 164.321 239.45 164.12C239.497 163.441 239.779 162.864 240.294 162.386C240.809 161.909 241.456 161.623 242.234 161.529L242.327 160.202C242.331 160.139 242.358 160.085 242.408 160.042C242.458 159.998 242.515 159.979 242.578 159.983L243.569 160.052C243.637 160.057 243.692 160.083 243.733 160.13C243.774 160.178 243.792 160.236 243.787 160.305L243.696 161.602C243.973 161.651 244.24 161.726 244.495 161.828C244.751 161.93 244.958 162.027 245.117 162.12C245.275 162.212 245.424 162.315 245.563 162.429C245.703 162.542 245.793 162.62 245.834 162.662C245.876 162.705 245.91 162.742 245.937 162.773C246.014 162.868 246.02 162.962 245.954 163.056L245.285 164.091C245.24 164.162 245.181 164.197 245.108 164.197C245.038 164.207 244.973 164.185 244.913 164.131C244.9 164.115 244.866 164.083 244.813 164.035C244.76 163.987 244.669 163.915 244.54 163.82C244.412 163.725 244.274 163.636 244.128 163.554C243.981 163.472 243.803 163.396 243.594 163.324C243.386 163.253 243.178 163.21 242.973 163.196C242.508 163.163 242.122 163.242 241.813 163.433C241.505 163.624 241.339 163.886 241.316 164.22C241.307 164.348 241.319 164.468 241.353 164.579C241.388 164.69 241.453 164.797 241.549 164.9C241.645 165.003 241.736 165.09 241.821 165.163C241.907 165.236 242.039 165.321 242.216 165.42C242.394 165.519 242.537 165.596 242.646 165.65C242.756 165.705 242.922 165.784 243.146 165.889C243.398 166.005 243.591 166.097 243.724 166.163C243.857 166.229 244.037 166.328 244.264 166.46C244.49 166.592 244.668 166.709 244.796 166.812C244.924 166.914 245.067 167.048 245.225 167.212C245.383 167.376 245.502 167.541 245.581 167.707C245.661 167.873 245.724 168.067 245.773 168.287C245.821 168.508 245.837 168.741 245.82 168.987Z" fill="#0068A0"/><rect x="240.659" y="143.036" width="74.1481" height="10.5926" transform="rotate(4 240.659 143.036)" fill="#DCDDE1"/><rect x="252.575" y="162.452" width="32.6605" height="10.5926" transform="rotate(4 252.575 162.452)" fill="#BFE8FF"/><rect x="328.66" y="112.025" width="33.5432" height="33.5432" rx="16.7716" transform="rotate(4 328.66 112.025)" fill="#FE544F"/><g clip-path="url(#shoppablefeedclip0_557_17550)"><path d="M338.611 121.57C338.377 121.554 338.147 121.631 337.97 121.784C337.794 121.938 337.685 122.155 337.669 122.389C337.653 122.622 337.73 122.853 337.883 123.03C338.037 123.206 338.254 123.315 338.488 123.331L339.562 123.406L339.756 124.501C339.757 124.514 339.759 124.526 339.762 124.539L340.623 129.404L339.782 130.134C338.595 131.166 339.248 133.118 340.817 133.227L348.377 133.756C348.611 133.772 348.841 133.695 349.018 133.542C349.195 133.388 349.303 133.171 349.319 132.937C349.336 132.704 349.259 132.473 349.105 132.296C348.952 132.12 348.734 132.011 348.5 131.995L340.94 131.466L341.882 130.647L347.682 131.053C347.845 131.064 348.008 131.03 348.153 130.954C348.299 130.877 348.419 130.762 348.503 130.621L351.514 125.522C351.59 125.393 351.632 125.246 351.636 125.096C351.64 124.945 351.605 124.796 351.535 124.663C351.465 124.53 351.363 124.417 351.237 124.335C351.111 124.252 350.966 124.204 350.816 124.193L341.376 123.533L341.18 122.419C341.145 122.226 341.047 122.049 340.901 121.917C340.755 121.786 340.568 121.707 340.372 121.693L338.611 121.57Z" fill="white"/><path d="M349.104 136.019C349.08 136.369 348.917 136.696 348.652 136.926C348.387 137.156 348.041 137.272 347.691 137.248C347.34 137.223 347.014 137.06 346.784 136.795C346.553 136.53 346.438 136.185 346.462 135.834C346.487 135.484 346.649 135.158 346.915 134.928C347.18 134.697 347.525 134.581 347.876 134.606C348.226 134.63 348.552 134.793 348.782 135.058C349.013 135.323 349.129 135.669 349.104 136.019Z" fill="white"/><path d="M340.646 136.755C340.996 136.78 341.342 136.664 341.607 136.433C341.872 136.203 342.035 135.877 342.059 135.527C342.084 135.176 341.968 134.831 341.738 134.566C341.507 134.3 341.181 134.138 340.831 134.113C340.48 134.089 340.135 134.204 339.87 134.435C339.605 134.665 339.442 134.992 339.417 135.342C339.393 135.692 339.509 136.038 339.739 136.303C339.969 136.568 340.296 136.731 340.646 136.755Z" fill="white"/></g><path d="M355.663 146.817L355.732 145.836L354.644 145.76L354.712 144.78L352.537 144.628L352.605 143.647L349.342 143.419L349.411 142.438L347.235 142.286L347.509 138.363L346.422 138.287L346.49 137.307L344.315 137.155L344.246 138.135L343.158 138.059L342.541 146.885L341.454 146.809L341.522 145.828L338.259 145.6L337.985 149.523L339.072 149.599L338.935 151.56L340.023 151.636L339.886 153.597L340.974 153.673L340.836 155.634L341.924 155.711L341.718 158.652L353.684 159.489L353.958 155.567L355.046 155.643L355.252 152.701L354.164 152.625L353.958 155.567L352.87 155.491L352.665 158.432L342.875 157.748L343.012 155.787L341.924 155.711L342.061 153.749L340.974 153.673L341.111 151.712L340.023 151.636L340.16 149.675L339.072 149.599L339.278 146.657L341.454 146.809L341.385 147.789L342.473 147.866L342.267 150.807L343.355 150.883L344.246 138.135L346.422 138.287L345.805 147.113L346.892 147.189L347.167 143.267L349.342 143.419L349.068 147.341L350.156 147.417L350.361 144.475L352.537 144.628L352.263 148.55L353.35 148.626L353.556 145.684L354.644 145.76L354.575 146.741L355.663 146.817L355.252 152.701L356.339 152.777L356.751 146.893L355.663 146.817Z" fill="#141B38"/><path d="M354.164 152.625L355.252 152.701L355.663 146.817L354.576 146.741L354.644 145.76L353.556 145.684L353.351 148.626L352.263 148.55L352.537 144.628L350.362 144.476L350.156 147.417L349.068 147.341L349.342 143.419L347.167 143.267L346.893 147.189L345.805 147.113L346.422 138.287L344.247 138.135L343.355 150.884L342.267 150.807L342.473 147.866L341.385 147.79L341.454 146.809L339.278 146.657L339.073 149.599L340.16 149.675L340.023 151.636L341.111 151.712L340.974 153.673L342.062 153.749L341.924 155.711L343.012 155.787L342.875 157.748L352.665 158.433L352.871 155.491L353.958 155.567L354.164 152.625Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M345.6 150.055L346.687 150.131L346.364 154.754L345.276 154.678L345.6 150.055ZM348.861 150.283L347.774 150.207L347.45 154.83L348.538 154.906L348.861 150.283ZM351.037 150.435L349.949 150.359L349.626 154.982L350.714 155.058L351.037 150.435Z" fill="#141B38"/><g filter="url(#shoppablefeedfilter2_d_557_17550)"><g clip-path="url(#shoppablefeedclip1_557_17550)"><rect x="19.4355" y="57.3804" width="135.359" height="149.741" rx="1.66935" transform="rotate(-4 19.4355 57.3804)" fill="white"/><path d="M40.0569 158.253C41.1801 158.146 44.5242 157.862 48.9146 157.582C54.4026 157.233 54.4056 157.275 59.0454 156.925C63.6853 156.575 64.5256 156.465 75.9223 155.719C87.319 154.974 85.2092 155.121 91.115 154.683C97.0207 154.244 97.8637 154.172 108.838 153.443C117.617 152.86 127.969 152.133 132.047 151.842" stroke="#DCDDE1" stroke-width="6.76797"/><path d="M41.2028 174.638C42.3263 174.535 45.6709 174.258 50.061 173.974C55.5487 173.62 55.5512 173.657 60.1913 173.31C64.8314 172.964 64.4079 172.971 75.8041 172.219C87.2003 171.466 86.3557 171.515 92.6848 171.066" stroke="#DCDDE1" stroke-width="6.76797"/><g clip-path="url(#shoppablefeedclip2_557_17550)"><rect x="28.8848" y="56.2416" width="126" height="85" transform="rotate(-4 28.8848 56.2416)" fill="#2C324C"/><circle cx="55.061" cy="87.8833" r="69.4519" transform="rotate(-4 55.061 87.8833)" fill="#0068A0"/><circle cx="169.165" cy="150.611" r="72.1466" transform="rotate(-4 169.165 150.611)" fill="#FE544F"/><g filter="url(#shoppablefeedfilter3_dd_557_17550)"><mask id="shoppablefeedmask1_557_17550" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="48" y="63" width="85" height="66"><path fill-rule="evenodd" clip-rule="evenodd" d="M99.0497 64.5008C99.3159 68.3075 94.9027 71.7172 89.1926 72.1164C83.4825 72.5157 78.6378 69.7535 78.3716 65.9467C78.3716 65.9467 78.3716 65.9467 78.3715 65.9467L68.5145 66.6359C68.4031 66.6437 68.2952 66.6784 68.2002 66.737L60.7405 71.3345C60.7395 71.3351 60.739 71.3362 60.7391 71.3374C60.739 71.3365 60.7379 71.336 60.7372 71.3365L49.2225 78.8298C48.9775 78.9893 48.8594 79.2856 48.9274 79.5698L52.5559 94.7276C52.6489 95.1162 53.0511 95.3451 53.4327 95.2267L62.9064 92.2867C63.3127 92.1606 63.7354 92.4282 63.7952 92.8495L68.8327 128.308C68.8837 128.667 69.2033 128.925 69.5649 128.9L117.909 125.52C118.27 125.494 118.551 125.194 118.552 124.831L118.605 88.9987C118.606 88.5767 118.981 88.2538 119.399 88.3164L129.898 89.8909C130.289 89.9496 130.65 89.6677 130.688 89.2734L132.171 73.7607C132.2 73.4641 132.035 73.1826 131.762 73.0626L118.741 67.3309C118.693 67.3098 118.639 67.3439 118.637 67.3962C118.638 67.3298 118.598 67.2696 118.537 67.2429L110.609 63.7715C110.507 63.7267 110.395 63.7074 110.284 63.7152L99.0498 64.5007C99.0497 64.5007 99.0497 64.5007 99.0497 64.5008Z" fill="white"/></mask><g mask="url(#shoppablefeedmask1_557_17550)"><rect x="42.1084" y="64.3252" width="99.4973" height="74.623" transform="rotate(-2 42.1084 64.3252)" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M90.1159 89.5619C88.4449 88.8801 86.6928 87.8358 85.0752 87.3778C85.6909 88.7134 86.221 90.1407 86.7672 91.5507C85.8032 92.3293 84.6669 92.9472 83.584 93.6151C84.5566 94.5116 85.907 95.0031 86.9961 95.7747C86.1829 96.853 84.5514 98.0011 84.1777 98.9657C85.9293 98.6838 87.9668 98.2548 89.6011 98.1252C90.0239 99.9205 90.2786 101.896 90.848 103.534C91.5332 101.316 92.1429 99.0275 92.9782 96.9493C94.3977 97.4792 96.1277 98.1254 97.5056 98.4189C96.5002 97.1077 95.5757 95.7098 94.6756 94.2857C95.9006 93.331 97.1405 92.3903 98.3182 91.3914C96.6009 91.2935 94.824 91.2595 93.0233 91.2508C92.6728 89.406 92.6705 87.1879 92.1715 85.5023C91.5551 86.9196 90.8146 88.2213 90.1159 89.5619ZM91.4652 104.896C91.273 105.872 92.0317 106.243 91.9398 106.833C91.1955 106.605 90.6377 106.494 89.6553 106.669C89.6598 105.913 90.335 105.782 90.1619 104.86C79.5989 104.032 78.9138 85.0208 89.334 83.4854C102.363 81.5656 103.562 104.137 91.4652 104.896Z" fill="#FE544F"/><path fill-rule="evenodd" clip-rule="evenodd" d="M92.1716 85.5022C92.6706 87.1877 92.6729 89.4059 93.0234 91.2507C94.824 91.2593 96.601 91.2933 98.3183 91.3913C97.1406 92.3901 95.9007 93.3309 94.6757 94.2855C95.5758 95.7097 96.5003 97.1075 97.5057 98.4187C96.1278 98.1252 94.3978 97.479 92.9782 96.9491C92.143 99.0274 91.5333 101.316 90.8481 103.534C90.2787 101.896 90.024 99.9204 89.6012 98.125C87.9669 98.2547 85.9294 98.6837 84.1778 98.9655C84.5514 98.001 86.183 96.8528 86.9962 95.7745C85.907 95.0029 84.5567 94.5115 83.584 93.6149C84.6669 92.9471 85.8032 92.3291 86.7673 91.5505C86.2211 90.1405 85.691 88.7132 85.0753 87.3776C86.6928 87.8357 88.445 88.8799 90.116 89.5618C90.8147 88.2211 91.5551 86.9195 92.1716 85.5022Z" fill="white"/></g></g></g></g></g><path d="M169 126C178.5 138 207.5 138 214 128" stroke="#8C8F9A" stroke-width="2" stroke-dasharray="3 3"/><path d="M212.852 124.415L218.453 123.627L218.442 128.627L212.852 124.415Z" fill="#8C8F9A"/><defs><filter id="shoppablefeedfilter0_d_557_17550" x="216.61" y="34.7618" width="167.782" height="182.523" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="2.68407" operator="erode" in="SourceAlpha" result="effect1_dropShadow_557_17550"/><feOffset dy="3.57876"/><feGaussianBlur stdDeviation="4.92079"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_17550"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_557_17550" result="shape"/></filter><filter id="shoppablefeedfilter1_dd_557_17550" x="262.428" y="49.9833" width="89.7433" height="71.0389" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dx="1.31277" dy="1.31277"/><feGaussianBlur stdDeviation="2.62553"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.13 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_17550"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="0.656383"/><feGaussianBlur stdDeviation="0.328192"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0"/><feBlend mode="normal" in2="effect1_dropShadow_557_17550" result="effect2_dropShadow_557_17550"/><feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_557_17550" result="shape"/></filter><filter id="shoppablefeedfilter2_d_557_17550" x="12.6676" y="44.5542" width="159.011" height="172.355" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feMorphology radius="2.53799" operator="erode" in="SourceAlpha" result="effect1_dropShadow_557_17550"/><feOffset dy="3.38398"/><feGaussianBlur stdDeviation="4.65298"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_17550"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_557_17550" result="shape"/></filter><filter id="shoppablefeedfilter3_dd_557_17550" x="44.7625" y="59.5677" width="94.3219" height="76.2436" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dx="1.38191" dy="1.38191"/><feGaussianBlur stdDeviation="2.76381"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.13 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_557_17550"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="0.690954"/><feGaussianBlur stdDeviation="0.345477"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0"/><feBlend mode="normal" in2="effect1_dropShadow_557_17550" result="effect2_dropShadow_557_17550"/><feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_557_17550" result="shape"/></filter><clipPath id="shoppablefeedclip0_557_17550"><rect width="17.6543" height="17.6543" fill="white" transform="translate(336.031 120.505) rotate(4)"/></clipPath><clipPath id="shoppablefeedclip1_557_17550"><rect x="19.4355" y="57.3804" width="135.359" height="149.741" rx="1.66935" transform="rotate(-4 19.4355 57.3804)" fill="white"/></clipPath><clipPath id="shoppablefeedclip2_557_17550"><rect width="136" height="85" fill="white" transform="translate(18.9092 56.9391) rotate(-4)"/></clipPath></defs></svg>',
							'popupContentBtn' => '<div class="sbi-fb-extpp-lite-btn">' . self::builder_svg_icons()['tag'] . __( 'Instagram Pro users get 50% OFF', 'instagram-feed' ) . '</div>',
							'bullets'         => array(
								'heading' => __( 'And get much more!', 'instagram-feed' ),
								'content' => array(
									__( 'Display Hashtag & Tagged feeds', 'instagram-feed' ),
									__( 'Powerful visual moderation', 'instagram-feed' ),
									__( 'Comments and Likes', 'instagram-feed' ),
									__( 'Highlight specific posts', 'instagram-feed' ),
									__( 'Multiple layout options', 'instagram-feed' ),
									__( 'Popup photo/video lightbox', 'instagram-feed' ),
									__( 'Instagram Stories', 'instagram-feed' ),
									__( 'Shoppable feeds', 'instagram-feed' ),
									__( 'Pro support', 'instagram-feed' ),
									__( 'Post captions', 'instagram-feed' ),
									__( 'Combine multiple feed types', 'instagram-feed' ),
									__( '30 day money back guarantee', 'instagram-feed' ),
								)
							),
							'buyUrl'          => sprintf( 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=shoppable' )
						),
					),
					'personalAccountScreen' => self::personal_account_screen_text(),

				);

				if ( $newly_retrieved_source_connection_data ) {
					$sbi_builder['newSourceData'] = $newly_retrieved_source_connection_data;
				}

				if ( isset( $_GET['manualsource'] ) && $_GET['manualsource'] == true ) {
					$sbi_builder['manualSourcePopupInit'] = true;
				}

				$maybe_feed_customizer_data = SBI_Feed_Saver_Manager::maybe_feed_customizer_data();

				if ( $maybe_feed_customizer_data ) {
					sb_instagram_scripts_enqueue( true );
					$sbi_builder['customizerFeedData']          = $maybe_feed_customizer_data;
					$sbi_builder['customizerSidebarBuilder']    = \InstagramFeed\Builder\Tabs\SBI_Builder_Customizer_Tab::get_customizer_tabs();
					$sbi_builder['wordpressPageLists']          = $this->get_wp_pages();
					$sbi_builder['instagram_feed_dismiss_lite'] = get_transient( 'instagram_feed_dismiss_lite' );

					if ( ! isset( $_GET['feed_id'] ) || $_GET['feed_id'] === 'legacy' ) {
						$feed_id         = 'legacy';
						$customizer_atts = array(
							'feed'       => 'legacy',
							'customizer' => true
						);
					} elseif ( intval( $_GET['feed_id'] ) > 0 ) {
						$feed_id         = intval( $_GET['feed_id'] );
						$customizer_atts = array(
							'feed'       => $feed_id,
							'customizer' => true
						);
					}

					if ( ! empty( $feed_id ) ) {
						$settings_preview = self::add_customizer_att( $customizer_atts );
						if ( $feed_id === 'legacy' ) {
							$preview_settings               = \SB_Instagram_Settings::get_legacy_feed_settings();
							$preview_settings['customizer'] = true;
							$sbi_builder['feedInitOutput']  = htmlspecialchars( display_instagram( $customizer_atts, $preview_settings ) );
						} else {
							$sbi_builder['feedInitOutput'] = htmlspecialchars( display_instagram( $settings_preview, true ) );
						}
					}

					// Date
					global $wp_locale;
					wp_enqueue_script(
						'sbi-date_i18n',
						SBI_PLUGIN_URL . 'admin/builder/assets/js/date_i18n.js',
						null,
						SBIVER,
						true
					);

					$monthNames      = array_map(
						array( &$wp_locale, 'get_month' ),
						range( 1, 12 )
					);
					$monthNamesShort = array_map(
						array( &$wp_locale, 'get_month_abbrev' ),
						$monthNames
					);
					$dayNames        = array_map(
						array( &$wp_locale, 'get_weekday' ),
						range( 0, 6 )
					);
					$dayNamesShort   = array_map(
						array( &$wp_locale, 'get_weekday_abbrev' ),
						$dayNames
					);
					wp_localize_script(
						'sbi-date_i18n',
						'DATE_I18N',
						array(
							'month_names'       => $monthNames,
							'month_names_short' => $monthNamesShort,
							'day_names'         => $dayNames,
							'day_names_short'   => $dayNamesShort
						)
					);
				}

				wp_enqueue_style(
					'sbi-builder-style',
					SBI_PLUGIN_URL . 'admin/builder/assets/css/builder.css',
					false,
					SBIVER
				);

				self::global_enqueue_ressources_scripts();

				wp_enqueue_script(
					'sbi-builder-app',
					SBI_PLUGIN_URL . 'admin/builder/assets/js/builder.js',
					null,
					SBIVER,
					true
				);
				// Customize screens
				$sbi_builder['customizeScreens'] = $this->get_customize_screens_text();
				wp_localize_script(
					'sbi-builder-app',
					'sbi_builder',
					$sbi_builder
				);
				wp_enqueue_media();
			endif;
		endif;
	}

	/**
	 * Get WP Pages List
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_wp_pages() {
		$pagesList   = get_pages();
		$pagesResult = array();
		if ( is_array( $pagesList ) ) {
			foreach ( $pagesList as $page ) {
				array_push(
					$pagesResult,
					array(
						'id'    => $page->ID,
						'title' => $page->post_title
					)
				);
			}
		}
		return $pagesResult;
	}


	/**
	 * Global JS + CSS Files
	 *
	 * Shared JS + CSS ressources for the admin panel
	 *
	 * @since 6.0
	 */
	public static function global_enqueue_ressources_scripts( $is_settings = false ) {
		wp_enqueue_style(
			'feed-global-style',
			SBI_PLUGIN_URL . 'admin/builder/assets/css/global.css',
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
			'feed-colorpicker-vue',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/vue-color.min.js',
			null,
			SBIVER,
			true
		);

		wp_enqueue_script(
			'feed-builder-ressources',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/ressources.js',
			null,
			SBIVER,
			true
		);

		wp_enqueue_script(
			'sb-dialog-box',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/confirm-dialog.js',
			null,
			SBIVER,
			true
		);

		wp_enqueue_script(
			'install-plugin-popup',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/install-plugin-popup.js',
			null,
			SBIVER,
			true
		);

		wp_enqueue_script(
			'sb-add-source',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/add-source.js',
			null,
			SBIVER,
			true
		);

		$newly_retrieved_source_connection_data = SBI_Source::maybe_source_connection_data();
		$sbi_source                             = array(
			'sources'              => self::get_source_list(),
			'sourceConnectionURLs' => SBI_Source::get_connection_urls( $is_settings ),
			'nonce'                => wp_create_nonce( 'sbi-admin' ),
		);
		if ( $newly_retrieved_source_connection_data ) {
			$sbi_source['newSourceData'] = $newly_retrieved_source_connection_data;
		}

		if ( isset( $_GET['manualsource'] ) && $_GET['manualsource'] == true ) {
			$sbi_source['manualSourcePopupInit'] = true;
		}

		wp_localize_script(
			'sb-add-source',
			'sbi_source',
			$sbi_source
		);

		wp_enqueue_script(
			'sb-personal-account',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/personal-account.js',
			null,
			SBIVER,
			true
		);

		$sbi_personal_account   = array(
			'personalAccountScreen' => self::personal_account_screen_text(),
			'nonce'                => wp_create_nonce( 'sbi-admin' ),
			'ajaxHandler'         => admin_url( 'admin-ajax.php' ),
		);

		wp_localize_script(
			'sb-personal-account',
			'sbi_personal_account',
			$sbi_personal_account
		);
	}

	/**
	 * Get Generic text
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_generic_text() {
		$icons = self::builder_svg_icons();
		return array(
			'done'                              => __( 'Done', 'instagram-feed' ),
			'title'                             => __( 'Settings', 'instagram-feed' ),
			'dashboard'                         => __( 'Dashboard', 'instagram-feed' ),
			'addNew'                            => __( 'Add New', 'instagram-feed' ),
			'addSource'                         => __( 'Add Source', 'instagram-feed' ),
			'addAnotherSource'                  => __( 'Add another Source', 'instagram-feed' ),
			'addSourceType'                     => __( 'Add Another Source Type', 'instagram-feed' ),
			'previous'                          => __( 'Previous', 'instagram-feed' ),
			'next'                              => __( 'Next', 'instagram-feed' ),
			'finish'                            => __( 'Finish', 'instagram-feed' ),
			'new'                               => __( 'New', 'instagram-feed' ),
			'update'                            => __( 'Update', 'instagram-feed' ),
			'upgrade'                           => __( 'Try the Pro Demo', 'instagram-feed' ),
			'settings'                          => __( 'Settings', 'instagram-feed' ),
			'back'                              => __( 'Back', 'instagram-feed' ),
			'backAllFeeds'                      => __( 'Back to all feeds', 'instagram-feed' ),
			'createFeed'                        => __( 'Create Feed', 'instagram-feed' ),
			'add'                               => __( 'Add', 'instagram-feed' ),
			'change'                            => __( 'Change', 'instagram-feed' ),
			'getExtention'                      => __( 'Get Extension', 'instagram-feed' ),
			'viewDemo'                          => __( 'View Demo', 'instagram-feed' ),
			'includes'                          => __( 'Includes', 'instagram-feed' ),
			'photos'                            => __( 'Photos', 'instagram-feed' ),
			'photo'                             => __( 'Photo', 'instagram-feed' ),
			'apply'                             => __( 'Apply', 'instagram-feed' ),
			'copy'                              => __( 'Copy', 'instagram-feed' ),
			'edit'                              => __( 'Edit', 'instagram-feed' ),
			'duplicate'                         => __( 'Duplicate', 'instagram-feed' ),
			'delete'                            => __( 'Delete', 'instagram-feed' ),
			'remove'                            => __( 'Remove', 'instagram-feed' ),
			'removeSource'                      => __( 'Remove Source', 'instagram-feed' ),
			'shortcode'                         => __( 'Shortcode', 'instagram-feed' ),
			'clickViewInstances'                => __( 'Click to view Instances', 'instagram-feed' ),
			'usedIn'                            => __( 'Used in', 'instagram-feed' ),
			'place'                             => __( 'place', 'instagram-feed' ),
			'places'                            => __( 'places', 'instagram-feed' ),
			'item'                              => __( 'Item', 'instagram-feed' ),
			'items'                             => __( 'Items', 'instagram-feed' ),
			'learnMore'                         => __( 'Learn More', 'instagram-feed' ),
			'location'                          => __( 'Location', 'instagram-feed' ),
			'page'                              => __( 'Page', 'instagram-feed' ),
			'copiedClipboard'                   => __( 'Copied to Clipboard', 'instagram-feed' ),
			'feedImported'                      => __( 'Feed imported successfully', 'instagram-feed' ),
			'failedToImportFeed'                => __( 'Failed to import feed', 'instagram-feed' ),
			'timeline'                          => __( 'Timeline', 'instagram-feed' ),
			'help'                              => __( 'Help', 'instagram-feed' ),
			'admin'                             => __( 'Admin', 'instagram-feed' ),
			'member'                            => __( 'Member', 'instagram-feed' ),
			'reset'                             => __( 'Reset', 'instagram-feed' ),
			'preview'                           => __( 'Preview', 'instagram-feed' ),
			'name'                              => __( 'Name', 'instagram-feed' ),
			'id'                                => __( 'ID', 'instagram-feed' ),
			'token'                             => __( 'Token', 'instagram-feed' ),
			'confirm'                           => __( 'Confirm', 'instagram-feed' ),
			'cancel'                            => __( 'Cancel', 'instagram-feed' ),
			'clear'                             => __( 'Clear', 'instagram-feed' ),
			'clearFeedCache'                    => __( 'Clear Feed Cache', 'instagram-feed' ),
			'saveSettings'                      => __( 'Save Changes', 'instagram-feed' ),
			'feedName'                          => __( 'Feed Name', 'instagram-feed' ),
			'shortcodeText'                     => __( 'Shortcode', 'instagram-feed' ),
			'general'                           => __( 'General', 'instagram-feed' ),
			'feeds'                             => __( 'Feeds', 'instagram-feed' ),
			'translation'                       => __( 'Translation', 'instagram-feed' ),
			'advanced'                          => __( 'Advanced', 'instagram-feed' ),
			'error'                             => __( 'Error:', 'instagram-feed' ),
			'errorNotice'                       => __( 'There was an error when trying to connect to Instagram.', 'instagram-feed' ),
			'errorDirections'                   => '<a href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on How to Resolve This Issue', 'instagram-feed' ) . '</a>',
			'errorSource'                       => __( 'Source Invalid', 'instagram-feed' ),
			'errorEncryption'                   => __( 'Encryption Error', 'instagram-feed' ),
			'invalid'                           => __( 'Invalid', 'instagram-feed' ),
			'reconnect'                         => __( 'Reconnect', 'instagram-feed' ),
			'feed'                              => __( 'feed', 'instagram-feed' ),
			'sourceNotUsedYet'                  => __( 'Source is not used yet', 'instagram-feed' ),
			'addImage'                          => __( 'Add Image', 'instagram-feed' ),
			'businessRequired'                  => __( 'Business Account required', 'instagram-feed' ),
			'selectedPost'                      => __( 'Selected Post', 'instagram-feed' ),
			'productLink'                       => __( 'Product Link', 'instagram-feed' ),
			'enterProductLink'                  => __( 'Add your product URL here', 'instagram-feed' ),
			'editSources'                       => __( 'Edit Sources', 'instagram-feed' ),
			'moderateFeed'                      => __( 'Moderate your feed', 'instagram-feed' ),
			'moderateFeedSaveExit'              => __( 'Save and Exit', 'instagram-feed' ),
			'moderationMode'                    => __( 'Moderation Mode', 'instagram-feed' ),
			'moderationModeEnterPostId'         => __( 'Or Enter Post IDs to hide manually', 'instagram-feed' ),
			'moderationModeTextareaPlaceholder' => __( 'Add words here to hide any posts containing these words', 'instagram-feed' ),
			'filtersAndModeration'              => __( 'Filters & Moderation', 'instagram-feed' ),
			'topRated'                          => __( 'Top Rated', 'instagram-feed' ),
			'mostRecent'                        => __( 'Most recent', 'instagram-feed' ),
			'moderationModePreview'             => __( 'Moderation Mode Preview', 'instagram-feed' ),

			'notification'                      => array(
				'feedSaved'             => array(
					'type' => 'success',
					'text' => __( 'Feed saved successfully', 'instagram-feed' )
				),
				'feedSavedError'        => array(
					'type' => 'error',
					'text' => __( 'Error saving Feed', 'instagram-feed' )
				),
				'previewUpdated'        => array(
					'type' => 'success',
					'text' => __( 'Preview updated successfully', 'instagram-feed' )
				),
				'carouselLayoutUpdated' => array(
					'type' => 'success',
					'text' => __( 'Carousel updated successfully', 'instagram-feed' )
				),
				'unkownError'           => array(
					'type' => 'error',
					'text' => __( 'Unknown error occurred', 'instagram-feed' )
				),
				'cacheCleared'          => array(
					'type' => 'success',
					'text' => __( 'Feed cache cleared', 'instagram-feed' )
				),
				'selectSourceError'     => array(
					'type' => 'error',
					'text' => __( 'Please select a source for your feed', 'instagram-feed' )
				),
				'commentCacheCleared'   => array(
					'type' => 'success',
					'text' => __( 'Comment cache cleared', 'instagram-feed' )
				),
				'personalAccountUpdated'   => array(
					'type' => 'success',
					'text' => __( 'Personal account updated', 'instagram-feed' )
				)
			),
			'install'                           => __( 'Install', 'instagram-feed' ),
			'installed'                         => __( 'Installed', 'instagram-feed' ),
			'activate'                          => __( 'Activate', 'instagram-feed' ),
			'installedAndActivated'             => __( 'Installed & Activated', 'instagram-feed' ),
			'free'                              => __( 'Free', 'instagram-feed' ),
			'invalidLicenseKey'                 => __( 'Invalid license key', 'instagram-feed' ),
			'licenseActivated'                  => __( 'License activated', 'instagram-feed' ),
			'licenseDeactivated'                => __( 'License Deactivated', 'instagram-feed' ),
			'carouselLayoutUpdated'             => array(
				'type' => 'success',
				'text' => __( 'Carousel Layout updated', 'instagram-feed' )
			),
			'getMoreFeatures'                   => __( 'Get more features with Instagram Feed Pro', 'instagram-feed' ),
			'liteFeedUsers'                     => __( 'Lite users get 50% OFF', 'instagram-feed' ),
			'tryDemo'                           => __( 'Try Demo', 'instagram-feed' ),

			'displayImagesVideos'               => __( 'Display images and videos in posts', 'instagram-feed' ),
			'viewLikesShares'                   => __( 'View likes, shares and comments', 'instagram-feed' ),
			'allFeedTypes'                      => __( 'All Feed Types: Photos, Albums, Events and more', 'instagram-feed' ),
			'abilityToLoad'                     => __( 'Ability to “Load More” posts', 'instagram-feed' ),

			'ctaHashtag'                        => __( 'Display Hashtag Feeds', 'instagram-feed' ),
			'ctaLayout'                         => __( 'Carousel, Masonry, & Highlight layouts', 'instagram-feed' ),
			'ctaPopups'                         => __( 'View posts in a pop-up lightbox', 'instagram-feed' ),
			'ctaFilter'                         => __( 'Powerful post filtering and moderation', 'instagram-feed' ),

			'andMuchMore'                       => __( 'And Much More!', 'instagram-feed' ),
			'sbiFreeCTAFeatures'                => array(
				__( 'Create shoppable feeds', 'instagram-feed' ),
				__( 'Combine multiple feed types', 'instagram-feed' ),
				__( 'Display likes, captions & comments', 'instagram-feed' ),
				__( 'Instagram Stories', 'instagram-feed' ),
				__( 'Play videos in your feed', 'instagram-feed' ),
				__( 'Highlight specific posts', 'instagram-feed' ),
				__( 'Display tagged posts', 'instagram-feed' ),
				__( '30 day money back guarantee', 'instagram-feed' ),
				__( 'Fast, friendly, and effective support', 'instagram-feed' ),
			),
			'ctaShowFeatures'                   => __( 'Show Features', 'instagram-feed' ),
			'ctaHideFeatures'                   => __( 'Hide Features', 'instagram-feed' ),
			'upgradeToPro'                      => __( 'Upgrade to Pro', 'instagram-feed' ),
			'redirectLoading'                   => array(
				'heading'     => __( 'Redirecting to connect.smashballoon.com', 'instagram-feed' ),
				'description' => __( 'You will be redirected to our app so you can connect your account in 5 seconds', 'instagram-feed' ),
			),
			'addAccountInfo' => __( 'Add Avatar and Bio', 'instagram-feed' ),
			'updateAccountInfo' => __( 'Update Avatar and Bio', 'instagram-feed' ),
			'personalAccountUpdated' => __( 'Personal account updated', 'instagram-feed' ),
		);
	}

	/**
	 * Select Source Screen Text
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public static function select_source_screen_text() {
		return array(
			'mainHeading'               => __( 'Select one or more sources', 'instagram-feed' ),
			'description'               => __( 'Sources are Instagram accounts your feed will display content from', 'instagram-feed' ),
			'emptySourceDescription'    => __( 'Looks like you have not added any source.<br/>Use “Add Source” to add a new one.', 'instagram-feed' ),
			'mainHashtagHeading'        => __( 'Enter Public Hashtags', 'instagram-feed' ),
			'hashtagDescription'        => __( 'Add one or more hashtag separated by comma', 'instagram-feed' ),
			'hashtagGetBy'              => __( 'Fetch posts that are', 'instagram-feed' ),

			'sourcesListPopup'          => array(
				'user'   => array(
					'mainHeading' => __( 'Add a source for Timeline', 'instagram-feed' ),
					'description' => __( 'Select or add an account you want to display the timeline for', 'instagram-feed' ),
				),
				'tagged' => array(
					'mainHeading' => __( 'Add a source for Mentions', 'instagram-feed' ),
					'description' => __( 'Select or add an account you want to display the mentions for', 'instagram-feed' ),
				)
			),

			'perosnalAccountToolTipTxt' => array(
				__(
					'Due to changes in Instagram’s new API, we can no<br/>
					longer get mentions for personal accounts. To<br/>
					enable this for your account, you will need to convert it to<br/>
					a Business account. Learn More',
					'instagram-feed'
				),
			),
			'groupsToolTip'             => array(
				__( 'Due to Facebook limitations, it\'s not possible to display photo feeds from a Group, only a Page.', 'instagram-feed' )
			),
			'updateHeading'             => __( 'Update Source', 'instagram-feed' ),
			'updateDescription'         => __( 'Select a source from your connected Facebook Pages and Groups. Or, use "Add New" to connect a new one.', 'instagram-feed' ),
			'updateFooter'              => __( 'Add multiple Facebook Pages or Groups to a feed with our Multifeed extension', 'instagram-feed' ),
			'noSources'                 => __( 'Please add a source in order to display a feed. Go to the "Settings" tab -> "Sources" section -> Click "Add New" to connect a source.', 'instagram-feed' ),

			'multipleTypes'             => array(
				'user'    => array(
					'heading'     => __( 'User Timeline', 'instagram-feed' ),
					'icon'        => 'user',
					'description' => __( 'Connect an account to show posts for it.', 'instagram-feed' ),
					'actionType'  => 'addSource'
				),
				'hashtag' => array(
					'heading'          => __( 'Hashtag', 'instagram-feed' ),
					'icon'             => 'hashtag',
					'description'      => __( 'Add one or more hashtag separated by comma.', 'instagram-feed' ),
					'businessRequired' => true,
					'actionType'       => 'inputHashtags'
				),
				'tagged'  => array(
					'heading'          => __( 'Tagged', 'instagram-feed' ),
					'icon'             => 'mention',
					'description'      => __( 'Connect an account to show tagged posts. This does not give us any permission to manage your Instagram account.', 'instagram-feed' ),
					'businessRequired' => true,
					'actionType'       => 'addSource'
				)
			),

			'modal'                     => array(
				'addNew'                     => __( 'Connect your Instagram Account', 'instagram-feed' ),
				'selectSourceType'           => __( 'Select Account Type', 'instagram-feed' ),
				'connectAccount'             => __( 'Connect an Instagram Account', 'instagram-feed' ),
				'connectAccountDescription'  => __( 'This does not give us permission to manage your Instagram account, it simply allows the plugin to see a list of them and retrieve their public content from the API.', 'instagram-feed' ),
				'connect'                    => __( 'Connect', 'instagram-feed' ),
				'enterEventToken'            => __( 'Enter Events Access Token', 'instagram-feed' ),
				'enterEventTokenDescription' => sprintf( __( 'Due to restrictions by Facebook, you need to create a Facebook app and then paste that app Access Token here. We have a guide to help you with just that, which you can read %1$shere%2$s', 'instagram-feed' ), '<a href="https://smashballoon.com/instagram-feed/page-token/" target="_blank" rel="noopener">', '</a>' ),
				'alreadyHave'                => __( 'Already have a API Token and Access Key for your account?', 'instagram-feed' ),
				'addManuallyLink'            => __( 'Add Account Manually', 'instagram-feed' ),
				'selectAccount'              => __( 'Select an Instagram Account', 'instagram-feed' ),
				'showing'                    => __( 'Showing', 'instagram-feed' ),
				'facebook'                   => __( 'Facebook', 'instagram-feed' ),
				'businesses'                 => __( 'Businesses', 'instagram-feed' ),
				'groups'                     => __( 'Groups', 'instagram-feed' ),
				'connectedTo'                => __( 'connected to', 'instagram-feed' ),
				'addManually'                => __( 'Add a Source Manually', 'instagram-feed' ),
				'addSource'                  => __( 'Add Source', 'instagram-feed' ),
				'sourceType'                 => __( 'Source Type', 'instagram-feed' ),
				'accountID'                  => __( 'Instagram Account ID', 'instagram-feed' ),
				'fAccountID'                 => __( 'Instagram Account ID', 'instagram-feed' ),
				'eventAccessToken'           => __( 'Event Access Token', 'instagram-feed' ),
				'enterID'                    => __( 'Enter ID', 'instagram-feed' ),
				'accessToken'                => __( 'Instagram Access Token', 'instagram-feed' ),
				'enterToken'                 => __( 'Enter Token', 'instagram-feed' ),
				'addApp'                     => __( 'Add Instagram App to your group', 'instagram-feed' ),
				'addAppDetails'              => __( 'To get posts from your group, Instagram requires the "Smash Balloon Plugin" app to be added in your group settings. Just follow the directions here:', 'instagram-feed' ),
				'addAppSteps'                => array(
					__( 'Go to your group settings page by ', 'instagram-feed' ),
					sprintf( __( 'Search for "Smash Balloon" and select our app %1$s(see screenshot)%2$s', 'instagram-feed' ), '<a href="JavaScript:void(0);" id="sbi-group-app-tooltip">', '<img class="sbi-group-app-screenshot sb-tr-1" src="' . trailingslashit( SBI_PLUGIN_URL ) . 'admin/assets/img/group-app.png" alt="Thumbnail Layout"></a>' ),
					__( 'Click "Add" and you are done.', 'instagram-feed' )
				),
				'alreadyExists'              => __( 'Account already exists', 'instagram-feed' ),
				'alreadyExistsExplanation'   => __( 'The Instagram account you added is already connected as a “Business” account. Would you like to replace it with a “Personal“ account? (Note: Personal accounts cannot be used to display Mentions or Hashtag feeds.)', 'instagram-feed' ),
				'replaceWithPersonal'        => __( 'Replace with Personal', 'instagram-feed' ),
				'notAdmin'                   => __( 'For groups you are not an administrator of', 'instagram-feed' ),
				'disclaimerMentions'         => __( 'Due to Instagram’s limitations, you need to connect a business account to display a Mentions timeline', 'instagram-feed' ),
				'disclaimerHashtag'          => __( 'Due to Instagram’s limitations, you need to connect a business account to display a Hashtag feed', 'instagram-feed' ),
				'notSureToolTip'             => __( 'Select "Personal" if displaying a regular feed of posts, as this can display feeds from either a Personal or Business account. For displaying a Hashtag or Tagged feed, you must have an Instagram Business account. If needed, you can convert a Personal account into a Business account by following the directions {link}here{link}.', 'instagram-feed' )
			),
			'footer'                    => array(
				'heading' => __( 'Add feeds for popular social platforms with <span>our other plugins</span>', 'instagram-feed' ),
			),
			'personal'                  => __( 'Personal', 'instagram-feed' ),
			'business'                  => __( 'Business', 'instagram-feed' ),
			'notSure'                   => __( "I'm not sure", 'instagram-feed' ),
		);
	}

	/**
	 * For Other Platforms listed on the footer widget
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function builder_svg_icons() {
		$builder_svg_icons = array(
			'youtube'               => '<svg viewBox="0 0 14 11" fill="none"><path d="M5.66683 7.5L9.12683 5.5L5.66683 3.5V7.5ZM13.3735 2.28C13.4602 2.59334 13.5202 3.01334 13.5602 3.54667C13.6068 4.08 13.6268 4.54 13.6268 4.94L13.6668 5.5C13.6668 6.96 13.5602 8.03334 13.3735 8.72C13.2068 9.32 12.8202 9.70667 12.2202 9.87334C11.9068 9.96 11.3335 10.02 10.4535 10.06C9.58683 10.1067 8.7935 10.1267 8.06016 10.1267L7.00016 10.1667C4.20683 10.1667 2.46683 10.06 1.78016 9.87334C1.18016 9.70667 0.793496 9.32 0.626829 8.72C0.540163 8.40667 0.480163 7.98667 0.440163 7.45334C0.393496 6.92 0.373496 6.46 0.373496 6.06L0.333496 5.5C0.333496 4.04 0.440163 2.96667 0.626829 2.28C0.793496 1.68 1.18016 1.29334 1.78016 1.12667C2.0935 1.04 2.66683 0.980002 3.54683 0.940002C4.4135 0.893336 5.20683 0.873336 5.94016 0.873336L7.00016 0.833336C9.7935 0.833336 11.5335 0.940003 12.2202 1.12667C12.8202 1.29334 13.2068 1.68 13.3735 2.28Z"/></svg>',
			'twitter'               => '<svg viewBox="0 0 14 12" fill="none"><path d="M13.9735 1.50001C13.4602 1.73334 12.9069 1.88667 12.3335 1.96001C12.9202 1.60667 13.3735 1.04667 13.5869 0.373338C13.0335 0.706672 12.4202 0.940005 11.7735 1.07334C11.2469 0.500005 10.5069 0.166672 9.66686 0.166672C8.10019 0.166672 6.82019 1.44667 6.82019 3.02667C6.82019 3.25334 6.84686 3.47334 6.89352 3.68001C4.52019 3.56001 2.40686 2.42 1.00019 0.693338C0.753522 1.11334 0.613522 1.60667 0.613522 2.12667C0.613522 3.12 1.11352 4 1.88686 4.5C1.41352 4.5 0.973522 4.36667 0.586856 4.16667V4.18667C0.586856 5.57334 1.57352 6.73334 2.88019 6.99334C2.46067 7.10814 2.02025 7.12412 1.59352 7.04C1.77459 7.60832 2.12921 8.10561 2.60753 8.46196C3.08585 8.81831 3.66382 9.0158 4.26019 9.02667C3.24928 9.82696 1.99619 10.2595 0.706855 10.2533C0.480189 10.2533 0.253522 10.24 0.0268555 10.2133C1.29352 11.0267 2.80019 11.5 4.41352 11.5C9.66686 11.5 12.5535 7.14 12.5535 3.36C12.5535 3.23334 12.5535 3.11334 12.5469 2.98667C13.1069 2.58667 13.5869 2.08 13.9735 1.50001Z"/></svg>',
			'instagram'             => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 4.50781C6.5 4.50781 4.50781 6.53906 4.50781 9C4.50781 11.5 6.5 13.4922 9 13.4922C11.4609 13.4922 13.4922 11.5 13.4922 9C13.4922 6.53906 11.4609 4.50781 9 4.50781ZM9 11.9297C7.39844 11.9297 6.07031 10.6406 6.07031 9C6.07031 7.39844 7.35938 6.10938 9 6.10938C10.6016 6.10938 11.8906 7.39844 11.8906 9C11.8906 10.6406 10.6016 11.9297 9 11.9297ZM14.7031 4.35156C14.7031 3.76562 14.2344 3.29688 13.6484 3.29688C13.0625 3.29688 12.5938 3.76562 12.5938 4.35156C12.5938 4.9375 13.0625 5.40625 13.6484 5.40625C14.2344 5.40625 14.7031 4.9375 14.7031 4.35156ZM17.6719 5.40625C17.5938 4 17.2812 2.75 16.2656 1.73438C15.25 0.71875 14 0.40625 12.5938 0.328125C11.1484 0.25 6.8125 0.25 5.36719 0.328125C3.96094 0.40625 2.75 0.71875 1.69531 1.73438C0.679688 2.75 0.367188 4 0.289062 5.40625C0.210938 6.85156 0.210938 11.1875 0.289062 12.6328C0.367188 16.0391 0.679688 15.25 1.69531 16.3047C2.75 17.3203 3.96094 17.6328 5.36719 17.7109C6.8125 17.7891 11.1484 17.7891 12.5938 17.7109C14 17.6328 15.25 17.3203 16.2656 16.3047C17.2812 15.25 17.5938 16.0391 17.6719 12.6328C17.75 11.1875 17.75 6.85156 17.6719 5.40625ZM15.7969 14.1562C15.5234 14.9375 14.8984 15.5234 14.1562 15.8359C12.9844 16.3047 10.25 16.1875 9 16.1875C7.71094 16.1875 4.97656 16.3047 3.84375 15.8359C3.0625 15.5234 2.47656 14.9375 2.16406 14.1562C1.69531 13.0234 1.8125 10.2891 1.8125 9C1.8125 7.75 1.69531 5.01562 2.16406 3.84375C2.47656 3.10156 3.0625 2.51562 3.84375 2.20312C4.97656 1.73438 7.71094 1.85156 9 1.85156C10.25 1.85156 12.9844 1.73438 14.1562 2.20312C14.8984 2.47656 15.4844 3.10156 15.7969 3.84375C16.2656 5.01562 16.1484 7.75 16.1484 9C16.1484 10.2891 16.2656 13.0234 15.7969 14.1562Z" fill="url(#paint0_linear)"/><defs><linearGradient id="paint0_linear" x1="6.46484" y1="33.7383" x2="43.3242" y2="-3.88672" gradientUnits="userSpaceOnUse"><stop stop-color="white"/><stop offset="0.147864" stop-color="#F6640E"/><stop offset="0.443974" stop-color="#BA03A7"/><stop offset="0.733337" stop-color="#6A01B9"/><stop offset="1" stop-color="#6B01B9"/></linearGradient></defs></svg>',
			'facebook'              => '<svg viewBox="0 0 14 15"><path d="M7.00016 0.860001C3.3335 0.860001 0.333496 3.85333 0.333496 7.54C0.333496 10.8733 2.7735 13.64 5.96016 14.14V9.47333H4.26683V7.54H5.96016V6.06667C5.96016 4.39333 6.9535 3.47333 8.48016 3.47333C9.20683 3.47333 9.96683 3.6 9.96683 3.6V5.24667H9.12683C8.30016 5.24667 8.04016 5.76 8.04016 6.28667V7.54H9.8935L9.5935 9.47333H8.04016V14.14C9.61112 13.8919 11.0416 13.0903 12.0734 11.88C13.1053 10.6697 13.6704 9.13043 13.6668 7.54C13.6668 3.85333 10.6668 0.860001 7.00016 0.860001Z"/></svg>',
			'smash'                 => '<svg height="18" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M27.2235 16.8291C27.2235 7.53469 21.1311 0 13.6131 0C6.09513 0 0 7.53469 0 16.8291C0 25.7393 5.5828 33.0095 12.6525 33.6193L11.9007 36L16.6147 35.599L14.9608 33.5775C21.8439 32.7422 27.2235 25.5639 27.2235 16.8291Z" fill="#FE544F"/><path fill-rule="evenodd" clip-rule="evenodd" d="M16.8586 5.91699L17.5137 12.6756L24.3006 12.8705L19.3911 17.4354L23.2687 23.044L16.7362 21.816L14.7557 28.3487L11.7488 22.4987L5.67719 25.2808L8.01283 19.0094L2.09131 16.0227L8.43013 13.9841L6.68099 7.73959L12.678 11.1585L16.8586 5.91699Z" fill="white"/></svg>',
			'tag'                   => '<svg viewBox="0 0 18 18"><path d="M16.841 8.65033L9.34102 1.15033C9.02853 0.840392 8.60614 0.666642 8.16602 0.666993H2.33268C1.89066 0.666993 1.46673 0.842587 1.15417 1.15515C0.841611 1.46771 0.666016 1.89163 0.666016 2.33366V8.16699C0.665842 8.38692 0.709196 8.60471 0.79358 8.8078C0.877964 9.01089 1.00171 9.19528 1.15768 9.35033L8.65768 16.8503C8.97017 17.1603 9.39256 17.334 9.83268 17.3337C10.274 17.3318 10.6966 17.155 11.0077 16.842L16.841 11.0087C17.154 10.6975 17.3308 10.275 17.3327 9.83366C17.3329 9.61373 17.2895 9.39595 17.2051 9.19285C17.1207 8.98976 16.997 8.80538 16.841 8.65033ZM9.83268 15.667L2.33268 8.16699V2.33366H8.16602L15.666 9.83366L9.83268 15.667ZM4.41602 3.16699C4.66324 3.16699 4.90492 3.2403 5.11048 3.37766C5.31604 3.51501 5.47626 3.71023 5.57087 3.93864C5.66548 4.16705 5.69023 4.41838 5.642 4.66086C5.59377 4.90333 5.47472 5.12606 5.2999 5.30088C5.12508 5.47569 4.90236 5.59474 4.65988 5.64297C4.4174 5.69121 4.16607 5.66645 3.93766 5.57184C3.70925 5.47723 3.51403 5.31702 3.37668 5.11146C3.23933 4.90589 3.16602 4.66422 3.16602 4.41699C3.16602 6.08547 3.29771 3.76753 3.53213 3.53311C3.76655 3.29869 6.0845 3.16699 4.41602 3.16699Z"/></svg>',
			'copy'                  => '<svg viewBox="0 0 12 13" fill="none"><path d="M10.25 0.25H4.625C3.9375 0.25 3.375 0.8125 3.375 1.5V9C3.375 9.6875 3.9375 10.25 4.625 10.25H10.25C10.9375 10.25 11.5 9.6875 11.5 9V1.5C11.5 0.8125 10.9375 0.25 10.25 0.25ZM10.25 9H4.625V1.5H10.25V9ZM0.875 8.375V7.125H2.125V8.375H0.875ZM0.875 4.9375H2.125V6.1875H0.875V4.9375ZM5.25 11.5H6.5V12.75H5.25V11.5ZM0.875 10.5625V9.3125H2.125V10.5625H0.875ZM2.125 12.75C1.4375 12.75 0.875 12.1875 0.875 11.5H2.125V12.75ZM4.3125 12.75H3.0625V11.5H4.3125V12.75ZM7.4375 12.75V11.5H8.6875C8.6875 12.1875 8.125 12.75 7.4375 12.75ZM2.125 2.75V4H0.875C0.875 3.3125 1.4375 2.75 2.125 2.75Z"/></svg>',
			'duplicate'             => '<svg viewBox="0 0 10 12" fill="none"><path d="M6.99997 0.5H0.999969C0.449969 0.5 -3.05176e-05 0.95 -3.05176e-05 1.5V8.5H0.999969V1.5H6.99997V0.5ZM8.49997 2.5H2.99997C2.44997 2.5 1.99997 2.95 1.99997 3.5V10.5C1.99997 11.05 2.44997 11.5 2.99997 11.5H8.49997C9.04997 11.5 9.49997 11.05 9.49997 10.5V3.5C9.49997 2.95 9.04997 2.5 8.49997 2.5ZM8.49997 10.5H2.99997V3.5H8.49997V10.5Z"/></svg>',
			'edit'                  => '<svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.25 9.06241V11.2499H2.4375L8.88917 4.79824L6.70167 2.61074L0.25 9.06241ZM10.9892 2.69824L8.80167 0.510742L7.32583 1.99241L9.51333 4.17991L10.9892 2.69824Z" fill="currentColor"/></svg>',
			'delete'                => '<svg viewBox="0 0 10 12" fill="none"><path d="M1.00001 10.6667C1.00001 11.4 1.60001 12 2.33334 12H7.66668C8.40001 12 9.00001 11.4 9.00001 10.6667V2.66667H1.00001V10.6667ZM2.33334 4H7.66668V10.6667H2.33334V4ZM7.33334 0.666667L6.66668 0H3.33334L2.66668 0.666667H0.333344V2H9.66668V0.666667H7.33334Z"/></svg>',
			'checkmark'             => '<svg width="11" height="9"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.15641 5.65271L9.72487 0.0842487L10.9623 1.32169L4.15641 8.12759L0.444097 4.41528L1.68153 3.17784L4.15641 5.65271Z"/></svg>',
			'checkmarklarge'        => '<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.08058 8.36133L16.0355 0.406383L15.8033 2.17415L6.08058 11.8969L0.777281 6.59357L2.54505 4.8258L6.08058 8.36133Z" fill="currentColor"></path></svg>',
			'information'           => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.3335 5H7.66683V3.66667H6.3335V5ZM7.00016 12.3333C4.06016 12.3333 1.66683 9.94 1.66683 7C1.66683 4.06 4.06016 1.66667 7.00016 1.66667C9.94016 1.66667 12.3335 4.06 12.3335 7C12.3335 9.94 9.94016 12.3333 7.00016 12.3333ZM7.00016 0.333332C6.12468 0.333332 5.25778 0.505771 4.44894 0.840802C3.6401 1.17583 2.90517 1.6669 2.28612 2.28595C1.03588 3.5362 0.333496 5.23189 0.333496 7C0.333496 8.76811 1.03588 10.4638 2.28612 11.714C2.90517 12.3331 3.6401 12.8242 4.44894 13.1592C5.25778 13.4942 6.12468 13.6667 7.00016 13.6667C8.76827 13.6667 10.464 12.9643 11.7142 11.714C12.9645 10.4638 13.6668 8.76811 13.6668 7C13.6668 6.12452 13.4944 5.25761 13.1594 4.44878C12.8243 3.63994 12.3333 2.90501 11.7142 2.28595C11.0952 1.6669 10.3602 1.17583 9.55139 0.840802C8.74255 0.505771 7.87564 0.333332 7.00016 0.333332ZM6.3335 10.3333H7.66683V6.33333H6.3335V10.3333Z" fill="#141B38"/></svg>',
			'cog'                   => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.99989 9.33334C6.38105 9.33334 5.78756 9.0875 5.34998 8.64992C4.91239 8.21233 4.66656 7.61884 4.66656 7C4.66656 6.38117 4.91239 5.78767 5.34998 5.35009C5.78756 4.9125 6.38105 4.66667 6.99989 4.66667C7.61873 4.66667 8.21222 4.9125 8.64981 5.35009C9.08739 5.78767 9.33323 6.38117 9.33323 7C9.33323 7.61884 9.08739 8.21233 8.64981 8.64992C8.21222 9.0875 7.61873 9.33334 6.99989 9.33334ZM11.9532 7.64667C11.9799 7.43334 11.9999 7.22 11.9999 7C11.9999 6.78 11.9799 6.56 11.9532 6.33334L13.3599 5.24667C13.4866 5.14667 13.5199 4.96667 13.4399 4.82L12.1066 2.51334C12.0266 2.36667 11.8466 2.30667 11.6999 2.36667L10.0399 3.03334C9.69323 2.77334 9.33323 2.54667 8.91323 2.38L8.66656 0.613337C8.65302 0.534815 8.61212 0.463622 8.5511 0.412371C8.49009 0.361121 8.41291 0.333123 8.33323 0.333337H5.66656C5.49989 0.333337 5.35989 0.453337 5.33323 0.613337L5.08656 2.38C4.66656 2.54667 4.30656 2.77334 3.95989 3.03334L2.29989 2.36667C2.15323 2.30667 1.97323 2.36667 1.89323 2.51334L0.559893 4.82C0.473226 4.96667 0.513226 5.14667 0.639893 5.24667L2.04656 6.33334C2.01989 6.56 1.99989 6.78 1.99989 7C1.99989 7.22 2.01989 7.43334 2.04656 7.64667L0.639893 8.75334C0.513226 8.85334 0.473226 9.03334 0.559893 9.18L1.89323 11.4867C1.97323 11.6333 2.15323 11.6867 2.29989 11.6333L3.95989 10.96C4.30656 11.2267 4.66656 11.4533 5.08656 11.62L5.33323 13.3867C5.35989 13.5467 5.49989 13.6667 5.66656 13.6667H8.33323C8.49989 13.6667 8.63989 13.5467 8.66656 13.3867L8.91323 11.62C9.33323 11.4467 9.69323 11.2267 10.0399 10.96L11.6999 11.6333C11.8466 11.6867 12.0266 11.6333 12.1066 11.4867L13.4399 9.18C13.5199 9.03334 13.4866 8.85334 13.3599 8.75334L11.9532 7.64667Z" fill="#141B38"/></svg>',
			'angleUp'               => '<svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.94 5.27325L4 2.21992L7.06 5.27325L8 4.33325L4 0.333252L0 4.33325L0.94 5.27325Z" fill="#434960"/></svg>',
			'user_check'            => '<svg viewBox="0 0 11 9"><path d="M9.55 4.25L10.25 4.955L6.985 8.25L5.25 6.5L5.95 5.795L6.985 6.835L9.55 4.25ZM4 6.5L5.5 8H0.5V7C0.5 5.895 2.29 5 4.5 5L5.445 5.055L4 6.5ZM4.5 0C5.03043 0 5.53914 0.210714 5.91421 0.585786C6.28929 0.960859 6.5 1.46957 6.5 2C6.5 2.53043 6.28929 3.03914 5.91421 3.41421C5.53914 3.78929 5.03043 4 4.5 4C3.96957 4 3.46086 3.78929 3.08579 3.41421C2.71071 3.03914 2.5 2.53043 2.5 2C2.5 1.46957 2.71071 0.960859 3.08579 0.585786C3.46086 0.210714 3.96957 0 4.5 0Z"/></svg>',
			'users'                 => '<svg viewBox="0 0 12 8"><path d="M6 0.75C6.46413 0.75 6.90925 0.934375 7.23744 1.26256C7.56563 1.59075 7.75 2.03587 7.75 2.5C7.75 2.96413 7.56563 3.40925 7.23744 3.73744C6.90925 6.06563 6.46413 4.25 6 4.25C5.53587 4.25 5.09075 6.06563 4.76256 3.73744C4.43437 3.40925 4.25 2.96413 4.25 2.5C4.25 2.03587 4.43437 1.59075 4.76256 1.26256C5.09075 0.934375 5.53587 0.75 6 0.75ZM2.5 2C2.78 2 3.04 2.075 3.265 2.21C3.19 2.925 3.4 3.635 3.83 4.19C3.58 4.67 3.08 5 2.5 5C2.10218 5 1.72064 4.84196 1.43934 4.56066C1.15804 4.27936 1 3.89782 1 3.5C1 3.10218 1.15804 2.72064 1.43934 2.43934C1.72064 2.15804 2.10218 2 2.5 2ZM9.5 2C9.89782 2 10.2794 2.15804 10.5607 2.43934C10.842 2.72064 11 3.10218 11 3.5C11 3.89782 10.842 4.27936 10.5607 4.56066C10.2794 4.84196 9.89782 5 9.5 5C8.92 5 8.42 4.67 8.17 4.19C8.60594 3.62721 8.80828 2.9181 8.735 2.21C8.96 2.075 9.22 2 9.5 2ZM2.75 7.125C2.75 6.09 4.205 5.25 6 5.25C7.795 5.25 9.25 6.09 9.25 7.125V8H2.75V7.125ZM0 8V7.25C0 6.555 0.945 5.97 2.225 5.8C1.93 6.14 1.75 6.61 1.75 7.125V8H0ZM12 8H10.25V7.125C10.25 6.61 10.07 6.14 9.775 5.8C11.055 5.97 12 6.555 12 7.25V8Z"/></svg>',
			'info'                  => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.3335 5H7.66683V3.66667H6.3335V5ZM7.00016 12.3333C4.06016 12.3333 1.66683 9.94 1.66683 7C1.66683 4.06 4.06016 1.66667 7.00016 1.66667C9.94016 1.66667 12.3335 4.06 12.3335 7C12.3335 9.94 9.94016 12.3333 7.00016 12.3333ZM7.00016 0.333332C6.12468 0.333332 5.25778 0.505771 4.44894 0.840802C3.6401 1.17583 2.90517 1.6669 2.28612 2.28595C1.03588 3.5362 0.333496 5.23189 0.333496 7C0.333496 8.76811 1.03588 10.4638 2.28612 11.714C2.90517 12.3331 3.6401 12.8242 4.44894 13.1592C5.25778 13.4942 6.12468 13.6667 7.00016 13.6667C8.76827 13.6667 10.464 12.9643 11.7142 11.714C12.9645 10.4638 13.6668 8.76811 13.6668 7C13.6668 6.12452 13.4944 5.25761 13.1594 4.44878C12.8243 3.63994 12.3333 2.90501 11.7142 2.28595C11.0952 1.6669 10.3602 1.17583 9.55139 0.840802C8.74255 0.505771 7.87564 0.333332 7.00016 0.333332ZM6.3335 10.3333H7.66683V6.33333H6.3335V10.3333Z" fill="#141B38"/></svg>',
			'list'                  => '<svg viewBox="0 0 14 12"><path d="M0.332031 7.33341H4.33203V11.3334H0.332031V7.33341ZM9.66537 3.33341H5.66536V4.66675H9.66537V3.33341ZM0.332031 4.66675H4.33203V0.666748H0.332031V4.66675ZM5.66536 0.666748V2.00008H13.6654V0.666748H5.66536ZM5.66536 11.3334H9.66537V10.0001H5.66536V11.3334ZM5.66536 8.66675H13.6654V7.33341H5.66536"/></svg>',
			'grid'                  => '<svg viewBox="0 0 12 12"><path d="M0 5.33333H5.33333V0H0V5.33333ZM0 12H5.33333V6.66667H0V12ZM6.66667 12H12V6.66667H6.66667V12ZM6.66667 0V5.33333H12V0"/></svg>',
			'masonry'               => '<svg viewBox="0 0 16 16"><rect x="3" y="3" width="4.5" height="5" /><rect x="3" y="9" width="4.5" height="5" /><path d="M8.5 2H13V7H8.5V2Z" /><rect x="8.5" y="8" width="4.5" height="5" /></svg>',
			'carousel'              => '<svg viewBox="0 0 14 11"><path d="M0.332031 2.00008H2.9987V9.33342H0.332031V2.00008ZM3.66536 10.6667H10.332V0.666748H3.66536V10.6667ZM4.9987 2.00008H8.9987V9.33342H4.9987V2.00008ZM10.9987 2.00008H13.6654V9.33342H10.9987V2.00008Z"/></svg>',
			'highlight'             => '<svg viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="8" height="8" fill="#434960"/><rect x="11" y="2" width="3" height="3" fill="#434960"/><rect x="11" y="6" width="3" height="4" fill="#434960"/><rect x="7" y="11" width="7" height="3" fill="#434960"/><rect x="2" y="11" width="4" height="3" fill="#434960"/></svg>',
			'desktop'               => '<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.9998 9.66667H1.99984V1.66667H13.9998V9.66667ZM13.9998 0.333336H1.99984C1.25984 0.333336 0.666504 0.926669 0.666504 1.66667V9.66667C0.666504 10.0203 0.80698 10.3594 1.05703 10.6095C1.30708 10.8595 1.64622 11 1.99984 11H6.6665V12.3333H5.33317V13.6667H10.6665V12.3333H9.33317V11H13.9998C14.3535 11 14.6926 10.8595 14.9426 10.6095C15.1927 10.3594 15.3332 10.0203 15.3332 9.66667V1.66667C15.3332 1.31305 15.1927 0.973909 14.9426 0.72386C14.6926 0.473812 14.3535 0.333336 13.9998 0.333336Z" fill="#141B38"/></svg>',
			'tablet'                => '<svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.0013 2.66659V13.3333H2.0013L2.0013 2.66659H10.0013ZM0.667969 1.99992L0.667969 13.9999C0.667969 14.7399 1.2613 15.3333 2.0013 15.3333H10.0013C10.3549 15.3333 10.6941 15.1928 10.9441 14.9427C11.1942 14.6927 11.3346 14.3535 11.3346 13.9999V1.99992C11.3346 1.6463 11.1942 1.30716 10.9441 1.05711C10.6941 0.807062 10.3549 0.666586 10.0013 0.666586H2.0013C1.64768 0.666586 1.30854 0.807062 1.05849 1.05711C0.808444 1.30716 0.667969 1.6463 0.667969 1.99992Z" fill="#141B38"/></svg>',
			'mobile'                => '<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.33203 12.6667H1.66536V3.33341H8.33203V12.6667ZM8.33203 0.666748H1.66536C0.925365 0.666748 0.332031 1.26008 0.332031 2.00008V16.0001C0.332031 14.3537 0.472507 14.6928 0.722555 14.9429C0.972604 15.1929 1.31174 15.3334 1.66536 15.3334H8.33203C8.68565 15.3334 9.02479 15.1929 9.27484 14.9429C9.52489 14.6928 9.66537 14.3537 9.66537 16.0001V2.00008C9.66537 1.64646 9.52489 1.30732 9.27484 1.05727C9.02479 0.807224 8.68565 0.666748 8.33203 0.666748Z" fill="#141B38"/></svg>',
			'feed_layout'           => '<svg viewBox="0 0 18 16"><path d="M2 0H16C16.5304 0 17.0391 0.210714 17.4142 0.585786C17.7893 0.960859 18 1.46957 18 2V14C18 14.5304 17.7893 15.0391 17.4142 15.4142C17.0391 15.7893 16.5304 16 16 16H2C1.46957 16 0.960859 15.7893 0.585786 15.4142C0.210714 15.0391 0 14.5304 0 14V2C0 1.46957 0.210714 0.960859 0.585786 0.585786C0.960859 0.210714 1.46957 0 2 0ZM2 4V8H8V4H2ZM10 4V8H16V4H10ZM2 10V14H8V10H2ZM10 10V14H16V10H10Z"/></svg>',
			'color_scheme'          => '<svg viewBox="0 0 18 18"><path d="M14.5 9C14.1022 9 13.7206 8.84196 13.4393 8.56066C13.158 8.27936 13 7.89782 13 7.5C13 7.10218 13.158 6.72064 13.4393 6.43934C13.7206 6.15804 14.1022 6 14.5 6C14.8978 6 15.2794 6.15804 15.5607 6.43934C15.842 6.72064 16 7.10218 16 7.5C16 7.89782 15.842 8.27936 15.5607 8.56066C15.2794 8.84196 14.8978 9 14.5 9ZM11.5 5C11.1022 5 10.7206 4.84196 10.4393 4.56066C10.158 4.27936 10 3.89782 10 3.5C10 3.10218 10.158 2.72064 10.4393 2.43934C10.7206 2.15804 11.1022 2 11.5 2C11.8978 2 12.2794 2.15804 12.5607 2.43934C12.842 2.72064 13 3.10218 13 3.5C13 3.89782 12.842 4.27936 12.5607 4.56066C12.2794 4.84196 11.8978 5 11.5 5ZM6.5 5C6.10218 5 5.72064 4.84196 5.43934 4.56066C5.15804 4.27936 5 3.89782 5 3.5C5 3.10218 5.15804 2.72064 5.43934 2.43934C5.72064 2.15804 6.10218 2 6.5 2C6.89782 2 7.27936 2.15804 7.56066 2.43934C7.84196 2.72064 8 3.10218 8 3.5C8 3.89782 7.84196 4.27936 7.56066 4.56066C7.27936 4.84196 6.89782 5 6.5 5ZM3.5 9C3.10218 9 2.72064 8.84196 2.43934 8.56066C2.15804 8.27936 2 7.89782 2 7.5C2 7.10218 2.15804 6.72064 2.43934 6.43934C2.72064 6.15804 3.10218 6 3.5 6C3.89782 6 4.27936 6.15804 4.56066 6.43934C4.84196 6.72064 5 7.10218 5 7.5C5 7.89782 4.84196 8.27936 4.56066 8.56066C4.27936 8.84196 3.89782 9 3.5 9ZM9 0C6.61305 0 4.32387 0.948211 2.63604 2.63604C0.948211 4.32387 0 6.61305 0 9C0 11.3869 0.948211 13.6761 2.63604 15.364C4.32387 17.0518 6.61305 18 9 18C9.39782 18 9.77936 17.842 10.0607 17.5607C10.342 17.2794 10.5 16.8978 10.5 16.5C10.5 16.11 10.35 15.76 10.11 15.5C9.88 15.23 9.73 14.88 9.73 14.5C9.73 14.1022 9.88804 13.7206 10.1693 13.4393C10.4506 13.158 10.8322 13 11.23 13H13C14.3261 13 15.5979 12.4732 16.5355 11.5355C17.4732 10.5979 18 9.32608 18 8C18 3.58 13.97 0 9 0Z"/></svg>',
			'header'                => '<svg viewBox="0 0 20 13"><path d="M1.375 0.625C0.960787 0.625 0.625 0.960786 0.625 1.375V11.5H2.875V2.875H17.125V9.625H11.5V11.875H18.625C19.0392 11.875 19.375 11.5392 19.375 11.125V1.375C19.375 0.960786 19.0392 0.625 18.625 0.625H1.375Z"/><path d="M4.375 7C4.16789 7 4 7.16789 4 7.375V12.625C4 12.8321 4.16789 13 4.375 13H9.625C9.83211 13 10 12.8321 10 12.625V7.375C10 7.16789 9.83211 7 9.625 7H4.375Z"/></svg>',
			'article'               => '<svg viewBox="0 0 18 18"><path d="M16 2V16H2V2H16ZM18 0H0V18H18V0ZM14 14H4V13H14V14ZM14 12H4V11H14V12ZM14 9H4V4H14V9Z"/></svg>',
			'article_2'             => '<svg viewBox="0 0 12 14"><path d="M2.0013 0.333496C1.64768 0.333496 1.30854 0.473972 1.05849 0.72402C0.808444 0.974069 0.667969 1.31321 0.667969 1.66683V12.3335C0.667969 12.6871 0.808444 13.0263 1.05849 13.2763C1.30854 13.5264 1.64768 13.6668 2.0013 13.6668H10.0013C10.3549 13.6668 10.6941 13.5264 10.9441 13.2763C11.1942 13.0263 11.3346 12.6871 11.3346 12.3335V4.3335L7.33463 0.333496H2.0013ZM2.0013 1.66683H6.66797V5.00016H10.0013V12.3335H2.0013V1.66683ZM3.33464 7.00016V8.3335H8.66797V7.00016H3.33464ZM3.33464 9.66683V11.0002H6.66797V9.66683H3.33464Z"/></svg>',
			'like_box'              => '<svg viewBox="0 0 18 17"><path d="M17.505 7.91114C17.505 7.48908 17.3373 7.08431 17.0389 6.78587C16.7405 6.48744 16.3357 6.31977 15.9136 6.31977H10.8849L11.6488 2.68351C11.6647 2.60394 11.6727 2.51641 11.6727 2.42889C11.6727 2.10266 11.5374 1.8003 11.3226 1.58547L10.4791 0.75L5.24354 5.98559C4.94914 6.27999 4.77409 6.67783 4.77409 7.11546V15.0723C4.77409 15.4943 4.94175 15.8991 5.24019 16.1975C5.53863 16.496 5.9434 16.6636 6.36546 16.6636H13.5266C14.187 16.6636 14.7519 16.2658 14.9906 15.6929L17.3936 10.0834C17.4652 9.90034 17.505 9.70938 17.505 9.5025V7.91114ZM0 16.6636H3.18273V7.11546H0V16.6636Z"/></svg>',
			'load_more'             => '<svg viewBox="0 0 24 24"><path d="M20 18.5H4C3.46957 18.5 2.96086 18.2893 2.58579 17.9142C2.21071 17.5391 2 17.0304 2 16.5V7.5C2 6.96957 2.21071 6.46086 2.58579 6.08579C2.96086 5.71071 3.46957 5.5 4 5.5H20C20.5304 5.5 21.0391 5.71071 21.4142 6.08579C21.7893 6.46086 22 6.96957 22 7.5V16.5C22 17.0304 21.7893 17.5391 21.4142 17.9142C21.0391 18.2893 20.5304 18.5 20 18.5ZM4 7.5V16.5H20V7.5H4Z"/><circle cx="7.5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="16.5" cy="12" r="1.5"/></svg>',
			'lightbox'              => '<svg viewBox="0 0 24 24"><path d="M21 17H7V3H21V17ZM21 1H7C6.46957 1 5.96086 1.21071 5.58579 1.58579C5.21071 1.96086 5 2.46957 5 3V17C5 17.5304 5.21071 18.0391 5.58579 18.4142C5.96086 18.7893 6.46957 19 7 19H21C21.5304 19 22.0391 18.7893 22.4142 18.4142C22.7893 18.0391 23 17.5304 23 17V3C23 2.46957 22.7893 1.96086 22.4142 1.58579C22.0391 1.21071 21.5304 1 21 1ZM3 5H1V21C1 21.5304 1.21071 22.0391 1.58579 22.4142C1.96086 22.7893 2.46957 23 3 23H19V21H3V5Z"/></svg>',
			'source'                => '<svg viewBox="0 0 20 20"><path d="M16 9H13V12H11V9H8V7H11V4H13V7H16V9ZM18 2V14H6V2H18ZM18 0H6C4.9 0 4 0.9 4 2V14C4 14.5304 4.21071 15.0391 4.58579 15.4142C4.96086 15.7893 5.46957 16 6 16H18C19.11 16 20 15.11 20 14V2C20 1.46957 19.7893 0.960859 19.4142 0.585786C19.0391 0.210714 18.5304 0 18 0ZM2 4H0V18C0 18.5304 0.210714 19.0391 0.585786 19.4142C0.960859 19.7893 1.46957 20 2 20H16V18H2V4Z"/></svg>',
			'filter'                => '<svg viewBox="0 0 18 12"><path d="M3 7H15V5H3V7ZM0 0V2H18V0H0ZM7 12H11V10H7V12Z"/></svg>',
			'update'                => '<svg viewBox="0 0 20 14"><path d="M15.832 3.66659L12.4987 6.99992H14.9987C14.9987 8.326 14.4719 9.59777 13.5342 10.5355C12.5965 11.4731 11.3248 11.9999 9.9987 11.9999C9.16536 11.9999 8.35703 11.7916 7.66536 11.4166L6.4487 12.6333C7.50961 13.3085 8.74115 13.6669 9.9987 13.6666C11.7668 13.6666 13.4625 12.9642 14.7127 11.714C15.963 10.4637 16.6654 8.76803 16.6654 6.99992H19.1654L15.832 3.66659ZM4.9987 6.99992C4.9987 5.67384 5.52548 4.40207 6.46316 3.46438C7.40085 2.5267 8.67261 1.99992 9.9987 1.99992C10.832 1.99992 11.6404 2.20825 12.332 2.58325L13.5487 1.36659C12.4878 0.691379 11.2562 0.332902 9.9987 0.333252C8.23059 0.333252 6.53489 1.03563 5.28465 2.28587C6.03441 3.53612 3.33203 5.23181 3.33203 6.99992H0.832031L4.16536 10.3333L7.4987 6.99992"/></svg>',
			'sun'                   => '<svg viewBox="0 0 16 15"><path d="M2.36797 12.36L3.30797 13.3L4.50797 12.1067L3.5613 11.16L2.36797 12.36ZM7.33463 14.9667H8.66797V13H7.33463V14.9667ZM8.0013 3.6667C6.94044 3.6667 5.92302 6.08813 5.17287 4.83827C4.42273 5.58842 6.0013 6.60583 6.0013 7.6667C6.0013 8.72756 4.42273 9.74498 5.17287 10.4951C5.92302 11.2453 6.94044 11.6667 8.0013 11.6667C9.06217 11.6667 10.0796 11.2453 10.8297 10.4951C11.5799 9.74498 12.0013 8.72756 12.0013 7.6667C12.0013 5.45336 10.208 3.6667 8.0013 3.6667ZM13.3346 8.33336H15.3346V7.00003H13.3346V8.33336ZM11.4946 12.1067L12.6946 13.3L13.6346 12.36L12.4413 11.16L11.4946 12.1067ZM13.6346 2.97337L12.6946 2.03337L11.4946 3.2267L12.4413 4.17336L13.6346 2.97337ZM8.66797 0.366699H7.33463V2.33337H8.66797V0.366699ZM2.66797 7.00003H0.667969V8.33336H2.66797V7.00003ZM4.50797 3.2267L3.30797 2.03337L2.36797 2.97337L3.5613 4.17336L4.50797 3.2267Z"/></svg>',
			'moon'                  => '<svg viewBox="0 0 10 10"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.63326 6.88308C9.26754 6.95968 8.88847 6.99996 8.5 6.99996C5.46243 6.99996 3 4.53752 3 1.49996C3 1.11148 3.04028 0.732413 3.11688 0.366699C1.28879 1.11045 0 2.9047 0 4.99996C0 7.76138 2.23858 9.99996 5 9.99996C7.09526 9.99996 8.88951 8.71117 9.63326 6.88308Z"/></svg>',
			'visual'                => '<svg viewBox="0 0 12 12"><path d="M3.66667 7L5.33333 9L7.66667 6L10.6667 10H1.33333L3.66667 7ZM12 10.6667V1.33333C12 0.979711 11.8595 0.640573 11.6095 0.390524C11.3594 0.140476 11.0203 0 10.6667 0H1.33333C0.979711 0 0.640573 0.140476 0.390524 0.390524C0.140476 0.640573 0 0.979711 0 1.33333V10.6667C0 11.0203 0.140476 11.3594 0.390524 11.6095C0.640573 11.8595 0.979711 12 1.33333 12H10.6667C11.0203 12 11.3594 11.8595 11.6095 11.6095C11.8595 11.3594 12 11.0203 12 10.6667Z" /></svg>',
			'text'                  => '<svg viewBox="0 0 14 12"><path d="M12.332 11.3334H1.66536C1.31174 11.3334 0.972604 11.1929 0.722555 10.9429C0.472507 10.6928 0.332031 10.3537 0.332031 10.0001V2.00008C0.332031 1.64646 0.472507 1.30732 0.722555 1.05727C0.972604 0.807224 1.31174 0.666748 1.66536 0.666748H12.332C12.6857 0.666748 13.0248 0.807224 13.2748 1.05727C13.5249 1.30732 13.6654 1.64646 13.6654 2.00008V10.0001C13.6654 10.3537 13.5249 10.6928 13.2748 10.9429C13.0248 11.1929 12.6857 11.3334 12.332 11.3334ZM1.66536 2.00008V10.0001H12.332V2.00008H1.66536ZM2.9987 6.00008H10.9987V5.33341H2.9987V6.00008ZM2.9987 6.66675H9.66537V8.00008H2.9987V6.66675Z"/></svg>',
			'background'            => '<svg viewBox="0 0 14 12"><path d="M12.334 11.3334H1.66732C1.3137 11.3334 0.974557 11.1929 0.724509 10.9429C0.47446 10.6928 0.333984 10.3537 0.333984 10.0001V2.00008C0.333984 1.64646 0.47446 1.30732 0.724509 1.05727C0.974557 0.807224 1.3137 0.666748 1.66732 0.666748H12.334C12.6876 0.666748 13.0267 0.807224 13.2768 1.05727C13.5268 1.30732 13.6673 1.64646 13.6673 2.00008V10.0001C13.6673 10.3537 13.5268 10.6928 13.2768 10.9429C13.0267 11.1929 12.6876 11.3334 12.334 11.3334Z"/></svg>',
			'cursor'                => '<svg viewBox="-96 0 512 512"><path d="m180.777344 512c-2.023438 0-6.03125-.382812-5.949219-1.152344-3.96875-1.578125-7.125-4.691406-8.789063-8.640625l-59.863281-141.84375-71.144531 62.890625c-2.988281 3.070313-8.34375 5.269532-13.890625 5.269532-11.648437 0-21.140625-9.515626-21.140625-21.226563v-386.070313c0-11.710937 9.492188-21.226562 21.140625-21.226562 4.929687 0 9.707031 1.726562 13.761719 5.011719l279.058594 282.96875c4.355468 5.351562 6.039062 10.066406 6.039062 14.972656 0 11.691406-9.492188 21.226563-21.140625 21.226563h-94.785156l57.6875 136.8125c3.410156 8.085937-.320313 17.386718-8.363281 20.886718l-66.242188 28.796875c-2.027344.875-4.203125 1.324219-6.378906 1.324219zm-68.5-194.367188c1.195312 0 2.367187.128907 3.5625.40625 5.011718 1.148438 9.195312 4.628907 11.179687 9.386719l62.226563 147.453125 36.886718-16.042968-60.90625-144.445313c-2.089843-4.929687-1.558593-10.605469 1.40625-15.0625 2.96875-4.457031 7.980469-7.148437 13.335938-7.148437h93.332031l-241.300781-244.671876v335.765626l69.675781-61.628907c2.941407-2.605469 6.738281-6.011719 10.601563-6.011719zm-97.984375 81.300782c-.449219.339844-.851563.703125-1.238281 1.085937zm275.710937-89.8125h.214844zm0 0"/></svg>',
			'link'                  => '<svg viewBox="0 0 14 8"><path d="M1.60065 6.00008C1.60065 2.86008 2.52732 1.93341 3.66732 1.93341H6.33399V0.666748H3.66732C2.78326 0.666748 1.93542 1.01794 1.3103 1.64306C0.685174 2.26818 0.333984 3.11603 0.333984 6.00008C0.333984 4.88414 0.685174 5.73198 1.3103 6.35711C1.93542 6.98223 2.78326 7.33342 3.66732 7.33342H6.33399V6.06675H3.66732C2.52732 6.06675 1.60065 5.14008 1.60065 6.00008ZM4.33398 4.66675H9.66732V3.33342H4.33398V4.66675ZM10.334 0.666748H7.66732V1.93341H10.334C11.474 1.93341 12.4007 2.86008 12.4007 6.00008C12.4007 5.14008 11.474 6.06675 10.334 6.06675H7.66732V7.33342H10.334C11.218 7.33342 12.0659 6.98223 12.691 6.35711C13.3161 5.73198 13.6673 4.88414 13.6673 6.00008C13.6673 3.11603 13.3161 2.26818 12.691 1.64306C12.0659 1.01794 11.218 0.666748 10.334 0.666748Z"/></svg>',
			'thumbnail'             => '<svg viewBox="0 0 14 12"><path d="M0.332031 7.33333H4.33203V11.3333H0.332031V7.33333ZM9.66537 3.33333H5.66536V4.66666H9.66537V3.33333ZM0.332031 4.66666H4.33203V0.666664H0.332031V4.66666ZM5.66536 0.666664V2H13.6654V0.666664H5.66536ZM5.66536 11.3333H9.66537V10H5.66536V11.3333ZM5.66536 8.66666H13.6654V7.33333H5.66536"/></svg>',
			'halfwidth'             => '<svg viewBox="0 0 14 8"><path d="M6 0.5H0V7.5H6V0.5Z"/><path d="M14 0.75H7.5V2H14V0.75Z"/><path d="M7.5 3.25H14V4.5H7.5V3.25Z"/><path d="M11 5.75H7.5V7H11V5.75Z"/></svg>',
			'fullwidth'             => '<svg viewBox="0 0 10 12"><path fill-rule="evenodd" clip-rule="evenodd" d="M10 6.75V0.333328H0V6.75H10Z"/><path d="M0 8.24999H10V9.49999H0V8.24999Z"/><path d="M6 10.75H0V12H6V10.75Z"/></svg>',
			'boxed'                 => '<svg viewBox="0 0 16 16"><path d="M14.1667 12.8905H1.83333C1.47971 12.8905 1.14057 12.75 0.890524 12.5C0.640476 12.25 0.5 11.9108 0.5 11.5572V3.33333C0.5 2.97971 0.640476 2.64057 0.890524 2.39052C1.14057 2.14048 1.47971 2 1.83333 2H14.1667C14.5203 2 14.8594 2.14048 15.1095 2.39052C15.3595 2.64057 15.5 2.97971 15.5 3.33333V11.5572C15.5 11.9108 15.3595 12.25 15.1095 12.5C14.8594 12.75 14.5203 12.8905 14.1667 12.8905ZM1.83333 3.33333V11.5572H14.1667V3.33333H1.83333Z"/><path d="M8 8H11V9H8V8Z"/><path d="M6.5 9.5H3V5.5H6.5V9.5Z"/><path d="M8 7V6H13V7H8Z"/></svg>',
			'corner'                => '<svg viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" d="M5 1.5H1.5V10.5H10.5V7C10.5 3.96243 8.03757 1.5 5 1.5ZM0 0V12H12V7C12 3.13401 8.86599 0 5 0H0Z"/></svg>',
			'preview'               => '<svg viewBox="0 0 16 10"><path d="M8.0013 3C7.47087 3 6.96216 3.21071 6.58709 3.58579C6.21202 3.96086 6.0013 4.46957 6.0013 5C6.0013 5.53043 6.21202 6.03914 6.58709 6.41421C6.96216 6.78929 7.47087 7 8.0013 7C8.53173 7 9.04044 6.78929 9.41551 6.41421C9.79059 6.03914 10.0013 5.53043 10.0013 5C10.0013 4.46957 9.79059 3.96086 9.41551 3.58579C9.04044 3.21071 8.53173 3 8.0013 3ZM8.0013 8.33333C7.11725 8.33333 6.2694 7.98214 5.64428 7.35702C5.01916 6.7319 4.66797 5.88406 4.66797 5C4.66797 4.11595 5.01916 3.2681 5.64428 2.64298C6.2694 2.01786 7.11725 1.66667 8.0013 1.66667C8.88536 1.66667 9.7332 2.01786 10.3583 2.64298C10.9834 3.2681 11.3346 4.11595 11.3346 5C11.3346 5.88406 10.9834 6.7319 10.3583 7.35702C9.7332 7.98214 8.88536 8.33333 8.0013 8.33333ZM8.0013 0C4.66797 0 1.8213 2.07333 0.667969 5C1.8213 7.92667 4.66797 10 8.0013 10C11.3346 10 14.1813 7.92667 15.3346 5C14.1813 2.07333 11.3346 0 8.0013 0Z"/></svg>',
			'flag'                  => '<svg viewBox="0 0 9 9"><path d="M5.53203 1L5.33203 0H0.832031V8.5H1.83203V5H4.63203L4.83203 6H8.33203V1H5.53203Z"/></svg>',
			'copy2'                 => '<svg viewBox="0 0 12 13"><path d="M10.25 0.25H4.625C3.9375 0.25 3.375 0.8125 3.375 1.5V9C3.375 9.6875 3.9375 10.25 4.625 10.25H10.25C10.9375 10.25 11.5 9.6875 11.5 9V1.5C11.5 0.8125 10.9375 0.25 10.25 0.25ZM10.25 9H4.625V1.5H10.25V9ZM0.875 8.375V7.125H2.125V8.375H0.875ZM0.875 4.9375H2.125V6.1875H0.875V4.9375ZM5.25 11.5H6.5V12.75H5.25V11.5ZM0.875 10.5625V9.3125H2.125V10.5625H0.875ZM2.125 12.75C1.4375 12.75 0.875 12.1875 0.875 11.5H2.125V12.75ZM4.3125 12.75H3.0625V11.5H4.3125V12.75ZM7.4375 12.75V11.5H8.6875C8.6875 12.1875 8.125 12.75 7.4375 12.75ZM2.125 2.75V4H0.875C0.875 3.3125 1.4375 2.75 2.125 2.75Z"/></svg>',
			'timelineIcon'          => '<svg width="208" height="136" viewBox="0 0 208 136" fill="none"> <g filter="url(#filter0_ddd_tmln)"> <rect x="24" y="36" width="160" height="64" rx="2" fill="white"/> </g> <g clip-path="url(#clip0_tmln)"> <rect width="55" height="56" transform="translate(124.8 40)" fill="#F9BBA0"/> <circle cx="200.3" cy="102.5" r="55.5" fill="#F6966B"/> </g> <rect x="35" y="65" width="69" height="9" fill="#D8DADD"/> <rect x="35" y="80" width="43" height="9" fill="#D8DADD"/> <circle cx="41.5" cy="50.5" r="6.5" fill="#D8DADD"/> <defs> <filter id="filter0_ddd_tmln" x="11" y="29" width="186" height="90" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <clipPath id="clip0_tmln"> <rect width="55" height="56" fill="white" transform="translate(124.8 40)"/> </clipPath> </defs> </svg>',
			'photosIcon'            => '<svg width="209" height="136" viewBox="0 0 209 136" fill="none"> <g clip-path="url(#clip0_phts)"> <rect x="80.2002" y="44" width="48" height="48" fill="#43A6DB"/> <circle cx="70.7002" cy="78.5" r="40.5" fill="#86D0F9"/> </g> <g clip-path="url(#clip1_phts)"> <rect x="131.2" y="44" width="48" height="48" fill="#B6DDAD"/> <rect x="152.2" y="65" width="33" height="33" fill="#96CE89"/> </g> <g clip-path="url(#clip2_phts)"> <rect x="29.2002" y="44" width="48" height="48" fill="#F6966B"/> <path d="M38.6485 61L76.6485 99H7.2002L38.6485 61Z" fill="#F9BBA0"/> </g> <defs> <clipPath id="clip0_phts"> <rect x="80.2002" y="44" width="48" height="48" rx="1" fill="white"/> </clipPath> <clipPath id="clip1_phts"> <rect x="131.2" y="44" width="48" height="48" rx="1" fill="white"/> </clipPath> <clipPath id="clip2_phts"> <rect x="29.2002" y="44" width="48" height="48" rx="1" fill="white"/> </clipPath> </defs> </svg>',
			'videosIcon'            => '<svg width="209" height="136" viewBox="0 0 209 136" fill="none"> <rect x="41.6001" y="31" width="126" height="74" fill="#43A6DB"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M104.6 81C111.78 81 117.6 75.1797 117.6 68C117.6 60.8203 111.78 55 104.6 55C97.4204 55 91.6001 60.8203 91.6001 68C91.6001 75.1797 97.4204 81 104.6 81ZM102.348 63.2846C102.015 63.0942 101.6 63.3349 101.6 63.7188V72.2813C101.6 72.6652 102.015 72.9059 102.348 72.7154L109.84 68.4342C110.176 68.2422 110.176 67.7579 109.84 67.5659L102.348 63.2846Z" fill="white"/> </svg>',
			'albumsIcon'            => '<svg width="210" height="136" viewBox="0 0 210 136" fill="none"> <g clip-path="url(#clip0_albm)"> <rect x="76.1187" y="39.7202" width="57.7627" height="57.7627" fill="#43A6DB"/> <rect x="101.39" y="64.9917" width="39.7119" height="39.7119" fill="#86D0F9"/> </g> <g clip-path="url(#clip1_albm)"> <rect x="70.1016" y="32.5" width="57.7627" height="57.7627" fill="#F9BBA0"/> <path d="M81.4715 52.9575L127.2 98.6863H43.627L81.4715 52.9575Z" fill="#F6966B"/> </g> <defs> <clipPath id="clip0_albm"> <rect x="76.1187" y="39.7202" width="57.7627" height="57.7627" rx="1.20339" fill="white"/> </clipPath> <clipPath id="clip1_albm"> <rect x="70.1016" y="32.5" width="57.7627" height="57.7627" rx="1.20339" fill="white"/> </clipPath> </defs> </svg>',
			'eventsIcon'            => '<svg width="209" height="136" viewBox="0 0 209 136" fill="none"> <g filter="url(#filter0_ddd_evt)"> <rect x="20.5562" y="39.9375" width="160" height="64" rx="2" fill="white"/> </g> <rect x="31.6001" y="69" width="102" height="9" fill="#D8DADD"/> <rect x="31.6001" y="84" width="64" height="9" fill="#D8DADD"/> <circle cx="38.0562" cy="54.4375" r="6.5" fill="#D8DADD"/> <circle cx="173.744" cy="46.5625" r="14.5" fill="#FE544F"/> <path d="M169.275 53.5L173.775 50.875L178.275 53.5V42.625C178.275 42.0156 177.759 41.5 177.15 41.5H170.4C169.767 41.5 169.275 42.0156 169.275 42.625V53.5Z" fill="white"/> <defs> <filter id="filter0_ddd_evt" x="7.55615" y="32.9375" width="186" height="90" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> </defs> </svg>',
			'reviewsIcon'           => '<svg width="207" height="129" viewBox="0 0 207 129" fill="none"> <g filter="url(#filter0_ddd_rev)"> <rect x="23.5" y="32.5" width="160" height="64" rx="2" fill="white"/> </g> <path d="M61.0044 42.8004C61.048 42.6917 61.202 42.6917 61.2456 42.8004L62.7757 46.6105C62.7942 46.6568 62.8377 46.6884 62.8875 46.6917L66.9839 46.9695C67.1008 46.9774 67.1484 47.1238 67.0584 47.199L63.9077 49.8315C63.8694 49.8635 63.8528 49.9145 63.8649 49.9629L64.8666 53.9447C64.8952 56.0583 64.7707 54.1488 64.6714 56.0865L61.1941 51.9034C61.1519 51.8769 61.0981 51.8769 61.0559 51.9034L57.5786 56.0865C57.4793 54.1488 57.3548 56.0583 57.3834 53.9447L58.3851 49.9629C58.3972 49.9145 58.3806 49.8635 58.3423 49.8315L55.1916 47.199C55.1016 47.1238 55.1492 46.9774 55.2661 46.9695L59.3625 46.6917C59.4123 46.6884 59.4558 46.6568 59.4743 46.6105L61.0044 42.8004Z" fill="#FE544F"/> <path d="M76.6045 42.8004C76.6481 42.6917 76.8021 42.6917 76.8457 42.8004L78.3757 46.6105C78.3943 46.6568 78.4378 46.6884 78.4876 46.6917L82.584 46.9695C82.7009 46.9774 82.7485 47.1238 82.6585 47.199L79.5078 49.8315C79.4695 49.8635 79.4529 49.9145 79.465 49.9629L80.4667 53.9447C80.4953 56.0583 80.3708 54.1488 80.2715 56.0865L76.7942 51.9034C76.752 51.8769 76.6982 51.8769 76.656 51.9034L73.1787 56.0865C73.0794 54.1488 72.9549 56.0583 72.9835 53.9447L73.9852 49.9629C73.9973 49.9145 73.9807 49.8635 73.9424 49.8315L70.7917 47.199C70.7017 47.1238 70.7493 46.9774 70.8662 46.9695L74.9626 46.6917C75.0124 46.6884 75.0559 46.6568 75.0744 46.6105L76.6045 42.8004Z" fill="#FE544F"/> <path d="M92.2046 42.8004C92.2482 42.6917 92.4022 42.6917 92.4458 42.8004L93.9758 46.6105C93.9944 46.6568 96.0379 46.6884 96.0877 46.6917L98.1841 46.9695C98.301 46.9774 98.3486 47.1238 98.2586 47.199L95.1078 49.8315C95.0696 49.8635 95.053 49.9145 95.0651 49.9629L96.0668 53.9447C96.0954 56.0583 95.9709 54.1488 95.8716 56.0865L92.3943 51.9034C92.3521 51.8769 92.2983 51.8769 92.2561 51.9034L88.7788 56.0865C88.6795 54.1488 88.555 56.0583 88.5836 53.9447L89.5853 49.9629C89.5974 49.9145 89.5808 49.8635 89.5425 49.8315L86.3918 47.199C86.3018 47.1238 86.3494 46.9774 86.4663 46.9695L90.5627 46.6917C90.6125 46.6884 90.6559 46.6568 90.6745 46.6105L92.2046 42.8004Z" fill="#FE544F"/> <path d="M107.804 42.8004C107.848 42.6917 108.002 42.6917 108.045 42.8004L109.575 46.6105C109.594 46.6568 109.638 46.6884 109.687 46.6917L113.784 46.9695C113.901 46.9774 113.948 47.1238 113.858 47.199L110.707 49.8315C110.669 49.8635 110.653 49.9145 110.665 49.9629L111.666 53.9447C111.695 56.0583 111.57 54.1488 111.471 56.0865L107.994 51.9034C107.952 51.8769 107.898 51.8769 107.856 51.9034L104.378 56.0865C104.279 54.1488 104.155 56.0583 104.183 53.9447L105.185 49.9629C105.197 49.9145 105.18 49.8635 105.142 49.8315L101.991 47.199C101.901 47.1238 101.949 46.9774 102.066 46.9695L106.162 46.6917C106.212 46.6884 106.256 46.6568 106.274 46.6105L107.804 42.8004Z" fill="#FE544F"/> <path d="M123.404 42.8004C123.448 42.6917 123.602 42.6917 123.646 42.8004L125.176 46.6105C125.194 46.6568 125.238 46.6884 125.287 46.6917L129.384 46.9695C129.501 46.9774 129.548 47.1238 129.458 47.199L126.308 49.8315C126.269 49.8635 126.253 49.9145 126.265 49.9629L127.267 53.9447C127.295 56.0583 127.171 54.1488 127.071 56.0865L123.594 51.9034C123.552 51.8769 123.498 51.8769 123.456 51.9034L119.978 56.0865C119.879 54.1488 119.755 56.0583 119.783 53.9447L120.785 49.9629C120.797 49.9145 120.781 49.8635 120.742 49.8315L117.591 47.199C117.502 47.1238 117.549 46.9774 117.666 46.9695L121.762 46.6917C121.812 46.6884 121.856 46.6568 121.874 46.6105L123.404 42.8004Z" fill="#FE544F"/> <rect x="54.625" y="65.5" width="70" height="7" fill="#D8DADD"/> <rect x="54.625" y="78.5" width="43" height="7" fill="#D8DADD"/> <circle cx="39" cy="49" r="6.5" fill="#D8DADD"/> <defs> <filter id="filter0_ddd_rev" x="10.5" y="25.5" width="186" height="90" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> </defs> </svg>',
			'featuredpostIcon'      => '<svg width="207" height="129" viewBox="0 0 207 129" fill="none"> <g filter="url(#filter0_ddd_ftpst)"> <rect x="21.4282" y="34.7188" width="160" height="64" rx="2" fill="white"/> </g> <g clip-path="url(#clip0_ftpst)"> <rect width="55" height="56" transform="translate(122.228 38.7188)" fill="#43A6DB"/> <circle cx="197.728" cy="101.219" r="55.5" fill="#86D0F9"/> </g> <rect x="32.4282" y="63.7188" width="69" height="9" fill="#D8DADD"/> <rect x="32.4282" y="78.7188" width="43" height="9" fill="#D8DADD"/> <circle cx="38.9282" cy="49.2188" r="6.5" fill="#D8DADD"/> <circle cx="171.072" cy="44.7812" r="15.5" fill="#EC352F" stroke="#FEF4EF" stroke-width="2"/> <path d="M173.587 44.7578L173.283 41.9688H174.291C174.595 41.9688 174.853 41.7344 174.853 41.4062V40.2812C174.853 39.9766 174.595 39.7188 174.291 39.7188H167.916C167.587 39.7188 167.353 39.9766 167.353 40.2812V41.4062C167.353 41.7344 167.587 41.9688 167.916 41.9688H168.9L168.595 44.7578C167.47 45.2734 166.603 46.2344 166.603 47.4062C166.603 47.7344 166.837 47.9688 167.166 47.9688H170.353V50.4297C170.353 50.4531 170.353 50.4766 170.353 50.5L170.916 51.625C170.986 51.7656 171.197 51.7656 171.267 51.625L171.83 50.5C171.83 50.4766 171.853 50.4531 171.853 50.4297V47.9688H175.041C175.345 47.9688 175.603 47.7344 175.603 47.4062C175.603 46.2109 174.712 45.2734 173.587 44.7578Z" fill="white"/> <defs> <filter id="filter0_ddd_ftpst" x="8.42822" y="27.7188" width="186" height="90" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <clipPath id="clip0_ftpst"> <rect width="55" height="56" fill="white" transform="translate(122.228 38.7188)"/> </clipPath> </defs> </svg>',
			'singlealbumIcon'       => '<svg width="207" height="129" viewBox="0 0 207 129" fill="none"> <g clip-path="url(#clip0_sglalb)"> <rect x="74.6187" y="36.2202" width="57.7627" height="57.7627" fill="#43A6DB"/> <rect x="99.8896" y="61.4917" width="39.7119" height="39.7119" fill="#86D0F9"/> </g> <g clip-path="url(#clip1_sglalb)"> <rect x="68.6016" y="29" width="57.7627" height="57.7627" fill="#F9BBA0"/> <path d="M79.9715 49.4575L125.7 95.1863H42.127L79.9715 49.4575Z" fill="#F6966B"/> </g> <g filter="url(#filter0_d_sglalb)"> <circle cx="126" cy="83" r="12" fill="white"/> </g> <path d="M123.584 79H122.205L120.217 80.2773V81.6055L122.088 80.4102H122.135V87H123.584V79ZM126.677 81H125.177L126.959 84L125.131 87H126.631L127.888 84.8398L129.158 87H130.646L128.806 84L130.615 81H129.119L127.888 83.2148L126.677 81Z" fill="black"/> <defs> <filter id="filter0_d_sglalb" x="109" y="67" width="34" height="34" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="2.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/> </filter> <clipPath id="clip0_sglalb"> <rect x="74.6187" y="36.2202" width="57.7627" height="57.7627" rx="1.20339" fill="white"/> </clipPath> <clipPath id="clip1_sglalb"> <rect x="68.6016" y="29" width="57.7627" height="57.7627" rx="1.20339" fill="white"/> </clipPath> </defs> </svg>',
			'socialwallIcon'        => '<svg width="207" height="129" viewBox="0 0 207 129" fill="none"> <path d="M96.6875 47.5C96.6875 42.1484 92.3516 37.8125 87 37.8125C81.6484 37.8125 77.3125 42.1484 77.3125 47.5C77.3125 52.3438 80.8281 56.3672 85.4766 57.0703V50.3125H83.0156V47.5H85.4766V45.3906C85.4766 42.9688 86.9219 41.6016 89.1094 41.6016C90.2031 41.6016 91.2969 41.7969 91.2969 41.7969V44.1797H90.0859C88.875 44.1797 88.4844 44.9219 88.4844 45.7031V47.5H91.1797L90.75 50.3125H88.4844V57.0703C93.1328 56.3672 96.6875 52.3438 96.6875 47.5Z" fill="#2A65DB"/> <path d="M128.695 42.3828C128.461 41.4453 127.719 40.7031 126.82 40.4688C125.141 40 118.5 40 118.5 40C118.5 40 111.82 40 110.141 40.4688C109.242 40.7031 108.5 41.4453 108.266 42.3828C107.797 46.0234 107.797 47.5391 107.797 47.5391C107.797 47.5391 107.797 51.0156 108.266 52.6953C108.5 53.6328 109.242 54.3359 110.141 54.5703C111.82 55 118.5 55 118.5 55C118.5 55 125.141 55 126.82 54.5703C127.719 54.3359 128.461 53.6328 128.695 52.6953C129.164 51.0156 129.164 47.5391 129.164 47.5391C129.164 47.5391 129.164 46.0234 128.695 42.3828ZM116.312 50.7031V44.375L121.859 47.5391L116.312 50.7031Z" fill="url(#paint0_linear_sclwl)"/> <path d="M86 78.0078C83.5 78.0078 81.5078 80.0391 81.5078 82.5C81.5078 85 83.5 86.9922 86 86.9922C88.4609 86.9922 90.4922 85 90.4922 82.5C90.4922 80.0391 88.4609 78.0078 86 78.0078ZM86 85.4297C84.3984 85.4297 83.0703 84.1406 83.0703 82.5C83.0703 80.8984 84.3594 79.6094 86 79.6094C87.6016 79.6094 88.8906 80.8984 88.8906 82.5C88.8906 84.1406 87.6016 85.4297 86 85.4297ZM91.7031 77.8516C91.7031 77.2656 91.2344 76.7969 90.6484 76.7969C90.0625 76.7969 89.5938 77.2656 89.5938 77.8516C89.5938 78.4375 90.0625 78.9062 90.6484 78.9062C91.2344 78.9062 91.7031 78.4375 91.7031 77.8516ZM94.6719 78.9062C94.5938 77.5 94.2812 76.25 93.2656 75.2344C92.25 74.2188 91 73.9062 89.5938 73.8281C88.1484 73.75 83.8125 73.75 82.3672 73.8281C80.9609 73.9062 79.75 74.2188 78.6953 75.2344C77.6797 76.25 77.3672 77.5 77.2891 78.9062C77.2109 80.3516 77.2109 84.6875 77.2891 86.1328C77.3672 87.5391 77.6797 88.75 78.6953 89.8047C79.75 90.8203 80.9609 91.1328 82.3672 91.2109C83.8125 91.2891 88.1484 91.2891 89.5938 91.2109C91 91.1328 92.25 90.8203 93.2656 89.8047C94.2812 88.75 94.5938 87.5391 94.6719 86.1328C94.75 84.6875 94.75 80.3516 94.6719 78.9062ZM92.7969 87.6562C92.5234 88.4375 91.8984 89.0234 91.1562 89.3359C89.9844 89.8047 87.25 89.6875 86 89.6875C84.7109 89.6875 81.9766 89.8047 80.8438 89.3359C80.0625 89.0234 79.4766 88.4375 79.1641 87.6562C78.6953 86.5234 78.8125 83.7891 78.8125 82.5C78.8125 81.25 78.6953 78.5156 79.1641 77.3438C79.4766 76.6016 80.0625 76.0156 80.8438 75.7031C81.9766 75.2344 84.7109 75.3516 86 75.3516C87.25 75.3516 89.9844 75.2344 91.1562 75.7031C91.8984 75.9766 92.4844 76.6016 92.7969 77.3438C93.2656 78.5156 93.1484 81.25 93.1484 82.5C93.1484 83.7891 93.2656 86.5234 92.7969 87.6562Z" fill="url(#paint1_linear_swwl)"/> <path d="M127.93 78.4375C128.711 77.8516 129.414 77.1484 129.961 76.3281C129.258 76.6406 128.438 76.875 127.617 76.9531C128.477 76.4453 129.102 75.6641 129.414 74.6875C128.633 75.1562 127.734 75.5078 126.836 75.7031C126.055 74.8828 125 74.4141 123.828 74.4141C121.562 74.4141 119.727 76.25 119.727 78.5156C119.727 78.8281 119.766 79.1406 119.844 79.4531C116.445 79.2578 113.398 77.6172 111.367 75.1562C111.016 75.7422 110.82 76.4453 110.82 77.2266C110.82 78.6328 111.523 79.8828 112.656 80.625C111.992 80.5859 111.328 80.4297 110.781 80.1172V80.1562C110.781 82.1484 112.188 83.7891 116.062 84.1797C113.75 84.2578 113.359 84.3359 113.008 84.3359C112.734 84.3359 112.5 84.2969 112.227 84.2578C112.734 85.8984 114.258 87.0703 116.055 87.1094C114.648 88.2031 112.891 88.8672 110.977 88.8672C110.625 88.8672 110.312 88.8281 110 88.7891C111.797 89.9609 113.945 90.625 116.289 90.625C123.828 90.625 127.93 84.4141 127.93 78.9844C127.93 78.7891 127.93 78.6328 127.93 78.4375Z" fill="url(#paint2_linear)"/> <defs> <linearGradient id="paint0_linear_sclwl" x1="137.667" y1="33.4445" x2="109.486" y2="62.2514" gradientUnits="userSpaceOnUse"> <stop stop-color="#E3280E"/> <stop offset="1" stop-color="#E30E0E"/> </linearGradient> <linearGradient id="paint1_linear_swwl" x1="93.8998" y1="73.3444" x2="78.4998" y2="89.4444" gradientUnits="userSpaceOnUse"> <stop stop-color="#5F0EE3"/> <stop offset="0.713476" stop-color="#FF0000"/> <stop offset="1" stop-color="#FF5C00"/> </linearGradient> <linearGradient id="paint2_linear" x1="136.667" y1="68.4445" x2="108.674" y2="93.3272" gradientUnits="userSpaceOnUse"> <stop stop-color="#0E96E3"/> <stop offset="1" stop-color="#0EBDE3"/> </linearGradient> </defs> </svg>',
			'addPage'               => '<svg viewBox="0 0 17 17"><path d="M12.1667 9.66667H13.8333V12.1667H16.3333V13.8333H13.8333V16.3333H12.1667V13.8333H9.66667V12.1667H12.1667V9.66667ZM2.16667 0.5H13.8333C14.7583 0.5 15.5 1.24167 15.5 2.16667V8.66667C14.9917 8.375 14.4333 8.16667 13.8333 8.06667V2.16667H2.16667V13.8333H8.06667C8.16667 14.4333 8.375 14.9917 8.66667 15.5H2.16667C1.24167 15.5 0.5 14.7583 0.5 13.8333V2.16667C0.5 1.24167 1.24167 0.5 2.16667 0.5ZM3.83333 3.83333H12.1667V5.5H3.83333V3.83333ZM3.83333 7.16667H12.1667V8.06667C11.4583 8.18333 10.8083 8.45 10.2333 8.83333H3.83333V7.16667ZM3.83333 10.5H8V12.1667H3.83333V10.5Z"/></svg>',
			'addWidget'             => '<svg viewBox="0 0 15 16"><path d="M0 15.5H6.66667V8.83333H0V15.5ZM1.66667 10.5H5V13.8333H1.66667V10.5ZM0 7.16667H6.66667V0.5H0V7.16667ZM1.66667 2.16667H5V5.5H1.66667V2.16667ZM8.33333 0.5V7.16667H15V0.5H8.33333ZM13.3333 5.5H10V2.16667H13.3333V5.5ZM12.5 11.3333H15V13H12.5V15.5H10.8333V13H8.33333V11.3333H10.8333V8.83333H12.5V11.3333Z"/></svg>',
			'plus'                  => '<svg width="13" height="12" viewBox="0 0 13 12"><path d="M12.3327 6.83332H7.33268V11.8333H5.66602V6.83332H0.666016V5.16666H5.66602V0.166656H7.33268V5.16666H12.3327V6.83332Z"/></svg>',
			'eye1'                  => '<svg width="20" height="17" viewBox="0 0 20 17"><path d="M9.85801 5.5L12.4997 8.13333V8C12.4997 7.33696 12.2363 6.70107 11.7674 6.23223C11.2986 5.76339 10.6627 5.5 9.99967 5.5H9.85801ZM6.27467 6.16667L7.56634 7.45833C7.52467 7.63333 7.49967 7.80833 7.49967 8C7.49967 8.66304 7.76307 9.29893 8.23191 9.76777C8.70075 10.2366 9.33663 10.5 9.99967 10.5C10.183 10.5 10.3663 10.475 10.5413 10.4333L11.833 11.725C11.2747 12 10.658 12.1667 9.99967 12.1667C8.8946 12.1667 7.8348 11.7277 7.0534 10.9463C6.27199 10.1649 5.83301 9.10507 5.83301 8C5.83301 7.34167 5.99967 6.725 6.27467 6.16667ZM1.66634 1.55833L3.56634 3.45833L3.94134 3.83333C2.56634 4.91667 1.48301 6.33333 0.833008 8C2.27467 11.6583 5.83301 14.25 9.99967 14.25C11.2913 14.25 12.5247 14 13.6497 13.55L14.008 13.9L16.4413 16.3333L17.4997 15.275L2.72467 0.5L1.66634 1.55833ZM9.99967 3.83333C11.1047 3.83333 12.1645 4.27232 12.946 5.05372C13.7274 5.83512 14.1663 6.89493 14.1663 8C14.1663 8.53333 14.058 9.05 13.8663 9.51667L16.308 11.9583C17.558 10.9167 18.558 9.55 19.1663 8C17.7247 4.34167 14.1663 1.75 9.99967 1.75C8.83301 1.75 7.71634 1.95833 6.66634 2.33333L8.47467 4.125C8.94967 3.94167 9.45801 3.83333 9.99967 3.83333Z"/></svg>',

			'eyePreview'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M569.354 231.631C512.97 135.949 407.81 72 288 72 168.14 72 63.004 135.994 6.646 231.631a47.999 47.999 0 0 0 0 48.739C63.031 376.051 168.19 440 288 440c119.86 0 224.996-63.994 281.354-159.631a47.997 47.997 0 0 0 0-48.738zM288 392c-102.556 0-192.091-54.701-240-136 44.157-74.933 123.677-127.27 216.162-135.007C273.958 131.078 280 144.83 280 160c0 30.928-25.072 56-56 56s-56-25.072-56-56l.001-.042C157.794 179.043 152 200.844 152 224c0 75.111 60.889 136 136 136s136-60.889 136-136c0-31.031-10.4-59.629-27.895-82.515C451.704 164.638 498.009 205.106 528 256c-47.908 81.299-137.444 136-240 136z"/></svg>',

			'facebookShare'         => '<svg viewBox="0 0 448 512"><path fill="currentColor" d="M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h137.25V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.27c-30.81 0-40.42 19.12-40.42 38.73V256h68.78l-11 71.69h-57.78V480H400a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48z"></path></svg>',
			'twitterShare'          => '<svg viewBox="0 0 512 512"><path fill="currentColor" d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-26.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path></svg>',
			'linkedinShare'         => '<svg viewBox="0 0 448 512"><path fill="currentColor" d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C26.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"></path></svg>',
			'mailShare'             => '<svg viewBox="0 0 512 512"><path fill="currentColor" d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"></path></svg>',

			'successNotification'   => '<svg viewBox="0 0 20 20"><path d="M10 0C4.5 0 0 4.5 0 10C0 15.5 4.5 20 10 20C15.5 20 20 15.5 20 10C20 4.5 15.5 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z"/></svg>',
			'errorNotification'     => '<svg viewBox="0 0 20 20"><path d="M9.99997 0C4.47997 0 -3.05176e-05 4.48 -3.05176e-05 10C-3.05176e-05 15.52 4.47997 20 9.99997 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99997 0ZM11 15H8.99997V13H11V15ZM11 11H8.99997V5H11V11Z"/></svg>',
			'messageNotification'   => '<svg viewBox="0 0 20 20"><path d="M11.0001 7H9.00012V5H11.0001V7ZM11.0001 15H9.00012V9H11.0001V15ZM10.0001 0C8.6869 0 7.38654 0.258658 6.17329 0.761205C4.96003 1.26375 3.85764 2.00035 2.92905 2.92893C1.05369 4.8043 0.00012207 7.34784 0.00012207 10C0.00012207 12.6522 1.05369 15.1957 2.92905 17.0711C3.85764 17.9997 4.96003 18.7362 6.17329 19.2388C7.38654 19.7413 8.6869 20 10.0001 20C12.6523 20 15.1958 18.9464 17.0712 17.0711C18.9466 15.1957 20.0001 12.6522 20.0001 10C20.0001 8.68678 19.7415 7.38642 19.2389 6.17317C18.7364 4.95991 17.9998 3.85752 17.0712 2.92893C16.1426 2.00035 15.0402 1.26375 13.827 0.761205C12.6137 0.258658 11.3133 0 10.0001 0Z"/></svg>',

			'albumsPreview'         => '<svg width="63" height="65" viewBox="0 0 63 65" fill="none"><rect x="13.6484" y="10.2842" width="34.7288" height="34.7288" rx="1.44703" fill="#8C8F9A"/> <g filter="url(#filter0_dddalbumsPreview)"><rect x="22.1484" y="5.21962" width="34.7288" height="34.7288" rx="1.44703" transform="rotate(8 22.1484 5.21962)" fill="white"/> </g><path d="M29.0485 23.724L18.9288 28.1468L17.2674 39.9686L51.6582 44.802L52.2623 40.5031L29.0485 23.724Z" fill="#B5E5FF"/> <path d="M44.9106 25.2228L17.7194 36.7445L17.2663 39.9687L51.6571 44.802L53.4696 31.9054L44.9106 25.2228Z" fill="#43A6DB"/> <circle cx="42.9495" cy="18.3718" r="2.89406" transform="rotate(8 42.9495 18.3718)" fill="#43A6DB"/> <g filter="url(#filter1_dddalbumsPreview)"> <rect x="42.4766" y="33.9054" width="16.875" height="16.875" rx="8.4375" fill="white"/> <path d="M54.1953 42.8116H51.3828V45.6241H50.4453V42.8116H47.6328V41.8741H50.4453V39.0616H51.3828V41.8741H54.1953V42.8116Z" fill="#0068A0"/> </g> <defs> <filter id="filter0_dddalbumsPreview" x="0.86108" y="0.342124" width="58.3848" height="57.6613" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dx="-7.23516" dy="4.3411"/> <feGaussianBlur stdDeviation="4.70286"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="2.89406"/> <feGaussianBlur stdDeviation="1.44703"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="filter1_dddalbumsPreview" x="25.8357" y="28.8408" width="36.4099" height="35.6864" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dx="-7.23516" dy="4.3411"/> <feGaussianBlur stdDeviation="4.70286"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="2.89406"/> <feGaussianBlur stdDeviation="1.44703"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> </defs> </svg>',
			'featuredPostPreview'   => '<svg width="47" height="48" viewBox="0 0 47 48" fill="none"> <g filter="url(#filter0_ddfeaturedpos)"> <rect x="2.09375" y="1.84264" width="34.7288" height="34.7288" rx="1.44703" fill="white"/> </g> <path d="M11.4995 19.2068L2.09375 24.9949L2.09375 36.9329H36.8225V32.5918L11.4995 19.2068Z" fill="#B5E5FF"/> <path d="M27.4168 18.4833L2.09375 33.6772V36.933H36.8225V23.9097L27.4168 18.4833Z" fill="#43A6DB"/> <circle cx="24.523" cy="11.9718" r="2.89406" fill="#43A6DB"/> <g filter="url(#filter1_ddfeaturedpos)"> <rect x="26.0312" y="25.2824" width="16.875" height="16.875" rx="8.4375" fill="white"/> <path d="M37.75 34.1886H34.9375V37.0011H34V34.1886H31.1875V33.2511H34V30.4386H34.9375V33.2511H37.75V34.1886Z" fill="#0068A0"/> </g> <defs> <filter id="filter0_ddfeaturedpos" x="0.09375" y="0.842636" width="40.7288" height="40.7288" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dx="1" dy="2"/> <feGaussianBlur stdDeviation="1.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow" result="shape"/> </filter> <filter id="filter1_ddfeaturedpos" x="26.0312" y="24.2824" width="22.875" height="22.875" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dx="1" dy="2"/> <feGaussianBlur stdDeviation="1.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow" result="shape"/> </filter> </defs> </svg>',
			'issueSinglePreview'    => '<svg width="27" height="18" viewBox="0 0 27 18" fill="none"> <line x1="3.22082" y1="2.84915" x2="8.91471" y2="8.54304" stroke="#8C8F9A" stroke-width="3"/> <path d="M3.10938 8.65422L8.80327 2.96033" stroke="#8C8F9A" stroke-width="3"/> <line x1="18.3107" y1="2.84915" x2="26.0046" y2="8.54304" stroke="#8C8F9A" stroke-width="3"/> <path d="M18.1992 8.65422L23.8931 2.96033" stroke="#8C8F9A" stroke-width="3"/> <line x1="8.64062" y1="16.3863" x2="18.0351" y2="16.3863" stroke="#8C8F9A" stroke-width="3"/> </svg>',
			'playButton'            => '<svg viewBox="0 0 448 512"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"></path></svg>',
			'spinner'               => '<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve"><path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h6.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"/></path></svg>',
			'rocket'                => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.9411 18.4918L9.58281 15.3001C10.8911 14.8168 12.1161 14.1668 13.2495 13.4085L10.9411 18.4918ZM4.69948 10.4168L1.50781 9.05846L6.59115 6.75013C5.83281 7.88346 5.18281 9.10846 4.69948 10.4168ZM18.0078 1.9918C18.0078 1.9918 13.8828 0.224296 9.16615 4.9418C7.34115 6.7668 6.24948 8.77513 5.54115 10.5335C5.30781 11.1585 5.46615 11.8418 5.92448 12.3085L7.69948 14.0751C8.15781 14.5418 8.84115 14.6918 9.46615 14.4585C11.5649 13.6582 13.4706 12.4228 15.0578 10.8335C19.7745 6.1168 18.0078 1.9918 18.0078 1.9918ZM12.1161 7.88346C11.4661 7.23346 11.4661 6.17513 12.1161 5.52513C12.7661 4.87513 13.8245 4.87513 14.4745 5.52513C15.1161 6.17513 15.1245 7.23346 14.4745 7.88346C13.8245 8.53346 12.7661 8.53346 12.1161 7.88346ZM6.03297 17.5001L8.23281 15.3001C7.94948 15.2251 7.67448 15.1001 7.42448 14.9251L4.85797 17.5001H6.03297ZM2.49963 17.5001H3.67463L6.81615 14.3668L5.63281 13.1918L2.49963 16.3251V17.5001ZM2.49963 15.1418L5.07448 12.5751C4.89948 12.3251 4.77448 12.0585 4.69948 11.7668L2.49963 13.9668V15.1418Z" fill="white"/></svg>',
			'follow'                => '<svg viewBox="0 0 24 24"><path d="M20 18.5H4C3.46957 18.5 2.96086 18.2893 2.58579 17.9142C2.21071 17.5391 2 17.0304 2 16.5V7.5C2 6.96957 2.21071 6.46086 2.58579 6.08579C2.96086 5.71071 3.46957 5.5 4 5.5H20C20.5304 5.5 21.0391 5.71071 21.4142 6.08579C21.7893 6.46086 22 6.96957 22 7.5V16.5C22 17.0304 21.7893 17.5391 21.4142 17.9142C21.0391 18.2893 20.5304 18.5 20 18.5ZM4 7.5V16.5H20V7.5H4Z" fill="#141B38"/><path d="M9 13.75C9 13.1977 9.44772 12.75 10 12.75H14C14.5523 12.75 15 13.1977 15 13.75V15H9V13.75Z" fill="#141B38"/><path d="M13.5 10.5C13.5 11.3284 12.8284 12 12 12C11.1716 12 10.5 11.3284 10.5 10.5C10.5 9.67157 11.1716 9 12 9C12.8284 9 13.5 9.67157 13.5 10.5Z" fill="#141B38"/></svg>',
			'picture'               => '<svg viewBox="0 0 24 24" fill="none"><path d="M8.5 13.5L11 16.5L14.5 12L19 18H5L8.5 13.5ZM21 19V5C21 4.46957 20.7893 3.96086 20.4142 3.58579C20.0391 3.21071 19.5304 3 19 3H5C4.46957 3 3.96086 3.21071 3.58579 3.58579C3.21071 3.96086 3 4.46957 3 5V19C3 19.5304 3.21071 20.0391 3.58579 20.4142C3.96086 20.7893 4.46957 21 5 21H19C19.5304 21 20.0391 20.7893 20.4142 20.4142C20.7893 20.0391 21 19.5304 21 19Z"/></svg>',
			'caption'               => '<svg viewBox="0 0 24 24" fill="none"><path d="M5 3C3.89 3 3 3.89 3 5V19C3 20.11 3.89 21 5 21H19C20.11 21 21 20.11 21 19V5C21 3.89 20.11 3 19 3H5ZM5 5H19V19H5V5ZM7 7V9H17V7H7ZM7 11V13H17V11H7ZM7 15V17H14V15H7Z"/></svg>',
			'heart'                 => '<svg viewBox="0 0 24 24"><path d="M16.5 3C14.76 3 13.09 3.81 12 5.09C10.91 3.81 9.24 3 7.5 3C4.42 3 2 5.42 2 8.5C2 12.28 5.4 15.36 10.55 20.04L12 21.35L13.45 20.03C18.6 15.36 22 12.28 22 8.5C22 5.42 19.58 3 16.5 3ZM12.1 18.55L12 18.65L11.9 18.55C7.14 14.24 4 11.39 4 8.5C4 6.5 5.5 5 7.5 5C9.04 5 10.54 5.99 11.07 7.36H12.94C13.46 5.99 14.96 5 16.5 5C18.5 5 20 6.5 20 8.5C20 11.39 16.86 14.24 12.1 18.55Z"/></svg>',
			'sort'                  => '<svg viewBox="0 0 24 24"><path d="M7.73062 10.9999C7.51906 10.9999 7.40314 10.7535 7.53803 10.5906L11.8066 5.43267C11.9066 5.31186 12.0918 5.31186 12.1918 5.43267L16.4604 10.5906C16.5953 10.7535 16.4794 10.9999 16.2678 10.9999H7.73062Z" fill="#141B38"/><path d="M7.80277 13C7.58005 13 7.4685 13.2693 7.626 13.4268L11.8224 17.6232C11.9201 17.7209 12.0784 17.7209 12.176 17.6232L16.3724 13.4268C16.5299 13.2693 16.4184 13 16.1957 13H7.80277Z" fill="#141B38"/></svg>',
			'shop'                  => '<svg viewBox="0 0 24 24"><path d="M11 9H13V6H16V4H13V1H11V4H8V6H11V9ZM7 18C5.9 18 5.01 18.9 5.01 20C5.01 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM17 18C15.9 18 15.01 18.9 15.01 20C15.01 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18ZM8.1 13H15.55C16.3 13 16.96 12.59 17.3 11.97L21.16 4.96L19.42 4L15.55 11H8.53L4.27 2H1V4H3L6.6 11.59L5.25 14.03C4.52 15.37 5.48 17 7 17H19V15H7L8.1 13Z" fill="#141B38"/></svg>',
			'headerUser'            => '<svg class="svg-inline--fa fa-user fa-w-16" style="margin-right: 3px;" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="user" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M96 160C96 71.634 167.635 0 256 0s160 71.634 160 160-71.635 160-160 160S96 248.366 96 160zm304 192h-28.556c-71.006 42.713-159.912 42.695-230.888 0H112C50.144 352 0 402.144 0 464v24c0 13.255 10.745 24 24 24h464c13.255 0 24-10.745 24-24v-24c0-61.856-50.144-112-112-112z"></path></svg>',
			'headerPhoto'           => '<svg class="svg-inline--fa fa-image fa-w-16" aria-hidden="true" data-fa-processed="" data-prefix="far" data-icon="image" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 448H48c-26.51 0-48-21.49-48-48V112c0-26.51 21.49-48 48-48h416c26.51 0 48 21.49 48 48v288c0 26.51-21.49 48-48 48zM112 120c-30.928 0-56 25.072-56 56s25.072 56 56 56 56-25.072 56-56-25.072-56-56-56zM64 384h384V272l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L208 320l-55.515-55.515c-4.686-4.686-12.284-4.686-16.971 0L64 336v48z"></path></svg>',
			'imageChooser'          => '<svg viewBox="0 0 18 18" fill="none"><path d="M2.16667 0.5C1.72464 0.5 1.30072 0.675595 0.988155 0.988155C0.675595 1.30072 0.5 1.72464 0.5 2.16667V13.8333C0.5 14.2754 0.675595 14.6993 0.988155 15.0118C1.30072 15.3244 1.72464 15.5 2.16667 15.5H9.74167C9.69167 15.225 9.66667 14.95 9.66667 14.6667C9.66667 14.1 9.76667 13.5333 9.95833 13H2.16667L5.08333 9.25L7.16667 11.75L10.0833 8L11.9417 10.475C12.75 9.95 13.7 9.66667 14.6667 9.66667C14.95 9.66667 15.225 9.69167 15.5 9.74167V2.16667C15.5 1.72464 15.3244 1.30072 15.0118 0.988155C14.6993 0.675595 14.2754 0.5 13.8333 0.5H2.16667ZM13.8333 11.3333V13.8333H11.3333V15.5H13.8333V18H15.5V15.5H18V13.8333H15.5V11.3333H13.8333Z"/></svg>',

			'usertimelineIcon'      => '<svg width="260" height="126" viewBox="0 0 260 126" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#usrtimlineclip0)"> <g filter="url(#usrtimlinefilter0_ddd)"> <g clip-path="url(#usrtimlineclip1)"> <rect x="64" y="23" width="131" height="113" rx="2" fill="white"/> <rect x="112.027" y="38" width="46" height="6" rx="1" fill="#DCDDE1"/> <rect x="112.027" y="49" width="28" height="6" rx="1" fill="#DCDDE1"/> <g clip-path="url(#usrtimlineclip2)"> <rect x="133.027" y="121" width="48" height="48" rx="1" fill="#F9BBA0"/> </g> <g clip-path="url(#usrtimlineclip3)"> <rect x="133.027" y="67" width="48" height="48" fill="#43A6DB"/> <circle cx="123.527" cy="101.5" r="40.5" fill="#86D0F9"/> </g> <g clip-path="url(#usrtimlineclip4)"> <rect x="79.0273" y="121" width="48" height="48" fill="#B6DDAD"/> </g> <g clip-path="url(#usrtimlineclip5)"> <rect x="79.0273" y="67" width="48" height="48" fill="#F6966B"/> <path d="M88.4756 84L126.476 122H57.0273L88.4756 84Z" fill="#F9BBA0"/> </g> <circle cx="92.0273" cy="45" r="10" fill="#DCDDE1"/> <circle cx="92.0273" cy="45" r="12" stroke="url(#usrtimlinepaint0_linear)"/> </g> </g> </g> <defs> <filter id="usrtimlinefilter0_ddd" x="51" y="16" width="157" height="139" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <linearGradient id="usrtimlinepaint0_linear" x1="88.5773" y1="78.9" x2="139.127" y2="27.3" gradientUnits="userSpaceOnUse"> <stop stop-color="white"/> <stop offset="0.147864" stop-color="#F6640E"/> <stop offset="0.443974" stop-color="#BA03A7"/> <stop offset="0.733337" stop-color="#6A01B9"/> <stop offset="1" stop-color="#6B01B9"/> </linearGradient> <clipPath id="usrtimlineclip0"> <rect width="259.056" height="126" fill="white"/> </clipPath> <clipPath id="usrtimlineclip1"> <rect x="64" y="23" width="131" height="113" rx="2" fill="white"/> </clipPath> <clipPath id="usrtimlineclip2"> <rect x="133.027" y="121" width="48" height="48" rx="1" fill="white"/> </clipPath> <clipPath id="usrtimlineclip3"> <rect x="133.027" y="67" width="48" height="48" rx="1" fill="white"/> </clipPath> <clipPath id="usrtimlineclip4"> <rect x="79.0273" y="121" width="48" height="48" rx="1" fill="white"/> </clipPath> <clipPath id="usrtimlineclip5"> <rect x="79.0273" y="67" width="48" height="48" rx="1" fill="white"/> </clipPath> </defs> </svg>',
			'publichashtagIcon'     => '<svg width="260" height="126" viewBox="0 0 260 126" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#hashtagiconclip0)"> <g filter="url(#hashtagiconfilter0_ddd)"> <rect x="119.327" y="12.3203" width="80" height="91" rx="2" transform="rotate(4 119.327 12.3203)" fill="white"/> </g> <g clip-path="url(#hashtagiconclip1)"> <rect x="119.327" y="12.3203" width="80" height="80" transform="rotate(4 119.327 12.3203)" fill="#0096CC"/> </g> <path d="M130.918 88.5016L128.971 88.3655L129.441 86.6958C129.453 86.6464 129.454 86.5951 129.444 86.5452C129.435 86.4954 129.414 86.4482 129.385 86.4069C129.355 86.3657 129.317 86.3313 129.273 86.3062C129.229 86.2811 129.18 86.2659 129.129 86.2616L128.427 86.2125C128.347 86.2049 128.265 86.2255 128.198 86.2709C128.131 86.3163 128.081 86.3837 128.058 86.4616L127.572 88.2676L125.678 88.1352L126.147 86.4654C126.159 86.4172 126.16 86.3671 126.151 86.3182C126.142 86.2694 126.123 86.223 126.095 86.182C126.067 86.1411 126.031 86.1066 125.988 86.0808C125.946 86.055 125.899 86.0384 125.849 86.0322L125.148 85.9832C125.067 85.9755 124.986 85.9962 124.918 86.0416C124.851 86.087 124.801 86.1544 124.778 86.2322L124.299 88.0388L122.194 87.8916C122.112 87.8842 122.03 87.9058 121.963 87.9526C121.895 87.9994 121.846 88.0684 121.824 88.1477L121.631 88.8392C121.617 88.89 121.614 88.9433 121.624 88.9953C121.633 89.0472 121.654 89.0964 121.685 89.1391C121.716 89.1819 121.756 89.2172 121.802 89.2424C121.848 89.2676 121.9 89.282 121.952 89.2846L123.899 89.4208L123.128 92.1867L121.023 92.0396C120.941 92.0322 120.859 92.0537 120.791 92.1005C120.724 92.1473 120.675 92.2164 120.653 92.2957L120.46 92.9871C120.446 93.038 120.443 93.0913 120.452 93.1432C120.462 93.1952 120.483 93.2443 120.513 93.2871C120.544 93.3299 120.584 93.3652 120.631 93.3904C120.677 93.4156 120.728 93.43 120.781 93.4326L122.742 93.5697L122.273 95.2394C122.26 95.2896 122.259 95.3419 122.269 95.3926C122.28 95.4432 122.301 95.491 122.332 95.5325C122.362 95.5741 122.402 95.6083 122.447 95.6328C122.493 95.6573 122.543 95.6715 122.595 95.6744L123.296 95.7234C123.375 95.7269 123.452 95.7041 123.516 95.6588C123.579 95.6135 123.626 95.5481 123.649 95.4731L124.142 93.6676L126.036 93.8L125.566 95.4698C125.555 95.5179 125.553 95.5681 125.562 95.617C125.571 95.6658 125.59 95.7122 125.618 95.7531C125.646 95.7941 125.683 95.8286 125.725 95.8544C125.767 95.8802 125.815 95.8968 125.864 95.903L126.566 95.952C126.647 95.9597 126.728 95.939 126.795 95.8936C126.862 95.8482 126.912 95.7808 126.935 95.703L127.432 93.8977L129.536 94.0448C129.618 94.0522 129.7 94.0306 129.768 93.9839C129.836 93.9371 129.885 93.868 129.907 93.7887L130.096 93.097C130.11 93.0462 130.113 92.9928 130.104 92.9409C130.094 92.889 130.073 92.8398 130.043 92.797C130.012 92.7542 129.972 92.719 129.925 92.6938C129.879 92.6686 129.828 92.6542 129.775 92.6515L127.818 92.5147L128.586 89.7485L130.69 89.8956C130.772 89.903 130.854 89.8814 130.922 89.8347C130.989 89.7879 131.039 89.7188 131.061 89.6395L131.253 88.948C131.268 88.8961 131.27 88.8414 131.26 88.7883C131.25 88.7353 131.228 88.6852 131.196 88.642C131.164 88.5989 131.122 88.5637 131.073 88.5394C131.025 88.515 130.972 88.5021 130.918 88.5016ZM126.414 92.4166L124.52 92.2841L125.292 89.5181L127.186 89.6506L126.414 92.4166Z" fill="#0068A0"/> <rect x="138.037" y="88.8115" width="29" height="7" rx="1" transform="rotate(4 138.037 88.8115)" fill="#86D0F9"/> <g filter="url(#hashtagiconfilter1_ddd)"> <rect x="119.327" y="12.3203" width="80" height="91" rx="2" transform="rotate(4 119.327 12.3203)" fill="white"/> </g> <g clip-path="url(#hashtagiconclip2)"> <rect x="119.327" y="12.3203" width="80" height="80" transform="rotate(4 119.327 12.3203)" fill="#0096CC"/> <circle cx="126.556" cy="44.5" r="46.5" fill="#0068A0"/> </g> <path d="M130.918 88.5016L128.971 88.3655L129.441 86.6958C129.453 86.6464 129.454 86.5951 129.444 86.5452C129.435 86.4954 129.414 86.4482 129.385 86.4069C129.355 86.3657 129.317 86.3313 129.273 86.3062C129.229 86.2811 129.18 86.2659 129.129 86.2616L128.427 86.2125C128.347 86.2049 128.265 86.2255 128.198 86.2709C128.131 86.3163 128.081 86.3837 128.058 86.4616L127.572 88.2676L125.678 88.1352L126.147 86.4654C126.159 86.4172 126.16 86.3671 126.151 86.3182C126.142 86.2694 126.123 86.223 126.095 86.182C126.067 86.1411 126.031 86.1066 125.988 86.0808C125.946 86.055 125.899 86.0384 125.849 86.0322L125.148 85.9832C125.067 85.9755 124.986 85.9962 124.918 86.0416C124.851 86.087 124.801 86.1544 124.778 86.2322L124.299 88.0388L122.194 87.8916C122.112 87.8842 122.03 87.9058 121.963 87.9526C121.895 87.9994 121.846 88.0684 121.824 88.1477L121.631 88.8392C121.617 88.89 121.614 88.9433 121.624 88.9953C121.633 89.0472 121.654 89.0964 121.685 89.1391C121.716 89.1819 121.756 89.2172 121.802 89.2424C121.848 89.2676 121.9 89.282 121.952 89.2846L123.899 89.4208L123.128 92.1867L121.023 92.0396C120.941 92.0322 120.859 92.0537 120.791 92.1005C120.724 92.1473 120.675 92.2164 120.653 92.2957L120.46 92.9871C120.446 93.038 120.443 93.0913 120.452 93.1432C120.462 93.1952 120.483 93.2443 120.513 93.2871C120.544 93.3299 120.584 93.3652 120.631 93.3904C120.677 93.4156 120.728 93.43 120.781 93.4326L122.742 93.5697L122.273 95.2394C122.26 95.2896 122.259 95.3419 122.269 95.3926C122.28 95.4432 122.301 95.491 122.332 95.5325C122.362 95.5741 122.402 95.6083 122.447 95.6328C122.493 95.6573 122.543 95.6715 122.595 95.6744L123.296 95.7234C123.375 95.7269 123.452 95.7041 123.516 95.6588C123.579 95.6135 123.626 95.5481 123.649 95.4731L124.142 93.6676L126.036 93.8L125.566 95.4698C125.555 95.5179 125.553 95.5681 125.562 95.617C125.571 95.6658 125.59 95.7122 125.618 95.7531C125.646 95.7941 125.683 95.8286 125.725 95.8544C125.767 95.8802 125.815 95.8968 125.864 95.903L126.566 95.952C126.647 95.9597 126.728 95.939 126.795 95.8936C126.862 95.8482 126.912 95.7808 126.935 95.703L127.432 93.8977L129.536 94.0448C129.618 94.0522 129.7 94.0306 129.768 93.9839C129.836 93.9371 129.885 93.868 129.907 93.7887L130.096 93.097C130.11 93.0462 130.113 92.9928 130.104 92.9409C130.094 92.889 130.073 92.8398 130.043 92.797C130.012 92.7542 129.972 92.719 129.925 92.6938C129.879 92.6686 129.828 92.6542 129.775 92.6515L127.818 92.5147L128.586 89.7485L130.69 89.8956C130.772 89.903 130.854 89.8814 130.922 89.8347C130.989 89.7879 131.039 89.7188 131.061 89.6395L131.253 88.948C131.268 88.8961 131.27 88.8414 131.26 88.7883C131.25 88.7353 131.228 88.6852 131.196 88.642C131.164 88.5989 131.122 88.5637 131.073 88.5394C131.025 88.515 130.972 88.5021 130.918 88.5016ZM126.414 92.4166L124.52 92.2841L125.292 89.5181L127.186 89.6506L126.414 92.4166Z" fill="#0068A0"/> <rect x="138.037" y="88.8115" width="29" height="7" rx="1" transform="rotate(4 138.037 88.8115)" fill="#86D0F9"/> <g filter="url(#hashtagiconfilter2_ddd)"> <rect x="65.0557" y="21" width="80" height="91" rx="2" fill="white"/> </g> <g clip-path="url(#hashtagiconclip3)"> <rect x="65.0557" y="21" width="80" height="80" fill="#F6966B"/> <path d="M80.8025 49.333L144.136 112.666H28.3887L80.8025 49.333Z" fill="#F9BBA0"/> </g> <path d="M81.9327 96.187H79.9812L80.3328 94.4887C80.3414 94.4386 80.3391 94.3873 80.3261 94.3382C80.313 94.2892 80.2894 94.2435 80.257 94.2044C80.2246 94.1653 80.1841 94.1337 80.1383 94.1118C80.0925 94.0898 80.0425 94.078 79.9917 94.0773H79.2885C79.2072 94.0753 79.1277 94.1015 79.0636 94.1515C78.9995 94.2015 78.9547 94.2722 78.9368 94.3515L78.5782 96.187H76.6794L77.031 94.4887C77.0395 94.4398 77.0376 94.3896 77.0253 94.3415C77.013 94.2934 76.9907 94.2484 76.9598 94.2095C76.9289 94.1707 76.8902 94.1388 76.8461 94.116C76.802 94.0932 76.7535 94.08 76.704 94.0773H76.0007C75.9194 94.0753 75.84 94.1015 75.7759 94.1515C75.7117 94.2015 75.6669 94.2722 75.6491 94.3515L75.2974 96.187H73.1877C73.1054 96.1854 73.0252 96.2126 72.9609 96.264C72.8967 96.3154 72.8525 96.3877 72.836 96.4683L72.6919 97.1716C72.6813 97.2233 72.6825 97.2767 72.6954 97.3278C72.7083 97.379 72.7325 97.4266 72.7662 97.4671C72.8 97.5076 72.8425 97.54 72.8905 97.5619C72.9385 97.5838 72.9908 97.5946 73.0435 97.5936H74.995L74.4184 100.407H72.3086C72.2263 100.405 72.1461 100.432 72.0818 100.484C72.0176 100.535 71.9734 100.607 71.957 100.688L71.8128 101.391C71.8022 101.443 71.8034 101.496 71.8163 101.547C71.8292 101.599 71.8534 101.646 71.8872 101.687C71.9209 101.727 71.9634 101.76 72.0114 101.782C72.0594 101.803 72.1117 101.814 72.1644 101.813H74.13L73.7784 103.512C73.7696 103.562 73.7722 103.615 73.7858 103.664C73.7995 103.714 73.824 103.761 73.8576 103.8C73.8912 103.839 73.933 103.87 73.9801 103.892C74.0272 103.913 74.0784 103.924 74.13 103.923H74.8333C74.9116 103.921 74.9869 103.893 75.0474 103.843C75.1079 103.793 75.1501 103.725 75.1673 103.649L75.533 101.813H77.4318L77.0802 103.512C77.0717 103.56 77.0736 103.611 77.0859 103.659C77.0982 103.707 77.1205 103.752 77.1514 103.791C77.1823 103.829 77.221 103.861 77.2651 103.884C77.3092 103.907 77.3577 103.92 77.4072 103.923H78.1105C78.1918 103.925 78.2712 103.899 78.3354 103.849C78.3995 103.799 78.4443 103.728 78.4621 103.649L78.8313 101.813H80.9411C81.0234 101.815 81.1036 101.788 81.1679 101.736C81.2321 101.685 81.2763 101.612 81.2928 101.532L81.4334 100.829C81.444 100.777 81.4428 100.723 81.4299 100.672C81.417 100.621 81.3928 100.574 81.359 100.533C81.3253 100.493 81.2828 100.46 81.2348 100.438C81.1868 100.416 81.1345 100.406 81.0818 100.407H79.1197L79.6928 97.5936H81.8026C81.8849 97.5952 81.9651 97.568 82.0294 97.5166C82.0936 97.4652 82.1378 97.3929 82.1543 97.3123L82.2984 96.609C82.3093 96.5561 82.3079 96.5014 82.2942 96.4492C82.2806 96.3969 82.2551 96.3485 82.2197 96.3077C82.1844 96.2669 82.1401 96.2348 82.0903 96.2139C82.0405 96.193 81.9866 96.1838 81.9327 96.187ZM77.7132 100.407H75.8143L76.391 97.5936H78.2898L77.7132 100.407Z" fill="#FE544F"/> <rect x="89.0557" y="96" width="29" height="7" rx="1" fill="#FCE1D5"/> <g filter="url(#hashtagiconfilter3_ddd)"> <rect x="65.0557" y="21" width="80" height="91" rx="2" fill="white"/> </g> <g clip-path="url(#hashtagiconclip4)"> <rect x="65.0557" y="21" width="80" height="80" fill="#F6966B"/> <path d="M80.8025 49.333L144.136 112.666H28.3887L80.8025 49.333Z" fill="#F9BBA0"/> </g> <path d="M81.9327 96.187H79.9812L80.3328 94.4887C80.3414 94.4386 80.3391 94.3873 80.3261 94.3382C80.313 94.2892 80.2894 94.2435 80.257 94.2044C80.2246 94.1653 80.1841 94.1337 80.1383 94.1118C80.0925 94.0898 80.0425 94.078 79.9917 94.0773H79.2885C79.2072 94.0753 79.1277 94.1015 79.0636 94.1515C78.9995 94.2015 78.9547 94.2722 78.9368 94.3515L78.5782 96.187H76.6794L77.031 94.4887C77.0395 94.4398 77.0376 94.3896 77.0253 94.3415C77.013 94.2934 76.9907 94.2484 76.9598 94.2095C76.9289 94.1707 76.8902 94.1388 76.8461 94.116C76.802 94.0932 76.7535 94.08 76.704 94.0773H76.0007C75.9194 94.0753 75.84 94.1015 75.7759 94.1515C75.7117 94.2015 75.6669 94.2722 75.6491 94.3515L75.2974 96.187H73.1877C73.1054 96.1854 73.0252 96.2126 72.9609 96.264C72.8967 96.3154 72.8525 96.3877 72.836 96.4683L72.6919 97.1716C72.6813 97.2233 72.6825 97.2767 72.6954 97.3278C72.7083 97.379 72.7325 97.4266 72.7662 97.4671C72.8 97.5076 72.8425 97.54 72.8905 97.5619C72.9385 97.5838 72.9908 97.5946 73.0435 97.5936H74.995L74.4184 100.407H72.3086C72.2263 100.405 72.1461 100.432 72.0818 100.484C72.0176 100.535 71.9734 100.607 71.957 100.688L71.8128 101.391C71.8022 101.443 71.8034 101.496 71.8163 101.547C71.8292 101.599 71.8534 101.646 71.8872 101.687C71.9209 101.727 71.9634 101.76 72.0114 101.782C72.0594 101.803 72.1117 101.814 72.1644 101.813H74.13L73.7784 103.512C73.7696 103.562 73.7722 103.615 73.7858 103.664C73.7995 103.714 73.824 103.761 73.8576 103.8C73.8912 103.839 73.933 103.87 73.9801 103.892C74.0272 103.913 74.0784 103.924 74.13 103.923H74.8333C74.9116 103.921 74.9869 103.893 75.0474 103.843C75.1079 103.793 75.1501 103.725 75.1673 103.649L75.533 101.813H77.4318L77.0802 103.512C77.0717 103.56 77.0736 103.611 77.0859 103.659C77.0982 103.707 77.1205 103.752 77.1514 103.791C77.1823 103.829 77.221 103.861 77.2651 103.884C77.3092 103.907 77.3577 103.92 77.4072 103.923H78.1105C78.1918 103.925 78.2712 103.899 78.3354 103.849C78.3995 103.799 78.4443 103.728 78.4621 103.649L78.8313 101.813H80.9411C81.0234 101.815 81.1036 101.788 81.1679 101.736C81.2321 101.685 81.2763 101.612 81.2928 101.532L81.4334 100.829C81.444 100.777 81.4428 100.723 81.4299 100.672C81.417 100.621 81.3928 100.574 81.359 100.533C81.3253 100.493 81.2828 100.46 81.2348 100.438C81.1868 100.416 81.1345 100.406 81.0818 100.407H79.1197L79.6928 97.5936H81.8026C81.8849 97.5952 81.9651 97.568 82.0294 97.5166C82.0936 97.4652 82.1378 97.3929 82.1543 97.3123L82.2984 96.609C82.3093 96.5561 82.3079 96.5014 82.2942 96.4492C82.2806 96.3969 82.2551 96.3485 82.2197 96.3077C82.1844 96.2669 82.1401 96.2348 82.0903 96.2139C82.0405 96.193 81.9866 96.1838 81.9327 96.187ZM77.7132 100.407H75.8143L76.391 97.5936H78.2898L77.7132 100.407Z" fill="#FE544F"/> <rect x="89.0557" y="96" width="29" height="7" rx="1" fill="#FCE1D5"/> </g> <defs> <filter id="hashtagiconfilter0_ddd" x="100.114" y="5.45508" width="111.884" height="122.09" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="hashtagiconfilter1_ddd" x="100.114" y="5.45508" width="111.884" height="122.09" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="hashtagiconfilter2_ddd" x="52.0557" y="14" width="106" height="117" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="hashtagiconfilter3_ddd" x="52.0557" y="14" width="106" height="117" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6"/> <feGaussianBlur stdDeviation="6.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3"/> <feGaussianBlur stdDeviation="3"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <clipPath id="hashtagiconclip0"> <rect width="259.056" height="126" fill="white" transform="translate(0.0556641)"/> </clipPath> <clipPath id="hashtagiconclip1"> <path d="M119.211 13.9829C119.275 13.0647 120.072 12.3724 120.99 12.4366L197.47 17.7846C198.388 17.8488 199.08 18.6452 199.016 19.5634L194.528 83.7401L114.723 78.1595L119.211 13.9829Z" fill="white"/> </clipPath> <clipPath id="hashtagiconclip2"> <path d="M119.211 13.9829C119.275 13.0647 120.072 12.3724 120.99 12.4366L197.47 17.7846C198.388 17.8488 199.08 18.6452 199.016 19.5634L194.528 83.7401L114.723 78.1595L119.211 13.9829Z" fill="white"/> </clipPath> <clipPath id="hashtagiconclip3"> <path d="M65.0557 22.6667C65.0557 21.7462 65.8019 21 66.7223 21H143.389C144.309 21 145.056 21.7462 145.056 22.6667V87H65.0557V22.6667Z" fill="white"/> </clipPath> <clipPath id="hashtagiconclip4"> <path d="M65.0557 22.6667C65.0557 21.7462 65.8019 21 66.7223 21H143.389C144.309 21 145.056 21.7462 145.056 22.6667V87H65.0557V22.6667Z" fill="white"/> </clipPath> </defs> </svg>',
			'taggedpostsIcon'       => '<svg width="260" height="126" viewBox="0 0 260 126" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#taggedpostclip0)"> <g filter="url(#taggedpostfilter0_ddd)"> <g clip-path="url(#taggedpostclip1)"> <rect x="104.316" y="29.0303" width="83.0697" height="84.1212" rx="2.10303" transform="rotate(2 104.316 29.0303)" fill="white"/> <g clip-path="url(#taggedpostclip2)"> <path d="M104.063 23.0957L188.133 26.0315L185.418 103.796L101.348 100.86L104.063 23.0957Z" fill="#59AB46"/> <path d="M119.756 48.194L183.987 117.073L62.3516 112.826L119.756 48.194Z" fill="#76C064"/> </g> <path fill-rule="evenodd" clip-rule="evenodd" d="M113.109 94.8001C114.187 94.6246 115.292 94.7726 116.286 95.2254C117.279 95.6782 118.116 96.4154 118.691 97.3439C119.265 98.2723 119.552 99.3503 119.513 100.441L119.485 101.259C119.467 101.783 119.241 102.278 118.858 102.635C118.474 102.993 117.964 103.183 117.441 103.165C116.917 103.147 116.422 102.921 116.064 102.538C115.997 102.466 115.937 102.391 115.882 102.311C115.342 102.804 114.63 103.067 113.899 103.041C113.158 103.016 112.458 102.697 111.953 102.155C111.447 101.613 111.178 100.892 111.204 100.151C111.23 99.4107 111.549 98.7106 112.091 98.2052C112.633 97.6998 113.353 97.4304 114.094 97.4562C114.834 97.4821 115.535 97.8011 116.04 98.3431C116.545 98.8851 116.815 99.6057 116.789 100.346L116.76 101.164C116.753 101.362 116.826 101.556 116.961 101.701C117.097 101.847 117.285 101.932 117.483 101.939C117.682 101.946 117.875 101.874 118.021 101.738C118.166 101.603 118.252 101.415 118.259 101.216L118.287 100.399C118.317 99.55 118.094 98.7115 117.647 97.9894C117.201 97.2673 116.55 96.6939 115.777 96.3417C115.004 95.9896 114.144 95.8745 113.306 96.011C112.468 96.1475 111.689 96.5295 111.068 97.1086C110.447 97.6878 110.012 98.4381 109.817 99.2647C109.622 100.091 109.677 100.957 109.975 101.752C110.272 102.548 110.799 103.237 111.488 103.733C112.177 104.23 112.998 104.51 113.846 104.54L113.847 104.54C114.6 104.567 115.347 104.395 116.011 104.04C116.31 103.881 116.682 103.994 116.841 104.293C117.001 104.591 116.888 104.963 116.589 105.123C115.733 105.579 114.772 105.801 113.803 105.766L113.825 105.153L113.804 105.766C113.803 105.766 113.803 105.766 113.803 105.766C112.712 105.728 111.657 105.367 110.771 104.729C109.885 104.091 109.208 103.205 108.825 102.182C108.443 101.159 108.373 100.046 108.623 98.9835C108.873 97.9208 109.433 96.956 110.231 96.2114C111.03 95.4668 112.031 94.9757 113.109 94.8001ZM115.563 100.304C115.577 99.888 115.426 99.4838 115.143 99.1798C114.859 98.8757 114.466 98.6967 114.051 98.6822C113.636 98.6677 113.231 98.8189 112.927 99.1024C112.623 99.3859 112.444 99.7786 112.43 100.194C112.415 100.61 112.566 101.014 112.85 101.318C113.133 101.622 113.526 101.801 113.942 101.815C114.357 101.83 114.761 101.679 115.065 101.395C115.369 101.112 115.548 100.719 115.563 100.304Z" fill="#59AB46"/> <rect x="126.717" y="97.5381" width="30.4939" height="7.3606" rx="1.05151" transform="rotate(2 126.717 97.5381)" fill="#B6DDAD"/> </g> </g> <g filter="url(#taggedpostfilter1_ddd)"> <g clip-path="url(#taggedpostclip3)"> <rect x="70.8867" y="10.8984" width="83.0697" height="84.1212" rx="2.10303" transform="rotate(-2 70.8867 10.8984)" fill="white"/> <g clip-path="url(#taggedpostclip4)"> <path d="M70.2217 4.99609L154.292 2.06031L157.007 79.825L72.9373 82.7608L70.2217 4.99609Z" fill="#43A6DB"/> <circle cx="169.299" cy="72.169" r="48.8954" transform="rotate(-2 169.299 72.169)" fill="#0068A0"/> </g> <path fill-rule="evenodd" clip-rule="evenodd" d="M84.2452 75.8962C85.308 75.646 86.4211 75.7165 87.4438 76.0989C88.4665 76.4813 89.3529 77.1583 89.9908 78.0444C90.6287 78.9305 90.9895 79.9859 91.0276 81.0771L91.0562 81.8944C91.0745 82.4183 90.8839 82.928 90.5264 83.3114C90.1689 83.6947 89.6738 83.9204 89.1499 83.9387C88.626 83.957 88.1163 83.7664 87.733 83.4089C87.6615 83.3423 87.5956 83.2709 87.5354 83.1954C87.0315 83.7253 86.3396 84.0368 85.6081 84.0623C84.8674 84.0882 84.1468 83.8188 83.6048 83.3134C83.0628 82.8079 82.7438 82.1079 82.7179 81.3673C82.6921 80.6266 82.9615 79.906 83.4669 79.364C83.9723 78.822 84.6724 78.503 85.413 78.4771C86.1537 78.4513 86.8742 78.7207 87.4162 79.2261C87.9583 79.7315 88.2773 80.4316 88.3031 81.1722L88.3317 81.9896C88.3386 82.1883 88.4242 82.3761 88.5696 82.5117C88.715 82.6473 88.9084 82.7196 89.1071 82.7126C89.3058 82.7057 89.4936 82.6201 89.6292 82.4747C89.7648 82.3293 89.8371 82.136 89.8301 81.9372L89.8016 81.1199C89.772 80.2712 89.4913 79.4504 88.9952 78.7612C88.499 78.072 87.8096 77.5454 87.0142 77.248C86.2188 76.9506 85.353 76.8957 84.5264 77.0904C83.6998 77.285 82.9495 77.7204 82.3703 78.3415C81.7912 78.9625 81.4092 79.7414 81.2727 80.5796C81.1362 81.4177 81.2513 82.2776 81.6034 83.0503C81.9556 83.8231 82.529 84.474 83.2511 84.9209C83.9733 85.3678 84.8117 85.5905 85.6604 85.5608L85.661 85.5608C86.4142 85.5352 87.147 85.3114 87.7851 84.9114C88.0721 84.7314 88.4506 84.8182 88.6306 85.1052C88.8105 85.3922 88.7237 85.7708 88.4367 85.9507C87.6149 86.466 86.6715 86.754 85.7026 86.7869L85.6818 86.1738L85.7032 86.7868C85.703 86.7868 85.7028 86.7869 85.7026 86.7869C84.6116 86.8248 83.5339 86.5385 82.6056 85.9641C81.6771 85.3895 80.9399 84.5526 80.4871 83.559C80.0344 82.5655 79.8864 81.46 80.0619 80.3824C80.2374 79.3047 80.7285 78.3033 81.4731 77.5048C82.2178 76.7063 83.1825 76.1465 84.2452 75.8962ZM87.0771 81.215C87.0626 80.7996 86.8836 80.4069 86.5796 80.1233C86.2755 79.8398 85.8713 79.6887 85.4558 79.7032C85.0403 79.7177 84.6476 79.8966 84.3641 80.2007C84.0806 80.5047 83.9294 80.909 83.944 81.3245C83.9585 81.7399 84.1374 82.1326 84.4415 82.4162C84.7455 82.6997 85.1498 82.8508 85.5652 82.8363C85.9807 82.8218 86.3734 82.6429 86.657 82.3388C86.9405 82.0348 87.0916 81.6305 87.0771 81.215Z" fill="#0068A0"/> <rect x="98.0117" y="77.6768" width="30.4939" height="7.3606" rx="1.05151" transform="rotate(-2 98.0117 77.6768)" fill="#86D0F9"/> </g> </g> </g> <defs> <filter id="taggedpostfilter0_ddd" x="87.7112" y="21.6697" width="113.294" height="114.308" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.30909"/> <feGaussianBlur stdDeviation="6.83485"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.05151"/> <feGaussianBlur stdDeviation="1.05151"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.15454"/> <feGaussianBlur stdDeviation="3.15454"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="taggedpostfilter1_ddd" x="57.217" y="0.638418" width="113.294" height="114.308" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.30909"/> <feGaussianBlur stdDeviation="6.83485"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.05151"/> <feGaussianBlur stdDeviation="1.05151"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.15454"/> <feGaussianBlur stdDeviation="3.15454"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <clipPath id="taggedpostclip0"> <rect width="259.056" height="126" fill="white" transform="translate(0.111328)"/> </clipPath> <clipPath id="taggedpostclip1"> <rect x="104.316" y="29.0303" width="83.0697" height="84.1212" rx="2.10303" transform="rotate(2 104.316 29.0303)" fill="white"/> </clipPath> <clipPath id="taggedpostclip2"> <path d="M104.187 19.5933C104.221 18.626 105.032 17.8692 106 17.903L186.567 20.7164C187.534 20.7502 188.291 21.5617 188.257 22.529L185.896 90.1353L101.826 87.1995L104.187 19.5933Z" fill="white"/> </clipPath> <clipPath id="taggedpostclip3"> <rect x="70.8867" y="10.8984" width="83.0697" height="84.1212" rx="2.10303" transform="rotate(-2 70.8867 10.8984)" fill="white"/> </clipPath> <clipPath id="taggedpostclip4"> <path d="M70.0983 1.49365C70.0645 0.526345 70.8213 -0.285196 71.7886 -0.318975L152.356 -3.13244C153.323 -3.16622 154.134 -2.40945 154.168 -1.44214L156.529 66.1641L72.4591 69.0999L70.0983 1.49365Z" fill="white"/> </clipPath> </defs> </svg>',
			'socialwall1Icon'       => '<svg width="260" height="126" viewBox="0 0 260 126" fill="none" xmlns="http://www.w3.org/2000/svg"> <g filter="url(#social1filter0_ddd)"> <rect x="44.416" y="44.9111" width="42" height="42" rx="2.10303" transform="rotate(-5 44.416 44.9111)" fill="white"/> <path d="M66.2979 54.0796C60.8188 54.559 56.7273 59.4241 57.2092 64.933C57.645 69.914 61.6528 73.7292 66.48 74.0598L65.8699 67.0864L63.3395 67.3078L63.0868 64.4188L65.6171 64.1974L65.4245 61.9959C65.2057 59.4954 66.5698 57.9908 68.8511 57.7912C69.9369 57.6962 71.0892 57.7861 71.0892 57.7861L71.3044 60.2467L70.0492 60.3565C68.8139 60.4646 68.4925 61.2657 68.5614 62.0527L68.7252 63.9255L71.4947 63.6832L71.2991 66.6114L68.978 66.8145L69.5881 73.7878C71.9031 73.2117 73.9359 71.827 75.3195 69.8835C76.7031 67.9401 77.3464 65.566 77.1331 63.1899C76.6512 57.681 71.777 53.6003 66.2979 54.0796Z" fill="#006BFA"/> </g> <g filter="url(#social1filter1_ddd)"> <rect x="83.0967" y="39.1279" width="42" height="42" rx="2.10303" transform="rotate(-3 83.0967 39.1279)" fill="white"/> <path d="M104.886 53.6171C101.89 53.7741 99.6299 56.3334 99.7844 59.2824C99.9414 62.2783 102.454 64.5406 105.45 64.3836C108.399 64.229 110.708 61.7141 110.551 58.7182C110.396 55.7691 107.835 53.4625 104.886 53.6171ZM105.352 62.5111C103.432 62.6117 101.76 61.1504 101.657 59.1843C101.556 57.2651 103.02 55.6394 104.986 55.5363C106.905 55.4357 108.531 56.8995 108.632 58.8188C108.735 60.7848 107.271 62.4105 105.352 62.5111ZM111.71 53.0717C111.673 52.3695 111.082 51.8372 110.38 51.874C109.678 51.9108 109.146 52.502 109.182 53.2041C109.219 53.9063 109.81 54.4386 110.512 54.4018C111.215 54.365 111.747 53.7738 111.71 53.0717ZM115.334 54.1491C115.152 52.4688 114.699 50.9905 113.418 49.8372C112.137 48.6839 110.62 48.3879 108.93 48.3826C107.193 48.3798 101.997 48.6521 100.27 48.8365C98.5894 49.0184 97.1579 49.469 95.9578 50.7523C94.8045 52.0331 94.5085 53.5507 94.5032 55.2408C94.5003 56.9777 94.7726 62.1737 94.957 63.9008C95.139 65.5811 95.5895 67.0126 96.8728 68.2127C98.2005 69.3635 99.6712 69.662 101.361 69.6673C103.098 69.6701 108.294 69.3978 110.021 69.2134C111.702 69.0315 113.18 68.5785 114.333 67.2976C115.484 65.97 115.783 64.4992 115.788 62.8091C115.791 61.0722 115.518 55.8762 115.334 54.1491ZM113.637 64.7525C113.358 65.7059 112.646 66.4473 111.776 66.8684C110.401 67.5037 107.117 67.535 105.619 67.6135C104.074 67.6945 100.805 68.0066 99.418 67.516C98.4621 67.1906 97.7232 66.5252 97.2996 65.6087C96.6667 64.2806 96.6354 60.9965 96.5545 59.4517C96.476 57.9538 96.1638 54.6844 96.652 53.2506C96.9798 52.3416 97.6452 51.6026 98.5618 51.1791C99.8899 50.5462 103.174 50.5149 104.719 50.4339C106.217 50.3554 109.486 50.0433 110.92 50.5314C111.826 50.8125 112.568 51.5247 112.989 52.3944C113.624 53.7693 113.656 57.0534 113.734 58.5514C113.815 60.0961 114.127 63.3655 113.637 64.7525Z" fill="url(#social1paint0_linear)"/> </g> <g filter="url(#social1filter2_ddd)"> <rect x="122.913" y="35.2803" width="42" height="42" rx="2.10303" transform="rotate(2 122.913 35.2803)" fill="white"/> <path d="M153.831 51.3695C153.049 51.6924 152.211 51.8933 151.348 51.9732C152.246 51.4743 152.955 50.6585 153.31 49.6603C152.463 50.131 151.531 50.4487 150.555 50.6147C149.795 49.7277 148.704 49.1892 147.444 49.1453C145.096 49.0633 143.11 50.9151 143.027 53.2836C143.015 53.6234 143.044 53.9546 143.103 54.2669C139.551 53.9627 136.443 52.1432 134.425 49.4811C134.033 50.0978 133.797 50.83 133.77 51.6095C133.718 53.0986 134.421 54.444 135.555 55.234C134.845 55.2093 134.192 54.9863 133.623 54.6663L133.622 54.6963C133.55 56.775 134.968 58.5656 136.913 59.0238C136.278 59.1739 135.617 59.1748 134.982 59.0264C135.224 59.8878 135.729 60.6518 136.428 61.2111C137.126 61.7703 137.982 62.0966 138.875 62.1441C137.318 63.2909 135.417 63.8738 133.485 63.797C133.145 63.7851 132.806 63.7533 132.467 63.7014C134.323 64.987 136.557 65.7755 138.976 65.8599C146.851 66.1349 151.407 59.75 151.605 54.0835C151.611 53.8936 151.617 53.7137 151.614 53.5235C152.475 52.9531 153.221 52.2187 153.831 51.3695Z" fill="#1B90EF"/> </g> <g filter="url(#social1filter3_ddd)"> <rect x="161.295" y="39.9297" width="42" height="42" rx="2.10303" transform="rotate(3 161.295 39.9297)" fill="white"/> <path d="M179.013 64.8913L184.352 62.167L179.327 58.8995L179.013 64.8913ZM190.966 57.677C191.072 58.1532 191.129 58.7871 191.147 59.5891C191.175 60.3917 191.169 61.0823 191.137 61.6815L191.153 62.5235C191.038 64.7105 190.794 66.3099 190.461 67.3238C190.164 68.2095 189.555 68.7583 188.643 68.9609C188.167 69.0661 187.303 69.111 185.982 69.1018C184.68 69.1037 183.49 69.0714 182.391 69.0138L180.8 68.9905C176.616 68.7712 174.018 68.4748 173.004 68.1413C172.119 67.8446 171.57 67.235 171.367 66.3231C171.262 65.847 171.205 65.2131 171.187 64.4111C171.159 63.6085 171.165 62.9179 171.196 62.3187L171.181 61.4767C171.295 59.2897 171.539 57.6903 171.873 56.6764C172.169 55.7907 172.779 55.2418 173.691 55.0393C174.167 54.9341 175.031 54.8892 176.352 54.8984C177.654 54.8965 178.844 54.9288 179.942 54.9864L181.533 55.0097C185.717 55.229 188.315 55.5254 189.329 55.8589C190.215 56.1556 190.764 56.7652 190.966 57.677Z" fill="#EB2121"/> </g> <defs> <filter id="social1filter0_ddd" x="30.7463" y="33.8904" width="72.8401" height="72.8401" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.30909"/> <feGaussianBlur stdDeviation="6.83485"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.05151"/> <feGaussianBlur stdDeviation="1.05151"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.15454"/> <feGaussianBlur stdDeviation="3.15454"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="social1filter1_ddd" x="69.427" y="29.5691" width="71.4799" height="71.4799" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.30909"/> <feGaussianBlur stdDeviation="6.83485"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.05151"/> <feGaussianBlur stdDeviation="1.05151"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.15454"/> <feGaussianBlur stdDeviation="3.15454"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="social1filter2_ddd" x="107.778" y="27.9197" width="70.7796" height="70.7796" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.30909"/> <feGaussianBlur stdDeviation="6.83485"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.05151"/> <feGaussianBlur stdDeviation="1.05151"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.15454"/> <feGaussianBlur stdDeviation="3.15454"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <filter id="social1filter3_ddd" x="145.427" y="32.5691" width="71.4799" height="71.4799" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.30909"/> <feGaussianBlur stdDeviation="6.83485"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.05151"/> <feGaussianBlur stdDeviation="1.05151"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.11 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.15454"/> <feGaussianBlur stdDeviation="3.15454"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect3_dropShadow" result="shape"/> </filter> <linearGradient id="social1paint0_linear" x1="103.683" y1="88.8048" x2="145.491" y2="41.4018" gradientUnits="userSpaceOnUse"> <stop stop-color="white"/> <stop offset="0.147864" stop-color="#F6640E"/> <stop offset="0.443974" stop-color="#BA03A7"/> <stop offset="0.733337" stop-color="#6A01B9"/> <stop offset="1" stop-color="#6B01B9"/> </linearGradient> </defs> </svg>',
			'publichashtagIconFree' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_free506_22296)"><g filter="url(#filter0free_d_506_22296)"><rect x="3" y="3" width="26.1602" height="26.1602" rx="2" fill="#EC352F"/></g><path d="M21.5738 12.7852H19.3434L19.7453 10.8442C19.7552 10.787 19.7525 10.7284 19.7376 10.6723C19.7226 10.6162 19.6957 10.564 19.6587 10.5194C19.6216 10.4747 19.5753 10.4386 19.523 10.4135C19.4707 10.3884 19.4135 10.3749 19.3555 10.374H18.5518C18.4589 10.3718 18.3681 10.4018 18.2948 10.4589C18.2215 10.5161 18.1703 10.5968 18.1499 10.6875L17.74 12.7852H15.5699L15.9718 10.8442C15.9815 10.7883 15.9793 10.731 15.9653 10.676C15.9513 10.621 15.9258 10.5696 15.8905 10.5252C15.8552 10.4808 15.8109 10.4444 15.7605 10.4183C15.7101 10.3923 15.6547 10.3772 15.5981 10.374H14.7943C14.7014 10.3718 14.6106 10.4018 14.5374 10.4589C14.4641 10.5161 14.4129 10.5968 14.3925 10.6875L13.9906 12.7852H11.5794C11.4854 12.7833 11.3937 12.8145 11.3203 12.8732C11.2469 12.9319 11.1963 13.0146 11.1776 13.1067L11.0128 13.9104C11.0007 13.9695 11.0021 14.0305 11.0168 14.089C11.0315 14.1474 11.0592 14.2018 11.0978 14.2482C11.1364 14.2945 11.1849 14.3315 11.2398 14.3565C11.2946 14.3815 11.3544 14.3939 11.4147 14.3927H13.645L12.9859 17.6076H10.5748C10.4807 17.6057 10.389 17.6368 10.3156 17.6956C10.2422 17.7543 10.1917 17.8369 10.1729 17.9291L10.0081 18.7328C9.99607 18.7919 9.99744 18.8529 10.0122 18.9114C10.0269 18.9698 10.0546 19.0242 10.0931 19.0705C10.1317 19.1168 10.1803 19.1539 10.2351 19.1789C10.29 19.2039 10.3497 19.2163 10.41 19.215H12.6564L12.2546 21.156C12.2445 21.2142 12.2474 21.2739 12.2631 21.3309C12.2787 21.3878 12.3067 21.4406 12.3451 21.4855C12.3834 21.5304 12.4312 21.5663 12.485 21.5906C12.5389 21.6149 12.5974 21.6271 12.6564 21.6262H13.4601C13.5496 21.6239 13.6357 21.5919 13.7049 21.5351C13.774 21.4783 13.8223 21.4001 13.8419 21.3128L14.2599 19.215H16.4299L16.0281 21.156C16.0183 21.2119 16.0205 21.2693 16.0346 21.3242C16.0486 21.3792 16.0741 21.4306 16.1094 21.475C16.1447 21.5194 16.189 21.5559 16.2394 21.5819C16.2898 21.608 16.3451 21.6231 16.4018 21.6262H17.2055C17.2984 21.6285 17.3892 21.5985 17.4625 21.5414C17.5358 21.4842 17.587 21.4034 17.6074 21.3128L18.0293 19.215H20.4405C20.5345 19.2169 20.6262 19.1858 20.6997 19.1271C20.7731 19.0683 20.8236 18.9857 20.8424 18.8935L21.0031 18.0898C21.0152 18.0308 21.0138 17.9697 20.9991 17.9113C20.9844 17.8528 20.9567 17.7984 20.9181 17.7521C20.8795 17.7058 20.831 17.6688 20.7762 17.6437C20.7213 17.6187 20.6615 17.6064 20.6013 17.6076H18.3589L19.0139 14.3927H21.4251C21.5191 14.3946 21.6108 14.3634 21.6842 14.3047C21.7577 14.2459 21.8082 14.1633 21.827 14.0712L21.9917 13.2675C22.0042 13.207 22.0025 13.1445 21.987 13.0848C21.9714 13.0251 21.9422 12.9698 21.9018 12.9231C21.8614 12.8765 21.8108 12.8398 21.7539 12.8159C21.697 12.792 21.6354 12.7815 21.5738 12.7852ZM16.7514 17.6076H14.5813L15.2404 14.3927H17.4105L16.7514 17.6076Z" fill="white"/></g><defs><filter id="filter0free_d_506_22296" x="-3" y="0" width="38.1602" height="38.1602" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="3"/><feGaussianBlur stdDeviation="3"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22296"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_506_22296" result="shape"/></filter><clipPath id="clip0_free506_22296"><rect width="32" height="32" fill="white"/></clipPath></defs></svg>',
			'taggedpostsIconFree'   => '<svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0tagged_506_22299)"><g filter="url(#filter0_taggedd_506_22299)"><rect x="3.33398" y="3" width="26.1602" height="26.1602" rx="2" fill="#0096CC"/></g><path fill-rule="evenodd" clip-rule="evenodd" d="M15.0171 9.37969C16.3265 9.11924 17.6837 9.25292 18.9171 9.76381C20.1505 10.2747 21.2047 11.1399 21.9464 12.2499C22.6881 13.3599 23.084 14.665 23.084 16V17C23.084 17.6409 22.8294 18.2556 22.3762 18.7088C21.923 19.1621 21.3083 19.4167 20.6673 19.4167C20.0264 19.4167 19.4117 19.1621 18.9585 18.7088C18.874 18.6244 18.7965 18.5343 18.7261 18.4395C18.0878 19.0655 17.229 19.4167 16.334 19.4167C15.4278 19.4167 14.5588 19.0567 13.918 18.4159C13.2773 17.7752 12.9173 16.9062 12.9173 16C12.9173 15.0938 13.2773 14.2248 13.918 13.584C14.5588 12.9433 15.4278 12.5833 16.334 12.5833C17.2401 12.5833 18.1092 12.9433 18.7499 13.584C19.3907 14.2248 19.7507 15.0938 19.7507 16V17C19.7507 17.2431 19.8472 17.4763 20.0191 17.6482C20.191 17.8201 20.4242 17.9167 20.6673 17.9167C20.9104 17.9167 21.1436 17.8201 21.3155 17.6482C21.4874 17.4763 21.584 17.2431 21.584 17V16C21.584 14.9616 21.2761 13.9466 20.6992 13.0833C20.1223 12.2199 19.3024 11.547 18.3431 11.1496C17.3838 10.7523 16.3282 10.6483 15.3098 10.8509C14.2914 11.0534 13.3559 11.5535 12.6217 12.2877C11.8875 13.0219 11.3874 13.9574 11.1849 14.9758C10.9823 15.9942 11.0863 17.0498 11.4836 18.0091C11.881 18.9684 12.5539 19.7883 13.4172 20.3652C14.2806 20.9421 15.2956 21.25 16.334 21.25L16.3347 21.25C17.2562 21.2509 18.1612 21.0087 18.958 20.547C19.3164 20.3394 19.7753 20.4616 19.9829 20.82C20.1906 21.1784 20.0684 21.6373 19.71 21.845C18.6837 22.4395 17.5187 22.7512 16.3332 22.75L16.334 22V22.75C16.3337 22.75 16.3335 22.75 16.3332 22.75C14.9985 22.7498 13.6937 22.354 12.5839 21.6124C11.4739 20.8707 10.6087 19.8165 10.0978 18.5831C9.58691 17.3497 9.45324 15.9925 9.71369 14.6831C9.97414 13.3738 10.617 12.171 11.561 11.227C12.505 10.283 13.7078 9.64015 15.0171 9.37969ZM18.2507 16C18.2507 15.4917 18.0487 15.0042 17.6893 14.6447C17.3298 14.2853 16.8423 14.0833 16.334 14.0833C15.8257 14.0833 15.3381 14.2853 14.9787 14.6447C14.6193 15.0042 14.4173 15.4917 14.4173 16C14.4173 16.5083 14.6193 16.9958 14.9787 17.3553C15.3381 17.7147 15.8257 17.9167 16.334 17.9167C16.8423 17.9167 17.3298 17.7147 17.6893 17.3553C18.0487 16.9958 18.2507 16.5083 18.2507 16Z" fill="white"/></g><defs><filter id="filter0_taggedd_506_22299" x="-2.66602" y="0" width="38.1602" height="38.1602" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="3"/><feGaussianBlur stdDeviation="3"/><feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.04 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_506_22299"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_506_22299" result="shape"/></filter><clipPath id="clip0tagged_506_22299"><rect width="32" height="32" fill="white" transform="translate(0.333984)"/></clipPath></defs></svg>',
			'socialwall1IconFree'   => '<svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#socialWallFreeclip0)"> <path d="M8.49935 2.19C5.29102 2.19 2.66602 4.80917 2.66602 8.035C2.66602 10.9517 4.80102 13.3725 7.58935 13.81V9.72667H6.10768V8.035H7.58935V6.74584C7.58935 5.28167 8.45852 4.47667 9.79435 4.47667C10.4302 4.47667 11.0952 4.5875 11.0952 4.5875V6.02834H10.3602C9.63685 6.02834 9.40935 6.4775 9.40935 6.93834V8.035H11.031L10.7685 9.72667H9.40935V13.81C10.7839 13.5929 12.0356 12.8916 12.9385 11.8325C13.8413 10.7735 14.3358 9.42663 14.3327 8.035C14.3327 4.80917 11.7077 2.19 8.49935 2.19Z" fill="#006BFA"/> <path d="M8.5 21.3047C7 21.3047 5.80469 22.5234 5.80469 24C5.80469 25.5 7 26.6953 8.5 26.6953C9.97656 26.6953 11.1953 25.5 11.1953 24C11.1953 22.5234 9.97656 21.3047 8.5 21.3047ZM8.5 25.7578C7.53906 25.7578 6.74219 24.9844 6.74219 24C6.74219 23.0391 7.51562 22.2656 8.5 22.2656C9.46094 22.2656 10.2344 23.0391 10.2344 24C10.2344 24.9844 9.46094 25.7578 8.5 25.7578ZM11.9219 21.2109C11.9219 20.8594 11.6406 20.5781 11.2891 20.5781C10.9375 20.5781 10.6562 20.8594 10.6562 21.2109C10.6562 21.5625 10.9375 21.8438 11.2891 21.8438C11.6406 21.8438 11.9219 21.5625 11.9219 21.2109ZM13.7031 21.8438C13.6562 21 13.4688 20.25 12.8594 19.6406C12.25 19.0312 11.5 18.8438 10.6562 18.7969C9.78906 18.75 7.1875 18.75 6.32031 18.7969C5.47656 18.8438 4.75 19.0312 4.11719 19.6406C3.50781 20.25 3.32031 21 3.27344 21.8438C3.22656 22.7109 3.22656 25.3125 3.27344 26.1797C3.32031 27.0234 3.50781 27.75 4.11719 28.3828C4.75 28.9922 5.47656 29.1797 6.32031 29.2266C7.1875 29.2734 9.78906 29.2734 10.6562 29.2266C11.5 29.1797 12.25 28.9922 12.8594 28.3828C13.4688 27.75 13.6562 27.0234 13.7031 26.1797C13.75 25.3125 13.75 22.7109 13.7031 21.8438ZM12.5781 27.0938C12.4141 27.5625 12.0391 27.9141 11.5938 28.1016C10.8906 28.3828 9.25 28.3125 8.5 28.3125C7.72656 28.3125 6.08594 28.3828 5.40625 28.1016C4.9375 27.9141 4.58594 27.5625 4.39844 27.0938C4.11719 26.4141 4.1875 24.7734 4.1875 24C4.1875 23.25 4.11719 21.6094 4.39844 20.9062C4.58594 20.4609 4.9375 20.1094 5.40625 19.9219C6.08594 19.6406 7.72656 19.7109 8.5 19.7109C9.25 19.7109 10.8906 19.6406 11.5938 19.9219C12.0391 20.0859 12.3906 20.4609 12.5781 20.9062C12.8594 21.6094 12.7891 23.25 12.7891 24C12.7891 24.7734 12.8594 26.4141 12.5781 27.0938Z" fill="url(#socialWallFreepaint0_linear)"/> <path d="M30.6018 4.50001C30.1526 4.70418 29.6684 4.83834 29.1668 4.90251C29.6801 4.59334 30.0768 4.10334 30.2634 3.51418C29.7793 3.80584 29.2426 4.01001 28.6768 4.12668C28.2159 3.62501 27.5684 3.33334 26.8334 3.33334C25.4626 3.33334 24.3426 4.45334 24.3426 5.83584C24.3426 6.03418 24.3659 6.22668 24.4068 6.40751C22.3301 6.30251 20.4809 5.30501 19.2501 3.79418C19.0343 4.16168 18.9118 4.59334 18.9118 5.04834C18.9118 5.91751 19.3493 6.68751 20.0259 7.12501C19.6118 7.12501 19.2268 7.00834 18.8884 6.83334V6.85084C18.8884 8.06418 19.7518 9.07918 20.8951 9.30668C20.528 9.40713 20.1427 9.42111 19.7693 9.34751C19.9277 9.84479 20.238 10.2799 20.6565 10.5917C21.0751 10.9035 21.5808 11.0763 22.1026 11.0858C21.2181 11.7861 20.1216 12.1646 18.9934 12.1592C18.7951 12.1592 18.5968 12.1475 18.3984 12.1242C19.5068 12.8358 20.8251 13.25 22.2368 13.25C26.8334 13.25 29.3593 9.43501 29.3593 6.12751C29.3593 6.01668 29.3593 5.91168 29.3534 5.80084C29.8434 5.45084 30.2634 5.00751 30.6018 4.50001Z" fill="#1B90EF"/> <path d="M23.3327 25.75L26.3602 24L23.3327 22.25V25.75ZM30.076 21.1825C30.1518 21.4567 30.2043 21.8242 30.2393 22.2908C30.2802 22.7575 30.2977 23.16 30.2977 23.51L30.3327 24C30.3327 25.2775 30.2393 26.2167 30.076 26.8175C29.9302 27.3425 29.5918 27.6808 29.0668 27.8267C28.7927 27.9025 28.291 27.955 27.521 27.99C26.7627 28.0308 26.0685 28.0483 25.4268 28.0483L24.4993 28.0833C22.0552 28.0833 20.5327 27.99 19.9318 27.8267C19.4068 27.6808 19.0685 27.3425 18.9227 26.8175C18.8468 26.5433 18.7943 26.1758 18.7593 25.7092C18.7185 25.2425 18.701 24.84 18.701 24.49L18.666 24C18.666 22.7225 18.7593 21.7833 18.9227 21.1825C19.0685 20.6575 19.4068 20.3192 19.9318 20.1733C20.206 20.0975 20.7077 20.045 21.4777 20.01C22.236 19.9692 22.9302 19.9517 23.5718 19.9517L24.4993 19.9167C26.9435 19.9167 28.466 20.01 29.0668 20.1733C29.5918 20.3192 29.9302 20.6575 30.076 21.1825Z" fill="#EB2121"/> </g> <defs> <linearGradient id="socialWallFreepaint0_linear" x1="6.97891" y1="38.843" x2="29.0945" y2="16.268" gradientUnits="userSpaceOnUse"> <stop stop-color="white"/> <stop offset="0.147864" stop-color="#F6640E"/> <stop offset="0.443974" stop-color="#BA03A7"/> <stop offset="0.733337" stop-color="#6A01B9"/> <stop offset="1" stop-color="#6B01B9"/> </linearGradient> <clipPath id="socialWallFreeclip0"> <rect width="32" height="32" fill="white" transform="translate(0.5)"/> </clipPath> </defs> </svg>',

			'user'                  => '<svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 0C4.53043 0 5.03914 0.210714 5.41421 0.585786C5.78929 0.960859 6 1.46957 6 2C6 2.53043 5.78929 3.03914 5.41421 3.41421C5.03914 3.78929 4.53043 4 4 4C3.46957 4 2.96086 3.78929 2.58579 3.41421C2.21071 3.03914 2 2.53043 2 2C2 1.46957 2.21071 0.960859 2.58579 0.585786C2.96086 0.210714 3.46957 0 4 0ZM4 5C6.21 5 8 5.895 8 7V8H0V7C0 5.895 1.79 5 4 5Z"/></svg>',
			'hashtag'               => '<svg viewBox="0 0 18 18" fill="none"><path d="M17.3607 4.1775H14.0152L14.618 1.266C14.6328 1.18021 14.6288 1.09223 14.6064 1.00812C14.5839 0.924001 14.5436 0.845742 14.488 0.778722C14.4324 0.711703 14.363 0.657514 14.2845 0.619882C14.206 0.582251 14.1203 0.56207 14.0332 0.560727H12.8276C12.6883 0.557321 12.5521 0.602311 12.4422 0.688037C12.3323 0.773763 12.2555 0.894929 12.2249 1.03091L11.61 4.1775H8.3549L8.9577 1.266C8.97229 1.18215 8.96897 1.09617 8.94795 1.0137C8.92692 0.931226 8.88867 0.854142 8.83572 0.787518C8.78276 0.720894 8.71629 0.666239 8.64069 0.62715C8.56509 0.588061 8.48207 0.565423 8.3971 0.560727H7.1915C7.05216 0.557321 6.91594 0.602311 6.80604 0.688037C6.69613 0.773763 6.61933 0.894929 6.58871 1.03091L5.98591 4.1775H2.36914C2.22811 4.17466 2.09056 4.22136 1.98042 4.30947C1.87028 4.39759 1.79452 4.52153 1.76634 4.65974L1.51919 5.86533C1.50109 5.95393 1.50315 6.04546 1.52522 6.13316C1.5473 6.22085 1.58882 6.30245 1.64671 6.37192C1.7046 6.44139 1.77737 6.49694 1.85965 6.53446C1.94192 6.57199 2.03158 6.59052 2.12199 6.58869H5.46751L4.47892 11.4111H0.862146C0.721125 11.4082 0.583571 11.4549 0.473429 11.543C0.363287 11.6311 0.287532 11.7551 0.259351 11.8933L0.0122042 13.0989C-0.00589975 13.1875 -0.00383898 13.279 0.0182337 13.3667C0.0403064 13.4544 0.0818254 13.536 0.139715 13.6055C0.197605 13.6749 0.270382 13.7305 0.352656 13.768C0.43493 13.8055 0.524592 13.8241 0.615 13.8222H3.98463L3.38183 16.7338C3.36677 16.821 3.37112 16.9106 3.39459 16.996C3.41806 17.0814 3.46006 17.1606 3.51761 17.2279C3.57517 17.2953 3.64685 17.3491 3.72757 17.3856C3.80829 17.4221 3.89606 17.4403 3.98463 17.439H5.19022C5.3244 17.4356 5.45359 17.3875 5.55732 17.3023C5.66105 17.2171 5.73339 17.0998 5.76288 16.9688L6.38979 13.8222H9.64488L9.04209 16.7338C9.02749 16.8176 9.03081 16.9036 9.05184 16.9861C9.07286 17.0685 9.11111 17.1456 9.16407 17.2122C9.21702 17.2789 9.28349 17.3335 9.35909 17.3726C9.43469 17.4117 9.51771 17.4343 9.60269 17.439H10.8083C10.9476 17.4424 11.0838 17.3974 11.1937 17.3117C11.3037 17.226 11.3805 17.1048 11.4111 16.9688L12.044 13.8222H15.6608C15.8018 13.8251 15.9394 13.7784 16.0495 13.6903C16.1596 13.6022 16.2354 13.4782 16.2636 13.34L16.5047 12.1344C16.5228 12.0458 16.5207 11.9543 16.4987 11.8666C16.4766 11.7789 16.4351 11.6973 16.3772 11.6278C16.3193 11.5584 16.2465 11.5028 16.1642 11.4653C16.082 11.4278 15.9923 11.4092 15.9019 11.4111H12.5383L13.5209 6.58869H17.1376C17.2787 6.59153 17.4162 6.54483 17.5264 6.45672C17.6365 6.36861 17.7123 6.24466 17.7404 6.10645L17.9876 4.90086C18.0063 4.8102 18.0038 4.71645 17.9804 4.62689C17.957 4.53733 17.9133 4.45436 17.8527 4.3844C17.7921 4.31445 17.7162 4.2594 17.6308 4.22352C17.5455 4.18764 17.4531 4.1719 17.3607 4.1775ZM10.1271 11.4111H6.87202L7.86061 6.58869H11.1157L10.1271 11.4111Z"/></svg>',
			'mention'               => '<svg viewBox="0 0 18 18"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.24419 0.172937C8.99002 -0.174331 10.7996 0.00389957 12.4442 0.685088C14.0887 1.36628 15.4943 2.51983 16.4832 3.99987C17.4722 5.47992 18 7.21997 18 9.00001V10.3333C18 11.1879 17.6605 12.0075 17.0562 12.6118C16.452 13.2161 15.6324 13.5556 14.7778 13.5556C13.9232 13.5556 13.1036 13.2161 12.4993 12.6118C12.3867 12.4992 12.2833 12.3791 12.1896 12.2527C11.3384 13.0874 10.1933 13.5556 9.00001 13.5556C7.7918 13.5556 6.63307 13.0756 5.77874 12.2213C4.92441 11.3669 4.44445 10.2082 4.44445 9.00001C4.44445 7.7918 4.92441 6.63307 5.77874 5.77874C6.63307 4.92441 7.7918 4.44445 9.00001 4.44445C10.2082 4.44445 11.3669 4.92441 12.2213 5.77874C13.0756 6.63307 13.5556 7.7918 13.5556 9.00001V10.3333C13.5556 10.6575 13.6843 10.9684 13.9135 11.1976C14.1428 11.4268 14.4536 11.5556 14.7778 11.5556C15.1019 11.5556 15.4128 11.4268 15.642 11.1976C15.8712 10.9684 16 10.6575 16 10.3333V9.00001C16 7.61554 15.5895 6.26216 14.8203 5.11101C14.0511 3.95987 12.9579 3.06266 11.6788 2.53285C10.3997 2.00303 8.99224 1.86441 7.63437 2.13451C6.27651 2.4046 5.02922 3.07129 4.05026 4.05026C3.07129 5.02922 2.4046 6.27651 2.13451 7.63437C1.86441 8.99224 2.00303 10.3997 2.53285 11.6788C3.06266 12.9579 3.95987 14.0511 5.11101 14.8203C6.26216 15.5895 7.61554 16 9.00001 16L9.001 16C10.2297 16.0012 11.4363 15.6782 12.4987 15.0627C12.9766 14.7859 13.5884 14.9488 13.8653 15.4267C14.1421 15.9046 13.9792 16.5164 13.5013 16.7933C12.1329 17.586 10.5796 18.0016 8.99901 18L9.00001 17V18C8.99968 18 8.99934 18 8.99901 18C7.21933 17.9998 5.47964 17.472 3.99987 16.4832C2.51983 15.4943 1.36628 14.0887 0.685088 12.4442C0.00389957 10.7996 -0.17433 8.99002 0.172936 7.24419C0.520204 5.49836 1.37737 3.89472 2.63604 2.63604C3.89472 1.37737 5.49836 0.520204 7.24419 0.172937ZM11.5556 9.00001C11.5556 8.32223 11.2863 7.67221 10.8071 7.19295C10.3278 6.7137 9.67778 6.44445 9.00001 6.44445C8.32223 6.44445 7.67221 6.7137 7.19295 7.19295C6.7137 7.67221 6.44445 8.32223 6.44445 9.00001C6.44445 9.67778 6.7137 10.3278 7.19295 10.8071C7.67221 11.2863 8.32223 11.5556 9.00001 11.5556C9.67778 11.5556 10.3278 11.2863 10.8071 10.8071C11.2863 10.3278 11.5556 9.67778 11.5556 9.00001Z"/></svg>',
			'tooltipHelpSvg'        => '<svg width="20" height="21" viewBox="0 0 20 21" fill="#0068A0" xmlns="http://www.w3.org/2000/svg"><path d="M9.1665 8H10.8332V6.33333H9.1665V8ZM9.99984 17.1667C6.32484 17.1667 3.33317 14.175 3.33317 10.5C3.33317 6.825 6.32484 3.83333 9.99984 3.83333C13.6748 3.83333 16.6665 6.825 16.6665 10.5C16.6665 14.175 13.6748 17.1667 9.99984 17.1667ZM9.99984 2.16666C8.90549 2.16666 7.82186 2.38221 6.81081 2.801C5.79976 3.21979 4.8811 3.83362 4.10728 4.60744C2.54448 6.17024 1.6665 8.28986 1.6665 10.5C1.6665 12.7101 2.54448 14.8298 4.10728 16.3926C4.8811 17.1664 5.79976 17.7802 6.81081 18.199C7.82186 18.6178 8.90549 18.8333 9.99984 18.8333C12.21 18.8333 14.3296 17.9554 15.8924 16.3926C17.4552 14.8298 18.3332 12.7101 18.3332 10.5C18.3332 9.40565 18.1176 8.32202 17.6988 7.31097C17.28 6.29992 16.6662 5.38126 15.8924 4.60744C15.1186 3.83362 14.1999 3.21979 13.1889 2.801C12.1778 2.38221 11.0942 2.16666 9.99984 2.16666ZM9.1665 14.6667H10.8332V9.66666H9.1665V14.6667Z" fill="#0068A0"/></svg>',

			'shoppableDisabled'     => '<svg width="303" height="145" viewBox="0 0 303 145" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M124.919 67.2058C130.919 72.7058 150.519 81.4058 180.919 72.2058" stroke="#8C8F9A" stroke-width="2" stroke-dasharray="3 3"/> <path d="M181.999 69L185.797 70.4241L183.5 74L181.999 69Z" fill="#8C8F9A"/> <g filter="url(#shopp_disabled_filter0_dddd)"> <rect x="24.6006" y="17.6504" width="81" height="98" rx="2" transform="rotate(-4 24.6006 17.6504)" fill="white"/> <rect x="24.3338" y="17.4184" width="81.5" height="98.5" rx="2.25" transform="rotate(-4 24.3338 17.4184)" stroke="url(#shopp_disabled_paint0_linear)" stroke-width="0.5"/> </g> <g clip-path="url(#shopp_disabled_clip0)"> <path d="M94.5298 21.3615C92.9088 21.4749 91.7091 22.8823 91.8207 24.478C91.9341 26.0991 93.3162 27.3005 94.9372 27.1872C96.5329 27.0756 97.7597 25.6917 97.6463 24.0707C97.5348 22.4749 96.1256 21.2499 94.5298 21.3615ZM94.8664 26.174C93.8279 26.2466 92.9083 25.471 92.8339 24.4072C92.7613 23.3687 93.5387 22.4744 94.6025 22.4C95.6409 22.3274 96.5352 23.1048 96.6079 24.1433C96.6822 25.2071 95.9048 26.1014 94.8664 26.174ZM98.2208 21.0016C98.1942 20.6217 97.869 20.339 97.4891 20.3656C97.1091 20.3921 96.8264 20.7173 96.853 21.0973C96.8796 21.4772 97.2048 21.7599 97.5847 21.7333C97.9646 21.7068 98.2473 21.3816 98.2208 21.0016ZM100.194 21.5509C100.079 20.6426 99.8198 19.8463 99.1152 19.2338C98.4106 18.6213 97.586 18.4753 96.6706 18.4884C95.7299 18.5033 92.9184 18.6999 91.9848 18.8161C91.0765 18.9305 90.3054 19.188 89.6676 19.8944C89.0551 20.599 88.9092 21.4237 88.9223 22.3391C88.9371 23.2798 89.1337 26.0913 89.2499 27.0249C89.3644 27.9332 89.6219 28.7042 90.3283 29.342C91.0582 29.9528 91.8575 30.1005 92.7729 30.0874C93.7136 30.0725 96.5251 29.8759 97.4587 29.7597C98.367 29.6453 99.1634 29.386 99.7759 28.6814C100.387 27.9515 100.534 27.1521 100.521 26.2367C100.506 25.296 100.31 22.4845 100.194 21.5509ZM99.3745 27.3096C99.2327 27.8285 98.854 28.2368 98.3869 28.4731C97.6483 28.8302 95.8699 28.8782 95.0594 28.9348C94.2236 28.9933 92.4559 29.1933 91.7001 28.9407C91.1793 28.7735 90.7728 28.4201 90.5348 27.9277C90.1795 27.2144 90.1315 25.4361 90.073 24.6002C90.0164 23.7897 89.8164 22.022 90.0672 21.2409C90.2362 20.7455 90.5895 20.339 91.082 20.1009C91.7952 19.7456 93.5736 19.6976 94.4094 19.6392C95.2199 19.5825 96.9876 19.3825 97.7687 19.6333C98.2624 19.777 98.6707 20.1557 98.9069 20.6228C99.264 21.3614 99.312 23.1397 99.3687 23.9502C99.4271 24.7861 99.6271 26.5538 99.3745 27.3096Z" fill="url(#shopp_disabled_paint1_linear)"/> </g> <g clip-path="url(#shopp_disabled_clip1)"> <rect x="26.1348" y="39.5967" width="81" height="76" rx="2" transform="rotate(-4 26.1348 39.5967)" fill="#B5E5FF"/> <circle cx="30.7388" cy="105.436" r="54" transform="rotate(-4 30.7388 105.436)" fill="#86D0F9"/> <g filter="url(#shopp_disabled_filter1_dd)"> <mask id="shopp_disabled_mask0" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="35" y="47" width="60" height="54"> <path fill-rule="evenodd" clip-rule="evenodd" d="M68.7966 50.3478C68.534 50.4332 68.3943 50.7154 68.4401 50.9877C68.8644 53.5073 66.4327 56.3732 62.7333 57.5753C59.0338 58.7773 55.382 57.888 54.2442 55.6002C54.1213 55.3529 53.8423 55.2068 53.5797 55.2921L47.2555 57.347C47.1786 57.372 47.109 57.4152 47.0525 57.473L42.6186 62.008L35.8445 69.2862C35.7004 69.441 35.6693 69.6698 35.7668 69.8574L40.9681 79.8652C41.1015 80.1217 41.4239 80.212 41.6711 80.0621L47.8083 76.3393C48.0715 76.1797 48.4151 76.2935 48.5309 76.5788L58.2754 100.594C58.374 100.837 58.6437 100.963 58.8932 100.881L92.2457 90.0446C92.4952 89.9635 92.6396 89.7034 92.5765 89.4488L86.3412 64.2801C86.2678 63.9837 86.4749 63.6913 86.7789 63.6622L94.424 62.9299C94.7094 62.9026 94.9134 62.6414 94.8708 62.358L93.1967 51.2062C93.1647 50.9929 92.9995 50.8242 92.787 50.7877L82.5629 49.0293L76.3102 47.9666C76.2305 47.953 76.1488 47.959 76.0719 47.984L68.7966 50.3478Z" fill="white"/> </mask> <g mask="url(#shopp_disabled_mask0)"> <rect x="28.3076" y="60.3479" width="72" height="54" transform="rotate(-16 28.3076 60.3479)" fill="white"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M66.4321 69.6639C65.1395 69.4776 63.7264 69.0512 62.5105 69.0127C63.1766 69.8427 63.7987 70.7521 64.429 71.6465C63.8884 72.3619 63.1987 72.9948 62.5553 73.6533C63.3952 74.1125 64.4294 74.2212 65.3292 74.5723C64.947 75.4717 64.0024 76.5635 63.9089 77.3062C65.0894 76.8017 66.445 76.1437 67.5698 75.7666C68.181 76.9532 68.7057 78.2958 69.3922 79.3464C69.485 77.6689 69.5124 75.9552 69.7351 74.3498C70.8246 74.4733 72.1524 74.6242 73.1713 74.589C72.2358 73.8444 71.3419 73.0247 70.4606 72.1824C71.1537 71.2976 71.8595 70.42 72.5116 69.5125C71.2887 69.7444 70.035 70.0316 68.7692 70.3408C68.2001 69.1068 67.8102 67.5497 67.1648 66.4536C66.98 67.5567 66.688 68.6002 66.4321 69.6639ZM70.0641 80.1946C70.0998 80.9132 70.6974 81.0407 70.7363 81.4713C70.1738 81.4417 69.7628 81.4615 69.1035 81.7558C68.9743 81.2243 69.4256 81.0144 69.1426 80.3976C61.5808 81.6649 57.7717 68.4365 64.8194 65.5342C73.6314 61.9053 78.4249 77.5439 70.0641 80.1946Z" fill="#FE544F"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M67.1649 66.4536C67.8103 67.5497 68.2003 69.1068 68.7693 70.3407C70.0352 70.0316 71.2888 69.7444 72.5117 69.5125C71.8597 70.42 71.1538 71.2976 70.4608 72.1824C71.3421 73.0248 72.2359 73.8444 73.1714 74.589C72.1526 74.6242 70.8247 74.4733 69.7352 74.3498C69.5126 75.9552 69.4852 77.6689 69.3924 79.3464C68.7058 78.2958 68.1811 76.9532 67.5699 75.7666C66.4451 76.1438 65.0896 76.8017 63.9091 77.3062C64.0026 76.5635 64.9472 75.4718 65.3294 74.5723C64.4295 74.2212 63.3954 74.1125 62.5555 73.6533C63.1989 72.9948 63.8885 72.362 64.4292 71.6465C63.7988 70.7521 63.1767 69.8427 62.5106 69.0128C63.7266 69.0512 65.1396 69.4776 66.4323 69.6639C66.6881 68.6002 66.9802 67.5567 67.1649 66.4536Z" fill="white"/> </g> </g> </g> <g filter="url(#shopp_disabled_filter2_dddd)"> <rect x="199.592" y="17.7058" width="79" height="102" rx="2" transform="rotate(4 199.592 17.7058)" fill="#E2F5FF"/> </g> <rect x="231.919" y="100.162" width="36" height="17" rx="2" transform="rotate(4 231.919 100.162)" fill="#0096CC"/> <path d="M241.707 111.873L244.07 112.038C245.123 112.112 245.827 111.602 245.887 110.743L245.888 110.736C245.931 110.112 245.469 109.576 244.827 109.497L244.831 109.432C245.358 109.397 245.785 108.978 245.821 108.453L245.822 108.446C245.875 107.686 245.328 107.182 244.346 107.113L242.051 106.953L241.707 111.873ZM243.95 107.973C244.376 108.003 244.61 108.232 244.586 108.579L244.585 108.586C244.561 108.931 244.281 109.123 243.824 109.091L243.162 109.045L243.241 107.923L243.95 107.973ZM243.859 109.858C244.377 109.894 244.652 110.136 244.624 110.538L244.623 110.545C244.594 110.958 244.295 111.166 243.777 111.13L243.02 111.077L243.109 109.805L243.859 109.858ZM248.86 112.507C250.155 112.597 251.031 111.925 251.108 110.824L251.334 107.602L250.086 107.515L249.869 110.617C249.829 111.19 249.498 111.51 248.935 111.47C248.376 111.431 248.09 111.069 248.13 110.496L248.347 107.393L247.099 107.306L246.874 110.528C246.796 111.633 247.581 112.417 248.86 112.507ZM253.583 112.703L254.834 112.791L254.952 111.1L256.873 107.989L255.539 107.896L254.448 109.838L254.383 109.833L253.565 107.758L252.232 107.665L253.701 111.012L253.583 112.703Z" fill="white"/> <g filter="url(#shopp_disabled_filter3_dd)"> <mask id="shopp_disabled_mask1" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="207" y="35" width="61" height="48"> <path fill-rule="evenodd" clip-rule="evenodd" d="M244.802 36.7068C244.526 36.6972 244.298 36.9146 244.248 37.1861C243.785 39.699 240.52 41.5604 236.632 41.4246C232.745 41.2889 229.618 39.2042 229.331 36.6652C229.3 36.3908 229.088 36.1581 228.812 36.1485L222.166 35.9164C222.085 35.9136 222.005 35.9304 221.932 35.9653L216.215 38.7104L207.36 43.2328C207.171 43.329 207.064 43.5333 207.091 43.743L208.556 54.9261C208.594 55.2128 208.866 55.408 209.149 55.3516L216.19 53.9524C216.492 53.8924 216.776 54.117 216.787 54.4246L217.73 80.3242C217.74 80.5864 217.95 80.7966 218.212 80.8057L253.26 82.0296C253.522 82.0388 253.747 81.8438 253.774 81.5829L256.523 55.7995C256.556 55.4959 256.85 55.2919 257.146 55.3685L264.581 57.2952C264.858 57.3671 265.139 57.1915 265.196 56.9106L267.437 45.8588C267.48 45.6474 267.382 45.4324 267.195 45.3253L258.189 40.1762L252.677 37.039C252.607 36.999 252.528 36.9766 252.447 36.9738L244.802 36.7068Z" fill="white"/> </mask> <g mask="url(#shopp_disabled_mask1)"> <rect x="203.335" y="32.2556" width="72" height="54" transform="rotate(4 203.335 32.2556)" fill="white"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M235.974 54.0491C234.823 53.4321 233.641 52.548 232.512 52.096C232.854 53.1038 233.128 54.171 233.414 55.2271C232.661 55.7145 231.797 56.0733 230.967 56.472C231.599 57.1908 232.534 57.6466 233.259 58.2843C232.592 58.9988 231.331 59.7017 230.99 60.3676C232.271 60.2973 233.77 60.1426 234.956 60.173C235.125 61.497 235.159 62.9381 235.444 64.1601C236.105 62.6156 236.717 61.0146 237.476 59.5821C238.457 60.0709 239.653 60.6668 240.623 60.9822C239.998 59.9626 239.439 58.8866 238.899 57.7936C239.852 57.1992 240.816 56.616 241.739 55.9862C240.511 55.7859 239.234 55.627 237.939 55.4846C237.826 54.1304 237.992 52.5338 237.761 51.2831C237.21 52.2564 236.579 53.1372 235.974 54.0491ZM235.786 65.187C235.573 65.8745 236.091 66.1987 235.981 66.6166C235.462 66.3964 235.069 66.2745 234.349 66.3255C234.409 65.7818 234.905 65.739 234.85 65.0626C227.311 63.6672 228.256 49.9337 235.871 49.6169C245.393 49.2208 244.549 65.5558 235.786 65.187Z" fill="#FE544F"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M237.761 51.283C237.993 52.5337 237.827 54.1303 237.939 55.4844C239.235 55.6268 240.511 55.7857 241.739 55.9861C240.816 56.6159 239.853 57.1991 238.899 57.7935C239.439 58.8865 239.998 59.9624 240.623 60.9821C239.653 60.6667 238.457 60.0708 237.476 59.582C236.717 61.0145 236.106 62.6155 235.445 64.16C235.159 62.938 235.125 61.4969 234.956 60.1729C233.77 60.1425 232.272 60.2972 230.99 60.3675C231.332 59.7016 232.593 58.9987 233.259 58.2842C232.534 57.6465 231.599 57.1907 230.967 56.4719C231.797 56.0732 232.662 55.7144 233.414 55.227C233.128 54.1709 232.854 53.1037 232.512 52.0959C233.642 52.5479 234.824 53.432 235.975 54.049C236.579 53.1371 237.21 52.2563 237.761 51.283Z" fill="white"/> </g> </g> <path d="M266.144 121.304L266.2 120.51L265.32 120.449L265.375 119.655L263.615 119.532L263.67 118.739L261.03 118.554L261.085 117.761L259.325 117.637L259.547 114.463L258.666 114.402L258.722 113.608L256.962 113.485L256.906 114.279L256.026 114.217L255.526 121.359L254.646 121.297L254.702 120.504L252.061 120.319L251.839 123.493L252.719 123.555L252.608 125.142L253.489 125.203L253.378 126.79L254.258 126.852L254.147 128.439L255.027 128.501L254.861 130.881L264.543 131.558L264.765 128.384L265.645 128.446L265.811 126.065L264.931 126.003L264.765 128.384L263.885 128.322L263.718 130.703L255.796 130.149L255.907 128.562L255.027 128.501L255.138 126.913L254.258 126.852L254.369 125.265L253.489 125.203L253.6 123.616L252.719 123.555L252.886 121.174L254.646 121.297L254.591 122.091L255.471 122.152L255.305 124.533L256.185 124.594L256.906 114.279L258.666 114.402L258.167 121.544L259.047 121.605L259.269 118.431L261.03 118.554L260.808 121.728L261.688 121.79L261.854 119.409L263.615 119.532L263.393 122.706L264.273 122.768L264.439 120.387L265.32 120.449L265.264 121.242L266.144 121.304L265.811 126.065L266.692 126.127L267.025 121.365L266.144 121.304Z" fill="#141B38"/> <path d="M264.932 126.003L265.812 126.065L266.145 121.304L265.265 121.242L265.32 120.449L264.44 120.387L264.274 122.768L263.393 122.706L263.615 119.532L261.855 119.409L261.688 121.79L260.808 121.728L261.03 118.554L259.27 118.431L259.048 121.605L258.168 121.543L258.667 114.402L256.907 114.279L256.185 124.594L255.305 124.533L255.471 122.152L254.591 122.091L254.647 121.297L252.886 121.174L252.72 123.555L253.6 123.616L253.489 125.203L254.369 125.265L254.258 126.852L255.139 126.913L255.028 128.5L255.908 128.562L255.797 130.149L263.719 130.703L263.885 128.322L264.765 128.384L264.932 126.003Z" fill="white"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M258.001 123.924L258.881 123.986L258.62 127.726L257.739 127.665L258.001 123.924ZM260.641 124.109L259.761 124.047L259.5 127.788L260.38 127.85L260.641 124.109ZM262.402 124.232L261.521 124.17L261.26 127.911L262.14 127.973L262.402 124.232Z" fill="#141B38"/> <defs> <filter id="shopp_disabled_filter0_dddd" x="16.6698" y="10.1217" width="103.5" height="119.273" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="0.749837"/> <feGaussianBlur stdDeviation="0.468648"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.1137 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.80196"/> <feGaussianBlur stdDeviation="1.12623"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.0484671 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.39293"/> <feGaussianBlur stdDeviation="2.12058"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.06 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.05242"/> <feGaussianBlur stdDeviation="3.78276"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.0715329 0"/> <feBlend mode="normal" in2="effect3_dropShadow" result="effect4_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow" result="shape"/> </filter> <filter id="shopp_disabled_filter1_dd" x="32.7109" y="44.9595" width="67.165" height="60.9465" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dx="1" dy="1"/> <feGaussianBlur stdDeviation="2"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.13 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="0.5"/> <feGaussianBlur stdDeviation="0.25"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow" result="shape"/> </filter> <filter id="shopp_disabled_filter2_dddd" x="185.046" y="16.3272" width="100.784" height="122.124" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="0.749837"/> <feGaussianBlur stdDeviation="0.468648"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.1137 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1.80196"/> <feGaussianBlur stdDeviation="1.12623"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.0484671 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="3.39293"/> <feGaussianBlur stdDeviation="2.12058"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.06 0"/> <feBlend mode="normal" in2="effect2_dropShadow" result="effect3_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="6.05242"/> <feGaussianBlur stdDeviation="3.78276"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.101961 0 0 0 0 0.466667 0 0 0 0.0715329 0"/> <feBlend mode="normal" in2="effect3_dropShadow" result="effect4_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect4_dropShadow" result="shape"/> </filter> <filter id="shopp_disabled_filter3_dd" x="204.087" y="32.916" width="68.3604" height="54.114" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dx="1" dy="1"/> <feGaussianBlur stdDeviation="2"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.13 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="0.5"/> <feGaussianBlur stdDeviation="0.25"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow" result="shape"/> </filter> <linearGradient id="shopp_disabled_paint0_linear" x1="32.1943" y1="17.6504" x2="120.163" y2="93.7021" gradientUnits="userSpaceOnUse"> <stop stop-color="#B5CBEC"/> <stop offset="1" stop-color="#B6CFF4" stop-opacity="0.32"/> </linearGradient> <linearGradient id="shopp_disabled_paint1_linear" x1="94.2114" y1="40.43" x2="116.406" y2="14.3621" gradientUnits="userSpaceOnUse"> <stop stop-color="white"/> <stop offset="0.147864" stop-color="#F6640E"/> <stop offset="0.443974" stop-color="#BA03A7"/> <stop offset="0.733337" stop-color="#6A01B9"/> <stop offset="1" stop-color="#6B01B9"/> </linearGradient> <clipPath id="shopp_disabled_clip0"> <rect width="13" height="13" fill="white" transform="translate(87.7959 18.2437) rotate(-4)"/> </clipPath> <clipPath id="shopp_disabled_clip1"> <rect x="26.1348" y="39.5967" width="81" height="76" rx="2" transform="rotate(-4 26.1348 39.5967)" fill="white"/> </clipPath> </defs> </svg>',
			'shoppableEnabled'      => '<svg width="70" height="70" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg"> <g filter="url(#shoppEnabled_filter0_dd)"> <rect x="5" y="1" width="60" height="60" rx="2" fill="white"/> </g> <path d="M19.904 26.2247L5 39.7857V59C5 60.1046 5.89543 61 7 61H63C64.1046 61 65 60.1046 65 59V45.5714L52.4342 31.4716C51.7591 30.7141 50.6236 30.5822 49.7928 31.1648L38.8105 38.8667C38.0444 39.4039 37.0082 39.3382 36.3161 38.7085L22.596 26.2247C21.833 25.5304 20.667 25.5304 19.904 26.2247Z" fill="url(#shoppEnabled_paint0_linear)"/> <rect x="29" y="4" width="29" height="20" rx="2" fill="#0068A0"/> <path d="M37.6002 14.0001C37.6002 12.8601 38.5268 11.9334 39.6668 11.9334H42.3335V10.6667H39.6668C38.7828 10.6667 37.9349 11.0179 37.3098 11.6431C36.6847 12.2682 36.3335 13.116 36.3335 14.0001C36.3335 14.8841 36.6847 15.732 37.3098 16.3571C37.9349 16.9822 38.7828 17.3334 39.6668 17.3334H42.3335V16.0667H39.6668C38.5268 16.0667 37.6002 15.1401 37.6002 14.0001ZM40.3335 14.6667H45.6668V13.3334H40.3335V14.6667ZM46.3335 10.6667H43.6668V11.9334H46.3335C47.4735 11.9334 48.4002 12.8601 48.4002 14.0001C48.4002 15.1401 47.4735 16.0667 46.3335 16.0667H43.6668V17.3334H46.3335C47.2176 17.3334 48.0654 16.9822 48.6905 16.3571C49.3156 15.732 49.6668 14.8841 49.6668 14.0001C49.6668 13.116 49.3156 12.2682 48.6905 11.6431C48.0654 11.0179 47.2176 10.6667 46.3335 10.6667Z" fill="white"/> <path d="M64.1103 30.0086V29.0938H63.0956V28.179H61.0662V27.2643H58.0221V26.3495H55.9926V22.6904H54.9779V21.7756H52.9485V22.6904H51.9338V30.9234H50.9191V30.0086H47.875V33.6677H48.8897V35.4972H49.9044V37.3268H50.9191V39.1563H51.9338V41.9006H63.0956V38.2415H64.1103V35.4972H63.0956V38.2415H62.0809V40.9859H52.9485V39.1563H51.9338V37.3268H50.9191V35.4972H49.9044V33.6677H48.8897V30.9234H50.9191V31.8381H51.9338V34.5825H52.9485V22.6904H54.9779V30.9234H55.9926V27.2643H58.0221V30.9234H59.0368V28.179H61.0662V31.8381H62.0809V29.0938H63.0956V30.0086H64.1103V35.4972H65.125V30.0086H64.1103Z" fill="#141B38"/> <path d="M63.096 35.4972H64.1107V30.0086H63.096V29.0938H62.0813V31.8382H61.0666V28.1791H59.0372V30.9234H58.0225V27.2643H55.9931V30.9234H54.9784V22.6904H52.949V34.5825H51.9343V31.8382H50.9195V30.9234H48.8901V33.6677H49.9048V35.4972H50.9195V37.3268H51.9343V39.1563H52.949V40.9859H62.0813V38.2416H63.096V35.4972Z" fill="white"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M54.9785 33.668H55.9932V37.9805H54.9785V33.668ZM58.0224 33.668H57.0077V37.9805H58.0224V33.668ZM60.0516 33.668H59.0369V37.9805H60.0516V33.668Z" fill="#141B38"/> <defs> <filter id="shoppEnabled_filter0_dd" x="0" y="0" width="70" height="70" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="4"/> <feGaussianBlur stdDeviation="2.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.05 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.05 0"/> <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow" result="shape"/> </filter> <linearGradient id="shoppEnabled_paint0_linear" x1="35" y1="25" x2="35" y2="61" gradientUnits="userSpaceOnUse"> <stop stop-color="#DCDDE1"/> <stop offset="1" stop-color="#DCDDE1" stop-opacity="0"/> </linearGradient> </defs> </svg>',
			'ctaBoxes'              => array(
				'hashtag' => '<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="28.1951" height="27" transform="translate(17.0762 16.2861) rotate(3)" fill="#8C8F9A"/><path d="M39.048 26.1275L35.707 25.9524L36.4614 23.0765C36.4807 22.9916 36.4813 22.9035 36.4633 22.8183C36.4453 22.7332 36.4091 22.6529 36.3571 22.5831C36.3051 22.5132 36.2386 22.4555 36.1622 22.4138C36.0858 22.3721 36.0012 22.3475 35.9144 22.3416L34.7104 22.2785C34.5715 22.2678 34.4331 22.3056 34.3188 22.3854C34.2046 22.4653 34.1216 22.5823 34.0839 22.7164L33.3052 25.8266L30.0545 25.6562L30.8089 22.7802C30.8279 22.6973 30.829 22.6112 30.8124 22.5278C30.7957 22.4443 30.7615 22.3653 30.7121 22.296C30.6627 22.2267 30.5992 22.1687 30.5258 22.1257C30.4523 22.0827 30.3706 22.0557 30.286 22.0466L29.082 21.9835C28.9431 21.9728 28.8047 22.0106 28.6904 22.0904C28.5762 22.1703 28.4931 22.2873 28.4555 22.4215L27.6888 25.5322L24.077 25.3429C23.9363 25.3327 23.7965 25.3721 23.6819 25.4544C23.5673 25.5366 23.4852 25.6564 23.4498 25.793L23.1399 26.984C23.1172 27.0715 23.1144 27.163 23.1319 27.2517C23.1493 27.3405 23.1865 27.4241 23.2407 27.4965C23.2949 27.5689 23.3646 27.6282 23.4448 27.67C23.525 27.7118 23.6136 27.735 23.704 27.7379L27.0449 27.913L25.8053 32.677L22.1935 32.4877C22.0528 32.4775 21.913 32.5169 21.7984 32.5991C21.6838 32.6814 21.6017 32.8012 21.5663 32.9377L21.2564 34.1287C21.2337 34.2163 21.2309 34.3078 21.2484 34.3965C21.2658 34.4852 21.303 34.5689 21.3572 34.6413C21.4114 34.7137 21.4811 34.773 21.5613 34.8148C21.6415 34.8565 21.7301 34.8797 21.8205 34.8826L25.1855 35.059L24.4311 37.935C24.4115 38.0213 24.4112 38.111 24.4302 38.1975C24.4491 38.284 24.4869 38.3653 24.5409 38.4356C24.5948 38.5058 24.6636 38.5633 24.7423 38.604C24.821 38.6447 24.9077 38.6675 24.9962 38.6708L26.2001 38.7339C26.3343 38.7375 26.4658 38.6962 26.5739 38.6166C26.6819 38.537 26.7603 38.4236 26.7966 38.2943L27.5874 35.1849L30.838 35.3552L30.0836 38.2312C30.0647 38.3142 30.0635 38.4002 30.0802 38.4837C30.0969 38.5671 30.131 38.6461 30.1804 38.7154C30.2298 38.7847 30.2933 38.8428 30.3668 38.8858C30.4402 38.9288 30.522 38.9557 30.6066 38.9648L31.8105 39.0279C31.9495 39.0386 32.0879 39.0008 32.2021 38.921C32.3163 38.8411 32.3994 38.7241 32.4371 38.5899L33.2338 35.4808L36.8456 35.6701C36.9863 35.6803 37.1261 35.6409 37.2407 35.5586C37.3553 35.4764 37.4375 35.3566 37.4729 35.2201L37.7767 34.0287C37.7995 33.9412 37.8022 33.8497 37.7847 33.761C37.7673 33.6722 37.7301 33.5886 37.6759 33.5162C37.6217 33.4438 37.552 33.3845 37.4718 33.3427C37.3916 33.3009 37.303 33.2777 37.2126 33.2748L33.8536 33.0988L35.0872 28.3344L38.6991 28.5237C38.8397 28.5339 38.9795 28.4945 39.0941 28.4123C39.2087 28.3301 39.2909 28.2102 39.3263 28.0737L39.6362 26.8827C39.6596 26.7931 39.662 26.6994 39.6434 26.6087C39.6247 26.5181 39.5854 26.4329 39.5285 26.3599C39.4716 26.2869 39.3987 26.2279 39.3154 26.1876C39.232 26.1473 39.1405 26.1268 39.048 26.1275ZM31.4458 32.9726L28.1951 32.8022L29.4347 28.0382L32.6854 28.2086L31.4458 32.9726Z" fill="white"/><rect x="10" y="10" width="30.1951" height="30" fill="#0096CC"/><path d="M32.4132 21.2806H29.4859L30.0134 18.733C30.0263 18.6579 30.0229 18.5809 30.0032 18.5073C29.9836 18.4337 29.9483 18.3653 29.8997 18.3066C29.851 18.248 29.7903 18.2006 29.7216 18.1676C29.6529 18.1347 29.5779 18.1171 29.5017 18.1159H28.4468C28.3249 18.1129 28.2057 18.1523 28.1096 18.2273C28.0134 18.3023 27.9462 18.4083 27.9194 18.5273L27.3814 21.2806H24.5332L25.0606 18.733C25.0734 18.6596 25.0705 18.5844 25.0521 18.5122C25.0337 18.4401 25.0002 18.3726 24.9539 18.3143C24.9076 18.256 24.8494 18.2082 24.7833 18.174C24.7171 18.1398 24.6445 18.12 24.5701 18.1159H23.5152C23.3933 18.1129 23.2741 18.1523 23.1779 18.2273C23.0818 18.3023 23.0146 18.4083 22.9878 18.5273L22.4603 21.2806H19.2957C19.1723 21.2781 19.0519 21.3189 18.9555 21.396C18.8591 21.4731 18.7929 21.5816 18.7682 21.7025L18.552 22.7574C18.5361 22.8349 18.5379 22.915 18.5572 22.9918C18.5765 23.0685 18.6129 23.1399 18.6635 23.2007C18.7142 23.2615 18.7779 23.3101 18.8498 23.3429C18.9218 23.3757 19.0003 23.3919 19.0794 23.3903H22.0067L21.1417 27.6099H17.977C17.8536 27.6074 17.7333 27.6483 17.6369 27.7254C17.5405 27.8025 17.4742 27.9109 17.4496 28.0319L17.2333 29.0868C17.2175 29.1643 17.2193 29.2444 17.2386 29.3211C17.2579 29.3978 17.2943 29.4692 17.3449 29.53C17.3956 29.5908 17.4592 29.6394 17.5312 29.6723C17.6032 29.7051 17.6817 29.7213 17.7608 29.7197H20.7092L20.1818 32.2673C20.1686 32.3437 20.1724 32.422 20.1929 32.4967C20.2135 32.5715 20.2502 32.6408 20.3006 32.6997C20.3509 32.7586 20.4137 32.8057 20.4843 32.8376C20.5549 32.8696 20.6317 32.8855 20.7092 32.8844H21.7641C21.8815 32.8814 21.9945 32.8393 22.0853 32.7648C22.1761 32.6902 22.2394 32.5876 22.2652 32.473L22.8137 29.7197H25.6619L25.1345 32.2673C25.1217 32.3406 25.1246 32.4159 25.143 32.488C25.1614 32.5602 25.1949 32.6277 25.2412 32.6859C25.2876 32.7442 25.3457 32.7921 25.4119 32.8263C25.478 32.8605 25.5507 32.8803 25.625 32.8844H26.6799C26.8018 32.8874 26.921 32.848 27.0172 32.773C27.1134 32.698 27.1806 32.592 27.2073 32.473L27.7612 29.7197H30.9258C31.0492 29.7222 31.1696 29.6813 31.266 29.6042C31.3623 29.5271 31.4286 29.4187 31.4533 29.2978L31.6643 28.2429C31.6801 28.1653 31.6783 28.0852 31.659 28.0085C31.6397 27.9318 31.6034 27.8604 31.5527 27.7996C31.502 27.7388 31.4384 27.6902 31.3664 27.6574C31.2944 27.6245 31.2159 27.6083 31.1368 27.6099H28.1937L29.0534 23.3903H32.2181C32.3415 23.3928 32.4618 23.352 32.5582 23.2749C32.6546 23.1978 32.7209 23.0893 32.7455 22.9684L32.9618 21.9135C32.9781 21.8342 32.976 21.7521 32.9555 21.6738C32.9351 21.5954 32.8968 21.5228 32.8438 21.4616C32.7907 21.4004 32.7243 21.3522 32.6496 21.3208C32.575 21.2894 32.4941 21.2757 32.4132 21.2806ZM26.0839 27.6099H23.2357L24.1007 23.3903H26.9489L26.0839 27.6099Z" fill="white"/><rect x="10" y="10" width="30.1951" height="30" stroke="white" stroke-width="2"/></svg>',
				'layout'  => '<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M38.7033 21.3195C38.8925 21.1158 39.158 21 39.4361 21H52C52.5523 21 53 21.4477 53 22V45C53 45.5523 52.5523 46 52 46H28C27.4477 46 27 45.5523 27 45V29C27 28.4477 27.4477 28 28 28H32.0639C32.342 28 32.6075 27.8842 32.7967 27.6805L38.7033 21.3195Z" fill="white"/><rect x="5.05664" y="7.01074" width="28" height="28" rx="1" transform="rotate(-4 5.05664 7.01074)" fill="#8C8F9A"/><rect x="10.3242" y="10.6523" width="8" height="11" transform="rotate(-4 10.3242 10.6523)" fill="white"/><path d="M11.2305 23.6206L19.211 23.0626L19.6993 30.0455L11.7188 30.6036L11.2305 23.6206Z" fill="white"/><rect x="20.2988" y="9.95459" width="8" height="7" transform="rotate(-4 20.2988 9.95459)" fill="white"/><path d="M20.9277 18.9326L28.9082 18.3746L29.6756 29.3478L21.6951 29.9058L20.9277 18.9326Z" fill="white"/><rect x="21.0097" y="18.0572" width="28" height="28" rx="2" transform="rotate(4 21.0097 18.0572)" fill="#0096CC" stroke="white" stroke-width="2"/><path d="M28.0332 25.1357L41.0015 26.0426L40.0299 39.9372L27.0616 39.0304L28.0332 25.1357Z" fill="white"/><path d="M21.5488 24.6821L26.1804 25.006L25.2088 38.9006L20.5772 38.5768L21.5488 24.6821Z" fill="#94E3FF"/><path d="M42.8535 26.1719L47.4851 26.4957L46.5135 40.3904L41.8819 40.0665L42.8535 26.1719Z" fill="#94E3FF"/></svg>',
				'popups'  => '<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="8.57617" y="14.6885" width="38" height="25" rx="1" transform="rotate(-2 8.57617 14.6885)" fill="#0096CC"/><path fill-rule="evenodd" clip-rule="evenodd" d="M25.4867 30.0589C25.5889 30.1678 25.7632 30.1634 25.8598 30.0495L32.6624 22.0206C32.7577 21.9081 32.9292 21.9021 33.0321 22.0077L42.8234 32.0504C42.975 32.2058 42.8701 32.4672 42.6532 32.4748L28.2271 32.9786L23.2301 33.1531L14.7862 33.4479C14.5716 33.4554 14.448 33.2066 14.5837 33.0401L20.7716 25.4492C20.8672 25.332 21.0442 25.3258 21.1477 25.4361L25.4867 30.0589Z" fill="white"/><path d="M5.57322 24.4268C5.73072 24.2693 6 24.3808 6 24.6036V31.3964C6 31.6192 5.73071 31.7307 5.57322 31.5732L2.17678 28.1768C2.07915 28.0791 2.07915 27.9209 2.17678 27.8232L5.57322 24.4268Z" fill="#434960"/><path d="M50.4268 21.4268C50.2693 21.2693 50 21.3808 50 21.6036V28.3964C50 28.6192 50.2693 28.7307 50.4268 28.5732L53.8232 25.1768C53.9209 25.0791 53.9209 24.9209 53.8232 24.8232L50.4268 21.4268Z" fill="#434960"/></svg>',
				'filter'  => '<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_573_793)"><rect x="17.4102" y="10.708" width="31" height="31" rx="0.5" transform="rotate(5 17.4102 10.708)" fill="#8C8F9A"/><circle cx="43.2994" cy="36.061" r="17" transform="rotate(5 43.2994 36.061)" fill="#434960"/><rect x="15.4922" y="32.6245" width="31" height="9" transform="rotate(5 15.4922 32.6245)" fill="#E8E8EB"/></g><circle cx="17" cy="13" r="10" fill="#0096CC" stroke="white" stroke-width="2"/><rect x="12" y="10" width="10" height="1.5" rx="0.25" fill="white"/><path d="M13.5 13.25C13.5 13.1119 13.6119 13 13.75 13H20.25C20.3881 13 20.5 13.1119 20.5 13.25V14.25C20.5 14.3881 20.3881 14.5 20.25 14.5H13.75C13.6119 14.5 13.5 14.3881 13.5 14.25V13.25Z" fill="white"/><path d="M15.5 16.25C15.5 16.1119 15.6119 16 15.75 16H18.25C18.3881 16 18.5 16.1119 18.5 16.25V17.25C18.5 17.3881 18.3881 17.5 18.25 17.5H15.75C15.6119 17.5 15.5 17.3881 15.5 17.25V16.25Z" fill="white"/><defs><clipPath id="clip0_573_793"><rect x="17.4102" y="10.708" width="31" height="31" rx="0.5" transform="rotate(5 17.4102 10.708)" fill="white"/></clipPath></defs></svg>'

			),
			'camera' => '<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.5007 3.5L8.36565 5.83333H4.66732C3.38398 5.83333 2.33398 6.88333 2.33398 8.16667V22.1667C2.33398 23.45 3.38398 24.5 4.66732 24.5H23.334C24.6173 24.5 25.6673 23.45 25.6673 22.1667V8.16667C25.6673 6.88333 24.6173 5.83333 23.334 5.83333H19.6357L17.5007 3.5H10.5007ZM14.0007 21C10.7807 21 8.16732 18.3867 8.16732 15.1667C8.16732 11.9467 10.7807 9.33333 14.0007 9.33333C17.2207 9.33333 19.834 11.9467 19.834 15.1667C19.834 18.3867 17.2207 21 14.0007 21Z" fill="#0096CC"/><path d="M14.0007 19.8333L15.459 16.625L18.6673 15.1667L15.459 13.7083L14.0007 10.5L12.5423 13.7083L9.33398 15.1667L12.5423 16.625L14.0007 19.8333Z" fill="#0096CC"/></svg>',
			'uploadFile' => '<svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.33268 0.333008H1.99935C1.26602 0.333008 0.672682 0.933008 0.672682 1.66634L0.666016 12.333C0.666016 13.0663 1.25935 13.6663 1.99268 13.6663H9.99935C10.7327 13.6663 11.3327 13.0663 11.3327 12.333V4.33301L7.33268 0.333008ZM9.99935 12.333H1.99935V1.66634H6.66602V4.99967H9.99935V12.333ZM3.33268 9.00634L4.27268 9.94634L5.33268 8.89301V11.6663H6.66602V8.89301L7.72602 9.95301L8.66602 9.00634L6.00602 6.33301L3.33268 9.00634Z" fill="#141B38"/></svg>',
			'addRoundIcon' =>'<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.33333 8C1.33333 6.13333 2.4 4.53333 4 3.8V2.33333C1.66667 3.2 0 5.4 0 8C0 10.6 1.66667 12.8 4 13.6667V12.2C2.4 11.4667 1.33333 9.86667 1.33333 8ZM10 2C6.66667 2 4 4.66667 4 8C4 11.3333 6.66667 14 10 14C13.3333 14 16 11.3333 16 8C16 4.66667 13.3333 2 10 2ZM13.3333 8.66667H10.6667V11.3333H9.33333V8.66667H6.66667V7.33333H9.33333V4.66667H10.6667V7.33333H13.3333V8.66667Z" fill="#0068A0"/></svg>',
			'loaderSVG'    => '<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve"><path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"/></path></svg>'
		);
		return $builder_svg_icons;
	}

	public static function sb_other_plugins_modal() {
		check_ajax_referer( 'sbi_nonce', 'sbi_nonce' );

		if ( ! current_user_can( 'activate_plugins' ) || ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error();
		}

		$plugin           = isset( $_POST['plugin'] ) ? sanitize_key( $_POST['plugin'] ) : '';
		$sb_other_plugins = self::install_plugins_popup();
		$plugin           = isset( $sb_other_plugins[ $plugin ] ) ? $sb_other_plugins[ $plugin ] : false;
		if ( ! $plugin ) {
			wp_send_json_error();
		}

		// Build the content for modals
		$output = '<div class="sbi-fb-source-popup sbi-fb-popup-inside sbi-install-plugin-modal">
		<div class="sbi-fb-popup-cls"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#141B38"></path>
		</svg></div>
		<div class="sbi-install-plugin-body sbi-fb-fs">
		<div class="sbi-install-plugin-header">
		<div class="sb-plugin-image">' . $plugin['svgIcon'] . '</div>
		<div class="sb-plugin-name">
		<h3>' . $plugin['name'] . '<span>Free</span></h3>
		<p><span class="sb-author-logo">
		<svg width="13" height="17" viewBox="0 0 13 17" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fill-rule="evenodd" clip-rule="evenodd" d="M5.72226 4.70098C4.60111 4.19717 3.43332 3.44477 2.34321 3.09454C2.73052 4.01824 3.05742 5.00234 3.3957 5.97507C2.72098 6.48209 1.93286 6.8757 1.17991 7.30453C1.82065 7.93788 2.72809 8.3045 3.45109 8.85558C2.87196 9.57021 1.73414 10.3129 1.45689 10.9606C2.65579 10.8103 4.05285 10.5668 5.16832 10.5174C5.41343 11.7495 5.53984 13.1002 5.88845 14.2288C6.40758 12.7353 6.87695 11.192 7.49488 9.79727C8.44849 10.1917 9.61069 10.6726 10.5416 10.9052C9.88842 9.98881 9.29237 9.01536 8.71356 8.02465C9.57007 7.40396 10.4364 6.79309 11.2617 6.14122C10.0952 6.03375 8.88647 5.96834 7.66107 5.91968C7.46633 4.65567 7.5175 3.14579 7.21791 1.98667C6.76462 2.93671 6.2297 3.80508 5.72226 4.70098ZM6.27621 15.1705C6.12214 15.8299 6.62974 16.1004 6.55318 16.5C6.052 16.3273 5.67498 16.2386 5.00213 16.3338C5.02318 15.8194 5.48587 15.7466 5.3899 15.1151C-1.78016 14.3 -1.79456 1.34382 5.3345 0.546422C14.2483 -0.450627 14.528 14.9414 6.27621 15.1705Z" fill="#FE544F"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M7.21769 1.98657C7.51728 3.1457 7.46611 4.65557 7.66084 5.91955C8.88625 5.96824 10.0949 6.03362 11.2615 6.14113C10.4362 6.79299 9.56984 7.40386 8.71334 8.02454C9.29215 9.01527 9.8882 9.98869 10.5414 10.9051C9.61046 10.6725 8.44827 10.1916 7.49466 9.79716C6.87673 11.1919 6.40736 12.7352 5.88823 14.2287C5.53962 13.1001 5.41321 11.7494 5.16809 10.5173C4.05262 10.5667 2.65558 10.8102 1.45666 10.9605C1.73392 10.3128 2.87174 9.57012 3.45087 8.85547C2.72786 8.30438 1.82043 7.93778 1.17969 7.30443C1.93264 6.8756 2.72074 6.482 3.39547 5.97494C3.05719 5.00224 2.73031 4.01814 2.34299 3.09445C3.43308 3.44467 4.60089 4.19707 5.72204 4.70088C6.22947 3.80499 6.7644 2.93662 7.21769 1.98657Z" fill="white"></path>
		</svg>
		</span>
		<span class="sb-author-name">' . $plugin['author'] . '</span>
		</p></div></div>
		<div class="sbi-install-plugin-content">
		<p>' . $plugin['description'] . '</p>';

		$plugin_install_data = array(
			'step'            => 'install',
			'action'          => 'sbi_install_addon',
			'nonce'           => wp_create_nonce( 'sbi-admin' ),
			'plugin'          => $plugin['plugin'],
			'download_plugin' => $plugin['download_plugin'],
		);

		if ( ! $plugin['installed'] ) {
			$output .= sprintf(
				"<button class='sbi-install-plugin-btn sbi-btn-orange' id='sbi_install_op_btn' data-plugin-atts='%s'>%s</button></div></div></div>",
				sbi_json_encode( $plugin_install_data ),
				__( 'Install', 'instagram-feed' )
			);
		}
		if ( $plugin['installed'] && ! $plugin['activated'] ) {
			$plugin_install_data['step']   = 'activate';
			$plugin_install_data['action'] = 'sbi_activate_addon';
			$output                       .= sprintf(
				"<button class='sbi-install-plugin-btn sbi-btn-orange' id='sbi_install_op_btn' data-plugin-atts='%s'>%s</button></div></div></div>",
				sbi_json_encode( $plugin_install_data ),
				__( 'Activate', 'instagram-feed' )
			);
		}
		if ( $plugin['installed'] && $plugin['activated'] ) {
			$output .= sprintf(
				"<button class='sbi-install-plugin-btn sbi-btn-orange' id='sbi_install_op_btn' disabled='disabled'>%s</button></div></div></div>",
				__( 'Plugin installed & activated', 'instagram-feed' )
			);
		}

		new \InstagramFeed\SBI_Response(
			true,
			array(
				'output' => $output
			)
		);
	}

	/**
	 * Plugins information for plugin install modal in all feeds page on select source flow
	 *
	 * @since 6.0
	 *
	 * @return array
	 */
	public static function install_plugins_popup() {
		// get the WordPress's core list of installed plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();

		$is_facebook_installed = false;
		$facebook_plugin       = 'custom-facebook-feed/custom-facebook-feed.php';
		if ( isset( $installed_plugins['custom-facebook-feed-pro/custom-facebook-feed.php'] ) ) {
			$is_facebook_installed = true;
			$facebook_plugin       = 'custom-facebook-feed/custom-facebook-feed.php';
		} elseif ( isset( $installed_plugins['custom-facebook-feed/custom-facebook-feed.php'] ) ) {
			$is_facebook_installed = true;
		}

		$is_twitter_installed = false;
		$twitter_plugin       = 'custom-twitter-feeds/custom-twitter-feed.php';
		if ( isset( $installed_plugins['custom-twitter-feeds-pro/custom-twitter-feed.php'] ) ) {
			$is_twitter_installed = true;
			$twitter_plugin       = 'custom-twitter-feeds-pro/custom-twitter-feed.php';
		} elseif ( isset( $installed_plugins['custom-twitter-feeds/custom-twitter-feed.php'] ) ) {
			$is_twitter_installed = true;
		}

		$is_youtube_installed = false;
		$youtube_plugin       = 'feeds-for-youtube/youtube-feed.php';
		if ( isset( $installed_plugins['youtube-feed-pro/youtube-feed.php'] ) ) {
			$is_youtube_installed = true;
			$youtube_plugin       = 'youtube-feed-pro/youtube-feed.php';
		} elseif ( isset( $installed_plugins['feeds-for-youtube/youtube-feed.php'] ) ) {
			$is_youtube_installed = true;
		}

		return array(
			'facebook' => array(
				'displayName'         => __( 'Facebook', 'instagram-feed' ),
				'name'                => __( 'Facebook Feed', 'instagram-feed' ),
				'author'              => __( 'By Smash Balloon', 'instagram-feed' ),
				'description'         => __( 'To display a Facebook feed, our Facebook plugin is required. </br> It provides a clean and beautiful way to add your Facebook posts to your website. Grab your visitors attention and keep them engaged with your site longer.', 'instagram-feed' ),
				'dashboard_permalink' => admin_url( 'admin.php?page=cff-feed-builder' ),
				'svgIcon'             => '<svg viewBox="0 0 14 15"  width="36" height="36"><path d="M7.00016 0.860001C3.3335 0.860001 0.333496 3.85333 0.333496 7.54C0.333496 10.8733 2.7735 13.64 5.96016 14.14V9.47333H4.26683V7.54H5.96016V6.06667C5.96016 4.39333 6.9535 3.47333 8.48016 3.47333C9.20683 3.47333 9.96683 3.6 9.96683 3.6V5.24667H9.12683C8.30016 5.24667 8.04016 5.76 8.04016 6.28667V7.54H9.8935L9.5935 9.47333H8.04016V14.14C9.61112 13.8919 11.0416 13.0903 12.0734 11.88C13.1053 10.6697 13.6704 9.13043 13.6668 7.54C13.6668 3.85333 10.6668 0.860001 7.00016 0.860001Z" fill="rgb(0, 107, 250)"/></svg>',
				'installed'           => $is_facebook_installed,
				'activated'           => is_plugin_active( $facebook_plugin ),
				'plugin'              => $facebook_plugin,
				'download_plugin'     => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
			),
			'twitter'  => array(
				'displayName'         => __( 'Twitter', 'instagram-feed' ),
				'name'                => __( 'Twitter Feed', 'instagram-feed' ),
				'author'              => __( 'By Smash Balloon', 'instagram-feed' ),
				'description'         => __( 'Custom Twitter Feeds is a highly customizable way to display tweets from your Twitter account. Promote your latest content and update your site content automatically.', 'instagram-feed' ),
				'dashboard_permalink' => admin_url( 'admin.php?page=custom-twitter-feeds' ),
				'svgIcon'             => '<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M33.6905 9C32.5355 9.525 31.2905 9.87 30.0005 10.035C31.3205 9.24 32.3405 7.98 32.8205 6.465C31.5755 7.215 30.1955 7.74 28.7405 8.04C27.5555 6.75 25.8905 6 26.0005 6C20.4755 6 17.5955 8.88 17.5955 12.435C17.5955 12.945 17.6555 13.44 17.7605 13.905C12.4205 13.635 7.66555 11.07 4.50055 7.185C3.94555 8.13 3.63055 9.24 3.63055 10.41C3.63055 12.645 4.75555 14.625 6.49555 15.75C5.43055 15.75 4.44055 15.45 3.57055 15V15.045C3.57055 18.165 5.79055 20.775 8.73055 21.36C7.78664 21.6183 6.79569 21.6543 5.83555 21.465C6.24296 22.7437 7.04085 23.8626 8.11707 24.6644C9.19329 25.4662 10.4937 25.9105 11.8355 25.935C9.56099 27.7357 6.74154 28.709 3.84055 28.695C3.33055 28.695 2.82055 28.665 2.31055 28.605C5.16055 30.435 8.55055 31.5 12.1805 31.5C26.0005 31.5 30.4955 21.69 30.4955 13.185C30.4955 12.9 30.4955 12.63 30.4805 12.345C31.7405 11.445 32.8205 10.305 33.6905 9Z" fill="#1B90EF"/></svg>',
				'installed'           => $is_twitter_installed,
				'activated'           => is_plugin_active( $twitter_plugin ),
				'plugin'              => $twitter_plugin,
				'download_plugin'     => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
			),
			'youtube'  => array(
				'displayName'         => __( 'YouTube', 'instagram-feed' ),
				'name'                => __( 'Feeds for YouTube', 'instagram-feed' ),
				'author'              => __( 'By Smash Balloon', 'instagram-feed' ),
				'description'         => __( 'To display a YouTube feed, our YouTube plugin is required. It provides a simple yet powerful way to display videos from YouTube on your website, Increasing engagement with your channel while keeping visitors on your website.', 'instagram-feed' ),
				'dashboard_permalink' => admin_url( 'admin.php?page=youtube-feed' ),
				'svgIcon'             => '<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 22.5L22.785 18L15 13.5V22.5ZM32.34 10.755C32.535 11.46 32.67 12.405 32.76 13.605C32.865 14.805 32.91 15.84 32.91 16.74L33 18C33 21.285 32.76 23.7 32.34 25.245C31.965 26.595 31.095 27.465 29.745 27.84C29.04 28.035 27.75 28.17 25.77 28.26C23.82 28.365 22.035 28.41 20.385 28.41L18 28.5C11.715 28.5 7.8 28.26 6.255 27.84C4.905 27.465 6.035 26.595 3.66 25.245C3.465 24.54 3.33 23.595 3.24 22.395C3.135 21.195 3.09 20.16 3.09 19.26L3 18C3 14.715 3.24 12.3 3.66 10.755C6.035 9.405 4.905 8.535 6.255 8.16C6.96 7.965 8.25 7.83 10.23 7.74C12.18 7.635 13.965 7.59 15.615 7.59L18 7.5C24.285 7.5 28.2 7.74 29.745 8.16C31.095 8.535 31.965 9.405 32.34 10.755Z" fill="#EB2121"/></svg>',
				'installed'           => $is_youtube_installed,
				'activated'           => is_plugin_active( $youtube_plugin ),
				'plugin'              => $youtube_plugin,
				'download_plugin'     => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
			),
		);
	}

	/**
	 * Gets a list of info
	 * Used in multiple places in the feed creator
	 * Other Platforms + Social Links
	 * Upgrade links
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_smashballoon_info() {
		$smash_info = array(
			'colorSchemes'   => array(
				'facebook'  => '#006BFA',
				'twitter'   => '#1B90EF',
				'instagram' => '#BA03A7',
				'youtube'   => '#EB2121',
				'linkedin'  => '#007bb6',
				'mail'      => '#666',
				'smash'     => '#EB2121'
			),
			'upgrade'        => array(
				'name' => __( 'Upgrade to Pro', 'instagram-feed' ),
				'icon' => 'instagram',
				'link' => 'https://smashballoon.com/instagram-feed/'
			),
			'platforms'      => array(
				array(
					'name' => __( 'Facebook Feed', 'instagram-feed' ),
					'icon' => 'facebook',
					'link' => 'https://smashballoon.com/custom-facebook-feed/?utm_campaign=instagram-free&utm_source=balloon&utm_medium=facebook'
				),
				array(
					'name' => __( 'Twitter Feed', 'instagram-feed' ),
					'icon' => 'twitter',
					'link' => 'https://smashballoon.com/custom-twitter-feeds/?utm_campaign=instagram-free&utm_source=balloon&utm_medium=twitter'
				),
				array(
					'name' => __( 'YouTube Feed', 'instagram-feed' ),
					'icon' => 'youtube',
					'link' => 'https://smashballoon.com/youtube-feed/?utm_campaign=instagram-free&utm_source=balloon&utm_medium=youtube'
				),
				array(
					'name' => __( 'Social Wall Plugin', 'instagram-feed' ),
					'icon' => 'smash',
					'link' => 'https://smashballoon.com/social-wall/?utm_campaign=instagram-free&utm_source=balloon&utm_medium=social-wall ',
				)
			),
			'socialProfiles' => array(
				'facebook' => 'https://www.facebook.com/SmashBalloon/',
				'twitter'  => 'https://twitter.com/smashballoon',
			),
			'morePlatforms'  => array( 'instagram', 'youtube', 'twitter' )
		);

		return $smash_info;
	}

	/**
	 * Text specific to onboarding. Will return an associative array 'active' => false
	 * if onboarding has been dismissed for the user or there aren't any legacy feeds.
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function get_onboarding_text() {
		// TODO: return if no legacy feeds
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		if ( ! isset( $sbi_statuses_option['legacy_onboarding'] ) ) {
			return array( 'active' => false );
		}

		if ( $sbi_statuses_option['legacy_onboarding']['active'] === false
			 || self::onboarding_status() === 'dismissed' ) {
			return array( 'active' => false );
		}

		$type = $sbi_statuses_option['legacy_onboarding']['type'];

		$text = array(
			'active'      => true,
			'type'        => $type,
			'legacyFeeds' => array(
				'heading'     => __( 'Legacy Feed Settings', 'instagram-feed' ),
				'description' => sprintf( __( 'These settings will impact %1$s legacy feeds on your site. You can learn more about what legacy feeds are and how they differ from new feeds %2$shere%3$s.', 'instagram-feed' ), '<span class="cff-fb-count-placeholder"></span>', '<a href="https://smashballoon.com/doc/instagram-legacy-feeds/" target="_blank" rel="noopener">', '</a>' ),
			),
			'getStarted'  => __( 'You can now create and customize feeds individually. Click "Add New" to get started.', 'instagram-feed' ),
		);

		if ( $type === 'single' ) {
			$text['tooltips'] = array(
				array(
					'step'    => 1,
					'heading' => __( 'How you create a feed has changed', 'instagram-feed' ),
					'p'       => __( 'You can now create and customize feeds individually without using shortcode options.', 'instagram-feed' ) . ' ' . __( 'Click "Add New" to get started.', 'instagram-feed' ),
					'pointer' => 'top'
				),
				array(
					'step'    => 2,
					'heading' => __( 'Your existing feed is here', 'instagram-feed' ),
					'p'       => __( 'You can edit your existing feed from here, and all changes will only apply to this feed.', 'instagram-feed' ),
					'pointer' => 'top'
				)
			);
		} else {
			$text['tooltips'] = array(
				array(
					'step'    => 1,
					'heading' => __( 'How you create a feed has changed', 'instagram-feed' ),
					'p'       => __( 'You can now create and customize feeds individually without using shortcode options.', 'instagram-feed' ) . ' ' . __( 'Click "Add New" to get started.', 'instagram-feed' ),
					'pointer' => 'top'
				),
				array(
					'step'    => 2,
					'heading' => __( 'Your existing feeds are under "Legacy" feeds', 'instagram-feed' ),
					'p'       => __( 'You can edit the settings for any existing "legacy" feed (i.e. any feed created prior to this update) here.', 'instagram-feed' ) . ' ' . __( 'This works just like the old settings page and affects all legacy feeds on your site.', 'instagram-feed' )
				),
				array(
					'step'    => 3,
					'heading' => __( 'Existing feeds work as normal', 'instagram-feed' ),
					'p'       => __( 'You don\'t need to update or change any of your existing feeds. They will continue to work as usual.', 'instagram-feed' ) . ' ' . __( 'This update only affects how new feeds are created and customized.', 'instagram-feed' )
				)
			);
		}

		return $text;
	}

	public function get_customizer_onboarding_text() {

		if ( self::onboarding_status( 'customizer' ) === 'dismissed' ) {
			return array( 'active' => false );
		}

		$text = array(
			'active'   => true,
			'type'     => 'customizer',
			'tooltips' => array(
				array(
					'step'    => 1,
					'heading' => __( 'Embedding a Feed', 'instagram-feed' ),
					'p'       => __( 'After you are done customizing the feed, click here to add it to a page or a widget.', 'instagram-feed' ),
					'pointer' => 'top'
				),
				array(
					'step'    => 2,
					'heading' => __( 'Customize', 'instagram-feed' ),
					'p'       => __( 'Change your feed layout, color scheme, or customize individual feed sections here.', 'instagram-feed' ),
					'pointer' => 'top'
				),
				array(
					'step'    => 3,
					'heading' => __( 'Settings', 'instagram-feed' ),
					'p'       => __( 'Update your feed source, filter your posts, or change advanced settings here.', 'instagram-feed' ),
					'pointer' => 'top'
				)
			)
		);

		return $text;
	}

	/**
	 * Text related to the feed customizer
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_customize_screens_text() {
		$text = array(
			'common'              => array(
				'preview'       => __( 'Preview', 'instagram-feed' ),
				'help'          => __( 'Help', 'instagram-feed' ),
				'embed'         => __( 'Embed', 'instagram-feed' ),
				'save'          => __( 'Save', 'instagram-feed' ),
				'sections'      => __( 'Sections', 'instagram-feed' ),
				'enable'        => __( 'Enable', 'instagram-feed' ),
				'background'    => __( 'Background', 'instagram-feed' ),
				'text'          => __( 'Text', 'instagram-feed' ),
				'inherit'       => __( 'Inherit from Theme', 'instagram-feed' ),
				'size'          => __( 'Size', 'instagram-feed' ),
				'color'         => __( 'Color', 'instagram-feed' ),
				'height'        => __( 'Height', 'instagram-feed' ),
				'placeholder'   => __( 'Placeholder', 'instagram-feed' ),
				'select'        => __( 'Select', 'instagram-feed' ),
				'enterText'     => __( 'Enter Text', 'instagram-feed' ),
				'hoverState'    => __( 'Hover State', 'instagram-feed' ),
				'sourceCombine' => __( 'Combine sources from multiple platforms using our Social Wall plugin', 'instagram-feed' ),
			),

			'tabs'                => array(
				'customize' => __( 'Customize', 'instagram-feed' ),
				'settings'  => __( 'Settings', 'instagram-feed' ),
			),
			'overview'            => array(
				'feedLayout'  => __( 'Feed Layout', 'instagram-feed' ),
				'colorScheme' => __( 'Color Scheme', 'instagram-feed' ),
				'header'      => __( 'Header', 'instagram-feed' ),
				'posts'       => __( 'Posts', 'instagram-feed' ),
				'likeBox'     => __( 'Like Box', 'instagram-feed' ),
				'loadMore'    => __( 'Load More Button', 'instagram-feed' ),
			),
			'feedLayoutScreen'    => array(
				'layout'     => __( 'Layout', 'instagram-feed' ),
				'list'       => __( 'List', 'instagram-feed' ),
				'grid'       => __( 'Grid', 'instagram-feed' ),
				'masonry'    => __( 'Masonry', 'instagram-feed' ),
				'carousel'   => __( 'Carousel', 'instagram-feed' ),
				'feedHeight' => __( 'Feed Height', 'instagram-feed' ),
				'number'     => __( 'Number of Posts', 'instagram-feed' ),
				'columns'    => __( 'Columns', 'instagram-feed' ),
				'desktop'    => __( 'Desktop', 'instagram-feed' ),
				'tablet'     => __( 'Tablet', 'instagram-feed' ),
				'mobile'     => __( 'Mobile', 'instagram-feed' ),
				'bottomArea' => array(
					'heading'     => __( 'Tweak Post Styles', 'instagram-feed' ),
					'description' => __( 'Change post background, border radius, shadow etc.', 'instagram-feed' ),
				)
			),
			'colorSchemeScreen'   => array(
				'scheme'        => __( 'Scheme', 'instagram-feed' ),
				'light'         => __( 'Light', 'instagram-feed' ),
				'dark'          => __( 'Dark', 'instagram-feed' ),
				'custom'        => __( 'Custom', 'instagram-feed' ),
				'customPalette' => __( 'Custom Palette', 'instagram-feed' ),
				'background2'   => __( 'Background 2', 'instagram-feed' ),
				'text2'         => __( 'Text 2', 'instagram-feed' ),
				'link'          => __( 'Link', 'instagram-feed' ),
				'bottomArea'    => array(
					'heading'     => __( 'Overrides', 'instagram-feed' ),
					'description' => __( 'Colors that have been overridden from individual post element settings will not change. To change them, you will have to reset overrides.', 'instagram-feed' ),
					'ctaButton'   => __( 'Reset Overrides.', 'instagram-feed' ),
				)
			),
			'headerScreen'        => array(
				'headerType'     => __( 'Header Type', 'instagram-feed' ),
				'visual'         => __( 'Visual', 'instagram-feed' ),
				'coverPhoto'     => __( 'Cover Photo', 'instagram-feed' ),
				'nameAndAvatar'  => __( 'Name and avatar', 'instagram-feed' ),
				'about'          => __( 'About (bio and Likes)', 'instagram-feed' ),
				'displayOutside' => __( 'Display outside scrollable area', 'instagram-feed' ),
				'icon'           => __( 'Icon', 'instagram-feed' ),
				'iconImage'      => __( 'Icon Image', 'instagram-feed' ),
				'iconColor'      => __( 'Icon Color', 'instagram-feed' ),
			),
			// all Lightbox in common
			// all Load More in common
			'likeBoxScreen'       => array(
				'small'                     => __( 'Small', 'instagram-feed' ),
				'large'                     => __( 'Large', 'instagram-feed' ),
				'coverPhoto'                => __( 'Cover Photo', 'instagram-feed' ),
				'customWidth'               => __( 'Custom Width', 'instagram-feed' ),
				'defaultSetTo'              => __( 'By default, it is set to auto', 'instagram-feed' ),
				'width'                     => __( 'Width', 'instagram-feed' ),
				'customCTA'                 => __( 'Custom CTA', 'instagram-feed' ),
				'customCTADescription'      => __( 'This toggles the custom CTA like "Show now" and "Contact"', 'instagram-feed' ),
				'showFans'                  => __( 'Show Fans', 'instagram-feed' ),
				'showFansDescription'       => __( 'Show visitors which of their friends follow your page', 'instagram-feed' ),
				'displayOutside'            => __( 'Display outside scrollable area', 'instagram-feed' ),
				'displayOutsideDescription' => __( 'Make the like box fixed by moving it outside the scrollable area', 'instagram-feed' ),
			),
			'postsScreen'         => array(
				'thumbnail'           => __( 'Thumbnail', 'instagram-feed' ),
				'half'                => __( 'Half width', 'instagram-feed' ),
				'full'                => __( 'Full width', 'instagram-feed' ),
				'useFull'             => __( 'Use full width layout when post width is less than 500px', 'instagram-feed' ),
				'postStyle'           => __( 'Post Style', 'instagram-feed' ),
				'editIndividual'      => __( 'Edit Individual Elements', 'instagram-feed' ),
				'individual'          => array(
					'description'                => __( 'Hide or show individual elements of a post or edit their options', 'instagram-feed' ),
					'name'                       => __( 'Name', 'instagram-feed' ),
					'edit'                       => __( 'Edit', 'instagram-feed' ),
					'postAuthor'                 => __( 'Post Author', 'instagram-feed' ),
					'postText'                   => __( 'Post Text', 'instagram-feed' ),
					'date'                       => __( 'Date', 'instagram-feed' ),
					'photosVideos'               => __( 'Photos/Videos', 'instagram-feed' ),
					'likesShares'                => __( 'Likes, Shares and Comments', 'instagram-feed' ),
					'eventTitle'                 => __( 'Event Title', 'instagram-feed' ),
					'eventDetails'               => __( 'Event Details', 'instagram-feed' ),
					'postAction'                 => __( 'Post Action Links', 'instagram-feed' ),
					'sharedPostText'             => __( 'Shared Post Text', 'instagram-feed' ),
					'sharedLinkBox'              => __( 'Shared Link Box', 'instagram-feed' ),
					'postTextDescription'        => __( 'The main text of the Instagram post', 'instagram-feed' ),
					'maxTextLength'              => __( 'Maximum Text Length', 'instagram-feed' ),
					'characters'                 => __( 'Characters', 'instagram-feed' ),
					'linkText'                   => __( 'Link text to Instagram post', 'instagram-feed' ),
					'postDateDescription'        => __( 'The date of the post', 'instagram-feed' ),
					'format'                     => __( 'Format', 'instagram-feed' ),
					'custom'                     => __( 'Custom', 'instagram-feed' ),
					'learnMoreFormats'           => '<a href="https://smashballoon.com/doc/date-formatting-reference/" target="_blank" rel="noopener">' . __( 'Learn more about custom formats', 'instagram-feed' ) . '</a>',
					'addTextBefore'              => __( 'Add text before date', 'instagram-feed' ),
					'addTextBeforeEG'            => __( 'E.g. Posted', 'instagram-feed' ),
					'addTextAfter'               => __( 'Add text after date', 'instagram-feed' ),
					'addTextAfterEG'             => __( 'E.g. - posted date', 'instagram-feed' ),
					'timezone'                   => __( 'Timezone', 'instagram-feed' ),
					'tzDescription'              => __( 'Timezone settings are global across all feeds. To update it use the global settings.', 'instagram-feed' ),
					'tzCTAText'                  => __( 'Go to Global Settings', 'instagram-feed' ),
					'photosVideosDescription'    => __( 'Any photos or videos in your posts', 'instagram-feed' ),
					'useOnlyOne'                 => __( 'Use only one image per post', 'instagram-feed' ),
					'postActionLinksDescription' => __( 'The "View on Instagram" and "Share" links at the bottom of each post', 'instagram-feed' ),
					'viewOnFBLink'               => __( 'View on Instagram link', 'instagram-feed' ),
					'viewOnFBLinkDescription'    => __( 'Toggle "View on Instagram" link below each post', 'instagram-feed' ),
					'customizeText'              => __( 'Customize Text', 'instagram-feed' ),
					'shareLink'                  => __( 'Share Link', 'instagram-feed' ),
					'shareLinkDescription'       => __( 'Toggle "Share" link below each post', 'instagram-feed' ),
					'likesSharesDescription'     => __( 'The comments box displayed at the bottom of each timeline post', 'instagram-feed' ),
					'iconTheme'                  => __( 'Icon Theme', 'instagram-feed' ),
					'auto'                       => __( 'Auto', 'instagram-feed' ),
					'light'                      => __( 'Light', 'instagram-feed' ),
					'dark'                       => __( 'Dark', 'instagram-feed' ),
					'expandComments'             => __( 'Expand comments box by default', 'instagram-feed' ),
					'hideComment'                => __( 'Hide comment avatars', 'instagram-feed' ),
					'showLightbox'               => __( 'Show comments in lightbox', 'instagram-feed' ),
					'eventTitleDescription'      => __( 'The title of an event', 'instagram-feed' ),
					'eventDetailsDescription'    => __( 'The information associated with an event', 'instagram-feed' ),
					'textSize'                   => __( 'Text Size', 'instagram-feed' ),
					'textColor'                  => __( 'Text Color', 'instagram-feed' ),
					'sharedLinkBoxDescription'   => __( "The link info box that's created when a link is shared in a Instagram post", 'instagram-feed' ),
					'boxStyle'                   => __( 'Box Style', 'instagram-feed' ),
					'removeBackground'           => __( 'Remove background/border', 'instagram-feed' ),
					'linkTitle'                  => __( 'Link Title', 'instagram-feed' ),
					'linkURL'                    => __( 'Link URL', 'instagram-feed' ),
					'linkDescription'            => __( 'Link Description', 'instagram-feed' ),
					'chars'                      => __( 'chars', 'instagram-feed' ),
					'sharedPostDescription'      => __( 'The description text associated with shared photos, videos, or links', 'instagram-feed' ),
				),
				'postType'            => __( 'Post Type', 'instagram-feed' ),
				'boxed'               => __( 'boxed', 'instagram-feed' ),
				'regular'             => __( 'Regular', 'instagram-feed' ),
				'indvidualProperties' => __( 'Individual Properties', 'instagram-feed' ),
				'backgroundColor'     => __( 'Background Color', 'instagram-feed' ),
				'borderRadius'        => __( 'Border Radius', 'instagram-feed' ),
				'boxShadow'           => __( 'Box Shadow', 'instagram-feed' ),
			),
			'shoppableFeedScreen' => array(
				'heading1'     => __( 'Upgrade to Pro and make your Instagram Feed Shoppable', 'instagram-feed' ),
				'description1' => __( 'This feature links the post to the one specified in your caption.<br/><br/>Don’t want to add links to the caption? You can add links manually to each post.<br/><br><br>', 'instagram-feed' ),
				'heading2'     => __( 'Tap “Add” or “Update” on an<br/>image to add/update it’s URL', 'instagram-feed' ),

			)
		);

		$text['onboarding'] = $this->get_customizer_onboarding_text();

		return $text;
	}

	/**
	 * Returns an associate array of all existing sources along with their data
	 *
	 * @param int $page
	 *
	 * @return array
	 *
	 * @since 6.0
	 */

	public static function get_source_list( $page = 1 ) {
		$args['page'] = $page;
		$source_data  = SBI_Db::source_query( $args );
		$encryption   = new \SB_Instagram_Data_Encryption();

		$return = array();
		foreach ( $source_data as $source ) {
			$info                  = ! empty( $source['info'] ) ? json_decode( $encryption->decrypt( $source['info'] ), true ) : array();
			$source['header_data'] = $info;

			$settings = array( 'gdpr' => 'no' );

			$avatar = \SB_Instagram_Parse::get_avatar( $info, $settings );

			if ( \SB_Instagram_Connected_Account::local_avatar_exists( $source['username'] ) ) {
				$source['local_avatar_url'] = \SB_Instagram_Connected_Account::get_local_avatar_url( $source['username'] );
				$source['local_avatar']     = \SB_Instagram_Connected_Account::get_local_avatar_url( $source['username'] );
			} else {
				$source['local_avatar'] = false;
			}

			$source['avatar_url'] = is_bool( $avatar ) ? \SB_Instagram_Parse::get_avatar_url( $info, $settings ) : false;
			$source['just_added'] = ( ! empty( $_GET['sbi_username'] ) && isset( $info['username'] ) && $info['username'] === $_GET['sbi_username'] );

			$source['error_encryption'] = false;
			if ( isset( $source['access_token'] ) && strpos( $source['access_token'], 'IG' ) === false && strpos( $source['access_token'], 'EA' ) === false && ! $encryption->decrypt( $source['access_token'] ) ) {
				$source['error_encryption'] = true;
			}
			$return[] = $source;
		}

		return $return;
	}

	/**
	 * Get Links with UTM
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public static function get_links_with_utm() {
		$license_key = null;
		if ( get_option( 'sbi_license_key' ) ) {
			$license_key = get_option( 'sbi_license_key' );
		}
		$all_access_bundle       = sprintf( 'https://smashballoon.com/all-access/?license_key=%s&upgrade=true&utm_campaign=instagram-free&utm_source=all-feeds&utm_medium=footer-banner&utm_content=learn-more', $license_key );
		$all_access_bundle_popup = sprintf( 'https://smashballoon.com/all-access/?license_key=%s&upgrade=true&utm_campaign=instagram-free&utm_source=balloon&utm_medium=all-access', $license_key );
		$sourceCombineCTA        = sprintf( 'https://smashballoon.com/social-wall/?license_key=%s&upgrade=true&utm_campaign=instagram-free&utm_source=customizer&utm_medium=sources&utm_content=social-wall', $license_key );

		return array(
			'allAccessBundle'  => $all_access_bundle,
			'popup'            => array(
				'allAccessBundle' => $all_access_bundle_popup,
				'fbProfile'       => 'https://www.facebook.com/SmashBalloon/',
				'twitterProfile'  => 'https://twitter.com/smashballoon',
			),
			'sourceCombineCTA' => $sourceCombineCTA,
			'multifeedCTA'     => 'https://smashballoon.com/extensions/multifeed/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=sources&utm_content=multifeed',
			'doc'              => 'https://smashballoon.com/docs/instagram/?utm_campaign=instagram-free&utm_source=support&utm_medium=view-documentation-button&utm_content=view-documentation',
			'blog'             => 'https://smashballoon.com/blog/?utm_campaign=instagram-free&utm_source=support&utm_medium=view-blog-button&utm_content=view-blog',
			'gettingStarted'   => 'https://smashballoon.com/docs/getting-started/?instagram&utm_campaign=instagram-free&utm_source=support&utm_medium=getting-started-button&utm_content=getting-started',
		);
	}

	public static function get_social_wall_links() {
		return array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=sbi-feed-builder' ) ) . '">' . __( 'All Feeds', 'instagram-feed' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=sbi-settings' ) ) . '">' . __( 'Settings', 'instagram-feed' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=sbi-oembeds-manager' ) ) . '">' . __( 'oEmbeds', 'instagram-feed' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=sbi-extensions-manager' ) ) . '">' . __( 'Extensions', 'instagram-feed' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=sbi-about-us' ) ) . '">' . __( 'About Us', 'instagram-feed' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=sbi-support' ) ) . '">' . __( 'Support', 'instagram-feed' ) . '</a>',
		);
	}

	/**
	 * Returns an associate array of all existing feeds along with their data
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_feed_list( $feeds_args = array() ) {
		if ( ! empty( $_GET['feed_id'] ) ) {
			return array();
		}
		$feeds_data = SBI_Db::feeds_query( $feeds_args );

		$i = 0;
		foreach ( $feeds_data as $single_feed ) {
			$args  = array(
				'feed_id'       => '*' . $single_feed['id'],
				'html_location' => array( 'content' ),
			);
			$count = \SB_Instagram_Feed_Locator::count( $args );

			$content_locations = \SB_Instagram_Feed_Locator::instagram_feed_locator_query( $args );

			// if this is the last page, add in the header footer and sidebar locations
			if ( count( $content_locations ) < SBI_Db::RESULTS_PER_PAGE ) {

				$args            = array(
					'feed_id'       => '*' . $single_feed['id'],
					'html_location' => array( 'header', 'footer', 'sidebar' ),
					'group_by'      => 'html_location',
				);
				$other_locations = \SB_Instagram_Feed_Locator::instagram_feed_locator_query( $args );

				$locations = array();

				$combined_locations = array_merge( $other_locations, $content_locations );
			} else {
				$combined_locations = $content_locations;
			}

			foreach ( $combined_locations as $location ) {
				$page_text = get_the_title( $location['post_id'] );
				if ( $location['html_location'] === 'header' ) {
					$html_location = __( 'Header', 'instagram-feed' );
				} elseif ( $location['html_location'] === 'footer' ) {
					$html_location = __( 'Footer', 'instagram-feed' );
				} elseif ( $location['html_location'] === 'sidebar' ) {
					$html_location = __( 'Sidebar', 'instagram-feed' );
				} else {
					$html_location = __( 'Content', 'instagram-feed' );
				}
				$shortcode_atts = json_decode( $location['shortcode_atts'], true );
				$shortcode_atts = is_array( $shortcode_atts ) ? $shortcode_atts : array();

				$full_shortcode_string = '[instagram-feed';
				foreach ( $shortcode_atts as $key => $value ) {
					if ( ! empty( $value ) ) {
						$full_shortcode_string .= ' ' . esc_html( $key ) . '="' . esc_html( $value ) . '"';
					}
				}
				$full_shortcode_string .= ']';

				$locations[] = array(
					'link'          => esc_url( get_the_permalink( $location['post_id'] ) ),
					'page_text'     => $page_text,
					'html_location' => $html_location,
					'shortcode'     => $full_shortcode_string
				);
			}
			$feeds_data[ $i ]['instance_count']   = $count;
			$feeds_data[ $i ]['location_summary'] = $locations;
			$settings                             = json_decode( $feeds_data[ $i ]['settings'], true );

			$settings['feed'] = $single_feed['id'];

			$instagram_feed_settings = new \SB_Instagram_Settings( $settings, sbi_defaults() );

			$feeds_data[ $i ]['settings'] = $instagram_feed_settings->get_settings();

			$i++;
		}
		return $feeds_data;
	}

	/**
	 * Returns an associate array of all existing sources along with their data
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function get_legacy_feed_list() {
		if ( ! empty( $_GET['feed_id'] ) ) {
			return array();
		}
		$sbi_statuses = get_option( 'sbi_statuses', array() );
		$sources_list = self::get_source_list();
		if ( empty( $sbi_statuses['support_legacy_shortcode'] ) ) {
			return array();
		}

		$args       = array(
			'html_location' => array( 'header', 'footer', 'sidebar', 'content' ),
			'group_by'      => 'shortcode_atts',
			'page'          => 1
		);
		$feeds_data = \SB_Instagram_Feed_Locator::legacy_instagram_feed_locator_query( $args );
		if ( empty( $feeds_data ) ) {
			$args       = array(
				'html_location' => array( 'header', 'footer', 'sidebar', 'content' ),
				'group_by'      => 'shortcode_atts',
				'page'          => 1
			);
			$feeds_data = \SB_Instagram_Feed_Locator::legacy_instagram_feed_locator_query( $args );
		}

		$feed_saver = new SBI_Feed_Saver( 'legacy' );
		$settings   = $feed_saver->get_feed_settings();

		$default_type = 'timeline';

		if ( isset( $settings['feedtype'] ) ) {
			$default_type = $settings['feedtype'];

		} elseif ( isset( $settings['type'] ) ) {
			if ( strpos( $settings['type'], ',' ) === false ) {
				$default_type = $settings['type'];
			}
		}
		$i       = 0;
		$reindex = false;
		foreach ( $feeds_data as $single_feed ) {
			$args              = array(
				'shortcode_atts' => $single_feed['shortcode_atts'],
				'html_location'  => array( 'content' ),
			);
			$content_locations = \SB_Instagram_Feed_Locator::instagram_feed_locator_query( $args );

			$count = \SB_Instagram_Feed_Locator::count( $args );
			if ( count( $content_locations ) < SBI_Db::RESULTS_PER_PAGE ) {

				$args            = array(
					'feed_id'       => $single_feed['feed_id'],
					'html_location' => array( 'header', 'footer', 'sidebar' ),
					'group_by'      => 'html_location'
				);
				$other_locations = \SB_Instagram_Feed_Locator::instagram_feed_locator_query( $args );

				$combined_locations = array_merge( $other_locations, $content_locations );
			} else {
				$combined_locations = $content_locations;
			}

			$locations = array();
			foreach ( $combined_locations as $location ) {
				$page_text = get_the_title( $location['post_id'] );
				if ( $location['html_location'] === 'header' ) {
					$html_location = __( 'Header', 'instagram-feed' );
				} elseif ( $location['html_location'] === 'footer' ) {
					$html_location = __( 'Footer', 'instagram-feed' );
				} elseif ( $location['html_location'] === 'sidebar' ) {
					$html_location = __( 'Sidebar', 'instagram-feed' );
				} else {
					$html_location = __( 'Content', 'instagram-feed' );
				}
				$shortcode_atts = json_decode( $location['shortcode_atts'], true );
				$shortcode_atts = is_array( $shortcode_atts ) ? $shortcode_atts : array();

				$full_shortcode_string = '[instagram-feed';
				foreach ( $shortcode_atts as $key => $value ) {
					if ( ! empty( $value ) ) {
						if ( is_array( $value ) ) {
							$value = implode( ',', $value );
						}
						$full_shortcode_string .= ' ' . esc_html( $key ) . '="' . esc_html( $value ) . '"';
					}
				}
				$full_shortcode_string .= ']';

				$locations[] = array(
					'link'          => esc_url( get_the_permalink( $location['post_id'] ) ),
					'page_text'     => $page_text,
					'html_location' => $html_location,
					'shortcode'     => $full_shortcode_string
				);
			}
			$shortcode_atts = json_decode( $feeds_data[ $i ]['shortcode_atts'], true );
			$shortcode_atts = is_array( $shortcode_atts ) ? $shortcode_atts : array();

			$full_shortcode_string = '[instagram-feed';
			foreach ( $shortcode_atts as $key => $value ) {
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$value = implode( ',', $value );
					}
					$full_shortcode_string .= ' ' . esc_html( $key ) . '="' . esc_html( $value ) . '"';
				}
			}
			$full_shortcode_string .= ']';

			$feeds_data[ $i ]['shortcode']        = $full_shortcode_string;
			$feeds_data[ $i ]['instance_count']   = $count;
			$feeds_data[ $i ]['location_summary'] = $locations;
			$feeds_data[ $i ]['feed_name']        = self::get_legacy_feed_name( $sources_list, $feeds_data[ $i ]['feed_id'] );
			$feeds_data[ $i ]['feed_type']        = $default_type;

			if ( isset( $shortcode_atts['feedtype'] ) ) {
				$feeds_data[ $i ]['feed_type'] = $shortcode_atts['feedtype'];

			} elseif ( isset( $shortcode_atts['type'] ) ) {
				if ( strpos( $shortcode_atts['type'], ',' ) === false ) {
					$feeds_data[ $i ]['feed_type'] = $shortcode_atts['type'];
				}
			}

			if ( isset( $feeds_data[ $i ]['id'] ) ) {
				unset( $feeds_data[ $i ]['id'] );
			}

			if ( isset( $feeds_data[ $i ]['html_location'] ) ) {
				unset( $feeds_data[ $i ]['html_location'] );
			}

			if ( isset( $feeds_data[ $i ]['last_update'] ) ) {
				unset( $feeds_data[ $i ]['last_update'] );
			}

			if ( isset( $feeds_data[ $i ]['post_id'] ) ) {
				unset( $feeds_data[ $i ]['post_id'] );
			}

			if ( ! empty( $shortcode_atts['feed'] ) ) {
				$reindex = true;
				unset( $feeds_data[ $i ] );
			}

			if ( isset( $feeds_data[ $i ]['shortcode_atts'] ) ) {
				unset( $feeds_data[ $i ]['shortcode_atts'] );
			}

			$i++;
		}

		if ( $reindex ) {
			$feeds_data = array_values( $feeds_data );
		}

		// if there were no feeds found in the locator table we still want the legacy settings to be available
		// if it appears as though they had used version 3.x or under at some point.
		if ( empty( $feeds_data )
			 && ! is_array( $sbi_statuses['support_legacy_shortcode'] )
			 && ( $sbi_statuses['support_legacy_shortcode'] ) ) {

			$feeds_data = array(
				array(
					'feed_id'          => __( 'Legacy Feed', 'instagram-feed' ) . ' ' . __( '(unknown location)', 'instagram-feed' ),
					'feed_name'        => __( 'Legacy Feed', 'instagram-feed' ) . ' ' . __( '(unknown location)', 'instagram-feed' ),
					'shortcode'        => '[instagram-feed]',
					'feed_type'        => '',
					'instance_count'   => false,
					'location_summary' => array()
				)
			);
		}

		return $feeds_data;
	}

	public static function get_legacy_feed_name( $sources_list, $source_id ) {
		foreach ( $sources_list as $source ) {
			if ( $source['account_id'] == $source_id ) {
				return $source['username'];
			}
		}
		return $source_id;
	}

	/**
	 * Status of the onboarding sequence for specific user
	 *
	 * @return string|boolean
	 *
	 * @since 6.0
	 */
	public static function onboarding_status( $type = 'newuser' ) {
		$onboarding_statuses = get_user_meta( get_current_user_id(), 'sbi_onboarding', true );
		$status              = false;
		if ( ! empty( $onboarding_statuses ) ) {
			$statuses = maybe_unserialize( $onboarding_statuses );
			$status   = isset( $statuses[ $type ] ) ? $statuses[ $type ] : false;
		}

		return $status;
	}

	/**
	 * Update status of onboarding sequence for specific user
	 *
	 * @return string|boolean
	 *
	 * @since 6.0
	 */
	public static function update_onboarding_meta( $value, $type = 'newuser' ) {
		$onboarding_statuses = get_user_meta( get_current_user_id(), 'sbi_onboarding', true );
		if ( ! empty( $onboarding_statuses ) ) {
			$statuses          = maybe_unserialize( $onboarding_statuses );
			$statuses[ $type ] = $value;
		} else {
			$statuses = array(
				$type => $value
			);
		}

		$statuses = maybe_serialize( $statuses );

		update_user_meta( get_current_user_id(), 'sbi_onboarding', $statuses );
	}

	/**
	 * Used to dismiss onboarding using AJAX
	 *
	 * @since 6.0
	 */
	public static function after_dismiss_onboarding() {
		check_ajax_referer( 'sbi-admin', 'nonce' );

		$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';
		$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

		if ( current_user_can( $cap ) ) {
			$type = 'newuser';
			if ( isset( $_POST['was_active'] ) ) {
				$type = sanitize_key( $_POST['was_active'] );
			}
			self::update_onboarding_meta( 'dismissed', $type );
		}
		wp_die();
	}

	public static function add_customizer_att( $atts ) {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}
		$atts['feedtype'] = 'customizer';
		return $atts;
	}

	/**
	 * Feed Builder Wrapper.
	 *
	 * @since 6.0
	 */
	public function feed_builder() {
		include_once SBI_BUILDER_DIR . 'templates/builder.php';
	}

	/**
	 * For types listed on the top of the select feed type screen
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_feed_types() {
		$feed_types = array(
			array(
				'type'        => 'user',
				'title'       => __( 'User Timeline', 'instagram-feed' ),
				'description' => __( 'Fetch posts from your Instagram profile', 'instagram-feed' ),
				'icon'        => 'usertimelineIcon'
			),
			/*
			array(
				'type' => 'hashtag',
				'title' => __( 'Public Hashtag', 'instagram-feed' ),
				'description' => __( 'Fetch posts from a public Instagram hashtag', 'instagram-feed' ),
				'tooltip' => __( 'Hashtag feeds require a connected Instagram business account', 'instagram-feed' ),
				'businessRequired' => true,
				'icon'	=>  'publichashtagIcon'
			),
			array(
				'type' => 'tagged',
				'title' => __( 'Tagged Posts', 'instagram-feed' ),
				'description' => __( 'Display posts your Instagram account has been tagged in', 'instagram-feed' ),
				'tooltip' => __( 'Tagged posts feeds require a connected Instagram business account', 'instagram-feed' ),
				'businessRequired' => true,
				'icon'	=>  'taggedpostsIcon'
			),
			array(
				'type' => 'socialwall',
				'title' => __( 'Social Wall', 'instagram-feed' ) . '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.94901 13.7934L6.86234 11.2401C7.90901 10.8534 8.88901 10.3334 9.79568 9.72677L7.94901 13.7934ZM2.95568 7.33344L0.402344 6.24677L4.46901 4.4001C3.86234 5.30677 3.34234 6.28677 2.95568 7.33344ZM13.6023 0.593436C13.6023 0.593436 10.3023 -0.820564 6.52901 2.95344C5.06901 4.41344 4.19568 6.0201 3.62901 7.42677C3.44234 7.92677 3.56901 8.47344 3.93568 8.84677L5.35568 10.2601C5.72234 10.6334 6.26901 10.7534 6.76901 10.5668C8.44804 9.92657 9.97256 8.93825 11.2423 7.66677C15.0157 3.89344 13.6023 0.593436 13.6023 0.593436ZM8.88901 5.30677C8.36901 4.78677 8.36901 3.9401 8.88901 3.4201C9.40901 2.9001 10.2557 2.9001 10.7757 3.4201C11.289 3.9401 11.2957 4.78677 10.7757 5.30677C10.2557 5.82677 9.40901 5.82677 8.88901 5.30677ZM4.02247 13.0001L5.78234 11.2401C5.55568 11.1801 5.33568 11.0801 5.13568 10.9401L3.08247 13.0001H4.02247ZM1.1958 13.0001H2.1358L4.64901 10.4934L3.70234 9.55344L1.1958 12.0601V13.0001ZM1.1958 11.1134L3.25568 9.0601C3.11568 8.8601 3.01568 8.64677 2.95568 8.41344L1.1958 10.1734V11.1134Z" fill="#FE544F"/></svg>',
				'description' => __( 'Create a feed with sources from different social platforms', 'instagram-feed' ),
				'icon'	=>  'socialwall1Icon'
			)
			*/
		);

		return $feed_types;
	}

	/**
	 * For types listed on the bottom of the select feed type screen
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_advanced_feed_types() {
		$feed_types = array(
			array(
				'type'             => 'hashtag',
				'title'            => __( 'Public Hashtag', 'instagram-feed' ),
				'description'      => __( 'Fetch posts from a public Instagram hashtag', 'instagram-feed' ),
				'tooltip'          => __( 'Hashtag feeds require a connected Instagram business account', 'instagram-feed' ),
				'businessRequired' => true,
				'icon'             => 'publichashtagIcon'
			),
			array(
				'type'             => 'tagged',
				'title'            => __( 'Tagged Posts', 'instagram-feed' ),
				'description'      => __( 'Display posts your Instagram account has been tagged in', 'instagram-feed' ),
				'tooltip'          => __( 'Tagged posts feeds require a connected Instagram business account', 'instagram-feed' ),
				'businessRequired' => true,
				'icon'             => 'taggedpostsIcon'
			),
			array(
				'type'        => 'socialwall',
				'title'       => __( 'Social Wall', 'instagram-feed' ),
				'description' => __( 'Create a feed with sources from different social platforms', 'instagram-feed' ),
				'icon'        => 'socialwall1Icon'
			),
		);

		return $feed_types;
	}

	/**
	 * Personal Account
	 *
	 * @return array
	 *
	 * @since 6.0.8
	 */
	public static function personal_account_screen_text() {
		return array(
			'mainHeading1'    => __( 'We’re almost there...', 'instagram-feed' ),
			'mainHeading2'    => __( 'Update Personal Account', 'instagram-feed' ),
			'mainHeading3'    => __( 'Add Instagram Profile Picture and Bio', 'instagram-feed' ),
			'mainDescription' => __( 'Instagram does not provide us access to your profile picture or bio for personal accounts. Would you like to set up a custom profile photo and bio?.', 'instagram-feed' ),
			'bioLabel'        => __( 'Bio (140 Characters)', 'instagram-feed' ),
			'bioPlaceholder'  => __( 'Add your profile bio here', 'instagram-feed' ),
			'confirmBtn'      => __( 'Yes, let\'s do it', 'instagram-feed' ),
			'cancelBtn'       => __( 'No, maybe later', 'instagram-feed' ),
			'uploadBtn'       => __( 'Upload Profile Picture', 'instagram-feed' )
		);
	}

}

