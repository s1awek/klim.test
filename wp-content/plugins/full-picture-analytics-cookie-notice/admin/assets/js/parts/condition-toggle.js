(() => {

	// CONDITIONS
	// toggle fields that can be conditionally toggled

	let condition_fields = FP.findAll( '.fupi_condition' );
	
	// sync after pageload

	condition_fields.forEach( field => {
		sync_settings( field );
	});
	
	function get_cond_field(e){
		if ( e.target.classList.contains('fupi_condition') ){
			sync_settings(e.target);
		}
	}

	// after click
	document.onclick = (e) => { get_cond_field( e ); };

	// on key up
	document.onkeyup = (e) => { get_cond_field( e ); }

	// on change
	document.onchange = (e) => { get_cond_field( e ); }

	function sync_settings( field ) {

		let field_type = field.getAttribute('type') || field.tagName.toLowerCase(),
			table_rows = FP.findAll('.' + field.dataset.target);

		switch (field_type) {

			case 'checkbox':
				let should_toggle = field.classList.contains('fupi_condition_reverse') ? ! field.checked : field.checked;
				toggle_rows(table_rows, should_toggle);
			break;

			case 'number':
				let val = field.value != 0 ? true : false;
				toggle_rows( table_rows, val );
			break;

			case 'text':
			case 'textarea':
				toggle_rows(table_rows, !!field.value );
			break;

			case 'select' :
				toggle_rows(table_rows, field.value, 'select');
			break;

			case 'radio' :
				if ( field.checked ) toggle_rows(table_rows, field.value, 'radio');
			break;

		}
	}

	function toggle_rows( table_rows, enabled, field_type ) {

		table_rows.forEach( row => {

			let inputs = row.classList.contains('fupi_missing_must_have') ? [] : FP.findAll( 'input, textarea, select', row );
			
			if ( field_type != 'select' && field_type != 'radio' ) {

				// toggle row
				if ( enabled ) {
					if ( row.classList.contains('fupi_missing_must_have') ) { 
						row.classList.remove( 'fupi_hidden' );
					} else {
						row.classList.remove( 'fupi_hidden', 'fupi_disabled' );
					}
				} else {
					if ( row.classList.contains('fupi_missing_must_have') ) { 
						row.classList.add( 'fupi_hidden' );
					} else {
						row.classList.add( 'fupi_hidden', 'fupi_disabled' );
					}
				}

				// disable fields
				inputs.forEach( input => input.disabled = ! enabled );

			} else {

				// toggle row and inputs
				if ( row.classList.contains('fupi_cond_val_' + enabled ) ) {
					
					if ( row.classList.contains('fupi_missing_must_have') ) { 
						row.classList.remove( 'fupi_hidden' );
					} else {
						row.classList.remove( 'fupi_hidden', 'fupi_disabled' );
					}

					inputs.forEach( input => input.disabled = false );

				} else {

					if ( row.classList.contains('fupi_missing_must_have') ) { 
						row.classList.add( 'fupi_hidden' );
					} else {
						row.classList.add( 'fupi_hidden', 'fupi_disabled' );
					}

					inputs.forEach( input => input.disabled = true );
				}
			}
		})
	}

})();

(()=>{
	
	// CONSENT BANNER > TOGGLE HIDDEN RADIOS & "MANUAL" FIELDS
	
	let mode_select = FP.findFirst('.fupi_cookie_notice_modes select');

	if ( ! mode_select ) return;
	
	// first we hide all manual fields
	FP.findID('fupi_settings_form').classList.add('fupi_hide_manual_cookie_settings');
	
	// show manual fields if manual radio is checked
	toggle_manual_fields();

	function toggle_manual_fields(){
		if ( mode_select.value == 'manual' ) {
			FP.findID('fupi_settings_form').classList.remove('fupi_hide_manual_cookie_settings');
		} else {
			FP.findID('fupi_settings_form').classList.add('fupi_hide_manual_cookie_settings');
		}
	}

	// then we go over each radio field
	mode_select.addEventListener('change', toggle_manual_fields )

})();