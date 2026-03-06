function fupi_clar_woo_footer(){

	// TRACK IMPRESSIONS

	function track_woo_impress( caller_id ) {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.clar ) fp.woo.clar = { 'single' : [] };
		
		let item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.clar.single.includes(id) ), // track only items that were not tracked before
			names_a = [],
			ids_a = [];

		if ( item_ids.length == 0 ) return;

		item_ids.forEach( id => {
			
			let prod = fpdata.woo.products[id];
				
			ids_a.push( FP.fns.get_woo_prod_id(prod) );
			names_a.push( FP.fns.get_woo_prod_name(prod) );
			
		} );

		clarity( 'set', 'product view', 'product view' );

		clarity( 'set', 'product view - item id', ids_a );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: product view - item id', ids_a );

		clarity( 'set', 'product view - item name', names_a );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: product view - item name', names_a );
		
		// prevent double tracking in case the next teasers are added dynamically
		fp.woo.clar.single.push(...item_ids);
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress( 'clar' );
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK ADD TO CART / REMOVE FROM CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name ){

		// send general event
		clarity( 'set', event_name, event_name );
		
		let products_data = data.products,
			names_a = [],
			ids_a = [];
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				prod_name = FP.fns.get_woo_prod_name( prod ),
				prod_id = FP.fns.get_woo_prod_id( prod );
			
			if ( ! ids_a.includes( prod_id ) ) ids_a.push( prod_id );
			if ( ! names_a.includes( prod_name ) ) names_a.push( prod_name );
		} );

		clarity( 'set', event_name + ' - item id', ids_a );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: ' + event_name + ' - item id', ids_a );

		clarity( 'set', event_name + ' - item name', names_a );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: ' + event_name + ' - item name', names_a );
	};

	FP.addAction( ['woo_add_to_cart'], data =>{
		track_items( data, 'add to cart' );
	} );

	if ( fp.woo.cart_to_track ) track_items( fp.woo.cart_to_track, 'add to cart' ); // when ATC is tracked in cart

	FP.addAction( ['woo_add_to_wishlist'], data => {
		track_items( data, 'add to wishlist');
	} );

	FP.addAction( ['woo_remove_from_cart'], data => {
		track_items( data, 'remove from cart' );
	} );

	// TRACK CHECKOUT
	// TRACK ORDER

	function track_cart( type ){ // type can be either "checkout" or "order"

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			event_name = type == 'checkout' ? 'checkout' : 'purchase',
			cart = type == 'checkout' ? fpdata.woo.cart : fpdata.woo.order,
			ids_a = [],
			names_a = [];

		for ( const id in cart[items_type] ) {

			let prod = cart[items_type][id];
			
			ids_a.push( FP.fns.get_woo_prod_id(prod) );
			names_a.push( FP.fns.get_woo_prod_name(prod) );
		}

		if ( ids_a.length == 0 ) return false;

		clarity( 'set', event_name, event_name );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: ' + event_name );

		clarity( 'set', event_name + ' - item id', ids_a );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: ' + event_name + ' - item id', ids_a );

		clarity( 'set', event_name + ' - item name', names_a );
		if ( fp.main.debug ) console.log('[FP] MS Clarity tag: ' + event_name + ' - item name', names_a );
	}

	// track order
	if ( fp.woo.order_data_ready ) track_cart('order');

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.clar.woo_checkout_tracked = true;
			track_cart('checkout')
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.clar.woo_checkout_tracked ) {
					fp.clar.woo_checkout_tracked = true;
					track_cart('checkout');
				}
			})
		};
	}

	FP.loaded('clar_woo_footer');
};

function fupi_clar_footer(){

	// TAG OUTBOUND LINKS

	if ( fp.clar.tag_outbound ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_outbound ) {
				clarity( 'set', 'Outbound link click', fpdata.clicked.link.href );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: outbound click', fpdata.clicked.link.href);
			}
		})
	}

	// TAG AFFILIATE LINKS

	if ( fp.clar.tag_affiliate ) {
		FP.addAction( ['click'], function(){
			var trackedAffLink = FP.getTrackedAffiliateLink( fp.clar.tag_affiliate );
			if ( trackedAffLink ) {
				clarity( 'set', 'Affiliate link click', trackedAffLink );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: affiliate click', trackedAffLink);
			}
		})
	}

	// TAG CLICKS ON EMAIL & TEL LINKS

	if ( fp.clar.tag_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				clarity( 'set', 'Contact link click', fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: contact click', fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel );
			}
		});
	}

	// TAG FILE DOWNLOADS

	if ( fp.clar.tag_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.clar.tag_file_downl );
			if ( filename ) {
				clarity( 'set', 'File download', filename );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: download', filename );
			}
		})
	}

	// TAG FORM SUBMITS

	if ( fp.clar.tag_forms ) {
		FP.addAction( ['form_submit'], function(){
			var formName = FP.getSubmittedForm( fp.clar.tag_forms );
			if ( formName ){
				clarity( 'set', 'Form submit', formName );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: form submit', formName);
			}
		})
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.clar_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			let name = el.dataset['clar_view'] || 'name not provided';
			clarity( 'set', 'Element view', name );
			if ( fp.main.debug ) console.log('[FP] MS Clarity tag: element view', name);
		};
		
		FP.intersectionObserver( newly_added_els, fp.clar.tag_views, 'clar', send_el_view_evt, true );
	}
	
	if ( fp.clar.tag_views ) setTimeout( ()=>{
		FP.fns.clar_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.clar_observe_inters );
	}, 250 );

	// TAG CLICKS IN ANCHORS

	if ( fp.clar.tag_anchor_clicks ){
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_anchor ){
				clarity( 'set', 'Anchor link click', fpdata.clicked.link.href );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: anchor click', fpdata.clicked.link.href);
			}
		})
	}

	// TAG SPECIFIC ELEMENTS

	if ( fp.clar.tag_elems ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.clar.tag_elems );
			if ( name ) {
				clarity( 'set', 'Page element click', name );
				if ( fp.main.debug ) console.log('[FP] MS Clarity tag: element click', name);
			}
		})
	}

	FP.loaded('clar_footer');
};

FP.load('clar_footer', 'fupi_clar_footer', ['clar', 'footer_helpers']);
FP.load('clar_woo_footer', 'fupi_clar_woo_footer', ['clar', 'footer_helpers', 'woo']);