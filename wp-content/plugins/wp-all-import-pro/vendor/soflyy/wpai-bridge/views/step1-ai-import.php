<?php
/**
 * Step 1 Automatic Setup - Modal iframe container
 *
 * This view provides a modal overlay with the Vercel app iframe
 * for automatic import configuration.
 *
 * The modal is triggered by JavaScript when user clicks "Set Up Automatically"
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get the LLM service URL
$llm_service_url = wpai_bridge_get_llm_service_url();

// Build iframe URL for Step 1
$iframe_url = $llm_service_url . '/step1';

/**
 * Build post types list matching WP All Import's filtering logic
 * See: wp-all-import-pro/views/admin/import/index.php
 */

// Post types to hide (same as WP All Import)
$hidden_posts = array(
    'attachment',
    'revision',
    'nav_menu_item',
    'shop_webhook',
    'import_users',
    'wp-types-group',
    'wp-types-user-group',
    'wp-types-term-group',
    'acf-field',
    'acf-field-group',
    'custom_css',
    'customize_changeset',
    'oembed_cache',
    'wp_block',
    'user_request',
    'scheduled-action',
    'wp_navigation',
);

// Get all post types (built-in + custom with show_ui)
$all_post_types = get_post_types( array( '_builtin' => true ), 'objects' )
                + get_post_types( array( '_builtin' => false, 'show_ui' => true ), 'objects' );

// Filter out hidden post types
foreach ( $all_post_types as $key => $pt ) {
    if ( in_array( $key, $hidden_posts, true ) ) {
        unset( $all_post_types[ $key ] );
    }
}

// Allow plugins to filter (same hook as WP All Import)
$all_post_types = apply_filters( 'pmxi_custom_types', $all_post_types, 'custom_types' );

// Also get hidden post types (show_ui = false) that might be importable
$hidden_post_types = get_post_types( array( '_builtin' => false, 'show_ui' => false ), 'objects' );
foreach ( $hidden_post_types as $key => $pt ) {
    if ( in_array( $key, $hidden_posts, true ) ) {
        unset( $hidden_post_types[ $key ] );
    }
}
$hidden_post_types = apply_filters( 'pmxi_custom_types', $hidden_post_types, 'hidden_post_types' );

// Merge all post types
$all_post_types = array_merge( $all_post_types, $hidden_post_types );

// Define preferred order (same as WP All Import)
$preferred_order = array(
    'post',
    'page',
    'product',
    'shop_order',
    'shop_coupon',
    'woo_reviews',
    'shop_customer',
    'gf_entries',
);

// Build ordered post types array
$post_types = array();
$added = array();

// Add preferred types first (if they exist)
foreach ( $preferred_order as $type_name ) {
    if ( isset( $all_post_types[ $type_name ] ) ) {
        $pt = $all_post_types[ $type_name ];
        $post_types[] = array(
            'name'  => $type_name,
            'label' => $pt->labels->name,
        );
        $added[] = $type_name;
    }
}

// Add remaining types alphabetically
$remaining = array();
foreach ( $all_post_types as $key => $pt ) {
    if ( ! in_array( $key, $added, true ) ) {
        $remaining[ $key ] = $pt;
    }
}
uasort( $remaining, function( $a, $b ) {
    return strcasecmp( $a->labels->name, $b->labels->name );
});
foreach ( $remaining as $key => $pt ) {
    $post_types[] = array(
        'name'  => $key,
        'label' => $pt->labels->name,
    );
}

/**
 * Build taxonomies list matching WP All Import's logic
 * Uses wp_all_import_get_taxonomies() if available, otherwise fallback
 */
$taxonomies = array();

