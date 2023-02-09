<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a select element for web safe fonts and Google Fonts.
 *
 * @since 1.0.0
 */
class Themify_Text_Decoration_Control extends Themify_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_font';

	/**
	 * Display the font control.
	 *
	 * @since 1.0.0
	 */
	function render_content() {
		$v = $this->value();
		$values = json_decode( $v );
		wp_enqueue_script( 'json2' );

		// Font styles and decoration
		$font_italic = ! empty( $values->italic ) ? $values->italic : '';
		$font_normal = ! empty( $values->normal ) ? $values->normal : '';
		$font_weight = ! empty( $values->bold ) ? $values->bold : '';
		$font_underline = ! empty( $values->underline ) ? $values->underline : '';
		$font_linethrough = ! empty( $values->linethrough ) ? $values->linethrough : '';
		$font_nostyle = ! empty( $values->nostyle ) ? $values->nostyle : '';
				$label = $this->show_label && ! empty( $this->label );
		?>

		<?php if ($label) : ?>
			<span class="customize-control-title themify-control-title themify-suba-toggle"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
				<?php if ($label) : ?>                    
					<ul class="themify-control-sub-accordeon">
						<li>            
				<?php endif;?>
					<!-- TEXT STYLE & DECORATION -->
					<div class="themify_font_style themify-customizer-brick">
							<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_italic, 'italic' ) ); ?>" data-style="italic" data-title="<?php esc_attr_e( 'Italic', 'themify' ) ?>"><?php _e( 'i', 'themify' ); ?></button>
							<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_normal, 'normal' ) ); ?>" data-style="normal" data-title="<?php esc_attr_e( 'Normal', 'themify' ) ?>"><?php _e( 'N', 'themify' ); ?></button>
							<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_weight, 'bold' ) ); ?>" data-style="bold" data-title="<?php esc_attr_e( 'Bold', 'themify' ) ?>"><?php _e( 'B', 'themify' ); ?></button>
							<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_underline, 'underline' ) ); ?>" data-style="underline" data-title="<?php esc_attr_e( 'Underline', 'themify' ) ?>"><?php _e( 'U', 'themify' ); ?></button>
							<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_linethrough, 'linethrough' ) ); ?>" data-style="linethrough" data-title="<?php esc_attr_e( 'Line Through', 'themify' ) ?>"><?php _e( 'S', 'themify' ); ?></button>
							<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_nostyle, 'nostyle' ) ); ?>" data-style="nostyle" data-title="<?php esc_attr_e( 'No Styles', 'themify' ) ?>"><?php _e( '&times;', 'themify' ); ?></button>
					</div>
					<input <?php $this->link(); ?> value='<?php echo esc_attr( $v ); ?>' type="hidden" class="<?php echo esc_attr( $this->type ); ?>_control themify-customizer-value-field"/>
				<?php if ($label) : ?>
						</li>
					</ul>
				<?php endif;?>
	<?php
	}

}
