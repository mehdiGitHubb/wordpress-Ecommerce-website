<?php

namespace InstagramFeed\Services;

use Smashballoon\Stubs\Services\ServiceProvider;

class ShortcodeService extends ServiceProvider {
	public function register() {
		add_filter('do_shortcode_tag', [$this, 'check_cron_status'], 10 ,  4);
	}

	/**
	 * Hooks into do_shortcode_tag and runs only on instagram-feed shortcode
	 * Forces cachetime attribute if the cron job next run is out of order.
	 *
	 * @param $output
	 * @param $tag
	 * @param $attributes
	 * @param $m
	 *
	 * @return string
	 */
	public function check_cron_status( $output, $tag, $attributes, $m ) {
		if ( $tag !== 'instagram-feed' ) {
			return $output;
		}

		global $shortcode_tags;
		$next_run = wp_next_scheduled('sbi_feed_update');
		$is_late  = false !== $next_run && $next_run < ( time() - 1800 );

		if ( false === $next_run || $next_run < 0 || $is_late ) {
			if(!is_array($attributes)) {
				$attributes = [];
			}

			$attributes['cachetime'] = $this->get_cache_time();
			$content = isset( $m[5] ) ? $m[5] : null;

			return $m[1] . call_user_func( $shortcode_tags[ $tag ], $attributes, $content, $tag ) . $m[6];
		}

		return $output;
	}

	private function get_cache_time() {
		$schedule = wp_get_schedule( 'sbi_feed_update' );
		if($schedule === 'twicedaily') {
			return 12 * 60;
		}

		return 30;
	}
}