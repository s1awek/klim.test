<?php

class Fupi_i18n {
	
	// this function was used to tell WP to look for translation files in the plugin's folder instead of the default one (WP folder) but it turned out that it wasn't necessary

	public function fupi_load_plugin_textdomain() {

		// get WP FP folder name
		$fupi_path_arr = explode('/', FUPI_PATH );
		$fupi_folder_name = end( $fupi_path_arr );
		
		load_plugin_textdomain(
			'full-picture-analytics-cookie-notice',
			false,
			$fupi_folder_name . '/languages/' // location of the plugin's folder with language files relative to WP_PLUGIN_DIR
		);
	}

	// Check if the language file exists in the WP FP's folder. If it doesn't, then look for the file from the default path (WP folder)
	public function fupi_load_textdomain_mofile( $mofile, $domain ){

		// $mofile: [...]/public_html/wp-content/languages/plugins/full-picture-analytics-cookie-notice-[locale].mo

		if ( 'full-picture-analytics-cookie-notice' === $domain ) {	
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain ); // this takes into account situations where the user uses a different language than the rest of the site
			$fupi_mofile = FUPI_PATH . '/languages/' . $domain . '-' . $locale . '.mo';
			if ( file_exists( $fupi_mofile ) ) return $fupi_mofile;
		}

		return $mofile;
	}
}
