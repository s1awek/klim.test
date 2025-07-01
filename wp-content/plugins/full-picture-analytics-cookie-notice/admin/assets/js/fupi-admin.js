(()=>{
    
    // Show notification if the tool is installed

    let required = FP.findAll('tr.fupi_required'),
        installed_info = FP.findID('fupi_installed_info'),
        fupi_not_installed_info = FP.findID('fupi_not_installed_info');

    if ( installed_info ) {
        if ( required.length == 0 ) {
            installed_info.classList.remove('fupi_hidden');
        } else {
            
            let some_required_are_empty = required.some( tr => {
                return ! FP.findFirst('input', tr).value;
            } );

            if ( ! some_required_are_empty ) {
                installed_info.classList.remove('fupi_hidden');
            } else {
                if ( fupi_not_installed_info ) fupi_not_installed_info.classList.remove('fupi_hidden');
            }
        }
    }
    
})();
// CREATE LINKS TO SECTIONS (IN SIDEBAR)

(() => {

	let fupi_settings_form 	= FP.findID('fupi_settings_form'),
		fupi_admin_page 	= FP.getUrlParamByName('page'),
		headlines 			= FP.findAll( '#fupi_settings_form h2' );

	function add_sub_items(){

		if ( headlines.length < 2 ) return;

		let current_page_el = FP.findID('fupi_current_page_sidenav'),
			output = '<div id="fupi_sidenav_sub">',
			active_tab = fupi_settings_form.dataset.activetab;
		
		headlines.forEach( ( headline, i ) => {
			
			let h_txt = headline.innerText,
				current_class = i == 0  ? 'active' : '';
			
			output += '<button type="button" data-target="hook_' + active_tab + '_' + i + '" class="fupi_nav_section_btn ' + current_class + '"><span>' + h_txt + '</span></button>';
			headline.setAttribute( 'id', 'hook_' + active_tab + '_' + i );
			headline.classList.add( 'fupi_hook', 'fupi_el');
		});

		current_page_el.insertAdjacentHTML('beforeend', output + '</div>');
	}

	function remove_highlight_from_active_menu_item(){
		let active_menu_link = FP.findFirst('#fupi_page_nav .fupi_nav_section_btn.active');
		if ( active_menu_link ) active_menu_link.classList.remove('active');
	}

	function enable_sections_toggle(){

		// headlines = FP.findAll('#fupi_settings_form h2');
		let fupi_page_nav_links = FP.findAll('#fupi_page_nav .fupi_nav_section_btn:not(.fupi_never_active)');

		// make 1st section visible & unhide form
		if ( headlines.length > 1 ) show_section( headlines[0].id ); // show first section
		// if ( fupi_settings_form ) fupi_settings_form.classList.remove('fupi_hidden');

		// add events to links that add a target to url & show sections
		if ( fupi_page_nav_links.length > 1 ) {

			fupi_page_nav_links.forEach( link => {
				link.addEventListener( 'click', () => {
					if ( ! link.classList.contains('active') ) {
						let hook = link.dataset.target;
						show_section( hook );
						FP.setCookie('fp_last_section', hook ); // remember chosen section
					}
				} )
			} )
		}
	}

	function show_last_viewed_section(){

		let last_viewed_section_hook = FP.readCookie('fp_last_section');

		if ( last_viewed_section_hook ) {
			let section_menu_item = FP.findFirst( '.fupi_nav_section_btn[data-target="' + last_viewed_section_hook + '"]' );
			if ( ! section_menu_item ) {
				FP.setCookie('fp_last_section', false );
			} else {
				show_section( last_viewed_section_hook );
			};
		}
	}

	function show_section( hook ) {

		let section_elements 	= FP.findAll( '.fupi_el, .form-table' ),
			pseudo_link 		= FP.findFirst( '.fupi_nav_section_btn[data-target="' + hook + '"]' );

		if ( pseudo_link ) {

			remove_highlight_from_active_menu_item();

			// hide all sections
			section_elements.forEach( element => element.classList.add('fupi_hidden'));
	
			// show chosen section
			FP.findAll('#' + hook + ', #' + hook + '+*, #' + hook + '+*+table').forEach( element => element.classList.remove('fupi_hidden'));
	
			// highlight current menu link
			pseudo_link.classList.add('active');
		}
	}

	if ( fupi_admin_page && fupi_admin_page != 'full_picture_tools' && fupi_settings_form) {
		add_sub_items();
		enable_sections_toggle();
		show_last_viewed_section();
	};

})();

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

	let mode_radios = FP.findAll('.fupi_cookie_notice_modes input[type="radio"]');

	function toggle_manual_fields( radio ){
		if ( radio.value == 'manual' ) {
			FP.findID('fupi_settings_form').classList.remove('fupi_hide_manual_cookie_settings');
		} else {
			FP.findID('fupi_settings_form').classList.add('fupi_hide_manual_cookie_settings');
		}
	} 

	// first we hide all manual fields
	if ( mode_radios.length > 0 ) {
		FP.findID('fupi_settings_form').classList.add('fupi_hide_manual_cookie_settings');
	};

	// then we go over each radio field
	mode_radios.forEach( radio => {

		let toggle = FP.findFirst('.fupi_cn_mode .fupi_switch_slider[data-mode="' + radio.value + '"]');
	
		// if we haven active radio
		if ( toggle && radio.checked ) {
			// make the toggle sldier active
			toggle.classList.add('fupi_active');
			// and show manual fields if we need to
			if ( radio.value == 'manual' ) FP.findID('fupi_settings_form').classList.remove('fupi_hide_manual_cookie_settings');
		}
	
		// Toggle on click
		toggle.addEventListener('click', () => {
			if ( ! toggle.classList.contains('fupi_active') ) {
				FP.findFirst('.fupi_cn_mode .fupi_active').classList.remove('fupi_active');
				toggle.classList.add('fupi_active');
				radio.checked = true;
				toggle_manual_fields(radio);
			}
		})
	} )
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
// FILTER THE LIST OF TOOLS

