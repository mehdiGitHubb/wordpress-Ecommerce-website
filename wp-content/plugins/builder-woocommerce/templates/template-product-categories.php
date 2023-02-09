<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
/**
 * Template Product Categories
 *
 * Access original fields: $args['mod_settings']
 */
if (themify_is_woocommerce_active()):

$fields_default = array(
	'mod_title' => '',
	'child_of' => 0,
	'columns' => 4,
	'orderby' => 'name',
	'order' => 'ASC',
	'exclude' => '',
	'number' => '',
    'image_w' => '',
    'image_h' => '',
	'hide_empty' => 'yes',
	'pad_counts' => 'yes',
	'cat_desc' => 'no',
	'display' => 'products',
	'latest_products' => 6,
	'subcategories_number' => '',
	'animation_effect' => '',
	'css_products' => '',
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$hide_empty = $fields_args['hide_empty'] === 'yes';

// get terms and workaround WP bug with parents/pad counts
$query_args = array(
	'taxonomy'=>'product_cat',
	'orderby' => $fields_args['orderby'],
	'order' => $fields_args['order'],
	'hide_empty' => $hide_empty,
	'pad_counts' => true,
	'number' => $fields_args['number'],
);
if ('top-level' === $fields_args['child_of']) {
	$query_args['parent'] = 0; /* show only top-level terms */
}
else if (0 != $fields_args['child_of']) {
    $query_args['child_of'] = $fields_args['child_of'];
}

if ( ! empty( $fields_args['exclude'] ) ) {
	$query_args['exclude'] = $fields_args['exclude'];
}

// check if we have to query the slug, instead of ID
// keep option to query by ID, for backward compatibility
if ('top-level' !== $fields_args['child_of'] && preg_match('/\D/', $fields_args['child_of'])) {
	$term = get_term_by('slug', $fields_args['child_of'], 'product_cat');
	if ( ! is_wp_error( $term ) && isset( $term->term_id ) ) {
		$fields_args['child_of'] = $query_args['child_of'] = $term->term_id;
	}
}

$product_categories = get_terms($query_args);

if (empty($product_categories) && 'top-level' !== $fields_args['child_of'] && 0 != $fields_args['child_of']) {
	$query_args['child_of'] = false;
	$query_args['term_taxonomy_id'] = $fields_args['child_of'];
	$product_categories = get_terms($query_args);
}

if ($hide_empty===true) {
	foreach ($product_categories as $key => $category) {
		if ($category->count === 0) {
			unset($product_categories[$key]);
		}
	}
}
/* backward compatibility to handle how Latest Products option worked */
if ( $fields_args['display'] === 'products' && $fields_args['latest_products'] == 0 ) {
	$fields_args['display'] = 'none';
}

$container_class = apply_filters('themify_builder_module_classes', array(
	'module','woocommerce', 'module-' . $args['mod_name'], $args['module_ID'], $fields_args['css_products'], 'shows_' . $fields_args['display']
	), $args['mod_name'], $args['module_ID'], $fields_args);
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	'id' => $args['module_ID'],
	'class' => implode(' ',$container_class),
)), $fields_args, $args['mod_name'], $args['module_ID']);


if ( Themify_Builder::$frontedit_active===false){
	$container_props['data-lazy']=1;
	$is_inline_edit_supported=function_exists('themify_make_lazy');
}
else{
	$is_inline_edit_supported=false;
}
$hasInline=method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
?>
<!-- module product categories -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php 
		$container_props=$container_class=null;
		if($hasInline===true){
			echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title');
		}
		elseif ($fields_args['mod_title'] !== ''){
			echo $fields_args['before_title'] , apply_filters('themify_builder_module_title', $fields_args['mod_title'], $fields_args) , $fields_args['after_title'];
		}
		do_action('themify_builder_before_template_content_render'); 
		?>
