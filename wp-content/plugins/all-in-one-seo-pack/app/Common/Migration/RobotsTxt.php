<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Robots.txt settings from V3.
 *
 * @since 4.0.0
 */
class RobotsTxt {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$oldOptions = aioseo()->migration->oldOptions;

		$rules = aioseo()->options->tools->robots->rules;

		if (
			! empty( $oldOptions['modules']['aiosp_robots_options'] ) &&
			! empty( $oldOptions['modules']['aiosp_robots_options']['aiosp_robots_rules'] )
		) {
			$rules += $this->convertRules( $oldOptions['modules']['aiosp_robots_options']['aiosp_robots_rules'] );
		}

		aioseo()->options->tools->robots->rules = $rules;
	}

	/**
	 * Converts the old Robots.txt rules to the new format.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $oldRules The old rules.
	 * @return array $newRules The converted rules.
	 */
	private function convertRules( $oldRules ) {
		$newRules = [];
		foreach ( $oldRules as $oldRule ) {
			$newRule                = new \stdClass();
			$newRule->userAgent     = aioseo()->helpers->sanitizeOption( $oldRule['agent'] );
			$newRule->rule          = aioseo()->helpers->sanitizeOption( lcfirst( $oldRule['type'] ) );
			$newRule->directoryPath = aioseo()->helpers->sanitizeOption( $oldRule['path'] );

			array_push( $newRules, wp_json_encode( $newRule ) );
		}

		return $newRules;
	}
}