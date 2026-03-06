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

}