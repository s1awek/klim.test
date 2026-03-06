function fupi_clar(){

	if ( allow_loading_clar() ) { 
		load_clar();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_clar() ) load_clar(); } );
	}

	// FUNCTIONS

	function allow_loading_clar(){
		 return FP.isAllowedToLoad( 'clar', ['stats'], ['id'], false, fp.clar && fp.clar.no_cookie ); // module id, req permission, required data ids, integration number, if has no cookie mode
	}

	function load_clar() {

		// LOAD CLARITY

	    (function(c,l,a,r,i,t,y){
	        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
	        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
	        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
	    })(window, document, "clarity", "script", fp.clar.id);

		// SET CONSENT
		// we must set it here, and not the head.js, because window.clarity is not set before the above script loads
		// clarity('consent', false) is triggered by the consent banner JS

		// if the user agreed in the past
		if ( fpdata.cookies ){
			if ( fpdata.cookies.stats ) window.clarity('consent');
		
		// if the user did not make a choice
		} else {

			// if consent banner is disabled
			if ( ! fp.notice.enabled ) {
				window.clarity('consent');
			
			// if consent banner is enabled
			} else {
				// but it only notifies
				if ( fp.notice.mode == 'notify' || fp.notice.mode == 'optout' ) {
					window.clarity('consent');
				}
			}
		}
		
		// TAG WITH USER ROLE AND STATUS
		if ( fp.clar.tag_user_role && fpdata.user.role ) clarity( 'set', 'User role', fpdata.user.role );

		// TAG WITH PAGE AUTHOR
		if ( fp.clar.tag_pageauthor && fpdata.author_name ) clarity( 'set', 'Author', fpdata.author_name );
		
		// TAG WITH PAGE TYPE
		if ( fp.clar.tag_pagetype && fpdata.page_type ) clarity( 'set', 'Page type', fpdata.page_type );
		
		

		FP.loaded('clar', 'clar','[FP] MS Clarity loaded');
	}
};

FP.load('clar', 'fupi_clar', ['head_helpers'])