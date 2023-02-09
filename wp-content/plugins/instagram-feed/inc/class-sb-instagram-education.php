<?php

/**
 *
 * @since 5.5
 */
class SB_Instagram_Education {

	var $plugin_version;

	/**
	 * Constructor.
	 *
	 * @since 5.5
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 5.5
	 */
	public function hooks() {
		$this->plugin_version = sbi_is_pro_version() ? 'pro' : 'free';
	}

	/**
	 * "Did You Know?" messages.
	 *
	 * @since 5.5
	 */
	public function dyk_messages() {

		$free_messages = array(
			array(
				'title' => esc_html__( 'Get the Most out of Hashtags', 'instagram-feed' ),
				'content' => esc_html__( 'You can use hashtags on Instagram for so many things; targeted promotions, engaging with your audience, running contests, or just for categorizing posts. Learn more about how you can display Instagram hashtag feeds on your website using the Instagram Feed Pro plugin.', 'instagram-feed' ),
				'more' => 'https://smashballoon.com/instagram-feed/features/?utm_campaign=instagram-free&utm_source=dyk-messages&utm_content=hashtag#hashtag',
				'item' => 1,
			),
			array(
				'title' => esc_html__( 'Keep Visitors on Your Site', 'instagram-feed' ),
				'content' => esc_html__( "You've done the hard work of getting a visitor onto your site, now keep them there by displaying your Instagram content inside a pop-up lightbox, rather than sending your visitors away to Instagram.  Learn more about the Instagram Feed Pro lightbox feature.", 'instagram-feed' ),
				'more' => 'https://smashballoon.com/instagram-feed/features/?utm_campaign=instagram-free&utm_source=dyk-messages&utm_content=popuplightbox#popuplightbox',
				'item' => 2,
			),
			array(
				'title' => esc_html__( 'Highlight Your Posts and Create Carousels', 'instagram-feed' ),
				'content' => esc_html__( "Feature specific Instagram posts in your feeds by using the Highlight layout to feature specific posts, either by using their ID or a hashtag in their caption. Also create rotating carousels of your photos and videos to best utilize the space on your site. These layouts and more are available in our Pro version.", 'instagram-feed' ),
				'more' => 'https://smashballoon.com/instagram-feed/features/?utm_campaign=instagram-free&utm_source=dyk-messages&utm_content=highlight#highlight',
				'item' => 3,
			),
			array(
				'title' => esc_html__( 'Moderate your Feed Content', 'instagram-feed' ),
				'content' => esc_html__( "Control exactly which posts show up in your feed by using the Visual Moderation Mode feature to pick and choose what to display. Remove specific posts or create a whitelist of approved content using Instagram Feed Pro.", 'instagram-feed' ),
				'more' => 'https://smashballoon.com/instagram-feed/features/?utm_campaign=instagram-free&utm_source=dyk-messages&utm_content=moderation#moderation',
				'item' => 4,
			),
		);

		$pro_messages = array(
			array(
				'title' => esc_html__( 'Automated YouTube Live Streaming', 'instagram-feed' ),
				'content' => esc_html__( 'You can automatically feed live YouTube videos to your website using our Feeds For YouTube Pro plugin. It takes all the hassle out of publishing live videos to your site by automating the process.', 'instagram-feed' ),
				'more' => 'https://smashballoon.com/youtube-feed/',
				'item' => 1,
			),
			array(
				'title' => esc_html__( 'Display Facebook Pages and Groups', 'instagram-feed' ),
				'content' => esc_html__( 'Have a Facebook Page or Group? Easily embed a feed of posts into your website, delivering fresh content automatically to your site from Facebook. Posts, Photos, Events, Videos, Albums, Reviews, and more!', 'instagram-feed' ),
				'more' => 'https://smashballoon.com/custom-facebook-feed/',
				'item' => 2,
			),
			array(
				'title' => esc_html__( 'Adding Social Proof with Twitter Feeds', 'instagram-feed' ),
				'content' => esc_html__( 'Twitter testimonials are one of the best ways to add verifiable social proof to your website. They add credibility to your brand, product, or service by displaying reviews from real people to your site, helping to convert more visitors into customers. Our free Custom Twitter Feeds plugin makes displaying Tweets on your website a breeze.', 'instagram-feed' ),
				'more' => 'https://wordpress.org/plugins/custom-twitter-feeds/',
				'item' => 3,
			),
		);

		if ( $this->plugin_version === 'pro' ) {
			return $pro_messages;
		}
		return $free_messages;

	}

	/**
	 * "Did You Know?" random message.
	 *
	 * @since 5.5
	 */
	public function dyk_message_rnd() {

		$messages = $this->dyk_messages();

		$index = array_rand( $messages );

		return $messages[ $index ];
	}

	/**
	 * "Did You Know?" display message.
	 *
	 * @since 5.5
	 *
	 */
	public function dyk_display() {

		$dyk_message  = $this->dyk_message_rnd();

		if ( ! empty( $dyk_message['more'] ) ) {
			//utm_source=plugin-pro&utm_campaign=
			$dyk_message['more'] = add_query_arg(
				array(
					'utm_source'   => 'issueemail',
					'utm_campaign' => 'instagram-'.$this->plugin_version,
					'utm_medium'  => $dyk_message['item'],
				),
				$dyk_message['more']
			);
		}

		return $dyk_message;
	}
}
