(() => {

	// DISABLE SETTINGS NOT AVAILABLE FOR FREE USERS

	let fupi_content_el = FP.findID('fupi_content');

	if ( ! fupi_content_el ) return;

	FP.findAll('.fupi_disable_fields').forEach( el => {
		
		// get what to block - all the fields in tr or fields in r3
		
		let closest_r3 = el.closest('.fupi_r3_field');

		let wrap = closest_r3 ? closest_r3 : el.closest('tr'),
			fields = FP.findAll( 'input, select, textarea', wrap ),
			buttons = FP.findAll( '.fupi_r3_btn', wrap );
		
		fields.forEach( field => field.disabled = true );
		buttons.forEach( btn => btn.classList.add('fupi_disabled') );
		
		el.classList.remove('fupi_disable_fields');
		wrap.classList.add('fupi_missing_must_have', 'fupi_disabled');
	});
	
})();
