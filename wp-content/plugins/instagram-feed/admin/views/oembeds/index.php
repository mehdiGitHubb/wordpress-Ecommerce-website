<?php
    /**
     * CFF Header Notices
     *
     * @since 4.0
     */
    do_action('sbi_header_notices');
?>
<div id="sbi-oembeds" class="sbi-oembeds">
    <?php
        InstagramFeed\SBI_View::render( 'sections.header' );
        InstagramFeed\SBI_View::render( 'oembeds.content' );
        InstagramFeed\SBI_View::render( 'sections.sticky_widget' );
    ?>
</div>