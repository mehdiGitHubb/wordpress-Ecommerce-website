<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Map
 * Description: Display Map
 */

class TB_Map_Module extends Themify_Builder_Component_Module {

	public function __construct() {
		parent::__construct('map');
		add_filter( 'themify_builder_active_vars', array(__CLASS__, 'check_map_api'));
	}
	
        public function get_name(){
            return __('Map', 'themify');
        }

	public function get_icon(){
	    return 'map-alt';
	}

	/**
	 * Handles Ajax request to check map api
	 *
	 * @since 4.5.0
	 */
	public static function check_map_api($values) {
		$googleAPI = themify_builder_get( 'setting-google_map_key', 'builder_settings_google_map_key' );
	    $values['google_api'] =  !empty($googleAPI);
		$url = themify_is_themify_theme() ? admin_url( 'admin.php?page=themify#setting-integration-api' ) : admin_url( 'admin.php?page=themify-builder&tab=builder_settings' );
		if(!$values['google_api']) {
		    $values['google_api_err'] = sprintf( __('Please enter the required <a href="%s" target="_blank">Google Maps API key</a>.','themify'), $url );
		}
	    $bingAPI = themify_builder_get( 'setting-bing_map_key', 'builder_settings_bing_map_key' );
		$values['bing_api'] =  !empty($bingAPI);
		if(!$values['bing_api']) {
			$values['bing_api_err'] = sprintf( __('Please enter the required <a href="%s" target="_blank">Bing Maps API key</a>.','themify'), $url );
		}
		return $values;
	}

	public function get_options() {
		$range = range(1,20);
		$map_key_setting =themify_is_themify_theme() ? admin_url('admin.php?page=themify#setting-google_map') : admin_url('admin.php?page=themify-builder&tab=builder_settings') ;
		return array(
			array(
				'id' => 'mod_title_map',
				'type' => 'title'
			),
			array(
				'id' => 'map_provider',
				'type' => 'radio',
				'label' => __('Map Provider', 'themify'),
				'options' => array(
					array('value' => 'google', 'name' => __('Google', 'themify')),
					array('value' => 'bing', 'name' => __('Bing', 'themify'))
				),
				'option_js' => true
			),
			array(
				'id' => 'map_display_type',
				'type' => 'radio',
				'label' => __('Type', 'themify'),
				'options' => array(
					array('value' => 'dynamic', 'name' => __('Dynamic', 'themify')),
					array('value' => 'static', 'name' => __('Static image', 'themify'))
				),
				'option_js' => true,
				'wrap_class' => 'tb_group_element_google'
			),
			array(
				'id' => 'address_map',
				'type' => 'address',
				'label' => __('Address', 'themify')
			),
			array(
				'id' => 'google_map_api_key',
				'type' => 'check_map_api',
				'map' => 'google',
				'label'=>'',
				'wrap_class' => 'tb_field_error_msg tb_group_element_google'
			),
			array(
				'id' => 'bing_map_api_key',
				'type' => 'check_map_api',
				'map' => 'bing',
				'label'=>'',
				'wrap_class' => 'tb_field_error_msg tb_group_element_bing'
			),
			array(
				'id' => 'latlong_map',
				'type' => 'text',
				'class' => 'large',
				'label' => __('Lat/Long', 'themify'),
				'help' => __('To use Lat/Long (eg. 43.6453137,-79.3918391) instead of address, leave address field empty.', 'themify')
			),
			array(
				'type' => 'group',
				'label' => __( 'Map Options', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
						'id' => 'bing_type_map',
						'type' => 'select',
						'label' => __('Type', 'themify'),
						'options' => array(
							'aerial' => __('Aerial', 'themify'),
							'road' => __('Road', 'themify'),
							'canvasDark' => __('Canvas Dark', 'themify'),
							'canvasLight' => __('Canvas Light', 'themify'),
							'grayscale' => __('Gray Scale', 'themify'),
						),
						'wrap_class' => 'tb_group_element_bing'
					),
					array(
						'id' => 'zoom_map',
						'type' => 'select',
						'label' => __('Zoom', 'themify'),
						'options' => array_combine($range,$range)
					),
					array(
						'id' => 'w_map',
						'type' => 'range',
						'class' => 'xsmall',
						'label' => 'w',
						'wrap_class' => 'tb_group_element_dynamic',
						'units' => array(
							'px' => array(
								'max' => 3500
							),
							'%' => '',
							'vw' => array(
								'max' => 3500
							),
						)
					),
					array(
						'id' => 'w_map_static',
						'type' => 'number',
						'label' => 'w',
						'after' => 'px',
						'wrap_class' => 'tb_group_element_static'
					),
					array(
						'id' => 'h_map',
						'type' => 'range',
						'label' => 'ht',
						'class' => 'xsmall',
						'units' => array(
							'px' => array(
								'max' => 3500
							),
							'%' => array(
								'max' => 100
							),
							'vh' => ''
						)
					),
					array(
						'type' => 'multi',
						'label' => __('Border', 'themify'),
						'options' => array(
							array(
								'id' => 'b_style_map',
								'type' => 'select',
								'border' => true
							),
							array(
								'id' => 'b_color_map',
								'type' => 'color',
								'class' => 'large'
							),
							array(
								'id' => 'b_width_map',
								'type' => 'range',
								'class' => 'small',
								'after' => 'px'
							),
						)
					),
					array(
						'id' => 'type_map',
						'type' => 'select',
						'label' => __('Type', 'themify'),
						'options' => array(
							'ROADMAP' => __('Road Map', 'themify'),
							'SATELLITE' => __('Satellite', 'themify'),
							'HYBRID' => __('Hybrid', 'themify'),
							'TERRAIN' => __('Terrain', 'themify')
						),
						'wrap_class' => 'tb_group_element_google'
					),
					array(
						'id' => 'map_control',
						'type' => 'toggle_switch',
						'label' => __('Map Controls', 'themify'),
						'options' => 'simple',
						'wrap_class' => 'tb_group_element_dynamic'
					),
					array(
						'id' => 'draggable_map',
						'type' => 'toggle_switch',
						'label' => __('Draggable', 'themify'),
						'options' => array(
							'on' => array('name'=>'enable', 'value' =>'en'),
							'off' => array('name'=>'disable', 'value' =>'dis')
						),
						'wrap_class' => 'tb_group_element_dynamic',
						'binding' => array(
							'checked' => array('show' => 'scrollwheel_map'),
							'not_checked' => array('hide' =>'scrollwheel_map')
						)
					),
					array(
						'id' => 'scrollwheel_map',
						'type' => 'toggle_switch',
						'label' => __('Scrollwheel', 'themify'),
						'options' => array(
							'on' => array('name'=>'enable', 'value' =>'en'),
							'off' => array('name'=>'disable', 'value' =>'dis')
						),
						'wrap_class' => 'tb_group_element_dynamic'
					),
					array(
						'id' => 'draggable_disable_mobile_map',
						'type' => 'toggle_switch',
						'label' => __('Mobile Draggable', 'themify'),
						'options' => array(
							'on' => array('name'=>'no', 'value' =>'en'),
							'off' => array('name'=>'yes', 'value' =>'dis')
						),

						'wrap_class' => 'tb_group_element_dynamic'
					),
					array(
						'id' => 'info_window_map',
						'type' => 'textarea',
						'label' => __('Info Window', 'themify'),
						'help' => __('Additional info that will be shown when clicking on map marker', 'themify'),
						'wrap_class' => 'tb_group_element_dynamic'
					),
				)
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_map' ),
		);
	}

