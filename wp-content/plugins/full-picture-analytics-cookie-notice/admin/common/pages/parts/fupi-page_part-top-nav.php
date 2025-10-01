<div id="fupi_top_bar">
<?php 
//
// TOP NAV
// ?>
    <script>
        let fupi_adv_mode_alert_text = '<?php echo esc_js( __( 'This will reload the page. All unsaved data will be lost. Are you sure?', 'full-picture-analytics-cookie-notice' ) ); ?>';
    </script>
    <div id="fupi_top_nav" class="top_menu_section"><?php 
        
        // FIRST STEPS / GDPR HELPER / DOCS / SUPPORT
//<a class="fupi_top_nav_link" href="https://wpfullpicture.com/support/documentation/first-steps-in-full-picture/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">' . esc_html__('First steps', 'full-picture-analytics-cookie-notice') . '</a>
        $top_nav = '
            <button type="button" class="fupi_top_nav_link fupi_open_popup" data-popup="fupi_first_steps_2_popup">' . esc_html__('Recommendations', 'full-picture-analytics-cookie-notice') . '</button>

            <button type="button" class="fupi_top_nav_link fupi_open_popup" data-popup="fupi_help_links_popup">' . esc_html__('Help', 'full-picture-analytics-cookie-notice') . '</button>';
        
        // MODULES TOGGLE BTN

        $top_nav .= '<button id="fupi_mobile_nav_toggle_button" type="button" class="button primary-button"><span class="dashicons dashicons-menu-alt3"></span><span class="fupi_srt">' . esc_html__( 'Menu', 'full-picture-analytics-cookie-notice' ) . '</span></button>';
        
        echo $top_nav; ?>
    </div>
    <?php

    //
    // SETUP HELPER
    //

    // check for meta value "fupi_easy_mode" in current user\'s meta

    if ( ! $user_adv_mode ) {
        $adv_mode_checked = '';
    } else {
        $adv_mode_checked = 'checked="checked"';
    }

    if ( empty ( $this->ver['debug'] ) ) {
        $setup_mode_checked = '';
    } else {
        $setup_mode_checked = 'checked="checked"';
    }

    echo '<div id="fupi_top_setup_info">
        
        <script> let fupi_setup_mode_nonce ="' . wp_create_nonce( 'fupi_update_modes_nonce' ) . '";</script>
        
        <div id="fupi_top_setup_info_adv">
            ' . sprintf( esc_html__( 'Show advanced settings', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '
            <label class="fupi_switch">
                <input type="checkbox" id="adv_mode_checkbox" value="1" ' . $adv_mode_checked . '>
                <span class="fupi_switch_slider"></span>
            </label>
            <button type="button" class="fupi_open_popup fupi_open_popup_i " data-popup="fupi_easy_mode_info_popup">i</button>
        </div>

        <div id="fupi_top_setup_info_setup">
            ' . sprintf( esc_html__( 'Setup helper', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '
            <label class="fupi_switch">
                <input type="checkbox" id="setup_mode_checkbox" value="1" ' . $setup_mode_checked . '>
                <span class="fupi_switch_slider"></span>
            </label>
            <button type="button" class="fupi_open_popup fupi_open_popup_i " data-popup="fupi_setup_mode_info_popup">i</button>
        </div>
    </div>';

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
    }; ?>
</div>