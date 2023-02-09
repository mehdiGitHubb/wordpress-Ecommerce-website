<?php

class WPF_Form {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;
    protected $themplate_id = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     *
     */
    public function __construct($plugin_name, $version, $themplate_id = false) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->themplate_id = $themplate_id;
    }

    private function add_fields($data = array()) {
        $layouts = array(
            'vertical' => __('Vertical Layout', 'wpf'),
            'horizontal' => __('Horizontal Layout', 'wpf')
        );
        $pagination = array(
            'pagination' => __('Standard Pagination', 'wpf'),
            'infinity_auto' => __('Infinite Scroll', 'wpf'),
            'infinity' => __('Load More', 'wpf')
        );
        ?>
        <div class="wpf_lightbox_row ">
            <div class="wpf_lightbox_label"><label for="wpf_name"><?php _e('Form Title', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input id="wpf_name" class="wpf_towidth" type="text" value="<?php echo!empty($data['name']) ? $data['name'] : '' ?>" name="name" />
            </div>
        </div>
        <div class="wpf_lightbox_row ">
            <div class="wpf_lightbox_label"><?php _e('Layout', 'wpf'); ?></div>
            <div class="wpf_lightbox_input wpf_grid wpf_changed">
                <?php foreach ($layouts as $id => $ch): ?>
                    <input id="wpf_<?php echo $id ?>" type="radio" value="<?php echo $id; ?>" name="type" <?php if ((!$data && $id === 'vertical' ) || ( isset($data['type']) && $data['type'] == $id )): ?>checked="checked"<?php endif; ?>/>
                    <label title="<?php echo $ch ?>" for="wpf_<?php echo $id ?>" class="wpf_grid_<?php echo $id; ?>"></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_empty_fields"><?php _e('Empty Fields', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if ((!isset($data['empty']) && empty($data)) || !empty($data['empty'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="empty" value="1" id="wpf_empty_fields"/>
                <label for="wpf_empty_fields"><?php _e('Do not show field if empty', 'wpf') ?></label>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_sort_fields"><?php _e('Product Sorting', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if (!empty($data['sort'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="sort" value="1" id="wpf_sort_fields"/>
                <label for="wpf_sort_fields"><?php _e('Hide product sorting', 'wpf') ?></label>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_result_fields"><?php _e('Product Count', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if (!empty($data['result'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="result" value="1" id="wpf_result_fields"/>
                <label for="wpf_result_fields"><?php _e('Hide result product count', 'wpf') ?></label>
            </div>
        </div>
		<div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_out_of_stock_fields"><?php _e('Out of Stock', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if (!empty($data['out_of_stock'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="out_of_stock" value="1" id="wpf_out_of_stock_fields"/>
                <label for="wpf_out_of_stock_fields"><?php _e('Do not show out of stock products', 'wpf') ?></label>
            </div>
        </div>
        <div class="wpf_lightbox_row wpf_changed">
            <div class="wpf_lightbox_label"><label for="wpf_pagination_fields"><?php _e('Pagination', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if (!empty($data['pagination'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="pagination" value="1" id="wpf_pagination_fields"/>
                <label for="wpf_pagination_fields"><?php _e('Hide Pagination', 'wpf') ?></label>
            </div>
        </div>
        <div class="wpf_lightbox_row wpf_infinity wpf_changed">
            <div class="wpf_lightbox_label"><label for="wpf_pagination_type_fields"><?php _e('Pagination Option', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <?php foreach ($pagination as $k => $v): ?>
                    <label for="wpf_<?php echo $k ?>_type_fields">
                        <input <?php if ((isset($data['pagination_type']) && $data['pagination_type'] === $k) || (empty($data['pagination_type']) && $k === 'pagination')): ?>checked="checked"<?php endif; ?>
                        type="radio" name="pagination_type" value="<?php echo $k ?>" id="wpf_<?php echo $k ?>_type_fields"/><?php echo $v ?>
                    </label>
                <?php endforeach; ?>

            </div>
        </div>
        <div class="wpf_lightbox_row wpf_infinity_buffer">
            <div class="wpf_lightbox_label"><label for="wpf_pagination_type_fields">&nbsp;</label></div>
            <div class="wpf_lightbox_input">
                <input type="text" name="infinitybuffer" value="<?php echo! empty( $data['infinitybuffer'] ) ? esc_attr( $data['infinitybuffer'] ) : '' ?>" id="wpf_infinity_buffer_fields" /> <?php _e( 'Infinite scroll trigger point (px)' ) ?><br><small><?php _e( 'Default is 300, higher number means infinite scroll will trigger earlier.' ) ?></small>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_posts_per_page_fields"><?php _e('Products Per Page', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input type="text" name="posts_per_page" value="<?php echo!empty($data['posts_per_page']) ? intval($data['posts_per_page']) : '' ?>" id="wpf_posts_per_page_fields"/>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_group_fields"><?php _e('Toggle Field Groups', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input
                    <?php if (!empty($data['group'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="group" value="1" id="wpf_group_fields"/>
                <label for="wpf_group_fields"><?php _e('Allow field groups toggle-able', 'wpf') ?></label>
            </div>
        </div>
        <div class="wpf_lightbox_row wpf_result_page_wrapper">
            <div class="wpf_lightbox_label"><label><?php _e('Reset Button', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <ul>
                    <li>
                        <input id="wpf_bottom_reset_button" type="radio" value="bottom" name="reset_button"
							   <?php if ( !empty( $data['reset_button'] ) && 'bottom' === $data['reset_button'] ): ?>checked="checked"<?php endif; ?>>
                        <label for="wpf_bottom_reset_button"><?php _e( 'Add reset button at bottom', 'wpf' ); ?></label>
                    </li>
                    <li>
                        <input id="wpf_group_reset_button" type="radio" value="group" name="reset_button"
							   <?php if ( !empty( $data['reset_button'] ) && 'group' === $data['reset_button'] ): ?>checked="checked"<?php endif; ?>>
                        <label for="wpf_group_reset_button"><?php _e( 'Add reset button at every field group', 'wpf' ); ?></label>
                    </li>
                    <li>
                        <input id="wpf_no_reset_button" type="radio" value="no" name="reset_button"
							   <?php if ( empty( $data['reset_button'] ) || (!empty( $data['reset_button'] ) && 'no' === $data['reset_button']) ): ?>checked="checked"<?php endif; ?>>
                        <label for="wpf_no_reset_button"><?php _e( 'No reset button', 'wpf' ); ?></label>
                    </li>
                </ul>
            </div>
        </div>
        <div class="wpf_lightbox_row ">
            <div class="wpf_lightbox_label"><label for="wpf_clear_label"><?php _e('Reset Label', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
				<?php WPF_Utils::module_language_tabs( 'clear_label', $data, WPF_Utils::get_all_languages(), null, 'text', __('Reset', 'wpf'), true ); ?>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_scroll_fields"><?php _e('Scroll To Result', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if (!empty($data['scroll'])): ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="scroll" value="1" id="wpf_scroll_fields"/>
                <label for="wpf_scroll_fields"><?php _e('Yes', 'wpf') ?></label>
            </div>
        </div>
        <?php
        $relations = array(
            'and' => __('AND', 'wpf'),
            'or' => __('OR', 'wpf')
        );
        ?>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_tax_relation_or"><?php _e('Logical Relationship Between Taxonomies', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <?php foreach ($relations as $k => $v): ?>
                    <label for="wpf_tax_relation_<?php echo $k ?>">
                        <input 
                            <?php if (isset($data['tax_relation']) && $data['tax_relation'] === $k || (!isset($data['tax_relation']) && $k === 'or')): ?>checked="checked"<?php endif; ?>
                            type="radio" name="tax_relation" value="<?php echo $k ?>" id="wpf_tax_relation_<?php echo $k ?>"/>
                            <?php echo $v ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC'
        ));
        $result_types = array(
            'same_page' => __('Show results on the same page', 'wpf'),
            'diff_page' => __('Show results on a different page', 'wpf')
                )
        ?>
        <div class="wpf_lightbox_row wpf_result_page_wrapper wpf_changed">
            <div class="wpf_lightbox_label"><label for="wpf_result_page"><?php _e('Result Page Template', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <ul>
                    <?php foreach ($result_types as $id => $ch): ?>
                        <li>
                            <input id="wpf_<?php echo $id ?>" type="radio" value="<?php echo $id; ?>" name="result_type" <?php if ((!$data && $id === 'same_page' ) || ( isset($data['result_type']) && $data['result_type'] == $id )): ?>checked="checked"<?php endif; ?>/>
                            <label for="wpf_<?php echo $id ?>"><?php echo $ch ?></label>
                        </li>
                    <?php endforeach; ?>
                    <li class ="wpf_result_page_select">
                        <input id="wpf_show_form_in_results" type="checkbox" value="show_form_in_results" name="show_form_in_results" <?php if (isset($data['show_form_in_results'])): ?> checked="checked"<?php endif; ?>/>
                        <label for="wpf_show_form_in_results"><?php _e('Show product filter form on search result page.', 'wpf') ?></label>
                    </li>
                </ul>
                <div class="wpf_result_page_select">
                    <div class="wpf_custom_select">
                        <select name="page" id="wpf_result_page">
                            <?php if (!empty($pages)): ?>
                                <?php foreach ($pages as $p): ?>
                                    <option <?php if (!empty($data['page']) && $data['page'] == $p->ID): ?>selected="selected"<?php endif; ?> value="<?php echo $p->ID ?>"><?php echo $p->post_title ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div><br>
                    <label for="wpf_result_page"><?php _e('The result page must have archive products displaying (it will swap the existing products with the result products).', 'wpf') ?></label>
                </div>
            </div>
        </div>
        <div class="wpf_lightbox_row ">
            <div class="wpf_lightbox_label"><label for="wpf_no_found_message"><?php _e('No Products Found Message', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
				<?php WPF_Utils::module_language_tabs( 'no_found_message', $data, WPF_Utils::get_all_languages(), null, 'text', __('No products were found matching your selection.', 'wpf'), true ); ?>
            </div>
        </div>
        <div class="wpf_lightbox_row">
            <div class="wpf_lightbox_label"><label for="wpf_variations"><?php _e('Variations Display', 'wpf'); ?></label></div>
            <div class="wpf_lightbox_input">
                <input 
                    <?php if ( ! empty( $data['variations'] ) ) : ?>checked="checked"<?php endif; ?>
                    type="checkbox" name="variations" value="1" id="wpf_variations" />
                <label for="wpf_variations"><?php _e('Display product variations in the search result page.', 'wpf') ?></label>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    }

    public function form() {
		$sort_cmb = WPF_Utils::get_all_field_types();
        $languages = WPF_Utils::get_all_languages();
        natcasesort($sort_cmb);

        $layout = $data = array();
        if (!empty($this->themplate_id)) {
            $option = WPF_Options::get_option($this->plugin_name, $this->version);
            $forms = $option->get();
            if (!empty($forms[$this->themplate_id])) {
                $layout = $forms[$this->themplate_id];
                $data = $layout['data'];
                $layout = $layout['layout'];
            }
        }
        $this->add_fields($data);
        ?>  
        <input type="hidden" value="" name="layout" id="wpf_layout"/>
        <input type="hidden" value="<?php echo $this->themplate_id ?>" id="wpf_themplate_id" name="themplate_id"/>
        <div class="wpf_back_builder">
            <?php //Metabox Buttons    ?>
            <div class="wpf_back_module_panel">
                <?php foreach ($sort_cmb as $type => $name): ?>
                    <div <?php if (!empty($layout[$type])): ?>style="display:none;"<?php endif; ?> data-type="<?php echo $type ?>"
                                                              id="wpf_cmb_<?php echo $type ?>"
                                                              class="wpf_back_module">
                                                                  <?php $this->module($type, $name, array(), $languages); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="wpf_back_row_content" id="wpf_module_content">   
                <div class="wpf_module_holder">
                    <div class="wpf_empty_holder_text"><?php _e('Drop module here', 'wpf') ?></div>
                    <?php if (!empty($layout)): ?>
                        <?php foreach ($layout as $type => $module): ?>
                            <?php
                            if (empty($sort_cmb[$type])) {
                                continue;
                            }
                            $name = $sort_cmb[$type];
                            ?>
                            <div data-type="<?php echo $type ?>" class="wpf_back_module wpf_dragged">
                                <?php $this->module($type, $name, $module, $languages); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function module($type, $name, array $module = array(), array $languages = array()) {
        ?>
        <strong class="wpf_module_name"><?php echo $name ?></strong>
        <div class="wpf_back_module_top">
            <div class="wpf_left">
                <span class="wpf_back_active_module_title"><?php echo $name ?></span>
            </div>
            <div class="wpf_right">
                <a href="#" class="wpf_module_btn wpf_toggle_module"></a>
                <a href="#" class="wpf_module_btn wpf_delete_module"></a>
            </div>
        </div>
        <div class="wpf_active_module">
            <div data-type="<?php echo $type ?>" class="wpf_back_active_module_content">    
                <?php WPF_Utils::module_multi_text($type, $module, $languages, 'field_title', __('Field Title', 'wpf')); ?>
                <div class="wpf_back_active_module_row wpf_back_active_module_hide_field">
                    <div class="wpf_back_active_module_label">&nbsp;</div>
                    <div class="wpf_back_active_module_input">
                        <label>
                            <input id="wpf_<?php echo $type ?>[hide_field]" type="checkbox" name="[<?php echo $type ?>][hide_field]" value="1" <?php if (!empty($module['hide_field'])): ?>checked="checked"<?php endif; ?>  />
                            <?php _e('Hide field title', 'wpf') ?>
                        </label>
                    </div>
                </div>
                <?php if (has_action('wpf_template_' . $type)): ?>
                    <?php do_action('wpf_template_' . $type, $this->themplate_id, $module) ?>
                <?php else: ?>
                    <?php $this->get_main_fields($type, $name, $module) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save post themplate
     *
     * @since 1.0.0
     *
     * @param post array $data
     */
    public function save_themplate(array $post) {
        $result = false;
        if (!empty($post['layout'])) {
            $option = WPF_Options::get_option($this->plugin_name, $this->version);

            if (empty($post['name'])) {
                $post['name'] = uniqid($this->plugin_name . '_');
            }
            $themplate_id = !empty($post['themplate_id']) ? $post['themplate_id'] : $option->unique_name($post['name']);
            $data = $option->get();
            $layout = stripslashes_deep($post['layout']);
            if (empty($data[$themplate_id])) {
                $data[$themplate_id] = array();
            }
            $data[$themplate_id]['layout'] = json_decode($layout, true);
            $data[$themplate_id]['data'] = array();
            $_keys = array( 'name', 'empty', 'group','reset_button', 'type', 'page', 'sort', 'pagination', 'posts_per_page', 'result', 'out_of_stock', 'scroll', 'result_type', 'tax_relation', 'pagination_type', 'no_found_message', 'infinitybuffer', 'clear_label', 'variations' );
            foreach ($_keys as $k) {
                if (!empty($post[$k])) {
                    $data[$themplate_id]['data'][$k] = $post[$k];
                }
            }
            $data[$themplate_id]['data']['date'] = current_time('timestamp');
            $data = apply_filters('wpf_template_save', $data, $themplate_id);
            $option->set($data);
            $result = array(
                'id' => $themplate_id,
                'status' => '1',
                'text' => __('Template successfully updated', 'wpf')
            );
        }
        return $result;
    }

    /**
     * Render  fields
     *
     * @since 1.0.0
     * @param string $type
     * @param array $module
     */
    protected function get_main_fields($type, $name, $module = array()) {
        switch ($type):
            case 'price':
                $query_args = array(
                    'post_type' => array('product_variation', 'product'),
                    'post_status' => 'publish',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'meta_key' => '_price',
                    'posts_per_page' => 1,
					'no_found_rows' => true,
                );
                $max_query = new WP_Query($query_args);
                $max = $min = 0;
                if ($max_query->have_posts()) {

                    $max_query->the_post();
                    $max = get_post_meta(get_the_ID(), '_price', true);
                    $query_args['order'] = 'ASC';
                    $min_query = new WP_Query($query_args);
                    $min_query->the_post();
					$min = get_post_meta( get_the_ID(), '_price', true );
                    if ( empty( $min ) || ! is_numeric( $min ) ) {
                        $min = 0;
                    } else {
						$min = floor( floatval( $min ) );
					}
                    if ( ! empty( $max ) ) {
                        $max = ceil($max);
                    }
                }
                $from = 0;
                $to = '';
                if (!empty($module['from'])) {
                    if(is_array($module['from'])) {
                        $k=key($module['from']);
                        $from=floor(floatval($module['from'][$k]));
                        unset($module['from'][$k]);
                    } else {
                        $from=floor(floatval($module['from']));
                        unset($module['from']);
                    }
                }
                if (!empty($module['to'])) {
                    if(is_array($module['to'])) {
                        $k = key($module['to']);
                        $to = ceil($module['to'][$k]);
                        unset($module['to'][$k]);
                    }else{
                        $to = ceil($module['to']);
                        unset($module['to']);
                    }
                }
                $step = ( $min >= 0 && $min <= 1)? 0.1 : 1;
                if(!empty($module['step'])){
                    $step = $module['step'];
                }

	            wp_reset_postdata();
                ?>

                <div class="wpf_back_active_module_row wpf_show_range wpf_changed">
                    <div class="wpf_back_active_module_label">
                        <label><?php _e('Display as', 'wpf') ?></label>
                    </div>
                    <div class="wpf_back_active_module_input">
                        <label>
                            <input type="radio" name="[<?php echo $type ?>][price_type]" value="slider" <?php if ((!empty($module['price_type']) && $module['price_type'] === 'slider') || empty($module['price_type'])): ?>checked="checked"<?php endif; ?>  />
                            <?php _e('Slider', 'wpf') ?>
                        </label>
                        <label>
                            <input type="radio" name="[<?php echo $type ?>][price_type]" value="group" <?php if (!empty($module['price_type']) && $module['price_type'] === 'group'): ?>checked="checked"<?php endif; ?>  />
                            <?php _e('Range', 'wpf') ?>
                        </label>
                    </div>
                </div>
                <div class="wpf_back_active_module_row wpf_group">
                    <div class="wpf_back_active_module_label">&nbsp;</div>
                    <div class="wpf_back_active_module_input wpf_back_active_module_add">
                        <span><?php printf(__('Min price is %s. Max  price is %s', 'wpf'), $min, $max) ?></span>
                        <ul>
                            <li>
                                <span><?php _e('Price from', 'wpf') ?></span>
                                <input min="0" max="<?php echo $max ?>" value="<?php echo $from ?>" name="[<?php echo $type ?>][from][]" type="number" />
                                <span><?php _e('to', 'wpf') ?></span>
                                <input min="<?php echo $min ?>" max="<?php echo $max ?>" value="<?php echo $to ?>"name="[<?php echo $type ?>][to][]" type="number" />
                                <span class="wpf_module_btn wpf_remove_item"></span>
                            </li>
                            <?php if (!empty($module['from'])): ?>
                                <?php foreach ($module['from'] as $i => $v): ?>
                                    <li>
                                        <span><?php _e('Price from', 'wpf') ?></span>
                                        <input min="0" max="<?php echo $max ?>" value="<?php echo floor(floatval($v)) ?>" name="[<?php echo $type ?>][from][]" type="number" />
                                        <span><?php _e('to', 'wpf') ?></span>
                                        <input min="<?php echo $min ?>" max="<?php echo $max ?>" value="<?php echo!empty($module['to'][$i]) ? ceil($module['to'][$i]) : '' ?>" name="[<?php echo $type ?>][to][]" type="number" />
                                        <span class="wpf_module_btn wpf_remove_item"></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <a href="#" class="wpf_add_item"><?php _e('+ Add new', 'wpf') ?></a>
                    </div>
                </div>

                <div class="wpf_back_active_module_row wpf_slider">
                    <div class="wpf_back_active_module_label">&nbsp;</div>
                    <div class="wpf_back_active_module_input wpf_back_active_module_add">
                        <span><?php printf(__('You can set step like 0.001 or 0.01 or 1, Max step is %s', 'wpf'), $max) ?></span>
                        <ul>
                            <li>
                                <span><?php _e('Step', 'wpf') ?></span>
                                <input value="<?php echo $step ?>" name="[<?php echo $type ?>][step][]" type="text" />
                            </li>
                        </ul>
                    </div>
                </div>
                <?php
                break;
            case 'title':
				?>
				<div class="wpf_back_active_module_row">
					<div class="wpf_back_active_module_label">
						<label for="wpf_<?php echo $type ?>[variation]"><?php _e('Include Variations in Search Suggestions', 'wpf') ?></label>
					</div>
					<div class="wpf_back_active_module_input">
						<label>
							<input type="radio" name="[<?php echo $type ?>][variation]" value="yes" <?php if ( ! ( isset( $module['variation'] ) ) || $module['variation'] === 'yes' ) : ?>checked="checked"<?php endif; ?> />
							<?php _e( 'Yes', 'wpf' ); ?>
						</label>
						<label>
							<input type="radio" name="[<?php echo $type ?>][variation]" value="no" <?php if ( isset( $module['variation'] ) && $module['variation'] === 'no' ) : ?>checked="checked"<?php endif; ?> />
							<?php _e( 'No', 'wpf' ); ?>
						</label>
					</div>
				</div>
				<?php
				break;
            case 'instock':
            case 'onsale':
            case 'submit':
            case 'sku':
                break;
            default:
                $order = array(
                    'term_order' => __('Custom Ordering', 'wpf'),
                    'name' => __('Name', 'wpf'),
                    'count' => __('Count', 'wpf'),
                    'id' => __('ID', 'wpf'),
                );
                $orderby = array(
                    'asc' => __('Ascending', 'wpf'),
                    'desc' => __('Descending', 'wpf')
                );
                $display = array(
		    'checkbox' => __('Checkbox', 'wpf'),
                    'link' => __('Links', 'wpf'),
                    'radio' => __('Radio', 'wpf'),
                    'dropdown' => __('Dropdown', 'wpf'),
                    'multiselect' => __('Multi Select', 'wpf'),
                );
                $logic = array(
                    'or' => __('OR', 'wpf'),
                    'and' => __('AND', 'wpf')
                );
                $include_children = array(
                    'yes' => __('Yes', 'wpf'),
                    'no' => __('No', 'wpf')
                );
                ?>
                <div class="wpf_back_active_module_row">
                    <div class="wpf_back_active_module_label">
                        <label for="wpf_<?php echo $type ?>[count]"><?php _e('Product Count', 'wpf') ?></label>
                    </div>
                    <div class="wpf_back_active_module_input">
                        <label>
                            <input id="wpf_<?php echo $type ?>[count]" type="checkbox" name="[<?php echo $type ?>][count]" value="1" <?php if (!empty($module['count']) || !$module): ?>checked="checked"<?php endif; ?>  />
                            <?php _e('Show product counts', 'wpf') ?>
                        </label>
                    </div>
                </div>
				<?php if ( $type === 'wpf_cat' || is_taxonomy_hierarchical( $type ) ) : ?>
					<div class="wpf_back_active_module_row">
						<div class="wpf_back_active_module_label">
							<label for="wpf_<?php echo $type ?>[hierachy]"><?php _e('Category Hierarchy', 'wpf') ?></label>
						</div>
						<div class="wpf_back_active_module_input">
							<label>
								<input id="wpf_<?php echo $type ?>[hierachy]" type="checkbox" name="[<?php echo $type ?>][hierachy]" value="1" <?php if (!empty($module['hierachy'])): ?>checked="checked"<?php endif; ?>  />
								<?php _e('Show category hierarchy', 'wpf') ?>
							</label>
						</div>
					</div>
					<div class="wpf_back_active_module_row">
						<div class="wpf_back_active_module_label">
							<label for="wpf_<?php echo $type ?>[include]"><?php _e('Include Children ', 'wpf') ?></label>
						</div>
						<div class="wpf_back_active_module_input">
							<?php foreach ($include_children as $k => $v): ?>
								<label>
									<input type="radio" name="[<?php echo $type ?>][include]" value="<?php echo $k ?>" <?php if ((isset($module['include']) && $module['include'] === $k) || (!isset($module['include']) && $k === 'yes')): ?>checked="checked"<?php endif; ?>  />
									<?php echo $v ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
                <div class="wpf_back_active_module_row">
                    <div class="wpf_back_active_module_label">
                        <label><?php _e('Display as', 'wpf') ?></label>
                    </div>
                    <div class="wpf_back_active_module_input wpf_display_as wpf_changed">
                        <?php foreach ($display as $k => $v): ?>
                            <label>
                                <input type="radio" name="[<?php echo $type ?>][show_as]" value="<?php echo $k ?>" <?php if ((isset($module['show_as']) && $module['show_as'] === $k) || (!isset($module['show_as']) && $k === 'checkbox')): ?>checked="checked"<?php endif; ?>  />
                                <?php echo $v ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="wpf_back_active_module_row">
                    <div class="wpf_back_active_module_label">
                        <label><?php _e('Logic', 'wpf') ?></label>
                    </div>
                    <div class="wpf_back_active_module_input wpf_logic">
                        <?php foreach ($logic as $k => $v): ?>
                            <label>
                                <input type="radio" name="[<?php echo $type ?>][logic]" value="<?php echo $k ?>" <?php if ((isset($module['logic']) && $module['logic'] === $k) || (!isset($module['logic']) && $k === 'or')): ?>checked="checked"<?php endif; ?>  />
                                <?php echo $v ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="wpf_back_active_module_row">
                    <div class="wpf_back_active_module_label">
                        <label for="wpf_<?php echo $type ?>[order]"><?php _e('Order', 'wpf') ?></label>
                    </div>
                    <div class="wpf_back_active_module_input">
                        <div class="wpf_custom_select wpf_order wpf_changed">
                            <select name="[<?php echo $type ?>][order]">
                                <?php foreach ( $order as $k => $v ) : ?>
                                    <option <?php if (!empty($module['order']) && $module['order'] === $k): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="wpf_custom_select wpf_orderby">
                            <select name="[<?php echo $type ?>][orderby]">
                                <?php foreach ($orderby as $k => $v): ?>
                                    <option <?php if (!empty($module['orderby']) && $module['orderby'] === $k): ?>selected="selected"<?php endif; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php
                $display = array(
                    'horizontal' => __('Horizontal', 'wpf'),
                    'vertical' => __('Vertical', 'wpf'),
                    'columns' => __('Columns', 'wpf'),
                );
                ?>
                <div class="wpf_back_active_module_row">
                    <div class="wpf_back_active_module_label">
                        <label for="wpf_<?php echo $type ?>[display]"><?php _e('Layout', 'wpf') ?></label>
                    </div>
                    <div class="wpf_back_active_module_input">
                        <?php foreach ($display as $k => $v): ?>
                            <label>
                                <input id="wpf_<?php echo $type ?>[display_<?php echo $k ?>]" type="radio" value="<?php echo $k ?>" name="[<?php echo $type ?>][display]" <?php if ((!isset($module['display']) && $k === 'horizontal') || (isset($module['display']) && $module['display'] === $k)): ?>checked="checked"<?php endif; ?>  />
                                <?php echo $v ?>
                                <?php if ($k === 'columns'): ?>
                                    <div class="wpf_custom_select">
                                        <select name="[<?php echo $type ?>][column]">
                                            <?php for ($i = 2; $i < 4; ++$i): ?>
                                                <option value="<?php echo $i ?>" <?php if (isset($module['column']) && $module['column'] == $i): ?>selected="selected"<?php endif; ?>><?php echo $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php self::show_color_icons($type, $name, $module); ?>
                <?php 
				if ( in_array( $type, array( 'wpf_cat', 'wpf_tag' ) ) || taxonomy_exists( $type ) ) : ?>
                    <div class="wpf_back_active_module_row">
                        <div class="wpf_back_active_module_label">
                            <label for="wpf_<?php echo $type ?>[include_cat]"><?php _e('Include Terms', 'wpf'); ?></label>
                        </div>
                        <div class="wpf_back_active_module_input">
                            <label>
                                <input id="wpf_<?php echo $type ?>[include_cat]" type="text" name="[<?php echo $type ?>][include_cat]" value="<?php echo!empty($module['include_cat']) ? $module['include_cat'] : ''; ?>">
                                <br>
								<?php _e('Enter term IDs or slugs to include only (eg. 2, 4, 12)', 'wpf'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="wpf_back_active_module_row">
                        <div class="wpf_back_active_module_label">
                            <label for="wpf_<?php echo $type ?>[exclude_cat]"><?php _e('Exclude Terms', 'wpf'); ?></label>
                        </div>
                        <div class="wpf_back_active_module_input">
                            <label>
                                <input id="wpf_<?php echo $type ?>[exclude_cat]" type="text" name="[<?php echo $type ?>][exclude_cat]" value="<?php echo!empty($module['exclude_cat']) ? $module['exclude_cat'] : ''; ?>">
                                <br>
								<?php _e('Enter term IDs or slugs to exclude (eg. 2, 4, 12)', 'wpf'); ?>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
				<div class="wpf_back_active_module_row wpf_show_all_block">
					<div class="wpf_back_active_module_label">
						<label for="wpf_<?php echo $type ?>[show_all]"><?php _e( 'Show "All" option', 'wpf' ); ?></label>
					</div>
					<div class="wpf_back_active_module_input">
						<label>
							<input id="wpf_<?php echo $type ?>[show_all]" type="checkbox" name="[<?php echo $type ?>][show_all]" value="1" <?php if ( ! empty( $module['show_all'] ) ) : ?>checked="checked"<?php endif; ?> />
							<?php _e( 'Show option to list all terms', 'wpf' ) ?>
						</label>
					</div>
				</div>
                <?php break; ?>
        <?php endswitch; ?>

        <?php
    }

    /**
     * Frontend layout render
     *
     * @since 1.0.0
     * @param array   $template
     */
    public function public_themplate(array $template, $page_id, array $request = array()) {
		WPF_Public::get_instance()->enqueue_assets();

        if (empty($template['layout'])) {
            return '';
        }
        $lang = WPF_Utils::get_current_language_code();
        $layout = $template['layout'];
		$sort_cmb = WPF_Utils::get_all_field_types();
        $is_horizontal = $template['data']['type'] === 'horizontal';
        $is_group = !empty($template['data']['group']) || $is_horizontal;
        $page = get_permalink($template['data']['page']);
        $is_result_page = (!empty($template['data']['result_type']) && $template['data']['result_type'] === 'same_page') || get_the_ID() == $template['data']['page'];
        $scroll = !empty($template['data']['scroll']);
        $reset_btn = !empty($template['data']['reset_button']) ? $template['data']['reset_button'] : 'no';
		$no_found_message = ! empty( $template['data']['no_found_message'] ) ? WPF_Utils::get_label( $template['data']['no_found_message'] ) : '';
		if ( empty( $no_found_message ) ) {
			$no_found_message = __( 'No products were found matching your selection.', 'wpf' );
		}
		$reset = ! empty( $template['data']['clear_label'] ) ? WPF_Utils::get_label( $template['data']['clear_label'] ) : '';
		if ( empty( $reset ) ) {
			$reset = __( 'Clear', 'wpf' );
		}
		$infinitybuffer = ! empty( $template['data']['infinitybuffer'] ) ? $template['data']['infinitybuffer'] : 300;
        if('group' === $reset_btn){
            $non_groups = array('submit','instock','onsale');
        }
        $clasess = array('wpf_form', 'wpf_form_' . $this->themplate_id);
        if ($scroll) {
            $clasess[] = 'wpf_form_scroll';
        }
        if (!isset($layout['submit'])) {
            $clasess[] = 'wpf_submit_on_change';
        }
        ob_start();

	    if ( is_product_category() || is_product_tag() ) {
		    $cate=get_queried_object();
		    $post_id='c_'.$cate->term_id;
        }else{
		    $post_id=get_the_ID();
        }

		$shop_page = WPF_Utils::get_shop_page_url();

		if ( ! (
			is_post_type_archive( 'product' )
			|| is_page()
			|| is_tax( array( 'product_cat', 'product_tag' ) )
		) ) {
			/* assume there's no loop to display the results in, post the data to the Shop page instead */
			$action = $shop_page;
			$is_result_page = false;
		} else if ( ! $is_result_page ) {
			/* Result Page option is set to a different page */
			$action = $page;
		} else {
			/*
			 * show result on the same page
			 * @note: get_pagenum_link removes /page/N query variable from current URL,
			 * pagination is handled by ?wpf_page= parameter.
			 */
			$action = get_pagenum_link( 1, false );
		}

		if ( $is_result_page ) {
            $clasess[] = 'wpf_form_ajax';
        }

		// clear previous WPF parameters from current page URL
		$wpf_parameters = array();
		if ( ! empty( $_GET ) ) {
			foreach ( $_GET as $key => $value ) {
				if ( substr( $key, 0, 3 ) === 'wpf' ) {
					$wpf_parameters[] = $key;
				}
			}
		}
		$action = remove_query_arg( $wpf_parameters, $action );
        ?>
        <form
			data-post-id="<?php echo $post_id; ?>"
			data-slug="<?php echo $this->themplate_id ?>"
			action="<?php echo esc_attr( $action ); ?>"
			data-shop="<?php echo esc_attr( $shop_page ); ?>"
			method="get"
			class="<?php echo implode(' ', $clasess) ?>"
			style="visibility: hidden;"
			data-infinitybuffer="<?php echo esc_attr( $infinitybuffer ); ?>"
		>
            <input type="hidden" name="wpf" value="<?php echo $this->themplate_id ?>" />
			<input type="hidden" name="orderby" value="" />
			<input type="hidden" name="wpf_cols" value="" />
			<input type="hidden" name="wpf_page" value="1" />
			<?php if ( ! empty( $_GET['s'] ) ) : ?>
                <input type="hidden" value="<?php echo sanitize_text_field( $_GET['s'] ); ?>" name="s" />
			<?php endif; ?>
            <?php if ( empty( $layout['wpf_cat'] ) && is_product_category() ) : ?>
                <input type="hidden" value="<?php echo $cate->slug; ?>" name="wpf_cat" />
            <?php endif; ?>
            <div class="wpf_items_wrapper wpf_layout_<?php echo $template['data']['type'] ?><?php if ($is_group): ?> wpf_items_grouped<?php endif; ?>">
                <?php foreach ($layout as $type => $module): ?>
                    <?php if (!empty($sort_cmb[$type])): ?>
                        <?php ob_start(); ?>
                        <?php if (has_action('wpf_public_template_' . $type)): ?>
                            <?php do_action('wpf_public_template_' . $type, $module, $this->themplate_id, $template['data'], $sort_cmb[$type], $this->themplate_id, $request, $lang) ?>
                        <?php else: ?>
                            <?php $this->get_public_fields($type, $module, $template['data'], $request, $lang) ?>
                        <?php endif; ?>
                        <?php
                        $view = trim(ob_get_contents());
                        ob_end_clean();
                        ?>
                        <?php if ($view || empty($template['data']['empty'])): ?>
                            <div class="wpf_item wpf_item_<?php echo $type ?>">
                                <?php if ($type !== 'submit' && ($is_horizontal || empty($module['hide_field']))): ?>
                                    <label class="wpf_item_name" for="wpf_<?php echo $this->themplate_id ?>_item_<?php echo $type ?>"><?php echo WPF_Utils::get_field_name($module, $sort_cmb[$type]) ?></label>
                                <?php endif; ?>
                                <?php if ($is_group && $type !== 'submit'): ?><div class="wpf_items_group"><?php endif; ?>
                                <?php echo $view ?>
									<?php if('group' === $reset_btn && !in_array($type,$non_groups) ): ?>
                                        <div class="wpf_reset_btn"><input type="reset" value="<?php echo esc_attr( $reset ); ?>"/></div>
									<?php endif; ?>
                                <?php if ($is_group && $type !== 'submit'): ?></div><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if('bottom' === $reset_btn): ?>
                <div class="wpf_reset_btn"><input type="reset" value="<?php echo esc_attr( $reset ) ?>"/></div>
            <?php endif; ?>

			<div class="wpf-no-products-found" style="display: none;">
				<p class="woocommerce-info"><?php echo esc_html( $no_found_message ); ?></p>
			</div>
        </form>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

	public function get_min_max_price() {
		/**
		 * Note, the result is not filtered by language, this returns min & max prices from products in ALL languages
		 */
		$query_args = array(
			'post_type' => array( 'product_variation', 'product' ),
			'post_status' => 'publish',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'meta_key' => '_price',
			'posts_per_page' => 1,
			'no_found_rows' => true,
		);
		$max_query = get_posts( $query_args );
		if ( $max_query && isset( $max_query[0]->ID ) ) {
			$max = get_post_meta( $max_query[0]->ID, '_price', true );

			$query_args['order'] = 'ASC';
			$min_query = get_posts( $query_args );
			$min = get_post_meta( $min_query[0]->ID, '_price', true );

			return array( $min, $max );
		}

		return array( 0, 0 );
	}

    /**
     * Frontend post fields render
     *
     * @since 1.0.0
     * @param string $type
     * @param array $args
     * @param array $data
     * @param array $request
     * @param array $lang
     */
    protected function get_public_fields($type, array $args, array $data, array $request = array(), $lang = false) {
		$wpf = WPF_Public::get_instance();

		$value = array();
        if (!empty($request[$type])) {
            $value = $request[$type];
        }
        switch ($type):
            case 'title':
            case 'sku':
                if ( ! wp_script_is( 'jquery-ui-autocomplete' ) ) {
                    wp_enqueue_style($this->plugin_name . 'ui-css');
                }
				$search_variation = ( $type === 'title' && isset( $args['variation'] ) && $args['variation'] === 'no' ) ? 'data-variation="no"' : '';
                ?>
                <div class="wpf_autocomplete">
                    <input <?php echo $search_variation; ?> type="text" autocomplete="off" name="<?php echo WPF_Utils::strtolower(WPF_Utils::get_field_name($args, $type)); ?>" value="<?php echo $value ? esc_attr($value) : '' ?>" />
                    <span class="wpf-search-wait"></span>
                </div>
                <?php
                break;
            case 'instock':
            case 'onsale':
                $show = true;
                if (!empty($data['empty'])) {
                    $query_args = array(
                        'post_type' => array('product'),
                        'post_status' => 'publish',
                        'posts_per_page' => 1
                    );
                    if ($type === 'onsale') {
                        $query_args['meta_query'] = array(array(
                                'relation' => 'OR',
                                array(// Simple products type
                                    'key' => '_sale_price',
                                    'value' => 0,
                                    'compare' => '>',
                                    'type' => 'NUMERIC'
                                ),
                                array(// Variable products type
                                    'key' => '_min_variation_sale_price',
                                    'value' => 0,
                                    'compare' => '>',
                                    'type' => 'NUMERIC'
                                )
                        ));
                    } else {
                        $query_args['meta_query'] = array(array(
                                'key' => '_stock_status',
                                'value' => 'instock',
                                'compare' => '=',
                        ));
                    }
                    $meta_query = new WP_Query($query_args);
                    $show = $meta_query->have_posts();
                    wp_reset_postdata();

					if ( $type === 'instock' && ! empty( $data['out_of_stock'] ) ) {
						$value = 1;
					}
                }
                ?>  <?php if ( $show ) : ?>
                    <div class="wpf_<?php echo $type ?>_wrapp">
                        <input 
							type="checkbox"
							id="wpf_<?php echo $this->themplate_id ?>_item_<?php echo $type ?>"
							<?php if ( ! empty( $value ) ) : ?>checked="checked"<?php endif; ?>
							name="<?php echo WPF_Utils::strtolower(WPF_Utils::get_field_name($args, $type)); ?>"
							value="1"
						/>
                    </div>
                <?php endif; ?>
                <?php
                break;
            case 'submit':
                ?>
                <button type="submit" class="wpf_search_button"><?php echo WPF_Utils::get_field_name($args, __('Search', 'wpf')) ?></button>
                <?php
                break;
            case 'price':
                $price_type = !empty($args['price_type']) ? $args['price_type'] : 'slider';
                $name = WPF_Utils::strtolower(WPF_Utils::get_field_name($args, $type));

                if ($price_type === 'slider') {
					list( $min, $max ) = $this->get_min_max_price();

					if ( empty( $max ) ) {
						return;
					}

					if ( ! wp_script_is( 'jquery-ui-slider' ) ) {
						wp_enqueue_style( $this->plugin_name . 'ui-css' );
					}

					$step = ($min >= 0 && $min < 1 && ceil($max) <=1)? 0.1 : 1;
					if (isset($args['step'])){
						$step = $args['step'];
					}

					$from = isset($value['from']) && is_numeric($value['from']) ? floor(floatval($value['from'])) : '';
					$to = isset($value['to']) && is_numeric($value['to']) ? ceil($value['to']) : '';
					if (empty($min)) {
						$min = 0;
					}
					$min = floor(floatval($min));

					/* products are not filtered by price, set $from and $to to empty to prevent unnecessary meta query */
					if ( $from === $min && $to === $max ) {
						$from = '';
						$to = '';
					}
					?>
					<div data-max="<?php echo ceil($max) ?>" data-min="<?php echo $min ?>" data-step="<?php echo $step;?>" class="wpf_slider"></div>
					<div class="wpf-slider-label">
						<?php echo str_replace( '00000', '<span class="wpf-price-min">' . ( empty( $from ) ? $min : $from ) . '</span>', wc_price( 0, array( 'decimal_separator' => '', 'decimals' => 4 ) ) ); ?>
						-
						<?php echo str_replace( '00000', '<span class="wpf-price-max">' . ( empty( $to ) ? $max : $to ) . '</span>', wc_price( 0, array( 'decimal_separator' => '', 'decimals' => 4 ) ) ); ?>
					</div>
					<input type="hidden" name="<?php echo $name ?>-from" value="<?php echo $from ?>" class="wpf_price_from" />
					<input type="hidden" name="<?php echo $name ?>-to" value="<?php echo $to ?>" class="wpf_price_to" />
					<?php
                } elseif (!empty($args['from'])) {
                    $selected = isset($value['from']) && is_numeric($value['from']) ? floor(floatval($value['from'])) : '';
                    $selected.='-';
                    $selected.= isset($value['to']) && is_numeric($value['to']) ? ceil($value['to']) : '';
                    if ($selected === '-') {
                        $selected = false;
                    }
                    ?>
                    <ul class="wpf_price_range">
                        <li>
                            <input <?php if (!$selected): ?> checked="checked"<?php endif; ?> id="wpf_<?php echo $this->themplate_id ?>_item_<?php echo $type ?>_all" type="radio" name="<?php echo $name ?>" value=""/>
                            <label for="wpf_<?php echo $this->themplate_id ?>_item_<?php echo $type ?>_all"><?php _e('All', 'wpf') ?></label>
                        </li>
                        <?php 
						foreach ( (array) $args['from'] as $i => $v): ?>
                            <?php
                            $from = floor(floatval($v));
							$to = (array) $args['to'];
                            $to = ! empty( $to[ $i ] ) ? ceil( $to[ $i ] ) : '';
                            $orig_v = $from . '-' . $to;
                            ?>
                            <li>
                                <input <?php if ($selected === $orig_v): ?> checked="checked"<?php endif; ?> id="wpf_<?php echo $this->themplate_id ?>_item_<?php echo $type ?>_<?php echo $i ?>" type="radio" name="<?php echo $name ?>" value="<?php echo $orig_v ?>"/>
                                <label for="wpf_<?php echo $this->themplate_id ?>_item_<?php echo $type ?>_<?php echo $i ?>"><?php echo WPF_Utils::format_price($from) ?> - <?php echo WPF_Utils::format_price($to) ?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php
                }
                ?>

                <?php break; ?>
            <?php
            default:
                $args['show_as'] = !empty($args['show_as']) ? $args['show_as'] : 'link';
                $link = $args['show_as'] === 'link';
                $hierarchy = !empty($args['hierachy']);
                $color = !$link && !empty($args['color']);
                $hide_text = !$link && !$hierarchy && $color && !empty($args['hide']);
                $column = $args['display'] === 'columns' ? (!empty($args['column']) ? $args['column'] : 1) : false;
				$taxonomy = str_replace( 'wpf_', 'product_', $type );
                $q = array(
                    'hide_empty' => !$hierarchy && !empty($data['empty']),
                    'hierarchical' => $hierarchy,
                    'pad_counts' => $hierarchy
                );
                if ($args['order'] !== 'term_order') {
                    $q['orderby'] = $args['order'];
                    $q['order'] = $args['orderby'];
                }

                if (!empty($args['include_cat'])) {
                    $q['include'] = self::get_terms( $args['include_cat'], $taxonomy );
                }

                if (!empty($args['exclude_cat'])) {
                    $q['exclude'] = self::get_terms( $args['exclude_cat'], $taxonomy );
                }

                $categories = get_terms( $taxonomy, $q);

                if ($hierarchy) {
                    $cats = array();
                    foreach ($categories as $c) {
                        if (!isset($cats[$c->parent])) {
                            $cats[$c->parent] = array();
                        }
                        $cats[$c->parent][] = $c;
                    }
                } else {
                    $cats = $categories;
                }
                unset($categories);
				$selected = null;
                if (($type === 'wpf_cat' && is_product_category()) || ($type === 'wpf_tag' && is_product_tag())) {
                    global $wp_query;
                    $cat = $wp_query->get_queried_object();
                    if (is_object($cat) && !empty($cat->slug)) {

                        if (empty($value)) {
                            $value = array();
							$value[] = $cat->slug;
                        } elseif ( $type === 'wpf_tag' ) {
                            $value = explode(',', $value);
							$value[] = $cat->slug;
							$selected = $cat->slug;
                        }
                    }
                }
                $is_dropdown = 'dropdown' === $args['show_as'] || 'multiselect' === $args['show_as'];
                if ($is_dropdown) {
                    $args['color'] = false;
                    wp_enqueue_style( $this->plugin_name . '-select' );
                }
                ?>
                <?php if (!empty($cats)): ?>
                    <?php if ($is_dropdown): ?>
                        <select  name="<?php echo WPF_Utils::strtolower(WPF_Utils::get_field_name($args, $type)); ?><?php if ('multiselect' === $args['show_as']): ?>[]<?php endif; ?>" <?php if ('multiselect' === $args['show_as']): ?>multiple="multiple" <?php if ($selected && ($type === 'wpf_cat' || $type === 'wpf_tag')): ?>data-selected="<?php echo $selected ?>" <?php endif; ?><?php endif; ?>class="wpf_dropdown">
                        <?php else: ?>
                            <ul class="<?php if ($link): ?>wpf_links <?php endif; ?><?php if (!$hierarchy): ?>wpf_column_<?php echo $args['display'] ?><?php if ($column): ?> wpf_column_<?php echo $column ?><?php endif; ?><?php else: ?>wpf_hierachy<?php endif; ?><?php if ($color): ?> wpf_color_icons<?php endif; ?><?php if ($hide_text): ?> wpf_hide_text<?php endif; ?>">
                            <?php endif; ?>
                            <?php if (!empty($args['hierachy'])): ?>
                                <?php $this->category_walker($cats[0], $cats, $type, $args, $value, !empty($data['empty']), $lang); ?>
                            <?php else: ?>
                                <?php $this->category_walker($cats, array(), $type, $args, $value, !empty($data['empty']), $lang); ?>
                            <?php endif; ?>
                            <?php if ($is_dropdown): ?>
                        </select>
                    <?php else: ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
                <?php break; ?>
        <?php endswitch; ?>
        <?php
    }

	/**
	 * Get term IDs, if a term slug is given it's converted to ID
	 *
	 * @return array
	 */
	private static function get_terms( $terms, $taxonomy ) {
		$slugs = $ids = [];
		/* seperate slugs and IDs */
		$terms = array_map( function( $item ) use ( &$slugs, &$ids ) {
			$item = trim( $item );
			if ( is_numeric( $item ) ) {
				$ids[] = $item;
			} else {
				$slugs[] = $item;
			}
		}, explode( ',', $terms ) );

		/* convert slugs to IDs */
		if ( ! empty( $slugs ) ) {
			$slugs = get_terms( array(
				'fields'  => 'ids',
				'slug'    => $slugs,
				'taxonomy' => $taxonomy,
			) );
			if ( ! is_wp_error( $slugs ) && count( $slugs ) > 0 ) {
				$ids = array_merge( $ids, $slugs );
			}
		}

		return $ids;
	}

    private function category_walker($items, $cats, $type, array $args, $value, $hide_empty, $lang, $i = 1) {
        $hierarchy = !empty($args['hierachy']);
        $color = !empty($args['color']);
        $hide_text = !$hierarchy && $color && !empty($args['hide']);
        $show_count = !empty($args['count']);
        $value = empty($value) ? array() : (!is_array($value) ? explode(',', $value) : $value);
        static $product_count = false;
        $name = WPF_Utils::strtolower( WPF_Utils::get_field_name( $args, $type ) );
		$name = urldecode( $name );
        if ($product_count === false && $show_count) {
            $product_count = WPF_Utils::count_posts( 'product' );
        }
        ++$i;
        if ( 2 === $i && ! empty( $args['show_all'] ) ) :
            ?>
            <?php if ('radio' === $args['show_as']):
            $term_id = isset($cats->term_id)? $cats->term_id : '';

            ?>
                <li class="<?php echo $name, '_option_all'; ?>">
                    <input <?php if (empty($value)): ?>checked="checked"<?php endif; ?> id="<?php echo $name, '_option_all'; ?>" type="radio" name="<?php echo $name; ?>[]" value="" />
                    <label <?php if (($color && !empty($args['color_bg_' . $term_id])) || !empty($args['image_bg_' . $term_id])): ?>
                            style="
                            <?php if (!empty($args['image_bg_' . $term_id])):?>background-image: url(<?php echo $args['image_bg_' . $term_id] ?>);background-size: cover;<?php endif;?>
                            <?php if (!empty($args['color_bg_' . $term_id])):?>background-color:<?php echo $args['color_bg_' . $term_id]?>; <?php endif; ?>
                            <?php if (!empty($args['color_text_' . $term_id])): ?> color:<?php echo $args['color_text_' . $term_id] ?>;<?php endif; ?>"
                          <?php endif; ?>
                            for="wpf_<?php echo $this->themplate_id ?>_<?php echo $term_id ?>">
                    <label <?php if ($color): ?> class="wpf-label-option-all"<?php endif; ?> for="<?php echo $name, '_option_all'; ?>">
                        <?php _e('All', 'wpf'); ?>
                    </label>
                    <?php if ($show_count): ?>
                        <span class="wpf_item_count"><?php echo $product_count; ?></span>
                    <?php endif; ?>
                </li>
            <?php elseif ('dropdown' === $args['show_as']): ?>
                <option value="">
                    <?php _e('All', 'wpf'); ?>
                    <?php if ($show_count): ?>
                        &nbsp;(<?php echo $product_count; ?>)
                    <?php endif; ?>
                </option>
            <?php elseif (strpos($type, 'pa_') === 0 && 'link' === $args['show_as']): ?>
                <li class="<?php echo $name; ?>">
                    <a class="wpf_pa_link" href="javascript:void(0)">
                        <input <?php if (!$value): ?>checked="checked"<?php endif; ?> type="radio" value="" name="<?php echo $name ?>" />
                        <span><?php _e('All', 'wpf'); ?></span>
                    </a>
                    <?php if ($show_count): ?>
                        <span class="wpf_item_count"><?php echo $product_count; ?></span>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php foreach ( $items as $cat ) :

			$cat->slug = urldecode( $cat->slug ); // make slug readable, required for multilingual websites

			$read_only = '';
			if ( is_product_category() || is_product_tag() ) {
				$query_object = get_queried_object();
				if ( $cat->taxonomy === $query_object->taxonomy && $query_object->slug === $cat->slug ) {
					$read_only = 'readonly="readonly"';
				}
			}
			?>
            <?php if ($hide_empty && $cat->count === 0): ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if ('dropdown' === $args['show_as'] || 'multiselect' === $args['show_as']): ?>
                <option<?php if ( in_array( $cat->slug, $value, true ) ) : ?> selected="selected"<?php endif; ?> value="<?php echo $cat->slug ?>">
                    <?php
                    if ($hierarchy && $i > 2) {
                        echo str_repeat('&nbsp;', ($i - 2) * 3);
                    }
                    ?>
                    <?php echo $cat->name; ?>
                    <?php if ($show_count): ?> &nbsp;(<?php echo $cat->count ?>)<?php endif; ?>
                </option>
                <?php if ($hierarchy && !empty($cats[$cat->term_id])): ?>
                    <?php $this->category_walker($cats[$cat->term_id], $cats, $type, $args, $value, $hide_empty, $lang, $i); ?>
                <?php endif; ?>
            <?php else: ?>
                <li class="<?php echo "wpf_{$cat->taxonomy}_{$cat->term_id}"; ?>">
                    <?php if ('link' === $args['show_as']): ?>
                        <?php if (strpos($type, 'pa_') !== 0 && in_array( $cat->slug, $value, true ) ): ?>
                            <span class="wpf_selected"><?php echo $cat->name ?></span>
                        <?php else: ?>
                            <?php if (strpos($type, 'pa_') === 0): ?>
                                <a class="wpf_pa_link" href="<?php echo get_term_link($cat->term_id, $cat->taxonomy); ?>">
                                    <input <?php echo $read_only; ?> <?php if (in_array($cat->slug, $value,true)): ?>checked="checked"<?php endif; ?> type="radio" value="<?php echo $cat->slug ?>" name="<?php echo $name ?>" />
                                    <span><?php echo $cat->name ?></span>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo get_term_link($cat->term_id, $cat->taxonomy); ?>">
                                    <?php echo $cat->name ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php
                        $show_label = $color && (!$hide_text || !empty($args['text_' . $cat->term_id][$lang]));
                        $label = $show_label && !empty($args['text_' . $cat->term_id][$lang]) ? WPF_Utils::get_label($args['text_' . $cat->term_id]) : $cat->name;

						$color_bg = !empty($args['color_bg_' . $cat->term_id])?$args['color_bg_' . $cat->term_id]:'';
						$color_text=!empty($args['color_text_' . $cat->term_id])?$args['color_text_' . $cat->term_id]:'';
						$image_bg=!empty($args['image_bg_' . $cat->term_id])?$args['image_bg_' . $cat->term_id]:'';
						/*
						 * "color icons" is saved for only one language, force the values
						 * stored for one language to be used on all.
						 */
						$is_multilingual = count( WPF_Utils::get_all_languages() ) > 1;
						if ( $is_multilingual && $color ) {
							$term_ids = WPF_Utils::get_object_id_in_all_languages( $cat->term_id, $type );
							if ( ! empty( $term_ids ) ) {
								foreach ( $term_ids as $object_id ) {
									foreach ( array( 'color_text', 'color_bg', 'image_bg' ) as $v ) {
										if ( ! empty( $args[ "{$v}_{$object_id}" ] ) ) {
											$$v = $args[ "{$v}_{$object_id}" ];
										}
									}
								}
							}
						}
                        ?>
                        <input <?php echo $read_only; ?> <?php if ( in_array( $cat->slug, $value, true ) ): ?>checked="checked"<?php endif; ?> id="wpf_<?php echo $this->themplate_id ?>_<?php echo $cat->term_id ?>" type="<?php echo $args['show_as'] ?>" name="<?php echo $name; ?>[]" value="<?php echo $cat->slug ?>" />
                        <label <?php if (($color && ! empty( $color_bg ) ) || ! empty( $image_bg ) ) : ?>
                                style="
                                    <?php if ( ! empty( $image_bg ) ) : ?>background-image: url(<?php echo $image_bg ?>);background-size: cover;<?php endif;?>
                                    <?php if ( ! empty( $color_bg ) ) : ?>background-color:<?php echo $color_bg; ?>; <?php endif;?>
                                    <?php if ( ! empty( $color_text ) ) : ?> color:<?php echo $color_text ?>;<?php endif; ?>"
                               <?php endif; ?>
                                for="wpf_<?php echo $this->themplate_id ?>_<?php echo $cat->term_id ?>">
                            <?php if ($show_label || !$color): ?><?php echo $label ?><?php else: ?><i></i><span class="screen-reader-text"><?php echo $cat->slug ?></span><?php endif; ?>
                            <?php if(!empty($args['tooltip'])): ?>
                            <span class="wpf_tooltip"><?php echo $label ?></span>
                            <?php endif; ?>
                        </label>
                    <?php endif; ?>

                    <?php if ($show_count): ?><span class="wpf_item_count"><?php echo $cat->count ?></span><?php endif; ?>
                    <?php if ($hierarchy && !empty($cats[$cat->term_id])): ?>
                        <ul class="wpf_submenu wpf_level_<?php echo $i ?>">
                            <?php $this->category_walker($cats[$cat->term_id], $cats, $type, $args, $value, $hide_empty, $lang, $i); ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php
    }

    private static function show_color_icons($type, $name, $module = array()) {
        $tax = $type === 'wpf_cat' || $type === 'wpf_tag' ? str_replace('wpf', 'product', $type) : $type;
        $is_tax=false;
        if($type === 'wpf_cat' || $type === 'wpf_tag'){
            if ($type === 'wpf_cat') {
                $name = __('categories', 'wpf');
            }
            $url = add_query_arg(array('action'=>'wpf_get_tax','nonce'=>wp_create_nonce('wpf_get_tax'),'tax'=>$tax,'slug'=>$_REQUEST['slug']),  admin_url('admin-ajax.php'));
            $is_tax=true;
        }
        ?>
        <?php $categories = get_terms($tax, array('hide_empty' => false)); ?>
        <?php if (!empty($categories)): ?>
            <div class="wpf_back_active_module_row wpf_icons_block">
                <div class="wpf_back_active_module_label">
                    <label for="wpf_<?php echo $type ?>[color]"><?php _e('Color Icons', 'wpf') ?></label>
                </div>
                <div class="wpf_back_active_module_input wpf_show_icons wpf_changed">
                    <label>
                        <input id="wpf_<?php echo $type ?>[color]" type="checkbox" name="[<?php echo $type ?>][color]" value="1" <?php if (!empty($module['color'])): ?>checked="checked"<?php endif; ?>  />
                        <?php printf(__('Display %s as color icons', 'wpf'), $name) ?>
                    </label>
                    <div class="wpf_items_container">
                        <label>
                            <input id="wpf_<?php echo $type ?>[tooltip]" type="checkbox" name="[<?php echo $type ?>][tooltip]" value="1" <?php if (!empty($module['tooltip'])): ?>checked="checked"<?php endif; ?>  />
							<?php _e('Display tooltip on icon hover', 'wpf'); ?>
                        </label>
                        <ul class="wpf_tax_items"<?php echo $is_tax?' data-url="'.$url.'"':''; ?>>
                            <?php if($is_tax): ?>
                            <li><?php _e('Loading...','wpf'); ?></li>
                            <?php else: ?>
                                <?php $languages = WPF_Utils::get_all_languages(); ?>
                                <?php foreach ($categories as $cat): ?>
                                    <li class="tf_clearfix">
                                        <div class="wpf_color_wrapper">
                                            <label class="wpf_color_name" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color"><?php echo $cat->name ?></label>
                                        </div>
                                        <div class="wpf_color_options_wrap">
                                            <label class="wpf_color_wrap" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_bg">
                                                <span><?php _e('Background', 'wpf') ?></span>
                                                <input class="wpf_color_picker" type="text" id="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_bg" name="[<?php echo $type ?>][color_bg_<?php echo $cat->term_id ?>]" <?php if (!empty($module['color_bg_' . $cat->term_id])): ?>data-value="<?php echo $module['color_bg_' . $cat->term_id] ?>"<?php endif; ?> />
                                            </label>
                                            <label class="wpf_color_wrap" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_text">
                                                <span><?php _e('Text Color', 'wpf') ?></span>
                                                <input class="wpf_color_picker" type="text" id="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_text" name="[<?php echo $type ?>][color_text_<?php echo $cat->term_id ?>]" <?php if (!empty($module['color_text_' . $cat->term_id])): ?>data-value="<?php echo $module['color_text_' . $cat->term_id] ?>"<?php endif; ?> />
                                            </label>
                                            <label class="wpf_color_wrap wpf_color_text" for="wpf_<?php echo $type ?>_text_<?php echo $cat->term_id ?>">
                                                <span><?php _e('Icon Text', 'wpf') ?></span>
                                                <?php WPF_Utils::module_language_tabs($type, $module, $languages, 'text_' . $cat->term_id); ?>
                                            </label>
                                            <label class="wpf_color_wrap wpf_background_image <?php if (!empty($module['image_bg_' . $cat->term_id])): ?> has-image <?php endif;?>" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_image_bg">
                                                <span><?php _e('Background Image', 'wpf') ?></span>
                                                <input class="" type="text" id="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_image_bg"
                                                       name="[<?php echo $type ?>][image_bg_<?php echo $cat->term_id ?>]"
                                                       name="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_image_bg"
                                                       <?php if (!empty($module['image_bg_' . $cat->term_id])): ?>value="<?php echo $module['image_bg_' . $cat->term_id] ?>"<?php endif; ?> />
                                                <button class="open_media_uploader_image button-link "><?php esc_attr_e( 'Select Image', 'wpf' ); ?></button>
                                                <div class="image-area">
                                                    <img class="preview-image-wraper" src="<?php echo $module['image_bg_' . $cat->term_id] ?>" alt="">
                                                    <i class="remove-background ti-close"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                <?php endif; ?>
                        </ul>
                        <input type="checkbox" id="wpf_<?php echo $type ?>_hide" name="[<?php echo $type ?>][hide]" value="1" <?php if (!empty($module['hide'])): ?>checked="checked"<?php endif; ?>/>
                        <label for="wpf_<?php echo $type ?>_hide">
                            <?php printf(__('Hide original %s labels', 'wpf'), $name) ?>
                        </label>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php
    }

}
