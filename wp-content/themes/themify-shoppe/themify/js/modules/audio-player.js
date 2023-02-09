/**
 * Audio player module
 */
;
((Themify,doc)=>{
    'use strict';
    const _CLICK_=Themify.click,
		_IS_IOS_=/iPhone|iPad|iPod|Mac OS/i.test(window.navigator.userAgent),
		humanTime=time=>{
			time=Infinity===time?0:time;
    		const tmp = new Date(time*1000).toISOString().substr(11, 8).split(':');
			if(tmp[0]==='00'){
				tmp.splice(0,1);
			}
			return tmp.join(':');
		},
		generateTracks=(opt,el)=>{
			const tracks=opt.tracks,
				showArtists=!!opt.artists,
				showImage=!!opt.images,
				showNumbers=!!opt.tracknumbers,
				currentClicked=doc.createElement('div'),
				container=doc.createElement('div'),
                f=doc.createDocumentFragment();
				container.className='tf_audio_playlist';
				currentClicked.className='tf_playlist_current';
				let firstClick=true;
				for(let i=0,len=tracks.length;i<len;++i){
					if(tracks[i].src){
						tracks[i].src=tracks[i].src.trim();
						let item=doc.createElement('div'),
                            duration=doc.createElement('div'),
                            link=doc.createElement('a'),
                            title=doc.createElement('span'),
                            audio = new Audio(tracks[i].src);
						if(!tracks[i].type || audio.canPlayType(tracks[i].type)){
							item.className='tf_playlist_item tf_w tf_rel';
							link.href=tracks[i].src;
							link.className='tf_playlist_caption tf_w';
							duration.className='tf_playlist_length';
							if(!isNaN(audio.duration)|| (tracks[i].meta && tracks[i].meta.length_formatted)){
								duration.textContent=!isNaN(audio.duration)?humanTime(audio.duration):tracks[i].meta.length_formatted;
								audio=null;
							}
							else{
								item.className+=' tf_lazy';
								audio.tfOn('durationchange',function(){
									duration.textContent=humanTime(this.duration);
									audio=null;
									item.classList.remove('tf_lazy');
								},{passive:true,once:true});
								
							}
							if(showNumbers===true){
								link.textContent=(i+1)+'.';
							}
							title.className='tf_playlist_title';
						
							if(tracks[i].caption){
								title.textContent=tracks[i].caption;
							}
							else if(tracks[i].title){
								title.textContent=tracks[i].title;
							}
							if(showArtists===true && tracks[i].meta && tracks[i].meta.artists){
								let artists=doc.createElement('span');
								artists.className='tf_playlist_artist';
								artists.textContent='-'+tracks[i].meta.artists;
								link.appendChild(artists);
							}
							link.appendChild(title);
							item.append(link,duration);
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
				container.tfOn(_CLICK_,function(e){
					const clicked=e.target.closest('.tf_playlist_caption'),
						isCurrent=clicked.parentNode.classList.contains('tf_audio_current');
					if(clicked){
						e.preventDefault();
						e.stopPropagation();
						if(!isCurrent || el.paused){
							if(!isCurrent){
								const prev =this.tfClass('tf_audio_current')[0],
									index=Themify.convert(this.children).indexOf(clicked.parentNode),
									caption=doc.createElement('div'),
									title=doc.createElement('span');
									caption.className='tf_playlist_caption';
									title.className='tf_playlist_title';
								if(firstClick===false){
									el.pause();
									el.tfOn('canplay',function(){
										this.play();
									},{passive:true,once:true})
                                    .src=clicked.getAttribute('href');
									el.load();
								}
								if(prev){
									prev.classList.remove('tf_audio_current');
								}
								clicked.parentNode.classList.add('tf_audio_current');
								currentClicked.innerHTML='';
								if(tracks[index]){
									const track=tracks[index];
									if(showImage && track.thumb && track.thumb.src){
										const img = new Image(),
                                                thumb = doc.createElement('div');
										if(track.thumb.width!==undefined) img.width=track.thumb.width;
										if(track.thumb.height!==undefined) img.height=track.thumb.height;
										img.decoding='async';
										img.src=track.thumb.src;
										thumb.className='post-image';
										img.decode()
                                        .catch(()=>{})
                                        .finally(()=>{
											thumb.appendChild(img);
										});
										currentClicked.appendChild(thumb);
									}
									title.textContent=track.title?track.title:(track.caption?track.caption:'');
									caption.appendChild(title);
									if(tracks.meta){
										if(tracks.meta.album){
											const album =doc.createElement('span');
												album.className=' tf_playlist_album';
												album.textContent=tracks.meta.album;
												caption.appendChild(album);
										}
										if(showArtists && tracks.meta.artists){
											const artist =doc.createElement('span');
											artist.className=' tf_playlist_artist';
											artist.textContent=tracks.meta.artists;
											caption.appendChild(artist);
										}
									}
									currentClicked.appendChild(caption);
								}	
							}
							else{
								el.play();
							}
						}
						firstClick=false;
					}
				});
                
                container.appendChild(f);
				el.before(currentClicked);
				Themify.triggerEvent(container.tfClass('tf_playlist_caption')[0],_CLICK_);
				return container;
		},
		loadMetaData=(el,opt)=>{
			if(el.previousElementSibling && el.previousElementSibling.classList.contains('tf_audio_container')){
				return;
			}
			const container = doc.createElement('div'),
				wrap = doc.createElement('div'),
				progressWrap=doc.createElement('div'),
				progressLoaded=doc.createElement('div'),
				progressCurrent=doc.createElement('div'),
				progressWaiting=doc.createElement('div'),
				hoverHandler=doc.createElement('div'),
				range=doc.createElement('input'),
				volumeRange=doc.createElement('input'),
				volumeWrap=doc.createElement('div'),
				volumeInner=doc.createElement('div'),
				controls=doc.createElement('div'),
				mute=doc.createElement('button'),
				play=doc.createElement('button'),
				currentTime = doc.createElement('div'),
				trackWrap=el.parentNode.closest('.track'),
				totalTime=doc.createElement('div'),
				isPlayList=opt && opt.tracks;
				let paused=true,//For error play-request-was-interrupted
					sliding=false;
				
				container.className='tf_audio_container tf_w tf_rel tf_box';
				wrap.className='tf_audio_wrap tf_w tf_rel tf_box';
				controls.className='tf_audio_controls';
				progressWrap.className='tf_audio_progress_wrap tf_rel tf_textl';
				progressLoaded.className='tf_audio_progress_loaded tf_w tf_h tf_abs';
				progressCurrent.className='tf_audio_progress_current tf_w tf_h tf_abs';
				range.className='tf_audio_progress_range tf_h tf_abs';
				volumeRange.min=range.min=0;
				volumeRange.max=range.max=100;
				volumeRange.type=range.type='range';
				range.value=0;
				volumeRange.value='50%';
				volumeWrap.className='tf_audio_volumn_wrap';
				volumeInner.className='tf_audio_volumn_inner';
				volumeRange.className='tf_audio_volumn_range tf_h tf_overflow';
				mute.className='tf_audio_mute';
				play.className='tf_auido_play';
				play.tabIndex=mute.tabIndex=0;
				play.type=mute.type='button';
				if(el.muted){
					mute.className+=' tf_muted';
				}
				currentTime.className='tf_audio_current_time';
				totalTime.className='tf_audio_total_time';
				hoverHandler.className='tf_audio_hover tf_abs tf_hide tf_box tf_textc';
				currentTime.textContent=humanTime(el.currentTime); 
				totalTime.textContent=humanTime(el.duration); 
				
				play.tfOn(_CLICK_,e=>{
                                    if(e.type==='click'){
                                            e.preventDefault();
                                            e.stopPropagation();
                                    }
                                    el.paused?el.play():el.pause();
				},{passive:_CLICK_!=='click'});
				if(trackWrap){
					const trackTitle=trackWrap.tfClass('track-title')[0];
					if(trackTitle){
						trackTitle.tfOn(_CLICK_,e=>{
							e.preventDefault();
							Themify.triggerEvent(play,_CLICK_);
						});
					}
				}
				mute.tfOn(_CLICK_,e=>{
					if(e.type==='click'){
						e.preventDefault();
						e.stopPropagation();
					}
					el.muted  =!el.muted;
				},{passive:_CLICK_!=='click'});
				if(!Themify.isTouch){
					progressWrap.tfOn('mouseenter',function(){
						if(!isNaN(el.duration)){
							hoverHandler.classList.remove('tf_hide');
							const w =this.clientWidth,
							hoverW=parseFloat(hoverHandler.clientWidth/2),
							duration=el.duration,
							move=e=>{
								const x = e.layerX !== undefined ? e.layerX : e.offsetX,
                                    hoverX=Themify.isRTL?(x+hoverW):(x - hoverW);
                                if (hoverX>0 && x>=0 && x <= w) {
                                    hoverHandler.style.transform = 'translateX(' + hoverX + 'px)';
                                    if (sliding === false) {
                                        hoverHandler.textContent = humanTime(parseFloat((x / w)) * duration);
                                    }
                                }
							};
							this.tfOn('mouseleave',function(){
								hoverHandler.classList.add('tf_hide');
								this.tfOff('mousemove',move,{passive:true});
							},{passive:true,once:true})
                            .tfOn('mousemove',move,{passive:true});
						}
					},{passive:true});
				}
				range.tfOn('input',function(e){
					e.preventDefault();
					e.stopPropagation();
					if(!isNaN(el.duration)){
						if(!el.paused && paused===true){
							el.pause();
						}
						sliding=true;
						const v=parseInt(this.value);
						el.currentTime=v===100?(el.duration-1):parseFloat((v*el.duration)/100).toFixed(4);
					}
				})
                .tfOn('change',e=>{
					e.preventDefault();
					e.stopPropagation();
					if(!isNaN(el.duration)){
						sliding=paused=false;
						if(el.paused){
							el.play().catch({}).finally(() => {
                                paused=true;
                            });
						}
					}
				});
				
				el.tfOn('progress', function() {
					if (this.buffered.length > 0) {
						progressLoaded.style.transform='scaleX('+parseFloat((this.buffered.end(0))/this.duration).toFixed(4)+')';
					}
				},{passive:true})
                .tfOn('durationchange',function() {
					totalTime.textContent=humanTime(this.duration); 
				},{passive:true})
                .tfOn('waiting', function() {
					progressWrap.classList.add('tf_audio_waiting');
					this.tfOn('playing', ()=>{
						progressWrap.classList.remove('tf_audio_waiting');
					},{passive:true,once:true});
				},{passive:true})
                .tfOn('emptied', function(){
					progressWrap.classList.add('tf_audio_waiting');
					this.tfOn('playing', ()=>{
						progressWrap.classList.remove('tf_audio_waiting');
					},{passive:true,once:true});
				},{passive:true})
                .tfOn('pause',()=>{
					play.classList.remove('tf_audio_playing');
				},{passive:true})
                .tfOn('play',function(){
					play.classList.add('tf_audio_playing');
					const allAudios = doc.tfTag('audio');
					for(let i=allAudios.length-1;i>-1;--i){
						if(allAudios[i]!==this){
							allAudios[i].pause();
						}
					}
				},{passive:true})
                .tfOn('timeupdate',function(){
					if(!isNaN(this.duration)){
						currentTime.textContent=humanTime(this.currentTime); 
						let v=parseFloat(this.currentTime/this.duration);
						progressCurrent.style.transform='scaleX('+v.toFixed(4)+')';
						if(sliding===false){
							range.value=parseInt(v*100);
						}
					}
				},{passive:true})
                .tfOn('volumechange',function(){
                    const cl=mute.classList;
					if(this.volume!==0){
						cl.remove('tf_mute_disabled');
					}
					if(this.muted===true || this.volume===0){
						if(this.volume===0){
							cl.add('tf_mute_disabled');
						}
						cl.add('tf_muted');
					}
					else{
						cl.remove('tf_muted');
					}
				},{passive:true});
				progressWrap.append(progressLoaded,progressCurrent,range,hoverHandler);
				volumeWrap.appendChild(mute);
				if(_IS_IOS_===false){
					volumeRange.tfOn('input',function(e){
						e.preventDefault();
						e.stopPropagation();
						el.volume=parseFloat(this.value/100).toFixed(3);
					});
					volumeInner.appendChild(volumeRange);
					volumeWrap.appendChild(volumeInner);
				}
				wrap.append(controls,currentTime,progressWrap,totalTime,volumeWrap);
				container.appendChild(wrap);
				if(isPlayList){
					const playList = container.appendChild(generateTracks(opt,el)),
						prev=doc.createElement('button'),
						next=doc.createElement('button');
					prev.className='tf_playlist_prev tf_play_disabled';
					next.className='tf_playlist_next';
					if(playList.children.length<=1){
						next.className+=' tf_play_disabled';
					}
					prev.tabIndex=next.tabIndex=0;
					prev.type=next.type='button';
					controls.tfOn(_CLICK_,e=>{
						const clicked=e.target,
						cl=clicked.classList;
						if(!cl.contains('tf_play_disabled') && (cl.contains('tf_playlist_prev') ||cl.contains('tf_playlist_next'))){
							if(e.type==='click'){
								e.preventDefault();
								e.stopPropagation();
							}
							const current = playList.tfClass('tf_audio_current')[0];
							if(current){
								const nextTrack = cl.contains('tf_playlist_prev')?current.previousElementSibling:current.nextElementSibling;
								if(nextTrack){
									Themify.triggerEvent(nextTrack.tfClass('tf_playlist_caption')[0],_CLICK_);
									if(cl.contains('tf_playlist_prev')){
										next.classList.remove('tf_play_disabled');
										cl.toggle('tf_play_disabled',!nextTrack.previousElementSibling);
									}
									else{
										prev.classList.remove('tf_play_disabled');
										cl.toggle('tf_play_disabled',!nextTrack.nextElementSibling);
									}
								}
							}
						}
					},{passive:_CLICK_!=='click'});
                    
					el.tfOn('ended',function(){
						if(!next.classList.contains('tf_play_disabled')){
							Themify.triggerEvent(next,_CLICK_);
						}
						else if(this.hasAttribute('data-loop') || this.hasAttribute('loop')){
							const first = playList.tfClass('tf_playlist_caption')[0];
							if(first){
								prev.classList.add('tf_play_disabled');
								next.classList.toggle('tf_play_disabled',playList.children.length<=1);
								Themify.triggerEvent(first,_CLICK_);
							}
						}
						
					},{passive:true});
					
					controls.append(prev,play,next);
				}
				else{
					controls.appendChild(play);
				}
                requestAnimationFrame(()=>{          
                    el.parentNode.classList.remove('tf_lazy');
                    el.before(container);
                    const lazy=el.closest('.tf_lazy');
                    if(lazy!==null){
                        lazy.classList.remove('tf_lazy');
                    }
                    if (el.dataset.autoplay) {
                        el.play();
                    }
                });
		},
		init=(items,options)=>{
			for(let i=items.length-1;i>-1;--i){
                    let item=items[i];
					if(!options){
						let p=item.parentNode.parentNode;
						if(p.classList.contains('wp-audio-playlist')){
							let playlist = p.tfClass('tf-playlist-script')[0];
							if(!playlist){
								playlist=p.tfClass('wp-playlist-script')[0];
							}
							if(playlist){
								options=JSON.parse(playlist.textContent);
								if(options.type!=='audio'){
									options=false;
								}
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
					if(item.readyState===4){
						loadMetaData(item,options);
					}
					else{
                        Themify.requestIdleCallback(()=>{
                            item.tfOn('canplay',function(){
                                loadMetaData(this,options);
                            },{passive:true,once:true})
                            .setAttribute('preload','metadata'); 
                            if(_IS_IOS_===true){
                                item.load();
                            }
                        },-1,200);
					}
			}
		};
    Themify.on('tf_audio_init',(items,options)=>{
        if(items.length===undefined){
                items=[items];
        }
        init(items,options);
    });

})(Themify,document);
