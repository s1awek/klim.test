function fupi_pin(){

	if ( allow_loading_pin() ) { 
		load_pin();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_pin() ) load_pin(); } );
	};

	// FUNCTIONS

	function allow_loading_pin(){
		return FP.isAllowedToLoad( 'pin', ['stats','marketing'], ['id'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_pin() {

		!function(e){if(!window.pintrk){window.pintrk = function () {
		window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
		n=window.pintrk;n.queue=[],n.version="3.0";var
		t=document.createElement("script");t.async=!0,t.src=e;var
		r=document.getElementsByTagName("script")[0];
		r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");

		var email = {};
		
		
		pintrk('load', fp.pin.id, email);
		pintrk('page');

		// TRACK SEARCH
		if ( fp.pin.track_search && ( fpdata.page_type == 'Search' || fpdata.page_type == 'Woo Product Search' ) ) {
			pintrk( 'track', 'search', { 'search_query' : fpdata.search_query } );
		}

		// TRACK PRODUCT CATEGORY

		if ( fpdata.page_type == 'Woo Category' ){
			pintrk('track', 'viewcategory', {
				'line_items' : [{
					'product_category': fpdata.page_title
				}]
			});
		}

		FP.loaded('pin','pin','[FP] Pinterest loaded');
	}

};

FP.load('pin', 'fupi_pin', ['head_helpers'])
