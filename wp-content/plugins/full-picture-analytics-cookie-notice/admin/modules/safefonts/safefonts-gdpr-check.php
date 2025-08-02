<?php

$module_info = $this->get_module_info( 'safefonts' );
$this->set_basic_module_info( 'safefonts', $module_info );
$safefonts_enabled = ! empty( $this->tools['safefonts'] );

if ( $safefonts_enabled ) {
    $this->data['safefonts']['pre-setup'][] = [ 
        sprintf( esc_html__('You enabled replacing Google Fonts with safe fonts from Bunny Fonts but your website can still load them dynamically (after the page loads). Scan your website with %1$s again. If it finds links to Google Fonts, you need to find the plugin or theme that loads them and disable Google Fonts in their settings. Alternatively, you can use an %2$sOMGF%3$s plugin.', 'full-picture-analytics-cookie-notice'), '<a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a>', '<a href="https://wordpress.org/plugins/host-webfonts-local/" target="_blank">', '</a>' )
    ];
} else {
    $this->data['safefonts']['pre-setup'][] = [ 
        sprintf( esc_html__('Check if your website uses Google Fonts. Scan it with %1$s. If it does, then either disable them in the settings of your website, enable the Safe Fonts module to replace them with GDPR-compliant fonts from Bunny Fonts or use a plugin %2$sOMGF (free)%3$s.', 'full-picture-analytics-cookie-notice'), '<a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a>', '<a href="https://wordpress.org/plugins/host-webfonts-local/" target="_blank">', '</a>' )
    ];
}