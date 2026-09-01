/**
 * Re-entry band handler - starts Automatic Setup for an already-configured import
 *
 * Mirrors the "Set Up Again" button on the success page: confirm, reset the
 * template to its pre-setup defaults, then open the Step 1 modal in reanalyze
 * mode. The modal establishes its own session, so no server-side hand-off is
 * involved.
 *
 * The reset is destructive and irreversible — it discards a working import
 * configuration — so the service is asked whether it can actually finish the job
 * BEFORE anything is discarded, and again at the moment of the click rather than
 * only at page load. Getting that order wrong leaves the worst possible outcome:
 * the old configuration gone and no new one to replace it.
 */

jQuery(document).ready(function($) {
    'use strict';

    const config = window.wpai_bridge_reentry || {};
    const i18n = config.i18n || {};

    const $band = $('.wpai-reentry-band');
    const $button = $('#wpai-start-automatic-setup');

    if (!$button.length) {
        return;
    }

    /**
     * Returns { ready, feature }. The body is read whether or not the response
     * was ok: an unavailable service and a switched-off one both answer 503, and
     * only the body tells them apart.
     */
    async function checkAvailability() {
        if (!config.llm_service_url) {
            // Nothing to ask. Treat as available so a missing localisation cannot
            // disable a working feature; the reset path still fails safe below.
            return { ready: true, feature: 'enabled' };
        }

        try {
            const url = `${config.llm_service_url}/api/health?wp_api_url=${encodeURIComponent(config.wp_api_url || '')}`;
            const response = await fetch(url, {
                method: 'GET',
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json().catch(() => ({}));

            return {
                ready: data.ready === true,
                // Absent means a service predating the switch, which cannot have
                // been switched off.
                feature: typeof data.feature === 'string' ? data.feature : 'enabled'
            };
        } catch (error) {
            WPAILogger.warn('[ReentryBand] Availability check failed:', error);
            // A network failure says nothing about the switch, but it does say we
            // cannot start — so it is not-ready without being switched-off.
            return { ready: false, feature: 'enabled' };
        }
    }

    function applyState(result) {
        // Withdrawn: remove the band entirely rather than greying it. A greyed
        // control reads as "try again later", which is the wrong promise, and the
        // band's whole purpose is to offer something no longer on offer.
        if (result.feature === 'disabled') {
            $button.addClass('disabled').prop('disabled', true);
            ($band.length ? $band : $button).hide();
            WPAILogger.debug('[ReentryBand] Automatic Setup is switched off');
            return;
        }

        if (result.ready) {
            $button.removeClass('disabled').prop('disabled', false).attr('title', '');
            return;
        }

        const message = result.feature === 'paused'
            ? (i18n.paused || i18n.unavailable)
            : i18n.unavailable;

        $button.addClass('disabled').prop('disabled', false).attr('title', message);
    }

    // Page load: the band starts unusable and is enabled only once the service
    // says it can serve. Rendering it live and finding out on click is what let a
    // configuration be discarded against a service that was never going to answer.
    $button.addClass('disabled').attr('title', i18n.checking || i18n.unavailable);
    (async function() {
        applyState(await checkAvailability());
    })();

    $button.on('click', async function(e) {
        e.preventDefault();

        const importId = parseInt($button.data('import-id'), 10);

        if (!importId) {
            WPAILogger.error('[ReentryBand] Button carries no import id');
            alert(i18n.unavailable);
            return;
        }

        if ($button.hasClass('disabled')) {
            alert($button.attr('title') || i18n.unavailable);
            return;
        }

        const originalText = $button.text();
        $button.text(i18n.checking || i18n.resetting).prop('disabled', true);

        // Re-check at the moment of the click. The page may have been open for
        // hours, and a switch flipped in between must not cost the user their
        // configuration.
        const availability = await checkAvailability();
        if (!availability.ready) {
            $button.text(originalText).prop('disabled', false);
            applyState(availability);
            alert(availability.feature === 'paused'
                ? (i18n.paused || i18n.unavailable)
                : i18n.unavailable);
            return;
        }

        // Only now, with the service confirmed able to finish, is it safe to ask
        // for confirmation and discard the existing configuration.
        if (!window.confirm(i18n.confirm)) {
            $button.text(originalText).prop('disabled', false);
            return;
        }

        if (typeof window.wpaiOpenStep1Modal !== 'function') {
            WPAILogger.error('[ReentryBand] Modal open function is unavailable');
            $button.text(originalText).prop('disabled', false);
            alert(i18n.unavailable);
            return;
        }

        $button.text(i18n.resetting);

        try {
            const response = await fetch(config.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: new URLSearchParams({
                    action: 'wpai_reset_template_to_defaults',
                    security: config.reset_nonce,
                    import_id: importId
                })
            });

            if (!response.ok) {
                throw new Error('Failed to reset template');
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.data?.message || 'Failed to reset template');
            }

            WPAILogger.debug('[ReentryBand] Template reset for import', importId);

            $button.text(originalText).prop('disabled', false);

            window.wpaiOpenStep1Modal({ reanalyze: true, importId: importId });
        } catch (error) {
            WPAILogger.error('[ReentryBand] Could not start Automatic Setup:', error);
            alert(i18n.reset_failed);
            $button.text(originalText).prop('disabled', false);
        }
    });
});
