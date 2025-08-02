(() => {

	// CONDITIONS
	// toggle fields that can be conditionally toggled

	let condition_fields = FP.findAll( '.fupi_condition' );

	if ( condition_fields.length > 0 ) {

		condition_fields.forEach( field => {

			// after pageload
			sync_settings( field );

			// after click
			field.onclick = (e) => { sync_settings( field ); };

			// on key up
			field.onkeyup = (e) => { sync_settings( field ); }

			// on change
			field.onchange = (e) => { sync_settings( field ); }
		})
	};


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

(()=>{

	// Google Ads - Show conversion ID field if GTAG is combined
	
	let gtag_field = FP.findID('fupi_gads[id]'),
		conv_id_field = FP.findID('fupi_gads[id2]');

	if ( ! gtag_field || ! conv_id_field ) return;

	// after pageload
	toggle_convid_field( gtag_field );

	// after click
	gtag_field.onclick = (e) => { toggle_convid_field(); };

	// on key up
	gtag_field.onkeyup = (e) => { toggle_convid_field(); }

	// on change
	gtag_field.onchange = (e) => { toggle_convid_field(); }

	function toggle_convid_field() {

		let row = conv_id_field.closest('tr');

		if ( gtag_field.value.length > 2 && gtag_field.value.indexOf('GT-') == 0 ) {
			
			row.classList.remove( 'fupi_hidden', 'fupi_disabled' );
			conv_id_field.disabled = false;
			
		} else {

			row.classList.add( 'fupi_hidden', 'fupi_disabled' );
			conv_id_field.disabled = true;

		}
	}

})();