(()=>{

	let filter_btns = FP.findAll('.fupi_filter_btn');

	filter_btns.forEach( filter_btn => {
		
		filter_btn.addEventListener('click', () => {
		
			filter_btn.classList.toggle('fupi_active');
	
			let active_filters = FP.findAll('.fupi_filter_btn.fupi_active'),
				active_filters_arr = [],
				tools = FP.findAll('#fupi_settings_form table:first-of-type tr');
				
			active_filters.forEach( f => {
				active_filters_arr.push( f.dataset.tag );
			});
	
			tools.forEach( tool => {
				
				let input = FP.findFirst( 'input', tool ),
					tool_tags = input.dataset.tags,
					tool_tags_arr = tool_tags.split(' ');
				
				// if tools has all the tags then show it, otherwise hide it
				if ( active_filters_arr.every( filter_tag => tool_tags_arr.includes( filter_tag ) ) ) {
					tool.style.display = "block";
				} else {
					tool.style.display = "none";
				}
			} );

			if ( active_filters.length == 0 ) {
				tools.forEach( tool => tool.style.display = "block" );
			}
		});
	} )

})();
// SCRIPT REPEATER

(() => {

	let fupi_content = FP.findFirst('#fupi_content');

	if ( ! fupi_content ) return;

	let module_id = fupi_content.dataset.page;

	function remove_or_clear_section(target){
		let closest_section_wrap = target.closest('.fupi_r3_section'),
			siblingSections = FP.getSiblings( closest_section_wrap );

		siblingSections = siblingSections.filter(sect => sect.classList.contains('fupi_r3_section')); // get only r3 sections

		// check if this is the only section on this level
		if ( siblingSections.length > 0 ) {
			FP.remove(closest_section_wrap);
		} else {
			clear_section(closest_section_wrap);
		}
	}

	function add_section(target){

		let current_section = target.closest('.fupi_r3_section'),
			cloned_section = current_section.cloneNode(true);

		cloned_section = clear_section(cloned_section);
		cloned_section = remove_extra_repeaters(cloned_section);
		current_section.parentElement.insertBefore(cloned_section, current_section.nextSibling);

		if ( module_id == 'cscr' || module_id == 'blockscr' || module_id == 'reports' || module_id == 'atrig' ) {
			let id_field = FP.findFirst('.fupi_field_id_wrap input', cloned_section);
			if ( id_field ) id_field.value = generate_random_id();
		}
	}

	function destroy_all_select2s(){
		(($)=>{
			if ( $.isFunction($.fn.select2) ){
				$('.fupi_r3_repeater .fupi_select2_enabled.fupi_select2').each( function(){
					$select2 = $(this);
					$select2.select2('destroy');
					$select2.removeClass('fupi_select2_enabled');
					
				});
			}
		})(jQuery)
	}

	function enable_all_select2s(){
		jQuery(document).ready(function($) {

			if ( $.isFunction($.fn.select2) ){

				$('.fupi_r3_repeater .fupi_select2').each( function(){

					$select2 = $(this);

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

					} else {
						$select2.select2();
					}

					$select2.addClass('fupi_select2_enabled');
				})
			};
		});
	}

	function rename_fields_in_section( sections_wrap, parent_sect_nums ){ // section_wrap is a <td> or - when called from inside this function - fupi_r3_field

		if ( ! parent_sect_nums ) parent_sect_nums = [];

		// get all the sections that are direct descendants of the section wrap
		let sibling_sections = FP.nl2Arr( sections_wrap.children ),
			sections = sibling_sections ? sibling_sections.filter( siblig => siblig.classList.contains('fupi_r3_section') ) : []; // children of <td> ( fupi_r3_section )

		// console.log('same level sections', sections);

		sections.forEach( (section, sect_i) => {

			let current_sect_nums = [...parent_sect_nums, sect_i]; // starts as [0]

			// get all the field wrappers that are direct descendants of the section or are inside a fupi_r3_fields_group wrapper
			let section_children_arr = FP.nl2Arr( section.children ),
				field_wrappers = [];

			section_children_arr.forEach( child => {
				if ( child.classList.contains( 'fupi_r3_field' ) && ! child.classList.contains('fupi_field_type_label') ) {
					field_wrappers.push( child );
				} else if ( child.classList.contains( 'fupi_r3_fields_group' ) ) {
					let sub_section_children_arr = FP.nl2Arr( child.children );
					sub_section_children_arr.forEach( sub_child => {
						if ( sub_child.classList.contains( 'fupi_r3_field' ) && ! sub_child.classList.contains('fupi_field_type_label') ) {
							field_wrappers.push( sub_child );
						}
					})
				}
			});

			field_wrappers.forEach( ( field_wrap, field_i ) => {

				// if field wrapper contains a repeater run renaming function on its content
				if ( field_wrap.classList.contains('fupi_field_type_r3') ){

					rename_fields_in_section( field_wrap, current_sect_nums );

				// if the field wrapper contains form fields then modify their names and ids
				} else {

					let field = FP.findFirst('input, textarea, select', field_wrap);

					// make sure that wrapper has a form field
					if ( field ) {

						let name_parts = field.name.split(/\]\[\d*?\]\[/g) || []; // this gives us strings with square brackets, like '][3]['. We don't need anything more than that. We use it only to count how many occurances of digits appear
	
						if (name_parts.length - 1 <= current_sect_nums.length ){ // there should always be 1 more name part than numbers to fill in
	
							// here we create a name and id
							let new_name = name_parts.shift(); // we take the first name part (this also lowers the number of elements in the array)
							name_parts.forEach( ( part, i ) => { new_name += '][' + current_sect_nums[i] + '][' + part; } );
	
							// console.log('new field name for ' + field.name + ' is ' + new_name);
	
							field.name = new_name;
							field.id = new_name;
						} else {
							console.error('name can\'t be constructed for ', field, ' with: ', name_parts, current_sect_nums );
						}
					}

				};
			} );
		} );
	}

	function remove_extra_repeaters(section){
		let extra_section = FP.findFirst('.fupi_r3_repeater + .fupi_r3_repeater', section);
		if ( extra_section ) {
			FP.remove(extra_section);
			return remove_extra_repeaters(section);
		} else {
			return section;
		}
	}

	function clear_section(section){

		section.classList.add('fupi_highlight_req');

		// remove all empties
		FP.findAll('.field_empty', section).forEach( field => field.classList.remove('field_empty') );

		// clear values and mark with empties
		FP.findAll('textarea', section).forEach( area => {
			area.value = '';
			if ( area.classList.contains('fupi_req') ) area.parentElement.classList.add('fupi_empty');
		} );

		FP.findAll('input[type="text"]', section).forEach( field => {
			field.value = '';
			if ( field.classList.contains('fupi_req') ) field.parentElement.classList.add('fupi_empty');
		} );
		
		FP.findAll('input[type="password"]', section).forEach( field => {
			field.value = '';
			if ( field.classList.contains('fupi_req') ) field.parentElement.classList.add('fupi_empty');
		} );
		
		FP.findAll('input[type="number"]', section).forEach( field => {
			field.value = 0;
			if ( field.classList.contains('fupi_req') ) field.parentElement.classList.add('fupi_empty');
		} );
		
		FP.findAll('select', section).forEach( sel => {
			let option_el = FP.findFirst('option', sel);
			if ( option_el && option_el.value ) sel.value = option_el.value;
			
			if ( sel.classList.contains('fupi_req') ) sel.parentElement.classList.add('fupi_empty');
		} );
		
		// these do not get empties
		FP.findAll('input[type="checkbox"]', section).forEach( box => box.checked = false );
		FP.findAll('input[type="hidden"]', section).forEach( field => field.value = '' );


		return section;
	}

	function enable_section_buttons() {

		FP.findID('fupi_settings_form').addEventListener('click', e => {

			let btn = e.target;

			if ( btn.classList.contains('fupi_r3_btn') ){

				destroy_all_select2s();
				let section_wrap = btn.closest('td');

				if ( btn.classList.contains('fupi_btn_remove') ){
					remove_or_clear_section(btn);
					rename_fields_in_section(section_wrap);
				} else {
					add_section(btn);
					rename_fields_in_section(section_wrap);
					modify_specific_fields();
				}

				enable_all_select2s();
				hide_already_selected_atrig_selects();
			}
		})
	}

	// This is for the Custom Scripts module

	function get_attributes_from_str( str ){

		// return an object with key-value pairs
		// string example: 'data-param='abc def ghi' async src="https://www.googletagmanager.com/gtag/js?id=UA-11111111-1" data-param2="abc" id="anID" class="class1 class2"'

		let parts = str.trim().split(' '), // we break the string into parts by 'space' (spaces can occur in unexpected places! see example above)
			ok_parts = parts.filter( v => v ), // and remove empty values from array
			ret_val = {};

		for ( var i = 0; i < ok_parts.length; i++ ) {

			let part = ok_parts[i],
				eq_index = part.indexOf('='),
				last_char = part[part.length-1];

			// if we have an equal sign
			// (this means that the value before the equal sign is the key and the one after is the value)
			if ( eq_index != -1 ){

				// we check if the last element of the current part is an apostrophe. If it isn\'t then it means we split the value in two or more pieces and need to join it with the next string parts
				while ( ! ( last_char == '"' || last_char == "'" ) && i+1 <= ok_parts.length ) {
					part += ' ' + ok_parts[i+1];
					last_char = part[part.length-1];
					i++;
				}

				// if we have the key and the whole value
				if ( last_char == '"' || last_char == "'" ) {
					// we save the key and value pair
					let key_val = part.split(/=(.*)/s); // split on the first occurance of "=" sign. They may sometimes show up in values too
					ret_val[key_val[0]]= key_val[1];
				}

			// if we don't have an equal sign this is a single value
			} else {
				ret_val[part] = true;
			}
		}

		return ret_val;
	}

	function reformat_custom_scripts( script ){

		// remove all ending "</script>" tags
		script = script.replaceAll('</script>', '');

		// find all <script[something or nothing]> tags
		let start_regex = /<script[\s\S]*?>/gi,
			matches = script.match(start_regex),
			replacements = [];

		// get their attributes
		if ( Array.isArray( matches ) && matches.length > 0 ) matches.forEach( txt => {

			// remove "<script" and final ">"
			txt = txt.replace('<script','');
			txt = txt.slice(0, -1);
			txt = txt.trim();

			if ( txt.length == 0 ) {

				replacements.push('');

			} else {

				// try to transform the "<script [attributes]>" into "getScript()"
				let attributes_obj = get_attributes_from_str(txt),
					src = attributes_obj.src ? attributes_obj.src : false,
					attrs_str = '';

				if ( ! src ) {
					// Remove the whole <script> tag
					replacements.push('');
				} else {
					// Build string with attributes
					for (const [key, val] of Object.entries(attributes_obj)) {
						if ( key != 'src' ) {
							if ( attrs_str.length > 1 ) attrs_str += ", ";
							attrs_str += '"' + key + '": ' + val;
						}
					}
	
					replacements.push([src, attrs_str]);
				}
			}
		} );

		// prepare elements to replace <string [something or nothing]> in the original script
		replacements.forEach( ( repl, i ) =>{
			if ( repl ) replacements[i] = 'FP.getScript(' + repl[0] + ', false, {' + repl[1] + '});'; // repl[0] & repl[1] are already properly formatted
		} )

		// replace elements in string one by one

		let i = 0;

		return script.replace(start_regex, ()=>{ return replacements[i++]; });

		// e.target.value = e.target.value.replaceAll(/<.*?script[\s\S]*?>/g, '');
	}

	function enable_focusout_checks(){
		document.addEventListener( 'focusout', e => {

			switch (e.target.tagName) {
				case 'INPUT':
				case 'TEXTAREA':
				case 'SELECT':

					let r3_field_wrap = e.target.closest('.fupi_r3_field');

					if ( r3_field_wrap ) {

						// mark as empty
						if ( e.target.value ) {
							r3_field_wrap.classList.remove('field_empty');
							r3_field_wrap.classList.add('field_filled');
						} else {
							r3_field_wrap.classList.remove('field_filled');
							r3_field_wrap.classList.add('field_empty');
						}
						
						// sanitize script
						if ( module_id == 'cscr' ) {
							if ( e.target.tagName == 'TEXTAREA' ) {
								let new_value = reformat_custom_scripts( e.target.value ); // e.target.value = e.target.value.replaceAll(/<.*?script[\s\S]*?>/g, '');
								if ( new_value ) e.target.value = new_value;
							}
						}
						
						// sanitize script id
						if ( module_id == 'cscr' || module_id == 'scrblock' || module_id == 'reports' ) {
							if ( e.target.name.includes('[id]') ) e.target.value = e.target.value.replace(/[^a-z0-9_]/g, "");
						}
					}

				break;
			}
		})
	}

	// R3 SPECIFIC FUNCTIONS

	// FOR ADDING CUSTOM SCRIPTS

	function generate_random_id(){
		return Math.random().toString(36).replace(/[^a-z1-9]+/g, '').substring(0, 5); // 5 random  characters
	}

	function fill_empty_id_fields_with_random_ids(){
		let script_id_fields = FP.findAll('.fupi_field_id_wrap input');
		script_id_fields.forEach( field => field.value = field.value || generate_random_id() );
	}

	// for the Reporst & Stats module
	function make_ids_from_titles(){
	
		let reports_fields = FP.findAll('.fupi_reports_fields');

		if ( reports_fields.length == 0 ) return;
	
		document.addEventListener( 'change', e => {
			if ( e.target.tagName == 'INPUT' && e.target.name.includes('[title]') ) {
				
				let id_field = FP.findFirst( '.fupi_field_id_wrap input', e.target.closest('.fupi_r3_section') );
				
				if ( id_field ){
					let new_val = e.target.value ? (e.target.value.toLowerCase()).replace(/[^a-z0-9]/g, "") : generate_random_id();
					id_field.value = new_val;
				}
			}
		});
	}
	
	function toggle_leadscore_repeat_field( select, show ){
		let closest_r3_section = select.closest('.fupi_r3_section'),
			action_repeat_select = FP.findFirst( '.fupi_field_repeat_wrap select', closest_r3_section );

		if ( show ) {
			action_repeat_select.disabled = false;
		} else {
			if ( select.value.includes('fp_leadscore') ) {
				action_repeat_select.value = 'no';
				action_repeat_select.disabled = true;
			} else {
				action_repeat_select.disabled = false;
			};
		}
		
	}

    function listen_to_select_events(){
        document.addEventListener('change', e=>{
            if( e.target.tagName =='SELECT' && e.target.getAttribute('name').includes('atrig_id') ) {
				toggle_leadscore_repeat_field( e.target );
				hide_already_selected_atrig_selects();
			}
        })
    }

	function hide_already_selected_atrig_selects( trigger_selects ){

		trigger_selects = trigger_selects || FP.findAll('.fupi_events_builder select[name*="atrig_id"]');

		// get fields that are already selected
		let selected_options = [];

		trigger_selects.forEach( select => {
			if ( select.value ) selected_options.push( select.value );
		} );

		// hide already selected fields
		trigger_selects.forEach( select => {

			let all_options = FP.nl2Arr( select.options );

			all_options.forEach( opt => {
				opt.style.display = selected_options.includes( opt.value ) ? 'none' : 'block';
			})
		});
	}

	function modify_specific_fields(){

		// FOR SELECTING ADVANCED TRIGGERS ON A MODULE'S PAGE
		let trigger_selects = FP.findAll('.fupi_events_builder select[name*="atrig_id"]');
		
		hide_already_selected_atrig_selects( trigger_selects );
		
		trigger_selects.forEach( select => {
			toggle_leadscore_repeat_field( select );
		});
	}

	// INIT

	// check if there are any r3s on page
	if ( FP.findFirst('.fupi_r3_repeater') ) {
		
		if ( module_id == 'cscr' || module_id == 'blockscr' || module_id == 'reports' || module_id == 'atrig' ) fill_empty_id_fields_with_random_ids();

		enable_all_select2s();
		enable_section_buttons();
		enable_focusout_checks();
		modify_specific_fields();
    	listen_to_select_events();

		if ( module_id == 'reports' ) make_ids_from_titles();
	}

})();

