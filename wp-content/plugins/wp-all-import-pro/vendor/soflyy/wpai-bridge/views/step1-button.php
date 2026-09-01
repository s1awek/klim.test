<?php
/**
 * Automatic Setup option for Step 1 (File Upload page)
 * Designed to match the WP All Import UI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<style>
.wpai-auto-setup-section {
    margin-top: 20px;
    margin-bottom: 25px;
}

.wpai-auto-setup-card {
    background: linear-gradient(135deg, #f8fbff 0%, #f0f7ff 100%);
    border: 2px solid #2d8a8b;
    border-radius: 4px;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.wpai-auto-setup-card:hover {
    border-color: #237273;
    box-shadow: 0 4px 12px rgba(45, 138, 139, 0.15);
    transform: translateY(-1px);
}

.wpai-auto-setup-card:active {
    transform: translateY(0);
}

.wpai-auto-setup-card.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #f5f5f5;
    border-color: #ccc;
}

.wpai-auto-setup-card.disabled:hover {
    transform: none;
    box-shadow: none;
}

.wpai-auto-setup-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.wpai-auto-setup-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #2d8a8b 0%, #237273 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.wpai-auto-setup-icon svg {
    width: 26px;
    height: 26px;
    color: white;
}

.wpallimport-plugin .wpai-auto-setup-card .wpai-auto-setup-content h3 {
    margin: 0;
    font-size: 20px;
    font-weight: normal;
    color: #2d8a8b;
}

.wpallimport-plugin .wpai-auto-setup-card .wpai-auto-setup-content p {
    margin: 0;
    font-size: 14px !important;
    font-weight: normal;
    color: #646970;
}

.wpai-auto-setup-badge {
    background: #2d8a8b;
    color: white;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 4px;
    margin-left: 10px;
    display: inline-block;
    vertical-align: middle;
}

.wpai-auto-setup-arrow {
    width: 32px;
    height: 32px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.wpai-auto-setup-card:hover .wpai-auto-setup-arrow {
    transform: translateX(3px);
}

.wpai-auto-setup-arrow svg {
    width: 16px;
    height: 16px;
    color: #2d8a8b;
}

.wpai-auto-setup-divider {
    display: flex;
    align-items: center;
    margin: 20px 0 15px 0;
    color: #646970;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.wpai-auto-setup-divider::before,
.wpai-auto-setup-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #ddd;
}

.wpai-auto-setup-divider span {
    padding: 0 15px;
}

/* Loading state */
.wpai-auto-setup-card.checking .wpai-auto-setup-icon {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
</style>

<div class="wpai-auto-setup-section">
    <a href="javascript:void(0);" id="wpai-configure-automatically-btn" class="wpai-auto-setup-card checking disabled" title="<?php esc_attr_e('Checking availability…', 'wpai-ai-bridge-plugin'); ?>">
        <div class="wpai-auto-setup-left">
            <div class="wpai-auto-setup-icon">
                <!-- Sparkles icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/>
                    <path d="M5 3v4"/>
                    <path d="M3 5h4"/>
                    <path d="M19 17v4"/>
                    <path d="M17 19h4"/>
                </svg>
            </div>
            <div class="wpai-auto-setup-content">
                <h3>
                    <?php _e('Set up automatically', 'wpai-ai-bridge-plugin'); ?>
                    <span class="wpai-auto-setup-badge"><?php _e('New', 'wpai-ai-bridge-plugin'); ?></span>
                </h3>
                <p><?php _e('Upload your file and have your import configured for you', 'wpai-ai-bridge-plugin'); ?></p>
            </div>
        </div>
        <div class="wpai-auto-setup-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </div>
    </a>

    <div class="wpai-auto-setup-divider">
        <span><?php _e('or set up manually', 'wpai-ai-bridge-plugin'); ?></span>
    </div>
</div>

