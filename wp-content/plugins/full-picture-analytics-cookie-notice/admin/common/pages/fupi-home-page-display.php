<?php

    $fupi_versions      = get_option('fupi_versions');
    $licence 			= fupi_fs()->can_use_premium_code() ? 'pro' : 'free';
    $sections_width_class = $licence == 'pro' ? 'd-col-span-3' : 'd-col-span-2';
    $active_tab   		= isset( $_GET[ 'tab' ] ) ? sanitize_html_class( $_GET[ 'tab' ] ) : false;
    $module_id          = $active_tab == 'gdpr_setup_helper' ? 'status' : 'home';
    $current_user_id    = get_current_user_id();
    $multisite_url 		= is_multisite() ? network_home_url() : '';
    $wp_nonce           = wp_create_nonce( 'wp_rest' );
    if ( fupi_fs()->is_not_paying() ) {
        $support_href = 'https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/';
    } else {
        $support_href = 'https://wpfullpicture.com/contact/';
    };

    // Advanced mode
    $default_adv_mode = ! empty( $fupi_versions['use_adv_mode'] );
    $user_adv_mode_meta = get_user_meta( $current_user_id, 'fupi_adv_mode', true ); 
	$user_adv_mode = $default_adv_mode;
	
	if ( ! empty ( $user_adv_mode_meta ) ) {
		$user_adv_mode = $user_adv_mode_meta == 'yes';
	}

    $adv_mode_class_suffix = $user_adv_mode ? 'on' : 'off';
?>

