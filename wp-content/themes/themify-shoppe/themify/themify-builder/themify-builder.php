<?php

/**
 * Framework Name: Themify Builder
 * Framework URI: https://themify.me/
 * Description: Page Builder with interactive drag and drop features
 * Version: 1.0
 * Author: Themify
 * Author URI: https://themify.me
 *
 *
 * @package ThemifyBuilder
 * @category Core
 * @author Themify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define builder constant
 */
define('THEMIFY_BUILDER_REGENERATE_CSS', true);
define('THEMIFY_BUILDER_DIR', dirname(__FILE__));
define('THEMIFY_BUILDER_MODULES_DIR', THEMIFY_BUILDER_DIR . '/modules');
define('THEMIFY_BUILDER_TEMPLATES_DIR', THEMIFY_BUILDER_DIR . '/templates');
define('THEMIFY_BUILDER_CLASSES_DIR', THEMIFY_BUILDER_DIR . '/classes');
define('THEMIFY_BUILDER_INCLUDES_DIR', THEMIFY_BUILDER_DIR . '/includes');


// URI Constant
define('THEMIFY_BUILDER_URI', THEMIFY_URI . '/themify-builder');
define('THEMIFY_BUILDER_CSS_MODULES', THEMIFY_BUILDER_URI . '/css/modules/');
define('THEMIFY_BUILDER_JS_MODULES', THEMIFY_BUILDER_URI . '/js/modules/');
/**
 * Include builder class
 */
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-model.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-layouts.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-global-styles.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder.php' );
///////////////////////////////////////////
// Version Getter
///////////////////////////////////////////
if (!function_exists('themify_builder_get')) {

    function themify_builder_get($theme_var, $builder_var = false,$data_only=true) {
	static $is=null;
	if($is===null){
	    $is=themify_is_themify_theme();
	}
        if ($is===true) {
            return themify_get($theme_var,null,$data_only);
        }
        if ($builder_var === false) {
            return false;
        }
        global $post;
        $data = Themify_Builder_Model::get_builder_settings();
        if (isset($data[$builder_var]) && $data[$builder_var] !== '') {
            return $data[$builder_var];
        } else if (is_object($post) && ($val = get_post_meta($post->ID, $builder_var, true)) !== '') {
            return $val;
        }
        return null;
    }

}
if ( ! function_exists( 'themify_builder_check' ) ) {

    function themify_builder_check( $theme_var, $builder_var = false, $data_only = true ) {
		$val = themify_builder_get( $theme_var, $builder_var, $data_only );

		return $val !== null && $val !== '' && $val !== 'off';
    }

}
/**
 * Init themify builder class
 */
add_action('after_setup_theme', 'themify_builder_init', 15);

function themify_builder_init() {
    if ( class_exists( 'Themify_Builder' ) && Themify_Builder_Model::builder_check() ) {
		do_action( 'themify_builder_before_init' );
		global $ThemifyBuilder;
		$ThemifyBuilder = new Themify_Builder();
		add_action( 'init', array( $ThemifyBuilder,'init' ), 0 );
    }
}

if (!function_exists('themify_manage_builder')) {

    /**
     * Builder Settings
     * @param array $data
     * @return string
     * @since 1.2.7
     */
    function themify_manage_builder($data = array()) {
        $data = themify_get_data();
        $pre = 'setting-page_builder_';

        $output='<div data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="enable">';
	Themify_Builder_Component_Module::load_modules('all');	
	$modules=Themify_Builder_Model::$modules;
        foreach ($modules as $k=>$m) {
		$exclude = $pre . 'exc_' . $k;
		$checked = !empty($data[$exclude]) ? 'checked="checked"' : '';
		$output .= '<p><span><input id="builder_module_' . $k. '" type="checkbox" name="' . $exclude . '" value="1" ' . $checked . '/> <label for="builder_module_' . $k . '">' . wp_kses_post(sprintf(__('Disable "%s" module', 'themify'), $m->get_name())) . '</label></span></p>';
	   
        }
	$output.='</div>';
        return $output;
    }

}

