<?php

$info = $this->get_module_info( 'cook' );
$notice_opts = get_option( 'fupi_cookie_notice' );
$status = 'ok';
// levels: ok > warning > alert
$this->data['cook'] = [
    'module_name' => esc_attr__( 'Consent Banner', 'full-picture-analytics-cookie-notice' ),
    'setup'       => [['ok', esc_attr__( 'Google Consent Mode v2 and Microsoft UET Consent Mode are activate and work according to the consent banner settings.', 'full-picture-analytics-cookie-notice' )]],
];
if ( !empty( $this->cook ) ) {
    if ( !empty( $this->tools['geo'] ) ) {
        if ( !empty( $this->cook['mode'] ) ) {
            switch ( $this->cook['mode'] ) {
                case 'optout':
                case 'notify':
                    // $status = 'alert';
                    $this->data['cook']['setup'][0] = ['alert', esc_html__( 'Change the consent banner mode to Opt-in or one of automatic modes.', 'full-picture-analytics-cookie-notice' )];
                    break;
                case 'manual':
                    // if ( $status != 'alert' ) $status = 'warning';
                    $this->data['cook']['setup'][0] = ['warning', sprintf( esc_html__( 'Consent banner is set to work in manual mode. Make sure that it uses Opt-in mode in %1$sall countries that require it%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/">', '</a>' )];
                    break;
                default:
            }
        }
        if ( !empty( $this->cook['enable_scripts_after'] ) && $this->cook['enable_scripts_after'] !== 'optin' ) {
            // $status = 'alert';
            $this->data['cook']['setup'][] = ['alert', esc_html__( 'Set the consent banner to work in Opt-in mode if geolocation is not found.', 'full-picture-analytics-cookie-notice' )];
        }
        // when geo is disabled, the mode is set in the setting "enable_scripts_after"
    } else {
        if ( isset( $this->cook['enable_scripts_after'] ) && ($this->cook['enable_scripts_after'] === 'optout' || $this->cook['enable_scripts_after'] === 'notify') ) {
            // $status = 'alert';
            $this->data['cook']['setup'][0] = ['alert', esc_html__( 'Change the consent banner mode to Opt-in or one of automatic modes.', 'full-picture-analytics-cookie-notice' )];
        } else {
            $this->data['cook']['setup'][] = ['ok', esc_html__( 'Usually, between 20% and 30% of website visitors decline tracking. Analytics tools which require tracking consents will not track those people. Other tools will work normally.', 'full-picture-analytics-cookie-notice' )];
        }
    }
    // Do NOT ask again when modules or PP change
    if ( isset( $this->cook['dont_ask_again'] ) ) {
        $this->data['cook']['setup'][] = ['alert', esc_html__( 'Visitors are not asked for consent when the privacy policy text changes and/or when new tracking modules are enabled. This function breaks GDPR and needs to be disabled on production sites.', 'full-picture-analytics-cookie-notice' )];
    }
}
// Privacy policy page
if ( empty( $this->priv_policy_url ) ) {
    // $status = 'alert';
    $this->data['cook']['setup'][] = ['alert', sprintf( esc_html__( 'Make sure that the Privacy policy page is published and set %1$son this page%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="/wp-admin/options-privacy.php" target="_blank">', '</a>' )];
}
// Text for pre-selected switches
if ( isset( $notice_opts['switches_on'] ) && is_array( $notice_opts['switches_on'] ) && !empty( $notice_opts['optin_switches'] ) ) {
    // and we are not hiding the whole section with settings
    if ( isset( $notice_opts['hide'] ) && is_array( $notice_opts['hide'] ) && !in_array( 'settings_btn', $notice_opts['hide'] ) ) {
        // $status = 'alert';
        $this->data['cook']['setup'][] = ['alert', esc_html__( 'Disable pre-selection of consent switches in the styling options of the consent banner', 'full-picture-analytics-cookie-notice' )];
    }
}
// Extra texts for the privacy policy
if ( $this->format != 'cdb' ) {
    $pp_cookies_info = [esc_html__( 'Add to your privacy policy information that WP Full Picture uses the following cookies:', 'full-picture-analytics-cookie-notice' ), [esc_html__( 'fp_cookie - a necessary cookie. It stores information on visitor\'s tracking consents, a list of tracking tools that a user agreed to and the date of the last update of the privacy policy page. Does not expire.', 'full-picture-analytics-cookie-notice' ), esc_html__( 'fp_current_session - an optional cookie. It requires consent to tracking statistics. In the free version it does not hold any value and is only used to check if a new session has started. In the Pro version it holds the number and type of pages that a visitor viewed in a session, domain of the traffic source, URL parameters of the first landing page in a session and visitor\'s lead score. Expires when a visitor is inactive for 30 minutes.', 'full-picture-analytics-cookie-notice' )]];
    $this->data['cook']['pp comments'][] = $pp_cookies_info;
}
// Button which toggles consent banner
$toggle_btn_enabled = !empty( $notice_opts['enable_toggle_btn'] );
if ( !$toggle_btn_enabled ) {
    // Check if the button is in the privacy policy
    $priv_policy_id = get_option( 'wp_page_for_privacy_policy' );
    $priv_policy_post = get_post( $priv_policy_id );
    $toggler_found = false;
    if ( !empty( $priv_policy_post ) ) {
        $priv_policy_content = $priv_policy_post->post_content;
        $priv_policy_content = apply_filters( 'the_content', $priv_policy_content );
        $priv_policy_content = do_shortcode( $priv_policy_content );
        $toggle_selectors = ['fp_show_cookie_notice'];
        if ( !empty( $this->cook['toggle_selector'] ) && strlen( $this->cook['toggle_selector'] ) > 3 ) {
            $toggle_selectors[] = ltrim( esc_attr( $this->cook['toggle_selector'] ), $this->cook['toggle_selector'][0] );
        }
        foreach ( $toggle_selectors as $sel ) {
            if ( str_contains( $priv_policy_content, $sel ) ) {
                $toggler_found = true;
            }
        }
        if ( !$toggler_found ) {
            $toggle_selectors_str = '.fp_show_cookie_notice';
            if ( !empty( $this->cook['toggle_selector'] ) && strlen( $this->cook['toggle_selector'] ) > 3 ) {
                $toggle_selectors_str = $toggle_selectors_str . ', ' . esc_attr( $this->cook['toggle_selector'] );
            }
            // if ( $status != 'alert' ) $status = 'warning';
            $this->data['cook']['setup'][] = ['warning', esc_html__( 'Allow your visitors to change their tracking preferences. Enable a toggle icon in the theme customizer (Appearance > Customize > Consent Banner) or add a button in your privacy policy with the CSS selector(s):', 'full-picture-analytics-cookie-notice' ) . ' ' . $toggle_selectors_str . '.'];
        }
    }
}
// Position of the consent banner
$notice_position = ( !empty( $notice_opts['position'] ) ? esc_attr( $notice_opts['position'] ) : 'popup' );
if ( $notice_position != 'popup' ) {
    $this->data['cook']['opt-setup'][] = [esc_html__( 'Place your consent banner in the central position on the screen to collect maximum number of consents.', 'full-picture-analytics-cookie-notice' )];
}
// TEXTS & STYLING
if ( isset( $notice_opts['hide'] ) && is_array( $notice_opts['hide'] ) && in_array( 'decline_btn', $notice_opts['hide'] ) ) {
    // $status = 'alert';
    $this->data['cook']['setup'][] = ['alert', esc_html__( 'Do not hide the "Decline" button in the consent banner.', 'full-picture-analytics-cookie-notice' )];
}
// if ( $status == 'ok' ) {
//     $this->data['cook']['setup'][] = [ 'ok', esc_html__('Consent banner is set up correctly.', 'full-picture-analytics-cookie-notice') ];
// }