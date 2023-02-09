<?php
/** @var array $model */
$unique = mt_rand(10000,99999);
?>
<div class="wapf-checkable">
    <input type="hidden" value="0" name="wapf[field_<?php echo $model['field']->id;?>]" />
    <label class="wapf-checkbox-label" for="<?php echo $unique;?>">
        <input id="<?php echo $unique;?>" type="checkbox" value="1" <?php echo $model['field_attributes']; ?> />
        <?php if(!empty($model['field']->options['message']) || $model['field']->pricing_enabled()){ ?>
            <span class="wapf-label-text">
                <?php
                    if(!empty($model['field']->options['message']))
                        echo esc_html($model['field']->options['message']);
                    if($model['field']->pricing_enabled())
                        echo ' <span class="wapf-pricing-hint">('. \SW_WAPF\Includes\Classes\Helper::format_pricing_hint($model['field']->pricing->type, $model['field']->pricing->amount,$model['product'],'shop') .')</span>';
                    ?>
            </span>
        <?php } ?>
    </label>
</div>