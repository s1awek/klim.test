FP.fns.ga4_set_g_ids = function(nr, type, ID) {
	
	let ga_ids = FP.readCookie('fp_ga_ids');

	if ( ga_ids ) {
		ga_ids = JSON.parse( ga_ids );
	} else {
		ga_ids = {};
	}
	
	ga_ids['ga4' + nr + '_' + type] = ID;
	
	FP.setCookie( 'fp_ga_ids', JSON.stringify(ga_ids) );
}

;(function(window){

	function can_enable_ga4() 	{ return FP.isAllowedToLoad( 'ga4', ['stats'], ['id'], 1, true ); }
	function can_enable_gads() 	{ return FP.isAllowedToLoad( 'gads', ['stats','marketing'], ['id'], false, true ); }

	load_gtag( can_enable_ga4(), can_enable_gads() );

	// Load on event
	document.addEventListener('fp_load_scripts', () => {
		load_gtag( can_enable_ga4(), can_enable_gads() );
	});

	function load_gads(){    
		
		fp.loaded.push( 'gads' );
		if ( fp.main.debug ) console.log('[FP] Google Ads loaded');
	}

	function load_ga4( nr, enable_gads, enh_conv_active ) {

		var _ga = fp[ 'ga4' + nr ],
			params = {};

		if ( enable_gads && fp.gads.id == fp.ga41.id && enh_conv_active ) {
			params.allow_enhanced_conversions = true;
		}

		// CHANGE COOKIE PREFIX
		// optional for the tracking ID #1
		if ( _ga.cookie_prefix && nr == '1' ) params.cookie_prefix = '_fp4' + nr;

		// enable GA's debug mode
		// @ https://support.google.com/analytics/answer/7201382?hl=en
		params.debug_mode = !! fpdata.cookies.ga4_debug;

		

		// SEND CLEAN "PAGE TITLE"
		if ( _ga.clean_page_title && fpdata.page_title && fpdata.page_title.length > 0 ) {
			params.page_title = fpdata.page_title;

			// "SEO TITLE" CUSTOM DIMENSION
			if ( _ga.seo_title ) {
				params.seo_title = document.title;
			}
		}

		// "PAGE LANGUAGE" CUSTOM DIMENSION
		if ( _ga.page_lang ) {
			params[_ga.page_lang] = document.documentElement.lang || 'undefined';
		}

		// "PAGE TYPE" CONTENT GROUPING
		if ( _ga.page_type && fpdata.page_type && fpdata.page_type.length > 1 ) {
			params[_ga.page_type] = fpdata.page_type;
		}

		// "PAGE ID" CUSTOM DIMENSION
		if ( _ga.page_id && fpdata.page_id && fpdata.page_id > 0 ) {
			params[_ga.page_id] = '' + fpdata.page_id;
		}

		// "PAGE NUMBER" CUSTOM DIMENSION
		if ( _ga.page_number && fpdata.page_number > 0 ) {
			params[_ga.page_number] = '' + fpdata.page_number;
		}

		// "AUTHOR DISPLAY NAME" CUSTOM DIMENSION
		if ( _ga.post_author && fpdata.author_name && fpdata.author_name.length > 0 ) {
			params[_ga.post_author] = fpdata.author_name;
		}

		// "AUTHOR ID" CUSTOM DIMENSION
		if ( _ga.author_id && fpdata.author_id > 0 ) {
			params[_ga.author_id] = '' + fpdata.author_id;
		}

		// "PUBLISHED DATE" CUSTOM DIMENSION
		if ( _ga.post_date && fpdata.published && fpdata.published.length > 0 ) {
			params[_ga.post_date] = fpdata.published;
		}

		

		// "SEARCH RESULTS" CUSTOM METRIC
		if ( _ga.search_results_nr && _ga.search_results_nr.length > 0 && fpdata.search_results && fpdata.search_results > 0 ) {
			params[_ga.search_results_nr] = parseInt(fpdata.search_results);
		}

		// "USER ROLE" USER PROPERTY
		if ( _ga.user_role ) {
			if ( fpdata.user.role && fpdata.user.role.length > 0 ) {
				params[_ga.user_role] = fpdata.user.role;
			}
		}

		// "TAXONOMY TERMS" CUSTOM DIMENSION
		if ( _ga.tax_terms && fpdata.terms && fpdata.terms.length > 0 ) {

			var term_arr = fpdata.terms.map( function (term_data) {
				var term = _ga.send_tax_terms_titles ? term_data.name : term_data.slug;
				term += _ga.add_tax_term_cat ? ' (' + term_data.taxonomy + ')' : '';
				return term;
			} );

			params[_ga.tax_terms] = term_arr.join(', ');
		}

		window.gtag( 'config', _ga.id, params );

		

		fp.loaded.push('ga4' + nr);
		if ( fp.main.debug ) console.log('[FP] GA4 #' + nr + ' loaded');
	}

	function load_gtag( enable_ga4, enable_gads ) {

		if ( ! enable_ga4 && ! enable_gads ) return;

		// Fix missing AW-
		if ( 
			fp.gads && 
			fp.gads.id && 
			! fp.gads.id.includes('AW-') && 
			! fp.gads.id.includes('GT-') && 
			! fp.gads.id.includes('G-') 
		) fp.gads.id = 'AW-' + fp.gads.id;

		if ( 
			fp.gads && 
			fp.gads.id2 && 
			! fp.gads.id2.includes('AW-') && 
			! fp.gads.id2.includes('GT-') && 
			! fp.gads.id2.includes('G-')
		) fp.gads.id2 = 'AW-' + fp.gads.id2;

		let script_id = enable_ga4 ? fp.ga41.id : fp.gads.id;

		if ( ! script_id ) return;

		// ! Datalayer is already created in head-js.php

		if ( ! fp.loading.includes('gtg') && ! fp?.gtag?.custom_gateway ) {

			fp.loading.push('gtg');

			FP.getScript(
				'https://www.googletagmanager.com/gtag/js?id=' + script_id,
				() => { enable_tags( enable_ga4, enable_gads ) },
				{'async' : 'async'}
			);
			
		} else {
			enable_tags( enable_ga4, enable_gads );
		};
	}

	function enable_tags( enable_ga4, enable_gads ) {

		if ( ! fp.loaded.includes('gtg') ) {
			window.gtag('js', new Date());
			fp.loaded.push('gtg');
		}

		let can_load_ga42 = false,
			enh_conv_active = false;

		

		if ( enable_ga4 ) {
			load_ga4(1, enable_gads, enh_conv_active );
			
		}

		if ( enable_gads ) {

			if ( ! ( fp.loaded.includes('ga41') && fp.ga41.id == fp.gads.id ) ) {
				if ( enh_conv_active ) {
					window.gtag( 'config', fp.gads.id, { 'allow_enhanced_conversions': true } );
				} else {
					window.gtag( 'config', fp.gads.id );
				}
			}

			load_gads();
		}

		if ( fp.loaded.includes('ga41') || fp.loaded.includes('gads') ) {
			FP.runFn( 'FP.fns.load_gtg_footer' );
		}
	}

})(window);
