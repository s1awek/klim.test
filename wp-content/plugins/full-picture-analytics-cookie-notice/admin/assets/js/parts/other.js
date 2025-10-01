(()=>{

	// TOGGLE FILTERS SECTION

	let toggle_btn = FP.findID('fupi_toggle_filters_section'),
		filters_section = FP.findID('fupi_tools_filters');

	if ( toggle_btn && filters_section ) toggle_btn.addEventListener( 'click', ()=> filters_section.classList.toggle('fupi_active') );
})();

(()=>{

	// COPY CURRENT PAGE NAME TO SECTION HEADINGS

	let module_name = FP.findFirst('.fupi_active_title'),
		section_headings = FP.findAll('#fupi_settings_form h2');

	if ( module_name ) {
		section_headings.forEach( h => {
			h.insertAdjacentHTML( 'afterbegin', '<span class="fupi_module_name">' + module_name.textContent + '</span>')
		} )
	}
})();

(()=>{

	// TAB NAVIGATION

	document.addEventListener('click', e=>{
		if ( e.target.classList.contains('fupi_tab') && ! e.target.classList.contains('fupi_active') ){
			FP.findFirst( '.fupi_tab.fupi_active', e.target.parentElement ).classList.remove('fupi_active');
			e.target.classList.add('fupi_active');
		}
	})

})();

(()=>{

	// ADD "EXTERNAL" DASHICON TO LINKS AND SET THEM TO OPEN IN A NEW TAB

	window.addEventListener( 'DOMContentLoaded', ()=>{

		let links = FP.findAll('#fupi_main_col a');

		links.forEach( link => {
			if ( ! link.href.includes(document.location.host) && ! link.classList.contains('fupi_vid') && ! link.classList.contains('fupi_vid_btn') ) {
				if ( ! link.target ) link.target = '_blank';
				link.insertAdjacentHTML('beforeend', ' <span class="dashicons dashicons-external"></span>');
			}
		})

	})
})();

(()=>{
	// PLAUSIBLE > TOGGLE SECTIONS OF SETTINGS PAGE DEPENDING IF A USER WANTS TO USE WP FP TO EXTEND PLAUSIBLE PLUGIN OR NOT

	let install_pla_with_wpfp = FP.findID('fupi_pla[pla_use]_install');

	if ( ! install_pla_with_wpfp ) return;

	let sections_to_hide = [
		'#fupi_current_page_sidenav button[data-target="hook_pla_1"]',
		'#fupi_current_page_sidenav button[data-target="hook_pla_3"]',
		'#fupi_current_page_sidenav button[data-target="hook_pla_6"]',
	]

	function toggle_sections( state ){

		sections_to_hide.forEach( selector => {
			let nav_el = FP.findFirst( selector );
			nav_el.style.display = state == 'hide' ? 'none' : 'block';
		})
	}

	let extend_pla_with_wpfp = FP.findID('fupi_pla[pla_use]_extend');

	// do after load
	
	if ( extend_pla_with_wpfp.checked ) toggle_sections( 'hide' );

	// do on click

	extend_pla_with_wpfp.addEventListener( 'change', ()=>{ if ( extend_pla_with_wpfp.checked ) toggle_sections( 'hide' ) } );
	install_pla_with_wpfp.addEventListener( 'change', ()=>{ if ( install_pla_with_wpfp.checked ) toggle_sections( 'show' ) } );
})();

(()=>{

	// SHOW ALERT ABOUT UNSAVED CHANGES

	window.fupi_unsaved = false;

	document.addEventListener("DOMContentLoaded", function() { 

		var els = document.querySelectorAll('#fupi_settings_form textarea, #fupi_settings_form input, #fupi_settings_form select');
		
		els.forEach( function(el) {
			el.addEventListener('change', function() {
				window.fupi_unsaved = true;
				// disable a button linking to consent banner customizer
				let cookie_notice_customizer = FP.findFirst('.fupi_customize_notice_btn');
				if ( cookie_notice_customizer ) cookie_notice_customizer.classList.add('fupi_disabled');
			});
		});  
			
		window.addEventListener('beforeunload', function(event) {
			if(window.fupi_unsaved){
				event.returnValue = "string";
			}
		});

		var forms = document.querySelectorAll('form');
		forms.forEach( function(form) {
			form.addEventListener('submit', function() {
				window.fupi_unsaved = false;
			});
		});  

	});
})();

