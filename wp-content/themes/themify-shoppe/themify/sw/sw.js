'use strict';
function RTrim(str,ch='/'){
    return (str[str.length - 1] === ch) ? str.slice(0, -1) : str;
}
const params=new URL(self.location.href), 
	pathname=params.pathname,
	min = pathname.indexOf('.min.js',5)!==-1?'.min':'';
	let _arr=pathname.split('/');
	_arr.pop();//remove sw.js
	_arr.pop();//remove sw folder
	
	let fwFolder=_arr.pop(),
		themeFolder=_arr.pop(),
		wpThemesFolder=_arr.pop(),
		wpContentFolder=_arr.pop();
	if(_arr[0]===''){
		_arr.shift();
	}
	_arr=_arr.join('/');
	if(_arr!==''){
		_arr+='/';
	}
	console.log(_arr);
const 
	DOMAIN=params.origin+'/'+_arr,
	FV = params.searchParams.get('ver'),
	TV=params.searchParams.get('tv'),
	WP=params.searchParams.get('wp'),
	WC=params.searchParams.get('wc'),
	JQV=params.searchParams.get('jq'),
	SWV='5.3.6',
	WPMIN=params.searchParams.get('wpm')?'':'.min',
	ISMULTISITE=!!params.searchParams.get('m'),
	THEME_URL=DOMAIN+wpContentFolder+'/'+wpThemesFolder+'/'+themeFolder+'/',
	FW_URL=THEME_URL+fwFolder+'/',
	BUILDER_URL=FW_URL+'themify-builder/',
	PLUGINS_URL=DOMAIN+wpContentFolder+'/'+params.searchParams.get('pl')+'/',
	INCLUDES_URL=RTrim(params.searchParams.get('i').replace('6789',DOMAIN))+'/',
	WC_URL=WC?(PLUGINS_URL+'woocommerce/'):null,
	CACHE_PREFIX='tf-cache-',
	CACHE_KEY_FW=CACHE_PREFIX+'fw'+FV,
	CACHE_KEY_THEME=CACHE_PREFIX+'theme-'+themeFolder+TV,
	CACHE_KEY_WP=CACHE_PREFIX+'wp'+WP,
	CACHE_KEY_WC=WC?(CACHE_PREFIX+'wc'+WC):null,
	CACHE_KEY_FONTS=CACHE_PREFIX+'fonts',
	CACHE_KEY_OTHERS=CACHE_PREFIX+'others',
	APP_CACHES={
		[CACHE_KEY_WP]:[
			INCLUDES_URL+'js/jquery/jquery'+WPMIN+'.js?ver='+JQV
		],
		[CACHE_KEY_FW]:[
			FW_URL+'css/modules/animate.min.css?ver=3.6.2',
			FW_URL+'css/grids/auto_tiles'+min+'.css?ver='+FV,
			FW_URL+'css/swiper/swiper'+min+'.css?ver='+FV,
			FW_URL+'css/swiper/effects/fade'+min+'.css?ver='+FV,
			FW_URL+'js/main'+min+'.js?ver='+FV,
			FW_URL+'js/modules/fixedheader'+min+'.js?ver='+FV,
			FW_URL+'js/modules/tf_wow'+min+'.js?ver='+FV,
			FW_URL+'js/modules/jquery.isotope.min.js?ver=3.0.6',
			FW_URL+'js/modules/isotop'+min+'.js?ver='+FV,
			FW_URL+'js/modules/autoTiles'+min+'.js?ver='+FV,
			FW_URL+'js/modules/infinite'+min+'.js?ver='+FV,
			FW_URL+'js/modules/themify.dropdown'+min+'.js?ver='+FV,
			FW_URL+'js/modules/themify.sidemenu'+min+'.js?ver='+FV,
			FW_URL+'js/themify.gallery'+min+'.js?ver='+FV,
			FW_URL+'js/modules/themify.carousel'+min+'.js?ver='+FV,
			FW_URL+'js/modules/lax'+min+'.js?ver='+FV,
			FW_URL+'js/modules/video-player'+min+'.js?ver='+FV,
			FW_URL+'js/modules/audio-player'+min+'.js?ver='+FV,
			FW_URL+'js/modules/edge.Menu'+min+'.js?ver='+FV,
			FW_URL+'js/modules/swiper/swiper.min.js?ver='+SWV,
			FW_URL+'js/modules/swiper/modules/autoplay.min.js?ver='+SWV,
			FW_URL+'js/modules/swiper/effects/flip.min.js?ver='+SWV,
			FW_URL+'js/modules/swiper/effects/fade.min.js?ver='+SWV,
			FW_URL+'megamenu/js/themify.mega-menu'+min+'.js?ver='+FV,
			BUILDER_URL+'js/themify.builder.script'+min+'.js?ver='+FV,
			BUILDER_URL+'css/modules/sliders/carousel'+min+'.css?ver='+FV,
			BUILDER_URL+'css/modules/sliders/gallery'+min+'.css?ver='+FV,
			BUILDER_URL+'css/modules/sliders/slider'+min+'.css?ver='+FV,
			BUILDER_URL+'css/modules/sliders/testimonial-slider'+min+'.css?ver='+FV,
			BUILDER_URL+'js/modules/fullwidthRows'+min+'.js?ver='+FV,
			BUILDER_URL+'js/modules/menu'+min+'.js?ver='+FV,
			BUILDER_URL+'js/modules/feature'+min+'.js?ver='+FV,
			BUILDER_URL+'js/modules/accordion'+min+'.js?ver='+FV,
			BUILDER_URL+'js/modules/tab'+min+'.js?ver='+FV,
			BUILDER_URL+'js/modules/parallax'+min+'.js?ver='+FV,
			BUILDER_URL+'js/modules/video'+min+'.js?ver='+FV
		],
		[CACHE_KEY_THEME]:[
			THEME_URL+'js/themify.script'+min+'.js?ver='+TV
		],
		[CACHE_KEY_FONTS]:[],
		[CACHE_KEY_OTHERS]:[]
   };
   if(CACHE_KEY_WC!==null){
		APP_CACHES[CACHE_KEY_WC]=[
			WC_URL+'assets/js/frontend/woocommerce'+WPMIN+'.js?ver='+WC,
			WC_URL+'assets/js/frontend/add-to-cart'+WPMIN+'.js?ver='+WC,
			WC_URL+'assets/js/frontend/cart-fragments'+WPMIN+'.js?ver='+WC,
			WC_URL+'assets/js/js-cookie/js.cookie'+WPMIN+'.js?ver=2.1.4-wc.'+WC,
			WC_URL+'assets/js/jquery-blockui/jquery.blockUI'+WPMIN+'.js?ver=2.7.0-wc.'+WC,
			WC_URL+'assets/js/frontend/add-to-cart-variation'+WPMIN+'.js?ver='+WC,
			WC_URL+'assets/js/frontend/single-product'+WPMIN+'.js?ver='+WC,
			WC_URL+'assets/css/photoswipe/photoswipe.min.css?ver='+WC,
			WC_URL+'assets/css/photoswipe/default-skin/default-skin.min.css?ver='+WC,
			WC_URL+'assets/js/photoswipe/photoswipe'+WPMIN+'.js?ver=4.1.1-wc.'+WC,
			WC_URL+'assets/js/zoom/jquery.zoom'+WPMIN+'.js?ver=1.7.21-wc.'+WC,
			WC_URL+'assets/js/flexslider/jquery.flexslider'+WPMIN+'.js?ver=2.7.2-wc.'+WC
	   ];
	   APP_CACHES[CACHE_KEY_FW].push(FW_URL+'js/modules/wc'+min+'.js?ver='+FV);
	   APP_CACHES[CACHE_KEY_FW].push(FW_URL+'js/modules/sticky-buy'+min+'.js?ver='+FV);
   }
   const CACHE_KEYS=Object.keys(APP_CACHES);
   