if (!function_exists('tb_tools_options')) {

    /**
     * Call and return all tools options HTML
     * @return array posts id
     * @since 4.1.2
     */
    function tb_tools_options() {
        return '<div data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="enable">'.tb_find_and_replace().themify_regenerate_css_files().'</div>';
    }

}

if (!function_exists('themify_regenerate_css_files')) {

    /**
     * Builder Settings
     * @param array $data
     * @return string
     * @since 1.2.7
     */
    function themify_regenerate_css_files($data = array()) {
        $output = '<hr><h4>'.__('Regenerate CSS Files','themify').'</h4>';
        $output .= '<div><label class="label" for="builder-regenerate-css-files">' . __('Regenerate Files', 'themify').themify_help(__('Builder styling are output to the generated CSS files stored in \'wp-content/uploads\' folder. Regenerate files will update all data in the generated files (eg. correct background image paths, etc.).', 'themify')) . '</label><input id="builder-regenerate-css-files" type="button" value="'.__('Regenerate CSS Files','themify').'" class="themify_button"/></div>';
        if(is_multisite()){
            $output.='<br/><label class="pushlabel"><input type="checkbox" value="1" id="tmp_regenerate_all_css"/>'.__('Regenerate CSS in the whole network site','themify').'</label>';
        }
        return $output;
    }

}

if (!function_exists('tb_find_and_replace')) {

    /**
     * Add find and replace string tool to builder setting
     * @param array $data
     * @return string
     * @since 4.1.2
     */
    function tb_find_and_replace($data = array()) {
        $in_progress = '1' === Themify_Storage::get( 'themify_find_and_replace_in_progress' );
        $disabled = $in_progress ? 'disabled="disabled"' : '';
        $value = $in_progress ? __('Replacing ...','themify') : __('Replace','themify');
        $output = '<h4>'.__('Find & Replace','themify').'</h4>';
        $output .= '<p><span class="label">' . __( 'Search for', 'themify' ) . '</span> <input type="text" class="width10" id="original_string" name="original_string" /></p>';
        $output .= '<p><span class="label">' . __( 'Replace to', 'themify' ) .themify_help(__('Use this tool to replace the strings in the Builder data. Warning: Please backup your database before replacing strings, this can not be undone.', 'themify')) . '</span> <input type="text" class="width10" id="replace_string" name="replace_string" /></p>';
        $output .= '<p><span class="pushlabel"><input id="builder-find-and-replace-btn" type="button" name="builder-find-and-replace-btn" '.$disabled.' value="'.$value.'" class="themify_button"/> </span></p>';
        return $output;
    }

}

