<?php
/**
 * Opt-in gate for the AI Bridge SDK.
 *
 * This is what a host plugin requires at file scope — not bootstrap.php directly.
 * It owns the setting end to end: the option, the Experimental section on WP All
 * Import's settings screen, saving it, and the decision to load the SDK at all.
 * A host therefore carries no copy of the feature's UI or wording, and free and
 * Pro cannot drift apart.
 *
 * While the setting is off, the SDK is never loaded, so nothing it registers
 * exists. Everything in this file is deliberately cheap enough to sit on every
 * request: the option is autoloaded, and the admin hooks are only added in admin.
 *
 * The host must render the settings seam:
 *
 *     do_action( 'pmxi_settings_sections', $post );
 *
 * between two of its own settings sections. Placement matters — WP All Import's
 * Save button lives inside the final section's row, so the seam belongs before
 * that section, not after it.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'WPAI_BRIDGE_GATE_LOADED' ) ) {
    return;
}
define( 'WPAI_BRIDGE_GATE_LOADED', true );

/**
 * Its own option rather than a key in PMXI_Plugin_Options, and autoloaded.
 * That array is stored with autoload off and WP All Import does not read it at
 * all on a front-end request, so reading it here would add a query to every page
 * view — a cost the disabled state is not supposed to carry.
 */
define( 'WPAI_BRIDGE_GATE_OPTION', 'wpai_experimental_ai_bridge' );

require_once __DIR__ . '/includes/remote-status.php';

/**
 * The setting AND whether the service is still offering the feature. A withdrawn
 * feature does not load at all; a paused one stays loaded, since it is expected
 * back and an import may be part-way through.
 *
 * Ordered so a site with the setting off reads only the one autoloaded option it
 * always has.
 */
if ( get_option( WPAI_BRIDGE_GATE_OPTION, 0 ) && 'disabled' !== wpai_bridge_remote_status() ) {
    require_once __DIR__ . '/bootstrap.php';
}

if ( is_admin() ) {
    add_action( 'pmxi_settings_sections', 'wpai_bridge_gate_render_section' );
    add_action( 'admin_init', 'wpai_bridge_gate_save' );
    add_action( 'admin_init', 'wpai_bridge_schedule_status_poll' );
    add_action( 'admin_init', 'wpai_bridge_prime_status_poll' );
}

/**
 * The Experimental section, rendered into the host's settings form.
 *
 * Structurally a sibling of the host's own sections — h3 then form-table, with
 * nothing between them — because anything extra reads as a different kind of
 * thing on that page. The experimental caveat therefore lives in the row's own
 * description rather than as a banner under the heading.
 *
 * The hidden 0 paired with the checkbox is what makes unchecking store 0 instead
 * of leaving the previous value behind.
 */
function wpai_bridge_gate_render_section() {
    $status = wpai_bridge_remote_status();

    // Withdrawn: no section at all. The stored setting is left untouched, so it
    // still applies if the feature is offered again.
    if ( 'disabled' === $status ) {
        return;
    }

    $enabled = (bool) get_option( WPAI_BRIDGE_GATE_OPTION, 0 );
    $paused  = ( 'paused' === $status );
    ?>
    <h3><?php esc_html_e( 'Experimental', 'wpai-ai-bridge-plugin' ); ?></h3>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label><?php esc_html_e( 'Automatic Setup', 'wpai-ai-bridge-plugin' ); ?></label></th>
                <td>
                    <?php
                    /**
                     * A pause never takes the control away from someone who has it
                     * on — that is how they withdraw consent — so it goes inert
                     * only when it is already off. The hidden companion goes with
                     * it: a disabled checkbox is not submitted but the hidden input
                     * beside it is, and the pair would post a 0 nobody chose.
                     */
                    $inert = ( $paused && ! $enabled );
                    ?>
                    <fieldset style="padding:0;">
                        <?php if ( ! $inert ) : ?>
                            <input type="hidden" name="experimental_ai_bridge" value="0"/>
                        <?php endif; ?>
                        <label for="experimental_ai_bridge">
                            <input type="checkbox" value="1" id="experimental_ai_bridge" name="experimental_ai_bridge" <?php checked( $enabled ); ?> <?php disabled( $inert ); ?>>
                            <?php esc_html_e( 'Enable Automatic Setup', 'wpai-ai-bridge-plugin' ); ?>
                        </label>
                    </fieldset>
                    <?php if ( $paused ) : ?>
                        <p class="description">
                            <?php esc_html_e( 'Automatic Setup is temporarily unavailable and cannot be switched on right now. It will return on its own.', 'wpai-ai-bridge-plugin' ); ?>
                        </p>
                    <?php else : ?>
                        <p class="description">
                            <?php esc_html_e( 'Adds Set Up Automatically to Step 1, which configures your import for you. Automatic Setup uploads your import file to our configuration service, which uses a third-party AI model to map your fields.', 'wpai-ai-bridge-plugin' ); ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

/**
 * Persist the setting when the host's settings form is submitted.
 *
 * The host filters its own POST against its options array, so it will not carry
 * a key it does not know about — this setting saves itself. Runs on admin_init,
 * which is before WP All Import dispatches its settings controller.
 *
 * Absence of the field is never an answer: `is_settings_submitted` and the
 * `edit-settings` nonce are also posted by WP All Export and the free WP All
 * Import, and neither form carries this field.
 */
function wpai_bridge_gate_save() {
    if ( empty( $_POST['is_settings_submitted'] ) ) {
        return;
    }
    if ( ! isset( $_POST['experimental_ai_bridge'] ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    check_admin_referer( 'edit-settings', '_wpnonce_edit-settings' );

    $enabling = ! empty( $_POST['experimental_ai_bridge'] );

    // Switching it off always works — it is how consent is withdrawn. Switching
    // it on is refused while the service is not offering it, so the setting
    // cannot claim a feature the site does not have.
    if ( $enabling && ! wpai_bridge_remote_status_is_serving() ) {
        return;
    }

    update_option( WPAI_BRIDGE_GATE_OPTION, $enabling ? 1 : 0, true );
}
