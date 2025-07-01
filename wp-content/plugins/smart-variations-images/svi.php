<?php

/**
 * Smart Variations Images & Swatches for WooCommerce
 *
 * A WooCommerce extension plugin that allows users to add multiple images to the product gallery
 * and use them as variable product variations images without needing to insert images per variation.
 *
 * @package           Smart_Variations_Images
 * @author            David Rosendo
 * @link              https://www.rosendo.pt
 * @since             5.2.16
 * @license           GPL-2.0+
 * @wordpress-plugin
 *
 * Plugin Name:       Smart Variations Images & Swatches for WooCommerce
 * Plugin URI:        https://www.smart-variations.com/
 * Description:       Enhance your WooCommerce store by adding multiple images to the product gallery and using them as variable product variations images effortlessly.
 * Version:           5.2.16
 * WC requires at least: 5.0
 * WC tested up to:   9.6.0
 * Author:            David Rosendo
 * Author URI:        https://www.rosendo.pt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc_svi
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * Define plugin constants.
 */
define( 'SMART_VARIATIONS_IMAGES_VERSION', '5.2.16' );
// Current plugin version.
define( 'WCSVFS_VERSION', '1.0' );
// Version for additional functionality.
define( 'SMART_SVI_DIR_URL', plugin_dir_url( __FILE__ ) );
// Plugin directory URL.
define( 'SMART_SCRIPT_DEBUG', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ) );
// Script debugging flag.
define( 'SMART_SVI_OPTIONS_CONTROL', '1' );
// Options control flag.
define( 'SMART_SVI_PROVS', '<span class="wpsfsvi-label label-warning">PRO VERSION</span>' );
// Pro version label.
// Check if Freemius is already initialized.
if ( function_exists( 'svi_fs' ) ) {
    svi_fs()->set_basename( false, __FILE__ );
    return;
} else {
    /**
     * Initialize Freemius SDK for premium features and licensing.
     *
     * @return Freemius
     */
    if ( !function_exists( 'svi_fs' ) ) {
        function svi_fs() {
            global $svi_fs;
            if ( !isset( $svi_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/includes/library/freemius/start.php';
                $svi_fs = fs_dynamic_init( array(
                    'id'             => '2228',
                    'slug'           => 'smart-variations-images',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_6a5f1fc0c8ab537a0b07683099ada',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'           => array(
                        'slug'       => 'woosvi-options-settings',
                        'first-path' => 'admin.php?page=woosvi-options-settings',
                        'support'    => false,
                        'network'    => true,
                        'parent'     => array(
                            'slug' => 'woocommerce',
                        ),
                    ),
                    'is_live'        => true,
                ) );
            }
            return $svi_fs;
        }

        // Initialize Freemius.
        svi_fs();
        // Signal that SDK was initiated.
        do_action( 'svi_fs_loaded' );
    }
}
/**
 * Include custom hooks for Freemius display.
 */
require plugin_dir_path( __FILE__ ) . 'includes/freemius_conditions.php';
/**
 * Activation hook for the plugin.
 *
 * This function runs during plugin activation and sets up necessary configurations.
 */
function activate_smart_variations_images() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-smart-variations-images-activator.php';
    Smart_Variations_Images_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_smart_variations_images' );
/**
 * Include the core plugin class and additional functionality.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-smart-variations-images.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wcsvfs.php';
/**
 * Begins execution of the plugin.
 *
 * Initializes the main plugin class and runs the plugin.
 */
function run_smart_variations_images() {
    $plugin = new Smart_Variations_Images();
    $plugin->run();
    $wcsvfs = new Wcsvfs($plugin->options);
    $wcsvfs->run();
}

/**
 * Main instance of the Smart_Variations_Images class.
 *
 * @return Smart_Variations_Images
 */
if ( !function_exists( 'WC_SVINST' ) ) {
    function WC_SVINST() {
        return Smart_Variations_Images::instance();
    }

}
/**
 * Main instance of the Wcsvfs class.
 *
 * @return Wcsvfs
 */
if ( !function_exists( 'WC_SVFS' ) ) {
    function WC_SVFS() {
        return Wcsvfs::instance();
    }

}
/**
 * Debugging function to print and die (for administrators only).
 *
 * @param mixed $args The data to debug.
 */
if ( !function_exists( 'fs_dd' ) ) {
    function fs_dd(  $args  ) {
        if ( current_user_can( 'administrator' ) ) {
            echo "<pre>" . print_r( $args, true ) . "</pre>";
            die;
        }
    }

}
/**
 * Debugging function to print (for administrators only).
 *
 * @param mixed $args The data to debug.
 */
if ( !function_exists( 'fs_ddd' ) ) {
    function fs_ddd(  $args  ) {
        if ( current_user_can( 'administrator' ) ) {
            echo "<pre>" . print_r( $args, true ) . "</pre>";
        }
    }

}
// Run the plugin after all plugins are loaded.
add_action( 'plugins_loaded', 'run_smart_variations_images', 99 );
/**
 * Declare compatibility with WooCommerce custom order tables.
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
/**
 * Enqueue scripts for the admin area.
 */
function svibo_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_add_inline_script( 'jquery', 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";', 'before' );
}

add_action( 'admin_enqueue_scripts', 'svibo_enqueue_scripts' );
/**
 * Handle dismissal of the admin notice.
 */
function svi_dismiss_notice() {
    if ( current_user_can( 'manage_options' ) ) {
        update_user_meta( get_current_user_id(), 'svi_notice_dismissed', 'yes' );
    }
    wp_die();
    // Properly close out the AJAX request.
}

add_action( 'wp_ajax_svi_dismiss_notice', 'svi_dismiss_notice' );
/**
 * Display a review notice in the admin area.
 */
function svi_plugin_review_notice() {
    // Check if the notice has been dismissed.
    if ( get_user_meta( get_current_user_id(), 'svi_notice_dismissed', true ) ) {
        return;
    }
    $logo_url = SMART_SVI_DIR_URL . 'admin/images/svi.png';
    ?>
    <div class="notice notice-success is-dismissible" id="svi-review-notice" style="padding: 10px; background-color: #fff; border-left: 4px solid #ffb900; box-shadow: 0 1px 1px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center;">
            <img src="<?php 
    echo esc_url( $logo_url );
    ?>" alt="Smart Variations Images & Swatches Logo" style="flex-shrink: 0; width: 60px; height: 60px; margin-right: 20px;">
            <div style="flex-grow: 1;">
                <h3 style="margin-top: 0; font-size: 1.4em;">Help Improve Smart Variations Images & Swatches</h3>
                <p>Thanks for using <strong>Smart Variations Images & Swatches</strong>! Since 2017, I've been working hard to make this plugin a powerful tool for your WooCommerce store. Your feedback and support are what keep this project moving forward.</p>
                <?php 
    // Show the special offer only for free version users.
    if ( svi_fs()->is_not_paying() ) {
        echo '<p><strong>Special Offer:</strong> Use the code <strong>superthanks25</strong> to get <strong>25% off</strong> the Pro annual plan. It’s a great way to unlock advanced features while supporting the plugin’s development.</p>';
    }
    ?>
                <p><strong>Your Review Matters:</strong> If you’re enjoying the plugin, take a moment to <a href="https://wordpress.org/support/plugin/smart-variations-images/reviews/#new-post" target="_blank" style="color: #0073aa; text-decoration: underline;">leave a review</a>. It helps others discover the plugin and motivates me to keep improving it.</p>
                <p><strong>What’s Coming Next:</strong> I’m currently working on <strong>version 6</strong>, which will bring even more features and improvements to enhance your WooCommerce experience.</p>
                <p>Thanks for being part of this journey. Let’s make <strong>Smart Variations Images & Swatches</strong> even better together!</p>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Use event delegation to ensure the button is found even if added dynamically.
            $(document).on('click', '#svi-review-notice .notice-dismiss', function() {
                $.post(ajaxurl, { action: 'svi_dismiss_notice' }, function(response) {
                    console.log('Notice dismissed');
                });
            });
        });
    </script>
    <?php 
}

add_action( 'admin_notices', 'svi_plugin_review_notice' );