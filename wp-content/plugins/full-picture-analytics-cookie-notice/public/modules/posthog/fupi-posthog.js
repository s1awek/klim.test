;(function(window){

	if ( allow_loading_posthog() ) {
		load_posthog();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_posthog() ) load_posthog(); } );
	}

	function allow_loading_posthog() {
		return FP.isAllowedToLoad( 'posthog', ['stats'], ['api_key'], false, false ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name), integration number, if requires cookies
	}

	function load_posthog(){

		let locale = fp.posthog.data_in_eu ? 'eu' : 'app';

		!function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capturing has_opted_out_capturing opt_in_capturing reset isFeatureEnabled onFeatureFlags getFeatureFlag getFeatureFlagPayload reloadFeatureFlags group updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures getActiveMatchingSurveys getSurveys onSessionId".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
		posthog.init(fp.posthog.api_key,{api_host:'https://' + locale + '.posthog.com'})
        
		fp.loaded.push('posthog');
	}

})(window);