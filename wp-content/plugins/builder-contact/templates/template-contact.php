<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Contact
 *
 * Access original fields: $args['mod_settings']
*/
$fields_default = array(
	'mod_title_contact' => '',
	'layout_contact' => 'style1',
	'field_name_label' => empty($args['mod_settings']['field_name_label']) && !empty($args['mod_settings']['field_name_placeholder']) ? '' : __('Name', 'builder-contact'),
	'field_name_placeholder' => '',
	'field_email_label' => empty($args['mod_settings']['field_email_label']) && !empty($args['mod_settings']['field_email_placeholder']) ? '' : __('Email', 'builder-contact'),
	'field_email_placeholder' => '',
	'field_subject_label' => empty($args['mod_settings']['field_subject_label']) && !empty($args['mod_settings']['field_subject_placeholder']) ? '' : __('Subject', 'builder-contact'),
	'field_subject_placeholder' => '',
	'field_recipients_label' => __('Recipient', 'builder-contact'),
	'gdpr' => '',
	'gdpr_label' => __('I consent to my submitted data being collected and stored', 'builder-contact'),
	'field_captcha_label' => __('Captcha', 'builder-contact'),
	'field_extra' => '{ "fields": [] }',
	'field_order' => '{}',
	'field_message_label' => empty($args['mod_settings']['field_message_label']) && !empty($args['mod_settings']['field_message_placeholder']) ? '' : __('Message', 'builder-contact'),
	'field_message_placeholder' => '',
	'field_sendcopy_label' => __('Send Copy', 'builder-contact'),
	'field_send_label' => __('Send', 'builder-contact'),
	'field_send_align' => 'left',
	'animation_effect' => '',
	'css_class_contact' => '',
	'field_message_active' => 'yes',
	'field_message_require' => '',
	'field_subject_active' => '',
	'field_subject_require' => '',
	'field_name_require' => '',
	'field_email_require' => '',
	'field_email_active' => 'yes',
	'field_name_active' => 'yes',
	'field_sendcopy_active' => '',
	'field_captcha_active' => '',
	'field_optin_active' => '',
	'field_optin_label' => __( 'Subscribe to my newsletter.', 'builder-contact' ),
	'provider' => '', // Optin service provider
	'nw'=>'',
	'name_icon' => '',
	'email_icon' => '',
	'subject_icon' => '',
	'message_icon' => '',
);

$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$field_extra = is_string($fields_args['field_extra'])?json_decode( $fields_args['field_extra'], true ):$fields_args['field_extra'];
$field_order = is_string($fields_args['field_order'])?json_decode( $fields_args['field_order'], true ):$fields_args['field_order'];

    $container_class = apply_filters('themify_builder_module_classes', array(
    'module','module-'.$args['mod_name'], $args['module_ID'], 'contact-' . $fields_args['layout_contact'], $fields_args['css_class_contact']
		), $args['mod_name'], $args['module_ID'], $fields_args);
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}

$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	'id' => $args['module_ID'],
'class' => implode(' ',$container_class),
)), $fields_args, $args['mod_name'], $args['module_ID']);

/* whether selective recipients is active, this shows a list of potential recipients for the email */
$selective_recipients = ! empty( $fields_args['send_to_admins'] ) && $fields_args['send_to_admins'] === 'true' && $fields_args['user_role'] === 'sr';
$orders = array();
if ( 'yes' === $fields_args['field_name_active'] ) {
    $orders['name']=0;
}
if ( 'yes' === $fields_args['field_email_active'] ) {
    $orders['email']=1;
}
if ( 'yes' === $fields_args['field_subject_active'] ) {
    $orders['subject']=2;
}
if ( $selective_recipients ) {
    $orders['recipients'] = 3;
}
if ( 'yes' === $fields_args['field_message_active'] ) {
    $orders['message']=4;
}

