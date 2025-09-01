;(function(window){

	if ( ! fp.cegg ) return;

	if ( allow_loading_cegg() ) {
		load_cegg();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_cegg() ) load_cegg(); } );
	}

	// FUNCTIONS

	function allow_loading_cegg(){
		return FP.isAllowedToLoad( 'cegg', ['stats', 'personalisation'], ['script_src'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	// THIS GETS FIRED ONCE CE LOADS
	( window.CE_API || ( window.CE_API = [] ) ).push( () => {
		
		

		// TAG WITH PAGE TYPE
		if ( fp.cegg.tag_pagetype && fpdata.page_type ) window.CE2.addTag( 'Page type: ' + fpdata.page_type );

		fp.loaded.push( 'cegg' );
		if ( fp.main.debug ) console.log('[FP] Crazy Egg loaded');
		FP.runFn( 'FP.fns.load_cegg_footer' );
	} );

	function load_cegg() {
	    FP.getScript( fp.cegg.script_src, false, { 'async' : 'async' } );
	}

})(window);
