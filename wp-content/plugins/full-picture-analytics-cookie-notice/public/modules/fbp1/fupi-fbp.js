;(function(window){

	//
	// ATTENTION!
	// This documentation is more complete than META's and has info on what data CAPI takes (not the same as the JS tracking)
	// >>> https://support.bigcommerce.com/s/article/Meta-Pixel?language=en_US
	//
	function getEventTimeInSecs(diff){
		let d = new Date();
		return Math.floor( ( d.getTime() / 1000 ) - diff ); // timestamp in seconds minus 0 secs (if FB makes problems, increase up to 2 secs);
	}

	function set_fbp_fbc(){
		
		let fbp = FP.readCookie( '_fbp' ),
			fbc = FP.readCookie( '_fbc' ),
			now = Date.now();

		if ( ! fbp ) {
			let rand_num = Math.floor( Math.random() * 10000000000000 );
			fbp = 'fb.1.' + now + '.' + rand_num;
			FP.setCookie( '_fbp', fbp, 90 );
		}

		fp.fbp.fbp = fbp; // save for easy access
		
		if ( ! fbc ) {
			let fbclid = FP.getUrlParamByName('fbclid');
			if ( fbclid ) fbc = 'fb.1.' + now + '.' + fbclid;
			if ( fbc ) FP.setCookie( '_fbc', fbc, 90 );
		}
		
		fp.fbp.fbc = fbc; // save for easy access
	}

	FP.track_fbp_evt = ( custom, evt_name, evt_time, extra_custom_data = false, use_capi = true ) => {

		if ( ! evt_time ) evt_time = getEventTimeInSecs(0);
		
		let custom_data = extra_custom_data ? { ...fp.fbp.custom_data, ...extra_custom_data } : fp.fbp.custom_data;

		// EVENT ID
		let evt_id = evt_time + fp.random + evt_name;

		if ( evt_name == 'woo_enh_order_tracking' ) {
			evt_id = FP.readCookie( 'fp_fbp_enh_order_evt_id' );
			if ( ! evt_id ) return; // cookie may not be available if it timed out
			evt_name = 'Purchase';
		}

		// PIXEL EVT
		if ( typeof fbq !== 'undefined' ) {
			let event_type = custom ? 'trackCustom' : 'track';
			fbq( event_type, evt_name, custom_data, { 'eventID' : evt_id } );
		}
		
		if ( fp.vars.debug ) console.log( '[FP] Meta Pixel "' + evt_name + '" event:', custom_data );

		
	}

    // Load on pageload
	if ( allow_loading_fbp() ) load_fbp();

    // Load on event
	document.addEventListener( 'fp_load_scripts', () => { if ( allow_loading_fbp() ) load_fbp(); } );

    function allow_loading_fbp(){
		return FP.isAllowedToLoad( 'fbp', ['stats', 'marketing'], ['pixel_id'] ); // module id in fp.XX / required cookie permission / setting name with required data (like in fp.ga.setting_name)
	}

	function set_custom_data(){

		let custom_data = {};

		// PAGE TYPE
		if ( fp.fbp.track_pagetype && fpdata.page_type ) custom_data.page_type = fpdata.page_type;

		// PAGE TITLE
		if ( fp.fbp.track_pagetitle && fpdata.page_title ) custom_data.page_title = fpdata.page_title;

		// AUTHOR DISPLAY NAME
		if ( fp.fbp.track_author && fpdata.author_name ) custom_data.author = fpdata.author_name;

		// PAGE NUMBER
		if ( fp.fbp.track_pagenum && fpdata.page_number > 0 ) custom_data.page_number = fpdata.page_number;

		// PAGE LANGUAGE
		if ( fp.fbp.page_lang ) custom_data.page_lang = document.documentElement.lang || 'undefined';

		// PAGE ID
		if ( fp.fbp.track_pageid && fpdata.page_id && fpdata.page_id > 0 ) custom_data.page_id = fpdata.page_id;

		// PUBLISH DATE
		if ( fp.fbp.track_pobdate && fpdata.published && fpdata.published.length > 0 ) custom_data.published = fpdata.published;

		

		// USER'S BROWSER LANGUANGE
		if ( fp.fbp.track_lang ) custom_data.browser_lang = navigator.language;

		// USER ROLE
		if ( fp.fbp.track_user_role && fpdata.user.role && fpdata.user.role.length > 0 ) custom_data.user_type = fpdata.user.role;

		// TERMS
		if ( fp.fbp.track_terms && fpdata.terms && fpdata.terms.length > 0 ) {

			var term_arr = fpdata.terms.map( function (term_data) {
				var term = fp.fbp.send_tax_terms_titles ? term_data.name : term_data.slug;
				term += fp.fbp.add_tax_term_cat ? ' (' + term_data.taxonomy + ')' : '';
				return term;
			});

			custom_data.terms = term_arr.join(', ');
		};

		// save in a variable for later use
		fp.fbp.custom_data = custom_data;
	}

	function set_user_data(){
		
		// PIXEL
		// !! Fires the "Init" ( with or without Advanced Matching data )
		// https://developers.facebook.com/docs/meta-pixel/advanced/advanced-matching

		
			fbq( 'init', fp.fbp.pixel_id );
		
	}

	function load_pixel(){
		
		if ( ! fp.loading.includes('fbp')) {
			
			fp.loading.push('fbp');

			!function(f,b,e,v,n,t,s)
			{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t,s)}(window, document,'script',
			'https://connect.facebook.net/en_US/fbevents.js');
		}

		// Set data processing options
		// https://developers.facebook.com/docs/meta-pixel/implementation/data-processing-options
		if ( fp.fbp.limit_data_use ) fbq('dataProcessingOptions', ['LDU'], 0, 0);
	}

    function load_fbp() {

		load_pixel();
		set_fbp_fbc();
		set_user_data();
		set_custom_data();

		FP.track_fbp_evt( false, 'PageView' );

		
		
		// Track Search
		if ( fpdata.page_type == 'Search' ) FP.track_fbp_evt( false, 'Search', false, { 'search_string' : fpdata.search_query } );

		fp.loaded.push( 'fbp' );
		FP.runFn( 'FP.fns.load_fbp_footer' );
    }
	
})(window);
