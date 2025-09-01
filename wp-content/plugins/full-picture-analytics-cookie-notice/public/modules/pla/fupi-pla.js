;(function(window){

	if ( allow_loading_pla() ) { 
		load_pla();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_pla() ) load_pla(); } );
	}

	function allow_loading_pla() {
		return FP.isAllowedToLoad( 'pla', [], [], false, true ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_pla(){

		if ( ! fp.pla.pla_use || fp.pla.pla_use != 'extend' ){ // for backwards compat
			
			let data_domain = fp.pla.domain ? fp.pla.domain : location.host,
				script_domain = fp.pla.custom_domain ? '//' + fp.pla.custom_domain : 'https://plausible.io';
	
			FP.getScript( script_domain + '/js/script.revenue.manual.pageview-props.js', after_pla_loads, { 'data-domain' : data_domain, 'defer' : 'defer' } );

		} else {
			setTimeout( ()=>{ after_pla_loads( false ) }, 250); // give plausible script some tiem to load
		}
	}

	function after_pla_loads ( send_pageview = true ) {
		
		window.plausible = window.plausible || function() { ( window.plausible.q = window.plausible.q || [] ).push(arguments) };

		let props = {};

		// PAGE TYPE
		if ( fp.pla.track_pagetype && fpdata.page_type ) props['page_type'] = fpdata.page_type;

		// PAGE TITLE
		if ( fp.pla.track_pagetitle && fpdata.page_title ) props['page_title'] = fpdata.page_title;

		// AUTHOR DISPLAY NAME
		if ( fp.pla.track_author && fpdata.author_name ) props['page_author'] = fpdata.author_name;

		// PAGE NUMBER
		if ( fp.pla.track_pagenum && fpdata.page_number > 0 ) props['page_number'] = fpdata.page_number;

		// PAGE LANGUAGE
		if ( fp.pla.page_lang ) props['page_lang'] = document.documentElement.lang || 'undefined';

		// PAGE ID
		if ( fp.pla.track_pageid && fpdata.page_id && fpdata.page_id > 0 ) props['page_id'] = fpdata.page_id;

		// SEARCH TERMS
		if ( ( fpdata.page_type == 'Search' || fpdata.page_type == 'Woo Product Search' ) && fpdata.search_query ) props['user_search'] = fpdata.search_query;

		// USER ROLE
		if ( fp.pla.track_user_role && fpdata.user.role && fpdata.user.role.length > 0 ) props['user_role'] = fpdata.user.role;

		

		if ( send_pageview ) plausible( 'pageview', {'props': props} );

		fp.loaded.push( 'pla' );
		if ( fp.main.debug ) console.log('[FP] Plausible loaded');
		FP.runFn( 'FP.fns.load_pla_footer' );
	};

})(window);
