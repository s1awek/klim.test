FP.fns.hotj_woo_events = () => {

	// TRACK ADD TO CART / REMOVE FROM CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name, action ){
		
		hj( 'event', event_name );
		if ( fp.vars.debug ) console.log( '[FP] Hotjar "' + event_name + '" event' );

		let track_params = fp.hotj[ 'tag_woo_' + action + '_data' ];
		
		if( track_params && track_params.length > 0 ) data.products.forEach( prod_a => {

			let prod = prod_a[0];

			if ( track_params.includes('p_id') ) hj( 'event', event_name + ' prod id: ' + FP.fns.get_woo_prod_id( prod ) );
			if ( track_params.includes('p_name') ) hj( 'event', event_name + ' prod: ' + FP.fns.get_woo_prod_name( prod ) );
		} );
		
	}

	if ( fp.hotj['tag_woo_addtocart'] ){
		FP.addAction( ['woo_add_to_cart'], data => {
			track_items( data, 'woo add to cart', 'addtocart' );
		});
	}

	if ( fp.hotj['tag_woo_removefromcart'] ){
		FP.addAction( ['woo_remove_from_cart'], data => {
			track_items( data, 'woo remove from cart', 'removefromcart' );
		} );
	}

	if ( fp.hotj['tag_woo_addtowishlist'] ){
		FP.addAction( ['woo_add_to_wishlist'], data => {
			track_items( data, 'woo add to wishlist', 'addtowishlist' );
		} );
	}

	// TRACK CHECKOUT
	// TRACK ORDER

	function track_cart( type ){ // type can be either "checkout" or "order"
		
		let track_params = type == 'checkout' ? fp.hotj[ 'tag_woo_checkouts_data' ] : fp.hotj[ 'tag_woo_purchases_data' ],
			event_name = 'woo ' + type;
		
		hj( 'event', event_name );
		if ( fp.vars.debug ) console.log( '[FP] Hotjar "' + event_name + '" event');

		if ( ! track_params ) return;
		
		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			cart = type == 'checkout' ? fpdata.woo.cart : fpdata.woo.order;

		if ( track_params && track_params.length > 0 ) {
			for ( const id in cart[items_type] ) {
				let prod = cart[items_type][id];

				if ( track_params.includes('p_id') ) hj( 'event', event_name + ' prod id: ' + FP.fns.get_woo_prod_id( prod ) );
				if ( track_params.includes('p_name') ) hj( 'event', event_name + ' prod: ' + FP.fns.get_woo_prod_name( prod ) );
			}
		}

		if ( type == 'order' && track_params.includes('id') ) {
			// track order IDs if hotjar is NOT in privacy mode (this means that it is loaded after required consents)
			if ( ! fp.hotj.no_pii ) hj( 'event', 'order ' + fpdata.woo.order.id )
		};
	}

	// track order
	if ( fp.woo.order_data_ready && fp.hotj.tag_woo_purchases ) track_cart('order');

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.hotj.tag_woo_checkouts ) {
			if ( fp.woo.checkout_data_ready ) {
				fp.hotj.woo_checkout_tracked = true;
				track_cart('checkout')
			} else {
				document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
					if ( ! fp.hotj.woo_checkout_tracked ) {
						fp.hotj.woo_checkout_tracked = true;
						track_cart('checkout');
					}
				})
			};
		}
	}
};

FP.fns.hotj_standard_events = function() {

	// TAG OUTBOUND LINKS

	if ( fp.hotj.tag_outbound ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_outbound ) {
				hj( 'event', 'outbound link click' );
				hj( 'event', 'outbound: ' + fpdata.clicked.link.href );
				if ( fp.vars.debug ) console.log( '[FP] Hotjar "outbound click" event:', fpdata.clicked.link.href );
			}
		})
	}

	// TAG AFFILIATE LINKS

	if ( fp.hotj.tag_affiliate ) {
		FP.addAction( ['click'], function(){
			var name = FP.getTrackedAffiliateLink( fp.hotj.tag_affiliate );
			if ( name ) {
				hj( 'event', 'affiliate link click' );
				hj( 'event', 'affiliate: ' + name );
				if ( fp.vars.debug ) console.log( '[FP] Hotjar "affiliate click" event', name );
			}
		})
	}

	// TAG CLICKS ON EMAIL & TEL LINKS

	if ( fp.hotj.tag_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				hj( 'event', 'contact link click' );
				if ( fpdata.clicked.link.safe_email ) hj( 'event', 'mail to ' + fpdata.clicked.link.safe_email );
				if ( fpdata.clicked.link.safe_tel ) hj( 'event', 'tel to ...' + fpdata.clicked.link.safe_tel );
				if ( fp.vars.debug ) console.log( '[FP] Hotjar "contact click" event' );
			}
		});
	}

	// TAG FILE DOWNLOADS

	if ( fp.hotj.tag_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.hotj.tag_file_downl );
			if ( filename ) {
				hj( 'event', 'file download' );
				hj( 'event', 'download: ' +  filename );
				if ( fp.vars.debug ) console.log('[FP] Hotjar "download" event:', filename);
			}
		})
	}

	// TAG FORM SUBMITS

	if ( fp.hotj.tag_forms ) {
		FP.addAction( ['form_submit'], function(){
			var name = FP.getSubmittedForm( fp.hotj.tag_forms );
			if ( name ){
				hj( 'event', 'form submit' );
				hj( 'event', 'form submit: ' + name );
				if ( fp.vars.debug ) console.log('[FP] Hotjar "form submit" event:', name);
			}
		})
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.hotj_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			let name = el.dataset['hotj_view'] || 'name not provided';
			hj( 'event', 'element view' );
			hj( 'event', name + ' viewed' );
			if ( fp.vars.debug ) console.log('[FP] Hotjar "element view" event:', name);
		};
		
		FP.intersectionObserver( newly_added_els, fp.hotj.tag_views, 'hotj', send_el_view_evt, true);
	}
	
	if ( fp.hotj.tag_views ) setTimeout( ()=>{
		FP.fns.hotj_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.hotj_observe_inters );
	}, 250 ); // wait for any dynamically generated content

	// TAG CLICKS IN ANCHORS

	if ( fp.hotj.tag_anchor_clicks ){
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_anchor ){
				hj( 'event', 'anchor link click' );
				hj( 'event', 'anchor click:' + fpdata.clicked.link.href );
				if ( fp.vars.debug ) console.log('[FP] Hotjar "anchor click" event:', fpdata.clicked.link.href );
			}
		})
	}

	// TAG SPECIFIC ELEMENTS

	if ( fp.hotj.tag_elems ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.hotj.tag_elems );
			if ( name ) {
				hj( 'event', 'page element click: ' + name );
				if ( fp.vars.debug ) console.log('[FP] Hotjar "element click" event:', name);
			}
		})
	}
}

FP.fns.load_hotj_footer = () => {
	FP.fns.hotj_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.hotj_woo_events();
};

FP.enqueueFn( 'FP.fns.load_hotj_footer' );