<?php
/**
 * Instagram Feed Database
 *
 * @since 6.0
 */

namespace InstagramFeed\Builder;

class SBI_Feed_Saver {

	/**
	 * @var int
	 *
	 * @since 6.0
	 */
	private $insert_id;

	/**
	 * @var array
	 *
	 * @since 6.0
	 */
	private $data;

	/**
	 * @var array
	 *
	 * @since 6.0
	 */
	private $sanitized_and_sorted_data;

	/**
	 * @var array
	 *
	 * @since 6.0
	 */
	private $feed_db_data;


	/**
	 * @var string
	 *
	 * @since 6.0
	 */
	private $feed_name;

	/**
	 * @var bool
	 *
	 * @since 6.0
	 */
	private $is_legacy;

	/**
	 * SBI_Feed_Saver constructor.
	 *
	 * @param int $insert_id
	 *
	 * @since 6.0
	 */
	public function __construct( $insert_id ) {
		if ( $insert_id === 'legacy' ) {
			$this->is_legacy = true;
			$this->insert_id = 0;
		} else {
			$this->is_legacy = false;
			$this->insert_id = $insert_id;
		}
	}

	/**
	 * Feed insert ID if it exists
	 *
	 * @return bool|int
	 *
	 * @since 6.0
	 */
	public function get_feed_id() {
		if ( $this->is_legacy ) {
			return 'legacy';
		}
		if ( ! empty( $this->insert_id ) ) {
			return $this->insert_id;
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 *
	 * @since 6.0
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * @param string $feed_name
	 *
	 * @since 6.0
	 */
	public function set_feed_name( $feed_name ) {
		$this->feed_name = $feed_name;
	}

	/**
	 * @param array $feed_db_data
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_feed_db_data() {
		return $this->feed_db_data;
	}

	/**
	 * Adds a new feed if there is no associated feed
	 * found. Otherwise updates the exiting feed.
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public function update_or_insert() {
		$this->sanitize_and_sort_data();

		if ( $this->exists_in_database() ) {
			return $this->update();
		} else {
			return $this->insert();
		}
	}

	/**
	 * Whether or not a feed exists with the
	 * associated insert ID
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public function exists_in_database() {
		if ( $this->is_legacy ) {
			return true;
		}

		if ( $this->insert_id === false ) {
			return false;
		}

		$args = array(
			'id' => $this->insert_id
		);

		$results = SBI_Db::feeds_query( $args );

		return isset( $results[0] );
	}

	/**
	 * Inserts a new feed from sanitized and sorted data.
	 * Some data is saved in the sbi_feeds table and some is
	 * saved in the sbi_feed_settings table.
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public function insert() {
		if ( $this->is_legacy ) {
			return $this->update();
		}

		if ( ! isset( $this->sanitized_and_sorted_data ) ) {
			return false;
		}

		$settings_array = SBI_Feed_Saver::format_settings( $this->sanitized_and_sorted_data['feed_settings'] );

		$this->sanitized_and_sorted_data['feeds'][] = array(
			'key' => 'settings',
			'values' => array( sbi_json_encode( $settings_array ) )
		);

		if ( ! empty( $this->feed_name ) ) {
			$this->sanitized_and_sorted_data['feeds'][] = array(
				'key' => 'feed_name',
				'values' => array( $this->feed_name )
			);
		}

		$this->sanitized_and_sorted_data['feeds'][] = array(
			'key' => 'status',
			'values' => array( 'publish' )
		);

		$insert_id = SBI_Db::feeds_insert( $this->sanitized_and_sorted_data['feeds'] );

		if ( $insert_id ) {
			$this->insert_id = $insert_id;

			return $insert_id;
		}

		return false;
	}

	/**
	 * Updates an existing feed and related settings from
	 * sanitized and sorted data.
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public function update() {
		if ( ! isset( $this->sanitized_and_sorted_data ) ) {
			return false;
		}

		$args = array(
			'id' => $this->insert_id
		);

		$settings_array = SBI_Feed_Saver::format_settings( $this->sanitized_and_sorted_data['feed_settings'] );

		if ( $this->is_legacy ) {
			$to_save_json = sbi_json_encode( $settings_array );
			return update_option( 'sbi_legacy_feed_settings', $to_save_json, false );
		}

		$this->sanitized_and_sorted_data['feeds'][] = array(
			'key' => 'settings',
			'values' => array( sbi_json_encode( $settings_array ) )
		);

		$this->sanitized_and_sorted_data['feeds'][] = array(
			'key' => 'feed_name',
			'values' => [sanitize_text_field($this->feed_name)]
		);

		$success = SBI_Db::feeds_update( $this->sanitized_and_sorted_data['feeds'], $args );

		return $success;
	}

	/**
	 * Converts settings that have been sanitized into an associative array
	 * that can be saved as JSON in the database
	 *
	 * @param $raw_settings
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function format_settings( $raw_settings ) {
		$settings_array = array();
		foreach ( $raw_settings as $single_setting ) {
			if ( count( $single_setting['values'] ) > 1 ) {
				$settings_array[ $single_setting['key'] ] = $single_setting['values'];

			} else {
				$settings_array[ $single_setting['key'] ] = isset( $single_setting['values'][0] ) ? $single_setting['values'][0] : '';
			}
		}

		return $settings_array;
	}

	/**
	 * Gets the Preview Settings
	 * for the Feed Fly Preview
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public function get_feed_preview_settings( $preview_settings ){

	}

	/**
	 * Retrieves and organizes feed setting data for easy use in
	 * the builder
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public function get_feed_settings() {
		if ( $this->is_legacy ) {
			if ( sbi_is_pro_version() ) {
				$instagram_feed_settings = new \SB_Instagram_Settings_Pro( array(), sbi_get_database_settings() );
			} else {
				$instagram_feed_settings = new \SB_Instagram_Settings( array(), sbi_get_database_settings() );
			}


			$instagram_feed_settings->set_feed_type_and_terms();
			$instagram_feed_settings->set_transient_name();
			$return = $instagram_feed_settings->get_settings();

			$this->feed_db_data = array(
				'id' => 'legacy',
				'feed_name' => __( 'Legacy Feeds', 'instagram-feed' ),
				'feed_title' => __( 'Legacy Feeds', 'instagram-feed' ),
				'status' => 'publish',
				'last_modified' => date( 'Y-m-d H:i:s' ),
			);
		} else if ( empty( $this->insert_id ) ) {
			return false;
		} else {
			$args = array(
				'id' => $this->insert_id,
			);
			$settings_db_data = SBI_Db::feeds_query( $args );
			if ( false === $settings_db_data || sizeof($settings_db_data) == 0) {
				return false;
			}
			$this->feed_db_data = array(
				'id' => $settings_db_data[0]['id'],
				'feed_name' => $settings_db_data[0]['feed_name'],
				'feed_title' => $settings_db_data[0]['feed_title'],
				'status' => $settings_db_data[0]['status'],
				'last_modified' => $settings_db_data[0]['last_modified'],
			);

			$return = json_decode( $settings_db_data[0]['settings'], true );
			$return['feed_name'] = $settings_db_data[0]['feed_name'];
		}


		$return = wp_parse_args( $return, SBI_Feed_Saver::settings_defaults() );


		if ( empty( $return['id'] ) ) {
			return $return;
		}

		if ( ! is_array( $return['id'] ) ) {
			$return['id'] = explode( ',', str_replace( ' ', '',  $return['id'] ) );
		}
		if ( ! is_array( $return['tagged'] ) ) {
			$return['tagged'] = explode( ',', str_replace( ' ', '',  $return['tagged'] ) );
		}
		if ( ! is_array( $return['hashtag'] ) ) {
			$return['hashtag'] = explode( ',', str_replace( ' ', '',  $return['hashtag'] ) );
		}
		$args = array( 'id' => $return['id'] );

		$source_query = SBI_Db::source_query( $args );

		$return['sources'] = array();

		if ( ! empty( $source_query ) ) {

			foreach ( $source_query as $source ) {
				$user_id = $source['account_id'];
				$return['sources'][ $user_id ] = self::get_processed_source_data( $source );
			}
		} else {
			$found_sources = array();

			foreach ( $return['id'] as $id_or_slug ) {
				$maybe_source_from_connected = SBI_Source::maybe_one_off_connected_account_update( $id_or_slug );

				if ( $maybe_source_from_connected ) {
					$found_sources[] = $maybe_source_from_connected;
				}

			}

			if ( ! empty( $found_sources ) ) {
				foreach ( $found_sources as $source ) {
					$user_id = $source['account_id'];
					$return['sources'][ $user_id ] = self::get_processed_source_data( $source );

				}
			} else {

				$source_query = SBI_Db::source_query( $args );

				if ( isset( $source_query[0] ) ) {
					$source = $source_query[0];

					$user_id = $source['account_id'];

					$return['sources'][ $user_id ] = self::get_processed_source_data( $source );
				}
			}

		}

		return $return;
	}

	public static function get_processed_source_data( $source ) {
		$encryption = new \SB_Instagram_Data_Encryption();
		$user_id = $source['account_id'];
		$info = ! empty( $source['info'] ) ? json_decode( $encryption->decrypt( $source['info'] ), true ) : array();

		$cdn_avatar_url = \SB_Instagram_Parse::get_avatar_url( $info );

		$processed = array(
			'record_id' => stripslashes( $source['id'] ),
			'user_id' => $user_id,
			'type' => stripslashes( $source['account_type'] ),
			'privilege' => stripslashes( $source['privilege'] ),
			'access_token' => stripslashes( $encryption->decrypt( $source['access_token'] ) ),
			'username' => stripslashes( $source['username'] ),
			'name' => stripslashes( $source['username'] ),
			'info' => stripslashes( $encryption->decrypt( $source['info'] ) ),
			'error' => stripslashes( $source['error'] ),
			'expires' => stripslashes( $source['expires'] ),
			'profile_picture' => $cdn_avatar_url,
			'local_avatar_url' => \SB_Instagram_Connected_Account::maybe_local_avatar( $source['username'], $cdn_avatar_url )
		);

		return $processed;
	}

	/**
	 * Retrieves and organizes feed setting data for easy use in
	 * the builder
	 * It will NOT get the settings from the DB, but from the Customizer builder
	 * To be used for updating feed preview on the fly
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public function get_feed_settings_preview( $settings_db_data ) {
		if ( false === $settings_db_data || sizeof($settings_db_data) == 0) {
			return false;
		}
		$return = $settings_db_data;
		$return = wp_parse_args( $return, SBI_Feed_Saver::settings_defaults() );
		if ( empty( $return['sources'] ) ) {
			return $return;
		}
		$sources = [];
		foreach ($return['sources'] as $single_source) {
			array_push($sources, $single_source['account_id']);
		}

		$args = array( 'id' => $sources );
		$source_query = SBI_Db::source_query( $args );

		$return['sources'] = array();
		if ( ! empty( $source_query ) ) {
			foreach ( $source_query as $source ) {
				$user_id = $source['account_id'];
				$return['sources'][ $user_id ] = self::get_processed_source_data( $source );
			}
		}

		return $return;
	}



	/**
	 * Default settings, $return_array equalling false will return
	 * the settings in the general way that the "SBI_Shortcode" class,
	 * "sbi_get_processed_options" method does
	 *
	 * @param bool $return_array
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function settings_defaults( $return_array = true ) {
		{
			$defaults = array(
				//V6
				'customizer' => false,

				//Feed general
				'type' => 'user', //user - hashtag -
				'order' => 'recent',
				'id' => [],
				'hashtag' => [],
				'tagged' => [],
				'width' => '',
				'widthunit' => '',
				'widthresp' => true,
				'height' => '',
				'heightunit' => '',
				'sortby' => 'none',
				'disablelightbox' => true,
				'captionlinks' => false,
				'offset' => 0,
				'num' => 20,
				'apinum'           => '',
				'nummobile' => 20,
				'cols' => 4,
				'colstablet' => 2,
				'colsmobile' => 1,
				'disablemobile' => false,
				'imagepadding' => '5',
				'imagepaddingunit' => 'px',
				'layout' => 'grid',

				//Lightbox comments
				'lightboxcomments' => true,
				'numcomments' => 20,

				//Photo hover styles
				'hovereffect' => '',
				'hovercolor' => '',
				'hovertextcolor' => '',
				'hoverdisplay' => 'username,date,instagram',

				//Item misc
				'background' => '',
				'imageres' => 'auto',
				'media' => 'all',
				'videotypes' => 'regular,igtv,reels',
				'showcaption' => true,
				'captionlength' => '',
				'captioncolor' => '',
				'captionsize' => '',
				'showlikes' => true,
				'likescolor' => '',
				'likessize' => '13',
				'hidephotos' => '',

				//Footer
				'showbutton' => true,
				'buttoncolor' => '',
				'buttonhovercolor' => '', // to be tested
				'buttontextcolor' => '',
				'buttontext' => 'Load More',
				'showfollow' => true,
				'followcolor' => '#408bd1',
				'followhovercolor' => '#359dff', // to be tested
				'followtextcolor' => '',
				'followtext' => 'Follow on Instagram',

				//Header
				'showheader' => true,
				'headertextsize'	=> '', //to be tested
				'headercolor' => '',
				'headerstyle' => 'standard',
				'showfollowers' => false,
				'showbio' => true,
				'custombio' => '',
				'customavatar' => '',
				'headerprimarycolor' => '#517fa4',
				'headersecondarycolor' => '#eeeeee',
				'headersize' => 'medium',
				'stories' => true,
				'storiestime' => '',
				'headeroutside' => false,

				'class' => '',
				'ajaxtheme' => '',
				'excludewords' => '',
				'includewords' => '',
				'maxrequests' => 5,

				//Carousel
				'carouselrows' => 1,
				'carouselloop' => 'rewind',
				'carouselarrows' => false,
				'carouselpag' => true,
				'carouselautoplay' => false,
				'carouseltime' => 5000,

				//Highlight
				'highlighttype' => 'pattern',
				'highlightoffset' => 0,
				'highlightpattern' => '',
				'highlighthashtag' => '',
				'highlightids' => '',

				//WhiteList
				'whitelist' => '',

				//Load More on Scroll
				'autoscroll' => false,
				'autoscrolldistance' => '',

				//Permanent
				'permanent' => false,
				'accesstoken' => '',
				'user' => '',

				//Misc
				'feedid' => false,

				'resizeprocess'    => 'background',
				'mediavine'    => '',
				'customtemplates'    => false,
				'moderationmode'    => false,

				//NEWLY ADDED
				//TO BE CHECKED
				'colstablet' => 2,
				'colorpalette'	=> 'inherit',
				'custombgcolor1'	=> '',
				'customtextcolor1'	=> '',
				'customtextcolor2'	=> '',
				'customlinkcolor1'	=> '',
				'custombuttoncolor1'	=> '',
				'custombuttoncolor2'	=> '',



				'photosposts'	=> true,
				'videosposts'	=> true,
				'igtvposts'	=> true,
				'reelsposts' => true,

				'shoppablefeed' => false,
				'shoppablelist' => '{}',
				'moderationlist' => '{"list_type_selected" : "allow", "allow_list" : [], "block_list" : [] }',
				'customBlockModerationlist'    => '',
				'enablemoderationmode' => false,

				'fakecolorpicker' => ''

			);

			$defaults = SBI_Feed_Saver::filter_defaults( $defaults );

			// some settings are comma separated and not arrays when the feed is created
			if ( $return_array ) {
				$settings_with_multiples = array(
					'sources'
				);

				foreach ( $settings_with_multiples as $multiple_key ) {
					if ( isset( $defaults[ $multiple_key ] ) ) {
						$defaults[ $multiple_key ] = explode( ',', $defaults[ $multiple_key ] );
					}
				}
			}

			return $defaults;
		}
	}

	/**
	 * Provides backwards compatibility for extensions
	 *
	 * @param array $defaults
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function filter_defaults( $defaults ) {

		return $defaults;
	}

	/**
	 * Saves settings for legacy feeds. Runs on first update automatically.
	 *
	 * @since 6.0
	 */
	public static function set_legacy_feed_settings() {
		$to_save = SBI_Post_Set::legacy_to_builder_convert();

		$to_save_json = sbi_json_encode( $to_save );

		update_option( 'sbi_legacy_feed_settings', $to_save_json, false );
	}

	/**
	 * Used for taking raw post data related to settings
	 * an sanitizing it and sorting it to easily use in
	 * the database tables
	 *
	 * @since 6.0
	 */
	private function sanitize_and_sort_data() {
		$data = $this->data;

		$sanitized_and_sorted = array(
			'feeds' => array(),
			'feed_settings' => array()
		);

		foreach ( $data as $key => $value ) {

			$data_type = SBI_Feed_Saver_Manager::get_data_type( $key );
			$sanitized_values = array();
			if ( is_array( $value ) ) {
				foreach ( $value as $item ) {
					$type = SBI_Feed_Saver_Manager::is_boolean( $item ) ? 'boolean' : $data_type['sanitization'];
					$sanitized_values[] = SBI_Feed_Saver_Manager::sanitize( $type , $item );
				}
			} else {
				$type = SBI_Feed_Saver_Manager::is_boolean( $value ) ? 'boolean' : $data_type['sanitization'];
				$sanitized_values[] = SBI_Feed_Saver_Manager::sanitize( $type, $value );
			}

			$single_sanitized = array(
				'key' => $key,
				'values' => $sanitized_values
			);

			$sanitized_and_sorted[ $data_type['table'] ][] = $single_sanitized;
		}

		$this->sanitized_and_sorted_data = $sanitized_and_sorted;
	}
}
