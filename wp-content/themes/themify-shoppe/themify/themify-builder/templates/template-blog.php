<?php

defined('ABSPATH') || exit;

/**
 * Template Post
 * This template can be overridden by copying it to yourtheme/themify-builder/template-blog.php.
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$mod_name=$args['mod_name'];
$fields_default=array(
    'mod_title_' . $mod_name=>'',
    'layout_' . $mod_name=>'grid4',
    'post_type_' . $mod_name=>$mod_name,
    'term_type'=>'category', // Query By option
    'type_query_' . $mod_name=>'category',
    'category_' . $mod_name=>'',
    'query_slug_' . $mod_name=>'',
    'sticky_' . $mod_name=>'no',
    'post_per_page_' . $mod_name=>'',
    'offset_' . $mod_name=>'',
    'order_' . $mod_name=>'desc',
    'orderby_' . $mod_name=>'date',
    'meta_key_' . $mod_name=>'',
    'display_' . $mod_name=>'content',
    'excerpt_length_' . $mod_name=>'',
    'hide_feat_img_' . $mod_name=>'no',
    'image_size_' . $mod_name=>'large',
    'img_width_' . $mod_name=>'',
    'img_height_' . $mod_name=>'',
    'unlink_feat_img_' . $mod_name=>'no',
    'hide_post_title_' . $mod_name=>'no',
    'title_tag_' . $mod_name=>'h2',
    'unlink_post_title_' . $mod_name=>'no',
    'hide_post_date_' . $mod_name=>'no',
    'hide_post_meta_' . $mod_name=>'no',
    'hide_author_' . $mod_name=>'',
    'hide_category_' . $mod_name=>'',
    'hide_comment_' . $mod_name=>'',
    'hide_page_nav_' . $mod_name=>'yes',
    'nav_type'=>'standard',
    'animation_effect'=>'',
    'hide_empty' => 'no',
    'css_' . $mod_name=>'',
    'auto_fullwidth_' . $mod_name=>false
);
$is_ajax_filter=isset($_POST['action']) && $_POST['action']==='themify_ajax_load_more';
if(true===$is_ajax_filter && isset($_POST['tax'])) {
    $cat=!empty($args['mod_settings']['type_query_post']) ? $args['mod_settings']['type_query_post'] : 'category';
    $args['mod_settings'][$cat . '_' . $mod_name]=intval($_POST['tax']);
} else if(isset($args['mod_settings']['category_' . $mod_name])) {
    $args['mod_settings']['category_' . $mod_name]=self::get_param_value($args['mod_settings']['category_' . $mod_name]);
}
$fields_args=wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$builder_id=$args['builder_id'];
$element_id=$args['module_ID'];
if($fields_args['layout_' . $mod_name]==='') {
    $fields_args['layout_' . $mod_name]=$fields_default['layout_' . $mod_name];
}
$fields_default=null;
$container_class=apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $fields_args['css_' . $mod_name]
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false) {
    $container_class[]=$fields_args['global_styles'];
}
if(!empty($fields_args['auto_fullwidth_' . $mod_name]) && $fields_args['auto_fullwidth_' . $mod_name]) {
    $container_class[]='tb_fullwidth_image';
}

$container_props=apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
    'class'=>implode(' ', $container_class),
)), $fields_args, $mod_name, $element_id);

global $wp;
if(true===$is_ajax_filter && isset($_POST['page'])) {
    $p=(int)$_POST['page'];
}
else {
    $p=self::get_paged_query();
}

$order=true===$is_ajax_filter && isset($_POST['order']) ? sanitize_text_field($_POST['order']) : $fields_args['order_' . $mod_name];
$orderby=true===$is_ajax_filter && isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : $fields_args['orderby_' . $mod_name];
$meta_key=$fields_args['meta_key_' . $mod_name];
$limit=$fields_args['post_per_page_' . $mod_name];
if(empty($limit)) {
	$limit=get_option('posts_per_page');
}
$mod_name_query=isset($fields_args['term_type']) && $fields_args['term_type']==='post_slug' ? $fields_args['term_type'] : $fields_args['type_query_' . $mod_name];
$offset=$fields_args['offset_' . $mod_name];
$args=array(
	'post_status'=>'publish',
	'posts_per_page'=>$limit,
	'ptb_disable'=>true,
	'order'=>$order,
	'orderby'=>$orderby,
	'paged'=>$p,
	'post_type'=>$fields_args['post_type_' . $mod_name],
	'ignore_sticky_posts'=>true
);

if('all'===$fields_args['term_type']) {
	if($fields_args["sticky_{$mod_name}"]==='yes') {
		$args['ignore_sticky_posts']=false;
	}
	$query_taxonomy=$mod_name!=='post' ? $mod_name . '-category' : 'category';
}
elseif('post_slug'===$fields_args['term_type']) {
	if(!empty($fields_args['query_slug_' . $mod_name])) {
		$args['post__in']=Themify_Builder_Model::parse_slug_to_ids($fields_args['query_slug_' . $mod_name], $args['post_type']);
	}
}
else {
	$terms=$mod_name==='post' && isset($fields_args["{$mod_name_query}_post"]) ? $fields_args["{$mod_name_query}_post"] : $fields_args['category_' . $mod_name];
	$query_taxonomy=$mod_name!=='post' ? $mod_name . '-category' : $mod_name_query;
	Themify_Builder_Model::parseTermsQuery($args, $terms, $query_taxonomy);
	$mod_name_query=$query_taxonomy;
}

/* backward compatibility, since Sep 2022 */
if ( $orderby === 'meta_value_num' ) {
	$orderby = 'meta_value';
	$fields_args['meta_key_type'] = 'NUMERIC';
}
/* end backward compatibility */

