<?php
namespace AIOSEO\Plugin\Common\Help;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Help {
	/**
	 * Source of the documentation content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $url = 'https://cdn.aioseo.com/wp-content/docs.json';

	/**
	 * Settings.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $settings = [
		'docsUrl'          => 'https://aioseo.com/docs/',
		'supportTicketUrl' => 'https://aioseo.com/account/support/',
		'upgradeUrl'       => 'https://aioseo.com/pricing/',
	];

	/**
	 * Gets the URL for the notifications api.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL to use for the api requests.
	 */
	private function getUrl() {
		if ( defined( 'AIOSEO_DOCS_FEED_URL' ) ) {
			return AIOSEO_DOCS_FEED_URL;
		}

		return $this->url;
	}

	/**
	 * Get docs from the network cache.
	 *
	 * @since 4.0.0
	 *
	 * @return array Docs data.
	 */
	public function getDocs() {
		$aioseoAdminHelpDocs          = aioseo()->core->networkCache->get( 'admin_help_docs' );
		$aioseoAdminHelpDocsCacheTime = WEEK_IN_SECONDS;
		if ( null === $aioseoAdminHelpDocs ) {
			$request = aioseo()->helpers->wpRemoteGet( $this->getUrl() );

			if ( is_wp_error( $request ) ) {
				return [];
			}

			$response = $request['response'];

			if ( ( $response['code'] <= 200 ) && ( $response['code'] > 299 ) ) {
				$aioseoAdminHelpDocsCacheTime = 10 * MINUTE_IN_SECONDS;
			}
			$aioseoAdminHelpDocs = wp_remote_retrieve_body( $request );
			aioseo()->core->networkCache->update( 'admin_help_docs', $aioseoAdminHelpDocs, $aioseoAdminHelpDocsCacheTime );
		}

		return $aioseoAdminHelpDocs ? json_decode( $aioseoAdminHelpDocs, true ) : [];
	}
}