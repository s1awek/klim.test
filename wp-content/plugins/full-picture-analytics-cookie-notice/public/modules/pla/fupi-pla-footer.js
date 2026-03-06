function fupi_pla_footer_woo(){

	function track_pla_event( event_name, event_goal_id, payload ){
		if ( typeof plausible !== 'undefined' ) plausible( event_goal_id, { 'props' : payload } );
		if ( fp.main.debug ) console.log('[FP] Plausible "' + event_name + '" goal (id: ' + event_goal_id + '):', { 'props' : payload } );
	}

	// TRACK ADD TO CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name, event_goal_id ){
		
		let products_data = data.products;
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				qty = prod_a[1] || prod.qty || 1,
				item = {
				'product_id' : FP.fns.get_woo_prod_id( prod ),
				'product_name' : FP.fns.get_woo_prod_name( prod ),
				'product_price' : prod.price,
				'product_quantity' : parseInt(qty),
			};
			
			if ( prod.categories && prod.categories.length > 0 ) {
				item['product_category'] = prod.categories[0];
			};

			track_pla_event( event_name, event_goal_id, item );
		} );
	};

	if ( fp.pla.track_woo_addtocart ) {
		FP.addAction( ['woo_add_to_cart'], data =>{
			track_items( data, 'add to cart', fp.pla.track_woo_addtocart );
		} );

		if ( fp.woo.cart_to_track ) track_items( fp.woo.cart_to_track, 'add to cart', fp.pla.track_woo_addtocart ); // when ATC is tracked in cart
	}
	
	if ( fp.pla.track_woo_addtowishlist ) {
		FP.addAction( ['woo_add_to_wishlist'], data => {
			track_items( data, 'add to wishlist', fp.pla.track_woo_addtowishlist );
		} );
	}

	// TRACK CHECKOUT

	function track_checkout(){

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			cart = fpdata.woo.cart;

		if ( fp.pla.track_woo_checkout_items ) {
			for ( const id in cart[items_type] ) {
	
				let prod = cart[items_type][id],
					item = {
						'product_id' : FP.fns.get_woo_prod_id( prod ),
						'product_name' : FP.fns.get_woo_prod_name( prod ),
						'product_price' : prod.price,
						'product_quantity' : prod.qty,
					};

				if ( prod.categories && prod.categories.length > 0 ) {
					item['product_category'] = prod.categories[0];
				};
				
				track_pla_event( 'items in checkout', fp.pla.track_woo_checkout_items, item );
			}
		}
		
		let payload_o = {
			'checkout_value' : cart.value,
		};

		track_pla_event( 'checkout', fp.pla.track_woo_checkouts, payload_o );
	}

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( fp.pla.track_woo_checkouts && ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.pla.woo_checkout_tracked = true;
			track_checkout();
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.pla.woo_checkout_tracked ) {
					fp.pla.woo_checkout_tracked = true;
					track_checkout();
				}
			})
		};
	}

	// TRACK PURCHASE

	function track_purchase(){ // type can be either "checkout" or "order"

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			order = fpdata.woo.order;

		if ( fp.pla.track_woo_purchased_items ) {
			for ( const id in order[items_type] ) {
	
				let prod = order[items_type][id],
					item = {
						'product_id' : FP.fns.get_woo_prod_id( prod ),
						'product_name' : FP.fns.get_woo_prod_name( prod ),
						'product_price' : prod.price,
						'product_quantity' : prod.qty,
					};
	
				if ( prod.categories && prod.categories.length > 0 ) {
					item['product_category'] = prod.categories[0];
				};
				
				track_pla_event( 'items in checkout', fp.pla.track_woo_purchased_items, item );
			}
		}

		let payload_o = {
			'props' : {
				'order_id' : order.id,
				'order_value' : order.value,
			},
			'revenue' : {
				'amount' : order.value, 
				'currency' : fpdata.woo.currency 
			}
		};

		if ( fp.woo.incl_tax_in_price && order.tax && order.tax > 0 ) payload_o['order_tax'] = order.tax;
		if ( fp.woo.incl_shipping_in_total && order.shipping && order.shipping > 0 ) payload_o['order_shipping'] = order.shipping;
		if ( order.coupons.length > 0 ) payload_o['order_used_coupon'] = order.coupons[0];
		
		if ( typeof plausible !== 'undefined' ) plausible( fp.pla.track_woo_purchases, payload_o );
		if ( fp.main.debug ) console.log('[FP] Plausible "checkout" goal (id: ' + fp.pla.track_woo_purchases + '):', payload_o );
	}

	if ( fp.pla.track_woo_purchases && fp.woo.order_data_ready ) track_purchase();

	FP.loaded('pla_footer_woo');
}

