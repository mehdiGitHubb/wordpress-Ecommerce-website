<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to output the beginning of a sub-accordion. Must have a Themify_Sub_Accordion_End match.
 *
 * @since 1.0.0
 */
class Themify_Sub_Accordion_Start extends WP_Customize_Control {

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'themify_subaccordion_start';

	public $accordion_id;

	/**
	 * @param WP_Customize_Manager $manager
	 * @param string               $id
	 * @param array                $args
	 * @param array                $options
	 */
	function __construct( $manager, $id, $args = array(), $options = array() ) {
		parent::__construct( $manager, $id, $args );
		$this->accordion_slug = sanitize_title_with_dashes( $this->label );
	}

	/**
	 * Display the font dropdown.
	 */
	function render_content() {
		?>
		<a href="#" class="themify-suba-toggle"><?php echo esc_html( $this->label ); ?></a>
		<!-- Accordion Start -->
		<ul class="themify-subaccordion" data-accordion="themify-accordion-<?php echo esc_attr( $this->accordion_id ); ?>">
		</ul>
		<!-- Accordion End -->
		<?php
	}

}

/**
 * Class to output the end of a sub-accordion. Must have a Themify_Sub_Accordion_Start match.
 *
 * @since 1.0.0
 */
class Themify_Sub_Accordion_End extends WP_Customize_Control {

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'themify_subaccordion_end';

	/**
	 * @param WP_Customize_Manager $manager
	 * @param string               $id
	 * @param array                $args
	 * @param array                $options
	 */
	function __construct( $manager, $id, $args = array(), $options = array() ) {
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Display the accordion closing tag.
	 */
	function render_content() {
	}

}