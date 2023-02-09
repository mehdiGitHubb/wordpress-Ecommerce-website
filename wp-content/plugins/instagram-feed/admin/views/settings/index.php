<?php
    /**
     * CFF Header Notices
     *
     * @since 4.0
     */
    do_action('sbi_header_notices');
?>
<div id="sbi-settings" class="sbi-settings" :data-app-loaded="appLoaded ? 'true' : 'false'">
    <?php
        InstagramFeed\SBI_View::render( 'sections.header' );
        InstagramFeed\SBI_View::render( 'settings.content' );
        InstagramFeed\SBI_View::render( 'sections.sticky_widget' );
    ?>
    <div class="sb-control-elem-tltp-content" v-show="tooltip.hover" @mouseover.prevent.default="hoverTooltip(true)" @mouseleave.prevent.default="hoverTooltip(false)">
		<div class="sb-control-elem-tltp-txt" v-html="tooltip.text"></div>
	</div>
</div>