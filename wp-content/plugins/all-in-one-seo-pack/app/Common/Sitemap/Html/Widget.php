<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widget.
 *
 * @since 4.1.3
 */
class Widget extends \WP_Widget {
	/**
	 * The default attributes.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $defaults = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.3
	 */
	public function __construct() {
		// The default widget settings.
		$this->defaults = [
			'title'            => '',
			'show_label'       => 'on',
			'archives'         => '',
			'nofollow_links'   => '',
			'order'            => 'asc',
			'order_by'         => 'publish_date',
			'publication_date' => 'on',
			'post_types'       => [ 'post', 'page' ],
			'taxonomies'       => [ 'category', 'post_tag' ],
			'excluded_posts'   => '',
			'excluded_terms'   => ''
		];

		$widgetSlug     = 'aioseo-html-sitemap-widget';
		$widgetOptions  = [
			'classname'   => $widgetSlug,
			// Translators: The short plugin name ("AIOSEO").
			'description' => sprintf( esc_html__( '%1$s HTML sitemap widget.', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME )
		];
		$controlOptions = [
			'id_base' => $widgetSlug
		];

		// Translators: 1 - The plugin short name ("AIOSEO").
		parent::__construct( $widgetSlug, sprintf( esc_html__( '%1$s - HTML Sitemap', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME ), $widgetOptions, $controlOptions );
	}

	/**
	 * Callback for the widget.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $args     The widget arguments.
	 * @param  array $instance The widget instance options.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		if ( ! aioseo()->options->sitemap->html->enable ) {
			return;
		}

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,Generic.Files.LineLength.MaxExceeded
		}

		$instance = aioseo()->htmlSitemap->frontend->getAttributes( $instance );
		aioseo()->htmlSitemap->frontend->output( true, $instance );
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Callback to update the widget options.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions The new options.
	 * @param  array $oldOptions The old options.
	 * @return array             The new options.
	 */
	public function update( $newOptions, $oldOptions ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$settings = [
			'title',
			'order',
			'order_by',
			'show_label',
			'publication_date',
			'archives',
			'excluded_posts',
			'excluded_terms'
		];

		foreach ( $settings as $setting ) {
			$newOptions[ $setting ] = ! empty( $newOptions[ $setting ] ) ? wp_strip_all_tags( $newOptions[ $setting ] ) : '';
		}

		$includedPostTypes = [];
		if ( ! empty( $newOptions['post_types'] ) ) {
			$postTypes = $this->getPublicPostTypes( true );
			foreach ( $newOptions['post_types'] as $v ) {
				if ( is_numeric( $v ) ) {
					$includedPostTypes[] = $postTypes[ $v ];
				} else {
					$includedPostTypes[] = $v;
				}
			}
		}
		$newOptions['post_types'] = $includedPostTypes;

		$includedTaxonomies = [];
		if ( ! empty( $newOptions['taxonomies'] ) ) {
			$taxonomies = aioseo()->helpers->getPublicTaxonomies( true );
			foreach ( $newOptions['taxonomies'] as $v ) {
				if ( is_numeric( $v ) ) {
					$includedTaxonomies[] = $taxonomies[ $v ];
				} else {
					$includedTaxonomies[] = $v;
				}
			}
		}
		$newOptions['taxonomies'] = $includedTaxonomies;

		if ( ! empty( $newOptions['excluded_posts'] ) ) {
			$newOptions['excluded_posts'] = $this->sanitizeExcludedIds( $newOptions['excluded_posts'] );
		}

		if ( ! empty( $newOptions['excluded_terms'] ) ) {
			$newOptions['excluded_terms'] = $this->sanitizeExcludedIds( $newOptions['excluded_terms'] );
		}

		return $newOptions;
	}

	/**
	 * Callback for the widgets options form.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $instance The widget options.
	 * @return void
	 */
	public function form( $instance ) {
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$instance        = wp_parse_args( (array) $instance, $this->defaults );
		$postTypeObjects = $this->getPublicPostTypes();
		$postTypes       = $this->getPublicPostTypes( true );
		$taxonomyObjects = aioseo()->helpers->getPublicTaxonomies();
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		include AIOSEO_DIR . '/app/Common/Views/sitemap/html/widget-options.php';
	}

	/**
	 * Returns the public post types (without attachments).
	 *
	 * @since 4.1.3
	 *
	 * @param  boolean $namesOnly Whether only the names should be returned.
	 * @return array              The public post types.
	 */
	private function getPublicPostTypes( $namesOnly = false ) {
		$postTypes = aioseo()->helpers->getPublicPostTypes( $namesOnly );
		foreach ( $postTypes as $k => $postType ) {
			if ( is_array( $postType ) && 'attachment' === $postType['name'] ) {
				unset( $postTypes[ $k ] );
				break;
			}
			if ( ! is_array( $postType ) && 'attachment' === $postType ) {
				unset( $postTypes[ $k ] );
				break;
			}
		}

		return array_values( $postTypes );
	}

	/**
	 * Sanitizes the excluded IDs by removing any non-integer values.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $ids The IDs as a string, comma-separated.
	 * @return string      The sanitized IDs as a string, comma-separated.
	 */
	private function sanitizeExcludedIds( $ids ) {
		$ids = array_map( 'trim', explode( ',', $ids ) );
		$ids = array_filter( $ids, 'is_numeric' );
		$ids = esc_sql( implode( ', ', $ids ) );

		return $ids;
	}
}