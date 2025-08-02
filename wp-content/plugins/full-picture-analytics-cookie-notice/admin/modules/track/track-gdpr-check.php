<?php

if ( ! empty( $this->track['extra_tools'] ) ){
    
    $tools = [];
    foreach( $this->track['extra_tools'] as $tool ){ 
        $tools[] = $tool['name'];
    }

    // Display section only if it has content
    if ( count( $tools ) == 0 ) return;
    
    $this->data['track'] = [ 
        'module_name' => $this->format == 'cdb' ? 'Additional tracking tools' : esc_html__('Additional tracking tools', 'full-picture-analytics-cookie-notice'),
        'setup' => []
    ];

    if ( $this->format == 'cdb' ) {
        $this->data['track']['setup'][0] = [ 'ok',  'These additional tracking tools are used on the website: ' . join( ', ', $tools ) ];
    } else {
        $this->data['track']['pp comments'][] = sprintf( esc_html__('In the "General settings" page > "Shortcode" tab, you entered, that your website uses these additional tracking tools: %1$s. Add information about them to your privacy policy. Link to their own privacy policies, write what data they collect, how it is used and whether or not it is shared with 3rd parties.', 'full-picture-analytics-cookie-notice'), join( ', ', $tools ) );
    }
}