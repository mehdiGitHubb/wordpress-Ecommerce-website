<?php
namespace AIOSEO\Plugin\Common\Standalone;

use AIOSEO\Plugin\Pro\Standalone as ProStandalone;

/**
 * Registers the standalone components.
 *
 * @since 4.2.0
 */
class Standalone {
	/**
	 * HeadlineAnalyzer class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var HeadlineAnalyzer
	 */
	public $headlineAnalyzer = null;

	/**
	 * FlyoutMenu class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var FlyoutMenu
	 */
	public $flyoutMenu = null;

	/**
	 * SeoPreview class instance.
	 *
	 * @since 4.2.8
	 *
	 * @var SeoPreview
	 */
	public $seoPreview = null;

	/**
	 * SetupWizard class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var SetupWizard
	 */
	public $setupWizard = null;

	/**
	 * List of page builder integration class instances.
	 *
	 * @since 4.2.7
	 *
	 * @var array[Object]
	 */
	public $pageBuilderIntegrations = [];

	/**
	 * List of block class instances.
	 *
	 * @since 4.2.7
	 *
	 * @var array[Object]
	 */
	public $standaloneBlocks = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		$this->headlineAnalyzer = new HeadlineAnalyzer;
		$this->flyoutMenu       = new FlyoutMenu;
		$this->seoPreview       = new SeoPreview;
		$this->setupWizard      = new SetupWizard;

		aioseo()->pro ? new ProStandalone\DetailsColumn : new DetailsColumn;

		new UserProfileTab;
		new PublishPanel;
		new LimitModifiedDate;
		new Notifications;

		$this->pageBuilderIntegrations = [
			'elementor' => new PageBuilders\Elementor,
			'divi'      => new PageBuilders\Divi,
			'seedprod'  => new PageBuilders\SeedProd
		];

		$this->standaloneBlocks = [
			'tocBlock' => new Blocks\TableOfContents(),
			'faqBlock' => new Blocks\FaqPage()
		];
	}
}