if ( ! empty( $meta_key ) && $orderby === 'meta_value' ) {
	$args['meta_key'] = $meta_key;
	if ( ! empty( $fields_args['meta_key_type'] ) ) {
		$args['meta_type'] = $fields_args['meta_key_type'];
	}
}

// add offset posts
if($offset!=='') {
	$args['offset']=(($p-1) * $limit)+$offset;
}
// Exclude the current post
if($mod_name==='post' && !isset($args['post__in']) && false!==($id=get_the_ID()) && is_single($id)) {
	$args['post__not_in']=array($id);
}

Themify_Builder_Model::parse_query_filter($fields_args, $args);
$post_filter_enabled=isset($fields_args[$mod_name.'_filter'])?$fields_args[$mod_name.'_filter']:(isset($fields_args['post_filter'])?$fields_args['post_filter']:false);
$post_filter_enabled=!empty($post_filter_enabled) && $post_filter_enabled!=='no';
$ajax_filter_enabled = false;
if(true===$post_filter_enabled && $fields_args['layout_' . $mod_name]!=='auto_tiles') {
    $ajax_filter_enabled=isset($fields_args['ajax_filter']) && $fields_args['ajax_filter']==='yes';
    if ( $ajax_filter_enabled ) {
	    /* in Ajax post filters, disable some query args */
	    unset( $args['post__in'] );
	    set_query_var('tf_ajax_filter', true);
    }
}
$args=apply_filters("themify_builder_module_{$mod_name}_query_args", $args, $fields_args);
if ( isset( $query_taxonomy ) ) {
	set_query_var('tf_query_tax', $query_taxonomy);
}
$the_query=self::query($args);
$posts=$the_query->posts;

if ( empty( $posts ) && $fields_args['hide_empty'] === 'yes' && Themify_Builder::$frontedit_active === false ) {
	return;
}

add_filter('themify_after_post_title_parse_args', array('Themify_Builder_Component_Module', 'post_title_tag'));

Themify_Builder_Model::hook_content_start($fields_args);

