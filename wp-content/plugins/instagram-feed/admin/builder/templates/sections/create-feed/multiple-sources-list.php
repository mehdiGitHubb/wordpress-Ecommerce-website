<div class="sbi-feedtype-section sbi-fb-fs" v-for="(feedType, feedTypeID) in selectSourceScreen.multipleTypes" v-if="checkMultipleFeedTypeActive(feedTypeID)" :data-type="feedTypeID">
    <button class="sbi-fd-lst-btn sbi-fd-lst-btn-delete sbi-fb-tltp-parent" @click.prevent.default="removeFeedTypeSource(feedTypeID)">
         <div class="sbi-fb-tltp-elem"><span>{{genericText.removeSource.replace(/ /g,"&nbsp;")}}</span></div>
         <div v-html="svgIcons['delete']"></div>
     </button>
     <div class="sbi-feedtype-sec-heading sbi-fb-fs">
        <div class="sbi-feedtype-icon-wrap" v-html="svgIcons[feedType.icon]"></div>
        <div class="sbi-feedtype-sec-wrap">
            <div class="sbi-feedtype-sec-icon-heading sbi-fb-fs">
                <span v-html="feedType.heading"></span>
                <a class="sbi-business-required" href="" v-if="feedType.businessRequired" v-html="genericText.businessRequired"></a>
            </div>
            <div class="sbi-feedtype-sec-desc sbi-fb-fs sb-caption sb-lighter" v-html="feedType.description"></div>

            <div class="sbi-fb-fs" v-if="feedType.actionType == 'inputHashtags'">
                <div class="sbi-hashtag-items-list">
                    <div class="sbi-hashtag-item" v-for="hashtag in (customizerFeedData ? selectedHastagsPopup : selectedHastags)">
                        <span>{{hashtag}}</span>
                        <div class="sbi-hashtag-item-delete" @click.prevent.default="removeHashtag(hashtag)"></div>
                    </div>
                </div>
                <div class="sbi-hashtag-fetchby sbi-fb-fs">
                    <span class="sbi-feedtype-sec-desc sbi-fb-fs sb-caption sb-lighter">{{selectSourceScreen.hashtagGetBy}}</span>
                    <div class="sbi-hashtag-fetchby-chbx sbi-fb-fs">
                        <div class="sbi-fb-stp-src-type sb-small-p sb-dark-text" :data-active="(customizerFeedData ? customizerFeedData.settings.order : hashtagOrderBy) == 'recent'" @click.prevent.default="selectedHastagOrderBy('recent')">
                            <div class="sbi-fb-chbx-round"></div>{{genericText.mostRecent}}
                        </div>
                        <div class="sbi-fb-stp-src-type sb-small-p sb-dark-text" :data-active="(customizerFeedData ? customizerFeedData.settings.order : hashtagOrderBy) == 'top'" @click.prevent.default="selectedHastagOrderBy('top')">
                            <div class="sbi-fb-chbx-round"></div>{{genericText.topRated}}
                        </div>
                    </div>
                </div>
                <input type="text" class="sbi-fb-wh-inp sbi-public-hashinp sbi-fb-fs" placeholder="#hashtag1, #hashtag2" v-model="hashtagInputText" @keyup="hashtagWriteDetect()">
            </div>
            <div class="sbi-selected-sources-ctn sbi-fb-fs" v-if="feedType.actionType == 'addSource'">
                <div class="sbi-selected-source-item" v-for="selectedSource in returnSelectedSourcesByType(feedTypeID)">
                    <div class="sbi-selected-source-item-avatar" v-if="returnAccountAvatar(selectedSource)">
                        <img :src="returnAccountAvatar(selectedSource)">
                    </div>
                    <span>@{{selectedSource.username}}</span>
                    <div class="sbi-selected-source-item-icon" v-html="svgIcons['delete']" @click.prevent.default="removeSourceFromFeedType( selectedSource, feedTypeID )"></div>
                </div>
                <button class="sbi-fb-hd-btn sb-btn-grey sb-button-standard" data-icon="left" @click.prevent.default="sourcesList.length > 0 ? openSourceListPopup(feedTypeID) : activateView('sourcePopup', 'creationRedirect', false)">
                    <svg width="17" height="17" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.66634 5.66634H5.66634V9.66634H4.33301V5.66634H0.333008V4.33301H4.33301V0.333008H5.66634V4.33301H9.66634V5.66634Z"/>
                    </svg>
                    <span v-if="! returnSelectedSourcesByType(feedTypeID).length">{{genericText.addSource}}</span>
                    <span v-if="returnSelectedSourcesByType(feedTypeID).length">{{genericText.addAnotherSource}}</span>

                </button>
            </div>
        </div>

    </div>
</div>