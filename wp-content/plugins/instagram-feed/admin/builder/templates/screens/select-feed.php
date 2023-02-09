<div v-if="viewsActive.pageScreen == 'selectFeed'" class="sbi-fb-fs">
	<span></span>
	<div class="sbi-fb-create-ctn sbi-fb-wrapper">
		<div class="sbi-fb-heading">
			<h1>{{selectFeedTypeScreen.mainHeading}}</h1>
			<div class="sbi-fb-btn sbi-fb-slctf-nxt sbi-fb-btn-ac sbi-btn-orange" :data-active="creationProcessCheckAction()" @click.prevent.default="creationProcessNext()">
				<span>{{genericText.next}}</span>
				<svg width="7" height="11" viewBox="0 0 7 11" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M1.3332 0.00683594L0.158203 1.18184L3.97487 5.00684L0.158203 8.83184L1.3332 10.0068L6.3332 5.00684L1.3332 0.00683594Z" fill="white"/>
				</svg>
			</div>
		</div>
		<?php
		include_once SBI_BUILDER_DIR . 'templates/sections/feeds-type.php';
		include_once SBI_BUILDER_DIR . 'templates/sections/select-source.php';
		?>
	</div>
	<div class="sbi-fb-ft-action sbi-fb-slctfd-action sbi-fb-fs">
		<div class="sbi-fb-wrapper">
			<div class="sbi-fb-slctf-back sbi-fb-hd-btn sbi-btn-grey" @click.prevent.default="creationProcessBack()"><svg width="7" height="11" viewBox="0 0 7 11" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.3415 1.18184L5.1665 0.00683594L0.166504 5.00684L5.1665 10.0068L6.3415 8.83184L2.52484 5.00684L6.3415 1.18184Z" fill="#141B38"/>
			</svg>
			<span>{{genericText.back}}</span>
		</div>
		<div class="sbi-fb-btn sbi-fb-slctf-nxt sbi-fb-btn-ac sbi-btn-orange" :data-active="creationProcessCheckAction()" @click.prevent.default="creationProcessNext()">
			<span>{{genericText.next}}</span>
			<svg width="7" height="11" viewBox="0 0 7 11" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1.3332 0.00683594L0.158203 1.18184L3.97487 5.00684L0.158203 8.83184L1.3332 10.0068L6.3332 5.00684L1.3332 0.00683594Z" fill="white"/>
			</svg>
		</div>
	</div>
</div>
</div>
