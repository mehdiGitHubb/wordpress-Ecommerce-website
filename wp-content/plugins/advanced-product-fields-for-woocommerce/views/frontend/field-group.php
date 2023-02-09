<?php
    /** @var \SW_WAPF\Includes\Models\FieldGroup $field_group */
    /** @var array $data */
    /** @var WC_Product $product */

use \SW_WAPF\Includes\Classes\Html;
    use \SW_WAPF\Includes\Classes\Helper;
    $label_position = isset($field_group->layout['labels_position']) ? $field_group->layout['labels_position'] : 'above';
    $instructions_position = isset($field_group->layout['instructions_position']) ? $field_group->layout['instructions_position'] : 'field';
    $mark_required = isset($field_group->layout['mark_required']) && $field_group->layout['mark_required'];

    if(isset($data['has_variation_logic']) && $data['has_variation_logic'] === true )
        $variation_rules_attr = Helper::thing_to_html_attribute_string($data['variation_rules']);

    ?>

<div
    class="wapf-field-group"
    data-group="<?php echo $field_group->id; ?>"
    <?php if(!empty($variation_rules_attr)) { ?>
        data-wapf-variation-rules="<?php echo $variation_rules_attr; ?>"
    <?php } ?>
>

    <?php
        // Prep
        $field_info = [];
        $prev_width = 0;
        for($i = 0; $i < count($field_group->fields);$i++) {
            $info = [
                'width'     => empty($field_group->fields[$i]->width) ? 100 : intval($field_group->fields[$i]->width),
                'end_row'   => false
            ];

            $prev_width += $info['width'];

            if($prev_width > 100) {

                $prev_width = $info['width']; // 0;//100 - $info['width'];
                $info['end_row'] = true;

            }

            $field_info[] = $info;

        }
    ?>
    <div class="wapf-field-row">
    <?php
        for($i = 0;$i < count($field_group->fields); $i++) {
            $field = $field_group->fields[$i];
            $info = $field_info[$i];
    ?>
        <?php if($info['end_row']) { ?>
            </div><div class="wapf-field-row">
        <?php } ?>
        <div class="<?php echo Html::field_container_classes($field); ?>" style="width:<?php echo $info['width'] ;?>%;" <?php echo Html::field_container_attributes($field);?> >

            <?php
            if($label_position === 'above' || $label_position === 'left') {
                echo sprintf(
                    '<div class="wapf-field-label wapf--%s"><label>%s</label></div>%s',
                    $label_position,
                    Html::field_label($field,$product,$mark_required),
                    $instructions_position === 'label' ? Html::field_description($field) : ''
                );
            }
            ?>

            <div class="wapf-field-input">
                <?php echo Html::field($product,$field,$field_group->id); ?>
            </div>

            <?php
            if($instructions_position === 'field')
                echo Html::field_description($field);
            ?>

            <?php
                if($label_position === 'below' || $label_position === 'right') {
                    echo sprintf(
                        '<div class="wapf-field-label wapf--%s"><label>%s</label></div>%s',
                        $label_position,
                        Html::field_label($field,$product,$mark_required),
                        $instructions_position === 'label' ? Html::field_description($field) : ''
                    );
                }
            ?>

        </div>
    <?php } ?>
    </div>
</div>