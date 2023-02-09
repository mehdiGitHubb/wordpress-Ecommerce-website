<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create tool buttons like Reset, Import and Export.
 *
 * @since 3.0.5
 */
class Themify_Tools_Control extends WP_Customize_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_tools';

	/**
	 * Render the control's content.
	 *
	 * @since 3.0.5
	 */
	public function render_content() {
		?>

		<span class="customize-control-title themify-control-title">
			<a href="#" class="tool_wrapper clearall" data-sitename="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-tagline="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>">
				<span class="clearall-icon tf_close"></span>
				<?php echo __( 'Clear All', 'themify' ); ?>
			</a>
			<span class="tool_wrapper customize-import">
				<i class="ti-import customize-import-icon"></i>
					<?php echo themify_get_uploader('customizer-import', array(
								'label'		=> __('Import', 'themify'),
								'preset'	=> false,
								'preview'   => false,
								'tomedia'	=> false,
								'topost'	=> '',
								'fields'	=> '',
								'featured'	=> '',
								'message'	=> '',
								'fallback'	=> '',
								'dragfiles' => false,
								'confirm'	=> __('Import will overwrite all settings and configurations. Press OK to continue, Cancel to stop.', 'themify'),
								'medialib'	=> false,
								'formats'	=> 'zip,txt',
								'type'		=> '',
								'action'    => 'themify_plupload_customizer',
							)
						); ?>
			</span>
			<a class="tool_wrapper customize-export"  href="<?php echo esc_attr(add_query_arg( 'export', 'themify-customizer', wp_nonce_url(admin_url('customize.php'), 'themify_customizer_export_nonce') )); ?>" target="_blank">
				<span class="ti-export customize-export-icon"></span>
				<?php echo __( 'Export', 'themify' ); ?>
			</a>
		</span>

		<input <?php $this->link(); ?> value="" type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control"/>
		<?php
	}
}