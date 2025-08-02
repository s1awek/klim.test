<?php

$info               = $this->get_module_info( 'cook' );
$notice_opts        = get_option('fupi_cookie_notice');

$this->data['cook'] = [ 
    'module_name' => 'Consent banner',
    'setup' => [
        [ 'ok', 'Google Consent Mode v2 and Microsoft UET Consent Mode are activate and work according to the consent banner settings' ]
    ],
];

$default_geo_texts = [
    [ 'ok', 'Consent banner uses strict, automatic setup mode - it chooses the correct mode of work depending on visitor\'s location. Strict mode is intended for websites that use visitor\'s data for marketing purposes and / or collect sensitive information.'],
    [ 'ok', 'Opt-in mode is used for visitors from: AT, BE, BG, CY, CZ, DE, DK, ES, EE, FI, FR, GB, GR, HR, HU, IE, IS, IT, LI, LT, LU, LV, MT, NG, NL, NO, PL, PT, RO, SK, SI, SE, MX, GP, GF, MQ, YT, RE, MF, IC, AR, BR, TR, SG, ZA, AU, CA, CL, CN, CO, HK, IN, ID, JP, MA, RU, KR, CH, TW, TH.' ],
    [ 'ok', 'Opt-out mode is used for visitors from: US (CA), KZ.'],
    [ 'ok', 'Visitors from other countries are notified that they are tracked.' ],
    [ 'ok', 'Fallback mode when no location is found: Opt-in mode.' ],
];

// Default texts if fupi_cook option has not been saved yet

