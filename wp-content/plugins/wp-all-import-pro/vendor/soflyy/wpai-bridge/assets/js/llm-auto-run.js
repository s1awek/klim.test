/**
 * LLM Auto-Run Handler
 * Auto-submits the WP All Import update confirmation form when llm_auto_run=1 is present
 */
(function() {
    'use strict';

    // Check if llm_auto_run parameter is present
    const urlParams = new URLSearchParams(window.location.search);
    const llmAutoRun = urlParams.get('llm_auto_run');

    if (llmAutoRun === '1') {
        WPAILogger.debug('[LLM Auto-Run] Detected llm_auto_run flag, will auto-submit form');

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', autoSubmitForm);
        } else {
            autoSubmitForm();
        }
    }

    function autoSubmitForm() {
        // Find the form with the "Create Posts" button
        // The update page has a form with a submit button and hidden input is_confirmed
        const form = document.querySelector('form input[name="is_confirmed"]')?.closest('form');

        if (form) {
            WPAILogger.debug('[LLM Auto-Run] Found confirmation form, submitting...');
            // Small delay to ensure page is fully loaded
            setTimeout(function() {
                form.submit();
            }, 500);
        } else {
            WPAILogger.warn('[LLM Auto-Run] Could not find confirmation form to auto-submit');
        }
    }
})();
