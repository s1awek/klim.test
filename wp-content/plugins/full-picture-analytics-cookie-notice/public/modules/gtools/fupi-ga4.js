FP.fns.load_ga4 = function (nr, wait_for_nr2) {

	var _ga = fp[ 'ga4' + nr ],
		params = {'groups' : 'fupi_ga4'};

	// CHANGE COOKIE PREFIX
	// optional for the tracking ID #1
	if ( _ga.cookie_prefix && nr == '1' ) params.cookie_prefix = '_fp4' + nr;

	// enable GA's debug mode
	// @ https://support.google.com/analytics/answer/7201382?hl=en
	params.debug_mode = fpdata.cookies.ga4_debug;

	

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
	FP.runFn( 'FP.fns.load_ga4_footer', [nr] );
};
