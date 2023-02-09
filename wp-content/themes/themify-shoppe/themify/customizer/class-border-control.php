<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control and specify the border width, style and color of an element.
 *
 * @since 1.0.0
 */
class Themify_Border_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_border';

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

		// Sides
		$sides = array(
			'top'    => __( 'Border Top', 'themify' ),
			'right'  => __( 'Border Right', 'themify' ),
			'bottom' => __( 'Border Bottom', 'themify' ),
			'left'   => __( 'Border Left', 'themify' ),
		);

		// Style
		$styles = array(
			'' => '',
			'solid'  => __( 'Solid', 'themify' ),
			'dotted' => __( 'Dotted', 'themify' ),
			'dashed' => __( 'Dashed', 'themify' ),
			'double' => __( 'Double', 'themify' ),
			'none' => __( 'None', 'themify' ),
		);
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
				<div class="wide-label <?php if ( $first ) : echo 'same-label'; endif; ?>" <?php if ( $first ) : echo 'data-same="' . esc_attr__( 'Border', 'themify' ) . '" data-notsame="' . esc_attr( $side_label ) . '"'; endif; ?>><?php echo
					$side_label;
					?></div>

				<!-- Border Style -->
				<div class="custom-select">
					<select class="border-style" data-side="<?php echo esc_attr( $side ); ?>">
						<?php foreach ( $styles as $style => $label ) : ?>
							<?php
							// Check style
							if ( 'same' === $same ) {
								$current_style = isset( $values->style ) ? $values->style : '';
							} else {
								$current_style = isset( $values->{$side} ) && isset( $values->{$side}->style ) ? $values->{$side}->style : '';
							}
							?>
							<option value="<?php echo esc_attr( $style ); ?>" <?php selected( $current_style, $style ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Border Color -->
				<div class="color-picker">
					<?php
					// Check color
					if ( 'same' === $same ) {
						$color = isset( $values->color ) ? $values->color : '';
						$opacity = isset( $values->opacity ) ? $values->opacity : '';
					} else {
						$color = isset( $values->{$side} ) && isset( $values->{$side}->color ) ? $values->{$side}->color : '';
						$opacity = isset( $values->{$side} ) && isset( $values->{$side}->opacity ) ? $values->{$side}->opacity : '';
					}
					?>
					<input type="text" class="color-select" data-side="<?php echo esc_attr( $side ); ?>" value="<?php echo esc_attr( $color ); ?>" data-opacity="<?php echo esc_attr( $opacity ); ?>"/>
					<a class="remove-color tf_close" href="#" <?php echo ( '' != $color || '' != $opacity ) ? 'style="display:inline"' : ''; ?> data-side="<?php echo esc_attr( $side ); ?>"></a>
				</div>

				<!-- Border Width -->
				<?php
				// Check width
				if ( 'same' == $same ) {
					$width = isset( $values->style ) ? $values->width : '';
				} else {
					$width = isset( $values->{$side} ) && isset( $values->{$side}->width ) ? $values->{$side}->width : '';
				}
				?>
				<input type="text" class="dimension-width border-width" data-side="<?php echo esc_attr( $side ); ?>" value="<?php echo esc_attr( $width ); ?>" />
				<label class="dimension-unit-label"><?php _e( 'px', 'themify' ); ?></label>
			</div>
		<?php
		$first = false;
		endforeach; ?>

		<div class="themify-customizer-brick collapse-same">
			<!-- Apply the same settings to all sides -->
			<?php $same_id = $this->id . '_same'; ?>
			<input id="<?php echo esc_attr( $same_id ); ?>" type="checkbox" class="same" <?php checked( $same, 'same' ); ?> value="same"/>
			<label for="<?php echo esc_attr( $same_id ); ?>">
				<?php _e( 'Apply to all borders', 'themify' ); ?>
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