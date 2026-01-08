<?php

$info               = $this->get_module_info( 'cook' );
$notice_opts        = get_option('fupi_cookie_notice');
$status             = 'ok'; // levels: ok > warning > alert

$this->data['cook'] = [ 
    'module_name' =>  esc_attr__('Consent Banner', 'full-picture-analytics-cookie-notice'),
    'top comments' => [
        esc_attr__( 'Your consent banner is active. It controls the loading of all tracking tools that you installed with WP Full Picture. The banner also uses Google Consent Mode v2 and Microsoft UET Consent Mode to control Google Analytics, Ads and Microsoft Advertising even if they are installed with other plugins.', 'full-picture-analytics-cookie-notice'),
    ],
    'setup' => [],
];

if ( ! empty( $this->cook ) ){

    if ( ! empty( $this->main['geo'] ) ) {

        if ( ! empty( $this->cook['mode'] ) ) {

            switch ( $this->cook['mode'] ) {
                case 'optout':
                    
                    $this->data['cook']['setup'][0] = [ 'warning', esc_html__('Your banner works in opt-out mode, which is illegal in 60+ countries (including the EU). Make sure that your site is only visited from countries where it is legal. Otherwise switch to opt-in mode or one of automatic modes (change modes depending on the visitor\'s location).', 'full-picture-analytics-cookie-notice') ];

                break;
                case 'notify':
                    
                    $this->data['cook']['setup'][0] = [ 'warning', esc_html__('Your banner only informs visitors about tracking - without giving them a way to prevent it. This is illegal in 60+ countries (including the EU). Make sure that your site is only visited from countries where it is legal. Otherwise switch to opt-in mode or one of automatic modes (change modes depending on the visitor\'s location).', 'full-picture-analytics-cookie-notice') ];
                break;
                case 'manual':
                    
                    $this->data['cook']['setup'][0] = [ 'warning', sprintf( esc_html__('Consent banner is set to work in manual mode. Make sure that it uses Opt-in mode in %1$sall countries that require it%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/">', '</a>') ];
                break;
                default:
            }
        }

        if ( ! empty ( $this->cook['enable_scripts_after'] ) && $this->cook['enable_scripts_after'] !== 'optin' ) {
            // $status = 'alert';
            $this->data['cook']['setup'][] = [ 'alert', esc_html__('Set the consent banner to work in Opt-in mode if geolocation is not found.', 'full-picture-analytics-cookie-notice') ];
        }

    // when geo is disabled, the mode is set in the setting "enable_scripts_after"
    } else {

        $optin_info_text = esc_html__('Usually, between 20% and 30% of website visitors decline tracking. Analytics tools which require tracking consents will not track those people. Other tools will work normally.', 'full-picture-analytics-cookie-notice');

        if ( isset ( $this->cook['enable_scripts_after'] ) ) {

            if ( $this->cook['enable_scripts_after'] === 'optout' ){

                $this->data['cook']['setup'][0] = [ 'warning', esc_html__('Your banner works in opt-out mode, which is illegal in 60+ countries (including the EU). Make sure that your site is only visited from countries where it is legal. Otherwise switch to opt-in mode or one of automatic modes (change modes depending on the visitor\'s location).', 'full-picture-analytics-cookie-notice') ];
                
            } else if ( $this->cook['enable_scripts_after'] === 'notify' ) {
                
                $this->data['cook']['setup'][0] = [ 'warning', esc_html__('Your banner works in opt-out mode, which is illegal in 60+ countries (including the EU). Make sure that your site is only visited from countries where it is legal. Otherwise switch to opt-in mode or one of automatic modes (change modes depending on the visitor\'s location).', 'full-picture-analytics-cookie-notice') ];
                
            } else {
                $this->data['cook']['setup'][] = [ 'ok', $optin_info_text ];
            }

        } else {
            $this->data['cook']['setup'][] = [ 'ok', $optin_info_text ];
        }
    }

    // Do NOT ask again when modules or PP change
    if ( isset( $this->cook['dont_ask_again'] ) ) {
        $this->data['cook']['setup'][] = [ 'alert', esc_html__('Visitors are not asked for consent when the privacy policy text changes and/or when new tracking modules are enabled. This function breaks GDPR and needs to be disabled on production sites.', 'full-picture-analytics-cookie-notice') ];
    }
}

// Privacy policy page
if ( empty ( $this->pp_ok ) ) {
    // $status = 'alert';
    $this->data['cook']['setup'][] = [ 'alert', sprintf( esc_html__('Make sure that the Privacy policy page is published, set %1$son this page%2$s and in the settings of the Consent Banner module.', 'full-picture-analytics-cookie-notice'), '<a href="/wp-admin/options-privacy.php" target="_blank">', '</a>' ) ];
}

// Text for pre-selected switches
if ( isset( $notice_opts['switches_on'] ) && is_array( $notice_opts['switches_on'] ) && ! empty( $notice_opts['optin_switches'] ) ) {
    // and we are not hiding the whole section with settings
    if ( isset( $notice_opts['hide'] ) && is_array($notice_opts['hide'] ) && ! in_array( 'settings_btn', $notice_opts['hide'] ) ) {
        // $status = 'alert';
        $this->data['cook']['setup'][] = [ 'alert', esc_html__('Disable pre-selection of consent switches in the styling options of the consent banner', 'full-picture-analytics-cookie-notice') ];
    };
};

// Extra texts for the privacy policy 

if ( $this->format != 'cdb' ) {

    $this->data['cook']['pp comments'][] = [ 
        sprintf( esc_html__('Add to your privacy policy information about WP Full Picture. We prepared a sample text for you that you can adjust to your needs and legal requirements in your country. %1$sView the text%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/texts-for-the-privacy-policy/">', '</a>' ),
    ];
}

// Button which toggles consent banner

$toggle_btn_enabled = ! empty( $notice_opts['enable_toggle_btn'] );

if ( ! $toggle_btn_enabled ) {

    // Check if the button is in the privacy policy

    $priv_policy_id     = ! empty( $this->cook['pp_id'] ) ? $this->cook['pp_id'] : false;
    $priv_policy_post   = ! empty ( $priv_policy_id ) ? get_post( $priv_policy_id ) : false;
    $toggler_found      = false;

    if ( ! empty( $priv_policy_post ) && isset( $priv_policy_post->post_status ) && $priv_policy_post->post_status === 'publish' ) {
        
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

        if ( ! $toggler_found ) {
            
            $toggle_selectors_str = '.fp_show_cookie_notice';
            
            if ( ! empty ( $this->cook['toggle_selector'] ) && strlen( $this->cook['toggle_selector'] ) > 3 ) {
                $toggle_selectors_str = $toggle_selectors_str . ', ' . esc_attr( $this->cook['toggle_selector'] );
            }
            
            // if ( $status != 'alert' ) $status = 'warning';

            $this->data['cook']['setup'][] = [ 'warning', esc_html__('Allow your visitors to change their tracking preferences. Enable a toggle icon in the theme customizer (Appearance > Customize > Consent Banner) or add a button in your privacy policy with the CSS selector(s):', 'full-picture-analytics-cookie-notice') . ' ' . $toggle_selectors_str . '.'];
        }   
    }
}

// Position of the consent banner

$notice_position = ! empty( $notice_opts['position'] ) ? esc_attr( $notice_opts['position'] ) : 'popup';

if ( $notice_position != 'popup' ) {
    $this->data['cook']['opt-setup'][] = [ 
        esc_html__('Place your consent banner in the central position on the screen to collect maximum number of consents.', 'full-picture-analytics-cookie-notice')
    ];
}

// TEXTS & STYLING

if ( isset( $notice_opts['hide'] ) && is_array( $notice_opts['hide'] ) && in_array( 'decline_btn', $notice_opts['hide'] ) ) {
    // $status = 'alert';
    $this->data['cook']['setup'][] = [ 'alert', esc_html__('Do not hide the "Decline" button in the consent banner.', 'full-picture-analytics-cookie-notice') ];
}

// if ( $status == 'ok' ) {
//     $this->data['cook']['setup'][] = [ 'ok', esc_html__('Consent banner is set up correctly.', 'full-picture-analytics-cookie-notice') ];
// }