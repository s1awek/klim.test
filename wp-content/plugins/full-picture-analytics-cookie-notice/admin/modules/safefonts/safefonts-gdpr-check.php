<?php

$module_info = $this->get_module_info( 'safefonts' );
$this->set_basic_module_info( 'safefonts', $module_info );

$this->data['safefonts']['top comments'] = [
    esc_attr__( 'Google Fonts do not comply with GDPR. Replace them with fonts hosted on GDPR-compliant servers or host their files locally, on the server of your website.', 'full-picture-analytics-cookie-notice'),
];

    $this->data['safefonts']['pre-setup'][] = [ 
        sprintf( esc_html__('Scan your website with %1$s to see if your website uses Google Fonts. If it does, %2$sfollow this guide%3$s to learn how to make them comply with GDPR.', 'full-picture-analytics-cookie-notice'), '<a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a>', '<a href="https://wpfullpicture.com/support/documentation/how-to-replace-google-fonts-with-gdpr-compliant-fonts/" target="_blank">', '</a>' )
    ];