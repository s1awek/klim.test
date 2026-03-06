(() => {

	// Change the styling of the modules' table to grid

	let fupi_settings_tables	= FP.findAll('#fupi_settings_form table'),
		fupi_admin_page 		= FP.getUrlParamByName('page');

	if ( fupi_admin_page && fupi_admin_page == 'full_picture_tools' && fupi_settings_tables.length > 0 ) {
		fupi_settings_tables.forEach( table => table.classList.add('fupi_table_grid') );
	}

	// Add a "Tag Managers" headline

	let fupi_tagman_module		= FP.findFirst('tr.fupi_tagman'),
		fupi_tagman_headline_html = FP.findFirst('.fupi_tagman_headline_html_template');

	if ( fupi_tagman_module && fupi_tagman_headline_html ){
		fupi_tagman_module.insertAdjacentHTML( 'beforebegin', fupi_tagman_headline_html.innerHTML );
	}
})();