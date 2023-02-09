<?php

class SB_Instagram_SiteHealth {

	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @since 1.5.5
	 *
	 * @return bool
	 */
	public function allow_load() {

		global $wp_version;

		return version_compare( $wp_version, '5.2', '>=' );
	}

	/**
	 * Loads an integration.
	 *
	 * @since 1.5.5
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 1.5.5
	 */
	protected function hooks() {
		add_filter( 'site_status_tests', array( $this, 'add_tests' ) );
	}

	/**
	 * Add MonsterInsights WP Site Health tests.
	 *
	 * @param array $tests The current filters array.
	 *
	 * @return array
	 */
	public function add_tests( $tests ) {
		$tests['direct']['sbi_test_check_errors'] = array(
			'label' => __( 'Instagram Feed Errors', 'instagram-feed' ),
			'test'  => array( $this, 'test_check_errors' )
		);

		return $tests;
	}

	/**
	 * Checks if there are Instagram API Errors
	 */
	public function test_check_errors() {
		$result = array(
			'label'       => __( 'Instagram Feed has no critical errors', 'instagram-feed' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Instagram Feed', 'instagram-feed' ),
				'color' => 'blue',
			),
			'description' => __( 'No critical errors have been detected.', 'instagram-feed' ),
			'test'        => 'sbi_test_check_errors',
		);

		global $sb_instagram_posts_manager;


		if ( $sb_instagram_posts_manager->are_critical_errors() ) {
			$link = admin_url( 'admin.php?page=sbi-settings');
			$result['status'] = 'critical';
			$result['label'] = __( 'Your Instagram Feed is experiencing an error.', 'instagram-feed' );
			$result['description'] = sprintf( __( 'A critical issue has been detected with your Instagram Feed. Visit the %sInstagram Feed settings page%s to fix the issue.', 'instagram-feed' ), '<a href="' . esc_url( $link ) . '">', '</a>' );
		}


		return $result;
	}
}
