(()=>{

    // Used in Custom Scripts module
    // Mark sections which contain scripts that can no longer trigger because they were triggered by custom triggers that have been removed

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

    // CUSTOM EVENTS BUILDER - clear sections that contain not existing (expired) trigger

    let builders = FP.findAll('.fupi_events_builder');
    
    builders.forEach( builder => {
        
        let builder_sections = FP.findAll('.fupi_r3_section', builder);

        builder_sections.forEach( section => {
            
            let atrig_select = FP.findFirst( '.fupi_field_type_atrig_select select', section );
    
            if ( atrig_select && ! atrig_select.value ){
                let minus_button = FP.findFirst( '.fupi_btn_remove', section );
                if ( minus_button ) minus_button.click();
            }
        } );
    })


})();

(()=>{

    // CUSTOM META BUILDER - clear sections that contain not existing (expired) custom meta

    let builder_sections = FP.findAll('.fupi_metadata_tracker .fupi_r3_section');

    builder_sections.forEach( section => {
        
        let metadata_select = FP.findFirst( '.fupi_field_type_custom_meta_select select', section );

        if ( metadata_select && ! metadata_select.value ){
            let minus_button = FP.findFirst( '.fupi_btn_remove', section );
            if ( minus_button ) minus_button.click();
        }
    } );

})();