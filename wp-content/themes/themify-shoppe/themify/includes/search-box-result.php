<?php
global $query,$found_types;
if( $query->have_posts()):
    ?>
    <ul class="tf_search_tab">
        <li class="active"><a href="#all"><?php _e('All','themify')?></a></li>
        <?php foreach ($found_types as $type): ?>
            <?php
            switch ($type){
                case 'product':
                    $title=__('Shop','themify');
                    break;
                case 'post':
                    $title=__('Blog','themify');
                    break;
                case 'page':
                    $title=__('Page','themify');
                    break;
                default:
                    $type_obj = get_post_type_object( $type );
                    $title=$type_obj->labels->singular_name;
                    $type_obj=null;
                    break;
            }
            ?>
            <li><a href="#<?php echo $type ?>"><?php echo $title; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php $is_disabled = themify_is_image_script_disabled();?>
    <?php  while ( $query->have_posts() ):?>
    <?php
    $query->the_post();
    $post_type  = get_post_type();
    $is_product = $post_type === 'product';
    if(has_post_thumbnail()){
        $post_image = $is_disabled===false?themify_get_image(array('w'=>47,'h'=>48,'crop'=>true,'urlonly'=>true)):'';
        if(!$post_image){
            $post_image = $is_product===true?get_the_post_thumbnail_url( null,'shop_thumbnail'):get_the_post_thumbnail_url( null,'thumbnail');
        }
    }
    else{
        $post_image = false;
    }
    ?>
    <div class="tf_search_item tf_search_<?php echo $post_type; ?> tf_rel tf_overflow tf_clear">
        <a href="<?php the_permalink()?>" class="tf_clearfix">
            <?php if($post_image!==false):?>
                <img src="<?php echo $post_image;?>" width="47" height="48" />
            <?php endif;?>
            <span class="title"><?php the_title()?></span>
            <?php if($is_product===true):?>
                <?php global $product?>
                <span class="price"><?php echo $product->get_price_html()?></span>
            <?php endif;?>
        </a>
    </div>
    <!-- /tf_search_item -->
<?php endwhile;?>

    <?php if($query->max_num_pages>1):?>
    <div class="tf_view_all tf_textc tf_clear">
        <?php $search_link = get_search_link($_POST['s']);?>
        <?php foreach ($found_types as $type): ?>
            <?php $type_obj = get_post_type_object( $type ); ?>
            <a id="tf_result_link_<?php echo $type; ?>" href="<?php echo add_query_arg(array('type'=>$type),$search_link)?>" class="tf_view_button tf_hide"><?php echo __('View All','themify').' '.$type_obj->label; ?></a>
        <?php endforeach; ?>
        <a id="tf_result_link_item" href="<?php echo $search_link ?>" class="tf_view_button tf_hide"><?php _e('View All','themify')?></a>
    </div>
    <!-- /tf_view_all -->
<?php endif;?>
<?php else:?>
    <p><?php _e('No Items Found','themify');?></p>
<?php endif;?>
