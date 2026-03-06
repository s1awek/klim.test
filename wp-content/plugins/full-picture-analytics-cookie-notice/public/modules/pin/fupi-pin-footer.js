function fupi_pin_footer_woo() {

    function add_brand( item, prod ){
		if ( prod.brand && prod.brand.length > 0 ) item['product_brand'] = prod.brand[0];
		return item;
	};

    function add_first_category( item, prod ){
		if ( prod.categories && prod.categories.length > 0 ) {
			item['product_category'] = prod.categories[0];
		};

		return item;
	};

    function track_pin_event( event_name, payload ){
        pintrk('track', event_name, payload );
        if ( fp.main.debug ) console.log('[FP] Pinterest "' + event_name + '" event: ', payload);
    }

    // TRACK IMPRESSIONS

	function track_woo_impress() {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.pin ) fp.woo.pin = { 'single' : [] };
		
		let items_a = [],
			item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.pin.single.includes(id) ); // track only items that were not tracked before

        if ( item_ids.length == 0 ) return;

		item_ids.forEach( id => {
			
			let prod = fpdata.woo.products[id],
				item = { 
				'product_id' : FP.fns.get_woo_prod_id(prod),
                'product_name' : FP.fns.get_woo_prod_name(prod),
                'product_price' : prod.price,
			};

            item = add_brand(item, prod);
            item = add_first_category(item, prod);

			items_a.push( item );
		});

		// prevent double tracking in case the next prods are added dynamically
		fp.woo.pin.single.push(...item_ids);

        let payload_o = { 
            'line_items' : items_a, 
            'currency' : fpdata.woo.currency, 
        };

        track_pin_event( 'pagevisit', payload_o );
		
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	};
	
	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		let prod = fpdata.woo.products[variant_id],
			item = {
				'product_id': FP.fns.get_woo_prod_id(prod),
				'product_name': FP.fns.get_woo_prod_name(prod),
				'product_price': prod.price,
			};

		item = add_brand(item, prod);
		item = add_first_category(item, prod);

		let payload_o = {
			'line_items': [item],
			'currency': fpdata.woo.currency,
		};

		track_pin_event('pagevisit', payload_o);
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK ADD TO CART

	function track_items( data ){
		
		let products_data = data.products,
			items_a = [];
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				qty = prod_a[1] || prod.qty || 1,
				item = { 
                    'product_id' : FP.fns.get_woo_prod_id(prod),
                    'product_name' : FP.fns.get_woo_prod_name(prod),
                    'product_price' : prod.price,
                    'product_quantity' : qty,
                };
    
            item = add_brand(item, prod);
            item = add_first_category(item, prod);

			items_a.push( item );
		} );

		let payload_o = {
			'line_items' : items_a, 
			'value' : data.value, 
			'currency' : fpdata.woo.currency,
		};

		track_pin_event( 'addtocart', payload_o );
	};

	FP.addAction( ['woo_add_to_cart'], data =>{
		track_items( data );
	} );

	if ( fp.woo.cart_to_track ) track_items( fp.woo.cart_to_track ); // when ATC is tracked in cart

	// TRACK ORDER 
    // ! this event is labeled as "checkout" !

	function track_purchase(){

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			order = fpdata.woo.order;

		for ( const id in order[items_type] ) {

			let prod = order[items_type][id],
                item = { 
                    'product_id' : FP.fns.get_woo_prod_id(prod),
                    'product_name' : FP.fns.get_woo_prod_name(prod),
                    'product_quantity' : prod.qty,
                    'product_price' : prod.price,
                };

            item = add_brand(item, prod);
            item = add_first_category(item, prod);

			items_a.push( item );
		}

		if ( items_a.length == 0 ) return false;

		let payload_o = {
            'order_id' : order.id,
            'order_quantity' : order.qty,
			'currency' : fpdata.woo.currency,
			'value' : order.value,
            'promo_code' : order.coupons.join(','),
			'line_items' : items_a,
            'user_data' : {
                'em' : fpdata.user.email
            }
		};
		
		track_pin_event( 'checkout', payload_o );
	}

	// track order
	if ( fp.woo.order_data_ready ) track_purchase();

	FP.loaded('pin_footer_woo');
}

// INIT FOOTER SCRIPTS
FP.load('pin_footer_woo', 'fupi_pin_footer_woo', ['pin', 'footer_helpers']);