if ( empty( $this->cook ) ){

    $this->data['cook']['setup'][] = [ 'ok', 'Consent banner is set to work in Opt-in mode - it enables tracking tools and loads embedded content according to visitors tracking choices.' ];
    
    if ( ! empty( $this->tools['geo'] ) ) $this->data['cook']['setup'] = array_merge( $this->data['cook']['setup'], $default_geo_texts );

    $this->data['cook']['setup'][] = [ 'ok', 'Visitors are asked for consent again, when the privacy policy text changes or when tracking modules are enabled.' ];

// When fupi_cook has settings

} else {
    
    // WHEN CONSENT BANNER IS ENABLED, HAS SETTINGS AND USES GEO

    if ( ! empty( $this->tools['geo'] ) ) {

        // check if saved after enabling geo
        $use_default_geo = empty( $this->cook['mode'] );

        if ( $use_default_geo ) {
            $this->data['cook']['setup'] = array_merge( $this->data['cook']['setup'], $default_geo_texts );
        } else {

            switch ( $this->cook['mode'] ) {
                
                case 'optin':
                    $this->data['cook']['setup'][] = [ 'ok', 'Consent banner is set to work in Opt-in mode - it enables tracking tools and loads embedded content according to visitors tracking choices.' ];
                break;

                case 'optout':
                    $this->data['cook']['setup'][] = [ 'alert', 'Consent banner is set to work in Opt-out mode - it enables tracking tools and loads embedded content when visitors enter the website but lets them decline tracking.' ];
                break;

                case 'notify':
                    $this->data['cook']['setup'][] = [ 'alert', 'Consent banner is set to track all visitors and only notify them that they are tracked. They cannot decline.' ];
                break;
                
                case 'auto_strict':
                    array_pop( $default_geo_texts ); // remove last element of array (text about fallback)
                    $this->data['cook']['setup'] = array_merge( $this->data['cook']['setup'], $default_geo_texts );
                break;
                
                case 'auto_lax':
                    $this->data['cook']['setup'] = [
                        [ 'ok', 'Consent banner uses lax, automatic setup mode - it chooses the correct mode of work depending on visitor\'s location. Lax mode is intended for websites that neither use visitor\'s data for marketing purposes nor collect sensitive information.' ],
                        [ 'ok', 'Opt-in mode is used for visitors from: AT, BE, BG, CY, CZ, DE, DK, ES, EE, FI, FR, GB, GR, HR, HU, IE, IS, IT, LI, LT, LU, LV, MT, NG, NL, NO, PL, PT, RO, SK, SI, SE, GP, GF, MQ, YT, RE, MF, IC, TR, ZA, AG, BR, CL, CN, CO, ID, MA, RU, KR, TW, TH, CH.' ],
                        [ 'ok', 'Opt-out mode is used for visitors from: US (CA), JP, CA, IN, MX, SG.' ],
                        [ 'ok', 'Visitors from KZ, PH are notified that they are tracked.' ],
                        [ 'ok', 'Visitors from other countries are tracked without notification.' ],
                    ];
                break;

                case 'manual':
                    $this->data['cook']['setup'][] = [ 'warning', 'Consent banner changes the mode of work depending on visitor\'s location. The list of locations was set manually by the user.' ];

                    // Opt-in
                    
                    if ( ! empty ( $this->cook['optin'] ) && $this->cook['optin'] == 'all' ) {
                        $this->data['cook']['tracked_extra_data'][] = 'Opt-in mode is used for visitors from all countries.';
                    }

                    if ( ! empty ( $this->cook['optin'] ) && $this->cook['optin'] == 'none' ) {
                        $this->data['cook']['setup'][] = [ 'alert', 'Opt-in mode is not used for visitors from any country.' ];
                    }

                    if ( ! empty ( $this->cook['optin'] ) && $this->cook['optin'] == 'specific' ) {
                        if ( isset ( $this->cook['optin_countries'] ) ){
                            $this->data['cook']['setup'][] = [ 'warning', 'Opt-in mode is used for visitors from: ' . $this->cook['optin_countries'] . '.' ];
                        } else {
                            $this->data['cook']['setup'][] = [ 'alert', 'Opt-in mode is not used for visitors from any country.' ];
                        }
                    }

                    // Opt-out
                    if ( ! empty ( $this->cook['optout'] ) && $this->cook['optout'] == 'all' ) {
                        $this->data['cook']['setup'][] = [ 'alert', 'Opt-out mode is used for visitors from all countries.' ];
                    }

                    if ( ! empty ( $this->cook['optout'] ) && $this->cook['optout'] == 'none' ) {
                        $this->data['cook']['setup'][] = [ 'ok', 'Opt-out mode is not used for visitors from any country.' ];
                    }

                    if ( ! empty ( $this->cook['optout'] ) && $this->cook['optout'] == 'specific' ) {
                        if ( isset ( $this->cook['optout_countries'] ) ){
                            $this->data['cook']['setup'][] = [ 'warning', 'Opt-out mode is used for visitors from: ' . $this->cook['optout_countries']  . '.' ];
                        } else {
                            $this->data['cook']['setup'][] = [ 'ok', 'Opt-out mode is not used for visitors from any country.' ];
                        }
                    }

                    // Notify
                    if ( ! empty ( $this->cook['inform'] ) && $this->cook['inform'] == 'all' ) {
                        $this->data['cook']['setup'][] = [ 'alert', 'Visitors from all countries are notified about tracking but can\'t opt-out' ];
                    }

                    if ( ! empty ( $this->cook['inform'] ) && $this->cook['inform'] == 'none' ) {
                        $this->data['cook']['tracked_extra_data'][] = 'Visitors from no country are only informed that they are tracked.';
                    } else if (  ! empty ( $this->cook['inform'] ) && $this->cook['inform'] == 'specific' ) {
                        if ( isset ( $this->cook['inform_countries'] ) ){
                            $this->data['cook']['setup'][] = [ 'warning', 'Visitors from these countries are notified about tracking but can\'t opt-out: ' . $this->cook['inform_countries'] . '.' ];
                        } else {
                            $this->data['cook']['setup'][] = [ 'ok', 'Visitors from no country are only informed that they are tracked.' ];
                        }
                    }

                    // Other
                    $this->data['cook']['setup'][] = [ 'warning', 'Visitors from other countries are tracked without notification.' ];

                break;
            }

            // Geo fallback
            if (  ! empty ( $this->cook['mode'] ) && ( $this->cook['mode'] == 'auto_strict' || $this->cook['mode'] == 'auto_lax' || $this->cook['mode'] == 'manual' ) ) {
                
                if ( ! isset( $this->cook['enable_scripts_after'] ) ) $this->cook['enable_scripts_after'] = 'optin';

                switch ( $this->cook['enable_scripts_after'] ) {
                    case 'optin':
                        $this->data['cook']['setup'][] = [ 'ok', 'When visitor location is not found, consent banner will start tracking visitors only after they consent to tracking (Opt-in mode).' ];
                    break;
                    case 'optout':
                        $this->data['cook']['setup'][] = [ 'alert', 'When visitor location is not found, consent banner will start tracking visitors from the moment they enter the website but will let them decline tracking (Opt-out mode)' ];
                    break;

                    case 'notify':
                        $this->data['cook']['setup'][] = [ 'alert', 'When visitor location is not found, visitors will be notified that they are tracked bu they will not be able to decline tracking.' ];
                    break;
                }
            }
        }

    // when geo is disabled, the mode is set in the setting "enable_scripts_after"
    } else {

        if ( ! isset( $this->cook['enable_scripts_after'] ) ) $this->cook['enable_scripts_after'] = 'optin';

        switch ( $this->cook['enable_scripts_after'] ) {
            case 'optin':
                $this->data['cook']['setup'][] = [ 'ok', 'Consent banner is set to work in Opt-in mode - it enables tracking tools and loads embedded content according to visitors tracking choices.' ];
            break;
            case 'optout':
                $this->data['cook']['setup'][] = [ 'alert', 'Consent banner is set to work in Opt-out mode - it enables tracking tools and loads embedded content when visitors enter the website but lets them decline tracking.' ];
            break;

            case 'notify':
                $this->data['cook']['setup'][] = [ 'alert', 'Visitors are notified that they are tracked but they can\'t decline tracking.' ];
            break;
        }
    }
}

