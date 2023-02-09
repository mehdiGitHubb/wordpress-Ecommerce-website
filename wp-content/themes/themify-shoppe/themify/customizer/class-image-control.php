<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control that injects the markup for an image in an element.
 *
 * @since 1.0.0
 */
class Themify_Image_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_image';

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

		// Image
		$src = isset( $values->src ) ? $values->src : '';
		$id = isset( $values->id ) ? $values->id : '';
		$thumb = wp_get_attachment_image_src( $id );
		$thumb_src = isset( $thumb[0] ) ? $thumb[0] : $src;
				$label = $this->show_label && ! empty( $this->label );
		?>

		<?php if ($label) : ?>
			<span class="customize-control-title themify-control-title themify-suba-toggle"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
				 <?php if ($label) : ?>                    
					<ul class="themify-control-sub-accordeon">
						<li>            
				<?php endif;?>
					<div class="themify-customizer-brick">
						<?php $this->render_image( $values, $this->image_options ); ?>
					</div>


					<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if ($label) : ?>
						</li>
					</ul>
				<?php endif;?>
		<?php
	}
}