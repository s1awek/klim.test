function fupi_head_gtm(){

	// !!! Datalayer is created in JS helpers

	var data_o = {
		'event' : 'fp_staticData',
	};

	// PASS COOKIE INFO

	if ( fp.notice.enabled ) {
		data_o['fp_privacyBannerEnabled'] = true;
		data_o['fp_visitorPrivacyPreferences'] = fpdata.cookies;
	} else {
		data_o['fp_privacyBannerEnabled'] = false;
		data_o['fp_visitorPrivacyPreferences'] = false;
	}

	// PASS IF CURRENT USER IS TRACKABLE

	data_o['fp_trackCurrentUser'] = fp.main.track_current_user;

	// PAGE TYPE
	if ( fp.gtm.page_type && fpdata.page_type && fpdata.page_type.length > 1 )
		data_o['fp_contentType'] = fpdata.page_type;

	

	// PAGE LANGUAGE
	if ( fp.gtm.page_lang )
		data_o['fp_contentLang'] = document.documentElement.lang || 'undefined';

	// USER BROWSER LANGUAGE
	if ( fp.gtm.browser_lang )
		data_o['fp_browserLang'] = navigator.language;

	// PAGE TITLE
	if ( fp.gtm.page_title ) {
		if ( fpdata.page_title ) data_o['fp_contentTitle'] = fpdata.page_title;
		data_o['fp_contentTitleSEO'] && document.title;
	}

	// PAGE ID
	if ( fp.gtm.page_id && fpdata.page_id && fpdata.page_id > 0 )
		data_o['fp_contentID'] = fpdata.page_id;

	// PAGE NUMBER
	if ( fp.gtm.page_num && fpdata.page_number > 0 )
		data_o['fp_contentNumber'] = fpdata.page_number;

	// AUTHOR DISPLAY NAME
	if ( fp.gtm.author && fpdata.author_name && fpdata.author_name.length > 0 )
		data_o['fp_contentAuthor'] = fpdata.author_name;

	// AUTHOR ID
	if ( fp.gtm.author_id && fpdata.author_id > 0 )
		data_o['fp_contentAuthorID'] = fpdata.author_id;

	// PUBLISHED DATE
	if ( fp.gtm.post_date && fpdata.published && fpdata.published.length > 0 )
		data_o['fp_contentDate'] = fpdata.published;

	// SEARCH TERMS
	if ( fp.gtm.search_terms && fpdata.search_query && fpdata.search_query.length > 0 )
	data_o['fp_searchTerm'] = fpdata.search_query;

	// NO OF SEARCH RESULTS
	if ( fp.gtm.search_results && fpdata.search_results && fpdata.search_results > 0 )
	data_o['fp_searchResults'] = fpdata.search_results;
	
	

	// USER ROLE
	if ( fp.gtm.user_role && fpdata.user.role && fpdata.user.role.length > 0)
	data_o['fp_visitorType'] = fpdata.user.role;

	// TAXONOMY TERMS
	if ( fp.gtm.terms && fpdata.terms && fpdata.terms.length > 0 ) {
		data_o['fp_contentTerms'] = fpdata.terms;
	}

	// LOAD GTM

	(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script',fp.gtm.datalayer,fp.gtm.id);

	// send basic data
	window[fp.gtm.datalayer].push(data_o);

	

	// mark as loaded
	FP.loaded('gtm', 'gtm', '[FP] GTM loaded');
}

if ( fp.gtm && fp.gtm.id ) FP.load('gtm', 'fupi_head_gtm', ['head_helpers']);
