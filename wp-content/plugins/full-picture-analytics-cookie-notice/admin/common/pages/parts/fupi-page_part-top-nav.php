<div id="fupi_top_bar">
<?php 
//
// TOP NAV
// ?>
    <div id="fupi_top_nav" class="top_menu_section"><?php 

        if ( fupi_fs()->is_not_paying() ) {
            $support_href = 'https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/';
        } else {
            $support_href = 'https://wpfullpicture.com/contact/?utm_source=fp_admin&utm_medium=fp_link';
        };
        
        // FIRST STEPS / GDPR HELPER / DOCS / SUPPORT

        $top_nav = '
            <a class="fupi_top_nav_link" href="https://wpfullpicture.com/support/documentation/first-steps-in-full-picture/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">' . esc_html__('First steps', 'full-picture-analytics-cookie-notice') . '</a>

            <a class="fupi_top_nav_link" href="https://wpfullpicture.com/support/documentation/troubleshooting/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">' . esc_html__('Solutions to problems', 'full-picture-analytics-cookie-notice') . '</a>
            
            <a class="fupi_top_nav_link" href="https://wpfullpicture.com/support/documentation/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">' . esc_html__('Documentation', 'full-picture-analytics-cookie-notice') . '</a>
                
            <a class="fupi_top_nav_link" href="' . $support_href . '" target="_blank">' . esc_html__('Support', 'full-picture-analytics-cookie-notice') . '</a>
            
            <a class="fupi_top_nav_link" href="https://wpfullpicture.com/release/wp-full-picture-update-9-1-1/" target="_blank">' . esc_html__('What\'s new in v9.1', 'full-picture-analytics-cookie-notice') . '</a>';
        
        // MODULES TOGGLE BTN

        $top_nav .= '<button id="fupi_mobile_nav_toggle_button" type="button" class="button primary-button"><span class="dashicons dashicons-menu-alt3"></span><span class="fupi_srt">' . esc_html__( 'Menu', 'full-picture-analytics-cookie-notice' ) . '</span></button>';
        
        echo $top_nav; ?>
    </div>
    <?php

    //
    // SETUP MODE INFO
    //

    $debug_info_text = empty ( $main_opts['debug'] ) ? sprintf( esc_html__( 'Setup mode is %1$sdisabled%2$s', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) : sprintf( esc_html__( 'Setup mode is %1$sactive%2$s', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' );

    echo '<div id="fupi_top_setup_info">' . $debug_info_text . '<button type="button" class="fupi_open_popup fupi_open_popup_i " data-popup="fupi_debug_info_popup">i</button></div>';

    //
    // ACCOUNT / GET PRO NAV
    //

    $pro_nav = '';

    if ( fupi_fs()->can_use_premium_code() ) {
        
        // ACCOUNT

        $pro_nav .= '<a class="fupi_top_nav_link" target="_blank" href="https://wpfullpicture.com/account/"><span class="dashicons dashicons-admin-users"></span> ' . esc_html__( 'Account', 'full-picture-analytics-cookie-notice' ) . '</a>';
    }
    
    if ( fupi_fs()->is_not_paying() ) {
        
        // GET PRO
        $pro_nav .= '<a id="fupi_get_pro_link" class="fupi_top_nav_link" href="https://wpfullpicture.com/pricing/" target="_blank">' . esc_html__('Get Pro', 'full-picture-analytics-cookie-notice') . '</a>'; 
    }; 

    if ( ! empty( $pro_nav ) ) {
        echo '<div id="fupi_pro_nav" class="top_menu_section">' . $pro_nav . '</div>';
    }
    ?>
</div>