// Privacy policy page
if ( empty ( $this->priv_policy_url ) ) {
    $this->data['cook']['setup'][] = [ 'alert', 'Privacy policy page is not set or published' ];
}

// Text for pre-selected switches
if ( isset( $notice_opts['switches_on'] ) && is_array( $notice_opts['switches_on'] ) && ! empty( $notice_opts['optin_switches'] ) ) {
    // and we are not hiding the whole section with settings
    if ( isset( $notice_opts['hide'] ) && is_array($notice_opts['hide'] ) && ! in_array( 'settings_btn', $notice_opts['hide'] ) ) {
        $this->data['cook']['setup'][] = [ 'alert', 'When visitors are asked for tracking consent (opt-in), switches for choosing allowed uses of tracked data are pre-selected.' ];
    };
};

// Button which toggles consent banner

$toggle_btn_enabled = ! empty( $notice_opts['enable_toggle_btn'] );

if ( $toggle_btn_enabled ) {
    $this->data['cook']['setup'][] = [ 'ok', 'Visitors who want to change their tracking preferences can do it in the consent banner which shows after they click an icon in the corner of the screen' ];
    
} else {

    $priv_policy_id = get_option( 'wp_page_for_privacy_policy' );
    $priv_policy_post = get_post( $priv_policy_id );
    $toggler_found = false;

    if ( ! empty( $priv_policy_post ) ) {
        
        $priv_policy_content = $priv_policy_post->post_content;
        $priv_policy_content = apply_filters( 'the_content', $priv_policy_content );
        $priv_policy_content = do_shortcode( $priv_policy_content );
        
        $toggle_selectors = [ 'fp_show_cookie_notice' ];

        if ( ! empty ( $this->cook['toggle_selector'] ) && strlen( $this->cook['toggle_selector'] ) > 3 ) {    
            $toggle_selectors[] = ltrim( esc_attr( $this->cook['toggle_selector'] ), $this->cook['toggle_selector'][0] );
        }

        foreach ( $toggle_selectors as $sel ) {
            if ( str_contains( $priv_policy_content, $sel ) ) $toggler_found = true;
        }

        if ( $toggler_found ) {
            $this->data['cook']['setup'][] = [ 'ok', 'Visitors who want to change their tracking preferences can do it in the consent banner which shows after they click a link/button in the privacy policy.' ];
        } else {
            $this->data['cook']['setup'][] = [ 'warning', 'Visitors probably can\'t change their tracking preferences' ];
        }  
    } else {
        $this->data['cook']['setup'][] = [ 'warning', 'Visitors probably can\'t change their tracking preferences' ];
    }
}

// TEXTS & STYLING

$hidden_elements = isset( $notice_opts['hide'] ) && is_array($notice_opts['hide']) ? $notice_opts['hide'] : [];
$hidden_descr = [];

$default_texts = [
    'notif_h' 			=> '',
    'notif_descr'		=> esc_html__('We use cookies to provide you with the best browsing experience, personalize content of our site, analyse its traffic and show you relevant ads. See our {{privacy policy}} for more information.', 'full-picture-analytics-cookie-notice'),
    'agree' 			=> esc_html__('Agree', 'full-picture-analytics-cookie-notice'),
    'ok' 				=> esc_html__('I understand', 'full-picture-analytics-cookie-notice'),
    'decline' 			=> esc_html__('Decline', 'full-picture-analytics-cookie-notice'),
    'cookie_settings' 	=> esc_html__('Settings', 'full-picture-analytics-cookie-notice'),
    'agree_to_selected' => esc_html__('Agree to selected', 'full-picture-analytics-cookie-notice'),
    'return' 			=> esc_html__('Return', 'full-picture-analytics-cookie-notice'),
    'necess_h' 			=> '',
    'necess_descr' 		=> '',
    'stats_h' 			=> esc_html__('Statistics', 'full-picture-analytics-cookie-notice'),
    'stats_descr' 		=> esc_html__('I want to help you make this site better so I will provide you with data about my use of this site.', 'full-picture-analytics-cookie-notice'),
    'pers_h' 			=> esc_html__('Personalisation', 'full-picture-analytics-cookie-notice'),
    'pers_descr' 		=> esc_html__('I want to have the best experience on this site so I agree to saving my choices, recommending things I may like and modifying the site to my liking', 'full-picture-analytics-cookie-notice'),
    'market_h' 			=> esc_html__('Marketing', 'full-picture-analytics-cookie-notice'),
    'market_descr' 		=> esc_html__('I want to see ads with your offers, coupons and exclusive deals rather than random ads from other advertisers.', 'full-picture-analytics-cookie-notice'),
];

