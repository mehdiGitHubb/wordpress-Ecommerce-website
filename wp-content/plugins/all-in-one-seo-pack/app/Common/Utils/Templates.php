<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Templates
 *
 * @since 4.0.17
 *
 * @package AIOSEO\Plugin\Common\Utils
 */
class Templates {
	/**
	 * This plugin absolute path.
	 *
	 * @since 4.0.17
	 *
	 * @var string
	 */
	protected $pluginPath = AIOSEO_DIR;

	/**
	 * Paths were our template files are located.
	 *
	 * @since 4.0.17
	 *
	 * @var string Array of paths.
	 */
	protected $paths = [
		'app/Common/Views'
	];

	/**
	 *
	 * The theme folder.
	 *
	 * @since 4.0.17
	 *
	 * @var string
	 */
	private $themeTemplatePath = 'aioseo/';

	/**
	 *
	 * A theme subfolder.
	 *
	 * @since 4.0.17
	 *
	 * @var string
	 */
	protected $themeTemplateSubpath = '';

	/**
	 * Locate a template file in the theme or our plugin paths.
	 *
	 * @since 4.0.17
	 *
	 * @param  string $templateName The template name.
	 * @return string               The template absolute path.
	 */
	public function locateTemplate( $templateName ) {
		// Try to find template file in the theme.
		$template = locate_template(
			[
				trailingslashit( $this->getThemeTemplatePath() ) . trailingslashit( $this->getThemeTemplateSubpath() ) . $templateName
			]
		);

		if ( ! $template ) {
			// Try paths, in order.
			foreach ( $this->paths as $path ) {
				$template = trailingslashit( $this->addPluginPath( $path ) ) . $templateName;
				if ( aioseo()->core->fs->exists( $template ) ) {
					break;
				}
			}
		}

		return apply_filters( 'aioseo_locate_template', $template, $templateName );
	}

	/**
	 * Includes a template if the file exists.
	 *
	 * @param  string $templateName The template path/name.php to be included.
	 * @param  null   $data         Data passed down to the template.
	 * @return void
	 */
	public function getTemplate( $templateName, $data = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$template = $this->locateTemplate( $templateName );
		if ( ! empty( $template ) and aioseo()->core->fs->exists( $template ) ) {
			include $template;
		}
	}

	/**
	 * Add this plugin path when trying the paths.
	 *
	 * @since 4.0.17
	 *
	 * @param  string $path A path.
	 * @return string       A path with the plugin absolute path.
	 */
	protected function addPluginPath( $path ) {
		return trailingslashit( $this->pluginPath ) . $path;
	}

	/**
	 * Returns the theme folder for templates.
	 *
	 * @since 4.0.17
	 *
	 * @return string The theme folder for templates.
	 */
	public function getThemeTemplatePath() {
		return apply_filters( 'aioseo_template_path', $this->themeTemplatePath );
	}

	/**
	 *
	 * Returns the theme subfolder for templates.
	 *
	 * @since 4.0.17
	 *
	 * @return string The theme subfolder for templates.
	 */
	public function getThemeTemplateSubpath() {
		return apply_filters( 'aioseo_template_subpath', $this->themeTemplateSubpath );
	}
}