if (!function_exists('themify_manage_builder_active')) {

    /**
     * Builder Settings
     * @param array $data
     * @return string
     * @since 1.2.7
     */
    function themify_manage_builder_active($data = array()) {
        $pre = 'setting-page_builder_';
        $options = array(
            array('name' => __('Enable', 'themify'), 'value' => 'enable'),
            array('name' => __('Disable', 'themify'), 'value' => 'disable')
        );

        $output = sprintf('<p><span class="label">%s</span><select id="%s" name="%s">%s</select>%s</p>', esc_html__('Themify Builder:', 'themify'), $pre . 'is_active', $pre . 'is_active', themify_options_module($options, $pre . 'is_active'), sprintf('<small class="pushlabel" data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="disable">%s</small>'
                        , esc_html__('WARNING: When Builder is disabled, all Builder content/layout will not appear. They will re-appear once Builder is enabled.', 'themify'))
        );

        if (Themify_Builder_Model::builder_check()) {

            $output .= '<div data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="enable">';
            $excludes =array('tbuilder_layout', 'tbuilder_layout_part','tglobal_style');
            foreach( $GLOBALS['ThemifyBuilder']->builder_post_types_support() as $v ) {
                if(in_array($v,$excludes,true)){
                    continue;
                }
                $key = $pre.'disable_'.$v;
                $output .= sprintf('<p><label class="pushlabel" for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>', $key, $key, $key, checked('on', themify_builder_get($key, 'builder_disable_tb_'.$v), false), sprintf(__('Disable Builder on "%s" type', 'themify'), $v));
            }
            

            $output .= sprintf('<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',$pre . 'disable_shortcuts', $pre . 'disable_shortcuts', $pre . 'disable_shortcuts', checked('on', themify_builder_get($pre . 'disable_shortcuts', 'builder_disable_shortcuts'), false), wp_kses_post(__('Disable Builder shortcuts (eg. disable shortcut like Cmd+S = save)', 'themify'))
            );

            // Disable WP editor
            $output .= sprintf('<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>', $pre . 'disable_wp_editor', $pre . 'disable_wp_editor', $pre . 'disable_wp_editor', checked('on', themify_builder_get($pre . 'disable_wp_editor', 'builder_disable_wp_editor'), false), wp_kses_post(__('Disable WordPress editor when Builder is in use', 'themify'))
            );

		/**
		 * Scroll to Offset
		 */
		$output .=
            '<p>'.
                '<span class="label">' . __('ScrollTo Position', 'themify') .themify_help(__('Enter the top position where row anchor should scrollTo', 'themify')) . '</span>'.
                '<input type="number" class="width4" min="0" max=5000" step="1" name="setting-scrollto_offset" value="' . themify_get( 'setting-scrollto_offset' ) . '" /> ' .
            '</p>';
		$output .=
            '<p>'.
                '<span class="label">' . __('ScrollTo Speed', 'themify') .themify_help(__('Speed of scrolling animation. Default: 0.9 second', 'themify')) . '</span>'.
                '<input type="number" class="width4" min="0" max=5000" step="1" name="scrollto_speed" value="' . themify_get( 'scrollto_speed' ) . '" /> ' . __( 'Seconds', 'themify' ) .
            '</p>';

            $output .= sprintf( '<p><span class="label">%s</span><select id="%s" name="%s">%s</select></p>',
                            esc_html__('Lightbox in Gallery module', 'themify'),
                            $pre . 'gallery_lightbox',
                            $pre . 'gallery_lightbox',
                            themify_options_module($options, $pre . 'gallery_lightbox')
                    );

                            $gutters=Themify_Builder_Model::get_gutters();
                            $gutters=array(
                                    'gutter'=>array('label'=>__('Normal gutter','themify'),'def'=>$gutters['gutter']),
                                    'narrow'=>array('label'=>__('Narrow gutter','themify'),'def'=>$gutters['narrow']),
                                    'none'=>array('label'=>__('None gutter','themify'),'def'=>$gutters['none'])
                            );
                            $output.='<div class="label">' .__('Gutter Size','themify') . '</div>';
                            $output.='<div class="pushlabel">';
                            foreach($gutters as $k=>$v){
                                    $k='setting-'.$k;
                                    $val=themify_builder_get($k);
                                    if($val===null ||$val===''){
                                            $val=$v['def'];
                                    }
                                    $output .='<label class="clearBoth"><input type="number" class="width4" min="0" max=50" step=".1" name="'.$k.'" value="' . $val . '"> '.$v['label'] . ' (%)</label>';
                            }
            $output.='</div></div>';
        }
        return $output;
    }

}