function fupi_pla_footer(){

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.pla_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {

			if ( ! el.dataset.pla_view ) return;

			let name = el.dataset.pla_view;
			
			if ( fp.pla.track_visib_goalname ) {
				if ( typeof plausible != 'undefined' ) plausible( fp.pla.track_visib_goalname, { 'props' : { 'name' : name } } );
				if ( fp.main.debug ) console.log( '[FP] Plausible goal "' + fp.pla.track_visib_goalname + '" with a property "name: ' + name + '"' );
			} else {
				if ( typeof plausible != 'undefined' ) plausible( name );
				if ( fp.main.debug ) console.log( '[FP] Plausible goal "' + name + '"' );
			}
		};
		
		FP.intersectionObserver( newly_added_els, fp.pla.track_views, 'pla', send_el_view_evt, true);
	}
	
	if ( fp.pla.track_views ) setTimeout( ()=>{
		FP.fns.pla_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.pla_observe_inters );
	}, 250 );

	
	// AFFILIATE TRACKING

	if ( fp.pla.track_affiliate_2 ) {
		FP.addAction( ['click'], function(){
			var name = FP.getTrackedAffiliateLink( fp.pla.track_affiliate_2 );
			if ( name ) {
				if ( fp.pla.track_affiliate_goalname ) {
					if ( typeof plausible != 'undefined' ) plausible( fp.pla.track_affiliate_goalname, { 'props': { 'link' : name } } );
					if ( fp.main.debug ) console.log( '[FP] Plausible goal "' + fp.pla.track_affiliate_goalname + '" with a property "link: ' + name + '"' );
				} else {
					if ( typeof plausible != 'undefined' ) plausible( name );
					if ( fp.main.debug ) console.log( '[FP] Plausible goal "' + name + '"' );
				}
			}
		})
	}

	// TRACK CLICKS ON EMAIL & TEL LINKS

	if ( fp.pla.track_contact_links ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				var props = {
					'target' :  fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel,
					'type' : fpdata.clicked.link.is_email ? 'email' : 'tel',
				};
				if ( typeof plausible !== 'undefined' ) plausible( fp.pla.track_contact_links, {'props': props} );
				if ( fp.main.debug ) console.log( '[FP] Plausible goal: "' + fp.pla.track_contact_links + '" with properties ', props );
			}
		});
	}

	// TRACK FILE DOWNLOADS

	if ( fp.pla.track_file_downl_goalname && fp.pla.track_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.pla.track_file_downl );
			if ( filename ) {
				if ( typeof plausible !== 'undefined' ) plausible( fp.pla.track_file_downl_goalname, { 'props': { 'file' : filename } } );
				if ( fp.main.debug ) console.log( '[FP] Plausible goal: "' + fp.pla.track_file_downl_goalname + '" with a property "file: ' + filename + '"' );
			}
		})
	}

	// FORM SUBMISSIONS

	if ( fp.pla.track_forms_2 ) {
		FP.addAction( ['form_submit'], function(){
			var name = FP.getSubmittedForm( fp.pla.track_forms_2 );
			if ( name ){
				if ( fp.pla.track_forms_goalname ) {
					if ( typeof plausible != 'undefined' ) plausible( fp.pla.track_forms_goalname, { 'props': { 'form' : name } } );
					if ( fp.main.debug ) console.log( '[FP] Plausible goal: "' + fp.pla.track_forms_goalname + '" with a property "form: ' + name + '"' );
				} else {
					if ( typeof plausible != 'undefined' ) plausible( name );
					if ( fp.main.debug ) console.log( '[FP] Plausible goal: "' + name + '"' );
				}
			}
		})
	}

	// PAGE ELEMENT TRACKING

	if ( fp.pla.track_elems_2 ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.pla.track_elems_2 );
			if ( name ) {
				if ( fp.pla.track_elems_goalname ) {
					if ( typeof plausible != 'undefined' ) plausible( fp.pla.track_elems_goalname, { 'props': { 'page element' : name } } );
					if ( fp.main.debug ) console.log( '[FP] Plausible goal: "' + fp.pla.track_elems_goalname + '" with a property "page element: ' + name + '"' );
				} else {
					if ( typeof plausible != 'undefined' ) plausible( name );
					if ( fp.main.debug ) console.log( '[FP] Plausible goal: "' + name + '"');
				}
			}
		})
	}

	FP.loaded('pla_footer');
}

FP.load('pla_footer', 'fupi_pla_footer', ['pla','footer_helpers']);
FP.load('pla_footer_woo', 'fupi_pla_footer_woo', ['pla','footer_helpers', 'woo']);
