<div class="sbi-settings-cta" :class="{'sbi-show-features': freeCtaShowFeatures}" v-if="feedsList.length > 0 || legacyFeedsList.length > 0">
    <div class="sbi-cta-head-inner">
        <div class="sbi-cta-title">
            <div class="sbi-plugin-logo">
                <svg width="32" height="33" viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 8.41406C11.5 8.41406 7.91406 12.0703 7.91406 16.5C7.91406 21 11.5 24.5859 16 24.5859C20.4297 24.5859 24.0859 21 24.0859 16.5C24.0859 12.0703 20.4297 8.41406 16 8.41406ZM16 21.7734C13.1172 21.7734 10.7266 19.4531 10.7266 16.5C10.7266 13.6172 13.0469 11.2969 16 11.2969C18.8828 11.2969 21.2031 13.6172 21.2031 16.5C21.2031 19.4531 18.8828 21.7734 16 21.7734ZM26.2656 8.13281C26.2656 7.07812 25.4219 6.23438 24.3672 6.23438C23.3125 6.23438 22.4688 7.07812 22.4688 8.13281C22.4688 9.1875 23.3125 10.0312 24.3672 10.0312C25.4219 10.0312 26.2656 9.1875 26.2656 8.13281ZM31.6094 10.0312C31.4688 7.5 30.9062 5.25 29.0781 3.42188C27.25 1.59375 25 1.03125 22.4688 0.890625C19.8672 0.75 12.0625 0.75 9.46094 0.890625C6.92969 1.03125 4.75 1.59375 2.85156 3.42188C1.02344 5.25 0.460938 7.5 0.320312 10.0312C0.179688 12.6328 0.179688 20.4375 0.320312 23.0391C0.460938 25.5703 1.02344 27.75 2.85156 29.6484C4.75 31.4766 6.92969 32.0391 9.46094 32.1797C12.0625 32.3203 19.8672 32.3203 22.4688 32.1797C25 32.0391 27.25 31.4766 29.0781 29.6484C30.9062 27.75 31.4688 25.5703 31.6094 23.0391C31.75 20.4375 31.75 12.6328 31.6094 10.0312ZM28.2344 25.7812C27.7422 27.1875 26.6172 28.2422 25.2812 28.8047C23.1719 29.6484 18.25 29.4375 16 29.4375C13.6797 29.4375 8.75781 29.6484 6.71875 28.8047C5.3125 28.2422 4.25781 27.1875 3.69531 25.7812C2.85156 23.7422 3.0625 18.8203 3.0625 16.5C3.0625 14.25 2.85156 9.32812 3.69531 7.21875C4.25781 5.88281 5.3125 4.82812 6.71875 4.26562C8.75781 3.42188 13.6797 3.63281 16 3.63281C18.25 3.63281 23.1719 3.42188 25.2812 4.26562C26.6172 4.75781 27.6719 5.88281 28.2344 7.21875C29.0781 9.32812 28.8672 14.25 28.8672 16.5C28.8672 18.8203 29.0781 23.7422 28.2344 25.7812Z" fill="url(#paint0_linear_2256_988)"/>
                    <defs>
                        <linearGradient id="paint0_linear_2256_988" x1="11.4367" y1="61.0289" x2="77.7836" y2="-6.69609" gradientUnits="userSpaceOnUse">
                            <stop stop-color="white"/>
                            <stop offset="0.147864" stop-color="#F6640E"/>
                            <stop offset="0.443974" stop-color="#BA03A7"/>
                            <stop offset="0.733337" stop-color="#6A01B9"/>
                            <stop offset="1" stop-color="#6B01B9"/>
                        </linearGradient>
                    </defs>
                </svg>

            </div>
            <div class="sbi-plugin-title">
                <h3>{{genericText.getMoreFeatures}}</h3>
                <div class="sbi-plugin-title-bt">
                    <span class="sbi-cta-discount-label">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.841 9.65008L10.341 2.15008C10.0285 1.84015 9.60614 1.6664 9.16602 1.66675H3.33268C2.89066 1.66675 2.46673 1.84234 2.15417 2.1549C1.84161 2.46746 1.66602 2.89139 1.66602 3.33342V9.16675C1.66584 9.38668 1.7092 9.60446 1.79358 9.80756C1.87796 10.0106 2.00171 10.195 2.15768 10.3501L9.65768 17.8501C9.97017 18.16 10.3926 18.3338 10.8327 18.3334C11.274 18.3316 11.6966 18.1547 12.0077 17.8417L17.841 12.0084C18.154 11.6973 18.3308 11.2747 18.3327 10.8334C18.3329 10.6135 18.2895 10.3957 18.2051 10.1926C18.1207 9.98952 17.997 9.80513 17.841 9.65008ZM10.8327 16.6667L3.33268 9.16675V3.33342H9.16602L16.666 10.8334L10.8327 16.6667ZM5.41602 4.16675C5.66324 4.16675 5.90492 4.24006 6.11048 4.37741C6.31604 4.51476 6.47626 4.70999 6.57087 4.93839C6.66548 5.1668 6.69023 5.41814 6.642 5.66061C6.59377 5.90309 6.47472 6.12582 6.2999 6.30063C6.12508 6.47545 5.90236 6.5945 5.65988 6.64273C5.4174 6.69096 5.16607 6.66621 4.93766 6.5716C4.70925 6.47699 4.51403 6.31677 4.37668 6.11121C4.23933 5.90565 4.16602 5.66398 4.16602 5.41675C4.16602 5.08523 4.29771 4.76729 4.53213 4.53287C4.76655 4.29844 5.0845 4.16675 5.41602 4.16675Z" fill="#663D00"/>
                        </svg>
                        {{genericText.liteFeedUsers}}
                    </span>
                    <span class="sbi-cta-btn">
                        <a :href="upgradeUrl" class="sbi-btn-blue" target="_blank">
                            {{genericText.tryDemo}}
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.166016 10.6584L8.99102 1.83341H3.49935V0.166748H11.8327V8.50008H10.166V3.00841L1.34102 11.8334L0.166016 10.6584Z" fill="white"/>
                            </svg>
                        </a>
                    </span>
                </div>
            </div>
        </div>

    </div>
    <div class="sbi-cta-boxes" v-if="freeCtaShowFeatures">
       <div class="sbi-cta-box">
            <span class="sbi-cta-box-icon" v-html="svgIcons.ctaBoxes.hashtag"></span>
            <span class="sbi-cta-box-title">{{genericText.ctaHashtag}}</span>
        </div>
        <div class="sbi-cta-box">
            <span class="sbi-cta-box-icon" v-html="svgIcons.ctaBoxes.layout"></span>
            <span class="sbi-cta-box-title">{{genericText.ctaLayout}}</span>
        </div>
        <div class="sbi-cta-box">
            <span class="sbi-cta-box-icon" v-html="svgIcons.ctaBoxes.popups"></span>
            <span class="sbi-cta-box-title">{{genericText.ctaPopups}}</span>
        </div>
        <div class="sbi-cta-box">
            <span class="sbi-cta-box-icon" v-html="svgIcons.ctaBoxes.filter"></span>
            <span class="sbi-cta-box-title">{{genericText.ctaFilter}}</span>
        </div>
    </div>
    <div class="sbi-cta-much-more" v-if="freeCtaShowFeatures">
        <div class="sbi-cta-mm-left">
            <h4>{{genericText.andMuchMore}}</h4>
        </div>
        <div class="sbi-cta-mm-right">
            <ul>
                <li v-for="item in genericText.sbiFreeCTAFeatures">{{item}}</li>
            </ul>
        </div>
    </div>
</div>

<div class="sbi-cta-toggle-features" v-if="feedsList.length > 0 || legacyFeedsList.length > 0">
    <button class="sbi-cta-toggle-btn" @click="ctaToggleFeatures">
        <span v-if="!freeCtaShowFeatures">{{genericText.ctaShowFeatures}}</span>
        <span v-else>{{genericText.ctaHideFeatures}}</span>

        <svg v-if="freeCtaShowFeatures" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.91 15.41L12.5 10.83L17.09 15.41L18.5 14L12.5 8L6.5 14L7.91 15.41Z" fill="#141B38"/>
        </svg>

        <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.41 8.59009L12 13.1701L16.59 8.59009L18 10.0001L12 16.0001L6 10.0001L7.41 8.59009Z" fill="#141B38"/>
        </svg>
    </button>
</div>