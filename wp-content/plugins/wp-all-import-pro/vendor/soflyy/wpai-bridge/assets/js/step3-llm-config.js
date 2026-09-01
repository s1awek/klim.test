jQuery(document).ready(function($) {
	const LLM_SERVICE_URL = wpai_bridge_step3.llm_service_url;
	const LLM_SERVICE_ORIGIN = new URL(LLM_SERVICE_URL).origin;
	const WP_API_URL = wpai_bridge_step3.wp_api_url;
	const IMPORT_ID = wpai_bridge_step3.import_id;
	const llmMode = wpai_bridge_step3.llm_mode;
	const AJAX_URL = wpai_bridge_step3.ajax_url;

	// Session data from Step 1
	const sessionData = {
		filePath: wpai_bridge_step3.file_path,
		source: wpai_bridge_step3.source,
		customType: wpai_bridge_step3.custom_type,
		isCsv: wpai_bridge_step3.is_csv,
		importId: IMPORT_ID
	};

	WPAILogger.debug('LLM Configuration Mode - Session Data:', sessionData);

	// Always reserve space for scrollbar in LLM mode to prevent layout shift when template section toggles
	$('html').css('overflow-y', 'scroll');

	let sessionToken = null;
	let configurationComplete = false;

	// IMMEDIATELY load the iframe with the Vercel app when in LLM mode (not success mode)
	// This ensures the user sees the Vercel UI right away, not a WordPress loading screen
	if (llmMode === '1') {
		const iframeUrl = new URL(LLM_SERVICE_URL + '/configure');
		iframeUrl.searchParams.set('wp_api', WP_API_URL);
		iframeUrl.searchParams.set('import_id', IMPORT_ID);
		iframeUrl.searchParams.set('preparing', '1');

		$('#wpai-llm-config-iframe').attr('src', iframeUrl.toString());
		WPAILogger.debug('Iframe loaded immediately with URL:', iframeUrl.toString());
	}

	/**
	 * Load all ACF field groups by checking all checkboxes and waiting for AJAX to complete
	 * This ensures all ACF fields are included in the template sent to the LLM
	 * Returns a promise that resolves when all groups are loaded
	 */
	function loadAllAcfFieldGroups() {
		return new Promise(function(resolve) {
			// Diagnostic: Check for ACF section and checkboxes
			const $acfSection = $('.pmai_options');
			WPAILogger.debug('ACF Diagnostic: ACF section found:', $acfSection.length > 0);
			if ($acfSection.length > 0) {
				WPAILogger.debug('ACF Diagnostic: ACF section visible:', $acfSection.is(':visible'));
				WPAILogger.debug('ACF Diagnostic: ACF section closed:', $acfSection.hasClass('closed'));
			}

			const $allAcfCheckboxes = $('.pmai_acf_group');
			WPAILogger.debug('ACF Diagnostic: Total ACF checkboxes found:', $allAcfCheckboxes.length);

			const $acfCheckboxes = $('.pmai_acf_group:not(:checked)');
			WPAILogger.debug('ACF Diagnostic: Unchecked ACF checkboxes found:', $acfCheckboxes.length);

			if ($acfCheckboxes.length === 0) {
				// Log the reason for no unchecked checkboxes
				if ($allAcfCheckboxes.length === 0) {
					WPAILogger.debug('No ACF field groups exist on this page (no .pmai_acf_group elements)');
				} else {
					WPAILogger.debug('All ACF field groups are already checked');
					$allAcfCheckboxes.each(function(i) {
						WPAILogger.debug('  ACF group ' + i + ':', $(this).attr('name'), 'rel=' + $(this).attr('rel'));
					});
				}
				resolve();
				return;
			}

			WPAILogger.debug('Loading ' + $acfCheckboxes.length + ' ACF field groups...');

			let loadedCount = 0;
			const totalToLoad = $acfCheckboxes.length;
			let resolved = false;
			let fallbackTimeoutId = null;

			// Listen for AJAX completion for each group
			$(document).on('ajaxComplete.acfLoader', function(event, xhr, settings) {
				// Check if this is an ACF group load request
				// For GET requests, jQuery may serialize data into the URL, so check both settings.data and settings.url
				var isAcfRequest = false;
				if (settings.data) {
					if (typeof settings.data === 'string') {
						isAcfRequest = settings.data.includes('action=get_acf');
					} else if (typeof settings.data === 'object') {
						isAcfRequest = settings.data.action === 'get_acf';
					}
				}
				// Also check the URL for GET requests where data is serialized into query string
				if (!isAcfRequest && settings.url && settings.url.includes('action=get_acf')) {
					isAcfRequest = true;
				}
				if (isAcfRequest && !resolved) {
					loadedCount++;
					WPAILogger.debug('ACF group loaded: ' + loadedCount + '/' + totalToLoad);

					// Diagnostic: Log AJAX response details
					try {
						var response = xhr.responseJSON || JSON.parse(xhr.responseText);
						if (response && response.html) {
							var htmlLength = response.html.length;
							var fieldCount = (response.html.match(/class="field field_type/g) || []).length;
							WPAILogger.debug('ACF Diagnostic: Response HTML length=' + htmlLength + ', fields detected=' + fieldCount);
						} else {
							WPAILogger.debug('ACF Diagnostic: Response has no HTML or is empty');
						}
					} catch (e) {
						WPAILogger.debug('ACF Diagnostic: Could not parse response', e);
					}

					if (loadedCount >= totalToLoad) {
						// All groups loaded, clean up and resolve
						resolved = true;
						$(document).off('ajaxComplete.acfLoader');
						if (fallbackTimeoutId) clearTimeout(fallbackTimeoutId);
						WPAILogger.debug('All ACF field groups loaded');
						// Give a small delay for DOM to update
						setTimeout(resolve, 100);
					}
				}
			});

			// Check all unchecked ACF group checkboxes to trigger loading
			$acfCheckboxes.each(function() {
				const $checkbox = $(this);
				// Check the checkbox - this triggers the change event which loads the group
				$checkbox.prop('checked', true).trigger('change');
			});

			// Fallback timeout in case AJAX events don't fire as expected
			fallbackTimeoutId = setTimeout(function() {
				if (!resolved) {
					resolved = true;
					$(document).off('ajaxComplete.acfLoader');
					WPAILogger.debug('ACF loading timeout - proceeding with available fields');
					resolve();
				}
			}, 10000); // 10 second timeout
		});
	}

	/**
	 * Load all Toolset Types field groups by checking all checkboxes and waiting for AJAX to complete
	 * Similar to ACF, Toolset uses .pmti_toolset_group checkboxes that trigger get_toolset AJAX
	 */
	function loadAllToolsetFieldGroups() {
		return new Promise(function(resolve) {
			const $toolsetCheckboxes = $('.pmti_toolset_group:not(:checked)');

			if ($toolsetCheckboxes.length === 0) {
				WPAILogger.debug('No unchecked Toolset field groups to load');
				resolve();
				return;
			}

			WPAILogger.debug('Loading ' + $toolsetCheckboxes.length + ' Toolset field groups...');

			let loadedCount = 0;
			const totalToLoad = $toolsetCheckboxes.length;
			let resolved = false;
			let fallbackTimeoutId = null;

			// Listen for AJAX completion for each group
			$(document).on('ajaxComplete.toolsetLoader', function(event, xhr, settings) {
				// Check if this is a Toolset group load request
				// For GET requests, jQuery may serialize data into the URL, so check both settings.data and settings.url
				var isToolsetRequest = false;
				if (settings.data) {
					if (typeof settings.data === 'string') {
						isToolsetRequest = settings.data.includes('action=get_toolset');
					} else if (typeof settings.data === 'object') {
						isToolsetRequest = settings.data.action === 'get_toolset';
					}
				}
				// Also check the URL for GET requests where data is serialized into query string
				if (!isToolsetRequest && settings.url && settings.url.includes('action=get_toolset')) {
					isToolsetRequest = true;
				}
				if (isToolsetRequest && !resolved) {
					loadedCount++;
					WPAILogger.debug('Toolset group loaded: ' + loadedCount + '/' + totalToLoad);

					if (loadedCount >= totalToLoad) {
						resolved = true;
						$(document).off('ajaxComplete.toolsetLoader');
						if (fallbackTimeoutId) clearTimeout(fallbackTimeoutId);
						WPAILogger.debug('All Toolset field groups loaded');
						setTimeout(resolve, 100);
					}
				}
			});

			// Check all unchecked Toolset group checkboxes to trigger loading
			$toolsetCheckboxes.each(function() {
				const $checkbox = $(this);
				$checkbox.prop('checked', true).trigger('change');
			});

			// Fallback timeout
			fallbackTimeoutId = setTimeout(function() {
				if (!resolved) {
					resolved = true;
					$(document).off('ajaxComplete.toolsetLoader');
					WPAILogger.debug('Toolset loading timeout - proceeding with available fields');
					resolve();
				}
			}, 10000);
		});
	}

	/**
	 * Load field groups for add-ons using the new PMXI_Addon_Base API (JetEngine, MetaBox, etc.)
	 * These add-ons use .wpallimport-import-group-checkbox checkboxes and fire 'pmxi-group-loaded' events
	 * Note: These add-ons use native JS event listeners, not jQuery, so we must dispatch native events
	 */
	function loadAllNewApiAddonFieldGroups() {
		return new Promise(function(resolve) {
			// Find all unchecked group checkboxes from new API add-ons
			const addonCheckboxes = document.querySelectorAll('.pmxi-addon .wpallimport-import-group-checkbox:not(:checked)');

			if (addonCheckboxes.length === 0) {
				WPAILogger.debug('No unchecked new API add-on field groups to load');
				resolve();
				return;
			}

			WPAILogger.debug('Loading ' + addonCheckboxes.length + ' new API add-on field groups...');

			let loadedCount = 0;
			const totalToLoad = addonCheckboxes.length;
			let resolved = false;
			let fallbackTimeoutId = null;

			// Listen for the custom event that fires when a group is loaded
			const groupLoadedHandler = function(event) {
				if (resolved) return;
				loadedCount++;
				WPAILogger.debug('New API add-on group loaded: ' + loadedCount + '/' + totalToLoad);

				if (loadedCount >= totalToLoad) {
					resolved = true;
					window.removeEventListener('pmxi-group-loaded', groupLoadedHandler);
					if (fallbackTimeoutId) clearTimeout(fallbackTimeoutId);
					WPAILogger.debug('All new API add-on field groups loaded');
					setTimeout(resolve, 100);
				}
			};

			window.addEventListener('pmxi-group-loaded', groupLoadedHandler);

			// Check all unchecked group checkboxes and dispatch native change event
			// The new API add-ons use native event listeners, not jQuery
			addonCheckboxes.forEach(function(checkbox) {
				checkbox.checked = true;
				checkbox.dispatchEvent(new Event('change', { bubbles: true }));
			});

			// Fallback timeout
			fallbackTimeoutId = setTimeout(function() {
				if (!resolved) {
					resolved = true;
					window.removeEventListener('pmxi-group-loaded', groupLoadedHandler);
					WPAILogger.debug('New API add-on loading timeout - proceeding with available fields');
					resolve();
				}
			}, 10000);
		});
	}

	/**
	 * Load all add-on field groups (ACF, Toolset, JetEngine, MetaBox, etc.)
	 * This ensures all available fields from all add-ons are included in the template
	 */
	async function loadAllAddonFieldGroups() {
		// Wait a moment for other document.ready handlers to execute
		// This ensures add-on event handlers (ACF, Toolset, etc.) are attached
		// before we trigger checkbox changes. Without this delay, our code might
		// run before the add-on's event handlers are attached, causing .trigger('change')
		// to do nothing.
		await new Promise(resolve => setTimeout(resolve, 200));
		WPAILogger.debug('Add-on initialization delay complete, loading field groups...');

		// Load ACF field groups (legacy API)
		await loadAllAcfFieldGroups();

		// Load Toolset Types field groups (legacy API)
		await loadAllToolsetFieldGroups();

		// Load JetEngine, MetaBox, and other new API add-on field groups
		await loadAllNewApiAddonFieldGroups();

		// Note: Other add-ons like WooCommerce, User, Yoast, Gravity Forms
		// typically render their fields directly without AJAX loading,
		// so they should already be present in the DOM
		WPAILogger.debug('All add-on field groups loaded');
	}

	// Check if we're returning from a successful LLM configuration
	const urlParams = new URLSearchParams(window.location.search);
	const llmSuccess = urlParams.get('llm_success');

	if (llmSuccess === '1') {
		WPAILogger.debug('Returning from successful LLM configuration - showing success state');

		// Hide the loading status
		$('.wpai-llm-config-status').hide();

		// Show the success message
		$('.wpai-llm-config-success').show();

		// Keep the normal template content hidden - user can toggle it with the manual config button
		// (it's already populated from session, just hidden)

		// Mark configuration as complete
		configurationComplete = true;

		// Keep llm_success in URL so user-initiated reloads maintain the success state

		// Load add-on field groups that were checked by the LLM
		// ACF and Toolset only load groups when the section is visible and the change event fires
		// Since we set the checkboxes in the database, we need to manually trigger loading for these legacy addons
		loadCheckedAcfFieldGroups();
		loadCheckedToolsetFieldGroups();

		// NOTE: We do NOT call loadCheckedNewApiAddonFieldGroups() here because
		// the new addon API (JetEngine, MetaBox) automatically loads checked groups on DOMContentLoaded
		// Calling it here would cause duplicate sections to load
	} else if (llmMode === '1') {
		// Only start the configuration process if we're in llm_mode (not llm_success)
		initializeLLMConfiguration();
	}

	/**
	 * Load ACF field groups that are already checked in the database
	 * This is needed because the ACF Add-on only loads groups when:
	 * 1. The section is visible (it starts closed)
	 * 2. The change event fires on the checkbox
	 * When we set checkboxes via the LLM API, neither condition is met on page load
	 */
	function loadCheckedAcfFieldGroups() {
		const $checkedGroups = $('.pmai_acf_group:checked');

		if ($checkedGroups.length === 0) {
			WPAILogger.debug('No checked ACF field groups to load');
			return;
		}

		WPAILogger.debug('Loading ' + $checkedGroups.length + ' checked ACF field group(s)...');

		// First, open the ACF section if it's closed
		// The ACF Add-on's pmai_reset_acf_groups() only loads groups when .pmai_options:visible
		const $acfSection = $('.pmai_options.wpallimport-collapsed');
		if ($acfSection.hasClass('closed')) {
			WPAILogger.debug('Opening ACF section...');
			$acfSection.removeClass('closed');
			$acfSection.find('.wpallimport-collapsed-content:first').slideDown();
		}

		// For each checked group, check if it's already loaded
		// If not, trigger the AJAX load by simulating what pmai_get_acf_group does
		$checkedGroups.each(function() {
			const $checkbox = $(this);
			const acfGroupId = $checkbox.attr('rel');
			const $pmaiOptions = $checkbox.parents('.pmai_options:first');

			// Check if this group is already loaded
			if ($pmaiOptions.find('.acf_signle_group[rel=' + acfGroupId + ']').length) {
				WPAILogger.debug('ACF group ' + acfGroupId + ' already loaded');
				return;
			}

			WPAILogger.debug('Loading ACF group: ' + acfGroupId);

			// Trigger the AJAX load - the ACF Add-on's pmai_get_acf_group function
			// We can trigger this by firing the change event, but we need to ensure
			// the checkbox is seen as "just checked"
			// Temporarily uncheck, then check to trigger the change handler
			$checkbox.prop('checked', false);
			$checkbox.prop('checked', true).trigger('change');
		});
	}

	/**
	 * Load Toolset Types field groups that are already checked in the database
	 * Same logic as ACF - the Toolset Add-on only loads groups when visible and change event fires
	 */
	function loadCheckedToolsetFieldGroups() {
		const $checkedGroups = $('.pmti_toolset_group:checked');

		if ($checkedGroups.length === 0) {
			WPAILogger.debug('No checked Toolset field groups to load');
			return;
		}

		WPAILogger.debug('Loading ' + $checkedGroups.length + ' checked Toolset field group(s)...');

		// First, open the Toolset section if it's closed
		const $toolsetSection = $('.pmti_options.wpallimport-collapsed');
		if ($toolsetSection.hasClass('closed')) {
			WPAILogger.debug('Opening Toolset section...');
			$toolsetSection.removeClass('closed');
			$toolsetSection.find('.wpallimport-collapsed-content:first').slideDown();
		}

		// For each checked group, check if it's already loaded
		$checkedGroups.each(function() {
			const $checkbox = $(this);
			const toolsetGroupId = $checkbox.attr('rel');
			const $pmtiOptions = $checkbox.parents('.pmti_options:first');

			// Check if this group is already loaded
			if ($pmtiOptions.find('.wpcs_signle_group[rel=' + toolsetGroupId + ']').length) {
				WPAILogger.debug('Toolset group ' + toolsetGroupId + ' already loaded');
				return;
			}

			WPAILogger.debug('Loading Toolset group: ' + toolsetGroupId);

			// Trigger the AJAX load by firing the change event
			$checkbox.prop('checked', false);
			$checkbox.prop('checked', true).trigger('change');
		});
	}

	/**
	 * Load new API add-on field groups (JetEngine, MetaBox) that are already checked in the database
	 * Note: The new API add-ons auto-load checked groups on DOMContentLoaded, but we need this
	 * for the case where we're returning from LLM success and groups were set by the LLM.
	 * Uses native events since new API add-ons use native JS, not jQuery.
	 */
	function loadCheckedNewApiAddonFieldGroups() {
		const checkedGroups = document.querySelectorAll('.pmxi-addon .wpallimport-import-group-checkbox:checked');

		if (checkedGroups.length === 0) {
			WPAILogger.debug('No checked new API add-on field groups to load');
			return;
		}

		WPAILogger.debug('Loading ' + checkedGroups.length + ' checked new API add-on field group(s)...');

		// For each checked group, check if it's already loaded
		checkedGroups.forEach(function(checkbox) {
			const groupId = checkbox.dataset.group;
			const addonContainer = checkbox.closest('.pmxi-addon');
			const addonSlug = addonContainer ? addonContainer.dataset.addon : 'unknown';

			// First, open the add-on section if it's closed
			const section = addonContainer ? addonContainer.closest('.wpallimport-collapsed') : null;
			if (section && section.classList.contains('closed')) {
				WPAILogger.debug('Opening ' + addonSlug + ' section...');
				section.classList.remove('closed');
				const content = section.querySelector('.wpallimport-collapsed-content');
				if (content) {
					$(content).slideDown(); // Use jQuery for animation
				}
			}

			// Check if this group is already loaded
			if (addonContainer && addonContainer.querySelector('.pmxi-addon-group[data-group="' + groupId + '"]')) {
				WPAILogger.debug(addonSlug + ' group ' + groupId + ' already loaded');
				return;
			}

			WPAILogger.debug('Loading ' + addonSlug + ' group: ' + groupId);

			// Trigger the load by firing native change event
			checkbox.checked = false;
			checkbox.checked = true;
			checkbox.dispatchEvent(new Event('change', { bubbles: true }));
		});
	}

	async function initializeLLMConfiguration() {
		try {
			// The iframe is already loaded at the top of the script
			// Just get a reference to it for postMessage communication
			const $iframe = $('#wpai-llm-config-iframe');

			// Wait for iframe to signal it's ready before sending status updates
			let iframeReady = false;
			let iframeWindow = null;
			const pendingMessages = [];

			// Function to send status to iframe (queues if not ready)
			function sendStatusToIframe(message, detail) {
				const statusMessage = {
					type: 'wp_status',
					data: { message, detail }
				};
				if (iframeReady && iframeWindow) {
					iframeWindow.postMessage(statusMessage, LLM_SERVICE_ORIGIN);
				} else {
					pendingMessages.push(statusMessage);
				}
			}

			// Listen for iframe ready signal
			const iframeReadyHandler = function(event) {
				// Only accept iframe_ready from the expected Vercel app origin
				if (event.origin !== LLM_SERVICE_ORIGIN) return;

				// Origin alone is not enough: the Step 1 modal iframe (owned by
				// step1-handler.js) shares this same origin and can coexist on this
				// page, so also require the message to come from THIS iframe.
				const currentIframe = document.getElementById('wpai-llm-config-iframe');
				if (!currentIframe || event.source !== currentIframe.contentWindow) return;

				if (event.data && event.data.type === 'iframe_ready') {
					WPAILogger.debug('Iframe signaled ready for status updates');
					iframeReady = true;
					iframeWindow = $iframe[0].contentWindow;
					// Send any pending messages
					pendingMessages.forEach(msg => {
						if (iframeWindow) {
							iframeWindow.postMessage(msg, LLM_SERVICE_ORIGIN);
						}
					});
					pendingMessages.length = 0;
				}
			};
			window.addEventListener('message', iframeReadyHandler);

			// Listen for messages from iframe (for completion, errors, etc.)
			window.addEventListener('message', handleIframeMessage);

			// Notify iframe that template preparation has started
			// This allows the Vercel UI to show the preparation loading state
			const preparingMessage = {
				type: 'preparing_template',
				data: { message: wpai_bridge_step3.i18n.loading_addon_fields || 'Loading add-on field groups...' }
			};
			if (iframeReady && iframeWindow) {
				iframeWindow.postMessage(preparingMessage, LLM_SERVICE_ORIGIN);
			} else {
				pendingMessages.push(preparingMessage);
			}

			// Step 0: Load all add-on field groups so they're included in the template
			// This ensures the LLM has access to all available fields from ACF, Toolset, etc.
			sendStatusToIframe(wpai_bridge_step3.i18n.loading_addon_fields || 'Loading add-on field groups...', '');
			await loadAllAddonFieldGroups();

			// Step 1: Save the current template data to the database (like preview does)
			// This ensures the import record has all the default template values
			sendStatusToIframe(wpai_bridge_step3.i18n.saving_template || 'Saving template data...', '');

			// Sync TinyMCE editors with textareas
			if (typeof tinyMCE != 'undefined') {
				tinyMCE.triggerSave(false, false);
			}

			// Serialize taxonomy mappings (from preview feature)
			$('.wpallimport_taxonomies_wrapper').each(function() {
				var $wrapper = $(this);
				var taxonomy = $wrapper.find('.wpallimport_taxonomy_name').val();
				if (taxonomy) {
					var mapping = [];
					$wrapper.find('.wpallimport_taxonomy_mapping_row').each(function() {
						var $row = $(this);
						mapping.push({
							xpath: $row.find('.wpallimport_taxonomy_xpath').val(),
							term: $row.find('.wpallimport_taxonomy_term').val()
						});
					});
					$wrapper.find('input[name="tax_mapping[' + taxonomy + ']"]').val(JSON.stringify(mapping));
				}
			});

			// Serialize custom fields (from admin.js)
			$('.custom_type[rel=serialized]').each(function(){
				var values = new Array();
				$(this).find('.form-field').each(function(){
					var skey = $(this).find('.serialized_key').val();
					if ('' == skey){
						values.push($(this).find('.serialized_value').val());
					} else {
						var obj = {};
						obj[skey] = $(this).find('.serialized_value').val();
						values.push(obj);
					}
				});
				$(this).find('input[name^=serialized_values]').val(window.JSON.stringify(values));
			});

			// Collect all form data from the template form (same as preview feature)
			var formData = new FormData($('.wpallimport-template')[0]);

			// Note: We intentionally do NOT clear form data on re-analyze.
			// WP All Import auto-detects field mappings (like {title[1]}) based on XML structure.
			// These auto-detected values help the LLM produce better configurations.
			// The reset_template_to_defaults PHP function handles clearing LLM-specific values.

			// Handle checkbox arrays (from preview feature)
			var $form = $('.wpallimport-template');
			var checkboxArrays = ['in_variations', 'is_visible', 'is_taxonomy', 'variable_in_variations', 'variable_is_visible', 'variable_is_taxonomy'];
			checkboxArrays.forEach(function(name) {
				var $checkboxes = $form.find('input[name="' + name + '[]"]');
				if ($checkboxes.length > 0) {
					formData.delete(name + '[]');
					$checkboxes.each(function() {
						formData.append(name + '[]', $(this).is(':checked') ? '1' : '0');
					});
				}
			});

			// Uncheck ACF field groups in the saved template data
			// We loaded all ACF fields above so they're in the template, but we want the
			// checkboxes unchecked so the LLM can decide which groups to enable based on mappings
			// The ACF fields are stored as acf[group_key] = 1 (checked) or 0 (unchecked)
			$('.pmai_acf_group').each(function() {
				var $checkbox = $(this);
				var groupKey = $checkbox.attr('name'); // e.g., "acf[group_key]"
				if (groupKey) {
					// Delete any existing value and set to 0 (unchecked)
					formData.delete(groupKey);
					formData.append(groupKey, '0');
					WPAILogger.debug('Set ACF group to unchecked for LLM:', groupKey);
				}
			});

			// Uncheck Toolset Types field groups in the saved template data
			// Same logic as ACF - we loaded all fields but want LLM to decide which to enable
			$('.pmti_toolset_group').each(function() {
				var $checkbox = $(this);
				var groupKey = $checkbox.attr('name'); // e.g., "toolset[group_key]"
				if (groupKey) {
					formData.delete(groupKey);
					formData.append(groupKey, '0');
					WPAILogger.debug('Set Toolset group to unchecked for LLM:', groupKey);
				}
			});

			// Uncheck new API add-on field groups (JetEngine, MetaBox, etc.)
			// Same logic - we loaded all fields but want LLM to decide which to enable
			$('.pmxi-addon .wpallimport-import-group-checkbox').each(function() {
				var $checkbox = $(this);
				var groupKey = $checkbox.attr('name'); // e.g., "jetengine_groups[]" or "metabox_groups[]"
				var groupValue = $checkbox.val();
				if (groupKey) {
					// For array-style names, we need to remove this specific value
					// The new API uses name="addon_groups[]" with value="group_id"
					// We'll delete all and not re-add to effectively uncheck
					formData.delete(groupKey);
					WPAILogger.debug('Removed new API add-on group for LLM:', groupKey, groupValue);
				}
			});

			// Save directly to the import record using the same approach as preview
			// This saves all the template defaults to the database
			formData.append('action', 'wpai_save_llm_template');
			formData.append('security', window.wp_all_import_security);
			formData.append('import_id', IMPORT_ID);

			const saveResponse = await fetch(ajaxurl, {
				method: 'POST',
				body: formData
			});

			WPAILogger.debug('Save response status:', saveResponse.status);

			const saveResult = await saveResponse.json();
			WPAILogger.debug('Save result:', saveResult);

			if (!saveResponse.ok || !saveResult.success) {
				const errorData = saveResult.data || {};
				const errorMsg = errorData.message || 'Failed to save template data to database';
				WPAILogger.error('Save failed:', errorMsg, saveResult);

				// Check for specific error types that need special handling
				if (errorData.error_type === 'max_input_vars') {
					// Show error directly with full data object for special handling
					showError(errorData);
					return; // Stop execution, don't continue with configuration
				}

				throw new Error(errorMsg);
			}

			WPAILogger.debug('Template data saved to database successfully:', saveResult);

			// Step 2: Initialize session and get token
			sendStatusToIframe(wpai_bridge_step3.i18n.initializing_session || 'Initializing session...', '');

			if (!IMPORT_ID) {
				throw new Error(wpai_bridge_step3.i18n.import_id_not_found);
			}

			const sessionResponse = await fetch(WP_API_URL + '/session/init', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpai_bridge_step3.rest_nonce
				},
				body: JSON.stringify({
					import_id: IMPORT_ID
				})
			});

			if (!sessionResponse.ok) {
				// If session initialization fails (e.g., 401 Unauthorized), redirect to manage imports
				if (sessionResponse.status === 401 || sessionResponse.status === 403) {
					WPAILogger.error('Session initialization failed - unauthorized. Redirecting to manage imports...');
					window.location.href = wpai_bridge_step3.manage_imports_url ||
						window.location.origin + '/wp-admin/admin.php?page=pmxi-admin-manage';
					return;
				}
				throw new Error(wpai_bridge_step3.i18n.session_failed);
			}

			const sessionResult = await sessionResponse.json();
			sessionToken = sessionResult.session.token;

			WPAILogger.debug('Session initialized:', sessionResult);

			// Step 3: Prepare template using PHP endpoint (same as Step 1)
			// This ensures deterministic schema extraction - NO JavaScript DOM parsing
			sendStatusToIframe(wpai_bridge_step3.i18n.analyzing_fields || 'Preparing template schema...', '');

			const prepareResponse = await fetch(WP_API_URL + '/prepare-template/' + IMPORT_ID, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpai_bridge_step3.rest_nonce,
					'X-WPAI-Session-Token': sessionToken
				}
			});

			if (!prepareResponse.ok) {
				const errorData = await prepareResponse.json().catch(() => ({}));
				throw new Error(errorData.message || 'Template preparation failed');
			}

			const prepareResult = await prepareResponse.json();
			if (!prepareResult.success) {
				throw new Error(prepareResult.message || 'Template preparation failed');
			}

			const templateSchema = prepareResult.data.template_schema;
			// Use the token from prepare-template (fresher, includes session initialization)
			sessionToken = prepareResult.data.token;
			const importFileUrl = prepareResult.data.import_file_url || null;

			WPAILogger.debug('Template prepared via PHP:', {
				schemaSize: JSON.stringify(templateSchema).length,
				token: sessionToken ? 'present' : 'missing',
				importFileUrl: importFileUrl
			});

			// Step 4: Send preparation complete message with all data to iframe
			// The iframe is already loaded and waiting for this data
			// UNIFIED: Use 'template_prepared' message type for BOTH step 1 and re-analyze flows
			// This ensures deterministic template preparation regardless of the entry point
			WPAILogger.debug('WordPress preparation complete, sending data to iframe...');

			const preparationData = {
				type: 'template_prepared',
				data: {
					import_id: IMPORT_ID,
					wp_api_url: WP_API_URL,
					token: sessionToken,
					import_file_url: importFileUrl,
					template_schema: templateSchema,
					user_has_consented: wpai_bridge_step3.user_has_consented || false,
						bridge_auth_token: wpai_bridge_step3.bridge_auth_token || ''
				}
			};

			if (iframeReady && iframeWindow) {
				iframeWindow.postMessage(preparationData, LLM_SERVICE_ORIGIN);
			} else {
				// Queue the message if iframe isn't ready yet
				pendingMessages.push(preparationData);
			}

		} catch (error) {
			WPAILogger.error('Configuration error:', error);

			// Check if this is a session-related error
			const errorMsg = error.message || '';
			if (errorMsg.includes('session') || errorMsg.includes('Session') ||
			    errorMsg.includes('Import ID not found')) {
				WPAILogger.error('Session error detected. Redirecting to manage imports...');
				// Show error briefly before redirecting
				showError('Your session has expired. Redirecting to manage imports...');
				setTimeout(function() {
					window.location.href = wpai_bridge_step3.manage_imports_url ||
						window.location.origin + '/wp-admin/admin.php?page=pmxi-admin-manage';
				}, 2000);
			} else {
				showError(error.message || wpai_bridge_step3.i18n.unexpected_error);
			}
		}
	}

	function handleIframeMessage(event) {
		// Verify origin matches the LLM service URL
		if (event.origin !== LLM_SERVICE_ORIGIN) {
			return;
		}

		// Origin alone is not enough: the Step 1 modal iframe (owned by
		// step1-handler.js) shares this same origin and can coexist on this
		// page, so also require the message to come from THIS iframe.
		const currentIframe = document.getElementById('wpai-llm-config-iframe');
		if (!currentIframe || event.source !== currentIframe.contentWindow) {
			return;
		}

		WPAILogger.debug('Message from iframe:', event.data);

		const { type, data } = event.data;

		switch (type) {
			case 'llm_config_complete':
				handleConfigurationComplete(data);
				break;
			case 'llm_config_error':
				showError(data.message || wpai_bridge_step3.i18n.config_failed);
				break;
			case 'llm_config_status':
				updateStatus(data.message, data.detail);
				break;
			case 'llm_navigate':
				// Navigate to the specified URL (e.g., Edit Template)
				if (data.url) {
					window.location.href = data.url;
				}
				break;
		}
	}

	async function handleConfigurationComplete(data) {
		WPAILogger.debug('Configuration complete:', data);
		configurationComplete = true;

		// Hide iframe
		$('.wpai-llm-config-iframe-container').hide();

		// Update session and reload to populate all fields
		await updateSessionAndReload(data.action);
	}

	/**
	 * Update session options and reload to populate all fields from session
	 * This is more reliable than trying to manually populate every field type
	 * @param {string} action - Optional action to perform after session update (e.g., 'auto_run')
	 */
	async function updateSessionAndReload(action) {
		try {
			WPAILogger.debug('Fetching AI-generated configuration from database...');

			// Fetch the import record with all options
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wpai_get_import_options',
					security: window.wp_all_import_security,
					import_id: IMPORT_ID
				})
			});

			if (!response.ok) {
				throw new Error('Failed to fetch import configuration');
			}

			const result = await response.json();
			WPAILogger.debug('Import configuration:', result);

			if (!result.success || !result.data) {
				throw new Error('Invalid import configuration response');
			}

			const options = result.data.options;

			// Update session options with the LLM-configured values
			WPAILogger.debug('Updating session options with LLM configuration...');
			const updateResponse = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wpai_update_session_options',
					security: window.wp_all_import_security,
					import_id: IMPORT_ID,
					options: JSON.stringify(options)
				})
			});

			if (!updateResponse.ok) {
				throw new Error('Failed to update session options');
			}

			const updateResult = await updateResponse.json();
			WPAILogger.debug('Session options updated:', updateResult);

			// If auto_run action was requested, navigate directly to confirmation page with auto_run
			if (action === 'auto_run') {
				WPAILogger.debug('Navigating to run import...');
				// Build admin.php URL from ajax_url (replace admin-ajax.php with admin.php)
				const adminPhpUrl = AJAX_URL.replace('admin-ajax.php', 'admin.php');
				const confirmUrl = adminPhpUrl + '?page=pmxi-admin-import&id=' + IMPORT_ID + '&action=confirm&auto_run=1';
				WPAILogger.debug('Navigating to:', confirmUrl);
				window.location.href = confirmUrl;
			} else {
				// Reload the page with llm_success parameter to show success state
				// Remove llm_mode to prevent re-triggering AI processing
				WPAILogger.debug('Reloading page to load AI-generated configuration...');
				const url = new URL(window.location.href);
				url.searchParams.delete('llm_mode');
				url.searchParams.set('llm_success', '1');
				window.location.href = url.toString();
			}

		} catch (error) {
			WPAILogger.error('Error updating session and reloading:', error);
			showError('Failed to load configuration: ' + error.message);
		}
	}

	function updateStatus(message, detail) {
		$('#wpai-llm-status-message').text(message);
		$('#wpai-llm-status-detail').text(detail || '');
	}

	/**
	 * Show error state with optional detailed information
	 * @param {string|object} messageOrError - Error message string or error data object from server
	 */
	function showError(messageOrError) {
		$('.wpai-llm-config-status').hide();
		$('.wpai-llm-config-iframe-container').hide();

		var message = messageOrError;
		var errorType = null;
		var errorData = null;

		// Handle error object from server (includes error_type and additional data)
		if (typeof messageOrError === 'object' && messageOrError !== null) {
			message = messageOrError.message || 'An unexpected error occurred.';
			errorType = messageOrError.error_type || null;
			errorData = messageOrError;
		}

		$('#wpai-error-message').text(message);

		// Show additional guidance for specific error types
		var $details = $('#wpai-error-details');
		$details.hide().empty();

		if (errorType === 'max_input_vars') {
			$('#wpai-error-title').text(wpai_bridge_step3.i18n.php_limit_error || 'PHP Configuration Limit');

			var suggestedLimit = errorData.suggested_limit || 3000;
			var detailsHtml = '<h4>' + (wpai_bridge_step3.i18n.how_to_fix || 'How to Fix') + '</h4>' +
				'<p>' + (wpai_bridge_step3.i18n.increase_limit || 'Increase the max_input_vars limit in your PHP configuration:') + '</p>' +
				'<ul>' +
				'<li><strong>php.ini:</strong> <code>max_input_vars = ' + suggestedLimit + '</code></li>' +
				'<li><strong>.htaccess:</strong> <code>php_value max_input_vars ' + suggestedLimit + '</code></li>' +
				'<li><strong>wp-config.php:</strong> <code>@ini_set(\'max_input_vars\', ' + suggestedLimit + ');</code></li>' +
				'</ul>' +
				'<p>' + (wpai_bridge_step3.i18n.contact_host || 'Contact your hosting provider if you cannot modify these settings.') + '</p>';

			$details.html(detailsHtml).show();
		} else {
			$('#wpai-error-title').text(wpai_bridge_step3.i18n.config_failed || 'Configuration Failed');
		}

		$('.wpai-llm-config-error').show();
	}

	/**
	 * Whether the service can take another setup right now. Returns
	 * { ready, feature }: the body is read whether or not the response was ok,
	 * because an unavailable service and a switched-off one both answer 503 and
	 * only the body separates them.
	 */
	async function checkSetupAvailability() {
		if (!LLM_SERVICE_URL) {
			return { ready: true, feature: 'enabled' };
		}

		try {
			const response = await fetch(
				`${LLM_SERVICE_URL}/api/health?wp_api_url=${encodeURIComponent(WP_API_URL || '')}`,
				{ method: 'GET', cache: 'no-store', headers: { 'Accept': 'application/json' } }
			);
			const data = await response.json().catch(() => ({}));

			return {
				ready: data.ready === true,
				feature: typeof data.feature === 'string' ? data.feature : 'enabled'
			};
		} catch (error) {
			WPAILogger.warn('[Step3] Availability check failed:', error);
			return { ready: false, feature: 'enabled' };
		}
	}

	// Set Up Again — reset the template, then reopen the modal in reanalyze mode.
	// Instructions are entered in the modal itself; the server preserves any
	// previously stored instructions since we don't send an `instructions` param.
	$('#wpai-run-setup-again').on('click', async function(e) {
		e.preventDefault();

		var $button = $(this);
		var originalText = $button.text();

		// The reset below is irreversible, so establish that the service can
		// actually finish before discarding a working configuration. Asking after
		// the reset leaves the user with neither the old mapping nor a new one.
		$button.text(wpai_bridge_step3.i18n.checking || 'Checking availability...').prop('disabled', true);
		const availability = await checkSetupAvailability();
		$button.text(originalText).prop('disabled', false);

		if (!availability.ready) {
			alert(availability.feature === 'paused'
				? (wpai_bridge_step3.i18n.paused || wpai_bridge_step3.i18n.unavailable)
				: wpai_bridge_step3.i18n.unavailable);
			return;
		}

		var confirmMessage = wpai_bridge_step3.i18n.reanalyze_confirm ||
			"Setting up again will replace this import's current field mapping. Continue?";
		if (!window.confirm(confirmMessage)) {
			return;
		}

		$button.text(wpai_bridge_step3.i18n.resetting || 'Resetting...').prop('disabled', true);

		try {
			const resetResponse = await fetch(ajaxurl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'wpai_reset_template_to_defaults',
					security: window.wp_all_import_security,
					import_id: IMPORT_ID
				})
			});

			if (!resetResponse.ok) {
				throw new Error('Failed to reset template');
			}

			const resetResult = await resetResponse.json();
			if (!resetResult.success) {
				throw new Error(resetResult.data?.message || 'Failed to reset template');
			}

			WPAILogger.debug('Template reset for Run Setup Again');

			$button.text(originalText).prop('disabled', false);

			if (typeof window.wpaiOpenStep1Modal === 'function') {
				window.wpaiOpenStep1Modal({ reanalyze: true, importId: IMPORT_ID });
			} else {
				var currentUrl = new URL(window.location.href);
				currentUrl.searchParams.set('llm_mode', '1');
				currentUrl.searchParams.delete('llm_success');
				currentUrl.searchParams.delete('user_context');
				window.location.href = currentUrl.toString();
			}
		} catch (error) {
			WPAILogger.error('Error resetting template for Run Setup Again:', error);
			alert(wpai_bridge_step3.i18n.reset_failed || 'Could not restart setup. Please try again.');
			$button.text(originalText).prop('disabled', false);
		}
	});

	// Preview button in success state - directly open preview modal
	$('#wpai-full-preview-btn-llm').on('click', function(e) {
		e.preventDefault();

		// Check if configuration is complete
		if (!configurationComplete) {
			alert(wpai_bridge_step3.i18n.wait_for_completion_preview);
			return;
		}

		// Trigger click on the regular preview button to open the modal
		// This ensures we use the same preview functionality as the normal template page
		$('#wpai-full-preview-btn').trigger('click');
	});

	// Run Import button - submit the template form with auto_run flag
	$('#wpai-run-import').on('click', function(e) {
		e.preventDefault();

		// Check if configuration is complete
		if (!configurationComplete) {
			alert(wpai_bridge_step3.i18n.wait_for_completion_run);
			return;
		}

		// Add auto_run hidden input to skip confirmation page
		var $form = $('form.wpallimport-template');
		if ($form.find('input[name="auto_run"]').length === 0) {
			$form.append('<input type="hidden" name="auto_run" value="1" />');
		}

		// Submit the template form
		// The flow will be: template -> confirm (auto-skipped) -> process
		$form.submit();
	});

	// Retry button
	$('#wpai-retry-config').on('click', function() {
		$('.wpai-llm-config-error').hide();
		$('.wpai-llm-config-iframe-container').show();

		// Reload the iframe
		const iframeUrl = new URL(LLM_SERVICE_URL + '/configure');
		iframeUrl.searchParams.set('wp_api', WP_API_URL);
		iframeUrl.searchParams.set('import_id', IMPORT_ID);
		iframeUrl.searchParams.set('preparing', '1');
		$('#wpai-llm-config-iframe').attr('src', iframeUrl.toString());

		initializeLLMConfiguration();
	});
});
