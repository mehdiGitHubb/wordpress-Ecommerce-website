<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control and set the width of an element.
 *
 * @since 1.0.0
 */
class Themify_Width_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_width';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );

		// Units
		$current_unit = isset( $values->unit ) ? $values->unit : 'px';
		$units = array( 'px', '%', 'em' );

		if ( 'themify_height' == $this->type ) {
			$dimension_label = __( 'Height', 'themify' );
			$dimension_type = 'height';
		} else {
			$dimension_label = __( 'Width', 'themify' );
			$dimension_type = 'width';
		}
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

							<div class="auto-prop-combo js-hide hcollapse">
									<!-- Width -->
									<?php
									// Check width
									$value = isset( $values->width ) ? $values->width : '';
									$id = $this->id . '_' . $dimension_type;
									?>
									<input type="text" class="dimension-width" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr( $id ); ?>" />
									<div class="custom-select">
											<select class="dimension-unit <?php echo esc_attr( $dimension_type ); ?>-unit">
													<?php foreach ( $units as $unit ) : ?>
															<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $unit, $current_unit ); ?>><?php echo esc_html( $unit ); ?></option>
													<?php endforeach; ?>
											</select>
									</div>
							</div>

							<span class="auto-prop-label">
									<label for="<?php echo esc_attr( $id ); ?>" class="dimension-row-label"><?php echo esc_html( $dimension_label ); ?></label>

									<?php
									// CSS property value: auto
									$auto = isset( $values->auto ) ? $values->auto : '';
									$auto_id = $this->id . '_auto';
									?>
									<input id="<?php echo esc_attr( $auto_id ); ?>" type="checkbox" class="auto-prop" <?php checked( $auto, 'auto' ); ?> value="auto" data-hide="js-hide"/>
									<label for="<?php echo esc_attr( $auto_id ); ?>">
											<?php _e( 'Auto', 'themify' ); ?>
									</label>
							</span>
					</div>

					<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if ($label) : ?>
						</li>
					</ul>
				<?php endif;?>
		<?php
	}
}