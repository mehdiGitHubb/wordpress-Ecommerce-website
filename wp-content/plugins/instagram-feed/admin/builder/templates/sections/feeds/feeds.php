<div class="sbi-fd-lst-bigctn sbi-fb-fs" v-if="feedsList != null && feedsList.length > 0">

	<div class="sbi-fd-lst-bulk-ctn sbi-fb-fs">
		<select class="sbi-fd-lst-bulk-select sbi-fb-select sb-caption" v-model="selectedBulkAction">
			<option value="false">{{allFeedsScreen.bulkActions}}</option>
			<option value="delete">{{genericText.delete}}</option>
		</select>
		<button class="sbi-fd-lst-bulk-btn sbi-btn-grey sb-button-small sb-button" @click.prevent.default="bulkActionClick()">{{genericText.apply}}</button>
		<div class="sbi-fd-lst-pagination-ctn" v-if="feedPagination.feedsCount != null && feedPagination.feedsCount > 0">
			<span class="sbi-fd-lst-count sb-caption">{{feedPagination.feedsCount +' '+ (feedPagination.feedsCount > 1 ? genericText.items : genericText.item)}}</span>
			<div class="sbi-fd-lst-pagination" v-if="feedPagination.pagesNumber != null && feedPagination.pagesNumber > 1">
				<button class="sbi-fd-lst-pgnt-btn sbi-fd-pgnt-prev sb-btn-grey" :data-active="feedPagination.currentPage == 1 ? 'false' : 'true'" :disabled="feedPagination.currentPage == 1" @click.prevent.default="feedListPagination('prev')"><</button>
				<span class="sbi-fd-lst-pgnt-info">
					{{feedPagination.currentPage}} of {{feedPagination.pagesNumber}}
				</span>
				<button class="sbi-fd-lst-pgnt-btn sbi-fd-pgnt-next sb-btn-grey" :data-active="feedPagination.currentPage == feedPagination.pagesNumber ? 'false' : 'true'" :disabled="feedPagination.currentPage == feedPagination.pagesNumber" @click.prevent.default="feedListPagination('next')">></button>
			</div>
		</div>
	</div>
    <div class="sbi-table-wrap" v-bind:class="{ 'sb-onboarding-highlight' : viewsActive.onboardingStep === 2 && allFeedsScreen.onboarding.type === 'single' }">
	<table>
		<thead class="sbi-fd-lst-thtf sbi-fd-lst-thead">
			<tr>
				<th>
					<div class="sbi-fd-lst-chkbx" @click.prevent.default="selectAllFeedCheckBox()" :data-active="checkAllFeedsActive()"></div>
				</th>
				<th>
					<span class="sb-caption sb-lighter">{{allFeedsScreen.columns.nameText}}</span>
				</th>
				<th>
					<span class="sb-caption sb-lighter">{{allFeedsScreen.columns.shortcodeText}}</span>
				</th>
				<th>
					<span class="sb-caption sb-lighter">{{allFeedsScreen.columns.instancesText}}</span>
				</th>
				<th class="sbi-fd-lst-act-th">
					<span class="sb-caption sb-lighter">{{allFeedsScreen.columns.actionsText}}</span>
				</th>
			</tr>
		</thead>
		<tbody  class="sbi-fd-lst-tbody">
			<tr v-for="(feed, feedIndex) in feedsList">
				<td>
					<div class="sbi-fd-lst-chkbx" @click.prevent.default="selectFeedCheckBox(feed.id)" :data-active="feedsSelected.includes(feed.id)"></div>
				</td>
				<td>
					<a :href="builderUrl+'&feed_id='+feed.id" class="sbi-fd-lst-name sb-small-p sb-bold">{{feed.feed_name}}</a>
					<span class="sbi-fd-lst-type sb-caption sb-lighter">{{feed.settings.type}}</span>
				</td>
				<td>
                    <div class="sb-flex-center">
                        <span class="sbi-fd-lst-shortcode sb-caption sb-lighter">[instagram-feed feed={{feed.id}}]</span>
                        <div class="sbi-fd-lst-shortcode-cp sbi-fd-lst-btn sbi-fb-tltp-parent" @click.prevent.default="copyToClipBoard('[instagram-feed feed='+feed.id+']')">
                            <div class="sbi-fb-tltp-elem"><span>{{(genericText.copy +' '+ genericText.shortcode).replace(/ /g,"&nbsp;")}}</span></div>
                            <div v-html="svgIcons['copy']"></div>
                        </div>
                    </div>
				</td>
				<td class="sb-caption sb-lighter">
                    <div class="sb-instances-cell">
                        <span>{{genericText.usedIn}} <span class="sbi-fb-view-instances sbi-fb-tltp-parent" :data-active="feed.instance_count < 1 ? 'false' : 'true'" @click.prevent.default="feed.instance_count > 0 ? viewFeedInstances(feed) : checkAllFeedsActive()">{{feed.instance_count + ' ' + (feed.instance_count !== 1 ? genericText.places : genericText.place)}} <div class="sbi-fb-tltp-elem" v-if="feed.instance_count > 0"><span>{{genericText.clickViewInstances.replace(/ /g,"&nbsp;")}}</span></div></span></span>
                    </div>
                </td>
				<td class="sbi-fd-lst-actions">
                    <div class="sb-flex-center">
                        <a class="sbi-fd-lst-btn sbi-fb-tltp-parent":href="builderUrl+'&feed_id='+feed.id">
                            <div class="sbi-fb-tltp-elem"><span>{{genericText.edit.replace(/ /g,"&nbsp;")}}</span></div>
                            <div v-html="svgIcons['edit']"></div>
                        </a>
                        <button class="sbi-fd-lst-btn sbi-fb-tltp-parent" @click.prevent.default="feedActionDuplicate(feed)">
                            <div class="sbi-fb-tltp-elem"><span>{{genericText.duplicate.replace(/ /g,"&nbsp;")}}</span></div>
                            <div v-html="svgIcons['duplicate']"></div>
                        </button>
                        <button class="sbi-fd-lst-btn sbi-fd-lst-btn-delete sbi-fb-tltp-parent" @click.prevent.default="openDialogBox('deleteSingleFeed', feed)">
                            <div class="sbi-fb-tltp-elem"><span>{{genericText.delete.replace(/ /g,"&nbsp;")}}</span></div>
                            <div v-html="svgIcons['delete']"></div>
                        </button>
                    </div>
				</td>

			</tr>
		</tbody>
		<tfoot class="sbi-fd-lst-thtf sbi-fd-lst-tfoot">
			<tr>
				<td>
					<div class="sbi-fd-lst-chkbx" @click.prevent.default="selectAllFeedCheckBox()" :data-active="checkAllFeedsActive()"></div>
				</td>
				<td>
					<span>{{allFeedsScreen.columns.nameText}}</span>
				</td>
				<td>
					<span>{{allFeedsScreen.columns.shortcodeText}}</span>
				</td>
				<td>
					<span>{{allFeedsScreen.columns.instancesText}}</span>
				</td>
				<td>
					<span>{{allFeedsScreen.columns.actionsText}}</span>
				</td>
			</tr>
		</tfoot>
	</table>
    </div>
</div>