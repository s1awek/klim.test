(() => {

	// Change the styling of the modules' table to grid

	let fupi_settings_tables	= FP.findAll('#fupi_settings_form table'),
		fupi_admin_page 		= FP.getUrlParamByName('page');

	if ( fupi_admin_page && fupi_admin_page == 'full_picture_tools' && fupi_settings_tables.length > 0 ) {
		fupi_settings_tables.forEach( table => table.classList.add('fupi_table_grid') );
	}

	// Add an "Advanced integrations" headline

	let fupi_first_module		= FP.findFirst('.fupi_table_grid tr'),
		fupi_adv_headline_html = FP.findFirst('.fupi_adv_headline_html_template');

	if ( fupi_first_module && fupi_adv_headline_html ){
		fupi_first_module.insertAdjacentHTML( 'beforebegin', fupi_adv_headline_html.innerHTML );
	}

	// Add a "Basic integrations" headline

	let fupi_basic_module		= FP.findFirst('tr.fupi_basic'),
		fupi_basic_headline_html = FP.findFirst('.fupi_basic_headline_html_template');

	if ( fupi_basic_module && fupi_basic_headline_html ){
		fupi_basic_module.insertAdjacentHTML( 'beforebegin', fupi_basic_headline_html.innerHTML );
	}
})();