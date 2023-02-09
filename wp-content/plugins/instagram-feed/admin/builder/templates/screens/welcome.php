<div class="sbi-fb-full-wrapper sbi-fb-fs" v-if="viewsActive.pageScreen == 'welcome' && !iscustomizerScreen">
    <?php
        /**
         * CFF Admin Notices
         *
         * @since 4.0
         */
        do_action('sbi_admin_notices');
    ?>

	<div class="sbi-fb-wlcm-header sbi-fb-fs">
		<h2>{{welcomeScreen.mainHeading}}</h2>
        <div class="sb-positioning-wrap" v-bind:class="{ 'sb-onboarding-highlight' : viewsActive.onboardingStep === 1 }">
            <div class="sbi-fb-btn sbi-fb-btn-new sbi-btn-orange" @click.prevent.default="! viewsActive.onboardingPopup ? switchScreen('pageScreen', 'selectFeed') : switchScreen('welcome')">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.66537 5.66659H5.66536V9.66659H4.33203V5.66659H0.332031V4.33325H4.33203V0.333252H5.66536V4.33325H9.66537V5.66659Z" fill="white"/>
                </svg>
                <span>{{genericText.addNew}}</span>
            </div>
        </div>
	</div>
	<?php
		include_once SBI_BUILDER_DIR . 'templates/sections/empty-state.php';
		include_once SBI_BUILDER_DIR . 'templates/sections/feeds-list.php';
	?>
    <div v-if="licenseType == 'free'" class="sbi-fb-fs">
        <?php
            InstagramFeed\SBI_View::render( 'sections.builder_footer_cta' );
        ?>
    </div>
</div>