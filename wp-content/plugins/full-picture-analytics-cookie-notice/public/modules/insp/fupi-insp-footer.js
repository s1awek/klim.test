function fupi_insp_footer_woo(){

	function track_woo_impress( caller_id ) {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.insp ) fp.woo.insp = { 'single' : [] };
		
		let item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.insp.single.includes(id) ); // check if there are any prods that were not tracked before

		if ( item_ids.length > 0 ) {
			__insp.push( [ 'tagSession', 'product view' ] );
			if ( fp.main.debug ) console.log( '[FP] Inspectlet "product view" tag');
		}

		// prevent double tracking in case the next teasers are added dynamically
		fp.woo.insp.single.push(...item_ids);
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress( 'insp' );
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	FP.addAction( ['woo_add_to_cart'], () =>{
		__insp.push( [ 'tagSession', 'add to cart' ] );
		if ( fp.main.debug ) console.log( '[FP] Inspectlet "add to cart" tag');
	} );
		
	if ( fp.woo.cart_to_track ) {
		__insp.push( [ 'tagSession', 'add to cart' ] );
		if ( fp.main.debug ) console.log( '[FP] Inspectlet "add to cart" tag');
	}

	FP.addAction( ['woo_add_to_wishlist'], () => {
		__insp.push( [ 'tagSession', 'add to wishlist' ] );
		if ( fp.main.debug ) console.log( '[FP] Inspectlet "add to wishlist" tag');
	} );

	FP.addAction( ['woo_remove_from_cart'], () => {
		__insp.push( [ 'tagSession', 'remove from cart' ] );
		if ( fp.main.debug ) console.log( '[FP] Inspectlet "remove from cart" tag');
	} );

	// track order
	if ( fp.woo.order_data_ready ) {
		__insp.push( [ 'tagSession', 'purchase' ] );
		if ( fp.main.debug ) console.log( '[FP] Inspectlet "purchase" tag');
	}

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.insp.woo_checkout_tracked = true;
			__insp.push( [ 'tagSession', 'checkout' ] );
			if ( fp.main.debug ) console.log( '[FP] Inspectlet "checkout" tag');
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.insp.woo_checkout_tracked ) {
					fp.insp.woo_checkout_tracked = true;
					__insp.push( [ 'tagSession', 'checkout' ] );
					if ( fp.main.debug ) console.log( '[FP] Inspectlet "checkout" tag');
				}
			})
		};
	}

	FP.loaded('insp_footer_woo');
}

function fupi_insp_footer() {

	// TAG OUTBOUND LINKS

	if ( fp.insp.tag_outbound ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_outbound ) {
				__insp.push( [ 'tagSession', { 'outbound link click' : fpdata.clicked.link.href } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: outbound click', fpdata.clicked.link.href );
			}
		})
	}

	// TAG AFFILIATE LINKS

	if ( fp.insp.tag_affiliate ) {
		FP.addAction( ['click'], function(){
			var name = FP.getTrackedAffiliateLink( fp.insp.tag_affiliate );
			if ( name ) {
				__insp.push( [ 'tagSession', { 'affiliate link click' :  name } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: affiliate click', name );
			}
		})
	}

	// TAG CLICKS ON EMAIL & TEL LINKS

	if ( fp.insp.tag_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				__insp.push( [ 'tagSession', { 'contact link click' : fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: contact click', fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel );
			}
		});
	}

	// TAG FILE DOWNLOADS

	if ( fp.insp.tag_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.insp.tag_file_downl );
			if ( filename ) {
				__insp.push( [ 'tagSession', { 'file download' : filename } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: download', filename );
			}
		})
	}

	// TAG FORM SUBMITS

	if ( fp.insp.tag_forms ) {
		FP.addAction( ['form_submit'], function(){
			var name = FP.getSubmittedForm( fp.insp.tag_forms );
			if ( name ){
				__insp.push( [ 'tagSession', { 'form submit' : name } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: form submit', name );
			}
		})
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.insp_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			let name = el.dataset['insp_view'] || 'name not provided';
			__insp.push( [ 'tagSession', 'element view: ' + name ] );
			if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: element view', name );
		};
		
		FP.intersectionObserver( newly_added_els, fp.insp.tag_views, 'insp', send_el_view_evt, true);
	}
	
	if ( fp.insp.tag_views ) setTimeout( ()=>{
		FP.fns.insp_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.insp_observe_inters );
	}, 250 );

	// TAG CLICKS IN ANCHORS

	if ( fp.insp.tag_anchor_clicks ){
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_anchor ){
				__insp.push( [ 'tagSession', { 'anchor click' : fpdata.clicked.link.element.textContent } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: anchor click', fpdata.clicked.link.element.textContent );
			}
		})
	}

	// TAG SPECIFIC ELEMENTS

	if ( fp.insp.tag_elems ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.insp.tag_elems );
			if ( name ) {
				__insp.push( [ 'tagSession', { 'page element click' : name } ] );
				if ( fp.main.debug ) console.log( '[FP] Inspectlet tag: element click', name );
			}
		})
	}

	FP.loaded('insp_footer');
}

FP.load('insp_footer','fupi_insp_footer', ['footer_helpers', 'insp']);
FP.load('insp_footer_woo','fupi_insp_footer_woo', ['insp', 'footer_helpers', 'woo']);