<?php

class Fupi_Activator {

	public static function activate() {

		// Make sure not to redirect when multiple plugins are bulk activated
		// Saves user ID to ensure it only redirects for the user who activated the plugin.
		// The redirect is hooked up to admin_init - Fn is in class-fupi-admin.php

		if (
			( isset( $_REQUEST['action'] ) && 'activate-selected' === $_REQUEST['action'] ) &&
			( isset( $_POST['checked'] ) && count( $_POST['checked'] ) > 1 )
		) {
			return;
		}

		add_option( 'fupi_activation_redirect', wp_get_current_user()->ID );
	}
}
