<?php
/*
Plugin Name:  Builder Contact
Plugin URI:   https://themify.me/addons/contact
Version:      3.0.3
Author:       Themify
Author URI:   https://themify.me
Description:  Simple contact form. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:  builder-contact
Domain Path:  /languages
Compatibility: 7.0.0
*/

defined('ABSPATH') or die('-1');

class Builder_Contact {

    public static $url;
    public static $version;
    private static $from_name;

    /**
     * Init Builder Contact
     */
    public static function init(){
        self::constants();
        add_action( 'init', array( __CLASS__, 'i18n' ) );
        add_action('themify_builder_setup_modules', array(__CLASS__, 'register_module'));

        if (is_admin()) {
            add_filter('plugin_row_meta', array(__CLASS__, 'themify_plugin_meta'), 10, 2);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'action_links'));
            add_action('wp_ajax_builder_contact_send', array(__CLASS__, 'contact_send'));
            add_action('wp_ajax_nopriv_builder_contact_send', array(__CLASS__, 'contact_send'));
            add_filter('manage_contact_messages_posts_columns', array(__CLASS__, 'set_custom_columns'));
            add_action('manage_contact_messages_posts_custom_column', array(__CLASS__, 'custom_contact_messages_columns'), 10, 2);
        } 
	add_action('themify_builder_active_enqueue', array(__CLASS__, 'admin_enqueue'));
    }


    private static function constants(){
        $data = get_file_data(__FILE__, array('Version'));
        self::$version = $data[0];
        self::$url = trailingslashit(plugin_dir_url(__FILE__));
    }

    public static function themify_plugin_meta($links, $file){
        if (plugin_basename(__FILE__) === $file) {
            $row_meta = array(
                'changelogs' => '<a href="' . esc_url('https://themify.me/changelogs/') . basename(dirname($file)) . '.txt" target="_blank" aria-label="' . esc_attr__('Plugin Changelogs', 'themify') . '">' . esc_html__('View Changelogs', 'themify') . '</a>'
            );

            return array_merge($links, $row_meta);
        }
        return (array)$links;
    }

    public static function action_links($links){
        if (is_plugin_active('themify-updater/themify-updater.php')) {
            $tlinks = array(
                '<a href="' . admin_url('index.php?page=themify-license') . '">' . __('Themify License', 'themify') . '</a>',
            );
        } else {
            $tlinks = array(
                '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '">' . __('Themify Updater', 'themify') . '</a>',
            );
        }
        return array_merge($links, $tlinks);
    }

    public static function i18n(){
        load_plugin_textdomain( 'builder-contact', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        self::create_post_type();
    }

    public static function admin_enqueue() {
        wp_enqueue_script( 'jquery-ui-sortable' );
        themify_enque_script('builder-contact-admin-scripts', self::$url . 'assets/admin.js',self::$version, array('themify-builder-app-js'));
        wp_localize_script('builder-contact-admin-scripts', 'tb_contact_l10n', array(
            'pl' => __('Placeholder', 'builder-contact'),
            'req' => __('Required', 'builder-contact'),
            'captcha' => Builder_Contact::get_option('public_key')?'':sprintf(__('Requires Captcha keys entered at: <a target="_blank" href="%s">reCAPTCHA settings</a>.', 'builder-contact'), admin_url('admin.php?page=builder-contact')),
			'admin_css' => self::$url . 'assets/admin.css',
            'v' => self::$version,
            'field_name' => __('Field Name', 'builder-contact'),
            'static_text' => __('Enter text or HTML here', 'builder-contact'),
            'add_option' => __('+ Add Option', 'builder-contact'),
			'sendcopy_sub' => __('Append text to subject', 'builder-contact'),
			'display' => __('Display', 'builder-contact'),
			'label' => __('Label', 'builder-contact'),
			'radio' => __('Radio', 'builder-contact'),
			'select' => __('Select', 'builder-contact'),
			'sr_info' => __('This field is only displayed if you enable the Send To option in the module and set it to Selective Recipients.', 'builder-contact'),
            'types' => array(
                'text' => __('Text', 'builder-contact'),
                'email' => __('Email', 'builder-contact'),
                'number' => __('Number', 'builder-contact'),
				'tel'=> __('Telephone', 'builder-contact'),
                'textarea' => __('Textarea', 'builder-contact'),
                'upload' => __('Upload File', 'builder-contact'),
                'radio' => __('Radio', 'builder-contact'),
                'select' => __('Select', 'builder-contact'),
                'checkbox' => __('Checkbox', 'builder-contact'),
                'static' => __('Static Text', 'builder-contact'),
            ),
            'default_template' => self::get_default_template(),
        ));
    }

    public static function register_module() {
        $dir=trailingslashit(plugin_dir_path(__FILE__));
        if(method_exists('Themify_Builder_Model', 'add_module')){
            Themify_Builder_Model::add_module($dir . 'modules/module-contact.php' );
        }
        else{
            Themify_Builder_Model::register_directory('templates', $dir . 'templates');
            Themify_Builder_Model::register_directory('modules', $dir . 'modules');
        }
    }

	/**
	 * Retrieve saved settings for a module
	 *
	 * @return array|0
	 */
	private static function get_element_settings( $post_id, $element_id ) {
		global $ThemifyBuilder;
		$data = $ThemifyBuilder->get_flat_modules_list( $post_id );
		if ( ! empty( $data ) ) {
			foreach ( $data as $module ) {
				if ( isset( $module['element_id'], $module['mod_settings'] ) && $module['element_id'] === $element_id ) {
					return $module['mod_settings'];
				}
			}
		}

		return 0;
	}

    public static function contact_send() {

		if ( ! isset( $_POST['post_id'], $_POST['element_id'] ) ) {
			return;
		}
		global $post;
		$post = get_post((int) $_POST['orig_id']);
		if ( $post ) {
			setup_postdata( $post );
		}
		$module_settings = self::get_element_settings( (int) $_POST['post_id'], sanitize_text_field( $_POST['element_id'] ) );
		$module_settings = apply_filters( 'themify_builder_module_render_vars', array('mod_settings'=>$module_settings) );
		$module_settings=$module_settings['mod_settings'];
		if ( empty( $module_settings ) ) {
			return;
		}
		$module_settings = wp_parse_args( $module_settings, array(
			'mail_contact' => get_option('admin_email'),
			'specify_from_address' => '',
			'send_to_admins' => '',
			'specify_email_address' => '',
			'bcc_mail_contact' => '',
			'bcc_mail' => '',
			'default_subject' => '',
			'success_url' => '',
			'post_type' => '',
			'post_author' => '',
			'success_message_text' => __('Message sent. Thank you.', 'builder-contact'),
			'contact_sent_from' => 'enable',
			'include_name_mail' => '',
			'field_sendcopy_subject' => '',
			'field_name_active' => 'yes',
			'field_name_require' => '',
			'field_email_active' => 'yes',
			'field_email_require' => '',
			'field_subject_active' => '',
			'field_subject_require' => '',
			'field_captcha_active' => '',
			'field_sendcopy_active' => '',
			'field_optin_active' => '',
			'auto_respond' => '',
			'auto_respond_subject' => __( 'Message sent. Thank you.', 'builder-contact' ),
			'auto_respond_message' => '',
			'user_role' => 'admin',
			'field_extra' => '{ "fields": [] }',
			'custom_template' => 'off',
			'include_name_mail' => '',
		) );

		foreach ( array( 'name', 'email', 'subject' ) as $field ) {
			if ( (isset($module_settings["field_{$field}_active"]) && $module_settings["field_{$field}_active"] === 'yes') && (isset($module_settings["field_{$field}_require"]) && $module_settings["field_{$field}_require"] === 'yes') ) {
				if ( empty( $_POST["contact-{$field}"] ) ) {
					wp_send_json_error( array( 'error' => __( 'Please fill in the required data.', 'builder-contact' ) ) );
				}
			}
		}

		$name = isset( $_POST['contact-name'] ) ? sanitize_text_field( stripslashes($_POST['contact-name']) ) : '';
		$email = isset( $_POST['contact-email'] ) ? sanitize_email( $_POST['contact-email'] ) : '';
		$subject = ! empty( $_POST['contact-subject'] ) ? sanitize_text_field( stripslashes($_POST['contact-subject']) ) : $module_settings['default_subject'];

		if ( $module_settings['field_email_active'] === 'yes' && ! empty( $_POST['contact-email'] ) && ! is_email( $email ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid Email address!', 'builder-contact' ) ) );
		}

		$extra_fields = is_string($module_settings['field_extra'])?json_decode( $module_settings['field_extra'], true ):$module_settings['field_extra'];
		if ( ! is_array( $extra_fields ) ) {
			$extra_fields = array();
		}

		// ensure "required" extra fields are submitted
		foreach ( $extra_fields['fields'] as $key => $field ) {
			if ( isset( $field['required'] ) && $field['required'] ) {
				if (
					( $field['type'] === 'upload' && empty( $_FILES["field_extra_{$key}"] ) )
					|| ( $field['type'] !== 'upload' && empty( $_POST["field_extra_{$key}"] ) )
				) {
					wp_send_json_error( array( 'error' => __( 'Please fill in the required data.', 'builder-contact' ) ) );
				}
			}
			if ( $field['type'] === 'email' && ! empty( $_POST["field_extra_{$key}"] ) && ! sanitize_email( $_POST["field_extra_{$key}"] ) ) {
				wp_send_json_error( array( 'error' => __( 'Invalid Email address is sent.', 'builder-contact' ) ) );
			}
		}

		/* reCAPTCHA validation */
		$keys = array('public'=>self::get_option('public_key'),'private'=>self::get_option('private_key'));
		if ( $module_settings['field_captcha_active'] === 'yes' && $keys['public'] != '' && $keys['private'] != '') {
			if ( isset( $_POST['g-recaptcha-response'] ) ) {
				$response = wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $keys['private'] . '&response=' . $_POST['g-recaptcha-response']);
				if (isset($response['body'])) {
					$response = json_decode($response['body'], true);
					if ( ! true == $response['success'] ) {
						wp_send_json_error( array( 'error' => __( 'Please verify captcha.', 'builder-contact' ) ) );
					}
				} else {
					wp_send_json_error( array( 'error' => __( 'Trouble verifying captcha. Please try again.', 'builder-contact' ) ) );
				}
			} else {
				wp_send_json_error( array( 'error' => __( 'Trouble verifying captcha. Please try again.', 'builder-contact' ) ) );
			}
		}

		if ( $module_settings['send_to_admins'] === 'true' ) {
			if ( 'sr' === $module_settings['user_role'] ) {
				$recipients = $module_settings['sr'];
				$selected_recipient = (int) $_POST['contact-recipients'];
				if ( empty( $module_settings['sr'][ $selected_recipient ]['email'] ) ) {
					/* just a fail safe, this should not happen */
					wp_send_json_error( array( 'error' => __( 'Something has gone horribly wrong. Please notify the website administrator.', 'builder-contact' ) ) );
				}
				$recipients = [ $module_settings['sr'][ $selected_recipient ]['email'] ];
			} else if ( 'author' === $module_settings['user_role'] ) {
				$authors_email = get_the_author_meta( 'user_email', get_post_field ( 'post_author', (int) $_POST['post_id'] ) );
				$recipients = ''!==$authors_email ? array($authors_email):array(get_option('admin_email'));
			} else {
				$recipients = array(get_option('admin_email'));
			}
		} else {
			$recipients = array_map( 'trim', explode( ',', $module_settings['mail_contact'] ) );
		}
		$active_bcc = $module_settings['bcc_mail'];
		$bcc_recipients = array_map( 'trim', explode( ',', $module_settings['bcc_mail_contact'] ) );

		$active_specify_from_address = $module_settings['specify_from_address'];
		$specify_email_address = trim( $module_settings['specify_email_address'] );

		$subject = apply_filters('builder_contact_subject', $subject);
		if ( empty( $subject ) ) {
			$subject = get_bloginfo( 'name' );
		}

		self::$from_name = $name;
		$headers = array();
		if ( 'enable' === $active_specify_from_address ) {
			$headers = array('From: ' . $specify_email_address, ' Reply-To: ' . $name . ' <' . $email . '>');
		} else if ('' !== $email){
			$headers = array('From: ' . $name . ' <' . $email . '>', ' Reply-To: ' . $name . ' <' . $email . '>');
		}
		add_filter('wp_mail_from_name', array(__CLASS__, 'set_from_name'));
		// add the email address to message body

		$custom_template = $module_settings['custom_template'] === 'enable';

		$message = isset( $_POST['contact-message'] ) ? wpautop( sanitize_textarea_field( stripslashes($_POST['contact-message']) ) ) : '';
		if ( isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] != '' ) {
			$referer = $_SERVER['HTTP_REFERER'];
		} else {
			$referer = get_site_url();
		}

		if ( $custom_template ) {
			$template_vars = array(
				'%name%' => $name,
				'%email%' => $email,
				'%subject%' => $subject,
				'%message%' => $message,
				'%referer%' => $referer,
			);
			$template = empty( $module_settings['template'] ) ? self::get_default_template() : $module_settings['template'];
		} else {
			$body = '';

			if ( '' !== $name && '' === $email ) {
				$body = __('From:', 'builder-contact') . ' ' . $name ;
			} elseif ( '' === $name && '' !== $email ) {
				$body .= __('From:', 'builder-contact') . ' '. ' &lt;' . $email . '&gt;' . "<br>" ;
			} elseif ( '' !== $name && '' !== $email ) {
				$body .= __('From:', 'builder-contact') . ' ' . $name . ' &lt;' . $email . '&gt;' . "<br>";
			}
			if ( 'enable' === $module_settings['include_name_mail'] ) {
				$body .= "<br><b>" . __('Name:', 'builder-contact').'</b> ' . $name .'<br>';
				$body .= "<b>" . __('Email:', 'builder-contact').'</b> ' . $email .'<br>';
				$body .= "<b>" . __('Subject:', 'builder-contact').'</b> ' . $subject .'<br>';
			}

			$body .= $message;
		}

		$uploaded_files_path = $uploaded_files_url = array();
		foreach ( $extra_fields['fields'] as $key => $field ) {

			if ( $field['type'] === 'static' ) {
				continue;
			} else if ( $field['type'] === 'upload' ) {
				if ( isset( $_FILES[ "field_extra_{$key}" ] ) && 0 !== $_FILES["field_extra_{$key}"]['size'] ) {
					$file_info = $_FILES["field_extra_{$key}"];
					$upload_file = self::upload_attachment( $file_info );
					if ( is_wp_error( $upload_file ) ) {
						wp_send_json_error( array( 'error' => $upload_file->get_error_message() ) );
					} 
					elseif ( $upload_file ) {
						$uploaded_files_url[ $key ] = $upload_file['url'];
						$uploaded_files_path[ $key ] = $upload_file['file'];
					}
				}
				continue;
			}

			if ( is_array( $_POST[ "field_extra_{$key}" ] ) ) {
				$value = '';
				foreach ( $_POST[ "field_extra_{$key}" ] as $val ) {
					$value .= sanitize_text_field( stripslashes($val) ) . ', ';
				}
				$value = trim( stripslashes( substr( $value, 0, -2 ) ) );
			} else {
				if ( $field['type'] === 'textarea' ) {
					$value = wpautop( sanitize_textarea_field( stripslashes($_POST[ "field_extra_{$key}" ] )) );
				} else if ( $field['type'] === 'email' ) {
					$value = sanitize_email( stripslashes( $_POST[ "field_extra_{$key}" ] ) );
				} else {
					$value = sanitize_text_field( stripslashes($_POST[ "field_extra_{$key}" ]) );
				}
			}
			if ( $custom_template ) {
				$template_vars[ '%' . $field['label'] . '%' ] = $value;
			} else {
				$body .= '<br>';
				$body .= '<b>' . $field['label'] . " :</b><br>" . $value . "<br>";
			}
		}

		add_filter( 'wp_mail_content_type', array( __CLASS__, 'set_content_type' ), 100, 1 );

		if ( $custom_template ) {
			$body = self::template( $template_vars, $template );
		} else {
			if ( 'enable' === $module_settings['contact_sent_from'] ) {
				if ( isset($_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] != '' ) {
					$referer = $_SERVER['HTTP_REFERER'];
				} else {
					$referer = get_site_url();
				}
				$body .= '<br>' . __( 'Sent from:', 'builder-contact' ) . ' ' . $referer . '<br><br>';
			}
		}

		if ( $module_settings['field_sendcopy_active'] === 'yes' && isset( $_POST['contact-sendcopy'] ) && $_POST['contact-sendcopy'] == '1' ) {
			wp_mail( $email, $module_settings['field_sendcopy_subject'] . $subject, $body, $headers, $uploaded_files_path );
		}

		if ( $module_settings['field_optin_active'] && isset( $_POST['contact-optin'] ) && $_POST['contact-optin'] == '1' ) {
			if ( ! class_exists( 'Builder_Optin_Service' ) ){
				include_once( THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php' );
			}
			$provider=sanitize_text_field($_POST['contact-optin-provider']);
			$optin_instance = Builder_Optin_Service::get_providers( $provider );
			if ( $optin_instance ) {
				// collect the data for optin service
				$data = array(
					'email' => $email,
					'fname' => $name,
					'lname' => '',
				);
				foreach ( $_POST as $key => $value ) {
					if ( preg_match( '/^contact-optin-/', $key ) ) {
						$key = preg_replace( '/^contact-optin-/', '', $key );
						$data[ $key ] = sanitize_text_field( trim( stripslashes($value) ) );
					}
				}
				$optin_instance->subscribe( $data );
			}
			unset($optin_instance);
		}

		if ( $module_settings['post_type'] === 'enable' ) {
			$files_links = '';// for add file link to the post
			if ( $uploaded_files_url && ! empty( $uploaded_files_url ) ) {
				$files_links .= '<br>' . __( 'Attachments : ', 'builder-contact' );
				foreach ( $uploaded_files_url as $link ) {
					$files_links .= "<br><a href='" . $link . "'>" . $link . "</a><br>";
				}
			}
			$post_author_id=false;
			if ( $module_settings['post_author'] === 'add' ) {
				$post_author_email = $recipients[0];
				$post_author_id = self::create_new_author( $post_author_email );
			}
			self::send_via_post_type( $subject, $body . $files_links, $post_author_id );
		}
		$auto_respond_sent = false;

		$headerStr = $headers;
		$recipientsArr = $recipients;
		unset( $recipientsArr[0] );
		$recipientsArr = implode(',', $recipientsArr);
		if ( $recipientsArr ) {
			array_push( $headerStr, 'Cc: ' . $recipientsArr );
		}

		if('enable' === $active_bcc){
			array_push($headerStr, 'Bcc: ' . implode(',', $bcc_recipients));
		}
		$sent=wp_mail($recipients[0], $subject, $body, $headerStr, $uploaded_files_path);
        remove_filter('wp_mail_from_name', array(__CLASS__, 'set_from_name'));
		if ($sent) {

			if ( ! $auto_respond_sent && ! empty( $module_settings['auto_respond'] ) && ! empty( $module_settings['auto_respond_message'] ) ) {
				$auto_respond_sent = true;
				$ar_subject = trim( stripslashes( $module_settings['auto_respond_subject'] ) );
				$ar_message = wpautop( trim( stripslashes( $module_settings['auto_respond_message'] ) ) );
                add_filter('wp_mail_from_name', array(__CLASS__, 'set_from_name_site'));
				wp_mail($email, $ar_subject, $ar_message, '');
                remove_filter('wp_mail_from_name', array(__CLASS__, 'set_from_name_site'));
			}
		} else {
			global $ts_mail_errors, $phpmailer;
			if ( ! isset( $ts_mail_errors ) )
				$ts_mail_errors = array();
			if ( !empty( $phpmailer->ErrorInfo ) ) {
				$ts_mail_errors[] = $phpmailer->ErrorInfo;
			}else{
			    $sent=true;
            }
		}
		if ( ! $sent ) {
			ob_start();
			print_r( $ts_mail_errors );
			$mail_error = ob_get_clean();
			$error_message = __( 'There was an error. Please try again.', 'builder-contact' );
			// show email error message to site admins
			if ( current_user_can( 'manage_options' ) ) {
				$error_message .= __( ' Error: ', 'builder-contact' ) . $mail_error;
			}
			wp_send_json_error( array( 'error' => $error_message ) );
		}
		remove_filter('wp_mail_content_type', array(__CLASS__, 'set_content_type'), 100, 1);
		do_action('builder_contact_mail_sent');

		if ( $uploaded_files_url && $module_settings['post_type'] !== 'enable' ) { // delete saved file , if no save in media library
		    foreach ( $uploaded_files_url as $attachment ) {
			unlink( $attachment );
		    }
		}

		wp_send_json_success( array(
			'msg' => $module_settings['success_message_text'],
			'redirect_url' => $module_settings['success_url'],
			'nw'=>!empty($module_settings['nw'])?1:''
		) );
    }

    private static function upload_attachment( $file_info ) {
        if ( ! empty( $file_info ) ) {
            if ( ! $file_info['error'] ) {
                if ( $file_info['size'] <= wp_max_upload_size() ) {
                    $movefile = wp_handle_upload( $file_info, array( 'test_form' => false ) );
                    if ( $movefile && ! isset( $movefile['error'] ) ) {
                        $result = $movefile;
                    } else {
                        return new WP_Error( 'error_filetype', __('WordPress doesn\'t allow this type of uploads.', 'builder-contact' ) );
                    }
                } else {
                    return new WP_Error( 'error_filesize', __('The selected file size is larger than the limit.', 'builder-contact') );
                }
            }
            return $result;
        }
        return false;
    }

    public static function set_from_name($name){
        return self::$from_name;
    }

    public static function set_from_name_site($name) {
        return get_bloginfo();
    }

    private static function create_new_author($email){

        $exists = email_exists($email);
        if (false !== $exists) {
            return $exists;
        }

        $random_password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $random_password, $email);

        return $user_id;
    }

    private static function send_via_post_type($title, $message, $author = false){

        $post_info = array(
            'post_title' => $title,
            'post_type' => 'contact_messages',
            'post_content' => $message
        );

        if (false !== $author) {
            $post_info['post_author'] = $author;
        }
        remove_filter('content_save_pre', 'wp_filter_post_kses', 10);
        return wp_insert_post($post_info);
    }

    private static function create_post_type(){

        return register_post_type('contact_messages',
            array(
                'labels' => array(
                    'name' => __('Builder Contact Submissions', 'builder-contact'),
                    'singular_name' => __('Builder Contact Submission', 'builder-contact'),
                    'all_items' => __('Contact Submissions', 'builder-contact'),
                    'menu_name' => __('Builder Contact', 'builder-contact'),
                ),
                'public' => false,
                'supports' => array('title', 'editor', 'author'),
                'show_ui' => true,
                'show_in_admin_bar' => false
            )
        );
    }

    public static function set_custom_columns($columns){
		
        unset($columns['date'], $columns['author']);
        $columns['sender'] = __('Sender', 'builder-contact');
        $columns['subject'] = __('Subject', 'builder-contact');
        $columns['date'] = __('Date', 'builder-contact');
        return $columns;
    }

    public static function custom_contact_messages_columns($column, $post_id){

        switch ($column) {

            case 'sender' :
                $content_post = get_post($post_id);
                $content = $content_post->post_content;
	            preg_match('/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i', $content, $result);
	            echo (isset($result[0])) ? $result[0] : '';
                break;

            case 'subject' :
                echo get_the_title($post_id);
                break;
        }
    }

    public static function set_content_type($content_type){
        return 'text/html';
    }


    public static function get_option($name, $default = null){
        if(method_exists('Themify_Builder_Model', 'getReCaptchaOption')){
            return Themify_Builder_Model::getReCaptchaOption($name,$default);
        }
        
        //bakward,can be removed in the future logged 28.04.22
        if($name==='version'){
            $contact_key='recapthca_version';
            $builder_key='builder_settings_recaptcha_version';
            $tf_name='setting-recaptcha_version';
        }
        elseif($name==='public_key'){
            $contact_key='recapthca_public_key';
            $builder_key='builder_settings_recaptcha_site_key';
            $tf_name='setting-recaptcha_site_key';
        }
        elseif($name==='private_key'){
            $contact_key='recapthca_private_key';
            $builder_key='builder_settings_recaptcha_secret_key';
            $tf_name='setting-recaptcha_secret_key';
        }
        if(isset($tf_name)){
            $val=themify_builder_get($tf_name, $builder_key,true );
            if(!empty($val)){
                return $val;
            }
        }
        $options = class_exists('Builder_Contact')?get_option('builder_contact'):array();
        return isset($options[$contact_key]) ? $options[$contact_key] : $default;
    }

    private static function template( $arr, $str ) {
            return str_replace( array_keys( $arr ), array_values( $arr ), $str );
    }

    private static function get_default_template() {
            return __( 'From:', 'builder-contact' ) . ' %name% @ %email%<br>' . "\n"
                    . '<b>' . __( 'Subject:', 'builder-contact' ) . '</b> %subject%' . "\n\n"
                    . '%message%' . "\n"
                    . '<br>' . __( 'Sent from:', 'builder-contact' ) . ' %referer%<br>';
    }
}
Builder_Contact::init();