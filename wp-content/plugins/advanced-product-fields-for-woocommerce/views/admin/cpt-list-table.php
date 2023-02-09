<?php
    /* @var $model array */
    /** @var $list \SW_WAPF\Includes\Classes\wapf_List_Table */
?>
<script>
    var m = 'defga';
</script>
<div class="wrap">
    <h2 style="display: block;">
        <?php echo $model['title']; ?>

        <?php if($model['can_create']){ ?>
            <a href="#" onclick="javascript:anpf('<?php echo admin_url('post-new.php?post_type=wapf_product'); ?>')" class="page-title-action">
                <?php _e('Add New', 'advanced-product-fields-for-woocommerce'); ?>
            </a>
        <?php } ?>
    </h2>

    <p style="padding-bottom:22px;margin:0 !important;" class="wapf-description">A field group is a collection of fields that belong together.</p>
    <div id="nds-wp-list-table-demo">
        <div id="nds-post-body">
            <?php $list->views(); ?>
            <form method="post">
                <?php $list->display(); ?>
            </form>
        </div>
    </div>
</div>
<script>
    function anpf(link) {
        if(<?php echo $model['count']; ?> >= m.length)
            alert("<?php _e("Sorry :( We can't add a new field group. You've reached the amount of field groups allowed in the free version. Consider removing an old field group or upgrading to premium for unlimited field groups.",'advanced-product-fields-for-woocommerce'); ?>");
        else window.location.href = link;
    }
</script>