(()=>{

	// SLIDER with info on PRO features

	function show_random_slide( slide_dots, slides ){
		
		let slide_nr = Math.floor( Math.random() * slides.length );
		
		slides[slide_nr].classList.add('fupi_active');
		slide_dots[slide_nr].classList.add('fupi_active');
	}

	function change_slide_on_click( slider, slide_dots, slides ){
		
		slide_dots.forEach( dot => {

			dot.addEventListener( 'click', ()=>{
				
				let slide_nr = slide_dots.indexOf( dot ),
					active_slide = FP.findFirst( '.fupi_slide.fupi_active', slider ),
					active_slide_dot = FP.findFirst( '.fupi_slider_dot.fupi_active', slider );

				active_slide.classList.remove('fupi_active');
				active_slide_dot.classList.remove('fupi_active');

				slides[slide_nr].classList.add('fupi_active');
				dot.classList.add('fupi_active');
			})
		})
	}

	function make_slider_dots( slider, slides ){

		let slider_dots_nav = FP.findFirst('.fupi_slider_nav', slider ),
			dots = '';

		for ( let i = 0; i < slides.length; i++ ) {
			dots += '<li><button type="button" class="fupi_slider_dot"><span>Show slide ' + ( i + 1 ) + '</span></button></li>';
		}

		slider_dots_nav.innerHTML = dots;
	}

	document.addEventListener('DOMContentLoaded', ()=>{
		
		let slider = FP.findFirst('.fupi_slider');
		if ( ! slider ) return;

		let slides = FP.findAll( '.fupi_slide', slider );

		make_slider_dots( slider, slides );

		let slide_dots = FP.findAll( '.fupi_slider_dot', slider )

		show_random_slide( slide_dots, slides );
		change_slide_on_click( slider, slide_dots, slides );
	})
})();

// UPDATE SETUP AND ADV MODES IN FUPI_MAIN WITH AJAX

(() => {

	const advModeCheckbox = FP.findID('adv_mode_checkbox');
	const setupModeCheckbox = FP.findID('setup_mode_checkbox');

	function alertBeforeChange(e){

		e.stopPropagation();
		e.preventDefault();

		const value = e.target.checked,
			adv_mode_alert_text = fupi_adv_mode_alert_text || 'This will reload the page. All unsaved data will be lost. Are you sure?';

		// open alert box with text" Are you sure" and buttons yes and no

		// if the user confirms
		if ( confirm( adv_mode_alert_text ) ) {

			// send the data to the server and refresh
			handleChange(e);
		
		// if the user cancels
		} else {

			// do not check the switcher's state
			e.target.checked = ! value;
		}
	}

	function handleChange(e) {

		const mode = e.target.id === 'adv_mode_checkbox' ? 'adv_mode' : 'setup_mode';
		const value = e.target.checked;
		const data = new FormData();

		data.append('action', 'fupi_update_modes');
		data.append('mode', mode);
		data.append('value', value);
		data.append('security', fupi_setup_mode_nonce);

		fetch( ajaxurl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin'
		} )
		.then(response => response.json())
		.then(response => {
			console.log(response.data);

			if ( mode == 'adv_mode' ) {
				
				// remember which popup to open after the reload
				let popup_id_to_open = value ? 'fupi_popup_adv_mode_intro' : 'fupi_popup_easy_mode_intro';
				FP.setCookie('fupi_admin_open_popup', popup_id_to_open );

				// reload the page
				document.location.reload();
			}
		});
	}

	if (advModeCheckbox) advModeCheckbox.addEventListener('change', alertBeforeChange  );
	if (setupModeCheckbox) setupModeCheckbox.addEventListener('change', handleChange);

})();

// ENABLE SELCT2 FIELSDS THAT ARE NOT IN A REPEATER

jQuery( document ).ready( function($) {
	if ( jQuery.isFunction(jQuery.fn.select2) ){
		jQuery('.fupi_select2:not(.fupi_select2_enabled)').each( function(){
			$select2 = jQuery(this);

			if ( $select2.hasClass('fupi_user_search') ) {

				$select2.select2({
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						delay: 250,
						data: function (params) {
							return {
								q: params.term,
								action: 'fupi_search_users',
							};
						},
						processResults: function(data) {
							return {
								results: data
							};
						},
						cache: true
					},
					width: '100%',
					minimumInputLength: 2,
					placeholder: $select2.data('placeholder_text')
				});

			} else if ( $select2.hasClass('fupi_page_search') ) {

				$select2.select2({
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						delay: 250,
						data: function (params) {
							return {
								q: params.term,
								action: 'fupi_search_pages',
							};
						},
						processResults: function(data) {
							return {
								results: data
							};
						},
						cache: true
					},
					width: '100%',
					minimumInputLength: 2,
					placeholder: $select2.data('placeholder_text')
				});
	
			} else {
				$select2.select2();
			}

			$select2.addClass('fupi_select2_enabled');
		})
	};
});

// HIDE WOOCOMMERCE SETTINGS FIELDS (e.g. in the GAds module) when Woo is not enabled

(()=>{
	let woo_not_installed_notice = FP.findFirst('.fupi_enable_woo_notice');

	if ( woo_not_installed_notice ) {
		// get description wrapper
		let descr = woo_not_installed_notice.parentElement;
		// get the next HTML element after description wrapper
		let next_element = descr.nextElementSibling;
		// check if next element is a table
		if ( next_element.tagName === 'TABLE' ) {
			next_element.style.display = 'none';
		}
	}
})();
