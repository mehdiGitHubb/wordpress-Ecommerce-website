<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to create a control to accept CSS rules and preview them instantly.
 *
 * @since 1.0.0
 */

class Themify_CustomCSS_Control extends Themify_Control{

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = 'themify_customcss';

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content(){
		$v = $this->value();
		$values = json_decode($v, true);
		wp_enqueue_script('json2');
		// Custom CSS
		if (is_array($values) && isset($values['css'])){
			$css = preg_replace('/(\{|\;)(\s*?)([a-z]+)/', '$1$3', $values['css']);
			$css = str_replace(array('{', '}', ';', '\\[', '\\]'), array("{\n  ", "}\n", ";\n", '[', ']'), $css);
		} else{
			$css = $v;
		}
		?>
		<?php if ($this->show_label && !empty($this->label)) :?>
			<span class="customize-control-title themify-control-title"><?php echo esc_html($this->label); ?></span>
		<?php endif; ?>

		<div class="themify-customizer-brick">
			<a class="themify-expand ti-new-window"></a>
			<a class="close-custom-css-expanded tf_close"></a>
			<textarea <?php $this->link(); ?> data-value="<?php echo esc_attr(base64_encode($css)) ?>" class="customcss <?php echo esc_attr($this->type); ?>_control themify-customizer-value-field tf_scrollbar" rows="20"></textarea>
		</div>
		<?php
	}

}