<div id="fupi_content" class="wrap fupi_page_<?php echo $module_id; ?> adv_mode_<?php echo $adv_mode_class_suffix; ?>" data-licence="<?php echo $licence; ?>" data-is_premium_module="no" data-msurl="<?php echo $multisite_url; ?>" data-page="home" data-step="0" data-wp_nonce="<?php echo $wp_nonce; ?>">

    <h1></h1>
    <?php settings_errors(); ?>
    
    <div id="fupi_extra_content">
        <?php include_once 'parts/fupi-page_part-guides.php'; ?>
    </div>

    <?php include_once 'parts/fupi-page_part-top-nav.php'; ?>

    <div id="fupi_main">       

        <div id="fupi_main_col">
            
            <?php 
            
            if ( $module_id == 'status' ) { 
                include_once 'parts/fupi-page-part-status.php';   
            } else { ?>
                
                <div id="fupi_home_header">
                    <?php /* <img id="fp_home_header_icon" src="<?php echo FUPI_URL . 'admin/assets/img/fp-ico.svg'; ?>"> */ ?>
                    <div id="fupi_home_header_title">
                        <h2>
                            <?php esc_html_e( 'Welcome to WP Full Picture', 'full-picture-analytics-cookie-notice' ); 
                                if ( $licence == 'pro' ) echo ' <span class="fupi_highlight">PRO</span>';
                            ?>
                        </h2>
                        <p><?php esc_html_e( 'Tracking manager for WordPress', 'full-picture-analytics-cookie-notice' ); ?></p>
                    </div>
                    <div id="fupi_home_header_img">
                        <img src="<?php echo FUPI_URL . 'admin/assets/img/home-hero.png'; ?>" aria-hidden="true">
                    </div>
                </div>

                <div class="fupi_home_sections fupi_grid">

                    <?php // FIRST STEPS ?>
                    
                    <div id="fupi_home_first_steps" class="fupi_home_section t-col-span-2 d-col-span-3">
                        <h3><?php esc_html_e( 'First steps', 'full-picture-analytics-cookie-notice' ); ?></h3>

                        <ol class="fupi_styled_ol"> 
                            <li>
                                <?php esc_html_e( 'Decide if you want to see advanced settings and modules (there\'s a switch in the top menu)', 'full-picture-analytics-cookie-notice' ); ?>
                            </li>
                            <li>
                                <?php printf( 
                                    esc_html__( '%1$sChoose and set up modules%2$s that you need', 'full-picture-analytics-cookie-notice' ),
                                    '<a href="' . admin_url('admin.php?page=full_picture_tools') . '">',
                                    '</a>'
                                ); ?>
                            </li>
                            <li>
                                <?php printf( 
                                    esc_html__( '%1$sApply recommended settings%2$s', 'full-picture-analytics-cookie-notice' ),
                                    '<a href="https://wpfullpicture.com/support/documentation/recommended-settings-for-wp-full-picture/">',
                                    '</a>'
                                ); ?>
                            </li>
                            <li>
                                <?php printf( 
                                    esc_html__( 'View recommendations from the %1$sGDPR Setup Helper%2$s', 'full-picture-analytics-cookie-notice' ),
                                    '<a href="' . admin_url('admin.php?page=full_picture_home&tab=gdpr_setup_helper') . '">',
                                    '</a>'
                                ); ?>
                            </li>
                            <li>
                                <?php esc_html_e( 'Scan for conflicts (in the section "Having issues" below)', 'full-picture-analytics-cookie-notice' ); ?>
                            </li>
                        </ol>
                    </div>

                    <?php // NEXT STEPS ?>

                    <div id="fupi_home_guides" class="fupi_home_section t-col-span-2 d-col-span-3">

                        <h3><?php esc_html_e( 'Next steps', 'full-picture-analytics-cookie-notice' ); ?></h3>

                        <p>
                            <?php echo sprintf( esc_html__('(Optionally) %1$sTranslate the consent banner%2$s to multiple languages', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-translate-consent-banner-in-wpml-polylang/" target="_blank">', '</a>' ); ?>
                        </p>
                        
                        <p><?php printf( 
                            esc_html__( 'Apply %1$srecommended tracking settings for WooCommerce%2$s', 'full-picture-analytics-cookie-notice' ),
                            '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-woocommerce-tracking-like-a-pro/">',
                            '</a>'
                        ); ?></p>

                        <p><?php printf( 
                            esc_html__( 'Improve conversion rates with %1$sreal-time site personalization%2$s', 'full-picture-analytics-cookie-notice' ),
                            '<a href="https://wpfullpicture.com/support/documentation/how-to-improve-conversion-rates-with-real-time-site-personalization/">',
                            '</a>'
                        ); ?></p>

                        <p><?php printf( 
                            esc_html__( '%1$sSet up Visitor Scoring%2$s to check where your best traffic comes from', 'full-picture-analytics-cookie-notice' ),
                            '<a href="https://wpfullpicture.com/support/documentation/how-to-use-lead-scoring/">',
                            '</a>'
                        ); ?></p>

                        <p><?php printf( 
                            esc_html__( '%1$sTips and tricks%2$s', 'full-picture-analytics-cookie-notice' ),
                            '<a href="https://wpfullpicture.com/support/documentation/tips-and-tricks/">',
                            '</a>'
                        ); ?></p>

                        <p style="text-align: right;">
                            <a href="https://wpfullpicture.com/support/documentation/" target="_blank" class="no_external_icon" style="font-weight: bold; text-decoration: none;"><?php esc_html_e( 'Documentation', 'full-picture-analytics-cookie-notice' ); ?> <span class="dashicons dashicons-arrow-right-alt2" style="position: relative; top: 3px;"></span></a>
                        </p>
                    </div>

                    <?php // SUPPORT ?>

                    <div id="fupi_home_support" class="fupi_home_section t-col-span-1 <?php echo $sections_width_class; ?>">
                        <h3><?php esc_html_e( 'Support', 'full-picture-analytics-cookie-notice' ); ?></h3>
                        <p>
                            <a href="https://wpfullpicture.com/support/documentation/" target="_blank"><?php esc_html_e( 'Documentation', 'full-picture-analytics-cookie-notice' ); ?></a>
                        </p>
                        <p>
                            <a href="<?php echo $support_href; ?>" target="_blank"><?php esc_html_e('Support', 'full-picture-analytics-cookie-notice'); ?></a>
                        </p>
                        <p>
                            <a href="https://wpfullpicture.com/releases/" target="_blank"><?php esc_html_e('Information on latest releases', 'full-picture-analytics-cookie-notice'); ?></a>
                        </p>
                        
                        <?php if ( $licence == 'free' ) { ?>
                            <p>
                                <a href="https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/" target="_blank"><?php esc_html_e( 'Community Forum', 'full-picture-analytics-cookie-notice' ); ?></a>
                            </p>
                        <?php }; ?>
                    </div>
                    
                    <?php // ISSUES ?>
                    
                    <div id="fupi_home_issues" class="fupi_home_section t-col-span-2 <?php echo $sections_width_class; ?>">
                        <h3><?php esc_html_e( 'Having issues?', 'full-picture-analytics-cookie-notice' ); ?></h3>

                        <p>
                            <button type="button" class="fupi_check_conflicts_btn button button-primary">
                                <?php esc_html_e( 'Check for conflicts', 'full-picture-analytics-cookie-notice' ); ?>
                            </button>
                        </p>

                        <p>
                            <a href="https://wpfullpicture.com/support/documentation/troubleshooting/" target="_blank">
                                <?php esc_html_e('View solutions to common problems', 'full-picture-analytics-cookie-notice'); ?>
                            </a>
                        </p>

                        <p><?php printf( 
                            esc_html__( '%1$sTest your setup with the Setup Helper%2$s', 'full-picture-analytics-cookie-notice' ),
                            '<a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">',
                            '</a>'
                        ); ?></p>
                        
                        <p><?php printf( esc_html__( '%1$sContact support%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="' . $support_href . '" target="_blank">', '</a>' ); ?></p>
                    </div>

                    <?php 

                    // PRO BANNER
                    
                    if ( ! fupi_fs()->can_use_premium_code() ) : ?>
                    <div id="fupi_home_getpro" class="fupi_home_section t-col-span-2 d-col-span-2 fupi_getpro_banner">
                        <div id="fupi_getpro_banner_unlock_icon"><span class="dashicons dashicons-performance"></span></div>
                        <h3><?php esc_html_e('Does this website make money?', 'full-picture-analytics-cookie-notice'); ?></h3>
                        <div id="fupi_getpro_content_wrap">
                            <p style="margin-top: 0;"><?php esc_html_e('Optimize your marketing and improve conversion rates with WP Full Picture PRO', 'full-picture-analytics-cookie-notice'); ?></p>
                            <a href="https://wpfullpicture.com/free-vs-pro/" class="button-primary no_external_icon" target="_blank" style="font-size: 18px;"><?php esc_html_e('Learn more', 'full-picture-analytics-cookie-notice'); ?></a>
                            <a href="https://wpfullpicture.com/pricing/" target="_blank" class="no_external_icon" style="color: lightblue; text-align: center; display: block; font-size: 18px;"><?php esc_html_e('View pricing', 'full-picture-analytics-cookie-notice'); ?></a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php }

            // FOOTER
            include_once 'parts/fupi-page_part-footer.php'; 
            ?> 

        </div>
    </div>

    <!-- Hidden popup content for conflict checker -->
    <div id="fupi_conflicts_popup_content" class="fupi_popup_content" data-style="popup" style="display:none;">
        <h3 style="margin-top: 0;"><?php esc_html_e( 'Conflict check', 'full-picture-analytics-cookie-notice' ); ?></h3>
        <div id="fupi_conflicts_results">
            <!-- Results will be inserted here via AJAX -->
        </div>
    </div>

    <?php include_once 'parts/fupi-page_part-offscreen.php'; ?> 
</div>
