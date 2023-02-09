let TF_Lottie;
((doc, Themify,und)=> {
    'use strict';
    const loaded=new Map(),
    loadJs=async path=>{
        let res=loaded.get(path);
        if(!res){
            res= await Themify.fetch('', null, {
                credentials: 'omit',
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Content-Type':'application/zip'
                }
            }, path);
            loaded.set(path,res);
        }
        return res;
    };
    class LottieElement extends HTMLElement{
        async init(){
            const p=this.tfTag('template')[0],
                args=p?p.content.textContent: this.dataset.args;
            if(p){
                p.remove();
            }
            if(args){
                const lottie=new TF_Lottie(this,JSON.parse(args));
                await lottie.run();
            }
        }
        connectedCallback () {
            if(!this.dataset.lazy){
                this.init();
            }
        }
        attributeChangedCallback(attribute, previousValue, currentValue){
            if(!currentValue && attribute==='data-lazy'){
                this.init();
            }
        }
        disconnectedCallback(){
            const players=lottie.getRegisteredAnimations(),
                id=this.dataset.id;
            for(let i=players.length-1;i>-1;--i){
                if(players[i].animationID===id){
                    players[i].destroy();
                    break;
                }
            }
        }
    }
    LottieElement.observedAttributes = ['data-lazy'];

    TF_Lottie=class{
        constructor(el,options){
            this.el=el;
            this.actions=options.actions;
            this.loop=!!options.loop;
            this.index=0;
        }
        destroy(){
            if(this.player){
                this.player.destroy();
                this.el=this.player=this.actions=null;
            }
        }
        loadChain(){
            const prms=[],
                max=2;
            for(let i=this.index,j=0,n=this.actions.length;i<n;++i){
                let item=this.actions[i],
                    data=item.data || loaded.get(item.path),
                    allow=i<1;
                if(!data){
                    if(allow===false){
                        ++j;
                        if(j>max){
                            break
                        }
                        else if(j===1){
                            allow=true;
                        }
                    }
                    if(allow===true){
                        prms.push(loadJs(item.path)); 
                    }
                }
                else{
                    prms.push(JSON.parse(JSON.stringify(data))); //deep clone
                }
            }
            return prms;
        }
        run(){
            return new Promise(async resolve=>{
                this.action=this.actions[this.index];
                const data=await Promise.all([Themify.loadJs('https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.0/lottie_light.min.js',!!window.lottie,false),...this.loadChain()]),
                loaded=()=>{
                    this.player.removeEventListener('DOMLoaded',loaded);
                    this.init();
                    this.el.style.height='';
                    this.el.dataset.id=this.player.animationID;
                    resolve();
                  //  lottie.setQuality('medium');
                };
                this.el.style.height=(parseFloat(this.el.getBoundingClientRect().width/parseFloat(data[1].w/data[1].h)))+'px';
                if(this.player){
                    this.player.destroy();
                }
                this.player=window.lottie.loadAnimation({
                    container: this.el, 
                    animationData: data[1],
                    renderer: 'svg',
                    loop: false,
                    autoplay: false,
                    rendererSettings: {
                        progressiveLoad: true
                    }
                });    
                this.player.addEventListener('DOMLoaded',loaded);
            });
        }
        async init(){
            const action=this.action,
                player=this.player,
                state=action.state,
                frameId=action.fr_id?action.fr_id.trim():'';
            if(action.fr_id){
                action.fr_id=player.markers.length>0?action.fr_id.trim():'';
                if(action.fr_id==='' || !player.getMarkerData(action.fr_id)){
                    action.fr_id=null;
                }
                else{
                    player.goToAndStop(action.fr_id,false);
                }
            }
            action.tr=action.tr.charAt(0).toUpperCase()+  action.tr.slice(1);
            action.count=action.count>0?parseInt(action.count):1;
            action.dir=parseInt(action.dir)<0?-1:1;
            player.setSpeed((action.speed>0?parseFloat(action.speed):1));
            player.loop=false;
            player.setDirection(action.dir);
            if(action.dir===-1){
                player.goToAndStop(player.totalFrames, !action.fr_id); 
            }
            await this[state]();
            this.loadChain();
            if(action.tr!=='Autoplay'){
                await this['tr'+action.tr]();
            }
            await this.loadNext();
        }
        async autoplay(){
            const prevLoop=this.player.loop;
            this.player.loop=true;
            for(let i=this.action.count-1;i>-1;--i){
                await this.play();
            }
            if(this.actions.length>1){
                this.player.loop=prevLoop;
            }
        }
        click(type){
            return new Promise(resolve=>{
                let count=0;
                const ev=type || Themify.click,
                    click=async e=>{
                    ++count;
                    if(count>=this.action.count){
                        this.el.tfOff(e.type,click,{passive:false});
                        await this.play();
                        resolve();
                    }
                };
                this.el.tfOn(ev,click,{passive:false});
                if(type!=='click' && this.player.isPaused && this.el.matches(':hover')){
                    Themify.triggerEvent(this.el,ev);
                }
            });
        }
        hover(){
            return this.click('pointerenter');
        }
        hold(type){
            return new Promise(resolve=>{
                const leave=async e=>{
                        this.player.trigger('reject');
                        this.player.pause();
                        if(type!=='pausehold'){
                            this.player.setDirection(-1*this.player.playDirection);
                            try{
                                await this.play();
                            }
                            catch(e){

                            }
                        }
                    },
                    enter=async e=>{
                        try{
                            this.player.trigger('reject');
                            this.player.setDirection(this.action.dir);
                            this.el.tfOn('pointerleave',leave,{passive:false,once:true});
                            await this.play();
                            this.el.tfOff(e.type,enter,{passive:false})
                                .tfOff('pointerleave',leave,{passive:false,once:true});
                            resolve();
                        }
                        catch(e){
                            
                        }
                    };
                this.el.tfOn('pointerenter',enter,{passive:false});
                if(this.player.isPaused && this.el.matches(':hover')){
                    Themify.triggerEvent(this.el,'pointerenter');
                }
            });
        }
        pausehold(){
            return this.hold('pausehold');
        }
        trHover(){
            return this.hover();
        }
        trClick(){
            return this.click();
        }
        async loadNext(){
            if(this.actions.length>1){
                ++this.index;
                this.loadChain();
                if(this.index>=this.actions.length){
                    if(this.loop===true){
                        this.index=0;
                    }
                    else{
                        return;
                    }
                }
                await this.run();
            }
        }
        play(){
            return new Promise((resolve,reject)=>{
                requestAnimationFrame(()=>{
                    const delay=this.action.delay>0?parseFloat(this.action.delay)*1000:0,
                        ev=this.player.loop?'loopComplete':'complete',
                        complete=()=>{ 
                            this.player.removeEventListener(ev,complete);
                            this.player.removeEventListener('reject',pause);
                            resolve();
                        },
                        pause=()=>{
                            this.player.removeEventListener('reject',pause);
                            this.player.removeEventListener(ev,complete);
                            reject();
                        },
                        __calback=()=>{
                            this.player.addEventListener(ev,complete);
                            this.player.addEventListener('reject',pause);
                            this.action.fr_id?this.player.goToAndPlay(this.action.fr_id,false):this.player.play();
                        };
                      
                    if(delay>0){
                        setTimeout(()=>{
                            __calback();
                        },delay);
                    }
                    else{
                        __calback();
                    }
                });
            });
        }
    };
    
    customElements.define('tf-lottie', LottieElement);
    
})(document, Themify,undefined);
export{
    TF_Lottie
};