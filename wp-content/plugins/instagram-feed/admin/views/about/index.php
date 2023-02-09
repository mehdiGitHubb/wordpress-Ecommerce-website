<?php
    /**
     * CFF Header Notices
     *
     * @since 4.0
     */
    do_action('sbi_header_notices');
?>
<div id="sbi-about" class="sbi-about">
    <?php
        InstagramFeed\SBI_View::render( 'sections.header' );
        InstagramFeed\SBI_View::render( 'about.content' );
        InstagramFeed\SBI_View::render( 'sections.sticky_widget' );
    ?>
</div>