if ( function_exists( 'wp_all_import_get_taxonomies' ) ) {
    // Use WP All Import's function directly
    $tax_options = wp_all_import_get_taxonomies();
    foreach ( $tax_options as $slug => $name ) {
        $taxonomies[] = array(
            'name'  => $slug,
            'label' => $name,
        );
    }
} else {
    // Fallback: get public taxonomies
    $all_taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

    // Preferred order for taxonomies
    $tax_order = array( 'category', 'post_tag', 'product_cat', 'product_tag' );
    $added_tax = array();

    // Add preferred taxonomies first
    foreach ( $tax_order as $tax_name ) {
        if ( isset( $all_taxonomies[ $tax_name ] ) ) {
            $tax = $all_taxonomies[ $tax_name ];
            $taxonomies[] = array(
                'name'  => $tax_name,
                'label' => $tax->labels->name,
            );
            $added_tax[] = $tax_name;
        }
    }

    // Add remaining taxonomies alphabetically
    $remaining_tax = array();
    foreach ( $all_taxonomies as $key => $tax ) {
        if ( ! in_array( $key, $added_tax, true ) ) {
            $remaining_tax[ $key ] = $tax;
        }
    }
    uasort( $remaining_tax, function( $a, $b ) {
        return strcasecmp( $a->labels->name, $b->labels->name );
    });
    foreach ( $remaining_tax as $key => $tax ) {
        $taxonomies[] = array(
            'name'  => $key,
            'label' => $tax->labels->name,
        );
    }
}

/**
 * Build add-on availability info matching WP All Import's checks
 * See: wp-all-import-pro/views/admin/import/index.php
 */
$has_woocommerce      = class_exists( 'WooCommerce' );
$has_user_addon       = class_exists( 'PMUI_Plugin' );
$has_woo_addon        = class_exists( 'PMWI_Plugin' );
$has_woo_addon_pro    = $has_woo_addon && defined( 'PMWI_EDITION' ) && PMWI_EDITION !== 'free';
$has_gf_addon         = class_exists( 'PMGI_Plugin' );
$has_gravity_forms    = class_exists( 'GFForms' );

$addons = array(
    // WooCommerce plugin active
    'woocommerce'      => $has_woocommerce,
    // User Import Add-On (required for Users, Customers)
    'user_addon'       => $has_user_addon,
    // WooCommerce Add-On (required for Products, Reviews)
    'woo_addon'        => $has_woo_addon,
    // WooCommerce Add-On Pro edition (required for Orders, Coupons)
    'woo_addon_pro'    => $has_woo_addon_pro,
    // Gravity Forms Add-On
    'gf_addon'         => $has_gf_addon,
    // Gravity Forms plugin active
    'gravity_forms'    => $has_gravity_forms,
);

// Purchase URLs for add-ons
$addon_urls = array(
    'user_addon'    => 'https://www.wpallimport.com/portal/discounts/?utm_source=import-plugin-pro&utm_medium=upgrade-notice&utm_campaign=user-import',
    'woo_addon'     => 'https://www.wpallimport.com/portal/discounts/?utm_source=import-plugin-pro&utm_medium=upgrade-notice&utm_campaign=woocommerce-import',
    'woo_addon_pro' => 'https://www.wpallimport.com/portal/discounts/?utm_source=import-plugin-pro&utm_medium=upgrade-notice&utm_campaign=woocommerce-import',
    'gf_addon'      => 'https://www.wpallimport.com/portal/discounts/?utm_source=import-plugin-pro&utm_medium=upgrade-notice&utm_campaign=gravity-forms-import',
);
?>

<style>
/* Modal overlay */
#wpai-step1-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 100000;
    backdrop-filter: blur(2px);
}

/* Modal container - 90% of viewport */
#wpai-step1-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90vw;
    height: 90vh;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    z-index: 100001;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Modal header */
#wpai-step1-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

#wpai-step1-modal-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}

#wpai-step1-modal-header h2 svg {
    width: 20px;
    height: 20px;
    color: #2d8a8b;
}

#wpai-step1-modal-close {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    border-radius: 6px;
    color: #646970;
    transition: all 0.15s ease;
}

#wpai-step1-modal-close:hover {
    background: #e5e7eb;
    color: #1f2937;
}

