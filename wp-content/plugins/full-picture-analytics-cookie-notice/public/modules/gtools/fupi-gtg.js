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

function fupi_load_gads(){   
	
	if ( ! fp.loaded.includes('ga41') || fp.ga41.id != fp.gads.id ) {
		if ( fp.vars.gtag_enh_conv ) {
			window.gtag( 'config', fp.gads.id, { 'allow_enhanced_conversions': true } );
		} else {
			window.gtag( 'config', fp.gads.id );
		}
	}

	

	FP.loaded('gads_config', 'gads');
};

function fupi_load_ga4( nr ) {

	var _ga = fp[ 'ga4' + nr ],
		params = {};

	if ( fp?.gads?.id && fp?.gads?.id == fp.ga41.id && fp.vars.gtag_enh_conv ) {
		params.allow_enhanced_conversions = true;
	}

	// CHANGE COOKIE PREFIX
	// optional for the tracking ID #1
	if ( _ga.cookie_prefix && nr == '1' ) params.cookie_prefix = '_fp4' + nr;

	// enable GA's debug mode
	// @ https://support.google.com/analytics/answer/7201382?hl=en
	if ( fpdata?.cookies?.ga4_debug ) params.debug_mode = true;

	

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

	

	FP.loaded('ga4' + nr + '_config', 'ga4' + nr );
}

function fupi_enable_gtag() {

	// First, fix GAds IDs
	if ( fp.gads ) {
		if ( 
			fp.gads.id && 
			! fp.gads.id.includes('AW-') && 
			! fp.gads.id.includes('GT-') && 
			! fp.gads.id.includes('G-') 
		) fp.gads.id = 'AW-' + fp.gads.id;
	
		if ( 
			fp.gads.id2 && 
			! fp.gads.id2.includes('AW-') && 
			! fp.gads.id2.includes('GT-') && 
			! fp.gads.id2.includes('G-')
		) fp.gads.id2 = 'AW-' + fp.gads.id2;
	}

	// set gtag date
	if ( ! fp.loaded.includes('gtg') ) {
		window.gtag('js', new Date());
	}

	fp.vars.gtag_enh_conv = false;

	

	FP.loaded('gtag_config', 'gtg');
};

function fupi_load_ga41(){
	fupi_load_ga4( 1 );
};

function fupi_load_ga42(){
	fupi_load_ga4( 2 );
};

FP.load('gtag_config', 'fupi_enable_gtag', ['head_helpers', 'gtag_file']);

if ( fp.ga41?.id ) {
	FP.load('ga41_config', 'fupi_load_ga41', ['gtag_config']);
	
}

if ( fp.gads?.id ) FP.load('gads_config', 'fupi_load_gads', ['gtag_config']);
