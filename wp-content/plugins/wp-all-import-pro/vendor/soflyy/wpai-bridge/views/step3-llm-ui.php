<?php
/**
 * LLM Auto-Configuration UI for Step 3 (Template page)
 *
 * Variables available:
 * - $data['llm_mode'] - Whether in LLM configuration mode
 * - $data['llm_success'] - Whether LLM configuration was successful
 * - $data['llm_service_url'] - URL of the LLM service
 * - $data['import_id'] - Import ID
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$llm_mode = $data['llm_mode'];
$llm_success = $data['llm_success'];
$llm_service_url = $data['llm_service_url'];
$import_id = $data['import_id'];
?>

<!-- LLM Auto-Configuration UI -->
<div class="wpai-llm-config-container<?php if ($llm_mode && !$llm_success) echo ' wpai-iframe-active'; ?>">
	<!-- Iframe Container (visible in llm_mode, hidden in llm_success) -->
	<div class="wpai-llm-config-iframe-container"<?php if ($llm_success) echo ' style="display: none;"'; ?>>
		<iframe id="wpai-llm-config-iframe" src="about:blank"></iframe>
	</div>

	<!-- Error State -->
	<div class="wpai-llm-config-error" style="display: none;">
		<div class="wpai-error-card">
			<div class="wpai-error-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="10"></circle>
					<line x1="12" y1="8" x2="12" y2="12"></line>
					<line x1="12" y1="16" x2="12.01" y2="16"></line>
				</svg>
			</div>
			<div class="wpai-error-content">
				<h3 id="wpai-error-title"><?php _e('Setup Failed', 'wpai-ai-bridge-plugin'); ?></h3>
				<p id="wpai-error-message"><?php _e('Something went wrong while setting up your import.', 'wpai-ai-bridge-plugin'); ?></p>
				<!-- Additional guidance for specific errors -->
				<div id="wpai-error-details" class="wpai-error-details" style="display: none;"></div>
			</div>
			<div class="wpai-error-actions">
				<button type="button" class="wpallimport-large-button wpai-run-import-btn" id="wpai-retry-config">
					<?php _e('Try Again', 'wpai-ai-bridge-plugin'); ?>
				</button>
				<a href="<?php echo esc_url(admin_url('admin.php?page=pmxi-admin-import')); ?>" class="wpallimport-large-button wpai-secondary-btn">
					<?php _e('Start New Import', 'wpai-ai-bridge-plugin'); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Success State -->
	<div class="wpai-llm-config-success" style="display: none;">
		<div class="wpai-success-header">
			<div class="wpai-success-content">
				<h3><?php _e('Your import is ready', 'wpai-ai-bridge-plugin'); ?></h3>
				<h4 class="wpai-success-subtitle"><?php _e('Review the template below, preview the results, or run the import now', 'wpai-ai-bridge-plugin'); ?></h4>
			</div>

			<div class="wpai-success-actions">
				<button type="button" id="wpai-run-setup-again" class="back rad3 wpai-back-button">
					<?php esc_html_e( 'Set Up Again', 'wpai-ai-bridge-plugin' ); ?>
				</button>
				<button type="button" id="wpai-full-preview-btn-llm" class="button wpallimport-large-button wpai-preview-button">
					<?php _e('Preview', 'wpai-ai-bridge-plugin'); ?>
				</button>
				<button type="button" class="button wpallimport-large-button wpallimport-continue-button" id="wpai-run-import">
					<?php _e('Run Import', 'wpai-ai-bridge-plugin'); ?>
				</button>
			</div>
		</div>
	</div>
</div>
