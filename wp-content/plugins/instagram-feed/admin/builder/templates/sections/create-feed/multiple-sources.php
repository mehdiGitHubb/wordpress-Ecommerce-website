<div id="sbi-multiple-sources-ctn" class="sbi-fb-fs" v-if="checkMultipleFeedType()">

	<div class="sbi-fb-slctsrc-content sbi-fb-fs">
		<div class="sbi-fb-sec-heading sbi-fb-fs">
			<h4>{{selectSourceScreen.mainHeading}}</h4>
			<span class="sb-caption sb-lighter">{{selectSourceScreen.description}}</span>
		</div>
	</div>

    <?php
        include_once SBI_BUILDER_DIR . 'templates/sections/create-feed/multiple-sources-list.php';
    ?>

	<div v-if="! maxTypesAdded()" class="sbi-addsource-type-btn sbi-fb-fs" @click.prevent.default="activateView('feedtypesPopup')">
		<svg width="14" height="14" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M9.66634 5.66634H5.66634V9.66634H4.33301V5.66634H0.333008V4.33301H4.33301V0.333008H5.66634V4.33301H9.66634V5.66634Z"/>
		</svg>
		<span>{{genericText.addSourceType}}</span>
	</div>

</div>