	public function get_live_default() {
		return array(
			'address_map' => 'Toronto',
			'b_style_map' => 'solid',
			'map_control'=>'yes',
			'draggable_map'=>'enable',
			'w_map' => '100',
			'w_map_unit' => '%',
			'h_map_unit' => 'px',
			'zoom_map'=>'8',
			'w_map_static'=>'500',
			'h_map'=>'300'
		);
	}

	public function get_styling() {
		$general = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color('', 'background_color', 'bg_c', 'background-color'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color('', 'bg_c', 'bg_c', 'background-color', 'h'),
						)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_padding()
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding('', 'p', 'h')
						)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_margin()
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin('', 'm', 'h')
						)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border()
						)
					),
					'h' => array(
						'options' => array(
							self::get_border('', 'b', 'h')
						)
					)
				))
			)),
			// Filter
			self::get_expand('f_l',
				array(
					self::get_tab(array(
						'n' => array(
							'options' => self::get_blend()

						),
						'h' => array(
							'options' => self::get_blend('', '', 'h')
						)
					))
				)
			),
			// Width
			self::get_expand('w', array(
				self::get_width('', 'w')
			)),
			// Height & Min Height
			self::get_expand('ht', array(
					self::get_height(),
					self::get_min_height(),
					self::get_max_height()
				)
			),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius('', 'r_c', 'h')
							)
						)
					))
				)
			),
			// Shadow
			self::get_expand('sh', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow('', 'sh', 'h')
							)
						)
					))
				)
			),
			// Display
			self::get_expand('disp', self::get_display())
		);

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'm_t' => array(
					'options' => $this->module_title_custom_style()
				)
			)
		);
	}

    protected function _visual_template() {
	$module_args = self::get_module_args('mod_title_map');
	$default_addres = sprintf('<b>%s</b><br/><p>#s#</p>', __('Address', 'themify'));
	?>
	<# 
	    let w_unit = data.w_map_unit===undefined?'px':false,
		h_unit = data.w_map_unit===undefined?'px':false,
		args=Object.assign({
		    'w_map_unit':'%',
		    'h_map_unit':'px',
		    'h_map':300,
		    'type_map':'ROADMAP',
		    'scrollwheel_map':'disable',
		    'draggable_map':'enable',
		    'map_control':'yes',
		    'draggable_disable_mobile_map':'yes',
		    'map_provider':'google'
		},data);
	    if(w_unit=='px' && args.unit_w == '%'){
		w_unit='%';
	    }
	    let address = args.address_map?args.address_map.trim().replace(/\s\s+/g, ' '):'',
		info = !args.info_window_map?'<?php echo $default_addres ?>'.replace('#s#',address):args.info_window_map;
		style = '';
		if(args.b_width_map){
		    const b_type=!args.b_style_map?'solid':args.b_style_map;
		    style+= 'border: '+b_type+' '+args.b_width_map+'px';
		    if (args.b_color_map) {
			style+=' '+api.Helper.toRGBA(args.b_color_map);
		    }
		    style+= ';';
		}
	    #>

	    <div class="module module-<?php echo $this->slug; ?> {{ args.css_map }}">
		<# if( args.mod_title_map ) { #>
		    <?php echo $module_args['before_title']; ?>{{{ args.mod_title_map }}}<?php echo $module_args['after_title']; ?>
		<# }

		if( args.map_provider == 'google' && args.map_display_type=='static' ) {
		    let q = 'key='+'<?php echo Themify_Builder_Model::getMapKey() ?>';
		    if(address){
			q+='&center='+address;
		    }
		    else if(args.latlong_map){
			q+='&center='+args.latlong_map;
		    }
		    args.w_map_static=args.w_map_static+'';
		    args.h_map=args.h_map+'';
		    q+='&zoom='+args.zoom_map;
		    q+='&maptype='+args.type_map.toLowerCase();
		    q+='&size='+args.w_map_static.replace(/[^0-9]/,'')+'x'+args.h_map.replace(/[^0-9]/,'');
		#>
		<img style="{{ style }}" src="https://maps.googleapis.com/maps/api/staticmap?{{ q }}">
		<#
		}
		else if( address || args.latlong_map ) {
		    w_unit = w_unit || args.w_map_unit;
		    h_unit = h_unit || args.h_map_unit;
		    style+= 'width:'+args.w_map + w_unit+';';
		    style+= 'height:'+args.h_map + h_unit+';';
		    reverse = !address && args.latlong_map;
		    address = address || args.latlong_map,
		    scroll = args.scrollwheel_map == 'enable'?1:0,
		    drag = args.draggable_map == 'enable'?1:0,
		    mdrag=args.draggable_disable_mobile_map=='yes'?1:0,
		    control = args.map_control == 'no' ? 1 : 0,
		    type= args.map_provider == 'google'?args.type_map:args.bing_type_map;
		#>
		    <div data-map-provider="{{ args.map_provider }}" data-address="{{ address }}" data-control="{{control}}" data-zoom="{{ args.zoom_map }}" data-type="{{ type }}" data-scroll="{{ args.scroll }}" data-drag="{{ args.drag }}" data-mdrag="{{ args.mdrag }}" class="themify_map<# print(args.map_provider != 'google'?' themify_bing_map':'')#>"  style="{{ style }}"  data-info-window="{{ info }}" data-reverse-geocoding="{{ reverse }}"></div>
		<# } #>

	    </div>
	    <?php
	}


	/**
	 * Render plain content
	 */
	public function get_plain_content($module) {
		$mod_settings = wp_parse_args($module['mod_settings'], array(
			'mod_title_map' => '',
			'address_map' => 'Toronto',
			'zoom_map' => 15
		));
		if (!empty($mod_settings['address_map'])) {
			$mod_settings['address_map'] = preg_replace('/\s+/', ' ', trim($mod_settings['address_map']));
		}
		$text = sprintf('<h3>%s</h3>', $mod_settings['mod_title_map']);
		$text .= sprintf(
			'<iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=%s&amp;t=m&amp;z=%d&amp;output=embed&amp;iwloc=near"></iframe>', urlencode($mod_settings['address_map']), absint($mod_settings['zoom_map'])
		);
		return $text;
	}

}

new TB_Map_Module();
