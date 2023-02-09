<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds support for Google AMP.
 *
 * @since 4.0.0
 */
class Amp {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'runAmp' ] );
	}

	/**
	 * Run the AMP hooks.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function runAmp() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Add social meta to AMP plugin.
		$enableAmp = apply_filters( 'aioseo_enable_amp_social_meta', true );

		if ( $enableAmp ) {
			$useSchema = apply_filters( 'aioseo_amp_schema', true );

			if ( $useSchema ) {
				add_action( 'amp_post_template_head', [ $this, 'removeHooksAmpSchema' ], 9 );
			}

			add_action( 'amp_post_template_head', [ aioseo()->head, 'output' ], 11 );
		}
	}

	/**
	 * Remove Hooks with AMP's Schema.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function removeHooksAmpSchema() {
		// Remove AMP Schema hook used for outputting data.
		remove_action( 'amp_post_template_head', 'amp_print_schemaorg_metadata' );
	}
}