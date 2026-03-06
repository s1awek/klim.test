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
 * @since             5.2.23
 * @license           GPL-2.0+
 * @wordpress-plugin
 *
 * Plugin Name:       Smart Variations Images & Swatches for WooCommerce
 * Plugin URI:        https://www.smart-variations.com/
 * Description:       Enhance your WooCommerce store by adding multiple images to the product gallery and using them as variable product variations images effortlessly.
 * Version:           5.2.24
 * WC requires at least: 5.0
 * WC tested up to:   10.5.3
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
define( 'SMART_VARIATIONS_IMAGES_VERSION', '5.2.24' );
// Current plugin version.
define( 'WCSVFS_VERSION', '1.0' );
// Version for additional functionality.
define( 'SMART_SVI_DIR_URL', plugin_dir_url( __FILE__ ) );
// Plugin directory URL.
// Load minified assets when not in SCRIPT_DEBUG
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
                    'id'               => '2228',
                    'slug'             => 'smart-variations-images',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_6a5f1fc0c8ab537a0b07683099ada',
                    'is_premium'       => false,
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'trial'            => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'             => array(
                        'slug'       => 'woosvi-options-settings',
                        'first-path' => 'admin.php?page=woosvi-options-settings',
                        'support'    => false,
                        'network'    => true,
                        'parent'     => array(
                            'slug' => 'woocommerce',
                        ),
                    ),
                    'is_live'          => true,
                    'is_org_compliant' => true,
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
add_action( 'init', 'run_smart_variations_images' );
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
        update_user_meta( get_current_user_id(), 'svi_notice_dismissed_2', 'yes' );
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
    if ( get_user_meta( get_current_user_id(), 'svi_notice_dismissed_2', true ) ) {
        return;
    }
    $logo_url = SMART_SVI_DIR_URL . 'admin/images/svi.png';
    $is_free_user = svi_fs()->is_not_paying();
    $upgrade_url = ( method_exists( svi_fs(), 'get_upgrade_url' ) ? svi_fs()->get_upgrade_url( WP_FS__PERIOD_ANNUALLY ) : admin_url( 'admin.php?page=woosvi-options-settings-pricing&billing_cycle=annual' ) );
    ?>
    <style>
        #svi-review-notice {
            border: none !important;
            padding: 0 !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
            margin: 20px 20px 20px 0 !important;
        }
        #svi-review-notice .svi-notice-content {
            background: #fff;
            margin: 3px;
            padding: 25px;
            border-radius: 2px;
        }
        #svi-review-notice .svi-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f1;
        }
        #svi-review-notice .svi-logo {
            flex-shrink: 0;
            width: 70px;
            height: 70px;
            margin-right: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        #svi-review-notice h2 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #1e1e1e;
            font-weight: 600;
        }
        #svi-review-notice .svi-subtitle {
            margin: 0;
            color: #646970;
            font-size: 14px;
        }
        #svi-review-notice .svi-section {
            margin-bottom: 20px;
        }
        #svi-review-notice .svi-v6-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 15px;
        }
        #svi-review-notice .svi-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
            margin: 15px 0;
        }
        #svi-review-notice .svi-feature {
            display: flex;
            align-items: start;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 6px;
            border-left: 3px solid #667eea;
        }
        #svi-review-notice .svi-feature-icon {
            font-size: 20px;
            margin-right: 10px;
            flex-shrink: 0;
        }
        #svi-review-notice .svi-feature-text strong {
            display: block;
            color: #1e1e1e;
            margin-bottom: 2px;
        }
        #svi-review-notice .svi-feature-text {
            font-size: 13px;
            color: #646970;
            line-height: 1.5;
        }
        #svi-review-notice .svi-cta-box {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            color: #fff;
        }
        #svi-review-notice .svi-cta-box h3 {
            margin: 0 0 10px 0;
            color: #fff;
            font-size: 20px;
        }
        #svi-review-notice .svi-cta-box p {
            margin: 10px 0;
            font-size: 15px;
        }
        #svi-review-notice .svi-btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px 5px 0 5px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        #svi-review-notice .svi-btn-primary {
            background: #fff;
            color: #19547b;
        }
        #svi-review-notice .svi-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,255,255,0.3);
        }
        #svi-review-notice .svi-btn-secondary {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 2px solid #fff;
        }
        #svi-review-notice .svi-btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }
        #svi-review-notice .svi-footer {
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f1;
            color: #646970;
            font-size: 13px;
        }
        #svi-review-notice .svi-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        #svi-review-notice .svi-footer a:hover {
            text-decoration: underline;
        }
        #svi-review-notice .svi-pro-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 15px;
            margin: 10px 0 15px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        #svi-review-notice .svi-pro-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255,216,155,0.4);
        }
        #svi-review-notice .svi-pro-badge:after {
            content: ' ▼';
            font-size: 12px;
            margin-left: 5px;
        }
        #svi-review-notice .svi-pro-badge.expanded:after {
            content: ' ▲';
        }
        #svi-review-notice .svi-cta-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
        }
        #svi-review-notice .svi-cta-details.expanded {
            max-height: 1000px;
            transition: max-height 0.5s ease-in;
        }
    </style>
    <div class="notice is-dismissible" id="svi-review-notice">
        <div class="svi-notice-content">
            <div class="svi-header">
                <img src="<?php 
    echo esc_url( $logo_url );
    ?>" alt="Smart Variations Images & Swatches Logo" class="svi-logo">
                <div>
                    <h2>Smart Variations Images & Swatches</h2>
                    <p class="svi-subtitle">Thanks for using <strong>SVI</strong>! Since 2017, I've been working hard to make this plugin a powerful tool for your WooCommerce store.</p>
                </div>
            </div>

            <div class="svi-section">
                <span class="svi-v6-badge">🚀 Version 6 is Coming Soon!</span>
                <p style="margin: 10px 0; font-size: 15px; color: #1e1e1e;">A complete rebuild from the ground up. Here's what's coming:</p>
                
                <div class="svi-features">
                    <div class="svi-feature">
                        <span class="svi-feature-icon">✨</span>
                        <div class="svi-feature-text">
                            <strong>AI-Powered Options</strong>
                            Powered by WordPress 7.0 AI Client - smart automation features to save you time and effort. Stay tuned for the reveal!
                        </div>
                    </div>
                    <div class="svi-feature">
                        <span class="svi-feature-icon">🧩</span>
                        <div class="svi-feature-text">
                            <strong>Modular Addon Architecture</strong>
                            Choose only the features you need: Sliders (Splide/Swiper), Lightbox, Magnifier Lens, Video Support, Quick View, Swatches - all as independent modules
                        </div>
                    </div>
                    <div class="svi-feature">
                        <span class="svi-feature-icon">⚡</span>
                        <div class="svi-feature-text">
                            <strong>Zero Build Step Frontend</strong>
                            Modern vanilla JavaScript architecture - no webpack, no build tools. Faster loading, easier debugging, and better compatibility
                        </div>
                    </div>
                    <div class="svi-feature">
                        <span class="svi-feature-icon">🎨</span>
                        <div class="svi-feature-text">
                            <strong>Enhanced Gallery System</strong>
                            New data model with attribute-term galleries, variation-specific galleries, and global fallbacks for more flexible product displays
                        </div>
                    </div>
                    <div class="svi-feature">
                        <span class="svi-feature-icon">🔧</span>
                        <div class="svi-feature-text">
                            <strong>Professional Options Framework</strong>
                            Modern settings interface with per-addon configuration tabs, live validation, and dynamic CSS generation
                        </div>
                    </div>
                    <div class="svi-feature">
                        <span class="svi-feature-icon">🔄</span>
                        <div class="svi-feature-text">
                            <strong>Seamless Migration</strong>
                            Automatic migration from v5 with data backup - your existing galleries and settings transfer safely to the new architecture
                        </div>
                    </div>
                </div>
            </div>

            <?php 
    if ( $is_free_user ) {
        ?>
            <div class="svi-section" style="text-align: center;">
                <button class="svi-pro-badge" id="svi-toggle-pro">⚡ Unlock PRO Features — Limited Time Offer!</button>
                <div class="svi-cta-details" id="svi-cta-details">
                    <div class="svi-cta-box">
                        <p style="font-size: 16px; margin: 10px 0;"><strong>You're missing out on powerful PRO features:</strong></p>
                        <ul style="text-align: left; max-width: 600px; margin: 15px auto; font-size: 14px; line-height: 1.8;">
                            <li>✅ <strong>Video Support</strong> in variation galleries</li>
                            <li>✅ <strong>Advanced Sliders</strong> with custom controls & layouts</li>
                            <li>✅ <strong>Premium Lightbox</strong> & Magnifier options</li>
                            <li>✅ <strong>Variation Images in Cart</strong>, emails, and orders</li>
                            <li>✅ <strong>Import/Export</strong> galleries across products</li>
                            <li>✅ <strong>Priority Support</strong> & early access to updates</li>
                        </ul>
                        <p style="font-size: 17px; margin: 20px 0 10px 0;"><strong>🚀 Plus: Get V6 features at NO extra cost when you upgrade now!</strong></p>
                        <p style="font-size: 15px; margin: 10px 0;">V6 will launch at a <strong>higher price</strong> to reflect the massive improvements. Upgrade today and get access to all V6 features including AI-powered options when they launch!</p>
                        <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <p style="font-size: 16px; margin: 5px 0;">💰 <strong>Special Discount Code:</strong></p>
                            <p style="font-size: 22px; margin: 10px 0;"><strong style="background: rgba(255,255,255,0.4); padding: 8px 20px; border-radius: 5px; letter-spacing: 1px;">superthanks25</strong></p>
                            <p style="font-size: 15px; margin: 5px 0;"><strong>Save 25% OFF</strong> on annual plans!</p>
                        </div>
                        <a href="<?php 
        echo esc_url( $upgrade_url );
        ?>" class="svi-btn svi-btn-primary" style="font-size: 16px; padding: 14px 35px;">
                            🔥 Upgrade to PRO Now & Save 25%
                        </a>
                        <a href="https://svi.rosendo.pt/pro" target="_blank" class="svi-btn svi-btn-secondary" style="margin-top: 10px;">
                            View PRO Demo
                        </a>
                    </div>
                </div>
            </div>
            <?php 
    }
    ?>

            <div class="svi-footer">
                <p>
                    ⭐ Enjoying the plugin? 
                    <a href="https://wordpress.org/support/plugin/smart-variations-images/reviews/#new-post" target="_blank">Leave a review</a> 
                    – it helps others discover SVI and motivates me to keep improving it!
                </p>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document).on('click', '#svi-review-notice .notice-dismiss', function() {
                $.post(ajaxurl, { action: 'svi_dismiss_notice' }, function(response) {
                    console.log('Notice dismissed');
                });
            });
            
            // Toggle PRO features details
            $('#svi-toggle-pro').on('click', function() {
                var badge = $(this);
                var details = $('#svi-cta-details');
                
                badge.toggleClass('expanded');
                details.toggleClass('expanded');
            });
        });
    </script>
    <?php 
}

add_action( 'admin_notices', 'svi_plugin_review_notice' );