<?php
namespace AIOSEO\Plugin\Lite\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Api as CommonApi;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Wizard extends CommonApi\Wizard {
	/**
	 * Save the wizard information.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveWizard( $request ) {
		$response = parent::saveWizard( $request );
		$body     = $request->get_json_params();
		$section  = ! empty( $body['section'] ) ? sanitize_text_field( $body['section'] ) : null;
		$wizard   = ! empty( $body['wizard'] ) ? $body['wizard'] : null;

		// Save the smart recommendations section.
		if ( 'smartRecommendations' === $section && ! empty( $wizard['smartRecommendations'] ) ) {
			$smartRecommendations = $wizard['smartRecommendations'];
			if ( isset( $smartRecommendations['usageTracking'] ) ) {
				aioseo()->options->advanced->usageTracking = $smartRecommendations['usageTracking'];
			}
		}

		return $response;
	}
}