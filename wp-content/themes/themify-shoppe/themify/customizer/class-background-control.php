<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control to set a background on an element.
 *
 * @since 1.0.0
 */
class Themify_Background_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_background';

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

		// Disable
		$noimage = isset( $values->noimage ) ? $values->noimage : '';
		// Fixed Background
		$fixed_bg_value = isset( $values->fixedbg ) ? $values->fixedbg : '';

		// Style Dropdown
		$styles = array(
			'' => '',
			'repeat' => __( 'Repeat All', 'themify' ),
			'repeat-x' => __( 'Repeat Horizontal', 'themify' ),
			'repeat-y' => __( 'Repeat Vertical', 'themify' ),
			'no-repeat' => __( 'No Repeat', 'themify' ),
			'fullcover' => __( 'Fullcover', 'themify' ),
		);
		$current_style = isset( $values->style ) ? $values->style : '';

		// Background position
		$positions = array(
			'' => '',
			'left top'      => __( 'Left Top', 'themify' ),
			'left center'   => __( 'Left Center', 'themify' ),
			'left bottom'   => __( 'Left Bottom', 'themify' ),
			'right top'     => __( 'Right Top', 'themify' ),
			'right center'  => __( 'Right Center', 'themify' ),
			'right bottom'  => __( 'Right Bottom', 'themify' ),
			'center top'    => __( 'Center Top', 'themify' ),
			'center center' => __( 'Center Center', 'themify' ),
			'center bottom' => __( 'Center Bottom', 'themify' ),
		);
		$current_position = isset( $values->position ) ? $values->position : '';
				$label = $this->show_label && ! empty( $this->label );
		?>

		<?php if ( $label ) : ?>
			<span class="customize-control-title themify-control-title themify-suba-toggle"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
				<?php if ($label) : ?>                    
					<ul class="themify-control-sub-accordeon"><li>            
				<?php endif;?>
					<div class="themify-customizer-brick">
							<!-- Background Image -->
							<?php $this->render_image( $values, array(
									'image_label' => __( 'Background Image', 'themify' ),
							) ); ?>

							<!-- Background Attachment or Size -->
							<div class="custom-select background-style">
									<select class="image-style">
											<?php foreach ( $styles as $style => $label ) : ?>
													<option value="<?php echo esc_attr( $style ); ?>" <?php selected( $current_style, $style ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
									</select>
							</div>

							<!-- Background Position -->
							<div class="custom-select background-position">
									<select class="position-style">
											<?php foreach ( $positions as $position => $label ) : ?>
													<option value="<?php echo esc_attr( $position ); ?>" <?php selected( $current_position, $position ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
									</select>
							</div>

							<div class="fixed-bg">
								<?php $fixed_bg = $this->id . '_fixed_bg'; ?>
								<input id="<?php echo esc_attr( $fixed_bg ); ?>" type="checkbox" <?php checked( $fixed_bg_value, 'fixed' ); ?> value="fixed"/>
								<label for="<?php echo esc_attr( $fixed_bg ); ?>">
										<?php _e( 'Fixed Background Attachment', 'themify' ); ?>
								</label>
							</div>

							<!-- No Background Image-->

							<div class="no-image">
									<?php $noimage_id = $this->id . '_noimage'; ?>
									<input id="<?php echo esc_attr( $noimage_id ); ?>" type="checkbox" class="disable-control" <?php checked( $noimage, 'noimage' ); ?> value="noimage"/>
									<label for="<?php echo esc_attr( $noimage_id ); ?>">
											<?php _e( 'No Background Image', 'themify' ); ?>
									</label>
							</div>

					</div>

					<div class="themify-customizer-brick">
							<?php $this->render_color( $values, array(
									'side_label' => true,
									'color_label' => __( 'Background Color', 'themify' ),
							) ); ?>
					</div>

					<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if ($label) : ?>
						</li>
					</ul>
				<?php endif;?>
		<?php
	}
}