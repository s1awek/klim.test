
(()=>{

	let offscreen = FP.findID('fupi_offscreen'),
		offscreen_content_el = FP.findID('fupi_offscreen_content'),
		offscreen_close_btn = FP.findID('fupi_offscreen_close_btn'),
		offscreen_maximize_btn = FP.findID('fupi_offscreen_maximize_btn'),
		content_els = FP.findAll('table.form-table .fupi_popup_content:not(.fupi_do_not_create_popup_icon)'),
		next_popup_btn = FP.findID('fupi_offscreen_next_btn'),
		prev_popup_btn = FP.findID('fupi_offscreen_prev_btn'),
		open_btn_side = FP.findID('fupi_offscreen_open_btn'),
		current_popup_index = -1,
		popup_history = [];

	function update_popup_history( btn ) {
		
		switch ( btn.dataset.type ) {
			
			// when a user clicks a previous button
			case 'prev':
				// subtract 1 from index
				if ( current_popup_index > 0 ) current_popup_index--;
			break;	
			
			// when a user clicks a next button
			case 'next':
				// add 1 to index
				current_popup_index++;	
			break;
			
			// when a user clicks a button that is neither next or previous
			default:
				// continue only if the user requested a different popup
				if ( popup_history[current_popup_index] != btn.dataset.popup ) {	
					// remove from popup_history all ids after the current one
					popup_history.splice( current_popup_index + 1 );
					// add new last element to popup history and update the index
					popup_history.push( btn.dataset.popup );
					current_popup_index++;
				}
			break;
		}
	}

	function update_popup_nav_btns() {

		// update the previous button
		if ( current_popup_index > 0 ) {
			prev_popup_btn.dataset.popup = popup_history[ current_popup_index - 1 ];
			prev_popup_btn.classList.remove('fupi_disabled');
		} else {
			prev_popup_btn.classList.add('fupi_disabled');
		}

		// update the next button
		if ( current_popup_index < ( popup_history.length - 1 ) ) {
			next_popup_btn.dataset.popup = popup_history[ current_popup_index + 1 ];
			next_popup_btn.classList.remove('fupi_disabled');
		} else {
			next_popup_btn.classList.add('fupi_disabled');
		}  
	}

	function add_content_to_popup( btn ) {

		offscreen.dataset.content_id = btn.dataset.popup;

		let new_content_el = btn.dataset.popup ? FP.findID( btn.dataset.popup ) : btn.nextElementSibling,
			buttonsHTML = '';

		if ( new_content_el.classList.contains( 'fupi_popup_content' ) ) {
			update_popup_nav_btns();
			offscreen_content_el.innerHTML = new_content_el.innerHTML + buttonsHTML;
		}
	}

	function show_popup() {
		offscreen.classList.add('fupi_active');
	}

	function hide_popup() {
		offscreen.dataset.content_id = '';
		offscreen.classList.remove('fupi_active');

		let youtubeIframe = FP.findFirst('.fupi_video iframe');
		if ( youtubeIframe ){
			youtubeIframe.contentpostMessage('{"event":"command","func":"stopVideo","args":""}', '*');
		};
	}

	function create_popup_icons() {
		
		// create popup "i" buttons in <th> that link to popups in <td>
		content_els.forEach( el => {

			let tr = el.closest('tr'),
				th = FP.findFirst('th', tr),
				warning_class = el.classList.contains('fupi_popup2') ? 'fupi_popup_warning' : el.classList.contains('fupi_popup3') ? 'fupi_popup_important' : '';

			th.insertAdjacentHTML('beforeend', '<button type="button" class="fupi_open_popup fupi_open_popup_i ' + warning_class + '" data-popup="' + el.id + '">i</button>');
		});

		// create popup "i" buttons in <th> that link to popups in other places
		FP.findAll('.fupi_create_popup_link').forEach( el => {
			
			let tr = el.closest('tr'),
				th = FP.findFirst('th', tr),
				warning_class = el.classList.contains('fupi_popup2') ? 'fupi_popup_warning' : el.classList.contains('fupi_popup3') ? 'fupi_popup_important' : '';

			th.insertAdjacentHTML('beforeend', '<button type="button" class="fupi_open_popup fupi_open_popup_i ' + warning_class + '" data-popup="' + el.dataset.popup_id + '">i</button>');
		});
	}

	// maximize/minimize popup

	function maximize_popup () {
		offscreen.classList.toggle('fupi_maximized');
		offscreen_maximize_btn.classList.toggle('fupi_maximized');
	};

	// start

	create_popup_icons();

	document.addEventListener( 'click', e => {
		
		let popup_btn = e.target.classList.contains('fupi_open_popup') ? e.target : e.target.parentElement.classList.contains('fupi_open_popup') ? e.target.parentElement : false;

		if ( popup_btn ) {

			if ( offscreen.dataset.content_id != popup_btn.dataset.popup ) {
				update_popup_history( popup_btn );
				add_content_to_popup( popup_btn );
				show_popup();
			} else { 
				hide_popup(); 
			}
		}
	})

	if ( open_btn_side ) open_btn_side.addEventListener('click', show_popup );
	if ( offscreen_close_btn ) offscreen_close_btn.addEventListener( 'click', hide_popup );
	if ( offscreen_maximize_btn ) offscreen_maximize_btn.addEventListener( 'click', maximize_popup );

})();
