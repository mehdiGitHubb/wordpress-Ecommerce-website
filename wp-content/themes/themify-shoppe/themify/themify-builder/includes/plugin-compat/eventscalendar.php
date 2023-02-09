<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_EventsCalendar {

	static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'the_events_calendar_fix' ) );
	}

	/**
	 * Fix duplicate content in The Events Calendar plugin
	 *
	 * @link https://wordpress.org/plugins/the-events-calendar/
	 */
	public static function the_events_calendar_fix() {
		if ( is_singular( 'tribe_events' ) ) {
			add_filter( 'tribe_events_after_html', array( __CLASS__, 'tribe_events_after_html' ) );
		}
	}

	/**
	 * Disable Builder frontend output after "tribe_events_after_html" filter
	 *
	 * @return string
	 */
	public static function tribe_events_after_html( $after ) {
		global $ThemifyBuilder;
		remove_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front' ), 11 );
		return $after;
	}
}