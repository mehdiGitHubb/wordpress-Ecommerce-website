<?php
/**
 * Themify Compatibility Code
 *
 * @package Themify
 */

/**
 * Sensei LMS
 * @link https://woocommerce.com/products/sensei/
 */
class Themify_Compat_sensei {

	static function init() {
		global $woothemes_sensei;

		add_theme_support( 'sensei' );
		remove_action('sensei_before_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper' ), 10 );
		remove_action( 'sensei_after_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper_end' ), 10 );
		add_action( 'sensei_before_main_content', array( __CLASS__, 'sensei_before_main_content' ), 1 );
		add_action( 'sensei_after_main_content', array( __CLASS__, 'sensei_after_main_content' ), 100 );
	}

	public static function sensei_before_main_content() {
		?>
		<!-- layout -->
		<div id="layout" class="pagewidth tf_box tf_clearfix">
			<?php themify_content_before(); // Hook ?>
			<!-- content -->
			<main id="content" class="tf_box tf_clearfix">
			<?php themify_content_start(); // Hook
	}

	public static function sensei_after_main_content() {
			themify_content_end(); // Hook 
			?>
			</main>
			<!-- /#content -->
		<?php
		themify_content_after(); // Hook 
		themify_get_sidebar();
		?>
		</div><!-- /#layout -->
		<?php
	}
}