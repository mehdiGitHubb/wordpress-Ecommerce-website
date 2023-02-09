/**
 * Video player module
 */
;
((Themify, doc) => {
    'use strict';
    let isLoaded = false;
    const _CLICK_ = Themify.click,
            _IS_IOS_ = /iPhone|iPad|iPod|Mac OS/i.test(window.navigator.userAgent),
            _COOKIE_ = 'tf_video_',
            humanTime = time => {
                time = Infinity === time ? 0 : time;
                const tmp = new Date(time * 1000).toISOString().substr(11, 8).split(':');
                if (tmp[0] === '00') {
                    tmp.splice(0, 1);
                }
                return tmp.join(':');
            },
            requestFullscreen = el => {
                    if (el.requestFullscreen) {
                        return el.requestFullscreen();
                    }
                    if (el.webkitEnterFullscreen) {
                        return el.webkitEnterFullscreen();
                    }
                    if (el.webkitrequestFullscreen) {
                        return el.webkitRequestFullscreen();
                    }
                    if (el.mozRequestFullscreen) {
                        return el.mozRequestFullScreen();
                    }
            },
            exitFullscreen = () => {
                try {
                    if (doc.exitFullscreen) {
                        return doc.exitFullscreen();
                    }
                    if (doc.webkitExitFullscreen) {
                        return doc.webkitExitFullscreen();
                    }
                    if (doc.webkitExitFullscreen) {
                        return doc.webkitExitFullscreen();
                    }
                    if (doc.mozCancelFullScreen) {
                        return doc.mozCancelFullScreen();
                    }
                    if (doc.cancelFullScreen) {
                        return doc.cancelFullScreen();
                    }
                    if (doc.msExitFullscreen) {
                        return doc.msExitFullscreen();
                    }
                    return false;
                } catch (e) {
                    return false;
                }
            },
            getPrefix = el => {
                if (doc.exitFullscreen) {
                    return '';
                }
                if (doc.webkitExitFullscreen || el.webkitSupportsFullscreen) {
                    return 'webkit';
                }
                if (doc.mozCancelFullScreen) {
                    return 'moz';
                }
                if (doc.msExitFullscreen) {
                    return 'ms';
                }
                return false;
            },
            getFullScreenElement = el => {
                const pre = getPrefix(el);
                if (pre === false) {
                    return false;
                }
                if (el.hasOwnProperty('webkitDisplayingFullscreen')) {
                    return el.webkitDisplayingFullscreen;
                }
                return pre === '' ? doc.fullscreenElement : doc[pre + 'FullscreenElement'];
            },
            createSvg = (icon, cl) => {
                const ns = 'http://www.w3.org/2000/svg',
                        use = doc.createElementNS(ns, 'use'),
                        svg = doc.createElementNS(ns, 'svg');
                icon = 'tf-' + icon;
                cl = cl ? (icon + ' ' + cl) : icon;
                svg.setAttribute('class', 'tf_fa ' + cl);
                use.setAttributeNS(null, 'href', '#' + icon);
                svg.appendChild(use);
                return svg;
            },
            getCookie = name => {
                const nameEQ = _COOKIE_ + name + '=';
                for (let ca = doc.cookie.split(';'), i = ca.length - 1; i > -1; --i) {
                    let c = ca[i];
                    while (c[0] === ' ') {
                        c = c.substring(1, c.length);
                    }
                    if (c.indexOf(nameEQ) === 0) {
                        return c.substring(nameEQ.length, c.length);
                    }
                }
                return null;
            },
            setCookie = (name, value, days) => {
                let expires = '';
                name = _COOKIE_ + name;
                if (days) {
                    const date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = ';expires=' + date.toUTCString();
                }
                doc.cookie = name + '=' + (value || '') + expires + ';SameSite=strict;path=/';
            },
            getID = el => {
                return Themify.hash(el.currentSrc.split('.').slice(0, -1).join('.'));
            };
    class Video{
        constructor(el,opt){
            const root=doc.createElement('div');
            this.wrap=doc.createElement('div');
            root.className='tf_vd_root';
            this.wrap.className='wrap tf_abs_t tf_w tf_h';
            root.attachShadow({
                mode:'open'
            }).appendChild(this.wrap);
            this.el=el;
            this.opt=opt;
            this.isPlayList=this.opt && this.opt.tracks;
            this.loadPrms = new Promise(resolve=>{
                if (el.readyState <3) {
                    Themify.requestIdleCallback(() => {
                        el.tfOn('canplay',resolve, {passive: true, once: true})
                        .load();
                    },-1, 200);
                }
                else{
                    resolve();
                }
            });
            this.init();
        }
        init(){
            const fr = doc.createDocumentFragment(),
                el=this.el,
                host=this.wrap.getRootNode().host,
                parentNode = el.parentNode,
                loader = doc.createElement('div'),
                elapsed = parseFloat(getCookie(getID(el))) || 0,
                bigPlay = doc.createElement('button');
            loader.className = 'tf_loader tf_abs_c tf_hide';
            bigPlay.className = 'play flex big_btn tf_abs_c';
            bigPlay.type = 'button';
            parentNode.tabIndex = 0;
            if (!el.hasAttribute('data-hide-controls')) {
                fr.append(bigPlay, this.controls());
            }
            fr.appendChild(loader);
            if(this.isPlayList){
                fr.appendChild(this.tracks());
            }
            requestAnimationFrame(async()=>{
                if (elapsed > 0 && elapsed < el.duration) {
                    el.currentTime = elapsed;
                }
                el.setAttribute('webkit-playsinline', '1');
                el.setAttribute('playsinline', '1');
                el.removeAttribute('controls');
                this.wrap.style.display='none';
                this.wrap.appendChild(fr);
                parentNode.appendChild(host);
                await Promise.all([this.loadPrms,Themify.loadCss(Themify.url+'css/base.min','tf_base',null,this.wrap),Themify.loadCss('video','tf_video',null,this.wrap),isLoaded]);
                if (!el.hasAttribute('data-hide-controls')) {
                    this.wrap.appendChild(doc.tfId('tf_svg').cloneNode(true));
                }
                this.airPlay();
                this.wrap.style.display='';
                el.tfOn('seeking waiting emptied pause play playing ended', e=>{this.events(e);}, {passive: true});
                this.wrap.tfOn(_CLICK_, e=> {
                    this.click(e);
                }, {passive: true});
                parentNode.classList.remove('tf_lazy');
                if(!this.isPlayList && el.dataset.hoverPlay){
                    bigPlay.style.pointerEvents='none';
                    this.hoverPlay();
                }
                else if (el.dataset.autoplay) {
                    this.playVideo();
                }
            });
        }
        click(e){
            const target = e.target,
                seek = target.closest('.sk'),
                el=this.el,
                cl=this.wrap.classList;
            e.stopImmediatePropagation();
            if(target.closest('.controls') && !target.classList.contains('play')){
                return;
            }
            if (el.paused || !Themify.isTouch || target.classList.contains('play') || (!seek && target.classList.contains('show'))) {
                cl.remove('show');
                el.paused? this.playVideo():el.pause();
            }
            else {
                cl.add('show');
                cl.remove('hide_ctl');
                if (this.timeout) {
                    clearTimeout(this.timeout);
                }
                this.timeout = setTimeout(() => {
                    cl.remove('show');
                    this.timeout=null;
                }, 2500);
                if (seek) {
                    el.currentTime += seek.classList.contains('sk_l') ? -15 : 15;
                }
            }
        }
        hoverPlay(wr){
            if(!wr){
                wr=this.wrap;
            }
            const el=wr.tfTag('video')[0] || this.el,
                hover=async wr=>{
                    if(!doc.fullscreenElement || wr.closest('.pl_v')){
                            try{
                                await this.playVideo(el,true);
                            }
                            catch(e){
                            }
                            wr.tfOn('mouseleave',e=>{
                                if(!el.paused && (!doc.fullscreenElement ||wr.closest('.pl_v'))){
                                    el.pause();
                                    el.currentTime = 0;    
                                }
                            },{passive:true,once:true});
                    }
                };
            wr.tfOn('mouseenter',e=>{hover(e.currentTarget);},{passive:true});
            if(el.dataset.forceplay || wr.matches(':hover') ){
                hover(wr);
            }
        }
        showLowPowerControls(){
            if(this.el.hasAttribute('data-hide-controls')&& !this.wrap.tfClass('lpw_play')[0]){
                const play=doc.createElement('button');
                play.className='play flex tf_abs_t lpw_play';
                play.type='button';
                this.wrap.appendChild(play);
            }
        }
        async playVideo(el,err){
            await this.loadPrms;
            if(!el){
                el=this.el;
            }
            if(el.paused){
                if (!el.dataset.playing) {
                    el.dataset.playing=1;
                }
                try{
                    await el.play();
                }
                catch(e){
                     try{
                        if(!el.muted){
                            el.muted = true;
                            await el.play();
                        }
                        else{
                            throw e;
                        }
                    }
                    catch(e){
                        if(el.paused){
                            this.showLowPowerControls();
                            if(err){
                                throw e;
                            }
                        }
                    }
                }
            }
        }
        events(e){
            const type=e.type,
                el=this.el,
                cl=this.wrap.classList;
            if(type==='play'){
                this.togglePlayList(true);
                cl.add('playing');
                for (let allVideos = doc.tfTag('video'), i = allVideos.length - 1; i > -1; --i) {
                    if (allVideos[i] !== el && allVideos[i].readyState === 4&& !allVideos[i].paused && allVideos[i].muted !== true) {
                        allVideos[i].pause();
                    }
                }
            }
            else if(type==='pause'){
               cl.remove('playing');
            }
            else if(type==='seeking' || type==='waiting' || type==='emptied'){
                const ev = type === 'seeking' ? 'seeked' : 'playing';
                cl.add('wait');
                el.tfOn(ev, () => {
                    cl.remove('wait');
                }, {passive: true, once: true}); 
            }
            else if(type==='playing' || type==='ended'){
                if(this.isPlayList && type==='ended'){
                    const wrap= this.wrap,
                    item=wrap.tfClass('pl_sel')[0].nextElementSibling || wrap.tfClass('pl_v')[0];
                    Themify.triggerEvent(item,_CLICK_);
                }
                else{
                    cl.toggle('end',type==='ended');
                }
            }
        }
        airPlay(){
            if (window.WebKitPlaybackTargetAvailabilityEvent) {
                this.el.tfOn('webkitplaybacktargetavailabilitychanged', e => {
                    if (e.availability === 'available') {
                        const airPlay = doc.createElement('button'),
                        full=this.wrap.tfClass('full')[0];
                        if(full){
                            airPlay.className = 'airplay flex';
                            airPlay.tfOn(_CLICK_, () => {
                                this.el.webkitShowPlaybackTargetPicker();
                            }, {passive: true})
                            .appendChild(createSvg('fas-airplay'));
                            full.before(airPlay);
                        }
                    }
                }, {passive: true, once: true});
            }
        }
        pip(){
            const el=this.el,
                f=doc.createDocumentFragment();
            if (!el.hasAttribute('disablePictureInPicture') && doc.pictureInPictureEnabled) {
                const pip = doc.createElement('button');
                pip.tfOn(_CLICK_, e=>{this.pipToggle();}, {passive: true}).className = 'pip flex';
                el.tfOn('enterpictureinpicture', () => {
                    this.wrap.classList.add('tf_is_pip');
                }, {passive: true})
                .tfOn('leavepictureinpicture', () => {
                    this.wrap.classList.remove('tf_is_pip');
                }, {passive: true});
                pip.appendChild(createSvg('fas-external-link-alt'));
                f.appendChild(pip);
            }
            return f;
        }
        pipToggle(){
            const el=this.el;
            try {
                if (el.webkitSupportsPresentationMode) {
                    el.webkitSetPresentationMode(el.webkitPresentationMode === 'picture-in-picture' ? 'inline' : 'picture-in-picture');
                } else {
                    if (getFullScreenElement(el)) {
                        exitFullscreen();
                    }
                    if (el !== doc.pictureInPictureElement) {
                        el.requestPictureInPicture();
                    } else {
                        doc.exitPictureInPicture();
                    }
                }
            } catch (e) {
                
            }
        }
        fullScreenToggle(e){
            const target = e.target,
                    el=this.el;
            if (e.type !== 'dblclick' || (target && !target.closest('.controls'))) {

                if (getFullScreenElement(el)) {
                    exitFullscreen(el);
                } else {
                    const __calback = async () => {
                        try{
                            await requestFullscreen(el.parentNode);
                        } 
                        catch (e) {
                            try{
                                await requestFullscreen(el.parentNode);
                            }
                            catch (e) {
                                
                            }
                        }
                    };
                    if (doc.pictureInPictureElement || el.webkitPresentationMode === 'picture-in-picture') {
                        this.pipToggle();
                        setTimeout(__calback, 80);
                    } else {
                        __calback();
                    }
                }
            }
        }
        controls(){
                let sliding = false,
                        paused = true;//For error play-request-was-interrupted
                const fr = doc.createDocumentFragment(),
                        el=this.el,
                        pre = getPrefix(el),
                        id = getID(el),
                        wrap = doc.createElement('div'),
                        parentNode = el.parentNode,
                        progressWrap = doc.createElement('div'),
                        progressLoaded = doc.createElement('div'),
                        progressCurrent = doc.createElement('div'),
                        hoverHandler = doc.createElement('div'),
                        controls = doc.createElement('div'),
                        currentTime = doc.createElement('div'),
                        totalTime = doc.createElement('div'),
                        play = doc.createElement('button'),
                        fullscreen = doc.createElement('button'),
                        seekLeft = doc.createElement('button'),
                        seekRight = doc.createElement('button'),
                        range = doc.createElement('input'),
                        parentCl = parentNode.classList;
                wrap.className = 'controls flex tf_abs_t tf_box tf_w';
                controls.className = 'btns';
                progressWrap.className = 'pr_wr tf_textl tf_rel';
                progressLoaded.className=progressCurrent.className=range.className='tf_abs_t tf_w tf_h';
                progressLoaded.className+= ' ld';
                progressCurrent.className+= ' cur';
                range.className+= ' pr tf_block tf_opacity';
                range.type = 'range';
                range.value = range.min = 0;
                range.max = 100;
                play.className = 'play flex';
                play.type = fullscreen.type = seekLeft.type = seekRight.type = 'button';
                seekLeft.className = 'sk sk_l flex tf_opacity';
                seekRight.className = 'sk sk_r flex tf_opacity';
                currentTime.className = 'time';
                totalTime.className = 'total';
                hoverHandler.className = 'htime tf_abs_t flex tf_box';
                hoverHandler.style.display='none';
                fullscreen.className = 'full flex';
                this.loadPrms.then(()=>{
                    currentTime.textContent = humanTime(el.currentTime);
                    totalTime.textContent = humanTime(el.duration);
                    if (!Themify.isTouch) {
                        progressWrap.tfOn('pointerenter', function (e) {
                            if (!isNaN(el.duration)) {
                                hoverHandler.style.display='';
                                const w = this.clientWidth,
                                        hoverW = parseFloat(hoverHandler.clientWidth / 2),
                                        duration = el.duration,
                                        move = e => {
                                            const x = e.layerX !== undefined ? e.layerX : e.offsetX,
                                                hoverX=Themify.isRTL?(x+hoverW):(x - hoverW);
                                            if (hoverX>0 && x>=0 && x <= w) {
                                                hoverHandler.style.transform = 'translateX(' + hoverX + 'px)';
                                                if (sliding === false) {
                                                    hoverHandler.textContent = humanTime(parseFloat((x / w)) * duration);
                                                }
                                            }
                                        };
                                this.tfOn('pointerleave', function () {
                                    hoverHandler.style.display='none';
                                    this.tfOff('pointermove', move, {passive: true});
                                }, {passive: true, once: true})
                                .tfOn('pointermove', move, {passive: true});
                            }
                        }, {passive: true});

                    }
                    range.tfOn('input', function (e) {
                        if (!isNaN(el.duration)) {
                            if (!el.paused && paused === true) {
                                el.pause();
                            }
                            sliding = true;
                            const v = parseInt(this.value),
                                    t = v === 100 ? (el.duration - 1) : parseFloat((v * el.duration) / 100).toFixed(4);
                            el.currentTime = t;
                            if (!Themify.isTouch) {
                                hoverHandler.textContent = humanTime(t);
                            }
                        }
                    }, {passive: true})
                    .tfOn('change', e => {
                        if (!isNaN(el.duration)) {
                            sliding = paused = false;
                            if (el.paused) {
                                el.play()
                                .catch({})
                                .finally(() => {
                                    paused = true;
                                });
                            }
                        }
                    }, {passive: true});
                    el.tfOn('progress', function () {
                        if (this.buffered.length > 0) {
                            progressLoaded.style.transform = 'scaleX(' + parseFloat((this.buffered.end(0)) / this.duration).toFixed(4) + ')';
                        }
                    }, {passive: true})
                    .tfOn('durationchange', function () {
                        totalTime.textContent = humanTime(this.duration);
                    }, {passive: true})
                    .tfOn('timeupdate', function () {
                        if (!isNaN(this.duration)) {
                            currentTime.textContent = humanTime(this.currentTime);
                            const v = parseFloat(this.currentTime / el.duration);
                            progressCurrent.style.transform = 'scaleX(' + v.toFixed(4) + ')';
                            if (sliding === false) {
                                range.value = parseInt(v * 100);
                            }
                            setCookie(id, this.currentTime, 30);
                        }
                    }, {passive: true});
                });
                
                controls.append(this.downloadBtn(),this.volumeControl(),this.pip());
                if (pre !== false) {
                    let isAdd = false,
                    timeout2 = false;
                    const mouseMove = () => {
                        toggleControls(true);
                        checkState();
                    },
                    toggleControls = isMoved => {
                        isAdd = isAdd === true || isMoved === true ? false : true;
                                this.wrap.classList.toggle('hide_ctl', isAdd);
                    },
                    checkState = () => {
                        if (timeout2) {
                            clearTimeout(timeout2);
                        }
                        timeout2 = setTimeout(toggleControls, 3000);
                    };
                    fullscreen.tfOn(_CLICK_, e=>{this.fullScreenToggle(e);},{passive:true}).appendChild(createSvg('fas-expand'));
                    if (!Themify.isTouch) {
                        this.wrap.tfOn('dblclick', e=>{this.fullScreenToggle(e);},{passive:true});
                    }
                    parentNode.tfOn(pre + 'fullscreenchange', e => {
                        if (!getFullScreenElement(el)) {
                            this.wrap.classList.remove('fullscreen', 'hide_ctl');
                            if (timeout2) {
                                clearTimeout(timeout2);
                                timeout2=null;
                            }
                            el.tfOff('pause', mouseMove, {passive: true});
                            parentNode.tfOff('pointermove', mouseMove, {passive: true});
                        } else {
                            this.wrap.classList.add('fullscreen');
                            parentNode.tfOn('pointermove', mouseMove, {passive: true});
                            el.tfOn('pause', mouseMove, {passive: true});
                            checkState();
                        }
                    }, {passive: true});
                    controls.appendChild(fullscreen);
                }
                progressWrap.append(progressLoaded, range, progressCurrent, hoverHandler);
                wrap.append(play, currentTime, progressWrap, totalTime, controls);
                seekRight.innerHTML = seekLeft.innerHTML = '<span class="tf_abs_c">15</span>';
                fr.append(seekLeft, seekRight, wrap);
                return fr;
        }
        volumeControl(){
            const volumeWrap = doc.createElement('div'),
                volumeInner = doc.createElement('div'),
                mute = doc.createElement('button'),
                volumeRange = doc.createElement('input'),
                el=this.el,
                id = getID(el),
                vols = getCookie('vol_' + id) || -1,
                fr = doc.createDocumentFragment();
            if(_IS_IOS_ === false || Themify.device !== 'mobile'){
                mute.type = 'button';
                if(!vols){
                    el.muted = 0;
                }
                else{
                    el.volume =vols>-1?vols:.5;
                }

                mute.append(createSvg('fas-volume-up','tf_abs_t tf_w tf_h'), createSvg('fas-volume-mute','tf_abs_t tf_w tf_h'));
                volumeWrap.appendChild(mute);
                volumeRange.min = 0;
                volumeRange.max = 100;
                volumeRange.type = 'range';
                volumeRange.value = vols > -1 ? (vols * 100) : 50;
                volumeRange.className = 'vol tf_block tf_overflow tf_w tf_h';
                volumeInner.className = 'vol_in';
                volumeWrap.className = 'vol_wr flex tf_rel';
                mute.className = 'mute tf_overflow tf_rel';
                if (el.muted) {
                    mute.className += ' muted';
                }
                volumeRange.tfOn('input', function (e) {
                    const v = parseFloat(this.value / 100).toFixed(3);
                    el.volume = v;
                    el.muted = v > 0 ? false : true;
                }, {passive: true});

                el.tfOn('volumechange', function () {
                    mute.classList.toggle('muted',this.muted === true || this.volume === 0);
                    setCookie('vol_' + id, this.volume, 120);
                }, {passive: true});

                mute.tfOn(_CLICK_, () => {
                    el.muted = !el.muted;
                    if (!el.muted && el.volume === 0) {
                        volumeRange.value = 50;
                        Themify.triggerEvent(volumeRange, 'input');
                    }
                }, {passive: true});
                volumeInner.appendChild(volumeRange);
                volumeWrap.appendChild(volumeInner);
                fr.appendChild(volumeWrap);
            }
            return fr;
        }
        downloadBtn(){
            const fr = doc.createDocumentFragment(),
                dl = doc.createElement('a'),
                el=this.el;
            if (el.hasAttribute('data-download')) {
                dl.setAttribute('download', '');
                dl.href = el.src;
                dl.className = 'download flex';
                dl.appendChild(createSvg('fas-download'));
                fr.appendChild(dl);    
            }
            return fr;
        }
        togglePlayList(open){
           this.wrap.classList.toggle('pl_hide',open);
        }
        tracks(){
             const el=this.el,
                tracks=this.opt.tracks,
				currentClicked=doc.createElement('div'),
				container=doc.createElement('div'),
                wrap=doc.createElement('div'),
                selected=doc.createElement('div'),
                close=doc.createElement('button'),
                open=doc.createElement('button'),
                f=doc.createDocumentFragment(),
                f2=doc.createDocumentFragment(),
                f3=doc.createDocumentFragment(),
                obs=new IntersectionObserver((entries,self)=>{
                    for (let i = entries.length - 1; i > -1; --i) {
                        if (entries[i].isIntersecting === true) {
                            let item=entries[i].target,
                                video=item.tfTag('video')[0];
                            self.unobserve(item);
                            if (video.readyState === 4) {
                                item.tfClass('pl_dur')[0].textContent=humanTime(video.duration);
                            }
                            else{
                                let loader=doc.createElement('div');
                                loader.className='tf_loader tf_abs_t';
                                video.after(loader);
                                video.tfOn('durationchange',e=>{
                                    item.tfClass('pl_dur')[0].textContent=humanTime(e.currentTarget.duration);
                                    loader.remove();
                                    this.hoverPlay(item);
                                },{passive:true,once:true})
                                .load();
                            }
                        }
                    }
                },{
                    root:container,
                    threshold:.3
                }),
                setSelected=()=>{
                    requestAnimationFrame(()=>{
                        const playlist=this.wrap.tfClass('pl')[0],
                        selected=playlist.tfClass('pl_sel')[0],
                        src=this.el.currentSrc.replace('#t=1','')+'#t=1';
                        let found;
                        for(let items=playlist.tfTag('video'),i=items.length-1;i>-1;--i){
                            if(src===items[i].src){
                                found=items[i];
                                break;
                            }
                        }
                        if(selected){
                            selected.classList.remove('pl_sel');
                        }
                        if(found){
                            const item=found.closest('.pl_v');
                            playlist.parentNode.tfClass('pl_sel_title')[0].textContent=item.title;
                            item.classList.add('pl_sel');
                        }
                    });
                };
                wrap.className='pl_wr flex tf_abs_t tf_h';
				container.className='pl tf_w tf_h tf_box tf_abs_t tf_scrollbar';
				currentClicked.className='tf_playlist_current';
                close.className='pl_close tf_close tf_box';
                open.className='pl_open tf_box tf_hide';
                open.type=close.type='button';
                selected.className='pl_sel_title tf_overflow';
				for(let i=0,len=tracks.length;i<len;++i){
					if(tracks[i].src){
						tracks[i].src=tracks[i].src.trim();
						let item=doc.createElement('div'),
                            duration=doc.createElement('div'),
                            wr=doc.createElement('div'),
						title=doc.createElement('span'),
                        caption=doc.createElement('span'),
                        video=doc.createElement('video'),
						sizes=tracks[i].dimensions?(tracks[i].dimensions.resized || tracks[i].dimensions.original):{width:el.width,height:el.height};
                        video.preload='none';
						video.width=sizes.width;
						video.height=sizes.height;
                        video.src=tracks[i].src+'#t=1';
                        video.className='tf_w tf_h';
						video.style.aspectRatio=sizes.width/sizes.height;
                        video.muted=true;
						if(!tracks[i].type || video.canPlayType(tracks[i].type)){
							item.className='pl_v tf_rel tf_box';
							wr.className='pl_info flex tf_overflow';
							duration.className='pl_dur tf_textc';
                            obs.observe(item);
							if(tracks[i].title){
                                title.className='pl_title tf_overflow';
								title.textContent=item.title=tracks[i].title;
                                wr.appendChild(title);
							}
							if(tracks[i].caption){
                                caption.className='pl_cap tf_overflow';
								caption.textContent=tracks[i].caption;
                                wr.appendChild(caption);
							}
							item.append(video,wr,duration);
                            f.appendChild(item);
						}
						else{
							tracks.slice(i,1);
						}
					}
					else{
						tracks.slice(i,1);
					}
				}
				wrap.tfOn(_CLICK_,e=>{
                    e.stopPropagation();
					const item=e.target?e.target.closest('.pl_v,.pl_close'):null;
					if(item){
                        if(item.classList.contains('pl_close')){
                            this.togglePlayList(!item.closest('.pl_hide'));
                        }
                        else{
                            el.pause();
                            const video=item.tfTag('video')[0];
                            requestAnimationFrame(()=>{
                                el.style.aspectRatio=video.style.aspectRatio;
                                el.src=video.currentSrc.replace('#t=1','');
                                requestAnimationFrame(()=>{
                                  this.playVideo();
                                });
                            });
                        }
					}
				},{passive:true});
                
                el.tfOn('durationchange',setSelected,{passive:true});
                open.tfOn(_CLICK_,e=>{
                    e.stopImmediatePropagation();
                    this.togglePlayList(false);
                },{passive:true});
                for(let i=8;i>-1;--i){
                    let b=doc.createElement('span');
                    b.className='tf_h tf_w';
                    f3.appendChild(b);
                }
                open.appendChild(f3);
                container.appendChild(f);
                wrap.append(selected,close,container);
                f2.append(open,wrap);
                if(el.readyState === 4){
                    setTimeout(()=>{
                        setSelected();
                    },100);
                }
                else{
                    el.tfOn('loadeddata',setSelected,{passive:true,once:true});
                }
				return f2;
        }
        
    }
    Themify.on('tf_video_init', items => {
        if (isLoaded === false) {
            isLoaded=Themify.fonts(['tf-fas-volume-mute','tf-fas-download', 'tf-fas-volume-up', 'tf-fas-external-link-alt', 'tf-fas-airplay','tf-fas-expand']);
        }
        if (items.length === undefined) {
            items = [items];
        }
        for (let i = items.length - 1; i > -1; --i) {
            let item = items[i],
                p = item.parentNode,
                parent=p.parentNode,
                options;
                if(parent.classList.contains('wp-video-playlist')){
                    let playlist = parent.tfClass('tf-playlist-script')[0] || parent.tfClass('wp-playlist-script')[0];
                    if(playlist){
                        try{
                            options=JSON.parse(playlist.textContent);
                            if(options.type!=='video'){
                                options=false;
                            }
                        }
                        catch(e){
                            options=false;
                        }
                    }
                }
            if(!item.hasAttribute('src') && !item.tfTag('source')[0]){
                if(!options || !options.tracks){
                    continue;
                }
                let track=options.tracks[0].src;
                if(!track){
                    for(let j=1,len=options.tracks.length;j<len;++j){
                        if(options.tracks[j].src){
                            track=options.tracks[j].src;
                            break;
                        }
                    }
                }
                if(!track){
                    continue;
                }

                item.src=track;
            }
            if (!p.classList.contains('tf_vd_lazy')) {
                const lazy = doc.createElement('div');
                lazy.className = 'tf_vd_lazy tf_w tf_h tf_box tf_rel tf_overflow tf_lazy';
                lazy.appendChild(item);
                p.appendChild(lazy);
            }
            new Video(item,options);
        }
    });

})(Themify, document);
