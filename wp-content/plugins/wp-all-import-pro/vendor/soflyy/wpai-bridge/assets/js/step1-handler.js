/**
 * Step 1 Handler - Modal and iframe communication for Automatic Setup
 *
 * This handler manages:
 * - Opening/closing the modal
 * - Initializing the session token (same-origin, so cookies work)
 * - Passing config + token to iframe via postMessage
 * - Preparing template schema server-side ahead of the LLM config transition
 * - Handling redirect when LLM configuration is complete
 */

(function($) {
    'use strict';

    const config = window.wpaiStep1Config || {};

    const $overlay = $('#wpai-step1-modal-overlay');
    const $modal = $('#wpai-step1-modal');
    const $iframe = $('#wpai-step1-ai-iframe');
    const $loading = $('#wpai-step1-modal-loading');
    const $closeBtn = $('#wpai-step1-modal-close');

    let sessionToken = null;
    let isInitialized = false;
    let iframeReady = false;

    // Reanalyze mode state
    let isReanalyzeMode = false;
    let reanalyzeImportId = null;

    // Track if an import has been created (for close confirmation)
    let createdImportId = null;

    // Derive the expected iframe origin for secure postMessage communication
    const iframeOrigin = config.iframeUrl ? new URL(config.iframeUrl).origin : null;

    WPAILogger.debug('[Step1Handler] Initializing with config:', config);

    /**
     * Initialize session token via REST API (same-origin request)
     * This mirrors the session pattern in class-llm-config-api.php
     */
    async function initSession() {
        try {
            const response = await fetch(`${config.wpApiUrl}/step1/init`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.wpRestNonce,
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP ${response.status}`);
            }

            const data = await response.json();
            // Session response format: { success: true, session: { token, expires, expires_in } }
            sessionToken = data.session?.token || data.token;
            WPAILogger.debug('[Step1Handler] Session initialized, token received');
            return true;
        } catch (error) {
            WPAILogger.error('[Step1Handler] Failed to init session:', error);
            return false;
        }
    }

    /**
     * Prepare template schema server-side for seamless LLM config transition
     * This calls the PHP endpoint that renders all add-on sections and extracts the schema
     */
    async function prepareTemplate(importId) {
        WPAILogger.debug('[Step1Handler] Preparing template for import:', importId);

        try {
            const response = await fetch(`${config.wpApiUrl}/prepare-template/${importId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.wpRestNonce,
                    'X-WPAI-Session-Token': sessionToken,
                },
                credentials: 'same-origin',
            });

            // Get raw response text first to debug
            const rawText = await response.text();
            WPAILogger.debug('[Step1Handler] Raw response (first 500 chars):', rawText.substring(0, 500));

            if (!response.ok) {
                let errorData = {};
                try {
                    errorData = JSON.parse(rawText);
                } catch (e) {
                    WPAILogger.error('[Step1Handler] Response is not JSON:', rawText.substring(0, 1000));
                }
                throw new Error(errorData.message || `HTTP ${response.status}`);
            }

            let result;
            try {
                result = JSON.parse(rawText);
            } catch (e) {
                WPAILogger.error('[Step1Handler] Failed to parse JSON. Raw response:', rawText.substring(0, 2000));
                throw new Error('Invalid JSON response from server');
            }
            WPAILogger.debug('[Step1Handler] Template prepared:', result);
            return result.data;
        } catch (error) {
            WPAILogger.error('[Step1Handler] Failed to prepare template:', error);
            throw error;
        }
    }

    /**
     * Open the modal and load the iframe
     * @param {Object} options - Optional settings
     * @param {boolean} options.reanalyze - If true, open in reanalyze mode
     * @param {number} options.importId - Import ID for reanalyze mode
     */
    async function openModal(options = {}) {
        // Set reanalyze mode state
        isReanalyzeMode = options.reanalyze === true;
        reanalyzeImportId = options.importId || null;

        // Reset created import tracking for fresh sessions
        if (!isReanalyzeMode) {
            createdImportId = null;
        }

        WPAILogger.debug('[Step1Handler] Opening modal', { isReanalyzeMode, reanalyzeImportId });

        // Show modal
        $overlay.fadeIn(200);

        // Initialize session if not already done
        if (!sessionToken) {
            $loading.removeClass('hidden');
            const success = await initSession();
            if (!success) {
                alert('Could not start Automatic Setup. Please try again.');
                closeModal();
                return;
            }
        }

        // Determine iframe URL - add mode=reanalyze for reanalyze mode
        let iframeUrl = config.iframeUrl;
        if (isReanalyzeMode) {
            const url = new URL(iframeUrl);
            url.searchParams.set('mode', 'reanalyze');
            iframeUrl = url.toString();
        }

        // Load iframe if not already loaded or if switching modes
        if (!isInitialized || isReanalyzeMode) {
            $loading.removeClass('hidden');
            iframeReady = false; // Reset iframe ready state for reanalyze
            $iframe.attr('src', iframeUrl);
            isInitialized = true;
        } else if (iframeReady) {
            // Iframe already loaded and ready - just send config again and hide loading
            $loading.addClass('hidden');
            sendToIframe('wp_config', {
                apiUrl: config.wpApiUrl,
                ajaxUrl: config.ajaxUrl,
                nonce: config.wpRestNonce,
                sessionToken: sessionToken,
                maxUploadSize: config.maxUploadSize,
                postTypes: config.postTypes || [],
                taxonomies: config.taxonomies || [],
                addons: config.addons || {},
                addonUrls: config.addonUrls || {},
                bridgeVersion: config.bridgeVersion || '',
                bridgeAuthToken: config.bridgeAuthToken || '',
            });
        } else {
            // Iframe is loading but not ready yet
            $loading.removeClass('hidden');
        }
    }

    /**
     * Close the modal
     * Show confirmation if an import exists that isn't fully configured
     */
    function closeModal() {
        const adminUrl = config.ajaxUrl.replace('admin-ajax.php', 'admin.php');

        if (isReanalyzeMode && reanalyzeImportId) {
            // In reanalyze mode, the template has already been wiped
            // Show confirmation before redirecting
            const confirmed = confirm(
                'Your previous import configuration has been cleared for re-analysis.\n\n' +
                'If you close now, you will need to manually configure the import from the Manage Imports page.\n\n' +
                'Do you want to close and go to Manage Imports?'
            );

            if (confirmed) {
                WPAILogger.debug('[Step1Handler] User confirmed close in reanalyze mode, redirecting to manage imports');
                window.location.href = adminUrl + '?page=pmxi-admin-manage';
            }
            // If not confirmed, stay in the modal (do nothing)
            return;
        }

        if (createdImportId) {
            // An import was created but configuration wasn't completed
            // Show confirmation before closing
            const confirmed = confirm(
                'An import has been created but Automatic Setup is not complete.\n\n' +
                'If you close now, you will need to manually configure the import from the Manage Imports page.\n\n' +
                'Do you want to close and go to Manage Imports?'
            );

            if (confirmed) {
                WPAILogger.debug('[Step1Handler] User confirmed close with pending import, redirecting to manage imports');
                window.location.href = adminUrl + '?page=pmxi-admin-manage';
            }
            // If not confirmed, stay in the modal (do nothing)
            return;
        }

        // No import created yet, safe to close
        $overlay.fadeOut(200);
    }

    /**
     * Send message to iframe
     */
    function sendToIframe(type, data) {
        // Never post to an iframe that hasn't been navigated to the frontend yet -
        // an unset/about:blank src has no meaningful recipient origin and postMessage
        // will throw a target-origin mismatch.
        const src = $iframe.attr('src');
        if (!$iframe.length || !src || src === 'about:blank') {
            WPAILogger.debug('[Step1Handler] Skipping sendToIframe - iframe not navigated to frontend yet', { type, src });
            return;
        }
        WPAILogger.debug('[Step1Handler] Sending to iframe:', type, data);
        $iframe[0].contentWindow.postMessage({ type, data }, iframeOrigin);
    }

    /**
     * Listen for messages from iframe
     */
    window.addEventListener('message', async function(event) {
        // Only accept messages from the expected iframe origin
        if (!iframeOrigin || event.origin !== iframeOrigin) return;

        // Origin alone is not enough here: the Step 3 page also loads a second,
        // same-origin iframe (#wpai-llm-config-iframe) owned by step3-llm-config.js.
        // Require the message to actually come from THIS script's iframe.
        if (!$iframe.length || event.source !== $iframe[0].contentWindow) return;

        const { type, data } = event.data || {};

        if (!type) return;

        WPAILogger.debug('[Step1Handler] Received message:', type, data);

        switch (type) {
            case 'iframe_ready':
                // Mark iframe as ready and hide loading spinner
                iframeReady = true;

                // In reanalyze mode, immediately prepare template
                if (isReanalyzeMode && reanalyzeImportId) {
                    WPAILogger.debug('[Step1Handler] Reanalyze mode - preparing template immediately');
                    // Show loading state while preparing template
                    $loading.removeClass('hidden');
                    sendToIframe('preparing_template', { message: 'Preparing import template...' });

                    try {
                        // Call server-side template preparation
                        const templateData = await prepareTemplate(reanalyzeImportId);
                        WPAILogger.debug('[Step1Handler] Template prepared for reanalyze:', templateData);

                        // Hide loading
                        $loading.addClass('hidden');

                        // Send template data to iframe - this triggers the reanalyze instructions UI
                        // Include wp_api_url so the hook can properly populate wpConfig with userHasConsented
                        sendToIframe('template_prepared', {
                            import_id: reanalyzeImportId,
                            redirect_url: '', // Will be determined after config
                            token: templateData.token,
                            expires: templateData.expires,
                            import_file_url: templateData.import_file_url,
                            template_schema: templateData.template_schema,
                            user_has_consented: config.userHasConsented || false,
                            wp_api_url: config.wpApiUrl,
                            user_instructions: templateData.user_instructions || '',
                            bridge_version: config.bridgeVersion || '',
                            bridge_auth_token: config.bridgeAuthToken || '',
                        });
                    } catch (error) {
                        WPAILogger.error('[Step1Handler] Template preparation failed:', error);
                        $loading.addClass('hidden');
                        sendToIframe('wp_error', {
                            message: 'Template preparation failed: ' + error.message
                        });
                        alert('Template preparation failed: ' + error.message);
                    }
                } else {
                    // Normal step 1 mode - send config and wait for import_created
                    $loading.addClass('hidden');

                    // Send WordPress config + session token to iframe
                    // Uses same header name (X-WPAI-Session-Token) as Step 3 for consistency
                    sendToIframe('wp_config', {
                        apiUrl: config.wpApiUrl,
                        ajaxUrl: config.ajaxUrl,
                        nonce: config.wpRestNonce,
                        sessionToken: sessionToken, // Same name as Step 3 uses
                        maxUploadSize: config.maxUploadSize,
                        postTypes: config.postTypes || [],
                        taxonomies: config.taxonomies || [],
                        addons: config.addons || {},
                        addonUrls: config.addonUrls || {},
                        userHasConsented: config.userHasConsented || false,
                        bridgeVersion: config.bridgeVersion || '',
                        bridgeAuthToken: config.bridgeAuthToken || '',
                    });
                }
                break;

            case 'import_created':
                // Import was created - now prepare template and transition to LLM config
                WPAILogger.debug('[Step1Handler] Import created:', data.import_id);

                // Track that an import was created (for close confirmation)
                createdImportId = data.import_id;

                // Show loading state while preparing template
                $loading.removeClass('hidden');
                sendToIframe('preparing_template', { message: 'Preparing import template...' });

                try {
                    // Call server-side template preparation
                    WPAILogger.debug('[Step1Handler] Calling prepareTemplate...');
                    const templateData = await prepareTemplate(data.import_id);
                    WPAILogger.debug('[Step1Handler] prepareTemplate returned:', templateData);

                    // Hide loading
                    $loading.addClass('hidden');

                    // Send template data to iframe for seamless LLM config transition
                    sendToIframe('template_prepared', {
                        import_id: data.import_id,
                        redirect_url: data.redirect_url,
                        token: templateData.token,
                        expires: templateData.expires,
                        import_file_url: templateData.import_file_url,
                        template_schema: templateData.template_schema,
                        user_instructions: templateData.user_instructions || '',
                        bridge_version: config.bridgeVersion || '',
                        bridge_auth_token: config.bridgeAuthToken || '',
                    });
                } catch (error) {
                    WPAILogger.error('[Step1Handler] Template preparation failed:', error);
                    // Show error to user - no fallback, this must work
                    $loading.addClass('hidden');
                    sendToIframe('preparation_error', {
                        message: 'Template preparation failed: ' + error.message
                    });
                    alert('Template preparation failed: ' + error.message);
                }
                break;

            case 'llm_config_complete':
                // LLM configuration is complete
                if (data.action === 'auto_run') {
                    // Navigate to action=update with llm_auto_run flag
                    // The update page will show confirmation, but our JS will auto-submit it
                    const adminUrl = config.ajaxUrl.replace('admin-ajax.php', 'admin.php');
                    const updateUrl = adminUrl + '?page=pmxi-admin-manage&id=' + data.import_id + '&action=update&llm_auto_run=1';
                    WPAILogger.debug('[Step1Handler] Auto-run requested, navigating to:', updateUrl);
                    window.location.href = updateUrl;
                } else {
                    // Standard redirect to template/review page
                    WPAILogger.debug('[Step1Handler] LLM config complete, redirecting to:', data.redirect_url);
                    window.location.href = data.redirect_url;
                }
                break;

            case 'llm_navigate':
                // Navigate to specified URL (e.g., Review Configuration button)
                if (data.url) {
                    WPAILogger.debug('[Step1Handler] Navigate requested to:', data.url);
                    window.location.href = data.url;
                }
                break;

            case 'record_ai_consent':
                // Record user consent for AI data processing (from Vercel consent modal)
                WPAILogger.debug('[Step1Handler] Recording AI consent');
                fetch(config.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    credentials: 'same-origin',
                    body: new URLSearchParams({
                        action: 'wpai_record_ai_consent',
                        security: config.consentNonce,
                    }),
                }).then(response => response.json())
                  .then(data => {
                      WPAILogger.debug('[Step1Handler] Consent recorded:', data);
                  })
                  .catch(error => {
                      WPAILogger.error('[Step1Handler] Failed to record consent:', error);
                  });
                break;

            case 'refresh_session':
                // Iframe requests a fresh session token (proactive refresh or retry after 401)
                // The iframe sends import_id when it knows which import is active;
                // fall back to createdImportId (set on import_created) for backwards compat.
                await (async () => {
                    const importId = data?.import_id || createdImportId;
                    WPAILogger.debug('[Step1Handler] Refreshing session token', { importId, createdImportId, dataImportId: data?.import_id });
                    if (importId) {
                        // Import exists — refresh the import-specific token
                        try {
                            const response = await fetch(`${config.wpApiUrl}/import/${importId}/refresh-session`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-WP-Nonce': config.wpRestNonce,
                                },
                                credentials: 'same-origin',
                            });
                            if (response.ok) {
                                const result = await response.json();
                                sessionToken = result.token;
                                sendToIframe('session_refreshed', {token: sessionToken});
                                WPAILogger.debug('[Step1Handler] Import session refreshed for import', importId);
                            } else {
                                WPAILogger.error('[Step1Handler] Import session refresh failed:', response.status);
                                sendToIframe('session_refresh_failed', {error: 'Failed to refresh session'});
                            }
                        } catch (error) {
                            WPAILogger.error('[Step1Handler] Import session refresh error:', error);
                            sendToIframe('session_refresh_failed', {error: error.message || 'Refresh failed'});
                        }
                    } else {
                        // No import yet — refresh the Step 1 session token
                        const success = await initSession();
                        if (success && sessionToken) {
                            sendToIframe('session_refreshed', {token: sessionToken});
                            WPAILogger.debug('[Step1Handler] Step 1 session refreshed, new token sent to iframe');
                        } else {
                            WPAILogger.error('[Step1Handler] Session refresh failed');
                            sendToIframe('session_refresh_failed', {error: 'Failed to refresh session'});
                        }
                    }
                })();
                break;

            case 'cancel':
                // User cancelled - close modal
                closeModal();
                break;
        }
    });

    // Close modal on close button click
    $closeBtn.on('click', closeModal);

    // Close modal on overlay click (outside modal)
    $overlay.on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $overlay.is(':visible')) {
            closeModal();
        }
    });

    // Expose openModal function globally for the button click handler
    window.wpaiOpenStep1Modal = openModal;

    WPAILogger.debug('[Step1Handler] Ready');

})(jQuery);

