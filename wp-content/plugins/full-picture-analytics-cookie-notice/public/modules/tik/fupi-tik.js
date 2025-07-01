;(function(window){

	if ( allow_loading_tik() ) { 
		load_tik();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_tik() ) load_tik(); } );
	}

	// FUNCTIONS

	function allow_loading_tik(){
		 return FP.isAllowedToLoad( 'tik', ['stats'], ['id'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_tik() {

		!function (w, d, t) {
		  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a) };

		  ttq.load(fp.tik.id);
		  ttq.page();
		}(window, document, 'ttq');

		fp.loaded.push('tik');
		FP.runFn( 'FP.fns.load_tik_footer' );
	}

})(window, document);
