<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template star
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-star.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'm_t' => '',
    'rates' => array(
	'ic'=>'fas fullstar',
	'count'=>5,
	'rating'=>5
    ),
    'css' => '',
    'animation_effect' => '',
);

$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$container_class =  apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $fields_args['css']
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
'class' => implode(' ',$container_class),
    )), $fields_args, $mod_name, $element_id);
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
?>
<!-- module star -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'m_t');
	$rates=$fields_args['rates'];
	unset($fields_args);
    ?>
    <div class="tb_star_wrap">
	<?php foreach($rates as $i=>$r):?>
	    <div class="tb_star_item">
		<?php
		    $count =(int)$r['count'];
		    $rating = round((float)$r['rating'],2);
		    $icon=isset($r['ic'])?$r['ic']:'ti-star';
		    $defaultIcon=themify_get_icon($icon);
		    $fillIcon=themify_get_icon($icon,false,false,false,array('class'=>'tb_star_fill'));
		?>
		<?php if(isset($r['text_b'])):?>
		    <span class="tb_star_text_b"<?php self::add_inline_edit_fields('rates',true,true,'text_b')?>><?php echo $r['text_b']?></span>
		<?php endif;?>
		<div class="tb_star_container">
		    <?php for($j=0;$j<$count;++$j){
			if(($rating-$j)>=1){
			echo $fillIcon;
			}
			elseif($rating>$j){
				$decimal =$rating-(int)$rating;
				$gid=$element_id.$i;
			?>
			    <svg width="0" height="0" aria-hidden="true" style="visibility:hidden;position:absolute">
				    <defs>
				    <linearGradient id="<?php echo $gid?>">
					    <stop offset="<?php echo $decimal*100?>%" class="tb_star_fill"/>
					    <stop offset="<?php echo $decimal*100?>%" stop-color="currentColor"/>
				    </linearGradient>
				    </defs>
			    </svg>
			    <?php echo themify_get_icon($icon,false,false,false,array('class'=>'tb_star_half','style'=>'--tb_star_half:url(#'.$gid.')'));
			}
			else{
			    echo $defaultIcon;
			}
		   }?>
		</div>
		<?php if(isset($r['text_a'])):?>
		    <span class="tb_star_text_a"<?php self::add_inline_edit_fields('rates',true,true,'text_a')?>><?php echo $r['text_a']?></span>
		<?php endif;?>
	    </div>
	<?php endforeach;?>
    </div>
</div>
<!-- /module star -->