;(()=>{

	// format: KEY
	// document.addEventListener('focusout', e=>{
	// 	if ( e.target.dataset.dataformat && e.target.dataset.dataformat == 'key' ){
	// 		console.log('focusout !');
	// 	}
	// })

	document.addEventListener('keyup', e=>{
		if ( e.target.dataset.dataformat && e.target.dataset.dataformat == 'key' ){
			let reg = /^\d/, // digit at the begining
				reg2 = /[^\w]/gi, // not a special char or underscore
				txt = e.target.value;

			txt = txt.replace(reg,'');
			txt = txt.replace(reg2,'');

			e.target.value = txt;
		}
	})
})();


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

(()=>{

    // clear sections that contain not existing (expired) trigger

    let builder_sections = FP.findAll('.fupi_events_builder .fupi_r3_section');

    builder_sections.forEach( section => {
        
        let atrig_select = FP.findFirst( '.fupi_field_type_atrig_select select', section );

        if ( ! atrig_select.value ){
            let minus_button = FP.findFirst( '.fupi_btn_remove', section );
            if ( minus_button ) minus_button.click();
        }
    } );

})();

(()=>{

    // Used in Custom Scripts module
    // Mark sections which contain scripts that can no longer trigger because they were triggered by advanced triggers that have been removed

    let atrig_selectors = FP.findAll('.fupi_r3_scr .fupi_field_atrig_id_wrap select');

    atrig_selectors.forEach( selector => {

        if ( selector.dataset.trigger == 'removed' || selector.value == 'removed' ) {
            
            let cscr_section = selector.closest( '.fupi_r3_scr' ),
                cscr_missing_atrig_text_el = FP.findID('fupi_cscr_missing_atrig_text'),
                cscr_missing_atrig_option_text_el = FP.findID('fupi_cscr_missing_atrig_select_text');
            
            cscr_section.classList.add('fupi_cscr_missing_atrig');

            // add notification above the script section
            if ( cscr_missing_atrig_text_el ) {
                $error_text = cscr_missing_atrig_text_el.textContent;
                cscr_section.insertAdjacentHTML('beforebegin', '<div class="fupi_cscr_missing_atrig_msg">' + $error_text + '</div>');
            }

            // add an option to the triggers select field
            if ( cscr_missing_atrig_option_text_el ) {
                $option_text = cscr_missing_atrig_option_text_el.textContent;
                selector.insertAdjacentHTML('afterbegin', '<option value="removed">' + $option_text + '</option>');
                selector.value = 'removed';
            }
        }
    } );

})();

