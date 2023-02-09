<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Themify_Child_Theme_Generator {

	static function init() {
		add_action( 'wp_ajax_themify_generate_child_theme', [ __CLASS__, 'wp_ajax_themify_generate_child_theme' ] );
	}

	/**
	 * Ajax handler for auto generating child themes
	 *
	 * @hooked to "wp_ajax_themify_generate_child_theme"
	 */
	static function wp_ajax_themify_generate_child_theme() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		$new_theme = self::maybe_generate_theme();
		if ( is_wp_error( $new_theme ) ) {
			wp_send_json_error( $new_theme );
		}

		/* switch theme */
		switch_theme( $new_theme['name'] );
		update_option( 'theme_switched', false ); /* disables 'after_switch_theme' hook */

		if ( 1 === (int) $_POST['import_customizer'] ) {
			/* import Customizer settings */
			$parent = get_template();
			$mods = get_option( "theme_mods_{$parent}" );
			update_option( "theme_mods_{$new_theme['name']}", $mods );
		}

		/* clear out concated stylesheets */
		Themify_Enqueue_Assets::clearConcateCss();

		/* Make child theme an allowed theme (network enable theme) */
		if ( is_multisite() ) {
			$allowed_themes = get_site_option( 'allowedthemes' );
			$allowed_themes[ $new_theme['name'] ] = true;
			update_site_option( 'allowedthemes', $allowed_themes );
		}

		wp_send_json_success( admin_url( 'admin.php?page=themify&child_theme_generated=1' ) );
	}

	/**
	 * Auto generate child theme from parent if it doesn't exist
	 *
	 * @return array|WP_Error
	 */
	private static function maybe_generate_theme() {
		if ( is_child_theme() ) {
			return new WP_Error( 'child-theme-active', __( 'A child theme is already active.', 'themify' ) );
		}

		$template = get_template();
		$themes_dir = get_theme_root( $template );
		if ( ! wp_is_writable( $themes_dir ) ) {
			return new WP_Error( 'unwritable-themes-dir', __( 'Themes directory is not writable.', 'themify' ) );
		}
		$name = self::get_name();
		$dir = trailingslashit( get_theme_root() ) . $name;
		if ( self::exists() ) {
			return compact( 'name', 'dir' );
		}

		if ( ! mkdir( $dir ) ) {
			return new WP_Error( 'directory-fail', __( 'Could not create the child theme directory.', 'themify' ) );
		}

		$parent = wp_get_theme( $template );
		$parent_name = $parent->get( 'Name' );
		$child_theme_name = sprintf( __( '%s Child', 'themify' ), $parent_name );
		$current_user = wp_get_current_user();
		$author = sprintf( '%s (%s)', $current_user->display_name, $current_user->user_email );

		$child_stylesheet_content = <<<MARKER
/*
Theme Name: $child_theme_name
Description: A child theme of $parent_name
Template: $template
Author: $author
*/

/* Woohoo! Let's customize! */

MARKER;

		/* create the child theme style.css */
		Themify_Filesystem::put_contents( trailingslashit( $dir ) . 'style.css', $child_stylesheet_content );

		$functions_content = <<<MARKER
<?php

/* To enable child-theme-scripts.js file, remove the PHP comment below: */
/* remove this line

function custom_child_theme_scripts() {
	wp_enqueue_script( 'themify-child-theme-js', get_stylesheet_directory_uri() . '/child-theme-scripts.js', [ 'jquery' ], '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'custom_child_theme_scripts' );

remove this line too */

/* Custom functions can be added below. */

MARKER;

		/* create the child theme functions.php */
		Themify_Filesystem::put_contents( trailingslashit( $dir ) . 'functions.php', $functions_content );

		/* copy screenshot */
		$screenshot = $parent->get_screenshot( 'relative' );
		if ( $screenshot ) {
			copy( trailingslashit( $parent->get_template_directory() ) . $screenshot, trailingslashit( $dir ) . $screenshot );
		}

		$scripts_content = <<<MARKER
/* custom JavaScript codes can be added here.
 * This file is disabled by default, to enable it open your functions.php file and uncomment the necessary lines.
 */

MARKER;
		Themify_Filesystem::put_contents( trailingslashit( $dir ) . 'child-theme-scripts.js', $scripts_content );

		return [
			'name' => $name,
			'dir' => $dir
		];
	}

	/**
	 * Generates a name to be used for auto generated child themes.
	 *
	 * @return string
	 */
	static function get_name() {
		$template = get_template();
		$new_theme = "{$template}-child";
		if ( is_multisite() ) {
			/* we may want a different child theme per site in the network */
			$new_theme .= '-' . get_current_blog_id();
		}

		return $new_theme;
	}

	/**
	 * Check if current theme has a child theme
	 *
	 * @return bool
	 */
	static function exists() {
		$name = self::get_name();
		$dir = trailingslashit( get_theme_root() ) . $name;
		return Themify_Filesystem::is_dir( $dir );
	}

	static function form() {
		$exists = self::exists();
		?>
		<div class="tf_generate_child_theme">
			<h3><?php _e( 'Child Theme', 'themify' ) ?></h3>
			<p class="success"><?php echo themify_get_icon( 'ti-check','ti' ); ?> <?php _e( 'Child theme has been generated and activated.', 'themify' ); ?></p>
			<p>
				<?php echo $exists ? __( 'It looks like you already have a child theme. Would you like to activate it?', 'themify' ) : __( 'If you need to add PHP functions & template modifications, they should be added in a child theme. When you update the theme, your modifications in the child theme will retain. Would you like to generate a child theme?', 'themify' ) ?>
			</p>
			<p>
				<label><input type="checkbox" checked> <?php _e( 'Import Customize settings (recommended)', 'themify' ); ?></label>
			</p>
			<a href="#" class="themify_button generate-child-theme" data-confirm="<?php echo $exists ? esc_attr__( 'Would you like to activate the child theme?', 'themify' ) : esc_attr__( 'Would you like to create and activate a child theme?', 'themify' ) ?>">
				<?php echo $exists ? __( 'Activate Child Theme', 'themify' ) : __( 'Generate Child Theme', 'themify' ); ?>
			</a>
		</div>
		<?php
	}
}
Themify_Child_Theme_Generator::init();