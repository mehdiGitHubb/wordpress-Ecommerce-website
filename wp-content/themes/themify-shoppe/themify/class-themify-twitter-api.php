<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Themify Twitter API
 * @version 1.0.0
 */
class Themify_Twitter_Api {

	public $bearer_token = null;
	const TOKEN_KEY = 'themify_bearer_token';

	// Default credentials
	private $keys = array(
		'consumer_key' => '',
		'consumer_secret' => '',
	);

	/**
	 * WordPress Twitter API Constructor
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		if ( empty( $args ) ) {
			$args = self::get_keys();
		}
		$this->keys = $args;
	}

	/**
	 * Get the token from oauth Twitter API
	 *
	 * @return string Oauth Token
	 */
	private function get_bearer_token() {
		$bearer_token_credentials = $this->keys['consumer_key'] . ':' . $this->keys['consumer_secret'];
		/**
		 * Encode token credentials since Twitter requires it that way.
		 * @since 2.0.2
		 */
		$bearer_token_credentials_64 = base64_encode( $bearer_token_credentials );

		$args = array(
			'method' => 'POST',
			'timeout' => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'compress' => true,
			'headers' => array(
				'Authorization'=>'Basic ' . $bearer_token_credentials_64,
				'Content-Type'=>'application/x-www-form-urlencoded;charset=UTF-8',
				'Accept-Encoding' => 'gzip'
			),
			'body' => array( 'grant_type' => 'client_credentials' ),
			'cookies' => array(),
			'sslverify' => false
		);

		$response = wp_remote_post( 'https://api.twitter.com/oauth2/token', $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif( 200 != $response['response']['code'] ) {
			return new WP_Error( 'bad_response', __( 'Error connecting to Twitter, please try again later.', 'themify' ) );
		}

		$result = json_decode( $response['body'] );
		
		delete_option( self::TOKEN_KEY );
		Themify_Storage::set( self::TOKEN_KEY, $result->access_token );
		return $result->access_token;
	}

	/**
	 * Query twitter's API
	 *
	 * @uses $this->get_bearer_token() to retrieve token if not working
	 *
	 * @param string $query Insert the query in the format "count=1&include_entities=true&include_rts=true&screen_name=micc1983!
	 *
	 * @return bool|object Return an object containing the result
	 */
	public function query( $query, $settings = [] ) {
		$query = wp_parse_args( $query, [
			'username' => '',
			'limit' => 5,
			'include_retweets' => true,
			'exclude_replies' => false,
		] );
		$settings = wp_parse_args( $settings, [
			'query_type' => 'statuses/user_timeline',
			'disable_cache' => false,
			'cache_duration' => 10, /* in minutes */
			'twitter_keys_page' => admin_url( 'admin.php?page=themify#setting-twitter_settings' ),
		] );
		if ( ! $settings['disable_cache'] ) {
			$transient_key = implode( '', $query );
			$transient = Themify_Storage::get( $transient_key,'tf_twit_' );			
			if ( false !== $transient ) {
				return json_decode( $transient );
			}
		}

		if ( empty( $query['username'] ) ) {
			return new WP_Error( 'empty_username', __( 'Username cannot be empty.', 'themify' ) );
		}
		if ( empty( $this->keys['consumer_key'] ) || empty( $this->keys['consumer_secret'] ) ) {
			return new WP_Error( 'twitter_keys_missing', sprintf( __( 'Error: access keys missing in <a href="%s">Themify > Settings > Twitter Settings</a>', 'themify' ), $settings['twitter_keys_page'] ) );
		}
		if ( ! $this->bearer_token = Themify_Storage::get( self::TOKEN_KEY ) ) {
			$this->bearer_token = $this->get_bearer_token();
		}
		if ( is_wp_error( $this->bearer_token ) ) {
			return $this->bearer_token;
		}

		$args = array(
			'method'		=> 'GET',
			'timeout'		=> 5,
			'redirection'	=> 5,
			'httpversion'	=> '1.0',
			'blocking'		=> true,
			'headers'		=> array(
				'Authorization'		=>	'Bearer ' . $this->bearer_token,
				'Accept-Encoding'	=>	'gzip'
			),
			'body' 			=> null,
			'cookies' 		=> array(),
			'sslverify' 	=> false
		);

		$query['user_name'] = urlencode( strip_tags( sanitize_user( str_replace( '@', '', $query['username'] ) ) ) );
		$query_string = 'screen_name='. $query['username'] . '&count=' . $query['limit'] . '&include_rts='. (int) $query['include_retweets'] . '&exclude_replies=' . (int) $query['exclude_replies'] . '&include_entities=true';
		$response = wp_remote_get( 'https://api.twitter.com/1.1/' . $settings['query_type'] . '.json?' . $query_string, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( 200 != $response['response']['code'] ) {
			$message = sprintf( __( 'Bad response from Twitter: %s, %s', 'themify' ), $response['response']['code'], $response['response']['message'] );
			return new WP_Error( 'bad_query', $message );
		}

		if ( ! $settings['disable_cache'] ) {
			$cache_duration = empty( $settings['cache_duration'] ) ? 10 : (int) $settings['cache_duration'];
			Themify_Storage::set( $transient_key, $response['body'], $cache_duration * 60,'tf_twit_' );
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Returns the Twitter API keys configured in the settings page
	 *
	 * @return array
	 */
	public static function get_keys() {
		return [
			'consumer_key' => themify_builder_get( 'setting-twitter_settings_consumer_key', 'builder_settings_twitter_consumer_key' ),
			'consumer_secret' => themify_builder_get( 'setting-twitter_settings_consumer_secret', 'builder_settings_twitter_consumer_secret' ),
		];
	}

	public static function clear_cache() {
	    global $wpdb;
	    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient_tf_twit_%')");
	    return Themify_Storage::query("DELETE FROM %s WHERE `key` LIKE 'tf_twit_%%'");
	}

	/**
	 * Gets a $tweet object, returns clickable tweet's text
	 *
	 * @return string
	 */
	public static function make_clickable( $tweet ) {
		$text = $tweet->text;
		if ( ! empty( $tweet->entities->urls[0]->url ) ) {
			$url = $tweet->entities->urls[0]->url;
		} else if ( ! empty( $tweet->entities->media[0]->url ) ) {
			$url = $tweet->entities->media[0]->url;
		}
		if ( ! empty( $url ) ) {
			return '<a href="' . esc_url( $url ) . '">' . $text . '</a>';
		}

		foreach ( $tweet->entities as $entity_type => $entity ) {
			if ( 'hashtags' === $entity_type ) {
				foreach( $entity as $hashtag ) {
					$update_with = '<a href="' . esc_url( '//twitter.com/search?q=%23' . $hashtag->text . '&src=hash' ) . '" target="_blank" title="' . esc_attr( $hashtag->text ) . '" class="twitter-user">#' . $hashtag->text . '</a>';
					$text = str_replace( '#'.$hashtag->text, $update_with, $text );
				}
			} elseif ( 'user_mentions' ===  $entity_type ) {
				foreach( $entity as $user ) {
					$user->screen_name = str_replace( '@', '', $user->screen_name );
					$update_with = '<a href="' . esc_url( '//twitter.com/' . $user->screen_name ) . '" target="_blank" title="' . esc_attr( $user->name ) . '" class="twitter-user">@' . $user->screen_name . '</a>';
					$text = str_replace( '@'.$user->screen_name, $update_with, $text );
				}
			}
		}

		return $text;
	}
}