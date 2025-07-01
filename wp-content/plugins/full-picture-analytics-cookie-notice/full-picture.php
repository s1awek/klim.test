<?php

/**
 * Plugin Name:       WP Full Picture
 * Plugin URI:        https://wpfullpicture.com/
 * Description:       All-in-1 privacy and analytics plugin. Install Google Analytics, Meta Pixel, GTM and other tools and use them according to privacy laws.
 * Version:           8.5.3.2
 * Requires at least: 5.4
 * Requires PHP:      7.4
 * Author:            Krzysztof Planeta
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       full-picture-analytics-cookie-notice
 * Domain Path:       /languages
*/
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'fupi_fs' ) ) {
    fupi_fs()->set_basename( false, __FILE__ );
} else {
    define( 'FUPI_VERSION', '8.5.3.2' );
    define( 'FUPI_URL', plugin_dir_url( __FILE__ ) );
    define( 'FUPI_PATH', __DIR__ );
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'fupi_fs' ) ) {
        // this will be removed in FREE version
        // Create a helper function for easy SDK access.
        function fupi_fs() {
            global $fupi_fs;
            if ( !isset( $fupi_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $fupi_fs = fs_dynamic_init( array(
                    'id'             => '5405',
                    'slug'           => 'full-picture-analytics-cookie-notice',
                    'premium_slug'   => 'full-picture-premium',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_2aee883bf3a3ae5559a119e92c744',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'anonymous_mode' => !function_exists( 'fupi_premium_test__premium_only' ),
                    'menu'           => array(
                        'slug'        => 'full_picture_tools',
                        'first-path'  => 'admin.php?page=full_picture_tools',
                        'contact'     => false,
                        'support'     => false,
                        'affiliation' => false,
                        'pricing'     => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $fupi_fs;
        }

        // Init Freemius.
        fupi_fs();
        // Signal that SDK was initiated.
        do_action( 'fupi_fs_loaded' );
    }
    // HIDE BILLING AND PAYMENT INFO FROM ACCOUNT
    fupi_fs()->add_filter( 'hide_billing_and_payments_info', '__return_true' );
    // CUSTOM FP ICON
    function fupi_fs_custom_icon() {
        return plugin_dir_path( __FILE__ ) . 'admin/assets/img/fp_logo_2_160.png';
    }

    fupi_fs()->add_filter( 'plugin_icon', 'fupi_fs_custom_icon' );
    // DISABLE DEACTIVATION FORM
    // fupi_fs()->add_filter( 'show_deactivation_feedback_form', '__return_false' );
    // ACTIVATE
    function activate_fupi() {
        // require_once plugin_dir_path( __FILE__ ) . 'includes/class-fupi-activator.php';
        require_once FUPI_PATH . '/admin/common/fupi-clear-cache.php';
        // Fupi_Activator::activate();
    }

    register_activation_hook( __FILE__, 'activate_fupi' );
    // DEACTIVATE
    function deactivate_fupi() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-fupi-deactivator.php';
        require_once FUPI_PATH . '/admin/common/fupi-clear-cache.php';
        Fupi_Deactivator::deactivate();
    }

    register_deactivation_hook( __FILE__, 'deactivate_fupi' );
    // ADD SETTINGS LINK TO THE ENTRY ON PLUGINS LIST
    function fupi_plugin_settings_link(  $links  ) {
        $settings_link = '<span><a href="admin.php?page=full_picture_tools">Settings</a></span>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'fupi_plugin_settings_link' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-fupi.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_fupi() {
        $plugin = new Fupi();
        $plugin->run();
    }

    run_fupi();
}