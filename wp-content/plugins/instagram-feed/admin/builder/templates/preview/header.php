<section id="sbi-header-section" class="sbi-preview-header-ctn sbi-fb-fs sbi-preview-section" :data-dimmed="!isSectionHighLighted('header')" v-if="valueIsEnabled(customizerFeedData.settings.showheader) && customizerFeedData.header && sourcesList.length">
	<!--Visual header-->
	<div class="sbi-preview-header-visual sbi-fb-fs" v-if="customizerFeedData.settings.headertype == 'visual'">
		<div class="sbi-preview-header-cover sbi-fb-fs" v-if="valueIsEnabled(customizerFeedData.settings.headercover)">
			<img v-if="hasOwnNestedProperty(customizerFeedData,  'header.cover.source')" :src="customizerFeedData.header.cover.source">
			<div class="sbi-preview-header-likebox" v-if="valueIsEnabled(customizerFeedData.settings.headerbio)">
				<div v-html="svgIcons['facebook']"></div>
				<span>{{customizerFeedData.header.fan_count}}</span>
			</div>
		</div>
		<div class="sbi-preview-header-info-ctn sbi-fb-fs">
			<div class="sbi-preview-header-avatar" v-if="valueIsEnabled(customizerFeedData.settings.headername)" >
				<img v-if="hasOwnNestedProperty(customizerFeedData,  'header.picture.data.url')" :src="customizerFeedData.header.picture.data.url">
			</div>
			<div class="sbi-preview-header-info">
				<h3 class="sbi-preview-header-name" v-if="valueIsEnabled(customizerFeedData.settings.headername)" >{{customizerFeedData.header.name}}</h3>
				<div class="sbi-preview-header-bio" v-if="valueIsEnabled(customizerFeedData.settings.headerbio)">{{customizerFeedData.header.about}}</div>
			</div>
		</div>
	</div>
	<!--Text header-->
	<div class="sbi-preview-header-text sbi-fb-fs" v-if="customizerFeedData.settings.headertype == 'text'">
		<h3 class="sbi-preview-header-text-h sbi-fb-fs">
			<div class="sbi-preview-header-text-icon" v-if="valueIsEnabled(customizerFeedData.settings.headericonenabled)">
				<span class="sbi-header-text-icon fa fab " :class="'fa-'+customizerFeedData.settings.headericon"></span>
			</div>
			<span class="sbi-header-text" v-html="customizerFeedData.settings.headertext"></span>
		</h3>
	</div>

</section>

<svg width="24px" height="24px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="sbi-screenreader" role="img" aria-labelledby="metaSVGid metaSVGdesc" alt="Comments Box SVG icons"><title id="metaSVGid">Comments Box SVG icons</title><desc id="metaSVGdesc">Used for the like, share, comment, and reaction icons</desc><defs><linearGradient id="angryGrad" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#f9ae9e"></stop><stop offset="70%" stop-color="#ffe7a4"></stop></linearGradient><linearGradient id="likeGrad"><stop offset="25%" stop-color="rgba(0,0,0,0.05)"></stop><stop offset="26%" stop-color="rgba(255,255,255,0.7)"></stop></linearGradient><linearGradient id="likeGradHover"><stop offset="25%" stop-color="#a3caff"></stop><stop offset="26%" stop-color="#fff"></stop></linearGradient><linearGradient id="likeGradDark"><stop offset="25%" stop-color="rgba(255,255,255,0.5)"></stop><stop offset="26%" stop-color="rgba(255,255,255,0.7)"></stop></linearGradient></defs></svg>