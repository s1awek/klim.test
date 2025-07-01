<?php 

    $module_id    	    = sanitize_html_class( $_GET[ 'page' ] );
    $module_id    	    = str_replace( 'fp_reports_', '', $module_id );
    $current_report     = false;
    $dashboards         = $this->fupi_report_pages;

    // Title & nav

    settings_errors();

    $output = '<h1>' . get_admin_page_title()  . '</h1>
        <div id="fupi_reports_nav">';
            foreach ( $dashboards as $db ) {
                $output .= '<a href="' . get_admin_url() . 'admin.php?page=fp_reports_' . $db['id'] . '" class="button-secondary">' . $db['title'] . '</a>';
            }
    $output .= '</div>';

    // Iframe
    // if ( empty( $module_id ) ){
    //     $current_report = $dashboards[0];
    // } else {
    //     foreach ( $dashboards as $db ) {
    //         if ( $db['id'] == $module_id ) $current_report = $db;
    //     }
    // }
    
    // get current page contents
    foreach ( $dashboards as $db ) {
        if ( $db['id'] == $module_id ) {
            $current_report = $db;
        } else {
            continue;
        }
    }
    
    if ( $current_report !== false ) {

        // show iframe
        $width = ! empty( $current_report['width'] ) ? (int) $current_report['width'] : 1200;        
        $max_width = 'max-width: ' . $width . 'px;';
    
        if ( ! empty( $current_report['height'] ) ){
            $padding = 'padding-bottom: ' . ( (int) $current_report['height'] / $width * 100) . '%;';
        } else {
            $padding = 'padding-bottom: ' . ( 675 / $width * 100 ) . '%;';
        }
        
        $output .= '<div id="fupi_report_wrap" style="' . $max_width . '"><div class="fupi_responsive_iframe" style="'. $padding . '">' . html_entity_decode( $current_report['iframe'] ) . '</div></div>';
    
        if ( ! empty( $current_report['type'] ) && $current_report['type'] == 'module' ){
            if ( $current_report['id'] == 'module_pla' ) {
                $output .= '<script async="" src="https://plausible.io/js/embed.host.js"></script>';
            }
        };
        
    }

    echo $output . '<p id="fp_report_warnings">' . esc_html__( 'Can\'t see the report? Make sure that: your ad blocker is disabled (if you are using any), you are logged in to the platform which generated the report, you have access rights to this report, your security solution doesn\'t use Content Security Policy (it can block the domain of the platform that this report is from).', 'full-picture-analytics-cookie-notice' ) . '</p>';

?>