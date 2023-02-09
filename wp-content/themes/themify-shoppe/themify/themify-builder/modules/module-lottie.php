<?php
defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Lottie
 * Description: Display Lottie
 */

class TB_Lottie_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('lottie');
    }

    public function get_name(){
        return __('Lottie Animation', 'themify');
    }

    public function get_icon(){
	return 'star';
    }

    public function get_assets() {
        return array(
	    'css'=>1
        );
    }

    public function get_options() {
	return array(
		array(
		    'id' => 'm_t',
		    'type' => 'title'
		),
		array(
		    'id' => 'loop',
		    'label'=>__('Loop Animation','themify'),
		    'type' => 'toggle_switch',
		    'options' => array(
			'on'=>array(
			    'name'=>1,
			    'value'=>__('Yes','themify'),
			),
			'off'=>array(
			    'value'=>__('No','themify'),
			)
		    )
		),
		array(
		    'type'=>'builder',
		    'wrap_class'=>'tb_lottie_chain',
		    'id'=>'actions',
		    'options'=>array(
			array(
			    'id'=>'path',
			    'type' => 'lottie',
			    'label'=>__('Json File','themify'),
			    'ext'=>'text/plain,application/zip',
			    'binding' => array(
				'empty' => array( 'hide' => 'tb_lottie_act_w' ) ,
				'not_empty' => array( 'show' => 'tb_lottie_act_w' )
			    )
			),
			array(
			    'type'=>'group',
			    'wrap_class'=>'tb_lottie_act_w',
			    'options'=>array(
				array(
				    'id' => 'state',
				    'type' => 'select',
				    'label'=>__('Play this animation when','themify'),
				    'options'=>array(
					'autoplay'=>__('On load','themify'),
					'click'=>__('On click','themify'),
					'hover'=>__('On hover','themify'),
					'hold'=>__('Hold','themify'),
					'pausehold'=>__('pauseHold','themify'),
					'seek'=>__('Seek','themify'),
					'none'=>__('Animation does not play','themify'),
				    ),
				    'binding' => array(
					'autoplay' => array('show'=>array('tb_loop_msg','tb_next_wr','count'), 'hide' => array('tb_click_msg','tb_hover_msg','tb_hold_msg','tb_pausehold_msg')),
					'click' => array( 'show' => array('tb_click_msg','tb_next_wr','count'),'hide' => array('tb_loop_msg','tb_hover_msg','tb_hold_msg','tb_pausehold_msg') ),
					'hover' =>array( 'show' => array('tb_hover_msg','tb_next_wr','count'),'hide' => array('tb_loop_msg','tb_click_msg','tb_hold_msg','tb_pausehold_msg') ),
					'pausehold'=>array('show' => array('tb_pausehold_msg','tb_next_wr'), 'hide' => array('tb_click_msg','tb_hover_msg','tb_hold_msg','tb_loop_msg','tb_next_wr','count') ),
					'hold' => array('show' => array('tb_hold_msg','tb_next_wr'), 'hide' => array('tb_click_msg','tb_hover_msg','tb_pausehold_msg','tb_loop_msg','tb_next_wr','count') ),
					'none' => array( 'hide' => array('tb_click_msg','tb_hover_msg','tb_hold_msg','count','tb_pausehold_msg','tb_next_msg','speed','delay','dir') ),
				    )
				),
				array(
				    'id'=>'count',
				    'label'=>__('Count','themify'),
				    'type'=>'range',
				    'min'=>1,
				    'placeholder'=>1
				),
				array(
				    'type'=>'message',
				    'label'=>'',
				    'wrap_class'=>'tb_click_msg',
				    'comment'=>__('After X click(s) this animtion will start play.','themify')
				),
				array(
				    'type'=>'message',
				    'label'=>'',
				    'wrap_class'=>'tb_hover_msg',
				    'comment'=>__('After X hover(s) this animtion will start play.','themify')
				),
				array(
				    'type'=>'message',
				    'label'=>'',
				    'wrap_class'=>'tb_loop_msg',
				    'comment'=>__('Type repeat(s).','themify')
				),
				array(
				    'type'=>'message',
				    'label'=>'',
				    'wrap_class'=>'tb_hold_msg',
				    'comment'=>__('Hover will start animation. If the user releases the hover over the animation it plays in reverse.','themify')
				),
				array(
				    'type'=>'message',
				    'label'=>'',
				    'wrap_class'=>'tb_pausehold_msg',
				    'comment'=>__('Hover will start animation. If the user releases the hover over the animation it pauses.','themify')
				),
				array(
				    'type'=>'group',
				    'wrap_class'=>'tb_next_wr',
				    'options'=>array(
					array(
					    'id' => 'tr',
					    'type' => 'select',
					    'label'=>__('Play next animation when','themify'),
					    'options'=>array(
						'autoplay'=>__('Animation End','themify'),
						'click'=>__('On click','themify'),
						'hover'=>__('On hover','themify'),
						'seek'=>__('Seek','themify'),
					    ),
					    'binding' => array(
						'autoplay' => array( 'hide' => array('tb_nloop_msg','tb_nhover_msg','tb_nclick_msg','tr_count') ),
						'click' => array( 'show' => array('tb_nclick_msg','tr_count'),'hide' => array('tb_nloop_msg','tb_nhover_msg') ),
						'hover' =>array( 'show' => array('tb_nhover_msg','tr_count'),'hide' => array('tb_nloop_msg','tb_nclick_msg') ),
						'seek' => array( 'hide' => array('tb_nclick_msg','tb_nhover_msg','tb_nclick_msg','tr_count') ),
					    )
					),
					array(
					    'id'=>'tr_count',
					    'label'=>__('Count','themify'),
					    'type'=>'range',
					    'min'=>1,
					    'placeholder'=>1
					),
					array(
					    'type'=>'message',
					    'label'=>'',
					    'wrap_class'=>'tb_nclick_msg',
					    'comment'=>__('After X click(s) will switch to next animation.','themify')
					),
					array(
					    'type'=>'message',
					    'label'=>'',
					    'wrap_class'=>'tb_nhover_msg',
					    'comment'=>__('After X hover(s) will switch to next animation.','themify')
					),
				    )
				),
				array(
				    'id' => 'speed',
				    'type' => 'range',
				    'label'=>__('Speed','themify'),
				    'increment'=>.1,
				    'min'=>.1,
				    'placeholder'=>1
				),
				array(
				    'id' => 'delay',
				    'type' => 'range',
				    'label'=>__('Delay','themify'),
				    'after'=>__('Seconds','themify'),
				    'increment'=>.1,
				    'min'=>0,
				    'placeholder'=>0
				),
				array(
				    'id' => 'dir',
				    'label'=>__('Reverse','themify'),
				    'type' => 'toggle_switch',
				    'options' => array(
					'on'=>array(
					    'name'=>-1,
					    'value'=>__('Yes','themify'),
					),
					'off'=>array(
					    'value'=>__('No','themify'),
					)
				    ),
				),
				array(
				    'id' => 'fr',
				    'label'=>__('Play Frames','themify'),
				    'type' => 'radio',
				    'options' => array(
					array('value'=>'','name'=>__('All', 'themify')),
					array('value'=>'1','name'=>__('By Id', 'themify')),
					array('value'=>'2','name'=>__('Range', 'themify'))
				    ),
				    'binding' => array(
					'' => array('hide' => array('fr_id','fr_range')),
					'1' => array( 'show' => 'fr_id','hide' => 'fr_range' ),
					'2' =>array( 'show' => 'fr_range','hide' => 'fr_id'),
				    )
				),
				array(
				    'id' => 'fr_id',
				    'type' => 'text',
				    'wrap_class' => 'tb_group_element_1',
				    'label' => __('FrameId', 'themify'),
				    'help'=>__('Type frame id to use only that frame in json','themif')
				),
				array(
				    'id' => 'fr_range',
				    'type' => 'slider_range',
				    'wrap_class' => 'tb_group_element_2',
				    'label' => __('Rating', 'themify'),
				    'help'=>__('Enter frame from-to,will be played only that segment','themif'),
				    'options' => array(
					'max' => 1000,
					'unit'=>''
				    )
				)
			    )
			),
		    )
		),
		array( 'type' => 'custom_css_id', 'custom_css' => 'css' ),
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
		self::get_row_gap(' .tb_star_wrap','','',true),
		self::get_column_gap(' .tb_star_item','','',true)
	    )),
	    // Star Base Color
	    self::get_expand('Star Base Color', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .tb_star_item', 'st_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .tb_star_item', 'st_c',null, null, 'hover')
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
	    // Text Before Color
	    self::get_expand('Color', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array(' .tb_star_textbefore'), 'c_t_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array(' .tb_star_textbefore'), 'c_t_b',null, null, 'hover')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		    self::get_tab(array(
			    'n' => array(
				    'options' => array(
						self::get_margin(' .tb_star_textbefore', 'm_t_b')
				    )
			    ),
			    'h' => array(
				    'options' => array(
						self::get_margin(' .tb_star_textbefore', 'm_t_b', 'h')
				    )
			    )
		    ))
	    )),
	    // Width
	    self::get_expand('w', array(
		    self::get_width(' .tb_star_textbefore', 'w_t_b')
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
			)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('m_t');
	?>
	<div class="module module-<?php echo $this->slug; ?> {{ data.css }}">
	    <# if( data.m_t ) { #>
		<?php echo $module_args['before_title']; ?>{{{ data.m_t }}}<?php echo $module_args['after_title']; ?>
	    <# }
		const json={actions:(data.actions || [])};
		if(data.loop){
		    json.loop=1;
		}
	    #>
	    <tf-lottie class="tf_w">
		<template>{{{JSON.stringify(json)}}}</template>
	    </tf-lottie>
	</div>
	<?php
    }

}

new TB_Lottie_Module();