$current_texts = [
    'notification_headline' 			=> ! empty( $notice_opts['notif_headline_text'] ) 	? esc_html( $notice_opts['notif_headline_text'] ) 		: $default_texts['notif_h'],
    'agree_to_all_cookies_button' 		=> ! empty( $notice_opts['agree_text'] ) 			? esc_html( $notice_opts['agree_text'] ) 				: $default_texts['agree'],
    'i_understand_button' 				=> ! empty( $notice_opts['ok_text'] ) 				? esc_html( $notice_opts['ok_text'] ) 					: $default_texts['ok'],
    'decline_button'			        => ! empty( $notice_opts['decline_text'] ) 			? esc_html( $notice_opts['decline_text'] ) 				: $default_texts['decline'],
    'cookie_settings_button' 	        => ! empty( $notice_opts['cookie_settings_text'] ) 	? esc_html( $notice_opts['cookie_settings_text'] ) 		: $default_texts['cookie_settings'],
    'agree_to_selected_button'          => ! empty( $notice_opts['agree_to_selected_text'] ) ? esc_html( $notice_opts['agree_to_selected_text'] ) 	: $default_texts['agree_to_selected'],
    'return_button' 			        => ! empty( $notice_opts['return_text'] ) 			? esc_html( $notice_opts['return_text'] ) 				: $default_texts['return'],
    'necessary_cookies_headline' 		=> ! empty( $notice_opts['necess_headline_text'] ) 	? esc_html( $notice_opts['necess_headline_text'] ) 		: '',
    'statistics_hookies_headline'		=> ! empty( $notice_opts['stats_headline_text'] ) 	? esc_html( $notice_opts['stats_headline_text'] ) 		: $default_texts['stats_h'],
    'peronalisation_cookies_headline' 	=> ! empty( $notice_opts['pers_headline_text'] ) 	? esc_html( $notice_opts['pers_headline_text'] ) 		: $default_texts['pers_h'],
    'marketing_cookies_headline' 		=> ! empty( $notice_opts['marketing_headline_text'] ) ? esc_html( $notice_opts['marketing_headline_text'] ) : $default_texts['market_h'],
    'notification_main_descr' 		    => ! empty( $notice_opts['notif_text'] ) 			? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['notif_text'] ) ) 	: $this->fupi_modify_cons_banner_text( $default_texts['notif_descr'] ),
    'necessary_cookies_descr' 		    => ! empty( $notice_opts['necess_text'] ) 			? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['necess_text'] ) )	: '',
    'statistics_cookies_descr' 		    => ! empty( $notice_opts['stats_text'] ) 			? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['stats_text'] ) )	: $default_texts['stats_descr'],
    'personalisation_cookies_descr' 	=> ! empty( $notice_opts['pers_text'] ) 			? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['pers_text'] ) )		: $default_texts['pers_descr'],
    'marketing_cookies_descr' 		    => ! empty( $notice_opts['marketing_text'] ) 		? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['marketing_text'] ) )	: $default_texts['market_descr'],
];

$this->data['cook']['notice_texts'] = $current_texts;

if ( count( $hidden_elements ) > 0 ) {

    if ( in_array( 'settings_btn', $hidden_elements ) ) $hidden_descr[] = 'button opening panel with cookie settings';
    if ( in_array( 'stats', $hidden_elements ) ) $hidden_descr[] = 'section where users can consent to the use of their data for statistics';
    if ( in_array( 'market', $hidden_elements ) ) $hidden_descr[] = 'section where users can consent to the use of their data for marketing';
    if ( in_array( 'pers', $hidden_elements ) ) $hidden_descr[] = 'section where users can consent to the use of their data for personalisation';
    if ( in_array( 'decline_btn', $hidden_elements ) ) {
        $hidden_descr[] = '"Decline" button';
        $this->data['cook']['setup'][] = [ 'alert', 'The consent banner does not display the "Decline" button' ];
    }

    $this->data['cook']['hidden_elements'] = 'Hidden consent baner elements: ' . join( ', ', $hidden_descr ) . '.';
}