<?php
defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Code
 * Description: Display formatted code
 */

class TB_Code_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('code');
    }
    
    public function get_name(){
        return __('Code', 'themify');
    }

    public function get_icon(){
	return 'notepad';
    }
    
    public function get_assets() {
        return array(
	    'async'=>true,
	    'css'=>1,
	    'js'=>1
        );
    }

    public function get_options() {
		return array(
			array(
			    'id' => 'm_t',
			    'type' => 'title'
			),
			array(
			    'type'=>'code',
			    'options'=>array(
				'lng'=>__('Language','themify'),
				'theme'=> __( 'Color Schemes', 'themify' )
			    ),
			    'control'=>false
			),
			array(
				'id' => 'numbers',
				'label'=>__('Line Numbers','themify'),
				'type' => 'toggle_switch',
				'options' => 'simple'
			),
			array(
				'id' => 'copy',
				'label'=>__('Copy Button','themify'),
				'type' => 'toggle_switch',
				'options' => 'simple'
			),
			array(
				'id' => 'highlight',
				'label'=>__('Higlight Lines','themify'),
				'type' => 'text',
				'description'=>__('Example: 1-2, 4, 5-8','themify'),
				'control'=>array('event'=>'change')
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css' ),
		);
    }
    
    public function get_live_default() {
		return array(
			'numbers'=>'yes',
			'copy'=>'yes',
			'lng' =>'javascript',
			'code' =>'function summ(a,b){
				return a+b;
			}'
		);
	}

    public function get_styling() {
	$general = array(

	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			     self::get_color(' pre', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' pre', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_transform(),
			    self::get_text_decoration('', 'text_decoration_regular'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_text_decoration('', 't_d_r', 'h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .line-numbers')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .line-numbers', 'p', 'h')
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
		// Position
		self::get_expand('po', array( self::get_css_position())),
		// Display
		self::get_expand('disp', self::get_display())
	);
	$highlight_code = array(
	    // Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
				   self::get_color(' .line-highlight', 'h_c_b_c', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .line-highlight', 'h_c_b_c', 'bg_c', 'background-color','h')
				)
				)
			))
	    )),	    // Background
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .line-highlight', 'h_c_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .line-highlight', 'h_c_b_sh', 'h')
					)
				)
			))
		)),
		// Filter
		self::get_expand('f_l',	array(
			self::get_tab(array(
				'n' => array(
					'options' => self::get_blend(' .line-highlight', 'h_c_fl')

				),
				'h' => array(
					'options' => self::get_blend(' .line-highlight', 'h_c_fl', 'h')
				)
			))
		)),
	);
	return array(
	    'type' => 'tabs',
	    'options' => array(
		'g' => array(
		    'options' => $general
		),
		'm_t' => array(
		    'options' => $this->module_title_custom_style()
		),
		'h_c' => array(
		    'label' => __('Highlight Code', 'themify'),
		    'options' => $highlight_code
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('m_t');
	?>
	<div class="module module-<?php echo $this->slug; ?> {{ data.css }} tf_scrollbar tb_prism_{{ data.theme }}" data-theme="{{ data.theme }}">
	    <# if ( data.m_t ) { #>
	    <?php echo $module_args['before_title']; ?>{{{ data.m_t }}}<?php echo $module_args['after_title']; ?>
	    <# } #>
	    <pre class="tf_rel tf_scrollbar tf_textl"<# if(data.highlight){#> data-line="{{data.highlight}}"<#}#>>
		<code class="language-{{data.lng}}<# if(data.numbers=='yes'){#> line-numbers<#}#>">{{ data.code }}</code>
		<# if(data.copy=='yes'){#>
			<em class="tb_code_copy tf_opacity">
			    <# print( api.Helper.getIcon('ti-clipboard').outerHTML ) #>
			</em>
		<#}#>
	    </pre>
	</div>
	<?php
    }

}

new TB_Code_Module();
