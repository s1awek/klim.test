document.addEventListener( 'DOMContentLoaded', () => {

    // BASIC HELPERS
    // loaded here since we don't load the full library in the customizer

    var FP = FP || {};

    FP.nl2Arr = function (nl) {
        if (nl) {
            return [].slice.call(nl);
        } else {
            return false;
        }
    };

    FP.findAll = function (e, c) {
        c = c || document;
        return FP.nl2Arr(c.querySelectorAll(e));
    };

    FP.findFirst = function (e, c) {
        c = c || document;
        return c.querySelector(e);
    };

    FP.findID = function (e, c) {
        c = c || document;
        return c.getElementById(e);
    };

    // ENABLE CUSTOM CONTROLS

    function enable_custom_controls() {

        // MULTI CHECKBOX
        var customMultiCheckboxes = FP.findAll( '.customize-control-multi_checkbox input[type="checkbox"]' );

        if ( customMultiCheckboxes ) {
            customMultiCheckboxes.forEach( checkbox => {

                checkbox.addEventListener( 'change', e => {

                    var control_wrapper = e.target.closest( '.customize-control' );

                    if ( control_wrapper ) {

                        var checked_fields = FP.findAll( 'input[type="checkbox"]:checked', control_wrapper );
                        
                        if ( checked_fields ) {
                            
                            var values_a = checked_fields.map( field => field.value ),
                                hidden_field = FP.findFirst( 'input[type="hidden"]', control_wrapper ),
                                change_evt = new Event("change", {"bubbles":false, "cancelable":true});
                        
                            hidden_field.value = values_a.join(',');
                            hidden_field.dispatchEvent(change_evt);
                        }
                    }
                });
            })
        }
    }

    function bind_customizer_panel_toggles(){

        // find consent banner section
        wp.customize.panel.each( function ( panel ) {
            if( panel.id == 'fupi_notice' ){
                // send open state to preview if the section is open at the moment
                if ( panel.expanded.get() ) { wp.customize.previewer.send( 'fupi_open_notice', true ); }
                // bind sending section state to previewer to future opening/closing events
                panel.expanded.bind( function( expanded ) {
                    wp.customize.previewer.send( 'fupi_open_notice', expanded );
                });
            }
        });

        // find consent banner section
        wp.customize.section.each( function ( section ) {
            if( section.id == 'fupi_notice_toggler' ){
                // send open state to preview if the section is open at the moment
                if ( section.expanded.get() ) { wp.customize.previewer.send( 'fupi_open_toggler', true ); }
                // bind sending section state to previewer to future opening/closing events
                section.expanded.bind( function( expanded ) {
                    wp.customize.previewer.send( 'fupi_open_toggler', expanded );
                });
            }
        });
    }

    // visually disable controls depending on the chosen mode - optin/out or notify
    function conditionally_disable_controls(preview_version){

        // DISABLE controls when "preview" is enabled
        [
            'fupi_cookie_notice-position',
            'fupi_cookie_notice-overlay',
            'fupi_cookie_notice-scroll_lock',
            'fupi_cookie_notice-blur_page',
            'fupi_cookie_notice-hide',
            'fupi_cookie_notice-show',
            'fupi_cookie_notice-switches_on',
            'fupi_cookie_notice-optin_switches',
            'fupi_cookie_notice-btn_config',
            'fupi_cookie_notice-btn_class',
            'fupi_notice_btn_color',
            'fupi_notice_btn_color_hover',
            'fupi_notice_btn_txt_color',
            'fupi_notice_btn_txt_color_hover',
            'fupi_notice_switch_color',
            'fupi_notice_necessary_switch_color',
            'fupi_cookie_notice-agree_text',
            'fupi_cookie_notice-agree_to_selected_text',
            'fupi_cookie_notice-decline_text',
            'fupi_cookie_notice-cookie_settings_text',
            'fupi_cookie_notice-return_text',
            'fupi_cookie_notice-necess_headline_text',
            'fupi_cookie_notice-necess_text',
            'fupi_cookie_notice-stats_headline_text',
            'fupi_cookie_notice-stats_text',
            'fupi_cookie_notice-pers_headline_text',
            'fupi_cookie_notice-pers_text',
            'fupi_cookie_notice-marketing_headline_text',
            'fupi_cookie_notice-marketing_text',
        ].forEach(
            el => {
                let found_el = FP.findID( 'customize-control-' + el );
                if ( found_el ) {
                    if ( preview_version == 'notify' ) {
                        if ( el !== 'fupi_cookie_notice-show' ) {
                            // simply disable full controls
                            found_el.classList.add('fupi_faux_disable');
                        } else {
                            // only disable the first option
                            FP.findFirst('li', found_el).classList.add('fupi_faux_disable');
                        }
                    } else {
                        if ( el !== 'fupi_cookie_notice-show' ) {
                            // simply disable full controls
                            found_el.classList.remove('fupi_faux_disable');
                        } else {
                            // only disable the first option
                            FP.findFirst('li', found_el).classList.remove('fupi_faux_disable');
                        }
                    };
                }
            }
        );

        // ENABLE controls when "preview" is enabled
        [
            'fupi_cookie_notice-position_inform',
            'fupi_cookie_notice-ok_text',
        ].forEach( 
            el => {
                let found_el = FP.findID( 'customize-control-' + el );
                if ( found_el ) {
                    if ( preview_version == 'notify' ) {
                        found_el.classList.remove('fupi_faux_disable');
                    } else {
                        found_el.classList.add('fupi_faux_disable');
                    };
                }
            }
        );
    }

    function update_faux_previewers(preview_mode){
        if ( preview_mode == 'notify' ) {
            document.body.classList.add( 'fupi_preview_notify' );
            document.body.classList.remove( 'fupi_preview_opt_in_out' );
        } else {
            document.body.classList.add( 'fupi_preview_opt_in_out' );
            document.body.classList.remove( 'fupi_preview_notify' );
        }
    }

    function set_value_in_real_previewer(preview_version){
        FP.findFirst('#customize-control-fupi_cookie_notice-active_preview input[value="' + preview_version + '"]+label').click();
    };

    function set_banner_type_preview(){
        
        let preview_wrapper = FP.findID('customize-control-fupi_cookie_notice-active_preview');

        // the user can switch previews if a preview exists (in automatic modes in Pro) and is visible
        if ( preview_wrapper && preview_wrapper.style.display != 'none' ){

            // get marked preview
            let preview_version = FP.findFirst('input:checked', preview_wrapper).value;
            
            conditionally_disable_controls( preview_version );
            update_faux_previewers( preview_version );

            // listen to changes on the real preview selector
            let choices = FP.findAll('#customize-control-fupi_cookie_notice-active_preview input');
                
            choices.forEach( choice => {
                choice.addEventListener( 'change', ()=>{
                    conditionally_disable_controls(choice.value);
                    update_faux_previewers(choice.value);
                })
            })
            
            // listen to clicks on faux preview selectors
            let previewChoices = FP.findAll('.fupi_preview_img');
                
            previewChoices.forEach( choice => {
                choice.addEventListener( 'click', ()=>{
                    let preview_mode = choice.classList.contains('fupi_opt_in_out_preview') ? 'opt_in_out' : 'notify';
                    set_value_in_real_previewer(preview_mode);
                })
            })
        
        // if preview is hidden or does not exist
        // (the user can't switch previews - here we just want to show the preview that matches the available controls)
        } else {

            // get preview set in a variable (in a faux preview switcher)
            let current_mode_el = FP.findFirst('.fupi_notice_mode'),
                preview_version = current_mode_el.dataset.preview_ver;

            // change the preview
            set_value_in_real_previewer(preview_version);
        }
    }

    function toggle_bg_overlay_control(){
        wp.customize.control('fupi_cookie_notice[position]', control => { // value saved as an option
			control.setting.bind( value => {
				switch (value) {
					case 'popup':
						wp.customize.control('fupi_cookie_notice[overlay]').activate(); // value saved as an option
					break;
					default:
                        wp.customize.control('fupi_cookie_notice[overlay]').deactivate();
					break;
				}
			});
		});
    }

    function toggle_border_color_control(){
        wp.customize.control('fupi_cookie_notice_border', control => { // value saved as a theme mod
			control.setting.bind( value => {
				switch (value) {
					case 'thin_border':
                    case 'wide_border':
						wp.customize.control('fupi_notice_border_color').activate(); // value saved as a theme mod
					break;
					default:
                        wp.customize.control('fupi_notice_border_color').deactivate();
					break;
				}
			});
		});
    }

    function toggle_optional_fields(){

        // When HIDE elements are selected
        wp.customize.control('fupi_cookie_notice[hide]', control => { // value saved as an option
			control.setting.bind( value => {
				
                let to_hide = [];

                // hide "settings btn" if "decline button" is checked
                if ( value.includes('decline_btn') ){
                    // settings_btn.closest('li').classList.add('fupi_faux_disable');
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="settings_btn"]').closest('li').slideUp();
                } else {
                    // settings_btn.closest('li').classList.remove('fupi_faux_disable');;
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="settings_btn"]').closest('li').slideDown();
                };

                // hide many elements if "settings button" is checked (including the setting for hiding the "decline btn")
                if ( value.includes('settings_btn') ){

                    // hide els by sliding up
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="decline_btn"]').closest('li').slideUp();
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="pers"]').closest('li').slideUp();
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="market"]').closest('li').slideUp();
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="stats"]').closest('li').slideUp();

                    // visually disable els
                    to_hide = [
                        'switches_on',
                        'optin_switches',
                        'agree_to_selected_text',
                        'cookie_settings_text',
                        'return_text',
                        'necess_headline_text',
                        'necess_text',
                        'stats_headline_text',
                        'stats_text',
                        'pers_headline_text',
                        'pers_text',
                        'marketing_headline_text',
                        'marketing_text'
                    ];

                } else {

                    // show els by sliding down
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="decline_btn"]').closest('li').slideDown();
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="pers"]').closest('li').slideDown();
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="market"]').closest('li').slideDown();
                    jQuery('#customize-control-fupi_cookie_notice-hide input[value="stats"]').closest('li').slideDown();
                    
                    // visually enable els
                    if ( value.includes('stats') ){
                        to_hide.push('stats_headline_text');
                        to_hide.push('stats_text');
                    }

                    if ( value.includes('market') ){
                        to_hide.push('marketing_headline_text');
                        to_hide.push('marketing_text');
                    }

                    if ( value.includes('pers') ){
                        to_hide.push('pers_headline_text');
                        to_hide.push('pers_text');
                    }
                }

                [
                    'switches_on',
                    'optin_switches',
                    'agree_to_selected_text',
                    'cookie_settings_text',
                    'return_text',
                    'necess_headline_text',
                    'necess_text',
                    'stats_headline_text',
                    'stats_text',
                    'pers_headline_text',
                    'pers_text',
                    'marketing_headline_text',
                    'marketing_text'
                ].forEach( field => {

                    if ( to_hide.includes( field ) ) {
                        wp.customize.control('fupi_cookie_notice[' + field + ']').deactivate(); // value saved as an option
                    } else {
                        wp.customize.control('fupi_cookie_notice[' + field + ']').activate(); // value saved as an option
                    }

                } );
			});
		});

        // When SHOW elements are selected
        wp.customize.control('fupi_cookie_notice[show]', control => { // value saved as an option
			control.setting.bind( value => {

                // show "stats_only_text" field if "stats_only_btn" field is checked
                if ( value.includes('stats_only_btn') ){
                    wp.customize.control('fupi_cookie_notice[stats_only_text]').activate();
                } else {
                    wp.customize.control('fupi_cookie_notice[stats_only_text]').deactivate();
                };
			});
		});

        // Toggle visibility of the Toggler's fields dependin on whether toggler is enabled or not
        wp.customize.control('fupi_cookie_notice[enable_toggle_btn]', control => {
			control.setting.bind( enabled => {

                // show "stats_only_text" field if "stats_only_btn" field is checked
                if ( enabled ) {
                    wp.customize.control('fupi_custom_toggler_img').activate();
                    wp.customize.control('fupi_toggler_bg_color').activate();
                } else {
                    wp.customize.control('fupi_custom_toggler_img').deactivate();
                    wp.customize.control('fupi_toggler_bg_color').deactivate();
                };
			});
		});

    }

    function toggle_elements_in_hideelements_control(){
        
        let decline_btn = FP.findFirst('#customize-control-fupi_cookie_notice-hide input[value="decline_btn"]'),
            settings_btn = FP.findFirst('#customize-control-fupi_cookie_notice-hide input[value="settings_btn"]'),
            stats_sect = FP.findFirst('#customize-control-fupi_cookie_notice-hide input[value="pers"]'),
            market_sect = FP.findFirst('#customize-control-fupi_cookie_notice-hide input[value="market"]'),
            pers_sect = FP.findFirst('#customize-control-fupi_cookie_notice-hide input[value="stats"]');
        
        if ( decline_btn.checked ) {
            settings_btn.closest('li').style.display = "none";
        } else {
            settings_btn.closest('li').style.display = "block";
        };

        if ( settings_btn.checked ) {
            decline_btn.closest('li').style.display = "none";
            stats_sect.closest('li').style.display = "none";
            market_sect.closest('li').style.display = "none";
            pers_sect.closest('li').style.display = "none";
        } else {
            decline_btn.closest('li').style.display = "block";
            stats_sect.closest('li').style.display = "block";
            market_sect.closest('li').style.display = "block";
            pers_sect.closest('li').style.display = "block";
        };

    }

    // wait for the preview iframe to load
    wp.customize.bind('ready', ()=>{
        wp.customize.previewer.bind( 'ready', function() {
            
            bind_customizer_panel_toggles(); // shows and hides the notice when "consent banner" settings panel is opened
            enable_custom_controls();
            set_banner_type_preview(); // optin/out or "notify"
            toggle_elements_in_hideelements_control();
            toggle_bg_overlay_control();
            toggle_border_color_control();
            toggle_optional_fields();
        } );

    })

} );