(()=>{

    // clear sections that contain not existing (expired) custom meta

    let builder_sections = FP.findAll('.fupi_metadata_tracker .fupi_r3_section');

    builder_sections.forEach( section => {
        
        let metadata_select = FP.findFirst( '.fupi_field_type_custom_meta_select select', section );

        if ( ! metadata_select.value ){
            let minus_button = FP.findFirst( '.fupi_btn_remove', section );
            if ( minus_button ) minus_button.click();
        }
    } );

})();
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

	// ADD "ACCOUNT" LINK TO SIDE NAV

	let sidebar_account_link = FP.findFirst('#toplevel_page_full_picture_tools a[href="https://wpfullpicture.com/account/"]'),
		sidenav_section = FP.findFirst('.fupi_sidenav_account_section');

	if ( sidebar_account_link && sidenav_section ) {
		sidenav_section.style.display = 'block';
		FP.findFirst( 'a', sidenav_section ).href = sidebar_account_link.href;
	}
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

/**
 * Copyright Marc J. Schmidt. See the LICENSE file at the top-level
 * directory of this distribution and at
 * https://github.com/marcj/css-element-queries/blob/master/LICENSE.
 */
;
(function() {

    /**
     * Class for dimension change detection.
     *
     * @param {Element|Element[]|Elements|jQuery} element
     * @param {Function} callback
     *
     * @constructor
     */
    var ResizeSensor = function(element, callback) {
        /**
         *
         * @constructor
         */
        function EventQueue() {
            this.q = [];
            this.add = function(ev) {
                this.q.push(ev);
            };

            var i, j;
            this.call = function() {
                for (i = 0, j = this.q.length; i < j; i++) {
                    this.q[i].call();
                }
            };
        }

        /**
         * @param {HTMLElement} element
         * @param {String}      prop
         * @returns {String|Number}
         */
        function getComputedStyle(element, prop) {
            if (element.currentStyle) {
                return element.currentStyle[prop];
            } else if (window.getComputedStyle) {
                return window.getComputedStyle(element, null).getPropertyValue(prop);
            } else {
                return element.style[prop];
            }
        }

        /**
         *
         * @param {HTMLElement} element
         * @param {Function}    resized
         */
        function attachResizeEvent(element, resized) {
            if (!element.resizedAttached) {
                element.resizedAttached = new EventQueue();
                element.resizedAttached.add(resized);
            } else if (element.resizedAttached) {
                element.resizedAttached.add(resized);
                return;
            }

            element.resizeSensor = document.createElement('div');
            element.resizeSensor.className = 'resize-sensor';
            var style = 'position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: hidden; z-index: -1; visibility: hidden;';
            var styleChild = 'position: absolute; left: 0; top: 0; transition: 0s;';

            element.resizeSensor.style.cssText = style;
            element.resizeSensor.innerHTML =
                '<div class="resize-sensor-expand" style="' + style + '">' +
                    '<div style="' + styleChild + '"></div>' +
                '</div>' +
                '<div class="resize-sensor-shrink" style="' + style + '">' +
                    '<div style="' + styleChild + ' width: 200%; height: 200%"></div>' +
                '</div>';
            element.appendChild(element.resizeSensor);

            if (!{fixed: 1, absolute: 1}[getComputedStyle(element, 'position')]) {
                element.style.position = 'relative';
            }

            var expand = element.resizeSensor.childNodes[0];
            var expandChild = expand.childNodes[0];
            var shrink = element.resizeSensor.childNodes[1];
            var shrinkChild = shrink.childNodes[0];

            var lastWidth, lastHeight;

            var reset = function() {
                expandChild.style.width = expand.offsetWidth + 10 + 'px';
                expandChild.style.height = expand.offsetHeight + 10 + 'px';
                expand.scrollLeft = expand.scrollWidth;
                expand.scrollTop = expand.scrollHeight;
                shrink.scrollLeft = shrink.scrollWidth;
                shrink.scrollTop = shrink.scrollHeight;
                lastWidth = element.offsetWidth;
                lastHeight = element.offsetHeight;
            };

            reset();

            var changed = function() {
                if (element.resizedAttached) {
                    element.resizedAttached.call();
                }
            };

            var addEvent = function(el, name, cb) {
                if (el.attachEvent) {
                    el.attachEvent('on' + name, cb);
                } else {
                    el.addEventListener(name, cb);
                }
            };

            var onScroll = function() {
              if (element.offsetWidth != lastWidth || element.offsetHeight != lastHeight) {
                  changed();
              }
              reset();
            };

            addEvent(expand, 'scroll', onScroll);
            addEvent(shrink, 'scroll', onScroll);
        }

        var elementType = Object.prototype.toString.call(element);
        var isCollectionTyped = ('[object Array]' === elementType
            || ('[object NodeList]' === elementType)
            || ('[object HTMLCollection]' === elementType)
            || ('undefined' !== typeof jQuery && element instanceof jQuery) //jquery
            || ('undefined' !== typeof Elements && element instanceof Elements) //mootools
        );

        if (isCollectionTyped) {
            var i = 0, j = element.length;
            for (; i < j; i++) {
                attachResizeEvent(element[i], callback);
            }
        } else {
            attachResizeEvent(element, callback);
        }

        this.detach = function() {
            if (isCollectionTyped) {
                var i = 0, j = element.length;
                for (; i < j; i++) {
                    ResizeSensor.detach(element[i]);
                }
            } else {
                ResizeSensor.detach(element);
            }
        };
    };

    ResizeSensor.detach = function(element) {
        if (element.resizeSensor) {
            element.removeChild(element.resizeSensor);
            delete element.resizeSensor;
            delete element.resizedAttached;
        }
    };

    // make available to common module loader
    if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
        module.exports = ResizeSensor;
    }
    else {
        window.ResizeSensor = ResizeSensor;
    }

})();


