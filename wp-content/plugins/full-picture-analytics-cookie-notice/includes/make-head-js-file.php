<?php
/*
// Uses "$updated_settings_id" variable, like 'cook' or 'ga41' - must be set in the calling function

$output = ''; // the variable $output is also used in the head-js
		
// GET contents of head_js ($output var is used there too)
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

// check if index.php file is in the same folder
$index_file_path = $js_folder_path . '/index.php';

if ( ! file_exists( $index_file_path ) ) {
    $index_file_content = '<?php
    header("HTTP/1.0 403 Forbidden");
    echo "Access denied.";
    exit;';

    file_put_contents( $index_file_path, $index_file_content );
};

if ( isset( $this->ver['debug'] ) ) trigger_error('[FP] Generated head.js file');*/