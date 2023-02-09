<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control to set the site title and logo image or remove it.
 *
 * @since 1.0.0
 */
class Themify_Logo_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_logo';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );
		wp_enqueue_media();

		// Mode
		$mode = isset( $values->mode ) ? $values->mode : 'text';

		// Site Link
		$link = isset( $values->link ) ? $values->link : '';
		$label = $this->show_label && ! empty( $this->label );
		?>

		<?php if ($label) : ?>
			<span class="customize-control-title themify-control-title themify-suba-toggle"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
				<?php if ($label) : ?>                    
					<ul class="themify-control-sub-accordeon">
						<li>            
				<?php endif;?>
						<!-- Site Logo Mode Selector -->
						<div class="themify-customizer-brick mode-switcher logo-modes">
								<label><input type="radio" value="text" class="logo-mode" name="logo_mode<?php echo $this->accordion_id; ?>" <?php checked( $mode, 'text' ); ?> /><?php _e( 'Site Title', 'themify' ); ?></label>
								<label><input type="radio" value="image" class="logo-mode" name="logo_mode<?php echo $this->accordion_id; ?>" <?php checked( $mode, 'image' ); ?>/><?php _e( 'Logo Image', 'themify' ); ?></label>
								<label><input type="radio" value="none" class="logo-mode" name="logo_mode<?php echo $this->accordion_id; ?>" <?php checked( $mode, 'none' ); ?>/><?php _e( 'None', 'themify' ); ?></label>
						</div>

						<!-- Site Logo Text Mode -->
						<div class="logo-mode-wrap logo-text-mode">
								<label><?php _e( 'Site Title', 'themify' ); ?><input type="text" class="site-name" value="<?php echo esc_attr( get_bloginfo('name') ); ?>"/></label>
						</div>

						<div class="logo-mode-wrap logo-text-mode">
								<?php $this->render_fonts( $values ); ?>

								<div class="themify-customizer-brick">
										<?php $this->render_color( $values, array( 'transparent' => false, 'side_label' => true ) ); ?>
								</div>
						</div>

						<!-- Site Logo Image Mode -->
						<div class="logo-mode-wrap logo-image-mode">
								<div class="themify-customizer-brick">
										<?php $this->render_image( $values, array( 'show_size_fields' => true ) ); ?>
								</div>
						</div>

						<div class="logo-mode-wrap logo-text-mode logo-image-mode">
								<p><label><?php _e( 'Custom Site Logo Link', 'themify' ); ?><input type="text" class="site-link" value="<?php echo esc_url( $link ); ?>"/></label></p>
						</div>

						<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if ($label) : ?>
						</li>
					</ul>
				<?php endif;?>
		<?php
	}

}