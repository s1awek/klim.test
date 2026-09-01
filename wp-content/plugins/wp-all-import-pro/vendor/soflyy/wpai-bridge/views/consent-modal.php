<?php
/**
 * Data Processing Consent Modal
 *
 * Displayed when a user clicks "Configure Automatically" for the first time.
 * Explains that import file data will be sent to an external service that uses a third-party AI model.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="wpai-consent-modal" class="wpai-consent-modal" style="display: none;">
    <div class="wpai-consent-modal-backdrop"></div>
    <div class="wpai-consent-modal-content">
        <div class="wpai-consent-modal-header">
            <h2><?php esc_html_e( 'Data Processing Consent', 'wpai-ai-bridge-plugin' ); ?></h2>
        </div>
        <div class="wpai-consent-modal-body">
            <div class="wpai-consent-icon">
                <span class="dashicons dashicons-cloud-upload"></span>
            </div>
            <p class="wpai-consent-intro">
                <?php esc_html_e( 'Automatic Setup needs your consent before sending any of your data.', 'wpai-ai-bridge-plugin' ); ?>
            </p>
            <div class="wpai-consent-details">
                <h4><?php esc_html_e( 'What data is sent?', 'wpai-ai-bridge-plugin' ); ?></h4>
                <ul>
                    <li><?php esc_html_e( 'Your import file data (may include all records)', 'wpai-ai-bridge-plugin' ); ?></li>
                    <li><?php esc_html_e( 'The structure and field names from your file', 'wpai-ai-bridge-plugin' ); ?></li>
                    <li><?php esc_html_e( 'Available WordPress fields for the selected import type', 'wpai-ai-bridge-plugin' ); ?></li>
                </ul>
                <h4><?php esc_html_e( 'How is it used?', 'wpai-ai-bridge-plugin' ); ?></h4>
                <p>
                    <?php esc_html_e( 'Automatic Setup uploads your import file to our configuration service, which uses a third-party AI model to map your fields. Your import file and field configuration may be retained and used to improve and refine the service.', 'wpai-ai-bridge-plugin' ); ?>
                </p>
            </div>
        </div>
        <div class="wpai-consent-modal-footer">
            <button type="button" id="wpai-consent-cancel" class="button button-secondary">
                <?php esc_html_e( 'Cancel', 'wpai-ai-bridge-plugin' ); ?>
            </button>
            <button type="button" id="wpai-consent-agree" class="button button-primary">
                <?php esc_html_e( 'I Understand, Continue', 'wpai-ai-bridge-plugin' ); ?>
            </button>
        </div>
    </div>
</div>

<style>
.wpai-consent-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100100;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wpai-consent-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
}

.wpai-consent-modal-content {
    position: relative;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    max-width: 520px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.wpai-consent-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e0e0e0;
}

.wpai-consent-modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #1d2327;
}

.wpai-consent-modal-body {
    padding: 24px;
}

.wpai-consent-icon {
    text-align: center;
    margin-bottom: 16px;
}

.wpai-consent-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #2271b1;
}

.wpai-consent-intro {
    font-size: 15px;
    color: #50575e;
    text-align: center;
    margin-bottom: 20px;
}

.wpai-consent-details {
    background: #f6f7f7;
    border-radius: 6px;
    padding: 16px 20px;
    margin-bottom: 16px;
}

.wpai-consent-details h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.wpai-consent-details h4:not(:first-child) {
    margin-top: 16px;
}

.wpai-consent-details ul {
    margin: 0 0 0 20px;
    padding: 0;
}

.wpai-consent-details li {
    margin-bottom: 6px;
    color: #50575e;
    font-size: 13px;
}

.wpai-consent-details p {
    margin: 0;
    color: #50575e;
    font-size: 13px;
    line-height: 1.5;
}

.wpai-consent-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.wpai-consent-modal-footer .button {
    padding: 6px 16px;
    height: auto;
    font-size: 14px;
}

.wpai-consent-modal-footer .button-primary {
    background: #2271b1;
    border-color: #2271b1;
}

.wpai-consent-modal-footer .button-primary:hover {
    background: #135e96;
    border-color: #135e96;
}
</style>

