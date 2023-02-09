<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Login
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-login.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
	'mod_title' => '',
	'content_text' => '',
	'alignment' => '',
	'remember_me_display' => 'show',
	'redirect_to' => add_query_arg( 'login_success', 1 ),
	'fail_action' => 'r',
	'redirect_fail' => '',
	'msg_fail' => '',
	'icon_username' => '',
	'icon_password' => '',
	'icon_remember' => '',
	'icon_log_in' => '',
	'icon_forgotten_password' => '',
	'label_username' => '',
	'label_password' => '',
	'label_remember' => '',
	'label_log_in' => '',
	'label_forgotten_password' => '',
	'css' => '',
	'lostpasswordform_redirect_to' => '',
	'lostpasswordform_icon_username' => '',
	'lostpasswordform_icon_reset' => '',
	'lostpasswordform_label_username' => '',
	'lostpasswordform_label_reset' => '',
	'animation_effect' => ''
);
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
unset( $args['mod_settings'] );
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$fields_args['alignment']=$fields_args['alignment']==='' || 'left'=== $fields_args['alignment']?'':('center'=== $fields_args['alignment']?'tb_login_c':'tf_right');
$container_class = apply_filters( 'themify_builder_module_classes', array(
	'module', 
	'module-' . $mod_name, 
	$element_id, 
	$fields_args['css']
	), $mod_name, $element_id, $fields_args );

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters( 'themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
	'class' => implode( ' ', $container_class),
)), $fields_args, $mod_name, $element_id );
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
?>
<!-- module login -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<div classss="tb_login_wrap <?php echo $fields_args['alignment'];?>">
	<?php $container_props=$container_class=null;
		echo Themify_Builder_Component_Module::get_module_title($fields_args);
	?>

	<?php if ( is_user_logged_in() && ! Themify_Builder_Model::is_front_builder_activate() ) : ?>

		<?php echo apply_filters( 'themify_builder_module_content', $fields_args['content_text'] ); ?>

	<?php else : ?>
		<?php if ( $fields_args['fail_action'] === 'm' && isset( $_GET['login_error'] ) ) : ?>
			<div class="tb_login_error"<?php self::add_inline_edit_fields('msg_fail')?>><?php echo esc_html( $fields_args['msg_fail'] ) ?></div>
		<?php endif; ?>

		<form class="tb_login_form tf_clearfix tf_box tf_clear" name="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ) ?>" method="post">
			<p class="tb_login_username">
				<label>
					<?php if ( $fields_args['icon_username'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['icon_username'] ); ?></em><?php endif; ?>
					<span class="tb_login_username_text"<?php self::add_inline_edit_fields('label_username')?>><?php echo esc_html( $fields_args['label_username'] ) ?></span>
					<input type="text" name="log" required="required">
				</label>
			</p>
			<p class="tb_login_password">
				<label>
					<?php if ( $fields_args['icon_password'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['icon_password'] ); ?></em><?php endif; ?>
					<span class="tb_login_password_text"<?php self::add_inline_edit_fields('label_password')?>><?php echo esc_html( $fields_args['label_password'] ) ?></span>
					<input type="password" name="pwd" required="required">
				</label>
			</p>
			<div class="tb_login_links tf_box tf_clear">
				<?php if ( $fields_args['icon_forgotten_password'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['icon_forgotten_password'] ); ?></em><?php endif; ?>
				<a href="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>"<?php self::add_inline_edit_fields('label_forgotten_password')?>><?php echo esc_html( $fields_args['label_forgotten_password'] ); ?></a>
			</div>

			<?php
			/** This action is documented in wp-login.php */
			do_action( 'login_form' );
			?>

			<?php if ( $fields_args['remember_me_display'] === 'show' ) : ?>
			<p class="tb_login_remember tf_left tf_box">
				<label>
					<input name="rememberme" type="checkbox" value="forever"> 
					<?php if ( $fields_args['icon_remember'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['icon_remember'] ); ?></em><?php endif; ?>
					<span class="tb_login_remember_text"<?php self::add_inline_edit_fields('label_remember')?>><?php echo esc_html( $fields_args['label_remember'] ); ?></span>
				</label>
			</p>
			<?php endif; ?>
			<p class="tb_login_submit tf_right">
				<button name="wp-submit"<?php self::add_inline_edit_fields('label_log_in')?>>
					<?php if ( $fields_args['icon_log_in'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['icon_log_in'] ); ?></em><?php endif; ?>
					<?php echo esc_html( $fields_args['label_log_in'] ) ?>
				</button>
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $fields_args['redirect_to'] ) ?>">
				<input type="hidden" name="tb_login" value="1">

				<?php if ( $fields_args['fail_action'] === 'c' && ! empty( $fields_args['redirect_fail'] ) ) : ?>
					<input type="hidden" name="tb_redirect_fail" value="<?php echo esc_url( $fields_args['redirect_fail'] ) ?>">
				<?php elseif ( $fields_args['fail_action'] === 'm' ) : ?>
					<input type="hidden" name="tb_redirect_fail" value="<?php echo esc_url( add_query_arg( 'login_error', 1 ) ) ?>">
				<?php endif; ?>
			</p>

		</form>

		<form class="tb_lostpassword_form tf_clearfix tf_box tf_clear" name="lostpasswordform" action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post" style="display:none">
			<p class="tb_lostpassword_username">
				<label>
					<?php if ( $fields_args['lostpasswordform_icon_username'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['lostpasswordform_icon_username'] ); ?></em><?php endif; ?>
					<span class="tb_lostpassword_username_text"<?php self::add_inline_edit_fields('lostpasswordform_label_username')?>><?php echo esc_html( $fields_args['lostpasswordform_label_username'] ) ?></span>
					<input type="text" name="user_login" required="required">
				</label>
			</p>

			<?php
			/** This action is documented in wp-login.php */
			do_action( 'lostpassword_form' );
			?>

			<?php if ( ! empty( $fields_args['lostpasswordform_redirect_to'] ) ) : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $fields_args['lostpasswordform_redirect_to'] ); ?>">
			<?php endif; ?>
			<p class="tb_lostpassword_submit tf_right">
				<button<?php self::add_inline_edit_fields('lostpasswordform_label_reset')?>><?php if ( $fields_args['lostpasswordform_icon_reset'] !== '' ) : ?><em><?php echo themify_get_icon( $fields_args['lostpasswordform_icon_reset'] ); ?></em><?php endif; ?> <?php echo esc_html( $fields_args['lostpasswordform_label_reset'] ) ?></button>
			</p>

			<div class="tb_login_links tf_left tf_box">
				<a href="<?php echo esc_url( site_url( 'wp-login.php' ) ); ?>"<?php self::add_inline_edit_fields('label_log_in')?>><?php echo esc_html( $fields_args['label_log_in'] ); ?></a>
			</div>
		</form>

	<?php endif; ?>
    </div>
</div><!-- /module login -->