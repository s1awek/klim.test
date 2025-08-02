<?php

// GET THE DATA

$iframeblock_rules = array();

// block rules

if ( ! empty( $this->cook['iframe_auto_rules'] ) ) {

    foreach ( $this->cook['iframe_auto_rules'] as $source ) {
        switch ( $source ) {
            case 'youtube':
                $iframeblock_rules[] = array(
                    'name'          => 'youtube.com',
                    'iframe_url'    => 'youtube.com',
                    'privacy_url'   => 'https://www.youtube.com/privacy',
                    'image_url'     => '',
                    'stats'         => true,
                    'market'        => true,
                    'pers'          => false
                );
            break;
            case 'vimeo':
                $iframeblock_rules[] = array( 
                    'name'          => 'vimeo.com',
                    'iframe_url'    => 'vimeo.com',
                    'privacy_url'   => 'https://vimeo.com/privacy',
                    'image_url'     => '',
                    'stats'         => true,
                    'market'        => true,
                    'pers'          => false
                );
            break;
        }
    }
};


if ( ! empty( $this->cook['control_other_iframes'] ) && ! empty( $this->cook['iframe_manual_rules'] ) ) {
    $iframeblock_rules = array_merge( $iframeblock_rules, $this->cook['iframe_manual_rules'] );
}

// GET IFRAMES IN HTML

// Get the content inside <body>

$new_html = preg_replace_callback( '/<body([^>]*)>(.*?)<\/body>/is', function( $body_matches ) use ( $iframeblock_rules ){
    
    $bodyAttributes = $body_matches[1];
    $bodyContent = $body_matches[2];

    // replace "iframe" with "fupi_iframe" in all the commented sections
    $bodyContent_1 = preg_replace_callback( '/fp_no_mod_start.*?fp_no_mod_end/is', function( $no_mod_section ) {
        return str_replace('iframe', 'fupi_temp_ifr', $no_mod_section[0]);
    }, $bodyContent );

    if ( ! empty( $bodyContent_1 ) ) $bodyContent = $bodyContent_1;

    // replace all the iframes with placeholders

    $bodyContent_2 = preg_replace_callback( '/<iframe([^>]*)>(.*?)<\/iframe>/is', function( $iframe ) use ( $iframeblock_rules ) {
        
        $ret_val = "<iframe{$iframe[1]}>{$iframe[2]}</iframe>";

        if ( count( $iframeblock_rules ) > 0 ) {

            foreach ( $iframeblock_rules as $iframe_settings ) {
               
                $pattern = '/src=["\'].*?' . preg_quote( $iframe_settings['iframe_url'], "/") . '[^"\']*["\']/i';
                
                if ( preg_match( $pattern, $iframe[1] ) ) {
    
                    $stats      = ! empty( $iframe_settings['stats'] ) ? '1' : '0';
                    $market     = ! empty( $iframe_settings['market'] ) ? '1' : '0';
                    $pers       = ! empty( $iframe_settings['pers'] ) ? '1' : '0';
                    $name 	    = ! empty( $iframe_settings['name'] ) ? ' data-name="' . esc_attr( $iframe_settings['name'] ) . '"': '';
                    $placeholder = ! empty( $iframe_settings['image_url'] ) ? ' data-placeholder="' . esc_url( $iframe_settings['image_url'] ) . '"': '';
                    $privacy    = ! empty( $iframe_settings['privacy_url'] ) ? ' data-privacy="' . esc_url( $iframe_settings['privacy_url'] ) . '"' : '';
    
                    $ret_val = '<div class="fupi_blocked_iframe" data-stats="' . $stats . '" data-market="' . $market . '" data-pers="' . $pers . '" ' . $placeholder . $privacy . $name . '><div class="fupi_iframe_data" ' . $iframe[1] . '>' . $iframe[2] . '</div></div><!--noptimize--><script data-no-optimize="1" nowprocket>FP.manageIframes();</script><!--/noptimize-->';

                };
            }
        };

        return $ret_val;

    }, $bodyContent );

    if ( ! empty( $bodyContent_2 ) ) $bodyContent = $bodyContent_2;

    // replace all occurances of "fupi_iframe" back to "iframe"
    $bodyContent = str_replace( 'fupi_temp_ifr', 'iframe', $bodyContent );
    
    return "<body$bodyAttributes>$bodyContent</body>";

}, $html);

if ( ! empty( $new_html ) ) $html = $new_html;