;(function(window){
	
	if ( allow_loading_linkd() ) { 
		load_linkd();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_linkd() ) load_linkd(); } );
	};

	// FUNCTIONS

	function allow_loading_linkd(){
		return FP.isAllowedToLoad( 'linkd', ['stats','marketing'], ['id'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function linkd_woo_events(){

		// TRACK ADD TO CART

		if ( fp.linkd.woo_add_to_cart_id ) {
			FP.addAction( ['woo_add_to_cart'], () =>{
				window.lintrk( 'track', { 'conversion_id': fp.linkd.woo_add_to_cart_id } );
				if ( fp.vars.debug ) console.log( '[FP] LinkedIn "add to cart" event. ID:', fp.linkd.woo_add_to_cart_id );
			} );
		}

		// TRACK ORDER
		if ( fp.linkd.woo_purchase_id && fp.woo.order_data_ready ) {
			window.lintrk( 'track', { 'conversion_id': fp.linkd.woo_purchase_id } );
			if ( fp.vars.debug ) console.log( '[FP] LinkedIn "purchase" event. ID:', fp.linkd.woo_purchase_id);
		}
		
		// TRACK CHECKOUT
		// (except when the whole page or its part is refreshed)
		if ( ! fpdata.refreshed ) {
			if ( fp.woo.checkout_data_ready ) {
				fp.linkd.woo_checkout_tracked = true;
				window.lintrk( 'track', { 'conversion_id': fp.linkd.woo_checkout_start_id } );
				if ( fp.vars.debug ) console.log( '[FP] LinkedIn "checkout" event. ID:', fp.linkd.woo_checkout_start_id);
			} else {
				document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
					if ( ! fp.linkd.woo_checkout_tracked ) {
						fp.linkd.woo_checkout_tracked = true;
						window.lintrk( 'track', { 'conversion_id': fp.linkd.woo_checkout_start_id } )
						if ( fp.vars.debug ) console.log( '[FP] LinkedIn "checkout" event. ID:', fp.linkd.woo_checkout_start_id);
					}
				})
			};
		}
	}

	function linkd_standard_events(){

		// TRACK CLICKS ON PAGE ELEMENTS

		if ( fp.linkd.track_elems ) {
			FP.addAction( ['click'], function(){
				var conv_id  = FP.getClickTarget( fp.linkd.track_elems );
				if ( conv_id ) {
					window.lintrk('track', { conversion_id: conv_id });
					if ( fp.vars.debug ) console.log('[FP] LinkedIn "element click" conversion. ID:', conv_id);
				}
			})
		}

		// TRACK EMAIL LINKS

		if ( fp.linkd.track_email ) {
			FP.addAction( ['click'], function(){
				if ( fpdata.clicked.link && fpdata.clicked.link.is_email ) {
					window.lintrk('track', { conversion_id: fp.linkd.track_email });
					if ( fp.vars.debug ) console.log('[FP] LinkedIn "email link click" conversion. ID:', fp.linkd.track_email );
				}
			} );
		}

		// TRACK TEL LINKS

		if ( fp.linkd.track_tel ) {
			FP.addAction( ['click'], function(){
				if ( fpdata.clicked.link && fpdata.clicked.link.is_tel ) {
					window.lintrk('track', { conversion_id: fp.linkd.track_tel });
					if ( fp.vars.debug ) console.log('[FP] LinkedIn "tel link click" conversion. ID:', fp.linkd.track_tel );
				}
			} );
		}

		// TRACK VIEWS OF ELEMENTS
		// for performance: waits 250ms for dynamically generated content to finish

		FP.fns.linkd_observe_inters = ( newly_added_els = false ) => {

			let send_el_view_evt = el => {
				
				if ( ! el.dataset.linkd_view ) return;

				window.lintrk('track', { conversion_id: el.dataset.linkd_view });
				if ( fp.vars.debug ) console.log('[FP] LinkedIn "element visibility" conversion. ID:', el.dataset.linkd_view );
			};
			
			FP.intersectionObserver( newly_added_els, fp.linkd.track_views, 'linkd', send_el_view_evt, true);
		}
		
		if ( fp.linkd.track_views ) setTimeout( ()=>{
			FP.fns.linkd_observe_inters();
			FP.addAction( ['dom_modified'], FP.fns.linkd_observe_inters );
		}, 250 );

		// TRACK VIEWS OF ELEMENTS

		var send_el_view_evt = function (el) {
			window.lintrk('track', { conversion_id: el.dataset.fp_linkd_convid });
			if ( fp.vars.debug ) console.log('[FP] LinkedIn "element visibility" conversion. ID:', el.dataset.fp_linkd_convid );
		};

		// TRACK FORM SUBMITS

		if ( fp.linkd.track_forms ) {
			FP.addAction( ['form_submit'], function(){
				var conv_id = FP.getSubmittedForm( fp.linkd.track_forms );
				if ( conv_id ){
					window.lintrk('track', { conversion_id: conv_id });
					if ( fp.vars.debug ) console.log('[FP] LinkedIn "form submit" conversion. ID:', conv_id);
				}
			})
		}

		// TRACK AFFILIATE LINKS

		if ( fp.linkd.track_affiliate ) {
			FP.addAction( ['click'], function(){
				var conv_id = FP.getTrackedAffiliateLink( fp.linkd.track_affiliate );
				if ( conv_id ) {
					window.lintrk('track', { conversion_id: conv_id });
					if ( fp.vars.debug ) console.log('[FP] LinkedIn "affiliate link click" conversion. ID:', conv_id);
				}
			} );
		};
	}

	function after_linkd_loads(){
		fp.loaded.push('linkd');
		linkd_standard_events();
		if ( fp.loaded.includes('woo') ) linkd_woo_events();
	}

	function load_linkd() {
		
		_linkedin_partner_id = fp.linkd.id;
		window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
		window._linkedin_data_partner_ids.push(_linkedin_partner_id);

		window.lintrk = function(a,b){window.lintrk.q.push([a,b])};
		window.lintrk.q=[];

		FP.getScript("https://snap.licdn.com/li.lms-analytics/insight.min.js", after_linkd_loads, {'async' : 'async'})
		
	}

})(window);