#wpai-step1-modal-close svg {
    width: 20px;
    height: 20px;
}

/* Modal body (iframe container) */
#wpai-step1-modal-body {
    flex: 1;
    position: relative;
    overflow: hidden;
}

#wpai-step1-ai-iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Loading state - light theme to match the Automatic Setup app */
#wpai-step1-modal-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    padding: 48px 16px 32px;
    background: #f0f0f1;
}

#wpai-step1-modal-loading.hidden {
    display: none;
}

.wpai-loading-card {
    width: 100%;
    max-width: 896px;
    height: fit-content;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 24px;
}

.wpai-loading-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.wpai-spinner {
    width: 32px;
    height: 32px;
    animation: wpai-spin 1s linear infinite;
}

.wpai-spinner svg {
    width: 100%;
    height: 100%;
}

.wpai-spinner .spinner-track {
    opacity: 0.25;
    stroke: #646970;
}

.wpai-spinner .spinner-arc {
    opacity: 0.9;
    fill: #425f9a;
}

@keyframes wpai-spin {
    to { transform: rotate(360deg); }
}

.wpai-loading-content span {
    color: #646970;
    font-size: 18px;
}

/* Responsive adjustments */
@media screen and (max-width: 782px) {
    #wpai-step1-modal {
        width: 95vw;
        height: 95vh;
        border-radius: 8px;
    }
}
</style>

<!-- Modal Structure -->
<div id="wpai-step1-modal-overlay">
    <div id="wpai-step1-modal">
        <div id="wpai-step1-modal-header">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/>
                </svg>
                <?php esc_html_e( 'Automatic Setup', 'wpai-ai-bridge-plugin' ); ?>
            </h2>
            <button id="wpai-step1-modal-close" type="button" title="<?php esc_attr_e( 'Close', 'wpai-ai-bridge-plugin' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18"/>
                    <path d="M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="wpai-step1-modal-body">
            <div id="wpai-step1-modal-loading">
                <div class="wpai-loading-card">
                    <div class="wpai-loading-content">
                        <div class="wpai-spinner">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle class="spinner-track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none"/>
                                <path class="spinner-arc" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                        </div>
                        <span><?php esc_html_e( 'Loading...', 'wpai-ai-bridge-plugin' ); ?></span>
                    </div>
                </div>
            </div>
            <iframe
                id="wpai-step1-ai-iframe"
                src="about:blank"
                allow="clipboard-write"
            ></iframe>
        </div>
    </div>
</div>

<script>
    // Configuration for the Step 1 modal and iframe
    window.wpaiStep1Config = {
        iframeUrl: <?php echo wp_json_encode( $iframe_url ); ?>,
        wpApiUrl: <?php echo wp_json_encode( rest_url( 'wp-all-import/v1' ) ); ?>,
        wpAdminUrl: <?php echo wp_json_encode( admin_url() ); ?>,
        wpRestNonce: <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>,
        ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
        consentNonce: <?php echo wp_json_encode( wp_create_nonce( 'wpai_bridge_consent' ) ); ?>,
        maxUploadSize: <?php echo wp_json_encode( wp_max_upload_size() ); ?>,
        postTypes: <?php echo wp_json_encode( $post_types ); ?>,
        taxonomies: <?php echo wp_json_encode( $taxonomies ); ?>,
        addons: <?php echo wp_json_encode( $addons ); ?>,
        addonUrls: <?php echo wp_json_encode( $addon_urls ); ?>,
        userHasConsented: <?php echo wp_json_encode( wpai_bridge_user_has_consented() ); ?>,
        bridgeVersion: <?php echo wp_json_encode( WPAI_BRIDGE_VERSION ); ?>,
        bridgeAuthToken: <?php echo wp_json_encode( WPAI_Bridge_FL_Signer::mint_session_token( rest_url( 'wp-all-import/v1' ) ) ); ?>,
    };
</script>