self.addEventListener('install', function(event){
    console.log(params,FW_URL,THEME_URL,WC_URL,INCLUDES_URL,DOMAIN);
    event.waitUntil(caches.keys().then(function (keys) {
        return Promise.all(CACHE_KEYS.map(function (cacheKey) {
          if (keys.indexOf(cacheKey) === -1) {
            return caches.open(cacheKey).then(function (cache) {
              return cache.addAll(APP_CACHES[cacheKey])
            })
			.catch(function(error) {
			  console.error('Pre-fetching failed for cache: '+cacheKey, error);
			});
          } 
          else {
            return Promise.resolve(true);
          }
        }))
		.then(function () {
          return self.skipWaiting();
        });
  }));
});
self.addEventListener('activate', function(event){
	console.log('active');
	event.waitUntil(
		caches.keys().then(function (keys) {
		  return Promise.all(keys.map(function (key) {
			if (CACHE_KEYS.indexOf(key) === -1 && key.indexOf(CACHE_PREFIX)===0 && (ISMULTISITE===false || CACHE_KEY_THEME!==key)) {
				return caches.delete(key);
			}
		  }))
		  .then(function () {
				return self.clients.claim();
		   });
		})
	);
});
self.addEventListener('fetch', function(ev) {
	if(ev.request.method==='GET' && ev.request.destination!=='fetch'){
		const req=ev.request,
			url= req.url,
			isGoogeRequest=url.indexOf('ajax.googleapis')!==-1,
			isGoogleFont=url.indexOf('fonts.googleapis')!==-1 || url.indexOf('fonts.gstatic')!==-1;
		if(req.cache === 'only-if-cached' && req.mode !== 'same-origin' || (url.indexOf('/testdemo/')===-1)){
			return;
		}
		let type=req.destination;
		if(!type){
			const path=new URL(url).pathname;
			let ext=path.substring(path.lastIndexOf('.') + 1);
			if(ext){
				ext=ext.toLowerCase();
				if(ext==='css' || isGoogleFont===true){
					type='style';
				}
				else if(ext==='js'){
					type='script';
				}
				else if(ext==='jpeg' || ext==='jpg' || ext==='png' || ext==='webp' || ext==='gif' || ext==='bmp' || ext==='apng' || ext==='ico'){
					type='image';
				}
				else if(ext==='woff2' || ext==='woff' || ext==='ttf' || ext==='EOT' || ext==='OTF'){
					type='font';
				}
			}
		}
		if(isGoogleFont===true || isGoogeRequest===true || type==='image' || url.indexOf(DOMAIN)!==-1){
			if(type==='script' || type==='style' || type==='font' || type==='image'){
				ev.respondWith(async function() {
					
					// Respond from the cache if we can
					const cachedResponse = await caches.match(req);
					if (cachedResponse){
						return cachedResponse;
					}
					return fetch(req,{importance:'low'}).then(function(response) {
						if(response && response.ok && (response.type === 'basic' || isGoogleFont===true)){
							let cacheKey;
							if(isGoogleFont===true){
								cacheKey=CACHE_KEY_FONTS;
							}
							else if(url.indexOf(FW_URL)!==-1 || url.indexOf('themify-css')!==-1){
								cacheKey=CACHE_KEY_FW;
							}
							else if(url.indexOf(THEME_URL)!==-1 || url.indexOf('themify-customizer')!==-1){
								cacheKey=CACHE_KEY_THEME;
							}
							else if(WC_URL!==null && url.indexOf(WC_URL)!==-1){
								cacheKey=CACHE_KEY_WC;
							}
							else if(url.indexOf(INCLUDES_URL)!==-1){
								cacheKey=CACHE_KEY_WP;
							}
							else{
								cacheKey=CACHE_KEY_OTHERS;
							}
							
							const responseClone=response.clone();
							ev.waitUntil(async function() {
								try {
                                                                    const cache = await caches.open(cacheKey);
                                                                    await cache.put(req, await responseClone);
								}
								catch (err) {
                                                                    if (err.name === 'QuotaExceededError') {
                                                                          // Fallback code goes here
                                                                    }
								}
							}());
							
						}
						return response;
					}).catch(function(error) {
						// This catch() will handle exceptions thrown from the fetch() operation.
						// Note that a HTTP error response (e.g. 404) will NOT trigger an exception.
						// It will return a normal response object that has the appropriate error code set.
						console.error('Fetching failed:', error);

						throw error;
					  });
				 }());
			}
		}
	}
});