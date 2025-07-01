<?php

    $module_id    	    = sanitize_html_class( $_GET[ 'page' ] );
    $module_id    	    = str_replace( 'full_picture_', '', $module_id );
    $active_tab   		= isset( $_GET[ 'tab' ] ) ? sanitize_html_class( $_GET[ 'tab' ] ) : false;
    $active_slug        = empty( $active_tab ) ? $module_id : $active_tab;
    $is_premium         = false;
    $licence 			= fupi_fs()->can_use_premium_code() ? 'pro' : 'free';
    $current_module_data = [];
    
    $addons_data        = apply_filters( 'fupi_register_addon', [] ); // ! ADDON
    $all_modules_data   = array_merge( $this->fupi_modules, $addons_data );

    foreach ( $all_modules_data as $module_data ) {
        if ( $module_data['id'] == $module_id ) {
            $current_module_data = $module_data;
            if ( ! empty ( $module_data['is_premium'] ) ) {
                $is_premium = true;
            }
        }
    };

    $multisite_url 		= is_multisite() ? network_home_url() : '';
    $module_slug 		= '';
    
    $wp_nonce           = wp_create_nonce( 'wp_rest' );
    
    // FIX to prevent double sanitizing settings (double-encoding HTML entities which makes scripts unusable) in Custom Scripts & Reports modules
    // https://core.trac.wordpress.org/ticket/21989)
    
    if ( $module_id == 'cscr' ) {
        $fupi_cscr = get_option('fupi_cscr');
        if ( empty( $fupi_cscr ) ) add_option('fupi_cscr', array());
    }
    
    if ( $module_id == 'reports' ) {
        $fupi_reports = get_option('fupi_reports');
        if ( empty( $fupi_reports ) ) add_option('fupi_reports', array());
    }
?>

<style>
    body{
        --fupi-require-text: '<?php esc_attr_e( 'Required', 'full-picture-analytics-cookie-notice' ); ?>';
    }
</style>

<div id="fupi_content" class="wrap <?php echo ' fupi_page_' . $module_id; ?>" data-licence="<?php echo $licence; ?>" data-is_premium_module="<?php echo $is_premium ? 'yes' : 'no'; ?>" data-msurl="<?php echo $multisite_url; ?>" data-page="<?php echo $module_id;?>" data-step="0" data-wp_nonce="<?php echo $wp_nonce; ?>">

    <h1></h1>
    <?php settings_errors();

    // GUIDES
    
    include_once 'parts/fupi-page_part-guides.php'; ?>

    <div class="fupi_adv_headline_html_template" style="display: none;">
        <div class="fupi_adv_headline">
            <div class="fupi_adv_headline_title"><?php esc_html_e( 'Extended integrations','full-picture-analytics-cookie-notice' ) ?></div>
            <p><?php esc_html_e( 'Extended integrations let you install tools, use their standard features and set up custom tracking.','full-picture-analytics-cookie-notice' ) ?></p>
        </div>
    </div>

    <div class="fupi_basic_headline_html_template" style="display: none;">
        <div class="fupi_basic_headline">
            <div class="fupi_basic_headline_title"><?php esc_html_e( 'Basic integrations','full-picture-analytics-cookie-notice' ) ?></div>
            <p><?php esc_html_e( 'Basic integrations only let you install tools and use their standard features.','full-picture-analytics-cookie-notice' ) ?></p>
        </div>
    </div>

    <div id="fupi_main">

        <?php include_once 'parts/fupi-page_part-side-nav.php'; ?>        

        <div id="fupi_main_col">

            <div id="fupi_help">
                <button type="button" id="fupi_help_btn" class="fupi_open_popup" data-popup="fupi_main_checklist_popup"><?php esc_html_e('Guides & Help' ,'full-picture-analytics-cookie-notice' ); ?> <span>i</span></button>
            </div>

            <?php if ( $module_id != 'status' ) { ?>
                <form id="fupi_settings_form" data-activetab="<?php echo $active_slug ?>" action="options.php" method="post">

                    <?php
                    // SETTINGS FIELDS

                    settings_fields( 'fupi_' . $active_slug );
                    do_settings_sections( 'fupi_' . $active_slug );

                    // SUBMIT BUTTON

                    if ( ! ( $module_id == 'track404' && $licence == 'free' ) ) { ?>
                        <div class="fupi_form_buttons_wrap">
                            <?php submit_button(); ?>
                        </div>
                    <?php }; 

                    // DEBUG DATA
                    
                    $fupi_main = get_option('fupi_main');
                    // $fupi_versions = get_option('fupi_versions');

                    if ( isset( $fupi_main['debug'] ) && $fupi_main['debug'] == 1 && $module_id != 'home' ) {

                        echo '<div id="fupi_option_debug_box">
                            <p>Option name: fupi_'. $active_slug .'</p>
                            <pre>' . print_r( get_option('fupi_' . $active_slug ), true ) . '</pre>
                        </div>';
                        // <div id="fupi_option_debug_box">
                        //     <p>Option name: fupi_versions</p>
                        //     <pre>' . print_r( $fupi_versions, true ) . '</pre>
                        // </div>';

                    } ?>
                </form>
            <?php } else {
                include_once 'parts/fupi-page-part-status.php';
            }; ?>

            <?php include_once 'parts/fupi-page_part-footer.php'; ?> 

        </div>
    </div>

    <?php include_once 'parts/fupi-page_part-offscreen.php'; ?> 
</div>
