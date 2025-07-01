(()=>{

	// INCREASE / DECREASE VALUE IN THE "NUMBER" FIELD AFTER CLICKS IN "PLUS" AND "MINUS" BUTTONS

	function changeNumber( action, btn ){

		let num_field = FP.findFirst( 'input[type="number"]', btn.parentElement.parentElement ),
			old_val = parseInt( num_field.value || 0 );

		if ( action == 'plus' ){
			num_field.value = old_val + ( num_field.step || 1 );
		} else {
			num_field.value = old_val - ( num_field.step || 1 );
		}
	}

	document.addEventListener('click', e=>{
		let btn = e.target;
		if ( btn.matches('tr:not(.fupi_disabled) .fupi_number_increase .dashicons') ) {
			changeNumber( 'plus', btn );
		} else if ( e.target.matches('tr:not(.fupi_disabled) .fupi_number_decrease .dashicons') ) {
			changeNumber( 'minus', btn );
		};
	})
})();