if (!function_exists('themify_manage_builder_animation')) {

    /**
     * Builder Setting Animations
     * @param array $data
     * @return string
     * @since 2.0.0
     */
    function themify_manage_builder_animation($data = array()) {
        $pre = 'setting-page_builder_animation_';
        $options = array(
            array('name' => '', 'value' => 'none'),
            array('name' => esc_html__('Disable on mobile & tablet', 'themify'), 'value' => 'mobile'),
            array('name' => esc_html__('Disable on all devices', 'themify'), 'value' => 'all')
        );
	$output='<div data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="enable">';
        $output.= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'appearance', esc_html__('Entrance Animation', 'themify'), $pre . 'appearance', $pre . 'appearance', themify_options_module($options, $pre . 'appearance')
        );
        $output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'parallax_bg', esc_html__('Parallax Background', 'themify'), $pre . 'parallax_bg', $pre . 'parallax_bg', themify_options_module($options, $pre . 'parallax_bg')
        );
        $output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'scroll_effect', esc_html__('Scroll Effects', 'themify'), $pre . 'scroll_effect', $pre . 'scroll_effect', themify_options_module($options, $pre . 'scroll_effect', true)
        );
        $output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'sticky_scroll', esc_html__('Sticky Scrolling', 'themify'), $pre . 'sticky_scroll', $pre . 'sticky_scroll', themify_options_module($options, $pre . 'sticky_scroll')
        );
	$output.='</div>';

        return $output;
    }

}

/**
 * Add Builder to all themes using the themify_theme_config_setup filter.
 * @param $themify_theme_config
 * @return mixed
 * @since 1.4.2
 */
function themify_framework_theme_config_add_builder($themify_theme_config) {
    $themify_theme_config['panel']['settings']['tab']['page_builder'] = array(
        'title' => __('Themify Builder', 'themify'),
        'id' => 'themify-builder',
        'custom-module' => array(
            array(
                'title' => __('Themify Builder Options', 'themify'),
                'function' => 'themify_manage_builder_active'
            )
        )
    );
    if(Themify_Builder_Model::builder_check()){
	    $themify_theme_config['panel']['settings']['tab']['integration-api']['custom-module'][] = array(
		    'title' => __('Optin', 'themify'),
		    'function' => 'themify_setting_optin',
	    );

	    $themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
		'title' => __('Animation Effects', 'themify'),
		'function' => 'themify_manage_builder_animation'
	    );

	    $themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
		'title' => __('Builder Modules', 'themify'),
		'function' => 'themify_manage_builder'
	    );
	    
	    $themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
		    'title' => __('Tools', 'themify'),
		    'function' => 'tb_tools_options'
	    );
    }
    return $themify_theme_config;
}

add_filter( 'themify_theme_config_setup', 'themify_framework_theme_config_add_builder', 11 );

function themify_setting_optin() {
	
	$providers = Builder_Optin_Service::get_providers();
	$clear=isset( $_GET['tb_option_flush_cache'] );
	ob_start();
	foreach ( $providers as $id => $instance ) {
		if ( $clear===true ) {
			$instance->clear_cache();
		}
		if ( $options = $instance->get_global_options() ) {
			?>
			<fieldset id="themify_setting_<?php echo $id; ?>">
				<legend>
					<span><?php echo $instance->get_label(); ?></span>
					<i class="tf_plus_icon"></i>
				</legend>
				<div class="themify_panel_fieldset_wrap" style="display: block !important;">
					<?php foreach ( $options as $field ) : ?>
						<p>
							<label class="label" for="setting-<?php echo $field['id']; ?>"><?php echo $field['label'] ?></label>
							<input type="text" name="setting-<?php echo $field['id']; ?>" id="setting-<?php echo $field['id']; ?>" value="<?php echo esc_attr( themify_builder_get( "setting-{$field['id']}" ) ); ?>" class="width10">
							<?php if ( isset( $field['description'] ) ) : ?>
								<small class="pushlabel"><?php echo $field['description'] ?></small>
							<?php endif; ?>
						</p>
					<?php endforeach; ?>
				</div><!-- .themify_panel_fieldset_wrap -->
			</fieldset>
		<?php } ?>
	<?php } ?>

	<br>
	<p>
		<a href="<?php echo add_query_arg( 'tb_option_flush_cache', 1 ); ?>" class="tb_option_flush_cache themify_button"><span><?php _e( 'Clear API Cache', 'themify' ); ?></span> </a>
	</p>

	<?php
	return ob_get_clean();
}