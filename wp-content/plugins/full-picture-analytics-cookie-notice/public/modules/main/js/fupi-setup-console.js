(()=>{
    'use strict';
    
    let console_toggle_btns = FP.findAll('.fupi_console_toggle_btn'),
        tracking_console = FP.findID('fupi_console');
    
    if ( ! tracking_console || console_toggle_btns.length == 0 ) return;

    let fixed_btn = FP.findID('fupi_console_fixed_btn'),
        open_intro_btn = FP.findID('fupi_open_intro_btn');
    
    // TOGGLE CONSOLE
    console_toggle_btns.forEach( btn => {
        btn.addEventListener('click', ()=>{
            tracking_console.classList.toggle('fupi_hidden');
            
            if ( tracking_console.classList.contains('fupi_hidden') ) {
                fixed_btn.classList.remove('fupi_hidden');
            } else {
                fixed_btn.classList.add('fupi_hidden');
            }
        });
    });

    // CHECK IF TRACKABLE
    let is_trackable = FP.readCookie('fp_track_me') == 1,
        reset_btns  = FP.findAll('.fupi_test_reset_btn'),
        end_test_btn = FP.findID('fupi_end_test'),
        show_banner_btn = FP.findID('fupi_show_banner');

    // IF IS TRACKABLE CHANGE CONSOLE CONTENT

    if ( is_trackable ) {
        tracking_console.classList.add('fupi_trackable');
    } else {
        tracking_console.classList.add('fupi_not_trackable');
    }

    // ENABLE TRACKING ON BTN CLICK
    
    if ( reset_btns ) reset_btns.forEach( btn => {
        btn.addEventListener('click', ()=>{
            FP.setCookie('fp_track_me', 1, 0, 360 ); // enable tests
            FP.deleteCookie('fp_cookie');
            FP.deleteCookie('fp_country');
            window.location.reload(); // reload window
        });
    });

    // SHOW BANNER IF NOT ALREADY VISIBLE

    if ( show_banner_btn ) {
        show_banner_btn.addEventListener('click', ()=>{

            let consent_banner = FP.findID('fupi_cookie_notice');

            if ( consent_banner && ! consent_banner.classList.contains('fupi_fadeInUp') ) {

                // click hidden button
                let hidden_show_banner_btn = FP.findID( 'fupi_hidden_show_banner_btn' );
                hidden_show_banner_btn.click();
            }
        });
    }

    // DISABLE TRACKING ON BTN CLICK
    
    end_test_btn.addEventListener('click', ()=>{
        FP.deleteCookie('fp_track_me' ); // disable tests
        window.location.reload(); // reload window
    });

    // TOGGLE INTRO

    open_intro_btn.addEventListener('click', ()=>{
        let intro = FP.findID('fupi_console_intro');
        intro.classList.toggle('fupi_hidden');
    });
    
})();