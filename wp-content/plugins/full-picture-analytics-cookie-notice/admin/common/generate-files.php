<?php

class Fupi_Generate_Files {

    private $tools;
    private $main;
    private $cook;
	private $ver;

	// $options can have the following keys:
	// - folder
	// - file_name
	// - file_format
	// - file_content

	public function __construct() {
		$this->tools = get_option( 'fupi_tools' );
    	$this->main = get_option( 'fupi_main' );
    	$this->cook = get_option( 'fupi_cook' );
		$this->ver = get_option( 'fupi_versions' );
	}

	public function make_file( $options ){

		// Create directory

		$folder_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/' . $options['folder'];

		if ( ! file_exists( $folder_path ) ) {
			mkdir( $folder_path, 0755, true );
		}

		// Save file

		$file_path = $folder_path . '/' . $options['file_name'] . '.' . $options['file_format'];

		if ( $options['file_format'] == 'json' ) {
			$options['file_content'] = json_encode( $options['file_content'], JSON_UNESCAPED_UNICODE );
		}

		$result = file_put_contents( $file_path, $options['file_content'] );

		if ( $result === false ) {
			if ( ! empty( $this->ver['debug'] ) ) trigger_error('[FP] Error generating ' . $file_path . ' file');
			return 'error';
		};

		// Add index.php to the same folder

		$index_file_path = $folder_path . '/index.php';

		if ( ! file_exists( $index_file_path ) ) {
			$index_file_content = '<?php
			header("HTTP/1.0 403 Forbidden");
			echo "Access denied.";
			exit;';
		
			file_put_contents( $index_file_path, $index_file_content );
		};

		if ( ! empty( $this->ver['debug'] ) ) trigger_error('[FP] Generated ' . $options['file_name'] . '.' . $options['file_format'] . ' file');

		return $file_path;

	}

	// GENERATE HEAD.JS

	public function make_head_js_file( $updated_settings_id, $clean_data ){ // $updated_settings_id is used in the head-js.php and location.php

		$output = ''; // the variable $output is also used in the head-js
		
		// GET contents of head_js (data saved in $output var)
		
		include_once FUPI_PATH . '/public/in_head/head-js.php';

		$js_folder_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/js/';

		if ( ! file_exists( $js_folder_path ) ) {
			mkdir( $js_folder_path, 0755, true );
		}

		$head_js_file_path = $js_folder_path . '/head.js';

		// GET contents of helpers.js
		
		$common_folder_path = FUPI_URL . '/public/common/';
		$output .= "\r\r" . file_get_contents( $common_folder_path . 'fupi-helpers.js' );

		// combine head and helpers JS
		$result = file_put_contents( $head_js_file_path, $output );

		if ( $result === false ) return 'error';

		// check if index.php file is in the same folder
		$index_file_path = $js_folder_path . '/index.php';

		if ( ! file_exists( $index_file_path ) ) {
			$index_file_content = '<?php
			header("HTTP/1.0 403 Forbidden");
			echo "Access denied.";
			exit;';
		
			file_put_contents( $index_file_path, $index_file_content );
		};

		if ( ! empty( $this->ver['debug'] ) ) trigger_error('[FP] Generated head.js file');

		return true;
	}

	// GENERATE CSCR FILES

	public function make_cscr_js_files( $cscr_settings ) {
		include_once FUPI_PATH . '/admin/modules/cscr/cscr-generate-files.php';
		new Fupi_generate_cscr_files( $cscr_settings );
	}

}