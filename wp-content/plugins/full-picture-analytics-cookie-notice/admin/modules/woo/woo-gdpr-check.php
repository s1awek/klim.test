<?php

if ( ! $this->woo_enabled ) return;

$woo_opts = $this->clean_val_id == 'woo' && ! empty( $this->clean_val ) ? $this->clean_val : get_option( 'fupi_woo' );
$module_info = $this->get_module_info( 'woo' );

$this->set_basic_module_info( 'woo', $module_info );

$order_attribution_enabled = get_option( 'woocommerce_feature_order_attribution_enabled' ); // returns either "yes" or "no"

// If attribution is enabled
if ( $order_attribution_enabled == 'yes' ){

    // If JS is not controled by the consent banner
    if ( empty( $woo_opts['block_sbjs'] ) ) {    
    
        if ( $this->format == 'cdb' ) {
            $this->data['woo']['setup'][] = [ 
                'alert', 
                'WooCommerce tracks traffic sources of all visitors without asking for consent.'
            ];
        } else {
            $this->data['woo']['setup'][] = [ 
                'alert', 
                sprintf( esc_html__('Make the order attribution feature in WooCommerce comply with GDPR (%2$slearn more%3$s). Enable it after visitor\'s consent. You can set it up in the WooCommerce Tracking module > Privacy tab. Alternatively, you can %1$sdisable it here%3$s.', 'full-picture-analytics-cookie-notice'), '<a href="/wp-admin/admin.php?page=wc-settings&tab=advanced&section=features" target="_blank">','<a href="https://wpfullpicture.com/blog/does-order-attribution-feature-in-woocommerce-8-5-1-break-gdpr-and-what-to-do-about-it/">', '</a>' )
            ];
        }
    
    // If JS is controlled
    } else {
        if ( $this->format == 'cdb' ) {
            $this->data['woo']['setup'][] = [ 
                'ok', 
                esc_html__('WooCommerce asks for consent before tracking what sources and ad campaigns brought clients to the website.', 'full-picture-analytics-cookie-notice')
            ];
        } else {
            $this->data['woo']['setup'][] = [ 
                'ok', 
                esc_html__('The source of order (Order details > "Order attribution" section) will be set to "Unknown" for user who did not agree to tracking or use ad blockers.', 'full-picture-analytics-cookie-notice')
            ];
            $this->data['woo']['pp comments'][] = sprintf( esc_html__('Add to your privacy policy information that your website uses SourceBuster.js script for Order Attribution in WooCommerce. The script is enabled after visitors agree to using their data for statistics. Optionally, you can include %1$sa list of cookies%2$s that the script uses', 'full-picture-analytics-cookie-notice'), '<a href="https://woocommerce.com/document/order-attribution-tracking/#section-7" target="_blank">', '</a>' );
        }
    }

// if attribution is disabled
} else {
    if ( $this->format == 'cdb' ) {
        $this->data['woo']['setup'][] = [ 
            'alert', 
            'Tracking order attribtion with sourcesbuster.js is disabled.'
        ];
    } else {
        unset ( $this->data['woo'] );
    }
}