?>
<!-- module post -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
    <?php
    $container_props=$container_props=null;
    echo Themify_Builder_Component_Module::get_module_title($fields_args, 'mod_title_' . $mod_name);

    // The Query
    do_action('themify_builder_before_template_content_render');

    $class=array('builder-posts-wrap', 'loops-wrapper');
    if(!empty($fields_args['post_type_' . $mod_name]) && $fields_args['post_type_' . $mod_name]!=='post') {
	$class[]=$fields_args['post_type_' . $mod_name];
    }
    if(($fields_args['layout_' . $mod_name]==='auto_tiles' || in_array('auto_tiles', $class, true)) && !in_array('masonry', $class, true)) {
	$count=count($posts);
	if($count===5 || $count===6) {
	    $class[]='tf_tiles_' . $count;
	} else {
	    $class[]='tf_tiles_more';
	}
    }
    $class[]=apply_filters('themify_builder_module_loops_wrapper', $fields_args['layout_' . $mod_name], $fields_args, $mod_name);//deprecated backward compatibility
    global $themify;
    if(isset($themify) && !empty($posts)) {
	// save a copy
	$themify_save=clone $themify;
	// override $themify object
	$themify->builder_post_module=$mod_name;
	$themify->hide_image=$fields_args['hide_feat_img_' . $mod_name];
	$themify->unlink_image=$fields_args['unlink_feat_img_' . $mod_name];
	$themify->hide_title=$fields_args['hide_post_title_' . $mod_name];
	$themify->themify_post_title_tag=$fields_args['title_tag_' . $mod_name];
	$themify->width=$fields_args['img_width_' . $mod_name];
	$themify->height=$fields_args['img_height_' . $mod_name];
	$themify->image_size=$fields_args['image_size_' . $mod_name];
	$themify->unlink_title=$fields_args['unlink_post_title_' . $mod_name];
	$themify->display_content=$fields_args['display_' . $mod_name];
	if($fields_args['display_' . $mod_name]==='excerpt' && !empty($fields_args['excerpt_length_' . $mod_name])) {
	    $themify->excerpt_length=$fields_args['excerpt_length_' . $mod_name];
	    add_filter("the_excerpt", "themify_custom_except", 999);
	}
	$themify->hide_date=$fields_args['hide_post_date_' . $mod_name];
	$themify->hide_meta=$fields_args['hide_post_meta_' . $mod_name];
	if($fields_args['hide_post_meta_' . $mod_name]!=='yes'){
	    if($fields_args['hide_author_' . $mod_name]==='yes'){
		$themify->hide_meta_author='yes';
	    }
	    if($fields_args['hide_category_' . $mod_name]==='yes'){
		$themify->hide_meta_category='yes';
		$themify->hide_meta_tag='yes';
	    }
	    if($fields_args['hide_comment_' . $mod_name]==='yes'){
		$themify->hide_meta_comment='yes';
	    }
	}
	$themify->post_layout=$fields_args['layout_' . $mod_name];
	if('auto_tiles'===$themify->post_layout || !empty($fields_args[$mod_name . '_content_layout']) && in_array($fields_args[$mod_name . '_content_layout'], array(
		'polaroid',
		'flip'
	    ))) {
	    $themify->media_position='above';
	}
	if('post'===$mod_name) {
	    $themify->post_module_hook=$mod_name;
	    if(isset($query_taxonomy)) {
		$themify->post_module_tax=$query_taxonomy;
	    }
	    if(isset($fields_args[$mod_name . '_content_layout'])) {
		$themify->post_layout_type=$fields_args[$mod_name . '_content_layout'];
	    }
	}
    }
    if(true===$post_filter_enabled && isset($query_taxonomy) && function_exists('themify_masonry_filter')) {
	if(isset($themify)){
	    $themify->post_filter='yes';
	}

	$filter_args=array(
	    'query_taxonomy'=>$query_taxonomy,
	    'query_category'=>'0',
	    'el_id'=>$element_id
	);
	if(isset($fields_args['filter_hashtag']) && $fields_args['filter_hashtag']==='yes'){
	    $filter_args['hash_tag']=true;
	}
	if(isset($ajax_filter_enabled) && true===$ajax_filter_enabled) {
	    $filter_args['ajax_filter']='yes';
	    $filter_args['ajax_filter_id']=$builder_id;
	    $filter_args['ajax_filter_paged']=$args['paged'];
	    $filter_args['ajax_filter_limit']=$args['posts_per_page'];
	    $fields_args['nav_type']='ajax';
			    $cat_filter = !empty($fields_args['ajax_filter_categories']) ? $fields_args['ajax_filter_categories'] : '';
			    if(('exclude' === $cat_filter || 'include' === $cat_filter) && !empty($fields_args['ajax_filter_'.$cat_filter])) {
				    $filter_args['ajax_filter_'.$cat_filter]=sanitize_text_field($fields_args['ajax_filter_'.$cat_filter]);
			    }
	    if(isset($fields_args['ajax_sort']) && $fields_args['ajax_sort']==='yes') {
		$filter_args['ajax_sort']='yes';
		$filter_args['ajax_sort_order']=$args['order'];
		$filter_args['ajax_sort_order_by']=$args['orderby'];
	    }
	}
	themify_masonry_filter($filter_args);
	unset($filter_args);
	$class[]='masonry';
    }
    $class=apply_filters('themify_loops_wrapper_class', $class, $fields_args['post_type_' . $mod_name], $fields_args['layout_' . $mod_name], 'builder', $fields_args, $mod_name);
    $class[]='tf_clear';
    $class[]='tf_clearfix';
    $container_props=apply_filters('themify_builder_blog_container_props', array(
	'class'=>$class
    ), $fields_args['post_type_' . $mod_name], $fields_args['layout_' . $mod_name], $fields_args, $mod_name);
    if('ajax'===$fields_args['nav_type'] || true===$is_ajax_filter) {
	$container_props['class'][]='tb_ajax_pagination';
	$container_props['data-id']=$element_id;
    }
    if(Themify_Builder::$frontedit_active===false) {
	$container_props['data-lazy']=1;
    }
    if(in_array('masonry',$container_props['class']) && !empty($fields_args['masonry_align']) && 'yes'===$fields_args['masonry_align']){
	$container_props['data-layout']='fitRows';
    }
    $container_props['class']=implode(' ', $container_props['class']);
    unset($class);
    ?>
    <div <?php echo self::get_element_attributes($container_props); ?>>
	<?php
	unset($container_props);
	if(!empty($posts)) {
	$isLoop=$ThemifyBuilder->in_the_loop===true;

	// if the active theme is using Themify framework use theme template loop (includes/loop.php file)
	if(themify_is_themify_theme() && ($mod_name==='post' || Themify_Builder_Model::is_loop_template_exist('loop-' . $mod_name . '.php', 'includes'))) {

	    // hooks action
	    do_action_ref_array('themify_builder_override_loop_themify_vars', array(
		$themify,
		$mod_name,
		$fields_args
	    ));
	    $ThemifyBuilder->in_the_loop=true;
	    echo themify_get_shortcode_template($posts, 'includes/loop', $mod_name);
	} else {
	// use builder template
	global $post;
	$temp_post=$post;
	$param_image=array(
	    'w'=>$fields_args['img_width_' . $mod_name],
	    'h'=>$fields_args['img_height_' . $mod_name]
	);
	if($fields_args['image_size_' . $mod_name]!=='') {
	    $param_image['image_size']=$fields_args['image_size_' . $mod_name];
	}
	$cl='post tf_clearfix';
	if($mod_name!=='post') {
	    $cl.=' ' . $mod_name . '-post';
	}
	$is_comment_open=themify_builder_get('setting-comments_posts');
	$ThemifyBuilder->in_the_loop=true;
	foreach($posts

	as $post):
	setup_postdata($post);
	?>

	<?php themify_post_before(); // hook   ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class($cl); ?>>

	    <?php themify_post_start(); // hook   ?>

	    <?php
	    if($fields_args['hide_feat_img_' . $mod_name]!=='yes') {

		//check if there is a video url in the custom field
		if($vurl=themify_builder_get('video_url', false, false)) {
		    global $wp_embed;

		    themify_before_post_image(); // Hook

		    echo $wp_embed->run_shortcode('[embed]' . esc_url($vurl) . '[/embed]');

		    themify_after_post_image(); // Hook
		} elseif($post_image=themify_get_image($param_image)) {

		    themify_before_post_image(); // Hook
		    ?>

		    <figure class="post-image">
			<?php if($fields_args['unlink_feat_img_' . $mod_name]==='yes'): ?>
			    <?php echo $post_image; ?>
			<?php else: ?>
			    <a <?php themify_permalink_attr(); ?>><?php echo $post_image; ?></a>
			<?php endif; ?>
		    </figure>

		    <?php
		    themify_after_post_image(); // Hook
		}
	    }
	    ?>

	    <div class="post-content">

		<?php if($fields_args['hide_post_date_' . $mod_name]!=='yes'): ?>
		    <time datetime="<?php the_time('o-m-d') ?>" class="post-date"
			  pubdate><?php echo get_the_date(apply_filters('themify_loop_date', '')) ?></time>
		<?php endif; //post date   ?>

		<?php if($fields_args['hide_post_title_' . $mod_name]!=='yes'): ?>
		<?php themify_before_post_title(); // Hook ?>
		<<?php echo $fields_args['title_tag_' . $mod_name]; ?> class="post-title">
		<?php if($fields_args['unlink_post_title_' . $mod_name]==='yes'): ?>
		    <?php the_title(); ?>
		<?php else: ?>
		    <a <?php themify_permalink_attr(); ?>><?php the_title(); ?></a>
		<?php endif; //unlink post title    ?>
	    </<?php echo $fields_args['title_tag_' . $mod_name]; ?>>
	<?php themify_after_post_title(); // Hook ?>
	<?php endif; //post title  ?>

	    <?php if($fields_args['hide_post_meta_' . $mod_name]!=='yes'): ?>
		<p class="post-meta">
		    <span class="post-author"><?php the_author_posts_link() ?></span>
		    <span class="post-category">
								    <?php if($mod_name==='post') :
				    echo get_the_category_list(', ', '', $post->ID);
				else :
				    $terms=wp_get_post_terms($post->ID, $mod_name . '-category', array('fields'=>'names'));
				    if(!is_wp_error($terms) && !empty($terms)) {
					echo implode(', ', $terms);
				    }
				endif;
				?>
								    </span>
		    <?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
		    <?php if(!$is_comment_open && comments_open()) : ?>
			<span class="post-comment"><?php comments_popup_link(__('0 Comments', 'themify'), __('1 Comment', 'themify'), __('% Comments', 'themify')); ?></span>
		    <?php endif; //post comment    ?>
		</p>
	    <?php endif; //post meta   ?>

	    <?php
	    if($fields_args['display_' . $mod_name]!=='none') {
		// fix the issue more link doesn't output
		global $more;
		$more=0;
		themify_before_post_content();
		if($fields_args['display_' . $mod_name]==='excerpt') {
		    the_excerpt();
		} else {
		    $moreText=themify_builder_get('setting-default_more_text');
		    if(!$moreText) {
			$moreText=__('More &rarr;', 'themify');
		    }
		    the_content($moreText);
		}
		themify_after_post_content();
	    }
	    themify_edit_link();
	    ?>
	    <?php if($mod_name==='testimonial'): ?>
		<p class="testimonial-author">
		    <?php
		    echo themify_builder_testimonial_author_name($post, 'yes');
		    ?>
		</p>
	    <?php endif; ?>

    </div>
    <!-- /.post-content -->
<?php themify_post_end(); // hook    ?>

    </article>
<?php themify_post_after(); // hook     ?>

<?php
endforeach;


wp_reset_postdata();
$post=$temp_post;
} // end $is_theme_template
$ThemifyBuilder->in_the_loop=$isLoop;
if(isset($themify_save)) {
    // revert to original $themify state
    $themify=clone $themify_save;
    unset($themify_save);
}
if($fields_args['display_' . $mod_name]==='excerpt' && !empty($fields_args['excerpt_length_' . $mod_name])) {
    remove_filter("the_excerpt", "themify_custom_except", 999);
}
} else{
	    if ( isset( $fields_args['no_posts'], $fields_args['no_posts_msg'] ) ) {
		    echo '<div class="tb_no_posts">' . $fields_args['no_posts_msg'] . '</div>';
	    } else if(current_user_can('publish_posts') && true!==$is_ajax_filter) {
		    printf(__('No posts found matching the query. <a href="%s" target="_blank">Click here</a> to add posts.', 'themify'), $args['post_type']!=='post' ? admin_url('post-new.php?post_type=' . $args['post_type']) : admin_url('post-new.php'));
	    }
}
?>
</div><!-- .builder-posts-wrap -->
<?php if('yes'!==$fields_args['hide_page_nav_' . $mod_name]): ?>
    <?php if('ajax'===$fields_args['nav_type']): ?>
        <?php
        if($the_query->max_num_pages >$args['paged']) {
	    if(Themify_Builder::$frontedit_active === false){
		Themify_Enqueue_Assets::loadinfiniteCss();
	    }
            $url=is_single()?add_query_arg( array('tf-page'=>($args['paged']+1)), get_permalink( get_queried_object_id() ) ):next_posts($the_query->max_num_pages, false );
            echo '<p class="tf_load_more tf_textc tf_clear"><a data-id="' . $element_id . '" href="' . $url . '" data-page="' . esc_attr($args['paged']) . '" class="load-more-button">' . __('Load More', 'themify') . '</a></p>';
        }
        ?>
    <?php else: ?>
        <?php echo self::get_pagination('', '', $the_query, $offset) ?>
    <?php endif; ?>
<?php endif; ?>
<?php
do_action('themify_builder_after_template_content_render');
?>
    </div>
    <!-- /module post -->
<?php remove_filter('themify_after_post_title_parse_args', array(
    'Themify_Builder_Component_Module',
    'post_title_tag'
));
Themify_Builder_Model::hook_content_end($fields_args);
