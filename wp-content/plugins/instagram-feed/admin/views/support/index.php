<?php
    /**
     * CFF Header Notices
     *
     * @since 4.0
     */
    do_action('sbi_header_notices');
?>
<div id="sbi-support" class="sbi-support">
    <?php
        InstagramFeed\SBI_View::render( 'sections.header' );
        InstagramFeed\SBI_View::render( 'support.content' );
        InstagramFeed\SBI_View::render( 'sections.sticky_widget' );
    ?>
</div>