/*!
 * Theia Sticky Sidebar v1.7.0
 * https://github.com/WeCodePixels/theia-sticky-sidebar
 *
 * Glues your website's sidebars, making them permanently visible while scrolling.
 *
 * Copyright 2013-2016 WeCodePixels and other contributors
 * Released under the MIT license
 */

(function ($) {
    $.fn.theiaStickySidebar = function (options) {
        var defaults = {
            'containerSelector': '',
            'additionalMarginTop': 0,
            'additionalMarginBottom': 0,
            'updateSidebarHeight': true,
            'minWidth': 0,
            'disableOnResponsiveLayouts': true,
            'sidebarBehavior': 'modern',
            'defaultPosition': 'relative',
            'namespace': 'TSS'
        };
        options = $.extend(defaults, options);

        // Validate options
        options.additionalMarginTop = parseInt(options.additionalMarginTop) || 0;
        options.additionalMarginBottom = parseInt(options.additionalMarginBottom) || 0;

        tryInitOrHookIntoEvents(options, this);

        // Try doing init, otherwise hook into window.resize and document.scroll and try again then.
        function tryInitOrHookIntoEvents(options, $that) {
            var success = tryInit(options, $that);

            if (!success) {
                console.log('TSS: Body width smaller than options.minWidth. Init is delayed.');

                $(document).on('scroll.' + options.namespace, function (options, $that) {
                    return function (evt) {
                        var success = tryInit(options, $that);

                        if (success) {
                            $(this).unbind(evt);
                        }
                    };
                }(options, $that));
                $(window).on('resize.' + options.namespace, function (options, $that) {
                    return function (evt) {
                        var success = tryInit(options, $that);

                        if (success) {
                            $(this).unbind(evt);
                        }
                    };
                }(options, $that))
            }
        }

        // Try doing init if proper conditions are met.
        function tryInit(options, $that) {
            if (options.initialized === true) {
                return true;
            }

            if ($('body').width() < options.minWidth) {
                return false;
            }

            init(options, $that);

            return true;
        }

        // Init the sticky sidebar(s).
        function init(options, $that) {
            options.initialized = true;

            // Add CSS
            var existingStylesheet = $('#theia-sticky-sidebar-stylesheet-' + options.namespace);
            if (existingStylesheet.length === 0) {
                $('head').append($('<style id="theia-sticky-sidebar-stylesheet-' + options.namespace + '">.theiaStickySidebar:after {content: ""; display: table; clear: both;}</style>'));
            }

            $that.each(function () {
                var o = {};

                o.sidebar = $(this);

                // Save options
                o.options = options || {};

                // Get container
                o.container = $(o.options.containerSelector);
                if (o.container.length == 0) {
                    o.container = o.sidebar.parent();
                }

                // Create sticky sidebar
                o.sidebar.parents().css('-webkit-transform', 'none'); // Fix for WebKit bug - https://code.google.com/p/chromium/issues/detail?id=20574
                o.sidebar.css({
                    'position': o.options.defaultPosition,
                    'overflow': 'visible',
                    // The "box-sizing" must be set to "content-box" because we set a fixed height to this element when the sticky sidebar has a fixed position.
                    '-webkit-box-sizing': 'border-box',
                    '-moz-box-sizing': 'border-box',
                    'box-sizing': 'border-box'
                });

                // Get the sticky sidebar element. If none has been found, then create one.
                o.stickySidebar = o.sidebar.find('.theiaStickySidebar');
                if (o.stickySidebar.length == 0) {
                    // Remove <script> tags, otherwise they will be run again when added to the stickySidebar.
                    var javaScriptMIMETypes = /(?:text|application)\/(?:x-)?(?:javascript|ecmascript)/i;
                    o.sidebar.find('script').filter(function (index, script) {
                        return script.type.length === 0 || script.type.match(javaScriptMIMETypes);
                    }).remove();

                    o.stickySidebar = $('<div>').addClass('theiaStickySidebar').append(o.sidebar.children());
                    o.sidebar.append(o.stickySidebar);
                }

                // Get existing top and bottom margins and paddings
                o.marginBottom = parseInt(o.sidebar.css('margin-bottom'));
                o.paddingTop = parseInt(o.sidebar.css('padding-top'));
                o.paddingBottom = parseInt(o.sidebar.css('padding-bottom'));

                // Add a temporary padding rule to check for collapsable margins.
                var collapsedTopHeight = o.stickySidebar.offset().top;
                var collapsedBottomHeight = o.stickySidebar.outerHeight();
                o.stickySidebar.css('padding-top', 1);
                o.stickySidebar.css('padding-bottom', 1);
                collapsedTopHeight -= o.stickySidebar.offset().top;
                collapsedBottomHeight = o.stickySidebar.outerHeight() - collapsedBottomHeight - collapsedTopHeight;
                if (collapsedTopHeight == 0) {
                    o.stickySidebar.css('padding-top', 0);
                    o.stickySidebarPaddingTop = 0;
                }
                else {
                    o.stickySidebarPaddingTop = 1;
                }

                if (collapsedBottomHeight == 0) {
                    o.stickySidebar.css('padding-bottom', 0);
                    o.stickySidebarPaddingBottom = 0;
                }
                else {
                    o.stickySidebarPaddingBottom = 1;
                }

                // We use this to know whether the user is scrolling up or down.
                o.previousScrollTop = null;

                // Scroll top (value) when the sidebar has fixed position.
                o.fixedScrollTop = 0;

                // Set sidebar to default values.
                resetSidebar();

                o.onScroll = function (o) {
                    // Stop if the sidebar isn't visible.
                    if (!o.stickySidebar.is(":visible")) {
                        return;
                    }

                    // Stop if the window is too small.
                    if ($('body').width() < o.options.minWidth) {
                        resetSidebar();
                        return;
                    }

                    // Stop if the sidebar width is larger than the container width (e.g. the theme is responsive and the sidebar is now below the content)
                    if (o.options.disableOnResponsiveLayouts) {
                        var sidebarWidth = o.sidebar.outerWidth(o.sidebar.css('float') == 'none');

                        if (sidebarWidth + 50 > o.container.width()) {
                            resetSidebar();
                            return;
                        }
                    }

                    var scrollTop = $(document).scrollTop();
                    var position = 'static';

                    // If the user has scrolled down enough for the sidebar to be clipped at the top, then we can consider changing its position.
                    if (scrollTop >= o.sidebar.offset().top + (o.paddingTop - o.options.additionalMarginTop)) {
                        // The top and bottom offsets, used in various calculations.
                        var offsetTop = o.paddingTop + options.additionalMarginTop;
                        var offsetBottom = o.paddingBottom + o.marginBottom + options.additionalMarginBottom;

                        // All top and bottom positions are relative to the window, not to the parent elemnts.
                        var containerTop = o.sidebar.offset().top;
                        var containerBottom = o.sidebar.offset().top + getClearedHeight(o.container);

                        // The top and bottom offsets relative to the window screen top (zero) and bottom (window height).
                        var windowOffsetTop = 0 + options.additionalMarginTop;
                        var windowOffsetBottom;

                        var sidebarSmallerThanWindow = (o.stickySidebar.outerHeight() + offsetTop + offsetBottom) < $(window).height();
                        if (sidebarSmallerThanWindow) {
                            windowOffsetBottom = windowOffsetTop + o.stickySidebar.outerHeight();
                        }
                        else {
                            windowOffsetBottom = $(window).height() - o.marginBottom - o.paddingBottom - options.additionalMarginBottom;
                        }

                        var staticLimitTop = containerTop - scrollTop + o.paddingTop;
                        var staticLimitBottom = containerBottom - scrollTop - o.paddingBottom - o.marginBottom;

                        var top = o.stickySidebar.offset().top - scrollTop;
                        var scrollTopDiff = o.previousScrollTop - scrollTop;

                        // If the sidebar position is fixed, then it won't move up or down by itself. So, we manually adjust the top coordinate.
                        if (o.stickySidebar.css('position') == 'fixed') {
                            if (o.options.sidebarBehavior == 'modern') {
                                top += scrollTopDiff;
                            }
                        }

                        if (o.options.sidebarBehavior == 'stick-to-top') {
                            top = options.additionalMarginTop;
                        }

                        if (o.options.sidebarBehavior == 'stick-to-bottom') {
                            top = windowOffsetBottom - o.stickySidebar.outerHeight();
                        }

                        if (scrollTopDiff > 0) { // If the user is scrolling up.
                            top = Math.min(top, windowOffsetTop);
                        }
                        else { // If the user is scrolling down.
                            top = Math.max(top, windowOffsetBottom - o.stickySidebar.outerHeight());
                        }

                        top = Math.max(top, staticLimitTop);

                        top = Math.min(top, staticLimitBottom - o.stickySidebar.outerHeight());

                        // If the sidebar is the same height as the container, we won't use fixed positioning.
                        var sidebarSameHeightAsContainer = o.container.height() == o.stickySidebar.outerHeight();

                        if (!sidebarSameHeightAsContainer && top == windowOffsetTop) {
                            position = 'fixed';
                        }
                        else if (!sidebarSameHeightAsContainer && top == windowOffsetBottom - o.stickySidebar.outerHeight()) {
                            position = 'fixed';
                        }
                        else if (scrollTop + top - o.sidebar.offset().top - o.paddingTop <= options.additionalMarginTop) {
                            // Stuck to the top of the page. No special behavior.
                            position = 'static';
                        }
                        else {
                            // Stuck to the bottom of the page.
                            position = 'absolute';
                        }
                    }

                    /*
                     * Performance notice: It's OK to set these CSS values at each resize/scroll, even if they don't change.
                     * It's way slower to first check if the values have changed.
                     */
                    if (position == 'fixed') {
                        var scrollLeft = $(document).scrollLeft();

                        o.stickySidebar.css({
                            'position': 'fixed',
                            'width': getWidthForObject(o.stickySidebar) + 'px',
                            'transform': 'translateY(' + top + 'px)',
                            'left': (o.sidebar.offset().left + parseInt(o.sidebar.css('padding-left')) - scrollLeft) + 'px',
                            'top': '0px'
                        });
                    }
                    else if (position == 'absolute') {
                        var css = {};

                        if (o.stickySidebar.css('position') != 'absolute') {
                            css.position = 'absolute';
                            css.transform = 'translateY(' + (scrollTop + top - o.sidebar.offset().top - o.stickySidebarPaddingTop - o.stickySidebarPaddingBottom) + 'px)';
                            css.top = '0px';
                        }

                        css.width = getWidthForObject(o.stickySidebar) + 'px';
                        css.left = '';

                        o.stickySidebar.css(css);
                    }
                    else if (position == 'static') {
                        resetSidebar();
                    }

                    if (position != 'static') {
                        if (o.options.updateSidebarHeight == true) {
                            o.sidebar.css({
                                'min-height': o.stickySidebar.outerHeight() + o.stickySidebar.offset().top - o.sidebar.offset().top + o.paddingBottom
                            });
                        }
                    }

                    o.previousScrollTop = scrollTop;
                };

                // Initialize the sidebar's position.
                o.onScroll(o);

                // Recalculate the sidebar's position on every scroll and resize.
                $(document).on('scroll.' + o.options.namespace, function (o) {
                    return function () {
                        o.onScroll(o);
                    };
                }(o));
                $(window).on('resize.' + o.options.namespace, function (o) {
                    return function () {
                        o.stickySidebar.css({'position': 'static'});
                        o.onScroll(o);
                    };
                }(o));

                // Recalculate the sidebar's position every time the sidebar changes its size.
                if (typeof ResizeSensor !== 'undefined') {
                    new ResizeSensor(o.stickySidebar[0], function (o) {
                        return function () {
                            o.onScroll(o);
                        };
                    }(o));
                }

                // Reset the sidebar to its default state
                function resetSidebar() {
                    o.fixedScrollTop = 0;
                    o.sidebar.css({
                        'min-height': '1px'
                    });
                    o.stickySidebar.css({
                        'position': 'static',
                        'width': '',
                        'transform': 'none'
                    });
                }

                // Get the height of a div as if its floated children were cleared. Note that this function fails if the floats are more than one level deep.
                function getClearedHeight(e) {
                    var height = e.height();

                    e.children().each(function () {
                        height = Math.max(height, $(this).height());
                    });

                    return height;
                }
            });
        }

        function getWidthForObject(object) {
            var width;

            try {
                width = object[0].getBoundingClientRect().width;
            }
            catch (err) {
            }

            if (typeof width === "undefined") {
                width = object.width();
            }

            return width;
        }

        return this;
    }
})(jQuery);

jQuery(document).ready(function() {
    jQuery('#fupi_page_nav, #fupi_side_menu').theiaStickySidebar({
        additionalMarginTop: 50,
        additionalMarginBottom : 50,
    });
});
