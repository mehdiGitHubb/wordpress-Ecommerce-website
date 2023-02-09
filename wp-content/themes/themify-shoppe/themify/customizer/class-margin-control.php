<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control to set the margin of an element.
 *
 * @since 1.0.0
 */
class Themify_Margin_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_margin';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );

		// Same for all
		$same = isset( $values->same ) ? $values->same : 'same';

		// Units
		$current_unit = isset( $values->unit ) ? $values->unit : 'px';
		$units = array( 'px', '%', 'em' );

		if ( 'themify_padding' == $this->type ) {
			$property = __( 'Padding', 'themify' );
			$dimension_type = 'padding';
			// Sides
			$sides = array(
				'top'    => __( 'Padding Top', 'themify' ),
				'right'  => __( 'Padding Right', 'themify' ),
				'bottom' => __( 'Padding Bottom', 'themify' ),
				'left'   => __( 'Padding Left', 'themify' ),
			);
			$apply_to_all = __( 'Apply to all padding', 'themify' );
		} else {
			$property = __( 'Margin', 'themify' );
			$dimension_type = 'margin';
			// Sides
			$sides = array(
				'top'    => __( 'Margin Top', 'themify' ),
				'right'  => __( 'Margin Right', 'themify' ),
				'bottom' => __( 'Margin Bottom', 'themify' ),
				'left'   => __( 'Margin Left', 'themify' ),
			);
			$apply_to_all = __( 'Apply to all margin', 'themify' );
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
					<?php
					$first = true;
					foreach ( $sides as $side => $side_label ) : ?>
							<div class="themify-customizer-brick <?php if ( $first ) : echo 'useforall'; else : echo 'component'; endif; ?>">

									<!-- Margin/Padding Width -->
									<?php
									// Check width
									if ( 'same' == $same ) {
											$width = isset( $values->width ) ? $values->width : '';
									} else {
											$width = isset( $values->{$side} ) && isset( $values->{$side}->width ) ? $values->{$side}->width : '';
									}
									$id = $this->id . '_' . $dimension_type . '_' . $side;
									?>
									<?php if ( 'margin' == $dimension_type ) : ?>
									<div class="auto-prop-combo js-hide-<?php echo esc_attr( $side ); ?> hcollapse">
											<?php endif; ?>

											<input type="text" class="dimension-width <?php echo esc_attr( $dimension_type ); ?>-width" data-side="<?php echo esc_attr( $side ); ?>" value="<?php echo esc_attr( $width ); ?>" id="<?php echo esc_attr( $id ); ?>" />
											<div class="custom-select">
													<select class="dimension-unit <?php echo esc_attr( $dimension_type ); ?>-unit" data-side="<?php echo esc_attr( $side ); ?>">
															<?php foreach ( $units as $unit ) : ?>
																	<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $unit, $current_unit ); ?>><?php echo esc_html( $unit ); ?></option>
															<?php endforeach; ?>
													</select>
											</div>

									<?php if ( 'padding' == $dimension_type ) : ?>
											<label for="<?php echo esc_attr( $id ); ?>" class="dimension-row-label <?php if ( $first ) : echo 'same-label'; endif; ?>" <?php if ( $first ) : echo 'data-same="' . esc_attr( $property ) . '" data-notsame="' . esc_attr( $side_label ) . '"'; endif; ?>><?php echo esc_html( $side_label ); ?></label>
									<?php endif; ?>

									<?php if ( 'margin' == $dimension_type ) : ?>

									</div>

											<span class="auto-prop-label">
													<?php
													// CSS property value: auto
													$auto = isset( $values->{$side} ) && isset( $values->{$side}->auto ) ? $values->{$side}->auto : '';
													$auto_id = $this->id . '_' . $side . '_auto';
													?>
													<label for="<?php echo esc_attr( $id ); ?>" class="dimension-row-label <?php if ( $first ) : echo 'same-label'; endif; ?>" <?php if ( $first ) : echo 'data-same="' . esc_attr( $property ) . '" data-notsame="' . esc_attr( $side_label ) . '"'; endif; ?>><?php echo esc_html( $side_label ); ?></label>
													<input id="<?php echo esc_attr( $auto_id ); ?>" type="checkbox" class="auto-prop" <?php checked( $auto, 'auto' ); ?> value="auto" data-hide="js-hide-<?php echo esc_attr( $side ); ?>" data-side="<?php echo esc_attr( $side ); ?>"/>
													<label for="<?php echo esc_attr( $auto_id ); ?>">
															<?php _e( 'Auto', 'themify' ); ?>
													</label>
									</span>
									<?php endif; ?>

							</div>

					<?php
					$first = false;
					endforeach; ?>

					<div class="themify-customizer-brick collapse-same">
							<!-- Apply the same settings to all sides -->
							<?php $same_id = $this->id . '_same'; ?>
							<input id="<?php echo esc_attr( $same_id ); ?>" type="checkbox" class="same" <?php checked( $same, 'same' ); ?> value="same"/>
							<label for="<?php echo esc_attr( $same_id ); ?>">
									<?php echo esc_html( $apply_to_all ); ?>
							</label>
					</div>

					<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if ($label) : ?>
						</li>
					</ul>
				<?php endif;?>
		<?php
	}
}