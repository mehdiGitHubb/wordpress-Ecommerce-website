/*backgroundSlider for row/column/subrow*/
;
(($,Themify,vars) =>{
    'use strict';
    /*v2.0.4*/
    !function(b,c,a){b.fn.tb_backstretch=function(d,f){return void 0!==f&&(void 0===f.mode&&(void 0!==c.themifyScript&&c.themifyScript.backgroundMode?f.mode=themifyScript.backgroundMode:void 0!==c.themifyVars&&c.themifyVars.backgroundMode&&(f.mode=themifyVars.backgroundMode)),void 0===f.position&&(void 0!==c.themifyScript&&c.themifyScript.backgroundPosition&&c.themifyScript.backgroundPosition?f.position=c.themifyScript.backgroundPosition:void 0!==c.themifyVars&&c.themifyVars.backgroundPosition&&c.themifyVars.backgroundPosition&&(f.position=c.themifyVars.backgroundPosition)),void 0===f.is_first&&(f.is_first=!0)),(d===a||0===d.length)&&b.error("No images were supplied for Backstretch"),0===b(c).scrollTop()&&c.scrollTo(0,0),this.each(function(){var a=b(this),c=a.data("tb_backstretch");if(c){if("string"==typeof d&&"function"==typeof c[d])return void c[d](f);f=b.extend(c.options,f),c.destroy(!0)}c=new g(this,d,f),a.data("tb_backstretch",c)})},b.tb_backstretch=function(a,c){return b("body").tb_backstretch(a,c).data("tb_backstretch")},b.expr.pseudos.tb_backstretch=function(c){return b(c).data("tb_backstretch")!==a},b.fn.tb_backstretch.defaults={centeredX:!0,centeredY:!0,duration:5e3,fade:0,mode:"",position:""};var d={left:0,top:0,overflow:"hidden",margin:0,padding:0,height:"100%",width:"100%",zIndex:-999999},f={position:"absolute",display:"none",margin:0,padding:0,border:"none",width:"auto",height:"auto",maxHeight:"none",maxWidth:"none",zIndex:-999999},g=function(f,g,h){for(var i in this.options=b.extend({},b.fn.tb_backstretch.defaults,h||{}),this.images=b.isArray(g)?g:[g],this.images)b("<img />")[0].src=void 0===this.images[i].url?this.images[i]:this.images[i].url;this.isBody=f===document.body,this.$container=b(f),this.$root=this.isBody?b(c):this.$container,f=this.$container.children(".tb_backstretch").first(),this.$wrap=f.length?f:b("<div class=\"tb_backstretch\"></div>").css(d).appendTo(this.$container),this.isBody||(f=this.$container.css("position"),g=this.$container.css("zIndex"),this.$container.css({position:"static"===f?"relative":f,zIndex:"auto"===g?0:g,backgroundImage:"none"}),this.$wrap.css({zIndex:-999998})),this.$wrap.css({position:this.isBody?"fixed":"absolute"}),this.index=0,this.show(this.index,this.options.is_first),b(c).on("resize.tb_backstretch",b.proxy(this.resize,this)).on("orientationchange.tb_backstretch",b.proxy(function(){this.isBody&&0===c.pageYOffset&&(c.scrollTo(0,1),this.resize())},this))};g.prototype={resize(){try{var a,b={left:0,top:0},d=this.isBody?this.$root.width():this.$root.innerWidth(),e=d,f=this.isBody?c.innerHeight?c.innerHeight:this.$root.height():this.$root.innerHeight(),g=e/this.$img.data("ratio");"best-fit"===this.options.mode&&(e/f>parseFloat(this.$img.data("ratio"))?this.$img.addClass("best-fit-vertical").removeClass("best-fit-horizontal"):this.$img.addClass("best-fit-horizontal").removeClass("best-fit-vertical")),g>=f?(a=(g-f)/2,this.options.centeredY&&(b.top="-"+a+"px")):(a=((e=(g=f)*this.$img.data("ratio"))-d)/2,this.options.centeredX&&(b.left="-"+a+"px")),this.$wrap.css({width:d,height:f}).find("img:not(.deleteable)").css({width:e,height:g}).css(b)}catch(a){}return this},show(c,d){if(!(Math.abs(c)>this.images.length-1)){var e,g=this,h=g.$wrap.find("img").addClass("deleteable"),i={relatedTarget:g.$container[0]};return g.$container.trigger(b.Event("tb_backstretch.before",i),[g,c]),this.index=c,clearInterval(g.interval),g.$img=b("<img />").css(f).bind("load",function(a){var e=this.width||b(a.target).width();if(a=this.height||b(a.target).height(),b(this).data("ratio",e/a),void 0!==g){if("best-fit"===g.options.mode){b(this).parent().addClass("best-fit-wrap");var f=e/a;g.$wrap.width()/g.$wrap.height()>f?b(this).addClass("best-fit best-fit-vertical"):b(this).addClass("best-fit best-fit-horizontal")}else"fullcover"===g.options.mode&&g.options.position?b(this).addClass("fullcover-"+g.options.position):"kenburns-effect"===g.options.mode&&b(this).parent().addClass("kenburns-effect");d&&b(this).show()}b(this).fadeIn(g.options.speed||g.options.fade,function(){h.remove(),g.paused||g.cycle(),b(["after","show"]).each(function(){g.$container.trigger(b.Event("tb_backstretch."+this,i),[g,c])})}),g.resize()}).appendTo(g.$wrap),void 0===g.images[c].url?e=g.images[c]:(e=g.images[c].url,g.images[c].alt&&g.$img.attr("alt",g.images[c].alt)),g.$img.attr("src",e),g}},next(){return this.show(this.index<this.images.length-1?this.index+1:0,!1)},prev(){return this.show(0===this.index?this.images.length-1:this.index-1,!1)},pause(){return this.paused=!0,this},resume(){return this.paused=!1,this.next(),this},cycle(){return 1<this.images.length&&(clearInterval(this.interval),this.interval=setInterval(b.proxy(function(){this.paused||this.next()},this),this.options.duration)),this},destroy(a){b(c).off("resize.tb_backstretch orientationchange.tb_backstretch"),clearInterval(this.interval),a||this.$wrap.remove(),this.$container.removeData("tb_backstretch")}}}(jQuery,window);
    
	vars['autoplay']=vars.backgroundSlider && vars.backgroundSlider.autoplay?parseInt(vars.backgroundSlider.autoplay.autoplay, 10):5000;
    const  _init= items=> {
        if (vars.autoplay <= 10) {
            vars.autoplay *= 1000;
        }
        for(let i=items.length-1;i>-1;--i){

            let $thisRowSlider = $(items[i]),
                $backel = $thisRowSlider.parent(),
                childs = items[i].tfTag('li'),
                rsImages = [];
            for(var j=childs.length-1;j>-1;--j){
                rsImages.push({url:childs[j].dataset.bg,alt:childs[j].dataset.bgAlt});
            }
            // Call backstretch for the first time
            $backel.tb_backstretch(rsImages, {
                speed: parseInt(items[i].dataset.sliderspeed),
                duration: vars.autoplay,
                mode: items[i].dataset.bgmode
            });

            // Cache Backstretch object
            let thisBGS = $backel.data('tb_backstretch'),
                sliderDots = $thisRowSlider.find('.row-slider-slides > li'),
                currentClass = 'row-slider-dot-active';

            // Previous and Next arrows
            childs=items[i].querySelectorAll('.row-slider-prev,.row-slider-next,.row-slider-dot');
            for(j=childs.length-1;j>-1;--j){
                childs[j].tfOn('click',function(e){
                    if(this.classList.contains('row-slider-dot')){// Dots
                        thisBGS.show($(this).data('index'));
                    }
                    else{
                        e.preventDefault();
                        this.classList.contains('row-slider-prev')?thisBGS.prev():thisBGS.next();
                    }
                },{passive:childs[j].classList.contains('row-slider-dot')});
            }
            if (sliderDots[0]) {
                sliderDots[0].classList.add(currentClass);
                $backel.on('tb_backstretch.show', (e, data)=> {
                    const currentDot = sliderDots.eq(thisBGS.index);
                    if (currentDot[0]) {
                        sliderDots.removeClass(currentClass);
                        currentDot.addClass(currentClass);
                    }
                });
            }
            if (items[i].dataset.bgmode === 'kenburns-effect') {
                let lastIndex,
                    kenburnsActive = 0;
                const imagesCount = rsImages.length > 4? 4 : rsImages.length,
                    createKenburnIndex = () => {
                        return (kenburnsActive + 1 > imagesCount) ? kenburnsActive = 1 : ++kenburnsActive;
                    };

                $backel.on('tb_backstretch.before', (e, data)=>{
                    setTimeout(() =>{
                        if (lastIndex != data.index) {
                            const $img = data.$wrap.find('img').last();
                            $img.addClass('kenburns-effect' + createKenburnIndex());
                            lastIndex = data.index;
                        }
                    }, 50);

                }).on('tb_backstretch.after', (e, data) =>{

                    const $img = data.$wrap.find('img').last(),
                        expr = /kenburns-effect\d/;
                    if (!expr.test($img.attr('class'))) {
                        $img.addClass('kenburns-effect' + createKenburnIndex());
                        lastIndex = data.index;
                    }

                });
                if(Themify.is_builder_active){
                    $backel.on('backstretch.show', (e, instance, index)=>{
                        // Needed for col styling icon and row grid menu to be above row and sub-row top bars.
                            $backel.css('zIndex', 0);
                    });
                }
            }
        }
    };
    Themify.on('builder_load_module_partial',(el,isLazy)=>{
        let items;
        if(isLazy===true ){
            if(el.tagName!=='DIV' || el.classList.contains('module')){
                return;
            }
            const item = el.querySelector(':scope>.tb_slider');
            if(item===null){
                return;
            }
            items=[item];
        }
        else{
            items = Themify.selectWithParent('tb_slider',el); 
        }
        if(items[0]!==undefined){
            Themify.loadCss(ThemifyBuilderModuleJs.cssUrl + 'backgroundSlider').then(() =>{
                _init(items);
            });
        }
    });

})(jQuery,Themify,tbLocalScript);