// WOO EVENTS

FP.fns.add_cegg_tag = ( tag ) => {
	window.CE2.addTag( tag );
	if ( fp.main.debug ) console.log( '[FP] CrazyEgg tag: ' + tag );
}

FP.fns.cegg_woo_events = ()=>{
	
	// TRACK IMPRESSIONS

	function track_woo_impress( caller_id ) {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.cegg ) fp.woo.cegg = { 'single' : [] };
		
		let item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.cegg.single.includes(id) ); // track only items that were not tracked before

		if ( item_ids.length > 0 ) FP.fns.add_cegg_tag( 'product view' );

		// prevent double tracking in case the next teasers are added dynamically
		fp.woo.cegg.single.push(...item_ids);
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress( 'cegg' );
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK ADD TO CART / REMOVE FROM CART
	// TRACK ADD TO WISHLIST

	FP.addAction( ['woo_add_to_cart'], () =>{
		FP.fns.add_cegg_tag( 'add to cart' );
	} );

	FP.addAction( ['woo_add_to_wishlist'], data => {
		FP.fns.add_cegg_tag( 'add to wishlist' );
	} );

	FP.addAction( ['woo_remove_from_cart'], data => {
		FP.fns.add_cegg_tag( 'remove from cart' );
	} );

	// TRACK ORDER
	
	if ( fp.woo.order_data_ready ) {
		FP.fns.add_cegg_tag( 'purchase' );
	};
	
	// TRACK CHECKOUT
	// track the start of checkout (except when the whole page or its part is refreshed)
	
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.cegg.woo_checkout_tracked = true;
			FP.fns.add_cegg_tag( 'checkout' );
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.cegg.woo_checkout_tracked ) {
					fp.cegg.woo_checkout_tracked = true;
					FP.fns.add_cegg_tag( 'checkout' );
				}
			})
		};
	}
};

// STANDARD EVENTS

FP.fns.cegg_standard_events = ()=>{

	// TAG OUTBOUND LINKS

	if ( fp.cegg.tag_outbound ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_outbound ) {
				FP.fns.add_cegg_tag( 'Outbound click: ' + fpdata.clicked.link.href );
			}
		})
	}

	// TAG AFFILIATE LINKS

	if ( fp.cegg.tag_affiliate ) {
		FP.addAction( ['click'], function(){
			var trackedAffLink = FP.getTrackedAffiliateLink( fp.cegg.tag_affiliate );
			if ( trackedAffLink ) FP.fns.add_cegg_tag( 'Affiliate click: ' + trackedAffLink );
		})
	}

	// TAG CLICKS ON EMAIL & TEL LINKS

	if ( fp.cegg.tag_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				FP.fns.add_cegg_tag( 'Contact click: ' + fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel );
			}
		});
	}

	// TAG FILE DOWNLOADS

	if ( fp.cegg.tag_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.cegg.tag_file_downl );
			if ( filename ) FP.fns.add_cegg_tag( 'File download: ' + filename );
		})
	}

	// TAG FORM SUBMITS

	if ( fp.cegg.tag_forms ) {
		FP.addAction( ['form_submit'], function(){
			var formName = FP.getSubmittedForm( fp.cegg.tag_forms );
			if ( formName ) FP.fns.add_cegg_tag( 'Form submit: ' + formName );
		})
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.cegg_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			let name = el.dataset['cegg_view'] || 'name not provided';
			FP.fns.add_cegg_tag( 'Element view: ' + name );
		};
		
		FP.intersectionObserver( newly_added_els, fp.cegg.tag_views, 'cegg', send_el_view_evt, true);
	}
	
	if ( fp.cegg.tag_views ) setTimeout( ()=>{
		FP.addAction( ['dom_modified', 'dom_loaded'], FP.fns.cegg_observe_inters );
	}, 250 );

	// TAG CLICKS IN ANCHORS

	if ( fp.cegg.tag_anchor_clicks ){
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_anchor ){
				FP.fns.add_cegg_tag( 'Anchor click: ' + fpdata.clicked.link.href );
			}
		})
	}

	// TAG CLICKS ON SPECIFIC ELEMENTS

	if ( fp.cegg.tag_elems ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.cegg.tag_elems );
			if ( name ) FP.fns.add_cegg_tag( 'Element click: ' + name );
		})
	}
};

FP.fns.load_cegg_footer = function() {
	FP.fns.cegg_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.cegg_woo_events();
}

// INIT FOOTER SCRIPTS
FP.enqueueFn( 'FP.fns.load_cegg_footer' );
