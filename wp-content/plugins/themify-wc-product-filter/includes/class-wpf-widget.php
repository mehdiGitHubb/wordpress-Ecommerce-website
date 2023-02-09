<?php

class WPF_Widget extends WP_Widget {
	
	///////////////////////////////////////////
	// Feature Posts
	///////////////////////////////////////////
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'themify-wpf', 'description' => __('Display Themify Products Filter Form.', 'wpf') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'themify-wpf' );

		/* Create the widget. */
		parent::__construct( 'themify-wpf', __( 'Themify Products Filter', 'wpf' ), $widget_ops, $control_ops );

	}

	///////////////////////////////////////////
	// Widget
	///////////////////////////////////////////
	function widget( $args, $instance ) {

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		/* Before widget (defined by themes). */
		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] , $title , $args['after_title'];
		}

		if ( isset( $instance['form'] ) ) {
			echo do_shortcode( '[searchandfilter id="' . $instance['form'] . '"]' );
		}

		/* After widget (defined by themes). */
		echo $args['after_widget'];
	}
	
	
	///////////////////////////////////////////
	// Update
	///////////////////////////////////////////
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['form'] = $new_instance['form'];

		return $instance;
	}
	
	///////////////////////////////////////////
	// Form
	///////////////////////////////////////////
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => '',
			'form' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$options = WPF_Options::get_option( WPF::get_instance()->get_plugin_name(), WPF::get_instance()->get_version() )->get();
		?>
		<p>
                   
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e('Title:', 'wpf'); ?></label><br />
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" type="text" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>"><?php _e( 'Form', 'wpf' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>" name="<?php  echo esc_attr( $this->get_field_name( 'form' ) ); ?>">
				<?php
				if ( ! empty( $options ) ) {
					foreach ( $options as $key => $form ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $instance['form'] ); ?>><?php echo esc_html( empty( $form['data']['name'] ) ? $key : $form['data']['name'] ); ?></option>
					<?php }
				}
				?>
			</select>
		</p>
		<?php
	}

	public static function register() {
		register_widget( __CLASS__ );
	}
}
add_action( 'widgets_init', array( 'WPF_Widget', 'register' ) );