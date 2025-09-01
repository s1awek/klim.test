;(function(window){

	if ( ! fp.mato ) return;

	if ( allow_loading_mato() ) { 
		load_mato();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_mato() ) load_mato( true ); } );
	};

	document.addEventListener('fupi_consents_changed', function (e) {
		if ( fp.mato.no_cookies && fpdata.cookies.stats ) {
			if ( fp.main.debug ) console.log('[FP] Matomo - cookie permissions have been updated. Matomo is now running in cookie mode');
			_paq.push(['setCookieConsentGiven']);
		}
	});

	// FUNCTIONS

	function allow_loading_mato(){
		return FP.isAllowedToLoad( 'mato', ['stats'], ['url', 'id'], false, fp.mato && fp.mato.no_cookies ); // module id, req permission, required data ids, integration number, if has no cookie mode
	}

	function load_mato( consent_given ) {

		if ( consent_given ) fp.mato.user_consent_granted = true;

		// LOAD MATOMO

		var _paq = window._paq = window._paq || [];
		// tracker methods like "setCustomDimension" should be called before "trackPageView"

		// if we can enable privacy mode
		// Documentation here > https://developer.matomo.org/guides/tracking-consent
		if ( fp.mato.no_cookies ) {

			// and the visitor made no cookie choices
			if ( ! fpdata.cookies ) {
				// and the cookies notice will show up here
				if ( fp.notice.enabled && ( fp.notice.mode == 'optin' || fp.notice.mode == 'optout' ) ) {
					// enable privacy mode
					if ( fp.main.debug ) console.log('[FP] Matomo - consent mode enabled');
					_paq.push(['requireCookieConsent']); // << this means that tracking will be done without cookies
				}
			// if the visitor made some cookies choices
			} else {
				// but didn\'t agree to stats
				if ( ! fpdata.cookies.stats ) {
					if ( fp.main.debug ) console.log('[FP] Matomo - consent mode enabled');
					// disable cookie permission in case it was given earlier
					_paq.push(['forgetCookieConsentGiven']); 
					// enable consent mode
					_paq.push(['requireCookieConsent']);
				// and agreed to cookies
				} else {
					if ( fp.main.debug ) console.log('[FP] Matomo - Cookie permissions have been updated. Matomo is now running in a standard mode (with cookies)');
					_paq.push(['setCookieConsentGiven']);
				}
			}
		};

		// add trailing slash if not present
		if ( fp.mato.url.substr(-1) != '/') fp.mato.url = fp.mato.url + '/';

		// TRACK CUSTOM DIMENSIONS ATTACHED TO THE PAGEVIEW ACTION

		let page_title = document.title;

		// track clean page titles
		if ( fp.mato.clean_page_title && fpdata.page_title && fpdata.page_title.length > 0 ) {

			page_title =  fpdata.page_title;

			_paq.push(['setDocumentTitle', fpdata.page_title]);

			// "SEO TITLE" CUSTOM DIMENSION
			if ( fp.mato.seo_title_dimens ) {
				_paq.push( ['setCustomDimension', fp.mato.seo_title_dimens, document.title ] );
			}

		}

		// "PAGE ID" CUSTOM DIMENSION
		if ( fp.mato.page_id_dimens && fpdata.page_id && fpdata.page_id > 0 ) {
			_paq.push( ['setCustomDimension', fp.mato.page_id_dimens, fpdata.page_id ] );
		}

		// "PAGE TYPE" CUSTOM DIMENSION
		if ( fp.mato.page_type_dimens && fpdata.page_type && fpdata.page_type.length > 1 ) {
			_paq.push( ['setCustomDimension', fp.mato.page_type_dimens, fpdata.page_type ] );
		}

		// "AUTHOR DISPLAY NAME" CUSTOM DIMENSION
		if ( fp.mato.author_dimens && fpdata.author_name && fpdata.author_name.length > 0 ) {
			_paq.push( ['setCustomDimension', fp.mato.author_dimens, fpdata.author_name ] );
		}

		// "AUTHOR ID" CUSTOM DIMENSION
		if ( fp.mato.author_id_dimens && fpdata.author_id > 0 ) {
			_paq.push( ['setCustomDimension', fp.mato.author_id_dimens, fpdata.author_id ] );
		}
		
		// "PAGE LANGUAGE" CUSTOM DIMENSION
		if ( fp.mato.page_lang_dimens ) {
			_paq.push( ['setCustomDimension', fp.mato.page_lang_dimens, document.documentElement.lang || 'undefined' ] );
		}
		
		
		
		// "USER ROLE" CUSTOM DIMENSION
		if ( fp.mato.user_role_dimens && fpdata.user.role && fpdata.user.role.length > 0 ) {
			_paq.push( ['setCustomDimension', fp.mato.user_role_dimens, fpdata.user.role ] );
		}
		
		// "TAXONOMY TERMS" CUSTOM DIMENSION
		if ( fp.mato.tax_terms_dimens && fpdata.terms && fpdata.terms.length > 0 ) {
			
			var term_arr = fpdata.terms.map( function (term_data) {
				var term = fp.mato.send_tax_terms_titles ? term_data.name : term_data.slug;
				term += fp.mato.add_tax_term_cat ? ' (' + term_data.taxonomy + ')' : '';
				return term;
			} );
			
			_paq.push( ['setCustomDimension', fp.mato.tax_terms_dimens, term_arr.join(', ') ] );
		}

		// FORMATS OF FILES TO TRACK TRACK DOWNLOADS
		if ( fp.mato.track_downl_file_formats ){
			_paq.push(['setDownloadExtensions', fp.mato.track_downl_file_formats.replaceAll(',','|').replaceAll(' ','') ]);
		}

		
		
		// ENABLE HEARTBEAT
		if ( fp.mato.enable_hearbeat ) {
			_paq.push(['enableHeartBeatTimer']);
		}

		_paq.push(['enableLinkTracking']);
		_paq.push(['setSiteId', fp.mato.id]);
		_paq.push(['setTrackerUrl', fp.mato.url + 'matomo.php']);
		
		if (fp.mato.track_subdomains){

			let subdomain = document.location.host.split('.');
			
			subdomain.shift();
			subdomain = '*.' + subdomain.join('.') + '/';
			
			// Share the tracking cookie across example.com, www.example.com, subdomain.example.com, ...
			_paq.push(['setCookieDomain', subdomain]);
	
			// Tell Matomo the website domain so that clicks on these domains are not tracked as 'Outlinks'
			_paq.push(['setDomains', subdomain]);
		}

		// send pageView or siteSearch
		switch ( fpdata.page_type ) {
			case 'Woo Product':
				break;
			
			case 'Woo Shop Page':
			case 'Woo Product Category':
			case 'Woo Product Tag':
				_paq.push(['setEcommerceView', false, false, page_title ]);
				_paq.push(['trackPageView']);
				break;

			case 'Search':
			case 'Woo Product Search':
				let search_cat = fpdata.page_type == "Woo Product Search" ? 'product' : false;
				_paq.push( [ 'trackSiteSearch', fpdata.search_query, search_cat, fpdata.search_results ] );	
				break;
		
			default:
				_paq.push(['trackPageView']);
				break;
		}

		let script_src = fp.mato.src ? fp.mato.src : fp.mato.url + 'matomo.js';

		FP.getScript( script_src, false, {'async':true} );

		
		
		// LOAD FOOTER SCRIPTS
		fp.loaded.push('mato');
		if ( fp.main.debug ) console.log('[FP] Matomo loaded');
		FP.runFn( 'FP.fns.load_mato_footer' );
	}

})(window);
