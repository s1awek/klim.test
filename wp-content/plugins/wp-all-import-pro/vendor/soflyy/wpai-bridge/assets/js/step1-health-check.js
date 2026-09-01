/**
 * LLM Service Health Check and Configure Automatically Button Handler
 * For Step 1 (File Upload page)
 */

jQuery(document).ready(function($) {
    const LLM_SERVICE_URL = wpai_bridge_step1.llm_service_url;
    const WP_API_URL = wpai_bridge_step1.wp_api_url;

    // Track consent status (can be updated after user agrees)
    let userHasConsented = wpai_bridge_step1.user_has_consented;

    // Retry with a doubling delay: an outage lasts minutes, and a fixed short
    // poll from every open Step 1 page multiplies it into a traffic problem.
    const HEALTH_CHECK_INTERVAL = 10000;
    const HEALTH_CHECK_MAX_INTERVAL = 120000;
    const HEALTH_CHECK_MAX_RETRIES = 10;
    let healthCheckRetryCount = 0;
    let healthCheckDelay = HEALTH_CHECK_INTERVAL;
    let healthCheckTimer = null;
    let isHealthy = false;

    function stopHealthChecks() {
        if (healthCheckTimer) {
            clearTimeout(healthCheckTimer);
            healthCheckTimer = null;
        }
    }

    /**
     * Returns { ready, feature }. The body is read whether or not the response was
     * ok: an unavailable service and a switched-off one both answer 503, and only
     * the body tells them apart.
     */
    async function checkLLMServiceHealth() {
        try {
            const healthUrl = `${LLM_SERVICE_URL}/api/health?wp_api_url=${encodeURIComponent(WP_API_URL)}`;

            const response = await fetch(healthUrl, {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json().catch(() => ({}));

            if (!data.ready) {
                WPAILogger.warn('LLM service not ready:', {
                    status: response.status,
                    feature: data.feature,
                    services: data.services,
                    wordpress_error: data.wordpress_error
                });
            }

            return {
                ready: data.ready === true,
                // Absent means a service predating the switch, which cannot have
                // been switched off.
                feature: typeof data.feature === 'string' ? data.feature : 'enabled'
            };
        } catch (error) {
            WPAILogger.warn('LLM service health check failed:', error);
            // A network failure says nothing about the switch.
            return { ready: false, feature: 'enabled' };
        }
    }

    // Show consent modal
    function showConsentModal() {
        $('#wpai-consent-modal').fadeIn(200);
    }

    // Hide consent modal
    function hideConsentModal() {
        $('#wpai-consent-modal').fadeOut(200);
    }

    // Record user consent via AJAX
    async function recordConsent() {
        try {
            const response = await fetch(wpai_bridge_step1.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wpai_record_ai_consent',
                    security: wpai_bridge_step1.consent_nonce
                })
            });

            const result = await response.json();
            return result.success === true;
        } catch (error) {
            WPAILogger.error('Failed to record consent:', error);
            return false;
        }
    }

    // Proceed with Automatic Setup
    function proceedWithAutoConfig() {
        // Open the Automatic Setup modal
        if (typeof window.wpaiOpenStep1Modal === 'function') {
            window.wpaiOpenStep1Modal();
        } else {
            WPAILogger.error('[Step1HealthCheck] Modal open function not found');
            alert('Automatic Setup is not available. Please refresh the page and try again.');
        }
    }

    // Keeps the click handler's message in step with the card's.
    let cardState = 'checking';

    // Update card UI based on health status
    function updateCardState(healthy) {
        const $card = $('#wpai-configure-automatically-btn');

        if (healthy) {
            // Enable card if service is healthy
            $card.removeClass('checking disabled')
                .attr('title', wpai_bridge_step1.ready_text);
            // Restore original description
            $card.find('.wpai-auto-setup-content p').text(wpai_bridge_step1.ready_description || 'Upload your file and have your import configured for you');
        } else {
            const message = cardState === 'paused'
                ? (wpai_bridge_step1.paused_text || wpai_bridge_step1.unavailable_text)
                : wpai_bridge_step1.unavailable_text;

            // Keep disabled with error message
            $card.removeClass('checking')
                .addClass('disabled')
                .attr('title', message);
            // Update the description to show unavailable status
            $card.find('.wpai-auto-setup-content p').text(message);
        }
    }

    // Perform health check with retry logic
    async function performHealthCheck() {
        const result = await checkLLMServiceHealth();

        // Withdrawn: hide the card rather than greying it, since a greyed card
        // reads as "try again later". Nothing is stored here — the status poll
        // owns that, so a mangled response cannot hide it permanently.
        if (result.feature === 'disabled') {
            stopHealthChecks();
            cardState = 'disabled';
            // The whole section, not just the card: the "or set up manually"
            // divider belongs to it, and on its own it introduces an alternative
            // to something no longer on the page.
            const $card = $('#wpai-configure-automatically-btn');
            // Marked unusable as well as hidden: the click handler stays bound if
            // anything re-shows the container.
            $card.removeClass('checking').addClass('disabled');
            const $section = $card.closest('.wpai-auto-setup-section');
            ($section.length ? $section : $card).hide();
            WPAILogger.debug('[Step1HealthCheck] Automatic Setup is switched off');
            return result;
        }

        if (result.ready) {
            isHealthy = true;
            cardState = 'ready';
            stopHealthChecks();
            updateCardState(true);
            WPAILogger.debug('[Step1HealthCheck] Service is healthy');
            return result;
        }

        healthCheckRetryCount++;
        cardState = result.feature === 'paused' ? 'paused' : 'unavailable';
        updateCardState(false);

        if (healthCheckRetryCount >= HEALTH_CHECK_MAX_RETRIES) {
            stopHealthChecks();
            WPAILogger.warn('[Step1HealthCheck] Max retries reached, giving up');
            return result;
        }

        WPAILogger.debug(`[Step1HealthCheck] Not ready, retry ${healthCheckRetryCount}/${HEALTH_CHECK_MAX_RETRIES} in ${healthCheckDelay/1000}s`);
        scheduleHealthCheck();
        return result;
    }

    function scheduleHealthCheck() {
        stopHealthChecks();
        healthCheckTimer = setTimeout(async () => {
            if (!isHealthy) {
                await performHealthCheck();
            }
        }, healthCheckDelay);
        healthCheckDelay = Math.min(healthCheckDelay * 2, HEALTH_CHECK_MAX_INTERVAL);
    }

    // Initialize card state - start in checking state, then check with retries
    (async function() {
        const $card = $('#wpai-configure-automatically-btn');

        // If cache table is missing, permanently disable without health check
        if (!wpai_bridge_step1.table_exists) {
            $card.removeClass('checking')
                .addClass('disabled')
                .attr('title', wpai_bridge_step1.table_missing_text);
            $card.find('.wpai-auto-setup-content p').text(wpai_bridge_step1.table_missing_text);
            return;
        }

        // Start in checking state
        $card.addClass('checking disabled');

        await performHealthCheck();
    })();

    // Submit form for LLM configuration when the Automatic Setup card is clicked
    $('#wpai-configure-automatically-btn').on('click', function(e) {
        e.preventDefault();

        // Check if card is disabled
        if ($(this).hasClass('disabled')) {
            if (!wpai_bridge_step1.table_exists) {
                alert(wpai_bridge_step1.table_missing_text);
            } else if (cardState === 'paused') {
                alert(wpai_bridge_step1.paused_alert || wpai_bridge_step1.unavailable_alert);
            } else {
                alert(wpai_bridge_step1.unavailable_alert);
            }
            return;
        }

        // No file validation needed - the Vercel iframe will handle file selection
        // Consent is now handled in the Vercel UI (shown after template preparation but before LLM analysis)
        // This ensures no data is sent to the AI service until the user explicitly consents
        proceedWithAutoConfig();
    });

    // Consent modal: Cancel button
    $('#wpai-consent-cancel').on('click', function() {
        hideConsentModal();
    });

    // Consent modal: Backdrop click to close
    $('.wpai-consent-modal-backdrop').on('click', function() {
        hideConsentModal();
    });

    // Consent modal: Agree button
    $('#wpai-consent-agree').on('click', async function() {
        // Slide up the footer for a smooth transition
        $('.wpai-consent-modal-footer').slideUp(200);

        const success = await recordConsent();

        if (success) {
            userHasConsented = true;
            // Hide the consent modal before proceeding
            hideConsentModal();
            // Proceed with the auto-configuration
            proceedWithAutoConfig();
        } else {
            // Show footer again on error
            $('.wpai-consent-modal-footer').slideDown(200);
            alert(wpai_bridge_step1.consent_error);
        }
    });

    // Close modal on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#wpai-consent-modal').is(':visible')) {
            hideConsentModal();
        }
    });
});

