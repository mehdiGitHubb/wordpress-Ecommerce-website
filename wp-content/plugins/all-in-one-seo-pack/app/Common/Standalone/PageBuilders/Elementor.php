<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Plugin as ElementorPlugin;
use Elementor\Controls_Manager as ControlsManager;
use Elementor\Core\DocumentTypes\PageBase;

/**
 * Integrate our SEO Panel with Elementor Page Builder.
 *
 * @since 4.1.7
 */
class Elementor extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	public $plugins = [
		'elementor/elementor.php',
		'elementor-pro/elementor-pro.php',
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.1.7
	 *
	 * @var string
	 */
	public $integrationSlug = 'elementor';

	/**
	 * Init the integration.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function init() {
		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		if ( ! did_action( 'elementor/init' ) ) {
			add_action( 'elementor/init', [ $this, 'addPanelTab' ] );
		} else {
			$this->addPanelTab();
		}

		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'elementor/documents/register_controls', [ $this, 'registerDocumentControls' ] );
		add_action( 'elementor/editor/footer', [ $this, 'addContainers' ] );
	}

	/**
	 * Add the AIOSEO Panel Tab on Elementor.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function addPanelTab() {
		ControlsManager::add_tab( 'aioseo', AIOSEO_PLUGIN_SHORT_NAME );
	}

	/**
	 * Register the Elementor Document Controls.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function registerDocumentControls( $document ) {
		// PageBase is the base class for documents like `post` `page` and etc.
		if ( ! $document instanceof PageBase || ! $document::get_property( 'has_elements' ) ) {
			return;
		}

		// This is needed to get the tab to appear, but will be overwritten in the JavaScript.
		$document->start_controls_section(
			'aioseo_section',
			[
				'label' => __( 'AIOSEO', 'all-in-one-seo-pack' ),
				'tab'   => 'aioseo',
			]
		);

		$document->end_controls_section();
	}

	/**
	 * Returns whether or not the given Post ID was built with Elementor.
	 *
	 * @since 4.1.7
	 *
	 * @param  int $postId The Post ID.
	 * @return boolean     Whether or not the Post was built with Elementor.
	 */
	public function isBuiltWith( $postId ) {
		if ( ! class_exists( 'ElementorPlugin' ) ) {
			return false;
		}

		$elementorPost = ElementorPlugin::instance()->documents->get( $postId );
		if ( empty( $elementorPost ) ) {
			return false;
		}

		return ElementorPlugin::instance()->documents->get( $postId )->is_built_with_elementor();
	}

	/**
	 * Add the containers to mount our panel.
	 *
	 * @since 4.1.9
	 *
	 * @return void
	 */
	public function addContainers() {
		echo '<div id="aioseo-admin"></div>';
	}
}