foreach($orders as $k=>$v){
    $orders[$k]=isset($field_order['field_'.$k.'_label'])?(int)$field_order['field_'.$k.'_label']:0;
}
if(!empty($field_extra['fields'])){
    foreach( $field_extra['fields'] as $i => $field ){
		$orders[ 'extra_' . $i ] = (int) ( isset( $field['label'], $field_order[ $field['label'] ] ) ? $field_order[ $field['label'] ] : ( isset( $field['order'] ) ? $field['order'] : 0 ) );
    }
}
$field_order=null;
$isAnimated=$fields_args['layout_contact']==='animated-label';
asort($orders,SORT_NUMERIC);
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
$is_inline_edit_supported=method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
?>
<!-- module contact -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php 
		$container_props=$container_class=null;
		if($is_inline_edit_supported===true){
			echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_contact');
		}
		elseif ($fields_args['mod_title_bar_chart'] !== ''){
			echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title_contact'], $fields_args) , $fields_args['after_title'];
		}
		do_action('themify_builder_before_template_content_render'); 
	?>

	<form class="builder-contact"
		id="<?php echo $args['module_ID']; ?>-form"
		method="post"
		data-post-id="<?php esc_attr_e( $args['builder_id'] ); ?>"
		data-element-id="<?php esc_attr_e( str_replace( 'tb_', '', $args['module_ID'] ) ); ?>"
		data-orig-id="<?php esc_attr_e( get_the_ID() ); ?>"
	>
    <div class="contact-message"></div>
	<div class="builder-contact-fields tf_rel">
	<?php foreach($orders as $k=>$i):?>
	    <?php if ( $k==='name' || $k==='email' || $k==='subject' || $k==='message' || $k === 'recipients' ) :
			if ( $k === 'recipients' && ! $selective_recipients ) {
				continue;
			}
			$label=$fields_args['field_'.$k.'_label'];
			$required = $k === 'recipients' || 'yes' === $fields_args["field_{$k}_active"] && 'yes' === $fields_args["field_{$k}_require"];
			$placeholder=$isAnimated===false?$fields_args['field_'.$k.'_placeholder']:' ';
		?>
		    <div class="builder-contact-field builder-contact-field-<?php echo $k,($k==='message'?' builder-contact-textarea-field':' builder-contact-text-field')?>">
			    <label class="control-label" for="<?php echo $args['module_ID']; ?>-contact-<?php echo $k?>">
					<?php if ( ! empty( $fields_args[ $k . '_icon' ] ) ) : ?>
						<em><?php echo themify_get_icon( $fields_args[ $k . '_icon' ] ); ?></em>
					<?php endif; ?>
					<span class="tb-label-span"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('field_'.$k.'_label'); }?>><?php if ($label!== ''): ?><?php echo $label; ?> </span><?php if ( $required ) : ?><span class="required">*</span><?php endif; endif; ?>
				</label>
			    <div class="control-input tf_rel">
				    <?php if ( $k === 'recipients' ) : ?>

						<?php if ( $fields_args['sr_display'] === 'select' ) : ?><select name="contact-recipients" id="<?php echo $args['module_ID']; ?>-contact-recipients"><?php endif; ?>
						<?php foreach( $fields_args['sr'] as $i => $recipient ) :
							if ( empty( $recipient['email'] ) ) {
								continue;
							} else if ( empty( $recipient['label'] ) ) {
								$recipient['label'] = $recipient['email'];
							}
						?>
							<?php if ( $fields_args['sr_display'] === 'radio' ) : ?>
								<label><input type="radio" name="contact-recipients" value="<?php echo $i; ?>" required><?php echo esc_html( $recipient['label'] ); ?></label>
							<?php else : ?>
								<option value="<?php echo $i; ?>" required><?php echo esc_html( $recipient['label'] ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if ( $fields_args['sr_display'] === 'select' ) : ?></select><?php endif; ?>

				    <?php elseif ( $k === 'message' ) : ?>
					    <textarea name="contact-message" placeholder="<?php echo $placeholder ?>" id="<?php echo $args['module_ID']; ?>-contact-message" class="form-control"></textarea>
				    <?php else:?>
					    <input type="<?php echo $k === 'email' ? 'email' : 'text'; ?>" name="contact-<?php echo $k?>" placeholder="<?php echo $placeholder; ?>" id="<?php echo $args['module_ID']; ?>-contact-<?php echo $k?>" value="" class="form-control" <?php echo $required===true ? 'required' : '' ?>>
				    <?php endif;?>
				    <?php if($isAnimated===true):?>
					    <span class="tb_contact_label">
						    <span class="tb-label-span"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('field_'.$k.'_label'); }?>><?php if ($label !== ''): ?><?php echo $label; ?> </span><?php if ( $required ) : ?><span class="required">*</span><?php endif; endif; ?>
					    </span>
				    <?php endif;?>
			    </div>
		</div>
	    <?php else:?>
		    <?php 
		    $index = str_replace('extra_','',$k);
		    if(!isset($field_extra['fields'][$index])){
			continue;
		    }
		    $field = $field_extra['fields'][$index];
		    $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
			$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
		    $required = isset( $field['required'] ) && true === $field['required']?' required':'';
			$inputName='field_extra_'.$index;
			$inputId='field_extra_'.$args['module_ID'] . '_' . $index;
			?>
		    <div class="builder-contact-field builder-contact-field-extra<?php if($field['type']==='tel'):?> builder-contact-text-field<?php endif;?> builder-contact-<?php echo $field['type']; ?>-field">
				
				<label class="control-label" for="<?php echo $inputId ?>">
					<?php if ( ! empty( $field['icon'] ) ) : ?>
						<em><?php echo themify_get_icon( $field['icon'] ); ?></em>
					<?php endif; ?>

					<?php echo $field['label']; ?>
					<?php if( 'static' !== $field['type'] ):?>
						<input type="hidden" name="field_extra_name_<?php echo $index; ?>" value="<?php echo $field['label']; ?>">
					<?php endif;
					if( $required!==''): ?>
						<span class="required">*</span>
					<?php endif; ?>
				</label>
				<div class="control-input tf_rel">
					<?php if( 'textarea' === $field['type'] ): ?>
						<textarea name="<?php echo $inputName; ?>" id="<?php echo $inputId ?>" placeholder="<?php echo $isAnimated===false?esc_html($field['value']):' '; ?>" class="form-control"<?php echo $required ?>></textarea>
					<?php elseif( 'text' === $field['type'] ||  'tel' === $field['type'] || 'upload' === $field['type'] || $field['type'] === 'email' || $field['type'] === 'number' ) : ?>
						<input type="<?php echo $field['type']==='upload'?'file':$field['type']?>" name="<?php echo $inputName; ?>" id="<?php echo $inputId?>" placeholder="<?php echo ($isAnimated===false &&  'upload' !== $field['type'])?esc_html($field['value']):' '; ?>" class="form-control"<?php echo $required ?>>
					<?php elseif( 'static' === $field['type'] ): ?>
						<?php echo do_shortcode( $field['value'] ); ?>
					<?php elseif(!empty($field['value'])):?>
						<?php if( 'radio' === $field['type'] || 'checkbox' === $field['type'] ): ?>
							<?php 
							$count =count($field['value']);
							foreach( $field['value'] as $value ): ?>
								<label>
									<input type="<?php echo $field['type']?>" name="<?php echo $inputName,($field['type']==='checkbox'?'[]':'')?>" value="<?php esc_attr_e($value); ?>" class="form-control"<?php echo ($required!=='' && ('radio' === $field['type'] || $count===1))?$required:''?>><?php echo $value; ?>
								</label>
							<?php endforeach; ?>
						<?php elseif( 'select' === $field['type'] ): ?>
							<select id="<?php echo $inputId ?>" name="<?php echo $inputName; ?>" class="form-control tf_scrollbar"<?php echo $required ?>>
								<?php if($required===''):?>
									<option value=""></option>
								<?php endif;?>
								<?php foreach( $field['value'] as $value ): ?>
									<option value="<?php esc_attr_e($value); ?>"> <?php echo strip_tags($value); ?> </option>
								<?php endforeach; ?>
							</select>
						<?php endif; ?>
					<?php endif; ?>

					<?php if($isAnimated===true && ('text' === $field['type'] || 'tel' === $field['type'] || 'textarea' === $field['type'])):?>
						<span class="tb_contact_label">
							<?php echo $field['label']; 
							if( $required!==''): ?>
								<span class="required">*</span>
							<?php endif; ?>
						</span>
					<?php endif;?>
				</div>
		    </div>
	    <?php endif;?>

	<?php endforeach;?>
	    <?php if ( 'yes' === $fields_args['field_sendcopy_active'] ) : ?>
		<div class="builder-contact-field builder-contact-field-sendcopy">
		    <div class="control-label">
				<div class="control-input tf_rel">
					<label class="send-copy">
						<input type="checkbox" name="contact-sendcopy" id="<?php echo $args['module_ID']; ?>-sendcopy" value="1">
						<span<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('field_sendcopy_label'); }?>><?php echo $fields_args['field_sendcopy_label']; ?></span>
					</label>
				</div>
		    </div>
		</div>
	    <?php endif; ?>

		<?php if ( $fields_args['field_optin_active'] ) : ?>
			<?php
			if ( ! class_exists( 'Builder_Optin_Service' ) ){
				include_once( THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php' );
			}
			$optin_instance =Builder_Optin_Service::get_providers( $fields_args['provider'] );
			$optin_inputs='';
			if($optin_instance){
				foreach ( $optin_instance->get_options() as $provider_field ) :
					if ( isset( $provider_field['id'], $fields_args[ $provider_field['id'] ] ) ){
						$optin_inputs .= '<input type="hidden" name="contact-optin-'.$provider_field['id'].'" value="'.esc_attr( $fields_args[ $provider_field['id'] ] ).'" />';
					}
				endforeach;
			}
			unset($optin_instance);
			if ( ''!==$optin_inputs ) : ?>
				<div class="builder-contact-field builder-contact-field-optin">
					<div class="control-label">
						<div class="control-input tf_rel">
							<input type="hidden" name="contact-optin-provider" value="<?php echo esc_attr( $fields_args['provider'] ); ?>">
							<?php echo $optin_inputs; ?>
							<label class="optin">
								<input type="checkbox" name="contact-optin" id="<?php echo $args['module_ID']; ?>-optin" value="1"> <?php echo $fields_args['field_optin_label']; ?>
							</label>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( 'accept' === $fields_args['gdpr'] ) : ?>
			<div class="builder-contact-field builder-contact-field-gdpr">
				<div class="control-label">
					<div class="control-input tf_rel">
						<label class="field-gdpr">
							<input type="checkbox" name="gdpr" value="1" required>
							<span<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('gdpr_label'); }?>><?php echo $fields_args['gdpr_label']; ?></span>
							<span class="required">*</span>
						</label>
					</div>
				</div>
			</div>
		<?php endif; ?>

	    <?php if ( 'yes' === $fields_args['field_captcha_active'] && Builder_Contact::get_option('public_key') != '' && Builder_Contact::get_option('private_key') != '') : ?>
		<?php $recaptcha_version = Builder_Contact::get_option('version','v2'); ?>
		<div class="builder-contact-field builder-contact-field-captcha">
			<?php if('v3' !== $recaptcha_version) : ?>
				<label class="control-label">
					<span<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('field_captcha_label'); }?>><?php echo $fields_args['field_captcha_label']; ?></span>
					<span class="required">*</span>
				</label>
			<?php endif; ?>
		    <div class="control-input tf_rel">
				<div class="themify_captcha_field <?php if ( 'v2' === $recaptcha_version ) :?>g-recaptcha<?php endif; ?>" data-sitekey="<?php echo esc_attr(Builder_Contact::get_option('public_key')); ?>" data-ver="<?php esc_attr_e($recaptcha_version); ?>"></div>
		    </div>
		</div>
	    <?php endif; ?>
	    <div class="builder-contact-field builder-contact-field-send control-input tf_text<?php echo $fields_args['field_send_align'][0];?> tf_clear tf_rel">
			<button type="submit" class="btn btn-primary"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('field_send_label'); }?>>
				<?php if(Themify_Builder::$frontedit_active===false):?><span class="tf_loader"></span><?php endif;?>
				<span class="tf_submit_icon"><?php if ( ! empty( $fields_args['send_icon'] ) ) echo themify_get_icon( $fields_args['send_icon'] ); ?></span> 
				<?php echo $fields_args['field_send_label']; ?>
			</button>
	    </div>
	</div>
    </form>

    <?php do_action('themify_builder_after_template_content_render'); ?>
</div>
<!-- /module contact -->
