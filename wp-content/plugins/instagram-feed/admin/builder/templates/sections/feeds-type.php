<div class="sbi-fb-types-ctn sbi-fb-fs sb-box-shadow" v-if="viewsActive.selectedFeedSection == 'feedsType'">
	<div class="sbi-fb-types sbi-fb-fs">
		<h4>{{selectFeedTypeScreen.feedTypeHeading}}</h4>
		<span class="sbi-fb-types-desc">{{selectFeedTypeScreen.mainDescription}}</span>
		<div class="sbi-fb-types-list sbi-fb-types-list-free">
			<div class="sbi-fb-type-el" v-for="(feedTypeEl, feedTypeIn) in feedTypes" :data-active="selectedFeed.includes(feedTypeEl.type) && feedTypeEl.type != 'socialwall'" :data-type="feedTypeEl.type" @click.prevent.default="chooseFeedType(feedTypeEl)">
				<div class="sbi-fb-type-el-img sbi-fb-fs" v-html="svgIcons[feedTypeEl.icon]"></div>
				<div class="sbi-fb-type-el-info sbi-fb-fs">
					<p class="sb-small-p sb-bold sb-dark-text" v-html="feedTypeEl.title"></p>
					<a href="" v-if="feedTypeEl.businessRequired != undefined && feedTypeEl.businessRequired">
						<span  v-html="genericText.businessRequired"></span>
						<div class="sb-control-elem-tltp" v-if="feedTypeEl.tooltip != undefined" @mouseover.prevent.default="toggleElementTooltip(feedTypeEl.tooltip, 'show', feedTypeEl.tooltipAlign ? feedTypeEl.tooltipAlign : 'center' )" @mouseleave.prevent.default="toggleElementTooltip('', 'hide')">
							<div class="sb-control-elem-tltp-icon" v-html="svgIcons['tooltipHelpSvg']"></div>
						</div>
					</a>
					<span class="sb-caption sb-lightest sb-small-text">{{feedTypeEl.description}}</span>
				</div>
			</div>

		</div>
	</div>

	<div class="sbi-fb-adv-types sbi-fb-fs">
		<h4>{{selectFeedTypeScreen.advancedHeading}}<span class="sb-breadcrumb-pro-label">PRO</span></h4>
		<div class="sbi-fb-types-list sbi-fb-types-list-pro sbi-fb-fs">
			<div class="sbi-fb-type-el-pro" v-for="(advFeedTypeEl, advFeedTypeIn) in advancedFeedTypes" @click.prevent.default="chooseFeedType(advFeedTypeEl)">
				<div class="sbi-fb-type-el-pro-img"  v-html="svgIcons[advFeedTypeEl.icon +'Free']"></div>
				<span>{{advFeedTypeEl.title}}</span>
			</div>
		</div>
	</div>

</div>