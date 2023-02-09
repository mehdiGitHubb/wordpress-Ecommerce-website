<?php

defined( 'ABSPATH' ) || exit;

$isActive=Themify_Builder::$frontedit_active===true;
$args['builder_id'] = (int) $args['builder_id'];
Themify_Builder_Stylesheet::enqueue_stylesheet( false, $args['builder_id'] );
Themify_Builder::$frontedit_active = false;
$attr = array(
	'class' => 'themify_builder_content themify_builder_content-' . $args['builder_id'] . ' themify_builder not_editable_builder',
	'data-postid' => $args['builder_id'],
);
if ( $ThemifyBuilder->in_the_loop === true ) {
	$attr['class'] .= ' in_the_loop';
}
$post_type = get_post_type( $args['builder_id'] );
if ($isActive===false &&  in_array( $post_type, array( 'tbuilder_layout_part', 'tbp_template' ), true ) && Themify_Builder_Model::is_frontend_editor_page( $args['builder_id'] ) ) {
	$post_type_label = get_post_type_object( $post_type )->labels->singular_name;
	$attr['data-label'] = sprintf( __( 'Edit %1$s<strong>: %2$s</strong>' ), $post_type_label, get_the_title( $args['builder_id'] ) );
}


?>
<?php if($isActive===false && isset($args['l_p'])):?>
<div class="tb_layout_part_wrap tf_w">
<?php endif; ?>
<!--themify_builder_content-->
    <div <?php echo themify_get_element_attributes( $attr ); ?>>
        <?php
        foreach ($args['builder_output'] as $row) :
            if (!empty($row)) {
                Themify_Builder_Component_Row::template($row, $args['builder_id'], true);
            }
        endforeach; // end row loop
        ?>
    </div>
<!--/themify_builder_content-->
<?php if($isActive===false && isset($args['l_p'])):?>
</div>
<?php endif; ?>
<?php if(!empty($args['pb_pagination'])): ?>
    <!--themify_lp_pagination-->
	<?php echo $args['pb_pagination']; ?>
    <!--/themify_lp_pagination-->
<?php endif; ?>
<?php Themify_Builder::$frontedit_active = $isActive;$args=null;?>
