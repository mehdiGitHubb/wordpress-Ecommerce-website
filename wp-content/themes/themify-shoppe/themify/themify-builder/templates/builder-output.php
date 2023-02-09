<?php

defined( 'ABSPATH' ) || exit;

$args['builder_id'] = (int) $args['builder_id'];
?>
<!--themify_builder_content-->
<div id="themify_builder_content-<?php echo $args['builder_id'] ; ?>" data-postid="<?php echo $args['builder_id'] ; ?>" class="themify_builder_content themify_builder_content-<?php echo $args['builder_id'] ; ?> themify_builder tf_clear<?php if(isset(Themify_Builder_Stylesheet::$generateStyles[$args['builder_id']])):?> tb_generate_css<?php endif;?>"<?php if(isset(Themify_Builder_Stylesheet::$generateStyles[$args['builder_id']])):?> style="visibility:hidden;opacity:0;"<?php endif;?>>
    <?php
    foreach ($args['builder_output'] as $row) {
        if (!empty($row)) {
            Themify_Builder_Component_Row::template($row, $args['builder_id'] , true);
        }
    } // end row loop
    ?>
</div>
<!--/themify_builder_content-->
<?php if(!empty($args['pb_pagination'])): ?>
    <!--themify_builder_pagination-->
    <?php echo $args['pb_pagination']; ?>
    <!--/themify_builder_pagination-->
	<?php endif; ?>
<?php $args=null;?>
