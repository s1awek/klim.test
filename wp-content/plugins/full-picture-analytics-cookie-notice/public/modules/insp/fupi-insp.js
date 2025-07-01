;(function(window){

	if ( allow_loading_insp() ) { 
		load_insp();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_insp() ) load_insp(); } );
	};

	// FUNCTIONS

	function allow_loading_insp(){
		return FP.isAllowedToLoad( 'insp', ['stats'], ['id'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_insp() {

		if ( fp.insp.ab_test_script && fpdata.cookies.personalisation ) {
			(function() {
				var insp_ab_loader = true;
				window.__insp = window.__insp || [];
				__insp.push(['wid', fp.insp.id]);
				var ldinsp = function(){
				if(typeof window.__inspld != "undefined") return; window.__inspld = 1; var insp = document.createElement('script'); insp.type = 'text/javascript'; insp.async = true; insp.id = "inspsync"; insp.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cdn.inspectlet.com/inspectlet.js?wid='+ fp.insp.id +'&r=' + Math.floor(new Date().getTime()/3600000); var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(insp, x);if(typeof insp_ab_loader != "undefined" && insp_ab_loader){ var adlt = function(){ var e = document.getElementById('insp_abl'); if(e){ e.parentNode.removeChild(e); __insp.push(['ab_timeout']); }}; var adlc = "body{ visibility: hidden !important; }"; var adln = typeof insp_ab_loader_t != "undefined" ? insp_ab_loader_t : 800; insp.onerror = adlt; var abti = setTimeout(adlt, adln); window.__insp_abt = abti; var abl = document.createElement('style'); abl.id = "insp_abl"; abl.type = "text/css"; if(abl.styleSheet) abl.styleSheet.cssText = adlc; else abl.appendChild(document.createTextNode(adlc)); document.head.appendChild(abl); } };
				setTimeout(ldinsp, 0);
			})();
		} else {
			(function() {
				window.__insp = window.__insp || [];
				__insp.push(['wid', fp.insp.id]);
				var ldinsp = function(){
				if(typeof window.__inspld != "undefined") return; window.__inspld = 1; var insp = document.createElement('script'); insp.type = 'text/javascript'; insp.async = true; insp.id = "inspsync"; insp.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cdn.inspectlet.com/inspectlet.js?wid=' + fp.insp.id + '&r=' + Math.floor(new Date().getTime()/3600000); var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(insp, x); };
				setTimeout(ldinsp, 0);
			})();
		};

		// TAG SESSION WITH STATIC TAGS
		let static_tags = {},
			has_static_tags = false;

		if ( fp.insp.tag_user_role && fpdata.user.role ) { static_tags['user role'] = fpdata.user.role; has_static_tags = true; }
		if ( fp.insp.tag_pagetype && fpdata.page_type ) { static_tags['page type'] = fpdata.page_type; has_static_tags = true; }
		if ( fp.insp.tag_pageauthor && fpdata.author_name ) { static_tags['page author'] = fpdata.author_name; has_static_tags = true; }
		if ( fp.insp.tag_referrer && document.referrer.length > 0 && document.referrer.indexOf( document.location.origin ) == -1 ) {
			static_tags['referrer'] = document.referrer;
			has_static_tags = true;
		}

		// Static tags - tag with utm parameters
		if ( fp.insp.tag_utm && document.location.href.indexOf( 'utm_source' ) != -1 ) {
			[ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ].forEach( function (utm_name) {
				has_static_tags = true;
				var utm_val = FP.getUrlParamByName( utm_name );
				if ( utm_val && utm_val.length > 1 ) static_tags[utm_name] = decodeURI( utm_val );
			} )
		};

		if ( has_static_tags ) {
			__insp.push( [ 'tagSession', static_tags ] );
			if ( fp.vars.debug ) console.log('[FP] Inspectlet tags: ', static_tags );
		}

		
		
		fp.loaded.push( 'insp' );
		FP.runFn( 'FP.fns.load_insp_footer' );
	}

})(window);
