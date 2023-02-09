<?php
/**
 * Class SB_Instagram_Settings
 *
 * Creates organized settings from shortcode settings and settings
 * from the options table.
 *
 * Also responsible for creating transient names/feed ids based on
 * feed settings
 *
 * @since 2.0/5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Settings {
	/**
	 * @var array
	 */
	protected $atts;

	/**
	 * @var array
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array
	 */
	protected $feed_type_and_terms;

	/**
	 * @var array
	 */
	protected $connected_accounts;

	/**
	 * @var array
	 */
	protected $connected_accounts_in_feed;

	/**
	 * @var string
	 */
	protected $transient_name;

	/**
	 * SB_Instagram_Settings constructor.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @param array $atts shortcode settings
	 * @param array $db settings from the wp_options table
	 */
	public function __construct( $atts, $db, $preview_settings = false ) {
		if ( empty( $atts['feed'] ) ) {
			$sbi_statuses       = get_option( 'sbi_statuses', array() );
			if ( empty( $sbi_statuses['support_legacy_shortcode'] ) ) {
				if ( empty( $atts ) ) {
					$atts = array();
				}

				$atts['feed'] = 1;
			}
		}

		$this->feed_type_and_terms = array();
		$this->connected_accounts_in_feed = array();
		$this->atts = $this->filter_atts_for_legacy( $atts );
		$this->db   = $db;

		if ( ! empty( $atts['feed'] ) && $atts['feed'] !== 'legacy' ) {
			$this->settings = SB_Instagram_Settings::get_settings_by_feed_id( $atts['feed'], $preview_settings );
			if ( ! empty( $this->settings ) ) {
				$this->settings['customizer'] = isset($atts['customizer']) && $atts['customizer'] == true ? true : false;
				$this->settings['feed'] = intval( $atts['feed'] );

				$this->connected_accounts = $this->get_connected_accounts_from_settings();

				if ( $this->settings['type'] === 'mixed' ) {
					$this->atts['tagged'] = $this->settings['tagged'];
					$this->atts['user'] = $this->settings['id'];
					$this->atts['hashtag'] = $this->settings['hashtag'];
				}

				foreach ( $this->atts as $key => $value ) {
					$this->settings[ $key ] = $value;
				}
			} else {
				return array();
			}
		}

		if ( empty( $this->settings ) ) {
			if ( ! empty( $preview_settings ) ) {
				$this->settings = $preview_settings;
			} else {
				$sbi_statuses = get_option( 'sbi_statuses', array() );

				if ( ! empty( $sbi_statuses['support_legacy_shortcode'] ) ) {
					$legacy_settings_option = self::get_legacy_feed_settings();
					if ( empty( $legacy_settings_option ) ) {
						$this->settings = SB_Instagram_Settings::get_settings_by_legacy_shortcode( $atts, $db );
					} else {
						$this->settings = wp_parse_args( $this->atts, $legacy_settings_option );
					}
				}

			}
			if ( ! empty( $this->settings ) ) {
				if ( ! is_array( $this->settings ) ) {
					$this->settings = array();
				}
				$this->settings['customizer'] = isset($atts['customizer']) && $atts['customizer'] == true ? true : false;

				$this->settings['feed'] = 'legacy';

				$this->connected_accounts = $this->get_connected_accounts_from_settings();
			}
			$this->settings = wp_parse_args( $this->settings, \InstagramFeed\Builder\SBI_Feed_Saver::settings_defaults() );

		}

		if ( empty( $this->settings ) ) {
			return;
		}

		$this->settings = $this->filter_for_builder( $this->settings, $atts );

		$this->settings = $this->filter_for_legacy( $this->settings, $atts );


		if ( ! empty( $this->settings['customizer'] ) ) {
			$this->settings = $this->filter_for_customizer( $this->settings );
		}

		if ( empty( $this->settings['feed_is_moderated'] ) ) {
			$this->settings['feed_is_moderated'] = false;
		}


		$this->connected_accounts = apply_filters( 'sbi_connected_accounts', $this->connected_accounts, $this->atts );

		$this->settings['customtemplates'] = $this->settings['customtemplates'] === 'true' || $this->settings['customtemplates'] === 'on' || $this->settings['customtemplates'] === true;
		if ( isset( $_GET['sbi_debug'] ) ) {
			$this->settings['customtemplates'] = false;
		}
		$this->settings['minnum'] = max( (int)$this->settings['num'], (int)$this->settings['nummobile'] );
		$this->settings['showbio'] = $this->settings['showbio'] === 'true' || $this->settings['showbio'] === 'on' || $this->settings['showbio'] === true;
		if ( isset( $this->atts['showbio'] ) && $this->atts['showbio'] === 'false' ) {
			$this->settings['showbio'] = false;
		}
		if ( isset( $this->atts['showheader'] ) && $this->atts['showheader'] === 'false' ) {
			$this->settings['showheader'] = false;
		} elseif ( isset( $this->atts['showheader'] ) && $this->atts['showheader'] === 'true' ) {
			$this->settings['showheader'] = true;
		}
		$this->settings['heightunit'] = ! empty( $this->settings['heightunit'] ) ? $this->settings['heightunit'] : 'px';
		$this->settings['disable_resize'] = isset( $db['sb_instagram_disable_resize'] ) && ($db['sb_instagram_disable_resize'] === 'on' || $db['sb_instagram_disable_resize'] === true);
		$this->settings['favor_local'] = true;
		$this->settings['backup_cache_enabled'] = ! isset( $db['sb_instagram_backup'] ) || $db['sb_instagram_backup'] === 'on' || $db['sb_instagram_backup'] === true;
		$this->settings['headeroutside'] = ($this->settings['headeroutside'] === true || $this->settings['headeroutside'] === 'on' || $this->settings['headeroutside'] === 'true');
		$this->settings['disable_js_image_loading'] = isset( $db['disable_js_image_loading'] ) && ($db['disable_js_image_loading'] === 'on' || $db['disable_js_image_loading'] === true);
		$this->settings['sb_ajax_initial'] = isset( $db['sb_ajax_initial'] ) && ($db['sb_ajax_initial'] === 'on' || $db['sb_ajax_initial'] === true);
		$this->settings['ajax_post_load'] = $this->settings['sb_ajax_initial'];

		switch ( $db['sbi_cache_cron_interval'] ) {
			case '30mins' :
				$this->settings['sbi_cache_cron_interval'] = 60*30;
				break;
			case '1hour' :
				$this->settings['sbi_cache_cron_interval'] = 60*60;
				break;
			default :
				$this->settings['sbi_cache_cron_interval'] = 60*60*12;
		}

		$this->settings['showfollow'] = ( $this->settings['showfollow'] == 'on' || $this->settings['showfollow'] == 'true' || $this->settings['showfollow'] == true ) && $this->settings['showfollow'] !== 'false';


		$this->settings['sb_instagram_cache_time'] = isset( $this->db['sb_instagram_cache_time'] ) ? $this->db['sb_instagram_cache_time'] : 1;
		$this->settings['sb_instagram_cache_time_unit'] = isset( $this->db['sb_instagram_cache_time_unit'] ) ? $this->db['sb_instagram_cache_time_unit'] : 'hours';
		$this->settings['gdpr'] = isset( $this->db['gdpr'] ) ? $this->db['gdpr'] : 'auto';

		if ( ! empty( $this->atts['cachetime'] ) ) {
			$this->settings['caching_type'] = 'page';
			$cache_time = max( 1, (int) $this->atts['cachetime'] );
			$this->settings['cachetimeseconds'] = 60 * $cache_time;
		} elseif ( ! empty( $this->db['legacy_page_cache'] ) ) {
			$this->settings['caching_type'] = 'page';
			$cache_time = max( 1, (int) $this->db['legacy_page_cache'] );
			$this->settings['cachetimeseconds'] = 60 * $cache_time;
		} else {
			$this->settings['caching_type'] = 'background';
		}

		$this->settings['isgutenberg'] = SB_Instagram_Blocks::is_gb_editor();
		if ( $this->settings['isgutenberg'] ) {
			$this->settings['sb_ajax_initial'] = false;
			$this->settings['disable_js_image_loading'] = true;
		}

		if ( SB_Instagram_GDPR_Integrations::doing_gdpr( $this->settings ) ) {
			SB_Instagram_GDPR_Integrations::init();
		}

		if ( $this->settings['feedid'] === 'false' ) {
			$this->settings['feedid'] = false;
		}
	}

	public static function legacy_to_builder_convert( $instagram_feed_settings ) {

		$moderation_json_array = array(
			'list_type_selected' => 'allow',
			'allow_list' => array(),
			'block_list' => ! empty( $instagram_feed_settings['hidephotos'] ) ? explode( ',', str_replace(',', ' ', $instagram_feed_settings['hidephotos'] ) ) : array()
		);

		if ( ! empty( $instagram_feed_settings['whitelist_ids'] ) ) {
			$moderation_json_array['allow_list'] = $instagram_feed_settings['whitelist_ids'];
		} else {
			$moderation_json_array['list_type_selected'] = 'block';
		}
		$instagram_feed_settings['moderationlist'] = json_encode( $moderation_json_array );
		$instagram_feed_settings['customizer'] = false;
		$instagram_feed_settings['feed'] = 'legacy';
		$unsets = array(
			'feed_is_moderated',
			'whitelist_ids',
			'whitelist_num',
			'minnum',
			'disable_resize',
			'favor_local',
			'backup_cache_enabled',
			'font_method',
			'disable_js_image_loading',
			'sbi_cache_cron_interval',
			'sb_instagram_cache_time',
			'sb_instagram_cache_time_unit',
			'addModerationModeLink',
			'caching_type',
			'sb_ajax_initial',
			'isgutenberg',
			'hidephotos',
		);

		foreach ( $unsets as $unset_key ) {
			if ( isset( $instagram_feed_settings[ $unset_key ] ) ) {
				unset( $instagram_feed_settings[ $unset_key ] );
			}
		}

		return $instagram_feed_settings;
	}

	public static function get_settings_by_legacy_shortcode( $atts, $db ) {

		$settings = shortcode_atts(
			array(
				'id'               => isset( $db['sb_instagram_user_id'] ) ? $db['sb_instagram_user_id'] : '',
				'width'            => isset( $db['sb_instagram_width'] ) ? $db['sb_instagram_width'] : '',
				'widthunit'        => isset( $db['sb_instagram_width_unit'] ) ? $db['sb_instagram_width_unit'] : '',
				'widthresp'        => isset( $db['sb_instagram_feed_width_resp'] ) ? $db['sb_instagram_feed_width_resp'] : '',
				'height'           => isset( $db['sb_instagram_height'] ) ? $db['sb_instagram_height'] : '',
				'heightunit'       => isset( $db['sb_instagram_height_unit'] ) ? $db['sb_instagram_height_unit'] : '',
				'sortby'           => isset( $db['sb_instagram_sort'] ) ? $db['sb_instagram_sort'] : '',
				'num'              => isset( $db['sb_instagram_num'] ) ? $db['sb_instagram_num'] : '',
				'apinum'           => isset( $db['sb_instagram_minnum'] ) ? $db['sb_instagram_minnum'] : '',
				'nummobile'        => isset($db[ 'sb_instagram_nummobile' ]) ? $db[ 'sb_instagram_nummobile' ] : '',
				'cols'             => isset( $db['sb_instagram_cols'] ) ? $db['sb_instagram_cols'] : '',
				'disablemobile'    => isset( $db['sb_instagram_disable_mobile'] ) ? $db['sb_instagram_disable_mobile'] : '',
				'imagepadding'     => isset( $db['sb_instagram_image_padding'] ) ? $db['sb_instagram_image_padding'] : '',
				'imagepaddingunit' => isset( $db['sb_instagram_image_padding_unit'] ) ? $db['sb_instagram_image_padding_unit'] : '',
				'background'       => isset( $db['sb_instagram_background'] ) ? $db['sb_instagram_background'] : '',
				'showbutton'       => isset( $db['sb_instagram_show_btn'] ) ? $db['sb_instagram_show_btn'] : '',
				'buttoncolor'      => isset( $db['sb_instagram_btn_background'] ) ? $db['sb_instagram_btn_background'] : '',
				'buttontextcolor'  => isset( $db['sb_instagram_btn_text_color'] ) ? $db['sb_instagram_btn_text_color'] : '',
				'buttontext'       => isset( $db['sb_instagram_btn_text'] ) ? $db['sb_instagram_btn_text'] : '',
				'imageres'         => isset( $db['sb_instagram_image_res'] ) ? $db['sb_instagram_image_res'] : '',
				'showfollow'       => isset( $db['sb_instagram_show_follow_btn'] ) ? $db['sb_instagram_show_follow_btn'] : '',
				'followcolor'      => isset( $db['sb_instagram_folow_btn_background'] ) ? $db['sb_instagram_folow_btn_background'] : '',
				'followtextcolor'  => isset( $db['sb_instagram_follow_btn_text_color'] ) ? $db['sb_instagram_follow_btn_text_color'] : '',
				'followtext'       => isset( $db['sb_instagram_follow_btn_text'] ) ? $db['sb_instagram_follow_btn_text'] : '',
				'showheader'       => isset( $db['sb_instagram_show_header'] ) ? $db['sb_instagram_show_header'] : '',
				'headersize'       => isset( $db['sb_instagram_header_size'] ) ? $db['sb_instagram_header_size'] : '',
				'showbio'          => isset( $db['sb_instagram_show_bio'] ) ? $db['sb_instagram_show_bio'] : '',
				'custombio' => isset($db[ 'sb_instagram_custom_bio' ]) ? $db[ 'sb_instagram_custom_bio' ] : '',
				'customavatar' => isset($db[ 'sb_instagram_custom_avatar' ]) ? $db[ 'sb_instagram_custom_avatar' ] : '',
				'headercolor'      => isset( $db['sb_instagram_header_color'] ) ? $db['sb_instagram_header_color'] : '',
				'class'            => '',
				'ajaxtheme'        => isset( $db['sb_instagram_ajax_theme'] ) ? $db['sb_instagram_ajax_theme'] : '',
				'cachetime'        => isset( $db['sb_instagram_cache_time'] ) ? $db['sb_instagram_cache_time'] : '',
				'media'            => isset( $db['sb_instagram_media_type'] ) ? $db['sb_instagram_media_type'] : '',
				'headeroutside' => isset($db[ 'sb_instagram_outside_scrollable' ]) ? $db[ 'sb_instagram_outside_scrollable' ] : '',
				'accesstoken'      => '',
				'user'             => isset( $db['sb_instagram_user'] ) ? $db['sb_instagram_user'] : false,
				'feedid'           => isset( $db['sb_instagram_feed_id'] ) ? $db['sb_instagram_feed_id'] : false,
				'resizeprocess'    => isset( $db['sb_instagram_resizeprocess'] ) ? $db['sb_instagram_resizeprocess'] : 'background',
				'customtemplates'    => isset( $db['custom_template'] ) ? $db['custom_template'] : '',
				'gdpr'    => isset( $db['gdpr'] ) ? $db['gdpr'] : 'auto',
				'altresize'    => isset( $db['altresize'] ) ? $db['altresize'] : true,
			), $atts );

		$settings['sources'] = is_string( $settings['id'] ) ? explode( ',', str_replace( ' ', '', $settings['id'] ) ) : array();

		return $settings;
	}

	/**
	 * Converts settings from the builder to settings used in the feed
	 *
	 * @param $settings
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function filter_for_builder( $settings, $atts ) {

		if ( ! isset( $atts['media'] ) ) {
			if( isset( $settings['reelsposts'] ) ) {
				$include_reels = $settings['reelsposts'] !== 'false' && ! empty( $settings['reelsposts'] ) ? true : false;
			} else {
				$include_reels = $settings['media'] === 'all' ? true : false;
				$settings['reelsposts'] = $include_reels ? true : false;
			}

			$settings['media'] = $include_reels ? 'all' : array( 'photos', 'videos' );
		} else {
			$include_reels = $settings['media'] === 'all' && strpos( $settings['videotypes'], 'reels' ) !== false;
		}

		if ( ! isset( $atts['videotypes'] ) ) {

			$video_types = array();
			if ( $include_reels ) {
				$video_types[] = 'reels';
				$video_types[] = 'regular';
			} else {
				$video_types[] = 'regular';
			}
			$settings['videotypes'] = implode( ',', $video_types );
		}

		if ( isset( $atts['ajaxtheme'] ) ) {
			$settings['ajaxtheme'] = $atts['ajaxtheme'] === 'true';
		} else {
			$db = sbi_get_database_settings();
			$settings['ajaxtheme'] = isset( $db['sb_instagram_ajax_theme'] ) ? $db['sb_instagram_ajax_theme'] === '1' || $db['sb_instagram_ajax_theme'] === true || $db['sb_instagram_ajax_theme'] === 'on' : false;
		}

		return $settings;
	}

	/**
	 * Disables or enables certain settings when using the
	 * customizer
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function filter_for_customizer( $settings ) {

		$settings['customtemplates'] = false;
		$settings['moderationmode'] = false;
		$settings['sb_ajax_initial'] = false;
		$settings['disable_js_image_loading'] = false;
		$settings['showheader'] = true;

		return $settings;

	}

	/**
	 * Filters out or converts allowed/disallowed shortcode settings
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function filter_atts_for_legacy( $atts ) {
		if ( ! empty( $atts['from_update'] ) ) {
			unset( $atts['from_update'] );
			return $atts;
		}
		$sbi_statuses = get_option( 'sbi_statuses', array() );

		$allowed_legacy_shortcode = array(
			'feed',
			'headersource',
			'customizer',
			'class'
		);

		if ( ! empty( $sbi_statuses['support_legacy_shortcode'] )
		     && empty( $atts['feed'] ) ) {

			if ( is_array( $sbi_statuses['support_legacy_shortcode'] ) ) {
				$atts_diff = array_diff( $sbi_statuses['support_legacy_shortcode'], $atts ); // determines if the shortcode settings match the shortcode settings of an existing feed

				foreach ( $atts_diff as $key => $value ) {
					if ( in_array( $key, $allowed_legacy_shortcode, true ) ) {
						unset( $atts_diff[ $key ] );
					}
				}
				if ( empty( $atts_diff ) ) {
					$atts['feed'] = 1;
				}
			}

			if ( empty( $atts['feed'] ) ) {
				return $atts;
			}
		}


		foreach ( $atts as $key => $value ) {
			if ( ! in_array( $key, $allowed_legacy_shortcode ) ) {
				unset( $atts[ $key ] );
			}
		}

		return $atts;

	}

	/**
	 * Converts legacy feed settings to work with new settings
	 *
	 * @param $settings
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function filter_for_legacy( $settings, $atts  ) {
		return $settings;
	}

	public static function get_settings_by_feed_id( $feed_id, $preview_settings = false ) {
		if ( is_array( $preview_settings ) ) {
			return $preview_settings;
		}

		if ( intval( $feed_id ) < 1 ) {
			return false;
		}

		$feed_saver = new \InstagramFeed\Builder\SBI_Feed_Saver( $feed_id );

		return $feed_saver->get_feed_settings();
	}

	public static function get_legacy_feed_settings() {
		return json_decode( get_option( 'sbi_legacy_feed_settings', '{}' ), true );
	}

	public function feed_type_and_terms_display() {

		if ( ! isset( $this->feed_type_and_terms ) ) {
			return array();
		}
		$return = array();
		foreach ( $this->feed_type_and_terms as $feed_type => $type_terms ) {
			foreach ( $type_terms as $term ) {
				if ( $feed_type === 'users'
				     || $feed_type === 'tagged' ) {
					if ( ! in_array( $this->connected_accounts_in_feed[ $term['term'] ]['username'], $return, true ) ) {
						$return[] = $this->connected_accounts_in_feed[ $term['term'] ]['username'];
					}
				} elseif ( $feed_type === 'hashtags_recent'
				           || $feed_type === 'hashtags_top' ) {
					if ( ! in_array( $term['hashtag_name'], $return, true ) ) {
						$return[] = $term['hashtag_name'];
					}
				}
			}
		}
		return $return;

	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * The plugin will output settings on the frontend for debugging purposes.
	 * Safe settings to display are added here.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public static function get_public_db_settings_keys() {
		$public = array(
			'sb_instagram_user_id',
			'sb_instagram_cache_time',
			'sb_instagram_cache_time_unit',
			'sbi_caching_type',
			'sbi_cache_cron_interval',
			'sbi_cache_cron_time',
			'sbi_cache_cron_am_pm',
			'sb_instagram_width',
			'sb_instagram_width_unit',
			'sb_instagram_feed_width_resp',
			'sb_instagram_height',
			'sb_instagram_num',
			'sb_instagram_height_unit',
			'sb_instagram_cols',
			'sb_instagram_disable_mobile',
			'sb_instagram_image_padding',
			'sb_instagram_image_padding_unit',
			'sb_instagram_sort',
			'sb_instagram_background',
			'sb_instagram_show_btn',
			'sb_instagram_btn_background',
			'sb_instagram_btn_text_color',
			'sb_instagram_btn_text',
			'sb_instagram_image_res',
			//Header
			'sb_instagram_show_header',
			'sb_instagram_header_size',
			'sb_instagram_header_color',
			//Follow button
			'sb_instagram_show_follow_btn',
			'sb_instagram_folow_btn_background',
			'sb_instagram_follow_btn_text_color',
			'sb_instagram_follow_btn_text',
			//Misc
			'sb_instagram_cron',
			'sb_instagram_backup',
			'sb_instagram_ajax_theme',
			'sb_instagram_disable_resize',
			'disable_js_image_loading',
			'enqueue_js_in_head',
			'sb_instagram_disable_awesome',
			'sb_ajax_initial',
			'use_custom'
		);

		return $public;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_connected_accounts() {
		return $this->connected_accounts;
	}

	/**
	 * @return array|bool
	 *
	 * @since 2.0/5.0
	 */
	public function get_connected_accounts_in_feed() {
		if ( isset( $this->connected_accounts_in_feed ) ) {
			return $this->connected_accounts_in_feed;
		} else {
			return false;
		}
	}

	/**
	 * @return bool|string
	 *
	 * @since 2.0/5.0
	 */
	public function get_transient_name() {
		if ( isset( $this->transient_name ) ) {
			return $this->transient_name;
		} else {
			return false;
		}
	}

	/**
	 * Uses the feed types and terms as well as as some
	 * settings to create a semi-unique feed id used for
	 * caching and other features.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @param string $transient_name
	 *
	 * @since 2.0/5.0
	 */
	public function set_transient_name( $transient_name = '' ) {

		if ( ! empty( $transient_name ) ) {
			$this->transient_name = $transient_name;
		} elseif ( ! empty( $this->settings['feed'] ) && $this->settings['feed'] !== 'legacy' && intval( $this->settings['feed'] ) > 0 ) {
			$this->transient_name = '*' . $this->settings['feed'];
		} elseif ( ! empty( $this->settings['feedid'] ) ) {
			$this->transient_name = 'sbi_' . $this->settings['feedid'];
		} else {
			$feed_type_and_terms = $this->feed_type_and_terms;

			$sbi_transient_name = 'sbi_';

			if ( isset( $feed_type_and_terms['users'] ) ) {
				foreach ( $feed_type_and_terms['users'] as $term_and_params ) {
					$user = $term_and_params['term'];
					$connected_account = isset( $this->connected_accounts_in_feed[ $user ] ) ? $this->connected_accounts_in_feed[ $user ] : array();
					if ( isset( $connected_account['type'] ) && $connected_account['type'] === 'business' ) {
						$sbi_transient_name .= $connected_account['username'];
					} else {
						$sbi_transient_name .= $user;
					}
				}
			}

			$num = $this->settings['num'];

			$num_length = strlen( $num ) + 1;


			//Add both parts of the caching string together and make sure it doesn't exceed 45
			$sbi_transient_name = substr( $sbi_transient_name, 0, 45 - $num_length );

			if ( $this->settings['sortby'] === 'random' ) {
				$sbi_transient_name .= 'rdm';
			}

			$sbi_transient_name .= '#' . $num;

			$this->transient_name = $sbi_transient_name;
		}

	}

	/**
	 * @return array|bool
	 *
	 * @since 2.0/5.0
	 */
	public function get_feed_type_and_terms() {
		if ( isset( $this->feed_type_and_terms ) ) {
			return $this->feed_type_and_terms;
		} else {
			return false;
		}
	}

	private function add_connected_accounts_in_feed( $connected_accounts ) {
		foreach ( $connected_accounts as $key => $connected_account ) {
			$this->connected_accounts_in_feed[ $key ] = $connected_account;
		}
	}

	private function add_feed_type_and_terms( $feed_type_and_terms ) {
		$this->feed_type_and_terms = array_merge( $this->feed_type_and_terms, $feed_type_and_terms );
	}

	private function set_user_feed( $users = false ) {
		global $sb_instagram_posts_manager;

		if ( ! $users ) {
			$set = false;
			if ( empty( $this->connected_accounts ) && empty( $this->settings['feed']) ) {

				if ( empty( $this->atts['feed'] ) ) {
					$error_message_return = array(
						'error_message' => __( 'Error: No Feed ID Set.', 'instagram-feed' ),
						'admin_only' => __( 'Visit the Instagram Feed settings page to see which feeds have been created and how to embed them.', 'instagram-feed' ),
						'frontend_directions' => '',
						'backend_directions' => ''
					);
				} else {
					$error_message_return = array(
						'error_message' => __( 'Error: Invalid Feed ID.', 'instagram-feed' ),
						'admin_only' => __( 'Visit the Instagram Feed settings page to see which feeds have been created and how to embed them.', 'instagram-feed' ),
						'frontend_directions' => '',
						'backend_directions' => ''
					);
				}

				$sb_instagram_posts_manager->maybe_set_display_error( 'configuration', $error_message_return );
				return;
			}
			foreach ( $this->connected_accounts as $connected_account ) {
				if ( ! $set ) {
					$set = true;
					$this->settings['user'] = $connected_account['username'];
					$this->connected_accounts_in_feed = array( $connected_account['user_id'] => $connected_account );
					$feed_type_and_terms = array(
						'users'=> array(
							array(
								'term' => $connected_account['user_id'],
								'params' => array()
							)
						)
					);
					if ( $sb_instagram_posts_manager->are_current_api_request_delays( $connected_account ) ) {
						$feed_type_and_terms['users'][0]['error'] = true;
					}
					$this->feed_type_and_terms = $feed_type_and_terms;
				}
			}
			return;
		} else {
			$connected_accounts_in_feed = array();
			$feed_type_and_terms = array(
				'users' => array()
			);
			$usernames_included = array();
			$usernames_not_connected = array();

			foreach ( $users as $user_id_or_name ) {
				$connected_account = ! empty( $this->connected_accounts[ $user_id_or_name ] ) ? $this->connected_accounts[ $user_id_or_name ] : SB_Instagram_Connected_Account::lookup( $user_id_or_name );

				if ( $connected_account ) {
					if ( ! in_array( $connected_account['username'], $usernames_included, true ) ) {
						if ( ! $sb_instagram_posts_manager->are_current_api_request_delays( $connected_account ) ) {
							$feed_type_and_terms['users'][] = array(
								'term'   => $connected_account['user_id'],
								'params' => array()
							);
						} else {
							$feed_type_and_terms['users'][] = array(
								'term'   => $connected_account['user_id'],
								'params' => array(),
								'error' => true
							);
						}
						$connected_accounts_in_feed[ $connected_account['user_id'] ] = $connected_account;
						$usernames_included[] = $connected_account['username'];
					}
				} else {
					$feed_type_and_terms['users'][] = array(
						'term'   => $user_id_or_name,
						'params' => array(),
						'error' => true
					);
					$usernames_not_connected[] = $user_id_or_name;
				}

			}

			if ( ! empty( $usernames_not_connected ) ) {
				global $sb_instagram_posts_manager;
				if ( count( $usernames_not_connected ) === 1 ) {
					$user = $usernames_not_connected[0];
				} else {
					$user = implode( ', ', $usernames_not_connected );
				}

				$settings_link = '<a href="'.admin_url( 'admin.php?page=sbi-settings' ).'" target="_blank">' . __( 'plugin Settings page', 'instagram-feed' ) . '</a>';

				$error_message_return = array(
					'error_message' => sprintf( __( 'Error: There is no connected account for the user %s.', 'instagram-feed' ), $user ),
					'admin_only' => sprintf( __( 'A connected account related to the user is required to display user feeds. Please connect an account for this user on the %s.', 'instagram-feed' ), $settings_link ),
					'frontend_directions' => '',
					'backend_directions' => ''
				);
				$sb_instagram_posts_manager->maybe_set_display_error( 'configuration', $error_message_return );
			}

			$this->add_feed_type_and_terms( $feed_type_and_terms );

			$this->add_connected_accounts_in_feed( $connected_accounts_in_feed );
		}

	}

	/**
	 * Based on the settings related to retrieving post data from the API,
	 * this setting is used to make sure all endpoints needed for the feed are
	 * connected and stored for easily looping through when adding posts
	 *
	 * Overwritten in the Pro version.
	 *
	 * @since 2.0/5.0
	 */
	public function set_feed_type_and_terms() {
		global $sb_instagram_posts_manager;

		$is_using_access_token_in_shortcode = ! empty( $this->atts['accesstoken'] );
		$settings_link = '<a href="'.admin_url( 'admin.php?page=sbi-settings' ).'" target="_blank">' . __( 'plugin Settings page', 'instagram-feed' ) . '</a>';
		if ( $is_using_access_token_in_shortcode ) {
			$error_message_return = array(
				'error_message' => __( 'Error: Cannot add access token directly to the shortcode.', 'instagram-feed' ),
				'admin_only' => sprintf( __( 'Due to recent Instagram platform changes, it\'s no longer possible to create a feed by adding the access token to the shortcode. Remove the access token from the shortcode and connect an account on the %s instead.', 'instagram-feed' ), $settings_link ),
				'frontend_directions' => '',
				'backend_directions' => ''
			);

			$sb_instagram_posts_manager->maybe_set_display_error( 'configuration', $error_message_return );

			$this->atts['accesstoken'] = '';
		}

		if ( empty( $this->settings['id'] )
		     && empty( $this->settings['user'] )
		     && ! empty ( $this->connected_accounts ) ) {

			$this->set_user_feed();
		} else {
			$user_array = array();
			if ( ! empty( $this->settings['user'] ) ) {
				$user_array = is_array( $this->settings['user'] ) ? $this->settings['user'] : explode( ',', str_replace( ' ', '',  $this->settings['user'] ) );
			} elseif ( ! empty( $this->settings['id'] ) ) {
				$user_array = is_array( $this->settings['id'] ) ? $this->settings['id'] : explode( ',', str_replace( ' ', '',  $this->settings['id'] ) );
			}

			$this->set_user_feed( $user_array );
		}

		if ( empty( $this->feed_type_and_terms['users'] ) ) {

			$error_message_return = array(
				'error_message' => __( 'Error: No users set.', 'instagram-feed' ),
				'admin_only' => __( 'Please visit the plugin\'s settings page to select a user account or add one to the shortcode - user="username".', 'instagram-feed' ),
				'frontend_directions' => '',
				'backend_directions' => ''
			);
			$sb_instagram_posts_manager->maybe_set_display_error( 'configuration', $error_message_return );
		}

		foreach ( $this->connected_accounts_in_feed as $connected_account_in_feed ) {
			if ( isset( $connected_account_in_feed['private'] )
			     && sbi_private_account_near_expiration( $connected_account_in_feed ) ) {
				$link_1 = '<a href="https://help.instagram.com/116024195217477/In">';
				$link_2 = '</a>';
				$error_message_return = array(
					'error_message' => __( 'Error: Private Instagram Account.', 'instagram-feed' ),
					'admin_only' => sprintf( __( 'It looks like your Instagram account is private. Instagram requires private accounts to be reauthenticated every 60 days. Refresh your account to allow it to continue updating, or %smake your Instagram account public%s.', 'instagram-feed' ), $link_1, $link_2 ),
					'frontend_directions' => '<a href="https://smashballoon.com/instagram-feed/docs/errors/#10">' . __( 'Click here to troubleshoot', 'instagram-feed' ) . '</a>',
					'backend_directions' => ''
				);

				$sb_instagram_posts_manager->maybe_set_display_error( 'configuration', $error_message_return );
			}
		}
	}

	/**
	 * @return float|int
	 *
	 * @since 2.0/5.0
	 */
	public function get_cache_time_in_seconds() {
		if ( $this->settings['caching_type'] === 'background' ) {
			return SBI_CRON_UPDATE_CACHE_TIME;
		} else {
			//If the caching time doesn't exist in the database then set it to be 1 hour
			$cache_time = isset( $this->settings['sb_instagram_cache_time'] ) ? (int)$this->settings['sb_instagram_cache_time'] : 1;
			$cache_time_unit = isset( $this->settings['sb_instagram_cache_time_unit'] ) ? $this->settings['sb_instagram_cache_time_unit'] : 'hours';

			//Calculate the cache time in seconds
			if ( $cache_time_unit == 'minutes' ) $cache_time_unit = 60;
			if ( $cache_time_unit == 'hours' ) $cache_time_unit = 60*60;
			if ( $cache_time_unit == 'days' ) $cache_time_unit = 60*60*24;

			$cache_time_return = min( $cache_time * $cache_time_unit, DAY_IN_SECONDS );

			return $cache_time_return;
		}
	}

	public static function default_settings() {
		$defaults = array(
			'sb_instagram_at'                   => '',
			'sb_instagram_type'                 => 'user',
			'sb_instagram_order'                => 'top',
			'sb_instagram_user_id'              => '',
			'sb_instagram_tagged_ids' => '',
			'sb_instagram_hashtag'              => '',
			'sb_instagram_type_self_likes'      => '',
			'sb_instagram_location'             => '',
			'sb_instagram_coordinates'          => '',
			'sb_instagram_preserve_settings'    => '',
			'sb_instagram_ajax_theme'           => false,
			'enqueue_js_in_head'                => false,
			'disable_js_image_loading'          => false,
			'sb_instagram_disable_resize'       => false,
			'sb_instagram_favor_local'          => true,
			'sb_instagram_cache_time'           => '1',
			'sb_instagram_cache_time_unit'      => 'hours',
			'sbi_caching_type'                  => 'background',
			'sbi_cache_cron_interval'           => '12hours',
			'sbi_cache_cron_time'               => '1',
			'sbi_cache_cron_am_pm'              => 'am',

			'sb_instagram_width'                => '100',
			'sb_instagram_width_unit'           => '%',
			'sb_instagram_feed_width_resp'      => false,
			'sb_instagram_height'               => '',
			'sb_instagram_num'                  => '20',
			'sb_instagram_nummobile'            => '',
			'sb_instagram_height_unit'          => '',
			'sb_instagram_cols'                 => '4',
			'sb_instagram_colsmobile'           => 'auto',
			'sb_instagram_image_padding'        => '5',
			'sb_instagram_image_padding_unit'   => 'px',

			//Layout Type
			'sb_instagram_layout_type'          => 'grid',
			'sb_instagram_highlight_type'       => 'pattern',
			'sb_instagram_highlight_offset'     => 0,
			'sb_instagram_highlight_factor'     => 6,
			'sb_instagram_highlight_ids'        => '',
			'sb_instagram_highlight_hashtag'    => '',

			//Hover style
			'sb_hover_background'               => '',
			'sb_hover_text'                     => '',
			'sbi_hover_inc_username'            => true,
			'sbi_hover_inc_icon'                => true,
			'sbi_hover_inc_date'                => true,
			'sbi_hover_inc_instagram'           => true,
			'sbi_hover_inc_location'            => false,
			'sbi_hover_inc_caption'             => false,
			'sbi_hover_inc_likes'               => false,
			// 'sb_instagram_hover_text_size'      => '',

			'sb_instagram_sort'                 => 'none',
			'sb_instagram_disable_lightbox'     => false,
			'sb_instagram_captionlinks'         => false,
			'sb_instagram_background'           => '',
			'sb_instagram_show_btn'             => true,
			'sb_instagram_btn_background'       => '',
			'sb_instagram_btn_text_color'       => '',
			'sb_instagram_btn_text'             => __( 'Load More', 'instagram-feed' ),
			'sb_instagram_image_res'            => 'auto',
			'sb_instagram_media_type'           => 'all',
			'sb_instagram_moderation_mode'      => 'manual',
			'sb_instagram_hide_photos'          => '',
			'sb_instagram_block_users'          => '',
			'sb_instagram_ex_apply_to'          => 'all',
			'sb_instagram_inc_apply_to'         => 'all',
			'sb_instagram_show_users'           => '',
			'sb_instagram_exclude_words'        => '',
			'sb_instagram_include_words'        => '',

			//Text
			'sb_instagram_show_caption'         => true,
			'sb_instagram_caption_length'       => '50',
			'sb_instagram_caption_color'        => '',
			'sb_instagram_caption_size'         => '13',

			//lightbox comments
			'sb_instagram_lightbox_comments'    => true,
			'sb_instagram_num_comments'         => '20',

			//Meta
			'sb_instagram_show_meta'            => true,
			'sb_instagram_meta_color'           => '',
			'sb_instagram_meta_size'            => '13',
			//Header
			'sb_instagram_show_header'          => true,
			'sb_instagram_header_color'         => '',
			'sb_instagram_header_style'         => 'standard',
			'sb_instagram_show_followers'       => true,
			'sb_instagram_show_bio'             => true,
			'sb_instagram_custom_bio' => '',
			'sb_instagram_custom_avatar' => '',
			'sb_instagram_header_primary_color'  => '517fa4',
			'sb_instagram_header_secondary_color'  => 'eeeeee',
			'sb_instagram_header_size'  => 'small',
			'sb_instagram_outside_scrollable' => false,
			'sb_instagram_stories' => true,
			'sb_instagram_stories_time' => 5000,

			//Follow button
			'sb_instagram_show_follow_btn'      => true,
			'sb_instagram_folow_btn_background' => '',
			'sb_instagram_follow_btn_text_color' => '',
			'sb_instagram_follow_btn_text'      => __( 'Follow on Instagram', 'instagram-feed' ),

			//Autoscroll
			'sb_instagram_autoscroll' => false,
			'sb_instagram_autoscrolldistance' => 200,

			//Misc
			'sb_instagram_custom_css'           => '',
			'sb_instagram_custom_js'            => '',
			'sb_instagram_requests_max'         => '5',
			'sb_instagram_minnum' => '0',
			'sb_instagram_cron'                 => 'unset',
			'sb_instagram_disable_font'         => false,
			'sb_instagram_backup' => true,
			'sb_ajax_initial' => false,
			'enqueue_css_in_shortcode' => false,
			'sb_instagram_disable_mob_swipe' => false,
			'sbi_br_adjust' => true,
			'sb_instagram_media_vine' => false,
			'custom_template' => false,
			'disable_admin_notice' => false,
			'enable_email_report' => 'on',
			'email_notification' => 'monday',
			'email_notification_addresses' => get_option( 'admin_email' ),

			//Carousel
			'sb_instagram_carousel'             => false,
			'sb_instagram_carousel_rows'        => 1,
			'sb_instagram_carousel_loop'        => 'rewind',
			'sb_instagram_carousel_arrows'      => false,
			'sb_instagram_carousel_pag'         => true,
			'sb_instagram_carousel_autoplay'    => false,
			'sb_instagram_carousel_interval'    => '5000'

		);

		return $defaults;
	}

	public function get_connected_accounts_from_settings() {
		if ( $this->settings['feed'] === 'legacy' ) {
			$sources = \InstagramFeed\Builder\SBI_Db::source_query();

			return \InstagramFeed\Builder\SBI_Source::convert_sources_to_connected_accounts( $sources );
		}
		$include_all_businesses = false;

		$account_ids = [];

		if ( $this->settings['type'] === 'hashtag' ) {
			$include_all_businesses = true;
		}

		if ( $this->settings['type'] === 'mixed' ) {
			$include_all_businesses = ! empty( $this->settings['hashtag'] );
		}

		$ids = is_array( $this->settings['id'] ) ? $this->settings['id'] : explode( ',', str_replace(' ', '', $this->settings['id'] ) );
		$tagged = ! empty( $this->settings['tagged'] ) && is_array( $this->settings['tagged'] ) ? $this->settings['tagged'] : array();

		$account_ids = array_merge( $ids, $tagged );

		$args = array(
			'all_businesses' => $include_all_businesses,
			'id' => $account_ids
		);
		$sources = \InstagramFeed\Builder\SBI_Db::source_query( $args );

		if ( empty( $sources ) ) {
			$sources = \InstagramFeed\Builder\SBI_Db::source_query();
		}

		return \InstagramFeed\Builder\SBI_Source::convert_sources_to_connected_accounts( $sources );
	}

	/**
	 * Compares given array with an allow list of
	 * setting keys and how they should be sanitized
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public static function sanitize_raw_atts( $atts ) {

		$sanitized_atts = array();

		$allowed_atts = array(
			'id' => array(
				'method' => 'alpha_numeric_and_comma',
				'allowed_vals' => 'any'
			),
			'width' => array(
				'method' => 'page_load_only',
			),
			'widthunit'=> array(
				'method' => 'page_load_only',
			),
			'widthresp'  => array(
				'method' => 'page_load_only',
			),
			'height' => array(
				'method' => 'page_load_only',
			),
			'heightunit' => array(
				'method' => 'page_load_only',
			),
			'sortby' => array(
				'method' => 'enum',
				'allowed_vals' => array( 'none', 'random', 'likes' )
			),
			'num' => array(
				'method' => 'intval',
				'allowed_vals' => 500
			),
			'nummobile' => array(
				'method' => 'intval',
				'allowed_vals' => 500
			),
			'apinum' => array(
				'method' => 'intval',
				'allowed_vals' => 100
			),
			'cols' => array(
				'method' => 'intval',
				'allowed_vals' => 15
			),
			'disablemobile' => array(
				'method' => 'page_load_only',
			),
			'imagepadding' => array(
				'method' => 'page_load_only',
			),
			'imagepaddingunit' => array(
				'method' => 'page_load_only',
			),
			'background' => array(
				'method' => 'page_load_only',
			),
			'showbutton' => array(
				'method' => 'page_load_only',
			),
			'buttoncolor' => array(
				'method' => 'page_load_only',
			),
			'buttontextcolor' => array(
				'method' => 'page_load_only',
			),
			'buttontext' => array(
				'method' => 'page_load_only',
			),
			'imageres' => array(
				'method' => 'enum',
				'allowed_vals' => array( 'auto', 'thumb', 'low', 'full' )
			),
			'showfollow' => array(
				'method' => 'page_load_only',
			),
			'followcolor' => array(
				'method' => 'page_load_only',
			),
			'followtextcolor' => array(
				'method' => 'page_load_only',
			),
			'followtext' => array(
				'method' => 'page_load_only',
			),
			'showheader' => array(
				'method' => 'page_load_only',
			),
			'headersize' => array(
				'method' => 'page_load_only',
			),
			'showbio' => array(
				'method' => 'page_load_only',
			),
			'custombio' => array(
				'method' => 'page_load_only',
			),
			'customavatar' => array(
				'method' => 'page_load_only',
			),
			'headercolor' => array(
				'method' => 'page_load_only',
			),
			'class' => array(
				'method' => 'page_load_only',
			),
			'ajaxtheme' => array(
				'method' => 'page_load_only',
			),
			'cachetime' => array(
				'method' => 'page_load_only',
			),
			'media' => array(
				'method' => 'page_load_only',
			),
			'headeroutside' => array(
				'method' => 'page_load_only',
			),
			'user' => array(
				'method' => 'alpha_numeric_and_comma',
				'allowed_vals' => 'any'
			),
			'feedid' => array(
				'method' => 'feedid_chars',
				'allowed_vals' => 'any'
			),
			'feed' => array(
				'method' => 'alpha_numeric_and_comma',
				'allowed_vals' => 'any'
			),
			'resizeprocess' => array(
				'method' => 'enum',
				'allowed_vals' => array( 'page', 'background' )
			),
			'customtemplates' => array(
				'method' => 'string_true',
				'allowed_vals' => 'any'
			),
			'gdpr' => array(
				'method' => 'enum',
				'allowed_vals' => array( 'auto', 'yes', 'no' )
			)
		);

		foreach ( $atts as $key => $value ) {
			$value = (string)$value;

			if ( isset( $allowed_atts[ $key ] ) && strlen( $value ) < 500 ) {
				$sanitization_method = $allowed_atts[ $key ]['method'];

				switch ( $sanitization_method ) {
					case 'enum' :
						if ( in_array( $value, $allowed_atts[ $key ]['allowed_vals'] ) ) {
							$sanitized_atts[ $key ] = sanitize_text_field( $value );
						}
						break;
					case 'alpha_numeric_and_comma' :
						$sanitized_atts[ $key ] = preg_replace( "/[^A-Za-z0-9,]/", '', $value );
						break;
					case 'feedid_chars' :
						$sanitized_atts[ $key ] = preg_replace( "/[^A-Za-z0-9#_%-\/?]/", '', urlencode( $value ) );
						break;
					case 'intval' :
						$value = intval( $value );

						if ( $value < (int)$allowed_atts[ $key ]['allowed_vals'] ) {
							$sanitized_atts[ $key ] = $value;
						}

						break;
					case 'floatval' :
						$value = floatval( $value );

						if ( $allowed_atts[ $key ]['allowed_vals'] === 'any' ) {
							$sanitized_atts[ $key ] = $value;
						} elseif ( $value < (float)$allowed_atts[ $key ]['allowed_vals'] ) {
							$sanitized_atts[ $key ] = $value;
						}

						if ( floor( $value ) === $value ) {
							$sanitized_atts[ $key ] = (int)$value;
						}

						break;
					case 'string_true' :
						$value = floatval( $value );

						if ( $value === 'true' || $value === 'on' || $value === true ) {
							$sanitized_atts[ $key ] = 'true';
						} else {
							$sanitized_atts[ $key ] = 'false';
						}

						break;
				}
			}
		}

		return $sanitized_atts;
	}

	public static function legacy_shortcode_atts( $atts, $db ) {
		$settings = shortcode_atts(
			array(
				'id'               => isset( $db['sb_instagram_user_id'] ) ? $db['sb_instagram_user_id'] : '',
				'width'            => isset( $db['sb_instagram_width'] ) ? $db['sb_instagram_width'] : '',
				'widthunit'        => isset( $db['sb_instagram_width_unit'] ) ? $db['sb_instagram_width_unit'] : '',
				'widthresp'        => isset( $db['sb_instagram_feed_width_resp'] ) ? $db['sb_instagram_feed_width_resp'] : '',
				'height'           => isset( $db['sb_instagram_height'] ) ? $db['sb_instagram_height'] : '',
				'heightunit'       => isset( $db['sb_instagram_height_unit'] ) ? $db['sb_instagram_height_unit'] : '',
				'sortby'           => isset( $db['sb_instagram_sort'] ) ? $db['sb_instagram_sort'] : '',
				'num'              => isset( $db['sb_instagram_num'] ) ? $db['sb_instagram_num'] : '',
				'apinum'           => isset( $db['sb_instagram_minnum'] ) ? $db['sb_instagram_minnum'] : '',
				'nummobile'        => isset( $db['sb_instagram_nummobile'] ) ? $db['sb_instagram_nummobile'] : '',
				'cols'             => isset( $db['sb_instagram_cols'] ) ? $db['sb_instagram_cols'] : '',
				'disablemobile'    => isset( $db['sb_instagram_disable_mobile'] ) ? $db['sb_instagram_disable_mobile'] : '',
				'imagepadding'     => isset( $db['sb_instagram_image_padding'] ) ? $db['sb_instagram_image_padding'] : '',
				'imagepaddingunit' => isset( $db['sb_instagram_image_padding_unit'] ) ? $db['sb_instagram_image_padding_unit'] : '',
				'background'       => isset( $db['sb_instagram_background'] ) ? $db['sb_instagram_background'] : '',
				'showbutton'       => isset( $db['sb_instagram_show_btn'] ) ? $db['sb_instagram_show_btn'] : '',
				'buttoncolor'      => isset( $db['sb_instagram_btn_background'] ) ? $db['sb_instagram_btn_background'] : '',
				'buttontextcolor'  => isset( $db['sb_instagram_btn_text_color'] ) ? $db['sb_instagram_btn_text_color'] : '',
				'buttontext'       => isset( $db['sb_instagram_btn_text'] ) ? $db['sb_instagram_btn_text'] : '',
				'imageres'         => isset( $db['sb_instagram_image_res'] ) ? $db['sb_instagram_image_res'] : '',
				'showfollow'       => isset( $db['sb_instagram_show_follow_btn'] ) ? $db['sb_instagram_show_follow_btn'] : '',
				'followcolor'      => isset( $db['sb_instagram_folow_btn_background'] ) ? $db['sb_instagram_folow_btn_background'] : '',
				'followtextcolor'  => isset( $db['sb_instagram_follow_btn_text_color'] ) ? $db['sb_instagram_follow_btn_text_color'] : '',
				'followtext'       => isset( $db['sb_instagram_follow_btn_text'] ) ? $db['sb_instagram_follow_btn_text'] : '',
				'showheader'       => isset( $db['sb_instagram_show_header'] ) ? $db['sb_instagram_show_header'] : '',
				'headersize'       => isset( $db['sb_instagram_header_size'] ) ? $db['sb_instagram_header_size'] : '',
				'showbio'          => isset( $db['sb_instagram_show_bio'] ) ? $db['sb_instagram_show_bio'] : '',
				'custombio'        => isset( $db['sb_instagram_custom_bio'] ) ? $db['sb_instagram_custom_bio'] : '',
				'customavatar'     => isset( $db['sb_instagram_custom_avatar'] ) ? $db['sb_instagram_custom_avatar'] : '',
				'headercolor'      => isset( $db['sb_instagram_header_color'] ) ? $db['sb_instagram_header_color'] : '',
				'class'            => '',
				'ajaxtheme'        => isset( $db['sb_instagram_ajax_theme'] ) ? $db['sb_instagram_ajax_theme'] : '',
				'cachetime'        => isset( $db['sb_instagram_cache_time'] ) ? $db['sb_instagram_cache_time'] : '',
				'media'            => isset( $db['sb_instagram_media_type'] ) ? $db['sb_instagram_media_type'] : '',
				'headeroutside'    => isset( $db['sb_instagram_outside_scrollable'] ) ? $db['sb_instagram_outside_scrollable'] : '',
				'accesstoken'      => '',
				'user'             => isset( $db['sb_instagram_user'] ) ? $db['sb_instagram_user'] : false,
				'feedid'           => isset( $db['sb_instagram_feed_id'] ) ? $db['sb_instagram_feed_id'] : false,
				'resizeprocess'    => isset( $db['sb_instagram_resizeprocess'] ) ? $db['sb_instagram_resizeprocess'] : 'background',
				'customtemplates'  => isset( $db['custom_template'] ) ? $db['custom_template'] : '',
				'gdpr'             => isset( $db['gdpr'] ) ? $db['gdpr'] : 'auto',
				'altresize'        => isset( $db['altresize'] ) ? $db['altresize'] : true,
			),
			$atts
		);

		$settings['customtemplates'] = $settings['customtemplates'] === 'true' || $settings['customtemplates'] === 'on' || $settings['customtemplates'] === true;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['sbi_debug'] ) ) {
			$settings['customtemplates'] = false;
		}
		$settings['minnum']  = max( (int) $settings['num'], (int) $settings['nummobile'] );
		$settings['showbio'] = $settings['showbio'] === 'true' || $settings['showbio'] === 'on' || $settings['showbio'] === true;
		if ( isset( $atts['showbio'] ) && $atts['showbio'] === 'false' ) {
			$settings['showbio'] = false;
		}
		if ( isset( $atts['showheader'] ) && $atts['showheader'] === 'false' ) {
			$settings['showheader'] = false;
		} elseif ( isset( $atts['showheader'] ) && $atts['showheader'] === 'true' ) {
			$settings['showheader'] = true;
		}
		$settings['disable_resize']           = isset( $db['sb_instagram_disable_resize'] ) && ( $db['sb_instagram_disable_resize'] === 'on' || $db['sb_instagram_disable_resize'] === true );
		$settings['favor_local']              = true;
		$settings['backup_cache_enabled']     = ! isset( $db['sb_instagram_backup'] ) || ( $db['sb_instagram_backup'] === 'on' ) || $db['sb_instagram_backup'] === true;
		$settings['headeroutside']            = ( $settings['headeroutside'] === true || $settings['headeroutside'] === 'on' || $settings['headeroutside'] === 'true' );
		$settings['disable_js_image_loading'] = isset( $db['disable_js_image_loading'] ) && ( $db['disable_js_image_loading'] === 'on' || $db['disable_js_image_loading'] === true );
		$settings['ajax_post_load']           = isset( $db['sb_ajax_initial'] ) && ( $db['sb_ajax_initial'] === 'on' || $db['sb_ajax_initial'] === true );

		switch ( $db['sbi_cache_cron_interval'] ) {
			case '30mins':
				$settings['sbi_cache_cron_interval'] = 60 * 30;
				break;
			case '1hour':
				$settings['sbi_cache_cron_interval'] = 60 * 60;
				break;
			default:
				$settings['sbi_cache_cron_interval'] = 60 * 60 * 12;
		}

		$settings['sb_instagram_cache_time']      = isset( $db['sb_instagram_cache_time'] ) ? $db['sb_instagram_cache_time'] : 1;
		$settings['sb_instagram_cache_time_unit'] = isset( $db['sb_instagram_cache_time_unit'] ) ? $db['sb_instagram_cache_time_unit'] : 'hours';

		// use our new colsmobile and colstablet instead of disable mobile if from the DB setting
		if ( ! empty( $settings['disablemobile'] ) ) {
			$settings['colstablet']    = $settings['cols'];
			$settings['colsmobile']    = $settings['cols'];
			$settings['disablemobile'] = false;
		}

		return $settings;
	}
}
