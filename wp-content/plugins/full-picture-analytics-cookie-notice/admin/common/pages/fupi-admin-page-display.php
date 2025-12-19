<?php

    $module_id    	    = sanitize_html_class( $_GET[ 'page' ] );
    $module_id    	    = str_replace( 'full_picture_', '', $module_id );
    $fupi_versions      = get_option('fupi_versions');
    $active_tab   		= isset( $_GET[ 'tab' ] ) ? sanitize_html_class( $_GET[ 'tab' ] ) : false;
    $consent_id         = ! empty( $_GET[ 'fupi_cons_id' ] ) ? sanitize_key( $_GET[ 'fupi_cons_id' ] ) : false;
    $active_slug        = empty( $active_tab ) ? $module_id : $active_tab;
    $is_premium         = false;
    $licence 			= fupi_fs()->can_use_premium_code() ? 'pro' : 'free';
    $current_module_data = [];
    $main_opts          = get_option('fupi_main');
    $current_user_id    = get_current_user_id();

    // Advanced mode

    // if a user updated from before 9.2, then we enable advanced mode by default
    // however, this can be overwritten if a user makes a choice using the switcher

    $default_adv_mode = ! empty( $fupi_versions['use_adv_mode'] );
    $user_adv_mode_meta = get_user_meta( $current_user_id, 'fupi_adv_mode', true ); 
	$user_adv_mode = $default_adv_mode;
	
	if ( ! empty ( $user_adv_mode_meta ) ) {
		$user_adv_mode = $user_adv_mode_meta == 'yes';
	}

    $adv_mode_class_suffix    = $user_adv_mode ? 'on' : 'off';
    
    // show welcome message if fupi_tools is empty
    $welcome_popup_id = $module_id === 'tools' && ( empty ( $this->tools ) || count( $this->tools ) == 0 ) ? 'fupi_first_steps_popup' : '';
    
    // Addons

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
    
    // FIX to prevent double sanitizing settings (double-encoding HTML entities which makes scripts unusable) in General settings (meta tags), Custom Scripts and Reports modules
    // https://core.trac.wordpress.org/ticket/21989)
    
    if ( $module_id == 'main' ) {
        if ( $main_opts === false ) add_option('fupi_main', array());
    }

    if ( $module_id == 'cscr' ) {
        $fupi_cscr = get_option('fupi_cscr');
        if ( $fupi_cscr === false ) add_option('fupi_cscr', array());
    }
    
    if ( $module_id == 'reports' ) {
        $fupi_reports = get_option('fupi_reports');
        if ( $fupi_reports === false ) add_option('fupi_reports', array());
    }

    if ( $active_slug == 'gdpr_setup_helper' ) $module_id = 'status';
?>

<style>
    body{
        --fupi-require-text: '<?php esc_attr_e( 'Required', 'full-picture-analytics-cookie-notice' ); ?>';
    }
</style>

<div id="fupi_content" class="wrap fupi_page_<?php echo $module_id; ?> adv_mode_<?php echo $adv_mode_class_suffix; ?>" data-licence="<?php echo $licence; ?>" data-is_premium_module="<?php echo $is_premium ? 'yes' : 'no'; ?>" data-msurl="<?php echo $multisite_url; ?>" data-page="<?php echo $module_id;?>" data-step="0" data-wp_nonce="<?php echo $wp_nonce; ?>" data-welcome_popup_id="<?php echo $welcome_popup_id?>">

    <h1></h1>
    <?php settings_errors();

    // GUIDES
    
    ?>
    
    <div id="fupi_extra_content">
    
        <?php include_once 'parts/fupi-page_part-guides.php'; ?>

        <?php if ( $module_id == 'tools' ) { ?>
            <div class="fupi_adv_headline_html_template fupi_tools_integr_section" style="display: none;">
                <div class="fupi_tools_integr_headline">
                    <div class="fupi_tools_integr_headline_title"><?php esc_html_e( 'Extended integrations','full-picture-analytics-cookie-notice' ) ?></div>
                    <p><?php esc_html_e( 'Extended integrations let you use basic and advanced functions of tracking tools.','full-picture-analytics-cookie-notice' ) ?></p>
                </div>
            </div>

            <div class="fupi_basic_headline_html_template fupi_tools_integr_section" style="display: none;">
                <div class="fupi_tools_integr_headline">
                    <div class="fupi_tools_integr_headline_title"><?php esc_html_e( 'Basic integrations','full-picture-analytics-cookie-notice' ) ?></div>
                    <p><?php esc_html_e( 'Basic integrations let you use only basic functions of tracking tools.','full-picture-analytics-cookie-notice' ) ?></p>
                </div>
            </div>

            <div class="fupi_tagman_headline_html_template fupi_tools_integr_section" style="display: none;">
                <div class="fupi_tools_integr_headline">
                    <div class="fupi_tools_integr_headline_title"><?php esc_html_e( 'Tag Managers','full-picture-analytics-cookie-notice' ) ?></div>
                    <p><?php esc_html_e( 'Tag managers let you install other tracking tools','full-picture-analytics-cookie-notice' ) ?></p>
                </div>
            </div>
        <?php }; ?>
    </div>

    <?php 
        include_once 'parts/fupi-page_part-top-nav.php'; 
    ?>

    <div id="fupi_main">

        <?php 
        include_once 'parts/fupi-page_part-side-nav.php'; 
        new Fupi_Build_Side_Nav($all_modules_data, $active_slug); 
        ?>        

        <div id="fupi_main_col">
            <?php 
            
            // Show GDPR setup helper or standard content
            
            if ( $active_slug == 'gdpr_setup_helper' ) { 
                include_once 'parts/fupi-page-part-status.php';   
            } if ( $active_slug == 'consents_list' ) {
                include_once 'parts/fupi-page_part-consents_list.php';
            } else { ?>
                <form id="fupi_settings_form" data-activetab="<?php echo $active_slug ?>" action="options.php" method="post">

                    <?php
                    // SETTINGS FIELDS

                    settings_fields( 'fupi_' . $active_slug );
                    do_settings_sections( 'fupi_' . $active_slug );

                    // SUBMIT BUTTON ?>
                    
                    <div class="fupi_form_buttons_wrap">
                        <?php submit_button(); ?>
                    </div>
                    
                    <?php 

                    // DEBUG DATA
                    
                    if ( defined( 'FUPI_TESTER' ) && FUPI_TESTER == true ) {
                        
                        echo '<div id="fupi_option_debug_box">
                            <p>Option name: fupi_'. $active_slug .'</p>
                            <pre>' . print_r( get_option('fupi_' . $active_slug ), true ) . '</pre>
                        </div>
                        <div id="fupi_option_debug_box">
                            <p>Option name: fupi_versions</p>
                            <pre>' . print_r( $fupi_versions, true ) . '</pre>
                        </div>';
                    }

                    ?>
                </form>
            <?php };

            // FOOTER
            include_once 'parts/fupi-page_part-footer.php'; ?> 

        </div>
    </div>

    <?php include_once 'parts/fupi-page_part-offscreen.php'; ?> 
</div>
