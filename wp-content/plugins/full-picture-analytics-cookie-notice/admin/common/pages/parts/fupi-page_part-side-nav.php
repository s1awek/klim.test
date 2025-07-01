<?php

    $fupi_tools = get_option('fupi_tools');
    $modules_nr = 0;
    $sections_html = [
        'settings' => '',
        'integr' => '',
        'tagman' => '',
        'priv' => '',
        'ext' => ''
    ];

    include FUPI_PATH . '/includes/fupi_modules_names.php';
    
    $addons_names = apply_filters( 'fupi_set_module_name', [] ); // ! ADDON
    $fupi_modules_names = array_merge( $fupi_modules_names, $addons_names );

    foreach( $all_modules_data as $module ){

        if ( $module['is_avail'] && $module['has_admin_page'] ) {

            if ( isset( $fupi_tools[$module['id']] ) || $module['id'] == 'tools' || $module['id'] == 'main' || $module['id'] == 'status' ) {
                
                $modules_nr++;
                
                // do not add a link to Woo if the plugin is deactivated
                if ( $module['id'] == 'woo' && ! class_exists( 'woocommerce' ) ) continue;

                // if this page is currently displayed
                if ( $module_id == $module['id'] ) {    

                    $el = '<div id="fupi_current_page_sidenav"><span class="fupi_active_title">' . $fupi_modules_names[$module['id']] . '</span></div>';
                
                // if this page is not currently displayed
                } else {

                    $el = '<a href="'. get_admin_url() .'admin.php?page=full_picture_' . $module['id'] . '" data-type="'.$module['type'].'">' . $fupi_modules_names[$module['id']] . '</a>';
                }

                // if this is the first element in the section
                if ( empty( $sections_html[$module['type']] ) ) {

                    switch ( $module['type'] ) {
                        case 'settings':
                            $sections_html[$module['type']] = '<div id="fupi_sidenav_settings_section" class="fupi_sidenav_section"><span class="fupi_sidenav_title"><span class="dashicons dashicons-admin-settings"></span> ' . esc_html__( 'Settings', 'full-picture-analytics-cookie-notice' ) . '</span><div class="fupi_sidenav_links">';
                            break;
                        case 'integr':
                            $sections_html[$module['type']] = '<div id="fupi_sidenav_integr_section" class="fupi_sidenav_section"><span class="fupi_sidenav_title"><span class="dashicons dashicons-chart-bar"></span> ' . esc_html__( 'Integrations', 'full-picture-analytics-cookie-notice' ) . '</span><div class="fupi_sidenav_links">';
                            break;
                        case 'tagman':
                            $sections_html[$module['type']] = '<div id="fupi_sidenav_tagman_section" class="fupi_sidenav_section"><span class="fupi_sidenav_title"><span class="dashicons dashicons-editor-code"></span> ' . esc_html__( 'Manual integrations', 'full-picture-analytics-cookie-notice' ) . '</span><div class="fupi_sidenav_links">';
                            break;
                        case 'priv':
                            $sections_html[$module['type']] = '<div id="fupi_sidenav_priv_section" class="fupi_sidenav_section"><span class="fupi_sidenav_title"><span class="dashicons dashicons-visibility"></span> ' . esc_html__( 'Privacy solutions', 'full-picture-analytics-cookie-notice' ) . '</span><div class="fupi_sidenav_links">';
                            break;
                        case 'ext':
                            $sections_html[$module['type']] = '<div id="fupi_sidenav_ext_section" class="fupi_sidenav_section"><span class="fupi_sidenav_title"><span class="dashicons dashicons-admin-plugins"></span> ' . esc_html__( 'Extensions', 'full-picture-analytics-cookie-notice' ) . '</span><div class="fupi_sidenav_links">';
                            break;
                    }
                }

                $sections_html[$module['type']] .= $el;
                
            }
        }
    };

    foreach( $sections_html as $name => $val ){
        if ( ! empty( $sections_html[$name] ) ) {
            $sections_html[$name] .= '</div></div>';
        }
    };

    $sections_html[$name] .= '<div id="fupi_sidenav_ext_section" class="fupi_sidenav_section fupi_sidenav_account_section" style="display: none;">
        <span class="fupi_sidenav_title">
            <span class="dashicons dashicons-admin-users"></span> ' . esc_html__( 'Account', 'full-picture-analytics-cookie-notice' ) . '
        </span>
        <div class="fupi_sidenav_links"><a href="" data-type="account">' . esc_html__( 'Account', 'full-picture-analytics-cookie-notice' ) . '</a></div>
    </div>';

    $under_nav_notice = $modules_nr <= 3 ? '<div id="under_nav_notice">' . esc_html__('Please enable some modules. Links to enabled modules will show up here.', 'full-picture-analytics-cookie-notice') . '</div>' : '';

    if ( fupi_fs()->is_not_paying() ) {
        $under_nav_notice = '<div id="sidenav_buy_pro_banner">
            <h3>' . esc_html__('Get Pro', 'full-picture-analytics-cookie-notice') . '</h3>
            <div id="fupi_feature_slider" class="fupi_slider">
                <div class="fupi_slides">
                    <div class="fupi_slide">
                        <div class="fupi_slide_main_text">' . esc_html__('Track orders when they get specific statuses', 'full-picture-analytics-cookie-notice') . '</div>
                    </div>
                    <div class="fupi_slide">
                        <div class="fupi_slide_main_text">' . esc_html__('See what traffic sources bring you the best traffic', 'full-picture-analytics-cookie-notice') . '</div>
                        <a href="https://wpfullpicture.com/free-vs-pro/#traffic_sources_section" target="_blank" class="small">' . esc_html__('Learn more', 'full-picture-analytics-cookie-notice') . '</a>
                    </div>
                    <div class="fupi_slide">
                        <div class="fupi_slide_main_text">' . esc_html__('Use server-side tracking in Meta Pixel and Google Analytics', 'full-picture-analytics-cookie-notice') . '</div>
                        <a href="https://wpfullpicture.com/free-vs-pro/#server_side_section" target="_blank" class="small">' . esc_html__('Learn more', 'full-picture-analytics-cookie-notice') . '</a>
                    </div>
                </div>
                <ul class="fupi_slider_nav"></ul>
            </div>
            <a href="https://wpfullpicture.com/pricing" class="button-primary"><span class="dashicons dashicons-unlock"></span> ' . esc_html__('See the pricing', 'full-picture-analytics-cookie-notice') . '</a>
        </div>';
    }

    echo '<div id="fupi_page_nav" ><div id="fupi_side_menu" role="link">' . join('', $sections_html) . '</div>' . $under_nav_notice . '</div>';

?>