<?php	if (!empty($product_categories)):
	    $layout = is_numeric($fields_args['columns'])?($fields_args['columns']>1?'grid'.$fields_args['columns']:'list-post'):$fields_args['columns'];
			$class=array('loops-wrapper','products');
			$class=apply_filters('themify_loops_wrapper_class', $class, 'product', $layout, 'builder', $fields_args, $args['mod_name']);
			$class[]='tf_clear';
            if ( !empty($fields_args['image_w']) || !empty($fields_args['image_h']) ) {
                add_image_size( 'themify_wc_category_thumbnail', (int)$fields_args['image_w'], (int)$fields_args['image_h'] );
                add_filter('subcategory_archive_thumbnail_size',array('TB_Product_Categories_Module','set_category_image_size'));
                $custom_size=true;
                add_filter('wp_get_attachment_image_src', array('TB_Product_Categories_Module','change_category_image_size'),10,4);
            }
			?>
			<ul class="<?php echo implode(' ',$class)?>">
				<?php 
				unset($class);
				foreach ( $product_categories as $category ) : ?>
					<li class="product-category product">

						<?php 
						woocommerce_template_loop_category_link_open($category);
						woocommerce_subcategory_thumbnail($category);
						woocommerce_template_loop_category_link_close();
						
						if ( $fields_args['display'] === 'products' ) {
							$query = wc_get_products(
									array(
										'post_type' => 'product', 
										'status' => 'publish',
										'limit' => $fields_args['latest_products'], 
										'visibility' => 'catalog',
										'category' => $category->slug,
										'paginate'=>false,
										'order'=>'DESC',
										'orderby'=>'date',
										'no_found_rows'=>true
										)
							);
							if (!empty($query)) :
								?>
								<div class="product-thumbs">
									<?php foreach ($query as $p) : ?>
										<?php $permalink=$p->is_visible() ? $p->get_permalink( $p ) : '';?>
										<div class="post">
											<?php if(!empty($permalink)):?>
												<a href="<?php echo $permalink; ?>">
											<?php endif;?>
												<?php $thumb=$p->get_image('shop_catalog'); 
													if($is_inline_edit_supported===true){
														$thumb=themify_make_lazy($thumb,false);
													}
													echo $thumb;
												?>
											<?php if(!empty($permalink)):?>
												</a>
											<?php endif;?>
										</div>
									<?php endforeach; ?>
								</div>
								<?php
							endif;
							unset($query);
						}
						?>
						<?php woocommerce_template_loop_category_link_open($category); ?>
						<h3>
							<?php
							echo $category->name;

							if ('yes' === $fields_args['pad_counts'] && $category->count > 0)
								echo apply_filters('woocommerce_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category);
							?>
						</h3>
						<?php woocommerce_template_loop_category_link_close(); ?>

						<?php if ( $fields_args['display'] === 'subcategories' ) {
							$sub_categories = get_terms(array(
								'taxonomy'=>'product_cat',
								'orderby' => $fields_args['orderby'],
								'order' => $fields_args['order'],
								'hide_empty' => $hide_empty,
								'pad_counts' => true,
								'no_found_rows'=>true,
								'number' => $fields_args['subcategories_number'],
								'parent' => $category->term_id
							) );
							if ( ! empty( $sub_categories ) ) : ?>
								<ul>
									<?php foreach ( $sub_categories as $sub_category ) : ?>
										<li>
											<?php woocommerce_template_loop_category_link_open( $sub_category ); ?>
												<?php echo $sub_category->name;
													if ( 'yes' === $fields_args['pad_counts'] && $sub_category->count > 0 )
														echo apply_filters('woocommerce_subcategory_count_html', ' <mark class="count">(' . $sub_category->count . ')</mark>', $sub_category);
												?>
											<?php woocommerce_template_loop_category_link_close(); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						<?php } ?>
                        <?php if ('yes' === $fields_args['cat_desc'] && $category->description !== ''): ?>
                            <div><?php echo esc_attr($category->description); ?></div>
                        <?php endif ?>
					</li>
					<?php
				endforeach;
				?>
			</ul>
            <?php
            if (isset($custom_size) ) {
                remove_filter('wp_get_attachment_image_src', array('TB_Product_Categories_Module','change_category_image_size'),10);
                remove_filter('subcategory_archive_thumbnail_size',array('TB_Product_Categories_Module','set_category_image_size'));
                remove_image_size( 'themify_wc_category_thumbnail' );
            }
            ?>
		<?php endif; ?>
		<?php woocommerce_reset_loop(); ?>
	<?php do_action('themify_builder_after_template_content_render'); ?>
</div>
<!-- module product categories -->
<?php endif;?>