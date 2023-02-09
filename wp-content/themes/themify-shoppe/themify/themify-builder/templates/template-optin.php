<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Newsletter
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-optin.php.
 *
 * Access original fields: $mod_settings
 * @author Themify
 */

$fields_default = array(
	'mod_title' => '',
	'provider' => 'mailchimp',
	'layout' => 'inline_block',
	'label_firstname' => '',
	'fn_placeholder' => '',
	'fname_hide' => 0,
	'default_fname' => __( 'John', 'themify' ),
	'lname_hide' => 0,
	'label_lastname' => '',
	'ln_placeholder' => '',
	'default_lname' => __( 'Doe', 'themify' ),
	'label_email' => '',
	'email_placeholder' => '',
	'label_submit' => '',
	'button_icon' => '',
	'success_action' => 's2',
	'redirect_to' => '',
	'message' => '',
	'captcha' => '',
	'gdpr' => '',
	'gdpr_label' => '',
	'css' => '',
	'animation_effect' => '',
);
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
unset( $args['mod_settings'] );
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

$instance = Builder_Optin_Service::get_providers( $fields_args['provider'] );
$container_class = apply_filters( 'themify_builder_module_classes', array(
	'module', 
	'module-' . $mod_name,
	$element_id, 
	$fields_args['css'],
	$fields_args['layout']
	), $mod_name, $element_id, $fields_args
);
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters( 'themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
	'id' => $element_id,
	'class' => implode(' ', $container_class ),
)), $fields_args, $mod_name, $element_id );
$args=null;
$icon =$fields_args['button_icon']? sprintf( '<em>%s</em>', themify_get_icon($fields_args['button_icon'] )):'';
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
if ( 'on' === $fields_args['captcha']){
    $captcha_site_key = Themify_Builder_Model::getReCaptchaOption( 'public_key');
    $captcha_secret_key = Themify_Builder_Model::getReCaptchaOption( 'private_key');
}
?>
<!-- module optin -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null; 
	?>
	<?php if ($instance ):?>
	    <?php
		echo Themify_Builder_Component_Module::get_module_title($fields_args);
		if ( is_wp_error( ( $error = $instance->validate_data( $fields_args ) ) ) ) :
		    if ( current_user_can( 'manage_options' ) ) {
			    echo $error->get_error_message();
		    }
	    ?>
	    <?php else: ?>
	    <form class="tb_optin_form" name="tb_optin" method="post"
		    action="<?php esc_attr_e( admin_url( 'admin-ajax.php' ) ); ?>"
		    data-success="<?php esc_attr_e( $fields_args['success_action'] ); ?>"
	    >
		    <input type="hidden" name="action" value="tb_optin_subscribe">
		    <input type="hidden" name="tb_optin_redirect" value="<?php esc_attr_e( $fields_args['redirect_to'] ); ?>">
		    <input type="hidden" name="tb_optin_provider" value="<?php esc_attr_e( $fields_args['provider'] ); ?>">

		    <?php
		    foreach ( $instance->get_options() as $provider_field ) :
			    if ( isset( $provider_field['id'] ) && isset( $fields_args[ $provider_field['id'] ] ) ) : ?>
				    <input type="hidden" name="tb_optin_<?php echo $provider_field['id']; ?>" value="<?php esc_attr_e( $fields_args[ $provider_field['id'] ] ); ?>" />
			    <?php endif;
		    endforeach;
		    ?>

		    <?php if ( $fields_args['fname_hide'] ) : ?>
			    <input type="hidden" name="tb_optin_fname" value="<?php esc_attr_e( $fields_args['default_fname'] ); ?>">
		    <?php else : ?>
			    <div class="tb_optin_fname">
				    <label class="tb_optin_fname_text"<?php self::add_inline_edit_fields('label_firstname')?>>
                        <?php echo !empty($fields_args['label_firstname'])?esc_html( $fields_args['label_firstname']):'<span class="screen-reader-text">'.__('First name','themify').'</span>'; ?>
                        <input type="text" name="tb_optin_fname" required="required" class="tb_optin_input"<?php echo !empty($fields_args['fn_placeholder'])?' placeholder="'.esc_attr($fields_args['fn_placeholder']).'"':''; ?>>
                    </label>

			    </div>
		    <?php endif; ?>

		    <?php if ( $fields_args['lname_hide'] ) : ?>
			    <input type="hidden" name="tb_optin_lname" value="<?php esc_attr_e( $fields_args['default_lname'] ); ?>">
		    <?php else : ?>
			    <div class="tb_optin_lname">
				    <label class="tb_optin_lname_text"<?php self::add_inline_edit_fields('label_lastname')?>>
                        <?php echo !empty($fields_args['label_lastname'])?esc_html( $fields_args['label_lastname']):'<span class="screen-reader-text">'.__('Last name','themify').'</span>'; ?>
                        <input type="text" name="tb_optin_lname" required="required" class="tb_optin_input"<?php echo !empty($fields_args['ln_placeholder'])?' placeholder="'.esc_attr($fields_args['ln_placeholder']).'"':''; ?>>
                    </label>
			    </div>
		    <?php endif; ?>

		    <div class="tb_optin_email">
			    <label class="tb_optin_email_text"<?php self::add_inline_edit_fields('label_email')?>>
                    <?php echo !empty($fields_args['label_email'])?esc_html( $fields_args['label_email']):'<span class="screen-reader-text">'.__('Email','themify').'</span>'; ?>
                    <input type="email" name="tb_optin_email" required="required" class="tb_optin_input"<?php echo !empty($fields_args['email_placeholder'])?' placeholder="'.esc_attr($fields_args['email_placeholder']).'"':''; ?>>
                </label>
		    </div>

			<?php if ( $fields_args['gdpr'] === 'on' ) : ?>
				<div class="tb_optin_gdpr">
					<label class="tb_optin_gdpr_text"<?php self::add_inline_edit_fields('gdpr_label')?>>
						<input type="checkbox" name="tb_optin_gdpr" required="required">
						<?php echo $fields_args['gdpr_label']; ?>
					</label>
				</div>
			<?php endif; ?>

            <?php if (!empty($captcha_site_key) && !empty($captcha_secret_key) ) : ?>
                <?php $recaptcha_version=Themify_Builder_Model::getReCaptchaOption( 'version','v2');?>
                <div class="tb_optin_captcha">
                    <div class="themify_captcha_field <?php if ( 'v2' === $recaptcha_version ) :?>g-recaptcha<?php endif; ?>" data-sitekey="<?php esc_attr_e($captcha_site_key); ?>" data-ver="<?php esc_attr_e($recaptcha_version); ?>"></div>
                </div>
            <?php endif; ?>

		    <div class="tb_optin_submit">
			    <button>
					<?php if( $icon!==''):?>
						<?php echo $icon?>
					<?php endif;?>
                     <span<?php self::add_inline_edit_fields('label_submit')?>><?php echo esc_html( $fields_args['label_submit'] ) ?></span>
                </button>
		    </div>
	    </form>
	    <div class="tb_optin_success_message tb_text_wrap" style="display:none"<?php self::add_inline_edit_fields('message',true,true)?>>
		    <?php echo $fields_args['message']!==''?apply_filters( 'themify_builder_module_content', $fields_args['message'] ):''; ?>
	    </div>
	<?php endif; ?>
    <?php endif; ?>
</div><!-- /module optin -->
<?php unset($instance)?>

