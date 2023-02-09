<div class="sbi-fb-fs" v-if="checkSingleFeedType('hashtag')">
	<div class="sbi-fb-slctsrc-content sbi-fb-fs">
        <div class="sbi-fb-sec-heading sbi-fb-fs">
            <h4>{{selectSourceScreen.mainHashtagHeading}}</h4>
            <span class="sb-caption sb-lighter">{{selectSourceScreen.hashtagDescription}}</span>
        </div>
        <div class="sbi-fb-fs">
            <div class="sbi-hashtag-items-list">
                <div class="sbi-hashtag-item" v-for="hashtag in selectedHastags">
                    <span>{{hashtag}}</span>
                    <div class="sbi-hashtag-item-delete" @click.prevent.default="removeHashtag(hashtag)"></div>
                </div>
            </div>
            <div class="sbi-hashtag-fetchby sbi-fb-fs">
                <span class="sbi-feedtype-sec-desc sbi-fb-fs sb-caption sb-lighter">{{selectSourceScreen.hashtagGetBy}}</span>
                <div class="sbi-hashtag-fetchby-chbx sbi-fb-fs">
                    <div class="sbi-fb-stp-src-type sb-small-p sb-dark-text" :data-active="hashtagOrderBy == 'recent'" @click.prevent.default="hashtagOrderBy = 'recent'">
                        <div class="sbi-fb-chbx-round"></div>{{genericText.mostRecent}}
                    </div>
                    <div class="sbi-fb-stp-src-type sb-small-p sb-dark-text" :data-active="hashtagOrderBy == 'top'" @click.prevent.default="hashtagOrderBy = 'top'">
                        <div class="sbi-fb-chbx-round"></div>{{genericText.topRated}}
                    </div>
                </div>
            </div>
            <input type="text" class="sbi-fb-wh-inp sbi-public-hashinp sbi-fb-fs" placeholder="#hashtag1, #hashtag2" v-model="hashtagInputText" @keyup="hashtagWriteDetect">
        </div>
	</div>
</div>