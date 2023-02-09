<?php

defined( 'ABSPATH' ) || exit;


/**
 * Class to create a control to set the color of an element.
 *
 * @since 1.0.0
 */
class Themify_Color_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_color';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );
				$label = $this->show_label && ! empty( $this->label );
		?>

		<?php if ($label) : ?>
			<span class="customize-control-title themify-control-title themify-suba-toggle"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
				<?php if($label):?>
					<ul class="themify-control-sub-accordeon">
						<li>
				<?php endif;?>
					<div class="themify-customizer-brick">
							<?php $this->render_color( $values, array(
									'transparent' => false,
									'side_label' => true,
									'color_label' => ( isset( $this->color_label ) && ! empty( $this->color_label ) ) ? $this->color_label : __( 'Color', 'themify' ),
							) ); ?>
					</div>

					<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if($label):?>
						</li>
					</ul>
				<?php endif;?>
		<?php
	}
}