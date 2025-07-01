
;(function(window){

	if ( allow_loading_hotj() ) { 
		load_hotj();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_hotj() ) load_hotj(); } );
	};

	// FUNCTIONS

	function allow_loading_hotj(){
		 return FP.isAllowedToLoad( 'hotj', ['stats'], ['id'], false, fp.hotj && fp.hotj.no_pii ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_hotj() {

		window.hj=window.hj||function(){(hj.q=hj.q||[]).push(arguments)};

		// LOAD HOTJAR

	    (function(h,o,t,j,a,r){
	        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
	        h._hjSettings={hjid:fp.hotj.id,hjsv:6};
	        a=o.getElementsByTagName('head')[0];
	        r=o.createElement('script');r.async=1;
	        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
	        a.appendChild(r);
	    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');

		// TAG SESSION WITH STATIC TAGS
		let static_tags = [];

		if ( fp.hotj.tag_user_role && fpdata.user.role ) static_tags.push( 'user role: ' + fpdata.user.role );
		if ( fp.hotj.tag_pagetype && fpdata.page_type) static_tags.push( 'page type: ' + fpdata.page_type );
		if ( fp.hotj.tag_pageauthor && fpdata.author_name ) static_tags.push( 'page author: ' + fpdata.author_name );

		// Add UTM parameters to static tags
		if ( fp.hotj.tag_utm && document.location.href.indexOf( 'utm_source' ) != -1 ) {
			[ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ].forEach( function ( utm_name ) {
				var utm_val = FP.getUrlParamByName( utm_name );
				if ( utm_val && utm_val.length > 1 ) static_tags.push( utm_name + ' ' + decodeURI( utm_val ) );
			} );
		};

		// Send static tags
		if ( static_tags.length > 0 ) {
			hj( 'event', static_tags );
			if ( fp.vars.debug ) console.log('[FP] Hotjar tag: sent static tags', static_tags );
		}

		

		// LOAD FOOTER SCRIPTS
		fp.loaded.push('hotj');
		FP.runFn( 'FP.fns.load_hotj_footer' );
	}

})(window);
