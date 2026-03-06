// CONFLICT CHECKER - Check for plugin/theme conflicts via AJAX

(()=>{
	
	const checkBtns = FP.findAll('.fupi_check_conflicts_btn');

	if ( checkBtns.length == 0 ) return;

	checkBtns.forEach( btn => checkForConflicts(btn) );
	
	function checkForConflicts(checkBtn){
		checkBtn.addEventListener('click', function() {
		
			// Show loading state
			checkBtn.disabled = true;
			const originalText = checkBtn.textContent;
			checkBtn.textContent = fupi_conflicts_data.i18n.checking;
			
			// Make AJAX request
			fetch(fupi_conflicts_data.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'fupi_check_conflicts',
					security: fupi_conflicts_data.nonce
				}),
				credentials: 'same-origin'
			})
			.then(response => response.json())
			.then(data => {
				// Reset button
				checkBtn.disabled = false;
				checkBtn.textContent = originalText;
				
				// Display results in popup
				displayConflicts(data);
			})
			.catch(error => {
				// Handle error
				console.error('Error checking conflicts:', error);
				checkBtn.disabled = false;
				checkBtn.textContent = originalText;
				
				// Show error message
				const resultsContainer = FP.findID('fupi_conflicts_results');
				if (resultsContainer) {
					resultsContainer.innerHTML = '<p style="color: red;"><span class="dashicons dashicons-warning"></span> ' + fupi_conflicts_data.i18n.error_occurred + '</p>';
					openPopup();
				}
			});
		});
	}
	
	function displayConflicts(data) {
		const resultsContainer = FP.findID('fupi_conflicts_results');
		
		if (!resultsContainer) {
			console.error('Conflicts results container not found');
			return;
		}
		
		if (data.success && data.data.has_conflicts) {
			let html = '';
			data.data.conflicts.forEach(conflict => {
				html += '<p><span class="dashicons dashicons-warning" style="color: red"></span> <span>' + conflict.message + '</span></p>';
			});
			resultsContainer.innerHTML = html;
		} else if (data.success) {
			resultsContainer.innerHTML = '<p style="color: green;"><span class="dashicons dashicons-yes-alt"></span> <span>' + fupi_conflicts_data.i18n.no_conflicts + '</span></p>';
		} else {
			resultsContainer.innerHTML = '<p><span class="dashicons dashicons-warning" style="color: red;"></span> ' + (data.data || fupi_conflicts_data.i18n.error_generic) + '</p>';
		}
		
		// Open the popup
		openPopup();
	}
	
	function openPopup() {
		// Create temporary button to trigger the existing popup system
		const popupBtn = document.createElement('button');
		popupBtn.classList.add('fupi_open_popup');
		popupBtn.dataset.popup = 'fupi_conflicts_popup_content';
		popupBtn.style.display = 'none';
		document.body.appendChild(popupBtn);
		popupBtn.click();
		// Clean up
		setTimeout(() => {
			document.body.removeChild(popupBtn);
		}, 100);
	}
	
})();