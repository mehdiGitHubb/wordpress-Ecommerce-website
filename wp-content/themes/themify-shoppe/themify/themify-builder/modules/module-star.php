<?php
defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Star
 * Description: Display Rating Star
 */

class TB_Star_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('star');
    }

    public function get_name(){
        return __('Rating Star', 'themify');
    }

    public function get_icon(){
	return 'star';
    }

    public function get_assets() {
        $_arr= array(
	    'css'=>1
        );
        if(!Themify_Builder_Model::is_front_builder_activate()){
            $_arr['js']=1;
        }
        return $_arr;
    }

    public function get_options() {
		return array(
			array(
			    'id' => 'm_t',
			    'type' => 'title'
			),
			array(
			'id' => 'rates',
			'type' => 'builder',
			'options' => array(
				array(
				    'type'=>'text',
				    'label'=>__('Text Before','themify'),
				    'id'=>'text_b',
				    'control' => array(
					'selector' => '.tb_star_text_b'
				    )
				),
				array(
				    'type'=>'text',
				    'label'=>__('Text After','themify'),
				    'id'=>'text_a',
				    'control' => array(
					'selector' => '.tb_star_text_a'
				    )
				),
				array(
				    'type'=>'icon',
				    'label'=>'icon',
				    'id'=>'ic'
				),
				array(
				    'id' => 'count',
				    'type' => 'range',
				    'label' => __('Stars Count', 'themify'),
				    'min' => 1,
				    'max' => 20
				),
				array(
				    'id' => 'rating',
				    'type' => 'slider_range',
				    'label' => __('Rating', 'themify'),
				    'options' => array(
					'max' => 20,
					'step'=>.1,
					'unit'=>'',
					'inputRange'=>true,
					'range' => false
				    )
				),
			    )
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css' ),
		);
    }

    public function get_live_default() {
	    return array(
		'rates' => array(
		    array(
			'ic'=>'fas fullstar',
			'count'=>5,
			'rating'=>5
		    )
		)
	    );
	}

    public function get_styling() {
	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_image()
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_image('', 'b_i','bg_c','b_r','b_p', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(),
			    self::get_color('', 'st_g'),
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_align(),
			    self::get_text_transform(),
			    self::get_font_style(),
				self::get_text_shadow(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('', 'f_f_h'),
			    self::get_color('','f_c_h',null,null,'h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_font_style('', 'f_st', 'f_w', 'h'),
				self::get_text_shadow('','t_sh','h'),
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
		// Position
		self::get_expand('po', array( self::get_css_position())),
		// Display
		self::get_expand('disp', self::get_display())
	);
	$star = array(
	    //Gaps
	    self::get_expand('gap', array(
		self::get_row_gap(' .tb_star_wrap'),
		self::get_column_gap(' .tb_star_item')
	    )),
	    // Star Base Color
	    self::get_expand('Star Base Color', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .tb_star_container', 'st_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .tb_star_container', 'st_c',null, null, 'hover')
			)
		    )
		))
	    )),
	    // Star Highlight Color
	    self::get_expand('Star Highlight Color', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array(' .tb_star_item .tb_star_fill'), 'st_h_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array(' .tb_star_item .tb_star_fill'), 'st_h_c',null, null, 'hover')
			)
		    )
		))
	    )),
	);
	$text_before = array(
	    // Text Before Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb_star_text_b', 'f_f_t_b'),
			    self::get_color(' .tb_star_text_b', 'c_t_b'),
			    self::get_font_size(' .tb_star_text_b', 'f_s_t_b'),
			    self::get_line_height(' .tb_star_text_b', 'l_h_t_b'),
			    self::get_letter_spacing(' .tb_star_text_b', 'l_s_t_b'),
			    self::get_text_transform(' .tb_star_text_b', 't_t_t_b'),
			    self::get_font_style(' .tb_star_text_b', 'f_sy_t_b', 'f_w_t_b'),
			    self::get_text_shadow(' .tb_star_text_b', 't_sh_t_b'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .tb_star_text_b', 'f_f_t_b', 'h'),
			    self::get_color(' .tb_star_text_b', 'c_t_b',  null, null, 'h'),
			    self::get_font_size(' .tb_star_text_b', 'f_s_t_b', '', 'h'),
			    self::get_font_style(' .tb_star_text_b', 'f_sy_t_b', 'f_w_t_b', 'h'),
			    self::get_text_shadow(' .tb_star_text_b', 't_sh_t_b','h'),
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		    self::get_tab(array(
			    'n' => array(
				    'options' => array(
						self::get_margin(' .tb_star_text_b', 'm_t_b')
				    )
			    ),
			    'h' => array(
				    'options' => array(
						self::get_margin(' .tb_star_text_b', 'm_t_b', 'h')
				    )
			    )
		    ))
	    )),
	    // Width
	    self::get_expand('w', array(
		    self::get_width(' .tb_star_text_b', 'w_t_b')
	    )),
	);
	$text_after = array(
	    // Text After Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb_star_text_a', 'f_f_t_a'),
			    self::get_color(' .tb_star_text_a', 'c_t_a'),
			    self::get_font_size(' .tb_star_text_a', 'f_s_t_a'),
			    self::get_line_height(' .tb_star_text_a', 'l_h_t_a'),
			    self::get_letter_spacing(' .tb_star_text_a', 'l_s_t_a'),
			    self::get_text_transform(' .tb_star_text_a', 't_t_t_a'),
			    self::get_font_style(' .tb_star_text_a', 'f_sy_t_a', 'f_w_t_a'),
			    self::get_text_shadow(' .tb_star_text_a', 't_sh_t_a'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .tb_star_text_a', 'f_f_t_a', 'h'),
			    self::get_color(' .tb_star_text_a', 'c_t_a',  null, null, 'h'),
			    self::get_font_size(' .tb_star_text_a', 'f_s_t_a', '', 'h'),
			    self::get_font_style(' .tb_star_text_a', 'f_sy_t_a', 'f_w_t_a', 'h'),
			    self::get_text_shadow(' .tb_star_text_a', 't_sh_t_a','h'),
			)
		    )
		))
	    )),

	    // Margin
	    self::get_expand('m', array(
		    self::get_tab(array(
			    'n' => array(
				    'options' => array(
						self::get_margin(' .tb_star_text_a', 'm_t_a')
				    )
			    ),
			    'h' => array(
				    'options' => array(
						self::get_margin(' .tb_star_text_a', 'm_t_a', 'h')
				    )
			    )
		    ))
	    )),
	    // Width
	    self::get_expand('w', array(
		    self::get_width(' .tb_star_text_a', 'w_t_a')
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
			'r_s' => array(
				'label' => __('Star', 'themify'),
				'options' => $star
			),
			't_b' => array(
				'label' => __('Text Before', 'themify'),
				'options' => $text_before
			),
			't_a' => array(
				'label' => __('Text After', 'themify'),
				'options' => $text_after
			)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('m_t');
	?>
	<div class="module module-<?php echo $this->slug; ?> {{ data.css }}">
	    <# if ( data.m_t ) { #>
		<?php echo $module_args['before_title']; ?>{{{ data.m_t }}}<?php echo $module_args['after_title']; ?>
	    <# } #>
	    <div class="tb_star_wrap">
		<# const rates=data.rates || [];
		for(var i=0,len=rates.length;i<len;++i){#>
		    <div class="tb_star_item tb_star_animate">
		    <# let item=rates[i],
			    count =parseInt(item.count) || 5,
			    rating = parseFloat(parseFloat(item.rating || count).toFixed(2)),
			    defaultIcon=api.Helper.getIcon((item.ic || 'ti-star')),
			    fillIcon=defaultIcon,
			    halfIcon=defaultIcon;

			defaultIcon=defaultIcon.outerHTML;

			fillIcon.classList.add('tb_star_fill');
			fillIcon=fillIcon.outerHTML;

			if(item.text_b){#>
			    <span class="tb_star_text_b" contenteditable="false" data-name="text_b" data-repeat="rates" data-index="{{i}}">{{item.text_b}}</span>
			<#} #>
			<div class="tb_star_container">
			<#for(var j=0;j<count;++j){
				if((rating-j)>=1){
				    print(fillIcon)
				}
				else if(rating>j){
				    let gid='tb_'+data.cid+i,
					decimal =(rating-parseInt(rating)).toFixed(2),
					cl=halfIcon.classList;
					cl.add('tb_star_half');
					cl.remove('tb_star_fill');
					halfIcon.style.setProperty('--tb_star_half','url(#'+gid+')');
					halfIcon=halfIcon.outerHTML;
				    #>
					<svg width="0" height="0" aria-hidden="true" style="visibility:hidden;position:absolute">
					    <defs>
						<linearGradient id="{{gid}}">
						    <stop offset="{{decimal*100}}%" class="tb_star_fill"/>
						    <stop offset="{{decimal*100}}%" stop-color="currentColor"/>
						</linearGradient>
					    </defs>
					</svg>
				    <#
				    print(halfIcon)
				}
				else{
				    print(defaultIcon)
				}
			    }#>
			</div>
			<# if(item.text_a){#>
			    <span class="tb_star_text_a" contenteditable="false" data-name="text_a" data-repeat="rates" data-index="{{i}}">{{item.text_a}}</span>
			<#} #>
		    </div>
		<#}#>
	    </div>
	</div>
	<?php
    }

}

new TB_Star_Module();
