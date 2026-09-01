<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Whether the host WP All Import is one this SDK can drive.
 *
 * The floor differs by edition because the hooks the bridge needs landed in
 * different releases of each: 5.0.4-beta-1.0 in Pro, 4.2.0 in free.
 */
class WPAI_Bridge_Host {

    const MIN_PAID_VERSION = '5.0.4-beta-1.0';
    const MIN_FREE_VERSION = '4.2.0';

    public static function is_supported() {
        return '' === self::unsupported_reason();
    }

    /** '' when supported, otherwise 'missing' or 'outdated'. */
    public static function unsupported_reason() {
        if ( ! defined( 'PMXI_VERSION' ) || ! class_exists( 'PMXI_Plugin' ) ) {
            return 'missing';
        }
        $edition = defined( 'PMXI_EDITION' ) ? (string) PMXI_EDITION : '';
        return self::meets_floor( PMXI_VERSION, $edition ) ? '' : 'outdated';
    }

    public static function meets_floor( $version, $edition ) {
        $floor = ( 'free' === $edition ) ? self::MIN_FREE_VERSION : self::MIN_PAID_VERSION;
        return (bool) version_compare( (string) $version, $floor, '>=' );
    }

    public static function required_version_label() {
        $edition = defined( 'PMXI_EDITION' ) ? (string) PMXI_EDITION : '';
        return ( 'free' === $edition ) ? self::MIN_FREE_VERSION : '5.0.4';
    }

    public static function render_unsupported_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e( 'WP All Import - AI Bridge', 'wpai-ai-bridge-plugin' ); ?></strong>
                <?php
                printf(
                    /* translators: %s: minimum required WP All Import version. */
                    esc_html__( 'requires WP All Import %s or higher.', 'wpai-ai-bridge-plugin' ),
                    esc_html( self::required_version_label() )
                );
                ?>
            </p>
        </div>
        <?php
    }

    public static function render_table_failure_notice() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'pmxi' ) === false ) {
            return;
        }
        ?>
        <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 16px 20px; margin: 15px 0;">
            <h3 style="color: #425f9a; font-size: 15px; margin: 0 0 6px 0;">
                <?php esc_html_e( 'Automatic Setup: Database Table Required', 'wpai-ai-bridge-plugin' ); ?>
            </h3>
            <p style="color: #3c434a; font-size: 13px; line-height: 1.5; margin: 0 0 8px 0;">
                <?php esc_html_e( 'The plugin could not create its cache table. Automatic Setup will not work until this is resolved.', 'wpai-ai-bridge-plugin' ); ?>
            </p>
            <p style="color: #646970; font-size: 13px; line-height: 1.5; margin: 0;">
                <?php echo wp_kses_post( __( 'Ensure your database user has <strong>CREATE TABLE</strong> permissions, then reload this page.', 'wpai-ai-bridge-plugin' ) ); ?>
            </p>
        </div>
        <?php
    }
}
