<?php
defined( 'ABSPATH' ) || exit;

class Themify_Builder_Component_Subrow extends Themify_Builder_Component_Base {

    public function get_name() {
		return 'subrow';
    }

    public function get_form_settings($onlyStyle=false) {
		$styles = $this->get_styling();
		if($onlyStyle===true){
			return $styles;
		}
		$row_form_settings = array(
			'setting' => array(
				'name' => __( 'Subrow Settings', 'themify' ),
				'options' => $this->get_settings(),
			),
			'styling' => array(
				'name' => __('Sub Row Styling', 'themify'),
				'options' => $styles
			)
		);
		return apply_filters('themify_builder_subrow_lightbox_form_settings', $row_form_settings);
    }


    /**
     * Get template Sub-Row.
     * 
     * @param array $mod 
     * @param string $builder_id 
     * @param boolean $echo 
     */
    public static function template(array &$mod, $builder_id, $echo = true) {
	$print_sub_row_classes = array('module_subrow themify_builder_sub_row');
	$subrow_tag_attrs =  array();
	$count = 0;
	$video_data = '';
	$is_styling = !empty($mod['styling']);
	if ($is_styling===true) {
            if (!empty($mod['styling']['custom_css_subrow'])) {
                $print_sub_row_classes[] = $mod['styling']['custom_css_subrow'];
            }
	    if (isset($mod['styling']['background_type'],$mod['styling']['background_zoom']) && $mod['styling']['background_type'] === 'image' && $mod['styling']['background_zoom'] === 'zoom' && $mod['styling']['background_repeat'] === 'repeat-none') {
		$print_sub_row_classes[] = 'themify-bg-zoom';
	    }
	    // background video
	    $video_data = self::get_video_background($mod['styling']);
            if($video_data){
                    $video_data=' '.$video_data;
            }
            else{
                $subrow_tag_attrs=self::setBgMode($subrow_tag_attrs,$mod['styling']);
            }
	    if(!empty( $mod['styling']['global_styles'] )){
		$print_sub_row_classes= Themify_Global_Styles::add_class_to_components($print_sub_row_classes , $mod['styling'] , $builder_id);
	    }
           
	}
	else{
	    $mod['styling']=array();
	}
	if (Themify_Builder::$frontedit_active===false) {
	    $count = !empty($mod['cols']) ? count($mod['cols']) : 0;
            $row_content_classes = $count > 0?self::get_responsive_cols($mod):array();
	    $row_content_classes = implode(' ', $row_content_classes);
	    $print_sub_row_classes[] = 'tb_' . $mod['element_id'];
	    if($is_styling===true ){
		$subrow_tag_attrs = self::sticky_element_props($subrow_tag_attrs,$mod['styling']);
	    }
	    $subrow_tag_attrs['data-lazy']=1;
	}
	$print_sub_row_classes = apply_filters('themify_builder_subrow_classes', $print_sub_row_classes, $mod, $builder_id);
	$print_sub_row_classes[]='tf_w tf_clearfix';
	$subrow_tag_attrs['class'] = implode(' ', $print_sub_row_classes);
	$print_sub_row_classes=null;
        if ($is_styling===true) {
            $subrow_tag_attrs = self::clickable_component( $mod['styling'], $subrow_tag_attrs );
        }
	$subrow_tag_attrs = apply_filters('themify_builder_subrow_attributes',self::parse_animation_effect($mod['styling'],$subrow_tag_attrs) ,$mod['styling'], $builder_id);
	

        if ($echo===false) {
            $output = PHP_EOL; // add line break
	    ob_start();
	}
	// Start Sub-Row Render ######
	?>
	<div <?php echo self::get_element_attributes($subrow_tag_attrs),$video_data; ?>>
	    <?php
	    $subrow_tag_attrs=$video_data=null;
	    if ($is_styling===true) {
		do_action('themify_builder_background_styling', $builder_id, $mod, 'subrow','');
		self::background_styling($mod, 'subrow',$builder_id);
	    }
	    ?>
			<div class="subrow_inner<?php if (Themify_Builder::$frontedit_active===false): ?> <?php echo $row_content_classes ?><?php endif; ?> tf_box tf_w">
		<?php
                unset($row_content_classes);
		if ($count > 0) {
                    if(isset($mod['desktop_dir']) && $mod['desktop_dir']==='rtl'){//backward compatibility
                            $mod['cols']=array_reverse($mod['cols']);
                    }
		    foreach ($mod['cols'] as $i=>$sub_col) {
			$cl=$i===0?'first':($i===($count-1)?'last':null);//backward compatibility
                        Themify_Builder_Component_Column::template($sub_col, $builder_id, true,true,$cl);
		    }
		}
		?>
	    </div>
	</div><!-- /themify_builder_sub_row -->
	<?php
	// End Sub-Row Render ######

		if ($echo===false) {
		   return PHP_EOL.ob_get_clean().PHP_EOL;
	}
    }

}